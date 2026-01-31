# Laravel Herd Setup Guide

This guide walks you through installing Laravel Herd and using it to set up the vibecode.law repository on your local machine.

This guide is a work in progress. If you have any issues getting setup please do open an issue, as we'll use it as an opportunity to improve this guide.

## What is Laravel Herd?

[Laravel Herd](https://herd.laravel.com) is a native development environment for macOS and Windows. It provides everything you need to start Laravel development — PHP, nginx, Node.js, and more — without manual configuration.

## Step 1: Install Laravel Herd

Download and install Laravel Herd from [herd.laravel.com](https://herd.laravel.com).

## Step 2: Configure PHP Version

This project requires **PHP 8.4**. To ensure you're using the correct version:

1. Open Herd
2. Go to **Settings** (or **Preferences** on macOS)
3. Navigate to the **PHP** section
4. Select **PHP 8.4** as your default version (install it first if needed)

## Step 3: Fork the repository

_If you are just playing around, and don't intend to contribute a PR later, you can skip this step and just use the vibecode-law/vibecode-law URL in Step 4 instead of your fork._

In the Github web UI, create a fork from the top right context menu:

![Fork menu](./images/fork-contextmenu.png "Fork menu")

On the page that opens, you don't need to fill anything out - just hit "Create fork" in the bottom right:

![Create fork screen](./images/create-fork.png "Create form screen")

After creating, you'll be redirected to your new fork.

## Step 4: Clone the forked repository

In VSCode, browser to the folder/directory above where you'd like the application to live.

Then clone the forked repository by entering to following into the terminal:

```bash
git clone URL_TO_REPO # Replace with the URL to your forked repository.
```

## Step 5: Setup Dependencies and Environment

Open the folder/directory that the above step just created (typically "vibecode-law").

In the terminal, run the following command to setup your environment:

```bash
composer setup
```

## Step 6: Database Setup

### Using SQLite (Simplest)

By default, the `.env.example` is configured for SQLite. Simply create the database file:

```bash
sqlite3 database/database.sqlite "VACUUM;"
```

### Using PostgreSQL (via Herd Pro)

If you have Herd Pro, you can use the built-in PostgreSQL service:

1. Open Herd
2. Go to **Services**
3. Enable **PostgreSQL**
4. Update your `.env` file:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=vibecode
DB_USERNAME=postgres
DB_PASSWORD=
```

Then create the database:

```bash
psql -U postgres -c "CREATE DATABASE vibecode"
```

### Run Migrations

Once your database is configured, run migrations:

```bash
php artisan migrate
```

Optionally, seed the database with demo data:

```bash
php artisan db:seed --class=DemoSeeder
```

Or, run both together:

```bash
php artisan migrate --seed --seeder=DemoSeeder
```

## Step 7: Link the Site in Herd

if you didn't create the project in the ~/Herd directory, link the site to Herd:

1. Open Herd
2. Click **Add Site** or drag your project folder into Herd
3. Herd will automatically configure nginx for your site

## Step 8: Build Frontend Assets

For development (with hot reloading):

```bash
npm run dev
```

For a production build:

```bash
npm run build
```

## Step 9: Access the Application

Open your browser and visit:

```
http://vibecode-law.test
```

You should see the vibecode.law homepage.

## Troubleshooting

### Not secure

Try enabling HTTPS for the site using the Laravel Herd GUI - find the site and click the padlock icon in the top right.

### "PHP version mismatch" errors

Ensure PHP 8.4 is selected in Herd settings and restart your terminal.

### "npm: command not found"

Install Node.js through Herd's settings, or install it separately from [nodejs.org](https://nodejs.org).

### Database connection errors

1. Verify your `.env` database settings
2. Ensure the database exists
3. For PostgreSQL, confirm the service is running in Herd

### Site not loading

1. Check that Herd is running
2. Verify the site is linked in Herd

### Vite manifest error

Run `npm run build` or start the dev server with `npm run dev`.

## Next Steps

- Read the [Contribution Guidelines](CONTRIBUTING.md) to learn how to contribute
- Explore the codebase structure in the main [README](README.md)

---

Need more help? Check the [Laravel Herd documentation](https://herd.laravel.com/docs) or open an issue in this repository.
