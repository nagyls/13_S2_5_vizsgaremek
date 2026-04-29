<template>
  <div class="main-page">
    <header class="main-header">
      <div class="container">
        <div class="header-content">
          <div class="logo-section" @click="$router.push('/')">
            <div class="logo-icon">
              <img :src="logo2" alt="EseményTér logó" class="logo-image">
            </div>
            <div class="logo-text">
              <h1 class="site-title">EseményTér</h1>
              <p class="site-subtitle">Ahol minden esemény helyet kap</p>
            </div>
          </div>
          
          <div class="nav-buttons">
            <button class="nav-btn login-btn" @click="goToLogin">
              <i class='bx bx-log-in'></i>
              <span>Bejelentkezés</span>
            </button>
            <button class="nav-btn register-btn" @click="goToRegister">
              <i class='bx bx-user-plus'></i>
              <span>Regisztráció</span>
            </button>
          </div>
        </div>
      </div>
    </header>

    <main class="main-content">
      <div class="container">
        <div class="welcome-section">
          <div class="hero-container">
            <div class="hero-content">
              <div class="hero-text">
                <h1 class="hero-title">
                  <span class="gradient-text">Ismerj meg egy</span>
                  <br>
                  <span class="hero-highlight">új dimenziót</span>
                  <br>
                  <span class="hero-subtitle">az iskolai életben</span>
                </h1>
                <p class="hero-description">
                  Az EseményTér egy olyan platform, ahol tanárok, diákok 
                  zökkenőmentesen együttműködhetnek. <br> Hozzon létre eseményeket, szavazzatok közösen,
                  vagy egyszerűen tartsa naprakészen magát!
                </p>
                <div class="hero-actions">
                  <button class="btn-primary btn-hero" @click="goToRegister">
                    <i class='bx bx-rocket'></i>
                    <span>Kezdjük el!</span>
                  </button>
                  <button class="btn-secondary btn-hero" @click="scrollToFeatures">
                    <i class='bx bx-info-circle'></i>
                    <span>További információk</span>
                  </button>
                </div>
              </div>
              <div class="hero-visual">
                <div class="floating-cards">
                  <div class="card card-1 floating">
                    <i class='bx bx-calendar-star'></i>
                    <h4>Események</h4>
                    <p>Könnyed tervezés</p>
                  </div>
                  <div class="card card-2 floating delayed">
                    <i class='bx bx-group'></i>
                    <h4>Közösség</h4>
                    <p>Interaktív részvétel</p>
                  </div>
                  <div class="card card-3 floating delayed-2">
                    <i class='bx bx-message'></i>
                    <h4>Visszajelzés</h4>
                    <p>Vélemények és kommentek</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div ref="featuresSection" class="features-section">
          <div class="section-header">
            <h2>Miért válassz minket?</h2>
            <p class="section-subtitle">
              Az EseményTér egyedülálló funkciókat kínál az iskolai élet szervezéséhez
            </p>
          </div>
          
          <div class="features-grid">
            <div class="feature-item">
              <div class="feature-icon-container">
                <div class="icon-wrapper">
                  <i class='bx bx-calendar-check'></i>
                </div>
              </div>
              <h4>Intelligens eseménykezelés</h4>
              <p>Könnyedén hozzon létre, szerkesszen és ossza meg az eseményeket valós idejű frissítésekkel.</p>
            </div>
            
            <div class="feature-item">
              <div class="feature-icon-container">
                <div class="icon-wrapper">
                  <i class='bx bx-poll'></i>
                </div>
              </div>
              <h4>Élő szavazás és statisztika</h4>
              <p>Indítson szavazásokat és kövesse nyomon az eredményeket részletes statisztikákkal.</p>
            </div>
            
            <div class="feature-item">
              <div class="feature-icon-container">
                <div class="icon-wrapper">
                  <i class='bx bx-bell'></i>
                </div>
              </div>
              <h4>Interaktív visszajelzések</h4>
              <p>Ossza meg véleményét a hozzászólások segítségével és vegyen részt aktívan a közösség életében.</p>
            </div>
          </div>
        </div>
      </div>
    </main>

    <footer class="main-footer">
      <div class="container">
        <div class="footer-content">
          <div class="footer-brand">
            <div class="footer-logo">
              <img :src="logo2" alt="EseményTér logó" class="footer-logo-image">
              <span>EseményTér</span>
            </div>
            <p class="footer-tagline">
              Az iskolai események digitális otthona
            </p>
          </div>
          
          <div class="footer-links">
            <div class="link-column contact-column">
              <h5>Kapcsolat</h5>
              <p>nagylevedsamuel@gmail.com</p>
              <p>+36 70 426 0259</p>
            </div>
            
            <!-- középső oszlop szándékosan üres (vagy ide jöhet később hírlevél/CTA) -->
            <div class="link-column middle-column" aria-hidden="true"></div>
            
            <div class="link-column legal-column">
              <h5>Jogi információk</h5>
              <router-link to="/privacy">Adatvédelem</router-link>
              <router-link to="/aszf">Általános szerződési feltételek</router-link>
            </div>
           </div>
        </div>
        
        <div class="footer-bottom">
          <p>&copy; 2024 EseményTér</p>
        </div>
      </div>
    </footer>

    <button v-if="showScrollTop" class="fab" @click="scrollToTop">
      <i class='bx bx-chevron-up'></i>
    </button>
  </div>
</template>

<script>
import logo2 from '../../assets/logo2.svg'
import { getToken } from '../../services/api'

export default {
  name: 'MainPage',
  
  data() {
    return {
      logo2,
      showScrollTop: false
    }
  },
  
  methods: {
    // Navigáció a bejelentkezési oldalra
    goToLogin() {
      this.$router.push('/login')
    },
    
    // Navigáció a regisztrációs oldalra
    goToRegister() {
      this.$router.push('/register')
    },
    
    // Simán görget a funkciók (Miért válassz minket?) szekcióhoz
    scrollToFeatures() {
      this.$refs.featuresSection?.scrollIntoView({ behavior: 'smooth' })
    },
    
    // Simán visszagörget az oldal tetejére
    scrollToTop() {
      window.scrollTo({ top: 0, behavior: 'smooth' })
    },
    
    // Figyeli a görgetést és bizonyos távolság után megjeleníti a segédgombot
    handleScroll() {
      this.showScrollTop = window.scrollY > 300
    }
  },
  
  mounted() {
    window.addEventListener('scroll', this.handleScroll)

    // Ha a felhasználó már be van jelentkezve, automatikusan irányítsuk a dashboardra
    const token = getToken()
    if (token && this.$route.path !== '/dashboard') {
      this.$router.push('/dashboard')
    }
  },
  
  beforeUnmount() {
    // Eseményfigyelő eltávolítása a memóriaszivárgás megelőzése érdekében
    window.removeEventListener('scroll', this.handleScroll)
  }
}
</script>

<style scoped>
.main-page {
  box-sizing: border-box;
  font-family: "Poppins", sans-serif;
  min-height: 100vh;
  width: 100%;
  background:
    radial-gradient(circle at 20% 30%, rgba(59, 130, 246, 0.15), transparent 100%),
    radial-gradient(circle at 85% 70%, rgba(255, 255, 255, 0.1), transparent 38%),
    linear-gradient(135deg, #0a0f1c 0%, #1e3a5f 50%, #0a0f1c 100%);
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

/* FEJLÉC ÉS LOGÓ SZEKCIÓ */
.main-header {
  background: linear-gradient(180deg, rgba(8, 14, 30, 0.86) 0%, rgba(11, 20, 42, 0.78) 100%);
  backdrop-filter: blur(10px); /* Üveghatású elmosódás */
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  padding: 16px 0;
  position: sticky;
  top: 0;
  z-index: 1000;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.logo-section {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  transition: opacity 0.3s;
}

.logo-section:hover {
  opacity: 0.8;
}

/* Logó ikon doboza árnyékkal */
.logo-icon {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #ffffff;
  box-shadow: 0 8px 18px rgba(15, 23, 42, 0.12);
  overflow: hidden;
}

.logo-image {
  width: 100%;
  height: 100%;
  object-fit: contain;
  display: block;
}

/* Színátmenetes weboldal cím */
.logo-text h1 {
  margin: 0;
  font-size: 24px;
  font-weight: 700;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.site-subtitle {
  margin: 0;
  font-size: 14px;
  color: #64748b;
  font-weight: 500;
}

/* Navigációs gombok stílusa */
.nav-buttons {
  display: flex;
  gap: 12px;
  align-items: center;
}

.nav-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  min-width: 140px;
  min-height: 42px;
  padding: 9px 16px;
  border-radius: 50px;
  border: 2px solid transparent;
  font-weight: 600;
  font-size: 14px;
  letter-spacing: 0;
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
}

.login-btn {
  background: rgba(255, 255, 255, 0.06);
  color: #e5e7eb;
  border-color: rgba(148, 163, 184, 0.45);
  box-shadow: none;
}

.login-btn:hover {
  background: rgba(255, 255, 255, 0.14);
  color: #f8fafc;
  border-color: rgba(191, 219, 254, 0.65);
  transform: translateY(-1px);
}

.register-btn {
  background: #5b63f6;
  color: #ffffff;
  border-color: rgba(255, 255, 255, 0.2);
  box-shadow: 0 4px 12px rgba(20, 28, 66, 0.25);
}

.register-btn:hover {
  background: #4f46e5;
  transform: translateY(-1px);
  box-shadow: 0 6px 14px rgba(20, 28, 66, 0.3);
}

.nav-btn:focus-visible {
  outline: 3px solid rgba(191, 219, 254, 0.95);
  outline-offset: 2px;
}

/* FŐ ÜDVÖZLŐ (HERO) SZEKCIÓ */
.welcome-section {
  padding: 80px 0;
}

.hero-container {
  background: white;
  border-radius: 32px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
}

/* Kétoszlopos elrendezés: szöveg és lebegő kártyák */
.hero-content {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 60px;
  align-items: center;
  min-height: 500px;
  padding: 60px;
}

.hero-text {
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.hero-title {
  font-size: 48px;
  font-weight: 800;
  line-height: 1.2;
  margin-bottom: 24px;
}

.gradient-text {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.hero-highlight {
  color: #111827;
  position: relative;
  display: inline-block;
}

.hero-highlight::after {
  content: '';
  position: absolute;
  bottom: 5px;
  left: 0;
  width: 100%;
  height: 8px;
  background: linear-gradient(90deg, #667eea, #764ba2);
  opacity: 0.3;
  z-index: -1;
}

.hero-subtitle {
  color: #6b7280;
  font-weight: 400;
}

.hero-description {
  font-size: 18px;
  line-height: 1.6;
  color: #4b5563;
  margin-bottom: 32px;
}

.hero-actions {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
}

.btn-hero {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px 32px;
  border-radius: 50px;
  font-weight: 600;
  font-size: 16px;
  cursor: pointer;
  transition: all 0.3s ease;
  border: none;
}

.btn-primary {
  background: linear-gradient(135deg, #4f46e5 0%, #7c73ff 100%);
  color: white;
}

.btn-primary:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
}

.btn-secondary {
  background: white;
  color: #4f46e5;
  border: 2px solid #4f46e5;
}

.btn-secondary:hover {
  background: #4f46e5;
  color: white;
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(79, 70, 229, 0.2);
}

/* Floating cards */
.floating-cards {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  position: relative;
}

.card {
  position: absolute;
  width: 200px;
  background: white;
  border-radius: 20px;
  padding: 24px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  text-align: center;
}

.card i {
  font-size: 48px;
  margin-bottom: 16px;
}

.card-1 {
  top: 20%;
  left: 10%;
}

.card-2 {
  top: 30%;
  right: 15%;
}

.card-3 {
  bottom: 10%;
  left: 20%;
}

.floating {
  animation: float 6s ease-in-out infinite;
}

.delayed {
  animation-delay: 2s;
}

.delayed-2 {
  animation-delay: 4s;
}

@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-20px); }
}

/* FUNKCIÓK SZEKCIÓ */
.features-section {
  padding: 80px 0;
}

.section-header {
  text-align: center;
  margin-bottom: 60px;
}

.section-header h2 {
  font-size: 36px;
  font-weight: 700;
  margin-bottom: 16px;
  color: white;
}

.section-subtitle {
  font-size: 18px;
  color: #b7bac0;
  max-width: 600px;
  margin: 0 auto;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 32px;
}

.feature-item {
  text-align: center;
  padding: 40px 24px;
  background: white;
  border-radius: 24px;
  transition: all 0.3s ease;
}

.feature-item:hover {
  transform: translateY(-10px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.feature-icon-container {
  margin-bottom: 24px;
}

.icon-wrapper {
  width: 80px;
  height: 80px;
  margin: 0 auto;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.icon-wrapper i {
  font-size: 36px;
  color: white;
}

.feature-item h4 {
  font-size: 20px;
  font-weight: 600;
  margin-bottom: 16px;
  color: #111827;
}

.feature-item p {
  color: #6b7280;
  line-height: 1.6;
}

/* LÁBLÉC */
.main-footer {
  background: #111827;
  color: white;
  padding: 60px 0 30px;
}

.footer-content {
  display: grid;
  grid-template-columns: 1fr 2fr;
  gap: 40px;
  margin-bottom: 24px;
}

.footer-brand {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.footer-logo {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 24px;
  font-weight: 700;
}

.footer-logo-image {
  width: 32px;
  height: 32px;
  object-fit: contain;
  display: block;
}

.footer-tagline {
  color: #9ca3af;
  font-size: 16px;
}

.social-links {
  display: flex;
  gap: 16px;
}

.social-link {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  text-decoration: none;
  transition: all 0.3s ease;
}

.social-link:hover {
  background: #4f46e5;
  transform: translateY(-3px);
}

.footer-links {
  display: grid;
  /* contact | spacer | legal — spacer can be used later for newsletter etc.
     using auto columns for the side columns keeps them close to their content */
  grid-template-columns: auto 1fr auto;
  gap: 20px; /* tighter horizontal spacing */
}

.link-column h5 {
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 12px; /* reduce vertical gap under heading */
  color: white;
}

.link-column {
  justify-self: end;
  text-align: left;
}

.contact-column { justify-self: start; text-align: left; }
.middle-column { justify-self: center; text-align: center; }
.legal-column  { justify-self: end;   text-align: left; }

@media (max-width: 1024px) {
  .link-column {
    justify-self: start;
    text-align: left;
  }

  .footer-links > .link-column {
    grid-column: auto;
  }
}

.link-column p {
  color: #9ca3af;
  margin-bottom: 8px; /* tighter spacing between lines */
}

.link-column a {
  display: block;
  color: #9ca3af;
  text-decoration: none;
  margin-bottom: 12px;
  transition: color 0.3s;
}

.link-column a:hover {
  color: #4f46e5;
}

.footer-bottom {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 16px; /* reduce space between divider line and bottom text */
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  color: #9ca3af;
  font-size: 14px;
}

.footer-bottom i {
  color: #ef4444;
}

/* FLOATING ACTION BUTTON */
.fab {
  position: fixed;
  bottom: 30px;
  right: 30px;
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
  transition: all 0.3s ease;
  z-index: 1000;
}

.fab:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 30px rgba(102, 126, 234, 0.4);
}

/* RESZPONZÍV DESIGN */
@media (max-width: 1024px) {
  .hero-content {
    grid-template-columns: 1fr;
    gap: 40px;
    padding: 40px;
  }

  .floating-cards {
    display: grid;
    grid-template-columns: repeat(3, minmax(140px, 1fr));
    gap: 16px;
    height: auto;
    position: static;
  }

  .card {
    position: relative;
    top: auto;
    right: auto;
    bottom: auto;
    left: auto;
    width: 100%;
    padding: 18px;
  }

  .floating,
  .delayed,
  .delayed-2 {
    animation: none;
  }
  
  .hero-title {
    font-size: 36px;
  }
  
  .footer-content {
    grid-template-columns: 1fr;
    gap: 40px;
  }
}

@media (max-width: 768px) {
  .main-header {
    padding: 12px 0;
  }

  .floating-cards {
    grid-template-columns: repeat(2, minmax(130px, 1fr));
  }

  .header-content {
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    gap: 0;
  }

  .logo-text h1,
  .site-subtitle {
    display: none;
  }

  .nav-buttons {
    width: auto;
    justify-content: flex-end;
    flex-wrap: nowrap;
    gap: 8px;
  }

  .nav-btn {
    min-width: 130px;
    min-height: 40px;
    padding: 8px 11px;
    font-size: 14px;
  }

  .features-grid {
    grid-template-columns: 1fr;
  }
  
  .footer-links {
    grid-template-columns: 1fr;
    gap: 30px;
  }
  
  .footer-bottom {
    flex-direction: column;
    gap: 10px;
    text-align: center;
  }
}

@media (max-width: 480px) {
  .main-header {
    padding: 8px 0;
  }

  .floating-cards {
    grid-template-columns: 1fr;
  }

  .nav-buttons {
    width: 100%;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    gap: 8px;
  }

  .nav-btn {
    width: auto;
    min-width: 0;
    flex: 1;
    max-width: 152px;
    font-size: 13px;
    padding: 7px 9px;
  }

  .nav-btn i {
    font-size: 16px;
  }

  .hero-content {
    padding: 30px 20px;
  }
  
  .hero-title {
    font-size: 28px;
  }
  
  .hero-actions {
    flex-direction: column;
  }
  
  .btn-hero {
    width: 100%;
    justify-content: center;
  }
  
  .fab {
    width: 48px;
    height: 48px;
    font-size: 20px;
    bottom: 20px;
    right: 20px;
  }
}
</style>