---
name: architecture
type: knowledge
version: 1.0.0
agent: CodeActAgent
triggers:
  - architecture
  - laravel
  - eloquent
  - controller
  - service
---

# Architecture — Laravel 11

## Project Structure

```
app/
├── Http/
│   ├── Controllers/           ← Resource controllers
│   │   └── UserController.php
│   ├── Middleware/
│   │   └── EnsureJsonResponse.php
│   ├── Requests/              ← Form Requests (validation)
│   │   └── StoreUserRequest.php
│   └── Resources/             ← API Resources (JSON transforms)
│       └── UserResource.php
├── Models/                    ← Eloquent models
│   └── User.php
├── Services/                  ← Business logic
│   └── UserService.php
├── Repositories/              ← Data access (optional)
│   └── UserRepository.php
├── Policies/                  ← Authorization
│   └── UserPolicy.php
└── Exceptions/
    └── Handler.php
config/
database/
├── migrations/
├── seeders/
└── factories/
routes/
├── api.php                    ← API routes
└── web.php
tests/
├── Feature/
└── Unit/
```

## Controller

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    public function index()
    {
        $users = $this->userService->getAll();
        return UserResource::collection($users);
    }

    public function store(StoreUserRequest $request)
    {
        $user = $this->userService->create($request->validated());
        return new UserResource($user);
    }

    public function show(int $id)
    {
        $user = $this->userService->findOrFail($id);
        return new UserResource($user);
    }
}
```

## Form Request (Validation)

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'min:8', 'confirmed'],
        ];
    }
}
```

## API Resource

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
```

## Service Layer

```php
<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function create(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    public function findOrFail(int $id): User
    {
        return User::findOrFail($id);
    }

    public function getAll()
    {
        return User::paginate(15);
    }
}
```

## Routes

```php
<?php
// routes/api.php
use App\Http\Controllers\UserController;

Route::apiResource('users', UserController::class);
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('posts', PostController::class);
});
```

## Rules

- Controller → Service → Model (thin controllers).
- Form Requests for all validation.
- API Resources for JSON response transformation.
- Policies for authorization.
- Eloquent for database access.
