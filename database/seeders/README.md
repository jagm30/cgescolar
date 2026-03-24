# Seeders y Form Requests — SGE v2.1

---

## Seeders

### Instalación
Copia los archivos de la carpeta `seeders/` a `database/seeders/` en tu proyecto Laravel.

### Ejecución
```bash
# Ejecutar todos los seeders
php artisan db:seed

# Migrar y poblar desde cero
php artisan migrate:fresh --seed

# Ejecutar un seeder específico
php artisan db:seed --class=CicloEscolarSeeder
```

### Datos de prueba incluidos

| Seeder | Datos creados |
|--------|---------------|
| `CicloEscolarSeeder` | 3 ciclos: 2024-2025 (cerrado), 2025-2026 (activo), 2026-2027 (configuración) |
| `NivelEscolarSeeder` | 4 niveles: Maternal, Preescolar, Primaria, Secundaria |
| `GradoSeeder` | 13 grados distribuidos en los 4 niveles |
| `UsuarioSeeder` | 3 usuarios internos (admin, caja, recepción) + 3 padres de familia |
| `GrupoSeeder` | 26 grupos (A y B por cada grado) para el ciclo 2025-2026 |
| `FamiliaSeeder` | 4 familias (Familia López García, Martínez Ruiz, Hernández Cruz, Sánchez Morales) |
| `AlumnoSeeder` | 6 alumnos: 2 hermanos López, 2 hermanos Martínez, 1 Hernández, 1 Sánchez |
| `InscripcionSeeder` | 6 inscripciones en diferentes grupos y niveles |
| `ContactoFamiliarSeeder` | 7 contactos familiares con distintos niveles de acceso al portal |
| `AlumnoContactoSeeder` | Vínculos alumno ↔ contacto con parentesco y permisos |
| `ConceptoCobroSeeder` | 10 conceptos: 4 colegiaturas, 4 inscripciones, material y seguro |
| `PlanPagoSeeder` | 4 planes mensuales (uno por nivel) + políticas de descuento y recargo |
| `AsignacionPlanSeeder` | 4 asignaciones a nivel (aplican para todos los alumnos del nivel) |
| `CargoSeeder` | 60 cargos mensuales (6 alumnos × 10 meses). Sep-Nov marcados como pagados |
| `CatalogoBecaSeeder` | 4 tipos de beca: Excelencia (50%), Hermanos (15%), Trabajador (100%), Especial ($500) |
| `BecaAlumnoSeeder` | 3 becas asignadas: Juan (Excelencia), Ana (Hermanos), Diego (Especial) |
| `ProspectoSeeder` | 4 prospectos en distintas etapas + seguimientos y documentos |

### Credenciales de prueba

| Usuario | Email | Contraseña | Rol |
|---------|-------|-----------|-----|
| Administrador | admin@escuela.edu.mx | Admin2025! | administrador |
| Cajero | caja@escuela.edu.mx | Caja2025! | caja |
| Recepción | recepcion@escuela.edu.mx | Recepcion2025! | recepcion |
| Padre (Roberto López) | roberto.lopez@gmail.com | Padre2025! | padre |
| Madre (María García) | maria.garcia@gmail.com | Padre2025! | padre |
| Padre (Carlos Martínez) | carlos.martinez@gmail.com | Padre2025! | padre |

---

## Form Requests

### Instalación
Copia los archivos de la carpeta `requests/` a `app/Http/Requests/` en tu proyecto Laravel.

### Lista de Form Requests

| Request | Módulo | Descripción |
|---------|--------|-------------|
| `StoreAlumnoRequest` | Alumnos | Validación completa al registrar un nuevo alumno con familia y contactos |
| `UpdateAlumnoRequest` | Alumnos | Actualización parcial de datos del alumno |
| `StoreInscripcionRequest` | Inscripciones | Valida cupo del grupo y que el alumno no esté inscrito en el ciclo |
| `StorePlanPagoRequest` | Planes | Crea plan con conceptos, políticas de descuento y recargo |
| `StoreAsignacionPlanRequest` | Planes | Asigna plan a alumno/grupo/nivel validando coherencia de origen |
| `StorePagoRequest` | Cobros | Registra abonos validando saldo pendiente y estado del cargo |
| `AnularPagoRequest` | Cobros | Anulación con verificación de CFDI timbrado |
| `StoreBecaAlumnoRequest` | Becas | Asigna beca validando que el concepto acepte becas |
| `StoreDescuentoCargoRequest` | Descuentos | Descuento manual con validación de saldo disponible |
| `StoreProspectoRequest` | Admisiones | Registro de nuevo prospecto |
| `UpdateProspectoEtapaRequest` | Admisiones | Cambio de etapa con nota obligatoria |
| `StoreUsuarioRequest` | Usuarios | Crea usuario vinculando contacto para rol padre |
| `UpdateUsuarioRequest` | Usuarios | Actualización incluyendo cambio de contraseña opcional |
| `StoreRazonSocialContactoRequest` | Facturación | Registra RFC validando formato SAT y límite de 3 por contacto |

### Uso en controladores

```php
// Ejemplo en AlumnoController
use App\Http\Requests\StoreAlumnoRequest;
use App\Http\Requests\UpdateAlumnoRequest;

public function store(StoreAlumnoRequest $request)
{
    // $request->validated() ya contiene solo los datos validados
    $datos = $request->validated();
    // ...
}

public function update(UpdateAlumnoRequest $request, int $alumno)
{
    $datos = $request->validated();
    // ...
}
```
