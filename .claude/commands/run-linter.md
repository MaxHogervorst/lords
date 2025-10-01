---
description: Run the code linter
---

Run the code linter for this Laravel application.

Try the following in order:
1. `vendor/bin/pint` (Laravel Pint)
2. `vendor/bin/phpcs` (PHP CodeSniffer)
3. `vendor/bin/php-cs-fixer fix --dry-run` (PHP CS Fixer)

If none are installed, recommend installing Laravel Pint:
```bash
composer require laravel/pint --dev
```

Show the linting results and any issues that need to be fixed.
