# Deploy — Proxmox (servidor interno) + DNS via cPanel

> **Domínio único** (welwitschia.ao): **NÃO precisas de wildcard DNS**. Só apontas
> `welwitschia.ao` ao teu IP público. O cPanel serve apenas de **DNS**; a app corre
> no Proxmox.

Legenda: `[VM]` na VM Ubuntu · `[Router]` no router · `[cPanel]` no painel de DNS do domínio.

---

## 0. Arquitetura

```
Internet → [Router: port forward 80/443] → VM Ubuntu (Proxmox)
                                              ├─ Nginx → PHP 8.4-fpm → Laravel
                                              ├─ PostgreSQL 16 · Redis · Horizon
                                              └─ (opcional) n8n em Docker
DNS welwitschia.ao (cPanel) → IP público do teu router
```

## 1. `[Proxmox]` Criar a VM
- Ubuntu Server 24.04 LTS · 4 vCPU · 8 GB RAM · 60 GB disco (mínimo)
- Anota o **IP local** da VM (ex.: `192.168.1.20`) e garante IP fixo (DHCP reservado).

## 2. `[VM]` Provisionar o stack
```bash
sudo DB_PASS='UMA-PASSWORD-FORTE' bash /caminho/para/deploy/provision.sh
```
(ou clona primeiro o repo e corres `sudo DB_PASS=... bash deploy/provision.sh`)

## 3. `[VM]` Clonar o repo
```bash
sudo mkdir -p /var/www && cd /var/www
sudo git clone git@github.com:clean900/welwitschia.git
sudo chown -R www-data:www-data /var/www/welwitschia
cd /var/www/welwitschia
```
> Precisas de uma **deploy key** (chave SSH da VM adicionada ao repo no GitHub:
> Settings → Deploy keys), ou usar HTTPS com token.

## 4. `[VM]` Configurar o `.env` de produção
```bash
sudo -u www-data cp .env.example .env
sudo -u www-data nano .env
```
Valores-chave:
```env
APP_NAME="Welwitschia ERP"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://welwitschia.ao
APP_TIMEZONE=Africa/Luanda
APP_LOCALE=pt

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_DATABASE=welwitschia_landlord
DB_USERNAME=welwitschia
DB_PASSWORD=UMA-PASSWORD-FORTE

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1

CENTRAL_DOMAINS="welwitschia.ao,www.welwitschia.ao"
TENANT_BASE_DOMAIN=welwitschia.ao

# Quando o n8n estiver a correr:
N8N_WEBHOOK_URL=
```

## 5. `[VM]` Instalar, compilar e migrar
```bash
sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data npm ci && sudo -u www-data npm run build
sudo -u www-data php artisan key:generate
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan db:seed --class="Database\\Seeders\\Landlord\\PlansSeeder" --force
sudo -u www-data php artisan welwitschia:admin admin@welwitschia.ao --name="Bráulio"
sudo -u www-data php artisan config:cache && php artisan route:cache
```

## 6. `[VM]` Permissões
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

## 7. `[VM]` Nginx + Horizon
```bash
sudo cp deploy/nginx.conf /etc/nginx/sites-available/welwitschia
sudo ln -sf /etc/nginx/sites-available/welwitschia /etc/nginx/sites-enabled/welwitschia
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx

sudo cp deploy/welwitschia-horizon.service /etc/systemd/system/
sudo systemctl daemon-reload && sudo systemctl enable --now welwitschia-horizon
```

## 8. `[Router]` Port forward
| Externo | → | Interno (VM) |
|---|---|---|
| 80 TCP | → | `192.168.1.20:80` |
| 443 TCP | → | `192.168.1.20:443` |

(SSH só se precisares de deploy remoto — preferir não expor o 22; usar porta alta ou VPN.)

## 9. `[cPanel]` DNS — apontar o domínio
No editor de zona DNS de `welwitschia.ao`:
| Tipo | Nome | Valor |
|---|---|---|
| A | `@` | **IP público do teu router** |
| A | `www` | **IP público do teu router** |
| A | `n8n` *(opcional)* | **IP público do teu router** |

> Se o IP público for **dinâmico**, configura DDNS (ex.: Cloudflare/no-ip) e aponta o A para o host DDNS.

## 10. `[VM]` SSL (Let's Encrypt) — só depois de 8 e 9 prontos
```bash
sudo certbot --nginx -d welwitschia.ao -d www.welwitschia.ao
```
(O certbot precisa que o porto 80 chegue à VM e que o DNS já resolva.)

## 11. `[VM]` n8n (opcional)
```bash
# instalar docker se não tiver: curl -fsSL https://get.docker.com | sh
docker compose -f docker-compose.n8n.yml up -d
# depois, no .env: N8N_WEBHOOK_URL=https://n8n.welwitschia.ao/webhook  (e refazer config:cache)
```

## 12. Atualizações futuras (git como fonte única)
```bash
cd /var/www/welwitschia
sudo -u www-data git pull
sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data npm ci && sudo -u www-data npm run build
sudo -u www-data php artisan migrate --force && php artisan tenants:migrate --force
sudo -u www-data php artisan config:cache && php artisan route:cache
sudo php artisan horizon:terminate   # Horizon reinicia sozinho (systemd)
```
> Isto está automatizado em `.github/workflows/deploy.yml` se quiseres deploy por push
> (precisa dos secrets `DEPLOY_HOST/USER/SSH_KEY/PATH` e SSH acessível).

## 13. Operação
- **Backup diário:** `pg_dump welwitschia_landlord` + dumps dos schemas `tenant_*` → disco externo/MinIO.
- **UPS** no servidor (o plano marca-o como obrigatório para o SLA).
- **Logs:** `storage/logs/laravel.log`, `journalctl -u welwitschia-horizon`.

---

## ⚠️ Antes de cobrar dinheiro a sério
- **Credenciais reais** ProxyPay (produção) + TelcoSMS por empresa.
- **Sign-off de consultor fiscal/laboral AO** (IRT, INSS, IVA, PGC, numeração AGT).
