<template>
  <v-card
    v-show="show"
    class="filters-card modern-card"
    elevation="0"
    :class="{ 'filters-card--hidden': !show }"
  >
    <v-card-text class="pa-6">
      <!-- Filters Content -->
      <div class="filters-card__content">
        <slot name="filtersCard" />
      </div>

      <!-- Filter Actions -->
      <div v-if="$slots.actionsFilters" class="filters-card__actions">
        <slot name="actionsFilters" />
      </div>
    </v-card-text>
  </v-card>
</template>

<script lang="ts" setup>
interface Props {
  show: boolean
}

defineProps<Props>()
</script>

<style scoped>
.filters-card {
  border-radius: 16px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.08);
  background: rgba(var(--v-theme-surface), 0.8);
  backdrop-filter: blur(10px);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden;
}

.filters-card--hidden {
  opacity: 0;
  transform: translateY(-10px);
  max-height: 0;
  margin-bottom: 0;
  padding: 0;
}

.filters-card__content {
  margin-bottom: 24px;
}

.filters-card__actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding-top: 16px;
  border-top: 1px solid rgba(var(--v-theme-on-surface), 0.08);
}

/* Melhorias nos campos de filtro */
.filters-card :deep(.v-field) {
  border-radius: 12px;
}

.filters-card :deep(.v-field__outline) {
  border-radius: 12px;
}

.filters-card :deep(.v-field--focused .v-field__outline) {
  border-width: 2px;
}

/* Responsividade */
@media (max-width: 768px) {
  .filters-card .pa-6 {
    padding: 16px !important;
  }

  .filters-card__actions {
    flex-direction: column;
    gap: 8px;
  }

  .filters-card__actions :deep(.v-btn) {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .filters-card .pa-6 {
    padding: 12px !important;
  }
}
</style>
