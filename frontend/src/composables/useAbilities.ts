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
    loadWithDecrypt(ABILITIES_STORAGE_KEY).then((value) => {
      userPermissions.value = value;
    });
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

  // Função para limpar permissions do cache
  const clearAbilitiesCache = () => {
    clearFromStorage(ABILITIES_STORAGE_KEY);
    userPermissions.value = {
      abilities: [],
    };
  };





  return {
    userPermissions,
    hasPermission,
    getUserPermissions,
    clearAbilitiesCache,
  };
}
