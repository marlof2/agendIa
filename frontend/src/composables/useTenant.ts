import { ref, computed } from 'vue'
import type { Ref } from 'vue'

export interface Tenant {
  id: number
  name: string
  slug: string
  logo?: string
  created_at?: string
}

// Estado global do tenant
const currentTenant: Ref<Tenant | null> = ref(null)
const availableTenants: Ref<Tenant[]> = ref([])

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
  const loadCurrentTenant = (): Tenant | null => {
    const stored = localStorage.getItem('agendia-current-tenant')
    if (stored) {
      try {
        const tenant = JSON.parse(stored)
        currentTenant.value = tenant
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
  }

  /**
   * Troca o tenant atual
   */
  const switchTenant = async (tenant: Tenant) => {
    setCurrentTenant(tenant)
    // Você pode adicionar lógica adicional aqui, como recarregar dados
    return true
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
  }
}

