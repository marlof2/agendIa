/**
 * Composable para funções de máscara
 * Reutilizável em diferentes telas do sistema
 */

export function useMask() {
  /**
   * Aplica máscara de CNPJ
   */
  const maskCNPJ = (event: Event) => {
    const target = event.target as HTMLInputElement;
    let value = target.value.replace(/\D/g, '');
    value = value.replace(/^(\d{2})(\d)/, '$1.$2');
    value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
    value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
    value = value.replace(/(\d{4})(\d)/, '$1-$2');
    target.value = value;
    return value;
  };

  /**
   * Aplica máscara de CPF
   */
  const maskCPF = (event: Event) => {
    const target = event.target as HTMLInputElement;
    let value = target.value.replace(/\D/g, '');
    value = value.replace(/^(\d{3})(\d)/, '$1.$2');
    value = value.replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3');
    value = value.replace(/\.(\d{3})(\d)/, '.$1-$2');
    target.value = value;
    return value;
  };

  /**
   * Aplica máscara de telefone (fixo ou celular)
   */
  const maskPhone = (event: Event) => {
    const target = event.target as HTMLInputElement;
    let value = target.value.replace(/\D/g, '');

    if (value.length <= 10) {
      // Telefone fixo: (11) 3333-4444
      value = value.replace(/^(\d{2})(\d)/, '($1) $2');
      value = value.replace(/(\d{4})(\d)/, '$1-$2');
    } else {
      // Celular: (11) 99999-8888
      value = value.replace(/^(\d{2})(\d)/, '($1) $2');
      value = value.replace(/(\d{5})(\d)/, '$1-$2');
    }

    target.value = value;
    return value;
  };

  /**
   * Remove máscara de CNPJ (apenas números)
   */
  const unmaskCNPJ = (value: string): string => {
    return value.replace(/\D/g, '');
  };

  /**
   * Remove máscara de CPF (apenas números)
   */
  const unmaskCPF = (value: string): string => {
    return value.replace(/\D/g, '');
  };

  /**
   * Remove máscara de telefone (apenas números)
   */
  const unmaskPhone = (value: string): string => {
    return value.replace(/\D/g, '');
  };

  /**
   * Formata CNPJ para exibição (adiciona máscara)
   */
  const formatCNPJ = (cnpj: string): string => {
    if (!cnpj) return 'Não informado';
    const clean = cnpj.replace(/\D/g, '');
    return clean.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5');
  };

  /**
   * Formata CPF para exibição (adiciona máscara)
   */
  const formatCPF = (cpf: string): string => {
    if (!cpf) return 'Não informado';
    const clean = cpf.replace(/\D/g, '');
    return clean.replace(/^(\d{3})(\d{3})(\d{3})(\d{2})$/, '$1.$2.$3-$4');
  };

  /**
   * Formata telefone para exibição (adiciona máscara)
   */
  const formatPhone = (phone: string): string => {
    if (!phone) return 'Não informado';
    const clean = phone.replace(/\D/g, '');

    if (clean.length === 10) {
      return clean.replace(/^(\d{2})(\d{4})(\d{4})$/, '($1) $2-$3');
    } else if (clean.length === 11) {
      return clean.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
    }

    return phone;
  };

  return {
    // Funções de máscara para inputs
    maskCNPJ,
    maskCPF,
    maskPhone,

    // Funções para remover máscaras
    unmaskCNPJ,
    unmaskCPF,
    unmaskPhone,

    // Funções para formatação de exibição
    formatCNPJ,
    formatCPF,
    formatPhone,
  };
}
