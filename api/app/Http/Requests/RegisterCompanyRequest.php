<?php

namespace App\Http\Requests;

use App\Rules\CnpjCpf;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterCompanyRequest extends FormRequest
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
            'user_id' => 'required|exists:users,id',
            'company' => 'required|array',
            'company.name' => 'required|string|max:255',
            'company.person_type' => 'required|in:legal,physical',
            'company.cnpj' => 'required_if:company.person_type,legal|nullable|string|max:18',
            'company.cpf' => 'required_if:company.person_type,physical|nullable|string|max:14',
            'company.responsible_name' => 'required|string|max:255',
            'company.phone_1' => 'required|string|max:20',
            'company.has_whatsapp_1' => 'boolean',
            'company.phone_2' => 'nullable|string|max:20',
            'company.has_whatsapp_2' => 'boolean',
            'company.timezone_id' => 'required|exists:timezones,id',
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
            'user_id.required' => 'ID do usuário é obrigatório',
            'user_id.exists' => 'Usuário não encontrado',
            'company.required' => 'Dados da empresa são obrigatórios',
            'company.array' => 'Dados da empresa devem ser um objeto',
            'company.name.required' => 'Nome da empresa é obrigatório',
            'company.name.max' => 'Nome da empresa deve ter no máximo 255 caracteres',
            'company.person_type.required' => 'Tipo de pessoa é obrigatório',
            'company.person_type.in' => 'Tipo de pessoa deve ser Pessoa Jurídica ou Pessoa Física',
            'company.cnpj.required_if' => 'CNPJ é obrigatório para Pessoa Jurídica',
            'company.cnpj.max' => 'CNPJ deve ter no máximo 18 caracteres',
            'company.cpf.required_if' => 'CPF é obrigatório para Pessoa Física',
            'company.cpf.max' => 'CPF deve ter no máximo 14 caracteres',
            'company.responsible_name.required' => 'Nome do responsável é obrigatório',
            'company.responsible_name.max' => 'Nome do responsável deve ter no máximo 255 caracteres',
            'company.phone_1.required' => 'Telefone principal é obrigatório',
            'company.phone_1.max' => 'Telefone principal deve ter no máximo 20 caracteres',
            'company.has_whatsapp_1.boolean' => 'Campo WhatsApp deve ser verdadeiro ou falso',
            'company.phone_2.max' => 'Telefone secundário deve ter no máximo 20 caracteres',
            'company.has_whatsapp_2.boolean' => 'Campo WhatsApp secundário deve ser verdadeiro ou falso',
            'company.timezone_id.required' => 'Fuso horário é obrigatório',
            'company.timezone_id.exists' => 'Fuso horário não encontrado',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'user_id' => 'usuário',
            'company.name' => 'nome da empresa',
            'company.person_type' => 'tipo de pessoa',
            'company.cnpj' => 'CNPJ',
            'company.cpf' => 'CPF',
            'company.responsible_name' => 'nome do responsável',
            'company.phone_1' => 'telefone principal',
            'company.has_whatsapp_1' => 'WhatsApp principal',
            'company.phone_2' => 'telefone secundário',
            'company.has_whatsapp_2' => 'WhatsApp secundário',
            'company.timezone_id' => 'fuso horário',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Remove máscara do CNPJ antes da validação
        if ($this->has('company.cnpj')) {
            $this->merge([
                'company' => array_merge($this->input('company', []), [
                    'cnpj' => preg_replace('/[^0-9]/', '', $this->input('company.cnpj'))
                ])
            ]);
        }

        // Remove máscara do CPF da empresa antes da validação
        if ($this->has('company.cpf')) {
            $this->merge([
                'company' => array_merge($this->input('company', []), [
                    'cpf' => preg_replace('/[^0-9]/', '', $this->input('company.cpf'))
                ])
            ]);
        }
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validar CNPJ se for pessoa jurídica
            if ($this->input('company.person_type') === 'legal' && $this->input('company.cnpj')) {
                $cnpjRule = new CnpjCpf('cnpj');
                $cnpjRule->validate('company.cnpj', $this->input('company.cnpj'), function ($message) use ($validator) {
                    $validator->errors()->add('company.cnpj', $message);
                });
            }

            // Validar CPF se for pessoa física
            if ($this->input('company.person_type') === 'physical' && $this->input('company.cpf')) {
                $cpfRule = new CnpjCpf('cpf');
                $cpfRule->validate('company.cpf', $this->input('company.cpf'), function ($message) use ($validator) {
                    $validator->errors()->add('company.cpf', $message);
                });
            }
        });
    }
}
