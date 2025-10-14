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
                      <v-col cols="12" md="6">
                        <v-text-field
                          v-model="personalData.name"
                          label="Nome Completo *"
                          prepend-inner-icon="mdi-account"
                          variant="outlined"
                          density="compact"
                          rounded="lg"
                          :rules="[rules.required]"
                          required
                          hint="Nome completo do usuário"
                          persistent-hint
                        />
                      </v-col>

                      <v-col cols="12" md="6">
                        <v-text-field
                          v-model="personalData.email"
                          label="E-mail *"
                          prepend-inner-icon="mdi-email"
                          variant="outlined"
                          density="compact"
                          rounded="lg"
                          type="email"
                          :rules="[rules.required, rules.email]"
                          required
                          readonly
                          hint="O e-mail não pode ser alterado"
                          persistent-hint
                        />
                      </v-col>

                      <v-col cols="12" md="6">
                        <v-text-field
                          v-model="formattedCpf"
                          label="CPF *"
                          prepend-inner-icon="mdi-card-account-details"
                          variant="outlined"
                          density="compact"
                          rounded="lg"
                          :rules="[rules.required, rules.cpf]"
                          required
                          maxlength="14"
                          hint="Seu CPF"
                          persistent-hint
                          @input="handleCpfInput"
                        />
                      </v-col>

                      <v-col cols="12" md="6">
                        <v-text-field
                          v-model="formattedPhone"
                          label="Telefone"
                          prepend-inner-icon="mdi-phone"
                          variant="outlined"
                          density="compact"
                          rounded="lg"
                          maxlength="15"
                          hint="Telefone de contato (opcional)"
                          persistent-hint
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

                    <div class="d-flex justify-end">
                      <v-btn
                        color="primary"
                        type="submit"
                        :loading="savingPersonal"
                        variant="flat"
                        prepend-icon="mdi-content-save"
                      >
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
                      v-show="company && company.id"
                    >
                      <v-card class="company-item-card" elevation="2" hover>
                        <v-card-title class="d-flex align-center pa-4">
                          <v-avatar
                            :color="isMainCompany(company) ? 'warning' : 'primary'"
                            size="48"
                            class="mr-3"
                          >
                            <v-icon color="white">
                              {{ isMainCompany(company) ? 'mdi-crown' : 'mdi-office-building' }}
                            </v-icon>
                          </v-avatar>
                          <div class="flex-grow-1">
                            <div class="text-subtitle-1 font-weight-bold">{{ company.name }}</div>
                            <div v-if="isMainCompany(company)" class="text-caption text-warning font-weight-bold">
                              <v-icon size="12" class="mr-1">mdi-crown</v-icon>
                              Empresa Principal
                            </div>
                          </div>

                          <!-- Badges -->
                          <div class="d-flex flex-column gap-1">
                            <!-- Badge Empresa Principal -->
                            <v-chip
                              v-if="isMainCompany(company)"
                              color="warning"
                              size="x-small"
                              variant="flat"
                            >
                              <v-icon start size="12">mdi-crown</v-icon>
                              Principal
                            </v-chip>

                            <!-- Badge Empresa Atual -->
                            <v-chip
                              v-if="currentTenant?.id === company.id"
                              color="success"
                              size="x-small"
                              variant="flat"
                            >
                              Atual
                            </v-chip>
                          </div>
                        </v-card-title>

                        <v-card-actions class="pa-4 pt-0">
                          <div class="d-flex flex-column gap-2 w-100">
                            <!-- Botões principais -->
                            <div class="d-flex gap-2">
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

                              <!-- Botão Empresa Principal -->
                              <v-btn
                                v-if="!isMainCompany(company)"
                                variant="tonal"
                                color="warning"
                                size="small"
                                prepend-icon="mdi-crown"
                                :loading="updatingMainCompany === company.id"
                                @click="setMainCompany(company)"
                              >
                                Definir como Principal
                              </v-btn>

                              <v-btn
                                v-else
                                variant="outlined"
                                color="warning"
                                size="small"
                                prepend-icon="mdi-crown-off"
                                :loading="updatingMainCompany === company.id"
                                @click="removeMainCompany(company)"
                              >
                                Remover Principal
                              </v-btn>
                            </div>

                            <!-- Botão de desvincular -->
                            <div class="d-flex justify-end">
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
                            </div>
                          </div>
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
                      <v-col cols="12" md="6">
                        <v-text-field
                          v-model="securityData.current_password"
                          label="Senha Atual *"
                          prepend-inner-icon="mdi-lock"
                          variant="outlined"
                          density="compact"
                          rounded="lg"
                          :type="showCurrentPassword ? 'text' : 'password'"
                          :append-inner-icon="showCurrentPassword ? 'mdi-eye-off' : 'mdi-eye'"
                          @click:append-inner="showCurrentPassword = !showCurrentPassword"
                          :rules="[rules.required]"
                          required
                          hint="Digite sua senha atual"
                          persistent-hint
                        />
                      </v-col>

                      <v-col cols="12" md="6">
                        <v-text-field
                          v-model="securityData.new_password"
                          label="Nova Senha *"
                          prepend-inner-icon="mdi-lock-plus"
                          variant="outlined"
                          density="compact"
                          rounded="lg"
                          :type="showNewPassword ? 'text' : 'password'"
                          :append-inner-icon="showNewPassword ? 'mdi-eye-off' : 'mdi-eye'"
                          @click:append-inner="showNewPassword = !showNewPassword"
                          :rules="[rules.required, rules.minLength(6)]"
                          required
                          hint="Mínimo de 6 caracteres"
                          persistent-hint
                        />
                      </v-col>

                      <v-col cols="12" md="6">
                        <v-text-field
                          v-model="securityData.new_password_confirmation"
                          label="Confirmar Nova Senha *"
                          prepend-inner-icon="mdi-lock-check"
                          variant="outlined"
                          density="compact"
                          rounded="lg"
                          :type="showConfirmPassword ? 'text' : 'password'"
                          :append-inner-icon="showConfirmPassword ? 'mdi-eye-off' : 'mdi-eye'"
                          @click:append-inner="showConfirmPassword = !showConfirmPassword"
                          :rules="[rules.required, rules.passwordMatch]"
                          required
                          hint="Confirme sua nova senha"
                          persistent-hint
                        />
                      </v-col>
                    </v-row>

                    <v-alert type="info" variant="tonal" class="mt-4">
                      <div class="text-body-2">
                        <strong>Dica:</strong> Use uma senha forte com pelo menos 6 caracteres.
                      </div>
                    </v-alert>

                    <v-divider class="my-6" />

                    <div class="d-flex justify-end">
                      <v-btn
                        color="primary"
                        type="submit"
                        :loading="savingSecurity"
                        variant="flat"
                        prepend-icon="mdi-shield-check"
                      >
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
const { user, updateUserData } = useAuth()
const { currentTenant, availableTenants, switchTenant, setAvailableTenants, setCurrentTenant } = useTenant()
const http = useHttp()
const { formatPhone, formatCPF, maskCPF } = useMask()

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
const updatingMainCompany = ref<number | null>(null)
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
  cpf: '',
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
  },
  cpf: (value: string) => {
    const cleaned = value?.replace(/\D/g, '') || ''
    return cleaned.length === 11 || 'CPF deve ter 11 dígitos'
  }
}

// Computed
const userCompanies = computed(() => {
  return userCompaniesDetailed.value.length > 0
    ? userCompaniesDetailed.value
    : user.value?.companies || []
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

const formattedCpf = computed({
  get: () => personalData.value.cpf ? formatCPF(personalData.value.cpf) : '',
  set: (value: string) => {
    personalData.value.cpf = value.replace(/\D/g, '')
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
  return userCompanies.value.some((c: any) => c.id === companyId)
}

const isMainCompany = (company: any) => {
  const isMain = company?.pivot?.is_main_company
  return isMain === true || isMain === 1
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

const handleCpfInput = (event: any) => {
  const maskedValue = maskCPF(event)
  personalData.value.cpf = maskedValue
}

const resetPersonalData = () => {
  if (user.value) {
    personalData.value = {
      name: user.value.name || '',
      email: user.value.email || '',
      phone: user.value.phone || '',
      cpf: user.value.cpf || '',
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
      email: personalData.value.email,
      phone: personalData.value.phone,
      cpf: personalData.value.cpf,
      has_whatsapp: personalData.value.has_whatsapp
    })

    if (response.success) {
      showSuccessToast('Dados pessoais atualizados com sucesso!')
      await updateUserData()
    }
  }  finally {
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
      await updateUserData()
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

    const response = await http.get(`/companies/available?${params.toString()}`)
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

const handleAssociate = async () => {
  if (selectedCompanyIds.value.length === 0) return

  associating.value = true
  try {
    const response = await http.post('/users/associate-companies', {
      company_ids: selectedCompanyIds.value
    })

    if (response.success) {
      await updateUserData()
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
      await updateUserData()

      // Se desvinculou da empresa atual, trocar para outra
      if (currentTenant.value?.id === companyToUnlink.value.id) {
        const remainingCompanies = userCompanies.value.filter((c: any) => c.id !== companyToUnlink.value.id)
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

const setMainCompany = async (company: any) => {
  updatingMainCompany.value = company.id
  try {
    const response = await http.post('/users/update-main-company', {
      company_id: company.id,
      is_main: true
    })

    if (response.success) {
      // Atualizar estado local PRIMEIRO se necessário
      if (response.data?.user?.companies) {
        userCompaniesDetailed.value = response.data.user.companies
      }

      // Depois atualizar dados do usuário
      await updateUserData()

      // Atualizar o flag is_main_company do tenant atual
      const { setCurrentTenant } = useTenant()
      if (currentTenant.value?.id === company.id) {
        // Se está definindo a empresa atual como principal
        setCurrentTenant({
          ...currentTenant.value,
          is_main_company: 1
        })
      } else {
        // Se está definindo outra empresa como principal, remover flag da atual
        setCurrentTenant({
          ...currentTenant.value,
          is_main_company: 0
        })
      }

      showSuccessToast(response.message || 'Empresa definida como principal!')
    }
  } catch (error: any) {
    console.error('Erro ao definir empresa principal:', error)
    showErrorToast(error.response?.data?.message || 'Erro ao definir empresa principal')
  } finally {
    updatingMainCompany.value = null
  }
}

const removeMainCompany = async (company: any) => {
  updatingMainCompany.value = company.id
  try {
    const response = await http.post('/users/update-main-company', {
      company_id: company.id,
      is_main: false
    })

    if (response.success) {
      // Atualizar estado local PRIMEIRO se necessário
      if (response.data?.user?.companies) {
        userCompaniesDetailed.value = response.data.user.companies
      }

      // Depois atualizar dados do usuário
      await updateUserData()

      // Atualizar apenas o flag is_main_company do tenant atual se for a empresa atual
      if (currentTenant.value?.id === company.id) {
        const { setCurrentTenant } = useTenant()
        setCurrentTenant({
          ...currentTenant.value,
          is_main_company: 0
        })
      }

      showSuccessToast(response.message || 'Empresa removida como principal!')
    }
  } catch (error: any) {
    console.error('Erro ao remover empresa principal:', error)
    showErrorToast(error.response?.data?.message || 'Erro ao remover empresa principal')
  } finally {
    updatingMainCompany.value = null
  }
}

// Watch user changes to update form
watch(user, (newUser) => {
  if (newUser) {
    personalData.value = {
      name: newUser.name || '',
      email: newUser.email || '',
      phone: newUser.phone || '',
      cpf: newUser.cpf || '',
      has_whatsapp: newUser.has_whatsapp || false
    }
  }
}, { immediate: true })

// Lifecycle
onMounted(async () => {
  loadingCompanies.value = true
  try {
    await updateUserData()
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
