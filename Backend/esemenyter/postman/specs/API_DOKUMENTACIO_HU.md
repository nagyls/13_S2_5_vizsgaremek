# Eseménytér – API teszt dokumentáció 

Ez a dokumentum a backend API kézi és Postman alapú teszteléséhez készült.

## 1. Tesztkörnyezet

- **Base URL:** `http://127.0.0.1:8000`
- **API prefix:** `/api`
- **Auth:** Laravel Sanctum Bearer token
- **Fejlécek:**
   - `Accept: application/json`
   - `Content-Type: application/json` (ahol van body)

### 1.1 Postman environment változók

- `base_url`
- `token`
- `establishment_id`
- `class_id`
- `event_id`

### 1.2 Aktuális Postman collection nevek

- `autentikáciok`
- `Region/InnerRegion/Settlement apik`
- `Establishment apik`
- `Class apik`
- `Tagok lekérése`
- `Request apik`
- `Event apik`

## 2. Tesztfuttatási sorrend

1. Regisztráció
2. Bejelentkezés (token mentése)
3. Nyilvános lekérdező API-k (régiók, intézmények)
4. Intézmény létrehozás
5. Kérelmek feladása/kezelése
6. Osztály műveletek
7. Esemény létrehozás/listázás
8. Kijelentkezés

## 3. Tesztesetek végpontonként

Az alábbiaknál minden végponthoz legalább egy **pozitív** és egy **negatív** teszt futtatása javasolt.

---

### 3.1 Auth

#### `POST /api/register`

**Cél:** Új felhasználó létrehozása.

**Pozitív test body példa:**
```json
{
   "username": "Teszt Elek",
   "email": "teszt.elek@example.com",
   "password": "Password123",
   "password_confirmation": "Password123"
}
```

**Elvárt eredmény:**
- `200`
- `message`, `user`, `token`, `email_verified` mezők a válaszban

**Negatív tesztek:**
- Már létező email -> `422`
- Gyenge jelszó (pl. nincs szám/nagybetű) -> `422`
- Hiányzó `password_confirmation` -> `422`

#### `POST /api/login`

**Cél:** Token igénylése.

**Pozitív body:**
```json
{
   "email": "teszt.elek@example.com",
   "password": "Password123"
}
```

**Elvárt eredmény:**
- `200`
- `token` mező létezik

**Negatív tesztek:**
- Nem létező email -> `422`
- Hibás jelszó -> `401`

#### `DELETE /api/logout` *(auth szükséges)*

**Cél:** Aktuális token törlése.

**Elvárt eredmény:**
- Érvényes tokennel `200`
- Token nélkül `401`

---

### 3.2 Email verifikáció *(auth szükséges)*

#### `GET /api/email/verify/{id}/{hash}`

**Cél:** Email megerősítése aláírt URL-lel.

**Teszt:**
- Érvényes signed URL + token -> `200`
- Érvénytelen/lejárt hash -> `403` vagy `401`

#### `POST /api/email/resend`

**Cél:** Megerősítő email újraküldése.

**Teszt:**
- Hitelesített user -> `200`
- 1 percen belül 6 hívás fölött throttle -> `429`

#### `GET /api/email/verification-status`

**Cél:** Verifikált állapot lekérdezése.

**Elvárt eredmény:**
- `200`
- `{ "verified": true|false }`

---

### 3.3 Régió / kistérség / település (publikus)

#### `GET /api/regions/all`
#### `GET /api/regions/{id}`
#### `GET /api/regions?search=B`
#### `GET /api/innerregions/all?region_id=1`
#### `GET /api/innerregions?search=g&region_id=1`
#### `GET /api/settlements/all?inner_region_id=1`
#### `GET /api/settlements?search=&inner_region_id=1`

**Elvárt eredmény (pozitív):** `200`, rendezett lista a `data` mezőben.

**Negatív tesztjavaslat:**
- Hiányzó kötelező szűrő paramétereknél üres lista (`data: []`) ellenőrzése

---

### 3.4 Intézmények

#### `GET /api/establishments?search=p&settlement_id=1`
#### `GET /api/establishments/{id}`

**Cél:** Intézmények keresése/lekérdezése.

**Elvárt eredmény:** `200`, `data` mező.

#### `POST /api/establishment/create` *(auth szükséges)*

**Pozitív body példa:**
```json
{
   "title": "Minta Gimnázium",
   "description": "Intézményi leírás",
   "settlement_id": 2,
   "website": "https://pelda.hu",
   "email": "info@pelda.hu",
   "phone": "+3612345678",
   "address": "1111 Budapest, Fő utca 1."
}
```

**Elvárt eredmény:**
- `201`
- `message`, `data`

**Negatív tesztek:**
- Ugyanazzal a `title`-lel újra -> `422`
- Nem létező `settlement_id` -> `422`
- Rossz `website` URL -> `422`

#### `GET /api/establishment/mine` *(auth szükséges)*

**Elvárt eredmény:** `200`, saját intézmények listája.

---

### 3.5 Tagok (publikus)

#### `GET /api/members/students/{establishment}`
#### `GET /api/members/staff/{establishment}`

**Elvárt eredmény:**
- Létező intézményre `200`, `data` lista
- Nem létező intézményre `400`, hibaüzenet

---

### 3.6 Osztályok *(auth szükséges)*

#### `POST /api/establishment/classes/create`

**Pozitív body:**
```json
{
   "name": "A",
   "grade": 10,
   "capacity": 30,
   "establishment_id": 1,
   "user_id": null
}
```

**Elvárt eredmény:** `201`, `class` objektum.

**Negatív teszt:**
- Hiányzó `name` vagy nem létező `establishment_id` -> `422`

#### `GET /api/establishment/{establishment}/classes`

**Elvárt eredmény:**
- Staff/admin tagként `200`
- Nem jogosult userként `403`

#### `GET /api/establishment/{establishment}/classes/{class}`

**Elvárt eredmény:**
- Jogosult tagként `200`
- Nem létező osztály/intézmény -> `404`
- Hibás intézmény-osztály kapcsolat -> `400`

#### `POST /api/establishment/classes/add-students`

**Pozitív body:**
```json
{
   "establishment_id": 1,
   "class_id": 2,
   "student_id": [2, 3]
}
```

**Elvárt eredmény:**
- Siker esetén `200`, üzenet
- Már osztálytag diáknál `409`
- Nem admin usernél `403`

#### `PATCH /api/establishment/{establishment}/classes/{class}`

**Megjegyzés (fontos):** A route path paraméteres, de a controller body-ban várja az `establishment_id` és `class_id` mezőket is.

**Javasolt body a jelenlegi implementációhoz:**
```json
{
   "establishment_id": 1,
   "class_id": 2,
   "teacher_id": 3
}
```

**Elvárt eredmény:**
- `200` sikeres hozzárendelés
- Nem intézményi admin -> `403`
- Nem intézményi tanár -> `400`

#### `POST /api/establishment/classes/remove-students`

**Megjegyzés:** Route létezik, de a `StudentController` jelenlegi kódban nincs hozzá `removeFromClass` metódus. Ez a végpont jelen állapotban hibát adhat.

---

### 3.7 Kérelmek *(auth szükséges)*

#### `POST /api/establishment/{establishment}/requests`

**Megjegyzés:** A route path paraméteres, de a controller a body `establishment_id` mezőt validálja. Teszteléskor add meg body-ban is.

**Pozitív body:**
```json
{
   "establishment_id": 1,
   "role": "student"
}
```

**Elvárt eredmény:**
- `201` új kérelem
- Már létező kérelem vagy tagság -> `409`

#### `GET /api/establishment/{establishment}/requests/students`
#### `GET /api/establishment/{establishment}/requests/teachers`

**Elvárt eredmény:**
- Intézményi adminként `200`
- Nem adminként `403`

#### `POST /api/establishment/requests/handle`

**Pozitív body példa:**
```json
{
   "establishment_id": 1,
   "action": "accept",
   "request_id": [1, 2]
}
```

**Elvárt eredmény:**
- `200`, `accepted` vagy `rejected` számláló
- Hibás `action` (`accept`/`reject`-en kívül) -> `422`
- Nem adminként -> `403`

#### `DELETE /api/establishment/{establishment}/requests/revoke`

**Elvárt eredmény:**
- Saját kérelem esetén `200`
- Nem létező kérelem esetén `404`

---

### 3.8 Események *(auth szükséges)*

#### `POST /api/events`

**Pozitív body:**
```json
{
   "type": "global",
   "title": "Nyílt nap",
   "description": "Leírás",
   "content": "Részletes tartalom",
   "start_date": "2026-03-01 10:00:00",
   "end_date": "2026-03-01 12:00:00"
}
```

**Elvárt eredmény:**
- `201`
- `message`, `event`

**Negatív tesztek:**
- `type` nem `local/global` -> `422`
- `end_date < start_date` -> `422`

#### `GET /api/events`

**Megjegyzés (fontos):** A route fájlban ez a GET végpont duplikáltan szerepel (`getEvents` és `index`). Működés backend route sorrendtől függhet.

**Elvárt minimum:**
- `200`
- Eseménylista (`events` vagy más lista jellegű kulcs)

#### `GET /api/events/{event}`

**Megjegyzés:** A route definiálva van, de a jelenlegi `EventController` fájlban nem látható `show()` metódus. Ez hibát okozhat, amíg nincs implementálva.

---

## 4. Általános hibaellenőrzés

Minden auth védett végpontra futtasd:

1. **Token nélkül** -> `401`
2. **Érvénytelen tokennel** -> `401`
3. **Jogosultság nélküli userrel** -> `403` (ahol releváns)

Validációs végpontokra:

1. Hiányzó kötelező mező -> `422`
2. Hibás adattípus -> `422`
3. Nem létező idegen kulcs (`id`) -> `422` vagy `400` implementációtól függően

## 5. Postman teszt script alap (opcionális)

```javascript
pm.test("Sikeres státusz", function () {
   pm.expect(pm.response.code).to.be.oneOf([200, 201]);
});

pm.test("JSON válasz", function () {
   pm.response.to.have.header("Content-Type");
   pm.expect(pm.response.headers.get("Content-Type")).to.include("application/json");
});
```

## 6. Ismert technikai eltérések (jelenlegi kódbázis)

1. `GET /api/events` duplikált route.
2. `POST /api/establishment/classes/remove-students` route mögött hiányzó controller metódus.
3. `GET /api/events/{event}` route mögött hiányzó `show()` (jelenlegi fájl alapján).
4. Bizonyos route-ok path paraméteresek, de a controller body mezőt is megkövetel (`establishment_id`, `class_id`).

Ezért a tesztelésnél a route és a controller validáció együttes viselkedése alapján kell elfogadni az eredményt.
