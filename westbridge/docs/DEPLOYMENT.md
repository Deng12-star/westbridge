# Putting WestBridge live

This takes the site from your PC to a real web address. Two routes are
covered: **shared hosting with cPanel** (cheapest, most common) and a **VPS**
(more control). Either works.

## What the server needs

| | Minimum |
|---|---|
| PHP | **8.3 or 8.4** (8.2 stops receiving security fixes at the end of 2026), with `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd` (image resizing) |
| Database | MySQL 8 or MariaDB 10.6 |
| Access | SSH / Terminal (cPanel has one under *Advanced -> Terminal*) |
| Other | Composer on the server, or upload the `vendor` folder from your PC |
| HTTPS | A certificate - cPanel's free *AutoSSL* / Let's Encrypt is fine |

## 1. Build on your PC

```bat
cd westbridge
npm run build
composer install --no-dev --optimize-autoloader
```

Zip the `westbridge` folder **without** `node_modules`, `.env`,
`database\database.sqlite` and `storage\logs\*`.

## 2. Upload

### cPanel
1. Upload the zip to your home folder (not `public_html`) and extract it, so
   you have `~/westbridge/app`, `~/westbridge/public` and so on.
2. Point the domain at the `public` folder. Either:
   - *Domains -> (your domain) -> Document root* -> `westbridge/public`, or
   - if the document root cannot be changed, **rename** (do not delete) the
     old folder and link the new one in its place:
     `mv ~/public_html ~/public_html.old && ln -s ~/westbridge/public ~/public_html`
3. *MySQL Databases*: create a database and a user, give the user ALL
   PRIVILEGES on it.

### VPS (Ubuntu + Nginx)
Put the app in `/var/www/westbridge`, set the Nginx `root` to
`/var/www/westbridge/public`, use Laravel's standard Nginx config, and
`chown -R www-data:www-data storage bootstrap/cache public/uploads`.

Nginx ignores `.htaccess`, so add this inside the `server { }` block - it
makes sure nothing in the uploads folder can ever run as code:

```nginx
location ^~ /uploads/ {
    location ~ \.php$ { return 403; }
    add_header X-Content-Type-Options nosniff always;
    try_files $uri =404;
}
```

## 3. The `.env` file on the server

Copy `.env.example` to `.env` and set at least:

```ini
APP_NAME="WestBridge Technologies"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://www.your-domain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

SESSION_SECURE_COOKIE=true
SESSION_ENCRYPT=true

QUEUE_CONNECTION=database
LOG_STACK=daily
LOG_LEVEL=warning

MAIL_MAILER=smtp
MAIL_HOST=mail.your-domain.com
MAIL_PORT=465
MAIL_USERNAME=website@your-domain.com
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=website@your-domain.com

# Only if the host sits behind Cloudflare or a load balancer:
# TRUSTED_PROXIES=*
```

`APP_ENV=production` matters: it switches on HTTPS-only links, the strict
Content-Security-Policy, HSTS, search-engine indexing (robots.txt and the
`noindex` tag are off everywhere else) and analytics.

## 4. First-time setup (in the server terminal)

```bash
cd ~/westbridge
bash scripts/deploy.sh --first-run
php artisan wb:create-admin
```

`deploy.sh` runs `php artisan wb:preflight` first and **refuses to continue**
unless `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` is https, real
email is configured, and no account has the password `password` or the
local test address. You can run `php artisan wb:preflight` yourself at any
time.

`wb:create-admin` creates the first Super Admin. **No default admin exists
in production** - `admin@westbridge.test` is created only by RUN-ME.bat on
your PC, and its seeder refuses to run anywhere else.

## 5. After that: updating the live site

Upload the changed files (or `git pull`), then:

```bash
bash scripts/deploy.sh
```

In order it: checks the configuration, **backs up the database**, puts the
site in maintenance mode, migrates, seeds (starter content only once - it
never overwrites or resurrects admin edits), rebuilds caches, checks again
and brings the site back. If any step fails it stops and **leaves the site in
maintenance mode**, and tells you where the backup is.

## 6. The one cron job (email + backups)

cPanel -> *Cron Jobs* -> every minute (`* * * * *`):

```
cd ~/westbridge && php artisan schedule:run >> /dev/null 2>&1
```

This single line drives:

| What | When |
|---|---|
| Sending queued email (new-message alerts, auto-replies) | every minute |
| Database backup, 14 kept | nightly 02:00 |
| Database + photos backup, 8 kept | Sundays 02:30 |

**Without it no email is ever sent.** The admin dashboard shows a warning
if email has been waiting more than 10 minutes.

## 7. Backups and restoring

Backups are written to `storage/app/backups` (not reachable from the web):

- `nightly-YYYYMMDD-HHMMSS.sql.gz` - the database
- `weekly-...-uploads.zip` - every product and project photo
- `pre-deploy-...` - taken automatically before each update

**Off-site copy (required).** A backup on the same server is lost with the
server. Install `rclone` (or use cPanel's *Backup* to a remote destination) and
add a second cron job, daily at 03:00:

```
rclone copy ~/westbridge/storage/app/backups remote:westbridge-backups --max-age 48h
```

Also turn on your host's own daily backups as a second layer.

**Restore (practise this once a month on a scratch database):**

```bash
php artisan down
gunzip -c storage/app/backups/nightly-XXXX.sql.gz | mysql -u USER -p DATABASE
unzip -o storage/app/backups/weekly-XXXX-uploads.zip -d public/
php artisan optimize:clear && php artisan optimize
php artisan up
```

A backup you have never restored is not a backup.

## 8. Launch checklist

- [ ] `php artisan wb:preflight` passes
- [ ] The cron job is running (dashboard shows no email warning after a test message)
- [ ] A backup exists in `storage/app/backups` and a copy exists off the server
- [ ] `APP_ENV=production`, `APP_DEBUG=false`
- [ ] HTTPS works and `http://` redirects to `https://`
- [ ] Signed in at `/admin` with the account from `wb:create-admin`
- [ ] Admin -> Settings -> Contact: WhatsApp, phone, email, address filled in
- [ ] Admin -> Settings -> Social: real page addresses only
- [ ] At least six products with photos
- [ ] Legal pages (Privacy, Terms, Warranty) written - see Admin -> Pages
- [ ] Opened a product page on a phone and tapped *Order on WhatsApp*
- [ ] Sent a test message through the Contact page and saw it in Admin -> Messages
- [ ] `https://your-domain.com/sitemap.xml` loads; submit it in Google Search Console
- [ ] Search Console verification token in Admin -> Settings -> Search & analytics
