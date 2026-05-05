# Admin Release Checklist Prompt

Use this prompt before any admin-related deployment.

## Goal
Verify that admin API + ExtJS admin changes are release-ready with minimal regression risk.

## Inputs
- Changed files from git status/diff.
- Target environment (staging or production).

## Steps
1. Summarize changed surface area:
   - Controllers in app/Controllers/Admin
   - Models/migrations
   - ExtJS files in public/admin/app
2. Confirm schema state:
   - List new migrations and whether they were applied.
3. Validate backend safety:
   - Run php -l on changed PHP files.
   - Check for empty error messages in catch blocks.
4. Validate frontend safety:
   - Ensure watch-mode compatibility (no forced production build requirement).
   - Verify changed dialogs/forms still submit expected payloads.
5. Deployment readiness:
   - If explicitly requested, run sencha app build production.
   - Confirm generated artifacts in public/admin/build/production/JBXAdmin when production build is used.
6. Output:
   - PASS/FAIL checklist with concrete blockers.
   - Exact follow-up actions needed before deploy.

## Constraints
- Keep report concise and actionable.
- Do not include speculative risks without evidence from changed files.
