<template>
  <div class="komment-box">
    <!-- Bejelentkezett felhasználó számára komment írás -->
    <div v-if="aktualisFelhasznalo" class="komment-iras">
      <div class="komment-iras-tarolo">
        <div class="felhasznalo-avatar">
          <span class="avatar-betuk">{{ felhasznaloInitial }}</span>
        </div>
        <div class="komment-input-tarolo">
          <textarea
            v-model="kommentSzoveg"
            placeholder="Írd meg a gondolataidat az eseményről..."
            rows="2"
            maxlength="500"
            :disabled="betoltesKozben"
            @keydown.enter.prevent="enterGombLeutes"
            class="komment-textarea"
          ></textarea>
          
          <div class="komment-input-footer">
            <div class="karakter-info">
              <div class="karakter-szamlalo" :class="{ 'hataron-tul': kommentSzoveg.length > 500 }">
                <i class='bx bx-text'></i>
                <span>{{ kommentSzoveg.length }}/500</span>
              </div>
              <div v-if="kommentSzoveg.length > 500" class="figyelmeztetes">
                <i class='bx bx-error-circle'></i>
                Túl hosszú!
              </div>
            </div>
            
            <button
              @click="kommentKuldes"
              :disabled="!kommentSzoveg.trim() || betoltesKozben || kommentSzoveg.length > 500"
              class="kuldes-gomb"
              :class="{ 'betoltes': betoltesKozben }"
            >
              <span class="gomb-tartalom">
                <i class='bx' :class="betoltesKozben ? 'bx-loader-circle bx-spin' : 'bx-send'"></i>
                <span>{{ betoltesKozben ? 'Küldés...' : 'Küldés' }}</span>
              </span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Nem bejelentkezett felhasználó -->
    <div v-else class="bejelentkezes-felhivas">
      <div class="felhivas-tartalom">
        <div class="felhivas-ikon">
          <i class='bx bx-message-square-detail'></i>
        </div>
        <div class="felhivas-szoveg">
          <h3>Szeretnél hozzászólni?</h3>
          <p>Jelentkezz be, hogy kommentelhess és csatlakozhass a beszélgetéshez!</p>
        </div>
        <router-link to="/bejelentkezes" class="bejelentkezes-gomb">
          <i class='bx bx-log-in'></i>
          <span>Bejelentkezés</span>
        </router-link>
      </div>
    </div>

    <!-- Kommentek lista -->
    <div class="komment-lista">
      <div v-if="osszesKomment.length > 0" class="kommentek">
        <div class="lista-fejlec">
          <div class="fejlec-bal">
            <i class='bx bx-message-dots'></i>
            <h3>Hozzászólások</h3>
          </div>
          <div class="komment-szamlalo">
            <span>{{ osszesKomment.length }}</span>
            <span>{{ osszesKomment.length === 1 ? 'komment' : 'komment' }}</span>
          </div>
        </div>

        <div v-for="komment in rendezettKommentek" :key="komment.id" class="komment-kartya">
          <div class="komment-kartya-tartalom">
            <div class="komment-fejlec">
              <div class="komment-szerzo">
                <div class="szerzo-avatar">
                  <span class="avatar-betuk">{{ getInitials(komment.username) }}</span>
                </div>
                <div class="szerzo-info">
                  <span class="szerzo-nev">{{ komment.username || 'Felhasználó' }}</span>
                  <span class="szerzo-badge" v-if="komment.user_id === aktualisFelhasznalo?.id">Te</span>
                  <span class="komment-datum">
                    <i class='bx bx-time'></i>
                    {{ formatDatum(komment.created_at) }}
                  </span>
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
              <p>{{ komment.content }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Nincs komment -->
      <div v-else class="nincs-komment">
        <div class="nincs-tartalom">
          <div class="nincs-ikon">
            <i class='bx bx-message-square-dots'></i>
          </div>
          <h4>Még nincsenek hozzászólások</h4>
          <p>Legyél te az első, aki megosztja a gondolatait!</p>
        </div>
      </div>
    </div>

    <!-- Hibaüzenet -->
    <transition name="slide-up">
      <div v-if="hibaUzenet" class="hiba-banner">
        <div class="hiba-tartalom">
          <i class='bx bx-error-circle'></i>
          <span>{{ hibaUzenet }}</span>
        </div>
        <button @click="hibaUzenet = ''" class="hiba-bezaras">
          <i class='bx bx-x'></i>
        </button>
      </div>
    </transition>

    <!-- Sikeres mentés jelzése -->
    <transition name="fade">
      <div v-if="sikeresMentes" class="siker-banner">
        <i class='bx bx-check-circle'></i>
        <span>Komment sikeresen elküldve!</span>
      </div>
    </transition>
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
    aktualisFelhasznalo: {
      type: Object,
      default: null
    }
  },

  data() {
    return {
      osszesKomment: [],
      kommentSzoveg: '',
      betoltesKozben: false,
      hibaUzenet: '',
      sikeresMentes: false
    }
  },

  computed: {
    felhasznaloInitial() {
      if (!this.aktualisFelhasznalo) return '?'
      const nev = this.aktualisFelhasznalo.name || this.aktualisFelhasznalo.username || 'Felhasználó'
      return nev.charAt(0).toUpperCase()
    },

    rendezettKommentek() {
      return [...this.osszesKomment].sort((a, b) => 
        new Date(b.created_at) - new Date(a.created_at)
      )
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
    getInitials(name) {
      if (!name) return '?'
      return name.charAt(0).toUpperCase()
    },

    async kommentekBetoltese() {
      try {
        this.betoltesKozben = true
        this.hibaUzenet = ''
        this.osszesKomment = await this.kommentekLekerese(this.esemenyId)
      } catch (hiba) {
        console.error('Hiba a kommentek betöltésekor:', hiba)
        this.hibaUzenet = 'Nem sikerült betölteni a kommenteket.'
      } finally {
        this.betoltesKozben = false
      }
    },

    async kommentekLekerese(esemenyId) {
      // Valós API hívás helyett localStorage-ból olvasunk
      const kommentek = JSON.parse(localStorage.getItem('esemeny_kommentek') || '[]')
      return kommentek.filter(k => k.event_id == esemenyId)
    },

    enterGombLeutes(esemeny) {
      if (!esemeny.shiftKey) {
        this.kommentKuldes()
      }
    },

    async kommentKuldes() {
      const tisztitottSzoveg = this.kommentSzoveg.trim()

      if (tisztitottSzoveg === '') {
        this.hibaUzenet = 'A komment nem lehet üres!'
        return
      }

      if (tisztitottSzoveg.length > 500) {
        this.hibaUzenet = 'A komment maximum 500 karakter hosszú lehet!'
        return
      }

      try {
        this.betoltesKozben = true
        this.hibaUzenet = ''

        const ujKomment = {
          event_id: this.esemenyId,
          user_id: this.aktualisFelhasznalo.id,
          username: this.aktualisFelhasznalo.name || this.aktualisFelhasznalo.username,
          content: tisztitottSzoveg,
          created_at: new Date().toISOString()
        }

        const mentettKomment = await this.kommentMentese(ujKomment)

        if (mentettKomment) {
          this.osszesKomment.push(mentettKomment)
          this.kommentSzoveg = ''
          this.sikeresMentes = true
          this.$emit('komment-sikeres', mentettKomment)

          setTimeout(() => {
            this.sikeresMentes = false
          }, 3000)
        }
      } catch (hiba) {
        console.error('Hiba a komment mentésekor:', hiba)
        this.hibaUzenet = 'Hiba történt a komment küldésekor. Próbáld újra!'
      } finally {
        this.betoltesKozben = false
      }
    },

    async kommentMentese(komment) {
      const kommentId = Date.now()
      const ujKomment = {
        id: kommentId,
        ...komment
      }

      const kommentek = JSON.parse(localStorage.getItem('esemeny_kommentek') || '[]')
      kommentek.push(ujKomment)
      localStorage.setItem('esemeny_kommentek', JSON.stringify(kommentek))

      return ujKomment
    },

    async kommentTorles(kommentId) {
      if (!confirm('Biztosan törölni szeretnéd ezt a kommentet?')) {
        return
      }

      try {
        this.osszesKomment = this.osszesKomment.filter(k => k.id !== kommentId)

        const kommentek = JSON.parse(localStorage.getItem('esemeny_kommentek') || '[]')
        const frissitettKommentek = kommentek.filter(k => k.id !== kommentId)
        localStorage.setItem('esemeny_kommentek', JSON.stringify(frissitettKommentek))

      } catch (hiba) {
        console.error('Hiba a komment törlésekor:', hiba)
        this.hibaUzenet = 'Nem sikerült törölni a kommentet.'
      }
    },

    formatDatum(datumString) {
      const datum = new Date(datumString)
      const most = new Date()
      const kulonbseg = most - datum

      const percek = Math.floor(kulonbseg / 60000)
      if (percek < 1) return 'Most'
      if (percek < 60) return `${percek} perce`

      const orak = Math.floor(percek / 60)
      if (orak < 24) return `${orak} órája`

      const napok = Math.floor(orak / 24)
      if (napok < 7) return `${napok} napja`

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
/* Alap stílusok */
.komment-box {
  width: 100%;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

/* Komment írás szekció */
.komment-iras {
  margin-bottom: 2.5rem;
}

.komment-iras-tarolo {
  display: flex;
  gap: 1rem;
  background: linear-gradient(135deg, #f8f9fe, #f1f4ff);
  border-radius: 24px;
  padding: 1.5rem;
  border: 1px solid rgba(102, 126, 234, 0.1);
  transition: all 0.3s ease;
}

.komment-iras-tarolo:focus-within {
  border-color: #667eea;
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
  background: white;
}

.felhasznalo-avatar {
  flex-shrink: 0;
}

.avatar-betuk {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: 1.25rem;
  box-shadow: 0 4px 10px rgba(102, 126, 234, 0.3);
}

.komment-input-tarolo {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.komment-textarea {
  width: 100%;
  padding: 1rem;
  border: 2px solid transparent;
  border-radius: 16px;
  background: white;
  font-family: inherit;
  font-size: 0.95rem;
  line-height: 1.6;
  resize: vertical;
  transition: all 0.3s;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
}

.komment-textarea:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.1);
}

.komment-textarea:disabled {
  background: #f8fafc;
  opacity: 0.7;
  cursor: not-allowed;
}

.komment-input-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.karakter-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.karakter-szamlalo {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: #f1f4ff;
  border-radius: 50px;
  color: #4a5568;
  font-size: 0.875rem;
  font-weight: 500;
}

.karakter-szamlalo i {
  color: #667eea;
  font-size: 1.1rem;
}

.karakter-szamlalo.hataron-tul {
  background: #fff5f5;
  color: #c53030;
}

.karakter-szamlalo.hataron-tul i {
  color: #c53030;
}

.figyelmeztetes {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  color: #c53030;
  font-size: 0.75rem;
  font-weight: 500;
}

.kuldes-gomb {
  padding: 0.625rem 1.5rem;
  border: none;
  border-radius: 50px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.3s;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.kuldes-gomb:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

.kuldes-gomb:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  box-shadow: none;
}

.kuldes-gomb.betoltes {
  background: linear-gradient(135deg, #8795e8, #8b6cb0);
}

.gomb-tartalom {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

/* Bejelentkezés felhívás */
.bejelentkezes-felhivas {
  background: linear-gradient(135deg, #f8f9fe, #f1f4ff);
  border-radius: 24px;
  padding: 2rem;
  margin-bottom: 2.5rem;
  border: 2px dashed rgba(102, 126, 234, 0.3);
}

.felhivas-tartalom {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  flex-wrap: wrap;
  justify-content: center;
  text-align: center;
}

.felhivas-ikon i {
  font-size: 3rem;
  color: #667eea;
  opacity: 0.5;
}

.felhivas-szoveg h3 {
  color: #2d3748;
  font-size: 1.25rem;
  margin-bottom: 0.5rem;
}

.felhivas-szoveg p {
  color: #718096;
}

.bejelentkezes-gomb {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 2rem;
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  text-decoration: none;
  border-radius: 50px;
  font-weight: 600;
  transition: all 0.3s;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.bejelentkezes-gomb:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

/* Komment lista */
.komment-lista {
  margin-top: 1rem;
}

.lista-fejlec {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #f1f4ff;
}

.fejlec-bal {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.fejlec-bal i {
  font-size: 1.5rem;
  color: #667eea;
}

.fejlec-bal h3 {
  font-size: 1.1rem;
  color: #2d3748;
  margin: 0;
}

.komment-szamlalo {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.375rem 1rem;
  background: #f1f4ff;
  border-radius: 50px;
  color: #667eea;
  font-weight: 600;
  font-size: 0.875rem;
}

.komment-szamlalo span:first-child {
  background: white;
  padding: 0.125rem 0.375rem;
  border-radius: 12px;
  min-width: 24px;
  text-align: center;
}

/* Komment kártyák */
.komment-kartya {
  margin-bottom: 1rem;
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.komment-kartya-tartalom {
  background: white;
  border-radius: 20px;
  padding: 1.5rem;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
  border: 1px solid #f1f4ff;
  transition: all 0.3s;
}

.komment-kartya-tartalom:hover {
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05);
  border-color: #e2e8f0;
  transform: translateY(-2px);
}

.komment-fejlec {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.komment-szerzo {
  display: flex;
  gap: 1rem;
}

.szerzo-avatar {
  flex-shrink: 0;
}

.szerzo-avatar .avatar-betuk {
  width: 40px;
  height: 40px;
  font-size: 1rem;
}

.szerzo-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.szerzo-nev {
  font-weight: 600;
  color: #2d3748;
  font-size: 1rem;
}

.szerzo-badge {
  display: inline-block;
  padding: 0.125rem 0.5rem;
  background: #f1f4ff;
  color: #667eea;
  border-radius: 12px;
  font-size: 0.625rem;
  font-weight: 600;
  margin-left: 0.5rem;
}

.komment-datum {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  color: #a0aec0;
  font-size: 0.75rem;
}

.komment-datum i {
  font-size: 0.875rem;
}

.torles-gomb {
  width: 32px;
  height: 32px;
  border: none;
  background: #fff5f5;
  color: #c53030;
  border-radius: 10px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s;
  opacity: 0.7;
}

.torles-gomb:hover {
  background: #c53030;
  color: white;
  opacity: 1;
  transform: scale(1.1);
}

.torles-gomb i {
  font-size: 1.1rem;
}

.komment-tartalom p {
  color: #4a5568;
  line-height: 1.6;
  font-size: 0.95rem;
  margin: 0;
  white-space: pre-line;
}

/* Nincs komment */
.nincs-komment {
  text-align: center;
  padding: 3rem 1.5rem;
  background: #f8fafc;
  border-radius: 24px;
  border: 2px dashed #e2e8f0;
}

.nincs-ikon i {
  font-size: 4rem;
  color: #667eea;
  opacity: 0.3;
  margin-bottom: 1rem;
}

.nincs-tartalom h4 {
  color: #2d3748;
  font-size: 1.25rem;
  margin-bottom: 0.5rem;
}

.nincs-tartalom p {
  color: #718096;
}

/* Hiba és siker banner */
.hiba-banner,
.siker-banner {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1rem 1.5rem;
  border-radius: 16px;
  background: white;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  z-index: 1000;
  min-width: 300px;
  animation: slideInRight 0.3s ease-out;
}

@keyframes slideInRight {
  from {
    opacity: 0;
    transform: translateX(100%);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.hiba-banner {
  background: #fff5f5;
  border-left: 4px solid #c53030;
}

.siker-banner {
  background: #f0fff4;
  border-left: 4px solid #2f855a;
}

.hiba-tartalom {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.hiba-banner i {
  color: #c53030;
  font-size: 1.25rem;
}

.siker-banner i {
  color: #2f855a;
  font-size: 1.25rem;
}

.hiba-banner span,
.siker-banner span {
  color: #2d3748;
  font-size: 0.95rem;
}

.hiba-bezaras {
  background: transparent;
  border: none;
  color: #718096;
  cursor: pointer;
  padding: 0.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  transition: all 0.3s;
}

.hiba-bezaras:hover {
  background: rgba(0, 0, 0, 0.05);
  color: #2d3748;
}

/* Animációk */
.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.3s ease;
}

.slide-up-enter-from,
.slide-up-leave-to {
  opacity: 0;
  transform: translateY(20px);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Responsive design */
@media (max-width: 768px) {
  .komment-iras-tarolo {
    flex-direction: column;
  }
  
  .felhasznalo-avatar {
    align-self: flex-start;
  }
  
  .komment-input-footer {
    flex-direction: column;
    align-items: stretch;
  }
  
  .karakter-info {
    justify-content: space-between;
  }
  
  .kuldes-gomb {
    width: 100%;
    justify-content: center;
  }
  
  .felhivas-tartalom {
    flex-direction: column;
    gap: 1rem;
  }
  
  .komment-fejlec {
    flex-direction: column;
    gap: 1rem;
  }
  
  .torles-gomb {
    align-self: flex-end;
  }
  
  .hiba-banner,
  .siker-banner {
    left: 1rem;
    right: 1rem;
    min-width: auto;
    bottom: 1rem;
  }
}

@media (max-width: 480px) {
  .komment-iras-tarolo {
    padding: 1rem;
  }
  
  .komment-kartya-tartalom {
    padding: 1rem;
  }
  
  .komment-szerzo {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  
  .szerzo-info {
    width: 100%;
  }
  
  .komment-datum {
    margin-top: 0.25rem;
  }
}
</style>