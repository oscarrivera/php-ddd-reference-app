# Contribuir

Cambios pequeños y revisables. El dominio no depende de HTTP, CLI ni PDO.

## Entorno

PHP 8.2+, Composer, extensión `pdo_sqlite`.

```bash
composer install
vendor/bin/phpunit
```

## Convenciones

- Código y nombres de tipo en inglés. Documentación de usuario en castellano.
- `declare(strict_types=1)` en PHP.
- Reglas de negocio en Domain. Handlers sin SQL ni `$_POST`.
- Un puerto, varias implementaciones: no filtrar InMemory/SQLite al dominio.
- No añadir frameworks ni dependencias de runtime sin una necesidad concreta.

## Pruebas

Todo cambio de política de SKU o de reserva lleva test unitario. No hace falta SQLite en PHPUnit.

## Parches

Rama corta, mensaje de commit en imperativo (qué cambia y por qué). No incluir `var/*.sqlite` ni `vendor/`.
