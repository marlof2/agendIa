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
          <BtnNew v-if="hasPermission('profiles.create')" @click="create" />
        </template>
        <template #right>
          <div class="d-flex action-buttons-container">
            <ExportActions
              :data="profiles"
              filename="perfis"
              button-text="Exportar"
              color="primary"
              variant="outlined"
              prepend-icon="mdi-download"
              endpoint="/profiles"
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
                label="Buscar nome ou descrição"
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
      <div v-if="loading" class="profiles-loading">
        <v-skeleton-loader v-for="i in 5" :key="i" type="card" class="mb-4" />
      </div>

      <!-- Empty State -->
      <div v-else-if="profiles.length === 0" class="profiles-empty">
        <v-icon size="64" color="grey-lighten-1">mdi-account-group-off</v-icon>
        <p class="text-h6 mt-4">Nenhum perfil encontrado</p>
        <p class="text-body-2 text-medium-emphasis">
          Crie seu primeiro perfil para começar
        </p>
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
              <div class="flex-grow-1">
                <div class="text-h6 font-weight-bold">
                  {{ item.display_name || "Perfil não informado" }}
                </div>
                <!-- <div class="text-body-2 text-medium-emphasis">
                  {{ item.name || "Nome não informado" }}
                </div> -->
              </div>
              <ActionsMenu
                :item="item"
                :custom-actions="profileActions"
                view-permission="profiles.show"
                edit-permission="profiles.edit"
                delete-permission="profiles.delete"
                @view="view"
                @edit="edit"
                @delete="remove"
                @action="handleProfileAction"
              />
            </div>
          </v-card-title>

          <!-- Card Content -->
          <v-card-text class="profile-card-content">
            <div class="profile-description mb-4">
              <div class="text-body-2 text-medium-emphasis">
                {{ item.description || "Sem descrição" }}
              </div>
            </div>

            <!-- Profile Stats -->
            <div class="profile-stats">
              <div class="stat-item">
                <v-icon size="18" color="primary" class="mr-2"
                  >mdi-shield-account</v-icon
                >
                <span class="text-body-2"
                  ><strong class="text-high-emphasis">Permissões: </strong
                  >{{ item.abilities?.length || 0 }}</span
                >
              </div>
              <div class="stat-item">
                <v-icon size="18" color="info" class="mr-2"
                  >mdi-calendar</v-icon
                >
                <span class="text-body-2">
                  <strong class="text-high-emphasis">Criado em: </strong
                  >{{ formatDate(item.created_at) }}
                </span>
              </div>
            </div>
          </v-card-text>

          <!-- Card Actions -->
          <v-card-actions class="profile-card-actions">
            <BtnView
              @click="view(item)"
              v-if="hasPermission('profiles.show')"
            />
            <v-btn
              v-if="hasPermission('profiles.update_abilities')"
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
            <BtnEdit
              @click="edit(item)"
              v-if="hasPermission('profiles.edit')"
            />
            <BtnDelete
              @click="remove(item)"
              v-if="hasPermission('profiles.delete')"
            />
          </v-card-actions>
        </v-card>
      </div>

      <!-- Pagination -->
      <div v-if="profiles.length > 0" class="profiles-pagination mt-6">
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
    @reload="handleListReload"
  />

  <ProfileViewModal
    v-model="showViewModal"
    :profile="selectedProfile"
    @edit="handleEditFromView"
    @reload="handleListReload"
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
    @reload="handleListReload"
  />
</template>

<script lang="ts" setup>
import { ref, onMounted } from "vue";
import BasePage from "@/components/BasePage.vue";
import ActionBar from "@/components/ActionBar.vue";
import FiltersCard from "@/components/FiltersCard.vue";
import ExportActions from "@/components/ExportActions.vue";
import ActionsMenu, { type CustomAction } from "@/components/ActionsMenu.vue";
import ProfileModal from "./components/ProfileModal.vue";
import ProfileViewModal from "./components/ProfileViewModal.vue";
import DeleteConfirmModal from "./components/DeleteConfirmModal.vue";
import ProfileAbilitiesModal from "./components/ProfileAbilitiesModal.vue";
import { useProfilesApi } from "./api";
import { useAbilities } from "@/composables/useAbilities";
import { showSuccessToast, showErrorToast } from "@/utils/swal";

const { hasPermission } = useAbilities();

// Ações customizadas do menu
const profileActions: CustomAction[] = [
  {
    key: "permissions",
    title: "Permissões",
    subtitle: "Gerenciar habilidades",
    icon: "mdi-shield-account-outline",
    permission: "profiles.update_abilities",
  },
];

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
  deleteItem,
} = useProfilesApi();

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
    console.error("Erro ao carregar perfis:", err);
  }
};

const create = () => {
  selectedProfile.value = null;
  isEditing.value = false;
  showProfileModal.value = true;
};

// Funções dos modais
const handleListReload = async () => {
  await getAll();
};

const handleDeleteConfirm = async (item: any) => {
  try {
    await deleteItem(item.id);
    showSuccessToast("Perfil excluído com sucesso!", "Sucesso!");
    await getAll(); // Recarregar o grid
  } catch (err: any) {
    const errorMessage =
      err?.response?.data?.message || err?.message || "Erro ao excluir perfil";
    showErrorToast(errorMessage, "Erro!");
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

const handleProfileAction = (key: string, item: any) => {
  if (key === "permissions") {
    manageAbilities(item);
  }
};

// Reactive data
const searchQuery = ref("");

// Modal states
const showProfileModal = ref(false);
const showViewModal = ref(false);
const showDeleteModal = ref(false);
const showAbilitiesModal = ref(false);
const selectedProfile = ref<any>(null);
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
  background: linear-gradient(
    135deg,
    rgba(var(--v-theme-primary), 0.05),
    rgba(var(--v-theme-primary), 0.02)
  );
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

/* Pagination */
.profiles-pagination {
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
  .profiles-grid {
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
