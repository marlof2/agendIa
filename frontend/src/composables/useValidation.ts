/**
 * Composable para validações de documentos brasileiros
 */
export function useValidation() {
  /**
   * Remove caracteres não numéricos de uma string
   */
  const cleanDocument = (document: string): string => {
    return document.replace(/[^0-9]/g, '');
  };

  /**
   * Verifica se todos os dígitos são iguais
   */
  const allDigitsEqual = (document: string): boolean => {
    return /^(\d)\1+$/.test(document);
  };

  /**
   * Valida CPF
   */
  const isValidCPF = (cpf: string): boolean => {
    const cleanCPF = cleanDocument(cpf);

    // Verificar se tem 11 dígitos
    if (cleanCPF.length !== 11) {
      return false;
    }

    // Verificar se todos os dígitos são iguais
    if (allDigitsEqual(cleanCPF)) {
      return false;
    }

    // Calcular o primeiro dígito verificador
    let sum = 0;
    for (let i = 0; i < 9; i++) {
      const digit = cleanCPF[i];
      if (!digit) return false;
      sum += parseInt(digit) * (10 - i);
    }
    let remainder = sum % 11;
    let digit1 = remainder < 2 ? 0 : 11 - remainder;

    const ninthDigit = cleanCPF[9];
    if (!ninthDigit || parseInt(ninthDigit) !== digit1) {
      return false;
    }

    // Calcular o segundo dígito verificador
    sum = 0;
    for (let i = 0; i < 10; i++) {
      const digit = cleanCPF[i];
      if (!digit) return false;
      sum += parseInt(digit) * (11 - i);
    }
    remainder = sum % 11;
    let digit2 = remainder < 2 ? 0 : 11 - remainder;

    const tenthDigit = cleanCPF[10];
    return tenthDigit ? parseInt(tenthDigit) === digit2 : false;
  };

  /**
   * Valida CNPJ
   */
  const isValidCNPJ = (cnpj: string): boolean => {
    const cleanCNPJ = cleanDocument(cnpj);

    // Verificar se tem 14 dígitos
    if (cleanCNPJ.length !== 14) {
      return false;
    }

    // Verificar se todos os dígitos são iguais
    if (allDigitsEqual(cleanCNPJ)) {
      return false;
    }

    // Calcular o primeiro dígito verificador
    let sum = 0;
    let weight = 5;
    for (let i = 0; i < 12; i++) {
      const digit = cleanCNPJ[i];
      if (!digit) return false;
      sum += parseInt(digit) * weight;
      weight = weight === 2 ? 9 : weight - 1;
    }
    let remainder = sum % 11;
    let digit1 = remainder < 2 ? 0 : 11 - remainder;

    const twelfthDigit = cleanCNPJ[12];
    if (!twelfthDigit || parseInt(twelfthDigit) !== digit1) {
      return false;
    }

    // Calcular o segundo dígito verificador
    sum = 0;
    weight = 6;
    for (let i = 0; i < 13; i++) {
      const digit = cleanCNPJ[i];
      if (!digit) return false;
      sum += parseInt(digit) * weight;
      weight = weight === 2 ? 9 : weight - 1;
    }
    remainder = sum % 11;
    let digit2 = remainder < 2 ? 0 : 11 - remainder;

    const thirteenthDigit = cleanCNPJ[13];
    return thirteenthDigit ? parseInt(thirteenthDigit) === digit2 : false;
  };

  /**
   * Valida CPF ou CNPJ baseado no tamanho
   */
  const isValidDocument = (document: string): boolean => {
    const cleanDoc = cleanDocument(document);

    if (cleanDoc.length === 11) {
      return isValidCPF(document);
    } else if (cleanDoc.length === 14) {
      return isValidCNPJ(document);
    }

    return false;
  };

  /**
   * Retorna regras de validação para CPF
   */
  const getCPFValidationRules = () => [
    (v: string) => {
      if (!v) return true; // Campo opcional
      return isValidCPF(v) || 'CPF inválido';
    }
  ];

  /**
   * Retorna regras de validação para CNPJ
   */
  const getCNPJValidationRules = () => [
    (v: string) => {
      if (!v) return true; // Campo opcional
      return isValidCNPJ(v) || 'CNPJ inválido';
    }
  ];

  /**
   * Retorna regras de validação para CPF ou CNPJ
   */
  const getDocumentValidationRules = () => [
    (v: string) => {
      if (!v) return true; // Campo opcional
      return isValidDocument(v) || 'Documento inválido';
    }
  ];

  return {
    isValidCPF,
    isValidCNPJ,
    isValidDocument,
    getCPFValidationRules,
    getCNPJValidationRules,
    getDocumentValidationRules,
    cleanDocument
  };
}
