<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Timezone;
use App\Factories\ExportFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CompanyService
{
    /**
     * Get all companies with pagination and search
     */
    public function getAllCompanies(array $filters = []): LengthAwarePaginator
    {
        $query = Company::with('timezone');

        // Filter by search term
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('cnpj', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $filters['per_page'] ?? 12;

        return $query->paginate($perPage);
    }

    /**
     * Get a specific company by ID
     */
    public function getCompanyById(int $id): Company
    {
        return Company::with('timezone')->findOrFail($id);
    }

    /**
     * Clean mask from field value
     */
    private function cleanMask(string $value): string
    {
        return preg_replace('/[^0-9]/', '', $value);
    }

    /**
     * Clean data before saving
     */
    private function cleanCompanyData(array $data): array
    {
        $cleaned = $data;

        // Clean CNPJ if present
        if (isset($cleaned['cnpj']) && $cleaned['cnpj']) {
            $cleaned['cnpj'] = $this->cleanMask($cleaned['cnpj']);
        }

        // Clean CPF if present
        if (isset($cleaned['cpf']) && $cleaned['cpf']) {
            $cleaned['cpf'] = $this->cleanMask($cleaned['cpf']);
        }

        // Clean phone numbers
        if (isset($cleaned['phone_1']) && $cleaned['phone_1']) {
            $cleaned['phone_1'] = $this->cleanMask($cleaned['phone_1']);
        }

        if (isset($cleaned['phone_2']) && $cleaned['phone_2']) {
            $cleaned['phone_2'] = $this->cleanMask($cleaned['phone_2']);
        }

        return $cleaned;
    }

    /**
     * Create a new company
     */
    public function createCompany(array $data): Company
    {
        $cleanedData = $this->cleanCompanyData($data);

        $company = Company::create([
            'name' => $cleanedData['name'],
            'person_type' => $cleanedData['person_type'],
            'cnpj' => $cleanedData['cnpj'] ?? null,
            'cpf' => $cleanedData['cpf'] ?? null,
            'responsible_name' => $cleanedData['responsible_name'],
            'phone_1' => $cleanedData['phone_1'],
            'has_whatsapp_1' => $cleanedData['has_whatsapp_1'] ?? false,
            'phone_2' => $cleanedData['phone_2'] ?? null,
            'has_whatsapp_2' => $cleanedData['has_whatsapp_2'] ?? false,
            'timezone_id' => $cleanedData['timezone_id'] ?? null,
        ]);

        return $company->load('timezone');
    }

    /**
     * Update an existing company
     */
    public function updateCompany(Company $company, array $data): Company
    {
        $cleanedData = $this->cleanCompanyData($data);

        $company->update([
            'name' => $cleanedData['name'],
            'person_type' => $cleanedData['person_type'],
            'cnpj' => $cleanedData['cnpj'] ?? null,
            'cpf' => $cleanedData['cpf'] ?? null,
            'responsible_name' => $cleanedData['responsible_name'],
            'phone_1' => $cleanedData['phone_1'],
            'has_whatsapp_1' => $cleanedData['has_whatsapp_1'] ?? false,
            'phone_2' => $cleanedData['phone_2'] ?? null,
            'has_whatsapp_2' => $cleanedData['has_whatsapp_2'] ?? false,
            'timezone_id' => $cleanedData['timezone_id'] ?? null,
        ]);

        return $company->load('timezone');
    }

    /**
     * Delete a company (soft delete)
     */
    public function deleteCompany(Company $company): bool
    {
        return $company->delete();
    }


    /**
     * Export companies to Excel or PDF
     */
    public function exportToExcel(array $filters = []): BinaryFileResponse
    {
        $companies = $this->getAllCompanies($filters);

        $headers = [
            ['key' => 'name', 'label' => 'Nome da Empresa'],
            ['key' => 'person_type', 'label' => 'Tipo'],
            ['key' => 'document', 'label' => 'CNPJ/CPF'],
            ['key' => 'responsible_name', 'label' => 'Responsável'],
            ['key' => 'phone_1', 'label' => 'Telefone 1'],
            ['key' => 'phone_2', 'label' => 'Telefone 2'],
            ['key' => 'created_at', 'label' => 'Data de Criação'],
        ];

        $data = $companies->map(function ($company) {
            // Determinar o documento baseado no tipo de pessoa
            $document = '';
            if ($company->person_type === 'legal' && $company->cnpj) {
                $document = $this->formatCNPJ($company->cnpj);
            } elseif ($company->person_type === 'physical' && $company->cpf) {
                $document = $this->formatCPF($company->cpf);
            }

            // Formatar telefones com indicador de WhatsApp
            $phone1 = $company->phone_1 ? $this->formatPhone($company->phone_1) : '';
            if ($phone1 && $company->has_whatsapp_1) {
                $phone1 .= ' WhatsApp';
            }

            $phone2 = $company->phone_2 ? $this->formatPhone($company->phone_2) : '';
            if ($phone2 && $company->has_whatsapp_2) {
                $phone2 .= ' WhatsApp';
            }

            return [
                'name' => $company->name,
                'person_type' => $company->person_type === 'legal' ? 'PJ' : 'PF',
                'document' => $document,
                'responsible_name' => $company->responsible_name,
                'phone_1' => $phone1,
                'phone_2' => $phone2,
                'created_at' => $company->created_at->format('d/m/Y H:i:s'),
            ];
        });

        return ExportFactory::exportToExcel($data->toArray(), $headers, 'empresas', 'Relatório de Empresas');
    }

    /**
     * Export companies to PDF
     */
    public function exportToPDF(array $filters = []): Response
    {
        $companies = $this->getAllCompanies($filters);
        $headers = [
            ['key' => 'name', 'label' => 'Nome da Empresa'],
            ['key' => 'person_type', 'label' => 'Tipo'],
            ['key' => 'document', 'label' => 'CNPJ/CPF'],
            ['key' => 'responsible_name', 'label' => 'Responsável'],
            ['key' => 'phone_1', 'label' => 'Telefone 1'],
            ['key' => 'phone_2', 'label' => 'Telefone 2'],
            ['key' => 'created_at', 'label' => 'Data de Criação'],
        ];

        $data = $companies->map(function ($company) {
            // Determinar o documento baseado no tipo de pessoa
            $document = '';
            if ($company->person_type === 'legal' && $company->cnpj) {
                $document = $this->formatCNPJ($company->cnpj);
            } elseif ($company->person_type === 'physical' && $company->cpf) {
                $document = $this->formatCPF($company->cpf);
            }

            // Formatar telefones com símbolos de WhatsApp
            $phone1 = $company->phone_1 ? $this->formatPhone($company->phone_1) : '';
            if ($phone1 && $company->has_whatsapp_1) {
                $phone1 .= ' WhatsApp';
            }

            $phone2 = $company->phone_2 ? $this->formatPhone($company->phone_2) : '';
            if ($phone2 && $company->has_whatsapp_2) {
                $phone2 .= ' WhatsApp';
            }

            return [
                'name' => $company->name,
                'person_type' => $company->person_type === 'legal' ? 'PJ' : 'PF',
                'document' => $document,
                'responsible_name' => $company->responsible_name,
                'phone_1' => $phone1,
                'phone_2' => $phone2,
                'created_at' => $company->created_at->format('d/m/Y H:i:s'),
            ];
        });

        return ExportFactory::exportToPDF($data->toArray(), $headers, 'empresas', 'Relatório de Empresas');
    }

    /**
     * Format CNPJ for display
     */
    private function formatCNPJ(string $cnpj): string
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        return preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', '$1.$2.$3/$4-$5', $cnpj);
    }

    /**
     * Format CPF for display
     */
    private function formatCPF(string $cpf): string
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        return preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', $cpf);
    }

    /**
     * Format phone for display
     */
    private function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($phone) === 11) {
            return preg_replace('/^(\d{2})(\d{5})(\d{4})$/', '($1) $2-$3', $phone);
        } elseif (strlen($phone) === 10) {
            return preg_replace('/^(\d{2})(\d{4})(\d{4})$/', '($1) $2-$3', $phone);
        }

        return $phone;
    }
}
