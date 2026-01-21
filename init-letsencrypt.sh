#!/bin/bash

# init-letsencrypt.sh - Initialize Let's Encrypt certificates with Certbot

set -e

domains=(animefeverzone.com)
rsa_key_size=4096
data_path="./certbot"
email="your-email@example.com" # Your email for Let's Encrypt notifications
staging=0 # Set to 1 if you're testing to avoid rate limits

echo "### Preparing directories ..."
mkdir -p "$data_path/conf"
mkdir -p "$data_path/www"

echo ""
echo "### Starting nginx ..."
docker compose -f docker-compose.prod.yml up -d nginx

echo ""
echo "### Waiting for nginx to start ..."
sleep 5

echo ""
echo "### Requesting Let's Encrypt certificate for ${domains[*]} ..."

# Join domains for certbot
domain_args=""
for domain in "${domains[@]}"; do
  domain_args="$domain_args -d $domain"
done

# Select appropriate email arg
case "$email" in
  your-email@example.com) email_arg="--register-unsafely-without-email" ;;
  *) email_arg="--email $email" ;;
esac

# Enable staging mode if testing
if [ $staging != "0" ]; then staging_arg="--staging"; fi

echo ""
echo "### Running certbot ..."
docker compose -f docker-compose.prod.yml run --rm certbot certonly \
  --webroot \
  --webroot-path=/var/www/certbot \
  $staging_arg \
  $email_arg \
  $domain_args \
  --rsa-key-size $rsa_key_size \
  --agree-tos \
  --force-renewal \
  --non-interactive

echo ""
echo "### Reloading nginx ..."
docker compose -f docker-compose.prod.yml exec nginx nginx -s reload

echo ""
echo "### SSL certificates successfully obtained!"
echo "### Starting certbot renewal service ..."
docker compose -f docker-compose.prod.yml up -d certbot

echo ""
echo "✅ Done! Your site should now be accessible via HTTPS."

