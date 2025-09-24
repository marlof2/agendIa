<template>
  <BasePage
    title="Agendamentos"
    subtitle="Gerencie todos os agendamentos do sistema"
    :breadcrumbs="[{ title: 'Agendamentos' }]"
    :show-filters="true"
  >
    <!-- Action Bar -->
    <template #actionBar>
      <ActionBar>
        <template #left>
          <v-btn
            v-if="permissionReactive('appointments.create')"
            color="success"
            prepend-icon="mdi-plus"
            rounded="lg"
            class="text-none font-weight-medium"
            @click="create"
          >
            Novo
          </v-btn>

        </template>
        <template #right>
          <div class="d-flex action-buttons-container">
            <v-btn
              :color="showFilters ? 'primary' : 'default'"
              :variant="showFilters ? 'flat' : 'outlined'"
              prepend-icon="mdi-filter"
              rounded="lg"
              class="text-none font-weight-medium filter-toggle-btn"
              @click="toggleFilters"
            >
              {{ showFilters ? "Ocultar Filtros" : "Mostrar Filtros" }}
            </v-btn>
            <ExportActions
              :data="appointments"
              :columns="tableColumns"
              filename="agendamentos"
              button-text="Exportar"
              color="primary"
              variant="outlined"
              prepend-icon="mdi-download"
              @export="handleExport"
            />
          </div>
        </template>
      </ActionBar>
    </template>

    <!-- Filters -->
    <template #filters>
      <FiltersCard :show="showFilters">
        <template #filtersCard>
          <v-row>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="searchQuery"
                label="Buscar cliente"
                prepend-inner-icon="mdi-magnify"
                variant="outlined"
                density="comfortable"
                clearable
                rounded="lg"
                hide-details
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-select
                v-model="selectedStatus"
                :items="statusOptions"
                label="Status"
                variant="outlined"
                density="comfortable"
                clearable
                rounded="lg"
                hide-details
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-select
                v-model="selectedProfessional"
                :items="professionalOptions"
                label="Profissional"
                variant="outlined"
                density="comfortable"
                clearable
                rounded="lg"
                hide-details
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model="selectedDate"
                label="Data"
                type="date"
                variant="outlined"
                density="comfortable"
                rounded="lg"
                hide-details
              />
            </v-col>
          </v-row>
        </template>
        <template #actionsFilters>
          <v-btn
            color="primary"
            prepend-icon="mdi-magnify"
            rounded="lg"
            class="text-none font-weight-medium"
            @click="performSearch"
          >
            Buscar
          </v-btn>
          <v-btn
            color="secondary"
            prepend-icon="mdi-filter-variant"
            rounded="lg"
            class="text-none font-weight-medium"
            variant="outlined"
            @click="clearFilters"
          >
            Limpar Filtros
          </v-btn>
        </template>
      </FiltersCard>
    </template>

    <!-- Content -->
    <template #content>
      <DataTable
        :headers="headers"
        :items="appointments"
        :search="searchQuery"
        :loading="loading"
        no-data-text="Nenhum agendamento encontrado"
        loading-text="Carregando agendamentos..."
        :items-per-page="10"
      >
        <!-- Desktop Table Slots -->
        <template v-slot:item.client="{ item }">
          <div class="d-flex align-center py-2">
            <v-avatar size="36" color="primary" class="mr-3">
              <v-icon color="white" size="20">mdi-account</v-icon>
            </v-avatar>
            <div>
              <div class="font-weight-medium text-body-1">
                {{ item.client?.name || 'Cliente não informado' }}
              </div>
              <div class="text-caption text-medium-emphasis">
                {{ item.client?.phone || 'Telefone não informado' }}
              </div>
            </div>
          </div>
        </template>

        <template v-slot:item.service="{ item }">
          <div class="py-2">
            <div class="font-weight-medium text-body-2">{{ item.service?.name || 'Serviço não informado' }}</div>
          </div>
        </template>

        <template v-slot:item.professional="{ item }">
          <div class="py-2">
            <div class="font-weight-medium text-body-2">
              {{ item.professional?.name || 'Profissional não informado' }}
            </div>
          </div>
        </template>

        <template v-slot:item.date="{ item }">
          <div class="py-2">
            <div class="font-weight-medium text-body-2">{{ formatDate(item.date) }}</div>
            <div class="text-caption text-medium-emphasis">{{ item.start_time }} - {{ item.end_time }}</div>
          </div>
        </template>

        <template v-slot:item.status="{ item }">
          <v-chip
            :color="getStatusColor(item.status)"
            size="small"
            variant="flat"
            rounded="lg"
            class="font-weight-medium"
          >
            {{ getStatusText(item.status) }}
          </v-chip>
        </template>

        <template v-slot:item.actions="{ item }">
          <div class="d-flex align-center gap-2">
            <v-btn
              icon
              size="small"
              variant="text"
              color="primary"
              @click="view(item)"
              class="action-btn"
            >
              <v-icon size="18">mdi-eye</v-icon>
            </v-btn>
            <v-btn
              icon
              size="small"
              variant="text"
              color="warning"
              @click="edit(item)"
              class="action-btn"
            >
              <v-icon size="18">mdi-pencil</v-icon>
            </v-btn>
            <v-btn
              icon
              size="small"
              variant="text"
              color="error"
              @click="remove(item)"
              class="action-btn"
            >
              <v-icon size="18">mdi-delete</v-icon>
            </v-btn>
          </div>
        </template>

        <!-- Mobile Card Slot -->
        <template #mobile-card="{ item }">
          <div class="mobile-appointment-card">
            <div class="d-flex align-center mb-3">
              <v-avatar size="40" color="primary" class="mr-3">
                <v-icon color="white" size="22">mdi-account</v-icon>
              </v-avatar>
              <div class="flex-grow-1">
                <div class="font-weight-bold text-h6">{{ item.client?.name || 'Cliente não informado' }}</div>
                <div class="text-caption text-medium-emphasis">
                  {{ item.client?.phone || 'Telefone não informado' }}
                </div>
              </div>
              <v-chip
                :color="getStatusColor(item.status)"
                size="small"
                variant="flat"
                rounded="lg"
                class="font-weight-medium"
              >
                {{ getStatusText(item.status) }}
              </v-chip>
            </div>

            <div class="mobile-appointment-details">
              <div class="detail-row">
                <v-icon size="16" class="mr-2">mdi-calendar</v-icon>
                <span class="font-weight-medium"
                  >{{ formatDate(item.date) }} às {{ item.start_time }}</span
                >
              </div>
              <div class="detail-row">
                <v-icon size="16" class="mr-2">mdi-account-tie</v-icon>
                <span>{{ item.professional?.name || 'Profissional não informado' }}</span>
              </div>
              <div class="detail-row">
                <v-icon size="16" class="mr-2">mdi-medical-bag</v-icon>
                <span>{{ item.service?.name || 'Serviço não informado' }}</span>
              </div>
            </div>

            <div class="mobile-appointment-actions mt-4">
              <v-btn
                color="primary"
                variant="text"
                size="small"
                prepend-icon="mdi-eye"
                @click="view(item)"
              >
                Ver
              </v-btn>
              <v-btn
                color="warning"
                variant="text"
                size="small"
                prepend-icon="mdi-pencil"
                @click="edit(item)"
              >
                Editar
              </v-btn>
              <v-btn
                color="error"
                variant="text"
                size="small"
                prepend-icon="mdi-delete"
                @click="remove(item)"
              >
                Excluir
              </v-btn>
            </div>
          </div>
        </template>
      </DataTable>
    </template>
  </BasePage>

  <!-- Modals -->
  <AppointmentModal
    v-model="showAppointmentModal"
    :appointment="selectedAppointment"
    @success="handleAppointmentSuccess"
  />

  <AppointmentViewModal
    v-model="showViewModal"
    :appointment="selectedAppointment"
    @edit="handleEditFromView"
  />

  <DeleteConfirmModal
    v-model="showDeleteModal"
    :item="selectedAppointment"
    item-type="agendamento"
    @confirm="handleDeleteConfirm"
  />
</template>

<script lang="ts" setup>
import BasePage from "@/components/BasePage.vue";
import ActionBar from "@/components/ActionBar.vue";
import FiltersCard from "@/components/FiltersCard.vue";
import DataTable from "@/components/DataTable.vue";
import ExportActions from "@/components/ExportActions.vue";
import AppointmentModal from "./components/AppointmentModal.vue";
import AppointmentViewModal from "./components/AppointmentViewModal.vue";
import DeleteConfirmModal from "./components/DeleteConfirmModal.vue";
import { useAppointmentsApi } from "./api";
import { useExport } from "@/composables/useExport";
import { useAbilities } from "@/composables/useAbilities";

const { permissionReactive } = useAbilities();


onMounted(async () => {
  // await loadAppointments();
  console.log(permissionReactive.value('appointments.create'));
});

// Composables
const {
  items: appointments,
  loading,
  error,
  getAll,
  createItem,
  updateItem,
  deleteItem,
} = useAppointmentsApi();

const { handleExport: exportData } = useExport();

// Funções de navegação específicas da página
const view = (item: any) => {
  selectedAppointment.value = item;
  showViewModal.value = true;
};

const edit = (item: any) => {
  selectedAppointment.value = item;
  isEditing.value = true;
  showAppointmentModal.value = true;
};

const remove = (item: any) => {
  selectedAppointment.value = item;
  showDeleteModal.value = true;
};



const loadAppointments = async () => {
  try {
    await getAll();
  } catch (err) {
    console.error('Erro ao carregar agendamentos:', err);
  }
};


const create = () => {
  selectedAppointment.value = null;
  isEditing.value = false;
  showAppointmentModal.value = true;
};

// Funções dos modais
const handleAppointmentSuccess = async (appointment: any) => {
  try {
    if (isEditing.value && selectedAppointment.value && 'id' in selectedAppointment.value) {
      await updateItem(selectedAppointment.value.id, appointment);
      console.log('Agendamento atualizado:', appointment);
    } else {
      await createItem(appointment);
      console.log('Agendamento criado:', appointment);
    }
    // A lista é atualizada automaticamente pelo composable
  } catch (err) {
    console.error('Erro ao salvar agendamento:', err);
  }
};

const handleDeleteConfirm = async (item: any) => {
  try {
    await deleteItem(item.id);
    console.log('Agendamento excluído:', item);
    // A lista é atualizada automaticamente pelo composable
  } catch (err) {
    console.error('Erro ao excluir agendamento:', err);
  }
};

const handleEditFromView = (appointment: any) => {
  selectedAppointment.value = appointment;
  isEditing.value = true;
  showAppointmentModal.value = true;
};

// Reactive data
const searchQuery = ref("");
const selectedStatus = ref("");
const selectedProfessional = ref("");
const selectedDate = ref("");
const showFilters = ref(true); // Default true para mostrar sempre

// Modal states
const showAppointmentModal = ref(false);
const showViewModal = ref(false);
const showDeleteModal = ref(false);
const selectedAppointment = ref<any>(null);
const isEditing = ref(false);

// Table headers
const headers = [
  { title: "Cliente", key: "client", sortable: true },
  { title: "Serviço", key: "service", sortable: true },
  { title: "Profissional", key: "professional", sortable: true },
  { title: "Data/Hora", key: "date", sortable: true },
  { title: "Status", key: "status", sortable: true },
  { title: "Ações", key: "actions", sortable: false, width: "120px" },
];

// Colunas para exportação (sem ações)
const tableColumns = [
  { title: "Cliente", key: "client.name" },
  { title: "Telefone", key: "client.phone" },
  { title: "Email", key: "client.email" },
  { title: "Serviço", key: "service.name" },
  { title: "Profissional", key: "professional.name" },
  { title: "Data", key: "date" },
  { title: "Hora Início", key: "start_time" },
  { title: "Hora Fim", key: "end_time" },
  { title: "Status", key: "status" },
  { title: "Valor", key: "service.price" },
  { title: "Observações", key: "notes" },
];

// Filter options
const statusOptions = [
  { title: "Agendado", value: "scheduled" },
  { title: "Confirmado", value: "confirmed" },
  { title: "Cancelado", value: "cancelled" },
  { title: "Concluído", value: "completed" }
];

const professionalOptions = [
  "Dr. João Silva",
  "Dra. Maria Santos",
  "Dr. Pedro Costa",
  "Dra. Ana Oliveira",
];

// Os dados de agendamentos agora vêm do composable useAppointmentsApi

// Methods
const getStatusColor = (status: string) => {
  const colors: Record<string, string> = {
    scheduled: "warning",
    confirmed: "success",
    cancelled: "error",
    completed: "info",
  };
  return colors[status] || "default";
};

const getStatusText = (status: string) => {
  const statusMap: Record<string, string> = {
    scheduled: "Agendado",
    confirmed: "Confirmado",
    cancelled: "Cancelado",
    completed: "Concluído",
  };
  return statusMap[status] || status;
};

const formatDate = (dateString: string) => {
  if (!dateString) return 'Data não informada';

  try {
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    });
  } catch (error) {
    return dateString;
  }
};

// Filter and search methods
const toggleFilters = () => {
  showFilters.value = !showFilters.value;
};

const performSearch = async () => {
  try {
    const filters: any = {};

    if (searchQuery.value) {
      filters.search = searchQuery.value;
    }

    if (selectedStatus.value) {
      filters.status = selectedStatus.value.toLowerCase();
    }

    if (selectedProfessional.value) {
      filters.professional_id = selectedProfessional.value;
    }

    if (selectedDate.value) {
      filters.date = selectedDate.value;
    }

    await getAll(filters);
    console.log("Busca realizada com filtros:", filters);
  } catch (err) {
    console.error('Erro ao realizar busca:', err);
  }
};

const clearFilters = async () => {
  searchQuery.value = "";
  selectedStatus.value = "";
  selectedProfessional.value = "";
  selectedDate.value = "";

  // Recarregar todos os agendamentos
  await loadAppointments();
};

// Export function
const handleExport = async (format: 'excel' | 'pdf' | 'csv', data: any[], filename: string) => {
  try {
    await exportData(format, data, tableColumns, filename);
    console.log(`Exportação ${format.toUpperCase()} concluída: ${filename}`);
  } catch (error) {
    console.error('Erro na exportação:', error);
  }
};
</script>

<style scoped>
/* Estilos específicos da página de agendamentos */
.action-btn {
  border-radius: 8px;
  transition: all 0.2s ease;
}

.action-btn:hover {
  background: rgba(var(--v-theme-primary), 0.08);
  transform: scale(1.05);
}

.filter-toggle-btn {
  transition: all 0.2s ease;
}

.filter-toggle-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Mobile appointment card styles */
.mobile-appointment-card {
  padding: 0;
}

.mobile-appointment-details {
  margin: 16px 0;
}

.detail-row {
  display: flex;
  align-items: center;
  margin-bottom: 8px;
  color: rgba(var(--v-theme-on-surface), 0.7);
}

.mobile-appointment-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.mobile-appointment-actions .v-btn {
  flex: 1;
  min-width: 80px;
}

/* Responsividade */
@media (max-width: 768px) {
  .action-buttons-container {
    flex-direction: column;
    gap: 8px;
    width: 100%;
  }

  .action-buttons-container .v-btn,
  .action-buttons-container .export-actions {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .mobile-appointment-actions {
    flex-direction: column;
  }

  .mobile-appointment-actions .v-btn {
    width: 100%;
  }
}
</style>
