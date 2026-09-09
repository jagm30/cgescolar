<?php

namespace App\Services;

use App\Models\Grupo;
use App\Models\Setting;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GrupoListaExcelExport
{
    // Paleta discreta: azul marino / gris / blanco
    private const COLOR_NAVY    = 'FF1E3A5F';
    private const COLOR_MID     = 'FF2C5282';
    private const COLOR_DIAS    = 'FF2D6A9F';
    private const COLOR_MES     = 'FF3A7BBF';   // fila mes/año (un tono más claro)
    private const COLOR_COL_BG  = 'FFE9EEF4';
    private const COLOR_ALTERNA = 'FFF4F7FA';
    private const COLOR_BLANCO  = 'FFFFFFFF';
    private const COLOR_GRIS    = 'FF718096';
    private const COLOR_BORDE   = 'FFCBD5E0';

    // Columnas fijas: A(#)  B(Nombre) — días desde C en adelante
    private const COL_NUM    = 'A';
    private const COL_NOMBRE = 'B';
    private const DIAS       = 31;

    // Filas de cabecera
    private const FILA_LOGO  = 1;   // filas 1-3: logo + nombre escuela
    private const FILA_INFO  = 4;   // banda datos del grupo
    private const FILA_MES   = 5;   // mes y año (sobre los días)
    private const FILA_DIAS  = 6;   // números 1-31
    private const FILA_DATOS = 7;   // primera fila de alumnos

    public function descargar(Grupo $grupo, int $mes, int $anio): StreamedResponse
    {
        $alumnos = $grupo->inscripciones
            ->where('activo', true)
            ->sortBy(fn ($i) => $i->alumno->ap_paterno.$i->alumno->ap_materno.$i->alumno->nombre)
            ->values();

        $spreadsheet = new Spreadsheet;
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Asistencia');

        $diasCols  = $this->generarCols('C', self::DIAS);
        $ultimaCol = end($diasCols);

        $this->insertarLogo($spreadsheet, $sheet);
        $this->escribirCabecera($sheet, $grupo, $ultimaCol);
        $this->escribirBandaInfo($sheet, $grupo, $ultimaCol);
        $this->escribirFilaMes($sheet, $mes, $anio, $diasCols, $ultimaCol);
        $this->escribirEncabezadoDias($sheet, $diasCols, $ultimaCol);
        $this->escribirAlumnos($sheet, $alumnos, $diasCols, $ultimaCol);
        $this->escribirLeyenda($sheet, $alumnos->count(), $ultimaCol);

        $sheet->freezePane('C'.self::FILA_DATOS);

        $nivel   = $grupo->grado->nivel->nombre;
        $grado   = $grupo->grado->numero;
        $nombre  = $grupo->nombre;
        $mesNombre = $this->nombreMes($mes);
        $archivo = "Asistencia_{$nivel}_{$grado}o_{$nombre}_{$mesNombre}{$anio}.xlsx";

        return new StreamedResponse(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$archivo}\"",
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    // ── Logo ──────────────────────────────────────────────────────────────────

    private function insertarLogo(Spreadsheet $spreadsheet, \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): void
    {
        $setting  = Setting::first();
        $rutaLogo = $setting?->logo_ruta
            ? public_path("imgs_escuela/reportes/{$setting->logo_ruta}")
            : public_path('imgs_escuela/reportes/logo_reportes.png');

        if (! file_exists($rutaLogo)) {
            return;
        }

        $drawing = new Drawing;
        $drawing->setPath($rutaLogo);
        $drawing->setCoordinates('A'.self::FILA_LOGO);
        $drawing->setHeight(62);
        $drawing->setOffsetX(6);
        $drawing->setOffsetY(5);
        $drawing->setWorksheet($sheet);
    }

    // ── Cabecera (filas 1-3): logo + nombre escuela + título ─────────────────

    private function escribirCabecera(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        Grupo $grupo,
        string $ultimaCol
    ): void {

        foreach (['1', '2', '3'] as $r) {
            $sheet->getRowDimension($r)->setRowHeight(22);
        }

        // Espacio para el logo (columnas A-B, filas 1-3)
        $sheet->mergeCells('A1:B3');
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(38);

        // Subtítulo (fila 1-3)
        $sheet->mergeCells("C1:{$ultimaCol}3");
        $sheet->setCellValue('C1', 'LISTA DE ASISTENCIA');
        $sheet->getStyle("C1:{$ultimaCol}3")->applyFromArray([
            'font'      => ['italic' => true, 'size' => 10,
                            'color'  => ['argb' => self::COLOR_GRIS]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT,
                            'vertical'   => Alignment::VERTICAL_CENTER, 'indent' => 1],
        ]);
    }

    // ── Banda de información del grupo (fila 4) ───────────────────────────────

    private function escribirBandaInfo(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        Grupo $grupo,
        string $ultimaCol
    ): void {
        $sheet->getRowDimension(self::FILA_INFO)->setRowHeight(20);

        $nivel   = $grupo->grado->nivel->nombre;
        $grado   = $grupo->grado->numero.'°';
        $nombre  = $grupo->nombre;
        $ciclo   = $grupo->ciclo->nombre;
        $docente = $grupo->docente?->nombre_completo ?? 'Sin asignar';
        $total   = $grupo->inscripciones->where('activo', true)->count();

        $mitad = $this->colOffset('C', (int) floor($this->countCols('C', $ultimaCol) / 2));
        $sigMitad = $this->siguienteCol($mitad);

        $sheet->mergeCells("A4:{$mitad}4");
        $sheet->setCellValue('A4', "  Nivel: {$nivel}   |   Grado: {$grado}   |   Grupo: {$nombre}   |   Ciclo: {$ciclo}");

        $sheet->mergeCells("{$sigMitad}4:{$ultimaCol}4");
        $sheet->setCellValue("{$sigMitad}4", "Docente: {$docente}   |   Total alumnos: {$total}   |   Generado: ".now()->format('d/m/Y H:i').'  ');

        $sheet->getStyle("A4:{$ultimaCol}4")->applyFromArray([
            'font'      => ['size' => 9, 'color' => ['argb' => self::COLOR_BLANCO]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_MID]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle("{$sigMitad}4")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }

    // ── Fila de mes y año (fila 5) ────────────────────────────────────────────

    private function escribirFilaMes(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        int $mes,
        int $anio,
        array $diasCols,
        string $ultimaCol
    ): void {
        $sheet->getRowDimension(self::FILA_MES)->setRowHeight(16);

        $etiquetaMes = mb_strtoupper($this->nombreMes($mes)).' '.$anio;

        // Columnas fijas vacías (A y B)
        $sheet->getStyle("A".self::FILA_MES.":B".self::FILA_MES)->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_MES);

        // Mes/año sobre los días (C…última)
        $sheet->mergeCells("C".self::FILA_MES.":{$ultimaCol}".self::FILA_MES);
        $sheet->setCellValue("C".self::FILA_MES, $etiquetaMes);
        $sheet->getStyle("C".self::FILA_MES.":{$ultimaCol}".self::FILA_MES)->applyFromArray([
            'font'      => ['bold' => true, 'size' => 9, 'color' => ['argb' => self::COLOR_BLANCO]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_MES]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical'   => Alignment::VERTICAL_CENTER],
        ]);
    }

    // ── Encabezado de días (fila 6) ───────────────────────────────────────────

    private function escribirEncabezadoDias(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        array $diasCols,
        string $ultimaCol
    ): void {
        $sheet->getRowDimension(self::FILA_DIAS)->setRowHeight(20);

        $sheet->setCellValue('A'.self::FILA_DIAS, '#');
        $sheet->setCellValue('B'.self::FILA_DIAS, 'Nombre del alumno');

        foreach ($diasCols as $i => $col) {
            $sheet->getColumnDimension($col)->setWidth(4.2);
            $sheet->setCellValue("{$col}".self::FILA_DIAS, $i + 1);
        }

        $sheet->getStyle("A".self::FILA_DIAS.":{$ultimaCol}".self::FILA_DIAS)->applyFromArray([
            'font'      => ['bold' => true, 'size' => 9, 'color' => ['argb' => self::COLOR_BLANCO]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_DIAS]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical'   => Alignment::VERTICAL_CENTER],
            'borders'   => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM,
                                         'color'       => ['argb' => self::COLOR_NAVY]]],
        ]);

        $sheet->getStyle('B'.self::FILA_DIAS)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)->setIndent(1);
    }

    // ── Filas de alumnos ─────────────────────────────────────────────────────

    private function escribirAlumnos(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        \Illuminate\Support\Collection $alumnos,
        array $diasCols,
        string $ultimaCol
    ): void {
        $fila = self::FILA_DATOS;

        foreach ($alumnos as $i => $inscripcion) {
            $alumno = $inscripcion->alumno;
            $sheet->getRowDimension($fila)->setRowHeight(15);

            $sheet->setCellValue(self::COL_NUM.$fila, $i + 1);
            $sheet->setCellValue(self::COL_NOMBRE.$fila,
                trim("{$alumno->ap_paterno} {$alumno->ap_materno}, {$alumno->nombre}"));

            $sheet->getStyle(self::COL_NUM.$fila)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle(self::COL_NOMBRE.$fila)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT)->setIndent(1);
            $sheet->getStyle("A{$fila}:{$ultimaCol}{$fila}")->getFont()->setSize(9);

            if ($fila % 2 === 0) {
                $sheet->getStyle("A{$fila}:{$ultimaCol}{$fila}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_ALTERNA);
            }

            $fila++;
        }

        if ($fila > self::FILA_DATOS) {
            $sheet->getStyle("A".self::FILA_DATOS.":{$ultimaCol}".($fila - 1))->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN,
                                               'color'       => ['argb' => self::COLOR_BORDE]]],
            ]);

            // Borde derecho grueso en columna B (separa nombre de los días)
            $sheet->getStyle("B".self::FILA_DIAS.":B".($fila - 1))->getBorders()
                ->getRight()->setBorderStyle(Border::BORDER_MEDIUM)
                ->getColor()->setARGB(self::COLOR_NAVY);
        }
    }

    // ── Leyenda al pie ────────────────────────────────────────────────────────

    private function escribirLeyenda(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        int $totalAlumnos,
        string $ultimaCol
    ): void {
        $fila = self::FILA_DATOS + $totalAlumnos + 1;
        $sheet->getRowDimension($fila)->setRowHeight(14);
        $sheet->mergeCells("A{$fila}:{$ultimaCol}{$fila}");
        $sheet->setCellValue("A{$fila}",
            '  Leyenda:   ✓ Asistió     F  Falta     J  Justificada     R  Retardo     —  Sin clase');
        $sheet->getStyle("A{$fila}:{$ultimaCol}{$fila}")->applyFromArray([
            'font'      => ['size' => 8, 'italic' => true, 'color' => ['argb' => self::COLOR_GRIS]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['top' => ['borderStyle' => Border::BORDER_THIN,
                                      'color'       => ['argb' => self::COLOR_BORDE]]],
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function generarCols(string $inicio, int $cantidad): array
    {
        $cols   = [];
        $actual = $inicio;
        for ($i = 0; $i < $cantidad; $i++) {
            $cols[] = $actual;
            $actual = $this->siguienteCol($actual);
        }
        return $cols;
    }

    private function siguienteCol(string $col): string
    {
        $letras = str_split($col);
        for ($i = count($letras) - 1; $i >= 0; $i--) {
            if ($letras[$i] !== 'Z') {
                $letras[$i] = chr(ord($letras[$i]) + 1);
                return implode('', $letras);
            }
            $letras[$i] = 'A';
        }
        return 'A'.implode('', $letras);
    }

    private function colOffset(string $inicio, int $n): string
    {
        $col = $inicio;
        for ($i = 0; $i < $n; $i++) {
            $col = $this->siguienteCol($col);
        }
        return $col;
    }

    private function countCols(string $desde, string $hasta): int
    {
        $count  = 1;
        $actual = $desde;
        while ($actual !== $hasta) {
            $actual = $this->siguienteCol($actual);
            $count++;
        }
        return $count;
    }

    private function nombreMes(int $mes): string
    {
        return ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'][$mes] ?? '';
    }
}
