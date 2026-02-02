# Architecture & file structure overview

This document provides an overview of the project's architecture and file structure.

## Project overview

vibecode.law is a community-driven showcase platform for legaltech projects built with AI coding tools. The tech stack is Laravel 12 (PHP 8.4) + React 19 + TypeScript + Inertia.js v2 + Tailwind CSS v4.

## Top-level directory structure

```
vibecode/
├── app/                  # Backend logic (Laravel application)
├── bootstrap/            # Application bootstrap configuration
├── config/               # Configuration files
├── content/              # Markdown content 
├── database/             # Migrations, factories, seeders
├── docs/                 # Setup and contribution guides
├── public/               # Publicly accessible files
├── resources/            # Frontend assets (React/CSS)
├── routes/               # Route definitions
├── storage/              # Application storage
├── tests/                # Test suite (Pest)
└── .github/              # GitHub workflows and templates
```

## Backend structure

### App directory organization

```
app/
├── Actions/              # Business logic actions (isolated operations)
├── Console/Commands/     # Artisan CLI commands
├── Concerns/             # Shared traits
├── Enums/                # PHP Enums (ShowcaseStatus, SourceStatus, etc.)
├── Http/
│   ├── Controllers/      # Request handlers (organized by domain)
│   ├── Middleware/       # HTTP middleware
│   └── Requests/         # Form validation classes
├── Jobs/                 # Queued jobs
├── Listeners/            # Event listeners
├── Models/               # Eloquent models
├── Notifications/        # Email/notification classes
├── Policies/             # Authorization policies
├── Providers/            # Service providers
├── Queries/              # Query builder helpers
├── Services/             # Reusable business logic
├── Support/              # Support classes (rate limits, etc.)
└── ValueObjects/         # Immutable value objects that don't belong elsewhere
```

### Key architectural patterns

- **Actions pattern**: Business logic extracted into invokable classes in `app/Actions/` rather than controllers. Use `CreateShowcaseDraftAction`, `ApproveShowcaseDraftAction`, etc.
- **Services**: Domain-specific services in `app/Services/` for complex operations (e.g., `MarkdownService`, `ShowcaseMediaService`, `ShowcaseRankingService`).
- **Eloquent resources**: Data transformation using Spatie Laravel Data resources in controller responses.
- **Query classes**: Complex queries encapsulated in `app/Queries/` for reusability.
- **Policies**: Authorization using Laravel policies in `app/Policies/`.

### Routes organization

```
routes/
├── web.php               # Main router (includes others)
├── auth.php              # Fortify auth routes
├── authed/
│   ├── user-area.php     # User dashboard routes
│   ├── showcase.php      # Authenticated showcase routes
│   └── staff.php         # Staff/admin routes
└── guest/
    ├── showcase.php      # Public showcase viewing
    └── user.php          # Public user profiles
```

## Frontend structure

### React file structure

```
resources/js/
├── app.tsx                                 # Entry point (Inertia setup)
├── ssr.tsx                                 # Server-side rendering entry
├── components/
│   ├── layout/                             # Layout components
│   ├── navigation/                         # Nav, menus, sidebars
│   ├── ui/                                 # Base UI components (Button, Input, etc.)
│   ├── providers/                          # Context providers
│   └── [page specific components]/         # Directories grouping components related to specific pages.
├── pages/
│   ├── auth/                               # Login, registration, password reset
│   └── [pages]/                            # Pages grouped into domains
├── layouts/
│   ├── public-layout.tsx                   # Main site layout
│   ├── auth-layout.tsx                     # Auth form layouts
│   ├── user-area/                          # Dashboard layout
│   └── staff-area/                         # Staff area layout
├── hooks/                                  # Custom React hooks
├── lib/                                    # Utility functions
├── actions/                                # Wayfinder-generated route functions
├── routes/                                 # Wayfinder-generated named routes
└── types/
    └── generated.d.ts                      # Auto-generated TypeScript types
```

### UI components

- UI components in `components/ui/` are built with Radix UI primitives and Tailwind CSS.
- Feature components are organized by domain (`showcase/`, `user/`, `navigation/`).
- Layouts are two-level: global layout + page-specific layout.

### Wayfinder integration

- Import route functions from `@/actions/` (controllers) or `@/routes/` (named routes).
- Use `.form()` with `<Form>` component or `form.submit(store())` with useForm.
- Run `php artisan wayfinder:generate --with-form` after route changes.

## Test structure

Tests follow the same structure as the app directory.

## Content management

Static content (legal, about, resources) is stored as Markdown files in `/content` and loaded via `ContentService`. This enables version control and easy editing without database changes.

## Authentication & authorization

- **Auth backend**: Laravel Fortify (headless authentication) with email/password, 2FA, and password reset.
- **Social login**: LinkedIn OAuth via Socialite.
- **Permissions**: Spatie Laravel Permission for roles and permissions.
- **Admin gate**: Custom check via `is_admin` boolean on User model.

## Key scripts

### Backend

- `composer dev` - Run dev server (Artisan + Vite)
- `composer format` - Run Pint formatter
- `composer types` - PHPStan analysis
- `composer definitions` - Generate IDE helpers, TypeScript types, Wayfinder
- `composer test` - Run full Pest suite

### Frontend

- `npm run dev` - Dev server with HMR
- `npm run build` - Production build
- `npm run format` - Prettier formatting
- `npm run lint` - ESLint with fixes
- `npm run types` - TypeScript checking
