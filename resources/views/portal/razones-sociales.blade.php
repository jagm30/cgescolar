@extends('layouts.master')

@section('page_title', 'Datos fiscales')
@section('page_subtitle', 'RFC para facturación')

@section('breadcrumb')
    <li><a href="{{ route('portal.dashboard') }}">Portal</a></li>
    <li class="active">Datos fiscales</li>
@endsection

@push('styles')
    @include('portal._styles')
    <style>
        /* ── Hero ── */
        .rs-hero {
            background: linear-gradient(135deg, #4c1d95 0%, #7c3aed 100%);
            border-radius: 14px;
            padding: 20px 18px;
            margin-bottom: 16px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .rs-hero-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: rgba(255,255,255,.18);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }
        .rs-hero-titulo {
            font-size: 20px;
            font-weight: 800;
            margin: 0 0 4px;
        }
        .rs-hero-sub {
            font-size: 13px;
            color: rgba(255,255,255,.78);
            margin: 0;
            line-height: 1.4;
        }

        /* ── Info banner ── */
        .rs-info {
            background: #ede9fe;
            border: 1px solid #c4b5fd;
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 16px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 13px;
            color: #4c1d95;
            line-height: 1.5;
        }
        .rs-info i { font-size: 18px; flex-shrink: 0; margin-top: 1px; }

        /* ── Botón agregar ── */
        .rs-btn-agregar {
            width: 100%;
            padding: 16px;
            border-radius: 12px;
            border: 2px dashed #c4b5fd;
            background: #faf5ff;
            color: #7c3aed;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 14px;
            transition: background .15s, border-color .15s;
        }
        .rs-btn-agregar:hover:not(:disabled) { background: #ede9fe; border-color: #7c3aed; }
        .rs-btn-agregar:disabled {
            opacity: .5;
            cursor: not-allowed;
            border-style: solid;
        }
        .rs-btn-agregar i { font-size: 20px; }

        /* ── Panel de formulario ── */
        .rs-form-panel {
            background: #faf5ff;
            border: 1px solid #c4b5fd;
            border-radius: 12px;
            padding: 18px 16px 14px;
            margin-bottom: 14px;
        }
        .rs-form-titulo {
            font-size: 15px;
            font-weight: 800;
            color: #4c1d95;
            margin: 0 0 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .rs-form-group {
            margin-bottom: 14px;
        }
        .rs-form-label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #4a5568;
            margin-bottom: 6px;
        }
        .rs-form-label .rs-requerido { color: #b91c1c; margin-left: 2px; }
        .rs-form-label .rs-hint {
            display: block;
            font-size: 11px;
            font-weight: 400;
            color: #9aa5b4;
            margin-top: 1px;
        }
        .rs-control {
            width: 100%;
            height: 46px;
            padding: 0 14px;
            border: 1.5px solid #d0dae5;
            border-radius: 10px;
            font-size: 15px;
            color: #1a2634;
            background: #fff;
            box-sizing: border-box;
            transition: border-color .15s;
            appearance: none;
        }
        .rs-control:focus { outline: none; border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124,58,237,.12); }
        .rs-control.rs-upper { text-transform: uppercase; }
        .rs-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .rs-check-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            background: #fff;
            border: 1.5px solid #d0dae5;
            border-radius: 10px;
            cursor: pointer;
            margin-bottom: 14px;
        }
        .rs-check-row input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; accent-color: #7c3aed; }
        .rs-check-label { font-size: 14px; font-weight: 600; color: #4a5568; cursor: pointer; }
        .rs-form-btns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .rs-btn {
            padding: 13px;
            border-radius: 10px;
            border: none;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            transition: opacity .15s;
        }
        .rs-btn:disabled { opacity: .6; cursor: not-allowed; }
        .rs-btn-cancel { background: #f0f3f7; color: #4a5568; }
        .rs-btn-cancel:hover:not(:disabled) { background: #e4eaf0; }
        .rs-btn-save   { background: #7c3aed; color: #fff; }
        .rs-btn-save:hover:not(:disabled)   { background: #4c1d95; }
        .rs-alerta {
            display: none;
            margin-top: 12px;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
        }
        .rs-alerta-danger  { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

        /* ── Tarjeta de RFC ── */
        .rs-card {
            background: #fff;
            border: 1px solid #e4eaf0;
            border-radius: 14px;
            margin-bottom: 12px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0,0,0,.04);
        }
        .rs-card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            padding: 16px 16px 0;
        }
        .rs-card-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 10px;
        }
        .rs-badge {
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .rs-badge-principal { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .rs-badge-mio       { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .rs-badge-otro      { background: #e0f2fe; color: #0369a1; border: 1px solid #7dd3fc; }
        .rs-card-rfc {
            font-size: 22px;
            font-weight: 800;
            color: #1a2634;
            letter-spacing: .04em;
            margin-bottom: 4px;
            line-height: 1;
        }
        .rs-card-nombre {
            font-size: 15px;
            color: #4a5568;
            font-weight: 600;
            margin-bottom: 14px;
            line-height: 1.3;
        }
        .rs-card-datos {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            border-top: 1px solid #f0f3f7;
        }
        .rs-dato {
            padding: 10px 16px;
            border-right: 1px solid #f0f3f7;
        }
        .rs-dato:nth-child(even) { border-right: none; }
        .rs-dato-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #9aa5b4;
            margin-bottom: 3px;
        }
        .rs-dato-valor {
            font-size: 12px;
            font-weight: 600;
            color: #1a2634;
            line-height: 1.3;
        }
        .rs-card-acciones {
            display: grid;
            gap: 0;
            border-top: 1px solid #f0f3f7;
        }
        .rs-card-acciones.tres { grid-template-columns: 1fr 1fr 1fr; }
        .rs-card-acciones.dos  { grid-template-columns: 1fr 1fr; }
        .rs-accion {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 12px 8px;
            border: none;
            background: transparent;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            border-right: 1px solid #f0f3f7;
            transition: background .15s;
            color: #4a5568;
        }
        .rs-accion:last-child { border-right: none; }
        .rs-accion:hover:not(:disabled) { background: #f8fafc; }
        .rs-accion:disabled { opacity: .4; cursor: not-allowed; }
        .rs-accion-principal { color: #b45309; }
        .rs-accion-editar    { color: #1d4ed8; }
        .rs-accion-eliminar  { color: #b91c1c; }

        /* ── Formulario de edición inline ── */
        .rs-form-editar {
            padding: 16px;
            border-top: 1px solid #f0f3f7;
            background: #faf5ff;
        }

        /* ── Vacío ── */
        .rs-empty {
            background: #fff;
            border: 1px solid #e4eaf0;
            border-radius: 14px;
            text-align: center;
            padding: 48px 20px;
            color: #9aa5b4;
        }
        .rs-empty i { font-size: 48px; color: #d1d9e0; display: block; margin-bottom: 14px; }
        .rs-empty p { font-size: 15px; margin: 0 0 6px; }
        .rs-empty small { font-size: 13px; }

        /* ── Botón regresar ── */
        .rs-nav-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 11px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none !important;
            border: 1px solid #dde3ea;
            background: #fff;
            color: #4a5568;
            transition: background .15s;
            margin-top: 6px;
            margin-bottom: 20px;
        }
        .rs-nav-btn:hover { background: #f0f3f7; color: #1a2634; }

        @media (max-width: 420px) {
            .rs-card-rfc { font-size: 19px; }
            .rs-grid-2   { grid-template-columns: 1fr; }
        }
    </style>
@endpush

@php
$regimenOpciones = [
    '601' => '601 – General de Ley Personas Morales',
    '603' => '603 – Personas Morales con Fines no Lucrativos',
    '605' => '605 – Sueldos y Salarios e Ingresos Asimilados',
    '606' => '606 – Arrendamiento',
    '608' => '608 – Demás ingresos',
    '612' => '612 – Personas Físicas con Actividades Empresariales y Profesionales',
    '616' => '616 – Sin obligaciones fiscales',
    '621' => '621 – Incorporación Fiscal',
    '626' => '626 – Régimen Simplificado de Confianza (RESICO)',
];
$cfdiOpciones = [
    'D10'  => 'D10 – Pagos por servicios educativos',
    'G03'  => 'G03 – Gastos en general',
    'D01'  => 'D01 – Honorarios médicos y hospitalarios',
    'D08'  => 'D08 – Transportación escolar obligatoria',
    'I04'  => 'I04 – Equipo de cómputo y accesorios',
    'S01'  => 'S01 – Sin efectos fiscales',
    'CP01' => 'CP01 – Pagos',
];
$misCantidad = $razonesSociales->where('contacto_id', $miContactoId)->count();
@endphp

@section('content')

    {{-- ── Hero ── --}}
    <div class="rs-hero">
        <div class="rs-hero-icon"><i class="fa fa-building-o"></i></div>
        <div>
            <div class="rs-hero-sub">Registra tu RFC para solicitar facturas de los pagos escolares.</div>
        </div>
    </div>

    {{-- ── Info ── --}}
    <div class="rs-info">
        <i class="fa fa-info-circle"></i>
        <span>
            Puedes registrar hasta <strong>3 RFC</strong>. Los datos deben coincidir
            exactamente con tu <strong>Constancia de Situación Fiscal</strong> del SAT.
        </span>
    </div>

    {{-- ── Botón agregar ── --}}
    <button id="btn-mostrar-form-nuevo" class="rs-btn-agregar"
        @if($misCantidad >= 3) disabled title="Ya tienes 3 RFC registrados (máximo permitido)" @endif>
        <i class="fa fa-plus-circle"></i>
        @if($misCantidad >= 3) Ya tienes 3 RFC registrados @else Agregar mi RFC @endif
    </button>

    {{-- ── Formulario nuevo ── --}}
    <div id="form-nuevo-rs" style="display:none; margin-bottom:14px;">
        <div class="rs-form-panel">
            <div class="rs-form-titulo">
                <i class="fa fa-plus-circle"></i> Nuevo RFC
            </div>

            <div class="rs-form-group">
                <label class="rs-form-label">
                    RFC <span class="rs-requerido">*</span>
                    <span class="rs-hint">Tal como aparece en tu constancia del SAT</span>
                </label>
                <input type="text" id="nuevo-rfc" class="rs-control rs-upper"
                       maxlength="13" placeholder="XAXX010101000">
            </div>

            <div class="rs-form-group">
                <label class="rs-form-label">
                    Nombre o razón social <span class="rs-requerido">*</span>
                    <span class="rs-hint">Tal como aparece en tu constancia del SAT</span>
                </label>
                <input type="text" id="nuevo-razon-social" class="rs-control" maxlength="300">
            </div>

            <div class="rs-grid-2">
                <div class="rs-form-group">
                    <label class="rs-form-label">
                        Código postal fiscal <span class="rs-requerido">*</span>
                        <span class="rs-hint">El de tu constancia del SAT</span>
                    </label>
                    <input type="text" id="nuevo-cp" class="rs-control"
                           maxlength="5" placeholder="00000" inputmode="numeric">
                </div>
                <div class="rs-form-group" style="min-width:0;">
                    <label class="rs-form-label">
                        Para qué usarás la factura <span class="rs-requerido">*</span>
                        <span class="rs-hint">Uso de CFDI</span>
                    </label>
                    <select id="nuevo-uso-cfdi" class="rs-control">
                        <option value="">-- Seleccionar --</option>
                        @foreach($cfdiOpciones as $val => $lbl)
                            <option value="{{ $val }}" @if($val === 'D10') selected @endif>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="rs-form-group">
                <label class="rs-form-label">
                    Régimen fiscal <span class="rs-requerido">*</span>
                    <span class="rs-hint">El que aparece en tu constancia del SAT</span>
                </label>
                <select id="nuevo-regimen" class="rs-control">
                    <option value="">-- Seleccionar --</option>
                    @foreach($regimenOpciones as $val => $lbl)
                        <option value="{{ $val }}">{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <label class="rs-check-row">
                <input type="checkbox" id="nuevo-principal">
                <span class="rs-check-label"><i class="fa fa-star" style="color:#d97706;margin-right:4px;"></i> Usar como RFC principal para facturación</span>
            </label>

            <div class="rs-form-btns">
                <button type="button" class="rs-btn rs-btn-cancel" id="btn-cancelar-nuevo">
                    <i class="fa fa-times"></i> Cancelar
                </button>
                <button type="button" class="rs-btn rs-btn-save" id="btn-guardar-nuevo">
                    <i class="fa fa-save"></i> Guardar RFC
                </button>
            </div>
            <div class="rs-alerta" id="nuevo-alerta"></div>
        </div>
    </div>

    {{-- ── Lista de RFC ── --}}
    <div id="rs-lista">
        @forelse ($razonesSociales as $rs)
            @php $esMia = $rs->contacto_id === $miContactoId; @endphp
            <div class="rs-card" data-id="{{ $rs->id }}" data-mio="{{ $esMia ? '1' : '0' }}">

                {{-- ── Vista normal ── --}}
                <div class="rs-vista">
                    <div class="rs-card-top">
                        <div style="flex:1;min-width:0;">

                            {{-- Badges --}}
                            <div class="rs-card-badges">
                                @if($rs->es_principal)
                                    <span class="rs-badge rs-badge-principal badge-principal">
                                        <i class="fa fa-star"></i> Principal
                                    </span>
                                @endif
                                <span class="badge-principal-placeholder"></span>
                                <span class="rs-badge {{ $esMia ? 'rs-badge-mio' : 'rs-badge-otro' }}">
                                    <i class="fa fa-user"></i>
                                    {{ trim($rs->contacto->nombre . ' ' . $rs->contacto->ap_paterno) }}
                                    @if($esMia) (yo) @endif
                                </span>
                            </div>

                            {{-- RFC --}}
                            <div class="rs-card-rfc rs-rfc-texto">{{ $rs->rfc }}</div>
                            {{-- Nombre --}}
                            <div class="rs-card-nombre rs-razon-social-texto">{{ $rs->razon_social }}</div>
                        </div>
                    </div>

                    {{-- Datos fiscales --}}
                    <div class="rs-card-datos">
                        <div class="rs-dato">
                            <div class="rs-dato-label">Régimen fiscal</div>
                            <div class="rs-dato-valor rs-regimen-texto">
                                {{ $regimenOpciones[$rs->regimen_fiscal] ?? $rs->regimen_fiscal }}
                            </div>
                        </div>
                        <div class="rs-dato">
                            <div class="rs-dato-label">CP fiscal</div>
                            <div class="rs-dato-valor rs-cp-texto">{{ $rs->domicilio_fiscal }}</div>
                        </div>
                        <div class="rs-dato" style="grid-column:1/-1;border-right:none;border-top:1px solid #f0f3f7;">
                            <div class="rs-dato-label">Uso de factura</div>
                            <div class="rs-dato-valor rs-uso-texto">
                                {{ $cfdiOpciones[$rs->uso_cfdi_default] ?? $rs->uso_cfdi_default }}
                            </div>
                        </div>
                    </div>

                    {{-- Acciones (solo para mis propios) --}}
                    @if($esMia)
                        <div class="rs-card-acciones {{ $rs->es_principal ? 'dos' : 'tres' }}">
                            @if(!$rs->es_principal)
                                <button class="rs-accion rs-accion-principal btn-principal-rs">
                                    <i class="fa fa-star-o"></i> Principal
                                </button>
                            @endif
                            <button class="rs-accion rs-accion-editar btn-editar-rs">
                                <i class="fa fa-pencil"></i> Editar
                            </button>
                            <button class="rs-accion rs-accion-eliminar btn-eliminar-rs">
                                <i class="fa fa-trash"></i> Eliminar
                            </button>
                        </div>
                    @endif
                </div>

                {{-- ── Formulario de edición ── --}}
                @if($esMia)
                <div class="rs-form-editar" style="display:none;">

                    <div class="rs-form-titulo" style="margin-bottom:14px;">
                        <i class="fa fa-pencil"></i> Editar RFC
                    </div>

                    <div class="rs-form-group">
                        <label class="rs-form-label">
                            Nombre o razón social <span class="rs-requerido">*</span>
                        </label>
                        <input type="text" class="rs-control editar-razon-social"
                               value="{{ $rs->razon_social }}" maxlength="300">
                    </div>

                    <div class="rs-grid-2">
                        <div class="rs-form-group">
                            <label class="rs-form-label">
                                Código postal fiscal <span class="rs-requerido">*</span>
                            </label>
                            <input type="text" class="rs-control editar-cp"
                                   value="{{ $rs->domicilio_fiscal }}" maxlength="5" inputmode="numeric">
                        </div>
                        <div class="rs-form-group" style="min-width:0;">
                            <label class="rs-form-label">
                                Uso de factura <span class="rs-requerido">*</span>
                            </label>
                            <select class="rs-control editar-uso-cfdi">
                                <option value="">-- Seleccionar --</option>
                                @foreach($cfdiOpciones as $val => $lbl)
                                    <option value="{{ $val }}" @if($rs->uso_cfdi_default === $val) selected @endif>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="rs-form-group">
                        <label class="rs-form-label">
                            Régimen fiscal <span class="rs-requerido">*</span>
                        </label>
                        <select class="rs-control editar-regimen">
                            <option value="">-- Seleccionar --</option>
                            @foreach($regimenOpciones as $val => $lbl)
                                <option value="{{ $val }}" @if($rs->regimen_fiscal === $val) selected @endif>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>

                    <label class="rs-check-row">
                        <input type="checkbox" class="editar-principal" @if($rs->es_principal) checked @endif>
                        <span class="rs-check-label"><i class="fa fa-star" style="color:#d97706;margin-right:4px;"></i> Usar como RFC principal</span>
                    </label>

                    <div class="rs-form-btns">
                        <button type="button" class="rs-btn rs-btn-cancel btn-cancelar-editar">
                            <i class="fa fa-times"></i> Cancelar
                        </button>
                        <button type="button" class="rs-btn rs-btn-save btn-guardar-editar">
                            <i class="fa fa-save"></i> Guardar
                        </button>
                    </div>
                    <div class="rs-alerta editar-alerta"></div>

                </div>
                @endif

            </div>
        @empty
            <div class="rs-empty" id="rs-vacio">
                <i class="fa fa-building-o"></i>
                <p>Aún no tienes RFC registrados.</p>
                <small>Usa el botón de arriba para agregar tus datos fiscales.</small>
            </div>
        @endforelse
    </div>

    {{-- ── Regresar ── --}}
    <a href="{{ route('portal.dashboard') }}" class="rs-nav-btn">
        <i class="fa fa-arrow-left"></i> Regresar al inicio
    </a>

@endsection

@push('scripts')
<script>
$(function () {

    var miContactoId    = {{ $miContactoId ?? 'null' }};
    var regimenOpciones = @json($regimenOpciones);
    var cfdiOpciones    = @json($cfdiOpciones);

    // ── Mostrar / cancelar formulario nuevo ──────────────────
    $('#btn-mostrar-form-nuevo').on('click', function () {
        $('#form-nuevo-rs').slideDown(150);
        $(this).hide();
        $('#nuevo-rfc').focus();
    });

    $('#btn-cancelar-nuevo').on('click', cerrarFormNuevo);

    function cerrarFormNuevo() {
        $('#form-nuevo-rs').slideUp(150);
        var misCant = $('#rs-lista .rs-card[data-mio="1"]').length;
        $('#btn-mostrar-form-nuevo').toggle(misCant < 3).prop('disabled', false);
        limpiarFormNuevo();
    }

    function limpiarFormNuevo() {
        $('#nuevo-rfc, #nuevo-razon-social, #nuevo-cp').val('');
        $('#nuevo-regimen').val('');
        $('#nuevo-uso-cfdi').val('D10');
        $('#nuevo-principal').prop('checked', false);
        ocultarAlerta('#nuevo-alerta');
        $('#btn-guardar-nuevo').prop('disabled', false).html('<i class="fa fa-save"></i> Guardar RFC');
    }

    // ── Guardar nuevo ─────────────────────────────────────────
    $('#btn-guardar-nuevo').on('click', function () {
        var btn = $(this);
        var datos = {
            rfc:              $('#nuevo-rfc').val().trim().toUpperCase(),
            razon_social:     $('#nuevo-razon-social').val().trim(),
            regimen_fiscal:   $('#nuevo-regimen').val(),
            domicilio_fiscal: $('#nuevo-cp').val().trim(),
            uso_cfdi_default: $('#nuevo-uso-cfdi').val(),
            es_principal:     $('#nuevo-principal').is(':checked') ? 1 : 0,
            _token:           '{{ csrf_token() }}',
        };

        if (!datos.rfc)              { mostrarAlerta('#nuevo-alerta', 'El RFC es obligatorio.'); return; }
        if (!datos.razon_social)     { mostrarAlerta('#nuevo-alerta', 'El nombre o razón social es obligatorio.'); return; }
        if (!datos.regimen_fiscal)   { mostrarAlerta('#nuevo-alerta', 'Selecciona el régimen fiscal.'); return; }
        if (!/^\d{5}$/.test(datos.domicilio_fiscal)) { mostrarAlerta('#nuevo-alerta', 'El código postal debe tener 5 dígitos.'); return; }
        if (!datos.uso_cfdi_default) { mostrarAlerta('#nuevo-alerta', 'Selecciona el uso de CFDI.'); return; }

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

        $.post('{{ route("portal.razones-sociales.store") }}', datos)
            .done(function (resp) {
                cerrarFormNuevo();
                agregarItemAlListado(resp.razon_social);
                toast(resp.mensaje, 'success');
            })
            .fail(function (xhr) {
                var msg = xhr.responseJSON?.mensaje
                    || xhr.responseJSON?.message
                    || primerError(xhr.responseJSON?.errors)
                    || 'Error al guardar.';
                mostrarAlerta('#nuevo-alerta', msg);
                btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar RFC');
            });
    });

    // ── Abrir / cerrar edición ────────────────────────────────
    $(document).on('click', '.btn-editar-rs', function () {
        var $card = $(this).closest('.rs-card');
        $card.find('.rs-vista').hide();
        $card.find('.rs-form-editar').show();
    });

    $(document).on('click', '.btn-cancelar-editar', function () {
        var $card = $(this).closest('.rs-card');
        $card.find('.rs-form-editar').hide();
        ocultarAlerta($card.find('.editar-alerta'));
        $card.find('.rs-vista').show();
        $card.find('.btn-guardar-editar').prop('disabled', false).html('<i class="fa fa-save"></i> Guardar');
    });

    // ── Guardar edición ───────────────────────────────────────
    $(document).on('click', '.btn-guardar-editar', function () {
        var btn     = $(this);
        var $card   = btn.closest('.rs-card');
        var rsId    = $card.data('id');
        var $alerta = $card.find('.editar-alerta');

        var datos = {
            razon_social:     $card.find('.editar-razon-social').val().trim(),
            regimen_fiscal:   $card.find('.editar-regimen').val(),
            domicilio_fiscal: $card.find('.editar-cp').val().trim(),
            uso_cfdi_default: $card.find('.editar-uso-cfdi').val(),
            es_principal:     $card.find('.editar-principal').is(':checked') ? 1 : 0,
            _token:           '{{ csrf_token() }}',
            _method:          'PUT',
        };

        if (!datos.razon_social)     { mostrarAlerta($alerta, 'El nombre o razón social es obligatorio.'); return; }
        if (!datos.regimen_fiscal)   { mostrarAlerta($alerta, 'Selecciona el régimen fiscal.'); return; }
        if (!/^\d{5}$/.test(datos.domicilio_fiscal)) { mostrarAlerta($alerta, 'El código postal debe tener 5 dígitos.'); return; }
        if (!datos.uso_cfdi_default) { mostrarAlerta($alerta, 'Selecciona el uso de CFDI.'); return; }

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

        $.post('/portal/razones-sociales/' + rsId, datos)
            .done(function (resp) {
                refrescarVistaCard($card, resp.razon_social);
                toast(resp.mensaje, 'success');
            })
            .fail(function (xhr) {
                var msg = xhr.responseJSON?.mensaje || xhr.responseJSON?.message || 'Error al actualizar.';
                mostrarAlerta($alerta, msg);
                btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar');
            });
    });

    // ── Eliminar ──────────────────────────────────────────────
    $(document).on('click', '.btn-eliminar-rs', function () {
        var btn   = $(this);
        var $card = btn.closest('.rs-card');
        var rsId  = $card.data('id');
        var rfc   = $card.find('.rs-rfc-texto').text().trim();

        if (!confirm('¿Eliminar el RFC ' + rfc + '?\nEsta acción no se puede deshacer.')) return;
        btn.prop('disabled', true);

        $.post('/portal/razones-sociales/' + rsId, { _token: '{{ csrf_token() }}', _method: 'DELETE' })
            .done(function (resp) {
                $card.fadeOut(300, function () {
                    $(this).remove();
                    if ($('#rs-lista .rs-card').length === 0) {
                        $('#rs-lista').html(htmlVacio());
                    }
                    actualizarBtnAgregar();
                });
                toast(resp.mensaje, 'success');
            })
            .fail(function () {
                btn.prop('disabled', false);
                toast('No se pudo eliminar.', 'danger');
            });
    });

    // ── Marcar como principal ─────────────────────────────────
    $(document).on('click', '.btn-principal-rs', function () {
        var btn   = $(this);
        var $card = btn.closest('.rs-card');
        var rsId  = $card.data('id');

        btn.prop('disabled', true);

        $.post('/portal/razones-sociales/' + rsId + '/principal', { _token: '{{ csrf_token() }}' })
            .done(function (resp) {
                // Quitar badge principal de todos los míos
                $('.rs-card[data-mio="1"] .badge-principal').remove();
                $('.rs-card[data-mio="1"] .badge-principal-placeholder').html('');
                // Restaurar botón "Principal" en todos menos este
                $('.rs-card[data-mio="1"]').not($card).each(function () {
                    var $acciones = $(this).find('.rs-card-acciones');
                    if (!$acciones.find('.btn-principal-rs').length) {
                        $acciones.removeClass('dos').addClass('tres')
                            .prepend('<button class="rs-accion rs-accion-principal btn-principal-rs"><i class="fa fa-star-o"></i> Principal</button>');
                    }
                    $acciones.find('.btn-principal-rs').prop('disabled', false);
                });
                // Marcar este
                $card.find('.badge-principal-placeholder').html(
                    '<span class="rs-badge rs-badge-principal badge-principal"><i class="fa fa-star"></i> Principal</span>'
                );
                btn.closest('.rs-card-acciones').removeClass('tres').addClass('dos');
                btn.remove();
                // Sincronizar checkbox en el form de edición
                $card.find('.editar-principal').prop('checked', true);
                toast(resp.mensaje, 'success');
            })
            .fail(function () {
                btn.prop('disabled', false);
                toast('No se pudo actualizar.', 'danger');
            });
    });

    // ── Helpers ───────────────────────────────────────────────
    function mostrarAlerta(selector, msg) {
        var $el = typeof selector === 'string' ? $(selector) : selector;
        $el.addClass('rs-alerta-danger').text(msg).show();
    }
    function ocultarAlerta(selector) {
        var $el = typeof selector === 'string' ? $(selector) : selector;
        $el.removeClass('rs-alerta-danger').text('').hide();
    }

    function toast(msg, tipo) {
        var bg = tipo === 'success' ? '#065f46' : '#b91c1c';
        var $t = $('<div>').css({
            position:'fixed', bottom:'24px', left:'50%', transform:'translateX(-50%)',
            background: bg, color:'#fff', padding:'12px 20px', borderRadius:'10px',
            zIndex: 9999, fontSize:'14px', fontWeight:'600',
            boxShadow:'0 4px 16px rgba(0,0,0,.2)', maxWidth:'90vw', textAlign:'center',
        }).text(msg).appendTo('body');
        setTimeout(function () { $t.fadeOut(400, function () { $t.remove(); }); }, 3000);
    }

    function primerError(errors) {
        if (!errors) return null;
        var vals = Object.values(errors);
        return vals.length ? vals[0][0] : null;
    }

    function actualizarBtnAgregar() {
        var misCant = $('#rs-lista .rs-card[data-mio="1"]').length;
        if (misCant >= 3) {
            $('#btn-mostrar-form-nuevo').prop('disabled', true)
                .html('<i class="fa fa-check-circle"></i> Ya tienes 3 RFC registrados').show();
        } else {
            $('#btn-mostrar-form-nuevo').prop('disabled', false)
                .html('<i class="fa fa-plus-circle"></i> Agregar mi RFC').show();
        }
    }

    function htmlVacio() {
        return '<div class="rs-empty" id="rs-vacio">'
             + '<i class="fa fa-building-o"></i>'
             + '<p>Aún no tienes RFC registrados.</p>'
             + '<small>Usa el botón de arriba para agregar tus datos fiscales.</small>'
             + '</div>';
    }

    function optsHtml(opciones, valorActual) {
        return Object.entries(opciones).map(function (kv) {
            return '<option value="' + kv[0] + '"' + (kv[0] === valorActual ? ' selected' : '') + '>' + kv[1] + '</option>';
        }).join('');
    }

    function agregarItemAlListado(rs) {
        $('#rs-vacio').remove();

        var badgePrincipal = rs.es_principal
            ? '<span class="rs-badge rs-badge-principal badge-principal"><i class="fa fa-star"></i> Principal</span>'
            : '';
        var btnPrincipal = rs.es_principal
            ? ''
            : '<button class="rs-accion rs-accion-principal btn-principal-rs"><i class="fa fa-star-o"></i> Principal</button>';
        var claseAcciones = rs.es_principal ? 'dos' : 'tres';

        var html =
            '<div class="rs-card" data-id="' + rs.id + '" data-mio="1">'
          + '<div class="rs-vista">'
          + '<div class="rs-card-top"><div style="flex:1;min-width:0;">'
          + '<div class="rs-card-badges">'
          + badgePrincipal
          + '<span class="badge-principal-placeholder"></span>'
          + '<span class="rs-badge rs-badge-mio"><i class="fa fa-user"></i> Yo</span>'
          + '</div>'
          + '<div class="rs-card-rfc rs-rfc-texto">' + rs.rfc + '</div>'
          + '<div class="rs-card-nombre rs-razon-social-texto">' + rs.razon_social + '</div>'
          + '</div></div>'
          + '<div class="rs-card-datos">'
          + '<div class="rs-dato"><div class="rs-dato-label">Régimen fiscal</div><div class="rs-dato-valor rs-regimen-texto">' + (regimenOpciones[rs.regimen_fiscal] || rs.regimen_fiscal) + '</div></div>'
          + '<div class="rs-dato"><div class="rs-dato-label">CP fiscal</div><div class="rs-dato-valor rs-cp-texto">' + rs.domicilio_fiscal + '</div></div>'
          + '<div class="rs-dato" style="grid-column:1/-1;border-right:none;border-top:1px solid #f0f3f7;"><div class="rs-dato-label">Uso de factura</div><div class="rs-dato-valor rs-uso-texto">' + (cfdiOpciones[rs.uso_cfdi_default] || rs.uso_cfdi_default) + '</div></div>'
          + '</div>'
          + '<div class="rs-card-acciones ' + claseAcciones + '">'
          + btnPrincipal
          + '<button class="rs-accion rs-accion-editar btn-editar-rs"><i class="fa fa-pencil"></i> Editar</button>'
          + '<button class="rs-accion rs-accion-eliminar btn-eliminar-rs"><i class="fa fa-trash"></i> Eliminar</button>'
          + '</div>'
          + '</div>'
          + '<div class="rs-form-editar" style="display:none;">'
          + '<div class="rs-form-titulo" style="margin-bottom:14px;"><i class="fa fa-pencil"></i> Editar RFC</div>'
          + '<div class="rs-form-group"><label class="rs-form-label">Nombre o razón social <span class="rs-requerido">*</span></label>'
          + '<input type="text" class="rs-control editar-razon-social" value="' + rs.razon_social + '" maxlength="300"></div>'
          + '<div class="rs-grid-2">'
          + '<div class="rs-form-group"><label class="rs-form-label">Código postal fiscal <span class="rs-requerido">*</span></label>'
          + '<input type="text" class="rs-control editar-cp" value="' + rs.domicilio_fiscal + '" maxlength="5" inputmode="numeric"></div>'
          + '<div class="rs-form-group" style="min-width:0;"><label class="rs-form-label">Uso de factura <span class="rs-requerido">*</span></label>'
          + '<select class="rs-control editar-uso-cfdi"><option value="">-- Seleccionar --</option>' + optsHtml(cfdiOpciones, rs.uso_cfdi_default) + '</select></div>'
          + '</div>'
          + '<div class="rs-form-group"><label class="rs-form-label">Régimen fiscal <span class="rs-requerido">*</span></label>'
          + '<select class="rs-control editar-regimen"><option value="">-- Seleccionar --</option>' + optsHtml(regimenOpciones, rs.regimen_fiscal) + '</select></div>'
          + '<label class="rs-check-row"><input type="checkbox" class="editar-principal"' + (rs.es_principal ? ' checked' : '') + '>'
          + '<span class="rs-check-label"><i class="fa fa-star" style="color:#d97706;margin-right:4px;"></i> Usar como RFC principal</span></label>'
          + '<div class="rs-form-btns">'
          + '<button type="button" class="rs-btn rs-btn-cancel btn-cancelar-editar"><i class="fa fa-times"></i> Cancelar</button>'
          + '<button type="button" class="rs-btn rs-btn-save btn-guardar-editar"><i class="fa fa-save"></i> Guardar</button>'
          + '</div>'
          + '<div class="rs-alerta editar-alerta"></div>'
          + '</div>'
          + '</div>';

        $('#rs-lista').append(html);
        actualizarBtnAgregar();
    }

    function refrescarVistaCard($card, rs) {
        $card.find('.rs-razon-social-texto').text(rs.razon_social);
        $card.find('.rs-regimen-texto').text(regimenOpciones[rs.regimen_fiscal] || rs.regimen_fiscal);
        $card.find('.rs-uso-texto').text(cfdiOpciones[rs.uso_cfdi_default] || rs.uso_cfdi_default);
        $card.find('.rs-cp-texto').text(rs.domicilio_fiscal);
        $card.find('.editar-razon-social').val(rs.razon_social);
        $card.find('.editar-regimen').val(rs.regimen_fiscal);
        $card.find('.editar-cp').val(rs.domicilio_fiscal);
        $card.find('.editar-uso-cfdi').val(rs.uso_cfdi_default);
        $card.find('.editar-principal').prop('checked', rs.es_principal == 1);
        $card.find('.rs-form-editar').hide();
        ocultarAlerta($card.find('.editar-alerta'));
        $card.find('.rs-vista').show();
        $card.find('.btn-guardar-editar').prop('disabled', false).html('<i class="fa fa-save"></i> Guardar');
    }

});
</script>
@endpush
