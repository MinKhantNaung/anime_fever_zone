# Deployment Options

This project supports **two separate deployment approaches**. Choose the one that fits your needs.

---

## 🎯 Overview

| Approach | Files | Best For | Complexity |
|----------|-------|----------|------------|
| **Docker Production** | `docker-compose.prod.yml`, `Dockerfile.prod` | Production servers, containerized environments | Medium |
| **CI/CD (No Docker)** | `.github/workflows/*.yml` | Traditional VPS, manual deployments | Low |

**Important:** These are **separate and independent** deployment methods. You can use one or both.

---

## 🐳 Option 1: Docker Production

### **What It Is**

A complete Docker-based production environment with:
- Laravel Octane (Swoole)
- Nginx
- MySQL 8
- Redis 7
- Certbot (SSL)

### **Key Files**

```
docker-compose.prod.yml          # Production orchestration
docker/php/Dockerfile.prod       # Optimized PHP image
docker/nginx/nginx.prod.conf     # Nginx config with SSL
docker/supervisor/octane.prod.conf # Octane supervisor config
init-letsencrypt.sh              # SSL certificate setup
```

### **When to Use**

✅ **Use Docker when you want:**
- Containerized, isolated environment
- Easy scaling across multiple servers
- Consistent deployment across environments
- Built-in SSL with Certbot
- Self-contained database and services

### **Setup**

```bash
# Build and start
docker compose -f docker-compose.prod.yml build
docker compose -f docker-compose.prod.yml up -d

# Setup SSL
./init-letsencrypt.sh

# Done!
```

### **Pros & Cons**

**Pros:**
- ✅ Isolated environment
- ✅ Easy to scale
- ✅ Includes all services (DB, Redis, SSL)
- ✅ Portable across servers
- ✅ Built-in SSL automation

**Cons:**
- ❌ Higher resource usage
- ❌ Learning curve for Docker
- ❌ Requires Docker knowledge for debugging

### **Documentation**

- `PRODUCTION_SETUP.md` - Complete Docker setup guide
- `SSL_SETUP_CERTBOT.md` - SSL configuration
- `docker-compose.prod.yml` - Production config

---

## 🚀 Option 2: CI/CD Without Docker

### **What It Is**

GitHub Actions workflows for automated testing and deployment to **traditional VPS** (no Docker) with:
- Laravel Octane (systemd service)
- Native PHP/Node.js
- Existing MySQL/Redis
- Nginx (managed separately)

### **Key Files**

```
.github/workflows/ci.yml                  # Automated tests
.github/workflows/code-quality.yml        # Static analysis
.github/workflows/deploy-production.yml   # Auto deployment
```

### **When to Use**

✅ **Use CI/CD (No Docker) when you:**
- Already have a VPS with PHP/MySQL/Redis
- Run Octane as a systemd service
- Want automated deployments
- Don't want to use Docker
- Have a traditional LEMP/LAMP stack

### **Setup**

```bash
# 1. Generate SSH key
ssh-keygen -t ed25519 -f ~/.ssh/github_actions
ssh-copy-id -i ~/.ssh/github_actions.pub your-user@vps

# 2. Add GitHub secrets:
# - SSH_PRIVATE_KEY
# - VPS_HOST
# - VPS_USER
# - VPS_PATH

# 3. Push to main branch
git push origin main

# Done! Deploys automatically
```

### **Pros & Cons**

**Pros:**
- ✅ Lower resource usage
- ✅ Works with existing VPS setup
- ✅ No Docker required
- ✅ Automated deployments
- ✅ Automated testing

**Cons:**
- ❌ Requires existing server setup
- ❌ Manual service management
- ❌ No built-in SSL automation
- ❌ Less portable

### **Documentation**

- `.github/workflows/QUICK_START.md` - 5-minute setup
- `CI_CD_SETUP.md` - Complete configuration
- `CI_SETUP_SUMMARY.md` - Overview
- `.github/DEPLOYMENT_CHECKLIST.md` - Deployment checklist

---

## 🔀 Can I Use Both?

**Yes!** These approaches are independent:

### **Scenario 1: Docker Production + Manual Dev**
```bash
# Development: Manual setup (docker-compose.yml)
docker compose up -d

# Production: Docker containers (docker-compose.prod.yml)
docker compose -f docker-compose.prod.yml up -d
```

### **Scenario 2: CI/CD + Docker**
```bash
# Testing: CI/CD tests (GitHub Actions)
git push origin develop  # Runs tests

# Deployment: Deploy Docker setup via CI/CD
# Modify deploy-production.yml to deploy Docker instead
```

### **Scenario 3: Use Different Environments**
```bash
# Staging: Traditional VPS with CI/CD
git push origin staging  # Deploys to VPS

# Production: Docker with manual deployment
docker compose -f docker-compose.prod.yml up -d
```

---

## 📊 Comparison

### **Development**

| Feature | Docker | Traditional |
|---------|--------|-------------|
| Setup Time | 5 min | 30 min |
| Isolation | ✅ Full | ❌ None |
| Resource Usage | High | Low |
| Dependencies | Auto | Manual |
| Portability | ✅ High | ❌ Low |

### **Production**

| Feature | Docker Prod | CI/CD (No Docker) |
|---------|-------------|-------------------|
| Setup Complexity | Medium | Low |
| SSL Automation | ✅ Built-in | ❌ Manual |
| Resource Usage | Higher | Lower |
| Scaling | ✅ Easy | ❌ Manual |
| Deployment | Manual/CD | ✅ Automated |
| Testing | Optional | ✅ Built-in |

### **CI/CD**

| Feature | Docker | No Docker |
|---------|--------|-----------|
| Test Automation | ✅ Yes | ✅ Yes |
| Deploy Automation | Optional | ✅ Yes |
| Build Time | Longer | Faster |
| Complexity | Higher | Lower |

---

## 🎯 Recommendations

### **For Beginners**

Start with **CI/CD (No Docker)** if:
- You have a traditional VPS
- You're comfortable with Linux/systemd
- You want automated deployments
- You don't need containerization

### **For Scalability**

Use **Docker Production** if:
- You plan to scale horizontally
- You want environment consistency
- You need full isolation
- You want built-in SSL automation

### **For Best of Both Worlds**

**Hybrid Approach:**
1. Development: Docker (`docker-compose.yml`)
2. Testing: CI/CD (GitHub Actions)
3. Production: Docker (`docker-compose.prod.yml`)

```yaml
# Modify .github/workflows/deploy-production.yml
# Replace systemd commands with Docker commands:

# Instead of:
sudo systemctl restart octane

# Use:
docker compose -f docker-compose.prod.yml restart app
```

---

## 📚 Quick Start Guides

### **Start with Docker Production:**
```bash
# Read these in order:
1. PRODUCTION_SETUP.md
2. SSL_SETUP_CERTBOT.md
3. Test: docker compose -f docker-compose.prod.yml up -d
```

### **Start with CI/CD (No Docker):**
```bash
# Read these in order:
1. .github/workflows/QUICK_START.md
2. CI_CD_SETUP.md
3. Test: git push origin main
```

---

## 🔄 Migration Paths

### **From Traditional VPS → Docker**

```bash
# 1. Backup everything
mysqldump -u root -p database > backup.sql

# 2. Setup Docker
docker compose -f docker-compose.prod.yml build
docker compose -f docker-compose.prod.yml up -d

# 3. Import data
docker compose -f docker-compose.prod.yml exec -T mysql \
  mysql -u root -p database < backup.sql

# 4. Test thoroughly
# 5. Switch DNS/Nginx upstream
```

### **From Docker → Traditional VPS**

```bash
# 1. Export data
docker compose -f docker-compose.prod.yml exec mysql \
  mysqldump -u root -p database > backup.sql

# 2. Setup traditional services
sudo apt install php8.3-cli mysql-server redis-server

# 3. Import data
mysql -u root -p database < backup.sql

# 4. Setup systemd service for Octane
# 5. Configure Nginx
# 6. Test thoroughly
```

---

## ❓ FAQ

### **Q: Which is better?**

**A:** Depends on your needs:
- **Docker:** Better for scalability, isolation, and consistency
- **No Docker:** Better for simplicity, lower resources, and traditional setups

### **Q: Can I use Docker in development and not in production?**

**A:** Yes! Many developers use Docker for development (consistency) but deploy to traditional VPS (simplicity).

### **Q: Will CI/CD work with Docker?**

**A:** Yes! You can modify the deployment workflow to use Docker commands instead of systemd.

### **Q: Which uses less resources?**

**A:** Traditional VPS (no Docker) uses significantly less memory and CPU.

### **Q: Which is more secure?**

**A:** Both can be equally secure. Docker provides isolation, but traditional setups can be hardened with proper configuration.

### **Q: Can I test locally before choosing?**

**A:** Yes!
- **Docker:** `docker compose -f docker-compose.prod.yml up -d`
- **Traditional:** Set up a test VPS and use CI/CD

---

## 📞 Support

- **Docker Issues:** See `PRODUCTION_SETUP.md`
- **CI/CD Issues:** See `CI_CD_SETUP.md`
- **General:** Check Laravel Octane documentation

---

## ✅ Summary

**Two independent deployment options:**

1. **🐳 Docker Production** - Containerized, scalable, includes SSL
2. **🚀 CI/CD (No Docker)** - Traditional VPS, automated, simpler

**Choose based on:**
- Your infrastructure (existing vs. new)
- Your expertise (Docker vs. traditional)
- Your needs (scalability vs. simplicity)

**Both work great!** Pick what fits your workflow. 🎉

