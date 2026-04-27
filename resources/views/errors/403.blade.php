<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>SISTCO-ML — Acceso denegado</title>
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><circle cx='16' cy='16' r='16' fill='%230099D6'/><circle cx='16' cy='16' r='11' stroke='white' stroke-width='2' fill='none'/><path d='M9 16 Q16 9 23 16 Q16 23 9 16Z' stroke='white' stroke-width='1.5' fill='none'/></svg>">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{background:linear-gradient(135deg,#0099D6 0%,#005f8a 50%,#1a1a2e 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;font-family:Arial,sans-serif;padding:20px}
.wrapper{display:flex;width:820px;height:520px;border-radius:20px;overflow:hidden;box-shadow:0 25px 60px rgba(0,0,0,0.35);position:fixed;top:50%;left:50%;transform:translate(-50%,-50%)}
.brand-panel{width:380px;background:linear-gradient(160deg,#0099D6 0%,#0077AA 60%,#005f8a 100%);display:flex;flex-direction:column;justify-content:space-between;padding:48px 44px;flex-shrink:0;position:relative;overflow:hidden}
.brand-panel::before{content:'';position:absolute;top:-80px;right:-80px;width:300px;height:300px;border-radius:50%;background:rgba(255,255,255,0.05)}
.brand-panel::after{content:'';position:absolute;bottom:-60px;left:-60px;width:250px;height:250px;border-radius:50%;background:rgba(255,255,255,0.04)}
.brand-logo{position:relative;z-index:1}
.brand-logo img{width:140px;border-radius:10px}
.brand-middle{position:relative;z-index:1}
.brand-headline{font-size:26px;font-weight:700;color:#fff;line-height:1.3;margin-bottom:12px}
.brand-headline span{color:rgba(255,255,255,0.55)}
.brand-desc{font-size:12px;color:rgba(255,255,255,0.75);line-height:1.7}
.brand-footer{font-size:10px;color:rgba(255,255,255,0.45);line-height:1.6;position:relative;z-index:1}
.form-panel{flex:1;background:#fff;display:flex;flex-direction:column;justify-content:center;padding:52px 48px}

.error-code{font-size:11px;font-weight:700;letter-spacing:1.5px;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.dot{width:8px;height:8px;border-radius:50%}
.title{font-size:24px;font-weight:700;color:#1a1a2e;margin-bottom:8px}
.subtitle{font-size:13px;color:#aaa;line-height:1.7;margin-bottom:28px}
.subtitle strong{color:#555;font-weight:600}

.info-box{border-radius:10px;padding:14px 16px;margin-bottom:24px;display:flex;align-items:flex-start;gap:12px}
.info-box-sinrol{background:#FEF9E7;border:1px solid #FDEBD0}
.info-box-sinpermiso{background:#FDEDEC;border:1px solid #f5c6c6}
.info-box svg{flex-shrink:0;margin-top:1px}
.info-box-text{font-size:12px;line-height:1.6}
.info-box-title{font-weight:700;margin-bottom:2px}
.sinrol-text{color:#A07000}
.sinpermiso-text{color:#A32D2D}

.btn{width:100%;padding:13px;border:none;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;transition:all .2s;text-decoration:none;display:block;text-align:center}
.btn-primary{background:linear-gradient(135deg,#0099D6,#0077AA);color:#fff;box-shadow:0 4px 15px rgba(0,153,214,0.3);margin-bottom:10px}
.btn-primary:hover{background:linear-gradient(135deg,#0088C0,#005f8a);transform:translateY(-1px)}
.btn-secondary{background:#f5f5f5;color:#555;border:1px solid #e8e8e8 !important}
.btn-secondary:hover{background:#ebebeb}

.form-footer{margin-top:20px;text-align:center;font-size:11px;color:#ccc;line-height:1.6}
.form-footer a{color:#0099D6;text-decoration:none}
</style>
</head>
<body>

@php
    $mensaje = $exception->getMessage();
    $sinRol  = str_contains($mensaje, 'no tiene un rol asignado');
@endphp

<div class="wrapper">

    {{-- Panel izquierdo --}}
    <div class="brand-panel">
        <div class="brand-logo">
            <img src="{{ asset('images/logo_sistco.png') }}" alt="SISTCO">
        </div>
        <div class="brand-middle">
            @if($sinRol)
                <div class="brand-headline">
                    Cuenta sin<br>
                    <span>rol asignado</span>
                </div>
                <div class="brand-desc">
                    Tu cuenta fue creada exitosamente pero aún no tiene un rol asignado. Contacta a soporte para activar tu acceso al sistema.
                </div>
            @else
                <div class="brand-headline">
                    Acceso no<br>
                    <span>autorizado</span>
                </div>
                <div class="brand-desc">
                    No tienes los permisos necesarios para acceder a esta sección. Si crees que es un error contacta a soporte.
                </div>
            @endif
        </div>
        <div class="brand-footer">
            SISTCO Sistemas y Comunicaciones SAS<br>
            Sistema de Análisis Predictivo · v1.0 · © 2026<br>
            Santander, Colombia
        </div>
    </div>

    {{-- Panel derecho --}}
    <div class="form-panel">

        @if($sinRol)
            <div class="error-code">
                <div class="dot" style="background:#F39C12"></div>
                <span style="color:#F39C12">SIN ROL ASIGNADO</span>
            </div>
            <div class="title">¡Bienvenido a SISTCO!</div>
            <div class="subtitle">Tu cuenta está creada pero aún no tiene acceso habilitado. <strong>Contacta a soporte</strong> para que activen tu cuenta.</div>
            <div class="info-box info-box-sinrol">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="#F39C12" stroke-width="1.5">
                    <path d="M8 6v3M8 11h.01"/>
                    <path d="M7.13 2.5L1.5 12.5a1 1 0 00.87 1.5h11.26a1 1 0 00.87-1.5L8.87 2.5a1 1 0 00-1.74 0z"/>
                </svg>
                <div class="info-box-text sinrol-text">
                    <div class="info-box-title">Acción requerida</div>
                    El equipo de soporte de SISTCO debe asignarte un rol para que puedas ingresar al sistema.
                </div>
            </div>
        @else
            <div class="error-code">
                <div class="dot" style="background:#E24B4A"></div>
                <span style="color:#E24B4A">ERROR 403 — SIN PERMISO</span>
            </div>
            <div class="title">Acceso denegado</div>
            <div class="subtitle">No tienes permiso para ver esta sección. Si crees que deberías tener acceso, <strong>contacta a soporte</strong>.</div>
            <div class="info-box info-box-sinpermiso">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="#A32D2D" stroke-width="1.5">
                    <rect x="3" y="7" width="10" height="8" rx="1"/><path d="M5 7V5a3 3 0 016 0v2"/>
                </svg>
                <div class="info-box-text sinpermiso-text">
                    <div class="info-box-title">Sección restringida</div>
                    Tu rol actual no tiene acceso a esta área del sistema.
                </div>
            </div>
        @endif

        <a href="mailto:soporte@sistco.com.co" class="btn btn-primary">
            Contactar soporte
        </a>

        @if($sinRol)
            <a href="{{ route('logout') }}"
               class="btn btn-secondary"
               onclick="event.preventDefault(); document.getElementById('logout-f').submit()">
                Cerrar sesión
            </a>
        @else
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                Volver al dashboard
            </a>
        @endif

        <form id="logout-f" action="{{ route('logout') }}" method="POST" style="display:none">@csrf</form>

        <div class="form-footer">
            Acceso restringido a personal autorizado de SISTCO<br>
            ¿Problemas? <a href="{{ route('soporte.publico') }}" class="btn btn-primary">
    Contactar soporte
</a>
        </div>
    </div>

</div>
</body>
</html>