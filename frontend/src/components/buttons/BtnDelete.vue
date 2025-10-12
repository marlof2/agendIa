<template>
  <v-btn
    v-if="iconOnly"
    :icon="displayIcon"
    :color="color"
    :variant="variant"
    :size="size"
    :loading="loading"
    :disabled="disabled"
    :rounded="rounded"
    :class="buttonClass"
    :title="text"
    @click="$emit('click')"
  />
  <v-btn
    v-else
    :color="color"
    :variant="variant"
    :size="size"
    :prepend-icon="prependIcon"
    :append-icon="appendIcon"
    :loading="loading"
    :disabled="disabled"
    :rounded="rounded"
    :class="buttonClass"
    @click="$emit('click')"
  >
    {{ displayText }}
  </v-btn>
</template>

<script lang="ts" setup>
interface Props {
  text?: string;
  color?: string;
  variant?: 'flat' | 'text' | 'outlined' | 'plain' | 'elevated' | 'tonal';
  size?: 'x-small' | 'small' | 'default' | 'large' | 'x-large';
  prependIcon?: string;
  appendIcon?: string;
  loading?: boolean;
  disabled?: boolean;
  rounded?: string;
  buttonClass?: string;
  iconOnly?: boolean;
}

interface Emits {
  (e: 'click'): void;
}

const props = withDefaults(defineProps<Props>(), {
  text: 'Excluir',
  color: 'error',
  variant: 'outlined',
  size: 'small',
  prependIcon: 'mdi-delete-outline',
  appendIcon: undefined,
  loading: false,
  disabled: false,
  rounded: 'lg',
  buttonClass: 'action-button',
  iconOnly: false,
});

defineEmits<Emits>();

// Se for apenas ícone, não mostra texto
const displayText = computed(() => {
  return props.iconOnly ? undefined : props.text;
});

const displayIcon = computed(() => {
  return props.iconOnly ? 'mdi-delete-outline' : props.prependIcon;
});
</script>
