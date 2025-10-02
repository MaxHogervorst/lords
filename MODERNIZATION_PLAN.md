# Laravel Application Modernization Plan

## Overview
This document outlines tasks to modernize the Laravel application to follow current PHP 8.4 and Laravel 12 best practices.

**Current Status:** 154 tests passing (387 assertions), 55% reduction in InvoiceController

---

## ✅ Completed Work

### Architecture & Refactoring
- ✅ **Service Layer** (Phase 3.1) - Created 5 services: InvoiceCalculation, SepaGeneration, InvoiceExport, Member, Order
- ✅ **Repository Pattern** (Phase 3.2) - Implemented base repository with interface, 5 domain repositories registered as singletons
- ✅ **InvoiceController Refactoring** (Phase 4.1) - Reduced from 442 to 199 lines (55% reduction)
- ✅ **Query Optimization** (Phase 5.2) - Fixed N+1 issues, added eager loading, query scopes, comprehensive indexes

### Infrastructure
- ✅ **Routes** - Logical grouping, route names, model binding
- ✅ **Testing** - 154 tests with comprehensive coverage (feature, unit, integration)
- ✅ **Security** - File download validation, path checking, authorization middleware

### Database Optimizations
- ✅ **Indexes** - Added 10 indexes across 8 tables for foreign keys and filtered columns
- ✅ **Query Scopes** - Order, Product, Group, Member scopes
- ✅ **Eager Loading** - BaseRepository supports relations, N+1 issues resolved
- ✅ **Caching** - Product cache, invoice group cache with invalidation

---

## 🚨 High Priority (Next 4-6 weeks)

### Phase 1: Security & Critical Issues (1-2 weeks)

#### 1.1 Use Laravel Storage Facade
**Location:** `app/Http/Controllers/SepaController.php`
- [ ] Replace `storage_path()` with `Storage` facade
- [ ] Use proper disk configuration for SEPA files

#### 1.2 Fix Session Security Issues
**Locations:** `InvoiceController.php:330, 348`
- [ ] Replace storing model objects in session with IDs only
- [ ] Create helper methods to retrieve models by ID
- [ ] Add proper session validation

#### 1.3 Add Mass Assignment Protection
**Location:** All models in `app/Models/`
- [ ] Add `$fillable` or `$guarded` to all models (most already done)
- [ ] Review for security implications

#### 1.4 Implement Proper Authorization
- [ ] Create policies for: Member, Group, Product, Order, Invoice
- [ ] Replace middleware authorization with policy gates
- [ ] Add policy checks in controllers

---

### Phase 2: Authentication Modernization (1 week)

**Note:** Simple auth needs (login/logout/admin check only). Laravel's built-in Auth is sufficient.

#### 2.1 Migrate from Cartalyst Sentinel to Laravel Auth
**Current usage:** 5 Sentinel calls in app, 171 in tests
- [ ] Add `isAdmin()` method to User model
- [ ] Update `AuthController` to use `Auth::attempt()` and `Auth::logout()`
- [ ] Replace `Sentinel::check()` with `Auth::check()` in middleware
- [ ] Replace `Sentinel::inRole('admin')` with admin check
- [ ] Update all test files (171 uses of `Sentinel::login()` → `actingAs()`)
- [ ] Test authentication flows thoroughly
- [ ] Remove Sentinel package and config

#### 2.2 Implement Admin Authorization
- [ ] Add `is_admin` boolean to users table (already exists)
- [ ] Create `isAdmin()` method on User model
- [ ] Replace `RedirectIfNotAAdmin` middleware with Gate
- [ ] Update middleware registration in `bootstrap/app.php`
- [ ] Remove custom `Authenticate` middleware

---

### Phase 4: Controller Refactoring (2-3 weeks)

#### 4.2 Implement Form Request Validation
Replace `Validator::make()` with Form Requests:
- [ ] `StoreInvoiceGroupRequest`
- [ ] `SelectInvoiceGroupRequest`
- [ ] `SetPersonRequest`
- [ ] `SetPersonalInvoiceGroupRequest`

**Note:** Already implemented: StoreOrderRequest, StoreMemberRequest, StoreGroupRequest, StoreProductRequest, StoreFiscusRequest, UpdateFiscusRequest

#### 4.3 Implement API Resources
For consistent JSON responses:
- [ ] `MemberResource`
- [ ] `GroupResource`
- [ ] `OrderResource`
- [ ] `InvoiceGroupResource`
- [ ] `ProductResource`

#### 4.4 Refactor Remaining Controllers
- [ ] `AuthController` - use actions
- [ ] `FiscusController` - extract services
- [ ] `GroupController` - clean up logic
- [ ] `HomeController` - use repositories consistently
- [ ] `MemberController` - use repositories
- [ ] `OrderController` - use actions
- [ ] `ProductController` - use form requests
- [ ] `SepaController` - extract to service

---

## 📊 Medium Priority (6-8 weeks)

### Phase 3: Architecture Completion

#### 3.3 Implement Action Classes
- [ ] `CreateInvoiceGroupAction`
- [ ] `GenerateSepaFileAction`
- [ ] `CalculateMemberOrdersAction`
- [ ] `CalculateGroupOrdersAction`
- [ ] `ExportInvoicesToExcelAction`

#### 3.4 Create Data Transfer Objects (DTOs)
- [ ] `InvoiceData`
- [ ] `MemberData`
- [ ] `SepaTransferData`
- [ ] `OrderData`
- [ ] Install `spatie/laravel-data`

---

### Phase 5: Model & Database Layer

#### 5.1 Enhance Models
- [ ] Add proper PHPDoc blocks
- [ ] Add property type hints using `@property` annotations
- [ ] Implement casting for date/boolean fields
- [ ] Add accessors/mutators where needed
- [ ] Add database seeders

**Note:** Model factories already exist for all models

---

### Phase 6: Code Quality & Standards

#### 6.1 Remove Magic Strings
- [ ] Create config files for constants
- [ ] Use enums for status values (PHP 8.1+)
- [ ] Create constants classes
- [ ] Examples: SEPA creditor info, invoice statuses

#### 6.2 Add Type Hints Everywhere
- [ ] Add property type hints to controllers
- [ ] Add return types to all methods
- [ ] Add parameter types to all methods
- [ ] Enable strict types: `declare(strict_types=1);`

**Note:** Many controllers already use strict types

#### 6.3 Remove Dead Code
- [ ] Remove commented code in controllers
- [ ] Remove unused imports
- [ ] Remove unused methods

#### 6.4 Implement Logging
- [ ] Add structured logging for important operations
- [ ] Log SEPA file generation
- [ ] Log invoice generation
- [ ] Log authentication attempts

---

### Phase 7: Testing

#### 7.1 Add Unit Tests for Actions
- [ ] Add unit tests for action classes (once implemented)
- [ ] Maintain 80%+ code coverage

**Current Status:** 154 tests, 387 assertions ✅

#### 7.2 Re-enable Browser Tests
- [ ] Fix Playwright configuration issues
- [ ] Re-enable browser tests in phpunit.xml
- [ ] Add remaining browser test coverage

---

## 🔧 Low Priority (Later)

### Phase 8: Performance Optimization

#### 8.1 Implement Caching Strategy
- [x] Cache invoice group queries ✅
- [x] Cache product lists ✅
- [ ] Implement cache tags
- [ ] Add cache invalidation logic
- [ ] Consider Redis for session storage

#### 8.2 Optimize Database
- [x] Add missing indexes ✅
- [x] Optimize N+1 queries ✅
- [ ] Review query performance with Debugbar
- [ ] Consider database query caching

#### 8.3 Implement Queue Jobs
- [ ] Move SEPA file generation to queue
- [ ] Move Excel export to queue
- [ ] Move email notifications to queue
- [ ] Set up queue workers

---

### Phase 9: Frontend & Views

#### 9.1 Update Blade Templates
- [ ] Review and optimize view queries
- [ ] Implement view composers for shared data
- [ ] Add Blade components for reusable UI
- [ ] Remove logic from views

#### 9.2 Modern Frontend Build
- [ ] Review and update npm dependencies
- [ ] Implement proper asset versioning
- [ ] Consider Vite if not already using
- [ ] Add frontend linting

---

### Phase 10: Documentation

#### 10.1 Add Code Documentation
- [ ] Document all public methods
- [ ] Add PHPDoc blocks with parameter descriptions
- [ ] Document complex business logic
- [ ] Add inline comments for non-obvious code

#### 10.2 Add Project Documentation
- [ ] API documentation (if applicable)
- [ ] Setup/installation guide
- [ ] Deployment guide
- [ ] Architecture overview
- [ ] Contributing guidelines

---

### Phase 11: Configuration & Environment

#### 11.1 Review Configuration Files
- [ ] Move hardcoded values to config files
- [ ] Use environment variables appropriately
- [ ] Document required environment variables
- [ ] Add `.env.example` with all variables

#### 11.2 Implement Feature Flags
- [ ] Use Laravel Pennant for feature flags
- [ ] Allow gradual rollout of new features
- [ ] Make migration safer

---

## 📈 Success Metrics

**Current Progress:**
- [x] 80%+ test coverage (154 tests, 387 assertions) ✅
- [x] Zero N+1 query issues ✅
- [x] InvoiceController under 200 lines ✅
- [ ] All PHPStan level 8 checks pass
- [ ] No security vulnerabilities
- [ ] All controllers under 200 lines
- [ ] All methods under 20 lines
- [ ] Response time improved by 30%

---

## 📅 Implementation Strategy

### Recommended Next Steps

**Sprint 1 (2 weeks):**
1. Phase 1: Security & Critical Issues
2. Phase 2: Authentication Modernization

**Sprint 2 (2 weeks):**
3. Phase 4.2: Form Request Validation
4. Phase 4.4: Start controller refactoring

**Sprint 3 (2 weeks):**
5. Phase 4.4: Complete controller refactoring
6. Phase 4.3: API Resources

**Sprint 4 (2 weeks):**
7. Phase 3.3: Action Classes
8. Phase 5.1: Enhance Models

**Later Sprints:**
9. Phase 6: Code Quality & Standards
10. Phase 7: Testing improvements
11. Phase 8-11: Performance, Frontend, Documentation

### Approach
1. **Incremental Migration**: Implement changes incrementally
2. **Test-Driven**: Write tests before refactoring
3. **Branch Strategy**: Use feature branches for each phase
4. **Code Review**: Review all changes before merging
5. **Rollback Plan**: Ensure ability to roll back any phase

### Timeline Estimate
- **High Priority Work**: 4-6 weeks
- **Medium Priority Work**: 6-8 weeks
- **Low Priority Work**: 3-4 weeks
- **Total**: ~13-18 weeks remaining

---

## 📝 Notes
- Backup database before each phase
- Test in staging environment first
- Monitor error logs during rollout
- Consider feature flags for major changes
- Keep stakeholders informed of progress
