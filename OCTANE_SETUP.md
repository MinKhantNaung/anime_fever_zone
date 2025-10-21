# Laravel Octane with Swoole - Docker Setup

> **Note:** This is a manual setup. You'll need to run `composer install` and `npm install` before starting containers.

## What Changed

Your Docker setup has been migrated from **PHP-FPM** to **Laravel Octane with Swoole**, which will provide significantly better performance.

### Changes Made:

1. **Dockerfile** (`docker/php/Dockerfile`)
   - Changed base image from `php:8.3-fpm` to `php:8.3-cli`
   - Installed Node.js 20.x and npm
   - Installed Swoole extension via PECL
   - Changed CMD to run `supervisord` instead of `php-fpm`

2. **Docker Compose** (`docker-compose.yml`)
   - Removed separate `supervisor` service
   - Merged supervisor functionality into the main `app` service
   - Added supervisord.conf volume mount

3. **Nginx Configuration** (`docker/nginx/default.conf`)
   - Enabled Octane proxy configuration
   - Removed PHP-FPM fastcgi_pass configuration
   - Now proxying requests to Octane on port 8000

4. **Supervisor Configuration**
   - Created main `supervisord.conf` file
   - Uses existing `octane.conf` to manage Octane process

## How to Use

### 1. Install Dependencies First

```bash
# Install Composer dependencies (on host or in container)
composer install

# Install npm dependencies (on host or in container)
npm install

# Or inside container
docker compose exec app composer install
docker compose exec app npm install
```

### 2. Rebuild and Start Containers

```bash
# Stop existing containers
docker compose down

# Rebuild the app container with Swoole
docker compose build --no-cache app

# Start all services
docker compose up -d
```

### 3. Verify Octane is Running

```bash
# Check app container logs
docker compose logs -f app

# You should see Octane server starting on port 8000
```

### 4. Check Supervisor Status

```bash
# Enter the container
docker compose exec app bash

# Check supervisor status
supervisorctl status

# You should see: animefeverzone-octane RUNNING
```

### 5. Access Your Application

Your application is accessible at: **http://localhost:8081**

Nginx proxies requests from port 80 (mapped to 8081) to Octane on port 8000.

## Managing Dependencies & Assets

### Install/Update Composer Packages

```bash
# Install new package
docker compose exec app composer require vendor/package

# Update dependencies
docker compose exec app composer update
```

### Build Frontend Assets

```bash
# Install npm packages
docker compose exec app npm install

# Build for production
docker compose exec app npm run build

# Or for development with watch
docker compose exec app npm run dev
```

## Managing Octane

### Restart Octane

```bash
# From host
docker-compose exec app supervisorctl restart animefeverzone-octane

# Or restart entire container
docker-compose restart app
```

### View Octane Logs

```bash
# Real-time logs
docker-compose logs -f app

# Or view log files inside container
docker-compose exec app tail -f /var/www/storage/logs/octane.log
docker-compose exec app tail -f /var/www/storage/logs/octane-error.log
```

### Stop Octane

```bash
docker-compose exec app supervisorctl stop animefeverzone-octane
```

### Start Octane

```bash
docker-compose exec app supervisorctl start animefeverzone-octane
```

## Performance Benefits

With Octane + Swoole, you'll get:

- **Faster response times**: No PHP bootstrap on each request
- **Persistent application state**: Framework stays in memory
- **Better concurrency**: Handles multiple requests efficiently
- **WebSocket support**: Real-time features possible
- **Lower resource usage**: Fewer processes needed

## Important Notes

### Code Changes

⚠️ **Octane keeps your application in memory**, so code changes won't be reflected automatically.

**After making code changes, restart Octane:**

```bash
docker compose exec app supervisorctl restart animefeverzone-octane
```

**Optional: Enable auto-reload for development**

Add `--watch` flag in `docker/supervisor/octane.conf`:

```ini
command=php /var/www/artisan octane:start --server=swoole --host=0.0.0.0 --port=8000 --watch
```

Then restart the container:
```bash
docker compose restart app
```

### Memory Leaks

Be careful with:
- Static variables
- Global state
- Singleton patterns that cache data indefinitely

### Config Caching

For production, cache your config:
```bash
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
```

## Troubleshooting

### Octane won't start

```bash
# Check logs
docker-compose logs app

# Check if Swoole is installed
docker-compose exec app php -m | grep swoole

# Check if port 8000 is available
docker-compose exec app netstat -tulpn | grep 8000
```

### 502 Bad Gateway

This means Nginx can't reach Octane. Check:
```bash
# Is Octane running?
docker-compose exec app supervisorctl status

# Check Octane logs
docker-compose exec app tail -f /var/www/storage/logs/octane.log
```

### High memory usage

```bash
# Restart Octane to clear memory
docker-compose exec app supervisorctl restart animefeverzone-octane
```

## Configuration Files

- **Dockerfile**: `docker/php/Dockerfile`
- **Docker Compose**: `docker-compose.yml`
- **Nginx Config**: `docker/nginx/default.conf`
- **Supervisor Main Config**: `docker/supervisor/supervisord.conf`
- **Octane Supervisor Config**: `docker/supervisor/octane.conf`

## Additional Features

### Add Queue Workers

Create `docker/supervisor/queue.conf`:

```ini
[program:animefeverzone-queue]
command=php /var/www/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=root
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/storage/logs/queue.log
stderr_logfile=/var/www/storage/logs/queue-error.log
```

Then restart: `docker-compose restart app`

### Add Scheduler

Create `docker/supervisor/scheduler.conf`:

```ini
[program:animefeverzone-scheduler]
command=/bin/bash -c "while true; do php /var/www/artisan schedule:run --verbose --no-interaction & sleep 60; done"
autostart=true
autorestart=true
user=root
redirect_stderr=true
stdout_logfile=/var/www/storage/logs/scheduler.log
```

Then restart: `docker-compose restart app`

## Resources

- [Laravel Octane Documentation](https://laravel.com/docs/octane)
- [Swoole Documentation](https://www.swoole.co.uk/)
- [Supervisor Documentation](http://supervisord.org/)

