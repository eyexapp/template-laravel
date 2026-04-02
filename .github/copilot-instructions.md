# Laravel API — Copilot Instructions

## Architecture

This is a **Laravel 12 REST API** using the following layered pattern:

```
Request → FormRequest (validation) → Controller → Action → Repository → Model
                                                      ↓
                                               DTO (data transfer)
                                                      ↓
                                               Event → Listener → Job
```

### Rules

- **Controllers are thin** — they only validate, delegate to Actions, and return Resources.
- **Actions** contain business logic. One action per use case (`CreateItemAction`, `UpdateItemAction`).
- **DTOs** use `spatie/laravel-data`. They transfer validated data between layers.
- **Repositories** abstract database access. Always code against the interface (`ItemRepositoryInterface`), not the Eloquent implementation.
- **Events/Listeners/Jobs** handle side effects asynchronously.

## Conventions

- API routes are versioned under `api/v1/` prefix.
- Auth uses **Sanctum** token-based authentication.
- Error responses follow a consistent format: `{ "error": { "code": "...", "message": "...", "details": {} } }`.
- Use `ApiException` for domain-specific errors with `ApiErrorCode` enum.
- All models with delete use **SoftDeletes**.
- Filtering and sorting use `spatie/laravel-query-builder`.

## Adding a New Resource

1. Create migration + Model (with factory) in `database/`
2. Create DTO in `app/DTOs/ResourceName/`
3. Create Repository interface in `app/Repositories/Contracts/`
4. Create Eloquent implementation in `app/Repositories/Eloquent/`
5. Bind interface → implementation in `AppServiceProvider`
6. Create Actions in `app/Actions/ResourceName/`
7. Create FormRequests in `app/Http/Requests/ResourceName/`
8. Create Resource in `app/Http/Resources/`
9. Create Controller in `app/Http/Controllers/Api/V1/`
10. Add routes to `routes/api.php`
11. Write Pest tests in `tests/Feature/`

## i18n

- Supported locales: `en`, `tr`
- Translation files: `lang/en.json`, `lang/tr.json`
- Locale is set via `?lang=` query param or `Accept-Language` header

## Testing

- Framework: **Pest** (not PHPUnit)
- Tests use SQLite in-memory (`phpunit.xml`)
- Feature tests in `tests/Feature/`, unit tests in `tests/Unit/`
- Run: `composer test`

## Commands

- `composer test` — Run Pest tests
- `composer analyse` — Run Larastan (PHPStan level 6)
- `composer format` — Fix code style with Pint
- `composer format:check` — Check code style
