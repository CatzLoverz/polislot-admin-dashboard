---
name: laravel-coding
description: Use this skill to check, format, and enforce Laravel/PHP coding standards and best practices using Laravel Pint and project conventions.
---

# Laravel Coding Standards Enforcer

This skill provides procedures to enforce, test, and auto-format PHP and Laravel code according to the project's coding standards defined in `.agents/rules/coding_standards.md` using Laravel Pint.

## Procedures

### 1. Test Code Formatting with Pint
Run Pint in test mode to identify code style violations without modifying files:
```powershell
./vendor/bin/pint --test
```
Or for a specific file/directory:
```powershell
./vendor/bin/pint path/to/file.php --test
```

### 2. Auto-Fix Code Formatting with Pint
To automatically fix formatting violations across modified or newly created files:
```powershell
./vendor/bin/pint
```
Or for specific files:
```powershell
./vendor/bin/pint path/to/file.php
```

### 3. Coding Standards Verification Checklist

Always ensure the following rules from `.agents/rules/coding_standards.md` are followed:

1. **Imports (Namespace / `use` Statement)**:
   - Always place `use` statements at the top of the file.
   - Do NOT use inline Fully Qualified Class Names (FQCN) in code logic (e.g. use `User::find(1)`, not `\App\Models\User::find(1)`).
   - In PHPDoc annotations (`@param`, `@return`, `@var`, `@throws`), use **short names** without namespace prefixes for all imported classes. Do NOT write `@return \Illuminate\Http\JsonResponse` if `JsonResponse` is already imported at the top.

2. **PHPDoc (Method & Class Documentation)**:
   - Brief 1-2 sentence description explaining the purpose.
   - Clear `@param` with data type and variable name.
   - Explicit `@return` type definition (`JsonResponse`, `View`, `RedirectResponse`, `bool`, `void`, etc.).
   - Optional `@throws` if exceptions are explicitly thrown.

3. **Eloquent Model Conventions**:
   - Explicitly declare `$table`, `$primaryKey`, `$fillable`, and `$casts`.
   - Avoid unrestricted `$guarded = []`.
   - Relation naming: singular camelCase for `hasOne`/`belongsTo`, plural camelCase for `hasMany`/`belongsToMany`.

4. **Architecture & Best Practices**:
   - **Fat Model, Skinny Controller**: Keep controllers lightweight; delegate business logic to Services/Models.
   - **Prevent N+1 Queries**: Use eager loading (`with(...)` or `load(...)`).
   - **Form Requests**: Separate complex validation rules into dedicated `FormRequest` classes.
   - **Laravel Helpers**: Prefer Laravel helpers (`route()`, `asset()`, `config()`, `now()`, `collect()`) over raw PHP functions or hardcoded strings.
   - **Consistent API Response**: Return structured JSON with `status`, `message`, and `data`.
   - **Database Transactions & Error Handling**: Use `DB::beginTransaction()`, `DB::commit()`, `DB::rollBack()` inside `try...catch` blocks for critical database write/update/delete operations.

## Helper Scripts
- Bash: [run.sh](./scripts/run.sh)
- PowerShell: [run.ps1](./scripts/run.ps1)
