<template>
  <v-btn
    v-if="iconOnly"
    :icon="icon"
    :color="color"
    :variant="variant"
    :size="size"
    :loading="loading"
    :disabled="disabled"
    :rounded="rounded"
    :class="btnClass"
    :title="title || text"
    @click="$emit('click')"
  />
  <v-btn
    v-else
    :color="color"
    :variant="variant"
    :size="size"
    :prepend-icon="icon"
    :append-icon="appendIcon"
    :loading="loading"
    :disabled="disabled"
    :rounded="rounded"
    :class="btnClass"
    :title="title"
    @click="$emit('click')"
  >
    {{ text }}
  </v-btn>
</template>

<script lang="ts" setup>
interface Props {
  text?: string
  icon?: string
  iconOnly?: boolean
  title?: string
  color?: string
  variant?: 'flat' | 'text' | 'outlined' | 'plain' | 'elevated' | 'tonal'
  size?: 'x-small' | 'small' | 'default' | 'large' | 'x-large'
  rounded?: string | boolean
  appendIcon?: string
  loading?: boolean
  disabled?: boolean
  btnClass?: string
}

interface Emits {
  (e: 'click'): void
}

withDefaults(defineProps<Props>(), {
  text: '',
  icon: undefined,
  iconOnly: false,
  title: undefined,
  color: 'primary',
  variant: 'flat',
  size: 'small',
  rounded: 'lg',
  appendIcon: undefined,
  loading: false,
  disabled: false,
  btnClass: 'text-none',
})

defineEmits<Emits>()
</script>
