<template>
  <BaseDialog
    v-model="isOpen"
    title="Detalhes do Agendamento"
    subtitle="Visualize as informações do agendamento"
    icon="mdi-eye"
    icon-color="primary"
    max-width="800px"
    :fullscreen="$vuetify.display.mobile"
    @close="closeModal"
  >

    <div v-if="appointment" class="appointment-details">
      <!-- Informações principais simplificadas -->
      <div class="mb-6">
        <div class="d-flex align-center mb-4">
          <v-avatar :color="getStatusColor(appointment.status)" size="48" class="mr-4">
            <v-icon :icon="getStatusIcon(appointment.status)" color="white" />
          </v-avatar>
          <div>
            <h3 class="text-h5 font-weight-bold mb-1">
              {{ appointment.client || 'Cliente' }}
            </h3>
            <v-chip
              :color="getStatusColor(appointment.status)"
              size="small"
              variant="flat"
              class="font-weight-medium"
            >
              {{ appointment.status }}
            </v-chip>
          </div>
        </div>
      </div>

      <!-- Dados essenciais -->
      <v-row>
        <v-col cols="12" md="6">
          <div class="mb-4">
            <div class="text-subtitle-2 text-medium-emphasis mb-1">Data e Hora</div>
            <div class="text-h6 font-weight-bold">
              {{ formatDate(appointment.date) }} às {{ appointment.time }}
            </div>
          </div>
        </v-col>

        <v-col cols="12" md="6">
          <div class="mb-4">
            <div class="text-subtitle-2 text-medium-emphasis mb-1">Profissional</div>
            <div class="text-h6 font-weight-bold">
              {{ appointment.professional || 'Profissional' }}
            </div>
          </div>
        </v-col>

        <v-col cols="12" md="6">
          <div class="mb-4">
            <div class="text-subtitle-2 text-medium-emphasis mb-1">Serviço</div>
            <div class="text-h6 font-weight-bold">
              {{ appointment.service || 'Serviço' }}
            </div>
          </div>
        </v-col>

        <v-col cols="12" md="6">
          <div class="mb-4">
            <div class="text-subtitle-2 text-medium-emphasis mb-1">Valor</div>
            <div class="text-h6 font-weight-bold text-success">
              R$ {{ appointment.price || '0,00' }}
            </div>
          </div>
        </v-col>

        <v-col cols="12" v-if="appointment.notes">
          <div class="mb-4">
            <div class="text-subtitle-2 text-medium-emphasis mb-1">Observações</div>
            <div class="text-body-1">
              {{ appointment.notes }}
            </div>
          </div>
        </v-col>
      </v-row>
    </div>

    <template #actions>
      <v-spacer />
      <div class="d-flex modal-actions-container">
        <v-btn
          color="secondary"
          variant="outlined"
          rounded="lg"
          class="text-none font-weight-medium px-6 mr-4"
          size="large"
          @click="closeModal"
        >
          <v-icon icon="mdi-close" class="mr-2" />
          Fechar
        </v-btn>
        <v-btn
          color="warning"
          rounded="lg"
          class="text-none font-weight-medium px-6"
          size="large"
          @click="handleEdit"
        >
          <v-icon icon="mdi-pencil" class="mr-2" />
          Editar Agendamento
        </v-btn>
      </div>
    </template>
  </BaseDialog>
</template>

<script lang="ts" setup>
import { ref, computed } from 'vue'
import BaseDialog from '@/components/BaseDialog.vue'

interface Appointment {
  id?: number | string
  client?: string
  phone?: string
  professional?: string
  specialty?: string
  service?: string
  duration?: string
  price?: string
  date?: string
  time?: string
  status?: string
  notes?: string
  created_at?: string
  updated_at?: string
}

interface Props {
  modelValue: boolean
  appointment?: Appointment | null
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  'edit': [appointment: Appointment]
}>()

// Computed
const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

// Methods
const closeModal = () => {
  isOpen.value = false
}

const handleEdit = () => {
  if (props.appointment) {
    emit('edit', props.appointment)
  }
  closeModal()
}

const getStatusColor = (status?: string) => {
  const statusColors: Record<string, string> = {
    'agendado': 'primary',
    'confirmado': 'success',
    'em_andamento': 'warning',
    'concluido': 'success',
    'cancelado': 'error',
    'reagendado': 'info'
  }
  return statusColors[status || 'agendado'] || 'primary'
}

const getStatusIcon = (status?: string) => {
  const statusIcons: Record<string, string> = {
    'agendado': 'mdi-calendar-clock',
    'confirmado': 'mdi-check-circle',
    'em_andamento': 'mdi-clock',
    'concluido': 'mdi-check',
    'cancelado': 'mdi-close-circle',
    'reagendado': 'mdi-calendar-refresh'
  }
  return statusIcons[status || 'agendado'] || 'mdi-calendar-clock'
}

const formatDate = (date?: string) => {
  if (!date) return 'Data não informada'
  return new Date(date).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}
</script>

<style scoped>
.appointment-details {
  padding: 0;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
  .appointment-details {
    padding: 0;
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
