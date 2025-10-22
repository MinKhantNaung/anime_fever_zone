# 🚀 Production Deployment Guide for AnimeFeverZone

This guide will walk you through deploying your Laravel application with Docker to a VPS.

## 📋 Table of Contents
- [Prerequisites](#prerequisites)
- [VPS Setup](#vps-setup)
- [Security Configuration](#security-configuration)
- [Application Deployment](#application-deployment)
- [SSL Certificate Setup](#ssl-certificate-setup)
- [Post-Deployment Tasks](#post-deployment-tasks)
- [Maintenance](#maintenance)
- [Troubleshooting](#troubleshooting)

---

## Prerequisites

### VPS Requirements
- **Minimum:** 2GB RAM, 2 vCPUs, 50GB storage
- **Recommended:** 4GB RAM, 2-4 vCPUs, 80GB storage
- **OS:** Ubuntu 22.04 LTS or Ubuntu 24.04 LTS
- **Root or sudo access**

### Domain Setup
- Domain name registered (e.g., animefeverzone.com)
- DNS A record pointing to your VPS IP address
- Wait for DNS propagation (can take up to 48 hours, usually 15-30 minutes)

### Local Requirements
- Git configured with SSH access to your repository
- Docker and Docker Compose knowledge

---

## 🖥️ VPS Setup

### 1. Initial Server Setup

```bash
# Connect to your VPS
ssh root@your_vps_ip

# Update system packages
apt update && apt upgrade -y

# Create a non-root user (recommended)
adduser deploy
usermod -aG sudo deploy
usermod -aG docker deploy

# Switch to the new user
su - deploy
```

### 2. Install Docker

```bash
# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Install Docker Compose
sudo apt install docker-compose-plugin -y

# Verify installation
docker --version
docker compose version

# Enable Docker to start on boot
sudo systemctl enable docker
sudo systemctl start docker
```

### 3. Add Swap Space (Important for Laravel Octane)

```bash
# Create 2GB swap file
sudo fallocate -l 2G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile

# Make swap permanent
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab

# Verify swap
free -h
```

### 4. Configure Firewall

```bash
# Install UFW if not already installed
sudo apt install ufw -y

# Configure firewall rules
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 80/tcp   # HTTP
sudo ufw allow 443/tcp  # HTTPS

# Enable firewall
sudo ufw enable

# Check status
sudo ufw status
```

---

## 🔒 Security Configuration

### 1. Secure SSH Access

```bash
# Edit SSH configuration
sudo nano /etc/ssh/sshd_config

# Recommended settings:
# PermitRootLogin no
# PasswordAuthentication no  # Only if you have SSH keys set up
# Port 22  # Or change to a non-standard port

# Restart SSH service
sudo systemctl restart sshd
```

### 2. Install Fail2Ban (Optional but Recommended)

```bash
sudo apt install fail2ban -y
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

---

## 📦 Application Deployment

### 1. Clone Your Repository

```bash
# Navigate to web directory
cd /var/www

# Clone your repository
sudo git clone https://github.com/yourusername/animefeverzone.git
cd animefeverzone

# Set proper ownership
sudo chown -R deploy:deploy /var/www/animefeverzone
```

### 2. Configure Environment Variables

```bash
# Copy the example file
cp env.prod.example .env.prod

# Generate strong passwords
openssl rand -base64 32  # Run this multiple times for different passwords

# Edit the .env.prod file
nano .env.prod
```

**Important Environment Variables to Set:**
```bash
# Generate APP_KEY (you'll need to do this locally or in a PHP container)
# docker run --rm -v $(pwd):/app -w /app php:8.3-cli php artisan key:generate --show

APP_KEY=base64:YOUR_GENERATED_KEY_HERE
DB_PASSWORD=your_strong_db_password_here
DB_ROOT_PASSWORD=your_strong_root_password_here
REDIS_PASSWORD=your_strong_redis_password_here

# Mail settings (use your actual SMTP provider)
MAIL_HOST=smtp.gmail.com  # or your mail provider
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-specific-password
MAIL_FROM_ADDRESS=noreply@animefeverzone.com
```

### 3. Generate Application Key

```bash
# Build the app container first (without starting it)
docker compose -f docker-compose.prod.yml build app

# Generate APP_KEY
docker run --rm \
  --entrypoint php \
  animefeverzone_app_prod \
  artisan key:generate --show

# Copy the output and add it to your .env.prod file
```

### 4. Update Configuration Files

If your domain is different from `animefeverzone.com`, update these files:

```bash
# Update nginx configuration
nano docker/nginx/nginx.prod.conf
# Replace all instances of animefeverzone.com with your domain

# Update init-letsencrypt.sh
nano init-letsencrypt.sh
# Update the domains array and email address
```

---

## 🔐 SSL Certificate Setup

### 1. Initial SSL Certificate Obtainment

```bash
# Make the script executable
chmod +x init-letsencrypt.sh

# Run the SSL initialization script
sudo ./init-letsencrypt.sh
```

**What this script does:**
1. Starts nginx container
2. Obtains SSL certificates from Let's Encrypt
3. Reloads nginx with SSL configuration
4. Starts the certbot renewal service

### 2. Verify SSL Certificate

```bash
# Check certificate
docker compose -f docker-compose.prod.yml exec certbot certbot certificates

# Test renewal (dry run)
docker compose -f docker-compose.prod.yml exec certbot certbot renew --dry-run
```

---

## 🚢 Application Startup

### 1. Build and Start All Services

```bash
# Load environment variables
export $(cat .env.prod | grep -v '^#' | xargs)

# Build all containers
docker compose -f docker-compose.prod.yml build

# Start all services
docker compose -f docker-compose.prod.yml up -d

# Check container status
docker compose -f docker-compose.prod.yml ps
```

### 2. Run Database Migrations

```bash
# Run migrations
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force

# Seed database (if needed)
docker compose -f docker-compose.prod.yml exec app php artisan db:seed --force
```

### 3. Verify Application

```bash
# Check logs
docker compose -f docker-compose.prod.yml logs -f app

# Test health checks
curl -I http://localhost
curl -I https://animefeverzone.com

# Check Octane status
docker compose -f docker-compose.prod.yml exec app php artisan octane:status
```

---

## ✅ Post-Deployment Tasks

### 1. Setup Log Rotation

```bash
# Create logrotate configuration
sudo nano /etc/logrotate.d/docker-animefeverzone

# Add the following:
/var/lib/docker/volumes/animefeverzone_mysql_data/_data/*.log {
    daily
    rotate 7
    compress
    delaycompress
    missingok
    notifempty
}
```

### 2. Setup Automated Backups

```bash
# Create backup script
sudo nano /usr/local/bin/backup-animefeverzone.sh
```

**Backup Script Content:**
```bash
#!/bin/bash
BACKUP_DIR="/var/backups/animefeverzone"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

# Backup database
docker compose -f /var/www/animefeverzone/docker-compose.prod.yml \
  exec -T mysql mysqldump -u root -p$DB_ROOT_PASSWORD anime_fever_zone \
  | gzip > $BACKUP_DIR/db_backup_$DATE.sql.gz

# Keep only last 7 days of backups
find $BACKUP_DIR -name "db_backup_*.sql.gz" -mtime +7 -delete

echo "Backup completed: $DATE"
```

```bash
# Make script executable
sudo chmod +x /usr/local/bin/backup-animefeverzone.sh

# Add to crontab (daily at 2 AM)
sudo crontab -e
# Add this line:
0 2 * * * /usr/local/bin/backup-animefeverzone.sh >> /var/log/backup-animefeverzone.log 2>&1
```

### 3. Setup Queue Workers (If Using Queues)

```bash
# Add queue worker to supervisor configuration
# This is already handled in the Docker container if you're using Octane
# Verify it's running:
docker compose -f docker-compose.prod.yml exec app php artisan queue:work --once
```

### 4. Setup Scheduled Tasks (Cron)

```bash
# Add Laravel scheduler to host cron
sudo crontab -e

# Add this line:
* * * * * cd /var/www/animefeverzone && docker compose -f docker-compose.prod.yml exec -T app php artisan schedule:run >> /dev/null 2>&1
```

---

## 🔧 Maintenance

### Updating Your Application

```bash
# Navigate to project directory
cd /var/www/animefeverzone

# Pull latest changes
git pull origin main

# Rebuild containers
docker compose -f docker-compose.prod.yml build

# Stop and restart services
docker compose -f docker-compose.prod.yml down
docker compose -f docker-compose.prod.yml up -d

# Run migrations
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force

# Clear caches
docker compose -f docker-compose.prod.yml exec app php artisan optimize:clear
docker compose -f docker-compose.prod.yml exec app php artisan optimize
```

### Viewing Logs

```bash
# All containers
docker compose -f docker-compose.prod.yml logs -f

# Specific container
docker compose -f docker-compose.prod.yml logs -f app
docker compose -f docker-compose.prod.yml logs -f nginx

# Application logs
docker compose -f docker-compose.prod.yml exec app tail -f storage/logs/laravel.log

# Octane logs
docker compose -f docker-compose.prod.yml exec app tail -f storage/logs/octane.log
```

### Restarting Services

```bash
# Restart all services
docker compose -f docker-compose.prod.yml restart

# Restart specific service
docker compose -f docker-compose.prod.yml restart app
docker compose -f docker-compose.prod.yml restart nginx

# Restart Octane without restarting container
docker compose -f docker-compose.prod.yml exec app php artisan octane:reload
```

### Database Backup & Restore

```bash
# Manual backup
docker compose -f docker-compose.prod.yml exec mysql mysqldump \
  -u root -p$DB_ROOT_PASSWORD anime_fever_zone \
  > backup_$(date +%Y%m%d).sql

# Restore from backup
docker compose -f docker-compose.prod.yml exec -T mysql mysql \
  -u root -p$DB_ROOT_PASSWORD anime_fever_zone \
  < backup_20240101.sql
```

---

## 🐛 Troubleshooting

### Container Won't Start

```bash
# Check container status
docker compose -f docker-compose.prod.yml ps -a

# Check logs for errors
docker compose -f docker-compose.prod.yml logs app

# Check if ports are in use
sudo netstat -tulpn | grep -E ':(80|443|3306|6379)'

# Rebuild container
docker compose -f docker-compose.prod.yml build --no-cache app
```

### SSL Certificate Issues

```bash
# Check certificate status
docker compose -f docker-compose.prod.yml exec certbot certbot certificates

# Manually renew certificate
docker compose -f docker-compose.prod.yml exec certbot certbot renew --force-renewal

# Check nginx configuration
docker compose -f docker-compose.prod.yml exec nginx nginx -t

# View certbot logs
docker compose -f docker-compose.prod.yml logs certbot
```

### Permission Issues

```bash
# Fix storage permissions
docker compose -f docker-compose.prod.yml exec app chmod -R 775 storage bootstrap/cache
docker compose -f docker-compose.prod.yml exec app chown -R www-data:www-data storage bootstrap/cache
```

### Database Connection Issues

```bash
# Check if MySQL is running
docker compose -f docker-compose.prod.yml exec mysql mysqladmin ping -h localhost

# Connect to MySQL
docker compose -f docker-compose.prod.yml exec mysql mysql -u root -p

# Check Laravel database connection
docker compose -f docker-compose.prod.yml exec app php artisan tinker
# Then run: DB::connection()->getPdo();
```

### High Memory Usage

```bash
# Check container resource usage
docker stats

# Restart Octane to free memory
docker compose -f docker-compose.prod.yml exec app php artisan octane:reload

# Check swap usage
free -h

# Adjust Octane workers in docker/supervisor/octane.prod.conf if needed
```

### Site Not Accessible

```bash
# Check firewall
sudo ufw status

# Check DNS resolution
nslookup animefeverzone.com

# Check if nginx is listening
docker compose -f docker-compose.prod.yml exec nginx netstat -tulpn

# Check nginx access logs
docker compose -f docker-compose.prod.yml logs nginx | tail -100

# Test direct connection to app container
docker compose -f docker-compose.prod.yml exec nginx curl http://app:8000
```

---

## 📊 Monitoring

### Setup Basic Monitoring

```bash
# Install htop for system monitoring
sudo apt install htop -y

# Check container resource usage
docker stats

# Monitor logs in real-time
docker compose -f docker-compose.prod.yml logs -f --tail=100
```

### Useful Commands

```bash
# Check disk usage
df -h
docker system df

# Clean up unused Docker resources
docker system prune -a --volumes

# Check container health
docker compose -f docker-compose.prod.yml ps

# View container resource limits
docker inspect animefeverzone_app_prod | grep -A 10 Resources
```

---

## 🎯 Performance Optimization

### 1. Enable Opcache Preloading (Optional)

Already configured in the Dockerfile, but you can verify:
```bash
docker compose -f docker-compose.prod.yml exec app php -i | grep opcache
```

### 2. Optimize Images

If serving images, consider setting up a CDN or using image optimization:
```bash
# Install image optimization tools in the container if needed
# Already included in Dockerfile: libpng-dev libjpeg-dev libfreetype6-dev
```

### 3. Monitor Application Performance

```bash
# Check Octane metrics
docker compose -f docker-compose.prod.yml exec app php artisan octane:status

# View slow query logs (if enabled)
docker compose -f docker-compose.prod.yml exec mysql mysql -u root -p \
  -e "SELECT * FROM mysql.slow_log ORDER BY start_time DESC LIMIT 10;"
```

---

## 📞 Support

If you encounter issues:
1. Check the logs: `docker compose -f docker-compose.prod.yml logs`
2. Review this troubleshooting guide
3. Check Laravel documentation: https://laravel.com/docs
4. Check Octane documentation: https://laravel.com/docs/octane

---

## 🔄 Quick Reference Commands

```bash
# Start all services
docker compose -f docker-compose.prod.yml up -d

# Stop all services
docker compose -f docker-compose.prod.yml down

# Restart services
docker compose -f docker-compose.prod.yml restart

# View logs
docker compose -f docker-compose.prod.yml logs -f

# Execute artisan command
docker compose -f docker-compose.prod.yml exec app php artisan [command]

# Access container shell
docker compose -f docker-compose.prod.yml exec app bash

# Rebuild containers
docker compose -f docker-compose.prod.yml build --no-cache

# Update application
git pull && docker compose -f docker-compose.prod.yml up -d --build
```

---

## ✅ Deployment Checklist

- [ ] VPS setup with Ubuntu 22.04/24.04
- [ ] Docker and Docker Compose installed
- [ ] Firewall configured (ports 80, 443 open)
- [ ] Domain DNS pointing to VPS IP
- [ ] Repository cloned to `/var/www/animefeverzone`
- [ ] `.env.prod` file created with strong passwords
- [ ] APP_KEY generated
- [ ] Domain name updated in config files
- [ ] SSL certificates obtained via `init-letsencrypt.sh`
- [ ] All containers running (`docker compose ps`)
- [ ] Database migrated
- [ ] Site accessible via HTTPS
- [ ] Backups configured
- [ ] Monitoring setup
- [ ] Logs rotation configured

---

**Last Updated:** October 2025
**Version:** 1.0.0

