<template>
  <BaseDialog
    :model-value="modelValue"
    :title="`Visualizar Empresa - ${company?.name || 'Nome não informado'}`"
    subtitle="Informações detalhadas da empresa"
    icon="mdi-eye"
    icon-color="primary"
    :fullscreen="$vuetify.display.mobile"
    @close="closeModal"
  >
    <div v-if="company" class="company-view">
      <!-- Company Information -->
      <div class="company-info mb-6">
        <h3 class="section-title mb-4">
          <v-icon size="22" class="mr-3">mdi-information</v-icon>
          Informações da Empresa
        </h3>

        <div class="info-grid">
          <!-- Tipo de Pessoa -->
          <div class="info-item">
            <div class="info-label">
              <v-icon size="20" color="primary" class="mr-3">mdi-account-group</v-icon>
              Tipo de Pessoa
            </div>
            <div class="info-value">
              <span class="value-text">{{ company.person_type === 'legal' ? 'Pessoa Jurídica' : 'Pessoa Física' }}</span>
            </div>
          </div>

          <!-- CNPJ/CPF -->
          <div class="info-item" v-if="(company.person_type === 'legal' && company.cnpj) || (company.person_type === 'physical' && company.cpf)">
            <div class="info-label">
              <v-icon size="20" color="primary" class="mr-3">mdi-card-account-details</v-icon>
              {{ company.person_type === 'legal' ? 'CNPJ' : 'CPF' }}
            </div>
            <div class="info-value">
              <span class="document-text">{{ company.person_type === 'legal' ? formatCNPJ(company.cnpj) : formatCPF(company.cpf) }}</span>
            </div>
          </div>

          <!-- Responsável -->
          <div class="info-item">
            <div class="info-label">
              <v-icon size="20" color="primary" class="mr-3">mdi-account</v-icon>
              Responsável pela Empresa
            </div>
            <div class="info-value">
              <span class="value-text">{{ company.responsible_name || "Não informado" }}</span>
            </div>
          </div>

          <!-- Timezone -->
          <div class="info-item">
            <div class="info-label">
              <v-icon size="20" color="info" class="mr-3">mdi-clock-outline</v-icon>
              Fuso Horário
            </div>
            <div class="info-value">
              <div v-if="company.timezone" class="timezone-info">
                <div class="timezone-name">{{ company.timezone.name }}</div>
                <div class="timezone-chips">
                  <v-chip size="small" color="primary" variant="tonal">
                    <v-icon start size="14">mdi-earth</v-icon>
                    {{ company.timezone.region }}
                  </v-chip>
                  <v-chip size="small" color="info" variant="tonal">
                    <v-icon start size="14">mdi-clock</v-icon>
                    {{ company.timezone.offset }}
                  </v-chip>
                </div>
              </div>
              <span v-else class="text-medium-emphasis">Não definido</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Contact Information -->
      <div class="contact-info mb-6">
        <h3 class="section-title mb-4">
          <v-icon size="22" class="mr-3">mdi-phone</v-icon>
          Contato
        </h3>

        <div class="info-grid">
          <!-- Telefone Principal -->
          <div class="info-item">
            <div class="info-label">
              <v-icon size="20" color="primary" class="mr-3">mdi-phone</v-icon>
              Telefone Principal
              <v-chip v-if="company.has_whatsapp_1" color="success" size="small" class="ml-3">
                <v-icon start size="14">mdi-whatsapp</v-icon>
                WhatsApp
              </v-chip>
            </div>
            <div class="info-value">
              <span class="phone-number">{{ formatPhone(company.phone_1) }}</span>
            </div>
          </div>

          <!-- Telefone Secundário -->
          <div class="info-item" v-if="company.phone_2">
            <div class="info-label">
              <v-icon size="20" color="primary" class="mr-3">mdi-phone-outgoing</v-icon>
              Telefone Secundário
              <v-chip v-if="company.has_whatsapp_2" color="success" size="small" class="ml-3">
                <v-icon start size="14">mdi-whatsapp</v-icon>
                WhatsApp
              </v-chip>
            </div>
            <div class="info-value">
              <span class="phone-number">{{ formatPhone(company.phone_2) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Company Metadata -->
      <div class="company-metadata">
        <h3 class="section-title mb-4">
          <v-icon size="22" class="mr-3">mdi-calendar</v-icon>
          Informações do Sistema
        </h3>

        <div class="metadata-grid">
          <!-- Created At -->
          <div class="metadata-item">
            <div class="metadata-label">
              <v-icon size="20" color="success" class="mr-3">mdi-calendar-plus</v-icon>
              Data de Criação
            </div>
            <div class="metadata-value">
              <span class="date-text">{{ formatDate(company.created_at) }}</span>
            </div>
          </div>

          <!-- Updated At -->
          <div class="metadata-item">
            <div class="metadata-label">
              <v-icon size="20" color="warning" class="mr-3">mdi-calendar-edit</v-icon>
              Última Atualização
            </div>
            <div class="metadata-value">
              <span class="date-text">{{ formatDate(company.updated_at) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-else class="loading-state">
      <v-progress-circular indeterminate color="primary" size="48" />
      <p class="text-body-1 mt-4">Carregando informações da empresa...</p>
    </div>

    <template #actions>
      <v-spacer />
      <div class="d-flex modal-actions-container">
        <BtnCancel @click="closeModal" />
        <BtnEdit @click="handleEdit" size="default" v-if="hasPermission('companies.edit')" variant="flat" />
      </div>
    </template>
  </BaseDialog>
</template>

<script lang="ts" setup>
import { computed } from "vue";
import { useMask } from "@/composables/useMask";
import { useAbilities } from "@/composables/useAbilities";

interface Props {
  modelValue: boolean;
  company?: any;
}

interface Emits {
  (e: "update:modelValue", value: boolean): void;
  (e: "edit", company: any): void;
  (e: "reload"): void;
}

const props = withDefaults(defineProps<Props>(), {
  company: null,
});

const emit = defineEmits<Emits>();

// Computed
const company = computed(() => props.company);

const { hasPermission } = useAbilities();

// Methods
const closeModal = () => {
  emit("update:modelValue", false);
};

const handleEdit = () => {
  emit("edit", props.company);
  closeModal();
};

// Funções de formatação
const { formatCNPJ, formatCPF, formatPhone } = useMask();

const formatDate = (dateString: string) => {
  if (!dateString) return "Data não informada";

  try {
    const date = new Date(dateString);
    return date.toLocaleDateString("pt-BR", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  } catch (error) {
    return dateString;
  }
};
</script>

<style scoped>
.company-view {
  padding: 12px;
}


.section-title {
  display: flex;
  align-items: center;
  font-weight: 600;
  color: rgba(var(--v-theme-on-surface), 0.87);
  font-size: 1.125rem;
  padding-bottom: 8px;
  border-bottom: 2px solid rgba(var(--v-theme-primary), 0.2);
}

.info-grid,
.metadata-grid {
  display: grid;
  gap: 20px;
}

.info-item,
.metadata-item {
  display: flex;
  flex-direction: column;
  gap: 12px;
  padding: 20px;
  background: rgba(var(--v-theme-surface-variant), 0.4);
  border-radius: 16px;
  border: 1px solid rgba(var(--v-theme-outline), 0.15);
  transition: all 0.2s ease;
  position: relative;
}

.info-item:hover,
.metadata-item:hover {
  background: rgba(var(--v-theme-surface-variant), 0.6);
  border-color: rgba(var(--v-theme-primary), 0.2);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(var(--v-theme-primary), 0.1);
}

.info-label,
.metadata-label {
  display: flex;
  align-items: center;
  font-weight: 600;
  color: rgba(var(--v-theme-on-surface), 0.87);
  font-size: 0.95rem;
}

.info-value,
.metadata-value {
  color: rgba(var(--v-theme-on-surface), 0.7);
  font-size: 0.95rem;
}

.value-text {
  font-weight: 500;
  color: rgba(var(--v-theme-on-surface), 0.87);
}

.document-text {
  font-family: 'Roboto Mono', monospace;
  font-weight: 500;
  color: rgba(var(--v-theme-on-surface), 0.87);
  font-size: 1rem;
  letter-spacing: 0.5px;
}

.phone-number {
  font-family: 'Roboto Mono', monospace;
  font-weight: 500;
  color: rgba(var(--v-theme-on-surface), 0.87);
  font-size: 1rem;
}

.date-text {
  font-family: 'Roboto Mono', monospace;
  font-weight: 500;
  color: rgba(var(--v-theme-on-surface), 0.87);
  font-size: 0.9rem;
}

.timezone-info {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.timezone-name {
  font-weight: 600;
  color: rgba(var(--v-theme-on-surface), 0.87);
  font-size: 1rem;
}

.timezone-chips {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}


.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 64px 24px;
  color: rgba(var(--v-theme-on-surface), 0.6);
}

.loading-state p {
  margin-top: 16px;
  font-size: 1rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .info-item,
  .metadata-item {
    padding: 16px;
  }

  .section-title {
    font-size: 1rem;
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

  .timezone-chips {
    flex-direction: column;
  }
}

@media (max-width: 480px) {
  .company-view {
    padding: 8px;
  }

  .info-item,
  .metadata-item {
    padding: 12px;
  }

}

/* Desktop styles */
.modal-actions-container {
  gap: 16px;
}
</style>
