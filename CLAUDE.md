# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

CGEscolar is a Spanish-language school management system (sistema de gestión escolar) built with Laravel 12. It handles student enrollment, billing/payments, scholarships, admissions pipeline, and a parent portal. All business logic, validation messages, and UI text are in Spanish.

## Commands

```bash
# Development (runs PHP server + queue listener + Vite dev server in parallel)
composer dev

# Run all tests
php artisan test --compact

# Run a specific test
php artisan test --compact --filter=TestName

# Fix code formatting (run before finalizing any changes)
vendor/bin/pint --dirty

# Production frontend build
npm run build

# Database migrations
php artisan migrate
```

Use `php artisan make:` for generating files. Pass `--no-interaction` to all Artisan commands.

---

## Architecture

### Modules

| Module | Controllers | Purpose |
|---|---|---|
| Academic | `CicloController`, `NivelController`, `GradoController`, `GrupoController` | School year / level / grade / group management |
| Students | `AlumnoController`, `FamiliaController` | Student + family/contact management |
| Financial | `ConceptoController`, `PlanPagoController`, `CargoController`, `PagoController`, `CobrosController` | Billing concepts, payment plans, invoices, payments, POS |
| Scholarships | `BecaController`, `PoliticaController` | Scholarship catalog and discount/surcharge policies |
| Admissions | `ProspectoController` | Admissions pipeline with stage tracking |
| Auth | `AuthController` | Login/logout with role-based access |
| Portals | `DashboardController`, `PortalPadreController` | Admin dashboards and parent-facing portal |

### Role-Based Access

Three internal roles (`administrador`, `caja`, `recepcion`) plus `padre` (parent portal). Access is enforced via `CheckRol` middleware on routes: `->middleware('rol:administrador,caja')`.

### Request/Response Pattern

Controllers use the `RespondsWithJson` trait which provides `respuestaExito()` and `respuestaError()` methods. Responses are dual-mode: JSON for AJAX requests, redirects for standard browser requests (detected via `request()->ajax()`).

### View Composition

A `CicloComposer` view composer globally injects `$ciclosDisponibles` and `$cicloActual` into all views — no need to pass them manually from controllers.

### Validation

Always use Form Request classes (`App\Http\Requests\Store*Request`). Validation messages are in Spanish. Check sibling Form Requests before creating new ones.

### Routing

- `routes/web.php` — browser routes, session-authenticated
- `routes/api.php` — API routes, Sanctum-authenticated (`auth:sanctum`), mirrors web routes

Use named routes and the `route()` helper for URL generation.

### Models

Most models set `public $timestamps = false`. Relationships are defined with explicit return type hints. Use `casts()` method (not `$casts` property) for attribute casting — follow existing model conventions.

Never use `DB::` raw queries; prefer `Model::query()`. Always eager-load to avoid N+1 queries.

---

## Frontend

- **Templates**: Blade (`.blade.php`), server-rendered MPA
- **UI Framework**: AdminLTE (jQuery/Bootstrap-based admin theme)
- **Build**: Vite + Tailwind CSS v4
- **Layout**: `resources/views/layouts/master.blade.php`
- **No** Vue, React, Livewire, or Inertia

If a frontend change isn't reflected, the user needs to run `npm run build` or `composer dev`.

### Blade Components & Partials

Before writing inline HTML in a view, check `resources/views/components/` and `resources/views/partials/` for existing reusable pieces. Create a Blade component (`php artisan make:component`) when the same UI block appears in more than one view.

---

## Testing

Uses **Pest v3**. Create tests with `php artisan make:test --pest {name}` (add `--unit` for unit tests). Most tests should be feature tests. Tests use SQLite in-memory database. Use model factories when creating test data.

---

## Key Conventions

- PHP 8.3 — use constructor property promotion, explicit return types, and type hints on all parameters
- Prefer PHPDoc blocks over inline comments
- Enum keys in TitleCase
- `env()` only inside `config/` files; use `config('key')` everywhere else
- Queued jobs (`ShouldQueue`) for time-consuming operations
- Middleware registration goes in `bootstrap/app.php` (Laravel 12 — no `Kernel.php`)
- Follow existing naming patterns in sibling files before creating anything new

---

## Code Quality — No Duplication

These rules apply to every file Claude touches. Read before writing any code.

### Before creating anything new

1. **Search first** — use `grep -r "nombre_funcion" app/` or check sibling files before writing a new function, scope, or constant. If it exists, reuse it.
2. **One responsibility** — each class, method, and Blade component does exactly one thing. If a method needs an "and" in its description, split it.
3. **Max method length: ~20 lines** — if it exceeds that, extract a private method or move logic to a Service class.

### Services

Business logic lives in `App\Services\`, not in controllers or models. Controller methods should only: validate → call service → return response.

```php
// ✅ Correcto
class CargoController extends Controller
{
    public function store(StoreCargoRequest $request, CargoService $service): JsonResponse
    {
        $cargo = $service->crear($request->validated());
        return $this->respuestaExito('Cargo creado', $cargo);
    }
}

// ❌ Incorrecto — lógica de negocio en el controlador
public function store(Request $request): JsonResponse
{
    $descuento = $request->beca_id
        ? Beca::find($request->beca_id)->porcentaje / 100
        : 0;
    $total = $request->monto - ($request->monto * $descuento);
    Cargo::create([...]);
}
```

### Scopes over raw where()

Define named scopes on models instead of repeating `->where()` chains:

```php
// En el modelo
public function scopeActivo(Builder $query): Builder
{
    return $query->where('activo', true);
}

// En el controlador/service — limpio y reutilizable
Alumno::query()->activo()->get();
```

### Constants & Enums over magic strings/numbers

Never hardcode role names, status strings, or numeric codes inline. Put them in Enums (`app/Enums/`) or constants.

```php
// ❌ Incorrecto
if ($user->rol === 'administrador') { ... }

// ✅ Correcto
if ($user->rol === Rol::Administrador->value) { ... }
```

### Traits for shared controller behavior

If two or more controllers share a method (e.g., building a select list for a dropdown), extract it to a trait in `app/Http/Traits/`.

### No variable re-assignment without reason

Do not reassign a variable to a new value in the same scope unless there is a clear, commented reason. Prefer immutable-style assignments.

---

## Service & Repository Structure

When a Service grows beyond ~150 lines or needs to coordinate multiple models, split it:

```
App\Services\
  CargoService.php        ← orquesta
  Cargos\
    CalculadorDescuento.php
    GeneradorCargo.php
```

Query logic that is reused across two or more Services belongs in a Repository (`App\Repositories\`).

---

## Error Handling

- All exceptions that the user should see must be caught and returned via `respuestaError()`.
- Use specific exception types (`ModelNotFoundException`, custom `App\Exceptions\*`) — never catch `\Exception` as a catch-all unless you re-throw.
- Validation errors are handled automatically by Form Requests — do not duplicate validation inside controllers or services.

---

## Naming Reference (Spanish ↔ Code)

Keeping names consistent prevents duplicated concepts under different aliases.

| Concepto | Modelo | Tabla BD | Ruta nombrada |
|---|---|---|---|
| Ciclo escolar | `Ciclo` | `ciclos` | `ciclos.*` |
| Alumno | `Alumno` | `alumnos` | `alumnos.*` |
| Familia / Contacto | `Familia` | `familias` | `familias.*` |
| Cargo (cobro generado) | `Cargo` | `cargos` | `cargos.*` |
| Pago | `Pago` | `pagos` | `pagos.*` |
| Beca | `Beca` | `becas` | `becas.*` |
| Prospecto | `Prospecto` | `prospectos` | `prospectos.*` |

When naming new models or routes, extend this table first — do not invent synonyms.

---

## What Claude Should NOT Do

- Do not create a new Service if one already exists for that module — extend it.
- Do not add a column to a migration that already exists in the table — check `database/migrations/` first.
- Do not pass `$cicloActual` or `$ciclosDisponibles` manually from a controller — `CicloComposer` handles it.
- Do not use `compact()` — pass named keys explicitly for clarity.
- Do not leave `dd()`, `dump()`, or `ray()` calls in final code.
- Do not add a `@csrf` token to API routes — they use Sanctum token auth.
- Do not create a Blade file for a UI fragment used in only one place — use an `@include` with a partial or inline it.
