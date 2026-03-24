# Modelos Eloquent — Sistema de Gestión Escolar v2.1

## Instalación

Copia todos los archivos `.php` de esta carpeta a `app/Models/` en tu proyecto Laravel.

---

## Lista de modelos

| Modelo | Tabla | Descripción |
|--------|-------|-------------|
| `CicloEscolar` | ciclo_escolar | Ciclos académicos |
| `NivelEscolar` | nivel_escolar | Maternal, Preescolar, Primaria, Secundaria |
| `Grado` | grado | Grados dentro de cada nivel |
| `Grupo` | grupo | Grupos A, B, etc. por ciclo y grado |
| `Usuario` | usuario | Usuarios del sistema (extiende Authenticatable) |
| `Familia` | familia | Agrupa alumnos hermanos y sus contactos |
| `Alumno` | alumno | Alumnos inscritos |
| `Inscripcion` | inscripcion | Inscripción alumno-ciclo-grupo |
| `ContactoFamiliar` | contacto_familiar | Padres, madres y contactos autorizados |
| `AlumnoContacto` | alumno_contacto | Tabla pivot alumno ↔ contacto |
| `DocumentoAlumno` | documento_alumno | Checklist de documentos por alumno |
| `ConceptoCobro` | concepto_cobro | Catálogo de conceptos (colegiatura, inscripción...) |
| `PlanPago` | plan_pago | Planes de pago por ciclo y nivel |
| `PlanPagoConcepto` | plan_pago_concepto | Montos por concepto dentro de un plan |
| `PoliticaDescuento` | politica_descuento | Reglas de descuento por plan |
| `PoliticaRecargo` | politica_recargo | Reglas de recargo por plan |
| `AsignacionPlan` | asignacion_plan | Asignación de plan a alumno/grupo/nivel |
| `Cargo` | cargo | Cargos generados por periodo |
| `Pago` | pago | Pagos y abonos registrados en caja |
| `CatalogoBeca` | catalogo_beca | Catálogo de tipos de beca |
| `BecaAlumno` | beca_alumno | Asignación de beca a alumno |
| `DescuentoCargo` | descuento_cargo | Descuentos manuales sobre cargos |
| `RazonSocialContacto` | razon_social_contacto | RFCs del contacto para facturación |
| `ConfigFiscal` | config_fiscal | Configuración fiscal de la institución |
| `Cfdi` | cfdi | Facturas electrónicas timbradas |
| `Prospecto` | prospecto | Prospectos en proceso de admisión |
| `SeguimientoAdmision` | seguimiento_admision | Bitácora de seguimiento a prospectos |
| `DocAdmision` | doc_admision | Documentos requeridos en admisión |
| `Auditoria` | auditoria | Bitácora de acciones del sistema |

---

## Ejemplos de uso

### Obtener alumnos de una familia
```php
$familia = Familia::find(1);
$alumnos = $familia->alumnos()->activo()->get();
```

### Hermanos de un alumno en el ciclo activo
```php
$ciclo = CicloEscolar::activo()->first();
$alumno = Alumno::find(12);
$hermanos = $alumno->hermanos();
$totalHermanos = $alumno->familia->alumnosActivosEnCiclo($ciclo->id);
```

### Alumnos del portal (desde el usuario padre)
```php
$usuario = Usuario::find(8); // rol = padre
$alumnos = $usuario->alumnos(); // todos los hijos de su familia
```

### Estado real de un cargo
```php
$cargo = Cargo::find(100);
echo $cargo->estado_real;     // 'pendiente', 'vencido', 'parcial', etc.
echo $cargo->saldo_abonado;   // suma de abonos vigentes
echo $cargo->saldo_pendiente_base; // monto_original - saldo_abonado
```

### Calcular descuento de beca
```php
$beca = BecaAlumno::vigenteHoy()
    ->where('alumno_id', 12)
    ->where('concepto_id', 3)
    ->where('ciclo_id', $cicloId)
    ->first();

if ($beca) {
    $descuento = $beca->calcularDescuento($cargo->monto_original);
}
```

### Calcular recargo de un cargo vencido
```php
$politica = $cargo->asignacion->plan->politicaRecargoActiva();
$recargo = $politica?->calcularRecargo($cargo->monto_original, $cargo->fecha_vencimiento) ?? 0;
```

### Registrar auditoría
```php
Auditoria::registrar('pago', $pago->id, 'anulacion', $pago->toArray(), null);
```

### Contactos pendientes de crear usuario
```php
$pendientes = ContactoFamiliar::where('tiene_acceso_portal', true)
    ->whereNull('usuario_id')
    ->with('familia')
    ->get();
```

---

## Notas importantes

### Usuario y autenticación
`Usuario` extiende `Authenticatable` de Laravel. Como la columna se llama
`password_hash` en lugar de `password`, se sobreescribe `getAuthPassword()`.
En `config/auth.php` asegúrate de apuntar al modelo correcto:
```php
'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model'  => App\Models\Usuario::class,
    ],
],
```

### Timestamps personalizados
La mayoría de tablas solo tienen `creado_at` (sin `updated_at`).
Por eso todos los modelos tienen `public $timestamps = false` y el cast
de `creado_at` se define manualmente.

### Nombres de tablas en español
Laravel por defecto pluraliza en inglés. Al definir `protected $table`
explícitamente en cada modelo se evita cualquier conflicto.

### Estado real del cargo
El estado `vencido` nunca se guarda en BD. Siempre usa el accessor
`$cargo->estado_real` para mostrar el estado correcto al usuario.
