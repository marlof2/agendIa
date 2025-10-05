/**
 * Composable para utilitários de perfis
 * Centraliza as funções de cores e ícones dos perfis
 */

export function useProfileUtils() {
  /**
   * Retorna a cor do perfil baseada no nome
   */
  const getProfileColor = (profileName?: string): string => {
    switch (profileName) {
      case 'admin': return 'error';
      case 'secretary': return 'warning';
      case 'professional': return 'info';
      case 'client': return 'success';
      default: return 'grey';
    }
  };

  /**
   * Retorna o ícone do perfil baseado no nome
   */
  const getProfileIcon = (profileName?: string): string => {
    switch (profileName) {
      case 'admin': return 'mdi-shield-crown';
      case 'secretary': return 'mdi-account-tie';
      case 'professional': return 'mdi-briefcase';
      case 'client': return 'mdi-account';
      default: return 'mdi-help';
    }
  };

  /**
   * Retorna informações completas do perfil (cor, ícone e nome)
   */
  const getProfileInfo = (profileName?: string) => {
    return {
      color: getProfileColor(profileName),
      icon: getProfileIcon(profileName),
      name: profileName || 'unknown'
    };
  };

  return {
    getProfileColor,
    getProfileIcon,
    getProfileInfo
  };
}
