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

PII fields (email, cpf, ra) on `AlunoModel` and `ProfessorModel` are encrypted at rest with AES-256 via the `encrypted` cast; `nome` is plain text by design. `UsuarioMaster` encrypts both `nome` and `email`. To allow searching, the `HasBlindIndex` trait (`app/Traits/HasBlindIndex.php`) fills deterministic SHA-256 hashes (peppered with `APP_KEY`) in `*_search` columns. Never query encrypted columns directly (`where('email', ...)` never matches) — use `where('email_search', Model::generateBlindIndex($value))`.

### Attendance Flow

1. Professor generates a QR code for a class session → stored in Laravel cache with key `aula_materia_{materia_id}_{date}` (2-hour TTL).
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

## Documentation

Detailed docs live in `docs/` (Portuguese): `manual-do-usuario.md`, `arquitetura.md`, `banco-de-dados.md`, `regras-academicas.md`, `seguranca.md`, `instalacao.md`, `deploy.md`, `testes-e-ci.md`, `design-e-acessibilidade.md`. When behavior changes, update the matching doc and add an entry under "Não lançado" in `CHANGELOG.md`. Contribution conventions (branch prefixes, Conventional Commits in Portuguese, PR checklist) are in `CONTRIBUTING.md`.

Attendance/grade math always goes through `App\Support\SituacaoAcademica` (used by the student dashboard, the professor grades page and its JS, and the Master attendance center).

## Deploy

Production runs on Vercel (`vercel.json`, `api/index.php`, runtime `vercel-php`) with PostgreSQL on Supabase in a dedicated `laravel` schema (`DB_SEARCH_PATH=laravel`). The function filesystem is read-only except `/tmp`, so cache/compiled paths are redirected there via env vars, and sessions/cache use the `database` driver. See the Deploy section of `README.md`.

## Environment

The app requires PostgreSQL (`DB_CONNECTION=pgsql`, port 5432, database `smart_attendance`). Copy `.env.example` to `.env` and configure `DB_PASSWORD` and `APP_KEY` before running.
