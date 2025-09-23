import { ref, computed } from "vue";
import { useAuth } from "./useAuth";
import {
  saveWithEncrypted,
  loadWithDecrypt,
  clearFromStorage,
} from "@/utils/storage";
import { showInfoToast, showErrorToast, showWarningToast } from "@/utils/swal";

interface UserAbilities {
  abilities: string[];
}

// Constantes específicas para abilities
const ABILITIES_STORAGE_KEY = 'agendia_user_abilities'

export function useAbilities() {
  const { user } = useAuth();
  const userAbilities = ref<UserAbilities>({
    abilities: [],
  });

  // Computed para verificar se o usuário tem uma ability específica
  const hasAbility = computed(() => {
    return (ability: string): boolean => {
      if (!user.value) {
        return false;
      }
      return userAbilities.value.abilities.includes(ability);
    };
  });

  // Função auxiliar para carregar abilities do cache
  const loadAbilitiesFromCache = async (): Promise<UserAbilities | null> => {
    try {
      return await loadWithDecrypt(ABILITIES_STORAGE_KEY);
    } catch (error) {
      showErrorToast("Erro ao carregar abilities do cache", "Sistema");
      return null;
    }
  };

  // Função auxiliar para carregar abilities do servidor
  const loadAbilitiesFromServer = async (): Promise<UserAbilities | null> => {
    if (!user.value?.id) {
      return null;
    }
    showInfoToast("Carregando abilities do servidor", "Sistema");
    try {
      const { useHttp } = await import("./useHttp");
      const { get } = useHttp();

      const response = await get(`/users/${user.value.id}/abilities`);

      if (response.success) {
        return {
          abilities: response.data.abilities || [],
        };
      }

      return null;
    } catch (error) {
      showErrorToast("Erro ao carregar abilities do servidor", "Sistema");
      return null;
    }
  };

  // Função auxiliar para salvar abilities no cache
  const saveAbilitiesToCache = async (abilities: UserAbilities): Promise<void> => {
    try {
      await saveWithEncrypted(ABILITIES_STORAGE_KEY, abilities);
    } catch (error) {
      showErrorToast("Erro ao salvar abilities no cache", "Sistema");
    }
  };

  // Função principal para carregar abilities do usuário
  const loadUserAbilities = async (): Promise<void> => {
    if (!user.value) {
      return;
    }

    try {
      // Tentar carregar do cache primeiro
      const cachedAbilities = await loadAbilitiesFromCache();

      if (cachedAbilities) {
        userAbilities.value = cachedAbilities;
        return;
      }

      // Se não há cache, carregar do servidor
      const serverAbilities = await loadAbilitiesFromServer();

      if (serverAbilities) {
        userAbilities.value = serverAbilities;
        await saveAbilitiesToCache(serverAbilities);
        return;
      }

      // Se chegou aqui, não conseguiu carregar de nenhuma fonte
      showWarningToast("Não foi possível carregar abilities do usuário", "Sistema");

    } catch (error) {
      showErrorToast("Erro geral ao carregar abilities", "Sistema");

      // Fallback: tentar carregar do cache como último recurso
      const fallbackAbilities = await loadAbilitiesFromCache();
      if (fallbackAbilities) {
        showInfoToast("Usando abilities do cache como fallback", "Sistema");
        userAbilities.value = fallbackAbilities;
      }
    }
  };

  // Função para limpar abilities do cache
  const clearAbilitiesCache = () => {
    clearFromStorage(ABILITIES_STORAGE_KEY);
    userAbilities.value = {
      abilities: [],
    };
  };

  // Funções específicas para abilities (wrappers das funções genéricas)
  const saveAbilitiesToStorage = async (abilities: UserAbilities): Promise<void> => {
    await saveWithEncrypted(ABILITIES_STORAGE_KEY, abilities);
  };

  const loadAbilitiesFromStorage = async (): Promise<UserAbilities | null> => {
    return await loadWithDecrypt(ABILITIES_STORAGE_KEY);
  };

  const clearAbilitiesFromStorage = (): void => {
    clearFromStorage(ABILITIES_STORAGE_KEY);
  };


  return {
    userAbilities,
    hasAbility,
    loadUserAbilities,
    clearAbilitiesCache,
    // Funções específicas para abilities
    saveAbilitiesToStorage,
    loadAbilitiesFromStorage,
    clearAbilitiesFromStorage,
  };
}
