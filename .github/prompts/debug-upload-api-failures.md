# Debug Upload and Admin API Failures Prompt

Use this prompt when admin API calls fail, especially uploads or empty error responses.

## Goal
Identify root cause quickly with reproducible evidence and targeted fixes.

## Triage Flow
1. Reproduce
   - Capture endpoint, method, payload fields, and response body.
2. Inspect server logs
   - Check writable/logs for matching timestamp and stack trace.
3. Check request contract
   - Verify route exists in app/Config/Routes.php.
   - Verify controller reads expected input source:
     - getPost()/getFile() for multipart
     - getRawInput() for JSON/urlencoded update payloads
4. Check DB/schema compatibility
   - Validate required columns, nullability, and foreign keys against inserted data.
5. Check frontend transport layer
   - For multipart uploads, prefer native FormData + XMLHttpRequest.
   - Avoid forcing Content-Type boundaries manually in ExtJS multipart requests.
6. Validate fix
   - Re-run failing action
   - Confirm non-empty failure messages on errors
   - Confirm success path refreshes UI store/grid

## Standard Commands
- php -l <changed_php_file>
- php spark migrate:status
- git diff -- <changed_files>

## Expected Output
- Root cause summary
- Minimal code change list
- Verification steps and observed result

## Repo-Specific Pitfalls
- BaseResourceController envelope expects success/message/data fields.
- Empty exception messages should be normalized to explicit fallback text.
- MySQL NOT NULL legacy columns can break modernized insert paths if not migrated.
