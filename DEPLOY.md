# Deploy & Sincronização — Welwitschia ERP

> **Regra de ouro:** o git é a fonte única de verdade. Nunca se edita código no
> servidor. O servidor só faz `git reset --hard origin/main` (pull). Tudo o que
> vai para produção passa por commit → push → CI verde → deploy.

## Porque NÃO é cPanel

O Welwitschia exige **PostgreSQL 16** (schema-per-tenant), **Redis + Horizon**
(daemon persistente) e **wildcard DNS** por tenant. O cPanel partilhado (us168,
MySQL, sem daemons) não suporta nada disto. Produção = **Proxmox local** ou um
**VPS** (DigitalOcean/Hetzner/Contabo) com Docker.

## Fluxo

```
[Mac] editar → flutter/php -l → php artisan test → git commit → git push
   → GitHub Actions: CI (18 testes, Postgres+Redis) → verde
   → GitHub Actions: Deploy (SSH git pull no servidor)
```

## Servidor — pré-requisitos (Proxmox VM01 ou VPS)

- Ubuntu 22.04, PHP 8.4 (pdo_pgsql, redis), Composer
- PostgreSQL 16, Redis 7
- Supervisor a correr `php artisan horizon`
- Nginx/Caddy → wildcard `*.welwitschia.ao`

## Activar o deploy automático

No GitHub: **Settings → Secrets and variables → Actions**, criar:

| Secret | Exemplo |
|---|---|
| `DEPLOY_HOST` | `192.168.1.10` ou IP do VPS |
| `DEPLOY_USER` | `welwitschia` |
| `DEPLOY_SSH_KEY` | chave privada SSH com acesso ao servidor |
| `DEPLOY_PATH` | `/var/www/welwitschia` |

Sem estes secrets, o job de deploy é **ignorado** (não falha) — útil enquanto o
servidor não existe. O CI continua a correr na mesma a cada push.

## Primeiro provisionamento no servidor (manual, uma vez)

```bash
git clone git@github.com:clean900/welwitschia.git /var/www/welwitschia
cd /var/www/welwitschia
composer install --no-dev --optimize-autoloader
cp .env.example .env   # depois preencher Postgres/Redis/keys de produção
php artisan key:generate
php artisan migrate --force
# criar supervisor para: php artisan horizon
```
