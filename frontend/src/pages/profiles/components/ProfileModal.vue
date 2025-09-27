<template>
  <BaseDialog
    :model-value="modelValue"
    :title="isEditing ? 'Editar Perfil' : 'Novo Perfil'"
    :subtitle="isEditing ? 'Atualize as informações do perfil' : 'Preencha os dados para criar um novo perfil'"
    :icon="isEditing ? 'mdi-pencil' : 'mdi-plus-circle'"
    :icon-color="isEditing ? 'info' : 'primary'"
    max-width="600px"
    :fullscreen="$vuetify.display.mobile"
    :show-progress="true"
    :progress="formProgress"
    @close="closeModal"
  >
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
                density="compact"
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
                density="compact"
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
                density="compact"
                rounded="lg"
                prepend-inner-icon="mdi-text"
                rows="3"
                auto-grow
                hint="Descrição opcional do perfil"
                persistent-hint
              />
            </v-col>
      </v-row>
    </v-form>

    <template #actions>
      <v-spacer />
      <div class="d-flex modal-actions-container">
        <v-btn
          color="grey-darken-1"
          variant="flat"
          rounded="lg"
          class="text-none font-weight-medium mr-4"
          @click="closeModal"
        >
          <v-icon icon="mdi-close" class="mr-2" />
          Fechar
        </v-btn>
        <v-btn
          :color="isEditing ? 'info' : 'primary'"
          variant="flat"
          rounded="lg"
          class="text-none font-weight-medium"
          :loading="loading"
          :disabled="!isValid"
          type="submit"
          @click="handleSubmit"
        >
          <v-icon :icon="isEditing ? 'mdi-pencil' : 'mdi-plus'" class="mr-2" />
          {{ isEditing ? "Atualizar" : "Salvar" }}
        </v-btn>
      </div>
    </template>
  </BaseDialog>
</template>

<script lang="ts" setup>
import { ref, computed, watch, nextTick } from "vue";
import { showSuccessToast, showErrorToast } from "@/utils/swal";
import { useProfilesApi } from "../api";
import BaseDialog from "@/components/BaseDialog.vue";

interface Props {
  modelValue: boolean;
  profile?: any;
}

interface Emits {
  (e: "update:modelValue", value: boolean): void;
  (e: "reload"): void;
}

const props = withDefaults(defineProps<Props>(), {
  profile: null,
});

const emit = defineEmits<Emits>();

// Composables
const { createItem, updateItem } = useProfilesApi();

// Reactive data
const formRef = ref();
const isValid = ref(false);
const loading = ref(false);

// Form data
const form = ref({
  name: "",
  display_name: "",
  description: "",
  abilities: [] as number[],
});

// Computed
const isEditing = computed(() => !!props.profile?.id);

const formProgress = computed(() => {
  const totalFields = 2; // name, display_name (campos obrigatórios)
  const filledFields = [
    form.value.name,
    form.value.display_name,
  ].filter((field) => field && field.trim() !== "").length;

  return (filledFields / totalFields) * 100;
});

// Validation rules
const nameRules = [
  (v: string) => !!v || "Nome do perfil é obrigatório",
  (v: string) =>
    (v && v.length >= 2) || "Nome deve ter pelo menos 2 caracteres",
  (v: string) =>
    /^[a-z_]+$/.test(v) ||
    "Nome deve conter apenas letras minúsculas e underscore",
];

const displayNameRules = [
  (v: string) => !!v || "Nome de exibição é obrigatório",
  (v: string) =>
    (v && v.length >= 2) || "Nome deve ter pelo menos 2 caracteres",
];

// Methods
const resetForm = () => {
  form.value = {
    name: "",
    display_name: "",
    description: "",
    abilities: [],
  };
};

const loadProfileData = () => {
  if (props.profile) {
    form.value = {
      name: props.profile.name || "",
      display_name: props.profile.display_name || "",
      description: props.profile.description || "",
      abilities: props.profile.abilities?.map((a: any) => a.id) || [],
    };
  } else {
    resetForm();
  }
};

const closeModal = () => {
  emit("update:modelValue", false);
  resetForm();
};

const handleSubmit = async () => {
  if (!isValid.value) return;

  loading.value = true;

  try {
    const profileData = {
      name: form.value.name,
      display_name: form.value.display_name,
      description: form.value.description,
      abilities: form.value.abilities,
    };

    let result;
    if (isEditing.value) {
      result = await updateItem(props.profile.id, profileData);
      showSuccessToast("Perfil atualizado com sucesso!", "Sucesso!");
    } else {
      result = await createItem(profileData);
      showSuccessToast("Perfil criado com sucesso!", "Sucesso!");
    }

    emit("reload");
    closeModal();
  } catch (error: any) {
    console.error("Erro ao salvar perfil:", error);

    const errorMessage = error?.response?.data?.message ||
                        error?.message ||
                        "Ocorreu um erro ao salvar o perfil.";

    showErrorToast(errorMessage, "Erro!");
  } finally {
    loading.value = false;
  }
};

// Watchers
watch(
  () => props.modelValue,
  (newValue) => {
    if (newValue) {
      loadProfileData();
      nextTick(() => {
        formRef.value?.resetValidation();
      });
    }
  }
);

watch(
  () => props.profile,
  () => {
    if (props.modelValue) {
      loadProfileData();
    }
  },
  { deep: true }
);
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
