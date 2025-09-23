import { ref, computed } from 'vue'
import { useHttp } from '@/composables/useHttp'

export interface Appointment {
  id: number | string
  client_id: number | string
  professional_id: number | string
  service_id?: number | string
  date: string
  start_time: string
  end_time: string
  status: 'scheduled' | 'confirmed' | 'cancelled' | 'completed'
  notes?: string
  created_at?: string
  updated_at?: string
  client?: {
    id: number | string
    name: string
    email: string
    phone: string
  }
  professional?: {
    id: number | string
    name: string
    email: string
  }
  service?: {
    id: number | string
    name: string
    duration: number
    price: number
  }
}

export interface Filters {
  date?: string
  date_from?: string
  date_to?: string
  professional_id?: number | string
  client_id?: number | string
  status?: string
  page?: number
  per_page?: number
}

export interface CreateData {
  client_id: number | string
  professional_id: number | string
  service_id?: number | string
  date: string
  start_time: string
  end_time: string
  notes?: string
}

export function useAppointmentsApi() {
  const { get, post, put, del } = useHttp()
  const loading = ref(false)
  const error = ref<string | null>(null)
  const items = ref<Appointment[]>([])
  const currentItem = ref<Appointment | null>(null)
  const url = '/appointments'


  // Buscar todos os agendamentos com filtros opcionais
  const getAll = async (filters?: Filters) => {
    try {
      loading.value = true
      error.value = null
      const response = await get(url, { params: filters })
      items.value = response.data || []
      return response
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao carregar agendamentos'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Buscar agendamento por ID
  const getById = async (id: string | number) => {
    try {
      loading.value = true
      error.value = null
      const response = await get(`${url}/${id}`)
      currentItem.value = response.data
      return response
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao carregar agendamento'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Criar novo agendamento
  const createItem = async (data: CreateData) => {
    try {
      loading.value = true
      error.value = null
      const response = await post(url, data)

      // Adicionar o novo agendamento à lista local
      if (response.data) {
        items.value.unshift(response.data)
      }

      return response
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao criar agendamento'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Atualizar agendamento
  const updateItem = async (id: string | number, data: Partial<CreateData>) => {
    try {
      loading.value = true
      error.value = null
      const response = await put(`${url}/${id}`, data)

      // Atualizar o agendamento na lista local
      if (response.data) {
        const index = items.value.findIndex(apt => apt.id === id)
        if (index !== -1) {
          items.value[index] = response.data
        }

        // Atualizar também o agendamento atual se for o mesmo
        if (currentItem.value?.id === id) {
          currentItem.value = response.data
        }
      }

      return response
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao atualizar agendamento'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Excluir agendamento
  const deleteItem = async (id: string | number) => {
    try {
      loading.value = true
      error.value = null
      const response = await del(`${url}/${id}`)

      // Remover o agendamento da lista local
      items.value = items.value.filter(apt => apt.id !== id)

      // Limpar o agendamento atual se for o mesmo
      if (currentItem.value?.id === id) {
        currentItem.value = null
      }

      return response
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao excluir agendamento'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Cancelar agendamento (mudança de status)
  const cancelAppointment = async (id: string | number, reason?: string) => {
    try {
      loading.value = true
      error.value = null
      const response = await put(`${url}/${id}/cancel`, { reason })

      // Atualizar o status na lista local
      const appointment = items.value.find(apt => apt.id === id)
      if (appointment) {
        appointment.status = 'cancelled'
      }

      // Atualizar também o agendamento atual se for o mesmo
      if (currentItem.value?.id === id) {
        currentItem.value.status = 'cancelled'
      }

      return response
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao cancelar agendamento'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Confirmar agendamento (mudança de status)
  const confirmAppointment = async (id: string | number) => {
    try {
      loading.value = true
      error.value = null
      const response = await put(`${url}/${id}/confirm`)

      // Atualizar o status na lista local
      const appointment = items.value.find(apt => apt.id === id)
      if (appointment) {
        appointment.status = 'confirmed'
      }

      // Atualizar também o agendamento atual se for o mesmo
      if (currentItem.value?.id === id) {
        currentItem.value.status = 'confirmed'
      }

      return response
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao confirmar agendamento'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Marcar como concluído
  const completeAppointment = async (id: string | number) => {
    try {
      loading.value = true
      error.value = null
      const response = await put(`${url}/${id}/complete`)

      // Atualizar o status na lista local
      const appointment = items.value.find(apt => apt.id === id)
      if (appointment) {
        appointment.status = 'completed'
      }

      // Atualizar também o agendamento atual se for o mesmo
      if (currentItem.value?.id === id) {
        currentItem.value.status = 'completed'
      }

      return response
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao marcar agendamento como concluído'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Buscar agendamentos por período (útil para calendário)
  const fetchAppointmentsByPeriod = async (startDate: string, endDate: string, professionalId?: string | number) => {
    try {
      loading.value = true
      error.value = null
      const filters: Filters = {
        date_from: startDate,
        date_to: endDate,
        ...(professionalId && { professional_id: professionalId })
      }
      const response = await get(url, { params: filters })
      items.value = response.data || []
      return response
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao carregar agendamentos do período'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Verificar disponibilidade de horário
  const checkAvailability = async (professionalId: string | number, date: string, startTime: string, endTime: string, excludeId?: string | number) => {
    try {
      loading.value = true
      error.value = null
      const params: any = {
        professional_id: professionalId,
        date,
        start_time: startTime,
        end_time: endTime
      }

      if (excludeId) {
        params.exclude_id = excludeId
      }

      const response = await get(`${url}/check-availability`, { params })
      return response
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao verificar disponibilidade'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Limpar dados locais
  const clearData = () => {
    items.value = []
    currentItem.value = null
    error.value = null
  }

  // Resetar loading e error
  const resetState = () => {
    loading.value = false
    error.value = null
  }

  return {
    // Estados
    loading,
    error,
    items,
    currentItem,

    // Funções de API CRUD básicas (padronizadas)
    getAll,
    getById,
    createItem,
    updateItem,
    deleteItem,

    // Funções específicas de agendamentos
    cancelAppointment,
    confirmAppointment,
    completeAppointment,
    fetchAppointmentsByPeriod,
    checkAvailability,

    // Utilitários
    clearData,
    resetState
  }
}
