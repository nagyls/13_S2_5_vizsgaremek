<template>
  <div class="profile">
    <header class="main-header">
      <div class="container">
        <div class="header-content">
          <div class="logo-section" @click="goToDashboard">
            <div class="logo-icon">
              <img :src="logo2" alt="EseményTér logó" class="logo-image">
            </div>
            <div class="logo-text">
              <h1 class="site-title">EseményTér</h1>
              <p class="site-subtitle">Ahol minden esemény helyet kap</p>
            </div>
          </div>
          
          <!-- Lenyíló felület a profilkép alatt -->
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
                  <router-link
                    v-if="user.role === 'admin'"
                    to="/institution-dashboard"
                    class="menu-item"
                  >
                    <i class='bx bx-building-house'></i>
                    <span>Intézményvezetői felület</span>
                  </router-link>
                  <router-link to="/events-list" class="menu-item">
                    <i class='bx bx-calendar'></i>
                    <span>Események</span>
                  </router-link>
                  <router-link to="/events-calendar" class="menu-item">
                    <i class='bx bx-calendar-week'></i>
                    <span>Naptár</span>
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
            <h1>{{ profileDisplayName }}</h1>
            <p class="profile-role" :class="user.role">
              <i :class="roleIcon"></i>
              {{ roleDisplayName }}
            </p>
          </div>
        </div>

        <!-- Profil tartalom -->
        <div class="profile-content">
          <div class="view-section">
            <div class="info-grid">
              <!-- Alapadatok -->
              <div class="info-card">
                <h3><i class='bx bx-user'></i> Alapadatok</h3>
                <div class="info-list">
                  <div class="info-item">
                    <span class="info-label">Email cím:</span>
                    <span class="info-value">{{ user.email }}</span>
                  </div>

                  <div class="info-item">
                    <span class="info-label">Felhasználónév:</span>
                    <div class="info-value-inline">
                      <template v-if="!isEditingName">
                        <span class="info-value">{{ user.name || 'Nincs megadva' }}</span>
                        <button class="btn-icon-edit inline" @click="startEditingName" title="Felhasználónév szerkesztése">
                          <i class='bx bx-edit-alt'></i>
                        </button>
                      </template>
                      <div v-else class="name-edit-group inline">
                        <input 
                          v-model="editName" 
                          type="text" 
                          class="form-control name-edit-input" 
                          @keyup.enter="saveName"
                          @keyup.esc="cancelEditingName"
                          ref="nameInput"
                        >
                        <div class="edit-actions">
                          <button class="btn-save-mini" @click="saveName" :disabled="isSavingName">
                            <i v-if="isSavingName" class='bx bx-loader-alt bx-spin'></i>
                            <i v-else class='bx bx-check'></i>
                          </button>
                          <button class="btn-cancel-mini" @click="cancelEditingName" :disabled="isSavingName">
                            <i class='bx bx-x'></i>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="info-item">
                    <span class="info-label">Profil név:</span>
                    <span class="info-value">{{ profileDisplayName }}</span>
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
                    <span class="info-label">Vármegye:</span>
                    <span class="info-value">{{ user.region || 'Nincs megadva' }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Járás:</span>
                    <span class="info-value">{{ user.district ? `${user.district}i` : 'Nincs megadva' }}</span>
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
                    <span class="info-label">Vármegye:</span>
                    <span class="info-value">{{ user.region || 'Nincs megadva' }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Járás:</span>
                    <span class="info-value">{{ user.district ? `${user.district}i` : 'Nincs megadva' }}</span>
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
              <div v-if="user.role === 'admin'" class="info-card">
                <h3><i class='bx bx-building-house'></i> Intézmény adatok</h3>
                <div class="info-list">
                  <div class="info-item">
                    <span class="info-label">Vármegye:</span>
                    <span class="info-value">{{ user.region || 'Nincs megadva' }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Járás:</span>
                    <span class="info-value">{{ user.district ? `${user.district}i` : 'Nincs megadva' }}</span>
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
                    <span class="info-label">Regisztráció dátuma:</span>
                    <span class="info-value">{{ formatDate(user.created_at) }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Utolsó módosítás:</span>
                    <span class="info-value">{{ formatDate(user.updated_at) }}</span>
                  </div>
                </div>
              </div>

              <div class="info-card switcher-card">
                <h3><i class='bx bx-transfer-alt'></i> Profilváltás</h3>
                <div class="switcher-card-content">
                  <p>Váltson másik intézményi profilra, vagy indítson új csatlakozást tanárként, diákként, illetve hozzon létre új intézményt.</p>
                  <button class="btn-primary" type="button" @click="openEstablishmentSwitcher">
                    <i class='bx bx-transfer'></i>
                    Profilváltó megnyitása
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <transition name="slide-fade">
      <div v-if="showEstablishmentSwitcher" class="modal-backdrop" @click.self="closeEstablishmentSwitcher">
        <div class="switcher-modal">
          <div class="switcher-header">
            <div>
              <h2>Profilváltás</h2>
              <p>Válassz az intézményeid közül, vagy menj tovább új csatlakozásra.</p>
            </div>
            <button type="button" class="switcher-close" @click="closeEstablishmentSwitcher">
              <i class='bx bx-x'></i>
            </button>
          </div>

          <div v-if="isLoadingEstablishments" class="switcher-state">
            <i class='bx bx-loader-circle bx-spin'></i>
            <span>Intézmények betöltése...</span>
          </div>

          <div v-else class="switcher-body">
            <div v-if="establishments.length" class="establishment-list">
              <button
                v-for="establishment in establishments"
                :key="establishment.id"
                type="button"
                class="establishment-option"
                :class="{ current: establishment.is_current }"
                :disabled="Boolean(establishment.is_current) || isSwitchingEstablishment"
                @click="switchEstablishment(establishment)"
              >
                <div class="establishment-option-main">
                  <div class="establishment-option-title-row">
                    <h3>{{ establishment.title }}</h3>
                    <span class="establishment-role" :class="establishment.role">{{ getRoleDisplayName(establishment.role) }}</span>
                  </div>
                  <p v-if="establishment.address" class="establishment-address">{{ establishment.address }}</p>
                </div>
                <span v-if="establishment.is_current" class="current-establishment-badge">Jelenlegi</span>
                <i v-else class='bx bx-chevron-right'></i>
              </button>
            </div>

            <div v-else class="switcher-state empty">
              <i class='bx bx-buildings'></i>
              <span>Még nincs másik választható intézményed.</span>
            </div>

            <div class="switcher-footer">
              <button type="button" class="btn-primary" @click="goToRoleChooser">
                <i class='bx bx-plus-circle'></i>
                Másik intézményhez csatlakozom vagy újat hozok létre
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>

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
import logo2 from '../../assets/logo2.svg';
import { API_BASE, getToken, getAuthHeaders, clearAuthStorage } from '../../services/api'

export default {
  name: 'Profile',
  
  data() {
    return {
      logo2,
      user: {
        id: null,
        name: '',
        currentAlias: '',
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
      isEditingName: false,
      editName: '',
      isSavingName: false,
      showUserMenu: false,
      showEstablishmentSwitcher: false,
      isLoadingEstablishments: false,
      isSwitchingEstablishment: false,
      establishments: [],
      showScrollTop: false,
      showToast: false,
      toastMessage: '',
      toastType: 'success'
    }
  },
  
  computed: {
    profileDisplayName() {
      return (this.user.currentAlias || '').trim() || this.user.name || 'Felhasználó';
    },

    userInitials() {
      const sourceName = this.profileDisplayName;
      if (!sourceName) return '??';
      return sourceName
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
      return roles[this.user.role] || this.user.role || 'Felhasználó';
    },
    
    roleIcon() {
      const icons = {
        'student': 'bx bx-graduation',
        'teacher': 'bx bx-chalkboard',
        'admin': 'bx bx-cog'
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
    // Felhasználónév szerkesztésének indítása és fókuszálás az input mezőre
    startEditingName() {
      this.editName = this.user.name;
      this.isEditingName = true;
      this.$nextTick(() => {
        if (this.$refs.nameInput) {
          this.$refs.nameInput.focus();
        }
      });
    },

    cancelEditingName() {
      this.isEditingName = false;
      this.editName = '';
    },

    // A módosított felhasználónév mentése a backend felé
    async saveName() {
      if (!this.editName.trim() || this.editName === this.user.name) {
        this.cancelEditingName();
        return;
      }

      this.isSavingName = true;
      try {
        const token = getToken();
        const response = await axios.patch(`${API_BASE}/user/update`, 
          { name: this.editName },
          { headers: getAuthHeaders(token) }
        );

        this.user.name = this.editName;
        // Lokális tároló frissítése, hogy frissítés után is megmaradjon a név
        this.persistStoredUser({ 
          name: this.editName,
          name_updated_at: response.data.user.name_updated_at 
        });
        this.showNotification('Név sikeresen frissítve!', 'success');
        this.isEditingName = false;
      } catch (error) {
        console.error('Hiba a név frissítésekor:', error);
        const message = error.response?.data?.message || 'Nem sikerült frissíteni a nevet.';
        this.showNotification(message, 'error');
      } finally {
        this.isSavingName = false;
      }
    },

    // Kezdeti felhasználói adatok betöltése a helyi tárolóból (localStorage/sessionStorage)
    loadUserData() {
      const savedUser =
        localStorage.getItem('esemenyter_user') ||
        sessionStorage.getItem('esemenyter_user');
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
    
    // Friss adatok lekérése a szerverről
    async loadUserFromBackend() {
      try {
        const token = getToken();

        if (!token) {
          this.$router.push('/');
          return;
        }

        const response = await axios.get(`${API_BASE}/user`, {
          headers: getAuthHeaders(token)
        });
        
        const userData = response.data.data || response.data;
        this.user = {
          ...this.user,
          ...userData,
          schoolId: userData.establishment_id || this.user.schoolId || null
        };

        await this.loadEstablishments();
        
      } catch (error) {
        console.error('Hiba a felhasználói adatok betöltésekor:', error);
        this.showNotification('Hiba történt az adatok betöltésekor', 'error');
      }
    },

    // A felhasználóhoz tartozó intézmények lekérése a profilváltáshoz
    async loadEstablishments() {
      const token = getToken();

      if (!token) {
        return;
      }

      this.isLoadingEstablishments = true;

      try {
        const response = await axios.get(`${API_BASE}/establishment/mine`, {
          headers: getAuthHeaders(token)
        });

        this.establishments = Array.isArray(response?.data?.data) ? response.data.data : [];

        const currentEstablishment = this.establishments.find(item => item.is_current) || null;
        if (currentEstablishment) {
          // Itt frissítjük az aktuális intézmény adatait a felületen
          this.user.school = currentEstablishment.title || this.user.school;
          this.user.schoolId = currentEstablishment.id || this.user.schoolId;
          this.user.currentAlias = (currentEstablishment.alias || '').trim();
          this.user.schoolAddress = currentEstablishment.address || this.user.schoolAddress;
          this.user.schoolEmail = currentEstablishment.email || this.user.schoolEmail;
          this.user.schoolPhone = currentEstablishment.phone || this.user.schoolPhone;
          this.user.schoolWebsite = currentEstablishment.website || this.user.schoolWebsite;
          if (currentEstablishment.role) {
            this.user.role = currentEstablishment.role;
          }
          await this.loadEstablishmentDetails(currentEstablishment.id);
          this.persistStoredUser({
            role: this.user.role,
            currentAlias: this.user.currentAlias,
            school: this.user.school,
            schoolId: this.user.schoolId,
            establishment_id: this.user.schoolId,
            institution_id: this.user.schoolId
          });
        }
      } catch (error) {
        console.error('Hiba az intézményi tagságok betöltésekor:', error);
      } finally {
        this.isLoadingEstablishments = false;
      }
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
      if (this.user.role) {
        this.$router.push('/user-dashboard');
      } else {
        this.$router.push('/dashboard');
      }
    },

    openEstablishmentSwitcher() {
      this.showUserMenu = false;
      this.showEstablishmentSwitcher = true;
      this.loadEstablishments();
    },

    closeEstablishmentSwitcher() {
      this.showEstablishmentSwitcher = false;
    },

    getRoleDisplayName(role) {
      const roles = {
        student: 'Diák',
        teacher: 'Tanár',
        admin: 'Adminisztrátor'
      };

      return roles[role] || role || 'Ismeretlen';
    },

    saveCurrentInstitution(institutionId) {
      const numericInstitutionId = Number(institutionId);

      if (!Number.isFinite(numericInstitutionId) || numericInstitutionId <= 0) {
        return;
      }

      if (localStorage.getItem('esemenyter_token')) {
        localStorage.setItem('CurrentInstitution', String(numericInstitutionId));
        sessionStorage.removeItem('CurrentInstitution');
        return;
      }

      sessionStorage.setItem('CurrentInstitution', String(numericInstitutionId));
      localStorage.removeItem('CurrentInstitution');
    },

    persistStoredUser(fields = {}) {
      const localUser = localStorage.getItem('esemenyter_user');
      const sessionUser = sessionStorage.getItem('esemenyter_user');

      if (localUser) {
        const parsed = JSON.parse(localUser);
        localStorage.setItem('esemenyter_user', JSON.stringify({ ...parsed, ...fields }));
      }

      if (sessionUser) {
        const parsed = JSON.parse(sessionUser);
        sessionStorage.setItem('esemenyter_user', JSON.stringify({ ...parsed, ...fields }));
      }
    },

    // Adott intézmény részletes adatainak lekérése (helyszín, elérhetőség)
    async loadEstablishmentDetails(establishmentId) {
      const numericEstablishmentId = Number(establishmentId);
      if (!Number.isFinite(numericEstablishmentId) || numericEstablishmentId <= 0) {
        return;
      }

      const token = getToken();
      if (!token) {
        return;
      }

      try {
        const response = await axios.get(`${API_BASE}/establishment/${numericEstablishmentId}`, {
          headers: getAuthHeaders(token)
        });

        const details = response?.data?.data;
        if (!details) {
          return;
        }

        // Adatok frissítése az objektumban és a lokális tárolóban
        this.user.region = details.region_name || '';
        this.user.district = details.inner_region_name || '';
        this.user.city = details.settlement_name || '';
        this.user.school = details.title || this.user.school;
        this.user.schoolAddress = details.address || this.user.schoolAddress;
        this.user.schoolEmail = details.email || this.user.schoolEmail;
        this.user.schoolPhone = details.phone || this.user.schoolPhone;
        this.user.schoolWebsite = details.website || this.user.schoolWebsite;

        this.persistStoredUser({
          region: this.user.region,
          district: this.user.district,
          city: this.user.city,
          school: this.user.school,
          schoolAddress: this.user.schoolAddress,
          schoolEmail: this.user.schoolEmail,
          schoolPhone: this.user.schoolPhone,
          schoolWebsite: this.user.schoolWebsite,
          establishment_id: this.user.schoolId,
          institution_id: this.user.schoolId
        });
      } catch (error) {
        console.error('Hiba az intézmény részleteinek betöltésekor:', error);
      }
    },

    // Aktív intézmény váltása a listából választva
    async switchEstablishment(establishment) {
      if (!establishment || establishment.is_current || this.isSwitchingEstablishment) {
        return;
      }

      const token = getToken();
      if (!token) {
        this.$router.push('/');
        return;
      }

      this.isSwitchingEstablishment = true;

      try {
        // Backend hívás az aktív intézmény átállításához
        const response = await axios.patch(
          `${API_BASE}/establishment/switch`,
          { establishment_id: establishment.id },
          { headers: getAuthHeaders(token, true) }
        );

        const payload = response?.data?.data || {};
        const nextRole = payload.role || establishment.role || this.user.role;
        const nextSchoolId = Number(payload.establishment_id || establishment.id) || null;
        const nextSchool = payload.title || establishment.title || '';

        // Új adatok mentése a lokális állapotba és tárolókba
        this.saveCurrentInstitution(nextSchoolId);
        this.user.role = nextRole;
        this.user.schoolId = nextSchoolId;
        this.user.school = nextSchool;
        this.user.currentAlias = (establishment.alias || '').trim();
        this.user.schoolAddress = payload.address || establishment.address || '';
        this.user.schoolEmail = payload.email || establishment.email || '';
        this.user.schoolPhone = payload.phone || establishment.phone || '';
        this.user.schoolWebsite = payload.website || establishment.website || '';
        
        // Frissítjük a listát, hogy az új legyen kijelölve 'Jelenlegi'-nek
        this.establishments = this.establishments.map(item => ({
          ...item,
          is_current: Number(item.id) === Number(nextSchoolId)
        }));

        this.persistStoredUser({
          role: nextRole,
          currentAlias: this.user.currentAlias,
          school: nextSchool,
          schoolId: nextSchoolId,
          establishment_id: nextSchoolId,
          institution_id: nextSchoolId,
          pendingApproval: false,
          requestedRole: ''
        });

        this.showNotification(response?.data?.message || 'Profil sikeresen átváltva.', 'success');
        this.closeEstablishmentSwitcher();
        await this.loadUserFromBackend();
        await this.loadEstablishmentDetails(nextSchoolId);
      } catch (error) {
        console.error('Hiba az aktív intézmény váltásakor:', error);
        const message = error?.response?.data?.message || 'Nem sikerült átváltani a kiválasztott profilra.';
        this.showNotification(message, 'error');
      } finally {
        this.isSwitchingEstablishment = false;
      }
    },

    goToRoleChooser() {
      this.closeEstablishmentSwitcher();
      this.$router.push({ path: '/dashboard', query: { chooseRole: '1' } });
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

    handleDocumentClick(e) {
      if (!e.target.closest('.user-profile')) {
        this.showUserMenu = false;
      }
    },
    
    logout() {
      axios.delete(`${API_BASE}/logout`, {
        headers: getAuthHeaders(getToken())
      })
        .finally(() => {
          clearAuthStorage();
          delete axios.defaults.headers.common['Authorization'];
          this.$router.push('/');
        });
    }
  },
  
  mounted() {
    this.loadUserData();
    window.addEventListener('scroll', this.handleScroll);

    document.addEventListener('click', this.handleDocumentClick);
  },
  
  beforeUnmount() {
    window.removeEventListener('scroll', this.handleScroll);
    document.removeEventListener('click', this.handleDocumentClick);
  }
}
</script>

<style scoped>
.profile {
  box-sizing: border-box;
  font-family: "Poppins", sans-serif;
  min-height: 100vh;
  width: 100%;
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
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #ffffff;
  box-shadow: 0 8px 18px rgba(15, 23, 42, 0.12);
  overflow: hidden;
}

.logo-image {
  width: 100%;
  height: 100%;
  object-fit: contain;
  display: block;
}

.logo-text h1 {
  margin: 0;
  font-size: 24px;
  font-weight: 700;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  background-clip: text;
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
  z-index: 9999;
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
  color: #111827;
}

.name-display-group {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 10px;
}

.btn-icon-edit {
  background: none;
  border: none;
  color: #6b7280;
  font-size: 20px;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: all 0.2s;
}

.btn-icon-edit:hover {
  background: #f3f4f6;
  color: #4f46e5;
}

.name-edit-group {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
}

.name-edit-input {
  font-size: 24px;
  font-weight: 700;
  padding: 4px 12px;
  height: auto;
  max-width: 300px;
}

.edit-actions {
  display: flex;
  gap: 8px;
}

.btn-save-mini, .btn-cancel-mini {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 18px;
  transition: all 0.2s;
}

.btn-save-mini {
  background: #10b981;
  color: white;
}

.btn-save-mini:hover:not(:disabled) {
  background: #059669;
}

.btn-cancel-mini {
  background: #ef4444;
  color: white;
}

.btn-cancel-mini:hover:not(:disabled) {
  background: #dc2626;
}

.btn-save-mini:disabled, .btn-cancel-mini:disabled {
  opacity: 0.5;
  cursor: not-allowednslateY(-10px);
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

.profile-role.admin {
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

.switcher-card {
  display: flex;
  flex-direction: column;
}

.switcher-card-content {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.switcher-card-content p {
  margin: 0;
  color: #4b5563;
  line-height: 1.6;
}

.switcher-card-content .btn-primary {
  align-self: flex-start;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.45);
  backdrop-filter: blur(6px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
  z-index: 2000;
}

.switcher-modal {
  width: min(720px, 100%);
  max-height: min(80vh, 760px);
  background: white;
  border-radius: 24px;
  box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.switcher-header {
  padding: 24px 28px;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  border-bottom: 1px solid #e5e7eb;
}

.switcher-header h2 {
  margin: 0 0 6px 0;
  color: #111827;
}

.switcher-header p {
  margin: 0;
  color: #6b7280;
}

.switcher-close {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  border: none;
  background: #f3f4f6;
  color: #374151;
  font-size: 22px;
  cursor: pointer;
}

.switcher-body {
  padding: 24px 28px 28px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.switcher-state {
  padding: 40px 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  color: #4b5563;
}

.switcher-state.empty {
  flex-direction: column;
}

.switcher-state i {
  font-size: 24px;
}

.establishment-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
  max-height: 420px;
  overflow-y: auto;
  padding-right: 6px;
}

.establishment-option {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  width: 100%;
  padding: 18px 20px;
  border: 1px solid #dbe4ff;
  border-radius: 18px;
  background: linear-gradient(135deg, #f8f9ff 0%, #eef2ff 100%);
  text-align: left;
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.establishment-option:hover:not(:disabled) {
  transform: translateY(-1px);
  border-color: #8b5cf6;
  box-shadow: 0 12px 24px rgba(79, 70, 229, 0.12);
}

.establishment-option:disabled {
  cursor: default;
  opacity: 1;
}

.establishment-option.current {
  border-color: #10b981;
  background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%);
}

.establishment-option-main {
  min-width: 0;
}

.establishment-option-title-row {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.establishment-option-title-row h3 {
  margin: 0;
  color: #111827;
  font-size: 18px;
}

.establishment-address {
  margin: 8px 0 0 0;
  color: #6b7280;
  font-size: 14px;
}

.establishment-role {
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  padding: 4px 10px;
  font-size: 12px;
  font-weight: 700;
}

.establishment-role.student {
  background: rgba(16, 185, 129, 0.12);
  color: #059669;
}

.establishment-role.teacher {
  background: rgba(249, 115, 22, 0.12);
  color: #ea580c;
}

.establishment-role.admin {
  background: rgba(99, 102, 241, 0.12);
  color: #4f46e5;
}

.current-establishment-badge {
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  padding: 6px 12px;
  background: #10b981;
  color: white;
  font-size: 12px;
  font-weight: 700;
}

.switcher-footer {
  display: flex;
  justify-content: flex-end;
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

.info-value-inline {
  display: flex;
  align-items: center;
  gap: 8px;
  flex: 1;
  min-width: 0;
}

.btn-icon-edit.inline {
  font-size: 16px;
  padding: 2px;
}

.name-edit-group.inline {
  margin-bottom: 0;
  width: 100%;
  gap: 8px;
}

.name-edit-group.inline .name-edit-input {
  font-size: 14px;
  font-weight: 500;
  max-width: 260px;
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
  .main-header {
    padding: 12px 0;
  }

  .header-content {
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    gap: 0;
  }

  .logo-text h1,
  .site-subtitle {
    display: none;
  }

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

  .switcher-card-content .btn-primary {
    width: 100%;
    justify-content: center;
    align-self: stretch;
  }

  .switcher-modal {
    max-height: 88vh;
  }

  .switcher-header,
  .switcher-body {
    padding-left: 20px;
    padding-right: 20px;
  }

  .switcher-footer .btn-primary {
    width: 100%;
    justify-content: center;
  }

  .establishment-option {
    align-items: flex-start;
    flex-direction: column;
  }
  
  .info-grid {
    grid-template-columns: 1fr;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .user-menu {
    width: 220px;
    right: 0;
    left: auto;
    transform: none;
  }

  .menu-header {
    padding: 12px 16px;
  }

  .menu-items {
    padding: 6px 0;
  }

  .menu-item {
    padding: 8px 12px;
    font-size: 13px;
  }

  .menu-item i {
    font-size: 16px;
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
  .main-header {
    padding: 8px 0;
  }

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