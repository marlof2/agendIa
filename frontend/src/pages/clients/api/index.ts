import { ref, computed } from 'vue'
import { useHttp } from '@/composables/useHttp'

export interface Client {
  id: number
  name: string
  email: string
  phone?: string
  profile_id: number
  profile: {
    id: number
    name: string
    display_name: string
  }
  companies: any[]
  created_at: string
  updated_at: string
}

export interface CreateClientData {
  name: string
  email: string
  phone?: string
  password: string
}

export interface UpdateClientData extends Partial<CreateClientData> {
  id: number
}

export interface ClientFilters {
  search?: string
  page?: number
  per_page?: number
}

export function useClientsApi() {
  const { get, post, put, del } = useHttp()

  // State
  const items = ref<Client[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 12,
    total: 0
  })

  // Computed
  const hasItems = computed(() => items.value.length > 0)
  const isEmpty = computed(() => !loading.value && items.value.length === 0)

  // Methods
  const getAll = async (filters: ClientFilters = {}) => {
    loading.value = true
    error.value = null

    try {
      const params = new URLSearchParams()
      // Always add pagination parameters
      const page = filters.page || pagination.value.current_page || 1
      const perPage = filters.per_page || pagination.value.per_page || 12

      params.append('page', page.toString())
      params.append('per_page', perPage.toString())

      // Add other filters to params
      Object.entries(filters).forEach(([key, value]) => {
        if (key !== 'page' && key !== 'per_page' && value !== undefined && value !== null && value !== '') {
          params.append(key, value.toString())
        }
      })

      const response = await get(`/clients?${params.toString()}`)

      // Handle direct Laravel pagination response
      items.value = response.data || []
      pagination.value = {
        current_page: response.current_page || 1,
        last_page: response.last_page || 1,
        per_page: response.per_page || 12,
        total: response.total || 0
      }

      return response
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao carregar clientes'
      throw err
    } finally {
      loading.value = false
    }
  }

  const getById = async (id: number): Promise<Client> => {
    loading.value = true
    error.value = null

    try {
      const response = await get(`/clients/${id}`)
      return response.data as Client
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao carregar cliente'
      throw err
    } finally {
      loading.value = false
    }
  }

  const createItem = async (data: CreateClientData): Promise<Client> => {
    loading.value = true
    error.value = null

    try {
      const response = await post('/clients', data)

      // Add the new item to the list
      if (response.data) {
        items.value.unshift(response.data)
      }

      return response.data as Client
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao criar cliente'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateItem = async (id: number, data: Partial<CreateClientData>): Promise<Client> => {
    loading.value = true
    error.value = null

    try {
      const response = await put(`/clients/${id}`, data)

      // Update the item in the list
      const index = items.value.findIndex(item => item.id === id)
      if (index !== -1 && response.data) {
        items.value[index] = response.data
      }

      return response.data as Client
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao atualizar cliente'
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteItem = async (id: number): Promise<void> => {
    loading.value = true
    error.value = null

    try {
      await del(`/clients/${id}`)

      // Remove the item from the list
      const index = items.value.findIndex(item => item.id === id)
      if (index !== -1) {
        items.value.splice(index, 1)
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao excluir cliente'
      throw err
    } finally {
      loading.value = false
    }
  }

  const bulkDelete = async (ids: number[]): Promise<void> => {
    loading.value = true
    error.value = null

    try {
      await del('/clients/bulk')

      // Remove items from the list
      items.value = items.value.filter(item => !ids.includes(item.id))
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao excluir clientes'
      throw err
    } finally {
      loading.value = false
    }
  }

  const clearError = () => {
    error.value = null
  }

  const refresh = async (filters: ClientFilters = {}) => {
    return getAll(filters)
  }

  return {
    // State
    items,
    loading,
    error,
    pagination,

    // Computed
    hasItems,
    isEmpty,

    // Methods
    getAll,
    getById,
    createItem,
    updateItem,
    deleteItem,
    bulkDelete,
    clearError,
    refresh,
  }
}
