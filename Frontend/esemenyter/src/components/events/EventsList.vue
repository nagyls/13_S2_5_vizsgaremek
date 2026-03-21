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
            <div class="hero-actions">
              <router-link to="/events-calendar" class="hero-calendar-btn">
                <i class='bx bx-calendar-week'></i>
                Naptár nézet
              </router-link>
            </div>
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
        <div class="filters-section compact">
          <div class="filter-header compact">
            <h3><i class='bx bx-filter-alt'></i> <span class="filter-label">Szűrés:</span></h3>
            <button v-if="hasActiveFilters" class="clear-button" @click="clearFilters">
              <i class='bx bx-reset'></i>
              <span>Szűrők törlése</span>
            </button>
          </div>
          
          <div class="filter-row compact">
            <div class="filter-group inline">
              <label class="inline-label"><i class='bx bx-world'></i></label>
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
            
            <div class="filter-group inline">
              <label class="inline-label"><i class='bx bx-calendar'></i></label>
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
            
            <div class="filter-group inline sorting">
              <label class="inline-label"><i class='bx bx-sort'></i></label>
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
                <div v-if="event.is_recurring" class="recurring-note">
                  <i class='bx bx-repeat'></i>
                  <span>Heti ismétlődő alkalom</span>
                </div>
                
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
                  <div class="stat-item" title="Részt vesz">
                    <i class='bx bx-user-check'></i>
                    <span>{{ event.attending_count ?? event.participants ?? 0 }}</span>
                  </div>
                  <div class="stat-item" title="Nem vesz részt">
                    <i class='bx bx-user-x'></i>
                    <span>{{ event.not_attending_count ?? 0 }}</span>
                  </div>
                  <div class="stat-item" title="Kedvencek">
                    <i class='bx bx-star'></i>
                    <span>{{ event.favorites || 0 }}</span>
                  </div>
                  <div class="stat-item" title="Hozzászólások">
                    <i class='bx bx-message-square-detail'></i>
                    <span>{{ event.comment_count || 0 }}</span>
                    <small>Komment</small>
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
        'admin': 'Admin'
      };
      return roles[this.normalizedRole] || 'Vendég';
    },
    
    activeEventsCount() {
      return this.events.filter(event => event.status === 'open').length;
    },
    
    totalParticipants() {
      return this.events.reduce((accumulator, event) => accumulator + (event.attending_count ?? event.participants ?? 0), 0);
    },
    
    canCreateEvent() {
      return ['teacher', 'admin'].includes(this.normalizedRole);
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

    getCurrentInstitutionId() {
      const storedInstitutionId =
        localStorage.getItem('CurrentInstitution') ||
        sessionStorage.getItem('CurrentInstitution') ||
        this.currentUser?.institution_id ||
        this.currentUser?.establishment_id;

      const institutionId = Number(storedInstitutionId);
      return Number.isFinite(institutionId) && institutionId > 0 ? institutionId : null;
    },

    normalizeEventStatus(status) {
      const normalized = String(status || '').toLowerCase();

      if (normalized === 'ongoing' || normalized === 'open') {
        return 'open';
      }

      if (normalized === 'ended' || normalized === 'closed') {
        return 'closed';
      }

      if (normalized === 'upcoming') {
        return 'open';
      }

      return 'open';
    },

    normalizeEventForList(event) {
      const normalizedStatus = this.normalizeEventStatus(event?.status);
      const creatorName =
        event?.creator_name ||
        event?.creator?.name ||
        event?.user?.name ||
        'Ismeretlen szervező';

      return {
        ...event,
        id: Number(event?.id),
        is_recurring: Boolean(event?.is_recurring),
        recurrence_parent_event_id: event?.recurrence_parent_event_id ? Number(event?.recurrence_parent_event_id) : null,
        status: normalizedStatus,
        creator_name: creatorName,
        participants: Number(event?.participants || event?.participant_count || 0),
        attending_count: Number(event?.attending_count || event?.participants || event?.participant_count || 0),
        not_attending_count: Number(event?.not_attending_count || 0),
        favorites: Number(event?.favorites || event?.favorite_count || 0),
        comment_count: Number(event?.comment_count || event?.comments_count || 0)
      };
    },

    collapseRecurringSeries(events) {
      const now = Date.now();
      const groupedRecurring = new Map();
      const standalone = [];

      const buildRecurringFallbackKey = (event) => {
        const startValue = String(event?.start_date || '');
        const timeMatch = startValue.match(/(\d{2}:\d{2})/);
        const startTime = timeMatch ? timeMatch[1] : '00:00';

        return [
          Number(event?.user_id || 0),
          Number(event?.establishment_id || 0),
          String(event?.title || '').trim().toLowerCase(),
          String(event?.recurrence_frequency || ''),
          String(event?.recurrence_until || ''),
          startTime
        ].join('|');
      };

      events.forEach((event) => {
        if (!event?.is_recurring) {
          standalone.push(event);
          return;
        }

        const seriesKey = event?.recurrence_parent_event_id
          ? `parent:${Number(event.recurrence_parent_event_id)}`
          : `fallback:${buildRecurringFallbackKey(event)}`;

        if (!groupedRecurring.has(seriesKey)) {
          groupedRecurring.set(seriesKey, []);
        }

        groupedRecurring.get(seriesKey).push(event);
      });

      const recurringRepresentatives = Array.from(groupedRecurring.values()).map((seriesEvents) => {
        const validEvents = seriesEvents.filter(item => !Number.isNaN(new Date(item?.start_date).getTime()));
        if (!validEvents.length) {
          return seriesEvents[0];
        }

        const upcoming = validEvents
          .filter(item => new Date(item.start_date).getTime() >= now)
          .sort((a, b) => new Date(a.start_date) - new Date(b.start_date));

        if (upcoming.length) {
          return upcoming[0];
        }

        const latestPast = validEvents
          .sort((a, b) => new Date(b.start_date) - new Date(a.start_date));

        return latestPast[0];
      });

      return [...standalone, ...recurringRepresentatives];
    },

    async fetchEventsFromApi() {
      const token =
        localStorage.getItem('esemenyter_token') ||
        sessionStorage.getItem('esemenyter_token');

      const institutionId = this.getCurrentInstitutionId();
      const normalizedInstitutionId = Number(institutionId);

      if (!Number.isFinite(normalizedInstitutionId) || normalizedInstitutionId <= 0) {
        return [];
      }

      const endpoint = `http://127.0.0.1:8000/api/establishment/${normalizedInstitutionId}/events`;

      const response = await axios.get(endpoint, {
        headers: {
          Accept: 'application/json',
          ...(token ? { Authorization: `Bearer ${token}` } : {})
        },
        // Itt helyben kezeljuk a hibakat, hogy ne triggerelodjon globalis kijelentkeztetes.
        validateStatus: (status) => status >= 200 && status < 600
      });

      if (response.status >= 400) return [];

      const { data } = response;

      const incomingEvents = Array.isArray(data)
        ? data
        : Array.isArray(data?.events)
          ? data.events
          : [];

      const normalizedEvents = incomingEvents
        .map((event) => this.normalizeEventForList(event));

      const collapsedEvents = this.collapseRecurringSeries(normalizedEvents);

      return collapsedEvents
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
  width: 100%;
  min-height: 100vh;
  overflow-x: hidden;
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

.role-badge.admin {
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

.hero-actions {
  margin-top: 1.25rem;
}

.hero-calendar-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.6rem;
  background: linear-gradient(135deg, #22c55e, #16a34a);
  color: white;
  text-decoration: none;
  font-weight: 700;
  padding: 0.85rem 1.2rem;
  border-radius: 999px;
  box-shadow: 0 10px 24px rgba(34, 197, 94, 0.35);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.hero-calendar-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 14px 28px rgba(34, 197, 94, 0.45);
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
  padding: 1.25rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.filters-section.compact {
  padding: 1rem;
}

.filter-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #e5e7eb;
}

.filter-header.compact {
  margin-bottom: 0.75rem;
  padding-bottom: 0.75rem;
}

.filter-header h3 {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.95rem;
  font-weight: 600;
  color: #374151;
  margin: 0;
}

.filter-label {
  display: none;
}

@media (min-width: 1024px) {
  .filter-label {
    display: inline;
  }
}

.filter-header h3 i {
  color: #667eea;
  font-size: 1.1rem;
}

.clear-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.6rem 1.2rem;
  background: #fee2e2;
  border: none;
  border-radius: 50px;
  color: #dc2626;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.clear-button:hover {
  background: #dc2626;
  color: white;
}

.filter-row {
  display: flex;
  flex-direction: row;
  gap: 2rem;
  flex-wrap: nowrap;
  align-items: flex-start;
  justify-content: space-between;
}

.filter-row.compact {
  gap: 2rem;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  min-width: fit-content;
  padding: 0.75rem 1rem;
  background: #f9fafb;
  border-radius: 12px;
  border-left: 4px solid #667eea;
}

.filter-group.inline {
  flex-direction: row;
  align-items: center;
  gap: 0.75rem;
  padding: 0.65rem 0.9rem;
}

.filter-group label {
  color: #4a5568;
  font-size: 0.75rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.inline-label {
  margin-bottom: 0 !important;
  display: inline-flex;
  padding: 0.35rem 0.5rem;
  background: #f3f4f6;
  border-radius: 8px;
  color: #667eea;
  font-size: 0.9rem;
}

.chip-container {
  display: flex;
  gap: 0.4rem;
  flex-wrap: wrap;
}

.chip {
  padding: 0.475rem 0.95rem;
  background: #f3f4f6;
  border: 1px solid #e5e7eb;
  border-radius: 50px;
  color: #4b5563;
  font-size: 0.8rem;
  font-weight: 500;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.3rem;
  transition: all 0.2s;
  white-space: nowrap;
}

.chip:hover {
  background: #e5e7eb;
  border-color: #d1d5db;
}

.chip.active {
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-color: #667eea;
  color: white;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.chip i {
  font-size: 0.9rem;
}

.sorting-buttons {
  display: flex;
  gap: 0.4rem;
  flex-wrap: wrap;
}

.sorting-button {
  padding: 0.475rem 0.95rem;
  background: #f3f4f6;
  border: 1px solid #e5e7eb;
  border-radius: 50px;
  color: #4b5563;
  font-size: 0.8rem;
  font-weight: 500;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.3rem;
  transition: all 0.2s;
  white-space: nowrap;
}

.sorting-button:hover {
  background: #e5e7eb;
  border-color: #d1d5db;
}

.sorting-button.active {
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-color: #667eea;
  color: white;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.sorting-button i {
  font-size: 0.9rem;
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

.recurring-note {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  margin-bottom: 1rem;
  padding: 0.25rem 0.5rem;
  background: #ecfdf3;
  border: 1px solid #bbf7d0;
  border-radius: 999px;
  color: #166534;
  font-size: 0.75rem;
  font-weight: 600;
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
  gap: 0.5rem;
  flex-wrap: wrap;
}


.stat-item {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  color: #718096;
  font-size: 0.75rem;
  background: #eef2ff;
  border: 1px solid #dbe4ff;
  border-radius: 999px;
  padding: 0.25rem 0.55rem;
}

.stat-item i {
  font-size: 1rem;
  color: #667eea;
}

.stat-item span {
  color: #334155;
  font-weight: 700;
}

.stat-item small {
  color: #64748b;
  font-size: 0.68rem;
  font-weight: 600;
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

  .user-menu {
    right: auto;
    left: 50%;
    transform: translateX(-50%);
    width: min(300px, calc(100vw - 2rem));
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
  
  .filter-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
  
  .filter-group {
    width: 100%;
  }
  
  .filter-group.inline {
    flex-direction: column;
    align-items: flex-start;
    width: 100%;
  }
  
  .chip-container {
    width: 100%;
  }
  
  .sorting-buttons {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .container {
    padding: 0.75rem;
  }

  .hero-title {
    font-size: 1.75rem;
  }
  
  .filters-section {
    padding: 0.75rem;
    margin-bottom: 1rem;
  }
  
  .filter-header h3 {
    font-size: 0.85rem;
  }
  
  .filter-label {
    display: none !important;
  }
  
  .chip-container {
    gap: 0.35rem;
  }
  
  .chip {
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
  }
  
  .sorting-button {
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
  }
}
</style>