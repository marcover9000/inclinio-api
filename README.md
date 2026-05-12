# inclinio-api

Backend Laravel 12 d'Inclinio v2 (CRM + futura API per a sites d'estadia).

## Convencions
- Arquitectura: DDD pragmàtic amb Eloquent enriquit dins `app/Modules/`.
- Tests: TDD pur amb Pest.

## Mòduls (bounded contexts)
- `Identity` — usuaris, autenticació, rols
- `Crm` — leads, clients, conversacions
- `Billing` — factures, pressupostos
- `Content` — articles, pàgines, SEO/AEO (futur)
- `Shared` — kernel comú (Value Objects base, esdeveniments)

## Comandes
Sempre des de `inclinio-infra/`:
```bash
./bin/api php artisan migrate
./bin/api ./vendor/bin/pest
./bin/composer install
```
