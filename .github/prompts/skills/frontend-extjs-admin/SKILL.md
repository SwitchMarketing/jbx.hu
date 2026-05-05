# Frontend ExtJS Admin Skill

Use this skill when changing the admin SPA under public/admin/app.

## Scope
- ExtJS 7 Modern views/controllers/stores in public/admin/app.
- Admin API integration via public/admin/app/util/API.js.

## Read Before Editing
- AGENTS.md
- CLAUDE.md
- public/admin/Readme.md
- app/Config/Routes.php

## Standard Workflow
1. Find the owning trio for a screen: View, ViewController, Store.
2. Keep API paths relative to /admin via API.call unless multipart upload is required.
3. For file uploads, use native FormData + XMLHttpRequest (avoid Ext.Ajax multipart boundary issues).
4. Preserve existing Hungarian labels/messages.
5. Keep edits minimal; avoid broad formatting in build output.

## Common Patterns
- Grid action tools: edit/delete handlers in ViewController.
- Dialog forms: create with Ext.create, gather values, call API, reload store.
- Response shape expectation: response.success, response.message, response.data.
- For dialogs created outside view tree, prefer dialog.down('[reference=...]') over controller lookup.

## Validation
- Because this repo uses watch mode, do not run production build by default.
- Verify no runtime errors in browser console.
- Confirm store reload paths after save/delete/upload.

## Quick Checklist
- Did I update all linked pieces (View + Controller + Store)?
- Did I preserve Hungarian UX strings?
- Did I avoid forcing production build?
