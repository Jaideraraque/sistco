@extends('sistco.layout')
@section('title', 'Mi Perfil')
@section('page-title', 'Mi Perfil')

@section('content')

@php
$usuario = auth()->user();
$rol     = $usuario->role?->nombre ?? null;
$rolColores = [
    'gerente'        => ['bg'=>'#E6F5FC','color'=>'#0099D6'],
    'administrativo' => ['bg'=>'#E8F5E9','color'=>'#27AE60'],
    'admin_sistema'  => ['bg'=>'#F5F0FF','color'=>'#6C3483'],
];
$rc = $rolColores[$rol] ?? ['bg'=>'#F1EFE8','color'=>'#5F5E5A'];
$rolLabel = match($rol) {
    'gerente'        => 'Gerente',
    'admin_sistema'  => 'Administrador del Sistema',
    'administrativo' => 'Administrativo',
    default          => 'Sin rol asignado',
};
@endphp

{{-- Alertas --}}
@if(session('success'))
<div style="background:#E8F5E9;border:1px solid #A9DFBF;color:#1E8449;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px">
    ✅ {{ session('success') }}
</div>
@endif
@if(session('error'))
<div style="background:#FDEDEC;border:1px solid #F5C6CB;color:#A32D2D;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px">
    ⚠️ {{ session('error') }}
</div>
@endif

<div style="display:grid;grid-template-columns:280px 1fr;gap:16px;max-width:960px">

    {{-- Columna izquierda --}}
    <div style="display:flex;flex-direction:column;gap:16px">

        {{-- Tarjeta foto --}}
        <div style="background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:24px;text-align:center">
            {{-- Avatar --}}
            <div style="margin-bottom:16px">
                @if($usuario->foto && file_exists(public_path('fotos/' . $usuario->foto)))
                    <img src="{{ asset('fotos/' . $usuario->foto) }}"
                        style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid #0099D6;margin:0 auto;display:block">
                @else
                    <div style="width:90px;height:90px;border-radius:50%;background:linear-gradient(135deg,#0099D6,#005F8A);color:#fff;display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:700;margin:0 auto;border:3px solid #E6F5FC">
                        {{ strtoupper(substr($usuario->name, 0, 2)) }}
                    </div>
                @endif
            </div>

            <div style="font-size:16px;font-weight:700;color:#1a1a2e;margin-bottom:4px">{{ $usuario->name }}</div>
            <div style="font-size:12px;color:#888;margin-bottom:6px">{{ $usuario->email }}</div>
            <div style="font-size:12px;color:#555;margin-bottom:8px">{{ $rolLabel }}</div>
            @if($usuario->telefono)
            <div style="font-size:12px;color:#888;margin-bottom:8px">📞 {{ $usuario->telefono }}</div>
            @endif

            <span style="background:{{ $rc['bg'] }};color:{{ $rc['color'] }};padding:4px 14px;border-radius:20px;font-size:11px;font-weight:600">
                {{ $rolLabel }}
            </span>

            <div style="margin-top:8px;font-size:10px;color:#aaa">
                Miembro desde {{ $usuario->created_at->format('d/m/Y') }}
            </div>

            {{-- Subir foto --}}
            <div style="margin-top:16px;padding-top:16px;border-top:1px solid #f0f0f0">
                <form method="POST" action="{{ route('perfil.foto') }}" enctype="multipart/form-data">
                    @csrf
                    <label style="display:block;font-size:11px;color:#888;margin-bottom:6px">Foto de perfil</label>
                    <input type="file" name="foto" accept="image/*" id="inputFoto"
                        style="display:none" onchange="this.form.submit()">
                    <button type="button" onclick="document.getElementById('inputFoto').click()"
                        style="width:100%;background:#F0F4F8;border:1px solid #e0e0e0;border-radius:7px;padding:7px;font-size:12px;cursor:pointer;color:#555">
                        📷 Subir foto
                    </button>
                    <div style="font-size:10px;color:#aaa;margin-top:4px">JPG o PNG · Máx 2MB</div>
                </form>
                @if($usuario->foto)
                <form method="POST" action="{{ route('perfil.foto') }}" style="margin-top:6px">
                    @csrf @method('PUT')
                    <input type="hidden" name="eliminar" value="1">
                    <button type="submit" onclick="return confirm('¿Eliminar foto de perfil?')"
                        style="width:100%;background:#fff;border:1px solid #f0f0f0;border-radius:7px;padding:5px;font-size:11px;cursor:pointer;color:#E74C3C">
                        🗑 Quitar foto
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- Estado de la cuenta --}}
        <div style="background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:16px">
            <div style="font-size:13px;font-weight:600;color:#1a1a2e;margin-bottom:12px">Estado de la cuenta</div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:0.5px solid #f5f5f5">
                <span style="font-size:12px;color:#888">Estado</span>
                <span style="background:#E8F5E9;color:#27AE60;padding:2px 10px;border-radius:20px;font-size:10px;font-weight:600">● Activo</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:0.5px solid #f5f5f5">
                <span style="font-size:12px;color:#888">Cargo</span>
                <span style="font-size:12px;font-weight:600;color:{{ $rc['color'] }}">{{ $rolLabel }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0">
                <span style="font-size:12px;color:#888">Miembro desde</span>
                <span style="font-size:11px;color:#555">{{ $usuario->created_at->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>

    {{-- Columna derecha --}}
    <div style="display:flex;flex-direction:column;gap:16px">

        {{-- Información personal --}}
        <div style="background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:24px">
            <div style="font-size:14px;font-weight:700;color:#1a1a2e;margin-bottom:16px">
                👤 Información personal
            </div>
            <form method="POST" action="{{ route('perfil.actualizar') }}">
                @csrf @method('PUT')
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
                    <div>
                        <label style="font-size:11px;color:#888;display:block;margin-bottom:4px">Nombre completo *</label>
                        <input type="text" name="name" required value="{{ old('name', $usuario->name) }}"
                            style="width:100%;border:1px solid #e0e0e0;border-radius:7px;padding:8px 12px;font-size:13px;outline:none">
                        @error('name')<div style="color:#E74C3C;font-size:11px;margin-top:3px">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label style="font-size:11px;color:#888;display:block;margin-bottom:4px">Correo electrónico *</label>
                        <input type="email" name="email" required value="{{ old('email', $usuario->email) }}"
                            style="width:100%;border:1px solid #e0e0e0;border-radius:7px;padding:8px 12px;font-size:13px;outline:none">
                        @error('email')<div style="color:#E74C3C;font-size:11px;margin-top:3px">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div style="margin-bottom:16px">
                    <label style="font-size:11px;color:#888;display:block;margin-bottom:4px">Teléfono de contacto</label>
                    <input type="text" name="telefono" value="{{ old('telefono', $usuario->telefono) }}"
                        placeholder="Ej: 300 123 4567"
                        style="width:100%;border:1px solid #e0e0e0;border-radius:7px;padding:8px 12px;font-size:13px;outline:none">
                </div>
                {{-- Cargo — solo lectura --}}
                <div style="margin-bottom:16px;padding:10px 14px;background:#F8F8F8;border-radius:7px;border:1px solid #f0f0f0">
                    <div style="font-size:11px;color:#888;margin-bottom:2px">Cargo</div>
                    <div style="font-size:13px;font-weight:600;color:{{ $rc['color'] }}">{{ $rolLabel }}</div>
                    <div style="font-size:10px;color:#aaa;margin-top:2px">Asignado por el administrador del sistema</div>
                </div>
                <button type="submit"
                    style="background:#0099D6;color:#fff;border:none;border-radius:7px;padding:9px 24px;font-size:13px;font-weight:600;cursor:pointer">
                    Guardar cambios
                </button>
            </form>
        </div>

        {{-- Cambiar contraseña --}}
        <div style="background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:24px">
            <div style="font-size:14px;font-weight:700;color:#1a1a2e;margin-bottom:16px">
                🔒 Cambiar contraseña
            </div>
            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                @livewire('profile.update-password-form')
            @endif
        </div>

    </div>
</div>

@endsection