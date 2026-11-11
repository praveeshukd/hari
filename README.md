# Molecules BioLabs (`hari`)

Laravel 11 QC / analysis sheet app for Molecules BioLabs.  
Live demo: [https://hari-qs7r.onrender.com](https://hari-qs7r.onrender.com)  
GitHub: [https://github.com/praveeshukd/hari](https://github.com/praveeshukd/hari)

---

## Overall overview

| Item | Detail |
| --- | --- |
| Stack | Laravel 11, PHP 8.2+ (Docker uses PHP 8.4) |
| Architecture | nWidart `Analysis` module + shared repos in `app/Repositories` + DDD Actions/Services/DTOs |
| Database | MySQL locally; SQLite fallback; Postgres supported on Render |
| Auth / roles | Session auth · `admin` / `analyst` / `viewer` |
| Main feature | Analysis (QC) sheet: CRUD, soft delete, bulk actions, filters, Excel/PDF, queued import |
| Security | Encrypted sensitive fields, audit trail, fail email alerts |
| UI | CaterHub-inspired admin shell (teal brand), Yajra DataTables |
| Hosting | Render Docker web service (+ optional Postgres + queue worker) |

---

## Module architecture (`nwidart/laravel-modules`)

```text
Modules/Analysis/
├── app/ Actions, Services, DTOs, Repositories, Models, Http, …
├── routes/web.php
└── config/analysis.php
app/
├── Repositories/                # RepositoryInterface + EloquentRepository
├── Http/ Controllers (Auth, Users, Landing)
└── … UI shell / policies for users
```

**Flow:** Form Request → Policy → Controller → Action / Service → Repository → Eloquent model (+ audit / fail notify)

---

## Roles

| Ability | Admin | Analyst | Viewer |
| --- | --- | --- | --- |
| View dashboard / analyses | yes | yes | yes |
| Create / update / quick-update / import | yes | yes | no |
| Bulk update result | yes | yes | no |
| Delete / restore / bulk delete | yes | no | no |
| Manage users / roles | yes | no | no |
| PDF settings | yes | yes | no |

First registered user becomes `admin`; later registrations default to `analyst`.

---

## Routes (main)

### Public
| Method | URL | Name |
| --- | --- | --- |
| GET | `/` | `landing` |
| GET/POST | `/login` | `login` |
| GET/POST | `/register` | `register` |
| GET | `/up` | health check |

### Auth required
| Method | URL | Name |
| --- | --- | --- |
| GET | `/dashboard?from=&to=` | `dashboard` |
| POST | `/logout` | `logout` |
| resource | `/analysis` | CRUD (`analysis.*`) |
| POST | `/analysis/{id}/restore` | `analysis.restore` |
| POST | `/analysis/bulk-delete` | `analysis.bulk-delete` |
| POST | `/analysis/bulk-result` | `analysis.bulk-result` |
| GET | `/analysis/bulk-export` | `analysis.bulk-export` |
| PATCH | `/analysis/{id}/quick-update` | `analysis.quick-update` |
| POST | `/analysis-import` | Queued import (modal on Analysis sheet) |
| GET | `/users` | Admin role management |

Trash view: `/analysis?trash=1` (admin).

---

## Local setup

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan serve
```

Queue worker (required for imports / queued mail):

```bash
php artisan queue:work
```

After deploying encryption for existing rows:

```bash
php artisan analysis:encrypt-existing
```

Daily fail summary (also scheduled at 07:00):

```bash
php artisan analysis:daily-fail-summary
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000).

### Tests

Feature tests use MySQL database `qc_testing` (see `tests/bootstrap.php`).

```bash
./vendor/bin/phpunit
```

---

## Encryption notes

Encrypted casts: `sample_name`, `batch_no`, `remarks`, `sample_submitted_by`, `done_by`.  
Exact filters use HMAC hash columns (`sample_name_hash`, `batch_no_hash`, `done_by_hash`).  
Free-text DataTable search covers non-encrypted columns only (`analysis`, `instrument`, `column_name`, `result`, `specification`).

---

## Hosting notes (Render)

- Service: `hari` · Docker · Free
- URL: `https://hari-qs7r.onrender.com`
- Env: `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY=...`
- Startup: `docker/start.sh` waits for the configured DB, then runs `migrate --force` (pending migrations only — does **not** clear existing data)
- Image build does **not** create or reset a database
- Dockerfile includes `pdo_pgsql` for Render Postgres
- Set `DB_CONNECTION=pgsql` (or `mysql`) + host/credentials to use an already-provisioned database
- SQLite file is only created at runtime if `DB_CONNECTION=sqlite` and the file is missing
- Free Render Postgres expires; use paid Postgres or an external DB for lasting data
- Add a **Background Worker** with the same image running `php artisan queue:work`
- Optional paid disk for `storage/app` (import files, logos)

---

## Important files for changes

| Task | Start here |
| --- | --- |
| Analysis business rules | `app/Domain/Analysis/Actions` / `Services` |
| Persistence / queries | `app/Domain/Analysis/Repositories` |
| Validation | `app/Http/Requests/Analysis` |
| HTTP entry | `app/Http/Controllers/AnalysisController.php` |
| DataTable | `app/DataTables/AnalysisDataTable.php` |
| Dashboard metrics | `app/Domain/Analysis/Services/DashboardMetricsService.php` |
| Admin UI | `resources/views/dashboard.blade.php`, `analysis/index.blade.php` |
| Styles | `public/css/style.css`, `public/css/datatable.css` |

---

## Security notes

- Never commit `.env`
- Keep `APP_DEBUG=false` in production
- Use a strong unique `APP_KEY` on the server (required for encryption + hashes)
- Authorization is enforced via `AnalysisPolicy` / `UserPolicy` + Form Requests + `role:admin` middleware
