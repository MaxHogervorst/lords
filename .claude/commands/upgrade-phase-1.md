---
description: Phase 1 - Upgrade Laravel 5.3 to 5.4
---

You are helping upgrade a Laravel application from version 5.3 to 5.4.

**Reference:** https://laravel.com/docs/5.4/upgrade

Please follow the upgrade checklist in `LARAVEL_UPGRADE_PLAN.md` for Phase 1:

1. **Update composer.json dependencies:**
   - laravel/framework: 5.4.*
   - phpunit/phpunit: ~5.7
   - laravelcollective/html: ^5.4
   - Add laravel/tinker package

2. **Delete bootstrap/cache/compiled.php**

3. **Code Changes Required:**
   - Update Gate::getPolicyFor() usage to handle null returns
   - Review all Blade templates for inline sections - escape or use {!! !!}
   - Replace Collection::every() with nth()
   - Update Collection::random() usage
   - Review date casts in models
   - Update wildcard event handlers
   - Migrate to object-based events where applicable

4. **Testing Migration:**
   - Create Tests/ namespace directory
   - Move tests to new structure
   - Update Event fake methods (assertFired → assertDispatched)

5. **Clear caches:**
   - php artisan view:clear
   - php artisan route:clear
   - php artisan config:clear

6. **Run composer update**

7. **After upgrade:**
   - Run linter
   - Run full test suite
   - Test critical features manually
   - Commit: "Upgrade to Laravel 5.4"

Create a todo list and execute the upgrade step by step. After each change, run tests and linter.
