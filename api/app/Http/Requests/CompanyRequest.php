<?php

namespace App\Http\Requests;

use App\Rules\CnpjCpf;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyRequest extends FormRequest
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
     * Validation rules for creating a company (POST)
     */
    private function storeRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'person_type' => 'required|in:physical,legal',
            'cnpj' => ['nullable', 'string', 'max:18', new CnpjCpf('cnpj'), 'unique:companies,cnpj'],
            'cpf' => ['nullable', 'string', 'max:14', new CnpjCpf('cpf'), 'unique:companies,cpf'],
            'responsible_name' => 'required|string|max:255',
            'phone_1' => 'required|string|max:20',
            'has_whatsapp_1' => 'boolean',
            'phone_2' => 'nullable|string|max:20',
            'has_whatsapp_2' => 'boolean',
            'timezone_id' => 'nullable|exists:timezones,id'
        ];
    }

    /**
     * Validation rules for updating a company (PUT - full update)
     */
    private function updateRules(): array
    {
        $companyId = $this->route('company')->id;

        return [
            'name' => 'required|string|max:255',
            'person_type' => 'required|in:physical,legal',
            'cnpj' => [
                'nullable',
                'string',
                'max:18',
                new CnpjCpf('cnpj'),
                Rule::unique('companies', 'cnpj')->ignore($companyId)
            ],
            'cpf' => [
                'nullable',
                'string',
                'max:14',
                new CnpjCpf('cpf'),
                Rule::unique('companies', 'cpf')->ignore($companyId)
            ],
            'responsible_name' => 'required|string|max:255',
            'phone_1' => 'required|string|max:20',
            'has_whatsapp_1' => 'boolean',
            'phone_2' => 'nullable|string|max:20',
            'has_whatsapp_2' => 'boolean',
            'timezone_id' => 'nullable|exists:timezones,id'
        ];
    }

    /**
     * Validation rules for patching a company (PATCH - partial update)
     */
    private function patchRules(): array
    {
        $companyId = $this->route('company')->id;
        $rules = [];

        if ($this->has('name')) {
            $rules['name'] = 'required|string|max:255';
        }

        if ($this->has('cnpj')) {
            $rules['cnpj'] = [
                'nullable',
                'string',
                'max:18',
                new CnpjCpf('cnpj'),
                Rule::unique('companies', 'cnpj')->ignore($companyId)
            ];
        }

        if ($this->has('phone')) {
            $rules['phone'] = 'required|string|max:20';
        }

        if ($this->has('whatsapp_number')) {
            $rules['whatsapp_number'] = 'required|string|max:20';
        }

        if ($this->has('timezone_id')) {
            $rules['timezone_id'] = 'nullable|exists:timezones,id';
        }

        return $rules;
    }

    /**
     * Validation rules for deleting a company (DELETE)
     */
    private function deleteRules(): array
    {
        return [];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Remove máscara do CNPJ antes da validação
        if ($this->has('cnpj')) {
            $this->merge([
                'cnpj' => preg_replace('/[^0-9]/', '', $this->input('cnpj'))
            ]);
        }

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
            'name.required' => 'O nome da empresa é obrigatório.',
            'name.string' => 'O nome da empresa deve ser um texto.',
            'name.max' => 'O nome da empresa não pode ter mais de 255 caracteres.',

            'person_type.required' => 'O tipo de pessoa é obrigatório.',
            'person_type.in' => 'O tipo de pessoa deve ser Física ou Jurídica.',

            'cnpj.string' => 'O CNPJ deve ser um texto.',
            'cnpj.max' => 'O CNPJ não pode ter mais de 18 caracteres.',
            'cnpj.unique' => 'Este CNPJ já está sendo usado por outra empresa.',

            'cpf.string' => 'O CPF deve ser um texto.',
            'cpf.max' => 'O CPF não pode ter mais de 14 caracteres.',
            'cpf.unique' => 'Este CPF já está sendo usado por outra empresa.',

            'responsible_name.required' => 'O nome do responsável é obrigatório.',
            'responsible_name.string' => 'O nome do responsável deve ser um texto.',
            'responsible_name.max' => 'O nome do responsável não pode ter mais de 255 caracteres.',

            'phone_1.required' => 'O telefone principal é obrigatório.',
            'phone_1.string' => 'O telefone principal deve ser um texto.',
            'phone_1.max' => 'O telefone principal não pode ter mais de 20 caracteres.',

            'phone_2.string' => 'O telefone secundário deve ser um texto.',
            'phone_2.max' => 'O telefone secundário não pode ter mais de 20 caracteres.',

            'has_whatsapp_1.boolean' => 'O campo WhatsApp principal deve ser verdadeiro ou falso.',
            'has_whatsapp_2.boolean' => 'O campo WhatsApp secundário deve ser verdadeiro ou falso.',

            'timezone_id.exists' => 'O fuso horário selecionado não existe.',
        ];
    }
}
