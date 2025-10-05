import { ref, computed } from 'vue'
import { useHttp } from '@/composables/useHttp'

export interface User {
  id: number
  name: string
  email: string
  phone?: string
  has_whatsapp: boolean
  profile_id?: number
  created_at: string
  updated_at: string
  profile?: Profile
  companies?: Company[]
}

export interface Profile {
  id: number
  name: string
  display_name: string
  description?: string
  created_at: string
  updated_at: string
  abilities: Ability[]
}

export interface Ability {
  id: number
  name: string
  category: string
  action: string
  display_name: string
  description?: string
  created_at: string
  updated_at: string
}

export interface Company {
  id: number
  name: string
  person_type: 'legal' | 'physical'
  cnpj?: string
  cpf?: string
  responsible_name: string
  phone_1?: string
  phone_2?: string
  has_whatsapp_1: boolean
  has_whatsapp_2: boolean
  timezone_id?: number
  created_at: string
  updated_at: string
  timezone?: Timezone
}

export interface Timezone {
  id: number
  region: string
  offset: string
  created_at: string
  updated_at: string
}

export interface CreateUserData {
  name: string
  email: string
  password?: string
  password_confirmation?: string
  phone?: string
  has_whatsapp?: boolean
  profile_id?: number
  company_ids?: number[]
}

export interface UpdateUserData extends Partial<CreateUserData> {
  id: number
}

export interface UserFilters {
  search?: string
  profile_id?: number
  page?: number
  per_page?: number
}

export function useUsersApi() {
  const { get, post, put, del } = useHttp()

  // State
  const items = ref<User[]>([])
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
  const getAll = async (filters: UserFilters = {}) => {
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

      const response = await get(`/users?${params.toString()}`)

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
      error.value = err.response?.data?.message || 'Erro ao carregar usuários'
      throw err
    } finally {
      loading.value = false
    }
  }

  const getById = async (id: number): Promise<User> => {
    loading.value = true
    error.value = null

    try {
      const response = await get(`/users/${id}`)
      return response.data as User
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao carregar usuário'
      throw err
    } finally {
      loading.value = false
    }
  }

  const createItem = async (data: CreateUserData): Promise<User> => {
    loading.value = true
    error.value = null

    try {
      const response = await post('/users', data)

      // Add the new item to the list
      if (response.data) {
        items.value.unshift(response.data)
      }

      return response.data as User
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao criar usuário'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateItem = async (id: number, data: Partial<CreateUserData>): Promise<User> => {
    loading.value = true
    error.value = null

    try {
      const response = await put(`/users/${id}`, data)

      // Update the item in the list
      const index = items.value.findIndex(item => item.id === id)
      if (index !== -1 && response.data) {
        items.value[index] = response.data
      }

      return response.data as User
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao atualizar usuário'
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteItem = async (id: number): Promise<void> => {
    loading.value = true
    error.value = null

    try {
      await del(`/users/${id}`)

      // Remove the item from the list
      const index = items.value.findIndex(item => item.id === id)
      if (index !== -1) {
        items.value.splice(index, 1)
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao excluir usuário'
      throw err
    } finally {
      loading.value = false
    }
  }

  const bulkDelete = async (ids: number[]): Promise<void> => {
    loading.value = true
    error.value = null

    try {
      await del('/users/bulk')

      // Remove items from the list
      items.value = items.value.filter(item => !ids.includes(item.id))
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao excluir usuários'
      throw err
    } finally {
      loading.value = false
    }
  }


  const clearError = () => {
    error.value = null
  }

  const refresh = async (filters: UserFilters = {}) => {
    return getAll(filters)
  }

  // Combo function for autoselects
  const getCombo = async (search?: string, profileId?: number) => {
    try {
      const params = new URLSearchParams();
      if (search) {
        params.append('search', search);
      }
      if (profileId) {
        params.append('profile_id', profileId.toString());
      }

      const url = params.toString()
        ? `combos/users?${params.toString()}`
        : 'combos/users';

      const response = await get(url);
      return response.data || [];
    } catch (err: any) {
      console.error('Erro ao carregar usuários para combo:', err);
      return [];
    }
  };


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

    // Combo method
    getCombo,
  }
}
