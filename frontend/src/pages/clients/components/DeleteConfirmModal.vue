<template>
  <BaseDialog
    :model-value="modelValue"
    title="Confirmar Exclusão"
    subtitle="Esta ação não pode ser desfeita"
    icon="mdi-alert-circle"
    icon-color="error"
    max-width="500px"
    :fullscreen="$vuetify.display.mobile"
    @close="closeModal"
  >
    <div class="delete-confirm-modal__content">
      <div class="warning-message">
        <p class="text-body-1 mb-4">
          Tem certeza que deseja excluir este cliente?
        </p>

        <div v-if="client" class="item-preview">
          <v-card variant="outlined" class="preview-card">
            <v-card-text>
              <div class="d-flex align-center">
                <v-avatar size="40" color="primary" class="mr-3">
                  <v-icon color="white" size="22">mdi-account</v-icon>
                </v-avatar>
                <div>
                  <div class="font-weight-medium text-body-1">
                    {{ client.name || 'Cliente não informado' }}
                  </div>
                  <div class="text-caption text-medium-emphasis">
                    {{ client.email || 'E-mail não informado' }}
                  </div>
                </div>
              </div>

              <v-divider class="my-3" />

              <div class="item-details">
                <div class="detail-row" v-if="client.phone">
                  <v-icon size="16" class="mr-2">mdi-phone</v-icon>
                  <span>{{ client.phone }}</span>
                </div>
                <div class="detail-row">
                  <v-icon size="16" class="mr-2">mdi-calendar</v-icon>
                  <span>Criado em {{ formatDate(client.created_at) }}</span>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </div>

        <v-alert
          type="warning"
          variant="tonal"
          class="mt-4"
        >
          <template #prepend>
            <v-icon>mdi-alert</v-icon>
          </template>
          <div class="text-body-2">
            <strong>Atenção:</strong> Esta ação não pode ser desfeita. Todos os dados relacionados a este cliente serão removidos permanentemente do sistema.
          </div>
        </v-alert>
      </div>
    </div>

    <template #actions>
      <v-spacer />
      <div class="d-flex modal-actions-container">
        <BtnCancel
          variant="outlined"
          @click="closeModal"
        />
        <BtnDelete
          variant="flat"
          size="default"
          :loading="loading"
          @click="handleConfirm"
        />
      </div>
    </template>
  </BaseDialog>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import BaseDialog from '@/components/BaseDialog.vue'
import { BtnCancel, BtnDelete } from '@/components/buttons'
import type { Client } from '../api'

interface Props {
  modelValue: boolean
  client?: Client | null
}

interface Emits {
  (e: 'update:modelValue', value: boolean): void
  (e: 'confirm', client: Client): void
}

const props = withDefaults(defineProps<Props>(), {
  client: null
})

const emit = defineEmits<Emits>()

// Reactive data
const loading = ref(false)

// Methods
const formatDate = (dateString: string) => {
  if (!dateString) return 'Data não informada'

  try {
    const date = new Date(dateString)
    return date.toLocaleDateString('pt-BR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    })
  } catch (error) {
    return dateString
  }
}

const closeModal = () => {
  emit('update:modelValue', false)
}

const handleConfirm = async () => {
  if (!props.client) return

  loading.value = true

  try {
    emit('confirm', props.client)
    closeModal()
  } catch (error) {
    console.error('Erro ao excluir cliente:', error)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.delete-confirm-modal__content {
  padding: 24px;
}

.warning-message {
  text-align: center;
}

.item-preview {
  margin: 16px 0;
}

.preview-card {
  border-radius: 12px;
  border: 1px solid #e5e7eb;
}

.item-details {
  margin-top: 8px;
}

.detail-row {
  display: flex;
  align-items: center;
  margin-bottom: 8px;
  color: rgba(var(--v-theme-on-surface), 0.7);
  font-size: 0.9rem;
}

.detail-row:last-child {
  margin-bottom: 0;
}

.modal-actions-container {
  gap: 16px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .delete-confirm-modal__content {
    padding: 20px;
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

