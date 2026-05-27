{{--
    Vista parcial: resources/views/familias/_modal.blade.php
    Se carga vía AJAX desde FamiliaController::show() con ?_modal=1
    Optimizado para consulta rápida de contactos por recepción
--}}
<style>
/* ── Contacto card ───────────────────────── */
.mc-card {
    border-radius: 8px;
    border: 1px solid #e4eaf0;
    border-left: 4px solid #c8d8e8;
    margin-bottom: 10px;
    background: #fff;
    overflow: hidden;
}
.mc-card.principal {
    border-color: #b8d4f0;
    border-left-color: #3c8dbc;
    background: #f5faff;
}

/* ── Nombre + avatar ─────────────────────── */
.mc-head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px 10px;
}
.mc-avatar {
    width: 44px; height: 44px; border-radius: 50%;
    background: #9e9e9e;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; overflow: hidden;
    font-size: 18px; color: #fff;
}
.mc-avatar.principal { background: #3c8dbc; }
.mc-name {
    font-size: 15px; font-weight: 700;
    color: #1a2634; line-height: 1.25;
    flex: 1;
}
.mc-badges {
    display: flex; gap: 5px; flex-wrap: wrap;
    margin-top: 5px;
}
.mc-badge {
    display: inline-flex; align-items: center; gap: 3px;
    font-size: 10px; font-weight: 700;
    padding: 2px 8px; border-radius: 10px;
    letter-spacing: .02em; white-space: nowrap;
}
.mc-badge-principal { background: #3c8dbc; color: #fff; }
.mc-badge-parentesco { background: #f0f0f0; color: #555; }
.mc-badge-recoger { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
.mc-badge-pago    { background: #fff3e0; color: #e65100; border: 1px solid #ffe0b2; }
.mc-badge-portal  { background: #e3f2fd; color: #1565c0; border: 1px solid #bbdefb; }

/* ── Teléfonos ───────────────────────────── */
.mc-phones {
    display: flex; flex-direction: column;
    gap: 6px; padding: 0 12px 12px;
}
.mc-phone-row {
    display: flex; align-items: center; gap: 12px;
    background: #fff; border: 1px solid #dde8f5;
    border-radius: 7px; padding: 9px 13px;
    text-decoration: none; color: #1a1a1a;
    transition: background .12s, border-color .12s;
}
.mc-phone-row:hover { background: #edf5ff; border-color: #a8c8e8; color: #1a1a1a; }
.mc-phone-icon {
    width: 32px; height: 32px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.mc-phone-icon.celular { background: #3c8dbc; }
.mc-phone-icon.trabajo { background: #607d8b; }
.mc-phone-num {
    font-size: 19px; font-weight: 700;
    letter-spacing: .04em; line-height: 1; color: #1a1a1a;
}
.mc-phone-lbl { font-size: 10px; color: #999; margin-top: 2px; }
.mc-phone-arrow { color: #3c8dbc; margin-left: auto; font-size: 13px; }

.mc-email {
    display: flex; align-items: center; gap: 10px;
    padding: 5px 4px 0 4px; font-size: 13px; color: #555;
}

/* ── Sin contacto ────────────────────────── */
.mc-no-data {
    font-size: 12px; color: #bbb;
    padding: 4px 4px; display: flex; align-items: center; gap: 6px;
}

/* ── Alumnos mini-lista ───────────────────── */
.mc-alm-row {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 10px; border-radius: 6px;
    background: #fafafa; border: 1px solid #f0f0f0;
    margin-bottom: 6px;
}
.mc-alm-avatar {
    width: 32px; height: 32px; border-radius: 50%;
    background: #e0e0e0;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; overflow: hidden;
}
.mc-alm-name {
    font-size: 13px; font-weight: 600; color: #333;
    display: flex; align-items: center; gap: 5px;
}
.mc-alm-sub { font-size: 11px; color: #aaa; margin-top: 1px; }
.mc-status-dot {
    display: inline-block; width: 7px; height: 7px;
    border-radius: 50%; flex-shrink: 0;
}

/* ── Sección label ───────────────────────── */
.mc-section-label {
    font-size: 10px; text-transform: uppercase;
    color: #aaa; font-weight: 700; letter-spacing: .07em;
    margin-bottom: 8px;
    display: flex; align-items: center; gap: 6px;
}
.mc-section-label span {
    background: #e8f0fa; color: #3c8dbc;
    border-radius: 8px; padding: 0 6px; font-size: 10px;
}
</style>

<div>

    {{-- ══ CONTACTOS ══ --}}
    <div style="padding:14px 14px 4px;">

        <div class="mc-section-label" style="margin-bottom:12px;">
            <i class="fa fa-address-book-o" style="color:#3c8dbc;font-size:12px;"></i>
            Contactos familiares
            <span>{{ $familia->contactos->count() }}</span>
        </div>

        @forelse($familia->contactos->sortBy('pivot.orden') as $contacto)
        @php
            $esPrincipal = isset($contacto->pivot) && $contacto->pivot->orden == 1;
            $ac = $contacto->alumnoContactos->first();
        @endphp

        <div class="mc-card {{ $esPrincipal ? 'principal' : '' }}">

            {{-- Nombre + avatar + badges --}}
            <div class="mc-head">
                <div class="mc-avatar {{ $esPrincipal ? 'principal' : '' }}">
                    @if($contacto->foto_url)
                        <img src="{{ asset('storage/'.$contacto->foto_url) }}"
                             style="width:100%;height:100%;object-fit:cover;">
                    @else
                        <i class="fa fa-user" style="font-size:18px;"></i>
                    @endif
                </div>
                <div style="flex:1;min-width:0;">
                    <div class="mc-name">
                        {{ $contacto->nombre }}
                        {{ $contacto->ap_paterno }}
                        {{ $contacto->ap_materno }}
                    </div>
                    <div class="mc-badges">
                        @if($esPrincipal)
                        <span class="mc-badge mc-badge-principal">
                            <i class="fa fa-star" style="font-size:8px;"></i> PRINCIPAL
                        </span>
                        @endif
                        @if($ac && $ac->parentesco)
                        <span class="mc-badge mc-badge-parentesco">
                            {{ ucfirst($ac->parentesco) }}
                        </span>
                        @endif
                        @if($ac && $ac->autorizado_recoger)
                        <span class="mc-badge mc-badge-recoger">
                            <i class="fa fa-check"></i> Autorizado recoger
                        </span>
                        @endif
                        @if($ac && $ac->es_responsable_pago)
                        <span class="mc-badge mc-badge-pago">
                            <i class="fa fa-credit-card"></i> Responsable de pago
                        </span>
                        @endif
                        @if($contacto->tiene_acceso_portal)
                        <span class="mc-badge mc-badge-portal">
                            <i class="fa fa-globe"></i> Portal
                        </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Teléfonos y email --}}
            <div class="mc-phones">
                @if($contacto->telefono_celular)
                <a href="tel:{{ preg_replace('/\D/','',$contacto->telefono_celular) }}" class="mc-phone-row">
                    <div class="mc-phone-icon celular">
                        <i class="fa fa-mobile" style="color:#fff;font-size:18px;"></i>
                    </div>
                    <div style="flex:1;">
                        <div class="mc-phone-num">{{ $contacto->telefono_celular }}</div>
                        <div class="mc-phone-lbl">Celular</div>
                    </div>
                    <i class="fa fa-phone mc-phone-arrow"></i>
                </a>
                @endif

                @if($contacto->telefono_trabajo)
                <a href="tel:{{ preg_replace('/\D/','',$contacto->telefono_trabajo) }}" class="mc-phone-row"
                   style="border-color:#e0e0e0;">
                    <div class="mc-phone-icon trabajo">
                        <i class="fa fa-phone" style="color:#fff;font-size:14px;"></i>
                    </div>
                    <div style="flex:1;">
                        <div class="mc-phone-num">{{ $contacto->telefono_trabajo }}</div>
                        <div class="mc-phone-lbl">Trabajo</div>
                    </div>
                    <i class="fa fa-phone mc-phone-arrow" style="color:#888;"></i>
                </a>
                @endif

                @if($contacto->email)
                <div class="mc-email">
                    <i class="fa fa-envelope-o" style="color:#aaa;font-size:14px;width:32px;text-align:center;"></i>
                    <span>{{ $contacto->email }}</span>
                </div>
                @endif

                @if(!$contacto->telefono_celular && !$contacto->telefono_trabajo && !$contacto->email)
                <div class="mc-no-data">
                    <i class="fa fa-info-circle"></i> Sin datos de contacto registrados
                </div>
                @endif
            </div>

        </div>
        @empty
        <div style="text-align:center;padding:32px 0;color:#ccc;">
            <i class="fa fa-address-book-o" style="font-size:36px;display:block;margin-bottom:10px;color:#dde4ea;"></i>
            <div style="font-size:13px;">Sin contactos registrados</div>
        </div>
        @endforelse

    </div>

    {{-- ══ DIVISOR ══ --}}
    <div style="border-top:2px solid #f0f2f5;margin:0 14px;"></div>

    {{-- ══ ALUMNOS ══ --}}
    <div style="padding:12px 14px 14px;">
        <div class="mc-section-label">
            <i class="fa fa-graduation-cap" style="color:#3c8dbc;font-size:12px;"></i>
            Alumnos
            <span>{{ $familia->alumnos->count() }}</span>
        </div>

        @forelse($familia->alumnos->sortBy('ap_paterno') as $alumno)
        @php
            $ins = $alumno->inscripciones->sortByDesc('id')->first();
            $dot = match($alumno->estado) {
                'activo'          => '#00a65a',
                'baja_temporal'   => '#f39c12',
                'baja_definitiva' => '#dd4b39',
                default           => '#aaa',
            };
        @endphp
        <div class="mc-alm-row">
            <div class="mc-alm-avatar">
                @if($alumno->foto_url)
                    <img src="{{ asset('storage/'.$alumno->foto_url) }}"
                         style="width:100%;height:100%;object-fit:cover;">
                @else
                    <i class="fa fa-user" style="color:#bbb;font-size:13px;"></i>
                @endif
            </div>
            <div style="flex:1;min-width:0;">
                <div class="mc-alm-name">
                    <a href="{{ route('alumnos.show',$alumno->id) }}" target="_blank"
                       style="color:#333;text-decoration:none;">
                        {{ $alumno->ap_paterno }} {{ $alumno->ap_materno }}, {{ $alumno->nombre }}
                    </a>
                    <span class="mc-status-dot" style="background:{{ $dot }};"></span>
                </div>
                <div class="mc-alm-sub">
                    <code style="font-size:10px;background:#efefef;padding:0 3px;border-radius:2px;">
                        {{ $alumno->matricula }}
                    </code>
                    @if($ins)
                        &nbsp;·&nbsp;
                        {{ $ins->grupo->grado->nivel->nombre ?? '' }}
                        {{ $ins->grupo->grado->numero }}°
                        {{ $ins->grupo->nombre }}
                    @endif
                </div>
            </div>
            <a href="{{ route('alumnos.show',$alumno->id) }}" target="_blank"
               class="btn btn-default btn-xs btn-flat" title="Ver ficha"
               style="border-radius:4px;flex-shrink:0;">
                <i class="fa fa-external-link"></i>
            </a>
        </div>
        @empty
        <p style="font-size:12px;color:#ccc;margin:0;">Sin alumnos registrados.</p>
        @endforelse
    </div>

    {{-- ══ PIE ══ --}}
    <div style="background:#f8f9fb;border-top:1px solid #edf1f5;
                padding:8px 18px;display:flex;gap:10px;align-items:center;
                font-size:12px;color:#999;">
        <i class="fa fa-home" style="color:#3c8dbc;"></i>
        <strong style="color:#555;">{{ $familia->apellido_familia }}</strong>
        <span class="label label-{{ $familia->activo ? 'success' : 'default' }}" style="font-size:10px;">
            {{ $familia->activo ? 'Activa' : 'Inactiva' }}
        </span>
        @if($familia->observaciones)
        <span style="margin-left:auto;font-style:italic;color:#bbb;">
            {{ Str::limit($familia->observaciones, 55) }}
        </span>
        @endif
    </div>

</div>
