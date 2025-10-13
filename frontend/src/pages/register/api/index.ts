import { ref } from 'vue'
import { useHttp } from '@/composables/useHttp'

export interface RegisterData {
  // Dados pessoais
  name: string
  email: string
  password: string
  password_confirmation: string
  phone?: string
  has_whatsapp?: boolean

  // Tipo de conta e perfil
  account_type: 'owner' | 'professional' | 'supervisor' | 'client'
  profile_id: number | null

  // Dados da empresa (apenas para proprietários)
  company?: {
    name: string
    person_type: 'physical' | 'legal'
    cnpj?: string
    cpf?: string
    responsible_name: string
    phone_1: string
    has_whatsapp_1?: boolean
    phone_2?: string
    has_whatsapp_2?: boolean
    timezone_id?: number
  }

  // IDs das empresas (para outros perfis)
  company_ids?: number[]
}

export const useRegisterApi = () => {
  const http = useHttp()
  const loading = ref(false)
  const error = ref<string | null>(null)
  const companies = ref<any[]>([])
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 12,
    total: 0
  })

  /**
   * Registrar novo usuário
   */
  const register = async (data: RegisterData) => {
    loading.value = true
    error.value = null

    try {
      const response = await http.post('/register', data)
      return response
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao criar conta'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Listar empresas públicas com paginação
   */
  const getPublicCompanies = async (params: { search?: string; page?: number; per_page?: number } = {}) => {
    loading.value = true
    error.value = null

    try {
      const queryParams = new URLSearchParams()
      if (params.search) queryParams.append('search', params.search)
      if (params.page) queryParams.append('page', params.page.toString())
      if (params.per_page) queryParams.append('per_page', params.per_page.toString())

      const url = queryParams.toString()
        ? `/companies/public?${queryParams.toString()}`
        : '/companies/public'

      const response = await http.get(url)

      companies.value = response.data
      pagination.value = {
        current_page: response.current_page || 1,
        last_page: response.last_page || 1,
        per_page: response.per_page || 12,
        total: response.total || 0
      }
      return response
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao buscar empresas'
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    loading,
    error,
    companies,
    pagination,
    register,
    getPublicCompanies
  }
}

