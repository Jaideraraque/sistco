@extends('sistco.layout')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('styles')
<style>
.alert-bar{background:#FFF8E6;border:0.5px solid #F5C842;border-radius:8px;padding:8px 14px;display:flex;align-items:center;gap:8px;font-size:12px;color:#7a5c00;margin-bottom:16px}
.kpi-row{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px}
.kpi{background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:14px 16px}
.kpi-label{font-size:11px;color:#888;margin-bottom:8px;display:flex;align-items:center;justify-content:space-between}
.kpi-label svg{width:14px;height:14px}
.kpi-value{font-size:22px;font-weight:700;color:#1a1a2e;margin-bottom:4px}
.kpi-trend{font-size:11px}
.trend-up{color:#27AE60}.trend-down{color:#E74C3C}
.row2{display:grid;grid-template-columns:1.4fr 1fr;gap:14px;margin-bottom:16px}
.row3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px}
.card{background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:16px}
.card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.card-title{font-size:13px;font-weight:600;color:#1a1a2e}
.badge{padding:3px 8px;border-radius:20px;font-size:10px;font-weight:600}
.badge-red{background:#FDEDEC;color:#A32D2D}
.badge-blue{background:#E6F1FB;color:#0C447C}
.badge-gray{background:#F1EFE8;color:#5F5E5A}
.badge-green{background:#EAF3DE;color:#27500A}
.chart-mini{display:flex;align-items:flex-end;gap:3px;height:56px;padding-top:4px}
.chart-bar{flex:1;border-radius:3px 3px 0 0;min-width:8px;transition:opacity .2s}
.chart-bar:hover{opacity:.8}
.risk-row{display:flex;align-items:center;padding:6px 0;border-bottom:0.5px solid #f5f5f5}
.risk-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
.risk-label{flex:1;font-size:12px;color:#444;margin-left:8px}
.risk-count{font-size:13px;font-weight:600;color:#1a1a2e}
.risk-pct{font-size:11px;color:#aaa;margin-left:6px}
.seg-item{display:flex;align-items:center;gap:8px;padding:5px 0;border-bottom:0.5px solid #f5f5f5}
.seg-bar-bg{flex:1;background:#f0f0f0;border-radius:4px;height:6px}
.seg-bar{height:6px;border-radius:4px}
.proj-row{display:flex;align-items:center;padding:5px 0;border-bottom:0.5px solid #f5f5f5}
.proj-month{font-size:12px;color:#555;width:76px;white-space:nowrap}
.proj-bar-bg{flex:1;background:#f0f0f0;border-radius:4px;height:7px;margin:0 10px}
.proj-bar{height:7px;border-radius:4px;background:#0099D6}
.proj-val{font-size:11px;font-weight:600;color:#1a1a2e;width:90px;text-align:right}
.btn{margin-top:12px;width:100%;padding:7px;background:#E6F5FC;border:0.5px solid #0099D6;border-radius:6px;color:#0099D6;font-size:12px;cursor:pointer;font-weight:500}
.btn:hover{background:#CCF0FF}
.ml-badge{background:#E8F5E9;color:#27AE60;padding:3px 8px;border-radius:20px;font-size:9px;font-weight:600;border:1px solid #A9DFBF}
</style>
@endsection

@section('content')

@php
  $mesesEs = [
    'January'=>'Enero','February'=>'Febrero','March'=>'Marzo',
    'April'=>'Abril','May'=>'Mayo','June'=>'Junio',
    'July'=>'Julio','August'=>'Agosto','September'=>'Septiembre',
    'October'=>'Octubre','November'=>'Noviembre','December'=>'Diciembre',
  ];

  // Calcular máximo para escalar barras
  $maxProy = count($proyecciones) > 0
    ? max(array_column($proyecciones, 'ingreso_proyectado'))
    : 1;
@endphp

{{-- Alerta --}}
<div class="alert-bar">
  <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="#E67E22" stroke-width="1.5"><path d="M8 2L14 13H2L8 2z"/><path d="M8 7v3M8 11.5v.5"/></svg>
  {{ $clientesMorosos }} clientes en mora &nbsp;·&nbsp;
  {{ $riesgoAlto }} en riesgo alto (ML) &nbsp;·&nbsp;
  Última actualización: {{ now()->format('d/m/Y') }} &nbsp;·&nbsp;
  Total clientes: {{ $totalClientes }}
</div>

{{-- KPIs --}}
<div class="kpi-row">
  <div class="kpi">
    <div class="kpi-label">Clientes activos
      <svg viewBox="0 0 16 16" fill="none" stroke="#0099D6" stroke-width="1.5"><circle cx="8" cy="6" r="3"/><path d="M2 14c0-3.3 2.7-6 6-6s6 2.7 6 6"/></svg>
    </div>
    <div class="kpi-value">{{ $totalClientes }}</div>
    <div class="kpi-trend trend-up">▲ Base de clientes SISTCO</div>
  </div>
  <div class="kpi">
    <div class="kpi-label">Ingreso mensual estimado
      <svg viewBox="0 0 16 16" fill="none" stroke="#0099D6" stroke-width="1.5"><rect x="2" y="4" width="12" height="9" rx="1"/><path d="M2 7h12M6 7v6"/></svg>
    </div>
    <div class="kpi-value" style="font-size:17px">${{ number_format($ingresoPromedio * $totalClientes * 1000, 0, ',', '.') }}</div>
    <div class="kpi-trend trend-up">▲ Basado en mensualidades actuales</div>
  </div>
  <div class="kpi">
    <div class="kpi-label">Pagos al día
      <svg viewBox="0 0 16 16" fill="none" stroke="#27AE60" stroke-width="1.5"><path d="M3 8l3 3 7-7"/></svg>
    </div>
    <div class="kpi-value" style="color:#27AE60">{{ 100 - $tasaMorosidad }}%</div>
    <div class="kpi-trend trend-up">▲ {{ $totalClientes - $clientesMorosos }} clientes al día</div>
  </div>
  <div class="kpi">
    <div class="kpi-label">Tasa de mora
      <svg viewBox="0 0 16 16" fill="none" stroke="#E74C3C" stroke-width="1.5"><path d="M8 2L14 13H2L8 2z"/><path d="M8 7v3"/></svg>
    </div>
    <div class="kpi-value" style="color:#E74C3C">{{ $tasaMorosidad }}%</div>
    <div class="kpi-trend trend-down">{{ $clientesMorosos }} clientes en mora</div>
  </div>
</div>

{{-- Fila 2 --}}
<div class="row2">

  {{-- Gráfica proyección ingresos --}}
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Proyección de ingresos — próximos 6 meses</div>
        <div style="font-size:11px;color:#aaa;margin-top:2px">Modelo: Regresión Lineal Temporal · MAPE: 0.74%</div>
      </div>
      <span class="ml-badge">✓ ML Activo</span>
    </div>
    @if(count($proyecciones) > 0)
    <div class="chart-mini">
      @foreach($proyecciones as $i => $p)
      @php
        $pct   = $maxProy > 0 ? round(($p['ingreso_proyectado'] / $maxProy) * 100) : 50;
        $alpha = 0.4 + ($i / count($proyecciones)) * 0.6;
      @endphp
      <div class="chart-bar" style="height:{{ $pct }}%;background:#0099D6;opacity:{{ $alpha }}"
           title="{{ str_replace(array_keys($mesesEs), array_values($mesesEs), $p['mes_nombre']) }}: ${{ number_format($p['ingreso_proyectado'], 0, ',', '.') }}">
      </div>
      @endforeach
    </div>
    <div style="display:flex;justify-content:space-between;margin-top:6px">
      @foreach($proyecciones as $p)
      @php
        [$mesEn] = explode(' ', $p['mes_nombre']);
        $mesEs   = substr($mesesEs[$mesEn] ?? $mesEn, 0, 3);
      @endphp
      <span style="font-size:10px;color:#0099D6;font-weight:500">{{ $mesEs }}</span>
      @endforeach
    </div>
    <div style="margin-top:10px;padding-top:10px;border-top:0.5px solid #f0f0f0;display:flex;gap:16px;flex-wrap:wrap">
      @if(isset($proyecciones[0]))
      <span style="font-size:11px;color:#555">
        Próximo mes: <strong style="color:#0099D6">${{ number_format($proyecciones[0]['ingreso_proyectado'], 0, ',', '.') }}</strong>
      </span>
      @endif
      @if(isset($proyecciones[2]))
      <span style="font-size:11px;color:#27AE60">
        En 3 meses: ${{ number_format($proyecciones[2]['ingreso_proyectado'], 0, ',', '.') }}
      </span>
      @endif
    </div>
    @else
    <div style="padding:20px;text-align:center;color:#aaa;font-size:12px">
      FastAPI no disponible — inicia el servidor ML para ver proyecciones
    </div>
    @endif
    <a href="{{ route('ingresos') }}"><button class="btn">Ver proyección detallada →</button></a>
  </div>

  {{-- Clasificación de clientes con ML --}}
  <div class="card">
    <div class="card-header">
      <div class="card-title">Clasificación de Clientes</div>
      <span class="badge badge-red">{{ $riesgoAlto }} riesgo alto</span>
    </div>
    <div class="risk-row">
      <div class="risk-dot" style="background:#E74C3C"></div>
      <div class="risk-label">Riesgo Alto</div>
      <div class="risk-count">{{ $riesgoAlto }}</div>
      <div class="risk-pct">{{ $totalClientes > 0 ? round($riesgoAlto / $totalClientes * 100, 1) : 0 }}%</div>
    </div>
    <div class="risk-row">
      <div class="risk-dot" style="background:#F39C12"></div>
      <div class="risk-label">Riesgo Medio</div>
      <div class="risk-count">{{ $riesgoMedio }}</div>
      <div class="risk-pct">{{ $totalClientes > 0 ? round($riesgoMedio / $totalClientes * 100, 1) : 0 }}%</div>
    </div>
    <div class="risk-row" style="border:none">
      <div class="risk-dot" style="background:#27AE60"></div>
      <div class="risk-label">Riesgo Bajo</div>
      <div class="risk-count">{{ $riesgoBajo }}</div>
      <div class="risk-pct">{{ $totalClientes > 0 ? round($riesgoBajo / $totalClientes * 100, 1) : 0 }}%</div>
    </div>
    <div style="margin-top:10px;padding:8px 10px;background:#F8F8F8;border-radius:6px;font-size:10px;color:#888">
      Modelo: Regresión Logística · AUC-ROC: 0.9655
    </div>
    <a href="{{ route('clasificacion') }}"><button class="btn">Ver módulo completo →</button></a>
  </div>
</div>

{{-- Fila 3 --}}
<div class="row3">

  {{-- Top 3 proyecciones --}}
  <div class="card">
    <div class="card-header">
      <div class="card-title">Próximos 3 meses</div>
      <span class="badge badge-blue">ML</span>
    </div>
    @if(count($proyecciones) >= 3)
    @foreach(array_slice($proyecciones, 0, 3) as $i => $p)
    @php
      [$mesEn, $anio] = explode(' ', $p['mes_nombre']);
      $mesEs = $mesesEs[$mesEn] ?? $mesEn;
      $pct   = $maxProy > 0 ? round(($p['ingreso_proyectado'] / $maxProy) * 90) : 70;
    @endphp
    <div class="proj-row" style="{{ $i === 2 ? 'border:none' : '' }}">
      <div class="proj-month">{{ $mesEs }}</div>
      <div class="proj-bar-bg"><div class="proj-bar" style="width:{{ $pct }}%"></div></div>
      <div class="proj-val">${{ number_format($p['ingreso_proyectado']/1000000, 2) }}M</div>
    </div>
    @endforeach
    @else
    <div style="padding:20px;text-align:center;color:#aaa;font-size:12px">FastAPI no disponible</div>
    @endif
    <a href="{{ route('ingresos') }}"><button class="btn">Ver proyección detallada →</button></a>
  </div>

  {{-- Clientes por municipio --}}
  <div class="card">
    <div class="card-header">
      <div class="card-title">Clientes por municipio</div>
      <span class="badge badge-gray">Top 4</span>
    </div>
    @foreach($porMunicipio->take(4) as $item)
    <div class="seg-item">
      <div style="width:10px;height:10px;border-radius:50%;background:#0099D6;flex-shrink:0"></div>
      <div style="flex:1;font-size:12px;color:#444">{{ $item->municipio }}</div>
      <div class="seg-bar-bg">
        <div class="seg-bar" style="width:{{ ($item->total / $totalClientes) * 100 }}%;background:#0099D6"></div>
      </div>
      <div style="font-size:12px;font-weight:600;color:#1a1a2e;margin-left:8px;width:36px;text-align:right">{{ $item->total }}</div>
    </div>
    @endforeach
    <a href="{{ route('segmentacion') }}"><button class="btn">Ver segmentación completa →</button></a>
  </div>

  {{-- Resumen del sistema --}}
  <div class="card">
    <div class="card-header">
      <div class="card-title">Resumen del sistema</div>
      <span class="badge badge-gray">hoy</span>
    </div>
    <div class="risk-row">
      <div class="risk-dot" style="background:#0099D6"></div>
      <div class="risk-label">Total clientes</div>
      <div class="risk-count">{{ $totalClientes }}</div>
    </div>
    <div class="risk-row">
      <div class="risk-dot" style="background:#27AE60"></div>
      <div class="risk-label">Al día</div>
      <div class="risk-count">{{ $totalClientes - $clientesMorosos }}</div>
    </div>
    <div class="risk-row">
      <div class="risk-dot" style="background:#E74C3C"></div>
      <div class="risk-label">En mora</div>
      <div class="risk-count">{{ $clientesMorosos }}</div>
    </div>
    <div class="risk-row">
      <div class="risk-dot" style="background:#E74C3C"></div>
      <div class="risk-label">Riesgo Alto ML</div>
      <div class="risk-count">{{ $riesgoAlto }}</div>
    </div>
    <div class="risk-row" style="border:none">
      <div class="risk-dot" style="background:#F39C12"></div>
      <div class="risk-label">Tasa mora</div>
      <div class="risk-count">{{ $tasaMorosidad }}%</div>
    </div>
    <a href="{{ route('analisis') }}"><button class="btn">Ver análisis completo →</button></a>
  </div>
</div>

@endsection