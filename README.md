# php-ddd-reference-app

Referencia hexagonal / DDD en PHP 8.2 para reservar stock de productos de catálogo. Sin framework: el dominio queda aislado del transporte (HTTP y CLI) y de la persistencia.

Laboratorio local. No es un sistema de producción.

## Mapa DDD

```
Catalog                         Inventory
---------                       ---------
Product (sku, name, active)     StockItem (sku, onHand, reserved)
Sku VO  [A-Z0-9-]{3,32}         ReserveStock (política: qty <= available)
                                Evento: StockReserved

Shared/Domain: Uuid, Clock, DomainEvent, DomainException, AggregateRoot
```

Dos bounded contexts. El SKU vive en Catálogo y se usa como identificador compartido en Inventario (shared kernel mínimo). Inventario no conoce el nombre del producto; Catálogo no conoce existencias.

Capas por contexto:

- **Domain**: agregados, value objects, eventos, excepciones, puertos (repositorios).
- **Application**: comandos y handlers. Orquestan; no contienen reglas de stock.
- **Infrastructure**: InMemory (tests) y SQLite PDO (`var/app.sqlite` en runtime).

La API JSON y el CLI son adapters. Ambos llaman a los mismos handlers.

## Requisitos

- PHP 8.2+ con `pdo_sqlite`
- Composer
- PHPUnit (dev)

## Cómo ejecutarlo

```bash
composer install
php -S 127.0.0.1:8080 -t public public/index.php
```

Enlazar **solo** `127.0.0.1`. No hay autenticación.

Docker (opcional; el puerto queda en loopback):

```bash
docker compose up --build
```

CLI:

```bash
php bin/console.php product:add SKU-001 "Tornillo M6"
php bin/console.php stock:set SKU-001 100
php bin/console.php stock:reserve SKU-001 5
```

API:

```bash
curl -sS -X POST http://127.0.0.1:8080/products \
  -H 'Content-Type: application/json' \
  -d '{"sku":"SKU-001","name":"Tornillo M6"}'

curl -sS http://127.0.0.1:8080/products

curl -sS -X POST http://127.0.0.1:8080/stock/set \
  -H 'Content-Type: application/json' \
  -d '{"sku":"SKU-001","onHand":100}'

curl -sS -X POST http://127.0.0.1:8080/stock/reserve \
  -H 'Content-Type: application/json' \
  -d '{"sku":"SKU-001","qty":5}'
```

Errores: `{ "error": "...", "code": "..." }`. `409` stock insuficiente o SKU duplicado. `422` validación. `404` producto o stock inexistente.

Tests:

```bash
vendor/bin/phpunit
```

## Decisiones de diseño

**Por qué no hay Symfony aquí.** El objetivo es un mapa DDD legible en un repositorio pequeño. Un kernel de framework diluye el borde entre dominio e infraestructura y obliga a DI, bundles y convenciones que no aportan a la regla de reserva. Los adapters HTTP/CLI son deliberadamente finos. Si este dominio se incrusta en un proyecto Symfony, los handlers y agregados se reutilizan; cambia el wiring, no el modelo.

**InMemory frente a SQLite.** Los tests no tocan disco. Runtime usa PDO SQLite para que el laboratorio sobreviva a un reinicio del proceso sin introducir un ORM.

**Política de reserva.** `ReserveStockPolicy` es una regla pura: no se reserva más que `onHand - reserved`. El agregado `StockItem` la aplica y emite `StockReserved`.

**Sin bus de eventos.** El evento queda en el agregado y se serializa en la respuesta de reserva. Un bus sería el siguiente paso, no el primero.

## Seguridad

No hay autenticación ni autorización. Datos locales, sin red pública. Detalle en [SECURITY.md](SECURITY.md).

## Licencia

MIT. Véase [LICENSE](LICENSE).
