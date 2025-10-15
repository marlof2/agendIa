/**
 * Composable para gerenciar autenticação e permissões
 */
import { ref, computed, nextTick } from "vue";
import { useHttp } from "@/composables/useHttp";
import { useTenant } from "@/composables/useTenant";
import router from "@/router";
import { showErrorToast } from "@/utils/swal";
const { post } = useHttp();

export interface User {
  id: string;
  name: string;
  email: string;
  company_ids: number[];
  companies: Array<{
    id: number;
    name: string;
    pivot: {
      profile_id: number;
      is_main_company: boolean;
    };
  }>;
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

      // As abilities serão carregadas apenas após a seleção de uma empresa
      // Não carregamos abilities no login pois o usuário ainda não selecionou uma empresa

      // Salvar empresas (tenants) disponíveis se retornadas
      if (response.data.tenants && Array.isArray(response.data.tenants)) {
        const { setAvailableTenants } = useTenant();
        setAvailableTenants(response.data.tenants);
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
        company_ids: [1],
        companies: [
          {
            id: 1,
            name: "Marques Tech Ltda",
            pivot: {
              profile_id: 2,
              is_main_company: true,
            },
          },
        ],
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
    // Salvar tema antes de fazer qualquer coisa
    const currentTheme = localStorage.getItem('agendia-theme');

    try {
      // 1. Fazer logout no backend PRIMEIRO (enquanto token ainda é válido)
      await post("/auth/logout");
    } catch (backendError) {
      console.warn("Erro ao fazer logout no backend:", backendError);
      // Continua o logout local mesmo se backend falhar
    }

    // 2. Limpar estado local
    authState.value = {
      user: null,
      token: null,
      isAuthenticated: false,
    };

    // 3. Limpar dados de tenant
    const { clearTenant } = useTenant();
    clearTenant();

    // 4. Limpar localStorage
    localStorage.clear();

    // 5. Restaurar tema
    if (currentTheme) {
      localStorage.setItem('agendia-theme', currentTheme);
    }

    // 6. Aguardar atualização do estado reativo
    await nextTick();

    // 7. Redirecionar para login
    await router.push("/login");
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

  // Atualizar dados do usuário
  const updateUserData = async (): Promise<void> => {
    try {
      const { get } = useHttp();
      const response = await get('/auth/me');

      if (response.data && response.data.user) {
        // Atualizar localStorage com dados do usuário
        localStorage.setItem("user_data", JSON.stringify(response.data.user));

        // Atualizar estado reativo com dados do usuário
        authState.value.user = response.data.user;

        // Atualizar tenants se disponíveis
        if (response.data.tenants && Array.isArray(response.data.tenants)) {
          const { setAvailableTenants } = useTenant();
          setAvailableTenants(response.data.tenants);
        }
      }
    } catch (error) {
      console.error('Erro ao atualizar dados do usuário:', error);
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
    updateUserData,
  };
}
