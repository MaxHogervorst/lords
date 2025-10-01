---
description: Phase 2 - Upgrade Laravel 5.4 to 5.5 LTS
scope: project
---

You are tasked with completing Phase 2 of the Laravel upgrade: Laravel 5.4 → 5.5 LTS.

**Reference:** https://laravel.com/docs/5.5/upgrade
**PHP Requirement:** >= 7.0.0

## Pre-Upgrade Steps

1. **Upgrade PHP to 7.0+**
   - Update Dockerfile to use PHP 7.0
   - Rebuild Docker containers
   - Test application on PHP 7.0

2. **Check Package Compatibility**
   - Verify all packages support Laravel 5.5 and PHP 7.0
   - Check cartalyst/sentinel compatibility
   - Check other dependencies

## Upgrade Steps

1. **Update composer.json dependencies:**
   ```json
   "laravel/framework": "5.5.*"
   "phpunit/phpunit": "~6.0"
   "laravelcollective/html": "^5.5"
   ```

2. **Run composer update:**
   ```bash
   docker-compose run --rm app composer update
   ```

3. **Key Changes to Address:**

   a. **Service Provider Registration**
   - Implement package auto-discovery
   - Remove providers from config/app.php that support auto-discovery
   - Update custom service providers

   b. **Exception Handling**
   - Update app/Exceptions/Handler.php with new report/render methods
   - Check Laravel 5.5 upgrade guide for Handler changes

   c. **Request Handling**
   - Review validation changes
   - Update custom request classes if any

   d. **Models**
   - Consider updating $dates properties to use $casts with 'datetime'

   e. **Routing**
   - Review middleware changes
   - Check route model binding

4. **Configuration Updates:**
   - Review all config files
   - Update config/app.php if needed
   - Clear caches

5. **Run Tests:**
   ```bash
   ./test.sh tests/Feature/
   ```

6. **Manual Testing:**
   - Test authentication flows
   - Test CRUD operations
   - Test invoice generation
   - Test SEPA exports

## Validation Checklist

- [ ] Composer update successful
- [ ] No deprecation warnings in logs
- [ ] All tests passing: `./test.sh tests/Feature/`
- [ ] Linter passing: `./vendor/bin/phpstan analyse`
- [ ] Application runs without errors
- [ ] All critical features work (auth, CRUD, invoices, SEPA)

## Commit

When all checks pass:
```bash
git add -A
git commit -m "Phase 2 Complete: Upgrade to Laravel 5.5 LTS

- Upgraded PHP to 7.0
- Upgraded Laravel to 5.5
- Implemented package auto-discovery
- Updated exception handling
- All tests passing

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude <noreply@anthropic.com>"
```

## Important Notes

- Laravel 5.5 is an LTS (Long Term Support) release
- Take extra care with this upgrade as it's a foundation version
- Test thoroughly before proceeding to Phase 3
- Document any issues encountered

## If Issues Arise

- Check Laravel 5.5 upgrade guide: https://laravel.com/docs/5.5/upgrade
- Check package compatibility on GitHub/Packagist
- Run `./test.sh tests/Feature/` frequently
- Check logs in storage/logs/

Start by upgrading PHP to 7.0 in the Dockerfile, then proceed with the Laravel upgrade.
