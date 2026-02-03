<template>
  <div class="main-page">
    <header class="main-header">
      <div class="container">
        <div class="header-content">
          <div class="logo-section" @click="$router.push('/')">
            <div class="logo-icon">
              <i class='bx bx-calendar-heart'></i>
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
                  zökkenőmentesen együttműködhetnek. <br> Hozz létre eseményeket, szavazzatok közösen, 
                  vagy egyszerűen tartsd naprakészen magad!
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
                    <i class='bx bx-bell-ring'></i>
                    <h4>Értesítések</h4>
                    <p>Időben informálás</p>
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
              <p>Könnyedén hozz létre, szerkessz és oszd meg az eseményeket valós idejű frissítésekkel.</p>
            </div>
            
            <div class="feature-item">
              <div class="feature-icon-container">
                <div class="icon-wrapper">
                  <i class='bx bx-chart'></i>
                </div>
              </div>
              <h4>Élő szavazás és statisztika</h4>
              <p>Indíts szavazásokat és kövesd nyomon az eredményeket részletes statisztikákkal.</p>
            </div>
            
            <div class="feature-item">
              <div class="feature-icon-container">
                <div class="icon-wrapper">
                  <i class='bx bx-bell'></i>
                </div>
              </div>
              <h4>Okos értesítési rendszer</h4>
              <p>Kapj időben értesítéseket a számodra fontos eseményekről és változásokról.</p>
            </div>
            
            <div class="feature-item">
              <div class="feature-icon-container">
                <div class="icon-wrapper">
                  <i class='bx bx-shield'></i>
                </div>
              </div>
              <h4>Biztonságos környezet</h4>
              <p>Adataid védelme és biztonságos kommunikáció a legmagasabb szintű titkosítással.</p>
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
              <i class='bx bx-calendar-heart'></i>
              <span>EseményTér</span>
            </div>
            <p class="footer-tagline">
              Az iskolai események digitális otthona
            </p>
            <div class="social-links">
              <a href="#" class="social-link"><i class='bx bxl-facebook'></i></a>
              <a href="#" class="social-link"><i class='bx bxl-twitter'></i></a>
              <a href="#" class="social-link"><i class='bx bxl-instagram'></i></a>
              <a href="#" class="social-link"><i class='bx bxl-linkedin'></i></a>
            </div>
          </div>
          
          <div class="footer-links">
            <div class="link-column">
              <h5>Termék</h5>
              <a href="#">Funkciók</a>
              <a href="#">Árak</a>
              <a href="#">Gyakori kérdések</a>
            </div>
            <div class="link-column">
              <h5>Rólunk</h5>
              <a href="#">Cégünk</a>
              <a href="#">Karrier</a>
              <a href="#">Blog</a>
            </div>
            <div class="link-column">
              <h5>Jogi információk</h5>
              <a href="/privacy">Adatvédelem</a>
              <a href="#">Felhasználási feltételek</a>
            </div>
          </div>
        </div>
        
        <div class="footer-bottom">
          <p>&copy; 2024 EseményTér. Minden jog fenntartva.</p>
          <p>Készítve <i class='bx bx-heart'></i> a magyar oktatásért</p>
        </div>
      </div>
    </footer>

    <button v-if="showScrollTop" class="fab" @click="scrollToTop">
      <i class='bx bx-chevron-up'></i>
    </button>
  </div>
</template>

<script>
export default {
  name: 'MainPage',
  
  data() {
    return {
      showScrollTop: false
    }
  },
  
  methods: {
    goToLogin() {
      this.$router.push('/login')
    },
    
    goToRegister() {
      this.$router.push('/register')
    },
    
    scrollToFeatures() {
      this.$refs.featuresSection?.scrollIntoView({ behavior: 'smooth' })
    },
    
    scrollToTop() {
      window.scrollTo({ top: 0, behavior: 'smooth' })
    },
    
    handleScroll() {
      this.showScrollTop = window.scrollY > 300
    }
  },
  
  mounted() {
    window.addEventListener('scroll', this.handleScroll)
    
    // Redirect if already logged in
    const savedUser = localStorage.getItem('esemenyter_user')
    if (savedUser) {
      const userData = JSON.parse(savedUser)
      if (userData.isLoggedIn) {
        this.$router.push('/dashboard')
      }
    }
  },
  
  beforeUnmount() {
    window.removeEventListener('scroll', this.handleScroll)
  }
}
</script>

<style scoped>
.main-page {
  box-sizing: border-box;
  font-family: "Poppins", sans-serif;
  min-height: 100vh;
  width: 100vw;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

/* FEJLÉC */
.main-header {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
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

.logo-icon {
  font-size: 32px;
  color: #4f46e5;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.logo-text h1 {
  margin: 0;
  font-size: 24px;
  font-weight: 700;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.site-subtitle {
  margin: 0;
  font-size: 14px;
  color: #64748b;
  font-weight: 500;
}

/* Navigációs gombok */
.nav-buttons {
  display: flex;
  gap: 12px;
}

.nav-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  border-radius: 50px;
  border: none;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.login-btn {
  background: transparent;
  color: #4f46e5;
  border: 2px solid #4f46e5;
}

.login-btn:hover {
  background: #4f46e5;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
}

.register-btn {
  background: linear-gradient(135deg, #4f46e5 0%, #7c73ff 100%);
  color: white;
  border: 2px solid transparent;
}

.register-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}

/* ÜDVÖZLŐ SZEKCIÓ */
.welcome-section {
  padding: 80px 0;
}

.hero-container {
  background: white;
  border-radius: 32px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
}

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
  color: #111827;
}

.section-subtitle {
  font-size: 18px;
  color: #6b7280;
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
  gap: 60px;
  margin-bottom: 40px;
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
  grid-template-columns: repeat(3, 1fr);
  gap: 40px;
}

.link-column h5 {
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 20px;
  color: white;
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
  padding-top: 30px;
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
  
  .hero-title {
    font-size: 36px;
  }
  
  .footer-content {
    grid-template-columns: 1fr;
    gap: 40px;
  }
}

@media (max-width: 768px) {
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