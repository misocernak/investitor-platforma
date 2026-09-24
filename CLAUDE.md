## O projektu

StructureOps = **platforma B** (privatni SaaS za investitore, Laravel 12 / PHP 8.2+ / MySQL),
poddomen `investitor.temelj.info`. Povezuje se sa **platformom A** (javni Temelj,
lokalno u `C:\Users\User\Desktop\temelj`) preko API-ja — nikad direktnim pristupom
bazama. Veza investitora A ↔ B ide preko matičnog broja (MB) uz verifikaciju.

## graphify

Ovaj projekat ima graf znanja u graphify-out/.

- Za pitanja o kodu prvo `graphify query "<pitanje>"`, `graphify path "<A>" "<B>"`, `graphify explain "<pojam>"`.
- Za Temelj (platforma A) koristi graf u `C:\Users\User\Desktop\temelj\graphify-out` (pokreni graphify iz tog foldera).
- Posle izmene koda: `graphify update .`

## Git / deploy

Nakon SVAKE izmene koda koju proceniš da je gotova, automatski uradi:

    git add -A
    git commit -m "<kratak opis izmene>"
    git push

Ne pitaj za dozvolu za ove git komande.
Izuzetak: ako izmena dodaje ili menja migraciju baze (database/migrations), NE push-uj
automatski dok korisnik ne potvrdi da je spreman da je pokrene na serveru.

Push na `main` automatski deploy-uje na server (.github/workflows/deploy.yml).

## Objašnjenja

Korisnik je početnik za server/GitHub deo — uputstva za ručne korake (cPanel, GitHub
podešavanja) piši korak po korak, jednostavnim jezikom.

## Izgled (CSS i ikonice)

- CSS se pravi na GitHub-u pri deploy-u (`npm run build:css` → `public/css/app.css`), tokeni su u `tailwind.config.js`. Nema Tailwind CDN-a.
- Ikonice (Material Symbols) se učitavaju samo sa spiska u `config/ikonice.php`. Nova ikonica u ekranu = dodati je i tamo (abecedno), inače se ne prikazuje.
- Statusi se prikazuju kroz `App\Support\Prikaz` i `<x-status :v="..."/>`, nikad sirovo.
- Ekrani se grade od zajedničkih delova: CSS klase `kartica`, `dugme-primarno` / `dugme-sekundarno` / `dugme-tiho`, `polje`, `oznaka`, `tabela`, `cip` (resources/css/app.css) i komponente `<x-zaglavlje>`, `<x-pokazatelj>`, `<x-modal>`, `<x-polje>`, `<x-prazno>`.
- Bez bočnih panela za detalje (korisnici rade na 14–15" laptopovima): lista → zasebna stranica detalja (npr. `units.show`, `claims.show`); unos ide u `<x-modal>`.

## Posle deploy-a (korisnik pokreće u cPanel Terminalu)

    cd ~/investitor.temelj.info && php artisan config:cache && php artisan route:cache && php artisan view:cache

Obavezno kad se menjaju rute ili config — keširane rute inače ne vide novu rutu (500 greška).

## Oglasi na Temelju (veza sa Temelj.rs)
- Server–server JSON POST potpisan HMAC-om (`App\Services\TemeljApi`); ključ `TEMELJ_API_KLJUC` samo u `.env`, isti kao `investitor_api.kljuc` u Temelj `app/config.php`.
- Temelj → ovde: `POST /api/temelj/upit`, `POST /api/temelj/veza`. Ovde → Temelj: `/api/v1/veza`, `/api/v1/veza/status`, `/api/v1/oglasi`, `/api/v1/upiti/preuzmi`, `/api/v1/profil`.
- Recenzije kupaca (`/recenzije`, Vlasnik/Administrator): čitaju se sa Temelja (`/api/v1/recenzije`), a javni odgovor firme ide na `/api/v1/recenzije/odgovor` (prazan tekst = brisanje). Ovde se ništa ne čuva.
- Opis firme i veb-sajt (profil investitora na Temelju) uređuju se na `/temelj/profil`, a čuvaju SAMO na Temelju (`investitori.opis`/`sajt`) — ovde nema kolone, stranica ih čita i šalje preko `/api/v1/profil`.
- Logika u `App\Services\OglasiNaTemelju`; izmena stana/zgrade/projekta automatski šalje oglas, prodat stan skida oglas.
- Fotografije oglasa su u `public/oglasi-slike/` (disk `oglasi`), Temelj ih preuzima po URL-u (APP_URL mora biti tačan domen).

## Registracija firmi i admin platforme
- Raskid vlasništva (platforma → firma → "Raskini vlasništvo"): Temelj `/api/v1/veza/raskini` skida vezu i oglase (opciono briše opis/sajt), firma dobija status `raskinut`, a MB je slobodan za novu registraciju.
- Firma se registruje sama (`/registracija`): MB → podaci iz APR-a preko Temelja (`/api/v1/firma`), lice, funkcija, punomoćje (obavezno samo za ovlašćeno lice). Status firme: `na_cekanju` → `aktivan` / `odbijen` / `suspendovan`.
- Odobrava **admin platforme** (uloga `Platforma`, `tenant_id` = null, panel `/platforma`). Odobrenje je jedina provera: aktivira firmu i šalje `odobreno_na_platformi` Temelju (veza odmah odobrena, profil investitora se pravi ako ne postoji).
- `TenantScope`: prijavljen korisnik bez firme ne vidi NIJEDAN podatak firmi (`1 = 0`). Middleware `firma` pušta u aplikaciju samo aktivne firme.
- Nalog admina platforme: `php artisan platforma:admin email@adresa` (ispisuje privremenu lozinku).
