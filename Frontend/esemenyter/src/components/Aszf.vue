<template>
  <div class="aszf-page">
    <button class="back-btn" @click="$router.back()">
      <i class='bx bx-arrow-back'></i> Vissza
    </button>

    <div class="aszf-wrapper">
      
      <h1>Általános Szerződési Feltételek (ÁSZF)</h1>

      <p class="intro">
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
        Kapcsolat: <a href="mailto:support@esemenyter.hu">support@esemenyter.hu</a>
      </p>

      <p class="last-update">
        Utolsó frissítés: <strong>2026. január</strong>
      </p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ASZF'
};
</script>

<style scoped>
* {
  box-sizing: border-box;
  font-family: "Poppins", sans-serif;
  margin: 0;
  padding: 0;
}

.aszf-page {
  min-height: 100vh;
  width: 100vw;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 100px 20px 40px;
  position: relative;
}

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

.aszf-wrapper {
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

.aszf-wrapper h1 {
  font-size: 32px;
  text-align: center;
  margin-bottom: 30px;
  font-weight: 600;
  opacity: 0.95;
}

.aszf-wrapper h2 {
  font-size: 22px;
  margin: 30px 0 15px;
  font-weight: 500;
  opacity: 0.9;
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
  padding-bottom: 8px;
}

.aszf-wrapper p,
.aszf-wrapper li {
  line-height: 1.8;
  text-align: justify;
  color: rgba(255, 255, 255, 0.9);
  margin-bottom: 10px;
}

.aszf-wrapper ul {
  padding-left: 25px;
  margin: 10px 0;
}

.aszf-wrapper li {
  margin-bottom: 8px;
}

.aszf-wrapper strong {
  color: #fff;
  font-weight: 600;
}

.aszf-wrapper a {
  color: #fff;
  text-decoration: none;
  border-bottom: 1px solid rgba(255, 255, 255, 0.3);
  transition: border-color 0.3s ease;
}

.aszf-wrapper a:hover {
  border-bottom-color: #fff;
}

.intro {
  font-size: 18px;
  text-align: center !important;
  margin-bottom: 40px;
  padding: 20px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 10px;
  border-left: 4px solid #fff;
}

.last-update {
  margin-top: 50px;
  text-align: center;
  opacity: 0.8;
  font-style: italic;
  padding-top: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.2);
}

@media (max-width: 768px) {
  .aszf-wrapper {
    padding: 30px 20px;
  }
  
  .aszf-wrapper h1 {
    font-size: 26px;
  }
  
  .aszf-wrapper h2 {
    font-size: 20px;
  }
  
  .intro {
    font-size: 16px;
    padding: 15px;
  }
  
  .back-btn {
    padding: 8px 16px;
    font-size: 14px;
  }
}
</style>