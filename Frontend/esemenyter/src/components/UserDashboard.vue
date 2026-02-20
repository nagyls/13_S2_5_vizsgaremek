<template>
  <div class="user-dashboard">
    <header class="main-header">
      <div class="container">
        <div class="header-content">
          <div class="logo-section" @click="$router.push('/user-dashboard')">
            <div class="logo-icon">
              <i class='bx bx-calendar-heart'></i>
            </div>
            <div class="logo-text">
              <h1 class="site-title">EseményTér</h1>
              <p class="site-subtitle">Ahol minden esemény helyet kap</p>
            </div>
          </div>
          
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

    <main class="main-content">
      <div class="container">
        <!-- Sikeres profilbeállítás utáni üzenet -->
        <div class="success-section">
          <div class="success-card">
            <div class="success-icon">
              <i class='bx bx-party'></i>
            </div>
            <h3>Profilod kész!</h3>
            <p>Sikeresen beállítottad a profilodat. Most már teljes mértékben használhatod az EseményTér funkcióit.</p>

            <!-- Gombok szerepkör alapján -->
            <div class="success-buttons">
              <button class="btn-primary btn-success" @click="goToEvents">
                <i class='bx bx-calendar'></i>
                Események megtekintése
              </button>

              <!-- Intézményvezetői felület gomb - csak admin/institution_manager szerepkörnél -->
              <button 
                v-if="user.role === 'institution_manager' || user.role === 'admin'" 
                class="btn-primary btn-institution" 
                @click="goToInstitutionDashboard"
              >
                <i class='bx bx-building-house'></i>
                Intézményvezetői felület
              </button>
            </div>

            <div class="success-actions">
              <button class="btn-text" @click="goToProfile">
                <i class='bx bx-user'></i>
                Profil szerkesztése
              </button>
            </div>
          </div>
        </div>

        <!-- Dashboard tartalom (később bővíthető) -->
        <div v-if="user.role === 'student'" class="role-content">
          <!-- Diák specifikus tartalom -->
        </div>
        <div v-else-if="user.role === 'teacher'" class="role-content">
          <!-- Tanár specifikus tartalom -->
        </div>
        <div v-else-if="user.role === 'admin' || user.role === 'institution_manager'" class="role-content">
          <!-- Admin specifikus tartalom -->
        </div>
      </div>
    </main>

    <button v-if="showScrollTop" class="fab" @click="scrollToTop">
      <i class='bx bx-chevron-up'></i>
    </button>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'UserDashboard',
  
  data() {
    return {
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
        'admin': 'Adminisztrátor',
        'institution_manager': 'Intézményvezető'
      };
      return roles[this.user.role] || this.user.role;
    }
  },
  
  methods: {
    loadUserData() {
      const savedUser = localStorage.getItem('esemenyter_user');
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
      } else {
        this.$router.push('/');
      }
    },

    async checkPendingStatus() {
      try {
        const token = localStorage.getItem('esemenyter_token');
        // Ellenőrizzük, hogy van-e függőben lévő kérelem
        const response = await axios.get(`http://127.0.0.1:8000/api/users/${this.user.id}/has-pending-request`, {
        headers: { Authorization: `Bearer ${token}` }
        });
        
        if (response.data.has_pending) {
            // Ha van függőben lévő kérelem, irányítsuk a PendingApproval oldalra
            this.$router.push('/pending-approval');
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
    
    goToProfile() {
      this.$router.push('/profile');
    },
    
    goToInstitutionDashboard() {
      this.$router.push('/institution-dashboard');
    },
    
    logout() {
      axios.post('http://127.0.0.1:8000/api/logout')
        .then(() => {
          console.log('Backend-en törölve a token');
        })
        .catch(err => {
          console.error('Logout hiba:', err);
        })
        .finally(() => {
          localStorage.removeItem('esemenyter_user');
          localStorage.removeItem('esemenyter_token');
          this.showUserMenu = false;
          this.$router.push('/');
        });
    },
    
    scrollToTop() {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    
    handleScroll() {
      this.showScrollTop = window.scrollY > 300;
    }
  },
  
  mounted() {
    this.loadUserData();
    window.addEventListener('scroll', this.handleScroll);
    
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
.user-dashboard {
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

.role-badge.institution_manager {
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

/* SIKER SZEKCIÓ */
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

.success-buttons {
  display: flex;
  gap: 16px;
  justify-content: center;
  margin-bottom: 24px;
  flex-wrap: wrap;
}

.btn-success {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  padding: 18px 36px;
  font-size: 18px;
}

.btn-institution {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  padding: 18px 36px;
  font-size: 18px;
  background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
}

.btn-institution:hover {
  background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(139, 92, 246, 0.3);
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

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 50px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
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

/* RESZPONZÍV */
@media (max-width: 768px) {
  .user-menu {
    width: 280px;
    right: -20px;
  }
  
  .success-card {
    padding: 40px 20px;
  }
  
  .success-icon {
    font-size: 60px;
  }
  
  .success-card h3 {
    font-size: 24px;
  }
  
  .success-card p {
    font-size: 16px;
  }
  
  .success-buttons {
    flex-direction: column;
  }
  
  .btn-success, .btn-institution {
    width: 100%;
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .fab {
    width: 48px;
    height: 48px;
    font-size: 20px;
    bottom: 20px;
    right: 20px;
  }
}
</style>