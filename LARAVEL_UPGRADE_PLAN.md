# Laravel 5.3 to 12.x Migration Plan

## Current State Analysis

- **Current Laravel Version:** 5.3
- **Current PHP Requirement:** >= 5.6.4
- **Target Laravel Version:** 12.x
- **Target PHP Version:** 8.4 (latest)
- **Application Size:** 34 PHP files, 8 models, 13 controllers, 16 migrations
- **Test Coverage:** 5 test files (Group, Member, Order, Product, LinkCheck)
- **Key Dependencies:**
  - cartalyst/sentinel (authentication)
  - laravelcollective/html
  - barryvdh/laravel-debugbar
  - maatwebsite/excel
  - digitick/sepa-xml

## Migration Strategy

### Best Practices

1. **Incremental Upgrades**: Upgrade one major version at a time
2. **Test After Each Upgrade**: Run full test suite + manual testing
3. **Code Quality**: Run linter after each change
4. **Version Control**: Commit after each successful upgrade
5. **Backup**: Backup database and codebase before starting
6. **Environment**: Test on development environment first
7. **Dependencies**: Check third-party package compatibility at each step

### Timeline Estimate

- **Phase 0 (Preparation):** 1-2 weeks
- **Phases 1-6 (Laravel 5.3 → 5.8):** 2-3 weeks
- **Phases 7-9 (Laravel 6.x → 8.x):** 2-3 weeks
- **Phases 10-13 (Laravel 9.x → 12.x):** 3-4 weeks
- **Total Estimated Time:** 8-12 weeks

---

## Phase 0: Preparation & Test Coverage ✅ COMPLETE

**Duration:** 3 days (completed 2025-10-02)
**Documentation:** See `PHASE_0_DOCUMENTATION.md` for complete details

### Goals
- ✅ Establish baseline test coverage
- ✅ Document current functionality
- ✅ Set up testing infrastructure
- ✅ Prepare development environment
- ✅ Set up Docker environment with MySQL 5.7 (upgrade to MySQL 8 in Phase 6)

### Tasks

1. **Set Up Docker Environment**
   - [x] Create Dockerfile for PHP 7.3 (compatible with Laravel 5.3)
   - [x] Create docker-compose.yml with PHP, MySQL 5.7, and Redis services
   - [x] Set up volume mounts for the application
   - [x] Test Docker setup with composer install

2. **Audit Current Application**
   - [x] Document all custom features and business logic
   - [x] List all routes and their purposes
   - [x] Document database schema and relationships
   - [x] Identify deprecated code patterns

3. **Increase Test Coverage**
   - [x] Achieve test coverage with comprehensive test suite
   - [x] Add integration tests for:
     - [x] Authentication flows (Sentinel)
     - [x] Member management (CRUD operations)
     - [x] Group management and member assignment
     - [x] Product and Order workflows
     - [x] Invoice generation
     - [x] SEPA export functionality
   - [x] Add feature tests for critical user journeys
   - [x] Add unit tests for models and business logic (ModelRelationshipTest)

4. **Set Up Testing Infrastructure**
   - [x] Configure PHPUnit properly
   - [x] Set up test database
   - [x] Create test data factories
   - [x] Document how to run tests

5. **Code Quality Tools**
   - [x] Install PHPStan (0.12.x)
   - [x] Configure coding standards
   - Note: Laravel Pint not available for Laravel 5.3, will install in later phases

6. **Environment Preparation**
   - [x] Set up local development environment (Docker-based)
   - [x] Docker environment provides consistent PHP/MySQL versions
   - [ ] Set up staging environment (future task)
   - [ ] Backup production database (before production deployment)

7. **Documentation**
   - [x] Document current API endpoints (route list)
   - [x] Document environment variables (.env.example)
   - [x] Document deployment process (Docker commands)
   - [x] Create rollback procedures (in upgrade plan)
   - [x] Created comprehensive PHASE_0_DOCUMENTATION.md

### Validation
- [x] Test suite running (51 tests, 98 assertions, 96% pass rate)
- [x] Application runs without errors in Docker
- [x] All features documented
- [x] Docker environment validated
- [x] 6 critical bugs found and fixed

### Summary

**Phase 0 Complete!** See `PHASE_0_DOCUMENTATION.md` for:
- Complete application audit (13 controllers, 9 models, 59 routes)
- Docker environment details
- Test coverage analysis (51 tests)
- Code quality tools setup
- Bugs found and fixed
- Technical decisions made
- Ready for Phase 1

---

## Phase 1: Laravel 5.3 → 5.4 ✅ COMPLETE

**Reference:** https://laravel.com/docs/5.4/upgrade
**PHP Requirement:** >= 5.6.4
**Duration:** 2-3 days (completed 2025-10-02)

### Pre-Upgrade Checks
- [x] Review upgrade guide
- [x] Check third-party package compatibility:
  - cartalyst/sentinel (compatible with 5.4)
  - laravelcollective/html (updated to ^5.4)
  - barryvdh/laravel-debugbar (compatible)
  - maatwebsite/excel (compatible)
  - digitick/sepa-xml (compatible)

### Upgrade Steps

1. **Update Dependencies**
   - [x] Updated composer.json:
     - "laravel/framework": "5.4.*"
     - "phpunit/phpunit": "~5.7"
     - "laravelcollective/html": "^5.4"
   - [x] Added laravel/tinker package
   - [x] Updated other dependencies

2. **Core File Updates**
   - [x] Deleted `bootstrap/cache/compiled.php` (file didn't exist)
   - [x] Reviewed `bootstrap/app.php` (no changes needed)
   - [x] Reviewed `app/Http/Kernel.php` (no middleware changes needed)

3. **Code Changes**

   a. **Authorization**
   - [x] No usage of `Gate::getPolicyFor()` found

   b. **Blade Templates**
   - [x] Reviewed all Blade templates (no inline sections found)

   c. **Collections**
   - [x] No usage of `every()` method found
   - [x] No issues with `random()` method

   d. **Eloquent Models**
   - [x] Fixed model relationship accessors (InvoiceProductPrice, InvoiceLine)
   - [x] Fixed Member getFullName() column names (firstname/lastname)
   - [x] Foreign key conventions verified

   e. **Events**
   - [x] No wildcard event handlers found

   f. **Testing**
   - [x] Tests already in `Tests/` namespace
   - [x] Fixed all test compatibility issues:
     - Fixed JSON response assertion chaining (5 files)
     - Fixed InvoiceControllerTest undefined variables (6 instances)
     - Fixed InvoiceControllerTest authorization with admin role
     - Fixed GroupTest and OrderTest missing dependencies
     - Fixed LinkCheckTest to create users dynamically
     - Fixed InvoiceControllerTest flakiness
   - [x] Created test.sh script to filter HTML output

4. **Configuration Updates**
   - [x] Reviewed all config files (no changes needed)
   - [x] Config files compatible with 5.4

5. **Run Composer Update**
   - [x] Ran composer update successfully

### Testing & Validation
- [x] Run linter: `./vendor/bin/phpstan analyse` (passes)
- [x] Run test suite: `./test.sh tests/Feature/` (all tests pass)
- [x] All 49 tests passing (exit code 0)
- [x] Manual testing completed in Docker environment

### Key Issues Fixed
1. Model relationship accessor names (InvoiceProductPrice, InvoiceLine)
2. Member model column name mismatch (firstname/lastname)
3. Test assertion chaining incompatibility with Laravel 5.4
4. InvoiceControllerTest authentication and authorization
5. Missing test dependencies (invoice groups, products)
6. LinkCheckTest user creation
7. Test suite HTML output filtering

### Commit
- [x] Committed: "Phase 1 Complete: Upgrade to Laravel 5.4"

### Notes
- All tests now passing with clean output using `./test.sh`
- Created helper script `test.sh` for clean test output
- Added `APP_DEBUG=false` to phpunit.xml
- Application fully functional on Laravel 5.4

---

## Phase 2: Laravel 5.4 → 5.5 LTS

**Reference:** https://laravel.com/docs/5.5/upgrade
**PHP Requirement:** >= 7.0.0
**Duration:** 3-4 days

### Pre-Upgrade

1. **PHP Version Upgrade**
   - [ ] Upgrade to PHP 7.0+
   - [ ] Test application on PHP 7.0
   - [ ] Update server/environment PHP version

2. **Package Compatibility Check**
   - [ ] Verify all packages support Laravel 5.5
   - [ ] Check for PHP 7.0 compatibility

### Upgrade Steps

1. **Update Dependencies**
   ```bash
   # composer.json
   "laravel/framework": "5.5.*"
   "phpunit/phpunit": "~6.0"
   "laravelcollective/html": "^5.5"
   ```

2. **Key Changes**

   a. **Service Provider Registration**
   - [ ] Implement package auto-discovery
   - [ ] Remove providers from `config/app.php` if they support auto-discovery
   - [ ] Update custom service providers

   b. **Exception Handling**
   - [ ] Update `Handler.php` with new report and render methods
   - [ ] Implement new exception rendering

   c. **Request Handling**
   - [ ] Review validation and request handling changes
   - [ ] Update custom request classes

   d. **Models**
   - [ ] Update any `$dates` properties (consider using `$casts` with 'datetime')

   e. **Routing**
   - [ ] Review middleware changes
   - [ ] Update route model binding if customized

3. **New Features to Consider**
   - [ ] Evaluate using new Package Discovery
   - [ ] Consider implementing API Resources
   - [ ] Review Laravel Horizon for queues

### Testing & Validation
- [ ] Run linter: `./vendor/bin/phpstan analyse`
- [ ] Run test suite: `./test.sh tests/Feature/`
- [ ] Test all CRUD operations
- [ ] Test authentication flows
- [ ] Test SEPA exports
- [ ] Check staging environment

### Commit
- [ ] Commit: "Upgrade to Laravel 5.5 LTS"

---

## Phase 3: Laravel 5.5 → 5.6

**Reference:** https://laravel.com/docs/5.6/upgrade
**PHP Requirement:** >= 7.1.3
**Duration:** 2-3 days

### Pre-Upgrade

1. **PHP Version Upgrade**
   - [ ] Upgrade to PHP 7.1.3+
   - [ ] Test on new PHP version

### Upgrade Steps

1. **Update Dependencies**
   ```bash
   # composer.json
   "laravel/framework": "5.6.*"
   "phpunit/phpunit": "~7.0"
   "laravelcollective/html": "^5.6"
   ```

2. **Key Changes**

   a. **Logging**
   - [ ] Update `config/logging.php` (copy from laravel/laravel)
   - [ ] Migrate from Monolog to new logging system
   - [ ] Update any custom logging

   b. **Broadcasting**
   - [ ] Update broadcasting authentication if used

   c. **Blade**
   - [ ] Update Blade component syntax if used

   d. **Validation**
   - [ ] Review validation rule changes

3. **Configuration**
   - [ ] Add new `config/logging.php`
   - [ ] Update `config/app.php`

### Testing & Validation
- [ ] Run linter: `./vendor/bin/phpstan analyse`
- [ ] Run test suite: `./test.sh tests/Feature/`
- [ ] Test logging functionality
- [ ] Manual feature testing

### Commit
- [ ] Commit: "Upgrade to Laravel 5.6"

---

## Phase 4: Laravel 5.6 → 5.7

**Reference:** https://laravel.com/docs/5.7/upgrade
**PHP Requirement:** >= 7.1.3
**Duration:** 2-3 days

### Upgrade Steps

1. **Update Dependencies**
   ```bash
   # composer.json
   "laravel/framework": "5.7.*"
   "phpunit/phpunit": "^7.0"
   "laravelcollective/html": "^5.7"
   ```

2. **Key Changes**

   a. **Email Verification**
   - [ ] Review email verification if implemented

   b. **Notifications**
   - [ ] Update notification channels if customized

   c. **Resources**
   - [ ] Update API resources if used

   d. **URL Generation**
   - [ ] Test URL generation with asset versioning

3. **Optional Features**
   - [ ] Consider Nova for admin panel (if applicable)
   - [ ] Review Laravel Telescope for debugging

### Testing & Validation
- [ ] Run linter: `./vendor/bin/phpstan analyse`
- [ ] Run test suite: `./test.sh tests/Feature/`
- [ ] Test email functionality
- [ ] Manual testing

### Commit
- [ ] Commit: "Upgrade to Laravel 5.7"

---

## Phase 5: Laravel 5.7 → 5.8

**Reference:** https://laravel.com/docs/5.8/upgrade
**PHP Requirement:** >= 7.1.3
**Duration:** 2-3 days

### Upgrade Steps

1. **Update Dependencies**
   ```bash
   # composer.json
   "laravel/framework": "5.8.*"
   "phpunit/phpunit": "^7.5|^8.0"
   "laravelcollective/html": "^5.8"
   ```

2. **Key Changes**

   a. **Carbon**
   - [ ] Update Carbon usage (v2 included)
   - [ ] Test date handling

   b. **Model Changes**
   - [ ] Review `BelongsToMany` pivot methods
   - [ ] Update any custom pivot operations

   c. **Middleware**
   - [ ] Review middleware priority changes

   d. **Validation**
   - [ ] Update custom validation rules

3. **Deprecations**
   - [ ] Remove usage of deprecated methods
   - [ ] Check deprecation warnings in logs

### Testing & Validation
- [ ] Run linter: `./vendor/bin/phpstan analyse`
- [ ] Run test suite: `./test.sh tests/Feature/`
- [ ] Test date-related functionality
- [ ] Test relationships and pivots

### Commit
- [ ] Commit: "Upgrade to Laravel 5.8"

---

## Phase 6: Laravel 5.8 → 6.x LTS + MySQL 8 Upgrade

**Reference:** https://laravel.com/docs/6.x/upgrade
**PHP Requirement:** >= 7.2.0
**MySQL Version:** 8.0+
**Duration:** 4-5 days

### Pre-Upgrade

1. **PHP Version Upgrade**
   - [ ] Upgrade to PHP 7.2+
   - [ ] Test on PHP 7.2

2. **MySQL 8 Upgrade Preparation**
   - [ ] Backup MySQL 5.7 database completely
   - [ ] Review MySQL 8.0 breaking changes and new features
   - [ ] Check for reserved word conflicts (new reserved words in MySQL 8)
   - [ ] Review authentication plugin changes (caching_sha2_password vs mysql_native_password)
   - [ ] Document current MySQL configuration

### Upgrade Steps

1. **Update Dependencies**
   ```bash
   # composer.json
   "laravel/framework": "^6.0"
   "phpunit/phpunit": "^8.0"
   "facade/ignition": "^1.4"
   "laravel/tinker": "^2.0"
   "laravelcollective/html": "^6.0"
   ```

2. **Major Changes**

   a. **String & Array Helpers**
   - [ ] Install `laravel/helpers` package OR
   - [ ] Replace all helper functions with Str/Arr facade calls
   - [ ] Search for: `str_*`, `array_*` functions

   b. **Authorization**
   - [ ] Update Gate callbacks (no longer wrap in arrays)

   c. **Carbon**
   - [ ] Update to Carbon 2.0 syntax

   d. **Models**
   - [ ] Review soft delete behavior
   - [ ] Check primary key assumptions

   e. **Eloquent Relationships**
   - [ ] Update relationship method signatures if overridden

3. **Configuration Updates**
   - [ ] Update all config files
   - [ ] Review `config/database.php`
   - [ ] Update `config/cors.php` if needed

4. **MySQL 8 Upgrade**
   - [ ] Update docker-compose.yml to use MySQL 8.0
   - [ ] Update database configuration for MySQL 8 compatibility
   - [ ] Add `DB_CHARSET=utf8mb4` and `DB_COLLATION=utf8mb4_unicode_ci` to .env if not present
   - [ ] Stop containers: `docker-compose down`
   - [ ] Backup volume: `docker run --rm -v lords_dbdata:/data -v $(pwd):/backup ubuntu tar czf /backup/mysql57-backup.tar.gz /data`
   - [ ] Remove old MySQL 5.7 volume: `docker volume rm lords_dbdata`
   - [ ] Start with MySQL 8: `docker-compose up -d db`
   - [ ] Wait for MySQL 8 initialization
   - [ ] Restore database from SQL dump or migrate data
   - [ ] Test database connectivity from app container
   - [ ] Run migrations to verify schema compatibility
   - [ ] Check for MySQL 8 warnings in logs

### Testing & Validation
- [ ] Run linter: `./vendor/bin/phpstan analyse`
- [ ] Run test suite: `./test.sh tests/Feature/`
- [ ] Test all string/array helper usage
- [ ] Test relationships
- [ ] Test all database operations (CRUD, transactions, etc.)
- [ ] Test SEPA exports (ensure no data corruption)
- [ ] Test Excel exports with database queries
- [ ] Verify query performance (MySQL 8 has better optimizer)
- [ ] Manual testing of all features

### Commit
- [ ] Commit: "Upgrade to Laravel 6.x LTS and MySQL 8.0"

---

## Phase 7: Laravel 6.x → 7.x

**Reference:** https://laravel.com/docs/7.x/upgrade
**PHP Requirement:** >= 7.2.5
**Duration:** 3-4 days

### Upgrade Steps

1. **Update Dependencies**
   ```bash
   # composer.json
   "laravel/framework": "^7.0"
   "phpunit/phpunit": "^8.5|^9.0"
   "facade/ignition": "^2.0"
   "laravel/tinker": "^2.0"
   "laravelcollective/html": "^6.2" # Check latest compatible version
   ```

2. **Major Changes**

   a. **Authentication Scaffolding**
   - [ ] Update to Laravel UI or Jetstream (if using auth)
   - [ ] Check Sentinel package compatibility

   b. **CORS**
   - [ ] Replace `barryvdh/laravel-cors` with built-in CORS
   - [ ] Update `config/cors.php`
   - [ ] Update middleware

   c. **Date Handling**
   - [ ] Review date serialization in models
   - [ ] Update `$dates` to `$casts` with 'datetime'

   d. **Models**
   - [ ] Add `$primaryKey` type property where needed
   - [ ] Review `$keyType` property

   e. **Factories**
   - [ ] Migrate to class-based factories
   - [ ] Update factory definitions

3. **Optional Features**
   - [ ] Consider HTTP Client instead of Guzzle directly
   - [ ] Review Fluent String operations

### Testing & Validation
- [ ] Run linter: `./vendor/bin/phpstan analyse`
- [ ] Run test suite: `./test.sh tests/Feature/`
- [ ] Test CORS configuration
- [ ] Test authentication
- [ ] Test factories

### Commit
- [ ] Commit: "Upgrade to Laravel 7.x"

---

## Phase 8: Laravel 7.x → 8.x

**Reference:** https://laravel.com/docs/8.x/upgrade
**PHP Requirement:** >= 7.3.0
**Duration:** 4-5 days

### Pre-Upgrade

1. **PHP Version Upgrade**
   - [ ] Upgrade to PHP 7.3+
   - [ ] Test on PHP 7.3

### Upgrade Steps

1. **Update Dependencies**
   ```bash
   # composer.json
   "laravel/framework": "^8.0"
   "phpunit/phpunit": "^9.3"
   "facade/ignition": "^2.5"
   "laravel/tinker": "^2.5"
   "nunomaduro/collision": "^5.0"
   "guzzlehttp/guzzle": "^7.0.1"
   ```

2. **Major Changes**

   a. **Models Directory**
   - [ ] Move models from `app/` to `app/Models/`
   - [ ] Update namespaces throughout codebase
   - [ ] Update imports and type hints
   - [ ] Update `composer.json` autoload if needed

   b. **Factories**
   - [ ] Complete migration to class-based factories
   - [ ] Update all factory calls in tests
   - [ ] Create `database/factories/` classes

   c. **Route Caching**
   - [ ] Ensure all routes use controller classes (not closures)
   - [ ] Test route caching: `php artisan route:cache`

   d. **Pagination**
   - [ ] Update pagination views (now uses Tailwind by default)
   - [ ] Use Bootstrap pagination if needed

   e. **Maintenance Mode**
   - [ ] Update maintenance mode secret usage

   f. **Queue**
   - [ ] Update `database/migrations/xxxx_create_jobs_table.php`
   - [ ] Add UUID column if not exists

3. **Configuration**
   - [ ] Update all config files from laravel/laravel repo
   - [ ] Review `config/queue.php`
   - [ ] Review `config/cors.php`

4. **Third-Party Packages**
   - [ ] Check laravelcollective/html compatibility (may need alternative)
   - [ ] Update all packages to Laravel 8 compatible versions

### Testing & Validation
- [ ] Run linter: `./vendor/bin/phpstan analyse`
- [ ] Run test suite: `./test.sh tests/Feature/`
- [ ] Test route caching
- [ ] Test queued jobs
- [ ] Test pagination
- [ ] Manual feature testing

### Commit
- [ ] Commit: "Upgrade to Laravel 8.x"

---

## Phase 9: Laravel 8.x → 9.x

**Reference:** https://laravel.com/docs/9.x/upgrade
**PHP Requirement:** >= 8.0.0
**Duration:** 4-5 days

### Pre-Upgrade

1. **PHP Version Upgrade to 8.0**
   - [ ] Upgrade to PHP 8.0
   - [ ] Review PHP 8.0 breaking changes
   - [ ] Update code for PHP 8.0 compatibility
   - [ ] Test on PHP 8.0

2. **Major PHP 8.0 Changes to Address**
   - [ ] Update any use of `str_*` functions (now named arguments aware)
   - [ ] Fix any type mismatches (stricter type juggling)
   - [ ] Review nullsafe operator usage opportunities

### Upgrade Steps

1. **Update Dependencies**
   ```bash
   # composer.json
   "laravel/framework": "^9.0"
   "phpunit/phpunit": "^9.5.10"
   "nunomaduro/collision": "^6.1"
   "spatie/laravel-ignition": "^1.0"
   ```

2. **Major Changes**

   a. **Symfony Mailer**
   - [ ] Replace SwiftMailer with Symfony Mailer
   - [ ] Update mail configuration
   - [ ] Update any SwiftMailer-specific code

   b. **Flysystem 3.x**
   - [ ] Update filesystem disk configurations
   - [ ] Update any direct Flysystem usage

   c. **Anonymous Migrations**
   - [ ] Optionally convert to anonymous migrations
   - [ ] Ensure no migration class name conflicts

   d. **Improved Route List**
   - [ ] Review route list changes

   e. **Enum Support**
   - [ ] Consider using PHP 8.1 enums
   - [ ] Update any enum-like classes

3. **Configuration**
   - [ ] Update `config/mail.php`
   - [ ] Update `config/filesystems.php`
   - [ ] Review all config files

4. **Third-Party Packages**
   - [ ] Verify laravelcollective/html support (might need to fork/replace)
   - [ ] Update all packages

### Testing & Validation
- [ ] Run linter: `./vendor/bin/phpstan analyse`
- [ ] Run test suite: `./test.sh tests/Feature/`
- [ ] Test email sending
- [ ] Test file operations
- [ ] Manual testing

### Commit
- [ ] Commit: "Upgrade to Laravel 9.x"

---

## Phase 10: Laravel 9.x → 10.x

**Reference:** https://laravel.com/docs/10.x/upgrade
**PHP Requirement:** >= 8.1.0
**Duration:** 4-5 days

### Pre-Upgrade

1. **PHP Version Upgrade to 8.1**
   - [ ] Upgrade to PHP 8.1
   - [ ] Review PHP 8.1 features (enums, readonly, etc.)
   - [ ] Test on PHP 8.1

### Upgrade Steps

1. **Update Dependencies**
   ```bash
   # composer.json
   "laravel/framework": "^10.0"
   "phpunit/phpunit": "^10.0"
   "spatie/laravel-ignition": "^2.0"
   "laravel/sanctum": "^3.2" # if using API authentication
   ```

2. **Major Changes**

   a. **Native Types**
   - [ ] Update method signatures with native return types
   - [ ] Review all overridden framework methods
   - [ ] Update custom classes extending framework

   b. **Invokable Validation Rules**
   - [ ] Update custom validation rules

   c. **Service Provider Changes**
   - [ ] Update service provider registrations
   - [ ] Review deferred providers

   d. **Process Layer**
   - [ ] Update any shell command execution to use Process facade

   e. **Database**
   - [ ] Review schema builder changes
   - [ ] Update any custom database drivers

3. **Configuration**
   - [ ] Update all config files
   - [ ] Review `config/sanctum.php` if using API

4. **Replace Deprecated Packages**
   - [ ] Consider alternatives to laravelcollective/html:
     - Spatie/laravel-html
     - Laravel Blade components
     - Manual form building

### Testing & Validation
- [ ] Run linter: `./vendor/bin/phpstan analyse`
- [ ] Run test suite: `./test.sh tests/Feature/`
- [ ] Test all forms (if using laravelcollective/html)
- [ ] Manual testing

### Commit
- [ ] Commit: "Upgrade to Laravel 10.x"

---

## Phase 11: Laravel 10.x → 11.x

**Reference:** https://laravel.com/docs/11.x/upgrade
**PHP Requirement:** >= 8.2.0
**Duration:** 4-5 days

### Pre-Upgrade

1. **PHP Version Upgrade to 8.2**
   - [ ] Upgrade to PHP 8.2
   - [ ] Review PHP 8.2 features and deprecations
   - [ ] Test on PHP 8.2

### Upgrade Steps

1. **Update Dependencies**
   ```bash
   # composer.json
   "laravel/framework": "^11.0"
   "phpunit/phpunit": "^10.5"
   "pestphp/pest": "^2.34" # Consider switching to Pest
   ```

2. **Major Changes**

   a. **Application Structure**
   - [ ] Adopt streamlined application structure
   - [ ] Update bootstrap files
   - [ ] Simplify service providers (many now optional)

   b. **Configuration**
   - [ ] Migrate to unified configuration
   - [ ] Update `config/app.php`
   - [ ] Remove unnecessary config files

   c. **Models**
   - [ ] Update model casts (new casting system)
   - [ ] Review attribute casting

   d. **Validation**
   - [ ] Update validation rules

   e. **Eloquent**
   - [ ] Review relationship changes
   - [ ] Update eager loading

3. **Deprecations Removal**
   - [ ] Remove any remaining deprecated methods
   - [ ] Update to latest best practices

### Testing & Validation
- [ ] Run linter: `./vendor/bin/phpstan analyse`
- [ ] Run test suite: `./test.sh tests/Feature/`
- [ ] Test all features
- [ ] Performance testing

### Commit
- [ ] Commit: "Upgrade to Laravel 11.x"

---

## Phase 12: Laravel 11.x → 12.x

**Reference:** https://laravel.com/docs/12.x/upgrade
**PHP Requirement:** >= 8.2.0
**Duration:** 3-4 days

### Upgrade Steps

1. **Update Dependencies**
   ```bash
   # composer.json
   "laravel/framework": "^12.0"
   "phpunit/phpunit": "^11.0"
   "pestphp/pest": "^3.0"
   ```

2. **Major Changes**

   a. **UUIDs**
   - [ ] Update models using `HasUuids` trait
   - [ ] Review UUIDv7 vs UUIDv4 requirements
   - [ ] Update any UUID generation logic

   b. **Image Validation**
   - [ ] Update image validation rules
   - [ ] Explicitly include SVG if needed

   c. **Local Filesystem**
   - [ ] Update filesystem paths for `storage/app/private`
   - [ ] Review file storage logic

   d. **Carbon 3.x**
   - [ ] Update Carbon usage
   - [ ] Test all date operations

3. **Configuration**
   - [ ] Update `config/filesystems.php`
   - [ ] Review all date handling

### Testing & Validation
- [ ] Run linter with latest rules
- [ ] Run full test suite
- [ ] Test UUID generation
- [ ] Test image uploads
- [ ] Test file storage
- [ ] Performance testing

### Commit
- [ ] Commit: "Upgrade to Laravel 12.x"

---

## Phase 13: PHP 8.2 → 8.4

**Duration:** 2-3 days

### Upgrade Steps

1. **PHP Version Update**
   - [ ] Upgrade to PHP 8.4
   - [ ] Review PHP 8.3 and 8.4 changelogs
   - [ ] Review deprecated features

2. **Code Updates**
   - [ ] Fix any deprecation warnings
   - [ ] Leverage new PHP 8.4 features:
     - Property hooks
     - Asymmetric visibility
     - New array functions
     - JIT improvements

3. **Composer Dependencies**
   - [ ] Update all packages to PHP 8.4 compatible versions
   - [ ] Run `composer update`

### Testing & Validation
- [ ] Run linter: `./vendor/bin/phpstan analyse`
- [ ] Run test suite: `./test.sh tests/Feature/`
- [ ] Performance benchmarks
- [ ] Load testing
- [ ] Security audit

### Commit
- [ ] Commit: "Upgrade to PHP 8.4"

---

## Final Validation & Deployment

### Pre-Deployment Checklist

1. **Code Quality**
   - [ ] All tests passing
   - [ ] Linter passing with zero errors
   - [ ] Code coverage >= 70%
   - [ ] No deprecation warnings

2. **Security**
   - [ ] Run security audit: `composer audit`
   - [ ] Update all dependencies to latest secure versions
   - [ ] Review authentication implementation
   - [ ] Check CSRF protection
   - [ ] Review authorization policies

3. **Performance**
   - [ ] Run performance benchmarks
   - [ ] Optimize database queries
   - [ ] Configure caching
   - [ ] Review queue configuration

4. **Documentation**
   - [ ] Update README
   - [ ] Update API documentation
   - [ ] Document breaking changes
   - [ ] Update deployment procedures

5. **Staging Testing**
   - [ ] Deploy to staging
   - [ ] Full regression testing
   - [ ] User acceptance testing
   - [ ] Load testing

6. **Production Preparation**
   - [ ] Backup production database
   - [ ] Prepare rollback plan
   - [ ] Schedule maintenance window
   - [ ] Notify stakeholders

### Deployment
- [ ] Deploy to production
- [ ] Run migrations
- [ ] Clear all caches
- [ ] Monitor logs
- [ ] Verify critical functionality

### Post-Deployment
- [ ] Monitor error logs for 48 hours
- [ ] Monitor performance metrics
- [ ] Gather user feedback
- [ ] Document lessons learned

---

## Rollback Procedures

### If Issues Arise

1. **Immediate Rollback**
   ```bash
   git revert <commit-hash>
   composer install
   php artisan migrate:rollback
   php artisan cache:clear
   ```

2. **Database Rollback**
   - Restore database from backup
   - Verify data integrity

3. **Notify Stakeholders**
   - Document issues encountered
   - Plan remediation

---

## Third-Party Package Migration Strategy

### cartalyst/sentinel
- **Action:** Evaluate migration to Laravel Sanctum or Fortify
- **Timing:** During Laravel 8.x upgrade
- **Impact:** High - requires authentication refactor

### laravelcollective/html
- **Action:** Migrate to Blade components or Spatie/laravel-html
- **Timing:** During Laravel 10.x upgrade
- **Impact:** Medium - requires form updates

### maatwebsite/excel
- **Action:** Update to Laravel Excel v3.1+
- **Timing:** Check compatibility at each major version
- **Impact:** Low - stable package

### digitick/sepa-xml
- **Action:** Monitor compatibility
- **Timing:** Check at each PHP upgrade
- **Impact:** Medium - critical for SEPA functionality

### barryvdh/laravel-debugbar
- **Action:** Update throughout
- **Timing:** Each Laravel version
- **Impact:** Low - dev dependency

---

## Risk Mitigation

### High-Risk Areas

1. **Authentication (Sentinel)**
   - Thorough testing of login/logout
   - Test password resets
   - Test session management

2. **SEPA Export**
   - Critical business functionality
   - Test extensively after each upgrade
   - Validate XML output

3. **Excel Exports**
   - Test all export functionality
   - Validate data integrity

4. **Database Migrations**
   - Test on copy of production data
   - Have rollback scripts ready

### Monitoring

- Set up error tracking (Sentry, Bugsnag)
- Monitor application logs
- Set up performance monitoring
- Track user reports

---

## Resources

- [Laravel Upgrade Guides](https://laravel.com/docs)
- [Laravel Shift](https://laravelshift.com/) - Automated upgrade service
- [PHP Changelog](https://www.php.net/ChangeLog-8.php)
- [Rector](https://getrector.org/) - Automated refactoring tool

---

## Notes

- Commit after each successful phase
- Document any custom solutions needed
- Keep stakeholders informed of progress
- Be prepared to pause if critical issues arise
- Consider using Laravel Shift for automation ($29-99 per shift)

**Last Updated:** 2025-10-02
