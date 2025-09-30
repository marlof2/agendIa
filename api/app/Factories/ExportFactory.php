<?php

namespace App\Factories;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Dompdf\Dompdf;
use Dompdf\Options;

class ExportFactory
{
    /**
     * Exportar dados para Excel
     */
    public static function exportToExcel(
        array $data,
        array $headers,
        string $filename = 'export',
        string $title = 'Relatório'
    ): BinaryFileResponse {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Configurar título
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:' . chr(65 + count($headers) - 1) . '1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Configurar cabeçalhos
        $headerRow = 3;
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $headerRow, $header['label']);
            $sheet->getStyle($col . $headerRow)->getFont()->setBold(true);
            $sheet->getStyle($col . $headerRow)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E3F2FD');
            $sheet->getStyle($col . $headerRow)->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
            $col++;
        }

        // Adicionar dados
        $row = $headerRow + 1;
        foreach ($data as $item) {
            $col = 'A';
            foreach ($headers as $header) {
                $value = self::getNestedValue($item, $header['key']);
                $sheet->setCellValue($col . $row, $value);
                $sheet->getStyle($col . $row)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                $col++;
            }
            $row++;
        }

        // Auto-ajustar largura das colunas
        foreach (range('A', chr(65 + count($headers) - 1)) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Salvar arquivo temporário
        $tempFile = tempnam(sys_get_temp_dir(), 'export_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        return response()->download($tempFile, $filename . '.xlsx')->deleteFileAfterSend(true);
    }

    /**
     * Exportar dados para PDF
     */
    public static function exportToPDF(
        array $data,
        array $headers,
        string $filename = 'export',
        string $title = 'Relatório'
    ): Response {
        $html = self::generatePDFHtml($data, $headers, $title);

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.pdf"',
        ]);
    }

    /**
     * Gerar HTML para PDF
     */
    private static function generatePDFHtml(array $data, array $headers, string $title): string
    {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>' . htmlspecialchars($title) . '</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .title { font-size: 24px; font-weight: bold; color: #1976d2; margin-bottom: 10px; }
                .date { font-size: 12px; color: #666; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th { background-color: #e3f2fd; color: #1976d2; font-weight: bold; padding: 12px 8px; text-align: left; border: 1px solid #ddd; }
                td { padding: 10px 8px; border: 1px solid #ddd; }
                tr:nth-child(even) { background-color: #f9f9f9; }
                tr:hover { background-color: #f5f5f5; }
                .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="title">' . htmlspecialchars($title) . '</div>
                <div class="date">Gerado em: ' . date('d/m/Y H:i:s') . '</div>
            </div>

            <table>
                <thead>
                    <tr>';

        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars($header['label']) . '</th>';
        }

        $html .= '
                    </tr>
                </thead>
                <tbody>';

        foreach ($data as $item) {
            $html .= '<tr>';
            foreach ($headers as $header) {
                $value = self::getNestedValue($item, $header['key']);
                $html .= '<td>' . htmlspecialchars($value) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '
                </tbody>
            </table>

            <div class="footer">
                <p>Total de registros: ' . count($data) . '</p>
            </div>
        </body>
        </html>';

        return $html;
    }

    /**
     * Obter valor aninhado de um array/objeto
     */
    private static function getNestedValue($data, string $key): string
    {
        if (is_array($data)) {
            $keys = explode('.', $key);
            $value = $data;

            foreach ($keys as $k) {
                if (isset($value[$k])) {
                    $value = $value[$k];
                } else {
                    return '';
                }
            }

            return is_array($value) ? implode(', ', $value) : (string) $value;
        }

        if (is_object($data)) {
            return (string) $data->{$key} ?? '';
        }

        return '';
    }
}
