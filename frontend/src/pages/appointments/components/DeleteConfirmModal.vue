<template>
  <BaseDialog
    v-model="isOpen"
    title="Confirmar Exclusão"
    subtitle="Esta ação não pode ser desfeita"
    icon="mdi-delete"
    icon-color="error"
    max-width="500px"
    :fullscreen="$vuetify.display.mobile"
    @close="closeModal"
  >

    <div class="text-center">
          <!-- Ícone de alerta animado -->
          <div class="delete-confirm-modal__icon mb-6">
            <v-icon
              icon="mdi-alert-circle"
              color="warning"
              size="80"
              class="delete-confirm-modal__alert-icon"
            />
          </div>

          <!-- Mensagem principal -->
          <div class="delete-confirm-modal__message mb-6">
            <h3 class="text-h5 font-weight-bold mb-3">
              Tem certeza que deseja excluir este agendamento?
            </h3>
            <p class="text-body-1 text-medium-emphasis">
              Esta ação removerá permanentemente o agendamento do sistema e não poderá ser desfeita.
            </p>
          </div>

          <!-- Informações do item a ser excluído -->
          <v-card
            v-if="item"
            class="delete-confirm-modal__item-card pa-6"
            variant="outlined"
          >
            <div class="d-flex align-center">
              <v-avatar
                :color="getItemColor(item)"
                class="mr-4"
                size="48"
              >
                <v-icon :icon="getItemIcon(item)" color="white" />
              </v-avatar>
              <div class="text-left flex-grow-1">
                <h4 class="text-h6 font-weight-bold mb-1">
                  {{ getItemTitle(item) }}
                </h4>
                <p class="text-body-2 text-medium-emphasis mb-0">
                  {{ getItemSubtitle(item) }}
                </p>
              </div>
              <v-chip
                :color="getItemColor(item)"
                size="small"
                variant="flat"
                class="ml-2"
              >
                {{ item.status || 'Agendamento' }}
              </v-chip>
            </div>
          </v-card>
    </div>

    <template #actions>
      <v-spacer />
      <div class="d-flex modal-actions-container">
        <v-btn
          color="grey-darken-1"
          variant="outlined"
          rounded="lg"
          class="text-none font-weight-medium px-6 mr-4"
          size="large"
          @click="closeModal"
        >
          <v-icon icon="mdi-close" class="mr-2" />
          Fechar
        </v-btn>
        <v-btn
          color="error"
          :loading="loading"
          rounded="lg"
          class="text-none font-weight-medium px-6"
          size="large"
          @click="handleDelete"
        >
          <v-icon icon="mdi-delete" class="mr-2" />
          Excluir Agendamento
        </v-btn>
      </div>
    </template>
  </BaseDialog>
</template>

<script lang="ts" setup>
import { ref, computed } from 'vue'
import BaseDialog from '@/components/BaseDialog.vue'

interface DeleteItem {
  id: number | string
  [key: string]: any
}

interface Props {
  modelValue: boolean
  item?: DeleteItem | null
  itemType?: string
}

const props = withDefaults(defineProps<Props>(), {
  item: null,
  itemType: 'item'
})

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  'confirm': [item: DeleteItem]
}>()

// Refs
const loading = ref(false)

// Computed
const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

// Methods
const getItemColor = (item: DeleteItem) => {
  // Cores baseadas no tipo de item
  if (item.status === 'Confirmado') return 'success'
  if (item.status === 'Pendente') return 'warning'
  if (item.status === 'Cancelado') return 'error'
  return 'primary'
}

const getItemIcon = (item: DeleteItem) => {
  // Ícones baseados no tipo de item
  if (item.client) return 'mdi-account'
  if (item.professional) return 'mdi-account-tie'
  if (item.service) return 'mdi-medical-bag'
  return 'mdi-calendar'
}

const getItemTitle = (item: DeleteItem) => {
  // Título baseado no tipo de item
  if (item.client) return item.client
  if (item.professional) return item.professional
  if (item.service) return item.service
  if (item.title) return item.title
  return `Item #${item.id}`
}

const getItemSubtitle = (item: DeleteItem) => {
  // Subtítulo baseado no tipo de item
  if (item.phone) return item.phone
  if (item.email) return item.email
  if (item.date && item.time) return `${item.date} às ${item.time}`
  if (item.status) return item.status
  return props.itemType
}

const closeModal = () => {
  isOpen.value = false
}

const handleDelete = async () => {
  if (!props.item) return

  loading.value = true
  try {
    // Simular delay da API
    await new Promise(resolve => setTimeout(resolve, 500))

    emit('confirm', props.item)
    closeModal()
  } catch (error) {
    console.error('Erro ao excluir item:', error)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>

.delete-confirm-modal__icon {
  position: relative;
}

.delete-confirm-modal__alert-icon {
  animation: pulse 2s infinite;
  filter: drop-shadow(0 4px 8px rgba(var(--v-theme-warning), 0.3));
}

@keyframes pulse {
  0% {
    transform: scale(1);
    opacity: 1;
  }
  50% {
    transform: scale(1.05);
    opacity: 0.8;
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

.delete-confirm-modal__message h3 {
  color: rgba(var(--v-theme-on-surface), 0.87);
  position: relative;
}

.delete-confirm-modal__message h3::after {
  content: '';
  position: absolute;
  bottom: -8px;
  left: 50%;
  transform: translateX(-50%);
  width: 60px;
  height: 3px;
  background: linear-gradient(90deg,
    rgba(var(--v-theme-error), 0.8) 0%,
    rgba(var(--v-theme-error), 0.3) 100%
  );
  border-radius: 2px;
}

.delete-confirm-modal__item-card {
  border-radius: 16px;
  background: rgba(var(--v-theme-surface), 0.5);
  border: 1px solid rgba(var(--v-theme-outline), 0.1);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.delete-confirm-modal__item-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg,
    rgba(var(--v-theme-error), 0.02) 0%,
    transparent 50%,
    rgba(var(--v-theme-error), 0.02) 100%
  );
  pointer-events: none;
}

.delete-confirm-modal__item-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
  border-color: rgba(var(--v-theme-error), 0.2);
  background: rgba(var(--v-theme-surface), 0.8);
}

/* Mobile responsiveness */
@media (max-width: 768px) {
  .delete-confirm-modal__item-card {
    padding: 16px !important;
  }
}

/* Mobile responsiveness */
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

/* Dark theme adjustments */
.v-theme--dark .delete-confirm-modal__item-card {
  background: rgba(var(--v-theme-surface), 0.3);
  border-color: rgba(var(--v-theme-outline), 0.2);
}

.v-theme--dark .delete-confirm-modal__item-card:hover {
  background: rgba(var(--v-theme-surface), 0.5);
  border-color: rgba(var(--v-theme-error), 0.3);
}
</style>
