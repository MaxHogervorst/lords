---
description: Phase 7 - Upgrade Laravel 6.x to 7.x
project: true
---

You are helping upgrade a Laravel application from version 6.x to 7.x.

## Context

This is Phase 7 of a multi-phase upgrade from Laravel 5.3 to Laravel 12.x.
Phases 0-6 have been completed successfully.

Reference: https://laravel.com/docs/7.x/upgrade

## Requirements

- PHP >= 7.2.5
- All Phase 6 changes must be committed

## Your Task

Follow these steps to upgrade from Laravel 6.x to 7.x:

### 1. Pre-Upgrade Checks

- Verify current Laravel version is 6.x
- Check git status (working directory should be clean)
- Run tests to ensure baseline: `./test.sh tests/Feature/`
- Run linter: `./vendor/bin/phpstan analyse`

### 2. Update Dependencies

Update composer.json:
- "laravel/framework": "^7.0"
- "phpunit/phpunit": "^8.5|^9.0"
- "facade/ignition": "^2.0"
- "laravel/tinker": "^2.0"
- Check laravelcollective/html compatibility (^6.2 or latest)

Run: `docker-compose run --rm app composer update`

### 3. Major Changes to Address

#### a. Authentication Scaffolding
- Review if using Laravel's default auth (currently using Sentinel)
- Verify Sentinel package compatibility with Laravel 7.x
- No changes needed if Sentinel is working

#### b. CORS
- Laravel 7 includes built-in CORS support
- Check if using `barryvdh/laravel-cors` package
- If yes, consider replacing with built-in CORS
- Update `config/cors.php` configuration
- Update middleware if needed

#### c. Date Handling
- Review date serialization in models
- Update `$dates` properties to use `$casts` with 'datetime'
- Search for models using `$dates`: `grep -r "protected \$dates" app/`

#### d. Models
- Add `$primaryKey` type property where needed
- Review `$keyType` property if using non-integer keys
- Check models: Group, Member, Order, Product, Invoice, etc.

#### e. Factories
- Begin migration to class-based factories (optional in 7.x, required in 8.x)
- Current factories are in database/factories/ModelFactory.php
- Consider starting conversion to new format

### 4. Code Changes

Search and update the following:

```bash
# Find models with $dates property
docker-compose run --rm app grep -r "protected \$dates" app/

# Find any direct Guzzle usage (consider Laravel HTTP Client)
docker-compose run --rm app grep -r "new GuzzleHttp" app/
```

### 5. Configuration Updates

- Copy new config files from laravel/laravel 7.x if needed
- Review and update `config/cors.php` if replacing CORS package
- Review `config/mail.php`
- Review `config/session.php`

### 6. Optional Features to Consider

- HTTP Client instead of Guzzle directly
- Fluent String operations
- Custom Eloquent casts

### 7. Testing & Validation

Run comprehensive tests:
```bash
# Run linter
docker-compose run --rm app ./vendor/bin/phpstan analyse

# Run tests
docker-compose run --rm app ./test.sh tests/Feature/

# Manual testing checklist:
# - Test authentication (login/logout)
# - Test member CRUD operations
# - Test group management
# - Test product and order workflows
# - Test invoice generation
# - Test SEPA exports
# - Test Excel exports
```

### 8. Commit Changes

If all tests pass and manual testing succeeds:
```bash
git add .
git commit -m "Phase 7 Complete: Upgrade to Laravel 7.x"
```

## Important Notes

- Test CORS configuration if making changes
- Pay special attention to date handling in models
- Sentinel authentication should remain compatible
- Run full test suite before committing
- Test all critical business functionality manually

## Rollback Plan

If issues arise:
```bash
git reset --hard HEAD~1
docker-compose run --rm app composer install
docker-compose restart app
```

## Next Steps

After successful completion:
- Update LARAVEL_UPGRADE_PLAN.md to mark Phase 7 as complete
- Run `/upgrade-phase-8` to continue to Laravel 8.x
