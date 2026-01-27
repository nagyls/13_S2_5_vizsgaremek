<template>
  <div class="komment-szekcio">
    <div class="komment-bevitel" v-if="aktualisFelhasznalo">
      <div class="felhasznalo-avatar">
        <i class='bx bx-user-circle'></i>
      </div>
      <div class="bevitel-mezo">
        <textarea 
          v-model="kommentSzoveg"
          placeholder="Írj egy kommentet..."
          rows="3"
          maxlength="500"
          :disabled="betoltesKozben"
          @keydown.enter.prevent="enterGombLeutes($event)"
        ></textarea>
        <div class="karakter-szamlalo">
          <span :class="{ 'tul-hosszu': kommentSzoveg.length > 500 }">
            {{ kommentSzoveg.length }}/500
          </span>
        </div>
        <div class="komment-gombok">
          <button 
            @click="kommentKuldes()"
            :disabled="!kommentSzoveg.trim() || betoltesKozben || kommentSzoveg.length > 500"
            class="btn btn-primary"
          >
            <i class='bx bx-send' v-if="!betoltesKozben"></i>
            <i class='bx bx-loader-circle bx-spin' v-else></i>
            {{ betoltesKozben ? 'Küldés...' : 'Komment küldése' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Ha nincs bejelentkezve -->
    <div v-else class="bejelentkezes-info">
      <p>A kommenteléshez jelentkezz be!</p>
      <router-link to="/bejelentkezes" class="btn btn-outline-primary">
        <i class='bx bx-log-in'></i> Bejelentkezés
      </router-link>
    </div>

    <!-- Kommentek megjelenítése -->
    <div class="komment-lista" v-if="osszesKomment.length > 0">
      <div v-for="komment in osszesKomment" :key="komment.id" class="komment">
        <div class="komment-fejlec">
          <div class="felhasznalo-info">
            <div class="avatar">
              <i class='bx bx-user'></i>
            </div>
            <div class="felhasznalo-nev">
              <strong>{{ komment.username || 'Felhasználó' }}</strong>
              <span class="datum">{{ formatDatum(komment.created_at) }}</span>
            </div>
          </div>
          <button 
            v-if="aktualisFelhasznalo && aktualisFelhasznalo.id === komment.user_id"
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

    <!-- Ha nincs egyetlen komment sem -->
    <div v-else class="nincs-komment">
      <i class='bx bx-message-square-detail'></i>
      <p>Még nincsenek kommentek. Legyél te az első!</p>
    </div>

    <!-- Hibaüzenet ha valami rosszul sült el -->
    <div v-if="hibaUzenet" class="hiba-uzenet">
      <i class='bx bx-error-circle'></i>
      <span>{{ hibaUzenet }}</span>
    </div>
  </div>
</template>

<script>
export default {
  name: 'KommentBox',

  props: {
    esemenyId: {        // Melyik eseményhez tartoznak a kommentek
      type: Number,
      required: true
    },
    aktualisFelhasznalo: {  // Jelenleg bejelentkezett felhasználó
      type: Object,
      default: null
    }
  },
  
  // DATA - komponens belső állapota
  data() {
    return {
      osszesKomment: [],    // Összes megjelenítendő komment
      kommentSzoveg: '',    // Új komment szövege
      betoltesKozben: false, // Betöltés állapota (loading)
      hibaUzenet: '',       // Hibák megjelenítéséhez
      sikeresMentes: false  // Sikeres mentés jelzése
    }
  },
  
  // LIFECYCLE HOOKS - életciklus metódusok
  mounted() {
    this.kommentekBetoltese()
  },
  
  watch: {
    esemenyId() {
      // Ha az eseményId változik, újratöltjük a kommenteket
      this.kommentekBetoltese()
    }
  },
  
  methods: {
    /*
      Kommentek betöltése az API-ból
    */
    async kommentekBetoltese() {
      try {
        this.betoltesKozben = true
        // API hívás a kommentekért
        const valasz = await this.kommentekLekerese(this.esemenyId)
        this.osszesKomment = valasz
        
      } catch (hiba) {
        console.error('Hiba a kommentek betöltésekor:', hiba)
        this.hibaUzenet = 'Nem sikerült betölteni a kommenteket.'
      } finally {
        this.betoltesKozben = false
      }
    },
    
    /*
      Mock API hívás kommentek lekéréséhez
      Valós implementációban itt lenne a fetch vagy axios hívás
    */
    async kommentekLekerese(esemenyId) {
      // DEMO: teszt kommentek
      const tesztKommentek = [
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
      return tesztKommentek
    },
    
    enterGombLeutes(esemeny) {
      if (esemeny.shiftKey) {
        return // Shift+Enter = új sor
      }
      this.kommentKuldes()
    },
    
    /*
      Új komment küldése
    */
    async kommentKuldes() {
      const tisztitottSzoveg = this.kommentSzoveg.trim()
      
      // 1. VALIDÁCIÓ: üres szöveg ellenőrzése
      if (tisztitottSzoveg === '') {
        this.hibaUzenet = 'A komment nem lehet üres!'
        return
      }
      
      // 2. VALIDÁCIÓ: maximális hossz ellenőrzése
      if (tisztitottSzoveg.length > 500) {
        this.hibaUzenet = 'A komment túl hosszú! (max. 500 karakter)'
        return
      }
      
      try {
        this.betoltesKozben = true
        this.hibaUzenet = ''
        
        // 3. ELLENŐRZÉS: létezik-e az esemény (LEKERDEZ művelet)
        const esemenyLetezik = await this.esemenyLetezikE(this.esemenyId)
        
        if (!esemenyLetezik) {
          this.hibaUzenet = 'A kiválasztott esemény nem létezik!'
          this.betoltesKozben = false
          return
        }
        
        // 4. ÚJ KOMMENT OBJEKTUM LÉTREHOZÁSA
        const ujKomment = {
          eventId: this.esemenyId,
          userId: this.aktualisFelhasznalo.id,
          content: tisztitottSzoveg
        }
        
        // 5. KOMMENT MENTÉSE ADATBÁZISBA (BESZÚR művelet)
        const mentettKomment = await this.kommentMentese(ujKomment)
        
        if (mentettKomment) {
          // Hozzáadjuk a kommentek listájához
          this.osszesKomment.unshift({
            ...mentettKomment,
            username: this.aktualisFelhasznalo.username || this.aktualisFelhasznalo.name
          })
          
          // Mezők törlése
          this.kommentSzoveg = ''
          
          // Sikeres művelet jelzése
          this.sikeresMentes = true
          this.$emit('komment-sikeres', mentettKomment)
          
          // Sikeres üzenet 2 másodpercig látszik
          setTimeout(() => {
            this.sikeresMentes = false
          }, 2000)
        }
        
      } catch (hiba) {
        console.error('Hiba a komment mentésekor:', hiba)
        this.hibaUzenet = 'Hiba történt a komment küldésekor. Próbáld újra!'
      } finally {
        this.betoltesKozben = false
      }
    },
    
    async esemenyLetezikE(esemenyId) {
      // Mock: mindig igaz, valós esetben API hívás
      return true
    },
    
    /*
      Komment mentése (mock implementáció)
    */
    async kommentMentese(komment) {
      const ujKomment = {
        id: Date.now(), // Egyedi ID generálása
        event_id: komment.eventId,
        user_id: komment.userId,
        content: komment.content,
        created_at: new Date().toISOString()
      }
      
      // DEMO: localStorage-ba mentés (valós esetben API)
      const kommentekLocalStoragebol = JSON.parse(localStorage.getItem('esemeny_kommentek') || '[]')
      kommentekLocalStoragebol.push(ujKomment)
      localStorage.setItem('esemeny_kommentek', JSON.stringify(kommentekLocalStoragebol))
      
      return ujKomment
    },
    
    async kommentTorles(kommentId) {
      if (!confirm('Biztosan törölni szeretnéd ezt a kommentet?')) {
        return
      }
      
      try {
        // Törlés a lokális listából
        this.osszesKomment = this.osszesKomment.filter(k => k.id !== kommentId)
        
        // DEMO: localStorage-ból is törlés
        const kommentek = JSON.parse(localStorage.getItem('esemeny_kommentek') || '[]')
        const frissitettKommentek = kommentek.filter(k => k.id !== kommentId)
        localStorage.setItem('esemeny_kommentek', JSON.stringify(frissitettKommentek))
        
      } catch (hiba) {
        console.error('Hiba a komment törlésekor:', hiba)
        alert('Nem sikerült törölni a kommentet.')
      }
    },
    
    /*
      Dátum
      Példák: "5 perce", "3 órája", "2023. dec. 15. 14:30"
    */
    formatDatum(datumString) {
      const datum = new Date(datumString)
      const most = new Date()
      const kulonbseg = most - datum
      
      // Percben
      const percek = Math.floor(kulonbseg / 60000)
      if (percek < 60) {
        return `${percek} perce`
      }
      
      // Órában
      const orak = Math.floor(percek / 60)
      if (orak < 24) {
        return `${orak} órája`
      }
      
      // Több mint egy nap => dátum formázás
      return datum.toLocaleDateString('hu-HU', {
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