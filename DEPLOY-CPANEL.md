# Deploy — cPanel (us168) · PostgreSQL + PHP 8.4

> Confirmado no us168: **PostgreSQL 10.23** + **PHP 8.4.21** (ea-php84). Sem Redis nem Node.
> Modelo: **schema-per-tenant** numa só BD → sem problema de quota de bases de dados.

**PHP correcto** (usar SEMPRE este caminho, não o `php` por defeito que é 8.1):
```
/opt/cpanel/ea-php84/root/usr/bin/php
```

---

## 1. `[cPanel]` Criar a base de dados PostgreSQL
cPanel → **PostgreSQL Databases**:
1. Criar BD: `landlord` → fica `welwitschia_landlord`
2. Criar utilizador + password forte
3. **Adicionar o utilizador à BD** com **ALL PRIVILEGES**

> O utilizador tem de poder **criar schemas** (cada empresa = um schema). Sendo dono da BD, consegue.

## 2. `[Mac]` Compilar os assets (não há Node no servidor)
```bash
cd ~/welwitschia && npm run build
```
Isto gera `public/build/`. Vais enviá-lo no passo 4.

## 3. `[cPanel]` Colocar o código
**Opção A — Git (recomendado):** cPanel → **Git Version Control** → Clone
`git@github.com:clean900/welwitschia.git` para `/home/welwitschia/welwitschia`.
**Opção B — Upload:** enviar o projecto por File Manager/FTP.

## 4. `[cPanel]` Enviar os assets compilados
Por File Manager, enviar a pasta **`public/build/`** (gerada no passo 2) para
`.../welwitschia/public/build/`. (Não está no git porque é artefacto de build.)

## 5. `[cPanel - Terminal]` Configurar
```bash
cd ~/welwitschia
cp .env.cpanel.example .env
nano .env        # preencher DB_DATABASE/USERNAME/PASSWORD + APP_URL
PHP=/opt/cpanel/ea-php84/root/usr/bin/php

# Composer (se não houver, baixar composer.phar)
$PHP composer.phar install --no-dev --optimize-autoloader   # ou: composer install ...

$PHP artisan key:generate
$PHP artisan migrate --force
$PHP artisan db:seed --class="Database\\Seeders\\Landlord\\PlansSeeder" --force
$PHP artisan welwitschia:admin admin@welwitschia.ao --name="Bráulio"
$PHP artisan config:cache && $PHP artisan route:cache
```

## 6. `[cPanel]` Apontar o domínio à pasta `public`
- cPanel → **Domains** → define o **Document Root** de `welwitschia.ao` para
  `/home/welwitschia/welwitschia/public`.

## 7. `[cPanel - Terminal]` Permissões
```bash
chmod -R 775 storage bootstrap/cache
```

## 8. `[cPanel]` SSL
cPanel → **SSL/TLS Status** → executar **AutoSSL** para `welwitschia.ao` (+ www).

---

## Verificar
- Abrir `https://welwitschia.ao` → landing.
- Registar uma empresa → confirma que o **schema do tenant é criado** (testa o CREATE SCHEMA).
- Entrar em `/app` e `/admin`.

## ⚠️ Notas importantes
- **Não usar `/horizon`** (precisa de Redis, que não existe aqui). As filas estão em
  `QUEUE_CONNECTION=sync` (processam em linha — bom para piloto).
- **PostgreSQL 10 está EOL** — serve para piloto + homologação AGT; para escala, migrar
  para VPS/Proxmox com PG 16 (mesmo código, só muda o `.env`).
- cPanel partilhado tem limites de CPU/processos — adequado a arranque/piloto, não a centenas de empresas activas.

## Atualizações futuras
```bash
cd ~/welwitschia && git pull
# se os assets mudaram: recompilar no Mac (npm run build) e reenviar public/build/
PHP=/opt/cpanel/ea-php84/root/usr/bin/php
$PHP composer.phar install --no-dev --optimize-autoloader
$PHP artisan migrate --force && $PHP artisan tenants:migrate --force
$PHP artisan config:cache && $PHP artisan route:cache
```
