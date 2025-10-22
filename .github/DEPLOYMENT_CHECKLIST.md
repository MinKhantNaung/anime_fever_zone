# Deployment Checklist

Use this checklist before and after deploying to production.

---

## 🔧 Pre-Deployment

### **Code Quality**

- [ ] All tests passing locally
  ```bash
  php artisan test
  ```

- [ ] No linting errors
  ```bash
  composer audit
  ```

- [ ] Frontend builds successfully
  ```bash
  npm run build
  ```

- [ ] No console errors in browser

- [ ] Git status clean
  ```bash
  git status
  ```

### **Environment**

- [ ] `.env.production` up to date on VPS

- [ ] All required secrets set in GitHub
  - [ ] `SSH_PRIVATE_KEY`
  - [ ] `VPS_HOST`
  - [ ] `VPS_USER`
  - [ ] `VPS_PATH`

- [ ] Database backup recent (if doing migrations)
  ```bash
  ssh your-user@vps-ip "cd /path/to/project && mysqldump -u user -p database > backup.sql"
  ```

### **Dependencies**

- [ ] `composer.lock` committed

- [ ] `package-lock.json` committed

- [ ] No breaking dependency changes

---

## 🚀 Deployment

### **Method 1: Automatic (Recommended)**

```bash
# Merge to main branch
git checkout main
git merge develop
git push origin main
```

✅ GitHub Actions will automatically deploy!

### **Method 2: Manual Trigger**

1. Go to: GitHub → Actions → Deploy to Production
2. Click: "Run workflow"
3. Select: `main` branch
4. Click: "Run workflow"

### **Method 3: Manual Deployment**

```bash
# SSH to VPS
ssh your-user@your-vps-ip
cd /home/minkhantnaungroot/projects/animefeverzone

# Pull latest
git pull origin main

# Install dependencies
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
npm ci --omit=dev

# Build assets
npm run build

# Migrate database
php artisan migrate --force

# Optimize
php artisan app:optimize-all

# Restart Octane
sudo systemctl restart octane
# OR
php artisan octane:reload
```

---

## ✅ Post-Deployment

### **Immediate Checks (0-5 minutes)**

- [ ] Site is accessible
  ```bash
  curl -I https://animefeverzone.com
  ```

- [ ] No 500 errors on homepage

- [ ] Login/Logout works

- [ ] Database queries working

- [ ] Assets loading (CSS, JS, images)

- [ ] HTTPS certificate valid (green padlock)

### **Detailed Checks (5-15 minutes)**

- [ ] All major features tested:
  - [ ] User registration
  - [ ] User login
  - [ ] Profile updates
  - [ ] [Add your critical features here]

- [ ] Admin panel accessible

- [ ] Search functionality working

- [ ] Image uploads working

- [ ] Email sending working (if applicable)

### **Monitoring (15-60 minutes)**

- [ ] No errors in Laravel logs
  ```bash
  ssh your-user@vps-ip "tail -f /path/to/project/storage/logs/laravel.log"
  ```

- [ ] No errors in Octane logs
  ```bash
  ssh your-user@vps-ip "sudo journalctl -u octane -f"
  ```

- [ ] Octane is running
  ```bash
  ssh your-user@vps-ip "sudo systemctl status octane"
  ```

- [ ] Response times acceptable
  ```bash
  curl -w "@curl-format.txt" -o /dev/null -s https://animefeverzone.com
  ```

- [ ] Check server resources
  ```bash
  ssh your-user@vps-ip "htop"
  # Or
  ssh your-user@vps-ip "free -h && df -h"
  ```

---

## 🐛 Rollback Plan

If something goes wrong:

### **Quick Rollback (Last Known Good Commit)**

```bash
# SSH to VPS
ssh your-user@your-vps-ip
cd /home/minkhantnaungroot/projects/animefeverzone

# Check recent commits
git log -5 --oneline

# Rollback to previous commit
git reset --hard HEAD~1
# Or specific commit
git reset --hard <commit-hash>

# Reinstall dependencies
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
npm ci --omit=dev
npm run build

# Migrate down if needed
php artisan migrate:rollback --step=1

# Optimize
php artisan app:optimize-all

# Restart
sudo systemctl restart octane
```

### **Full Rollback (Restore from Backup)**

```bash
# Restore database
mysql -u user -p database < backup.sql

# Restore .env
cp .env.backup .env

# Clear caches
php artisan config:clear
php artisan cache:clear

# Restart
sudo systemctl restart octane
```

---

## 📊 Deployment History

Keep track of your deployments:

| Date | Commit | Deployed By | Status | Notes |
|------|--------|-------------|--------|-------|
| 2025-10-22 | abc1234 | GitHub Actions | ✅ Success | Initial CI/CD setup |
| | | | | |

---

## 🆘 Emergency Contacts

**If deployment fails:**

1. Check GitHub Actions logs
2. Check Laravel logs on VPS
3. Check Docker logs
4. SSH to VPS and investigate

**Quick disable site (if critical issue):**

```bash
ssh your-user@vps-ip
cd /path/to/project
php artisan down --message="Under maintenance, back soon!"
```

**Quick enable site:**

```bash
php artisan up
```

---

## 📝 Notes for Next Deployment

Document any issues or improvements for next time:

```
[Add notes here]
```

---

## ✅ Deployment Complete!

Once all checks pass, update this checklist and commit any notes for future reference.

```bash
# Document successful deployment
git tag -a v1.0.0 -m "Production deployment - [date]"
git push origin v1.0.0
```

---

**Last Updated:** 2025-10-22  
**Next Review:** After each deployment

