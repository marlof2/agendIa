import { ref } from 'vue'
import { useHttp } from '@/composables/useHttp'

export interface RegisterData {
  // Dados pessoais
  name: string
  email: string
  password: string
  password_confirmation: string
  phone?: string
  cpf?: string
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

  // IDs das empresas (removido - associação será feita após login)
}

export const useRegisterApi = () => {
  const http = useHttp()
  const loading = ref(false)
  const error = ref<string | null>(null)

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

  return {
    loading,
    error,
    register
  }
}

