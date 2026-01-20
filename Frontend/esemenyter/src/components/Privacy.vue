<template>
  <h1>Adatvédelem</h1>
  
  <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Possimus iusto, sint, eum repellat doloribus quidem, quia doloremque voluptas quas consequuntur asperiores officia reiciendis ullam perferendis maxime sed ab iure earum!</p>
    <div class="privacy-page">
        <button class="back-btn" @click="$router.back()" title="Vissza az előző oldalra">⟵ Vissza</button>

        <div class="privacy-wrapper">
            <h1>Adatvédelmi Tájékoztató</h1>

            <p>
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
            <p>
                E-mail: <strong>support@esemenyter.hu</strong>
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
            <p>
                <strong>support@esemenyter.hu</strong>
            </p>

            <p>
                A jelen Adatvédelmi Tájékoztató legutóbb frissítve:
                <strong>2025. január</strong>
            </p>
        </div>
    </div>
>>>>>>> frontend
</template>

<script>
</script>

<style scoped>
.privacy-page {
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

.privacy-wrapper {
  max-width: 1000px;
  width: 100%;
  margin: 0 auto;
  background: rgba(255,255,255,0.15);
  backdrop-filter: blur(20px);
  border-radius: 14px;
  padding: 40px;
  color: #fff;
}



.privacy-wrapper h1 {
  text-align: center;
  margin-bottom: 30px;
}

.privacy-wrapper h2 {
  margin-top: 30px;
}

.privacy-wrapper p,
.privacy-wrapper li {
  line-height: 1.7;
  text-align: justify;
}

.last-update {
  margin-top: 40px;
  text-align: center;
  opacity: 0.8;
}

@media (max-width: 768px) {
  .privacy-wrapper {
    padding: 24px;
  }
}
</style>