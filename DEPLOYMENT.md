# FocusFlow Deployment Guide

This document outlines the requirements and steps necessary to deploy FocusFlow to a production environment.

## 🛠 Prerequisites & Requirements

- **PHP**: 8.3 or 8.4
- **Database**: PostgreSQL 15+
- **Cache/Queue**: Redis 7+
- **Web Server**: Nginx or Apache
- **SSL Certificate**: HTTPS is required (especially for Stripe webhooks and secure cookies)
- **Node.js & npm/pnpm**: For compiling frontend assets

---

## 🔒 Required Environment Variables

Ensure these keys are configured in your production environment (`.env`):

### Application Core
```ini
APP_NAME=FocusFlow
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_KEY=base64:your-generated-key
```

### Database (PostgreSQL)
```ini
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=focusflow_prod
DB_USERNAME=focusflow_user
DB_PASSWORD=secure-database-password
```

### Redis (Cache, Session, & Queue)
```ini
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Queue & Session Drivers
```ini
CACHE_STORE=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120
QUEUE_CONNECTION=redis
```

### Laravel Reverb (WebSockets)
```ini
REVERB_APP_ID=focusflow-app-id
REVERB_APP_KEY=focusflow-app-key
REVERB_APP_SECRET=focusflow-app-secret
REVERB_HOST=yourdomain.com
REVERB_PORT=443
REVERB_SCHEME=https
```

### Laravel Cashier (Stripe Billing)
```ini
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...
CASHIER_CURRENCY=usd
```

---

## 🚀 Deployment Steps

Run the following command sequence on the server during deployment:

### 1. Install Dependencies
```bash
# Install PHP dependencies optimized for production
composer install --no-dev --optimize-autoloader

# Install Node dependencies and compile production assets
pnpm install
pnpm run build
```

### 2. Cache Configurations
```bash
# Cache config, routes, and views for optimal performance
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 3. Run Database Migrations
```bash
# Run migrations with the force flag to bypass confirmation prompts
php artisan migrate --force
```

---

## 😈 Daemon Management (Production Services)

FocusFlow relies on persistent background processes that **must** run as daemons (e.g., managed via Supervisor).

### 1. Laravel Queue / Horizon (Queue Worker)
Instead of running standard queue workers, FocusFlow uses **Laravel Horizon** for queue monitoring and scaling:
```bash
# Start Horizon in daemon mode
php artisan horizon
```
*Supervisor configuration snippet:*
```ini
[program:focusflow-horizon]
process_name=%(program_name)s
command=php /home/forge/yourdomain.com/artisan horizon
autostart=true
autorestart=true
user=forge
redirect_stderr=true
stdout_logfile=/home/forge/yourdomain.com/storage/logs/horizon.log
stopwaitsecs=3600
```

### 2. Laravel Reverb (WebSockets Server)
To support real-time task drag-and-drop, presence updates, and notification updates, the Reverb server must run continuously:
```bash
# Start the WebSocket server
php artisan reverb:start --host=0.0.0.0 --port=8080
```
*Supervisor configuration snippet:*
```ini
[program:focusflow-reverb]
process_name=%(program_name)s
command=php /home/forge/yourdomain.com/artisan reverb:start --host=0.0.0.0 --port=8080
autostart=true
autorestart=true
user=forge
redirect_stderr=true
stdout_logfile=/home/forge/yourdomain.com/storage/logs/reverb.log
```

---

## 🌐 Recommended Hosting Options

1. **Laravel Forge + AWS/DigitalOcean (Recommended)**:
   - Configures Nginx, PHP, Redis, and Postgres automatically.
   - Built-in daemon managers for Horizon and Reverb.
   - Native integration with Let's Encrypt for SSL certificates.

2. **Fly.io**:
   - Run Laravel and Reverb in lightweight Firecracker microVMs.
   - High availability with multi-region support.
   - Uses managed Redis/PostgreSQL resources.
