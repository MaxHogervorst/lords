---
description: Phase 10 - Upgrade Laravel 9.x to 10.x (requires PHP 8.1)
project: true
---

You are helping upgrade a Laravel application from version 9.x to 10.x.

## Context

This is Phase 10 of a multi-phase upgrade from Laravel 5.3 to Laravel 12.x.
Phases 0-9 have been completed successfully.

Reference: https://laravel.com/docs/10.x/upgrade

## Requirements

- **PHP >= 8.1.0** (requires PHP upgrade)
- All Phase 9 changes must be committed
- This requires updating Docker configuration for PHP 8.1

## Your Task

Follow these steps to upgrade from Laravel 9.x to 10.x:

### 1. PHP 8.1 Upgrade

Update Dockerfile to use PHP 8.1:
```dockerfile
FROM php:8.1-fpm
```

Rebuild Docker containers:
```bash
docker-compose down
docker-compose build
docker-compose up -d
```

Review PHP 8.1 new features:
- Enums (native support)
- Readonly properties
- First-class callable syntax
- Fibers (async support)
- Array unpacking with string keys
- `new` in initializers

### 2. Pre-Upgrade Checks

- Verify current Laravel version is 9.x
- Check git status (working directory should be clean)
- Verify PHP 8.1 is running: `docker-compose run --rm app php -v`
- Run tests to ensure baseline: `./test.sh tests/Feature/`
- Run linter: `./vendor/bin/phpstan analyse`

### 3. Update Dependencies

Update composer.json:
- "php": "^8.1"
- "laravel/framework": "^10.0"
- "phpunit/phpunit": "^10.0"
- "spatie/laravel-ignition": "^2.0"
- "laravel/sanctum": "^3.2" (if using API authentication)
- "laravel/tinker": "^2.8"

Check third-party packages:
- maatwebsite/excel (update to latest)
- digitick/sepa-xml (verify PHP 8.1 compatibility)
- Form builder solution (if replaced laravelcollective/html)
- cartalyst/sentinel or its replacement

Run: `docker-compose run --rm app composer update`

### 4. Major Changes to Address

#### a. Native Type Declarations (CRITICAL)

Laravel 10 requires native return types on all framework method overrides.

Update method signatures with native return types:
```php
// Before
public function render($request, Exception $exception)

// After
public function render($request, Throwable $exception): Response
```

Common methods to update:
- Service providers: `register()`, `boot()`
- Middleware: `handle()`, `terminate()`
- Exception handler: `render()`, `report()`
- Custom validation rules
- Event listeners
- Job classes
- Command classes

Search for methods to update:
```bash
# Find service providers
docker-compose run --rm app find app/Providers -name "*.php"

# Find middleware
docker-compose run --rm app find app/Http/Middleware -name "*.php"

# Find custom validation rules
docker-compose run --rm app find app/Rules -name "*.php"
```

#### b. Invokable Validation Rules

Update custom validation rules if you have any:
```php
// Old style
class CustomRule implements Rule
{
    public function passes($attribute, $value) { }
    public function message() { }
}

// New style (optional but recommended)
class CustomRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (/* validation fails */) {
            $fail('The :attribute is invalid.');
        }
    }
}
```

#### c. Service Provider Registration

Review service provider registrations:
- Check deferred providers
- Update `$bindings` and `$singletons` properties if used
- Verify package discovery is working

#### d. Process Layer

If executing shell commands, use Process facade:
```php
// Instead of
exec('command');

// Use
use Illuminate\Support\Facades\Process;
Process::run('command');
```

Search for shell command execution:
```bash
docker-compose run --rm app grep -r "exec(" app/
docker-compose run --rm app grep -r "shell_exec" app/
docker-compose run --rm app grep -r "system(" app/
```

#### e. Database Schema Changes

Review schema builder changes:
- Spatial types improved
- JSON column methods updated
- Check any custom database drivers

#### f. Rate Limiting

Rate limiting API changes:
- Review any custom rate limiters
- Update rate limiting middleware if customized

### 5. Configuration Updates

Update config files from laravel/laravel 10.x:
- `config/sanctum.php` (if using API)
- Review all config files for new options
- Check for deprecated config values

### 6. Migration from laravelcollective/html (if not done)

If still using laravelcollective/html, must replace now:

**Option 1: Spatie/laravel-html**
```bash
docker-compose run --rm app composer require spatie/laravel-html
```

**Option 2: Blade Components**
Create reusable form components.

**Option 3: Manual HTML**
Convert Form::open(), Form::text(), etc. to plain HTML.

### 7. Leverage PHP 8.1 Features (Optional)

Consider using new PHP 8.1 features:
- Enums for status values, types, etc.
- Readonly properties for value objects
- First-class callables: `$fn = strlen(...)` instead of `Closure::fromCallable('strlen')`

### 8. Code Search and Updates

```bash
# Find methods that need return types
docker-compose run --rm app grep -r "public function register(" app/
docker-compose run --rm app grep -r "public function boot(" app/
docker-compose run --rm app grep -r "public function handle(" app/

# Find shell command usage
docker-compose run --rm app grep -r "exec\|shell_exec\|system" app/

# Find custom validation rules
docker-compose run --rm app find app -name "*Rule.php"
```

### 9. Testing & Validation

Run comprehensive tests:
```bash
# Verify PHP version
docker-compose run --rm app php -v

# Run linter (will catch missing return types)
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
# - Test all forms (especially if migrated from laravelcollective/html)
# - Test rate-limited endpoints (if any)
# - Test file operations
```

### 10. Commit Changes

If all tests pass and manual testing succeeds:
```bash
git add .
git commit -m "Phase 10 Complete: Upgrade to Laravel 10.x and PHP 8.1

- Upgraded to PHP 8.1
- Updated to Laravel 10.x
- Added native return types to all framework method overrides
- Updated validation rules
- Migrated from laravelcollective/html (if applicable)
- Updated all dependencies
- All tests passing"
```

## Important Notes

- Native return types are **required** - code won't work without them
- Use PHPStan/IDE to help find methods needing type declarations
- laravelcollective/html must be replaced in this version
- PHP 8.1 enums are great for refactoring status/type fields
- This is a significant upgrade - thorough testing required
- Consider enum refactoring for better type safety

## PHP 8.1 Benefits

- Native enums for type-safe constants
- Readonly properties for immutable objects
- Better performance improvements
- First-class callable syntax
- Fibers for advanced async operations

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
- Update LARAVEL_UPGRADE_PLAN.md to mark Phase 10 as complete
- Consider refactoring to use PHP 8.1 enums
- Run `/upgrade-phase-11` to continue to Laravel 11.x
