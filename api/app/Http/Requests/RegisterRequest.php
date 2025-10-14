<?php

namespace App\Http\Requests;

use App\Rules\CnpjCpf;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            // Dados pessoais
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'cpf' => ['required', 'string', 'max:14', new CnpjCpf('cpf'), 'unique:users,cpf'],
            'has_whatsapp' => 'boolean',

            // Tipo de conta
            'account_type' => 'required|in:owner,professional,supervisor,client',
            'profile_id' => 'required|exists:profiles,id',
        ];

        // Se for proprietário, dados da empresa são obrigatórios
        if ($this->account_type === 'owner') {
            $rules = array_merge($rules, [
                'company.name' => 'required|string|max:255',
                'company.person_type' => 'required|in:physical,legal',
                'company.cnpj' => ['nullable', 'string', 'max:18', new CnpjCpf('cnpj'), 'unique:companies,cnpj'],
                'company.cpf' => ['nullable', 'string', 'max:14', new CnpjCpf('cpf'), 'unique:companies,cpf'],
                'company.responsible_name' => 'required|string|max:255',
                'company.phone_1' => 'required|string|max:20',
                'company.has_whatsapp_1' => 'boolean',
                'company.phone_2' => 'nullable|string|max:20',
                'company.has_whatsapp_2' => 'boolean',
                'company.timezone_id' => 'nullable|exists:timezones,id',
            ]);
        }
        // Para outros perfis, a associação será feita após o login

        return $rules;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Remove máscara do CPF antes da validação
        if ($this->has('cpf')) {
            $this->merge([
                'cpf' => preg_replace('/[^0-9]/', '', $this->input('cpf'))
            ]);
        }

        // Remove máscara do CNPJ da empresa antes da validação
        if ($this->has('company.cnpj')) {
            $this->merge([
                'company.cnpj' => preg_replace('/[^0-9]/', '', $this->input('company.cnpj'))
            ]);
        }

        // Remove máscara do CPF da empresa antes da validação
        if ($this->has('company.cpf')) {
            $this->merge([
                'company.cpf' => preg_replace('/[^0-9]/', '', $this->input('company.cpf'))
            ]);
        }
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório',
            'email.required' => 'O e-mail é obrigatório',
            'email.email' => 'E-mail inválido',
            'email.unique' => 'Este e-mail já está cadastrado',
            'password.required' => 'A senha é obrigatória',
            'password.min' => 'A senha deve ter no mínimo 6 caracteres',
            'password.confirmed' => 'As senhas não conferem',
            'account_type.required' => 'Selecione o tipo de conta',
            'account_type.in' => 'Tipo de conta inválido',
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.string' => 'O CPF deve ser um texto.',
            'cpf.max' => 'O CPF não pode ter mais de 14 caracteres.',
            'cpf.unique' => 'Este CPF já está sendo usado por outro usuário.',
            'company.name.required' => 'O nome da empresa é obrigatório',
            'company.person_type.required' => 'O tipo de pessoa é obrigatório',
            'company.responsible_name.required' => 'O nome do responsável é obrigatório',
            'company.phone_1.required' => 'O telefone da empresa é obrigatório',
        ];
    }
}

