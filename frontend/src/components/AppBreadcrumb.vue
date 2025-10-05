<template>
  <div class="breadcrumb">
    <div class="breadcrumb__items">
      <router-link to="/" class="breadcrumb__item breadcrumb__item--home">
        <v-icon size="16" class="breadcrumb__home-icon">mdi-home</v-icon>
        Home
      </router-link>
      <template v-for="(item, index) in items" :key="index">
        <v-icon size="14" class="breadcrumb__separator">mdi-chevron-right</v-icon>
        <router-link
          v-if="item.to && !item.disabled && index !== items.length - 1"
          :to="item.to"
          class="breadcrumb__item breadcrumb__item--link"
        >
          {{ item.title }}
        </router-link>
        <span
          v-else
          class="breadcrumb__item"
          :class="{ 'breadcrumb__item--current': index === items.length - 1 }"
        >
          {{ item.title }}
        </span>
      </template>
    </div>
  </div>
</template>

<script lang="ts" setup>
interface BreadcrumbItem {
  title: string
  to?: string
  disabled?: boolean
}

interface Props {
  items: BreadcrumbItem[]
}

defineProps<Props>()
</script>

<style scoped>
.breadcrumb {
  margin-bottom: 0;
}

.breadcrumb__items {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.875rem;
  flex-wrap: wrap;
}

.breadcrumb__item {
  color: #64748b;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 4px;
  transition: color 0.2s ease;
  text-decoration: none;
}

.breadcrumb__item--home {
  color: #1e293b;
  font-weight: 600;
}

.breadcrumb__item--home:hover {
  color: #334155;
}

.breadcrumb__item--current {
  color: #1e293b;
  font-weight: 600;
}

.breadcrumb__item--link {
  cursor: pointer;
  text-decoration: none;
}

.breadcrumb__item--link:hover {
  color: #334155;
  text-decoration: underline;
}

.breadcrumb__separator {
  color: #cbd5e1;
  margin: 0 2px;
  flex-shrink: 0;
}

.breadcrumb__home-icon {
  color: #1e293b;
}

/* Dark theme */
.v-theme--dark .breadcrumb__item {
  color: #94a3b8;
}

.v-theme--dark .breadcrumb__item--home {
  color: #f1f5f9;
}

.v-theme--dark .breadcrumb__item--home:hover {
  color: #cbd5e1;
}

.v-theme--dark .breadcrumb__item--current {
  color: #f1f5f9;
}

.v-theme--dark .breadcrumb__item--link:hover {
  color: #cbd5e1;
}

.v-theme--dark .breadcrumb__separator {
  color: #475569;
}

.v-theme--dark .breadcrumb__home-icon {
  color: #f1f5f9;
}

/* Responsive */
@media (max-width: 768px) {
  .breadcrumb {
    padding: 12px 0 6px 0;
    margin-bottom: 12px;
  }

  .breadcrumb__items {
    font-size: 0.8rem;
    gap: 4px;
  }

  .breadcrumb__separator {
    margin: 0 1px;
  }
}

@media (max-width: 480px) {
  .breadcrumb__items {
    font-size: 0.75rem;
  }
}
</style>
