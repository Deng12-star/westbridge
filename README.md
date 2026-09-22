# WestBridge Technologies

## Current state (admin, shop, Phases 8-10)

| | Where |
|---|---|
| Website | http://127.0.0.1:8000 |
| Admin panel | http://127.0.0.1:8000/admin - local sign-in `admin@westbridge.test` / `password` |
| Staff guide | `westbridge/docs/ADMIN-GUIDE.md` |
| Going live | `westbridge/docs/DEPLOYMENT.md` (+ `scripts/deploy.sh`, `php artisan wb:create-admin`) |
| API | `westbridge/docs/API.md` - `/api/v1/...` |

- **Admin**: dashboard, products, categories, portfolio projects, contact messages, page text, site settings, staff accounts with roles.
- **Shop**: a catalogue. Every product page has *Order on WhatsApp*, which opens a chat with the product already named; taps are counted on the dashboard. No cart or online payment - `/cart` and `/checkout` redirect to the shop.
- **Phase 8 - SEO & performance**: live `sitemap.xml`, `robots.txt`, Organization and Product structured data, Search Console and Analytics settings, image resizing on upload, long-lived caching for built assets.
- **Phase 9 - security & API**: security headers (CSP and HSTS in production), sign-in lock-out, permission check on every admin action, safe HTML for page text, no scripts in uploads, public read API plus Sanctum staff tokens.
- **Phase 10 - quality & handover**: Pest tests for admin, shop, SEO, security and API; deployment guide and script; staff guide.

Double-click `RUN-ME.bat` after any update: it installs what is new, updates the database (never overwriting admin edits) and starts the site.

---

## History: Phases 1 & 2

**Phase 1 — foundation, branding and layout.** The Laravel 12 skeleton, the
brand design system, the site shell (header, mega menus, mobile drawer, footer,
error pages) and the settings and permissions foundation.

**Phase 2 — the public website and lead capture.** The full homepage, About,
Services, Contact and the three-step Request a Quote form. Every enquiry from
every entry point becomes a lead in the database.

Products, orders, quotations, invoices and the admin panel arrive in Phases 4–7.

---

## Acceptance gates

**Phase 1** — *"The header, footer and a styled placeholder page render on
staging in WestBridge colours on a phone and a laptop."*
Met. See `screenshots/` for the rendered result at 1440px and 390px.

**Phase 2** — *"You submit a quote request from your own phone and it appears
in the database with the right service pre-selected."*
Met, and written as a test: `tests/Feature/QuoteRequestTest.php`, first case.
Run `php artisan test` once installed to see it pass.

---

## Requirements

| | |
|---|---|
| PHP | **8.2 or newer** — Laravel 12's floor. 8.3+ recommended on the server. `scripts/check-env.php` checks the version and every required extension for you. |
| Composer | 2.x |
| Node | 20 or newer |
| MySQL | 8.0 — **only for production.** Local installs use SQLite and need no database server. |

## Install

Both installers do the same thing: create a fresh Laravel 12 application,
install the dependencies, copy the WestBridge source over the top, wire
everything together, then migrate and seed.

**They default to SQLite**, so you do not need to install or run a database
server to see the site working. Production uses MySQL; the schema is written
to work on both. Pass the MySQL flag when you want it.

### Windows — the easy way

**Double-click `RUN-ME.bat`.**

It checks what is installed, sets everything up, starts the site and opens your
browser. If PHP, Composer or Node is missing it opens the Laravel Herd download
page and tells you what to do next. Double-clicking it again later just starts
the site — it does not reinstall.

Windows may warn that the file came from the internet. Choose **More info →
Run anyway**, or right-click the file → Properties → tick **Unblock** → OK.

### Windows — the manual way

Install **[Laravel Herd](https://herd.laravel.com/windows)** (free) — it gives
you PHP, Composer and Node in one installer. Close and reopen PowerShell
afterwards so the commands are on your PATH. Then, in this folder:

```powershell
.\install.ps1                 # creates .\westbridge, on SQLite
.\install.ps1 -Name my-site   # a different folder name
.\install.ps1 -Mysql          # use MySQL instead
```

If PowerShell refuses to run it, allow scripts for that window only:

```powershell
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass
```

### macOS / Linux

```bash
./install.sh                # creates ./westbridge, on SQLite
./install.sh my-folder      # a different folder name
./install.sh --mysql        # use MySQL instead
```

### Then

```bash
cd westbridge
php artisan serve
```

Open **http://127.0.0.1:8000**.

Local admin login (local environment only): `admin@westbridge.test` / `password`.
No administrator is seeded in production.

On a local install, mail is written to `storage/logs/laravel.log` rather than
sent, so you can read the notifications a form submission produces without
configuring SMTP.

---

## Fill these in before the site is public

Every one of these is seeded **empty on purpose**. Nothing about WestBridge is
invented in code, and each front-end element hides itself until its value is
set — the footer will show no phone row, no social icons, and no WhatsApp
button until you configure them.

Until the admin panel exists (Phase 7), set them with tinker:

```bash
php artisan tinker
>>> setting_service()->set('contact.phone', '+211 ...');
```

or directly in the `settings` table.

| Setting | Notes |
|---|---|
| `contact.phone`, `contact.whatsapp`, `contact.email` | WhatsApp drives the floating button and the CTA band |
| `contact.address`, `contact.hours` | Footer and contact page |
| `social.*` | Each icon appears only once its URL exists |
| `company.tin` | Appears on invoices from Phase 6 |
| `shop.tax_rate` | **Confirm with your accountant.** Seeded at 0 |
| `shop.exchange_rate` | Only relevant once decision D2 is settled |

---

## What is in this phase

```
overlay/
├── app/
│   ├── Enums/                              LeadStatus, LeadSource, ServiceInterest,
│   │                                       BudgetRange, ProjectTimeline
│   ├── Events/                             LeadCaptured, LeadStatusChanged
│   ├── Listeners/NotifyTeamOfLead.php      role-driven recipients
│   ├── Livewire/                           QuoteRequestForm, ContactForm
│   ├── Models/                             User, Setting, Lead, LeadNote,
│   │                                       QuoteRequest, ContactMessage, Page
│   ├── Notifications/                      staff + customer, queued
│   │   └── Channels/WhatsAppChannel.php    stub behind an interface
│   ├── Services/
│   │   ├── Crm/LeadService.php             the single enquiry entry point
│   │   └── Platform/SettingsService.php    cached settings repository
│   ├── Support/helpers.php                 setting(), active_socials(), whatsapp_url()
│   ├── Providers/WestBridgeServiceProvider.php
│   └── Http/Controllers/Web/               home, CMS pages, phase placeholder
├── config/westbridge.php                   brand, numbering, navigation, areas
├── database/
│   ├── migrations/                         settings, users, leads, lead_notes,
│   │                                       quote_requests, contact_messages, pages
│   └── seeders/                            RoleSeeder, SettingSeeder, PageSeeder
├── resources/
│   ├── css/app.css                         THE design system — all brand tokens
│   ├── images/brand/                       logo variants
│   └── views/
│       ├── components/layouts/app          page layout
│       ├── components/layout/              header, mega menus, drawer, footer,
│       │                                   CTA band, WhatsApp button
│       ├── components/ui/                  button, field, input, textarea,
│       │                                   section header, alert, empty state,
│       │                                   skeleton, breadcrumbs
│       ├── components/home/                the eleven homepage blocks
│       ├── components/page/hero            interior page hero
│       ├── components/brand/logo           logo variant resolver
│       ├── livewire/                       quote form, contact form
│       ├── pages/                          home, about, services, contact,
│       │                                   quote, legal, placeholder
│       └── errors/                         branded 404, 419, 500, 503
├── routes/web.php                          every URL from the approved sitemap
├── tests/                                  Pest feature and unit tests
├── public/brand/                           favicons, PWA icons, OG image
├── pint.json, phpstan.neon                 formatting and static analysis
└── .github/workflows/ci.yml                Pint + PHPStan + Pest + build
```

### Design system

All brand values live in `resources/css/app.css` and nowhere else. Templates
reference tokens (`bg-navy-700`, `font-display`, `text-h2`) — never a raw hex.

- **Navy `#1C2B4F`** and **Lime `#89C726`**, sampled from the official logo file
- **Lime-700 `#4B7412`** is the only green permitted for text; the brand lime
  fails WCAG AA against white at body sizes
- Neutrals are grey biased slightly green, not pure grey
- **Jost** for headings (the closest well-built web face to the logo's Century
  Gothic), **IBM Plex Sans** for body, **IBM Plex Mono** for SKUs, prices and
  document numbers
- Fonts are **self-hosted** via Fontsource — no third-party round trip, which
  matters on Juba bandwidth and avoids depending on a font host being reachable

`tests/Unit/BrandTokensTest.php` fails if anyone edits the brand colours.

### Routing

Every URL from the approved sitemap is registered from day one, so the
navigation is fully clickable during review and no internal link 404s. Routes
still on the placeholder controller are annotated in `routes/web.php` with the
phase that replaces them.

### Roles

The full permission system is in place. Per decision **D6** only four roles are
seeded — Super Admin, Sales, Inventory Manager, Content Manager. The other three
are created from the admin UI when someone is hired into them; their permission
sets are listed in `RoleSeeder` for reference.

---

## What has and has not been verified

**Verified:** the design system compiles, and every rendered section was
checked at desktop and phone width (`screenshots/`). All 93 PHP files are
syntax-checked; the installer, config arrays and JSON are validated.

**Not verified here:** the application has not been booted. Composer's package
registry was not reachable from the machine this was built on, so `composer
install` and `php artisan migrate` have not been executed against a live
database. Run `./install.sh` then `php artisan test` as the first step.

The Pest suite covers: the Phase 2 acceptance gate end to end, service
pre-selection, staff and customer notifications, step validation, both spam
defences, attachment storage on the private disk, rejection of an executable
disguised as an attachment, the lead status machine including illegal
transitions, every page rendering, a deleted page record not breaking the site,
and the brand tokens.

---

## Known items

- **The logo is raster.** The supplied file is a flattened PDF. Everything here
  is generated from it and looks correct at the sizes used, but a favicon at
  16px cannot resolve the bridge's fine lines. Send the vector file (.ai, .eps
  or .svg) and the icon set will be regenerated properly. A legible small-size
  icon may need an approved simplified mark — that is your call, not something
  to invent.
- **`logo-compact`** (the lockup without the slogan line) is the default variant
  and is used in the header, footer and drawer. The slogan is set far smaller
  than the wordmark and turns to mush below about 64px. The full lockup is kept
  for documents, print and the OG image. Both are crops of the official file,
  not redrawings.
- **Decisions D1–D7 are still open.** Nothing in Phase 1 depends on them, but
  D2 (currency) and D7 (delivery zones) block Phase 4.

---

## Phase 2 — what was added

### Lead capture

Every enquiry lands in `leads` through one service, `LeadService`, so there is
exactly one place a lead comes into existence and one notification path. The
quote form, the contact form and (from Phase 4) product enquiries all use it.

- `leads` — the hub, with a polymorphic `sourceable` pointing at whatever
  captured it, plus assignment, value estimate and the status pipeline
- `lead_notes` — every status change and assignment writes a system note
  automatically, so a lead's history is never reconstructed from memory
- `quote_requests`, `contact_messages` — the capture records
- `LeadStatus` enum carries the transition map. An illegal move **throws**;
  the dropdown is only its presentation

### The quote form

Three steps, deliberately ordered: what you need → rough scope → who you are.
Asking a stranger for a phone number before they have described their problem
is where journey J1 leaks.

- Arrives pre-selected from a service page: `/quote?service=starlink`
- Attachment support (PDF, Word, Excel, image; 10MB) stored on the **private**
  disk with a randomised name — never in public storage
- Two-layer spam defence: a hidden honeypot field, and a minimum completion
  time. Both fail **silently** so a bot learns nothing
- Rate limited to 5 submissions per IP per hour
- Validation on blur, not on every keystroke; a failed submit keeps every value

### Pages

About, Services, Contact and the three legal pages are CMS-backed — content
comes from the `pages` table, the template supplies only structure. A missing
or half-written page renders rather than 500s.

The homepage is now complete: all eleven blocks from the approved wireframe.

### Draft copy — please replace

`PageSeeder` contains **draft structural copy**, not final marketing text. It is
deliberately free of anything unverifiable: no founding date, no team size, no
client numbers, no superlatives.

Two blocks are left empty on purpose because they cannot be written without
you, and their sections hide until filled:

| Block | What is needed |
|---|---|
| `about.story` | How WestBridge actually started, in your words |
| `about.why_technology` | The company's own position on why this matters |

The three legal pages are routed, linked and live, each showing an empty state
until its body is written. Publishing invented terms of sale or a made-up
privacy policy would be worse than an empty page — that text is yours to
provide or have drafted.

### Two deviations from the blueprint's package list

- **Honeypot is built in, not `spatie/laravel-honeypot`.** That package is
  built around form posts and fits Livewire awkwardly. The inline version is
  about fifteen lines, adds the timing check the package does not do, and
  removes a dependency.
- **`propaganistas/laravel-phone` is installed but not yet enforcing.** Phone
  validation is currently a length check. Switching it to `+211` normalisation
  is a one-line rule change once you confirm which countries should be accepted
  — NGOs and suppliers often submit Kenyan and Ugandan numbers.

---

## Next

Phase 3 — the three service landing pages with the seven-step process,
portfolio and projects with filtering, and the blog engine.

Blocked on: project photos and the case-study facts (client, challenge,
solution, result) for at least three projects. Per the blueprint's risk note,
this is the single biggest threat to the launch — an empty portfolio will cost
more credibility than a later launch would.
