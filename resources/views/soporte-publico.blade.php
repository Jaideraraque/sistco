<x-guest-layout>
@push('head')
<title>SISTCO-ML — Contactar Soporte</title>
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><circle cx='16' cy='16' r='16' fill='%230099D6'/><circle cx='16' cy='16' r='11' stroke='white' stroke-width='2' fill='none'/><path d='M9 16 Q16 9 23 16 Q16 23 9 16Z' stroke='white' stroke-width='1.5' fill='none'/></svg>">
@endpush
<style>
*{box-sizing:border-box;margin:0;padding:0}
.min-h-screen{display:flex!important;align-items:center!important;justify-content:center!important;background:linear-gradient(135deg,#0099D6 0%,#005f8a 50%,#1a1a2e 100%)!important;min-height:100vh!important;padding:20px!important}
.min-h-screen>div{display:none!important}
.wrapper{display:flex;width:920px;min-height:600px;border-radius:20px;overflow:hidden;box-shadow:0 25px 60px rgba(0,0,0,0.35);position:fixed;top:50%;left:50%;transform:translate(-50%,-50%)}
.brand-panel{width:380px;background:linear-gradient(160deg,#0099D6 0%,#0077AA 60%,#005f8a 100%);display:flex;flex-direction:column;justify-content:space-between;padding:48px 44px;flex-shrink:0;position:relative;overflow:hidden}
.brand-panel::before{content:'';position:absolute;top:-80px;right:-80px;width:300px;height:300px;border-radius:50%;background:rgba(255,255,255,0.05)}
.brand-panel::after{content:'';position:absolute;bottom:-60px;left:-60px;width:250px;height:250px;border-radius:50%;background:rgba(255,255,255,0.04)}
.brand-logo{position:relative;z-index:1}
.brand-logo img{width:140px;border-radius:10px}
.brand-middle{position:relative;z-index:1}
.brand-headline{font-size:26px;font-weight:700;color:#fff;line-height:1.3;margin-bottom:12px}
.brand-headline span{color:rgba(255,255,255,0.55)}
.brand-desc{font-size:12px;color:rgba(255,255,255,0.75);line-height:1.7;margin-bottom:28px}
.features{display:flex;flex-direction:column;gap:12px}
.feature-item{display:flex;align-items:center;gap:10px}
.feature-icon{width:30px;height:30px;border-radius:8px;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.feature-text{font-size:12px;color:rgba(255,255,255,0.85)}
.brand-footer{font-size:10px;color:rgba(255,255,255,0.45);line-height:1.6;position:relative;z-index:1}
.form-panel{flex:1;background:#fff;display:flex;flex-direction:column;justify-content:center;padding:48px 44px;overflow-y:auto}
.form-title{font-size:22px;font-weight:700;color:#1a1a2e;margin-bottom:6px}
.form-subtitle{font-size:12px;color:#aaa;margin-bottom:24px;line-height:1.6}
.form-group{margin-bottom:16px}
.form-label{font-size:12px;font-weight:600;color:#555;margin-bottom:7px;display:block}
.input-wrap{position:relative}
.input-icon{position:absolute;left:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#bbb}
.form-input{width:100%;padding:11px 12px 11px 38px;border:1px solid #e8e8e8;border-radius:10px;font-size:13px;color:#1a1a2e;background:#FAFAFA;outline:none;transition:all .2s}
.form-input:focus{border-color:#0099D6;background:#fff;box-shadow:0 0 0 3px rgba(0,153,214,0.08)}
.form-input::placeholder{color:#ccc}
.form-select{width:100%;padding:11px 12px;border:1px solid #e8e8e8;border-radius:10px;font-size:13px;color:#1a1a2e;background:#FAFAFA;outline:none;transition:all .2s}
.form-select:focus{border-color:#0099D6;background:#fff;box-shadow:0 0 0 3px rgba(0,153,214,0.08)}
.form-textarea{width:100%;padding:11px 12px;border:1px solid #e8e8e8;border-radius:10px;font-size:13px;color:#1a1a2e;background:#FAFAFA;outline:none;transition:all .2s;resize:none;font-family:Arial,sans-serif}
.form-textarea:focus{border-color:#0099D6;background:#fff;box-shadow:0 0 0 3px rgba(0,153,214,0.08)}
.btn-submit{width:100%;padding:13px;background:linear-gradient(135deg,#0099D6,#0077AA);border:none;border-radius:10px;color:#fff;font-size:14px;font-weight:600;cursor:pointer;transition:all .2s;box-shadow:0 4px 15px rgba(0,153,214,0.3)}
.btn-submit:hover{background:linear-gradient(135deg,#0088C0,#005f8a);transform:translateY(-1px)}
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.prioridad-btn{text-align:center;padding:8px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;transition:all .15s}
.back-link{display:flex;align-items:center;gap:6px;margin-top:16px;font-size:12px;color:#0099D6;text-decoration:none;justify-content:center}
.back-link:hover{text-decoration:underline}
.alert-error{background:#FDEDEC;border:1px solid #f5c6c6;border-radius:10px;padding:12px 14px;margin-bottom:16px;font-size:12px;color:#A32D2D}
</style>

<div class="wrapper">

    {{-- Panel izquierdo --}}
    <div class="brand-panel">
        <div class="brand-logo">
            <img src="{{ asset('images/logo_sistco.png') }}" alt="SISTCO">
        </div>
        <div class="brand-middle">
            <div class="brand-headline">
                Estamos aquí<br>
                <span>para ayudarte</span>
            </div>
            <div class="brand-desc">
                Envíanos tu consulta y el equipo de SISTCO te responderá a la brevedad posible.
            </div>
            <div class="features">
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="white" stroke-width="1.5"><path d="M3 8l3 3 7-7"/></svg>
                    </div>
                    <div class="feature-text">Respuesta en menos de 24 horas</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="white" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><path d="M8 5v3l2 2"/></svg>
                    </div>
                    <div class="feature-text">Soporte técnico especializado</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="white" stroke-width="1.5"><rect x="2" y="3" width="12" height="10" rx="1"/><path d="M5 7h6M5 10h4"/></svg>
                    </div>
                    <div class="feature-text">Seguimiento a tu solicitud</div>
                </div>
            </div>
        </div>
        <div class="brand-footer">
            SISTCO Sistemas y Comunicaciones SAS<br>
            Sistema de Análisis Predictivo · v1.0 · © 2026<br>
            Santander, Colombia
        </div>
    </div>

    {{-- Panel derecho --}}
    <div class="form-panel">

        @if(session('soporte_enviado'))
        <div style="text-align:center;padding:20px 0">
            <div style="width:64px;height:64px;border-radius:50%;background:#F0FFF4;border:1.5px solid #C0DD97;display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
                <svg width="28" height="28" viewBox="0 0 16 16" fill="none" stroke="#27AE60" stroke-width="1.5"><path d="M3 8l3 3 7-7"/></svg>
            </div>
            <div style="font-size:22px;font-weight:700;color:#1a1a2e;margin-bottom:8px">¡Mensaje enviado!</div>
            <div style="font-size:13px;color:#888;line-height:1.7;margin-bottom:28px">Tu solicitud fue recibida correctamente. El equipo de soporte de SISTCO te contactará pronto.</div>
            <a href="{{ route('login') }}" class="btn-submit" style="display:block;text-decoration:none;text-align:center">
                Volver al inicio de sesión
            </a>
        </div>

        @else

        <div class="form-title">Contactar soporte</div>
        <div class="form-subtitle">Completa el formulario y te responderemos a la brevedad.</div>

        @if($errors->any())
        <div class="alert-error">⚠️ {{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('soporte.publico.enviar') }}">
            @csrf

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Nombre completo</label>
                    <div class="input-wrap">
                        <svg class="input-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="6" r="3"/><path d="M2 14c0-3.3 2.7-6 6-6s6 2.7 6 6"/></svg>
                        <input class="form-input" type="text" name="nombre" value="{{ old('nombre') }}"
                               placeholder="Tu nombre" required autofocus>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Correo electrónico</label>
                    <div class="input-wrap">
                        <svg class="input-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="4" width="12" height="9" rx="1"/><path d="M2 4l6 5 6-5"/></svg>
                        <input class="form-input" type="email" name="correo" value="{{ old('correo') }}"
                               placeholder="correo@ejemplo.com" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Asunto</label>
                <select name="asunto" class="form-select" required>
                    <option value="">Selecciona una categoría...</option>
                    <option value="Problema técnico" {{ old('asunto') === 'Problema técnico' ? 'selected' : '' }}>Problema técnico</option>
                    <option value="No puedo acceder al sistema" {{ old('asunto') === 'No puedo acceder al sistema' ? 'selected' : '' }}>No puedo acceder al sistema</option>
                    <option value="Error en el sistema" {{ old('asunto') === 'Error en el sistema' ? 'selected' : '' }}>Error en el sistema</option>
                    <option value="Solicitud de nuevo rol" {{ old('asunto') === 'Solicitud de nuevo rol' ? 'selected' : '' }}>Solicitud de nuevo rol</option>
                    <option value="Error en modelo de IA" {{ old('asunto') === 'Error en modelo de IA' ? 'selected' : '' }}>Error en modelo de IA</option>
                    <option value="Otro" {{ old('asunto') === 'Otro' ? 'selected' : '' }}>Otro</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Prioridad</label>
                <div style="display:flex;gap:8px">
                    @foreach(['baja' => ['#27AE60','#F0FFF4','#C0DD97'], 'media' => ['#F39C12','#FEF9E7','#FDEBD0'], 'alta' => ['#E24B4A','#FDEDEC','#f5c6c6']] as $p => $c)
                    <label style="flex:1;cursor:pointer">
                        <input type="radio" name="prioridad" value="{{ $p }}" {{ $p === 'media' ? 'checked' : '' }}
                               style="display:none" class="radio-prioridad">
                        <div class="prioridad-btn" data-p="{{ $p }}"
                             style="border:1.5px solid {{ $c[2] }};background:{{ $c[1] }};color:{{ $c[0] }}">
                            {{ ucfirst($p) }}
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Mensaje</label>
                <textarea name="mensaje" class="form-textarea" rows="4"
                          placeholder="Describe el problema con el mayor detalle posible..." required>{{ old('mensaje') }}</textarea>
            </div>

            <button type="submit" class="btn-submit">Enviar mensaje</button>
        </form>

        <a href="{{ route('login') }}" class="back-link">
            <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M10 4L6 8l4 4"/></svg>
            Volver al inicio de sesión
        </a>
        @endif
    </div>
</div>

<script>
const coloresActivo = {
    baja:  { bg: '#C0DD97', border: '#27AE60', color: '#27AE60' },
    media: { bg: '#FAC775', border: '#F39C12', color: '#854F0B' },
    alta:  { bg: '#F09595', border: '#E24B4A', color: '#A32D2D' },
};
const coloresInactivo = {
    baja:  { bg: '#F0FFF4', border: '#C0DD97', color: '#27AE60' },
    media: { bg: '#FEF9E7', border: '#FDEBD0', color: '#F39C12' },
    alta:  { bg: '#FDEDEC', border: '#f5c6c6', color: '#E24B4A' },
};
function actualizarPrioridad(valor) {
    document.querySelectorAll('.prioridad-btn').forEach(btn => {
        const p = btn.dataset.p;
        const c = p === valor ? coloresActivo[p] : coloresInactivo[p];
        btn.style.background  = c.bg;
        btn.style.borderColor = c.border;
        btn.style.color       = c.color;
        btn.style.opacity     = p === valor ? '1' : '0.5';
        btn.style.transform   = p === valor ? 'scale(1.04)' : 'scale(1)';
    });
}
document.querySelectorAll('.radio-prioridad').forEach(radio => {
    radio.addEventListener('change', function() { actualizarPrioridad(this.value); });
});
actualizarPrioridad('media');
</script>
</x-guest-layout>