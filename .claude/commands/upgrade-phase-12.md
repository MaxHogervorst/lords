---
description: Phase 12 - Upgrade Laravel 11.x to 12.x (final Laravel upgrade)
project: true
---

You are helping upgrade a Laravel application from version 11.x to 12.x.

## Context

This is Phase 12 of a multi-phase upgrade from Laravel 5.3 to Laravel 12.x.
Phases 0-11 have been completed successfully.

Reference: https://laravel.com/docs/12.x/upgrade

## Requirements

- PHP >= 8.2.0 (same as Laravel 11)
- All Phase 11 changes must be committed
- This is the final Laravel version upgrade

## Your Task

Follow these steps to upgrade from Laravel 11.x to 12.x:

### 1. Pre-Upgrade Checks

- Verify current Laravel version is 11.x
- Check git status (working directory should be clean)
- Verify PHP 8.2+ is running: `docker-compose run --rm app php -v`
- Run tests to ensure baseline: `./test.sh tests/Feature/`
- Run linter: `./vendor/bin/phpstan analyse`

### 2. Update Dependencies

Update composer.json:
- "laravel/framework": "^12.0"
- "phpunit/phpunit": "^11.0"
- "pestphp/pest": "^3.0" (if using Pest)
- "laravel/tinker": "^2.10"
- "spatie/laravel-ignition": "^3.0"

Check third-party packages for Laravel 12 compatibility:
- maatwebsite/excel (update to latest)
- digitick/sepa-xml (verify compatibility)
- All other packages

Run: `docker-compose run --rm app composer update`

### 3. Major Changes to Address

#### a. UUIDs Changes

**UUIDv7 by Default:**
- Laravel 12 uses UUIDv7 by default (instead of UUIDv4)
- UUIDv7 is time-ordered and better for database indexing

If using `HasUuids` trait:
```php
// Old (UUIDv4)
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Model extends Model
{
    use HasUuids; // Now generates UUIDv7 by default
}

// If you need UUIDv4 specifically:
class Model extends Model
{
    use HasUuids;

    public function newUniqueId(): string
    {
        return (string) Str::uuid(); // UUIDv4
    }
}
```

**Review UUID Usage:**
```bash
docker-compose run --rm app grep -r "HasUuids" app/Models/
docker-compose run --rm app grep -r "Str::uuid" app/
docker-compose run --rm app grep -r "Str::orderedUuid" app/
```

#### b. Image Validation Updates

**SVG No Longer Included by Default:**

Image validation rules now exclude SVG by default (security):
```php
// Old (included SVG)
$request->validate([
    'image' => 'image',
]);

// New (excludes SVG by default)
$request->validate([
    'image' => 'image', // Does NOT include SVG
]);

// To include SVG explicitly:
$request->validate([
    'image' => 'image:jpeg,png,jpg,gif,svg',
]);
```

**Update Image Validation:**
```bash
docker-compose run --rm app grep -r "'image'" app/Http/
docker-compose run --rm app grep -r '"image"' app/Http/
```

#### c. Local Filesystem Changes

**Private Storage Directory:**
- New `storage/app/private` directory for private files
- Public files go in `storage/app/public`
- Review file storage logic

Update filesystem configuration if needed:
```php
// config/filesystems.php
'local' => [
    'driver' => 'local',
    'root' => storage_path('app/private'),
],

'public' => [
    'driver' => 'local',
    'root' => storage_path('app/public'),
    'url' => env('APP_URL').'/storage',
    'visibility' => 'public',
],
```

**Review File Storage:**
```bash
docker-compose run --rm app grep -r "Storage::disk" app/
docker-compose run --rm app grep -r "->store(" app/
```

#### d. Carbon 3.x

**Carbon Upgrade:**
- Laravel 12 uses Carbon 3.x
- Mostly backward compatible
- Some minor behavior changes

Test all date operations:
```bash
docker-compose run --rm app grep -r "Carbon::" app/
docker-compose run --rm app grep -r "now()" app/
docker-compose run --rm app grep -r "today()" app/
```

#### e. Validation Changes

Review validation rules:
- Some validation rules have updated behavior
- Check custom validation rules
- Test validation extensively

```bash
docker-compose run --rm app grep -r "->validate(" app/Http/Controllers/
docker-compose run --rm app find app/Rules -name "*.php"
```

### 4. Configuration Updates

Update config files from laravel/laravel 12.x:
- `config/filesystems.php` (private disk changes)
- Review all config files for new options
- Remove any deprecated configuration

### 5. Database and Migrations

Check migrations:
- Review any UUID-related migrations
- Ensure migrations are compatible
- Test migration rollback

```bash
docker-compose run --rm app php artisan migrate:status
```

### 6. File Storage Audit

Review all file storage locations:
```bash
# Check current file structure
docker-compose run --rm app ls -la storage/app/
docker-compose run --rm app ls -la storage/app/public/

# Review file upload controllers
docker-compose run --rm app grep -r "storeAs\|store\|put" app/Http/Controllers/
```

Migrate files to appropriate directories:
- Private files → `storage/app/private`
- Public files → `storage/app/public`

### 7. Security Review

With SVG exclusion from image validation:
- Review all image upload endpoints
- Ensure SVG uploads are intentional
- Add explicit SVG validation only where needed
- Security best practice: avoid SVG uploads unless required

### 8. Code Search and Updates

```bash
# UUID usage
docker-compose run --rm app grep -r "HasUuids\|Str::uuid\|Str::orderedUuid" app/

# Image validation
docker-compose run --rm app grep -r "validate.*image" app/

# File storage
docker-compose run --rm app grep -r "Storage::\|->store\|->storeAs" app/

# Carbon/date usage
docker-compose run --rm app grep -r "Carbon\|->toDateString\|->format" app/

# Validation rules
docker-compose run --rm app grep -r "->validate\|Validator::make" app/
```

### 9. Testing & Validation

Run comprehensive tests:
```bash
# Verify versions
docker-compose run --rm app php -v
docker-compose run --rm app php artisan --version

# Run linter
docker-compose run --rm app ./vendor/bin/phpstan analyse

# Run tests
docker-compose run --rm app ./test.sh tests/Feature/

# Specific tests for new changes:
# - Test UUID generation
# - Test image uploads
# - Test file storage (private vs public)
# - Test date operations
# - Test validation rules

# Manual testing checklist:
# - Test authentication (login/logout)
# - Test member CRUD operations
# - Test group management
# - Test product and order workflows
# - Test invoice generation
# - Test SEPA exports
# - Test Excel exports
# - Test file uploads (if any)
# - Test image uploads (if any)
# - Verify date displays correctly
# - Test all forms with validation
```

### 10. Performance Testing

Laravel 12 may have performance improvements:
```bash
# Test application performance
docker-compose run --rm app php artisan route:list
docker-compose run --rm app php artisan optimize
```

### 11. Commit Changes

If all tests pass and manual testing succeeds:
```bash
git add .
git commit -m "Phase 12 Complete: Upgrade to Laravel 12.x (Final Laravel Upgrade)

- Updated to Laravel 12.x
- Updated to UUIDv7 by default
- Updated image validation (SVG excluded by default)
- Updated filesystem configuration for private storage
- Updated to Carbon 3.x
- Updated all dependencies
- All tests passing

This completes the Laravel upgrade from 5.3 to 12.x!"
```

## Important Notes

- This is the **final Laravel version upgrade** (5.3 → 12.x complete!)
- UUIDv7 is a positive change for database performance
- SVG exclusion is a security improvement
- Private storage separation improves security
- Thoroughly test file storage and image uploads
- Review UUID generation in any custom code

## Laravel 12 Benefits

- UUIDv7 for better database performance
- Improved security (SVG exclusion)
- Better file organization (private vs public)
- Carbon 3.x improvements
- Latest framework features and security updates

## Rollback Plan

If issues arise:
```bash
git reset --hard HEAD~1
docker-compose run --rm app composer install
docker-compose restart app
```

## Next Steps

After successful completion:
- Update LARAVEL_UPGRADE_PLAN.md to mark Phase 12 as complete
- **Run `/upgrade-phase-13`** to upgrade PHP 8.2 → 8.4 (final phase!)
- Then proceed to final validation and deployment planning

## Milestone Achievement

🎉 **Laravel Upgrade Complete!**

You've successfully upgraded from Laravel 5.3 to 12.x - a journey spanning **7 major Laravel versions** and **multiple PHP versions**.

One more phase (PHP 8.2 → 8.4) and you'll have completed the entire modernization!
