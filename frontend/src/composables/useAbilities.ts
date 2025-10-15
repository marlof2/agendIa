import { ref, computed } from "vue";
import {
  saveWithEncrypted,
  loadWithDecrypt,
  clearFromStorage,
} from "@/utils/storage";

interface UserPermissions {
  abilities: string[];
}

// Constantes específicas para permissions
const ABILITIES_STORAGE_KEY = 'agendia_user_abilities'

export function useAbilities() {
  const userPermissions = ref<UserPermissions>({
    abilities: [],
  });

  // Computed para verificar se o usuário tem uma ability específica
  const hasPermission = computed(() => {
    // Força reatividade quando userPermissions muda
    return (ability: string): boolean => {
      return userPermissions.value.abilities.includes(ability);
    };
  });

  // Função principal para carregar permissions do usuário
  const getUserPermissions = async (): Promise<void> => {
    try {
      const permissions = await loadWithDecrypt(ABILITIES_STORAGE_KEY);

      if (permissions) {
        userPermissions.value = permissions;
        return;
      }

    } catch (error) {
      console.log("Erro ao carregar permissions do usuário", error);
    }
  };

  // Inicializar abilities automaticamente
  getUserPermissions();

  // Função para limpar permissions do cache
  const clearAbilitiesCache = () => {
    clearFromStorage(ABILITIES_STORAGE_KEY);
    userPermissions.value = {
      abilities: [],
    };
  };

  // Função para recarregar abilities do localStorage
  const reloadAbilities = async () => {
    try {
      const abilities = await loadWithDecrypt(ABILITIES_STORAGE_KEY);
      if (abilities) {
        userPermissions.value = abilities;
      }
    } catch (error) {
      console.error('Erro ao recarregar abilities:', error);
    }
  };






  return {
    userPermissions,
    hasPermission,
    getUserPermissions,
    clearAbilitiesCache,
    reloadAbilities,
  };
}
