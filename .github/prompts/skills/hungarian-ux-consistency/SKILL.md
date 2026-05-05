# Hungarian UX Consistency Skill

Use this skill for user-facing strings and route naming in public and admin features.

## Scope
- UI labels, button texts, dialogs, toasts, validation messages
- Route segments and slug conventions for public pages
- Admin labels/messages in ExtJS views/controllers

## Rules
1. Match existing Hungarian terminology in nearby files.
2. Keep tone consistent: concise, practical, non-formal marketing style.
3. Reuse established terms instead of introducing synonyms.
4. Preserve existing diacritics where already used.
5. Keep error messages actionable and specific.

## Route and Slug Conventions
- Follow existing Hungarian route patterns in app/Config/Routes.php.
- Do not silently rename existing public routes.
- If introducing new paths, align style with current URL vocabulary.

## Review Checklist
- Are all new user-visible strings Hungarian?
- Are labels consistent between create/edit dialogs?
- Do success/error toasts match existing phrasing pattern?
- Are admin and public terms aligned for the same concept?

## Avoid
- Mixed-language UI strings in the same screen
- Overly long helper text where existing style is short labels + concise messages
- Unnecessary capitalization or punctuation changes in existing UI text
