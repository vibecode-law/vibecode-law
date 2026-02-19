---
name: backend-code-review
description: >-
  Reviews backend PHP/Laravel code for quality, structure, and consistency.
  Activates when the user asks for a backend code review, cleanup, tidy up,
  or refactor of controllers, models, resources, or other backend code.
user_invocable: true
---

# Backend Code Review

Review backend PHP/Laravel code for quality issues and fix them. Focus on the specific files or directories the user indicates; if none are specified, review recently changed files (`git diff --name-only` against the main branch, filtered to `app/`, `routes/`, `database/`, and `tests/`).

## Review Checklist

Work through each check below. For every issue found, fix it directly — do not just report it.

### 1. Eloquent Over Raw Queries

- Replace `DB::table()` calls with Eloquent model queries (e.g., `CourseUser::query()` instead of `DB::table('course_user')`).
- Use `ModelName::` as the starting point, never `DB::`.
- Use Eloquent relationships and scopes where they simplify queries.

### 2. Facades Over Helpers

- Replace helper function calls with their Facade equivalents (e.g., `Auth::user()` instead of `auth()->user()`, `Redirect::route()` instead of `redirect()->route()`).
- Import Facades explicitly at the top of the file.

### 3. Controller Method Extraction

- Controller actions (especially `__invoke`) should be lean. If a method exceeds ~20 lines of logic, extract focused private methods.
- Each extracted method should have a single responsibility (e.g., `getCompletedLessonIds()`, `getSiblingLessons()`, `getNextLessonSlug()`).
- Add PHPDoc blocks with `@param` and `@return` type annotations (including array shapes) on every extracted method.

### 4. Type Annotations

- Use explicit type hints on all method parameters and return types.
- Add PHPDoc array shape annotations where PHP's type system can't express the shape (e.g., `@return array<int, array{progressPercentage: int|float}>`).

### 5. Minimal Data in Responses

- Only send data the frontend actually needs. Remove props that the frontend can derive from other data (e.g., don't send `isEnrolled` if the frontend can infer it from `completedLessonIds`, don't send `completedLessonsCount` if `completedLessonIds` is already sent).
- Remove raw markdown fields from responses when rendered HTML versions are available (e.g., send `description_html` not `description`).
- Use `->only()` on Spatie Data resources to explicitly limit fields.
- Use `->include()` to only include lazy properties that are needed.
- Use `->select()` on Eloquent queries to limit columns when returning collections.

### 6. Eager Loading and Query Optimisation

- Remove unnecessary eager loads (e.g., don't `->with('tags')` if tags aren't being sent to the frontend).
- Replace N+1 query patterns (loops containing queries) with batch queries (e.g., a single grouped query with `pluck` instead of per-item queries inside a `foreach`).
- Use `->withCount()` instead of loading full relationships when only the count is needed.

### 7. Named Parameters

- Use named parameters when calling methods, especially for long or ambiguous argument lists.
- For long method calls, break each named parameter onto its own line.

### 8. Boolean Checks

- Be explicit when checking booleans: `$value === true` instead of `$value`, `$value === false` instead of `! $value`.
- Use `$var !== null` / `$var === null` instead of `isset()` or empty checks where the variable is known to exist.

### 9. Test Quality

After fixing backend code, review and update the corresponding tests:

- **Test values, not just presence** — replace `->has('field')` with `->where('field', $expected)` assertions that verify the actual value.
- **Test nested resources fully** — assert on all fields of nested resources (e.g., user, lessons) rather than just checking they exist.
- **Use factory states** — prefer `LessonUser::factory()->completed()` over manual attribute overrides where states exist.
- **Import Pest helpers** — use `use function Pest\Laravel\actingAs;` and `use function Pest\Laravel\get;` rather than `$this->actingAs()`.
- **Avoid `->etc()`** — tests should be exhaustive about what props are returned; don't use `->etc()` to skip unknown props.
- **Test `->missing()` props** — verify that removed or excluded data is not leaking to the frontend.
- **Group related tests** — use `describe()` blocks to group related scenarios (e.g., `describe('lesson progress', ...)`).
- **Test both paths** — cover authenticated and unauthenticated users, empty and populated states, edge cases (first/last item, null relations).
- **Don't use `assertDatabaseHas`** — for controller tests that affect the database, test the response, load the model(s), and test using Pest expectations.

## After Review

Run the standard backend cleanup:

```bash
vendor/bin/pint --dirty
composer types
```

If Spatie Data resources were changed:

```bash
php artisan typescript:transform
```

If routes or controllers were changed:

```bash
php artisan wayfinder:generate --with-form
```

Run affected tests:

```bash
php artisan test --compact --filter=<relevant-test>
```

Fix any errors these surface before finishing.
