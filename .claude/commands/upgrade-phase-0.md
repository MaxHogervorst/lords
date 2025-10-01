---
description: Phase 0 - Preparation and increase test coverage
---

You are helping upgrade a Laravel 5.3 application to Laravel 12.x.

**Current Phase:** Phase 0 - Preparation & Test Coverage

**IMPORTANT:** All commands must be run via Docker. Use Docker containers for PHP, Composer, and all testing tools.

**Database Strategy:** Start with MySQL 5.7 for Laravel 5.3 compatibility. Upgrade to MySQL 8.0 during Phase 6 (Laravel 6.x upgrade) for better long-term support and performance.

Please follow the checklist in `LARAVEL_UPGRADE_PLAN.md` for Phase 0:

1. **Set Up Docker Environment**
   - Create Dockerfile for PHP 7.4+ (compatible with Laravel 5.3)
   - Create docker-compose.yml with PHP, MySQL 5.7, and Redis services
   - Set up volume mounts for the application
   - Test Docker setup with composer install

2. **Audit Current Application**
   - Document all custom features and business logic
   - List all routes and their purposes
   - Document database schema and relationships
   - Identify deprecated code patterns

3. **Increase Test Coverage to minimum 70%**
   - Review existing tests
   - Add integration tests for all controllers
   - Add unit tests for models
   - Focus on critical features: Authentication, Members, Groups, Products, Orders, Invoices, SEPA

4. **Set Up Testing Infrastructure**
   - Ensure PHPUnit is configured properly
   - Set up test database configuration via Docker
   - Create/update test data factories
   - Document how to run tests via Docker

5. **Install Code Quality Tools**
   - Install Laravel Pint or PHP CS Fixer via Composer in Docker
   - Install PHPStan/Larastan via Composer in Docker
   - Configure coding standards
   - Run initial linting and fix issues

6. **After completing each task:**
   - Run tests: `docker-compose exec app vendor/bin/phpunit`
   - Run linter: `docker-compose exec app vendor/bin/pint` or `vendor/bin/phpcs`
   - Commit changes with descriptive messages

Create a todo list and work through each item systematically. Ask for clarification if needed.
