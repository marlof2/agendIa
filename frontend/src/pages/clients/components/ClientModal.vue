<template>
  <BaseDialog
    :model-value="modelValue"
    :title="isEditing ? 'Editar Cliente' : 'Novo Cliente'"
    :subtitle="
      isEditing
        ? 'Atualize as informações do cliente'
        : 'Preencha os dados para criar um novo cliente'
    "
    :icon="isEditing ? 'mdi-pencil' : 'mdi-plus-circle'"
    :icon-color="isEditing ? 'info' : 'primary'"
    :fullscreen="$vuetify.display.mobile"
    :show-progress="true"
    :progress="formProgress"
    @close="closeModal"
  >
    <v-form ref="formRef" v-model="isValid" @submit.prevent="handleSubmit">
      <v-row>
        <!-- Informações do Cliente -->
        <v-col cols="12">
          <h3 class="text-h6 mb-4 d-flex align-center">
            <v-icon size="20" class="mr-2">mdi-account</v-icon>
            Informações do Cliente
          </h3>
        </v-col>

        <v-col cols="12" md="6">
          <v-text-field
            v-model="form.name"
            label="Nome completo *"
            variant="outlined"
            density="compact"
            rounded="lg"
            :rules="nameRules"
            prepend-inner-icon="mdi-account"
            required
            hint="Nome completo do cliente"
            persistent-hint
          />
        </v-col>

        <v-col cols="12" md="6">
          <v-text-field
            v-model="form.email"
            label="E-mail *"
            variant="outlined"
            density="compact"
            rounded="lg"
            :rules="emailRules"
            prepend-inner-icon="mdi-email"
            required
            hint="E-mail do cliente"
            persistent-hint
          />
        </v-col>

        <v-col cols="12" md="6">
          <v-text-field
            v-model="formattedPhone"
            label="Telefone"
            variant="outlined"
            density="compact"
            rounded="lg"
            prepend-inner-icon="mdi-phone"
            hint="Telefone de contato"
            persistent-hint
          />
        </v-col>

        <v-col v-if="!isEditing" cols="12" md="6">
          <v-text-field
            v-model="form.password"
            label="Senha *"
            :type="showPassword ? 'text' : 'password'"
            variant="outlined"
            density="compact"
            rounded="lg"
            :rules="passwordRules"
            prepend-inner-icon="mdi-lock"
            required
            hint="Senha de acesso do cliente"
            persistent-hint
            :append-inner-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
            @click:append-inner="showPassword = !showPassword"
          />
        </v-col>
      </v-row>
    </v-form>

    <template #actions>
      <v-spacer />
      <div class="d-flex modal-actions-container">
        <BtnCancel @click="closeModal" />
        <BtnSave
          :loading="loading"
          :disabled="!isValid"
          :is-editing="isEditing"
          @click="handleSubmit"
        />
      </div>
    </template>
  </BaseDialog>
</template>

<script lang="ts" setup>
import { ref, watch, computed } from 'vue'
import BaseDialog from '@/components/BaseDialog.vue'
import { BtnCancel, BtnSave } from '@/components/buttons'
import type { Client, CreateClientData } from '../api'

interface Props {
  modelValue: boolean
  client?: Client | null
}

interface Emits {
  (e: 'update:modelValue', value: boolean): void
  (e: 'submit', data: CreateClientData): void
  (e: 'close'): void
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: false,
  client: null
})

const emit = defineEmits<Emits>()

// Form state
const formRef = ref()
const isValid = ref(false)
const loading = ref(false)
const showPassword = ref(false)

// Computed
const isEditing = computed(() => !!props.client?.id)

// Form data
const form = ref<CreateClientData>({
  name: '',
  email: '',
  phone: '',
  password: ''
})

const formattedPhone = computed({
  get: () => {
    if (!form.value.phone) return ''
    const cleaned = form.value.phone.replace(/\D/g, '')
    if (cleaned.length === 11) {
      return `(${cleaned.slice(0, 2)}) ${cleaned.slice(2, 7)}-${cleaned.slice(7)}`
    } else if (cleaned.length === 10) {
      return `(${cleaned.slice(0, 2)}) ${cleaned.slice(2, 6)}-${cleaned.slice(6)}`
    }
    return form.value.phone
  },
  set: (value: string) => {
    form.value.phone = value.replace(/\D/g, '')
  }
})

const formProgress = computed(() => {
  let progress = 0
  let totalFields = isEditing.value ? 3 : 4

  if (form.value.name) progress++
  if (form.value.email) progress++
  if (form.value.phone) progress++
  if (!isEditing.value && form.value.password) progress++

  return (progress / totalFields) * 100
})

// Validation rules
const nameRules = [
  (v: string) => !!v || 'Nome é obrigatório',
  (v: string) => v.length >= 3 || 'Nome deve ter pelo menos 3 caracteres'
]

const emailRules = [
  (v: string) => !!v || 'E-mail é obrigatório',
  (v: string) => /.+@.+\..+/.test(v) || 'E-mail deve ser válido'
]

const passwordRules = [
  (v: string) => !!v || 'Senha é obrigatória',
  (v: string) => v.length >= 6 || 'Senha deve ter pelo menos 6 caracteres'
]

// Methods
const resetForm = () => {
  form.value = {
    name: '',
    email: '',
    phone: '',
    password: ''
  }
  formRef.value?.resetValidation()
}

const closeModal = () => {
  emit('update:modelValue', false)
  emit('close')
  resetForm()
}

// Watchers
watch(
  () => props.client,
  (newClient) => {
    if (newClient && newClient.id) {
      form.value = {
        name: newClient.name || '',
        email: newClient.email || '',
        phone: newClient.phone || '',
        password: ''
      }
    } else {
      resetForm()
    }
  },
  { immediate: true }
)

watch(
  () => props.modelValue,
  (newValue) => {
    if (!newValue) {
      resetForm()
    }
  }
)

const handleSubmit = async () => {
  const { valid } = await formRef.value.validate()

  if (!valid) {
    return
  }

  loading.value = true

  try {
    emit('submit', form.value)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.modal-actions-container {
  gap: 12px;
}

/* Responsive */
@media (max-width: 768px) {
  .modal-actions-container {
    flex-direction: column;
    gap: 8px;
    width: 100%;
  }

  .modal-actions-container :deep(.v-btn) {
    width: 100%;
  }
}
</style>

