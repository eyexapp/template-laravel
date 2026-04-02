---
name: version-control
type: knowledge
version: 1.0.0
agent: CodeActAgent
triggers:
  - git
  - commit
  - artisan
  - docker
  - deploy
---

# Version Control — Laravel

## Commits

- `feat(users): add user registration endpoint`
- `fix(auth): refresh sanctum token on password change`
- `refactor(posts): extract post filtering to scope`

## Artisan Commands

```bash
php artisan make:model Post -mfcr    # Model + migration + factory + controller + resource
php artisan make:request StorePostRequest
php artisan make:policy PostPolicy
php artisan make:resource PostResource
php artisan migrate
php artisan migrate:rollback
php artisan db:seed
php artisan route:list
php artisan tinker
```

## .gitignore

```
vendor/
node_modules/
.env
storage/*.key
*.cache
public/build/
```

## Environment

```bash
# .env
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=myapp
DB_USERNAME=root
DB_PASSWORD=
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
```

## Docker Compose (Development)

```yaml
services:
  app:
    build: .
    ports: ["8000:8000"]
    volumes: [".:/app"]
    depends_on: [mysql, redis]
  mysql:
    image: mysql:8
    environment:
      MYSQL_DATABASE: myapp
      MYSQL_ROOT_PASSWORD: secret
    ports: ["3306:3306"]
  redis:
    image: redis:7-alpine
    ports: ["6379:6379"]
```

## CI Pipeline

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan test --parallel
./vendor/bin/pint --test
```
