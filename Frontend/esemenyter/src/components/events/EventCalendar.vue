<template>
  <div class="event-calendar">
    <!-- FŐ OLDALFEJLÉC ÉS NAVIGÁCIÓ -->
    <header class="main-header">
      <div class="container">
        <div class="header-content">
          <!-- Logó és visszatérés a vezérlőpultra -->
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
            <div class="user-avatar" @click.stop="toggleUserMenu">
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
                  <!-- Admin jogosultságú linkek -->
                  <router-link
                    v-if="currentUser?.role === 'admin'"
                    to="/institution-dashboard"
                    class="menu-item"
                  >
                    <i class='bx bx-building-house'></i>
                    <span>Intézményvezetői felület</span>
                  </router-link>
                  <router-link to="/events-list" class="menu-item">
                    <i class='bx bx-calendar-event'></i>
                    <span>Események</span>
                  </router-link>
                  <router-link to="/profile" class="menu-item">
                    <i class='bx bx-user'></i>
                    <span>Profilom</span>
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

    <!-- FŐ TARTALMI RÉSZ -->
    <main class="main-content">
      <div class="container">
        <!-- Naptár fejléce: Cím és műveleti gombok -->
        <div class="calendar-header">
          <div class="header-title">
            <h1>
              <i class='bx bx-calendar'></i>
              Eseménynaptár
            </h1>
            <p>A heti ismétlődő és egyszeri események egy helyen láthatóak havi nézetben</p>
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
            <!-- Létrehozás gomb csak jogosultaknak (admin, tanár, int.vez) -->
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

        <!-- Hónap váltó navigáció -->
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

        <!-- Naptári rács megjelenítése -->
        <div v-else class="calendar-container">
          <!-- Napok nevei fejrész (Hétfő-Vasárnap) -->
          <div class="weekdays-grid">
            <div 
              v-for="day in weekdays" 
              :key="day" 
              class="weekday-cell"
            >
              {{ day }}
            </div>
          </div>

          <!-- Aktuális havi rács (napok és események) -->
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
                <!-- Esemény kártyák (maximum 3 látszik egyszerre) -->
                <router-link
                  v-for="event in dayCell.events.slice(0, 3)"
                  :key="`${event.id}-${event.calendar_role}`"
                  :to="`/esemenyek/${event.id}`"
                  class="event-chip"
                  :class="[event.type, event.calendar_role, { cancelled: isCancelled(event) }]"
                >
                  <span class="event-time">{{ formatTimeRange(event) }}</span>
                  <span class="event-title">
                    {{ isCancelled(event) ? `TÖRÖLVE - ${event.title}` : event.title }}
                  </span>
                </router-link>
                
                <!-- További események jelzése -->
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
import { API_BASE, getToken, getCurrentInstitutionId } from '../../services/api'

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
    /**
     * Felhasználó monogramjának generálása
     */
    userInitials() {
      const name = this.currentUser?.name || ''
      return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .substring(0, 2)
    },

    /**
     * Szerepkör magyar megnevezése
     */
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

    /**
     * Esemény létrehozási jogosultság ellenőrzése
     */
    canCreateEvent() {
      const role = String(this.currentUser?.role || '').toLowerCase()
      return role === 'teacher' || role === 'admin' || role === 'institution_manager'
    },

    /**
     * Aktuális hónap magyar címe (pl. 2024. május)
     */
    monthTitle() {
      return this.currentMonth.toLocaleDateString('hu-HU', {
        year: 'numeric',
        month: 'long'
      })
    },

    /**
     * Mai naptár-fejléc szöveg
     */
    todayLabel() {
      return new Date().toLocaleDateString('hu-HU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    },

    /**
     * Események csoportosítása dátum szerint a naptárhoz.
     * Kezeli a több napos eseményeket és a különböző dátum formátumokat.
     */
    eventsByDate() {
      return this.events.reduce((acc, event) => {
        if (!event.start_date) return acc

        // Dátum kulcs kinyerése (YYYY-MM-DD)
        const getISOKey = (dateStr) => {
          if (!dateStr) return null
          const match = String(dateStr).match(/^(\d{4})-(\d{2})-(\d{2})/)
          if (!match) return null
          const [, y, m, d] = match
          return `${y}-${m.padStart(2, '0')}-${d.padStart(2, '0')}`
        }

        const startKey = getISOKey(event.start_date)
        const endKey = getISOKey(event.end_date)

        // Kezdő dátumhoz rendelés
        if (startKey) {
          if (!acc[startKey]) acc[startKey] = []
          acc[startKey].push({ ...event, calendar_role: 'start' })
        }

        // Több napos esemény esetén a záró naphoz is hozzárendeljük
        if (endKey && endKey !== startKey) {
          if (!acc[endKey]) acc[endKey] = []
          acc[endKey].push({ ...event, calendar_role: 'end' })
        }

        return acc
      }, {})
    },

    /**
     * Naptári rács (cellák) generálása
     * Kiszámolja a kezdő hétfőt és záró vasárnapot a teljes nézethez.
     */
    monthCells() {
      const year = this.currentMonth.getFullYear()
      const month = this.currentMonth.getMonth()
      const startOfMonth = new Date(year, month, 1)
      const endOfMonth = new Date(year, month + 1, 0)

      // Hétfőre állítás (az előző hónap utolsó napjait is belevesszük, ha szükséges)
      const startDay = startOfMonth.getDay()
      const startDiff = startDay === 0 ? 6 : startDay - 1

      // Vasárnapra bővítjük a hónap végét a teljes hét érdekében
      const endDay = endOfMonth.getDay()
      const endDiff = endDay === 0 ? 0 : 7 - endDay

      const calendarStart = new Date(year, month, 1 - startDiff)
      const calendarEnd = new Date(endOfMonth.getFullYear(), endOfMonth.getMonth(), endOfMonth.getDate() + endDiff)
      const cells = []
      
      const todayDate = new Date()
      const todayKey = `${todayDate.getFullYear()}-${String(todayDate.getMonth() + 1).padStart(2, '0')}-${String(todayDate.getDate()).padStart(2, '0')}`

      let i = 0
      while (true) {
        const d = new Date(calendarStart.getFullYear(), calendarStart.getMonth(), calendarStart.getDate() + i)
        if (d > calendarEnd) break
        
        const y = d.getFullYear()
        const m = String(d.getMonth() + 1).padStart(2, '0')
        const day = String(d.getDate()).padStart(2, '0')
        const key = `${y}-${m}-${day}`
        
        // Események rendezése időpont szerint a cellán belül
        const dayEvents = (this.eventsByDate[key] || []).slice().sort((a, b) => {
          const left = this.extractDateTimeParts(a.start_date)
          const right = this.extractDateTimeParts(b.start_date)
          const leftMinute = left ? (Number(left.hour) * 60 + Number(left.minute)) : 0
          const rightMinute = right ? (Number(right.hour) * 60 + Number(right.minute)) : 0
          return leftMinute - rightMinute
        })

        cells.push({
          key,
          dayNumber: d.getDate(),
          isCurrentMonth: d.getMonth() === month,
          isToday: key === todayKey,
          events: dayEvents
        })

        i += 1
      }

      return cells
    }
  },

  async created() {
    await this.loadCurrentUser()
    await this.loadEvents()
  },

  mounted() {
    window.addEventListener('scroll', this.handleScroll)
    document.addEventListener('click', this.handleDocumentClick)
  },

  beforeUnmount() {
    window.removeEventListener('scroll', this.handleScroll)
    document.removeEventListener('click', this.handleDocumentClick)
  },

  methods: {
    /**
     * Felhasználói menü nyitása/zárása
     */
    toggleUserMenu() {
      this.showUserMenu = !this.showUserMenu
    },

    /**
     * Lenyíló menü bezárása külső kattintásra
     */
    handleDocumentClick(event) {
      const profile = this.$el?.querySelector('.user-profile')
      if (!profile) return

      if (!profile.contains(event.target)) {
        this.showUserMenu = false
      }
    },

    /**
     * Vissza a tetejére gomb megjelenítése görgetés alapján
     */
    handleScroll() {
      this.showScrollTop = window.scrollY > 300
    },

    /**
     * Sima visszagörgetés az oldal tetejére
     */
    scrollToTop() {
      window.scrollTo({ top: 0, behavior: 'smooth' })
    },

    /**
     * Dátum és idő részek kinyerése stringből (Regex alapú normalizálás)
     */
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

    /**
     * Idő formázása (HH:mm)
     */
    formatTime(dateValue) {
      const date = dateValue instanceof Date ? dateValue : new Date(dateValue.replace(' ', 'T'))
      if (isNaN(date.getTime())) return '--:--'
      return date.toLocaleTimeString('hu-HU', { hour: '2-digit', minute: '2-digit' })
    },

    /**
     * Esemény időtartamának formázása megjelenítéshez
     */
    formatTimeRange(event) {
      const start = this.formatTime(event?.start_date)
      const end = this.formatTime(event?.end_date)
      const status = String(event?.status || '').toLowerCase()

      // Több napos esemény záró napján, illetve lezárt eseménynél
      // mindig a befejezési idő legyen hangsúlyos.
      if (event.calendar_role === 'end' || status === 'ended' || status === 'closed') {
        return `Befejezés: ${end}`
      }

      // Kezdő napon vagy egynapos eseménynél kiírjuk a kezdést (a kép alapján "Kezdés: HH:mm")
      return `Kezdés: ${start}`
    },

    /**
     * Törölt esemény állapotának ellenőrzése
     */
    isCancelled(event) {
      return Boolean(event?.cancelled_at)
    },

    /**
     * Hónap váltása (+1 vagy -1)
     */
    changeMonth(step) {
      this.currentMonth = new Date(this.currentMonth.getFullYear(), this.currentMonth.getMonth() + step, 1)
    },

    /**
     * Felhasználói adatok betöltése local/session storage-ból
     */
    async loadCurrentUser() {
      const savedUser = localStorage.getItem('esemenyter_user') || sessionStorage.getItem('esemenyter_user')
      if (!savedUser) return

      try {
        this.currentUser = JSON.parse(savedUser)
      } catch (error) {
        this.currentUser = null
      }
    },

    /**
     * Események lehívása az API-tól az aktuális intézményhez
     */
    async loadEvents() {
      this.isLoading = true

      try {
        const token = getToken()
        const institutionId = getCurrentInstitutionId(this.currentUser)

        if (!institutionId) {
          this.events = []
          return
        }

        const response = await axios.get(
          `${API_BASE}/establishment/${institutionId}/events`,
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

        // Adatok normalizálása és tárolása
        this.events = incomingEvents.map(event => ({
          ...event,
          id: Number(event.id),
          title: event.title || 'Névtelen esemény',
          type: event.type || 'local'
        }))
      } catch (error) {
        console.error('Naptár események betöltési hiba:', error)
        this.events = []
      } finally {
        this.isLoading = false
      }
    },

    /**
     * Kijelentkezés folyamata (Token törlés és API hívás)
     */
    async logout() {
      try {
        const token = getToken()
        await axios.delete(`${API_BASE}/logout`, {
          headers: { Authorization: `Bearer ${token}` }
        })
      } catch (error) {
        console.error('Logout hiba:', error)
      } finally {
        localStorage.clear()
        sessionStorage.clear()
        delete axios.defaults.headers.common['Authorization']
        this.showUserMenu = false
        this.$router.push('/')
      }
    }
  }
}
</script>

<style scoped>
/* ==========================================================================
   ALAP STÍLUSOK ÉS GLOBÁLIS BEÁLLÍTÁSOK
   ========================================================================== */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.event-calendar {
  min-height: 100vh;
  background:
    radial-gradient(circle at 12% 14%, rgba(88, 115, 235, 0.22), transparent 36%),
    radial-gradient(circle at 88% 24%, rgba(56, 189, 248, 0.1), transparent 30%),
    linear-gradient(135deg, #0a0f1c 0%, #1a3558 52%, #0f203d 100%);
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  width: 100%;
}

.container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 24px;
}

/* ==========================================================================
   FEJLÉC ÉS NAVIGÁCIÓS SZEKCIÓ
   ========================================================================== */
.main-header {
  background: rgba(10, 15, 28, 0.74);
  backdrop-filter: blur(16px);
  border-bottom: 1px solid rgba(148, 163, 184, 0.24);
  padding: 16px 0;
  position: sticky;
  top: 0;
  z-index: 1000;
  box-shadow: 0 10px 28px rgba(2, 6, 23, 0.36);
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
  opacity: 0.92;
}

.logo-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.14);
  border: 1px solid rgba(255, 255, 255, 0.28);
  box-shadow: 0 8px 20px rgba(2, 6, 23, 0.32);
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
  background: linear-gradient(135deg, #dbeafe 0%, #93c5fd 55%, #c4b5fd 100%);
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.site-subtitle {
  margin: 0;
  font-size: 12px;
  color: rgba(226, 232, 240, 0.9);
}

/* ==========================================================================
   FELHASZNÁLÓI PROFIL ÉS LENYÍLÓ MENÜ
   ========================================================================== */
.user-profile {
  position: relative;
}

.user-avatar {
  cursor: pointer;
  position: relative;
  padding: 4px;
  border-radius: 999px;
  transition: background 0.25s ease;
}

.user-avatar:hover {
  background: rgba(255, 255, 255, 0.1);
}

.avatar-circle {
  width: 44px;
  height: 44px;
  background: linear-gradient(135deg, #4f46e5, #3b82f6);
  border: 1px solid rgba(255, 255, 255, 0.32);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 16px;
  transition: transform 0.3s ease;
  box-shadow: 0 8px 20px rgba(37, 99, 235, 0.45);
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
  border: 2px solid rgba(10, 15, 28, 0.9);
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
  z-index: 9999;
}

.menu-header {
  padding: 20px;
  background: linear-gradient(150deg, #5873eb, rgb(0 0 0));
  color: white;
}

.menu-user-info h4 {
  margin: 0 0 5px 0;
  font-size: 16px;
  font-weight: 600;
  color: #e4e2e2;
}

.user-email {
  margin: 0;
  font-size: 12px;
  opacity: 0.9;
  color: #b8b8b8;
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
  background: rgba(239, 68, 68, 0.18);
  color: #ef4444;
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

/* ==========================================================================
   NAPTÁR FEJLÉC ÉS VEZÉRLÉS (VEZÉRLŐPULT)
   ========================================================================== */
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
  color: #f8fafc;
  margin: 0 0 8px 0;
}

.header-title h1 i {
  color: #93c5fd;
  font-size: 36px;
}

.header-title p {
  color: rgba(226, 232, 240, 0.92);
  margin: 0 0 12px 0;
  font-size: 16px;
}

.today-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: rgba(255, 255, 255, 0.14);
  border: 1px solid rgba(255, 255, 255, 0.24);
  border-radius: 50px;
  font-size: 14px;
  color: #e2e8f0;
}

.today-badge i {
  color: #bfdbfe;
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

/* ==========================================================================
   NAPTÁRI RÁCS ÉS NAPOK MEGJELENÍTÉSE
   ========================================================================== */
.calendar-container {
  background: white;
  border-radius: 24px;
  overflow: hidden;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
}

.weekdays-grid {
  display: grid;
  grid-template-columns: repeat(7, minmax(0, 1fr));
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
  grid-template-columns: repeat(7, minmax(0, 1fr));
}

.weekday-cell,
.calendar-day {
  min-width: 0;
}

.calendar-day {
  min-height: 140px;
  padding: 12px;
  border-right: 1px solid #e2e8f0;
  border-bottom: 1px solid #e2e8f0;
  transition: all 0.2s ease;
  background: white;
  display: flex;
  flex-direction: column;
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
  flex-shrink: 0;
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
  flex-grow: 1;
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
  width: 100%;
  box-sizing: border-box;
}

.event-title {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  flex-grow: 1;
  color: #1e293b;
}

.event-chip.global .event-title {
  color: #92400e;
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

.event-chip.end {
  background: #fee2e2;
  border-left: 3px solid #ef4444;
}

.event-chip.end:hover {
  background: #fecaca;
}

.event-chip.end .event-time {
  color: #b91c1c;
}

.event-chip.end .event-title {
  color: #7f1d1d;
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

/* ==========================================================================
   INTERAKTÍV ELEMEK (ÜRES ÁLLAPOT, FAB, RESPONSIVE)
   ========================================================================== */
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