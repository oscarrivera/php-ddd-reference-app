# Changelog

Formato basado en [Keep a Changelog](https://keepachangelog.com/es/1.1.0/).
Versionado semántico.

## [0.1.0] - 2026-09-07

### Añadido

- Contextos Catalog e Inventory (hexagonal / DDD).
- Value object `Sku`, agregado `Product`, agregado `StockItem`.
- Comando `ReserveStock` con política de disponibilidad y evento `StockReserved`.
- Persistencia InMemory (tests) y SQLite PDO (`var/app.sqlite`).
- API JSON mínima y CLI (`product:add`, `stock:set`, `stock:reserve`).
- Tests unitarios de `Sku`, política de reserva y repositorios en memoria.
