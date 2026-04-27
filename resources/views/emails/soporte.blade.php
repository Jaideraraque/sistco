 
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Soporte SISTCO</title>
</head>
<body style="margin:0;padding:0;background:#F0F4F8;font-family:Arial,sans-serif">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#F0F4F8;padding:40px 20px">
  <tr>
    <td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08)">

        {{-- Header --}}
        <tr>
          <td style="background:linear-gradient(135deg,#0099D6 0%,#005f8a 100%);padding:32px 40px">
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td>
                  <div style="font-size:20px;font-weight:700;color:#fff;letter-spacing:0.5px">SISTCO-ML</div>
                  <div style="font-size:12px;color:rgba(255,255,255,0.7);margin-top:2px">Sistema de Análisis Predictivo</div>
                </td>
                <td align="right">
                  @php
                    $colorPrioridad = match($prioridad) {
                        'alta'  => '#E24B4A',
                        'media' => '#F39C12',
                        default => '#27AE60',
                    };
                  @endphp
                  <span style="background:rgba(255,255,255,0.15);color:#fff;padding:6px 14px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:1px">
                    PRIORIDAD {{ strtoupper($prioridad) }}
                  </span>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        {{-- Título --}}
        <tr>
          <td style="padding:32px 40px 0">
            <div style="font-size:11px;font-weight:700;color:#0099D6;letter-spacing:1.5px;margin-bottom:8px">NUEVO TICKET DE SOPORTE</div>
            <div style="font-size:22px;font-weight:700;color:#1a1a2e;margin-bottom:4px">{{ $asunto }}</div>
            <div style="font-size:12px;color:#aaa">{{ $fecha }}</div>
          </td>
        </tr>

        {{-- Datos del usuario --}}
        <tr>
          <td style="padding:24px 40px 0">
            <table width="100%" cellpadding="0" cellspacing="0" style="background:#F8FAFC;border-radius:10px;overflow:hidden;border:1px solid #eee">
              <tr>
                <td style="padding:16px 20px;border-bottom:1px solid #f0f0f0">
                  <div style="font-size:10px;color:#aaa;font-weight:700;letter-spacing:0.8px;margin-bottom:4px">USUARIO</div>
                  <div style="font-size:14px;color:#1a1a2e;font-weight:600">{{ $nombreUsuario }}</div>
                </td>
                <td style="padding:16px 20px;border-bottom:1px solid #f0f0f0">
                  <div style="font-size:10px;color:#aaa;font-weight:700;letter-spacing:0.8px;margin-bottom:4px">CORREO</div>
                  <div style="font-size:14px;color:#0099D6">{{ $correoUsuario }}</div>
                </td>
              </tr>
              <tr>
                <td style="padding:16px 20px">
                  <div style="font-size:10px;color:#aaa;font-weight:700;letter-spacing:0.8px;margin-bottom:4px">ROL</div>
                  <div style="font-size:14px;color:#1a1a2e;font-weight:600">{{ ucfirst($rolUsuario) }}</div>
                </td>
                <td style="padding:16px 20px">
                  <div style="font-size:10px;color:#aaa;font-weight:700;letter-spacing:0.8px;margin-bottom:4px">PÁGINA ORIGEN</div>
                  <div style="font-size:13px;color:#888">{{ $paginaOrigen }}</div>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        {{-- Mensaje --}}
        <tr>
          <td style="padding:24px 40px 0">
            <div style="font-size:10px;color:#aaa;font-weight:700;letter-spacing:0.8px;margin-bottom:12px">MENSAJE</div>
            <div style="background:#F8FAFC;border-left:3px solid #0099D6;border-radius:0 10px 10px 0;padding:16px 20px;font-size:13px;color:#444;line-height:1.8">
              {{ $mensaje }}
            </div>
          </td>
        </tr>

        {{-- Prioridad visual --}}
        <tr>
          <td style="padding:24px 40px 0">
            <table cellpadding="0" cellspacing="0">
              <tr>
                <td style="background:{{ $colorPrioridad }}1A;border:1px solid {{ $colorPrioridad }}44;border-radius:8px;padding:10px 16px">
                  <span style="font-size:12px;font-weight:700;color:{{ $colorPrioridad }}">
                    ● Prioridad {{ $prioridad }} — requiere atención {{ $prioridad === 'alta' ? 'inmediata' : ($prioridad === 'media' ? 'pronto' : 'cuando sea posible') }}
                  </span>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        {{-- Footer --}}
        <tr>
          <td style="padding:32px 40px;margin-top:24px">
            <div style="border-top:1px solid #f0f0f0;padding-top:24px;text-align:center">
              <div style="font-size:11px;color:#ccc;line-height:1.8">
                SISTCO Sistemas y Comunicaciones SAS<br>
                Sistema de Análisis Predictivo · v1.0 · © 2026 · Santander, Colombia<br>
                Este correo fue generado automáticamente desde el sistema.
              </div>
            </div>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>

</body>
</html>