<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Support\Facades\Storage;

class ExcelService
{
    /**
     * Parse excel/csv file and return header row & preview data rows.
     */
    public function getPreview(string $filePath, int $previewRows = 5): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();
        $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        $headers = [];
        for ($col = 1; $col <= $highestColIndex; $col++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $cellValue = trim((string) $worksheet->getCell($colLetter . '1')->getCalculatedValue());
            $headers[$colLetter] = $cellValue ?: "Kolom $colLetter";
        }

        $rows = [];
        $maxPreview = min($highestRow, $previewRows + 1);
        for ($row = 2; $row <= $maxPreview; $row++) {
            $rowData = [];
            foreach ($headers as $colLetter => $headerName) {
                $rowData[$colLetter] = (string) $worksheet->getCell($colLetter . $row)->getCalculatedValue();
            }
            $rows[] = $rowData;
        }

        return [
            'headers' => $headers,
            'preview_rows' => $rows,
            'total_rows' => max(0, $highestRow - 1),
        ];
    }

    /**
     * Import questions mapping from sheet.
     */
    public function parseQuestionsWithMapping(string $filePath, array $mapping): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow();

        $questions = [];

        for ($row = 2; $row <= $highestRow; $row++) {
            $qCol = $mapping['question_col'] ?? 'A';
            $qText = trim((string) $worksheet->getCell($qCol . $row)->getCalculatedValue());
            
            if (empty($qText)) {
                continue;
            }

            $optA = trim((string) $worksheet->getCell(($mapping['opt_a_col'] ?? 'B') . $row)->getCalculatedValue());
            $optB = trim((string) $worksheet->getCell(($mapping['opt_b_col'] ?? 'C') . $row)->getCalculatedValue());
            $optC = isset($mapping['opt_c_col']) && $mapping['opt_c_col'] ? trim((string) $worksheet->getCell($mapping['opt_c_col'] . $row)->getCalculatedValue()) : null;
            $optD = isset($mapping['opt_d_col']) && $mapping['opt_d_col'] ? trim((string) $worksheet->getCell($mapping['opt_d_col'] . $row)->getCalculatedValue()) : null;
            $optE = isset($mapping['opt_e_col']) && $mapping['opt_e_col'] ? trim((string) $worksheet->getCell($mapping['opt_e_col'] . $row)->getCalculatedValue()) : null;

            $rawKey = strtoupper(trim((string) $worksheet->getCell(($mapping['key_col'] ?? 'F') . $row)->getCalculatedValue()));
            // Bersihkan jika user menulis "A. Jawaban", ambil huruf pertamanya saja jika valid
            $correctKey = 'A';
            if (preg_match('/^[A-E]/', $rawKey, $matches)) {
                $correctKey = $matches[0];
            } elseif (in_array(strtoupper($rawKey), ['1', '2', '3', '4', '5'])) {
                $mapNum = ['1' => 'A', '2' => 'B', '3' => 'C', '4' => 'D', '5' => 'E'];
                $correctKey = $mapNum[$rawKey];
            }

            $points = isset($mapping['points_col']) && $mapping['points_col'] 
                ? (int) $worksheet->getCell($mapping['points_col'] . $row)->getCalculatedValue() 
                : 10;
            if ($points <= 0) $points = 10;

            $questions[] = [
                'question_text' => $qText,
                'option_a' => $optA,
                'option_b' => $optB,
                'option_c' => $optC,
                'option_d' => $optD,
                'option_e' => $optE,
                'correct_answer' => $correctKey,
                'points' => $points,
            ];
        }

        return $questions;
    }

    /**
     * Generate template Excel download for Quiz questions.
     */
    public function generateTemplate(): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Soal Kuis');

        // Headers
        $headers = [
            'A1' => 'Soal Pertanyaan',
            'B1' => 'Pilihan A',
            'C1' => 'Pilihan B',
            'D1' => 'Pilihan C',
            'E1' => 'Pilihan D',
            'F1' => 'Pilihan E',
            'G1' => 'Kunci Jawaban (A/B/C/D/E)',
            'H1' => 'Bobot Poin',
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        // Header Styling - Pastel Sage
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '2F4858'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E1ECE5'], // pastel sage
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'BDD3C5'],
                ],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Contoh baris
        $examples = [
            [
                'Ibu kota negara Indonesia di masa depan yang berlokasi di Kalimantan Timur adalah...',
                'Nusantara',
                'Balikpapan',
                'Samarinda',
                'Banjarmasin',
                'Pontianak',
                'A',
                10
            ],
            [
                'Lagu kebangsaan Indonesia Raya diciptakan oleh komponis...',
                'Ismail Marzuki',
                'W.R. Soepratman',
                'Kusbini',
                'C. Simanjuntak',
                'L. Manik',
                'B',
                10
            ],
            [
                'Alat musik tradisional yang terbuat dari bambu dan berasal dari Jawa Barat adalah...',
                'Gamelan',
                'Sasando',
                'Angklung',
                'Kolintang',
                'Tifa',
                'C',
                10
            ],
        ];

        $rowNum = 2;
        foreach ($examples as $row) {
            $sheet->setCellValue("A$rowNum", $row[0]);
            $sheet->setCellValue("B$rowNum", $row[1]);
            $sheet->setCellValue("C$rowNum", $row[2]);
            $sheet->setCellValue("D$rowNum", $row[3]);
            $sheet->setCellValue("E$rowNum", $row[4]);
            $sheet->setCellValue("F$rowNum", $row[5]);
            $sheet->setCellValue("G$rowNum", $row[6]);
            $sheet->setCellValue("H$rowNum", $row[7]);
            $sheet->getStyle("G$rowNum:H$rowNum")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $rowNum++;
        }

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(45);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(25);
        $sheet->getColumnDimension('F')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(24);
        $sheet->getColumnDimension('H')->setWidth(15);

        $tempPath = Storage::path('template_quiz.xlsx');
        if (!is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0777, true);
        }
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return $tempPath;
    }

    /**
     * Export Quiz Participants & Results to Excel.
     */
    public function exportQuizResults($quiz, $participants): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Hasil Kuis');

        // Title Info
        $sheet->setCellValue('A1', 'REKAPITULASI HASIL KUIS: ' . strtoupper($quiz->title));
        $sheet->setCellValue('A2', 'Kode Kuis: ' . $quiz->code . ' | Total Peserta: ' . $participants->count() . ' | Tanggal Unduh: ' . now()->format('d M Y H:i'));
        
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('2D3748');
        $sheet->getStyle('A2')->getFont()->setSize(10)->getColor()->setRGB('718096');

        // Headers
        $headers = [
            'A4' => 'Peringkat',
            'B4' => 'Nama Peserta',
            'C4' => 'Status',
            'D4' => 'Skor Akhir',
            'E4' => 'Benar',
            'F4' => 'Terjawab',
            'G4' => 'Total Soal',
            'H4' => 'Waktu Mulai',
            'I4' => 'Waktu Selesai',
            'J4' => 'Durasi Pengerjaan',
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        // Header Styling - Pastel Blue/Lavish
        $sheet->getStyle('A4:J4')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '2B4C5F'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E3EEF8'], // soft pastel blue
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'C9DCED'],
                ],
            ],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(26);

        $rowNum = 5;
        $rank = 1;
        foreach ($participants as $p) {
            $durationStr = '-';
            if ($p->started_at && $p->finished_at) {
                $durationStr = $p->started_at->diff($p->finished_at)->format('%i mnt %s dtk');
            } elseif ($p->started_at) {
                $durationStr = 'Sedang berjalan (' . $p->started_at->diffForHumans() . ')';
            }

            $sheet->setCellValue("A$rowNum", $rank++);
            $sheet->setCellValue("B$rowNum", $p->name);
            $sheet->setCellValue("C$rowNum", $p->status === 'completed' ? 'Selesai' : 'Mengerjakan');
            $sheet->setCellValue("D$rowNum", round($p->score, 1));
            $sheet->setCellValue("E$rowNum", $p->total_correct);
            $sheet->setCellValue("F$rowNum", $p->total_answered);
            $sheet->setCellValue("G$rowNum", $p->total_questions ?: $quiz->questions->count());
            $sheet->setCellValue("H$rowNum", $p->started_at ? $p->started_at->format('d/m/Y H:i:s') : '-');
            $sheet->setCellValue("I$rowNum", $p->finished_at ? $p->finished_at->format('d/m/Y H:i:s') : '-');
            $sheet->setCellValue("J$rowNum", $durationStr);

            // Row styling
            $sheet->getStyle("A$rowNum")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C$rowNum:G$rowNum")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H$rowNum:J$rowNum")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Zebra striping soft pastel
            if ($rowNum % 2 == 0) {
                $sheet->getStyle("A$rowNum:J$rowNum")->getFill()->applyFromArray([
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F9FBFC'],
                ]);
            }

            $sheet->getStyle("A$rowNum:J$rowNum")->getBorders()->getAllBorders()->applyFromArray([
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => 'E2E8F0'],
            ]);

            $rowNum++;
        }

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(28);
        $sheet->getColumnDimension('C')->setWidth(16);
        $sheet->getColumnDimension('D')->setWidth(14);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(20);
        $sheet->getColumnDimension('I')->setWidth(20);
        $sheet->getColumnDimension('J')->setWidth(22);

        $tempPath = Storage::path('hasil_kuis_' . $quiz->code . '_' . date('Ymd_His') . '.xlsx');
        if (!is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0777, true);
        }
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return $tempPath;
    }
}
