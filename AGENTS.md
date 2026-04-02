# AGENTS.md — Laravel 12 REST API

## Project Identity

| Key | Value |
|-----|-------|
| Framework | Laravel 12 |
| Language | PHP 8.3 |
| Category | Fullstack API |
| Auth | Sanctum (token-based) |
| Database | MySQL 8 + Eloquent ORM |
| Queue | Redis + Laravel Jobs |
| Testing | Pest (NOT PHPUnit) |
| Analysis | Larastan (PHPStan Level 6) |
| Formatting | Laravel Pint |
| i18n | en, tr (JSON translation files) |

---

## Architecture — Actions Pattern with Repository Abstraction

```
Request → FormRequest (validation) → Controller → Action → Repository → Model
                                                      ↓
                                               DTO (spatie/laravel-data)
                                                      ↓
                                               Event → Listener → Job
```

```
app/
├── Http/
│   ├── Controllers/Api/V1/      ← PRESENTATION: Thin controllers
│   ├── Requests/                ← VALIDATION: FormRequest classes
│   └── Resources/               ← SERIALIZATION: API Resources
├── Actions/                     ← BUSINESS LOGIC: One action per use case
│   └── Product/
│       ├── CreateProductAction.php
│       └── UpdateProductAction.php
├── DTOs/                        ← DATA TRANSFER: spatie/laravel-data
│   └── Product/
│       ├── CreateProductData.php
│       └── ProductData.php
├── Models/                      ← DOMAIN: Eloquent models + SoftDeletes
├── Repositories/
│   ├── Contracts/               ← INTERFACES: Repository contracts
│   └── Eloquent/                ← IMPLEMENTATION: Eloquent repositories
├── Events/                      ← ASYNC: Domain events
├── Listeners/                   ← ASYNC: Event handlers
├── Jobs/                        ← ASYNC: Queue jobs
├── Exceptions/
│   ├── ApiException.php         ← Base exception with ApiErrorCode
│   └── ApiErrorCode.php         ← Enum for error codes
└── Providers/
    └── AppServiceProvider.php   ← Interface → implementation bindings

database/
├── factories/                   ← Test factories
├── migrations/                  ← Schema migrations
└── seeders/

routes/
└── api.php                      ← API routes (versioned: api/v1/)

lang/
├── en.json                      ← English translations
└── tr.json                      ← Turkish translations
```

### Strict Layer Rules

| Layer | Can Import From | NEVER Imports |
|-------|----------------|---------------|
| Controller | Actions/, DTOs/, Resources/, Requests/ | Repositories/, Models/ directly |
| Action | Repositories/ (interfaces), DTOs/, Models/, Events/ | Controllers/, Requests/ |
| Repository | Models/, Eloquent | Actions/, Controllers/ |
| DTO | Models/ (for `from()` factory) | Everything else |
| FormRequest | DTOs/ (for `toData()`) | Actions/, Repositories/ |

---

## Adding New Code — Where Things Go

### New Resource Checklist
1. **Migration + Model**: `php artisan make:model Product -mf` (with factory)
2. **DTO**: `app/DTOs/Product/CreateProductData.php` — spatie/laravel-data
3. **Repository contract**: `app/Repositories/Contracts/ProductRepositoryInterface.php`
4. **Repository impl**: `app/Repositories/Eloquent/ProductRepository.php`
5. **Bind in ServiceProvider**: `$this->app->bind(Interface::class, Implementation::class)`
6. **Action(s)**: `app/Actions/Product/CreateProductAction.php`
7. **FormRequest**: `app/Http/Requests/Product/CreateProductRequest.php`
8. **Resource**: `app/Http/Resources/ProductResource.php`
9. **Controller**: `app/Http/Controllers/Api/V1/ProductController.php`
10. **Routes**: Add to `routes/api.php` under `api/v1/`
11. **Pest tests**: `tests/Feature/Product/`

### Controller Pattern (Thin)
```php
class ProductController extends Controller
{
    public function store(
        CreateProductRequest $request,
        CreateProductAction $action,
    ): ProductResource {
        $data = CreateProductData::from($request->validated());
        $product = $action->execute($data);

        return new ProductResource($product);
    }
}
```

### Action Pattern (One per use case)
```php
class CreateProductAction
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository,
    ) {}

    public function execute(CreateProductData $data): Product
    {
        $product = $this->repository->create($data->toArray());

        event(new ProductCreated($product));

        return $product;
    }
}
```

### DTO Pattern (spatie/laravel-data)
```php
class CreateProductData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly float $price,
        public readonly ?string $description = null,
    ) {}
}
```

### Repository Pattern
```php
// Contract (interface)
interface ProductRepositoryInterface
{
    public function create(array $data): Product;
    public function findById(int $id): ?Product;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
}

// Implementation
class ProductRepository implements ProductRepositoryInterface
{
    public function create(array $data): Product
    {
        return Product::create($data);
    }
}

// Binding in AppServiceProvider
$this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
```

---

## Design & Architecture Principles

### Thin Controllers, Fat Actions
- Controllers ONLY: validate (FormRequest) → delegate (Action) → serialize (Resource)
- Actions contain ALL business logic
- One Action per use case — `CreateProductAction`, `UpdateProductAction`
- Actions can call other Actions for complex orchestration

### Repository Abstraction
- ALWAYS code against the interface (`ProductRepositoryInterface`)
- NEVER import Eloquent model directly in controllers/actions
- Binding in `AppServiceProvider` — swap implementations easily

### SoftDeletes on All Deletable Models
```php
class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'price', 'description'];
    protected $casts = ['price' => 'float'];
}
```

### Filtering with spatie/laravel-query-builder
```php
$products = QueryBuilder::for(Product::class)
    ->allowedFilters(['name', 'price'])
    ->allowedSorts(['name', 'created_at'])
    ->paginate();
```

---

## Error Handling

### ApiException Pattern
```php
class ApiException extends \Exception
{
    public function __construct(
        public readonly ApiErrorCode $errorCode,
        string $message,
        public readonly int $statusCode = 400,
        public readonly array $details = [],
    ) {
        parent::__construct($message);
    }
}

enum ApiErrorCode: string
{
    case PRODUCT_NOT_FOUND = 'PRODUCT_NOT_FOUND';
    case VALIDATION_ERROR = 'VALIDATION_ERROR';
    case UNAUTHORIZED = 'UNAUTHORIZED';
}
```

### Consistent Error Response
```json
{
  "error": {
    "code": "PRODUCT_NOT_FOUND",
    "message": "Product with ID 123 not found",
    "details": {}
  }
}
```

---

## Code Quality

### Naming Conventions
| Artifact | Convention | Example |
|----------|-----------|---------|
| Controller | `NounController` | `ProductController.php` |
| Action | `VerbNounAction` | `CreateProductAction.php` |
| FormRequest | `VerbNounRequest` | `CreateProductRequest.php` |
| Resource | `NounResource` | `ProductResource.php` |
| DTO | `VerbNounData` | `CreateProductData.php` |
| Repository | `NounRepository` | `ProductRepository.php` |
| Event | `NounVerbed` | `ProductCreated.php` |
| Job | `VerbNounJob` | `SendWelcomeEmailJob.php` |
| Migration | Laravel default | `2024_01_01_create_products_table.php` |

### i18n
- Locale set via `?lang=` query param or `Accept-Language` header
- Translation files: `lang/en.json`, `lang/tr.json`
- Use `__('key')` helper for translated strings

---

## Testing Strategy

| Level | What | Where | Tool |
|-------|------|-------|------|
| Feature | HTTP endpoints, actions | `tests/Feature/` | Pest |
| Unit | DTOs, utilities | `tests/Unit/` | Pest |
| Static | Type coverage | — | Larastan Level 6 |

### Pest — NOT PHPUnit
```php
// tests/Feature/Product/CreateProductTest.php
it('creates a product', function () {
    $response = postJson('/api/v1/products', [
        'name' => 'Test Product',
        'price' => 29.99,
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['data' => ['id', 'name', 'price']]);

    $this->assertDatabaseHas('products', ['name' => 'Test Product']);
});

it('validates required fields', function () {
    postJson('/api/v1/products', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'price']);
});
```

### Test Database
- SQLite in-memory (`phpunit.xml` configuration)
- `RefreshDatabase` trait for each test
- Factories for model generation

---

## Security & Performance

### Security
- Sanctum token-based auth — middleware on protected routes
- FormRequest validation — before controller logic executes
- Mass assignment protection — `$fillable` on all models
- SoftDeletes — data never truly deleted unless explicit purge
- Environment variables for secrets (`.env`)

### Performance
- `QueryBuilder` with pagination — no full table scans
- Eager loading (`with()`) to prevent N+1 queries
- Redis for queue jobs — async heavy processing
- Route caching in production: `php artisan route:cache`
- Config caching: `php artisan config:cache`

---

## Commands

| Action | Command |
|--------|---------|
| Dev server | `php artisan serve` |
| Test | `composer test` |
| Analysis | `composer analyse` |
| Format | `composer format` |
| Format check | `composer format:check` |
| Migrate | `php artisan migrate` |
| Rollback | `php artisan migrate:rollback` |
| Seed | `php artisan db:seed` |
| Cache routes | `php artisan route:cache` |
| Clear cache | `php artisan cache:clear` |

---

## Prohibitions — NEVER Do These

1. **NEVER** put business logic in controllers — delegate to Actions
2. **NEVER** use PHPUnit syntax — Pest is the test framework
3. **NEVER** import Eloquent models directly in controllers — use Repositories (interfaces)
4. **NEVER** skip FormRequest validation — every mutation endpoint needs it
5. **NEVER** return Eloquent models from API — use API Resources
6. **NEVER** use `DB::raw()` without parameterized queries — SQL injection risk
7. **NEVER** hardcode config values — use `.env` + `config()` helper
8. **NEVER** skip SoftDeletes on deletable models
9. **NEVER** use `dd()` or `dump()` in committed code — log instead
10. **NEVER** skip `readonly` on DTO/Action constructor properties — immutability first
