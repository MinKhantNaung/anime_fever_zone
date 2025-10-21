# 🚀 Production Docker Setup Guide

## Overview

This guide covers deploying your Laravel Octane application to production using Docker.

---

## 📋 What's Different in Production

| Aspect | Development | Production |
|--------|-------------|------------|
| **Dockerfile** | `Dockerfile` | `Dockerfile.prod` |
| **Docker Compose** | `docker-compose.yml` | `docker-compose.prod.yml` |
| **PHP Config** | `php.ini-development` | `php.ini-production` |
| **Volumes** | Code mounted | Code baked in image |
| **Octane** | `--watch` enabled | Optimized workers |
| **Laravel Cache** | None | Config/routes/views cached |
| **Dependencies** | Manual install | Baked in image |
| **User** | root | www-data (non-root) |
| **Opcache** | Disabled | Enabled & optimized |
| **Node.js** | Kept | Removed after build |

---

## 🏗️ Production Architecture

```
┌─────────────────────────────────────────────────────┐
│                  Production Server                   │
│                                                       │
│  Internet → Port 80/443                              │
└────────────────────┬────────────────────────────────┘
                     │
┌────────────────────┼────────────────────────────────┐
│             Docker Network: laravel                 │
│                     │                               │
│  ┌─────────────┐  ┌─┴─────────────┐  ┌───────────┐ │
│  │   Nginx     │  │    App        │  │  MySQL    │ │
│  │  (Alpine)   │→ │  (Optimized)  │  │  (Prod)   │ │
│  │  Port 80    │  │  Port 8000    │  │           │ │
│  │  (443 SSL)  │  │  (Octane)     │  │           │ │
│  └─────────────┘  └───────────────┘  └───────────┘ │
│                          │                          │
│                   ┌──────┴──────┐                   │
│                   │    Redis    │                   │
│                   │  (Password) │                   │
│                   └─────────────┘                   │
└─────────────────────────────────────────────────────┘
```

---

## 🔧 Pre-Deployment Checklist

### 1. **Prepare Environment Variables**

Create `.env.production` file (or use your hosting provider's environment):

```bash
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_KEY_HERE  # Generate with: php artisan key:generate

# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_DATABASE=anime_fever_zone
DB_USERNAME=animeuser
DB_PASSWORD=STRONG_PASSWORD_HERE
DB_ROOT_PASSWORD=STRONG_ROOT_PASSWORD_HERE

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=STRONG_REDIS_PASSWORD_HERE

# Cache & Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### 2. **Security Checklist**

- ✅ Change all default passwords
- ✅ Generate new `APP_KEY`
- ✅ Set `APP_DEBUG=false`
- ✅ Configure SSL/TLS certificates
- ✅ Set up firewall rules
- ✅ Enable Redis password
- ✅ Use strong MySQL passwords

### 3. **Server Requirements**

- ✅ Docker Engine 20.10+
- ✅ Docker Compose 2.0+
- ✅ 2GB+ RAM minimum (4GB recommended)
- ✅ 10GB+ disk space
- ✅ Open ports: 80, 443

---

## 🚀 Deployment Steps

### Step 1: Clone Repository

```bash
cd /var/www
git clone https://github.com/your-repo/animefeverzone.git
cd animefeverzone
```

### Step 2: Configure Environment

```bash
# Copy and edit production environment
cp .env.example .env.production
nano .env.production  # Edit with your production values
```

**Important settings:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
```

### Step 3: Build Production Image

```bash
# Build the production image
docker compose -f docker-compose.prod.yml build --no-cache

# This will:
# ✓ Install all dependencies
# ✓ Build frontend assets
# ✓ Cache Laravel config/routes/views
# ✓ Optimize everything for production
# ✓ Remove unnecessary packages
```

⏱️ **Build time:** ~10-15 minutes (first time)

### Step 4: Start Services

```bash
# Start all services
docker compose -f docker-compose.prod.yml up -d

# Check status
docker compose -f docker-compose.prod.yml ps
```

### Step 5: Run Migrations

```bash
# Run database migrations
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force

# Seed database (if needed)
docker compose -f docker-compose.prod.yml exec app php artisan db:seed --force
```

### Step 6: Verify Deployment

```bash
# Check application health
curl http://localhost

# Check Octane is running
docker compose -f docker-compose.prod.yml exec app supervisorctl status

# View logs
docker compose -f docker-compose.prod.yml logs -f app
```

---

## 📊 Production Optimizations Explained

### **1. Baked Dependencies**
```dockerfile
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader
```
- Dependencies are installed during build
- No need to mount `vendor/` directory
- Faster container startup

### **2. Asset Building**
```dockerfile
COPY package*.json ./
RUN npm ci --only=production
COPY . .
RUN npm run build
```
- Assets built during image creation
- Node.js removed after building (smaller image)
- No runtime asset compilation

### **3. Laravel Caching**
```dockerfile
RUN php artisan app:optimize-all
```
- **Custom optimization command:** Handles all Laravel optimizations
- **Config cache:** ~50ms faster per request
- **Route cache:** ~30ms faster per request
- **View cache:** Blade compilation cached
- **Event cache:** Event discovery cached

### **4. Custom PHP Configuration**
```ini
# Security
disable_functions = system, exec, shell_exec, passthru, phpinfo, show_source, ...

# File Upload Limits
upload_max_filesize = 100M
max_file_uploads = 100
post_max_size = 110M

# Execution Limits
max_execution_time = 300
max_input_time = 300
memory_limit = 256M
```
- **Security:** Dangerous functions disabled
- **Upload support:** 100MB files, 100 files per request
- **Long-running:** 5 minutes execution time (for heavy tasks)

### **5. Opcache Configuration**
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0  # Never check for file changes
```
- **Benefit:** 2-3x faster PHP execution
- Scripts compiled and cached in memory

### **6. Octane Workers**
```ini
command=php artisan octane:start --workers=4 --task-workers=6 --max-requests=1000
```
- `--workers=4`: Number of request workers (adjust based on CPU cores)
- `--task-workers=6`: Concurrent tasks
- `--max-requests=1000`: Restart workers after 1000 requests (prevents memory leaks)

### **7. Non-Root User**
```dockerfile
USER www-data
```
- Enhanced security
- Follows Docker best practices
- Limits potential attack surface

---

## 🔄 Updating Production

### Quick Update (Code Changes Only)

```bash
# Pull latest code
git pull origin main

# Rebuild and restart
docker compose -f docker-compose.prod.yml build app
docker compose -f docker-compose.prod.yml up -d --no-deps app

# Re-optimize application
docker compose -f docker-compose.prod.yml exec app php artisan app:optimize-all

# Or clear caches if needed
docker compose -f docker-compose.prod.yml exec app php artisan cache:clear
```

### Full Rebuild (Dependencies Changed)

```bash
# Pull latest code
git pull origin main

# Rebuild completely
docker compose -f docker-compose.prod.yml down
docker compose -f docker-compose.prod.yml build --no-cache
docker compose -f docker-compose.prod.yml up -d

# Run migrations
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
```

---

## 🔍 Monitoring & Debugging

### View Logs

```bash
# All logs
docker compose -f docker-compose.prod.yml logs -f

# Specific service
docker compose -f docker-compose.prod.yml logs -f app
docker compose -f docker-compose.prod.yml logs -f nginx

# Inside container
docker compose -f docker-compose.prod.yml exec app tail -f /var/www/storage/logs/laravel.log
docker compose -f docker-compose.prod.yml exec app tail -f /var/www/storage/logs/octane.log
```

### Check Service Health

```bash
# Check all services
docker compose -f docker-compose.prod.yml ps

# Check supervisor processes
docker compose -f docker-compose.prod.yml exec app supervisorctl status

# Check Octane health
docker compose -f docker-compose.prod.yml exec app curl http://localhost:8000
```

### Resource Usage

```bash
# Container stats
docker stats

# Disk usage
docker system df
```

---

## 🔐 Security Best Practices

### 1. **Use Strong Passwords**
```bash
# Generate secure passwords
openssl rand -base64 32
```

### 2. **Enable Firewall**
```bash
# UFW example
sudo ufw allow 22/tcp  # SSH
sudo ufw allow 80/tcp  # HTTP
sudo ufw allow 443/tcp # HTTPS
sudo ufw enable
```

### 3. **SSL/TLS Certificates**

Add to `docker-compose.prod.yml`:
```yaml
nginx:
  volumes:
    - ./ssl/cert.pem:/etc/nginx/ssl/cert.pem:ro
    - ./ssl/key.pem:/etc/nginx/ssl/key.pem:ro
```

### 4. **Regular Updates**
```bash
# Update images
docker compose -f docker-compose.prod.yml pull

# Rebuild with latest base images
docker compose -f docker-compose.prod.yml build --pull
```

### 5. **Backup Strategy**
```bash
# Backup database
docker compose -f docker-compose.prod.yml exec mysql mysqldump -u root -p anime_fever_zone > backup.sql

# Backup volumes
docker run --rm -v animefeverzone_mysql_data:/data -v $(pwd):/backup alpine tar czf /backup/mysql-backup.tar.gz /data
```

---

## ⚙️ Performance Tuning

### For 2 CPU Cores:
```ini
--workers=2 --task-workers=4
```

### For 4 CPU Cores:
```ini
--workers=4 --task-workers=6
```

### For 8 CPU Cores:
```ini
--workers=8 --task-workers=12
```

### Memory Optimization:
```ini
opcache.memory_consumption=512  # For high traffic
```

---

## 🚨 Troubleshooting

### Issue: 502 Bad Gateway

**Check:**
```bash
docker compose -f docker-compose.prod.yml logs app
docker compose -f docker-compose.prod.yml exec app supervisorctl status
```

**Solution:**
```bash
docker compose -f docker-compose.prod.yml restart app
```

### Issue: High Memory Usage

**Solution:** Adjust max_requests:
```ini
--max-requests=500  # Lower value = more frequent restarts
```

### Issue: Slow Response Times

**Check:**
```bash
# View Octane workers
docker compose -f docker-compose.prod.yml exec app php artisan octane:status

# Check database queries
# Enable query log in .env
DB_LOG=true
```

---

## 📈 Scaling

### Horizontal Scaling (Multiple App Containers)

```yaml
app:
  deploy:
    replicas: 3  # Run 3 instances
```

### Load Balancer
Add Nginx upstream:
```nginx
upstream octane {
    server app1:8000;
    server app2:8000;
    server app3:8000;
}
```

---

## 📝 Quick Command Reference

```bash
# Start production
docker compose -f docker-compose.prod.yml up -d

# Stop production
docker compose -f docker-compose.prod.yml down

# Restart app only
docker compose -f docker-compose.prod.yml restart app

# View logs
docker compose -f docker-compose.prod.yml logs -f app

# Run artisan command
docker compose -f docker-compose.prod.yml exec app php artisan [command]

# Access shell
docker compose -f docker-compose.prod.yml exec app bash

# Check supervisor
docker compose -f docker-compose.prod.yml exec app supervisorctl status

# Restart Octane
docker compose -f docker-compose.prod.yml exec app supervisorctl restart animefeverzone-octane
```

---

## ✅ Production Checklist

Before going live:

- [ ] All environment variables configured
- [ ] Strong passwords set
- [ ] `APP_DEBUG=false`
- [ ] SSL certificates configured
- [ ] Firewall configured
- [ ] Backups configured
- [ ] Monitoring set up
- [ ] Logs rotation configured
- [ ] Health checks working
- [ ] Load testing completed
- [ ] Rollback plan ready

---

## 🎯 Performance Benchmarks

Expected performance with production setup:

- **Requests per second:** 500-2000 (depending on hardware)
- **Response time:** 10-50ms (simple routes)
- **Memory per worker:** ~50-100MB
- **CPU usage:** 30-60% (under load)

Compare this to PHP-FPM: **5-10x faster!** 🚀

