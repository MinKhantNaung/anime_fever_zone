# Quick Start - CI/CD (No Docker)

Get your CI/CD up and running in 5 minutes!

**Note:** This CI/CD setup is for **traditional VPS deployments without Docker**. It deploys to servers running Laravel Octane with systemd.

---

## ⚡ Quick Setup (5 Minutes)

### **Step 1: Generate SSH Key (2 minutes)**

```bash
# On your local machine
ssh-keygen -t ed25519 -C "github-actions" -f ~/.ssh/github_actions

# Copy public key to VPS
ssh-copy-id -i ~/.ssh/github_actions.pub your-user@your-vps-ip

# Test connection
ssh -i ~/.ssh/github_actions your-user@your-vps-ip
```

✅ Should connect without password!

---

### **Step 2: Add GitHub Secrets (2 minutes)**

Go to: **GitHub Repository → Settings → Secrets and variables → Actions**

Click **"New repository secret"** and add 4 secrets:

```
Name: SSH_PRIVATE_KEY
Value: [Paste entire contents of ~/.ssh/github_actions - the PRIVATE key]

Name: VPS_HOST
Value: your-vps-ip-address (e.g., 123.45.67.89)

Name: VPS_USER
Value: your-username

Name: VPS_PATH
Value: /home/your-username/projects/animefeverzone
```

**Need detailed help?** See `../.github/GITHUB_SECRETS_SETUP.md` for step-by-step screenshots.

---

### **Step 3: Test It! (1 minute)**

```bash
# Push to develop (runs tests)
git checkout develop
git commit --allow-empty -m "Test CI"
git push origin develop

# Check GitHub → Actions tab
# Should see workflow running ✅

# Push to main (deploys to production)
git checkout main
git merge develop
git push origin main

# Check GitHub → Actions tab
# Should see deployment running 🚀
```

---

## 🎯 That's It!

Your CI/CD is now live!

- ✅ Every push to develop → Runs tests
- ✅ Every push to main → Deploys to production
- ✅ All automatic, no manual work

---

## 📋 Daily Workflow

```bash
# 1. Work on develop
git checkout develop

# 2. Make changes
# ... code ...

# 3. Push
git add .
git commit -m "Add feature"
git push origin develop
# ✅ Tests run automatically

# 4. When ready to deploy
git checkout main
git merge develop
git push origin main
# 🚀 Deploys automatically
```

---

## 🔍 Monitor Deployments

```
GitHub → Actions tab
```

Or use CLI:
```bash
gh run list
gh run watch
```

---

## 📚 Need More Info?

- **Complete Guide:** See `../../CI_CD_SETUP.md`
- **Summary:** See `../../CI_SETUP_SUMMARY.md`
- **Checklist:** See `../DEPLOYMENT_CHECKLIST.md`

---

## 🆘 Troubleshooting

**Tests failing?**
```bash
php artisan test  # Run locally first
```

**Deployment failing?**
```bash
# Check SSH works
ssh -i ~/.ssh/github_actions your-user@your-vps-ip
```

**Site down after deploy?**
```bash
# SSH and check logs
ssh your-user@your-vps-ip
cd /home/your-username/projects/animefeverzone
sudo journalctl -u octane -f
tail -f storage/logs/laravel.log
```

---

**Done! Now deploy with confidence! 🚀**

