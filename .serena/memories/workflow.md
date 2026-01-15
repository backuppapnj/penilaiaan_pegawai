# Workflow

## Development Phases
The project follows "Context-Driven Development" with 7 phases:
1. Planning
2. Database & Models
3. Auth & Authorization
4. Core Features (Import, Categories)
5. Dashboard & UI
6. Certificates
7. Testing & Deployment

## Verification
- Manual verification required after each phase.
- **Tests**: Run `php artisan test` (Pest).
- **Linting**: Run `npm run lint` and `composer run lint` (Pint).

## Frontend Bundling
- Changes to frontend require `npm run build` or running the dev server `composer run dev` / `npm run dev`.
