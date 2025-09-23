import { onMounted } from "vue";
import { useRouter } from "vue-router";
import { useAbilities } from "./useAbilities";

/**
 * Composable para abstrair a lógica básica de guard de permissões
 * Verifica se tem permissão e redireciona se não tiver
 */
export function usePermission() {
  const { hasAbility, loadUserAbilities } = useAbilities();
  const router = useRouter();

  /**
   * Verifica permissão e redireciona se não tiver
   * @param permission - A permissão a ser verificada
   * @param redirectTo - Rota para redirecionar se não tiver permissão (padrão: /unauthorized)
   * @returns Promise<boolean> - true se tem permissão, false se foi redirecionado
   */
  const requirePermission = async (
    permission: string,
    redirectTo: string = "/unauthorized"
  ): Promise<boolean> => {
    await loadUserAbilities();
    const hasPermission = hasAbility.value(permission);

    if (!hasPermission) {
      router.push(redirectTo);
      return false;
    }

    return true;
  };

  /**
   * Hook para usar em componentes Vue que precisam verificar permissão no onMounted
   * @param permission - A permissão a ser verificada
   * @param onAuthorized - Callback executado quando o usuário tem permissão
   * @param redirectTo - Rota para redirecionar se não tiver permissão (padrão: /unauthorized)
   */
  const usePermissionGuard = (
    permission: string,
    onAuthorized?: () => void | Promise<void>,
    redirectTo: string = "/unauthorized"
  ) => {
    onMounted(async () => {
      const hasPermission = await requirePermission(permission, redirectTo);

      if (hasPermission && onAuthorized) {
        await onAuthorized();
      }
    });
  };

  return {
    requirePermission,
    usePermissionGuard,
  };
}
