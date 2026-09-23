## O projektu

StructureOps = **platforma B** (privatni SaaS za investitore, Laravel 12 / PHP 8.2+ / MySQL),
poddomen `investitori.temelj.info`. Povezuje se sa **platformom A** (javni Temelj,
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
