<template>
  <v-btn
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
    {{ text }}
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
}

interface Emits {
  (e: 'click'): void;
}

withDefaults(defineProps<Props>(), {
  text: 'Novo',
  color: 'success',
  variant: 'flat',
  size: 'default',
  prependIcon: 'mdi-plus',
  appendIcon: undefined,
  loading: false,
  disabled: false,
  rounded: 'lg',
  buttonClass: 'text-none font-weight-medium',
});

defineEmits<Emits>();
</script>
