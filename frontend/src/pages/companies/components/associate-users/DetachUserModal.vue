<template>
  <BaseDialog
    :model-value="modelValue"
    title="Desassociar Usuário"
    subtitle="Remover vínculo do usuário com a empresa"
    icon="mdi-link-off"
    icon-color="error"
    :fullscreen="$vuetify.display.mobile"
    width="500px"
    @close="closeModal"
  >
    <div class="pa-4">
      <!-- User Info -->
      <v-card variant="outlined" class="mb-4">
        <v-card-text class="pa-4">
          <div class="d-flex align-center">
            <v-avatar color="primary" size="48" class="mr-3">
              <v-icon color="white">mdi-account</v-icon>
            </v-avatar>
            <div>
              <h3 class="text-h6 font-weight-bold">{{ user?.name }}</h3>
              <p class="text-body-2 text-medium-emphasis">{{ user?.email }}</p>
            </div>
          </div>
        </v-card-text>
      </v-card>

      <!-- Company Info -->
      <v-card variant="outlined" class="mb-4">
        <v-card-text class="pa-4">
          <div class="d-flex align-center">
            <v-avatar color="secondary" size="48" class="mr-3">
              <v-icon color="white">mdi-domain</v-icon>
            </v-avatar>
            <div>
              <h3 class="text-h6 font-weight-bold">{{ company?.name }}</h3>
              <p class="text-body-2 text-medium-emphasis">{{ company?.email }}</p>
            </div>
          </div>
        </v-card-text>
      </v-card>

      <!-- Current Profile -->
      <div v-if="currentProfile" class="mb-4">
        <h4 class="text-subtitle-1 font-weight-bold mb-2">Perfil Atual:</h4>
        <v-chip
          :color="getProfileColor(currentProfile.name)"
          variant="tonal"
          size="large"
        >
          <v-icon start size="16">{{ getProfileIcon(currentProfile.name) }}</v-icon>
          {{ currentProfile.display_name || currentProfile.name }}
        </v-chip>
      </div>

      <!-- Warning Message -->
      <v-alert
        type="warning"
        variant="tonal"
        icon="mdi-alert"
        class="mb-4"
      >
        <div class="text-body-2">
          <strong>Atenção:</strong> Esta ação irá remover o usuário da empresa e revogar todas as permissões e acessos relacionados a esta empresa.
        </div>
      </v-alert>

      <!-- Confirmation Message -->
      <div class="text-center py-4">
        <p class="text-h6 font-weight-bold mb-2">
          Deseja realmente desassociar este usuário?
        </p>
        <p class="text-body-2 text-medium-emphasis">
          Esta ação não pode ser desfeita e o usuário perderá acesso a esta empresa.
        </p>
      </div>
    </div>

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
        color="error"
        @click="handleConfirm"
        :loading="submitting"
      >
        Desassociar Usuário
      </v-btn>
    </div>
  </BaseDialog>
</template>

<script lang="ts" setup>
import { computed } from "vue";
import BaseDialog from "@/components/BaseDialog.vue";
import { useProfileUtils } from "@/composables/useProfileUtils";

interface Props {
  modelValue: boolean;
  user: any;
  company: any;
  submitting?: boolean;
}

interface Emits {
  (e: "update:modelValue", value: boolean): void;
  (e: "confirm"): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const { getProfileColor, getProfileIcon } = useProfileUtils();

// Computed
const currentProfile = computed(() => {
  if (!props.user?.companies || props.user.companies.length === 0) return null;

  const companyRelation = props.user.companies.find((c: any) => c.id === props.company?.id);
  return companyRelation?.pivot?.profile || null;
});

const submitting = computed(() => props.submitting || false);

// Methods
const handleConfirm = () => {
  emit("confirm");
};

const closeModal = () => {
  emit("update:modelValue", false);
};
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
