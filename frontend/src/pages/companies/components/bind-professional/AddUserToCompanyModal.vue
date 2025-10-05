<template>
  <BaseDialog
    :model-value="modelValue"
    title="Adicionar Profissional"
    subtitle="Selecione os profissionais para associar à empresa"
    icon="mdi-account-plus"
    icon-color="primary"
    :fullscreen="$vuetify.display.mobile"
    :show-progress="true"
    :progress="formProgress"
    @close="closeModal"
  >
    <v-form ref="formRef" v-model="isValid" @submit.prevent="handleSubmit">
      <v-row>
        <!-- Filtros de Busca -->
        <v-col cols="12">
          <h3 class="text-h6 mb-4 d-flex align-center">
            <v-icon size="20" class="mr-2">mdi-filter</v-icon>
            Filtros de Busca
          </h3>
        </v-col>

        <v-col cols="12" md="6">
          <v-text-field
            v-model="searchQuery"
            label="Buscar por nome ou email"
            variant="outlined"
            density="compact"
            rounded="lg"
            prepend-inner-icon="mdi-magnify"
            clearable
            hint="Digite para filtrar os profissionais"
            persistent-hint
            @input="performSearch"
          />
        </v-col>


        <!-- Lista de Usuários Disponíveis -->
        <v-col cols="12">
          <h3 class="text-h6 mb-4 d-flex align-center">
            <v-icon size="20" class="mr-2">mdi-account-multiple</v-icon>
            Profissionais Disponíveis
            <v-chip
              v-if="selectedUsers.length > 0"
              color="primary"
              size="small"
              class="ml-2"
            >
              {{ selectedUsers.length }} selecionado(s)
            </v-chip>
          </h3>
        </v-col>

        <!-- Loading State -->
        <v-col v-if="loading" cols="12">
          <div class="text-center py-8">
            <v-progress-circular indeterminate color="primary" size="48" />
            <p class="text-body-1 mt-4">Carregando profissionais...</p>
          </div>
        </v-col>

        <!-- Empty State -->
        <v-col v-else-if="availableUsers.length === 0" cols="12">
          <div class="text-center py-8">
            <v-icon size="64" color="grey-lighten-1">mdi-account-multiple-outline</v-icon>
            <p class="text-h6 mt-4">Nenhum profissional encontrado</p>
            <p class="text-body-2 text-medium-emphasis">
              Todos os profissionais já estão associados a esta empresa
            </p>
          </div>
        </v-col>

        <!-- Users List -->
        <v-col v-else cols="12">
          <div class="users-list">
            <v-card
              v-for="user in availableUsers"
              :key="user.id"
              class="user-item mb-3"
              :class="{ 'user-selected': isUserSelected(user.id) }"
              variant="outlined"
              hover
              @click="toggleUserSelection(user)"
            >
              <v-card-text class="pa-4">
                <div class="d-flex align-center">
                  <v-checkbox
                    :model-value="isUserSelected(user.id)"
                    color="primary"
                    hide-details
                    class="mr-3"
                    @click.stop="toggleUserSelection(user)"
                  />

                  <div class="flex-grow-1">
                    <div class="d-flex align-center justify-space-between">
                      <div>
                        <div class="text-h6 font-weight-bold">
                          {{ user.name }}
                        </div>
                        <div class="text-body-2 text-medium-emphasis">
                          {{ user.email }}
                        </div>

                      </div>

                      <div class="d-flex align-center gap-2">
                        <!-- Profile Badge -->
                        <v-chip
                          v-if="user.profile"
                          :color="getProfileColor(user.profile.name)"
                          variant="tonal"
                          size="small"
                        >
                          <v-icon start size="14">{{ getProfileIcon(user.profile.name) }}</v-icon>
                          {{ user.profile.display_name || user.profile.name }}
                        </v-chip>

                        <!-- WhatsApp Badge -->
                        <v-chip
                          v-if="user.phone && user.has_whatsapp"
                          color="success"
                          size="x-small"
                        >
                          <v-icon start size="10">mdi-whatsapp</v-icon>
                          WhatsApp
                        </v-chip>
                      </div>
                    </div>

                    <!-- User Details -->
                    <div class="mt-2">
                      <div class="text-body-2">
                        <v-icon size="16" color="primary" class="mr-1">mdi-phone</v-icon>
                        {{ user.phone ? formatPhone(user.phone) : 'Telefone não informado' }}
                      </div>
                    </div>
                  </div>
                </div>
              </v-card-text>
            </v-card>
          </div>

          <!-- Pagination -->
          <div v-if="pagination.total > 0" class="mt-4">
            <div class="text-caption text-medium-emphasis mb-2">
              Mostrando {{ pagination.from }} a {{ pagination.to }} de {{ pagination.total }} profissional(is)
            </div>
            <v-pagination
              v-model="pagination.current_page"
              :length="pagination.last_page"
              :total-visible="5"
              color="primary"
              @update:model-value="handlePageChange"
            />
          </div>
        </v-col>
      </v-row>
    </v-form>

    <template #actions>
      <v-spacer />
      <div class="d-flex modal-actions-container">
        <BtnCancel @click="closeModal" />
        <BtnSave
          :loading="loading"
          :disabled="!isValid || selectedUsers.length === 0"
          text="Associar Profissionais"
          @click="handleSubmit"
        />
      </div>
    </template>
  </BaseDialog>
</template>

<script lang="ts" setup>
import { ref, computed, watch, nextTick } from "vue";
import { showSuccessToast, showErrorToast } from "@/utils/swal";
import { useCompaniesApi } from "@/pages/companies/api";
import { useMask } from "@/composables/useMask";
import { useProfileUtils } from "@/composables/useProfileUtils";
import BaseDialog from "@/components/BaseDialog.vue";
import { BtnCancel, BtnSave } from "@/components/buttons";

interface Props {
  modelValue: boolean;
  company?: any;
}

interface Emits {
  (e: "update:modelValue", value: boolean): void;
  (e: "reload"): void;
}

const props = withDefaults(defineProps<Props>(), {
  company: null,
});

const emit = defineEmits<Emits>();

// Composables
const { getAvailableUsersForCompany, attachProfessional } = useCompaniesApi();
const { formatPhone } = useMask();
const { getProfileColor, getProfileIcon } = useProfileUtils();

// Reactive data
const formRef = ref();
const isValid = ref(false);
const loading = ref(false);

// Form data
const selectedUsers = ref<any[]>([]);
const availableUsers = ref<any[]>([]);

// Search and pagination
const searchQuery = ref("");
const pagination = ref({
  current_page: 1,
  per_page: 12,
  total: 0,
  last_page: 1,
  from: 0,
  to: 0
});

// Computed
const formProgress = computed(() => {
  if (selectedUsers.value.length === 0) return 0;
  return Math.min((selectedUsers.value.length / 5) * 100, 100);
});

// Methods
const resetForm = () => {
  selectedUsers.value = [];
  availableUsers.value = [];
  searchQuery.value = "";
  pagination.value = {
    current_page: 1,
    per_page: 12,
    total: 0,
    last_page: 1,
    from: 0,
    to: 0
  };
};

const isUserSelected = (userId: number) => {
  return selectedUsers.value.some(user => user.id === userId);
};

const toggleUserSelection = (user: any) => {
  const index = selectedUsers.value.findIndex(u => u.id === user.id);
  if (index > -1) {
    selectedUsers.value.splice(index, 1);
  } else {
    selectedUsers.value.push(user);
  }
};

const loadAvailableUsers = async (page: number = 1) => {
  try {
    loading.value = true;

    if (!props.company?.id) {
      availableUsers.value = [];
      pagination.value = {
        current_page: 1,
        per_page: 12,
        total: 0,
        last_page: 1,
        from: 0,
        to: 0
      };
      return;
    }

    // Buscar profissionais disponíveis com paginação
    const result = await getAvailableUsersForCompany(
      props.company.id,
      searchQuery.value,
      page,
      pagination.value.per_page
    );

    availableUsers.value = result.data || [];

    pagination.value = {
      current_page: result.current_page || 1,
      per_page: result.per_page || 12,
      total: result.total || 0,
      last_page: result.last_page || 1,
      from: (result as any).from || 0,
      to: (result as any).to || 0
    };
  } catch (error) {
    console.error("Erro ao carregar profissionais disponíveis:", error);
    showErrorToast("Erro ao carregar profissionais", "Erro!");
  } finally {
    loading.value = false;
  }
};



const performSearch = () => {
  pagination.value.current_page = 1;
  loadAvailableUsers(1);
};

const handlePageChange = (page: number) => {
  pagination.value.current_page = page;
  loadAvailableUsers(page);
};

const handleSubmit = async () => {
  if (selectedUsers.value.length === 0) {
    showErrorToast("Selecione pelo menos um profissional", "Atenção!");
    return;
  }

  try {
    loading.value = true;

    // Associar cada profissional selecionado
    const promises = selectedUsers.value.map(user =>
      attachProfessional(props.company.id, user.id)
    );

    await Promise.all(promises);

    showSuccessToast(
      `${selectedUsers.value.length} profissional(is) associado(s) com sucesso!`,
      "Sucesso!"
    );

    // Emitir evento para atualizar a lista
    emit("reload");

    // Fechar modal
    closeModal();

  } catch (error: any) {
    console.error("Erro ao associar profissionais:", error);
    showErrorToast(
      error.response?.data?.message || "Erro ao associar profissionais",
      "Erro!"
    );
  } finally {
    loading.value = false;
  }
};


const closeModal = () => {
  emit("update:modelValue", false);
  resetForm();
};




// Watchers
watch(
  () => props.modelValue,
  (newValue) => {
    if (newValue) {
      loadAvailableUsers();
      nextTick(() => {
        formRef.value?.resetValidation();
      });
    }
  }
);

watch(
  () => props.company,
  () => {
    if (props.modelValue) {
      loadAvailableUsers();
    }
  },
  { deep: true }
);
</script>

<style scoped>
.users-list {
  max-height: 400px;
  overflow-y: auto;
}

.user-item {
  transition: all 0.2s ease;
  cursor: pointer;
}

.user-item:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.user-selected {
  border-color: rgb(var(--v-theme-primary)) !important;
  background: rgba(var(--v-theme-primary), 0.05);
}

.user-selected:hover {
  background: rgba(var(--v-theme-primary), 0.08);
}

/* Custom scrollbar */
.users-list::-webkit-scrollbar {
  width: 6px;
}

.users-list::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.users-list::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.users-list::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .modal-actions-container {
    flex-direction: column;
    gap: 8px;
    width: 100%;
  }

  .modal-actions-container .v-btn {
    width: 100%;
    margin-right: 0 !important;
  }
}

/* Desktop styles */
.modal-actions-container {
  gap: 16px;
}
</style>
