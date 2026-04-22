<template>
  <div class="privacy-page">
    <button class="back-btn" @click="$router.back()">
      <i class='bx bx-arrow-back'></i> Vissza
    </button>

    <div class="privacy-wrapper">
      
      <h1>Adatvédelmi Tájékoztató</h1>

      <p class="intro">
        Az EseményTér elkötelezett a felhasználók személyes adatainak védelme iránt.
        Jelen Adatvédelmi Tájékoztató bemutatja, hogy milyen adatokat gyűjtünk,
        milyen célból kezeljük azokat, valamint milyen jogok illetik meg a
        felhasználókat az adatkezeléssel kapcsolatban.
      </p>

      <h2>1. Az adatkezelő adatai</h2>
      <p>
        Az adatkezelő az EseményTér platform üzemeltetője.
        Az adatkezeléssel kapcsolatos kérdésekben az alábbi elérhetőségen
        lehet kapcsolatba lépni velünk:
      </p>
      <p class="highlight">
        E-mail: <a href="mailto:support@esemenyter.hu">support@esemenyter.hu</a>
      </p>

      <h2>2. Kezelt személyes adatok köre</h2>
      <p>
        A regisztráció és a szolgáltatás használata során az alábbi személyes
        adatokat kezeljük:
      </p>
      <ul>
        <li>felhasználónév,</li>
        <li>e-mail cím,</li>
        <li>jelszó (titkosított formában),</li>
        <li>felhasználói szerepkör (diák, tanár, admin),</li>
        <li>az eseményekhez kapcsolódó aktivitások (jelentkezések, szavazások).</li>
      </ul>

      <h2>3. Az adatkezelés célja</h2>
      <p>
        A személyes adatok kezelésének célja:
      </p>
      <ul>
        <li>a felhasználói fiók létrehozása és kezelése,</li>
        <li>az események szervezésének és lebonyolításának biztosítása,</li>
        <li>a felhasználók azonosítása és jogosultságainak kezelése,</li>
        <li>a rendszer biztonságos működésének fenntartása.</li>
      </ul>

      <h2>4. Az adatkezelés jogalapja</h2>
      <p>
        Az adatkezelés jogalapja a felhasználó önkéntes hozzájárulása,
        valamint a szolgáltatás nyújtásához szükséges szerződés teljesítése.
        Bizonyos esetekben jogszabályi kötelezettség is indokolhatja az
        adatkezelést.
      </p>

      <h2>5. Adatbiztonság</h2>
      <p>
        Az EseményTér megfelelő technikai és szervezési intézkedéseket alkalmaz
        a személyes adatok védelme érdekében. A jelszavak titkosítva kerülnek
        tárolásra, az adatokhoz csak jogosult személyek férhetnek hozzá.
      </p>

      <h2>6. Az adatok megőrzésének ideje</h2>
      <p>
        A személyes adatokat kizárólag addig kezeljük, ameddig az a szolgáltatás
        működéséhez szükséges, illetve ameddig azt jogszabály előírja.
        A fiók törlése esetén a felhasználó adatai véglegesen eltávolításra kerülnek.
      </p>

      <h2>7. Felhasználói jogok</h2>
      <p>
        A felhasználót megilleti különösen:
      </p>
      <ul>
        <li>a tájékoztatáshoz való jog,</li>
        <li>a személyes adatokhoz való hozzáférés joga,</li>
        <li>az adatok helyesbítésének vagy törlésének joga,</li>
        <li>az adatkezelés korlátozásához való jog.</li>
      </ul>

      <h2>8. Kapcsolat</h2>
      <p>
        Amennyiben adatkezeléssel kapcsolatos kérdése vagy panasza merül fel,
        kérjük, vegye fel velünk a kapcsolatot az alábbi e-mail címen:
      </p>
      <p class="highlight">
        <a href="mailto:support@esemenyter.hu">support@esemenyter.hu</a>
      </p>

      <p class="last-update">
        A jelen Adatvédelmi Tájékoztató legutóbb frissítve:
        <strong>2026. január</strong>
      </p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Privacy'
};
</script>

<style scoped>
* {
  box-sizing: border-box;
  font-family: "Poppins", sans-serif;
  margin: 0;
  padding: 0;
}

.privacy-page {
  min-height: 100vh;
  width: 100vw;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 100px 20px 40px;
  position: relative;
}

/* Visszalépés gomb */
.back-btn {
  position: fixed;
  top: 20px;
  left: 20px;
  background: rgba(255, 255, 255, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: #fff;
  padding: 10px 20px;
  border-radius: 30px;
  cursor: pointer;
  backdrop-filter: blur(10px);
  font-size: 16px;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s ease;
  z-index: 100;
}

.back-btn i {
  font-size: 20px;
}

.back-btn:hover {
  background: rgba(255, 255, 255, 0.25);
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

/* Áttetsző üvegkártya (glassmorphism) hatás az adattartalomnak */
.privacy-wrapper {
  max-width: 1000px;
  width: 100%;
  margin: 0 auto;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 20px;
  padding: 50px;
  color: #fff;
  box-shadow: 0 25px 45px rgba(0, 0, 0, 0.2);
  animation: fadeIn 0.6s ease-out;
}

/* Beúsztató animáció a kártya megjelenésekor */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

.privacy-wrapper h1 {
  font-size: 32px;
  text-align: center;
  margin-bottom: 30px;
  font-weight: 600;
  opacity: 0.95;
}

.privacy-wrapper h2 {
  font-size: 22px;
  margin: 30px 0 15px;
  font-weight: 500;
  opacity: 0.9;
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
  padding-bottom: 8px;
}

.privacy-wrapper p,
.privacy-wrapper li {
  line-height: 1.8;
  text-align: justify;
  color: rgba(255, 255, 255, 0.9);
  margin-bottom: 10px;
}

.privacy-wrapper ul {
  padding-left: 25px;
  margin: 10px 0;
}

.privacy-wrapper li {
  margin-bottom: 8px;
}

.privacy-wrapper strong {
  color: #fff;
  font-weight: 600;
}

.privacy-wrapper a {
  color: #fff;
  text-decoration: none;
  border-bottom: 1px solid rgba(255, 255, 255, 0.3);
  transition: border-color 0.3s ease;
}

.privacy-wrapper a:hover {
  border-bottom-color: #fff;
}

.intro {
  font-size: 18px;
  text-align: center !important;
  margin-bottom: 40px;
  padding: 20px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 10px;
  border-left: 4px solid #fff; /* Megkülönböztető sáv a bevezetőnek */
}

/* Kiemelt információk (pl. email) keretezése */
.highlight {
  font-size: 18px;
  padding: 15px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px;
  margin: 15px 0;
  text-align: center !important;
}

.last-update {
  margin-top: 50px;
  text-align: center;
  opacity: 0.8;
  font-style: italic;
  padding-top: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.2);
}

/* Reszponzív nézetek mobil eszközökre */
@media (max-width: 768px) {
  .privacy-wrapper {
    padding: 30px 20px;
  }
  
  .privacy-wrapper h1 {
    font-size: 26px;
  }
  
  .privacy-wrapper h2 {
    font-size: 20px;
  }
  
  .intro {
    font-size: 16px;
    padding: 15px;
  }
  
  .highlight {
    font-size: 16px;
    padding: 12px;
  }
  
  .back-btn {
    padding: 8px 16px;
    font-size: 14px;
  }
}
</style>