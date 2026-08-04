<?php

namespace App\Providers;

use App\View\Composers\CicloComposer;
use App\View\Composers\PortalNotificacionesComposer;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('es_MX');

        View::composer('*', CicloComposer::class);
        View::composer('partials.navbar', PortalNotificacionesComposer::class);
        Paginator::defaultView('vendor.pagination.adminlte');
        Paginator::defaultSimpleView('vendor.pagination.simple-adminlte');
    }
}
