# Deploy — cPanel (us168) · PostgreSQL + PHP 8.4

> **Estado: EM PRODUÇÃO** em https://welwitschia.ao (conta cPanel `welwitschia`).
> Validado E2E: registo de empresa cria schema PostgreSQL por tenant (`tenant_<slug>`, 32 tabelas).
> Confirmado no us168: **PostgreSQL 10.23** + **PHP 8.4.21** (ea-php84). Sem Redis nem Node.
> Modelo: **schema-per-tenant** numa só BD → sem problema de quota de bases de dados.

**PHP correcto** (o `php`/`composer` por defeito são 8.1 — partem tudo):
```
/opt/cpanel/ea-php84/root/usr/bin/php
```

---

## 1. `[cPanel Terminal]` Base de dados PostgreSQL (via UAPI)
⚠️ **Gotcha do prefixo:** `create_*` prefixam sozinhos (passas nome curto); `grant_all_privileges`
e `set_password` exigem o nome **completo** `welwitschia_*`. A função de grant é
`grant_all_privileges` (NÃO `set_privileges_on_database`, que não existe no módulo Postgres).

```bash
uapi Postgresql create_database name=landlord
uapi Postgresql create_user name=weluser password='PASS_FORTE'
uapi Postgresql grant_all_privileges user=welwitschia_weluser database=welwitschia_landlord
# verificar (users: deve mostrar welwitschia_weluser; e schema-test):
uapi Postgresql list_databases
psql -h 127.0.0.1 -U welwitschia_weluser -d welwitschia_landlord -W -c "CREATE SCHEMA _t; DROP SCHEMA _t;"
```
> O grant é o que regista a entrada no `pg_hba.conf`. Sem ele → `FATAL: no pg_hba.conf entry`.

## 2. `[Mac]` Compilar assets + enviar por git (servidor não tem Node)
```bash
cd ~/welwitschia && npm run build
git add -f public/build && git commit -m "build: assets" && git push
```

## 3. `[cPanel]` Código via Git Version Control
- Gerar chave SSH na conta: `ssh-keygen -t ed25519 -f ~/.ssh/id_ed25519 -N ""`
- Adicionar a pública como **Deploy key (read-only)** em github.com/clean900/welwitschia
- `cd ~ && git clone git@github.com:clean900/welwitschia.git welwitschia`

## 4. `[cPanel Terminal]` Configurar + instalar
```bash
cd ~/welwitschia
PHP=/opt/cpanel/ea-php84/root/usr/bin/php
cp .env.cpanel.example .env          # preencher DB_* (welwitschia_landlord / welwitschia_weluser) + APP_URL
$PHP /usr/local/bin/composer install --no-dev --optimize-autoloader   # composer COM php84!
$PHP artisan key:generate
$PHP artisan migrate --force
$PHP artisan db:seed --class='Database\Seeders\Landlord\PlansSeeder' --force
$PHP artisan welwitschia:admin admin@welwitschia.ao --name="Bráulio" --password='PASS_ADMIN'
chmod -R 775 storage bootstrap/cache
```

## 5. `[cPanel]` PHP do domínio → 8.4
O document root do domínio principal **não é editável** na UI (só WHM). E o site corre na
versão PHP do MultiPHP (era 8.1). Pôr em 8.4:
```bash
uapi LangPHP php_set_vhost_versions version=ea-php84 vhost-0=welwitschia.ao
```
(ou cPanel → MultiPHP Manager → welwitschia.ao → ea-php84)

## 6. `[cPanel Terminal]` Docroot: `public_html` = pasta pública do Laravel
⚠️ **O LiteSpeed do us168 NÃO segue symlink no docroot** (dá `defaultwebpage.cgi`).
Solução: `public_html` é uma **cópia** de `public/`, com `index.php` a apontar para `~/welwitschia`.
```bash
cd ~
mv public_html public_html_old_bak          # backup do que lá estava
mkdir public_html
cp -r ~/welwitschia/public/. ~/public_html/
# editar ~/public_html/index.php: trocar '/../' por '/../welwitschia/' nas 3 linhas
#   (maintenance, vendor/autoload, bootstrap/app)
```

## 7. `[cPanel]` Cache + SSL
```bash
$PHP artisan optimize                        # config+route+view cache
```
SSL: o AutoSSL do domínio já existia (o domínio já era servido). Confirmar em SSL/TLS Status.

---

## Verificar
```bash
curl -s https://welwitschia.ao/ -k | head -c 120            # <title>Welwitschia ERP</title>
# registar empresa cria schema:
export PGPASSWORD='...'
psql -h 127.0.0.1 -U welwitschia_weluser -d welwitschia_landlord -c "\dn"   # tenant_<slug>
```

## ⚠️ Notas
- **Não usar `/horizon`** (sem Redis). Filas em `QUEUE_CONNECTION=sync`.
- **PostgreSQL 10 é EOL** — ok para piloto/homologação AGT; para escala → VPS/Proxmox PG16 (só muda `.env`).
- Comandos `artisan`/`git`/`composer` SÓ no terminal da conta `welwitschia` (há outras contas no host: adigital2021, etc.).

## Atualizações futuras
```bash
cd ~/welwitschia && git pull
PHP=/opt/cpanel/ea-php84/root/usr/bin/php
$PHP /usr/local/bin/composer install --no-dev --optimize-autoloader
$PHP artisan migrate --force && $PHP artisan tenants:migrate --force
# se os assets mudaram (rebuild no Mac + push): re-copiar para o docroot:
cp -r ~/welwitschia/public/build/. ~/public_html/build/
$PHP artisan optimize
```
