@extends('sistco.layout')

@section('title', 'Asistente IA')
@section('page-title', 'Asistente IA')

@section('styles')
<style>
.chat-wrap {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 132px);
    gap: 16px;
}
.chat-header {
    background: #fff;
    border-radius: 10px;
    padding: 20px 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.chat-header-left { display: flex; align-items: center; gap: 14px; }
.chat-icon {
    width: 44px; height: 44px; border-radius: 50%;
    background: linear-gradient(135deg, #0099D6, #005F8A);
    display: flex; align-items: center; justify-content: center;
}
.chat-title    { font-size: 16px; font-weight: 700; color: #1A1A2E; }
.chat-subtitle { font-size: 12px; color: #888; margin-top: 2px; }
.badge-beta {
    background: #E6F5FC; color: #0099D6;
    font-size: 11px; font-weight: 700;
    padding: 4px 10px; border-radius: 20px;
    border: 1px solid #B3D9F0;
}
.badge-realtime {
    background: #E8F5E9; color: #27AE60;
    font-size: 11px; font-weight: 700;
    padding: 4px 10px; border-radius: 20px;
    border: 1px solid #A9DFBF;
}
.chat-box {
    flex: 1; background: #fff; border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    display: flex; flex-direction: column; overflow: hidden;
}
.messages {
    flex: 1; overflow-y: auto; padding: 24px;
    display: flex; flex-direction: column; gap: 16px;
}
.msg { display: flex; gap: 10px; align-items: flex-start; }
.msg.user { flex-direction: row-reverse; }
.msg-avatar {
    width: 32px; height: 32px; border-radius: 50%;
    flex-shrink: 0; display: flex; align-items: center;
    justify-content: center; font-size: 12px; font-weight: 700;
}
.msg-avatar.ia      { background: linear-gradient(135deg, #0099D6, #005F8A); color: #fff; }
.msg-avatar.user-av { background: #1A1A2E; color: #fff; font-size: 10px; }
.msg-bubble {
    max-width: 72%; padding: 12px 16px;
    border-radius: 12px; font-size: 13px; line-height: 1.6;
}
.msg.ia   .msg-bubble { background: #F0F4F8; color: #1A1A2E; border-top-left-radius: 4px; }
.msg.user .msg-bubble { background: #0099D6; color: #fff;    border-top-right-radius: 4px; }
.msg-time { font-size: 10px; color: #aaa; margin-top: 4px; }
.msg.user .msg-time { text-align: right; }
.historial-sep {
    text-align: center; font-size: 11px; color: #aaa;
    margin: 4px 0; display: flex; align-items: center; gap: 8px;
}
.historial-sep::before,
.historial-sep::after { content: ''; flex: 1; height: 1px; background: #e8e8e8; }
.sugerencias { padding: 0 24px 16px; display: flex; gap: 8px; flex-wrap: wrap; }
.sugerencia-btn {
    background: #F0F4F8; border: 1px solid #E0E0E0;
    border-radius: 20px; padding: 6px 14px; font-size: 12px;
    color: #555; cursor: pointer; transition: all .15s;
}
.sugerencia-btn:hover { background: #E6F5FC; border-color: #0099D6; color: #0099D6; }
.chat-input-area {
    border-top: 1px solid #F0F4F8; padding: 16px 24px;
    display: flex; gap: 10px; align-items: flex-end;
}
.chat-input {
    flex: 1; border: 1.5px solid #E0E0E0; border-radius: 10px;
    padding: 10px 16px; font-size: 13px; font-family: Arial, sans-serif;
    resize: none; outline: none; transition: border-color .15s;
    max-height: 120px; min-height: 42px;
}
.chat-input:focus { border-color: #0099D6; }
.send-btn {
    width: 42px; height: 42px; background: #0099D6; border: none;
    border-radius: 10px; cursor: pointer; display: flex;
    align-items: center; justify-content: center; transition: background .15s; flex-shrink: 0;
}
.send-btn:hover    { background: #005F8A; }
.send-btn:disabled { background: #ccc; cursor: not-allowed; }
.clear-btn {
    height: 42px; background: #fff; border: 1px solid #e0e0e0;
    border-radius: 10px; padding: 0 14px; font-size: 12px;
    color: #888; cursor: pointer; transition: all .15s; flex-shrink: 0;
}
.clear-btn:hover { background: #fff0f0; border-color: #E74C3C; color: #E74C3C; }
.typing { display: none; align-items: center; gap: 6px; padding: 0 24px 8px; font-size: 12px; color: #888; }
.typing.show { display: flex; }
.dot { width: 6px; height: 6px; background: #0099D6; border-radius: 50%; animation: bounce 1.2s infinite; }
.dot:nth-child(2) { animation-delay: 0.2s; }
.dot:nth-child(3) { animation-delay: 0.4s; }
@keyframes bounce {
    0%, 60%, 100% { transform: translateY(0); }
    30%           { transform: translateY(-6px); }
}
</style>
@endsection

@section('content')
<div class="chat-wrap">

    {{-- Header --}}
    <div class="chat-header">
        <div class="chat-header-left">
            <div class="chat-icon">
                <svg width="22" height="22" viewBox="0 0 16 16" fill="none" stroke="white" stroke-width="1.5">
                    <path d="M2 3h12a1 1 0 011 1v7a1 1 0 01-1 1H5l-3 2V4a1 1 0 011-1z"/>
                </svg>
            </div>
            <div>
                <div class="chat-title">Asistente IA — SISTCO</div>
                <div class="chat-subtitle">Consulta los datos de tu cartera en lenguaje natural · Powered by LLaMA 3.3 70B</div>
            </div>
        </div>
        <div style="display:flex;gap:8px;align-items:center">
            <span class="badge-realtime">● Datos en tiempo real</span>
            <span class="badge-beta">BETA</span>
        </div>
    </div>

    {{-- Chat box --}}
    <div class="chat-box">

        <div class="messages" id="messages">

            {{-- Bienvenida --}}
            <div class="msg ia">
                <div class="msg-avatar ia">IA</div>
                <div>
                    <div class="msg-bubble">
                        ¡Hola <strong>{{ auth()->user()->name }}</strong>! Soy el Asistente IA de SISTCO con acceso a datos en tiempo real. Cualquier cambio en la base de datos se refleja inmediatamente en mis respuestas.<br><br>
                        <strong>Puedes preguntarme sobre:</strong> clientes en riesgo, proyección de ingresos, municipios con mora, segmentos y métricas del sistema.
                    </div>
                    <div class="msg-time">Ahora</div>
                </div>
            </div>

            {{-- Historial desde BD --}}
            @if($historial->count() > 0)
            <div class="historial-sep">Conversaciones anteriores</div>
            @foreach($historial as $conv)
            <div class="msg user">
                <div class="msg-avatar user-av">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
                <div>
                    <div class="msg-bubble">{{ $conv->pregunta }}</div>
                    <div class="msg-time">{{ $conv->created_at->format('d/m/Y H:i') }}</div>
                </div>
            </div>
            <div class="msg ia">
                <div class="msg-avatar ia">IA</div>
                <div>
                    <div class="msg-bubble">{!! nl2br(e($conv->respuesta)) !!}</div>
                    <div class="msg-time">{{ $conv->created_at->format('d/m/Y H:i') }}</div>
                </div>
            </div>
            @endforeach
            <div class="historial-sep">Conversación actual</div>
            @endif

        </div>

        {{-- Sugerencias --}}
        <div class="sugerencias" id="sugerencias">
            <button class="sugerencia-btn" onclick="usarSugerencia(this)">¿Cuántos clientes están en riesgo alto?</button>
            <button class="sugerencia-btn" onclick="usarSugerencia(this)">¿Quién estuvo en mora el último mes?</button>
            <button class="sugerencia-btn" onclick="usarSugerencia(this)">¿Cuál es la proyección de ingresos para mayo?</button>
            <button class="sugerencia-btn" onclick="usarSugerencia(this)">¿En qué municipio hay más mora?</button>
        </div>

        {{-- Typing --}}
        <div class="typing" id="typing">
            <div class="dot"></div><div class="dot"></div><div class="dot"></div>
            <span>El asistente está consultando los datos...</span>
        </div>

        {{-- Input --}}
        <div class="chat-input-area">
            <textarea
                class="chat-input"
                id="inputMensaje"
                placeholder="Escribe tu pregunta sobre los clientes o datos de SISTCO..."
                rows="1"
                onkeydown="handleKey(event)"
                oninput="autoResize(this)"
            ></textarea>
            <button class="clear-btn" onclick="limpiarChat()" title="Limpiar chat actual">🗑 Limpiar</button>
            <button class="send-btn" id="sendBtn" onclick="enviarMensaje()" title="Enviar">
                <svg width="18" height="18" viewBox="0 0 16 16" fill="none" stroke="white" stroke-width="1.5">
                    <path d="M14 8L2 2l3 6-3 6 12-6z"/>
                </svg>
            </button>
        </div>

    </div>
</div>

<script>
// Historial en memoria para contexto de conversación
let historialSesion = [];

// Precargar historial desde BD
@foreach($historial as $conv)
historialSesion.push({ rol: 'user',      contenido: @json($conv->pregunta)  });
historialSesion.push({ rol: 'assistant', contenido: @json($conv->respuesta) });
@endforeach

function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 120) + 'px';
}

function handleKey(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        enviarMensaje();
    }
}

function usarSugerencia(btn) {
    document.getElementById('inputMensaje').value = btn.textContent;
    document.getElementById('sugerencias').style.display = 'none';
    enviarMensaje();
}

function ahora() {
    return new Date().toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' });
}

function agregarMensaje(texto, tipo) {
    const msgs        = document.getElementById('messages');
    const div         = document.createElement('div');
    div.className     = `msg ${tipo}`;
    const iniciales   = tipo === 'user' ? '{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}' : 'IA';
    const avatarClass = tipo === 'user' ? 'user-av' : 'ia';
    const textoFmt    = tipo === 'ia' ? texto.replace(/\n/g, '<br>') : texto;

    div.innerHTML = `
        <div class="msg-avatar ${avatarClass}">${iniciales}</div>
        <div>
            <div class="msg-bubble">${textoFmt}</div>
            <div class="msg-time">${ahora()}</div>
        </div>`;
    msgs.appendChild(div);
    msgs.scrollTop = msgs.scrollHeight;
}

function limpiarChat() {
    if (!confirm('¿Limpiar el chat actual? El historial guardado en la base de datos se mantiene.')) return;
    const msgs = document.getElementById('messages');
    while (msgs.children.length > 1) msgs.removeChild(msgs.lastChild);
    historialSesion = [];
    document.getElementById('sugerencias').style.display = 'flex';
}

function enviarMensaje() {
    const input   = document.getElementById('inputMensaje');
    const texto   = input.value.trim();
    const sendBtn = document.getElementById('sendBtn');
    const typing  = document.getElementById('typing');

    if (!texto) return;

    agregarMensaje(texto, 'user');
    input.value = '';
    input.style.height = 'auto';
    document.getElementById('sugerencias').style.display = 'none';
    sendBtn.disabled = true;
    typing.classList.add('show');
    document.getElementById('messages').scrollTop = 99999;

    fetch('{{ route("asistente.consulta") }}', {
        method: 'POST',
        headers: {
            'Content-Type':  'application/json',
            'X-CSRF-TOKEN':  '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            pregunta:  texto,
            historial: historialSesion.slice(-12)  // últimos 6 intercambios
        })
    })
    .then(r => r.json())
    .then(data => {
        typing.classList.remove('show');
        sendBtn.disabled = false;
        const respuesta = data.respuesta || 'Lo siento, no pude procesar tu pregunta.';
        agregarMensaje(respuesta, 'ia');
        // Guardar en historial de sesión para próximas preguntas
        historialSesion.push({ rol: 'user',      contenido: texto     });
        historialSesion.push({ rol: 'assistant', contenido: respuesta });
    })
    .catch(() => {
        typing.classList.remove('show');
        sendBtn.disabled = false;
        agregarMensaje('No se pudo conectar con el asistente. Verifica que FastAPI esté corriendo en el puerto 8000.', 'ia');
    });
}

// Scroll al final al cargar
window.addEventListener('load', () => {
    document.getElementById('messages').scrollTop = 99999;
});
</script>
@endsection