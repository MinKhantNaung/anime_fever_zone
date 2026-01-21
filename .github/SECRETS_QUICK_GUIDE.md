# GitHub Secrets - Quick Guide

**⏱️ 5-Minute Setup**

---

## 🎯 What You Need

4 secrets in GitHub for deployment to work:

```
✅ SSH_PRIVATE_KEY  →  Your SSH private key
✅ VPS_HOST         →  123.45.67.89
✅ VPS_USER         →  your-username
✅ VPS_PATH         →  /home/your-username/projects/animefeverzone
```

---

## 🚀 Quick Setup (3 Steps)

### **Step 1: Generate SSH Key** (2 minutes)

```bash
# On your local machine
ssh-keygen -t ed25519 -C "github-actions" -f ~/.ssh/github_actions
# Press Enter twice (no passphrase)

# Copy to VPS
ssh-copy-id -i ~/.ssh/github_actions.pub your-user@your-vps-ip

# Test (should work without password)
ssh -i ~/.ssh/github_actions your-user@your-vps-ip
exit
```

---

### **Step 2: Get Private Key** (1 minute)

```bash
# Display private key
cat ~/.ssh/github_actions

# Copy ENTIRE output (including BEGIN and END lines)
```

---

### **Step 3: Add to GitHub** (2 minutes)

**Go to:**
```
GitHub → Your Repo → Settings → Secrets and variables → Actions
```

**Add 4 secrets:**

#### **1. SSH_PRIVATE_KEY**
```
Name:   SSH_PRIVATE_KEY
Secret: [Paste entire private key from Step 2]
```

#### **2. VPS_HOST**
```
Name:   VPS_HOST
Secret: 123.45.67.89  (your VPS IP)
```

#### **3. VPS_USER**
```
Name:   VPS_USER
Secret: your-username  (your SSH username)
```

#### **4. VPS_PATH**
```
Name:   VPS_PATH
Secret: /home/your-username/projects/animefeverzone
```

---

## ✅ Done!

Test it:
```bash
git push origin main
```

Check: `GitHub → Actions tab`

---

## 🆘 Troubleshooting

**Connection fails?**
```bash
# Test SSH key works
ssh -i ~/.ssh/github_actions your-user@your-vps-ip
```

**Wrong path?**
```bash
# SSH to VPS and verify path
ssh your-user@vps-ip
cd /home/your-username/projects/animefeverzone
pwd  # Copy this path
```

---

## 📖 Full Guide

See: `.github/GITHUB_SECRETS_SETUP.md`

---

**That's it! Your CI/CD is ready! 🎉**

