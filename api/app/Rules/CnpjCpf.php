<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CnpjCpf implements ValidationRule
{
    private string $type;

    public function __construct(string $type = 'both')
    {
        $this->type = $type; // 'cnpj', 'cpf', ou 'both'
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        $cleanValue = preg_replace('/[^0-9]/', '', $value);

        if ($this->type === 'cnpj' || $this->type === 'both') {
            if (strlen($cleanValue) === 14 && !$this->isValidCNPJ($cleanValue)) {
                $fail('O CNPJ informado é inválido.');
                return;
            }
        }

        if ($this->type === 'cpf' || $this->type === 'both') {
            if (strlen($cleanValue) === 11 && !$this->isValidCPF($cleanValue)) {
                $fail('O CPF informado é inválido.');
                return;
            }
        }

        // Verificar se o tamanho está correto para o tipo esperado
        if ($this->type === 'cnpj' && strlen($cleanValue) !== 14) {
            $fail('O CNPJ deve ter 14 dígitos.');
            return;
        }

        if ($this->type === 'cpf' && strlen($cleanValue) !== 11) {
            $fail('O CPF deve ter 11 dígitos.');
            return;
        }

        if ($this->type === 'both' && !in_array(strlen($cleanValue), [11, 14])) {
            $fail('O documento deve ter 11 dígitos (CPF) ou 14 dígitos (CNPJ).');
            return;
        }
    }

    /**
     * Valida CNPJ
     */
    private function isValidCNPJ(string $cnpj): bool
    {
        // Verificar se todos os dígitos são iguais
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        // Calcular o primeiro dígito verificador
        $sum = 0;
        $weight = 5;
        for ($i = 0; $i < 12; $i++) {
            $sum += intval($cnpj[$i]) * $weight;
            $weight = ($weight == 2) ? 9 : $weight - 1;
        }
        $remainder = $sum % 11;
        $digit1 = ($remainder < 2) ? 0 : 11 - $remainder;

        if (intval($cnpj[12]) !== $digit1) {
            return false;
        }

        // Calcular o segundo dígito verificador
        $sum = 0;
        $weight = 6;
        for ($i = 0; $i < 13; $i++) {
            $sum += intval($cnpj[$i]) * $weight;
            $weight = ($weight == 2) ? 9 : $weight - 1;
        }
        $remainder = $sum % 11;
        $digit2 = ($remainder < 2) ? 0 : 11 - $remainder;

        return intval($cnpj[13]) === $digit2;
    }

    /**
     * Valida CPF
     */
    private function isValidCPF(string $cpf): bool
    {
        // Verificar se todos os dígitos são iguais
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Calcular o primeiro dígito verificador
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += intval($cpf[$i]) * (10 - $i);
        }
        $remainder = $sum % 11;
        $digit1 = ($remainder < 2) ? 0 : 11 - $remainder;

        if (intval($cpf[9]) !== $digit1) {
            return false;
        }

        // Calcular o segundo dígito verificador
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += intval($cpf[$i]) * (11 - $i);
        }
        $remainder = $sum % 11;
        $digit2 = ($remainder < 2) ? 0 : 11 - $remainder;

        return intval($cpf[10]) === $digit2;
    }
}
