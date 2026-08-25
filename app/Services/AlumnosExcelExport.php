<?php

namespace App\Services;

use App\Models\Alumno;
use App\Models\CicloEscolar;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AlumnosExcelExport
{
    /** Última columna de la hoja (actualizar si se agregan secciones) */
    private const ULTIMA_COL = 'AH';

    /**
     * Grupos de columnas.
     * Estructura: desde, hasta, color ARGB, etiqueta de sección, cols[letra => [ancho, título]]
     */
    private const GRUPOS = [
        [
            'desde' => 'A', 'hasta' => 'I',
            'color' => 'FF1E4D7B', 'label' => 'Datos del Alumno',
            'cols'  => [
                'A' => [14, 'Matrícula'],
                'B' => [18, 'Nombre'],
                'C' => [18, 'Ap. Paterno'],
                'D' => [18, 'Ap. Materno'],
                'E' => [10, 'Género'],
                'F' => [16, 'Fecha Nac.'],
                'G' => [20, 'CURP'],
                'H' => [12, 'Religión'],
                'I' => [12, 'Estado'],
            ],
        ],
        [
            'desde' => 'J', 'hasta' => 'N',
            'color' => 'FF2E7D32', 'label' => 'Domicilio',
            'cols'  => [
                'J' => [28, 'Calle'],
                'K' => [18, 'Colonia'],
                'L' => [10, 'C.P.'],
                'M' => [16, 'Ciudad'],
                'N' => [18, 'Estado (residencia)'],
            ],
        ],
        [
            'desde' => 'O', 'hasta' => 'R',
            'color' => 'FF6A1E6E', 'label' => 'Datos Escolares',
            'cols'  => [
                'O' => [18, 'Nivel'],
                'P' => [20, 'Grado'],
                'Q' => [22, 'Grupo'],
                'R' => [16, 'Fecha Inscripción'],
            ],
        ],
        [
            'desde' => 'S', 'hasta' => 'Z',
            'color' => 'FF7B3A1E', 'label' => 'Contacto 1',
            'cols'  => [
                'S' => [28, 'Nombre completo'],
                'T' => [16, 'Parentesco'],
                'U' => [16, 'Tel. Celular'],
                'V' => [16, 'Tel. Trabajo'],
                'W' => [24, 'Email'],
                'X' => [22, 'Lugar Trabajo'],
                'Y' => [18, 'Puesto'],
                'Z' => [8,  'Vive'],
            ],
        ],
        [
            'desde' => 'AA', 'hasta' => 'AH',
            'color' => 'FF1A5276', 'label' => 'Contacto 2',
            'cols'  => [
                'AA' => [28, 'Nombre completo'],
                'AB' => [16, 'Parentesco'],
                'AC' => [16, 'Tel. Celular'],
                'AD' => [16, 'Tel. Trabajo'],
                'AE' => [24, 'Email'],
                'AF' => [22, 'Lugar Trabajo'],
                'AG' => [18, 'Puesto'],
                'AH' => [8,  'Vive'],
            ],
        ],
    ];

    public function descargar(Request $request, int $cicloId): StreamedResponse
    {
        $ciclo   = CicloEscolar::findOrFail($cicloId);
        $alumnos = $this->consultarAlumnos($request, $cicloId);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Alumnos');

        $this->escribirTitulo($sheet, $ciclo);
        $this->escribirEncabezados($sheet);
        $this->escribirDatos($sheet, $alumnos, $cicloId);
        $this->congelarPaneles($sheet);

        $nombre = "Alumnos_{$ciclo->nombre}_" . now()->format('Ymd_His') . '.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$nombre}\"",
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    // ── Consulta ──────────────────────────────────────────────────────────────

    private function consultarAlumnos(Request $request, int $cicloId): \Illuminate\Support\Collection
    {
        return Alumno::with([
            'inscripciones' => fn ($q) => $q
                ->where('ciclo_id', $cicloId)
                ->with('grupo.grado.nivel'),
            'contactos' => fn ($q) => $q->orderByPivot('orden'),
        ])
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->when($request->filled('nivel_id'), fn ($q) => $q->whereHas('inscripciones', fn ($q) => $q
                ->where('ciclo_id', $cicloId)
                ->whereHas('grupo.grado', fn ($q) => $q->where('nivel_id', $request->nivel_id))
            ))
            ->when($request->filled('grupo_id'), fn ($q) => $q->whereHas('inscripciones', fn ($q) => $q
                ->where('ciclo_id', $cicloId)
                ->where('grupo_id', $request->grupo_id)
            ))
            ->when($request->filled('buscar'), fn ($q) => $q->where(fn ($q) => $q
                ->where('nombre', 'like', "%{$request->buscar}%")
                ->orWhere('ap_paterno', 'like', "%{$request->buscar}%")
                ->orWhere('matricula', 'like', "%{$request->buscar}%")
                ->orWhere('curp', 'like', "%{$request->buscar}%")
            ))
            ->orderBy('ap_paterno')
            ->orderBy('nombre')
            ->get();
    }

    // ── Fila de título ────────────────────────────────────────────────────────

    private function escribirTitulo(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        CicloEscolar $ciclo
    ): void {
        $rango = "A1:" . self::ULTIMA_COL . "1";
        $sheet->mergeCells($rango);
        $sheet->setCellValue('A1', "Reporte de Alumnos  —  Ciclo Escolar: {$ciclo->nombre}  |  Generado: " . now()->format('d/m/Y H:i'));
        $sheet->getRowDimension(1)->setRowHeight(24);

        $sheet->getStyle($rango)->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 12],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0D2E4E']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER,
                            'indent' => 1],
        ]);
    }

    // ── Encabezados de sección y columna (filas 2 y 3) ───────────────────────

    private function escribirEncabezados(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): void
    {
        $sheet->getRowDimension(2)->setRowHeight(20);
        $sheet->getRowDimension(3)->setRowHeight(20);

        foreach (self::GRUPOS as $grupo) {
            // Fila 2: título de sección (celdas combinadas)
            $rango = "{$grupo['desde']}2:{$grupo['hasta']}2";
            $sheet->mergeCells($rango);
            $sheet->setCellValue("{$grupo['desde']}2", $grupo['label']);
            $sheet->getStyle($rango)->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 10],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $grupo['color']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);

            // Fila 3: nombre de cada columna
            foreach ($grupo['cols'] as $col => [$ancho, $titulo]) {
                $sheet->getColumnDimension($col)->setWidth($ancho);
                $sheet->setCellValue("{$col}3", $titulo);
            }
        }

        // Estilo unificado fila 3
        $sheet->getStyle("A3:" . self::ULTIMA_COL . "3")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FF333333'], 'size' => 10],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE8EEF5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FFAABDD4']]],
        ]);
    }

    // ── Datos (desde fila 4) ──────────────────────────────────────────────────

    private function escribirDatos(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        \Illuminate\Support\Collection $alumnos,
        int $cicloId
    ): void {
        $fila = 4;

        foreach ($alumnos as $alumno) {
            $inscripcion = $alumno->inscripciones->first();
            $grupo       = $inscripcion?->grupo;
            $contacto1   = $alumno->contactos->get(0);
            $contacto2   = $alumno->contactos->get(1);

            // Datos del alumno
            $sheet->setCellValue("A{$fila}", $alumno->matricula);
            $sheet->setCellValue("B{$fila}", $alumno->nombre);
            $sheet->setCellValue("C{$fila}", $alumno->ap_paterno);
            $sheet->setCellValue("D{$fila}", $alumno->ap_materno);
            $sheet->setCellValue("E{$fila}", ucfirst($alumno->genero ?? ''));
            $sheet->setCellValue("F{$fila}", $alumno->fecha_nacimiento?->format('d/m/Y'));
            $sheet->setCellValue("G{$fila}", $alumno->curp);
            $sheet->setCellValue("H{$fila}", $alumno->religion);
            $sheet->setCellValue("I{$fila}", ucfirst($alumno->estado ?? ''));

            // Domicilio
            $sheet->setCellValue("J{$fila}", $alumno->calle);
            $sheet->setCellValue("K{$fila}", $alumno->colonia);
            $sheet->setCellValue("L{$fila}", $alumno->codigo_postal);
            $sheet->setCellValue("M{$fila}", $alumno->ciudad);
            $sheet->setCellValue("N{$fila}", $alumno->estado_residencia);

            // Datos escolares
            $sheet->setCellValue("O{$fila}", $grupo?->grado?->nivel?->nombre);
            $sheet->setCellValue("P{$fila}", $grupo?->grado?->numero);
            $sheet->setCellValue("Q{$fila}", $grupo?->nombre);
            $sheet->setCellValue("R{$fila}", $inscripcion?->fecha?->format('d/m/Y'));

            // Contacto 1 (inicia en S)
            $this->escribirContacto($sheet, $fila, 'S', $contacto1);

            // Contacto 2 (inicia en AA)
            $this->escribirContacto($sheet, $fila, 'AA', $contacto2);

            // Fila alterna
            if ($fila % 2 === 0) {
                $sheet->getStyle("A{$fila}:" . self::ULTIMA_COL . "{$fila}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF5F8FC');
            }

            $fila++;
        }

        // Bordes en la tabla de datos
        if ($fila > 4) {
            $sheet->getStyle("A4:" . self::ULTIMA_COL . ($fila - 1))->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['argb' => 'FFD8E4EF'],
                    ],
                ],
            ]);
        }
    }

    private function escribirContacto(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        int $fila,
        string $colInicio,
        ?\App\Models\ContactoFamiliar $contacto
    ): void {
        if (! $contacto) {
            return;
        }

        $cols = $this->colsDesde($colInicio, 8);

        $sheet->setCellValue("{$cols[0]}{$fila}", $contacto->nombre_completo);
        $sheet->setCellValue("{$cols[1]}{$fila}", $contacto->pivot?->parentesco);
        $sheet->setCellValue("{$cols[2]}{$fila}", $contacto->telefono_celular);
        $sheet->setCellValue("{$cols[3]}{$fila}", $contacto->telefono_trabajo);
        $sheet->setCellValue("{$cols[4]}{$fila}", $contacto->email);
        $sheet->setCellValue("{$cols[5]}{$fila}", $contacto->lugar_trabajo);
        $sheet->setCellValue("{$cols[6]}{$fila}", $contacto->puesto);
        $sheet->setCellValue("{$cols[7]}{$fila}", $contacto->vive ? 'Sí' : 'No');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Genera N letras de columna consecutivas a partir de $inicio (soporta AA, AB…) */
    private function colsDesde(string $inicio, int $cantidad): array
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

        return 'A' . implode('', $letras);
    }

    private function congelarPaneles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): void
    {
        // Congela las 3 filas de encabezado y las 4 primeras columnas (A–D: matrícula + nombre completo)
        $sheet->freezePane('E4');
    }
}
