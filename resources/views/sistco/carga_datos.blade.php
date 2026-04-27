@extends('sistco.layout')
@section('title', 'Cargar Datos')
@section('page-title', 'Cargar Datos')
@section('styles')
<style>
.card{background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:16px}
.card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.card-title{font-size:13px;font-weight:600;color:#1a1a2e;display:flex;align-items:center;gap:6px}
.badge{padding:3px 8px;border-radius:20px;font-size:10px;font-weight:600}
.badge-blue{background:#E6F1FB;color:#185FA5}
.badge-gray{background:#F1EFE8;color:#5F5E5A}
.btn-primary{background:#0099D6;color:#fff;border:none;border-radius:7px;padding:8px 16px;font-size:12px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;justify-content:center}
.btn-primary:hover{background:#007DB8}
.btn-primary:disabled{background:#aaa;cursor:not-allowed}
.btn-secondary{background:#fff;color:#555;border:0.5px solid #ddd;border-radius:7px;padding:8px 14px;font-size:12px;cursor:pointer;display:flex;align-items:center;gap:6px}
.btn-secondary:hover{background:#f5f5f5}
.drop-zone{border:2px dashed #B5D4F4;border-radius:12px;background:#F5FBFF;padding:32px 24px;text-align:center;cursor:pointer;transition:all .2s}
.drop-zone:hover,.drop-zone.drag-over{border-color:#0099D6;background:#E6F5FC}
.drop-formats{display:flex;justify-content:center;gap:8px;margin-top:12px}
.fmt-chip{background:#fff;border:0.5px solid #e0e0e0;border-radius:6px;padding:3px 10px;font-size:10px;color:#666;font-weight:500}
.file-selected{display:flex;align-items:center;gap:14px;padding:14px 16px;background:#F0FFF4;border:0.5px solid #C0DD97;border-radius:10px}
.file-icon{width:40px;height:40px;border-radius:8px;background:#27AE60;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.file-meta{flex:1}
.file-name{font-size:13px;font-weight:600;color:#1a1a2e;margin-bottom:2px}
.file-info{font-size:11px;color:#555}
.progress-wrap{background:#f0f0f0;border-radius:10px;height:10px;overflow:hidden}
.progress-bar{height:10px;border-radius:10px;background:linear-gradient(90deg,#0099D6,#00C2FF);transition:width .6s ease}
.hist-table{width:100%;border-collapse:collapse;font-size:12px}
.hist-table th{font-size:10px;font-weight:500;color:#aaa;letter-spacing:0.5px;padding:6px 10px;text-align:left;border-bottom:0.5px solid #f0f0f0}
.hist-table td{padding:8px 10px;border-bottom:0.5px solid #f5f5f5;color:#444;vertical-align:middle}
.hist-table tr:last-child td{border-bottom:none}
.hist-table tr:hover td{background:#fafafa}
.status-pill{padding:3px 9px;border-radius:20px;font-size:10px;font-weight:600;display:inline-block}
.pill-ok{background:#EAF3DE;color:#3B6D11}
.pill-warn{background:#FAEEDA;color:#854F0B}
.pill-err{background:#FCEBEB;color:#A32D2D}
.pill-pend{background:#E6F1FB;color:#185FA5}
.steps{display:flex;align-items:center;margin-bottom:4px}
.step{display:flex;align-items:center;gap:6px;flex:1}
.step-circle{width:24px;height:24px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0}
.step-circle.done{background:#0099D6;color:#fff}
.step-circle.active{background:#E0F4FC;color:#0099D6;border:2px solid #0099D6}
.step-circle.pending{background:#f0f0f0;color:#bbb;border:1.5px solid #e0e0e0}
.step-label{font-size:11px;font-weight:500}
.step-label.done,.step-label.active{color:#0099D6}
.step-label.pending{color:#bbb}
.step-line{flex:1;height:1.5px;background:#e0e0e0;margin:0 4px}
.step-line.done{background:#0099D6}
</style>
@endsection

@section('content')

{{-- Alertas --}}
<div id="alertaExito" style="display:none;background:#E8F5E9;border:1px solid #A9DFBF;color:#1E8449;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px"></div>
<div id="alertaError" style="display:none;background:#FDEDEC;border:1px solid #F5C6CB;color:#A32D2D;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px"></div>

@if(session('success'))
<div style="background:#E8F5E9;border:1px solid #A9DFBF;color:#1E8449;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px">
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div style="background:#FDEDEC;border:1px solid #F5C6CB;color:#A32D2D;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px">
    {{ session('error') }}
</div>
@endif

{{-- Pasos --}}
<div class="card" style="padding:14px 18px;margin-bottom:16px">
    <div style="margin-bottom:10px;font-size:11px;font-weight:600;color:#888;letter-spacing:0.5px">PROCESO DE CARGA</div>
    <div class="steps" id="stepsBar">
        <div class="step">
            <div class="step-circle active" id="step1">1</div>
            <span class="step-label active">Seleccionar archivo</span>
        </div>
        <div class="step-line" id="line1"></div>
        <div class="step">
            <div class="step-circle pending" id="step2">2</div>
            <span class="step-label pending" id="stepLabel2">Enviar a FastAPI</span>
        </div>
        <div class="step-line" id="line2"></div>
        <div class="step">
            <div class="step-circle pending" id="step3">3</div>
            <span class="step-label pending" id="stepLabel3">Procesar datos</span>
        </div>
        <div class="step-line" id="line3"></div>
        <div class="step">
            <div class="step-circle pending" id="step4">4</div>
            <span class="step-label pending" id="stepLabel4">Actualizar modelos</span>
        </div>
    </div>
</div>

{{-- Fila principal --}}
<div style="display:grid;grid-template-columns:1.05fr 1fr;gap:14px;margin-bottom:16px">

    {{-- Zona de carga --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <svg viewBox="0 0 16 16" fill="none" stroke="#0099D6" stroke-width="1.5"><path d="M8 2v8M5 5L8 2l3 3"/><path d="M2 11v1a2 2 0 002 2h8a2 2 0 002-2v-1"/></svg>
                Subir archivo Excel
            </div>
            <span class="badge badge-blue">Mensual</span>
        </div>

        {{-- Input oculto --}}
        <input type="file" id="inputArchivo" name="archivo" accept=".xlsx,.xls" style="display:none" onchange="mostrarArchivo(this)">

        {{-- Drop zone --}}
        <div class="drop-zone" id="dropZone" onclick="document.getElementById('inputArchivo').click()">
            <div style="width:52px;height:52px;border-radius:50%;background:#E0F4FC;display:flex;align-items:center;justify-content:center;margin:0 auto 14px">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#0099D6" stroke-width="1.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            </div>
            <div style="font-size:14px;font-weight:600;color:#1a1a2e;margin-bottom:6px">Arrastra tu archivo Excel aquí</div>
            <div style="font-size:12px;color:#888;margin-bottom:16px">o haz clic para seleccionar desde tu computador</div>
            <div class="drop-formats">
                <div class="fmt-chip">.xlsx</div>
                <div class="fmt-chip">.xls</div>
                <div class="fmt-chip">Máx. 50 MB</div>
                <div class="fmt-chip">Hoja: Pilar</div>
            </div>
        </div>

        {{-- Archivo seleccionado --}}
        <div class="file-selected" id="fileSelected" style="display:none;margin-top:12px">
            <div class="file-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <div class="file-meta">
                <div class="file-name" id="fileName">—</div>
                <div class="file-info" id="fileInfo">—</div>
            </div>
            <div style="cursor:pointer;color:#E74C3C;font-size:11px;font-weight:600" onclick="quitarArchivo()">✕ Quitar</div>
        </div>

        {{-- Columnas requeridas --}}
        <div style="margin-top:12px;padding:10px 12px;background:#FAFAFA;border:0.5px solid #eee;border-radius:8px">
            <div style="font-size:11px;font-weight:600;color:#555;margin-bottom:6px">Columnas requeridas</div>
            <div style="display:flex;flex-wrap:wrap;gap:5px">
                @foreach(['T.I.','Codigo Cliente','Mensualidad','Megas','Municipio','Fecha Instalacion','Columnas de pago (Ene 2019…)'] as $col)
                <span style="background:#E6F5FC;color:#185FA5;font-size:10px;padding:2px 8px;border-radius:4px;font-weight:500">{{ $col }}</span>
                @endforeach
            </div>
        </div>

        {{-- Botones --}}
        <div style="display:flex;gap:8px;margin-top:14px">
            <button type="button" id="btnProcesar" class="btn-primary" style="flex:1" disabled onclick="enviarArchivo()">
                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="white" stroke-width="2"><path d="M3 8l3 3 7-7"/></svg>
                <span id="btnTexto">Validar y procesar</span>
            </button>
            <button type="button" class="btn-secondary" onclick="quitarArchivo()">
                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 2l12 12M14 2L2 14"/></svg>
                Cancelar
            </button>
        </div>

        {{-- Barra de progreso --}}
        <div id="progressSection" style="display:none;margin-top:14px">
            <div style="display:flex;justify-content:space-between;font-size:10px;color:#888;margin-bottom:5px">
                <span id="progressLabel">Procesando...</span>
                <span id="progressPct" style="color:#0099D6;font-weight:600">0%</span>
            </div>
            <div class="progress-wrap">
                <div class="progress-bar" id="progressBar" style="width:0%"></div>
            </div>
            <div style="font-size:10px;color:#aaa;margin-top:4px;text-align:center">
                Este proceso puede tardar hasta 5 minutos. No cierres esta página.
            </div>
        </div>
    </div>

    {{-- Panel de información --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <svg viewBox="0 0 16 16" fill="none" stroke="#0099D6" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><path d="M8 7v5M8 5h.01"/></svg>
                ¿Cómo funciona la carga?
            </div>
            <span class="badge badge-gray">Guía</span>
        </div>
        <div style="display:flex;flex-direction:column;gap:10px">
            @foreach([
                ['1', '#0099D6', 'Selecciona el archivo Excel', 'El archivo debe ser el reporte mensual de SISTCO con la hoja "Pilar" que contiene el historial de pagos.'],
                ['2', '#0099D6', 'Limpieza automática', 'FastAPI procesa el Excel automáticamente: normaliza municipios, megas, fechas y calcula todas las variables necesarias.'],
                ['3', '#27AE60', 'Predicciones automáticas', 'Si FastAPI está activo, las predicciones ML se recalculan automáticamente para todos los clientes.'],
                ['4', '#F39C12', 'FastAPI requerido', 'Este módulo requiere que FastAPI esté corriendo en el puerto 8000. Inicia uvicorn antes de cargar.'],
            ] as [$num, $color, $titulo, $desc])
            <div style="display:flex;gap:10px;padding:10px;background:#FAFAFA;border-radius:8px;border:0.5px solid #eee">
                <div style="width:24px;height:24px;border-radius:50%;background:{{ $color }};color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0">{{ $num }}</div>
                <div>
                    <div style="font-size:12px;font-weight:600;color:#1a1a2e;margin-bottom:2px">{{ $titulo }}</div>
                    <div style="font-size:11px;color:#888;line-height:1.4">{{ $desc }}</div>
                </div>
            </div>
            @endforeach
        </div>
        <div style="margin-top:14px;padding:10px;background:#FFF8E6;border:0.5px solid #F5C842;border-radius:8px">
            <div style="font-size:11px;color:#7a5c00;display:flex;gap:6px;align-items:flex-start">
                <span>⚠️</span>
                <span>La carga reemplaza todos los datos actuales. Asegúrate de que el archivo sea el más reciente antes de procesar.</span>
            </div>
        </div>
    </div>
</div>

{{-- Historial --}}
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <svg viewBox="0 0 16 16" fill="none" stroke="#0099D6" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><path d="M8 5v3l2 1.5"/></svg>
            Historial de cargas recientes
        </div>
        <span class="badge badge-gray">Últimas {{ $historial->count() }}</span>
    </div>
    @if($historial->count() > 0)
    <table class="hist-table">
        <thead>
            <tr>
                <th>Archivo</th><th>Fecha</th><th>Usuario</th><th>Clientes</th><th>Estado</th><th>Modelos ML</th><th>Mensaje</th>
            </tr>
        </thead>
        <tbody>
            @foreach($historial as $carga)
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:7px">
                        <div style="width:24px;height:24px;background:{{ $carga->estado==='exitoso'?'#27AE60':($carga->estado==='fallido'?'#E74C3C':'#F39C12') }};border-radius:5px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                            <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="white" stroke-width="1.8"><path d="M14 2H6a2 2 0 00-2 2v9a2 2 0 002 2h8a2 2 0 002-2V4z"/></svg>
                        </div>
                        <span style="font-weight:500;color:#1a1a2e;font-size:11px">{{ $carga->nombre_archivo }}</span>
                    </div>
                </td>
                <td style="font-size:11px;color:#888">{{ $carga->created_at->format('d/m/Y H:i') }}</td>
                <td style="font-size:11px">{{ $carga->user?->name ?? '—' }}</td>
                <td style="font-weight:600;color:#1a1a2e">{{ $carga->total_clientes > 0 ? $carga->total_clientes : '—' }}</td>
                <td>
                    @if($carga->estado==='exitoso') <span class="status-pill pill-ok">✓ Exitoso</span>
                    @elseif($carga->estado==='advertencias') <span class="status-pill pill-warn">⚠ Advertencias</span>
                    @else <span class="status-pill pill-err">✕ Fallido</span>
                    @endif
                </td>
                <td>
                    @if($carga->modelos_estado==='actualizados') <span class="status-pill pill-ok">✓ Actualizados</span>
                    @elseif($carga->modelos_estado==='pendiente') <span class="status-pill pill-pend">⏳ Pendiente</span>
                    @else <span class="status-pill pill-err">✕ Sin cambios</span>
                    @endif
                </td>
                <td style="font-size:11px;color:#888;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $carga->mensaje ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div style="padding:30px;text-align:center;color:#aaa;font-size:13px">No hay cargas registradas aún.</div>
    @endif
</div>

<script>
let intervaloProgreso = null;
let controller = null;
let timeoutId = null;

function mostrarArchivo(input) {
    if (input.files && input.files[0]) {
        const file   = input.files[0];
        const sizeMB = (file.size / 1024 / 1024).toFixed(2);
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileInfo').textContent = sizeMB + ' MB · Listo para procesar';
        document.getElementById('fileSelected').style.display = 'flex';
        document.getElementById('dropZone').style.display     = 'none';
        document.getElementById('btnProcesar').disabled       = false;
    }
}

function quitarArchivo() {
    // Cancelar fetch si está en curso
    if (controller) {
        controller.abort();
        controller = null;
    }
    if (timeoutId) {
        clearTimeout(timeoutId);
        timeoutId = null;
    }
    document.getElementById('inputArchivo').value             = '';
    document.getElementById('fileSelected').style.display    = 'none';
    document.getElementById('dropZone').style.display        = 'block';
    document.getElementById('btnProcesar').disabled          = true;
    document.getElementById('progressSection').style.display = 'none';
    if (intervaloProgreso) clearInterval(intervaloProgreso);
}

function avanzarPaso(paso) {
    for (let i = 1; i <= 4; i++) {
        const circle = document.getElementById('step' + i);
        const label  = document.getElementById('stepLabel' + i);
        const line   = document.getElementById('line' + i);
        if (i < paso) {
            circle.className = 'step-circle done';
            circle.innerHTML = '✓';
            if (label) { label.className = 'step-label done'; }
            if (line)  { line.className  = 'step-line done'; }
        } else if (i === paso) {
            circle.className = 'step-circle active';
            circle.textContent = i;
            if (label) { label.className = 'step-label active'; }
        }
    }
}

function setProgreso(pct, label) {
    document.getElementById('progressBar').style.width  = pct + '%';
    document.getElementById('progressPct').textContent  = pct + '%';
    document.getElementById('progressLabel').textContent = label;
}

function enviarArchivo() {
    const input = document.getElementById('inputArchivo');
    if (!input.files || input.files.length === 0) {
        alert('Por favor selecciona un archivo primero.');
        return;
    }

    // Cancelar fetch anterior si existe
    if (controller) {
        controller.abort();
    }
    
    // Crear nuevo AbortController
    controller = new AbortController();
    
    // Timeout de 5 minutos (300,000 ms)
    timeoutId = setTimeout(() => {
        if (controller) {
            controller.abort();
        }
    }, 300000);

    // Deshabilitar botón y mostrar progreso
    document.getElementById('btnProcesar').disabled          = true;
    document.getElementById('btnTexto').textContent          = 'Procesando...';
    document.getElementById('progressSection').style.display = 'block';
    document.getElementById('alertaExito').style.display     = 'none';
    document.getElementById('alertaError').style.display     = 'none';

    avanzarPaso(2);
    setProgreso(5, 'Enviando archivo a FastAPI...');

    // Simular progreso visual mientras espera
    let pctActual = 5;
    intervaloProgreso = setInterval(() => {
        if (pctActual < 85) {
            pctActual += 3;
            if (pctActual < 20)      setProgreso(pctActual, 'Enviando archivo a FastAPI...');
            else if (pctActual < 50) { avanzarPaso(3); setProgreso(pctActual, 'Procesando y limpiando datos...'); }
            else if (pctActual < 80) { avanzarPaso(4); setProgreso(pctActual, 'Importando clientes a la BD...'); }
            else                       setProgreso(pctActual, 'Actualizando predicciones ML...');
        }
    }, 3000);

    // Enviar con fetch y AbortController
    const formData = new FormData();
    formData.append('archivo', input.files[0]);
    formData.append('_token', '{{ csrf_token() }}');

    fetch('{{ route("carga-datos.procesar") }}', {
        method: 'POST',
        body:   formData,
        signal: controller.signal,  // ← Importante: agregar la señal
    })
    .then(response => response.json())
    .then(data => {
        clearInterval(intervaloProgreso);
        clearTimeout(timeoutId);
        setProgreso(100, 'Completado');
        avanzarPaso(5);

        if (data && data.success) {
            document.getElementById('alertaExito').style.display = 'block';
            document.getElementById('alertaExito').textContent   = '✅ ' + data.mensaje;
            setTimeout(() => location.reload(), 2000);
        } else {
            const msg = (data && data.error) ? data.error : 'Error desconocido. Revisa que FastAPI esté corriendo.';
            document.getElementById('alertaError').style.display = 'block';
            document.getElementById('alertaError').textContent   = '❌ ' + msg;
            document.getElementById('btnProcesar').disabled      = false;
            document.getElementById('btnTexto').textContent      = 'Validar y procesar';
        }
        controller = null;
    })
    .catch(err => {
        clearInterval(intervaloProgreso);
        clearTimeout(timeoutId);
        
        // Manejo especial para AbortError (timeout)
        if (err.name === 'AbortError') {
            setProgreso(100, 'Completado');
            document.getElementById('alertaExito').style.display = 'block';
            document.getElementById('alertaExito').textContent   = '✅ Proceso completado. Recarga la página para ver el historial.';
            setTimeout(() => location.reload(), 2000);
        } else {
            document.getElementById('alertaError').style.display = 'block';
            document.getElementById('alertaError').textContent   = '❌ No se pudo conectar con el servidor. Verifica que FastAPI esté corriendo.';
            document.getElementById('btnProcesar').disabled      = false;
            document.getElementById('btnTexto').textContent      = 'Validar y procesar';
        }
        controller = null;
    });
}

// Drag and drop
const dropZone = document.getElementById('dropZone');
dropZone.addEventListener('dragover',  e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('drag-over');
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        document.getElementById('inputArchivo').files = files;
        mostrarArchivo(document.getElementById('inputArchivo'));
    }
});
</script>

@endsection