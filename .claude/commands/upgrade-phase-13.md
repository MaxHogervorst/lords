---
description: Phase 13 - Upgrade PHP 8.2 to 8.4 (final upgrade phase)
project: true
---

You are helping upgrade PHP from version 8.2 to 8.4 in a Laravel 12.x application.

## Context

This is Phase 13 - the **FINAL PHASE** of the upgrade from Laravel 5.3 to Laravel 12.x.
All Laravel upgrades (Phases 0-12) have been completed successfully.

## Requirements

- Laravel 12.x must be installed
- All Phase 12 changes must be committed
- This is the final PHP version upgrade

## Your Task

Follow these steps to upgrade from PHP 8.2 to 8.4:

### 1. Review PHP 8.3 and 8.4 Changes

**PHP 8.3 Features (if skipping from 8.2):**
- Typed class constants
- `json_validate()` function
- Dynamic class constant fetch
- `#[\Override]` attribute
- Randomizer additions
- Negative indices in arrays

**PHP 8.4 Features:**
- Property hooks (game changer!)
- Asymmetric visibility
- New `array_*` functions
- Lazy objects
- JIT improvements
- PDO driver improvements

**Deprecations to Address:**
- Implicitly nullable parameter types deprecated
- Some dynamic property usages
- Various minor deprecations

### 2. Update Dockerfile

Update Dockerfile to use PHP 8.4:
```dockerfile
FROM php:8.4-fpm

# Copy existing PHP extensions installation
# Ensure all required extensions are installed
RUN docker-php-ext-install pdo pdo_mysql

# Add any other extensions you need
# RUN docker-php-ext-install gd zip etc.
```

Rebuild Docker containers:
```bash
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### 3. Pre-Upgrade Validation

Before updating PHP:
```bash
# Verify current versions
docker-compose run --rm app php -v
docker-compose run --rm app php artisan --version

# Run full test suite on PHP 8.2
docker-compose run --rm app ./test.sh tests/Feature/

# Run linter
docker-compose run --rm app ./vendor/bin/phpstan analyse
```

### 4. Update Composer Dependencies

Update composer.json:
```json
{
    "require": {
        "php": "^8.4"
    }
}
```

Update all packages to PHP 8.4 compatible versions:
```bash
docker-compose run --rm app composer update
```

If any packages fail, check for alternatives or updates:
- maatwebsite/excel
- digitick/sepa-xml
- Any other packages

### 5. Fix PHP 8.4 Deprecations

#### a. Implicitly Nullable Parameter Types

**Deprecated:**
```php
function foo(string $param = null) { } // DEPRECATED
```

**Fixed:**
```php
function foo(?string $param = null) { } // Correct
```

Search and fix:
```bash
docker-compose run --rm app grep -rn " = null)" app/ --include="*.php"
```

#### b. Review Dynamic Properties

Ensure all dynamic properties are declared or use `#[AllowDynamicProperties]`:
```bash
docker-compose run --rm app grep -r "AllowDynamicProperties" app/
```

#### c. Check Deprecated Functions

Run deprecation checks:
```bash
# Enable E_DEPRECATED in php.ini or .env
docker-compose run --rm app php artisan serve --verbose
```

### 6. Leverage PHP 8.4 Features (Optional)

#### Property Hooks (Recommended)

Replace getters/setters with property hooks:
```php
// Old style
class Member
{
    private string $firstName;
    private string $lastName;

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }
}

// New style with property hooks
class Member
{
    public string $firstName;
    public string $lastName;

    public string $fullName {
        get => $this->firstName . ' ' . $this->lastName;
    }
}
```

#### Asymmetric Visibility

```php
// Old style
private string $id;

public function getId(): string
{
    return $this->id;
}

// New style
public private(set) string $id; // Public read, private write
```

#### Override Attribute

Add to methods that override parent methods:
```php
class Controller extends BaseController
{
    #[\Override]
    public function middleware(): array
    {
        return ['auth'];
    }
}
```

### 7. Code Quality Improvements

Update PHPStan to latest version:
```bash
docker-compose run --rm app composer require --dev phpstan/phpstan:^2.0
```

Update PHPStan configuration for PHP 8.4:
```yaml
# phpstan.neon
parameters:
    level: 8
    phpVersion: 80400
```

Consider Laravel Pint for code styling:
```bash
docker-compose run --rm app composer require --dev laravel/pint
docker-compose run --rm app ./vendor/bin/pint
```

### 8. Performance Optimizations

PHP 8.4 includes JIT improvements:

Update `php.ini` in Dockerfile (optional):
```ini
; Enable JIT
opcache.enable=1
opcache.jit_buffer_size=100M
opcache.jit=tracing
```

### 9. Testing & Validation

Run comprehensive tests:
```bash
# Verify PHP 8.4 is running
docker-compose run --rm app php -v
docker-compose run --rm app php -m # Check modules

# Run linter with PHP 8.4 checks
docker-compose run --rm app ./vendor/bin/phpstan analyse --level max

# Run full test suite
docker-compose run --rm app ./test.sh tests/Feature/

# Check for deprecation warnings
docker-compose run --rm app php artisan test --log-junit results.xml

# Performance benchmarks (optional)
docker-compose run --rm app php artisan optimize
docker-compose run --rm app php artisan route:cache
docker-compose run --rm app php artisan config:cache

# Manual testing checklist:
# - Test authentication (login/logout)
# - Test member CRUD operations
# - Test group management
# - Test product and order workflows
# - Test invoice generation
# - Test SEPA exports
# - Test Excel exports
# - Test all file operations
# - Monitor PHP error logs
```

### 10. Performance Testing

Benchmark the application:
```bash
# Clear all caches
docker-compose run --rm app php artisan optimize:clear

# Rebuild optimizations
docker-compose run --rm app php artisan optimize

# Test critical endpoints
# Consider using Apache Bench or similar tool
```

### 11. Security Audit

Run security checks:
```bash
# Composer security audit
docker-compose run --rm app composer audit

# Check for known vulnerabilities
docker-compose run --rm app composer outdated

# Review Laravel security updates
docker-compose run --rm app php artisan about
```

### 12. Documentation Updates

Update project documentation:
- Update README.md with PHP 8.4 requirement
- Update deployment documentation
- Document any new PHP 8.4 features used
- Update development environment setup guide

### 13. Final Validation Checklist

Before committing:

**Code Quality:**
- [ ] All tests passing
- [ ] Linter passing with zero errors
- [ ] No deprecation warnings
- [ ] Code coverage maintained or improved

**Performance:**
- [ ] Application runs smoothly
- [ ] No performance regressions
- [ ] Optimizations enabled

**Security:**
- [ ] No security vulnerabilities
- [ ] All dependencies up to date
- [ ] Security audit passed

**Functionality:**
- [ ] All features working
- [ ] Authentication working
- [ ] SEPA exports working
- [ ] Excel exports working
- [ ] File operations working

### 14. Commit Changes

If all validations pass:
```bash
git add .
git commit -m "Phase 13 Complete: Upgrade to PHP 8.4 (FINAL UPGRADE!)

- Upgraded to PHP 8.4
- Fixed all PHP 8.4 deprecations
- Updated all dependencies for PHP 8.4
- Applied property hooks and modern PHP features
- Performance optimizations enabled
- All tests passing
- Security audit completed

🎉 MIGRATION COMPLETE: Laravel 5.3 → 12.x, PHP 5.6 → 8.4
Full stack modernization achieved!"
```

### 15. Tag Release

Consider tagging this milestone:
```bash
git tag -a v12.0-php84 -m "Complete migration to Laravel 12.x and PHP 8.4"
git push origin v12.0-php84
```

## PHP 8.4 Benefits

- **Property Hooks:** Cleaner code, less boilerplate
- **Asymmetric Visibility:** Better encapsulation
- **Performance:** JIT improvements, faster execution
- **Array Functions:** New convenience functions
- **Modern Syntax:** Latest PHP features
- **Better Type Safety:** Enhanced type system

## Rollback Plan

If critical issues arise:
```bash
git reset --hard HEAD~1
# Update Dockerfile back to PHP 8.2
docker-compose build
docker-compose up -d
docker-compose run --rm app composer install
```

## Next Steps - Deployment Preparation

Now that all upgrades are complete:

### 1. Staging Environment Testing
```bash
# Deploy to staging
# Run extended testing
# User acceptance testing
# Load testing
```

### 2. Performance Optimization
- Database query optimization
- Cache configuration
- CDN setup (if applicable)
- Queue configuration

### 3. Documentation
- Update all documentation
- Create deployment runbook
- Document rollback procedures
- Update API documentation

### 4. Production Deployment Planning
- Schedule maintenance window
- Prepare rollback plan
- Notify stakeholders
- Backup production database
- Prepare monitoring

### 5. Post-Deployment
- Monitor error logs
- Monitor performance metrics
- Gather user feedback
- Document lessons learned

## Milestone Achievement 🎉

**CONGRATULATIONS! FULL MIGRATION COMPLETE!**

You have successfully completed:
- ✅ Laravel 5.3 → 5.4 → 5.5 → 5.6 → 5.7 → 5.8 → 6.x → 7.x → 8.x → 9.x → 10.x → 11.x → 12.x
- ✅ PHP 5.6 → 7.0 → 7.1 → 7.2 → 7.3 → 8.0 → 8.1 → 8.2 → 8.4
- ✅ MySQL 5.7 → 8.0
- ✅ Modern architecture and patterns
- ✅ Comprehensive test suite
- ✅ Code quality tools
- ✅ Docker containerization

**Upgrade Statistics:**
- 13 phases completed
- 7 Laravel major versions
- 8 PHP major versions
- 1 MySQL major version
- Estimated 8-12 weeks → Actual timeline: [To be filled]

**Final Status:**
- All tests passing ✅
- All linter checks passing ✅
- Modern codebase ✅
- Production ready ✅

Update `LARAVEL_UPGRADE_PLAN.md` to reflect completion and celebrate! 🚀
