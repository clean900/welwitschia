#!/usr/bin/env bash
# Provisionamento de uma VM Ubuntu 22.04/24.04 para o Welwitschia ERP.
# Correr como root (ou com sudo) na VM Proxmox.  Idempotente no essencial.
set -euo pipefail

echo "==> Pacotes base"
apt-get update
apt-get install -y software-properties-common ca-certificates curl gnupg git unzip nginx redis-server supervisor

echo "==> PHP 8.4"
add-apt-repository -y ppa:ondrej/php
apt-get update
apt-get install -y php8.4-fpm php8.4-cli php8.4-pgsql php8.4-redis php8.4-mbstring \
  php8.4-bcmath php8.4-intl php8.4-xml php8.4-curl php8.4-zip php8.4-gd

echo "==> PostgreSQL 16"
install -d /usr/share/postgresql-common/pgdg
curl -fsSL https://www.postgresql.org/media/keys/ACCC4CF8.asc -o /usr/share/postgresql-common/pgdg/apt.postgresql.org.asc
echo "deb [signed-by=/usr/share/postgresql-common/pgdg/apt.postgresql.org.asc] https://apt.postgresql.org/pub/repos/apt $(. /etc/os-release && echo $VERSION_CODENAME)-pgdg main" > /etc/apt/sources.list.d/pgdg.list
apt-get update
apt-get install -y postgresql-16

echo "==> Node 20 (build dos assets)"
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt-get install -y nodejs

echo "==> Composer"
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

echo "==> Certbot (Let's Encrypt)"
apt-get install -y certbot python3-certbot-nginx

echo "==> Base de dados (definir a password!)"
DB_PASS="${DB_PASS:-troca-esta-password}"
sudo -u postgres psql -tc "SELECT 1 FROM pg_roles WHERE rolname='welwitschia'" | grep -q 1 || \
  sudo -u postgres psql -c "CREATE USER welwitschia WITH PASSWORD '${DB_PASS}' CREATEDB;"
sudo -u postgres psql -tc "SELECT 1 FROM pg_database WHERE datname='welwitschia_landlord'" | grep -q 1 || \
  sudo -u postgres psql -c "CREATE DATABASE welwitschia_landlord OWNER welwitschia;"

echo "==> Concluído. Próximo: clonar o repo em /var/www/welwitschia e seguir o DEPLOY-PROXMOX.md"
