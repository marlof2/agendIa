<template>
  <v-dialog
    :model-value="modelValue"
    @update:model-value="$emit('update:modelValue', $event)"
    max-width="900px"
    persistent
    scrollable
  >
    <v-card class="profile-view-modal">
      <v-card-title class="profile-view-modal__header">
        <div class="d-flex align-center">
          <v-avatar size="40" color="primary" class="mr-4">
            <v-icon color="white" size="24">mdi-account-tie</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold mb-1">
              {{ profile?.display_name || 'Perfil não informado' }}
            </h2>
            <p class="text-body-2 text-medium-emphasis mb-0">
              {{ profile?.name || 'Nome não informado' }}
            </p>
          </div>
        </div>
        <v-btn
          icon
          variant="text"
          @click="closeModal"
          class="profile-view-modal__close-btn"
        >
          <v-icon size="20">mdi-close</v-icon>
        </v-btn>
      </v-card-title>

      <v-card-text class="profile-view-modal__content">
        <v-row>
          <!-- Informações Básicas -->
          <v-col cols="12" md="6">
            <v-card variant="outlined" class="info-card">
              <v-card-title class="info-card__title">
                <v-icon size="20" class="mr-2">mdi-account-tie</v-icon>
                Informações Básicas
              </v-card-title>
              <v-card-text>
                <div class="info-item">
                  <div class="info-label">Nome do perfil</div>
                  <div class="info-value">{{ profile?.name || 'Não informado' }}</div>
                </div>
                <div class="info-item">
                  <div class="info-label">Nome de exibição</div>
                  <div class="info-value">{{ profile?.display_name || 'Não informado' }}</div>
                </div>
                <div class="info-item">
                  <div class="info-label">Descrição</div>
                  <div class="info-value">{{ profile?.description || 'Sem descrição' }}</div>
                </div>
              </v-card-text>
            </v-card>
          </v-col>

          <!-- Datas -->
          <v-col cols="12" md="6">
            <v-card variant="outlined" class="info-card">
              <v-card-title class="info-card__title">
                <v-icon size="20" class="mr-2">mdi-calendar</v-icon>
                Datas
              </v-card-title>
              <v-card-text>
                <div class="info-item">
                  <div class="info-label">Criado em</div>
                  <div class="info-value">{{ formatDate(profile?.created_at) }}</div>
                </div>
                <div class="info-item">
                  <div class="info-label">Última atualização</div>
                  <div class="info-value">{{ formatDate(profile?.updated_at) }}</div>
                </div>
              </v-card-text>
            </v-card>
          </v-col>

          <!-- Estatísticas -->
          <v-col cols="12" md="6">
            <v-card variant="outlined" class="info-card">
              <v-card-title class="info-card__title">
                <v-icon size="20" class="mr-2">mdi-chart-line</v-icon>
                Estatísticas
              </v-card-title>
              <v-card-text>
                <div class="info-item">
                  <div class="info-label">Total de abilities</div>
                  <div class="info-value">{{ profile?.abilities?.length || 0 }}</div>
                </div>
                <div class="info-item">
                  <div class="info-label">Usuários com este perfil</div>
                  <div class="info-value">{{ profile?.users_count || 0 }}</div>
                </div>
              </v-card-text>
            </v-card>
          </v-col>

          <!-- Abilities por Categoria -->
          <v-col cols="12" md="6">
            <v-card variant="outlined" class="info-card">
              <v-card-title class="info-card__title">
                <v-icon size="20" class="mr-2">mdi-shield-account</v-icon>
                Abilities por Categoria
              </v-card-title>
              <v-card-text>
                <div v-if="abilitiesByCategory && Object.keys(abilitiesByCategory).length" class="category-stats">
                  <div
                    v-for="(abilities, category) in abilitiesByCategory"
                    :key="category"
                    class="category-item"
                  >
                    <div class="d-flex align-center justify-space-between">
                      <div class="d-flex align-center">
                        <v-icon
                          :color="getCategoryColor(category)"
                          size="16"
                          class="mr-2"
                        >
                          {{ getCategoryIcon(category) }}
                        </v-icon>
                        <span class="text-body-2">{{ getCategoryTitle(category) }}</span>
                      </div>
                      <v-chip
                        :color="getCategoryColor(category)"
                        size="x-small"
                        variant="outlined"
                      >
                        {{ abilities.length }}
                      </v-chip>
                    </div>
                  </div>
                </div>
                <div v-else class="text-body-2 text-medium-emphasis">
                  Nenhuma ability atribuída
                </div>
              </v-card-text>
            </v-card>
          </v-col>

          <!-- Abilities Detalhadas -->
          <v-col cols="12">
            <v-card variant="outlined" class="info-card">
              <v-card-title class="info-card__title">
                <v-icon size="20" class="mr-2">mdi-shield-account</v-icon>
                Abilities Detalhadas
              </v-card-title>
              <v-card-text>
                <div v-if="profile?.abilities?.length" class="abilities-list">
                  <v-expansion-panels variant="accordion">
                    <v-expansion-panel
                      v-for="(abilities, category) in abilitiesByCategory"
                      :key="category"
                    >
                      <template #title>
                        <div class="d-flex align-center">
                          <v-icon
                            :color="getCategoryColor(category)"
                            size="20"
                            class="mr-3"
                          >
                            {{ getCategoryIcon(category) }}
                          </v-icon>
                          <span class="font-weight-medium">{{ getCategoryTitle(category) }}</span>
                          <v-chip
                            :color="getCategoryColor(category)"
                            size="small"
                            variant="outlined"
                            class="ml-3"
                          >
                            {{ abilities.length }}
                          </v-chip>
                        </div>
                      </template>
                      <template #text>
                        <div class="abilities-grid">
                          <v-card
                            v-for="ability in abilities"
                            :key="ability.id"
                            variant="outlined"
                            class="ability-card"
                          >
                            <v-card-text class="pa-3">
                              <div class="font-weight-medium text-body-1 mb-1">
                                {{ ability.display_name }}
                              </div>
                              <div class="text-caption text-medium-emphasis mb-2">
                                {{ ability.name }}
                              </div>
                              <div v-if="ability.description" class="text-body-2">
                                {{ ability.description }}
                              </div>
                            </v-card-text>
                          </v-card>
                        </div>
                      </template>
                    </v-expansion-panel>
                  </v-expansion-panels>
                </div>
                <div v-else class="text-center py-8">
                  <v-icon color="grey" size="64" class="mb-4">mdi-shield-off</v-icon>
                  <p class="text-body-1 text-grey">Nenhuma ability atribuída a este perfil</p>
                </div>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-card-text>

      <v-card-actions class="profile-view-modal__actions">
        <v-spacer />
        <v-btn
          color="secondary"
          variant="outlined"
          rounded="lg"
          class="text-none font-weight-medium"
          @click="closeModal"
        >
          Fechar
        </v-btn>
        <v-btn
          v-if="hasPermission('profiles.edit')"
          color="primary"
          variant="flat"
          rounded="lg"
          class="text-none font-weight-medium"
          prepend-icon="mdi-cog"
          @click="openAbilitiesModal"
        >
          Gerenciar Abilities
        </v-btn>
        <v-btn
          v-if="hasPermission('profiles.edit')"
          color="warning"
          variant="flat"
          rounded="lg"
          class="text-none font-weight-medium"
          prepend-icon="mdi-pencil"
          @click="handleEdit"
        >
          Editar
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
import { computed, ref } from 'vue'
import { useAbilities } from '@/composables/useAbilities'
import ProfileAbilitiesModal from './ProfileAbilitiesModal.vue'

interface Props {
  modelValue: boolean
  profile?: any
}

interface Emits {
  (e: 'update:modelValue', value: boolean): void
  (e: 'edit', profile: any): void
}

const props = withDefaults(defineProps<Props>(), {
  profile: null
})

const emit = defineEmits<Emits>()

const { hasPermission } = useAbilities()
const showAbilitiesModal = ref(false)

// Computed
const abilitiesByCategory = computed(() => {
  if (!props.profile?.abilities) return {}

  const grouped: Record<string, any[]> = {}

  props.profile.abilities.forEach((ability: any) => {
    if (!grouped[ability.category]) {
      grouped[ability.category] = []
    }
    grouped[ability.category].push(ability)
  })

  return grouped
})

// Methods
const getCategoryColor = (category: string) => {
  const colors: Record<string, string> = {
    appointments: 'blue',
    clients: 'green',
    professionals: 'orange',
    services: 'purple',
    profiles: 'red',
    reports: 'teal',
    companies: 'indigo'
  }
  return colors[category] || 'grey'
}

const getCategoryIcon = (category: string) => {
  const icons: Record<string, string> = {
    appointments: 'mdi-calendar-clock',
    clients: 'mdi-account-group',
    professionals: 'mdi-account-tie',
    services: 'mdi-briefcase',
    profiles: 'mdi-account-group',
    reports: 'mdi-chart-line',
    companies: 'mdi-office-building'
  }
  return icons[category] || 'mdi-shield'
}

const getCategoryTitle = (category: string) => {
  const titles: Record<string, string> = {
    appointments: 'Agendamentos',
    clients: 'Clientes',
    professionals: 'Profissionais',
    services: 'Serviços',
    profiles: 'Perfis',
    reports: 'Relatórios',
    companies: 'Empresas'
  }
  return titles[category] || category.charAt(0).toUpperCase() + category.slice(1)
}

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
  emit('edit', props.profile);
  closeModal();
};

const openAbilitiesModal = () => {
  showAbilitiesModal.value = true;
};

const handleAbilitiesSuccess = (updatedProfile: any) => {
  // Update the profile with new abilities
  Object.assign(props.profile, updatedProfile);
  showAbilitiesModal.value = false;
};
</script>

<style scoped>
.profile-view-modal {
  border-radius: 16px;
  overflow: hidden;
}

.profile-view-modal__header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 24px;
  position: relative;
}

.profile-view-modal__close-btn {
  position: absolute;
  top: 16px;
  right: 16px;
  color: white !important;
}

.profile-view-modal__content {
  padding: 24px;
  max-height: 70vh;
  overflow-y: auto;
}

.profile-view-modal__actions {
  padding: 16px 24px;
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
}

.info-card {
  border-radius: 12px;
  height: 100%;
}

.info-card__title {
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
  font-size: 0.95rem;
  font-weight: 600;
  color: #374151;
  padding: 16px;
}

.info-card__title .v-icon {
  color: #667eea;
}

.info-item {
  margin-bottom: 16px;
}

.info-item:last-child {
  margin-bottom: 0;
}

.info-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #6b7280;
  margin-bottom: 4px;
}

.info-value {
  font-size: 0.95rem;
  font-weight: 500;
  color: #111827;
}

.category-stats {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.category-item {
  padding: 8px 0;
  border-bottom: 1px solid #f3f4f6;
}

.category-item:last-child {
  border-bottom: none;
}

.abilities-list {
  margin-top: 16px;
}

.abilities-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 12px;
}

.ability-card {
  transition: all 0.2s ease;
}

.ability-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Custom scrollbar */
.profile-view-modal__content::-webkit-scrollbar {
  width: 6px;
}

.profile-view-modal__content::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.profile-view-modal__content::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.profile-view-modal__content::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .profile-view-modal__header {
    padding: 20px;
  }

  .profile-view-modal__content {
    padding: 20px;
  }

  .profile-view-modal__actions {
    padding: 16px 20px;
    flex-direction: column-reverse;
    gap: 12px;
  }

  .profile-view-modal__actions .v-btn {
    width: 100%;
  }

  .abilities-grid {
    grid-template-columns: 1fr;
  }
}
</style>
