@extends('sistco.layout')
@section('title', 'Análisis Exploratorio')
@section('page-title', 'Análisis Exploratorio')
@section('styles')
<style>
.kpi-row{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px}
.kpi{background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:14px 16px}
.kpi-label{font-size:11px;color:#888;margin-bottom:8px;display:flex;align-items:center;justify-content:space-between}
.kpi-label svg{width:14px;height:14px}
.kpi-value{font-size:22px;font-weight:700;color:#1a1a2e;margin-bottom:4px}
.kpi-trend{font-size:11px}
.trend-up{color:#27AE60}.trend-down{color:#E74C3C}.trend-neutral{color:#888}
.row2{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px}
.row3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-bottom:16px}
.card{background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:16px}
.card-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:14px}
.card-title{font-size:13px;font-weight:600;color:#1a1a2e}
.card-sub{font-size:11px;color:#aaa;margin-top:2px}
.badge{padding:3px 8px;border-radius:20px;font-size:10px;font-weight:600}
.badge-blue{background:#E6F1FB;color:#0C447C}
.badge-gray{background:#F1EFE8;color:#5F5E5A}
.badge-green{background:#EAF3DE;color:#27500A}
.badge-red{background:#FDEDEC;color:#A32D2D}
.badge-ml{background:#E8F5E9;color:#27AE60;border:1px solid #A9DFBF}
.bar-row{display:flex;align-items:center;gap:8px;margin-bottom:8px}
.bar-label{font-size:11px;color:#555;width:100px;text-align:right;flex-shrink:0}
.bar-track{flex:1;background:#f0f0f0;border-radius:4px;height:8px}
.bar-fill{height:8px;border-radius:4px}
.bar-val{font-size:11px;color:#555;width:34px;text-align:right;flex-shrink:0}
.bar-count{font-size:10px;color:#aaa;width:34px;text-align:right;flex-shrink:0}
.hist-row{display:flex;align-items:center;gap:8px;margin-bottom:8px}
.hist-label{font-size:11px;color:#555;width:65px;flex-shrink:0}
.hist-track{flex:1;background:#f0f0f0;border-radius:4px;height:10px}
.hist-fill{height:10px;border-radius:4px}
.hist-pct{font-size:11px;color:#555;width:34px;text-align:right;flex-shrink:0}
.hist-count{font-size:10px;color:#aaa;width:34px;text-align:right;flex-shrink:0}
.risk-row{display:flex;align-items:center;padding:7px 0;border-bottom:0.5px solid #f5f5f5}
.risk-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
.risk-label{flex:1;font-size:12px;color:#444;margin-left:8px}
.risk-count{font-size:13px;font-weight:600;color:#1a1a2e}
.risk-pct{font-size:11px;color:#aaa;margin-left:6px}
.mora-row{display:flex;align-items:center;gap:8px;padding:6px 0;border-bottom:0.5px solid #f5f5f5}
.mora-muni{font-size:12px;color:#555;flex:1}
.mora-bar-bg{width:80px;background:#f0f0f0;border-radius:4px;height:6px;flex-shrink:0}
.mora-bar{height:6px;border-radius:4px;background:#E74C3C}
.mora-pct{font-size:11px;font-weight:600;color:#E74C3C;width:36px;text-align:right;flex-shrink:0}
</style>
@endsection

@section('content')

@php
  $maxMuni    = $porMunicipio->max('total') ?: 1;
  $maxMegas   = $porMegas->max('total') ?: 1;
  $maxAntig   = max($antig0_12, $antig13_24, $antig25_48, $antig49_72, $antig72mas) ?: 1;
  $maxMora    = $municipiosMora->max('mora_prom') ?: 1;
  $totalRiesgo = $riesgoAlto + $riesgoMedio + $riesgoBajo;
@endphp

{{-- KPIs --}}
<div class="kpi-row">
    <div class="kpi">
        <div class="kpi-label">Total clientes
            <svg viewBox="0 0 16 16" fill="none" stroke="#0099D6" stroke-width="1.5"><circle cx="8" cy="6" r="3"/><path d="M2 14c0-3.3 2.7-6 6-6s6 2.7 6 6"/></svg>
        </div>
        <div class="kpi-value">{{ $totalClientes }}</div>
        <div class="kpi-trend trend-neutral">Base de clientes SISTCO</div>
    </div>
    <div class="kpi">
        <div class="kpi-label">Antigüedad promedio
            <svg viewBox="0 0 16 16" fill="none" stroke="#0099D6" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><path d="M8 5v3l2 2"/></svg>
        </div>
        <div class="kpi-value" style="font-size:20px">{{ $antigüedadProm }} m.</div>
        <div class="kpi-trend trend-neutral">Promedio de permanencia</div>
    </div>
    <div class="kpi">
        <div class="kpi-label">Clientes al día
            <svg viewBox="0 0 16 16" fill="none" stroke="#27AE60" stroke-width="1.5"><path d="M3 8l3 3 7-7"/></svg>
        </div>
        <div class="kpi-value" style="color:#27AE60">{{ 100 - $tasaMorosidad }}%</div>
        <div class="kpi-trend trend-up">▲ {{ $totalClientes - $clientesMorosos }} clientes</div>
    </div>
    <div class="kpi">
        <div class="kpi-label">Ingreso promedio/cliente
            <svg viewBox="0 0 16 16" fill="none" stroke="#0099D6" stroke-width="1.5"><rect x="2" y="4" width="12" height="9" rx="1"/><path d="M2 7h12"/></svg>
        </div>
        <div class="kpi-value" style="font-size:18px">${{ number_format($ingresoPromedio * 1000, 0, ',', '.') }}</div>
        <div class="kpi-trend trend-neutral">Mensualidad promedio</div>
    </div>
</div>

{{-- Fila 1: Municipios + Megas --}}
<div class="row2">
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Clientes por municipio</div>
                <div class="card-sub">Distribución geográfica real</div>
            </div>
            <span class="badge badge-gray">{{ $porMunicipio->count() }} municipios</span>
        </div>
        @foreach($porMunicipio as $item)
        @php $pct = round(($item->total / $maxMuni) * 100); @endphp
        <div class="bar-row">
            <div class="bar-label">{{ $item->municipio }}</div>
            <div class="bar-track"><div class="bar-fill" style="width:{{ $pct }}%;background:#0099D6"></div></div>
            <div class="bar-val">{{ round(($item->total/$totalClientes)*100,1) }}%</div>
            <div class="bar-count">{{ $item->total }}</div>
        </div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Distribución por plan de megas</div>
                <div class="card-sub">Planes contratados por clientes</div>
            </div>
            <span class="badge badge-blue">{{ $porMegas->count() }} planes</span>
        </div>
        @foreach($porMegas as $item)
        @php $pct = round(($item->total / $maxMegas) * 100); @endphp
        <div class="bar-row">
            <div class="bar-label">{{ $item->megas }}</div>
            <div class="bar-track"><div class="bar-fill" style="width:{{ $pct }}%;background:#378ADD"></div></div>
            <div class="bar-val">{{ round(($item->total/$totalClientes)*100,1) }}%</div>
            <div class="bar-count">{{ $item->total }}</div>
        </div>
        @endforeach
    </div>
</div>

{{-- Fila 2: Riesgo ML + Mora por municipio + Antigüedad --}}
<div class="row3">

    {{-- Clasificación ML --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Clasificación de riesgo ML</div>
                <div class="card-sub">Regresión Logística · AUC-ROC: 0.9655</div>
            </div>
            <span class="badge badge-ml">✓ ML</span>
        </div>
        <div class="risk-row">
            <div class="risk-dot" style="background:#E74C3C"></div>
            <div class="risk-label">Riesgo Alto</div>
            <div class="risk-count">{{ $riesgoAlto }}</div>
            <div class="risk-pct">{{ $totalRiesgo > 0 ? round($riesgoAlto/$totalRiesgo*100,1) : 0 }}%</div>
        </div>
        <div class="risk-row">
            <div class="risk-dot" style="background:#F39C12"></div>
            <div class="risk-label">Riesgo Medio</div>
            <div class="risk-count">{{ $riesgoMedio }}</div>
            <div class="risk-pct">{{ $totalRiesgo > 0 ? round($riesgoMedio/$totalRiesgo*100,1) : 0 }}%</div>
        </div>
        <div class="risk-row" style="border:none">
            <div class="risk-dot" style="background:#27AE60"></div>
            <div class="risk-label">Riesgo Bajo</div>
            <div class="risk-count">{{ $riesgoBajo }}</div>
            <div class="risk-pct">{{ $totalRiesgo > 0 ? round($riesgoBajo/$totalRiesgo*100,1) : 0 }}%</div>
        </div>
        @if($totalRiesgo === 0)
        <div style="font-size:11px;color:#aaa;margin-top:8px;text-align:center">
            Ejecuta ml:calcular-predicciones para ver los datos
        </div>
        @endif
        <div style="margin-top:12px;padding:8px;background:#F8F8F8;border-radius:6px">
            <div style="font-size:10px;color:#888;margin-bottom:4px">Distribución de riesgo</div>
            <div style="display:flex;height:8px;border-radius:4px;overflow:hidden">
                @if($totalRiesgo > 0)
                <div style="width:{{ round($riesgoAlto/$totalRiesgo*100) }}%;background:#E74C3C"></div>
                <div style="width:{{ round($riesgoMedio/$totalRiesgo*100) }}%;background:#F39C12"></div>
                <div style="width:{{ round($riesgoBajo/$totalRiesgo*100) }}%;background:#27AE60"></div>
                @endif
            </div>
        </div>
    </div>

    {{-- Mora por municipio --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Mora histórica por municipio</div>
                <div class="card-sub">Tasa promedio de incumplimiento</div>
            </div>
            <span class="badge badge-red">Real</span>
        </div>
        @foreach($municipiosMora as $item)
        @php $pct = $maxMora > 0 ? round(($item->mora_prom / $maxMora) * 100) : 0; @endphp
        <div class="mora-row">
            <div class="mora-muni">{{ $item->municipio }}</div>
            <div class="mora-bar-bg">
                <div class="mora-bar" style="width:{{ $pct }}%"></div>
            </div>
            <div class="mora-pct">{{ $item->mora_prom }}%</div>
        </div>
        @endforeach
    </div>

    {{-- Histograma antigüedad --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Antigüedad de clientes</div>
                <div class="card-sub">Distribución por rango de meses</div>
            </div>
            <span class="badge badge-gray">5 rangos</span>
        </div>
        @php
            $rangos = [
                ['0 – 12 m.',  $antig0_12,  '#B5D4F4'],
                ['13 – 24 m.', $antig13_24, '#378ADD'],
                ['25 – 48 m.', $antig25_48, '#0099D6'],
                ['49 – 72 m.', $antig49_72, '#005F8A'],
                ['72+ m.',     $antig72mas, '#003A5C'],
            ];
        @endphp
        @foreach($rangos as [$label, $count, $color])
        @php $pct = $maxAntig > 0 ? round(($count / $maxAntig) * 100) : 0; @endphp
        <div class="hist-row">
            <div class="hist-label">{{ $label }}</div>
            <div class="hist-track">
                <div class="hist-fill" style="width:{{ $pct }}%;background:{{ $color }}"></div>
            </div>
            <div class="hist-pct">{{ $totalClientes > 0 ? round(($count/$totalClientes)*100,1) : 0 }}%</div>
            <div class="hist-count">{{ $count }}</div>
        </div>
        @endforeach
    </div>
</div>

{{-- Fila 3: Estado de pagos + Mora vs clientes al día --}}
<div class="row2">
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Estado de pagos actual</div>
                <div class="card-sub">Distribución de la cartera en tiempo real</div>
            </div>
            <span class="badge badge-gray">Hoy</span>
        </div>
        @php
            $alDia    = $totalClientes - $clientesMorosos;
            $estados  = [
                ['Al día',        $alDia,           '#27AE60', '#EAF3DE', '#27500A'],
                ['En mora',       $clientesMorosos,  '#E74C3C', '#FDEDEC', '#A32D2D'],
                ['Riesgo Alto ML',$riesgoAlto,       '#E74C3C', '#FDEDEC', '#A32D2D'],
                ['Riesgo Medio ML',$riesgoMedio,     '#F39C12', '#FAEEDA', '#633806'],
                ['Riesgo Bajo ML', $riesgoBajo,      '#27AE60', '#EAF3DE', '#27500A'],
            ];
        @endphp
        @foreach($estados as [$label, $count, $dotColor, $pillBg, $pillColor])
        <div style="display:flex;align-items:center;padding:7px 0;border-bottom:0.5px solid #f5f5f5">
            <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:{{ $dotColor }};margin-right:8px;flex-shrink:0"></span>
            <span style="flex:1;font-size:12px;color:#444">{{ $label }}</span>
            <span style="font-weight:600;color:#1a1a2e;font-size:13px;margin-right:8px">{{ $count }}</span>
            <span style="background:{{ $pillBg }};color:{{ $pillColor }};padding:2px 8px;border-radius:20px;font-size:10px;font-weight:600">
                {{ $totalClientes > 0 ? round(($count/$totalClientes)*100,1) : 0 }}%
            </span>
        </div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Resumen ejecutivo</div>
                <div class="card-sub">Indicadores clave de la cartera</div>
            </div>
        </div>
        @php
            $indicadores = [
                ['Total clientes activos',   $totalClientes,                                          '#0099D6'],
                ['Clientes al día',          $totalClientes - $clientesMorosos,                       '#27AE60'],
                ['Clientes en mora',         $clientesMorosos,                                        '#E74C3C'],
                ['Tasa de mora',             $tasaMorosidad . '%',                                    '#E74C3C'],
                ['Ingreso mensual estimado', '$' . number_format($ingresoPromedio*$totalClientes*1000,0,',','.'), '#0099D6'],
                ['Antigüedad promedio',      $antigüedadProm . ' meses',                              '#555'],
                ['Municipios activos',       $porMunicipio->count(),                                  '#555'],
                ['Planes de servicio',       $porMegas->count(),                                      '#555'],
            ];
        @endphp
        @foreach($indicadores as [$label, $valor, $color])
        <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:0.5px solid #f5f5f5">
            <span style="font-size:12px;color:#888">{{ $label }}</span>
            <span style="font-size:12px;font-weight:600;color:{{ $color }}">{{ $valor }}</span>
        </div>
        @endforeach
    </div>
</div>

{{-- Componente Livewire --}}
<div style="background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:16px">
    <div style="font-size:13px;font-weight:600;color:#1a1a2e;margin-bottom:14px;display:flex;align-items:center;gap:8px">
        Base de clientes — exploración en tiempo real
        <span style="background:#E6F5FC;color:#0099D6;font-size:10px;font-weight:600;padding:2px 8px;border-radius:20px">⚡ Livewire</span>
    </div>
    @livewire('tabla-clientes')
</div>

@endsection