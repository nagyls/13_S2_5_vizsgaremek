<template>
  <div class="esemeny-keszito-oldal">
    <button class="vissza-gomb" @click="$router.back()">
      <i class='bx bx-arrow-back'></i> Vissza
    </button>

    <div class="esemeny-keszito-wrapper">
      <div class="keszito-fejlec">
        <h1><i class='bx bx-calendar-plus'></i> Új esemény létrehozása</h1>
        <p class="alcim">{{ getSzerepUzenet }}</p>
      </div>

      <div v-if="!vanJogosultsag" class="nincs-jogosultsag">
        <div class="jogosultsag-hiba">
          <i class='bx bx-shield-x'></i>
          <h3>Nincs jogosultságod eseményt létrehozni!</h3>
          <p>Csak adminisztrátorok és osztályfőnökök hozhatnak létre eseményeket.</p>
          <router-link to="/mainpage" class="gomb gomb-primary">
            <i class='bx bx-home'></i> Vissza a főoldalra
          </router-link>
        </div>
      </div>

      <!-- JOGOSULT FELHASZNÁLÓ FORMJA -->
      <div v-else class="keszito-tartalom">
        <div class="lepteto-navigacio">
          <div class="lepesek">
            <div v-for="lepes in leptetok" :key="lepes.szam" 
                 class="lepes" 
                 :class="{ 'aktiv': aktualisLepes >= lepes.szam, 'befejezett': aktualisLepes > lepes.szam }">
              <span class="lepes-szam">{{ lepes.szam }}</span>
              <span class="lepes-cimke">{{ lepes.cimke }}</span>
            </div>
          </div>
        </div>

        <!-- 1.esemeny tipusa -->
        <div v-if="aktualisLepes === 1 && felhasznaloSzerep === 'admin'" class="form-resz">
          <h3><i class='bx bx-category'></i> 1. Esemény típusa</h3>
          <div class="tipus-valasztas">
            <label v-for="tipus in esemenyTipusok" :key="tipus.ertek" 
                   class="tipus-opcio" 
                   :class="{ 'kivalasztott': kivalasztottEsemenyTipus === tipus.ertek }">
              <input type="radio" v-model="kivalasztottEsemenyTipus" :value="tipus.ertek" hidden>
              <div class="opcio-tartalom">
                <i :class="tipus.ikon"></i>
                <h4>{{ tipus.cimke }}</h4>
                <p>{{ tipus.leiras }}</p>
              </div>
            </label>
          </div>
        </div>

        <!-- 2.celcsoport -->
        <div v-if="aktualisLepes === 2 && kellMutatniCelcsoportValasztast" class="form-resz">
          <h3><i class='bx bx-target-lock'></i> 2. Célcsoport kiválasztása</h3>
          
          <!-- helyi esemeny -->
          <div v-if="helyiEsemeny" class="celcsoport-info">
            <div class="info-doboz">
              <i class='bx bx-info-circle'></i>
              <p>Helyi eseményként automatikusan a saját intézményed lesz kiválasztva.</p>
            </div>
            <div class="kivalasztott-intezmeny">
              <i class='bx bx-check-circle'></i>
              <span>{{ felhasznaloIntezmeny.cim }}</span>
            </div>
          </div>

          <!-- globalis esemeny -->
          <div v-else class="celcsoport-valasztas">
            <div class="celcsoport-opciok">
              <label v-for="celcsoport in celcsoportMódok" :key="celcsoport.ertek"
                     class="celcsoport-opcio"
                     :class="{ 'kivalasztott': kivalasztottCelcsoportMod === celcsoport.ertek }">
                <input type="radio" v-model="kivalasztottCelcsoportMod" :value="celcsoport.ertek" hidden>
                <div class="opcio-tartalom">
                  <i :class="celcsoport.ikon"></i>
                  <h4>{{ celcsoport.cimke }}</h4>
                  <p>{{ celcsoport.leiras }}</p>
                </div>
              </label>
            </div>

            <div class="valasztas-tipp">
              {{ kivalasztottCelcsoportMod === 'intezmeny_lista' ? 'Válassz intézményeket' : 'Válassz szűrőket' }}
            </div>
          </div>
        </div>

        <!-- 3.osztalyok -->
        <div v-if="aktualisLepes === 3 && vanKivalasztottIntezmeny" class="form-resz">
          <h3><i class='bx bx-group'></i> 3. Osztályok kiválasztása</h3>
          <div class="valasztas-tipp">
            {{ kivalasztottOsztalyok.length }} osztály kiválasztva
          </div>
        </div>

        <!-- 4.esemeny adatai -->
        <div v-if="aktualisLepes === 4 && vanKivalasztottOsztaly" class="form-resz">
          <h3><i class='bx bx-edit'></i> 4. Esemény adatai</h3>
          <div class="esemeny-adatok-form">
            <div class="form-csoport">
              <label>Cím *</label>
              <input v-model="esemenyAdatok.cim" placeholder="Esemény címe" required>
            </div>
            <div class="form-csoport">
              <label>Leírás *</label>
              <textarea v-model="esemenyAdatok.leiras" placeholder="Részletes leírás" required></textarea>
            </div>
            <div class="form-sor">
              <div class="form-csoport">
                <label>Kezdés *</label>
                <input type="date" v-model="esemenyAdatok.kezdes_idopont" :min="maiNap" required>
              </div>
              <div class="form-csoport">
                <label>Befejezés *</label>
                <input type="date" v-model="esemenyAdatok.befejezes_idopont" :min="esemenyAdatok.kezdes_idopont || maiNap" required>
              </div>
            </div>
          </div>
        </div>

        <!-- 5.attekintes -->
        <div v-if="aktualisLepes === 5 && vanKivalasztottOsztaly" class="form-resz">
          <h3><i class='bx bx-check-circle'></i> 5. Áttekintés</h3>
          <div class="attekintes">
            <div class="osszegzes-elem">
              <strong>Típus:</strong> {{ getEsemenyTipusCimke(kivalasztottEsemenyTipus) }}
            </div>
            <div class="osszegzes-elem">
              <strong>Intézmények:</strong> {{ kivalasztottIntezmenyek.length }}
            </div>
            <div class="osszegzes-elem">
              <strong>Osztályok:</strong> {{ kivalasztottOsztalyok.length }}
            </div>
            <div class="osszegzes-elem">
              <strong>Cím:</strong> {{ esemenyAdatok.cim || '(nincs megadva)' }}
            </div>
          </div>
        </div>

        <div class="form-muveletek">
          <button @click="elozoLepes" :disabled="aktualisLepes === 1" class="gomb gomb-secondary">
            <i class='bx bx-chevron-left'></i> Előző
          </button>
          
          <button v-if="aktualisLepes < 5" @click="kovetkezoLepes" :disabled="!tovabblephetE" class="gomb gomb-primary">
            Következő <i class='bx bx-chevron-right'></i>
          </button>
          
          <button v-else @click="esemenyLetrehozasa" :disabled="!ervenyesEaForm || betoltesKozben" class="gomb gomb-siker">
            <i class='bx bx-check' v-if="!betoltesKozben"></i>
            <i class='bx bx-loader-circle bx-spin' v-else></i>
            {{ betoltesKozben ? 'Feldolgozás...' : 'Esemény létrehozása' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'EsemenyKeszito',
  
  data() {
    return {
      // FELHASZNÁLÓ ADATAI
      felhasznaloSzerep: 'admin',  // 'admin', 'teacher' vagy 'student'
      felhasznaloIntezmeny: { 
        azonosito: 1, 
        cim: 'Kossuth Lajos Gimnázium' 
      },
      
      // LÉPTETŐ ADATOK
      aktualisLepes: 1,           // Jelenlegi lépés száma
      betoltesKozben: false,       // Betöltés állapota
      maiNap: new Date().toISOString().split('T')[0], // Mai dátum YYYY-MM-DD formában
      
      // ESEMÉNY TÍPUS
      kivalasztottEsemenyTipus: 'local',  // 'local' vagy 'global'
      
      // CÉLCSOPORT BEÁLLÍTÁSOK
      kivalasztottCelcsoportMod: 'intezmeny_lista',  // 'intezmeny_lista' vagy 'teruleti_szures'
      kivalasztottIntezmenyek: [],  // Kiválasztott intézmények listája
      
      // OSZTÁLYOK
      kivalasztottOsztalyok: [],  // Kiválasztott osztályok listája
      
      // ESEMÉNY ADATOK
      esemenyAdatok: {
        cim: '',
        leiras: '',
        tartalom: '',
        kezdes_idopont: '',
        befejezes_idopont: ''
      },
      
      // KONFIGURÁCIÓK
      leptetok: [
        { szam: 1, cimke: 'Típus' },
        { szam: 2, cimke: 'Célcsoport' },
        { szam: 3, cimke: 'Osztályok' },
        { szam: 4, cimke: 'Adatok' },
        { szam: 5, cimke: 'Létrehozás' }
      ],
      
      esemenyTipusok: [
        { 
          ertek: 'local', 
          cimke: 'Helyi esemény', 
          ikon: 'bx bx-building', 
          leiras: 'Csak a saját intézményedben' 
        },
        { 
          ertek: 'global', 
          cimke: 'Globális esemény', 
          ikon: 'bx bx-world', 
          leiras: 'Több intézményben' 
        }
      ],
      
      celcsoportMódok: [
        { 
          ertek: 'intezmeny_lista', 
          cimke: 'Intézmény lista', 
          ikon: 'bx bx-list-ol', 
          leiras: 'Kézzel válaszd ki' 
        },
        { 
          ertek: 'teruleti_szures', 
          cimke: 'Területi szűrés', 
          ikon: 'bx bx-filter-alt', 
          leiras: 'Szűrés alapján' 
        }
      ]
    }
  },
  
  computed: {
    vanJogosultsag() {
      return this.felhasznaloSzerep === 'admin' || this.felhasznaloSzerep === 'teacher'
    },
    
    getSzerepUzenet() {
      if (this.felhasznaloSzerep === 'teacher') {
        return 'Osztályfőnökként helyi eseményt hozhatsz létre'
      }
      if (this.felhasznaloSzerep === 'admin') {
        return 'Adminisztrátorként helyi vagy globális eseményt hozhatsz létre'
      }
      return 'Nincs jogosultságod eseményt létrehozni'
    },
    
    helyiEsemeny() {
      return this.kivalasztottEsemenyTipus === 'local' || this.felhasznaloSzerep === 'teacher'
    },
    
    kellMutatniCelcsoportValasztast() {
      if (this.felhasznaloSzerep === 'teacher') return false
      return this.aktualisLepes >= 2
    },
    
    vanKivalasztottIntezmeny() {
      return this.kivalasztottIntezmenyek.length > 0 || this.helyiEsemeny
    },
    
    vanKivalasztottOsztaly() {
      return this.kivalasztottOsztalyok.length > 0 || this.aktualisLepes < 3
    },
    
    tovabblephetE() {
      switch (this.aktualisLepes) {
        case 1: return true  // Típus mindig választható
        case 2: return this.helyiEsemeny || this.kivalasztottIntezmenyek.length > 0
        case 3: return this.kivalasztottOsztalyok.length > 0 || true // DEMO
        case 4: return this.ellenorizEsemenyAdatokat()
        default: return true
      }
    },
    
    ervenyesEaForm() {
      return this.ellenorizEsemenyAdatokat() && this.aktualisLepes === 5
    }
  },
  
  watch: {
    //Ha az esemény típusa változik, automatikusan beállítjuk az intézményeket
    kivalasztottEsemenyTipus(ujErtek) {
      if (ujErtek === 'local') {
        // Helyi esemény
        this.kivalasztottIntezmenyek = [this.felhasznaloIntezmeny]
      } else {
        // Globális esemény
        this.kivalasztottIntezmenyek = []
      }
    }
  },
  
  created() {
    this.inicializalas()
  },
  
  methods: {
    /**
     * ALGORITMUS: Demó adatok inicializálása
     */
    inicializalas() {
      // Ha tanár, automatikusan helyi esemény
      if (this.felhasznaloSzerep === 'teacher') {
        this.kivalasztottEsemenyTipus = 'local'
        this.kivalasztottIntezmenyek = [this.felhasznaloIntezmeny]
      }
    },
    
    /**
     * Esemény típus címkéjének lekérdezése
     * @param {string} tipus - 'local' vagy 'global'
     * @returns {string} - A típus magyar neve
     */
    getEsemenyTipusCimke(tipus) {
      const tipusObjektum = this.esemenyTipusok.find(t => t.ertek === tipus)
      return tipusObjektum ? tipusObjektum.cimke : tipus
    },
    
    /**
     * Esemény adatok ellenőrzése
     * @returns {boolean} - Érvényesek-e az adatok
     */
    ellenorizEsemenyAdatokat() {
      const { cim, leiras, kezdes_idopont, befejezes_idopont } = this.esemenyAdatok
      
      return cim.trim() !== '' && 
             leiras.trim() !== '' && 
             kezdes_idopont !== '' && 
             befejezes_idopont !== '' &&
             new Date(kezdes_idopont) <= new Date(befejezes_idopont)
    },
    
    kovetkezoLepes() {
      if (this.aktualisLepes < 5 && this.tovabblephetE) {
        this.aktualisLepes++
        
        // DEMO: Automatikus kitöltés
        this.demóAdatokKitoltese()
      }
    },
    
    demóAdatokKitoltese() {
      // 3.osztályok
      if (this.aktualisLepes === 3) {
        this.kivalasztottOsztalyok = [1, 2, 3] // DEMO osztály azonosítók
      }
      
      // 4.esemény adatok
      if (this.aktualisLepes === 4 && !this.esemenyAdatok.cim) {
        this.esemenyAdatok = {
          cim: 'Tavaszi kirándulás',
          leiras: 'Éves tavaszi kirándulás a természetben',
          tartalom: 'Hozz magaddal kenyeret és vizet!',
          kezdes_idopont: this.maiNap,
          befejezes_idopont: this.getKovetkezoHetiDatum()
        }
      }
    },
    
    /**
     * Következő heti dátum generálása
     * @returns {string} - Dátum YYYY-MM-DD formátumban
     */
    getKovetkezoHetiDatum() {
      const datum = new Date()
      datum.setDate(datum.getDate() + 7)
      return datum.toISOString().split('T')[0]
    },
    
    elozoLepes() {
      if (this.aktualisLepes > 1) {
        this.aktualisLepes--
      }
    },
  
    async esemenyLetrehozasa() {
      // 1.Form ellenőrzés
      if (!this.ervenyesEaForm) {
        alert('Kérjük, töltsd ki az összes kötelező mezőt!')
        return
      }

      this.betoltesKozben = true

      try {
        // 2.Adatok összeállítása
        const adatCsomag = {
          type: this.kivalasztottEsemenyTipus,
          scope_mode: this.kivalasztottCelcsoportMod,
          establishment_ids: this.helyiEsemeny
            ? [this.felhasznaloIntezmeny.azonosito]
            : this.kivalasztottIntezmenyek.map(e => e.azonosito),
          class_ids: this.kivalasztottOsztalyok,
          title: this.esemenyAdatok.cim,
          description: this.esemenyAdatok.leiras,
          content: this.esemenyAdatok.tartalom,
          start_date: this.esemenyAdatok.kezdes_idopont,
          end_date: this.esemenyAdatok.befejezes_idopont,
        }

        // 3. LÉPÉS: API hívás
        await axios.post('http://127.0.0.1:8000/api/events', adatCsomag, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
            Accept: 'application/json'
          }
        })

        // 4.Sikeres válasz kezelése
        alert('✅ Esemény sikeresen létrehozva!')
        this.$router.push('/esemenyek')

      } catch (hiba) {
        console.error(hiba)

        if (hiba.response?.data?.message) {
          alert('Hiba: ' + hiba.response.data.message)
        } else {
          alert('Ismeretlen hiba történt')
        }
      } finally {
        this.betoltesKozben = false
      }
    }
  }
}
</script>

<style scoped>
.esemeny-keszito-oldal {
  background: linear-gradient(135deg, #8c8c8f 0%, #764ba2 100%);
  font-family: "Poppins", sans-serif;
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  box-sizing: border-box;
  width: 100vw;
}

.vissza-gomb {
  position: absolute;
  top: 20px;
  left: 20px;
  background: rgba(255, 255, 255, 0.9);
  border: none;
  padding: 10px 15px;
  border-radius: 50px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
  color: #333;
  z-index: 100;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transition: all 0.3s;
  max-width: 120px;
  white-space: nowrap;
}

.vissza-gomb:hover {
  background: white;
  transform: translateX(-5px);
}

.esemeny-keszito-wrapper {
  max-width: 900px;
  width: 100%;
  background: white;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  margin: 60px 20px 20px;
  box-sizing: border-box;
}

.keszito-fejlec {
  background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
  color: white;
  padding: 25px 30px;
  text-align: center;
  box-sizing: border-box;
}

.keszito-fejlec h1 {
  font-size: 28px;
  margin-bottom: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
}

.alcim {
  opacity: 0.9;
  font-size: 14px;
}

.keszito-tartalom {
  padding: 25px;
  box-sizing: border-box;
}

.nincs-jogosultsag {
  padding: 30px;
  text-align: center;
  box-sizing: border-box;
}

.jogosultsag-hiba {
  padding: 25px;
  background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
  border-radius: 15px;
  border-left: 5px solid #ef4444;
  box-sizing: border-box;
}

.jogosultsag-hiba i {
  font-size: 48px;
  color: #ef4444;
  margin-bottom: 15px;
}

.jogosultsag-hiba h3 {
  color: #dc2626;
  margin-bottom: 10px;
  font-size: 20px;
}

.jogosultsag-hiba p {
  color: #7f1d1d;
  margin-bottom: 20px;
  font-size: 15px;
}

.lepteto-navigacio {
  margin-bottom: 25px;
}

.lepesek {
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
  flex-wrap: wrap;
  gap: 10px;
}

.lepes {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 5px;
  position: relative;
  z-index: 2;
  flex: 1;
  min-width: 70px;
}

.lepes-szam {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #e5e7eb;
  color: #6b7280;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 16px;
  transition: all 0.3s;
}

.lepes.aktiv .lepes-szam {
  background: #4f46e5;
  color: white;
  transform: scale(1.1);
}

.lepes.befejezett .lepes-szam {
  background: #10b981;
  color: white;
}

.lepes-cimke {
  color: #6b7280;
  font-size: 12px;
  font-weight: 500;
  text-align: center;
  word-break: break-word;
  max-width: 80px;
}

.lepes.aktiv .lepes-cimke {
  color: #4f46e5;
  font-weight: 600;
}

.form-resz {
  margin-bottom: 25px;
  padding-bottom: 15px;
  border-bottom: 1px solid #e5e7eb;
  box-sizing: border-box;
}

.form-resz:last-child {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
}

.form-resz h3 {
  color: #1f2937;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 20px;
}

.form-resz h3 i {
  color: #4f46e5;
}

.tipus-valasztas, .celcsoport-opciok {
  display: grid;
  grid-template-columns: 1fr;
  gap: 15px;
  margin-top: 15px;
  box-sizing: border-box;
}

.tipus-opcio, .celcsoport-opcio {
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 15px;
  cursor: pointer;
  transition: all 0.3s;
  background: white;
  box-sizing: border-box;
}

.tipus-opcio:hover, .celcsoport-opcio:hover {
  border-color: #c7d2fe;
  transform: translateY(-2px);
}

.tipus-opcio.kivalasztott, .celcsoport-opcio.kivalasztott {
  border-color: #4f46e5;
  background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
}

.opcio-tartalom {
  text-align: center;
  box-sizing: border-box;
}

.opcio-tartalom i {
  font-size: 36px;
  color: #4f46e5;
  margin-bottom: 10px;
}

.opcio-tartalom h4 {
  color: #1f2937;
  margin-bottom: 5px;
  font-size: 18px;
}

.opcio-tartalom p {
  color: #6b7280;
  font-size: 13px;
  line-height: 1.4;
}

.celcsoport-info {
  background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
  border-radius: 12px;
  padding: 20px;
  margin-top: 15px;
  box-sizing: border-box;
}

.info-doboz {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 15px;
  padding: 15px;
  background: white;
  border-radius: 8px;
  border-left: 4px solid #0ea5e9;
  box-sizing: border-box;
}

.info-doboz i {
  color: #0ea5e9;
  font-size: 20px;
  flex-shrink: 0;
}

.info-doboz p {
  color: #0369a1;
  margin: 0;
  font-size: 14px;
  line-height: 1.4;
}

.kivalasztott-intezmeny {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 15px;
  background: white;
  border-radius: 8px;
  border: 2px solid #10b981;
  box-sizing: border-box;
}

.kivalasztott-intezmeny i {
  color: #10b981;
  font-size: 20px;
}

.kivalasztott-intezmeny span {
  font-weight: 600;
  color: #047857;
  font-size: 16px;
}

/* FORM ELEMEK */
.valasztas-tipp {
  padding: 15px;
  background: #f9fafb;
  border-radius: 8px;
  text-align: center;
  color: #6b7280;
  font-size: 14px;
  margin-top: 15px;
  box-sizing: border-box;
}

.esemeny-adatok-form {
  margin-top: 20px;
  box-sizing: border-box;
}

.form-csoport {
  margin-bottom: 20px;
  box-sizing: border-box;
}

.form-csoport label {
  display: block;
  margin-bottom: 8px;
  color: #374151;
  font-weight: 600;
  font-size: 14px;
}

.form-csoport input, .form-csoport textarea {
  width: 100%;
  padding: 10px 12px;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  font-size: 15px;
  font-family: inherit;
  transition: all 0.3s;
  box-sizing: border-box;
}

.form-csoport input:focus, .form-csoport textarea:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.form-csoport textarea {
  min-height: 100px;
  resize: vertical;
}

.form-sor {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
  box-sizing: border-box;
}

.attekintes {
  background: #f9fafb;
  border-radius: 12px;
  padding: 15px;
  margin-top: 15px;
  box-sizing: border-box;
}

.osszegzes-elem {
  padding: 10px 0;
  border-bottom: 1px solid #e5e7eb;
  font-size: 15px;
  line-height: 1.4;
}

.osszegzes-elem:last-child {
  border-bottom: none;
}

.osszegzes-elem strong {
  color: #374151;
  margin-right: 8px;
}

/* gombok */
.form-muveletek {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  margin-top: 25px;
  padding-top: 15px;
  border-top: 2px solid #e5e7eb;
  box-sizing: border-box;
}

.gomb {
  padding: 10px 20px;
  border: none;
  border-radius: 10px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  transition: all 0.3s;
  text-decoration: none;
  box-sizing: border-box;
  flex: 1;
}

.gomb-primary {
  background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
  color: white;
}

.gomb-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 20px rgba(79, 70, 229, 0.2);
}

.gomb-secondary {
  background: #f1f5f9;
  color: #475569;
  border: 2px solid #e2e8f0;
}

.gomb-secondary:hover:not(:disabled) {
  background: #e2e8f0;
}

.gomb-siker {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
}

.gomb-siker:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);
}

.gomb:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none !important;
  box-shadow: none !important;
}

/* reszponziv */
@media (max-width: 768px) {
  .esemeny-keszito-oldal {
    padding: 15px;
    align-items: flex-start;
  }
  
  .esemeny-keszito-wrapper {
    margin: 50px 15px 15px;
    max-width: 100%;
  }
  
  .vissza-gomb {
    top: 15px;
    left: 15px;
    padding: 8px 12px;
    font-size: 14px;
    max-width: 110px;
  }
  
  .keszito-fejlec {
    padding: 20px;
  }
  
  .keszito-fejlec h1 {
    font-size: 22px;
    flex-direction: row;
    gap: 10px;
  }
  
  .alcim {
    font-size: 12px;
  }
  
  .keszito-tartalom {
    padding: 20px;
  }
  
  .lepesek {
    gap: 5px;
  }
  
  .lepes {
    min-width: 60px;
  }
  
  .lepes-szam {
    width: 32px;
    height: 32px;
    font-size: 14px;
  }
  
  .lepes-cimke {
    font-size: 11px;
    max-width: 70px;
  }
  
  .form-resz h3 {
    font-size: 18px;
  }
  
  .opcio-tartalom h4 {
    font-size: 16px;
  }
  
  .opcio-tartalom i {
    font-size: 32px;
  }
  
  .form-csoport label {
    font-size: 13px;
  }
  
  .form-csoport input, .form-csoport textarea {
    font-size: 14px;
    padding: 9px 11px;
  }
  
  .form-sor {
    grid-template-columns: 1fr;
    gap: 12px;
  }
  
  .gomb {
    padding: 9px 16px;
    font-size: 14px;
  }
  
  .form-muveletek {
    flex-direction: row;
    gap: 10px;
  }
  
  .jogosultsag-hiba {
    padding: 20px;
  }
  
  .jogosultsag-hiba i {
    font-size: 40px;
  }
  
  .jogosultsag-hiba h3 {
    font-size: 18px;
  }
}

@media (max-width: 480px) {
  .esemeny-keszito-oldal {
    padding: 10px;
  }
  
  .esemeny-keszito-wrapper {
    margin: 45px 10px 10px;
    border-radius: 15px;
  }
  
  .vissza-gomb {
    top: 10px;
    left: 10px;
    padding: 7px 10px;
    font-size: 13px;
    max-width: 100px;
  }
  
  .keszito-fejlec {
    padding: 15px;
  }
  
  .keszito-fejlec h1 {
    font-size: 18px;
    gap: 8px;
  }
  
  .alcim {
    font-size: 12px;
  }
  
  .keszito-tartalom {
    padding: 15px;
  }
  
  .lepes {
    min-width: 50px;
  }
  
  .lepes-szam {
    width: 28px;
    height: 28px;
    font-size: 13px;
  }
  
  .lepes-cimke {
    font-size: 10px;
    max-width: 60px;
  }
  
  .form-resz h3 {
    font-size: 16px;
    gap: 8px;
  }
  
  .form-resz h3 i {
    font-size: 18px;
  }
  
  .tipus-valasztas, .celcsoport-opciok {
    gap: 10px;
  }
  
  .tipus-opcio, .celcsoport-opcio {
    padding: 12px;
  }
  
  .opcio-tartalom i {
    font-size: 28px;
    margin-bottom: 8px;
  }
  
  .opcio-tartalom h4 {
    font-size: 15px;
    margin-bottom: 4px;
  }
  
  .opcio-tartalom p {
    font-size: 12px;
  }
  
  .celcsoport-info {
    padding: 15px;
  }
  
  .info-doboz, .kivalasztott-intezmeny {
    padding: 12px;
  }
  
  .valasztas-tipp {
    padding: 12px;
    font-size: 13px;
  }
  
  .form-muveletek {
    flex-direction: column;
    gap: 8px;
  }
  
  .gomb {
    width: 100%;
    padding: 10px;
  }
}

@media (min-width: 769px) {
  .tipus-valasztas, .celcsoport-opciok {
    grid-template-columns: 1fr 1fr;
  }
  
  .form-muveletek .gomb {
    flex: none;
    min-width: 140px;
  }
}

@media (min-width: 1024px) {
  .esemeny-keszito-wrapper {
    max-width: 950px;
  }
  
  .keszito-tartalom {
    padding: 30px;
  }
}
</style>