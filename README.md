# StructureOps — SaaS platforma za investitore stambene izgradnje (MVP)

Implementacija PRD verzije 1.0: **Laravel 12 (PHP 8.2+) + MySQL + Blade/Tailwind (CDN, bez build koraka)**.

## Šta je urađeno po PRD-u

| PRD zahtev | Implementacija |
|---|---|
| Sekcija 2 — nula automatike | Nema eksternih integracija, nema automatskih statusa/roka/dodela. Jedina "automatika" dozvoljena PRD-om: zbir checklist stavki, vizuelni predlog, oduzimanje datuma ("kasni X dana"). |
| Sekcija 6 — fiksne liste | Sve u `config/statusi.php` — jedini izvor istine, validacija kroz `App\Support\FiksneListe` (prevlacenje: string sa zarezima u Rule::in). |
| Sekcija 5 — matrica dozvola | `EnsureRole` middleware + provere u kontrolerima (Nadzor vidi samo svoje dodele; hard delete projekta samo Vlasnik; Admin ne menja ulogu Vlasniku). |
| Sekcija 9 — ekrani | Dashboard (4 kartice, tabela, to-do), Dosije zgrade (5 tabova), Stanovi (tabela + side-over panel, 3 sekcije), Checkliste (3 fiksne procesa + progress), Reklamacije (tabela + filteri + detalj sa logom), Korisnici. |
| 12.2 — multi-tenancy | Shared database/shared schema, `tenant_id` + globalni Eloquent scope (`App\Scopes\TenantScope`). |
| 12.1/12.3 — migracija ka Node/AWS | API-first (`routes/api.php`, isti kontroleri vraćaju JSON), DB bez stored procedura, storage preko Laravel Storage facade-a (`documents` disk local → `FILESYSTEM_DISK=s3` bez izmene koda). |
| 11 — sigurnost | Audit log (`audit_logs`), RBAC middleware, CSRF, validacija, IDOR zaštita kroz tenant scope. |

## Instalacija na cPanel (shared hosting)

1. **Baza:** kroz cPanel MySQL kreirati bazu i korisnika; ubaciti podatke u `.env`.
2. **Upload:** sadržaj ZIP-a u `public_html` (ili poddomen). Document root mora biti `public/` — na cPanelu to znači da se aplikacione fajlove drži u folderu iznad (npr. `/home/USER/structureops/`) i da se `public/` mapira kao web root (cPanel "document root" opcija), ili koristiti poddomen sa posebnim document root-om.
3. **Composer:** `composer install --no-dev --optimize-autoloader` (preko cPanel Terminala ili SSH).
4. **Konfiguracija:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   php artisan storage:link          # ako se koristi public disk
   php artisan migrate:fresh --seed  # seed puni demo podatke (tenant, korisnici, projekti, stanovi, reklamacije)
   ```
5. **Prava pristupa:** `storage/` i `bootstrap/cache/` moraju biti writable (755/775).
6. **Login (seed):** `admin@structureops.rs` / `password` (Administrator — Jelena Simić), `vlasnik@...`, `operater@...`, `nadzor@...` — sve sa lozinkom `password`. **Promeniti odmah.**

## Struktura

```
app/
  Http/Controllers/   # Auth, Dashboard, Project, Building, Unit, Document, Checklist, Claim, User
  Http/Middleware/    # EnsureRole (RBAC po PRD matrici)
  Models/             # Tenant, User, Customer, Project, Building, Unit, Document(+Type), Checklist(+Item), Claim(+Note,+File), AuditLog
  Models/Concerns/    # BelongsToTenant (globalni TenantScope + auto-set tenant_id)
  Scopes/             # TenantScope (shared-schema izolacija)
  Services/           # ChecklistService (seed checklisti po PRD 6.6)
  Support/            # FiksneListe (validacija fiksnih lista)
config/
  statusi.php         # SVE fiksne liste (PRD sekcija 14)
  checklists.php      # Startne stavki checklisti (PRD 6.6)
database/migrations/  # Kompletan data model (PRD sekcija 6)
database/seeders/     # Demo tenant + podaci iz dizajna
resources/views/      # Blade šabloni po dizajnu (Tailwind CDN, Hanken Grotesk/JetBrains Mono)
routes/web.php        # Web (Blade) rute
routes/api.php        # API-first sloj za buduću Node.js migraciju
```

## Put ka Node.js + AWS (PRD 12.3) — šta je već spremno

- **API-first:** isti kontroleri servisiraju web i `/api/*` — Node (NestJS) može postepeno preuzimati module uz isti contract.
- **DB neutralna:** obične relacije, bez stored procedura, MySQL → Postgres migrira se alatom (npr. pgloader).
- **Storage apstrakcija:** dokumenti kroz `Storage::disk('documents')` — prelazak na S3 = `.env` promena (`FILESYSTEM_DISK=s3` + AWS ključevi).
- **Queue:** database queue spremna za Redis (ElastiCache) kasnije.
- **Fajlovi van baze**, u bazi samo metapodaci i putanja (PRD 12.1).

## Napomene / ograničenja MVP-a

- Tailwind se učitava preko CDN-a (bez npm/build koraka) — dovoljno za MVP na cPanelu; za produkciju uz veći saobraćaj preći na buildovani CSS.
- Autentikacija API-ja je sesijska (isti sajt); token auth (Sanctum) je Faza 2.
- Billing, customer portal, custom checkliste: van obima MVP-a (PRD 3.2 / 13).
- Nije implementirano "arhiviranje" UI dugmiće — polje `arhiviran` postoji na svim entitetima i filteri ga poštuju; UI dugmad se lako dodaju.

*Kraj dokumentacije.*
