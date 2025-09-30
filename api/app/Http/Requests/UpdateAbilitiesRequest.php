<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAbilitiesRequest extends FormRequest
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
            'abilities' => 'required|array',
            'abilities.*' => 'exists:abilities,id'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'abilities.required' => 'As permissões são obrigatórias.',
            'abilities.array' => 'As permissões devem ser um array.',
            'abilities.*.exists' => 'Uma ou mais permissões selecionadas são inválidas.'
        ];
    }
}
