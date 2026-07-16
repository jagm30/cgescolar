<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AlertaModificacionUsuarioMail extends Mailable
{
    use Queueable, SerializesModels;

    public $nombreUsuario;
    public $emailUsuario;
    public $rolActual;
    public $cambiosRealizados;
    public $quienModifico;

    public function __construct($nombreUsuario, $emailUsuario, $rolActual, $cambiosRealizados, $quienModifico)
    {
        $this->nombreUsuario = $nombreUsuario;
        $this->emailUsuario = $emailUsuario;
        $this->rolActual = $rolActual;
        $this->cambiosRealizados = $cambiosRealizados;
        $this->quienModifico = $quienModifico;
    }

    public function build()
    {
        return $this->subject('Alerta de Seguridad Crítica: Modificación de cuenta')
                    ->view('emails.alerta-modificacion-usuario');
    }
}