<template>
  <div class="aszf-page">
    <button class="back-btn" @click="$router.back()">⟵ Vissza</button>

    <div class="aszf-wrapper">
      <h1>Általános Szerződési Feltételek (ÁSZF)</h1>

      <p>
        Az EseményTér egy olyan digitális felület, amely az iskolai programok és
        rendezvények szervezését, kezelését és kommunikációját segíti.
        A rendszer használatával Ön elfogadja a jelen dokumentumban foglalt feltételeket.
      </p>

      <h2>1. A szolgáltatás igénybevétele</h2>
      <p>
        A szolgáltatás használatához regisztráció szükséges. A felhasználó köteles
        valós adatokat megadni, és a rendszert jogszerűen használni.
      </p>

      <h2>2. Felhasználói szerepkörök</h2>
      <ul>
        <li><strong>Diák:</strong> események megtekintése, jelentkezés, szavazás</li>
        <li><strong>Tanár:</strong> események létrehozása, kezelése</li>
        <li><strong>Admin:</strong> rendszerfelügyelet</li>
      </ul>

      <h2>3. Felhasználói kötelezettségek</h2>
      <ul>
        <li>bejelentkezési adatok védelme</li>
        <li>kulturált kommunikáció</li>
        <li>a rendszer rendeltetésszerű használata</li>
      </ul>

      <h2>4. Eseménykezelés</h2>
      <p>
        Az események tartalmazhatnak időpontot, helyszínt, leírást és jelentkezési feltételeket.
      </p>

      <h2>5. Adatkezelés és biztonság</h2>
      <p>
        Az adatkezelés megfelel a hatályos jogszabályoknak és a GDPR előírásainak.
      </p>

      <h2>6. Szolgáltatás módosítása</h2>
      <p>
        A szolgáltatás tartalma időről időre módosulhat.
      </p>

      <h2>7. Kapcsolat</h2>
      <p>
        Kapcsolat: support@esemenyter.hu
      </p>

      <p class="last-update">
        Utolsó frissítés: <strong>2025. január</strong>
      </p>
    </div>
  </div>
</template>

<script>
export default {};
</script>

<style scoped>
.aszf-page {
  min-height: 100vh;
  width: 100vw;

  background-image: url("./src/assets/login-img.jpg");
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;

  padding: 100px 20px 40px;
  font-family: "Poppins", sans-serif;
}



.back-btn {
  position: fixed;
  top: 20px;
  left: 20px;
  background: rgba(255,255,255,0.25);
  border: 1px solid rgba(255,255,255,0.35);
  color: #fff;
  padding: 6px 14px;
  border-radius: 999px;
  cursor: pointer;
  backdrop-filter: blur(6px);
}

.aszf-wrapper {
  max-width: 1000px;
  width: 100%;
  margin: 0 auto;
  background: rgba(255,255,255,0.15);
  backdrop-filter: blur(20px);
  border-radius: 14px;
  padding: 40px;
  color: #fff;
}



.aszf-wrapper h1 {
  text-align: center;
  margin-bottom: 30px;
}

.aszf-wrapper h2 {
  margin-top: 30px;
}

.aszf-wrapper p,
.aszf-wrapper li {
  line-height: 1.7;
  text-align: justify;
}

.last-update {
  margin-top: 40px;
  text-align: center;
  opacity: 0.8;
}

@media (max-width: 768px) {
  .aszf-wrapper {
    padding: 24px;
  }
}
</style>
