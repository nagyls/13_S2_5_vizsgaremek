<template>
  <div class="user-dashboard">
    <!-- HEADER -->
    <header class="main-header">
      <div class="container">
        <div class="header-content">
          <!-- Logo -->
          <div class="logo-section" @click="$router.push('/user-dashboard')">
            <div class="logo-icon">
              <img :src="logo2" alt="EseményTér logó" class="logo-image">
            </div>
            <div class="logo-text">
              <h1 class="site-title">EseményTér</h1>
              <p class="site-subtitle">Ahol minden esemény helyet kap</p>
            </div>
          </div>

          <!-- User menu -->
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

    <!-- MAIN CONTENT -->
    <main class="main-content">
      <div class="container">
        <!-- Welcome Hero -->
        <div class="welcome-section">
          <div class="welcome-card">
            <div class="welcome-icon">
              <i class='bx bx-party'></i>
            </div>
            <h2>{{ welcomeText }}</h2>
            <p class="welcome-text">
              Sikeresen beállította a profil ját. Most már teljes mértékben használhatja az EseményTér funkcióit.
            </p>
            <div class="role-indicator">
              <span class="role-label">Szerepköröd:</span>
              <span class="role-value" :class="user.role">{{ roleDisplayName }}</span>
            </div>
          </div>
        </div>

        <!-- Navigation Hub -->
        <div class="nav-hub">
          <h3 class="hub-title">
            <i class='bx bx-compass'></i>
            {{ navHubTitle }}
          </h3>

          <div class="nav-grid">
            <!-- Események kártya (mindenkinek) -->
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

            <!-- Profil kártya (mindenkinek) -->
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

            <!-- Intézményvezetői felület (admin) -->
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

            <!-- További kártyák később bővíthetők -->
            <!-- <div class="nav-card coming-soon">
              <div class="card-icon soon">
                <i class='bx bx-time'></i>
              </div>
              <h4>További funkciók</h4>
              <p>Új lehetőségek hamarosan érkeznek</p>
              <div class="card-footer">
                <span class="badge-soon">Hamarosan</span>
              </div>
            </div> -->
          </div>
        </div>

        <!-- Quick Info (opcionális) -->
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

    <!-- Floating Action Button -->
    <button v-if="showScrollTop" class="fab" @click="scrollToTop">
      <i class='bx bx-chevron-up'></i>
    </button>
  </div>
</template>

<script>
import axios from 'axios';
import logo2 from '../../assets/logo2.svg';

export default {
  name: 'UserDashboard',

  data() {
    return {
      logo2,
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
      showUserMenu: false,
      showScrollTop: false
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

    isFormal() {
      return this.user.role === 'admin' || this.user.role === 'teacher';
    },

    welcomeText() {
      const firstName = this.user.name.split(' ')[0];
      return this.isFormal ? `Üdvözlöm, ${firstName}!` : `Üdvözöllek, ${firstName}!`;
    },

    navHubTitle() {
      return 'Hová szeretne menni?';
    },

    helpTitle() {
      return this.isFormal ? 'Segítségre van szüksége?' : 'Segítségre van szükséged?';
    }
  },

  methods: {
    updateStoredUserState(updates) {
      const storages = [localStorage, sessionStorage];

      storages.forEach(storage => {
        const savedUserRaw = storage.getItem('esemenyter_user');
        if (!savedUserRaw) {
          return;
        }

        try {
          const savedUser = JSON.parse(savedUserRaw);
          Object.assign(savedUser, updates);
          storage.setItem('esemenyter_user', JSON.stringify(savedUser));
        } catch (error) {
          // Hibás storage érték esetén ne álljon le az oldal.
        }
      });
    },

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

    loadUserData() {
      const savedUser =
        localStorage.getItem('esemenyter_user') ||
        sessionStorage.getItem('esemenyter_user');

      const token =
        localStorage.getItem('esemenyter_token') ||
        sessionStorage.getItem('esemenyter_token');

      if (!savedUser || !token) {
        this.$router.push('/');
        return;
      }

      if (savedUser) {
        const userData = JSON.parse(savedUser);
        if (userData.isLoggedIn) {
          this.user = { ...this.user, ...userData };

          // Ha nincs szerepkör beállítva, irányítsuk vissza a dashboardra
          if (!this.user.role) {
            this.$router.push('/dashboard');
            return;
          }
          // Ellenőrizzük, hogy van-e függőben lévő kérelme
          this.checkPendingStatus();
        } else {
          this.$router.push('/');
        }
      }
    },

    async checkPendingStatus() {
      try {
        const token =
          localStorage.getItem('esemenyter_token') ||
          sessionStorage.getItem('esemenyter_token');

        if (!token) {
          return;
        }

        const response = await axios.get('http://127.0.0.1:8000/api/user', {
          headers: { Authorization: `Bearer ${token}` }
        });

        const backendUser = response.data || {};
        const hasEstablishedMembership = Number(backendUser.establishment_id) > 0;
        if (hasEstablishedMembership) {
          return;
        }

        // Ha az intézményi tagság megszűnt, a helyi role/státusz is törlendő.
        if (this.user.role === 'student' || this.user.role === 'teacher') {
          this.resetMembershipAndRedirectToDashboard();
          return;
        }

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
        console.error('Hiba a függőben lévő kérelem ellenőrzésekor:', error);
      }
    },

    toggleUserMenu() {
      this.showUserMenu = !this.showUserMenu;
    },

    goToEvents() {
      this.$router.push('/events-list');
    },

    goToCalendar() {
      this.$router.push('/events-calendar');
    },

    goToProfile() {
      this.$router.push('/profile');
    },

    goToInstitutionDashboard() {
      this.$router.push('/institution-dashboard');
    },

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

    scrollToTop() {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },

    handleScroll() {
      this.showScrollTop = window.scrollY > 300;
    },

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
/* ===== Alap stílusok ===== */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.user-dashboard {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
  width: 100%;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

/* ===== FEJLÉC ===== */
.main-header {
  background: rgba(255, 255, 255, 0.95);
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

/* Logo */
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
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

/* ===== USER PROFIL ===== */
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

/* ===== USER MENÜ ===== */
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
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.menu-user-info h4 {
  margin: 0 0 5px 0;
  font-size: 18px;
  font-weight: 600;
}

.user-email {
  margin: 0;
  font-size: 14px;
  opacity: 0.9;
}

/* Role badge a menüben */
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

/* ===== MAIN CONTENT ===== */
.main-content {
  padding: 60px 0;
}

/* ===== WELCOME SECTION ===== */
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

/* ===== NAVIGATION HUB ===== */
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
  color: #111827;
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

.nav-card.coming-soon {
  cursor: default;
  opacity: 0.7;
}

.nav-card.coming-soon:hover {
  transform: none;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

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

.card-icon.institution {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  box-shadow: 0 10px 20px rgba(245, 158, 11, 0.3);
}

.card-icon.soon {
  background: linear-gradient(135deg, #9ca3af, #6b7280);
  box-shadow: 0 10px 20px rgba(156, 163, 175, 0.3);
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

.badge-soon {
  display: inline-block;
  padding: 6px 16px;
  background: #f3f4f6;
  color: #6b7280;
  border-radius: 50px;
  font-size: 14px;
  font-weight: 500;
}

/* ===== INFO SECTION ===== */
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
  color: #111827;
  margin-bottom: 4px;
}

.info-content p {
  font-size: 14px;
  color: #6b7280;
  margin: 0;
}

/* ===== FLOATING ACTION BUTTON ===== */
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

/* ===== RESPONSIVE ===== */
@media (max-width: 700px) {
  .main-header {
    padding: 12px 0;
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

  .user-menu {
    width: 220px;
    right: 0;
    left: auto;
    transform: none;
  }

  .menu-header {
    padding: 12px 16px;
  }

  .menu-items {
    padding: 6px 0;
  }

  .menu-item {
    padding: 8px 12px;
    font-size: 13px;
  }

  .menu-item i {
    font-size: 16px;
  }

  .welcome-card {
    padding: 32px 20px;
  }

  .welcome-card h2 {
    font-size: 28px;
  }

  .welcome-text {
    font-size: 16px;
  }

  .hub-title {
    font-size: 24px;
  }

  .nav-grid {
    gap: 20px;
  }

  .nav-card {
    padding: 24px 20px;
  }

  .card-icon {
    width: 60px;
    height: 60px;
    font-size: 30px;
  }

  .nav-card h4 {
    font-size: 20px;
  }

  .nav-card p {
    font-size: 14px;
  }
}

@media (max-width: 480px) {
  .main-header {
    padding: 8px 0;
  }

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

  .hub-title {
    font-size: 20px;
  }

  .nav-grid {
    grid-template-columns: 1fr;
  }

  .info-card {
    flex-direction: column;
    text-align: center;
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
