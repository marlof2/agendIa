<template>
  <BasePage
    title="Empresas"
    subtitle="Gerencie todas as empresas do sistema"
    :breadcrumbs="[{ title: 'Empresas' }]"
  >
    <!-- Action Bar -->
    <template #actionBar>
      <ActionBar>
        <template #left>
          <BtnNew v-if="hasPermission('companies.create')" @click="create" />
        </template>
        <template #right>
          <div class="d-flex action-buttons-container">
            <ExportActions
              :data="companies"
              filename="empresas"
              button-text="Exportar"
              color="primary"
              variant="outlined"
              prepend-icon="mdi-download"
              endpoint="/companies"
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
                label="Buscar nome, CNPJ ou telefone"
                prepend-inner-icon="mdi-magnify"
                variant="outlined"
                density="compact"
                clearable
                rounded="lg"
                hide-details
                @keyup.enter="performSearch"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-select
                v-model="statusFilter"
                :items="statusOptions"
                label="Status"
                prepend-inner-icon="mdi-toggle-switch"
                variant="outlined"
                density="compact"
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
      <div v-if="loading" class="companies-loading">
        <v-skeleton-loader v-for="i in 5" :key="i" type="card" class="mb-4" />
      </div>

      <!-- Empty State -->
      <div v-else-if="companies.length === 0" class="companies-empty">
        <v-icon size="64" color="grey-lighten-1">mdi-domain-off</v-icon>
        <p class="text-h6 mt-4">Nenhuma empresa encontrada</p>
        <p class="text-body-2 text-medium-emphasis">
          Crie sua primeira empresa para começar
        </p>
      </div>

      <!-- Companies Grid -->
      <div v-else class="companies-grid">
        <v-card
          v-for="item in companies"
          :key="item.id"
          class="company-card"
          elevation="2"
          hover
        >
          <!-- Card Header -->
          <v-card-title class="company-card-header">
            <div class="d-flex align-center">
              <div class="flex-grow-1">
                <div class="text-h6 font-weight-bold mb-1">
                  {{ item.name || "Empresa não informada" }}
                </div>
                <div class="text-body-2 text-medium-emphasis">
                  <template v-if="item.person_type === 'legal'">
                    <v-chip
                      color="primary"
                      variant="outlined"
                      size="small"
                      class="document-chip"
                    >
                      <v-icon start size="14">mdi-domain</v-icon>
                      <span class="chip-label">CNPJ:</span>
                      <span class="chip-value">{{
                        formatCNPJ(item?.cnpj || "")
                      }}</span>
                    </v-chip>
                  </template>
                  <template v-else>
                    <v-chip
                      color="primary"
                      variant="outlined"
                      size="small"
                      class="document-chip"
                    >
                      <v-icon start size="14">mdi-account</v-icon>
                      <span class="chip-label">CPF:</span>
                      <span class="chip-value">{{
                        formatCPF(item?.cpf || "")
                      }}</span>
                    </v-chip>
                  </template>
                </div>
              </div>
              <ActionsMenu
                :item="item"
                view-permission="companies.show"
                edit-permission="companies.edit"
                :show-delete="false"
                :custom-actions="getCustomActions(item)"
                @view="view"
                @edit="edit"
              />
            </div>
          </v-card-title>

          <!-- Card Content -->
          <v-card-text class="company-card-content">
            <!-- Company Info - Simplified -->
            <div class="company-info">
              <div class="info-item mb-3">
                <v-icon size="18" color="primary" class="mr-2"
                  >mdi-account-group</v-icon
                >
                <span class="text-body-2">
                  <strong class="text-high-emphasis">Tipo: </strong>
                  {{
                    item.person_type === "legal"
                      ? "Pessoa Jurídica"
                      : "Pessoa Física"
                  }}
                </span>
              </div>

              <div class="info-item mb-3">
                <v-icon size="18" color="primary" class="mr-2"
                  >mdi-account</v-icon
                >
                <span class="text-body-2">
                  <strong class="text-high-emphasis">Responsável: </strong
                  >{{ item.responsible_name }}
                </span>
              </div>

              <div class="info-item mb-3">
                <v-icon size="18" color="primary" class="mr-2"
                  >mdi-phone</v-icon
                >
                <span class="text-body-2">
                  <strong class="text-high-emphasis">Telefone: </strong
                  >{{ formatPhone(item.phone_1) }}
                  <v-chip
                    v-if="item.has_whatsapp_1"
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
                  >mdi-toggle-switch</v-icon
                >
                <span class="text-body-2">
                  <strong class="text-high-emphasis">Status: </strong>
                </span>
                <v-chip
                  :color="item.deleted_at ? 'error' : 'success'"
                  :text="item.deleted_at ? 'Inativa' : 'Ativa'"
                  size="x-small"
                  variant="outlined"
                  class="ml-1"
                />
              </div>
            </div>
          </v-card-text>

          <!-- Card Actions -->
          <v-card-actions class="company-card-actions">
            <BtnView
              @click="view(item)"
              v-if="hasPermission('companies.show')"
            />
            <Btn
              v-if="hasPermission('companies.attach_professional')"
              text="GERENCIAR PROFISSIONAIS"
              icon="mdi-account-multiple-outline"
              variant="outlined"
              color="info"
              @click="manageUsers(item)"
            />
          </v-card-actions>
        </v-card>
      </div>

      <!-- Pagination -->
      <div v-if="companies.length > 0" class="companies-pagination mt-6">
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
            de {{ pagination.total }} empresas
          </span>
        </div>
      </div>
    </template>
  </BasePage>

  <!-- Modals -->
  <CompanyModal
    v-model="showCompanyModal"
    :company="selectedCompany"
    @reload="handleListReload"
  />

  <CompanyViewModal
    v-model="showViewModal"
    :company="selectedCompany"
    @edit="handleEditFromView"
    @reload="handleListReload"
  />

  <ActivateConfirmModal
    v-model="showActivateModal"
    :item="selectedCompany"
    @confirm="handleActivateConfirm"
  />

  <DeactivateConfirmModal
    v-model="showDeactivateModal"
    :item="selectedCompany"
    @confirm="handleDeactivateConfirm"
  />
</template>

<script lang="ts" setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import BasePage from "@/components/BasePage.vue";
import ActionBar from "@/components/ActionBar.vue";
import FiltersCard from "@/components/FiltersCard.vue";
import ExportActions from "@/components/ExportActions.vue";
import ActionsMenu from "@/components/ActionsMenu.vue";
import CompanyModal from "./components/CompanyModal.vue";
import CompanyViewModal from "./components/CompanyViewModal.vue";
import ActivateConfirmModal from "./components/ActivateConfirmModal.vue";
import DeactivateConfirmModal from "./components/DeactivateConfirmModal.vue";
import { useCompaniesApi } from "./api";
import { useAbilities } from "@/composables/useAbilities";
import { useMask } from "@/composables/useMask";
import { showSuccessToast, showErrorToast } from "@/utils/swal";

const { hasPermission } = useAbilities();
const router = useRouter();

onMounted(async () => {
  await loadCompanies();
});

// Composables
const {
  items: companies,
  loading,
  error,
  pagination,
  getAll,
  deactivateItem,
  activateItem,
} = useCompaniesApi();

// Funções de navegação específicas da página
const view = (item: any) => {
  selectedCompany.value = item;
  showViewModal.value = true;
};

const edit = (item: any) => {
  selectedCompany.value = item;
  isEditing.value = true;
  showCompanyModal.value = true;
};

const manageUsers = (item: any) => {
  // Navegar para a página de gerenciamento de usuários da empresa
  router.push(`/companies/${item.id}/bind-professional`);
};

const handleDeactivate = (item: any) => {
  selectedCompany.value = item;
  showDeactivateModal.value = true;
};

const handleActivate = (item: any) => {
  selectedCompany.value = item;
  showActivateModal.value = true;
};

const handleDeactivateConfirm = async (item: any) => {
  try {
    await deactivateItem(item.id);
    showDeactivateModal.value = false;
    showSuccessToast("Empresa inativada com sucesso!", "Sucesso!");
    await performSearch(); // Recarregar com os filtros atuais
  } catch (err: any) {
    const errorMessage =
      err?.response?.data?.message || err?.message || "Erro ao inativar empresa";
    showErrorToast(errorMessage, "Erro!");
  }
};

const handleActivateConfirm = async (item: any) => {
  try {
    await activateItem(item.id);
    showActivateModal.value = false;
    showSuccessToast("Empresa ativada com sucesso!", "Sucesso!");
    await performSearch(); // Recarregar com os filtros atuais
  } catch (err: any) {
    const errorMessage =
      err?.response?.data?.message || err?.message || "Erro ao ativar empresa";
    showErrorToast(errorMessage, "Erro!");
  }
};

const getCustomActions = (item: any) => {
  const actions = [];

  if (item.deleted_at) {
    // Empresa inativa - mostrar botão de ativar
    actions.push({
      key: 'activate',
      icon: 'mdi-check-circle-outline',
      color: 'green',
      title: 'Ativar',
      subtitle: 'Ativar empresa',
      permission: 'companies.edit',
      onClick: () => handleActivate(item)
    });
  } else {
    // Empresa ativa - mostrar botão de inativar
    actions.push({
      key: 'deactivate',
      icon: 'mdi-cancel',
      color: 'red',
      title: 'Inativar',
      subtitle: 'Inativar empresa',
      permission: 'companies.edit',
      onClick: () => handleDeactivate(item)
    });
  }

  return actions;
};

const loadCompanies = async () => {
  try {
    await getAll({ status: statusFilter.value });
  } catch (err) {
    console.error("Erro ao carregar empresas:", err);
  }
};

const create = () => {
  selectedCompany.value = null;
  isEditing.value = false;
  showCompanyModal.value = true;
};

// Funções dos modais
const handleListReload = async () => {
  await getAll();
};

const handleEditFromView = (company: any) => {
  selectedCompany.value = company;
  isEditing.value = true;
  showCompanyModal.value = true;
};

// Reactive data
const searchQuery = ref("");
const statusFilter = ref<'active' | 'inactive' | 'all'>('active');

// Status options
const statusOptions = [
  { title: 'Ativas', value: 'active' },
  { title: 'Inativas', value: 'inactive' },
  { title: 'Todas', value: 'all' }
];

// Modal states
const showCompanyModal = ref(false);
const showViewModal = ref(false);
const showActivateModal = ref(false);
const showDeactivateModal = ref(false);
const selectedCompany = ref<any>(null);
const isEditing = ref(false);

// Methods
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

// Funções de formatação
const { formatCNPJ, formatCPF, formatPhone } = useMask();

const performSearch = async () => {
  try {
    const filters: any = {};

    if (searchQuery.value) {
      filters.search = searchQuery.value;
    }

    if (statusFilter.value) {
      filters.status = statusFilter.value;
    }

    // Reset to first page when searching
    filters.page = 1;

    await getAll(filters);
  } catch (err) {
    console.error("Erro ao realizar busca:", err);
  }
};

const clearFilters = async () => {
  searchQuery.value = "";
  statusFilter.value = 'active';

  // Reset to first page and reload all companies
  await getAll({ page: 1, status: 'active' });
};

// Pagination handlers
const handlePageChange = async (page: number) => {
  try {
    const filters: any = { page };

    if (searchQuery.value) {
      filters.search = searchQuery.value;
    }

    if (statusFilter.value) {
      filters.status = statusFilter.value;
    }

    await getAll(filters);
  } catch (err) {
    console.error("Erro ao alterar página:", err);
  }
};

const handlePerPageChange = async (perPage: number) => {
  try {
    const filters: any = {
      page: 1, // Reset to first page when changing items per page
      per_page: perPage,
    };

    if (searchQuery.value) {
      filters.search = searchQuery.value;
    }

    if (statusFilter.value) {
      filters.status = statusFilter.value;
    }

    await getAll(filters);
  } catch (err) {
    console.error("Erro ao alterar itens por página:", err);
  }
};
</script>

<style scoped>
/* Estilos específicos da página de empresas */

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
.companies-loading {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* Empty State */
.companies-empty {
  text-align: center;
  padding: 64px 32px;
  color: rgba(var(--v-theme-on-surface), 0.6);
}

/* Companies Grid */
.companies-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 24px;
  margin-bottom: 32px;
}

/* Company Card */
.company-card {
  border-radius: 16px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden;
}

.company-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
}

.company-card-header {
  background: linear-gradient(
    135deg,
    rgba(var(--v-theme-primary), 0.05),
    rgba(var(--v-theme-primary), 0.02)
  );
  padding: 20px 24px;
  border-bottom: 1px solid rgba(var(--v-theme-outline), 0.12);
}

.company-card-content {
  padding: 20px 24px;
}

.company-info {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.info-item {
  display: flex;
  align-items: center;
  padding: 4px 0;
}

.company-stats {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.stat-item {
  display: flex;
  align-items: center;
  padding: 8px 0;
}

.company-card-actions {
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
.companies-pagination {
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
  .companies-grid {
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

  .company-card-header {
    padding: 16px 20px;
  }

  .company-card-content {
    padding: 16px 20px;
  }

  .company-card-actions {
    padding: 12px 20px;
    flex-direction: column;
    gap: 8px;
  }

  .company-card-actions .v-btn {
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
  .companies-grid {
    gap: 12px;
  }

  .company-card-header {
    padding: 12px 16px;
  }

  .company-card-content {
    padding: 12px 16px;
  }

  .company-card-actions {
    padding: 8px 16px;
  }
}

/* Estilos para chips de documento */
.document-chip {
  font-weight: 500;
}

.document-chip .chip-label {
  font-weight: 600;
  margin-right: 4px;
}

.document-chip .chip-value {
  font-family: "Roboto Mono", monospace;
  font-weight: 500;
  letter-spacing: 0.5px;
}

/* Responsividade para chips */
@media (max-width: 600px) {
  .document-chip {
    font-size: 0.75rem;
  }

  .document-chip .v-icon {
    font-size: 12px !important;
  }
}
</style>
