# E2E Testing Quick Guide

## Prerequisites

Ensure the following are installed and configured:
- Laravel 12 application running
- Pest 4 testing framework
- Pest Browser plugin v4.2.1
- Playwright v1.54.1+
- Chromium browser

## Setup Instructions

### 1. Install Dependencies

```bash
# Install Pest Browser plugin
composer require pestphp/pest-plugin-browser:^4.0 --dev

# Install Playwright
npm install playwright@latest --save-dev

# Install Playwright browsers
npx playwright install chromium

# Install Pest Browser plugin dependencies
cd vendor/pestphp/pest-plugin-browser && npm install
```

### 2. Configure Environment

Update `/tests/Pest.php`:
```php
pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Browser');

pest()->browser()
    ->timeout(10000)
    ->withHost('127.0.0.1:8000');
```

### 3. Start Laravel Server

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

## Running Tests

### Run All E2E Tests
```bash
php artisan test tests/Browser/Voting/VotingE2ETest.php --compact
```

### Run Specific Test
```bash
php artisan test tests/Browser/Voting/VotingE2ETest.php --filter="test name here"
```

### Run All Browser Tests
```bash
php artisan test tests/Browser/ --compact
```

### Run with Browser Visibility
```bash
php artisan test tests/Browser/Voting/VotingE2ETest.php --with-browser
```

## Test Structure

### Test File: `tests/Browser/Voting/VotingE2ETest.php`

Contains 15 comprehensive E2E tests covering:
- Dashboard functionality
- Voting page access
- Form validation
- Vote submission
- Duplicate prevention
- Voting history
- Period restrictions
- Self-voting prevention

## Writing New Tests

### Basic Test Template

```php
test('description of what is being tested', function () {
    // 1. Setup: Create test data
    $period = Period::factory()->create(['status' => 'open']);
    $category = Category::factory()->create();
    $employee = Employee::factory()->create(['category_id' => $category->id]);

    // 2. Act: Authenticate and perform action
    $this->actingAs($this->penilai);

    visit('/penilai/voting')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();

    // 3. Assert: Verify expected outcome
    $this->assertDatabaseHas('votes', [
        'period_id' => $period->id,
        'employee_id' => $employee->id,
    ]);
});
```

### Common Assertions

```php
// Browser assertions
->assertSee($text)
->assertDontSee($text)
->assertPathIs($path)
->assertStatus($status)
->assertNoJavascriptErrors()
->assertNoConsoleLogs()

// Database assertions
$this->assertDatabaseHas($table, $conditions);
$this->assertDatabaseMissing($table, $conditions);
$this->assertDatabaseCount($table, $count);

// Response assertions
$response->assertSuccessful();
$response->assertRedirect();
$response->assertSessionHas('success');
$response->assertSessionHasErrors('field');
```

## Troubleshooting

### Issue: PlaywrightNotInstalledException

**Solution**:
```bash
# Ensure correct Playwright version
npm install playwright@1.54.1

# Install browsers
npx playwright install chromium

# Install plugin dependencies
cd vendor/pestphp/pest-plugin-browser && npm install
```

### Issue: Tests Timeout

**Solution**: Increase timeout in `Pest.php`:
```php
pest()->browser()
    ->timeout(30000)  // 30 seconds
    ->withHost('127.0.0.1:8000');
```

### Issue: Database Errors

**Solution**: Ensure RefreshDatabase is working:
```php
pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Browser');
```

## Test Data Factories

### Available Factories

```php
// Create a period
$period = Period::factory()->create([
    'status' => 'open',
    'name' => 'Test Period',
]);

// Create a category
$category = Category::factory()->create([
    'nama' => 'Kategori 1',
    'urutan' => 1,
]);

// Create employees
$employees = Employee::factory()->count(5)->create([
    'category_id' => $category->id,
]);

// Create criteria
$criteria = Criterion::factory()->count(7)->create([
    'category_id' => $category->id,
]);

// Create users
$penilai = User::factory()->penilai()->create();
$admin = User::factory()->admin()->create();
$peserta = User::factory()->peserta()->create();
```

## Best Practices

1. **Use Factories**: Always use factories for test data
2. **Isolate Tests**: Each test should be independent
3. **Clean Up**: Use RefreshDatabase trait for automatic cleanup
4. **Descriptive Names**: Use clear, descriptive test names
5. **Arrange-Act-Assert**: Follow the AAA pattern
6. **One Assertion Per Test**: Focus on one behavior per test
7. **Test Edge Cases**: Include negative test cases
8. **No Hardcoded Data**: Use factories and fakers

## Continuous Integration

### GitHub Actions Example

```yaml
name: E2E Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v2

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'

      - name: Install Dependencies
        run: composer install --prefer-dist --no-progress

      - name: Install Node Dependencies
        run: npm install

      - name: Install Playwright
        run: npx playwright install chromium

      - name: Run E2E Tests
        run: php artisan test tests/Browser/ --compact
```

## Resources

- [Pest Documentation](https://pestphp.com)
- [Pest Browser Plugin](https://github.com/pestphp/pest-plugin-browser)
- [Playwright Documentation](https://playwright.dev)
- [Laravel Testing](https://laravel.com/docs/testing)

## Support

For issues or questions:
1. Check the main E2E_TEST_REPORT.md for detailed information
2. Review test files in `/tests/Browser/Voting/`
3. Consult Laravel and Pest documentation
4. Check application logs: `storage/logs/laravel.log`

---

**Last Updated**: January 14, 2026
