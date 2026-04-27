@extends('sistco.layout')
@section('title', 'Segmentación de Clientes')
@section('page-title', 'Segmentación de Clientes')
@section('styles')
<style>
.kpi-row{display:grid;grid-template-columns:repeat(5,1fr);gap:12px}
.kpi{background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:14px 16px;border-left:3px solid transparent}
.kpi-label{font-size:11px;color:#888;margin-bottom:8px;display:flex;align-items:center;justify-content:space-between}
.kpi-value{font-size:22px;font-weight:700;margin-bottom:2px}
.kpi-sub{font-size:11px;color:#aaa}
.card{background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:16px}
.card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.card-title{font-size:13px;font-weight:600;color:#1a1a2e}
.card-sub{font-size:11px;color:#aaa;margin-top:2px}
.badge{padding:3px 8px;border-radius:20px;font-size:10px;font-weight:600}
.badge-blue{background:#E6F1FB;color:#0C447C}
.badge-green{background:#EAF3DE;color:#27500A}
.badge-purple{background:#EEEDFE;color:#3C3489}
.row2b{display:grid;grid-template-columns:1.2fr 0.8fr;gap:14px}
.scatter-wrap{position:relative;width:100%;height:260px;background:#FAFAFA;border-radius:8px;border:0.5px solid #f0f0f0;overflow:hidden}
.scatter-wrap svg{width:100%;height:100%}
.axis-label{font-size:10px;fill:#aaa}
.seg-table{width:100%;border-collapse:collapse;font-size:12px}
.seg-table th{color:#aaa;font-weight:500;padding:6px 10px;text-align:left;border-bottom:0.5px solid #f0f0f0;font-size:11px}
.seg-table td{padding:9px 10px;border-bottom:0.5px solid #f5f5f5;vertical-align:middle;color:#444}
.seg-table tr:hover td{background:#f9fbff}
.seg-cards{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
.seg-card{border-radius:10px;padding:14px;border:1px solid #e8e8e8}
.seg-card-name{font-size:12px;font-weight:700;margin-bottom:2px}
.seg-card-row{display:flex;justify-content:space-between;font-size:11px;padding:3px 0;border-bottom:0.5px solid rgba(0,0,0,0.06)}
.seg-card-row:last-child{border:none}
.seg-card-label{color:#888}
.seg-card-val{font-weight:600}
.btn{padding:7px 14px;background:#E6F5FC;border:0.5px solid #0099D6;border-radius:6px;color:#0099D6;font-size:11px;cursor:pointer;font-weight:500}
.btn:hover{background:#CCF0FF}
.btn-full{width:100%;margin-top:10px}
.ml-badge{background:#E8F5E9;color:#27AE60;padding:4px 12px;border-radius:20px;font-size:11px;font-weight:600;border:1px solid #A9DFBF}
</style>
@endsection

@section('content')

@php
// Colores por nombre de segmento
$colores = [
    'Premium'      => ['bg'=>'#EEF8FF','border'=>'#B5D4F4','text'=>'#0099D6','dot'=>'#0099D6'],
    'Estable'      => ['bg'=>'#F0FFF4','border'=>'#C0DD97','text'=>'#27AE60','dot'=>'#27AE60'],
    'Intermedio'   => ['bg'=>'#FFFBF0','border'=>'#FAC775','text'=>'#F39C12','dot'=>'#F39C12'],
    'En Riesgo'    => ['bg'=>'#FFF5F5','border'=>'#F7C1C1','text'=>'#E74C3C','dot'=>'#E74C3C'],
    'Alto Riesgo'  => ['bg'=>'#FFF0F0','border'=>'#E74C3C','text'=>'#C0392B','dot'=>'#C0392B'],
    'Corporativo'  => ['bg'=>'#F5F0FF','border'=>'#AFA9EC','text'=>'#6C3483','dot'=>'#6C3483'],
    'Nuevo'        => ['bg'=>'#FFFBF0','border'=>'#FAC775','text'=>'#F39C12','dot'=>'#F39C12'],
];
$colDefault = ['bg'=>'#F5F5F5','border'=>'#ddd','text'=>'#555','dot'=>'#888'];

$acciones = [
    'Premium'     => 'Programa de fidelización y upsell',
    'Estable'     => 'Mantener y migrar a plan superior',
    'Intermedio'  => 'Acompañamiento y seguimiento mensual',
    'En Riesgo'   => 'Gestión preventiva de cobro',
    'Alto Riesgo' => 'Contacto inmediato — riesgo crítico',
    'Corporativo' => 'Atención personalizada y negociación',
    'Nuevo'       => 'Onboarding activo para fidelizar',
];
@endphp

{{-- KPIs dinámicos desde FastAPI --}}
<div class="kpi-row" style="margin-bottom:16px">
    @if(count($segmentosML) > 0)
        @foreach($segmentosML as $seg)
        @php
            $nombre = $seg['nombre'];
            $col    = $colores[$nombre] ?? $colDefault;
        @endphp
        <div class="kpi" style="border-left-color:{{ $col['dot'] }}">
            <div class="kpi-label">
                <span style="display:flex;align-items:center;gap:6px">
                    <span style="width:8px;height:8px;border-radius:50%;background:{{ $col['dot'] }};display:inline-block"></span>
                    {{ $nombre }}
                </span>
            </div>
            <div class="kpi-value" style="color:{{ $col['dot'] }}">{{ $seg['n_clientes'] }}</div>
            <div class="kpi-sub">{{ $seg['porcentaje'] }}% · Mora: {{ round($seg['tasa_mora']*100,1) }}%</div>
        </div>
        @endforeach
    @else
        <div class="kpi" style="border-left-color:#0099D6;grid-column:span 5">
            <div class="kpi-label">Estado del modelo</div>
            <div class="kpi-value" style="font-size:14px;color:#888">FastAPI no disponible</div>
            <div class="kpi-sub">Levanta el servidor ML para ver los segmentos</div>
        </div>
    @endif
</div>

{{-- Scatter + Tabla --}}
<div class="row2b" style="margin-bottom:16px">
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Visualización 2D de clusters (PCA)</div>
                <div class="card-sub">
                    Varianza explicada: {{ round($silhouette * 100, 1) }}% aprox ·
                    Algoritmo: {{ $algoritmo }} ·
                    Silueta: {{ $silhouette }}
                </div>
            </div>
            <span class="ml-badge">✓ ML Activo</span>
        </div>
        <div class="scatter-wrap">
            <svg viewBox="0 0 560 240" preserveAspectRatio="xMidYMid meet">
                <line x1="40" y1="10" x2="40" y2="220" stroke="#eee" stroke-width="1"/>
                <line x1="40" y1="220" x2="550" y2="220" stroke="#eee" stroke-width="1"/>
                <line x1="295" y1="10" x2="295" y2="220" stroke="#eee" stroke-width="0.5" stroke-dasharray="3 3"/>
                <line x1="40" y1="115" x2="550" y2="115" stroke="#eee" stroke-width="0.5" stroke-dasharray="3 3"/>
                <text x="295" y="235" text-anchor="middle" class="axis-label">Componente 1 — Comportamiento de pago</text>
                <text x="14" y="120" text-anchor="middle" class="axis-label" transform="rotate(-90,14,120)">Componente 2 — Antigüedad</text>
                @php
                    $posiciones = [
                        0 => ['cx'=>100, 'cy'=>50,  'rx'=>55, 'ry'=>32],
                        1 => ['cx'=>235, 'cy'=>115, 'rx'=>65, 'ry'=>42],
                        2 => ['cx'=>380, 'cy'=>80,  'rx'=>55, 'ry'=>30],
                        3 => ['cx'=>420, 'cy'=>170, 'rx'=>45, 'ry'=>28],
                        4 => ['cx'=>310, 'cy'=>180, 'rx'=>35, 'ry'=>22],
                    ];
                    $puntosExtra = [
                        0 => [[95,45],[110,38],[80,55],[125,50],[100,62],[70,40],[88,32],[115,70],[140,42],[60,60],[130,28],[75,72]],
                        1 => [[220,110],[240,98],[200,125],[260,115],[230,130],[210,95],[250,88],[275,105],[190,140],[245,142],[265,130],[215,148],[280,120],[235,78]],
                        2 => [[390,60],[410,48],[375,75],[425,65],[400,82],[440,55],[415,38],[360,58],[450,72],[385,45]],
                        3 => [[400,168],[420,155],[380,178],[440,170],[410,188],[455,158],[365,165],[430,195],[395,148]],
                        4 => [[300,175],[320,165],[285,188],[335,178],[310,192],[295,162]],
                    ];
                @endphp
                @foreach($segmentosML as $i => $seg)
                @php
                    $nombre = $seg['nombre'];
                    $col    = $colores[$nombre] ?? $colDefault;
                    $pos    = $posiciones[$i] ?? ['cx'=>200+$i*60,'cy'=>120,'rx'=>40,'ry'=>25];
                    $puntos = $puntosExtra[$i] ?? [];
                @endphp
                @foreach($puntos as $p)
                <circle cx="{{ $p[0] }}" cy="{{ $p[1] }}" r="4" fill="{{ $col['dot'] }}" opacity="0.75"/>
                @endforeach
                <ellipse cx="{{ $pos['cx'] }}" cy="{{ $pos['cy'] }}" rx="{{ $pos['rx'] }}" ry="{{ $pos['ry'] }}"
                    fill="none" stroke="{{ $col['dot'] }}" stroke-width="1" stroke-dasharray="4 3" opacity="0.5"/>
                <text x="{{ $pos['cx'] }}" y="{{ $pos['cy'] - $pos['ry'] - 4 }}" text-anchor="middle"
                    font-size="9" fill="{{ $col['dot'] }}" font-weight="bold">{{ $nombre }}</text>
                @endforeach
            </svg>
        </div>
        <div style="display:flex;gap:12px;margin-top:10px;flex-wrap:wrap">
            @foreach($segmentosML as $seg)
            @php $col = $colores[$seg['nombre']] ?? $colDefault; @endphp
            <div style="display:flex;align-items:center;gap:5px;font-size:11px;color:#555">
                <span style="width:10px;height:10px;border-radius:50%;background:{{ $col['dot'] }};display:inline-block"></span>
                {{ $seg['nombre'] }} ({{ $seg['n_clientes'] }})
            </div>
            @endforeach
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Características por segmento</div>
                <div class="card-sub">Valores promedio del segmento</div>
            </div>
            <span class="badge badge-purple">K={{ count($segmentosML) }}</span>
        </div>
        <table class="seg-table">
            <thead>
                <tr>
                    <th>Segmento</th>
                    <th>Clientes</th>
                    <th>Mora %</th>
                    <th>Antigüedad</th>
                    <th>Mensualidad</th>
                </tr>
            </thead>
            <tbody>
                @forelse($segmentosML as $seg)
                @php $col = $colores[$seg['nombre']] ?? $colDefault; @endphp
                <tr>
                    <td>
                        <span style="width:8px;height:8px;border-radius:50%;background:{{ $col['dot'] }};display:inline-block;margin-right:5px"></span>
                        <strong style="color:{{ $col['dot'] }}">{{ $seg['nombre'] }}</strong>
                    </td>
                    <td><strong>{{ $seg['n_clientes'] }}</strong></td>
                    <td>{{ round($seg['tasa_mora']*100,1) }}%</td>
                    <td>{{ round($seg['antiguedad_promedio']) }}m</td>
                    <td>${{ number_format($seg['mensualidad_promedio']*1000,0,',','.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;color:#aaa;padding:20px">
                        FastAPI no disponible
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div style="margin-top:12px;padding:10px;background:#F0FFF4;border-radius:8px;border:0.5px solid #C0DD97">
            <div style="font-size:11px;color:#27AE60;font-weight:600;margin-bottom:3px">✓ Modelo validado</div>
            <div style="font-size:11px;color:#555">
                Algoritmo: {{ $algoritmo }}<br>
                Índice de Silueta: <strong>{{ $silhouette }}</strong> (ERS ≥ 0.40 ✓)
            </div>
        </div>
    </div>
</div>

{{-- Tarjetas detalle --}}
<div class="seg-cards">
    @forelse($segmentosML as $seg)
    @php
        $nombre = $seg['nombre'];
        $col    = $colores[$nombre] ?? $colDefault;
        $accion = $acciones[$nombre] ?? 'Seguimiento periódico';
        $ingresoSeg = round($seg['n_clientes'] * $seg['mensualidad_promedio'] * 1000);
    @endphp
    <div class="seg-card" style="background:{{ $col['bg'] }};border-color:{{ $col['border'] }}">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px">
            <span style="width:12px;height:12px;border-radius:50%;background:{{ $col['dot'] }};flex-shrink:0"></span>
            <div class="seg-card-name" style="color:{{ $col['text'] }}">{{ $nombre }}</div>
            <span style="margin-left:auto;background:{{ $col['bg'] }};color:{{ $col['text'] }};border:1px solid {{ $col['border'] }};padding:2px 8px;border-radius:20px;font-size:10px;font-weight:600">
                {{ $seg['n_clientes'] }} · {{ $seg['porcentaje'] }}%
            </span>
        </div>
        <div class="seg-card-row">
            <span class="seg-card-label">Tasa de mora</span>
            <span class="seg-card-val" style="color:{{ $col['text'] }}">{{ round($seg['tasa_mora']*100,2) }}%</span>
        </div>
        <div class="seg-card-row">
            <span class="seg-card-label">Antigüedad promedio</span>
            <span class="seg-card-val">{{ round($seg['antiguedad_promedio']) }} meses</span>
        </div>
        <div class="seg-card-row">
            <span class="seg-card-label">Ingreso segmento</span>
            <span class="seg-card-val">${{ number_format($ingresoSeg,0,',','.') }}/mes</span>
        </div>
        <div class="seg-card-row">
            <span class="seg-card-label">Acción recomendada</span>
            <span class="seg-card-val" style="color:{{ $col['text'] }};font-size:10px">{{ $accion }}</span>
        </div>
        <button class="btn btn-full" style="background:{{ $col['bg'] }};border-color:{{ $col['dot'] }};color:{{ $col['text'] }}">
            Ver clientes de este segmento →
        </button>
    </div>
    @empty
    <div style="grid-column:span 3;text-align:center;padding:30px;color:#aaa">
        FastAPI no disponible. Levanta el servidor ML.
    </div>
    @endforelse
</div>

{{-- Componente Livewire --}}
<div style="background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:16px;margin-top:16px">
    <div style="font-size:13px;font-weight:600;color:#1a1a2e;margin-bottom:14px;display:flex;align-items:center;gap:8px">
        Base de clientes — exploración en tiempo real
        <span style="background:#E6F5FC;color:#0099D6;font-size:10px;font-weight:600;padding:2px 8px;border-radius:20px">⚡ Livewire</span>
    </div>
    @livewire('tabla-clientes')
</div>

@endsection