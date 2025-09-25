<template>
  <v-dialog
    :model-value="modelValue"
    @update:model-value="$emit('update:modelValue', $event)"
    max-width="500px"
    persistent
  >
    <v-card class="delete-confirm-modal">
      <v-card-title class="delete-confirm-modal__header">
        <div class="d-flex align-center">
          <v-icon
            color="error"
            size="28"
            class="mr-3"
          >
            mdi-alert-circle
          </v-icon>
          <span class="text-h5 font-weight-bold">
            Confirmar Exclusão
          </span>
        </div>
      </v-card-title>

      <v-card-text class="delete-confirm-modal__content">
        <div class="warning-message">
          <p class="text-body-1 mb-4">
            Tem certeza que deseja excluir este {{ itemType }}?
          </p>

          <div v-if="item" class="item-preview">
            <v-card variant="outlined" class="preview-card">
              <v-card-text>
                <div class="d-flex align-center">
                  <v-avatar size="40" color="primary" class="mr-3">
                    <v-icon color="white" size="22">mdi-account-tie</v-icon>
                  </v-avatar>
                  <div>
                    <div class="font-weight-medium text-body-1">
                      {{ item.display_name || 'Perfil não informado' }}
                    </div>
                    <div class="text-caption text-medium-emphasis">
                      {{ item.name || 'Nome não informado' }}
                    </div>
                  </div>
                </div>

                <v-divider class="my-3" />

                <div class="item-details">
                  <div class="detail-row">
                    <v-icon size="16" class="mr-2">mdi-text</v-icon>
                    <span>{{ item.description || 'Sem descrição' }}</span>
                  </div>
                  <div class="detail-row">
                    <v-icon size="16" class="mr-2">mdi-shield-account</v-icon>
                    <span>{{ item.abilities?.length || 0 }} abilities atribuídas</span>
                  </div>
                  <div class="detail-row">
                    <v-icon size="16" class="mr-2">mdi-calendar</v-icon>
                    <span>Criado em {{ formatDate(item.created_at) }}</span>
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
              <strong>Atenção:</strong> Esta ação não pode ser desfeita. Todos os dados relacionados a este {{ itemType }} serão removidos permanentemente do sistema.
            </div>
          </v-alert>
        </div>
      </v-card-text>

      <v-card-actions class="delete-confirm-modal__actions">
        <v-spacer />
        <v-btn
          color="secondary"
          variant="outlined"
          rounded="lg"
          class="text-none font-weight-medium"
          @click="closeModal"
        >
          Cancelar
        </v-btn>
        <v-btn
          color="error"
          variant="flat"
          rounded="lg"
          class="text-none font-weight-medium"
          :loading="loading"
          @click="handleConfirm"
        >
          Excluir {{ itemType }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script lang="ts" setup>
import { ref } from 'vue'

interface Props {
  modelValue: boolean
  item?: any
  itemType?: string
}

interface Emits {
  (e: 'update:modelValue', value: boolean): void
  (e: 'confirm', item: any): void
}

const props = withDefaults(defineProps<Props>(), {
  item: null,
  itemType: 'item'
})

const emit = defineEmits<Emits>()

// Reactive data
const loading = ref(false)

// Methods
const formatDate = (dateString: string) => {
  if (!dateString) return 'Data não informada';

  try {
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    });
  } catch (error) {
    return dateString;
  }
};

const closeModal = () => {
  emit('update:modelValue', false);
};

const handleConfirm = async () => {
  loading.value = true;

  try {
    emit('confirm', props.item);
    closeModal();
  } catch (error) {
    console.error('Erro ao excluir item:', error);
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.delete-confirm-modal {
  border-radius: 16px;
  overflow: hidden;
}

.delete-confirm-modal__header {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
  padding: 24px;
}

.delete-confirm-modal__content {
  padding: 24px;
}

.delete-confirm-modal__actions {
  padding: 16px 24px;
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
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

/* Responsive adjustments */
@media (max-width: 768px) {
  .delete-confirm-modal__header {
    padding: 20px;
  }

  .delete-confirm-modal__content {
    padding: 20px;
  }

  .delete-confirm-modal__actions {
    padding: 16px 20px;
    flex-direction: column-reverse;
    gap: 12px;
  }

  .delete-confirm-modal__actions .v-btn {
    width: 100%;
  }
}
</style>
