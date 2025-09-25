<template>
  <v-dialog
    :model-value="modelValue"
    @update:model-value="$emit('update:modelValue', $event)"
    max-width="900px"
    persistent
    scrollable
  >
    <v-card class="profile-abilities-modal">
      <v-card-title class="profile-abilities-modal__header">
        <div class="d-flex align-center">
          <v-icon
            color="primary"
            size="24"
            class="mr-3"
          >
            mdi-shield-account
          </v-icon>
          <span class="text-h5 font-weight-bold">
            Gerenciar Abilities - {{ profile?.display_name }}
          </span>
        </div>
        <v-btn
          icon
          variant="text"
          @click="closeModal"
          class="profile-abilities-modal__close-btn"
        >
          <v-icon size="20">mdi-close</v-icon>
        </v-btn>
      </v-card-title>

      <v-card-text class="profile-abilities-modal__content">
        <div v-if="loading" class="text-center py-8">
          <v-progress-circular
            indeterminate
            color="primary"
            size="64"
          />
          <p class="text-body-1 mt-4">Carregando abilities...</p>
        </div>

        <div v-else-if="error" class="text-center py-8">
          <v-icon color="error" size="64" class="mb-4">mdi-alert-circle</v-icon>
          <p class="text-body-1 text-error">{{ error }}</p>
          <v-btn
            color="primary"
            variant="outlined"
            class="mt-4"
            @click="loadAbilities"
          >
            Tentar Novamente
          </v-btn>
        </div>

        <div v-else>
          <!-- Search and Filter -->
          <div class="mb-6">
            <v-row>
              <v-col cols="12" md="8">
                <v-text-field
                  v-model="searchQuery"
                  label="Buscar abilities"
                  prepend-inner-icon="mdi-magnify"
                  variant="outlined"
                  density="comfortable"
                  clearable
                  rounded="lg"
                  hide-details
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-select
                  v-model="selectedCategory"
                  :items="categoryOptions"
                  label="Categoria"
                  variant="outlined"
                  density="comfortable"
                  clearable
                  rounded="lg"
                  hide-details
                />
              </v-col>
            </v-row>
          </div>

          <!-- Abilities by Category -->
          <div v-if="filteredAbilities.length === 0" class="text-center py-8">
            <v-icon color="grey" size="64" class="mb-4">mdi-shield-off</v-icon>
            <p class="text-body-1 text-grey">Nenhuma ability encontrada</p>
          </div>

          <div v-else>
            <v-expansion-panels
              v-for="(abilities, category) in groupedAbilities"
              :key="category"
              variant="accordion"
              class="mb-4"
            >
              <v-expansion-panel>
                <template #title>
                  <div class="d-flex align-center">
                    <v-icon :color="getCategoryColor(category)" class="mr-3">
                      {{ getCategoryIcon(category) }}
                    </v-icon>
                    <span class="font-weight-medium">{{ getCategoryTitle(category) }}</span>
                    <v-chip
                      size="small"
                      color="primary"
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
                      :class="[
                        'ability-card',
                        { 'ability-card--selected': isAbilitySelected(ability.id) }
                      ]"
                      @click="toggleAbility(ability.id)"
                    >
                      <v-card-text class="pa-4">
                        <div class="d-flex align-center">
                          <v-checkbox
                            :model-value="isAbilitySelected(ability.id)"
                            color="primary"
                            hide-details
                            class="mr-3"
                            @click.stop
                          />
                          <div class="flex-grow-1">
                            <div class="font-weight-medium text-body-1 mb-1">
                              {{ ability.display_name }}
                            </div>
                            <div class="text-caption text-medium-emphasis mb-2">
                              {{ ability.name }}
                            </div>
                            <div v-if="ability.description" class="text-body-2">
                              {{ ability.description }}
                            </div>
                          </div>
                          <v-chip
                            :color="getCategoryColor(category)"
                            size="x-small"
                            variant="outlined"
                          >
                            {{ category }}
                          </v-chip>
                        </div>
                      </v-card-text>
                    </v-card>
                  </div>
                </template>
              </v-expansion-panel>
            </v-expansion-panels>
          </div>
        </div>
      </v-card-text>

      <v-card-actions class="profile-abilities-modal__actions">
        <v-spacer />
        <div class="text-body-2 text-medium-emphasis mr-4">
          {{ selectedAbilities.length }} abilities selecionadas
        </div>
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
          :loading="saving"
          :disabled="!hasChanges"
          @click="saveAbilities"
        >
          Salvar Alterações
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script lang="ts" setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useProfilesApi, type Ability } from '../api'

interface Props {
  modelValue: boolean
  profile?: any
}

interface Emits {
  (e: 'update:modelValue', value: boolean): void
  (e: 'reload', profile: any): void
}

const props = withDefaults(defineProps<Props>(), {
  profile: null
})

const emit = defineEmits<Emits>()

// Composables
const { getAllAbilities, updateAbilities } = useProfilesApi()

// Reactive data
const loading = ref(false)
const saving = ref(false)
const error = ref<string | null>(null)
const searchQuery = ref('')
const selectedCategory = ref('')
const allAbilities = ref<Ability[]>([])
const selectedAbilities = ref<number[]>([])
const originalAbilities = ref<number[]>([])

// Computed
const hasChanges = computed(() => {
  const current = [...selectedAbilities.value].sort()
  const original = [...originalAbilities.value].sort()
  return JSON.stringify(current) !== JSON.stringify(original)
})

const filteredAbilities = computed(() => {
  let filtered = allAbilities.value

  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(ability =>
      ability.display_name.toLowerCase().includes(query) ||
      ability.name.toLowerCase().includes(query) ||
      ability.description?.toLowerCase().includes(query)
    )
  }

  // Filter by category
  if (selectedCategory.value) {
    filtered = filtered.filter(ability => ability.category === selectedCategory.value)
  }

  return filtered
})

const groupedAbilities = computed(() => {
  const grouped: Record<string, Ability[]> = {}

  filteredAbilities.value.forEach(ability => {
    if (!grouped[ability.category]) {
      grouped[ability.category] = []
    }
    grouped[ability.category].push(ability)
  })

  // Sort abilities within each category
  Object.keys(grouped).forEach(category => {
    grouped[category].sort((a, b) => a.display_name.localeCompare(b.display_name))
  })

  return grouped
})

const categoryOptions = computed(() => {
  const categories = [...new Set(allAbilities.value.map(ability => ability.category))]
  return categories.map(category => ({
    title: getCategoryTitle(category),
    value: category
  }))
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

const isAbilitySelected = (abilityId: number) => {
  return selectedAbilities.value.includes(abilityId)
}

const toggleAbility = (abilityId: number) => {
  const index = selectedAbilities.value.indexOf(abilityId)
  if (index > -1) {
    selectedAbilities.value.splice(index, 1)
  } else {
    selectedAbilities.value.push(abilityId)
  }
}

const loadAbilities = async () => {
  if (!props.profile) return

  loading.value = true
  error.value = null

  try {
    // Load all available abilities
    const abilities = await getAllAbilities()
    allAbilities.value = Object.values(abilities).flat()

    // Set current profile abilities
    selectedAbilities.value = props.profile.abilities?.map((a: any) => a.id) || []
    originalAbilities.value = [...selectedAbilities.value]
  } catch (err: any) {
    error.value = err.message || 'Erro ao carregar abilities'
  } finally {
    loading.value = false
  }
}

const saveAbilities = async () => {
  if (!props.profile || !hasChanges.value) return

  saving.value = true

  try {
    await updateAbilities(props.profile.id, selectedAbilities.value)
    emit('reload', props.profile)
    closeModal()
  } catch (err: any) {
    error.value = err.message || 'Erro ao salvar abilities'
  } finally {
    saving.value = false
  }
}

const closeModal = () => {
  emit('update:modelValue', false)
  // Reset form
  selectedAbilities.value = []
  originalAbilities.value = []
  searchQuery.value = ''
  selectedCategory.value = ''
  error.value = null
}

// Watchers
watch(() => props.modelValue, (newValue) => {
  if (newValue && props.profile) {
    loadAbilities()
  }
})

watch(() => props.profile, () => {
  if (props.modelValue && props.profile) {
    loadAbilities()
  }
}, { deep: true })
</script>

<style scoped>
.profile-abilities-modal {
  border-radius: 16px;
  overflow: hidden;
}

.profile-abilities-modal__header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 24px;
  position: relative;
}

.profile-abilities-modal__close-btn {
  position: absolute;
  top: 16px;
  right: 16px;
  color: white !important;
}

.profile-abilities-modal__content {
  padding: 24px;
  max-height: 70vh;
  overflow-y: auto;
}

.profile-abilities-modal__actions {
  padding: 16px 24px;
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
}

.abilities-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 12px;
}

.ability-card {
  cursor: pointer;
  transition: all 0.2s ease;
  border: 2px solid transparent;
}

.ability-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.ability-card--selected {
  border-color: rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.05);
}

/* Custom scrollbar */
.profile-abilities-modal__content::-webkit-scrollbar {
  width: 6px;
}

.profile-abilities-modal__content::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.profile-abilities-modal__content::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.profile-abilities-modal__content::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .profile-abilities-modal__header {
    padding: 20px;
  }

  .profile-abilities-modal__content {
    padding: 20px;
  }

  .profile-abilities-modal__actions {
    padding: 16px 20px;
    flex-direction: column-reverse;
    gap: 12px;
  }

  .profile-abilities-modal__actions .v-btn {
    width: 100%;
  }

  .abilities-grid {
    grid-template-columns: 1fr;
  }
}
</style>
