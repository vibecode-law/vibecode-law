---
name: frontend-code-review
description: >-
  Reviews frontend React/TypeScript code for quality, structure, and consistency.
  Activates when the user asks for a code review, frontend review, cleanup, tidy up,
  or refactor of React pages or components.
user_invocable: true
---

# Frontend Code Review

Review frontend React/TypeScript code for quality issues and fix them. Focus on the specific files or directories the user indicates; if none are specified, review recently changed files (`git diff --name-only` against the main branch, filtered to `resources/js/`).

## Review Checklist

Work through each check below. For every issue found, fix it directly — do not just report it.

### 1. Component Extraction

- Pages should be lean orchestrators. If a page file exceeds ~150 lines, look for self-contained UI sections that can be extracted.
- Extract reusable sections into dedicated component files under `resources/js/components/<domain>/`.
- Each extracted component should have a clear, typed props interface.
- Use named exports (`export function ComponentName`) not default exports for components (pages are the exception — they use default exports).

### 2. Deduplication

- Look for utility functions or constants duplicated across files (e.g. formatting helpers, colour maps, icon maps).
- Extract shared logic into `resources/js/lib/<domain>-utils.ts`.
- Extract shared interfaces/types to either the component that owns them (exported) or to a shared types file.

### 3. TypeScript Quality

- Replace `any` types with proper types from `resources/js/types/generated.d.ts` (e.g. `App.Http.Resources.*`).
- Remove `// eslint-disable` comments that suppress type-safety warnings — fix the underlying type issue instead.
- Ensure all component props have explicit interfaces (not inline object types).

### 4. Mock / Placeholder Data

- Remove hardcoded mock data arrays from page components. Pages should rely on props from the backend.
- Default missing optional props to empty arrays/objects, not mock data (e.g. `propCourses ?? []`).

### 5. Route References

- Replace hardcoded URL strings with Wayfinder controller/route functions (e.g. `LearnIndexController.url()` instead of `'/learn'`).
- Import route functions from `@/actions/` or `@/routes/`.

### 6. Comment Hygiene

- Remove redundant section-divider comments that just restate what the JSX below already says (e.g. `{/* Title and tagline */}` above an `<h1>`).
- Keep comments only where the logic is non-obvious.

### 7. Import Cleanup

- After extractions, verify unused imports are removed.
- Ensure imports are ordered: external packages, then `@/` aliases, grouped logically.

### 8. Existing Component Reuse

- Check `resources/js/components/ui/` for existing primitives (Badge, Avatar, Button, etc.) before writing custom markup.
- Check sibling component directories for existing components that already do what's needed.

## After Review

Run the standard frontend cleanup:

```bash
npm run format
npm run lint
npm run types
```

Fix any errors these surface before finishing.
