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

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: setupLayouts(routes),
});

// Configurações de rotas
const PUBLIC_ROUTES = ["/", "/login", "/register"];

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
  if (to.name === "login" && useAuth().isAuthenticated.value) {
    next("/dashboard");
    return true;
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
  // 1. Redirecionar usuário autenticado que tenta acessar login
  if (redirectAuthenticatedUser(to, next)) {
    return;
  }

  // 2. Redirecionar usuário não autenticado para login
  if (redirectUnauthenticatedUser(to, next)) {
    return;
  }

  // 3. Verificar permissões específicas da rota
  const requiresAuth = to.meta.requiresAuth as boolean;
  const requiresAbility = to.meta.requiresAbility as string;

  if (requiresAuth) {
    const hasPermission = await checkUserPermissions(requiresAbility);

    if (!hasPermission) {
      next("/unauthorized");
      return;
    }
  }

  // 4. Permitir acesso à rota
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
