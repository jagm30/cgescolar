<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AlertaCambioCorreoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $nombreUsuario;
    public $correoAnterior;
    public $correoNuevo;
    public $quienModifico;
    public $tipoPerfil; // Ej: 'Padre de Familia' o 'Personal Administrativo'

    public function __construct($nombreUsuario, $correoAnterior, $correoNuevo, $quienModifico, $tipoPerfil)
    {
        $this->nombreUsuario  = $nombreUsuario;
        $this->correoAnterior = $correoAnterior;
        $this->correoNuevo    = $correoNuevo;
        $this->quienModifico  = $quienModifico;
        $this->tipoPerfil     = $tipoPerfil;
    }

    public function build()
    {
        // El asunto también se vuelve dinámico
        return $this->subject("Alerta de Seguridad: Cambio de correo ({$this->tipoPerfil})")
                    ->view('emails.alerta-cambio-correo');
    }
}