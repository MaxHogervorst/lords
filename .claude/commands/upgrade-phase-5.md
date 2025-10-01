---
description: Phase 5 - Upgrade Laravel 5.7 to 5.8
scope: project
---

You are tasked with completing Phase 5 of the Laravel upgrade: Laravel 5.7 → 5.8.

**Reference:** https://laravel.com/docs/5.8/upgrade
**PHP Requirement:** >= 7.1.3

## Upgrade Steps

1. **Update composer.json dependencies:**
   ```json
   "laravel/framework": "5.8.*"
   "phpunit/phpunit": "^7.5|^8.0"
   "laravelcollective/html": "^5.8"
   ```

2. **Run composer update:**
   ```bash
   docker-compose run --rm app composer update
   ```

3. **Key Changes to Address:**

   a. **Carbon 2.0**
   - Update Carbon usage (v2 included)
   - Test all date handling functionality
   - Check for any Carbon-specific code

   b. **Model Changes**
   - Review BelongsToMany pivot methods
   - Update any custom pivot operations

   c. **Middleware**
   - Review middleware priority changes

   d. **Validation**
   - Update custom validation rules if any

4. **Deprecations:**
   - Remove usage of deprecated methods
   - Check deprecation warnings in logs

5. **Run Tests:**
   ```bash
   ./test.sh tests/Feature/
   ```

6. **Manual Testing:**
   - Test date-related functionality (orders, invoices)
   - Test relationships and pivots (groups/members)
   - Test all CRUD operations

## Validation Checklist

- [ ] Composer update successful
- [ ] No Carbon-related errors
- [ ] All tests passing: `./test.sh tests/Feature/`
- [ ] Linter passing: `./vendor/bin/phpstan analyse`
- [ ] Date functionality works correctly
- [ ] Relationships work correctly

## Commit

When all checks pass:
```bash
git add -A
git commit -m "Phase 5 Complete: Upgrade to Laravel 5.8

- Upgraded Laravel to 5.8
- Updated to Carbon 2.0
- Updated dependencies
- All tests passing

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude <noreply@anthropic.com>"
```

## Important Notes

- Carbon 2.0 is a major change - test date handling thoroughly
- This is the last 5.x version before Laravel 6
- Pay special attention to invoice dates and SEPA export dates
