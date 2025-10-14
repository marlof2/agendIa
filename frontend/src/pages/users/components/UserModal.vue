<template>
  <BaseDialog
    :model-value="modelValue"
    :title="isEditing ? 'Editar Usuário' : 'Novo Usuário'"
    :subtitle="
      isEditing
        ? 'Atualize as informações do usuário'
        : 'Preencha os dados para criar um novo usuário'
    "
    :icon="isEditing ? 'mdi-pencil' : 'mdi-plus-circle'"
    :icon-color="isEditing ? 'info' : 'primary'"
    :fullscreen="$vuetify.display.mobile"
    :show-progress="true"
    :progress="formProgress"
    @close="closeModal"
  >
    <v-form ref="formRef" v-model="isValid" @submit.prevent="handleSubmit">
      <v-row>
        <!-- Informações do Usuário -->
        <v-col cols="12">
          <h3 class="text-h6 mb-4 d-flex align-center">
            <v-icon size="20" class="mr-2">mdi-account</v-icon>
            Informações do Usuário
          </h3>
        </v-col>

        <v-col cols="12" md="6">
          <v-text-field
            v-model="form.name"
            label="Nome completo *"
            variant="outlined"
            density="compact"
            rounded="lg"
            :rules="nameRules"
            prepend-inner-icon="mdi-account"
            required
            hint="Nome completo do usuário"
            persistent-hint
          />
        </v-col>

        <v-col cols="12" md="6">
          <v-text-field
            v-model="form.email"
            label="E-mail *"
            variant="outlined"
            density="compact"
            rounded="lg"
            :rules="emailRules"
            prepend-inner-icon="mdi-email"
            required
            hint="E-mail de acesso do usuário"
            persistent-hint
          />
        </v-col>

        <v-col v-if="!isEditing" cols="12" md="6">
          <v-text-field
            v-model="form.password"
            label="Senha *"
            :type="showPassword ? 'text' : 'password'"
            variant="outlined"
            density="compact"
            rounded="lg"
            :rules="passwordRules"
            prepend-inner-icon="mdi-lock"
            required
            hint="Senha de acesso do usuário"
            persistent-hint
            :append-inner-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
            @click:append-inner="showPassword = !showPassword"
          />
        </v-col>

        <v-col v-if="!isEditing" cols="12" md="6">
          <v-text-field
            v-model="form.password_confirmation"
            label="Confirmar senha *"
            :type="showPasswordConfirmation ? 'text' : 'password'"
            variant="outlined"
            density="compact"
            rounded="lg"
            :rules="passwordConfirmationRules"
            prepend-inner-icon="mdi-lock-check"
            required
            hint="Confirme a senha"
            persistent-hint
            :append-inner-icon="
              showPasswordConfirmation ? 'mdi-eye' : 'mdi-eye-off'
            "
            @click:append-inner="
              showPasswordConfirmation = !showPasswordConfirmation
            "
          />
        </v-col>

        <v-col cols="12" md="6">
          <v-text-field
            v-model="formattedPhone"
            label="Telefone"
            variant="outlined"
            density="compact"
            rounded="lg"
            prepend-inner-icon="mdi-phone"
            hint="Telefone de contato"
            persistent-hint
            @input="handlePhoneInput"
            maxlength="15"
          />
        </v-col>

        <v-col cols="12" md="6">
          <v-text-field
            v-model="formattedCpf"
            label="CPF *"
            variant="outlined"
            density="compact"
            rounded="lg"
            prepend-inner-icon="mdi-card-account-details"
            hint="Seu CPF"
            persistent-hint
            :rules="cpfRules"
            required
            maxlength="14"
            @input="handleCpfInput"
          />
        </v-col>


        <v-col cols="12" md="6">
          <v-switch
            v-model="form.has_whatsapp"
            label="Telefone é WhatsApp ?"
            color="primary"
            density="compact"
            :disabled="!form.phone"
          >
            <template #prepend>
              <v-icon :color="form.has_whatsapp ? 'success' : 'grey'"
                >mdi-whatsapp</v-icon
              >
            </template>
          </v-switch>
        </v-col>
      </v-row>
    </v-form>

    <template #actions>
      <v-spacer />
      <div class="d-flex modal-actions-container">
        <BtnCancel @click="closeModal" />
        <BtnSave
          :loading="loading"
          :disabled="!isValid"
          :is-editing="isEditing"
          @click="handleSubmit"
        />
      </div>
    </template>
  </BaseDialog>
</template>

<script lang="ts" setup>
import { ref, computed, watch, nextTick } from "vue";
import { showSuccessToast, showErrorToast } from "@/utils/swal";
import { useUsersApi } from "../api";
import { useMask } from "@/composables/useMask";
import BaseDialog from "@/components/BaseDialog.vue";

interface Props {
  modelValue: boolean;
  user?: any;
}

interface Emits {
  (e: "update:modelValue", value: boolean): void;
  (e: "reload"): void;
}

const props = withDefaults(defineProps<Props>(), {
  user: null,
});

const emit = defineEmits<Emits>();

// Composables
const { createItem, updateItem } = useUsersApi();
const { maskPhone, formatPhone, maskCPF, formatCPF } = useMask();

// Reactive data
const formRef = ref();
const isValid = ref(false);
const loading = ref(false);

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

// Password visibility
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

// Computed
const isEditing = computed(() => !!props.user?.id);

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

const formProgress = computed(() => {
  const totalFields = 3; // name, email, cpf (campos obrigatórios)
  const filledFields = [
    form.value.name,
    form.value.email,
    form.value.cpf,
  ].filter((field) => field && field.toString().trim() !== "").length;

  if (!isEditing.value) {
    // Para criação, adicionar senha e confirmação
    const passwordFields = [
      form.value.password,
      form.value.password_confirmation,
    ].filter((field) => field && field.trim() !== "").length;
    return ((filledFields + passwordFields) / 5) * 100;
  }

  return (filledFields / 3) * 100;
});

// Validation rules
const nameRules = [
  (v: string) => !!v || "Nome é obrigatório",
  (v: string) =>
    (v && v.length >= 2) || "Nome deve ter pelo menos 2 caracteres",
];

const emailRules = [
  (v: string) => !!v || "E-mail é obrigatório",
  (v: string) => /.+@.+\..+/.test(v) || "E-mail deve ser válido",
];

const passwordRules = [
  (v: string) => {
    if (isEditing.value) return true; // Senha opcional na edição
    return !!v || "Senha é obrigatória";
  },
  (v: string) => {
    if (isEditing.value) return true;
    return (v && v.length >= 6) || "Senha deve ter pelo menos 6 caracteres";
  },
];

const passwordConfirmationRules = [
  (v: string) => {
    if (isEditing.value) return true; // Confirmação opcional na edição
    return !!v || "Confirmação de senha é obrigatória";
  },
  (v: string) => {
    if (isEditing.value) return true;
    return v === form.value.password || "Senhas não coincidem";
  },
];


const cpfRules = [
  (v: string) => !!v || "CPF é obrigatório",
  (v: string) => {
    const cleaned = v.replace(/\D/g, "");
    return cleaned.length === 11 || "CPF deve ter 11 dígitos";
  },
];

// Methods
const resetForm = () => {
  form.value = {
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    phone: "",
    cpf: "",
    has_whatsapp: false,
  };
  showPassword.value = false;
  showPasswordConfirmation.value = false;
};

const handlePhoneInput = (event: Event) => {
  const maskedValue = maskPhone(event);
  form.value.phone = maskedValue;
};

const handleCpfInput = (event: Event) => {
  const maskedValue = maskCPF(event);
  form.value.cpf = maskedValue;
};

const loadUserData = () => {
  if (props.user) {
    form.value = {
      name: props.user.name || "",
      email: props.user.email || "",
      password: "",
      password_confirmation: "",
      phone: props.user.phone || "",
      cpf: props.user.cpf || "",
      has_whatsapp: props.user.has_whatsapp || false,
    };
  } else {
    resetForm();
  }
};


const closeModal = () => {
  emit("update:modelValue", false);
  resetForm();
};

const handleSubmit = async () => {
  if (!isValid.value) return;

  loading.value = true;

  try {
    const userData: any = {
      name: form.value.name,
      email: form.value.email,
      phone: form.value.phone,
      cpf: form.value.cpf,
      has_whatsapp: form.value.has_whatsapp,
    };

    // Adicionar senha apenas se estiver preenchida (para edição) ou se for criação
    if (!isEditing.value || form.value.password) {
      userData.password = form.value.password;
      userData.password_confirmation = form.value.password_confirmation;
    }

    let result;
    if (isEditing.value) {
      result = await updateItem(props.user.id, userData);
      showSuccessToast("Usuário atualizado com sucesso!", "Sucesso!");
    } else {
      result = await createItem(userData);
      showSuccessToast("Usuário criado com sucesso!", "Sucesso!");
    }

    emit("reload");
    closeModal();
  } catch (error) {
    console.error("Erro ao salvar usuário:", error);
  } finally {
    loading.value = false;
  }
};

// Lifecycle
// Removido onMounted - carregamento movido para o watch

// Watchers
watch(
  () => props.modelValue,
  (newValue) => {
    if (newValue) {
      loadUserData();
      nextTick(() => {
        formRef.value?.resetValidation();
      });
    }
  }
);

watch(
  () => props.user,
  () => {
    if (props.modelValue) {
      loadUserData();
    }
  },
  { deep: true }
);
</script>

<style scoped>
.user-modal {
  border-radius: 16px;
  overflow: hidden;
}

.user-modal__header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 24px;
  position: relative;
}

.user-modal__close-btn {
  position: absolute;
  top: 16px;
  right: 16px;
  color: white !important;
}

.user-modal__content {
  padding: 24px;
  max-height: 70vh;
  overflow-y: auto;
}

.user-modal__actions {
  padding: 16px 24px;
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
}

/* Custom scrollbar */
.user-modal__content::-webkit-scrollbar {
  width: 6px;
}

.user-modal__content::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.user-modal__content::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.user-modal__content::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .user-modal__header {
    padding: 20px;
  }

  .user-modal__content {
    padding: 20px;
  }

  .user-modal__actions {
    padding: 16px 20px;
    flex-direction: column-reverse;
    gap: 12px;
  }

  .user-modal__actions .v-btn {
    width: 100%;
  }

  .modal-actions-container {
    flex-direction: column;
    gap: 8px;
    width: 100%;
  }

  .modal-actions-container .v-btn {
    width: 100%;
    margin-right: 0 !important;
  }
}

/* Desktop styles */
.modal-actions-container {
  gap: 16px;
}
</style>
