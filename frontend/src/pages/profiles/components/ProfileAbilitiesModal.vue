<template>
  <BaseDialog
    :model-value="modelValue"
    :title="`Gerenciar Permissões - ${profile?.display_name || ''}`"
    :subtitle="'Configure as permissões do perfil'"
    icon="mdi-shield-account"
    icon-color="primary"
    max-width="900px"
    :fullscreen="$vuetify.display.mobile"
    :show-progress="false"
    @close="closeModal"
  >
    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <v-progress-circular
        indeterminate
        color="primary"
        size="64"
      />
      <p class="text-body-1 mt-4">Carregando permissões...</p>
    </div>

    <!-- Error State -->
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

    <!-- Content -->
    <div v-else>
          <!-- Search and Filter -->
          <div class="mb-6">
            <v-row>
              <v-col cols="12" md="8">
                <v-text-field
                  v-model="searchQuery"
                  label="Buscar permissões"
                  prepend-inner-icon="mdi-magnify"
                  variant="outlined"
                  density="compact"
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
                  density="compact"
                  clearable
                  rounded="lg"
                  hide-details
                />
              </v-col>
            </v-row>

              <!-- Action Buttons Row -->
              <v-row class="mt-4">
                <v-col cols="12">
                  <div class="d-flex align-center">
                    <!-- Marcar/Desmarcar Todas Switch -->
                    <div class="d-flex align-center">
                      <v-switch
                        v-model="markAllSwitch"
                        color="primary"
                        hide-details
                        density="compact"
                        @update:model-value="handleMarkAllSwitch"
                      />
                      <span class="ml-2 text-body-1 font-weight-medium">
                        {{ markAllSwitch ? 'Desmarcar Todas' : 'Marcar Todas' }}
                      </span>
                    </div>
                  </div>
                </v-col>
              </v-row>
          </div>

          <!-- Permissões por Categoria -->
          <div v-if="filteredAbilities.length === 0" class="text-center py-8">
            <v-icon color="grey" size="64" class="mb-4">mdi-shield-off</v-icon>
            <p class="text-body-1 text-grey">Nenhuma permissão encontrada</p>
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
                      {{ getSelectedCountInCategory(category) }}/{{ abilities.length }} Permissões
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
                        </div>
                      </v-card-text>
                    </v-card>
                  </div>
                </template>
              </v-expansion-panel>
            </v-expansion-panels>
        </div>
      </div>

    <template #actions>
      <v-spacer />
      <div class="d-flex modal-actions-container">
        <v-chip
          color="primary"
          variant="outlined"
          class="mr-4 align-self-center"
        >
          {{ selectedAbilities.length }}/{{ allAbilities.length }}
        </v-chip>
        <BtnCancel @click="closeModal" />
        <BtnSave
          :loading="saving"
          text="Salvar Alterações"
          prepend-icon="mdi-check"
          @click="saveAbilities"
        />
      </div>
    </template>
  </BaseDialog>
</template>

<script lang="ts" setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useProfilesApi, type Ability } from '../api'
import { showSuccessToast, showErrorToast } from '@/utils/swal'
import BaseDialog from '@/components/BaseDialog.vue'

interface Props {
  modelValue: boolean
  profile?: any
}

interface Emits {
  (e: 'update:modelValue', value: boolean): void
  (e: 'reload'): void
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
const markAllSwitch = ref(false)

// Computed
const hasChanges = computed(() => {
  const current = [...selectedAbilities.value].sort()
  const original = [...originalAbilities.value].sort()
  return JSON.stringify(current) !== JSON.stringify(original)
})

const formProgress = computed(() => {
  if (!allAbilities.value.length) return 0
  return (selectedAbilities.value.length / allAbilities.value.length) * 100
})

const filteredAbilities = computed(() => {
  let filtered = Array.isArray(allAbilities.value) ? allAbilities.value : []

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
    grouped[ability.category]?.push(ability)
  })

  // Sort permissions within each category
  Object.keys(grouped).forEach(category => {
    grouped[category]?.sort((a, b) => a.display_name.localeCompare(b.display_name))
  })

  return grouped
})

const categoryOptions = computed(() => {
  const abilities = Array.isArray(allAbilities.value) ? allAbilities.value : []
  const categories = [...new Set(abilities.map(ability => ability.category))]
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

const getSelectedCountInCategory = (category: string) => {
  const abilitiesInCategory = allAbilities.value.filter(ability => ability.category === category)
  return abilitiesInCategory.filter(ability => selectedAbilities.value.includes(ability.id)).length
}

const selectAll = () => {
  selectedAbilities.value = allAbilities.value.map(ability => ability.id)
  markAllSwitch.value = true
}

const clearAll = () => {
  selectedAbilities.value = []
  markAllSwitch.value = false
}

const handleMarkAllSwitch = (value: boolean | null) => {
  if (value) {
    selectAll()
  } else {
    clearAll()
  }
}

const loadAbilities = async () => {
  if (!props.profile) return

  loading.value = true
  error.value = null

  try {
    // Load all available permissions
    const abilities = await getAllAbilities()
    allAbilities.value = abilities

    // Set current profile permissions
    selectedAbilities.value = props.profile.abilities?.map((a: any) => a.id) || []
    originalAbilities.value = [...selectedAbilities.value]

    // Sync switch state
    markAllSwitch.value = selectedAbilities.value.length === allAbilities.value.length
  } catch (err: any) {
    error.value = err.message || 'Erro ao carregar permissões'
  } finally {
    loading.value = false
  }
}

const saveAbilities = async () => {
  if (!props.profile) return

  saving.value = true

  try {
    await updateAbilities(props.profile.id, selectedAbilities.value)
    showSuccessToast('Permissões do perfil atualizadas com sucesso!', 'Sucesso!')
    emit('reload')
    closeModal()
  } catch (err: any) {
    const errorMessage = err?.response?.data?.message ||
                        err?.message ||
                        'Erro ao salvar permissões'
    showErrorToast(errorMessage, 'Erro!')
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
  markAllSwitch.value = false
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

// Watch for changes in selectedAbilities to sync the switch
watch(selectedAbilities, (newSelection) => {
  markAllSwitch.value = newSelection.length === allAbilities.value.length && allAbilities.value.length > 0
}, { deep: true })
</script>

<style scoped>

.abilities-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 12px;
}

.ability-card {
  cursor: pointer;
  transition: all 0.2s ease;
  border: 2px solid #e0e0e0;
}

.ability-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  border-color: #b0b0b0;
}

.ability-card--selected {
  border-color: rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.05);
}

.ability-card--selected:hover {
  border-color: rgb(var(--v-theme-primary));
}

/* Action buttons styling */
.d-flex.gap-2 {
  gap: 8px;
}

.d-flex.gap-2 .v-btn {
  flex: 1;
}

/* Desktop styles */
.modal-actions-container {
  gap: 16px;
}

/* Mobile responsiveness */
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

  .abilities-grid {
    grid-template-columns: 1fr;
  }

  .d-flex.gap-2 {
    flex-direction: column;
    gap: 4px;
  }

  .d-flex.gap-2 .v-btn {
    width: 100%;
  }
}

</style>
