# Contributing to vibecode.law

Thank you for your interest in contributing to vibecode.law! This document provides guidelines and instructions for contributing to the project.

Contributions are welcome and will be fully credited.

## Ways to Contribute

- **Report bugs** — Found something broken? Open an issue with steps to reproduce.
- **Suggest features** — Have an idea? Open an issue.
- **Fix issues or contribute features** — Submit a pull request (see guide below).
- **Review pull requests** — Provide feedback on open pull requests.

## Submitting a Pull Request

> [!TIP]
> If you are unsure about this process, why not point your AI coding tool at this file and ask it to guide you through?

### Steps

1. Fork the `vibecode-law/vibecode-law` repository, or sync it if you've already forked it.
2. Clone your fork locally or `git pull` if you've synced an existing fork.
3. Create a new branch for your changes
```bash
git checkout -b feat/your-feature-name  # Feature
```
4. Make your changes.
5. Re-generate definitions and run code quality checks & tests:
```bash
composer definitions    # Auto-generate IDE-helpers and frontend types.
composer format-test    # Backend formating, linting and tests
npm run format-lint     # Frontend formatting and linting
```
6. Fix any flagged issues and re-run.
7. Commit your changes and push them to Github.
8. Create a pull request in `vibecode-law/vibecode-law`.

For a more detailed guide, please see our [Pull Requests for Beginners Guide](../docs/PULL_REQUEST_GUIDE.md).

### Tests

Please ensure that:
- You update existing tests or add new tests to reflect new or changing functional.
- Those tests cover both the 'happy' and 'unhappy' path.
- You follow existing conventions within the codebase regarding how tests are written and organised.

### Commit Messages

We use [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/) for commit messages. This makes the commit history easier to read and enables automated tooling.

Common prefixes:
- `feat:` — A new feature
- `fix:` — A bug fix
- `docs:` — Documentation changes
- `refactor:` — Code changes that neither fix a bug nor add a feature
- `test:` — Adding or updating tests
- `chore:` — Maintenance tasks (dependencies, config, etc.)

Example: `feat: add newsletter subscription form`

### General tips
- Use descriptive names for your branch and pull request title.
- If you are contributing more than one feature that "stand in their own right", please make a separate PR for each feature.
- Send coherent history. Make sure each individual commit in your pull request is meaningful. If you had to make multiple intermediate commits while developing, please [squash them](https://git-scm.com/book/en/v2/Git-Tools-Rewriting-History#Changing-Multiple-Commit-Messages) before submitting.

### A note for AI coding agents

This repository uses Laravel Boost to provide application specific context to you. 

If not already setup, take a look at the [README](../README.md) for instructions on how to do so.