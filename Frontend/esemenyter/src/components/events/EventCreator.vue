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

          <div
            v-if="selectedSchoolTargetGroup !== 'teljes_iskola'"
            class="class-target-dashboard"
          >
            <h4>
              <i class='bx bx-layout'></i>
              Osztály célzás
            </h4>

            <div v-if="isLoadingClasses" class="class-target-state">
              Osztályok betöltése...
            </div>

            <div v-else-if="!institutionClasses.length" class="class-target-state warning">
              Nem található osztály az intézményben. Kérlek hozz létre legalább egy osztályt.
            </div>

            <template v-else>
              <div v-if="requiresManualClassSelection" class="class-target-help">
                Ehhez a célzáshoz válassz egy osztályt. A rendszer ebből számolja a címzetteket.
              </div>

              <div v-else-if="effectiveTargetClassId" class="class-target-help success">
                Automatikusan a saját osztályod lesz használva:
                <strong>{{ selectedTargetClassDisplay }}</strong>
              </div>

              <div
                v-if="requiresManualClassSelection"
                class="class-target-grid"
              >
                <label
                  v-for="classItem in institutionClasses"
                  :key="classItem.id"
                  class="class-target-card"
                  :class="{ selected: Number(selectedClassId) === Number(classItem.id) }"
                >
                  <input
                    type="radio"
                    name="class-target"
                    :value="String(classItem.id)"
                    v-model="selectedClassId"
                    hidden
                  >
                  <div class="class-target-title">{{ formatClassLabel(classItem) }}</div>
                  <div class="class-target-meta">{{ classItem.student_count || 0 }} tanuló</div>
                </label>
              </div>
            </template>
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
              <div v-if="selectedSchoolTargetGroup !== 'teljes_iskola'" class="summary-item">
                <strong>Alap osztály:</strong> {{ selectedTargetClassDisplay || 'Nincs kiválasztva' }}
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
import { toast } from '../../services/toast'

export default {
  name: 'EsemenyKeszito',
  
  data() {
    return {
      // FELHASZNÁLÓ ADATAI
      userRole: 'student',
      currentUserId: null,
      userInstitution: {
        id: null,
        name: 'Kossuth Lajos Gimnázium',
        countyId: null
      },
      userCountyId: 1,
      institutionUserIds: [],
      institutionClasses: [],
      selectedClassId: '',
      isLoadingClasses: false,
      
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
          if (this.selectedEventScope === 'global') {
            return this.selectedCountyIds.length > 0
          } else {
            if (this.selectedSchoolTargetGroup === 'teljes_iskola') {
              return this.selectedSchoolTargetGroup !== ''
            }

            if (!this.institutionClasses.length) {
              return false
            }

            return Boolean(this.effectiveTargetClassId)
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
    },

    assignedClasses() {
      if (!this.currentUserId) {
        return []
      }

      return this.institutionClasses.filter(
        classItem => Number(classItem?.user_id) === Number(this.currentUserId)
      )
    },

    defaultAssignedClassId() {
      if (!this.assignedClasses.length) {
        return null
      }

      return Number(this.assignedClasses[0]?.id) || null
    },

    requiresManualClassSelection() {
      if (this.selectedEventScope !== 'school') {
        return false
      }

      if (!['sajat_osztaly', 'evfolyam'].includes(this.selectedSchoolTargetGroup)) {
        return false
      }

      return !this.defaultAssignedClassId
    },

    effectiveTargetClassId() {
      if (this.selectedSchoolTargetGroup === 'teljes_iskola') {
        return null
      }

      if (this.requiresManualClassSelection) {
        return Number(this.selectedClassId) || null
      }

      return this.defaultAssignedClassId
    },

    selectedTargetClassDisplay() {
      const classId = Number(this.effectiveTargetClassId)
      if (!classId) {
        return ''
      }

      const classItem = this.institutionClasses.find(item => Number(item?.id) === classId)
      if (!classItem) {
        return ''
      }

      return this.formatClassLabel(classItem)
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

        if (!this.institutionClasses.length) {
          this.loadInstitutionClasses()
        }
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
    },

    selectedSchoolTargetGroup() {
      if (!this.requiresManualClassSelection) {
        this.selectedClassId = this.defaultAssignedClassId ? String(this.defaultAssignedClassId) : ''
      }
    },

    currentStep(newValue) {
      if (newValue === 2 && this.selectedEventScope === 'school' && !this.institutionClasses.length) {
        this.loadInstitutionClasses()
      }
    }
  },
  
  created() {
    this.initialize()
  },
  
  methods: {
    async initialize() {
      const token =
        localStorage.getItem('esemenyter_token') ||
        sessionStorage.getItem('esemenyter_token')

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

          const [userResponse, roleResponse] = await Promise.all([
            axios.get('http://127.0.0.1:8000/api/user', { headers }).catch(() => null),
            axios.get('http://127.0.0.1:8000/api/establishment/role', { headers }).catch(() => null)
          ])

          const backendUser = userResponse?.data || {}
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

          if (mergedUser) {
            mergedUser = {
              ...mergedUser,
              id: this.currentUserId || mergedUser.id,
              role: this.userRole,
              institution_id: Number(this.userInstitution.id) || null
            }

            if (localStorage.getItem('esemenyter_token')) {
              localStorage.setItem('esemenyter_user', JSON.stringify(mergedUser))
            } else {
              sessionStorage.setItem('esemenyter_user', JSON.stringify(mergedUser))
            }
          }
        }
      } catch (error) {
        console.error('Felhasználó inicializálási hiba:', error)
      }

      if (!this.canCreateGlobalEvent) {
        this.selectedEventScope = 'school'
      }

      await this.loadInstitutionData()
      await this.loadCounties()
      await this.loadInstitutionUsers()
    },

    async loadInstitutionClasses() {
      try {
        const institutionId = Number(this.userInstitution.id)
        if (!institutionId) {
          this.institutionClasses = []
          return
        }

        const token =
          localStorage.getItem('esemenyter_token') ||
          sessionStorage.getItem('esemenyter_token')

        if (!token) {
          this.institutionClasses = []
          return
        }

        this.isLoadingClasses = true

        const response = await axios.get(`http://127.0.0.1:8000/api/establishment/${institutionId}/classes`, {
          headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' }
        })

        this.institutionClasses = Array.isArray(response?.data?.data)
          ? response.data.data
          : []

        if (this.defaultAssignedClassId) {
          this.selectedClassId = String(this.defaultAssignedClassId)
        }
      } catch (error) {
        console.error('Osztályok betöltési hiba:', error)
        this.institutionClasses = []
      } finally {
        this.isLoadingClasses = false
      }
    },

    async loadInstitutionUsers() {
      try {
        const institutionId = Number(this.userInstitution.id)
        if (!institutionId) {
          this.institutionUserIds = this.currentUserId ? [this.currentUserId] : []
          return
        }

        const token =
          localStorage.getItem('esemenyter_token') ||
          sessionStorage.getItem('esemenyter_token')

        if (!token) {
          this.institutionUserIds = this.currentUserId ? [this.currentUserId] : []
          return
        }

        const [studentsResponse, staffResponse] = await Promise.all([
          axios.get(`http://127.0.0.1:8000/api/members/students/${institutionId}`, {
            headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' }
          }).catch(() => ({ data: { data: [] } })),
          axios.get(`http://127.0.0.1:8000/api/members/staff/${institutionId}`, {
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

        this.userCountyId = Number(this.userInstitution.countyId) || this.userCountyId
      } catch (error) {
        console.error('Intézmény betöltési hiba:', error)
      }
    },

    formatClassLabel(classItem) {
      const grade = Number(classItem?.grade)
      const className = String(classItem?.name || '').trim()

      if (Number.isFinite(grade) && className) {
        return `${grade}.${className}`
      }

      return className || `Osztály #${classItem?.id ?? '?'}`
    },

    async fetchClassMemberUserIds(classId, token) {
      const institutionId = Number(this.userInstitution.id)
      if (!institutionId || !classId) {
        return []
      }

      const response = await axios.get(
        `http://127.0.0.1:8000/api/establishment/${institutionId}/classes/${classId}`,
        {
          headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' }
        }
      )

      const members = Array.isArray(response?.data?.data) ? response.data.data : []
      return members
        .map(member => Number(member?.id))
        .filter(Number.isFinite)
    },

    async resolveLocalTargetUserIds(token) {
      const currentUserIds = this.currentUserId ? [Number(this.currentUserId)] : []

      if (this.selectedSchoolTargetGroup === 'teljes_iskola') {
        return this.institutionUserIds
      }

      const baseClassId = Number(this.effectiveTargetClassId)
      if (!baseClassId) {
        throw new Error('Nincs kiválasztott osztály a célzáshoz.')
      }

      if (this.selectedSchoolTargetGroup === 'sajat_osztaly') {
        const ownClassUsers = await this.fetchClassMemberUserIds(baseClassId, token)
        const selectedClass = this.institutionClasses.find(item => Number(item?.id) === baseClassId)
        const homeroomTeacherId = Number(selectedClass?.user_id)

        return Array.from(new Set([
          ...ownClassUsers,
          ...(Number.isFinite(homeroomTeacherId) ? [homeroomTeacherId] : []),
          ...currentUserIds
        ]))
      }

      if (this.selectedSchoolTargetGroup === 'evfolyam') {
        const baseClass = this.institutionClasses.find(item => Number(item?.id) === baseClassId)
        const baseGrade = Number(baseClass?.grade)

        let classIds = [baseClassId]
        if (Number.isFinite(baseGrade)) {
          classIds = this.institutionClasses
            .filter(item => Number(item?.grade) === baseGrade)
            .map(item => Number(item?.id))
            .filter(Number.isFinite)
        }

        const memberResponses = await Promise.all(
          classIds.map(classId =>
            this.fetchClassMemberUserIds(classId, token).catch(() => [])
          )
        )

        const gradeTeacherIds = this.institutionClasses
          .filter(item => classIds.includes(Number(item?.id)))
          .map(item => Number(item?.user_id))
          .filter(Number.isFinite)

        return Array.from(new Set([
          ...memberResponses.flat(),
          ...gradeTeacherIds,
          ...currentUserIds
        ]))
      }

      return this.institutionUserIds
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

    async resolveCollabEstablishmentIdsByCounties(countyIds) {
      const normalizedCountyIds = Array.from(
        new Set((countyIds || []).map(id => Number(id)).filter(Number.isFinite))
      )

      if (!normalizedCountyIds.length) {
        return []
      }

      const sourceEstablishmentId = Number(this.userInstitution.id)

      // Fast path: one request per county using region filter on establishments.
      const directResponses = await Promise.all(
        normalizedCountyIds.map(countyId =>
          axios.get('http://127.0.0.1:8000/api/establishments', {
            params: { region_id: countyId },
            headers: { Accept: 'application/json' }
          }).catch(() => ({ data: { data: [] } }))
        )
      )

      const directIds = Array.from(new Set(
        directResponses.flatMap(response =>
          (Array.isArray(response?.data?.data) ? response.data.data : [])
            .map(item => Number(item?.id))
            .filter(Number.isFinite)
        )
      )).filter(id => id !== sourceEstablishmentId)

      if (directIds.length) {
        return directIds
      }

      const districtResponses = await Promise.all(
        normalizedCountyIds.map(countyId =>
          axios.get('http://127.0.0.1:8000/api/innerregions/all', {
            params: { region_id: countyId },
            headers: { Accept: 'application/json' }
          }).catch(() => ({ data: { data: [] } }))
        )
      )

      const districtIds = Array.from(new Set(
        districtResponses.flatMap(response =>
          (Array.isArray(response?.data?.data) ? response.data.data : [])
            .map(item => Number(item?.id))
            .filter(Number.isFinite)
        )
      ))

      if (!districtIds.length) {
        return []
      }

      const settlementResponses = await Promise.all(
        districtIds.map(innerRegionId =>
          axios.get('http://127.0.0.1:8000/api/settlements/all', {
            params: { inner_region_id: innerRegionId },
            headers: { Accept: 'application/json' }
          }).catch(() => ({ data: { data: [] } }))
        )
      )

      const settlementIds = Array.from(new Set(
        settlementResponses.flatMap(response =>
          (Array.isArray(response?.data?.data) ? response.data.data : [])
            .map(item => Number(item?.id))
            .filter(Number.isFinite)
        )
      ))

      if (!settlementIds.length) {
        return []
      }

      const establishmentResponses = await Promise.all(
        settlementIds.map(settlementId =>
          axios.get('http://127.0.0.1:8000/api/establishments', {
            params: { settlement_id: settlementId },
            headers: { Accept: 'application/json' }
          }).catch(() => ({ data: { data: [] } }))
        )
      )

      const collabIds = Array.from(new Set(
        establishmentResponses.flatMap(response =>
          (Array.isArray(response?.data?.data) ? response.data.data : [])
            .map(item => Number(item?.id))
            .filter(Number.isFinite)
        )
      )).filter(id => id !== sourceEstablishmentId)

      return collabIds
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

      if (this.selectedEventScope === 'global' && !this.canCreateGlobalEvent) {
        toast.error('Nincs jogosultságod globális esemény létrehozására.')
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

        const establishmentId = Number(this.userInstitution.id)
        if (!establishmentId) {
          toast.error('Hiányzó intézmény azonosító. Frissítsd az oldalt és próbáld újra.')
          return
        }

        // Adatok összeállítása a backend elvárásai szerint
        const payload = {
          title: this.eventForm.title,
          description: this.eventForm.description,
          content: this.eventForm.content,
          start_date: this.eventForm.startDateTime,
          end_date: this.eventForm.endDateTime,
          type: this.selectedEventScope === 'global' ? 'global' : 'local',
          establishment_id: establishmentId
        }

        if (this.selectedEventScope === 'global') {
          const collabEstablishmentIds = await this.resolveCollabEstablishmentIdsByCounties(this.selectedCountyIds)

          if (!collabEstablishmentIds.length) {
            toast.warning('A kiválasztott vármegyékből nem találtunk más intézményt. Válassz további vármegyét.')
            return
          }

          payload.collab_establishment_ids = collabEstablishmentIds
        } else {
          payload.target_group = this.selectedSchoolTargetGroup

          if (!this.institutionUserIds.length) {
            await this.loadInstitutionUsers()
          }

          if (!this.institutionUserIds.length) {
            toast.error('Nem sikerült betölteni az intézmény felhasználóit.')
            return
          }

          // Ensure newly assigned class teachers are included in targeting even if
          // assignments changed after this page was opened.
          await this.loadInstitutionClasses()

          const localTargetUserIds = await this.resolveLocalTargetUserIds(token)

          if (!localTargetUserIds.length) {
            toast.warning('A kiválasztott célcsoporthoz nem találtunk címzett felhasználót.')
            return
          }

          payload.users = localTargetUserIds
        }

        await axios.post('http://127.0.0.1:8000/api/establishment/events', payload, {
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
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

.event-creator-page {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  font-family: 'Inter', sans-serif;
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  box-sizing: border-box;
  width: 100%;
  overflow-x: hidden;
  padding: 20px;
}

.back-button {
  position: absolute;
  top: 24px;
  left: 24px;
  background: white;
  border: 1px solid #e2e8f0;
  padding: 10px 18px;
  border-radius: 12px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 500;
  color: #1e293b;
  z-index: 100;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  transition: all 0.2s ease;
  max-width: 120px;
  white-space: nowrap;
  font-size: 14px;
}

.back-button:hover {
  background: #f8fafc;
  transform: translateX(-3px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.event-creator-wrapper {
  max-width: 1000px;
  width: 100%;
  background: white;
  border-radius: 32px;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  margin: 60px 20px 20px;
  box-sizing: border-box;
  border: 1px solid rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
}

.creator-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 32px 40px;
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
  opacity: 0.8;
  font-size: 15px;
  font-weight: 400;
  position: relative;
}

.creator-content {
  padding: 32px 40px;
  box-sizing: border-box;
  background: white;
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
  margin-bottom: 32px;
}

.steps {
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
  gap: 8px;
  padding: 0 20px;
}

.steps::before {
  content: '';
  position: absolute;
  top: 18px;
  left: 60px;
  right: 60px;
  height: 2px;
  background: #e2e8f0;
  z-index: 1;
}

.step {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  position: relative;
  z-index: 2;
  flex: 1;
  background: white;
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
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.step.active .step-number {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-color: #667eea;
  transform: scale(1.1);
  box-shadow: 0 10px 15px -3px rgba(102, 126, 234, 0.35);
}

.step.completed .step-number {
  background: #10b981;
  color: white;
  border-color: #10b981;
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

.form-section {
  margin-bottom: 32px;
  padding-bottom: 24px;
  border-bottom: 1px solid #e2e8f0;
  box-sizing: border-box;
}

.form-section:last-child {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
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

.type-selection, .target-group-options {
  display: grid;
  grid-template-columns: 1fr;
  gap: 20px;
  margin-top: 20px;
  box-sizing: border-box;
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
  box-shadow: 0 20px 25px -5px rgba(102, 126, 234, 0.2);
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

/* Új stílusok a globális megye választóhoz */
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
}

.class-target-card:hover {
  border-color: #94a3b8;
}

.class-target-card.selected {
  border-color: #4f46e5;
  box-shadow: 0 0 0 1px #4f46e5;
  background: #eef2ff;
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

.form-group input, .form-group textarea {
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

.form-group input:focus, .form-group textarea:focus {
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

/* gombok */
.form-actions {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  margin-top: 32px;
  padding-top: 24px;
  border-top: 1px solid #e2e8f0;
  box-sizing: border-box;
}

.btn {
  padding: 14px 28px;
  border: none;
  border-radius: 16px;
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
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  box-shadow: 0 4px 10px rgba(102, 126, 234, 0.3);
}

.btn-primary:hover:not(:disabled) {
  background: linear-gradient(135deg, #5b6ee8 0%, #6b43a0 100%);
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
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
  box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2);
}

.btn-success:hover:not(:disabled) {
  background: #059669;
  transform: translateY(-2px);
  box-shadow: 0 20px 25px -5px rgba(16, 185, 129, 0.3);
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
    padding: 12px;
    align-items: flex-start;
  }
  
  .event-creator-wrapper {
    margin: 60px 10px 10px;
    max-width: 100%;
  }
  
  .creator-header {
    padding: 24px;
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
  
  .creator-content {
    padding: 24px;
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
  }
}

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
    padding: 20px;
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
    padding: 20px;
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
  
  .btn {
    width: 100%;
    padding: 14px;
  }
}

@media (min-width: 769px) {
  .type-selection, .target-group-options {
    grid-template-columns: 1fr 1fr;
  }
  
  .form-actions .btn {
    flex: none;
    min-width: 160px;
  }
}

@media (min-width: 1024px) {
  .event-creator-wrapper {
    max-width: 1000px;
  }
  
  .creator-content {
    padding: 40px;
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