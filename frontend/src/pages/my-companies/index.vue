<template>
  <BasePage
    title="Minhas Empresas"
    subtitle="Gerencie suas associações com empresas"
    :breadcrumbs="[{ title: 'Minhas Empresas' }]"
  >
    <template #content>
      <v-row>
        <v-col cols="12">
          <v-card elevation="2" class="content-card">
            <!-- Header com estatísticas -->
            <v-card-text class="pa-6 pb-4">
              <div
                class="d-flex flex-column flex-md-row justify-space-between align-start align-md-center mb-6 gap-2"
              >
                <!-- Estatísticas rápidas -->
                <div class="stats-item">
                  <v-icon color="primary" size="20" class="mr-2"
                    >mdi-office-building</v-icon
                  >
                  <span class="text-body-2 font-weight-medium"
                    >{{ userCompanies.length }} empresa(s)</span
                  >
                </div>
                <div v-if="currentTenant" class="stats-item">
                  <v-icon color="success" size="20" class="mr-2"
                    >mdi-check-circle</v-icon
                  >
                  <span class="text-body-2 font-weight-medium"
                    >Atual: {{ currentTenant.name }}</span
                  >
                </div>

                <!-- Botão de ação principal -->
                <v-btn
                  color="primary"
                  prepend-icon="mdi-plus"
                  variant="flat"
                  class="action-button"
                  @click="openAssociateModal"
                >
                  <span class="d-none d-sm-inline">Vincular</span>
                  <span class="d-sm-none">Vincular</span>
                </v-btn>
              </div>

              <!-- Loading State -->
              <div v-if="loadingCompanies" class="loading-container">
                <div class="loading-content">
                  <v-progress-circular
                    indeterminate
                    color="primary"
                    size="64"
                    width="4"
                  />
                  <h4 class="text-h6 font-weight-medium mt-4 mb-2">
                    Carregando suas empresas...
                  </h4>
                  <p class="text-body-2 text-medium-emphasis">
                    Buscando suas associações
                  </p>
                </div>
              </div>

              <!-- Empty State -->
              <div v-else-if="userCompanies.length === 0" class="empty-state">
                <v-card elevation="0" variant="tonal" class="empty-card">
                  <v-card-text class="text-center pa-8">
                    <div class="empty-icon-container">
                      <v-icon size="80" color="grey-lighten-1"
                        >mdi-office-building-outline</v-icon
                      >
                    </div>
                    <h3 class="text-h5 font-weight-bold mt-6 mb-3">
                      Nenhuma empresa vinculada
                    </h3>
                    <p
                      class="text-body-1 text-medium-emphasis mb-6 max-width-400 mx-auto"
                    >
                      Você ainda não está vinculado a nenhuma empresa.
                      Vincule-se a uma empresa para começar a usar o sistema.
                    </p>
                    <v-btn
                      color="primary"
                      variant="flat"
                      prepend-icon="mdi-plus"
                      size="large"
                      class="empty-action-btn"
                      @click="openAssociateModal"
                    >
                      Vincular à Primeira Empresa
                    </v-btn>
                  </v-card-text>
                </v-card>
              </div>

              <!-- Lista de Empresas -->
              <div v-else class="companies-grid">
                <v-row>
                  <v-col
                    v-for="company in userCompanies"
                    :key="company.id"
                    cols="12"
                    sm="6"
                    xl="4"
                    v-show="company && company.id"
                  >
                    <v-card
                      class="company-card"
                      elevation="2"
                      :class="{
                        'company-card--current':
                          currentTenant?.id === company.id,
                        'company-card--main': isMainCompany(company),
                      }"
                      hover
                    >
                      <!-- Header do Card -->
                      <v-card-title class="pa-4 pb-2 flex-grow-1">
                        <div class="company-header">
                          <!-- Avatar e Info Principal -->
                          <div class="company-info">
                            <v-avatar
                              :color="
                                isMainCompany(company) ? 'warning' : 'primary'
                              "
                              size="56"
                              class="company-avatar"
                            >
                              <v-icon color="white" size="28">
                                {{
                                  isMainCompany(company)
                                    ? "mdi-crown"
                                    : "mdi-office-building"
                                }}
                              </v-icon>
                            </v-avatar>

                            <div class="company-details">
                              <h4 class="text-h6 font-weight-bold mb-1">
                                {{ company.name }}
                              </h4>

                              <!-- Perfil do usuário -->
                              <div class="company-profile">
                                <v-chip
                                  v-if="getCurrentProfile(company)"
                                  :color="
                                    getProfileColor(getCurrentProfile(company))
                                  "
                                  size="small"
                                  variant="tonal"
                                  class="profile-chip"
                                >
                                  <v-icon start size="14">
                                    {{
                                      getProfileIcon(getCurrentProfile(company))
                                    }}
                                  </v-icon>
                                  {{
                                    getProfileName(getCurrentProfile(company))
                                  }}
                                </v-chip>
                                <span
                                  v-else
                                  class="text-caption text-medium-emphasis"
                                >
                                  Perfil não definido
                                </span>
                              </div>
                            </div>
                          </div>

                          <!-- Badges de Status -->
                          <div class="company-badges">
                            <v-chip
                              v-if="isMainCompany(company)"
                              color="warning"
                              size="small"
                              variant="flat"
                              class="status-badge"
                            >
                              <v-icon start size="14">mdi-crown</v-icon>
                              Principal
                            </v-chip>

                            <v-chip
                              v-if="currentTenant?.id === company.id"
                              color="success"
                              size="small"
                              variant="flat"
                              class="status-badge"
                            >
                              <v-icon start size="14">mdi-check-circle</v-icon>
                              Atual
                            </v-chip>
                          </div>
                        </div>
                      </v-card-title>

                      <!-- Ações do Card -->
                      <v-card-actions class="pa-4 pt-2">
                        <div class="company-actions">
                          <!-- Ações Principais -->
                          <div class="primary-actions">
                            <v-btn
                              v-if="currentTenant?.id !== company.id"
                              color="primary"
                              variant="flat"
                              size="small"
                              prepend-icon="mdi-swap-horizontal"
                              class="action-btn"
                              @click="switchToCompany(company)"
                            >
                              Trocar
                            </v-btn>

                            <v-btn
                              v-if="!isMainCompany(company)"
                              color="warning"
                              variant="tonal"
                              size="small"
                              prepend-icon="mdi-crown"
                              class="action-btn"
                              :loading="updatingMainCompany === company.id"
                              @click="setMainCompany(company)"
                            >
                              Definir Principal
                            </v-btn>

                            <v-btn
                              v-else
                              color="warning"
                              variant="outlined"
                              size="small"
                              prepend-icon="mdi-crown-off"
                              class="action-btn"
                              :loading="updatingMainCompany === company.id"
                              @click="removeMainCompany(company)"
                            >
                              Remover Principal
                            </v-btn>
                          </div>

                          <!-- Ação Secundária -->
                          <div class="secondary-actions">
                            <v-btn
                              variant="text"
                              color="error"
                              size="small"
                              icon="mdi-link-off"
                              class="unlink-btn"
                              @click="confirmUnlink(company)"
                              :disabled="userCompanies.length === 1"
                            >
                              <v-icon size="18">mdi-link-off</v-icon>
                              <v-tooltip activator="parent" location="top">
                                {{
                                  userCompanies.length === 1
                                    ? "Você precisa estar vinculado a pelo menos uma empresa"
                                    : "Desvincular empresa"
                                }}
                              </v-tooltip>
                            </v-btn>
                          </div>
                        </div>
                      </v-card-actions>
                    </v-card>
                  </v-col>
                </v-row>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </template>
  </BasePage>

  <!-- Modal de Associar Empresas -->
  <AssociateCompaniesModal
    v-model="showAssociateModal"
    :user-companies="userCompanies"
    @success="handleAssociateSuccess"
  />

  <!-- Modal de Confirmação de Desvincular -->
  <v-dialog v-model="showUnlinkModal" max-width="500">
    <v-card>
      <v-card-title class="pa-4 bg-error">
        <v-icon class="mr-2">mdi-alert</v-icon>
        Desvincular Empresa
      </v-card-title>

      <v-card-text class="pa-6">
        <p class="text-body-1 mb-2">
          Tem certeza que deseja se desvincular da empresa
          <strong>{{ companyToUnlink?.name }}</strong
          >?
        </p>
        <v-alert type="warning" variant="tonal" class="mt-4">
          <div class="text-body-2">
            <strong>Atenção:</strong> Você perderá o acesso aos dados e
            funcionalidades desta empresa.
          </div>
        </v-alert>
      </v-card-text>

      <v-card-actions class="pa-4">
        <v-spacer />
        <v-btn variant="text" @click="showUnlinkModal = false">
          Cancelar
        </v-btn>
        <v-btn
          color="error"
          variant="flat"
          :loading="unlinking"
          @click="handleUnlink"
        >
          Desvincular
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script lang="ts" setup>
import { ref, computed, onMounted } from "vue";
import { useAuth } from "@/composables/useAuth";
import { useTenant } from "@/composables/useTenant";
import { useHttp } from "@/composables/useHttp";
import { useMask } from "@/composables/useMask";
import { showSuccessToast, showErrorToast } from "@/utils/swal";
import BasePage from "@/components/BasePage.vue";
import BaseDialog from "@/components/BaseDialog.vue";
import AssociateCompaniesModal from "./components/AssociateCompaniesModal.vue";

const { user, updateUserData } = useAuth();
const { currentTenant, switchTenant, setCurrentTenant } = useTenant();
const http = useHttp();
const { formatPhone } = useMask();

// State
const loadingCompanies = ref(false);
const userCompaniesDetailed = ref<any[]>([]);
const showAssociateModal = ref(false);
const showUnlinkModal = ref(false);
const unlinking = ref(false);
const updatingMainCompany = ref<number | null>(null);
const companyToUnlink = ref<any>(null);

// Computed
const userCompanies = computed(() => {
  return userCompaniesDetailed.value.length > 0
    ? userCompaniesDetailed.value
    : user.value?.companies || [];
});


// Methods
const getProfileColor = (profileName: string) => {
  const colors: Record<string, string> = {
    admin: "error",
    owner: "primary",
    professional: "info",
    supervisor: "warning",
    client: "success",
  };
  return colors[profileName] || "default";
};

const isAlreadyLinked = (companyId: number) => {
  return userCompanies.value.some((c: any) => c.id === companyId);
};

const isMainCompany = (company: any) => {
  const isMain = company?.pivot?.is_main_company;
  return isMain === true || isMain === 1;
};

// Função para obter perfil atual de uma empresa
const getCurrentProfile = (company: any) => {
  return company?.pivot?.profile_id || null;
};

// Função para obter nome do perfil por ID
const getProfileName = (profileId: number) => {
  const profileMap: Record<number, string> = {
    1: "Administrador",
    2: "Propietário",
    3: "Supervisor",
    4: "Profissional",
    5: "Cliente",
  };
  return profileMap[profileId] || "Perfil";
};

// Função para obter ícone do perfil por ID
const getProfileIcon = (profileId: number) => {
  const iconMap: Record<number, string> = {
    1: "mdi-shield-crown",
    2: "mdi-crown",
    3: "mdi-account-tie-hat",
    4: "mdi-account-tie",
    5: "mdi-account",
  };
  return iconMap[profileId] || "mdi-account";
};


const switchToCompany = (company: any) => {
  switchTenant(company, user.value?.companies);
  showSuccessToast(`Agora você está em: ${company.name}`, "Empresa Alterada");
};

const confirmUnlink = (company: any) => {
  companyToUnlink.value = company;
  showUnlinkModal.value = true;
};

const openAssociateModal = async () => {
  showAssociateModal.value = true;
};

const handleAssociateSuccess = async () => {
  await updateUserData();
  showAssociateModal.value = false;
};


const handleUnlink = async () => {
  if (!companyToUnlink.value) return;

  unlinking.value = true;
  try {
    const response = await http.del(
      `/users/detach-company/${companyToUnlink.value.id}`
    );

    if (response.success) {
      await updateUserData();

      // Se desvinculou da empresa atual, trocar para outra
      if (currentTenant.value?.id === companyToUnlink.value.id) {
        const remainingCompanies = userCompanies.value.filter(
          (c: any) => c.id !== companyToUnlink.value.id
        );
        if (remainingCompanies.length > 0) {
          switchTenant(remainingCompanies[0], user.value?.companies);
        }
      }

      showSuccessToast("Empresa desvinculada com sucesso!");
      showUnlinkModal.value = false;
      companyToUnlink.value = null;
    }
  } catch (error: any) {
    console.error("Erro ao desvincular empresa:", error);
    showErrorToast(
      error.response?.data?.message || "Erro ao desvincular empresa"
    );
  } finally {
    unlinking.value = false;
  }
};

const setMainCompany = async (company: any) => {
  updatingMainCompany.value = company.id;
  try {
    const response = await http.post("/users/update-main-company", {
      company_id: company.id,
      is_main: true,
    });

    if (response.success) {
      // Atualizar estado local PRIMEIRO se necessário
      if (response.data?.user?.companies) {
        userCompaniesDetailed.value = response.data.user.companies;
      }

      // Depois atualizar dados do usuário
      await updateUserData();

      // Atualizar o flag is_main_company do tenant atual
      if (currentTenant.value?.id === company.id) {
        // Se está definindo a empresa atual como principal
        setCurrentTenant({
          id: currentTenant.value!.id,
          name: currentTenant.value!.name,
          is_main_company: true,
          profile_id: currentTenant.value!.profile_id,
          profile_name: currentTenant.value!.profile_name,
        });
      } else {
        // Se está definindo outra empresa como principal, remover flag da atual
        setCurrentTenant({
          id: currentTenant.value!.id,
          name: currentTenant.value!.name,
          is_main_company: false,
          profile_id: currentTenant.value!.profile_id,
          profile_name: currentTenant.value!.profile_name,
        });
      }

      showSuccessToast(response.message || "Empresa definida como principal!");
    }
  } catch (error: any) {
    console.error("Erro ao definir empresa principal:", error);
    showErrorToast(
      error.response?.data?.message || "Erro ao definir empresa principal"
    );
  } finally {
    updatingMainCompany.value = null;
  }
};

const removeMainCompany = async (company: any) => {
  updatingMainCompany.value = company.id;
  try {
    const response = await http.post("/users/update-main-company", {
      company_id: company.id,
      is_main: false,
    });

    if (response.success) {
      // Atualizar estado local PRIMEIRO se necessário
      if (response.data?.user?.companies) {
        userCompaniesDetailed.value = response.data.user.companies;
      }

      // Depois atualizar dados do usuário
      await updateUserData();

      // Atualizar apenas o flag is_main_company do tenant atual se for a empresa atual
      if (currentTenant.value?.id === company.id) {
        setCurrentTenant({
          id: currentTenant.value!.id,
          name: currentTenant.value!.name,
          is_main_company: false,
          profile_id: currentTenant.value!.profile_id,
          profile_name: currentTenant.value!.profile_name,
        });
      }

      showSuccessToast(response.message || "Empresa removida como principal!");
    }
  } catch (error: any) {
    console.error("Erro ao remover empresa principal:", error);
    showErrorToast(
      error.response?.data?.message || "Erro ao remover empresa principal"
    );
  } finally {
    updatingMainCompany.value = null;
  }
};

// Lifecycle
onMounted(async () => {
  loadingCompanies.value = true;
  try {
    await updateUserData();
  } catch (error) {
    console.error("Erro ao carregar dados do usuário:", error);
  } finally {
    loadingCompanies.value = false;
  }
});
</script>

<style scoped>
/* Layout Principal */
.content-card {
  border-radius: 20px;
  overflow: hidden;
}

/* Estatísticas */
.stats-item {
  display: flex;
  align-items: center;
  background: rgba(var(--v-theme-primary), 0.05);
  padding: 8px 12px;
  border-radius: 8px;
  border: 1px solid rgba(var(--v-theme-primary), 0.1);
}

/* Botão de Ação */
.action-button {
  min-width: 180px;
  font-weight: 600;
  border-radius: 12px;
  text-transform: none;
  letter-spacing: 0.5px;
}

/* Estados de Loading */
.loading-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 300px;
  padding: 40px 20px;
}

.loading-content {
  text-align: center;
  max-width: 300px;
}

/* Estado Vazio */
.empty-state {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 400px;
  padding: 40px 20px;
}

.empty-card {
  border-radius: 16px;
  max-width: 500px;
  width: 100%;
}

.empty-icon-container {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 120px;
  height: 120px;
  margin: 0 auto;
  background: rgba(var(--v-theme-primary), 0.05);
  border-radius: 50%;
  border: 2px dashed rgba(var(--v-theme-primary), 0.2);
}

.empty-action-btn {
  border-radius: 12px;
  text-transform: none;
  font-weight: 600;
  padding: 0 32px;
}

.max-width-400 {
  max-width: 400px;
}

/* Grid de Empresas */
.companies-grid {
  margin-top: 8px;
}

.company-card {
  border-radius: 16px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: 2px solid transparent;
  position: relative;
  overflow: hidden;
  min-height: 280px; /* Altura mínima fixa para todos os cards */
  display: flex;
  flex-direction: column;
}

.company-card::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: transparent;
  transition: all 0.3s ease;
}

.company-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
  border-color: rgba(var(--v-theme-primary), 0.2);
}

.company-card:hover::before {
  background: linear-gradient(
    90deg,
    rgb(var(--v-theme-primary)),
    rgb(var(--v-theme-secondary))
  );
}

/* Estados especiais dos cards */
.company-card--current {
  border-color: rgb(var(--v-theme-success));
  box-shadow: 0 4px 20px rgba(var(--v-theme-success), 0.2);
  transform: none; /* Mantém o mesmo tamanho */
}

.company-card--current:hover {
  transform: translateY(-8px); /* Mantém o hover normal */
}

.company-card--current::before {
  background: rgb(var(--v-theme-success));
  height: 6px; /* Barra mais espessa para destacar */
}

.company-card--main {
  border-color: rgb(var(--v-theme-warning));
  box-shadow: 0 4px 20px rgba(var(--v-theme-warning), 0.2);
  transform: none; /* Mantém o mesmo tamanho */
}

.company-card--main:hover {
  transform: translateY(-8px); /* Mantém o hover normal */
}

.company-card--main::before {
  background: rgb(var(--v-theme-warning));
  height: 6px; /* Barra mais espessa para destacar */
}

/* Card que é tanto atual quanto principal */
.company-card--current.company-card--main::before {
  background: linear-gradient(
    90deg,
    rgb(var(--v-theme-success)),
    rgb(var(--v-theme-warning))
  );
  height: 8px; /* Barra ainda mais espessa */
}

/* Header do Card */
.company-header {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.company-info {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  margin-bottom: 12px;
}

.company-avatar {
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.company-details {
  flex: 1;
  min-width: 0;
}

.company-details h4 {
  line-height: 1.3;
  word-break: break-word;
}

.company-profile {
  margin-top: 4px;
}

.profile-chip {
  font-weight: 500;
  border-radius: 8px;
}

.company-badges {
  display: flex;
  flex-direction: column;
  gap: 6px;
  align-items: flex-end;
}

.status-badge {
  font-weight: 600;
  border-radius: 8px;
  font-size: 0.75rem;
}

/* Ações do Card */
.company-actions {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.primary-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.action-btn {
  border-radius: 10px;
  text-transform: none;
  font-weight: 500;
  letter-spacing: 0.3px;
  flex: 1;
  min-width: 120px;
}

.secondary-actions {
  display: flex;
  justify-content: flex-end;
  align-items: center;
}

.unlink-btn {
  border-radius: 8px;
  transition: all 0.2s ease;
}

.unlink-btn:hover {
  background: rgba(var(--v-theme-error), 0.1);
  color: rgb(var(--v-theme-error));
}

/* Modal Styles */
.company-list-item {
  border: 2px solid transparent;
  border-radius: 12px;
  transition: all 0.2s ease;
  cursor: pointer;
  margin-bottom: 8px;
}

.company-list-item:hover {
  background: rgba(var(--v-theme-primary), 0.05);
  border-color: rgba(var(--v-theme-primary), 0.3);
  transform: translateX(4px);
}

.company-list-item.selected {
  background: rgba(var(--v-theme-primary), 0.1);
  border-color: rgb(var(--v-theme-primary));
}

/* Responsive Design */
@media (max-width: 1200px) {
  .company-info {
    gap: 12px;
  }

  .company-avatar {
    width: 48px !important;
    height: 48px !important;
  }

  .company-details h4 {
    font-size: 1.1rem;
  }
}

@media (max-width: 960px) {
  .content-card {
    border-radius: 16px;
  }

  .company-card {
    border-radius: 12px;
    min-height: 260px; /* Altura ajustada para mobile */
  }

  .company-card:hover {
    transform: translateY(-4px);
  }

  .action-button {
    min-width: 140px;
  }

  .empty-card {
    border-radius: 12px;
  }

  .empty-icon-container {
    width: 100px;
    height: 100px;
  }
}

@media (max-width: 600px) {
  .content-card {
    border-radius: 12px;
  }

  .company-card {
    border-radius: 10px;
    min-height: 240px; /* Altura ajustada para telas pequenas */
  }

  .company-info {
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 12px;
  }

  .company-badges {
    align-items: center;
    flex-direction: row;
    justify-content: center;
    flex-wrap: wrap;
  }

  .company-actions {
    gap: 8px;
  }

  .primary-actions {
    flex-direction: column;
    gap: 6px;
  }

  .action-btn {
    width: 100%;
    min-width: auto;
  }

  .secondary-actions {
    justify-content: center;
  }

  .stats-item {
    padding: 6px 10px;
    font-size: 0.875rem;
  }

  .empty-card {
    border-radius: 8px;
  }

  .empty-icon-container {
    width: 80px;
    height: 80px;
  }

  .action-button {
    width: 100%;
    min-width: auto;
  }
}

@media (max-width: 480px) {
  .content-card {
    border-radius: 8px;
  }

  .company-card {
    border-radius: 8px;
    min-height: 220px; /* Altura ajustada para telas muito pequenas */
  }

  .company-avatar {
    width: 44px !important;
    height: 44px !important;
  }

  .company-details h4 {
    font-size: 1rem;
  }

  .status-badge {
    font-size: 0.7rem;
  }

  .profile-chip {
    font-size: 0.75rem;
  }
}

/* Dark Theme Adjustments */
.v-theme--dark .stats-item {
  background: rgba(var(--v-theme-primary), 0.1);
  border-color: rgba(var(--v-theme-primary), 0.2);
}

.v-theme--dark .company-card {
  background: rgba(255, 255, 255, 0.02);
  border-color: rgba(255, 255, 255, 0.1);
}

.v-theme--dark .company-card:hover {
  background: rgba(255, 255, 255, 0.04);
  border-color: rgba(var(--v-theme-primary), 0.3);
}

.v-theme--dark .empty-icon-container {
  background: rgba(var(--v-theme-primary), 0.1);
  border-color: rgba(var(--v-theme-primary), 0.3);
}

/* Animations */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.company-card {
  animation: fadeInUp 0.5s ease-out;
}

.company-card:nth-child(2) {
  animation-delay: 0.1s;
}

.company-card:nth-child(3) {
  animation-delay: 0.2s;
}

.company-card:nth-child(4) {
  animation-delay: 0.3s;
}

/* Loading animation */
@keyframes pulse {
  0%,
  100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.loading-content {
  animation: pulse 2s infinite;
}
</style>
