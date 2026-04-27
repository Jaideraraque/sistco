<x-guest-layout>
@push('head')
<title>SISTCO-ML — Restablecer Contraseña</title>
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><circle cx='16' cy='16' r='16' fill='%230099D6'/><circle cx='16' cy='16' r='11' stroke='white' stroke-width='2' fill='none'/><path d='M9 16 Q16 9 23 16 Q16 23 9 16Z' stroke='white' stroke-width='1.5' fill='none'/></svg>">
@endpush<style>
*{box-sizing:border-box;margin:0;padding:0}
.min-h-screen{display:flex!important;align-items:center!important;justify-content:center!important;background:linear-gradient(135deg,#0099D6 0%,#005f8a 50%,#1a1a2e 100%)!important;min-height:100vh!important;padding:20px!important}
.min-h-screen>div{display:none!important}
.reset-wrapper{display:flex;width:820px;height:560px;border-radius:20px;overflow:hidden;box-shadow:0 25px 60px rgba(0,0,0,0.35);position:fixed;top:50%;left:50%;transform:translate(-50%,-50%)}
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
.form-panel{flex:1;background:#fff;display:flex;flex-direction:column;justify-content:center;padding:48px 48px}
.form-title{font-size:22px;font-weight:700;color:#1a1a2e;margin-bottom:6px}
.form-subtitle{font-size:12px;color:#aaa;margin-bottom:24px;line-height:1.6}
.form-group{margin-bottom:16px}
.form-label{font-size:12px;font-weight:600;color:#555;margin-bottom:7px;display:block}
.input-wrap{position:relative}
.input-icon{position:absolute;left:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#bbb}
.form-input{width:100%;padding:11px 12px 11px 38px;border:1px solid #e8e8e8;border-radius:10px;font-size:13px;color:#1a1a2e;background:#FAFAFA;outline:none;transition:all .2s}
.form-input:focus{border-color:#0099D6;background:#fff;box-shadow:0 0 0 3px rgba(0,153,214,0.08)}
.form-input::placeholder{color:#ccc}
.btn-submit{width:100%;padding:13px;background:linear-gradient(135deg,#0099D6,#0077AA);border:none;border-radius:10px;color:#fff;font-size:14px;font-weight:600;cursor:pointer;transition:all .2s;box-shadow:0 4px 15px rgba(0,153,214,0.3);margin-top:4px}
.btn-submit:hover{background:linear-gradient(135deg,#0088C0,#005f8a);transform:translateY(-1px)}
.alert-error{background:#FDEDEC;border:1px solid #f5c6c6;border-radius:10px;padding:12px 14px;margin-bottom:16px;font-size:12px;color:#A32D2D}
.req-hint{font-size:10px;color:#aaa;margin-top:4px}
</style>

<div class="reset-wrapper">
    {{-- Panel izquierdo --}}
    <div class="brand-panel">
        <div class="brand-logo">
            <img src="{{ asset('images/logo_sistco.png') }}" alt="SISTCO">
        </div>
        <div class="brand-middle">
            <div class="brand-headline">
                Crea tu nueva<br>
                <span>contraseña segura</span>
            </div>
            <div class="brand-desc">
                Elige una contraseña segura de al menos 8 caracteres. Una vez confirmada podrás acceder al sistema normalmente.
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
        <div class="form-title">Restablecer contraseña</div>
        <div class="form-subtitle">Ingresa tu correo y tu nueva contraseña para recuperar el acceso.</div>

        @if($errors->any())
        <div class="alert-error">
            ⚠️ {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="form-group">
                <label class="form-label">Correo electrónico</label>
                <div class="input-wrap">
                    <svg class="input-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="2" y="4" width="12" height="9" rx="1"/><path d="M2 4l6 5 6-5"/>
                    </svg>
                    <input class="form-input" type="email" name="email"
                           value="{{ old('email', $request->email) }}"
                           placeholder="correo@sistco.com.co" required autofocus autocomplete="username">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Nueva contraseña</label>
                <div class="input-wrap">
                    <svg class="input-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="7" width="10" height="8" rx="1"/><path d="M5 7V5a3 3 0 016 0v2"/>
                    </svg>
                    <input class="form-input" type="password" name="password"
                           placeholder="Mínimo 8 caracteres" required autocomplete="new-password">
                </div>
                <div class="req-hint">Mínimo 8 caracteres</div>
            </div>

            <div class="form-group">
                <label class="form-label">Confirmar nueva contraseña</label>
                <div class="input-wrap">
                    <svg class="input-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="7" width="10" height="8" rx="1"/><path d="M5 7V5a3 3 0 016 0v2"/>
                    </svg>
                    <input class="form-input" type="password" name="password_confirmation"
                           placeholder="Repite la contraseña" required autocomplete="new-password">
                </div>
            </div>

            <button type="submit" class="btn-submit">
                Restablecer contraseña
            </button>
        </form>
    </div>
</div>
</x-guest-layout>
