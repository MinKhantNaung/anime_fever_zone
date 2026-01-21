# Permission Management Strategy

Explains how file ownership and permissions work in the deployment workflow.

---

## 🎯 Overview

Your production setup uses **two different users** for different purposes:

| User | Purpose | What They Do |
|------|---------|-------------|
| `your-username` | Deployment | Run git, composer, npm commands |
| `www-data` | Application | Serve web app, run Octane |

**Why?** Security and proper separation of concerns.

---

## 🔒 Permission Strategy

### **During Normal Operation:**

```bash
# Ownership: www-data
sudo chown -R www-data:www-data /home/your-username/projects/animefeverzone

# Directories: 755 (rwxr-xr-x)
sudo find . -type d -exec chmod 755 {} \;

# Files: 644 (rw-r--r--)
sudo find . -type f -exec chmod 644 {} \;

# Artisan: 755 (rwxr-xr-x)
sudo chmod +x artisan

# Writable directories: 775 (rwxrwxr-x)
sudo chmod -R 775 storage bootstrap/cache
```

**Result:**
- ✅ Nginx (www-data) can read files
- ✅ Octane (www-data) can write to storage/cache
- ✅ Application runs securely
- ❌ Regular user can't modify files (security!)

---

### **During Deployment:**

```bash
# 1. Change to deploy user
sudo chown -R $USER:$USER .

# 2. Deploy operations
git pull
composer install
npm run build

# 3. Change back to www-data
sudo chown -R www-data:www-data .

# 4. Set proper permissions
# (same as above)
```

**Result:**
- ✅ Deploy user can run git/composer/npm
- ✅ Build tools work properly
- ✅ After deployment, www-data owns everything again
- ✅ Application continues to run securely

---

## 📋 Deployment Workflow Steps

### **Step 1: Preparation**
```bash
# Application is running as www-data
ls -la  # Shows: www-data www-data
```

### **Step 2: Enable Maintenance Mode**
```bash
php artisan down
# Users see maintenance page
```

### **Step 3: Change Ownership to Deploy User**
```bash
sudo chown -R $USER:$USER .
ls -la  # Shows: your-username your-username
```

**Why?**
- Git needs write permissions
- Composer needs to update vendor/
- npm needs to write node_modules/ and public/

### **Step 4: Pull Latest Code**
```bash
git fetch origin main
git reset --hard origin/main
git pull origin main
```

### **Step 5: Install Dependencies**
```bash
composer install --no-dev
npm ci --omit=dev
```

### **Step 6: Build Assets**
```bash
npm run build
# Writes to public/build/
```

### **Step 7: Change Ownership Back to www-data**
```bash
sudo chown -R www-data:www-data .
ls -la  # Shows: www-data www-data
```

**Why?**
- Nginx runs as www-data
- Octane runs as www-data
- PHP-FPM (if used) runs as www-data
- Security: application files owned by web user

### **Step 8: Set Proper Permissions**
```bash
# Directories readable/executable
sudo find . -type d -exec chmod 755 {} \;

# Files readable
sudo find . -type f -exec chmod 644 {} \;

# Artisan executable
sudo chmod +x artisan

# Storage writable by www-data
sudo chmod -R 775 storage bootstrap/cache
```

### **Step 9: Optimize Application**
```bash
php artisan migrate --force
php artisan app:optimize-all
```

### **Step 10: Disable Maintenance Mode**
```bash
php artisan up
```

### **Step 11: Reload Octane as www-data**
```bash
sudo -u www-data php artisan octane:reload
```

**Why run as www-data?**
- Octane workers run as www-data
- Reload command needs to signal those workers
- Must be same user for proper communication

---

## 🔍 Understanding Permissions

### **Permission Numbers:**

| Number | Symbolic | Meaning | Used For |
|--------|----------|---------|----------|
| `755` | `rwxr-xr-x` | Owner: read+write+execute<br>Group: read+execute<br>Others: read+execute | Directories, executables |
| `775` | `rwxrwxr-x` | Owner: read+write+execute<br>Group: read+write+execute<br>Others: read+execute | Writable directories |
| `644` | `rw-r--r--` | Owner: read+write<br>Group: read<br>Others: read | Regular files |

### **Breakdown:**

**755 (Directories):**
```
7 = rwx (Owner: read, write, execute)
5 = r-x (Group: read, execute)
5 = r-x (Others: read, execute)
```

**775 (storage/bootstrap/cache):**
```
7 = rwx (Owner: read, write, execute)
7 = rwx (Group: read, write, execute)  ← www-data can write
5 = r-x (Others: read, execute)
```

**644 (Files):**
```
6 = rw- (Owner: read, write)
4 = r-- (Group: read)
4 = r-- (Others: read)
```

---

## ⚙️ CI/CD Workflow Implementation

The deployment workflow now matches your manual process:

```yaml
# Change ownership for deployment
sudo chown -R $USER:$USER .

# Deploy operations
git pull
composer install
npm run build

# Restore www-data ownership
sudo chown -R www-data:www-data .

# Set permissions
sudo find . -type d -exec chmod 755 {} \;
sudo find . -type f -exec chmod 644 {} \;
sudo chmod +x artisan
sudo chmod -R 775 storage bootstrap/cache

# Reload as www-data
sudo -u www-data php artisan octane:reload
```

---

## 🔐 Security Benefits

### **1. Principle of Least Privilege**
- Application runs with minimal permissions
- Deploy user can't modify files during runtime
- Separation of deployment and runtime

### **2. Protection Against Attacks**
- If application is compromised:
  - ❌ Attacker can't modify core files (owned by www-data, read-only in runtime)
  - ❌ Attacker can't install malicious packages
  - ✅ Can only affect runtime data (storage/)

### **3. Audit Trail**
- Clear separation: who deployed vs who's running
- Easy to track changes via git log
- Deployment changes require sudo (logged)

---

## 🛠️ Common Operations

### **View Current Ownership**
```bash
ls -la /home/your-username/projects/animefeverzone
```

### **Check Who Owns a File**
```bash
stat -c '%U:%G' /home/your-username/projects/animefeverzone/artisan
# Output: www-data:www-data
```

### **Check Octane Process Owner**
```bash
ps aux | grep octane
# Should show www-data
```

### **Fix Permissions After Manual Changes**
```bash
cd /home/your-username/projects/animefeverzone

# Restore www-data ownership
sudo chown -R www-data:www-data .

# Set proper permissions
sudo find . -type d -exec chmod 755 {} \;
sudo find . -type f -exec chmod 644 {} \;
sudo chmod +x artisan
sudo chmod -R 775 storage bootstrap/cache
```

---

## ⚠️ Common Issues

### **Issue 1: "Permission denied" during deployment**

**Problem:**
```
git pull
error: cannot open .git/FETCH_HEAD: Permission denied
```

**Cause:** Files owned by www-data, trying to pull as deploy user

**Solution:**
```bash
sudo chown -R $USER:$USER .
git pull
sudo chown -R www-data:www-data .
```

---

### **Issue 2: "Cannot write to storage/"**

**Problem:**
```
failed to open stream: Permission denied (storage/logs/laravel.log)
```

**Cause:** storage/ not writable by www-data

**Solution:**
```bash
sudo chown -R www-data:www-data storage
sudo chmod -R 775 storage
```

---

### **Issue 3: "Artisan not executable"**

**Problem:**
```
bash: ./artisan: Permission denied
```

**Cause:** artisan file not executable

**Solution:**
```bash
sudo chmod +x artisan
```

---

### **Issue 4: "Cannot reload Octane"**

**Problem:**
```
php artisan octane:reload
Octane workers could not be reloaded
```

**Cause:** Running as wrong user (deploy user instead of www-data)

**Solution:**
```bash
# Run as www-data
sudo -u www-data php artisan octane:reload
```

---

## 📊 Permission Checklist

Before deployment:
- [ ] Application owned by www-data
- [ ] Directories: 755
- [ ] Files: 644
- [ ] Artisan: 755 (executable)
- [ ] storage/: 775
- [ ] bootstrap/cache/: 775

During deployment:
- [ ] Change to deploy user
- [ ] Pull/build as deploy user
- [ ] Change back to www-data
- [ ] Set proper permissions
- [ ] Reload as www-data

After deployment:
- [ ] Verify ownership: `ls -la`
- [ ] Verify Octane running: `ps aux | grep octane`
- [ ] Verify application works
- [ ] Check logs writable

---

## 🎓 Best Practices

### **1. Always Deploy in Correct Order**
```bash
# ✅ Correct
sudo chown -R $USER:$USER .
git pull
npm run build
sudo chown -R www-data:www-data .

# ❌ Wrong
git pull  # Permission denied!
sudo chown -R $USER:$USER .
npm run build
# Forgot to change back to www-data! App breaks!
```

### **2. Use Consistent User**
```bash
# ✅ Correct
sudo -u www-data php artisan octane:reload

# ❌ Wrong
php artisan octane:reload  # Running as deploy user
```

### **3. Always Set Permissions After Ownership Change**
```bash
# ✅ Correct
sudo chown -R www-data:www-data .
sudo find . -type d -exec chmod 755 {} \;
sudo find . -type f -exec chmod 644 {} \;

# ❌ Wrong
sudo chown -R www-data:www-data .
# Forgot permissions! May have wrong perms from before!
```

---

## 📝 Summary

**Your permission strategy:**

1. **Runtime:** www-data owns everything
2. **Deployment:** Temporarily change to deploy user
3. **After deployment:** Change back to www-data
4. **Always:** Set proper permissions (755/644/775)
5. **Octane commands:** Run as www-data

**Benefits:**
- ✅ Secure
- ✅ Proper separation
- ✅ Works with nginx/Octane
- ✅ Allows safe deployment
- ✅ CI/CD compatible

**The CI/CD workflow now implements this exactly!** 🎉

---

**Last Updated:** 2025-10-22

