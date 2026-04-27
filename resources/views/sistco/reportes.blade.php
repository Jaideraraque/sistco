@extends('sistco.layout')
@section('title', 'Reportes')
@section('page-title', 'Reportes')

@section('content')

{{-- KPIs --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px">
    <div style="background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:14px 16px;border-left:4px solid #0099D6">
        <div style="font-size:11px;color:#888;margin-bottom:6px">Total clientes</div>
        <div style="font-size:22px;font-weight:700;color:#1a1a2e">{{ $totalClientes }}</div>
    </div>
    <div style="background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:14px 16px;border-left:4px solid #E74C3C">
        <div style="font-size:11px;color:#888;margin-bottom:6px">Clientes en mora</div>
        <div style="font-size:22px;font-weight:700;color:#E74C3C">{{ $clientesMorosos }}</div>
    </div>
    <div style="background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:14px 16px;border-left:4px solid #E74C3C">
        <div style="font-size:11px;color:#888;margin-bottom:6px">Tasa de morosidad</div>
        <div style="font-size:22px;font-weight:700;color:#E74C3C">{{ $tasaMorosidad }}%</div>
    </div>
    <div style="background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:14px 16px;border-left:4px solid #27AE60">
        <div style="font-size:11px;color:#888;margin-bottom:6px">Ingreso mensual total</div>
        <div style="font-size:17px;font-weight:700;color:#1a1a2e">${{ number_format($ingresoTotal, 0, ',', '.') }}</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:16px">

    {{-- Tabla municipios --}}
    <div>
        {{-- Riesgo ML --}}
        <div style="background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:16px;margin-bottom:16px">
            <div style="font-size:13px;font-weight:700;color:#1a1a2e;margin-bottom:14px;display:flex;align-items:center;gap:8px">
                Clasificación de Riesgo ML
                <span style="background:#E8F5E9;color:#27AE60;font-size:10px;font-weight:600;padding:2px 8px;border-radius:20px;border:1px solid #A9DFBF">✓ AUC-ROC: 0.9655</span>
            </div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px">
                <div style="background:#FDEDEC;border:1px solid #E74C3C;border-radius:8px;padding:14px;text-align:center">
                    <div style="font-size:28px;font-weight:700;color:#E74C3C">{{ $riesgoAlto }}</div>
                    <div style="font-size:11px;color:#555;margin-top:2px">Riesgo Alto</div>
                    <div style="font-size:10px;color:#E74C3C">{{ $totalClientes > 0 ? round($riesgoAlto/$totalClientes*100,1) : 0 }}%</div>
                </div>
                <div style="background:#FEF9E7;border:1px solid #F39C12;border-radius:8px;padding:14px;text-align:center">
                    <div style="font-size:28px;font-weight:700;color:#F39C12">{{ $riesgoMedio }}</div>
                    <div style="font-size:11px;color:#555;margin-top:2px">Riesgo Medio</div>
                    <div style="font-size:10px;color:#F39C12">{{ $totalClientes > 0 ? round($riesgoMedio/$totalClientes*100,1) : 0 }}%</div>
                </div>
                <div style="background:#E8F5E9;border:1px solid #27AE60;border-radius:8px;padding:14px;text-align:center">
                    <div style="font-size:28px;font-weight:700;color:#27AE60">{{ $riesgoBajo }}</div>
                    <div style="font-size:11px;color:#555;margin-top:2px">Riesgo Bajo</div>
                    <div style="font-size:10px;color:#27AE60">{{ $totalClientes > 0 ? round($riesgoBajo/$totalClientes*100,1) : 0 }}%</div>
                </div>
            </div>
        </div>

        {{-- Tabla municipios --}}
        <div style="background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:16px;margin-bottom:16px">
            <div style="font-size:13px;font-weight:700;color:#1a1a2e;margin-bottom:14px">Reporte por municipio</div>
            <table style="width:100%;border-collapse:collapse;font-size:12px">
                <thead>
                    <tr style="background:#F0F4F8">
                        <th style="padding:10px 12px;text-align:left;color:#555;font-weight:600;font-size:11px">Municipio</th>
                        <th style="padding:10px 12px;text-align:left;color:#555;font-weight:600;font-size:11px">Clientes</th>
                        <th style="padding:10px 12px;text-align:left;color:#555;font-weight:600;font-size:11px">Ingreso mensual</th>
                        <th style="padding:10px 12px;text-align:left;color:#555;font-weight:600;font-size:11px">% del total</th>
                        <th style="padding:10px 12px;text-align:left;color:#555;font-weight:600;font-size:11px">Mora prom.</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($porMunicipio as $item)
                    <tr style="border-bottom:1px solid #f5f5f5">
                        <td style="padding:10px 12px;color:#1a1a2e;font-weight:600">{{ $item->municipio }}</td>
                        <td style="padding:10px 12px;color:#555">{{ $item->total }}</td>
                        <td style="padding:10px 12px;color:#555">${{ number_format($item->ingreso, 0, ',', '.') }}</td>
                        <td style="padding:10px 12px;color:#555">{{ round(($item->total / $totalClientes) * 100, 1) }}%</td>
                        <td style="padding:10px 12px;color:#E74C3C;font-weight:600">{{ round($item->mora_prom * 100, 2) }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Top 10 riesgo alto --}}
        <div style="background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:16px">
            <div style="font-size:13px;font-weight:700;color:#1a1a2e;margin-bottom:14px">Top 10 clientes mayor riesgo</div>
            <table style="width:100%;border-collapse:collapse;font-size:12px">
                <thead>
                    <tr style="background:#F0F4F8">
                        <th style="padding:8px 12px;text-align:left;color:#555;font-weight:600;font-size:11px">Código</th>
                        <th style="padding:8px 12px;text-align:left;color:#555;font-weight:600;font-size:11px">Municipio</th>
                        <th style="padding:8px 12px;text-align:left;color:#555;font-weight:600;font-size:11px">Mensualidad</th>
                        <th style="padding:8px 12px;text-align:left;color:#555;font-weight:600;font-size:11px">Probabilidad ML</th>
                        <th style="padding:8px 12px;text-align:left;color:#555;font-weight:600;font-size:11px">Nivel</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topRiesgo as $c)
                    <tr style="border-bottom:1px solid #f5f5f5">
                        <td style="padding:8px 12px;font-weight:600;color:#1a1a2e">#{{ $c->codigo_cliente }}</td>
                        <td style="padding:8px 12px;color:#555">{{ $c->municipio }}</td>
                        <td style="padding:8px 12px;color:#555">${{ number_format($c->mensualidad * 1000, 0, ',', '.') }}</td>
                        <td style="padding:8px 12px;font-weight:600;color:#E74C3C">{{ round($c->probabilidad_ml, 2) }}%</td>
                        <td style="padding:8px 12px">
                            @if($c->nivel_riesgo_ml === 'Alto')
                                <span style="background:#FDEDEC;color:#E74C3C;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:600">Alto</span>
                            @elseif($c->nivel_riesgo_ml === 'Medio')
                                <span style="background:#FEF9E7;color:#F39C12;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:600">Medio</span>
                            @else
                                <span style="background:#E8F5E9;color:#27AE60;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:600">Bajo</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Panel exportar --}}
    <div style="display:flex;flex-direction:column;gap:16px">

        <div style="background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:16px">
            <div style="font-size:14px;font-weight:700;color:#1a1a2e;margin-bottom:6px">📊 Exportar reportes</div>
            <div style="font-size:11px;color:#888;margin-bottom:16px;line-height:1.5">Descarga el reporte en el formato que necesites para presentaciones o análisis.</div>

            <a href="{{ route('reportes.pdf') }}" target="_blank">
                <button style="width:100%;background:#E74C3C;color:#fff;border:none;border-radius:7px;padding:10px;font-size:12px;font-weight:600;cursor:pointer;margin-bottom:8px;text-align:left;padding-left:14px">
                    📄 Descargar PDF ejecutivo
                </button>
            </a>
            <a href="{{ route('reportes.excel') }}">
                <button style="width:100%;background:#27AE60;color:#fff;border:none;border-radius:7px;padding:10px;font-size:12px;font-weight:600;cursor:pointer;text-align:left;padding-left:14px">
                    📥 Descargar Excel / CSV
                </button>
            </a>

            <div style="margin-top:14px;padding:10px;background:#F8F8F8;border-radius:6px;font-size:10px;color:#888;line-height:1.5">
                El PDF incluye KPIs, clasificación ML, distribución por municipio y top 10 clientes en riesgo.<br><br>
                El CSV incluye todos los {{ $totalClientes }} clientes con sus métricas y predicciones ML.
            </div>
        </div>

        <div style="background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:16px">
            <div style="font-size:13px;font-weight:700;color:#1a1a2e;margin-bottom:12px">Resumen rápido</div>
            @php
                $indicadores = [
                    ['Total clientes',    $totalClientes,                         '#0099D6'],
                    ['Al día',            $totalClientes - $clientesMorosos,      '#27AE60'],
                    ['En mora',           $clientesMorosos,                        '#E74C3C'],
                    ['Tasa mora',         $tasaMorosidad . '%',                   '#E74C3C'],
                    ['Riesgo Alto ML',    $riesgoAlto,                            '#E74C3C'],
                    ['Riesgo Medio ML',   $riesgoMedio,                           '#F39C12'],
                    ['Riesgo Bajo ML',    $riesgoBajo,                            '#27AE60'],
                    ['Municipios',        $porMunicipio->count(),                 '#555'],
                ];
            @endphp
            @foreach($indicadores as [$label, $valor, $color])
            <div style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:0.5px solid #f5f5f5">
                <span style="font-size:12px;color:#888">{{ $label }}</span>
                <span style="font-size:12px;font-weight:600;color:{{ $color }}">{{ $valor }}</span>
            </div>
            @endforeach
        </div>

        <div style="background:#E6F5FC;border:1px solid #B3D9F0;border-radius:10px;padding:14px">
            <div style="font-size:11px;color:#0099D6;font-weight:600;margin-bottom:4px">ℹ️ Modelos ML activos</div>
            <div style="font-size:10px;color:#555;line-height:1.5">
                Regresión Logística · AUC-ROC: 0.9655<br>
                Regresión Lineal · MAPE: 0.74%<br>
                K-Means K=5 · Silueta: 0.4294
            </div>
        </div>
    </div>
</div>

@endsection
