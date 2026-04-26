---
name: lint
description: Run TypeScript type checking and ESLint on the frontend codebase. Lighter than /verify — JS/TS only, no PHP tests or static analysis.
---

Run both checks from the project root (`src/`):

1. **TypeScript type check**: `npx tsc --noEmit`
2. **ESLint**: `npm run lint`

Report which checks passed and which failed. Show relevant output for any failures.
