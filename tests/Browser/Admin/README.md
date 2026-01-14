# Admin Dashboard & Period Management E2E Tests

## Overview
This directory contains comprehensive End-to-End (E2E) Playwright browser tests for the Admin Dashboard and Period Management features of the Vote System application.

## Test File
- **AdminDashboardTest.php** - Complete E2E test suite covering all admin dashboard and period management scenarios

## Test Coverage

### Test 1: Admin Dashboard Loads Successfully
- Verifies dashboard loads with all elements
- Checks stat cards (Kategori 1, Kategori 2, Status Periode)
- Validates dashboard statistics accuracy
- Confirms no console errors
- Tests active period warning display
- Handles empty periods list state

### Test 2: Admin Can View Periods List
- Verifies periods list displays correctly
- Tests status filtering (draft, open, closed, announced)
- Validates period columns (Name, Status, Dates, Actions)
- Confirms no console errors on list view

### Test 3: Admin Can Create New Period
- Tests period creation with valid data
- Validates all form fields (name, semester, year, dates)
- Confirms success messages
- Verifies period appears in list after creation
- Tests creation without optional dates

### Test 4: Admin Can Edit Period
- Tests editing draft periods
- Validates data updates
- Confirms success messages
- Tests partial data updates

### Test 5: Admin Can Change Period Status
- Tests status change: Draft → Open
- Tests status change: Open → Closed
- Tests status change: Closed → Announced
- Verifies status badge updates
- Confirms appropriate action buttons appear for each status

### Test 6: Admin Cannot Delete Active Period
- Verifies delete protection for open periods
- Verifies delete protection for closed periods
- Tests error messages
- Confirms delete button is not visible for active periods

### Test 7: Admin Can Delete Draft Period
- Tests successful deletion of draft periods
- Verifies deletion from list view
- Tests deletion from detail page
- Confirms success messages

### Test 8: Quick Actions Work
- Tests "Buat Periode Baru" quick action
- Tests "Kelola Kriteria" quick action
- Tests "Import Data SIKEP" quick action
- Verifies navigation to correct pages

### Test 9: Dashboard Statistics Accuracy
- Validates stat card values match database counts
- Tests category 1 employee count
- Tests category 2 employee count
- Tests active period count
- Verifies voting progress display

### Test 10: SIKEP Import Link
- Tests SIKEP import page loads correctly
- Verifies navigation from dashboard
- Confirms upload form is visible

### Additional Tests

#### Validation Tests
- Required field validation (name, semester, year)
- End date must be after start date
- Year range validation (2020-2100)
- Semester selection validation

#### Authorization Tests
- Non-admin users cannot access admin dashboard
- Guest users redirected to login
- Role-based access control

#### Period Detail Tests
- Displays all period information correctly
- Shows correct action buttons for each status
- Tests all status transitions

#### Navigation Tests
- Browser back/forward button functionality
- Link navigation tests

#### Edge Cases
- Year boundary handling
- Period ordering by year and semester
- Multiple periods with different statuses
- Status badge styling for each status

## Running the Tests

### Prerequisites
1. Ensure Playwright browsers are installed:
   ```bash
   npx playwright install chromium
   ```

2. Ensure Laravel dependencies are installed:
   ```bash
   composer install
   npm install
   ```

3. Set up environment:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

### Run All Admin Dashboard Tests
```bash
php artisan test --testsuite=Browser --filter="AdminDashboard"
```

### Run Specific Test Suite
```bash
# Test 1: Dashboard loading
php artisan test --testsuite=Browser --filter="admin_dashboard_loads"

# Test 3: Create period
php artisan test --testsuite=Browser --filter="admin_can_create"

# Test 5: Status changes
php artisan test --testsuite=Browser --filter="admin_can_change_period_status"
```

### Run with Debugging
```bash
# Run with visible browser
php artisan test --testsuite=Browser --filter="AdminDashboard" --headed

# Run with debug mode
php artisan test --testsuite=Browser --filter="AdminDashboard" --debug
```

### Run Specific Test
```bash
php artisan test --testsuite=Browser --filter="admin_can_create_new_period_with_valid_data"
```

## Test Configuration

Browser tests are configured in `tests/Pest.php`:
```php
pest()->browser()
    ->timeout(10000)
    ->withHost('127.0.0.1:8000');
```

## Admin Credentials for Manual Testing
- **NIP**: 199605112025212037
- **Password**: 199605112025212037

Note: Tests use factories to create admin users automatically, so manual credentials are only needed for manual testing.

## Troubleshooting

### Issue: "Playwright is not installed" Error
**Solution**:
```bash
npx playwright install chromium
npx playwright install-deps chromium
```

### Issue: Tests Timeout
**Solution**: Increase timeout in `tests/Pest.php`:
```php
pest()->browser()->timeout(30000)
```

### Issue: Port Already in Use
**Solution**: Ensure no other services are using port 8000. The Pest plugin manages its own server.

### Issue: Database Errors
**Solution**: Ensure database migrations are run:
```bash
php artisan migrate:fresh --seed
```

## Test Output
Tests generate HTML reports in `test-results/` directory:
```bash
npx playwright show-report
```

## Best Practices
1. Tests use factories for data creation
2. Each test is isolated and independent
3. Tests verify both UI and database state
4. All tests check for console errors
5. Tests use descriptive names matching the scenario they test

## Maintenance
When adding new features to admin dashboard or period management:
1. Add corresponding E2E test
2. Ensure test covers happy path and edge cases
3. Run full test suite to catch regressions
4. Update this README with new test descriptions

## Coverage Summary
- **Total Tests**: 60+
- **Test Categories**: 10 main scenarios + additional edge cases
- **Browser**: Chromium (via Playwright)
- **Test Framework**: Pest 4 Browser Testing
- **Assertions**: UI + Database + Console logs

## Notes
- Tests use Laravel's RefreshDatabase trait for clean state
- Tests automatically create admin users via factories
- All tests include `assertNoConsoleErrors()` to catch JavaScript issues
- Tests verify both Inertia page loads and database state changes
