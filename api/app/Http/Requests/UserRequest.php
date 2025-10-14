<?php

namespace App\Http\Requests;

use App\Rules\CnpjCpf;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $userId = $this->route('user') ? $this->route('user')->id : null;

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId)
            ],
            'password' => $userId ? 'nullable|string|min:8|confirmed' : 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'has_whatsapp' => 'boolean',
            'company_ids' => 'nullable|array',
            'company_ids.*' => 'exists:companies,id',
            'cpf' => $userId
                ? ['sometimes', 'string', 'max:14', new CnpjCpf('cpf'), Rule::unique('users', 'cpf')->ignore($userId)]
                : ['required', 'string', 'max:14', new CnpjCpf('cpf'), 'unique:users,cpf'],
        ];
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
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.string' => 'O nome deve ser um texto.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',

            'email.required' => 'O email é obrigatório.',
            'email.email' => 'O email deve ter um formato válido.',
            'email.max' => 'O email não pode ter mais de 255 caracteres.',
            'email.unique' => 'Este email já está sendo usado.',

            'password.required' => 'A senha é obrigatória.',
            'password.string' => 'A senha deve ser um texto.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            'password.confirmed' => 'A confirmação da senha não confere.',

            'phone.string' => 'O telefone deve ser um texto.',
            'phone.max' => 'O telefone não pode ter mais de 20 caracteres.',

            'has_whatsapp.boolean' => 'O campo WhatsApp deve ser verdadeiro ou falso.',

            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.string' => 'O CPF deve ser um texto.',
            'cpf.max' => 'O CPF não pode ter mais de 14 caracteres.',
            'cpf.unique' => 'Este CPF já está sendo usado por outro usuário.',

            'company_ids.array' => 'As empresas devem ser uma lista.',
            'company_ids.*.exists' => 'Uma ou mais empresas selecionadas não existem.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'email' => 'email',
            'password' => 'senha',
            'phone' => 'telefone',
            'has_whatsapp' => 'WhatsApp',
            'company_ids' => 'empresas',
        ];
    }
}
