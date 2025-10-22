# 🔐 SSL/HTTPS Setup with Certbot (Docker)

Complete guide for setting up Let's Encrypt SSL certificates using Certbot in Docker.

---

## 📋 Overview

Your setup now includes:
- **Certbot container** - Automatically obtains and renews SSL certificates
- **Nginx** - Configured to serve ACME challenges and use SSL certificates
- **Auto-renewal** - Certificates renew automatically every 12 hours

---

## 🚀 Quick Start (First Time Setup)

### **Step 1: Update Configuration**

Edit `init-letsencrypt.sh` and update your email:

```bash
nano init-letsencrypt.sh
```

Change this line:
```bash
email="your-email@example.com"  # ← Change to your real email
```

To:
```bash
email="admin@animefeverzone.com"  # Your real email
```

### **Step 2: Run Initialization Script**

```bash
cd /home/min-khant-naung/animefeverzone

# Make sure DNS is pointing to your server!
# A record: animefeverzone.com → Your Server IP
# A record: www.animefeverzone.com → Your Server IP

# Run the script
./init-letsencrypt.sh
```

**What it does:**
1. ✅ Creates necessary directories
2. ✅ Starts nginx
3. ✅ Requests SSL certificates from Let's Encrypt
4. ✅ Reloads nginx with SSL
5. ✅ Starts auto-renewal service

### **Step 3: Verify HTTPS**

```bash
# Check if certificates exist
docker compose -f docker-compose.prod.yml exec nginx ls -la /etc/letsencrypt/live/animefeverzone.com/

# Test your site
curl -I https://animefeverzone.com
```

**Done!** 🎉 Your site should now be accessible via HTTPS!

---

## 📊 Architecture

```
Internet
    ↓
Port 80/443
    ↓
┌─────────────────────────────────────────┐
│  Nginx Container                         │
│  ├─ Port 80: HTTP                       │
│  │  ├─ /.well-known/acme-challenge/     │
│  │  │  (Certbot challenges)             │
│  │  └─ Redirect to HTTPS                │
│  └─ Port 443: HTTPS                     │
│     └─ Proxy to Octane                  │
└─────────────────────────────────────────┘
           ↓
    Certbot Container
    ├─ Obtains certificates
    ├─ Stores in: certbot_conf volume
    └─ Renews every 12h
```

---

## 🔄 How Certificate Renewal Works

### **Automatic Renewal:**

The certbot container runs continuously and:
1. Checks certificates every 12 hours
2. If certificate expires in < 30 days, renews it
3. Writes to `/etc/letsencrypt` (shared volume)
4. Nginx automatically uses new certificates

**Command running in certbot container:**
```bash
while :; do 
    certbot renew
    sleep 12h
    wait
done
```

### **Manual Renewal (if needed):**

```bash
# Force renewal
docker compose -f docker-compose.prod.yml run --rm certbot renew --force-renewal

# Reload nginx
docker compose -f docker-compose.prod.yml exec nginx nginx -s reload
```

---

## 📁 File Structure

```
project/
├── init-letsencrypt.sh          # Initial certificate setup
├── docker-compose.prod.yml      # Includes certbot service
└── docker/
    └── nginx/
        └── nginx.prod.conf      # Nginx with ACME challenge support

Docker Volumes:
├── certbot_conf/                # SSL certificates
│   └── live/
│       └── animefeverzone.com/
│           ├── fullchain.pem
│           ├── privkey.pem
│           └── ...
└── certbot_www/                 # ACME challenge files
    └── .well-known/
        └── acme-challenge/
```

---

## 🛠️ Common Operations

### **Check Certificate Expiration:**

```bash
docker compose -f docker-compose.prod.yml exec nginx \
  openssl x509 -in /etc/letsencrypt/live/animefeverzone.com/fullchain.pem -noout -dates
```

### **View Certbot Logs:**

```bash
docker compose -f docker-compose.prod.yml logs certbot
```

### **Test Certificate Renewal:**

```bash
# Dry run (doesn't actually renew)
docker compose -f docker-compose.prod.yml run --rm certbot renew --dry-run
```

### **Add More Domains:**

1. Edit `init-letsencrypt.sh`:
```bash
domains=(animefeverzone.com www.animefeverzone.com api.animefeverzone.com)
```

2. Update `nginx.prod.conf`:
```nginx
server_name animefeverzone.com www.animefeverzone.com api.animefeverzone.com;
```

3. Re-run:
```bash
./init-letsencrypt.sh
```

### **Force Certificate Renewal:**

```bash
docker compose -f docker-compose.prod.yml run --rm certbot certonly \
  --webroot \
  --webroot-path=/var/www/certbot \
  -d animefeverzone.com \
  -d www.animefeverzone.com \
  --email admin@animefeverzone.com \
  --agree-tos \
  --force-renewal

# Reload nginx
docker compose -f docker-compose.prod.yml exec nginx nginx -s reload
```

---

## 🧪 Testing Mode (Staging)

Let's Encrypt has rate limits (5 certificates per week). Use staging for testing:

Edit `init-letsencrypt.sh`:
```bash
staging=1  # Enable staging mode
```

**Staging vs Production:**
- **Staging:** No rate limits, but certificates not trusted by browsers
- **Production:** Trusted certificates, but rate limited

**After testing, switch to production:**
```bash
staging=0  # Disable staging mode
./init-letsencrypt.sh
```

---

## 🚨 Troubleshooting

### **Issue: "Connection refused"**

**Cause:** Nginx not running or ports not accessible

**Solution:**
```bash
# Check if nginx is running
docker compose -f docker-compose.prod.yml ps nginx

# Check port binding
sudo netstat -tulpn | grep :80
sudo netstat -tulpn | grep :443

# Start nginx
docker compose -f docker-compose.prod.yml up -d nginx
```

### **Issue: "DNS problem: NXDOMAIN"**

**Cause:** Domain not pointing to your server

**Solution:**
```bash
# Check DNS
dig animefeverzone.com +short
# Should return your server IP

# If not, update DNS records:
# A record: animefeverzone.com → Your Server IP
# Wait 5-10 minutes for DNS propagation
```

### **Issue: "Rate limit exceeded"**

**Cause:** Too many certificate requests in short time

**Solution:**
- Wait 1 week for limit to reset
- Use staging mode for testing
- Check: https://crt.sh/?q=animefeverzone.com

### **Issue: "Challenge validation failed"**

**Cause:** Nginx can't serve ACME challenge files

**Solution:**
```bash
# Check nginx config
docker compose -f docker-compose.prod.yml exec nginx nginx -t

# Check certbot directory
docker compose -f docker-compose.prod.yml exec nginx ls -la /var/www/certbot/

# Restart nginx
docker compose -f docker-compose.prod.yml restart nginx
```

### **Issue: Certificate not updating after renewal**

**Solution:**
```bash
# Reload nginx to pick up new certificates
docker compose -f docker-compose.prod.yml exec nginx nginx -s reload
```

---

## 📊 Certificate Information

### **Let's Encrypt Details:**
- **Issuer:** Let's Encrypt Authority
- **Validity:** 90 days
- **Auto-renewal:** When < 30 days remaining
- **Algorithm:** RSA 4096-bit (can be changed in init script)
- **Rate Limits:** 50 certificates per domain per week

### **Certificate Files:**
- `fullchain.pem` - Full certificate chain
- `privkey.pem` - Private key
- `cert.pem` - Your certificate
- `chain.pem` - Intermediate certificates

---

## 🔐 Security Best Practices

### **1. Strong SSL Configuration:**

Already configured in `nginx.prod.conf`:
```nginx
ssl_protocols TLSv1.2 TLSv1.3;
ssl_prefer_server_ciphers on;
ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:...;
```

### **2. HSTS Header:**

Already enabled:
```nginx
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
```

### **3. Test SSL Configuration:**

```bash
# Online test
# Visit: https://www.ssllabs.com/ssltest/analyze.html?d=animefeverzone.com

# Or use testssl.sh
docker run --rm -ti drwetter/testssl.sh animefeverzone.com
```

### **4. Backup Certificates:**

```bash
# Backup Let's Encrypt directory
docker run --rm -v animefeverzone_certbot_conf:/data -v $(pwd):/backup \
  alpine tar czf /backup/letsencrypt-backup.tar.gz /data

# Restore
docker run --rm -v animefeverzone_certbot_conf:/data -v $(pwd):/backup \
  alpine tar xzf /backup/letsencrypt-backup.tar.gz -C /
```

---

## 🎯 Quick Command Reference

```bash
# Initial setup
./init-letsencrypt.sh

# Check certificate
docker compose -f docker-compose.prod.yml exec nginx \
  openssl x509 -in /etc/letsencrypt/live/animefeverzone.com/fullchain.pem -noout -text

# Test renewal
docker compose -f docker-compose.prod.yml run --rm certbot renew --dry-run

# Force renewal
docker compose -f docker-compose.prod.yml run --rm certbot renew --force-renewal

# Reload nginx
docker compose -f docker-compose.prod.yml exec nginx nginx -s reload

# View certbot logs
docker compose -f docker-compose.prod.yml logs -f certbot

# Restart services
docker compose -f docker-compose.prod.yml restart nginx certbot
```

---

## 📝 Pre-Deployment Checklist

Before running `init-letsencrypt.sh`:

- [ ] DNS A records configured (point to your server)
- [ ] Ports 80 and 443 open on firewall
- [ ] Email address updated in `init-letsencrypt.sh`
- [ ] Domain names correct in `init-letsencrypt.sh`
- [ ] Docker compose services running
- [ ] Server accessible from internet

---

## 🎉 Success Indicators

After successful setup, you should see:

```bash
# ✅ Certificate files exist
docker compose -f docker-compose.prod.yml exec nginx \
  ls -la /etc/letsencrypt/live/animefeverzone.com/
# Shows: fullchain.pem, privkey.pem, etc.

# ✅ HTTPS works
curl -I https://animefeverzone.com
# HTTP/2 200

# ✅ HTTP redirects to HTTPS
curl -I http://animefeverzone.com
# 301 Moved Permanently
# Location: https://animefeverzone.com

# ✅ SSL test passes
openssl s_client -connect animefeverzone.com:443 -servername animefeverzone.com
# Shows certificate chain
```

---

## 🚀 Production Deployment

Complete deployment with SSL:

```bash
cd /home/min-khant-naung/animefeverzone

# 1. Build production images
docker compose -f docker-compose.prod.yml build

# 2. Start services (without SSL first)
docker compose -f docker-compose.prod.yml up -d

# 3. Initialize SSL certificates
./init-letsencrypt.sh

# 4. Verify everything is working
curl -I https://animefeverzone.com

# Done! 🎉
```

---

## 📚 Additional Resources

- **Let's Encrypt:** https://letsencrypt.org/
- **Certbot Documentation:** https://eff-certbot.readthedocs.io/
- **SSL Labs Test:** https://www.ssllabs.com/ssltest/
- **Rate Limits:** https://letsencrypt.org/docs/rate-limits/

---

Your SSL setup is now complete and will automatically renew! 🔒✨

