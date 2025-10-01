# Test Coverage Baseline - Phase 0

## Current Test Status

**Total Tests**: 19 tests across 5 test files
**Test Result**: Most passing (1 failure in GroupTest due to missing invoice group)
**Estimated Coverage**: 15-25%
**Target Coverage**: 70%

## Existing Tests

### MemberTest.php (3 tests)
- ✅ testCreateMember - Create member with validation
- ✅ testEditMember - Update member details including SEPA info
- ✅ testDeleteMember - Delete member from database

### OrderTest.php (1 test)
- ✅ testCreateOrder - Create order for member/group with product

### GroupTest.php (5 tests)
- ⚠️ testCreateGroup - Create group (failing: no invoice group)
- ✅ testEditGroup - Update group details
- ✅ testDeleteGroup - Delete group
- ✅ testGroupMembers - Add members to group
- ✅ testDeleteGroupMember - Remove member from group

### ProductTest.php (3 tests)
- ✅ testCreateProduct - Create product with name and price
- ✅ testEditProduct - Update product details
- ✅ testDeleteProduct - Delete product

### LinkCheckTest.php (7 tests)
- ✅ testHome - Home page accessible
- ✅ testMembers - Members page accessible
- ✅ testGroups - Groups page accessible
- ✅ testProducts - Products page accessible
- ✅ testFiscus - Fiscus page accessible (admin)
- ✅ testInvoice - Invoice page accessible (admin)
- ✅ testSepa - SEPA page accessible (admin)

## Coverage Analysis

### Well Covered (15-20% total)
- **Member CRUD** - Full create, read, update, delete operations
- **Product CRUD** - Full CRUD operations
- **Group Management** - CRUD + member association
- **Order Creation** - Basic order creation flow
- **Route Accessibility** - All main routes tested

### Missing Coverage (Controllers)
1. **AuthController** (0% coverage)
   - Login flow
   - Logout flow
   - Authentication validation
   - Session management

2. **FiscusController** (0% coverage)
   - Invoice price management
   - Invoice line viewing
   - Financial data manipulation
   - Admin-only access control

3. **HomeController** (0% coverage)
   - Dashboard display
   - User session checks

4. **InvoiceController** (0% coverage) - **CRITICAL**
   - Invoice generation logic
   - PDF export functionality
   - Excel export functionality
   - SEPA file generation
   - Invoice grouping
   - Person assignment to invoices

5. **SepaController** (0% coverage) - **CRITICAL**
   - SEPA XML file generation
   - Payment collection logic
   - Bank file format validation

6. **WelcomeController** (0% coverage)
   - Landing page

### Missing Coverage (Models - 0% unit tests)
- **Member** model methods and relationships
- **Group** model relationships
- **Order** polymorphic relationships
- **Product** model methods
- **InvoiceGroup, InvoiceProduct, InvoiceProductPrice** - Complete invoice system
- **InvoiceLine** - Invoice line item logic

### Missing Coverage (Business Logic)
- Invoice calculation algorithms
- SEPA XML generation
- Payment collection workflows
- Group billing logic
- Member SEPA validation
- Price history tracking

## Priority for New Tests

### High Priority (Critical Business Logic)
1. **InvoiceController Tests** - Invoice generation, PDF/Excel/SEPA
2. **SepaController Tests** - SEPA file generation and validation
3. **AuthController Tests** - Login/logout/session management
4. **Invoice Model Tests** - Calculation logic, relationships
5. **SEPA Model Tests** - Payment collection logic

### Medium Priority
6. **FiscusController Tests** - Financial management
7. **HomeController Tests** - Dashboard
8. **Model Relationship Tests** - All model associations
9. **Model Scope Tests** - Query scopes (Frst, Rcur)

### Low Priority
10. **WelcomeController Tests** - Landing page

## Test Infrastructure Status

✅ **PHPUnit** - Configured and working
✅ **Database Transactions** - Test isolation in place
✅ **Model Factories** - All models have factories
✅ **Test Users** - Seeded with Sentinel authentication
✅ **Xdebug** - Installed for coverage analysis
✅ **Docker** - All tests run via Docker containers
⚠️ **Coverage Report** - HTML output interfering with reporting

## Recommendations for Reaching 70%

1. **Add InvoiceController integration tests** (30-40% of missing coverage)
   - Test invoice generation for individuals
   - Test invoice generation for groups
   - Test PDF/Excel exports (mock or test files)
   - Test SEPA generation

2. **Add AuthController tests** (10% of missing coverage)
   - Login success/failure
   - Logout
   - Session management

3. **Add Model unit tests** (15-20% of missing coverage)
   - Test all relationships
   - Test scopes
   - Test custom methods (getFullName, etc.)

4. **Add SepaController tests** (5-10% of missing coverage)
   - SEPA file creation
   - Payment validation

5. **Add FiscusController tests** (5-10% of missing coverage)
   - Invoice price viewing
   - Invoice line management

**Estimated tests needed**: 40-60 new test methods to reach 70% coverage

## Next Steps

1. Fix failing GroupTest (create invoice group)
2. Add InvoiceController test suite
3. Add AuthController test suite
4. Add Model relationship tests
5. Add SepaController tests
6. Re-run coverage analysis
7. Verify 70% threshold achieved
