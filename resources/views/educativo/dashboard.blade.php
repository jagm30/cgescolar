@extends('layouts.educativo')

@section('page_title', 'Módulo Educativo')
@section('page_subtitle', 'Calificaciones')

@section('content')
<div class="row">

    {{-- Bienvenida --}}
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-book"></i>
                    Bienvenido, {{ auth()->user()->nombre }}
                </h3>
            </div>
            <div class="box-body">
                <p class="text-muted">
                    Este es el módulo de captura de calificaciones.
                    Las secciones estarán disponibles conforme se configuren
                    los planes de estudio, materias y períodos evaluativos.
                </p>

                @if(auth()->user()->esAdministrador())
                    <div class="alert alert-info" style="margin-top:12px;">
                        <i class="fa fa-info-circle"></i>
                        <strong>Administrador:</strong>
                        El siguiente paso es configurar los planes de estudio y materias por nivel.
                    </div>
                @else
                    <div class="alert alert-warning" style="margin-top:12px;">
                        <i class="fa fa-clock-o"></i>
                        Aún no tienes grupos asignados en el ciclo actual,
                        o no hay períodos de evaluación abiertos.
                        Contacta al administrador si esto es un error.
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
