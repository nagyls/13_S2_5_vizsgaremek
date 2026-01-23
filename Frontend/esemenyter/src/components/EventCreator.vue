<template>
  <div class="event-creator-page">
    <button class="back-btn" @click="$router.back()">
      <i class='bx bx-arrow-back'></i> Vissza
    </button>

    <div class="event-creator-wrapper">
      <div class="creator-header">
        <h1><i class='bx bx-calendar-plus'></i> Új esemény létrehozása</h1>
        <p class="subtitle">{{ getRoleMessage }}</p>
      </div>

      <!-- Nincs jogosultság -->
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

      <!-- Jogosult felhasználó -->
      <div v-else class="creator-content">
        <!-- LÉPTETŐ -->
        <div class="step-navigation">
          <div class="steps">
            <div v-for="step in steps" :key="step.number" 
                 class="step" 
                 :class="{ 'active': currentStep >= step.number, 'completed': currentStep > step.number }">
              <span class="step-number">{{ step.number }}</span>
              <span class="step-label">{{ step.label }}</span>
            </div>
          </div>
        </div>

        <!-- 1. ESEMÉNY TÍPUSA -->
        <div v-if="currentStep === 1 && userRole === 'admin'" class="form-section">
          <h3><i class='bx bx-category'></i> 1. Esemény típusa</h3>
          <div class="type-selection">
            <label v-for="type in eventTypes" :key="type.value" 
                   class="type-option" 
                   :class="{ 'selected': eventType === type.value }">
              <input type="radio" v-model="eventType" :value="type.value" hidden>
              <div class="option-content">
                <i :class="type.icon"></i>
                <h4>{{ type.label }}</h4>
                <p>{{ type.description }}</p>
              </div>
            </label>
          </div>
        </div>

        <!-- 2. CÉLCSOPORT -->
        <div v-if="currentStep === 2 && shouldShowScopeSelection" class="form-section">
          <h3><i class='bx bx-target-lock'></i> 2. Célcsoport kiválasztása</h3>
          
          <div v-if="isLocalEvent" class="scope-info">
            <div class="info-box">
              <i class='bx bx-info-circle'></i>
              <p>Helyi eseményként automatikusan a saját intézményed lesz kiválasztva.</p>
            </div>
            <div class="selected-establishment">
              <i class='bx bx-check-circle'></i>
              <span>{{ userEstablishment.title }}</span>
            </div>
          </div>

          <div v-else class="scope-selection">
            <!-- Tartalom a korábbi Step2 komponensből... -->
            <div class="scope-options">
              <label v-for="scope in scopeModes" :key="scope.value"
                     class="scope-option"
                     :class="{ 'selected': scopeMode === scope.value }">
                <input type="radio" v-model="scopeMode" :value="scope.value" hidden>
                <div class="option-content">
                  <i :class="scope.icon"></i>
                  <h4>{{ scope.label }}</h4>
                  <p>{{ scope.description }}</p>
                </div>
              </label>
            </div>

            <!-- Tartalom a további részekből... -->
            <!-- Rövidítve a példa kedvéért -->
            <div class="selection-hint">
              {{ scopeMode === 'intezmeny_lista' ? 'Válassz intézményeket' : 'Válassz szűrőket' }}
            </div>
          </div>
        </div>

        <!-- 3. OSZTÁLYOK -->
        <div v-if="currentStep === 3 && hasSelectedEstablishments" class="form-section">
          <h3><i class='bx bx-group'></i> 3. Osztályok kiválasztása</h3>
          <div class="selection-hint">
            {{ selectedClasses.length }} osztály kiválasztva
          </div>
        </div>

        <!-- 4. ESEMÉNY ADATAI -->
        <div v-if="currentStep === 4 && hasSelectedClasses" class="form-section">
          <h3><i class='bx bx-edit'></i> 4. Esemény adatai</h3>
          <div class="event-details-form">
            <div class="form-group">
              <label>Cím *</label>
              <input v-model="eventDetails.title" placeholder="Esemény címe" required>
            </div>
            <div class="form-group">
              <label>Leírás *</label>
              <textarea v-model="eventDetails.description" placeholder="Részletes leírás" required></textarea>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Kezdés *</label>
                <input type="date" v-model="eventDetails.start_date" :min="today" required>
              </div>
              <div class="form-group">
                <label>Befejezés *</label>
                <input type="date" v-model="eventDetails.end_date" :min="eventDetails.start_date || today" required>
              </div>
            </div>
          </div>
        </div>

        <!-- 5. ÁTTEKINTÉS -->
        <div v-if="currentStep === 5 && hasSelectedClasses" class="form-section">
          <h3><i class='bx bx-check-circle'></i> 5. Áttekintés</h3>
          <div class="overview">
            <div class="summary-item">
              <strong>Típus:</strong> {{ getEventTypeLabel(eventType) }}
            </div>
            <div class="summary-item">
              <strong>Intézmények:</strong> {{ selectedEstablishments.length }}
            </div>
            <div class="summary-item">
              <strong>Osztályok:</strong> {{ selectedClasses.length }}
            </div>
            <div class="summary-item">
              <strong>Cím:</strong> {{ eventDetails.title || '(nincs megadva)' }}
            </div>
          </div>
        </div>

        <!-- GOMBOK -->
        <div class="form-actions">
          <button @click="prevStep" :disabled="currentStep === 1" class="btn btn-secondary">
            <i class='bx bx-chevron-left'></i> Előző
          </button>
          
          <button v-if="currentStep < 5" @click="nextStep" :disabled="!canProceedToNextStep" class="btn btn-primary">
            Következő <i class='bx bx-chevron-right'></i>
          </button>
          
          <button v-else @click="createEvent" :disabled="!isFormValid || loading" class="btn btn-success">
            <i class='bx bx-check' v-if="!loading"></i>
            <i class='bx bx-loader-circle bx-spin' v-else></i>
            {{ loading ? 'Feldolgozás...' : 'Esemény létrehozása' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';


export default {
  name: 'EventCreator',
  
  data() {
    return {
      userRole: 'admin',
      userEstablishment: { id: 1, title: 'Kossuth Lajos Gimnázium' },
      eventType: 'local',
      scopeMode: 'intezmeny_lista',
      selectedEstablishments: [],
      selectedClasses: [],
      eventDetails: {
        title: '',
        description: '',
        content: '',
        start_date: '',
        end_date: ''
      },
      currentStep: 1,
      loading: false,
      today: new Date().toISOString().split('T')[0],
      
      // Konfiguráció
      steps: [
        { number: 1, label: 'Típus' },
        { number: 2, label: 'Célcsoport' },
        { number: 3, label: 'Osztályok' },
        { number: 4, label: 'Adatok' },
        { number: 5, label: 'Létrehozás' }
      ],
      
      eventTypes: [
        { value: 'local', label: 'Helyi esemény', icon: 'bx bx-building', description: 'Csak a saját intézményedben' },
        { value: 'global', label: 'Globális esemény', icon: 'bx bx-world', description: 'Több intézményben' }
      ],
      
      scopeModes: [
        { value: 'intezmeny_lista', label: 'Intézmény lista', icon: 'bx bx-list-ol', description: 'Kézzel válaszd ki' },
        { value: 'teruleti_szures', label: 'Területi szűrés', icon: 'bx bx-filter-alt', description: 'Szűrés alapján' }
      ]
    }
  },
  
  computed: {
    hasPermission() {
      return this.userRole === 'admin' || this.userRole === 'teacher'
    },
    
    getRoleMessage() {
      if (this.userRole === 'teacher') return 'Osztályfőnökként helyi eseményt hozhatsz létre'
      if (this.userRole === 'admin') return 'Adminisztrátorként helyi vagy globális eseményt hozhatsz létre'
      return 'Nincs jogosultságod eseményt létrehozni'
    },
    
    isLocalEvent() {
      return this.eventType === 'local' || this.userRole === 'teacher'
    },
    
    shouldShowScopeSelection() {
      if (this.userRole === 'teacher') return false
      return this.currentStep >= 2
    },
    
    hasSelectedEstablishments() {
      return this.selectedEstablishments.length > 0 || this.isLocalEvent
    },
    
    hasSelectedClasses() {
      return this.selectedClasses.length > 0 || this.currentStep < 3
    },
    
    canProceedToNextStep() {
      switch (this.currentStep) {
        case 1: return true
        case 2: return this.isLocalEvent || this.selectedEstablishments.length > 0
        case 3: return this.selectedClasses.length > 0 || true // Demo
        case 4: return this.validateEventDetails()
        default: return true
      }
    },
    
    isFormValid() {
      return this.validateEventDetails() && this.currentStep === 5
    }
  },
  
  watch: {
    eventType(newVal) {
      if (newVal === 'local') {
        this.selectedEstablishments = [this.userEstablishment]
      } else {
        this.selectedEstablishments = []
      }
    }
  },
  
  created() {
    this.initializeDemoData()
  },
  
  methods: {
    initializeDemoData() {
      // Demo adatok
      if (this.userRole === 'teacher') {
        this.eventType = 'local'
        this.selectedEstablishments = [this.userEstablishment]
      }
    },
    
    getEventTypeLabel(type) {
      const typeObj = this.eventTypes.find(t => t.value === type)
      return typeObj ? typeObj.label : type
    },
    
    validateEventDetails() {
      const { title, description, start_date, end_date } = this.eventDetails
      return title.trim() !== '' && 
             description.trim() !== '' && 
             start_date !== '' && 
             end_date !== '' &&
             new Date(start_date) <= new Date(end_date)
    },
    
    nextStep() {
      if (this.currentStep < 5 && this.canProceedToNextStep) {
        this.currentStep++
        
        // Demo: automatikus kitöltés
        if (this.currentStep === 3) {
          this.selectedClasses = [1, 2, 3] // Demo osztály ID-k
        }
        if (this.currentStep === 4 && !this.eventDetails.title) {
          this.eventDetails = {
            title: 'Tavaszi kirándulás',
            description: 'Éves tavaszi kirándulás a természetben',
            content: 'Hozz magaddal kenyeret és vizet!',
            start_date: this.today,
            end_date: this.getNextWeekDate()
          }
        }
      }
    },
    
    getNextWeekDate() {
      const date = new Date()
      date.setDate(date.getDate() + 7)
      return date.toISOString().split('T')[0]
    },
    
    prevStep() {
      if (this.currentStep > 1) {
        this.currentStep--
      }
    },
    
    async createEvent() {
      if (!this.isFormValid) {
        alert('Kérjük, töltsd ki az összes kötelező mezőt!')
        return
      }

      this.loading = true

      try {
        const payload = {
          type: this.eventType,
          scope_mode: this.scopeMode,
          establishment_ids: this.isLocalEvent
            ? [this.userEstablishment.id]
            : this.selectedEstablishments.map(e => e.id),
          class_ids: this.selectedClasses,
          title: this.eventDetails.title,
          description: this.eventDetails.description,
          content: this.eventDetails.content,
          start_date: this.eventDetails.start_date,
          end_date: this.eventDetails.end_date,
        }

        await axios.post('http://127.0.0.1:8000/api/events', payload, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
            Accept: 'application/json'
          }
        })

        alert('✅ Esemény sikeresen létrehozva!')
        this.$router.push('/esemenyek')

      } catch (error) {
        console.error(error)

        if (error.response?.data?.message) {
          alert('Hiba: ' + error.response.data.message)
        } else {
          alert('Ismeretlen hiba történt')
        }
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style scoped>
/* ALAP STÍLUSOK */
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

.back-btn {
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

.back-btn:hover {
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

/* TARTALOM */
.creator-content {
  padding: 25px;
  box-sizing: border-box;
}

/* NINCS JOGOSULTSÁG */
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

/* LÉPTETŐ */
.step-navigation {
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

/* FORM SECTION */
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

/* TYPE SELECTION */
.type-selection, .scope-options {
  display: grid;
  grid-template-columns: 1fr;
  gap: 15px;
  margin-top: 15px;
  box-sizing: border-box;
}

.type-option, .scope-option {
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 15px;
  cursor: pointer;
  transition: all 0.3s;
  background: white;
  box-sizing: border-box;
}

.type-option:hover, .scope-option:hover {
  border-color: #c7d2fe;
  transform: translateY(-2px);
}

.type-option.selected, .scope-option.selected {
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

/* SCOPE INFO */
.scope-info {
  background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
  border-radius: 12px;
  padding: 20px;
  margin-top: 15px;
  box-sizing: border-box;
}

.info-box {
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

.info-box i {
  color: #0ea5e9;
  font-size: 20px;
  flex-shrink: 0;
}

.info-box p {
  color: #0369a1;
  margin: 0;
  font-size: 14px;
  line-height: 1.4;
}

.selected-establishment {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 15px;
  background: white;
  border-radius: 8px;
  border: 2px solid #10b981;
  box-sizing: border-box;
}

.selected-establishment i {
  color: #10b981;
  font-size: 20px;
}

.selected-establishment span {
  font-weight: 600;
  color: #047857;
  font-size: 16px;
}

/* FORM ELEMENTS */
.selection-hint {
  padding: 15px;
  background: #f9fafb;
  border-radius: 8px;
  text-align: center;
  color: #6b7280;
  font-size: 14px;
  margin-top: 15px;
  box-sizing: border-box;
}

.event-details-form {
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

/* OVERVIEW */
.overview {
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

/* FORM ACTIONS */
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

/* RESZPONZÍV */
@media (max-width: 768px) {
  .event-creator-page {
    padding: 15px;
    align-items: flex-start;
  }
  
  .event-creator-wrapper {
    margin: 50px 15px 15px;
    max-width: 100%;
  }
  
  .back-btn {
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
}

@media (max-width: 480px) {
  .event-creator-page {
    padding: 10px;
  }
  
  .event-creator-wrapper {
    margin: 45px 10px 10px;
    border-radius: 15px;
  }
  
  .back-btn {
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
  
  .type-selection, .scope-options {
    gap: 10px;
  }
  
  .type-option, .scope-option {
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
  
  .scope-info {
    padding: 15px;
  }
  
  .info-box, .selected-establishment {
    padding: 12px;
  }
  
  .selection-hint {
    padding: 12px;
    font-size: 13px;
  }
  
  .form-actions {
    flex-direction: column;
    gap: 8px;
  }
  
  .btn {
    width: 100%;
    padding: 10px;
  }
}

@media (min-width: 769px) {
  .type-selection, .scope-options {
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