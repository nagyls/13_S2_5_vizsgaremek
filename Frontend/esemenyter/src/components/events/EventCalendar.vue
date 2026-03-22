<template>
  <div class="event-calendar">
    <!-- HEADER -->
    <header class="main-header">
      <div class="container">
        <div class="header-content">
          <div class="logo-section" @click="$router.push('/user-dashboard')">
            <div class="logo-icon">
              <img :src="logo2" alt="EseményTér logó" class="logo-image">
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
                    <h4>{{ currentUser?.name || 'Felhasználó' }}</h4>
                    <p class="user-email">{{ currentUser?.email || '' }}</p>
                  </div>
                  <div class="role-badge" :class="currentUser?.role">
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
                    <span>Események listája</span>
                  </router-link>
                  <router-link to="/user-dashboard" class="menu-item">
                    <i class='bx bx-home'></i>
                    <span>Főoldal</span>
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
        <!-- Calendar Header -->
        <div class="calendar-header">
          <div class="header-title">
            <h1>
              <i class='bx bx-calendar'></i>
              Eseménynaptár
            </h1>
            <p>A heti ismétlődő és egyszeri események egy helyen</p>
            <div class="today-badge">
              <i class='bx bx-calendar-alt'></i>
              <span>{{ todayLabel }}</span>
            </div>
          </div>
          
          <div class="header-actions">
            <router-link to="/events-list" class="btn btn-secondary">
              <i class='bx bx-list-ul'></i>
              Lista nézet
            </router-link>
            <router-link 
              v-if="canCreateEvent" 
              to="/event-creator" 
              class="btn btn-primary"
            >
              <i class='bx bx-plus'></i>
              Új esemény
            </router-link>
          </div>
        </div>

        <!-- Month Navigation -->
        <div class="month-navigation">
          <button @click="changeMonth(-1)" class="nav-btn prev">
            <i class='bx bx-chevron-left'></i>
            Előző
          </button>
          
          <div class="month-display">
            <h2 class="month-title">{{ monthTitle }}</h2>
          </div>
          
          <button @click="changeMonth(1)" class="nav-btn next">
            Következő
            <i class='bx bx-chevron-right'></i>
          </button>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="loading-state">
          <div class="loading-spinner">
            <i class='bx bx-loader-alt bx-spin'></i>
          </div>
          <p>Naptár betöltése...</p>
        </div>

        <!-- Calendar Grid -->
        <div v-else class="calendar-container">
          <!-- Weekdays -->
          <div class="weekdays-grid">
            <div 
              v-for="day in weekdays" 
              :key="day" 
              class="weekday-cell"
            >
              {{ day }}
            </div>
          </div>

          <!-- Calendar Days -->
          <div class="calendar-grid">
            <div
              v-for="dayCell in monthCells"
              :key="dayCell.key"
              class="calendar-day"
              :class="{
                'other-month': !dayCell.isCurrentMonth,
                'today': dayCell.isToday
              }"
            >
              <div class="day-number">{{ dayCell.dayNumber }}</div>
              
              <div class="day-events">
                <router-link
                  v-for="event in dayCell.events.slice(0, 3)"
                  :key="event.id"
                  :to="`/esemenyek/${event.id}`"
                  class="event-chip"
                  :class="[event.type, { cancelled: isCancelled(event) }]"
                >
                  <span class="event-time">{{ formatTime(event.start_date) }}</span>
                  <span class="event-title">
                    {{ isCancelled(event) ? `TÖRÖLVE - ${event.title}` : event.title }}
                  </span>
                </router-link>
                
                <div v-if="dayCell.events.length > 3" class="more-events">
                  <i class='bx bx-dots-horizontal-rounded'></i>
                  <span>+{{ dayCell.events.length - 3 }} további</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="!isLoading && monthCells.length === 0" class="empty-state">
          <i class='bx bx-calendar-x'></i>
          <h3>Nincsenek események</h3>
          <p>Ebben a hónapban még nem került rögzítésre esemény.</p>
          <router-link v-if="canCreateEvent" to="/event-creator" class="btn btn-primary">
            <i class='bx bx-plus'></i>
            Új esemény létrehozása
          </router-link>
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
import axios from 'axios'
import logo2 from '../../assets/logo2.svg'

export default {
  name: 'EventCalendar',

  data() {
    return {
      logo2,
      currentUser: null,
      isLoading: true,
      events: [],
      currentMonth: new Date(new Date().getFullYear(), new Date().getMonth(), 1),
      weekdays: ['Hétfő', 'Kedd', 'Szerda', 'Csütörtök', 'Péntek', 'Szombat', 'Vasárnap'],
      showUserMenu: false,
      showScrollTop: false
    }
  },

  computed: {
    userInitials() {
      const name = this.currentUser?.name || ''
      return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .substring(0, 2)
    },

    roleDisplayName() {
      const roles = {
        'student': 'Diák',
        'teacher': 'Tanár',
        'admin': 'Adminisztrátor',
        'institution_manager': 'Intézményvezető'
      }
      const role = String(this.currentUser?.role || '').toLowerCase()
      return roles[role] || role
    },

    canCreateEvent() {
      const role = String(this.currentUser?.role || '').toLowerCase()
      return role === 'teacher' || role === 'admin' || role === 'institution_manager'
    },

    monthTitle() {
      return this.currentMonth.toLocaleDateString('hu-HU', {
        year: 'numeric',
        month: 'long'
      })
    },

    todayLabel() {
      return new Date().toLocaleDateString('hu-HU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    },

    eventsByDate() {
      return this.events.reduce((acc, event) => {
        const parts = this.extractDateTimeParts(event.start_date)
        if (!parts) return acc

        const key = `${parts.year}-${parts.month}-${parts.day}`
        if (!acc[key]) acc[key] = []
        acc[key].push(event)
        return acc
      }, {})
    },

    monthCells() {
      const monthStart = new Date(this.currentMonth.getFullYear(), this.currentMonth.getMonth(), 1)
      const monthEnd = new Date(this.currentMonth.getFullYear(), this.currentMonth.getMonth() + 1, 0)

      // Hétfő az első nap (magyar formátum)
      let startWeekday = monthStart.getDay()
      startWeekday = startWeekday === 0 ? 6 : startWeekday - 1
      
      const gridStart = new Date(monthStart)
      gridStart.setDate(monthStart.getDate() - startWeekday)

      let endWeekday = monthEnd.getDay()
      endWeekday = endWeekday === 0 ? 6 : endWeekday - 1
      
      const gridEnd = new Date(monthEnd)
      gridEnd.setDate(monthEnd.getDate() + (6 - endWeekday))

      const cells = []
      const cursor = new Date(gridStart)

      while (cursor <= gridEnd) {
        const key = this.toDateKey(cursor)
        const dayEvents = (this.eventsByDate[key] || []).slice().sort((a, b) => {
          const left = this.extractDateTimeParts(a.start_date)
          const right = this.extractDateTimeParts(b.start_date)
          const leftMinute = left ? (Number(left.hour) * 60 + Number(left.minute)) : 0
          const rightMinute = right ? (Number(right.hour) * 60 + Number(right.minute)) : 0
          return leftMinute - rightMinute
        })

        cells.push({
          key,
          dayNumber: cursor.getDate(),
          isCurrentMonth: cursor.getMonth() === this.currentMonth.getMonth(),
          isToday: key === this.toDateKey(new Date()),
          events: dayEvents
        })

        cursor.setDate(cursor.getDate() + 1)
      }

      return cells
    }
  },

  async created() {
    await this.loadCurrentUser()
    await this.loadEvents()
    window.addEventListener('scroll', this.handleScroll)
  },

  beforeUnmount() {
    window.removeEventListener('scroll', this.handleScroll)
  },

  methods: {
    extractDateTimeParts(value) {
      if (typeof value !== 'string') return null

      const normalized = value.trim().replace(' ', 'T')
      const match = normalized.match(
        /^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})(?::(\d{2}))?(?:\.\d+)?(?:Z|[+-]\d{2}:?\d{2})?$/
      )

      if (!match) return null

      const [, year, month, day, hour, minute, second = '00'] = match
      return { year, month, day, hour, minute, second }
    },

    parseEventDateTime(value) {
      if (value instanceof Date) return value
      if (typeof value !== 'string') return new Date(NaN)

      const localMatch = this.extractDateTimeParts(value)
      if (localMatch) {
        const { year: y, month: m, day: d, hour: hh, minute: mm, second: ss = '00' } = localMatch
        return new Date(Number(y), Number(m) - 1, Number(d), Number(hh), Number(mm), Number(ss))
      }

      return new Date(value)
    },

    toDateKey(dateValue) {
      const date = this.parseEventDateTime(dateValue)
      const year = date.getFullYear()
      const month = String(date.getMonth() + 1).padStart(2, '0')
      const day = String(date.getDate()).padStart(2, '0')
      return `${year}-${month}-${day}`
    },

    formatTime(dateValue) {
      const parts = this.extractDateTimeParts(dateValue)
      if (parts) {
        return `${parts.hour}:${parts.minute}`
      }

      const date = new Date(dateValue)
      if (Number.isNaN(date.getTime())) return '--:--'

      return date.toLocaleTimeString('hu-HU', { hour: '2-digit', minute: '2-digit' })
    },

    isCancelled(event) {
      return Boolean(event?.cancelled_at)
    },

    changeMonth(step) {
      this.currentMonth = new Date(this.currentMonth.getFullYear(), this.currentMonth.getMonth() + step, 1)
    },

    getCurrentInstitutionId() {
      const savedInstitutionId =
        localStorage.getItem('CurrentInstitution') ||
        sessionStorage.getItem('CurrentInstitution') ||
        this.currentUser?.institution_id ||
        this.currentUser?.establishment_id

      const institutionId = Number(savedInstitutionId)
      return Number.isFinite(institutionId) && institutionId > 0 ? institutionId : null
    },

    async loadCurrentUser() {
      const savedUser = localStorage.getItem('esemenyter_user') || sessionStorage.getItem('esemenyter_user')
      if (!savedUser) return

      try {
        this.currentUser = JSON.parse(savedUser)
      } catch (error) {
        this.currentUser = null
      }
    },

    async loadEvents() {
      this.isLoading = true

      try {
        const token = localStorage.getItem('esemenyter_token') || sessionStorage.getItem('esemenyter_token')
        const institutionId = this.getCurrentInstitutionId()

        if (!institutionId) {
          this.events = []
          return
        }

        const response = await axios.get(
          `http://127.0.0.1:8000/api/establishment/${institutionId}/events`,
          {
            headers: {
              Accept: 'application/json',
              ...(token ? { Authorization: `Bearer ${token}` } : {})
            },
            validateStatus: (status) => status >= 200 && status < 600
          }
        )

        if (response.status >= 400) {
          this.events = []
          return
        }

        const incomingEvents = Array.isArray(response?.data)
          ? response.data
          : Array.isArray(response?.data?.events)
            ? response.data.events
            : []

        this.events = incomingEvents.map(event => ({
          ...event,
          id: Number(event.id),
          title: event.title || 'Névtelen esemény',
          type: event.type || 'local'
        }))
      } catch (error) {
        this.events = []
      } finally {
        this.isLoading = false
      }
    },

    toggleUserMenu() {
      this.showUserMenu = !this.showUserMenu
    },

    scrollToTop() {
      window.scrollTo({ top: 0, behavior: 'smooth' })
    },

    handleScroll() {
      this.showScrollTop = window.scrollY > 300
    },

    async logout() {
      try {
        const token = localStorage.getItem('esemenyter_token') || sessionStorage.getItem('esemenyter_token')
        await axios.delete('http://127.0.0.1:8000/api/logout', {
          headers: { Authorization: `Bearer ${token}` }
        })
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        localStorage.clear()
        sessionStorage.clear()
        delete axios.defaults.headers.common['Authorization']
        this.$router.push('/')
      }
    }
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

.event-calendar {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #eef2f6 100%);
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  width: 100%;
}

.container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 24px;
}

/* ===== HEADER ===== */
.main-header {
  background: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  padding: 16px 0;
  position: sticky;
  top: 0;
  z-index: 1000;
  box-shadow: 0 2px 20px rgba(0, 0, 0, 0.03);
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
  width: 44px;
  height: 44px;
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

.logo-text h1 {
  margin: 0;
  font-size: 22px;
  font-weight: 700;
  background: linear-gradient(135deg, #667eea, #764ba2);
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.site-subtitle {
  margin: 0;
  font-size: 12px;
  color: #64748b;
}

/* ===== USER PROFIL ===== */
.user-profile {
  position: relative;
}

.user-avatar {
  cursor: pointer;
  position: relative;
}

.avatar-circle {
  width: 44px;
  height: 44px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 16px;
  transition: transform 0.3s ease;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
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
  width: 10px;
  height: 10px;
  border-radius: 50%;
  border: 2px solid white;
}

.status-dot.online {
  background: #10b981;
}

/* ===== USER MENÜ ===== */
.user-menu {
  position: absolute;
  top: calc(100% + 10px);
  right: 0;
  background: white;
  border-radius: 16px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  width: 280px;
  overflow: hidden;
  z-index: 1000;
}

.menu-header {
  padding: 20px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
}

.menu-user-info h4 {
  margin: 0 0 5px 0;
  font-size: 16px;
  font-weight: 600;
}

.user-email {
  margin: 0;
  font-size: 12px;
  opacity: 0.9;
}

.role-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 11px;
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

.role-badge.admin,
.role-badge.institution_manager {
  background: rgba(139, 92, 246, 0.2);
  color: #8b5cf6;
}

.menu-items {
  padding: 8px 0;
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
  margin: 8px 20px;
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
  padding: 32px 0 48px;
}

/* ===== CALENDAR HEADER ===== */
.calendar-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 24px;
  margin-bottom: 32px;
  flex-wrap: wrap;
}

.header-title h1 {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 32px;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 8px 0;
}

.header-title h1 i {
  color: #4f46e5;
  font-size: 36px;
}

.header-title p {
  color: #64748b;
  margin: 0 0 12px 0;
  font-size: 16px;
}

.today-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: #f1f5f9;
  border-radius: 50px;
  font-size: 14px;
  color: #334155;
}

.today-badge i {
  color: #4f46e5;
  font-size: 18px;
}

.header-actions {
  display: flex;
  gap: 12px;
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.3s ease;
  text-decoration: none;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  border: none;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
}

.btn-secondary {
  background: white;
  color: #334155;
  border: 1px solid #e2e8f0;
}

.btn-secondary:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
}

/* ===== MONTH NAVIGATION ===== */
.month-navigation {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: white;
  border-radius: 20px;
  padding: 12px 20px;
  margin-bottom: 32px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
  border: 1px solid #e2e8f0;
}

.nav-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  color: #334155;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.nav-btn:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
}

.month-display {
  text-align: center;
}

.month-title {
  margin: 0;
  font-size: 24px;
  font-weight: 700;
  color: #1e293b;
}

/* ===== LOADING STATE ===== */
.loading-state {
  text-align: center;
  padding: 80px 20px;
  background: white;
  border-radius: 24px;
  border: 1px solid #e2e8f0;
}

.loading-spinner {
  font-size: 48px;
  color: #4f46e5;
  margin-bottom: 16px;
}

.loading-state p {
  color: #64748b;
  font-size: 16px;
}

/* ===== CALENDAR ===== */
.calendar-container {
  background: white;
  border-radius: 24px;
  overflow: hidden;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
}

.weekdays-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}

.weekday-cell {
  padding: 16px 12px;
  text-align: center;
  font-weight: 700;
  color: #475569;
  font-size: 14px;
  border-right: 1px solid #e2e8f0;
}

.weekday-cell:last-child {
  border-right: none;
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
}

.calendar-day {
  min-height: 140px;
  padding: 12px;
  border-right: 1px solid #e2e8f0;
  border-bottom: 1px solid #e2e8f0;
  transition: all 0.2s ease;
  background: white;
}

.calendar-day:hover {
  background: #fefce8;
}

.calendar-day:nth-child(7n) {
  border-right: none;
}

.day-number {
  font-size: 16px;
  font-weight: 600;
  color: #334155;
  margin-bottom: 8px;
  display: inline-block;
  width: 32px;
  height: 32px;
  line-height: 32px;
  text-align: center;
  border-radius: 50%;
}

.calendar-day.today .day-number {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
}

.calendar-day.other-month {
  background: #fefce8;
}

.calendar-day.other-month .day-number {
  color: #94a3b8;
}

.day-events {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.event-chip {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 4px 8px;
  background: #eff6ff;
  border-radius: 8px;
  font-size: 11px;
  text-decoration: none;
  transition: all 0.2s ease;
  cursor: pointer;
  overflow: hidden;
}

.event-chip:hover {
  transform: translateX(2px);
  background: #dbeafe;
}

.event-chip.global {
  background: #fef3c7;
}

.event-chip.global:hover {
  background: #fde68a;
}

.event-time {
  font-weight: 700;
  color: #4f46e5;
  font-size: 10px;
  flex-shrink: 0;
}

.event-chip.global .event-time {
  color: #d97706;
}

.event-chip.cancelled {
  background: #fee2e2;
}

.event-chip.cancelled:hover {
  background: #fecaca;
}

.event-chip.cancelled .event-time {
  color: #b91c1c;
}

.event-chip.cancelled .event-title {
  color: #7f1d1d;
  text-decoration: line-through;
}

.event-title {
  color: #334155;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-size: 11px;
}

.more-events {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px;
  font-size: 11px;
  color: #64748b;
  cursor: pointer;
}

.more-events i {
  font-size: 14px;
}

.more-events:hover {
  color: #4f46e5;
}

/* ===== EMPTY STATE ===== */
.empty-state {
  text-align: center;
  padding: 80px 20px;
  background: white;
  border-radius: 24px;
  border: 1px solid #e2e8f0;
}

.empty-state i {
  font-size: 64px;
  color: #94a3b8;
  margin-bottom: 20px;
}

.empty-state h3 {
  font-size: 20px;
  color: #334155;
  margin-bottom: 8px;
}

.empty-state p {
  color: #64748b;
  margin-bottom: 24px;
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
@media (max-width: 1024px) {
  .container {
    padding: 0 20px;
  }
}

@media (max-width: 768px) {
  .header-content {
    flex-direction: column;
    gap: 16px;
  }

  .calendar-header {
    flex-direction: column;
  }

  .header-actions {
    width: 100%;
  }

  .btn {
    flex: 1;
    justify-content: center;
  }

  .month-navigation {
    flex-direction: column;
    gap: 16px;
  }

  .nav-btn {
    width: 100%;
    justify-content: center;
  }

  .weekday-cell {
    padding: 12px 8px;
    font-size: 12px;
  }

  .calendar-day {
    min-height: 100px;
    padding: 8px;
  }

  .day-number {
    width: 28px;
    height: 28px;
    line-height: 28px;
    font-size: 14px;
  }

  .user-menu {
    width: 260px;
    right: -20px;
  }
}

@media (max-width: 480px) {
  .container {
    padding: 0 16px;
  }

  .header-title h1 {
    font-size: 24px;
  }

  .month-title {
    font-size: 20px;
  }

  .weekday-cell {
    font-size: 11px;
    padding: 10px 4px;
  }

  .calendar-day {
    min-height: 80px;
    padding: 6px;
  }

  .event-chip {
    padding: 2px 6px;
  }

  .event-time, .event-title {
    font-size: 9px;
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