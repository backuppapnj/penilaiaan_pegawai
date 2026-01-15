# Suggested Commands

## Development Server
- Start dev server (Laravel + Vite + Queue + Pail):
  `composer run dev`

## Testing
- Run all tests (PHP):
  `php artisan test`
  `composer run test` (includes linting)
- Run specific test file:
  `php artisan test tests/Feature/ExampleTest.php`
- Run browser tests:
  `php artisan dusk` (if configured separately, but Pest v4 handles browser tests via `php artisan test` usually if configured) -> *Correction based on `composer.json`: `pestphp/pest-plugin-browser` is installed, so `php artisan test` should suffice, or strictly `php artisan test --group=browser` if grouped.*

## Code Quality
- Fix PHP code style (Pint):
  `vendor/bin/pint` or `composer run lint`
- Lint JS/TS (ESLint):
  `npm run lint`
- Format JS/TS (Prettier):
  `npm run format`
- Type check JS/TS:
  `npm run types`

## Database
- Migrate:
  `php artisan migrate`
- Seed:
  `php artisan db:seed`
- Fresh migrate + seed:
  `php artisan migrate:fresh --seed`

## Utilities
- Generate Wayfinder types (TS routes):
  `php artisan wayfinder:generate`
- List routes:
  `php artisan route:list`
- Tinker (REPL):
  `php artisan tinker`
