import { ref, computed } from 'vue'
import { useHttp } from '@/composables/useHttp'

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

export interface CreateProfileData {
  name: string
  display_name: string
  description?: string
  abilities?: number[]
}

export interface UpdateProfileData extends Partial<CreateProfileData> {
  id: number
}

export interface ProfileFilters {
  search?: string
  page?: number
  per_page?: number
}

export function useProfilesApi() {
  const { get, post, put, del, patch } = useHttp()

  // State
  const items = ref<Profile[]>([])
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
  const getAll = async (filters: ProfileFilters = {}) => {
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

      const response = await get(`/profiles?${params.toString()}`)

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
      error.value = err.response?.data?.message || 'Erro ao carregar perfis'
      throw err
    } finally {
      loading.value = false
    }
  }

  const getById = async (id: number): Promise<Profile> => {
    loading.value = true
    error.value = null

    try {
      const response = await get(`/profiles/${id}`)
      return response.data as Profile
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao carregar perfil'
      throw err
    } finally {
      loading.value = false
    }
  }

  const createItem = async (data: CreateProfileData): Promise<Profile> => {
    loading.value = true
    error.value = null

    try {
      const response = await post('/profiles', data)

      // Add the new item to the list
      if (response.data) {
        items.value.unshift(response.data)
      }

      return response.data as Profile
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao criar perfil'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateItem = async (id: number, data: Partial<CreateProfileData>): Promise<Profile> => {
    loading.value = true
    error.value = null

    try {
      const response = await put(`/profiles/${id}`, data)

      // Update the item in the list
      const index = items.value.findIndex(item => item.id === id)
      if (index !== -1 && response.data) {
        items.value[index] = response.data
      }

      return response.data as Profile
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao atualizar perfil'
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteItem = async (id: number): Promise<void> => {
    loading.value = true
    error.value = null

    try {
      await del(`/profiles/${id}`)

      // Remove the item from the list
      const index = items.value.findIndex(item => item.id === id)
      if (index !== -1) {
        items.value.splice(index, 1)
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao excluir perfil'
      throw err
    } finally {
      loading.value = false
    }
  }

  const bulkDelete = async (ids: number[]): Promise<void> => {
    loading.value = true
    error.value = null

    try {
      await del('/profiles/bulk')

      // Remove items from the list
      items.value = items.value.filter(item => !ids.includes(item.id))
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao excluir perfis'
      throw err
    } finally {
      loading.value = false
    }
  }


  const updateAbilities = async (id: number, abilities: number[]): Promise<Profile> => {
    loading.value = true
    error.value = null

    try {
      const response = await patch(`/profiles/${id}/abilities`, { abilities })

      // Update the item in the list
      const index = items.value.findIndex(item => item.id === id)
      if (index !== -1) {
        items.value[index]!.abilities = response.data.abilities
        items.value[index] = { ...items.value[index]! }
      }

      return response.data as Profile
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao atualizar abilities do perfil'
      throw err
    } finally {
      loading.value = false
    }
  }


  const getAllAbilities = async (): Promise<Ability[]> => {
    try {
      const response = await get('/profiles/abilities/all')
      return response.data as Ability[]
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao carregar abilities'
      throw err
    }
  }



  const clearError = () => {
    error.value = null
  }

  const refresh = async (filters: ProfileFilters = {}) => {
    return getAll(filters)
  }

  // Combo function for autoselects
  const getCombo = async (search?: string) => {
    try {
      const params = new URLSearchParams();
      if (search) {
        params.append('search', search);
      }

      const url = params.toString()
        ? `combos/profiles?${params.toString()}`
        : 'combos/profiles';

      const response = await get(url);
      return response.data || [];
    } catch (err: any) {
      console.error('Erro ao carregar perfis para combo:', err);
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
    updateAbilities,
    getAllAbilities,
    clearError,
    refresh,

    // Combo method
    getCombo
  }
}
