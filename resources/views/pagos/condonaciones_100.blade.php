@extends('layouts.master')

@section('page_title', 'Alumnos con condonación 100%')
@section('page_subtitle', 'Alumnos que tienen al menos un cargo condonado en el ciclo actual')

@section('breadcrumb')
    <li><a href="{{ route('pagos.index') }}">Pagos</a></li>
    <li class="active">Condonaciones 100%</li>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('dist/css/alt/AdminLTE-select2.min.css') }}">
<style>
.cond-hero {
    background: linear-gradient(135deg, #6a0dad 0%, #9b30f5 100%);
    border-radius: 8px; padding: 20px 28px; margin-bottom: 22px;
    display: flex; align-items: center; gap: 0;
    box-shadow: 0 4px 16px rgba(106,13,173,.22);
    flex-wrap: wrap;
}
.cond-stat { text-align: center; padding: 0 24px; border-left: 1px solid rgba(255,255,255,.18); }
.cond-stat:first-child { border-left: none; padding-left: 0; }
.cond-stat-num { font-size: 26px; font-weight: 800; color: #fff; line-height: 1; }
.cond-stat-lbl { font-size: 10px; color: rgba(255,255,255,.65); margin-top: 3px;
                 text-transform: uppercase; letter-spacing: .06em; }

.cond-table { width: 100%; border-collapse: collapse; }
.cond-table thead th {
    background: #f4f6f8; color: #6b7a8d;
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .05em; padding: 9px 12px;
    border-bottom: 2px solid #e4eaf0; white-space: nowrap;
}
.cond-table tbody tr { border-bottom: 1px solid #f0f3f7; transition: background .1s; }
.cond-table tbody tr:hover td { background: #f9f5ff; }
.cond-table td { padding: 10px 12px; vertical-align: middle; font-size: 13px; }

.badge-condonado {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 11px; font-weight: 700; padding: 3px 9px;
    border-radius: 10px; white-space: nowrap;
    background: #f3e8ff; color: #6a0dad; border: 1px solid #d8b4fe;
}

.cargo-tag {
    display: inline-block; background: #f3e8ff; color: #7c3aed;
    border: 1px solid #ddd6fe; border-radius: 6px;
    font-size: 11px; padding: 2px 8px; margin: 2px 2px 2px 0;
}
</style>
@endpush

@section('content')

{{-- ── Hero con totales ── --}}
<div class="cond-hero">
    <div class="cond-stat">
        <div class="cond-stat-num">{{ $alumnos->count() }}</div>
        <div class="cond-stat-lbl">Alumnos con condonación</div>
    </div>
    <div class="cond-stat">
        <div class="cond-stat-num">
            {{ $alumnos->sum(fn ($a) => $a->inscripciones->first()?->cargos->count() ?? 0) }}
        </div>
        <div class="cond-stat-lbl">Cargos condonados</div>
    </div>
    <div class="cond-stat">
        <div class="cond-stat-num">
            ${{ number_format(
                $alumnos->sum(fn ($a) => $a->inscripciones->first()?->cargos->sum('monto_original') ?? 0),
                2
            ) }}
        </div>
        <div class="cond-stat-lbl">Total condonado</div>
    </div>
</div>

{{-- ── Filtro de búsqueda ── --}}
<div class="box" style="border-radius:8px;border:1px solid #e0e7ef;box-shadow:0 2px 10px rgba(0,0,0,.05);overflow:hidden;margin-bottom:20px;">
    <div class="box-body" style="padding:14px 18px;">
        <form method="GET" action="{{ route('pagos.condonaciones-100') }}" class="form-inline" style="gap:8px;display:flex;flex-wrap:wrap;align-items:center;">
            <select name="alumno_id" class="form-control select2-alumno" style="min-width:260px;"
                    data-placeholder="Todos los alumnos">
                <option value="">Todos los alumnos</option>
                @foreach ($opciones as $op)
                    <option value="{{ $op->id }}" {{ request('alumno_id') == $op->id ? 'selected' : '' }}>
                        {{ trim("{$op->ap_paterno} {$op->ap_materno} {$op->nombre}") }}
                    </option>
                @endforeach
            </select>
            @if(request('alumno_id'))
                <a href="{{ route('pagos.condonaciones-100') }}" class="btn btn-default btn-flat btn-sm">
                    <i class="fa fa-times"></i> Limpiar
                </a>
            @endif
        </form>
    </div>
</div>

{{-- ── Tabla ── --}}
<div class="box" style="border-radius:8px;border:1px solid #e0e7ef;box-shadow:0 2px 10px rgba(0,0,0,.05);overflow:hidden;">
    <div class="box-body" style="padding:0;">
        @if($alumnos->isEmpty())
            <div style="text-align:center;padding:48px 24px;color:#9ba8b8;">
                <i class="fa fa-check-circle" style="font-size:36px;color:#c4b5fd;margin-bottom:12px;display:block;"></i>
                <h4 style="margin:0 0 6px;">Sin condonaciones registradas</h4>
                <p style="margin:0;font-size:13px;">No hay alumnos con cargos condonados al 100% en el ciclo actual.</p>
            </div>
        @else
            <table class="cond-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Alumno</th>
                        <th>Grupo</th>
                        <th>Cargos condonados</th>
                        <th style="text-align:right;">Total condonado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($alumnos as $i => $alumno)
                        @php
                            $inscripcion = $alumno->inscripciones->first();
                            $cargos      = $inscripcion?->cargos ?? collect();
                            $grupo       = $inscripcion?->grupo;
                            $totalCond   = $cargos->sum('monto_original');
                        @endphp
                        <tr>
                            <td style="color:#9ba8b8;font-size:12px;">{{ $i + 1 }}</td>
                            <td>
                                <div style="font-weight:600;">
                                    {{ trim("{$alumno->ap_paterno} {$alumno->ap_materno} {$alumno->nombre}") }}
                                </div>
                                @if($alumno->matricula)
                                    <div style="font-size:11px;color:#9ba8b8;">{{ $alumno->matricula }}</div>
                                @endif
                            </td>
                            <td>
                                @if($grupo)
                                    <span style="font-size:13px;">
                                        {{ $grupo->grado?->nombre }} — {{ $grupo->nombre }}
                                    </span>
                                @else
                                    <span style="color:#9ba8b8;font-size:12px;">Sin grupo</span>
                                @endif
                            </td>
                            <td>
                                @foreach($cargos as $cargo)
                                    <span class="cargo-tag">
                                        <i class="fa fa-ban" style="font-size:10px;"></i>
                                        {{ $cargo->etiqueta }}
                                        (${{ number_format($cargo->monto_original, 2) }})
                                    </span>
                                @endforeach
                            </td>
                            <td style="text-align:right;font-weight:700;color:#6a0dad;">
                                ${{ number_format($totalCond, 2) }}
                            </td>
                            <td>
                                <a href="{{ route('alumnos.show', $alumno) }}"
                                   class="btn btn-default btn-xs btn-flat" title="Ver alumno">
                                    <i class="fa fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

@endsection

@push('scripts')
    <script src="{{ asset('bower_components/select2/dist/js/select2.full.min.js') }}"></script>
    <script>
        $(function () {
            $('.select2-alumno').select2({
                allowClear: true,
                width: '260px',
                placeholder: 'Todos los alumnos',
            }).on('change', function () {
                $(this).closest('form').trigger('submit');
            });
        });
    </script>
@endpush
