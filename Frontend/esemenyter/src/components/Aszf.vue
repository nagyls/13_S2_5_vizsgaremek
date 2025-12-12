<template>
    <div class="aszf-page">
      <button class="back-btn" @click="$router.back()" title="Vissza az előző oldalra">⟵ Vissza</button>
        <div class="aszf-wrapper">
            <h1>Általános Szerződési Feltételek (ÁSZF)</h1>

            <p>
                Az EseményTér egy olyan digitális felület, amely az iskolai programok és rendezvények
                szervezését, kezelését és kommunikációját segíti. A platform célja, hogy a tanárok
                egyszerűen létrehozhassanak eseményeket, a diákok pedig átlátható módon 
                jelentkezhessenek, szavazhassanak és visszajelzést adhassanak.
                A rendszer használatával Ön elfogadja a jelen dokumentumban foglalt feltételeket.
            </p>

            <h2>1. A szolgáltatás igénybevétele</h2>
            <p>
                A rendszer használatához a felhasználóknak regisztráció szükséges. A regisztráció során
                megadott adatoknak valósnak és pontosnak kell lenniük. A felhasználó vállalja, hogy
                az EseményTér rendszerét nem használja jogellenes vagy másokat sértő módon.
                A szolgáltatás több intézményt is képes kiszolgálni, így minden iskola saját
                felhasználói körrel rendelkezik.
            </p>

            <h2>2. Felhasználói szerepkörök</h2>
            <p>
                Az EseményTér három fő szerepkört különböztet meg:
            </p>
            <ul>
                <li><strong>Diák:</strong> események megtekintése, jelentkezés, szavazás, hozzászólás.</li>
                <li><strong>Tanár:</strong> események létrehozása, módosítása, résztvevők kezelése, szavazások indítása.</li>
                <li><strong>Admin:</strong> iskolák kezelése, tanárok és diákok fiókjainak felügyelete, rendszerbeállítások módosítása.</li>
            </ul>
            <p>
                A jogosultsági szintek meghatározzák, ki milyen funkciókat érhet el a rendszerben.
            </p>

            <h2>3. Felhasználói kötelezettségek</h2>
            <p>
                A felhasználó köteles:
            </p>
            <ul>
                <li>gondoskodni a bejelentkezési adatok biztonságáról,</li>
                <li>nem megosztani jelszavát harmadik féllel,</li>
                <li>a rendszerben kulturáltan kommunikálni (kommentelés, részvételi visszajelzések),</li>
                <li>a platformot a rendeltetésének megfelelően használni.</li>
            </ul>
            <p>
                A visszaélések a fiók felfüggesztését vagy törlését vonhatják maguk után.
            </p>

            <h2>4. Eseménykezelés</h2>
            <p>
                A tanárok által létrehozott események tartalmazhatnak időpontot, helyszínt, 
                leírást, jelentkezési feltételeket és opcionális szavazásokat.  
                A diákok megtekinthetik az eseményeket, jelentkezhetnek és hozzászólhatnak azokhoz.
                A rendszer minden módosítás esetén értesítést küld a résztvevőknek.
            </p>

            <h2>5. Adatkezelés és biztonság</h2>
            <p>
                A megadott személyes adatokat az Adatvédelmi Tájékoztatóban foglaltak szerint kezeljük.
                A jelszavak a rendszerben titkosítva tárolódnak.  
                Az adatkezelés megfelel a hatályos jogszabályoknak és GDPR-alapelveknek.
            </p>

            <h2>6. Szolgáltatás módosítása</h2>
            <p>
                Az EseményTér rendszer folyamatos fejlesztés alatt áll. A funkciók, felület vagy 
                szolgáltatások bármikor módosulhatnak. Jelentősebb változás esetén a felhasználók
                értesítést kapnak.
            </p>

            <h2>7. Kapcsolat</h2>
            <p>
                Amennyiben a szolgáltatással kapcsolatban kérdése vagy problémája van,
                lépjen kapcsolatba velünk az alábbi e-mail címen:
            </p>

            <p>
                A jelen ÁSZF legutóbb frissítve: <strong>2025. január</strong>
            </p>
        </div>
    </div>
</template>

<script>
</script>

<style scoped>
.back-btn {
    position: fixed;
    top: 20px;
    left: 20px;

    display: flex;
    align-items: center;
    gap: 6px;

    background: rgba(255, 255, 255, 0.25);
    border: 1px solid rgba(255, 255, 255, 0.35);
    color: #fff;

    padding: 6px 14px;
    font-size: 16px;
    border-radius: 999px;
    cursor: pointer;

    backdrop-filter: blur(6px);
    transition: 0.25s ease;
}

.back-btn:hover {
    background: rgba(255, 255, 255, 0.4);
}

.aszf-page {
    box-sizing: border-box;
    font-family: "Poppins", sans-serif;

    display: flex;
    justify-content: center;
    align-items: center;
    
    min-height: 100vh;
    width: 100vw;

    background-image: url("./src/assets/login-img.jpg"); 
    background-position: center;
    background-size: cover; 
}
.aszf-wrapper {
    width: 90vw;
    max-height: 80vh;

    background: transparent;
    border: 2px solid rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(20px);
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    color: #fff;
    border-radius: 10px;

    padding: 30px 40px;

    column-count: 2;
    column-gap: 40px;

    overflow-y: auto;
}

.aszf-wrapper h1,
.aszf-wrapper h2 {
    break-inside: avoid;
    break-after: avoid;
}

.aszf-wrapper p {
    line-height: 1.6;
    text-align: justify;
}

@media (max-width: 900px) {
    .aszf-wrapper {
        column-count: 1;
        padding: 20px;
    }
}


.aszf-wrapper h1 {
    font-size: 36px;
    text-align: center;
    font-weight: bold;
}

.aszf-wrapper .input-box {
    position: relative;
    width: 100%;
    height: 50px;
    margin: 30px 0;
}
.input-box input {
    width: 100%;
    height: 100%;
    background: transparent;
    border: none;
    outline: none;
    border: 2px solid rgba(255, 255, 255, 0.2);
    border-radius: 40px;
    padding-left: 20px;
    color: #fff;
}

.input-box input::placeholder {
    color: #fff;
}

.input-box i {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    
    font-size: 20px;
}

.aszf-wrapper .remember-forgot {
    display: flex;
    justify-content: space-between;
    font-size: 14.5px;
    margin: -15px 0 15px;
}
.remember-forgot label input {
    accent-color: #fff;
    margin-right: 3px;
}
.remember-forgot a:hover , .remember-forgot a {
    text-decoration: underline;
    color: #fff;
}
.aszf-wrapper .btn {
    width: 100%;
    height: 45px;
    background: #fff;
    border: none;
    outline: none;
    border-radius: 40px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    font-size: 16px;
    color: #333;
    font-weight: 600;

    transition: background 0.5s ease, color 0.5s ease;
}


.aszf-wrapper .register-link {
    font-size: 14.5px;
    text-align: center;
    margin-top: 4px;
}
.register-link p a {
    color: #fff;
    text-decoration: none;
    font-weight: 600;
}
.register-link p a:hover {
    text-decoration: underline;
}
    
.btn:hover {
  background: rgb(40, 40, 59);
  color: #fff;
}
</style>