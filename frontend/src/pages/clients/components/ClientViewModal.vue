<template>
  <BaseDialog
    :model-value="modelValue"
    :title="`Visualizar Cliente - ${client?.name || 'Nome não informado'}`"
    subtitle="Informações detalhadas do cliente"
    icon="mdi-eye"
    icon-color="primary"
    :fullscreen="$vuetify.display.mobile"
    @close="closeModal"
  >
    <div v-if="client" class="client-view">
      <!-- Client Information -->
      <div class="client-info mb-6">
        <h3 class="section-title mb-4">
          <v-icon size="22" class="mr-3">mdi-information</v-icon>
          Informações Pessoais
        </h3>

        <div class="info-grid">
          <!-- Nome -->
          <div class="info-item">
            <div class="info-label">
              <v-icon size="20" color="primary" class="mr-3">mdi-account</v-icon>
              Nome Completo
            </div>
            <div class="info-value">
              <span class="value-text">{{
                client?.name || "Não informado"
              }}</span>
            </div>
          </div>

          <!-- Email -->
          <div class="info-item">
            <div class="info-label">
              <v-icon size="20" color="primary" class="mr-3">mdi-email</v-icon>
              E-mail
            </div>
            <div class="info-value">
              <span class="value-text">{{
                client?.email || "Não informado"
              }}</span>
            </div>
          </div>

          <!-- Telefone -->
          <div class="info-item" v-if="client?.phone">
            <div class="info-label">
              <v-icon size="20" color="primary" class="mr-3">mdi-phone</v-icon>
              Telefone
            </div>
            <div class="info-value">
              <span class="phone-number">{{ formatPhone(client.phone) }}</span>
            </div>
          </div>

          <!-- CPF -->
          <div class="info-item" v-if="client?.cpf">
            <div class="info-label">
              <v-icon size="20" color="primary" class="mr-3">mdi-card-account-details</v-icon>
              CPF
            </div>
            <div class="info-value">
              <span class="value-text">{{ client.cpf }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Companies Information -->
      <div class="companies-info mb-6" v-if="client?.companies?.length">
        <h3 class="section-title mb-4">
          <v-icon size="22" class="mr-3">mdi-domain</v-icon>
          Empresas Associadas
        </h3>

        <div class="companies-grid">
          <v-card
            v-for="company in client.companies"
            :key="company.id"
            variant="outlined"
            class="company-card"
          >
            <v-card-text class="pa-3">
              <div class="d-flex align-center">
                <v-icon color="primary" class="mr-3">mdi-domain</v-icon>
                <span class="font-weight-medium">{{ company.name }}</span>
              </div>
            </v-card-text>
          </v-card>
        </div>
      </div>

      <!-- Client Metadata -->
      <div class="client-metadata">
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
              <span class="date-text">{{ formatDate(client?.created_at) }}</span>
            </div>
          </div>

          <!-- Updated At -->
          <div class="metadata-item">
            <div class="metadata-label">
              <v-icon size="20" color="info" class="mr-3">mdi-calendar-edit</v-icon>
              Última Atualização
            </div>
            <div class="metadata-value">
              <span class="date-text">{{ formatDate(client?.updated_at) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <template #actions>
      <v-spacer />
      <div class="d-flex modal-actions-container">
        <BtnCancel
          text="Fechar"
          @click="closeModal"
        />
        <BtnEdit
          :icon-only="false"
          size="default"
          variant="flat"
          @click="editClient"
        />
      </div>
    </template>
  </BaseDialog>
</template>

<script lang="ts" setup>
import BaseDialog from '@/components/BaseDialog.vue'
import { BtnCancel, BtnEdit } from '@/components/buttons'
import type { Client } from '../api'

interface Props {
  modelValue: boolean
  client?: Client | null
}

interface Emits {
  (e: 'update:modelValue', value: boolean): void
  (e: 'edit', client: Client): void
  (e: 'close'): void
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: false,
  client: null
})

const emit = defineEmits<Emits>()

// Methods
const closeModal = () => {
  emit('update:modelValue', false)
  emit('close')
}

const editClient = () => {
  if (props.client) {
    emit('edit', props.client)
  }
}

const formatDate = (date: string | undefined) => {
  if (!date) return 'Não informado'
  try {
    const d = new Date(date)
    return d.toLocaleDateString('pt-BR', {
      day: '2-digit',
      month: 'long',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch {
    return date
  }
}

const formatPhone = (phone: string | undefined) => {
  if (!phone) return 'Não informado'
  const cleaned = phone.replace(/\D/g, '')
  if (cleaned.length === 11) {
    return `(${cleaned.slice(0, 2)}) ${cleaned.slice(2, 7)}-${cleaned.slice(7)}`
  } else if (cleaned.length === 10) {
    return `(${cleaned.slice(0, 2)}) ${cleaned.slice(2, 6)}-${cleaned.slice(6)}`
  }
  return phone
}
</script>

<style scoped>
.client-view {
  padding: 0;
}

/* Section Title */
.section-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: rgb(var(--v-theme-on-surface));
  display: flex;
  align-items: center;
  padding-bottom: 12px;
  border-bottom: 2px solid rgba(var(--v-theme-primary), 0.1);
}

/* Info Grid */
.info-grid {
  display: grid;
  gap: 24px;
  grid-template-columns: 1fr;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.info-label {
  display: flex;
  align-items: center;
  font-size: 0.875rem;
  font-weight: 600;
  color: rgb(var(--v-theme-on-surface-variant));
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.info-value {
  padding-left: 32px;
}

.value-text {
  font-size: 1rem;
  font-weight: 500;
  color: rgb(var(--v-theme-on-surface));
}

.phone-number {
  font-size: 1rem;
  font-weight: 500;
  color: rgb(var(--v-theme-on-surface));
  font-family: 'Courier New', monospace;
}

/* Companies Grid */
.companies-grid {
  display: grid;
  gap: 12px;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
}

.company-card {
  transition: all 0.2s ease;
}

.company-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Metadata Grid */
.metadata-grid {
  display: grid;
  gap: 20px;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
}

.metadata-item {
  padding: 16px;
  background: rgba(var(--v-theme-surface-variant), 0.3);
  border-radius: 12px;
  border: 1px solid rgba(var(--v-theme-primary), 0.1);
}

.metadata-label {
  display: flex;
  align-items: center;
  font-size: 0.8125rem;
  font-weight: 600;
  color: rgb(var(--v-theme-on-surface-variant));
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 8px;
}

.metadata-value {
  padding-left: 32px;
}

.date-text {
  font-size: 0.9375rem;
  font-weight: 500;
  color: rgb(var(--v-theme-on-surface));
}

.modal-actions-container {
  gap: 12px;
}

/* Responsive */
@media (max-width: 768px) {
  .info-grid {
    grid-template-columns: 1fr;
  }

  .companies-grid {
    grid-template-columns: 1fr;
  }

  .metadata-grid {
    grid-template-columns: 1fr;
  }

  .modal-actions-container {
    flex-direction: column;
    gap: 8px;
    width: 100%;
  }

  .modal-actions-container :deep(.v-btn) {
    width: 100%;
  }
}
</style>

