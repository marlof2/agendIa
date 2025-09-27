<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\Ability;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Http\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Dompdf\Dompdf;
use Dompdf\Options;

class ProfileController extends Controller
{
    /**
     * Get all profiles
     */
    public function index(Request $request): JsonResponse
    {
        $query = Profile::with('abilities');

        // Filter by search term
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('display_name', 'like', "%{$search}%");
        }

        // Pagination
        $perPage = $request->get('per_page', 12);
        $profiles = $query->paginate($perPage);

        return response()->json($profiles);
    }

    /**
     * Get a specific profile
     */
    public function show(Profile $profile): JsonResponse
    {
        $profile->load('abilities');

        return response()->json([
            'success' => true,
            'data' => $profile
        ]);
    }

    /**
     * Create a new profile
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:profiles,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'abilities' => 'array',
            'abilities.*' => 'exists:abilities,id'
        ]);

        $profile = Profile::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
        ]);

        if ($request->has('abilities')) {
            $profile->abilities()->attach($request->abilities);
        }

        $profile->load('abilities');

        return response()->json([
            'success' => true,
            'message' => 'Perfil criado com sucesso',
            'data' => $profile
        ], 201);
    }

    /**
     * Update a profile
     */
    public function update(Request $request, Profile $profile): JsonResponse
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('profiles', 'name')->ignore($profile->id)
            ],
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'abilities' => 'array',
            'abilities.*' => 'exists:abilities,id'
        ]);

        $profile->update([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
        ]);

        if ($request->has('abilities')) {
            $profile->abilities()->sync($request->abilities);
        }

        $profile->load('abilities');

        return response()->json([
            'success' => true,
            'message' => 'Perfil atualizado com sucesso',
            'data' => $profile
        ]);
    }

    /**
     * Delete a profile
     */
    public function destroy(Profile $profile): JsonResponse
    {
        // Verificar se o perfil está sendo usado por algum usuário
        if ($profile->users()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível deletar um perfil que está sendo usado por usuários'
            ], 422);
        }

        $profile->abilities()->detach();
        $profile->delete();

        return response()->json([
            'success' => true,
            'message' => 'Perfil deletado com sucesso'
        ]);
    }

    /**
     * Get all abilities
     */
    public function abilities(): JsonResponse
    {
        $abilities = Ability::get();

        return response()->json([
            'success' => true,
            'data' => $abilities
        ]);
    }

    /**
     * Update profile abilities
     */
    public function updateAbilities(Request $request, Profile $profile): JsonResponse
    {
        $request->validate([
            'abilities' => 'required|array',
            'abilities.*' => 'exists:abilities,id'
        ]);

        $profile->abilities()->sync($request->abilities);
        $profile->load('abilities');

        return response()->json([
            'success' => true,
            'message' => 'Permissões do perfil atualizadas com sucesso',
            'data' => $profile
        ]);
    }

    /**
     * Export profiles to Excel or PDF
     */
    public function export(Request $request)
    {
        $request->validate([
            'format' => 'required|in:excel,pdf'
        ]);

        $format = $request->format;
        // Build query with filters
        $query = Profile::with('abilities');

        // Filter by search term
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('display_name', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        $profiles = $query->get();

        if ($format === 'excel') {
            return $this->exportToExcel($profiles);
        } else {
            return $this->exportToPDF($profiles);
        }
    }

    /**
     * Export to Excel
     */
    private function exportToExcel($profiles)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Perfis');

        // Headers
        $headers = ['ID', 'Nome', 'Nome de Exibição', 'Descrição', 'Permissões', 'Criado em', 'Atualizado em'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        // Style headers
        $headerRange = 'A1:' . chr(ord('A') + count($headers) - 1) . '1';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e293b']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Data
        $row = 2;
        foreach ($profiles as $profile) {
            // Mostrar apenas a quantidade de permissões
            $abilitiesCount = $profile->abilities->count();
            $abilities = $abilitiesCount > 0 ? "{$abilitiesCount} permissões" : 'Nenhuma permissão';

            $sheet->setCellValue('A' . $row, $profile->id);
            $sheet->setCellValue('B' . $row, $profile->name);
            $sheet->setCellValue('C' . $row, $profile->display_name);
            $sheet->setCellValue('D' . $row, $profile->description ?? '');
            $sheet->setCellValue('E' . $row, $abilities);
            $sheet->setCellValue('F' . $row, $profile->created_at->format('d/m/Y H:i'));
            $sheet->setCellValue('G' . $row, $profile->updated_at->format('d/m/Y H:i'));
            $row++;
        }

        // Auto size columns
        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Create writer and save to temporary file
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'profiles_export_');
        $writer->save($tempFile);

        return response()->download($tempFile, 'perfis.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Export to PDF
     */
    private function exportToPDF($profiles)
    {
        $html = $this->generatePDFHtml($profiles);

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="perfis.pdf"'
        ]);
    }

    /**
     * Generate HTML for PDF
     */
    private function generatePDFHtml($profiles): string
    {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Relatório de Perfis</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .header h1 { color: #1e293b; margin: 0; }
                .header p { color: #64748b; margin: 5px 0; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th { background-color: #1e293b; color: white; padding: 12px; text-align: left; }
                td { padding: 10px; border-bottom: 1px solid #e5e7eb; }
                tr:nth-child(even) { background-color: #f9fafb; }
                .abilities { max-width: 200px; word-wrap: break-word; }
                .footer { margin-top: 30px; text-align: center; color: #64748b; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Relatório de Perfis</h1>
                <p>AgendIA - Sistema de Agendamentos</p>
                <p>Gerado em: ' . now()->format('d/m/Y H:i') . '</p>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Nome de Exibição</th>
                        <th>Descrição</th>
                        <th>Permissões</th>
                        <th>Criado em</th>
                        <th>Atualizado em</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($profiles as $profile) {
            // Mostrar apenas a quantidade de permissões
            $abilitiesCount = $profile->abilities->count();
            $abilities = $abilitiesCount > 0 ? "{$abilitiesCount} permissões" : 'Nenhuma permissão';

            $html .= '<tr>';
            $html .= '<td>' . $profile->id . '</td>';
            $html .= '<td>' . htmlspecialchars($profile->name) . '</td>';
            $html .= '<td>' . htmlspecialchars($profile->display_name) . '</td>';
            $html .= '<td>' . htmlspecialchars($profile->description ?? '') . '</td>';
            $html .= '<td class="abilities">' . htmlspecialchars($abilities) . '</td>';
            $html .= '<td>' . $profile->created_at->format('d/m/Y H:i') . '</td>';
            $html .= '<td>' . $profile->updated_at->format('d/m/Y H:i') . '</td>';
            $html .= '</tr>';
        }

        $html .= '
                </tbody>
            </table>

            <div class="footer">
                <p>Total de perfis: ' . $profiles->count() . '</p>
                <p>AgendIA © ' . date('Y') . ' - Todos os direitos reservados</p>
            </div>
        </body>
        </html>';

        return $html;
    }
}
