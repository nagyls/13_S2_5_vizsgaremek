<template>
  <div class="dashboard">
    <header class="main-header">
      <div class="container">
        <div class="header-content">
          <div class="logo-section" @click="$router.push('/dashboard')">
            <div class="logo-icon">
              <i class='bx bx-calendar-heart'></i>
            </div>
            <div class="logo-text">
              <h1 class="site-title">EseményTér</h1>
              <p class="site-subtitle">Ahol minden esemény helyet kap</p>
            </div>
          </div>
          
          <div class="user-profile">
            <div class="user-avatar" @click="toggleUserMenu">
              <div class="avatar-circle">
                <span>{{ userInitials }}</span>
              </div>
              <div class="user-status">
                <div class="status-dot online"></div>
              </div>
            </div>
            
            <transition name="slide-fade">
              <div v-if="showUserMenu" class="user-menu">
                <div class="menu-header">
                  <div class="menu-user-info">
                    <h4>{{ user.name }}</h4>
                    <p class="user-email">{{ user.email }}</p>
                  </div>
                  <div class="role-badge" :class="user.role">
                    {{ roleDisplayName }}
                  </div>
                </div>
                <div class="menu-items">
                  <router-link to="/profile" class="menu-item">
                    <i class='bx bx-user'></i>
                    <span>Profilom</span>
                  </router-link>
                  <router-link to="/events-list" class="menu-item">
                    <i class='bx bx-calendar'></i>
                    <span>Események</span>
                  </router-link>
                  <div class="menu-divider"></div>
                  <button class="menu-item logout-btn" @click="logout">
                    <i class='bx bx-log-out'></i>
                    <span>Kijelentkezés</span>
                  </button>
                </div>
              </div>
            </transition>
          </div>
        </div>
      </div>
    </header>

    <main class="main-content">
      <div class="container">
        <!-- Szerepkör választás ha még nincs beállítva -->
        <div v-if="!profileConfigured" class="role-selection-section">
          <div class="section-header">
            <div class="header-icon">
              <i class='bx bx-cast'></i>
            </div>
            <h2>Válassz szerepkört</h2>
            <p class="section-subtitle">
              Az alábbi lehetőségek közül választhatod ki, hogyan szeretnél részt venni az EseményTérben
            </p>
          </div>
          
          <div class="role-cards-grid">
            <div class="role-card student" :class="{ 'selected': selectedRole === 'student' }" 
                 @click="selectedRole = 'student'">
              <div class="card-decoration">
                <div class="decoration-circle"></div>
                <div class="decoration-circle small"></div>
              </div>
              <div class="role-icon">
                <i class='bx bx-graduation'></i>
              </div>
              <h3>Diák</h3>
              <p class="role-description">
                Eseményekre jelentkezhetsz, részt vehetsz szavazásokon és hozzászólhatsz a közösségben.
              </p>
              <ul class="role-features">
                <li><i class='bx bx-check'></i> Eseményekre jelentkezés</li>
                <li><i class='bx bx-check'></i> Szavazások részvétel</li>
                <li><i class='bx bx-check'></i> Közösségi interakció</li>
              </ul>
              <button v-if="selectedRole === 'student'" class="card-action-btn" @click.stop="setupStudent">
                <span>Diákként folytatás</span>
                <i class='bx bx-chevron-right'></i>
              </button>
            </div>
            
            <div class="role-card teacher" :class="{ 'selected': selectedRole === 'teacher' }" 
                 @click="selectedRole = 'teacher'">
              <div class="card-decoration">
                <div class="decoration-circle"></div>
                <div class="decoration-circle small"></div>
              </div>
              <div class="role-icon">
                <i class='bx bx-chalkboard'></i>
              </div>
              <h3>Tanár</h3>
              <p class="role-description">
                Hozz létre eseményeket, indíts szavazásokat és koordináld az iskolád tevékenységeit.
              </p>
              <ul class="role-features">
                <li><i class='bx bx-check'></i> Események létrehozása</li>
                <li><i class='bx bx-check'></i> Szavazások kezelése</li>
                <li><i class='bx bx-check'></i> Iskolakoordináció</li>
              </ul>
              <button v-if="selectedRole === 'teacher'" class="card-action-btn" @click.stop="setupTeacher">
                <span>Tanárként folytatás</span>
                <i class='bx bx-chevron-right'></i>
              </button>
            </div>
            
            <div class="role-card admin" :class="{ 'selected': selectedRole === 'admin' }" 
                 @click="selectedRole = 'admin'">
              <div class="card-decoration">
                <div class="decoration-circle"></div>
                <div class="decoration-circle small"></div>
              </div>
              <div class="role-icon">
                <i class='bx bx-cog'></i>
              </div>
              <h3>Adminisztrátor</h3>
              <p class="role-description">
                Kezeld az iskolákat, felhasználókat és a rendszer teljes működését.
              </p>
              <ul class="role-features">
                <li><i class='bx bx-check'></i> Iskola regisztrálás</li>
                <li><i class='bx bx-check'></i> Felhasználókezelés</li>
                <li><i class='bx bx-check'></i> Rendszeradminisztráció</li>
              </ul>
              <button v-if="selectedRole === 'admin'" class="card-action-btn" @click.stop="setupAdmin">
                <span>Adminisztrátorként folytatás</span>
                <i class='bx bx-chevron-right'></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Diák beállítás -->
        <transition name="slide-up">
          <div v-if="selectedRole === 'student' && !profileConfigured" class="setup-wizard">
            <div class="wizard-header">
              <div class="wizard-progress">
                <div class="progress-bar">
                  <div class="progress-fill" :style="{ width: progressWidth }"></div>
                </div>
                <div class="step-indicators">
                  <div class="step" :class="{ 'active': currentStep >= 1 }">1</div>
                  <div class="step" :class="{ 'active': currentStep >= 2 }">2</div>
                  <div class="step" :class="{ 'active': currentStep >= 3 }">3</div>
                  <div class="step" :class="{ 'active': currentStep >= 4 }">4</div>
                  <div class="step" :class="{ 'active': currentStep >= 5 }">5</div>
                </div>
              </div>
              <h3>Diák profil beállítása</h3>
              <p>Néhány lépésben állítsd be profilodat a teljes funkcionalitás érdekében</p>
            </div>
            
            <div class="wizard-content">
              <!-- 1. lépés: Régió kiválasztása -->
              <div v-if="currentStep === 1" class="step-content">
                <div class="step-icon">
                  <i class='bx bx-map'></i>
                </div>
                <h4>Válassz régiót</h4>
                <p>Először válaszd ki a régiót, ahol az iskolád található</p>
                
                <div class="search-wrapper">
                  <i class='bx bx-search'></i>
                  <input 
                    type="text" 
                    v-model="searchQuery"
                    placeholder="Kezdj el gépelni a régió nevéből..."
                    class="search-input"
                  />
                </div>
                
                <div class="suggestions-grid">
                  <div 
                    v-for="region in filteredRegions" 
                    :key="region.id"
                    class="suggestion-card"
                    :class="{ 'selected': region.id === selectedRegionId }"
                    @click="selectRegion(region)"
                  >
                    <div class="suggestion-icon">
                      <i class='bx bx-current-location'></i>
                    </div>
                    <div class="suggestion-text">
                      <h5>{{ region.title }} </h5>
                      <p>{{ region.title === 'Budapest' ? 'főváros' : 'vármegye' }}</p>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- 2. lépés: Járás kiválasztása -->
              <div v-if="currentStep === 2" class="step-content">
                <div class="step-icon">
                  <i class='bx bx-map-alt'></i>
                </div>
                <h4>Válassz járást</h4>
                <p>Válaszd ki a járást, ahol a városod található</p>
                
                <div class="search-wrapper">
                  <i class='bx bx-search'></i>
                  <input 
                    type="text" 
                    v-model="jarasSearchQuery"
                    placeholder="Keresd a járásod..."
                    class="search-input"
                  />
                </div>
                
                <div class="suggestions-grid">
                  <div 
                    v-for="district in filteredDistricts" 
                    :key="district.id"
                    class="suggestion-card"
                    :class="{ 'selected': district.id === selectedDistrictId }"
                    @click="selectDistrict(district)"
                  >
                    <div class="suggestion-icon">
                      <i class='bx bx-map-pin'></i>
                    </div>
                    <div class="suggestion-text">
                      <h5>{{ district.title + "i" }}</h5>
                      <p>{{ district.cityCount || 'járás' }}</p>
                    </div>
                  </div>
                  <div v-if="filteredDistricts.length === 0" class="no-results">
                    <i class='bx bx-search-alt'></i>
                    <p>Nincs találat</p>
                  </div>
                </div>
              </div>
              
              <!-- 3. lépés: Város kiválasztása -->
              <div v-if="currentStep === 3" class="step-content">
                <div class="step-icon">
                  <i class='bx bx-building-house'></i>
                </div>
                <h4>Válassz várost</h4>
                <p>Válaszd ki a várost, ahol az iskolád található</p>
                
                <div class="search-wrapper">
                  <i class='bx bx-search'></i>
                  <input 
                    type="text" 
                    v-model="citySearchQuery"
                    placeholder="Keresd a városod..."
                    class="search-input"
                  />
                </div>
                
                <div class="suggestions-grid">
                  <div 
                    v-for="city in filteredCities" 
                    :key="city.id"
                    class="suggestion-card"
                    :class="{ 'selected': city.id === selectedCityId }"
                    @click="selectCity(city)"
                  >
                    <div class="suggestion-icon">
                      <i class='bx bx-city'></i>
                    </div>
                    <div class="suggestion-text">
                      <h5>{{ city.title }}</h5>
                    </div>
                  </div>
                  <div v-if="filteredCities.length === 0" class="no-results">
                    <i class='bx bx-search-alt'></i>
                    <p>Nincs találat</p>
                  </div>
                </div>
              </div>
              
              <!-- 4. lépés: Iskola kiválasztása -->
              <div v-if="currentStep === 4" class="step-content">
                <div class="step-icon">
                  <i class='bx bx-school'></i>
                </div>
                <h4>Válassz iskolát</h4>
                <p>Válaszd ki az iskoládat a listából</p>
                
                <div class="search-wrapper">
                  <i class='bx bx-search'></i>
                  <input 
                    type="text" 
                    v-model="schoolSearchQuery"
                    placeholder="Keresd az iskolád..."
                    class="search-input"
                  />
                </div>
                
                <div class="suggestions-grid">
                  <div 
                    v-for="school in filteredSchools" 
                    :key="school.id"
                    class="suggestion-card"
                    :class="{ 'selected': school.id === selectedSchoolId }"
                    @click="selectSchool(school)"
                  >
                    <div class="suggestion-icon">
                      <i class='bx bx-book'></i>
                    </div>
                    <div class="suggestion-text">
                      <h5>{{ school.title }}</h5>
                      <p>{{ school.type }}</p>
                    </div>
                  </div>
                  <div v-if="filteredSchools.length === 0" class="no-results">
                    <i class='bx bx-search-alt'></i>
                    <p>Nincs találat</p>
                  </div>
                </div>
              </div>
              
              <!-- 5. lépés: Megerősítés -->
              <div v-if="currentStep === 5" class="step-content">
                <div class="step-icon">
                  <i class='bx bx-check-circle'></i>
                </div>
                <h4>Profil beállítása</h4>
                <p>Ellenőrizd a megadott adatokat és fejezd be a profilod beállítását</p>
                
                <div class="confirmation-card">
                  <div class="confirmation-item">
                    <i class='bx bx-user'></i>
                    <div>
                      <h5>Név</h5>
                      <p>{{ user.name }}</p>
                    </div>
                  </div>
                  <div class="confirmation-item">
                    <i class='bx bx-map'></i>
                    <div>
                      <h5>Régió</h5>
                      <p>{{ selectedRegion?.title }}</p>
                    </div>
                  </div>
                  <div class="confirmation-item">
                    <i class='bx bx-map-alt'></i>
                    <div>
                      <h5>Járás</h5>
                      <p>{{ selectedDistrict?.title }}</p>
                    </div>
                  </div>
                  <div class="confirmation-item">
                    <i class='bx bx-city'></i>
                    <div>
                      <h5>Város</h5>
                      <p>{{ selectedCity?.title }}</p>
                    </div>
                  </div>
                  <div class="confirmation-item">
                    <i class='bx bx-school'></i>
                    <div>
                      <h5>Iskola</h5>
                      <p>{{ selectedSchool?.title }}</p>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="wizard-actions">
                <button class="btn-outline" @click="prevStep">
                  <i class='bx bx-arrow-back'></i>
                  {{ currentStep === 1 ? 'Vissza' : 'Előző lépés' }}
                </button>
                <button class="btn-primary" @click="nextStep" :disabled="!isStepValid">
                  {{ currentStep === 5 ? 'Profil mentése' : 'Következő lépés' }}
                  <i class='bx bx-chevron-right'></i>
                </button>
              </div>
            </div>
          </div>
        </transition>

        <!-- Tanár beállítás -->
        <transition name="slide-up">
          <div v-if="selectedRole === 'teacher' && !profileConfigured" class="setup-wizard">
            <div class="wizard-header">
              <div class="wizard-progress">
                <div class="progress-bar">
                  <div class="progress-fill" :style="{ width: teacherProgressWidth }"></div>
                </div>
                <div class="step-indicators">
                  <div class="step" :class="{ 'active': teacherCurrentStep >= 1 }">1</div>
                  <div class="step" :class="{ 'active': teacherCurrentStep >= 2 }">2</div>
                  <div class="step" :class="{ 'active': teacherCurrentStep >= 3 }">3</div>
                  <div class="step" :class="{ 'active': teacherCurrentStep >= 4 }">4</div>
                  <div class="step" :class="{ 'active': teacherCurrentStep >= 5 }">5</div>
                </div>
              </div>
              <h3>Tanár profil beállítása</h3>
              <p>Néhány lépésben állítsd be profilodat a teljes funkcionalitás érdekében</p>
            </div>
            
            <div class="wizard-content">
              <!-- 1. lépés: Régió kiválasztása -->
              <div v-if="teacherCurrentStep === 1" class="step-content">
                <div class="step-icon">
                  <i class='bx bx-map'></i>
                </div>
                <h4>Válassz régiót</h4>
                <p>Először válaszd ki a régiót, ahol az iskolád található</p>
                
                <div class="search-wrapper">
                  <i class='bx bx-search'></i>
                  <input 
                    type="text" 
                    v-model="teacherSearchQuery"
                    placeholder="Kezdj el gépelni a régió nevéből..."
                    class="search-input"
                  />
                </div>
                
                <div class="suggestions-grid">
                  <div 
                    v-for="region in filteredTeacherRegions" 
                    :key="region.id"
                    class="suggestion-card"
                    :class="{ 'selected': region.id === teacherSelectedRegionId }"
                    @click="teacherSelectRegion(region)"
                  >
                    <div class="suggestion-icon">
                      <i class='bx bx-current-location'></i>
                    </div>
                    <div class="suggestion-text">
                      <h5>{{ region.title }}</h5>
                      <p>{{ region.title === 'Budapest' ? 'főváros' : 'vármegye' }}</p>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- 2. lépés: Járás kiválasztása -->
              <div v-if="teacherCurrentStep === 2" class="step-content">
                <div class="step-icon">
                  <i class='bx bx-map-alt'></i>
                </div>
                <h4>Válassz járást</h4>
                <p>Válaszd ki a járást, ahol a városod található</p>
                
                <div class="search-wrapper">
                  <i class='bx bx-search'></i>
                  <input 
                    type="text" 
                    v-model="teacherjarasSearchQuery"
                    placeholder="Keresd a járásod..."
                    class="search-input"
                  />
                </div>
                
                <div class="suggestions-grid">
                  <div 
                    v-for="district in filteredTeacherDistricts" 
                    :key="district.id"
                    class="suggestion-card"
                    :class="{ 'selected': district.id === teacherSelectedDistrictId }"
                    @click="teacherSelectDistrict(district)"
                  >
                    <div class="suggestion-icon">
                      <i class='bx bx-map-pin'></i>
                    </div>
                    <div class="suggestion-text">
                      <h5>{{ district.title + "i"}}</h5>
                      <p>{{ district.cityCount || 'járás'}}</p>
                    </div>
                  </div>
                  <div v-if="filteredTeacherDistricts.length === 0" class="no-results">
                    <i class='bx bx-search-alt'></i>
                    <p>Nincs találat</p>
                  </div>
                </div>
              </div>
              
              <!-- 3. lépés: Város kiválasztása -->
              <div v-if="teacherCurrentStep === 3" class="step-content">
                <div class="step-icon">
                  <i class='bx bx-building-house'></i>
                </div>
                <h4>Válassz várost</h4>
                <p>Válaszd ki a várost, ahol az iskolád található</p>
                
                <div class="search-wrapper">
                  <i class='bx bx-search'></i>
                  <input 
                    type="text" 
                    v-model="teacherCitySearchQuery"
                    placeholder="Keresd a városod..."
                    class="search-input"
                  />
                </div>
                
                <div class="suggestions-grid">
                  <div 
                    v-for="city in filteredTeacherCities" 
                    :key="city.id"
                    class="suggestion-card"
                    :class="{ 'selected': city.id === teacherSelectedCityId }"
                    @click="teacherSelectCity(city)"
                  >
                    <div class="suggestion-icon">
                      <i class='bx bx-city'></i>
                    </div>
                    <div class="suggestion-text">
                      <h5>{{ city.title }}</h5>
                    </div>
                  </div>
                  <div v-if="filteredTeacherCities.length === 0" class="no-results">
                    <i class='bx bx-search-alt'></i>
                    <p>Nincs találat</p>
                  </div>
                </div>
              </div>
              
              <!-- 4. lépés: Iskola kiválasztása -->
              <div v-if="teacherCurrentStep === 4" class="step-content">
                <div class="step-icon">
                  <i class='bx bx-school'></i>
                </div>
                <h4>Válassz iskolát</h4>
                <p>Válaszd ki az iskoládat a listából</p>
                
                <div class="search-wrapper">
                  <i class='bx bx-search'></i>
                  <input 
                    type="text" 
                    v-model="teacherSchoolSearchQuery"
                    placeholder="Keresd az iskolád..."
                    class="search-input"
                  />
                </div>
                
                <div class="suggestions-grid">
                  <div 
                    v-for="school in filteredTeacherSchools" 
                    :key="school.id"
                    class="suggestion-card"
                    :class="{ 'selected': school.id === teacherSelectedSchoolId }"
                    @click="teacherSelectSchool(school)"
                  >
                    <div class="suggestion-icon">
                      <i class='bx bx-book'></i>
                    </div>
                    <div class="suggestion-text">
                      <h5>{{ school.title }}</h5>
                      <p>{{ school.type }}</p>
                    </div>
                  </div>
                  <div v-if="filteredTeacherSchools.length === 0" class="no-results">
                    <i class='bx bx-search-alt'></i>
                    <p>Nincs találat</p>
                  </div>
                </div>
              </div>
              
              <!-- 5. lépés: Megerősítés -->
              <div v-if="teacherCurrentStep === 5" class="step-content">
                <div class="step-icon">
                  <i class='bx bx-check-circle'></i>
                </div>
                <h4>Profil beállítása</h4>
                <p>Ellenőrizd a megadott adatokat és fejezd be a profilod beállítását</p>
                
                <div class="confirmation-card">
                  <div class="confirmation-item">
                    <i class='bx bx-user'></i>
                    <div>
                      <h5>Név</h5>
                      <p>{{ user.name }}</p>
                    </div>
                  </div>
                  <div class="confirmation-item">
                    <i class='bx bx-school'></i>
                    <div>
                      <h5>Iskola</h5>
                      <p>{{ teacherSelectedSchool?.title }}</p>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="wizard-actions">
                <button class="btn-outline" @click="teacherPrevStep">
                  <i class='bx bx-arrow-back'></i>
                  {{ teacherCurrentStep === 1 ? 'Vissza' : 'Előző lépés' }}
                </button>
                <button class="btn-primary" @click="teacherNextStep" :disabled="!isTeacherStepValid">
                  {{ teacherCurrentStep === 6 ? 'Profil mentése' : 'Következő lépés' }}
                  <i class='bx bx-chevron-right'></i>
                </button>
              </div>
            </div>
          </div>
        </transition>

        <!-- Admin beállítás -->
        <transition name="slide-up">
          <div v-if="selectedRole === 'admin' && !profileConfigured" class="setup-wizard">
            <div class="wizard-header">
              <div class="wizard-progress">
                <div class="progress-bar">
                  <div class="progress-fill" :style="{ width: adminProgressWidth }"></div>
                </div>
                <div class="step-indicators">
                  <div class="step" :class="{ 'active': adminCurrentStep >= 1 }">1</div>
                  <div class="step" :class="{ 'active': adminCurrentStep >= 2 }">2</div>
                  <div class="step" :class="{ 'active': adminCurrentStep >= 3 }">3</div>
                  <div class="step" :class="{ 'active': adminCurrentStep >= 4 }">4</div>
                  <div class="step" :class="{ 'active': adminCurrentStep >= 5 }">5</div>
                </div>
              </div>
              <h3>Iskola regisztrálása</h3>
              <p>Regisztrálj egy új iskolát a rendszerbe</p>
            </div>
            
            <div class="wizard-content">
              <!-- 1. lépés: Régió kiválasztása -->
              <div v-if="adminCurrentStep === 1" class="step-content">
                <div class="step-icon">
                  <i class='bx bx-map'></i>
                </div>
                <h4>Válassz régiót</h4>
                <p>Először válaszd ki a régiót, ahol az iskola található</p>
                
                <div class="search-wrapper">
                  <i class='bx bx-search'></i>
                  <input 
                    type="text" 
                    v-model="adminSearchQuery"
                    placeholder="Kezdj el gépelni a régió nevéből..."
                    class="search-input"
                  />
                </div>
                
                <div class="suggestions-grid">
                  <div 
                    v-for="region in filteredAdminRegions" 
                    :key="region.id"
                    class="suggestion-card"
                    :class="{ 'selected': region.id === adminSelectedRegionId }"
                    @click="adminSelectRegion(region)"
                  >
                    <div class="suggestion-icon">
                      <i class='bx bx-current-location'></i>
                    </div>
                    <div class="suggestion-text">
                      <h5>{{ region.title }}</h5>
                      <p>{{ region.title === 'Budapest' ? 'főváros' : 'vármegye' }}</p>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- 2. lépés: Járás kiválasztása -->
              <div v-if="adminCurrentStep === 2" class="step-content">
                <div class="step-icon">
                  <i class='bx bx-map-alt'></i>
                </div>
                <h4>Válassz járást</h4>
                <p>Válaszd ki a járást, ahol a város található</p>
                
                <div class="search-wrapper">
                  <i class='bx bx-search'></i>
                  <input 
                    type="text" 
                    v-model="adminjarasSearchQuery"
                    placeholder="Keresd a járást..."
                    class="search-input"
                  />
                </div>
                
                <div class="suggestions-grid">
                  <div 
                    v-for="district in filteredAdminDistricts" 
                    :key="district.id"
                    class="suggestion-card"
                    :class="{ 'selected': district.id === adminSelectedDistrictId }"
                    @click="adminSelectDistrict(district)"
                  >
                    <div class="suggestion-icon">
                      <i class='bx bx-map-pin'></i>
                    </div>
                    <div class="suggestion-text">
                      <h5>{{ district.title + "i" }}</h5>
                      <p>{{ district.cityCount || 'járás' }}</p>
                    </div>
                  </div>
                  <div v-if="filteredAdminDistricts.length === 0" class="no-results">
                    <i class='bx bx-search-alt'></i>
                    <p>Nincs találat</p>
                  </div>
                </div>
              </div>
              
              <!-- 3. lépés: Város kiválasztása -->
              <div v-if="adminCurrentStep === 3" class="step-content">
                <div class="step-icon">
                  <i class='bx bx-building-house'></i>
                </div>
                <h4>Válassz várost</h4>
                <p>Válaszd ki a várost, ahol az iskola található</p>
                
                <div class="search-wrapper">
                  <i class='bx bx-search'></i>
                  <input 
                    type="text" 
                    v-model="adminCitySearchQuery"
                    placeholder="Keresd a várost..."
                    class="search-input"
                  />
                </div>
                
                <div class="suggestions-grid">
                  <div 
                    v-for="city in filteredAdminCities" 
                    :key="city.id"
                    class="suggestion-card"
                    :class="{ 'selected': city.id === adminSelectedCityId }"
                    @click="adminSelectCity(city)"
                  >
                    <div class="suggestion-icon">
                      <i class='bx bx-city'></i>
                    </div>
                    <div class="suggestion-text">
                      <h5>{{ city.title }}</h5>
                    </div>
                  </div>
                  <div v-if="filteredAdminCities.length === 0" class="no-results">
                    <i class='bx bx-search-alt'></i>
                    <p>Nincs találat</p>
                  </div>
                </div>
              </div>
              
              <!-- 4. lépés: Iskola adatainak megadása -->
              <div v-if="adminCurrentStep === 4" class="step-content">
                <div class="step-icon">
                  <i class='bx bx-school'></i>
                </div>
                <h4>Iskola adatai</h4>
                <p>Add meg az iskola részletes adatait</p>
                
                <div class="school-form">
                  <div class="form-group">
                    <label class="form-label">
                      <span>Iskola neve *</span>
                    </label>
                    <input 
                      type="text" 
                      v-model="schoolForm.name"
                      placeholder="Pl.: Kossuth Lajos Általános Iskola"
                      class="form-input"
                      :class="{ 'error': schoolFormErrors.name }"
                    />
                    <span v-if="schoolFormErrors.name" class="error-message">{{ schoolFormErrors.name }}</span>
                  </div>
                  
                  <div class="form-group">
                    <label class="form-label">
                      <span>Rövid leírás *</span>
                    </label>
                    <textarea 
                      v-model="schoolForm.description"
                      placeholder="Rövid leírás az iskoláról..."
                      class="form-textarea"
                      :class="{ 'error': schoolFormErrors.description }"
                      rows="4"
                    ></textarea>
                    <span v-if="schoolFormErrors.description" class="error-message">{{ schoolFormErrors.description }}</span>
                  </div>
                  
                  <div class="form-group">
                    <label class="form-label">
                      <span>Iskola címe *</span>
                    </label>
                    <input 
                      type="text" 
                      v-model="schoolForm.address"
                      placeholder="Pl.: Fő út 1."
                      class="form-input"
                      :class="{ 'error': schoolFormErrors.address }"
                    />
                    <span v-if="schoolFormErrors.address" class="error-message">{{ schoolFormErrors.address }}</span>
                  </div>
                  
                  <div class="form-row">
                    <div class="form-group">
                      <label class="form-label">
                        <span>Telefonszám</span>
                      </label>
                      <input 
                        type="tel" 
                        v-model="schoolForm.phone"
                        placeholder="Pl.: +36 1 234 5678"
                        class="form-input"
                      />
                    </div>
                    
                    <div class="form-group">
                      <label class="form-label">
                        <span>Email cím</span>
                      </label>
                      <input 
                        type="email" 
                        v-model="schoolForm.email"
                        placeholder="Pl.: info@iskola.edu.hu"
                        class="form-input"
                      />
                    </div>
                  </div>
                  
                  <div class="form-group">
                    <label class="form-label">
                      <span>Weboldal</span>
                    </label>
                    <input 
                      type="url" 
                      v-model="schoolForm.website"
                      placeholder="Pl.: https://www.iskola.edu.hu"
                      class="form-input"
                    />
                  </div>
                </div>
              </div>
              
              <!-- 5. lépés: Megerősítés -->
              <div v-if="adminCurrentStep === 5" class="step-content">
                <div class="step-icon">
                  <i class='bx bx-check-circle'></i>
                </div>
                <h4>Megerősítés</h4>
                <p>Ellenőrizd az iskola adatait és fejezd be a regisztrációt</p>
                
                <div class="confirmation-card">
                  <div class="confirmation-item">
                    <i class='bx bx-map'></i>
                    <div>
                      <h5>Régió</h5>
                      <p>{{ adminSelectedRegion?.title }}</p>
                    </div>
                  </div>
                  <div class="confirmation-item">
                    <i class='bx bx-map-alt'></i>
                    <div>
                      <h5>Járás</h5>
                      <p>{{ adminSelectedDistrict?.title || adminSelectedDistrict?.title }}</p>
                    </div>
                  </div>
                  <div class="confirmation-item">
                    <i class='bx bx-city'></i>
                    <div>
                      <h5>Város</h5>
                      <p>{{ adminSelectedCity?.title || adminSelectedCity?.title || adminNewCityName }}</p>
                    </div>
                  </div>
                  <div class="confirmation-item">
                    <i class='bx bx-school'></i>
                    <div>
                      <h5>Iskola neve</h5>
                      <p>{{ schoolForm.name }}</p>
                    </div>
                  </div>
                  <div class="confirmation-item">
                    <i class='bx bx-note'></i>
                    <div>
                      <h5>Leírás</h5>
                      <p class="description-text">{{ schoolForm.description }}</p>
                    </div>
                  </div>
                  <div class="confirmation-item">
                    <i class='bx bx-map-pin'></i>
                    <div>
                      <h5>Cím</h5>
                      <p>{{ schoolForm.address }}</p>
                    </div>
                  </div>
                  <div v-if="schoolForm.director" class="confirmation-item">
                    <i class='bx bx-user'></i>
                    <div>
                      <h5>Igazgató</h5>
                      <p>{{ schoolForm.director }}</p>
                    </div>
                  </div>
                  <div v-if="schoolForm.phone" class="confirmation-item">
                    <i class='bx bx-phone'></i>
                    <div>
                      <h5>Telefonszám</h5>
                      <p>{{ schoolForm.phone }}</p>
                    </div>
                  </div>
                </div>
                
                <div class="admin-agreement">
                  <label class="form-label">
                    <input 
                      type="checkbox" 
                      v-model="adminAgreement"
                      class="checkbox-input"
                    />
                    <span class="checkbox-label">
                      Elfogadom, hogy az iskola adatait hozzáadom a rendszerhez és vállalom a felelősséget a helyes adatok megadásáért.
                    </span>
                  </label>
                </div>
              </div>
              
              <div class="wizard-actions">
                <button class="btn-outline" @click="adminPrevStep">
                  <i class='bx bx-arrow-back'></i>
                  {{ adminCurrentStep === 1 ? 'Vissza' : 'Előző lépés' }}
                </button>
                <button class="btn-primary" @click="adminNextStep" :disabled="!isAdminStepValid">
                  {{ adminCurrentStep === 5 ? 'Iskola regisztrálása' : 'Következő lépés' }}
                  <i class='bx bx-chevron-right'></i>
                </button>
              </div>
            </div>
          </div>
        </transition>
      </div>
    </main>

    <button v-if="showScrollTop" class="fab" @click="scrollToTop">
      <i class='bx bx-chevron-up'></i>
    </button>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'Dashboard',
  
  data() {
    return {
      user: {
        id: null,
        name: 'Kovács János',
        email: 'janos.kovacs@example.com',
        role: '',
        region: '',
        district: '',
        city: '',
        school: '',
        schoolId: null,
        isClassTeacher: false,
        mainClass: '',
        teachingClasses: [],
        specialTeaching: {}
      },
      profileConfigured: false,
      showUserMenu: false,
      showScrollTop: false,
      
      selectedRole: '',
      
      // Diák beállítás adatai
      currentStep: 1,
      searchQuery: '',
      jarasSearchQuery: '',
      citySearchQuery: '',
      schoolSearchQuery: '',
      selectedRegionId: null,
      selectedDistrictId: null,
      selectedCityId: null,
      selectedSchoolId: null,
      
      // Tanár beállítás adatai
      teacherCurrentStep: 1,
      teacherSearchQuery: '',
      teacherjarasSearchQuery: '',
      teacherCitySearchQuery: '',
      teacherSchoolSearchQuery: '',
      teacherSelectedRegionId: null,
      teacherSelectedDistrictId: null,
      teacherSelectedCityId: null,
      teacherSelectedSchoolId: null,
      isClassTeacher: false,
      selectedMainClass: '',
      selectedClasses: [],
      classSearchQuery: '',
      availableGrades: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13],
      schoolClasses: [
        { id: 101, name: '9.A', grade: 9, studentCount: 28 },
        { id: 102, name: '9.B', grade: 9, studentCount: 25 },
        { id: 103, name: '9.C', grade: 9, studentCount: 30 },
        { id: 104, name: '10.A', grade: 10, studentCount: 27 },
        { id: 105, name: '10.B', grade: 10, studentCount: 26 },
        { id: 106, name: '11.A', grade: 11, studentCount: 24 },
        { id: 107, name: '11.B', grade: 11, studentCount: 29 },
        { id: 108, name: '12.A', grade: 12, studentCount: 22 },
        { id: 109, name: '12.B', grade: 12, studentCount: 25 },
        { id: 110, name: '5.A', grade: 5, studentCount: 20 },
        { id: 111, name: '5.B', grade: 5, studentCount: 22 },
        { id: 112, name: '6.A', grade: 6, studentCount: 24 },
        { id: 113, name: '6.B', grade: 6, studentCount: 23 },
        { id: 114, name: '7.A', grade: 7, studentCount: 26 },
        { id: 115, name: '7.B', grade: 7, studentCount: 25 },
        { id: 116, name: '8.A', grade: 8, studentCount: 27 },
        { id: 117, name: '8.B', grade: 8, studentCount: 28 },
      ],
      specialTeaching: {
        specialNeeds: false,
        talentManagement: false,
        extraCurricular: false
      },
      
      // Admin beállítás adatai
      adminCurrentStep: 1,
      adminSearchQuery: '',
      adminjarasSearchQuery: '',
      adminCitySearchQuery: '',
      adminSelectedRegionId: null,
      adminSelectedDistrictId: null,
      adminSelectedCityId: null,
      adminNewCityName: '',
      adminNewCityZip: '',
      adminNewCityError: '',
      showAddCityModal: false,
      schoolForm: {
        name: '',
        description: '',
        type: '',
        foundedYear: '',
        address: '',
        phone: '',
        email: '',
        website: '',
        director: '',
        hasDormitory: false
      },
      schoolFormErrors: {},
      adminAgreement: false,
      
      // Közös adatlisták
      regions: [],
      
      // Diák adatai
      districts: [],
      cities: [],
      schools: [],
      
      // Tanár adatai
      teacherDistricts: [],
      teacherCities: [],
      teacherSchools: [],
      
      // Admin adatai
      adminDistricts: [],
      adminCities: []
    }
  },
  
  computed: {
    userInitials() {
      return this.user.name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
    },
    
    roleDisplayName() {
      const roles = {
        'student': 'Diák',
        'teacher': 'Tanár',
        'admin': 'Adminisztrátor'
      };
      return roles[this.user.role] || this.user.role;
    },
    
    // Diák kiválasztott elemek
    selectedRegion() {
      return this.regions.find(r => r.id === this.selectedRegionId);
    },
    
    selectedDistrict() {
      return this.districts.find(d => d.id === this.selectedDistrictId);
    },
    
    selectedCity() {
      return this.cities.find(c => c.id === this.selectedCityId);
    },
    
    selectedSchool() {
      return this.schools.find(s => s.id === this.selectedSchoolId);
    },
    
    // Tanár kiválasztott elemek
    teacherSelectedRegion() {
      return this.regions.find(r => r.id === this.teacherSelectedRegionId);
    },
    
    teacherSelectedDistrict() {
      return this.teacherDistricts.find(d => d.id === this.teacherSelectedDistrictId);
    },
    
    teacherSelectedCity() {
      return this.teacherCities.find(c => c.id === this.teacherSelectedCityId);
    },
    
    teacherSelectedSchool() {
      return this.teacherSchools.find(s => s.id === this.teacherSelectedSchoolId);
    },
    
    // Admin kiválasztott elemek
    adminSelectedRegion() {
      return this.regions.find(r => r.id === this.adminSelectedRegionId);
    },
    
    adminSelectedDistrict() {
      return this.adminDistricts.find(d => d.id === this.adminSelectedDistrictId);
    },
    
    adminSelectedCity() {
      return this.adminCities.find(c => c.id === this.adminSelectedCityId);
    },
    
    // Osztályok szűrt listája
    filteredClasses() {
      if (!this.classSearchQuery) return this.schoolClasses;
      const query = this.classSearchQuery.toLowerCase();
      return this.schoolClasses.filter(classItem =>
        classItem.name.toLowerCase().includes(query) ||
        classItem.grade.toString().includes(query)
      );
    },
    
    // Diák szűrt listák
    filteredRegions() {
      if (!this.searchQuery) return this.regions;
      return this.regions.filter(region =>
        region.title.toLowerCase().includes(this.searchQuery.toLowerCase())
      );
    },
    
    filteredDistricts() {
      if (!this.jarasSearchQuery) return this.districts;
      return this.districts.filter(district =>
         (district.title).toLowerCase().includes(this.jarasSearchQuery.toLowerCase())
      );
    },
     
    filteredCities() {
      if (!this.citySearchQuery) return this.cities;
      return this.cities.filter(city =>
        (city.title).toLowerCase().includes(this.citySearchQuery.toLowerCase())
      );
    },
    
    filteredSchools() {
      if (!this.schoolSearchQuery) return this.schools;
      const q = this.schoolSearchQuery.toLowerCase();
      return this.schools.filter(school =>
        (school.title).toLowerCase().includes(q)
      );
    },
    
    // Tanár szűrt listák
    filteredTeacherRegions() {
      if (!this.teacherSearchQuery) return this.regions;
      return this.regions.filter(region =>
        region.title.toLowerCase().includes(this.teacherSearchQuery.toLowerCase())
      );
    },
    
    filteredTeacherDistricts() {
      if (!this.teacherjarasSearchQuery) return this.teacherDistricts;
      return this.teacherDistricts.filter(district =>
        district.title.toLowerCase().includes(this.teacherjarasSearchQuery.toLowerCase())
      );
    },
    
    filteredTeacherCities() {
      if (!this.teacherCitySearchQuery) return this.teacherCities;
      return this.teacherCities.filter(city =>
        city.title.toLowerCase().includes(this.teacherCitySearchQuery.toLowerCase())
      );
    },
    
    filteredTeacherSchools() {
      if (!this.teacherSchoolSearchQuery) return this.teacherSchools;
      return this.teacherSchools.filter(school =>
        school.title.toLowerCase().includes(this.teacherSchoolSearchQuery.toLowerCase()) ||
        school.type.toLowerCase().includes(this.teacherSchoolSearchQuery.toLowerCase())
      );
    },
    
    // Admin szűrt listák
    filteredAdminRegions() {
      if (!this.adminSearchQuery) return this.regions;
      return this.regions.filter(region =>
        region.title.toLowerCase().includes(this.adminSearchQuery.toLowerCase())
      );
    },
    
    filteredAdminDistricts() {
      if (!this.adminjarasSearchQuery) return this.adminDistricts;
      return this.adminDistricts.filter(district =>
        (district.title).toLowerCase().includes(this.adminjarasSearchQuery.toLowerCase())
      );
    },
    
    filteredAdminCities() {
      if (!this.adminCitySearchQuery) return this.adminCities;
      return this.adminCities.filter(city =>
        (city.title).toLowerCase().includes(this.adminCitySearchQuery.toLowerCase())
      );
    },
    
    // Progress barok
    progressWidth() {
      return `${(this.currentStep / 5) * 100}%`;
    },
    
    teacherProgressWidth() {
      return `${(this.teacherCurrentStep / 6) * 100}%`;
    },
    
    adminProgressWidth() {
      return `${(this.adminCurrentStep / 5) * 100}%`;
    },
    
    // Validációs ellenőrzések
    isStepValid() {
      switch (this.currentStep) {
        case 1: return !!this.selectedRegionId;
        case 2: return !!this.selectedDistrictId;
        case 3: return !!this.selectedCityId;
        case 4: return !!this.selectedSchoolId;
        case 5: return true;
        default: return false;
      }
    },
    
    isTeacherStepValid() {
      switch (this.teacherCurrentStep) {
        case 1: return !!this.teacherSelectedRegionId;
        case 2: return !!this.teacherSelectedDistrictId;
        case 3: return !!this.teacherSelectedCityId;
        case 4: return !!this.teacherSelectedSchoolId;
        case 5: return this.selectedClasses.length > 0;
        case 6: return true;
        default: return false;
      }
    },
    
    isAdminStepValid() {
      switch (this.adminCurrentStep) {
        case 1: return !!this.adminSelectedRegionId;
        case 2: return !!this.adminSelectedDistrictId;
        case 3: return !!this.adminSelectedCityId || this.adminNewCityName;
        case 4: return this.validateSchoolForm();
        case 5: return this.adminAgreement;
        default: return false;
      }
    }
  },
  
  methods: {
    checkLoginStatus() {
      const savedUser = localStorage.getItem('esemenyter_user');
      if (savedUser) {
        const userData = JSON.parse(savedUser);
        if (userData.isLoggedIn) {
          this.user = { ...this.user, ...userData };
          this.profileConfigured = !!userData.role;
        } else {
          this.$router.push('/');
        }
      } else {
        this.$router.push('/');
      }
    },
    
    toggleUserMenu() {
      this.showUserMenu = !this.showUserMenu;
    },
    
    // Diák beállítás
    setupStudent() {
      this.selectedRole = 'student';
      this.currentStep = 1;
      this.resetStudentSetup();
    },
    
    // Tanár beállítás
    setupTeacher() {
      this.selectedRole = 'teacher';
      this.teacherCurrentStep = 1;
      this.resetTeacherSetup();
    },
    
    // Admin beállítás
    setupAdmin() {
      this.selectedRole = 'admin';
      this.adminCurrentStep = 1;
      this.resetAdminSetup();
    },
    
    // Diák lépésenkénti navigáció
    nextStep() {
      if (this.currentStep === 1) {
        if (!this.selectedRegionId) {
          alert('Kérjük válassz egy régiót!');
          return;
        }
        this.loadDistrictsForSelectedRegion();
        this.currentStep = 2;
      }
      else if (this.currentStep === 2) {
        if (!this.selectedDistrictId) {
          alert('Kérjük válassz egy járást!');
          return;
        }
        this.loadCitiesForSelectedDistrict();
        this.currentStep = 3;
      }
      else if (this.currentStep === 3) {
        if (!this.selectedCityId) {
          alert('Kérjük válassz egy várost!');
          return;
        }
        this.loadSchoolsForSelectedCity();
        this.currentStep = 4;
      }
      else if (this.currentStep === 4) {
        if (!this.selectedSchoolId) {
          alert('Kérjük válassz egy iskolát!');
          return;
        }
        this.currentStep = 5;
      }
      else if (this.currentStep === 5) {
        this.completeStudentProfileSetup();
      }
    },
    
    prevStep() {
      if (this.currentStep > 1) {
        this.currentStep--;
      } else {
        this.selectedRole = '';
        this.currentStep = 1;
        this.resetStudentSetup();
      }
    },
    
    // Tanár lépésenkénti navigáció
    teacherNextStep() {
      if (this.teacherCurrentStep === 1) {
        if (!this.teacherSelectedRegionId) {
          alert('Kérjük válassz egy régiót!');
          return;
        }
        this.loadTeacherDistrictsForSelectedRegion();
        this.teacherCurrentStep = 2;
      }
      else if (this.teacherCurrentStep === 2) {
        if (!this.teacherSelectedDistrictId) {
          alert('Kérjük válassz egy járást!');
          return;
        }
        this.loadTeacherCitiesForSelectedDistrict();
        this.teacherCurrentStep = 3;
      }
      else if (this.teacherCurrentStep === 3) {
        if (!this.teacherSelectedCityId) {
          alert('Kérjük válassz egy várost!');
          return;
        }
        this.loadTeacherSchoolsForSelectedCity();
        this.teacherCurrentStep = 4;
      }
      else if (this.teacherCurrentStep === 4) {
        if (!this.teacherSelectedSchoolId) {
          alert('Kérjük válassz egy iskolát!');
          return;
        }
        this.teacherCurrentStep = 5;
      }
      else if (this.teacherCurrentStep === 5) {
        if (this.selectedClasses.length === 0) {
          alert('Kérjük válassz legalább egy osztályt!');
          return;
        }
        this.teacherCurrentStep = 6;
      }
      else if (this.teacherCurrentStep === 6) {
        this.completeTeacherProfileSetup();
      }
    },
    
    teacherPrevStep() {
      if (this.teacherCurrentStep > 1) {
        this.teacherCurrentStep--;
      } else {
        this.selectedRole = '';
        this.teacherCurrentStep = 1;
        this.resetTeacherSetup();
      }
    },
    
    // Admin lépésenkénti navigáció
    adminNextStep() {
      if (this.adminCurrentStep === 1) {
        if (!this.adminSelectedRegionId) {
          alert('Kérjük válassz egy régiót!');
          return;
        }
        this.loadAdminDistrictsForSelectedRegion();
        this.adminCurrentStep = 2;
      }
      else if (this.adminCurrentStep === 2) {
        if (!this.adminSelectedDistrictId) {
          alert('Kérjük válassz egy járást!');
          return;
        }
        this.loadAdminCitiesForSelectedDistrict();
        this.adminCurrentStep = 3;
      }
      else if (this.adminCurrentStep === 3) {
        if (!this.adminSelectedCityId && !this.adminNewCityName) {
          alert('Kérjük válassz egy várost vagy adj meg egy új várost!');
          return;
        }
        this.adminCurrentStep = 4;
      }
      else if (this.adminCurrentStep === 4) {
        if (!this.validateSchoolForm()) {
          return;
        }
        this.adminCurrentStep = 5;
      }
      else if (this.adminCurrentStep === 5) {
        this.completeAdminProfileSetup();
      }
    },
    
    adminPrevStep() {
      if (this.adminCurrentStep > 1) {
        this.adminCurrentStep--;
      } else {
        this.selectedRole = '';
        this.adminCurrentStep = 1;
        this.resetAdminSetup();
      }
    },
    
    // Iskola form validálása
    validateSchoolForm() {
      this.schoolFormErrors = {};
      let isValid = true;
      
      if (!this.schoolForm.name.trim()) {
        this.schoolFormErrors.name = 'Az iskola neve kötelező';
        isValid = false;
      }
      
      if (!this.schoolForm.description.trim()) {
        this.schoolFormErrors.description = 'A leírás kötelező';
        isValid = false;
      }
      
      if (!this.schoolForm.address.trim()) {
        this.schoolFormErrors.address = 'Az iskola címe kötelező';
        isValid = false;
      }
      
      return isValid;
    },
    
    // Osztály kiválasztása
    toggleClassSelection(classId) {
      const index = this.selectedClasses.indexOf(classId);
      if (index === -1) {
        this.selectedClasses.push(classId);
      } else {
        this.selectedClasses.splice(index, 1);
      }
    },
    
    // Osztály eltávolítása
    removeClass(classId) {
      const index = this.selectedClasses.indexOf(classId);
      if (index !== -1) {
        this.selectedClasses.splice(index, 1);
      }
    },
    
    // Osztály nevének lekérdezése
    getClassName(classId) {
      const classItem = this.schoolClasses.find(c => c.id === classId);
      return classItem ? classItem.name : '';
    },
    
    // Reset metódusok
    resetStudentSetup() {
      this.selectedRegionId = null;
      this.selectedDistrictId = null;
      this.selectedCityId = null;
      this.selectedSchoolId = null;
      this.districts = [];
      this.cities = [];
      this.schools = [];
      this.searchQuery = '';
      this.jarasSearchQuery = '';
      this.citySearchQuery = '';
      this.schoolSearchQuery = '';
    },
    
    resetTeacherSetup() {
      this.teacherSelectedRegionId = null;
      this.teacherSelectedDistrictId = null;
      this.teacherSelectedCityId = null;
      this.teacherSelectedSchoolId = null;
      this.isClassTeacher = false;
      this.selectedMainClass = '';
      this.selectedClasses = [];
      this.classSearchQuery = '';
      this.specialTeaching = {
        specialNeeds: false,
        talentManagement: false,
        extraCurricular: false
      };
      this.teacherDistricts = [];
      this.teacherCities = [];
      this.teacherSchools = [];
      this.teacherSearchQuery = '';
      this.teacherjarasSearchQuery = '';
      this.teacherCitySearchQuery = '';
      this.teacherSchoolSearchQuery = '';
    },
    
    resetAdminSetup() {
      this.adminSelectedRegionId = null;
      this.adminSelectedDistrictId = null;
      this.adminSelectedCityId = null;
      this.adminNewCityName = '';
      this.adminNewCityZip = '';
      this.adminNewCityError = '';
      this.showAddCityModal = false;
      this.schoolForm = {
        title: '',
        description: '',
        website: '',
        email: '',
        phone: '',
        address: '',
      };
      this.schoolFormErrors = {};
      this.adminAgreement = false;
      this.adminDistricts = [];
      this.adminCities = [];
      this.adminSearchQuery = '';
      this.adminjarasSearchQuery = '';
      this.adminCitySearchQuery = '';
    },
    
    // Diák adatbetöltők
    loadDistrictsForSelectedRegion() {
      axios.get('http://127.0.0.1:8000/api/innerregions/all', {
        params: { region_id: this.selectedRegionId }
      })
      .then(res => {
        this.districts = res.data.data || [];
        this.jarasSearchQuery = '';
        console.log('Járások betöltve:', this.districts);
      })
      .catch(err => {
        console.error('Járások lekérésének hibája:', err);
        this.districts = [];
      });
    },
    
    loadCitiesForSelectedDistrict() {
      axios.get('http://127.0.0.1:8000/api/settlements/all', {
        params: { inner_region_id: this.selectedDistrictId }
      })
      .then(res => {
        this.cities = res.data.data || res.data || [];
        this.citySearchQuery = '';
        console.log('Települések betöltve:', this.cities);
      })
      .catch(err => {
        console.error('Települések lekérésének hibája:', err);
        this.cities = [];
      });
    },
    
    loadSchoolsForSelectedCity() {
      axios.get('http://127.0.0.1:8000/api/establishments', {
        params: {
          settlement_id: this.selectedCityId
        }
      })
      .then(res => {
        this.schools = res.data.data || [];
        this.schoolSearchQuery = '';
      })
      .catch(err => {
        console.error('Iskolák lekérésének hibája:', err);
        this.schools = [];
      });
    },

    // Tanár adatbetöltők
    loadTeacherDistrictsForSelectedRegion() {
      axios.get('http://127.0.0.1:8000/api/innerregions/all', {
        params: { region_id: this.teacherSelectedRegionId }
      })
      .then(res => {
        this.teacherDistricts = res.data.data || [];
        this.teacherjarasSearchQuery = '';
        console.log('Járások betöltve:', this.teacherDistricts);
      })
      .catch(err => {
        console.error('Járások lekérésének hibája:', err);
        this.teacherDistricts = [];
      });
    },

    loadTeacherCitiesForSelectedDistrict() {
      axios.get('http://127.0.0.1:8000/api/settlements/all', {
        params: {
          inner_region_id: this.teacherSelectedDistrictId
        }
      })
      .then(res => {
        this.teacherCities = res.data.data || [];
        this.teacherCitySearchQuery = '';
      })
      .catch(() => {
        this.teacherCities = [];
      });
    },

    
    loadTeacherSchoolsForSelectedCity() {
      axios.get('http://127.0.0.1:8000/api/establishments', {
        params: {
          settlement_id: this.teacherSelectedCityId
        }
      })
      .then(res => {
        this.teacherSchools = res.data.data || [];
        this.teacherSchoolSearchQuery = '';
      })
      .catch(() => {
        this.teacherSchools = [];
      });
    },

    
    // Admin adatbetöltők
    loadAdminDistrictsForSelectedRegion() {
      axios.get('http://127.0.0.1:8000/api/innerregions/all', {
        params: { region_id: this.adminSelectedRegionId }
      })
      .then(res => {
        this.adminDistricts = res.data.data || [];
        this.adminjarasSearchQuery = '';
        console.log('Admin járások betöltve:', this.adminDistricts);
      })
      .catch(err => {
        console.error('Admin járások lekérésének hibája:', err);
        this.adminDistricts = [];
      });
    },
    
    loadAdminCitiesForSelectedDistrict() {
      axios.get('http://127.0.0.1:8000/api/settlements/all', {
        params: { inner_region_id: this.adminSelectedDistrictId }
      })
      .then(res => {
        this.adminCities = res.data.data || [];
        this.adminCitySearchQuery = '';
        console.log('Admin települések betöltve:', this.adminCities);
      })
      .catch(err => {
        console.error('Admin települések lekérésének hibája:', err);
        this.adminCities = [];
      });
    },
    
    // Kiválasztó metódusok
    selectRegion(region) {
      this.selectedRegionId = region.id;
    },
    
    selectDistrict(district) {
      this.selectedDistrictId = district.id;
    },
    
    selectCity(city) {
      this.selectedCityId = city.id;
    },
    
    selectSchool(school) {
      this.selectedSchoolId = school.id;
    },
    
    teacherSelectRegion(region) {
      this.teacherSelectedRegionId = region.id;
    },
    
    teacherSelectDistrict(district) {
      this.teacherSelectedDistrictId = district.id;
    },
    
    teacherSelectCity(city) {
      this.teacherSelectedCityId = city.id;
    },
    
    teacherSelectSchool(school) {
      this.teacherSelectedSchoolId = school.id;
    },
    
    adminSelectRegion(region) {
      this.adminSelectedRegionId = region.id;
    },
    
    adminSelectDistrict(district) {
      this.adminSelectedDistrictId = district.id;
    },
    
    adminSelectCity(city) {
      this.adminSelectedCityId = city.id;
      this.adminNewCityName = '';
    },
    
    // Profil befejező metódusok
    completeStudentProfileSetup() {
      this.profileConfigured = true;
      this.user.role = this.selectedRole;
      this.user.region = this.selectedRegion?.title || '';
      this.user.district = this.selectedDistrict?.title || '';
      this.user.city = this.selectedCity?.title || '';
      this.user.school = this.selectedSchool?.title || '';
      this.user.schoolId = this.selectedSchoolId;
      this.saveUserData();

      // Átirányítás a UserDashboard-ra
      this.$router.push('/pending-approval');
    },
    
    completeTeacherProfileSetup() {
      this.profileConfigured = true;
      this.user.role = this.selectedRole;
      this.user.region = this.teacherSelectedRegion?.title || '';
      this.user.district = this.teacherSelectedDistrict?.title || '';
      this.user.city = this.teacherSelectedCity?.title || '';
      this.user.school = this.teacherSelectedSchool?.title || '';
      this.user.schoolId = this.teacherSelectedSchoolId;
      this.user.isClassTeacher = this.isClassTeacher;
      this.user.mainClass = this.selectedMainClass;
      this.user.teachingClasses = this.selectedClasses.map(id => ({
        id,
        name: this.getClassName(id)
      }));
      this.user.specialTeaching = { ...this.specialTeaching };
      this.saveUserData();

      // Átirányítás a UserDashboard-ra
      this.$router.push('/pending-approval');
    },
    
    completeAdminProfileSetup() {
      const token = localStorage.getItem('esemenyter_token');
      
      if (!token) {
        alert('Nincs bejelentkezve. Kérjük jelentkezzen be újra.');
        return;
      }
      
      // Prepare establishment data for API
      const establishmentData = {
        title: this.schoolForm.name,
        description: this.schoolForm.description,
        settlement_id: this.adminSelectedCityId,
        website: this.schoolForm.website || null,
        email: this.schoolForm.email || null,
        phone: this.schoolForm.phone || null,
        address: this.schoolForm.address
      };
      
      // Submit to Laravel API
      axios.post('http://127.0.0.1:8000/api/establishment', establishmentData, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      })
      .then(response => {
        console.log('Intézmény sikeresen létrehozva:', response.data);

        if (this.adminSelectedCityId) {
          this.selectedCityId = this.adminSelectedCityId;
          this.loadSchoolsForSelectedCity();
        }
      
        this.profileConfigured = true;
        this.user.role = 'institution_manager';
        this.user.institution_id = response.data.data?.id || response.data?.id;
        this.user.region = this.adminSelectedRegion?.title || '';
        this.user.district = this.adminSelectedDistrict?.title || '';
        this.user.city = this.adminSelectedCity?.title || this.adminNewCityName;
        this.user.school = this.schoolForm.name;
        this.user.schoolDetails = { ...this.schoolForm };
      
        this.saveUserData();
        alert('Intézmény sikeresen regisztrálva!');

        this.$router.push('/institution-dashboard');
      })
      .catch(err => {
        console.error('Hiba az intézmény létrehozásakor:', err);
        if (err.response?.data?.errors) {
          const errors = err.response.data.errors;
          let errorMsg = 'Hibák:\n';
          Object.keys(errors).forEach(key => {
            errorMsg += `${key}: ${errors[key].join(', ')}\n`;
          });
          alert(errorMsg);
        } else {
          alert('Hiba történt az intézmény regisztrálása során. Kérjük próbálja újra.');
        }
      });
    },
    
    saveUserData() {
      const userData = {
        ...this.user,
        isLoggedIn: true
      };
      localStorage.setItem('esemenyter_user', JSON.stringify(userData));
    },
    
    goToEvents() {
      this.$router.push('/events-list');
    },
    goToProfile() {
      this.$router.push('/profile');
    },
    goToInstitutionDashboard() {
      this.$router.push('/institution-dashboard');
    },
    
    logout() {
      // API-hoz logout kérés (tokennel)
      axios.post('http://127.0.0.1:8000/api/logout')
        .then(() => {
          console.log('Backend-en törölve a token');
        })
        .catch(err => {
          console.error('Logout hiba:', err);
        })
        .finally(() => {
          // Frontend localStorage tisztítás
          localStorage.removeItem('esemenyter_user');
          localStorage.removeItem('esemenyter_token');
          
          this.showUserMenu = false;
          
          // Átirányítás kezdőoldalra
          this.$router.push('/');
        });
    },
    
    scrollToTop() {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    
    handleScroll() {
      this.showScrollTop = window.scrollY > 300;
    },
    
    // regio betöltés
    loadRegions() {
      axios.get('http://127.0.0.1:8000/api/regions/all')
        .then(res => {
          this.regions = res.data.data || [];
          console.log('Régiók betöltve:', this.regions);
        })
        .catch(err => {
          console.error('Régiók lekérésének hibája:', err);
          this.regions = [];
        });
    }
  },
  
  mounted() {
    this.checkLoginStatus();
    this.loadRegions();
    window.addEventListener('scroll', this.handleScroll);
    
    document.addEventListener('click', (e) => {
      if (!e.target.closest('.user-profile')) {
        this.showUserMenu = false;
      }
    });
  },
  
  beforeUnmount() {
    window.removeEventListener('scroll', this.handleScroll);
  }
}
</script>

<style scoped>
.dashboard {
  box-sizing: border-box;
  font-family: "Poppins", sans-serif;
  min-height: 100vh;
  width: 100vw;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

/* FEJLÉC */
.main-header {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  padding: 16px 0;
  position: sticky;
  top: 0;
  z-index: 1000;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.logo-section {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  transition: opacity 0.3s;
}

.logo-section:hover {
  opacity: 0.8;
}

.logo-icon {
  font-size: 32px;
  color: #4f46e5;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.logo-text h1 {
  margin: 0;
  font-size: 24px;
  font-weight: 700;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.site-subtitle {
  margin: 0;
  font-size: 14px;
  color: #64748b;
  font-weight: 500;
}

/* Felhasználó profil */
.user-profile {
  position: relative;
}

.user-avatar {
  cursor: pointer;
  position: relative;
}

.avatar-circle {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 16px;
  transition: transform 0.3s ease;
}

.user-avatar:hover .avatar-circle {
  transform: scale(1.05);
}

.user-status {
  position: absolute;
  bottom: 0;
  right: 0;
}

.status-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  border: 2px solid white;
}

.status-dot.online {
  background: #10b981;
}

/* Felhasználó menü */
.user-menu {
  position: absolute;
  top: calc(100% + 10px);
  right: 0;
  background: white;
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  width: 300px;
  overflow: hidden;
  z-index: 1000;
}

.menu-header {
  padding: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.menu-user-info h4 {
  margin: 0 0 5px 0;
  font-size: 18px;
}

.user-email {
  margin: 0;
  font-size: 14px;
  opacity: 0.9;
}

.role-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  margin-top: 10px;
}

.role-badge.student {
  background: rgba(16, 185, 129, 0.2);
  color: #10b981;
}

.role-badge.teacher {
  background: rgba(249, 115, 22, 0.2);
  color: #f97316;
}

.role-badge.admin {
  background: rgba(139, 92, 246, 0.2);
  color: #8b5cf6;
}

.menu-items {
  padding: 10px 0;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  padding: 12px 20px;
  background: none;
  border: none;
  text-align: left;
  color: #374151;
  font-size: 14px;
  cursor: pointer;
  transition: background 0.2s;
}

.menu-item:hover {
  background: #f3f4f6;
}

.menu-item i {
  font-size: 20px;
  color: #6b7280;
}

.menu-divider {
  height: 1px;
  background: #e5e7eb;
  margin: 10px 20px;
}

.logout-btn {
  color: #ef4444;
}

.logout-btn i {
  color: #ef4444;
}

/* Animációk */
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.3s ease;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

/* SZEREPKÖR VÁLASZTÁS */
.role-selection-section {
  padding: 80px 0;
}

.section-header {
  text-align: center;
  margin-bottom: 60px;
}

.header-icon {
  font-size: 48px;
  color: #4f46e5;
  margin-bottom: 20px;
}

.section-header h2 {
  font-size: 36px;
  font-weight: 700;
  margin-bottom: 16px;
  color: #111827;
}

.section-subtitle {
  font-size: 18px;
  color: #6b7280;
  max-width: 600px;
  margin: 0 auto;
}

.role-cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 30px;
}

.role-card {
  background: white;
  border-radius: 24px;
  padding: 40px 30px;
  position: relative;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.role-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.role-card.selected {
  border-color: #4f46e5;
  box-shadow: 0 20px 40px rgba(79, 70, 229, 0.1);
}

.card-decoration {
  position: absolute;
  top: -50px;
  right: -50px;
}

.decoration-circle {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  opacity: 0.1;
}

.decoration-circle.small {
  width: 60px;
  height: 60px;
  position: absolute;
  top: 30px;
  right: 30px;
}

.role-card.student .decoration-circle {
  background: #10b981;
}

.role-card.teacher .decoration-circle {
  background: #f97316;
}

.role-card.admin .decoration-circle {
  background: #8b5cf6;
}

.role-icon {
  font-size: 48px;
  margin-bottom: 24px;
}

.role-card.student .role-icon {
  color: #10b981;
}

.role-card.teacher .role-icon {
  color: #f97316;
}

.role-card.admin .role-icon {
  color: #8b5cf6;
}

.role-card h3 {
  font-size: 24px;
  font-weight: 600;
  margin-bottom: 16px;
  color: #111827;
}

.role-description {
  color: #6b7280;
  line-height: 1.6;
  margin-bottom: 24px;
}

.role-features {
  list-style: none;
  padding: 0;
  margin: 0 0 30px 0;
}

.role-features li {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
  color: #4b5563;
  font-size: 14px;
}

.role-features i {
  color: #10b981;
}

.card-action-btn {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  padding: 16px 24px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.card-action-btn:hover {
  transform: translateX(5px);
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
}

/* WIZARD STÍLUSOK */
.setup-wizard {
  background: white;
  border-radius: 32px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
  margin: 40px 0;
}

.wizard-header {
  padding: 40px 40px 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.wizard-progress {
  margin-bottom: 24px;
}

.progress-bar {
  height: 6px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 3px;
  overflow: hidden;
  margin-bottom: 10px;
}

.progress-fill {
  height: 100%;
  background: white;
  border-radius: 3px;
  transition: width 0.3s ease;
}

.step-indicators {
  display: flex;
  justify-content: space-between;
}

.step {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.3s ease;
}

.step.active {
  background: white;
  color: #4f46e5;
  box-shadow: 0 4px 12px rgba(255, 255, 255, 0.3);
}

.wizard-header h3 {
  font-size: 28px;
  margin-bottom: 8px;
}

.wizard-header p {
  opacity: 0.9;
  font-size: 16px;
}

.wizard-content {
  padding: 40px;
}

.step-content {
  text-align: center;
  max-width: 800px;
  margin: 0 auto;
}

.step-icon {
  font-size: 64px;
  color: #4f46e5;
  margin-bottom: 24px;
}

.step-content h4 {
  font-size: 24px;
  margin-bottom: 12px;
  color: #111827;
}

.step-content p {
  color: #6b7280;
  margin-bottom: 32px;
}

.search-wrapper {
  position: relative;
  max-width: 500px;
  margin: 0 auto 32px;
}

.search-wrapper i {
  position: absolute;
  left: 20px;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
  font-size: 20px;
}

.search-input {
  width: 100%;
  padding: 16px 16px 16px 52px;
  border: 2px solid #e5e7eb;
  border-radius: 50px;
  font-size: 16px;
  transition: all 0.3s ease;
}

.search-input:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.suggestions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 40px;
}

.suggestion-card {
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 16px;
  padding: 16px;
  cursor: pointer;
  transition: all 0.3s ease;

  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
  text-align: center;
}

.suggestion-card:hover {
  border-color: #4f46e5;
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.suggestion-card.selected {
  border-color: #4f46e5;
  background: #f8f9ff;
}

.suggestion-icon {
  margin-bottom: 2px;
  color: #4f46e5;
}

.suggestion-text {
  text-align: center;
  width: 100%;
}

.suggestion-text h5 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
  color: #111827;
}

.suggestion-text p {
  margin: 0;
  font-size: 14px;
  color: #6b7280;
}

.no-results {
  grid-column: 1 / -1;
  text-align: center;
  padding: 40px;
  color: #6b7280;
}

.no-results i {
  font-size: 48px;
  margin-bottom: 16px;
  color: #9ca3af;
}

.no-results p {
  font-size: 16px;
}

.confirmation-card {
  background: #f8f9ff;
  border-radius: 16px;
  padding: 24px;
  margin: 32px 0;
  border: 1px solid #e5e7eb;
}

.confirmation-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px 0;
  border-bottom: 1px solid #e5e7eb;
}

.confirmation-item:last-child {
  border-bottom: none;
}

.confirmation-item i {
  font-size: 24px;
  color: #4f46e5;
}

.confirmation-item h5 {
  margin: 0 0 4px 0;
  font-size: 14px;
  color: #6b7280;
  font-weight: 500;
}

.confirmation-item p {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
  color: #111827;
}

.description-text {
  font-size: 14px !important;
  color: #6b7280 !important;
  font-weight: normal !important;
  line-height: 1.5;
}

.wizard-actions {
  display: flex;
  justify-content: space-between;
  max-width: 400px;
  margin: 0 auto;
}

.btn-outline {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  background: transparent;
  border: 2px solid #4f46e5;
  color: #4f46e5;
  border-radius: 50px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-outline:hover {
  background: #4f46e5;
  color: white;
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none !important;
}

.btn-primary:disabled:hover {
  box-shadow: none;
}

/* TANÁR SPECIFIKUS STÍLUSOK */
.class-selection {
  max-width: 700px;
  margin: 0 auto;
}

.form-group {
  margin-bottom: 32px;
  text-align: left;
}

.form-label {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  font-weight: 600;
  color: #111827;
  margin-bottom: 24px;
  font-size: 18px;
}

.checkbox-input {
  width: 20px;
  height: 20px;
  cursor: pointer;
}

.checkbox-label {
  font-size: 16px;
}

.form-subtitle {
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 12px;
  color: #111827;
}

.form-description {
  color: #6b7280;
  margin-bottom: 20px;
  font-size: 14px;
}

.select-input {
  width: 200px;
  padding: 12px 16px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 16px;
  background: white;
  cursor: pointer;
}

.select-input:focus {
  outline: none;
  border-color: #4f46e5;
}

.classes-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 12px;
  margin: 20px 0;
}

.class-card {
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 16px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 12px;
  position: relative;
}

.class-card:hover {
  border-color: #4f46e5;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(79, 70, 229, 0.1);
}

.class-card.selected {
  border-color: #4f46e5;
  background: #f8f9ff;
}

.class-card.selected .class-check {
  display: block;
}

.class-icon {
  font-size: 24px;
  color: #4f46e5;
}

.class-text h5 {
  margin: 0 0 4px 0;
  font-size: 16px;
  font-weight: 600;
  color: #111827;
}

.class-text p {
  margin: 0;
  font-size: 14px;
  color: #6b7280;
}

.class-check {
  position: absolute;
  top: -8px;
  right: -8px;
  background: #10b981;
  color: white;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  display: none;
}

.selected-classes {
  margin-top: 24px;
  padding: 16px;
  background: #f8f9ff;
  border-radius: 12px;
  border: 1px dashed #4f46e5;
}

.selected-classes h5 {
  margin: 0 0 12px 0;
  font-size: 16px;
  color: #4f46e5;
  font-weight: 600;
}

.selected-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.class-tag {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  background: white;
  border: 1px solid #4f46e5;
  border-radius: 20px;
  font-size: 14px;
  color: #4f46e5;
}

.tag-remove {
  background: none;
  border: none;
  color: #4f46e5;
  cursor: pointer;
  padding: 0;
  font-size: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.tag-remove:hover {
  color: #ef4444;
}

.special-teaching-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 16px;
  margin-top: 16px;
}

.special-option {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px;
  background: #f8f9ff;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  cursor: pointer;
  transition: all 0.3s ease;
}

.special-option:hover {
  border-color: #4f46e5;
  background: white;
}

.classes-summary {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 8px;
}

.class-summary-tag {
  display: inline-block;
  padding: 4px 12px;
  background: #e0e7ff;
  color: #4f46e5;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 500;
}

.special-summary {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 8px;
}

.special-summary span {
  display: inline-block;
  padding: 4px 12px;
  background: #f0f9ff;
  color: #0369a1;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 500;
}

/* ADMIN SPECIFIKUS STÍLUSOK */
.school-form {
  max-width: 600px;
  margin: 0 auto;
  text-align: left;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 24px;
}

.form-label span {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #374151;
}

.form-input,
.form-textarea,
.form-select {
  width: 100%;
  padding: 12px 16px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 16px;
  transition: all 0.3s ease;
  font-family: "Poppins", sans-serif;
}

.form-input:focus,
.form-textarea:focus,
.form-select:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.form-input.error,
.form-textarea.error {
  border-color: #ef4444;
}

.error-message {
  display: block;
  margin-top: 8px;
  color: #ef4444;
  font-size: 14px;
}

.form-textarea {
  resize: vertical;
  min-height: 100px;
}

.form-select {
  cursor: pointer;
  background: white;
}

.btn-add-city {
  margin-top: 16px;
}

.admin-agreement {
  margin: 32px 0;
  padding: 20px;
  background: #f8f9ff;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
}

.admin-agreement .checkbox-label {
  font-size: 14px;
  line-height: 1.5;
}

/* FLOATING ACTION BUTTON */
.fab {
  position: fixed;
  bottom: 30px;
  right: 30px;
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
  transition: all 0.3s ease;
  z-index: 1000;
}

.fab:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 30px rgba(102, 126, 234, 0.4);
}

/* ANIMÁCIÓK */
.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.5s ease;
}

.slide-up-enter-from,
.slide-up-leave-to {
  opacity: 0;
  transform: translateY(30px);
}

/* RESZPONZÍV */
@media (max-width: 1024px) {
  .step {
    width: 32px;
    height: 32px;
    font-size: 12px;
  }
  
  .classes-grid {
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  }
  
  .form-row {
    grid-template-columns: 1fr;
    gap: 16px;
  }
}

@media (max-width: 768px) {
  .role-cards-grid {
    grid-template-columns: 1fr;
  }
  
  .user-menu {
    width: 280px;
    right: -20px;
  }
  
  .wizard-content {
    padding: 30px 20px;
  }
  
  .step-indicators {
    gap: 10px;
  }
  
  .step {
    width: 30px;
    height: 30px;
    font-size: 11px;
  }
  
  .confirmation-card {
    padding: 16px;
  }
  
  .confirmation-item {
    padding: 12px 0;
  }
  
  .classes-grid {
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  }
  
  .special-teaching-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .success-card {
    padding: 30px 20px;
  }
  
  .success-icon {
    font-size: 60px;
  }
  
  .success-card h3 {
    font-size: 24px;
  }
  
  .role-card {
    padding: 30px 20px;
  }
  
  .wizard-content {
    padding: 20px 15px;
  }
  
  .suggestions-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }
  
  .suggestion-card {
    padding: 16px;
  }
  
  .step-indicators {
    gap: 5px;
  }
  
  .step {
    width: 28px;
    height: 28px;
    font-size: 10px;
  }
  
  .step-icon {
    font-size: 48px;
  }
  
  .step-content h4 {
    font-size: 20px;
  }
  
  .wizard-actions {
    flex-direction: column;
    gap: 12px;
  }
  
  .wizard-actions button {
    width: 100%;
    justify-content: center;
  }
  
  .search-input {
    padding: 14px 14px 14px 48px;
    font-size: 14px;
  }
  
  .classes-grid {
    grid-template-columns: 1fr;
  }
  
  .class-card {
    padding: 12px;
  }
  
  .select-input {
    width: 100%;
  }
  
  .fab {
    width: 48px;
    height: 48px;
    font-size: 20px;
    bottom: 20px;
    right: 20px;
  }
}
</style>