<template>
  <div class="esemenyek-oldal">
    <header class="esemenyek-fejlec">
      <div class="kontener">
        <div class="fejlec-tartalom">
          <button class="vissza-gomb" @click="$router.push('/user-dashboard')">
            <i class='bx bx-arrow-back'></i>
            <span>Főoldal</span>
          </button>
          
          <div class="logo-cim">
            <i class='bx bx-calendar-star'></i>
            <h1>Események</h1>
          </div>
          
          <div class="felhasznalo-info" v-if="aktualisFelhasznalo">
            <div class="avatar-kor">
              <span>{{ felhasznaloInicialek }}</span>
            </div>
            <div class="felhasznalo-adatok">
              <span class="nev">{{ aktualisFelhasznalo.nev }}</span>
              <span class="szerep" :class="aktualisFelhasznalo.role">
                {{ szerepMegjelenites }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </header>

    <main class="esemenyek-tartalom">
      <div class="kontener">
        <!-- Hero szekció (mint az EventDetails-ben) -->
        <div class="hero-szekcio">
          <div class="hero-overlay"></div>
          <div class="hero-tartalom">
            <div class="hero-cimke">
              <span class="cimke">
                <i class='bx bx-calendar'></i>
                {{ esemenyek.length }} esemény
              </span>
              <span class="cimke" :class="{ 'open': aktivEsemenyekSzama > 0 }">
                <i class='bx bx-calendar-event'></i>
                {{ aktivEsemenyekSzama }} aktív
              </span>
            </div>
            <h1 class="hero-cim">Fedezd fel az eseményeket</h1>
            <p class="hero-leiras">Csatlakozz iskolád eseményeihez, vagy fedezz fel globális programokat.</p>
          </div>
        </div>

        <!-- Statisztikai kártyák -->
        <div class="statisztika-kartyak">
          <div class="stat-kartya">
            <div class="stat-ikon">
              <i class='bx bx-calendar-check'></i>
            </div>
            <div class="stat-adat">
              <span class="stat-szam">{{ esemenyek.length }}</span>
              <span class="stat-cimke">Összes esemény</span>
            </div>
          </div>
          <div class="stat-kartya">
            <div class="stat-ikon">
              <i class='bx bx-time'></i>
            </div>
            <div class="stat-adat">
              <span class="stat-szam">{{ aktivEsemenyekSzama }}</span>
              <span class="stat-cimke">Aktív esemény</span>
            </div>
          </div>
          <div class="stat-kartya">
            <div class="stat-ikon">
              <i class='bx bx-group'></i>
            </div>
            <div class="stat-adat">
              <span class="stat-szam">{{ osszesResztvevo }}</span>
              <span class="stat-cimke">Összes résztvevő</span>
            </div>
          </div>
        </div>

        <!-- Szűrők és keresés -->
        <div class="szurok-resz">
          <div class="szuro-fejlec">
            <h3><i class='bx bx-filter-alt'></i> Szűrők és rendezés</h3>
            <button v-if="vannakAktivSzurok" class="torles-gomb" @click="szurokTorlese">
              <i class='bx bx-reset'></i> Szűrők törlése
            </button>
          </div>
          
          <div class="szuro-sor">
            <div class="szuro-csoport">
              <label><i class='bx bx-world'></i> Típus:</label>
              <div class="chip-kontener">
                <button 
                  class="chip" 
                  :class="{ 'aktiv': szurok.tipus === '' }"
                  @click="szurok.tipus = ''; esemenyekBetoltese()"
                >
                  Összes
                </button>
                <button 
                  class="chip" 
                  :class="{ 'aktiv': szurok.tipus === 'local' }"
                  @click="szurok.tipus = 'local'; esemenyekBetoltese()"
                >
                  <i class='bx bx-building'></i> Helyi
                </button>
                <button 
                  class="chip" 
                  :class="{ 'aktiv': szurok.tipus === 'global' }"
                  @click="szurok.tipus = 'global'; esemenyekBetoltese()"
                >
                  <i class='bx bx-world'></i> Globális
                </button>
              </div>
            </div>
            
            <div class="szuro-csoport">
              <label><i class='bx bx-calendar'></i> Állapot:</label>
              <div class="chip-kontener">
                <button 
                  class="chip" 
                  :class="{ 'aktiv': szurok.allapot === '' }"
                  @click="szurok.allapot = ''; esemenyekBetoltese()"
                >
                  Összes
                </button>
                <button 
                  class="chip" 
                  :class="{ 'aktiv': szurok.allapot === 'open' }"
                  @click="szurok.allapot = 'open'; esemenyekBetoltese()"
                >
                  <i class='bx bx-check-circle'></i> Aktív
                </button>
                <button 
                  class="chip" 
                  :class="{ 'aktiv': szurok.allapot === 'closed' }"
                  @click="szurok.allapot = 'closed'; esemenyekBetoltese()"
                >
                  <i class='bx bx-x-circle'></i> Lezárt
                </button>
              </div>
            </div>
            
            <div class="szuro-csoport rendezes">
              <label><i class='bx bx-sort'></i> Rendezés:</label>
              <div class="rendezes-gombok">
                <button 
                  class="rendezes-gomb" 
                  :class="{ 'aktiv': szurok.rendezes === 'newest' }"
                  @click="szurok.rendezes = 'newest'; esemenyekBetoltese()"
                >
                  <i class='bx bx-sort-down'></i> Legújabb
                </button>
                <button 
                  class="rendezes-gomb" 
                  :class="{ 'aktiv': szurok.rendezes === 'oldest' }"
                  @click="szurok.rendezes = 'oldest'; esemenyekBetoltese()"
                >
                  <i class='bx bx-sort-up'></i> Legrégebbi
                </button>
                <button 
                  class="rendezes-gomb" 
                  :class="{ 'aktiv': szurok.rendezes === 'start_date' }"
                  @click="szurok.rendezes = 'start_date'; esemenyekBetoltese()"
                >
                  <i class='bx bx-calendar'></i> Kezdés
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Események lista -->
        <div class="esemeny-lista">
          <div v-if="betoltesKozben" class="allapot-kartya betoltes">
            <div class="loader">
              <div class="spinner"></div>
            </div>
            <h3>Események betöltése...</h3>
            <p>Kérlek várj, amíg betöltjük az eseményeket</p>
          </div>
          
          <div v-else-if="esemenyek.length === 0" class="allapot-kartya nincs-esemeny">
            <div class="ures-animacio">
              <i class='bx bx-calendar-x'></i>
            </div>
            <h3>Nincsenek események</h3>
            <p>Még nem hoztak létre eseményeket az iskoládban.</p>
            <router-link v-if="létrehozhatEsemenyt" to="/event-creator" class="btn btn-elsodleges">
              <i class='bx bx-plus'></i> Első esemény létrehozása
            </router-link>
          </div>
          
          <div v-else class="esemeny-grid">
            <div v-for="esemeny in esemenyek" :key="esemeny.id" class="esemeny-kartya">
              <div class="kartya-fejlec">
                <div class="tipus-cimke" :class="esemeny.type">
                  <i class='bx bx-building' v-if="esemeny.type === 'local'"></i>
                  <i class='bx bx-world' v-else></i>
                  <span>{{ esemeny.type === 'local' ? 'Helyi' : 'Globális' }}</span>
                </div>
                <div class="allapot-cimke" :class="esemeny.status">
                  <span class="pont"></span>
                  {{ esemeny.status === 'open' ? 'Aktív' : 'Lezárva' }}
                </div>
              </div>
              
              <div class="kartya-tartalom">
                <h3>{{ esemeny.title }}</h3>
                <p class="leiras">{{ esemeny.description }}</p>
                
                <div class="meta-info">
                  <div class="meta-sor">
                    <i class='bx bx-calendar'></i>
                    <span>{{ formatDatum(esemeny.start_date) }}</span>
                  </div>
                  <div class="meta-sor">
                    <i class='bx bx-user'></i>
                    <span>{{ esemeny.creator_name }}</span>
                  </div>
                </div>
              </div>
              
              <div class="kartya-lablec">
                <div class="statisztika">
                  <div class="stat-item" title="Résztvevők">
                    <i class='bx bx-user-check'></i>
                    <span>{{ esemeny.participants || 0 }}</span>
                  </div>
                  <div class="stat-item" title="Kedvencek">
                    <i class='bx bx-star'></i>
                    <span>{{ esemeny.favorites || 0 }}</span>
                  </div>
                  <div class="stat-item" title="Hozzászólások">
                    <i class='bx bx-message-square-detail'></i>
                    <span>{{ esemeny.comment_count || 0 }}</span>
                  </div>
                </div>
                
                <router-link 
                  :to="`/esemenyek/${esemeny.id}`" 
                  class="reszletek-gomb"
                >
                  <span>Részletek</span>
                  <i class='bx bx-right-arrow-alt'></i>
                </router-link>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Új esemény gomb (csak tanár/admin) -->
        <div v-if="létrehozhatEsemenyt" class="uj-esemeny-gomb">
          <router-link to="/event-creator" class="btn btn-siker">
            <i class='bx bx-plus-circle'></i> Új esemény létrehozása
          </router-link>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
export default {
  name: 'EsemenyekLista',
  
  data() {
    return {
      esemenyek: [],
      betoltesKozben: true,
      aktualisFelhasznalo: null,
      szurok: {
        tipus: '',
        allapot: '',
        rendezes: 'newest'
      }
    }
  },
  
  computed: {
    felhasznaloInicialek() {
      if (!this.aktualisFelhasznalo?.nev) return '?';
      return this.aktualisFelhasznalo.nev
        .split(' ')
        .map(szo => szo[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
    },
    
    szerepMegjelenites() {
      const szerepek = {
        'student': 'Diák',
        'teacher': 'Tanár',
        'admin': 'Admin'
      };
      return szerepek[this.aktualisFelhasznalo?.role] || 'Vendég';
    },
    
    aktivEsemenyekSzama() {
      return this.esemenyek.filter(e => e.status === 'open').length;
    },
    
    osszesResztvevo() {
      return this.esemenyek.reduce((acc, e) => acc + (e.participants || 0), 0);
    },
    
    létrehozhatEsemenyt() {
      return this.aktualisFelhasznalo && 
             (this.aktualisFelhasznalo.role === 'teacher' || 
              this.aktualisFelhasznalo.role === 'admin');
    },
    
    vannakAktivSzurok() {
      return this.szurok.tipus !== '' || this.szurok.allapot !== '';
    }
  },
  
  async created() {
    await this.betoltFelhasznalot();
    await this.esemenyekBetoltese();
  },
  
  methods: {
    async betoltFelhasznalot() {
      try {
        const mentettFelhasznalo = localStorage.getItem('esemenyter_user');
        if (mentettFelhasznalo) {
          this.aktualisFelhasznalo = JSON.parse(mentettFelhasznalo);
        }
      } catch (hiba) {
        console.error('Hiba a felhasználó betöltésekor:', hiba);
      }
    },
  
    async esemenyekBetoltese() {
      try {
        this.betoltesKozben = true;
        
        if (!this.aktualisFelhasznalo) {
          this.esemenyek = [];
          return;
        }
        
        this.esemenyek = await this.apiEsemenyekLekerese();
        
      } catch (hiba) {
        console.error('Hiba az események betöltésekor:', hiba);
        this.esemenyek = [];
      } finally {
        this.betoltesKozben = false;
      }
    },
    
    async apiEsemenyekLekerese() {
      // Demo adatok
      const demoEsemenyek = [
        {
          id: 1,
          title: 'Tavaszi kirándulás',
          description: 'Éves tavaszi kirándulás a természetben. Gyülekező 8:00-kor az iskola előtt.',
          type: 'local',
          status: 'open',
          start_date: new Date().toISOString(),
          creator_name: 'Admin User',
          comment_count: 5,
          participants: 25,
          favorites: 12
        },
        {
          id: 2,
          title: 'Verseny a tudásért',
          description: 'Országos tudományos verseny középiskolásoknak. Több kategóriában lehet nevezni.',
          type: 'global',
          status: 'open',
          start_date: new Date(Date.now() + 86400000).toISOString(),
          creator_name: 'Tanár Béla',
          comment_count: 3,
          participants: 45,
          favorites: 18
        }
      ];
      
      return demoEsemenyek.filter(esemeny => {
        if (this.szurok.tipus && esemeny.type !== this.szurok.tipus) return false;
        if (this.szurok.allapot && esemeny.status !== this.szurok.allapot) return false;
        return true;
      }).sort((a, b) => {
        switch (this.szurok.rendezes) {
          case 'oldest':
            return new Date(a.start_date) - new Date(b.start_date);
          case 'start_date':
            return new Date(a.start_date) - new Date(b.start_date);
          case 'newest':
          default:
            return new Date(b.start_date) - new Date(a.start_date);
        }
      });
    },
    
    formatDatum(datumString) {
      if (!datumString) return '';
      const datum = new Date(datumString);
      return datum.toLocaleDateString('hu-HU', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    },
    
    szurokTorlese() {
      this.szurok.tipus = '';
      this.szurok.allapot = '';
      this.esemenyekBetoltese();
    }
  }
}
</script>

<style scoped>
/* Alap stílusok (EventDetails-ből átvéve) */
.esemenyek-oldal {
  min-width: 100vw;
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

.kontener {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
}

/* Fejléc (EventDetails-ből) */
.esemenyek-fejlec {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  padding: 16px 0;
  position: relative;
  top: 0;
  z-index: 100;
}

.fejlec-tartalom {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
}

.vissza-gomb {
  display: flex;
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

.vissza-gomb:hover {
  transform: translateX(-5px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  background: white;
}

.logo-cim {
  display: flex;
  align-items: center;
  gap: 12px;
}

.logo-cim i {
  font-size: 32px;
  color: #667eea;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.logo-cim h1 {
  margin: 0;
  font-size: 28px;
  font-weight: 700;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.felhasznalo-info {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 6px 12px 6px 6px;
  background: white;
  border-radius: 50px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.avatar-kor {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 16px;
}

.felhasznalo-adatok {
  display: flex;
  flex-direction: column;
}

.felhasznalo-adatok .nev {
  font-weight: 600;
  color: #333;
  font-size: 14px;
}

.felhasznalo-adatok .szerep {
  font-size: 12px;
  color: #666;
}

.szerep.student { color: #10b981; }
.szerep.teacher { color: #f97316; }
.szerep.admin { color: #8b5cf6; }

/* Hero szekció (EventDetails-ből) */
.hero-szekcio {
  position: relative;
  border-radius: 32px;
  overflow: hidden;
  margin-bottom: 2rem;
  background: linear-gradient(45deg, #1a202c, #2d3748);
  min-height: 300px;
  display: flex;
  align-items: center;
}

.hero-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(to right, rgba(0,0,0,0.8), rgba(0,0,0,0.4));
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
  background: rgba(255, 255, 255, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: white;
}

.cimke.open {
  background: rgba(72, 187, 120, 0.2);
  border-color: rgba(72, 187, 120, 0.4);
}

.hero-cim {
  font-size: 3rem;
  font-weight: 800;
  line-height: 1.2;
  margin-bottom: 1rem;
}

.hero-leiras {
  font-size: 1.25rem;
  opacity: 0.9;
  max-width: 600px;
}

/* Statisztika kártyák (EventDetails-ből) */
.statisztika-kartyak {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-kartya {
  background: white;
  border-radius: 24px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  transition: all 0.3s;
}

.stat-kartya:hover {
  transform: translateY(-5px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.stat-ikon {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #667eea20, #764ba220);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-ikon i {
  font-size: 2rem;
  color: #667eea;
}

.stat-adat {
  display: flex;
  flex-direction: column;
}

.stat-szam {
  font-size: 2rem;
  font-weight: 700;
  color: #1a202c;
  line-height: 1.2;
}

.stat-cimke {
  font-size: 0.875rem;
  color: #718096;
}

/* Szűrők rész (EventDetails stílusban) */
.szurok-resz {
  background: white;
  border-radius: 24px;
  padding: 2rem;
  margin-bottom: 2rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.szuro-fejlec {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  padding-bottom: 1.5rem;
  border-bottom: 2px solid #f0f0f0;
}

.szuro-fejlec h3 {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 1.25rem;
  font-weight: 700;
  color: #1a202c;
  margin: 0;
}

.szuro-fejlec h3 i {
  color: #667eea;
}

.torles-gomb {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: #ffebee;
  border: none;
  border-radius: 50px;
  color: #ef4444;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s;
}

.torles-gomb:hover {
  background: #ef4444;
  color: white;
}

.szuro-sor {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.szuro-csoport label {
  display: block;
  margin-bottom: 0.75rem;
  color: #4a5568;
  font-size: 0.875rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.chip-kontener {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.chip {
  padding: 0.625rem 1.25rem;
  background: #f7fafc;
  border: 2px solid transparent;
  border-radius: 50px;
  color: #4a5568;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s;
}

.chip:hover {
  background: #edf2f7;
  transform: translateY(-2px);
}

.chip.aktiv {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
}

.rendezes-gombok {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.rendezes-gomb {
  padding: 0.625rem 1.25rem;
  background: #f7fafc;
  border: 2px solid transparent;
  border-radius: 50px;
  color: #4a5568;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s;
}

.rendezes-gomb:hover {
  background: #edf2f7;
  transform: translateY(-2px);
}

.rendezes-gomb.aktiv {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
}

/* Események grid */
.esemeny-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 1.5rem;
  margin-top: 2rem;
}

/* Esemény kártya (EventDetails stílusban) */
.esemeny-kartya {
  background: white;
  border-radius: 24px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  transition: all 0.3s;
  display: flex;
  flex-direction: column;
}

.esemeny-kartya:hover {
  transform: translateY(-10px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.kartya-fejlec {
  padding: 1rem 1.5rem;
  background: #f7fafc;
  border-bottom: 2px solid #f0f0f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.tipus-cimke {
  padding: 0.375rem 0.875rem;
  border-radius: 50px;
  font-size: 0.75rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.tipus-cimke.local {
  background: #e3f2fd;
  color: #1565c0;
}

.tipus-cimke.global {
  background: #f3e5f5;
  color: #7b1fa2;
}

.allapot-cimke {
  padding: 0.375rem 0.875rem;
  border-radius: 50px;
  font-size: 0.75rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.allapot-cimke .pont {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  display: inline-block;
}

.allapot-cimke.open {
  background: #e8f5e9;
  color: #2e7d32;
}

.allapot-cimke.open .pont {
  background: #2e7d32;
  box-shadow: 0 0 0 2px rgba(46, 125, 50, 0.2);
}

.allapot-cimke.closed {
  background: #ffebee;
  color: #c62828;
}

.allapot-cimke.closed .pont {
  background: #c62828;
  box-shadow: 0 0 0 2px rgba(198, 40, 40, 0.2);
}

.kartya-tartalom {
  padding: 1.5rem;
  flex: 1;
}

.kartya-tartalom h3 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1a202c;
  margin-bottom: 0.75rem;
}

.leiras {
  color: #718096;
  font-size: 0.875rem;
  line-height: 1.6;
  margin-bottom: 1.5rem;
  display: -webkit-box;
  line-clamp: 3;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.meta-info {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  padding-top: 1rem;
  border-top: 2px solid #f0f0f0;
}

.meta-sor {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: #a0aec0;
  font-size: 0.875rem;
}

.meta-sor i {
  font-size: 1rem;
  color: #667eea;
}

.kartya-lablec {
  padding: 1rem 1.5rem;
  background: #f7fafc;
  border-top: 2px solid #f0f0f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.statisztika {
  display: flex;
  gap: 1rem;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  color: #718096;
  font-size: 0.75rem;
}

.stat-item i {
  font-size: 1rem;
  color: #667eea;
}

.reszletek-gomb {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: linear-gradient(135deg, #667eea, #764ba2);
  border: none;
  border-radius: 50px;
  color: white;
  font-size: 0.75rem;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s;
}

.reszletek-gomb:hover {
  transform: translateX(5px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

/* Állapot kártyák (EventDetails-ből) */
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

.ures-animacio i {
  font-size: 4rem;
  color: #cbd5e0;
  margin-bottom: 1.5rem;
}

/* Gombok (EventDetails-ből) */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1.75rem;
  border: none;
  border-radius: 50px;
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
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

.btn-siker {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
}

.btn-siker:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4);
}

/* Új esemény gomb */
.uj-esemeny-gomb {
  text-align: center;
  margin-top: 3rem;
}

.uj-esemeny-gomb .btn {
  padding: 1rem 2rem;
  font-size: 1.125rem;
}

/* Reszponzív */
@media (max-width: 1024px) {
  .hero-cim {
    font-size: 2.5rem;
  }
}

@media (max-width: 768px) {
  .kontener {
    padding: 1rem;
  }
  
  .fejlec-tartalom {
    flex-direction: column;
    text-align: center;
  }
  
  .vissza-gomb {
    width: 100%;
    justify-content: center;
  }
  
  .hero-tartalom {
    padding: 2rem;
  }
  
  .hero-cim {
    font-size: 2rem;
  }
  
  .hero-cimke {
    flex-direction: column;
  }
  
  .cimke {
    width: fit-content;
  }
  
  .statisztika-kartyak {
    grid-template-columns: 1fr;
  }
  
  .esemeny-grid {
    grid-template-columns: 1fr;
  }
  
  .kartya-lablec {
    flex-direction: column;
    gap: 1rem;
  }
  
  .statisztika {
    justify-content: center;
  }
  
  .reszletek-gomb {
    width: 100%;
    justify-content: center;
  }
  
  .szuro-fejlec {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }
  
  .rendezes-gombok {
    flex-direction: column;
  }
  
  .rendezes-gomb {
    width: 100%;
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .hero-cim {
    font-size: 1.75rem;
  }
  
  .chip-kontener {
    flex-direction: column;
  }
  
  .chip {
    width: 100%;
    justify-content: center;
  }
}
</style>