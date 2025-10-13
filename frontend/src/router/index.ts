/**
 * router/index.ts
 *
 * Manual routes configuration
 */

// Composables
import { createRouter, createWebHistory } from "vue-router";
import { setupLayouts } from "virtual:generated-layouts";
import routes from "./routes";
import { useAuth } from "@/composables/useAuth";
import { useTenant } from "@/composables/useTenant";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: setupLayouts(routes),
});

// Configurações de rotas
const PUBLIC_ROUTES = ["/", "/login", "/register", "/select-tenant"];

// Funções auxiliares para guards
const isPublicRoute = (path: string): boolean => {
  return PUBLIC_ROUTES.includes(path);
};

const checkUserPermissions = async (
  requiredAbility: string
): Promise<boolean> => {
  try {
    const { useAbilities } = await import("@/composables/useAbilities");
    const { hasPermission, getUserPermissions } = useAbilities();

    await getUserPermissions();

    return hasPermission.value(requiredAbility);
  } catch (error) {
    console.error("Erro ao verificar permissões:", error);
    return false;
  }
};

const redirectAuthenticatedUser = (to: any, next: any): boolean => {
  const { isAuthenticated } = useAuth();
  const { hasTenantSelected } = useTenant();

  // Permitir acesso ao login se vindo de logout ou se não autenticado
  if (to.name === "login") {
    // Verificar localStorage diretamente para evitar estado desatualizado
    const hasToken = localStorage.getItem('auth_token');

    if (!hasToken || !isAuthenticated.value) {
      return false; // Permite acessar login
    }

    // Se está autenticado e tem token, redireciona
    if (isAuthenticated.value) {
      // Se não tem tenant selecionado, vai para seleção
      if (!hasTenantSelected.value) {
        next("/select-tenant");
      } else {
        next("/dashboard");
      }
      return true;
    }
  }
  return false;
};

const redirectUnauthenticatedUser = (to: any, next: any): boolean => {
  const { isAuthenticated } = useAuth();

  if (!isAuthenticated.value && !isPublicRoute(to.path)) {
    next("/login");
    return true;
  }
  return false;
};

// Guards globais
router.beforeEach(async (to, from, next) => {
  const { isAuthenticated } = useAuth();
  const { hasTenantSelected, loadCurrentTenant } = useTenant();

  // 1. Redirecionar usuário autenticado que tenta acessar login
  if (redirectAuthenticatedUser(to, next)) {
    return;
  }

  // 2. Redirecionar usuário não autenticado para login
  if (redirectUnauthenticatedUser(to, next)) {
    return;
  }

  // 3. Verificar se usuário autenticado precisa selecionar tenant
  const requiresAuth = to.meta.requiresAuth as boolean;
  const requiresTenant = to.meta.requiresTenant !== false; // Por padrão, requer tenant

  if (requiresAuth && requiresTenant && isAuthenticated.value) {
    // Carrega tenant do localStorage se ainda não carregou
    if (!hasTenantSelected.value) {
      loadCurrentTenant();
    }

    // Se ainda não tem tenant selecionado e não está indo para seleção
    if (!hasTenantSelected.value && to.path !== '/select-tenant') {
      next('/select-tenant');
      return;
    }
  }

  // 4. Verificar permissões específicas da rota
  const requiresAbility = to.meta.requiresAbility as string;

  if (requiresAuth && requiresAbility) {
    const hasPermission = await checkUserPermissions(requiresAbility);

    if (!hasPermission) {
      next("/unauthorized");
      return;
    }
  }

  // 5. Permitir acesso à rota
  next();
});

// Workaround for https://github.com/vitejs/vite/issues/11804
router.onError((err, to) => {
  if (err?.message?.includes?.("Failed to fetch dynamically imported module")) {
    if (localStorage.getItem("vuetify:dynamic-reload")) {
      console.error("Dynamic import error, reloading page did not fix it", err);
    } else {
      console.log("Reloading page to fix dynamic import error");
      localStorage.setItem("vuetify:dynamic-reload", "true");
      location.assign(to.fullPath);
    }
  } else {
    console.error(err);
  }
});

router.isReady().then(() => {
  localStorage.removeItem("vuetify:dynamic-reload");
});

export default router;
