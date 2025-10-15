<template>
  <div class="select-tenant-container">
    <!-- Background Elements -->
    <div class="background-elements">
      <div class="floating-shape shape-1"></div>
      <div class="floating-shape shape-2"></div>
      <div class="floating-shape shape-3"></div>
    </div>

    <v-container fluid class="fill-height">
      <v-row align="center" justify="center" class="fill-height">
        <v-col cols="12" sm="10" md="8" lg="6" xl="5">
          <v-card class="select-card" elevation="0">
            <!-- Header -->
            <div class="select-header">
              <div class="header-content">
                <v-icon size="48" color="white" class="mb-3">mdi-office-building-outline</v-icon>
                <h1 class="header-title">Selecione a Empresa</h1>
                <p class="header-subtitle">
                  Escolha a empresa que deseja acessar
                </p>
              </div>
            </div>

            <!-- Content -->
            <v-card-text class="content-section">
              <!-- User Info -->
              <div class="user-info-box">
                <v-avatar size="48" color="primary" class="mr-3">
                  <v-icon color="white">mdi-account</v-icon>
                </v-avatar>
                <div class="user-details">
                  <div class="user-name">{{ userName }}</div>
                  <div class="user-email">{{ userEmail }}</div>
                </div>
              </div>

              <v-divider class="my-6" />

              <!-- Info Alert -->
              <v-alert
                v-if="!loading && availableTenants.length > 0"
                type="info"
                variant="tonal"
                density="compact"
                class="mb-4"
                icon="mdi-information-outline"
              >
                <span class="text-body-2">
                  Se alguma empresa não estiver aparecendo, ela pode ter sido apagada ou inativada.
                </span>
              </v-alert>

              <!-- Step 1: Profile Selection -->
              <div v-if="currentStep === 1" class="profile-selection-section">
                <h3 class="text-h6 mb-4 text-center">Escolha seu perfil</h3>
                <p class="text-body-2 text-center mb-6 text-medium-emphasis">
                  Selecione o tipo de perfil que melhor descreve você
                </p>

                <v-row>
                  <v-col cols="12" md="4" v-for="profile in availableProfiles" :key="profile.id">
                    <v-card
                      class="profile-card"
                      :class="{ 'profile-card--selected': selectedProfileId === profile.id }"
                      elevation="2"
                      hover
                      @click="selectProfile(profile)"
                    >
                      <v-card-text class="text-center pa-6">
                        <v-avatar
                          size="64"
                          :color="getProfileColor(profile.name)"
                          class="mb-4"
                        >
                          <v-icon size="32" color="white">{{ getProfileIcon(profile.name) }}</v-icon>
                        </v-avatar>
                        <h4 class="text-h6 mb-2">{{ profile.display_name }}</h4>
                        <p class="text-body-2 text-medium-emphasis mb-4">{{ profile.description }}</p>

                        <!-- Profile specific info -->
                        <v-alert
                          v-if="profile.name === 'owner'"
                          type="info"
                          variant="tonal"
                          density="compact"
                          class="mt-2"
                        >
                          <template #prepend>
                            <v-icon size="16">mdi-information</v-icon>
                          </template>
                          <span class="text-caption">Você criará sua própria empresa</span>
                        </v-alert>

                        <v-alert
                          v-else
                          type="success"
                          variant="tonal"
                          density="compact"
                          class="mt-2"
                        >
                          <template #prepend>
                            <v-icon size="16">mdi-check-circle</v-icon>
                          </template>
                          <span class="text-caption">Você se associará a empresas existentes</span>
                        </v-alert>
                      </v-card-text>
                    </v-card>
                  </v-col>
                </v-row>

                <!-- Next Button -->
                <div class="text-center mt-6">
                  <v-btn
                    color="primary"
                    variant="flat"
                    size="large"
                    :disabled="!selectedProfileId"
                    @click="goToNextStep"
                  >
                    Continuar
                    <v-icon right>mdi-arrow-right</v-icon>
                  </v-btn>
                </div>
              </div>

              <!-- Step 2A: Company Registration (for owners) -->
              <div v-else-if="currentStep === 2 && isOwner" class="company-registration-section">
                <v-card class="pa-6">
                  <v-card-title class="text-h5 mb-4 d-flex align-center">
                    <v-icon size="24" class="mr-3" color="purple">mdi-domain-plus</v-icon>
                    Cadastrar Nova Empresa
                  </v-card-title>

                  <v-card-subtitle class="text-body-1 mb-6">
                    Como proprietário, você precisa criar sua empresa para começar a usar o sistema.
                  </v-card-subtitle>

                  <v-form ref="companyFormRef" v-model="companyFormValid" @submit.prevent="handleCompanySubmit">
                    <v-row>
                      <!-- Informações da Empresa -->
                      <v-col cols="12">
                        <h3 class="text-h6 mb-4 d-flex align-center">
                          <v-icon size="20" class="mr-2">mdi-domain</v-icon>
                          Informações da Empresa
                        </h3>
                      </v-col>

                      <v-col cols="12" md="6">
                        <v-text-field
                          v-model="companyForm.name"
                          label="Nome da empresa *"
                          variant="outlined"
                          density="compact"
                          rounded="lg"
                          :rules="companyNameRules"
                          prepend-inner-icon="mdi-domain"
                          required
                          hint="Nome da empresa"
                          persistent-hint
                          maxlength="255"
                        />
                      </v-col>

                      <v-col cols="12" md="6">
                        <v-select
                          v-model="companyForm.person_type"
                          :items="[
                            { title: 'Pessoa Jurídica', value: 'legal' },
                            { title: 'Pessoa Física', value: 'physical' }
                          ]"
                          label="Tipo de Pessoa *"
                          variant="outlined"
                          density="compact"
                          rounded="lg"
                          prepend-inner-icon="mdi-account-group"
                          required
                          hint="Selecione o tipo de pessoa"
                          persistent-hint
                        />
                      </v-col>

                      <v-col cols="12" md="6">
                        <v-text-field
                          v-if="companyForm.person_type === 'legal'"
                          v-model="companyForm.cnpj"
                          label="CNPJ"
                          variant="outlined"
                          density="compact"
                          rounded="lg"
                          :rules="companyCnpjRules"
                          prepend-inner-icon="mdi-card-account-details"
                          hint="CNPJ da empresa"
                          persistent-hint
                          @input="handleCompanyMaskCNPJ"
                          maxlength="18"
                        />
                        <v-text-field
                          v-else
                          v-model="companyForm.cpf"
                          label="CPF"
                          variant="outlined"
                          density="compact"
                          rounded="lg"
                          :rules="companyCpfRules"
                          prepend-inner-icon="mdi-card-account-details"
                          hint="CPF do responsável"
                          persistent-hint
                          @input="handleCompanyMaskCPF"
                          maxlength="14"
                        />
                      </v-col>

                      <v-col cols="12" md="6">
                        <v-text-field
                          v-model="companyForm.responsible_name"
                          label="Nome do Responsável *"
                          variant="outlined"
                          density="compact"
                          rounded="lg"
                          :rules="companyResponsibleNameRules"
                          prepend-inner-icon="mdi-account"
                          required
                          hint="Nome do responsável pela empresa"
                          persistent-hint
                          maxlength="255"
                        />
                      </v-col>

                      <v-col cols="12">
                        <v-divider class="my-4"></v-divider>
                        <h4 class="text-subtitle-1 mb-3">Telefones de Contato</h4>
                      </v-col>

                      <v-col cols="12" md="6">
                        <v-text-field
                          v-model="companyForm.phone_1"
                          label="Telefone Principal *"
                          variant="outlined"
                          density="compact"
                          rounded="lg"
                          :rules="companyPhone1Rules"
                          prepend-inner-icon="mdi-phone"
                          required
                          hint="Telefone principal de contato"
                          persistent-hint
                          @input="handleCompanyMaskPhone1"
                          maxlength="15"
                        />
                      </v-col>

                      <v-col cols="12" md="6">
                        <div class="d-flex align-center h-100">
                          <v-switch
                            v-model="companyForm.has_whatsapp_1"
                            color="success"
                            :disabled="!companyForm.phone_1"
                            hide-details
                          >
                            <template #label>
                              <div class="d-flex align-center">
                                <v-icon
                                  :color="companyForm.has_whatsapp_1 ? 'success' : 'grey'"
                                  class="mr-2"
                                  size="20"
                                >
                                  {{ companyForm.has_whatsapp_1 ? 'mdi-whatsapp' : 'mdi-phone' }}
                                </v-icon>
                                <span class="text-body-2">É WhatsApp</span>
                              </div>
                            </template>
                          </v-switch>
                        </div>
                      </v-col>

                      <v-col cols="12" md="6">
                        <v-text-field
                          v-model="companyForm.phone_2"
                          label="Telefone Secundário"
                          variant="outlined"
                          density="compact"
                          rounded="lg"
                          :rules="companyPhone2Rules"
                          prepend-inner-icon="mdi-phone"
                          hint="Telefone secundário (opcional)"
                          persistent-hint
                          @input="handleCompanyMaskPhone2"
                          maxlength="15"
                        />
                      </v-col>

                      <v-col cols="12" md="6">
                        <div class="d-flex align-center h-100">
                          <v-switch
                            v-model="companyForm.has_whatsapp_2"
                            color="success"
                            :disabled="!companyForm.phone_2"
                            hide-details
                          >
                            <template #label>
                              <div class="d-flex align-center">
                                <v-icon
                                  :color="companyForm.has_whatsapp_2 ? 'success' : 'grey'"
                                  class="mr-2"
                                  size="20"
                                >
                                  {{ companyForm.has_whatsapp_2 ? 'mdi-whatsapp' : 'mdi-phone' }}
                                </v-icon>
                                <span class="text-body-2">É WhatsApp</span>
                              </div>
                            </template>
                          </v-switch>
                        </div>
                      </v-col>

                      <v-col cols="12" md="6">
                        <v-select
                          v-model="companyForm.timezone_id"
                          :items="availableTimezones"
                          item-title="name"
                          item-value="id"
                          label="Fuso Horário *"
                          variant="outlined"
                          density="compact"
                          rounded="lg"
                          prepend-inner-icon="mdi-clock-outline"
                          required
                          hint="Selecione o fuso horário da empresa"
                          persistent-hint
                        />
                      </v-col>
                    </v-row>

                    <!-- Botões de navegação -->
                    <div class="d-flex justify-space-between mt-6">
                      <v-btn
                        color="grey"
                        variant="outlined"
                        prepend-icon="mdi-arrow-left"
                        @click="goToPreviousStep"
                      >
                        Voltar
                      </v-btn>

                      <v-btn
                        color="primary"
                        variant="flat"
                        prepend-icon="mdi-check"
                        type="submit"
                        :loading="registeringCompany"
                        :disabled="!companyFormValid"
                      >
                        Cadastrar Empresa
                      </v-btn>
                    </div>
                  </v-form>
                </v-card>
              </div>

              <!-- Step 2B: Company Selection (for professionals/clients) -->
              <div v-else-if="currentStep === 2 && !isOwner" class="company-selection-section">
                <h3 class="text-h6 mb-4 text-center">Selecione a empresa</h3>
                <p class="text-body-2 text-center mb-6 text-medium-emphasis">
                  Escolha a empresa que você trabalha ou é cliente
                </p>

                <!-- Search -->
                <v-text-field
                  v-model="searchCompanies"
                  label="Buscar empresa"
                  variant="outlined"
                  density="compact"
                  rounded="lg"
                  prepend-inner-icon="mdi-magnify"
                  clearable
                  class="mb-4"
                  @keyup.enter="handleCompanySearch"
                  @click:clear="handleCompanySearch"
                />

                <!-- Loading Companies -->
                <div v-if="loadingCompanies" class="companies-loading">
                  <v-skeleton-loader v-for="i in 3" :key="i" type="card" class="mb-3" />
                </div>

                <!-- Empty Companies -->
                <div v-else-if="publicCompanies.length === 0" class="text-center py-4">
                  <v-icon size="48" color="grey">mdi-office-building-off</v-icon>
                  <p class="text-body-2 text-medium-emphasis mt-2">Nenhuma empresa encontrada</p>
                </div>

                <!-- Companies List -->
                <div v-else class="companies-list-container">
                  <v-card
                    v-for="company in publicCompanies"
                    :key="company.id"
                    class="company-select-card mb-3"
                    :class="{ 'company-selected': selectedCompanyIds[0] === company.id }"
                    elevation="1"
                    @click="selectCompany(company.id)"
                  >
                    <v-card-text class="d-flex align-center pa-3">
                      <v-radio
                        :model-value="selectedCompanyIds[0]"
                        :value="company.id"
                        color="primary"
                        hide-details
                        @click.stop="selectCompany(company.id)"
                      />
                      <div class="flex-grow-1 ml-3">
                        <div class="text-subtitle-2 font-weight-bold">{{ company.name }}</div>
                        <div class="text-caption text-medium-emphasis d-flex align-center mb-1">
                          <v-icon size="12" class="mr-1">mdi-account-tie</v-icon>
                          <span class="font-weight-medium">Responsável:</span>
                          <span class="ml-1">{{ company.responsible_name }}</span>
                        </div>
                        <div class="text-caption text-medium-emphasis d-flex align-center">
                          <v-icon size="12" class="mr-1">mdi-phone</v-icon>
                          <span class="font-weight-medium">Telefone:</span>
                          <span class="ml-1">{{ formatPhone(company.phone_1) }}</span>
                        </div>
                      </div>
                      <v-chip
                        v-if="selectedCompanyIds[0] === company.id"
                        color="success"
                        size="x-small"
                      >
                        <v-icon start size="12">mdi-check</v-icon>
                        Selecionada
                      </v-chip>
                    </v-card-text>
                  </v-card>
                </div>

                <!-- Pagination -->
                <div v-if="publicCompanies.length > 0 && companiesPagination.last_page > 1" class="text-center mt-4">
                  <v-pagination
                    v-model="companiesPagination.current_page"
                    :length="companiesPagination.last_page"
                    :total-visible="5"
                    size="small"
                    @update:model-value="handleCompanyPageChange"
                  />
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-center mt-3">
                  <v-btn
                    variant="outlined"
                    color="grey"
                    class="mr-4"
                    @click="goToPreviousStep"
                  >
                    <v-icon left>mdi-arrow-left</v-icon>
                    Voltar
                  </v-btn>

                  <v-btn
                    color="primary"
                    variant="flat"
                    :disabled="selectedCompanyIds.length === 0"
                    :loading="associating"
                    @click="associateToCompanies"
                  >
                    <v-icon left>mdi-check-circle</v-icon>
                    Associar Empresa
                  </v-btn>
                </div>
              </div>

              <!-- Tenants List (existing flow) -->
              <div v-else-if="loading" class="text-center py-8">
                <v-progress-circular
                  color="primary"
                  indeterminate
                  size="48"
                />
                <p class="mt-4 text-body-2">Carregando empresas...</p>
              </div>

              <div v-else-if="availableTenants.length === 0" class="no-companies-section">
                <v-icon size="64" color="warning">mdi-alert-circle-outline</v-icon>
                <p class="mt-4 text-h6 font-weight-bold">Associação Obrigatória</p>
                <p class="text-body-2 text-medium-emphasis mb-4">
                  Para acessar o sistema, você precisa se associar a pelo menos uma empresa
                </p>

                <v-alert
                  type="warning"
                  variant="tonal"
                  density="compact"
                  class="mb-6"
                >
                  <template #prepend>
                    <v-icon>mdi-information</v-icon>
                  </template>
                  <span class="text-body-2">
                    Selecione abaixo as empresas às quais deseja se associar
                  </span>
                </v-alert>

                <!-- Search -->
                <v-text-field
                  v-model="searchCompanies"
                  label="Buscar empresa"
                  variant="outlined"
                  density="compact"
                  rounded="lg"
                  prepend-inner-icon="mdi-magnify"
                  clearable
                  class="mb-4"
                  @keyup.enter="handleCompanySearch"
                  @click:clear="handleCompanySearch"
                />

                <!-- Loading Companies -->
                <div v-if="loadingCompanies" class="companies-loading">
                  <v-skeleton-loader v-for="i in 3" :key="i" type="card" class="mb-3" />
                </div>

                <!-- Empty Companies -->
                <div v-else-if="publicCompanies.length === 0" class="text-center py-4">
                  <v-icon size="48" color="grey">mdi-office-building-off</v-icon>
                  <p class="text-body-2 text-medium-emphasis mt-2">Nenhuma empresa encontrada</p>
                </div>

                <!-- Companies List -->
                <div v-else class="companies-list-container">
                  <v-card
                    v-for="company in publicCompanies"
                    :key="company.id"
                    class="company-select-card mb-3"
                    :class="{ 'company-selected': selectedCompanyIds[0] === company.id }"
                    elevation="1"
                    @click="selectCompany(company.id)"
                  >
                    <v-card-text class="d-flex align-center pa-3">
                      <v-radio
                        :model-value="selectedCompanyIds[0]"
                        :value="company.id"
                        color="primary"
                        hide-details
                        @click.stop="selectCompany(company.id)"
                      />
                      <div class="flex-grow-1 ml-3">
                        <div class="text-subtitle-2 font-weight-bold">{{ company.name }}</div>
                        <div class="text-caption text-medium-emphasis d-flex align-center mb-1">
                          <v-icon size="12" class="mr-1">mdi-account-tie</v-icon>
                          <span class="font-weight-medium">Responsável:</span>
                          <span class="ml-1">{{ company.responsible_name }}</span>
                        </div>
                        <div class="text-caption text-medium-emphasis d-flex align-center">
                          <v-icon size="12" class="mr-1">mdi-phone</v-icon>
                          <span class="font-weight-medium">Telefone:</span>
                          <span class="ml-1">{{ formatPhone(company.phone_1) }}</span>
                        </div>
                      </div>
                      <v-chip
                        v-if="selectedCompanyIds[0] === company.id"
                        color="success"
                        size="x-small"
                      >
                        <v-icon start size="12">mdi-check</v-icon>
                        Selecionada
                      </v-chip>
                    </v-card-text>
                  </v-card>
                </div>

                <!-- Pagination -->
                <div v-if="publicCompanies.length > 0 && companiesPagination.last_page > 1" class="text-center mt-4">
                  <v-pagination
                    v-model="companiesPagination.current_page"
                    :length="companiesPagination.last_page"
                    :total-visible="5"
                    size="small"
                    @update:model-value="handleCompanyPageChange"
                  />
                </div>

                <!-- Association Button -->
                <v-btn
                  v-if="selectedCompanyIds.length > 0"
                  color="primary"
                  variant="flat"
                  size="large"
                  block
                  rounded="lg"
                  class="mt-6"
                  :loading="associating"
                  @click="associateToCompanies"
                >
                  <v-icon left>mdi-check-circle</v-icon>
                  Associar a {{ selectedCompanyIds.length }} {{ selectedCompanyIds.length === 1 ? 'empresa' : 'empresas' }}
                </v-btn>

                <v-btn
                  color="grey"
                  variant="outlined"
                  size="small"
                  class="mt-3"
                  block
                  @click="handleLogout"
                >
                  Cancelar e Sair
                </v-btn>
              </div>

              <div v-else class="tenants-grid">
                <v-card
                  v-for="tenant in availableTenants"
                  :key="tenant.id"
                  class="tenant-card"
                  :class="{ 'tenant-card--selected': selectedTenantId === tenant.id }"
                  elevation="0"
                  @click="selectTenant(tenant)"
                  hover
                >
                  <div class="tenant-card-content">
                    <div class="tenant-icon">
                      <v-avatar
                        size="64"
                        :color="tenant.is_main_company ? 'warning' : 'primary'"
                        rounded="lg"
                      >
                        <v-icon size="32" color="white">
                          {{ tenant.is_main_company ? 'mdi-crown' : 'mdi-domain' }}
                        </v-icon>
                      </v-avatar>
                    </div>

                    <div class="tenant-info">
                      <h3 class="tenant-name">{{ tenant.name }}</h3>
                      <div class="d-flex flex-column gap-1">
                        <v-chip
                          v-if="tenant.is_main_company"
                          size="x-small"
                          color="warning"
                          variant="flat"
                          class="mt-1"
                        >
                          <v-icon size="12" class="mr-1">mdi-crown</v-icon>
                          Principal
                        </v-chip>
                      <v-chip
                        size="x-small"
                        color="success"
                        variant="flat"
                          class="mt-1"
                      >
                        <v-icon size="12" class="mr-1">mdi-check-circle</v-icon>
                        Ativa
                      </v-chip>
                      </div>
                    </div>

                    <!-- Selected Indicator -->
                    <div v-if="selectedTenantId === tenant.id" class="selected-indicator">
                      <v-icon color="success" size="32">mdi-check-circle</v-icon>
                    </div>
                  </div>
                </v-card>
              </div>

              <!-- Action Button -->
              <v-btn
                v-if="!loading && availableTenants.length > 0"
                color="primary"
                variant="flat"
                size="large"
                block
                rounded="lg"
                class="mt-6 action-btn"
                :disabled="!selectedTenantId || selecting"
                :loading="selecting"
                @click="confirmSelection"
              >
                <v-icon left>mdi-arrow-right</v-icon>
                {{ selecting ? 'Acessando...' : 'Acessar Empresa' }}
              </v-btn>
            </v-card-text>

            <!-- Footer -->
            <v-card-actions class="select-footer">
              <v-btn
                variant="outlined"
                color="grey"
                prepend-icon="mdi-logout"
                @click="handleLogout"
              >
                Sair
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script lang="ts" setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import { useTenant, type Tenant } from '@/composables/useTenant'
import { useHttp } from '@/composables/useHttp'
import { useRegisterApi } from '@/pages/register/api'
import { useMask } from '@/composables/useMask'
import { useValidation } from '@/composables/useValidation'
import { showSuccessToast, showErrorToast } from '@/utils/swal'

const router = useRouter()
const { user, logout } = useAuth()
const http = useHttp()
const { registerCompany } = useRegisterApi()
const { maskCNPJ, maskCPF, maskPhone, formatPhone } = useMask()
const { getCPFValidationRules, getCNPJValidationRules } = useValidation()
const {
  availableTenants,
  setCurrentTenant,
  setAvailableTenants,
  loadAvailableTenants,
  switchTenant
} = useTenant()

// Estado
const loading = ref(true)
const selecting = ref(false)
const selectedTenantId = ref<number | null>(null)

// Novo fluxo de seleção
const currentStep = ref(1)
const selectedProfileId = ref<number | null>(null)
const availableProfiles = ref<any[]>([])
const isOwner = computed(() => {
  const profile = availableProfiles.value.find(p => p.id === selectedProfileId.value)
  return profile?.name === 'owner'
})

// Estado para associação de empresas
const loadingCompanies = ref(false)
const associating = ref(false)
const publicCompanies = ref<any[]>([])
const searchCompanies = ref('')
const selectedCompanyIds = ref<number[]>([])
const companiesPagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 5,
  total: 0
})

// Estado para cadastro de empresa (proprietários)
const companyFormRef = ref()
const companyFormValid = ref(false)
const registeringCompany = ref(false)
const availableTimezones = ref<any[]>([])
const companyForm = ref({
  name: '',
  person_type: 'legal' as 'physical' | 'legal',
  cnpj: '',
  cpf: '',
  responsible_name: '',
  phone_1: '',
  has_whatsapp_1: false,
  phone_2: '',
  has_whatsapp_2: false,
  timezone_id: null as number | null
})

// Computed
const userName = computed(() => user.value?.name || 'Usuário')
const userEmail = computed(() => user.value?.email || '')

// Validation rules for company form
const companyNameRules = [
  (v: string) => !!v || "Nome da empresa é obrigatório",
  (v: string) => (v && v.length >= 2) || "Nome deve ter pelo menos 2 caracteres",
]

const companyResponsibleNameRules = [
  (v: string) => !!v || "Nome do responsável é obrigatório",
  (v: string) => (v && v.length >= 2) || "Nome deve ter pelo menos 2 caracteres",
]

const companyPhone1Rules = [
  (v: string) => !!v || "Telefone principal é obrigatório",
  (v: string) => (v && v.length >= 10) || "Telefone deve ter pelo menos 10 caracteres",
]

const companyPhone2Rules = [
  (v: string) => {
    if (!v) return true // Campo opcional
    return v.length >= 10 || "Telefone deve ter pelo menos 10 caracteres"
  },
]

// Validation rules for documents
const companyCnpjRules = getCNPJValidationRules()
const companyCpfRules = getCPFValidationRules()

// Profile selection functions
const selectProfile = (profile: any) => {
  selectedProfileId.value = profile.id
}

const goToNextStep = () => {
  if (currentStep.value === 1 && selectedProfileId.value) {
    currentStep.value = 2
  }
}

const goToPreviousStep = () => {
  if (currentStep.value === 2) {
    currentStep.value = 1
  }
}

const getProfileColor = (profileName: string) => {
  const colors: Record<string, string> = {
    owner: "purple",
    professional: "primary",
    client: "success",
  }
  return colors[profileName] || "grey"
}

const getProfileIcon = (profileName: string) => {
  const icons: Record<string, string> = {
    owner: "mdi-crown",
    professional: "mdi-briefcase",
    client: "mdi-account",
  }
  return icons[profileName] || "mdi-account"
}

const loadAvailableProfiles = async () => {
  try {
    const response = await http.get('/combos/profiles')
    // Filtrar apenas perfis que podem ser selecionados no registro
    availableProfiles.value = response.data.filter((profile: any) =>
      ['owner', 'professional', 'client'].includes(profile.name)
    )
  } catch (error) {
    console.error('Erro ao carregar perfis:', error)
    showErrorToast('Erro ao carregar perfis', 'Erro!')
  }
}

// Métodos
const selectTenant = (tenant: Tenant) => {
  selectedTenantId.value = tenant.id
}

const confirmSelection = async () => {
  if (!selectedTenantId.value) return

  selecting.value = true

  try {
    const tenant = availableTenants.value.find(t => t.id === selectedTenantId.value)

    if (tenant) {
      await switchTenant(tenant, user.value?.companies)

      // Redireciona para o dashboard
      router.push('/dashboard')
    }
  } catch (error) {
    console.error('Erro ao selecionar empresa:', error)
  } finally {
    selecting.value = false
  }
}

// Company registration methods
const loadTimezones = async () => {
  try {
    const response = await http.get('/timezones')
    availableTimezones.value = response.data || []
  } catch (error) {
    console.error('Erro ao carregar fusos horários:', error)
  }
}

const handleCompanyMaskCNPJ = (event: Event) => {
  const maskedValue = maskCNPJ(event)
  companyForm.value.cnpj = maskedValue
}

const handleCompanyMaskCPF = (event: Event) => {
  const maskedValue = maskCPF(event)
  companyForm.value.cpf = maskedValue
}

const handleCompanyMaskPhone1 = (event: Event) => {
  const maskedValue = maskPhone(event)
  companyForm.value.phone_1 = maskedValue
}

const handleCompanyMaskPhone2 = (event: Event) => {
  const maskedValue = maskPhone(event)
  companyForm.value.phone_2 = maskedValue
}

const handleCompanySubmit = async () => {
  if (!companyFormValid.value || !user.value?.id) return

  registeringCompany.value = true

  try {
    const companyData = {
      user_id: user.value.id,
      company: {
        name: companyForm.value.name,
        person_type: companyForm.value.person_type,
        cnpj: companyForm.value.cnpj || undefined,
        cpf: companyForm.value.cpf || undefined,
        responsible_name: companyForm.value.responsible_name,
        phone_1: companyForm.value.phone_1,
        has_whatsapp_1: companyForm.value.has_whatsapp_1,
        phone_2: companyForm.value.phone_2 || undefined,
        has_whatsapp_2: companyForm.value.has_whatsapp_2,
        timezone_id: companyForm.value.timezone_id || 1 // Default timezone if none selected
      }
    }

    const result = await registerCompany(companyData)
    showSuccessToast("Empresa cadastrada com sucesso!", "Sucesso!")

    // Pegar os dados da empresa criada
    const createdCompany = result.data.company
    if (createdCompany) {
      // Criar objeto tenant com os dados da empresa criada
      const newTenant = {
        id: createdCompany.id,
        name: createdCompany.name,
        is_main_company: true, // Empresa recém-criada é sempre a principal
        profile_id: result.data.profile_id, // ID do perfil owner
        profile_name: result.data.profile_name || 'Proprietário'
      }

      // Associar automaticamente à empresa criada
      await switchTenant(newTenant, user.value?.companies)

      // Redirecionar para o dashboard
      showSuccessToast('Bem-vindo ao sistema!', 'Sucesso!')
      await router.push('/dashboard')
    } else {
      // Fallback: recarregar tenants se não conseguir pegar os dados
      await loadAvailableTenants()

      if (availableTenants.value.length === 1) {
        const tenant = availableTenants.value[0]
        if (tenant) {
          await switchTenant(tenant, user.value?.companies)
          showSuccessToast('Bem-vindo ao sistema!', 'Sucesso!')
          await router.push('/dashboard')
        }
      } else {
        currentStep.value = 1
      }
    }
  } catch (error: any) {
    const errorMessage = error?.response?.data?.message || error?.message || "Erro ao cadastrar empresa"
    showErrorToast(errorMessage, "Erro!")
  } finally {
    registeringCompany.value = false
  }
}

const handleLogout = async () => {
  await logout()
  router.push('/login')
}

// Company association methods
const isCompanySelected = (companyId: number) => {
  return selectedCompanyIds.value.includes(companyId)
}

const selectCompany = (companyId: number) => {
  // Seleção única - substitui a empresa selecionada
  selectedCompanyIds.value = [companyId]
}

const loadAvailableCompanies = async () => {
  loadingCompanies.value = true
  try {
    const queryParams = new URLSearchParams()
    if (searchCompanies.value) queryParams.append('search', searchCompanies.value)
    queryParams.append('page', companiesPagination.value.current_page.toString())
    queryParams.append('per_page', companiesPagination.value.per_page.toString())

    const url = `/companies/available?${queryParams.toString()}`
    const response = await http.get(url)

    publicCompanies.value = response.data || []
    companiesPagination.value = {
      current_page: response.current_page || 1,
      last_page: response.last_page || 1,
      per_page: response.per_page || 6,
      total: response.total || 0
    }
  } catch (error) {
    showErrorToast('Erro ao carregar empresas', 'Erro!')
  } finally {
    loadingCompanies.value = false
  }
}

const handleCompanySearch = async () => {
  // Reset para página 1 quando pesquisar
  companiesPagination.value.current_page = 1
  await loadAvailableCompanies()
}

const handleCompanyPageChange = async (page: number) => {
  companiesPagination.value.current_page = page
  await loadAvailableCompanies()
}

const associateToCompanies = async () => {
  if (selectedCompanyIds.value.length === 0 || !selectedProfileId.value || !user.value?.id) {
    showErrorToast('Selecione uma empresa e um perfil', 'Atenção!')
    return
  }

  associating.value = true
  try {
    // Fazer a associação com a empresa selecionada
    const companyId = selectedCompanyIds.value[0]
    const response = await http.post('/register/associate', {
      user_id: user.value!.id,
      company_id: companyId,
      profile_id: selectedProfileId.value
    })

    showSuccessToast('Associação realizada com sucesso!', 'Sucesso!')

    // Atualizar tenants disponíveis com os dados retornados
    const company = response.data.company
    const tenant = {
      id: company.id,
      name: company.name,
      profile_id: selectedProfileId.value || undefined,
      profile_name: availableProfiles.value.find(p => p.id === selectedProfileId.value)?.display_name || undefined
    }

    // Usar setAvailableTenants do useTenant (salva no localStorage automaticamente)
    setAvailableTenants([tenant])

    // Selecionar a empresa associada
    await switchTenant(tenant, user.value?.companies)

      // Redirecionar para o dashboard
      showSuccessToast('Bem-vindo ao sistema!', 'Sucesso!')
      await router.push('/dashboard')
  } catch (error: any) {
    const errorMessage = error.response?.data?.message || 'Erro ao associar empresa'
    showErrorToast(errorMessage, 'Erro!')
  } finally {
    associating.value = false
  }
}

// Lifecycle
onMounted(async () => {
  // Carrega os tenants disponíveis
  await loadAvailableTenants()
  loadAvailableCompanies()

  // Carrega fusos horários para o formulário de empresa
  await loadTimezones()

  // Se não tiver empresas associadas, iniciar novo fluxo de seleção de perfil
  if (availableTenants.value.length === 0) {
    await loadAvailableProfiles()
    currentStep.value = 1 // Forçar para seleção de perfil
    loading.value = false
    return
  }

  // Se já tem empresas associadas, vai direto para seleção de empresa
  currentStep.value = 0 // Voltar para seleção de empresa

  // Se só houver uma empresa, seleciona automaticamente
  if (availableTenants.value.length === 1) {
    const tenant = availableTenants.value[0]
    if (tenant) {
      selectedTenantId.value = tenant.id
    }
  }

  loading.value = false
})
</script>

<style scoped>
/* Container */
.select-tenant-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #1e293b 0%, #334155 50%, #475569 100%);
  position: relative;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Background Elements */
.background-elements {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 0;
}

.floating-shape {
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  animation: float 6s ease-in-out infinite;
}

.shape-1 {
  width: 80px;
  height: 80px;
  top: 15%;
  left: 10%;
  animation-delay: 0s;
}

.shape-2 {
  width: 100px;
  height: 100px;
  top: 60%;
  right: 15%;
  animation-delay: 2s;
}

.shape-3 {
  width: 60px;
  height: 60px;
  bottom: 20%;
  left: 20%;
  animation-delay: 4s;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px) rotate(0deg);
  }
  50% {
    transform: translateY(-20px) rotate(180deg);
  }
}

/* Card */
.select-card {
  position: relative;
  z-index: 1;
  border-radius: 20px;
  backdrop-filter: blur(20px);
  background: rgba(255, 255, 255, 0.95);
  border: 1px solid rgba(255, 255, 255, 0.3);
  box-shadow:
    0 15px 35px -5px rgba(0, 0, 0, 0.2),
    0 0 0 1px rgba(255, 255, 255, 0.1);
  overflow: hidden;
  animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Header */
.select-header {
  background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
  padding: 2rem 1.5rem;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.select-header::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
  animation: rotate 20s linear infinite;
}

@keyframes rotate {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.header-content {
  position: relative;
  z-index: 1;
}

.header-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: white;
  margin: 0;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.header-subtitle {
  color: rgba(255, 255, 255, 0.9);
  font-size: 1rem;
  margin: 0.5rem 0 0 0;
}

/* Content */
.content-section {
  padding: 2rem 1.5rem;
}

/* User Info */
.user-info-box {
  display: flex;
  align-items: center;
  padding: 1rem;
  background: rgba(30, 41, 59, 0.05);
  border-radius: 12px;
  border: 1px solid rgba(30, 41, 59, 0.1);
}

.user-details {
  flex: 1;
}

.user-name {
  font-weight: 600;
  font-size: 1rem;
  color: #1e293b;
}

.user-email {
  font-size: 0.875rem;
  color: #64748b;
}

/* Tenants Grid */
.tenants-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1rem;
}

.tenant-card {
  border: 2px solid rgba(30, 41, 59, 0.1);
  border-radius: 16px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
  background: white;
}

.tenant-card:hover {
  border-color: rgba(30, 41, 59, 0.3);
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(30, 41, 59, 0.15);
}

.tenant-card--selected {
  border-color: #059669;
  background: rgba(5, 150, 105, 0.05);
  box-shadow: 0 8px 20px rgba(5, 150, 105, 0.2);
}

.tenant-card-content {
  padding: 1.5rem;
  position: relative;
}

.tenant-icon {
  display: flex;
  justify-content: center;
  margin-bottom: 1rem;
}

.tenant-info {
  text-align: center;
}

.tenant-name {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 0.5rem 0;
}

.selected-indicator {
  position: absolute;
  top: 1rem;
  right: 1rem;
}

/* Action Button */
.action-btn {
  font-weight: 600;
  text-transform: none;
  letter-spacing: 0.5px;
  transition: all 0.3s ease;
}

.action-btn:not(:disabled):hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(30, 41, 59, 0.3);
}

/* Footer */
.select-footer {
  padding: 1rem 1.5rem;
  background: rgba(30, 41, 59, 0.03);
  border-top: 1px solid rgba(30, 41, 59, 0.1);
  justify-content: center;
}

/* Dark Theme */
.v-theme--dark .select-card {
  background: rgba(30, 41, 59, 0.95);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.v-theme--dark .user-info-box {
  background: rgba(51, 65, 85, 0.3);
  border-color: rgba(255, 255, 255, 0.1);
}

.v-theme--dark .user-name {
  color: #f1f5f9;
}

.v-theme--dark .user-email {
  color: #94a3b8;
}

.v-theme--dark .tenant-card {
  background: rgba(51, 65, 85, 0.5);
  border-color: rgba(255, 255, 255, 0.1);
}

.v-theme--dark .tenant-card:hover {
  border-color: rgba(255, 255, 255, 0.2);
}

.v-theme--dark .tenant-card--selected {
  background: rgba(5, 150, 105, 0.15);
  border-color: #10b981;
}

.v-theme--dark .tenant-name {
  color: #f1f5f9;
}

.v-theme--dark .select-footer {
  background: rgba(51, 65, 85, 0.3);
  border-top-color: rgba(255, 255, 255, 0.1);
}

/* Profile Selection Styles */
.profile-selection-section {
  max-width: 100%;
}

.profile-card {
  cursor: pointer;
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.profile-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.profile-card--selected {
  border-color: rgba(var(--v-theme-primary), 0.5);
  background: rgba(var(--v-theme-primary), 0.05);
  box-shadow: 0 8px 24px rgba(var(--v-theme-primary), 0.2);
}

/* Company Association Styles */
.no-companies-section {
  max-width: 100%;
}

.companies-loading {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.companies-list-container {
  max-height: 350px;
  overflow-y: auto;
  padding-right: 8px;
  margin-bottom: 12px;
}

.companies-list-container::-webkit-scrollbar {
  width: 6px;
}

.companies-list-container::-webkit-scrollbar-track {
  background: rgba(var(--v-theme-surface-variant), 0.3);
  border-radius: 3px;
}

.companies-list-container::-webkit-scrollbar-thumb {
  background: rgba(var(--v-theme-primary), 0.3);
  border-radius: 3px;
}

.company-select-card {
  cursor: pointer;
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.company-select-card:hover {
  border-color: rgba(var(--v-theme-primary), 0.3);
  transform: translateX(4px);
}

.company-selected {
  border-color: rgba(var(--v-theme-success), 0.5) !important;
  background: rgba(var(--v-theme-success), 0.05);
}

/* Responsive */
@media (max-width: 768px) {
  .select-tenant-container {
    padding: 0.5rem;
  }

  .select-header {
    padding: 1.5rem 1rem;
  }

  .header-title {
    font-size: 1.5rem;
  }

  .content-section {
    padding: 1.5rem 1rem;
  }

  .tenants-grid {
    grid-template-columns: 1fr;
  }

  .companies-list-container {
    max-height: 250px;
  }

  .company-select-card:hover {
    transform: none;
  }
}

@media (max-width: 480px) {
  .select-header {
    padding: 1.25rem 0.75rem;
  }

  .header-title {
    font-size: 1.35rem;
  }

  .content-section {
    padding: 1.25rem 0.75rem;
  }
}
</style>

