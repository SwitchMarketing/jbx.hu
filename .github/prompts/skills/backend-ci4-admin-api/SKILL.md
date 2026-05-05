# Backend CI4 Admin API Skill

Use this skill when changing admin endpoints in app/Controllers/Admin.

## Scope
- CodeIgniter 4 admin API controllers and related models/migrations.
- Endpoints under /admin in app/Config/Routes.php.

## Read Before Editing
- AGENTS.md
- CLAUDE.md
- app/Controllers/Admin/BaseResourceController.php
- app/Config/Routes.php
- app/Config/Database.php

## Non-Negotiables
- Extend or follow BaseResourceController response contract.
- Use setSuccess, setMessage, setData, setTotal, then return setResponse().
- Keep endpoint behavior JSON-first for admin.
- Preserve logical DB group intent (default vs shop).

## Implementation Pattern
1. Add/adjust route in app/Config/Routes.php.
2. Implement controller method with try/catch and clear messages.
3. Update model allowedFields/validation if payload changed.
4. Add migration when schema changes are required.
5. Keep compatibility with existing frontend response handling.

## Error Handling
- Never return blank failure messages.
- Include actionable failure text for validation, DB, and file operations.
- Log hard failures when useful for production diagnosis.

## Validation
- Run php -l on touched PHP files.
- Run php spark migrate for new migrations when requested.
- Spot-check endpoint payload compatibility with corresponding ExtJS controller/store.
