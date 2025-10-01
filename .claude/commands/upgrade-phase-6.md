---
description: Phase 6 - Upgrade Laravel 5.8 to 6.x LTS + MySQL 8
scope: project
---

You are tasked with completing Phase 6 of the Laravel upgrade: Laravel 5.8 → 6.x LTS + MySQL 8.

**Reference:** https://laravel.com/docs/6.x/upgrade
**PHP Requirement:** >= 7.2.0
**MySQL Version:** 8.0+

## Pre-Upgrade Steps

1. **Upgrade PHP to 7.2+**
   - Update Dockerfile to use PHP 7.2
   - Rebuild Docker containers
   - Test application on PHP 7.2

2. **Backup MySQL 5.7 Database:**
   ```bash
   docker-compose exec db mysqldump -u root -p lords > backup-mysql57.sql
   ```

3. **Review MySQL 8.0 breaking changes:**
   - Check for reserved word conflicts
   - Review authentication plugin changes

## Upgrade Steps

1. **Update composer.json dependencies:**
   ```json
   "laravel/framework": "^6.0"
   "phpunit/phpunit": "^8.0"
   "facade/ignition": "^1.4"
   "laravel/tinker": "^2.0"
   "laravelcollective/html": "^6.0"
   ```

2. **Run composer update:**
   ```bash
   docker-compose run --rm app composer update
   ```

3. **Major Code Changes:**

   a. **String & Array Helpers**
   - Install laravel/helpers package OR
   - Replace all helper functions with Str/Arr facade calls
   - Search for: str_*, array_* functions in codebase

   b. **Authorization**
   - Update Gate callbacks (no longer wrap in arrays)

   c. **Carbon 2.0**
   - Verify Carbon 2.0 compatibility

   d. **Models**
   - Review soft delete behavior
   - Check primary key assumptions

4. **MySQL 8 Upgrade:**
   - Update docker-compose.yml to use mysql:8.0
   - Stop containers: `docker-compose down`
   - Remove old volume: `docker volume rm lords_dbdata`
   - Start MySQL 8: `docker-compose up -d db`
   - Wait for initialization
   - Restore database: `docker-compose exec -T db mysql -u root -p lords < backup-mysql57.sql`
   - Run migrations to verify: `docker-compose run --rm app php artisan migrate`

5. **Configuration Updates:**
   - Update all config files
   - Review config/database.php for MySQL 8
   - Update config/cors.php if needed

6. **Run Tests:**
   ```bash
   ./test.sh tests/Feature/
   ```

7. **Manual Testing:**
   - Test all database operations (CRUD, transactions)
   - Test SEPA exports (ensure no data corruption)
   - Test Excel exports
   - Test invoice generation
   - Verify query performance

## Validation Checklist

- [ ] PHP 7.2 working
- [ ] MySQL 8.0 running
- [ ] Database restored successfully
- [ ] Composer update successful
- [ ] All tests passing: `./test.sh tests/Feature/`
- [ ] Linter passing: `./vendor/bin/phpstan analyse`
- [ ] No MySQL 8 warnings in logs
- [ ] String/array helpers working
- [ ] SEPA exports work correctly
- [ ] Application runs without errors

## Commit

When all checks pass:
```bash
git add -A
git commit -m "Phase 6 Complete: Upgrade to Laravel 6.x LTS and MySQL 8.0

- Upgraded PHP to 7.2
- Upgraded Laravel to 6.x LTS
- Upgraded MySQL to 8.0
- Migrated string/array helpers
- Updated all dependencies
- All tests passing

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude <noreply@anthropic.com>"
```

## Important Notes

- Laravel 6 is an LTS release - very important milestone
- MySQL 8 is a major upgrade - test thoroughly
- CRITICAL: Test SEPA exports and invoice generation
- Keep MySQL 5.7 backup safe until fully validated
