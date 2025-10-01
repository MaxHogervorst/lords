---
description: Phase 8 - Upgrade Laravel 7.x to 8.x
project: true
---

You are helping upgrade a Laravel application from version 7.x to 8.x.

## Context

This is Phase 8 of a multi-phase upgrade from Laravel 5.3 to Laravel 12.x.
Phases 0-7 have been completed successfully.

Reference: https://laravel.com/docs/8.x/upgrade

## Requirements

- PHP >= 7.3.0
- All Phase 7 changes must be committed
- Models directory migration is a major change in this version

## Your Task

Follow these steps to upgrade from Laravel 7.x to 8.x:

### 1. Pre-Upgrade Checks

- Verify current Laravel version is 7.x
- Check git status (working directory should be clean)
- Run tests to ensure baseline: `./test.sh tests/Feature/`
- Run linter: `./vendor/bin/phpstan analyse`
- Update Docker to use PHP 7.3+ if needed

### 2. Update Dependencies

Update composer.json:
- "laravel/framework": "^8.0"
- "phpunit/phpunit": "^9.3"
- "facade/ignition": "^2.5"
- "laravel/tinker": "^2.5"
- "nunomaduro/collision": "^5.0"
- "guzzlehttp/guzzle": "^7.0.1"
- Check laravelcollective/html compatibility (may need alternative)

Run: `docker-compose run --rm app composer update`

### 3. Major Changes to Address

#### a. Models Directory Migration (CRITICAL)

Move all models from `app/` to `app/Models/`:

```bash
# Create Models directory
mkdir -p app/Models

# Move models
mv app/*.php app/Models/

# Move back non-model files
mv app/Models/Http app/
mv app/Models/Console app/
mv app/Models/Exceptions app/
mv app/Models/Providers app/
```

Update namespaces in all model files:
- Change `namespace App;` to `namespace App\Models;`
- Update models: Group, Member, Order, Product, Invoice, InvoiceLine, InvoiceProductPrice

Update imports throughout the codebase:
- Controllers: Update `use App\ModelName` to `use App\Models\ModelName`
- Tests: Update model imports
- Factories: Update model references
- Config files: Update any model references

Search and replace:
```bash
# Find all imports to update
docker-compose run --rm app grep -r "use App\\\\Group" .
docker-compose run --rm app grep -r "use App\\\\Member" .
docker-compose run --rm app grep -r "use App\\\\Order" .
docker-compose run --rm app grep -r "use App\\\\Product" .
docker-compose run --rm app grep -r "use App\\\\Invoice" .
```

#### b. Class-Based Factories (REQUIRED)

Convert factories to class-based format:

Create factory classes in `database/factories/`:
- GroupFactory.php
- MemberFactory.php
- OrderFactory.php
- ProductFactory.php
- InvoiceFactory.php

Update factory calls in tests:
- Old: `factory(Member::class)->create()`
- New: `Member::factory()->create()`

#### c. Route Caching

Ensure all routes use controller classes (not closures):
```bash
# Check routes file
docker-compose run --rm app cat routes/web.php

# Test route caching
docker-compose run --rm app php artisan route:cache
docker-compose run --rm app php artisan route:clear
```

#### d. Pagination Views

- Laravel 8 uses Tailwind by default
- If using Bootstrap, update pagination calls:
  - `$items->links()` → `$items->links('pagination::bootstrap-4')`
- Check all controller methods that return paginated results

#### e. Maintenance Mode

- Update maintenance mode usage if applicable
- New secret-based bypass available

#### f. Queue System

Update jobs table migration if it exists:
```bash
# Check for jobs migration
docker-compose run --rm app ls database/migrations/*_create_jobs_table.php

# Add UUID column if needed
```

### 4. Configuration Updates

- Copy new config files from laravel/laravel 8.x repo
- Update `config/queue.php`
- Update `config/cors.php`
- Update `config/database.php`
- Review all config files for new options

### 5. Third-Party Package Updates

Check package compatibility:
- laravelcollective/html (may not have Laravel 8 support)
  - Consider alternatives: spatie/laravel-html, Blade components, or manual forms
- cartalyst/sentinel (verify compatibility)
- maatwebsite/excel (update to v3.1+)
- digitick/sepa-xml (verify compatibility)
- barryvdh/laravel-debugbar (update)

### 6. Code Search and Updates

```bash
# Find deprecated method usage
docker-compose run --rm app grep -r "assertJsonStructure" tests/
docker-compose run --rm app grep -r "factory(" tests/

# Check for closures in routes
docker-compose run --rm app grep "Route::" routes/web.php | grep "function"
```

### 7. Testing & Validation

Run comprehensive tests:
```bash
# Run linter
docker-compose run --rm app ./vendor/bin/phpstan analyse

# Run tests
docker-compose run --rm app ./test.sh tests/Feature/

# Test route caching
docker-compose run --rm app php artisan route:cache
docker-compose run --rm app ./test.sh tests/Feature/
docker-compose run --rm app php artisan route:clear

# Manual testing checklist:
# - Test authentication (login/logout)
# - Test member CRUD operations
# - Test group management
# - Test product and order workflows
# - Test invoice generation
# - Test SEPA exports
# - Test Excel exports
# - Test pagination on list pages
```

### 8. Commit Changes

If all tests pass and manual testing succeeds:
```bash
git add .
git commit -m "Phase 8 Complete: Upgrade to Laravel 8.x

- Moved models to app/Models directory
- Converted to class-based factories
- Updated all imports and namespaces
- Updated dependencies to Laravel 8.x
- All tests passing"
```

## Important Notes

- Models directory migration is mandatory - update ALL imports
- Factory migration is required - update ALL test files
- Test route caching before committing
- Verify pagination styling matches your frontend
- laravelcollective/html may need to be replaced
- This is a major version with significant changes

## Rollback Plan

If issues arise:
```bash
git reset --hard HEAD~1
docker-compose run --rm app composer install
docker-compose restart app
```

## Next Steps

After successful completion:
- Update LARAVEL_UPGRADE_PLAN.md to mark Phase 8 as complete
- Run `/upgrade-phase-9` to continue to Laravel 9.x
