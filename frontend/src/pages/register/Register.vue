<template>
  <div class="register-page">
    <v-container fluid class="fill-height pa-0">
      <v-row no-gutters class="fill-height">
        <!-- Left Side - Branding -->
        <v-col cols="12" md="6" class="register-branding d-none d-md-flex">
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
        <v-col cols="12" md="6" class="register-form-section" >
          <div class="form-container">
            <!-- Header -->
            <div class="form-header mb-8">
              <h2 class="text-h4 font-weight-bold mb-2">Criar Conta</h2>
              <p class="text-body-1 text-medium-emphasis">
                Preencha os dados para começar a usar o AgendIA
              </p>
            </div>

            <!-- Stepper -->
            <v-stepper
              v-model="currentStep"
              :mobile="$vuetify.display.mobile"
              alt-labels
              flat
              class="register-stepper"
            >
              <v-stepper-header>
                <v-stepper-item
                  :complete="currentStep > 1"
                  :value="1"
                  title="Perfil"
                  :subtitle="
                    $vuetify.display.mobile ? undefined : 'Escolha seu perfil'
                  "
                />
                <v-divider />
                <v-stepper-item
                  :complete="currentStep > 2"
                  :value="2"
                  :title="
                    form.account_type === 'owner' ? 'Dados Pessoais' : 'Dados Pessoais'
                  "
                  :subtitle="
                    $vuetify.display.mobile ? undefined : 'Suas informações pessoais'
                  "
                />
                <v-divider v-if="form.account_type === 'owner'" />
                <v-stepper-item
                  v-if="form.account_type === 'owner'"
                  :value="3"
                  title="Empresa"
                  :subtitle="
                    $vuetify.display.mobile ? undefined : 'Dados da empresa'
                  "
                />
              </v-stepper-header>

              <v-stepper-window>
                <!-- Step 1: Account Type -->
                <v-stepper-window-item :value="1">
                  <v-form ref="formRef" v-model="isValid">
                    <v-card flat>
                      <v-card-text class="step-content">
                        <h3 class="text-h6 mb-4">Selecione o tipo de conta</h3>

                        <v-autocomplete
                          v-model="form.profile_id"
                          :items="availableProfiles"
                          item-title="display_name"
                          item-value="id"
                          label="Perfil *"
                          variant="outlined"
                          density="comfortable"
                          rounded="lg"
                          prepend-inner-icon="mdi-account-tie"
                          :rules="profileRules"
                          @update:model-value="handleProfileChange"
                        >
                          <template #item="{ props, item }">
                            <v-list-item
                              v-bind="props"
                              :title="item.raw.display_name"
                            >
                              <template #prepend>
                                <v-avatar
                                  :color="getProfileColor(item.raw.name)"
                                >
                                  <v-icon color="white">{{
                                    getProfileIcon(item.raw.name)
                                  }}</v-icon>
                                </v-avatar>
                              </template>
                            </v-list-item>
                          </template>
                        </v-autocomplete>

                        <v-alert
                          v-if="form.account_type === 'owner'"
                          type="info"
                          variant="tonal"
                          class="mt-4"
                        >
                          <template #prepend>
                            <v-icon>mdi-information</v-icon>
                          </template>
                          Como <strong>Proprietário</strong>, você poderá criar
                          sua empresa e terá acesso total para gerenciá-la.
                        </v-alert>

                        <v-alert
                          v-else-if="form.profile_id"
                          type="info"
                          variant="tonal"
                          class="mt-4"
                        >
                          <template #prepend>
                            <v-icon>mdi-information</v-icon>
                          </template>
                          Você poderá se associar a empresas existentes no
                          sistema.
                        </v-alert>
                      </v-card-text>
                    </v-card>
                  </v-form>
                </v-stepper-window-item>

                <!-- Step 2: Personal Data + Company Data (if owner) -->
                <v-stepper-window-item :value="2">
                  <v-card flat>
                    <v-card-text class="step-content">
                      <h3 class="text-h6 mb-4">Seus dados pessoais</h3>

                      <v-row>
                        <v-col cols="12">
                          <v-text-field
                            v-model="form.name"
                            label="Nome completo *"
                            variant="outlined"
                            density="comfortable"
                            rounded="lg"
                            prepend-inner-icon="mdi-account"
                            :rules="nameRules"
                          />
                        </v-col>

                        <v-col cols="12">
                          <v-text-field
                            v-model="form.email"
                            label="E-mail *"
                            type="email"
                            variant="outlined"
                            density="comfortable"
                            rounded="lg"
                            prepend-inner-icon="mdi-email"
                            :rules="emailRules"
                          />
                        </v-col>

                        <v-col cols="12" md="6">
                          <v-text-field
                            v-model="formattedPhone"
                            label="Telefone"
                            variant="outlined"
                            density="comfortable"
                            rounded="lg"
                            prepend-inner-icon="mdi-phone"
                            placeholder="(00) 00000-0000"
                            hint="Telefone de contato"
                            persistent-hint
                            :rules="phoneRules"
                            @input="handlePhoneInput"
                            maxlength="15"
                          />
                        </v-col>

                        <v-col cols="12" md="6">
                          <v-text-field
                            v-model="formattedCpf"
                            label="CPF *"
                            variant="outlined"
                            density="comfortable"
                            rounded="lg"
                            prepend-inner-icon="mdi-card-account-details"
                            placeholder="000.000.000-00"
                            hint="Seu CPF"
                            persistent-hint
                            :rules="cpfRules"
                            required
                            @input="handleCpfInput"
                            maxlength="14"
                          />
                        </v-col>

                        <v-col cols="12" md="6">
                          <v-switch
                            v-model="form.has_whatsapp"
                            label="Telefone é WhatsApp?"
                            color="primary"
                            density="comfortable"
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

                        <v-col cols="12">
                          <v-text-field
                            v-model="form.password"
                            label="Senha *"
                            :type="showPassword ? 'text' : 'password'"
                            variant="outlined"
                            density="comfortable"
                            rounded="lg"
                            prepend-inner-icon="mdi-lock"
                            :append-inner-icon="
                              showPassword ? 'mdi-eye-off' : 'mdi-eye'
                            "
                            :rules="passwordRules"
                            @click:append-inner="showPassword = !showPassword"
                          />
                        </v-col>

                        <v-col cols="12">
                          <v-text-field
                            v-model="form.password_confirmation"
                            label="Confirmar senha *"
                            :type="
                              showPasswordConfirmation ? 'text' : 'password'
                            "
                            variant="outlined"
                            density="comfortable"
                            rounded="lg"
                            prepend-inner-icon="mdi-lock-check"
                            :append-inner-icon="
                              showPasswordConfirmation
                                ? 'mdi-eye-off'
                                : 'mdi-eye'
                            "
                            :rules="passwordConfirmationRules"
                            @click:append-inner="
                              showPasswordConfirmation =
                                !showPasswordConfirmation
                            "
                          />
                        </v-col>
                      </v-row>

                    </v-card-text>
                  </v-card>
                </v-stepper-window-item>

                <!-- Step 3: Company Data (Only for Owner) -->
                <v-stepper-window-item v-if="form.account_type === 'owner'" :value="3">
                  <v-card flat>
                    <v-card-text class="step-content">
                      <h3 class="text-h6 mb-4">Dados da sua empresa</h3>

                      <v-row v-if="form.company">
                        <v-col cols="12">
                          <v-text-field
                            v-model="form.company.name"
                            label="Nome da empresa *"
                            variant="outlined"
                            density="comfortable"
                            rounded="lg"
                            prepend-inner-icon="mdi-office-building"
                            :rules="companyNameRules"
                          />
                        </v-col>

                        <v-col cols="12" md="6">
                          <v-select
                            v-model="form.company.person_type"
                            :items="personTypes"
                            label="Tipo de pessoa *"
                            variant="outlined"
                            density="comfortable"
                            rounded="lg"
                            prepend-inner-icon="mdi-account-box"
                            :rules="personTypeRules"
                          />
                        </v-col>

                        <v-col cols="12" md="6">
                          <v-text-field
                            v-if="form.company.person_type === 'legal'"
                            v-model="form.company.cnpj"
                            label="CNPJ *"
                            variant="outlined"
                            density="comfortable"
                            rounded="lg"
                            prepend-inner-icon="mdi-card-account-details"
                            placeholder="00.000.000/0000-00"
                            hint="CNPJ da empresa"
                            persistent-hint
                            :rules="companyCnpjRules"
                            @input="handleMaskCNPJ"
                            maxlength="18"
                          />
                          <v-text-field
                            v-else
                            v-model="form.company.cpf"
                            label="CPF *"
                            variant="outlined"
                            density="comfortable"
                            rounded="lg"
                            prepend-inner-icon="mdi-card-account-details"
                            placeholder="000.000.000-00"
                            hint="CPF do responsável"
                            persistent-hint
                            :rules="companyCpfRules"
                            @input="handleMaskCPF"
                            maxlength="14"
                          />
                        </v-col>

                        <v-col cols="12">
                          <v-text-field
                            v-model="form.company.responsible_name"
                            label="Nome do Responsável *"
                            variant="outlined"
                            density="comfortable"
                            rounded="lg"
                            prepend-inner-icon="mdi-account"
                            hint="Nome do responsável pela empresa"
                            persistent-hint
                            :rules="responsibleNameRules"
                          />
                        </v-col>

                        <v-col cols="12" md="6">
                          <v-text-field
                            v-model="form.company.phone_1"
                            label="Telefone Principal *"
                            variant="outlined"
                            density="comfortable"
                            rounded="lg"
                            prepend-inner-icon="mdi-phone"
                            placeholder="(00) 00000-0000"
                            hint="Telefone principal de contato"
                            persistent-hint
                            :rules="companyPhone1Rules"
                            @input="handleMaskCompanyPhone1"
                            maxlength="15"
                          />
                        </v-col>

                        <v-col cols="12" md="6">
                          <v-switch
                            v-model="form.company.has_whatsapp_1"
                            label="Telefone é WhatsApp?"
                            color="primary"
                            density="comfortable"
                            hide-details
                          >
                            <template #prepend>
                              <v-icon
                                :color="
                                  form.company?.has_whatsapp_1
                                    ? 'success'
                                    : 'grey'
                                "
                                >mdi-whatsapp</v-icon
                              >
                            </template>
                          </v-switch>
                        </v-col>

                        <v-col cols="12" md="6">
                          <v-text-field
                            v-model="form.company.phone_2"
                            label="Telefone Secundário"
                            variant="outlined"
                            density="comfortable"
                            rounded="lg"
                            prepend-inner-icon="mdi-phone-outgoing"
                            placeholder="(00) 00000-0000"
                            hint="Telefone secundário (opcional)"
                            persistent-hint
                            :rules="companyPhone2Rules"
                            @input="handleMaskCompanyPhone2"
                            maxlength="15"
                          />
                        </v-col>

                        <v-col cols="12" md="6">
                          <v-switch
                            v-model="form.company.has_whatsapp_2"
                            label="Telefone é WhatsApp?"
                            color="primary"
                            density="comfortable"
                            hide-details
                            :disabled="!form.company.phone_2"
                          >
                            <template #prepend>
                              <v-icon
                                :color="
                                  form.company?.has_whatsapp_2
                                    ? 'success'
                                    : 'grey'
                                "
                                >mdi-whatsapp</v-icon
                              >
                            </template>
                          </v-switch>
                        </v-col>
                      </v-row>
                    </v-card-text>
                  </v-card>
                </v-stepper-window-item>

              </v-stepper-window>

              <!-- Navigation Buttons -->
              <v-stepper-actions>
                <template #prev>
                  <v-btn
                    v-if="currentStep > 1"
                    variant="flat"
                    color="primary"
                    @click="previousStep"
                  >
                    <v-icon start>mdi-arrow-left</v-icon>
                    Voltar
                  </v-btn>
                </template>
                <template #next>
                  <v-btn
                    v-if="currentStep < (form.account_type === 'owner' ? 3 : 2)"
                    color="blue"
                    :disabled="!canProceed"
                    @click="nextStep"
                  >
                    Próximo
                    <v-icon end>mdi-arrow-right</v-icon>
                  </v-btn>
                  <v-btn
                    v-else
                    color="success"
                    :loading="loading"
                    :disabled="!canProceed"
                    @click="handleSubmit"
                  >
                    <v-icon start>mdi-check</v-icon>
                    Criar Conta
                  </v-btn>
                </template>
              </v-stepper-actions>
            </v-stepper>

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
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useRegisterApi, type RegisterData } from "./api";
import { useCompaniesApi } from "@/pages/companies/api";
import { useProfilesApi } from "@/pages/profiles/api";
import { useMask } from "@/composables/useMask";
import { useValidation } from "@/composables/useValidation";
import { showSuccessToast, showErrorToast } from "@/utils/swal";

const router = useRouter();
const registerApi = useRegisterApi();
const { loading, register } = registerApi;
const { getCombo: getProfilesCombo } = useProfilesApi();

// Validation
const { getCPFValidationRules, getCNPJValidationRules, isValidCPF, isValidCNPJ } = useValidation();

// Estado local para combos
const availableProfiles = ref<any[]>([]);

// Form state
const formRef = ref();
const isValid = ref(false);
const currentStep = ref(1);
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

// Form data
const form = ref<RegisterData>({
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
  phone: "",
  cpf: "",
  has_whatsapp: false,
  account_type: "owner",
  profile_id: null as any,
  company: {
    name: "",
    person_type: "legal",
    cnpj: "",
    cpf: "",
    responsible_name: "",
    phone_1: "",
    has_whatsapp_1: false,
    phone_2: "",
    has_whatsapp_2: false,
    timezone_id: undefined,
  },
});

// Person types
const personTypes = [
  { title: "Pessoa Jurídica (CNPJ)", value: "legal" },
  { title: "Pessoa Física (CPF)", value: "physical" },
];

// Computed
const canProceed = computed(() => {
  if (currentStep.value === 1) {
    return form.value.profile_id && form.value.profile_id > 0;
  }
  if (currentStep.value === 2) {
    // Validação para dados pessoais
    return (
      form.value.name && form.value.name.length >= 3 &&
      form.value.email && form.value.email.includes('@') &&
      form.value.cpf && form.value.cpf.length >= 11 &&
      form.value.password && form.value.password.length >= 6 &&
      form.value.password_confirmation && form.value.password_confirmation === form.value.password
    );
  }
  if (currentStep.value === 3 && form.value.account_type === 'owner') {
    // Validação para dados da empresa (apenas proprietários)
    const basicCompanyData = (
      form.value.company?.name && form.value.company.name.length >= 3 &&
      form.value.company?.person_type &&
      form.value.company?.responsible_name && form.value.company.responsible_name.length >= 3 &&
      form.value.company?.phone_1 && form.value.company.phone_1.length >= 10
    );

    // Validação do CNPJ ou CPF baseado no tipo de pessoa
    let documentValid = false;
    if (form.value.company?.person_type === 'legal') {
      // Para pessoa jurídica, CNPJ é obrigatório
      documentValid = !!(form.value.company?.cnpj && isValidCNPJ(form.value.company.cnpj));
    } else if (form.value.company?.person_type === 'physical') {
      // Para pessoa física, CPF é obrigatório
      documentValid = !!(form.value.company?.cpf && isValidCPF(form.value.company.cpf));
    }

    return basicCompanyData && documentValid;
  }
  return true;
});

// Validation rules
const profileRules = [(v: any) => (v && v > 0) || "Selecione um perfil"];
const nameRules = [
  (v: string) => !!v || "Nome é obrigatório",
  (v: string) => v.length >= 3 || "Nome deve ter pelo menos 3 caracteres",
];
const emailRules = [
  (v: string) => !!v || "E-mail é obrigatório",
  (v: string) => /.+@.+\..+/.test(v) || "E-mail inválido",
];
const passwordRules = [
  (v: string) => !!v || "Senha é obrigatória",
  (v: string) => v.length >= 6 || "Senha deve ter pelo menos 6 caracteres",
];
const passwordConfirmationRules = [
  (v: string) => !!v || "Confirme a senha",
  (v: string) => v === form.value.password || "As senhas não conferem",
];
const cpfRules = [
  (v: string) => !!v || "CPF é obrigatório",
  ...getCPFValidationRules(),
];
const companyNameRules = [
  (v: string) => !!v || "Nome da empresa é obrigatório",
];
const personTypeRules = [(v: string) => !!v || "Tipo de pessoa é obrigatório"];
const responsibleNameRules = [
  (v: string) => !!v || "Nome do responsável é obrigatório",
];
const companyPhone1Rules = [
  (v: string) => !!v || "Telefone da empresa é obrigatório",
];

const companyCpfRules = [
  (v: string) => !!v || "CPF é obrigatório",
  ...getCPFValidationRules(),
];

// Validação para telefone pessoal
const phoneRules = [
  (v: string) => {
    if (!v) return true; // Telefone é opcional
    const cleaned = v.replace(/\D/g, '');
    return cleaned.length >= 10 || "Telefone deve ter pelo menos 10 dígitos";
  },
];

// Validação para CNPJ da empresa
const companyCnpjRules = [
  (v: string) => !!v || "CNPJ é obrigatório",
  (v: string) => {
    if (!v) return true; // Já validado acima
    const cnpjRules = getCNPJValidationRules();
    return cnpjRules[0] ? cnpjRules[0](v) : true; // Usa a validação completa do composable
  },
];

// Validação para telefone secundário da empresa
const companyPhone2Rules = [
  (v: string) => {
    if (!v) return true; // Telefone secundário é opcional
    const cleaned = v.replace(/\D/g, '');
    return cleaned.length >= 10 || "Telefone deve ter pelo menos 10 dígitos";
  },
];

// Phone formatting
const { maskPhone, formatPhone, maskCNPJ, maskCPF, formatCPF } = useMask();

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

const handleMaskCompanyPhone1 = (event: Event) => {
  const maskedValue = maskPhone(event);
  if (form.value.company) {
    form.value.company.phone_1 = maskedValue;
  }
};

const handleMaskCompanyPhone2 = (event: Event) => {
  const maskedValue = maskPhone(event);
  if (form.value.company) {
    form.value.company.phone_2 = maskedValue;
  }
};

const handleMaskCNPJ = (event: Event) => {
  const maskedValue = maskCNPJ(event);
  if (form.value.company) {
    form.value.company.cnpj = maskedValue;
  }
};

const handleMaskCPF = (event: Event) => {
  const maskedValue = maskCPF(event);
  if (form.value.company) {
    form.value.company.cpf = maskedValue;
  }
};

// Methods
const handleProfileChange = (profileId: number) => {
  const profile = availableProfiles.value.find((p) => p.id === profileId);
  if (profile) {
    form.value.account_type = profile.name as any;
  }
};

const getProfileColor = (profileName: string) => {
  const colors: Record<string, string> = {
    owner: "purple",
    professional: "primary",
    supervisor: "success",
    client: "info",
  };
  return colors[profileName] || "grey";
};

const getProfileIcon = (profileName: string) => {
  const icons: Record<string, string> = {
    owner: "mdi-crown",
    professional: "mdi-briefcase",
    supervisor: "mdi-account-supervisor",
    client: "mdi-account",
  };
  return icons[profileName] || "mdi-account";
};

const nextStep = () => {
  if (canProceed.value && currentStep.value < 3) {
    currentStep.value++;
  }
};

const previousStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--;
  }
};

const handleSubmit = async () => {
  if (!isValid.value) return;

  const response = await register(form.value);

  // Se chegou aqui, o registro foi bem-sucedido
  if (response && response.success) {
    showSuccessToast(
      "Conta criada com sucesso! Você será redirecionado para fazer login.",
      "Cadastro realizado!"
    );

    // Redirecionar para login com email preenchido
    await router.push({
      path: "/login",
      query: { email: form.value.email }
    });
  }
};


// Lifecycle
onMounted(async () => {
  try {
    // Carregar perfis
    const profiles = await getProfilesCombo();

    // Filtrar admin do registro público
    availableProfiles.value = profiles
      .filter((profile: any) => profile.name !== "admin")
      .filter((profile: any) => profile.name !== "supervisor");
  } catch (error) {
    showErrorToast("Erro ao carregar dados", "Erro!");
  }
});
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

