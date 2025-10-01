---
description: Check upgrade status and show next phase
scope: project
---

Check the current Laravel and PHP versions and determine the next upgrade phase.

1. Check current Laravel version from composer.json
2. Check current PHP version from Dockerfile
3. Run test suite to verify current state: `./test.sh tests/Feature/`
4. Determine current phase based on versions
5. Show next recommended upgrade phase from LARAVEL_UPGRADE_PLAN.md
6. Show summary of what the next phase entails
7. Show available slash command for the next phase (e.g., /upgrade-phase-2)

Be concise and clear about the current state and next steps.

Current available phase commands:
- /upgrade-phase-2 (Laravel 5.4 → 5.5 LTS, PHP 7.0+)
- /upgrade-phase-3 (Laravel 5.5 → 5.6, PHP 7.1+)
- /upgrade-phase-4 (Laravel 5.6 → 5.7)
- /upgrade-phase-5 (Laravel 5.7 → 5.8)
- /upgrade-phase-6 (Laravel 5.8 → 6.x LTS + MySQL 8)

Show the user they can run the appropriate slash command to get detailed instructions for the next phase.
