<template>
  <div class="event-details">
    <div class="container">
      <!-- Navigáció -->
      <div class="navigation">
        <button class="btn-back" @click="backToEvents">
          <i class='bx bx-arrow-back'></i>
          <span>Vissza az eseményekhez</span>
        </button>
      </div>

      <!-- Betöltés állapot -->
      <div v-if="isLoading" class="status-card loading">
        <div class="loader">
          <div class="spinner"></div>
        </div>
        <h3>Esemény betöltése...</h3>
        <p>Kérlek várj, amíg betöltjük az esemény részleteit</p>
      </div>

      <!-- Hiba állapot -->
      <div v-else-if="errorMessage" class="status-card error-state">
        <div class="error-icon">
          <i class='bx bx-error-circle'></i>
        </div>
        <h3>Hiba történt</h3>
        <p class="error-message">{{ errorMessage }}</p>
        <div class="error-actions">
          <button @click="$router.back()" class="btn btn-secondary">
            <i class='bx bx-arrow-back'></i> Vissza
          </button>
          <button @click="$router.push('/esemenyek')" class="btn btn-primary">
            <i class='bx bx-grid'></i> Összes esemény
          </button>
        </div>
      </div>

      <!-- Esemény tartalom -->
      <div v-else-if="eventData" class="event-content">
        <!-- Hero szekció -->
        <div class="hero-section">
          <div class="hero-overlay"></div>
          <div class="hero-content">
            <div class="hero-badges">
              <span class="badge" :class="eventData.type">
                <i class='bx' :class="eventData.type === 'local' ? 'bx-building' : 'bx-world'"></i>
                {{ eventData.type === 'local' ? 'Helyi esemény' : 'Globális esemény' }}
              </span>
              <span class="badge" :class="eventData.status">
                <i class='bx' :class="eventData.status === 'open' ? 'bx-calendar-event' : 'bx-calendar-x'"></i>
                {{ eventData.status === 'open' ? 'Aktív' : 'Lezárva' }}
              </span>
            </div>
            <h1 class="hero-title">{{ eventData.title }}</h1>
            <div class="hero-actions">
              <button v-if="currentUser" class="icon-button" @click="toggleFavorite">
                <i class='bx bx-star' :class="{ 'active': eventData.isFavorite }"></i>
                <span class="btn-text">Kedvenc</span>
              </button>
              <button class="icon-button" @click="shareEvent">
                <i class='bx bx-share-alt'></i>
                <span class="btn-text">Megosztás</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Fő tartalom rács -->
        <div class="content-grid">
          <!-- Bal oldali oszlop -->
          <div class="left-column">
            <!-- Szervező infok -->
            <div class="info-block">
              <div class="block-header">
                <i class='bx bx-user'></i>
                <h2>Szervező</h2>
              </div>
              <div class="organizer-profile">
                <div class="profile-avatar">
                  {{ eventData.creator_name?.charAt(0) || '?' }}
                </div>
                <div class="profile-data">
                  <span class="profile-name">{{ eventData.creator_name || 'Ismeretlen' }}</span>
                  <span class="profile-type">Esemény szervező</span>
                </div>
              </div>
            </div>

            <!-- Leírás -->
            <div class="info-block">
              <div class="block-header">
                <i class='bx bx-detail'></i>
                <h2>Leírás</h2>
              </div>
              <p class="description">{{ eventData.description }}</p>
            </div>

            <!-- Részletes tartalom -->
            <div v-if="eventData.content" class="info-block">
              <div class="block-header">
                <i class='bx bx-file'></i>
                <h2>Részletek</h2>
              </div>
              <div class="detailed-content">{{ eventData.content }}</div>
            </div>

            <!-- Kép -->
            <div v-if="eventData.image_url" class="info-block">
              <div class="block-header">
                <i class='bx bx-image'></i>
                <h2>Galéria</h2>
              </div>
              <div class="image-container">
                <img :src="eventData.image_url" :alt="eventData.title">
              </div>
            </div>
          </div>

          <!-- Jobb oldali oszlop -->
          <div class="right-column">
            <!-- Dátum és idő -->
            <div class="info-card">
              <h3><i class='bx bx-calendar'></i> Időpontok</h3>
              <div class="date-list">
                <div class="date-item">
                  <span class="date-label">Kezdés</span>
                  <span class="date-value">{{ formatDate(eventData.start_date) }}</span>
                </div>
                <div class="date-item">
                  <span class="date-label">Befejezés</span>
                  <span class="date-value">{{ formatDate(eventData.end_date) }}</span>
                </div>
                <div class="date-item">
                  <span class="date-label">Létrehozva</span>
                  <span class="date-value">{{ formatDate(eventData.created_at) }}</span>
                </div>
              </div>
            </div>

            <!-- Statisztika -->
            <div class="info-card">
              <h3><i class='bx bx-stats'></i> Statisztika</h3>
              <div class="stats-grid">
                <div class="stat-item stat-elem">
                  <div class="stat-ikon">
                    <i class='bx bx-message-detail'></i>
                  </div>
                  <div class="stat-data">
                    <span class="stat-number">{{ commentCount }}</span>
                    <span class="stat-label">Komment</span>
                  </div>
                </div>
                <div class="stat-item stat-elem">
                  <div class="stat-ikon">
                    <i class='bx bx-check-circle'></i>
                  </div>
                  <div class="stat-data">
                    <span class="stat-number">{{ attendingCount }}</span>
                    <span class="stat-label">Részt vesz</span>
                  </div>
                </div>
                <div class="stat-item stat-elem">
                  <div class="stat-ikon">
                    <i class='bx bx-x-circle'></i>
                  </div>
                  <div class="stat-data">
                    <span class="stat-number">{{ notAttendingCount }}</span>
                    <span class="stat-label">Nem vesz részt</span>
                  </div>
                </div>
                <div class="stat-item stat-elem">
                  <div class="stat-ikon">
                    <i class='bx bx-star'></i>
                  </div>
                  <div class="stat-data">
                    <span class="stat-number">{{ favoriteCount }}</span>
                    <span class="stat-label">Kedvenc</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Részvétel -->
            <div v-if="eventData.status === 'open' && currentUser" class="info-card participation">
              <h3><i class='bx bx-check-shield'></i> Részvétel</h3>
              <p class="participation-description">{{ isFormal ? 'Hogyan szeretne részt venni az eseményen?' : 'Hogyan szeretnél részt venni az eseményen?' }}</p>
              <div class="participation-options">
                <button @click="submitParticipation('y')" 
                        class="answer-button attending" 
                        :class="{ 'active': userParticipation === 'y' }">
                  <i class='bx bx-check-circle'></i>
                  <div class="answer-content">
                    <span class="answer-title">Részvétel</span>
                    <span class="answer-description">Részt veszek az eseményen</span>
                  </div>
                </button>
                <button @click="submitParticipation('n')" 
                        class="answer-button not-attending" 
                        :class="{ 'active': userParticipation === 'n' }">
                  <i class='bx bx-x-circle'></i>
                  <div class="answer-content">
                    <span class="answer-title">Nem veszek részt</span>
                    <span class="answer-description">Nem tudok jelen lenni</span>
                  </div>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Komment szekció -->
        <div class="comment-section">
          <div class="comment-header">
            <div class="header-left">
              <i class='bx bx-message-dots'></i>
              <h2>Hozzászólások</h2>
            </div>
            <div class="comment-counter">
              <span>{{ commentCount }}</span>
              <span>komment</span>
            </div>
          </div>
          
          <CommentBox 
            :esemenyId="parseInt(eventId)"
            :currentUser="currentUser"
            @komment-sikeres="onCommentAdded"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import CommentBox from './CommentBox.vue';

export default {
  name: 'EventDetails',
  
  components: {
    CommentBox
  },
  
  data() {
    return {
      eventId: this.$route.params.id,
      eventData: null,
      currentUser: null,
      isLoading: true,
      errorMessage: '',
      commentCount: 0,
      attendingCount: 0,
      notAttendingCount: 0,
      favoriteCount: 0,
      userParticipation: null,
      studentHasClass: null
    }
  },
  
  created() {
    this.loadEvent()
    this.loadCurrentUser()
  },
  
  computed: {
    isFormal() {
      return this.currentUser?.role === 'admin' || this.currentUser?.role === 'teacher' || this.currentUser?.role === 'institution_manager';
    }
  },
  
  methods: {
    backToEvents() {
      this.$router.push('/events-list')
    },

    async loadEvent() {
      try {
        this.isLoading = true
        const foundEvent = await this.fetchEvent(this.eventId)
        
        if (!foundEvent) {
          this.errorMessage = 'A kiválasztott esemény nem létezik vagy elérhetetlen.'
          return
        }
        
        this.eventData = foundEvent
        await Promise.all([
          this.loadStats(),
          this.loadParticipationStatus()
        ])
      } catch (error) {
        console.error('Hiba az esemény betöltésekor:', error)
        this.errorMessage = 'Nem sikerült betölteni az esemény adatait. Kérlek próbáld újra később.'
      } finally {
        this.isLoading = false
      }
    },
    
    async fetchEvent(eventId) {
      const allEvents = JSON.parse(localStorage.getItem('esemenyek') || '[]')
      let event = allEvents.find(item => item.id == eventId)
      
      if (!event) {
        event = {
          id: eventId,
          title: 'Tavaszi kirándulás a Budai-hegyekben',
          description: 'Csatlakozz hozzánk egy felejthetetlen tavaszi kirándulásra! Fedezzük fel együtt a Budai-hegyek legszebb túraútvonalait, miközben új barátságokat köthetünk.',
          content: 'Találkozó: Déli pályaudvar főbejárat\nIdőtartam: 4-5 óra\nNe felejtsd el: kényelmes cipő, víz, uzsonna, jókedv!\n\nTervezett program:\n- 9:00 Találkozó\n- 9:30 Indulás\n- 12:00 Ebéd a Normafánál\n- 14:00 Visszaindulás\n- 16:00 Érkezés',
          type: 'local',
          status: 'open',
          creator_name: 'Kovács Anna',
          start_date: new Date().toISOString(),
          end_date: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString(),
          created_at: new Date(Date.now() - 3 * 24 * 60 * 60 * 1000).toISOString()
        }
      }
      
      return event
    },
    
    async loadCurrentUser() {
      const savedUser = JSON.parse(localStorage.getItem('esemenyter_user') || 'null')
      
      if (savedUser?.isLoggedIn) {
        this.currentUser = {
          id: savedUser.id || 1,
          username: savedUser.name || 'Felhasználó',
          name: savedUser.name || 'Felhasználó',
          email: savedUser.email || '',
          role: savedUser.role || 'student'
        }
      }
    },
    
    async loadStats() {
      try {
        const comments = JSON.parse(localStorage.getItem('esemeny_kommentek') || '[]')
        this.commentCount = comments.filter(item => item.event_id == this.eventId).length
        
        this.attendingCount = 45
        this.notAttendingCount = 12
        this.favoriteCount = 28
      } catch (error) {
        console.error('Hiba a statisztikák betöltésekor:', error)
      }
    },
    
    async loadParticipationStatus() {
      if (!this.currentUser) return
      
      const participationData = JSON.parse(localStorage.getItem('esemeny_resztvetel') || '[]')
      const currentUserParticipation = participationData.find(
        item => item.event_id == this.eventId && item.user_id == this.currentUser.id
      )
      
      this.userParticipation = currentUserParticipation?.valasz || null
    },
    
    async submitParticipation(answer) {
      if (!this.currentUser) {
        this.showMessage('A részvételhez be kell jelentkezned!', 'info')
        this.$router.push('/login')
        return
      }

      const canParticipate = await this.canCurrentUserParticipate()
      if (!canParticipate) {
        return
      }
      
      try {
        const participationData = JSON.parse(localStorage.getItem('esemeny_resztvetel') || '[]')
        const updatedData = participationData.filter(
          item => !(item.event_id == this.eventId && item.user_id == this.currentUser.id)
        )
        
        updatedData.push({
          event_id: this.eventId,
          user_id: this.currentUser.id,
          valasz: answer,
          frissitve: new Date().toISOString()
        })
        
        localStorage.setItem('esemeny_resztvetel', JSON.stringify(updatedData))
        const previousParticipation = this.userParticipation
        this.userParticipation = answer
        
        if (answer === 'y') {
          this.attendingCount++
          if (previousParticipation === 'n') this.notAttendingCount--
          this.showMessage('Köszönjük a részvételi szándékod!', 'success')
        } else {
          this.notAttendingCount++
          if (previousParticipation === 'y') this.attendingCount--
          this.showMessage('Válaszod rögzítettük.', 'success')
        }
      } catch (error) {
        console.error('Hiba a részvétel küldésekor:', error)
        this.showMessage('Hiba történt a válasz küldése közben.', 'error')
      }
    },

    async canCurrentUserParticipate() {
      if (!this.currentUser) {
        return false
      }

      if (this.currentUser.role !== 'student') {
        return true
      }

      if (this.studentHasClass === null) {
        this.studentHasClass = await this.checkStudentClassMembership()
      }

      const hasClass = this.studentHasClass

      if (!hasClass) {
        this.showMessage('Osztályhoz nem rendelt diákként nem tudsz eseményre jelentkezni.', 'warning')
        return false
      }

      return true
    },

    async checkStudentClassMembership() {
      try {
        const token =
          localStorage.getItem('esemenyter_token') ||
          sessionStorage.getItem('esemenyter_token')

        if (!token) {
          return false
        }

        const institutionId =
          this.currentUser?.establishment_id ||
          localStorage.getItem('CurrentInstitution') ||
          sessionStorage.getItem('CurrentInstitution') ||
          localStorage.getItem('institutionId') ||
          sessionStorage.getItem('institutionId')

        if (!institutionId) {
          return false
        }

        const classesResponse = await fetch(`http://127.0.0.1:8000/api/establishment/${institutionId}/classes`, {
          headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
          }
        })

        if (!classesResponse.ok) {
          return false
        }

        const classesPayload = await classesResponse.json()
        const classes = Array.isArray(classesPayload?.data) ? classesPayload.data : []

        for (const classItem of classes) {
          const membersResponse = await fetch(
            `http://127.0.0.1:8000/api/establishment/${institutionId}/classes/${classItem.id}`,
            {
              headers: {
                Authorization: `Bearer ${token}`,
                Accept: 'application/json'
              }
            }
          )

          if (!membersResponse.ok) {
            continue
          }

          const membersPayload = await membersResponse.json()
          const members = Array.isArray(membersPayload?.data) ? membersPayload.data : []
          const belongsToClass = members.some(member => Number(member?.id) === Number(this.currentUser.id))

          if (belongsToClass) {
            return true
          }
        }

        return false
      } catch (error) {
        console.error('Hiba az osztály tagság ellenőrzése során:', error)
        return false
      }
    },
    
    onCommentAdded() {
      this.commentCount++
      this.showMessage('Hozzászólásod sikeresen elküldtük!', 'success')
    },
    
    async toggleFavorite() {
      if (!this.currentUser) {
        this.showMessage('A kedvencekhez adáshoz jelentkezz be!', 'info')
        return
      }
      
      try {
        const favorites = JSON.parse(localStorage.getItem('esemeny_kedvencek') || '[]')
        const index = favorites.findIndex(
          item => item.event_id == this.eventId && item.user_id == this.currentUser.id
        )
        
        if (index === -1) {
          favorites.push({
            event_id: this.eventId,
            user_id: this.currentUser.id,
            letrehozva: new Date().toISOString()
          })
          this.favoriteCount++
          this.showMessage('Esemény hozzáadva a kedvencekhez!', 'success')
        } else {
          favorites.splice(index, 1)
          this.favoriteCount--
          this.showMessage('Esemény eltávolítva a kedvencekből!', 'success')
        }
        
        localStorage.setItem('esemeny_kedvencek', JSON.stringify(favorites))
        this.eventData.isFavorite = index === -1
      } catch (error) {
        console.error('Hiba a kedvenc beállításakor:', error)
        this.showMessage('Hiba történt a művelet során.', 'error')
      }
    },
    
    shareEvent() {
      if (navigator.share) {
        navigator.share({
          title: this.eventData.title,
          text: this.eventData.description,
          url: window.location.href
        }).catch(() => {
          this.copyLink()
        })
      } else {
        this.copyLink()
      }
    },
    
    copyLink() {
      navigator.clipboard.writeText(window.location.href)
      this.showMessage('Link másolva a vágólapra!', 'success')
    },
    
    formatDate(dateString) {
      if (!dateString) return 'Nincs megadva'
      const date = new Date(dateString)
      return date.toLocaleDateString('hu-HU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    },
    
    showMessage(message, type = 'success') {
      // Itt lehetne egy szép toast értesítő
      console.log(`[${type}]`, message)
      alert(message)
    }
  }
}
</script>

<style scoped>
/* Alap stílusok */
.event-details {
  min-height: 100vh;
  width: 100%;
  overflow-x: hidden;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

.container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
}

/* Navigáció */
.navigation {
  margin-bottom: 2rem;
}

.btn-back {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1.75rem;
  background: rgba(255, 255, 255, 0.95);
  border: none;
  border-radius: 50px;
  color: #2d3748;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
  backdrop-filter: blur(10px);
}

.btn-back:hover {
  transform: translateX(-5px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  background: white;
}

/* Állapot kártyák */
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

.error-icon i {
  font-size: 4rem;
  color: #fc8181;
  margin-bottom: 1rem;
}

.error-message {
  background: #fff5f5;
  color: #c53030;
  padding: 1rem;
  border-radius: 12px;
  margin: 1.5rem 0;
  font-weight: 500;
}

.error-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

/* Hero szekció */
.hero-section {
  position: relative;
  border-radius: 32px;
  overflow: hidden;
  margin-bottom: 2rem;
  background: linear-gradient(45deg, #1a202c, #2d3748);
  min-height: 400px;
  display: flex;
  align-items: flex-end;
}

.hero-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(to top, rgba(0,0,0,0.8), rgba(0,0,0,0.3));
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
}

.badge.local {
  background: rgba(72, 187, 120, 0.2);
  color: #fff;
  border: 1px solid rgba(72, 187, 120, 0.4);
}

.badge.global {
  background: rgba(66, 153, 225, 0.2);
  color: #fff;
  border: 1px solid rgba(66, 153, 225, 0.4);
}

.badge.open {
  background: rgba(237, 137, 54, 0.2);
  color: #fff;
  border: 1px solid rgba(237, 137, 54, 0.4);
}

.badge.closed {
  background: rgba(229, 62, 62, 0.2);
  color: #fff;
  border: 1px solid rgba(229, 62, 62, 0.4);
}

.hero-title {
  font-size: 3rem;
  font-weight: 800;
  line-height: 1.2;
  margin-bottom: 1.5rem;
  max-width: 800px;
}

.hero-actions {
  display: flex;
  gap: 1rem;
}

.icon-button {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1.75rem;
  background: rgba(255, 255, 255, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 50px;
  color: white;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  backdrop-filter: blur(10px);
}

.icon-button:hover {
  background: rgba(255, 255, 255, 0.25);
  transform: translateY(-2px);
}

.icon-button i.active {
  color: #ffd700;
  fill: #ffd700;
}

/* Tartalom rács */
.content-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 2rem;
  margin-bottom: 2rem;
}

/* Bal oszlop */
.left-column {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.info-block {
  background: white;
  border-radius: 24px;
  padding: 2rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.block-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.block-header i {
  font-size: 1.5rem;
  color: #667eea;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.block-header h2 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1a202c;
  margin: 0;
}

/* Szervező profil */
.organizer-profile {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  background: linear-gradient(135deg, #667eea10, #764ba210);
  padding: 1.5rem;
  border-radius: 16px;
}

.profile-avatar {
  width: 64px;
  height: 64px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  font-weight: 700;
  color: white;
}

.profile-data {
  display: flex;
  flex-direction: column;
}

.profile-name {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1a202c;
  margin-bottom: 0.25rem;
}

.profile-type {
  font-size: 0.875rem;
  color: #718096;
}

/* Leírás */
.description {
  font-size: 1.125rem;
  line-height: 1.7;
  color: #2d3748;
  margin: 0;
}

.detailed-content {
  background: #f7fafc;
  padding: 1.5rem;
  border-radius: 16px;
  color: #4a5568;
  line-height: 1.7;
  white-space: pre-line;
}

/* Kép */
.image-container {
  border-radius: 16px;
  overflow: hidden;
}

.image-container img {
  width: 100%;
  height: auto;
  display: block;
  transition: transform 0.5s;
}

.image-container:hover img {
  transform: scale(1.05);
}

/* Jobb oszlop */
.right-column {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.info-card {
  background: white;
  border-radius: 24px;
  padding: 2rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.info-card h3 {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 1.25rem;
  font-weight: 700;
  color: #1a202c;
  margin-bottom: 1.5rem;
}

.info-card h3 i {
  color: #667eea;
}

/* Dátum lista */
.date-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.date-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: #f7fafc;
  border-radius: 12px;
}

.date-label {
  color: #718096;
  font-size: 0.875rem;
  font-weight: 500;
}

.date-value {
  color: #1a202c;
  font-weight: 600;
}

/* Statisztika */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.stat-elem {
  background: #f7fafc;
  border-radius: 16px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: all 0.3s;
}

.stat-elem:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.stat-ikon {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #667eea20, #764ba220);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-ikon i {
  font-size: 1.5rem;
  color: #667eea;
}

.stat-data {
  display: flex;
  flex-direction: column;
}

.stat-number {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1a202c;
  line-height: 1.2;
}

.stat-label {
  font-size: 0.75rem;
  color: #718096;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Részvétel */
.participation-description {
  color: #718096;
  margin-bottom: 1.5rem;
}

.participation-options {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.answer-button {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.5rem;
  border: 2px solid transparent;
  border-radius: 16px;
  background: #f7fafc;
  cursor: pointer;
  transition: all 0.3s;
  width: 100%;
  text-align: left;
}

.answer-button i {
  font-size: 1.5rem;
}

.answer-content {
  display: flex;
  flex-direction: column;
}

.answer-title {
  font-weight: 700;
  margin-bottom: 0.25rem;
}

.answer-description {
  font-size: 0.75rem;
  color: #718096;
}

.answer-button.attending {
  color: #2f855a;
}

.answer-button.attending:hover,
.answer-button.attending.active {
  background: #2f855a;
  color: white;
}

.answer-button.attending.active .answer-description {
  color: rgba(255, 255, 255, 0.9);
}

.answer-button.not-attending {
  color: #c53030;
}

.answer-button.not-attending:hover,
.answer-button.not-attending.active {
  background: #c53030;
  color: white;
}

.answer-button.not-attending.active .answer-description {
  color: rgba(255, 255, 255, 0.9);
}

/* Komment szekció */
.comment-section {
  background: white;
  border-radius: 24px;
  padding: 2rem;
  margin-top: 2rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.comment-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.header-left i {
  font-size: 1.5rem;
  color: #667eea;
}

.header-left h2 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1a202c;
  margin: 0;
}

.comment-counter {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  border-radius: 50px;
  font-weight: 600;
}

/* Gombok */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1.75rem;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
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

.btn-secondary {
  background: white;
  color: #2d3748;
  border: 2px solid #e2e8f0;
}

.btn-secondary:hover {
  background: #f7fafc;
  border-color: #cbd5e0;
}

/* Responsive */
@media (max-width: 1024px) {
  .content-grid {
    grid-template-columns: 1fr;
  }
  
  .hero-title {
    font-size: 2.5rem;
  }
}

@media (max-width: 768px) {
  .container {
    padding: 1rem;
  }

  .btn-back {
    width: 100%;
    justify-content: center;
  }
  
  .hero-content {
    padding: 2rem;
  }
  
  .hero-title {
    font-size: 2rem;
  }
  
  .hero-actions {
    flex-direction: column;
  }
  
  .icon-button {
    width: 100%;
    justify-content: center;
  }
  
  .hero-badges {
    flex-direction: column;
  }
  
  .badge {
    width: fit-content;
  }
  
  .organizer-profile {
    flex-direction: column;
    text-align: center;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .error-actions {
    flex-direction: column;
  }
  
  .comment-header {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }
}

@media (max-width: 480px) {
  .container {
    padding: 0.75rem;
  }

  .hero-title {
    font-size: 1.75rem;
  }

  .btn-back {
    font-size: 0.875rem;
    padding: 0.75rem 1rem;
  }
  
  .info-block,
  .info-card,
  .comment-section {
    padding: 1.5rem;
  }
  
  .date-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  
  .answer-button {
    flex-direction: column;
    text-align: center;
  }
  
  .answer-content {
    align-items: center;
  }
}

/* Animációk */
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

.event-content {
  animation: fadeIn 0.5s ease-out;
}

.info-block,
.info-card,
.comment-section {
  animation: fadeIn 0.5s ease-out;
  animation-fill-mode: both;
}

.info-block:nth-child(1) { animation-delay: 0.1s; }
.info-block:nth-child(2) { animation-delay: 0.2s; }
.info-block:nth-child(3) { animation-delay: 0.3s; }
.info-card:nth-child(1) { animation-delay: 0.15s; }
.info-card:nth-child(2) { animation-delay: 0.25s; }
.info-card:nth-child(3) { animation-delay: 0.35s; }
</style>