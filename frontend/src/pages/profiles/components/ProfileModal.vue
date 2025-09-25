<template>
  <v-dialog
    :model-value="modelValue"
    @update:model-value="$emit('update:modelValue', $event)"
    max-width="600px"
    persistent
    scrollable
  >
    <v-card class="profile-modal">
      <v-card-title class="profile-modal__header">
        <div class="d-flex align-center">
          <v-icon
            :color="isEditing ? 'warning' : 'success'"
            size="24"
            class="mr-3"
          >
            {{ isEditing ? 'mdi-pencil' : 'mdi-plus' }}
          </v-icon>
          <span class="text-h5 font-weight-bold">
            {{ isEditing ? 'Editar Perfil' : 'Novo Perfil' }}
          </span>
        </div>
        <v-btn
          icon
          variant="text"
          @click="closeModal"
          class="profile-modal__close-btn"
        >
          <v-icon size="20">mdi-close</v-icon>
        </v-btn>
      </v-card-title>

      <v-card-text class="profile-modal__content">
        <v-form ref="formRef" v-model="isValid" @submit.prevent="handleSubmit">
          <v-row>
            <!-- Informações do Perfil -->
            <v-col cols="12">
              <h3 class="text-h6 mb-4 d-flex align-center">
                <v-icon size="20" class="mr-2">mdi-account-tie</v-icon>
                Informações do Perfil
              </h3>
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.name"
                label="Nome do perfil *"
                variant="outlined"
                density="comfortable"
                rounded="lg"
                :rules="nameRules"
                prepend-inner-icon="mdi-tag"
                required
                hint="Nome único para identificação do perfil"
                persistent-hint
              />
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.display_name"
                label="Nome de exibição *"
                variant="outlined"
                density="comfortable"
                rounded="lg"
                :rules="displayNameRules"
                prepend-inner-icon="mdi-account"
                required
                hint="Nome que será exibido para os usuários"
                persistent-hint
              />
            </v-col>

            <v-col cols="12">
              <v-textarea
                v-model="form.description"
                label="Descrição"
                variant="outlined"
                density="comfortable"
                rounded="lg"
                prepend-inner-icon="mdi-text"
                rows="3"
                auto-grow
                hint="Descrição opcional do perfil"
                persistent-hint
              />
            </v-col>

            <!-- Abilities Preview -->
            <v-col cols="12" v-if="isEditing && profile?.abilities?.length">
              <h3 class="text-h6 mb-4 d-flex align-center">
                <v-icon size="20" class="mr-2">mdi-shield-account</v-icon>
                Abilities Atuais
              </h3>
              <div class="abilities-preview">
                <v-chip
                  v-for="ability in profile.abilities.slice(0, 5)"
                  :key="ability.id"
                  color="primary"
                  size="small"
                  variant="outlined"
                  class="mr-2 mb-2"
                >
                  {{ ability.display_name }}
                </v-chip>
                <v-chip
                  v-if="profile.abilities.length > 5"
                  color="secondary"
                  size="small"
                  variant="flat"
                  class="mr-2 mb-2"
                >
                  +{{ profile.abilities.length - 5 }} mais
                </v-chip>
              </div>
              <v-btn
                color="primary"
                variant="outlined"
                size="small"
                prepend-icon="mdi-cog"
                class="mt-2"
                @click="openAbilitiesModal"
              >
                Gerenciar Abilities
              </v-btn>
            </v-col>
          </v-row>
        </v-form>
      </v-card-text>

      <v-card-actions class="profile-modal__actions">
        <v-spacer />
        <v-btn
          color="secondary"
          variant="outlined"
          rounded="lg"
          class="text-none font-weight-medium"
          @click="closeModal"
        >
          Cancelar
        </v-btn>
        <v-btn
          color="primary"
          variant="flat"
          rounded="lg"
          class="text-none font-weight-medium"
          :loading="loading"
          :disabled="!isValid"
          @click="handleSubmit"
        >
          {{ isEditing ? 'Atualizar' : 'Criar' }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>

  <!-- Abilities Modal -->
  <ProfileAbilitiesModal
    v-model="showAbilitiesModal"
    :profile="profile"
    @success="handleAbilitiesSuccess"
  />
</template>

<script lang="ts" setup>
import { ref, computed, watch, nextTick } from 'vue'
import ProfileAbilitiesModal from './ProfileAbilitiesModal.vue'

interface Props {
  modelValue: boolean
  profile?: any
}

interface Emits {
  (e: 'update:modelValue', value: boolean): void
  (e: 'success', profile: any): void
}

const props = withDefaults(defineProps<Props>(), {
  profile: null
})

const emit = defineEmits<Emits>()

// Reactive data
const formRef = ref()
const isValid = ref(false)
const loading = ref(false)
const showAbilitiesModal = ref(false)

// Form data
const form = ref({
  name: '',
  display_name: '',
  description: '',
  abilities: [] as number[]
})

// Computed
const isEditing = computed(() => !!props.profile?.id)

// Validation rules
const nameRules = [
  (v: string) => !!v || 'Nome do perfil é obrigatório',
  (v: string) => (v && v.length >= 2) || 'Nome deve ter pelo menos 2 caracteres',
  (v: string) => /^[a-z_]+$/.test(v) || 'Nome deve conter apenas letras minúsculas e underscore'
]

const displayNameRules = [
  (v: string) => !!v || 'Nome de exibição é obrigatório',
  (v: string) => (v && v.length >= 2) || 'Nome deve ter pelo menos 2 caracteres'
]

// Methods
const resetForm = () => {
  form.value = {
    name: '',
    display_name: '',
    description: '',
    abilities: []
  }
}

const loadProfileData = () => {
  if (props.profile) {
    form.value = {
      name: props.profile.name || '',
      display_name: props.profile.display_name || '',
      description: props.profile.description || '',
      abilities: props.profile.abilities?.map((a: any) => a.id) || []
    }
  } else {
    resetForm()
  }
}

const closeModal = () => {
  emit('update:modelValue', false)
  resetForm()
}

const openAbilitiesModal = () => {
  showAbilitiesModal.value = true
}

const handleAbilitiesSuccess = (updatedProfile: any) => {
  // Update the profile with new abilities
  Object.assign(props.profile, updatedProfile)
  showAbilitiesModal.value = false
}

const handleSubmit = async () => {
  if (!isValid.value) return

  loading.value = true

  try {
    const profileData = {
      name: form.value.name,
      display_name: form.value.display_name,
      description: form.value.description,
      abilities: form.value.abilities
    }

    emit('success', profileData)
    closeModal()
  } catch (error) {
    console.error('Erro ao salvar perfil:', error)
  } finally {
    loading.value = false
  }
}

// Watchers
watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    loadProfileData()
    nextTick(() => {
      formRef.value?.resetValidation()
    })
  }
})

watch(() => props.profile, () => {
  if (props.modelValue) {
    loadProfileData()
  }
}, { deep: true })
</script>

<style scoped>
.profile-modal {
  border-radius: 16px;
  overflow: hidden;
}

.profile-modal__header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 24px;
  position: relative;
}

.profile-modal__close-btn {
  position: absolute;
  top: 16px;
  right: 16px;
  color: white !important;
}

.profile-modal__content {
  padding: 24px;
  max-height: 70vh;
  overflow-y: auto;
}

.profile-modal__actions {
  padding: 16px 24px;
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
}

.abilities-preview {
  background: #f8fafc;
  border-radius: 12px;
  padding: 16px;
  border: 1px solid #e2e8f0;
}

/* Custom scrollbar */
.profile-modal__content::-webkit-scrollbar {
  width: 6px;
}

.profile-modal__content::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.profile-modal__content::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.profile-modal__content::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .profile-modal__header {
    padding: 20px;
  }

  .profile-modal__content {
    padding: 20px;
  }

  .profile-modal__actions {
    padding: 16px 20px;
    flex-direction: column-reverse;
    gap: 12px;
  }

  .profile-modal__actions .v-btn {
    width: 100%;
  }
}
</style>
