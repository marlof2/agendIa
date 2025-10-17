<template>
  <BaseDialog
    :model-value="modelValue"
    title="Alterar Perfil"
    subtitle="Modifique o perfil do usuário nesta empresa"
    icon="mdi-account-edit"
    icon-color="primary"
    :fullscreen="$vuetify.display.mobile"
    width="500px"
    @close="closeModal"
  >
    <v-form ref="formRef" v-model="isValid" @submit.prevent="handleSubmit">
      <v-row>
        <!-- User Info - Compact -->
        <v-col cols="12">
          <div class="d-flex align-center pa-3 bg-grey-lighten-5 rounded-lg mb-4">
            <v-avatar color="primary" size="40" class="mr-3">
              <v-icon color="white" size="20">mdi-account</v-icon>
            </v-avatar>
            <div class="flex-grow-1">
              <div class="text-subtitle-1 font-weight-bold">{{ user?.name }}</div>
              <div class="text-body-2 text-medium-emphasis">{{ user?.email }}</div>
            </div>
            <div class="text-caption text-medium-emphasis">
              {{ company?.name }}
            </div>
          </div>
        </v-col>


        <!-- Current Profile - Compact -->
        <v-col cols="12">
          <div class="mb-3">
            <span class="text-body-2 text-medium-emphasis">Perfil atual:</span>
            <v-chip
              :color="getProfileColor(currentProfile?.name)"
              size="small"
              class="ml-2"
            >
              <v-icon start size="14">{{ getProfileIcon(currentProfile?.name) }}</v-icon>
              {{ currentProfile?.display_name || currentProfile?.name }}
            </v-chip>
          </div>
        </v-col>

        <!-- New Profile Selection -->
        <v-col cols="12">
          <v-select
            v-model="selectedProfileId"
            :items="availableProfileOptions"
            item-title="text"
            item-value="value"
            label="Novo perfil *"
            variant="outlined"
            density="comfortable"
            rounded="lg"
            prepend-inner-icon="mdi-shield-account-outline"
            :rules="profileRules"
            hint="Selecione o novo perfil para este usuário"
            persistent-hint
            required
          >
          </v-select>
        </v-col>

        <!-- Warning Message - Compact -->
        <v-col cols="12">
          <v-alert
            type="warning"
            variant="tonal"
            density="compact"
            class="mb-2"
          >
            <span class="text-body-2">
              <strong>Atenção:</strong> Isso pode alterar as permissões do usuário.
            </span>
          </v-alert>
        </v-col>
      </v-row>

      <!-- Actions -->
      <div slot="actions" class="modal-actions-container d-flex justify-end">
        <v-btn
          variant="outlined"
          @click="closeModal"
          :disabled="submitting"
        >
          Cancelar
        </v-btn>
        <v-btn
          color="primary"
          type="submit"
          :loading="submitting"
          :disabled="!isValid || !selectedProfileId || selectedProfileId === currentProfile?.id"
        >
          Alterar Perfil
        </v-btn>
      </div>
    </v-form>
  </BaseDialog>
</template>

<script lang="ts" setup>
import { ref, computed, watch, nextTick } from "vue";
import BaseDialog from "@/components/BaseDialog.vue";
import { useCompaniesApi } from "@/pages/companies/api";
import { useProfilesApi } from "@/pages/profiles/api";
import { useProfileUtils } from "@/composables/useProfileUtils";
import { showSuccessToast, showErrorToast } from "@/utils/swal";

interface Props {
  modelValue: boolean;
  user: any;
  company: any;
}

interface Emits {
  (e: "update:modelValue", value: boolean): void;
  (e: "reload"): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const { getProfileColor, getProfileIcon } = useProfileUtils();

// Composables
const {
  updateUserProfileInCompany,
} = useCompaniesApi();

const {
  getCombo: getProfilesCombo,
} = useProfilesApi();

// Form refs
const formRef = ref();
const isValid = ref(false);
const submitting = ref(false);

// State
const selectedProfileId = ref<number | null>(null);
const profiles = ref<any[]>([]);

// Computed
const currentProfile = computed(() => {
  if (!props.user?.companies || props.user.companies.length === 0) return null;

  const companyRelation = props.user.companies.find((c: any) => c.id === props.company?.id);
  return companyRelation?.pivot?.profile || null;
});

const availableProfileOptions = computed(() =>
  profiles.value
    .filter(profile => profile.id !== currentProfile.value?.id)
    .map(profile => ({
      text: profile.display_name || profile.name,
      value: profile.id,
      raw: profile
    }))
);

// Validation rules
const profileRules = [
  (v: any) => !!v || "Selecione um perfil",
  (v: any) => v !== currentProfile.value?.id || "Selecione um perfil diferente do atual"
];

// Methods
const loadProfiles = async () => {
  try {
    const result = await getProfilesCombo();
    profiles.value = result || [];
  } catch (error) {
    console.error("Erro ao carregar perfis:", error);
    showErrorToast("Erro ao carregar perfis", "Erro!");
  }
};

const handleSubmit = async () => {
  if (!selectedProfileId.value) {
    showErrorToast("Selecione um perfil", "Atenção!");
    return;
  }

  if (selectedProfileId.value === currentProfile.value?.id) {
    showErrorToast("Selecione um perfil diferente do atual", "Atenção!");
    return;
  }

  try {
    submitting.value = true;

    await updateUserProfileInCompany(
      props.company.id,
      props.user.id,
      selectedProfileId.value
    );

    const newProfile = profiles.value.find(p => p.id === selectedProfileId.value);
    showSuccessToast(
      `Perfil alterado para "${newProfile?.display_name || newProfile?.name}" com sucesso!`,
      "Sucesso!"
    );
    emit("reload");
    closeModal();

  } catch (error: any) {
    console.error("Erro ao alterar perfil:", error);
    showErrorToast(
      error.response?.data?.message || "Erro ao alterar perfil do usuário",
      "Erro!"
    );
  } finally {
    submitting.value = false;
  }
};

const closeModal = () => {
  emit("update:modelValue", false);
  resetForm();
};

const resetForm = () => {
  selectedProfileId.value = null;

  nextTick(() => {
    formRef.value?.resetValidation();
  });
};

// Watchers
watch(
  () => props.modelValue,
  (newValue) => {
    if (newValue) {
      loadProfiles();
      nextTick(() => {
        formRef.value?.resetValidation();
      });
    }
  }
);

watch(
  () => props.user,
  () => {
    if (props.modelValue && props.user) {
      loadProfiles();
    }
  },
  { deep: true }
);
</script>

<style scoped>
/* Responsive adjustments */
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

/* Desktop styles */
.modal-actions-container {
  gap: 16px;
}
</style>
