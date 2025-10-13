<template>
  <BasePage
    title="Meu Perfil"
    subtitle="Gerencie suas informações pessoais e preferências"
    :breadcrumbs="[{ title: 'Perfil' }]"
  >
    <template #content>
      <v-row v-if="user">
        <!-- Coluna Esquerda - Card do Usuário -->
        <v-col cols="12" md="4">
          <v-card class="profile-card" elevation="2">
            <v-card-text class="text-center pa-6">
              <!-- Avatar -->
              <v-avatar size="120" color="primary" class="mb-4">
                <v-icon size="60" color="white">mdi-account</v-icon>
              </v-avatar>

              <!-- Nome do Usuário -->
              <h2 class="text-h5 font-weight-bold mb-2">{{ user?.name }}</h2>
              <p class="text-body-2 text-medium-emphasis mb-1">{{ user?.email }}</p>

              <!-- Badge do Perfil -->
              <v-chip
                :color="getProfileColor(user?.profile?.name)"
                variant="tonal"
                class="mt-3"
                size="small"
              >
                <v-icon start size="16">mdi-shield-account</v-icon>
                {{ user?.profile?.display_name }}
              </v-chip>

              <!-- Informações Adicionais -->
              <v-divider class="my-4" />

              <div v-if="user?.phone" class="info-item mb-3">
                <v-icon size="18" color="primary" class="mr-2">mdi-phone</v-icon>
                <span class="text-body-2">{{ user.phone }}</span>
                <v-chip
                  v-if="user.has_whatsapp"
                  color="success"
                  size="x-small"
                  class="ml-1"
                >
                  <v-icon size="10">mdi-whatsapp</v-icon>
                </v-chip>
              </div>

              <!-- Estatísticas -->
              <v-card variant="tonal" color="primary" class="mt-4">
                <v-card-text class="pa-3">
                  <div class="d-flex justify-space-around">
                    <div class="text-center">
                      <div class="text-h6 font-weight-bold">{{ userCompanies.length }}</div>
                      <div class="text-caption">Empresas</div>
                    </div>
                  </div>
                </v-card-text>
              </v-card>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Coluna Direita - Abas de Conteúdo -->
        <v-col cols="12" md="8">
          <v-card elevation="2" class="content-card">
            <v-tabs
              v-model="currentTab"
              bg-color="primary"
              color="white"
              grow
            >
              <v-tab value="personal">
                <v-icon start>mdi-account-edit</v-icon>
                <span class="d-none d-sm-inline">Dados Pessoais</span>
                <span class="d-sm-none">Dados</span>
              </v-tab>
              <v-tab value="companies">
                <v-icon start>mdi-office-building</v-icon>
                Empresas
              </v-tab>
              <v-tab value="security">
                <v-icon start>mdi-shield-lock</v-icon>
                Segurança
              </v-tab>
            </v-tabs>

            <v-window v-model="currentTab">
              <!-- Aba: Dados Pessoais -->
              <v-window-item value="personal">
                <v-card-text class="pa-6">
                  <h3 class="text-h6 font-weight-bold mb-4">Informações Pessoais</h3>

                  <v-form ref="personalForm" @submit.prevent="savePersonalInfo">
                    <v-row>
                      <v-col cols="12">
                        <v-text-field
                          v-model="personalData.name"
                          label="Nome Completo"
                          prepend-inner-icon="mdi-account"
                          variant="outlined"
                          density="comfortable"
                          :rules="[rules.required]"
                          required
                        />
                      </v-col>

                      <v-col cols="12">
                        <v-text-field
                          v-model="personalData.email"
                          label="E-mail"
                          prepend-inner-icon="mdi-email"
                          variant="outlined"
                          density="comfortable"
                          type="email"
                          :rules="[rules.required, rules.email]"
                          required
                          readonly
                          hint="O e-mail não pode ser alterado"
                          persistent-hint
                        />
                      </v-col>

                      <v-col cols="12">
                        <v-text-field
                          v-model="formattedPhone"
                          label="Telefone"
                          prepend-inner-icon="mdi-phone"
                          variant="outlined"
                          density="comfortable"
                          maxlength="15"
                          @input="handlePhoneInput"
                        />
                      </v-col>

                      <v-col cols="12">
                        <v-switch
                          v-model="personalData.has_whatsapp"
                          color="success"
                          :disabled="!personalData.phone"
                          hide-details
                        >
                          <template v-slot:label>
                            <div class="d-flex align-center">
                              <v-icon color="success" size="20" class="mr-2">mdi-whatsapp</v-icon>
                              <span>Este número tem WhatsApp</span>
                            </div>
                          </template>
                        </v-switch>
                      </v-col>
                    </v-row>

                    <v-divider class="my-6" />

                    <div class="d-flex justify-end gap-2">
                      <v-btn
                        variant="outlined"
                        @click="resetPersonalData"
                      >
                        Cancelar
                      </v-btn>
                      <v-btn
                        color="primary"
                        type="submit"
                        :loading="savingPersonal"
                      >
                        <v-icon start>mdi-content-save</v-icon>
                        Salvar Alterações
                      </v-btn>
                    </div>
                  </v-form>
                </v-card-text>
              </v-window-item>

              <!-- Aba: Empresas -->
              <v-window-item value="companies">
                <v-card-text class="pa-6">
                  <div class="d-flex justify-space-between align-center mb-4">
                    <div>
                      <h3 class="text-h6 font-weight-bold">Minhas Empresas</h3>
                      <p class="text-body-2 text-medium-emphasis">Empresas às quais você está vinculado</p>
                    </div>
                    <v-btn
                      color="primary"
                      prepend-icon="mdi-plus"
                      size="small"
                      @click="openAssociateModal"
                    >
                      Vincular
                    </v-btn>
                  </div>

                  <!-- Loading -->
                  <div v-if="loadingCompanies" class="text-center py-8">
                    <v-progress-circular indeterminate color="primary" />
                  </div>

                  <!-- Empty State -->
                  <v-card v-else-if="userCompanies.length === 0" class="text-center pa-8" elevation="0" variant="tonal">
                    <v-icon size="64" color="grey-lighten-1">mdi-office-building-outline</v-icon>
                    <h3 class="text-h6 mt-4 mb-2">Nenhuma empresa vinculada</h3>
                    <p class="text-body-2 text-medium-emphasis mb-4">
                      Vincule-se a uma empresa para começar
                    </p>
                    <v-btn
                      color="primary"
                      variant="flat"
                      prepend-icon="mdi-plus"
                      @click="openAssociateModal"
                    >
                      Vincular à Primeira Empresa
                    </v-btn>
                  </v-card>

                  <!-- Lista de Empresas -->
                  <v-row v-else>
                    <v-col
                      v-for="company in userCompanies"
                      :key="company.id"
                      cols="12"
                      sm="6"
                    >
                      <v-card class="company-item-card" elevation="2" hover>
                        <v-card-title class="d-flex align-center pa-4">
                          <v-avatar color="primary" size="48" class="mr-3">
                            <v-icon color="white">mdi-office-building</v-icon>
                          </v-avatar>
                          <div class="flex-grow-1">
                            <div class="text-subtitle-1 font-weight-bold">{{ company.name }}</div>
                          </div>

                          <!-- Badge Empresa Atual -->
                          <v-chip
                            v-if="currentTenant?.id === company.id"
                            color="success"
                            size="x-small"
                            variant="flat"
                          >
                            Atual
                          </v-chip>
                        </v-card-title>

                        <v-card-actions class="pa-4 pt-0">
                          <v-btn
                            v-if="currentTenant?.id !== company.id"
                            variant="tonal"
                            color="primary"
                            size="small"
                            prepend-icon="mdi-swap-horizontal"
                            @click="switchToCompany(company)"
                          >
                            Trocar
                          </v-btn>
                          <v-spacer />
                          <v-btn
                            variant="text"
                            color="error"
                            size="small"
                            icon="mdi-link-off"
                            @click="confirmUnlink(company)"
                            :disabled="userCompanies.length === 1"
                          >
                            <v-icon>mdi-link-off</v-icon>
                            <v-tooltip activator="parent" location="top">
                              {{ userCompanies.length === 1 ? 'Você precisa estar vinculado a pelo menos uma empresa' : 'Desvincular' }}
                            </v-tooltip>
                          </v-btn>
                        </v-card-actions>
                      </v-card>
                    </v-col>
                  </v-row>
                </v-card-text>
              </v-window-item>

              <!-- Aba: Segurança -->
              <v-window-item value="security">
                <v-card-text class="pa-6">
                  <h3 class="text-h6 font-weight-bold mb-4">Alterar Senha</h3>

                  <v-form ref="securityForm" @submit.prevent="changePassword">
                    <v-row>
                      <v-col cols="12">
                        <v-text-field
                          v-model="securityData.current_password"
                          label="Senha Atual"
                          prepend-inner-icon="mdi-lock"
                          variant="outlined"
                          density="comfortable"
                          :type="showCurrentPassword ? 'text' : 'password'"
                          :append-inner-icon="showCurrentPassword ? 'mdi-eye-off' : 'mdi-eye'"
                          @click:append-inner="showCurrentPassword = !showCurrentPassword"
                          :rules="[rules.required]"
                          required
                        />
                      </v-col>

                      <v-col cols="12">
                        <v-text-field
                          v-model="securityData.new_password"
                          label="Nova Senha"
                          prepend-inner-icon="mdi-lock-plus"
                          variant="outlined"
                          density="comfortable"
                          :type="showNewPassword ? 'text' : 'password'"
                          :append-inner-icon="showNewPassword ? 'mdi-eye-off' : 'mdi-eye'"
                          @click:append-inner="showNewPassword = !showNewPassword"
                          :rules="[rules.required, rules.minLength(6)]"
                          required
                        />
                      </v-col>

                      <v-col cols="12">
                        <v-text-field
                          v-model="securityData.new_password_confirmation"
                          label="Confirmar Nova Senha"
                          prepend-inner-icon="mdi-lock-check"
                          variant="outlined"
                          density="comfortable"
                          :type="showConfirmPassword ? 'text' : 'password'"
                          :append-inner-icon="showConfirmPassword ? 'mdi-eye-off' : 'mdi-eye'"
                          @click:append-inner="showConfirmPassword = !showConfirmPassword"
                          :rules="[rules.required, rules.passwordMatch]"
                          required
                        />
                      </v-col>
                    </v-row>

                    <v-alert type="info" variant="tonal" class="mt-4">
                      <div class="text-body-2">
                        <strong>Dica:</strong> Use uma senha forte com pelo menos 6 caracteres.
                      </div>
                    </v-alert>

                    <v-divider class="my-6" />

                    <div class="d-flex justify-end gap-2">
                      <v-btn
                        variant="outlined"
                        @click="resetSecurityData"
                      >
                        Cancelar
                      </v-btn>
                      <v-btn
                        color="primary"
                        type="submit"
                        :loading="savingSecurity"
                      >
                        <v-icon start>mdi-shield-check</v-icon>
                        Alterar Senha
                      </v-btn>
                    </div>
                  </v-form>
                </v-card-text>
              </v-window-item>
            </v-window>
          </v-card>
        </v-col>
      </v-row>
    </template>
  </BasePage>

  <!-- Modal de Associar Empresas -->
  <v-dialog v-model="showAssociateModal" max-width="800" scrollable>
    <v-card>
      <v-card-title class="d-flex align-center pa-4 bg-primary">
        <v-icon class="mr-2">mdi-office-building-plus</v-icon>
        <span>Vincular-se a Empresas</span>
        <v-spacer />
        <v-btn icon="mdi-close" variant="text" @click="showAssociateModal = false" />
      </v-card-title>

      <v-card-text class="pa-4">
        <!-- Busca -->
        <v-text-field
          v-model="searchCompanies"
          label="Buscar empresas"
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          density="comfortable"
          clearable
          hide-details
          class="mb-4"
          @update:model-value="handleSearchCompanies"
        />

        <!-- Loading -->
        <div v-if="loadingPublicCompanies" class="text-center py-8">
          <v-progress-circular indeterminate color="primary" />
          <p class="text-body-2 text-medium-emphasis mt-2">Carregando empresas...</p>
        </div>

        <!-- Lista de Empresas Disponíveis -->
        <div v-else-if="availableCompanies.length > 0">
          <p class="text-body-2 text-medium-emphasis mb-3">
            Selecione as empresas que deseja se vincular
          </p>

          <v-list class="pa-0">
            <v-list-item
              v-for="company in availableCompanies"
              :key="company.id"
              class="company-list-item mb-2"
              :class="{ 'selected': isSelected(company.id) }"
              @click="toggleSelection(company.id)"
            >
              <template v-slot:prepend>
                <v-checkbox-btn
                  :model-value="isSelected(company.id)"
                  color="primary"
                />
              </template>

              <v-list-item-title class="font-weight-medium">
                {{ company.name }}
              </v-list-item-title>

              <v-list-item-subtitle v-if="company.person_type">
                {{ company.person_type === 'legal' ? 'Pessoa Jurídica' : 'Pessoa Física' }}
              </v-list-item-subtitle>

              <template v-slot:append>
                <v-chip
                  v-if="isAlreadyLinked(company.id)"
                  color="success"
                  size="x-small"
                  variant="tonal"
                >
                  Vinculado
                </v-chip>
              </template>
            </v-list-item>
          </v-list>

          <!-- Paginação -->
          <div v-if="companiesPagination.last_page > 1" class="d-flex justify-center mt-4">
            <v-pagination
              v-model="companiesPagination.current_page"
              :length="companiesPagination.last_page"
              :total-visible="5"
              @update:model-value="handlePageChange"
            />
          </div>
        </div>

        <!-- Empty State -->
        <v-card v-else variant="tonal" class="text-center pa-8">
          <v-icon size="48" color="grey-lighten-1">mdi-office-building-off</v-icon>
          <p class="text-body-2 text-medium-emphasis mt-2 mb-0">
            Nenhuma empresa encontrada
          </p>
        </v-card>
      </v-card-text>

      <v-card-actions class="pa-4 bg-grey-lighten-4">
        <v-chip v-if="selectedCompanyIds.length > 0" color="primary" variant="tonal">
          {{ selectedCompanyIds.length }} empresa(s) selecionada(s)
        </v-chip>
        <v-spacer />
        <v-btn
          variant="text"
          @click="showAssociateModal = false"
        >
          Cancelar
        </v-btn>
        <v-btn
          color="primary"
          variant="flat"
          :disabled="selectedCompanyIds.length === 0"
          :loading="associating"
          @click="handleAssociate"
        >
          Vincular ({{ selectedCompanyIds.length }})
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>

  <!-- Modal de Confirmação de Desvincular -->
  <v-dialog v-model="showUnlinkModal" max-width="500">
    <v-card>
      <v-card-title class="pa-4 bg-error">
        <v-icon class="mr-2">mdi-alert</v-icon>
        Desvincular Empresa
      </v-card-title>

      <v-card-text class="pa-6">
        <p class="text-body-1 mb-2">
          Tem certeza que deseja se desvincular da empresa <strong>{{ companyToUnlink?.name }}</strong>?
        </p>
        <v-alert type="warning" variant="tonal" class="mt-4">
          <div class="text-body-2">
            <strong>Atenção:</strong> Você perderá o acesso aos dados e funcionalidades desta empresa.
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
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import { useTenant } from '@/composables/useTenant'
import { useHttp } from '@/composables/useHttp'
import { useMask } from '@/composables/useMask'
import { showSuccessToast, showErrorToast } from '@/utils/swal'
import BasePage from '@/components/BasePage.vue'

const router = useRouter()
const { user } = useAuth()
const { currentTenant, availableTenants, switchTenant, setAvailableTenants } = useTenant()
const http = useHttp()
const { formatPhone } = useMask()

// State
const currentTab = ref('personal')
const loadingCompanies = ref(false)
const userCompaniesDetailed = ref<any[]>([])
const loadingPublicCompanies = ref(false)
const showAssociateModal = ref(false)
const showUnlinkModal = ref(false)
const searchCompanies = ref('')
const selectedCompanyIds = ref<number[]>([])
const associating = ref(false)
const unlinking = ref(false)
const companyToUnlink = ref<any>(null)
const publicCompanies = ref<any[]>([])
const companiesPagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0
})

// Personal Data
const savingPersonal = ref(false)
const personalData = ref({
  name: '',
  email: '',
  phone: '',
  has_whatsapp: false
})

// Security Data
const savingSecurity = ref(false)
const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)
const securityData = ref({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
})

// Validation Rules
const rules = {
  required: (value: any) => !!value || 'Campo obrigatório',
  email: (value: string) => {
    const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return pattern.test(value) || 'E-mail inválido'
  },
  minLength: (min: number) => (value: string) => {
    return (value && value.length >= min) || `Mínimo de ${min} caracteres`
  },
  passwordMatch: (value: string) => {
    return value === securityData.value.new_password || 'As senhas não coincidem'
  }
}

// Computed
const userCompanies = computed(() => {
  return userCompaniesDetailed.value.length > 0
    ? userCompaniesDetailed.value
    : availableTenants.value || []
})

const availableCompanies = computed(() => {
  return publicCompanies.value.filter(company =>
    !isAlreadyLinked(company.id)
  )
})

const formattedPhone = computed({
  get: () => personalData.value.phone ? formatPhone(personalData.value.phone) : '',
  set: (value: string) => {
    personalData.value.phone = value.replace(/\D/g, '')
  }
})

// Methods
const getProfileColor = (profileName: string) => {
  const colors: Record<string, string> = {
    admin: 'error',
    owner: 'primary',
    professional: 'info',
    supervisor: 'warning',
    client: 'success'
  }
  return colors[profileName] || 'default'
}

const isAlreadyLinked = (companyId: number) => {
  return userCompanies.value.some(c => c.id === companyId)
}

const isSelected = (companyId: number) => {
  return selectedCompanyIds.value.includes(companyId)
}

const toggleSelection = (companyId: number) => {
  if (isAlreadyLinked(companyId)) return

  const index = selectedCompanyIds.value.indexOf(companyId)
  if (index > -1) {
    selectedCompanyIds.value.splice(index, 1)
  } else {
    selectedCompanyIds.value.push(companyId)
  }
}

const switchToCompany = (company: any) => {
  switchTenant(company)
  showSuccessToast(`Agora você está em: ${company.name}`, 'Empresa Alterada')
  router.push('/dashboard')
}

const confirmUnlink = (company: any) => {
  companyToUnlink.value = company
  showUnlinkModal.value = true
}

const handlePhoneInput = (event: any) => {
  const value = typeof event === 'string' ? event : event.target?.value || ''
  personalData.value.phone = value.replace(/\D/g, '')
}

const resetPersonalData = () => {
  if (user.value) {
    personalData.value = {
      name: user.value.name || '',
      email: user.value.email || '',
      phone: user.value.phone || '',
      has_whatsapp: user.value.has_whatsapp || false
    }
  }
}

const resetSecurityData = () => {
  securityData.value = {
    current_password: '',
    new_password: '',
    new_password_confirmation: ''
  }
}

const savePersonalInfo = async () => {
  savingPersonal.value = true
  try {
    const response = await http.put(`/users/${user.value.id}`, {
      name: personalData.value.name,
      phone: personalData.value.phone,
      has_whatsapp: personalData.value.has_whatsapp
    })

    if (response.success) {
      showSuccessToast('Dados pessoais atualizados com sucesso!')
      await refreshUserData()
    }
  } catch (error: any) {
    console.error('Erro ao salvar dados pessoais:', error)
    showErrorToast(error.response?.data?.message || 'Erro ao atualizar dados pessoais')
  } finally {
    savingPersonal.value = false
  }
}

const changePassword = async () => {
  savingSecurity.value = true
  try {
    const response = await http.post('/auth/change-password', securityData.value)

    if (response.success) {
      showSuccessToast('Senha alterada com sucesso!')
      resetSecurityData()
    }
  } catch (error: any) {
    console.error('Erro ao alterar senha:', error)
    showErrorToast(error.response?.data?.message || 'Erro ao alterar senha')
  } finally {
    savingSecurity.value = false
  }
}

const openAssociateModal = async () => {
  showAssociateModal.value = true
  if (publicCompanies.value.length === 0) {
    await loadPublicCompanies()
  }
}

const loadPublicCompanies = async (page: number = 1) => {
  loadingPublicCompanies.value = true
  try {
    const params = new URLSearchParams()
    params.append('page', page.toString())
    params.append('per_page', companiesPagination.value.per_page.toString())
    if (searchCompanies.value) {
      params.append('search', searchCompanies.value)
    }

    const response = await http.get(`/companies/public?${params.toString()}`)
    publicCompanies.value = response.data || []
    companiesPagination.value = {
      current_page: response.current_page || 1,
      last_page: response.last_page || 1,
      per_page: response.per_page || 10,
      total: response.total || 0
    }
  } catch (error) {
    console.error('Erro ao carregar empresas:', error)
    showErrorToast('Erro ao carregar empresas disponíveis')
  } finally {
    loadingPublicCompanies.value = false
  }
}

const handleSearchCompanies = async () => {
  companiesPagination.value.current_page = 1
  await loadPublicCompanies(1)
}

const handlePageChange = async (page: number) => {
  await loadPublicCompanies(page)
}

const refreshUserData = async () => {
  try {
    const response = await http.get('/auth/me')
    if (response.data?.tenants) {
      setAvailableTenants(response.data.tenants)
      userCompaniesDetailed.value = response.data.tenants
    }
  } catch (error) {
    console.error('Erro ao atualizar dados do usuário:', error)
  }
}

const handleAssociate = async () => {
  if (selectedCompanyIds.value.length === 0) return

  associating.value = true
  try {
    const response = await http.post('/users/associate-companies', {
      company_ids: selectedCompanyIds.value
    })

    if (response.success) {
      await refreshUserData()
      showSuccessToast(response.message || 'Vinculação realizada com sucesso!')
      selectedCompanyIds.value = []
      showAssociateModal.value = false
      await loadPublicCompanies()
    }
  } catch (error: any) {
    console.error('Erro ao associar empresas:', error)
    showErrorToast(error.response?.data?.message || 'Erro ao vincular empresas')
  } finally {
    associating.value = false
  }
}

const handleUnlink = async () => {
  if (!companyToUnlink.value) return

  unlinking.value = true
  try {
    const response = await http.del(`/users/detach-company/${companyToUnlink.value.id}`)

    if (response.success) {
      await refreshUserData()

      // Se desvinculou da empresa atual, trocar para outra
      if (currentTenant.value?.id === companyToUnlink.value.id) {
        const remainingCompanies = userCompanies.value.filter(c => c.id !== companyToUnlink.value.id)
        if (remainingCompanies.length > 0) {
          switchTenant(remainingCompanies[0])
        }
      }

      showSuccessToast('Empresa desvinculada com sucesso!')
      showUnlinkModal.value = false
      companyToUnlink.value = null
    }
  } catch (error: any) {
    console.error('Erro ao desvincular empresa:', error)
    showErrorToast(error.response?.data?.message || 'Erro ao desvincular empresa')
  } finally {
    unlinking.value = false
  }
}

// Watch user changes to update form
watch(user, (newUser) => {
  if (newUser) {
    personalData.value = {
      name: newUser.name || '',
      email: newUser.email || '',
      phone: newUser.phone || '',
      has_whatsapp: newUser.has_whatsapp || false
    }
  }
}, { immediate: true })

// Lifecycle
onMounted(async () => {
  loadingCompanies.value = true
  try {
    await refreshUserData()
  } catch (error) {
    console.error('Erro ao carregar dados do usuário:', error)
  } finally {
    loadingCompanies.value = false
  }
})
</script>

<style scoped>
.profile-card {
  border-radius: 16px;
  position: sticky;
  top: 24px;
}

.content-card {
  border-radius: 16px;
}

.info-item {
  display: flex;
  align-items: center;
  justify-content: center;
}

.company-item-card {
  border-radius: 12px;
  transition: all 0.3s ease;
}

.company-item-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.company-list-item {
  border: 2px solid transparent;
  border-radius: 8px;
  transition: all 0.2s ease;
  cursor: pointer;
}

.company-list-item:hover {
  background: rgba(var(--v-theme-primary), 0.05);
  border-color: rgba(var(--v-theme-primary), 0.3);
}

.company-list-item.selected {
  background: rgba(var(--v-theme-primary), 0.1);
  border-color: rgb(var(--v-theme-primary));
}

/* Responsive */
@media (max-width: 960px) {
  .profile-card {
    position: relative;
    top: 0;
  }
}
</style>
