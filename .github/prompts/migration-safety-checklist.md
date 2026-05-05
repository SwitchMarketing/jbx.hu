# Migration Safety Checklist Prompt

Use this prompt whenever a change includes database migrations.

## Goal
Catch schema risks early and ensure forward and rollback safety.

## Inputs
- Changed migration files in app/Database/Migrations
- Related model/controller changes
- Current DB engine (MySQL)

## Checks
1. Constraint safety
   - Are new NOT NULL columns backfilled or defaulted?
   - Are foreign keys compatible with existing data?
   - Are unique constraints safe against existing duplicates?
2. Nullable and compatibility strategy
   - If old data can violate new constraints, is a transition strategy included?
   - Are legacy columns preserved long enough for compatibility if needed?
3. Data migration correctness
   - Does migration SQL avoid ambiguous column references?
   - Does up() handle duplicates idempotently (INSERT IGNORE or equivalent)?
4. Rollback safety
   - Does down() restore prior schema without silent data corruption?
   - If destructive rollback is unavoidable, is it explicitly documented in migration comments?
5. Runtime impact
   - Are indexes created for new sort/filter columns used by frontend and API?
   - Is locking risk acceptable for production table sizes?

## Validation Commands
- php spark migrate
- php spark migrate:status
- php -l on touched migration and model files

## Output Format
- PASS/FAIL per check
- Exact file and line references for each blocker
- Minimal patch plan to resolve blockers

## Repo-Specific Notes
- Keep logical DB group distinction (default vs shop) from app/Config/Database.php.
- Prefer additive migration steps with safe backfill before enforcing strict constraints.
