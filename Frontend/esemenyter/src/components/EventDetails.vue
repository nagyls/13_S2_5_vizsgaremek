<template>
  <div class="esemeny-reszletek">
    <div class="container">
      <!-- Navigáció -->
      <div class="navigacio">
        <button class="btn-vissza" @click="visszaAzEsemenyekhez">
          <i class='bx bx-arrow-back'></i>
          <span>Vissza az eseményekhez</span>
        </button>
      </div>

      <!-- Betöltés állapot -->
      <div v-if="betoltesKozben" class="allapot-kartya betoltes">
        <div class="loader">
          <div class="spinner"></div>
        </div>
        <h3>Esemény betöltése...</h3>
        <p>Kérlek várj, amíg betöltjük az esemény részleteit</p>
      </div>

      <!-- Hiba állapot -->
      <div v-else-if="hibaUzenet" class="allapot-kartya hiba">
        <div class="hiba-ikon">
          <i class='bx bx-error-circle'></i>
        </div>
        <h3>Hiba történt</h3>
        <p class="hiba-uzenet">{{ hibaUzenet }}</p>
        <div class="hiba-muveletek">
          <button @click="$router.back()" class="btn btn-masodlagos">
            <i class='bx bx-arrow-back'></i> Vissza
          </button>
          <button @click="$router.push('/esemenyek')" class="btn btn-elsodleges">
            <i class='bx bx-grid'></i> Összes esemény
          </button>
        </div>
      </div>

      <!-- Esemény tartalom -->
      <div v-else-if="esemenyAdatok" class="esemeny-tartalom">
        <!-- Hero szekció -->
        <div class="hero-szekcio">
          <div class="hero-overlay"></div>
          <div class="hero-tartalom">
            <div class="hero-cimke">
              <span class="cimke" :class="esemenyAdatok.type">
                <i class='bx' :class="esemenyAdatok.type === 'local' ? 'bx-building' : 'bx-world'"></i>
                {{ esemenyAdatok.type === 'local' ? 'Helyi esemény' : 'Globális esemény' }}
              </span>
              <span class="cimke" :class="esemenyAdatok.status">
                <i class='bx' :class="esemenyAdatok.status === 'open' ? 'bx-calendar-event' : 'bx-calendar-x'"></i>
                {{ esemenyAdatok.status === 'open' ? 'Aktív' : 'Lezárva' }}
              </span>
            </div>
            <h1 class="hero-cim">{{ esemenyAdatok.title }}</h1>
            <div class="hero-muveletek">
              <button v-if="aktualisFelhasznalo" class="btn-ikon" @click="kedvencBeallitas">
                <i class='bx bx-star' :class="{ 'aktiv': esemenyAdatok.isFavorite }"></i>
                <span class="btn-szoveg">Kedvenc</span>
              </button>
              <button class="btn-ikon" @click="megosztas">
                <i class='bx bx-share-alt'></i>
                <span class="btn-szoveg">Megosztás</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Fő tartalom rács -->
        <div class="tartalom-racs">
          <!-- Bal oldali oszlop -->
          <div class="bal-oszlop">
            <!-- Szervező infok -->
            <div class="info-blokk">
              <div class="blokk-fejlec">
                <i class='bx bx-user'></i>
                <h2>Szervező</h2>
              </div>
              <div class="szervezo-profil">
                <div class="profil-avatar">
                  {{ esemenyAdatok.creator_name?.charAt(0) || '?' }}
                </div>
                <div class="profil-adatok">
                  <span class="profil-nev">{{ esemenyAdatok.creator_name || 'Ismeretlen' }}</span>
                  <span class="profil-tipus">Esemény szervező</span>
                </div>
              </div>
            </div>

            <!-- Leírás -->
            <div class="info-blokk">
              <div class="blokk-fejlec">
                <i class='bx bx-detail'></i>
                <h2>Leírás</h2>
              </div>
              <p class="leiras">{{ esemenyAdatok.description }}</p>
            </div>

            <!-- Részletes tartalom -->
            <div v-if="esemenyAdatok.content" class="info-blokk">
              <div class="blokk-fejlec">
                <i class='bx bx-file'></i>
                <h2>Részletek</h2>
              </div>
              <div class="reszletes-tartalom">{{ esemenyAdatok.content }}</div>
            </div>

            <!-- Kép -->
            <div v-if="esemenyAdatok.image_url" class="info-blokk">
              <div class="blokk-fejlec">
                <i class='bx bx-image'></i>
                <h2>Galéria</h2>
              </div>
              <div class="kep-container">
                <img :src="esemenyAdatok.image_url" :alt="esemenyAdatok.title">
              </div>
            </div>
          </div>

          <!-- Jobb oldali oszlop -->
          <div class="jobb-oszlop">
            <!-- Dátum és idő -->
            <div class="info-kartya">
              <h3><i class='bx bx-calendar'></i> Időpontok</h3>
              <div class="datum-lista">
                <div class="datum-elem">
                  <span class="datum-cimke">Kezdés</span>
                  <span class="datum-ertek">{{ formatDatum(esemenyAdatok.start_date) }}</span>
                </div>
                <div class="datum-elem">
                  <span class="datum-cimke">Befejezés</span>
                  <span class="datum-ertek">{{ formatDatum(esemenyAdatok.end_date) }}</span>
                </div>
                <div class="datum-elem">
                  <span class="datum-cimke">Létrehozva</span>
                  <span class="datum-ertek">{{ formatDatum(esemenyAdatok.created_at) }}</span>
                </div>
              </div>
            </div>

            <!-- Statisztika -->
            <div class="info-kartya">
              <h3><i class='bx bx-stats'></i> Statisztika</h3>
              <div class="statisztika-grid">
                <div class="stat-elem">
                  <div class="stat-ikon">
                    <i class='bx bx-message-detail'></i>
                  </div>
                  <div class="stat-adat">
                    <span class="stat-szam">{{ kommentekSzama }}</span>
                    <span class="stat-cimke">Komment</span>
                  </div>
                </div>
                <div class="stat-elem">
                  <div class="stat-ikon">
                    <i class='bx bx-check-circle'></i>
                  </div>
                  <div class="stat-adat">
                    <span class="stat-szam">{{ resztvevokSzama }}</span>
                    <span class="stat-cimke">Részt vesz</span>
                  </div>
                </div>
                <div class="stat-elem">
                  <div class="stat-ikon">
                    <i class='bx bx-x-circle'></i>
                  </div>
                  <div class="stat-adat">
                    <span class="stat-szam">{{ nemResztvevokSzama }}</span>
                    <span class="stat-cimke">Nem vesz részt</span>
                  </div>
                </div>
                <div class="stat-elem">
                  <div class="stat-ikon">
                    <i class='bx bx-star'></i>
                  </div>
                  <div class="stat-adat">
                    <span class="stat-szam">{{ kedvencekSzama }}</span>
                    <span class="stat-cimke">Kedvenc</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Részvétel -->
            <div v-if="esemenyAdatok.status === 'open' && aktualisFelhasznalo" class="info-kartya resztvetel">
              <h3><i class='bx bx-check-shield'></i> Részvétel</h3>
              <p class="resztvetel-leiras">Hogyan szeretnél részt venni az eseményen?</p>
              <div class="resztvetel-valasztok">
                <button @click="resztvetelKuldes('y')" 
                        class="btn-valasz resztvesz" 
                        :class="{ 'aktiv': felhasznaloResztvetel === 'y' }">
                  <i class='bx bx-check-circle'></i>
                  <div class="valasz-tartalom">
                    <span class="valasz-cim">Részvétel</span>
                    <span class="valasz-leiras">Részt veszek az eseményen</span>
                  </div>
                </button>
                <button @click="resztvetelKuldes('n')" 
                        class="btn-valasz nem-resztvesz" 
                        :class="{ 'aktiv': felhasznaloResztvetel === 'n' }">
                  <i class='bx bx-x-circle'></i>
                  <div class="valasz-tartalom">
                    <span class="valasz-cim">Nem veszek részt</span>
                    <span class="valasz-leiras">Nem tudok jelen lenni</span>
                  </div>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Komment szekció -->
        <div class="komment-szekcio">
          <div class="komment-fejlec">
            <div class="fejlec-bal">
              <i class='bx bx-message-dots'></i>
              <h2>Hozzászólások</h2>
            </div>
            <div class="komment-szamlalo">
              <span>{{ kommentekSzama }}</span>
              <span>komment</span>
            </div>
          </div>
          
          <CommentBox 
            :esemenyId="parseInt(esemenyId)"
            :currentUser="aktualisFelhasznalo"
            @komment-sikeres="ujKommentHozzaadva"
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
      esemenyId: this.$route.params.id,
      esemenyAdatok: null,
      aktualisFelhasznalo: null,
      betoltesKozben: true,
      hibaUzenet: '',
      kommentekSzama: 0,
      resztvevokSzama: 0,
      nemResztvevokSzama: 0,
      kedvencekSzama: 0,
      felhasznaloResztvetel: null
    }
  },
  
  created() {
    this.betoltEsemenyt()
    this.betoltFelhasznalot()
  },
  
  methods: {
    visszaAzEsemenyekhez() {
      this.$router.push('/events-list')
    },

    async betoltEsemenyt() {
      try {
        this.betoltesKozben = true
        const esemenyMegtalalva = await this.lekerdezEsemenyt(this.esemenyId)
        
        if (!esemenyMegtalalva) {
          this.hibaUzenet = 'A kiválasztott esemény nem létezik vagy elérhetetlen.'
          return
        }
        
        this.esemenyAdatok = esemenyMegtalalva
        await Promise.all([
          this.betoltStatisztikakat(),
          this.betoltResztveteliAllapotot()
        ])
      } catch (hiba) {
        console.error('Hiba az esemény betöltésekor:', hiba)
        this.hibaUzenet = 'Nem sikerült betölteni az esemény adatait. Kérlek próbáld újra később.'
      } finally {
        this.betoltesKozben = false
      }
    },
    
    async lekerdezEsemenyt(esemenyId) {
      const osszesEsemeny = JSON.parse(localStorage.getItem('esemenyek') || '[]')
      let esemeny = osszesEsemeny.find(e => e.id == esemenyId)
      
      if (!esemeny) {
        esemeny = {
          id: esemenyId,
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
      
      return esemeny
    },
    
    async betoltFelhasznalot() {
      const mentettFelhasznalo = JSON.parse(localStorage.getItem('esemenyter_user') || 'null')
      
      if (mentettFelhasznalo?.isLoggedIn) {
        this.aktualisFelhasznalo = {
          id: mentettFelhasznalo.id || 1,
          username: mentettFelhasznalo.name || 'Felhasználó',
          name: mentettFelhasznalo.name || 'Felhasználó',
          email: mentettFelhasznalo.email || '',
          role: mentettFelhasznalo.role || 'student'
        }
      }
    },
    
    async betoltStatisztikakat() {
      try {
        const kommentek = JSON.parse(localStorage.getItem('esemeny_kommentek') || '[]')
        this.kommentekSzama = kommentek.filter(k => k.event_id == this.esemenyId).length
        
        // Demo adatok
        this.resztvevokSzama = 45
        this.nemResztvevokSzama = 12
        this.kedvencekSzama = 28
      } catch (hiba) {
        console.error('Hiba a statisztikák betöltésekor:', hiba)
      }
    },
    
    async betoltResztveteliAllapotot() {
      if (!this.aktualisFelhasznalo) return
      
      const resztveteliAdatok = JSON.parse(localStorage.getItem('esemeny_resztvetel') || '[]')
      const felhasznaloResztvetelAdat = resztveteliAdatok.find(
        r => r.event_id == this.esemenyId && r.user_id == this.aktualisFelhasznalo.id
      )
      
      this.felhasznaloResztvetel = felhasznaloResztvetelAdat?.valasz || null
    },
    
    async resztvetelKuldes(valasz) {
      if (!this.aktualisFelhasznalo) {
        this.mutatUzenetet('A részvételhez be kell jelentkezned!', 'info')
        this.$router.push('/login')
        return
      }
      
      try {
        const resztveteliAdatok = JSON.parse(localStorage.getItem('esemeny_resztvetel') || '[]')
        const ujAdatok = resztveteliAdatok.filter(
          r => !(r.event_id == this.esemenyId && r.user_id == this.aktualisFelhasznalo.id)
        )
        
        ujAdatok.push({
          event_id: this.esemenyId,
          user_id: this.aktualisFelhasznalo.id,
          valasz: valasz,
          frissitve: new Date().toISOString()
        })
        
        localStorage.setItem('esemeny_resztvetel', JSON.stringify(ujAdatok))
        this.felhasznaloResztvetel = valasz
        
        if (valasz === 'y') {
          this.resztvevokSzama++
          if (this.felhasznaloResztvetel === 'n') this.nemResztvevokSzama--
          this.mutatUzenetet('Köszönjük a részvételi szándékod!', 'success')
        } else {
          this.nemResztvevokSzama++
          if (this.felhasznaloResztvetel === 'y') this.resztvevokSzama--
          this.mutatUzenetet('Válaszod rögzítettük.', 'success')
        }
      } catch (hiba) {
        console.error('Hiba a részvétel küldésekor:', hiba)
        this.mutatUzenetet('Hiba történt a válasz küldése közben.', 'error')
      }
    },
    
    ujKommentHozzaadva() {
      this.kommentekSzama++
      this.mutatUzenetet('Hozzászólásod sikeresen elküldtük!', 'success')
    },
    
    async kedvencBeallitas() {
      if (!this.aktualisFelhasznalo) {
        this.mutatUzenetet('A kedvencekhez adáshoz jelentkezz be!', 'info')
        return
      }
      
      try {
        const kedvencek = JSON.parse(localStorage.getItem('esemeny_kedvencek') || '[]')
        const index = kedvencek.findIndex(
          k => k.event_id == this.esemenyId && k.user_id == this.aktualisFelhasznalo.id
        )
        
        if (index === -1) {
          kedvencek.push({
            event_id: this.esemenyId,
            user_id: this.aktualisFelhasznalo.id,
            letrehozva: new Date().toISOString()
          })
          this.kedvencekSzama++
          this.mutatUzenetet('Esemény hozzáadva a kedvencekhez!', 'success')
        } else {
          kedvencek.splice(index, 1)
          this.kedvencekSzama--
          this.mutatUzenetet('Esemény eltávolítva a kedvencekből!', 'success')
        }
        
        localStorage.setItem('esemeny_kedvencek', JSON.stringify(kedvencek))
        this.esemenyAdatok.isFavorite = index === -1
      } catch (hiba) {
        console.error('Hiba a kedvenc beállításakor:', hiba)
        this.mutatUzenetet('Hiba történt a művelet során.', 'error')
      }
    },
    
    megosztas() {
      if (navigator.share) {
        navigator.share({
          title: this.esemenyAdatok.title,
          text: this.esemenyAdatok.description,
          url: window.location.href
        }).catch(() => {
          this.linkMasolas()
        })
      } else {
        this.linkMasolas()
      }
    },
    
    linkMasolas() {
      navigator.clipboard.writeText(window.location.href)
      this.mutatUzenetet('Link másolva a vágólapra!', 'success')
    },
    
    formatDatum(datumString) {
      if (!datumString) return 'Nincs megadva'
      const datum = new Date(datumString)
      return datum.toLocaleDateString('hu-HU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    },
    
    mutatUzenetet(uzenet, tipus = 'success') {
      // Itt lehetne egy szép toast értesítő
      console.log(`[${tipus}]`, uzenet)
      alert(uzenet)
    }
  }
}
</script>

<style scoped>
/* Alap stílusok */
.esemeny-reszletek {
  min-height: 100vh;
  min-width: 100vw;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

.container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
}

/* Navigáció */
.navigacio {
  margin-bottom: 2rem;
}

.btn-vissza {
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

.btn-vissza:hover {
  transform: translateX(-5px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  background: white;
}

/* Állapot kártyák */
.allapot-kartya {
  background: white;
  border-radius: 32px;
  padding: 3rem 2rem;
  text-align: center;
  max-width: 500px;
  margin: 4rem auto;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.allapot-kartya.betoltes .loader {
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

.allapot-kartya h3 {
  font-size: 1.5rem;
  color: #1a202c;
  margin-bottom: 0.75rem;
  font-weight: 700;
}

.allapot-kartya p {
  color: #718096;
  line-height: 1.6;
}

.hiba-ikon i {
  font-size: 4rem;
  color: #fc8181;
  margin-bottom: 1rem;
}

.hiba-uzenet {
  background: #fff5f5;
  color: #c53030;
  padding: 1rem;
  border-radius: 12px;
  margin: 1.5rem 0;
  font-weight: 500;
}

.hiba-muveletek {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

/* Hero szekció */
.hero-szekcio {
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

.hero-tartalom {
  position: relative;
  padding: 3rem;
  width: 100%;
  color: white;
}

.hero-cimke {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.cimke {
  padding: 0.5rem 1rem;
  border-radius: 50px;
  font-size: 0.875rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  backdrop-filter: blur(10px);
}

.cimke.local {
  background: rgba(72, 187, 120, 0.2);
  color: #fff;
  border: 1px solid rgba(72, 187, 120, 0.4);
}

.cimke.global {
  background: rgba(66, 153, 225, 0.2);
  color: #fff;
  border: 1px solid rgba(66, 153, 225, 0.4);
}

.cimke.open {
  background: rgba(237, 137, 54, 0.2);
  color: #fff;
  border: 1px solid rgba(237, 137, 54, 0.4);
}

.cimke.closed {
  background: rgba(229, 62, 62, 0.2);
  color: #fff;
  border: 1px solid rgba(229, 62, 62, 0.4);
}

.hero-cim {
  font-size: 3rem;
  font-weight: 800;
  line-height: 1.2;
  margin-bottom: 1.5rem;
  max-width: 800px;
}

.hero-muveletek {
  display: flex;
  gap: 1rem;
}

.btn-ikon {
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

.btn-ikon:hover {
  background: rgba(255, 255, 255, 0.25);
  transform: translateY(-2px);
}

.btn-ikon i.aktiv {
  color: #ffd700;
  fill: #ffd700;
}

/* Tartalom rács */
.tartalom-racs {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 2rem;
  margin-bottom: 2rem;
}

/* Bal oszlop */
.bal-oszlop {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.info-blokk {
  background: white;
  border-radius: 24px;
  padding: 2rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.blokk-fejlec {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.blokk-fejlec i {
  font-size: 1.5rem;
  color: #667eea;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.blokk-fejlec h2 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1a202c;
  margin: 0;
}

/* Szervező profil */
.szervezo-profil {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  background: linear-gradient(135deg, #667eea10, #764ba210);
  padding: 1.5rem;
  border-radius: 16px;
}

.profil-avatar {
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

.profil-adatok {
  display: flex;
  flex-direction: column;
}

.profil-nev {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1a202c;
  margin-bottom: 0.25rem;
}

.profil-tipus {
  font-size: 0.875rem;
  color: #718096;
}

/* Leírás */
.leiras {
  font-size: 1.125rem;
  line-height: 1.7;
  color: #2d3748;
  margin: 0;
}

.reszletes-tartalom {
  background: #f7fafc;
  padding: 1.5rem;
  border-radius: 16px;
  color: #4a5568;
  line-height: 1.7;
  white-space: pre-line;
}

/* Kép */
.kep-container {
  border-radius: 16px;
  overflow: hidden;
}

.kep-container img {
  width: 100%;
  height: auto;
  display: block;
  transition: transform 0.5s;
}

.kep-container:hover img {
  transform: scale(1.05);
}

/* Jobb oszlop */
.jobb-oszlop {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.info-kartya {
  background: white;
  border-radius: 24px;
  padding: 2rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.info-kartya h3 {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 1.25rem;
  font-weight: 700;
  color: #1a202c;
  margin-bottom: 1.5rem;
}

.info-kartya h3 i {
  color: #667eea;
}

/* Dátum lista */
.datum-lista {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.datum-elem {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: #f7fafc;
  border-radius: 12px;
}

.datum-cimke {
  color: #718096;
  font-size: 0.875rem;
  font-weight: 500;
}

.datum-ertek {
  color: #1a202c;
  font-weight: 600;
}

/* Statisztika */
.statisztika-grid {
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

.stat-adat {
  display: flex;
  flex-direction: column;
}

.stat-szam {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1a202c;
  line-height: 1.2;
}

.stat-cimke {
  font-size: 0.75rem;
  color: #718096;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Részvétel */
.resztvetel-leiras {
  color: #718096;
  margin-bottom: 1.5rem;
}

.resztvetel-valasztok {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.btn-valasz {
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

.btn-valasz i {
  font-size: 1.5rem;
}

.valasz-tartalom {
  display: flex;
  flex-direction: column;
}

.valasz-cim {
  font-weight: 700;
  margin-bottom: 0.25rem;
}

.valasz-leiras {
  font-size: 0.75rem;
  color: #718096;
}

.btn-valasz.resztvesz {
  color: #2f855a;
}

.btn-valasz.resztvesz:hover,
.btn-valasz.resztvesz.aktiv {
  background: #2f855a;
  color: white;
}

.btn-valasz.resztvesz.aktiv .valasz-leiras {
  color: rgba(255, 255, 255, 0.9);
}

.btn-valasz.nem-resztvesz {
  color: #c53030;
}

.btn-valasz.nem-resztvesz:hover,
.btn-valasz.nem-resztvesz.aktiv {
  background: #c53030;
  color: white;
}

.btn-valasz.nem-resztvesz.aktiv .valasz-leiras {
  color: rgba(255, 255, 255, 0.9);
}

/* Komment szekció */
.komment-szekcio {
  background: white;
  border-radius: 24px;
  padding: 2rem;
  margin-top: 2rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.komment-fejlec {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
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

.fejlec-bal h2 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1a202c;
  margin: 0;
}

.komment-szamlalo {
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

.btn-elsodleges {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
}

.btn-elsodleges:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
}

.btn-masodlagos {
  background: white;
  color: #2d3748;
  border: 2px solid #e2e8f0;
}

.btn-masodlagos:hover {
  background: #f7fafc;
  border-color: #cbd5e0;
}

/* Responsive */
@media (max-width: 1024px) {
  .tartalom-racs {
    grid-template-columns: 1fr;
  }
  
  .hero-cim {
    font-size: 2.5rem;
  }
}

@media (max-width: 768px) {
  .container {
    padding: 1rem;
  }
  
  .hero-tartalom {
    padding: 2rem;
  }
  
  .hero-cim {
    font-size: 2rem;
  }
  
  .hero-muveletek {
    flex-direction: column;
  }
  
  .btn-ikon {
    width: 100%;
    justify-content: center;
  }
  
  .hero-cimke {
    flex-direction: column;
  }
  
  .cimke {
    width: fit-content;
  }
  
  .szervezo-profil {
    flex-direction: column;
    text-align: center;
  }
  
  .statisztika-grid {
    grid-template-columns: 1fr;
  }
  
  .hiba-muveletek {
    flex-direction: column;
  }
  
  .komment-fejlec {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }
}

@media (max-width: 480px) {
  .hero-cim {
    font-size: 1.75rem;
  }
  
  .info-blokk,
  .info-kartya,
  .komment-szekcio {
    padding: 1.5rem;
  }
  
  .datum-elem {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  
  .btn-valasz {
    flex-direction: column;
    text-align: center;
  }
  
  .valasz-tartalom {
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

.esemeny-tartalom {
  animation: fadeIn 0.5s ease-out;
}

.info-blokk,
.info-kartya,
.komment-szekcio {
  animation: fadeIn 0.5s ease-out;
  animation-fill-mode: both;
}

.info-blokk:nth-child(1) { animation-delay: 0.1s; }
.info-blokk:nth-child(2) { animation-delay: 0.2s; }
.info-blokk:nth-child(3) { animation-delay: 0.3s; }
.info-kartya:nth-child(1) { animation-delay: 0.15s; }
.info-kartya:nth-child(2) { animation-delay: 0.25s; }
.info-kartya:nth-child(3) { animation-delay: 0.35s; }
</style>