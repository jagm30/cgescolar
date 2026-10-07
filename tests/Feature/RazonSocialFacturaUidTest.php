<?php

use App\Models\ContactoFamiliar;
use App\Models\Familia;
use App\Models\RazonSocialContacto;
use App\Services\CfdiService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function crearRazonSocialConUid(): RazonSocialContacto
{
    $familia = Familia::create(['apellido_familia' => 'Perez', 'activo' => true]);

    $contacto = ContactoFamiliar::create([
        'familia_id' => $familia->id,
        'nombre' => 'Tutor',
        'ap_paterno' => 'Perez',
    ]);

    return RazonSocialContacto::create([
        'contacto_id' => $contacto->id,
        'rfc' => 'PEPJ800101AAA',
        'razon_social' => 'JUAN PEREZ',
        'regimen_fiscal' => '605',
        'domicilio_fiscal' => '29000',
        'uso_cfdi_default' => 'D10',
        'factura_uid' => 'uid-anterior',
    ]);
}

test('cambiar el código postal limpia el UID de factura.com en caché', function () {
    $rs = crearRazonSocialConUid();

    $rs->update(['domicilio_fiscal' => '29010']);

    expect($rs->fresh()->factura_uid)->toBeNull();
});

test('cambiar campos no fiscales conserva el UID de factura.com', function () {
    $rs = crearRazonSocialConUid();

    $rs->update(['es_principal' => true]);

    expect($rs->fresh()->factura_uid)->toBe('uid-anterior');
});

test('asignar un UID nuevo junto con datos fiscales lo conserva', function () {
    $rs = crearRazonSocialConUid();

    $rs->update(['domicilio_fiscal' => '29010', 'factura_uid' => 'uid-nuevo']);

    expect($rs->fresh()->factura_uid)->toBe('uid-nuevo');
});

test('detecta el error CFDI40147 de domicilio fiscal del receptor', function () {
    $service = app(CfdiService::class);

    expect($service->esErrorDomicilioReceptorNoCoincide(
        'factura.com: CFDI40147 - El campo DomicilioFiscalReceptor del receptor, debe encontrarse en la lista de RFC inscritos no cancelados en el SAT. | | | error'
    ))->toBeTrue()
        ->and($service->esErrorDomicilioReceptorNoCoincide('CFDI40145 - nombre del receptor'))->toBeFalse();
});
