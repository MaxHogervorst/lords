---
description: Phase 9 - Upgrade Laravel 8.x to 9.x (requires PHP 8.0)
project: true
---

You are helping upgrade a Laravel application from version 8.x to 9.x.

## Context

This is Phase 9 of a multi-phase upgrade from Laravel 5.3 to Laravel 12.x.
Phases 0-8 have been completed successfully.

Reference: https://laravel.com/docs/9.x/upgrade

## Requirements

- **PHP >= 8.0.0** (MAJOR VERSION JUMP)
- All Phase 8 changes must be committed
- This requires updating Docker configuration for PHP 8.0

## Your Task

Follow these steps to upgrade from Laravel 8.x to 9.x:

### 1. PHP 8.0 Upgrade (CRITICAL FIRST STEP)

Update Dockerfile to use PHP 8.0:
```dockerfile
FROM php:8.0-fpm
```

Update docker-compose.yml if needed.

Rebuild Docker containers:
```bash
docker-compose down
docker-compose build
docker-compose up -d
```

Review PHP 8.0 breaking changes:
- Named arguments support
- Union types available
- Nullsafe operator available
- Match expressions available
- Attributes (annotations) available
- Stricter type checking
- Several deprecated features removed

### 2. Pre-Upgrade Checks

- Verify current Laravel version is 8.x
- Check git status (working directory should be clean)
- Verify PHP 8.0 is running: `docker-compose run --rm app php -v`
- Run tests to ensure baseline: `./test.sh tests/Feature/`
- Run linter: `./vendor/bin/phpstan analyse`

### 3. Update Dependencies

Update composer.json:
- "php": "^8.0"
- "laravel/framework": "^9.0"
- "phpunit/phpunit": "^9.5.10"
- "nunomaduro/collision": "^6.1"
- "spatie/laravel-ignition": "^1.0"
- "laravel/tinker": "^2.7"

Remove facade/ignition, add spatie/laravel-ignition.

Check third-party packages:
- laravelcollective/html (likely need alternative - may not support Laravel 9)
- cartalyst/sentinel (verify PHP 8.0 + Laravel 9 compatibility)
- maatwebsite/excel (update to latest 3.x)
- digitick/sepa-xml (verify PHP 8.0 compatibility)

Run: `docker-compose run --rm app composer update`

### 4. Major Changes to Address

#### a. Symfony Mailer (replaces SwiftMailer)

Update mail configuration:
- Review `config/mail.php`
- Update any SwiftMailer-specific code
- Update mail sending logic if using SwiftMailer directly
- Test email functionality thoroughly

Search for SwiftMailer usage:
```bash
docker-compose run --rm app grep -r "Swift_" app/
docker-compose run --rm app grep -r "SwiftMailer" app/
```

#### b. Flysystem 3.x

Update filesystem disk configurations:
- Review `config/filesystems.php`
- Update any direct Flysystem usage
- Test file upload/download functionality

```bash
# Find direct Flysystem usage
docker-compose run --rm app grep -r "Flysystem" app/
```

#### c. Anonymous Migrations (Optional)

Optionally convert migrations to anonymous classes:
- Old: `class CreateUsersTable extends Migration`
- New: `return new class extends Migration`
- Prevents migration class name conflicts
- Not required but recommended for new migrations

#### d. Improved Validation

- String validation rules now trim by default
- Review any validation that depends on whitespace
- Check form validation in controllers

#### e. PHP 8.0 Specific Updates

Update code to leverage PHP 8.0 features:
- Use nullsafe operator where appropriate: `$user?->profile?->name`
- Consider using named arguments for clarity
- Review any code that might break with stricter type checking
- Fix any `str_*` function issues with named arguments

### 5. Configuration Updates

Copy new config files from laravel/laravel 9.x:
- `config/mail.php` (major changes for Symfony Mailer)
- `config/filesystems.php` (Flysystem 3.x updates)
- Review all config files for new options

### 6. Third-Party Package Strategy

#### laravelcollective/html
If not supporting Laravel 9:
- Option 1: Use spatie/laravel-html
- Option 2: Convert to Blade components
- Option 3: Manual HTML forms
- Decision: Choose based on form complexity

#### cartalyst/sentinel
- Verify compatibility with Laravel 9 and PHP 8.0
- If not compatible, consider migration to:
  - Laravel Fortify
  - Laravel Sanctum
  - Laravel Breeze/Jetstream

### 7. Code Search and Updates

```bash
# Find PHP 7.x specific code that might break
docker-compose run --rm app grep -r "create_function" app/
docker-compose run --rm app grep -r "each(" app/

# Check for SwiftMailer usage
docker-compose run --rm app grep -r "Swift" app/ config/

# Check for old validation patterns
docker-compose run --rm app grep -r "->validate(" app/Http/Controllers/
```

### 8. Testing & Validation

Run comprehensive tests:
```bash
# Verify PHP version
docker-compose run --rm app php -v

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
# - Test email sending (if applicable)
# - Test file uploads/downloads
# - Test all forms
```

### 9. Commit Changes

If all tests pass and manual testing succeeds:
```bash
git add .
git commit -m "Phase 9 Complete: Upgrade to Laravel 9.x and PHP 8.0

- Upgraded to PHP 8.0
- Updated to Laravel 9.x
- Migrated from SwiftMailer to Symfony Mailer
- Updated to Flysystem 3.x
- Updated all dependencies
- All tests passing"
```

## Important Notes

- **PHP 8.0 is a major version** - thoroughly test everything
- SwiftMailer to Symfony Mailer is a major change - test emails
- Flysystem 3.x has breaking changes - test file operations
- laravelcollective/html likely needs replacement
- Consider this a high-risk upgrade due to PHP version jump
- Test on staging environment before production

## PHP 8.0 Benefits

- Better performance (JIT compiler)
- Improved type system (union types, mixed type)
- Nullsafe operator for cleaner code
- Named arguments for better readability
- Match expressions (improved switch)
- Attributes (PHP's annotations)

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
- Update LARAVEL_UPGRADE_PLAN.md to mark Phase 9 as complete
- Consider a staging deployment for extended testing
- Run `/upgrade-phase-10` to continue to Laravel 10.x
