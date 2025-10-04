<template>
  <BaseDialog
    :model-value="modelValue"
    :title="`Visualizar Usuário - ${user?.name || 'Nome não informado'}`"
    subtitle="Informações detalhadas do usuário"
    icon="mdi-eye"
    icon-color="primary"
    :fullscreen="$vuetify.display.mobile"
    @close="closeModal"
  >
    <div v-if="user" class="user-view">
      <!-- User Information -->
      <div class="user-info mb-6">
        <h3 class="section-title mb-4">
          <v-icon size="22" class="mr-3">mdi-information</v-icon>
          Informações Pessoais
        </h3>

        <div class="info-grid">
          <!-- Nome -->
          <div class="info-item">
            <div class="info-label">
              <v-icon size="20" color="primary" class="mr-3">mdi-account</v-icon>
              Nome Completo
            </div>
            <div class="info-value">
              <span class="value-text">{{ user?.name || "Não informado" }}</span>
            </div>
          </div>

          <!-- Email -->
          <div class="info-item">
            <div class="info-label">
              <v-icon size="20" color="primary" class="mr-3">mdi-email</v-icon>
              E-mail
            </div>
            <div class="info-value">
              <span class="value-text">{{ user?.email || "Não informado" }}</span>
            </div>
          </div>

          <!-- Telefone -->
          <div class="info-item" v-if="user?.phone">
            <div class="info-label">
              <v-icon size="20" color="primary" class="mr-3">mdi-phone</v-icon>
              Telefone
            </div>
            <div class="info-value">
              <span class="phone-number">{{ formatPhone(user.phone) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Profile Information -->
      <div class="profile-info mb-6">
        <h3 class="section-title mb-4">
          <v-icon size="22" class="mr-3">mdi-account-tie</v-icon>
          Perfil e Acesso
        </h3>

        <div class="info-grid">
          <!-- Perfil -->
          <div class="info-item">
            <div class="info-label">
              <v-icon size="20" color="primary" class="mr-3">mdi-shield-account</v-icon>
              Perfil de Acesso
            </div>
            <div class="info-value">
              <div v-if="user?.profile" class="profile-display">
                <v-chip
                  :color="getProfileColor(user.profile.name)"
                  variant="tonal"
                  size="small"
                >
                  <v-icon start size="14">{{ getProfileIcon(user.profile.name) }}</v-icon>
                  {{ user.profile.display_name || user.profile.name }}
                </v-chip>
              </div>
              <span v-else class="text-medium-emphasis">Não definido</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Companies Information -->
      <div class="companies-info mb-6" v-if="user?.companies?.length">
        <h3 class="section-title mb-4">
          <v-icon size="22" class="mr-3">mdi-domain</v-icon>
          Empresas Associadas
        </h3>

        <div class="companies-grid">
          <v-card
            v-for="company in user.companies"
            :key="company.id"
            variant="outlined"
            class="company-card"
          >
            <v-card-text class="pa-3">
              <div class="d-flex align-center">
                <v-icon color="primary" class="mr-3">mdi-domain</v-icon>
                <span class="font-weight-medium">{{ company.name }}</span>
              </div>
            </v-card-text>
          </v-card>
        </div>
      </div>

      <!-- User Metadata -->
      <div class="user-metadata">
        <h3 class="section-title mb-4">
          <v-icon size="22" class="mr-3">mdi-calendar</v-icon>
          Informações do Sistema
        </h3>

        <div class="metadata-grid">
          <!-- Created At -->
          <div class="metadata-item">
            <div class="metadata-label">
              <v-icon size="20" color="success" class="mr-3">mdi-calendar-plus</v-icon>
              Data de Criação
            </div>
            <div class="metadata-value">
              <span class="date-text">{{ formatDate(user?.created_at) }}</span>
            </div>
          </div>

          <!-- Updated At -->
          <div class="metadata-item">
            <div class="metadata-label">
              <v-icon size="20" color="warning" class="mr-3">mdi-calendar-edit</v-icon>
              Última Atualização
            </div>
            <div class="metadata-value">
              <span class="date-text">{{ formatDate(user?.updated_at) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-else class="loading-state">
      <v-progress-circular indeterminate color="primary" size="48" />
      <p class="text-body-1 mt-4">Carregando informações do usuário...</p>
    </div>

    <template #actions>
      <v-spacer />
      <div class="d-flex modal-actions-container">
        <BtnCancel @click="closeModal" />
        <BtnEdit @click="handleEdit" />
      </div>
    </template>
  </BaseDialog>

</template>

<script lang="ts" setup>
import { computed, ref } from 'vue'
import { useMask } from '@/composables/useMask'
import BaseDialog from '@/components/BaseDialog.vue'

interface Props {
  modelValue: boolean
  user?: any
}

interface Emits {
  (e: 'update:modelValue', value: boolean): void
  (e: 'edit', user: any): void
  (e: 'reload'): void
}

const props = withDefaults(defineProps<Props>(), {
  user: null
})

const emit = defineEmits<Emits>()

const { formatPhone } = useMask()

// Computed

// Methods

const getProfileColor = (profileName?: string) => {
  switch (profileName) {
    case 'admin': return 'error';
    case 'secretary': return 'warning';
    case 'client': return 'success';
    default: return 'grey';
  }
};

const getProfileIcon = (profileName?: string) => {
  switch (profileName) {
    case 'admin': return 'mdi-shield-crown';
    case 'secretary': return 'mdi-account-tie';
    case 'client': return 'mdi-account';
    default: return 'mdi-help';
  }
};

const formatDate = (dateString: string) => {
  if (!dateString) return 'Não informado';

  try {
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  } catch (error) {
    return dateString;
  }
};

const closeModal = () => {
  emit('update:modelValue', false);
};

const handleEdit = () => {
  emit('edit', props.user);
  closeModal();
};

</script>

<style scoped>
.user-view {
  padding: 12px;
}

.section-title {
  display: flex;
  align-items: center;
  font-weight: 600;
  color: rgba(var(--v-theme-on-surface), 0.87);
  font-size: 1.125rem;
  padding-bottom: 8px;
  border-bottom: 2px solid rgba(var(--v-theme-primary), 0.2);
}

.info-grid,
.metadata-grid {
  display: grid;
  gap: 20px;
}

.info-item,
.metadata-item {
  display: flex;
  flex-direction: column;
  gap: 12px;
  padding: 20px;
  background: rgba(var(--v-theme-surface-variant), 0.4);
  border-radius: 16px;
  border: 1px solid rgba(var(--v-theme-outline), 0.15);
  transition: all 0.2s ease;
  position: relative;
}

.info-item:hover,
.metadata-item:hover {
  background: rgba(var(--v-theme-surface-variant), 0.6);
  border-color: rgba(var(--v-theme-primary), 0.2);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(var(--v-theme-primary), 0.1);
}

.info-label,
.metadata-label {
  display: flex;
  align-items: center;
  font-weight: 600;
  color: rgba(var(--v-theme-on-surface), 0.87);
  font-size: 0.95rem;
}

.info-value,
.metadata-value {
  color: rgba(var(--v-theme-on-surface), 0.7);
  font-size: 0.95rem;
}

.value-text {
  font-weight: 500;
  color: rgba(var(--v-theme-on-surface), 0.87);
}

.phone-number {
  font-family: 'Roboto Mono', monospace;
  font-weight: 500;
  color: rgba(var(--v-theme-on-surface), 0.87);
  font-size: 1rem;
}

.date-text {
  font-family: 'Roboto Mono', monospace;
  font-weight: 500;
  color: rgba(var(--v-theme-on-surface), 0.87);
  font-size: 0.9rem;
}

.profile-display {
  display: flex;
  align-items: center;
}

.companies-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 12px;
}

.company-card {
  transition: all 0.2s ease;
}

.company-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 64px 24px;
  color: rgba(var(--v-theme-on-surface), 0.6);
}

.loading-state p {
  margin-top: 16px;
  font-size: 1rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .info-item,
  .metadata-item {
    padding: 16px;
  }

  .section-title {
    font-size: 1rem;
  }

  .modal-actions-container {
    flex-direction: column;
    gap: 12px;
    width: 100%;
  }

  .modal-actions-container .v-btn {
    width: 100%;
    margin-right: 0 !important;
  }

  .companies-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .user-view {
    padding: 8px;
  }

  .info-item,
  .metadata-item {
    padding: 12px;
  }
}

/* Desktop styles */
.modal-actions-container {
  gap: 16px;
}
</style>
