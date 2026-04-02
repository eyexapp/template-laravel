# Laravel API Template

Production-ready Laravel 12 REST API template with clean architecture, token authentication, and full tooling.

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 12 (PHP 8.2+) |
| Auth | Sanctum (token-based) |
| DTOs | spatie/laravel-data |
| Query | spatie/laravel-query-builder |
| API Docs | Scramble (auto-generated) |
| Testing | Pest |
| Static Analysis | Larastan (PHPStan level 6) |
| Code Style | Laravel Pint |
| Database | MySQL 8.x (SQLite for tests) |
| Container | Docker + Docker Compose |

## Quick Start

### Local Development

```bash
# Install dependencies
composer install

# Configure environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Start server
php artisan serve
```

### Docker

```bash
docker compose up -d
```

The API will be available at `http://localhost:8080`.

## Project Structure

```
app/
├── Actions/           # Business logic (one action per use case)
│   └── Item/
├── DTOs/              # Data Transfer Objects (spatie/laravel-data)
│   └── Item/
├── Enums/             # PHP enums (ApiErrorCode)
├── Events/            # Domain events
├── Exceptions/        # Custom exceptions (ApiException)
├── Http/
│   ├── Controllers/Api/V1/   # Thin API controllers
│   ├── Middleware/            # SetLocale
│   ├── Requests/Item/        # Form requests (validation)
│   └── Resources/            # API resources (serialization)
├── Jobs/              # Queued jobs
├── Listeners/         # Event listeners
├── Models/            # Eloquent models
├── Repositories/
│   ├── Contracts/     # Repository interfaces
│   └── Eloquent/      # Eloquent implementations
└── Services/          # Service classes
```

## API Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `GET` | `/` | Health check | No |
| `POST` | `/api/v1/auth/register` | Register user | No |
| `POST` | `/api/v1/auth/login` | Login | No |
| `POST` | `/api/v1/auth/logout` | Logout | Yes |
| `GET` | `/api/v1/auth/me` | Current user | Yes |
| `GET` | `/api/v1/items` | List items (paginated) | Yes |
| `POST` | `/api/v1/items` | Create item | Yes |
| `GET` | `/api/v1/items/{id}` | Get item | Yes |
| `PUT` | `/api/v1/items/{id}` | Update item | Yes |
| `DELETE` | `/api/v1/items/{id}` | Delete item | Yes |
| `GET` | `/docs/api` | API documentation (Scramble) | No |

### Query Parameters (Items)

- **Filter:** `?filter[title]=keyword`
- **Sort:** `?sort=title` or `?sort=-created_at`
- **Pagination:** `?page=1&per_page=15`
- **Locale:** `?lang=tr` or `Accept-Language: tr`

## Architecture

```
Request → FormRequest → Controller → Action → Repository → Model
                                        ↓
                                       DTO
                                        ↓
                                  Event → Listener → Job
```

- **Controllers** — Thin. Validate, delegate, return.
- **Actions** — Business logic. One action per use case.
- **DTOs** — Type-safe data transfer with `spatie/laravel-data`.
- **Repositories** — Database abstraction via interfaces.
- **Events/Listeners/Jobs** — Async side effects.

## Development Commands

```bash
# Run tests
composer test

# Static analysis
composer analyse

# Code formatting
composer format
composer format:check
```

## Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `APP_NAME` | Application name | MyApp |
| `APP_ENV` | Environment | local |
| `DB_CONNECTION` | Database driver | mysql |
| `DB_HOST` | Database host | 127.0.0.1 |
| `DB_PORT` | Database port | 3306 |
| `DB_DATABASE` | Database name | myapp |
| `DB_USERNAME` | Database user | myapp |
| `DB_PASSWORD` | Database password | secret |

## Adding a New Resource

1. Create migration + Model (with factory)
2. Create DTO in `app/DTOs/`
3. Create Repository interface + Eloquent implementation
4. Bind in `AppServiceProvider`
5. Create Actions
6. Create FormRequests + Resource
7. Create Controller + Routes
8. Write Pest tests

## License

MIT
