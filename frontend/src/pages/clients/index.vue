<template>
  <BasePage
    title="Clientes"
    subtitle="Gerencie todos os clientes cadastrados"
    :breadcrumbs="[{ title: 'Clientes' }]"
  >
    <!-- Action Bar -->
    <template #actionBar>
      <ActionBar>
        <template #left>
          <BtnNew v-if="hasPermission('clients.create')" @click="create" />
        </template>
        <template #right>
          <div class="d-flex action-buttons-container">
            <ExportActions
              :data="clients"
              filename="clientes"
              button-text="Exportar"
              color="primary"
              variant="outlined"
              prepend-icon="mdi-download"
              endpoint="/clients"
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
                label="Buscar nome, e-mail ou telefone"
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
      <div v-if="loading" class="clients-loading">
        <v-skeleton-loader v-for="i in 6" :key="i" type="card" class="mb-4" />
      </div>

      <!-- Empty State -->
      <div v-else-if="clients.length === 0" class="clients-empty">
        <v-icon size="64" color="grey-lighten-1">mdi-account-off</v-icon>
        <p class="text-h6 mt-4">Nenhum cliente encontrado</p>
        <p class="text-body-2 text-medium-emphasis">
          Crie seu primeiro cliente para começar
        </p>
      </div>

      <!-- Clients Grid -->
      <div v-else class="clients-grid">
        <v-card
          v-for="item in clients"
          :key="item.id"
          class="client-card"
          elevation="2"
          hover
        >
          <!-- Card Header -->
          <v-card-title class="client-card-header">
            <div class="d-flex align-center">
              <div class="flex-grow-1">
                <div class="text-h6 font-weight-bold">
                  {{ item.name || "Nome não informado" }}
                </div>
                <div class="text-body-2 text-medium-emphasis">
                  {{ item.email }}
                </div>
              </div>
              <ActionsMenu
                :item="item"
                view-permission="clients.show"
                edit-permission="clients.edit"
                delete-permission="clients.delete"
                @view="view"
                @edit="edit"
                @delete="remove"
              />
            </div>
          </v-card-title>

          <!-- Card Content -->
          <v-card-text class="client-card-content">
            <div class="client-description mb-4" v-if="item.phone">
              <div class="text-body-2 text-medium-emphasis">
                <v-icon size="16" class="mr-1">mdi-phone</v-icon>
                {{ formatPhone(item.phone) }}
              </div>
            </div>

            <!-- Client Stats -->
            <div class="client-stats">
              <div class="stat-item">
                <v-icon size="18" color="primary" class="mr-2">mdi-shield-account</v-icon>
                <span class="text-body-2">
                  <strong class="text-high-emphasis">Perfil: </strong>
                  {{ item.profile?.display_name || 'Cliente' }}
                </span>
              </div>
              <div class="stat-item">
                <v-icon size="18" color="success" class="mr-2">mdi-office-building</v-icon>
                <span class="text-body-2">
                  <strong class="text-high-emphasis">Empresas: </strong>
                  {{ item.companies?.length || 0 }} {{ item.companies?.length === 1 ? 'empresa' : 'empresas' }}
                </span>
              </div>
              <div class="stat-item" v-if="item.created_at">
                <v-icon size="18" color="info" class="mr-2">mdi-calendar</v-icon>
                <span class="text-body-2">
                  <strong class="text-high-emphasis">Criado em: </strong>
                  {{ formatDate(item.created_at) }}
                </span>
              </div>
            </div>
          </v-card-text>

          <!-- Card Actions -->
          <v-card-actions class="client-card-actions">
            <BtnView @click="view(item)" v-if="hasPermission('clients.show')" />
            <v-spacer />
            <BtnEdit @click="edit(item)" v-if="hasPermission('clients.edit')" />
            <BtnDelete @click="remove(item)" v-if="hasPermission('clients.delete')" />
          </v-card-actions>
        </v-card>
      </div>

      <!-- Pagination -->
      <div v-if="clients.length > 0" class="clients-pagination mt-6">
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
            <span class="text-body-2 text-medium-emphasis mr-3">Itens por página:</span>
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
        <div class="pagination-info mt-4 text-center">
          <p class="text-body-2 text-medium-emphasis">
            Mostrando
            <span class="font-weight-medium">{{ clients.length }}</span> de
            <span class="font-weight-medium">{{ pagination.total }}</span>
            clientes
          </p>
        </div>
      </div>
    </template>
  </BasePage>

  <!-- Modals -->
  <ClientModal
    v-model="showClientModal"
    :client="selectedClient"
    @submit="handleSave"
    @close="showClientModal = false"
  />

  <ClientViewModal
    v-model="showViewModal"
    :client="selectedClient"
    @edit="handleEditFromView"
    @close="showViewModal = false"
  />

  <DeleteConfirmModal
    v-model="showDeleteModal"
    :client="selectedClient"
    @confirm="handleDelete"
  />
</template>

<script lang="ts" setup>
import { ref, onMounted } from "vue";
import BasePage from "@/components/BasePage.vue";
import ActionBar from "@/components/ActionBar.vue";
import FiltersCard from "@/components/FiltersCard.vue";
import ExportActions from "@/components/ExportActions.vue";
import ActionsMenu from "@/components/ActionsMenu.vue";
import ClientModal from "./components/ClientModal.vue";
import ClientViewModal from "./components/ClientViewModal.vue";
import DeleteConfirmModal from "./components/DeleteConfirmModal.vue";
import { useClientsApi } from "./api";
import { useAbilities } from "@/composables/useAbilities";
import { showSuccessToast, showErrorToast } from "@/utils/swal";
import { BtnNew, BtnView, BtnEdit, BtnDelete, BtnSearch, BtnFilter } from "@/components/buttons";

const { hasPermission } = useAbilities();

onMounted(async () => {
  await loadClients();
});

// Composables
const {
  items: clients,
  loading,
  error,
  pagination,
  getAll,
  deleteItem,
} = useClientsApi();

// Funções de navegação específicas da página
const view = (item: any) => {
  selectedClient.value = item;
  showViewModal.value = true;
};

const edit = (item: any) => {
  selectedClient.value = item;
  showClientModal.value = true;
};

const remove = (item: any) => {
  selectedClient.value = item;
  showDeleteModal.value = true;
};

// Load data
const loadClients = async () => {
  try {
    await getAll({
      search: searchQuery.value,
      page: pagination.value.current_page,
      per_page: 12,
    });
  } catch (error: any) {
    const errorMessage =
      error.response?.data?.message || "Erro ao carregar clientes";
    showErrorToast(errorMessage, "Erro!");
  }
};

const handleDelete = async (client: any) => {
  try {
    loading.value = true;

    await deleteItem(client.id);

    showSuccessToast(`Cliente ${client.name} foi excluído com sucesso!`, "Sucesso!");

    // Reload the list
    await loadClients();
  } catch (error: any) {
    const errorMessage =
      error.response?.data?.message || "Erro ao excluir cliente";
    showErrorToast(errorMessage, "Erro!");
  }
};

const handleEditFromView = (client: any) => {
  selectedClient.value = client;
  showClientModal.value = true;
};

// Reactive data
const searchQuery = ref("");

// Modal states
const showClientModal = ref(false);
const showViewModal = ref(false);
const showDeleteModal = ref(false);

// Editing/Creating state
const selectedClient = ref<any>(null);

// Handlers
const create = () => {
  selectedClient.value = null;
  showClientModal.value = true;
};

const handleSave = async (data: any) => {
  try {
    loading.value = true;

    if (selectedClient.value && selectedClient.value.id) {
      // Update
      await useClientsApi().updateItem(selectedClient.value.id, data);
      showSuccessToast(
        "Cliente atualizado com sucesso!",
        "Sucesso!"
      );
    } else {
      // Create
      await useClientsApi().createItem(data);
      showSuccessToast(
        "Cliente criado com sucesso!",
        "Sucesso!"
      );
    }

    showClientModal.value = false;
    selectedClient.value = null;

    // Reload the list
    await loadClients();
  } catch (error: any) {
    const errorMessage = error.response?.data?.message || (selectedClient.value?.id ? "Erro ao atualizar cliente" : "Erro ao criar cliente");
    showErrorToast(errorMessage, "Erro!");
  }
};

const performSearch = async () => {
  try {
    const filters: any = {};

    if (searchQuery.value) {
      filters.search = searchQuery.value;
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

  // Reset to first page and reload all clients
  await getAll({ page: 1 });
};

// Pagination handlers
const handlePageChange = async (page: number) => {
  try {
    const filters: any = { page };

    if (searchQuery.value) {
      filters.search = searchQuery.value;
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

    await getAll(filters);
  } catch (err) {
    console.error("Erro ao alterar itens por página:", err);
  }
};

const formatPhone = (phone: string) => {
  if (!phone) return ''

  const cleaned = phone.replace(/\D/g, '')

  if (cleaned.length === 11) {
    return `(${cleaned.slice(0, 2)}) ${cleaned.slice(2, 7)}-${cleaned.slice(7)}`
  } else if (cleaned.length === 10) {
    return `(${cleaned.slice(0, 2)}) ${cleaned.slice(2, 6)}-${cleaned.slice(6)}`
  }

  return phone
};

const formatDate = (dateString: string) => {
  if (!dateString) return 'Data não informada';

  try {
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    });
  } catch (error) {
    return dateString;
  }
};
</script>

<style scoped>
/* Estilos específicos da página de clientes */

/* Filters Header */
.filters-header {
  border-bottom: 1px solid rgba(var(--v-theme-outline), 0.12);
  padding-bottom: 16px;
}

.filters-header h3 {
  color: rgba(var(--v-theme-on-surface), 0.87);
  margin-bottom: 4px;
}

.filter-toggle-btn {
  transition: all 0.2s ease;
}

.filter-toggle-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Loading State */
.clients-loading {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* Empty State */
.clients-empty {
  text-align: center;
  padding: 64px 32px;
  color: rgba(var(--v-theme-on-surface), 0.6);
}

/* Clients Grid */
.clients-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 24px;
}

/* Client Card */
.client-card {
  border-radius: 16px;
  border: 1px solid rgba(var(--v-theme-primary), 0.12);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden;
  background: rgb(var(--v-theme-surface));
}

.client-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
  border-color: rgba(var(--v-theme-primary), 0.3);
}

.client-card-header {
  padding: 20px;
  background: linear-gradient(135deg,
      rgba(var(--v-theme-primary), 0.05) 0%,
      rgba(var(--v-theme-primary), 0.02) 100%);
  border-bottom: 1px solid rgba(var(--v-theme-primary), 0.1);
}

.client-title {
  flex: 1;
  min-width: 0;
}

.client-card-content {
  padding: 20px;
}

.client-description {
  padding-bottom: 12px;
}

.client-stats {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.stat-item {
  display: flex;
  align-items: center;
  padding: 8px 0;
}

.client-card-actions {
  padding: 12px 20px;
  background: rgba(var(--v-theme-surface-variant), 0.3);
  border-top: 1px solid rgba(var(--v-theme-primary), 0.08);
}

/* Pagination */
.clients-pagination {
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
  color: rgba(var(--v-theme-on-surface), 0.7);
}

/* Action Buttons Container */
.action-buttons-container {
  gap: 12px;
  align-items: center;
}

.action-button {
  transition: all 0.2s ease;
}

.action-button:hover {
  transform: translateY(-1px);
}

/* Responsive */
@media (max-width: 960px) {
  .pagination-controls-container {
    flex-direction: column;
    align-items: stretch;
  }

  .pagination-controls {
    order: 2;
  }

  .items-per-page-selector {
    order: 1;
    justify-content: center;
    width: 100%;
  }
}

@media (max-width: 768px) {
  .clients-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }

  .client-card-header {
    padding: 16px;
  }

  .client-card-content {
    padding: 16px;
  }

  .client-card-actions {
    padding: 12px 16px;
  }

  .action-buttons-container {
    flex-direction: column;
    width: 100%;
  }

  .action-buttons-container > * {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .clients-grid {
    gap: 12px;
  }

  .client-card-header {
    padding: 12px;
  }

  .client-card-content {
    padding: 12px;
  }

  .flex-grow-1 .text-h6 {
    font-size: 1rem;
  }
}

/* Dark theme adjustments */
.v-theme--dark .client-card {
  background: rgb(var(--v-theme-surface));
  border-color: rgba(255, 255, 255, 0.12);
}

.v-theme--dark .client-card:hover {
  border-color: rgba(255, 255, 255, 0.24);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.3);
}

.v-theme--dark .client-card-header {
  background: linear-gradient(135deg,
      rgba(var(--v-theme-primary), 0.15) 0%,
      rgba(var(--v-theme-primary), 0.08) 100%);
  border-bottom-color: rgba(255, 255, 255, 0.12);
}

.v-theme--dark .client-card-actions {
  background: rgba(var(--v-theme-surface-variant), 0.5);
  border-top-color: rgba(255, 255, 255, 0.08);
}
</style>
