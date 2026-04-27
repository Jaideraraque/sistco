<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SISTCO-ML — @yield('title')</title>
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><circle cx='16' cy='16' r='16' fill='%230099D6'/><circle cx='16' cy='16' r='11' stroke='white' stroke-width='2' fill='none'/><path d='M9 16 Q16 9 23 16 Q16 23 9 16Z' stroke='white' stroke-width='1.5' fill='none'/></svg>">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{background:#E8EDF2;min-height:100vh;font-family:Arial,sans-serif}
.app{display:flex;min-height:100vh;background:#F0F4F8;font-size:13px}
.sidebar{width:200px;background:#fff;border-right:0.5px solid #e8e8e8;display:flex;flex-direction:column;flex-shrink:0;position:fixed;height:100vh;top:0;left:0;z-index:100}
.logo{padding:20px 16px 16px;border-bottom:0.5px solid #f0f0f0}
.logo-top{display:flex;align-items:center;gap:8px}
.logo-circle{width:28px;height:28px;border-radius:50%;background:#0099D6;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.logo-name{font-size:15px;font-weight:700;color:#0099D6;letter-spacing:0.5px}
.logo-sub{font-size:10px;color:#808080;margin-top:2px;letter-spacing:0.3px}
.nav{flex:1;padding:12px 0;overflow-y:auto}
.nav-section{padding:4px 0 8px}
.nav-label{font-size:10px;color:#aaa;padding:0 16px 6px;letter-spacing:0.8px;font-weight:500}
.nav-item{display:flex;align-items:center;gap:10px;padding:8px 16px;cursor:pointer;color:#555;font-size:13px;text-decoration:none;transition:all .15s}
.nav-item:hover{background:#f5f5f5;color:#0099D6}
.nav-item.active{background:#E6F5FC;color:#0099D6;font-weight:500;border-right:2px solid #0099D6}
.nav-item svg{width:14px;height:14px;flex-shrink:0}
.nav-item .badge-new{background:#0099D6;color:#fff;font-size:9px;padding:1px 5px;border-radius:8px;margin-left:auto;font-weight:600}
.nav-divider{height:0.5px;background:#f0f0f0;margin:4px 0}
.rol-chip{margin:8px 16px 4px;padding:5px 10px;border-radius:6px;font-size:10px;font-weight:600;text-align:center}
.chip-gerente{background:#E6F5FC;color:#0099D6;border:1px solid #B3D9F0}
.chip-admin_sistema{background:#F5F0FF;color:#6C3483;border:1px solid #D4C5F0}
.chip-administrativo{background:#E8F5E9;color:#27AE60;border:1px solid #A9DFBF}
.chip-sin_rol{background:#FEF9E7;color:#F39C12;border:1px solid #FDEBD0}
.support-box{margin:12px;padding:12px;background:#E6F5FC;border-radius:8px;text-align:center}
.support-box p{font-size:11px;color:#0099D6;margin-bottom:8px;line-height:1.4}
.support-btn{background:#0099D6;color:#fff;border:none;padding:6px 12px;border-radius:6px;font-size:11px;cursor:pointer;width:100%}
.main{flex:1;display:flex;flex-direction:column;margin-left:200px;min-height:100vh}
.topbar{background:#fff;border-bottom:0.5px solid #e8e8e8;padding:0 20px;height:52px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;position:sticky;top:0;z-index:50}
.topbar-left{display:flex;align-items:center;gap:12px}
.topbar-title{font-size:15px;font-weight:600;color:#1a1a2e}
.date-chip{display:flex;align-items:center;gap:5px;background:#F0F4F8;padding:4px 10px;border-radius:20px;font-size:11px;color:#888}
.topbar-right{display:flex;align-items:center;gap:10px}
.avatar{width:32px;height:32px;border-radius:50%;background:#0099D6;color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700}
.content{flex:1;padding:20px;overflow-y:auto}
</style>
@livewireStyles
@vite(['resources/css/app.css', 'resources/js/app.js'])
@yield('styles')
</head>
<body>
<div class="app">
  <div class="sidebar">
    <div class="logo">
      <div class="logo-top">
        <div class="logo-circle">
          <svg viewBox="0 0 16 16" fill="none" width="16" height="16">
            <circle cx="8" cy="8" r="6" stroke="white" stroke-width="1.5"/>
            <path d="M4 8 Q8 4 12 8 Q8 12 4 8Z" stroke="white" stroke-width="1" fill="none"/>
          </svg>
        </div>
        <div>
          <div class="logo-name">SISTCO</div>
          <div class="logo-sub">SISTEMAS Y COMUNICACIONES</div>
        </div>
      </div>
    </div>

    @php
      $rol              = auth()->user()->role?->nombre ?? null;
      $esGerente        = $rol === 'gerente';
      $esAdminSistema   = $rol === 'admin_sistema';
      $esAdministrativo = $rol === 'administrativo';
      $chipClass = match($rol) {
          'gerente'        => 'chip-gerente',
          'admin_sistema'  => 'chip-admin_sistema',
          'administrativo' => 'chip-administrativo',
          default          => 'chip-sin_rol',
      };
      $rolLabel = match($rol) {
          'gerente'        => '👔 Gerente',
          'admin_sistema'  => '⚙️ Admin Sistema',
          'administrativo' => '📋 Administrativo',
          default          => '⚠️ Sin rol asignado',
      };
    @endphp

    <div class="rol-chip {{ $chipClass }}">{{ $rolLabel }}</div>

    <div class="nav">

      {{-- PRINCIPAL — todos --}}
      <div class="nav-section">
        <div class="nav-label">PRINCIPAL</div>
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
          <svg viewBox="0 0 16 16" fill="currentColor"><rect x="1" y="1" width="6" height="6" rx="1"/><rect x="9" y="1" width="6" height="6" rx="1"/><rect x="1" y="9" width="6" height="6" rx="1"/><rect x="9" y="9" width="6" height="6" rx="1"/></svg>
          Dashboard
        </a>
        <a href="{{ route('analisis') }}" class="nav-item {{ request()->routeIs('analisis') ? 'active' : '' }}">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 13L2 8M6 13L6 5M10 13L10 7M14 13L14 3"/></svg>
          Análisis
        </a>
        @if($esAdministrativo || $esAdminSistema)
        <a href="{{ route('carga-datos') }}" class="nav-item {{ request()->routeIs('carga-datos') ? 'active' : '' }}">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="2" width="10" height="13" rx="1"/><path d="M6 6h4M6 9h4M6 12h2"/></svg>
          Cargar datos
        </a>
        @endif
      </div>

      {{-- MÓDULOS IA --}}
      <div class="nav-divider"></div>
      <div class="nav-section">
        <div class="nav-label">MÓDULOS IA</div>

        {{-- Clasificación — todos los roles --}}
        <a href="{{ route('clasificacion') }}" class="nav-item {{ request()->routeIs('clasificacion') ? 'active' : '' }}">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 3L8 8L11 11"/><circle cx="8" cy="8" r="6"/></svg>
          Clasificación
        </a>

        {{-- Ingresos y Segmentación — Gerente y Admin Sistema --}}
        @if($esGerente || $esAdminSistema)
        <a href="{{ route('ingresos') }}" class="nav-item {{ request()->routeIs('ingresos') ? 'active' : '' }}">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 12L6 7L9 9L14 4"/></svg>
          Ingresos
        </a>
        <a href="{{ route('segmentacion') }}" class="nav-item {{ request()->routeIs('segmentacion') ? 'active' : '' }}">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="5" cy="8" r="3"/><circle cx="11" cy="8" r="3"/></svg>
          Segmentación
        </a>
        @endif

        {{-- Asistente IA — todos los roles --}}
        <a href="{{ route('asistente') }}" class="nav-item {{ request()->routeIs('asistente') ? 'active' : '' }}">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 3h12a1 1 0 011 1v7a1 1 0 01-1 1H5l-3 2V4a1 1 0 011-1z"/></svg>
          Asistente IA
          <span class="badge-new">BETA</span>
        </a>
      </div>

      {{-- SISTEMA --}}
      <div class="nav-divider"></div>
      <div class="nav-section">
        <div class="nav-label">SISTEMA</div>

        @if($esGerente || $esAdminSistema)
        <a href="{{ route('reportes') }}" class="nav-item {{ request()->routeIs('reportes') ? 'active' : '' }}">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="12" height="10" rx="1"/><path d="M5 7h6M5 10h4"/></svg>
          Reportes
        </a>
        @endif

        @if($esAdminSistema)
        <a href="{{ route('administracion') }}" class="nav-item {{ request()->routeIs('administracion') ? 'active' : '' }}">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="8" r="2.5"/><path d="M8 1v2M8 13v2M1 8h2M13 8h2M3.5 3.5l1.4 1.4M11.1 11.1l1.4 1.4M3.5 12.5l1.4-1.4M11.1 4.9l1.4-1.4"/></svg>
          Administración
        </a>
        @endif

        <a href="{{ route('logout') }}" class="nav-item"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 3H3a1 1 0 00-1 1v8a1 1 0 001 1h3M10 11l3-3-3-3M13 8H6"/></svg>
          Cerrar sesión
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none">
          @csrf
        </form>
      </div>
    </div>

    {{-- Botón de soporte actualizado con modal --}}
    <div style="margin:8px 12px;padding:8px 10px;background:#E6F5FC;border-radius:8px;text-align:center">
      <p style="font-size:10px;color:#0099D6;margin-bottom:6px;line-height:1.3">¿Necesitas ayuda?</p>
      <button onclick="document.getElementById('modal-soporte').style.display='flex'"
              class="support-btn" style="font-size:10px;padding:4px 10px">Soporte</button>
    </div>
  </div>

  <div class="main">
    <div class="topbar">
      <div class="topbar-left">
        <div class="topbar-title">@yield('page-title')</div>
        <div class="date-chip">
          <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="12" height="11" rx="1"/><path d="M5 2v2M11 2v2M2 7h12"/></svg>
          {{ now()->format('d/m/Y') }}
        </div>
      </div>
      <div class="topbar-right">
        @if(auth()->user()->foto && file_exists(public_path('fotos/' . auth()->user()->foto)))
            <img src="{{ asset('fotos/' . auth()->user()->foto) }}"
                style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:2px solid #E6F5FC">
        @else
            <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
        @endif
        <div>
          <div style="font-weight:600;color:#1a1a2e;font-size:12px">{{ auth()->user()->name }}</div>
          <div style="color:#aaa;font-size:11px">
            <a href="{{ route('profile.show') }}" style="color:#0099D6;text-decoration:none">Ver perfil</a>
          </div>
        </div>
      </div>
    </div>
    <div class="content">
      @yield('content')
    </div>
  </div>
</div>

{{-- Modal de soporte --}}
<div id="modal-soporte"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:999;align-items:center;justify-content:center;padding:20px">
  <div style="background:#fff;border-radius:20px;width:100%;max-width:500px;overflow:hidden;box-shadow:0 25px 60px rgba(0,0,0,0.35)">

    {{-- Header del modal --}}
    <div style="background:linear-gradient(135deg,#0099D6 0%,#005f8a 100%);padding:24px 28px;display:flex;align-items:center;justify-content:space-between">
      <div>
        <div style="font-size:16px;font-weight:700;color:#fff">Contactar soporte</div>
        <div style="font-size:11px;color:rgba(255,255,255,0.7);margin-top:2px">Te respondemos a la brevedad</div>
      </div>
      <button onclick="document.getElementById('modal-soporte').style.display='none'"
              style="background:rgba(255,255,255,0.15);border:none;border-radius:50%;width:32px;height:32px;cursor:pointer;color:#fff;font-size:18px;display:flex;align-items:center;justify-content:center">
        ×
      </button>
    </div>

    {{-- Éxito --}}
    @if(session('soporte_enviado'))
    <div style="padding:28px;text-align:center">
      <div style="width:56px;height:56px;border-radius:50%;background:#F0FFF4;border:1.5px solid #C0DD97;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
        <svg width="24" height="24" viewBox="0 0 16 16" fill="none" stroke="#27AE60" stroke-width="1.5"><path d="M3 8l3 3 7-7"/></svg>
      </div>
      <div style="font-size:18px;font-weight:700;color:#1a1a2e;margin-bottom:8px">¡Mensaje enviado!</div>
      <div style="font-size:13px;color:#888;line-height:1.6">Tu solicitud fue recibida. El equipo de soporte de SISTCO te contactará pronto.</div>
      <button onclick="document.getElementById('modal-soporte').style.display='none'"
              style="margin-top:20px;background:linear-gradient(135deg,#0099D6,#0077AA);color:#fff;border:none;padding:10px 28px;border-radius:10px;font-size:13px;font-weight:600;cursor:pointer">
        Cerrar
      </button>
    </div>
    @else

    {{-- Formulario --}}
    <form method="POST" action="{{ route('soporte.enviar') }}" style="padding:28px">
      @csrf

      {{-- Info del usuario --}}
      <div style="background:#F8FAFC;border-radius:10px;padding:12px 16px;margin-bottom:20px;display:flex;align-items:center;gap:12px">
        <div style="width:36px;height:36px;border-radius:50%;background:#0099D6;color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0">
          {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
        </div>
        <div>
          <div style="font-size:13px;font-weight:600;color:#1a1a2e">{{ auth()->user()->name }}</div>
          <div style="font-size:11px;color:#aaa">{{ auth()->user()->email }} · {{ ucfirst(auth()->user()->role?->nombre ?? 'Sin rol') }}</div>
        </div>
      </div>

      {{-- Asunto --}}
      <div style="margin-bottom:16px">
        <label style="font-size:12px;font-weight:600;color:#555;display:block;margin-bottom:6px">Asunto</label>
        <select name="asunto" required
                style="width:100%;padding:10px 12px;border:1px solid #e8e8e8;border-radius:10px;font-size:13px;color:#1a1a2e;background:#FAFAFA;outline:none">
          <option value="">Selecciona una categoría...</option>
          <option value="Problema técnico">Problema técnico</option>
          <option value="No puedo acceder al sistema">No puedo acceder al sistema</option>
          <option value="Error en el sistema">Error en el sistema</option>
          <option value="Solicitud de nuevo rol">Solicitud de nuevo rol</option>
          <option value="Error en modelo de IA">Error en modelo de IA</option>
          <option value="Otro">Otro</option>
        </select>
        @error('asunto')
          <div style="font-size:11px;color:#A32D2D;margin-top:4px">{{ $message }}</div>
        @enderror
      </div>

      {{-- Prioridad --}}
      <div style="margin-bottom:16px">
        <label style="font-size:12px;font-weight:600;color:#555;display:block;margin-bottom:6px">Prioridad</label>
        <div style="display:flex;gap:8px">
          @foreach(['baja' => ['#27AE60','#F0FFF4','#C0DD97'], 'media' => ['#F39C12','#FEF9E7','#FDEBD0'], 'alta' => ['#E24B4A','#FDEDEC','#f5c6c6']] as $p => $colores)
          <label style="flex:1;cursor:pointer">
            <input type="radio" name="prioridad" value="{{ $p }}" {{ $p === 'media' ? 'checked' : '' }}
                   style="display:none" class="radio-prioridad">
            <div class="prioridad-btn" data-p="{{ $p }}"
                 style="text-align:center;padding:8px;border-radius:8px;font-size:12px;font-weight:600;border:1.5px solid {{ $colores[2] }};background:{{ $colores[1] }};color:{{ $colores[0] }};transition:all .15s">
              {{ ucfirst($p) }}
            </div>
          </label>
          @endforeach
        </div>
        @error('prioridad')
          <div style="font-size:11px;color:#A32D2D;margin-top:4px">{{ $message }}</div>
        @enderror
      </div>

      {{-- Mensaje --}}
      <div style="margin-bottom:20px">
        <label style="font-size:12px;font-weight:600;color:#555;display:block;margin-bottom:6px">Mensaje</label>
        <textarea name="mensaje" required rows="4" placeholder="Describe el problema con el mayor detalle posible..."
                  style="width:100%;padding:10px 12px;border:1px solid #e8e8e8;border-radius:10px;font-size:13px;color:#1a1a2e;background:#FAFAFA;outline:none;resize:none;font-family:Arial,sans-serif"></textarea>
        @error('mensaje')
          <div style="font-size:11px;color:#A32D2D;margin-top:4px">{{ $message }}</div>
        @enderror
      </div>

      {{-- Botones --}}
      <div style="display:flex;gap:10px">
        <button type="button"
                onclick="document.getElementById('modal-soporte').style.display='none'"
                style="flex:1;padding:12px;background:#f5f5f5;border:1px solid #e8e8e8;border-radius:10px;font-size:13px;font-weight:600;color:#555;cursor:pointer">
          Cancelar
        </button>
        <button type="submit"
                style="flex:2;padding:12px;background:linear-gradient(135deg,#0099D6,#0077AA);border:none;border-radius:10px;font-size:13px;font-weight:600;color:#fff;cursor:pointer;box-shadow:0 4px 15px rgba(0,153,214,0.3)">
          Enviar mensaje
        </button>
      </div>
    </form>
    @endif

  </div>
</div>

<script>
  // Abrir modal si hay éxito
  @if(session('soporte_enviado'))
    document.getElementById('modal-soporte').style.display = 'flex';
  @endif

  // Cerrar modal al hacer clic fuera
  document.getElementById('modal-soporte').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
  });

  // Prioridad visual
  document.querySelectorAll('.radio-prioridad').forEach(radio => {
    radio.addEventListener('change', function() {
      document.querySelectorAll('.prioridad-btn').forEach(btn => {
        const p = btn.dataset.p;
        const colores = {
          baja:  { bg: '#F0FFF4', border: '#C0DD97', color: '#27AE60' },
          media: { bg: '#FEF9E7', border: '#FDEBD0', color: '#F39C12' },
          alta:  { bg: '#FDEDEC', border: '#f5c6c6', color: '#E24B4A' },
        };
        btn.style.background   = colores[p].bg;
        btn.style.borderColor  = colores[p].border;
        btn.style.color        = colores[p].color;
        btn.style.opacity      = '0.5';
        btn.style.transform    = 'scale(1)';
      });

      const selected = document.querySelector('.prioridad-btn[data-p="' + this.value + '"]');
      const coloresActivo = {
        baja:  { bg: '#C0DD97', border: '#27AE60', color: '#27AE60' },
        media: { bg: '#FAC775', border: '#F39C12', color: '#854F0B' },
        alta:  { bg: '#F09595', border: '#E24B4A', color: '#A32D2D' },
      };
      selected.style.background  = coloresActivo[this.value].bg;
      selected.style.borderColor = coloresActivo[this.value].border;
      selected.style.color       = coloresActivo[this.value].color;
      selected.style.opacity     = '1';
      selected.style.transform   = 'scale(1.04)';
    });
  });

  // Marcar "media" como activo por defecto al abrir
  document.querySelector('.prioridad-btn[data-p="media"]').style.background  = '#FAC775';
  document.querySelector('.prioridad-btn[data-p="media"]').style.borderColor = '#F39C12';
  document.querySelector('.prioridad-btn[data-p="media"]').style.color       = '#854F0B';
  document.querySelectorAll('.prioridad-btn[data-p="baja"], .prioridad-btn[data-p="alta"]').forEach(b => b.style.opacity = '0.5');
</script>

@livewireScripts
</body>
</html>