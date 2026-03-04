<template>
  <div class="events-page">
    <header class="events-header">
      <div class="container">
        <div class="header-content">
          <div class="logo-title" @click="$router.push('/user-dashboard')">
            <i class='bx bx-calendar-heart'></i>
            <div class="logo-text">
              <h1 class="site-title">EseményTér</h1>
              <p class="site-subtitle">Ahol minden esemény helyet kap</p>
            </div>
          </div>
          
          <div class="user-profile" v-if="currentUser">
            <div class="user-avatar" @click.stop="toggleUserMenu">
              <div class="avatar-circle">
                <span>{{ userInitials }}</span>
              </div>
              <div class="user-status">
                <div class="status-dot online"></div>
              </div>
            </div>

            <transition name="slide-fade">
              <div v-if="showUserMenu" class="user-menu" @click.stop>
                <div class="menu-header">
                  <div class="menu-user-info">
                    <h4>{{ currentUser.nev || currentUser.name || 'Felhasználó' }}</h4>
                    <p class="user-email">{{ currentUser.email || '' }}</p>
                  </div>
                  <div class="role-badge" :class="normalizedRole">
                    {{ roleDisplay }}
                  </div>
                </div>

                <div class="menu-items">
                  <router-link to="/user-dashboard" class="menu-item">
                    <i class='bx bx-home'></i>
                    <span>Főoldal</span>
                  </router-link>
                  <router-link
                    v-if="normalizedRole === 'admin'"
                    to="/institution-dashboard"
                    class="menu-item"
                  >
                    <i class='bx bx-building-house'></i>
                    <span>Intézményvezetői felület</span>
                  </router-link>
                  <router-link to="/profile" class="menu-item">
                    <i class='bx bx-user'></i>
                    <span>Profilom</span>
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

    <main class="events-content">
      <div class="container">
        <!-- Hero szekció (mint az EventDetails-ben) -->
        <div class="hero-section">
          <div class="hero-overlay"></div>
          <div class="hero-content">
            <div class="hero-badges">
              <span class="badge">
                <i class='bx bx-calendar'></i>
                {{ events.length }} esemény
              </span>
              <span class="badge" :class="{ 'open': activeEventsCount > 0 }">
                <i class='bx bx-calendar-event'></i>
                {{ activeEventsCount }} aktív
              </span>
            </div>
            <h1 class="hero-title">Fedezd fel az eseményeket</h1>
            <p class="hero-description">Csatlakozz iskolád eseményeihez, vagy fedezz fel globális programokat.</p>
          </div>
        </div>

        <!-- Statisztikai kártyák -->
        <div class="stats-cards">
          <div class="stat-card">
            <div class="stat-icon">
              <i class='bx bx-calendar-check'></i>
            </div>
            <div class="stat-data">
              <span class="stat-number">{{ events.length }}</span>
              <span class="stat-label">Összes esemény</span>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon">
              <i class='bx bx-time'></i>
            </div>
            <div class="stat-data">
              <span class="stat-number">{{ activeEventsCount }}</span>
              <span class="stat-label">Aktív esemény</span>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon">
              <i class='bx bx-group'></i>
            </div>
            <div class="stat-data">
              <span class="stat-number">{{ totalParticipants }}</span>
              <span class="stat-label">Összes résztvevő</span>
            </div>
          </div>
        </div>

        <!-- Szűrők és keresés -->
        <div class="filters-section">
          <div class="filter-header">
            <h3><i class='bx bx-filter-alt'></i> Szűrők és rendezés</h3>
            <button v-if="hasActiveFilters" class="clear-button" @click="clearFilters">
              <i class='bx bx-reset'></i> Szűrők törlése
            </button>
          </div>
          
          <div class="filter-row">
            <div class="filter-group">
              <label><i class='bx bx-world'></i> Típus:</label>
              <div class="chip-container">
                <button 
                  class="chip" 
                  :class="{ 'active': filters.type === '' }"
                  @click="filters.type = ''; loadEvents()"
                >
                  Összes
                </button>
                <button 
                  class="chip" 
                  :class="{ 'active': filters.type === 'local' }"
                  @click="filters.type = 'local'; loadEvents()"
                >
                  <i class='bx bx-building'></i> Helyi
                </button>
                <button 
                  class="chip" 
                  :class="{ 'active': filters.type === 'global' }"
                  @click="filters.type = 'global'; loadEvents()"
                >
                  <i class='bx bx-world'></i> Globális
                </button>
              </div>
            </div>
            
            <div class="filter-group">
              <label><i class='bx bx-calendar'></i> Állapot:</label>
              <div class="chip-container">
                <button 
                  class="chip" 
                  :class="{ 'active': filters.status === '' }"
                  @click="filters.status = ''; loadEvents()"
                >
                  Összes
                </button>
                <button 
                  class="chip" 
                  :class="{ 'active': filters.status === 'open' }"
                  @click="filters.status = 'open'; loadEvents()"
                >
                  <i class='bx bx-check-circle'></i> Aktív
                </button>
                <button 
                  class="chip" 
                  :class="{ 'active': filters.status === 'closed' }"
                  @click="filters.status = 'closed'; loadEvents()"
                >
                  <i class='bx bx-x-circle'></i> Lezárt
                </button>
              </div>
            </div>
            
            <div class="filter-group sorting">
              <label><i class='bx bx-sort'></i> Rendezés:</label>
              <div class="sorting-buttons">
                <button 
                  class="sorting-button" 
                  :class="{ 'active': filters.sorting === 'newest' }"
                  @click="filters.sorting = 'newest'; loadEvents()"
                >
                  <i class='bx bx-sort-down'></i> Legújabb
                </button>
                <button 
                  class="sorting-button" 
                  :class="{ 'active': filters.sorting === 'oldest' }"
                  @click="filters.sorting = 'oldest'; loadEvents()"
                >
                  <i class='bx bx-sort-up'></i> Legrégebbi
                </button>
                <button 
                  class="sorting-button" 
                  :class="{ 'active': filters.sorting === 'start_date' }"
                  @click="filters.sorting = 'start_date'; loadEvents()"
                >
                  <i class='bx bx-calendar'></i> Kezdés
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Események lista -->
        <div class="events-list">
          <div v-if="isLoading" class="status-card loading">
            <div class="loader">
              <div class="spinner"></div>
            </div>
            <h3>Események betöltése...</h3>
            <p>Kérlek várj, amíg betöltjük az eseményeket</p>
          </div>
          
          <div v-else-if="events.length === 0" class="status-card no-events">
            <div class="empty-animation">
              <i class='bx bx-calendar-x'></i>
            </div>
            <h3>Nincsenek események</h3>
            <p>Még nem hoztak létre eseményeket az iskoládban.</p>
            <router-link v-if="canCreateEvent" to="/event-creator" class="btn btn-primary">
              <i class='bx bx-plus'></i> Első esemény létrehozása
            </router-link>
          </div>
          
          <div v-else class="events-grid">
            <div v-for="event in events" :key="event.id" class="event-card">
              <div class="card-header">
                <div class="type-badge" :class="event.type">
                  <i class='bx bx-building' v-if="event.type === 'local'"></i>
                  <i class='bx bx-world' v-else></i>
                  <span>{{ event.type === 'local' ? 'Helyi' : 'Globális' }}</span>
                </div>
                <div class="status-badge" :class="event.status">
                  <span class="dot"></span>
                  {{ event.status === 'open' ? 'Aktív' : 'Lezárva' }}
                </div>
              </div>
              
              <div class="card-content">
                <h3>{{ event.title }}</h3>
                <p class="description">{{ event.description }}</p>
                
                <div class="meta-info">
                  <div class="meta-row">
                    <i class='bx bx-calendar'></i>
                    <span>{{ formatDate(event.start_date) }}</span>
                  </div>
                  <div class="meta-row">
                    <i class='bx bx-user'></i>
                    <span>{{ event.creator_name }}</span>
                  </div>
                </div>
              </div>
              
              <div class="card-footer">
                <div class="stats">
                  <div class="stat-item" title="Résztvevők">
                    <i class='bx bx-user-check'></i>
                    <span>{{ event.participants || 0 }}</span>
                  </div>
                  <div class="stat-item" title="Kedvencek">
                    <i class='bx bx-star'></i>
                    <span>{{ event.favorites || 0 }}</span>
                  </div>
                  <div class="stat-item" title="Hozzászólások">
                    <i class='bx bx-message-square-detail'></i>
                    <span>{{ event.comment_count || 0 }}</span>
                  </div>
                </div>
                
                <router-link 
                  :to="`/esemenyek/${event.id}`" 
                  class="details-button"
                >
                  <span>Részletek</span>
                  <i class='bx bx-right-arrow-alt'></i>
                </router-link>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Új esemény gomb (csak tanár/admin) -->
        <div v-if="canCreateEvent" class="new-event-button">
          <router-link to="/event-creator" class="btn btn-success">
            <i class='bx bx-plus-circle'></i> Új esemény létrehozása
          </router-link>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'EsemenyekLista',
  
  data() {
    return {
      events: [],
      isLoading: true,
      currentUser: null,
      showUserMenu: false,
      filters: {
        type: '',
        status: '',
        sorting: 'newest'
      }
    }
  },
  
  computed: {
    normalizedRole() {
      return String(this.currentUser?.role || '').toLowerCase();
    },

    userInitials() {
      const fullName = (this.currentUser?.nev || this.currentUser?.name || '').toString().trim();
      if (fullName) {
        return fullName
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
      }

      const email = (this.currentUser?.email || '').toString().trim();
      if (email) {
        return email[0].toUpperCase();
      }

      return '?';
    },
    
    roleDisplay() {
      const roles = {
        'student': 'Diák',
        'teacher': 'Tanár',
        'admin': 'Admin',
        'institution_manager': 'Intézményvezető'
      };
      return roles[this.normalizedRole] || 'Vendég';
    },
    
    activeEventsCount() {
      return this.events.filter(event => event.status === 'open').length;
    },
    
    totalParticipants() {
      return this.events.reduce((accumulator, event) => accumulator + (event.participants || 0), 0);
    },
    
    canCreateEvent() {
      return ['teacher', 'admin', 'institution_manager'].includes(this.normalizedRole);
    },
    
    hasActiveFilters() {
      return this.filters.type !== '' || this.filters.status !== '';
    }
  },
  
  async created() {
    await this.loadCurrentUser();
    await this.loadEvents();
  },

  mounted() {
    document.addEventListener('click', this.closeUserMenu);
  },

  beforeUnmount() {
    document.removeEventListener('click', this.closeUserMenu);
  },
  
  methods: {
    toggleUserMenu() {
      this.showUserMenu = !this.showUserMenu;
    },

    closeUserMenu() {
      this.showUserMenu = false;
    },

    async logout() {
      try {
        const token =
          localStorage.getItem('esemenyter_token') ||
          sessionStorage.getItem('esemenyter_token');

        await axios.delete('http://127.0.0.1:8000/api/logout', {
          headers: token ? { Authorization: `Bearer ${token}` } : {}
        });
      } catch (error) {
        console.error('Logout hiba:', error);
      } finally {
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
      }
    },

    async loadCurrentUser() {
      try {
        const savedUser =
          localStorage.getItem('esemenyter_user') ||
          sessionStorage.getItem('esemenyter_user');
        if (savedUser) {
          this.currentUser = JSON.parse(savedUser);
        }
      } catch (error) {
        console.error('Hiba a felhasználó betöltésekor:', error);
      }
    },
  
    async loadEvents() {
      try {
        this.isLoading = true;
        
        if (!this.currentUser) {
          this.events = [];
          return;
        }
        
        this.events = await this.fetchEventsFromApi();
        
      } catch (error) {
        console.error('Hiba az események betöltésekor:', error);
        this.events = [];
      } finally {
        this.isLoading = false;
      }
    },
    
    async fetchEventsFromApi() {
      const { data } = await axios.get('http://127.0.0.1:8000/api/events', {
        headers: {
          Accept: 'application/json'
        }
      });

      const incomingEvents = Array.isArray(data)
        ? data
        : Array.isArray(data?.events)
          ? data.events
          : [];

      return incomingEvents
        .filter(event => {
          if (this.filters.type && event.type !== this.filters.type) return false;
          if (this.filters.status && event.status !== this.filters.status) return false;
          return true;
        })
        .sort((a, b) => {
          switch (this.filters.sorting) {
            case 'oldest':
              return new Date(a.start_date) - new Date(b.start_date);
            case 'start_date':
              return new Date(a.start_date) - new Date(b.start_date);
            case 'newest':
            default:
              return new Date(b.start_date) - new Date(a.start_date);
          }
        });
    },
    
    formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString('hu-HU', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    },
    
    clearFilters() {
      this.filters.type = '';
      this.filters.status = '';
      this.loadEvents();
    }
  }
}
</script>

<style scoped>
/* Alap stílusok (EventDetails-ből átvéve) */
.events-page {
  min-width: 100vw;
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

.container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
}

/* Fejléc (EventDetails-ből) */
.events-header {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  padding: 16px 0;
  position: relative;
  top: 0;
  z-index: 100;
}

.header-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
}

.logo-title {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  transition: opacity 0.3s ease;
}

.logo-title:hover {
  opacity: 0.8;
}

.logo-title i {
  font-size: 32px;
  color: #667eea;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.site-title {
  margin: 0;
  font-size: 24px;
  font-weight: 700;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.logo-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.site-subtitle {
  margin: 0;
  font-size: 14px;
  color: #64748b;
  font-weight: 500;
  line-height: 1.2;
}

.user-profile {
  position: relative;
}

.user-avatar {
  display: flex;
  align-items: center;
  gap: 15px;
  cursor: pointer;
  padding: 5px 10px;
  border-radius: 50px;
  transition: background 0.3s ease;
}

.user-avatar:hover {
  background: #f3f4f6;
}

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
  background: linear-gradient(135deg, #667eea10, #764ba210);
  border-bottom: 1px solid #e5e7eb;
}

.menu-user-info h4 {
  margin: 0 0 5px 0;
  color: #374151;
  font-size: 16px;
}

.user-email {
  margin: 0;
  color: #6b7280;
  font-size: 14px;
}

.role-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 50px;
  font-size: 12px;
  font-weight: 600;
  margin-top: 10px;
}

.role-badge.institution {
  background: #4f46e5;
  color: white;
}

.role-badge.student {
  background: rgba(16, 185, 129, 0.2);
  color: #10b981;
}

.role-badge.teacher {
  background: rgba(249, 115, 22, 0.2);
  color: #f97316;
}

.role-badge.admin,
.role-badge.institution_manager {
  background: rgba(139, 92, 246, 0.2);
  color: #8b5cf6;
}

.menu-items {
  padding: 10px;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-radius: 10px;
  color: #374151;
  text-decoration: none;
  transition: all 0.3s ease;
  width: 100%;
  border: none;
  background: none;
  font-size: 14px;
  cursor: pointer;
  text-align: left;
}

.menu-item:hover {
  background: #f3f4f6;
  color: #4f46e5;
}

.menu-item i {
  font-size: 20px;
  color: #6b7280;
  transition: color 0.3s ease;
}

.menu-item:hover i {
  color: #4f46e5;
}

.menu-divider {
  height: 1px;
  background: #e5e7eb;
  margin: 8px 0;
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

.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.3s ease-out;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateY(-10px);
  opacity: 0;
}

.avatar-circle {
  width: 45px;
  height: 45px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 18px;
  box-shadow: 0 4px 10px rgba(102, 126, 234, 0.3);
}

.user-status {
  position: relative;
}

.status-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  border: 2px solid white;
  position: absolute;
  bottom: 2px;
  right: 2px;
}

.status-dot.online {
  background: #10b981;
  box-shadow: 0 0 0 2px white;
}

/* Hero szekció (EventDetails-ből) */
.hero-section {
  position: relative;
  border-radius: 32px;
  overflow: hidden;
  margin-bottom: 2rem;
  background: linear-gradient(45deg, #1a202c, #2d3748);
  min-height: 300px;
  display: flex;
  align-items: center;
}

.hero-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(to right, rgba(0,0,0,0.8), rgba(0,0,0,0.4));
}

.hero-content {
  position: relative;
  padding: 3rem;
  width: 100%;
  color: white;
}

.hero-badges {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.badge {
  padding: 0.5rem 1rem;
  border-radius: 50px;
  font-size: 0.875rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  backdrop-filter: blur(10px);
  background: rgba(255, 255, 255, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: white;
}

.badge.open {
  background: rgba(72, 187, 120, 0.2);
  border-color: rgba(72, 187, 120, 0.4);
}

.hero-title {
  font-size: 3rem;
  font-weight: 800;
  line-height: 1.2;
  margin-bottom: 1rem;
}

.hero-description {
  font-size: 1.25rem;
  opacity: 0.9;
  max-width: 600px;
}

/* Statisztika kártyák (EventDetails-ből) */
.stats-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  border-radius: 24px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  transition: all 0.3s;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.stat-icon {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #667eea20, #764ba220);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-icon i {
  font-size: 2rem;
  color: #667eea;
}

.stat-data {
  display: flex;
  flex-direction: column;
}

.stat-number {
  font-size: 2rem;
  font-weight: 700;
  color: #1a202c;
  line-height: 1.2;
}

.stat-label {
  font-size: 0.875rem;
  color: #718096;
}

/* Szűrők rész (EventDetails stílusban) */
.filters-section {
  background: white;
  border-radius: 24px;
  padding: 2rem;
  margin-bottom: 2rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.filter-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  padding-bottom: 1.5rem;
  border-bottom: 2px solid #f0f0f0;
}

.filter-header h3 {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 1.25rem;
  font-weight: 700;
  color: #1a202c;
  margin: 0;
}

.filter-header h3 i {
  color: #667eea;
}

.clear-button {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: #ffebee;
  border: none;
  border-radius: 50px;
  color: #ef4444;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s;
}

.clear-button:hover {
  background: #ef4444;
  color: white;
}

.filter-row {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.filter-group label {
  display: block;
  margin-bottom: 0.75rem;
  color: #4a5568;
  font-size: 0.875rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.chip-container {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.chip {
  padding: 0.625rem 1.25rem;
  background: #f7fafc;
  border: 2px solid transparent;
  border-radius: 50px;
  color: #4a5568;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s;
}

.chip:hover {
  background: #edf2f7;
  transform: translateY(-2px);
}

.chip.active {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
}

.sorting-buttons {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.sorting-button {
  padding: 0.625rem 1.25rem;
  background: #f7fafc;
  border: 2px solid transparent;
  border-radius: 50px;
  color: #4a5568;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s;
}

.sorting-button:hover {
  background: #edf2f7;
  transform: translateY(-2px);
}

.sorting-button.active {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
}

/* Események grid */
.events-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 1.5rem;
  margin-top: 2rem;
}

/* Esemény kártya (EventDetails stílusban) */
.event-card {
  background: white;
  border-radius: 24px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  transition: all 0.3s;
  display: flex;
  flex-direction: column;
}

.event-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.card-header {
  padding: 1rem 1.5rem;
  background: #f7fafc;
  border-bottom: 2px solid #f0f0f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.type-badge {
  padding: 0.375rem 0.875rem;
  border-radius: 50px;
  font-size: 0.75rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.type-badge.local {
  background: #e3f2fd;
  color: #1565c0;
}

.type-badge.global {
  background: #f3e5f5;
  color: #7b1fa2;
}

.status-badge {
  padding: 0.375rem 0.875rem;
  border-radius: 50px;
  font-size: 0.75rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.status-badge .dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  display: inline-block;
}

.status-badge.open {
  background: #e8f5e9;
  color: #2e7d32;
}

.status-badge.open .dot {
  background: #2e7d32;
  box-shadow: 0 0 0 2px rgba(46, 125, 50, 0.2);
}

.status-badge.closed {
  background: #ffebee;
  color: #c62828;
}

.status-badge.closed .dot {
  background: #c62828;
  box-shadow: 0 0 0 2px rgba(198, 40, 40, 0.2);
}

.card-content {
  padding: 1.5rem;
  flex: 1;
}

.card-content h3 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1a202c;
  margin-bottom: 0.75rem;
}

.description {
  color: #718096;
  font-size: 0.875rem;
  line-height: 1.6;
  margin-bottom: 1.5rem;
  display: -webkit-box;
  line-clamp: 3;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.meta-info {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  padding-top: 1rem;
  border-top: 2px solid #f0f0f0;
}

.meta-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: #a0aec0;
  font-size: 0.875rem;
}

.meta-row i {
  font-size: 1rem;
  color: #667eea;
}

.card-footer {
  padding: 1rem 1.5rem;
  background: #f7fafc;
  border-top: 2px solid #f0f0f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.stats {
  display: flex;
  gap: 1rem;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  color: #718096;
  font-size: 0.75rem;
}

.stat-item i {
  font-size: 1rem;
  color: #667eea;
}

.details-button {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: linear-gradient(135deg, #667eea, #764ba2);
  border: none;
  border-radius: 50px;
  color: white;
  font-size: 0.75rem;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s;
}

.details-button:hover {
  transform: translateX(5px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

/* Állapot kártyák (EventDetails-ből) */
.status-card {
  background: white;
  border-radius: 32px;
  padding: 3rem 2rem;
  text-align: center;
  max-width: 500px;
  margin: 4rem auto;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.status-card.loading .loader {
  margin-bottom: 1.5rem;
}

.spinner {
  width: 64px;
  height: 64px;
  border: 4px solid #e2e8f0;
  border-top-color: #667eea;
  border-radius: 50%;
  margin: 0 auto;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.status-card h3 {
  font-size: 1.5rem;
  color: #1a202c;
  margin-bottom: 0.75rem;
  font-weight: 700;
}

.status-card p {
  color: #718096;
  line-height: 1.6;
}

.empty-animation i {
  font-size: 4rem;
  color: #cbd5e0;
  margin-bottom: 1.5rem;
}

/* Gombok (EventDetails-ből) */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1.75rem;
  border: none;
  border-radius: 50px;
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
  transition: all 0.3s;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
}

.btn-success {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
}

.btn-success:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4);
}

/* Új esemény gomb */
.new-event-button {
  text-align: center;
  margin-top: 3rem;
}

.new-event-button .btn {
  padding: 1rem 2rem;
  font-size: 1.125rem;
}

/* Reszponzív */
@media (max-width: 1024px) {
  .hero-title {
    font-size: 2.5rem;
  }
}

@media (max-width: 768px) {
  .container {
    padding: 1rem;
  }
  
  .header-content {
    flex-direction: column;
    text-align: center;
  }
  
  .hero-content {
    padding: 2rem;
  }
  
  .hero-title {
    font-size: 2rem;
  }
  
  .hero-badges {
    flex-direction: column;
  }
  
  .badge {
    width: fit-content;
  }
  
  .stats-cards {
    grid-template-columns: 1fr;
  }
  
  .events-grid {
    grid-template-columns: 1fr;
  }
  
  .card-footer {
    flex-direction: column;
    gap: 1rem;
  }
  
  .stats {
    justify-content: center;
  }
  
  .details-button {
    width: 100%;
    justify-content: center;
  }
  
  .filter-header {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }
  
  .sorting-buttons {
    flex-direction: column;
  }
  
  .sorting-button {
    width: 100%;
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .hero-title {
    font-size: 1.75rem;
  }
  
  .chip-container {
    flex-direction: column;
  }
  
  .chip {
    width: 100%;
    justify-content: center;
  }
}
</style>