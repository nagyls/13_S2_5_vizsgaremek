# Gyakorlat – Manuális tesztelés (API tesztcase dokumentáció)

Ez a dokumentum manuális API tesztesetek rögzítésére szolgál az Eseménytér backendhez.

## 1) Tesztcase sablon (mintád alapján)

### 1.1 Alapadatok

| Mező | Leírás |
|---|---|
| Név (Name) | A teszt neve. Legyen egyértelmű és rövid. |
| Leírás (Description) | Rövid összefoglaló arról, mit ellenőriz a teszt. |
| Hiba típusa (Issue Type) | Példa: `crash`, `content`, `visual`, `validation`, `permission`, `integration`. |
| Állapot (Status) | Javasolt: `draft`, `ready`, `blocked`, `passed`, `failed`. |
| Előfeltétel (Precondition) | Mi szükséges a futtatás előtt (pl. token, seedelt adat, user szerepkör). |
| Cél (Objective) | Mit bizonyít a teszt. |
| Prioritás (Priority) | `High`, `Medium`, `Low`. |
| Felelős (Owner) | A teszt tulajdonosa. |
| Becsült idő (Estimated Time) | Átlagos végrehajtási idő (pl. 3–10 perc). |

### 1.2 Teszt script mezők

| Mező | Leírás |
|---|---|
| Steps | A tesztelő által végrehajtandó lépések. |
| Test Data | A futtatáshoz használt adatok (body, query, path param, token). |
| Expected Result | Az elvárt eredmény (HTTP státusz + kulcs válaszmezők). |
| Actual Result | A tényleges futtatási eredmény. |

---

## 2) API tesztcase-ek (kitöltött példák)

## TC-API-001 – Regisztráció sikeres

### Alapadatok

| Mező | Érték |
|---|---|
| Név | Regisztráció – sikeres |
| Leírás | Új felhasználó létrehozása érvényes adatokkal. |
| Hiba típusa | validation |
| Állapot | ready |
| Előfeltétel | Az email cím még ne létezzen az adatbázisban. |
| Cél | A `POST /api/register` végpont helyes működésének ellenőrzése. |
| Prioritás | High |
| Felelős | QA / Backend fejlesztő |
| Becsült idő | 5 perc |

### Test Script

| Steps | Test Data | Expected Result | Actual Result |
|---|---|---|---|
| 1) `POST /api/register` hívás küldése | `{ "username":"John", "email":"john.unique@example.com", "password":"Password123", "password_confirmation":"Password123" }` | `200`, válaszban: `message`, `user`, `token`, `email_verified:false` | |

---

## TC-API-002 – Login hibás jelszó

### Alapadatok

| Mező | Érték |
|---|---|
| Név | Login – hibás jelszó |
| Leírás | Létező email + rossz jelszó esetén elutasítás. |
| Hiba típusa | permission |
| Állapot | ready |
| Előfeltétel | Létező user az adatbázisban. |
| Cél | `POST /api/login` negatív ág ellenőrzése. |
| Prioritás | High |
| Felelős | QA / Backend fejlesztő |
| Becsült idő | 3 perc |

### Test Script

| Steps | Test Data | Expected Result | Actual Result |
|---|---|---|---|
| 1) `POST /api/login` hívás | `{ "email":"test@example.com", "password":"WrongPass123" }` | `401`, hibaüzenet (`Hibás jelszó!`) | |

---

## TC-API-003 – Logout token nélkül

### Alapadatok

| Mező | Érték |
|---|---|
| Név | Logout – token nélkül |
| Leírás | Auth védett végpont hívása token nélkül. |
| Hiba típusa | permission |
| Állapot | ready |
| Előfeltétel | Nincs Bearer token fejléce. |
| Cél | `DELETE /api/logout` auth védelmének ellenőrzése. |
| Prioritás | High |
| Felelős | QA |
| Becsült idő | 2 perc |

### Test Script

| Steps | Test Data | Expected Result | Actual Result |
|---|---|---|---|
| 1) `DELETE /api/logout` token nélkül | Header: nincs `Authorization` | `401` | |

---

## TC-API-004 – Régiók lekérése kereséssel

### Alapadatok

| Mező | Érték |
|---|---|
| Név | Régió keresés |
| Leírás | Keresett régiólista visszaadása query param alapján. |
| Hiba típusa | content |
| Állapot | ready |
| Előfeltétel | Seedelt régió adatok elérhetők. |
| Cél | `GET /api/regions?search=B` eredményének ellenőrzése. |
| Prioritás | Medium |
| Felelős | QA |
| Becsült idő | 3 perc |

### Test Script

| Steps | Test Data | Expected Result | Actual Result |
|---|---|---|---|
| 1) `GET /api/regions?search=B` hívás | Query: `search=B` | `200`, `data` lista, rendezett elemek | |

---

## TC-API-005 – Intézmény létrehozás sikeres

### Alapadatok

| Mező | Érték |
|---|---|
| Név | Intézmény létrehozás – sikeres |
| Leírás | Új intézmény mentése auth userrel. |
| Hiba típusa | integration |
| Állapot | ready |
| Előfeltétel | Érvényes Bearer token, létező `settlement_id`. |
| Cél | `POST /api/establishment/create` helyes működésének tesztje. |
| Prioritás | High |
| Felelős | QA / Backend |
| Becsült idő | 5 perc |

### Test Script

| Steps | Test Data | Expected Result | Actual Result |
|---|---|---|---|
| 1) `POST /api/establishment/create` hívás tokennel | `{ "title":"My School2", "description":"Short description", "settlement_id":2 }` | `201`, `message`, `data.id` | |

---

## TC-API-006 – Osztály létrehozás validációs hiba

### Alapadatok

| Mező | Érték |
|---|---|
| Név | Osztály létrehozás – hiányzó név |
| Leírás | Kötelező mező hiánya esetén validációs hiba. |
| Hiba típusa | validation |
| Állapot | ready |
| Előfeltétel | Érvényes token. |
| Cél | `POST /api/establishment/classes/create` validációjának ellenőrzése. |
| Prioritás | High |
| Felelős | QA |
| Becsült idő | 4 perc |

### Test Script

| Steps | Test Data | Expected Result | Actual Result |
|---|---|---|---|
| 1) Hívás `name` nélkül | `{ "grade":10, "establishment_id":1 }` | `422`, validációs hiba mezőszinten | |

---

## TC-API-007 – Kérelmek kezelése (accept)

### Alapadatok

| Mező | Érték |
|---|---|
| Név | Kérelmek kezelése – elfogadás |
| Leírás | Több kérelem elfogadása egy hívásban. |
| Hiba típusa | integration |
| Állapot | ready |
| Előfeltétel | Admin jogosultságú token, létező request ID-k. |
| Cél | `POST /api/establishment/requests/handle` működésének ellenőrzése. |
| Prioritás | High |
| Felelős | QA / Backend |
| Becsült idő | 6 perc |

### Test Script

| Steps | Test Data | Expected Result | Actual Result |
|---|---|---|---|
| 1) Kérelmek elfogadása | `{ "establishment_id":1, "action":"accept", "request_id":[1,2] }` | `200`, válaszban `accepted` számláló | |

---

## TC-API-008 – Esemény létrehozás dátum hiba

### Alapadatok

| Mező | Érték |
|---|---|
| Név | Esemény létrehozás – hibás dátumtartomány |
| Leírás | `end_date` korábbi, mint `start_date`. |
| Hiba típusa | validation |
| Állapot | ready |
| Előfeltétel | Érvényes token. |
| Cél | `POST /api/events` dátumvalidáció ellenőrzése. |
| Prioritás | Medium |
| Felelős | QA |
| Becsült idő | 4 perc |

### Test Script

| Steps | Test Data | Expected Result | Actual Result |
|---|---|---|---|
| 1) Hibás dátummal esemény létrehozása | `{ "type":"global", "title":"Test", "description":"Test", "start_date":"2026-03-10 10:00:00", "end_date":"2026-03-10 09:00:00" }` | `422` validációs hiba (`end_date`) | |

---

## TC-API-009 – Diákok lekérése nem létező intézménnyel

### Alapadatok

| Mező | Érték |
|---|---|
| Név | Diáklista – nem létező intézmény |
| Leírás | Publikus végpont hívása hibás intézmény azonosítóval. |
| Hiba típusa | content |
| Állapot | ready |
| Előfeltétel | Olyan `establishment_id`, ami biztosan nem létezik (pl. 999999). |
| Cél | `GET /api/members/students/{establishment}` hibakezelésének ellenőrzése. |
| Prioritás | Medium |
| Felelős | QA |
| Becsült idő | 3 perc |

### Test Script

| Steps | Test Data | Expected Result | Actual Result |
|---|---|---|---|
| 1) `GET /api/members/students/999999` hívás | Path: `establishment_id=999999` | `400`, hibaüzenet (`Intézmény nem található!`) | |

---

## TC-API-010 – Kérelmek kezelése jogosultság nélkül

### Alapadatok

| Mező | Érték |
|---|---|
| Név | Kérelmek kezelése – nem admin felhasználó |
| Leírás | Nem admin user próbál kérelmeket feldolgozni. |
| Hiba típusa | permission |
| Állapot | ready |
| Előfeltétel | Érvényes token, de a user nem admin az adott intézményben. |
| Cél | `POST /api/establishment/requests/handle` jogosultságkezelés ellenőrzése. |
| Prioritás | High |
| Felelős | QA / Backend |
| Becsült idő | 4 perc |

### Test Script

| Steps | Test Data | Expected Result | Actual Result |
|---|---|---|---|
| 1) Kérelmek kezelése nem admin tokennel | `{ "establishment_id":1, "action":"accept", "request_id":[1] }` | `403`, `Nem Felhatalmazott!` | |

---

## 3) Rövid kitöltési szabályok

- Minden teszt futás után töltsd ki az `Actual Result` mezőt.
- Sikertelen futásnál tedd mellé a HTTP státuszt és a válasz rövid részletét.
- Egy endpointhoz legalább 1 pozitív és 1 negatív teszt legyen.
- Release előtt minden `High` prioritású teszt státusza legyen `passed`.
