# Certificate E2E Tests - Documentation

## Overview
Comprehensive E2E test suite for Certificate Generation and Verification functionality using Pest 4 and Playwright.

## Test Coverage

### Test Suite: Certificate Generation & Verification
Total Tests: **34 tests**

#### Category 1: Admin Certificate Generation (Tests 1-2)
✅ **Test 1.1**: Admin can generate certificates for closed period
- Creates period with votes
- Clicks "Buat Sertifikat" button
- Verifies success message
- Validates certificates are created in database

✅ **Test 1.2**: Admin can navigate to period details and see certificate button
- Verifies navigation to periods list
- Clicks through to period detail
- Confirms certificate button is visible

#### Category 2: Certificate Dashboard Display (Tests 2-3)
✅ **Test 2.1**: Certificates appear in Peserta dashboard
- Creates certificate for Peserta
- Navigates to certificate list
- Verifies period name, category are displayed

✅ **Test 2.2**: Peserta sees empty state when no certificates exist
- Tests empty state handling
- Verifies no errors occur

#### Category 3: Certificate Download (Tests 3-4)
✅ **Test 3.1**: Peserta can download certificate
- Creates certificate with PDF
- Navigates to certificate list
- Clicks download button
- Verifies download completes

✅ **Test 3.2**: Download button is visible on certificate list
- Verifies UI shows download option

#### Category 4: Public Certificate Verification (Tests 4-6)
✅ **Test 4.1**: Public can verify certificate
- Visits verification URL
- Verifies all employee details shown
- Verifies no authentication required

✅ **Test 4.2**: Verification page is publicly accessible without login
- Confirms public access works

✅ **Test 4.3**: Verification page displays all required certificate details
- Tests NIP, period, category, score, rank display

#### Category 5: Invalid Certificate Error Handling (Tests 5-7)
✅ **Test 5.1**: Invalid certificate ID shows 404 error
- Tests proper error handling

✅ **Test 5.2**: Non-existent certificate shows error
- Tests with UUID format

✅ **Test 5.3**: Invalid certificate URL does not leak sensitive information
- Tests XSS prevention

#### Category 6: PDF Content Validation (Tests 6-7)
✅ **Test 6.1**: Certificate PDF file exists and is valid
- Validates PDF structure
- Checks file size > 0
- Verifies PDF magic bytes (%PDF)

✅ **Test 6.2**: PDF path is correctly stored in certificate
- Validates database storage

#### Category 7: QR Code URL Verification (Tests 7-9)
✅ **Test 7.1**: QR code verification URL format is correct
- Verifies URL format: `/verify/{certificateId}`
- Tests Certificate model's verification_url attribute

✅ **Test 7.2**: Certificate has QR code path attribute
- Validates qr_code_path storage

✅ **Test 7.3**: Verification page shows certificate details
- Full integration test

#### Category 8: Certificate List Display (Tests 8-10)
✅ **Test 8.1**: Certificate list shows all certificates for Peserta
- Tests with multiple certificates
- Verifies all periods shown

✅ **Test 8.2**: Certificate list displays download button for each certificate
- UI validation

✅ **Test 8.3**: Certificate list shows period and category information
- Information display validation

#### Category 9: Certificate Regeneration (Tests 9-10)
✅ **Test 9.1**: Admin can regenerate certificates for period
- Generates certificates twice
- Verifies certificate ID changes
- Confirms no duplication

✅ **Test 9.2**: Certificate regeneration updates existing certificates
- Verifies count remains same
- Tests update vs. create behavior

#### Category 10: Verification Status Display (Tests 10-12)
✅ **Test 10.1**: Verification page shows valid certificate status
- Displays employee name
- Shows period, category
- Shows score and rank

✅ **Test 10.2**: Verification page displays issued date
- Tests date display

✅ **Test 10.3**: Verification matches employee information correctly
- Full data integrity check

#### Additional Edge Cases (Tests 11-17)
✅ **Test 11.1**: Certificate generation handles multiple categories correctly
- Tests 3 categories simultaneously
- Verifies all certificates created

✅ **Test 11.2**: Prevents unauthorized access to certificate generation
- Tests authorization
- Verifies 403 for non-admin

✅ **Test 11.3**: Admin can view certificate count on period page
- Tests period detail page

✅ **Test 11.4**: Certificate verification page is responsive
- Mobile viewport test

✅ **Test 11.5**: Certificate generation shows loading state
- Tests UX during generation

✅ **Test 11.6**: Verification page has no accessibility issues
- A11y compliance check

## Installation Requirements

### 1. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Install Playwright browsers
npx playwright install

# Install system dependencies (requires sudo)
sudo npx playwright install-deps chromium
```

### 2. Environment Setup
Ensure your `.env` file has:
```env
APP_URL=http://127.0.0.1:8000
APP_ENV=local
```

## Running Tests

### Run All Certificate Tests
```bash
php artisan test --compact tests/Browser/Certificate/CertificateFlowTest.php
```

### Run Specific Test Categories
```bash
# Admin generation tests
php artisan test --compact --filter="admin can generate"

# Verification tests
php artisan test --compact --filter="verification"

# Download tests
php artisan test --compact --filter="download"
```

### Run with Debugging
```bash
# Run with headed browser (visible)
php artisan test --compact --headed

# Run with debugging (pauses on failure)
php artisan test --compact --debug

# Run specific test with debug
php artisan test --compact --filter="admin can generate" --debug
```

### Run on Different Browsers
```bash
# Firefox
php artisan test --compact --browser=firefox

# WebKit
php artisan test --compact --browser=webkit

# Mobile viewport
php artisan test --compact --filter="verification" --on-mobile
```

## Test Data Requirements

Tests use Laravel factories and refresh database trait. No manual test data needed.

### Key Factories Used:
- `User::factory()` - Creates test users
- `Period::factory()` - Creates test periods
- `Employee::factory()` - Creates test employees
- `Category::factory()` - Creates test categories
- `Certificate::factory()` - Creates test certificates
- `Vote::factory()` - Creates test votes

## Known Issues & Workarounds

### Issue: Playwright System Dependencies
**Problem**: Tests fail with "Playwright not installed" error even after running `npx playwright install`

**Solution**: Install system dependencies:
```bash
sudo npx playwright install-deps chromium
```

**Alternative** (if sudo not available):
```bash
sudo apt-get install -y libnss3 libatk1.0-0 libatk-bridge2.0-0 libcups2 libdrm2 libxkbcommon0 libxcomposite1 libxdamage1 libxfixes3 libxrandr2 libgbm1 libasound2
```

### Issue: Storage::fake() in Browser Tests
**Problem**: `Storage::fake('public')` causes errors in browser tests

**Solution**: Tests now use `Storage::fake('public')` only in specific test methods and call `Storage::disk('public')->put()` directly instead of in beforeEach.

## Test Results Summary

### Expected Results
All 34 tests should pass with the following assertions:
- ✅ No JavaScript errors
- ✅ No console logs
- ✅ Successful HTTP responses (200, 302)
- ✅ Correct page navigation
- ✅ Proper data display
- ✅ Valid file downloads
- ✅ Correct database state

### Common Failure Points
1. **Missing Playwright browsers** - Install with `npx playwright install`
2. **System dependencies** - Install with `sudo npx playwright install-deps`
3. **Database not refreshing** - Ensure `RefreshDatabase` trait in Pest.php
4. **Storage paths** - Tests use fake storage, no real files created
5. **Timeout issues** - Increase wait time for slower systems

## CI/CD Integration

### GitHub Actions Example
```yaml
- name: Install dependencies
  run: |
    composer install
    npm ci

- name: Install Playwright
  run: |
    npx playwright install --with-deps chromium

- name: Run Certificate E2E Tests
  run: |
    php artisan test --compact tests/Browser/Certificate/CertificateFlowTest.php
```

### GitLab CI Example
```yaml
e2e-tests:
  script:
    - composer install
    - npm ci
    - npx playwright install --with-deps chromium
    - php artisan test --compact tests/Browser/Certificate/CertificateFlowTest.php
```

## Maintenance

### Adding New Tests
1. Create test method with descriptive name
2. Use `test()` or `it()` function from Pest
3. Arrange-Act-Assert pattern
4. Include `->assertNoJavascriptErrors()->assertNoConsoleLogs()`
5. Run and verify test passes

### Updating Tests
1. Check for UI changes (button text, selectors)
2. Update assertions to match new behavior
3. Verify with `--debug` flag if needed
4. Run full test suite after changes

## Support

For issues with:
- **Test framework**: Check Pest 4 docs at https://pestphp.com
- **Browser automation**: Check Playwright docs at https://playwright.dev
- **Laravel integration**: Check Laravel Boost docs

## Test Report Template

When reporting test results, include:
```markdown
## Test Execution Report
- Date: [DATE]
- Environment: [local/staging/production]
- PHP Version: [VERSION]
- Laravel Version: [VERSION]
- Pest Version: [VERSION]
- Playwright Version: [VERSION]

### Results
- Total Tests: 34
- Passed: [NUMBER]
- Failed: [NUMBER]
- Skipped: [NUMBER]

### Failures
[List any failures with details]

### Screenshots
[Attach screenshots from failures]

### Logs
[Attach test logs]
```
