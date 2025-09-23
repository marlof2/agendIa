<template>
  <div class="clients">
    <!-- Page Header -->
    <PageHeader
      title="Clientes"
      subtitle="Gerencie todos os clientes cadastrados"
      :breadcrumbs="[
        { title: 'Clientes' }
      ]"
    >
      <template #actions>
        <v-btn
          color="primary"
          prepend-icon="mdi-plus"
          size="large"
        >
          Novo Cliente
        </v-btn>
        <v-btn
          variant="outlined"
          prepend-icon="mdi-download"
          size="large"
        >
          Exportar
        </v-btn>
      </template>
    </PageHeader>

    <!-- Search and Filters -->
    <v-card class="mb-6" elevation="2">
      <v-card-text class="pa-4">
        <v-row>
          <v-col cols="12" md="4">
            <v-text-field
              v-model="searchQuery"
              label="Buscar cliente"
              prepend-inner-icon="mdi-magnify"
              variant="outlined"
              density="compact"
              clearable
            />
          </v-col>
          <v-col cols="12" md="4">
            <v-text-field
              v-model="selectedStatus"
              label="Filtrar por status"
              variant="outlined"
              density="compact"
              clearable
              placeholder="Ex: Ativo, Inativo"
            />
          </v-col>
          <v-col cols="12" md="4">
            <v-text-field
              v-model="selectedSource"
              label="Filtrar por origem"
              variant="outlined"
              density="compact"
              clearable
              placeholder="Ex: Site, WhatsApp"
            />
          </v-col>
          <v-col cols="12" md="2" class="d-flex align-center">
            <v-btn
              color="primary"
              variant="outlined"
              block
              @click="applyFilters"
            >
              Aplicar
            </v-btn>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Loading State -->
    <v-card v-if="loading" elevation="2">
      <v-card-text class="text-center pa-8">
        <v-progress-circular indeterminate color="primary" size="64" class="mb-4" />
        <h3 class="text-h6 mb-2">Carregando clientes...</h3>
        <p class="text-body-2 text-medium-emphasis">Aguarde enquanto buscamos os dados</p>
      </v-card-text>
    </v-card>

    <!-- Error State -->
    <v-card v-else-if="error" elevation="2">
      <v-card-text class="text-center pa-8">
        <v-icon size="64" color="error" class="mb-4">mdi-alert-circle-outline</v-icon>
        <h3 class="text-h6 mb-2">Erro ao carregar clientes</h3>
        <p class="text-body-2 text-medium-emphasis mb-4">{{ error }}</p>
        <v-btn color="primary" @click="loadClients">
          Tentar Novamente
        </v-btn>
      </v-card-text>
    </v-card>

    <!-- Clients Grid or Empty State -->
    <div v-else>
      <!-- Clients Grid -->
      <v-row v-if="filteredClients.length > 0">
        <v-col
          v-for="client in filteredClients"
          :key="client.id"
          cols="12"
          sm="6"
          md="4"
          lg="3"
        >
          <v-card
            class="client-card"
            elevation="2"
            @click="viewClient(client)"
          >
            <v-card-text class="pa-4">
              <div class="text-center mb-4">
                <v-avatar size="64" color="primary" class="mb-2">
                  <v-icon color="white" size="32">mdi-account</v-icon>
                </v-avatar>
                <h3 class="text-h6 font-weight-bold">{{ client.name }}</h3>
                <p class="text-caption text-medium-emphasis">{{ client.email }}</p>
              </div>

              <v-divider class="mb-3" />

              <div class="client-info">
                <div class="d-flex align-center mb-2">
                  <v-icon size="16" class="mr-2">mdi-phone</v-icon>
                  <span class="text-body-2">{{ client.phone }}</span>
                </div>
                <div v-if="client.cpf" class="d-flex align-center mb-2">
                  <v-icon size="16" class="mr-2">mdi-card-account-details</v-icon>
                  <span class="text-body-2">{{ client.cpf }}</span>
                </div>
                <div v-if="client.birth_date" class="d-flex align-center mb-2">
                  <v-icon size="16" class="mr-2">mdi-cake</v-icon>
                  <span class="text-body-2">{{ new Date(client.birth_date).toLocaleDateString('pt-BR') }}</span>
                </div>
                <div v-if="client.address?.city" class="d-flex align-center mb-2">
                  <v-icon size="16" class="mr-2">mdi-map-marker</v-icon>
                  <span class="text-body-2">{{ client.address.city }}, {{ client.address.state }}</span>
                </div>
                <div v-if="client.status" class="d-flex align-center justify-space-between mb-2">
                  <div class="d-flex align-center">
                    <v-icon size="16" class="mr-2">mdi-account-check</v-icon>
                    <span class="text-body-2">Status</span>
                  </div>
                  <v-chip
                    :color="getStatusColor(client.status)"
                    size="small"
                    variant="flat"
                  >
                    {{ client.status }}
                  </v-chip>
                </div>
                <div v-if="client.source" class="d-flex align-center mb-2">
                  <v-icon size="16" class="mr-2">mdi-source-branch</v-icon>
                  <span class="text-body-2">{{ client.source }}</span>
                </div>
              </div>
            </v-card-text>

            <v-card-actions class="pa-4 pt-0">
              <v-btn
                color="primary"
                variant="text"
                size="small"
                @click.stop="editClient(client)"
              >
                <v-icon start>mdi-pencil</v-icon>
                Editar
              </v-btn>
              <v-spacer />
              <v-btn
                color="error"
                variant="text"
                size="small"
                @click.stop="deleteClient(client)"
              >
                <v-icon start>mdi-delete</v-icon>
                Excluir
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>

      <!-- Empty State -->
      <v-card v-else elevation="2">
        <v-card-text class="text-center pa-8">
          <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-account-group-outline</v-icon>
          <h3 class="text-h6 mb-2">Nenhum cliente encontrado</h3>
          <p class="text-body-2 text-medium-emphasis mb-4">
            {{ searchQuery ? 'Tente ajustar os filtros de busca' : 'Comece adicionando seu primeiro cliente' }}
          </p>
          <v-btn
            color="primary"
            prepend-icon="mdi-plus"
            @click="addNewClient"
          >
            Adicionar Cliente
          </v-btn>
        </v-card-text>
      </v-card>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref, computed, onMounted } from 'vue'
import PageHeader from '@/components/PageHeader.vue'
import { useClientsApi, type Client, type Filters } from './api'
import { usePermission } from '@/composables/usePermission'

const { requirePermission } = usePermission();

// API composable
const {
  loading,
  error,
  clients,
  currentClient,
  getAll,
  getById,
  createItem,
  updateItem,
  deleteItem,
  clearData,
  resetState
} = useClientsApi()

// Reactive data
const searchQuery = ref('')
const selectedStatus = ref('')
const selectedSource = ref('')

// Filter options
const statusOptions = [
  'Ativo',
  'Inativo',
  'Bloqueado'
]

const sourceOptions = [
  'Site',
  'WhatsApp',
  'Indicação',
  'Google',
  'Facebook'
]

// Computed properties
const filteredClients = computed(() => {
  let filtered = clients.value

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(client =>
      client.name.toLowerCase().includes(query) ||
      client.email.toLowerCase().includes(query) ||
      client.phone.includes(query)
    )
  }

  if (selectedStatus.value) {
    const status = selectedStatus.value.toLowerCase()
    filtered = filtered.filter(client =>
      client.status?.toLowerCase().includes(status)
    )
  }

  if (selectedSource.value) {
    const source = selectedSource.value.toLowerCase()
    filtered = filtered.filter(client =>
      client.source?.toLowerCase().includes(source)
    )
  }

  return filtered
})

// Lifecycle
onMounted(async () => {
  await loadClients()
  // requirePermission("clients.view");
})

// Methods
const loadClients = async () => {
  try {
    const filters: Filters = {}
    if (searchQuery.value) filters.name = searchQuery.value
    if (selectedStatus.value) filters.status = selectedStatus.value
    if (selectedSource.value) filters.source = selectedSource.value

    await getAll(filters)
  } catch (err) {
    console.error('Erro ao carregar clientes:', err)
  }
}

const getStatusColor = (status: string) => {
  const colors: Record<string, string> = {
    'Ativo': 'success',
    'Inativo': 'warning',
    'Bloqueado': 'error'
  }
  return colors[status] || 'default'
}

const applyFilters = async () => {
  await loadClients()
}

const viewClient = async (client: Client) => {
  try {
    await getById(client.id)
    // Aqui você pode abrir um modal ou navegar para uma página de detalhes
    console.log('Visualizar cliente:', currentClient.value)
  } catch (err) {
    console.error('Erro ao carregar cliente:', err)
  }
}

const editClient = (client: Client) => {
  // Aqui você pode abrir um modal de edição ou navegar para uma página de edição
  console.log('Editar cliente:', client)
}

const scheduleAppointment = (client: Client) => {
  // Aqui você pode navegar para a página de agendamentos com o cliente pré-selecionado
  console.log('Agendar para cliente:', client)
}

const addNewClient = () => {
  // Aqui você pode abrir um modal de criação ou navegar para uma página de criação
  console.log('Adicionar novo cliente')
}

const deleteClient = async (client: Client) => {
  if (confirm(`Tem certeza que deseja excluir o cliente ${client.name}?`)) {
    try {
      await deleteItem(client.id)
      console.log('Cliente excluído com sucesso')
    } catch (err) {
      console.error('Erro ao excluir cliente:', err)
    }
  }
}
</script>

<style scoped>
.clients {
  max-width: 1400px;
  margin: 0 auto;
}

.client-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  cursor: pointer;
}

.client-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
}

.client-info {
  font-size: 0.875rem;
}

.v-card-actions {
  border-top: 1px solid rgba(0, 0, 0, 0.05);
}
</style>
