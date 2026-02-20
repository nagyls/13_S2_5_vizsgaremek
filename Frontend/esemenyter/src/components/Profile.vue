<template>
  <div class="profile">
    <header class="main-header">
      <div class="container">
        <div class="header-content">
          <div class="logo-section" @click="goToDashboard">
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
                  <router-link to="/user-dashboard" class="menu-item">
                    <i class='bx bx-home'></i>
                    <span>Főoldal</span>
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
        <!-- Profil fejléc -->
        <div class="profile-header">
          <div class="profile-avatar-large">
            <span>{{ userInitials }}</span>
          </div>
          <div class="profile-title">
            <h1>{{ user.name }}</h1>
            <p class="profile-role" :class="user.role">
              <i :class="roleIcon"></i>
              {{ roleDisplayName }}
            </p>
          </div>
          <div class="profile-actions">
            <button v-if="!isEditing" class="btn-primary" @click="startEditing">
              <i class='bx bx-edit'></i>
              Profil szerkesztése
            </button>
            <button v-if="isEditing" class="btn-outline" @click="cancelEditing">
              <i class='bx bx-x'></i>
              Mégse
            </button>
          </div>
        </div>

        <!-- Profil tartalom -->
        <div class="profile-content">
          <!-- Szerkesztési mód -->
          <div v-if="isEditing" class="edit-section">
            <form @submit.prevent="saveProfile" class="edit-form">
              <!-- Alapadatok -->
              <div class="form-section">
                <h2><i class='bx bx-user-circle'></i> Alapadatok</h2>
                
                <div class="form-grid">
                  <div class="form-group">
                    <label for="name">Teljes név *</label>
                    <input 
                      type="text" 
                      id="name"
                      v-model="editForm.name"
                      class="form-control"
                      :class="{ 'error': errors.name }"
                      required
                    />
                    <span v-if="errors.name" class="error-message">{{ errors.name }}</span>
                  </div>

                  <div class="form-group">
                    <label for="email">Email cím *</label>
                    <input 
                      type="email" 
                      id="email"
                      v-model="editForm.email"
                      class="form-control"
                      :class="{ 'error': errors.email }"
                      required
                    />
                    <span v-if="errors.email" class="error-message">{{ errors.email }}</span>
                  </div>

                  <div class="form-group">
                    <label for="phone">Telefonszám</label>
                    <input 
                      type="tel" 
                      id="phone"
                      v-model="editForm.phone"
                      class="form-control"
                      placeholder="+36 30 123 4567"
                    />
                  </div>

                  <div class="form-group">
                    <label for="birthdate">Születési dátum</label>
                    <input 
                      type="date" 
                      id="birthdate"
                      v-model="editForm.birthdate"
                      class="form-control"
                    />
                  </div>
                </div>
              </div>

              <!-- Iskolai adatok (diákoknak) -->
              <div v-if="user.role === 'student'" class="form-section">
                <h2><i class='bx bx-school'></i> Iskolai adatok</h2>
                
                <div class="form-grid">
                  <div class="form-group">
                    <label for="region">Régió</label>
                    <input 
                      type="text" 
                      id="region"
                      v-model="editForm.region"
                      class="form-control"
                      readonly
                      disabled
                    />
                    <small class="form-text">A régió nem módosítható</small>
                  </div>

                  <div class="form-group">
                    <label for="district">Járás</label>
                    <input 
                      type="text" 
                      id="district"
                      v-model="editForm.district"
                      class="form-control"
                      readonly
                      disabled
                    />
                  </div>

                  <div class="form-group">
                    <label for="city">Város</label>
                    <input 
                      type="text" 
                      id="city"
                      v-model="editForm.city"
                      class="form-control"
                      readonly
                      disabled
                    />
                  </div>

                  <div class="form-group">
                    <label for="school">Iskola</label>
                    <input 
                      type="text" 
                      id="school"
                      v-model="editForm.school"
                      class="form-control"
                      readonly
                      disabled
                    />
                  </div>

                  <div class="form-group">
                    <label for="class">Osztály</label>
                    <select 
                      id="class"
                      v-model="editForm.classId"
                      class="form-control"
                    >
                      <option value="">Válassz osztályt...</option>
                      <option 
                        v-for="classItem in availableClasses" 
                        :key="classItem.id" 
                        :value="classItem.id"
                      >
                        {{ classItem.name }}
                      </option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Tanári adatok -->
              <div v-if="user.role === 'teacher'" class="form-section">
                <h2><i class='bx bx-chalkboard'></i> Tanári adatok</h2>
                
                <div class="form-grid">
                  <div class="form-group">
                    <label for="teacher-region">Régió</label>
                    <input 
                      type="text" 
                      id="teacher-region"
                      v-model="editForm.region"
                      class="form-control"
                      readonly
                      disabled
                    />
                  </div>

                  <div class="form-group">
                    <label for="teacher-district">Járás</label>
                    <input 
                      type="text" 
                      id="teacher-district"
                      v-model="editForm.district"
                      class="form-control"
                      readonly
                      disabled
                    />
                  </div>

                  <div class="form-group">
                    <label for="teacher-city">Város</label>
                    <input 
                      type="text" 
                      id="teacher-city"
                      v-model="editForm.city"
                      class="form-control"
                      readonly
                      disabled
                    />
                  </div>

                  <div class="form-group">
                    <label for="teacher-school">Iskola</label>
                    <input 
                      type="text" 
                      id="teacher-school"
                      v-model="editForm.school"
                      class="form-control"
                      readonly
                      disabled
                    />
                  </div>

                  <div class="form-group" v-if="user.isClassTeacher">
                    <label for="main-class">Osztályfőnöki osztály</label>
                    <input 
                      type="text" 
                      id="main-class"
                      v-model="editForm.mainClass"
                      class="form-control"
                      readonly
                      disabled
                    />
                  </div>

                  <div class="form-group">
                    <label>Tanított osztályok</label>
                    <div class="classes-tags">
                      <span 
                        v-for="classItem in editForm.teachingClasses" 
                        :key="classItem.id"
                        class="class-tag"
                      >
                        {{ classItem.name }}
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Speciális tanítási formák -->
                <div class="form-group">
                  <label class="checkbox-label">
                    <input 
                      type="checkbox" 
                      v-model="editForm.specialNeeds"
                    />
                    <span>Speciális nevelési igényű tanulók oktatása</span>
                  </label>
                </div>

                <div class="form-group">
                  <label class="checkbox-label">
                    <input 
                      type="checkbox" 
                      v-model="editForm.talentManagement"
                    />
                    <span>Tehetséggondozás, versenyfelkészítés</span>
                  </label>
                </div>

                <div class="form-group">
                  <label class="checkbox-label">
                    <input 
                      type="checkbox" 
                      v-model="editForm.extraCurricular"
                    />
                    <span>Szakkörök, fakultációk vezetése</span>
                  </label>
                </div>
              </div>

              <!-- Intézményvezetői adatok -->
              <div v-if="user.role === 'institution_manager'" class="form-section">
                <h2><i class='bx bx-building-house'></i> Intézmény adatok</h2>
                
                <div class="form-grid">
                  <div class="form-group">
                    <label for="inst-region">Régió</label>
                    <input 
                      type="text" 
                      id="inst-region"
                      v-model="editForm.region"
                      class="form-control"
                      readonly
                      disabled
                    />
                  </div>

                  <div class="form-group">
                    <label for="inst-district">Járás</label>
                    <input 
                      type="text" 
                      id="inst-district"
                      v-model="editForm.district"
                      class="form-control"
                      readonly
                      disabled
                    />
                  </div>

                  <div class="form-group">
                    <label for="inst-city">Város</label>
                    <input 
                      type="text" 
                      id="inst-city"
                      v-model="editForm.city"
                      class="form-control"
                      readonly
                      disabled
                    />
                  </div>

                  <div class="form-group">
                    <label for="inst-name">Intézmény neve</label>
                    <input 
                      type="text" 
                      id="inst-name"
                      v-model="editForm.school"
                      class="form-control"
                    />
                  </div>

                  <div class="form-group">
                    <label for="inst-address">Cím</label>
                    <input 
                      type="text" 
                      id="inst-address"
                      v-model="editForm.schoolAddress"
                      class="form-control"
                    />
                  </div>

                  <div class="form-group">
                    <label for="inst-phone">Telefonszám</label>
                    <input 
                      type="tel" 
                      id="inst-phone"
                      v-model="editForm.schoolPhone"
                      class="form-control"
                    />
                  </div>

                  <div class="form-group">
                    <label for="inst-email">Email</label>
                    <input 
                      type="email" 
                      id="inst-email"
                      v-model="editForm.schoolEmail"
                      class="form-control"
                    />
                  </div>

                  <div class="form-group">
                    <label for="inst-website">Weboldal</label>
                    <input 
                      type="url" 
                      id="inst-website"
                      v-model="editForm.schoolWebsite"
                      class="form-control"
                    />
                  </div>
                </div>
              </div>

              <!-- Jelszó módosítás -->
              <div class="form-section">
                <h2><i class='bx bx-lock-alt'></i> Jelszó módosítása</h2>
                
                <div class="form-grid">
                  <div class="form-group">
                    <label for="current-password">Jelenlegi jelszó</label>
                    <input 
                      type="password" 
                      id="current-password"
                      v-model="editForm.currentPassword"
                      class="form-control"
                    />
                  </div>

                  <div class="form-group">
                    <label for="new-password">Új jelszó</label>
                    <input 
                      type="password" 
                      id="new-password"
                      v-model="editForm.newPassword"
                      class="form-control"
                    />
                  </div>

                  <div class="form-group">
                    <label for="confirm-password">Új jelszó megerősítése</label>
                    <input 
                      type="password" 
                      id="confirm-password"
                      v-model="editForm.confirmPassword"
                      class="form-control"
                    />
                  </div>
                </div>
              </div>

              <!-- Mentés gombok -->
              <div class="form-actions">
                <button type="button" class="btn-outline" @click="cancelEditing">
                  <i class='bx bx-x'></i>
                  Mégse
                </button>
                <button type="submit" class="btn-primary" :disabled="isSaving">
                  <i class='bx bx-save'></i>
                  {{ isSaving ? 'Mentés...' : 'Változtatások mentése' }}
                </button>
              </div>
            </form>
          </div>

          <!-- Megtekintési mód -->
          <div v-else class="view-section">
            <div class="info-grid">
              <!-- Alapadatok -->
              <div class="info-card">
                <h3><i class='bx bx-user'></i> Alapadatok</h3>
                <div class="info-list">
                  <div class="info-item">
                    <span class="info-label">Teljes név:</span>
                    <span class="info-value">{{ user.name }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Email cím:</span>
                    <span class="info-value">{{ user.email }}</span>
                  </div>
                  <div class="info-item" v-if="user.phone">
                    <span class="info-label">Telefonszám:</span>
                    <span class="info-value">{{ user.phone }}</span>
                  </div>
                  <div class="info-item" v-if="user.birthdate">
                    <span class="info-label">Születési dátum:</span>
                    <span class="info-value">{{ formatDate(user.birthdate) }}</span>
                  </div>
                </div>
              </div>

              <!-- Iskolai adatok (diák) -->
              <div v-if="user.role === 'student'" class="info-card">
                <h3><i class='bx bx-school'></i> Iskolai adatok</h3>
                <div class="info-list">
                  <div class="info-item">
                    <span class="info-label">Régió:</span>
                    <span class="info-value">{{ user.region || 'Nincs megadva' }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Járás:</span>
                    <span class="info-value">{{ user.district + "i" || 'Nincs megadva' }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Város:</span>
                    <span class="info-value">{{ user.city || 'Nincs megadva' }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Iskola:</span>
                    <span class="info-value">{{ user.school || 'Nincs megadva' }}</span>
                  </div>
                  <div class="info-item" v-if="user.className">
                    <span class="info-label">Osztály:</span>
                    <span class="info-value">{{ user.className }}</span>
                  </div>
                </div>
              </div>

              <!-- Tanári adatok -->
              <div v-if="user.role === 'teacher'" class="info-card">
                <h3><i class='bx bx-chalkboard'></i> Tanári adatok</h3>
                <div class="info-list">
                  <div class="info-item">
                    <span class="info-label">Régió:</span>
                    <span class="info-value">{{ user.region || 'Nincs megadva' }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Járás:</span>
                    <span class="info-value">{{ user.district + "i" || 'Nincs megadva' }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Város:</span>
                    <span class="info-value">{{ user.city || 'Nincs megadva' }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Iskola:</span>
                    <span class="info-value">{{ user.school || 'Nincs megadva' }}</span>
                  </div>
                  <div v-if="user.isClassTeacher" class="info-item">
                    <span class="info-label">Osztályfőnöki osztály:</span>
                    <span class="info-value">{{ user.mainClass }}. osztály</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Tanított osztályok:</span>
                    <span class="info-value">
                      <span v-for="(classItem, index) in user.teachingClasses" :key="index">
                        {{ classItem.name }}{{ index < user.teachingClasses.length - 1 ? ', ' : '' }}
                      </span>
                    </span>
                  </div>
                </div>
              </div>

              <!-- Intézményvezetői adatok -->
              <div v-if="user.role === 'institution_manager'" class="info-card">
                <h3><i class='bx bx-building-house'></i> Intézmény adatok</h3>
                <div class="info-list">
                  <div class="info-item">
                    <span class="info-label">Régió:</span>
                    <span class="info-value">{{ user.region || 'Nincs megadva' }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Járás:</span>
                    <span class="info-value">{{ user.district + "i" || 'Nincs megadva' }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Város:</span>
                    <span class="info-value">{{ user.city || 'Nincs megadva' }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Intézmény neve:</span>
                    <span class="info-value">{{ user.school || 'Nincs megadva' }}</span>
                  </div>
                  <div class="info-item" v-if="user.schoolAddress">
                    <span class="info-label">Cím:</span>
                    <span class="info-value">{{ user.schoolAddress }}</span>
                  </div>
                  <div class="info-item" v-if="user.schoolPhone">
                    <span class="info-label">Telefonszám:</span>
                    <span class="info-value">{{ user.schoolPhone }}</span>
                  </div>
                  <div class="info-item" v-if="user.schoolEmail">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ user.schoolEmail }}</span>
                  </div>
                  <div class="info-item" v-if="user.schoolWebsite">
                    <span class="info-label">Weboldal:</span>
                    <span class="info-value">
                      <a :href="user.schoolWebsite" target="_blank">{{ user.schoolWebsite }}</a>
                    </span>
                  </div>
                </div>
              </div>

              <!-- Fiók információk -->
              <div class="info-card">
                <h3><i class='bx bx-info-circle'></i> Fiók információk</h3>
                <div class="info-list">
                  <div class="info-item">
                    <span class="info-label">Felhasználó azonosító:</span>
                    <span class="info-value">#{{ user.id }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Regisztráció dátuma:</span>
                    <span class="info-value">{{ formatDate(user.created_at) }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Utolsó módosítás:</span>
                    <span class="info-value">{{ formatDate(user.updated_at) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Értesítés -->
    <transition name="toast">
      <div v-if="showToast" class="toast-notification" :class="toastType">
        <i :class="toastIcon"></i>
        <span>{{ toastMessage }}</span>
      </div>
    </transition>

    <!-- Floating Action Button -->
    <button v-if="showScrollTop" class="fab" @click="scrollToTop">
      <i class='bx bx-chevron-up'></i>
    </button>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'Profile',
  
  data() {
    return {
      user: {
        id: null,
        name: '',
        email: '',
        phone: '',
        birthdate: '',
        role: '',
        region: '',
        district: '',
        city: '',
        school: '',
        schoolId: null,
        schoolAddress: '',
        schoolPhone: '',
        schoolEmail: '',
        schoolWebsite: '',
        className: '',
        classId: null,
        isClassTeacher: false,
        mainClass: '',
        teachingClasses: [],
        specialNeeds: false,
        talentManagement: false,
        extraCurricular: false,
        created_at: null,
        updated_at: null
      },
      showUserMenu: false,
      showScrollTop: false,
      showToast: false,
      toastMessage: '',
      toastType: 'success',
      
      isEditing: false,
      isSaving: false,
      editForm: {
        name: '',
        email: '',
        phone: '',
        birthdate: '',
        region: '',
        district: '',
        city: '',
        school: '',
        schoolId: null,
        schoolAddress: '',
        schoolPhone: '',
        schoolEmail: '',
        schoolWebsite: '',
        classId: null,
        mainClass: '',
        teachingClasses: [],
        specialNeeds: false,
        talentManagement: false,
        extraCurricular: false,
        currentPassword: '',
        newPassword: '',
        confirmPassword: ''
      },
      errors: {},
      availableClasses: []
    }
  },
  
  computed: {
    userInitials() {
      if (!this.user.name) return '??';
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
        'admin': 'Adminisztrátor',
        'institution_manager': 'Intézményvezető'
      };
      return roles[this.user.role] || this.user.role;
    },
    
    roleIcon() {
      const icons = {
        'student': 'bx bx-graduation',
        'teacher': 'bx bx-chalkboard',
        'admin': 'bx bx-cog',
        'institution_manager': 'bx bx-building-house'
      };
      return icons[this.user.role] || 'bx bx-user';
    },
    
    toastIcon() {
      return {
        success: 'bx bx-check-circle',
        error: 'bx bx-error-circle',
        warning: 'bx bx-error',
        info: 'bx bx-info-circle'
      }[this.toastType];
    }
  },
  
  methods: {
    loadUserData() {
      const savedUser = localStorage.getItem('esemenyter_user');
      if (savedUser) {
        const userData = JSON.parse(savedUser);
        if (userData.isLoggedIn) {
          this.user = { ...this.user, ...userData };
          this.loadUserFromBackend();
        } else {
          this.$router.push('/');
        }
      } else {
        this.$router.push('/');
      }
    },
    
    async loadUserFromBackend() {
      try {
        const token = localStorage.getItem('esemenyter_token');
        const response = await axios.get(`http://127.0.0.1:8000/api/users/${this.user.id}`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        
        const userData = response.data.data || response.data;
        this.user = { ...this.user, ...userData };
        
        // Ha diák, töltsük be az elérhető osztályokat
        if (this.user.role === 'student' && this.user.schoolId) {
          await this.loadAvailableClasses();
        }
        
      } catch (error) {
        console.error('Hiba a felhasználói adatok betöltésekor:', error);
        this.showNotification('Hiba történt az adatok betöltésekor', 'error');
      }
    },
    
    async loadAvailableClasses() {
      try {
        const token = localStorage.getItem('esemenyter_token');
        const response = await axios.get(`http://127.0.0.1:8000/api/establishments/${this.user.schoolId}/classes`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        
        this.availableClasses = response.data.data || [];
        
      } catch (error) {
        console.error('Hiba az osztályok betöltésekor:', error);
      }
    },
    
    startEditing() {
      this.editForm = {
        name: this.user.name || '',
        email: this.user.email || '',
        phone: this.user.phone || '',
        birthdate: this.user.birthdate || '',
        region: this.user.region || '',
        district: this.user.district ? this.user.district + "i" : '',
        city: this.user.city || '',
        school: this.user.school || '',
        schoolId: this.user.schoolId,
        schoolAddress: this.user.schoolAddress || '',
        schoolPhone: this.user.schoolPhone || '',
        schoolEmail: this.user.schoolEmail || '',
        schoolWebsite: this.user.schoolWebsite || '',
        classId: this.user.classId || null,
        mainClass: this.user.mainClass || '',
        teachingClasses: this.user.teachingClasses ? [...this.user.teachingClasses] : [],
        specialNeeds: this.user.specialNeeds || false,
        talentManagement: this.user.talentManagement || false,
        extraCurricular: this.user.extraCurricular || false,
        currentPassword: '',
        newPassword: '',
        confirmPassword: ''
      };
      this.errors = {};
      this.isEditing = true;
    },
    
    cancelEditing() {
      this.isEditing = false;
      this.editForm = {};
      this.errors = {};
    },
    
    async saveProfile() {
      this.errors = {};
      
      // Validáció
      if (!this.editForm.name?.trim()) {
        this.errors.name = 'A név megadása kötelező';
      }
      
      if (!this.editForm.email?.trim()) {
        this.errors.email = 'Az email cím megadása kötelező';
      } else if (!this.isValidEmail(this.editForm.email)) {
        this.errors.email = 'Érvénytelen email cím';
      }
      
      // Jelszó validáció
      if (this.editForm.newPassword || this.editForm.currentPassword || this.editForm.confirmPassword) {
        if (!this.editForm.currentPassword) {
          this.errors.currentPassword = 'A jelenlegi jelszó megadása kötelező';
        }
        if (!this.editForm.newPassword) {
          this.errors.newPassword = 'Az új jelszó megadása kötelező';
        } else if (this.editForm.newPassword.length < 6) {
          this.errors.newPassword = 'A jelszónak legalább 6 karakter hosszúnak kell lennie';
        }
        if (this.editForm.newPassword !== this.editForm.confirmPassword) {
          this.errors.confirmPassword = 'A jelszavak nem egyeznek';
        }
      }
      
      if (Object.keys(this.errors).length > 0) {
        return;
      }
      
      this.isSaving = true;
      
      try {
        const token = localStorage.getItem('esemenyter_token');
        
        // Alapadatok mentése
        const updateData = {
          name: this.editForm.name,
          email: this.editForm.email,
          phone: this.editForm.phone || null,
          birthdate: this.editForm.birthdate || null
        };
        
        // Ha intézményvezető, iskola adatok mentése
        if (this.user.role === 'institution_manager') {
          updateData.school_name = this.editForm.school;
          updateData.school_address = this.editForm.schoolAddress;
          updateData.school_phone = this.editForm.schoolPhone;
          updateData.school_email = this.editForm.schoolEmail;
          updateData.school_website = this.editForm.schoolWebsite;
        }
        
        // Ha diák, osztály mentése
        if (this.user.role === 'student' && this.editForm.classId) {
          updateData.class_id = this.editForm.classId;
        }
        
        // Ha tanár, speciális beállítások mentése
        if (this.user.role === 'teacher') {
          updateData.special_needs = this.editForm.specialNeeds;
          updateData.talent_management = this.editForm.talentManagement;
          updateData.extra_curricular = this.editForm.extraCurricular;
        }
        
        const response = await axios.put(`http://127.0.0.1:8000/api/users/${this.user.id}`, updateData, {
          headers: { Authorization: `Bearer ${token}` }
        });
        
        // Jelszó módosítás, ha szükséges
        if (this.editForm.newPassword && this.editForm.currentPassword) {
          await axios.post(`http://127.0.0.1:8000/api/users/${this.user.id}/change-password`, {
            current_password: this.editForm.currentPassword,
            new_password: this.editForm.newPassword,
            new_password_confirmation: this.editForm.confirmPassword
          }, {
            headers: { Authorization: `Bearer ${token}` }
          });
        }
        
        // Frissítsük a felhasználó adatokat
        const updatedUser = response.data.data || response.data;
        this.user = { ...this.user, ...updatedUser };
        
        // Frissítsük a localStorage-t
        const savedUser = JSON.parse(localStorage.getItem('esemenyter_user'));
        const updatedSavedUser = { ...savedUser, ...updatedUser };
        localStorage.setItem('esemenyter_user', JSON.stringify(updatedSavedUser));
        
        this.isEditing = false;
        this.showNotification('Profil sikeresen frissítve!', 'success');
        
      } catch (error) {
        console.error('Hiba a profil mentésekor:', error);
        
        if (error.response?.data?.errors) {
          this.errors = error.response.data.errors;
        } else {
          this.showNotification('Hiba történt a profil mentésekor', 'error');
        }
      } finally {
        this.isSaving = false;
      }
    },
    
    isValidEmail(email) {
      return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    },
    
    formatDate(date) {
      if (!date) return 'Ismeretlen';
      return new Date(date).toLocaleDateString('hu-HU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    },
    
    goToDashboard() {
      if (this.user.role === 'institution_manager' || this.user.role === 'admin') {
        this.$router.push('/institution-dashboard');
      } else if (this.user.role) {
        this.$router.push('/user-dashboard');
      } else {
        this.$router.push('/dashboard');
      }
    },
    
    toggleUserMenu() {
      this.showUserMenu = !this.showUserMenu;
    },
    
    showNotification(message, type = 'success') {
      this.toastMessage = message;
      this.toastType = type;
      this.showToast = true;
      
      setTimeout(() => {
        this.showToast = false;
      }, 3000);
    },
    
    scrollToTop() {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    
    handleScroll() {
      this.showScrollTop = window.scrollY > 300;
    },
    
    logout() {
      axios.post('http://127.0.0.1:8000/api/logout')
        .finally(() => {
          localStorage.removeItem('esemenyter_user');
          localStorage.removeItem('esemenyter_token');
          this.$router.push('/');
        });
    }
  },
  
  mounted() {
    this.loadUserData();
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
.profile {
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
  background: rgba(255, 255, 255, 0.2);
  color: white;
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
  text-decoration: none;
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

/* Fő tartalom */
.main-content {
  padding: 40px 0;
}

/* Profil fejléc */
.profile-header {
  background: white;
  border-radius: 24px;
  padding: 40px;
  margin-bottom: 30px;
  display: flex;
  align-items: center;
  gap: 30px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.profile-avatar-large {
  width: 100px;
  height: 100px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 40px;
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
}

.profile-title {
  flex: 1;
}

.profile-title h1 {
  font-size: 28px;
  margin-bottom: 10px;
  color: #111827;
}

.profile-role {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border-radius: 50px;
  font-size: 14px;
  font-weight: 600;
}

.profile-role.student {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
}

.profile-role.teacher {
  background: rgba(249, 115, 22, 0.1);
  color: #f97316;
}

.profile-role.admin,
.profile-role.institution_manager {
  background: rgba(139, 92, 246, 0.1);
  color: #8b5cf6;
}

.profile-role i {
  font-size: 20px;
}

.profile-actions {
  display: flex;
  gap: 16px;
}

/* Profil tartalom */
.profile-content {
  background: white;
  border-radius: 24px;
  padding: 40px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

/* Info grid (megtekintési mód) */
.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 30px;
}

.info-card {
  background: #f8f9ff;
  border-radius: 16px;
  padding: 24px;
  border: 1px solid #e5e7eb;
}

.info-card h3 {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 0 0 20px 0;
  font-size: 18px;
  color: #4f46e5;
}

.info-card h3 i {
  font-size: 24px;
}

.info-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.info-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  font-size: 14px;
}

.info-label {
  min-width: 120px;
  color: #6b7280;
  font-weight: 500;
}

.info-value {
  color: #111827;
  font-weight: 500;
  flex: 1;
}

.info-value a {
  color: #4f46e5;
  text-decoration: none;
}

.info-value a:hover {
  text-decoration: underline;
}

/* Szerkesztési mód */
.edit-section {
  max-width: 800px;
  margin: 0 auto;
}

.form-section {
  margin-bottom: 40px;
  padding-bottom: 30px;
  border-bottom: 1px solid #e5e7eb;
}

.form-section:last-child {
  border-bottom: none;
}

.form-section h2 {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 20px;
  margin-bottom: 24px;
  color: #4f46e5;
}

.form-section h2 i {
  font-size: 24px;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #374151;
  font-size: 14px;
}

.form-control {
  width: 100%;
  padding: 12px 16px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.3s ease;
  font-family: "Poppins", sans-serif;
}

.form-control:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.form-control.error {
  border-color: #ef4444;
}

.form-control:disabled,
.form-control[readonly] {
  background: #f3f4f6;
  cursor: not-allowed;
}

.error-message {
  display: block;
  margin-top: 5px;
  color: #ef4444;
  font-size: 12px;
}

.form-text {
  display: block;
  margin-top: 5px;
  color: #6b7280;
  font-size: 12px;
}

/* Checkbox */
.checkbox-label {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  font-size: 14px;
  color: #374151;
}

.checkbox-label input[type="checkbox"] {
  width: 18px;
  height: 18px;
  cursor: pointer;
}

/* Osztály tag-ek */
.classes-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 8px;
}

.class-tag {
  display: inline-block;
  padding: 6px 12px;
  background: #e0e7ff;
  color: #4f46e5;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 500;
}

/* Form actions */
.form-actions {
  display: flex;
  gap: 16px;
  justify-content: flex-end;
  margin-top: 40px;
}

/* Gombok */
.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 50px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 14px;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-outline {
  display: inline-flex;
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
  font-size: 14px;
}

.btn-outline:hover {
  background: #4f46e5;
  color: white;
}

/* Toast értesítések */
.toast-notification {
  position: fixed;
  bottom: 30px;
  right: 30px;
  padding: 16px 24px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
  display: flex;
  align-items: center;
  gap: 12px;
  z-index: 3000;
  min-width: 300px;
  border-left: 4px solid;
}

.toast-notification.success {
  border-left-color: #10b981;
}

.toast-notification.error {
  border-left-color: #ef4444;
}

.toast-notification.warning {
  border-left-color: #f59e0b;
}

.toast-notification.info {
  border-left-color: #3b82f6;
}

.toast-notification i {
  font-size: 24px;
}

.toast-notification.success i {
  color: #10b981;
}

.toast-notification.error i {
  color: #ef4444;
}

.toast-notification.warning i {
  color: #f59e0b;
}

.toast-notification.info i {
  color: #3b82f6;
}

.toast-notification span {
  font-size: 14px;
  color: #374151;
}

/* Toast animáció */
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateX(30px);
}

/* FAB gomb */
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

/* Reszponzív */
@media (max-width: 768px) {
  .profile-header {
    flex-direction: column;
    text-align: center;
    padding: 30px;
  }
  
  .profile-actions {
    width: 100%;
    flex-direction: column;
  }
  
  .profile-actions button {
    width: 100%;
    justify-content: center;
  }
  
  .info-grid {
    grid-template-columns: 1fr;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .user-menu {
    width: 280px;
    right: -20px;
  }
  
  .form-actions {
    flex-direction: column;
  }
  
  .form-actions button {
    width: 100%;
    justify-content: center;
  }
  
  .toast-notification {
    left: 20px;
    right: 20px;
    min-width: auto;
    bottom: 20px;
  }
}

@media (max-width: 480px) {
  .profile-header {
    padding: 20px;
  }
  
  .profile-avatar-large {
    width: 80px;
    height: 80px;
    font-size: 32px;
  }
  
  .profile-title h1 {
    font-size: 22px;
  }
  
  .profile-content {
    padding: 20px;
  }
  
  .info-item {
    flex-direction: column;
    gap: 5px;
  }
  
  .info-label {
    min-width: auto;
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