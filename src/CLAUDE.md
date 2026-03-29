# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Parf Edhellen** is the source for [elfdict.com](https://www.elfdict.com), a free dictionary for Tolkien's languages. It is a full-stack Laravel 12 + React 18 application.

## Commands

### Backend (PHP)
```bash
php artisan serve            # Run local dev server
php artisan test             # Run all PHP tests (PHPUnit)
php artisan test --filter=TestName  # Run a single test class or method
./vendor/bin/phpunit         # Direct PHPUnit invocation
./vendor/bin/phpstan analyse # Static analysis (level 1)
./vendor/bin/pint            # Laravel code formatter
```

### Frontend (Node)
```bash
npm run watch                # Watch mode for development (user runs this automatically)
npm run dev                  # Single development build
npm run test                 # Run Jest tests
npm run lint                 # ESLint (TS/TSX only)
npm run production           # Lint + test + production build
```

**Type checking** is NOT part of the webpack build (swc-loader skips it). Run separately:
```bash
npx tsc --noEmit
```

### Running a single Jest test
```bash
npx jest path/to/File._spec.tsx
```

## Architecture

The app is split into two main layers — see their respective CLAUDE.md files for detail:

- **`app/`** — Laravel backend: Repository → Adapter → Controller, DI via constructor injection, event-driven with Jobs and Subscribers. See `app/CLAUDE.md`.
- **`resources/assets/ts/`** — React 18 frontend: 19 independent SPAs, per-app Redux, DI container, fetch-based API connectors. See `resources/assets/ts/CLAUDE.md`.

**Routes** are split into modular files under `routes/web-routes/` and `routes/api-routes/`. API endpoints live at `/api/v3/`. See `routes/constants.php` for the `API_PATH` constant and regex patterns.

**Build output:** `public/v{ED_VERSION}/` — version controlled by `ED_VERSION` in `.env`. Changing this value shifts all public asset paths; the `.env` value and the compiled assets must stay in sync.

## Blade vs React

Blade templates (`resources/views/`) own page structure, layout, and server-rendered HTML. React apps are mounted into Blade pages via `data-inject-module` attributes — Blade provides the shell, React owns any interactive UI within it.

- **Use Blade** for: page layout, navigation, static content, passing server-side data as props to React via `data-` attributes.
- **Use React** for: anything interactive, stateful, or that needs client-side updates.
- Never put business logic in Blade views. Data should arrive pre-shaped from the controller.

## Coding Style

### TypeScript/React
- 2 spaces indentation
- Functional components preferred; interfaces prefixed with `I` (`IProps`, `ILexicalEntry`)
- File naming: `Component.tsx`, `Component._types.ts`, `Component._spec.tsx`, `Component.scss`
- Import order: external deps → internal utils → types (with `type` keyword) → components → styles
- Path alias: `@root/*` → `resources/assets/ts/*`

### SCSS
- 2 spaces indentation; kebab-case class names; max 3–4 nesting levels
- Use `var(--ed-*)` for custom color tokens; `var(--bs-*)` for Bootstrap tokens

### SQL
- UPPER CASE keywords; snake_case table/column names; 4 spaces indentation

## Key Domain Concepts

- **Gloss** — a dictionary entry (word with translation, part of speech, notes)
- **Sentence** — an example sentence in a Tolkien language with annotated inflections
- **LexicalEntry** — the searchable entity linking glosses to keywords
- **Keyword** — the normalised search form of a word
- **AuditTrail** — activity log; entries with `is_admin = 1` are visible to admins only
- **`ed-usermode` cookie** — admins can set this to `incognito` to hide their activity
