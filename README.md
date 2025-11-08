# YoPrint Laravel Application

This repository contains the YoPrint web application built with the Laravel 12 framework and PHP 8.3. The guide below covers everything you need to get the project running locally, including environment setup, common workflows, and troubleshooting tips.

---

## 1. Requirements

Make sure the following tools are installed on your machine:

- PHP 8.3 (along with `mbstring`, `pdo_sqlite`, `openssl`, `curl`, `xml`, `fileinfo`)
- Composer 2.5 or newer
- Node.js 20 and npm 10 (for Vite asset compilation)
- SQLite 3 (default development database) or another database driver configured in `.env`
- Git

Optional but recommended:

- Redis server (if you want to replace the default queue/cache driver)
- A process manager such as [Laravel Herd](https://herd.laravel.com) or [Valet](https://laravel.com/docs/valet)

---

## 2. Clone the Repository

```bash
git clone https://github.com/YOUR_ORG/yoprint_laravel_coding_project_php83.git
cd yoprint_laravel_coding_project_php83
```

---

## 3. Environment Configuration

1. Copy the example environment file and update any values as needed:
   ```bash
   cp .env.example .env
   ```
2. Generate the application key (if not running the automated setup script below):
   ```bash
   php artisan key:generate
   ```
3. Database configuration:
   - By default the project uses SQLite. Ensure the file `database/database.sqlite` exists:
     ```bash
     touch database/database.sqlite
     ```
   - Update `DB_CONNECTION`, `DB_DATABASE`, and other settings in `.env` if you prefer MySQL, PostgreSQL, etc.
4. Mail, queue, cache, and filesystem settings can stay at their defaults for local development. Adjust them in `.env` as required.

---

## 4. Install Dependencies

Run the automated setup script (recommended):

```bash
composer run setup
```

This will:

- Install PHP dependencies
- Copy `.env` if it does not yet exist
- Generate the application key
- Run database migrations (`php artisan migrate --force`)
- Install JavaScript dependencies
- Build production assets (`npm run build`)

### Manual installation (if you prefer granular control)

```bash
composer install
php -r "file_exists('.env') || copy('.env.example', '.env');"
php artisan key:generate
php artisan migrate
npm install
npm run build
```

---

## 5. Running the Application in Development

### Option A: Use the bundled dev script

```bash
composer run dev
```

This starts the following services in parallel using `concurrently`:

- Laravel HTTP server (`php artisan serve`)
- Queue listener (`php artisan queue:listen --tries=1`)
- Log tailing via Laravel Pail
- Vite dev server for live asset reloading (`npm run dev`)

All processes stop together when you exit the command.

### Option B: Run processes manually

1. Start the Laravel application:
   ```bash
   php artisan serve
   ```
2. In another terminal, run the queue worker if your features require queued jobs:
   ```bash
   php artisan queue:listen --tries=1
   ```
3. In a third terminal, compile frontend assets with Vite:
   ```bash
   npm run dev
   ```

Visit the app at [http://127.0.0.1:8000](http://127.0.0.1:8000). The Vite dev server typically listens on [http://127.0.0.1:5173](http://127.0.0.1:5173).

---

## 6. Running Database Migrations and Seeders

- Apply fresh migrations:
  ```bash
  php artisan migrate:fresh
  ```
- Seed the database (if seeders are available):
  ```bash
  php artisan db:seed
  ```
- Run both in one go:
  ```bash
  php artisan migrate --seed
  ```

---

## 7. Building Assets for Production

When you are ready to generate optimized static assets:

```bash
npm run build
```

This command outputs compiled CSS and JavaScript to `public/build`, ready for deployment.

---

## 8. Running Tests and Static Analysis

- Execute the default PHPUnit test suite:
  ```bash
  composer run test
  ```
- Run only the Laravel test command:
  ```bash
  php artisan test
  ```
- Run Pint (Laravel’s opinionated code style formatter):
  ```bash
  ./vendor/bin/pint
  ```

---

## 9. Common Troubleshooting

- **Missing PHP extensions**: Verify that required extensions are enabled (`php -m`). Install missing ones via Homebrew or your OS package manager.
- **Permission errors on `storage` or `bootstrap/cache`**: Ensure they are writable:
  ```bash
  chmod -R ug+w storage bootstrap/cache
  ```
- **SQLite database errors**: Confirm the file path in `.env` matches the actual file and that PHP has write access.
- **Vite fails to start**: Clear caches, remove `node_modules`, reinstall with `npm ci`, or check for conflicting processes on port 5173.
- **Composer scripts fail**: Run `composer install` and `npm install` separately to isolate the failing step, then re-run the script.

---

## 10. Useful Composer Scripts

| Script             | Purpose                                                        |
| ------------------ | -------------------------------------------------------------- |
| `composer run setup` | End-to-end project bootstrap (dependencies, key, migrate, build) |
| `composer run dev`   | Start application, queue worker, logs, and Vite dev server   |
| `composer run test`  | Clear cached config and run the test suite                   |

You can list all scripts with:

```bash
composer run --list
```

---

## 11. Next Steps

- Configure CI/CD to run `composer run test` and `npm run build`
- Review `.env.example` and document any environment-specific variables
- Replace this README section with project-specific business context as new features are added

If you get stuck, open an issue in the repository or reach out to the maintainers.
