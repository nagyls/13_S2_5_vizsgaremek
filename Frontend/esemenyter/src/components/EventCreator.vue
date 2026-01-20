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
          <!-- Intézmény kiválasztása -->
          <div class="form-section">
            <h3><i class='bx bx-building'></i> Intézmény kiválasztása</h3>
            <div class="form-group">
              <label for="establishment-select">Válassz intézményt:</label>
              <div class="select-wrapper">
                <i class='bx bx-school'></i>
                <select 
                  id="establishment-select" 
                  v-model="selectedEstablishment"
                  class="form-control"
                  required
                >
                  <option value="">-- válassz intézményt --</option>
                  <option v-for="establishment in establishments" :key="establishment.id" :value="establishment.id">
                    {{ establishment.title }} ({{ getSettlementName(establishment.settlement_id) }})
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
                <label for="start-date">Kezdés dátuma *</label>
                <div class="input-wrapper">
                  <i class='bx bx-calendar'></i>
                  <input 
                    id="start-date"
                    type="date" 
                    v-model="event.start_date" 
                    class="form-control"
                    required
                  >
                </div>
              </div>
              
              <div class="form-group">
                <label for="start-time">Kezdés időpontja *</label>
                <div class="input-wrapper">
                  <i class='bx bx-time'></i>
                  <input 
                    id="start-time"
                    type="time" 
                    v-model="event.start_time" 
                    class="form-control"
                    required
                  >
                </div>
              </div>
            </div>

            <!-- Befejezés dátuma -->
            <div class="form-row">
              <div class="form-group">
                <label for="end-date">Befejezés dátuma *</label>
                <div class="input-wrapper">
                  <i class='bx bx-calendar'></i>
                  <input 
                    id="end-date"
                    type="date" 
                    v-model="event.end_date" 
                    class="form-control"
                    required
                  >
                </div>
              </div>
              
              <div class="form-group">
                <label for="end-time">Befejezés időpontja *</label>
                <div class="input-wrapper">
                  <i class='bx bx-time-five'></i>
                  <input 
                    id="end-time"
                    type="time" 
                    v-model="event.end_time" 
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

            <!-- Esemény típusa -->
            <div class="form-group">
              <label for="event-type">Esemény típusa *</label>
              <div class="select-wrapper">
                <i class='bx bx-category'></i>
                <select 
                  id="event-type"
                  v-model="event.type" 
                  class="form-control"
                  required
                >
                  <option value="">-- válassz típust --</option>
                  <option value="local">Helyi esemény</option>
                  <option value="global">Globális esemény</option>
                </select>
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

            <!-- Tartalom (extra információk)
            <div class="form-group">
              <label for="event-content">További információk (JSON)</label>
              <div class="textarea-wrapper">
                <i class='bx bx-code'></i>
                <textarea 
                  id="event-content"
                  v-model="event.content" 
                  rows="3" 
                  placeholder='{"maxParticipants": 30, "requirements": "...", "notes": "..."}'
                  class="form-control"
                ></textarea>
              </div>
              <small class="form-hint">JSON formátumban add meg a további információkat</small>
            </div> -->

            <!-- Max résztvevők -->
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

            <!-- Megjelenítés kiknek -->
            <div class="form-section">
              <h4><i class='bx bx-show'></i> Megjelenítés kiknek</h4>
              <div class="conditions">
                <label class="condition-checkbox">
                  <input type="checkbox" v-model="event.showTo.entireEstablishment">
                  <span class="checkmark"></span>
                  <span class="condition-text">Egész intézmény számára</span>
                </label>
                
                <label class="condition-checkbox">
                  <input type="checkbox" v-model="event.showTo.specificClasses">
                  <span class="checkmark"></span>
                  <span class="condition-text">Csak adott osztályok</span>
                </label>
                
                <!-- Osztály választás -->
                <div v-if="event.showTo.specificClasses && selectedEstablishment" class="class-selection">
                  <label>Válassz osztályokat:</label>
                  <div class="class-checkboxes">
                    <label v-for="classItem in establishmentClasses" :key="classItem.id" class="class-checkbox">
                      <input 
                        type="checkbox" 
                        v-model="event.selectedClasses" 
                        :value="classItem.id"
                      >
                      <span class="checkmark"></span>
                      <span class="class-text">{{ classItem.name }} ({{ classItem.grade }}. évf.)</span>
                    </label>
                  </div>
                </div>
                
                <label class="condition-checkbox">
                  <input type="checkbox" v-model="event.showTo.specificUsers">
                  <span class="checkmark"></span>
                  <span class="condition-text">Csak adott felhasználók</span>
                </label>
                
                <!-- Felhasználó választás -->
                <div v-if="event.showTo.specificUsers && selectedEstablishment" class="user-selection">
                  <label>Válassz felhasználókat:</label>
                  <div class="user-search">
                    <div class="input-wrapper">
                      <i class='bx bx-search'></i>
                      <input 
                        type="text" 
                        v-model="userSearch" 
                        placeholder="Keresés felhasználók között..."
                        class="form-control"
                        @input="searchUsers"
                      >
                    </div>
                  </div>
                  <div class="user-checkboxes">
                    <label v-for="user in filteredUsers" :key="user.id" class="user-checkbox">
                      <input 
                        type="checkbox" 
                        v-model="event.selectedUsers" 
                        :value="user.id"
                      >
                      <span class="checkmark"></span>
                      <span class="user-text">{{ user.username }} ({{ user.email }})</span>
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <!-- Fájl feltöltés (content rész) -->
            <div class="form-group">
              <label for="file-upload">Dokumentumok feltöltése (content része)</label>
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
              <span class="event-type-badge" :class="event.type">
                {{ getEventTypeLabel(event.type) }}
              </span>
              <span class="event-status-badge" :class="event.status">
                {{ event.status === 'open' ? 'Nyitott' : 'Zárt' }}
              </span>
            </div>
            
            <!-- Esemény részletek -->
            <div class="preview-details">
              <div class="preview-detail-row">
                <div class="detail-item">
                  <i class='bx bx-calendar'></i>
                  <div>
                    <span class="detail-label">Kezdés</span>
                    <span class="detail-value">{{ formatDateTime(event.start_date, event.start_time) || "Nem megadva" }}</span>
                  </div>
                </div>
                
                <div class="detail-item">
                  <i class='bx bx-time-five'></i>
                  <div>
                    <span class="detail-label">Befejezés</span>
                    <span class="detail-value">{{ formatDateTime(event.end_date, event.end_time) || "Nem megadva" }}</span>
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
                <div v-if="selectedEstablishment" class="detail-item">
                  <i class='bx bx-building'></i>
                  <div>
                    <span class="detail-label">Intézmény</span>
                    <span class="detail-value">{{ getEstablishmentName(selectedEstablishment) }}</span>
                  </div>
                </div>
                
                <div class="detail-item">
                  <i class='bx bx-user'></i>
                  <div>
                    <span class="detail-label">Létrehozó</span>
                    <span class="detail-value">{{ currentUser.username }}</span>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Leírás -->
            <div v-if="event.description" class="preview-description">
              <h5>Leírás:</h5>
              <p>{{ event.description }}</p>
            </div>
            
            <!-- Tartalom (JSON) -->
            <div v-if="event.content" class="preview-content">
              <h5><i class='bx bx-code'></i> További információk</h5>
              <pre class="json-content">{{ formatJSONContent(event.content) }}</pre>
            </div>
            
            <!-- Megjelenítés kiknek -->
            <div class="preview-showto">
              <h5><i class='bx bx-show-alt'></i> Megjelenítve a következőknek:</h5>
              <ul class="showto-list">
                <li v-if="event.showTo.entireEstablishment">
                  <i class='bx bx-check'></i> Egész intézmény
                </li>
                <li v-if="event.showTo.specificClasses && event.selectedClasses.length > 0">
                  <i class='bx bx-check'></i> {{ event.selectedClasses.length }} osztály
                </li>
                <li v-if="event.showTo.specificUsers && event.selectedUsers.length > 0">
                  <i class='bx bx-check'></i> {{ event.selectedUsers.length }} felhasználó
                </li>
                <li v-if="!event.showTo.entireEstablishment && !event.showTo.specificClasses && !event.showTo.specificUsers">
                  <i class='bx bx-x'></i> Nincs megadva, kiknek jelenjen meg
                </li>
              </ul>
            </div>
            
            <!-- Fájlok -->
            <div v-if="uploadedFiles.length > 0" class="preview-files">
              <h5><i class='bx bx-paperclip'></i> Csatolt fájlok (content része)</h5>
              <div class="files-count">
                {{ uploadedFiles.length }} fájl
              </div>
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
              <div class="stat-item">
                <span class="stat-value">{{ event.status === 'open' ? 'Nyitott' : 'Zárt' }}</span>
                <span class="stat-label">Státusz</span>
              </div>
            </div>
          </div>
          
          <!-- Adatbázis szerkezet info -->
          <div class="database-info">
            <h5><i class='bx bx-data'></i> Adatbázis szerkezet</h5>
            <div class="db-structure">
              <div class="db-field">
                <span class="db-field-name">event.type</span>
                <span class="db-field-value">ENUM('local', 'global')</span>
              </div>
              <div class="db-field">
                <span class="db-field-name">event.status</span>
                <span class="db-field-value">ENUM('open', 'closed')</span>
              </div>
              <div class="db-field">
                <span class="db-field-name">event_shown_to</span>
                <span class="db-field-value">Kapcsolótábla</span>
              </div>
            </div>
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
      // Kiválasztott intézmény
      selectedEstablishment: '',
      
      // Esemény adatok az SQL séma szerint
      event: {
        title: '',
        description: '',
        content: '',
        type: 'local',
        status: 'open',
        start_date: '',
        start_time: '',
        end_date: '',
        end_time: '',
        location: '',
        maxParticipants: null,
        
        // Megjelenítés kiknek
        showTo: {
          entireEstablishment: true,
          specificClasses: false,
          specificUsers: false
        },
        selectedClasses: [],
        selectedUsers: []
      },
      
      // Feltöltött fájlok (content rész)
      uploadedFiles: [],
      
      // Intézmények (demo adatok)
      establishments: [
        { id: 1, title: 'Kossuth Lajos Gimnázium', settlement_id: 1 },
        { id: 2, title: 'Petőfi Sándor Általános Iskola', settlement_id: 1 },
        { id: 3, title: 'Bolyai János Gimnázium', settlement_id: 2 },
        { id: 4, title: 'Arany János Általános Iskola', settlement_id: 3 }
      ],
      
      // Települések (demo)
      settlements: [
        { id: 1, title: 'Budapest' },
        { id: 2, title: 'Miskolc' },
        { id: 3, title: 'Szeged' }
      ],
      
      // Osztályok a kiválasztott intézményhez
      establishmentClasses: [],
      
      // Felhasználók
      users: [],
      filteredUsers: [],
      userSearch: '',
      
      // Jelenlegi felhasználó
      currentUser: {
        id: 1,
        username: 'admin_user',
        email: 'admin@example.com'
      },
      
      // Esemény típusok leírása
      eventTypes: {
        local: 'Helyi esemény',
        global: 'Globális esemény'
      }
    }
  },
  
  computed: {
    // Form validáció
    isFormValid() {
      return (
        this.selectedEstablishment &&
        this.event.title.trim() &&
        this.event.type &&
        this.event.start_date &&
        this.event.start_time &&
        this.event.end_date &&
        this.event.end_time &&
        this.event.location.trim()
      )
    }
  },
  
  watch: {
    // Amikor intézmény változik, betöltjük az osztályait
    selectedEstablishment(newVal) {
      if (newVal) {
        this.loadEstablishmentClasses(newVal)
        this.loadEstablishmentUsers(newVal)
      }
    }
  },
  
  methods: {
    // Település neve
    getSettlementName(settlementId) {
      const settlement = this.settlements.find(s => s.id === settlementId)
      return settlement ? settlement.title : 'Ismeretlen'
    },
    
    // Intézmény neve
    getEstablishmentName(establishmentId) {
      const establishment = this.establishments.find(e => e.id == establishmentId)
      return establishment ? `${establishment.title} (${this.getSettlementName(establishment.settlement_id)})` : 'Ismeretlen intézmény'
    },
    
    // Intézmény osztályainak betöltése
    loadEstablishmentClasses(establishmentId) {
      // Demo adatok
      this.establishmentClasses = [
        { id: 1, name: '9.A', grade: 9, establishment_id: establishmentId },
        { id: 2, name: '9.B', grade: 9, establishment_id: establishmentId },
        { id: 3, name: '10.A', grade: 10, establishment_id: establishmentId },
        { id: 4, name: '10.B', grade: 10, establishment_id: establishmentId },
        { id: 5, name: '11.A', grade: 11, establishment_id: establishmentId },
        { id: 6, name: '12.A', grade: 12, establishment_id: establishmentId }
      ].filter(c => c.establishment_id == establishmentId)
    },
    
    // Intézmény felhasználóinak betöltése
    loadEstablishmentUsers(establishmentId) {
      // Demo adatok
      this.users = [
        { id: 1, username: 'tanar1', email: 'tanar1@example.com', role: 'teacher' },
        { id: 2, username: 'tanar2', email: 'tanar2@example.com', role: 'teacher' },
        { id: 3, username: 'diak1', email: 'diak1@example.com', role: 'student' },
        { id: 4, username: 'diak2', email: 'diak2@example.com', role: 'student' },
        { id: 5, username: 'diak3', email: 'diak3@example.com', role: 'student' },
        { id: 6, username: 'szulo1', email: 'szulo1@example.com', role: 'parent' }
      ]
      this.filteredUsers = [...this.users]
    },
    
    // Felhasználók keresése
    searchUsers() {
      if (!this.userSearch.trim()) {
        this.filteredUsers = [...this.users]
        return
      }
      
      const searchTerm = this.userSearch.toLowerCase()
      this.filteredUsers = this.users.filter(user =>
        user.username.toLowerCase().includes(searchTerm) ||
        user.email.toLowerCase().includes(searchTerm)
      )
    },
    
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
        const isValidType = ['image/jpeg', 'image/png', 'application/pdf', 'text/plain', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'].includes(file.type)
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
        alert('Max. 5 fájl tölthető fel, és csak támogatott formátumok (max. 10MB/fájl).')
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
    
    // Esemény típus label
    getEventTypeLabel(type) {
      return this.eventTypes[type] || type
    },
    
    // Dátum és idő formázása
    formatDateTime(dateString, timeString) {
      if (!dateString || !timeString) return ''
      const date = new Date(`${dateString}T${timeString}:00`)
      return date.toLocaleString('hu-HU', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        weekday: 'short'
      })
    },
    
    // JSON content formázása
    formatJSONContent(content) {
      try {
        const parsed = JSON.parse(content)
        return JSON.stringify(parsed, null, 2)
      } catch (e) {
        return content
      }
    },
    
    // Form reset
    resetForm() {
      if (confirm('Biztosan törlöd az összes kitöltött adatot?')) {
        this.selectedEstablishment = ''
        this.event = {
          title: '',
          description: '',
          content: '',
          type: 'local',
          status: 'open',
          start_date: '',
          start_time: '',
          end_date: '',
          end_time: '',
          location: '',
          maxParticipants: null,
          showTo: {
            entireEstablishment: true,
            specificClasses: false,
            specificUsers: false
          },
          selectedClasses: [],
          selectedUsers: []
        }
        this.uploadedFiles = []
        this.establishmentClasses = []
        this.filteredUsers = []
        this.userSearch = ''
      }
    },
    
    // Esemény létrehozása
    async createEvent() {
      if (!this.isFormValid) {
        alert('Kérjük, töltsd ki az összes kötelező mezőt!')
        return
      }
      
      // Content összeállítása
      let contentObj = {}
      try {
        if (this.event.content) {
          contentObj = JSON.parse(this.event.content)
        }
      } catch (e) {
        alert('Érvénytelen JSON formátum a tartalom mezőben!')
        return
      }
      
      // Fájlok hozzáadása a contenthez
      if (this.uploadedFiles.length > 0) {
        contentObj.files = this.uploadedFiles.map(file => ({
          name: file.name,
          size: file.size,
          type: file.type,
          lastModified: file.lastModified
        }))
      }
      
      // Max résztvevők hozzáadása
      if (this.event.maxParticipants) {
        contentObj.maxParticipants = this.event.maxParticipants
      }
      
      // Helyszín hozzáadása
      contentObj.location = this.event.location
      
      // Adatbázis kompatibilis esemény adatok
      const eventData = {
        title: this.event.title,
        description: this.event.description,
        content: JSON.stringify(contentObj),
        type: this.event.type,
        status: 'open', // Mindig open-ként jön létre
        user_id: this.currentUser.id, // Létrehozó felhasználó
        start_date: `${this.event.start_date} ${this.event.start_time}:00`,
        end_date: `${this.event.end_date} ${this.event.end_time}:00`,
        created_at: new Date().toISOString().slice(0, 19).replace('T', ' ')
      }
      
      console.log('Esemény adatok (adatbázis):', eventData)
      
      // Megjelenítés kiknek
      const showToData = []
      
      if (this.event.showTo.entireEstablishment) {
        // Egész intézmény -> minden osztályt hozzáadunk
        this.establishmentClasses.forEach(classItem => {
          showToData.push({
            event_id: null, // Ezt majd a backend állítja be
            class_id: classItem.id,
            user_id: null
          })
        })
      }
      
      if (this.event.showTo.specificClasses && this.event.selectedClasses.length > 0) {
        this.event.selectedClasses.forEach(classId => {
          showToData.push({
            event_id: null,
            class_id: classId,
            user_id: null
          })
        })
      }
      
      if (this.event.showTo.specificUsers && this.event.selectedUsers.length > 0) {
        this.event.selectedUsers.forEach(userId => {
          showToData.push({
            event_id: null,
            class_id: null,
            user_id: userId
          })
        })
      }
      
      console.log('Megjelenítés adatok:', showToData)
      
      // Itt jönne a backend API hívás
      // try {
      //   // 1. Esemény létrehozása
      //   const eventResponse = await axios.post('http://127.0.0.1:8000/api/events', eventData)
      //   const eventId = eventResponse.data.id
      //   
      //   // 2. Megjelenítés adatok küldése
      //   if (showToData.length > 0) {
      //     const showToPromises = showToData.map(data => {
      //       data.event_id = eventId
      //       return axios.post('http://127.0.0.1:8000/api/event-shown-to', data)
      //     })
      //     await Promise.all(showToPromises)
      //   }
      //   
      //   alert('Esemény sikeresen létrehozva!')
      //   this.$router.push('/esemenyek')
      // } catch (error) {
      //   console.error('Hiba az esemény létrehozásakor:', error)
      //   alert('Hiba történt az esemény létrehozásakor.')
      // }
      
      // Demo
      alert('Esemény sikeresen létrehozva! (DEMO - Adatbázis kompatibilis)')
      console.log('Létrehozott esemény:', eventData)
      console.log('Megjelenítés adatok:', showToData)
      this.resetForm()
    }
  },
  
  mounted() {
    console.log('EventCreator komponens betöltve - SQL séma kompatibilis')
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

.form-hint {
  display: block;
  margin-top: 5px;
  color: #666;
  font-size: 12px;
  font-style: italic;
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

/* Megjelenítés kiknek */
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

/* Osztály választás */
.class-selection,
.user-selection {
  margin-top: 15px;
  padding: 20px;
  background: #f8f9fa;
  border-radius: 10px;
}

.class-selection label,
.user-selection label {
  display: block;
  margin-bottom: 15px;
  font-weight: 600;
}

.class-checkboxes,
.user-checkboxes {
  display: flex;
  flex-direction: column;
  gap: 10px;
  max-height: 200px;
  overflow-y: auto;
}

.class-checkbox,
.user-checkbox {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 12px;
  background: white;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.3s;
}

.class-checkbox:hover,
.user-checkbox:hover {
  background: #f1f5f9;
}

.class-checkbox input,
.user-checkbox input {
  width: 16px;
  height: 16px;
  cursor: pointer;
}

.class-text,
.user-text {
  font-size: 14px;
}

.user-search {
  margin-bottom: 15px;
}

.user-search .input-wrapper i {
  left: 12px;
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
  flex-wrap: wrap;
  align-items: center;
  gap: 10px;
  margin-bottom: 25px;
  padding-bottom: 20px;
  border-bottom: 1px solid #e2e8f0;
}

.preview-event-header h4 {
  font-size: 24px;
  color: #1e293b;
  margin: 0;
  flex: 1;
  min-width: 200px;
}

.event-type-badge,
.event-status-badge {
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 500;
}

.event-type-badge.local {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  color: white;
}

.event-type-badge.global {
  background: linear-gradient(135deg, #10b981 0%, #047857 100%);
  color: white;
}

.event-status-badge {
  background: #f1f5f9;
  color: #475569;
}

.event-status-badge.open {
  background: linear-gradient(135deg, #10b981 0%, #047857 100%);
  color: white;
}

.event-status-badge.closed {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
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

/* Tartalom (JSON) */
.preview-content {
  margin-bottom: 25px;
  padding-bottom: 20px;
  border-bottom: 1px solid #e2e8f0;
}

.preview-content h5 {
  color: #334155;
  margin-bottom: 10px;
  font-size: 16px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.json-content {
  background: #1e293b;
  color: #e2e8f0;
  padding: 15px;
  border-radius: 8px;
  font-size: 13px;
  overflow-x: auto;
  font-family: 'Courier New', monospace;
  max-height: 200px;
  overflow-y: auto;
}

/* Megjelenítés kiknek */
.preview-showto {
  margin-bottom: 25px;
  padding-bottom: 20px;
  border-bottom: 1px solid #e2e8f0;
}

.preview-showto h5 {
  color: #334155;
  margin-bottom: 15px;
  font-size: 16px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.showto-list {
  list-style: none;
  padding: 0;
}

.showto-list li {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 0;
  color: #475569;
}

.showto-list li i {
  color: #10b981;
}

.showto-list li:last-child i {
  color: #ef4444;
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

/* Adatbázis info */
.database-info {
  background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
  border-radius: 15px;
  padding: 25px;
  border-left: 5px solid #0ea5e9;
}

.database-info h5 {
  color: #0369a1;
  margin-bottom: 15px;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 18px;
}

.db-structure {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.db-field {
  display: flex;
  justify-content: space-between;
  padding: 8px 12px;
  background: white;
  border-radius: 6px;
  border-left: 3px solid #0ea5e9;
}

.db-field-name {
  font-family: 'Courier New', monospace;
  font-weight: 600;
  color: #1e293b;
}

.db-field-value {
  font-family: 'Courier New', monospace;
  font-size: 12px;
  color: #64748b;
  background: #f1f5f9;
  padding: 2px 8px;
  border-radius: 4px;
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
  
  .preview-event-header {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .preview-event-header h4 {
    min-width: auto;
  }
}

@media (max-width: 480px) {
  .event-creator-page {
    padding: 10px;
  }
  
  .class-checkboxes,
  .user-checkboxes {
    max-height: 150px;
  }
}
</style>