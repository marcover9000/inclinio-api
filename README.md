# inclinio-api

Backend Laravel 12 d'Inclinio v2 (CRM + futura API per a sites d'estadia).

## Convencions
- **Codi en anglès** (classes, mètodes, variables).
- **Comentaris i documentació en català**.
- Arquitectura: DDD pragmàtic amb Eloquent enriquit dins `app/Modules/`.
- Tests: TDD pur amb Pest (cada feature comença per un test fallant).

## Mòduls (bounded contexts)
- `Identity` — usuaris, autenticació, rols
- `Crm` — leads, clients, conversacions
- `Billing` — factures, pressupostos
- `Content` — articles, pàgines, SEO/AEO (futur)
- `Shared` — kernel comú (Value Objects base, esdeveniments)

## Comandes
Sempre des de `inclinio-infra/`:
```bash
./bin/api artisan migrate
./bin/api pest
./bin/composer install
```
