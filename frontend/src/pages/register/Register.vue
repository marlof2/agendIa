<template>
  <div class="register-page">
    <v-container fluid class="fill-height pa-0">
      <v-row no-gutters class="fill-height">
        <!-- Left Side - Branding -->
        <v-col cols="12" md="4" class="register-branding d-none d-md-flex">
          <div class="branding-content">
            <div class="brand-logo mb-8">
              <v-icon size="80" color="white">mdi-calendar-clock</v-icon>
              <h1 class="text-h3 font-weight-bold mt-4">AgendIA</h1>
              <p class="text-h6 mt-2">Agendamentos Inteligentes</p>
            </div>
            <v-card
              class="feature-card"
              elevation="4"
              color="rgba(255, 255, 255, 0.1)"
            >
              <v-card-text>
                <div class="feature-item">
                  <v-icon size="32" color="white">mdi-check-circle</v-icon>
                  <div class="ml-4">
                    <h3 class="text-h6">Gestão Simplificada</h3>
                    <p class="text-body-2">
                      Gerencie seus agendamentos de forma fácil e rápida
                    </p>
                  </div>
                </div>
                <v-divider class="my-4 opacity-20" />
                <div class="feature-item">
                  <v-icon size="32" color="white">mdi-calendar-multiple</v-icon>
                  <div class="ml-4">
                    <h3 class="text-h6">Multi-empresa</h3>
                    <p class="text-body-2">
                      Gerencie múltiplas empresas em um único sistema
                    </p>
                  </div>
                </div>
                <v-divider class="my-4 opacity-20" />
                <div class="feature-item">
                  <v-icon size="32" color="white">mdi-whatsapp</v-icon>
                  <div class="ml-4">
                    <h3 class="text-h6">Integração WhatsApp</h3>
                    <p class="text-body-2">
                      Notificações automáticas para seus clientes
                    </p>
                  </div>
                </div>
              </v-card-text>
            </v-card>
          </div>
        </v-col>

        <!-- Right Side - Register Form -->
        <v-col cols="12" md="8" class="register-form-section">
          <div class="form-container">
            <!-- Header -->
            <div class="form-header mb-8">
              <h2 class="text-h4 font-weight-bold mb-2">Criar Conta</h2>
              <p class="text-body-1 text-medium-emphasis">
                Preencha os dados para começar a usar o AgendIA
              </p>
            </div>

            <!-- Form -->
            <v-form
              ref="formRef"
              v-model="isValid"
              @submit.prevent="handleSubmit"
            >
              <v-card elevation="2" class="form-card">
                <v-card-text class="pa-6">
                  <!-- Dados Pessoais -->
                  <div class="mb-6">
                    <h3 class="text-h6 mb-4 d-flex align-center">
                      <v-icon size="20" class="mr-2">mdi-account</v-icon>
                      Dados Pessoais
                    </h3>

                    <v-row>
                      <v-col cols="12">
                        <v-text-field
                          v-model="form.name"
                          label="Nome completo *"
                          variant="outlined"
                          density="compact"
                          rounded="lg"
                          :rules="nameRules"
                          prepend-inner-icon="mdi-account"
                          required
                          hint="Digite seu nome completo"
                          persistent-hint
                        />
                      </v-col>
                    </v-row>

                    <v-row>
                      <v-col cols="12">
                        <v-text-field
                          v-model="form.email"
                          label="E-mail *"
                          type="email"
                          variant="outlined"
                          density="compact"
                          rounded="lg"
                          :rules="emailRules"
                          prepend-inner-icon="mdi-email"
                          required
                          hint="Digite seu e-mail"
                          persistent-hint
                        />
                      </v-col>
                    </v-row>

                    <v-row>
                      <v-col cols="12" md="6">
                        <v-text-field
                          v-model="formattedPhone"
                          label="Telefone"
                          variant="outlined"
                          density="compact"
                          rounded="lg"
                          :rules="phoneRules"
                          prepend-inner-icon="mdi-phone"
                          hint="Telefone de contato"
                          persistent-hint
                          @input="handlePhoneInput"
                          maxlength="15"
                        />
                      </v-col>

                      <v-col cols="12" md="6">
                        <v-switch
                          v-model="form.has_whatsapp"
                          label="É WhatsApp"
                          color="primary"
                          density="compact"
                          hide-details
                          :disabled="!form.phone"
                        >
                          <template #prepend>
                            <v-icon
                              :color="form.has_whatsapp ? 'success' : 'grey'"
                              >mdi-whatsapp</v-icon
                            >
                          </template>
                        </v-switch>
                      </v-col>
                    </v-row>

                    <v-row>
                      <v-col cols="12" md="12">
                        <v-text-field
                          v-model="formattedCpf"
                          label="CPF *"
                          variant="outlined"
                          density="compact"
                          rounded="lg"
                          :rules="cpfRules"
                          prepend-inner-icon="mdi-card-account-details"
                          required
                          hint="Seu CPF"
                          persistent-hint
                          @input="handleCpfInput"
                          maxlength="14"
                        />
                      </v-col>
                    </v-row>
                  </div>

                  <!-- Senha -->
                  <div class="mb-6">
                    <v-row>
                      <v-col cols="12" md="6">
                        <v-text-field
                          v-model="form.password"
                          label="Senha *"
                          :type="showPassword ? 'text' : 'password'"
                          variant="outlined"
                          density="compact"
                          rounded="lg"
                          :rules="passwordRules"
                          prepend-inner-icon="mdi-lock"
                          :append-inner-icon="
                            showPassword ? 'mdi-eye' : 'mdi-eye-off'
                          "
                          @click:append-inner="showPassword = !showPassword"
                          required
                          hint="Mínimo 6 caracteres"
                          persistent-hint
                        />
                      </v-col>
                      <v-col cols="12" md="6">
                        <v-text-field
                          v-model="form.password_confirmation"
                          label="Confirmar senha *"
                          :type="showPasswordConfirmation ? 'text' : 'password'"
                          variant="outlined"
                          density="compact"
                          rounded="lg"
                          :rules="passwordConfirmationRules"
                          prepend-inner-icon="mdi-lock-check"
                          :append-inner-icon="
                            showPasswordConfirmation ? 'mdi-eye' : 'mdi-eye-off'
                          "
                          @click:append-inner="
                            showPasswordConfirmation = !showPasswordConfirmation
                          "
                          required
                          hint="Digite a senha novamente"
                          persistent-hint
                        />
                      </v-col>
                    </v-row>
                  </div>

                  <!-- Info -->
                  <v-alert
                    type="info"
                    variant="tonal"
                    density="compact"
                    class="mb-4"
                  >
                    <template #prepend>
                      <v-icon>mdi-information</v-icon>
                    </template>
                    <span class="text-body-2">
                      Após criar sua conta, você poderá se associar a empresas
                      existentes ou criar sua própria empresa.
                    </span>
                  </v-alert>
                </v-card-text>

                <!-- Actions -->
                <v-card-actions class="pa-6 pt-0">
                  <v-spacer />
                  <v-btn
                    variant="text"
                    color="grey"
                    @click="$router.push('/login')"
                  >
                    Cancelar
                  </v-btn>
                  <v-btn
                    type="submit"
                    color="primary"
                    size="large"
                    :loading="loading"
                    :disabled="!isValid"
                    prepend-icon="mdi-check"
                  >
                    Criar Conta
                  </v-btn>
                </v-card-actions>
              </v-card>
            </v-form>

            <!-- Footer -->
            <div class="form-footer mt-8 text-center">
              <p class="text-body-2">
                Já tem uma conta?
                <router-link to="/login" class="text-primary font-weight-bold">
                  Fazer login
                </router-link>
              </p>
            </div>
          </div>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script lang="ts" setup>
import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import { useRegisterApi } from "./api";
import { useMask } from "@/composables/useMask";
import { useValidation } from "@/composables/useValidation";
import { showSuccessToast, showErrorToast } from "@/utils/swal";

const router = useRouter();
const registerApi = useRegisterApi();
const { loading, register } = registerApi;

// Validation
const { getCPFValidationRules, isValidCPF } = useValidation();

// Mask
const { maskPhone, maskCPF, formatPhone, formatCPF } = useMask();

// Form state
const formRef = ref();
const isValid = ref(false);
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

// Form data
const form = ref({
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
  phone: "",
  cpf: "",
  has_whatsapp: false,
});

// Phone formatting
const formattedPhone = computed({
  get: () => {
    if (!form.value.phone) return "";
    return formatPhone(form.value.phone);
  },
  set: (value) => {
    form.value.phone = value;
  },
});

const formattedCpf = computed({
  get: () => {
    if (!form.value.cpf) return "";
    return formatCPF(form.value.cpf);
  },
  set: (value) => {
    form.value.cpf = value;
  },
});

const handlePhoneInput = (event: Event) => {
  const maskedValue = maskPhone(event);
  form.value.phone = maskedValue;
};

const handleCpfInput = (event: Event) => {
  const maskedValue = maskCPF(event);
  form.value.cpf = maskedValue;
};

// Validation rules
const nameRules = [
  (v: string) => !!v || "Nome é obrigatório",
  (v: string) =>
    (v && v.length >= 3) || "Nome deve ter pelo menos 3 caracteres",
];

const emailRules = [
  (v: string) => !!v || "E-mail é obrigatório",
  (v: string) => {
    const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return pattern.test(v) || "E-mail inválido";
  },
];

const passwordRules = [
  (v: string) => !!v || "Senha é obrigatória",
  (v: string) =>
    (v && v.length >= 6) || "Senha deve ter pelo menos 6 caracteres",
];

const passwordConfirmationRules = [
  (v: string) => !!v || "Confirme a senha",
  (v: string) => v === form.value.password || "As senhas não conferem",
];

const cpfRules = [
  (v: string) => !!v || "CPF é obrigatório",
  ...getCPFValidationRules(),
];

const phoneRules = [
  (v: string) => {
    if (!v) return true; // Telefone é opcional
    const cleaned = v.replace(/\D/g, "");
    return cleaned.length >= 10 || "Telefone deve ter pelo menos 10 dígitos";
  },
];

const handleSubmit = async () => {
  if (!isValid.value) return;

  try {
    const response = await register(form.value);

    if (response && response.success) {
      showSuccessToast(
        "Conta criada com sucesso! Agora você pode fazer login.",
        "Cadastro realizado!"
      );

      // Redirecionar para login com email preenchido
      await router.push({
        path: "/login",
        query: { email: form.value.email },
      });
    }
  } catch (error: any) {
    showErrorToast(
      error.response?.data?.message || "Erro ao criar conta",
      "Erro no cadastro"
    );
  }
};
</script>

<style scoped>
.register-page {
  min-height: 100vh;
  height: 100%;
  background: rgb(var(--v-theme-surface));
}

.register-branding {
  background: linear-gradient(135deg, #1e293b 0%, #334155 50%, #475569 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 48px;
  position: relative;
  overflow: hidden;
  min-height: 100vh;
}

.register-branding::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.1)" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,160C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>')
    no-repeat bottom;
  background-size: cover;
  opacity: 0.3;
}

.branding-content {
  position: relative;
  z-index: 1;
  max-width: 600px;
  text-align: center;
}

.feature-card {
  backdrop-filter: blur(10px);
  border-radius: 24px !important;
}

.feature-item {
  display: flex;
  align-items: flex-start;
  color: white;
}

.register-form-section {
  background: rgb(var(--v-theme-surface));
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 32px;
  overflow-y: auto;
  min-height: 100vh;
}

.form-container {
  width: 100%;
  max-width: 600px;
}

.register-stepper {
  background: transparent !important;
  box-shadow: none !important;
}

.register-stepper :deep(.v-stepper-window) {
  margin: 24px 0;
}

.step-content {
  min-height: 300px;
  padding: 16px 0;
}

@media (max-width: 960px) {
  .register-page {
    background: rgb(var(--v-theme-surface));
  }

  .register-form-section {
    padding: 16px;
  }

  .step-content {
    min-height: auto;
    padding: 8px 0;
  }

  .register-stepper :deep(.v-stepper-header) {
    box-shadow: none !important;
  }

  .register-stepper :deep(.v-stepper-item__title) {
    font-size: 0.875rem !important;
  }

  .form-header {
    margin-bottom: 24px !important;
  }

  .form-header h2 {
    font-size: 1.75rem !important;
  }
}
</style>
