---
name: verify
description: Run the full verification suite for this project (PHP tests, static analysis, TypeScript check, ESLint). Use before marking a task done or before committing.
---

Run each of the following commands in sequence from the project root (`src/`). Stop and report immediately if any step fails.

1. **PHP tests**: `php artisan test`
2. **PHP static analysis**: `./vendor/bin/phpstan analyse`
3. **TypeScript type check**: `npx tsc --noEmit`
4. **Frontend lint**: `npm run lint`

Report a summary of which steps passed and which failed. If any step fails, show the relevant output so the user can act on it.
