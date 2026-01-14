# Peserta E2E Tests Documentation

## Overview
This directory contains comprehensive End-to-End (E2E) Playwright browser tests for the Peserta Dashboard and related functionality using Pest 4.

## Test Files

### 1. PesertaDashboardTest.php
Contains 15 test scenarios covering the Peserta Dashboard functionality:

- **peserta dashboard loads successfully**: Verifies the dashboard loads without errors
- **peserta can view their profile information**: Validates profile data display
- **peserta can view results when announced**: Checks results visibility based on period status
- **peserta cannot see admin features**: Ensures access control to admin routes
- **peserta cannot access admin period management**: Tests period management access restrictions
- **peserta cannot access admin sikep import**: Tests SIKEP import access restrictions
- **peserta results only show after announcement**: Validates conditional display of results
- **peserta dashboard displays rankings with medals when announced**: Checks ranking display with medals
- **peserta dashboard is responsive on mobile**: Tests mobile responsiveness (375x667)
- **peserta dashboard is responsive on tablet**: Tests tablet responsiveness (iPad)
- **peserta dashboard is responsive on desktop**: Tests desktop responsiveness (1920x1080)
- **peserta dashboard works in dark mode**: Validates dark mode compatibility
- **peserta can access certificates page**: Tests navigation to certificates
- **peserta dashboard certificate cards display correctly**: Validates certificate display on dashboard

### 2. PesertaCertificatesTest.php
Contains 10 test scenarios covering the Certificates page functionality:

- **peserta can access certificates page**: Verifies certificates page access
- **peserta certificates page displays correctly**: Checks page layout and elements
- **peserta can search certificates**: Tests certificate search functionality
- **peserta certificates page shows empty state when no certificates**: Validates empty state display
- **peserta certificate cards display required information**: Checks certificate card information
- **peserta certificates page has verification info**: Validates verification information display
- **peserta certificates page is responsive on mobile**: Tests mobile responsiveness
- **peserta certificates page is responsive on tablet**: Tests tablet responsiveness
- **peserta certificates page works in dark mode**: Validates dark mode compatibility
- **peserta can navigate back from certificates to dashboard**: Tests navigation flow

## Test Credentials

- **NIP**: 199702012022031004
- **Password**: 199702012022031004
- **Role**: Peserta
- **Name**: Muhammad Ilham

## Running the Tests

### Prerequisites
1. Ensure PHP 8.4+ is installed
2. Ensure Node.js and npm are installed
3. Ensure Playwright browsers are installed: `npx playwright install`
4. Ensure Laravel development server can run on port 8000

### Command to Run Tests
```bash
# Run all Peserta tests
./vendor/bin/pest tests/Browser/Peserta/

# Run specific test file
./vendor/bin/pest tests/Browser/Peserta/PesertaDashboardTest.php

# Run specific test
./vendor/bin/pest tests/Browser/Peserta/PesertaDashboardTest.php --filter="dashboard loads"

# Run with debug mode
./vendor/bin/pest tests/Browser/Peserta/ --debug

# Run with headed browser (visible)
./vendor/bin/pest tests/Browser/Peserta/ --headed
```

## Test Data Setup

The tests use the `RefreshDatabase` trait, which means the database is reset before each test. The `beforeEach` hook in each test file creates the necessary test data:

```php
beforeEach(function () {
    // Create test peserta user if not exists
    $user = User::where('nip', '199702012022031004')->first();

    if (!$user) {
        $user = User::factory()->create([
            'name' => 'Muhammad Ilham',
            'nip' => '199702012022031004',
            'email' => 'muhammad.ilham@pa-penajam.go.id',
            'password' => bcrypt('199702012022031004'),
            'role' => 'Peserta',
            'is_active' => true,
        ]);

        // Create associated employee
        $user->employee()->create([
            'nip' => '199702012022031004',
            'nama' => 'Muhammad Ilham',
            'jabatan' => 'Staf',
            'unit_kerja' => 'Bagian Umum',
            'golongan' => 'III/a',
            'tmt' => '2022-03-01',
        ]);
    }
});
```

## Known Issues & Bugs

### Environment Configuration Issue
**Status**: Needs Resolution

**Description**: The Pest Browser plugin is throwing `PlaywrightNotInstalledException` even though:
- Playwright 1.57.0 is installed (required: 1.54.1+)
- All browsers are installed (Chromium, Firefox, WebKit)
- The `playwright run-server --version` command works correctly
- PHP can execute the Playwright commands

**Potential Causes**:
1. Base directory detection issue in Pest Browser plugin
2. Process wait timeout issue during server startup
3. Permission or environment configuration issue

**Workaround Options**:
1. Ensure the Laravel development server is running: `php artisan serve`
2. Try running tests with the project root as current directory
3. Check if the `node_modules/.bin/playwright` path is accessible
4. Verify all Playwright browsers are installed: `npx playwright install --with-deps`

### Test Data Dependencies
Some tests depend on the presence of announced periods and certificates. If these don't exist in the test database, certain test assertions will be skipped gracefully.

## Test Coverage Summary

| Feature | Test Scenarios | Status |
|---------|---------------|---------|
| Dashboard Loading | 3 | ✅ Created |
| Profile Display | 1 | ✅ Created |
| Results Viewing | 3 | ✅ Created |
| Access Control | 3 | ✅ Created |
| Responsiveness | 6 | ✅ Created |
| Dark Mode | 2 | ✅ Created |
| Certificates | 10 | ✅ Created |
| Navigation | 1 | ✅ Created |
| **Total** | **29** | **✅ Created** |

## Assertions Used

The tests use Pest's browser testing assertions including:
- `assertSee()` - Verify text is visible
- `assertDontSee()` - Verify text is not visible
- `assertPathIs()` - Verify current URL path
- `assertNoJavascriptErrors()` - Verify no JS errors
- `assertNoConsoleLogs()` - Verify no console errors
- `assertWait()` - Wait for specified time
- `navigate()` - Navigate to a different URL
- `script()` - Execute JavaScript in the browser

## Future Improvements

1. Add visual regression testing using screenshots
2. Add accessibility testing assertions
3. Test more complex user flows (voting, history)
4. Add performance metrics testing
5. Test with different browser types (Firefox, Safari)
6. Add more detailed error message assertions
7. Test certificate download functionality
8. Add API testing alongside browser tests
