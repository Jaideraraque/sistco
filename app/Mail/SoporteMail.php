<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SoporteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $nombreUsuario,
        public string $correoUsuario,
        public string $rolUsuario,
        public string $asunto,
        public string $prioridad,
        public string $mensaje,
        public string $paginaOrigen,
        public string $fecha,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[SISTCO Soporte] ' . $this->asunto . ' — ' . strtoupper($this->prioridad),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.soporte',
        );
    }
}