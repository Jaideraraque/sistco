<x-guest-layout>
@push('head')
<title>SISTCO-ML — Iniciar Sesión</title>
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><circle cx='16' cy='16' r='16' fill='%230099D6'/><circle cx='16' cy='16' r='11' stroke='white' stroke-width='2' fill='none'/><path d='M9 16 Q16 9 23 16 Q16 23 9 16Z' stroke='white' stroke-width='1.5' fill='none'/></svg>">
@endpush

<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

/* Override Jetstream guest layout */
body { background: #E8EDF2 !important; }

.min-h-screen {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: linear-gradient(135deg, #0099D6 0%, #005f8a 50%, #1a1a2e 100%) !important;
    min-height: 100vh !important;
    padding: 20px !important;
}

/* Ocultar el contenedor por defecto de Jetstream */
.min-h-screen > div { display: none !important; }

/* Nuestro wrapper personalizado */
.login-wrapper {
    display: flex;
    width: 920px;
    height: 600px;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(0,0,0,0.35);
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

/* Panel izquierdo */
.brand-panel {
    width: 420px;
    background: linear-gradient(160deg, #0099D6 0%, #0077AA 60%, #005f8a 100%);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 48px 44px;
    flex-shrink: 0;
    position: relative;
    overflow: hidden;
}

.brand-panel::before {
    content: '';
    position: absolute;
    top: -80px;
    right: -80px;
    width: 300px;
    height: 300px;
    border-radius: 50%;
    background: rgba(255,255,255,0.05);
}

.brand-panel::after {
    content: '';
    position: absolute;
    bottom: -60px;
    left: -60px;
    width: 250px;
    height: 250px;
    border-radius: 50%;
    background: rgba(255,255,255,0.04);
}

.brand-logo {
    display: flex;
    align-items: center;
    gap: 14px;
    position: relative;
    z-index: 1;
}

.brand-logo img {
    width: 160px;
    filter: drop-shadow(0 0 0 transparent);
    mix-blend-mode: multiply;
    border-radius: 12px;
}

.brand-middle { position: relative; z-index: 1; }

.brand-headline {
    font-size: 30px;
    font-weight: 700;
    color: #fff;
    line-height: 1.3;
    margin-bottom: 16px;
}

.brand-headline span { color: rgba(255,255,255,0.55); }

.brand-desc {
    font-size: 13px;
    color: rgba(255,255,255,0.75);
    line-height: 1.7;
    margin-bottom: 32px;
}

.features { display: flex; flex-direction: column; gap: 14px; }

.feature-item { display: flex; align-items: center; gap: 12px; }

.feature-icon {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: rgba(255,255,255,0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    backdrop-filter: blur(4px);
}

.feature-icon svg { width: 16px; height: 16px; }
.feature-text { font-size: 12.5px; color: rgba(255,255,255,0.85); line-height: 1.4; }

.brand-footer {
    font-size: 10px;
    color: rgba(255,255,255,0.45);
    line-height: 1.6;
    position: relative;
    z-index: 1;
}

/* Panel derecho */
.form-panel {
    flex: 1;
    background: #fff;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 52px 48px;
}

.form-header { margin-bottom: 28px; }

.form-title {
    font-size: 24px;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 6px;
}

.form-subtitle { font-size: 13px; color: #aaa; }

.form-group { margin-bottom: 18px; }

.form-label {
    font-size: 12px;
    font-weight: 600;
    color: #555;
    margin-bottom: 7px;
    display: block;
    letter-spacing: 0.2px;
}

.input-wrap { position: relative; }

.input-icon {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    width: 15px;
    height: 15px;
    color: #bbb;
    z-index: 1;
}

.form-input {
    width: 100%;
    padding: 11px 12px 11px 38px;
    border: 1px solid #e8e8e8;
    border-radius: 10px;
    font-size: 13px;
    color: #1a1a2e;
    background: #FAFAFA;
    outline: none;
    transition: all .2s;
}

.form-input:focus {
    border-color: #0099D6;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(0,153,214,0.08);
}

.form-input::placeholder { color: #ccc; }

.form-options {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
}

.checkbox-wrap { display: flex; align-items: center; gap: 8px; cursor: pointer; }
.checkbox-wrap input { width: 15px; height: 15px; accent-color: #0099D6; cursor: pointer; }
.checkbox-label { font-size: 12px; color: #666; }

.forgot-link {
    font-size: 12px;
    color: #0099D6;
    text-decoration: none;
}

.forgot-link:hover { text-decoration: underline; }

.btn-login {
    width: 100%;
    padding: 13px;
    background: linear-gradient(135deg, #0099D6, #0077AA);
    border: none;
    border-radius: 10px;
    color: #fff;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    letter-spacing: 0.3px;
    transition: all .2s;
    box-shadow: 0 4px 15px rgba(0,153,214,0.3);
}

.btn-login:hover {
    background: linear-gradient(135deg, #0088C0, #005f8a);
    box-shadow: 0 6px 20px rgba(0,153,214,0.4);
    transform: translateY(-1px);
}

.status-bar {
    margin-top: 20px;
    padding: 10px 14px;
    background: #F0FFF4;
    border: 1px solid #C0DD97;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-footer {
    margin-top: 24px;
    text-align: center;
    font-size: 11px;
    color: #ccc;
    line-height: 1.6;
}

.form-footer a { color: #0099D6; text-decoration: none; }

/* Errores de validación */
.validation-errors {
    background: #FDEDEC;
    border: 1px solid #f5c6c6;
    border-radius: 10px;
    padding: 10px 14px;
    margin-bottom: 16px;
    font-size: 12px;
    color: #A32D2D;
}
</style>

<div class="login-wrapper">

    <!-- Panel izquierdo -->
    <div class="brand-panel">
        <div class="brand-logo">
            <img src="{{ asset('images/logo_sistco.png') }}" alt="SISTCO">
        </div>

        <div class="brand-middle">
            <div class="brand-headline">
                Análisis predictivo<br>
                <span>para tu empresa</span>
            </div>
            <div class="brand-desc">
                Transforma tus datos históricos de clientes en decisiones estratégicas con inteligencia artificial.
            </div>
            <div class="features">
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg viewBox="0 0 16 16" fill="none" stroke="white" stroke-width="1.5">
                            <path d="M8 2L14 13H2L8 2z"/><path d="M8 7v3"/>
                        </svg>
                    </div>
                    <div class="feature-text">Predicción de morosidad con Machine Learning</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg viewBox="0 0 16 16" fill="none" stroke="white" stroke-width="1.5">
                            <path d="M2 12L6 7L9 9L14 4"/>
                        </svg>
                    </div>
                    <div class="feature-text">Proyección de ingresos a 3 y 6 meses</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg viewBox="0 0 16 16" fill="none" stroke="white" stroke-width="1.5">
                            <circle cx="5" cy="8" r="3"/><circle cx="11" cy="8" r="3"/>
                        </svg>
                    </div>
                    <div class="feature-text">Segmentación automática de clientes</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg viewBox="0 0 16 16" fill="none" stroke="white" stroke-width="1.5">
                            <rect x="2" y="3" width="12" height="10" rx="1"/><path d="M5 7h6M5 10h4"/>
                        </svg>
                    </div>
                    <div class="feature-text">Reportes y dashboards en tiempo real</div>
                </div>
            </div>
        </div>

        <div class="brand-footer">
            SISTCO Sistemas y Comunicaciones SAS<br>
            Sistema de Análisis Predictivo · v1.0 · © 2026<br>
            Santander, Colombia
        </div>
    </div>

    <!-- Panel derecho -->
    <div class="form-panel">
        <div class="form-header">
            <div class="form-title">Bienvenido de nuevo</div>
            <div class="form-subtitle">Ingresa tus credenciales para acceder al sistema</div>
        </div>

        <!-- Errores de validación -->
        @if ($errors->any())
            <div class="validation-errors">
                <strong>Error:</strong> Credenciales incorrectas. Verifica tu correo y contraseña.
            </div>
        @endif

        @if(session('error'))
            <div style="background:#FDEDEC;border:1px solid #F5C6CB;color:#A32D2D;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px">
                ⚠️ {{ session('error') }}
            </div>
        @endif

        <!-- Estado -->
        @session('status')
            <div style="background:#F0FFF4;border:1px solid #C0DD97;border-radius:10px;padding:10px 14px;margin-bottom:16px;font-size:12px;color:#27500A">
                {{ $value }}
            </div>
        @endsession

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email -->
            <div class="form-group">
                <label class="form-label">Correo electrónico</label>
                <div class="input-wrap">
                    <svg class="input-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="8" cy="6" r="3"/><path d="M2 14c0-3.3 2.7-6 6-6s6 2.7 6 6"/>
                    </svg>
                    <input class="form-input" type="email" name="email" value="{{ old('email') }}"
                           placeholder="correo@sistco.com.co" required autofocus autocomplete="username">
                </div>
            </div>

            <!-- Contraseña con botón mostrar/ocultar -->
            <div class="form-group">
                <label class="form-label">Contraseña</label>
                <div class="input-wrap">
                    <svg class="input-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="7" width="10" height="8" rx="1"/><path d="M5 7V5a3 3 0 016 0v2"/>
                    </svg>
                    <input class="form-input" type="password" name="password" id="password-input"
                           placeholder="••••••••" required autocomplete="current-password">
                    <button type="button" onclick="togglePassword()" id="eye-btn"
                            style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:0;color:#bbb;z-index:1;">
                        <svg id="eye-icon" width="17" height="17" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M1 8s2.5-5 7-5 7 5 7 5-2.5 5-7 5-7-5-7-5z"/>
                            <circle cx="8" cy="8" r="2"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Opciones -->
            <div class="form-options">
                <label class="checkbox-wrap">
                    <input type="checkbox" name="remember">
                    <span class="checkbox-label">Recordar sesión</span>
                </label>
                @if (Route::has('password.request'))
                    <a class="forgot-link" href="{{ route('password.request') }}">
                        ¿Olvidaste tu contraseña?
                    </a>
                @endif
            </div>

            <!-- Botón -->
            <button type="submit" class="btn-login">Iniciar sesión</button>
        </form>

        <!-- Estado del sistema -->
        <div class="status-bar">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="#27AE60" stroke-width="1.5">
                <path d="M3 8l3 3 7-7"/>
            </svg>
            <div>
                <div style="font-size:11px;font-weight:600;color:#27500A">Sistema operativo</div>
                <div style="font-size:10px;color:#3B6D11">Modelo activo v1.0 · Datos al {{ now()->format('d/m/Y') }}</div>
            </div>
        </div>

        <div class="form-footer">
            Acceso restringido a personal autorizado de SISTCO<br>
            ¿Problemas para ingresar? <a href="{{ route('soporte.publico') }}">Contactar soporte</a>
        </div>
    </div>

</div>

<script>
function togglePassword() {
    const input = document.getElementById('password-input');
    const icon  = document.getElementById('eye-icon');
    const show  = input.type === 'password';
    input.type  = show ? 'text' : 'password';
    icon.innerHTML = show
        ? `<path d="M2 2l12 12M6.5 6.6A3 3 0 0 0 8 11a3 3 0 0 0 2.9-2.2M1 8s2.5-5 7-5c1 0 2 .2 2.9.6M15 8s-.8 1.6-2.4 2.9"/>`
        : `<path d="M1 8s2.5-5 7-5 7 5 7 5-2.5 5-7 5-7-5-7-5z"/><circle cx="8" cy="8" r="2"/>`;
    document.getElementById('eye-btn').style.color = show ? '#0099D6' : '#bbb';
}
</script>

</x-guest-layout>