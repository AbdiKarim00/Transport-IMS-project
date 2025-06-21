# Unified Laravel + Vite Development Setup

This guide explains how to run both Laravel backend and Vite frontend servers together for a unified development experience.

## Prerequisites

- PHP 8.2+ installed
- Composer installed
- Node.js and npm installed
- Laravel dependencies installed (`composer install`)
- Frontend dependencies installed (`npm install`)

## Option 1: Concurrent Development (Recommended)

### Using the built-in concurrent setup:

```bash
# This runs all services simultaneously:
# - Laravel server (port 8000)
# - Queue worker
# - Log viewer
# - Vite dev server (port 5173)
npx concurrently -c "#93c5fd,#c4b5fd,#fb7185,#fdba74" "php artisan serve" "php artisan queue:listen --tries=1" "php artisan pail --timeout=0" "npm run dev" --names=server,queue,logs,vite
```

### Access your application:

- **Main App**: http://localhost:8000 (Laravel server with Vite assets)
- **Vite Dev Server**: http://localhost:5173 (with Laravel backend proxy)

## Option 2: Manual Setup

### Terminal 1 - Laravel Server:

```bash
php artisan serve
```

### Terminal 2 - Vite Dev Server:

```bash
npm run dev
```

### Access your application:

- **Main App**: http://localhost:8000
- **API Endpoints**: http://localhost:8000/api/\*
- **Vite with Proxy**: http://localhost:5173 (proxies to Laravel)

## Option 3: Simple Concurrent Setup

If you prefer a simpler setup without queue and logs:

```bash
npx concurrently "php artisan serve" "npm run dev" --names=laravel,vite --prefix=name
```

## How the Unified System Works

1. **Laravel Server (port 8000)**: Handles all PHP requests, blade templates, API routes, authentication
2. **Vite Dev Server (port 5173)**: Provides hot module replacement for CSS/JS and proxies requests to Laravel
3. **Proxy Configuration**: Vite automatically forwards requests for `/api`, `/login`, `/admin`, etc. to Laravel

## Environment Setup

Make sure you have a `.env` file:

```bash
cp .env.example .env
php artisan key:generate
```

## Database Setup

```bash
# If using SQLite (default)
touch database/database.sqlite

# Run migrations
php artisan migrate

# Optional: Seed with test data
php artisan db:seed
```

## Troubleshooting

### Port Conflicts

- Laravel default: 8000
- Vite default: 5173
- Change ports if needed: `php artisan serve --port=8001`

### Asset Loading Issues

- Ensure APP_URL in .env matches your Laravel server URL
- Clear config cache: `php artisan config:clear`

### Hot Module Replacement

- Vite provides HMR for CSS/JS changes
- Laravel server handles PHP/Blade template changes

## Development Workflow

1. Start both servers (using concurrent command above)
2. Access your app at http://localhost:8000
3. Make changes to:
   - **PHP/Blade files**: Laravel will serve updated content
   - **CSS/JS files**: Vite will hot-reload changes
4. API calls work seamlessly between frontend and backend

This setup gives you the best of both worlds: Laravel's powerful backend with Vite's fast frontend development experience.
