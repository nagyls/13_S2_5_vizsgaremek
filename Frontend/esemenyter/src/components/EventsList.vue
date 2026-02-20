<template>
  <div class="esemenyek-oldal">
    <header class="esemenyek-fejlec">
      <div class="kontener">
        <div class="fejlec-tartalom">
          <button class="vissza-gomb" @click="$router.push('/user-dashboard')">
            <i class='bx bx-arrow-back'></i> Főoldal
          </button>
          
          <h1><i class='bx bx-calendar'></i> Események</h1>
          
          <div class="felhasznalo-info" v-if="aktualisFelhasznalo">
            <span>{{ aktualisFelhasznalo.nev }}</span>
          </div>
        </div>
      </div>
    </header>

    <main class="esemenyek-tartalom">
      <div class="kontener">
        <div class="szurok-resz">
          <div class="szuro-sor">
            <div class="szuro-csoport">
              <label><i class='bx bx-filter-alt'></i> Típus:</label>
              <select v-model="szurok.tipus" @change="esemenyekBetoltese">
                <option value="">Összes</option>
                <option value="local">Helyi</option>
                <option value="global">Globális</option>
              </select>
            </div>
            
            <div class="szuro-csoport">
              <label><i class='bx bx-calendar'></i> Állapot:</label>
              <select v-model="szurok.allapot" @change="esemenyekBetoltese">
                <option value="">Összes</option>
                <option value="open">Aktív</option>
                <option value="closed">Lezárt</option>
              </select>
            </div>
            
            <div class="szuro-csoport">
              <label><i class='bx bx-sort'></i> Rendezés:</label>
              <select v-model="szurok.rendezes" @change="esemenyekBetoltese">
                <option value="newest">Legújabb</option>
                <option value="oldest">Legrégebbi</option>
                <option value="start_date">Kezdés dátuma</option>
              </select>
            </div>
          </div>
        </div>

        <div class="esemeny-lista">
          <div v-if="betoltesKozben" class="betoltes">
            <i class='bx bx-loader-circle bx-spin'></i>
            <p>Események betöltése...</p>
          </div>
          
          <div v-else-if="esemenyek.length === 0" class="nincs-esemeny">
            <i class='bx bx-calendar-x'></i>
            <h3>Nincsenek események</h3>
            <p>Még nem hoztak létre eseményeket az iskoládban.</p>
          </div>
          
          <div v-else>
            <div v-for="esemeny in esemenyek" :key="esemeny.id" class="esemeny-kartya">
              <div class="esemeny-fejlec">
                <div class="esemeny-tipus" :class="esemeny.type">
                  <i class='bx bx-building' v-if="esemeny.type === 'local'"></i>
                  <i class='bx bx-world' v-else></i>
                  {{ esemeny.type === 'local' ? 'Helyi' : 'Globális' }}
                </div>
                <div class="esemeny-allapot" :class="esemeny.status">
                  {{ esemeny.status === 'open' ? 'Aktív' : 'Lezárva' }}
                </div>
              </div>
              
              <div class="esemeny-torzse">
                <h3>{{ esemeny.title }}</h3>
                <p class="esemeny-leiras">{{ esemeny.description }}</p>
                
                <div class="esemeny-meta">
                  <div class="meta-elem">
                    <i class='bx bx-calendar'></i>
                    <span>{{ formatDatum(esemeny.start_date) }}</span>
                  </div>
                  <div class="meta-elem">
                    <i class='bx bx-user'></i>
                    <span>{{ esemeny.creator_name }}</span>
                  </div>
                  <div class="meta-elem">
                    <i class='bx bx-message-square-detail'></i>
                    <span>{{ esemeny.comment_count || 0 }} hozzászólás</span>
                  </div>
                </div>
              </div>
              
              <div class="esemeny-lablec">
                <router-link 
                  :to="`/esemenyek/${esemeny.id}`" 
                  class="gomb gomb-primary"
                >
                  <i class='bx bx-show'></i> Részletek
                </router-link>

                <div class="esemeny-statisztika">
                  <span class="stat">
                    <i class='bx bx-user-check'></i> {{ esemeny.participants || 0 }}
                  </span>
                  <span class="stat">
                    <i class='bx bx-star'></i> {{ esemeny.favorites || 0 }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div v-if="aktualisFelhasznalo && (aktualisFelhasznalo.role === 'teacher' || aktualisFelhasznalo.role === 'admin')" 
             class="uj-esemeny-gomb">
          <router-link to="/event-creator" class="gomb gomb-siker">
            <i class='bx bx-plus'></i> Új esemény létrehozása
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
      esemenyek: [],                // Események listája
      betoltesKozben: true,          // Betöltés állapota
      aktualisFelhasznalo: null,     // Bejelentkezett felhasználó
      szurok: {
        tipus: '',                   // 'local' vagy 'global' vagy ''
        allapot: '',                 // 'open' vagy 'closed' vagy ''
        rendezes: 'newest'           // 'newest', 'oldest', 'start_date'
      }
    }
  },
  
  async created() {
    await this.betoltFelhasznalot();   // Felhasználó adatok betöltése
    await this.esemenyekBetoltese();   // Események betöltése
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
        
        // 1. Ellenőrizzük, hogy van-e bejelentkezett felhasználó
        if (!this.aktualisFelhasznalo) {
          this.esemenyek = [];
          return;
        }
        
        // 2. API hívás az eseményekért a szűrőkkel
        this.esemenyek = await this.apiEsemenyekLekerese();
        
      } catch (hiba) {
        console.error('Hiba az események betöltésekor:', hiba);
        this.esemenyek = [];
      } finally {
        this.betoltesKozben = false;
      }
    },
    
    async apiEsemenyekLekerese() {
      return [
        {
          id: 1,
          title: 'Tavaszi kirándulás',
          description: 'Éves tavaszi kirándulás a természetben',
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
          description: 'Országos tudományos verseny',
          type: 'global',
          status: 'open',
          start_date: new Date(Date.now() + 86400000).toISOString(),
          creator_name: 'Tanár Béla',
          comment_count: 3,
          participants: 45,
          favorites: 18
        }
      ].filter(esemeny => {
        if (this.szurok.tipus && esemeny.type !== this.szurok.tipus) {
          return false;
        }
        if (this.szurok.allapot && esemeny.status !== this.szurok.allapot) {
          return false;
        }
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
    
    /**
     * Dátum formázása magyar nyelven
     * @param {string} datumString - ISO dátum string
     * @returns {string} - Rövid dátum (pl: "márc. 15.")
     */
    formatDatum(datumString) {
      if (!datumString) return '';
      const datum = new Date(datumString);
      return datum.toLocaleDateString('hu-HU', {
        month: 'short',
        day: 'numeric'
      });
    }
  }
}
</script>

<style scoped>
.esemenyek-oldal {
  box-sizing: border-box;
  font-family: "Poppins", sans-serif;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  width: 100vw;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.esemenyek-fejlec {
  background: rgba(255, 255, 255, 0.9);
  padding: 15px 0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.kontener {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.fejlec-tartalom {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.vissza-gomb {
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

.vissza-gomb:hover {
  background: #667eea;
  color: white;
  transform: translateX(-5px);
}

.fejlec-tartalom h1 {
  margin: 0;
  color: #333;
  font-size: 24px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.fejlec-tartalom h1 i {
  color: #667eea;
}

.felhasznalo-info {
  background: #f8f9fa;
  padding: 8px 16px;
  border-radius: 20px;
  font-weight: 500;
  color: #333;
}

.esemenyek-tartalom {
  padding: 30px 0;
}

.szurok-resz {
  background: white;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

.szuro-sor {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
}

.szuro-csoport {
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex: 1;
  min-width: 200px;
}

.szuro-csoport label {
  color: #333;
  font-weight: 500;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 6px;
}

.szuro-csoport select {
  padding: 10px;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 14px;
  background: white;
  cursor: pointer;
  transition: border-color 0.3s;
}

.szuro-csoport select:focus {
  outline: none;
  border-color: #667eea;
}

.esemeny-lista {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.esemeny-kartya {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0,0,0,0.05);
  transition: all 0.3s;
}

.esemeny-kartya:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.esemeny-fejlec {
  display: flex;
  justify-content: space-between;
  padding: 12px 20px;
  background: #f8f9fa;
  border-bottom: 1px solid #e9ecef;
}

.esemeny-tipus {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
  display: inline-flex;
  align-items: center;
  gap: 5px;
}

.esemeny-tipus.local {
  background: #e3f2fd;
  color: #1565c0;
}

.esemeny-tipus.global {
  background: #f3e5f5;
  color: #7b1fa2;
}

.esemeny-allapot {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
}

.esemeny-allapot.open {
  background: #e8f5e9;
  color: #2e7d32;
}

.esemeny-allapot.closed {
  background: #ffebee;
  color: #c62828;
}

.esemeny-torzse {
  padding: 20px;
}

.esemeny-torzse h3 {
  margin: 0 0 10px 0;
  color: #333;
  font-size: 18px;
}

.esemeny-leiras {
  color: #666;
  margin-bottom: 15px;
  font-size: 14px;
  line-height: 1.5;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.esemeny-meta {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
}

.meta-elem {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #888;
  font-size: 13px;
}

.meta-elem i {
  font-size: 16px;
}

.esemeny-lablec {
  padding: 15px 20px;
  background: #f8f9fa;
  border-top: 1px solid #e9ecef;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.gomb {
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

.gomb-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.gomb-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.gomb-siker {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
}

.esemeny-statisztika {
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

.betoltes {
  text-align: center;
  padding: 40px;
  background: white;
  border-radius: 12px;
}

.betoltes i {
  font-size: 48px;
  color: #667eea;
  margin-bottom: 15px;
}

.betoltes p {
  color: #666;
}

.nincs-esemeny {
  text-align: center;
  padding: 60px 20px;
  background: white;
  border-radius: 12px;
}

.nincs-esemeny i {
  font-size: 60px;
  color: #ccc;
  margin-bottom: 20px;
}

.nincs-esemeny h3 {
  color: #666;
  margin-bottom: 10px;
}

.nincs-esemeny p {
  color: #888;
}

.uj-esemeny-gomb {
  text-align: center;
  margin-top: 30px;
}

.uj-esemeny-gomb .gomb {
  padding: 12px 24px;
  font-size: 16px;
}

/* reszponziv */
@media (max-width: 768px) {
  .fejlec-tartalom {
    flex-direction: column;
    gap: 15px;
    text-align: center;
  }
  
  .szuro-sor {
    flex-direction: column;
    gap: 15px;
  }
  
  .szuro-csoport {
    min-width: 100%;
  }
  
  .esemeny-lablec {
    flex-direction: column;
    gap: 15px;
    align-items: stretch;
  }
  
  .esemeny-lablec .gomb {
    width: 100%;
    justify-content: center;
  }
  
  .esemeny-statisztika {
    justify-content: center;
  }
}
</style>