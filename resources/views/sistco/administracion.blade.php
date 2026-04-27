@extends('sistco.layout')
@section('title', 'Administración')
@section('page-title', 'Administración')

@section('content')

@php
  $rolColores = [
    'gerente'        => ['bg'=>'#E6F5FC','color'=>'#0099D6'],
    'administrativo' => ['bg'=>'#E8F5E9','color'=>'#27AE60'],
    'admin_sistema'  => ['bg'=>'#F5F0FF','color'=>'#6C3483'],
  ];
@endphp

{{-- Alertas --}}
@if(session('success'))
<div style="background:#E8F5E9;border:1px solid #A9DFBF;color:#1E8449;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;display:flex;align-items:center;gap:8px">
    ✅ {{ session('success') }}
</div>
@endif
@if(session('error'))
<div style="background:#FDEDEC;border:1px solid #F5C6CB;color:#A32D2D;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;display:flex;align-items:center;gap:8px">
    ⚠️ {{ session('error') }}
</div>
@endif

{{-- KPIs --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px">
    <div style="background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:14px 16px;border-left:4px solid #0099D6">
        <div style="font-size:11px;color:#888;margin-bottom:6px">Total usuarios</div>
        <div style="font-size:24px;font-weight:700;color:#1a1a2e">{{ $usuarios->count() }}</div>
    </div>
    <div style="background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:14px 16px;border-left:4px solid #27AE60">
        <div style="font-size:11px;color:#888;margin-bottom:6px">Usuarios activos</div>
        <div style="font-size:24px;font-weight:700;color:#27AE60">{{ $usuarios->where('activo', true)->count() }}</div>
    </div>
    <div style="background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:14px 16px;border-left:4px solid #E74C3C">
        <div style="font-size:11px;color:#888;margin-bottom:6px">Usuarios inactivos</div>
        <div style="font-size:24px;font-weight:700;color:#E74C3C">{{ $usuarios->where('activo', false)->count() }}</div>
    </div>
    <div style="background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:14px 16px;border-left:4px solid #27AE60">
        <div style="font-size:11px;color:#888;margin-bottom:6px">Total clientes en BD</div>
        <div style="font-size:24px;font-weight:700;color:#27AE60">{{ $totalClientes }}</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:16px">

    {{-- Tabla de usuarios --}}
    <div style="background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:16px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
            <div style="font-size:15px;font-weight:700;color:#1a1a2e">Usuarios del sistema</div>
        </div>
        <table style="width:100%;border-collapse:collapse;font-size:12px">
            <thead>
                <tr style="background:#F0F4F8">
                    <th style="padding:10px 12px;text-align:left;color:#555;font-weight:600;font-size:11px">Usuario</th>
                    <th style="padding:10px 12px;text-align:left;color:#555;font-weight:600;font-size:11px">Correo</th>
                    <th style="padding:10px 12px;text-align:left;color:#555;font-weight:600;font-size:11px">Rol</th>
                    <th style="padding:10px 12px;text-align:center;color:#555;font-weight:600;font-size:11px">Estado</th>
                    <th style="padding:10px 12px;text-align:left;color:#555;font-weight:600;font-size:11px">Registro</th>
                    <th style="padding:10px 12px;text-align:center;color:#555;font-weight:600;font-size:11px">Acciones</th>
                 </tr>
            </thead>
            <tbody>
                @forelse($usuarios as $usuario)
                @php
                    $rc = $rolColores[$usuario->role?->nombre] ?? ['bg'=>'#F1EFE8','color'=>'#5F5E5A'];
                @endphp
                <tr style="border-bottom:1px solid #F0F4F8;{{ !$usuario->activo ? 'opacity:0.6;' : '' }}">
                    <td style="padding:10px 12px">
                        <div style="display:flex;align-items:center;gap:8px">
                            @if($usuario->foto && file_exists(public_path('fotos/' . $usuario->foto)))
                                <img src="{{ asset('fotos/' . $usuario->foto) }}"
                                    style="width:30px;height:30px;border-radius:50%;object-fit:cover;flex-shrink:0">
                            @else
                                <div style="width:30px;height:30px;border-radius:50%;background:{{ $usuario->activo ? '#0099D6' : '#ccc' }};color:#fff;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;flex-shrink:0">
                                    {{ strtoupper(substr($usuario->name, 0, 2)) }}
                                </div>
                            @endif
                            <div>
                                <div style="font-weight:600;color:#1a1a2e;font-size:12px">{{ $usuario->name }}</div>
                                @if($usuario->id === auth()->id())
                                <div style="font-size:10px;color:#0099D6">Tú</div>
                                @endif
                            </div>
                        </div>
                     </td>
                    <td style="padding:10px 12px;color:#555;font-size:12px">{{ $usuario->email }}</td>
                    <td style="padding:10px 12px">
                        @if($usuario->id !== auth()->id())
                        <form method="POST" action="{{ route('usuarios.rol', $usuario->id) }}">
                            @csrf @method('PUT')
                            <select name="role_id" onchange="this.form.submit()"
                                style="border:1px solid #e0e0e0;border-radius:6px;padding:4px 8px;font-size:11px;color:#555;background:#fff;cursor:pointer">
                                <option value="">Sin rol</option>
                                @foreach($roles as $rol)
                                <option value="{{ $rol->id }}" {{ $usuario->role_id == $rol->id ? 'selected' : '' }}>
                                    {{ ucfirst($rol->nombre) }}
                                </option>
                                @endforeach
                            </select>
                        </form>
                        @else
                        <span style="background:{{ $rc['bg'] }};color:{{ $rc['color'] }};padding:3px 10px;border-radius:20px;font-size:10px;font-weight:600">
                            {{ ucfirst($usuario->role?->nombre ?? 'Sin rol') }}
                        </span>
                        @endif
                     </td>
                    <td style="padding:10px 12px;text-align:center">
                        @if($usuario->activo)
                        <span style="background:#E8F5E9;color:#27AE60;padding:3px 10px;border-radius:20px;font-size:10px;font-weight:600">● Activo</span>
                        @else
                        <span style="background:#FDEDEC;color:#E74C3C;padding:3px 10px;border-radius:20px;font-size:10px;font-weight:600">● Inactivo</span>
                        @endif
                     </td>
                    <td style="padding:10px 12px;color:#aaa;font-size:11px">{{ $usuario->created_at->format('d/m/Y') }}</td>
                    <td style="padding:10px 12px;text-align:center">
                        @if($usuario->id !== auth()->id())
                        <div style="display:flex;gap:6px;justify-content:center">
                            {{-- Editar --}}
                            <button type="button"
                                style="border:none;border-radius:6px;padding:5px 10px;font-size:11px;cursor:pointer;font-weight:600;background:#E6F5FC;color:#0099D6"
                                onclick="abrirModal({{ $usuario->id }}, '{{ addslashes($usuario->name) }}', '{{ $usuario->email }}', '{{ $usuario->telefono ?? '' }}')">
                                ✏️ Editar
                            </button>
                            {{-- Activar/Desactivar --}}
                            <form method="POST" action="{{ route('usuarios.toggle', $usuario->id) }}">
                                @csrf @method('PUT')
                                <button type="submit"
                                    style="border:none;border-radius:6px;padding:5px 10px;font-size:11px;cursor:pointer;font-weight:600;background:{{ $usuario->activo ? '#FEF9E7' : '#E8F5E9' }};color:{{ $usuario->activo ? '#F39C12' : '#27AE60' }}"
                                    onclick="return confirm('¿{{ $usuario->activo ? 'Desactivar' : 'Activar' }} al usuario {{ $usuario->name }}?')">
                                    {{ $usuario->activo ? '⏸ Desactivar' : '▶ Activar' }}
                                </button>
                            </form>
                            {{-- Eliminar --}}
                            <form method="POST" action="{{ route('usuarios.eliminar', $usuario->id) }}">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    style="border:none;border-radius:6px;padding:5px 10px;font-size:11px;cursor:pointer;font-weight:600;background:#FDEDEC;color:#E74C3C"
                                    onclick="return confirm('¿Eliminar permanentemente al usuario {{ $usuario->name }}? Esta acción no se puede deshacer.')">
                                    🗑 Eliminar
                                </button>
                            </form>
                        </div>
                        @else
                        <span style="font-size:11px;color:#aaa">—</span>
                        @endif
                     </td>
                 </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding:30px;text-align:center;color:#aaa">No hay usuarios registrados</td>
                </tr>
                @endforelse
            </tbody>
         </table>
    </div>

    {{-- Panel derecho --}}
    <div style="display:flex;flex-direction:column;gap:16px">

        {{-- Formulario crear usuario --}}
        <div style="background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:16px">
            <div style="font-size:14px;font-weight:700;color:#1a1a2e;margin-bottom:14px">
                ➕ Crear nuevo usuario
            </div>
            <form method="POST" action="{{ route('usuarios.crear') }}">
                @csrf
                <div style="margin-bottom:10px">
                    <label style="font-size:11px;color:#888;display:block;margin-bottom:4px">Nombre completo *</label>
                    <input type="text" name="name" required placeholder="Ej: María Oviedo"
                        value="{{ old('name') }}"
                        style="width:100%;border:1px solid #e0e0e0;border-radius:7px;padding:8px 12px;font-size:13px;outline:none">
                    @error('name')<div style="color:#E74C3C;font-size:11px;margin-top:3px">{{ $message }}</div>@enderror
                </div>
                <div style="margin-bottom:10px">
                    <label style="font-size:11px;color:#888;display:block;margin-bottom:4px">Correo electrónico *</label>
                    <input type="email" name="email" required placeholder="correo@empresa.com"
                        value="{{ old('email') }}"
                        style="width:100%;border:1px solid #e0e0e0;border-radius:7px;padding:8px 12px;font-size:13px;outline:none">
                    @error('email')<div style="color:#E74C3C;font-size:11px;margin-top:3px">{{ $message }}</div>@enderror
                </div>
                <div style="margin-bottom:10px">
                    <label style="font-size:11px;color:#888;display:block;margin-bottom:4px">Teléfono</label>
                    <input type="text" name="telefono" placeholder="Ej: 300 123 4567"
                        value="{{ old('telefono') }}"
                        style="width:100%;border:1px solid #e0e0e0;border-radius:7px;padding:8px 12px;font-size:13px;outline:none">
                </div>
                <div style="margin-bottom:10px">
                    <label style="font-size:11px;color:#888;display:block;margin-bottom:4px">Contraseña *</label>
                    <input type="password" name="password" required placeholder="Mínimo 8 caracteres"
                        style="width:100%;border:1px solid #e0e0e0;border-radius:7px;padding:8px 12px;font-size:13px;outline:none">
                    @error('password')<div style="color:#E74C3C;font-size:11px;margin-top:3px">{{ $message }}</div>@enderror
                </div>
                <div style="margin-bottom:14px">
                    <label style="font-size:11px;color:#888;display:block;margin-bottom:4px">Rol *</label>
                    <select name="role_id" required
                        style="width:100%;border:1px solid #e0e0e0;border-radius:7px;padding:8px 12px;font-size:13px;outline:none;background:#fff">
                        <option value="">Seleccionar rol...</option>
                        @foreach($roles as $rol)
                        <option value="{{ $rol->id }}" {{ old('role_id') == $rol->id ? 'selected' : '' }}>
                            {{ ucfirst($rol->nombre) }} — {{ $rol->descripcion }}
                        </option>
                        @endforeach
                    </select>
                    @error('role_id')<div style="color:#E74C3C;font-size:11px;margin-top:3px">{{ $message }}</div>@enderror
                </div>
                <button type="submit"
                    style="width:100%;background:#0099D6;color:#fff;border:none;border-radius:7px;padding:9px;font-size:13px;font-weight:600;cursor:pointer">
                    Crear usuario
                </button>
            </form>
        </div>

        {{-- Roles del sistema --}}
        <div style="background:#fff;border:0.5px solid #e8e8e8;border-radius:10px;padding:16px">
            <div style="font-size:14px;font-weight:700;color:#1a1a2e;margin-bottom:12px">Roles del sistema</div>
            @foreach($roles as $rol)
            @php $rc = $rolColores[$rol->nombre] ?? ['bg'=>'#F1EFE8','color'=>'#5F5E5A']; @endphp
            <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:0.5px solid #f5f5f5">
                <span style="background:{{ $rc['bg'] }};color:{{ $rc['color'] }};padding:3px 10px;border-radius:20px;font-size:10px;font-weight:600;white-space:nowrap">
                    {{ ucfirst($rol->nombre) }}
                </span>
                <span style="font-size:11px;color:#888">{{ $rol->descripcion }}</span>
            </div>
            @endforeach
        </div>

    </div>
</div>

{{-- Modal editar usuario --}}
<div id="modalEditar" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:12px;padding:24px;width:440px;box-shadow:0 10px 40px rgba(0,0,0,0.2)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
            <div style="font-size:15px;font-weight:700;color:#1a1a2e">✏️ Editar usuario</div>
            <button onclick="cerrarModal()" style="border:none;background:none;font-size:18px;cursor:pointer;color:#888">✕</button>
        </div>
        <form id="formEditar" method="POST">
            @csrf @method('PUT')
            <div style="margin-bottom:12px">
                <label style="font-size:11px;color:#888;display:block;margin-bottom:4px">Nombre completo *</label>
                <input type="text" id="editNombre" name="name" required
                    style="width:100%;border:1px solid #e0e0e0;border-radius:7px;padding:8px 12px;font-size:13px;outline:none">
            </div>
            <div style="margin-bottom:12px">
                <label style="font-size:11px;color:#888;display:block;margin-bottom:4px">Correo electrónico *</label>
                <input type="email" id="editEmail" name="email" required
                    style="width:100%;border:1px solid #e0e0e0;border-radius:7px;padding:8px 12px;font-size:13px;outline:none">
            </div>
            <div style="margin-bottom:20px">
                <label style="font-size:11px;color:#888;display:block;margin-bottom:4px">Teléfono</label>
                <input type="text" id="editTelefono" name="telefono"
                    placeholder="Ej: 300 123 4567"
                    style="width:100%;border:1px solid #e0e0e0;border-radius:7px;padding:8px 12px;font-size:13px;outline:none">
            </div>
            <div style="display:flex;gap:8px">
                <button type="submit"
                    style="flex:1;background:#0099D6;color:#fff;border:none;border-radius:7px;padding:9px;font-size:13px;font-weight:600;cursor:pointer">
                    Guardar cambios
                </button>
                <button type="button" onclick="cerrarModal()"
                    style="flex:1;background:#f0f0f0;color:#555;border:none;border-radius:7px;padding:9px;font-size:13px;cursor:pointer">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModal(id, nombre, email, telefono) {
    document.getElementById('editNombre').value   = nombre;
    document.getElementById('editEmail').value    = email;
    document.getElementById('editTelefono').value = telefono;
    document.getElementById('formEditar').action  = '/usuarios/' + id + '/editar';
    document.getElementById('modalEditar').style.display = 'flex';
}
function cerrarModal() {
    document.getElementById('modalEditar').style.display = 'none';
}
document.getElementById('modalEditar').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});
</script>

@endsection