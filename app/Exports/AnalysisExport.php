<?php

namespace App\Exports;

use App\Models\Analysis;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AnalysisExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    ShouldAutoSize
{
    use Exportable;

    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        return Analysis::query()
            ->filtered($this->filters)
            ->orderBy('date')
            ->orderBy('id');
    }

    public function headings(): array
    {
        return [
            'Date',
            'Sample Name',
            'Batch No',
            'Analysis',
            'Pressure',
            'Column',
            'Instrument',
            'Std Concentration',
            'Std Area',
            'Sample Concentration',
            'Sample Area',
            'Purity %',
            'Result',
            'Done By / Reviewed By',
            'Remarks',
        ];
    }

    public function map($row): array
    {
        return [
            $row->date?->format('Y-m-d'),
            $row->sample_name,
            $row->batch_no,
            $row->analysis,
            $row->pressure,
            $row->column_name,
            $row->instrument,
            $row->std_con,
            $row->std_area,
            $row->sample_con,
            $row->sample_area,
            $row->purity,
            $row->result,
            $row->done_by,
            $row->remarks,
        ];
    }

    public function title(): string
    {
        return 'QC Analysis Data';
    }

    public function styles(Worksheet $sheet): array
    {
        // Header row style
        $headerStyle = [
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size'  => 11,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'], // indigo
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => '6366f1'],
                ],
            ],
        ];

        // Data rows style
        $dataStyle = [
            'font' => ['size' => 10],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => 'D1D5DB'],
                ],
            ],
        ];

        $lastRow = $sheet->getHighestRow();

        return [
            1             => $headerStyle,
            "2:$lastRow"  => $dataStyle,
        ];
    }

    /**
     * After writing, apply extra formatting (purity column colour, row height).
     */
    public function registerEvents(): array
    {
        return [
            \Maatwebsite\Excel\Events\AfterSheet::class => function(\Maatwebsite\Excel\Events\AfterSheet $event) {
                $sheet    = $event->sheet->getDelegate();
                $lastRow  = $sheet->getHighestRow();

                // Row height for header
                $sheet->getRowDimension(1)->setRowHeight(22);

                // Freeze top row
                $sheet->freezePane('A2');

                // Colour-code Purity column (L = col 12)
                for ($r = 2; $r <= $lastRow; $r++) {
                    $purity = (float) $sheet->getCell("L$r")->getValue();
                    if ($purity <= 0) continue;
                    $color = $purity >= 99 ? '16a34a' : ($purity >= 95 ? 'd97706' : 'dc2626');
                    $sheet->getStyle("L$r")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => $color]],
                    ]);
                }

                // Auto-filter on header row
                $sheet->setAutoFilter("A1:O1");
            },
        ];
    }
}
