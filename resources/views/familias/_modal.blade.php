{{--
    Vista parcial: resources/views/familias/_modal.blade.php
    Se carga vía AJAX desde FamiliaController::show() con ?_modal=1
    Optimizado para consulta rápida de contactos por recepción
--}}

{{-- ══ CONTACTOS — protagonista ══ --}}
<div style="padding:0;">

    {{-- Cabecera contactos --}}
    <div style="
        background:linear-gradient(135deg,#2c6fad 0%,#3c8dbc 100%);
        padding:12px 18px 10px;
        display:flex;align-items:center;gap:10px;
    ">
        <i class="fa fa-phone" style="color:#fff;font-size:16px;"></i>
        <span style="color:#fff;font-weight:700;font-size:14px;letter-spacing:.02em;">
            Contactos familiares
        </span>
        <span style="background:rgba(255,255,255,.25);color:#fff;border-radius:10px;
                     padding:1px 8px;font-size:12px;font-weight:600;">
            {{ $familia->contactos->count() }}
        </span>
    </div>

    {{-- Tarjetas de contacto --}}
    <div style="padding:12px 14px;">
        @forelse($familia->contactos->sortBy('pivot.orden') as $contacto)
        @php
            $esPrincipal = isset($contacto->pivot) && $contacto->pivot->orden == 1;
            $ac = $contacto->alumnoContactos->first();
        @endphp

        <div style="
            background:{{ $esPrincipal ? '#f0f7ff' : '#fff' }};
            border:1px solid {{ $esPrincipal ? '#b8d4f0' : '#e8e8e8' }};
            border-left:4px solid {{ $esPrincipal ? '#3c8dbc' : '#d0d0d0' }};
            border-radius:6px;padding:12px 14px;margin-bottom:10px;
        ">
            {{-- Nombre y badges --}}
            <div style="display:flex;align-items:flex-start;gap:10px;margin-bottom:10px;">
                <div style="
                    width:40px;height:40px;border-radius:50%;flex-shrink:0;
                    background:{{ $esPrincipal ? '#3c8dbc' : '#9e9e9e' }};
                    display:flex;align-items:center;justify-content:center;overflow:hidden;
                ">
                    @if($contacto->foto_url)
                        <img src="{{ asset('storage/'.$contacto->foto_url) }}"
                             style="width:100%;height:100%;object-fit:cover;">
                    @else
                        <i class="fa fa-user" style="color:#fff;font-size:17px;"></i>
                    @endif
                </div>
                <div style="flex:1;">
                    <div style="font-size:16px;font-weight:700;color:#1a1a1a;line-height:1.2;">
                        {{ $contacto->nombre }}
                        {{ $contacto->ap_paterno }}
                        {{ $contacto->ap_materno }}
                    </div>
                    <div style="margin-top:4px;display:flex;gap:5px;flex-wrap:wrap;">
                        @if($esPrincipal)
                        <span style="background:#3c8dbc;color:#fff;font-size:10px;font-weight:700;
                                     padding:1px 8px;border-radius:10px;letter-spacing:.03em;">
                            PRINCIPAL
                        </span>
                        @endif
                        @if($ac && $ac->parentesco)
                        <span style="background:#f0f0f0;color:#555;font-size:10px;font-weight:600;
                                     padding:1px 8px;border-radius:10px;">
                            {{ ucfirst($ac->parentesco) }}
                        </span>
                        @endif
                        @if($ac && $ac->autorizado_recoger)
                        <span style="background:#e8f5e9;color:#2e7d32;font-size:10px;font-weight:600;
                                     padding:1px 8px;border-radius:10px;">
                            <i class="fa fa-check"></i> Autorizado recoger
                        </span>
                        @endif
                        @if($contacto->tiene_acceso_portal)
                        <span style="background:#e3f2fd;color:#1565c0;font-size:10px;font-weight:600;
                                     padding:1px 8px;border-radius:10px;">
                            <i class="fa fa-globe"></i> Portal
                        </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Teléfonos — grandes y clickeables --}}
            <div style="display:flex;flex-direction:column;gap:6px;">

                @if($contacto->telefono_celular)
                <a href="tel:{{ preg_replace('/\D/','',$contacto->telefono_celular) }}" style="
                    display:flex;align-items:center;gap:12px;
                    background:#fff;border:1px solid #c8dff5;border-radius:7px;
                    padding:9px 14px;text-decoration:none;color:#1a1a1a;
                ">
                    <div style="width:32px;height:32px;border-radius:50%;background:#3c8dbc;
                                display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fa fa-mobile" style="color:#fff;font-size:17px;"></i>
                    </div>
                    <div style="flex:1;">
                        <div style="font-size:19px;font-weight:700;letter-spacing:.04em;
                                    color:#1a1a1a;line-height:1;">
                            {{ $contacto->telefono_celular }}
                        </div>
                        <div style="font-size:10px;color:#888;margin-top:1px;">Celular</div>
                    </div>
                    <i class="fa fa-phone" style="color:#3c8dbc;font-size:14px;"></i>
                </a>
                @endif

                @if($contacto->telefono_trabajo)
                <a href="tel:{{ preg_replace('/\D/','',$contacto->telefono_trabajo) }}" style="
                    display:flex;align-items:center;gap:12px;
                    background:#fff;border:1px solid #e0e0e0;border-radius:7px;
                    padding:9px 14px;text-decoration:none;color:#1a1a1a;
                ">
                    <div style="width:32px;height:32px;border-radius:50%;background:#607d8b;
                                display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fa fa-phone" style="color:#fff;font-size:14px;"></i>
                    </div>
                    <div style="flex:1;">
                        <div style="font-size:19px;font-weight:700;letter-spacing:.04em;
                                    color:#1a1a1a;line-height:1;">
                            {{ $contacto->telefono_trabajo }}
                        </div>
                        <div style="font-size:10px;color:#888;margin-top:1px;">Trabajo</div>
                    </div>
                    <i class="fa fa-phone" style="color:#888;font-size:13px;"></i>
                </a>
                @endif

                @if($contacto->email)
                <div style="display:flex;align-items:center;gap:12px;padding:5px 4px;">
                    <i class="fa fa-envelope-o" style="color:#aaa;font-size:14px;width:32px;text-align:center;"></i>
                    <span style="font-size:13px;color:#555;">{{ $contacto->email }}</span>
                </div>
                @endif

                @if(!$contacto->telefono_celular && !$contacto->telefono_trabajo && !$contacto->email)
                <div style="font-size:12px;color:#ccc;padding:4px 4px;">
                    <i class="fa fa-info-circle"></i> Sin datos de contacto
                </div>
                @endif

            </div>
        </div>
        @empty
        <div style="text-align:center;padding:30px 0;color:#ccc;">
            <i class="fa fa-phone" style="font-size:30px;display:block;margin-bottom:8px;"></i>
            Sin contactos registrados
        </div>
        @endforelse
    </div>

    {{-- ══ DIVISOR ══ --}}
    <div style="border-top:2px solid #f0f0f0;"></div>

    {{-- ══ ALUMNOS — sección secundaria ══ --}}
    <div style="padding:12px 14px 14px;">
        <div style="font-size:10px;text-transform:uppercase;color:#aaa;font-weight:700;
                    letter-spacing:.06em;margin-bottom:8px;display:flex;align-items:center;gap:6px;">
            <i class="fa fa-graduation-cap" style="color:#3c8dbc;"></i>
            Alumnos
            <span style="background:#e8f0fa;color:#3c8dbc;border-radius:8px;
                          padding:0 6px;font-size:10px;">
                {{ $familia->alumnos->count() }}
            </span>
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
        <div style="display:flex;align-items:center;gap:10px;padding:8px 10px;
                    border-radius:6px;background:#fafafa;border:1px solid #f0f0f0;margin-bottom:6px;">
            <div style="width:32px;height:32px;border-radius:50%;flex-shrink:0;
                        background:#e0e0e0;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                @if($alumno->foto_url)
                    <img src="{{ asset('storage/'.$alumno->foto_url) }}" style="width:100%;height:100%;object-fit:cover;">
                @else
                    <i class="fa fa-user" style="color:#bbb;font-size:13px;"></i>
                @endif
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:13px;font-weight:600;color:#333;display:flex;align-items:center;gap:5px;">
                    <a href="{{ route('alumnos.show',$alumno->id) }}" target="_blank" style="color:#333;text-decoration:none;">
                        {{ $alumno->nombre }} {{ $alumno->ap_paterno }} {{ $alumno->ap_materno }}
                    </a>
                    <span style="display:inline-block;width:7px;height:7px;
                                 border-radius:50%;background:{{ $dot }};flex-shrink:0;"></span>
                </div>
                <div style="font-size:11px;color:#aaa;margin-top:1px;">
                    <code style="font-size:10px;background:#efefef;padding:0 3px;border-radius:2px;">
                        {{ $alumno->matricula }}
                    </code>
                    @if($ins)
                        &nbsp;·&nbsp;
                        {{ $ins->grupo->grado->nivel->nombre ?? '' }}
                        {{ $ins->grupo->grado->nombre }}°
                        {{ $ins->grupo->nombre }}
                    @endif
                </div>
            </div>
            <a href="{{ route('alumnos.show',$alumno->id) }}" target="_blank"
               class="btn btn-default btn-xs btn-flat" title="Ver ficha">
                <i class="fa fa-external-link"></i>
            </a>
        </div>
        @empty
        <p style="font-size:12px;color:#ccc;">Sin alumnos registrados.</p>
        @endforelse
    </div>

    {{-- ══ PIE de familia ══ --}}
    <div style="background:#f8f8f8;border-top:1px solid #eee;padding:7px 18px;
                display:flex;gap:12px;align-items:center;font-size:12px;color:#999;">
        <i class="fa fa-home" style="color:#3c8dbc;"></i>
        <strong style="color:#555;">{{ $familia->apellido_familia }}</strong>
        <span class="label label-{{ $familia->activo ? 'success' : 'default' }}" style="font-size:10px;">
            {{ $familia->activo ? 'Activa' : 'Inactiva' }}
        </span>
        @if($familia->observaciones)
        <span style="margin-left:auto;font-style:italic;">
            {{ Str::limit($familia->observaciones, 55) }}
        </span>
        @endif
    </div>

</div>
