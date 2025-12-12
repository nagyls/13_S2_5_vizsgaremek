<template>
    <div class="aszf-page">
        <button
            class="back-btn"
            @click="$router.back()"
            title="Vissza az előző oldalra"
        >
            ⟵ Vissza
        </button>

        <div class="aszf-wrapper">
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
</style>