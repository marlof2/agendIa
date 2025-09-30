<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileRequest extends FormRequest
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
        return match ($this->method()) {
            'POST' => $this->storeRules(),
            'PUT' => $this->updateRules(),
            'PATCH' => $this->patchRules(),
            'DELETE' => $this->deleteRules(),
            default => []
        };
    }

    /**
     * Validation rules for creating a profile (POST)
     */
    private function storeRules(): array
    {
        return [
            'name' => 'required|string|unique:profiles,name',
            'display_name' => 'required|unique:profiles,display_name|string',
            'description' => 'nullable|string|max:500',
            'abilities' => 'array',
            'abilities.*' => 'exists:abilities,id'
        ];
    }

    /**
     * Validation rules for updating a profile (PUT - full update)
     */
    private function updateRules(): array
    {
        $profileId = $this->route('profile')->id;

        return [
            'name' => [
                'required',
                'string',
                Rule::unique('profiles', 'name')->ignore($profileId)
            ],
            'display_name' => 'required|unique:profiles,display_name|string',
            'description' => 'nullable|string|max:500',
            'abilities' => 'array',
            'abilities.*' => 'exists:abilities,id'
        ];
    }

    /**
     * Validation rules for patching a profile (PATCH - partial update)
     */
    private function patchRules(): array
    {
        $profileId = $this->route('profile')->id;
        $rules = [];

        // Só valida os campos que foram enviados
        if ($this->has('name')) {
            $rules['name'] = [
                'string',
                Rule::unique('profiles', 'name')->ignore($profileId)
            ];
        }

        if ($this->has('display_name')) {
            $rules['display_name'] = 'unique:profiles,display_name|string';
        }

        if ($this->has('description')) {
            $rules['description'] = 'nullable|string|max:500';
        }

        if ($this->has('abilities')) {
            $rules['abilities'] = 'array';
            $rules['abilities.*'] = 'exists:abilities,id';
        }

        return $rules;
    }

    /**
     * Validation rules for deleting a profile (DELETE)
     */
    private function deleteRules(): array
    {
        return [];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome do perfil é obrigatório.',
            'name.unique' => 'Já existe um perfil com este nome.',
            'display_name.required' => 'O nome de exibição é obrigatório.',
            'display_name.unique' => 'Já existe um perfil com este nome de exibição.',
            'description.max' => 'A descrição não pode ter mais de 500 caracteres.',
            'abilities.array' => 'As permissões devem ser um array.',
            'abilities.*.exists' => 'Uma ou mais permissões selecionadas são inválidas.'
        ];
    }
}
