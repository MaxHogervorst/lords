# Laravel Application Modernization Plan

## Overview
This document outlines the remaining tasks to modernize the Laravel application to follow current PHP 8.4 and Laravel 12 best practices.

**Completed:** Route optimization, test infrastructure, basic security improvements

---

## Phase 1: Security & Critical Issues (Priority: Critical)

### 1.1 Use Laravel Storage Facade
**Location:** `app/Http/Controllers/SepaController.php`
- [ ] Replace `storage_path()` with `Storage` facade
- [ ] Use proper disk configuration for SEPA files

### 1.2 Fix Session Security Issues
**Locations:** `InvoiceController.php:330, 348`
- [ ] Replace storing model objects in session with IDs only
- [ ] Create helper methods to retrieve models by ID
- [ ] Add proper session validation

### 1.3 Add Mass Assignment Protection
**Location:** All models in `app/Models/`
- [ ] Add `$fillable` or `$guarded` to all models
- [ ] Document which fields are mass-assignable
- [ ] Review for security implications

### 1.4 Implement Proper Authorization
- [ ] Create policies for: Member, Group, Product, Order, Invoice
- [ ] Replace middleware authorization with policy gates
- [ ] Add policy checks in controllers
- [ ] Remove inline authorization logic

---

## Phase 2: Authentication Modernization (Priority: High)

### 2.1 Migrate from Cartalyst Sentinel to Laravel Sanctum/Fortify
- [ ] Install Laravel Fortify
- [ ] Create migration script for user data
- [ ] Map Sentinel roles/permissions to Laravel Gates/Policies
- [ ] Update authentication middleware
- [ ] Update all `Sentinel::` calls to `Auth::`
- [ ] Test authentication flows thoroughly
- [ ] Remove Sentinel package

### 2.2 Update Middleware
**Location:** `app/Http/Middleware/`
- [ ] Replace custom `Authenticate` middleware with Laravel's default
- [ ] Refactor `RedirectIfNotAAdmin` to use policies
- [ ] Update middleware registration in `bootstrap/app.php`
- [ ] Remove unused middleware aliases

---

## Phase 3: Architecture Refactoring (Priority: High)

### 3.1 Implement Service Layer
Create services for business logic:
- [ ] `InvoiceService` - handle invoice calculations and generation
- [ ] `SepaService` - handle SEPA file generation
- [ ] `MemberService` - handle member operations
- [ ] `OrderService` - handle order processing
- [ ] `ExcelExportService` - handle Excel generation

### 3.2 Implement Repository Pattern
Create repositories for data access:
- [ ] `MemberRepository`
- [ ] `GroupRepository`
- [ ] `OrderRepository`
- [ ] `InvoiceRepository`
- [ ] `ProductRepository`
- [ ] Bind repositories in `AppServiceProvider`

### 3.3 Implement Action Classes
For single-responsibility operations:
- [ ] `CreateInvoiceGroupAction`
- [ ] `GenerateSepaFileAction`
- [ ] `CalculateMemberOrdersAction`
- [ ] `CalculateGroupOrdersAction`
- [ ] `ExportInvoicesToExcelAction`

### 3.4 Create Data Transfer Objects (DTOs)
- [ ] `InvoiceData`
- [ ] `MemberData`
- [ ] `SepaTransferData`
- [ ] `OrderData`
- [ ] Install `spatie/laravel-data`

---

## Phase 4: Controller Refactoring (Priority: High)

### 4.1 Slim Down InvoiceController
**Current:** 412 lines with business logic
- [ ] Extract calculation logic to services
- [ ] Move SEPA generation to dedicated service
- [ ] Move Excel export to dedicated service
- [ ] Keep controller methods under 20 lines each
- [ ] Use dependency injection for services

### 4.2 Implement Form Request Validation
Replace `Validator::make()` with Form Requests:
- [ ] `StoreInvoiceGroupRequest`
- [ ] `SelectInvoiceGroupRequest`
- [ ] `SetPersonRequest`
- [ ] `SetPersonalInvoiceGroupRequest`

**Note:** Already implemented: `StoreOrderRequest`, `StoreMemberRequest`, `StoreGroupRequest`, `StoreProductRequest`, `StoreFiscusRequest`, `UpdateFiscusRequest`

### 4.3 Implement API Resources
For consistent JSON responses:
- [ ] `MemberResource`
- [ ] `GroupResource`
- [ ] `OrderResource`
- [ ] `InvoiceGroupResource`
- [ ] `ProductResource`
- [ ] Replace raw array returns with resources

### 4.4 Refactor All Controllers
Apply consistent patterns:
- [ ] `AuthController` - use actions
- [ ] `FiscusController` - extract services
- [ ] `GroupController` - clean up logic
- [ ] `HomeController` - optimize queries
- [ ] `InvoiceController` - major refactor (covered above)
- [ ] `MemberController` - use repositories
- [ ] `OrderController` - use actions
- [ ] `ProductController` - use form requests
- [ ] `SepaController` - extract to service

---

## Phase 5: Model & Database Layer (Priority: Medium)

### 5.1 Enhance Models
- [ ] Add proper PHPDoc blocks
- [ ] Add property type hints using `@property` annotations
- [ ] Implement casting for date/boolean fields
- [ ] Add accessors/mutators where needed
- [ ] Add database seeders

**Note:** Model factories already exist for all models

### 5.2 Optimize Queries
- [ ] Review N+1 query problems
- [ ] Add eager loading where needed
- [ ] Implement query scopes for common filters
- [ ] Add database indexes for frequently queried columns
- [ ] Use `select()` to limit retrieved columns

---

## Phase 6: Code Quality & Standards (Priority: Medium)

### 6.1 Remove Magic Strings
- [ ] Create config files for constants
- [ ] Use enums for status values (PHP 8.1+)
- [ ] Create constants classes where appropriate
- [ ] Examples: SEPA creditor info, invoice statuses

### 6.2 Add Type Hints Everywhere
- [ ] Add property type hints to controllers
- [ ] Add return types to all methods
- [ ] Add parameter types to all methods
- [ ] Enable strict types: `declare(strict_types=1);`

**Note:** Many controllers already use strict types and type hints

### 6.3 Remove Dead Code
- [ ] Remove commented code in controllers
- [ ] Remove unused imports
- [ ] Remove unused methods

### 6.4 Implement Logging
- [ ] Add structured logging for important operations
- [ ] Log SEPA file generation
- [ ] Log invoice generation
- [ ] Log authentication attempts
- [ ] Use log channels appropriately

---

## Phase 7: Testing (Priority: Medium)

### 7.1 Add Unit Tests for Actions
- [ ] Add unit tests for action classes (once implemented)
- [ ] Maintain 80%+ code coverage

**Current Status:** 142 tests, 349 assertions, strong test coverage

### 7.2 Re-enable Browser Tests
- [ ] Fix Playwright configuration issues
- [ ] Re-enable browser tests in phpunit.xml
- [ ] Add remaining browser test coverage:
  - [ ] Invoice generation flow
  - [ ] Member management
  - [ ] Group management
  - [ ] Order creation

---

## Phase 8: Performance Optimization (Priority: Low)

### 8.1 Implement Caching Strategy
- [ ] Cache invoice group queries
- [ ] Cache product lists
- [ ] Implement cache tags
- [ ] Add cache invalidation logic
- [ ] Consider Redis for session storage

### 8.2 Optimize Database
- [ ] Add missing indexes
- [ ] Review query performance with Debugbar
- [ ] Optimize N+1 queries
- [ ] Consider database query caching

### 8.3 Implement Queue Jobs
- [ ] Move SEPA file generation to queue
- [ ] Move Excel export to queue
- [ ] Move email notifications to queue
- [ ] Set up queue workers

---

## Phase 9: Frontend & Views (Priority: Low)

### 9.1 Update Blade Templates
- [ ] Review and optimize view queries
- [ ] Implement view composers for shared data
- [ ] Add Blade components for reusable UI
- [ ] Remove logic from views

### 9.2 Modern Frontend Build
- [ ] Review and update npm dependencies
- [ ] Implement proper asset versioning
- [ ] Consider Vite if not already using
- [ ] Add frontend linting

---

## Phase 10: Documentation (Priority: Low)

### 10.1 Add Code Documentation
- [ ] Document all public methods
- [ ] Add PHPDoc blocks with parameter descriptions
- [ ] Document complex business logic
- [ ] Add inline comments for non-obvious code

### 10.2 Add Project Documentation
- [ ] API documentation (if applicable)
- [ ] Setup/installation guide
- [ ] Deployment guide
- [ ] Architecture overview
- [ ] Contributing guidelines

---

## Phase 11: Configuration & Environment (Priority: Low)

### 11.1 Review Configuration Files
- [ ] Move hardcoded values to config files
- [ ] Use environment variables appropriately
- [ ] Document required environment variables
- [ ] Add `.env.example` with all variables

### 11.2 Implement Feature Flags
- [ ] Use Laravel Pennant for feature flags
- [ ] Allow gradual rollout of new features
- [ ] Make migration safer

---

## Implementation Strategy

### Approach
1. **Incremental Migration**: Implement changes incrementally, not all at once
2. **Test-Driven**: Write tests before refactoring
3. **Branch Strategy**: Use feature branches for each phase
4. **Code Review**: Review all changes before merging
5. **Rollback Plan**: Ensure ability to roll back any phase

### Revised Timeline
- **Phase 1 (Security)**: 3-4 days
- **Phase 2 (Auth)**: 2 weeks
- **Phase 3 (Architecture)**: 3 weeks
- **Phase 4 (Controllers)**: 3 weeks
- **Phase 5 (Models)**: 1-2 weeks
- **Phase 6 (Code Quality)**: 1-2 weeks
- **Phase 7 (Testing)**: 3-5 days
- **Phase 8 (Performance)**: 1 week
- **Phase 9 (Frontend)**: 1 week
- **Phase 10 (Documentation)**: 1 week
- **Phase 11 (Config)**: 3-4 days

**Total Remaining Time**: ~13-15 weeks

### Success Metrics
- [ ] All PHPStan level 8 checks pass
- [x] 80%+ test coverage (142 tests, 349 assertions)
- [ ] No security vulnerabilities
- [ ] All controllers under 200 lines
- [ ] All methods under 20 lines
- [ ] Response time improved by 30%
- [ ] Zero N+1 query issues

---

## Completed Items ✓

### Routes & Organization
- ✓ Refactored route file with logical grouping
- ✓ Added route names to all routes
- ✓ Implemented route model binding
- ✓ Moved download logic to controller
- ✓ Removed commented code from routes

### Testing Infrastructure
- ✓ Feature tests for all controllers
- ✓ Unit tests for services
- ✓ Integration tests for SEPA/invoice generation
- ✓ In-memory SQLite for tests
- ✓ Comprehensive model factories
- ✓ Database transaction tests
- ✓ Model relationship tests

### Security Improvements
- ✓ Moved file download logic to dedicated controller
- ✓ Added file path validation
- ✓ Added authorization checks (via middleware)
- ✓ Added file existence validation

---

## Notes
- Backup database before each phase
- Test in staging environment first
- Monitor error logs during rollout
- Consider feature flags for major changes
- Keep stakeholders informed of progress
- Browser tests temporarily excluded but exist
