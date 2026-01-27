<template>
  <div class="esemeny-reszletek-oldal">
    <!-- VISSZA GOMB -->
    <button class="vissza-gomb" @click="goBackToEvents">
      <i class='bx bx-arrow-back'></i> Vissza az eseményekhez
    </button>

    <!-- BETÖLTÉS -->
    <div v-if="betoltas" class="betoltas-container">
      <i class='bx bx-loader-circle bx-spin'></i>
      <p>Esemény betöltése...</p>
    </div>

    <!-- HIBÁK -->
    <div v-else-if="hiba" class="hiba-container">
      <div class="hiba-kartya">
        <i class='bx bx-error-circle'></i>
        <h3>Hiba történt</h3>
        <p>{{ hiba }}</p>
        <div class="hiba-gombok">
          <button @click="$router.back()" class="btn btn-outline">
            <i class='bx bx-arrow-back'></i> Vissza
          </button>
          <button @click="$router.push('/esemenyek')" class="btn btn-primary">
            <i class='bx bx-list-ul'></i> Összes esemény
          </button>
        </div>
      </div>
    </div>

    <!-- ESEMÉNY TARTALMA -->
    <div v-else-if="esemeny" class="esemeny-tartalom">
      <!-- FEJLÉC -->
      <div class="esemeny-fejlec">
        <div class="esemeny-cim">
          <h1>{{ esemeny.title }}</h1>
          <div class="esemeny-meta">
            <span class="meta-cimke" :class="esemeny.type">
              <i class='bx bx-building' v-if="esemeny.type === 'local'"></i>
              <i class='bx bx-world' v-else></i>
              {{ esemeny.type === 'local' ? 'Helyi' : 'Globális' }}
            </span>
            <span class="meta-cimke" :class="esemeny.status">
              <i class='bx bx-calendar-event' v-if="esemeny.status === 'open'"></i>
              <i class='bx bx-calendar-x' v-else></i>
              {{ esemeny.status === 'open' ? 'Aktív' : 'Lezárva' }}
            </span>
          </div>
        </div>
        
        <div class="esemeny-muveletek">
          <button v-if="currentUser" class="muvelet-gomb" @click="kedvencBeallitas">
            <i class='bx bx-star' :class="{ 'kedvenc': esemeny.isFavorite }"></i>
          </button>
          <button class="muvelet-gomb" @click="megosztas">
            <i class='bx bx-share-alt'></i>
          </button>
        </div>
      </div>

      <!-- INFORMÁCIÓK -->
      <div class="info-kartya">
        <div class="info-grid">
          <div class="info-elem">
            <i class='bx bx-user'></i>
            <div>
              <small>Szervező</small>
              <strong>{{ esemeny.creator_name || 'Ismeretlen' }}</strong>
            </div>
          </div>
          
          <div class="info-elem">
            <i class='bx bx-calendar'></i>
            <div>
              <small>Kezdés</small>
              <strong>{{ formatDate(esemeny.start_date) }}</strong>
            </div>
          </div>
          
          <div class="info-elem">
            <i class='bx bx-calendar-check'></i>
            <div>
              <small>Befejezés</small>
              <strong>{{ formatDate(esemeny.end_date) }}</strong>
            </div>
          </div>
          
          <div class="info-elem">
            <i class='bx bx-time'></i>
            <div>
              <small>Létrehozva</small>
              <strong>{{ formatDate(esemeny.created_at) }}</strong>
            </div>
          </div>
        </div>
      </div>

      <!-- TARTALOM -->
      <div class="tartalom-kartya">
        <div class="tartalom-blokk">
          <h3><i class='bx bx-detail'></i> Leírás</h3>
          <p class="leiras">{{ esemeny.description }}</p>
        </div>
        
        <div v-if="esemeny.content" class="tartalom-blokk">
          <h3><i class='bx bx-file'></i> Részletek</h3>
          <div class="tartalom">{{ esemeny.content }}</div>
        </div>
      </div>

      <!-- KÉP (ha van) -->
      <div v-if="esemeny.image_url" class="kep-container">
        <img :src="esemeny.image_url" :alt="esemeny.title" class="esemeny-kep">
      </div>

      <!-- RÉSZTVÉTEL -->
      <div v-if="esemeny.status === 'open' && currentUser" class="resztvetel-kartya">
        <h3><i class='bx bx-check-circle'></i> Részvételi visszajelzés</h3>
        <p class="resztvetel-info">Jelentkezz, hogy részt veszel-e az eseményen:</p>
        <div class="resztvetel-gombok">
          <button @click="resztvetelKuldes('y')" 
                  class="btn resztvesz" 
                  :class="{ 'active': userResztvetel === 'y' }">
            <i class='bx bx-check'></i> Részvétel
          </button>
          <button @click="resztvetelKuldes('n')" 
                  class="btn nem-resztvesz" 
                  :class="{ 'active': userResztvetel === 'n' }">
            <i class='bx bx-x'></i> Nem veszek részt
          </button>
        </div>
      </div>

      <!-- STATISZTIKA -->
      <div class="statisztika-kartya">
        <h3><i class='bx bx-stats'></i> Statisztika</h3>
        <div class="statisztika-grid">
          <div class="stat">
            <i class='bx bx-message-square-detail'></i>
            <span class="szam">{{ kommentekSzama }}</span>
            <small>Komment</small>
          </div>
          <div class="stat">
            <i class='bx bx-user-check'></i>
            <span class="szam">{{ resztvevokSzama }}</span>
            <small>Részvétel</small>
          </div>
          <div class="stat">
            <i class='bx bx-user-x'></i>
            <span class="szam">{{ nemResztvevokSzama }}</span>
            <small>Nem vesz részt</small>
          </div>
          <div class="stat">
            <i class='bx bx-star'></i>
            <span class="szam">{{ kedvencekSzama }}</span>
            <small>Kedvenc</small>
          </div>
        </div>
      </div>

      <!-- KOMMENT SZEKCIÓ -->
      <div class="komment-szekcio-kartya">
        <div class="szekcio-fejlec">
          <h3><i class='bx bx-message-square-detail'></i> Kommentek</h3>
          <span class="komment-szam">{{ kommentekSzama }}</span>
        </div>
        
        <!-- COMMENTBOX KOMPONENS -->
        <CommentBox 
          :esemenyId="parseInt(esemenyId)"
          :currentUser="currentUser"
          @komment-sikeres="ujKommentHozzaadva"
        />
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
      esemenyId: this.$route.params.id,
      esemeny: null,
      currentUser: null,
      betoltas: true,
      hiba: '',
      kommentekSzama: 0,
      resztvevokSzama: 0,
      nemResztvevokSzama: 0,
      kedvencekSzama: 0,
      userResztvetel: null
    }
  },
  
  created() {
    this.betoltasEsemenyt()
    this.betoltasFelhasznalot()
  },
  
  methods: {
    goBackToEvents() {
      this.$router.push('/esemenyek')
    },

    async betoltasEsemenyt() {
      try {
        this.betoltas = true
        
        // 1. ELLENŐRZÉS: Létezik-e az esemény? (LEKERDEZ)
        const esemenyTalalat = await this.lekerdezEsemenyt(this.esemenyId)
        
        if (!esemenyTalalat) {
          this.hiba = 'A kiválasztott esemény nem létezik!'
          this.betoltas = false
          return
        }
        
        this.esemeny = esemenyTalalat
        
        // 2. Statisztikák betöltése
        await this.betoltasStatisztikakat()
        
        // 3. Felhasználó részvételi állapota
        await this.betoltasResztveteliAllapot()
        
      } catch (error) {
        console.error('Hiba az esemény betöltésekor:', error)
        this.hiba = 'Nem sikerült betölteni az eseményt.'
      } finally {
        this.betoltas = false
      }
    },
    
    async lekerdezEsemenyt(esemenyId) {
      // Mock: betöltés localStorage-ból
      const osszesEsemeny = JSON.parse(localStorage.getItem('esemenyek') || '[]')
      let esemeny = osszesEsemeny.find(e => e.id == esemenyId)
      
      // Ha nincs a localStorage-ban, demo adatokat hozunk létre
      if (!esemeny) {
        esemeny = {
          id: esemenyId,
          title: 'Tavaszi kirándulás',
          description: 'Éves tavaszi kirándulás a természetben',
          content: 'Hozz magaddal kenyeret és vizet!',
          type: 'local',
          status: 'open',
          creator_name: 'Admin User',
          start_date: new Date().toISOString(),
          end_date: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString(),
          created_at: new Date().toISOString()
        }
      }
      
      return esemeny
    },
    
    async betoltasFelhasznalot() {
      // Mock: bejelentkezett felhasználó
      const mentettFelhasznalo = JSON.parse(localStorage.getItem('esemenyter_user') || 'null')
      
      if (mentettFelhasznalo && mentettFelhasznalo.isLoggedIn) {
        this.currentUser = {
          id: mentettFelhasznalo.id || 1,
          username: mentettFelhasznalo.name || 'Felhasználó',
          name: mentettFelhasznalo.name || 'Felhasználó',
          email: mentettFelhasznalo.email || '',
          role: mentettFelhasznalo.role || 'student'
        }
      } else {
        // Ha nincs bejelentkezve, alap felhasználó
        this.currentUser = null
      }
    },
    
    async betoltasStatisztikakat() {
      try {
        // Mock statisztikák
        const kommentek = JSON.parse(localStorage.getItem('esemeny_kommentek') || '[]')
        this.kommentekSzama = kommentek.filter(k => k.event_id == this.esemenyId).length
        
        // Mock részvétel
        this.resztvevokSzama = Math.floor(Math.random() * 50) + 10
        this.nemResztvevokSzama = Math.floor(Math.random() * 20) + 5
        this.kedvencekSzama = Math.floor(Math.random() * 15) + 3
        
      } catch (error) {
        console.error('Hiba a statisztikák betöltésekor:', error)
      }
    },
    
    async betoltasResztveteliAllapot() {
      if (!this.currentUser) return
      
      // Mock: részvétel adatok
      const resztveteliAdatok = JSON.parse(localStorage.getItem('esemeny_resztvetel') || '[]')
      const userResztvetel = resztveteliAdatok.find(
        r => r.event_id == this.esemenyId && r.user_id == this.currentUser.id
      )
      
      this.userResztvetel = userResztvetel ? userResztvetel.answer : null
    },
    
    async resztvetelKuldes(valasz) {
      if (!this.currentUser) {
        alert('A részvételhez be kell jelentkezned!')
        this.$router.push('/login')
        return
      }
      
      try {
        // Részvétel mentése
        const resztveteliAdatok = JSON.parse(localStorage.getItem('esemeny_resztvetel') || '[]')
        
        // Régi adat törlése
        const ujAdatok = resztveteliAdatok.filter(
          r => !(r.event_id == this.esemenyId && r.user_id == this.currentUser.id)
        )
        
        // Új adat hozzáadása
        ujAdatok.push({
          event_id: this.esemenyId,
          user_id: this.currentUser.id,
          answer: valasz,
          updated_at: new Date().toISOString()
        })
        
        localStorage.setItem('esemeny_resztvetel', JSON.stringify(ujAdatok))
        this.userResztvetel = valasz
        
        // Statisztika frissítése
        if (valasz === 'y') {
          this.resztvevokSzama++
          if (this.userResztvetel === 'n') this.nemResztvevokSzama--
        } else {
          this.nemResztvevokSzama++
          if (this.userResztvetel === 'y') this.resztvevokSzama--
        }
        
        // Értesítés
        alert(valasz === 'y' ? 'Részvétel rögzítve!' : 'Nem részvétel rögzítve!')
        
      } catch (error) {
        console.error('Hiba a részvétel küldésekor:', error)
        alert('Hiba történt a részvétel küldésekor')
      }
    },
    
    ujKommentHozzaadva(ujKomment) {
      // Kommentszám növelése
      this.kommentekSzama++
      
      // Értesítés
      this.mutatSikerUzenetet('Komment sikeresen hozzáadva!')
    },
    
    async kedvencBeallitas() {
      if (!this.currentUser) {
        alert('A kedvencekhez adáshoz be kell jelentkezned!')
        return
      }
      
      try {
        const kedvencek = JSON.parse(localStorage.getItem('esemeny_kedvencek') || '[]')
        const index = kedvencek.findIndex(
          k => k.event_id == this.esemenyId && k.user_id == this.currentUser.id
        )
        
        if (index === -1) {
          // Hozzáadás
          kedvencek.push({
            event_id: this.esemenyId,
            user_id: this.currentUser.id,
            created_at: new Date().toISOString()
          })
          this.kedvencekSzama++
          this.mutatSikerUzenetet('Hozzáadva a kedvencekhez!')
        } else {
          // Törlés
          kedvencek.splice(index, 1)
          this.kedvencekSzama--
          this.mutatSikerUzenetet('Eltávolítva a kedvencekből!')
        }
        
        localStorage.setItem('esemeny_kedvencek', JSON.stringify(kedvencek))
        
      } catch (error) {
        console.error('Hiba a kedvenc beállításakor:', error)
        alert('Hiba történt')
      }
    },
    
    megosztas() {
      if (navigator.share) {
        navigator.share({
          title: this.esemeny.title,
          text: this.esemeny.description,
          url: window.location.href
        })
      } else {
        // Vágólapra másolás
        navigator.clipboard.writeText(window.location.href)
        this.mutatSikerUzenetet('Link másolva a vágólapra!')
      }
    },
    
    formatDate(dateString) {
      if (!dateString) return '-'
      const date = new Date(dateString)
      return date.toLocaleDateString('hu-HU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    },
    
    mutatSikerUzenetet(uzenet) {
      // Egyszerű alert, de lehetne szebb értesítő komponens is
      alert('✅ ' + uzenet)
    }
  }
}
</script>

<style scoped>
.esemeny-reszletek-oldal {
  box-sizing: border-box;
  font-family: "Poppins", sans-serif;
  padding: 20px 0;
  /* display: flex; */
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  width: 100vw;
  background: #f8f9fa;
}

.vissza-gomb {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: white;
  border: 2px solid #e0e0e0;
  border-radius: 50px;
  padding: 10px 20px;
  cursor: pointer;
  font-weight: 600;
  color: #333;
  margin-bottom: 30px;
  transition: all 0.3s;
}

.vissza-gomb:hover {
  background: #f5f5f5;
  transform: translateX(-5px);
}

.vissza-gomb i {
  font-size: 20px;
}

/* Betöltés */
.betoltas-container {
  text-align: center;
  padding: 60px 20px;
}

.betoltas-container i {
  font-size: 48px;
  color: #667eea;
  margin-bottom: 20px;
}

.betoltas-container p {
  color: #666;
  font-size: 18px;
}

/* Hiba */
.hiba-container {
  max-width: 600px;
  margin: 0 auto;
  padding: 40px 0;
}

.hiba-kartya {
  background: white;
  border-radius: 12px;
  padding: 40px;
  text-align: center;
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
  border-left: 5px solid #f44336;
}

.hiba-kartya i {
  font-size: 60px;
  color: #f44336;
  margin-bottom: 20px;
}

.hiba-kartya h3 {
  color: #333;
  margin-bottom: 10px;
}

.hiba-kartya p {
  color: #666;
  margin-bottom: 30px;
  font-size: 16px;
}

.hiba-gombok {
  display: flex;
  gap: 15px;
  justify-content: center;
}

/* Esemény tartalom */
.esemeny-tartalom {
  animation: slideUp 0.5s ease;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Fejléc */
.esemeny-fejlec {
  background: white;
  border-radius: 12px;
  padding: 30px;
  margin-bottom: 20px;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

.esemeny-cim h1 {
  margin: 0 0 15px 0;
  color: #333;
  font-size: 32px;
  line-height: 1.2;
}

.esemeny-meta {
  display: flex;
  gap: 10px;
}

.meta-cimke {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 500;
  display: inline-flex;
  align-items: center;
  gap: 5px;
}

.meta-cimke.local {
  background: #e3f2fd;
  color: #1565c0;
  border: 1px solid #bbdefb;
}

.meta-cimke.global {
  background: #f3e5f5;
  color: #7b1fa2;
  border: 1px solid #e1bee7;
}

.meta-cimke.open {
  background: #e8f5e9;
  color: #2e7d32;
  border: 1px solid #c8e6c9;
}

.meta-cimke.closed {
  background: #ffebee;
  color: #c62828;
  border: 1px solid #ffcdd2;
}

.esemeny-muveletek {
  display: flex;
  gap: 10px;
}

.muvelet-gomb {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: 2px solid #e0e0e0;
  background: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s;
}

.muvelet-gomb:hover {
  border-color: #667eea;
  background: #f8f9ff;
}

.muvelet-gomb i {
  font-size: 20px;
  color: #666;
}

.muvelet-gomb:hover i {
  color: #667eea;
}

.kedvenc {
  color: #ff9800 !important;
}

/* Info kártya */
.info-kartya {
  background: white;
  border-radius: 12px;
  padding: 25px;
  margin-bottom: 20px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
}

.info-elem {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 15px;
  background: #f8f9fa;
  border-radius: 8px;
  border: 1px solid #e9ecef;
}

.info-elem i {
  font-size: 24px;
  color: #667eea;
  width: 40px;
  height: 40px;
  background: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.info-elem div {
  flex: 1;
}

.info-elem small {
  display: block;
  color: #666;
  font-size: 12px;
  margin-bottom: 4px;
}

.info-elem strong {
  display: block;
  color: #333;
  font-size: 16px;
}

/* Tartalom kártya */
.tartalom-kartya {
  background: white;
  border-radius: 12px;
  padding: 30px;
  margin-bottom: 20px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

.tartalom-blokk {
  margin-bottom: 30px;
}

.tartalom-blokk:last-child {
  margin-bottom: 0;
}

.tartalom-blokk h3 {
  color: #333;
  margin-bottom: 15px;
  font-size: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.tartalom-blokk h3 i {
  color: #667eea;
}

.leiras {
  color: #444;
  line-height: 1.6;
  font-size: 16px;
  white-space: pre-wrap;
}

.tartalom {
  color: #555;
  line-height: 1.6;
  font-size: 15px;
  white-space: pre-wrap;
  padding: 15px;
  background: #f8f9fa;
  border-radius: 8px;
  border-left: 4px solid #667eea;
}

/* Kép */
.kep-container {
  margin: 20px 0;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.esemeny-kep {
  width: 100%;
  height: auto;
  display: block;
  transition: transform 0.3s;
}

.esemeny-kep:hover {
  transform: scale(1.02);
}

/* Részvétel kártya */
.resztvetel-kartya {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 12px;
  padding: 30px;
  margin: 20px 0;
  color: white;
}

.resztvetel-kartya h3 {
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.resztvetel-info {
  opacity: 0.9;
  margin-bottom: 25px;
}

.resztvetel-gombok {
  display: flex;
  gap: 15px;
}

.resztvetel-gombok .btn {
  flex: 1;
  padding: 15px 25px;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  transition: all 0.3s;
}

.resztvetel-gombok .resztvesz {
  background: white;
  color: #4CAF50;
}

.resztvetel-gombok .resztvesz:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 20px rgba(76, 175, 80, 0.3);
}

.resztvetel-gombok .resztvesz.active {
  background: #4CAF50;
  color: white;
}

.resztvetel-gombok .nem-resztvesz {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  backdrop-filter: blur(10px);
}

.resztvetel-gombok .nem-resztvesz:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-3px);
}

.resztvetel-gombok .nem-resztvesz.active {
  background: #f44336;
  color: white;
}

/* Statisztika kártya */
.statisztika-kartya {
  background: white;
  border-radius: 12px;
  padding: 30px;
  margin: 20px 0;
  box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

.statisztika-kartya h3 {
  color: #333;
  margin-bottom: 25px;
  font-size: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.statisztika-kartya h3 i {
  color: #667eea;
}

.statisztika-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 20px;
}

.stat {
  text-align: center;
  padding: 20px;
  background: #f8f9fa;
  border-radius: 8px;
  border: 1px solid #e9ecef;
  transition: all 0.3s;
}

.stat:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0,0,0,0.1);
  border-color: #667eea;
}

.stat i {
  font-size: 32px;
  color: #667eea;
  margin-bottom: 10px;
}

.szam {
  display: block;
  font-size: 32px;
  font-weight: 700;
  color: #333;
  margin-bottom: 5px;
}

.stat small {
  color: #666;
  font-size: 14px;
}

/* Komment szekció */
.komment-szekcio-kartya {
  background: white;
  border-radius: 12px;
  padding: 30px;
  margin: 20px 0;
  box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

.szekcio-fejlec {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
  padding-bottom: 15px;
  border-bottom: 2px solid #f0f0f0;
}

.szekcio-fejlec h3 {
  color: #333;
  font-size: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 0;
}

.szekcio-fejlec h3 i {
  color: #667eea;
}

.komment-szam {
  background: #667eea;
  color: white;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 14px;
}

/* Gombok */
.btn {
  padding: 12px 24px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
  font-family: inherit;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
}

.btn-outline {
  background: white;
  border: 2px solid #667eea;
  color: #667eea;
}

.btn-outline:hover {
  background: #667eea;
  color: white;
}

/* Responsive */
@media (max-width: 768px) {
  .esemeny-reszletek-oldal {
    padding: 15px;
  }
  
  .esemeny-fejlec {
    flex-direction: column;
    gap: 20px;
  }
  
  .esemeny-cim h1 {
    font-size: 24px;
  }
  
  .info-grid {
    grid-template-columns: 1fr;
  }
  
  .resztvetel-gombok {
    flex-direction: column;
  }
  
  .statisztika-grid {
    grid-template-columns: 1fr 1fr;
  }
  
  .hiba-gombok {
    flex-direction: column;
  }
  
  .hiba-gombok .btn {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .esemeny-cim h1 {
    font-size: 20px;
  }
  
  .meta-cimke {
    font-size: 12px;
    padding: 4px 8px;
  }
  
  .statisztika-grid {
    grid-template-columns: 1fr;
  }
  
  .tartalom-kartya,
  .info-kartya,
  .statisztika-kartya,
  .komment-szekcio-kartya {
    padding: 20px;
  }
}
</style>