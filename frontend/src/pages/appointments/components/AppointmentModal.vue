<template>
  <BaseDialog
    v-model="isOpen"
    :title="isEdit ? 'Editar Agendamento' : 'Novo Agendamento'"
    :subtitle="
      isEdit ? 'Atualize as informações' : 'Preencha os dados para criar'
    "
    :icon="isEdit ? 'mdi-pencil' : 'mdi-plus'"
    :icon-color="isEdit ? 'warning' : 'success'"
    max-width="900px"
    :fullscreen="$vuetify.display.mobile"
    :show-progress="true"
    :progress="formProgress"
    @close="closeModal"
  >
    <v-form ref="formRef" v-model="isValid">
      <!-- Seção de Informações Básicas -->
      <div class="appointment-modal__section mb-8">
        <div class="d-flex align-center mb-4">
          <v-icon icon="mdi-information" color="primary" class="mr-2" />
          <h3 class="text-h6 font-weight-bold">Informações Básicas</h3>
        </div>
        <v-row>
          <!-- Cliente -->
          <v-col cols="12" md="6">
            <v-select
              :model-value="formData.client_id as any"
              :items="clients"
              item-title="name"
              item-value="id"
              label="Cliente"
              variant="outlined"
              density="comfortable"
              rounded="lg"
              :rules="[rules.required]"
              :loading="loadingClients"
              clearable
              prepend-inner-icon="mdi-account"
              @update:model-value="(value) => (formData.client_id = value)"
            >
              <template #item="{ props, item }">
                <v-list-item v-bind="props">
                  <template #prepend>
                    <v-avatar color="primary" size="32">
                      <v-icon icon="mdi-account" color="white" />
                    </v-avatar>
                  </template>
                  <v-list-item-title>{{ item.raw.name }}</v-list-item-title>
                  <v-list-item-subtitle>{{
                    item.raw.phone || "Telefone não informado"
                  }}</v-list-item-subtitle>
                </v-list-item>
              </template>
            </v-select>
          </v-col>

          <!-- Profissional -->
          <v-col cols="12" md="6">
            <v-select
              :model-value="formData.professional_id as any"
              :items="professionals"
              item-title="name"
              item-value="id"
              label="Profissional"
              variant="outlined"
              density="comfortable"
              rounded="lg"
              :rules="[rules.required]"
              :loading="loadingProfessionals"
              clearable
              prepend-inner-icon="mdi-account-tie"
              @update:model-value="
                (value) => (formData.professional_id = value)
              "
            >
              <template #item="{ props, item }">
                <v-list-item v-bind="props">
                  <template #prepend>
                    <v-avatar color="success" size="32">
                      <v-icon icon="mdi-account-tie" color="white" />
                    </v-avatar>
                  </template>
                  <v-list-item-title>{{ item.raw.name }}</v-list-item-title>
                  <v-list-item-subtitle>{{
                    item.raw.specialty || "Especialidade não informada"
                  }}</v-list-item-subtitle>
                </v-list-item>
              </template>
            </v-select>
          </v-col>

          <!-- Serviço -->
          <v-col cols="12">
            <v-select
              :model-value="formData.service_id as any"
              :items="services"
              item-title="name"
              item-value="id"
              label="Serviço"
              variant="outlined"
              density="comfortable"
              rounded="lg"
              :rules="[rules.required]"
              :loading="loadingServices"
              clearable
              prepend-inner-icon="mdi-medical-bag"
              @update:model-value="(value) => (formData.service_id = value)"
            >
              <template #item="{ props, item }">
                <v-list-item v-bind="props">
                  <template #prepend>
                    <v-avatar color="info" size="32">
                      <v-icon icon="mdi-medical-bag" color="white" />
                    </v-avatar>
                  </template>
                  <v-list-item-title>{{ item.raw.name }}</v-list-item-title>
                  <v-list-item-subtitle>
                    {{
                      item.raw.duration
                        ? `${item.raw.duration} min`
                        : "Duração não informada"
                    }}
                    -
                    {{
                      item.raw.price
                        ? `R$ ${item.raw.price}`
                        : "Preço não informado"
                    }}
                  </v-list-item-subtitle>
                </v-list-item>
              </template>
            </v-select>
          </v-col>
        </v-row>
      </div>

      <!-- Seção de Data e Hora -->
      <div class="appointment-modal__section mb-8">
        <div class="d-flex align-center mb-4">
          <v-icon icon="mdi-calendar-clock" color="primary" class="mr-2" />
          <h3 class="text-h6 font-weight-bold">Data e Hora</h3>
        </div>
        <v-row>
          <!-- Data -->
          <v-col cols="12" md="6">
            <v-text-field
              v-model="formData.date"
              label="Data do Agendamento"
              type="date"
              variant="outlined"
              density="comfortable"
              rounded="lg"
              :rules="[rules.required, rules.futureDate]"
              prepend-inner-icon="mdi-calendar"
              :min="today"
            />
          </v-col>

          <!-- Hora -->
          <v-col cols="12" md="6">
            <v-text-field
              v-model="formData.time"
              label="Horário"
              type="time"
              variant="outlined"
              density="comfortable"
              rounded="lg"
              :rules="[rules.required]"
              prepend-inner-icon="mdi-clock"
            />
          </v-col>
        </v-row>
      </div>

      <!-- Seção de Status e Observações -->
      <div class="appointment-modal__section">
        <div class="d-flex align-center mb-4">
          <v-icon icon="mdi-cog" color="primary" class="mr-2" />
          <h3 class="text-h6 font-weight-bold">Configurações</h3>
        </div>
        <v-row>
          <!-- Status -->
          <v-col cols="12" md="6">
            <v-select
              v-model="formData.status"
              :items="statusOptions"
              label="Status do Agendamento"
              variant="outlined"
              density="comfortable"
              rounded="lg"
              :rules="[rules.required]"
              prepend-inner-icon="mdi-flag"
            />
          </v-col>

          <!-- Observações -->
          <v-col cols="12">
            <v-textarea
              v-model="formData.notes"
              label="Observações Adicionais"
              variant="outlined"
              density="comfortable"
              rounded="lg"
              rows="3"
              counter="500"
              :rules="[rules.maxLength(500)]"
              prepend-inner-icon="mdi-note-text"
              placeholder="Adicione observações importantes sobre o agendamento..."
            />
          </v-col>
        </v-row>
      </div>
    </v-form>

    <template #actions>
      <v-spacer />
      <div class="d-flex modal-actions-container">
        <v-btn
          color="secondary"
          variant="outlined"
          rounded="lg"
          class="text-none font-weight-medium px-6 mr-4"
          size="large"
          @click="closeModal"
        >
          <v-icon icon="mdi-close" class="mr-2" />
          Cancelar
        </v-btn>
        <v-btn
          :color="isEdit ? 'warning' : 'success'"
          :loading="loading"
          rounded="lg"
          class="text-none font-weight-medium px-6"
          size="large"
          :disabled="!isValid"
          @click="handleSubmit"
        >
          <v-icon :icon="isEdit ? 'mdi-pencil' : 'mdi-plus'" class="mr-2" />
          {{ isEdit ? "Atualizar Agendamento" : "Criar Agendamento" }}
        </v-btn>
      </div>
    </template>
  </BaseDialog>
</template>

<script lang="ts" setup>
import { ref, computed, watch, onMounted } from "vue";
import { useClientsApi } from "@/pages/clients/api";
import { useAppointmentsApi } from "@/pages/appointments/api";
import BaseDialog from "@/components/BaseDialog.vue";

interface Appointment {
  id?: number | string;
  client_id?: number | string;
  professional_id?: number | string;
  service_id?: number | string;
  status: string;
  date: string;
  time: string;
  notes?: string;
}

interface Props {
  modelValue: boolean;
  appointment?: Appointment | null;
}

const props = withDefaults(defineProps<Props>(), {
  appointment: null,
});

const emit = defineEmits<{
  "update:modelValue": [value: boolean];
  success: [appointment: Appointment];
}>();

// Composables
const { getAll: getClients, loading: loadingClients } = useClientsApi();
const { getAll: getProfessionals } = useAppointmentsApi();

// Refs
const formRef = ref();
const isValid = ref(false);
const loading = ref(false);

// Data
const clients = ref<any[]>([]);
const professionals = ref<any[]>([]);
const services = ref<any[]>([]);
const loadingProfessionals = ref(false);
const loadingServices = ref(false);

// Form data
const formData = ref<Appointment>({
  status: "Pendente",
  date: "",
  time: "",
  notes: "",
});

// Computed
const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit("update:modelValue", value),
});

const isEdit = computed(() => !!props.appointment?.id);

const today = computed(() => {
  const today = new Date();
  return today.toISOString().split("T")[0];
});

const formProgress = computed(() => {
  const totalFields = 6;
  const filledFields = [
    formData.value.client_id,
    formData.value.professional_id,
    formData.value.service_id,
    formData.value.status,
    formData.value.date,
    formData.value.time,
  ].filter((field) => field && field !== "").length;

  return (filledFields / totalFields) * 100;
});

// Status options
const statusOptions = ["Pendente", "Confirmado", "Cancelado", "Concluído"];

// Validation rules
const rules = {
  required: (value: any) => !!value || "Campo obrigatório",
  maxLength: (max: number) => (value: string) =>
    !value || value.length <= max || `Máximo ${max} caracteres`,
  futureDate: (value: string) => {
    if (!value) return true;
    const selectedDate = new Date(value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return selectedDate >= today || "A data deve ser hoje ou no futuro";
  },
};

// Methods
const loadClients = async () => {
  try {
    const response = await getClients();
    clients.value = response.data || [];
  } catch (error) {
    console.error("Erro ao carregar clientes:", error);
  }
};

const loadProfessionals = async () => {
  try {
    loadingProfessionals.value = true;
    const response = await getProfessionals();
    professionals.value = response.data;
  } catch (error) {
    console.error("Erro ao carregar profissionais:", error);
  } finally {
    loadingProfessionals.value = false;
  }
};

const loadServices = async () => {
  try {
    loadingServices.value = true;
    // const response = await getServices();
    // services.value = response.data;
  } catch (error) {
    console.error("Erro ao carregar serviços:", error);
  } finally {
    loadingServices.value = false;
  }
};

const resetForm = () => {
  formData.value = {
    status: "Pendente",
    date: "",
    time: "",
    notes: "",
  };
  formRef.value?.reset();
};

const closeModal = () => {
  resetForm();
  isOpen.value = false;
};

const handleSubmit = async () => {
  const { valid } = await formRef.value.validate();
  if (!valid) return;

  loading.value = true;
  try {
    // Em produção, aqui seria feita a chamada para a API
    console.log("Dados do formulário:", formData.value);

    // Simular delay da API
    await new Promise((resolve) => setTimeout(resolve, 1000));

    emit("success", { ...formData.value });
    closeModal();
  } catch (error) {
    console.error("Erro ao salvar agendamento:", error);
  } finally {
    loading.value = false;
  }
};

// Watchers
watch(
  () => props.appointment,
  (newAppointment) => {
    if (newAppointment) {
      formData.value = { ...newAppointment };
    } else {
      resetForm();
    }
  },
  { immediate: true }
);

// Lifecycle
onMounted(() => {
  loadClients();
  loadProfessionals();
  loadServices();
});
</script>

<style scoped>
.appointment-modal__section {
  position: relative;
  padding: 24px;
  border-radius: 16px;
  background: rgba(var(--v-theme-surface), 0.5);
  border: 1px solid rgba(var(--v-theme-outline), 0.1);
  transition: all 0.3s ease;
}

.appointment-modal__section:hover {
  background: rgba(var(--v-theme-surface), 0.8);
  border-color: rgba(var(--v-theme-primary), 0.2);
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
}

.appointment-modal__section h3 {
  color: rgba(var(--v-theme-on-surface), 0.87);
  position: relative;
}

.appointment-modal__section h3::after {
  content: "";
  position: absolute;
  bottom: -8px;
  left: 0;
  width: 40px;
  height: 3px;
  background: linear-gradient(
    90deg,
    rgba(var(--v-theme-primary), 0.8) 0%,
    rgba(var(--v-theme-primary), 0.3) 100%
  );
  border-radius: 2px;
}

/* Form field improvements */
.appointment-modal :deep(.v-field) {
  border-radius: 12px;
  transition: all 0.3s ease;
}

.appointment-modal :deep(.v-field__outline) {
  border-radius: 12px;
  transition: all 0.3s ease;
}

.appointment-modal :deep(.v-field--focused .v-field__outline) {
  border-width: 2px;
  box-shadow: 0 0 0 4px rgba(var(--v-theme-primary), 0.1);
}

.appointment-modal :deep(.v-field--error .v-field__outline) {
  border-color: rgba(var(--v-theme-error), 0.8);
  box-shadow: 0 0 0 4px rgba(var(--v-theme-error), 0.1);
}

.appointment-modal :deep(.v-field--success .v-field__outline) {
  border-color: rgba(var(--v-theme-success), 0.8);
  box-shadow: 0 0 0 4px rgba(var(--v-theme-success), 0.1);
}

/* Select improvements */
.appointment-modal :deep(.v-list-item) {
  border-radius: 8px;
  margin: 4px 8px;
  transition: all 0.2s ease;
}

.appointment-modal :deep(.v-list-item:hover) {
  background: rgba(var(--v-theme-primary), 0.05);
  transform: translateX(4px);
}

.appointment-modal :deep(.v-list-item--active) {
  background: rgba(var(--v-theme-primary), 0.1);
}

/* Mobile responsiveness */
@media (max-width: 768px) {
  .appointment-modal__section {
    padding: 16px;
    margin-bottom: 16px;
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

/* Dark theme adjustments */
.v-theme--dark .appointment-modal__section {
  background: rgba(var(--v-theme-surface), 0.3);
  border-color: rgba(var(--v-theme-outline), 0.2);
}

.v-theme--dark .appointment-modal__section:hover {
  background: rgba(var(--v-theme-surface), 0.5);
  border-color: rgba(var(--v-theme-primary), 0.3);
}
</style>
