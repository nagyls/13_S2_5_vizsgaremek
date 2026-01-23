<template>
  <div class="komment-szekcio">
    <!-- Komment bevitel -->
    <div class="komment-bevitel" v-if="currentUser">
      <div class="felhasznalo-avatar">
        <i class='bx bx-user-circle'></i>
      </div>
      <div class="bevitel-mezo">
        <textarea 
          v-model="content"
          placeholder="Írj egy kommentet..."
          rows="3"
          maxlength="500"
          :disabled="betoltas"
          @keydown.enter.prevent="enterLeutes($event)"
        ></textarea>
        <div class="karakter-szamlalo">
          <span :class="{ 'tul-hosszu': content.length > 500 }">
            {{ content.length }}/500
          </span>
        </div>
        <div class="komment-gombok">
          <button 
            @click="kommentKuldes()"
            :disabled="!content.trim() || betoltas || content.length > 500"
            class="btn btn-primary"
          >
            <i class='bx bx-send' v-if="!betoltas"></i>
            <i class='bx bx-loader-circle bx-spin' v-else></i>
            {{ betoltas ? 'Küldés...' : 'Komment küldése' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Bejelentkezés prompt -->
    <div v-else class="bejelentkezes-info">
      <p>A kommenteléshez jelentkezz be!</p>
      <router-link to="/bejelentkezes" class="btn btn-outline-primary">
        <i class='bx bx-log-in'></i> Bejelentkezés
      </router-link>
    </div>

    <!-- Kommentek listája -->
    <div class="komment-lista" v-if="kommentek.length > 0">
      <div v-for="komment in kommentek" :key="komment.id" class="komment">
        <div class="komment-fejlec">
          <div class="felhasznalo-info">
            <div class="avatar">
              <i class='bx bx-user'></i>
            </div>
            <div class="felhasznalo-nev">
              <strong>{{ komment.username || 'Felhasználó' }}</strong>
              <span class="datum">{{ formatDate(komment.created_at) }}</span>
            </div>
          </div>
          <button 
            v-if="currentUser && currentUser.id === komment.user_id"
            @click="kommentTorles(komment.id)"
            class="torles-gomb"
            title="Komment törlése"
          >
            <i class='bx bx-trash'></i>
          </button>
        </div>
        <div class="komment-tartalom">
          {{ komment.content }}
        </div>
      </div>
    </div>

    <!-- Nincs komment -->
    <div v-else class="nincs-komment">
      <i class='bx bx-message-square-detail'></i>
      <p>Még nincsenek kommentek. Legyél te az első!</p>
    </div>

    <!-- Hibaüzenet -->
    <div v-if="hiba" class="hiba-uzenet">
      <i class='bx bx-error-circle'></i>
      <span>{{ hiba }}</span>
    </div>
  </div>
</template>

<script>
export default {
  name: 'KommentBox',
  
  props: {
    esemenyId: {
      type: Number,
      required: true
    },
    currentUser: {
      type: Object,
      default: null
    }
  },
  
  data() {
    return {
      kommentek: [],
      content: '',
      betoltas: false,
      hiba: '',
      siker: false
    }
  },
  
  mounted() {
    this.kommentekBetoltese()
  },
  
  watch: {
    esemenyId() {
      this.kommentekBetoltese()
    }
  },
  
  methods: {
    async kommentekBetoltese() {
      try {
        this.betoltas = true
        // Valós API hívás vagy mock adatok
        const response = await this.fetchKommentek(this.esemenyId)
        this.kommentek = response
        
      } catch (error) {
        console.error('Hiba a kommentek betöltésekor:', error)
        this.hiba = 'Nem sikerült betölteni a kommenteket.'
      } finally {
        this.betoltas = false
      }
    },
    
    async fetchKommentek(esemenyId) {
      // Demo: mock kommentek
      const mockKommentek = [
        {
          id: 1,
          event_id: esemenyId,
          user_id: 1,
          username: 'Admin User',
          content: 'Ez egy példa komment az eseményhez.',
          created_at: new Date().toISOString()
        },
        {
          id: 2,
          event_id: esemenyId,
          user_id: 2,
          username: 'Tanár Béla',
          content: 'Nagyon jó esemény lesz, mindenki jöjjön!',
          created_at: new Date(Date.now() - 3600000).toISOString()
        }
      ]
      return mockKommentek
    },
    
    enterLeutes(event) {
      if (event.shiftKey) {
        return // Shift+Enter esetén sortörés
      }
      this.kommentKuldes()
    },
    
    async kommentKuldes() {
      // ALGORITMUS megvalósítása
      const tartalom = this.content.trim()
      
      // 1. Validáció: nem lehet üres
      if (tartalom === '') {
        this.hiba = 'A komment nem lehet üres!'
        return
      }
      
      // 2. Validáció: max 500 karakter
      if (tartalom.length > 500) {
        this.hiba = 'A komment túl hosszú! (max. 500 karakter)'
        return
      }
      
      try {
        this.betoltas = true
        this.hiba = ''
        
        // 3. Ellenőrizzük, hogy létezik-e az esemény (LEKERDEZ)
        const eventTalalat = await this.ellenorizEsemeny(this.esemenyId)
        
        if (!eventTalalat) {
          this.hiba = 'A kiválasztott esemény nem létezik!'
          this.betoltas = false
          return
        }
        
        // 4. Új komment objektum létrehozása
        const ujKomment = {
          eventId: this.esemenyId,
          userId: this.currentUser.id,
          content: tartalom
        }
        
        // 5. Komment beszúrása az adatbázisba (BESZÚR)
        const mentettKomment = await this.beszurKomment(ujKomment)
        
        if (mentettKomment) {
          // Hozzáadjuk a kommentek listájához
          this.kommentek.unshift({
            ...mentettKomment,
            username: this.currentUser.username || this.currentUser.name
          })
          
          // Tisztítjuk az input mezőt
          this.content = ''
          
          // Sikeres üzenet
          this.siker = true
          this.$emit('komment-sikeres', mentettKomment)
          
          // Visszajelzés a felhasználónak
          setTimeout(() => {
            this.siker = false
          }, 2000)
        }
        
      } catch (error) {
        console.error('Hiba a komment mentésekor:', error)
        this.hiba = 'Hiba történt a komment küldésekor. Próbáld újra!'
      } finally {
        this.betoltas = false
      }
    },
    
    async ellenorizEsemeny(esemenyId) {
      // Mock: mindig igazzal tér vissza, de valós esetben API hívás
      return true
      
      // Valós implementáció:
      // try {
      //   const response = await this.$http.get(`/esemenyek/${esemenyId}`)
      //   return response.data && response.data.length > 0
      // } catch (error) {
      //   console.error('Hiba az esemény ellenőrzésekor:', error)
      //   return false
      // }
    },
    
    async beszurKomment(komment) {
      // Mock beszúrás
      const ujKomment = {
        id: Date.now(),
        event_id: komment.eventId,
        user_id: komment.userId,
        content: komment.content,
        created_at: new Date().toISOString()
      }
      
      // Mock: mentés localStorage-ba
      const kommentek = JSON.parse(localStorage.getItem('esemeny_kommentek') || '[]')
      kommentek.push(ujKomment)
      localStorage.setItem('esemeny_kommentek', JSON.stringify(kommentek))
      
      return ujKomment
      
      // Valós implementáció:
      // try {
      //   const response = await this.$http.post('/kommentek', {
      //     event_id: komment.eventId,
      //     user_id: komment.userId,
      //     content: komment.content
      //   })
      //   return response.data
      // } catch (error) {
      //   console.error('Hiba a komment beszúrásakor:', error)
      //   throw error
      // }
    },
    
    async kommentTorles(kommentId) {
      if (!confirm('Biztosan törölni szeretnéd ezt a kommentet?')) {
        return
      }
      
      try {
        // Mock törlés
        this.kommentek = this.kommentek.filter(k => k.id !== kommentId)
        
        // Valós implementáció:
        // await this.$http.delete(`/kommentek/${kommentId}`)
        
        // Frissítjük a localStorage-t is
        const kommentek = JSON.parse(localStorage.getItem('esemeny_kommentek') || '[]')
        const frissitettKommentek = kommentek.filter(k => k.id !== kommentId)
        localStorage.setItem('esemeny_kommentek', JSON.stringify(frissitettKommentek))
        
      } catch (error) {
        console.error('Hiba a komment törlésekor:', error)
        alert('Nem sikerült törölni a kommentet.')
      }
    },
    
    formatDate(dateString) {
      const date = new Date(dateString)
      const now = new Date()
      const diff = now - date
      
      // Percben
      const minutes = Math.floor(diff / 60000)
      if (minutes < 60) {
        return `${minutes} perce`
      }
      
      // Órában
      const hours = Math.floor(minutes / 60)
      if (hours < 24) {
        return `${hours} órája`
      }
      
      // Dátum formázás
      return date.toLocaleDateString('hu-HU', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }
  }
}
</script>

<style scoped>
.komment-szekcio {
  margin-top: 30px;
  padding-top: 20px;
  border-top: 1px solid #e0e0e0;
}

.komment-bevitel {
  display: flex;
  gap: 15px;
  margin-bottom: 30px;
}

.felhasznalo-avatar {
  flex-shrink: 0;
}

.felhasznalo-avatar i {
  font-size: 40px;
  color: #667eea;
}

.bevitel-mezo {
  flex: 1;
}

.bevitel-mezo textarea {
  width: 100%;
  padding: 12px;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  resize: vertical;
  font-family: inherit;
  font-size: 14px;
  transition: border-color 0.3s;
}

.bevitel-mezo textarea:focus {
  outline: none;
  border-color: #667eea;
}

.bevitel-mezo textarea:disabled {
  background-color: #f5f5f5;
  cursor: not-allowed;
}

.karakter-szamlalo {
  text-align: right;
  margin-top: 5px;
  font-size: 12px;
  color: #666;
}

.tul-hosszu {
  color: #f44336;
  font-weight: bold;
}

.komment-gombok {
  margin-top: 10px;
  display: flex;
  justify-content: flex-end;
}

.btn {
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-weight: 500;
  transition: all 0.3s;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none !important;
}

.btn-outline-primary {
  background: transparent;
  border: 2px solid #667eea;
  color: #667eea;
}

.btn-outline-primary:hover {
  background: #667eea;
  color: white;
}

.bejelentkezes-info {
  background: #f8f9fa;
  padding: 15px;
  border-radius: 8px;
  text-align: center;
  margin-bottom: 20px;
  border: 1px dashed #dee2e6;
}

.bejelentkezes-info p {
  margin-bottom: 10px;
  color: #666;
}

.komment-lista {
  margin-top: 20px;
}

.komment {
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 15px;
  margin-bottom: 15px;
  transition: box-shadow 0.3s;
}

.komment:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.komment-fejlec {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 10px;
}

.felhasznalo-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.felhasznalo-nev {
  display: flex;
  flex-direction: column;
}

.felhasznalo-nev strong {
  font-size: 14px;
  color: #333;
}

.datum {
  font-size: 12px;
  color: #888;
}

.torles-gomb {
  background: none;
  border: none;
  color: #f44336;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  transition: background-color 0.3s;
}

.torles-gomb:hover {
  background-color: rgba(244, 67, 54, 0.1);
}

.komment-tartalom {
  font-size: 14px;
  line-height: 1.5;
  color: #333;
  white-space: pre-wrap;
  word-break: break-word;
}

.nincs-komment {
  text-align: center;
  padding: 40px 20px;
  color: #888;
}

.nincs-komment i {
  font-size: 48px;
  margin-bottom: 15px;
  display: block;
  color: #ccc;
}

.hiba-uzenet {
  background: #ffebee;
  color: #c62828;
  padding: 12px;
  border-radius: 6px;
  margin-top: 15px;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 14px;
}

.hiba-uzenet i {
  font-size: 18px;
}

@media (max-width: 768px) {
  .komment-bevitel {
    flex-direction: column;
  }
  
  .felhasznalo-avatar {
    align-self: flex-start;
  }
  
  .bevitel-mezo textarea {
    min-height: 80px;
  }
  
  .komment {
    padding: 12px;
  }
}
</style>