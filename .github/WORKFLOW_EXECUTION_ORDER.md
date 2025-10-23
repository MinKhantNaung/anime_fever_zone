# Workflow Execution Order

How GitHub Actions workflows run when you merge to main.

---

## 🔄 When You Merge to Main

### **Before Fix (Parallel - Dangerous!)**

```
git push origin main
        │
        ├─────────────────┬─────────────────┬─────────────────┐
        │                 │                 │                 │
        ▼                 ▼                 ▼                 ▼
   ┌─────────┐      ┌──────────┐    ┌──────────────┐   ┌──────┐
   │ CI Test │      │  Code    │    │ Deploy to    │   │ Main │
   │ Running │      │ Quality  │    │ Production   │   │ Build│
   └─────────┘      │ Running  │    │ Running!     │   └──────┘
        │           └──────────┘    └──────────────┘       │
        │                 │                 │              │
        ▼                 ▼                 ▼              ▼
    ❌ FAIL          ✅ PASS           ✅ DONE         ✅ DONE
        │                                   │
        └───────────────────────────────────┘
                    ⚠️ PROBLEM!
         Deployed even though tests failed!
```

**Issues:**
- ❌ Deployment starts immediately
- ❌ Doesn't wait for tests
- ❌ Could deploy broken code
- ❌ Tests fail but code already deployed

---

### **After Fix (Sequential - Safe!)**

```
git push origin main
        │
        ├────────────────┬─────────────────┐
        │                │                 │
        ▼                ▼                 ▼
   ┌─────────┐      ┌──────────┐    ┌──────────┐
   │ CI Test │      │  Code    │    │ Deploy   │
   │ Running │      │ Quality  │    │ Waiting  │
   └─────────┘      │ Running  │    │ ...      │
        │           └──────────┘    └──────────┘
        ▼                 │              │
    ✅ PASS          ✅ PASS            │
        │                                │
        └────────────────────────────────┤
                                         │
                         Triggers deployment
                                         │
                                         ▼
                                ┌──────────────────┐
                                │ Deploy to        │
                                │ Production       │
                                │ Now Running!     │
                                └──────────────────┘
                                         │
                                         ▼
                                    ✅ DONE
                             Production Updated!
```

**Benefits:**
- ✅ Deployment waits for CI
- ✅ Only deploys if tests pass
- ✅ Safe deployment
- ✅ No broken code in production

---

## 📊 Detailed Flow

### **Step 1: Push to Main**
```bash
git checkout main
git merge develop
git push origin main
```

### **Step 2: CI Tests Start (Parallel)**

Two workflows run in parallel:

**A) CI - Laravel Tests:**
```
✓ Setup PHP 8.3
✓ Install Composer dependencies
✓ Install npm dependencies
✓ Build frontend assets
✓ Run migrations
✓ Run PHPUnit tests
```

**B) Code Quality:**
```
✓ PHPStan analysis
✓ Psalm analysis
✓ PHP CS Fixer check
✓ Composer audit
✓ ESLint
✓ Prettier check
```

**Duration:** ~3-5 minutes (parallel)

### **Step 3: Deployment Waits**

```yaml
# deploy-production.yml
on:
  workflow_run:
    workflows: ["CI - Laravel Tests"]  # Wait for this
    types:
      - completed
    branches:
      - main

jobs:
  deploy:
    if: ${{ github.event.workflow_run.conclusion == 'success' }}
```

**Status:** ⏳ Waiting for CI to complete...

### **Step 4: Check CI Result**

**If CI Passes (✅):**
```
CI - Laravel Tests: ✅ Success
Code Quality: ✅ Success (doesn't block deployment)
        ↓
Deploy to Production: 🚀 Triggered!
```

**If CI Fails (❌):**
```
CI - Laravel Tests: ❌ Failed
        ↓
Deploy to Production: 🛑 Skipped (not triggered)
```

### **Step 5: Deployment Runs (Only if CI Passed)**

```
✓ SSH to VPS
✓ Change ownership to deploy user
✓ Pull latest code
✓ Install dependencies
✓ Build assets
✓ Restore www-data ownership
✓ Set permissions
✓ Run migrations
✓ Optimize Laravel
✓ Reload Octane
```

**Duration:** ~3-5 minutes

### **Step 6: Done!**

```
Total Time:
- CI Tests: 3-5 min
- Deployment: 3-5 min (after CI)
- Total: 6-10 min
```

---

## 🎯 Workflow Triggers

### **CI - Laravel Tests**

**Triggers on:**
- ✅ Push to `main`
- ✅ Push to `develop`
- ✅ Pull request to `main`
- ✅ Pull request to `develop`

**Purpose:** Test code before merging/deploying

### **Code Quality**

**Triggers on:**
- ✅ Push to `main`
- ✅ Push to `develop`
- ✅ Pull request to `main`
- ✅ Pull request to `develop`

**Purpose:** Check code quality (doesn't block deployment)

### **Deploy to Production**

**Triggers on:**
- ✅ CI workflow completes successfully on `main` branch
- ✅ Manual trigger (workflow_dispatch)

**Doesn't trigger on:**
- ❌ Pull requests
- ❌ Push to `develop`
- ❌ If CI fails

**Purpose:** Deploy only tested code

---

## 📋 Different Scenarios

### **Scenario 1: Push to `develop`**

```
git push origin develop
        ↓
┌───────────────────┐
│ CI Tests Run      │
│ Code Quality Run  │
└───────────────────┘
        ↓
    ✅ Pass / ❌ Fail
        ↓
   No Deployment
   (develop branch)
```

### **Scenario 2: Pull Request to `main`**

```
Create PR: develop → main
        ↓
┌───────────────────┐
│ CI Tests Run      │
│ Code Quality Run  │
└───────────────────┘
        ↓
    ✅ Pass / ❌ Fail
        ↓
   No Deployment
   (not merged yet)
```

### **Scenario 3: Merge to `main` (CI Passes)**

```
Merge PR → main
        ↓
┌───────────────────┐
│ CI Tests Run      │
│ Code Quality Run  │
└───────────────────┘
        ↓
    ✅ All Pass
        ↓
┌───────────────────┐
│ Deploy Triggered  │
│ Deployment Runs   │
└───────────────────┘
        ↓
  🎉 Production Updated!
```

### **Scenario 4: Merge to `main` (CI Fails)**

```
Merge PR → main
        ↓
┌───────────────────┐
│ CI Tests Run      │
│ Code Quality Run  │
└───────────────────┘
        ↓
    ❌ Tests Fail
        ↓
┌───────────────────┐
│ Deploy Skipped    │
│ Production Safe   │
└───────────────────┘
        ↓
  ⚠️ Fix tests first!
```

### **Scenario 5: Manual Deployment**

```
GitHub Actions → Deploy to Production → Run workflow
        ↓
┌───────────────────┐
│ Deploy Runs       │
│ (Skips CI check)  │
└───────────────────┘
        ↓
  🎉 Production Updated!
```

**Use when:** Emergency hotfix, CI is broken but code is safe

---

## ⚙️ Configuration Details

### **deploy-production.yml**

```yaml
on:
  workflow_run:
    workflows: ["CI - Laravel Tests"]  # Must match exact workflow name
    types:
      - completed                      # When workflow finishes
    branches:
      - main                           # Only on main branch
  workflow_dispatch:                   # Allow manual trigger

jobs:
  deploy:
    if: |
      github.event.workflow_run.conclusion == 'success' || 
      github.event_name == 'workflow_dispatch'
```

**Key points:**
- `workflows: ["CI - Laravel Tests"]` - Must match CI workflow name exactly
- `types: [completed]` - Triggers when CI finishes (success or failure)
- `if: conclusion == 'success'` - Only runs if CI succeeded
- `workflow_dispatch` - Allows manual deployment (bypasses CI check)

---

## 🔍 How to Monitor

### **View Workflow Runs**

```
GitHub → Actions tab
```

**You'll see:**

```
All workflows
├── CI - Laravel Tests      ✅ (3m 45s)
├── Code Quality            ✅ (2m 30s)
└── Deploy to Production    ⏳ Waiting for CI...
                              ↓
                            ✅ (4m 12s) Started after CI
```

### **Check Deployment Trigger**

Click on "Deploy to Production" run:

```
This workflow run was triggered by CI - Laravel Tests
✅ CI - Laravel Tests completed successfully
→ Starting deployment...
```

---

## 🎓 Best Practices

### **1. Always Wait for CI**

```yaml
# ✅ Good
on:
  workflow_run:
    workflows: ["CI - Laravel Tests"]

# ❌ Bad
on:
  push:
    branches: [main]  # Deploys immediately, no CI check
```

### **2. Use Manual Trigger for Emergencies**

```yaml
# ✅ Good - Allow manual override
workflow_dispatch: 

# Use when:
# - Hotfix needed urgently
# - CI is broken but code is safe
# - Rolling back quickly
```

### **3. Fail Fast**

```yaml
# In ci.yml
strategy:
  fail-fast: true  # Stop all jobs if one fails
```

### **4. Branch Protection**

Enable in GitHub Settings:
```
Settings → Branches → Add rule
- Branch name: main
- Require status checks: CI - Laravel Tests ✓
- Require review before merging ✓
```

---

## 📊 Timeline Example

### **Successful Deployment:**

```
00:00 - Push to main
00:01 - CI Tests start
00:01 - Code Quality starts
00:05 - CI Tests complete ✅
00:04 - Code Quality complete ✅
00:05 - Deploy starts (triggered by CI)
00:10 - Deploy complete ✅
```

**Total: 10 minutes**

### **Failed Tests:**

```
00:00 - Push to main
00:01 - CI Tests start
00:01 - Code Quality starts
00:03 - CI Tests fail ❌
00:04 - Code Quality complete ✅
00:03 - Deploy skipped ⏭️
```

**Total: 3 minutes, No deployment**

---

## ✅ Summary

**New workflow order:**

1. ✅ Push to main
2. ✅ CI Tests run (parallel with Code Quality)
3. ✅ Wait for CI to complete
4. ✅ Check CI result
5. ✅ If CI passed → Deploy
6. ✅ If CI failed → Skip deployment

**Benefits:**

- ✅ Safe deployments only
- ✅ Broken code never reaches production
- ✅ Automatic quality gate
- ✅ Manual override available

**The deployment now waits for CI to pass!** 🎉

---

**Last Updated:** 2025-10-22

