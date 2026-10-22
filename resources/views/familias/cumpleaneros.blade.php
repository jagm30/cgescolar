@extends('layouts.master')

@php
    $nombreMes = fn (int $m) => ucfirst(\Carbon\Carbon::create(null, $m, 1)->translatedFormat('F'));
    $totalContactos = $grupos->sum(fn ($g) => $g['contactos']->count());
    $hoy = now()->format('md');
@endphp

@section('page_title', 'Cumpleañeros')
@section('page_subtitle', 'Cumpleaños de contactos familiares por grupo')

@section('breadcrumb')
    <li><a href="{{ route('familias.index') }}">Familias</a></li>
    <li class="active">Cumpleañeros</li>
@endsection

@push('styles')
<style>
.cu-table { width:100%; border-collapse:collapse; }
.cu-table thead th {
    background:#f4f6f8; color:#6b7a8d;
    font-size:11px; font-weight:700; text-transform:uppercase;
    letter-spacing:.05em; padding:8px 12px;
    border-bottom:2px solid #e4eaf0; white-space:nowrap;
}
.cu-table tbody tr { border-bottom:1px solid #f0f3f7; }
.cu-table td { padding:8px 12px; vertical-align:middle; font-size:13px; }
.cu-table tr.cu-hoy td { background:#fff8e1; }
.cu-grupo {
    background:#1e4d7b; color:#fff; font-weight:700;
    padding:8px 14px; font-size:13px; text-transform:uppercase; letter-spacing:.04em;
    display:flex; justify-content:space-between;
}
.cu-dia {
    display:inline-block; min-width:42px; text-align:center;
    background:#eaf3fb; color:#1e4d7b; border-radius:6px;
    font-weight:700; padding:3px 6px; font-size:12px;
}
</style>
@endpush

@section('content')

{{-- ══ ENCABEZADO ══ --}}
<div style="background:#fff;border:1px solid #e0e7ef;border-radius:8px;padding:12px 18px;margin-bottom:12px;
            display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;
            box-shadow:0 1px 3px rgba(0,0,0,0.04);">
    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
        <h4 style="margin:0;font-weight:700;color:#1e4d7b;">
            <i class="fa fa-birthday-cake text-orange"></i> Cumpleañeros
        </h4>
        <span style="background:#eaf3fb;color:#2980b9;border:1px solid #90c2e7;border-radius:20px;
                     padding:2px 10px;font-size:12px;font-weight:600;">
            <i class="fa fa-calendar"></i> {{ $mes ? $nombreMes($mes) : 'Todos los meses' }}
        </span>
        <span style="background:#fff8e1;color:#b45309;border:1px solid #fde68a;border-radius:20px;
                     padding:2px 10px;font-size:12px;font-weight:600;">
            <i class="fa fa-users"></i> {{ $totalContactos }} contacto(s)
        </span>
    </div>
    @if ($grupos->isNotEmpty())
        <a href="{{ route('familias.cumpleaneros.pdf', ['mes' => $mes, 'grupo_id' => $grupoId]) }}"
           target="_blank" class="btn btn-danger btn-sm btn-flat" style="border-radius:20px;">
            <i class="fa fa-file-pdf-o"></i> Exportar PDF
        </a>
    @endif
</div>

{{-- ══ FILTROS ══ --}}
<form method="GET" action="{{ route('familias.cumpleaneros') }}"
      style="display:flex;align-items:center;gap:8px;padding:10px 14px;margin-bottom:12px;
             background:#f9fafb;border:1px solid #e8ecf0;border-radius:8px;flex-wrap:wrap;">
    <select name="mes" class="form-control input-sm" style="border-radius:6px;height:32px;max-width:180px;">
        <option value="" {{ $mes === null ? 'selected' : '' }}>Todos los meses</option>
        @foreach (range(1, 12) as $m)
            <option value="{{ $m }}" {{ $mes === $m ? 'selected' : '' }}>{{ $nombreMes($m) }}</option>
        @endforeach
    </select>

    <select name="grupo_id" class="form-control input-sm" style="border-radius:6px;height:32px;max-width:260px;">
        <option value="">Todos los grupos</option>
        @foreach ($gruposFiltro as $g)
            <option value="{{ $g->id }}" {{ $grupoId === $g->id ? 'selected' : '' }}>
                {{ $g->grado?->nivel?->nombre }} — {{ $g->grado?->numero }}° {{ $g->nombre }}
            </option>
        @endforeach
    </select>

    <button type="submit" class="btn btn-primary btn-sm btn-flat" style="border-radius:20px;padding:4px 14px;height:32px;">
        <i class="fa fa-search"></i> Consultar
    </button>

    <span style="margin-left:auto;font-size:12px;color:#6b7a8d;">
        Ciclo: <strong>{{ $cicloActual?->nombre ?? '—' }}</strong>
    </span>
</form>

{{-- ══ RESULTADOS POR GRUPO ══ --}}
@forelse ($grupos as $fila)
    @php $grupo = $fila['grupo']; @endphp
    <div style="border:1px solid #e4eaf0;border-radius:8px;background:#fff;margin-bottom:14px;overflow:hidden;
                box-shadow:0 1px 4px rgba(0,0,0,.04);">
        <div class="cu-grupo">
            <span>{{ $grupo?->grado?->nivel?->nombre }} — {{ $grupo?->grado?->numero }}° {{ $grupo?->nombre }}</span>
            <span style="color:#e8c46a;">{{ $fila['contactos']->count() }} cumpleañero(s)</span>
        </div>
        <div style="overflow-x:auto;">
            <table class="cu-table">
                <thead>
                    <tr>
                        <th style="width:90px;">Fecha</th>
                        <th>Contacto</th>
                        <th>Alumno(s) / Parentesco</th>
                        <th>Teléfono</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($fila['contactos'] as $item)
                        @php $contacto = $item['contacto']; @endphp
                        <tr class="{{ $contacto->fecha_nacimiento->format('md') === $hoy ? 'cu-hoy' : '' }}">
                            <td>
                                <span class="cu-dia">{{ $contacto->fecha_nacimiento->translatedFormat('d M') }}</span>
                                @if ($contacto->fecha_nacimiento->format('md') === $hoy)
                                    <i class="fa fa-birthday-cake text-orange" title="¡Hoy!"></i>
                                @endif
                            </td>
                            <td style="font-weight:700;color:#1a2634;">{{ $contacto->nombre_completo }}</td>
                            <td style="color:#4a5568;">
                                @foreach ($item['alumnos'] as $a)
                                    <div>
                                        {{ $a['alumno']->nombre }} {{ $a['alumno']->ap_paterno }}
                                        <small class="text-muted">({{ ucfirst($a['parentesco'] ?? '—') }})</small>
                                    </div>
                                @endforeach
                            </td>
                            <td style="color:#4a5568;">{{ $contacto->telefono_celular ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@empty
    <div style="padding:56px 20px;text-align:center;background:#fff;border:1px solid #e4eaf0;border-radius:8px;">
        <i class="fa fa-birthday-cake" style="font-size:42px;color:#cfd8dc;display:block;margin-bottom:12px;"></i>
        <p style="color:#b0bec5;margin:0;font-weight:600;">
            No hay contactos familiares con cumpleaños {{ $mes ? 'en '.$nombreMes($mes) : 'registrados' }} para los filtros seleccionados.
        </p>
    </div>
@endforelse

@endsection
