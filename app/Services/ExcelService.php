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
     * Import questions mapping from sheet with support for dynamic options (2 - 20).
     */
    public function parseQuestionsWithMapping(string $filePath, array $mapping): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow();

        $questions = [];
        $letters = range('A', 'T'); // 20 opsi (A s/d T)

        for ($row = 2; $row <= $highestRow; $row++) {
            $qCol = $mapping['question_col'] ?? 'A';
            $qText = trim((string) $worksheet->getCell($qCol . $row)->getCalculatedValue());
            
            if (empty($qText)) {
                continue;
            }

            // Kumpulkan opsi jawaban dinamis
            $optionsData = [];
            foreach ($letters as $letter) {
                $mapKey = 'opt_' . strtolower($letter) . '_col';
                if (!empty($mapping[$mapKey])) {
                    $colName = $mapping[$mapKey];
                    $val = trim((string) $worksheet->getCell($colName . $row)->getCalculatedValue());
                    if ($val !== '') {
                        $optionsData[$letter] = $val;
                    }
                }
            }

            // Opsi fallback jika mapping format lama
            $optA = $optionsData['A'] ?? (isset($mapping['opt_a_col']) ? trim((string)$worksheet->getCell($mapping['opt_a_col'] . $row)->getCalculatedValue()) : null);
            $optB = $optionsData['B'] ?? (isset($mapping['opt_b_col']) ? trim((string)$worksheet->getCell($mapping['opt_b_col'] . $row)->getCalculatedValue()) : null);
            $optC = $optionsData['C'] ?? (isset($mapping['opt_c_col']) ? trim((string)$worksheet->getCell($mapping['opt_c_col'] . $row)->getCalculatedValue()) : null);
            $optD = $optionsData['D'] ?? (isset($mapping['opt_d_col']) ? trim((string)$worksheet->getCell($mapping['opt_d_col'] . $row)->getCalculatedValue()) : null);
            $optE = $optionsData['E'] ?? (isset($mapping['opt_e_col']) ? trim((string)$worksheet->getCell($mapping['opt_e_col'] . $row)->getCalculatedValue()) : null);

            if (!empty($optA)) $optionsData['A'] = $optA;
            if (!empty($optB)) $optionsData['B'] = $optB;
            if (!empty($optC)) $optionsData['C'] = $optC;
            if (!empty($optD)) $optionsData['D'] = $optD;
            if (!empty($optE)) $optionsData['E'] = $optE;

            $rawKey = strtoupper(trim((string) $worksheet->getCell(($mapping['key_col'] ?? 'G') . $row)->getCalculatedValue()));
            // Bersihkan jika user menulis "A. Jawaban", ambil hurufnya
            $correctKey = 'A';
            if (preg_match('/^[A-T]/', $rawKey, $matches)) {
                $correctKey = $matches[0];
            } elseif (is_numeric($rawKey) && (int)$rawKey >= 1 && (int)$rawKey <= 20) {
                $correctKey = $letters[(int)$rawKey - 1] ?? 'A';
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
                'options_data' => $optionsData,
                'correct_answer' => $correctKey,
                'points' => $points,
            ];
        }

        return $questions;
    }

    /**
     * Generate template Excel download for Quiz questions, menyesuaikan jumlah pilihan ganda (2 s/d 20).
     */
    public function generateTemplate(int $optionsCount = 5): string
    {
        $optionsCount = max(2, min(20, $optionsCount));
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Soal Kuis');

        $letters = range('A', 'T');
        $headers = [];

        // Kolom 1: Soal
        $headers['A1'] = 'Soal Pertanyaan';

        // Kolom Opsi (B1, C1, dst)
        for ($i = 0; $i < $optionsCount; $i++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 2);
            $optionKey = $letters[$i];
            $headers[$colLetter . '1'] = 'Pilihan ' . $optionKey;
        }

        // Kolom Kunci Jawaban
        $keyCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($optionsCount + 2);
        $firstOpt = $letters[0];
        $lastOpt = $letters[$optionsCount - 1];
        $headers[$keyCol . '1'] = "Kunci Jawaban ($firstOpt-$lastOpt)";

        // Kolom Bobot Poin
        $pointsCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($optionsCount + 3);
        $headers[$pointsCol . '1'] = 'Bobot Poin';

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        // Header Styling - Pastel Sage
        $sheet->getStyle("A1:{$pointsCol}1")->applyFromArray([
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

        // Contoh baris dinamis
        $sampleQuestions = [
            [
                'q' => 'Ibu kota negara Indonesia di masa depan yang berlokasi di Kalimantan Timur adalah...',
                'opts' => ['Nusantara', 'Balikpapan', 'Samarinda', 'Banjarmasin', 'Pontianak', 'Tarakan', 'Palangkaraya'],
                'key' => 'A',
                'points' => 10,
            ],
            [
                'q' => 'Lagu kebangsaan Indonesia Raya diciptakan oleh komponis nasional...',
                'opts' => ['Ismail Marzuki', 'W.R. Soepratman', 'Kusbini', 'C. Simanjuntak', 'L. Manik', 'Gesang', 'Cornel Simanjuntak'],
                'key' => 'B',
                'points' => 10,
            ],
            [
                'q' => 'Alat musik tradisional yang terbuat dari bambu dan berasal dari Jawa Barat adalah...',
                'opts' => ['Gamelan', 'Sasando', 'Angklung', 'Kolintang', 'Tifa', 'Saluang', 'Calung'],
                'key' => 'C',
                'points' => 10,
            ],
        ];

        $rowNum = 2;
        foreach ($sampleQuestions as $item) {
            $sheet->setCellValue("A$rowNum", $item['q']);
            
            for ($i = 0; $i < $optionsCount; $i++) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 2);
                $optText = $item['opts'][$i] ?? ('Opsi ' . $letters[$i]);
                $sheet->setCellValue($colLetter . $rowNum, $optText);
            }

            // Kunci & Poin
            $key = $item['key'];
            if (!in_array($key, array_slice($letters, 0, $optionsCount))) {
                $key = $firstOpt;
            }
            $sheet->setCellValue($keyCol . $rowNum, $key);
            $sheet->setCellValue($pointsCol . $rowNum, $item['points']);
            $sheet->getStyle("{$keyCol}$rowNum:{$pointsCol}$rowNum")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $rowNum++;
        }

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(45);
        for ($i = 0; $i < $optionsCount; $i++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 2);
            $sheet->getColumnDimension($colLetter)->setWidth(22);
        }
        $sheet->getColumnDimension($keyCol)->setWidth(24);
        $sheet->getColumnDimension($pointsCol)->setWidth(15);

        $tempPath = Storage::path("template_quiz_{$optionsCount}_opsi.xlsx");
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
        $sheet->setCellValue('A2', 'Kode Kuis: ' . $quiz->code . ' | Total Peserta: ' . $participants->count() . ' | Tanggal Unduh: ' . now()->format('d M Y H:i') . ' WIB');
        
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('2D3748');
        $sheet->getStyle('A2')->getFont()->setSize(10)->getColor()->setRGB('718096');

        // Headers
        $headers = [
            'A4' => 'Peringkat',
            'B4' => 'Nama Peserta',
            'C4' => 'No. Absen',
            'D4' => 'Kelas',
            'E4' => 'Email / No. HP / Instansi',
            'F4' => 'Status',
            'G4' => 'Skor Akhir',
            'H4' => 'Benar',
            'I4' => 'Terjawab',
            'J4' => 'Total Soal',
            'K4' => 'Waktu Mulai',
            'L4' => 'Waktu Selesai',
            'M4' => 'Durasi Pengerjaan',
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        // Header Styling - Pastel Blue/Lavish
        $sheet->getStyle('A4:M4')->applyFromArray([
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
            $sheet->setCellValue("C$rowNum", $p->attendance_number ?: '-');
            $sheet->setCellValue("D$rowNum", $p->class_name ?: '-');
            $sheet->setCellValue("E$rowNum", $p->identifier ?: '-');
            $sheet->setCellValue("F$rowNum", $p->status === 'completed' ? 'Selesai' : 'Mengerjakan');
            $sheet->setCellValue("G$rowNum", round($p->score, 1));
            $sheet->setCellValue("H$rowNum", $p->total_correct);
            $sheet->setCellValue("I$rowNum", $p->total_answered);
            $sheet->setCellValue("J$rowNum", $p->total_questions ?: $quiz->questions->count());
            $sheet->setCellValue("K$rowNum", $p->started_at ? $p->started_at->format('d/m/Y H:i:s') : '-');
            $sheet->setCellValue("L$rowNum", $p->finished_at ? $p->finished_at->format('d/m/Y H:i:s') : '-');
            $sheet->setCellValue("M$rowNum", $durationStr);

            // Row styling
            $sheet->getStyle("A$rowNum")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C$rowNum:D$rowNum")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F$rowNum:J$rowNum")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("K$rowNum:M$rowNum")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Zebra striping soft pastel
            if ($rowNum % 2 == 0) {
                $sheet->getStyle("A$rowNum:M$rowNum")->getFill()->applyFromArray([
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F9FBFC'],
                ]);
            }

            $sheet->getStyle("A$rowNum:M$rowNum")->getBorders()->getAllBorders()->applyFromArray([
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => 'E2E8F0'],
            ]);

            $rowNum++;
        }

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(28);
        $sheet->getColumnDimension('C')->setWidth(14);
        $sheet->getColumnDimension('D')->setWidth(16);
        $sheet->getColumnDimension('E')->setWidth(26);
        $sheet->getColumnDimension('F')->setWidth(16);
        $sheet->getColumnDimension('G')->setWidth(14);
        $sheet->getColumnDimension('H')->setWidth(12);
        $sheet->getColumnDimension('I')->setWidth(12);
        $sheet->getColumnDimension('J')->setWidth(12);
        $sheet->getColumnDimension('K')->setWidth(20);
        $sheet->getColumnDimension('L')->setWidth(20);
        $sheet->getColumnDimension('M')->setWidth(22);

        $tempPath = Storage::path('hasil_kuis_' . $quiz->code . '_' . date('Ymd_His') . '.xlsx');
        if (!is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0777, true);
        }
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return $tempPath;
    }

    /**
     * Export hasil formulasi gabungan beberapa kuis ke Excel.
     */
    public function exportCombinedResults(string $title, array $quizzes, array $data): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Hasil Akhir');

        // Judul
        $sheet->setCellValue('A1', strtoupper($title));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('2F4858'));
        $sheet->setCellValue('A2', 'Diekspor pada: ' . date('d F Y H:i:s'));
        $sheet->getStyle('A2')->getFont()->setSize(10)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748B'));

        // Header Table di Baris 4
        $headers = ['Peringkat', 'Nama Peserta'];
        foreach ($quizzes as $q) {
            $headers[] = $q['title'] . ' (' . ($q['weight'] ?? 0) . '%)';
        }
        $headers[] = 'Nilai Akhir';
        $headers[] = 'Status';

        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        
        foreach ($headers as $idx => $h) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1);
            $sheet->setCellValue($colLetter . '4', $h);
        }

        // Style header
        $sheet->getStyle("A4:{$lastColLetter}4")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '2F4858'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E6EFEA'],
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
        $sheet->getRowDimension(4)->setRowHeight(28);

        // Data Rows
        $rowNum = 5;
        foreach ($data as $item) {
            $sheet->setCellValue("A$rowNum", $item['rank'] ?? ($rowNum - 4));
            $sheet->setCellValue("B$rowNum", $item['name'] ?? '-');

            $colIdx = 3;
            foreach ($quizzes as $q) {
                $qId = $q['id'];
                $score = $item['quiz_scores'][$qId] ?? 0;
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $sheet->setCellValue($colLetter . $rowNum, round((float)$score, 2));
                $sheet->getStyle($colLetter . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $colIdx++;
            }

            // Nilai Akhir
            $finalScoreCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
            $sheet->setCellValue($finalScoreCol . $rowNum, round((float)($item['final_score'] ?? 0), 2));
            $sheet->getStyle($finalScoreCol . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($finalScoreCol . $rowNum)->getFont()->setBold(true);

            // Status
            $statusCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
            $status = ($item['final_score'] ?? 0) >= 70 ? 'Lulus' : 'Remedial';
            $sheet->setCellValue($statusCol . $rowNum, $status);
            $sheet->getStyle($statusCol . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Align Center A
            $sheet->getStyle("A$rowNum")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Zebra
            if ($rowNum % 2 == 0) {
                $sheet->getStyle("A$rowNum:{$lastColLetter}$rowNum")->getFill()->applyFromArray([
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F9FBFC'],
                ]);
            }

            $sheet->getStyle("A$rowNum:{$lastColLetter}$rowNum")->getBorders()->getAllBorders()->applyFromArray([
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => 'E2E8F0'],
            ]);

            $rowNum++;
        }

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(30);
        for ($c = 3; $c <= count($headers); $c++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
            $sheet->getColumnDimension($colLetter)->setWidth(20);
        }

        $tempPath = Storage::path('formulasi_hasil_' . date('Ymd_His') . '.xlsx');
        if (!is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0777, true);
        }
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return $tempPath;
    }
}
