# GitHub Actions Workflows

## Available Workflows

### 🧪 CI - Laravel Tests
**File:** `ci.yml`  
**Trigger:** Push/PR to main/develop  
**Duration:** ~3-5 minutes

Runs PHPUnit tests with SQLite `:memory:` and Redis services.

**Database:** SQLite in-memory (fast, no setup needed)  
**Cache:** Redis for session/cache testing

---

### ✨ Code Quality
**File:** `code-quality.yml`  
**Trigger:** Push/PR to main/develop  
**Duration:** ~3-4 minutes

Runs static analysis and code style checks.

---

### 🚀 Deploy to Production
**File:** `deploy-production.yml`  
**Trigger:** Push to main or manual  
**Duration:** ~3-5 minutes

Automatically deploys to production VPS.

---

## Quick Commands

```bash
# View workflow status
gh workflow list

# View recent runs
gh run list

# View specific run logs
gh run view <run-id>

# Trigger manual deployment
gh workflow run deploy-production.yml

# Watch deployment
gh run watch
```

---

## 🔧 Setup Required

### **Quick Setup:**
See [SECRETS_QUICK_GUIDE.md](../SECRETS_QUICK_GUIDE.md) - 5 minutes

### **Detailed Setup:**
See [GITHUB_SECRETS_SETUP.md](../GITHUB_SECRETS_SETUP.md) - Step-by-step with screenshots

### **Complete Guide:**
See [CI_CD_SETUP.md](../../CI_CD_SETUP.md) - Full documentation

---

## 🔐 Required Secrets

Add these in GitHub → Settings → Secrets → Actions:

| Secret | Example | What It Is |
|--------|---------|------------|
| `SSH_PRIVATE_KEY` | `-----BEGIN OPENSSH...` | SSH private key |
| `VPS_HOST` | `123.45.67.89` | VPS IP address |
| `VPS_USER` | `minkhantnaungroot` | SSH username |
| `VPS_PATH` | `/home/user/projects/app` | Project path |

