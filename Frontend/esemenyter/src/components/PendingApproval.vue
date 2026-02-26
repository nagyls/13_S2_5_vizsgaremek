<template>
  <div class="pending-approval">
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

    <main class="main-content">
      <div class="container">
        <div class="pending-card">
          <div class="pending-icon">
            <i class='bx bx-time'></i>
          </div>
          
          <h2>Csatlakozási kérelmed elbírálás alatt</h2>
          
          <p class="pending-message">
            Köszönjük, hogy csatlakozni szeretnél a(z) 
            <strong>{{ user.school || 'kiválasztott iskolához' }}</strong>!
          </p>
          
          <div class="info-box">
            <i class='bx bx-info-circle'></i>
            <p>
              Kérelmedet az intézményvezető hamarosan elbírálja. 
              Amint elfogadásra kerül, értesítést kapsz és teljes hozzáférést kapsz a rendszerhez.
            </p>
          </div>

          <div class="status-timeline">
            <div class="timeline-item completed">
              <div class="timeline-dot">
                <i class='bx bx-check'></i>
              </div>
              <div class="timeline-content">
                <h4>Profil létrehozva</h4>
                <p>{{ formatDate(user.created_at) }}</p>
              </div>
            </div>

            <div class="timeline-item active">
              <div class="timeline-dot">
                <i class='bx bx-time'></i>
              </div>
              <div class="timeline-content">
                <h4>Csatlakozási kérelem elküldve</h4>
                <p>{{ formatDate(user.requested_at) }}</p>
              </div>
            </div>

            <div class="timeline-item pending">
              <div class="timeline-dot">
                <i class='bx bx-hourglass'></i>
              </div>
              <div class="timeline-content">
                <h4>Intézményvezetői jóváhagyás</h4>
                <p>Folyamatban...</p>
              </div>
            </div>
          </div>

          <div class="action-buttons">
            <button class="btn-primary" @click="checkStatus">
              <i class='bx bx-refresh'></i>
              Státusz ellenőrzése
            </button>
          </div>

          <p class="help-text">
            <i class='bx bx-help-circle'></i>
            Kérdés esetén vedd fel a kapcsolatot az intézményvezetővel.
          </p>
        </div>
      </div>
    </main>

    <!-- Értesítés -->
    <transition name="toast">
      <div v-if="showToast" class="toast-notification" :class="toastType">
        <i :class="toastIcon"></i>
        <span>{{ toastMessage }}</span>
      </div>
    </transition>

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
        schoolId: null,
        created_at: null,
        requested_at: null
      },
      showUserMenu: false,
      showScrollTop: false,
      showToast: false,
      toastMessage: '',
      toastType: 'info'
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
    },
    
    toastIcon() {
      return {
        success: 'bx bx-check-circle',
        error: 'bx bx-error-circle',
        warning: 'bx bx-error',
        info: 'bx bx-info-circle'
      }[this.toastType];
    }
  },
  
  methods: {
    loadUserData() {
      const savedUser = localStorage.getItem('esemenyter_user');
      if (savedUser) {
        const userData = JSON.parse(savedUser);
        if (userData.isLoggedIn) {
          this.user = { 
            ...this.user, 
            ...userData,
            created_at: userData.created_at || new Date().toISOString(),
            requested_at: userData.requested_at || new Date().toISOString()
          };
          
          // Ellenőrizzük a felhasználó státuszát a backendben
          this.checkUserStatus();
        } else {
          this.$router.push('/');
        }
      } else {
        this.$router.push('/');
      }
    },
    
    async checkUserStatus() {
      try {
        const token = localStorage.getItem('esemenyter_token');
        
        // Ellenőrizzük, hogy van-e függőben lévő kérelem
        const response = await axios.get(`http://127.0.0.1:8000/api/users/${this.user.id}/pending-request`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        
        const hasPendingRequest = response.data.has_pending;
        
        if (!hasPendingRequest) {
          // Ha nincs függőben lévő kérelem, akkor már elfogadták
          // Töltsük újra a felhasználó adatokat
          await this.refreshUserData();
        }
        
      } catch (error) {
        console.error('Hiba a státusz ellenőrzésekor:', error);
      }
    },
    
    async refreshUserData() {
      try {
        const token = localStorage.getItem('esemenyter_token');
        
        const response = await axios.get(`http://127.0.0.1:8000/api/users/${this.user.id}`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        
        const userData = response.data.data || response.data;
        
        // Frissítsük a localStorage-t
        const savedUser = JSON.parse(localStorage.getItem('esemenyter_user'));
        const updatedUser = { ...savedUser, ...userData };
        localStorage.setItem('esemenyter_user', JSON.stringify(updatedUser));
        
        // Ha már elfogadták, irányítsuk át a dashboardra
        if (userData.status === 'approved' || userData.role) {
          this.showNotification('Kérelmed elfogadásra került! Átirányítás...', 'success');
          setTimeout(() => {
            this.$router.push('/user-dashboard');
          }, 2000);
        }
        
      } catch (error) {
        console.error('Hiba a felhasználói adatok frissítésekor:', error);
      }
    },
    
    async checkStatus() {
      this.showNotification('Státusz ellenőrzése...', 'info');
      
      try {
        const token = localStorage.getItem('esemenyter_token');
        
        const response = await axios.get(`http://127.0.0.1:8000/api/users/${this.user.id}/request-status`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        
        const status = response.data.status;
        
        if (status === 'approved') {
          this.showNotification('Kérelmed elfogadásra került! Átirányítás...', 'success');
          
          // Frissítsük a felhasználó adatokat
          await this.refreshUserData();
          
          setTimeout(() => {
            this.$router.push('/user-dashboard');
          }, 2000);
        } else if (status === 'rejected') {
          this.showNotification('Sajnáljuk, kérelmed elutasításra került.', 'error');
        } else {
          this.showNotification('Kérelmed még elbírálás alatt áll.', 'info');
        }
        
      } catch (error) {
        console.error('Hiba a státusz ellenőrzésekor:', error);
        this.showNotification('Hiba történt a státusz lekérésekor', 'error');
      }
    },
    
    formatDate(date) {
      if (!date) return 'Ismeretlen';
      return new Date(date).toLocaleDateString('hu-HU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    },
    
    toggleUserMenu() {
      this.showUserMenu = !this.showUserMenu;
    },
    
    showNotification(message, type = 'info') {
      this.toastMessage = message;
      this.toastType = type;
      this.showToast = true;
      
      setTimeout(() => {
        this.showToast = false;
      }, 3000);
    },
    
    scrollToTop() {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    
    handleScroll() {
      this.showScrollTop = window.scrollY > 300;
    },
    
    logout() {
      axios.post('http://127.0.0.1:8000/api/logout')
        .finally(() => {
          localStorage.removeItem('esemenyter_user');
          localStorage.removeItem('esemenyter_token');
          this.$router.push('/');
        });
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
.pending-approval {
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

/* Fő tartalom */
.main-content {
  padding: 80px 0;
  min-height: calc(100vh - 80px);
  display: flex;
  align-items: center;
}

/* Pending card */
.pending-card {
  background: white;
  border-radius: 32px;
  padding: 60px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
  max-width: 700px;
  margin: 0 auto;
  text-align: center;
}

.pending-icon {
  font-size: 80px;
  color: #f59e0b;
  margin-bottom: 32px;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% {
    transform: scale(1);
    opacity: 1;
  }
  50% {
    transform: scale(1.1);
    opacity: 0.8;
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

.pending-card h2 {
  font-size: 32px;
  font-weight: 700;
  margin-bottom: 20px;
  color: #111827;
}

.pending-message {
  font-size: 18px;
  color: #4b5563;
  line-height: 1.6;
  margin-bottom: 30px;
}

.pending-message strong {
  color: #4f46e5;
}

.info-box {
  background: #f0f9ff;
  border-left: 4px solid #3b82f6;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 40px;
  display: flex;
  align-items: flex-start;
  gap: 15px;
  text-align: left;
}

.info-box i {
  font-size: 24px;
  color: #3b82f6;
  flex-shrink: 0;
}

.info-box p {
  margin: 0;
  color: #0369a1;
  font-size: 16px;
  line-height: 1.5;
}

/* Timeline */
.status-timeline {
  margin: 40px 0;
  position: relative;
  padding-left: 30px;
}

.status-timeline::before {
  content: '';
  position: absolute;
  left: 15px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: #e5e7eb;
}

.timeline-item {
  position: relative;
  margin-bottom: 30px;
  display: flex;
  align-items: flex-start;
  gap: 20px;
}

.timeline-item:last-child {
  margin-bottom: 0;
}

.timeline-dot {
  position: absolute;
  left: -30px;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: white;
  border: 2px solid #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1;
}

.timeline-item.completed .timeline-dot {
  background: #10b981;
  border-color: #10b981;
  color: white;
}

.timeline-item.active .timeline-dot {
  background: #f59e0b;
  border-color: #f59e0b;
  color: white;
  animation: pulse 2s infinite;
}

.timeline-item.pending .timeline-dot {
  background: #9ca3af;
  border-color: #9ca3af;
  color: white;
}

.timeline-dot i {
  font-size: 18px;
}

.timeline-content {
  flex: 1;
  text-align: left;
  padding-left: 20px;
}

.timeline-content h4 {
  margin: 0 0 5px 0;
  font-size: 18px;
  color: #111827;
}

.timeline-content p {
  margin: 0;
  font-size: 14px;
  color: #6b7280;
}

.timeline-item.completed .timeline-content h4 {
  color: #10b981;
}

.timeline-item.active .timeline-content h4 {
  color: #f59e0b;
}

/* Action buttons */
.action-buttons {
  display: flex;
  gap: 16px;
  justify-content: center;
  margin: 40px 0 20px;
}

.btn-outline {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 14px 28px;
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

.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 14px 28px;
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

.help-text {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  color: #6b7280;
  font-size: 14px;
  margin-top: 20px;
}

.help-text i {
  color: #4f46e5;
}

/* Toast értesítések */
.toast-notification {
  position: fixed;
  bottom: 30px;
  right: 30px;
  padding: 16px 24px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
  display: flex;
  align-items: center;
  gap: 12px;
  z-index: 3000;
  min-width: 300px;
  border-left: 4px solid;
}

.toast-notification.success {
  border-left-color: #10b981;
}

.toast-notification.error {
  border-left-color: #ef4444;
}

.toast-notification.warning {
  border-left-color: #f59e0b;
}

.toast-notification.info {
  border-left-color: #3b82f6;
}

.toast-notification i {
  font-size: 24px;
}

.toast-notification.success i {
  color: #10b981;
}

.toast-notification.error i {
  color: #ef4444;
}

.toast-notification.warning i {
  color: #f59e0b;
}

.toast-notification.info i {
  color: #3b82f6;
}

.toast-notification span {
  font-size: 14px;
  color: #374151;
}

/* Toast animáció */
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateX(30px);
}

/* FAB gomb */
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

/* Reszponzív */
@media (max-width: 768px) {
  .main-content {
    padding: 40px 0;
  }
  
  .pending-card {
    padding: 40px 20px;
  }
  
  .pending-icon {
    font-size: 60px;
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
  }
  
  .action-buttons {
    flex-direction: column;
  }
  
  .btn-outline,
  .btn-primary {
    width: 100%;
    justify-content: center;
  }
  
  .user-menu {
    width: 280px;
    right: -20px;
  }
  
  .toast-notification {
    left: 20px;
    right: 20px;
    min-width: auto;
    bottom: 20px;
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
  
  .timeline-item {
    flex-direction: column;
  }
  
  .timeline-content {
    padding-left: 0;
    margin-top: 10px;
  }
}
</style>