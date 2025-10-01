---
description: Phase 11 - Upgrade Laravel 10.x to 11.x (requires PHP 8.2)
project: true
---

You are helping upgrade a Laravel application from version 10.x to 11.x.

## Context

This is Phase 11 of a multi-phase upgrade from Laravel 5.3 to Laravel 12.x.
Phases 0-10 have been completed successfully.

Reference: https://laravel.com/docs/11.x/upgrade

## Requirements

- **PHP >= 8.2.0** (requires PHP upgrade)
- All Phase 10 changes must be committed
- This requires updating Docker configuration for PHP 8.2

## Your Task

Follow these steps to upgrade from Laravel 10.x to 11.x:

### 1. PHP 8.2 Upgrade

Update Dockerfile to use PHP 8.2:
```dockerfile
FROM php:8.2-fpm
```

Rebuild Docker containers:
```bash
docker-compose down
docker-compose build
docker-compose up -d
```

Review PHP 8.2 new features:
- Readonly classes
- Disjunctive Normal Form (DNF) types
- Constants in traits
- Deprecated dynamic properties
- Sensitive parameter redaction

### 2. Pre-Upgrade Checks

- Verify current Laravel version is 10.x
- Check git status (working directory should be clean)
- Verify PHP 8.2 is running: `docker-compose run --rm app php -v`
- Run tests to ensure baseline: `./test.sh tests/Feature/`
- Run linter: `./vendor/bin/phpstan analyse`

### 3. Update Dependencies

Update composer.json:
- "php": "^8.2"
- "laravel/framework": "^11.0"
- "phpunit/phpunit": "^10.5"
- Consider adding: "pestphp/pest": "^2.34" (modern testing framework)
- "laravel/tinker": "^2.9"
- "spatie/laravel-ignition": "^2.4"

Check third-party packages:
- maatwebsite/excel (update to latest)
- digitick/sepa-xml (verify PHP 8.2 compatibility)
- All other packages for Laravel 11 support

Run: `docker-compose run --rm app composer update`

### 4. Major Changes to Address

#### a. Streamlined Application Structure

Laravel 11 has a simplified application structure:

**Service Providers:**
- Many service providers are now optional
- Default `app/Providers/AppServiceProvider.php` consolidates most logic
- Can remove: `AuthServiceProvider`, `BroadcastServiceProvider`, `EventServiceProvider`, `RouteServiceProvider`
- Move their logic to `AppServiceProvider` if needed

**Middleware:**
- Middleware is now registered in `bootstrap/app.php`
- `app/Http/Kernel.php` is replaced by `bootstrap/app.php`
- Update middleware registration:

```php
// bootstrap/app.php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\YourMiddleware::class,
        ]);
    })
    ->create();
```

#### b. Configuration Changes

**Unified Configuration:**
- Many config files are now optional
- Configuration can be done in `bootstrap/app.php`
- Review which config files you actually need
- Can simplify: `config/cors.php`, `config/view.php`, etc.

Update `config/app.php`:
- Remove unnecessary service providers
- Simplify configuration

#### c. Model Casts Updates

New casting system improvements:
```php
// Old
protected $casts = [
    'is_active' => 'boolean',
    'created_at' => 'datetime',
];

// New (more options available)
protected $casts = [
    'is_active' => 'boolean',
    'created_at' => 'datetime:Y-m-d H:i:s',
    'metadata' => AsArrayObject::class,
];
```

Review all model casts:
```bash
docker-compose run --rm app grep -r "protected \$casts" app/Models/
```

#### d. Validation Updates

- New validation rules available
- Review custom validation logic
- Check validation method signatures

#### e. Eloquent Improvements

- Relationship changes and improvements
- Eager loading optimizations
- Review complex queries

#### f. Rate Limiting

Rate limiting improvements:
- Review any custom rate limiters
- Update if using advanced rate limiting

### 5. Bootstrap/App.php Migration

Create or update `bootstrap/app.php` with new structure:
```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register your middleware
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Configure exception handling
    })
    ->create();
```

Move logic from:
- `app/Http/Kernel.php` → `bootstrap/app.php`
- Various service providers → `AppServiceProvider.php`

### 6. Service Provider Consolidation

Review and consolidate service providers:

**Keep:**
- `AppServiceProvider` (main provider)
- Any custom business logic providers

**Can Remove (move logic to AppServiceProvider):**
- `AuthServiceProvider` (move gate definitions)
- `EventServiceProvider` (use auto-discovery or manual registration)
- `RouteServiceProvider` (routing now in bootstrap/app.php)
- `BroadcastServiceProvider` (if not using broadcasting)

### 7. Configuration Cleanup

Optional: Simplify configuration by removing unnecessary config files and moving to `bootstrap/app.php`:

```php
// Example: Configure CORS in bootstrap/app.php
return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->cors(
            allowedOrigins: ['https://example.com'],
        );
    })
```

### 8. PHP 8.2 Specific Updates

Address PHP 8.2 changes:
- Fix any deprecated dynamic properties:
  ```php
  // If you get warnings, declare properties:
  class MyClass {
      private mixed $dynamicProp; // Declare it
  }

  // Or allow dynamic properties:
  #[\AllowDynamicProperties]
  class MyClass { }
  ```

- Review any DNF type opportunities
- Use readonly classes where appropriate

### 9. Code Search and Updates

```bash
# Find service providers
docker-compose run --rm app ls -la app/Providers/

# Find middleware registrations in old Kernel.php
docker-compose run --rm app cat app/Http/Kernel.php

# Find model casts to review
docker-compose run --rm app grep -r "protected \$casts" app/

# Check for dynamic properties
docker-compose run --rm app grep -r "AllowDynamicProperties" app/
```

### 10. Testing & Validation

Run comprehensive tests:
```bash
# Verify PHP version
docker-compose run --rm app php -v

# Run linter
docker-compose run --rm app ./vendor/bin/phpstan analyse

# Run tests
docker-compose run --rm app ./test.sh tests/Feature/

# Test middleware functionality
docker-compose run --rm app php artisan route:list

# Manual testing checklist:
# - Test authentication (login/logout)
# - Test member CRUD operations
# - Test group management
# - Test product and order workflows
# - Test invoice generation
# - Test SEPA exports
# - Test Excel exports
# - Test all middleware (auth, CORS, etc.)
# - Verify error handling
# - Test rate limiting (if implemented)
```

### 11. Commit Changes

If all tests pass and manual testing succeeds:
```bash
git add .
git commit -m "Phase 11 Complete: Upgrade to Laravel 11.x and PHP 8.2

- Upgraded to PHP 8.2
- Updated to Laravel 11.x
- Migrated to streamlined application structure
- Consolidated service providers into AppServiceProvider
- Migrated middleware registration to bootstrap/app.php
- Simplified configuration files
- Updated model casts
- All tests passing"
```

## Important Notes

- **Major architectural changes** - application structure is different
- Middleware registration moved from Kernel to bootstrap/app.php
- Many service providers are now optional
- Consider this a significant refactor, not just an upgrade
- The new structure is much simpler but requires migration effort
- PHP 8.2 deprecates dynamic properties - fix any warnings

## PHP 8.2 Benefits

- Readonly classes for better immutability
- DNF types for complex type unions
- Performance improvements
- Sensitive parameter redaction in stack traces
- Better null safety

## Laravel 11 Benefits

- Simpler application structure
- Fewer files to maintain
- Better performance
- Improved developer experience
- More modern conventions

## Rollback Plan

If issues arise:
```bash
git reset --hard HEAD~1
# Rebuild containers with old PHP version
docker-compose down
# Restore old Dockerfile
docker-compose build
docker-compose up -d
docker-compose run --rm app composer install
```

## Next Steps

After successful completion:
- Update LARAVEL_UPGRADE_PLAN.md to mark Phase 11 as complete
- Review the simplified structure and remove unused files
- Consider refactoring to use readonly classes
- Run `/upgrade-phase-12` to continue to Laravel 12.x (final Laravel upgrade)
