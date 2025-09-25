<template>
  <BasePage
    title="Perfis"
    subtitle="Gerencie todos os perfis de usuário do sistema"
    :breadcrumbs="[{ title: 'Perfis' }]"
  >
    <!-- Action Bar -->
    <template #actionBar>
      <ActionBar>
        <template #left>
          <v-btn
            v-if="hasPermission('profiles.create')"
            color="success"
            prepend-icon="mdi-plus"
            rounded="lg"
            class="text-none font-weight-medium"
            @click="create"
          >
            Novo
          </v-btn>
        </template>
        <template #right>
          <div class="d-flex action-buttons-container">
            <ExportActions
              :data="profiles"
              :columns="tableColumns"
              filename="perfis"
              button-text="Exportar"
              color="primary"
              variant="outlined"
              prepend-icon="mdi-download"
              @export="handleExport"
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
                label="Buscar nome ou descrição"
                prepend-inner-icon="mdi-magnify"
                variant="outlined"
                density="comfortable"
                clearable
                rounded="lg"
                hide-details
              />
            </v-col>
          </v-row>
        </template>
        <template #actionsFilters>
          <v-btn
            color="primary"
            prepend-icon="mdi-magnify"
            rounded="lg"
            class="text-none font-weight-medium"
            @click="performSearch"
          >
            Buscar
          </v-btn>
          <v-btn
            color="secondary"
            prepend-icon="mdi-filter-variant"
            rounded="lg"
            class="text-none font-weight-medium"
            variant="outlined"
            @click="clearFilters"
          >
            Limpar Filtros
          </v-btn>
        </template>
      </FiltersCard>
    </template>

    <!-- Content -->
    <template #content>
      <!-- Loading State -->
      <div v-if="loading" class="profiles-loading">
        <v-skeleton-loader
          v-for="i in 5"
          :key="i"
          type="card"
          class="mb-4"
        />
      </div>

      <!-- Empty State -->
      <div v-else-if="profiles.length === 0" class="profiles-empty">
        <v-icon size="64" color="grey-lighten-1">mdi-account-group-off</v-icon>
        <p class="text-h6 mt-4">Nenhum perfil encontrado</p>
        <p class="text-body-2 text-medium-emphasis">Crie seu primeiro perfil para começar</p>
      </div>

      <!-- Profiles Grid -->
      <div v-else class="profiles-grid">
        <v-card
          v-for="item in profiles"
          :key="item.id"
          class="profile-card"
          elevation="2"
          hover
        >
          <!-- Card Header -->
          <v-card-title class="profile-card-header">
            <div class="d-flex align-center">
              <v-avatar size="48" color="primary" class="mr-4">
                <v-icon color="white" size="24">mdi-account-tie</v-icon>
              </v-avatar>
              <div class="flex-grow-1">
                <div class="text-h6 font-weight-bold">{{ item.display_name || 'Perfil não informado' }}</div>
                <div class="text-body-2 text-medium-emphasis">{{ item.name || 'Nome não informado' }}</div>
              </div>
              <v-menu location="bottom end" offset="8">
                <template v-slot:activator="{ props }">
                  <v-btn
                    v-bind="props"
                    icon
                    size="small"
                    variant="text"
                    color="default"
                    class="action-btn"
                  >
                    <v-icon size="20">mdi-dots-vertical</v-icon>
                  </v-btn>
                </template>
                <v-list density="compact" class="action-menu">
                  <v-list-item
                    prepend-icon="mdi-eye-outline"
                    title="Visualizar"
                    @click="view(item)"
                    class="action-item primary-action"
                  />
                  <v-list-item
                    prepend-icon="mdi-shield-account-outline"
                    title="Permissões"
                    @click="manageAbilities(item)"
                    class="action-item info-action"
                  />
                  <v-list-item
                    prepend-icon="mdi-pencil-outline"
                    title="Editar"
                    @click="edit(item)"
                    class="action-item warning-action"
                  />
                  <v-divider />
                  <v-list-item
                    prepend-icon="mdi-delete-outline"
                    title="Excluir"
                    @click="remove(item)"
                    class="action-item danger-action"
                  />
                </v-list>
              </v-menu>
            </div>
          </v-card-title>

          <!-- Card Content -->
          <v-card-text class="profile-card-content">
            <div class="profile-description mb-4">
              <div class="text-body-2 text-medium-emphasis">
                {{ item.description || 'Sem descrição' }}
              </div>
            </div>

            <!-- Profile Stats -->
            <div class="profile-stats">
              <div class="stat-item">
                <v-icon size="18" color="primary" class="mr-2">mdi-shield-account</v-icon>
                <span class="text-body-2">{{ item.abilities?.length || 0 }} permissões</span>
              </div>
              <div class="stat-item">
                <v-icon size="18" color="info" class="mr-2">mdi-calendar</v-icon>
                <span class="text-body-2">{{ formatDate(item.created_at) }}</span>
              </div>
            </div>
          </v-card-text>

          <!-- Card Actions -->
          <v-card-actions class="profile-card-actions">
            <v-btn
              color="primary"
              variant="outlined"
              size="small"
              prepend-icon="mdi-eye-outline"
              @click="view(item)"
              class="action-button"
            >
              Ver
            </v-btn>
            <v-btn
              color="info"
              variant="outlined"
              size="small"
              prepend-icon="mdi-shield-account-outline"
              @click="manageAbilities(item)"
              class="action-button"
            >
              Permissões
            </v-btn>
            <v-spacer />
            <v-btn
              color="warning"
              variant="text"
              size="small"
              icon="mdi-pencil-outline"
              @click="edit(item)"
              class="action-button"
            />
            <v-btn
              color="error"
              variant="text"
              size="small"
              icon="mdi-delete-outline"
              @click="remove(item)"
              class="action-button"
            />
          </v-card-actions>
        </v-card>
      </div>

      <!-- Pagination -->
      <div v-if="profiles.length > 0" class="profiles-pagination mt-6">
        <v-pagination
          v-model="pagination.current_page"
          :length="pagination.last_page"
          :total-visible="7"
          @update:model-value="handlePageChange"
          class="pagination-controls"
        />
        <div class="pagination-info">
          <span class="text-body-2 text-medium-emphasis">
            Mostrando {{ ((pagination.current_page - 1) * pagination.per_page) + 1 }} a
            {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}
            de {{ pagination.total }} perfis
          </span>
        </div>
      </div>
    </template>
  </BasePage>

  <!-- Modals -->
  <ProfileModal
    v-model="showProfileModal"
    :profile="selectedProfile"
    @success="handleProfileSuccess"
  />

  <ProfileViewModal
    v-model="showViewModal"
    :profile="selectedProfile"
    @edit="handleEditFromView"
  />

  <DeleteConfirmModal
    v-model="showDeleteModal"
    :item="selectedProfile"
    item-type="perfil"
    @confirm="handleDeleteConfirm"
  />

  <ProfileAbilitiesModal
    v-model="showAbilitiesModal"
    :profile="selectedProfile"
    @reload="handlePermissionReload"
  />
</template>

<script lang="ts" setup>
import { ref, onMounted } from 'vue'
import BasePage from "@/components/BasePage.vue";
import ActionBar from "@/components/ActionBar.vue";
import FiltersCard from "@/components/FiltersCard.vue";
import DataTable from "@/components/DataTable.vue";
import ExportActions from "@/components/ExportActions.vue";
import ProfileModal from "./components/ProfileModal.vue";
import ProfileViewModal from "./components/ProfileViewModal.vue";
import DeleteConfirmModal from "./components/DeleteConfirmModal.vue";
import ProfileAbilitiesModal from "./components/ProfileAbilitiesModal.vue";
import { useProfilesApi } from "./api";
import { useExport } from "@/composables/useExport";
import { useAbilities } from "@/composables/useAbilities";

const { hasPermission } = useAbilities();

onMounted(async () => {
  await loadProfiles();
});

// Composables
const {
  items: profiles,
  loading,
  error,
  pagination,
  getAll,
  createItem,
  updateItem,
  deleteItem,
} = useProfilesApi();

const { handleExport: exportData } = useExport();

// Funções de navegação específicas da página
const view = (item: any) => {
  selectedProfile.value = item;
  showViewModal.value = true;
};

const edit = (item: any) => {
  selectedProfile.value = item;
  isEditing.value = true;
  showProfileModal.value = true;
};

const remove = (item: any) => {
  selectedProfile.value = item;
  showDeleteModal.value = true;
};

const loadProfiles = async () => {
  try {
    await getAll();
  } catch (err) {
    console.error('Erro ao carregar perfis:', err);
  }
};

const create = () => {
  selectedProfile.value = null;
  isEditing.value = false;
  showProfileModal.value = true;
};

// Funções dos modais
const handleProfileSuccess = async (profile: any) => {
  try {
    if (isEditing.value && selectedProfile.value && 'id' in selectedProfile.value) {
      await updateItem(selectedProfile.value.id, profile);
      console.log('Perfil atualizado:', profile);
    } else {
      await createItem(profile);
      console.log('Perfil criado:', profile);
    }
    // A lista é atualizada automaticamente pelo composable
  } catch (err) {
    console.error('Erro ao salvar perfil:', err);
  }
};

const handleDeleteConfirm = async (item: any) => {
  try {
    await deleteItem(item.id);
    console.log('Perfil excluído:', item);
    // A lista é atualizada automaticamente pelo composable
  } catch (err) {
    console.error('Erro ao excluir perfil:', err);
  }
};

const handleEditFromView = (profile: any) => {
  selectedProfile.value = profile;
  isEditing.value = true;
  showProfileModal.value = true;
};

const manageAbilities = (item: any) => {
  selectedProfile.value = item;
  showAbilitiesModal.value = true;
};

const handlePermissionReload = async () => {
  // Recarregar a lista completa para garantir que os dados estão atualizados
  try {
    await getAll();
    showAbilitiesModal.value = false;
  } catch (err) {
    console.error('Erro ao recarregar perfis:', err);
    showAbilitiesModal.value = false;
  }
};

// Métodos para ações mobile
const getPrimaryActions = (item: any) => [
  {
    key: 'view',
    title: 'Visualizar',
    icon: 'mdi-eye-outline',
    color: 'primary',
    onClick: () => view(item)
  },
  {
    key: 'permissions',
    title: 'Permissões',
    icon: 'mdi-shield-account-outline',
    color: 'info',
    onClick: () => manageAbilities(item),
    badge: item.abilities?.length || 0
  }
];

const getSecondaryActions = (item: any) => [
  {
    key: 'edit',
    title: 'Editar',
    icon: 'mdi-pencil-outline',
    color: 'warning',
    onClick: () => edit(item)
  },
  {
    key: 'delete',
    title: 'Excluir',
    icon: 'mdi-delete-outline',
    color: 'error',
    onClick: () => remove(item)
  }
];

// Reactive data
const searchQuery = ref("");

// Modal states
const showProfileModal = ref(false);
const showViewModal = ref(false);
const showDeleteModal = ref(false);
const showAbilitiesModal = ref(false);
const selectedProfile = ref<any>(null);
const isEditing = ref(false);

// Table headers
const headers = [
  { title: "Nome", key: "display_name", sortable: true },
  { title: "Descrição", key: "description", sortable: true },
  { title: "Permissões", key: "abilities", sortable: true },
  { title: "Criado em", key: "created_at", sortable: true },
  { title: "Ações", key: "actions", sortable: false, width: "80px" },
];

// Colunas para exportação (sem ações)
const tableColumns = [
  { title: "Nome", key: "display_name" },
  { title: "Descrição", key: "description" },
  { title: "Permissões", key: "abilities" },
  { title: "Criado em", key: "created_at" },
  { title: "Última atualização", key: "updated_at" },
];


// Methods

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

const performSearch = async () => {
  try {
    const filters: any = {};

    if (searchQuery.value) {
      filters.search = searchQuery.value;
    }

    // Reset to first page when searching
    filters.page = 1;

    await getAll(filters);
    console.log("Busca realizada com filtros:", filters);
  } catch (err) {
    console.error('Erro ao realizar busca:', err);
  }
};

const clearFilters = async () => {
  searchQuery.value = "";

  // Reset to first page and reload all profiles
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
    console.error('Erro ao alterar página:', err);
  }
};

const handleItemsPerPageChange = async (itemsPerPage: number) => {
  try {
    const filters: any = {
      per_page: itemsPerPage,
      page: 1 // Reset to first page when changing items per page
    };

    if (searchQuery.value) {
      filters.search = searchQuery.value;
    }

    await getAll(filters);
  } catch (err) {
    console.error('Erro ao alterar itens por página:', err);
  }
};

// Export function
const handleExport = async (format: 'excel' | 'pdf' | 'csv', data: any[], filename: string) => {
  try {
    await exportData(format, data, tableColumns, filename);
    console.log(`Exportação ${format.toUpperCase()} concluída: ${filename}`);
  } catch (error) {
    console.error('Erro na exportação:', error);
  }
};
</script>

<style scoped>
/* Estilos específicos da página de perfis */

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
.profiles-loading {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* Empty State */
.profiles-empty {
  text-align: center;
  padding: 64px 32px;
  color: rgba(var(--v-theme-on-surface), 0.6);
}

/* Profiles Grid */
.profiles-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 24px;
  margin-bottom: 32px;
}

/* Profile Card */
.profile-card {
  border-radius: 16px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden;
}

.profile-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
}

.profile-card-header {
  background: linear-gradient(135deg, rgba(var(--v-theme-primary), 0.05), rgba(var(--v-theme-primary), 0.02));
  padding: 20px 24px;
  border-bottom: 1px solid rgba(var(--v-theme-outline), 0.12);
}

.profile-card-content {
  padding: 20px 24px;
}

.profile-description {
  min-height: 48px;
  display: flex;
  align-items: center;
}

.profile-stats {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.stat-item {
  display: flex;
  align-items: center;
  padding: 8px 0;
}

.profile-card-actions {
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

.action-btn {
  border-radius: 8px;
  transition: all 0.2s ease;
}

.action-btn:hover {
  background: rgba(var(--v-theme-primary), 0.08);
  transform: scale(1.05);
}

.action-menu {
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.action-item {
  border-radius: 8px;
  margin: 2px 4px;
  transition: all 0.2s ease;
}

.action-item:hover {
  background: rgba(var(--v-theme-primary), 0.08);
}

.primary-action:hover {
  background: rgba(var(--v-theme-primary), 0.1);
}

.info-action:hover {
  background: rgba(var(--v-theme-info), 0.1);
}

.warning-action:hover {
  background: rgba(var(--v-theme-warning), 0.1);
}

.danger-action:hover {
  background: rgba(var(--v-theme-error), 0.1);
}

/* Pagination */
.profiles-pagination {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

.pagination-controls {
  margin: 0 auto;
}

.pagination-info {
  text-align: center;
}

/* Responsividade */
@media (max-width: 768px) {
  .profiles-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }

  .profile-card-header {
    padding: 16px 20px;
  }

  .profile-card-content {
    padding: 16px 20px;
  }

  .profile-card-actions {
    padding: 12px 20px;
    flex-direction: column;
    gap: 8px;
  }

  .profile-card-actions .v-btn {
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
  .profiles-grid {
    gap: 12px;
  }

  .profile-card-header {
    padding: 12px 16px;
  }

  .profile-card-content {
    padding: 12px 16px;
  }

  .profile-card-actions {
    padding: 8px 16px;
  }
}
</style>
