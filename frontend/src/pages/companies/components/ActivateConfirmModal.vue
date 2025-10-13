<template>
  <BaseDialog
    :model-value="modelValue"
    title="Confirmar Ativação"
    subtitle="A empresa voltará a funcionar normalmente"
    icon="mdi-check-circle-outline"
    icon-color="success"
    max-width="500px"
    :fullscreen="$vuetify.display.mobile"
    @close="closeModal"
  >
    <div v-if="item" class="activate-confirm">
      <!-- Success Icon -->
      <div class="icon-container mb-4">
        <v-icon size="64" color="success">mdi-check-circle-outline</v-icon>
      </div>

      <!-- Confirmation Message -->
      <div class="confirm-message mb-6">
        <h3 class="text-h6 font-weight-bold mb-3 text-center">
          Tem certeza que deseja ativar esta empresa?
        </h3>
        <p class="text-body-1 text-center text-medium-emphasis">
          A empresa <strong>"{{ item.name }}"</strong> será ativada e poderá ser utilizada normalmente.
        </p>
      </div>

      <!-- Company Details -->
      <div class="company-details">
        <v-card variant="outlined" class="pa-4">
          <div class="d-flex align-center mb-3">
            <v-icon size="20" color="primary" class="mr-2">mdi-domain</v-icon>
            <span class="text-subtitle-1 font-weight-medium">Detalhes da Empresa</span>
          </div>

          <div class="details-list">
            <div class="detail-item">
              <span class="detail-label">Nome:</span>
              <span class="detail-value">{{ item.name || "Não informado" }}</span>
            </div>

            <div v-if="item.cnpj" class="detail-item">
              <span class="detail-label">CNPJ:</span>
              <span class="detail-value">{{ formatCNPJ(item.cnpj) }}</span>
            </div>

            <div class="detail-item">
              <span class="detail-label">Responsável:</span>
              <span class="detail-value">{{ item.responsible_name || "Não informado" }}</span>
            </div>
          </div>
        </v-card>
      </div>

      <!-- Info Notice -->
      <div class="info-notice mt-4">
        <v-alert
          type="success"
          variant="tonal"
          density="compact"
          class="mb-0"
        >
          <template v-slot:prepend>
            <v-icon>mdi-information</v-icon>
          </template>
          <div class="text-body-2">
            <strong>Informação:</strong> Após ativar, a empresa poderá ser utilizada normalmente no sistema.
          </div>
        </v-alert>
      </div>
    </div>

    <template #actions>
      <v-spacer />
      <div class="d-flex modal-actions-container">
        <v-btn
          variant="outlined"
          color="default"
          @click="closeModal"
          :disabled="loading"
          class="cancel-btn"
        >
          <v-icon start>mdi-close</v-icon>
          Cancelar
        </v-btn>
        <v-btn
          color="success"
          variant="flat"
          @click="handleConfirm"
          :loading="loading"
          class="confirm-btn"
        >
          <v-icon start>mdi-check-circle</v-icon>
          Ativar Empresa
        </v-btn>
      </div>
    </template>
  </BaseDialog>
</template>

<script lang="ts" setup>
import { ref } from "vue";
import { useMask } from "@/composables/useMask";

interface Props {
  modelValue: boolean;
  item?: any;
}

interface Emits {
  (e: "update:modelValue", value: boolean): void;
  (e: "confirm", item: any): void;
}

const props = withDefaults(defineProps<Props>(), {
  item: null,
});

const emit = defineEmits<Emits>();

// Reactive data
const loading = ref(false);

// Methods
const closeModal = () => {
  emit("update:modelValue", false);
};

const handleConfirm = () => {
  loading.value = true;
  emit("confirm", props.item);
  // Loading will be set to false by parent component
};

// Função de formatação
const { formatCNPJ } = useMask();

// Expose method to parent
defineExpose({
  setLoading: (value: boolean) => {
    loading.value = value;
  },
});
</script>

<style scoped>
.activate-confirm {
  padding: 8px;
}

.icon-container {
  display: flex;
  justify-content: center;
  align-items: center;
}

.confirm-message {
  text-align: center;
}

.company-details {
  margin-bottom: 16px;
}

.details-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 4px 0;
}

.detail-label {
  font-weight: 500;
  color: rgba(var(--v-theme-on-surface), 0.87);
  font-size: 0.875rem;
}

.detail-value {
  color: rgba(var(--v-theme-on-surface), 0.6);
  font-size: 0.875rem;
  text-align: right;
  max-width: 60%;
  word-break: break-word;
}

.info-notice {
  margin-top: 16px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .icon-container {
    margin-bottom: 24px;
  }

  .icon-container .v-icon {
    font-size: 48px !important;
  }

  .confirm-message {
    margin-bottom: 24px;
  }

  .confirm-message h3 {
    font-size: 1.25rem;
  }

  .modal-actions-container {
    flex-direction: column;
    gap: 12px;
    width: 100%;
  }

  .modal-actions-container .v-btn {
    width: 100%;
    margin-right: 0 !important;
  }

  .detail-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
  }

  .detail-value {
    text-align: left;
    max-width: 100%;
  }
}

/* Desktop styles */
.modal-actions-container {
  gap: 16px;
}

.cancel-btn {
  min-width: 120px;
}

.confirm-btn {
  min-width: 140px;
}
</style>

