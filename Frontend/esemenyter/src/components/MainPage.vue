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
              <label class="form-label">1. Válassz régiót:</label>
              
              <!-- Autocomplete input -->
              <div class="autocomplete-wrapper">
                <input
                  type="text"
                  v-model="searchQuery"
                  @input="onInput"
                  @focus="showDropdown = true"
                  @blur="onBlur"
                  @keydown.down="highlightNext"
                  @keydown.up="highlightPrev"
                  @keydown.enter="selectHighlighted"
                  placeholder="Kezdj el gépelni a régió nevéből..."
                  class="form-control"
                  :class="{ 'is-loading': isLoading }"
                />
                
                <!-- Loading indicator -->
                <div v-if="isLoading" class="autocomplete-loading">
                  <span class="spinner-border spinner-border-sm"></span>
                </div>
                
                <!-- Clear button -->
                <button 
                  v-if="searchQuery" 
                  @click="clearSearch"
                  class="autocomplete-clear"
                  type="button"
                >
                  &times;
                </button>
                
                <!-- Dropdown with suggestions -->
                <div 
                  v-if="showDropdown && filteredRegions.length > 0" 
                  class="autocomplete-dropdown"
                >
                  <ul class="autocomplete-list">
                    <li 
                      v-for="(region, index) in filteredRegions" 
                      :key="region.id"
                      @click="selectRegion(region)"
                      @mouseenter="highlightedIndex = index"
                      :class="{ 
                        'highlighted': index === highlightedIndex,
                        'selected': region.id === selectedRegionId
                      }"
                      class="autocomplete-item"
                    >
                      {{ region.title }}
                    </li>
                  </ul>
                </div>
                
                <!-- No results message -->
                <div 
                  v-if="showDropdown && !isLoading && filteredRegions.length === 0 && searchQuery" 
                  class="autocomplete-no-results"
                >
                  Nincs találat a(z) "{{ searchQuery }}" kifejezésre
                </div>
              </div>
            </div>
            
            <!-- 2. Város -->
            <div class="form-group mb-3">
              <label class="form-label">2. Válassz várost:</label>
              <select v-model="selectedCity" @change="loadSchools" :disabled="!selectedRegionId" class="form-control">
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
                <i class='bx bx-check'></i> Mentés és tovább
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
              <i class='bx bx-check'></i> Mentés és tovább
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
                <label class="form-label">Régió *</label>
                <div class="autocomplete-wrapper">
                  <input
                    type="text"
                    v-model="adminRegionSearch"
                    @input="onAdminRegionInput"
                    @focus="adminShowDropdown = true"
                    @blur="onAdminRegionBlur"
                    @keydown.down="highlightAdminNext"
                    @keydown.up="highlightAdminPrev"
                    @keydown.enter="selectAdminHighlighted"
                    placeholder="Kezdj el gépelni a régió nevéből..."
                    class="form-control"
                    :class="{ 'is-loading': adminIsLoading }"
                  />
                  
                  <!-- Loading indicator -->
                  <div v-if="adminIsLoading" class="autocomplete-loading">
                    <span class="spinner-border spinner-border-sm"></span>
                  </div>
                  
                  <!-- Clear button -->
                  <button 
                    v-if="adminRegionSearch" 
                    @click="clearAdminRegionSearch"
                    class="autocomplete-clear"
                    type="button"
                  >
                    &times;
                  </button>
                  
                  <!-- Dropdown with suggestions -->
                  <div 
                    v-if="adminShowDropdown && adminFilteredRegions.length > 0" 
                    class="autocomplete-dropdown"
                  >
                    <ul class="autocomplete-list">
                      <li 
                        v-for="(region, index) in adminFilteredRegions" 
                        :key="region.id"
                        @click="selectAdminRegion(region)"
                        @mouseenter="adminHighlightedIndex = index"
                        :class="{ 
                          'highlighted': index === adminHighlightedIndex,
                          'selected': region.id === newSchool.regionId
                        }"
                        class="autocomplete-item"
                      >
                        {{ region.title }}
                      </li>
                    </ul>
                  </div>
                  
                  <!-- No results message -->
                  <div 
                    v-if="adminShowDropdown && !adminIsLoading && adminFilteredRegions.length === 0 && adminRegionSearch" 
                    class="autocomplete-no-results"
                  >
                    Nincs találat a(z) "{{ adminRegionSearch }}" kifejezésre
                  </div>
                </div>
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
                <i class='bx bx-check'></i> Mentés és tovább
              </button>
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

        <!-- Ha már beállította a profilját, de még a MainPage-en van -->
        <div v-if="profileConfigured && isLoggedIn" class="profile-ready-section">
          <div class="profile-ready-content">
            <i class='bx bx-check-circle'></i>
            <h3>Profilja sikeresen beállítva!</h3>
            <p>Kattintson az alábbi gombra az események megtekintéséhez.</p>
            <button class="btn btn-primary mt-3" @click="goToEvents">
              <i class='bx bx-calendar'></i> Események megtekintése
            </button>
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
      
      selectedRole: '',
      selectedCounty: '',
      selectedCity: '',
      selectedSchool: '',
      selectedClass: '',
      
      teacherSchools: [],
      isClassTeacher: 'no',
      classTeacherClass: '',
      
      newSchool: {
        name: '',
        county: '',
        city: '',
        district: '',
        grades: 12,
        classesPerGrade: 3,
        type: 'gimnazium'
      },
      
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
      ]
    }
  },
  
  computed: {
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
    // Ellenőrizzük a bejelentkezést
    checkLoginStatus() {
      const savedUser = localStorage.getItem('esemenyter_user');
      
      if (!savedUser) {
        this.isLoggedIn = false;
        this.profileConfigured = false;
        return;
      }
      
      try {
        const userData = JSON.parse(savedUser);
        
        if (userData.isLoggedIn === true) {
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
          this.profileConfigured = !!userData.role;
          
        } else {
          this.isLoggedIn = false;
          this.profileConfigured = false;
          localStorage.removeItem('esemenyter_user');
        }
        
      } catch (error) {
        localStorage.removeItem('esemenyter_user');
        this.isLoggedIn = false;
        this.profileConfigured = false;
      }
    },
    
    // Navigáció az eseményekhez
    goToEvents() {
      this.$router.push('/events-list');
    },
    
    // Navigáció
    goToLogin() {
      this.$router.push('/login');
    },
    
    goToRegister() {
      this.$router.push('/register');
    },
    
    // Diák beállítás
    setupStudent() {
      this.selectedRole = 'student';
    },
    
    saveStudentSetup() {
      const selectedSchool = this.schools.find(s => s.id == this.selectedSchool);
      const selectedClass = this.classes.find(c => c.id == this.selectedClass);
      
      this.user.role = 'student';
      this.user.school = selectedSchool ? selectedSchool.name : 'Ismeretlen';
      this.user.class = selectedClass ? `${selectedClass.grade}.${selectedClass.name}` : '';
      this.user.schoolId = this.selectedSchool;
      this.user.classId = this.selectedClass;
      
      const savedUser = JSON.parse(localStorage.getItem('esemenyter_user') || '{}');
      savedUser.role = 'student';
      savedUser.school = this.user.school;
      savedUser.class = this.user.class;
      savedUser.schoolId = this.user.schoolId;
      savedUser.classId = this.user.classId;
      savedUser.isLoggedIn = true;
      localStorage.setItem('esemenyter_user', JSON.stringify(savedUser));
      
      this.profileConfigured = true;
      this.goToEvents();
    },
    
    // Tanár beállítás
    setupTeacher() {
      this.selectedRole = 'teacher';
    },
    
    toggleTeacherSchool(schoolId) {
      const index = this.teacherSchools.indexOf(schoolId);
      if (index === -1) {
        this.teacherSchools.push(schoolId);
      } else {
        this.teacherSchools.splice(index, 1);
      }
    },
    
    saveTeacherSetup() {
      const schoolNames = this.schools
        .filter(s => this.teacherSchools.includes(s.id))
        .map(s => s.name)
        .join(', ');
      
      this.user.role = 'teacher';
      this.user.school = schoolNames;
      this.user.schoolId = this.teacherSchools[0];
      
      if (this.isClassTeacher === 'yes' && this.classTeacherClass) {
        const selectedClass = this.classes.find(c => c.id == this.classTeacherClass);
        this.user.class = selectedClass ? `${selectedClass.grade}.${selectedClass.name}` : '';
        this.user.classId = this.classTeacherClass;
      }
      
      const savedUser = JSON.parse(localStorage.getItem('esemenyter_user') || '{}');
      savedUser.role = 'teacher';
      savedUser.school = this.user.school;
      savedUser.class = this.user.class;
      savedUser.schoolId = this.user.schoolId;
      savedUser.classId = this.user.classId;
      savedUser.isLoggedIn = true;
      localStorage.setItem('esemenyter_user', JSON.stringify(savedUser));
      
      this.profileConfigured = true;
      this.goToEvents();
    },
    
    // Admin beállítás
    setupAdmin() {
      this.selectedRole = 'admin';
    },
    
    saveAdminSetup() {
      const newSchoolId = this.schools.length + 1;
      this.schools.push({
        id: newSchoolId,
        name: this.newSchool.name,
        city_id: 1,
        county_id: this.newSchool.county
      });
      
      for (let grade = 1; grade <= this.newSchool.grades; grade++) {
        for (let i = 0; i < this.newSchool.classesPerGrade; i++) {
          const className = String.fromCharCode(65 + i);
          this.classes.push({
            id: this.classes.length + 1,
            name: className,
            grade: grade,
            school_id: newSchoolId
          });
        }
      }
      
      this.user.role = 'admin';
      this.user.school = this.newSchool.name;
      this.user.schoolId = newSchoolId;
      
      const savedUser = JSON.parse(localStorage.getItem('esemenyter_user') || '{}');
      savedUser.role = 'admin';
      savedUser.school = this.user.school;
      savedUser.schoolId = this.user.schoolId;
      savedUser.isLoggedIn = true;
      localStorage.setItem('esemenyter_user', JSON.stringify(savedUser));
      
      this.profileConfigured = true;
      this.goToEvents();
    },
    
    // Segéd függvények
    loadCities() {
      this.selectedCity = '';
      this.selectedSchool = '';
      this.selectedClass = '';
    },
    
    loadSchools() {
      this.selectedSchool = '';
      this.selectedClass = '';
    },
    
    loadClasses() {
      this.selectedClass = '';
    },
    
    // Kijelentkezés
    logout() {
      localStorage.removeItem('esemenyter_user');
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
      window.scrollTo(0, 0);
      
      if (this.$route.path !== '/') {
        this.$router.push('/');
      }
    }
  },

  created() {
    this.checkLoginStatus();
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
  padding: 20px 0;
  box-sizing: border-box;
    font-family: "Poppins", sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    width: 99vw;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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
  color: white;
}

.site-subtitle {
  margin: 0;
  opacity: 0.9;
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.8);
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

/* Profil kész üzenet */
.profile-ready-section {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 400px;
}

.profile-ready-content {
  text-align: center;
  padding: 3rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.profile-ready-content i {
  font-size: 60px;
  color: #28a745;
  margin-bottom: 20px;
}

.profile-ready-content h3 {
  color: #333;
  margin-bottom: 10px;
}

.profile-ready-content p {
  color: #666;
  margin-bottom: 20px;
}

/* Gomb stílusok finomítása */
.btn-primary {
  background: linear-gradient(135deg, #4f46e5 0%, #7c73ff 100%);
  border: none;
  padding: 0.75rem 1.5rem;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(79, 70, 229, 0.3);
}

.btn-outline-primary {
  border-color: #4f46e5;
  color: #4f46e5;
  font-weight: 600;
}

.btn-outline-primary:hover {
  background-color: #4f46e5;
  color: white;
}

.btn-outline-danger {
  border-color: #ef4444;
  color: #ef4444;
}

.btn-outline-danger:hover {
  background-color: #ef4444;
  color: white;
}

.btn-sm {
  padding: 0.375rem 0.75rem;
  font-size: 0.875rem;
}

.btn-lg {
  padding: 0.75rem 1.5rem;
  font-size: 1.25rem;
}

/* User section */
.user-section {
  display: flex;
  align-items: center;
  gap: 1rem;
  background: rgba(255, 255, 255, 0.1);
  padding: 0.75rem 1.25rem;
  border-radius: 8px;
  backdrop-filter: blur(10px);
}

.user-info {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.user-name {
  font-weight: 600;
  color: white;
  font-size: 0.95rem;
}

.user-role {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.8);
  background: rgba(255, 255, 255, 0.15);
  padding: 0.1rem 0.5rem;
  border-radius: 4px;
  margin-top: 0.2rem;
  text-transform: capitalize;
}

/* Form elemek */
.form-control {
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  padding: 0.75rem 1rem;
  transition: all 0.3s ease;
}

.form-control:focus {
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.form-label {
  font-weight: 600;
  color: #4a5568;
  margin-bottom: 0.5rem;
}

/* Reszponzív design */
@media (max-width: 768px) {
  .header-content {
    flex-direction: column;
    text-align: center;
    gap: 1rem;
  }
  
  .role-cards {
    grid-template-columns: 1fr;
  }
  
  .hero-section {
    padding: 2rem 1rem !important;
  }
  
  .cta-buttons {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }
  
  .cta-buttons .btn {
    width: 100%;
    margin: 0 !important;
  }
  
  .school-selection {
    grid-template-columns: 1fr;
  }
  
  .user-section {
    flex-direction: column;
    text-align: center;
  }
  
  .user-info {
    align-items: center;
  }
}

/* Animációk */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.role-card, .feature-card, .setup-section, .profile-ready-content {
  animation: fadeIn 0.5s ease-out;
}
</style>