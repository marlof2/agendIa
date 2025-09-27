<template>
  <div class="export-actions">
    <v-menu>
      <template #activator="{ props }">
        <v-btn
          v-bind="props"
          :color="color"
          :variant="variant"
          :size="size"
          :loading="loading"
          :disabled="disabled"
          :prepend-icon="prependIcon"
          :append-icon="appendIcon"
          :class="buttonClass"
          rounded="lg"
        >
          {{ buttonText }}
        </v-btn>
      </template>

      <v-list class="export-menu">
        <v-list-item
          @click="handleExport('pdf')"
          :disabled="loading"
          class="export-item"
        >
          <template #prepend>
            <v-icon color="error" class="mr-3">mdi-file-pdf-box</v-icon>
          </template>
          <v-list-item-title>PDF</v-list-item-title>
          <v-list-item-subtitle>.pdf</v-list-item-subtitle>
        </v-list-item>
        <v-list-item
          @click="handleExport('excel')"
          :disabled="loading"
          class="export-item"
        >
          <template #prepend>
            <v-icon color="success" class="mr-3">mdi-file-excel</v-icon>
          </template>
          <v-list-item-title>Excel</v-list-item-title>
          <v-list-item-subtitle>.xlsx</v-list-item-subtitle>
        </v-list-item>
      </v-list>
    </v-menu>
  </div>
</template>

<script lang="ts" setup>
import { ref } from "vue";

interface Props {
  buttonText?: string;
  color?: string;
  variant?: "flat" | "outlined" | "text" | "elevated" | "tonal" | "plain";
  size?: string;
  prependIcon?: string;
  appendIcon?: string;
  buttonClass?: string;
  disabled?: boolean;
  filename?: string;
  data?: any[];
  columns?: any[];
}

const props = withDefaults(defineProps<Props>(), {
  buttonText: "Exportar",
  color: "primary",
  variant: "outlined",
  size: "default",
  prependIcon: "mdi-download",
  appendIcon: "mdi-chevron-down",
  buttonClass: "",
  disabled: false,
  filename: "export",
  data: () => [],
  columns: () => [],
});

const emit = defineEmits<{
  export: [format: "excel" | "pdf", data: any[], filename: string];
}>();

const loading = ref(false);

const handleExport = async (format: "excel" | "pdf") => {
  loading.value = true;

  try {
    // Emitir evento para o componente pai processar
    emit("export", format, props.data, props.filename);

    // Simular delay de processamento
    await new Promise((resolve) => setTimeout(resolve, 1000));
  } catch (error) {
    console.error("Erro ao exportar:", error);
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.export-actions {
  display: inline-block;
}

.export-menu {
  min-width: 200px;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.export-item {
  border-radius: 8px;
  margin: 4px 8px;
  transition: all 0.2s ease;
}

.export-item:hover {
  background: rgba(var(--v-theme-primary), 0.08);
}

.export-item:active {
  background: rgba(var(--v-theme-primary), 0.12);
}

.export-item .v-list-item-title {
  font-weight: 500;
  font-size: 0.875rem;
}

.export-item .v-list-item-subtitle {
  font-size: 0.75rem;
  opacity: 0.7;
}

/* Dark theme adjustments */
.v-theme--dark .export-menu {
  background: rgba(var(--v-theme-surface), 0.95);
  backdrop-filter: blur(10px);
}

.v-theme--dark .export-item:hover {
  background: rgba(var(--v-theme-primary), 0.12);
}
</style>
