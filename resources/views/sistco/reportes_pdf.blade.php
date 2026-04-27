<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: Arial, sans-serif; font-size: 11px; color: #1a1a2e; background: #fff; }
.header { background: #0099D6; color: #fff; padding: 20px 24px; margin-bottom: 20px; }
.header-top { display: flex; justify-content: space-between; align-items: center; }
.empresa { font-size: 20px; font-weight: 700; letter-spacing: 1px; }
.subtitulo { font-size: 11px; opacity: 0.85; margin-top: 2px; }
.fecha { font-size: 10px; opacity: 0.8; text-align: right; }
.titulo-reporte { font-size: 15px; font-weight: 700; margin-top: 8px; }
.kpi-grid { display: flex; gap: 12px; margin-bottom: 20px; }
.kpi { flex: 1; background: #F0F4F8; border-radius: 6px; padding: 12px; border-left: 4px solid #0099D6; }
.kpi-label { font-size: 9px; color: #888; margin-bottom: 4px; text-transform: uppercase; }
.kpi-value { font-size: 18px; font-weight: 700; color: #1a1a2e; }
.kpi-value.rojo { color: #E74C3C; }
.kpi-value.verde { color: #27AE60; }
.section { margin-bottom: 20px; }
.section-title { font-size: 12px; font-weight: 700; color: #1a1a2e; border-bottom: 2px solid #0099D6; padding-bottom: 4px; margin-bottom: 10px; }
table { width: 100%; border-collapse: collapse; font-size: 10px; }
th { background: #0099D6; color: #fff; padding: 7px 10px; text-align: left; font-weight: 600; }
td { padding: 6px 10px; border-bottom: 0.5px solid #E8E8E8; }
tr:nth-child(even) td { background: #F8F8F8; }
.riesgo-grid { display: flex; gap: 12px; }
.riesgo-box { flex: 1; padding: 12px; border-radius: 6px; text-align: center; }
.riesgo-box.alto { background: #FDEDEC; border: 1px solid #E74C3C; }
.riesgo-box.medio { background: #FEF9E7; border: 1px solid #F39C12; }
.riesgo-box.bajo { background: #E8F5E9; border: 1px solid #27AE60; }
.riesgo-num { font-size: 22px; font-weight: 700; }
.riesgo-num.alto { color: #E74C3C; }
.riesgo-num.medio { color: #F39C12; }
.riesgo-num.bajo { color: #27AE60; }
.riesgo-label { font-size: 9px; color: #555; margin-top: 2px; }
.ml-badge { background: #E8F5E9; color: #27AE60; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: 700; }
.footer { margin-top: 30px; border-top: 1px solid #E8E8E8; padding-top: 10px; display: flex; justify-content: space-between; font-size: 9px; color: #aaa; }
.pill { padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: 700; }
.pill-verde { background: #E8F5E9; color: #27AE60; }
.pill-rojo  { background: #FDEDEC; color: #E74C3C; }
.pill-naranja { background: #FEF9E7; color: #F39C12; }
</style>
</head>
<body>

{{-- Encabezado --}}
<div class="header">
    <div class="header-top">
        <div>
            <div class="empresa">SISTCO</div>
            <div class="subtitulo">Sistemas y Comunicaciones SAS · Santander, Colombia</div>
        </div>
        <div class="fecha">
            Generado: {{ now()->format('d/m/Y H:i') }}<br>
            Corte: {{ now()->format('F Y') }}
        </div>
    </div>
    <div class="titulo-reporte">Reporte Ejecutivo de Cartera — Sistema SISTCO-ML</div>
</div>

{{-- KPIs --}}
<div class="kpi-grid">
    <div class="kpi">
        <div class="kpi-label">Total clientes</div>
        <div class="kpi-value">{{ $totalClientes }}</div>
    </div>
    <div class="kpi">
        <div class="kpi-label">Clientes en mora</div>
        <div class="kpi-value rojo">{{ $clientesMorosos }}</div>
    </div>
    <div class="kpi">
        <div class="kpi-label">Tasa de mora</div>
        <div class="kpi-value rojo">{{ $tasaMorosidad }}%</div>
    </div>
    <div class="kpi">
        <div class="kpi-label">Clientes al día</div>
        <div class="kpi-value verde">{{ $totalClientes - $clientesMorosos }}</div>
    </div>
    <div class="kpi">
        <div class="kpi-label">Ingreso mensual estimado</div>
        <div class="kpi-value" style="font-size:13px">${{ number_format($ingresoTotal, 0, ',', '.') }}</div>
    </div>
</div>

{{-- Clasificación ML --}}
<div class="section">
    <div class="section-title">
        Clasificación de Riesgo ML &nbsp;
        <span class="ml-badge">✓ Regresión Logística · AUC-ROC: 0.9655</span>
    </div>
    <div class="riesgo-grid">
        <div class="riesgo-box alto">
            <div class="riesgo-num alto">{{ $riesgoAlto }}</div>
            <div class="riesgo-label">Riesgo Alto</div>
            <div style="font-size:9px;color:#E74C3C">{{ $totalClientes > 0 ? round($riesgoAlto/$totalClientes*100,1) : 0 }}%</div>
        </div>
        <div class="riesgo-box medio">
            <div class="riesgo-num medio">{{ $riesgoMedio }}</div>
            <div class="riesgo-label">Riesgo Medio</div>
            <div style="font-size:9px;color:#F39C12">{{ $totalClientes > 0 ? round($riesgoMedio/$totalClientes*100,1) : 0 }}%</div>
        </div>
        <div class="riesgo-box bajo">
            <div class="riesgo-num bajo">{{ $riesgoBajo }}</div>
            <div class="riesgo-label">Riesgo Bajo</div>
            <div style="font-size:9px;color:#27AE60">{{ $totalClientes > 0 ? round($riesgoBajo/$totalClientes*100,1) : 0 }}%</div>
        </div>
    </div>
</div>

{{-- Tabla por municipio --}}
<div class="section">
    <div class="section-title">Distribución por Municipio</div>
    <table>
        <thead>
            <tr>
                <th>Municipio</th>
                <th>Clientes</th>
                <th>% del total</th>
                <th>Ingreso mensual</th>
                <th>Mora promedio</th>
            </tr>
        </thead>
        <tbody>
            @foreach($porMunicipio as $item)
            <tr>
                <td style="font-weight:600">{{ $item->municipio }}</td>
                <td>{{ $item->total }}</td>
                <td>{{ round(($item->total / $totalClientes) * 100, 1) }}%</td>
                <td>${{ number_format($item->ingreso, 0, ',', '.') }}</td>
                <td>
                    @php
                        $moraMuni = \App\Models\Cliente::where('municipio', $item->municipio)
                            ->avg('tasa_mora_historica');
                    @endphp
                    {{ round($moraMuni * 100, 2) }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Top clientes riesgo alto --}}
<div class="section">
    <div class="section-title">Top 10 Clientes Mayor Riesgo de Incumplimiento</div>
    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Municipio</th>
                <th>Megas</th>
                <th>Mensualidad</th>
                <th>Moras hist.</th>
                <th>Probabilidad ML</th>
                <th>Nivel Riesgo</th>
            </tr>
        </thead>
        <tbody>
            @foreach(\App\Models\Cliente::whereNotNull('probabilidad_ml')->orderByDesc('probabilidad_ml')->take(10)->get() as $c)
            <tr>
                <td style="font-weight:600">#{{ $c->codigo_cliente }}</td>
                <td>{{ $c->municipio }}</td>
                <td>{{ $c->megas }}</td>
                <td>${{ number_format($c->mensualidad * 1000, 0, ',', '.') }}</td>
                <td>{{ $c->n_moras_historicas }}</td>
                <td style="font-weight:600;color:#E74C3C">{{ round($c->probabilidad_ml, 2) }}%</td>
                <td>
                    @if($c->nivel_riesgo_ml === 'Alto')
                        <span class="pill pill-rojo">Alto</span>
                    @elseif($c->nivel_riesgo_ml === 'Medio')
                        <span class="pill pill-naranja">Medio</span>
                    @else
                        <span class="pill pill-verde">Bajo</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Footer --}}
<div class="footer">
    <div>SISTCO Sistemas y Comunicaciones SAS · Sistema SISTCO-ML v2.0</div>
    <div>Reporte generado el {{ now()->format('d/m/Y \a \l\a\s H:i') }} · Confidencial</div>
</div>

</body>
</html>
