<template>
  <div class="event-creator-page">
    <!-- Vissza gomb -->
    <button class="back-btn" @click="$router.back()" title="Vissza az előző oldalra">
      <i class='bx bx-arrow-back'></i> Vissza
    </button>

    <div class="event-creator-wrapper">
      <!-- Fejléc -->
      <div class="creator-header">
        <h1><i class='bx bx-calendar-plus'></i> Új Esemény Létrehozása</h1>
        <p class="subtitle">Töltsd ki az alábbi űrlapot az eseményed részleteivel</p>
      </div>

      <!-- Fő tartalom -->
      <div class="creator-content">
        
        <!-- Bal oldal: Űrlap -->
        <div class="form-side">
          <!-- Iskola kiválasztása -->
          <div class="form-section">
            <h3><i class='bx bx-building'></i> Iskola kiválasztása</h3>
            <div class="form-group">
              <label for="school-select">Válassz iskolát:</label>
              <div class="select-wrapper">
                <i class='bx bx-school'></i>
                <select 
                  id="school-select" 
                  v-model="selectedSchool"
                  class="form-control"
                  required
                >
                  <option value="">-- válassz iskolát --</option>
                  <option v-for="school in schools" :key="school.id" :value="school.id">
                    {{ school.name }} ({{ school.city }})
                  </option>
                </select>
              </div>
            </div>
          </div>

          <!-- Esemény adatok -->
          <div class="form-section">
            <h3><i class='bx bx-detail'></i> Esemény részletei</h3>
            
            <!-- Cím -->
            <div class="form-group">
              <label for="event-title">Esemény címe *</label>
              <div class="input-wrapper">
                <i class='bx bx-edit'></i>
                <input 
                  id="event-title"
                  type="text" 
                  v-model="event.title" 
                  placeholder="pl. Tavaszi kirándulás"
                  class="form-control"
                  required
                >
              </div>
            </div>

            <!-- Dátum és idő -->
            <div class="form-row">
              <div class="form-group">
                <label for="event-date">Dátum *</label>
                <div class="input-wrapper">
                  <i class='bx bx-calendar'></i>
                  <input 
                    id="event-date"
                    type="date" 
                    v-model="event.date" 
                    class="form-control"
                    required
                  >
                </div>
              </div>
              
              <div class="form-group">
                <label for="event-time">Időpont *</label>
                <div class="input-wrapper">
                  <i class='bx bx-time'></i>
                  <input 
                    id="event-time"
                    type="time" 
                    v-model="event.time" 
                    class="form-control"
                    required
                  >
                </div>
              </div>
            </div>

            <!-- Helyszín -->
            <div class="form-group">
              <label for="event-location">Helyszín *</label>
              <div class="input-wrapper">
                <i class='bx bx-map'></i>
                <input 
                  id="event-location"
                  type="text" 
                  v-model="event.location" 
                  placeholder="pl. Iskola előtt vagy konkrét cím"
                  class="form-control"
                  required
                >
              </div>
            </div>

            <!-- Leírás -->
            <div class="form-group">
              <label for="event-description">Leírás</label>
              <div class="textarea-wrapper">
                <i class='bx bx-note'></i>
                <textarea 
                  id="event-description"
                  v-model="event.description" 
                  rows="4" 
                  placeholder="Részletes leírás az eseményről..."
                  class="form-control"
                ></textarea>
              </div>
            </div>

            <!-- Max résztvevők és típus -->
            <div class="form-row">
              <div class="form-group">
                <label for="max-participants">Max. résztvevők</label>
                <div class="input-wrapper">
                  <i class='bx bx-group'></i>
                  <input 
                    id="max-participants"
                    type="number" 
                    v-model="event.maxParticipants" 
                    min="1"
                    placeholder="pl. 30"
                    class="form-control"
                  >
                </div>
              </div>
              
              <div class="form-group">
                <label for="event-type">Esemény típusa</label>
                <div class="select-wrapper">
                  <i class='bx bx-category'></i>
                  <select 
                    id="event-type"
                    v-model="event.type" 
                    class="form-control"
                  >
                    <option value="">-- válassz típust --</option>
                    <option value="kirandulas">Kirándulás</option>
                    <option value="verseny">Verseny</option>
                    <option value="unnepseg">Ünnepség</option>
                    <option value="sport">Sportnap</option>
                    <option value="eloadas">Előadás</option>
                    <option value="tanfolyam">Tanfolyam</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Jelentkezési feltételek -->
            <div class="form-section">
              <h4><i class='bx bx-check-shield'></i> Jelentkezési feltételek</h4>
              <div class="conditions">
                <label class="condition-checkbox">
                  <input type="checkbox" v-model="event.conditions.entireSchool">
                  <span class="checkmark"></span>
                  <span class="condition-text">Egész iskola számára</span>
                </label>
                
                <label class="condition-checkbox">
                  <input type="checkbox" v-model="event.conditions.specificGrades">
                  <span class="checkmark"></span>
                  <span class="condition-text">Csak adott évfolyamok</span>
                </label>
                
                <!-- Évfolyam választás -->
                <div v-if="event.conditions.specificGrades" class="grade-selection">
                  <label>Válassz évfolyamokat:</label>
                  <div class="grade-buttons">
                    <button 
                      v-for="grade in 12" 
                      :key="grade"
                      type="button"
                      :class="{ 'selected': event.selectedGrades.includes(grade) }"
                      @click="toggleGrade(grade)"
                      class="grade-btn"
                    >
                      {{ grade }}. évf.
                    </button>
                  </div>
                </div>
                
                <label class="condition-checkbox">
                  <input type="checkbox" v-model="event.conditions.specificClasses">
                  <span class="checkmark"></span>
                  <span class="condition-text">Csak adott osztályok</span>
                </label>
              </div>
            </div>

            <!-- Fájl feltöltés -->
            <div class="form-group">
              <label for="file-upload">Dokumentumok feltöltése</label>
              <div class="file-upload-area" @click="triggerFileInput" @dragover.prevent @drop="handleDrop">
                <input 
                  id="file-upload"
                  ref="fileInput"
                  type="file" 
                  @change="handleFileUpload"
                  multiple
                  class="file-input"
                  hidden
                >
                <div class="upload-content">
                  <i class='bx bx-cloud-upload'></i>
                  <p>Kattints ide vagy húzd ide a fájlokat</p>
                  <p class="upload-info">Max. 5 fájl, egyenként max. 10MB</p>
                </div>
              </div>
              
              <!-- Feltöltött fájlok listája -->
              <div v-if="uploadedFiles.length > 0" class="file-list">
                <h5>Feltöltött fájlok:</h5>
                <div class="file-items">
                  <div v-for="(file, index) in uploadedFiles" :key="index" class="file-item">
                    <div class="file-icon">
                      <i class='bx bx-file'></i>
                    </div>
                    <div class="file-details">
                      <span class="file-name">{{ file.name }}</span>
                      <span class="file-size">{{ formatFileSize(file.size) }}</span>
                    </div>
                    <button 
                      type="button" 
                      @click.stop="removeFile(index)"
                      class="remove-file-btn"
                      title="Fájl eltávolítása"
                    >
                      <i class='bx bx-x'></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Gombok -->
          <div class="form-actions">
            <button 
              type="button" 
              @click="resetForm"
              class="btn btn-secondary"
            >
              <i class='bx bx-reset'></i> Alaphelyzet
            </button>
            
            <button 
              type="button" 
              @click="createEvent"
              :disabled="!isFormValid"
              class="btn btn-primary"
            >
              <i class='bx bx-check'></i> Esemény Létrehozása
            </button>
          </div>
        </div>

        <!-- Jobb oldal: Előnézet -->
        <div class="preview-side">
          <div class="preview-header">
            <h3><i class='bx bx-show'></i> Előnézet</h3>
            <p>Így fog kinézni az esemény</p>
          </div>
          
          <div class="preview-card">
            <!-- Esemény fejléc -->
            <div class="preview-event-header">
              <h4>{{ event.title || "Esemény címe" }}</h4>
              <span v-if="event.type" class="event-type-badge">
                {{ getEventTypeLabel(event.type) }}
              </span>
            </div>
            
            <!-- Esemény részletek -->
            <div class="preview-details">
              <div class="preview-detail-row">
                <div class="detail-item">
                  <i class='bx bx-calendar'></i>
                  <div>
                    <span class="detail-label">Dátum</span>
                    <span class="detail-value">{{ formatDate(event.date) || "Nem megadva" }}</span>
                  </div>
                </div>
                
                <div class="detail-item">
                  <i class='bx bx-time'></i>
                  <div>
                    <span class="detail-label">Időpont</span>
                    <span class="detail-value">{{ event.time || "Nem megadva" }}</span>
                  </div>
                </div>
              </div>
              
              <div class="preview-detail-row">
                <div class="detail-item">
                  <i class='bx bx-map'></i>
                  <div>
                    <span class="detail-label">Helyszín</span>
                    <span class="detail-value">{{ event.location || "Nem megadva" }}</span>
                  </div>
                </div>
                
                <div v-if="event.maxParticipants" class="detail-item">
                  <i class='bx bx-group'></i>
                  <div>
                    <span class="detail-label">Max. résztvevők</span>
                    <span class="detail-value">{{ event.maxParticipants }} fő</span>
                  </div>
                </div>
              </div>
              
              <div class="preview-detail-row">
                <div v-if="selectedSchool" class="detail-item">
                  <i class='bx bx-building'></i>
                  <div>
                    <span class="detail-label">Iskola</span>
                    <span class="detail-value">{{ getSchoolName(selectedSchool) }}</span>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Leírás -->
            <div v-if="event.description" class="preview-description">
              <h5>Leírás:</h5>
              <p>{{ event.description }}</p>
            </div>
            
            <!-- Fájlok -->
            <div v-if="uploadedFiles.length > 0" class="preview-files">
              <h5><i class='bx bx-paperclip'></i> Csatolt fájlok</h5>
              <div class="files-count">
                {{ uploadedFiles.length }} fájl
              </div>
            </div>
            
            <!-- Feltételek -->
            <div class="preview-conditions">
              <h5><i class='bx bx-check-circle'></i> Jelentkezési feltételek</h5>
              <ul class="conditions-list">
                <li v-if="event.conditions.entireSchool">
                  <i class='bx bx-check'></i> Egész iskola számára
                </li>
                <li v-if="event.conditions.specificGrades && event.selectedGrades.length > 0">
                  <i class='bx bx-check'></i> Csak kiválasztott évfolyamok
                </li>
                <li v-if="event.conditions.specificClasses">
                  <i class='bx bx-check'></i> Csak adott osztályok
                </li>
                <li v-if="!event.conditions.entireSchool && !event.conditions.specificGrades && !event.conditions.specificClasses">
                  <i class='bx bx-x'></i> Nincsenek megadva feltételek
                </li>
              </ul>
            </div>
            
            <!-- Statisztika -->
            <div class="preview-stats">
              <div class="stat-item">
                <span class="stat-value">0</span>
                <span class="stat-label">Jelentkezett</span>
              </div>
              <div v-if="event.maxParticipants" class="stat-item">
                <span class="stat-value">{{ event.maxParticipants }}</span>
                <span class="stat-label">Férőhely</span>
              </div>
            </div>
          </div>
          
          <!-- Segítség doboz -->
          <div class="help-box">
            <h5><i class='bx bx-help-circle'></i> Segítség</h5>
            <ul class="help-list">
              <li>A * jelölt mezők kitöltése kötelező</li>
              <li>Dátum és időpont megadása kötelező</li>
              <li>Fájlokat csak PDF, JPG, PNG formátumban tudsz feltölteni</li>
              <li>Mentés után módosíthatod az eseményt</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'EventCreator',
  
  data() {
    return {
      // Kiválasztott iskola
      selectedSchool: '',
      
      // Esemény adatok
      event: {
        title: '',
        date: '',
        time: '',
        location: '',
        description: '',
        maxParticipants: null,
        type: '',
        conditions: {
          entireSchool: false,
          specificGrades: false,
          specificClasses: false
        },
        selectedGrades: []
      },
      
      // Feltöltött fájlok
      uploadedFiles: [],
      
      // Iskola lista (demo adatok)
      schools: [
        { id: 1, name: 'Kossuth Lajos Gimnázium', city: 'Budapest' },
        { id: 2, name: 'Petőfi Sándor Általános Iskola', city: 'Budapest' },
        { id: 3, name: 'Bolyai János Gimnázium', city: 'Miskolc' },
        { id: 4, name: 'Arany János Általános Iskola', city: 'Szeged' }
      ],
      
      // Esemény típusok leírása
      eventTypes: {
        kirandulas: 'Kirándulás',
        verseny: 'Verseny',
        unnepseg: 'Ünnepség',
        sport: 'Sportnap',
        eloadas: 'Előadás',
        tanfolyam: 'Tanfolyam'
      }
    }
  },
  
  computed: {
    // Form validáció
    isFormValid() {
      return (
        this.selectedSchool &&
        this.event.title.trim() &&
        this.event.date &&
        this.event.time &&
        this.event.location.trim()
      )
    },
    
    // Dátum formázása
    formattedDate() {
      if (!this.event.date) return ''
      const date = new Date(this.event.date)
      return date.toLocaleDateString('hu-HU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    }
  },
  
  methods: {
    // Fájl input trigger
    triggerFileInput() {
      this.$refs.fileInput.click()
    },
    
    // Fájl feltöltés kezelése
    handleFileUpload(event) {
      const files = Array.from(event.target.files)
      
      // Méret és szám ellenőrzés
      const validFiles = files.filter(file => {
        const isValidSize = file.size <= 10 * 1024 * 1024 // 10MB
        const isValidType = ['image/jpeg', 'image/png', 'application/pdf'].includes(file.type)
        return isValidSize && isValidType
      })
      
      // Max 5 fájl
      const remainingSlots = 5 - this.uploadedFiles.length
      const filesToAdd = validFiles.slice(0, remainingSlots)
      
      this.uploadedFiles.push(...filesToAdd)
      
      // Reset file input
      event.target.value = ''
      
      // Ha túl sok fájl
      if (files.length !== filesToAdd.length) {
        alert('Max. 5 fájl tölthető fel, és csak JPG, PNG, PDF formátumok (max. 10MB/fájl).')
      }
    },
    
    // Drag & drop
    handleDrop(event) {
      event.preventDefault()
      const files = Array.from(event.dataTransfer.files)
      const fileInput = { files: files }
      this.handleFileUpload({ target: fileInput })
    },
    
    // Fájl eltávolítása
    removeFile(index) {
      this.uploadedFiles.splice(index, 1)
    },
    
    // Fájlméret formázása
    formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes'
      const k = 1024
      const sizes = ['Bytes', 'KB', 'MB', 'GB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
    },
    
    // Évfolyam hozzáadása/eltávolítása
    toggleGrade(grade) {
      const index = this.event.selectedGrades.indexOf(grade)
      if (index === -1) {
        this.event.selectedGrades.push(grade)
      } else {
        this.event.selectedGrades.splice(index, 1)
      }
    },
    
    // Iskola neve
    getSchoolName(schoolId) {
      const school = this.schools.find(s => s.id == schoolId)
      return school ? `${school.name} (${school.city})` : 'Ismeretlen iskola'
    },
    
    // Esemény típus label
    getEventTypeLabel(type) {
      return this.eventTypes[type] || type
    },
    
    // Dátum formázása
    formatDate(dateString) {
      if (!dateString) return ''
      const date = new Date(dateString)
      return date.toLocaleDateString('hu-HU', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        weekday: 'long'
      })
    },
    
    // Form reset
    resetForm() {
      if (confirm('Biztosan törlöd az összes kitöltött adatot?')) {
        this.selectedSchool = ''
        this.event = {
          title: '',
          date: '',
          time: '',
          location: '',
          description: '',
          maxParticipants: null,
          type: '',
          conditions: {
            entireSchool: false,
            specificGrades: false,
            specificClasses: false
          },
          selectedGrades: []
        }
        this.uploadedFiles = []
      }
    },
    
    // Esemény létrehozása
    async createEvent() {
      if (!this.isFormValid) {
        alert('Kérjük, töltsd ki az összes kötelező mezőt!')
        return
      }
      
      // Adatok összeállítása
      const eventData = {
        school_id: this.selectedSchool,
        title: this.event.title,
        date: this.event.date,
        time: this.event.time,
        location: this.event.location,
        description: this.event.description,
        max_participants: this.event.maxParticipants,
        type: this.event.type,
        conditions: this.event.conditions,
        selected_grades: this.event.selectedGrades,
        files: this.uploadedFiles.map(file => ({
          name: file.name,
          size: file.size,
          type: file.type
        }))
      }
      
      console.log('Esemény adatok:', eventData)
      
      // Itt jönne a backend API hívás
      // try {
      //   const response = await axios.post('http://127.0.0.1:8000/api/events', eventData)
      //   alert('Esemény sikeresen létrehozva!')
      //   this.$router.push('/esemenyek')
      // } catch (error) {
      //   console.error('Hiba az esemény létrehozásakor:', error)
      //   alert('Hiba történt az esemény létrehozásakor.')
      // }
      
      // Demo
      alert('Esemény sikeresen létrehozva! (DEMO)')
      this.resetForm()
    }
  },
  
  mounted() {
    // Ha kell, betölthetjük az iskolákat backendről
    console.log('EventCreator komponens betöltve')
  }
}
</script>

<style scoped>
/* ====================
   ALAP STÍLUSOK
   ==================== */
.event-creator-page {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  box-sizing: border-box;
    font-family: "Poppins", sans-serif;

    display: flex;
    justify-content: center;
    align-items: center;
    
    min-height: 100vh;
    width: 99vw;
}

.back-btn {
  position: fixed;
  top: 20px;
  left: 20px;
  background: rgba(255, 255, 255, 0.9);
  border: none;
  padding: 10px 20px;
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
}

.back-btn:hover {
  background: white;
  transform: translateX(-5px);
}

.back-btn i {
  font-size: 20px;
}

/* ====================
   TARTALOM BURKOLÓ
   ==================== */
.event-creator-wrapper {
  max-width: 1400px;
  margin: 60px auto 40px;
  background: rgba(255, 255, 255, 0.95);
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  backdrop-filter: blur(10px);
}

/* ====================
   FEJLÉC
   ==================== */
.creator-header {
  background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
  color: white;
  padding: 30px 40px;
  text-align: center;
}

.creator-header h1 {
  font-size: 32px;
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 15px;
}

.creator-header h1 i {
  font-size: 40px;
}

.subtitle {
  opacity: 0.9;
  font-size: 16px;
}

/* ====================
   FŐ TARTALOM
   ==================== */
.creator-content {
  display: grid;
  grid-template-columns: 1.5fr 1fr;
  gap: 0;
  min-height: 800px;
}

/* ====================
   BAL OLDAL - ŰRLAP
   ==================== */
.form-side {
  padding: 40px;
  border-right: 1px solid #e0e0e0;
  background: white;
  overflow-y: auto;
  max-height: 800px;
}

.form-section {
  margin-bottom: 30px;
  padding-bottom: 25px;
  border-bottom: 1px solid #eee;
}

.form-section h3,
.form-section h4 {
  color: #333;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.form-section h3 i,
.form-section h4 i {
  color: #4f46e5;
}

/* Form elemek */
.form-group {
  margin-bottom: 25px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #555;
  font-size: 14px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

/* Input és select wrapper */
.input-wrapper,
.select-wrapper,
.textarea-wrapper {
  position: relative;
}

.input-wrapper i,
.select-wrapper i,
.textarea-wrapper i {
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: #667eea;
  font-size: 20px;
}

.textarea-wrapper i {
  top: 20px;
  transform: none;
}

.form-control {
  width: 100%;
  padding: 12px 15px 12px 45px;
  border: 2px solid #e0e0e0;
  border-radius: 10px;
  font-size: 16px;
  transition: all 0.3s;
  background: white;
}

.form-control:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

textarea.form-control {
  min-height: 120px;
  resize: vertical;
  padding-top: 15px;
}

.select-wrapper .form-control {
  appearance: none;
  padding-right: 40px;
}

/* Jelentkezési feltételek */
.conditions {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.condition-checkbox {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  padding: 10px;
  border-radius: 8px;
  transition: background 0.3s;
}

.condition-checkbox:hover {
  background: #f8f9fa;
}

.condition-checkbox input {
  width: 18px;
  height: 18px;
  cursor: pointer;
}

.condition-text {
  font-weight: 500;
}

/* Évfolyam gombok */
.grade-selection {
  margin-top: 15px;
  padding: 20px;
  background: #f8f9fa;
  border-radius: 10px;
}

.grade-selection label {
  display: block;
  margin-bottom: 15px;
  font-weight: 600;
}

.grade-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.grade-btn {
  padding: 8px 16px;
  border: 2px solid #ddd;
  background: white;
  border-radius: 20px;
  cursor: pointer;
  transition: all 0.3s;
  font-size: 14px;
}

.grade-btn:hover {
  border-color: #4f46e5;
}

.grade-btn.selected {
  background: #4f46e5;
  color: white;
  border-color: #4f46e5;
}

/* Fájl feltöltés */
.file-upload-area {
  border: 2px dashed #cbd5e1;
  border-radius: 10px;
  padding: 40px 20px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s;
  background: #f8fafc;
}

.file-upload-area:hover {
  border-color: #4f46e5;
  background: #f1f5f9;
}

.upload-content i {
  font-size: 48px;
  color: #94a3b8;
  margin-bottom: 15px;
}

.upload-content p {
  margin: 5px 0;
  color: #64748b;
}

.upload-info {
  font-size: 14px;
  color: #94a3b8;
}

/* Fájl lista */
.file-list {
  margin-top: 20px;
}

.file-list h5 {
  margin-bottom: 15px;
  color: #333;
}

.file-items {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.file-item {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 15px;
  background: #f8fafc;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
}

.file-icon i {
  font-size: 24px;
  color: #4f46e5;
}

.file-details {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.file-name {
  font-weight: 500;
  color: #334155;
}

.file-size {
  font-size: 12px;
  color: #64748b;
}

.remove-file-btn {
  background: none;
  border: none;
  color: #ef4444;
  cursor: pointer;
  padding: 5px;
  border-radius: 4px;
  transition: background 0.3s;
}

.remove-file-btn:hover {
  background: #fee2e2;
}

.remove-file-btn i {
  font-size: 20px;
}

/* Gombok */
.form-actions {
  display: flex;
  gap: 20px;
  margin-top: 40px;
  padding-top: 30px;
  border-top: 1px solid #e0e0e0;
}

.btn {
  flex: 1;
  padding: 16px 30px;
  border: none;
  border-radius: 10px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  transition: all 0.3s;
}

.btn-primary {
  background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(79, 70, 229, 0.3);
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-secondary {
  background: #f1f5f9;
  color: #475569;
  border: 2px solid #e2e8f0;
}

.btn-secondary:hover {
  background: #e2e8f0;
}

/* ====================
   JOBB OLDAL - ELŐNÉZET
   ==================== */
.preview-side {
  padding: 40px;
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  overflow-y: auto;
  max-height: 800px;
}

.preview-header {
  margin-bottom: 30px;
}

.preview-header h3 {
  color: #333;
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 5px;
}

.preview-header p {
  color: #64748b;
  font-size: 14px;
}

/* Előnézet kártya */
.preview-card {
  background: white;
  border-radius: 15px;
  padding: 30px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
  margin-bottom: 30px;
}

.preview-event-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 25px;
  padding-bottom: 20px;
  border-bottom: 1px solid #e2e8f0;
}

.preview-event-header h4 {
  font-size: 24px;
  color: #1e293b;
  margin: 0;
  flex: 1;
}

.event-type-badge {
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  color: white;
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 500;
}

/* Részletek */
.preview-details {
  margin-bottom: 25px;
}

.preview-detail-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 20px;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 15px;
}

.detail-item i {
  font-size: 24px;
  color: #6366f1;
  width: 30px;
}

.detail-label {
  display: block;
  font-size: 12px;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 4px;
}

.detail-value {
  display: block;
  font-weight: 600;
  color: #1e293b;
}

/* Leírás */
.preview-description {
  margin-bottom: 25px;
  padding-bottom: 20px;
  border-bottom: 1px solid #e2e8f0;
}

.preview-description h5 {
  color: #334155;
  margin-bottom: 10px;
  font-size: 16px;
}

.preview-description p {
  color: #475569;
  line-height: 1.6;
  white-space: pre-line;
}

/* Fájlok */
.preview-files {
  margin-bottom: 25px;
  padding-bottom: 20px;
  border-bottom: 1px solid #e2e8f0;
}

.preview-files h5 {
  color: #334155;
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 16px;
}

.files-count {
  background: #f1f5f9;
  padding: 8px 16px;
  border-radius: 20px;
  display: inline-block;
  font-size: 14px;
  color: #475569;
}

/* Feltételek */
.preview-conditions h5 {
  color: #334155;
  margin-bottom: 15px;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 16px;
}

.conditions-list {
  list-style: none;
  padding: 0;
}

.conditions-list li {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 0;
  color: #475569;
}

.conditions-list li i {
  color: #10b981;
}

.conditions-list li:last-child i {
  color: #ef4444;
}

/* Statisztika */
.preview-stats {
  display: flex;
  gap: 20px;
  margin-top: 25px;
  padding-top: 20px;
  border-top: 1px solid #e2e8f0;
}

.stat-item {
  flex: 1;
  text-align: center;
  padding: 15px;
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  border-radius: 10px;
}

.stat-value {
  display: block;
  font-size: 28px;
  font-weight: 700;
  color: #4f46e5;
  margin-bottom: 5px;
}

.stat-label {
  display: block;
  font-size: 12px;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 1px;
}

/* Segítség doboz */
.help-box {
  background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
  border-radius: 15px;
  padding: 25px;
  border-left: 5px solid #0ea5e9;
}

.help-box h5 {
  color: #0369a1;
  margin-bottom: 15px;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 18px;
}

.help-list {
  list-style: none;
  padding: 0;
}

.help-list li {
  padding: 8px 0;
  color: #475569;
  display: flex;
  align-items: center;
  gap: 10px;
}

.help-list li:before {
  content: "•";
  color: #0ea5e9;
  font-weight: bold;
  font-size: 20px;
}

/* ====================
   RESZPONZÍV DESIGN
   ==================== */
@media (max-width: 1200px) {
  .creator-content {
    grid-template-columns: 1fr;
  }
  
  .form-side {
    border-right: none;
    border-bottom: 1px solid #e0e0e0;
  }
}

@media (max-width: 768px) {
  .event-creator-wrapper {
    margin: 40px 10px;
  }
  
  .form-side,
  .preview-side {
    padding: 20px;
  }
  
  .creator-header {
    padding: 20px;
  }
  
  .creator-header h1 {
    font-size: 24px;
    flex-direction: column;
    gap: 10px;
  }
  
  .form-row,
  .preview-detail-row {
    grid-template-columns: 1fr;
    gap: 15px;
  }
  
  .form-actions {
    flex-direction: column;
  }
  
  .btn {
    width: 100%;
  }
  
  .back-btn {
    position: absolute;
    top: 10px;
    left: 10px;
  }
}

@media (max-width: 480px) {
  .event-creator-page {
    padding: 10px;
  }
  
  .grade-buttons {
    justify-content: center;
  }
  
  .grade-btn {
    flex: 1;
    min-width: 60px;
  }
}
</style>