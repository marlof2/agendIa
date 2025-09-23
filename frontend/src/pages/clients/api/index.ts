import { ref } from 'vue'
import { useHttp } from '@/composables/useHttp'

export interface Client {
  id: number | string
  name: string
  email: string
  phone: string
  cpf?: string
  birth_date?: string
  status?: string
  source?: string
  address?: {
    street?: string
    number?: string
    complement?: string
    neighborhood?: string
    city?: string
    state?: string
    zip_code?: string
  }
  notes?: string
  created_at?: string
  updated_at?: string
}

export interface Filters {
  name?: string
  email?: string
  phone?: string
  cpf?: string
  status?: string
  source?: string
  page?: number
  per_page?: number
}

export interface CreateData {
  name: string
  email: string
  phone: string
  cpf?: string
  birth_date?: string
  status?: string
  source?: string
  address?: {
    street?: string
    number?: string
    complement?: string
    neighborhood?: string
    city?: string
    state?: string
    zip_code?: string
  }
  notes?: string
}

export function useClientsApi() {
  const { get, post, put, del } = useHttp()
  const loading = ref(false)
  const error = ref<string | null>(null)
  const clients = ref<Client[]>([])
  const currentClient = ref<Client | null>(null)
  const url = '/clients'

  // Buscar todos os clientes com filtros opcionais
  const getAll = async (filters?: Filters) => {
    try {
      loading.value = true
      error.value = null
      const response = await get(url, { params: filters })
      clients.value = response.data || []
      return response
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao carregar clientes'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Buscar cliente por ID
  const getById = async (id: string | number) => {
    try {
      loading.value = true
      error.value = null
      const response = await get(`${url}/${id}`)
      currentClient.value = response.data
      return response
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao carregar cliente'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Criar novo cliente
  const createItem = async (data: CreateData) => {
    try {
      loading.value = true
      error.value = null
      const response = await post(url, data)

      // Adicionar o novo cliente à lista local
      if (response.data) {
        clients.value.unshift(response.data)
      }

      return response
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao criar cliente'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Atualizar cliente
  const updateItem = async (id: string | number, data: Partial<CreateData>) => {
    try {
      loading.value = true
      error.value = null
      const response = await put(`${url}/${id}`, data)

      // Atualizar o cliente na lista local
      if (response.data) {
        const index = clients.value.findIndex(client => client.id === id)
        if (index !== -1) {
          clients.value[index] = response.data
        }

        // Atualizar também o cliente atual se for o mesmo
        if (currentClient.value?.id === id) {
          currentClient.value = response.data
        }
      }

      return response
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao atualizar cliente'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Excluir cliente
  const deleteItem = async (id: string | number) => {
    try {
      loading.value = true
      error.value = null
      const response = await del(`${url}/${id}`)

      // Remover o cliente da lista local
      clients.value = clients.value.filter(client => client.id !== id)

      // Limpar o cliente atual se for o mesmo
      if (currentClient.value?.id === id) {
        currentClient.value = null
      }

      return response
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao excluir cliente'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Limpar dados locais
  const clearData = () => {
    clients.value = []
    currentClient.value = null
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
    clients,
    currentClient,

    // Funções de API CRUD básicas
    getAll,
    getById,
    createItem,
    updateItem,
    deleteItem,

    // Utilitários
    clearData,
    resetState
  }
}
