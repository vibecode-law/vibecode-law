<p align="center"><img src="./public/static/text-logo.png" width="50%" /></p>

# vibecode.law

The open-source platform powering [vibecode.law](https://vibecode.law) — a community-driven showcase for legaltech projects — especially prototypes built with AI coding tools.

This repository is primarily geared towards those looking to make contributions back to the functionality platform - whether that be a feature request, a bug report or a code contribution.

## Contributing

We welcome contributions from the community. 

Please see our [Contribution Guidelines](./.github/CONTRIBUTING.md) on how to get involved.

> [!TIP]
> If you are unsure about the process of making a pull request, why not point your AI coding tool at the [Contribution Guidelines](./.github/CONTRIBUTING.md) and ask it to guide you through?

## Technology Stack

| Layer | Technology |
|-------|------------|
| Backend | [Laravel 12](https://laravel.com) (PHP 8.4) |
| Frontend | [React 19](https://react.dev) with [TypeScript](https://www.typescriptlang.org) |
| Backend-Frontend Communication | [Inertia.js v2](https://inertiajs.com) |
| Styling | [Tailwind CSS v4](https://tailwindcss.com) |

## Getting Started

### Remote Environment - Github Codespaces

The easiest way to get started is by using Github Codespaces, which gives you access to a fully configured remote development environment. 

See [Getting Started using Github Codespaces Guide](docs/CODESPACES_SETUP.md)

### Local Environment - Laravel Herd

If you wish to run the app locally, we recommend using [Laravel Herd](https://herd.laravel.com). 

See [Laravel Herd Setup Guide](docs/HERD_SETUP.md)

### AI-Assisted Development

This project uses [Laravel Boost](https://github.com/laravel/boost) to enhance AI-assisted development. Boost is an MCP server that provides AI tools with deep Laravel knowledge.

Boost automatically configures application-specific guidance that makes AI tools more effective when working on this codebase. This includes project conventions, package versions, architectural patterns and instructions on how to run its test suite and linting tools.

Boost supports:
- Claude Code
- Cursor
- Codex
- Gemini CLI
- Github Copilot
- Junie
- OpenCode

To get started with Boost, from your project directory run the following command from our editor of choice and follow the terminal instructions:

```bash
php artisan boost:install
```

For troubleshooting, see the [Laravel Boost Github Readme](https://github.com/laravel/boost).

## Useful commands


```bash
## Development workflow
composer fresh-demo     # Wipe existing database and rebuild database with fresh demo data

## Code quality & testing
composer format         # Format backend code
composer types          # Static analysis & type checks
composer test           # Run full test suite

composer lint-test      # Run code quality checks and tests
composer format-test    # Run formatter, static analysis and then tests

npm run format          # Auto format frontend code
npm run lint            # Check for frontend code quality issues
npm run types           # Check for frontend type issues

npm run format-lint     # Auto format, lint and type checks.
npm run check-all       # Check formatting (without making changes), lint and check types.
```


## Authors

Created by **Chris Bridges**, **Matt Pollins** and **Alex Baker**, with contributions from the [Open Source Community](https://github.com/vibecode-law/vibecode-law/graphs/contributors).

## License

This project is open-sourced software licensed under the [MIT License](LICENSE).
