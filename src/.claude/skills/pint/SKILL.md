---
name: pint
description: Format all PHP files in the project with Laravel Pint in one pass. The format-on-save hook handles individual files automatically — use this skill when you want to reformat the entire codebase at once (e.g. after a merge or bulk change).
---

Run Laravel Pint across the whole project:

```bash
./vendor/bin/pint
```

If `$ARGUMENTS` is provided, scope it to that path:

```bash
./vendor/bin/pint $ARGUMENTS
```

Report which files were reformatted.
