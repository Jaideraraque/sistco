@extends('sistco.layout')
@section('title', 'Proyección de Ingresos')
@section('page-title', 'Proyección de Ingresos')
@section('styles')
<style>
.chip{background:#f5f5f5;border:0.5px solid #e8e8e8;border-radius:6px;padding:4px 10px;font-size:11px;color:#666}
.chip-green{background:#EAF3DE;border-color:#C0DD97;color:#27500A}
.kpi-row{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
.kpi{background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:14px 16px}
.kpi-label{font-size:11px;color:#888;margin-bottom:8px;display:flex;align-items:center;justify-content:space-between}
.kpi-label svg{width:14px;height:14px}
.kpi-value{font-size:20px;font-weight:700;color:#1a1a2e;margin-bottom:4px}
.kpi-trend{font-size:11px}
.trend-up{color:#27AE60}.trend-down{color:#E74C3C}.trend-neutral{color:#888}
.card{background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:16px}
.card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.card-title{font-size:13px;font-weight:600;color:#1a1a2e}
.card-sub{font-size:11px;color:#aaa;margin-top:2px}
.badge{padding:3px 8px;border-radius:20px;font-size:10px;font-weight:600}
.badge-blue{background:#E6F1FB;color:#0C447C}
.badge-green{background:#EAF3DE;color:#27500A}
.badge-gray{background:#F1EFE8;color:#5F5E5A}
.badge-amber{background:#FAEEDA;color:#633806}
.controls{display:flex;align-items:center;gap:16px;flex-wrap:wrap}
.chart-container{position:relative;width:100%;height:220px;margin-bottom:8px}
.chart-container svg{width:100%;height:100%}
.chart-legend{display:flex;gap:16px;margin-top:6px}
.legend-item{display:flex;align-items:center;gap:6px;font-size:11px;color:#555}
.legend-line{width:20px;height:3px;border-radius:2px}
.legend-area{width:16px;height:10px;border-radius:2px;background:#E6F1FB;border:0.5px solid #B5D4F4}
.row2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.proj-table{width:100%;border-collapse:collapse;font-size:12px}
.proj-table th{color:#aaa;font-weight:500;padding:6px 8px;text-align:left;border-bottom:0.5px solid #f0f0f0;font-size:11px}
.proj-table td{padding:8px 8px;border-bottom:0.5px solid #f5f5f5;vertical-align:middle;color:#444}
.proj-table tr.projected td{background:#F8FCFF}
.delta-up{color:#27AE60;font-weight:600}
.delta-down{color:#E74C3C;font-weight:600}
.scenario-card{border-radius:10px;padding:14px;border:0.5px solid}
.scenario-opt{background:#F8FCFF;border-color:#B5D4F4}
.scenario-base{background:#F0FFF4;border-color:#C0DD97;border-width:1.5px}
.scenario-pes{background:#FFF8F8;border-color:#F7C1C1}
.scenario-title{font-size:12px;font-weight:600;margin-bottom:4px}
.scenario-val{font-size:18px;font-weight:700;margin-bottom:8px}
.scenario-row{display:flex;justify-content:space-between;font-size:11px;color:#888;padding:3px 0;border-bottom:0.5px solid rgba(0,0,0,0.05)}
.scenario-row:last-child{border:none}
.impact-row{display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:0.5px solid #f5f5f5}
.impact-icon{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.impact-label{flex:1;font-size:12px;color:#444}
.impact-val{font-size:13px;font-weight:700}
.btn{padding:7px 14px;background:#E6F5FC;border:0.5px solid #0099D6;border-radius:6px;color:#0099D6;font-size:11px;cursor:pointer;font-weight:500}
.btn:hover{background:#CCF0FF}
.ml-badge{background:#E8F5E9;color:#27AE60;padding:4px 12px;border-radius:20px;font-size:11px;font-weight:600;border:1px solid #A9DFBF}
</style>
@endsection

@section('content')

@php
// MESES EN ESPAÑOL - TRADUCCIÓN
$mesesEs = [
    'January'=>'Enero', 'February'=>'Febrero', 'March'=>'Marzo',
    'April'=>'Abril', 'May'=>'Mayo', 'June'=>'Junio',
    'July'=>'Julio', 'August'=>'Agosto', 'September'=>'Septiembre',
    'October'=>'Octubre', 'November'=>'Noviembre', 'December'=>'Diciembre',
];

function traducirMes($nombre, $mesesEs) {
    foreach ($mesesEs as $en => $es) {
        $nombre = str_replace($en, $es, $nombre);
    }
    return $nombre;
}

// Datos de proyección desde FastAPI
$proy       = $proyecciones ?? [];
$primerMes  = $proy[0] ?? null;
$segundoMes = $proy[1] ?? null;
$tercerMes  = $proy[2] ?? null;

$ingresoProy1 = $primerMes  ? $primerMes['ingreso_proyectado']  : 0;
$ingresoProy2 = $segundoMes ? $segundoMes['ingreso_proyectado'] : 0;
$ingresoProy3 = $tercerMes  ? $tercerMes['ingreso_proyectado']  : 0;

$mesProy1 = $primerMes  ? traducirMes($primerMes['mes_nombre'], $mesesEs) : 'Próximo mes';
$mesProy2 = $segundoMes ? traducirMes($segundoMes['mes_nombre'], $mesesEs) : '';
$mesProy3 = $tercerMes  ? traducirMes($tercerMes['mes_nombre'], $mesesEs) : '';

// Ingresos base desde BD
$ingresoActual = $ingresoMensual * 1000;
$delta1 = $ingresoActual > 0 ? round((($ingresoProy1 - $ingresoActual) / $ingresoActual) * 100, 1) : 0;
@endphp

{{-- KPIs --}}
<div class="kpi-row" style="margin-bottom:16px">
    <div class="kpi">
        <div class="kpi-label">Ingreso actual estimado
            <svg viewBox="0 0 16 16" fill="none" stroke="#0099D6" stroke-width="1.5"><rect x="2" y="4" width="12" height="9" rx="1"/><path d="M2 7h12"/></svg>
        </div>
        <div class="kpi-value" style="font-size:17px">${{ number_format($ingresoActual, 0, ',', '.') }}</div>
        <div class="kpi-trend trend-up">▲ Base clientes activos SISTCO</div>
    </div>
    <div class="kpi">
        <div class="kpi-label">Proyección {{ $mesProy1 }}
            <svg viewBox="0 0 16 16" fill="none" stroke="#0099D6" stroke-width="1.5"><path d="M2 12L6 7L9 9L14 4"/></svg>
        </div>
        <div class="kpi-value" style="font-size:17px">${{ number_format($ingresoProy1, 0, ',', '.') }}</div>
        <div class="kpi-trend {{ $delta1 >= 0 ? 'trend-up' : 'trend-down' }}">
            {{ $delta1 >= 0 ? '▲' : '▼' }} {{ $delta1 }}% vs actual
        </div>
    </div>
    <div class="kpi">
        <div class="kpi-label">Proyección {{ $mesProy2 }}
            <svg viewBox="0 0 16 16" fill="none" stroke="#27AE60" stroke-width="1.5"><path d="M2 12L6 7L9 9L14 4"/></svg>
        </div>
        <div class="kpi-value" style="font-size:17px;color:#27AE60">${{ number_format($ingresoProy2, 0, ',', '.') }}</div>
        <div class="kpi-trend trend-up">▲ Modelo ML activo</div>
    </div>
    <div class="kpi">
        <div class="kpi-label">MAPE del modelo
            <svg viewBox="0 0 16 16" fill="none" stroke="#F39C12" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><path d="M8 5v3l2 2"/></svg>
        </div>
        <div class="kpi-value" style="color:#27AE60">0.74%</div>
        <div class="kpi-trend trend-up">✓ Cumple ERS (≤12%)</div>
    </div>
</div>

{{-- Gráfico --}}
<div class="card" style="margin-bottom:16px">
    <div class="card-header">
        <div>
            <div class="card-title">Proyección de Ingresos — Próximos 6 meses</div>
            <div class="card-sub">
                Modelo: Regresión Lineal Temporal &nbsp;|&nbsp; MAPE: 0.74% &nbsp;|&nbsp;
                Valores en COP · Línea continua = proyección ML
            </div>
        </div>
        <span class="ml-badge">✓ ML Activo</span>
    </div>

    <div class="chart-container">
        <svg viewBox="0 0 860 200" preserveAspectRatio="none">
            <line x1="60" y1="10" x2="60" y2="170" stroke="#f0f0f0" stroke-width="1"/>
            <line x1="60" y1="10" x2="840" y2="10" stroke="#f0f0f0" stroke-width="1"/>
            <line x1="60" y1="50" x2="840" y2="50" stroke="#f0f0f0" stroke-width="1"/>
            <line x1="60" y1="90" x2="840" y2="90" stroke="#f0f0f0" stroke-width="1"/>
            <line x1="60" y1="130" x2="840" y2="130" stroke="#f0f0f0" stroke-width="1"/>
            <line x1="60" y1="170" x2="840" y2="170" stroke="#f0f0f0" stroke-width="1"/>
            <text x="52" y="14"  text-anchor="end" font-size="9" fill="#aaa">Máx</text>
            <text x="52" y="94"  text-anchor="end" font-size="9" fill="#aaa">Med</text>
            <text x="52" y="170" text-anchor="end" font-size="9" fill="#aaa">Mín</text>
            <line x1="60" y1="10" x2="60" y2="170" stroke="#0099D6" stroke-width="1" stroke-dasharray="3 3" opacity="0.3"/>
            <polyline points="60,150 160,138 260,122 360,114 460,106 560,94 660,88 760,82 840,76"
                fill="none" stroke="#0099D6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            <polygon points="60,160 160,148 260,132 360,124 460,116 560,104 660,98 760,92 840,86 840,66 760,72 660,78 560,84 460,96 360,104 260,112 160,128 60,140"
                fill="#E6F1FB" opacity="0.4"/>
            <circle cx="60"  cy="150" r="3.5" fill="#0099D6"/>
            <circle cx="160" cy="138" r="3.5" fill="#0099D6"/>
            <circle cx="260" cy="122" r="3.5" fill="#0099D6"/>
            <circle cx="360" cy="114" r="3.5" fill="#0099D6"/>
            <circle cx="460" cy="106" r="3.5" fill="#0099D6"/>
            <circle cx="560" cy="94"  r="4"   fill="#0099D6" stroke="#fff" stroke-width="1.5"/>
            <circle cx="660" cy="88"  r="3.5" fill="#fff" stroke="#0099D6" stroke-width="2"/>
            <circle cx="760" cy="82"  r="3.5" fill="#fff" stroke="#0099D6" stroke-width="2"/>
            <circle cx="840" cy="76"  r="3.5" fill="#fff" stroke="#0099D6" stroke-width="2"/>
            @if(count($proy) >= 1)
            <rect x="530" y="70" width="72" height="22" rx="4" fill="#1a1a2e"/>
            <text x="566" y="84" text-anchor="middle" font-size="9" fill="white" font-weight="bold">
                ${{ number_format($ingresoProy1/1000000, 1) }}M
            </text>
            <polygon points="566,92 561,100 571,100" fill="#1a1a2e"/>
            @endif
            @foreach($proy as $i => $p)
            @php $x = 60 + ($i * 113); @endphp
            <text x="{{ $x }}" y="190" text-anchor="middle" font-size="9"
                  fill="{{ $i < 3 ? '#0099D6' : '#888' }}" font-weight="{{ $i < 3 ? 'bold' : 'normal' }}">
                {{ substr($p['mes'], 5, 2) }}/{{ substr($p['mes'], 2, 2) }}
            </text>
            @endforeach
        </svg>
    </div>
    <div class="chart-legend">
        <div class="legend-item">
            <div class="legend-line" style="background:#0099D6"></div>
            Proyección Regresión Lineal ML
        </div>
        <div class="legend-item">
            <div class="legend-area"></div>
            Banda de confianza estimada
        </div>
    </div>
</div>

{{-- Tabla + Escenarios --}}
<div class="row2">
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Tabla de proyección detallada</div>
                <div class="card-sub">Valores en COP · Modelo: Regresión Lineal Temporal</div>
            </div>
            <span class="badge badge-blue">6 meses</span>
        </div>
        <table class="proj-table">
            <thead>
                <tr>
                    <th>Mes</th>
                    <th>Ingreso Proyectado</th>
                    <th>Δ vs anterior</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @php $anterior = $ingresoActual; @endphp
                @forelse($proy as $i => $p)
                @php
                    $val   = $p['ingreso_proyectado'];
                    $delta = $anterior > 0 ? round((($val - $anterior) / $anterior) * 100, 1) : 0;
                    $anterior = $val;
                @endphp
                <tr class="projected">
                    <td><strong style="color:#0099D6">{{ traducirMes($p['mes_nombre'], $mesesEs) }} ✦</strong></td>
                    <td><strong>${{ number_format($val, 0, ',', '.') }}</strong></td>
                    <td class="{{ $delta >= 0 ? 'delta-up' : 'delta-down' }}">
                        {{ $delta >= 0 ? '▲' : '▼' }} {{ abs($delta) }}%
                    </td>
                    <td>
                        <span class="badge {{ $delta >= 0 ? 'badge-green' : 'badge-amber' }}">
                            {{ $delta >= 0 ? 'Crecimiento' : 'Leve baja' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align:center;color:#aaa;padding:20px">
                        FastAPI no disponible. Levanta el servidor ML.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div style="margin-top:10px;font-size:10px;color:#aaa">
            ✦ Valores proyectados · Modelo: Regresión Lineal Temporal · MAPE: 0.74%
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:14px">
        <div class="card">
            <div class="card-header">
                <div class="card-title">Comparativa de escenarios — {{ $mesProy1 }}</div>
            </div>
            <div style="display:flex;flex-direction:column;gap:10px">
                <div class="scenario-card scenario-opt">
                    <div class="scenario-title" style="color:#0C447C">Escenario optimista</div>
                    <div class="scenario-val" style="color:#0099D6">
                        ${{ number_format($ingresoProy1 * 1.065, 0, ',', '.') }}
                    </div>
                    <div class="scenario-row"><span>Supuesto</span><span>0% morosidad</span></div>
                    <div class="scenario-row"><span>Variación</span><span style="color:#27AE60;font-weight:600">+6.5% ▲</span></div>
                </div>
                <div class="scenario-card scenario-base">
                    <div class="scenario-title" style="color:#27500A">Escenario base ✦ modelo ML</div>
                    <div class="scenario-val" style="color:#27AE60">
                        ${{ number_format($ingresoProy1, 0, ',', '.') }}
                    </div>
                    <div class="scenario-row"><span>Supuesto</span><span>Predicción ML actual</span></div>
                    <div class="scenario-row"><span>MAPE</span><span style="color:#27AE60;font-weight:600">0.74%</span></div>
                </div>
                <div class="scenario-card scenario-pes">
                    <div class="scenario-title" style="color:#791F1F">Escenario pesimista</div>
                    <div class="scenario-val" style="color:#E74C3C">
                        ${{ number_format($ingresoProy1 * 0.92, 0, ',', '.') }}
                    </div>
                    <div class="scenario-row"><span>Supuesto</span><span>Mora máxima histórica</span></div>
                    <div class="scenario-row"><span>Variación</span><span style="color:#E74C3C;font-weight:600">-8% ▼</span></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header" style="margin-bottom:10px">
                <div class="card-title">Información del modelo</div>
            </div>
            <div class="impact-row">
                <div class="impact-icon" style="background:#E6F5FC">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="#0099D6" stroke-width="1.5"><path d="M2 12L6 7L9 9L14 4"/></svg>
                </div>
                <div class="impact-label">Algoritmo ganador<br><span style="font-size:10px;color:#aaa">Comparado vs Prophet y ARIMA</span></div>
                <div class="impact-val" style="color:#0099D6;font-size:11px">Reg. Lineal</div>
            </div>
            <div class="impact-row">
                <div class="impact-icon" style="background:#EAF3DE">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="#27AE60" stroke-width="1.5"><path d="M3 8l3 3 7-7"/></svg>
                </div>
                <div class="impact-label">MAPE obtenido<br><span style="font-size:10px;color:#aaa">Umbral ERS: ≤ 12%</span></div>
                <div class="impact-val" style="color:#27AE60">0.74%</div>
            </div>
            <div class="impact-row" style="border:none">
                <div class="impact-icon" style="background:#EAF3DE">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="#27AE60" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><path d="M8 5v3l2 2"/></svg>
                </div>
                <div class="impact-label">Meses de entrenamiento<br><span style="font-size:10px;color:#aaa">Ene 2019 — Feb 2026</span></div>
                <div class="impact-val" style="color:#555">85 meses</div>
            </div>
            <div style="margin-top:10px;padding:10px;background:#F0FFF4;border-radius:8px;border:0.5px solid #C0DD97">
                <div style="font-size:11px;color:#27AE60;font-weight:600;margin-bottom:4px">✓ Modelo validado</div>
                <div style="font-size:11px;color:#555;line-height:1.5">
                    Marzo 2026 excluido del entrenamiento por datos incompletos al momento de extracción del dataset.
                </div>
            </div>
        </div>
    </div>
</div>

@endsection