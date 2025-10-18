# Approved Docker Commands

This file tracks commands that the user has given permission to execute without asking for approval.

## Docker Compose Commands

```bash
# Build and manage containers
docker-compose build
docker-compose up -d
docker-compose down
docker-compose ps
docker-compose logs

# Volume management
docker volume rm lords_dbdata
```

## Docker Compose Run/Exec Commands

```bash
# Composer operations
docker-compose run --rm app composer install
docker-compose run --rm app composer dump-autoload

# Artisan commands
docker-compose exec -T app php artisan key:generate
docker-compose exec -T app php artisan migrate
docker-compose exec -T app php artisan migrate --force

# Testing
docker-compose exec -T app vendor/bin/phpunit
docker-compose exec -T app vendor/bin/phpunit --coverage-text

# Code quality tools
docker-compose exec -T app vendor/bin/pint
docker-compose exec -T app vendor/bin/phpstan analyze
```

## Notes
- All commands use Docker containers, not local PHP/Composer
- `-T` flag is used for non-interactive execution
- `--rm` flag removes containers after execution
