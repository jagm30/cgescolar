<?php

namespace App\Services;

use App\Models\CicloEscolar;
use App\Models\Condonacion;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CondonacionesExcelExport
{
    private const ULTIMA_COL = 'I';

    private const COLS = [
        'A' => [8,  'ID'],
        'B' => [28, 'Alumno'],
        'C' => [16, 'Matrícula'],
        'D' => [24, 'Plan de pago'],
        'E' => [40, 'Motivo'],
        'F' => [14, 'Monto total'],
        'G' => [12, 'Estado'],
        'H' => [20, 'Registrado por'],
        'I' => [16, 'Fecha registro'],
    ];

    public function descargar(Request $request, int $cicloId): StreamedResponse
    {
        $ciclo = CicloEscolar::findOrFail($cicloId);
        $condonaciones = $this->consultar($request, $cicloId);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Condonaciones');

        $this->escribirTitulo($sheet, $ciclo);
        $this->escribirEncabezados($sheet);
        $this->escribirDatos($sheet, $condonaciones);
        $sheet->freezePane('A3');

        $nombre = "Condonaciones_{$ciclo->nombre}_".now()->format('Ymd_His').'.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$nombre}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }

    // ── Consulta ──────────────────────────────────────────────────────────────

    private function consultar(Request $request, int $cicloId): Collection
    {
        return Condonacion::with(['alumno', 'ciclo', 'creadoPor', 'detalles.cargo.asignacion.plan:id,nombre'])
            ->where('ciclo_id', $cicloId)
            ->when($request->filled('alumno_id'), fn ($q) => $q->where('alumno_id', $request->alumno_id))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->when($request->filled('plan_id'), fn ($q) => $q->whereHas(
                'detalles.cargo.asignacion', fn ($q) => $q->where('plan_id', $request->plan_id)
            ))
            ->orderByDesc('creado_at')
            ->get();
    }

    // ── Fila de título ────────────────────────────────────────────────────────

    private function escribirTitulo(
        Worksheet $sheet,
        CicloEscolar $ciclo
    ): void {
        $rango = 'A1:'.self::ULTIMA_COL.'1';
        $sheet->mergeCells($rango);
        $sheet->setCellValue('A1', "Reporte de Condonaciones  —  Ciclo: {$ciclo->nombre}  |  Generado: ".now()->format('d/m/Y H:i'));
        $sheet->getRowDimension(1)->setRowHeight(24);

        $sheet->getStyle($rango)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0D2E4E']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
        ]);
    }

    // ── Fila de encabezados ───────────────────────────────────────────────────

    private function escribirEncabezados(Worksheet $sheet): void
    {
        $sheet->getRowDimension(2)->setRowHeight(20);

        foreach (self::COLS as $col => [$ancho, $titulo]) {
            $sheet->getColumnDimension($col)->setWidth($ancho);
            $sheet->setCellValue("{$col}2", $titulo);
        }

        $sheet->getStyle('A2:'.self::ULTIMA_COL.'2')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FF333333'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE8EEF5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM,
                'color' => ['argb' => 'FFAABDD4']]],
        ]);
    }

    // ── Datos (desde fila 3) ──────────────────────────────────────────────────

    private function escribirDatos(
        Worksheet $sheet,
        Collection $condonaciones
    ): void {
        $fila = 3;

        foreach ($condonaciones as $cond) {
            $planes = $cond->detalles
                ->pluck('cargo.asignacion.plan.nombre')
                ->filter()->unique()->implode(', ');

            $sheet->setCellValue("A{$fila}", $cond->id);
            $sheet->setCellValue("B{$fila}", $cond->alumno->nombre_completo);
            $sheet->setCellValue("C{$fila}", $cond->alumno->matricula);
            $sheet->setCellValue("D{$fila}", $planes ?: '—');
            $sheet->setCellValue("E{$fila}", $cond->motivo);
            $sheet->setCellValue("F{$fila}", (float) $cond->monto_total);
            $sheet->setCellValue("G{$fila}", ucfirst($cond->estado));
            $sheet->setCellValue("H{$fila}", $cond->creadoPor?->nombre ?? '—');
            $sheet->setCellValue("I{$fila}", $cond->creado_at?->format('d/m/Y H:i'));

            $sheet->getStyle("F{$fila}")->getNumberFormat()
                ->setFormatCode('"$"#,##0.00');

            if ($fila % 2 === 0) {
                $sheet->getStyle("A{$fila}:".self::ULTIMA_COL."{$fila}")
                    ->getFill()->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF5F8FC');
            }

            $fila++;
        }

        if ($fila > 3) {
            $sheet->getStyle('A3:'.self::ULTIMA_COL.($fila - 1))->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FFD8E4EF']],
                ],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
        }
    }
}
