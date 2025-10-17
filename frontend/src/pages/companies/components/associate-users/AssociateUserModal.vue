<template>
  <BaseDialog
    :model-value="modelValue"
    title="Associar Usuário à Empresa"
    subtitle="Selecione um usuário e defina o perfil para associação"
    icon="mdi-account-plus"
    icon-color="primary"
    :fullscreen="$vuetify.display.mobile"
    :show-progress="true"
    :progress="formProgress"
    @close="closeModal"
  >
    <v-form ref="formRef" v-model="isValid" @submit.prevent="handleSubmit">
      <v-row>
        <!-- Company Info -->
        <v-col cols="12">
          <v-card variant="outlined" class="mb-4">
            <v-card-text class="pa-4">
              <div class="d-flex align-center">
                <v-avatar color="primary" size="48" class="mr-3">
                  <v-icon color="white">mdi-domain</v-icon>
                </v-avatar>
                <div>
                  <h3 class="text-h6 font-weight-bold">{{ company?.name }}</h3>
                  <p class="text-body-2 text-medium-emphasis">{{ company?.email }}</p>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Profile Selection -->
        <v-col cols="12">
          <h3 class="text-h6 mb-4 d-flex align-center">
            <v-icon size="20" class="mr-2">mdi-account-cog</v-icon>
            Selecionar Perfil
          </h3>
        </v-col>

        <v-col cols="12">
          <v-select
            v-model="selectedProfileId"
            :items="profileOptions"
            item-title="text"
            item-value="value"
            label="Perfil do usuário na empresa *"
            variant="outlined"
            density="comfortable"
            rounded="lg"
            prepend-inner-icon="mdi-shield-account"
            :rules="profileRules"
            hint="Defina o perfil que o usuário terá nesta empresa"
            persistent-hint
            required
          >
            <template #item="{ props, item }">
              <v-list-item v-bind="props">
                <template #prepend>
                  <v-avatar
                    :color="getProfileColor((item.raw as any)?.name)"
                    size="32"
                    class="mr-3"
                  >
                    <v-icon size="16" color="white">
                      {{ getProfileIcon((item.raw as any)?.name) }}
                    </v-icon>
                  </v-avatar>
                </template>
              </v-list-item>
            </template>
          </v-select>
        </v-col>

        <!-- User Search -->
        <v-col cols="12">
          <h3 class="text-h6 mb-4 d-flex align-center">
            <v-icon size="20" class="mr-2">mdi-account-search</v-icon>
            Buscar Usuário
          </h3>
        </v-col>

        <v-col cols="12">
          <v-text-field
            v-model="searchQuery"
            label="Buscar por nome ou email"
            variant="outlined"
            density="comfortable"
            rounded="lg"
            prepend-inner-icon="mdi-magnify"
            clearable
            hint="Digite para filtrar os usuários disponíveis"
            persistent-hint
            @input="performSearch"
          />
        </v-col>

        <!-- Loading State -->
        <v-col v-if="loading" cols="12">
          <div class="text-center py-8">
            <v-progress-circular indeterminate color="primary" size="48" />
            <p class="text-body-1 mt-4">Carregando usuários...</p>
          </div>
        </v-col>

        <!-- Empty State -->
        <v-col v-else-if="availableUsers.length === 0" cols="12">
          <div class="text-center py-8">
            <v-icon size="64" color="grey-lighten-1">mdi-account-multiple-outline</v-icon>
            <p class="text-h6 mt-4">Nenhum usuário encontrado</p>
            <p class="text-body-2 text-medium-emphasis">
              {{ searchQuery ? 'Nenhum usuário corresponde aos filtros de busca' : 'Todos os usuários já estão associados a esta empresa' }}
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
              :class="{ 'user-selected': selectedUserId === user.id }"
              variant="outlined"
              hover
              @click="selectUser(user)"
            >
              <v-card-text class="pa-4">
                <div class="d-flex align-center">
                  <v-radio
                    :model-value="selectedUserId === user.id"
                    color="primary"
                    class="mr-3"
                    @click.stop="selectUser(user)"
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
              Mostrando {{ pagination.from }} a {{ pagination.to }} de {{ pagination.total }} usuário(s)
            </div>
            <v-pagination
              v-model="pagination.current_page"
              :length="pagination.last_page"
              :total-visible="3"
              color="primary"
              size="small"
              @update:model-value="handlePageChange"
            />
          </div>
        </v-col>
      </v-row>

      <!-- Actions -->
      <div slot="actions" class="modal-actions-container d-flex justify-end">
        <v-btn
          variant="outlined"
          @click="closeModal"
          :disabled="submitting"
        >
          Cancelar
        </v-btn>
        <v-btn
          color="primary"
          type="submit"
          :loading="submitting"
          :disabled="!isValid || !selectedUserId || !selectedProfileId"
        >
          Associar Usuário
        </v-btn>
      </div>
    </v-form>
  </BaseDialog>
</template>

<script lang="ts" setup>
import { ref, computed, watch, nextTick } from "vue";
import BaseDialog from "@/components/BaseDialog.vue";
import { useCompaniesApi } from "@/pages/companies/api";
import { useProfilesApi } from "@/pages/profiles/api";
import { useMask } from "@/composables/useMask";
import { useProfileUtils } from "@/composables/useProfileUtils";
import { showSuccessToast, showErrorToast } from "@/utils/swal";

interface Props {
  modelValue: boolean;
  company: any;
}

interface Emits {
  (e: "update:modelValue", value: boolean): void;
  (e: "reload"): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const { formatPhone } = useMask();
const { getProfileColor, getProfileIcon } = useProfileUtils();

// Composables
const {
  getAvailableUsers,
  attachUserToCompany,
} = useCompaniesApi();

const {
  getCombo: getProfilesCombo,
} = useProfilesApi();

// Form refs
const formRef = ref();
const isValid = ref(false);
const submitting = ref(false);

// State
const availableUsers = ref<any[]>([]);
const loading = ref(false);
const searchQuery = ref("");
const selectedUserId = ref<number | null>(null);
const selectedProfileId = ref<number | null>(null);
const profiles = ref<any[]>([]);

// Pagination
const pagination = ref({
  current_page: 1,
  per_page: 15,
  total: 0,
  last_page: 1,
  from: 0,
  to: 0
});

// Profile options
const profileOptions = computed(() =>
  profiles.value.map(profile => ({
    text: profile.display_name || profile.name,
    value: profile.id,
    raw: profile
  }))
);

// Validation rules
const profileRules = [
  (v: any) => !!v || "Selecione um perfil para o usuário"
];

// Computed
const formProgress = computed(() => {
  let progress = 0;
  if (selectedProfileId.value) progress += 50;
  if (selectedUserId.value) progress += 50;
  return progress;
});

// Methods
const loadAvailableUsers = async (page: number = 1) => {
  try {
    loading.value = true;

    if (!props.company?.id) {
      availableUsers.value = [];
      return;
    }

    const result = await getAvailableUsers(
      props.company.id,
      searchQuery.value,
      page,
      pagination.value.per_page
    );

    availableUsers.value = result.data || [];
    pagination.value = {
      current_page: result.current_page || 1,
      per_page: result.per_page || 15,
      total: result.total || 0,
      last_page: result.last_page || 1,
      from: (result as any).from || 0,
      to: (result as any).to || 0
    };
  } catch (error) {
    console.error("Erro ao carregar usuários disponíveis:", error);
    showErrorToast("Erro ao carregar usuários", "Erro!");
  } finally {
    loading.value = false;
  }
};

const loadProfiles = async () => {
  try {
    const result = await getProfilesCombo();
    profiles.value = result || [];
  } catch (error) {
    console.error("Erro ao carregar perfis:", error);
    showErrorToast("Erro ao carregar perfis", "Erro!");
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

const selectUser = (user: any) => {
  selectedUserId.value = user.id;
};

const handleSubmit = async () => {
  if (!selectedUserId.value || !selectedProfileId.value) {
    showErrorToast("Selecione um usuário e um perfil", "Atenção!");
    return;
  }

  try {
    submitting.value = true;

    await attachUserToCompany(
      props.company.id,
      selectedUserId.value,
      selectedProfileId.value
    );

    showSuccessToast("Usuário associado com sucesso à empresa!", "Sucesso!");
    emit("reload");
    closeModal();

  } catch (error: any) {
    console.error("Erro ao associar usuário:", error);
    showErrorToast(
      error.response?.data?.message || "Erro ao associar usuário",
      "Erro!"
    );
  } finally {
    submitting.value = false;
  }
};

const closeModal = () => {
  emit("update:modelValue", false);
  resetForm();
};

const resetForm = () => {
  selectedUserId.value = null;
  selectedProfileId.value = null;
  searchQuery.value = "";
  availableUsers.value = [];
  pagination.value = {
    current_page: 1,
    per_page: 15,
    total: 0,
    last_page: 1,
    from: 0,
    to: 0
  };

  nextTick(() => {
    formRef.value?.resetValidation();
  });
};

// Watchers
watch(
  () => props.modelValue,
  (newValue) => {
    if (newValue) {
      loadAvailableUsers();
      loadProfiles();
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
