<template>
  <v-dialog
    v-model="isOpen"
    :max-width="maxWidth"
    :fullscreen="fullscreen"
    persistent
    scrollable
    class="base-dialog"
  >
    <v-card class="base-dialog__card">
      <!-- Header -->
      <v-card-title class="base-dialog__header d-flex align-center justify-space-between">
        <div class="d-flex align-center">
          <v-avatar
            :color="iconColor"
            size="48"
            class="mr-4"
          >
            <v-icon :icon="icon" color="white" />
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-1">
              {{ title }}
            </h2>
            <p class="text-body-2 text-medium-emphasis mb-0 base-dialog__subtitle">
              {{ subtitle }}
            </p>
          </div>
        </div>
        <v-btn
          icon="mdi-close"
          variant="text"
          size="large"
          class="base-dialog__close-btn"
          @click="closeDialog"
        />
      </v-card-title>

      <!-- Progress indicator (opcional) -->
      <v-progress-linear
        v-if="showProgress"
        :model-value="progress"
        :color="progressColor"
        height="4"
        class="base-dialog__progress"
      />

      <v-divider />

      <!-- Content -->
      <v-card-text class="base-dialog__content">
        <slot />
      </v-card-text>

      <!-- Actions (opcional) -->
      <div v-if="$slots.actions" class="base-dialog__actions">
        <v-divider />
        <div class="pa-6">
          <slot name="actions" />
        </div>
      </div>
    </v-card>
  </v-dialog>
</template>

<script lang="ts" setup>
import { computed } from 'vue'

interface Props {
  modelValue: boolean
  title: string
  subtitle: string
  icon: string
  iconColor?: string
  maxWidth?: string | number
  fullscreen?: boolean
  showProgress?: boolean
  progress?: number
  progressColor?: string
}

const props = withDefaults(defineProps<Props>(), {
  iconColor: 'primary',
  maxWidth: '900px',
  fullscreen: false,
  showProgress: false,
  progress: 0,
  progressColor: 'primary'
})

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  'close': []
}>()

// Computed
const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

// Methods
const closeDialog = () => {
  emit('close')
  isOpen.value = false
}
</script>

<style scoped>
/* Aplicar bordas arredondadas no dialog */
.base-dialog :deep(.v-dialog) {
  border-radius: 20px !important;
  overflow: hidden;
}

.base-dialog :deep(.v-dialog .v-card) {
  border-radius: 20px !important;
  overflow: hidden;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.base-dialog :deep(.v-dialog .v-overlay__content) {
  border-radius: 20px !important;
  overflow: hidden;
}

.base-dialog :deep(.v-dialog .v-overlay__content .v-card) {
  border-radius: 20px !important;
  overflow: hidden;
}

/* Forçar bordas arredondadas em todos os elementos do modal */
.base-dialog :deep(.v-overlay__content) {
  border-radius: 20px !important;
  overflow: hidden;
}

.base-dialog :deep(.v-overlay__content > .v-card) {
  border-radius: 20px !important;
  overflow: hidden;
}

.base-dialog__card {
  border-radius: 20px !important;
  overflow: hidden;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.base-dialog__header {
  background: linear-gradient(135deg,
    rgba(var(--v-theme-primary), 0.1) 0%,
    rgba(var(--v-theme-primary), 0.05) 50%,
    rgba(var(--v-theme-surface), 0.8) 100%
  );
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(var(--v-theme-primary), 0.1);
  position: relative;
  overflow: hidden;
}

.base-dialog__header::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(45deg,
    rgba(var(--v-theme-primary), 0.05) 0%,
    transparent 50%,
    rgba(var(--v-theme-primary), 0.05) 100%
  );
  pointer-events: none;
}

.base-dialog__close-btn {
  position: relative;
  z-index: 1;
  transition: all 0.2s ease;
}

.base-dialog__close-btn:hover {
  transform: scale(1.1);
  background: rgba(var(--v-theme-error), 0.1);
}

.base-dialog__progress {
  position: relative;
  z-index: 1;
}

.base-dialog__content {
  padding: 24px;
}

.base-dialog__actions {
  background: rgba(var(--v-theme-surface), 0.8);
  backdrop-filter: blur(10px);
  border-top: 1px solid rgba(var(--v-theme-outline), 0.1);
}

.base-dialog__actions > div {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}

.base-dialog__actions .d-flex {
  gap: 12px;
  align-items: center;
}

.base-dialog__actions .d-flex.gap-3 {
  gap: 12px;
}

.base-dialog__actions .v-btn {
  flex-shrink: 0;
}

.base-dialog__actions .v-spacer {
  flex: 1 1 auto;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
  .base-dialog__header {
    padding: 16px;
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }

  .base-dialog__header .d-flex.align-center {
    width: 100%;
  }

  .base-dialog__close-btn {
    align-self: flex-end;
    margin-top: -8px;
  }

  .base-dialog__subtitle {
    font-size: 0.875rem !important;
    line-height: 1.25rem !important;
  }

  .base-dialog__content {
    padding: 16px;
  }

  .base-dialog__actions > div {
    flex-direction: column;
    align-items: stretch;
    gap: 12px;
  }

  .base-dialog__actions .d-flex {
    flex-direction: column;
    align-items: stretch;
    gap: 8px;
  }

  .base-dialog__actions .v-btn {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .base-dialog__subtitle {
    font-size: 0.8125rem !important;
    line-height: 1.125rem !important;
  }
}

/* Dark theme adjustments */
.v-theme--dark .base-dialog__header {
  background: linear-gradient(135deg,
    rgba(var(--v-theme-primary), 0.15) 0%,
    rgba(var(--v-theme-primary), 0.08) 50%,
    rgba(var(--v-theme-surface), 0.9) 100%
  );
}

.v-theme--dark .base-dialog__actions {
  background: rgba(var(--v-theme-surface), 0.6);
}
</style>

<style>
/* CSS global para garantir bordas arredondadas nos modais */
.v-dialog .v-overlay__content {
  border-radius: 20px !important;
  overflow: hidden;
}

.v-dialog .v-overlay__content .v-card {
  border-radius: 20px !important;
  overflow: hidden;
}

/* Aplicar para todos os dialogs do sistema */
.v-dialog:not(.v-dialog--fullscreen) .v-overlay__content {
  border-radius: 20px !important;
  overflow: hidden;
}

.v-dialog:not(.v-dialog--fullscreen) .v-overlay__content .v-card {
  border-radius: 20px !important;
  overflow: hidden;
}

/* Forçar bordas arredondadas em todos os elementos do modal */
.v-dialog .v-overlay__content,
.v-dialog .v-overlay__content > * {
  border-radius: 20px !important;
  overflow: hidden;
}

/* Aplicar especificamente para o BaseDialog */
.base-dialog .v-overlay__content {
  border-radius: 20px !important;
  overflow: hidden;
}

.base-dialog .v-overlay__content .v-card {
  border-radius: 20px !important;
  overflow: hidden;
}
</style>
