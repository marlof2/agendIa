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
    <div v-if="$slots.actionBar"   class="mb-4">
      <slot name="actionBar" />
    </div>

    <!-- Filters Card (opcional) -->
    <div v-if="showFilters && $slots.filters" class="mb-6">
      <slot name="filters" />
    </div>

    <!-- Main Content -->
    <div class="">
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

defineProps(
  {
    showFilters: {
      type: Boolean,
      required: false,
      default: true
    },
    title: {
      type: String,
      required: true
    },
    subtitle: {
      type: String,
      required: false
    },
    breadcrumbs: {
      type: Array as PropType<BreadcrumbItem[]>,
      required: false
    }
  }
)
</script>

<style scoped>
.base-page {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 24px;
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
