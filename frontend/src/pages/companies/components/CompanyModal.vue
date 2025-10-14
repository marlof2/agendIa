<template>
  <BaseDialog
    :model-value="modelValue"
    :title="isEditing ? 'Editar Empresa' : 'Nova Empresa'"
    :subtitle="
      isEditing
        ? 'Atualize as informações da empresa'
        : 'Preencha os dados para criar uma nova empresa'
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
        <!-- Informações da Empresa -->
        <v-col cols="12">
          <h3 class="text-h6 mb-4 d-flex align-center">
            <v-icon size="20" class="mr-2">mdi-domain</v-icon>
            Informações da Empresa
          </h3>
        </v-col>

        <v-col cols="12" md="6">
          <v-text-field
            v-model="form.name"
            label="Nome da empresa *"
            variant="outlined"
            density="compact"
            rounded="lg"
            :rules="nameRules"
            prepend-inner-icon="mdi-domain"
            required
            hint="Nome da empresa"
            persistent-hint
          />
        </v-col>

        <v-col cols="12" md="6">
          <v-select
            v-model="form.person_type"
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
            v-if="form.person_type === 'legal'"
            v-model="form.cnpj"
            label="CNPJ"
            variant="outlined"
            density="compact"
            rounded="lg"
            :rules="cnpjRules"
            prepend-inner-icon="mdi-card-account-details"
            hint="CNPJ da empresa"
            persistent-hint
            @input="handleMaskCNPJ"
            maxlength="18"
          />
          <v-text-field
            v-else
            v-model="form.cpf"
            label="CPF"
            variant="outlined"
            density="compact"
            rounded="lg"
            :rules="cpfRules"
            prepend-inner-icon="mdi-card-account-details"
            hint="CPF do responsável"
            persistent-hint
            @input="handleMaskCPF"
            maxlength="14"
          />
        </v-col>

        <v-col cols="12" md="6">
          <v-text-field
            v-model="form.responsible_name"
            label="Nome do Responsável *"
            variant="outlined"
            density="compact"
            rounded="lg"
            :rules="responsibleNameRules"
            prepend-inner-icon="mdi-account"
            required
            hint="Nome do responsável pela empresa"
            persistent-hint
          />
        </v-col>

        <v-col cols="12">
          <v-divider class="my-4"></v-divider>
          <h4 class="text-subtitle-1 mb-3">Telefones de Contato</h4>
        </v-col>

        <v-col cols="12" md="6">
          <v-text-field
            v-model="form.phone_1"
            label="Telefone Principal *"
            variant="outlined"
            density="compact"
            rounded="lg"
            :rules="phone1Rules"
            prepend-inner-icon="mdi-phone"
            required
            hint="Telefone principal de contato"
            persistent-hint
            @input="handleMaskPhone1"
            maxlength="15"
          />
        </v-col>

        <v-col cols="12" md="6">
          <v-switch
            v-model="form.has_whatsapp_1"
            label="Este telefone tem WhatsApp"
            color="primary"
            density="compact"
            hide-details
          >
            <template #prepend>
              <v-icon :color="form.has_whatsapp_1 ? 'success' : 'grey'">mdi-whatsapp</v-icon>
            </template>
          </v-switch>
        </v-col>

        <v-col cols="12" md="6">
          <v-text-field
            v-model="form.phone_2"
            label="Telefone Secundário"
            variant="outlined"
            density="compact"
            rounded="lg"
            :rules="phone2Rules"
            prepend-inner-icon="mdi-phone-outgoing"
            hint="Telefone secundário (opcional)"
            persistent-hint
            @input="handleMaskPhone2"
            maxlength="15"
          />
        </v-col>

        <v-col cols="12" md="6">
          <v-switch
            v-model="form.has_whatsapp_2"
            label="Este telefone tem WhatsApp"
            color="primary"
            density="compact"
            hide-details
            :disabled="!form.phone_2"
          >
            <template #prepend>
              <v-icon :color="form.has_whatsapp_2 ? 'success' : 'grey'">mdi-whatsapp</v-icon>
            </template>
          </v-switch>
        </v-col>

        <v-col cols="12">
          <v-autocomplete
            v-model="form.timezone_id"
            :items="timezones"
            item-title="name"
            item-value="id"
            label="Fuso Horário"
            variant="outlined"
            density="compact"
            rounded="lg"
            prepend-inner-icon="mdi-clock-outline"
            clearable
            hint="Selecione o fuso horário da empresa"
            persistent-hint
          >
            <template v-slot:item="{ props, item }">
              <v-list-item v-bind="props" :title="item.raw.name" :subtitle="item.raw.region">
                <template v-slot:append>
                  <v-chip size="small" color="primary" variant="outlined">
                    {{ item.raw.offset }}
                  </v-chip>
                </template>
              </v-list-item>
            </template>
          </v-autocomplete>
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
import { ref, computed, watch, nextTick, onMounted } from "vue";
import { showSuccessToast, showErrorToast } from "@/utils/swal";
import { useCompaniesApi, type Timezone } from "../api";
import { useMask } from "@/composables/useMask";
import { useValidation } from "@/composables/useValidation";
import BaseDialog from "@/components/BaseDialog.vue";

interface Props {
  modelValue: boolean;
  company?: any;
}

interface Emits {
  (e: "update:modelValue", value: boolean): void;
  (e: "reload"): void;
}

const props = withDefaults(defineProps<Props>(), {
  company: null,
});

const emit = defineEmits<Emits>();

// Composables
const { createItem, updateItem, getAllTimezones } = useCompaniesApi();
const { getCPFValidationRules, getCNPJValidationRules } = useValidation();

// Reactive data
const formRef = ref();
const isValid = ref(false);
const loading = ref(false);
const timezones = ref<Timezone[]>([]);

// Form data
const form = ref({
  name: "",
  person_type: "legal" as "physical" | "legal",
  cnpj: "",
  cpf: "",
  responsible_name: "",
  phone_1: "",
  has_whatsapp_1: false,
  phone_2: "",
  has_whatsapp_2: false,
  timezone_id: null,
});

// Computed
const isEditing = computed(() => !!props.company?.id);

const formProgress = computed(() => {
  const totalFields = 4; // name, person_type, responsible_name, phone_1 (campos obrigatórios)
  const filledFields = [form.value.name, form.value.person_type, form.value.responsible_name, form.value.phone_1].filter(
    (field) => field && field.trim() !== ""
  ).length;

  return (filledFields / totalFields) * 100;
});

// Validation rules
const nameRules = [
  (v: string) => !!v || "Nome da empresa é obrigatório",
  (v: string) =>
    (v && v.length >= 2) || "Nome deve ter pelo menos 2 caracteres",
];

const responsibleNameRules = [
  (v: string) => !!v || "Nome do responsável é obrigatório",
  (v: string) =>
    (v && v.length >= 2) || "Nome deve ter pelo menos 2 caracteres",
];

const phone1Rules = [
  (v: string) => !!v || "Telefone principal é obrigatório",
  (v: string) =>
    (v && v.length >= 10) || "Telefone deve ter pelo menos 10 caracteres",
];

const phone2Rules = [
  (v: string) => {
    if (!v) return true; // Campo opcional
    return v.length >= 10 || "Telefone deve ter pelo menos 10 caracteres";
  },
];

// Validation rules for documents
const cnpjRules = getCNPJValidationRules();
const cpfRules = getCPFValidationRules();

// Methods
const loadTimezones = async () => {
  try {
    timezones.value = await getAllTimezones();
  } catch (err) {
    console.error("Erro ao carregar fusos horários:", err);
  }
};

const resetForm = () => {
  form.value = {
    name: "",
    person_type: "legal",
    cnpj: "",
    cpf: "",
    responsible_name: "",
    phone_1: "",
    has_whatsapp_1: false,
    phone_2: "",
    has_whatsapp_2: false,
    timezone_id: null,
  };
};

const loadCompanyData = () => {
  if (props.company) {
    const { formatCNPJ, formatCPF, formatPhone } = useMask();

    form.value = {
      name: props.company.name || "",
      person_type: props.company.person_type || "legal",
      cnpj: props.company.cnpj ? formatCNPJ(props.company.cnpj) : "",
      cpf: props.company.cpf ? formatCPF(props.company.cpf) : "",
      responsible_name: props.company.responsible_name || "",
      phone_1: props.company.phone_1 ? formatPhone(props.company.phone_1) : "",
      has_whatsapp_1: props.company.has_whatsapp_1 || false,
      phone_2: props.company.phone_2 ? formatPhone(props.company.phone_2) : "",
      has_whatsapp_2: props.company.has_whatsapp_2 || false,
      timezone_id: props.company.timezone_id || null,
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
    const companyData = {
      name: form.value.name,
      person_type: form.value.person_type,
      cnpj: form.value.cnpj || undefined,
      cpf: form.value.cpf || undefined,
      responsible_name: form.value.responsible_name,
      phone_1: form.value.phone_1,
      has_whatsapp_1: form.value.has_whatsapp_1,
      phone_2: form.value.phone_2 || undefined,
      has_whatsapp_2: form.value.has_whatsapp_2,
      timezone_id: form.value.timezone_id || undefined,
    };

    let result;
    if (isEditing.value) {
      result = await updateItem(props.company.id, companyData);
      showSuccessToast("Empresa atualizada com sucesso!", "Sucesso!");
    } else {
      result = await createItem(companyData);
      showSuccessToast("Empresa criada com sucesso!", "Sucesso!");
    }

    emit("reload");
    closeModal();
  } catch (err: any) {
    const errorMessage = err?.response?.data?.message || err?.message || "Erro ao salvar empresa";
    showErrorToast(errorMessage, "Erro!");
  } finally {
    loading.value = false;
  }
};

// Funções de máscara
const { maskCNPJ, maskCPF, maskPhone } = useMask();

const handleMaskCNPJ = (event: Event) => {
  const maskedValue = maskCNPJ(event);
  form.value.cnpj = maskedValue;
};

const handleMaskCPF = (event: Event) => {
  const maskedValue = maskCPF(event);
  form.value.cpf = maskedValue;
};

const handleMaskPhone1 = (event: Event) => {
  const maskedValue = maskPhone(event);
  form.value.phone_1 = maskedValue;
};

const handleMaskPhone2 = (event: Event) => {
  const maskedValue = maskPhone(event);
  form.value.phone_2 = maskedValue;
};

// Lifecycle
onMounted(() => {
  loadTimezones();
});

// Watchers
watch(
  () => props.modelValue,
  (newValue) => {
    if (newValue) {
      loadCompanyData();
      nextTick(() => {
        formRef.value?.resetValidation();
      });
    }
  }
);

watch(
  () => props.company,
  () => {
    if (props.modelValue) {
      loadCompanyData();
    }
  },
  { deep: true }
);
</script>

<style scoped>
.company-modal {
  border-radius: 16px;
  overflow: hidden;
}

.company-modal__header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 24px;
  position: relative;
}

.company-modal__close-btn {
  position: absolute;
  top: 16px;
  right: 16px;
  color: white !important;
}

.company-modal__content {
  padding: 24px;
  max-height: 70vh;
  overflow-y: auto;
}

.company-modal__actions {
  padding: 16px 24px;
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
}

/* Custom scrollbar */
.company-modal__content::-webkit-scrollbar {
  width: 6px;
}

.company-modal__content::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.company-modal__content::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.company-modal__content::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .company-modal__header {
    padding: 20px;
  }

  .company-modal__content {
    padding: 20px;
  }

  .company-modal__actions {
    padding: 16px 20px;
    flex-direction: column-reverse;
    gap: 12px;
  }

  .company-modal__actions .v-btn {
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
