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

## Frontend

- **Templates**: Blade (`.blade.php`), server-rendered MPA
- **UI Framework**: AdminLTE (jQuery/Bootstrap-based admin theme)
- **Build**: Vite + Tailwind CSS v4
- **Layout**: `resources/views/layouts/master.blade.php`
- **No** Vue, React, Livewire, or Inertia

If a frontend change isn't reflected, the user needs to run `npm run build` or `composer dev`.

## Testing

Uses **Pest v3**. Create tests with `php artisan make:test --pest {name}` (add `--unit` for unit tests). Most tests should be feature tests. Tests use SQLite in-memory database. Use model factories when creating test data.

## Key Conventions

- PHP 8.3 — use constructor property promotion, explicit return types, and type hints on all parameters
- Prefer PHPDoc blocks over inline comments
- Enum keys in TitleCase
- `env()` only inside `config/` files; use `config('key')` everywhere else
- Queued jobs (`ShouldQueue`) for time-consuming operations
- Middleware registration goes in `bootstrap/app.php` (Laravel 12 — no `Kernel.php`)
- Follow existing naming patterns in sibling files before creating anything new
