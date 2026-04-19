<template>
  <div class="event-creator-page">
    <button class="back-button" @click="$router.back()">
      <i class='bx bx-arrow-back'></i> Vissza
    </button>

    <div class="event-creator-wrapper">
      <div class="creator-header">
        <h1><i class='bx bx-calendar-plus'></i> Új esemény létrehozása</h1>
        <p class="subtitle">{{ roleMessage }}</p>
        <div class="header-badges">
          <span class="header-badge"><i class='bx bx-shield-quarter'></i> Jogosultságvezérelt</span>
          <span class="header-badge"><i class='bx bx-layer'></i> 5 lépéses felület</span>
          <span class="header-badge"><i class='bx bx-wand'></i> Modern eseménylétrehozás</span>
        </div>
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
        <!-- Lépésjelző navigáció (Stepper) -->
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

        <!-- 1. ESEMÉNY SZINTJÉNEK KIVÁLASZTÁSA (Globális vagy Iskolai) -->
        <div v-if="currentStep === 1" class="form-section">
          <h3><i class='bx bx-layer'></i> 1. Esemény szintjének kiválasztása</h3>
          <div class="type-selection">
            <!-- Globális opció - Csak adminisztrátori jogosultsággal jelenik meg -->
              <label v-if="userRole === 'admin'" 
                   class="type-option" 
                  :class="{ 'selected': selectedEventScope === 'global' }">
                <input type="radio" v-model="selectedEventScope" value="global" hidden>
              <div class="option-content">
                <i class='bx bx-globe'></i>
                <h4>Globális szintű esemény</h4>
                <p>Több intézménybe is kiküldhető vármegyei alapú esemény</p>
                <span class="permission-badge">Csak adminoknak</span>
              </div>
            </label>

            <!-- Iskolai opció - Tanároknak és adminoknak is elérhető -->
            <label class="type-option" 
              :class="{ 'selected': selectedEventScope === 'school' }">
                <input type="radio" v-model="selectedEventScope" value="school" hidden>
              <div class="option-content">
                <i class='bx bx-building'></i>
                <h4>Iskolai szintű esemény</h4>
                <p>Saját intézményeden belüli esemény, ismétlődés lehetőséggel</p>
              </div>
            </label>
          </div>

          <!-- Információs blokk az ismétlődő eseményekről -->
          <div class="recurrence-guide">
            <i class='bx bx-info-circle'></i>
            <span>
              Ismétlődő (heti) eseményt az <strong>Iskolai szintű esemény</strong> kiválasztása után,
              a <strong>3. lépésben</strong> tudsz bekapcsolni a
              <strong>"Heti ismétlődő esemény"</strong> kapcsolóval.
            </span>
          </div>
        </div>

        <!-- 2. GLOBÁLIS ESEMÉNY - VÁRMEGYÉK KIVÁLASZTÁSA -->
        <div v-if="currentStep === 2 && selectedEventScope === 'global'" class="form-section global-county-section">
          <h3><i class='bx bx-map'></i> 2/A. Vármegyék kiválasztása</h3>
          <p class="selection-description">Válaszd ki, mely vármegyék iskolái kapják meg az eseményt. A saját vármegyéd automatikusan kijelölésre kerül.</p>
          
          <!-- Vármegye választó lista görgethető felülettel -->
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
                    (kötelező saját vármegye)
                  </span>
                </span>
              </label>
              <span class="school-count">{{ county.schoolCount }} rögzített intézmény</span>
            </div>
          </div>

          <div class="selected-info" v-if="selectedCountyIds.length > 0">
            <i class='bx bx-check-circle'></i>
            <span>{{ selectedCountyIds.length }} vármegye kiválasztva</span>
          </div>

          <div v-if="selectedCountyIds.length > 0 && isCheckingGlobalCollab" class="class-target-state">
            Intézmények ellenőrzése a kiválasztott vármegyékben...
          </div>

          <div v-if="selectedCountyIds.length > 0 && !isCheckingGlobalCollab && globalCollabCount === 0" class="class-target-state warning">
            A kiválasztott vármegyékben nem található másik intézmény. Válassz további vármegyét.
          </div>
        </div>

        <div v-if="currentStep === 2 && selectedEventScope === 'global'" class="step-split-hint">
          <i class='bx bx-separate'></i>
          <span>Következő: célcsoport beállítása a saját intézményeden belül</span>
        </div>

        <!-- 3. CÉLCSOPORT MEGHATÁROZÁSA (Saját intézményen belül) -->
        <div v-if="currentStep === 3" class="form-section" :class="{ 'global-target-section': selectedEventScope === 'global' }">
          <h3><i class='bx bx-target-lock'></i> 3. Célcsoport kiválasztása</h3>

          <!-- Globális esemény esetén külön tájékoztató -->
          <div v-if="selectedEventScope === 'global'" class="scope-notice">
            <i class='bx bx-info-circle'></i>
            <span>
              Ez a beállítás határozza meg, hogy a saját iskolád diákjai közül kik lássák az eseményt.
              A globális esemény minden választott vármegyei iskolába eljut, de ott intézményi szintű lesz a szűrés.
            </span>
          </div>
          
          <div class="target-group-selection">
            <div class="target-group-options">
              <div v-for="target in schoolTargetOptions" 
                     :key="target.value"
                     class="target-group-option"
                     :class="{ 'selected': selectedSchoolTargetGroup === target.value }"
                     @click="selectSchoolTargetGroup(target.value)">
                <div class="option-content">
                  <i :class="target.icon"></i>
                  <h4>{{ target.label }}</h4>
                  <p>{{ target.description }}</p>
                </div>

                <!-- Osztály-szintű célzás -->
                <div
                  v-if="selectedSchoolTargetGroup === target.value && target.value === 'osztaly_szintu'"
                  class="target-selector-panel"
                  @click.stop
                >
                  <div class="selector-toolbar">
                    <div class="class-target-help">
                      Válaszd ki a címzett osztályokat, vagy jelöld be egyszerre az összeset.
                    </div>
                    <div class="bulk-actions">
                      <button type="button" class="selector-btn" @click="selectAllClasses">
                        Összes osztály
                      </button>
                      <button type="button" class="selector-btn secondary" @click="clearSelectedClasses">
                        Törlés
                      </button>
                    </div>
                  </div>

                  <div v-if="isLoadingClasses" class="class-target-state">
                    Osztályok betöltése...
                  </div>

                  <div v-else-if="!institutionClasses.length" class="class-target-state warning">
                    Nem található osztály az intézményben. Kérjük, hozzon létre legalább egy osztályt.
                  </div>

                  <div v-else class="class-target-grid">
                    <label
                      v-for="classItem in institutionClasses"
                      :key="classItem.id"
                      class="class-target-card"
                      :class="{ selected: selectedClassIds.includes(Number(classItem.id)) }"
                    >
                      <input
                        type="checkbox"
                        :checked="selectedClassIds.includes(Number(classItem.id))"
                        @change="toggleClassSelection(classItem.id)"
                      >
                      <div class="class-target-copy">
                        <div class="class-target-title">{{ formatClassLabel(classItem) }}</div>
                        <div class="class-target-meta">{{ classItem.student_count || 0 }} tanuló</div>
                      </div>
                    </label>
                  </div>

                  <div v-if="selectedClassIds.length" class="selected-info compact">
                    <i class='bx bx-check-circle'></i>
                    <span>{{ selectedClassIds.length }} osztály kijelölve</span>
                  </div>
                </div>

                <!-- Évfolyam-szintű célzás -->
                <div
                  v-if="selectedSchoolTargetGroup === target.value && target.value === 'evfolyam_szintu'"
                  class="target-selector-panel"
                  @click.stop
                >
                  <div class="selector-toolbar">
                    <div class="class-target-help">
                      Válaszd ki a címzett évfolyamokat, vagy jelöld be egyszerre az összeset.
                    </div>
                    <div class="bulk-actions">
                      <button type="button" class="selector-btn" @click="selectAllGrades">
                        Összes évfolyam
                      </button>
                      <button type="button" class="selector-btn secondary" @click="clearSelectedGrades">
                        Törlés
                      </button>
                    </div>
                  </div>

                  <div v-if="isLoadingGrades" class="class-target-state">
                    Évfolyamok betöltése...
                  </div>

                  <div v-else-if="!institutionGrades.length" class="class-target-state warning">
                    Nem található évfolyam az intézményben.
                  </div>

                  <div v-else class="class-target-grid">
                    <label
                      v-for="gradeItem in institutionGrades"
                      :key="gradeItem.grade"
                      class="class-target-card"
                      :class="{ selected: selectedGradeIds.includes(Number(gradeItem.grade)) }"
                    >
                      <input
                        type="checkbox"
                        :checked="selectedGradeIds.includes(Number(gradeItem.grade))"
                        @change="toggleGradeSelection(gradeItem.grade)"
                      >
                      <div class="class-target-copy">
                        <div class="class-target-title">{{ getGradeLabel(gradeItem.grade) }}</div>
                        <div class="class-target-meta">{{ gradeItem.class_count || 0 }} osztály</div>
                      </div>
                    </label>
                  </div>

                  <div v-if="selectedGradeIds.length" class="selected-info compact">
                    <i class='bx bx-check-circle'></i>
                    <span>{{ selectedGradeIds.length }} évfolyam kijelölve</span>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="selectedSchoolTargetGroup === 'teljes_iskola'" class="class-target-help success school-wide-info">
              A teljes intézmény minden felhasználója megkapja az eseményt.
            </div>
          </div>
        </div>

        <!-- 4. ESEMÉNY RÉSZLETES ADATAI -->
        <div v-if="currentStep === 4" class="form-section">
          <h3><i class='bx bx-edit'></i> 4. Esemény adatai</h3>
          <div v-if="selectedEventScope === 'school'" class="recurrence-guide compact">
            <i class='bx bx-repeat'></i>
            <span>Ebben a lépésben konfigurálhatja az egyszeri vagy a heti rendszerességű időpontokat.</span>
          </div>
          <div class="event-form">
            <div class="form-group">
              <label>Esemény neve *</label>
              <input v-model="eventForm.title" placeholder="Pl: Iskolai sportnap" required>
            </div>
            <div class="form-group">
              <label>Rövid leírás *</label>
              <textarea v-model="eventForm.description" placeholder="Az esemény lényege pár mondatban" required></textarea>
            </div>
            <div class="form-group">
              <label>Részletes tartalom (opcionális)</label>
              <textarea v-model="eventForm.content" placeholder="Minden egyéb fontos információ, instrukciók"></textarea>
            </div>

            <!-- IDŐPONTOK: Csak akkor látszik, ha NEM ismétlődő az esemény -->
            <div class="form-row" v-if="!(selectedEventScope === 'school' && eventForm.isRecurring)">
              <div class="form-group">
                <label>Kezdés dátuma *</label>
                <input
                  type="date"
                  v-model="eventForm.startDate"
                  :min="todayMin"
                  @input="updateStartDateTimeFromParts"
                  @change="updateStartDateTimeFromParts"
                  required
                >
              </div>
              <div class="form-group">
                <label>Kezdés időpontja *</label>
                <!-- Firefox alatt sima text inputot használunk a megbízhatóság érdekében -->
                <input
                  :type="isFirefoxBrowser ? 'text' : 'time'"
                  v-model="eventForm.startTime"
                  inputmode="numeric"
                  placeholder="HH:MM"
                  pattern="^([01]?\d|2[0-3]):[0-5]\d$"
                  step="60"
                  @input="updateStartDateTimeFromParts"
                  @change="updateStartDateTimeFromParts"
                  @blur="normalizeEventFormTime('startTime', updateStartDateTimeFromParts)"
                  required
                >
              </div>
              <div class="form-group">
                <label>Befejezés dátuma *</label>
                <input
                  type="date"
                  v-model="eventForm.endDate"
                  :min="eventForm.startDate || todayMin"
                  @input="updateEndDateTimeFromParts"
                  @change="updateEndDateTimeFromParts"
                  required
                >
              </div>
              <div class="form-group">
                <label>Befejezés időpontja *</label>
                <input
                  :type="isFirefoxBrowser ? 'text' : 'time'"
                  v-model="eventForm.endTime"
                  inputmode="numeric"
                  placeholder="HH:MM"
                  pattern="^([01]?\d|2[0-3]):[0-5]\d$"
                  step="60"
                  :min="eventForm.endDate === eventForm.startDate ? eventForm.startTime : ''"
                  @input="updateEndDateTimeFromParts"
                  @change="updateEndDateTimeFromParts"
                  @blur="normalizeEventFormTime('endTime', updateEndDateTimeFromParts)"
                  required
                >
              </div>
            </div>

            <!-- ISMÉTLŐDÉS KAPCSOLÓ: Globális eseménynél nem elérhető -->
            <div v-if="selectedEventScope === 'school'" class="form-group recurrence-toggle">
              <label class="checkbox-label">
                <input type="checkbox" v-model="eventForm.isRecurring">
                <span class="checkbox-copy">
                  <span class="checkbox-title">Heti ismétlődő esemény</span>
                  <span class="checkbox-description">Kapcsolja be rendszeres alkalmak (pl. minden keddi szakkör) esetén.</span>
                </span>
              </label>
            </div>

            <!-- Heti ismétlődő esemény beállításai -->
            <div v-if="selectedEventScope === 'school' && eventForm.isRecurring" class="recurrence-flow">
              <div class="recurrence-step">
                <label>1. Melyik nap legyen az esemény? *</label>
                <select v-model="eventForm.recurrenceWeekday" required>
                  <option v-for="option in recurrenceWeekdayOptions" :key="option.value" :value="option.value">
                    {{ option.label }}
                  </option>
                </select>
              </div>

              <div class="recurrence-step">
                <label>2. Hánykor kezdődjön? *</label>
                <input
                  :type="isFirefoxBrowser ? 'text' : 'time'"
                  v-model="eventForm.recurrenceStartTime"
                  inputmode="numeric"
                  placeholder="HH:MM"
                  pattern="^([01]?\d|2[0-3]):[0-5]\d$"
                  @blur="normalizeEventFormTime('recurrenceStartTime')"
                  required
                >
              </div>

              <div class="recurrence-step">
                <label>3. Meddig tartson? (perc) *</label>
                <input
                  type="number"
                  v-model.number="eventForm.recurrenceDurationMinutes"
                  min="15"
                  step="5"
                  required
                >
              </div>

              <div class="recurrence-step">
                <label>4. Melyik dátumkor induljon az esemény? *</label>
                <input
                  type="date"
                  v-model="eventForm.recurrenceStartDate"
                  :min="todayMin.slice(0, 10)"
                  required
                >
                <p class="field-help">A rendszer az első alkalmat a kiválasztott naphoz igazítja.</p>
              </div>

              <div class="recurrence-step">
                <label>5. Meddig ismétlődjön az esemény? *</label>
                <input
                  type="date"
                  v-model="eventForm.recurrenceUntil"
                  :min="eventForm.recurrenceStartDate || todayMin.slice(0, 10)"
                  required
                >
                <p class="field-help">Az esemény hetente ismétlődik a megadott dátumig.</p>
              </div>
            </div>
          </div>
        </div>

        <!-- 5. ÁTTEKINTÉS ÉS MENTÉS -->
        <div v-if="currentStep === 5" class="form-section">
          <h3><i class='bx bx-check-circle'></i> 5. Áttekintés</h3>
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
              <div class="summary-item">
                <strong>Célcsoport:</strong> {{ getSchoolTargetLabel(selectedSchoolTargetGroup) }}
              </div>
              <div v-if="selectedSchoolTargetGroup !== 'teljes_iskola'" class="summary-item">
                <strong>Kijelölés:</strong> {{ selectedTargetSummary || 'Nincs kiválasztva' }}
              </div>
            </template>
            
            <!-- Iskolai esemény részletei -->
            <template v-else>
              <div class="summary-item">
                <strong>Célcsoport:</strong> {{ getSchoolTargetLabel(selectedSchoolTargetGroup) }}
              </div>
              <div v-if="selectedSchoolTargetGroup !== 'teljes_iskola'" class="summary-item">
                <strong>Kijelölés:</strong> {{ selectedTargetSummary || 'Nincs kiválasztva' }}
              </div>
              <div class="summary-item">
                <strong>Intézmény:</strong> {{ userInstitution.name }}
              </div>
            </template>

            <div class="summary-item">
              <strong>Cím:</strong> {{ eventForm.title || '(nincs megadva)' }}
            </div>

            <!-- Ismétlődő esemény összegzése -->
            <template v-if="selectedEventScope === 'school' && eventForm.isRecurring">
              <div class="summary-item">
                <strong>Nap:</strong> {{ getWeekdayLabel(eventForm.recurrenceWeekday) }}
              </div>
              <div class="summary-item">
                <strong>Kezdés:</strong> {{ eventForm.recurrenceStartTime || 'nincs megadva' }}
              </div>
              <div class="summary-item">
                <strong>Első alkalom:</strong> {{ formatDateTime(buildRecurringStartDateTime()) }}
              </div>
              <div class="summary-item">
                <strong>Alkalom vége:</strong> {{ formatDateTime(buildRecurringEndDateTime()) }}
              </div>
              <div class="summary-item">
                <strong>Időtartam:</strong> {{ eventForm.recurrenceDurationMinutes }} perc
              </div>
              <div class="summary-item">
                <strong>Ismétlődés:</strong> Hetente, {{ formatDateTime(eventForm.recurrenceUntil + 'T00:00') }} dátumig
              </div>
            </template>

            <!-- Egyszeri esemény összegzése -->
            <template v-else>
              <div class="summary-item">
                <strong>Kezdés:</strong> {{ formatDateTime(eventForm.startDateTime) }}
              </div>
              <div class="summary-item">
                <strong>Befejezés:</strong> {{ formatDateTime(eventForm.endDateTime) }}
              </div>
            </template>
          </div>
        </div>

        <!-- Navigációs gombok -->
        <div class="form-actions">
          <button @click="previousStep" :disabled="currentStep === 1" class="btn btn-secondary">
            <i class='bx bx-chevron-left'></i> Előző
          </button>
          
          <button v-if="currentStep < 5" @click="nextStep" :disabled="!canProceed" class="btn btn-primary">
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
import axios from 'axios'
import { toast } from '../../services/toast'
import { API_BASE, getToken, hasLocalToken } from '../../services/api'

export default {
  name: 'EsemenyKeszito',

  data() {
    const today = new Date().toISOString().slice(0, 10)

    return {
      userRole: 'student',
      currentUserId: null,
      userInstitution: {
        id: null,
        name: 'Kossuth Lajos Gimnázium',
        countyId: null
      },
      userCountyId: null,
      institutionUserIds: [],
      institutionClasses: [],
      institutionGrades: [],
      selectedClassIds: [],
      selectedGradeIds: [],
      isLoadingClasses: false,
      isLoadingGrades: false,
      currentStep: 1,
      isSubmitting: false,
      isFirefoxBrowser: false,
      todayMin: today,
      nowMin: new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 16),
      selectedEventScope: 'school',
      selectedCountyIds: [],
      globalCollabCount: 0,
      globalCollabIds: [],
      isCheckingGlobalCollab: false,
      countiesList: [],
      selectedSchoolTargetGroup: 'osztaly_szintu',
      eventForm: {
        title: '',
        description: '',
        content: '',
        startDateTime: '',
        endDateTime: '',
        startDate: today,
        startTime: '',
        endDate: '',
        endTime: '',
        isRecurring: false,
        recurrenceWeekday: '1',
        recurrenceStartTime: '',
        recurrenceStartDate: '',
        recurrenceUntil: '',
        recurrenceDurationMinutes: 90
      },

      recurrenceWeekdayOptions: [
        { value: '1', label: 'Hétfő' },
        { value: '2', label: 'Kedd' },
        { value: '3', label: 'Szerda' },
        { value: '4', label: 'Csütörtök' },
        { value: '5', label: 'Péntek' }
      ],
      
      // KONFIGURÁCIÓK
      steps: [
        { number: 1, label: 'Szint' },
        { number: 2, label: 'Vármegye' },
        { number: 3, label: 'Célcsoport' },
        { number: 4, label: 'Adatok' },
        { number: 5, label: 'Létrehozás' }
      ],
      schoolTargetOptions: [
        {
          value: 'osztaly_szintu',
          label: 'Osztály szintű',
          icon: 'bx bx-user',
          description: 'A kijelölt osztályok diákjai és osztályfőnökei látják'
        },
        {
          value: 'evfolyam_szintu',
          label: 'Évfolyam szintű',
          icon: 'bx bx-group',
          description: 'A kijelölt évfolyamok összes osztálya megkapja'
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
      return ['admin', 'teacher'].includes(this.userRole)
    },

    canCreateGlobalEvent() {
      return this.userRole === 'admin'
    },

    roleMessage() {
      if (this.userRole === 'teacher') {
        return 'Osztályfőnökként iskolai szintű eseményt hozhatsz létre'
      }
      if (this.userRole === 'admin') {
        return 'Adminisztrátorként globális és iskolai eseményt is létrehozhatsz'
      }
      return 'Nincs jogosultságod eseményt létrehozni'
    },

    canProceed() {
      switch (this.currentStep) {
        case 1:
          if (this.selectedEventScope === 'global' && !this.canCreateGlobalEvent) {
            return false
          }
          return this.selectedEventScope !== ''

        case 2:
          if (this.selectedEventScope !== 'global') {
            return true
          }

          if (this.selectedCountyIds.length === 0) {
            return false
          }

          if (this.isCheckingGlobalCollab || this.globalCollabCount === 0) {
            return false
          }

          return true

        case 3:
          if (this.selectedEventScope === 'global') {
            if (this.selectedSchoolTargetGroup === 'teljes_iskola') {
              return true
            }

            if (this.selectedSchoolTargetGroup === 'osztaly_szintu') {
              return this.selectedClassIds.length > 0
            }

            if (this.selectedSchoolTargetGroup === 'evfolyam_szintu') {
              return this.selectedGradeIds.length > 0
            }

            return false
          }

          if (this.selectedSchoolTargetGroup === 'teljes_iskola') {
            return true
          }

          if (this.selectedSchoolTargetGroup === 'osztaly_szintu') {
            return this.selectedClassIds.length > 0
          }

          if (this.selectedSchoolTargetGroup === 'evfolyam_szintu') {
            return this.selectedGradeIds.length > 0
          }

          return false

        case 4:
          return this.validateEventForm()

        default:
          return true
      }
    },

    isFormValid() {
      return this.validateEventForm() && this.currentStep === 5
    },

    counties() {
      return this.countiesList
    },

    availableClassIds() {
      return this.institutionClasses
        .map(classItem => Number(classItem?.id))
        .filter(Number.isFinite)
    },

    availableGradeIds() {
      return this.institutionGrades
        .map(gradeItem => Number(gradeItem?.grade))
        .filter(Number.isFinite)
    },

    selectedClassSummaries() {
      return this.institutionClasses
        .filter(classItem => this.selectedClassIds.includes(Number(classItem?.id)))
        .map(classItem => this.formatClassLabel(classItem))
    },

    selectedGradeSummaries() {
      return this.institutionGrades
        .filter(gradeItem => this.selectedGradeIds.includes(Number(gradeItem?.grade)))
        .map(gradeItem => this.getGradeLabel(gradeItem.grade))
    },

    selectedTargetSummary() {
      if (this.selectedSchoolTargetGroup === 'teljes_iskola') {
        return 'Teljes intézmény'
      }

      if (this.selectedSchoolTargetGroup === 'osztaly_szintu') {
        return this.selectedClassSummaries.join(', ')
      }

      if (this.selectedSchoolTargetGroup === 'evfolyam_szintu') {
        return this.selectedGradeSummaries.join(', ')
      }

      return ''
    }
  },

  watch: {
    selectedEventScope(newValue) {
      // Saját vármegye azonosítása az inicializált adatokból
      const ownCountyId = Number(this.userCountyId)
      const hasOwnCounty = Number.isFinite(ownCountyId) && ownCountyId > 0

      // Globális hatókör esetén bizonyos adatok alaphelyzetbe állítása
      if (newValue === 'global') {
        this.selectedSchoolTargetGroup = 'teljes_iskola'
        this.selectedClassIds = []
        this.selectedGradeIds = []
        this.eventForm.isRecurring = false
        this.eventForm.recurrenceWeekday = '1'
        this.eventForm.recurrenceStartTime = ''
        this.eventForm.recurrenceStartDate = ''
        this.eventForm.recurrenceUntil = ''

        // Globálisnál kötelező a saját vármegyének is benne lennie
        if (hasOwnCounty && !this.selectedCountyIds.includes(ownCountyId)) {
          this.selectedCountyIds = [ownCountyId]
          // A selectedCountyIds watcher majd meghívja az ellenőrzést
        } else {
          this.refreshGlobalCollabCount()
        }

        if (!this.institutionClasses.length) {
          this.loadInstitutionClasses()
        }
        if (!this.institutionGrades.length) {
          this.loadInstitutionGrades()
        }
        return
      }

      // Iskolai hatókör esetén osztály szintű kiválasztás az alapértelmezett
      this.selectedSchoolTargetGroup = 'osztaly_szintu'
      this.globalCollabCount = 0
      this.globalCollabIds = []
      this.isCheckingGlobalCollab = false
      if (!this.institutionClasses.length) {
        this.loadInstitutionClasses()
      }
      if (!this.institutionGrades.length) {
        this.loadInstitutionGrades()
      }
    },

    // Vármegye választó figyelése
    selectedCountyIds(newValue) {
      const ownCountyId = Number(this.userCountyId)
      const hasOwnCounty = Number.isFinite(ownCountyId) && ownCountyId > 0

      // Globális mód esetén mindig legyen benne a saját vármegyénk
      if (this.selectedEventScope === 'global' && hasOwnCounty && !newValue.includes(ownCountyId)) {
        this.$nextTick(() => {
          this.selectedCountyIds = [ownCountyId, ...newValue]
        })
        return
      }

      if (this.selectedEventScope === 'global') {
        this.refreshGlobalCollabCount()
      }
    },

    selectedSchoolTargetGroup(newValue) {
      if (newValue === 'osztaly_szintu' && !this.institutionClasses.length) {
        this.loadInstitutionClasses()
      }

      if (newValue === 'evfolyam_szintu') {
        if (!this.institutionClasses.length) {
          this.loadInstitutionClasses()
        }
        if (!this.institutionGrades.length) {
          this.loadInstitutionGrades()
        }
      }
    },

    institutionClasses(newValue) {
      const validIds = new Set(
        newValue.map(classItem => Number(classItem?.id)).filter(Number.isFinite)
      )
      this.selectedClassIds = this.selectedClassIds.filter(classId => validIds.has(classId))

      if (!this.institutionGrades.length) {
        this.institutionGrades = this.buildGradesFromClasses(newValue)
      }
    },

    institutionGrades(newValue) {
      const validIds = new Set(
        newValue.map(gradeItem => Number(gradeItem?.grade)).filter(Number.isFinite)
      )
      this.selectedGradeIds = this.selectedGradeIds.filter(gradeId => validIds.has(gradeId))
    },

    // Ismétlődés állapotának kezelése
    'eventForm.isRecurring'(newValue) {
      if (newValue) {
        // Ha ismétlődő, nincs egyedi befejező dátum
        this.eventForm.endDateTime = ''
        this.eventForm.endDate = ''
        this.eventForm.endTime = ''
        if (!this.eventForm.recurrenceStartDate) {
          this.eventForm.recurrenceStartDate = this.todayMin.slice(0, 10)
        }
      } else {
        // Ürítés kikapcsoláskor
        this.eventForm.recurrenceStartTime = ''
        this.eventForm.recurrenceStartDate = ''
        this.eventForm.recurrenceUntil = ''
      }
    },

    // Lépések közti váltásnál adatok betöltése
    currentStep(newValue) {
      if (newValue === 3 && (this.selectedEventScope === 'school' || this.selectedEventScope === 'global')) {
        if (!this.institutionClasses.length) {
          this.loadInstitutionClasses()
        }
        if (!this.institutionGrades.length) {
          this.loadInstitutionGrades()
        }
      }
    }
  },

  created() {
    // Firefox specifikus viselkedés detektálása (idő bevitel miatt)
    this.isFirefoxBrowser = typeof navigator !== 'undefined' && /firefox/i.test(navigator.userAgent || '')
    this.initialize()
  },

  methods: {
    // Kollaboráló intézmények számának frissítése (Globális esetén)
    async refreshGlobalCollabCount() {
      if (this.selectedEventScope !== 'global') {
        this.globalCollabCount = 0
        this.globalCollabIds = []
        this.isCheckingGlobalCollab = false
        return
      }

      if (!this.selectedCountyIds.length) {
        this.globalCollabCount = 0
        this.globalCollabIds = []
        this.isCheckingGlobalCollab = false
        return
      }

      this.isCheckingGlobalCollab = true

      try {
        const collabEstablishmentIds = await this.resolveCollabEstablishmentIdsByCounties(this.selectedCountyIds)
        this.globalCollabIds = collabEstablishmentIds
        this.globalCollabCount = collabEstablishmentIds.length
      } catch (error) {
        console.error('Kollaboráló intézmények ellenőrzési hiba:', error)
        this.globalCollabCount = 0
        this.globalCollabIds = []
      } finally {
        this.isCheckingGlobalCollab = false
      }
    },

    // Numerikus lista normalizálása
    normalizeNumericList(values) {
      return Array.from(
        new Set((values || []).map(value => Number(value)).filter(Number.isFinite))
      )
    },

    /**
     * Idő érték normalizálása (HH:mm formátumra)
     * Firefox és egyéb böngészők közötti inkonzisztencia leküzdésére.
     */
    normalizeTimeValue(timeValue) {
      if (!timeValue) {
        return ''
      }

      const raw = String(timeValue).trim().replace(/\./g, ':')
      if (raw === '') {
        return ''
      }

      let hours
      let minutes

      // Kompakt formátum kezelése (pl. 0800 -> 08:00)
      const compactMatch = raw.match(/^(\d{1,2})(\d{2})$/)
      if (compactMatch) {
        hours = Number(compactMatch[1])
        minutes = Number(compactMatch[2])
      } else {
        const separatedMatch = raw.match(/^(\d{1,2}):(\d{1,2})$/)
        if (!separatedMatch) {
          return ''
        }
        hours = Number(separatedMatch[1])
        minutes = Number(separatedMatch[2])
      }

      if (!Number.isFinite(hours) || !Number.isFinite(minutes) || hours < 0 || hours > 23 || minutes < 0 || minutes > 59) {
        return ''
      }

      return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`
    },

    normalizeEventFormTime(fieldName, afterNormalize) {
      const normalized = this.normalizeTimeValue(this.eventForm[fieldName])
      this.eventForm[fieldName] = normalized

      if (typeof afterNormalize === 'function') {
        afterNormalize.call(this)
      }
    },

    combineDateAndTime(date, time) {
      const normalizedTime = this.normalizeTimeValue(time)

      if (!date || !normalizedTime) {
        return ''
      }

      return `${date}T${normalizedTime}`
    },

    splitDateTimeParts(dateTime) {
      if (!dateTime || !String(dateTime).includes('T')) {
        return { date: '', time: '' }
      }

      const [date, timeRaw] = String(dateTime).split('T')
      const time = (timeRaw || '').slice(0, 5)

      return { date, time }
    },

    updateStartDateTimeFromParts() {
      this.eventForm.startDateTime = this.combineDateAndTime(this.eventForm.startDate, this.eventForm.startTime)

      if (
        this.eventForm.endDate && this.eventForm.endTime &&
        this.eventForm.startDateTime &&
        new Date(this.combineDateAndTime(this.eventForm.endDate, this.eventForm.endTime)) < new Date(this.eventForm.startDateTime)
      ) {
        this.eventForm.endDate = this.eventForm.startDate
        this.eventForm.endTime = this.eventForm.startTime
      }

      this.updateEndDateTimeFromParts()
    },

    updateEndDateTimeFromParts() {
      this.eventForm.endDateTime = this.combineDateAndTime(this.eventForm.endDate, this.eventForm.endTime)
    },

    async initialize() {
      this.updateStartDateTimeFromParts()
      this.updateEndDateTimeFromParts()

      const token = getToken()

      try {
        const savedUser =
          localStorage.getItem('esemenyter_user') ||
          sessionStorage.getItem('esemenyter_user')

        const savedInstitutionId =
          localStorage.getItem('CurrentInstitution') ||
          sessionStorage.getItem('CurrentInstitution')

        let mergedUser = null

        if (savedUser) {
          const user = JSON.parse(savedUser)
          mergedUser = { ...user }
          this.userRole = String(user?.role || 'student').toLowerCase()
          this.currentUserId = Number(user?.id || 0) || null

          const userInstitutionId = Number(user?.institution_id || user?.establishment_id || 0)
          const storageInstitutionId = Number(savedInstitutionId || 0)

          if (storageInstitutionId > 0) {
            this.userInstitution.id = storageInstitutionId
          } else if (userInstitutionId > 0) {
            this.userInstitution.id = userInstitutionId
          }
        }

        if (token) {
          const headers = {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
          }

          const userResponse = await axios.get(`${API_BASE}/user`, { headers }).catch(() => null)

          const backendUser = userResponse?.data || {}
          const roleInstitutionId = Number(savedInstitutionId || backendUser?.establishment_id || this.userInstitution.id || 0)
          const roleResponse = roleInstitutionId > 0
            ? await axios.get(`${API_BASE}/establishment/${roleInstitutionId}/role`, { headers }).catch(() => null)
            : null
          const backendRole = String(roleResponse?.data?.role || '').toLowerCase()

          if (backendRole) {
            this.userRole = backendRole
          }

          if (Number(backendUser?.id) > 0) {
            this.currentUserId = Number(backendUser.id)
          }

          const backendInstitutionId = Number(backendUser?.establishment_id || 0)
          if (backendInstitutionId > 0 && !Number(savedInstitutionId || 0)) {
            this.userInstitution.id = backendInstitutionId
          }

          const hasValidSavedInstitution = Number(savedInstitutionId || 0) > 0 && !!roleResponse?.data?.role
          if (!hasValidSavedInstitution && backendInstitutionId > 0) {
            this.userInstitution.id = backendInstitutionId
          }

          if (mergedUser) {
            mergedUser = {
              ...mergedUser,
              id: this.currentUserId || mergedUser.id,
              role: this.userRole,
              institution_id: Number(this.userInstitution.id) || null
            }

            if (hasLocalToken()) {
              localStorage.setItem('esemenyter_user', JSON.stringify(mergedUser))
            } else {
              sessionStorage.setItem('esemenyter_user', JSON.stringify(mergedUser))
            }
          }
        }
      } catch (error) {
        console.error('Felhasználó inicializálási hiba:', error)
      }

      const startParts = this.splitDateTimeParts(this.eventForm.startDateTime)
      const endParts = this.splitDateTimeParts(this.eventForm.endDateTime)

      if (startParts.date && startParts.time) {
        this.eventForm.startDate = startParts.date
        this.eventForm.startTime = startParts.time
      }

      if (endParts.date && endParts.time) {
        this.eventForm.endDate = endParts.date
        this.eventForm.endTime = endParts.time
      }

      if (!this.canCreateGlobalEvent) {
        this.selectedEventScope = 'school'
      }

      await this.loadInstitutionData()
      await this.loadCounties()
      await this.loadInstitutionUsers()
    },

    buildGradesFromClasses(classes) {
      const gradeMap = new Map()

      for (const classItem of classes || []) {
        const grade = Number(classItem?.grade)
        if (!Number.isFinite(grade)) {
          continue
        }

        gradeMap.set(grade, (gradeMap.get(grade) || 0) + 1)
      }

      return Array.from(gradeMap.entries())
        .map(([grade, classCount]) => ({
          grade,
          class_count: classCount
        }))
        .sort((left, right) => left.grade - right.grade)
    },

    async loadInstitutionClasses() {
      try {
        const institutionId = Number(this.userInstitution.id)
        if (!institutionId) {
          this.institutionClasses = []
          return
        }

        const token = getToken()

        if (!token) {
          this.institutionClasses = []
          return
        }

        this.isLoadingClasses = true

        const response = await axios.get(`${API_BASE}/establishment/${institutionId}/classes`, {
          headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' }
        })

        this.institutionClasses = Array.isArray(response?.data?.data)
          ? response.data.data
          : []
      } catch (error) {
        console.error('Osztályok betöltési hiba:', error)
        this.institutionClasses = []
      } finally {
        this.isLoadingClasses = false
      }
    },

    async loadInstitutionGrades() {
      try {
        const institutionId = Number(this.userInstitution.id)
        if (!institutionId) {
          this.institutionGrades = []
          return
        }

        const token = getToken()

        if (!token) {
          this.institutionGrades = this.buildGradesFromClasses(this.institutionClasses)
          return
        }

        this.isLoadingGrades = true

        const response = await axios.get(`${API_BASE}/establishment/${institutionId}/grades`, {
          headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' }
        })

        const grades = Array.isArray(response?.data?.data)
          ? response.data.data
          : []

        this.institutionGrades = grades.length
          ? grades
          : this.buildGradesFromClasses(this.institutionClasses)
      } catch (error) {
        console.error('Évfolyamok betöltési hiba:', error)
        this.institutionGrades = this.buildGradesFromClasses(this.institutionClasses)
      } finally {
        this.isLoadingGrades = false
      }
    },

    async loadInstitutionUsers() {
      try {
        const institutionId = Number(this.userInstitution.id)
        if (!institutionId) {
          this.institutionUserIds = this.currentUserId ? [this.currentUserId] : []
          return
        }

        const token = getToken()

        if (!token) {
          this.institutionUserIds = this.currentUserId ? [this.currentUserId] : []
          return
        }

        const [studentsResponse, staffResponse] = await Promise.all([
          axios.get(`${API_BASE}/establishment/${institutionId}/members/students`, {
            headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' }
          }).catch(() => ({ data: { data: [] } })),
          axios.get(`${API_BASE}/establishment/${institutionId}/members/staff`, {
            headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' }
          }).catch(() => ({ data: { data: [] } }))
        ])

        const studentIds = Array.isArray(studentsResponse?.data?.data)
          ? studentsResponse.data.data.map(item => Number(item?.id)).filter(Number.isFinite)
          : []
        const staffIds = Array.isArray(staffResponse?.data?.data)
          ? staffResponse.data.data.map(item => Number(item?.id)).filter(Number.isFinite)
          : []

        const merged = new Set([...studentIds, ...staffIds])
        if (this.currentUserId) {
          merged.add(Number(this.currentUserId))
        }

        this.institutionUserIds = Array.from(merged)
      } catch (error) {
        console.error('Intézményi felhasználók betöltési hiba:', error)
        this.institutionUserIds = this.currentUserId ? [this.currentUserId] : []
      }
    },

    async loadInstitutionData() {
      try {
        const institutionId = Number(this.userInstitution.id)
        if (!institutionId) {
          return
        }

        const token = getToken()

        if (!token) {
          return
        }

        const response = await axios.get(`${API_BASE}/establishment/${institutionId}`, {
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
          countyId: Number(
            institution.region_id ||
            institution.regionId ||
            institution.county_id ||
            this.userInstitution.countyId ||
            0
          ) || null
        }

        this.userCountyId = Number(this.userInstitution.countyId) || null
      } catch (error) {
        console.error('Intézmény betöltési hiba:', error)
      }
    },

    selectSchoolTargetGroup(value) {
      this.selectedSchoolTargetGroup = value
    },

    selectAllClasses() {
      this.selectedClassIds = [...this.availableClassIds]
    },

    clearSelectedClasses() {
      this.selectedClassIds = []
    },

    toggleClassSelection(classId) {
      const normalizedClassId = Number(classId)
      if (!Number.isFinite(normalizedClassId)) {
        return
      }

      if (this.selectedClassIds.includes(normalizedClassId)) {
        this.selectedClassIds = this.selectedClassIds.filter(item => item !== normalizedClassId)
        return
      }

      this.selectedClassIds = [...this.selectedClassIds, normalizedClassId].sort((left, right) => left - right)
    },

    selectAllGrades() {
      this.selectedGradeIds = [...this.availableGradeIds]
    },

    clearSelectedGrades() {
      this.selectedGradeIds = []
    },

    toggleGradeSelection(grade) {
      const normalizedGrade = Number(grade)
      if (!Number.isFinite(normalizedGrade)) {
        return
      }

      if (this.selectedGradeIds.includes(normalizedGrade)) {
        this.selectedGradeIds = this.selectedGradeIds.filter(item => item !== normalizedGrade)
        return
      }

      this.selectedGradeIds = [...this.selectedGradeIds, normalizedGrade].sort((left, right) => left - right)
    },

    formatClassLabel(classItem) {
      const grade = Number(classItem?.grade)
      const className = String(classItem?.name || '').trim()

      if (Number.isFinite(grade) && className) {
        // Ha az osztály nevében már benne van az évfolyam, nem írjuk ki mégegyszer
        const startsWithGrade = className.startsWith(`${grade}.`) || className.startsWith(`${grade}`)
        return startsWithGrade ? className : `${grade}.${className}`
      }

      return className || `Osztály #${classItem?.id ?? '?'}`
    },

    getGradeLabel(grade) {
      return `${Number(grade)}. évfolyam`
    },

    async fetchClassMemberUserIdsInMass(classIds, token) {
      const institutionId = Number(this.userInstitution.id)
      const normalizedClassIds = this.normalizeNumericList(classIds)

      if (!institutionId || !normalizedClassIds.length) {
        return []
      }

      const response = await axios.get(
        `${API_BASE}/establishment/${institutionId}/classes/members`,
        {
          params: { class_ids: normalizedClassIds },
          headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' }
        }
      )

      const payload = response?.data || {}
      const mergedIds = Array.isArray(payload?.user_ids) && payload.user_ids.length
        ? payload.user_ids
        : [...(payload?.student_ids || []), ...(payload?.teacher_ids || [])]

      return this.normalizeNumericList(mergedIds)
    },

    async fetchGradeMemberUserIdsInMass(gradeIds, token) {
      const institutionId = Number(this.userInstitution.id)
      const normalizedGradeIds = this.normalizeNumericList(gradeIds)

      if (!institutionId || !normalizedGradeIds.length) {
        return []
      }

      const response = await axios.get(
        `${API_BASE}/establishment/${institutionId}/grades/members`,
        {
          params: { grade_ids: normalizedGradeIds },
          headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' }
        }
      )

      const payload = response?.data || {}
      const mergedIds = Array.isArray(payload?.user_ids) && payload.user_ids.length
        ? payload.user_ids
        : [...(payload?.student_ids || []), ...(payload?.teacher_ids || [])]

      return this.normalizeNumericList(mergedIds)
    },

    async resolveLocalTargetUserIds(token) {
      if (this.selectedSchoolTargetGroup === 'teljes_iskola') {
        return this.normalizeNumericList(this.institutionUserIds)
      }

      if (this.selectedSchoolTargetGroup === 'osztaly_szintu') {
        if (!this.selectedClassIds.length) {
          throw new Error('Legalább egy osztályt ki kell választani.')
        }

        const userIds = await this.fetchClassMemberUserIdsInMass(this.selectedClassIds, token)
        return this.normalizeNumericList([
          ...userIds,
          ...(this.currentUserId ? [this.currentUserId] : [])
        ])
      }

      if (this.selectedSchoolTargetGroup === 'evfolyam_szintu') {
        if (!this.selectedGradeIds.length) {
          throw new Error('Legalább egy évfolyamot ki kell választani.')
        }

        const userIds = await this.fetchGradeMemberUserIdsInMass(this.selectedGradeIds, token)
        return this.normalizeNumericList([
          ...userIds,
          ...(this.currentUserId ? [this.currentUserId] : [])
        ])
      }

      return this.normalizeNumericList(this.institutionUserIds)
    },

    // Vármegyék letöltése és formázása
    async loadCounties() {
      try {
        const response = await axios.get(`${API_BASE}/regions/all`, {
          headers: {
            Accept: 'application/json'
          }
        })

        const apiCounties = Array.isArray(response?.data?.data) ? response.data.data : []

        if (!apiCounties.length) {
          return
        }

        // Adatok egységesítése (név, iskola szám)
        this.countiesList = apiCounties.map(county => ({
          id: Number(county.id),
          name: county.title || county.name || county.nev || `Vármegye #${county.id}`,
          schoolCount: Number(county.iskolakSzama || county.schools_count || 0)
        }))
      } catch (error) {
        console.error('Vármegyék betöltési hiba:', error)
      }
    },

    async resolveCollabEstablishmentIdsByCounties(countyIds) {
      const normalizedCountyIds = Array.from(
        new Set((countyIds || []).map(id => Number(id)).filter(Number.isFinite))
      )

      if (!normalizedCountyIds.length) {
        return []
      }

      const sourceEstablishmentId = Number(this.userInstitution.id)

      const responses = await Promise.all(
        normalizedCountyIds.map(countyId =>
          axios.get(`${API_BASE}/establishments`, {
            params: { region_id: countyId, include_all: 1 },
            headers: { Accept: 'application/json' }
          }).catch(() => ({ data: { data: [] } }))
        )
      )

      return Array.from(new Set(
        responses.flatMap(response =>
          (Array.isArray(response?.data?.data) ? response.data.data : [])
            .map(item => Number(item?.id))
            .filter(Number.isFinite)
        )
      )).filter(id => id !== sourceEstablishmentId)
    },

    getEventScopeLabel(scope) {
      const labels = {
        global: 'Globális esemény',
        school: 'Iskolai esemény'
      }
      return labels[scope] || scope
    },

    getSchoolTargetLabel(targetGroup) {
      const target = this.schoolTargetOptions.find(option => option.value === targetGroup)
      return target ? target.label : targetGroup
    },

    getSelectedCountyNames() {
      return this.selectedCountyIds.map(id => {
        const county = this.counties.find(item => item.id === id)
        return county ? county.name : id
      })
    },

    validateEventForm() {
      const {
        title,
        description,
        startDateTime,
        endDateTime,
        isRecurring,
        recurrenceWeekday,
        recurrenceStartTime,
        recurrenceStartDate,
        recurrenceUntil,
        recurrenceDurationMinutes
      } = this.eventForm

      const hasCommonFields =
        title && title.trim() !== '' &&
        description && description.trim() !== ''

      if (!hasCommonFields) {
        return false
      }

      const isSchoolRecurring = this.selectedEventScope === 'school' && isRecurring

      if (isSchoolRecurring) {
        const weekday = Number(recurrenceWeekday)
        const weekdayValid = Number.isFinite(weekday) && weekday >= 1 && weekday <= 5
        const startTimeValid = Boolean(recurrenceStartTime)
        const startDateValid = Boolean(recurrenceStartDate)
        const duration = Number(recurrenceDurationMinutes)
        const durationValid = Number.isFinite(duration) && duration >= 15
        const recurrenceDateValid = recurrenceUntil && recurrenceUntil !== '' && recurrenceStartDate && new Date(`${recurrenceUntil}T23:59:59`) >= new Date(`${recurrenceStartDate}T00:00:00`)
        return weekdayValid && startTimeValid && startDateValid && durationValid && recurrenceDateValid
      }

      if (!startDateTime || startDateTime === '') {
        return false
      }

      return endDateTime && endDateTime !== '' && new Date(startDateTime) <= new Date(endDateTime)
    },

    getWeekdayLabel(weekdayValue) {
      const found = this.recurrenceWeekdayOptions.find(option => Number(option.value) === Number(weekdayValue))
      return found?.label || 'Ismeretlen nap'
    },

    buildRecurringStartDateTime() {
      const { recurrenceStartDate, recurrenceStartTime, recurrenceWeekday } = this.eventForm

      if (!recurrenceStartDate || !recurrenceStartTime) {
        return ''
      }

      const [hours, minutes] = String(recurrenceStartTime).split(':').map(Number)
      if (!Number.isFinite(hours) || !Number.isFinite(minutes)) {
        return ''
      }

      const targetWeekday = Number(recurrenceWeekday)
      if (!Number.isFinite(targetWeekday)) {
        return ''
      }

      const firstDate = new Date(`${recurrenceStartDate}T00:00:00`)
      if (Number.isNaN(firstDate.getTime())) {
        return ''
      }

      const currentWeekday = ((firstDate.getDay() + 6) % 7) + 1
      const dayOffset = (targetWeekday - currentWeekday + 7) % 7
      firstDate.setDate(firstDate.getDate() + dayOffset)
      firstDate.setHours(hours, minutes, 0, 0)

      const year = firstDate.getFullYear()
      const month = String(firstDate.getMonth() + 1).padStart(2, '0')
      const day = String(firstDate.getDate()).padStart(2, '0')
      const hh = String(firstDate.getHours()).padStart(2, '0')
      const mm = String(firstDate.getMinutes()).padStart(2, '0')

      return `${year}-${month}-${day}T${hh}:${mm}`
    },

    buildRecurringEndDateTime() {
      const startDateTime = this.buildRecurringStartDateTime()
      const duration = Number(this.eventForm.recurrenceDurationMinutes)

      if (!startDateTime || !Number.isFinite(duration) || duration < 15) {
        return ''
      }

      const startDate = new Date(startDateTime)
      if (Number.isNaN(startDate.getTime())) {
        return ''
      }

      const endDate = new Date(startDate.getTime() + (duration * 60 * 1000))
      const year = endDate.getFullYear()
      const month = String(endDate.getMonth() + 1).padStart(2, '0')
      const day = String(endDate.getDate()).padStart(2, '0')
      const hours = String(endDate.getHours()).padStart(2, '0')
      const minutes = String(endDate.getMinutes()).padStart(2, '0')

      return `${year}-${month}-${day}T${hours}:${minutes}`
    },

    formatDateTime(dateTime) {
      if (!dateTime) {
        return 'nincs megadva'
      }

      if (/^\d{4}-\d{2}-\d{2}$/.test(dateTime)) {
        return new Date(`${dateTime}T00:00:00`).toLocaleDateString('hu-HU', {
          year: 'numeric',
          month: '2-digit',
          day: '2-digit'
        })
      }

      const date = new Date(dateTime)
      return date.toLocaleString('hu-HU', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
      })
    },

    async nextStep() {
      if (!(this.currentStep < 5 && this.canProceed)) {
        return
      }

      if (this.currentStep === 2 && this.selectedEventScope === 'global') {
        if (!this.globalCollabIds.length) {
          toast.warning('A kiválasztott vármegyékből nem találtunk más intézményt. Válassz további vármegyét.')
          return
        }
      }

      if (this.currentStep === 1 && this.selectedEventScope !== 'global') {
        this.currentStep = 3
        return
      }

      this.currentStep++
    },

    previousStep() {
      if (this.currentStep <= 1) {
        return
      }

      if (this.currentStep === 3 && this.selectedEventScope !== 'global') {
        this.currentStep = 1
        return
      }

      this.currentStep--
    },

    /**
     * Esemény létrehozása (API hívás)
     * Összeállítja a komplex payload-ot a hatókör (iskolai/globális) 
     * és az ismétlődés adatai alapján.
     */
    async createEvent() {
      if (!this.isFormValid) {
        toast.warning('Kérjük, töltse ki az összes kötelező mezőt!')
        return
      }

      if (this.selectedEventScope === 'global' && !this.canCreateGlobalEvent) {
        toast.error('Nincs jogosultságod globális esemény létrehozására.')
        return
      }

      this.isSubmitting = true

      try {
        const token = getToken()
        if (!token) {
          toast.error('Lejárt munkamenet. Kérjük, jelentkezzen be újra!')
          this.$router.push('/')
          return
        }

        const establishmentId = Number(this.userInstitution.id)
        if (!establishmentId) {
          toast.error('Hiányzó intézmény azonosító. Frissítsd az oldalt és próbáld újra.')
          return
        }

        // Felhasználók betöltése, ha még nincsenek meg
        if (!this.institutionUserIds.length) {
          await this.loadInstitutionUsers()
        }

        const resolvedStartDateTime =
          this.selectedEventScope === 'school' && this.eventForm.isRecurring
            ? this.buildRecurringStartDateTime()
            : this.eventForm.startDateTime

        const resolvedEndDateTime =
          this.selectedEventScope === 'school' && this.eventForm.isRecurring
            ? this.buildRecurringEndDateTime()
            : this.eventForm.endDateTime

        // Alapadatok összeállítása
        const payload = {
          title: this.eventForm.title,
          description: this.eventForm.description,
          content: this.eventForm.content,
          start_date: resolvedStartDateTime,
          end_date: resolvedEndDateTime,
          type: this.selectedEventScope === 'global' ? 'global' : 'local',
          establishment_id: establishmentId
        }

        // Globális specifikus adatok
        if (this.selectedEventScope === 'global') {
          payload.collab_establishment_ids = this.globalCollabIds
        }

        // Célcsoport adatok (mindkét típusnál)
        payload.target_group = this.selectedSchoolTargetGroup

        if (this.eventForm.isRecurring) {
          payload.is_recurring = true
          payload.recurrence_frequency = 'weekly'
          payload.recurrence_until = this.eventForm.recurrenceUntil
        }

        if (this.selectedSchoolTargetGroup === 'osztaly_szintu') {
          payload.selected_class_ids = this.normalizeNumericList(this.selectedClassIds)
        } else if (this.selectedSchoolTargetGroup === 'evfolyam_szintu') {
          payload.selected_grade_ids = this.normalizeNumericList(this.selectedGradeIds)
        }

        // Címzettek meghatározása a választott célcsoport alapján
        const localTargetUserIds = await this.resolveLocalTargetUserIds(token)
        if (!localTargetUserIds.length) {
          toast.warning('A kiválasztott célcsoporthoz nem találtunk címzett felhasználót.')
          return
        }
        payload.users = localTargetUserIds

        // API mentés
        await axios.post(`${API_BASE}/establishment/events`, payload, {
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
        } else if (error.message) {
          toast.error('Hiba: ' + error.message)
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
@import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap');

/* ==========================================================================
   ALAPVETŐ OLDALSTRUKTÚRA ÉS LAYOUT
   ========================================================================== */
.event-creator-page {
  background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
  font-family: 'Manrope', sans-serif;
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: flex-start;
  box-sizing: border-box;
  width: 100%;
  overflow-x: hidden;
  padding: 24px 20px;
}

.back-button {
  position: absolute;
  top: 20px;
  left: 20px;
  background: white;
  color: #1e293b;
  border: 1px solid #e2e8f0;
  padding: 11px 18px;
  border-radius: 999px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 500;
  z-index: 100;
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
  transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
  max-width: 130px;
  white-space: nowrap;
  font-size: 14px;
}

.back-button:hover {
  background: #f8fafc;
  transform: translateX(-3px) translateY(-1px);
  box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
}

.event-creator-wrapper {
  max-width: 1140px;
  width: 100%;
  background: white;
  border-radius: 32px;
  overflow: hidden;
  box-shadow: 0 20px 50px rgba(15, 23, 42, 0.12);
  margin: 62px auto 20px;
  box-sizing: border-box;
  border: 1px solid rgba(226, 232, 240, 0.9);
}

.creator-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 36px 44px;
  text-align: center;
  box-sizing: border-box;
  position: relative;
  overflow: hidden;
}

.creator-header::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
  pointer-events: none;
}

.creator-header h1 {
  font-size: 32px;
  margin-bottom: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  font-weight: 700;
  letter-spacing: -0.5px;
  position: relative;
}

.creator-header h1 i {
  font-size: 36px;
  color: #e0e7ff;
}

.subtitle {
  opacity: 0.82;
  font-size: 15px;
  font-weight: 400;
  position: relative;
}

.header-badges {
  margin-top: 20px;
  display: flex;
  justify-content: center;
  gap: 10px;
  flex-wrap: wrap;
}

.header-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 9px 14px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.16);
  border: 1px solid rgba(255, 255, 255, 0.18);
  color: rgba(255, 255, 255, 0.92);
  font-size: 13px;
  font-weight: 600;
  letter-spacing: 0.2px;
}

.header-badge i {
  font-size: 16px;
}

.creator-content {
  padding: 34px 40px 40px;
  box-sizing: border-box;
  background: #ffffff;
}

.no-permission {
  padding: 40px;
  text-align: center;
  box-sizing: border-box;
}

.permission-error {
  padding: 32px;
  background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
  border-radius: 24px;
  border: 1px solid #fecaca;
  box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
  box-sizing: border-box;
}

.permission-error i {
  font-size: 56px;
  color: #ef4444;
  margin-bottom: 16px;
}

.permission-error h3 {
  color: #b91c1c;
  margin-bottom: 12px;
  font-size: 22px;
  font-weight: 600;
}

.permission-error p {
  color: #7f1d1d;
  margin-bottom: 24px;
  font-size: 16px;
}

.stepper-nav {
  margin-bottom: 30px;
}

/* ==========================================================================
   STEPPER (LÉPTETŐ) ESZTÉTIKA
   ========================================================================== */
.steps {
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
  gap: 8px;
  padding: 12px 14px;
  border-radius: 20px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
}

.steps::before {
  content: '';
  position: absolute;
  top: 32px;
  left: 88px;
  right: 88px;
  height: 2px;
  background: #e2e8f0;
  z-index: 1;
}

/* STEPPER LÉPÉSEK EGYEDI STÍLUSAI */
.step {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  position: relative;
  z-index: 2;
  flex: 1;
  padding: 0 10px;
}

.step-number {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #f1f5f9;
  color: #64748b;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 16px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: 2px solid #e2e8f0;
  box-shadow: 0 2px 4px rgba(15, 23, 42, 0.04);
}

.step.active .step-number {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-color: transparent;
  transform: scale(1.1);
  box-shadow: 0 10px 15px -3px rgba(102, 126, 234, 0.28);
}

.step.completed .step-number {
  background: #10b981;
  color: white;
  border-color: transparent;
}

.step-label {
  color: #64748b;
  font-size: 13px;
  font-weight: 500;
  text-align: center;
  word-break: keep-all;
  overflow-wrap: normal;
  max-width: 120px;
  line-height: 1.3;
  letter-spacing: 0.5px;
}

.step.active .step-label {
  color: #4f46e5;
  font-weight: 600;
}

/* ==========================================================================
   FORM SZEKCIÓK ÉS KOMPONENSEK
   ========================================================================== */
.form-section {
  margin-bottom: 28px;
  padding: 24px;
  border: 1px solid #e2e8f0;
  border-radius: 24px;
  background: #ffffff;
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
  box-sizing: border-box;
}

.form-section:last-child {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 24px;
}

.form-section h3 {
  color: #1f2937;
  margin-bottom: 24px;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 20px;
  font-weight: 600;
  letter-spacing: -0.3px;
}

.form-section h3 i {
  color: #667eea;
  font-size: 24px;
  background: #eef2ff;
  padding: 8px;
  border-radius: 12px;
}

/* ==========================================================================
   VÁLASZTÓ OPCIÓK (TÍPUS ÉS CÉLCSOPORT)
   ========================================================================== */
.type-selection, .target-group-options {
  display: grid;
  grid-template-columns: 1fr;
  gap: 20px;
  margin-top: 20px;
  box-sizing: border-box;
}

.recurrence-guide {
  margin-top: 16px;
  display: flex;
  align-items: flex-start;
  gap: 10px;
  background: #ecfeff;
  border: 1px solid #a5f3fc;
  border-radius: 14px;
  padding: 12px 14px;
  color: #0f172a;
  font-size: 14px;
  line-height: 1.45;
}

.recurrence-guide i {
  color: #0891b2;
  font-size: 18px;
  margin-top: 1px;
}

.recurrence-guide.compact {
  margin: 0 0 14px;
}

.type-option, .target-group-option {
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  padding: 24px;
  cursor: pointer;
  transition: all 0.2s ease;
  background: white;
  box-sizing: border-box;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.type-option:hover, .target-group-option:hover {
  border-color: #94a3b8;
  transform: translateY(-2px);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.type-option.selected, .target-group-option.selected {
  border-color: #667eea;
  background: linear-gradient(135deg, #eef2ff 0%, #ede9fe 100%);
  box-shadow: 0 20px 25px -5px rgba(102, 126, 234, 0.16);
}

.target-group-option {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.option-content {
  text-align: left;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
}

.option-content i {
  font-size: 40px;
  color: #667eea;
  margin-bottom: 16px;
  background: #f1f5f9;
  width: 64px;
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 16px;
}

.option-content h4 {
  color: #1f2937;
  margin-bottom: 8px;
  font-size: 20px;
  font-weight: 600;
}

.option-content p {
  color: #64748b;
  font-size: 14px;
  line-height: 1.5;
  margin-bottom: 12px;
}

/* ==========================================================================
   VÁRMEGYE VÁLASZTÓ LISTA (GLOBÁLIS MÓD)
   ========================================================================== */
.county-list {
  max-height: 450px;
  overflow-y: auto;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  padding: 16px;
  margin-top: 20px;
  background: #ffffff;
  box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.02);
}

.county-option {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid #f1f5f9;
  transition: all 0.2s ease;
  border-radius: 12px;
  margin-bottom: 4px;
}

.county-option:last-child {
  border-bottom: none;
  margin-bottom: 0;
}

.county-option:hover {
  background: #f8fafc;
}

.county-option.selected {
  background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
  border: 1px solid #bae6fd;
}

.county-option.own-county {
  background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
  border: 1px solid #bbf7d0;
  position: relative;
}

.county-checkbox {
  display: flex;
  align-items: center;
  gap: 16px;
  cursor: pointer;
  flex: 1;
}

.county-checkbox input[type="checkbox"] {
  width: 20px;
  height: 20px;
  cursor: pointer;
  accent-color: #667eea;
  border-radius: 6px;
}

.county-checkbox input[type="checkbox"]:disabled {
  accent-color: #10b981;
  cursor: not-allowed;
  opacity: 0.8;
}

.county-name {
  font-weight: 500;
  color: #1e293b;
  font-size: 15px;
}

.own-county-badge {
  font-size: 12px;
  color: #059669;
  font-weight: 600;
  margin-left: 8px;
  background: rgba(16, 185, 129, 0.1);
  padding: 4px 8px;
  border-radius: 20px;
}

.school-count {
  font-size: 13px;
  color: #64748b;
  background: #f1f5f9;
  padding: 4px 12px;
  border-radius: 30px;
  white-space: nowrap;
  font-weight: 500;
}

.selected-info {
  margin-top: 20px;
  padding: 16px 20px;
  background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
  border-radius: 16px;
  display: flex;
  align-items: center;
  gap: 12px;
  color: #0369a1;
  font-weight: 500;
  border: 1px solid #bae6fd;
}

.selected-info i {
  font-size: 24px;
  color: #0284c7;
}

.selection-description {
  color: #64748b;
  font-size: 15px;
  margin-bottom: 16px;
  font-weight: 400;
}

.global-county-section {
  margin-bottom: 18px;
}

.step-split-hint {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 0 0 20px;
  padding: 10px 14px;
  border: 1px dashed #cbd5e1;
  border-radius: 12px;
  background: #f8fafc;
  color: #475569;
  font-size: 14px;
}

.step-split-hint i {
  color: #6366f1;
  font-size: 18px;
}

.global-target-section {
  padding-top: 24px;
  border-top: 2px solid #e2e8f0;
}

.scope-notice {
  margin-bottom: 16px;
  padding: 12px 14px;
  border-radius: 12px;
  border: 1px solid #bfdbfe;
  background: #eff6ff;
  color: #1e3a8a;
  display: flex;
  align-items: flex-start;
  gap: 10px;
  font-size: 14px;
  line-height: 1.45;
}

.scope-notice i {
  font-size: 18px;
  margin-top: 1px;
  color: #2563eb;
}

.permission-badge {
  display: inline-block;
  margin-top: 12px;
  padding: 6px 12px;
  background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
  color: #dc2626;
  border-radius: 30px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border: 1px solid #fecaca;
  align-self: flex-start;
}

.county-list-summary {
  display: block;
  margin-top: 8px;
  font-size: 14px;
  color: #475569;
  line-height: 1.6;
  background: #f8fafc;
  padding: 12px;
  border-radius: 12px;
}

.target-selector-panel {
  border-top: 1px solid rgba(99, 102, 241, 0.18);
  padding-top: 18px;
}

.selector-toolbar {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
  align-items: flex-start;
  margin-bottom: 12px;
}

.bulk-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.selector-btn {
  border: 1px solid #c7d2fe;
  background: white;
  color: #4338ca;
  border-radius: 12px;
  padding: 9px 14px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.selector-btn:hover {
  background: #eef2ff;
  border-color: #818cf8;
}

.selector-btn.secondary {
  border-color: #cbd5e1;
  color: #475569;
}

.selector-btn.secondary:hover {
  background: #f8fafc;
  border-color: #94a3b8;
}

.class-target-dashboard {
  margin-top: 24px;
  border: 1px solid #e2e8f0;
  border-radius: 18px;
  padding: 18px;
  background: #f8fafc;
}

.class-target-dashboard h4 {
  margin: 0 0 14px 0;
  display: flex;
  align-items: center;
  gap: 8px;
  color: #1e293b;
  font-size: 16px;
}

.class-target-state {
  padding: 12px 14px;
  border-radius: 12px;
  background: #e2e8f0;
  color: #334155;
  font-size: 14px;
}

.class-target-state.warning {
  background: #fee2e2;
  color: #991b1b;
}

.class-target-help {
  margin-bottom: 12px;
  padding: 10px 12px;
  border-radius: 12px;
  background: #dbeafe;
  color: #1e3a8a;
  font-size: 14px;
}

.class-target-help.success {
  background: #dcfce7;
  color: #166534;
}

.class-target-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
  gap: 10px;
}

.class-target-card {
  border: 1px solid #cbd5e1;
  border-radius: 14px;
  padding: 12px;
  background: white;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.class-target-card:hover {
  border-color: #94a3b8;
}

.class-target-card.selected {
  border-color: #4f46e5;
  box-shadow: 0 0 0 1px #4f46e5;
  background: #eef2ff;
}

.class-target-card input[type="checkbox"] {
  width: 18px;
  height: 18px;
  margin-top: 2px;
  accent-color: #4f46e5;
  cursor: pointer;
}

.class-target-copy {
  flex: 1;
}

.class-target-title {
  font-size: 15px;
  font-weight: 600;
  color: #1e293b;
}

.class-target-meta {
  margin-top: 4px;
  font-size: 12px;
  color: #64748b;
}

.selected-info.compact {
  margin-top: 12px;
  padding: 12px 14px;
}

.school-wide-info {
  margin-top: 16px;
}

/* FORM ELEMEK */
.event-form {
  margin-top: 24px;
  box-sizing: border-box;
}

.form-group {
  margin-bottom: 24px;
  box-sizing: border-box;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  color: #1e293b;
  font-weight: 600;
  font-size: 14px;
  letter-spacing: 0.3px;
}

.form-group input, .form-group textarea, .form-group select {
  width: 100%;
  padding: 14px 16px;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  font-size: 15px;
  font-family: 'Inter', sans-serif;
  transition: all 0.2s ease;
  box-sizing: border-box;
  background: #f8fafc;
}

.form-group input:focus, .form-group textarea:focus, .form-group select:focus {
  outline: none;
  border-color: #667eea;
  background: white;
  box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
}

.form-group textarea {
  min-height: 120px;
  resize: vertical;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  box-sizing: border-box;
}

.recurrence-toggle {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 14px 16px;
  box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 12px;
  font-weight: 600;
  margin-bottom: 0;
  cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
  width: 18px;
  height: 18px;
  flex: 0 0 auto;
  accent-color: #667eea;
  margin-top: 2px;
}

.checkbox-copy {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.checkbox-title {
  color: #1e293b;
  font-size: 15px;
  font-weight: 700;
  line-height: 1.3;
}

.checkbox-description {
  color: #64748b;
  font-size: 13px;
  font-weight: 400;
  line-height: 1.45;
}

.field-help {
  margin: 0;
  color: #64748b;
  font-size: 13px;
}

.recurrence-flow {
  display: grid;
  gap: 14px;
  margin-top: 12px;
}

.recurrence-step {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 12px;
}

.recurrence-step label {
  display: block;
  margin-bottom: 8px;
  color: #1e293b;
  font-weight: 600;
  font-size: 14px;
}

.recurrence-step input,
.recurrence-step select {
  width: 100%;
  padding: 12px 14px;
  border: 1px solid #cbd5e1;
  border-radius: 12px;
  font-size: 14px;
  background: #ffffff;
  box-sizing: border-box;
}

.summary {
  background: #ffffff;
  border-radius: 24px;
  padding: 24px;
  margin-top: 20px;
  box-sizing: border-box;
  border: 1px solid #e2e8f0;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
}

.summary-item {
  padding: 14px 0;
  border-bottom: 1px solid #e2e8f0;
  font-size: 15px;
  line-height: 1.6;
  color: #475569;
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.summary-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.summary-item strong {
  color: #1e293b;
  min-width: 140px;
  font-weight: 600;
}

.summary-item span {
  color: #475569;
}

/* ==========================================================================
   FORM MŰVELETEK (GOMBOK ÉS INTERAKCIÓK)
   ========================================================================== */
.form-actions {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  margin-top: 32px;
  padding-top: 24px;
  border-top: 1px solid #e2e8f0;
  box-sizing: border-box;
  position: sticky;
  bottom: 0;
  background: linear-gradient(180deg, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0.96) 24%, rgba(255, 255, 255, 0.98) 100%);
  z-index: 5;
}

.btn {
  padding: 14px 28px;
  border: none;
  border-radius: 999px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.2s ease;
  text-decoration: none;
  box-sizing: border-box;
  flex: 1;
  letter-spacing: 0.3px;
}

.btn-primary {
  background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
  color: white;
  box-shadow: 0 10px 20px rgba(79, 70, 229, 0.18);
}

.btn-primary:hover:not(:disabled) {
  background: linear-gradient(135deg, #1d4ed8 0%, #6d28d9 100%);
  transform: translateY(-2px);
  box-shadow: 0 14px 28px rgba(79, 70, 229, 0.24);
}

.btn-secondary {
  background: #f1f5f9;
  color: #475569;
  border: 1px solid #e2e8f0;
}

.btn-secondary:hover:not(:disabled) {
  background: #e2e8f0;
  transform: translateY(-2px);
}

.btn-success {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  box-shadow: 0 10px 20px rgba(16, 185, 129, 0.18);
}

.btn-success:hover:not(:disabled) {
  background: #059669;
  transform: translateY(-2px);
  box-shadow: 0 14px 28px rgba(16, 185, 129, 0.24);
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none !important;
  box-shadow: none !important;
}

/* Reszponzív kiegészítések */
/* ==========================================================================
   RESPONSIVE DESIGN (REASZPONSZÍV MEGJELENÉS)
   ========================================================================== */
@media (max-width: 768px) {
  .event-creator-page {
    padding: 12px;
    align-items: flex-start;
  }
  
  .event-creator-wrapper {
    margin: 60px 10px 10px;
    max-width: 100%;
  }
  
  .creator-header {
    padding: 24px 20px;
  }
  
  .creator-header h1 {
    font-size: 24px;
  }
  
  .creator-header h1 i {
    font-size: 28px;
  }
  
  .subtitle {
    font-size: 13px;
  }

  .header-badges {
    gap: 8px;
  }

  .header-badge {
    font-size: 12px;
    padding: 8px 12px;
  }
  
  .creator-content {
    padding: 24px 18px 28px;
  }

  .steps {
    overflow-x: auto;
    justify-content: flex-start;
    padding: 0 8px 6px;
    gap: 12px;
    scroll-snap-type: x proximity;
  }

  .steps::-webkit-scrollbar {
    height: 6px;
  }

  .steps::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 999px;
  }

  .step {
    flex: 0 0 90px;
    padding: 0;
    scroll-snap-align: start;
  }
  
  .steps::before {
    display: none;
  }
  
  .step-number {
    width: 36px;
    height: 36px;
    font-size: 14px;
  }
  
  .step-label {
    font-size: 11px;
    max-width: 95px;
  }
  
  .form-section h3 {
    font-size: 18px;
  }

  .form-section {
    padding: 20px;
    border-radius: 20px;
  }
  
  .option-content i {
    width: 56px;
    height: 56px;
    font-size: 32px;
  }
  
  .option-content h4 {
    font-size: 18px;
  }
  
  .form-row {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .county-list {
    max-height: 350px;
  }
  
  .county-option {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
    padding: 16px;
  }
  
  .school-count {
    align-self: flex-start;
  }
  
  .summary-item {
    flex-direction: column;
    gap: 4px;
  }
  
  .summary-item strong {
    min-width: auto;
  }
  
  .btn {
    padding: 12px 20px;
    font-size: 14px;
  }
  
  .form-actions {
    flex-direction: row;
    gap: 12px;
    padding-top: 18px;
  }
}

/* MOBIL ESZKÖZÖK FINOMHANGOLÁSA */
@media (max-width: 480px) {
  .event-creator-page {
    padding: 8px;
  }
  
  .event-creator-wrapper {
    margin: 55px 5px 5px;
    border-radius: 24px;
  }
  
  .back-button {
    top: 12px;
    left: 12px;
    padding: 8px 14px;
    font-size: 13px;
  }
  
  .creator-header {
    padding: 20px 16px;
  }
  
  .creator-header h1 {
    font-size: 20px;
    gap: 6px;
  }
  
  .creator-header h1 i {
    font-size: 24px;
  }
  
  .subtitle {
    font-size: 12px;
  }
  
  .creator-content {
    padding: 20px 14px 24px;
  }

  .steps {
    gap: 10px;
  }
  
  .steps::before {
    display: none;
  }
  
  .step {
    flex: 0 0 80px;
  }
  
  .step-number {
    width: 32px;
    height: 32px;
    font-size: 13px;
  }
  
  .step-label {
    font-size: 10px;
    max-width: 80px;
    letter-spacing: 0.3px;
  }
  
  .type-selection, .target-group-options {
    gap: 12px;
  }
  
  .type-option, .target-group-option {
    padding: 20px;
  }
  
  .option-content i {
    width: 48px;
    height: 48px;
    font-size: 28px;
    margin-bottom: 12px;
  }
  
  .option-content h4 {
    font-size: 16px;
  }
  
  .option-content p {
    font-size: 13px;
  }
  
  .permission-badge {
    font-size: 11px;
    padding: 4px 8px;
  }
  
  .county-list {
    max-height: 300px;
    padding: 12px;
  }
  
  .county-option {
    padding: 14px;
  }
  
  .county-checkbox {
    gap: 12px;
  }
  
  .county-checkbox input[type="checkbox"] {
    width: 18px;
    height: 18px;
  }
  
  .county-name {
    font-size: 14px;
  }
  
  .own-county-badge {
    display: block;
    margin-left: 0;
    margin-top: 6px;
    width: fit-content;
  }
  
  .school-count {
    font-size: 12px;
    padding: 3px 8px;
  }
  
  .form-actions {
    flex-direction: column;
    gap: 10px;
  }

  .selector-toolbar {
    flex-direction: column;
  }

  .bulk-actions {
    width: 100%;
  }

  .selector-btn {
    flex: 1;
    text-align: center;
  }
  
  .btn {
    width: 100%;
    padding: 14px;
  }

  .form-section {
    padding: 18px;
    border-radius: 20px;
  }

  .summary {
    padding: 18px;
  }
}

@media (min-width: 769px) {
  .type-selection {
    grid-template-columns: 1fr 1fr;
  }

  .target-group-options {
    grid-template-columns: 1fr;
  }
  
  .form-actions .btn {
    flex: none;
    min-width: 160px;
  }
}

@media (min-width: 1024px) {
  .event-creator-wrapper {
    max-width: 1180px;
  }
  
  .creator-content {
    padding: 40px 44px 44px;
  }
}

/* Animációk */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.form-section {
  animation: fadeInUp 0.4s ease-out;
}

.form-section:nth-of-type(2) {
  animation-delay: 0.03s;
}

.form-section:nth-of-type(3) {
  animation-delay: 0.06s;
}

.form-section:nth-of-type(4) {
  animation-delay: 0.09s;
}

.form-section:nth-of-type(5) {
  animation-delay: 0.12s;
}

/* Modern scrollbar */
.county-list::-webkit-scrollbar {
  width: 8px;
}

.county-list::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 10px;
}

.county-list::-webkit-scrollbar-thumb {
  background: #94a3b8;
  border-radius: 10px;
}

.county-list::-webkit-scrollbar-thumb:hover {
  background: #64748b;
}
</style>