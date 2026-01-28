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
          
          <!-- Navigációs gombok -->
          <div v-if="!isLoggedIn" class="nav-buttons">
            <button class="nav-btn login-btn" @click="goToLogin">
              <i class='bx bx-log-in'></i>
              <span>Bejelentkezés</span>
            </button>
            <button class="nav-btn register-btn" @click="goToRegister">
              <i class='bx bx-user-plus'></i>
              <span>Regisztráció</span>
            </button>
          </div>
          
          <!-- felhasznaloi profil -->
          <div v-else class="user-profile">
            <div class="user-avatar" @click="toggleUserMenu">
              <div class="avatar-circle">
                <span>{{ userInitials }}</span>
              </div>
              <div class="user-status">
                <div class="status-dot online"></div>
              </div>
            </div>
            
            <transition name="slide-fade">
              <div v-if="showUserMenu" class="user-menu">
                <div class="menu-header">
                  <div class="menu-user-info">
                    <h4>{{ user.name }}</h4>
                    <p class="user-email">{{ user.email }}</p>
                  </div>
                  <div class="role-badge" :class="user.role">
                    {{ roleDisplayName }}
                  </div>
                </div>
                <div class="menu-items">
                  <router-link to="/profile" class="menu-item">
                    <i class='bx bx-user'></i>
                    <span>Profilom</span>
                  </router-link>
                  <router-link to="/events-list" class="menu-item">
                    <i class='bx bx-calendar'></i>
                    <span>Események</span>
                  </router-link>
                  <div class="menu-divider"></div>
                  <button class="menu-item logout-btn" @click="logout">
                    <i class='bx bx-log-out'></i>
                    <span>Kijelentkezés</span>
                  </button>
                </div>
              </div>
            </transition>
          </div>
        </div>
      </div>
    </header>

    <!-- fo tartalom -->
    <main class="main-content">
      <div class="container">
        <div v-if="!isLoggedIn" class="welcome-section">
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
                  vagy egyszerűen tarts naprakészen magad!
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

        <!-- Szerepkör kiválasztás (bejelentkezett felhasználónak) -->
        <div v-if="isLoggedIn && !profileConfigured" class="role-selection-section">
          <div class="section-header">
            <div class="header-icon">
              <i class='bx bx-cast'></i>
            </div>
            <h2>Válassz szerepkört</h2>
            <p class="section-subtitle">
              Az alábbi lehetőségek közül választhatod ki, hogyan szeretnél részt venni az EseményTérben
            </p>
          </div>
          
          <div class="role-cards-grid">
            <!-- Diák kártya -->
            <div class="role-card student" :class="{ 'selected': selectedRole === 'student' }" 
                 @click="selectedRole = 'student'">
              <div class="card-decoration">
                <div class="decoration-circle"></div>
                <div class="decoration-circle small"></div>
              </div>
              <div class="role-icon">
                <i class='bx bx-graduation'></i>
              </div>
              <h3>Diák</h3>
              <p class="role-description">
                Eseményekre jelentkezhetsz, részt vehetsz szavazásokon és hozzászólhatsz a közösségben.
              </p>
              <ul class="role-features">
                <li><i class='bx bx-check'></i> Eseményekre jelentkezés</li>
                <li><i class='bx bx-check'></i> Szavazások részvétel</li>
                <li><i class='bx bx-check'></i> Közösségi interakció</li>
              </ul>
              <button v-if="selectedRole === 'student'" class="card-action-btn" @click.stop="setupStudent">
                <span>Diákként folytatás</span>
                <i class='bx bx-chevron-right'></i>
              </button>
            </div>
            
            <!-- Tanár kártya -->
            <div class="role-card teacher" :class="{ 'selected': selectedRole === 'teacher' }" 
                 @click="selectedRole = 'teacher'">
              <div class="card-decoration">
                <div class="decoration-circle"></div>
                <div class="decoration-circle small"></div>
              </div>
              <div class="role-icon">
                <i class='bx bx-chalkboard'></i>
              </div>
              <h3>Tanár</h3>
              <p class="role-description">
                Hozz létre eseményeket, indíts szavazásokat és koordináld az osztályod tevékenységeit.
              </p>
              <ul class="role-features">
                <li><i class='bx bx-check'></i> Események létrehozása</li>
                <li><i class='bx bx-check'></i> Szavazások kezelése</li>
                <li><i class='bx bx-check'></i> Osztálykoordináció</li>
              </ul>
              <button v-if="selectedRole === 'teacher'" class="card-action-btn" @click.stop="setupTeacher">
                <span>Tanárként folytatás</span>
                <i class='bx bx-chevron-right'></i>
              </button>
            </div>
            
            <!-- Admin kártya -->
            <div class="role-card admin" :class="{ 'selected': selectedRole === 'admin' }" 
                 @click="selectedRole = 'admin'">
              <div class="card-decoration">
                <div class="decoration-circle"></div>
                <div class="decoration-circle small"></div>
              </div>
              <div class="role-icon">
                <i class='bx bx-cog'></i>
              </div>
              <h3>Adminisztrátor</h3>
              <p class="role-description">
                Kezeld az iskolákat, felhasználókat és a rendszer teljes működését.
              </p>
              <ul class="role-features">
                <li><i class='bx bx-check'></i> Iskola regisztrálás</li>
                <li><i class='bx bx-check'></i> Felhasználókezelés</li>
                <li><i class='bx bx-check'></i> Rendszeradminisztráció</li>
              </ul>
              <button v-if="selectedRole === 'admin'" class="card-action-btn" @click.stop="setupAdmin">
                <span>Adminisztrátorként folytatás</span>
                <i class='bx bx-chevron-right'></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Diák beállítás -->
        <transition name="slide-up">
          <div v-if="selectedRole === 'student' && !profileConfigured" class="setup-wizard">
            <div class="wizard-header">
              <div class="wizard-progress">
                <div class="progress-bar">
                  <div class="progress-fill" :style="{ width: '25%' }"></div>
                </div>
                <div class="step-indicators">
                  <div class="step active">1</div>
                  <div class="step">2</div>
                  <div class="step">3</div>
                  <div class="step">4</div>
                </div>
              </div>
              <h3>Diák profil beállítása</h3>
              <p>Néhány lépésben állítsd be profilodat a teljes funkcionalitás érdekében</p>
            </div>
            
            <div class="wizard-content">
              <div class="step-content">
                <div class="step-icon">
                  <i class='bx bx-map'></i>
                </div>
                <h4>Válassz régiót</h4>
                <p>Először válaszd ki a régiót, ahol az iskolád található</p>
                
                <div class="search-wrapper">
                  <i class='bx bx-search'></i>
                  <input 
                    type="text" 
                    v-model="searchQuery"
                    placeholder="Kezdj el gépelni a régió nevéből..."
                    class="search-input"
                  />
                </div>
                
                <div class="suggestions-grid">
                  <div 
                    v-for="region in filteredRegions" 
                    :key="region.id"
                    class="suggestion-card"
                    :class="{ 'selected': region.id === selectedRegionId }"
                    @click="selectRegion(region)"
                  >
                    <div class="suggestion-icon">
                      <i class='bx bx-current-location'></i>
                    </div>
                    <div class="suggestion-text">
                      <h5>{{ region.title }}</h5>
                      <p>{{ region.cityCount }} település</p>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="wizard-actions">
                <button class="btn-outline" @click="selectedRole = ''">
                  <i class='bx bx-arrow-back'></i>
                  Vissza
                </button>
                <button class="btn-primary" @click="nextStep" :disabled="!selectedRegionId">
                  Következő lépés
                  <i class='bx bx-chevron-right'></i>
                </button>
              </div>
            </div>
          </div>
        </transition>

        <!-- Funkciók (nem bejelentkezett felhasználóknak) -->
        <div v-if="!isLoggedIn" ref="featuresSection" class="features-section">
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

        <!-- Profil beállítva üzenet -->
        <div v-if="profileConfigured && isLoggedIn" class="success-section">
          <div class="success-card">
            <div class="success-icon">
              <i class='bx bx-party'></i>
            </div>
            <h3>Profilod kész!</h3>
            <p>Sikeresen beállítottad a profilodat. Most már teljes mértékben használhatod az EseményTér funkcióit.</p>
            <button class="btn-primary btn-success" @click="goToEvents">
              <i class='bx bx-calendar'></i>
              Események megtekintése
            </button>
            <div class="success-actions">
              <button class="btn-text" @click="goToProfile">
                <i class='bx bx-user'></i>
                Profil szerkesztése
              </button>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Lábléc -->
    <footer class="main-footer" v-if="!isLoggedIn">
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
              <a href="#">Cookie-k</a>
            </div>
          </div>
        </div>
        
        <div class="footer-bottom">
          <p>&copy; 2024 EseményTér. Minden jog fenntartva.</p>
          <p>Készítve <i class='bx bx-heart'></i> a magyar oktatásért</p>
        </div>
      </div>
    </footer>

    <!-- Floating Action Button -->
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
      isLoggedIn: false,
      user: {
        id: null,
        name: 'Kovács János',
        email: 'janos.kovacs@example.com',
        role: '',
        school: '',
        class: '',
        schoolId: null,
        classId: null
      },
      profileConfigured: false,
      showUserMenu: false,
      showScrollTop: false,
      
      selectedRole: '',
      
      // Minta adatok
      searchQuery: '',
      selectedRegionId: null,
      regions: [
        { id: 1, title: 'Budapest', cityCount: 1 },
        { id: 2, title: 'Pest megye', cityCount: 42 },
        { id: 3, title: 'Bács-Kiskun', cityCount: 28 },
        { id: 4, title: 'Baranya', cityCount: 17 },
        { id: 5, title: 'Békés', cityCount: 21 },
        { id: 6, title: 'Borsod-Abaúj-Zemplén', cityCount: 35 },
        { id: 7, title: 'Csongrád', cityCount: 16 },
        { id: 8, title: 'Fejér', cityCount: 22 }
      ]
    }
  },
  
  computed: {
    userInitials() {
      return this.user.name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
    },
    
    roleDisplayName() {
      const roles = {
        'student': 'Diák',
        'teacher': 'Tanár',
        'admin': 'Adminisztrátor'
      };
      return roles[this.user.role] || this.user.role;
    },
    
    filteredRegions() {
      if (!this.searchQuery) return this.regions;
      return this.regions.filter(region =>
        region.title.toLowerCase().includes(this.searchQuery.toLowerCase())
      );
    }
  },
  
  methods: {
    checkLoginStatus() {
      const savedUser = localStorage.getItem('esemenyter_user');
      if (savedUser) {
        const userData = JSON.parse(savedUser);
        if (userData.isLoggedIn) {
          this.isLoggedIn = true;
          this.user = { ...this.user, ...userData };
          this.profileConfigured = !!userData.role;
        }
      }
    },
    
    toggleUserMenu() {
      this.showUserMenu = !this.showUserMenu;
    },
    
    setupStudent() {
      this.selectedRole = 'student';
    },
    
    setupTeacher() {
      this.selectedRole = 'teacher';
    },
    
    setupAdmin() {
      this.selectedRole = 'admin';
    },
    
    nextStep() {
      // Jelenleg csak demo
      this.profileConfigured = true;
      this.user.role = this.selectedRole;
      this.saveUserData();
    },
    
    saveUserData() {
      const userData = {
        ...this.user,
        isLoggedIn: true
      };
      localStorage.setItem('esemenyter_user', JSON.stringify(userData));
    },
    
    selectRegion(region) {
      this.selectedRegionId = region.id;
    },
    
    goToLogin() {
      this.$router.push('/login');
    },
    
    goToRegister() {
      this.$router.push('/register');
    },
    
    goToEvents() {
      this.$router.push('/events-list');
    },
    
    goToProfile() {
      this.$router.push('/profile');
    },
    
    logout() {
      localStorage.removeItem('esemenyter_user');
      this.isLoggedIn = false;
      this.profileConfigured = false;
      this.showUserMenu = false;
      this.user.role = '';
    },
    
    scrollToFeatures() {
      this.$refs.featuresSection?.scrollIntoView({ behavior: 'smooth' });
    },
    
    scrollToTop() {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    
    handleScroll() {
      this.showScrollTop = window.scrollY > 300;
    }
  },
  
  mounted() {
    this.checkLoginStatus();
    window.addEventListener('scroll', this.handleScroll);
    
    // Close user menu when clicking outside
    document.addEventListener('click', (e) => {
      if (!e.target.closest('.user-profile')) {
        this.showUserMenu = false;
      }
    });
  },
  
  beforeUnmount() {
    window.removeEventListener('scroll', this.handleScroll);
  }
}
</script>

<style scoped>
/* ====================
   ÁLTALÁNOS STÍLUSOK
   ==================== */
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

/* ====================
   FEJLÉC
   ==================== */
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

/* Felhasználó profil */
.user-profile {
  position: relative;
}

.user-avatar {
  cursor: pointer;
  position: relative;
}

.avatar-circle {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 16px;
  transition: transform 0.3s ease;
}

.user-avatar:hover .avatar-circle {
  transform: scale(1.05);
}

.user-status {
  position: absolute;
  bottom: 0;
  right: 0;
}

.status-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  border: 2px solid white;
}

.status-dot.online {
  background: #10b981;
}

/* Felhasználó menü */
.user-menu {
  position: absolute;
  top: calc(100% + 10px);
  right: 0;
  background: white;
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  width: 300px;
  overflow: hidden;
  z-index: 1000;
}

.menu-header {
  padding: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.menu-user-info h4 {
  margin: 0 0 5px 0;
  font-size: 18px;
}

.user-email {
  margin: 0;
  font-size: 14px;
  opacity: 0.9;
}

.role-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  margin-top: 10px;
}

.role-badge.student {
  background: rgba(16, 185, 129, 0.2);
  color: #10b981;
}

.role-badge.teacher {
  background: rgba(249, 115, 22, 0.2);
  color: #f97316;
}

.role-badge.admin {
  background: rgba(139, 92, 246, 0.2);
  color: #8b5cf6;
}

.menu-items {
  padding: 10px 0;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  padding: 12px 20px;
  background: none;
  border: none;
  text-align: left;
  color: #374151;
  font-size: 14px;
  cursor: pointer;
  transition: background 0.2s;
}

.menu-item:hover {
  background: #f3f4f6;
}

.menu-item i {
  font-size: 20px;
  color: #6b7280;
}

.menu-divider {
  height: 1px;
  background: #e5e7eb;
  margin: 10px 20px;
}

.logout-btn {
  color: #ef4444;
}

.logout-btn i {
  color: #ef4444;
}

/* Animációk */
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.3s ease;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

/* ====================
   ÜDVÖZLŐ SZEKCIÓ
   ==================== */
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
  grid-template-columns: 1fr 1fr;  /* Két egyenlő oszlop */
  gap: 60px;
  align-items: center;  /* Vertikálisan középre igazít */
  min-height: 500px;    /* Minimum magasság */
  padding: 60px;
}

.hero-text {
  display: flex;
  flex-direction: column;
  justify-content: center;  /* Vertikálisan középre igazítja a tartalmat */
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
  height: 100%;  /* Teljes magasságot elfoglal */
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

/* ====================
   SZEREPKÖR VÁLASZTÁS
   ==================== */
.role-selection-section {
  padding: 80px 0;
}

.section-header {
  text-align: center;
  margin-bottom: 60px;
}

.header-icon {
  font-size: 48px;
  color: #4f46e5;
  margin-bottom: 20px;
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

.role-cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 30px;
}

.role-card {
  background: white;
  border-radius: 24px;
  padding: 40px 30px;
  position: relative;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.role-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.role-card.selected {
  border-color: #4f46e5;
  box-shadow: 0 20px 40px rgba(79, 70, 229, 0.1);
}

.card-decoration {
  position: absolute;
  top: -50px;
  right: -50px;
}

.decoration-circle {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  opacity: 0.1;
}

.decoration-circle.small {
  width: 60px;
  height: 60px;
  position: absolute;
  top: 30px;
  right: 30px;
}

.role-card.student .decoration-circle {
  background: #10b981;
}

.role-card.teacher .decoration-circle {
  background: #f97316;
}

.role-card.admin .decoration-circle {
  background: #8b5cf6;
}

.role-icon {
  font-size: 48px;
  margin-bottom: 24px;
}

.role-card.student .role-icon {
  color: #10b981;
}

.role-card.teacher .role-icon {
  color: #f97316;
}

.role-card.admin .role-icon {
  color: #8b5cf6;
}

.role-card h3 {
  font-size: 24px;
  font-weight: 600;
  margin-bottom: 16px;
  color: #111827;
}

.role-description {
  color: #6b7280;
  line-height: 1.6;
  margin-bottom: 24px;
}

.role-features {
  list-style: none;
  padding: 0;
  margin: 0 0 30px 0;
}

.role-features li {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
  color: #4b5563;
  font-size: 14px;
}

.role-features i {
  color: #10b981;
}

.card-action-btn {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  padding: 16px 24px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.card-action-btn:hover {
  transform: translateX(5px);
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
}

/* ====================
   WIZARD
   ==================== */
.setup-wizard {
  background: white;
  border-radius: 32px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
  margin: 40px 0;
}

.wizard-header {
  padding: 40px 40px 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.wizard-progress {
  margin-bottom: 24px;
}

.progress-bar {
  height: 6px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 3px;
  overflow: hidden;
  margin-bottom: 10px;
}

.progress-fill {
  height: 100%;
  background: white;
  border-radius: 3px;
  transition: width 0.3s ease;
}

.step-indicators {
  display: flex;
  justify-content: space-between;
}

.step {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 14px;
}

.step.active {
  background: white;
  color: #4f46e5;
}

.wizard-header h3 {
  font-size: 28px;
  margin-bottom: 8px;
}

.wizard-header p {
  opacity: 0.9;
  font-size: 16px;
}

.wizard-content {
  padding: 40px;
}

.step-content {
  text-align: center;
  max-width: 600px;
  margin: 0 auto;
}

.step-icon {
  font-size: 64px;
  color: #4f46e5;
  margin-bottom: 24px;
}

.step-content h4 {
  font-size: 24px;
  margin-bottom: 12px;
  color: #111827;
}

.step-content p {
  color: #6b7280;
  margin-bottom: 32px;
}

.search-wrapper {
  position: relative;
  max-width: 400px;
  margin: 0 auto 32px;
}

.search-wrapper i {
  position: absolute;
  left: 20px;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
  font-size: 20px;
}

.search-input {
  width: 100%;
  padding: 16px 16px 16px 52px;
  border: 2px solid #e5e7eb;
  border-radius: 50px;
  font-size: 16px;
  transition: all 0.3s ease;
}

.search-input:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.suggestions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 40px;
}

.suggestion-card {
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 16px;
  padding: 20px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 16px;
}

.suggestion-card:hover {
  border-color: #4f46e5;
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.suggestion-card.selected {
  border-color: #4f46e5;
  background: #f8f9ff;
}

.suggestion-icon {
  font-size: 24px;
  color: #4f46e5;
}

.suggestion-text h5 {
  margin: 0 0 4px 0;
  font-size: 16px;
  font-weight: 600;
  color: #111827;
}

.suggestion-text p {
  margin: 0;
  font-size: 14px;
  color: #6b7280;
}

.wizard-actions {
  display: flex;
  justify-content: space-between;
  max-width: 400px;
  margin: 0 auto;
}

.btn-outline {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  background: transparent;
  border: 2px solid #4f46e5;
  color: #4f46e5;
  border-radius: 50px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-outline:hover {
  background: #4f46e5;
  color: white;
}

/* ====================
   FUNKCIÓK
   ==================== */
.features-section {
  padding: 80px 0;
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

/* ====================
   SIKER SZEKCIÓ
   ==================== */
.success-section {
  padding: 80px 0;
}

.success-card {
  text-align: center;
  background: white;
  border-radius: 32px;
  padding: 60px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
}

.success-icon {
  font-size: 80px;
  color: #10b981;
  margin-bottom: 32px;
}

.success-card h3 {
  font-size: 32px;
  font-weight: 700;
  margin-bottom: 16px;
  color: #111827;
}

.success-card p {
  font-size: 18px;
  color: #6b7280;
  max-width: 500px;
  margin: 0 auto 32px;
  line-height: 1.6;
}

.btn-success {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  padding: 18px 36px;
  font-size: 18px;
}

.success-actions {
  margin-top: 24px;
}

.btn-text {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: none;
  border: none;
  color: #4f46e5;
  font-size: 16px;
  cursor: pointer;
  transition: color 0.3s;
}

.btn-text:hover {
  color: #7c73ff;
}

/* ====================
   LÁBLÉC
   ==================== */
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

/* ====================
   FLOATING ACTION BUTTON
   ==================== */
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

/* ====================
   RESZPONZÍV DESIGN
   ==================== */
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
  .nav-buttons {
    display: none;
  }
  
  .role-cards-grid {
    grid-template-columns: 1fr;
  }
  
  .features-grid {
    grid-template-columns: 1fr;
  }
  
  .stats-container {
    grid-template-columns: repeat(2, 1fr);
    padding: 40px 20px;
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
  
  .stats-container {
    grid-template-columns: 1fr;
  }
  
  .wizard-content {
    padding: 30px 20px;
  }
  
  .suggestions-grid {
    grid-template-columns: 1fr;
  }
}

/* ====================
   ANIMÁCIÓK
   ==================== */
.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.5s ease;
}

.slide-up-enter-from,
.slide-up-leave-to {
  opacity: 0;
  transform: translateY(30px);
}

.fade-in {
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

/* ====================
   SCROLLBAR
   ==================== */
::-webkit-scrollbar {
  width: 10px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 5px;
}

::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(135deg, #5a6fd8 0%, #6a5ba6 100%);
}
</style>