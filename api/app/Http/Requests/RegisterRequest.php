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
        return [
            // Dados pessoais básicos
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'cpf' => ['required', 'string', 'max:14', new CnpjCpf('cpf'), 'unique:users,cpf'],
            'has_whatsapp' => 'boolean',
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
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório',
            'name.max' => 'O nome deve ter no máximo 255 caracteres',
            'email.required' => 'O e-mail é obrigatório',
            'email.email' => 'E-mail inválido',
            'email.unique' => 'Este e-mail já está cadastrado',
            'email.max' => 'O e-mail deve ter no máximo 255 caracteres',
            'password.required' => 'A senha é obrigatória',
            'password.min' => 'A senha deve ter no mínimo 6 caracteres',
            'password.confirmed' => 'As senhas não conferem',
            'phone.max' => 'O telefone deve ter no máximo 20 caracteres',
            'cpf.required' => 'O CPF é obrigatório',
            'cpf.string' => 'O CPF deve ser um texto',
            'cpf.max' => 'O CPF deve ter no máximo 14 caracteres',
            'cpf.unique' => 'Este CPF já está sendo usado por outro usuário',
            'has_whatsapp.boolean' => 'O campo WhatsApp deve ser verdadeiro ou falso',
        ];
    }
}

