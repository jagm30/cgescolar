<?php

namespace App\Providers;

use App\View\Composers\CicloComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        /**
         * Registrar el CicloComposer sobre el layout principal.
         *
         * Al estar asociado a 'layouts.app', se ejecuta UNA SOLA VEZ
         * por request — cuando Blade renderiza el layout.
         * Todas las vistas hijas (@extends('layouts.app')) heredan
         * automáticamente $ciclosDisponibles y $cicloActual.
         *
         * NO usar View::share() — ese ejecuta la consulta en cada
         * request aunque la vista no se renderice (ej: requests AJAX
         * que devuelven JSON puros sin vista).
         */
        View::composer('partials.navbar', CicloComposer::class);
    }
}
