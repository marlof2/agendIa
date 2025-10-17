<template>
  <BaseDialog
    :model-value="modelValue"
    title="Vincular-se a Empresas"
    subtitle="Encontre e vincule-se a empresas para começar a usar o sistema"
    icon="mdi-office-building-plus"
    icon-color="primary"
    max-width="1000px"
    :fullscreen="$vuetify.display.mobile"
    @close="closeModal"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <template #default>
      <!-- Informação sobre o perfil -->
      <v-alert
        type="info"
        variant="tonal"
        class="mb-6"
        border="start"
        prominent
      >
        <template v-slot:prepend>
          <v-icon>mdi-information</v-icon>
        </template>
        <div>
          <div class="text-h6 mb-2">Vinculação como Cliente</div>
          <p class="text-body-2 mb-0">
            Você será vinculado automaticamente com o perfil de <strong>Cliente</strong> nas empresas selecionadas.
            Este perfil permite que você faça agendamentos e acesse os serviços das empresas.
          </p>
        </div>
      </v-alert>

      <!-- Busca -->
      <div class="mb-6">
        <v-text-field
          v-model="searchCompanies"
          label="Buscar empresas"
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          density="compact"
          rounded="lg"
          clearable
          hide-details
          hint="Digite o nome da empresa que você procura"
          persistent-hint
          @update:model-value="handleSearchCompanies"
        />
      </div>

      <!-- Loading -->
      <div v-if="loadingPublicCompanies" class="text-center py-12">
        <v-progress-circular
          indeterminate
          color="primary"
          size="64"
          width="4"
        />
        <h4 class="text-h6 font-weight-medium mt-4 mb-2">Carregando empresas...</h4>
        <p class="text-body-2 text-medium-emphasis">
          Buscando empresas disponíveis para vinculação
        </p>
      </div>

      <!-- Lista de Empresas Disponíveis -->
      <div v-else-if="availableCompanies.length > 0">
        <div class="mb-4">
          <div class="d-flex align-center justify-space-between mb-4">
            <div>
              <h4 class="text-h6 font-weight-bold mb-2">Empresas Disponíveis</h4>
              <p class="text-body-2 text-medium-emphasis">
                Selecione as empresas que deseja se vincular
              </p>
            </div>
          </div>
        </div>

        <v-list class="pa-0">
          <v-list-item
            v-for="company in availableCompanies"
            :key="company.id"
            class="company-list-item mb-3"
            :class="{ 'selected': isSelected(company.id) }"
            @click="toggleCompany(company.id)"
          >
            <template v-slot:prepend>
              <v-checkbox
                :model-value="isSelected(company.id)"
                color="primary"
                @click.stop="toggleCompany(company.id)"
                hide-details
              />
            </template>

            <v-list-item-title class="font-weight-bold text-h6 mb-2">
              {{ company.name }}
            </v-list-item-title>

            <v-list-item-subtitle class="mb-2">
              <div class="d-flex align-center mb-1">
                <v-icon size="16" class="mr-2">mdi-account-tie</v-icon>
                <span class="font-weight-medium">Responsável:</span>
                <span class="ml-2">{{ company.responsible_name || 'Não informado' }}</span>
              </div>

              <div class="d-flex align-center mb-1">
                <v-icon size="16" class="mr-2">mdi-phone</v-icon>
                <span class="font-weight-medium">Telefone:</span>
                <span class="ml-2">{{ formatPhone(company.phone_1) || 'Não informado' }}</span>
              </div>

              <div v-if="company.person_type" class="d-flex align-center">
                <v-icon size="16" class="mr-2">mdi-domain</v-icon>
                <span class="font-weight-medium">Tipo:</span>
                <span class="ml-2">{{ company.person_type === 'legal' ? 'Pessoa Jurídica' : 'Pessoa Física' }}</span>
              </div>
            </v-list-item-subtitle>

            <template v-slot:append>
              <v-chip
                v-if="isAlreadyLinked(company.id)"
                color="success"
                size="small"
                variant="tonal"
                prepend-icon="mdi-check"
              >
                Vinculado
              </v-chip>
            </template>
          </v-list-item>
        </v-list>

        <!-- Paginação -->
        <div v-if="companiesPagination.last_page > 1" class="d-flex justify-center mt-6">
          <v-pagination
            :model-value="companiesPagination.current_page"
            :length="companiesPagination.last_page"
            :total-visible="5"
            @update:model-value="handlePageChange"
          />
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-12">
        <v-icon size="80" color="grey-lighten-1">mdi-office-building-off</v-icon>
        <h4 class="text-h6 font-weight-bold mt-4 mb-2">Nenhuma empresa encontrada</h4>
        <p class="text-body-2 text-medium-emphasis mb-4">
          Não foram encontradas empresas disponíveis para vinculação.
        </p>
        <v-btn
          color="primary"
          variant="tonal"
          prepend-icon="mdi-refresh"
          @click="handleSearchCompanies"
        >
          Tentar Novamente
        </v-btn>
      </div>
    </template>

        <template #actions>
          <div class="w-100">
            <div class="d-flex flex-column flex-md-row gap-3 align-center justify-end">
              <v-btn
                variant="text"
                @click="closeModal"
                class="order-2 order-md-1"
              >
                Cancelar
              </v-btn>
              <v-btn
                color="primary"
                variant="flat"
                :disabled="selectedCompanyIds.length === 0"
                :loading="associating"
                prepend-icon="mdi-link"
                @click="handleAssociate"
                class="order-1 order-md-2"
              >
                Vincular {{ selectedCompanyIds.length > 1 ? 'Empresas' : 'Empresa' }}
              </v-btn>
            </div>
          </div>
        </template>
  </BaseDialog>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useHttp } from '@/composables/useHttp'
import { useMask } from '@/composables/useMask'
import { showSuccessToast, showErrorToast } from '@/utils/swal'
import BaseDialog from '@/components/BaseDialog.vue'

interface Props {
  modelValue: boolean
  userCompanies: any[]
}

interface Emits {
  (e: 'update:modelValue', value: boolean): void
  (e: 'success'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const http = useHttp()
const { formatPhone } = useMask()

// Estados
const searchCompanies = ref('')
const selectedCompanyIds = ref<number[]>([])
const associating = ref(false)
const loadingPublicCompanies = ref(false)
const publicCompanies = ref<any[]>([])
const companiesPagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
  per_page: 10
})

// Computed
const availableCompanies = computed(() => {
  return publicCompanies.value.filter(company =>
    !isAlreadyLinked(company.id)
  )
})

// Watch para carregar empresas quando modal abre
watch(() => props.modelValue, (isOpen) => {
  if (isOpen) {
    selectedCompanyIds.value = []
    searchCompanies.value = ''
    if (publicCompanies.value.length === 0) {
      loadPublicCompanies()
    }
  }
})

// Métodos
const isSelected = (companyId: number) => {
  return selectedCompanyIds.value.includes(companyId)
}

const toggleCompany = (companyId: number) => {
  const index = selectedCompanyIds.value.indexOf(companyId)
  if (index > -1) {
    selectedCompanyIds.value.splice(index, 1)
  } else {
    selectedCompanyIds.value.push(companyId)
  }
}


const isAlreadyLinked = (companyId: number) => {
  return props.userCompanies.some(userCompany => userCompany.id === companyId)
}

const loadPublicCompanies = async (page: number = 1) => {
  loadingPublicCompanies.value = true
  try {
    const params = new URLSearchParams()
    if (searchCompanies.value) {
      params.append('search', searchCompanies.value)
    }
    params.append('page', page.toString())
    params.append('per_page', '10')

    const response = await http.get(`/companies/available?${params}`)

    // A API retorna diretamente os dados paginados do Laravel
    // response já é o objeto de paginação
    if (response && response.data) {
      const data = response.data || []

      publicCompanies.value = data

      // Configurar paginação com os dados do Laravel
      companiesPagination.value = {
        current_page: response.current_page || 1,
        last_page: response.last_page || 1,
        total: response.total || 0,
        per_page: response.per_page || 10
      }

    } else {
      console.error('Erro na resposta:', response)
      publicCompanies.value = []
    }
  } catch (error: any) {
    console.error('Erro ao carregar empresas:', error)
    showErrorToast('Erro ao carregar empresas disponíveis')
  } finally {
    loadingPublicCompanies.value = false
  }
}

const handleSearchCompanies = async () => {
  await loadPublicCompanies(1)
}

const handlePageChange = async (page: number) => {
  await loadPublicCompanies(page)
}

const handleAssociate = async () => {
  if (selectedCompanyIds.value.length === 0) {
    showErrorToast('Selecione pelo menos uma empresa')
    return
  }

  associating.value = true
  try {
    const response = await http.post('/users/associate-companies', {
      company_ids: selectedCompanyIds.value,
      profile_id: 5, // Perfil de Cliente
    })

    if (response.success) {
      showSuccessToast(response.message || 'Vinculação realizada com sucesso!')
      closeModal()
      emit('success')
    }
  } catch (error: any) {
    console.error('Erro ao associar empresas:', error)
    showErrorToast(error.response?.data?.message || 'Erro ao vincular empresas')
  } finally {
    associating.value = false
  }
}

const closeModal = () => {
  emit('update:modelValue', false)
  selectedCompanyIds.value = []
  searchCompanies.value = ''
}
</script>

<style scoped>
.company-list-item {
  border: 2px solid transparent;
  border-radius: 12px;
  transition: all 0.2s ease;
  cursor: pointer;
  margin-bottom: 8px;
}

.company-list-item:hover {
  background: rgba(var(--v-theme-primary), 0.05);
  border-color: rgba(var(--v-theme-primary), 0.3);
  transform: translateX(4px);
}

.company-list-item.selected {
  background: rgba(var(--v-theme-primary), 0.1);
  border-color: rgb(var(--v-theme-primary));
}
</style>
