<template>
  <div class="main-page">
    <!-- Fejléc -->
    <header class="main-header">
      <div class="container">
        <div class="header-content">
          <div class="logo-section">
            <h1 class="site-title">EseményTér</h1>
            <p class="site-subtitle">Ahol minden program helyet kap</p>
          </div>
          
          <!-- Bejelentkezés/Regisztráció -->
          <div v-if="!isLoggedIn" class="auth-buttons">
            <button class="btn btn-outline-primary" @click="goToLogin">
              <i class='bx bx-log-in'></i> Bejelentkezés
            </button>
            <button class="btn btn-primary" @click="goToRegister">
              <i class='bx bx-user-plus'></i> Regisztráció
            </button>
          </div>
          
          <!-- Bejelentkezett felhasználó -->
          <div v-else class="user-section">
            <div class="user-info">
              <span class="user-name">{{ user.name }}</span>
              <span class="user-role">{{ user.role }}</span>
            </div>
            <button class="btn btn-outline-danger btn-sm" @click="logout">
              <i class='bx bx-log-out'></i> Kijelentkezés
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Fő tartalom -->
    <main class="main-content">
      <div class="container">
        
        <!-- Szerepkör kiválasztás (ha nincs beállítva) -->
        <div v-if="isLoggedIn && !profileConfigured" class="role-selection-section">
          <h2 class="text-center mb-4">Válaszd ki a szerepkörödet</h2>
          
          <div class="role-cards">
            <!-- Diák -->
            <div class="role-card" :class="{ 'selected': selectedRole === 'student' }" @click="selectedRole = 'student'">
              <div class="role-icon">
                <i class='bx bx-user'></i>
              </div>
              <h3>Diák</h3>
              <p>Eseményekre jelentkezhetsz, szavazhatsz, hozzászólhatsz.</p>
              <button v-if="selectedRole === 'student'" class="btn btn-primary mt-3" @click="setupStudent">
                Diákként folytatás
              </button>
            </div>
            
            <!-- Tanár -->
            <div class="role-card" :class="{ 'selected': selectedRole === 'teacher' }" @click="selectedRole = 'teacher'">
              <div class="role-icon">
                <i class='bx bx-chalkboard'></i>
              </div>
              <h3>Tanár</h3>
              <p>Eseményeket hozhatsz létre, szavazásokat indíthatsz.</p>
              <button v-if="selectedRole === 'teacher'" class="btn btn-primary mt-3" @click="setupTeacher">
                Tanárként folytatás
              </button>
            </div>
            
            <!-- Admin -->
            <div class="role-card" :class="{ 'selected': selectedRole === 'admin' }" @click="selectedRole = 'admin'">
              <div class="role-icon">
                <i class='bx bx-cog'></i>
              </div>
              <h3>Adminisztrátor</h3>
              <p>Iskolákat regisztrálhatsz, felhasználókat kezelhetsz.</p>
              <button v-if="selectedRole === 'admin'" class="btn btn-primary mt-3" @click="setupAdmin">
                Adminisztrátorként folytatás
              </button>
            </div>
          </div>
        </div>

        <!-- Diák beállítás -->
        <div v-if="selectedRole === 'student' && !profileConfigured" class="setup-section">
          <h3 class="mb-4">Diák beállítás</h3>
          <p class="mb-4">Válaszd ki az iskolád és osztályod:</p>
          
          <div class="setup-steps">
            <!-- 1. Megye -->
            <div class="form-group mb-3">
              <label class="form-label">1. Válassz megyét:</label>
              <select v-model="selectedCounty" @change="loadCities" class="form-control">
                <option value="">-- válassz megyét --</option>
                <option v-for="county in counties" :key="county.id" :value="county.id">
                  {{ county.name }}
                </option>
              </select>
            </div>
            
            <!-- 2. Város -->
            <div class="form-group mb-3">
              <label class="form-label">2. Válassz várost:</label>
              <select v-model="selectedCity" @change="loadSchools" :disabled="!selectedCounty" class="form-control">
                <option value="">-- válassz várost --</option>
                <option v-for="city in filteredCities" :key="city.id" :value="city.id">
                  {{ city.name }}
                </option>
              </select>
            </div>
            
            <!-- 3. Iskola -->
            <div class="form-group mb-3">
              <label class="form-label">3. Válassz iskolát:</label>
              <select v-model="selectedSchool" @change="loadClasses" :disabled="!selectedCity" class="form-control">
                <option value="">-- válassz iskolát --</option>
                <option v-for="school in filteredSchools" :key="school.id" :value="school.id">
                  {{ school.name }}
                </option>
              </select>
            </div>
            
            <!-- 4. Osztály -->
            <div class="form-group mb-4">
              <label class="form-label">4. Válassz osztályt:</label>
              <select v-model="selectedClass" :disabled="!selectedSchool" class="form-control">
                <option value="">-- válassz osztályt --</option>
                <option v-for="classItem in filteredClasses" :key="classItem.id" :value="classItem.id">
                  {{ classItem.grade }}. évfolyam {{ classItem.name }} osztály
                </option>
              </select>
            </div>
            
            <div class="d-flex gap-2">
              <button class="btn btn-secondary" @click="selectedRole = ''">
                <i class='bx bx-arrow-back'></i> Vissza
              </button>
              <button class="btn btn-primary" @click="saveStudentSetup" :disabled="!selectedClass">
                <i class='bx bx-check'></i> Mentés
              </button>
            </div>
          </div>
        </div>

        <!-- Tanár beállítás -->
        <div v-if="selectedRole === 'teacher' && !profileConfigured" class="setup-section">
          <h3 class="mb-4">Tanár beállítás</h3>
          
          <div class="form-group mb-3">
            <label class="form-label">Válassz iskolá(ka)t ahol tanítasz:</label>
            <div class="school-selection">
              <div v-for="school in allSchools" :key="school.id" 
                   class="school-option" 
                   :class="{ 'selected': teacherSchools.includes(school.id) }"
                   @click="toggleTeacherSchool(school.id)">
                <input type="checkbox" :value="school.id" v-model="teacherSchools" hidden>
                <div class="school-info">
                  <h5>{{ school.name }}</h5>
                  <p>{{ school.city }}, {{ school.county }}</p>
                </div>
              </div>
            </div>
          </div>
          
          <div class="form-group mb-4">
            <label class="form-label">Osztályfőnök vagy?</label>
            <div class="d-flex gap-3">
              <div class="form-check">
                <input class="form-check-input" type="radio" v-model="isClassTeacher" value="yes" id="ct-yes">
                <label class="form-check-label" for="ct-yes">Igen</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" v-model="isClassTeacher" value="no" id="ct-no">
                <label class="form-check-label" for="ct-no">Nem</label>
              </div>
            </div>
            
            <!-- Ha osztályfőnök -->
            <div v-if="isClassTeacher === 'yes'" class="mt-3">
              <label class="form-label">Melyik osztály osztályfőnöke vagy?</label>
              <select v-model="classTeacherClass" class="form-control">
                <option value="">-- válassz osztályt --</option>
                <option v-for="classItem in allClasses" :key="classItem.id" :value="classItem.id">
                  {{ classItem.school }} - {{ classItem.grade }}.{{ classItem.name }}
                </option>
              </select>
            </div>
          </div>
          
          <div class="d-flex gap-2">
            <button class="btn btn-secondary" @click="selectedRole = ''">
              <i class='bx bx-arrow-back'></i> Vissza
            </button>
            <button class="btn btn-primary" @click="saveTeacherSetup" :disabled="teacherSchools.length === 0">
              <i class='bx bx-check'></i> Mentés
            </button>
          </div>
        </div>

        <!-- Admin beállítás (iskola létrehozás) -->
        <div v-if="selectedRole === 'admin' && !profileConfigured" class="setup-section">
          <h3 class="mb-4">Adminisztrátor - Új iskola létrehozása</h3>
          
          <div class="school-creation-form">
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Iskola neve *</label>
                <input type="text" v-model="newSchool.name" class="form-control" placeholder="pl. Kossuth Lajos Gimnázium" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Város *</label>
                <input type="text" v-model="newSchool.city" class="form-control" placeholder="pl. Budapest" required>
              </div>
            </div>
            
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Megye *</label>
                <select v-model="newSchool.county" class="form-control" required>
                  <option value="">-- válassz megyét --</option>
                  <option v-for="county in counties" :key="county.id" :value="county.id">
                    {{ county.name }}
                  </option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Járás (opcionális)</label>
                <input type="text" v-model="newSchool.district" class="form-control" placeholder="pl. III. kerület">
              </div>
            </div>
            
            <div class="row mb-4">
              <div class="col-md-4">
                <label class="form-label">Évfolyamok száma</label>
                <input type="number" v-model="newSchool.grades" min="1" max="12" class="form-control" value="12">
              </div>
              <div class="col-md-4">
                <label class="form-label">Osztályok/évfolyam</label>
                <input type="number" v-model="newSchool.classesPerGrade" min="1" max="10" class="form-control" value="3">
              </div>
              <div class="col-md-4">
                <label class="form-label">Iskola típusa</label>
                <select v-model="newSchool.type" class="form-control">
                  <option value="gimnazium">Gimnázium</option>
                  <option value="altalanos">Általános iskola</option>
                  <option value="szakkozep">Szakközépiskola</option>
                  <option value="szakiskola">Szakiskola</option>
                </select>
              </div>
            </div>
            
            <div class="d-flex gap-2">
              <button class="btn btn-secondary" @click="selectedRole = ''">
                <i class='bx bx-arrow-back'></i> Vissza
              </button>
              <button class="btn btn-primary" @click="saveAdminSetup" :disabled="!isNewSchoolValid">
                <i class='bx bx-check'></i> Iskola létrehozása
              </button>
            </div>
          </div>
        </div>

        <!-- Főoldal (ha már beállította a profilját) -->
        <div v-if="profileConfigured" class="dashboard">
          <!-- Oldalsáv + Tartalom -->
          <div class="row">
            <!-- Oldalsáv -->
            <div class="col-md-3">
              <div class="sidebar">
                <nav class="nav flex-column">
                  <router-link to="/mainpage" class="nav-link" active-class="active">
                    <i class='bx bx-home'></i> Kezdőlap
                  </router-link>
                  
                  <router-link to="/esemenyek" class="nav-link" active-class="active">
                    <i class='bx bx-calendar'></i> Események
                  </router-link>
                  
                  <router-link v-if="user.role === 'teacher' || user.role === 'admin'" 
                             to="/event-creator" 
                             class="nav-link" 
                             active-class="active">
                    <i class='bx bx-plus-circle'></i> Új esemény
                  </router-link>
                  
                  <router-link v-if="user.role === 'admin'" 
                             to="/admin" 
                             class="nav-link" 
                             active-class="active">
                    <i class='bx bx-cog'></i> Admin panel
                  </router-link>
                  
                  <router-link to="/profil" class="nav-link" active-class="active">
                    <i class='bx bx-user'></i> Profil
                  </router-link>
                </nav>
              </div>
            </div>
            
            <!-- Tartalom -->
            <div class="col-md-9">
              <div class="dashboard-content">
                <!-- Üdvözlés -->
                <div class="welcome-card mb-4">
                  <h3>Üdvözöljük újra, {{ user.name }}!</h3>
                  <p class="text-muted">
                    <i class='bx bx-building'></i> {{ user.school }}
                    <span v-if="user.class"> | <i class='bx bx-group'></i> {{ user.class }}. osztály</span>
                  </p>
                </div>
                
                <!-- Diák dashboard -->
                <div v-if="user.role === 'student'" class="student-dashboard">
                  <div class="row mb-4">
                    <div class="col-md-4">
                      <div class="stat-card">
                        <div class="stat-icon">
                          <i class='bx bx-calendar'></i>
                        </div>
                        <div class="stat-info">
                          <h4>{{ stats.upcomingEvents }}</h4>
                          <p>Közelgő esemény</p>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="stat-card">
                        <div class="stat-icon">
                          <i class='bx bx-check-circle'></i>
                        </div>
                        <div class="stat-info">
                          <h4>{{ stats.registeredEvents }}</h4>
                          <p>Jelentkezve</p>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="stat-card">
                        <div class="stat-icon">
                          <i class='bx bx-poll'></i>
                        </div>
                        <div class="stat-info">
                          <h4>{{ stats.activePolls }}</h4>
                          <p>Aktív szavazás</p>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Ajánlott események -->
                  <div class="recommended-events">
                    <h4 class="mb-3">Ajánlott események</h4>
                    <div class="event-list">
                      <div v-for="event in recommendedEvents" :key="event.id" class="event-card">
                        <div class="event-header">
                          <h5>{{ event.title }}</h5>
                          <span class="event-date">{{ event.date }}</span>
                        </div>
                        <p class="event-location">{{ event.location }}</p>
                        <div class="event-actions">
                          <button class="btn btn-sm btn-outline-primary">Részletek</button>
                          <button class="btn btn-sm btn-primary">Jelentkezés</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Tanár dashboard -->
                <div v-if="user.role === 'teacher'" class="teacher-dashboard">
                  <div class="row mb-4">
                    <div class="col-md-6">
                      <div class="teacher-stat-card">
                        <h4>Szervezett események</h4>
                        <h2 class="text-primary">{{ stats.organizedEvents }}</h2>
                        <button class="btn btn-primary mt-2" @click="goToEventCreator">
                          <i class='bx bx-plus'></i> Új esemény
                        </button>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="teacher-stat-card">
                        <h4>Aktív szavazások</h4>
                        <h2 class="text-success">{{ stats.teacherPolls }}</h2>
                        <button class="btn btn-outline-success mt-2" @click="startPoll">
                          <i class='bx bx-poll'></i> Szavazás indítása
                        </button>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Gyors műveletek -->
                  <div class="quick-actions mb-4">
                    <h4 class="mb-3">Gyors műveletek</h4>
                    <div class="d-flex gap-2 flex-wrap">
                      <button class="btn btn-outline-primary">
                        <i class='bx bx-list-check'></i> Résztvevők listája
                      </button>
                      <button class="btn btn-outline-primary">
                        <i class='bx bx-bell'></i> Értesítés küldése
                      </button>
                      <button class="btn btn-outline-primary">
                        <i class='bx bx-download'></i> Exportálás
                      </button>
                    </div>
                  </div>
                </div>
                
                <!-- Admin dashboard -->
                <div v-if="user.role === 'admin'" class="admin-dashboard">
                  <div class="admin-header mb-4">
                    <h3>Adminisztráció - {{ user.school }}</h3>
                    <p class="text-muted">Iskola kezelése és konfigurálása</p>
                  </div>
                  
                  <div class="row">
                    <div class="col-md-3 mb-3">
                      <div class="admin-card" @click="manageUsers">
                        <div class="admin-card-icon">
                          <i class='bx bx-user'></i>
                        </div>
                        <h5>Felhasználók</h5>
                        <p>{{ stats.schoolUsers }} felhasználó</p>
                      </div>
                    </div>
                    <div class="col-md-3 mb-3">
                      <div class="admin-card" @click="manageClasses">
                        <div class="admin-card-icon">
                          <i class='bx bx-building'></i>
                        </div>
                        <h5>Osztályok</h5>
                        <p>{{ stats.classesCount }} osztály</p>
                      </div>
                    </div>
                    <div class="col-md-3 mb-3">
                      <div class="admin-card" @click="manageEvents">
                        <div class="admin-card-icon">
                          <i class='bx bx-calendar'></i>
                        </div>
                        <h5>Események</h5>
                        <p>{{ stats.totalEvents }} esemény</p>
                      </div>
                    </div>
                    <div class="col-md-3 mb-3">
                      <div class="admin-card" @click="goToSettings">
                        <div class="admin-card-icon">
                          <i class='bx bx-cog'></i>
                        </div>
                        <h5>Beállítások</h5>
                        <p>Iskola konfigurálása</p>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Iskola információk -->
                  <div class="school-info-card mt-4">
                    <h5>Iskola információk</h5>
                    <div class="row mt-3">
                      <div class="col-md-6">
                        <p><strong>Város:</strong> {{ newSchool.city }}</p>
                        <p><strong>Megye:</strong> {{ getCountyName(newSchool.county) }}</p>
                      </div>
                      <div class="col-md-6">
                        <p><strong>Típus:</strong> {{ getSchoolTypeLabel(newSchool.type) }}</p>
                        <p><strong>Évfolyamok:</strong> {{ newSchool.grades }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Kezdőlap (ha nincs bejelentkezve) -->
        <div v-if="!isLoggedIn" class="homepage">
          <div class="hero-section text-center py-5">
            <h1 class="display-4 mb-4">Üdvözöljük az EseményTérben!</h1>
            <p class="lead mb-4">Az iskolai események egyszerű kezelése és kommunikációja</p>
            
            <div class="features mb-5">
              <div class="row">
                <div class="col-md-4 mb-3">
                  <div class="feature-card">
                    <i class='bx bx-calendar-event'></i>
                    <h4>Események kezelése</h4>
                    <p>Könnyedén hozz létre és kezelj iskolai eseményeket</p>
                  </div>
                </div>
                <div class="col-md-4 mb-3">
                  <div class="feature-card">
                    <i class='bx bx-group'></i>
                    <h4>Jelentkezés és szavazás</h4>
                    <p>Diákok egyszerűen jelentkezhetnek és szavazhatnak</p>
                  </div>
                </div>
                <div class="col-md-4 mb-3">
                  <div class="feature-card">
                    <i class='bx bx-bell'></i>
                    <h4>Értesítések</h4>
                    <p>Mindig értesülj a fontos változásokról</p>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="cta-buttons">
              <button class="btn btn-primary btn-lg me-3" @click="goToLogin">
                <i class='bx bx-log-in'></i> Bejelentkezés
              </button>
              <button class="btn btn-outline-primary btn-lg" @click="goToRegister">
                <i class='bx bx-user-plus'></i> Regisztráció
              </button>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
export default {
  name: 'MainPage',
  
  data() {
    return {
      // Felhasználó állapot
      isLoggedIn: false,
      user: {
        id: null,
        name: '',
        email: '',
        role: '',
        school: '',
        class: '',
        schoolId: null,
        classId: null
      },
      profileConfigured: false,
      
      // Szerepkör választás
      selectedRole: '',
      
      // Diák adatok
      selectedCounty: '',
      selectedCity: '',
      selectedSchool: '',
      selectedClass: '',
      
      // Tanár adatok
      teacherSchools: [],
      isClassTeacher: 'no',
      classTeacherClass: '',
      
      // Admin adatok
      newSchool: {
        name: '',
        county: '',
        city: '',
        district: '',
        grades: 12,
        classesPerGrade: 3,
        type: 'gimnazium'
      },
      
      // Adatbázis adatok (demo)
      counties: [
        { id: 1, name: 'Budapest' },
        { id: 2, name: 'Bács-Kiskun' },
        { id: 3, name: 'Baranya' },
        { id: 4, name: 'Békés' },
        { id: 5, name: 'Borsod-Abaúj-Zemplén' }
      ],
      
      cities: [
        { id: 1, name: 'Budapest', county_id: 1 },
        { id: 2, name: 'Kecskemét', county_id: 2 },
        { id: 3, name: 'Pécs', county_id: 3 },
        { id: 4, name: 'Békéscsaba', county_id: 4 },
        { id: 5, name: 'Miskolc', county_id: 5 }
      ],
      
      schools: [
        { id: 1, name: 'Kossuth Lajos Gimnázium', city_id: 1, county_id: 1 },
        { id: 2, name: 'Petőfi Sándor Általános Iskola', city_id: 1, county_id: 1 },
        { id: 3, name: 'Bolyai János Gimnázium', city_id: 5, county_id: 5 }
      ],
      
      classes: [
        { id: 1, name: 'A', grade: 9, school_id: 1 },
        { id: 2, name: 'B', grade: 9, school_id: 1 },
        { id: 3, name: 'A', grade: 10, school_id: 1 },
        { id: 4, name: 'B', grade: 10, school_id: 1 }
      ],
      
      // Statisztikák
      stats: {
        upcomingEvents: 3,
        registeredEvents: 2,
        activePolls: 1,
        organizedEvents: 5,
        teacherPolls: 2,
        schoolUsers: 125,
        classesCount: 24,
        totalEvents: 15
      },
      
      // Ajánlott események
      recommendedEvents: [
        { id: 1, title: 'Tavaszi kirándulás', date: '2024. április 15.', location: 'Balaton' },
        { id: 2, title: 'Iskolai verseny', date: '2024. április 20.', location: 'Iskola tornaterme' },
        { id: 3, title: 'Tavaszi ünnepség', date: '2024. április 25.', location: 'Iskola udvara' }
      ]
    }
  },
  
  computed: {
    // Szűrt adatok
    filteredCities() {
      if (!this.selectedCounty) return []
      return this.cities.filter(city => city.county_id == this.selectedCounty)
    },
    
    filteredSchools() {
      if (!this.selectedCity) return []
      return this.schools.filter(school => school.city_id == this.selectedCity)
    },
    
    filteredClasses() {
      if (!this.selectedSchool) return []
      return this.classes.filter(classItem => classItem.school_id == this.selectedSchool)
    },
    
    allSchools() {
      return this.schools
    },
    
    allClasses() {
      return this.classes.map(classItem => {
        const school = this.schools.find(s => s.id === classItem.school_id)
        return {
          ...classItem,
          school: school ? school.name : 'Ismeretlen'
        }
      })
    },
    
    isNewSchoolValid() {
      return this.newSchool.name && this.newSchool.county && this.newSchool.city
    }
  },
  
  methods: {
    // FONTOS: Bejelentkezés ellenőrzése
    checkLoginStatus() {
      console.log('Bejelentkezési állapot ellenőrzése...');
      
      const savedUser = localStorage.getItem('esemenyter_user');
      
      if (!savedUser) {
        console.log('❌ Nincs user a localStorage-ban - nincs bejelentkezve');
        this.isLoggedIn = false;
        this.profileConfigured = false;
        return;
      }
      
      try {
        const userData = JSON.parse(savedUser);
        console.log('Parsed user data:', userData);
        
        // Fontos: csak akkor tekintjük bejelentkezettnek, ha explicit van isLoggedIn flag
        if (userData.isLoggedIn === true || userData.token) {
          this.user = {
            id: userData.id || Date.now(),
            name: userData.name || 'Felhasználó',
            email: userData.email || '',
            role: userData.role || '',
            school: userData.school || '',
            class: userData.class || '',
            schoolId: userData.schoolId || null,
            classId: userData.classId || null
          };
          
          this.isLoggedIn = true;
          this.profileConfigured = !!userData.role; // Ha van role, akkor be van állítva
          
          console.log('✅ Bejelentkezve:', this.isLoggedIn);
          console.log('✅ User neve:', this.user.name);
          console.log('✅ Profil beállítva:', this.profileConfigured);
          
        } else {
          console.log('❌ User nincs bejelentkezve (nincs isLoggedIn flag)');
          this.isLoggedIn = false;
          this.profileConfigured = false;
          // Töröljük a hibás adatot
          localStorage.removeItem('esemenyter_user');
        }
        
      } catch (error) {
        console.error('❌ Hibás JSON a localStorage-ban:', error);
        localStorage.removeItem('esemenyter_user');
        this.isLoggedIn = false;
        this.profileConfigured = false;
      }
    },
    
    // Navigáció
    goToLogin() {
      this.$router.push('/login')
    },
    
    goToRegister() {
      this.$router.push('/register')
    },
    
    goToEventCreator() {
      this.$router.push('/event-creator')
    },
    
    // Diák beállítás
    setupStudent() {
      this.selectedRole = 'student'
    },
    
    saveStudentSetup() {
      const selectedSchool = this.schools.find(s => s.id == this.selectedSchool)
      const selectedClass = this.classes.find(c => c.id == this.selectedClass)
      
      this.user.role = 'student'
      this.user.school = selectedSchool ? selectedSchool.name : 'Ismeretlen'
      this.user.class = selectedClass ? `${selectedClass.grade}.${selectedClass.name}` : ''
      this.user.schoolId = this.selectedSchool
      this.user.classId = this.selectedClass
      
      this.profileConfigured = true
      alert('Diák profil sikeresen beállítva!')
    },
    
    // Tanár beállítás
    setupTeacher() {
      this.selectedRole = 'teacher'
    },
    
    toggleTeacherSchool(schoolId) {
      const index = this.teacherSchools.indexOf(schoolId)
      if (index === -1) {
        this.teacherSchools.push(schoolId)
      } else {
        this.teacherSchools.splice(index, 1)
      }
    },
    
    saveTeacherSetup() {
      const schoolNames = this.schools
        .filter(s => this.teacherSchools.includes(s.id))
        .map(s => s.name)
        .join(', ')
      
      this.user.role = 'teacher'
      this.user.school = schoolNames
      this.user.schoolId = this.teacherSchools[0]
      
      if (this.isClassTeacher === 'yes' && this.classTeacherClass) {
        const selectedClass = this.classes.find(c => c.id == this.classTeacherClass)
        this.user.class = selectedClass ? `${selectedClass.grade}.${selectedClass.name}` : ''
        this.user.classId = this.classTeacherClass
      }
      
      this.profileConfigured = true
      alert('Tanár profil sikeresen beállítva!')
    },
    
    // Admin beállítás
    setupAdmin() {
      this.selectedRole = 'admin'
    },
    
    saveAdminSetup() {
      // Új iskola létrehozása
      const newSchoolId = this.schools.length + 1
      this.schools.push({
        id: newSchoolId,
        name: this.newSchool.name,
        city_id: 1, // Demo
        county_id: this.newSchool.county
      })
      
      // Osztályok generálása
      for (let grade = 1; grade <= this.newSchool.grades; grade++) {
        for (let i = 0; i < this.newSchool.classesPerGrade; i++) {
          const className = String.fromCharCode(65 + i) // A, B, C...
          this.classes.push({
            id: this.classes.length + 1,
            name: className,
            grade: grade,
            school_id: newSchoolId
          })
        }
      }
      
      this.user.role = 'admin'
      this.user.school = this.newSchool.name
      this.user.schoolId = newSchoolId
      
      this.profileConfigured = true
      this.stats.classesCount = this.newSchool.grades * this.newSchool.classesPerGrade
      
      alert(`Sikeresen létrehoztad a(z) ${this.newSchool.name} iskolát!`)
    },
    
    // Segéd függvények
    getCountyName(countyId) {
      const county = this.counties.find(c => c.id == countyId)
      return county ? county.name : ''
    },
    
    getSchoolTypeLabel(type) {
      const types = {
        gimnazium: 'Gimnázium',
        altalanos: 'Általános iskola',
        szakkozep: 'Szakközépiskola',
        szakiskola: 'Szakiskola'
      }
      return types[type] || type
    },
    
    // Demo adatok betöltése
    loadCities() {
      this.selectedCity = ''
      this.selectedSchool = ''
      this.selectedClass = ''
    },
    
    loadSchools() {
      this.selectedSchool = ''
      this.selectedClass = ''
    },
    
    loadClasses() {
      this.selectedClass = ''
    },
    
    // Admin műveletek
    manageUsers() {
      alert('Felhasználók kezelése (demo)')
    },
    
    manageClasses() {
      alert('Osztályok kezelése (demo)')
    },
    
    manageEvents() {
      alert('Események kezelése (demo)')
    },
    
    goToSettings() {
      alert('Beállítások (demo)')
    },
    
    startPoll() {
      alert('Szavazás indítása (demo)')
    },
    
    // Kijelentkezés
    logout() {
      // 1. Töröld a felhasználói adatokat a localStorage-ből
      localStorage.removeItem('esemenyter_user');
      
      // 2. Reseteld az összes állapotváltozót
      this.isLoggedIn = false;
      this.user = {
        id: null,
        name: '',
        email: '',
        role: '',
        school: '',
        class: '',
        schoolId: null,
        classId: null
      };
      this.profileConfigured = false;
      this.selectedRole = '';
      
      // 3. Opcionális: visszaugrás a kezdőlap tetejére
      window.scrollTo(0, 0);
      
      console.log('✅ Sikeres kijelentkezés, localStorage törölve');
    }
  },

  // Lifecycle hooks
  created() {
    console.log('=== MAINPAGE CREATED - BEJELENTKEZÉS ELLENŐRZÉS ===');
    this.checkLoginStatus();
  },
  
  beforeMount() {
    console.log('=== BEFORE MOUNT - FRISSÍTÉS ===');
    // Ellenőrizzük, hogy a localStorage és a komponens állapota szinkronban van-e
    const savedUser = localStorage.getItem('esemenyter_user');
    if (!savedUser && this.isLoggedIn) {
      console.log('⚠️ Inkonzistens állapot: nincs localStorage, de isLoggedIn=true');
      this.isLoggedIn = false;
      this.profileConfigured = false;
    }
  },
  
  mounted() {
    console.log('MainPage mounted');
  },

  // Watch - UTOLSÓ
  watch: {
    '$route'() {
      console.log('Route changed, checking login...');
      this.checkLoginStatus();
    }
  }
}
</script>

<style scoped>
/* ====================
   SAJÁT STÍLUSOK
   ==================== */

/* Fejléc */
.main-header {
  background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
  box-sizing: border-box;
    font-family: "Poppins", sans-serif;

    display: flex;
    justify-content: center;
    align-items: center;
    
    min-height: 100vh;
    width: 99vw;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.logo-section h1 {
  margin: 0;
  font-size: 1.8rem;
  font-weight: 700;
}

.site-subtitle {
  margin: 0;
  opacity: 0.9;
  font-size: 0.9rem;
}

/* Szerepkör kártyák */
.role-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
  margin-top: 2rem;
}

.role-card {
  background: white;
  border: 2px solid #dee2e6;
  border-radius: 12px;
  padding: 2rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.role-card:hover {
  border-color: #4f46e5;
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.role-card.selected {
  border-color: #4f46e5;
  background: #f8f9ff;
}

.role-icon {
  font-size: 3rem;
  color: #4f46e5;
  margin-bottom: 1rem;
}

.role-card h3 {
  margin-bottom: 1rem;
  color: #333;
}

.role-card p {
  color: #666;
  flex-grow: 1;
}

/* Beállítás szekciók */
.setup-section {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  margin-top: 2rem;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

.school-selection {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1rem;
  max-height: 300px;
  overflow-y: auto;
  padding: 1rem;
  border: 1px solid #dee2e6;
  border-radius: 8px;
}

.school-option {
  padding: 1rem;
  border: 2px solid #dee2e6;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.school-option:hover {
  border-color: #adb5bd;
}

.school-option.selected {
  border-color: #4f46e5;
  background: #f8f9ff;
}

/* Oldalsáv */
.sidebar {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  height: fit-content;
}

.nav-link {
  color: #495057;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  transition: all 0.3s ease;
}

.nav-link:hover {
  background: #f8f9fa;
  color: #4f46e5;
}

.nav-link.active {
  background: #4f46e5;
  color: white;
}

.nav-link i {
  font-size: 1.25rem;
}

/* Dashboard tartalom */
.dashboard-content {
  padding-left: 2rem;
}

.welcome-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 2rem;
  border-radius: 12px;
}

/* Statisztika kártyák */
.stat-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1.5rem;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  height: 100%;
}

.stat-icon {
  font-size: 2.5rem;
  color: #4f46e5;
}

.stat-info h4 {
  font-size: 2rem;
  font-weight: 700;
  margin: 0;
  color: #333;
}

.stat-info p {
  margin: 0;
  color: #666;
}

.teacher-stat-card {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  text-align: center;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  height: 100%;
}

/* Esemény kártyák */
.event-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.event-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  border: 1px solid #dee2e6;
  transition: all 0.3s ease;
}

.event-card:hover {
  border-color: #4f46e5;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.event-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.75rem;
}

.event-header h5 {
  margin: 0;
  color: #333;
}

.event-date {
  background: #f8f9fa;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.85rem;
  color: #666;
}

.event-location {
  color: #666;
  margin-bottom: 1rem;
}

.event-actions {
  display: flex;
  gap: 0.5rem;
}

/* Admin kártyák */
.admin-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 2px solid transparent;
  height: 100%;
}

.admin-card:hover {
  border-color: #4f46e5;
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.admin-card-icon {
  font-size: 2.5rem;
  color: #4f46e5;
  margin-bottom: 1rem;
}

.admin-card h5 {
  margin-bottom: 0.5rem;
  color: #333;
}

.admin-card p {
  color: #666;
  margin: 0;
  font-size: 0.9rem;
}

.school-info-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

/* Kezdőlap */
.hero-section {
  background: white;
  border-radius: 20px;
  padding: 4rem 2rem !important;
  margin-top: 2rem;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.feature-card {
  background: #f8f9ff;
  border-radius: 12px;
  padding: 2rem;
  text-align: center;
  height: 100%;
  transition: all 0.3s ease;
}

.feature-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.feature-card i {
  font-size: 3rem;
  color: #4f46e5;
  margin-bottom: 1rem;
}

.feature-card h4 {
  margin-bottom: 1rem;
  color: #333;
}

.feature-card p {
  color: #666;
  margin: 0;
}

/* Reszponzív */
@media (max-width: 768px) {
  .header-content {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
  
  .role-cards {
    grid-template-columns: 1fr;
  }
  
  .dashboard-content {
    padding-left: 0;
    margin-top: 2rem;
  }
  
  .hero-section {
    padding: 2rem 1rem !important;
  }
}
</style>