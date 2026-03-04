<template>
  <div class="event-creator-page">
    <button class="back-button" @click="$router.back()">
      <i class='bx bx-arrow-back'></i> Vissza
    </button>

    <div class="event-creator-wrapper">
      <div class="creator-header">
        <h1><i class='bx bx-calendar-plus'></i> Új esemény létrehozása</h1>
        <p class="subtitle">{{ roleMessage }}</p>
      </div>

      <div v-if="!hasPermission" class="no-permission">
        <div class="permission-error">
          <i class='bx bx-shield-x'></i>
          <h3>Nincs jogosultságod eseményt létrehozni!</h3>
          <p>Csak adminisztrátorok és osztályfőnökök hozhatnak létre eseményeket.</p>
          <router-link to="/mainpage" class="btn btn-primary">
            <i class='bx bx-home'></i> Vissza a főoldalra
          </router-link>
        </div>
      </div>

      <!-- JOGOSULT FELHASZNÁLÓ FORMJA -->
      <div v-else class="creator-content">
        <div class="stepper-nav">
          <div class="steps">
            <div v-for="step in steps" :key="step.number" 
                 class="step" 
                 :class="{ 'active': currentStep >= step.number, 'completed': currentStep > step.number }">
              <span class="step-number">{{ step.number }}</span>
              <span class="step-label">{{ step.label }}</span>
            </div>
          </div>
        </div>

        <!-- 1. ESEMÉNY SZINTJÉNEK KIVÁLASZTÁSA -->
        <div v-if="currentStep === 1" class="form-section">
          <h3><i class='bx bx-layer'></i> 1. Esemény szintjének kiválasztása</h3>
          <div class="type-selection">
            <!-- Globális opció - csak adminoknak -->
              <label v-if="userRole === 'admin'" 
                   class="type-option" 
                  :class="{ 'selected': selectedEventScope === 'global' }">
                <input type="radio" v-model="selectedEventScope" value="global" hidden>
              <div class="option-content">
                <i class='bx bx-world'></i>
                <h4>Globális szintű esemény</h4>
                <p>Több intézménybe is kiküldhető esemény</p>
                <span class="permission-badge">Csak adminoknak</span>
              </div>
            </label>

            <!-- Iskolai opció - minden jogosultnak -->
            <label class="type-option" 
              :class="{ 'selected': selectedEventScope === 'school' }">
                <input type="radio" v-model="selectedEventScope" value="school" hidden>
              <div class="option-content">
                <i class='bx bx-building'></i>
                <h4>Iskolai szintű esemény</h4>
                <p>Saját intézményeden belüli esemény</p>
              </div>
            </label>
          </div>
        </div>

        <!-- 2. GLOBÁLIS ESEMÉNY - MEGYÉK KIVÁLASZTÁSA -->
        <div v-if="currentStep === 2 && selectedEventScope === 'global'" class="form-section">
          <h3><i class='bx bx-map'></i> 2. Vármegyék kiválasztása</h3>
          <p class="selection-description">Válaszd ki, mely vármegyék iskolái kapják meg az eseményt</p>
          
          <div class="county-list">
            <div v-for="county in counties" 
                 :key="county.id" 
                 class="county-option"
                 :class="{ 
                   'selected': selectedCountyIds.includes(county.id),
                   'own-county': county.id === userCountyId
                 }">
              <label class="county-checkbox">
                <input 
                  type="checkbox" 
                  :value="county.id" 
                  v-model="selectedCountyIds"
                  :disabled="county.id === userCountyId && !selectedCountyIds.includes(county.id)"
                >
                <span class="county-name">
                  {{ county.name }}
                  <span v-if="county.id === userCountyId" class="own-county-badge">
                    (saját vármegye - kötelező)
                  </span>
                </span>
              </label>
              <span class="school-count">{{ county.schoolCount }} iskola</span>
            </div>
          </div>

          <div class="selected-info" v-if="selectedCountyIds.length > 0">
            <i class='bx bx-check-circle'></i>
            <span>{{ selectedCountyIds.length }} vármegye kiválasztva</span>
          </div>
        </div>

        <!-- 2. ISKOLAI ESEMÉNY - CÉLCSOPORT KIVÁLASZTÁSA -->
        <div v-if="currentStep === 2 && selectedEventScope === 'school'" class="form-section">
          <h3><i class='bx bx-target-lock'></i> 2. Célcsoport kiválasztása</h3>
          
          <div class="target-group-selection">
            <div class="target-group-options">
              <label v-for="target in schoolTargetOptions" 
                     :key="target.value"
                     class="target-group-option"
                     :class="{ 'selected': selectedSchoolTargetGroup === target.value }">
                <input type="radio" v-model="selectedSchoolTargetGroup" :value="target.value" hidden>
                <div class="option-content">
                  <i :class="target.icon"></i>
                  <h4>{{ target.label }}</h4>
                  <p>{{ target.description }}</p>
                </div>
              </label>
            </div>
          </div>
        </div>

        <!-- 3. ESEMÉNY ADATAI (közös rész) -->
        <div v-if="currentStep === 3" class="form-section">
          <h3><i class='bx bx-edit'></i> 3. Esemény adatai</h3>
          <div class="event-form">
            <div class="form-group">
              <label>Cím *</label>
              <input v-model="eventForm.title" placeholder="Esemény címe" required>
            </div>
            <div class="form-group">
              <label>Leírás *</label>
              <textarea v-model="eventForm.description" placeholder="Részletes leírás" required></textarea>
            </div>
            <div class="form-group">
              <label>Tartalom (opcionális)</label>
              <textarea v-model="eventForm.content" placeholder="Bővebb tartalom, instrukciók"></textarea>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Kezdés *</label>
                <input type="datetime-local" v-model="eventForm.startDateTime" :min="todayMin" required>
              </div>
              <div class="form-group">
                <label>Befejezés *</label>
                <input type="datetime-local" v-model="eventForm.endDateTime" :min="eventForm.startDateTime || todayMin" required>
              </div>
            </div>
          </div>
        </div>

        <!-- 4. ÁTTEKINTÉS -->
        <div v-if="currentStep === 4" class="form-section">
          <h3><i class='bx bx-check-circle'></i> 4. Áttekintés</h3>
          <div class="summary">
            <div class="summary-item">
              <strong>Esemény szintje:</strong> {{ getEventScopeLabel(selectedEventScope) }}
            </div>
            
            <!-- Globális esemény részletei -->
            <template v-if="selectedEventScope === 'global'">
              <div class="summary-item">
                <strong>Kiválasztott vármegyék:</strong> {{ selectedCountyIds.length }} db
              </div>
              <div class="summary-item">
                <strong>Érintett vármegyék:</strong> 
                <span class="county-list-summary">
                  {{ getSelectedCountyNames().join(', ') }}
                </span>
              </div>
            </template>
            
            <!-- Iskolai esemény részletei -->
            <template v-else>
              <div class="summary-item">
                <strong>Célcsoport:</strong> {{ getSchoolTargetLabel(selectedSchoolTargetGroup) }}
              </div>
              <div class="summary-item">
                <strong>Intézmény:</strong> {{ userInstitution.name }}
              </div>
            </template>

            <div class="summary-item">
              <strong>Cím:</strong> {{ eventForm.title || '(nincs megadva)' }}
            </div>
            <div class="summary-item">
              <strong>Időpont:</strong> {{ formatDateTime(eventForm.startDateTime) }} - {{ formatDateTime(eventForm.endDateTime) }}
            </div>
          </div>
        </div>

        <div class="form-actions">
          <button @click="previousStep" :disabled="currentStep === 1" class="btn btn-secondary">
            <i class='bx bx-chevron-left'></i> Előző
          </button>
          
          <button v-if="currentStep < 4" @click="nextStep" :disabled="!canProceed" class="btn btn-primary">
            Következő <i class='bx bx-chevron-right'></i>
          </button>
          
          <button v-else @click="createEvent" :disabled="!isFormValid || isSubmitting" class="btn btn-success">
            <i class='bx bx-check' v-if="!isSubmitting"></i>
            <i class='bx bx-loader-circle bx-spin' v-else></i>
            {{ isSubmitting ? 'Feldolgozás...' : 'Esemény létrehozása' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { toast } from '../services/toast'

export default {
  name: 'EsemenyKeszito',
  
  data() {
    return {
      // FELHASZNÁLÓ ADATAI
      userRole: 'student',
      userInstitution: {
        id: 1,
        name: 'Kossuth Lajos Gimnázium',
        countyId: 1
      },
      userCountyId: 1,
      
      // LÉPTETŐ ADATOK
      currentStep: 1,
      isSubmitting: false,
      todayMin: new Date().toISOString().slice(0, 16),
      
      // ESEMÉNY SZINT
      selectedEventScope: 'school',
      
      // GLOBÁLIS ESEMÉNY ADATOK
      selectedCountyIds: [],
      countiesList: [],
      
      // ISKOLAI ESEMÉNY ADATOK
      selectedSchoolTargetGroup: 'sajat_osztaly',
      
      // ESEMÉNY ADATOK
      eventForm: {
        title: '',
        description: '',
        content: '',
        startDateTime: '',
        endDateTime: ''
      },
      
      // KONFIGURÁCIÓK
      steps: [
        { number: 1, label: 'Szint' },
        { number: 2, label: 'Célcsoport' },
        { number: 3, label: 'Adatok' },
        { number: 4, label: 'Létrehozás' }
      ],
      
      schoolTargetOptions: [
        { 
          value: 'sajat_osztaly', 
          label: 'Saját osztály', 
          icon: 'bx bx-user', 
          description: 'Csak a saját osztályod diákjai és tanárai látják' 
        },
        { 
          value: 'evfolyam', 
          label: 'Évfolyam szintű', 
          icon: 'bx bx-group', 
          description: 'A saját osztályod és az évfolyam többi osztálya' 
        },
        { 
          value: 'teljes_iskola', 
          label: 'Teljes iskola', 
          icon: 'bx bx-building-house', 
          description: 'Az iskolában lévő összes felhasználó látja' 
        }
      ]
    }
  },
  
  computed: {
    hasPermission() {
      return ['admin', 'teacher', 'institution_manager'].includes(this.userRole)
    },
    
    roleMessage() {
      if (this.userRole === 'teacher') {
        return 'Osztályfőnökként iskolai szintű eseményt hozhatsz létre'
      }
      if (this.userRole === 'admin') {
        return 'Adminisztrátorként globális és iskolai eseményt is létrehozhatsz'
      }
      if (this.userRole === 'institution_manager') {
        return 'Intézményvezetőként iskolai szintű eseményt hozhatsz létre'
      }
      return 'Nincs jogosultságod eseményt létrehozni'
    },
    
    canProceed() {
      switch (this.currentStep) {
        case 1: 
          if (this.selectedEventScope === 'global' && this.userRole !== 'admin') {
            return false
          }
          return this.selectedEventScope !== ''
        
        case 2:
          if (this.selectedEventScope === 'global') {
            return this.selectedCountyIds.length > 0
          } else {
            return this.selectedSchoolTargetGroup !== ''
          }
        
        case 3:
          return this.validateEventForm()
        
        default:
          return true
      }
    },
    
    isFormValid() {
      return this.validateEventForm() && this.currentStep === 4
    },
    
    counties() {
      return this.countiesList
    }
  },
  
  watch: {
    // Ha az esemény szintje változik, reseteljük a megfelelő adatokat
    selectedEventScope(newValue) {
      if (newValue === 'global') {
        if (!this.selectedCountyIds.includes(this.userCountyId)) {
          this.selectedCountyIds = [this.userCountyId]
        }
      } else {
        this.selectedSchoolTargetGroup = 'sajat_osztaly'
      }
    },
    
    // Saját megye mindig legyen kiválasztva globális eseménynél
    selectedCountyIds(newValue) {
      if (this.selectedEventScope === 'global' && 
          !newValue.includes(this.userCountyId)) {
        this.$nextTick(() => {
          this.selectedCountyIds = [this.userCountyId, ...newValue]
        })
      }
    }
  },
  
  created() {
    this.initialize()
  },
  
  methods: {
    initialize() {
      try {
        const savedUser =
          localStorage.getItem('esemenyter_user') ||
          sessionStorage.getItem('esemenyter_user')

        if (savedUser) {
          const user = JSON.parse(savedUser)
          this.userRole = String(user?.role || 'student').toLowerCase()

          if (user?.institution_id) {
            this.userInstitution.id = Number(user.institution_id)
          }
        }
      } catch (error) {
        console.error('Felhasználó inicializálási hiba:', error)
      }

      if (this.userRole !== 'admin') {
        this.selectedEventScope = 'school'
      }

      this.loadCounties()
      this.loadInstitutionData()
    },

    async loadInstitutionData() {
      try {
        const institutionId = Number(this.userInstitution.id)
        if (!institutionId) {
          return
        }

        const token =
          localStorage.getItem('esemenyter_token') ||
          sessionStorage.getItem('esemenyter_token')

        if (!token) {
          return
        }

        const response = await axios.get(`http://127.0.0.1:8000/api/establishment/${institutionId}`, {
          headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
          }
        })

        const institution = response?.data?.data || {}

        this.userInstitution = {
          ...this.userInstitution,
          id: Number(institution.id || institutionId),
          name: institution.title || institution.name || this.userInstitution.name,
          countyId: Number(institution.region_id || this.userInstitution.countyId || 1)
        }
      } catch (error) {
        console.error('Intézmény betöltési hiba:', error)
      }
    },

    async loadCounties() {
      try {
        const response = await axios.get('http://127.0.0.1:8000/api/regions/all', {
          headers: {
            Accept: 'application/json'
          }
        })

        const apiCounties = Array.isArray(response?.data?.data) ? response.data.data : []

        if (!apiCounties.length) {
          return
        }

        this.countiesList = apiCounties.map((county) => ({
          id: Number(county.id),
          name: county.title || county.name || county.nev || `Vármegye #${county.id}`,
          schoolCount: Number(county.iskolakSzama || county.schools_count || 0)
        }))
      } catch (error) {
        console.error('Megyék betöltési hiba:', error)
      }
    },
    
    getEventScopeLabel(scope) {
      const labels = {
        'global': 'Globális esemény',
        'school': 'Iskolai esemény'
      }
      return labels[scope] || scope
    },
    
    getSchoolTargetLabel(targetGroup) {
      const target = this.schoolTargetOptions.find(option => option.value === targetGroup)
      return target ? target.label : targetGroup
    },
    
    getSelectedCountyNames() {
      return this.selectedCountyIds.map((id) => {
        const county = this.counties.find(item => item.id === id)
        return county ? county.name : id
      })
    },
    
    validateEventForm() {
      const { title, description, startDateTime, endDateTime } = this.eventForm
      
      return title && title.trim() !== '' && 
             description && description.trim() !== '' && 
             startDateTime && startDateTime !== '' && 
             endDateTime && endDateTime !== '' &&
             new Date(startDateTime) <= new Date(endDateTime)
    },
    
    formatDateTime(dateTime) {
      if (!dateTime) return 'nincs megadva'
      const date = new Date(dateTime)
      return date.toLocaleString('hu-HU', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
      })
    },
    
    nextStep() {
      if (this.currentStep < 4 && this.canProceed) {
        this.currentStep++
      }
    },
    
    previousStep() {
      if (this.currentStep > 1) {
        this.currentStep--
      }
    },
  
    async createEvent() {
      if (!this.isFormValid) {
        toast.warning('Kérjük, töltsd ki az összes kötelező mezőt!')
        return
      }

      this.isSubmitting = true

      try {
        const token =
          localStorage.getItem('esemenyter_token') ||
          sessionStorage.getItem('esemenyter_token')

        if (!token) {
          toast.error('Lejárt munkamenet. Kérlek jelentkezz be újra!')
          this.$router.push('/')
          return
        }

        // Adatok összeállítása a backend elvárásai szerint
        const payload = {
          title: this.eventForm.title,
          description: this.eventForm.description,
          content: this.eventForm.content,
          start_date: this.eventForm.startDateTime,
          end_date: this.eventForm.endDateTime,
          type: this.selectedEventScope === 'global' ? 'global' : 'local'
        }

        if (this.selectedEventScope === 'global') {
          payload.counties = this.selectedCountyIds
        } 
        else {
          payload.target_group = this.selectedSchoolTargetGroup
          payload.institution_id = this.userInstitution.id
        }

        await axios.post('http://127.0.0.1:8000/api/events', payload, {
          headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json',
            'Content-Type': 'application/json'
          }
        })

        toast.success('Esemény sikeresen létrehozva!')
        this.$router.push('/events-list')

      } catch (error) {
        console.error('Hiba az esemény létrehozásakor:', error)

        if (error.response?.data?.message) {
          toast.error('Hiba: ' + error.response.data.message)
        } else if (error.response?.data?.errors) {
          const errors = Object.values(error.response.data.errors).flat()
          toast.error('Hiba: ' + errors.join(' '))
        } else {
          toast.error('Ismeretlen hiba történt')
        }
      } finally {
        this.isSubmitting = false
      }
    }
  }
}
</script>

<style scoped>
.event-creator-page {
  background: linear-gradient(135deg, #8c8c8f 0%, #764ba2 100%);
  font-family: "Poppins", sans-serif;
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  box-sizing: border-box;
  width: 100vw;
}

.back-button {
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

.back-button:hover {
  background: white;
  transform: translateX(-5px);
}

.event-creator-wrapper {
  max-width: 900px;
  width: 100%;
  background: white;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  margin: 60px 20px 20px;
  box-sizing: border-box;
}

.creator-header {
  background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
  color: white;
  padding: 25px 30px;
  text-align: center;
  box-sizing: border-box;
}

.creator-header h1 {
  font-size: 28px;
  margin-bottom: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
}

.subtitle {
  opacity: 0.9;
  font-size: 14px;
}

.creator-content {
  padding: 25px;
  box-sizing: border-box;
}

.no-permission {
  padding: 30px;
  text-align: center;
  box-sizing: border-box;
}

.permission-error {
  padding: 25px;
  background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
  border-radius: 15px;
  border-left: 5px solid #ef4444;
  box-sizing: border-box;
}

.permission-error i {
  font-size: 48px;
  color: #ef4444;
  margin-bottom: 15px;
}

.permission-error h3 {
  color: #dc2626;
  margin-bottom: 10px;
  font-size: 20px;
}

.permission-error p {
  color: #7f1d1d;
  margin-bottom: 20px;
  font-size: 15px;
}

.stepper-nav {
  margin-bottom: 25px;
}

.steps {
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
  flex-wrap: wrap;
  gap: 10px;
}

.step {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 5px;
  position: relative;
  z-index: 2;
  flex: 1;
  min-width: 70px;
}

.step-number {
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

.step.active .step-number {
  background: #4f46e5;
  color: white;
  transform: scale(1.1);
}

.step.completed .step-number {
  background: #10b981;
  color: white;
}

.step-label {
  color: #6b7280;
  font-size: 12px;
  font-weight: 500;
  text-align: center;
  word-break: break-word;
  max-width: 80px;
}

.step.active .step-label {
  color: #4f46e5;
  font-weight: 600;
}

.form-section {
  margin-bottom: 25px;
  padding-bottom: 15px;
  border-bottom: 1px solid #e5e7eb;
  box-sizing: border-box;
}

.form-section:last-child {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
}

.form-section h3 {
  color: #1f2937;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 20px;
}

.form-section h3 i {
  color: #4f46e5;
}

.type-selection, .target-group-options {
  display: grid;
  grid-template-columns: 1fr;
  gap: 15px;
  margin-top: 15px;
  box-sizing: border-box;
}

.type-option, .target-group-option {
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 15px;
  cursor: pointer;
  transition: all 0.3s;
  background: white;
  box-sizing: border-box;
}

.type-option:hover, .target-group-option:hover {
  border-color: #c7d2fe;
  transform: translateY(-2px);
}

.type-option.selected, .target-group-option.selected {
  border-color: #4f46e5;
  background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
}

.option-content {
  text-align: center;
  box-sizing: border-box;
}

.option-content i {
  font-size: 36px;
  color: #4f46e5;
  margin-bottom: 10px;
}

.option-content h4 {
  color: #1f2937;
  margin-bottom: 5px;
  font-size: 18px;
}

.option-content p {
  color: #6b7280;
  font-size: 13px;
  line-height: 1.4;
}

/* Új stílusok a globális megye választóhoz */
.county-list {
  max-height: 400px;
  overflow-y: auto;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 10px;
  margin-top: 15px;
  background: #f9fafb;
}

.county-option {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 15px;
  border-bottom: 1px solid #e5e7eb;
  transition: all 0.3s;
}

.county-option:last-child {
  border-bottom: none;
}

.county-option:hover {
  background: #f3f4f6;
}

.county-option.selected {
  background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
}

.county-option.own-county {
  background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
  border-left: 4px solid #10b981;
}

.county-checkbox {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  flex: 1;
}

.county-checkbox input[type="checkbox"] {
  width: 18px;
  height: 18px;
  cursor: pointer;
  accent-color: #4f46e5;
}

.county-checkbox input[type="checkbox"]:disabled {
  accent-color: #10b981;
  cursor: not-allowed;
}

.county-name {
  font-weight: 500;
  color: #1f2937;
}

.own-county-badge {
  font-size: 12px;
  color: #10b981;
  font-weight: 600;
  margin-left: 8px;
}

.school-count {
  font-size: 13px;
  color: #6b7280;
  background: #f3f4f6;
  padding: 4px 8px;
  border-radius: 20px;
  white-space: nowrap;
}

.selected-info {
  margin-top: 15px;
  padding: 12px;
  background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 10px;
  color: #0369a1;
  font-weight: 500;
}

.selected-info i {
  font-size: 20px;
  color: #0284c7;
}

.selection-description {
  color: #6b7280;
  font-size: 14px;
  margin-bottom: 10px;
}

.permission-badge {
  display: inline-block;
  margin-top: 8px;
  padding: 4px 8px;
  background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
  color: #dc2626;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
}

.county-list-summary {
  display: block;
  margin-top: 5px;
  font-size: 14px;
  color: #4b5563;
  line-height: 1.5;
}

/* FORM ELEMEK */
.event-form {
  margin-top: 20px;
  box-sizing: border-box;
}

.form-group {
  margin-bottom: 20px;
  box-sizing: border-box;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  color: #374151;
  font-weight: 600;
  font-size: 14px;
}

.form-group input, .form-group textarea {
  width: 100%;
  padding: 10px 12px;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  font-size: 15px;
  font-family: inherit;
  transition: all 0.3s;
  box-sizing: border-box;
}

.form-group input:focus, .form-group textarea:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.form-group textarea {
  min-height: 100px;
  resize: vertical;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
  box-sizing: border-box;
}

.summary {
  background: #f9fafb;
  border-radius: 12px;
  padding: 15px;
  margin-top: 15px;
  box-sizing: border-box;
}

.summary-item {
  padding: 10px 0;
  border-bottom: 1px solid #e5e7eb;
  font-size: 15px;
  line-height: 1.4;
}

.summary-item:last-child {
  border-bottom: none;
}

.summary-item strong {
  color: #374151;
  margin-right: 8px;
}

/* gombok */
.form-actions {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  margin-top: 25px;
  padding-top: 15px;
  border-top: 2px solid #e5e7eb;
  box-sizing: border-box;
}

.btn {
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

.btn-primary {
  background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 20px rgba(79, 70, 229, 0.2);
}

.btn-secondary {
  background: #f1f5f9;
  color: #475569;
  border: 2px solid #e2e8f0;
}

.btn-secondary:hover:not(:disabled) {
  background: #e2e8f0;
}

.btn-success {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
}

.btn-success:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none !important;
  box-shadow: none !important;
}

/* Reszponzív kiegészítések */
@media (max-width: 768px) {
  .event-creator-page {
    padding: 15px;
    align-items: flex-start;
  }
  
  .event-creator-wrapper {
    margin: 50px 15px 15px;
    max-width: 100%;
  }
  
  .back-button {
    top: 15px;
    left: 15px;
    padding: 8px 12px;
    font-size: 14px;
    max-width: 110px;
  }
  
  .creator-header {
    padding: 20px;
  }
  
  .creator-header h1 {
    font-size: 22px;
    flex-direction: row;
    gap: 10px;
  }
  
  .subtitle {
    font-size: 12px;
  }
  
  .creator-content {
    padding: 20px;
  }
  
  .steps {
    gap: 5px;
  }
  
  .step {
    min-width: 60px;
  }
  
  .step-number {
    width: 32px;
    height: 32px;
    font-size: 14px;
  }
  
  .step-label {
    font-size: 11px;
    max-width: 70px;
  }
  
  .form-section h3 {
    font-size: 18px;
  }
  
  .option-content h4 {
    font-size: 16px;
  }
  
  .option-content i {
    font-size: 32px;
  }
  
  .form-group label {
    font-size: 13px;
  }
  
  .form-group input, .form-group textarea {
    font-size: 14px;
    padding: 9px 11px;
  }
  
  .form-row {
    grid-template-columns: 1fr;
    gap: 12px;
  }
  
  .btn {
    padding: 9px 16px;
    font-size: 14px;
  }
  
  .form-actions {
    flex-direction: row;
    gap: 10px;
  }
  
  .permission-error {
    padding: 20px;
  }
  
  .permission-error i {
    font-size: 40px;
  }
  
  .permission-error h3 {
    font-size: 18px;
  }
  
  .county-list {
    max-height: 350px;
  }
  
  .county-option {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  
  .school-count {
    align-self: flex-end;
  }
  
  .county-name {
    font-size: 14px;
  }
}

@media (max-width: 480px) {
  .event-creator-page {
    padding: 10px;
  }
  
  .event-creator-wrapper {
    margin: 45px 10px 10px;
    border-radius: 15px;
  }
  
  .back-button {
    top: 10px;
    left: 10px;
    padding: 7px 10px;
    font-size: 13px;
    max-width: 100px;
  }
  
  .creator-header {
    padding: 15px;
  }
  
  .creator-header h1 {
    font-size: 18px;
    gap: 8px;
  }
  
  .subtitle {
    font-size: 12px;
  }
  
  .creator-content {
    padding: 15px;
  }
  
  .step {
    min-width: 50px;
  }
  
  .step-number {
    width: 28px;
    height: 28px;
    font-size: 13px;
  }
  
  .step-label {
    font-size: 10px;
    max-width: 60px;
  }
  
  .form-section h3 {
    font-size: 16px;
    gap: 8px;
  }
  
  .form-section h3 i {
    font-size: 18px;
  }
  
  .type-selection, .target-group-options {
    gap: 10px;
  }
  
  .type-option, .target-group-option {
    padding: 12px;
  }
  
  .option-content i {
    font-size: 28px;
    margin-bottom: 8px;
  }
  
  .option-content h4 {
    font-size: 15px;
    margin-bottom: 4px;
  }
  
  .option-content p {
    font-size: 12px;
  }
  
  .form-actions {
    flex-direction: column;
    gap: 8px;
  }
  
  .btn {
    width: 100%;
    padding: 10px;
  }
  
  .county-list {
    max-height: 300px;
    padding: 8px;
  }
  
  .county-option {
    padding: 10px;
  }
  
  .county-checkbox {
    gap: 8px;
  }
  
  .county-checkbox input[type="checkbox"] {
    width: 16px;
    height: 16px;
  }
  
  .own-county-badge {
    display: block;
    margin-left: 0;
    margin-top: 4px;
  }
}

@media (min-width: 769px) {
  .type-selection, .target-group-options {
    grid-template-columns: 1fr 1fr;
  }
  
  .form-actions .btn {
    flex: none;
    min-width: 140px;
  }
}

@media (min-width: 1024px) {
  .event-creator-wrapper {
    max-width: 950px;
  }
  
  .creator-content {
    padding: 30px;
  }
}
</style>