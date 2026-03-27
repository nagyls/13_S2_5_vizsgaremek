<template>
  <div class="pending-approval">
    <!-- HEADER -->
    <header class="main-header">
      <div class="container">
        <div class="header-content">
          <div class="logo-section">
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
        <div class="pending-card">
          <div class="pending-icon">
            <i class='bx bx-time-five'></i>
          </div>
          
          <h2>Csatlakozási kérelmed elbírálás alatt áll</h2>
          
          <p class="pending-message">
            Köszönjük, hogy csatlakozni szeretnél a(z)
            <strong>{{ user.school || 'kiválasztott iskolához' }}</strong>!
          </p>
          
          <div class="info-box">
            <i class='bx bx-info-circle'></i>
            <p>
              Kérelmedet az intézményvezető hamarosan elbírálja. 
              <!-- Amint elfogadásra kerül, értesítést kapsz és teljes hozzáférést kapsz a rendszerhez. -->
              Amint elfogadásra kerül, hozzáférést kapsz a teljes rendszerhez.
            </p>
          </div>

          <div class="status-indicator">
            <div class="status-line">
              <div class="status-step completed">
                <div class="step-icon">
                  <i class='bx bx-check'></i>
                </div>
                <span class="step-label">Profil létrehozva</span>
              </div>
              <div class="status-step active">
                <div class="step-icon">
                  <i class='bx bx-time'></i>
                </div>
                <span class="step-label">Elbírálás alatt</span>
              </div>
              <div class="status-step">
                <div class="step-icon">
                  <i class='bx bx-check-circle'></i>
                </div>
                <span class="step-label">Jóváhagyva</span>
              </div>
            </div>
          </div>

          <div class="info-message">
            <i class='bx bx-envelope'></i>
            <p>
              Amint a kérelmedet elbírálták, hozzáférést kapsz a további funkciókhoz.
              Ez általában 1-2 munkanapot igénybe vehet.
            </p>
          </div>

          <div class="action-buttons">
            <button class="btn-outline" @click="goToDashboard">
              <i class='bx bx-arrow-back'></i>
              Vissza a főoldalra
            </button>
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

export default {
  name: 'PendingApproval',
  
  data() {
    return {
      user: {
        id: null,
        name: '',
        email: '',
        role: '',
        school: '',
        schoolId: null
      },
      showUserMenu: false,
      showScrollTop: false
    }
  },
  
  computed: {
    userInitials() {
      if (!this.user.name) return '??';
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
        'teacher': 'Tanár'
      };
      return roles[this.user.role] || this.user.role;
    }
  },
  
  methods: {
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
        } else {
          this.$router.push('/');
        }
      }
    },
    
    toggleUserMenu() {
      this.showUserMenu = !this.showUserMenu;
    },
    
    goToDashboard() {
      this.$router.push('/user-dashboard');
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
          this.$router.push('/');
        });
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
/* ===== ALAP STÍLUSOK ===== */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.pending-approval {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
  width: 100%;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

/* ===== HEADER ===== */
.main-header {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
  padding: 16px 0;
  position: sticky;
  top: 0;
  z-index: 1000;
  box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
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
  gap: 15px;
  cursor: pointer;
  transition: transform 0.3s ease;
}

.logo-section:hover {
  transform: scale(1.02);
}

.logo-icon {
  width: 50px;
  height: 50px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 28px;
  box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
}

.logo-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.site-title {
  margin: 0;
  font-size: 24px;
  font-weight: 700;
  background: linear-gradient(135deg, #667eea, #764ba2);
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
  background: linear-gradient(135deg, #667eea, #764ba2);
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
  background: linear-gradient(135deg, #667eea, #764ba2);
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

/* Role badge */
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

.logout-btn:hover {
  background: #fee2e2;
}

/* ===== ANIMÁCIÓK ===== */
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
  padding: 80px 0;
  min-height: calc(100vh - 80px);
  display: flex;
  align-items: center;
}

/* ===== PENDING CARD ===== */
.pending-card {
  background: white;
  border-radius: 32px;
  padding: 60px;
  box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
  max-width: 700px;
  margin: 0 auto;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.pending-card::before {
  content: '';
  position: absolute;
  top: -50px;
  right: -50px;
  width: 200px;
  height: 200px;
  background: linear-gradient(135deg, #667eea10, #764ba210);
  border-radius: 50%;
  z-index: 0;
}

.pending-card::after {
  content: '';
  position: absolute;
  bottom: -50px;
  left: -50px;
  width: 200px;
  height: 200px;
  background: linear-gradient(135deg, #764ba210, #667eea10);
  border-radius: 50%;
  z-index: 0;
}

.pending-icon {
  font-size: 100px;
  color: #f59e0b;
  margin-bottom: 32px;
  position: relative;
  z-index: 1;
  animation: gentlePulse 2s infinite ease-in-out;
}

@keyframes gentlePulse {
  0%, 100% {
    transform: scale(1);
    opacity: 1;
  }
  50% {
    transform: scale(1.05);
    opacity: 0.9;
  }
}

.pending-card h2 {
  font-size: 32px;
  font-weight: 700;
  margin-bottom: 20px;
  color: #111827;
  position: relative;
  z-index: 1;
}

.pending-message {
  font-size: 18px;
  color: #4b5563;
  line-height: 1.6;
  margin-bottom: 30px;
  position: relative;
  z-index: 1;
}

.pending-message strong {
  color: #4f46e5;
  font-weight: 600;
}

/* ===== INFO BOX ===== */
.info-box {
  background: #f0f9ff;
  border-radius: 16px;
  padding: 24px;
  margin-bottom: 40px;
  display: flex;
  align-items: flex-start;
  gap: 16px;
  text-align: left;
  border: 1px solid #bae6fd;
  position: relative;
  z-index: 1;
}

.info-box i {
  font-size: 28px;
  color: #3b82f6;
  flex-shrink: 0;
}

.info-box p {
  margin: 0;
  color: #0369a1;
  font-size: 16px;
  line-height: 1.6;
}

/* ===== STATUS INDICATOR ===== */
.status-indicator {
  margin: 40px 0;
  position: relative;
  z-index: 1;
}

.status-line {
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
}

.status-line::before {
  content: '';
  position: absolute;
  top: 25px;
  left: 60px;
  right: 60px;
  height: 2px;
  background: #e5e7eb;
  z-index: 0;
}

.status-step {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  position: relative;
  z-index: 1;
  flex: 1;
}

.step-icon {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: white;
  border: 2px solid #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: #9ca3af;
  transition: all 0.3s ease;
}

.status-step.completed .step-icon {
  background: #10b981;
  border-color: #10b981;
  color: white;
}

.status-step.active .step-icon {
  background: #f59e0b;
  border-color: #f59e0b;
  color: white;
  box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.2);
}

.step-label {
  font-size: 14px;
  font-weight: 500;
  color: #6b7280;
  text-align: center;
}

.status-step.completed .step-label {
  color: #10b981;
}

.status-step.active .step-label {
  color: #f59e0b;
  font-weight: 600;
}

/* ===== INFO MESSAGE ===== */
.info-message {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px;
  background: #f3f4f6;
  border-radius: 16px;
  margin: 30px 0;
  text-align: left;
  position: relative;
  z-index: 1;
}

.info-message i {
  font-size: 28px;
  color: #4f46e5;
  flex-shrink: 0;
}

.info-message p {
  margin: 0;
  color: #374151;
  font-size: 15px;
  line-height: 1.5;
}

/* ===== ACTION BUTTONS ===== */
.action-buttons {
  display: flex;
  gap: 16px;
  justify-content: center;
  margin-top: 40px;
  position: relative;
  z-index: 1;
}

.btn-outline {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 14px 32px;
  background: transparent;
  border: 2px solid #4f46e5;
  color: #4f46e5;
  border-radius: 50px;
  font-weight: 600;
  font-size: 16px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-outline:hover {
  background: #4f46e5;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
}

.btn-outline i {
  font-size: 20px;
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

/* ===== RESPONZÍV ===== */
@media (max-width: 768px) {
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

  .main-content {
    padding: 40px 0;
  }

  .pending-card {
    padding: 40px 24px;
  }

  .pending-icon {
    font-size: 80px;
  }

  .pending-card h2 {
    font-size: 24px;
  }

  .pending-message {
    font-size: 16px;
  }

  .info-box {
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 20px;
  }

  .status-line::before {
    left: 40px;
    right: 40px;
  }

  .step-icon {
    width: 40px;
    height: 40px;
    font-size: 20px;
  }

  .step-label {
    font-size: 12px;
  }

  .info-message {
    flex-direction: column;
    text-align: center;
    padding: 20px;
  }

  .action-buttons {
    flex-direction: column;
  }

  .btn-outline {
    width: 100%;
    justify-content: center;
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
}

@media (max-width: 480px) {
  .main-header {
    padding: 8px 0;
  }

  .fab {
    width: 48px;
    height: 48px;
    font-size: 20px;
    bottom: 20px;
    right: 20px;
  }

  .pending-icon {
    font-size: 60px;
  }

  .status-line {
    flex-direction: column;
    gap: 20px;
  }

  .status-line::before {
    display: none;
  }

  .status-step {
    flex-direction: row;
    width: 100%;
    justify-content: flex-start;
  }

  .step-icon {
    width: 36px;
    height: 36px;
    font-size: 18px;
  }

  .step-label {
    font-size: 14px;
    text-align: left;
  }
}
</style>