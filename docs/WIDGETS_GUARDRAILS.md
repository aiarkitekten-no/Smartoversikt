# Widgets Guardrails

This repository adds lightweight guardrails to prevent accidental breakage of widget templates and regressions in Quicklinks.

What it checks:
- Balanced `<div>`/`</div>` tags in each `resources/views/widgets/*.blade.php`
- Presence of core markers in `tools-quicklinks.blade.php` (ensures widget wasn’t gutted)

How to run locally:
```bash
php scripts/check-widgets.php
```

Enable as pre-commit hook:
```bash
git config core.hooksPath .githooks
chmod +x .githooks/pre-commit
```

CI enforcement:
- GitHub Actions workflow `.github/workflows/widgets-guardrails.yml` runs the checker on every push/PR to `main`.
