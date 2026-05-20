@extends('layouts.master')

@section('page_title', 'Razones sociales')
@section('page_subtitle', 'Datos fiscales registrados')

@section('breadcrumb')
    <li><a href="{{ route('portal.dashboard') }}">Portal</a></li>
    <li class="active">Razones sociales</li>
@endsection

@push('styles')
    @include('portal._styles')
@endpush

@section('content')
    <div class="portal-card">
        <div class="portal-card-header">
            <h4 class="portal-card-title"><i class="fa fa-building-o"></i> Datos fiscales</h4>
            <span class="portal-pill portal-pill-ok">{{ $razonesSociales->count() }} activa(s)</span>
        </div>

        @forelse ($razonesSociales as $razonSocial)
            <div style="padding:16px;border-bottom:1px solid #f0f3f7;">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                    <h4 class="portal-student-name">{{ $razonSocial->razon_social }}</h4>
                    @if ($razonSocial->es_principal)
                        <span class="portal-pill portal-pill-ok"><i class="fa fa-star"></i> Principal</span>
                    @endif
                </div>
                <div class="row" style="margin-top:12px;">
                    <div class="col-sm-6 col-md-3">
                        <div class="portal-meta"><strong>RFC</strong><br>{{ $razonSocial->rfc }}</div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="portal-meta"><strong>Regimen</strong><br>{{ $razonSocial->regimen_fiscal ?: 'No registrado' }}</div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="portal-meta"><strong>Uso CFDI</strong><br>{{ $razonSocial->uso_cfdi_default ?: 'No registrado' }}</div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="portal-meta"><strong>CP fiscal</strong><br>{{ $razonSocial->domicilio_fiscal ?: 'No registrado' }}</div>
                    </div>
                </div>
            </div>
        @empty
            <div style="padding:16px;">
                <div class="portal-empty">
                    <i class="fa fa-building-o" style="font-size:34px;margin-bottom:10px;"></i>
                    <div>No hay razones sociales activas registradas.</div>
                </div>
            </div>
        @endforelse
    </div>
@endsection
