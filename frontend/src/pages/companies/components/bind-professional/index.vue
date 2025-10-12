<template>
  <BasePage
    :title="`Profissionais da Empresa - ${company?.name || 'Carregando...'}`"
    subtitle="Gerencie os profissionais associados a esta empresa"
    :breadcrumbs="[
      { title: 'Empresas', to: '/companies' },
      { title: 'Profissionais' },
    ]"
  >
    <!-- Action Bar -->
    <template #actionBar>
      <ActionBar>
        <template #left>
          <BtnNew
            v-if="hasPermission('companies.attach_professional')"
            text="Adicionar Profissional"
            @click="showAddUserModal = true"
          />
        </template>
        <template #right>
          <div class="d-flex action-buttons-container">
            <ExportActions
              :data="companyUsers"
              filename="profissionais-empresa"
              button-text="Exportar"
              color="primary"
              variant="outlined"
              prepend-icon="mdi-download"
              :endpoint="`/companies/${companyId}/bind-professional`"
              :filters="{ search: searchQuery || undefined }"
            />
          </div>
        </template>
      </ActionBar>
    </template>

    <!-- Filters -->
    <template #filters>
      <FiltersCard :show="true">
        <template #filtersCard>
          <div class="filters-header mb-4">
            <h3 class="text-h6 font-weight-bold d-flex align-center">
              <v-icon class="mr-2" color="primary">mdi-filter</v-icon>
              Filtros de Busca
            </h3>
          </div>
          <v-row>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="searchQuery"
                label="Buscar nome ou email"
                prepend-inner-icon="mdi-magnify"
                variant="outlined"
                density="compact"
                clearable
                rounded="lg"
                hide-details
                @keyup.enter="performSearch"
              />
            </v-col>
          </v-row>
        </template>
        <template #actionsFilters>
          <BtnSearch @click="performSearch" />
          <BtnFilter @click="clearFilters" />
        </template>
      </FiltersCard>
    </template>

    <!-- Content -->
    <template #content>
      <!-- Loading State -->
      <div v-if="loading" class="users-loading">
        <v-skeleton-loader v-for="i in 5" :key="i" type="card" class="mb-4" />
      </div>

      <!-- Empty State -->
      <div v-else-if="companyUsers.length === 0" class="users-empty">
        <v-icon size="64" color="grey-lighten-1"
          >mdi-account-multiple-outline</v-icon
        >
        <p class="text-h6 mt-4">Nenhum profissional associado</p>
        <p class="text-body-2 text-medium-emphasis">
          Adicione profissionais para começar a gerenciar esta empresa
        </p>
      </div>

      <!-- Users Grid -->
      <div v-else class="users-grid">
        <v-card
          v-for="item in companyUsers"
          :key="item.id"
          class="user-card"
          elevation="2"
          hover
        >
          <!-- Card Header -->
          <v-card-title class="user-card-header">
            <div class="d-flex align-center">
              <div class="flex-grow-1">
                <div class="text-h6 font-weight-bold">
                  {{ item.name || "Usuário sem nome" }}
                </div>
                <div class="text-body-2 text-medium-emphasis">
                  {{ item.email }}
                </div>
              </div>
              <ActionsMenu
                :item="item"
                :show-delete="false"
                :custom-actions="professionalActions"
                view-permission="companies.show"
                edit-permission="companies.edit"
                @view="view"
                @edit="edit"
                @action="handleProfessionalAction"
              />
            </div>
          </v-card-title>

          <!-- Card Content -->
          <v-card-text class="user-card-content">
            <!-- Profile Badge -->
            <div v-if="item.profile" class="profile-badge mb-4">
              <v-chip
                :color="getProfileColor(item.profile.name)"
                variant="tonal"
                size="small"
              >
                <v-icon start size="14">{{
                  getProfileIcon(item.profile.name)
                }}</v-icon>
                {{ item.profile.display_name || item.profile.name }}
              </v-chip>
            </div>

            <!-- User Stats -->
            <div class="user-stats">
              <div class="stat-item">
                <v-icon size="18" color="primary" class="mr-2"
                  >mdi-phone</v-icon
                >
                <span class="text-body-2">
                  <strong class="text-high-emphasis">Telefone: </strong>
                  {{ item.phone ? formatPhone(item.phone) : "Não informado" }}
                  <v-chip
                    v-if="item.phone && item.has_whatsapp"
                    color="success"
                    size="x-small"
                    class="ml-1"
                  >
                    <v-icon start size="10">mdi-whatsapp</v-icon>
                    WhatsApp
                  </v-chip>
                </span>
              </div>
              <div class="stat-item">
                <v-icon size="18" color="info" class="mr-2"
                  >mdi-calendar</v-icon
                >
                <span class="text-body-2">
                  <strong class="text-high-emphasis">Associado em: </strong>
                  {{ formatDate(item.pivot?.created_at || item.created_at) }}
                </span>
              </div>
            </div>
          </v-card-text>

          <!-- Card Actions -->
          <v-card-actions class="user-card-actions">
            <BtnView
              @click="view(item)"
              v-if="hasPermission('companies.show')"
            />
            <v-spacer />
            <BtnEdit
              @click="edit(item)"
              v-if="hasPermission('companies.edit')"
            />
            <v-btn
              rounded="lg"
              color="error"
              variant="outlined"
              size="small"
              prepend-icon="mdi-link-off"
              @click="removeAssociation(item)"
              v-if="hasPermission('companies.detach_professional')"
            >
              Desassociar
            </v-btn>
          </v-card-actions>
        </v-card>
      </div>

      <!-- Pagination -->
      <div v-if="companyUsers.length > 0" class="users-pagination mt-6">
        <div class="pagination-controls-container">
          <!-- Pagination component (centered) -->
          <v-pagination
            v-model="pagination.current_page"
            :length="pagination.last_page"
            :total-visible="$vuetify.display.mobile ? 3 : 7"
            @update:model-value="handlePageChange"
            class="pagination-controls"
          />

          <!-- Items per page selector (right side) -->
          <div class="items-per-page-selector">
            <span class="text-body-2 text-medium-emphasis mr-3"
              >Itens por página:</span
            >
            <v-select
              v-model="pagination.per_page"
              :items="[6, 12, 24, 48]"
              density="compact"
              variant="outlined"
              hide-details
              class="items-per-page-select"
              @update:model-value="handlePerPageChange"
            />
          </div>
        </div>

        <!-- Pagination info -->
        <div class="pagination-info">
          <span class="text-body-2 text-medium-emphasis">
            Mostrando
            {{ (pagination.current_page - 1) * pagination.per_page + 1 }} a
            {{
              Math.min(
                pagination.current_page * pagination.per_page,
                pagination.total
              )
            }}
            de {{ pagination.total }} profissionais
          </span>
        </div>
      </div>
    </template>
  </BasePage>

  <!-- Modals -->
  <AddUserToCompanyModal
    v-model="showAddUserModal"
    :company="company"
    @reload="handleListReload"
  />

  <UserViewModal
    v-model="showViewModal"
    :user="selectedUser"
    @edit="handleEditFromView"
    @reload="handleListReload"
  />

  <UserModal
    v-model="showUserModal"
    :user="selectedUser"
    @reload="handleListReload"
  />

  <DetachProfessionalModal
    v-model="showDetachModal"
    :user="selectedUser"
    @confirm="handleDetachConfirm"
  />
</template>

<script lang="ts" setup>
import { ref, onMounted, computed } from "vue";
import { useRoute } from "vue-router";
import BasePage from "@/components/BasePage.vue";
import ActionBar from "@/components/ActionBar.vue";
import FiltersCard from "@/components/FiltersCard.vue";
import ExportActions from "@/components/ExportActions.vue";
import ActionsMenu, { type CustomAction } from "@/components/ActionsMenu.vue";
import AddUserToCompanyModal from "./AddUserToCompanyModal.vue";
import UserViewModal from "@/pages/users/components/UserViewModal.vue";
import UserModal from "@/pages/users/components/UserModal.vue";
import DetachProfessionalModal from "./DetachProfessionalModal.vue";
import { useCompaniesApi } from "@/pages/companies/api";
import { useAbilities } from "@/composables/useAbilities";
import { useMask } from "@/composables/useMask";
import { useProfileUtils } from "@/composables/useProfileUtils";
import { showSuccessToast, showErrorToast } from "@/utils/swal";
import {
  BtnNew,
  BtnSearch,
  BtnFilter,
  BtnView,
  BtnEdit,
} from "@/components/buttons";

const route = useRoute();
const { hasPermission } = useAbilities();

// Ações customizadas do menu
const professionalActions: CustomAction[] = [
  {
    key: "detach",
    title: "Desassociar",
    subtitle: "Remover vínculo",
    icon: "mdi-link-off",
    class: "delete-action",
    permission: "companies.detach_professional",
  },
];

// Get company ID from route
const companyId = computed(() => route.params.id as string);

onMounted(async () => {
  await loadCompany();
  await loadCompanyUsers();
});

// Composables
const {
  getById: getCompany,
  getCompanyUsers,
  detachProfessional,
} = useCompaniesApi();
const { getProfileColor, getProfileIcon } = useProfileUtils();

// State
const company = ref<any>(null);
const companyUsers = ref<any[]>([]);
const loading = ref(false);
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 12,
  total: 0,
});

// Modal states
const showAddUserModal = ref(false);
const showViewModal = ref(false);
const showUserModal = ref(false);
const showDetachModal = ref(false);
const selectedUser = ref<any>(null);
const isEditing = ref(false);

// Profiles for filter

// Reactive data
const searchQuery = ref("");

// Methods
const loadCompany = async () => {
  try {
    const response = await getCompany(parseInt(companyId.value));
    company.value = response;
  } catch (err) {
    console.error("Erro ao carregar empresa:", err);
    showErrorToast("Erro ao carregar dados da empresa", "Erro!");
  }
};

const loadCompanyUsers = async () => {
  try {
    loading.value = true;

    if (!company.value?.id) {
      companyUsers.value = [];
      pagination.value = {
        current_page: 1,
        last_page: 1,
        per_page: 12,
        total: 0,
      };
      return;
    }

    const result = await getCompanyUsers(
      company.value.id,
      searchQuery.value,
      pagination.value.current_page,
      pagination.value.per_page
    );

    companyUsers.value = result.data || [];
    pagination.value = {
      current_page: result.current_page || 1,
      last_page: result.last_page || 1,
      per_page: result.per_page || 12,
      total: result.total || 0,
    };
  } catch (err) {
    console.error("Erro ao carregar usuários da empresa:", err);
  } finally {
    loading.value = false;
  }
};

// Funções de navegação específicas da página
const view = (item: any) => {
  selectedUser.value = item;
  showViewModal.value = true;
};

const edit = (item: any) => {
  selectedUser.value = item;
  isEditing.value = true;
  showUserModal.value = true;
};

const removeAssociation = (item: any) => {
  selectedUser.value = item;
  showDetachModal.value = true;
};

const handleProfessionalAction = (key: string, item: any) => {
  if (key === "detach") {
    removeAssociation(item);
  }
};

const handleDetachProfessional = async (user: any) => {
  try {
    loading.value = true;

    await detachProfessional(company.value.id, user.id);

    showSuccessToast(
      `${user.name} foi desassociado com sucesso da empresa!`,
      "Sucesso!"
    );

    // Recarregar lista
    await loadCompanyUsers();
  } catch (error: any) {
    console.error("Erro ao desassociar profissional:", error);
    showErrorToast(
      error.response?.data?.message || "Erro ao desassociar profissional",
      "Erro!"
    );
  } finally {
    loading.value = false;
  }
};

// Funções dos modals
const handleListReload = async () => {
  await loadCompanyUsers();
};

const handleDetachConfirm = async (user: any) => {
  await handleDetachProfessional(user);
};

const handleEditFromView = (user: any) => {
  selectedUser.value = user;
  isEditing.value = true;
  showUserModal.value = true;
};

// Utility methods
const { formatPhone } = useMask();

const formatDate = (dateString: string) => {
  if (!dateString) return "Data não informada";

  try {
    const date = new Date(dateString);
    return date.toLocaleDateString("pt-BR", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
    });
  } catch (error) {
    return dateString;
  }
};

const performSearch = async () => {
  try {
    // TODO: Implementar busca com filtros
    await loadCompanyUsers();
  } catch (err) {
    console.error("Erro ao realizar busca:", err);
  }
};

const clearFilters = async () => {
  searchQuery.value = "";
  await loadCompanyUsers();
};

// Pagination handlers
const handlePageChange = async (page: number) => {
  try {
    pagination.value.current_page = page;
    await loadCompanyUsers();
  } catch (err) {
    console.error("Erro ao alterar página:", err);
  }
};

const handlePerPageChange = async (perPage: number) => {
  try {
    pagination.value.per_page = perPage;
    pagination.value.current_page = 1;
    await loadCompanyUsers();
  } catch (err) {
    console.error("Erro ao alterar itens por página:", err);
  }
};
</script>

<style scoped>
/* Estilos específicos da página de usuários da empresa */

/* Filters Header */
.filters-header {
  border-bottom: 1px solid rgba(var(--v-theme-outline), 0.12);
  padding-bottom: 16px;
}

.filters-header h3 {
  color: rgba(var(--v-theme-on-surface), 0.87);
  margin-bottom: 4px;
}

.filters-header p {
  color: rgba(var(--v-theme-on-surface), 0.6);
  line-height: 1.4;
}

.filter-toggle-btn {
  transition: all 0.2s ease;
}

.filter-toggle-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Loading State */
.users-loading {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* Empty State */
.users-empty {
  text-align: center;
  padding: 64px 32px;
  color: rgba(var(--v-theme-on-surface), 0.6);
}

/* Users Grid */
.users-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 24px;
  margin-bottom: 32px;
}

/* User Card */
.user-card {
  border-radius: 16px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden;
}

.user-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
}

.user-card-header {
  background: linear-gradient(
    135deg,
    rgba(var(--v-theme-primary), 0.05),
    rgba(var(--v-theme-primary), 0.02)
  );
  padding: 20px 24px;
  border-bottom: 1px solid rgba(var(--v-theme-outline), 0.12);
}

.user-card-content {
  padding: 20px 24px;
}

.profile-badge {
  display: flex;
  justify-content: flex-start;
}

.user-stats {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.stat-item {
  display: flex;
  align-items: center;
  padding: 8px 0;
}

.user-card-actions {
  padding: 16px 24px;
  background: rgba(var(--v-theme-surface-variant), 0.3);
  border-top: 1px solid rgba(var(--v-theme-outline), 0.12);
}

.action-button {
  border-radius: 8px;
  transition: all 0.2s ease;
}

.action-button:hover {
  transform: translateY(-1px);
}

/* Pagination */
.users-pagination {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 20px;
}

.pagination-controls-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  max-width: 800px;
  gap: 20px;
}

.items-per-page-selector {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}

.items-per-page-select {
  min-width: 80px;
  max-width: 120px;
}

.pagination-controls {
  flex: 1;
  display: flex;
  justify-content: center;
  order: 1;
}

.items-per-page-selector {
  order: 2;
}

.pagination-info {
  text-align: center;
  margin-top: 8px;
}

/* Responsividade */
@media (max-width: 768px) {
  .users-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }

  .pagination-controls-container {
    flex-direction: column;
    gap: 16px;
  }

  .pagination-controls {
    order: 1;
    width: 100%;
  }

  .items-per-page-selector {
    order: 2;
    justify-content: center;
  }

  .user-card-header {
    padding: 16px 20px;
  }

  .user-card-content {
    padding: 16px 20px;
  }

  .user-card-actions {
    padding: 12px 20px;
    flex-direction: column;
    gap: 8px;
  }

  .user-card-actions .v-btn {
    width: 100%;
  }

  .action-buttons-container {
    flex-direction: column;
    gap: 8px;
    width: 100%;
  }

  .action-buttons-container .v-btn,
  .action-buttons-container .export-actions {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .users-grid {
    gap: 12px;
  }

  .user-card-header {
    padding: 12px 16px;
  }

  .user-card-content {
    padding: 12px 16px;
  }

  .user-card-actions {
    padding: 8px 16px;
  }
}
</style>
