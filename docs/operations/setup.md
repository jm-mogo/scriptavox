# Setup (Local Development)

This project follows the standard Laravel + Vue 3 + Inertia.js setup. Use the commands below to get running locally.

## Prerequisites

- PHP 8.2+
- Composer
- Node.js & npm
- A local database (MySQL, or SQLite)

## Installation

1. **Clone and enter the repo**
   ```bash
   git clone https://github.com/jm-mogo/scriptavox.git
   cd scriptavox
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   
   If using SQLite (as suggested by the post-create script), create the DB file:
   ```bash
   touch database/database.sqlite
   ```
   Then update `DB_CONNECTION=sqlite` in `.env` (and clear other DB_* vars as appropriate).

4. **Migrate**
   ```bash
   php artisan migrate
   ```

## Run the dev server

The repo defines a convenient `composer dev` script that runs everything together:
```bash
composer dev
```

This starts (via concurrently):
- `php artisan serve --port=8007` (server)
- `php artisan queue:listen --tries=1` (queue)
- `php artisan pail --timeout=0` (logs)
- `npm run dev` (Vite)

If you prefer to run them separately, you can do that too.

## Notes

- The app uses Inertia.js with Vue 3 and TypeScript. Frontend assets are built with Vite.
- For SSR, see `composer dev:ssr` (builds assets first then runs SSR).
