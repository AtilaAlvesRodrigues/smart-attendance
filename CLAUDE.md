# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Full setup from scratch
composer install && npm install && npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve

# Development (starts server, queue, log tailing, and Vite dev server concurrently)
composer dev

# Build frontend assets
npm run build       # production
npm run dev         # watch mode

# Testing
composer test                          # clears config then runs phpunit
php artisan test                       # run all tests
php artisan test --filter=TestName     # run a single test

# Database
php artisan migrate --seed             # apply migrations and seed data
php artisan migrate:fresh --seed       # reset and reseed
```

## Architecture

**Stack**: PHP 8.2+, Laravel 12, PostgreSQL, Blade templates, TailwindCSS 4, Vite 7.

### Multi-Guard Authentication

Three independent authentication guards — each with its own login/logout flow and session:

| Guard | Model | Route prefix |
|-------|-------|-------------|
| `auth:alunos` | `AlunoModel` | `/aluno/*` |
| `auth:professores` | `ProfessorModel` | `/professor/*` |
| `auth:masters` | `UsuarioMaster` | `/master/*` |

The `CheckRole` middleware (`app/Http/Middleware/CheckRole.php`) enforces role separation. `DashboardController` reads the authenticated guard and redirects to the appropriate dashboard.

### PII Encryption

All personally identifiable fields (nome, email, cpf, ra) on `AlunoModel` and `ProfessorModel` are encrypted at rest with AES-256 via Laravel's `Crypt` facade. To allow searching on encrypted fields, `HasBlindIndex` trait (`app/Traits/HasBlindIndex.php`) generates deterministic SHA-256 hashes stored in `*_hash` columns. Always use the blind index columns when querying by name, email, CPF, or RA.

### Attendance Flow

1. Professor generates a QR code for a class session → stored in Laravel cache with key `aula_materia_{materia_id}_{date}` (30-minute TTL).
2. Student scans the QR → `PresencaController` validates the cache key and creates a `Presenca` record.
3. Rate limiting: 5 check-in attempts per minute per user.

### Database Relations

- `aluno_materia` — pivot table with grade columns (`prova1`, `trabalho1`, `trabalho2`, `prova2`) linking students to subjects.
- `materia_professor` — pivot linking professors to subjects.
- `Presenca` — attendance records (`materia_id`, `aluno_id`, `codigo_aula`, timestamps).

### Frontend

Themes are compiled per role: `theme.css`, `theme_professor.css`, `theme_aluno.css`, `theme_master.css` in `public/css/`. Vite compiles from `resources/css/` and `resources/js/`. TailwindCSS is configured via `@tailwindcss/vite`.

## UI / Design Standards

The `.cursorrules` file contains the authoritative UI spec. Key rules:

- **Glassmorphism design system**: glass cards (`glass-card`), animated blobs, backdrop blur.
- **CSS variables**: color palette via `--pal-*` variables; always use them, never hardcode hex values.
- **Typography**: use `.pal-title`, `.pal-eyebrow`, `.pal-text`, `.pal-always-white` classes.
- **Inputs**: `.login-input`, `.login-label`, `.login-error` classes for form fields.
- **Buttons**: colors are role-specific — QR generation = dark, confirm = green, cancel = outline.
- **Animation**: stagger card entrances with `.pal-card-delay-1`, `.pal-card-delay-2`, `.pal-card-delay-3`.
- **Dark/Light mode**: all styles must work in both modes via CSS variables.

Before making any UI changes, consult `.cursorrules` — it documents known bugs, component patterns, and visual testing checklists.

## Test Credentials (seeded data)

| Role | Email | Password |
|------|-------|----------|
| Master | `master@admin.com` | `senha123` |
| Professor | `professor@teste.com` | `senha123` |
| Aluno | `aluno.teste@site.com` | `senha123` |

## Environment

The app requires PostgreSQL (`DB_CONNECTION=pgsql`, port 5432, database `smart_attendance`). Copy `.env.example` to `.env` and configure `DB_PASSWORD` and `APP_KEY` before running.
