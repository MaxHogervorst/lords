---
description: Phase 4 - Upgrade Laravel 5.6 to 5.7
scope: project
---

You are tasked with completing Phase 4 of the Laravel upgrade: Laravel 5.6 → 5.7.

**Reference:** https://laravel.com/docs/5.7/upgrade
**PHP Requirement:** >= 7.1.3

## Upgrade Steps

1. **Update composer.json dependencies:**
   ```json
   "laravel/framework": "5.7.*"
   "phpunit/phpunit": "^7.0"
   "laravelcollective/html": "^5.7"
   ```

2. **Run composer update:**
   ```bash
   docker-compose run --rm app composer update
   ```

3. **Key Changes to Address:**

   a. **Email Verification**
   - Review email verification if implemented

   b. **Notifications**
   - Update notification channels if customized

   c. **Resources**
   - Update API resources if used

   d. **URL Generation**
   - Test URL generation with asset versioning

4. **Optional Features:**
   - Consider Laravel Telescope for debugging (dev dependency)

5. **Run Tests:**
   ```bash
   ./test.sh tests/Feature/
   ```

6. **Manual Testing:**
   - Test email functionality
   - Test all core features

## Validation Checklist

- [ ] Composer update successful
- [ ] All tests passing: `./test.sh tests/Feature/`
- [ ] Linter passing: `./vendor/bin/phpstan analyse`
- [ ] Application runs without errors
- [ ] Email functionality works

## Commit

When all checks pass:
```bash
git add -A
git commit -m "Phase 4 Complete: Upgrade to Laravel 5.7

- Upgraded Laravel to 5.7
- Updated dependencies
- All tests passing

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude <noreply@anthropic.com>"
```

## Important Notes

- This is a smaller upgrade compared to previous phases
- Focus on testing notification and email functionality
