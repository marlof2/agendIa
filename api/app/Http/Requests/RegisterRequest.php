<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
                'company.cnpj' => 'nullable|string|max:18',
                'company.cpf' => 'nullable|string|max:14',
                'company.responsible_name' => 'required|string|max:255',
                'company.phone_1' => 'required|string|max:20',
                'company.has_whatsapp_1' => 'boolean',
                'company.phone_2' => 'nullable|string|max:20',
                'company.has_whatsapp_2' => 'boolean',
                'company.timezone_id' => 'nullable|exists:timezones,id',
            ]);
        } else {
            // Se não for proprietário, pode selecionar empresas para se associar
            $rules['company_ids'] = 'nullable|array';
            $rules['company_ids.*'] = 'exists:companies,id';
        }

        return $rules;
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

            'company.name.required' => 'O nome da empresa é obrigatório',
            'company.person_type.required' => 'O tipo de pessoa é obrigatório',
            'company.responsible_name.required' => 'O nome do responsável é obrigatório',
            'company.phone_1.required' => 'O telefone da empresa é obrigatório',
        ];
    }
}

