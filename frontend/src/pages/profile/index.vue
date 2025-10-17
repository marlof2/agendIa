<template>
  <BasePage
    title="Meu Perfil"
    subtitle="Gerencie suas informações pessoais e preferências"
    :breadcrumbs="[{ title: 'Perfil' }]"
  >
    <template #content>
      <div v-if="user">
        <!-- Cards de Estatísticas do Usuário -->
        <v-row class="mb-8">
          <v-col cols="12" sm="6" md="3">
            <v-card class="profile-stats-card profile-stats-card--primary" elevation="0">
              <div class="profile-stats-card__background profile-stats-card__background--primary"></div>
              <v-card-text class="pa-6">
                <div class="d-flex align-center justify-space-between mb-4">
                  <div class="profile-stats-icon profile-stats-icon--primary">
                    <v-icon color="white" size="24">mdi-account-circle</v-icon>
                  </div>
                  <div class="profile-status">
                    <v-chip
                      v-if="currentProfileName"
                      :color="getProfileColor(currentProfileName)"
                      variant="flat"
                      size="small"
                    >
                      <v-icon start size="12">mdi-shield-account</v-icon>
                      {{ currentProfileName }}
                    </v-chip>
                  </div>
                </div>
                <div class="profile-stats-content">
                  <div class="profile-stats-number">{{ user?.name || 'Usuário' }}</div>
                  <div class="profile-stats-label">Nome do Usuário</div>
                  <div class="profile-stats-description">perfil atual</div>
                </div>
              </v-card-text>
            </v-card>
          </v-col>

          <v-col cols="12" sm="6" md="3">
            <v-card class="profile-stats-card profile-stats-card--info" elevation="0">
              <div class="profile-stats-card__background profile-stats-card__background--info"></div>
              <v-card-text class="pa-6">
                <div class="d-flex align-center justify-space-between mb-4">
                  <div class="profile-stats-icon profile-stats-icon--info">
                    <v-icon color="white" size="24">mdi-email-check</v-icon>
                  </div>
                  <div class="profile-status">
                    <v-chip
                      color="success"
                      variant="flat"
                      size="small"
                    >
                      <v-icon start size="12">mdi-check</v-icon>
                      Verificado
                    </v-chip>
                  </div>
                </div>
                <div class="profile-stats-content">
                  <div class="profile-stats-number">{{ user?.email || 'email@exemplo.com' }}</div>
                  <div class="profile-stats-label">E-mail</div>
                  <div class="profile-stats-description">conta ativa</div>
                </div>
              </v-card-text>
            </v-card>
          </v-col>

          <v-col cols="12" sm="6" md="3">
            <v-card class="profile-stats-card profile-stats-card--success" elevation="0">
              <div class="profile-stats-card__background profile-stats-card__background--success"></div>
              <v-card-text class="pa-6">
                <div class="d-flex align-center justify-space-between mb-4">
                  <div class="profile-stats-icon profile-stats-icon--success">
                    <v-icon color="white" size="24">mdi-phone-check</v-icon>
                  </div>
                  <div class="profile-status">
                    <v-chip
                      v-if="user?.phone"
                      :color="user.has_whatsapp ? 'success' : 'warning'"
                      variant="flat"
                      size="small"
                    >
                      <v-icon start size="12">{{ user.has_whatsapp ? 'mdi-whatsapp' : 'mdi-phone' }}</v-icon>
                      {{ user.has_whatsapp ? 'WhatsApp' : 'Telefone' }}
                    </v-chip>
                    <v-chip
                      v-else
                      color="error"
                      variant="flat"
                      size="small"
                    >
                      <v-icon start size="12">mdi-phone-off</v-icon>
                      Não informado
                    </v-chip>
                  </div>
                </div>
                <div class="profile-stats-content">
                  <div class="profile-stats-number">{{ formatPhone(user?.phone) }}</div>
                  <div class="profile-stats-label">Telefone</div>
                  <div class="profile-stats-description">contato principal</div>
                </div>
              </v-card-text>
            </v-card>
          </v-col>

          <v-col cols="12" sm="6" md="3">
            <v-card class="profile-stats-card profile-stats-card--warning" elevation="0">
              <div class="profile-stats-card__background profile-stats-card__background--warning"></div>
              <v-card-text class="pa-6">
                <div class="d-flex align-center justify-space-between mb-4">
                  <div class="profile-stats-icon profile-stats-icon--warning">
                    <v-icon color="white" size="24">{{ user?.cpf ? 'mdi-card-account-details' : 'mdi-alert-circle' }}</v-icon>
                  </div>
                  <div class="profile-status">
                    <v-chip
                      :color="user?.cpf ? 'success' : 'error'"
                      variant="flat"
                      size="small"
                    >
                      <v-icon start size="12">{{ user?.cpf ? 'mdi-check' : 'mdi-alert' }}</v-icon>
                      {{ user?.cpf ? 'Completo' : 'Pendente' }}
                    </v-chip>
                  </div>
                </div>
                <div class="profile-stats-content">
                  <div class="profile-stats-number">{{  formatCPF(user?.cpf) }}</div>
                  <div class="profile-stats-label">CPF Cadastrado</div>
                  <div class="profile-stats-description">documento obrigatório</div>
                </div>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>

        <!-- Conteúdo Principal com Abas -->
        <v-row>
          <v-col cols="12">
            <v-card elevation="0" class="profile-content-card">
              <v-tabs
                v-model="currentTab"
                bg-color="transparent"
                color="primary"
                align-tabs="start"
                class="profile-tabs"
              >
                <v-tab value="personal" class="profile-tab">
                  <v-icon start>mdi-account-edit</v-icon>
                  <span class="d-none d-sm-inline">Dados Pessoais</span>
                  <span class="d-sm-none">Dados</span>
                </v-tab>
                <v-tab value="security" class="profile-tab">
                  <v-icon start>mdi-shield-lock</v-icon>
                  Segurança
                </v-tab>
                <v-tab value="activity" class="profile-tab">
                  <v-icon start>mdi-history</v-icon>
                  <span class="d-none d-sm-inline">Atividade Recente</span>
                  <span class="d-sm-none">Atividade</span>
                </v-tab>
              </v-tabs>

              <v-window v-model="currentTab">
                <!-- Aba: Dados Pessoais -->
                <v-window-item value="personal">
                  <v-card-text class="pa-8">
                    <div class="profile-section-header mb-6">
                      <h3 class="text-h5 font-weight-bold d-flex align-center">
                        <v-icon class="mr-3" color="primary">mdi-account-edit</v-icon>
                        Informações Pessoais
                      </h3>
                      <p class="text-body-1 text-medium-emphasis mt-2">
                        Atualize suas informações pessoais e de contato
                      </p>
                    </div>

                    <v-form ref="personalForm" @submit.prevent="savePersonalInfo">
                      <v-row>
                        <v-col cols="12" md="6">
                          <v-text-field
                            v-model="personalData.name"
                            label="Nome Completo *"
                            prepend-inner-icon="mdi-account"
                            variant="outlined"
                            density="comfortable"
                            rounded="lg"
                            :rules="[rules.required]"
                            required
                            hint="Nome completo do usuário"
                            persistent-hint
                            class="profile-form-field"
                          />
                        </v-col>

                        <v-col cols="12" md="6">
                          <v-text-field
                            v-model="personalData.email"
                            label="E-mail *"
                            prepend-inner-icon="mdi-email"
                            variant="outlined"
                            density="comfortable"
                            rounded="lg"
                            type="email"
                            :rules="[rules.required, rules.email]"
                            required
                            readonly
                            hint="O e-mail não pode ser alterado"
                            persistent-hint
                            class="profile-form-field"
                          />
                        </v-col>

                        <v-col cols="12" md="6">
                          <v-text-field
                            v-model="formattedCpf"
                            label="CPF *"
                            prepend-inner-icon="mdi-card-account-details"
                            variant="outlined"
                            density="comfortable"
                            rounded="lg"
                            :rules="[rules.required, rules.cpf]"
                            required
                            maxlength="14"
                            hint="CPF é obrigatório para todos os usuários"
                            persistent-hint
                            @input="handleCpfInput"
                            disabled
                            class="profile-form-field"
                          />
                        </v-col>

                        <v-col cols="12" md="6">
                          <v-text-field
                            v-model="formattedPhone"
                            label="Telefone"
                            prepend-inner-icon="mdi-phone"
                            variant="outlined"
                            density="comfortable"
                            rounded="lg"
                            maxlength="15"
                            hint="Telefone de contato (opcional)"
                            persistent-hint
                            @input="handlePhoneInput"
                            class="profile-form-field"
                          />
                        </v-col>

                        <v-col cols="12">
                          <v-card variant="outlined" class="profile-switch-card">
                            <v-card-text class="pa-4">
                              <v-switch
                                v-model="personalData.has_whatsapp"
                                color="success"
                                :disabled="!personalData.phone"
                                hide-details
                                class="profile-switch"
                              >
                                <template v-slot:label>
                                  <div class="d-flex align-center">
                                    <v-icon color="success" size="20" class="mr-2">mdi-whatsapp</v-icon>
                                    <span class="text-body-1 font-weight-medium">Este número tem WhatsApp</span>
                                  </div>
                                </template>
                              </v-switch>
                              <p class="text-body-2 text-medium-emphasis mt-2 mb-0">
                                Ative para receber notificações via WhatsApp
                              </p>
                            </v-card-text>
                          </v-card>
                        </v-col>
                      </v-row>

                      <v-divider class="my-8" />

                      <div class="d-flex justify-end">
                        <v-btn
                          color="primary"
                          type="submit"
                          :loading="savingPersonal"
                          variant="flat"
                          prepend-icon="mdi-content-save"
                          size="large"
                          rounded="lg"
                        >
                          Salvar Alterações
                        </v-btn>
                      </div>
                    </v-form>
                  </v-card-text>
                </v-window-item>

                <!-- Aba: Segurança -->
                <v-window-item value="security">
                  <v-card-text class="pa-8">
                    <div class="profile-section-header mb-6">
                      <h3 class="text-h5 font-weight-bold d-flex align-center">
                        <v-icon class="mr-3" color="primary">mdi-shield-lock</v-icon>
                        Segurança da Conta
                      </h3>
                      <p class="text-body-1 text-medium-emphasis mt-2">
                        Altere sua senha e gerencie as configurações de segurança
                      </p>
                    </div>

                    <v-form ref="securityForm" @submit.prevent="changePassword">
                      <v-row>
                        <v-col cols="12">
                          <v-text-field
                            v-model="securityData.current_password"
                            label="Senha Atual *"
                            prepend-inner-icon="mdi-lock"
                            variant="outlined"
                            density="comfortable"
                            rounded="lg"
                            :type="showCurrentPassword ? 'text' : 'password'"
                            :append-inner-icon="showCurrentPassword ? 'mdi-eye-off' : 'mdi-eye'"
                            @click:append-inner="showCurrentPassword = !showCurrentPassword"
                            :rules="[rules.required]"
                            required
                            hint="Digite sua senha atual"
                            persistent-hint
                            class="profile-form-field"
                          />
                        </v-col>

                        <v-col cols="12">
                          <v-text-field
                            v-model="securityData.new_password"
                            label="Nova Senha *"
                            prepend-inner-icon="mdi-lock-plus"
                            variant="outlined"
                            density="comfortable"
                            rounded="lg"
                            :type="showNewPassword ? 'text' : 'password'"
                            :append-inner-icon="showNewPassword ? 'mdi-eye-off' : 'mdi-eye'"
                            @click:append-inner="showNewPassword = !showNewPassword"
                            :rules="[rules.required, rules.minLength(6)]"
                            required
                            hint="Mínimo de 6 caracteres"
                            persistent-hint
                            class="profile-form-field"
                          />
                        </v-col>

                        <v-col cols="12">
                          <v-text-field
                            v-model="securityData.new_password_confirmation"
                            label="Confirmar Nova Senha *"
                            prepend-inner-icon="mdi-lock-check"
                            variant="outlined"
                            density="comfortable"
                            rounded="lg"
                            :type="showConfirmPassword ? 'text' : 'password'"
                            :append-inner-icon="showConfirmPassword ? 'mdi-eye-off' : 'mdi-eye'"
                            @click:append-inner="showConfirmPassword = !showConfirmPassword"
                            :rules="[rules.required, rules.passwordMatch]"
                            required
                            hint="Confirme sua nova senha"
                            persistent-hint
                            class="profile-form-field"
                          />
                        </v-col>
                      </v-row>

                      <v-alert type="info" variant="tonal" class="mt-6">
                        <div class="text-body-2">
                          <strong>Dica:</strong> Use uma senha forte com pelo menos 6 caracteres, incluindo números e símbolos.
                        </div>
                      </v-alert>

                      <v-divider class="my-8" />

                      <div class="d-flex justify-end">
                        <v-btn
                          color="primary"
                          type="submit"
                          :loading="savingSecurity"
                          variant="flat"
                          prepend-icon="mdi-shield-check"
                          size="large"
                          rounded="lg"
                        >
                          Alterar Senha
                        </v-btn>
                      </div>
                    </v-form>
                  </v-card-text>
                </v-window-item>

                <!-- Aba: Atividade Recente -->
                <v-window-item value="activity">
                  <v-card-text class="pa-8">
                    <div class="profile-section-header mb-6">
                      <h3 class="text-h5 font-weight-bold d-flex align-center">
                        <v-icon class="mr-3" color="primary">mdi-history</v-icon>
                        Atividade Recente
                      </h3>
                      <p class="text-body-1 text-medium-emphasis mt-2">
                        Acompanhe suas últimas atividades no sistema
                      </p>
                    </div>

                    <!-- Placeholder para atividade recente -->
                    <v-card variant="outlined" class="profile-activity-card">
                      <v-card-text class="pa-6">
                        <div class="text-center py-8">
                          <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-history</v-icon>
                          <h4 class="text-h6 mb-2">Nenhuma atividade recente</h4>
                          <p class="text-body-2 text-medium-emphasis">
                            Suas atividades aparecerão aqui conforme você usa o sistema
                          </p>
                        </div>
                      </v-card-text>
                    </v-card>
                  </v-card-text>
                </v-window-item>
              </v-window>
            </v-card>
          </v-col>
        </v-row>
      </div>
    </template>
  </BasePage>
</template>

<script lang="ts" setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useAuth } from '@/composables/useAuth'
import { useTenant } from '@/composables/useTenant'
import { useHttp } from '@/composables/useHttp'
import { useMask } from '@/composables/useMask'
import { showSuccessToast, showErrorToast } from '@/utils/swal'
import BasePage from '@/components/BasePage.vue'

const { user, updateUserData } = useAuth()
const { getCurrentProfileName } = useTenant()
const http = useHttp()
const { formatCPF, maskCPF, formatPhone } = useMask()

// State
const currentTab = ref('personal')

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

// Perfil atual do usuário na empresa selecionada
const currentProfileName = computed(() => {
  return getCurrentProfileName()
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
  try {
    await updateUserData()
  } catch (error) {
    console.error('Erro ao carregar dados do usuário:', error)
  }
})
</script>

<style scoped>
/* ========================================
   PROFILE STATS CARDS
   ======================================== */
.profile-stats-card {
  position: relative;
  border-radius: 20px;
  overflow: hidden;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(20px);
  height: 100%;
}

.profile-stats-card:hover {
  transform: translateY(-8px) scale(1.02);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.profile-stats-card__background {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  opacity: 0.1;
}

.profile-stats-card__background--primary {
  background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
}

.profile-stats-card__background--info {
  background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
}

.profile-stats-card__background--success {
  background: linear-gradient(135deg, #059669 0%, #10b981 100%);
}

.profile-stats-card__background--warning {
  background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
}

.profile-stats-card :deep(.v-card-text) {
  position: relative;
  z-index: 1;
}

.profile-stats-icon {
  width: 56px;
  height: 56px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.profile-stats-icon--primary {
  background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
}

.profile-stats-icon--info {
  background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
}

.profile-stats-icon--success {
  background: linear-gradient(135deg, #059669 0%, #10b981 100%);
}

.profile-stats-icon--warning {
  background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
}

.profile-stats-content {
  margin-top: 8px;
}

.profile-stats-number {
  font-size: 1.1rem;
  font-weight: 600;
  line-height: 1.2;
  margin-bottom: 8px;
  color: rgba(var(--v-theme-on-surface), 0.87);
  word-break: break-word;
}

.profile-stats-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: rgba(var(--v-theme-on-surface), 0.6);
  margin-bottom: 4px;
}

.profile-stats-description {
  font-size: 0.75rem;
  color: rgba(var(--v-theme-on-surface), 0.5);
  font-weight: 500;
}

/* ========================================
   PROFILE MAIN CARD
   ======================================== */
.profile-main-card {
  position: relative;
  background: linear-gradient(135deg, #1e293b 0%, #334155 50%, #475569 100%);
  color: white;
  border-radius: 24px;
  overflow: hidden;
  box-shadow: 0 20px 40px rgba(30, 41, 59, 0.3);
}

.profile-main-card__background {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="dots" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="2" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23dots)"/></svg>');
  opacity: 0.3;
}

.profile-main-card :deep(.v-card-text) {
  color: white;
  position: relative;
  z-index: 1;
}

.profile-main-info {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 24px;
}

.profile-avatar {
  background: rgba(255, 255, 255, 0.2);
  border: 3px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
}

.profile-main-details {
  flex: 1;
  min-width: 200px;
}

.profile-main-title {
  font-size: 2.5rem;
  font-weight: 800;
  margin: 0 0 8px 0;
  background: linear-gradient(135deg, #ffffff 0%, #f0f0f0 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  text-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.profile-main-subtitle {
  font-size: 1.2rem;
  margin: 0 0 24px 0;
  color: rgba(255, 255, 255, 0.9);
  font-weight: 500;
}

.profile-main-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

/* ========================================
   PROFILE CONTENT CARD
   ======================================== */
.profile-content-card {
  border-radius: 20px;
  overflow: hidden;
  border: 1px solid rgba(var(--v-theme-outline), 0.12);
  background: rgba(var(--v-theme-surface), 0.8);
  backdrop-filter: blur(20px);
}

.profile-tabs {
  background: rgba(var(--v-theme-surface-variant), 0.5);
  border-bottom: 1px solid rgba(var(--v-theme-outline), 0.12);
}

.profile-tab {
  font-weight: 600;
  text-transform: none;
  letter-spacing: normal;
}

.profile-section-header {
  border-bottom: 1px solid rgba(var(--v-theme-outline), 0.12);
  padding-bottom: 16px;
}

.profile-form-field {
  transition: all 0.3s ease;
}

.profile-form-field:deep(.v-field--focused) {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(var(--v-theme-primary), 0.15);
}

.profile-switch-card {
  border-radius: 16px;
  background: rgba(var(--v-theme-surface-variant), 0.3);
  border: 1px solid rgba(var(--v-theme-outline), 0.08);
}

.profile-switch {
  margin: 0;
}

.profile-activity-card {
  border-radius: 16px;
  border: 2px dashed rgba(var(--v-theme-outline), 0.2);
  background: rgba(var(--v-theme-surface-variant), 0.2);
}

/* ========================================
   DARK THEME ADJUSTMENTS
   ======================================== */
.v-theme--dark .profile-main-card {
  background: linear-gradient(135deg, #334155 0%, #475569 50%, #64748b 100%);
}

.v-theme--dark .profile-main-title {
  background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.v-theme--dark .profile-main-subtitle {
  color: rgba(255, 255, 255, 0.9);
}

.v-theme--dark .profile-content-card {
  background: rgba(30, 41, 59, 0.5);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.v-theme--dark .profile-tabs {
  background: rgba(51, 65, 85, 0.3);
  border-bottom-color: rgba(255, 255, 255, 0.1);
}

.v-theme--dark .profile-stats-card {
  background: rgba(30, 41, 59, 0.5);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.v-theme--dark .profile-stats-card:hover {
  background: rgba(30, 41, 59, 0.7);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
}

.v-theme--dark .profile-switch-card {
  background: rgba(51, 65, 85, 0.3);
  border-color: rgba(255, 255, 255, 0.1);
}

.v-theme--dark .profile-activity-card {
  background: rgba(51, 65, 85, 0.2);
  border-color: rgba(255, 255, 255, 0.1);
}

/* ========================================
   RESPONSIVE DESIGN
   ======================================== */
@media (max-width: 768px) {
  .profile-main-title {
    font-size: 2rem;
  }

  .profile-main-info {
    flex-direction: column;
    text-align: center;
    gap: 16px;
  }

  .profile-main-badges {
    justify-content: center;
  }

  .profile-stats-number {
    font-size: 1rem;
  }

  .profile-form-field:deep(.v-field--focused) {
    transform: none;
  }
}

@media (max-width: 480px) {
  .profile-main-title {
    font-size: 1.75rem;
  }

  .profile-main-subtitle {
    font-size: 1rem;
  }

  .profile-avatar {
    width: 100px !important;
    height: 100px !important;
  }

  .profile-main-badges .v-chip {
    font-size: 0.75rem;
  }
}

/* ========================================
   ANIMATIONS
   ======================================== */
@keyframes slideInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.profile-stats-card {
  animation: slideInUp 0.6s ease-out;
}

.profile-stats-card:nth-child(1) {
  animation-delay: 0.1s;
}
.profile-stats-card:nth-child(2) {
  animation-delay: 0.2s;
}
.profile-stats-card:nth-child(3) {
  animation-delay: 0.3s;
}
.profile-stats-card:nth-child(4) {
  animation-delay: 0.4s;
}

.profile-main-card {
  animation: slideInUp 0.8s ease-out;
}

.profile-content-card {
  animation: slideInUp 1s ease-out;
}
</style>
