<template>
  <div class="page-header">
    <!-- Breadcrumb -->
    <div v-if="breadcrumbs && breadcrumbs.length > 0" class="page-header__breadcrumb">
      <AppBreadcrumb :items="breadcrumbs" />
    </div>

    <!-- Header Content -->
    <div class="page-header__content">
      <div class="page-header__left">
        <h1 class="page-header__title">{{ title }}</h1>
        <p v-if="subtitle" class="page-header__subtitle">{{ subtitle }}</p>
      </div>

      <div v-if="$slots.actionsFilters" class="page-header__actions">
        <slot name="actionsFilters" />
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import AppBreadcrumb from './AppBreadcrumb.vue'

interface BreadcrumbItem {
  title: string
  to?: string
  disabled?: boolean
}

interface Props {
  title: string
  subtitle?: string
  breadcrumbs?: BreadcrumbItem[]
}

defineProps<Props>()
</script>

<style scoped>
.page-header {
  margin-bottom: 32px;
}

.page-header__breadcrumb {
  margin-bottom: 8px;
}

.page-header__content {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 24px;
}

.page-header__left {
  flex: 1;
  min-width: 0;
}

.page-header__title {
  font-size: 2rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 8px 0;
  line-height: 1.2;
}

.page-header__subtitle {
  font-size: 1.125rem;
  color: #64748b;
  margin: 0;
  line-height: 1.5;
}

.page-header__actions {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-shrink: 0;
}

/* Dark theme */
.v-theme--dark .page-header__title {
  color: #f1f5f9;
}

.v-theme--dark .page-header__subtitle {
  color: #94a3b8;
}

/* Responsive */
@media (max-width: 768px) {
  .page-header__breadcrumb {
    padding: 0 16px;
  }

  .page-header__content {
    flex-direction: column;
    align-items: stretch;
    gap: 16px;
    padding: 0 16px;
  }

  .page-header__title {
    font-size: 1.75rem;
  }

  .page-header__subtitle {
    font-size: 1rem;
  }

  .page-header__actions {
    justify-content: flex-start;
    flex-wrap: wrap;
  }
}

@media (max-width: 480px) {
  .page-header__title {
    font-size: 1.5rem;
  }

  .page-header__subtitle {
    font-size: 0.9rem;
  }
}
</style>
