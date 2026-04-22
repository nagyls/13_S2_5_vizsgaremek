<template>
  <div class="user-dashboard">
    <!-- FŐ FEJLÉC ÉS NAVIGÁCIÓ -->
    <header class="main-header">
      <div class="container">
        <div class="header-content">
          <!-- Logó szekció -->
          <div class="logo-section" @click="$router.push('/user-dashboard')">
            <div class="logo-icon">
              <img :src="logo2" alt="EseményTér logó" class="logo-image">
            </div>
            <div class="logo-text">
              <h1 class="site-title">EseményTér</h1>
              <p class="site-subtitle">Ahol minden esemény helyet kap</p>
            </div>
          </div>

          <!-- Felhasználói profil és lenyíló menü -->
          <div class="user-profile">
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
                    <i class='bx bx-calendar-event'></i>
                    <span>Események</span>
                  </router-link>
                  <router-link to="/events-calendar" class="menu-item">
                    <i class='bx bx-calendar-week'></i>
                    <span>Naptár</span>
                  </router-link>
                  <router-link
                    v-if="user.role === 'admin'"
                    to="/institution-dashboard"
                    class="menu-item"
                  >
                    <i class='bx bx-building-house'></i>
                    <span>Intézményvezetői felület</span>
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

    <!-- FŐ TARTALOM -->
    <main class="main-content">
      <div class="container">
        <!-- Üdvözlő hős szekció (Welcome Hero) -->
        <div class="welcome-section">
          <div class="welcome-card">
            <div class="welcome-icon">
              <i class='bx bx-party'></i>
            </div>
            <h2>{{ welcomeText }}</h2>
            <p class="welcome-text">
              Sikeresen beállította a profilját. Most már teljes mértékben használhatja az EseményTér funkcióit.
            </p>
            <div class="role-indicator">
              <span class="role-label">Szerepköröd:</span>
              <span class="role-value" :class="user.role">{{ roleDisplayName }}</span>
            </div>
          </div>
        </div>

        <!-- Navigációs központ (Hub) -->
        <div class="nav-hub">
          <h3 class="hub-title">
            <i class='bx bx-compass'></i>
            {{ navHubTitle }}
          </h3>

          <div class="nav-grid">
            <!-- Események kártya -->
            <div class="nav-card" @click="goToEvents">
              <div class="card-icon events">
                <i class='bx bx-calendar-star'></i>
              </div>
              <h4>Események</h4>
              <p>Böngésszen az események között, jelentkezzen és vegyen részt</p>
              <div class="card-footer">
                <span class="btn-text">
                  Megtekintés <i class='bx bx-right-arrow-alt'></i>
                </span>
              </div>
            </div>

            <!-- Naptár kártya -->
            <div class="nav-card" @click="goToCalendar">
              <div class="card-icon calendar">
                <i class='bx bx-calendar-week'></i>
              </div>
              <h4>Naptár</h4>
              <p>Tekintse át havi nézetben az eseményeket és az ismétlődő alkalmakat</p>
              <div class="card-footer">
                <span class="btn-text">
                  Megnyitás <i class='bx bx-right-arrow-alt'></i>
                </span>
              </div>
            </div>

            <!-- Esemény létrehozása (Csak Tanár/Admin) -->
            <div v-if="canCreateEvent" class="nav-card" @click="goToEventCreator">
              <div class="card-icon create-event">
                <i class='bx bx-calendar-plus'></i>
              </div>
              <h4>Esemény létrehozása</h4>
              <p>Új esemény rögzítése időponttal, leírással és részletekkel</p>
              <div class="card-footer">
                <span class="btn-text">
                  Megnyitás <i class='bx bx-right-arrow-alt'></i>
                </span>
              </div>
            </div>

            <!-- Profil kártya -->
            <div class="nav-card" @click="goToProfile">
              <div class="card-icon profile">
                <i class='bx bx-user-circle'></i>
              </div>
              <h4>Profilom</h4>
              <p>Tekintse meg és szerkessze profil adatait</p>
              <div class="card-footer">
                <span class="btn-text">
                  Megnyitás <i class='bx bx-right-arrow-alt'></i>
                </span>
              </div>
            </div>

            <!-- Intézményvezetői felület kártya (Csak Admin) -->
            <div
              v-if="user.role === 'admin'"
              class="nav-card"
              @click="goToInstitutionDashboard"
            >
              <div class="card-icon institution">
                <i class='bx bx-building-house'></i>
              </div>
              <h4>Intézményvezetői felület</h4>
              <p>Kezeld az intézményed, osztályokat és kérelmeket</p>
              <div class="card-footer">
                <span class="btn-text">
                  Vezérlőpult <i class='bx bx-right-arrow-alt'></i>
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Segítség szekció -->
        <div v-if="user.role === 'student' || user.role === 'teacher'" class="info-section">
          <div class="info-card">
            <i class='bx bx-info-circle'></i>
            <div class="info-content">
              <h4>{{ helpTitle }}</h4>
              <p>{{ isFormal ? 'Ha bármilyen kérdése van, keresd bátran az intézmény adminisztrátorát.' : 'Ha bármilyen kérdésed van, keresd bátran az intézményed adminisztrátorát.' }}</p>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Úszó navigációs gomb (Vissza a tetejére) -->
    <button v-if="showScrollTop" class="fab" @click="scrollToTop">
      <i class='bx bx-chevron-up'></i>
    </button>
  </div>
</template>

<script>
import axios from 'axios';
import logo2 from '../../assets/logo2.svg';

/**
 * UserDashboard komponens
 * Ez a komponens szolgál a bejelentkezett felhasználók (Diák, Tanár, Admin) központi irányítópultjaként.
 * Feladata a felhasználói adatok betöltése, a tagsági állapot ellenőrzése, és a főbb funkciók elérésének biztosítása.
 */
export default {
  name: 'UserDashboard',

  data() {
    return {
      logo2,
      // Felhasználói adatok kezdeti állapota
      user: {
        id: null,
        name: '',
        email: '',
        role: '',
        region: '',
        district: '',
        city: '',
        school: '',
        schoolId: null,
        isClassTeacher: false,
        mainClass: '',
        teachingClasses: [],
        institution_id: null
      },
      showUserMenu: false,    // A fejlécben lévő profil menü láthatósága
      showScrollTop: false    // A "Vissza a tetejére" gomb láthatósága
    }
  },

  computed: {
    /**
     * Létrehozza a felhasználó monogramját a nevéből (max 2 karakter).
     */
    userInitials() {
      if (!this.user.name) return '?';
      return this.user.name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
    },

    /**
     * A szerepkör magyar megnevezése a felületen.
     */
    roleDisplayName() {
      const roles = {
        'student': 'Diák',
        'teacher': 'Tanár',
        'admin': 'Adminisztrátor'
      };
      return roles[this.user.role] || this.user.role;
    },

    /**
     * Meghatározza, hogy a felhasználó formális szerepkörrel bír-e (Admin vagy Tanár).
     */
    isFormal() {
      return this.user.role === 'admin' || this.user.role === 'teacher';
    },

    /**
     * Az üdvözlő szöveg generálása a szerepkör (tegezés/magázás) alapján.
     */
    welcomeText() {
      const firstName = this.user.name.split(' ')[0];
      return this.isFormal ? `Üdvözlöm, ${firstName}!` : `Üdvözöllek, ${firstName}!`;
    },

    /**
     * A navigációs hub címe.
     */
    navHubTitle() {
      return 'Hová szeretne menni?';
    },

    /**
     * Meghatározza, hogy a felhasználó jogosult-e események létrehozására.
     */
    canCreateEvent() {
      return this.user.role === 'admin' || this.user.role === 'teacher';
    },

    /**
     * A segítség szekció címe (tegezés/magázás alapján).
     */
    helpTitle() {
      return this.isFormal ? 'Segítségre van szüksége?' : 'Segítségre van szükséged?';
    }
  },

  methods: {
    /**
     * Frissíti a helyi tárolókban (localStorage, sessionStorage) tárolt felhasználói adatokat.
     */
    updateStoredUserState(updates) {
      const storages = [localStorage, sessionStorage];

      storages.forEach(storage => {
        const savedUserRaw = storage.getItem('esemenyter_user');
        if (!savedUserRaw) return;

        try {
          const savedUser = JSON.parse(savedUserRaw);
          Object.assign(savedUser, updates);
          storage.setItem('esemenyter_user', JSON.stringify(savedUser));
        } catch (error) {
          console.error('Hiba a storage frissítésekor:', error);
        }
      });
    },

    /**
     * Törli az intézményi tagságot a kliens oldalról és visszairányít a kezdő dashboardra.
     */
    resetMembershipAndRedirectToDashboard() {
      this.user = {
        ...this.user,
        role: '',
        institution_id: null,
        schoolId: null,
        school: ''
      };

      this.updateStoredUserState({
        role: '',
        requestedRole: '',
        pendingApproval: false,
        institution_id: null,
        schoolId: null,
        school: ''
      });

      localStorage.removeItem('CurrentInstitution');
      sessionStorage.removeItem('CurrentInstitution');
      this.$router.replace('/dashboard');
    },

    /**
     * Betölti a felhasználói adatokat a tárolókból.
     */
    loadUserData() {
      const savedUser =
        localStorage.getItem('esemenyter_user') ||
        sessionStorage.getItem('esemenyter_user');

      const token =
        localStorage.getItem('esemenyter_token') ||
        sessionStorage.getItem('esemenyter_token');

      // Ha nincs mentett felhasználó vagy token, irány a kezdőoldal
      if (!savedUser || !token) {
        this.$router.push('/');
        return;
      }

      if (savedUser) {
        const userData = JSON.parse(savedUser);
        if (userData.isLoggedIn) {
          this.user = { ...this.user, ...userData };

          // Ha nincs szerepkör beállítva, irányítsuk vissza a választó felületre
          if (!this.user.role) {
            this.$router.push('/dashboard');
            return;
          }
          // Intézményi kérelem állapotának ellenőrzése
          this.checkPendingStatus();
        } else {
          this.$router.push('/');
        }
      }
    },

    /**
     * Ellenőrzi, hogy a felhasználónak van-e aktív vagy függőben lévő tagsági kérelme.
     * Ha a tagság megszűnt a szerver oldalon, kivezetjük a felületről.
     */
    async checkPendingStatus() {
      try {
        const token =
          localStorage.getItem('esemenyter_token') ||
          sessionStorage.getItem('esemenyter_token');

        if (!token) return;

        // API lekérés a legfrissebb felhasználói adatokért
        const response = await axios.get('http://127.0.0.1:8000/api/user', {
          headers: { Authorization: `Bearer ${token}` }
        });

        const backendUser = response.data || {};
        const hasEstablishedMembership = Number(backendUser.establishment_id) > 0;
        
        // Ha már rögzített tag, minden rendben
        if (hasEstablishedMembership) return;

        // Ha nincs intézményi ID-ja a backend szerint, de a kliensen van szerepköre, akkor az elévült
        if (this.user.role === 'student' || this.user.role === 'teacher') {
          this.resetMembershipAndRedirectToDashboard();
          return;
        }

        // Kérelem státuszának ellenőrzése
        const institutionId = Number(
          this.user.schoolId ||
          this.user.institution_id ||
          localStorage.getItem('CurrentInstitution') ||
          sessionStorage.getItem('CurrentInstitution')
        );

        const effectiveRole = this.user.role || this.user.requestedRole || '';
        const canHaveRequest =
          ['student', 'teacher'].includes(effectiveRole) &&
          Number.isFinite(institutionId) &&
          institutionId > 0;
          
        if (!canHaveRequest) {
          this.resetMembershipAndRedirectToDashboard();
          return;
        }

        const requestStatusResponse = await axios.get(
          `http://127.0.0.1:8000/api/establishment/${institutionId}/requests/me`,
          { headers: { Authorization: `Bearer ${token}` } }
        );

        const status = requestStatusResponse?.data?.status || '';

        // Irányítás a kérelem állapota alapján
        if (status === 'pending') {
          this.$router.push('/pending-approval');
          return;
        }

        if (status === 'rejected') {
          this.$router.push('/approval-rejected');
          return;
        }

        if (status === 'none' || status === '') {
          this.resetMembershipAndRedirectToDashboard();
        }
      } catch (error) {
        console.error('Hiba a kérelem állapotának ellenőrzésekor:', error);
      }
    },

    /**
     * Felhasználói menü ki/be kapcsolása.
     */
    toggleUserMenu() {
      this.showUserMenu = !this.showUserMenu;
    },

    /**
     * Navigációs metódusok a különböző felületekre.
     */
    goToEvents() {
      this.$router.push('/events-list');
    },

    goToCalendar() {
      this.$router.push('/events-calendar');
    },

    goToEventCreator() {
      this.$router.push('/event-creator');
    },

    goToProfile() {
      this.$router.push('/profile');
    },

    goToInstitutionDashboard() {
      this.$router.push('/institution-dashboard');
    },

    /**
     * Kijelentkezési folyamat: API hívás és helyi adatok törlése.
     */
    logout() {
      axios.delete('http://127.0.0.1:8000/api/logout')
        .finally(() => {
          localStorage.removeItem('esemenyter_user');
          localStorage.removeItem('esemenyter_token');
          localStorage.removeItem('CurrentInstitution');
          localStorage.removeItem('remember_me');
          sessionStorage.removeItem('esemenyter_user');
          sessionStorage.removeItem('esemenyter_token');
          sessionStorage.removeItem('CurrentInstitution');
          delete axios.defaults.headers.common['Authorization'];
          this.showUserMenu = false;
          this.$router.push('/');
        });
    },

    /**
     * Sima görgetés az oldal tetejére.
     */
    scrollToTop() {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },

    /**
     * Figyeli a görgetést a "Vissza a tetejére" gomb megjelenítéséhez.
     */
    handleScroll() {
      this.showScrollTop = window.scrollY > 300;
    },

    /**
     * Bezárja a felhasználói menüt, ha a menün kívülre kattintanak.
     */
    handleDocumentClick(event) {
      if (!event.target.closest('.user-profile')) {
        this.showUserMenu = false;
      }
    }
  },

  mounted() {
    this.loadUserData();
    window.addEventListener('scroll', this.handleScroll);
    document.addEventListener('click', this.handleDocumentClick);
  },

  beforeUnmount() {
    window.removeEventListener('scroll', this.handleScroll);
    document.removeEventListener('click', this.handleDocumentClick);
  }
}
</script>

<style scoped>
/* ============================================================
   ALAP STÍLUSOK ÉS KONSTRUKCIÓ
   ============================================================ */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.user-dashboard {
  min-height: 100vh;
  background:
    radial-gradient(circle at 12% 14%, rgba(88, 115, 235, 0.22), transparent 36%),
    radial-gradient(circle at 88% 24%, rgba(56, 189, 248, 0.1), transparent 30%),
    linear-gradient(135deg, #0a0f1c 0%, #1a3558 52%, #0f203d 100%);
  font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
  width: 100%;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

/* ============================================================
   FŐ FEJLÉC (HEADER)
   ============================================================ */
.main-header {
  background: rgba(10, 15, 28, 0.74);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  padding: 16px 0;
  position: sticky;
  top: 0;
  z-index: 1000;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  
}

/* Logó szekció stílusa */
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

.logo-text {
  display: flex;
  flex-direction: column;
}

.site-title {
  margin: 0;
  font-size: 24px;
  font-weight: 700;
  background: linear-gradient(135deg, #dbeafe 0%, #93c5fd 55%, #c4b5fd 100%);
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  line-height: 1.2;
}

.site-subtitle {
  margin: 0;
  font-size: 14px;
  color: #64748b;
  font-weight: 500;
}

/* ============================================================
   FELHASZNÁLÓI PROFIL ÉS AVATAR
   ============================================================ */
.user-profile {
  position: relative;
}

.user-avatar {
  cursor: pointer;
  position: relative;
  display: flex;
  align-items: center;
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
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
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
  box-shadow: 0 0 0 2px white;
}

/* ============================================================
   LENYÍLÓ FELHASZNÁLÓI MENÜ (DROPDOWN)
   ============================================================ */
.user-menu {
  position: absolute;
  top: calc(100% + 10px);
  right: 0;
  background: white;
  border-radius: 16px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  width: 300px;
  overflow: hidden;
  z-index: 9999;
}

.menu-header {
  padding: 20px;
  background: linear-gradient(150deg, #5873eb, rgb(0 0 0));
  color: white;
  border-bottom: 1px solid #e5e7eb;
}

.menu-user-info h4 {
  margin: 0 0 5px 0;
  font-size: 18px;
  font-weight: 600;
  color: #e4e2e2;
}

.user-email {
  margin: 0;
  font-size: 14px;
  opacity: 0.9;
  color: #b8b8b8;
}

/* Role badge stílusok a menüben */
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
  background: rgba(239, 68, 68, 0.18);
  color: #ef4444;
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
  text-decoration: none;
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

.logout-btn:hover {
  background: #fee2e2;
}

/* Animációk (Áttűnések) */
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.3s ease;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

/* ============================================================
   FŐ TARTALMI EGYSÉG (MAIN)
   ============================================================ */
.main-content {
  padding: 60px 0;
}

/* ============================================================
   ÜDVÖZLŐ SZEKCIÓ (WELCOME HERO)
   ============================================================ */
.welcome-section {
  margin-bottom: 60px;
}

.welcome-card {
  text-align: center;
  background: white;
  border-radius: 32px;
  padding: 48px 32px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
  position: relative;
  overflow: hidden;
}

/* Dekoratív elemek a kártya hátterében */
.welcome-card::before {
  content: '';
  position: absolute;
  top: -50px;
  right: -50px;
  width: 200px;
  height: 200px;
  background: linear-gradient(135deg, #667eea20, #764ba220);
  border-radius: 50%;
  z-index: 0;
}

.welcome-card::after {
  content: '';
  position: absolute;
  bottom: -50px;
  left: -50px;
  width: 200px;
  height: 200px;
  background: linear-gradient(135deg, #764ba220, #667eea20);
  border-radius: 50%;
  z-index: 0;
}

.welcome-icon {
  font-size: 80px;
  color: #4f46e5;
  margin-bottom: 24px;
  position: relative;
  z-index: 1;
}

.welcome-card h2 {
  font-size: 36px;
  font-weight: 700;
  margin-bottom: 16px;
  color: #111827;
  position: relative;
  z-index: 1;
}

.welcome-text {
  font-size: 18px;
  color: #6b7280;
  max-width: 600px;
  margin: 0 auto 24px;
  line-height: 1.6;
  position: relative;
  z-index: 1;
}

.role-indicator {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 24px;
  background: #f3f4f6;
  border-radius: 50px;
  position: relative;
  z-index: 1;
}

.role-label {
  font-size: 14px;
  color: #6b7280;
}

.role-value {
  font-size: 14px;
  font-weight: 600;
  padding: 4px 12px;
  border-radius: 50px;
}

/* Szerepkör alapú színezés */
.role-value.student {
  background: #10b98120;
  color: #10b981;
}

.role-value.teacher {
  background: #f9731620;
  color: #f97316;
}

.role-value.admin {
  background: #8b5cf620;
  color: #8b5cf6;
}

/* ============================================================
   NAVIGÁCIÓS HUB (GRÁD KÁRTYÁK)
   ============================================================ */
.nav-hub {
  margin-bottom: 60px;
}

.hub-title {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  font-size: 28px;
  font-weight: 600;
  color: white;
  margin-bottom: 40px;
}

.hub-title i {
  color: #4f46e5;
  font-size: 32px;
}

.nav-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 30px;
  max-width: 1200px;
  margin: 0 auto;
}

.nav-card {
  background: white;
  border-radius: 24px;
  padding: 32px 24px;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: column;
  height: 100%;
}

.nav-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 30px 50px rgba(102, 126, 234, 0.2);
}

/* Kártya ikonok egyedi színezése */
.card-icon {
  width: 80px;
  height: 80px;
  border-radius: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 24px;
  font-size: 40px;
  color: white;
}

.card-icon.events {
  background: linear-gradient(135deg, #667eea, #764ba2);
  box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
}

.card-icon.profile {
  background: linear-gradient(135deg, #10b981, #059669);
  box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
}

.card-icon.calendar {
  background: linear-gradient(135deg, #06b6d4, #0891b2);
  box-shadow: 0 8px 20px rgba(8, 145, 178, 0.3);
}

.card-icon.create-event {
  background: linear-gradient(135deg, #f59e0b, #ea580c);
  box-shadow: 0 10px 20px rgba(245, 158, 11, 0.3);
}

.card-icon.institution {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  box-shadow: 0 10px 20px rgba(245, 158, 11, 0.3);
}

.nav-card h4 {
  font-size: 24px;
  font-weight: 600;
  color: #111827;
  margin-bottom: 12px;
}

.nav-card p {
  font-size: 16px;
  color: #6b7280;
  line-height: 1.5;
  margin-bottom: 24px;
  flex-grow: 1;
}

.card-footer {
  margin-top: auto;
}

.btn-text {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: #4f46e5;
  font-weight: 500;
  font-size: 16px;
  transition: gap 0.3s ease;
}

.nav-card:hover .btn-text {
  gap: 12px;
}

/* ============================================================
   INFORMÁCIÓS SZEKCIÓ (SEGÍTSÉG)
   ============================================================ */
.info-section {
  max-width: 600px;
  margin: 0 auto;
}

.info-card {
  display: flex;
  align-items: center;
  gap: 16px;
  background: white;
  border-radius: 16px;
  padding: 20px 24px;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.03);
}

.info-card i {
  font-size: 32px;
  color: #4f46e5;
  opacity: 0.5;
}

.info-content h4 {
  font-size: 16px;
  font-weight: 600;
  color: white;
  margin-bottom: 4px;
}

.info-content p {
  font-size: 14px;
  color: #6b7280;
  margin: 0;
}

/* ============================================================
   ÚSZÓ AKCIÓGOMB (FAB)
   ============================================================ */
.fab {
  position: fixed;
  bottom: 30px;
  right: 30px;
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea, #764ba2);
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

/* ============================================================
   RESPONSIVE DESIGN (MOBIL ÉS TABLET)
   ============================================================ */
@media (max-width: 700px) {
  .main-header {
    padding: 12px 0;
  }

  /* Logó szöveg elrejtése mobilon a helytakarékosság miatt */
  .logo-text h1,
  .site-subtitle {
    display: none;
  }

  .user-menu {
    width: 220px;
    right: 0;
    left: auto;
    transform: none;
  }

  .welcome-card {
    padding: 32px 20px;
  }

  .welcome-card h2 {
    font-size: 28px;
  }

  .nav-grid {
    gap: 20px;
  }
}

@media (max-width: 480px) {
  .main-content {
    padding: 40px 0;
  }

  .welcome-icon {
    font-size: 60px;
  }

  .welcome-card h2 {
    font-size: 24px;
  }

  .role-indicator {
    flex-direction: column;
    gap: 4px;
  }

  .nav-grid {
    grid-template-columns: 1fr;
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
