<template>
  <div class="action-bar">
    <div class="action-bar__container">
      <!-- Left Actions -->
      <div class="action-bar__left">
        <slot name="left" />
      </div>

      <!-- Right Actions -->
      <div class="action-bar__right">
        <slot name="right" />
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
// Componente para padronizar barras de ação
</script>

<style scoped>
.action-bar {
  margin-bottom: 24px;
}

.action-bar__container {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  min-height: 48px;
}

.action-bar__left {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
}

.action-bar__right {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-shrink: 0;
}

/* Botões usam altura padrão do Vuetify no desktop */

/* Responsividade */
@media (max-width: 768px) {
  .action-bar__container {
    flex-direction: column;
    align-items: stretch;
    gap: 16px;
  }

  .action-bar__left,
  .action-bar__right {
    justify-content: center;
    flex-wrap: wrap;
  }

  .action-bar__left :deep(.v-btn),
  .action-bar__right :deep(.v-btn) {
    flex: 1;
    min-width: 120px;
    min-height: 36px;
    height: 36px;
  }
}

@media (max-width: 480px) {
  .action-bar__left,
  .action-bar__right {
    flex-direction: column;
    gap: 8px;
  }

  .action-bar__left :deep(.v-btn),
  .action-bar__right :deep(.v-btn) {
    width: 100%;
    min-height: 36px;
    height: 36px;
  }
}
</style>
