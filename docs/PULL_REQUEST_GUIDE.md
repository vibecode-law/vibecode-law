# Pull Requests for Beginners Guide

This guide walks you through making your first contribution to vibecode.law, assuming no prior knowledge of Git or GitHub.

> [!TIP]
> If you are unsure about this process, why not point your AI coding tool at the [Contribution Guidelines](../.github/CONTRIBUTING.md) and ask it to guide you through?

## Key Concepts

Before we begin, let's understand the key concepts you'll encounter.

### Git

Git is a version control system that tracks changes to files. Think of it like a detailed history of every change ever made to a project. It allows multiple people to work on the same codebase without overwriting each other's work.

### Repository

A repository (or "repo") is a project's folder that Git tracks. It contains all the project files plus the entire history of changes. The vibecode.law repository lives on GitHub at `vibecode-law/vibecode-law`.

### Fork

A fork is your own copy of someone else's repository. When you fork a project, you get an independent copy under your GitHub account that you can modify freely. Your changes in your fork don't affect the original repository until you submit them for review.

### Clone

Cloning downloads a repository from GitHub to your computer so you can work on it locally. When you clone your fork, you get all the files and the complete history on your machine.

### Branch

A branch is a separate line of development. Think of it like a parallel universe for your code. The main branch (usually called `main`) contains the stable, released code. When you create a new branch, you can make changes without affecting the main branch. This keeps your work isolated until it's ready.

### Commit

A commit is a snapshot of your changes at a specific point in time. Each commit has a message describing what changed. Commits create a trail of what was changed, when, and by whom.

### Push

Pushing uploads the commits on your device to the corresponding location on GitHub.

### Pull Request

A pull request (or "PR") is a request to merge your changes into another repository (in this case, from the branch on your fork to the `main` branch on `vibecode-law/vibecode-law`).

When you open a pull request, you're asking the project maintainers to review your changes and, if they approve, merge them into the main project.

This is how contributions work on GitHub.

## Prerequisites

Before starting, you'll need:

1. **A GitHub account** — Sign up free at [github.com](https://github.com)
2. **A development environment** - See our [Codespaces Setup Guide](./CODESPACES_SETUP.md) for the easiest way to get started.

## Step-by-Step Guide

### 1. Fork the Repository

1. Go to the [vibecode-law/vibecode-law](https://github.com/vibecode-law/vibecode-law) repository on GitHub.
2. Click the **Fork** button in the top-right corner.
3. GitHub will create a copy under your account (e.g., `your-username/vibecode-law`).

Or, if you've already forked the repository before, click **Sync fork** on your fork's page to get the latest changes from the original repository.

_If you have followed the Codespaces Setup Guide above, you can now skip to step 4._

### 2. Clone Your Fork

Open the terminal in your code editor and run:

```bash
git clone https://github.com/YOUR-USERNAME/vibecode-law.git
```

Replace `YOUR-USERNAME` with your GitHub username.

This downloads your fork to a folder called `vibecode-law` on your computer.

Navigate into the newly created folder.

### 3. Set Up the Upstream Remote

Run the following in the terminal. This connects your local repository to the original project so you can pull in updates:

```bash
git remote add upstream https://github.com/vibecode-law/vibecode-law.git
```

You can verify your remotes with:

```bash
git remote -v
```

You should see:

- `origin` pointing to your fork
- `upstream` pointing to the original repository

### 4. Create a Branch

_Note, if you don't like using the terminal, all of the below steps can be done from the VSCode "Source Control" user interface, which looks like three nodes connected by two lines._

Always create a new branch for your changes. To avoid yourself a headache later, never work directly on `main`.

First, make sure you have the latest changes:

```bash
git checkout main
git pull upstream main
```

Then create and switch to a new branch:

```bash
git checkout -b feat/your-feature-name
```

Use a descriptive branch name:

- `feat/add-newsletter-form` for a new feature
- `fix/broken-login-button` for a bug fix
- `docs/update-readme` for documentation changes

### 5. Make Your Changes

Make your changes manually or with your AI assistant of choice. Follow the coding standards in the [CONTRIBUTING.md](./CONTRIBUTING.md).

### 6. Check Your Changes

Before committing, review what you've changed via the Source Control screen in your editor or by running the follow in the terminal:

```bash
git status
```

This shows which files have been modified, added, or deleted.

To see the actual changes in a file:

```bash
git diff path/to/file
```

### 7. Stage Your Changes

Staging tells Git which changes you want to include in your next commit.

You can stage changes from the Source Control screen in your editor (typically by clicking a "+" icon next to the file), or to stage specific files via the terminal:

```bash
git add path/to/file1 path/to/file2
```

To stage all changed files via the terminal:

```bash
git add .
```

### 8. Commit Your Changes

Create a commit with a descriptive message - either via the Source Control screen in your editor - or via the terminal by running:

```bash
git commit -m "feat: add newsletter subscription form"
```

We use [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/). Start your message with a prefix like:

- `feat:` for new features
- `fix:` for bug fixes
- `docs:` for documentation changes

See [CONTRIBUTING.md](./CONTRIBUTING.md#commit-messages) for more examples.

### 9. Push Your Branch

Push your branch to your fork on GitHub via the Source Control screen, or via terminal by running:

```bash
git push origin feat/your-feature-name
```

### 10. Create a Pull Request

1. Go to the [vibecode-law/vibecode-law](https://github.com/vibecode-law/vibecode-law) repository.
2. You should see a banner saying your branch was recently pushed. Click **Compare & pull request**.
    - If you don't see the banner, click **Pull requests** > **New pull request**, then click **compare across forks** and select your fork and branch.
3. Fill in the pull request form:
    - **Title**: A short description of your changes
    - **Description**: Explain what you changed and why. Include any relevant issue numbers (e.g., "Fixes #123").
4. Click **Create pull request**.

### 11. Wait for Review

A maintainer will review your pull request. They may:

- **Approve it** — Your changes will be merged.
- **Request changes** — They'll leave comments explaining what needs to be updated.

If changes are requested, make them locally, commit, and push again. Your pull request will update automatically.

## Common Issues and Solutions

### "Your branch is behind main"

This means the main branch has been updated since you created your branch. Update your branch:

```bash
git checkout main
git pull upstream main
git checkout feat/your-feature-name
git merge main
```

If there are conflicts, Git will tell you which files need attention. Open them, look for the conflict markers (`<<<<<<<`, `=======`, `>>>>>>>`), and resolve them manually.

### "Permission denied"

Make sure you're pushing to your fork (`origin`), not the original repository (`upstream`).

### "Merge conflicts"

This happens when your changes overlap with other changes. Open the conflicting files, look for conflict markers, and manually choose which code to keep. Then:

```bash
git add .
git commit -m "fix: resolve merge conflicts"
git push origin feat/your-feature-name
```

## Getting Help

- **GitHub Docs**: [docs.github.com](https://docs.github.com)
- **Git Cheat Sheet**: [education.github.com/git-cheat-sheet](https://education.github.com/git-cheat-sheet-education.pdf)
- **Ask Questions**: Open an issue on the repository if you're stuck
