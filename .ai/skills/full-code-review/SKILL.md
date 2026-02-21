---
name: full-code-review
description: >-
  Runs both backend and frontend code reviews on changed files.
  Activates when the user asks for a full review, code review, cleanup,
  or tidy up without specifying backend or frontend.
user_invocable: true
---

# Full Code Review

Run a complete code review across both backend and frontend. Identify the changed files (`git diff --name-only` against the main branch), then:

1. Invoke the `backend-code-review` skill for any changed files under `app/`, `routes/`, `database/`, and `tests/`.
2. Invoke the `frontend-code-review` skill for any changed files under `resources/js/`.

If the user specifies particular files or directories, scope each review accordingly.
