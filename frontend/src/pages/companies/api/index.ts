import { ref, computed } from 'vue'
import { useHttp } from '@/composables/useHttp'

export interface Timezone {
  id: number
  name: string
  region: string
  offset: string
  created_at: string
  updated_at: string
}

export interface Company {
  id: number
  name: string
  person_type: 'physical' | 'legal'
  cnpj?: string
  cpf?: string
  responsible_name: string
  phone_1: string
  has_whatsapp_1: boolean
  phone_2?: string
  has_whatsapp_2: boolean
  timezone_id?: number
  timezone?: Timezone
  created_at: string
  updated_at: string
}

export interface CreateCompanyData {
  name: string
  person_type: 'physical' | 'legal'
  cnpj?: string
  cpf?: string
  responsible_name: string
  phone_1: string
  has_whatsapp_1: boolean
  phone_2?: string
  has_whatsapp_2: boolean
  timezone_id?: number
}

export interface UpdateCompanyData extends Partial<CreateCompanyData> {
  id: number
}

export interface CompanyFilters {
  search?: string
  page?: number
  per_page?: number
}

export function useCompaniesApi() {
  const { get, post, put, del, patch } = useHttp()

  // State
  const items = ref<Company[]>([])
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
  const getAll = async (filters: CompanyFilters = {}) => {
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

      const response = await get(`/companies?${params.toString()}`)

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
      error.value = err.response?.data?.message || 'Erro ao carregar empresas'
      throw err
    } finally {
      loading.value = false
    }
  }

  const getById = async (id: number): Promise<Company> => {
    loading.value = true
    error.value = null

    try {
      const response = await get(`/companies/${id}`)
      return response.data as Company
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao carregar empresa'
      throw err
    } finally {
      loading.value = false
    }
  }

  const createItem = async (data: CreateCompanyData): Promise<Company> => {
    loading.value = true
    error.value = null

    try {
      const response = await post('/companies', data)

      // Add the new item to the list
      if (response.data) {
        items.value.unshift(response.data)
      }

      return response.data as Company
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao criar empresa'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateItem = async (id: number, data: Partial<CreateCompanyData>): Promise<Company> => {
    loading.value = true
    error.value = null

    try {
      const response = await put(`/companies/${id}`, data)

      // Update the item in the list
      const index = items.value.findIndex(item => item.id === id)
      if (index !== -1 && response.data) {
        items.value[index] = response.data
      }

      return response.data as Company
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao atualizar empresa'
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteItem = async (id: number): Promise<void> => {
    loading.value = true
    error.value = null

    try {
      await del(`/companies/${id}`)

      // Remove the item from the list
      const index = items.value.findIndex(item => item.id === id)
      if (index !== -1) {
        items.value.splice(index, 1)
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao excluir empresa'
      throw err
    } finally {
      loading.value = false
    }
  }

  const bulkDelete = async (ids: number[]): Promise<void> => {
    loading.value = true
    error.value = null

    try {
      await del('/companies/bulk')

      // Remove items from the list
      items.value = items.value.filter(item => !ids.includes(item.id))
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao excluir empresas'
      throw err
    } finally {
      loading.value = false
    }
  }

  const getAllTimezones = async (): Promise<Timezone[]> => {
    try {
      const response = await get('/timezones')
      return response.data as Timezone[]
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao carregar fusos horários'
      throw err
    }
  }

  const clearError = () => {
    error.value = null
  }

  const refresh = async (filters: CompanyFilters = {}) => {
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
        ? `combos/companies?${params.toString()}`
        : 'combos/companies';

      const response = await get(url);
      return response.data || [];
    } catch (err: any) {
      console.error('Erro ao carregar empresas para combo:', err);
      return [];
    }
  };

  // Get users for a specific company
  const getCompanyUsers = async (companyId: number, search?: string, page: number = 1, perPage: number = 12) => {
    try {
      const params = new URLSearchParams();
      if (search) {
        params.append('search', search);
      }
      params.append('page', page.toString());
      params.append('per_page', perPage.toString());

      const url = `companies/${companyId}/professionals?${params.toString()}`;
      const response = await get(url);
      return response ;
    } catch (err: any) {
      console.error('Erro ao carregar usuários da empresa:', err);
      return {
        data: [],
        current_page: 1,
        per_page: 12,
        total: 0,
        last_page: 1,
        from: 0,
        to: 0
      };
    }
  };

  // Get available professionals for a company (not associated yet)
  const getAvailableUsersForCompany = async (companyId: number, search?: string, page: number = 1, perPage: number = 12) => {
    try {
      const params = new URLSearchParams();
      if (search) {
        params.append('search', search);
      }
      params.append('company_id', companyId.toString());
      params.append('page', page.toString());
      params.append('per_page', perPage.toString());

      const url = `users/available-professionals?${params.toString()}`;
      const response = await get(url);
      return response || {};
    } catch (err: any) {
      console.error('Erro ao carregar profissionais disponíveis:', err);
      return {
        data: [],
        current_page: 1,
        per_page: 12,
        total: 0,
        last_page: 1
      };
    }
  };

  // Attach professional to company
  const attachProfessional = async (companyId: number, userId: number) => {
    try {
      const response = await post(`companies/${companyId}/professionals/${userId}`);
      return response.data;
    } catch (err: any) {
      console.error('Erro ao associar profissional:', err);
      throw err;
    }
  };

  // Detach professional from company
  const detachProfessional = async (companyId: number, userId: number) => {
    try {
      const response = await del(`companies/${companyId}/professionals/${userId}`);
      return response.data;
    } catch (err: any) {
      console.error('Erro ao desassociar profissional:', err);
      throw err;
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
    getAllTimezones,
    clearError,
    refresh,

    // Combo methods
    getCombo,
    getCompanyUsers,
    getAvailableUsersForCompany,

    // Professional management
    attachProfessional,
    detachProfessional
  }
}
