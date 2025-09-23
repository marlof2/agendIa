<template>
  <v-card class="data-table-card modern-card" elevation="0">
    <!-- Desktop Table -->
    <v-data-table
      v-if="!isMobile"
      :headers="headers"
      :items="items"
      :search="search"
      :loading="loading"
      :items-per-page="itemsPerPage"
      :no-data-text="noDataText"
      :loading-text="loadingText"
      class="elevation-0 modern-table"
      v-bind="$attrs"
    >
      <template v-for="(_, name) in $slots" v-slot:[name]="slotData">
        <slot :name="name" v-bind="slotData" />
      </template>
    </v-data-table>

    <!-- Mobile Cards -->
    <div v-else class="data-table-mobile">
      <div v-if="loading" class="data-table-mobile__loading">
        <v-skeleton-loader
          v-for="i in 3"
          :key="i"
          type="card"
          class="mb-4"
        />
      </div>

      <div v-else-if="items.length === 0" class="data-table-mobile__empty">
        <v-icon size="64" color="grey-lighten-1">mdi-database-off</v-icon>
        <p class="text-h6 mt-4">{{ noDataText }}</p>
      </div>

      <div v-else class="data-table-mobile__cards">
        <v-card
          v-for="item in items"
          :key="item.id || item"
          class="data-table-mobile__card mb-4"
          elevation="1"
        >
          <v-card-text class="pa-4">
            <slot name="mobile-card" :item="item" />
          </v-card-text>
        </v-card>
      </div>
    </div>
  </v-card>
</template>

<script lang="ts" setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

interface Header {
  title: string
  key: string
  sortable?: boolean
  width?: string
}

interface Props {
  headers: Header[]
  items: any[]
  search?: string
  loading?: boolean
  itemsPerPage?: number
  noDataText?: string
  loadingText?: string
}

const props = withDefaults(defineProps<Props>(), {
  search: '',
  loading: false,
  itemsPerPage: 10,
  noDataText: 'Nenhum item encontrado',
  loadingText: 'Carregando...'
})

const windowWidth = ref(0)
const isMobile = computed(() => windowWidth.value < 768)

const handleResize = () => {
  windowWidth.value = window.innerWidth
}

onMounted(() => {
  windowWidth.value = window.innerWidth
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})
</script>

<style scoped>
.data-table-card {
  border-radius: 16px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.08);
  background: rgba(var(--v-theme-surface), 0.8);
  backdrop-filter: blur(10px);
}

.modern-table {
  border-radius: 16px;
  overflow: hidden;
}

.modern-table :deep(.v-data-table__wrapper) {
  border-radius: 16px;
}

.modern-table :deep(.v-data-table__th) {
  background: rgba(var(--v-theme-primary), 0.04);
  font-weight: 600;
  color: rgba(var(--v-theme-on-surface), 0.87);
  border-bottom: 1px solid rgba(var(--v-theme-on-surface), 0.08);
}

.modern-table :deep(.v-data-table__td) {
  border-bottom: 1px solid rgba(var(--v-theme-on-surface), 0.04);
  padding: 16px;
}

.modern-table :deep(.v-data-table__tr:hover) {
  background: rgba(var(--v-theme-primary), 0.02);
}

/* Mobile Styles */
.data-table-mobile__loading {
  padding: 16px;
}

.data-table-mobile__empty {
  text-align: center;
  padding: 48px 16px;
  color: rgba(var(--v-theme-on-surface), 0.6);
}

.data-table-mobile__cards {
  padding: 16px;
}

.data-table-mobile__card {
  border-radius: 12px;
  transition: all 0.2s ease;
}

.data-table-mobile__card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Responsividade */
@media (max-width: 768px) {
  .modern-table :deep(.v-data-table__td) {
    padding: 12px 8px;
  }
}
</style>
