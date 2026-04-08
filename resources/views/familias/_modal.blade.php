{{--
    Vista parcial: resources/views/familias/_modal.blade.php
    Se carga vía AJAX desde FamiliaController::show()
    cuando la petición incluye ?_modal=1
--}}
<div style="display:flex;gap:0;">

    {{-- Columna principal: Alumnos + Contactos --}}
    <div style="flex:1;padding:16px;border-right:1px solid #f0f0f0;overflow:hidden;">

        {{-- Alumnos --}}
        <strong style="font-size:13px;color:#444;display:block;margin-bottom:10px;">
            <i class="fa fa-graduation-cap" style="color:#3c8dbc;"></i>
            Alumnos
            <span class="badge bg-blue" style="margin-left:4px;">{{ $familia->alumnos->count() }}</span>
        </strong>

        @forelse($familia->alumnos->sortBy('ap_paterno') as $alumno)
        @php
            $ins = $alumno->inscripciones->sortByDesc('id')->first();
            $dot = match($alumno->estado) {
                'activo' => '#00a65a', 'baja_temporal' => '#f39c12',
                'baja_definitiva' => '#dd4b39', default => '#aaa'
            };
        @endphp
        <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid #f8f8f8;">
            <div style="width:40px;height:40px;border-radius:50%;overflow:hidden;flex-shrink:0;
                        background:#e8e8e8;display:flex;align-items:center;justify-content:center;">
                @if($alumno->foto_url)
                    <img src="{{ asset('storage/'.$alumno->foto_url) }}"
                         style="width:100%;height:100%;object-fit:cover;">
                @else
                    <i class="fa fa-user" style="color:#bbb;font-size:16px;"></i>
                @endif
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:13px;font-weight:600;">
                    <a href="{{ route('alumnos.show',$alumno->id) }}" target="_blank" style="color:#333;">
                        {{ $alumno->nombre }} {{ $alumno->ap_paterno }} {{ $alumno->ap_materno }}
                    </a>
                    <span style="display:inline-block;width:8px;height:8px;border-radius:50%;
                                 background:{{ $dot }};margin-left:4px;vertical-align:middle;"></span>
                </div>
                <div style="font-size:11px;color:#888;margin-top:2px;">
                    <code style="font-size:10px;">{{ $alumno->matricula }}</code>
                    @if($alumno->fecha_nacimiento)
                        &nbsp;·&nbsp; {{ $alumno->fecha_nacimiento->age }} años
                    @endif
                    @if($ins)
                        &nbsp;·&nbsp;
                        {{ $ins->grupo->grado->nivel->nombre ?? '' }}
                        {{ $ins->grupo->grado->nombre }}° {{ $ins->grupo->nombre }}
                    @endif
                </div>
            </div>
            <a href="{{ route('alumnos.show',$alumno->id) }}" target="_blank"
               class="btn btn-default btn-xs btn-flat"><i class="fa fa-eye"></i></a>
        </div>
        @empty
        <p class="text-muted" style="font-size:12px;">Sin alumnos registrados.</p>
        @endforelse

        {{-- Contactos --}}
        <strong style="font-size:13px;color:#444;display:block;margin:16px 0 10px;">
            <i class="fa fa-phone" style="color:#3c8dbc;"></i>
            Contactos
            <span class="badge" style="margin-left:4px;background:#777;">{{ $familia->contactos->count() }}</span>
        </strong>

        @forelse($familia->contactos->sortBy('pivot.orden') as $contacto)
        <div style="display:flex;gap:10px;padding:8px 0;border-bottom:1px solid #f8f8f8;">
            <div style="width:36px;height:36px;border-radius:50%;overflow:hidden;flex-shrink:0;
                        background:#f0f0f0;display:flex;align-items:center;justify-content:center;">
                @if($contacto->foto_url)
                    <img src="{{ asset('storage/'.$contacto->foto_url) }}"
                         style="width:100%;height:100%;object-fit:cover;">
                @else
                    <i class="fa fa-user" style="color:#ccc;font-size:14px;"></i>
                @endif
            </div>
            <div style="flex:1;">
                <div style="font-size:13px;font-weight:600;">
                    {{ $contacto->nombre }} {{ $contacto->ap_paterno }} {{ $contacto->ap_materno }}
                    @if(isset($contacto->pivot) && $contacto->pivot->orden == 1)
                        <span class="label label-primary" style="font-size:9px;">Principal</span>
                    @endif
                    @if($contacto->tiene_acceso_portal)
                        <span class="label label-info" style="font-size:9px;">
                            <i class="fa fa-globe"></i> Portal
                        </span>
                    @endif
                </div>
                <div style="font-size:11px;color:#888;margin-top:3px;display:flex;flex-wrap:wrap;gap:10px;">
                    @if($contacto->telefono_celular)
                        <span><i class="fa fa-mobile"></i> {{ $contacto->telefono_celular }}</span>
                    @endif
                    @if($contacto->telefono_trabajo)
                        <span><i class="fa fa-phone"></i> {{ $contacto->telefono_trabajo }}</span>
                    @endif
                    @if($contacto->email)
                        <span><i class="fa fa-envelope-o"></i> {{ $contacto->email }}</span>
                    @endif
                </div>
                @if($contacto->alumnoContactos && $contacto->alumnoContactos->count())
                <div style="margin-top:4px;">
                    @foreach($contacto->alumnoContactos as $ac)
                        <span class="label label-default" style="font-size:9px;margin-right:2px;">
                            {{ ucfirst($ac->parentesco) }}
                            — {{ $ac->alumno->nombre ?? '' }} {{ $ac->alumno->ap_paterno ?? '' }}
                        </span>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        @empty
        <p class="text-muted" style="font-size:12px;">Sin contactos registrados.</p>
        @endforelse

    </div>

    {{-- Columna lateral: Resumen --}}
    <div style="width:190px;flex-shrink:0;padding:16px;background:#fafafa;">

        <div style="margin-bottom:14px;">
            <div style="font-size:10px;text-transform:uppercase;color:#bbb;margin-bottom:4px;">Estado</div>
            <span class="label label-{{ $familia->activo ? 'success' : 'default' }}" style="font-size:12px;">
                {{ $familia->activo ? 'Activa' : 'Inactiva' }}
            </span>
        </div>

        <div style="margin-bottom:14px;">
            <div style="font-size:10px;text-transform:uppercase;color:#bbb;margin-bottom:2px;">Alumnos activos</div>
            <div style="font-size:24px;font-weight:700;color:#3c8dbc;line-height:1;">
                {{ $familia->alumnos->where('estado','activo')->count() }}
            </div>
            <div style="font-size:11px;color:#bbb;">de {{ $familia->alumnos->count() }} total</div>
        </div>

        <div style="margin-bottom:14px;">
            <div style="font-size:10px;text-transform:uppercase;color:#bbb;margin-bottom:2px;">Contactos</div>
            <div style="font-size:24px;font-weight:700;color:#555;line-height:1;">
                {{ $familia->contactos->count() }}
            </div>
        </div>

        @php
            $hayDeuda = false;
            foreach($familia->alumnos->where('estado','activo') as $a) {
                $d = $a->inscripciones
                    ->flatMap(fn($i) => $i->cargos ?? collect())
                    ->whereIn('estado',['pendiente','parcial'])
                    ->sum('monto_original');
                if ($d > 0) { $hayDeuda = true; break; }
            }
        @endphp
        <div style="margin-bottom:14px;">
            <div style="font-size:10px;text-transform:uppercase;color:#bbb;margin-bottom:4px;">Cuenta</div>
            @if($hayDeuda)
                <span style="color:#dd4b39;font-size:12px;">
                    <i class="fa fa-exclamation-circle"></i> Con adeudo
                </span>
            @else
                <span style="color:#00a65a;font-size:12px;">
                    <i class="fa fa-check-circle"></i> Al corriente
                </span>
            @endif
        </div>

        @if($familia->observaciones)
        <div>
            <div style="font-size:10px;text-transform:uppercase;color:#bbb;margin-bottom:4px;">Notas</div>
            <div style="font-size:11px;color:#666;line-height:1.5;">
                {{ $familia->observaciones }}
            </div>
        </div>
        @endif

    </div>

</div>
