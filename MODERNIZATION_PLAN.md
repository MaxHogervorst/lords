# Laravel Application Modernization Plan

## Overview
This document outlines a comprehensive plan to modernize the Laravel application to follow current PHP 8.4 and Laravel 12 best practices.

---

## Phase 1: Security & Critical Issues (Priority: Critical)

### 1.1 Fix File Download Security Vulnerability
**Location:** `routes/web.php:32-44`
- [ ] Move download logic to dedicated controller
- [ ] Add proper file path validation
- [ ] Implement authorization checks
- [ ] Add file existence validation
- [ ] Use Laravel's `Storage` facade instead of raw file operations

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
- [ ] Install `spatie/data-transfer-object` or `spatie/laravel-data`

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
- [ ] `StoreOrderRequest`
- [ ] `StoreMemberRequest`
- [ ] `StoreGroupRequest`
- [ ] `StoreProductRequest`

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
- [ ] `GroupController` - use form requests
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
- [ ] Add model factories for all models
- [ ] Add database seeders

### 5.2 Optimize Queries
- [ ] Review N+1 query problems
- [ ] Add eager loading where needed
- [ ] Implement query scopes for common filters
- [ ] Add database indexes for frequently queried columns
- [ ] Use `select()` to limit retrieved columns

---

## Phase 6: Route Optimization (Priority: Medium)

### 6.1 Refactor Route File
**Location:** `routes/web.php`
- [ ] Remove commented code
- [ ] Move download route to controller
- [ ] Use route model binding
- [ ] Group routes more logically
- [ ] Add route names to all routes

### 6.2 Implement Route Model Binding
- [ ] Use implicit binding where possible
- [ ] Create custom route bindings for complex lookups
- [ ] Update controller methods to accept model parameters

---

## Phase 7: Code Quality & Standards (Priority: Medium)

### 7.1 Remove Magic Strings
- [ ] Create config files for constants
- [ ] Use enums for status values (PHP 8.1+)
- [ ] Create constants classes where appropriate
- [ ] Examples: SEPA creditor info, invoice statuses

### 7.2 Add Type Hints Everywhere
- [ ] Add property type hints to controllers
- [ ] Add return types to all methods
- [ ] Add parameter types to all methods
- [ ] Enable strict types: `declare(strict_types=1);`

### 7.3 Remove Dead Code
- [ ] Remove commented code in routes
- [ ] Remove commented code in controllers
- [ ] Remove unused imports
- [ ] Remove unused methods

### 7.4 Implement Logging
- [ ] Add structured logging for important operations
- [ ] Log SEPA file generation
- [ ] Log invoice generation
- [ ] Log authentication attempts
- [ ] Use log channels appropriately

---

## Phase 8: Testing (Priority: Medium)

### 8.1 Increase Test Coverage
Current structure appears to have Pest tests already.
- [ ] Add feature tests for all controllers
- [ ] Add unit tests for services
- [ ] Add unit tests for actions
- [ ] Add integration tests for SEPA generation
- [ ] Add integration tests for invoice generation
- [ ] Target: 80%+ code coverage

### 8.2 Add Database Testing
- [ ] Use in-memory SQLite for tests
- [ ] Create comprehensive factories
- [ ] Test database transactions
- [ ] Test model relationships

### 8.3 Add Browser Tests
Playwright is already set up.
- [ ] Test authentication flow
- [ ] Test invoice generation flow
- [ ] Test member management
- [ ] Test group management
- [ ] Test order creation

---

## Phase 9: Performance Optimization (Priority: Low)

### 9.1 Implement Caching Strategy
- [ ] Cache invoice group queries
- [ ] Cache product lists
- [ ] Implement cache tags
- [ ] Add cache invalidation logic
- [ ] Consider Redis for session storage

### 9.2 Optimize Database
- [ ] Add missing indexes
- [ ] Review query performance with Debugbar
- [ ] Optimize N+1 queries
- [ ] Consider database query caching

### 9.3 Implement Queue Jobs
- [ ] Move SEPA file generation to queue
- [ ] Move Excel export to queue
- [ ] Move email notifications to queue
- [ ] Set up queue workers

---

## Phase 10: Frontend & Views (Priority: Low)

### 10.1 Update Blade Templates
- [ ] Review and optimize view queries
- [ ] Implement view composers for shared data
- [ ] Add Blade components for reusable UI
- [ ] Remove logic from views

### 10.2 Modern Frontend Build
Already has npm setup.
- [ ] Review and update npm dependencies
- [ ] Implement proper asset versioning
- [ ] Consider Vite if not already using
- [ ] Add frontend linting

---

## Phase 11: Documentation (Priority: Low)

### 11.1 Add Code Documentation
- [ ] Document all public methods
- [ ] Add PHPDoc blocks with parameter descriptions
- [ ] Document complex business logic
- [ ] Add inline comments for non-obvious code

### 11.2 Add Project Documentation
- [ ] API documentation (if applicable)
- [ ] Setup/installation guide
- [ ] Deployment guide
- [ ] Architecture overview
- [ ] Contributing guidelines

---

## Phase 12: Configuration & Environment (Priority: Low)

### 12.1 Review Configuration Files
- [ ] Move hardcoded values to config files
- [ ] Use environment variables appropriately
- [ ] Document required environment variables
- [ ] Add `.env.example` with all variables

### 12.2 Implement Feature Flags
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

### Estimated Timeline
- **Phase 1 (Critical)**: 1 week
- **Phase 2 (Auth)**: 2 weeks
- **Phase 3 (Architecture)**: 3 weeks
- **Phase 4 (Controllers)**: 3 weeks
- **Phase 5 (Models)**: 2 weeks
- **Phase 6 (Routes)**: 1 week
- **Phase 7 (Code Quality)**: 2 weeks
- **Phase 8 (Testing)**: 3 weeks
- **Phase 9 (Performance)**: 1 week
- **Phase 10 (Frontend)**: 1 week
- **Phase 11 (Documentation)**: 1 week
- **Phase 12 (Config)**: 1 week

**Total Estimated Time**: ~21 weeks (5 months)

### Success Metrics
- [ ] All PHPStan level 8 checks pass
- [ ] 80%+ test coverage
- [ ] No security vulnerabilities
- [ ] All controllers under 200 lines
- [ ] All methods under 20 lines
- [ ] Response time improved by 30%
- [ ] Zero N+1 query issues

---

## Notes
- Backup database before each phase
- Test in staging environment first
- Monitor error logs during rollout
- Consider feature flags for major changes
- Keep stakeholders informed of progress
