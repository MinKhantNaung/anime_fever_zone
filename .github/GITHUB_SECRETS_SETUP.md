# GitHub Secrets Setup Guide

Step-by-step guide to add credentials for CI/CD workflows.

---

## 📋 Overview

GitHub Secrets are encrypted environment variables used in your workflows. They keep sensitive data (like SSH keys, passwords, API tokens) secure.

**Required for:** Deployment workflow to work  
**Time needed:** 10 minutes  
**One-time setup:** Yes

---

## 🔐 Required Secrets

For the deployment workflow to work, you need **4 secrets**:

| Secret Name | Description | Example |
|-------------|-------------|---------|
| `SSH_PRIVATE_KEY` | Private SSH key for VPS access | Contents of private key file |
| `VPS_HOST` | VPS IP address or domain | `123.45.67.89` |
| `VPS_USER` | SSH username on VPS | `username` |
| `VPS_PATH` | Full path to project on VPS | `/home/username/projects/animefeverzone` |

---

## 🚀 Step-by-Step Setup

### **Step 1: Generate SSH Key Pair**

On your **local machine** (not VPS):

```bash
# Generate a new SSH key specifically for GitHub Actions
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/github_actions

# You'll see:
# Generating public/private ed25519 key pair.
# Enter passphrase (empty for no passphrase): [Press Enter - leave empty]
# Enter same passphrase again: [Press Enter]
```

**Important:** Don't set a passphrase (leave empty)!

This creates two files:
- `~/.ssh/github_actions` → **Private key** (keep secret!)
- `~/.ssh/github_actions.pub` → **Public key** (can share)

---

### **Step 2: Copy Public Key to VPS**

```bash
# Copy public key to VPS
ssh-copy-id -i ~/.ssh/github_actions.pub your-user@your-vps-ip

# Example:
ssh-copy-id -i ~/.ssh/github_actions.pub your-username@123.45.67.89

# You'll be asked for your VPS password
# Enter it, and the key will be added
```

**Verify it works:**
```bash
# Test SSH connection (should work without password)
ssh -i ~/.ssh/github_actions your-user@your-vps-ip

# If it asks for password, something went wrong
# If it connects directly, you're good! ✅
```

**Exit the SSH session:**
```bash
exit
```

---

### **Step 3: Get Your Private Key Content**

```bash
# Display private key content
cat ~/.ssh/github_actions

# You'll see something like:
# -----BEGIN OPENSSH PRIVATE KEY-----
# b3BlbnNzaC1rZXktdjEAAAAABG5vbmUAAAAEbm9uZQAAAAAAAAABAAAAMwAAAAtz
# c2gtZWQyNTUxOQAAACBK... (many lines)
# ...more key content...
# -----END OPENSSH PRIVATE KEY-----
```

**Copy the ENTIRE output**, including:
- `-----BEGIN OPENSSH PRIVATE KEY-----`
- All the middle lines
- `-----END OPENSSH PRIVATE KEY-----`

---

### **Step 4: Add Secrets to GitHub**

#### **4.1 Navigate to GitHub Settings**

1. Go to your GitHub repository
2. Click **"Settings"** tab (top right)
3. In left sidebar, click **"Secrets and variables"**
4. Click **"Actions"**
5. You'll see "Actions secrets and variables" page

**URL format:**
```
https://github.com/YOUR_USERNAME/animefeverzone/settings/secrets/actions
```

---

#### **4.2 Add SSH_PRIVATE_KEY Secret**

1. Click **"New repository secret"** (green button)
2. **Name:** `SSH_PRIVATE_KEY`
3. **Secret:** Paste the entire private key content from Step 3
4. Click **"Add secret"**

**Screenshot guide:**
```
┌─────────────────────────────────────┐
│ Name *                              │
│ ┌─────────────────────────────────┐ │
│ │ SSH_PRIVATE_KEY                 │ │
│ └─────────────────────────────────┘ │
│                                     │
│ Secret *                            │
│ ┌─────────────────────────────────┐ │
│ │ -----BEGIN OPENSSH PRIVATE KEY--│ │
│ │ b3BlbnNzaC1rZXktdjEAAAAA...    │ │
│ │ ...                             │ │
│ │ -----END OPENSSH PRIVATE KEY----│ │
│ └─────────────────────────────────┘ │
│                                     │
│         [Add secret]                │
└─────────────────────────────────────┘
```

✅ Secret added!

---

#### **4.3 Add VPS_HOST Secret**

1. Click **"New repository secret"** again
2. **Name:** `VPS_HOST`
3. **Secret:** Your VPS IP address or domain
   ```
   Example: 123.45.67.89
   Or: animefeverzone.com
   ```
4. Click **"Add secret"**

✅ Secret added!

---

#### **4.4 Add VPS_USER Secret**

1. Click **"New repository secret"** again
2. **Name:** `VPS_USER`
3. **Secret:** Your SSH username
   ```
   Example: your-username
   ```
4. Click **"Add secret"**

✅ Secret added!

---

#### **4.5 Add VPS_PATH Secret**

1. Click **"New repository secret"** again
2. **Name:** `VPS_PATH`
3. **Secret:** Full path to your project on VPS
   ```
   Example: /home/your-username/projects/animefeverzone
   ```
4. Click **"Add secret"**

✅ Secret added!

---

### **Step 5: Verify All Secrets**

You should now see all 4 secrets in the list:

```
Repository secrets

SSH_PRIVATE_KEY      Updated X seconds ago      [Update] [Remove]
VPS_HOST            Updated X seconds ago      [Update] [Remove]
VPS_USER            Updated X seconds ago      [Update] [Remove]
VPS_PATH            Updated X seconds ago      [Update] [Remove]
```

**Note:** GitHub hides secret values for security. You can't view them after adding, only update or remove.

---

## ✅ Test Your Setup

### **Test 1: Manual SSH Test**

On your local machine:

```bash
# Test SSH connection with the key
ssh -i ~/.ssh/github_actions $VPS_USER@$VPS_HOST

# Replace with your values:
ssh -i ~/.ssh/github_actions your-username@123.45.67.89

# Should connect WITHOUT asking for password ✅
```

---

### **Test 2: Test Deployment Workflow**

```bash
# Make a small change
git checkout main
echo "# Test deployment" >> README.md
git add README.md
git commit -m "Test CI/CD deployment"
git push origin main
```

**Check the workflow:**
1. Go to: `https://github.com/YOUR_USERNAME/animefeverzone/actions`
2. You should see "Deploy to Production" running
3. Click on it to see live logs
4. If it succeeds: ✅ Everything works!
5. If it fails: Check logs for errors

---

## 🎯 Optional Secrets

### **For Code Coverage (Codecov)**

If you want code coverage reports:

1. Sign up at https://codecov.io
2. Link your GitHub repository
3. Get your upload token
4. Add as secret:
   - **Name:** `CODECOV_TOKEN`
   - **Secret:** Your Codecov token

## 🔍 Troubleshooting

### **Issue 1: "Permission denied (publickey)"**

**Problem:** GitHub Actions can't connect to VPS

**Solution:**
```bash
# Verify SSH key is on VPS
ssh your-user@vps-ip "cat ~/.ssh/authorized_keys"

# Should show the public key you added
# If not, add it again:
ssh-copy-id -i ~/.ssh/github_actions.pub your-user@vps-ip
```

---

### **Issue 2: "Host key verification failed"**

**Problem:** VPS host key not recognized

**Solution:** The workflow has this step to fix it:
```yaml
- name: Add VPS to known hosts
  run: |
    mkdir -p ~/.ssh
    ssh-keyscan -H ${{ secrets.VPS_HOST }} >> ~/.ssh/known_hosts
```

This runs automatically. If it still fails, check `VPS_HOST` secret is correct.

---

### **Issue 3: "No such file or directory"**

**Problem:** `VPS_PATH` is incorrect

**Solution:**
```bash
# SSH to VPS and check path
ssh your-user@vps-ip
pwd
cd /home/user-name/projects/animefeverzone
# If this works, use this path

# Update VPS_PATH secret in GitHub
```

---

### **Issue 4: "Secret not found"**

**Problem:** Secret name is misspelled

**Solution:** Secret names are case-sensitive!
- ✅ Correct: `SSH_PRIVATE_KEY`
- ❌ Wrong: `ssh_private_key`
- ❌ Wrong: `SSH_Private_Key`

Check your workflow file uses exact names:
```yaml
${{ secrets.SSH_PRIVATE_KEY }}  # Must match exactly
${{ secrets.VPS_HOST }}
${{ secrets.VPS_USER }}
${{ secrets.VPS_PATH }}
```

---

### **Issue 5: Can't see secret value**

**Problem:** Need to verify/update secret but can't see it

**Solution:** GitHub hides secrets for security. To update:
1. Go to repository Settings → Secrets → Actions
2. Click **"Update"** next to the secret
3. Enter the new value
4. Click **"Update secret"**

To verify: Test the workflow and check logs.

---

## 🔒 Security Best Practices

### **1. Never Commit Secrets**

❌ **DON'T:**
```bash
# Never put secrets in code
DB_PASSWORD=mysecretpassword  # Bad!
SSH_KEY=xxxxxxxxxxx          # Very bad!
```

✅ **DO:**
```bash
# Use GitHub Secrets
${{ secrets.DB_PASSWORD }}
${{ secrets.SSH_PRIVATE_KEY }}
```

---

### **2. Use Different Keys**

- ✅ One SSH key for GitHub Actions
- ✅ Different SSH key for personal use
- ✅ Different key per service if possible

**Why?** If one is compromised, others stay safe.

---

### **3. Rotate Keys Regularly**

Every 6-12 months:
1. Generate new SSH key
2. Add to VPS
3. Update GitHub secret
4. Remove old key from VPS

---

### **4. Limit Key Permissions**

On VPS, restrict what the key can do:

```bash
# On VPS, edit authorized_keys
nano ~/.ssh/authorized_keys

# Add restrictions before the key:
command="cd /home/user/project && git pull" ssh-ed25519 AAAA...
```

This limits the key to only pull code.

---

### **5. Monitor Secret Usage**

Check workflow logs regularly:
- Unusual deployments?
- Failed authentication attempts?
- Unexpected changes?

---

## 📊 Quick Reference

### **Where Secrets Are Used**

```yaml
# In .github/workflows/deploy-production.yml

- name: Setup SSH
  uses: webfactory/ssh-agent@v0.9.0
  with:
    ssh-private-key: ${{ secrets.SSH_PRIVATE_KEY }}  # ← Used here

- name: Add VPS to known hosts
  run: ssh-keyscan -H ${{ secrets.VPS_HOST }}  # ← Used here

- name: Deploy to VPS
  env:
    VPS_HOST: ${{ secrets.VPS_HOST }}    # ← Used here
    VPS_USER: ${{ secrets.VPS_USER }}    # ← Used here
    VPS_PATH: ${{ secrets.VPS_PATH }}    # ← Used here
  run: ssh $VPS_USER@$VPS_HOST ...
```

---

### **Secret Value Examples**

| Secret | Example Value | Notes |
|--------|---------------|-------|
| `SSH_PRIVATE_KEY` | `-----BEGIN OPENSSH...` | Full private key, 40+ lines |
| `VPS_HOST` | `123.45.67.89` | IP or domain |
| `VPS_USER` | `your-username` | SSH username |
| `VPS_PATH` | `/home/user/projects/app` | Absolute path |

---

### **Common Commands**

```bash
# Generate SSH key
ssh-keygen -t ed25519 -C "github-actions" -f ~/.ssh/github_actions

# Copy to VPS
ssh-copy-id -i ~/.ssh/github_actions.pub user@vps-ip

# Test connection
ssh -i ~/.ssh/github_actions user@vps-ip

# View private key
cat ~/.ssh/github_actions

# View public key
cat ~/.ssh/github_actions.pub
```

---

## 🎓 Understanding Secrets

### **What are GitHub Secrets?**

Encrypted variables stored in GitHub, accessible in workflows.

**Features:**
- ✅ Encrypted at rest
- ✅ Encrypted in transit
- ✅ Hidden in logs (`***`)
- ✅ Only accessible in workflows
- ✅ Can't be exported or viewed

### **How They Work**

```yaml
# 1. Define in workflow
${{ secrets.MY_SECRET }}

# 2. GitHub injects value at runtime
# 3. Value used in workflow
# 4. Logs show: ***
# 5. Never exposed
```

### **Secret Scope**

**Repository secrets:** Only this repository  
**Organization secrets:** All repos in organization  
**Environment secrets:** Specific environments (production, staging)

For this setup, we use **repository secrets**.

---

## ✅ Checklist

Before running deployment:

- [ ] SSH key generated (`~/.ssh/github_actions`)
- [ ] Public key added to VPS
- [ ] SSH connection works without password
- [ ] `SSH_PRIVATE_KEY` secret added to GitHub
- [ ] `VPS_HOST` secret added to GitHub
- [ ] `VPS_USER` secret added to GitHub
- [ ] `VPS_PATH` secret added to GitHub
- [ ] All 4 secrets visible in GitHub Settings
- [ ] Test deployment runs successfully

---

## 🎉 You're Done!

Once all secrets are added:

1. ✅ CI tests run on every push
2. ✅ Deployment runs automatically on push to `main`
3. ✅ No manual deployment needed
4. ✅ Secure, automated workflow

---

## 📚 Additional Resources

- [GitHub Secrets Documentation](https://docs.github.com/en/actions/security-guides/encrypted-secrets)
- [SSH Key Generation Guide](https://docs.github.com/en/authentication/connecting-to-github-with-ssh/generating-a-new-ssh-key-and-adding-it-to-the-ssh-agent)
- [GitHub Actions Security](https://docs.github.com/en/actions/security-guides/security-hardening-for-github-actions)

---

**Last Updated:** 2025-10-22  
**Version:** 1.0.0

