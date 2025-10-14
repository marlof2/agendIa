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
    @update:model-value="closeModal"
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
            maxlength="15"
          />
        </v-col>

        <v-col cols="12" md="6">
          <v-text-field
            v-model="formattedCpf"
            label="CPF *"
            variant="outlined"
            density="compact"
            rounded="lg"
            prepend-inner-icon="mdi-card-account-details"
            hint="Seu CPF"
            persistent-hint
            :rules="cpfRules"
            required
            maxlength="14"
            @input="handleCpfInput"
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
import { useMask } from '@/composables/useMask'
import { useValidation } from '@/composables/useValidation'
import { useClientsApi } from '../api'
import { showSuccessToast, showErrorToast } from '@/utils/swal'
import type { Client, CreateClientData } from '../api'

interface Props {
  modelValue: boolean
  client?: Client | null
}

interface Emits {
  (e: 'update:modelValue', value: boolean): void
  (e: 'reload'): void
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: false,
  client: null
})

const emit = defineEmits<Emits>()

// Composables
const { formatCPF, maskCPF } = useMask()
const { getCPFValidationRules } = useValidation()
const { createItem, updateItem } = useClientsApi()

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
  cpf: '',
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

const formattedCpf = computed({
  get: () => {
    if (!form.value.cpf) return ''
    return formatCPF(form.value.cpf)
  },
  set: (value: string) => {
    form.value.cpf = value.replace(/\D/g, '')
  }
})

const formProgress = computed(() => {
  let progress = 0
  let totalFields = isEditing.value ? 4 : 5

  if (form.value.name) progress++
  if (form.value.email) progress++
  if (form.value.cpf) progress++
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

const cpfRules = [
  (v: string) => !!v || 'CPF é obrigatório',
  ...getCPFValidationRules()
]

// Methods
const resetForm = () => {
  form.value = {
    name: '',
    email: '',
    phone: '',
    cpf: '',
    password: ''
  }
  formRef.value?.resetValidation()
}


// Watchers
watch(
  () => props.modelValue,
  (newValue) => {
    if (newValue) {
      // Quando o modal abre, carrega os dados do cliente
      if (props.client && props.client.id) {
        form.value = {
          name: props.client.name || '',
          email: props.client.email || '',
          phone: props.client.phone || '',
          cpf: props.client.cpf || '',
          password: ''
        }
      } else {
        resetForm()
      }
    }
  }
)

const handleCpfInput = (event: Event) => {
  const maskedValue = maskCPF(event)
  form.value.cpf = maskedValue
}


const closeModal = () => {
  emit('update:modelValue', false)
  // Não resetar aqui, será resetado quando o modal abrir novamente
}

const handleSubmit = async () => {
  if (!isValid.value) return

  loading.value = true

  try {
    const clientData = {
      name: form.value.name,
      email: form.value.email,
      password: form.value.password || '',
      phone: form.value.phone || undefined,
      cpf: form.value.cpf || undefined,
    }

    let result
    if (isEditing.value) {
      result = await updateItem(props.client!.id, clientData)
      showSuccessToast('Cliente atualizado com sucesso!', 'Sucesso!')
    } else {
      result = await createItem(clientData)
      showSuccessToast('Cliente criado com sucesso!', 'Sucesso!')
    }

    emit('reload')
    closeModal()
  } catch (err: any) {
    const errorMessage = err?.response?.data?.message || err?.message || 'Erro ao salvar cliente'
    showErrorToast(errorMessage, 'Erro!')
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

