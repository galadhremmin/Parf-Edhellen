---
name: deploy
description: Deploy Parf Edhellen to production by merging master into the prod branch and compiling assets. Runs from the repo root (one level above src/).
disable-model-invocation: true
---

Run these two scripts from the repo root (`../` relative to `src/`):

**Step 1 — Merge master into prod branch:**
```bash
cd .. && ./merge-prod.sh
```
This checks out master, pushes it, then merges into `parf-edhellen-prod-v2` and pushes that branch.

**Step 2 — Compile and verify:**
```bash
./compile-prod.sh
```
This runs phpstan + PHP tests on master, switches to the prod branch, runs tests again, then builds production assets (`npm run production`), and returns to master.

Both scripts must complete without errors for a successful deploy.
