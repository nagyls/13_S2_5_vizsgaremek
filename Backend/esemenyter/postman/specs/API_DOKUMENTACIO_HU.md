# Eseménytér Backend API dokumentáció

## 1) Állapotfelmérés (Postman review)

A Postman collectionök normalizálása elkészült, így már jó alapot adnak a publikálható dokumentációhoz.

### Jól működő / használható csomagok
- `autentikáciok` (login/logout/register) – alap auth flow benne van.
- `regio apik` – régió / kistérség / település lekérdezések több példával.
- `class` – osztálykezelés fő műveletei (létrehozás, lista, tagok, tanár csere).
- `kérelmek` – kérelem listázás és kezelés alapjai megvannak.

### Elvégzett javítások
1. **Token kezelés egységesítve**: minden authos collection `{{token}}` változót használ.
2. **Base URL egységesítve**: minden request `{{base_url}}` alapú.
3. **`eventek` collection kiegészítve**: URL + Bearer auth + JSON headerek beállítva.
4. **Útvonalak route-hoz igazítva**:
   - `POST /api/establishment/create`
   - `POST /api/establishment/{establishment}/requests`
5. **Collection szintű változók hozzáadva**: `base_url`, `token`, és ahol szükséges `establishment_id`, `class_id`.

### Még nyitott pontok
1. **`GET /api/events` duplikált route** a backendben (két handler).
2. **Válaszminták hiányoznak** több requestből (Postman `response` mezők üresek).
3. **`New Collection` üres**: törölhető vagy későbbi bővítésre megtartható.

### Route oldali megjegyzés
- `GET /api/events` duplán szerepel a route fájlban (két külön handlerrel). Dokumentáció előtt döntsetek, melyik a tényleges/elfogadott végpont viselkedés.

---

## 2) Javasolt dokumentációs struktúra (magyar)

Készíts egy központi API-doksit az alábbi fejezetekkel:

1. **Bevezetés**
   - API célja
   - alap URL (`base_url`)
   - verziózás (ha van)

2. **Hitelesítés**
   - login, token használat (Bearer)
   - auth szükséges / nem szükséges endpointok
   - tipikus hibák (`401`, `403`)

3. **Erőforrások szerint endpointok**
   - Auth
   - Régiók
   - Intézmények
   - Osztályok
   - Tagok (diák/tanár)
   - Kérelmek
   - Események

4. **Hibakezelés**
   - standard státuszkódok (`200`, `201`, `204`, `400`, `401`, `403`, `404`, `422`, `500`)
   - validációs hibaformátum

5. **Mintafolyamatok (use-case-ek)**
   - Regisztráció → login → tokenes hívás
   - Intézményhez csatlakozási kérelem és kezelése
   - Osztály létrehozása és diák hozzárendelése

---

## 3) Endpoint sablon (másold minden végponthoz)

```md
### [METHOD] /api/...

**Leírás:** Rövid funkcióleírás.

**Auth:** Igen/Nem (Bearer token)

**Path paraméterek:**
- `id` (integer) – ...

**Query paraméterek:**
- `search` (string, optional) – ...

**Request body:**
```json
{
  "...": "..."
}
```

**Sikeres válasz (200/201):**
```json
{
  "...": "..."
}
```

**Hibák:**
- `401` – nincs vagy hibás token
- `422` – validációs hiba
- `404` – nem található erőforrás
```

---

## 4) Következő lépések (ajánlott sorrend)

1. **Postman Environment létrehozása (kötelező):**
   - `base_url`, `token` (és opcionálisan `establishment_id`, `class_id`)
   - ezeket állítsd be aktív környezetben (Local/Dev)

2. **Példaválaszok rögzítése (fontos):**
   - minden requesthez legalább 1 sikeres és 1 hiba példa

3. **Automatikus publikálás Postmanből:**
   - collection leírások kitöltése magyarul
   - Postman “Generate Documentation” használata

4. **Repo-ba mentett hivatalos doksi:**
   - ez a fájl maradjon a „forrás igazság” (`postman/specs/API_DOKUMENTACIO_HU.md`)
   - release előtt frissítés kötelező

---

## 5) Rövid ellenőrzőlista (vizsga/átadás előtt)

- [ ] Nincs hardcode token a collectionökben
- [ ] Minden URL változós (`{{base_url}}`)
- [ ] Minden route dokumentálva van
- [ ] Auth requirement minden végpontnál jelölve
- [ ] Van request + response példa minden végponthoz
- [ ] Van legalább 1 end-to-end folyamatleírás
