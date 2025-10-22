# CI/CD Setup Guide

Complete guide for the Continuous Integration and Continuous Deployment workflows for Anime Fever Zone.

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Workflows](#workflows)
3. [Setup Requirements](#setup-requirements)
4. [GitHub Secrets Configuration](#github-secrets-configuration)
5. [Local Testing](#local-testing)
6. [Troubleshooting](#troubleshooting)

---

## 🎯 Overview

This project uses **GitHub Actions** for CI/CD **WITHOUT Docker**. The workflows run directly on Ubuntu runners with native PHP and Node.js. Deployment targets traditional VPS setups with systemd services.

**Note:** This CI/CD setup is for **manual/native deployments only**. For Docker-based production, see `docker-compose.prod.yml` instead.

### **What Gets Automated:**

- ✅ **Automated Testing** - Every push/PR
- ✅ **Code Quality Checks** - PHPStan, Psalm, PHP CS Fixer
- ✅ **Frontend Building** - npm build verification
- ✅ **Security Scanning** - Composer audit
- ✅ **Automated Deployment** - Push to production on main branch

---

## 🔄 Workflows

### **1. CI - Laravel Tests** (`.github/workflows/ci.yml`)

**Triggers:**
- Push to `main` or `develop` branches
- Pull requests to `main` or `develop`

**What it does:**
1. Sets up PHP 8.3 with all required extensions
2. Starts Redis service
3. Uses SQLite `:memory:` for database (fast!)
4. Installs Composer dependencies
5. Installs npm dependencies
6. Builds frontend assets
7. Runs database migrations
8. Executes PHPUnit tests in parallel
9. Uploads coverage reports to Codecov

**Duration:** ~3-5 minutes (faster with SQLite!)

---

### **2. Code Quality** (`.github/workflows/code-quality.yml`)

**Triggers:**
- Push to `main` or `develop` branches
- Pull requests to `main` or `develop`

**What it does:**

**PHP Quality:**
1. PHP CS Fixer - Code style checking
2. PHPStan - Static analysis
3. Psalm - Static analysis
4. Composer audit - Security vulnerabilities

**JavaScript Quality:**
1. ESLint - Code linting
2. Prettier - Code formatting
3. Build verification - Ensures assets compile

**Duration:** ~3-4 minutes

**Note:** Quality checks continue on error (won't block PRs)

---

### **3. Deploy to Production** (`.github/workflows/deploy-production.yml`)

**Triggers:**
- Push to `main` branch
- Manual trigger (workflow_dispatch)

**What it does:**
1. Connects to VPS via SSH
2. Pulls latest code from Git
3. Backs up .env file
4. Enables maintenance mode
5. Installs dependencies (composer + npm)
6. Builds frontend assets
7. Runs database migrations
8. Clears caches
9. Runs `php artisan app:optimize-all`
10. Restarts services (Docker or systemd)
11. Disables maintenance mode

**Duration:** ~3-5 minutes

**Deployment Flow:**
```
main branch push
     ↓
GitHub Actions triggered
     ↓
SSH to VPS
     ↓
Pull latest code
     ↓
Install dependencies
     ↓
Build assets
     ↓
Migrate database
     ↓
Optimize Laravel
     ↓
Restart services
     ↓
✅ Live!
```

---

## 🔧 Setup Requirements

### **1. GitHub Repository Settings**

Enable GitHub Actions:
```
Settings → Actions → General → Allow all actions
```

### **2. SSH Key for Deployment**

Generate SSH key pair on your local machine:

```bash
# Generate SSH key
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/github_actions

# Copy public key to VPS
ssh-copy-id -i ~/.ssh/github_actions.pub your-user@your-vps-ip

# Test connection
ssh -i ~/.ssh/github_actions your-user@your-vps-ip
```

---

## 🔐 GitHub Secrets Configuration

Go to: `Repository → Settings → Secrets and variables → Actions → New repository secret`

### **Required Secrets:**

| Secret Name | Description | Example Value |
|------------|-------------|---------------|
| `SSH_PRIVATE_KEY` | Private key for VPS access | Contents of `~/.ssh/github_actions` |
| `VPS_HOST` | VPS hostname or IP | `123.45.67.89` or `animefeverzone.com` |
| `VPS_USER` | SSH username | `minkhantnaungroot` |
| `VPS_PATH` | Project path on VPS | `/home/minkhantnaungroot/projects/animefeverzone` |

### **Optional Secrets:**

| Secret Name | Description | Example Value |
|------------|-------------|---------------|
| `CODECOV_TOKEN` | Codecov upload token | Get from codecov.io |
| `SLACK_WEBHOOK_URL` | Slack notifications | `https://hooks.slack.com/...` |
| `DISCORD_WEBHOOK_URL` | Discord notifications | `https://discord.com/api/webhooks/...` |

---

## 📝 How to Add Secrets

### **Step 1: Copy SSH Private Key**

```bash
# On your local machine
cat ~/.ssh/github_actions

# Copy the entire output, including:
# -----BEGIN OPENSSH PRIVATE KEY-----
# ...key content...
# -----END OPENSSH PRIVATE KEY-----
```

### **Step 2: Add to GitHub**

1. Go to: `https://github.com/YOUR_USERNAME/animefeverzone/settings/secrets/actions`
2. Click: **New repository secret**
3. Name: `SSH_PRIVATE_KEY`
4. Value: Paste the entire private key
5. Click: **Add secret**

### **Step 3: Add Other Secrets**

Repeat for:
- `VPS_HOST` → `your-vps-ip`
- `VPS_USER` → `minkhantnaungroot`
- `VPS_PATH` → `/home/minkhantnaungroot/projects/animefeverzone`

---

## 🧪 Local Testing

### **Test CI Workflow Locally (Using Act)**

```bash
# Install act (GitHub Actions runner)
# macOS
brew install act

# Linux
curl https://raw.githubusercontent.com/nektos/act/master/install.sh | sudo bash

# Run tests locally
act -j tests

# Run code quality checks
act -j code-quality

# Run specific workflow
act -W .github/workflows/ci.yml
```

### **Test Deployment Script Locally**

```bash
# SSH to your VPS
ssh your-user@your-vps-ip

# Navigate to project
cd /home/minkhantnaungroot/projects/animefeverzone

# Manually run deployment steps
git pull origin main
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
npm ci --omit=dev
npm run build
php artisan migrate --force
php artisan app:optimize-all

# Restart services
docker compose -f docker-compose.prod.yml restart app
# OR
sudo systemctl restart octane
```

---

## 🔍 Monitoring CI/CD

### **View Workflow Runs**

```
GitHub Repository → Actions tab
```

### **View Logs**

1. Click on workflow run
2. Click on job name
3. Expand steps to see detailed logs

### **Check Deployment Status**

```bash
# SSH to VPS
ssh your-user@your-vps-ip

# Check last deployment
cd /home/minkhantnaungroot/projects/animefeverzone
git log -1

# Check services
docker compose -f docker-compose.prod.yml ps
# OR
sudo systemctl status octane
```

---

## 🐛 Troubleshooting

### **Issue 1: Tests Failing**

**Symptoms:**
- CI workflow fails on "Run tests" step

**Solutions:**
```bash
# Run tests locally first
php artisan test

# Check .env.example has all required variables
cat .env.example

# Ensure database migrations are working
php artisan migrate:fresh
```

---

### **Issue 2: SSH Connection Failed**

**Symptoms:**
- Deployment fails with "Permission denied (publickey)"

**Solutions:**
```bash
# Verify SSH key is correct
cat ~/.ssh/github_actions.pub

# Ensure key is added to VPS
ssh your-user@your-vps-ip "cat ~/.ssh/authorized_keys"

# Test connection
ssh -i ~/.ssh/github_actions your-user@your-vps-ip
```

---

### **Issue 3: Build Fails**

**Symptoms:**
- "npm run build" fails in CI

**Solutions:**
```bash
# Test build locally
npm ci
npm run build

# Check for missing dependencies
npm install

# Verify vite.config.js is correct
cat vite.config.js
```

---

### **Issue 4: Deployment Fails**

**Symptoms:**
- Deployment workflow succeeds but site is down

**Solutions:**
```bash
# SSH to VPS and check logs
ssh your-user@your-vps-ip
cd /home/minkhantnaungroot/projects/animefeverzone

# Check Octane logs
sudo journalctl -u octane -f

# Check Laravel logs
tail -f storage/logs/laravel.log

# Verify .env file
cat .env

# Manually restart services
sudo systemctl restart octane
php artisan config:clear
php artisan cache:clear
```

---

### **Issue 5: Maintenance Mode Stuck**

**Symptoms:**
- Site shows "503 Service Unavailable" after deployment

**Solutions:**
```bash
# SSH to VPS
ssh your-user@your-vps-ip
cd /home/minkhantnaungroot/projects/animefeverzone

# Disable maintenance mode
php artisan up

# Or force remove maintenance file
rm storage/framework/down
```

---

## 🚀 Deployment Process

### **Automatic Deployment**

1. Make changes locally
2. Commit and push to `main` branch:
   ```bash
   git add .
   git commit -m "Your changes"
   git push origin main
   ```
3. GitHub Actions automatically deploys
4. Check progress: `GitHub → Actions`
5. Site updates in ~3-5 minutes

### **Manual Deployment**

1. Go to: `GitHub → Actions → Deploy to Production`
2. Click: "Run workflow"
3. Select branch: `main`
4. Click: "Run workflow"

---

## 📊 Workflow Status Badges

Add these to your README.md:

```markdown
![CI Tests](https://github.com/YOUR_USERNAME/animefeverzone/workflows/CI%20-%20Laravel%20Tests/badge.svg)
![Code Quality](https://github.com/YOUR_USERNAME/animefeverzone/workflows/Code%20Quality/badge.svg)
![Deploy](https://github.com/YOUR_USERNAME/animefeverzone/workflows/Deploy%20to%20Production/badge.svg)
```

---

## 📈 Best Practices

### **1. Branch Protection**

Protect `main` branch:
```
Settings → Branches → Add rule
- Branch name pattern: main
- Require status checks to pass
- Require pull request reviews
```

### **2. Environment-Specific .env**

```bash
# Development
cp .env.example .env

# Production (on VPS)
cp .env.production .env
```

### **3. Database Backups**

Before deployment:
```bash
# Automatic backup in deployment workflow
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
```

### **4. Zero-Downtime Deployment**

For critical sites:
1. Use Laravel Vapor or Forge
2. Or implement blue-green deployment
3. Or use load balancer with multiple servers

---

## 🎯 Next Steps

### **Optional Enhancements:**

1. **Add PHPStan/Psalm configs:**
   ```bash
   composer require --dev phpstan/phpstan
   php artisan vendor:publish --provider="Nunomaduro\Larastan\LarastanServiceProvider"
   ```

2. **Add code coverage reporting:**
   ```bash
   # Sign up at codecov.io
   # Add CODECOV_TOKEN to GitHub secrets
   ```

3. **Add Slack/Discord notifications:**
   ```yaml
   # Add to deploy-production.yml
   - name: Notify Slack
     uses: 8398a7/action-slack@v3
     with:
       status: ${{ job.status }}
       webhook_url: ${{ secrets.SLACK_WEBHOOK_URL }}
   ```

4. **Add staging environment:**
   - Create `deploy-staging.yml`
   - Deploy `develop` branch to staging server

---

## ✅ Checklist

### **Initial Setup:**

- [ ] SSH key generated
- [ ] SSH key added to VPS
- [ ] GitHub secrets configured
- [ ] Test SSH connection
- [ ] Workflows enabled in GitHub
- [ ] First manual deployment tested

### **After Each Deployment:**

- [ ] Check GitHub Actions logs
- [ ] Verify site is accessible
- [ ] Check for errors in Laravel logs
- [ ] Test critical features
- [ ] Monitor performance

---

## 📚 Additional Resources

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Laravel Deployment Guide](https://laravel.com/docs/deployment)
- [GitHub Actions for Laravel](https://github.com/shivammathur/setup-php)
- [Act - Local GitHub Actions](https://github.com/nektos/act)

---

## 🆘 Support

If you encounter issues:

1. Check workflow logs in GitHub Actions
2. SSH to VPS and check Laravel logs
3. Review this documentation
4. Check Laravel documentation
5. Review GitHub Actions documentation

---

**Your CI/CD is now configured! Every push to `main` will automatically deploy to production.** 🚀

