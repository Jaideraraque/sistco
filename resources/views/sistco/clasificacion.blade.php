@extends('sistco.layout')
@section('title', 'Clasificación de Clientes')
@section('page-title', 'Clasificación de Clientes')

@section('content')

{{-- KPIs --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px">
    <div style="background:#fff;border-radius:10px;padding:16px;border:0.5px solid #e8e8e8;border-left:4px solid #0099D6">
        <div style="font-size:11px;color:#888;margin-bottom:6px">Total Clientes</div>
        <div style="font-size:26px;font-weight:700;color:#1A1A2E">{{ $kpis['total'] }}</div>
    </div>
    <div style="background:#fff;border-radius:10px;padding:16px;border:0.5px solid #e8e8e8;border-left:4px solid #E74C3C">
        <div style="font-size:11px;color:#888;margin-bottom:6px">🔴 Riesgo Alto</div>
        <div style="font-size:26px;font-weight:700;color:#E74C3C">{{ $kpis['alto'] }}</div>
        <div style="font-size:10px;color:#aaa;margin-top:2px">Contactar inmediato</div>
    </div>
    <div style="background:#fff;border-radius:10px;padding:16px;border:0.5px solid #e8e8e8;border-left:4px solid #F39C12">
        <div style="font-size:11px;color:#888;margin-bottom:6px">🟡 Riesgo Medio</div>
        <div style="font-size:26px;font-weight:700;color:#F39C12">{{ $kpis['medio'] }}</div>
        <div style="font-size:10px;color:#aaa;margin-top:2px">Monitorear</div>
    </div>
    <div style="background:#fff;border-radius:10px;padding:16px;border:0.5px solid #e8e8e8;border-left:4px solid #27AE60">
        <div style="font-size:11px;color:#888;margin-bottom:6px">🟢 Riesgo Bajo</div>
        <div style="font-size:26px;font-weight:700;color:#27AE60">{{ $kpis['bajo'] }}</div>
        <div style="font-size:10px;color:#aaa;margin-top:2px">Sin acción requerida</div>
    </div>
</div>

{{-- Filtros --}}
<div style="background:#fff;border-radius:10px;padding:16px;border:0.5px solid #e8e8e8;margin-bottom:16px">
    <form method="GET" action="{{ route('clasificacion') }}" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap">

        <div style="flex:1;min-width:160px">
            <label style="font-size:11px;color:#888;display:block;margin-bottom:4px">Buscar por código</label>
            <input type="text" name="buscar" value="{{ request('buscar') }}"
                placeholder="Ej: 145"
                style="width:100%;border:1px solid #e0e0e0;border-radius:7px;padding:8px 12px;font-size:13px;outline:none">
        </div>

        <div style="flex:1;min-width:140px">
            <label style="font-size:11px;color:#888;display:block;margin-bottom:4px">Nivel de riesgo</label>
            <select name="riesgo"
                style="width:100%;border:1px solid #e0e0e0;border-radius:7px;padding:8px 12px;font-size:13px;outline:none;background:#fff">
                <option value="">Todos</option>
                <option value="Alto"   {{ request('riesgo') == 'Alto'  ? 'selected' : '' }}>🔴 Alto</option>
                <option value="Medio"  {{ request('riesgo') == 'Medio' ? 'selected' : '' }}>🟡 Medio</option>
                <option value="Bajo"   {{ request('riesgo') == 'Bajo'  ? 'selected' : '' }}>🟢 Bajo</option>
            </select>
        </div>

        <div style="flex:1;min-width:140px">
            <label style="font-size:11px;color:#888;display:block;margin-bottom:4px">Municipio</label>
            <select name="municipio"
                style="width:100%;border:1px solid #e0e0e0;border-radius:7px;padding:8px 12px;font-size:13px;outline:none;background:#fff">
                <option value="">Todos</option>
                @foreach($municipios as $mun)
                    <option value="{{ $mun }}" {{ request('municipio') == $mun ? 'selected' : '' }}>
                        {{ $mun }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="display:flex;gap:8px">
            <button type="submit"
                style="background:#0099D6;color:#fff;border:none;border-radius:7px;padding:8px 18px;font-size:13px;font-weight:600;cursor:pointer">
                Filtrar
            </button>
            <a href="{{ route('clasificacion') }}"
                style="background:#f0f0f0;color:#555;border:none;border-radius:7px;padding:8px 14px;font-size:13px;cursor:pointer;text-decoration:none;display:flex;align-items:center">
                Limpiar
            </a>
        </div>

    </form>
</div>

{{-- Tabla --}}
<div style="background:#fff;border-radius:10px;padding:20px;border:0.5px solid #e8e8e8">

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
        <div>
            <div style="font-size:15px;font-weight:700;color:#1A1A2E">Clasificación de Clientes por Riesgo de Incumplimiento</div>
            <div style="font-size:12px;color:#888;margin-top:3px">
                Modelo: Regresión Logística &nbsp;|&nbsp; AUC-ROC: 0.9655 &nbsp;|&nbsp;
                Mostrando {{ $clientes->firstItem() }}–{{ $clientes->lastItem() }} de {{ $clientes->total() }} clientes
            </div>
        </div>
        <span style="background:#E8F5E9;color:#27AE60;padding:5px 14px;border-radius:20px;font-size:12px;font-weight:600;border:1px solid #A9DFBF">
            ✓ ML Activo — BD
        </span>
    </div>

    <table style="width:100%;border-collapse:collapse;font-size:13px">
        <thead>
            <tr style="background:#F0F4F8">
                <th style="padding:10px 14px;text-align:left;color:#555;font-weight:600;font-size:12px">Código</th>
                <th style="padding:10px 14px;text-align:left;color:#555;font-weight:600;font-size:12px">Municipio</th>
                <th style="padding:10px 14px;text-align:left;color:#555;font-weight:600;font-size:12px">Mensualidad</th>
                <th style="padding:10px 14px;text-align:left;color:#555;font-weight:600;font-size:12px">Megas</th>
                <th style="padding:10px 14px;text-align:left;color:#555;font-weight:600;font-size:12px">Antigüedad</th>
                <th style="padding:10px 14px;text-align:left;color:#555;font-weight:600;font-size:12px">Mora Histórica</th>
                <th style="padding:10px 14px;text-align:center;color:#555;font-weight:600;font-size:12px">Riesgo ML</th>
                <th style="padding:10px 14px;text-align:left;color:#555;font-weight:600;font-size:12px">Acción Sugerida</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clientes as $cliente)
            @php
                $nivel = $cliente->nivel_riesgo_ml ?? 'Sin datos';
                $prob  = $cliente->probabilidad_ml ?? null;

                $badgeStyle = match($nivel) {
                    'Alto'  => 'background:#FDEDEC;color:#E74C3C;border:1px solid #F5C6CB;',
                    'Medio' => 'background:#FEF9E7;color:#F39C12;border:1px solid #FDEBD0;',
                    'Bajo'  => 'background:#EAFAF1;color:#27AE60;border:1px solid #A9DFBF;',
                    default => 'background:#F0F4F8;color:#888;border:1px solid #ddd;',
                };
                $emoji = match($nivel) {
                    'Alto'  => '🔴',
                    'Medio' => '🟡',
                    'Bajo'  => '🟢',
                    default => '⚪',
                };
                $accion = match($nivel) {
                    'Alto'  => 'Contactar de inmediato',
                    'Medio' => 'Enviar recordatorio',
                    'Bajo'  => 'Sin acción requerida',
                    default => '—',
                };
            @endphp
            <tr style="border-bottom:1px solid #F0F4F8">
                <td style="padding:10px 14px;font-weight:600;color:#1A1A2E">{{ $cliente->codigo_cliente }}</td>
                <td style="padding:10px 14px;color:#555">{{ $cliente->municipio ?? '—' }}</td>
                <td style="padding:10px 14px;color:#555">${{ number_format($cliente->mensualidad * 1000, 0, ',', '.') }}</td>
                <td style="padding:10px 14px;color:#555">{{ $cliente->megas ?? '—' }}</td>
                <td style="padding:10px 14px;color:#555">{{ round($cliente->antiguedad_meses) }} meses</td>
                <td style="padding:10px 14px;color:#555">{{ number_format($cliente->tasa_mora_historica * 100, 1) }}%</td>
                <td style="padding:10px 14px;text-align:center">
                    @if($prob !== null)
                        <span style="padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;{{ $badgeStyle }}">
                            {{ $emoji }} {{ $nivel }} — {{ $prob }}%
                        </span>
                    @else
                        <span style="color:#aaa;font-size:12px">Sin datos</span>
                    @endif
                </td>
                <td style="padding:10px 14px;color:#666;font-size:12px">{{ $accion }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="padding:30px;text-align:center;color:#aaa;font-size:13px">
                    No se encontraron clientes con los filtros aplicados.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Paginación --}}
    @if($clientes->hasPages())
    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;padding-top:16px;border-top:1px solid #F0F4F8">
        <div style="font-size:12px;color:#888">
            Página {{ $clientes->currentPage() }} de {{ $clientes->lastPage() }}
        </div>
        <div style="display:flex;gap:6px;align-items:center">
            @if($clientes->onFirstPage())
                <span style="padding:6px 14px;border-radius:7px;background:#f0f0f0;color:#ccc;font-size:13px">← Anterior</span>
            @else
                <a href="{{ $clientes->previousPageUrl() }}"
                    style="padding:6px 14px;border-radius:7px;background:#fff;border:1px solid #e0e0e0;color:#555;font-size:13px;text-decoration:none">
                    ← Anterior
                </a>
            @endif

            @foreach($clientes->getUrlRange(max(1, $clientes->currentPage()-2), min($clientes->lastPage(), $clientes->currentPage()+2)) as $page => $url)
                @if($page == $clientes->currentPage())
                    <span style="padding:6px 12px;border-radius:7px;background:#0099D6;color:#fff;font-size:13px;font-weight:600">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" style="padding:6px 12px;border-radius:7px;background:#fff;border:1px solid #e0e0e0;color:#555;font-size:13px;text-decoration:none">{{ $page }}</a>
                @endif
            @endforeach

            @if($clientes->hasMorePages())
                <a href="{{ $clientes->nextPageUrl() }}"
                    style="padding:6px 14px;border-radius:7px;background:#fff;border:1px solid #e0e0e0;color:#555;font-size:13px;text-decoration:none">
                    Siguiente →
                </a>
            @else
                <span style="padding:6px 14px;border-radius:7px;background:#f0f0f0;color:#ccc;font-size:13px">Siguiente →</span>
            @endif
        </div>
    </div>
    @endif

</div>

@endsection