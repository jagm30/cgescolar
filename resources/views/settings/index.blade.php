@extends('layouts.master')

@section('page_title', 'Configuración del Sistema')

@section('breadcrumb')
    <li><a href="#">Sistema</a></li>
    <li class="active">Ajustes</li>
@endsection

@push('styles')
    <style>
        .content-wrapper {
            background-color: #f4f7f6 !important;
        }

        /* Contenedor Principal Plano */
        .box-flat {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.03) !important;
            margin-bottom: 25px;
            background: #fff;
        }

        .box-header-flat {
            padding: 20px 25px;
            border-bottom: 1px solid #edf1f2;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .box-title-flat {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .box-title-flat i {
            color: #3498db;
            background: #ebf5fb;
            padding: 10px;
            border-radius: 8px;
            font-size: 16px;
        }

        .box-body-flat {
            padding: 30px;
        }

        /* Estilo de los Inputs */
        .form-group label {
            font-size: 11px;
            color: #94a3b8;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            display: block;
        }

        .form-control-flat {
            width: 100%;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            padding: 12px 15px;
            height: auto;
            font-size: 14px;
            color: #334155;
            transition: all 0.2s ease;
            background: #fcfdfe;
        }

        .form-control-flat:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            background: #fff;
        }

        /* Preview del Logo */
        .logo-preview-container {
            border: 2px dashed #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            background: #f8fafc;
            margin-top: 10px;
        }

        .logo-preview-img {
            max-width: 150px;
            height: auto;
            border-radius: 4px;
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.05));
        }

        /* Botones Planos */
        .btn-flat-info {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-flat-info:hover {
            background-color: #2980b9;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.2);
            color: white;
        }

        /* Alertas Planas */
        .alert-flat {
            border: none;
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 20px;
            font-weight: 500;
            font-size: 14px;
        }

        .alert-success-flat {
            background-color: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-flat alert-success-flat alert-dismissible">
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                <i class="fa fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <div class="box-flat">
            <div class="box-header-flat">
                <h3 class="box-title-flat">
                    <i class="fa fa-sliders"></i> Personalización de Identidad
                </h3>
            </div>

            <div class="box-body-flat">
                <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        {{-- Configuración de Nombre --}}
                        <div class="col-md-7">
                            <div class="form-group" style="margin-bottom: 25px;">
                                <label>Nombre de la Institución</label>
                                <input type="text" name="nombre_escuela" class="form-control-flat"
                                    value="{{ $setting->nombre_escuela ?? 'CGESCOLAR' }}"
                                    placeholder="Ej. Colegio Bachilleres Plantel 33">
                                <p class="text-muted" style="font-size: 12px; margin-top: 8px;">Este nombre aparecerá en
                                    todos los encabezados de reportes PDF y listas.</p>
                            </div>

                            <div style="margin-top: 40px;">
                                <button type="submit" class="btn-flat-info">
                                    <i class="fa fa-save"></i> Guardar Cambios
                                </button>
                            </div>
                        </div>

                        {{-- Configuración de Logo --}}
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Logo Institucional (PNG recomendado)</label>
                                <input type="file" name="escuela_logo" class="form-control"
                                    style="border: none; padding: 5px 0; margin-bottom: 10px;">

                                <div class="logo-preview-container">
                                    @if ($setting->logo_ruta && file_exists(public_path('imgs_escuela/' . $setting->logo_ruta)))
                                        <img src="{{ asset('imgs_escuela/' . $setting->logo_ruta) }}"
                                            class="logo-preview-img" alt="Logo actual">
                                        <p style="margin-top: 10px; font-size: 11px; color: #64748b;">Logo cargado:
                                            <strong>{{ $setting->logo_ruta }}</strong></p>
                                    @else
                                        <div style="padding: 20px;">
                                            <i class="fa fa-image" style="font-size: 40px; color: #cbd5e1;"></i>
                                            <p style="margin-top: 10px; font-size: 12px; color: #94a3b8;">No hay logo
                                                personalizado cargado</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- ══ Configuración Fiscal (Emisor CFDI) ══ --}}
        <div class="box-flat" style="margin-top:8px;">
            <div class="box-header-flat">
                <h3 class="box-title-flat">
                    <i class="fa fa-file-text-o" style="color:#7b2d8b;background:#f5eafb;"></i>
                    Datos del Emisor (CFDI)
                </h3>
                @if($configFiscal->exists)
                <span style="background:#e8f5ee;color:#00875a;font-size:11px;font-weight:700;
                             padding:3px 10px;border-radius:8px;">
                    <i class="fa fa-check-circle"></i> Configurado
                </span>
                @else
                <span style="background:#fff3cd;color:#856404;font-size:11px;font-weight:700;
                             padding:3px 10px;border-radius:8px;">
                    <i class="fa fa-exclamation-triangle"></i> Sin configurar
                </span>
                @endif
            </div>

            <div class="box-body-flat">
                <p style="font-size:12px;color:#8a9ab0;margin-bottom:20px;">
                    Estos datos corresponden al <strong>emisor</strong> de las facturas electrónicas (el colegio).
                    Deben coincidir exactamente con los registrados en el SAT y en tu cuenta de factura.com.
                </p>

                @if($errors->any())
                <div class="alert alert-flat" style="background:#fdecea;color:#a94442;border-left:4px solid #e74c3c;margin-bottom:16px;">
                    <ul style="margin:0;padding-left:16px;">
                        @foreach($errors->all() as $error)
                        <li style="font-size:13px;">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('settings.fiscal') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group" style="margin-bottom:20px;">
                                <label>RFC del emisor <span style="color:#e74c3c;">*</span></label>
                                <input type="text" name="rfc"
                                       class="form-control-flat"
                                       value="{{ old('rfc', $configFiscal->rfc) }}"
                                       placeholder="Ej: EKU9003173C9"
                                       maxlength="13"
                                       style="text-transform:uppercase;">
                                <p class="text-muted" style="font-size:11px;margin-top:5px;">
                                    12 caracteres (persona moral) o 13 (persona física).
                                </p>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group" style="margin-bottom:20px;">
                                <label>Razón social <span style="color:#e74c3c;">*</span></label>
                                <input type="text" name="razon_social"
                                       class="form-control-flat"
                                       value="{{ old('razon_social', $configFiscal->razon_social) }}"
                                       placeholder="Ej: ESCUELA MODELO SA DE CV"
                                       maxlength="300"
                                       style="text-transform:uppercase;">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group" style="margin-bottom:20px;">
                                <label>Régimen fiscal <span style="color:#e74c3c;">*</span></label>
                                <select name="regimen_fiscal" class="form-control-flat">
                                    @php
                                        $regimenActual = old('regimen_fiscal', $configFiscal->regimen_fiscal);
                                        $regimenes = [
                                            '601' => '601 — General de Ley Personas Morales',
                                            '603' => '603 — Personas Morales con Fines no Lucrativos',
                                            '605' => '605 — Sueldos y Salarios',
                                            '608' => '608 — Demás ingresos',
                                            '612' => '612 — Personas Físicas con Actividades Empresariales',
                                            '616' => '616 — Sin Obligaciones Fiscales',
                                            '621' => '621 — Incorporación Fiscal',
                                            '625' => '625 — Régimen de las Actividades Agrícolas',
                                            '626' => '626 — Régimen Simplificado de Confianza',
                                            '630' => '630 — Enajenación de acciones en bolsa',
                                        ];
                                    @endphp
                                    <option value="">-- Seleccionar --</option>
                                    @foreach($regimenes as $clave => $descripcion)
                                    <option value="{{ $clave }}" {{ $regimenActual == $clave ? 'selected' : '' }}>
                                        {{ $descripcion }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group" style="margin-bottom:20px;">
                                <label>Serie de facturas <span style="color:#e74c3c;">*</span></label>
                                <input type="text" name="serie"
                                       class="form-control-flat"
                                       value="{{ old('serie', $configFiscal->serie ?? 'A') }}"
                                       placeholder="A"
                                       maxlength="5"
                                       style="text-transform:uppercase;text-align:center;letter-spacing:.1em;">
                                <p class="text-muted" style="font-size:11px;margin-top:5px;">
                                    Letra(s) para el folio. Ej: A, FA, EDU.
                                </p>
                            </div>
                        </div>
                        @if($configFiscal->exists)
                        <div class="col-md-4">
                            <div class="form-group" style="margin-bottom:20px;">
                                <label>Folio actual</label>
                                <div style="padding:12px 15px;background:#f4f6f8;border-radius:8px;
                                            border:1px solid #e2e8f0;font-size:14px;font-weight:700;color:#1a2634;">
                                    {{ $configFiscal->serie }}{{ str_pad($configFiscal->folio_actual, 8, '0', STR_PAD_LEFT) }}
                                </div>
                                <p class="text-muted" style="font-size:11px;margin-top:5px;">
                                    El folio se incrementa automáticamente al emitir.
                                </p>
                            </div>
                        </div>
                        @endif
                    </div>

                    <div style="margin-top:10px;">
                        <button type="submit" class="btn-flat-info"
                                style="background:#7b2d8b;">
                            <i class="fa fa-save"></i>
                            {{ $configFiscal->exists ? 'Actualizar datos fiscales' : 'Guardar datos fiscales' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
