# Migraciones — Sistema de Gestión Escolar v2.1

## Uso

Copia todos los archivos de esta carpeta a `database/migrations/` en tu proyecto Laravel y ejecuta:

```bash
php artisan migrate
```

Para revertir todas las migraciones:

```bash
php artisan migrate:rollback --step=24
```

---

## Orden de ejecución

Las migraciones están numeradas para garantizar el orden correcto según dependencias de llaves foráneas:

| # | Archivo | Tablas creadas |
|---|---------|----------------|
| 01 | create_ciclo_escolar_table | ciclo_escolar |
| 02 | create_nivel_escolar_table | nivel_escolar |
| 03 | create_grado_table | grado |
| 04 | create_usuario_table | usuario (sin ciclo_seleccionado_id) |
| 05 | create_grupo_table | grupo |
| 06 | create_familia_table | familia |
| 07 | create_alumno_table | alumno |
| 08 | create_inscripcion_table | inscripcion |
| 09 | create_contacto_familiar_table | contacto_familiar |
| 10 | create_alumno_contacto_table | alumno_contacto |
| 11 | create_documento_alumno_table | documento_alumno |
| 12 | create_concepto_cobro_table | concepto_cobro |
| 13 | create_plan_pago_table | plan_pago |
| 14 | create_plan_pago_detalle_tables | plan_pago_concepto, politica_descuento, politica_recargo |
| 15 | create_asignacion_plan_table | asignacion_plan |
| 16 | create_cargo_table | cargo |
| 17 | create_pago_table | pago |
| 18 | create_becas_tables | catalogo_beca, beca_alumno |
| 19 | create_descuento_cargo_table | descuento_cargo |
| 20 | create_razon_social_contacto_table | razon_social_contacto |
| 21 | create_facturacion_tables | config_fiscal, cfdi |
| 22 | create_admisiones_tables | prospecto, seguimiento_admision, doc_admision |
| 23 | create_auditoria_table | auditoria |
| 24 | add_ciclo_seleccionado_to_usuario_table | ALTER usuario (agrega ciclo_seleccionado_id) |

---

## Nota importante — referencia circular usuario ↔ ciclo_escolar

`usuario` necesita existir antes que `contacto_familiar` (que lo referencia).
`ciclo_escolar` necesita existir antes que `usuario` (por `ciclo_seleccionado_id`).

La solución es crear `usuario` primero **sin** `ciclo_seleccionado_id` (migración 04)
y agregar ese campo al final (migración 24), una vez que ambas tablas ya existen.

---

## Convenciones aplicadas

- Todas las tablas usan nombres en **snake_case en español** para coincidir con el DBML del proyecto.
- Se usa `restrictOnDelete()` en relaciones donde eliminar el padre rompería integridad de datos históricos (pagos, cargos, inscripciones).
- Se usa `nullOnDelete()` en relaciones donde el hijo puede seguir existiendo sin el padre (ej. alumno sin familia).
- Se usa `cascadeOnDelete()` solo cuando los hijos no tienen sentido sin el padre (ej. alumno_contacto sin alumno).
- Los campos `creado_at` usan `useCurrent()` en lugar de `timestamps()` para evitar generar `updated_at` en tablas que son inmutables por diseño.
- `json()` se usa para `datos_anteriores` y `datos_nuevos` en auditoría — compatible con MySQL 5.7+ y PostgreSQL.

---

## Requisitos

- Laravel 10 o superior
- PHP 8.1 o superior
- MySQL 8.0+ o PostgreSQL 14+
