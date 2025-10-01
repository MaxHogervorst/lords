---
description: Phase 0 - Preparation and increase test coverage
---

You are helping upgrade a Laravel 5.3 application to Laravel 12.x.

**Current Phase:** Phase 0 - Preparation & Test Coverage

Please follow the checklist in `LARAVEL_UPGRADE_PLAN.md` for Phase 0:

1. **Audit Current Application**
   - Document all custom features and business logic
   - List all routes and their purposes
   - Document database schema and relationships
   - Identify deprecated code patterns

2. **Increase Test Coverage to minimum 70%**
   - Review existing tests
   - Add integration tests for all controllers
   - Add unit tests for models
   - Focus on critical features: Authentication, Members, Groups, Products, Orders, Invoices, SEPA

3. **Set Up Testing Infrastructure**
   - Ensure PHPUnit is configured properly
   - Set up test database configuration
   - Create/update test data factories
   - Document how to run tests

4. **Install Code Quality Tools**
   - Install Laravel Pint or PHP CS Fixer
   - Install PHPStan/Larastan
   - Configure coding standards
   - Run initial linting and fix issues

5. **After completing each task:**
   - Run tests: `vendor/bin/phpunit`
   - Run linter: `vendor/bin/pint` or `vendor/bin/phpcs`
   - Commit changes with descriptive messages

Create a todo list and work through each item systematically. Ask for clarification if needed.
