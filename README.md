# FocusFlow

FocusFlow is a multi-tenant team productivity SaaS built with Laravel 11. It provides task management, workspace collaboration, and real-time features.

## Installation & Execution

1. Clone the repository.
2. Run `composer install`.
3. Run `pnpm install` and `pnpm run build`.
4. Copy `.env.example` to `.env` and configure your environment variables.
5. Generate an app key: `php artisan key:generate`.
6. Run migrations: `php artisan migrate`.
7. Start the local server: `php artisan serve`.
8. Start Reverb for WebSockets (if applicable): `php artisan reverb:start`.

## Required Environment Variables

Ensure the following variables are configured in your `.env` file:
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (Database connection details)
- `REVERB_APP_ID`, `REVERB_APP_KEY`, `REVERB_APP_SECRET` (For WebSockets)
- `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET` (For Billing)
- `SLACK_WEBHOOK_URL` (For Slack integration)

## Running Tests

This project uses Pest PHP for testing.

Run the test suite using:
```bash
php artisan test
```
Or use pest directly:
```bash
./vendor/bin/pest
```
