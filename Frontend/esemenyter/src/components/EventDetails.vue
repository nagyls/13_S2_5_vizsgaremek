<template>
  <div class="esemeny-reszletek-oldal">
    <button class="vissza-gomb" @click="visszaAzEsemenyekhez">
      <i class='bx bx-arrow-back'></i> Vissza az eseményekhez
    </button>

    <div v-if="betoltesKozben" class="betoltas-container">
      <i class='bx bx-loader-circle bx-spin'></i>
      <p>Esemény betöltése...</p>
    </div>

    <div v-else-if="hibaUzenet" class="hiba-container">
      <div class="hiba-kartya">
        <i class='bx bx-error-circle'></i>
        <h3>Hiba történt</h3>
        <p>{{ hibaUzenet }}</p>
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

    <div v-else-if="esemenyAdatok" class="esemeny-tartalom">
      <!-- fejlec -->
      <div class="esemeny-fejlec">
        <div class="esemeny-cim">
          <h1>{{ esemenyAdatok.title }}</h1>
          <div class="esemeny-meta">
            <!-- tipus -->
            <span class="meta-cimke" :class="esemenyAdatok.type">
              <i class='bx bx-building' v-if="esemenyAdatok.type === 'local'"></i>
              <i class='bx bx-world' v-else></i>
              {{ esemenyAdatok.type === 'local' ? 'Helyi' : 'Globális' }}
            </span>
            <!-- allapot -->
            <span class="meta-cimke" :class="esemenyAdatok.status">
              <i class='bx bx-calendar-event' v-if="esemenyAdatok.status === 'open'"></i>
              <i class='bx bx-calendar-x' v-else></i>
              {{ esemenyAdatok.status === 'open' ? 'Aktív' : 'Lezárva' }}
            </span>
          </div>
        </div>
        
        <div class="esemeny-muveletek">
          <button v-if="aktualisFelhasznalo" class="muvelet-gomb" @click="kedvencBeallitas">
            <i class='bx bx-star' :class="{ 'kedvenc': esemenyAdatok.isFavorite }"></i>
          </button>
          <button class="muvelet-gomb" @click="megosztas">
            <i class='bx bx-share-alt'></i>
          </button>
        </div>
      </div>

      <!-- alap infok -->
      <div class="info-kartya">
        <div class="info-grid">
          <div class="info-elem">
            <i class='bx bx-user'></i>
            <div>
              <small>Szervező</small>
              <strong>{{ esemenyAdatok.creator_name || 'Ismeretlen' }}</strong>
            </div>
          </div>
          
          <div class="info-elem">
            <i class='bx bx-calendar'></i>
            <div>
              <small>Kezdés</small>
              <strong>{{ formatDatum(esemenyAdatok.start_date) }}</strong>
            </div>
          </div>
          
          <div class="info-elem">
            <i class='bx bx-calendar-check'></i>
            <div>
              <small>Befejezés</small>
              <strong>{{ formatDatum(esemenyAdatok.end_date) }}</strong>
            </div>
          </div>
          
          <div class="info-elem">
            <i class='bx bx-time'></i>
            <div>
              <small>Létrehozva</small>
              <strong>{{ formatDatum(esemenyAdatok.created_at) }}</strong>
            </div>
          </div>
        </div>
      </div>

      <!-- SZÖVEGES TARTALOM -->
      <div class="tartalom-kartya">
        <div class="tartalom-blokk">
          <h3><i class='bx bx-detail'></i> Leírás</h3>
          <p class="leiras">{{ esemenyAdatok.description }}</p>
        </div>
        
        <!-- RÉSZLETES TARTALOM -->
        <div v-if="esemenyAdatok.content" class="tartalom-blokk">
          <h3><i class='bx bx-file'></i> Részletek</h3>
          <div class="tartalom">{{ esemenyAdatok.content }}</div>
        </div>
      </div>

      <!-- KÉP MEGJELENÍTÉSE -->
      <div v-if="esemenyAdatok.image_url" class="kep-container">
        <img :src="esemenyAdatok.image_url" :alt="esemenyAdatok.title" class="esemeny-kep">
      </div>

      <!-- RÉSZTVÉTELI ŰRLAP -->
      <div v-if="esemenyAdatok.status === 'open' && aktualisFelhasznalo" class="resztvetel-kartya">
        <h3><i class='bx bx-check-circle'></i> Részvételi visszajelzés</h3>
        <p class="resztvetel-info">Jelentkezz, hogy részt veszel-e az eseményen:</p>
        <div class="resztvetel-gombok">
          <button @click="resztvetelKuldes('y')" 
                  class="btn resztvesz" 
                  :class="{ 'active': felhasznaloResztvetel === 'y' }">
            <i class='bx bx-check'></i> Részvétel
          </button>
          <button @click="resztvetelKuldes('n')" 
                  class="btn nem-resztvesz" 
                  :class="{ 'active': felhasznaloResztvetel === 'n' }">
            <i class='bx bx-x'></i> Nem veszek részt
          </button>
        </div>
      </div>

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

      <div class="komment-szekcio-kartya">
        <div class="szekcio-fejlec">
          <h3><i class='bx bx-message-square-detail'></i> Kommentek</h3>
          <span class="komment-szam">{{ kommentekSzama }}</span>
        </div>
        
        <CommentBox 
          :esemenyId="parseInt(esemenyId)"
          :currentUser="aktualisFelhasznalo"
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
      esemenyId: this.$route.params.id,        // URL-ből kapott esemény azonosító
      esemenyAdatok: null,                     // Esemény adatai
      aktualisFelhasznalo: null,               // Bejelentkezett felhasználó
      betoltesKozben: true,                     // Betöltés állapota
      hibaUzenet: '',                          // Hibák megjelenítéséhez
      kommentekSzama: 0,                       // Összes komment száma
      resztvevokSzama: 0,                      // Résztvevők száma
      nemResztvevokSzama: 0,                   // Nem résztvevők száma
      kedvencekSzama: 0,                       // Kedvencként jelölők száma
      felhasznaloResztvetel: null              // Felhasználó részvételi állapota
    }
  },
  
  created() {
    this.betoltEsemenyt()    
    this.betoltFelhasznalot()
  },
  
  methods: {
    visszaAzEsemenyekhez() {
      this.$router.push('/esemenyek')
    },

    async betoltEsemenyt() {
      try {
        this.betoltesKozben = true
        
        // 1.Ellenőrizzük, hogy létezik-e az esemény (LEKERDEZ)
        const esemenyMegtalalva = await this.lekerdezEsemenyt(this.esemenyId)
        
        if (!esemenyMegtalalva) {
          this.hibaUzenet = 'A kiválasztott esemény nem létezik!'
          this.betoltesKozben = false
          return
        }
        
        this.esemenyAdatok = esemenyMegtalalva
        
        // 2.Statisztikák betöltése
        await this.betoltStatisztikakat()
        
        // 3.Felhasználó részvételi állapota
        await this.betoltResztveteliAllapotot()
        
      } catch (hiba) {
        console.error('Hiba az esemény betöltésekor:', hiba)
        this.hibaUzenet = 'Nem sikerült betölteni az eseményt.'
      } finally {
        this.betoltesKozben = false
      }
    },
    
    /**
     Esemény lekérdezése adatbázisból vagy mock adatokból
     @param {number} esemenyId - Az esemény azonosítója
     @returns {Object|null} - Az esemény adatai vagy null
    */
    async lekerdezEsemenyt(esemenyId) {
      // DEMO: localStorage-ból betöltés
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
    
    async betoltFelhasznalot() {
      // DEMO: localStorage-ból felhasználó betöltése
      const mentettFelhasznalo = JSON.parse(localStorage.getItem('esemenyter_user') || 'null')
      
      if (mentettFelhasznalo && mentettFelhasznalo.isLoggedIn) {
        this.aktualisFelhasznalo = {
          id: mentettFelhasznalo.id || 1,
          username: mentettFelhasznalo.name || 'Felhasználó',
          name: mentettFelhasznalo.name || 'Felhasználó',
          email: mentettFelhasznalo.email || '',
          role: mentettFelhasznalo.role || 'student'
        }
      } else {
        // Ha nincs bejelentkezve, null érték
        this.aktualisFelhasznalo = null
      }
    },
    
    async betoltStatisztikakat() {
      try {
        // Kommente számának kiszámítása
        const kommentek = JSON.parse(localStorage.getItem('esemeny_kommentek') || '[]')
        this.kommentekSzama = kommentek.filter(k => k.event_id == this.esemenyId).length
        
        // DEMO: random statisztikák (valós esetben API hívás)
        this.resztvevokSzama = Math.floor(Math.random() * 50) + 10
        this.nemResztvevokSzama = Math.floor(Math.random() * 20) + 5
        this.kedvencekSzama = Math.floor(Math.random() * 15) + 3
        
      } catch (hiba) {
        console.error('Hiba a statisztikák betöltésekor:', hiba)
      }
    },
    
    /**
     * Felhasználó részvételi állapotának betöltése
     */
    async betoltResztveteliAllapotot() {
      if (!this.aktualisFelhasznalo) return
      
      // DEMO: localStorage-ból részvételi adatok
      const resztveteliAdatok = JSON.parse(localStorage.getItem('esemeny_resztvetel') || '[]')
      const felhasznaloResztvetelAdat = resztveteliAdatok.find(
        r => r.event_id == this.esemenyId && r.user_id == this.aktualisFelhasznalo.id
      )
      
      this.felhasznaloResztvetel = felhasznaloResztvetelAdat ? felhasznaloResztvetelAdat.valasz : null
    },
    
    /**
     * Részvételi válasz küldése
     * @param {string} valasz - 'y' (részvétel) vagy 'n' (nem részvétel)
    */
    async resztvetelKuldes(valasz) {
      if (!this.aktualisFelhasznalo) {
        alert('A részvételhez be kell jelentkezned!')
        this.$router.push('/login')
        return
      }
      
      try {
        // Részvételi adatok betöltése
        const resztveteliAdatok = JSON.parse(localStorage.getItem('esemeny_resztvetel') || '[]')
        
        // Régi adat törlése (ha van)
        const ujAdatok = resztveteliAdatok.filter(
          r => !(r.event_id == this.esemenyId && r.user_id == this.aktualisFelhasznalo.id)
        )
        
        // Új adat hozzáadása (BESZÚR művelet)
        ujAdatok.push({
          event_id: this.esemenyId,
          user_id: this.aktualisFelhasznalo.id,
          valasz: valasz,
          frissitve: new Date().toISOString()
        })
        
        // Adatok mentése
        localStorage.setItem('esemeny_resztvetel', JSON.stringify(ujAdatok))
        this.felhasznaloResztvetel = valasz
        
        // Statisztikák frissítése
        if (valasz === 'y') {
          this.resztvevokSzama++
          if (this.felhasznaloResztvetel === 'n') this.nemResztvevokSzama--
        } else {
          this.nemResztvevokSzama++
          if (this.felhasznaloResztvetel === 'y') this.resztvevokSzama--
        }
        
        // Sikeres üzenet
        alert(valasz === 'y' ? 'Részvétel rögzítve!' : 'Nem részvétel rögzítve!')
        
      } catch (hiba) {
        console.error('Hiba a részvétel küldésekor:', hiba)
        alert('Hiba történt a részvétel küldésekor')
      }
    },
    
    /**
     * Új komment hozzáadásakor meghívott metódus
     * @param {Object} ujKomment - Az új komment adatai
    */
    ujKommentHozzaadva(ujKomment) {
      this.kommentekSzama++
      
      this.mutatSikerUzenetet('Komment sikeresen hozzáadva!')
    },
    
    async kedvencBeallitas() {
      if (!this.aktualisFelhasznalo) {
        alert('A kedvencekhez adáshoz be kell jelentkezned!')
        return
      }
      
      try {
        // Kedvencek betöltése
        const kedvencek = JSON.parse(localStorage.getItem('esemeny_kedvencek') || '[]')
        const index = kedvencek.findIndex(
          k => k.event_id == this.esemenyId && k.user_id == this.aktualisFelhasznalo.id
        )
        
        if (index === -1) {
          // Hozzáadás kedvencekhez (BESZÚR)
          kedvencek.push({
            event_id: this.esemenyId,
            user_id: this.aktualisFelhasznalo.id,
            letrehozva: new Date().toISOString()
          })
          this.kedvencekSzama++
          this.mutatSikerUzenetet('Hozzáadva a kedvencekhez!')
        } else {
          // Törlés a kedvencekből (TÖRÖL)
          kedvencek.splice(index, 1)
          this.kedvencekSzama--
          this.mutatSikerUzenetet('Eltávolítva a kedvencekből!')
        }
        
        // Frissített adatok mentése
        localStorage.setItem('esemeny_kedvencek', JSON.stringify(kedvencek))
        
      } catch (hiba) {
        console.error('Hiba a kedvenc beállításakor:', hiba)
        alert('Hiba történt')
      }
    },
    
    megosztas() {
      // Ellenőrizzük, támogatja-e a böngésző a megosztási API-t
      if (navigator.share) {
        navigator.share({
          cim: this.esemenyAdatok.title,
          szoveg: this.esemenyAdatok.description,
          url: window.location.href
        })
      } else {
        // Ha nem támogatja, akkor vágólapra másolás
        navigator.clipboard.writeText(window.location.href)
        this.mutatSikerUzenetet('Link másolva a vágólapra!')
      }
    },
    
    /**
     * Dátum formázása magyar nyelven
     * @param {string} datumString - ISO dátum string
     * @returns {string} - Formázott dátum
     */
    formatDatum(datumString) {
      if (!datumString) return '-'
      const datum = new Date(datumString)
      return datum.toLocaleDateString('hu-HU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    },
    
    /**
     * Sikeres művelet üzenet megjelenítése
     * @param {string} uzenet - A megjelenítendő üzenet
     */
    mutatSikerUzenetet(uzenet) {
      alert('✅ ' + uzenet)
    }
  }
}
</script>

<style scoped>
.esemeny-reszletek-oldal {
  box-sizing: border-box;
  font-family: "Poppins", sans-serif;
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
  background: #f0f0f0;
  border-color: #ccc;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Betöltés konténer */
.betoltas-container {
  text-align: center;
  padding: 60px 20px;
}

.betoltas-container i {
  font-size: 48px;
  color: #4a6cf7;
  margin-bottom: 20px;
}

.betoltas-container p {
  color: #666;
  font-size: 18px;
}

/* Hiba konténer */
.hiba-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 60vh;
}

.hiba-kartya {
  background: white;
  border-radius: 12px;
  padding: 40px;
  text-align: center;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  max-width: 500px;
  width: 100%;
}

.hiba-kartya i {
  font-size: 64px;
  color: #ff4757;
  margin-bottom: 20px;
}

.hiba-kartya h3 {
  color: #333;
  margin-bottom: 10px;
}

.hiba-kartya p {
  color: #666;
  margin-bottom: 30px;
}

.hiba-gombok {
  display: flex;
  gap: 10px;
  justify-content: center;
}

.btn {
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s;
  border: none;
}

.btn-outline {
  background: white;
  border: 2px solid #e0e0e0;
  color: #333;
}

.btn-outline:hover {
  background: #f8f9fa;
  border-color: #ccc;
}

.btn-primary {
  background: #4a6cf7;
  color: white;
  border: 2px solid #4a6cf7;
}

.btn-primary:hover {
  background: #3a5ce5;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(74, 108, 247, 0.3);
}

/* Esemény fejléc */
.esemeny-fejlec {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  background: white;
  border-radius: 12px;
  padding: 30px;
  margin-bottom: 20px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.esemeny-cim h1 {
  color: #333;
  margin-bottom: 15px;
  font-size: 28px;
}

.esemeny-meta {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.meta-cimke {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.meta-cimke.local {
  background: #e3f2fd;
  color: #1976d2;
}

.meta-cimke.global {
  background: #f3e5f5;
  color: #7b1fa2;
}

.meta-cimke.open {
  background: #e8f5e9;
  color: #2e7d32;
}

.meta-cimke.closed {
  background: #ffebee;
  color: #c62828;
}

.esemeny-muveletek {
  display: flex;
  gap: 10px;
}

.muvelet-gomb {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #f8f9fa;
  border: 2px solid #e0e0e0;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s;
}

.muvelet-gomb:hover {
  background: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.muvelet-gomb i {
  font-size: 20px;
  color: #666;
}

.muvelet-gomb i.kedvenc {
  color: #ffd700;
}

/* Info kártya */
.info-kartya {
  background: white;
  border-radius: 12px;
  padding: 25px;
  margin-bottom: 20px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
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
}

.info-elem i {
  font-size: 24px;
  color: #4a6cf7;
  background: #f0f5ff;
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.info-elem div {
  display: flex;
  flex-direction: column;
}

.info-elem small {
  color: #666;
  font-size: 12px;
  margin-bottom: 4px;
}

.info-elem strong {
  color: #333;
  font-size: 16px;
}

/* Tartalom kártya */
.tartalom-kartya {
  background: white;
  border-radius: 12px;
  padding: 30px;
  margin-bottom: 20px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
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
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 20px;
}

.tartalom-blokk h3 i {
  color: #4a6cf7;
}

.leiras {
  color: #555;
  line-height: 1.6;
  font-size: 16px;
}

.tartalom {
  color: #555;
  line-height: 1.6;
  font-size: 15px;
}

/* Kép konténer */
.kep-container {
  margin-bottom: 20px;
}

.esemeny-kep {
  width: 100%;
  max-height: 500px;
  object-fit: cover;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

/* Részvétel kártya */
.resztvetel-kartya {
  background: white;
  border-radius: 12px;
  padding: 30px;
  margin-bottom: 20px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.resztvetel-info {
  color: #666;
  margin-bottom: 20px;
}

.resztvetel-gombok {
  display: flex;
  gap: 15px;
}

.resztvetel-gombok .btn {
  flex: 1;
  padding: 12px 24px;
  font-size: 16px;
}

.resztvetel-gombok .resztvesz {
  background: #e8f5e9;
  color: #2e7d32;
  border: 2px solid #c8e6c9;
}

.resztvetel-gombok .resztvesz:hover,
.resztvetel-gombok .resztvesz.active {
  background: #2e7d32;
  color: white;
  border-color: #2e7d32;
}

.resztvetel-gombok .nem-resztvesz {
  background: #ffebee;
  color: #c62828;
  border: 2px solid #ffcdd2;
}

.resztvetel-gombok .nem-resztvesz:hover,
.resztvetel-gombok .nem-resztvesz.active {
  background: #c62828;
  color: white;
  border-color: #c62828;
}

/* Statisztika kártya */
.statisztika-kartya {
  background: white;
  border-radius: 12px;
  padding: 30px;
  margin-bottom: 20px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.statisztika-kartya h3 {
  color: #333;
  margin-bottom: 25px;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 20px;
}

.statisztika-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 20px;
}

.stat {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 20px;
  text-align: center;
  transition: transform 0.3s;
}

.stat:hover {
  transform: translateY(-5px);
  background: white;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.stat i {
  font-size: 32px;
  color: #4a6cf7;
  margin-bottom: 10px;
}

.stat .szam {
  display: block;
  font-size: 28px;
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
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.szekcio-fejlec {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
}

.szekcio-fejlec h3 {
  color: #333;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 20px;
}

.komment-szam {
  background: #4a6cf7;
  color: white;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 600;
}

/* Responsive design */
@media (max-width: 768px) {
  .esemeny-reszletek-oldal {
    padding: 15px;
  }
  
  .esemeny-fejlec {
    flex-direction: column;
    gap: 20px;
  }
  
  .esemeny-muveletek {
    align-self: flex-start;
  }
  
  .info-grid {
    grid-template-columns: 1fr;
  }
  
  .resztvetel-gombok {
    flex-direction: column;
  }
  
  .statisztika-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .tartalom-kartya,
  .info-kartya,
  .statisztika-kartya,
  .komment-szekcio-kartya {
    padding: 20px;
  }
}

@media (max-width: 480px) {
  .esemeny-cim h1 {
    font-size: 24px;
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
    padding: 15px;
  }
  
  .btn {
    padding: 8px 16px;
    font-size: 14px;
  }
  
  .hiba-kartya {
    padding: 20px;
  }
  
  .hiba-kartya i {
    font-size: 48px;
  }
}
</style>