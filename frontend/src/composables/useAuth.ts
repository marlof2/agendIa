/**
 * Composable para gerenciar autenticação e permissões
 */
import { ref, computed } from "vue";
import { useHttp } from "@/composables/useHttp";
import router from "@/router";
import { showErrorToast } from "@/utils/swal";
const { post } = useHttp();

export interface User {
  id: string;
  name: string;
  email: string;
  profile_id: number;
  company_id: string;
  profile: object ;
}

export interface AuthState {
  user: any;
  token: any;
  isAuthenticated: boolean;
}

const authState = ref<AuthState>({
  user: null ,
  token: null,
  isAuthenticated: false,
});

export function useAuth() {
  // Estado reativo
  const user = computed(() => authState.value.user);
  const token = computed(() => authState.value.token);
  const isAuthenticated = computed(() => authState.value.isAuthenticated);

  // Login
  const login = async (email: string, password: string): Promise<boolean> => {
    try {
      const response = await post("/auth/login", { email, password });

      // Salvar no localStorage
      localStorage.setItem("auth_token", response.data.token);
      localStorage.setItem("user_data", JSON.stringify(response.data.user));

      // Atualizar estado
      authState.value = {
        user: response.data.user,
        token: response.data.token,
        isAuthenticated: true,
      };

      // Carregar abilities automaticamente se disponíveis
      if (response.data.abilities && response.data.profile) {
        const { saveWithEncrypted } = await import('@/utils/storage');

        const abilitiesData = {
          abilities: response.data.abilities,
        };

        // Salvar abilities criptografadas no localStorage
        await saveWithEncrypted('agendia_user_abilities', abilitiesData);
      }

      return true;
    } catch (error: any) {
      // Não mostrar toast genérico se for erro de credenciais (401)
      // O useHttp já trata e mostra o toast apropriado
      if (error.response?.status !== 401) {
        showErrorToast("Erro no login", "Autenticação");
      }
      return false;
    }
  };

  // Login com Google
  const loginWithGoogle = async (): Promise<boolean> => {
    try {
      // Implementar integração com Google OAuth
      // const response = await api.post('/auth/google', { token: googleToken })

      // Simulação (remover em produção)
      const mockUser: User = {
        id: "2",
        name: "Maria Santos",
        email: "maria@example.com",
        profile_id: 2,
        company_id: "company_1",
        profile: {
          display_name: "Administrador",
        },
      };

      const mockToken = "mock_google_token";

      authState.value = {
        user: mockUser,
        token: mockToken,
        isAuthenticated: true,
      };

      localStorage.setItem("auth_token", mockToken);
      localStorage.setItem("user_data", JSON.stringify(mockUser));

      return true;
    } catch (error) {
      showErrorToast("Erro no login com Google", "Autenticação");
      return false;
    }
  };

  // Logout
  const logout = async (): Promise<void> => {
    try {
      const response = await post("/auth/logout");

      if (response.success) {

        authState.value = {
          user: null,
          token: null,
          isAuthenticated: false,
        };
        localStorage.clear();
        router.push("/login");
      }
    } catch (error) {
      showErrorToast("Erro no logout", "Autenticação");
      authState.value = {
        user: null,
        token: null,
        isAuthenticated: false,
      };
      localStorage.clear();
      router.push("/login");
    }
  };

  // Verificar autenticação no carregamento da página
  const checkAuth = async (): Promise<void> => {
    const token = localStorage.getItem("auth_token");
    const userData = localStorage.getItem("user_data");

    if (token && userData) {
      try {
        const user = JSON.parse(userData) as User;
        authState.value = {
          user,
          token,
          isAuthenticated: true,
        };

      } catch (error) {
        showErrorToast("Erro ao carregar dados do usuário", "Autenticação");
        logout();
      }
    }
  };

  // Inicializar verificação de auth
  checkAuth();

  return {
    // Estado
    user,
    token,
    isAuthenticated,

    // Métodos
    login,
    loginWithGoogle,
    logout,
    checkAuth,
  };
}
