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
    :type="type"
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
  type?: string;
  isEditing?: boolean;
}

interface Emits {
  (e: 'click'): void;
}

const props = withDefaults(defineProps<Props>(), {
  text: 'Salvar',
  color: 'primary',
  variant: 'flat',
  size: 'default',
  prependIcon: 'mdi-plus',
  appendIcon: undefined,
  loading: false,
  disabled: false,
  rounded: 'lg',
  buttonClass: 'text-none font-weight-medium',
  type: 'submit',
  isEditing: false,
});

defineEmits<Emits>();

// Computed para texto e ícone baseado no estado de edição
const computedText = computed(() => {
  if (props.text !== 'Salvar') return props.text;
  return props.isEditing ? 'Atualizar' : 'Salvar';
});

const computedIcon = computed(() => {
  if (props.prependIcon !== 'mdi-plus') return props.prependIcon;
  return props.isEditing ? 'mdi-pencil' : 'mdi-plus';
});

const computedColor = computed(() => {
  if (props.color !== 'primary') return props.color;
  return props.isEditing ? 'info' : 'primary';
});
</script>
