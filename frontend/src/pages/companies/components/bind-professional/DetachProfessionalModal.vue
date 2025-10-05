<template>
  <BaseDialog
    :model-value="modelValue"
    title="Desassociar Profissional"
    subtitle="Remover profissional da empresa"
    icon="mdi-account-remove"
    icon-color="warning"
    max-width="500px"
    :fullscreen="$vuetify.display.mobile"
    @close="closeModal"
  >
    <div class="detach-confirm-modal__content">
      <div class="warning-message">
        <p class="text-body-1 mb-4">
          Tem certeza que deseja desassociar este profissional da empresa?
        </p>

        <div v-if="user" class="user-preview">
          <v-card variant="outlined" class="preview-card">
            <v-card-text>
              <div class="d-flex align-center">
                <v-avatar size="40" color="primary" class="mr-3">
                  <v-icon color="white" size="22">mdi-account</v-icon>
                </v-avatar>
                <div>
                  <div class="font-weight-medium text-body-1">
                    {{ user.name || 'Usuário não informado' }}
                  </div>
                  <div class="text-caption text-medium-emphasis">
                    {{ user.email || 'Email não informado' }}
                  </div>
                </div>
              </div>

              <v-divider class="my-3" />

              <div class="user-details">
                <div class="detail-row">
                  <v-icon size="16" class="mr-2">mdi-phone</v-icon>
                  <span>{{ user.phone ? formatPhone(user.phone) : 'Telefone não informado' }}</span>
                </div>
                <div class="detail-row">
                  <v-icon size="16" class="mr-2">mdi-account-tie</v-icon>
                  <span>{{ user.profile?.display_name || user.profile?.name || 'Perfil não informado' }}</span>
                </div>
                <div v-if="user.phone && user.has_whatsapp" class="detail-row">
                  <v-icon size="16" class="mr-2" color="success">mdi-whatsapp</v-icon>
                  <span>WhatsApp disponível</span>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </div>

        <v-alert
          type="warning"
          variant="tonal"
          class="mt-4"
          density="compact"
        >
          <template #prepend>
            <v-icon>mdi-information</v-icon>
          </template>
          <div>
            <strong>Atenção:</strong> Esta ação apenas remove a associação entre o profissional e a empresa.
            O profissional continuará existindo no sistema e poderá ser associado novamente.
          </div>
        </v-alert>
      </div>
    </div>

    <template #actions>
      <v-spacer />
      <div class="d-flex modal-actions-container">
        <BtnCancel @click="closeModal" />
        <BtnDelete
          variant="flat"
          size="default"
          :loading="loading"
          text="Desassociar Profissional"
          @click="handleConfirm"
        />
      </div>
    </template>
  </BaseDialog>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import { useMask } from '@/composables/useMask'
import BaseDialog from '@/components/BaseDialog.vue'
import { BtnCancel, BtnDelete } from '@/components/buttons'

interface Props {
  modelValue: boolean
  user?: any
}

interface Emits {
  (e: 'update:modelValue', value: boolean): void
  (e: 'confirm', user: any): void
}

const props = withDefaults(defineProps<Props>(), {
  user: null,
})

const emit = defineEmits<Emits>()

// Composables
const { formatPhone } = useMask()

// State
const loading = ref(false)

// Methods
const closeModal = () => {
  emit('update:modelValue', false)
}

const handleConfirm = async () => {
  loading.value = true

  try {
    emit('confirm', props.user)
    closeModal()
  } catch (error) {
    console.error('Erro ao desassociar profissional:', error)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.detach-confirm-modal__content {
  padding: 8px 0;
}

.warning-message {
  text-align: center;
}

.user-preview {
  margin: 16px 0;
}

.preview-card {
  border: 1px solid rgb(var(--v-theme-outline-variant));
}

.user-details {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.detail-row {
  display: flex;
  align-items: center;
  font-size: 0.875rem;
  color: rgb(var(--v-theme-on-surface-variant));
}

/* Responsive adjustments */
@media (max-width: 768px) {
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
