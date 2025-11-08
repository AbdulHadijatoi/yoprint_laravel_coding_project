# YoPrint Laravel Application ⚙️

> Laravel 12 · PHP 8.3 · Built for quick local onboarding  
> Repository: `https://github.com/AbdulHadijatoi/yoprint_laravel_coding_project.git`

---

## 🚀 Quick Start

1. **Clone**
   ```bash
   git clone https://github.com/AbdulHadijatoi/yoprint_laravel_coding_project.git
   cd yoprint_laravel_coding_project_php83
   ```
2. **Configure**
   ```bash
   cp .env.example .env
   php artisan key:generate
   touch database/database.sqlite
   ```
   > Update `.env` if you prefer MySQL, PostgreSQL, etc.
3. **Install & Migrate**
   ```bash
   composer install
   php artisan migrate
   ```
4. **Serve**
   ```bash
   php artisan serve
   ```
   Open `http://127.0.0.1:8000`

---

## 📦 Requirements

| Tool | Minimum |
| --- | --- |
| PHP | 8.3 + extensions `mbstring`, `pdo_sqlite`, `openssl`, `curl`, `xml`, `fileinfo` |
| Composer | 2.5 |
| Database | SQLite 3 (default) or other driver via `.env` |
| Other | Git |

---

## 🧰 Helpful Commands

| Task | Command |
| --- | --- |
| Reset schema | `php artisan migrate:fresh` |
| Seed database | `php artisan db:seed` |
| Run tests | `composer run test` |
| Run artisan tests | `php artisan test` |
| Format (Pint) | `./vendor/bin/pint` |

---

Need more? Reach out to the maintainers or open an issue in the repository. Happy building! 🎉
