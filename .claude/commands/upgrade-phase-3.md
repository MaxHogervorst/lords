---
description: Phase 3 - Upgrade Laravel 5.5 to 5.6
scope: project
---

You are tasked with completing Phase 3 of the Laravel upgrade: Laravel 5.5 → 5.6.

**Reference:** https://laravel.com/docs/5.6/upgrade
**PHP Requirement:** >= 7.1.3

## Pre-Upgrade Steps

1. **Upgrade PHP to 7.1.3+**
   - Update Dockerfile to use PHP 7.1
   - Rebuild Docker containers
   - Test application on PHP 7.1

## Upgrade Steps

1. **Update composer.json dependencies:**
   ```json
   "laravel/framework": "5.6.*"
   "phpunit/phpunit": "~7.0"
   "laravelcollective/html": "^5.6"
   ```

2. **Run composer update:**
   ```bash
   docker-compose run --rm app composer update
   ```

3. **Key Changes to Address:**

   a. **Logging System**
   - Copy config/logging.php from laravel/laravel repo
   - Migrate from Monolog to new logging system
   - Update any custom logging code
   - Review and update logging configuration

   b. **Broadcasting**
   - Update broadcasting authentication if used

   c. **Blade**
   - Update Blade component syntax if used

   d. **Validation**
   - Review validation rule changes

4. **Configuration Updates:**
   - Add new config/logging.php
   - Update config/app.php
   - Clear all caches

5. **Run Tests:**
   ```bash
   ./test.sh tests/Feature/
   ```

6. **Manual Testing:**
   - Test logging functionality
   - Test all core features

## Validation Checklist

- [ ] Composer update successful
- [ ] config/logging.php added
- [ ] All tests passing: `./test.sh tests/Feature/`
- [ ] Linter passing: `./vendor/bin/phpstan analyse`
- [ ] Logging works correctly
- [ ] Application runs without errors

## Commit

When all checks pass:
```bash
git add -A
git commit -m "Phase 3 Complete: Upgrade to Laravel 5.6

- Upgraded PHP to 7.1
- Upgraded Laravel to 5.6
- Migrated to new logging system
- Added config/logging.php
- All tests passing

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude <noreply@anthropic.com>"
```

## Important Notes

- The logging system changed significantly in 5.6
- Test logging after upgrade
- Check error logs for any issues

Start by upgrading PHP to 7.1 in the Dockerfile.
