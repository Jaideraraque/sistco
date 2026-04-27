@extends('sistco.layout')
@section('title', 'Predicción de Morosidad')
@section('page-title', 'Predicción de Morosidad')
@section('styles')
<style>
.nav-item:hover{background:#f5f5f5;color:#0099D6}
.nav-item.active{background:#E6F5FC;color:#0099D6;font-weight:500;border-right:2px solid #0099D6}
.nav-item svg{width:14px;height:14px;flex-shrink:0}
.support-box p{font-size:11px;color:#0099D6;margin-bottom:8px;line-height:1.4}
.chip{background:#f5f5f5;border:0.5px solid #e8e8e8;border-radius:6px;padding:4px 10px;font-size:11px;color:#666;display:flex;align-items:center;gap:4px}
.chip-red{background:#FDEDEC;border-color:#f5c6c6;color:#A32D2D}
/* KPIs */
.kpi-row{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
.kpi{background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:14px 16px}
.kpi-label{font-size:11px;color:#888;margin-bottom:8px;display:flex;align-items:center;justify-content:space-between}
.kpi-label svg{width:14px;height:14px}
.kpi-value{font-size:22px;font-weight:700;color:#1a1a2e;margin-bottom:4px}
.kpi-trend{font-size:11px}
.trend-up{color:#27AE60}.trend-down{color:#E74C3C}.trend-neutral{color:#888}
/* Filtros */
.filter-bar{background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:12px 16px;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.filter-bar label{font-size:11px;color:#888}
.filter-bar input,.filter-bar select{border:0.5px solid #ddd;border-radius:6px;padding:5px 8px;font-size:11px;color:#444;background:#f9f9f9;outline:none}
.filter-btn{background:#0099D6;color:#fff;border:none;padding:6px 14px;border-radius:6px;font-size:11px;cursor:pointer;font-weight:500}
.filter-clear{background:#f5f5f5;color:#555;border:0.5px solid #ddd;padding:6px 14px;border-radius:6px;font-size:11px;cursor:pointer}
.export-btn{background:#fff;color:#0099D6;border:0.5px solid #0099D6;padding:6px 14px;border-radius:6px;font-size:11px;cursor:pointer;font-weight:500;margin-left:auto}
/* Layout principal */
.main-layout{display:grid;grid-template-columns:1fr 320px;gap:14px}
/* Tabla de clientes */
.card{background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:16px}
.card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.card-title{font-size:13px;font-weight:600;color:#1a1a2e}
.card-sub{font-size:11px;color:#aaa;margin-top:2px}
.badge{padding:3px 8px;border-radius:20px;font-size:10px;font-weight:600}
.badge-red{background:#FDEDEC;color:#A32D2D}
.badge-amber{background:#FAEEDA;color:#633806}
.badge-green{background:#EAF3DE;color:#27500A}
.badge-blue{background:#E6F1FB;color:#0C447C}
.badge-gray{background:#F1EFE8;color:#5F5E5A}
.client-table{width:100%;border-collapse:collapse;font-size:12px}
.client-table th{color:#aaa;font-weight:500;padding:6px 8px;text-align:left;border-bottom:0.5px solid #f0f0f0;font-size:11px;white-space:nowrap}
.client-table td{padding:8px 8px;border-bottom:0.5px solid #f5f5f5;vertical-align:middle;color:#444}
.client-table tr:hover td{background:#f9fbff}
.client-table tr.selected td{background:#E6F5FC}
.risk-pill{padding:3px 10px;border-radius:20px;font-size:10px;font-weight:700;white-space:nowrap}
.risk-alto{background:#FDEDEC;color:#A32D2D}
.risk-medio{background:#FAEEDA;color:#633806}
.risk-bajo{background:#EAF3DE;color:#27500A}
.prob-bar-wrap{display:flex;align-items:center;gap:6px}
.prob-track{width:60px;background:#f0f0f0;border-radius:4px;height:6px}
.prob-fill{height:6px;border-radius:4px}
.prob-val{font-size:11px;font-weight:600;color:#1a1a2e;width:28px}
.action-btn{padding:3px 8px;border-radius:6px;font-size:10px;cursor:pointer;border:0.5px solid #0099D6;background:#E6F5FC;color:#0099D6;white-space:nowrap}
.pagination{display:flex;align-items:center;justify-content:space-between;margin-top:12px;padding-top:12px;border-top:0.5px solid #f0f0f0}
.page-btn{padding:5px 10px;border-radius:6px;font-size:11px;cursor:pointer;border:0.5px solid #ddd;background:#fff;color:#555}
.page-btn.active{background:#0099D6;color:#fff;border-color:#0099D6}
/* Panel lateral detalle */
.detail-panel{display:flex;flex-direction:column;gap:12px}
.detail-card{background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:14px}
.detail-name{font-size:14px;font-weight:700;color:#1a1a2e;margin-bottom:2px}
.detail-meta{font-size:11px;color:#aaa;margin-bottom:10px}
.detail-row{display:flex;justify-content:space-between;padding:5px 0;border-bottom:0.5px solid #f5f5f5;font-size:12px}
.detail-row:last-child{border:none}
.detail-label{color:#888}
.detail-val{font-weight:600;color:#1a1a2e}
.risk-score{text-align:center;padding:12px 0;border-bottom:0.5px solid #f5f5f5;margin-bottom:10px}
.risk-score-val{font-size:36px;font-weight:700;color:#E74C3C}
.risk-score-label{font-size:11px;color:#aaa;margin-top:2px}
/* Factores influyentes */
.factor-row{margin-bottom:8px}
.factor-header{display:flex;justify-content:space-between;margin-bottom:3px}
.factor-name{font-size:11px;color:#555}
.factor-level{font-size:10px;font-weight:600}
.factor-high{color:#E74C3C}
.factor-med{color:#F39C12}
.factor-low{color:#27AE60}
.factor-track{background:#f0f0f0;border-radius:4px;height:6px}
.factor-fill{height:6px;border-radius:4px}
/* Historial pagos */
.hist-table{width:100%;border-collapse:collapse;font-size:11px}
.hist-table th{color:#aaa;font-weight:500;padding:4px 6px;text-align:left;border-bottom:0.5px solid #f0f0f0}
.hist-table td{padding:5px 6px;border-bottom:0.5px solid #f5f5f5;color:#555}
.action-row{display:flex;gap:8px;margin-top:10px}
.btn-primary{flex:1;padding:7px;background:#0099D6;border:none;border-radius:6px;color:#fff;font-size:11px;cursor:pointer;font-weight:500}
.btn-secondary{flex:1;padding:7px;background:#E6F5FC;border:0.5px solid #0099D6;border-radius:6px;color:#0099D6;font-size:11px;cursor:pointer;font-weight:500}
</style>
@endsection
@section('content')
<div class="content">

      <!-- KPIs -->
      <div class="kpi-row">
        <div class="kpi">
          <div class="kpi-label">Riesgo alto
            <svg viewBox="0 0 16 16" fill="none" stroke="#E74C3C" stroke-width="1.5"><path d="M8 2L14 13H2L8 2z"/><path d="M8 7v3M8 11.5v.5"/></svg>
          </div>
          <div class="kpi-value" style="color:#E74C3C">89</div>
          <div class="kpi-trend trend-down">7,1% de la cartera</div>
        </div>
        <div class="kpi">
          <div class="kpi-label">Riesgo medio
            <svg viewBox="0 0 16 16" fill="none" stroke="#F39C12" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><path d="M8 5v4M8 11v.5"/></svg>
          </div>
          <div class="kpi-value" style="color:#F39C12">145</div>
          <div class="kpi-trend trend-neutral">11,6% de la cartera</div>
        </div>
        <div class="kpi">
          <div class="kpi-label">Riesgo bajo
            <svg viewBox="0 0 16 16" fill="none" stroke="#27AE60" stroke-width="1.5"><path d="M3 8l3 3 7-7"/></svg>
          </div>
          <div class="kpi-value" style="color:#27AE60">1.013</div>
          <div class="kpi-trend trend-up">81,2% de la cartera</div>
        </div>
        <div class="kpi">
          <div class="kpi-label">Precisión del modelo
            <svg viewBox="0 0 16 16" fill="none" stroke="#0099D6" stroke-width="1.5"><path d="M2 8h3l2 4 4-8 2 4h3"/></svg>
          </div>
          <div class="kpi-value" style="font-size:20px;color:#0099D6">81%</div>
          <div class="kpi-trend trend-neutral">F1-Score: 0,79</div>
        </div>
      </div>

      <!-- FILTROS -->
      <div class="filter-bar">
        <label>Buscar:</label>
        <input type="text" placeholder="Nombre o ID cliente..." style="width:160px">
        <label>Riesgo:</label>
        <select style="width:100px">
          <option>Todos</option>
          <option>Alto</option>
          <option>Medio</option>
          <option>Bajo</option>
        </select>
        <label>Servicio:</label>
        <select style="width:110px">
          <option>Todos</option>
          <option>Básico 30M</option>
          <option>Estándar 50M</option>
          <option>Premium 100M</option>
        </select>
        <label>Municipio:</label>
        <select style="width:120px">
          <option>Todos</option>
          <option>Bucaramanga</option>
          <option>Floridablanca</option>
          <option>Girón</option>
          <option>Piedecuesta</option>
        </select>
        <button class="filter-btn">Filtrar</button>
        <button class="filter-clear">Limpiar</button>
        <button class="export-btn">📥 Exportar Excel</button>
      </div>

      <!-- LAYOUT PRINCIPAL -->
      <div class="main-layout">

        <!-- TABLA CLIENTES -->
        <div class="card">
          <div class="card-header">
            <div>
              <div class="card-title">Listado de clientes — ordenado por riesgo</div>
              <div class="card-sub">Haz clic en un cliente para ver su detalle y factores de riesgo</div>
            </div>
            <span class="badge badge-gray">1.247 clientes</span>
          </div>
          <table class="client-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Municipio</th>
                <th>Servicio</th>
                <th>Antigüedad</th>
                <th>Último pago</th>
                <th>Nivel riesgo</th>
                <th>Probabilidad</th>
                <th>Acción</th>
              </tr>
            </thead>
            <tbody>
              <tr class="selected">
                <td style="color:#aaa">CLI-0041</td>
                <td><strong>Carlos Martínez</strong></td>
                <td>Girón</td>
                <td>Básico 30M</td>
                <td>14 meses</td>
                <td style="color:#E74C3C">Hace 38 días</td>
                <td><span class="risk-pill risk-alto">ALTO</span></td>
                <td>
                  <div class="prob-bar-wrap">
                    <div class="prob-track"><div class="prob-fill" style="width:87%;background:#E74C3C"></div></div>
                    <div class="prob-val" style="color:#E74C3C">87%</div>
                  </div>
                </td>
                <td><button class="action-btn">Ver detalle</button></td>
              </tr>
              <tr>
                <td style="color:#aaa">CLI-0087</td>
                <td><strong>Ana Gómez</strong></td>
                <td>Lebrija</td>
                <td>Básico 30M</td>
                <td>8 meses</td>
                <td style="color:#E74C3C">Hace 32 días</td>
                <td><span class="risk-pill risk-alto">ALTO</span></td>
                <td>
                  <div class="prob-bar-wrap">
                    <div class="prob-track"><div class="prob-fill" style="width:83%;background:#E74C3C"></div></div>
                    <div class="prob-val" style="color:#E74C3C">83%</div>
                  </div>
                </td>
                <td><button class="action-btn">Ver detalle</button></td>
              </tr>
              <tr>
                <td style="color:#aaa">CLI-0153</td>
                <td><strong>Pedro Ramírez</strong></td>
                <td>Floridablanca</td>
                <td>Estándar 50M</td>
                <td>22 meses</td>
                <td style="color:#E74C3C">Hace 28 días</td>
                <td><span class="risk-pill risk-alto">ALTO</span></td>
                <td>
                  <div class="prob-bar-wrap">
                    <div class="prob-track"><div class="prob-fill" style="width:79%;background:#E74C3C"></div></div>
                    <div class="prob-val" style="color:#E74C3C">79%</div>
                  </div>
                </td>
                <td><button class="action-btn">Ver detalle</button></td>
              </tr>
              <tr>
                <td style="color:#aaa">CLI-0210</td>
                <td><strong>Luz Torres</strong></td>
                <td>Piedecuesta</td>
                <td>Básico 30M</td>
                <td>5 meses</td>
                <td style="color:#E74C3C">Hace 25 días</td>
                <td><span class="risk-pill risk-alto">ALTO</span></td>
                <td>
                  <div class="prob-bar-wrap">
                    <div class="prob-track"><div class="prob-fill" style="width:76%;background:#E74C3C"></div></div>
                    <div class="prob-val" style="color:#E74C3C">76%</div>
                  </div>
                </td>
                <td><button class="action-btn">Ver detalle</button></td>
              </tr>
              <tr>
                <td style="color:#aaa">CLI-0334</td>
                <td><strong>Jorge Vargas</strong></td>
                <td>Bucaramanga</td>
                <td>Estándar 50M</td>
                <td>31 meses</td>
                <td style="color:#F39C12">Hace 22 días</td>
                <td><span class="risk-pill risk-medio">MEDIO</span></td>
                <td>
                  <div class="prob-bar-wrap">
                    <div class="prob-track"><div class="prob-fill" style="width:68%;background:#F39C12"></div></div>
                    <div class="prob-val" style="color:#F39C12">68%</div>
                  </div>
                </td>
                <td><button class="action-btn">Ver detalle</button></td>
              </tr>
              <tr>
                <td style="color:#aaa">CLI-0412</td>
                <td><strong>María Suárez</strong></td>
                <td>Girón</td>
                <td>Premium 100M</td>
                <td>47 meses</td>
                <td style="color:#F39C12">Hace 19 días</td>
                <td><span class="risk-pill risk-medio">MEDIO</span></td>
                <td>
                  <div class="prob-bar-wrap">
                    <div class="prob-track"><div class="prob-fill" style="width:61%;background:#F39C12"></div></div>
                    <div class="prob-val" style="color:#F39C12">61%</div>
                  </div>
                </td>
                <td><button class="action-btn">Ver detalle</button></td>
              </tr>
              <tr>
                <td style="color:#aaa">CLI-0589</td>
                <td><strong>Luis Herrera</strong></td>
                <td>Bucaramanga</td>
                <td>Básico 30M</td>
                <td>11 meses</td>
                <td style="color:#F39C12">Hace 16 días</td>
                <td><span class="risk-pill risk-medio">MEDIO</span></td>
                <td>
                  <div class="prob-bar-wrap">
                    <div class="prob-track"><div class="prob-fill" style="width:54%;background:#F39C12"></div></div>
                    <div class="prob-val" style="color:#F39C12">54%</div>
                  </div>
                </td>
                <td><button class="action-btn">Ver detalle</button></td>
              </tr>
              <tr>
                <td style="color:#aaa">CLI-0721</td>
                <td><strong>Rosa Peña</strong></td>
                <td>Floridablanca</td>
                <td>Estándar 50M</td>
                <td>38 meses</td>
                <td style="color:#27AE60">Hace 4 días</td>
                <td><span class="risk-pill risk-bajo">BAJO</span></td>
                <td>
                  <div class="prob-bar-wrap">
                    <div class="prob-track"><div class="prob-fill" style="width:29%;background:#27AE60"></div></div>
                    <div class="prob-val" style="color:#27AE60">29%</div>
                  </div>
                </td>
                <td><button class="action-btn">Ver detalle</button></td>
              </tr>
            </tbody>
          </table>
          <div class="pagination">
            <span style="font-size:11px;color:#aaa">Mostrando 8 de 1.247 clientes</span>
            <div style="display:flex;gap:4px">
              <button class="page-btn">←</button>
              <button class="page-btn active">1</button>
              <button class="page-btn">2</button>
              <button class="page-btn">3</button>
              <span style="padding:5px 6px;font-size:11px;color:#aaa">...</span>
              <button class="page-btn">32</button>
              <button class="page-btn">→</button>
            </div>
          </div>
        </div>

        <!-- PANEL DETALLE -->
        <div class="detail-panel">

          <!-- Info cliente seleccionado -->
          <div class="detail-card">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
              <div style="width:38px;height:38px;border-radius:50%;background:#FDEDEC;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;color:#A32D2D;flex-shrink:0">CM</div>
              <div>
                <div class="detail-name">Carlos Martínez</div>
                <div class="detail-meta">CLI-0041 · Girón · Básico 30M</div>
              </div>
            </div>
            <div class="risk-score">
              <div class="risk-score-val">87%</div>
              <div class="risk-score-label">Probabilidad de morosidad</div>
              <div style="margin-top:6px"><span class="risk-pill risk-alto" style="font-size:11px;padding:4px 14px">RIESGO ALTO</span></div>
            </div>
            <div class="detail-row">
              <span class="detail-label">Antigüedad</span>
              <span class="detail-val">14 meses</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Último pago</span>
              <span class="detail-val" style="color:#E74C3C">Hace 38 días</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Días prom. retraso</span>
              <span class="detail-val" style="color:#E74C3C">22 días</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Valor mensualidad</span>
              <span class="detail-val">$45.000 COP</span>
            </div>
          </div>

          <!-- Factores influyentes -->
          <div class="detail-card">
            <div class="card-title" style="margin-bottom:12px">Factores de riesgo influyentes</div>
            <div class="factor-row">
              <div class="factor-header">
                <span class="factor-name">Días desde último pago</span>
                <span class="factor-level factor-high">Alta influencia</span>
              </div>
              <div class="factor-track"><div class="factor-fill" style="width:92%;background:#E74C3C"></div></div>
            </div>
            <div class="factor-row">
              <div class="factor-header">
                <span class="factor-name">Retraso histórico prom.</span>
                <span class="factor-level factor-high">Alta influencia</span>
              </div>
              <div class="factor-track"><div class="factor-fill" style="width:80%;background:#E74C3C"></div></div>
            </div>
            <div class="factor-row">
              <div class="factor-header">
                <span class="factor-name">Antigüedad del cliente</span>
                <span class="factor-level factor-med">Media influencia</span>
              </div>
              <div class="factor-track"><div class="factor-fill" style="width:55%;background:#F39C12"></div></div>
            </div>
            <div class="factor-row">
              <div class="factor-header">
                <span class="factor-name">Tipo de plan (Básico)</span>
                <span class="factor-level factor-med">Media influencia</span>
              </div>
              <div class="factor-track"><div class="factor-fill" style="width:42%;background:#F39C12"></div></div>
            </div>
            <div class="factor-row" style="margin-bottom:0">
              <div class="factor-header">
                <span class="factor-name">Municipio (Girón)</span>
                <span class="factor-level factor-low">Baja influencia</span>
              </div>
              <div class="factor-track"><div class="factor-fill" style="width:22%;background:#27AE60"></div></div>
            </div>
          </div>

          <!-- Historial de pagos -->
          <div class="detail-card">
            <div class="card-title" style="margin-bottom:10px">Historial de pagos reciente</div>
            <table class="hist-table">
              <thead>
                <tr>
                  <th>Mes</th>
                  <th>Fecha pago</th>
                  <th>Retraso</th>
                  <th>Estado</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Ene 2026</td>
                  <td>18/01/2026</td>
                  <td style="color:#F39C12">18 días</td>
                  <td><span class="risk-pill risk-medio" style="font-size:9px;padding:2px 6px">Tardío</span></td>
                </tr>
                <tr>
                  <td>Feb 2026</td>
                  <td>25/02/2026</td>
                  <td style="color:#E74C3C">25 días</td>
                  <td><span class="risk-pill risk-alto" style="font-size:9px;padding:2px 6px">Muy tardío</span></td>
                </tr>
                <tr style="border:none">
                  <td>Mar 2026</td>
                  <td style="color:#E74C3C">Sin pago</td>
                  <td style="color:#E74C3C">38 días</td>
                  <td><span class="risk-pill risk-alto" style="font-size:9px;padding:2px 6px">Vencido</span></td>
                </tr>
              </tbody>
            </table>
            <div class="action-row">
              <button class="btn-primary">Registrar gestión</button>
              <button class="btn-secondary">Enviar alerta</button>
            </div>
          </div>

        </div>
      </div>

    </div>
    {{-- Componente Livewire --}}
<div style="background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:16px;margin-top:16px">
    <div style="font-size:13px;font-weight:600;color:#1a1a2e;margin-bottom:14px;display:flex;align-items:center;gap:8px">
        Clientes en base de datos — filtro en tiempo real
        <span style="background:#E6F5FC;color:#0099D6;font-size:10px;font-weight:600;padding:2px 8px;border-radius:20px">⚡ Livewire</span>
    </div>
    @livewire('tabla-clientes')
</div>
@endsection
