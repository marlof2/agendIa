import { ref, computed } from 'vue'
import type { Ref } from 'vue'
import { useHttp } from './useHttp'

export interface Tenant {
  id: number
  name: string
  created_at?: string
  is_main_company?: boolean
  profile_id?: number
  profile_name?: string
}

// Estado global do tenant
const currentTenant: Ref<Tenant | null> = ref(null)
const availableTenants: Ref<Tenant[]> = ref([])

// Flag para saber se já inicializou
let initialized = false

export const useTenant = () => {
  /**
   * Define o tenant atual
   */
  const setCurrentTenant = (tenant: Tenant | null) => {
    currentTenant.value = tenant

    if (tenant) {
      // Salva no localStorage para persistir entre sessões
      localStorage.setItem('agendia-current-tenant', JSON.stringify(tenant))
      // Salva o tenant_id para enviar nas requisições
      localStorage.setItem('agendia-tenant-id', tenant.id.toString())
    } else {
      localStorage.removeItem('agendia-current-tenant')
      localStorage.removeItem('agendia-tenant-id')
    }
  }

  /**
   * Define a lista de tenants disponíveis para o usuário
   */
  const setAvailableTenants = (tenants: Tenant[]) => {
    availableTenants.value = tenants
    localStorage.setItem('agendia-available-tenants', JSON.stringify(tenants))
  }

  /**
   * Carrega o tenant atual do localStorage
   */
  const loadCurrentTenant = async (): Promise<Tenant | null> => {
    const stored = localStorage.getItem('agendia-current-tenant')
    if (stored) {
      try {
        const tenant = JSON.parse(stored)
        currentTenant.value = tenant

        // Se temos um tenant carregado mas não temos abilities, carregar
        if (tenant?.profile_id) {
          const abilitiesData = localStorage.getItem('agendia_user_abilities')
          if (!abilitiesData) {
            await updateAbilitiesForProfile(tenant.profile_id)
          }
        }

        return tenant
      } catch (error) {
        console.error('Erro ao carregar tenant do localStorage:', error)
        return null
      }
    }
    return null
  }

  /**
   * Carrega os tenants disponíveis do localStorage
   */
  const loadAvailableTenants = (): Tenant[] => {
    const stored = localStorage.getItem('agendia-available-tenants')
    if (stored) {
      try {
        const tenants = JSON.parse(stored)
        availableTenants.value = tenants
        return tenants
      } catch (error) {
        console.error('Erro ao carregar tenants do localStorage:', error)
        return []
      }
    }
    return []
  }

  /**
   * Limpa os dados do tenant (usado no logout)
   */
  const clearTenant = () => {
    currentTenant.value = null
    availableTenants.value = []
    localStorage.removeItem('agendia-current-tenant')
    localStorage.removeItem('agendia-tenant-id')
    localStorage.removeItem('agendia-available-tenants')
    localStorage.removeItem('agendia-current-profile-id')
    localStorage.removeItem('agendia-current-profile-name')
  }

  /**
   * Troca o tenant atual e atualiza o perfil correspondente
   */
  const switchTenant = async (tenant: Tenant, userCompanies?: any[]) => {
    // Buscar o perfil do usuário para esta empresa
    let tenantWithProfile = { ...tenant }

    if (userCompanies) {
      const companyData = userCompanies.find((c: any) => c.id === tenant.id)
      if (companyData?.pivot) {
        tenantWithProfile.profile_id = companyData.pivot.profile_id
        tenantWithProfile.profile_name = companyData.pivot.profile_name || getProfileNameById(companyData.pivot.profile_id)
      }
    }

    setCurrentTenant(tenantWithProfile)

    // Atualizar abilities se o perfil mudou
    if (tenantWithProfile.profile_id) {
      await updateAbilitiesForProfile(tenantWithProfile.profile_id)
    }

    return true
  }

  /**
   * Função auxiliar para obter nome do perfil por ID
   */
  const getProfileNameById = (profileId: number): string => {
    const profileMap: Record<number, string> = {
      1: 'Administrador',
      2: 'Propietário',
      3: 'Supervisor',
      4: 'Profissional',
      5: 'Cliente'
    }
    return profileMap[profileId] || 'Perfil'
  }

  /**
   * Função para atualizar abilities baseado no perfil
   */
  const updateAbilitiesForProfile = async (profileId: number) => {
    try {
      // Salvar informações do perfil
      localStorage.setItem('agendia-current-profile-id', profileId.toString())
      localStorage.setItem('agendia-current-profile-name', getProfileNameById(profileId))

      // Buscar abilities do perfil
      const { get } = useHttp()
      const response = await get(`/profiles/${profileId}`)

      if (response.success && response.data) {
        const abilities = response.data.abilities || []
        const abilitiesArray = abilities.map((ability: any) => ability.full_name || `${ability.category}.${ability.action}`)

        // Salvar abilities no localStorage
        const abilitiesData = {
          abilities: abilitiesArray,
          profile_id: profileId,
          updated_at: new Date().toISOString()
        }

        // Salvar abilities criptografadas no localStorage
        const { saveWithEncrypted } = await import('@/utils/storage')
        await saveWithEncrypted('agendia_user_abilities', abilitiesData)

        // Recarregar abilities no composable useAbilities
        // Usar importação dinâmica para evitar dependência circular
        const { reloadAbilities } = await import('./useAbilities').then(m => m.useAbilities())
        await reloadAbilities()

      }
    } catch (error) {
      console.error('Erro ao atualizar abilities do perfil:', error)
    }
  }

  /**
   * Verifica se o usuário tem múltiplos tenants
   */
  const hasMultipleTenants = computed(() => {
    return availableTenants.value.length > 1
  })

  /**
   * Verifica se já existe um tenant selecionado
   */
  const hasTenantSelected = computed(() => {
    return currentTenant.value !== null
  })

  /**
   * Retorna o ID do tenant atual (útil para requisições)
   */
  const currentTenantId = computed(() => {
    return currentTenant.value?.id || null
  })

  /**
   * Retorna o perfil atual do usuário
   */
  const getCurrentProfile = () => {
    return {
      id: currentTenant.value?.profile_id || null,
      name: currentTenant.value?.profile_name || null
    }
  }

  /**
   * Retorna o nome do perfil atual
   */
  const getCurrentProfileName = () => {
    return currentTenant.value?.profile_name || null
  }

  /**
   * Inicializa os dados do localStorage (executa apenas uma vez)
   */
  const initialize = async () => {
    if (!initialized) {
      await loadCurrentTenant()
      loadAvailableTenants()
      initialized = true
    }
  }

  // Inicializa automaticamente
  initialize()

  return {
    // Estado
    currentTenant: computed(() => currentTenant.value),
    availableTenants: computed(() => availableTenants.value),
    currentTenantId,
    hasMultipleTenants,
    hasTenantSelected,

    // Métodos
    setCurrentTenant,
    setAvailableTenants,
    loadCurrentTenant,
    loadAvailableTenants,
    clearTenant,
    switchTenant,
    getCurrentProfile,
    getCurrentProfileName,
  }
}

