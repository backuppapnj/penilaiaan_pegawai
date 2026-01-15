# Code Style & Conventions

## PHP / Laravel
- **Style**: Follow PSR-12/Laravel standards. Use `Laravel Pint` for formatting.
- **Naming**: Descriptive variables/methods (e.g., `isRegistered` vs `check`).
- **Constructors**: Use PHP 8 constructor property promotion.
- **Types**: Explicit return types and parameter type hints are mandatory.
- **Models**: Use Eloquent relationships with type hints.
- **Controllers**: Use Form Requests for validation. Return `Inertia::render`.
- **Tests**: Use Pest. Prefer Feature tests. Use factories.

## React / Inertia
- **Components**: Functional components in `resources/js/Pages` (pages) or `resources/js/components`.
- **Navigation**: Use `Link` component or `router.visit`.
- **Forms**: Use Inertia v2 `<Form>` component or `useForm`.
- **Styling**: Tailwind CSS v4 utility classes.
- **Routes**: Use `Wayfinder` for type-safe route generation (e.g., `import { show } from '@/actions/...'`).

## Documentation
- **Comments**: Sparse, explain *why* not *what*. Prefer PHPDoc blocks for complex types.
