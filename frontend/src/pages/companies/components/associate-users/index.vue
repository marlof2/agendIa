<template>
  <BasePage
    :title="`Usuários da Empresa - ${company?.name || 'Carregando...'}`"
    subtitle="Gerencie os usuários associados a esta empresa"
    :breadcrumbs="[
      { title: 'Empresas', to: '/companies' },
      { title: 'Usuários' },
    ]"
  >
    <!-- Action Bar -->
    <template #actionBar>
      <ActionBar>
        <template #left>
          <BtnNew
            v-if="hasPermission('companies.users.attach')"
            text="Associar Usuário"
            @click="showAssociateModal = true"
          />
        </template>
        <template #right>
          <div class="d-flex action-buttons-container">
            <ExportActions
              :data="companyUsers"
              filename="usuarios-empresa"
              button-text="Exportar"
              color="primary"
              variant="outlined"
              prepend-icon="mdi-download"
              :endpoint="`/companies/${companyId}/users`"
              :filters="{ search: searchQuery || undefined, profile_id: selectedProfileId || undefined }"
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
            <v-col cols="12" md="4">
              <v-text-field
                v-model="searchQuery"
                label="Buscar por nome ou email"
                prepend-inner-icon="mdi-magnify"
                variant="outlined"
                density="compact"
                clearable
                rounded="lg"
                hide-details
                @keyup.enter="performSearch"
              />
            </v-col>
            <v-col cols="12" md="4">
              <v-select
                v-model="selectedProfileId"
                :items="profileOptions"
                item-title="text"
                item-value="value"
                label="Filtrar por perfil"
                variant="outlined"
                density="compact"
                clearable
                rounded="lg"
                hide-details
                @update:model-value="performSearch"
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
      <div v-if="loading" class="text-center py-12">
        <v-progress-circular indeterminate color="primary" size="64" />
        <p class="text-h6 mt-4">Carregando usuários...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="text-center py-12">
        <v-icon size="64" color="error">mdi-alert-circle</v-icon>
        <p class="text-h6 mt-4 text-error">{{ error }}</p>
        <v-btn color="primary" @click="loadCompanyUsers" class="mt-4">
          Tentar Novamente
        </v-btn>
      </div>

      <!-- Empty State -->
      <div v-else-if="isEmpty" class="text-center py-12">
        <v-icon size="64" color="grey-lighten-1">mdi-account-multiple-outline</v-icon>
        <p class="text-h6 mt-4">Nenhum usuário encontrado</p>
        <p class="text-body-2 text-medium-emphasis mb-6">
          Não há usuários associados a esta empresa ou os filtros não retornaram resultados
        </p>
        <v-btn
          v-if="hasPermission('companies.users.attach')"
          color="primary"
          @click="showAssociateModal = true"
        >
          Associar Primeiro Usuário
        </v-btn>
      </div>

      <!-- Users Grid -->
      <div v-else class="users-grid">
        <v-card
          v-for="user in companyUsers"
          :key="user.id"
          class="user-card"
          elevation="2"
          hover
        >
          <!-- Card Header -->
          <v-card-title class="user-card-header">
            <div class="d-flex align-center">
              <div class="flex-grow-1">
                <div class="text-h6 font-weight-bold mb-1">
                  {{ user.name || "Usuário não informado" }}
                </div>
                <div class="text-body-2 text-medium-emphasis">
                  <v-chip
                    v-if="getUserProfile(user)"
                    :color="getProfileColor(getUserProfile(user).name)"
                    variant="outlined"
                    size="small"
                    class="profile-chip"
                  >
                    <v-icon start size="14">{{ getProfileIcon(getUserProfile(user).name) }}</v-icon>
                    <span class="chip-label">Perfil:</span>
                    <span class="chip-value">{{ getUserProfile(user).display_name || getUserProfile(user).name }}</span>
                  </v-chip>
                </div>
              </div>
              <ActionsMenu
                :item="user"
                view-permission="companies.show"
                :show-delete="false"
                :show-edit="false"
                :custom-actions="getUserActions(user)"
                @view="view"
              />
            </div>
          </v-card-title>

          <!-- Card Content -->
          <v-card-text class="user-card-content">
            <!-- User Info - Simplified -->
            <div class="user-info">
              <div class="info-item mb-3">
                <v-icon size="18" color="primary" class="mr-2"
                  >mdi-email</v-icon
                >
                <span class="text-body-2">
                  <strong class="text-high-emphasis">E-mail: </strong
                  >{{ user.email }}
                </span>
              </div>

              <div class="info-item mb-3">
                <v-icon size="18" color="primary" class="mr-2"
                  >mdi-phone</v-icon
                >
                <span class="text-body-2">
                  <strong class="text-high-emphasis">Telefone: </strong
                  >{{ user.phone ? formatPhone(user.phone) : 'Não informado' }}
                  <v-chip
                    v-if="user.phone && user.has_whatsapp"
                    color="success"
                    size="x-small"
                    class="ml-1"
                  >
                    <v-icon start size="10">mdi-whatsapp</v-icon>
                    WhatsApp
                  </v-chip>
                </span>
              </div>

              <div class="info-item">
                <v-icon size="18" color="primary" class="mr-2"
                  >mdi-calendar</v-icon
                >
                <span class="text-body-2">
                  <strong class="text-high-emphasis">Cadastrado em: </strong
                  >{{ formatDate(user.created_at) }}
                </span>
              </div>
            </div>
          </v-card-text>

          <!-- Card Actions -->
          <v-card-actions class="user-card-actions">
            <BtnView
              @click="view(user)"
              v-if="hasPermission('companies.users.show')"
            />
            <Btn
              v-if="hasPermission('companies.users.update_profile')"
              text="ALTERAR PERFIL"
              icon="mdi-account-edit"
              variant="outlined"
              color="warning"
              @click="edit(user)"
            />
            <Btn
              v-if="hasPermission('companies.users.detach')"
              text="DESASSOCIAR"
              icon="mdi-link-off"
              variant="outlined"
              color="error"
              @click="remove(user)"
            />
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
            de {{ pagination.total }} usuário(s)
          </span>
        </div>
      </div>
    </template>
  </BasePage>

  <!-- Modals -->
  <AssociateUserModal
    v-model="showAssociateModal"
    :company="company"
    @reload="handleListReload"
  />

  <UserViewModal
    v-model="showViewModal"
    :user="selectedUser"
    @reload="handleListReload"
    disable-edit
  />

  <EditUserProfileModal
    v-model="showEditProfileModal"
    :user="selectedUser"
    :company="company"
    @reload="handleListReload"
  />

  <DetachUserModal
    v-model="showDetachModal"
    :user="selectedUser"
    :company="company"
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
import AssociateUserModal from "@/pages/companies/components/associate-users/AssociateUserModal.vue";
import UserViewModal from "@/pages/users/components/UserViewModal.vue";
import EditUserProfileModal from "@/pages/companies/components/associate-users/EditUserProfileModal.vue";
import DetachUserModal from "@/pages/companies/components/associate-users/DetachUserModal.vue";
import { useCompaniesApi } from "@/pages/companies/api";
import { useProfilesApi } from "@/pages/profiles/api";
import { useAbilities } from "@/composables/useAbilities";
import { useMask } from "@/composables/useMask";
import { useProfileUtils } from "@/composables/useProfileUtils";
import { showSuccessToast, showErrorToast } from "@/utils/swal";
import Btn from "@/components/buttons/Btn.vue";
import {
  BtnNew,
  BtnSearch,
  BtnFilter,
} from "@/components/buttons";

const route = useRoute();
const { hasPermission } = useAbilities();
const { formatPhone } = useMask();
const { getProfileColor, getProfileIcon } = useProfileUtils();

// Get company ID from route
const companyId = computed(() => route.params.id as string);

onMounted(async () => {
  await loadCompany();
  await loadCompanyUsers();
  await loadProfiles();
});

// Composables
const {
  getById: getCompany,
  getCompanyUsers,
  detachUserFromCompany,
} = useCompaniesApi();

const {
  getCombo: getProfilesCombo,
} = useProfilesApi();

// State
const company = ref<any>(null);
const companyUsers = ref<any[]>([]);
const loading = ref(false);
const error = ref<string | null>(null);
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 12,
  total: 0,
});

// Modal states
const showAssociateModal = ref(false);
const showViewModal = ref(false);
const showEditProfileModal = ref(false);
const showDetachModal = ref(false);
const selectedUser = ref<any>(null);

// Filters
const searchQuery = ref("");
const selectedProfileId = ref<number | null>(null);
const profiles = ref<any[]>([]);

// Profile options for filter
const profileOptions = computed(() => [
  { text: "Todos os perfis", value: null },
  ...profiles.value.map(profile => ({
    text: profile.display_name || profile.name,
    value: profile.id
  }))
]);

// Ações customizadas do menu baseadas no usuário
const getUserActions = (user: any) => {
  const actions = [];

  // Ação de alterar perfil
  actions.push({
    key: "edit_profile",
    title: "Alterar Perfil",
    subtitle: "Modificar perfil na empresa",
    icon: "mdi-account-edit",
    class: "edit-action",
    permission: "companies.users.update_profile",
    onClick: () => edit(user)
  });

  // Ação de desassociar
  actions.push({
    key: "detach",
    title: "Desassociar",
    subtitle: "Remover vínculo",
    icon: "mdi-link-off",
    class: "delete-action",
    permission: "companies.users.detach",
    onClick: () => remove(user)
  });

  return actions;
};

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
    error.value = null;

    const result = await getCompanyUsers(
      parseInt(companyId.value),
      searchQuery.value,
      selectedProfileId.value || undefined,
      pagination.value.current_page,
      pagination.value.per_page
    );

    companyUsers.value = result.data || [];
    pagination.value = {
      current_page: result.current_page || 1,
      per_page: result.per_page || 12,
      total: result.total || 0,
      last_page: result.last_page || 1,
    };
  } catch (err) {
    console.error("Erro ao carregar usuários:", err);
    error.value = "Erro ao carregar usuários da empresa";
    showErrorToast("Erro ao carregar usuários", "Erro!");
  } finally {
    loading.value = false;
  }
};

const loadProfiles = async () => {
  try {
    const result = await getProfilesCombo();
    profiles.value = result || [];
  } catch (err) {
    console.error("Erro ao carregar perfis:", err);
  }
};

const performSearch = () => {
  pagination.value.current_page = 1;
  loadCompanyUsers();
};

const clearFilters = () => {
  searchQuery.value = "";
  selectedProfileId.value = null;
  performSearch();
};

const handlePageChange = (page: number) => {
  pagination.value.current_page = page;
  loadCompanyUsers();
};

const handlePerPageChange = async (perPage: number) => {
  try {
    pagination.value.per_page = perPage;
    pagination.value.current_page = 1; // Reset to first page when changing items per page
    await loadCompanyUsers();
  } catch (err) {
    console.error("Erro ao alterar itens por página:", err);
  }
};

const handleListReload = () => {
  loadCompanyUsers();
  showAssociateModal.value = false;
  showViewModal.value = false;
  showEditProfileModal.value = false;
  showDetachModal.value = false;
};

// User actions
const view = (user: any) => {
  selectedUser.value = user;
  showViewModal.value = true;
};

const edit = (user: any) => {
  selectedUser.value = user;
  showEditProfileModal.value = true;
};

const remove = (user: any) => {
  selectedUser.value = user;
  showDetachModal.value = true;
};

const handleDetachConfirm = async () => {
  if (!selectedUser.value) return;

  try {
    await detachUserFromCompany(
      parseInt(companyId.value),
      selectedUser.value.id
    );

    showSuccessToast("Usuário desassociado com sucesso!", "Sucesso!");
    handleListReload();
  } catch (error: any) {
    showErrorToast(
      error.response?.data?.message || "Erro ao desassociar usuário",
      "Erro!"
    );
  }
};

// Helper function to get user profile from company relationship
const getUserProfile = (user: any) => {
  if (!user.companies || user.companies.length === 0) return null;

  const companyRelation = user.companies.find((c: any) => c.id === parseInt(companyId.value));
  return companyRelation?.pivot?.profile || null;
};

// Helper function to format date
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

// Computed properties
const isEmpty = computed(() => !loading.value && companyUsers.value.length === 0);
</script>

<style scoped>
/* Estilos específicos da página de usuários - copiados exatamente da tela de empresas */

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

.user-info {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.info-item {
  display: flex;
  align-items: center;
  padding: 4px 0;
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

/* Estilos para chips de perfil */
.profile-chip {
  font-weight: 500;
}

.profile-chip .chip-label {
  font-weight: 600;
  margin-right: 4px;
}

.profile-chip .chip-value {
  font-family: "Roboto Mono", monospace;
  font-weight: 500;
  letter-spacing: 0.5px;
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

/* Desktop styles */
.action-buttons-container {
  gap: 16px;
}

/* Responsividade para chips */
@media (max-width: 600px) {
  .profile-chip {
    font-size: 0.75rem;
  }

  .profile-chip .v-icon {
    font-size: 12px !important;
  }
}
</style>
