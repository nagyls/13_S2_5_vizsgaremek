<!-- src/components/EventsList.vue -->
<template>
  <div class="events-page">
    <!-- Fejléc -->
    <header class="events-header">
      <div class="container">
        <div class="header-content">
          <button class="back-btn" @click="$router.push('/mainpage')">
            <i class='bx bx-arrow-back'></i> Főoldal
          </button>
          <h1><i class='bx bx-calendar'></i> Események</h1>
          <div class="user-info" v-if="currentUser">
            <span>{{ currentUser.name }}</span>
          </div>
        </div>
      </div>
    </header>

    <main class="events-content">
      <div class="container">
        <!-- Szűrők -->
        <div class="filters-section">
          <div class="filter-row">
            <div class="filter-group">
              <label><i class='bx bx-filter-alt'></i> Típus:</label>
              <select v-model="filters.type" @change="loadEvents">
                <option value="">Összes</option>
                <option value="local">Helyi</option>
                <option value="global">Globális</option>
              </select>
            </div>
            
            <div class="filter-group">
              <label><i class='bx bx-calendar'></i> Állapot:</label>
              <select v-model="filters.status" @change="loadEvents">
                <option value="">Összes</option>
                <option value="open">Aktív</option>
                <option value="closed">Lezárt</option>
              </select>
            </div>
            
            <div class="filter-group">
              <label><i class='bx bx-sort'></i> Rendezés:</label>
              <select v-model="filters.sort" @change="loadEvents">
                <option value="newest">Legújabb</option>
                <option value="oldest">Legrégebbi</option>
                <option value="start_date">Kezdés dátuma</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Események listája -->
        <div class="events-list">
          <div v-if="loading" class="loading">
            <i class='bx bx-loader-circle bx-spin'></i>
            <p>Események betöltése...</p>
          </div>
          
          <div v-else-if="events.length === 0" class="no-events">
            <i class='bx bx-calendar-x'></i>
            <h3>Nincsenek események</h3>
            <p>Még nem hoztak létre eseményeket az iskoládban.</p>
          </div>
          
          <div v-else>
            <!-- Esemény kártyák -->
            <div v-for="event in events" :key="event.id" class="event-card">
              <div class="event-header">
                <div class="event-type" :class="event.type">
                  <i class='bx bx-building' v-if="event.type === 'local'"></i>
                  <i class='bx bx-world' v-else></i>
                  {{ event.type === 'local' ? 'Helyi' : 'Globális' }}
                </div>
                <div class="event-status" :class="event.status">
                  {{ event.status === 'open' ? 'Aktív' : 'Lezárva' }}
                </div>
              </div>
              
              <div class="event-body">
                <h3>{{ event.title }}</h3>
                <p class="event-description">{{ event.description }}</p>
                
                <div class="event-meta">
                  <div class="meta-item">
                    <i class='bx bx-calendar'></i>
                    <span>{{ formatDate(event.start_date) }}</span>
                  </div>
                  <div class="meta-item">
                    <i class='bx bx-user'></i>
                    <span>{{ event.creator_name }}</span>
                  </div>
                  <div class="meta-item">
                    <i class='bx bx-message-square-detail'></i>
                    <span>{{ event.comment_count || 0 }} hozzászólás</span>
                  </div>
                </div>
              </div>
              
              <div class="event-footer">
                <router-link 
                  :to="`/esemenyek/${event.id}`" 
                  class="btn btn-primary"
                >
                  <i class='bx bx-show'></i> Részletek
                </router-link>
                
                <div class="event-stats">
                  <span class="stat">
                    <i class='bx bx-user-check'></i> {{ event.participants || 0 }}
                  </span>
                  <span class="stat">
                    <i class='bx bx-star'></i> {{ event.favorites || 0 }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Új esemény gomb (ha tanár vagy admin) -->
        <div v-if="currentUser && (currentUser.role === 'teacher' || currentUser.role === 'admin')" 
             class="create-event-btn">
          <router-link to="/event-creator" class="btn btn-success">
            <i class='bx bx-plus'></i> Új esemény létrehozása
          </router-link>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
// import api from '@/services/api';

export default {
  name: 'EventsList',
  
  data() {
    return {
      events: [],
      loading: true,
      currentUser: null,
      filters: {
        type: '',
        status: '',
        sort: 'newest'
      }
    }
  },
  
  async created() {
    await this.loadCurrentUser();
    await this.loadEvents();
  },
  
  methods: {
    async loadCurrentUser() {
      const savedUser = localStorage.getItem('esemenyter_user');
      if (savedUser) {
        this.currentUser = JSON.parse(savedUser);
      }
    },
    
    async loadEvents() {
      try {
        this.loading = true;
        
        if (!this.currentUser) {
          this.events = [];
          return;
        }
        
        // API hívás az eseményekért
        this.events = await api.getEvents(this.currentUser.id, this.filters);
        
      } catch (error) {
        console.error('Hiba az események betöltésekor:', error);
        this.events = [];
      } finally {
        this.loading = false;
      }
    },
    
    formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString('hu-HU', {
        month: 'short',
        day: 'numeric'
      });
    }
  }
}
</script>

<style scoped>
.events-page {
    box-sizing: border-box;
    font-family: "Poppins", sans-serif;
    /* display: flex; */
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    width: 100vw;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.events-header {
  background: rgba(255, 255, 255, 0.9);
  padding: 15px 0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.header-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.back-btn {
  background: none;
  border: 2px solid #667eea;
  color: #667eea;
  padding: 8px 16px;
  border-radius: 50px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
  transition: all 0.3s;
}

.back-btn:hover {
  background: #667eea;
  color: white;
  transform: translateX(-5px);
}

.header-content h1 {
  margin: 0;
  color: #333;
  font-size: 24px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.header-content h1 i {
  color: #667eea;
}

.user-info {
  background: #f8f9fa;
  padding: 8px 16px;
  border-radius: 20px;
  font-weight: 500;
  color: #333;
}

/* Tartalom */
.events-content {
  padding: 30px 0;
}

/* Szűrők */
.filters-section {
  background: white;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

.filter-row {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex: 1;
  min-width: 200px;
}

.filter-group label {
  color: #333;
  font-weight: 500;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 6px;
}

.filter-group select {
  padding: 10px;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 14px;
  background: white;
  cursor: pointer;
  transition: border-color 0.3s;
}

.filter-group select:focus {
  outline: none;
  border-color: #667eea;
}

/* Esemény kártyák */
.events-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.event-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0,0,0,0.05);
  transition: all 0.3s;
}

.event-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.event-header {
  display: flex;
  justify-content: space-between;
  padding: 12px 20px;
  background: #f8f9fa;
  border-bottom: 1px solid #e9ecef;
}

.event-type {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
  display: inline-flex;
  align-items: center;
  gap: 5px;
}

.event-type.local {
  background: #e3f2fd;
  color: #1565c0;
}

.event-type.global {
  background: #f3e5f5;
  color: #7b1fa2;
}

.event-status {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
}

.event-status.open {
  background: #e8f5e9;
  color: #2e7d32;
}

.event-status.closed {
  background: #ffebee;
  color: #c62828;
}

.event-body {
  padding: 20px;
}

.event-body h3 {
  margin: 0 0 10px 0;
  color: #333;
  font-size: 18px;
}

.event-description {
  color: #666;
  margin-bottom: 15px;
  font-size: 14px;
  line-height: 1.5;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.event-meta {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #888;
  font-size: 13px;
}

.meta-item i {
  font-size: 16px;
}

.event-footer {
  padding: 15px 20px;
  background: #f8f9fa;
  border-top: 1px solid #e9ecef;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.btn {
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  text-decoration: none;
  transition: all 0.3s;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-success {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
}

.event-stats {
  display: flex;
  gap: 15px;
}

.stat {
  display: flex;
  align-items: center;
  gap: 4px;
  color: #666;
  font-size: 14px;
}

.stat i {
  color: #667eea;
}

/* Betöltés */
.loading {
  text-align: center;
  padding: 40px;
  background: white;
  border-radius: 12px;
}

.loading i {
  font-size: 48px;
  color: #667eea;
  margin-bottom: 15px;
}

.loading p {
  color: #666;
}

/* Nincs esemény */
.no-events {
  text-align: center;
  padding: 60px 20px;
  background: white;
  border-radius: 12px;
}

.no-events i {
  font-size: 60px;
  color: #ccc;
  margin-bottom: 20px;
}

.no-events h3 {
  color: #666;
  margin-bottom: 10px;
}

.no-events p {
  color: #888;
}

/* Új esemény gomb */
.create-event-btn {
  text-align: center;
  margin-top: 30px;
}

.create-event-btn .btn {
  padding: 12px 24px;
  font-size: 16px;
}

/* Reszponzív */
@media (max-width: 768px) {
  .header-content {
    flex-direction: column;
    gap: 15px;
    text-align: center;
  }
  
  .filter-row {
    flex-direction: column;
    gap: 15px;
  }
  
  .filter-group {
    min-width: 100%;
  }
  
  .event-footer {
    flex-direction: column;
    gap: 15px;
    align-items: stretch;
  }
  
  .event-footer .btn {
    width: 100%;
    justify-content: center;
  }
  
  .event-stats {
    justify-content: center;
  }
}
</style>