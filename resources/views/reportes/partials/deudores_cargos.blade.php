<table class="deu-cargos">
    <thead>
        <tr>
            <th>Concepto</th>
            <th>Mes</th>
            <th>Vence</th>
            <th>Estado</th>
            <th style="text-align:right;">Importe</th>
            <th style="text-align:right;">Abonado</th>
            <th style="text-align:right;">Saldo</th>
        </tr>
    </thead>
    <tbody>
        @forelse($cargos as $cargo)
        <tr class="{{ $cargo['vencido'] ? 'es-vencido' : '' }}">
            <td>{{ $cargo['concepto'] }}</td>
            <td style="font-weight:600;">{{ $cargo['mes'] }}</td>
            <td>{{ $cargo['fecha_vencimiento']?->format('d/m/Y') ?? '—' }}</td>
            <td>
                <span class="deu-badge deu-{{ $cargo['estado'] }}">{{ ucfirst($cargo['estado']) }}</span>
                @if($cargo['estado'] === 'vencido' && $cargo['saldo_abonado'] > 0)
                    <span style="font-size:10px;color:#64748b;">con abono</span>
                @endif
            </td>
            <td style="text-align:right;">${{ number_format($cargo['monto_original'], 2) }}</td>
            <td style="text-align:right;">${{ number_format($cargo['saldo_abonado'], 2) }}</td>
            <td style="text-align:right;font-weight:700;">${{ number_format($cargo['saldo_pendiente'], 2) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="7" style="text-align:center;color:#b0bec5;">Sin cargos adeudados.</td>
        </tr>
        @endforelse
    </tbody>
</table>
