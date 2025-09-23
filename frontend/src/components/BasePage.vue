<template>
  <div class="base-page">
    <!-- Page Header -->
    <PageHeader
      :title="title"
      :subtitle="subtitle"
      :breadcrumbs="breadcrumbs"
    >
      <template v-if="$slots.headerActions" #actionsFilters>
        <slot name="actionsFilters" />
      </template>
    </PageHeader>

    <!-- Action Bar -->
    <div v-if="$slots.actionBar" class="base-page__action-bar mb-4">
      <slot name="actionBar" />
    </div>

    <!-- Filters Card (opcional) -->
    <div v-if="showFilters && $slots.filters" class="base-page__filters mb-6">
      <slot name="filters" />
    </div>

    <!-- Main Content -->
    <div class="base-page__content">
      <slot name="content" />
    </div>
  </div>
</template>

<script lang="ts" setup>
import PageHeader from './PageHeader.vue'

interface BreadcrumbItem {
  title: string
  to?: string
  disabled?: boolean
}

interface Props {
  title: string
  subtitle?: string
  breadcrumbs?: BreadcrumbItem[]
  showFilters?: boolean
}

defineProps<Props>()
</script>

<style scoped>
.base-page {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 24px;
}


.base-page__action-bar {
  /* Espaçamento para barra de ações */
}

.base-page__filters {
  /* Espaçamento para filtros */
}

.base-page__content {
  /* Conteúdo principal */
}

/* Responsividade */
@media (max-width: 768px) {
  .base-page {
    padding: 0 16px;
  }
}

@media (max-width: 480px) {
  .base-page {
    padding: 0 12px;
  }
}
</style>
