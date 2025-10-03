<template>
  <div class="modal-buttons">
    <v-spacer />
    <div class="modal-actions-container">
      <v-btn
        v-if="showCancel"
        color="grey-darken-1"
        variant="flat"
        rounded="lg"
        class="text-none font-weight-medium mr-4"
        :disabled="loading"
        @click="$emit('cancel')"
      >
        <v-icon icon="mdi-close" class="mr-2" />
        {{ cancelText }}
      </v-btn>
      <v-btn
        :color="primaryColor"
        variant="flat"
        rounded="lg"
        class="text-none font-weight-medium"
        :loading="loading"
        :disabled="disabled"
        :type="submitType"
        @click="$emit('submit')"
      >
        <v-icon :icon="primaryIcon" class="mr-2" />
        {{ primaryText }}
      </v-btn>
    </div>
  </div>
</template>

<script lang="ts" setup>
interface Props {
  loading?: boolean;
  disabled?: boolean;
  showCancel?: boolean;
  cancelText?: string;
  primaryText?: string;
  primaryIcon?: string;
  primaryColor?: string;
  submitType?: 'button' | 'submit';
  isEditing?: boolean;
}

interface Emits {
  (e: 'cancel'): void;
  (e: 'submit'): void;
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  disabled: false,
  showCancel: true,
  cancelText: 'Fechar',
  primaryText: 'Salvar',
  primaryIcon: 'mdi-plus',
  primaryColor: 'primary',
  submitType: 'submit',
  isEditing: false,
});

const emit = defineEmits<Emits>();

// Computed properties para textos e ícones baseados no estado de edição
const computedPrimaryText = computed(() => {
  if (props.primaryText !== 'Salvar') return props.primaryText;
  return props.isEditing ? 'Atualizar' : 'Salvar';
});

const computedPrimaryIcon = computed(() => {
  if (props.primaryIcon !== 'mdi-plus') return props.primaryIcon;
  return props.isEditing ? 'mdi-pencil' : 'mdi-plus';
});

const computedPrimaryColor = computed(() => {
  if (props.primaryColor !== 'primary') return props.primaryColor;
  return props.isEditing ? 'info' : 'primary';
});
</script>

<style scoped>
.modal-buttons {
  display: flex;
  align-items: center;
  width: 100%;
}

.modal-actions-container {
  display: flex;
  align-items: center;
  gap: 8px;
}

/* Responsividade */
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
</style>
