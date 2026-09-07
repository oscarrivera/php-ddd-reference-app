# Seguridad

Este repositorio es un laboratorio de diseño. **No es software de producción.**

## Alcance

- No hay autenticación, sesiones, CSRF ni rate limiting.
- La API JSON acepta cualquier cliente que alcance el puerto.
- SQLite en `var/app.sqlite` no está cifrado.

## Exposición de red

El servidor de desarrollo debe escucharse solo en loopback:

```bash
php -S 127.0.0.1:8080 -t public public/index.php
```

`docker compose` publica `127.0.0.1:8080:8080`. No cambie el bind a `0.0.0.0` en el host.

## Reportes

Si encuentra un fallo en el modelo de dominio (por ejemplo, reserva por encima de disponible), ábralo como issue. No envíe datos reales de almacén a este proyecto.

## Dependencias

PHPUnit es `require-dev`. Runtime: PHP >= 8.2 y PDO SQLite. Revise avisos de Composer antes de actualizar.
