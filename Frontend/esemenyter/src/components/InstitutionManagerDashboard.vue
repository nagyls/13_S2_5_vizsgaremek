<template>
  <div class="institution-dashboard">
    <header class="main-header">
      <div class="container">
        <div class="header-content">
          <!-- Logo - csak akkor kattintható, ha van legalább 1 osztály -->
          <div class="logo-section" :class="{ 'disabled': classes.length === 0 }" @click="goToDashboard">
            <div class="logo-icon">
              <i class='bx bx-calendar-heart'></i>
            </div>
            <div class="logo-text">
              <h1 class="site-title">EseményTér</h1>
              <p class="site-subtitle">Ahol minden eseményt helyet kap</p>
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
                  <div class="role-badge institution">
                    Intézményvezető
                  </div>
                </div>
                <div class="menu-items">
                  <!-- Profil menüpont - csak akkor jelenik meg, ha van osztály -->
                  <router-link v-if="classes.length > 0" to="/profile" class="menu-item">
                    <i class='bx bx-user'></i>
                    <span>Profilom</span>
                  </router-link>
                  
                  <!-- Események menüpont - csak akkor jelenik meg, ha van osztály -->
                  <router-link v-if="classes.length > 0" to="/events-list" class="menu-item">
                    <i class='bx bx-calendar'></i>
                    <span>Események</span>
                  </router-link>
                  
                  <!-- Intézmény beállítások - mindig elérhető -->
                  <router-link to="/institution-settings" class="menu-item">
                    <i class='bx bx-building'></i>
                    <span>Intézmény beállítások</span>
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
        <!-- Intézmény információ -->
        <div class="institution-info-card">
          <div class="institution-icon">
            <i class='bx bx-school'></i>
          </div>
          <div class="institution-details">
            <h2>{{ institution.name }}</h2>
            <p class="institution-address">
              <i class='bx bx-map'></i>
              {{ institution.address || 'Cím nem elérhető' }}
            </p>
            <div class="institution-stats">
              <div class="stat-item">
                <span class="stat-value">{{ stats.totalStudents }}</span>
                <span class="stat-label">Diák</span>
              </div>
              <div class="stat-item">
                <span class="stat-value">{{ stats.totalTeachers }}</span>
                <span class="stat-label">Tanár</span>
              </div>
              <div class="stat-item">
                <span class="stat-value">{{ stats.totalClasses }}</span>
                <span class="stat-label">Osztály</span>
              </div>
              <div class="stat-item">
                <span class="stat-value">{{ totalPendingRequests }}</span>
                <span class="stat-label">Függő kérelem</span>
              </div>
            </div>
          </div>
        </div>

        <!-- CSATLAKOZÁSI KÉRELMEK SZEKCIÓ - csak akkor jelenik meg, ha van osztály -->
        <div v-if="classes.length > 0" class="requests-section">
          <div class="section-header">
            <h3>
              <i class='bx bx-user-check'></i>
              Csatlakozási kérelmek
            </h3>
            <div class="header-actions">
              <div class="search-wrapper">
                <i class='bx bx-search'></i>
                <input 
                  type="text" 
                  v-model="searchQuery"
                  placeholder="Keresés név vagy email alapján..."
                  class="search-input"
                />
              </div>
            </div>
          </div>
          
          <!-- Fülek a kérelmekhez -->
          <div class="request-tabs">
            <button 
              class="request-tab" 
              :class="{ 'active': activeRequestTab === 'students' }"
              @click="activeRequestTab = 'students'"
            >
              <i class='bx bx-graduation'></i>
              Diák kérelmek ({{ pendingStudentRequests.length }})
            </button>
            <button 
              class="request-tab" 
              :class="{ 'active': activeRequestTab === 'teachers' }"
              @click="activeRequestTab = 'teachers'"
            >
              <i class='bx bx-chalkboard'></i>
              Tanár kérelmek ({{ pendingTeacherRequests.length }})
            </button>
          </div>

          <!-- Diák kérelmek -->
          <div v-if="activeRequestTab === 'students'" class="requests-group">
            <div v-if="filteredStudentRequests.length > 0" class="requests-grid">
              <div 
                v-for="request in filteredStudentRequests" 
                :key="request.id"
                class="request-card pending"
              >
                <div class="request-header">
                  <div class="user-avatar-medium">
                    <span>{{ getUserInitials(request.user) }}</span>
                  </div>
                  <div class="user-info">
                    <h4>{{ request.user.name }}</h4>
                    <p class="user-email">{{ request.user.email }}</p>
                  </div>
                </div>

                <div class="request-body">
                  <div class="info-row">
                    <i class='bx bx-calendar'></i>
                    <span>Kérelem dátuma: {{ formatDate(request.created_at) }}</span>
                  </div>
                </div>

                <div class="request-actions">
                  <button class="btn-approve" @click="showClassAssignmentModal(request)">
                    <i class='bx bx-check'></i>
                    <span>Elfogadás</span>
                  </button>
                  <button class="btn-reject" @click="rejectRequest(request)">
                    <i class='bx bx-x'></i>
                    <span>Elutasítás</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- Nincs diák kérelem -->
            <div v-else-if="pendingStudentRequests.length === 0 && !searchQuery" class="empty-state">
              <i class='bx bx-inbox'></i>
              <h4>Nincsenek diák kérelmek</h4>
              <p>Még nem érkezett diák csatlakozási kérelem az intézményhez.</p>
            </div>

            <!-- Nincs találat keresésre -->
            <div v-else-if="filteredStudentRequests.length === 0 && searchQuery" class="empty-state">
              <i class='bx bx-search-alt'></i>
              <h4>Nincs találat</h4>
              <p>A keresés nem hozott eredményt: "{{ searchQuery }}"</p>
            </div>
          </div>

          <!-- Tanár kérelmek -->
          <div v-if="activeRequestTab === 'teachers'" class="requests-group">
            <div v-if="filteredTeacherRequests.length > 0" class="requests-grid">
              <div 
                v-for="request in filteredTeacherRequests" 
                :key="request.id"
                class="request-card pending"
              >
                <div class="request-header">
                  <div class="user-avatar-medium">
                    <span>{{ getUserInitials(request.user) }}</span>
                  </div>
                  <div class="user-info">
                    <h4>{{ request.user.name }}</h4>
                    <p class="user-email">{{ request.user.email }}</p>
                  </div>
                </div>

                <div class="request-body">
                  <div class="info-row">
                    <i class='bx bx-calendar'></i>
                    <span>Kérelem dátuma: {{ formatDate(request.created_at) }}</span>
                  </div>
                  <div class="info-row" v-if="request.specializations">
                    <i class='bx bx-star'></i>
                    <span>Szakosodás: {{ request.specializations }}</span>
                  </div>
                </div>

                <div class="request-actions">
                  <button class="btn-approve" @click="showClassAssignmentModal(request)">
                    <i class='bx bx-check'></i>
                    <span>Elfogadás</span>
                  </button>
                  <button class="btn-reject" @click="rejectRequest(request)">
                    <i class='bx bx-x'></i>
                    <span>Elutasítás</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- Nincs tanár kérelem -->
            <div v-else-if="pendingTeacherRequests.length === 0 && !searchQuery" class="empty-state">
              <i class='bx bx-inbox'></i>
              <h4>Nincsenek tanár kérelmek</h4>
              <p>Még nem érkezett tanár csatlakozási kérelem az intézményhez.</p>
            </div>

            <!-- Nincs találat keresésre -->
            <div v-else-if="filteredTeacherRequests.length === 0 && searchQuery" class="empty-state">
              <i class='bx bx-search-alt'></i>
              <h4>Nincs találat</h4>
              <p>A keresés nem hozott eredményt: "{{ searchQuery }}"</p>
            </div>
          </div>
        </div>

        <!-- OSZTÁLYOK LÉTREHOZÁSA SZEKCIÓ - mindig látható -->
        <div class="classes-section" :class="{ 
          'warning': classes.length === 0,
          'completed': classes.length > 0 
        }">
          <div class="section-header">
            <h3>
              <i class='bx bx-group'></i>
              Osztályok kezelése
            </h3>
            <div v-if="classes.length === 0" class="warning-message">
              <i class='bx bx-error-circle'></i>
              <span>Legalább egy osztályt létre kell hoznod!</span>
            </div>
            <div v-else class="success-message">
              <i class='bx bx-check-circle'></i>
              <span>Osztályok sikeresen létrehozva ({{ classes.length }} osztály)</span>
            </div>
          </div>

          <!-- Osztály létrehozó űrlap -->
          <div class="create-class-form">
            <h4>Új osztály létrehozása</h4>
            <div class="form-row">
              <div class="form-group">
                <label for="className">Osztály neve *</label>
                <input 
                  type="text" 
                  id="className"
                  v-model="newClass.name"
                  placeholder="Pl.: 9.A, 10.B, 5. osztály"
                  class="form-control"
                  :class="{ 'error': classErrors.name }"
                />
                <span v-if="classErrors.name" class="error-message">{{ classErrors.name }}</span>
              </div>

              <div class="form-group">
                <label for="classGrade">Évfolyam *</label>
                <select 
                  id="classGrade"
                  v-model="newClass.grade"
                  class="form-control"
                  :class="{ 'error': classErrors.grade }"
                >
                  <option value="">Válassz évfolyamot</option>
                  <option v-for="grade in availableGrades" :key="grade" :value="grade">
                    {{ grade }}. évfolyam
                  </option>
                </select>
                <span v-if="classErrors.grade" class="error-message">{{ classErrors.grade }}</span>
              </div>

              <div class="form-group">
                <label for="classCapacity">Férőhely (opcionális)</label>
                <input 
                  type="number" 
                  id="classCapacity"
                  v-model="newClass.capacity"
                  placeholder="Pl.: 30"
                  min="1"
                  class="form-control"
                />
              </div>

              <div class="form-group">
                <label for="classTeacher">Osztályfőnök (opcionális)</label>
                <select 
                  id="classTeacher"
                  v-model="newClass.teacher_id"
                  class="form-control"
                >
                  <option value="">-- Nincs kijelölve --</option>
                  <option 
                    v-for="teacher in teachers" 
                    :key="teacher.id" 
                    :value="teacher.id"
                  >
                    {{ teacher.name }}
                  </option>
                </select>
                <small class="form-text">Az osztályfőnök később is kijelölhető</small>
              </div>

              <div class="form-group">
                <button class="btn-primary" @click="createClass" :disabled="isCreatingClass">
                  <i class='bx bx-plus'></i>
                  {{ isCreatingClass ? 'Létrehozás...' : 'Osztály létrehozása' }}
                </button>
              </div>
            </div>
          </div>

          <!-- Létrehozott osztályok listája -->
          <div class="classes-list">
            <h4>Létrehozott osztályok ({{ classes.length }})</h4>
            
            <div v-if="classes.length === 0" class="empty-state small">
              <i class='bx bx-folder-open'></i>
              <p>Még nem hoztál létre egyetlen osztályt sem.</p>
            </div>

            <div v-else class="classes-grid">
              <div v-for="classItem in classes" :key="classItem.id" class="class-item">
                <div class="class-item-header">
                  <h5>{{ classItem.name }}</h5>
                  <span class="class-grade">{{ classItem.grade }}. évfolyam</span>
                </div>
                <div class="class-item-body">
                  <div class="class-stat">
                    <i class='bx bx-group'></i>
                    <span>{{ classItem.student_count || 0 }} / {{ classItem.capacity || '∞' }} diák</span>
                  </div>
                  <div class="class-stat" v-if="classItem.teacher_name">
                    <i class='bx bx-chalkboard'></i>
                    <span>Osztályfőnök: {{ classItem.teacher_name }}</span>
                  </div>
                </div>
                <div class="class-item-actions">
                  <button class="btn-icon" @click="editClass(classItem)" title="Szerkesztés">
                    <i class='bx bx-edit'></i>
                  </button>
                  <button class="btn-icon" @click="deleteClass(classItem)" title="Törlés">
                    <i class='bx bx-trash'></i>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Figyelmeztetés, ha nincs osztály -->
          <div v-if="classes.length === 0" class="blocking-warning">
            <i class='bx bx-error-circle'></i>
            <div class="warning-content">
              <h4>Osztály létrehozása szükséges</h4>
              <p>Ahhoz, hogy diákok és tanárok csatlakozhassanak az intézményhez, és hogy elérd a teljes funkciókat, legalább egy osztályt létre kell hoznod.</p>
            </div>
          </div>

          <!-- Sikeres véglegesítés üzenet, ha van osztály -->
          <div v-if="classes.length > 0" class="success-info">
            <i class='bx bx-check-circle'></i>
            <div class="success-content">
              <h4>Gratulálunk! 🎉</h4>
              <p>Sikeresen létrehoztad az első osztályt. Most már teljes körűen használhatod az EseményTér összes funkcióját:</p>
              <ul>
                <li><i class='bx bx-check'></i> Diákok és tanárok csatlakozási kérelmeinek kezelése</li>
                <li><i class='bx bx-check'></i> Események létrehozása és kezelése</li>
                <li><i class='bx bx-check'></i> Profilod szerkesztése</li>
                <li><i class='bx bx-check'></i> Események böngészése</li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Már csatlakozott felhasználók - csak akkor jelenik meg, ha van osztály -->
        <div v-if="classes.length > 0" class="connected-users-section">
          <div class="section-header">
            <h3>
              <i class='bx bx-group'></i>
              Már csatlakozott felhasználók
            </h3>
          </div>

          <!-- Tabok a szerepkörök szerint -->
          <div class="user-tabs">
            <button 
              class="user-tab" 
              :class="{ 'active': activeUserTab === 'students' }"
              @click="activeUserTab = 'students'"
            >
              <i class='bx bx-graduation'></i>
              Diákok ({{ students.length }})
            </button>
            <button 
              class="user-tab" 
              :class="{ 'active': activeUserTab === 'teachers' }"
              @click="activeUserTab = 'teachers'"
            >
              <i class='bx bx-chalkboard'></i>
              Tanárok ({{ teachers.length }})
            </button>
          </div>

          <!-- Diákok listája -->
          <div v-if="activeUserTab === 'students'" class="users-grid">
            <div v-if="students.length === 0" class="empty-state small">
              <i class='bx bx-user-x'></i>
              <p>Még nincsenek diákok az intézményben</p>
            </div>
            <div v-else v-for="student in students" :key="student.id" class="user-card">
              <div class="user-card-header">
                <div class="user-avatar-small">
                  <span>{{ getUserInitials(student) }}</span>
                </div>
                <div class="user-card-info">
                  <h4>{{ student.name }}</h4>
                  <p class="user-email">{{ student.email }}</p>
                </div>
              </div>
              <div class="user-card-body">
                <div class="info-row">
                  <i class='bx bx-group'></i>
                  <span>Osztály: {{ student.class_name || 'Nincs beállítva' }}</span>
                </div>
              </div>
              <div class="user-card-actions">
                <button class="btn-icon" @click="editUserClass(student)" title="Osztály módosítása">
                  <i class='bx bx-edit'></i>
                </button>
                <button class="btn-icon" @click="viewUserDetails(student)" title="Részletek">
                  <i class='bx bx-show'></i>
                </button>
              </div>
            </div>
          </div>

          <!-- Tanárok listája -->
          <div v-if="activeUserTab === 'teachers'" class="users-grid">
            <div v-if="teachers.length === 0" class="empty-state small">
              <i class='bx bx-user-x'></i>
              <p>Még nincsenek tanárok az intézményben</p>
            </div>
            <div v-else v-for="teacher in teachers" :key="teacher.id" class="user-card">
              <div class="user-card-header">
                <div class="user-avatar-small">
                  <span>{{ getUserInitials(teacher) }}</span>
                </div>
                <div class="user-card-info">
                  <h4>{{ teacher.name }}</h4>
                  <p class="user-email">{{ teacher.email }}</p>
                </div>
              </div>
              <div class="user-card-body">
                <div class="info-row">
                  <i class='bx bx-chalkboard'></i>
                  <span>Tanított osztályok: {{ teacher.classes?.length || 0 }}</span>
                </div>
              </div>
              <div class="user-card-actions">
                <button class="btn-icon" @click="editTeacherClasses(teacher)" title="Osztályok módosítása">
                  <i class='bx bx-edit'></i>
                </button>
                <button class="btn-icon" @click="viewUserDetails(teacher)" title="Részletek">
                  <i class='bx bx-show'></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Osztály hozzárendelés modal - csak akkor jelenik meg, ha van osztály -->
    <transition name="modal">
      <div v-if="showAssignmentModal && classes.length > 0" class="modal-overlay" @click.self="closeAssignmentModal">
        <div class="modal-container">
          <div class="modal-header">
            <h3>
              <i class='bx bx-group'></i>
              Osztály hozzárendelése
            </h3>
            <button class="modal-close" @click="closeAssignmentModal">
              <i class='bx bx-x'></i>
            </button>
          </div>
          
          <div class="modal-body">
            <div class="user-summary">
              <div class="user-avatar-large">
                <span>{{ getUserInitials(selectedRequest?.user) }}</span>
              </div>
              <div class="user-summary-info">
                <h4>{{ selectedRequest?.user.name }}</h4>
                <p>{{ selectedRequest?.user.email }}</p>
                <div class="role-badge-small">
                  {{ selectedRequest?.role === 'student' ? 'Diák' : 'Tanár' }}
                </div>
              </div>
            </div>

            <div class="assignment-form">
              <div class="form-group">
                <label for="class-select">Válassz osztályt:</label>
                <select 
                  id="class-select"
                  v-model="selectedClassId" 
                  class="form-select"
                  :disabled="!classes.length"
                >
                  <option value="">-- Osztály kiválasztása --</option>
                  <option 
                    v-for="classItem in classes" 
                    :key="classItem.id" 
                    :value="classItem.id"
                  >
                    {{ classItem.name }} 
                    ({{ classItem.student_count || 0 }}/{{ classItem.capacity || '∞' }} diák)
                  </option>
                </select>
                <p v-if="!classes.length" class="form-hint">
                  <i class='bx bx-info-circle'></i>
                  Még nincsenek osztályok létrehozva. Először hozz létre osztályokat.
                </p>
              </div>
            </div>

            <div v-if="errorMessage" class="error-message">
              <i class='bx bx-error-circle'></i>
              <span>{{ errorMessage }}</span>
            </div>
          </div>

          <div class="modal-footer">
            <button class="btn-outline" @click="closeAssignmentModal">Mégse</button>
            <button 
              class="btn-primary" 
              @click="approveRequest" 
              :disabled="!selectedClassId"
            >
              <i class='bx bx-check'></i>
              Kérelem elfogadása
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Sikeres művelet értesítés -->
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
// import { format } from 'date-fns';
// import { hu } from 'date-fns/locale';

export default {
  name: 'InstitutionManagerDashboard',
  
  data() {
    return {
      user: {
        id: null,
        name: '',
        email: '',
        institution_id: null,
        role: 'institution_manager'
      },
      institution: {
        id: null,
        name: '',
        address: '',
        type: ''
      },
      stats: {
        totalStudents: 0,
        totalTeachers: 0,
        totalClasses: 0
      },
      establishmentRequests: [], // Összes kérelem a táblából
      allUsers: [], // Összes felhasználó
      students: [], // Diákok
      teachers: [], // Tanárok
      classes: [], // Osztályok
      userRoles: {}, // Felhasználók szerepköreinek gyorsítótárazása
      
      activeRequestTab: 'students', // 'students' vagy 'teachers'
      activeUserTab: 'students',
      showUserMenu: false,
      showScrollTop: false,
      
      // Keresés
      searchQuery: '',
      
      // Modal állapotok
      showAssignmentModal: false,
      selectedRequest: null,
      selectedClassId: '',
      errorMessage: '',
      
      // Toast értesítések
      showToast: false,
      toastMessage: '',
      toastType: 'success',
      
      // Új osztály létrehozása
      newClass: {
        name: '',
        grade: '',
        capacity: 30,
        teacher_id: ''
      },
      classErrors: {},
      isCreatingClass: false,
      availableGrades: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13]
    };
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
    
    // Összes függőben lévő kérelem
    totalPendingRequests() {
      return this.establishmentRequests.length;
    },
    
    // Diák kérelmek
    pendingStudentRequests() {
      return this.establishmentRequests.filter(req => req.role === 'student');
    },
    
    // Tanár kérelmek
    pendingTeacherRequests() {
      return this.establishmentRequests.filter(req => req.role === 'teacher');
    },
    
    // Szűrt diák kérelmek
    filteredStudentRequests() {
      if (!this.searchQuery) return this.pendingStudentRequests;
      
      const query = this.searchQuery.toLowerCase();
      return this.pendingStudentRequests.filter(request => {
        const user = this.getUserById(request.user_id);
        return user && (
          user.name.toLowerCase().includes(query) ||
          user.email.toLowerCase().includes(query)
        );
      });
    },
    
    // Szűrt tanár kérelmek
    filteredTeacherRequests() {
      if (!this.searchQuery) return this.pendingTeacherRequests;
      
      const query = this.searchQuery.toLowerCase();
      return this.pendingTeacherRequests.filter(request => {
        const user = this.getUserById(request.user_id);
        return user && (
          user.name.toLowerCase().includes(query) ||
          user.email.toLowerCase().includes(query)
        );
      });
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
  
  watch: {
    // Ha nincs osztály, blokkoljuk a navigációt
    classes: {
      handler(newVal) {
        if (newVal.length === 0) {
          // Blokkoljuk a navigációt
          window.onbeforeunload = () => {
            return 'Még nem hoztál létre osztályt! Ha elhagyod az oldalt, nem fogadják el a csatlakozási kérelmeket.';
          };
        } else {
          window.onbeforeunload = null;
        }
      },
      immediate: true
    }
  },
  
  methods: {
    // Felhasználó lekérése ID alapján
    getUserById(userId) {
      return this.allUsers.find(u => u.id === userId) || null;
    },
    
    // Felhasználó kezdőbetűi
    getUserInitials(user) {
      if (!user || !user.name) return '?';
      return user.name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
    },
    
    // Dátum formázás
    formatDate(date) {
      if (!date) return 'Ismeretlen';
      return new Date(date).toLocaleDateString('hu-HU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    },
    
    toggleUserMenu() {
      this.showUserMenu = !this.showUserMenu;
    },
    
    // Navigáció a dashboardra - csak akkor engedélyezett, ha van osztály
    goToDashboard() {
      if (this.classes.length > 0) {
        this.$router.push('/user-dashboard');
      }
      // Ha nincs osztály, nem csinál semmit
    },
    
    // Adatok betöltése
    async loadInstitutionData() {
      try {
        const token = localStorage.getItem('esemenyter_token');
        const institutionId = this.user.institution_id;
        
        if (!institutionId) {
          console.error('Nincs intézmény ID');
          return;
        }
        
        // Intézmény adatok
        const instResponse = await axios.get(`http://127.0.0.1:8000/api/establishments/${institutionId}`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        this.institution = instResponse.data.data || instResponse.data;
        
        // Csatlakozási kérelmek (establishment_requests tábla)
        const requestsResponse = await axios.get(`http://127.0.0.1:8000/api/establishments/${institutionId}/requests`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        this.establishmentRequests = requestsResponse.data.data || [];
        
        // Összes felhasználó betöltése (a kérelmekhez kapcsolódóan)
        const userIds = this.establishmentRequests.map(r => r.user_id);
        if (userIds.length > 0) {
          await this.loadUsers(userIds);
        }
        
        // Diákok és tanárok betöltése (akik már csatlakoztak)
        await this.loadInstitutionUsers(institutionId);
        
        // Osztályok betöltése
        await this.loadClasses(institutionId);
        
        // Statisztikák frissítése
        this.updateStats();
        
      } catch (error) {
        console.error('Hiba az adatok betöltésekor:', error);
        this.showNotification('Hiba történt az adatok betöltésekor', 'error');
      }
    },
    
    // Felhasználók betöltése ID-k alapján
    async loadUsers(userIds) {
      try {
        const token = localStorage.getItem('esemenyter_token');
        
        // Unique user IDs
        const uniqueIds = [...new Set(userIds)];
        
        // Felhasználók betöltése egyesével (vagy batch API-val)
        const userPromises = uniqueIds.map(id => 
          axios.get(`http://127.0.0.1:8000/api/users/${id}`, {
            headers: { Authorization: `Bearer ${token}` }
          }).then(res => res.data.data || res.data)
        );
        
        const users = await Promise.all(userPromises);
        this.allUsers = users;
        
      } catch (error) {
        console.error('Hiba a felhasználók betöltésekor:', error);
      }
    },
    
    // Intézmény felhasználóinak betöltése (akik már csatlakoztak)
    async loadInstitutionUsers(institutionId) {
      try {
        const token = localStorage.getItem('esemenyter_token');
        
        // Diákok
        const studentsResponse = await axios.get(`http://127.0.0.1:8000/api/establishments/${institutionId}/students`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        this.students = studentsResponse.data.data || [];
        
        // Tanárok
        const teachersResponse = await axios.get(`http://127.0.0.1:8000/api/establishments/${institutionId}/teachers`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        this.teachers = teachersResponse.data.data || [];
        
      } catch (error) {
        console.error('Hiba az intézmény felhasználóinak betöltésekor:', error);
      }
    },
    
    // Osztályok betöltése
    async loadClasses(institutionId) {
      try {
        const token = localStorage.getItem('esemenyter_token');
        
        const response = await axios.get(`http://127.0.0.1:8000/api/establishments/${institutionId}/classes`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        this.classes = response.data.data || [];
        
      } catch (error) {
        console.error('Hiba az osztályok betöltésekor:', error);
      }
    },
    
    // Statisztikák frissítése
    updateStats() {
      this.stats = {
        totalStudents: this.students.length,
        totalTeachers: this.teachers.length,
        totalClasses: this.classes.length
      };
    },
    
    // Új osztály létrehozása
    async createClass() {
      this.classErrors = {};
      
      // Validáció - csak a kötelező mezőket ellenőrizzük
      if (!this.newClass.name?.trim()) {
        this.classErrors.name = 'Az osztály neve kötelező';
      }
      
      if (!this.newClass.grade) {
        this.classErrors.grade = 'Az évfolyam kötelező';
      }
      
      if (Object.keys(this.classErrors).length > 0) {
        return;
      }
      
      this.isCreatingClass = true;
      
      try {
        const token = localStorage.getItem('esemenyter_token');
        
        const classData = {
          name: this.newClass.name,
          grade: this.newClass.grade,
          capacity: this.newClass.capacity || null,
          teacher_id: this.newClass.teacher_id || null, // Lehet üres is
          establishment_id: this.user.institution_id
        };
        
        const response = await axios.post(`http://127.0.0.1:8000/api/classes`, classData, {
          headers: { Authorization: `Bearer ${token}` }
        });
        
        // Újratöltjük az osztályokat
        await this.loadClasses(this.user.institution_id);
        
        // Űrlap alaphelyzetbe
        this.newClass = {
          name: '',
          grade: '',
          capacity: 30,
          teacher_id: ''  // Üresen hagyható
        };
        
        this.showNotification('Osztály sikeresen létrehozva!', 'success');
        
      } catch (error) {
        console.error('Hiba az osztály létrehozásakor:', error);
        this.showNotification('Hiba történt az osztály létrehozásakor', 'error');
      } finally {
        this.isCreatingClass = false;
      }
    },
    
    // Osztály szerkesztése
    async editClass(classItem) {
      // TODO: Szerkesztő modal megnyitása
      console.log('Edit class:', classItem);
      this.showNotification('Szerkesztés funkció fejlesztés alatt', 'info');
    },
    
    // Osztály törlése
    async deleteClass(classItem) {
      if (!confirm(`Biztosan törölni szeretnéd a(z) ${classItem.name} osztályt?`)) {
        return;
      }
      
      try {
        const token = localStorage.getItem('esemenyter_token');
        
        await axios.delete(`http://127.0.0.1:8000/api/classes/${classItem.id}`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        
        // Újratöltjük az osztályokat
        await this.loadClasses(this.user.institution_id);
        
        this.showNotification('Osztály sikeresen törölve!', 'success');
        
      } catch (error) {
        console.error('Hiba az osztály törlésekor:', error);
        this.showNotification('Hiba történt az osztály törlésekor', 'error');
      }
    },
    
    // Kérelem kezelés
    showClassAssignmentModal(request) {
      this.selectedRequest = request;
      this.selectedClassId = '';
      this.errorMessage = '';
      this.showAssignmentModal = true;
    },
    
    closeAssignmentModal() {
      this.showAssignmentModal = false;
      this.selectedRequest = null;
      this.selectedClassId = '';
    },
    
    async approveRequest() {
      try {
        const token = localStorage.getItem('esemenyter_token');
        const requestId = this.selectedRequest.id;
        const userId = this.selectedRequest.user_id;
        const role = this.selectedRequest.role;

        // Osztályba sorolás
        if (this.selectedClassId) {
          await axios.post(`http://127.0.0.1:8000/api/users/${userId}/assign-class`, {
            class_id: this.selectedClassId,
            establishment_id: this.user.institution_id
          }, {
            headers: { Authorization: `Bearer ${token}` }
          });
        }

        // Kérelem törlése (ez jelzi, hogy elfogadták)
        await axios.delete(`http://127.0.0.1:8000/api/establishment-requests/${requestId}`, {
          headers: { Authorization: `Bearer ${token}` }
        });

        // Frissítsük a felhasználó szerepkörét az adatbázisban
        await axios.put(`http://127.0.0.1:8000/api/users/${userId}/role`, {
          role: role
        }, {
          headers: { Authorization: `Bearer ${token}` }
        });

        // Eltávolítjuk a listából
        const index = this.establishmentRequests.findIndex(r => r.id === requestId);
        if (index !== -1) {
          this.establishmentRequests.splice(index, 1);
        }

        // Felhasználók újratöltése
        await this.loadInstitutionUsers(this.user.institution_id);

        this.closeAssignmentModal();
        this.showNotification('Kérelem sikeresen elfogadva és a felhasználó osztályba sorolva', 'success');

      } catch (error) {
        console.error('Hiba a kérelem elfogadásakor:', error);
        this.errorMessage = error.response?.data?.message || 'Hiba történt a kérelem feldolgozása során';
        this.showNotification(this.errorMessage, 'error');
      }
    },
    
    async rejectRequest(request) {
      if (!confirm(`Biztosan elutasítja ${this.getUserById(request.user_id)?.name || 'a felhasználó'} csatlakozási kérelmét?`)) {
        return;
      }
      
      try {
        const token = localStorage.getItem('esemenyter_token');
        
        // Kérelem törlése (elutasítás = törlés)
        await axios.delete(`http://127.0.0.1:8000/api/establishment-requests/${request.id}`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        
        // Eltávolítjuk a listából
        const index = this.establishmentRequests.findIndex(r => r.id === request.id);
        if (index !== -1) {
          this.establishmentRequests.splice(index, 1);
        }
        
        this.showNotification('Kérelem elutasítva', 'warning');
        
      } catch (error) {
        console.error('Hiba a kérelem elutasításakor:', error);
        this.showNotification('Hiba történt a művelet során', 'error');
      }
    },
    
    // Felhasználó műveletek
    editUserClass(user) {
      console.log('Edit user class:', user);
      this.showNotification('Osztály módosítás funkció fejlesztés alatt', 'info');
    },
    
    editTeacherClasses(teacher) {
      console.log('Edit teacher classes:', teacher);
      this.showNotification('Tanított osztályok módosítása fejlesztés alatt', 'info');
    },
    
    viewUserDetails(user) {
      console.log('User details:', user);
      this.showNotification('Részletes nézet fejlesztés alatt', 'info');
    },
    
    // Értesítés megjelenítése
    showNotification(message, type = 'success') {
      this.toastMessage = message;
      this.toastType = type;
      this.showToast = true;
      
      setTimeout(() => {
        this.showToast = false;
      }, 3000);
    },
    
    // Scroll kezelés
    scrollToTop() {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    
    handleScroll() {
      this.showScrollTop = window.scrollY > 300;
    },
    
    async logout() {
      try {
        const token = localStorage.getItem('esemenyter_token');
        await axios.post('http://127.0.0.1:8000/api/logout', {}, {
          headers: { Authorization: `Bearer ${token}` }
        });
      } catch (error) {
        console.error('Logout hiba:', error);
      } finally {
        localStorage.removeItem('esemenyter_user');
        localStorage.removeItem('esemenyter_token');
        this.$router.push('/');
      }
    },
    
    checkLoginStatus() {
      const savedUser = localStorage.getItem('esemenyter_user');
      if (savedUser) {
        const userData = JSON.parse(savedUser);
        if (userData.isLoggedIn) {
          this.user = { ...this.user, ...userData };
          
          // Ellenőrizzük, hogy intézményvezető-e
          if (userData.role !== 'institution_manager' && userData.role !== 'admin') {
            this.$router.push('/dashboard');
            return;
          }
          
          this.loadInstitutionData();
        } else {
          this.$router.push('/');
        }
      } else {
        this.$router.push('/');
      }
    }
  },
  
  mounted() {
    this.checkLoginStatus();
    window.addEventListener('scroll', this.handleScroll);
    
    document.addEventListener('click', (e) => {
      if (!e.target.closest('.user-profile')) {
        this.showUserMenu = false;
      }
    });
  },
  
  beforeUnmount() {
    window.removeEventListener('scroll', this.handleScroll);
    window.onbeforeunload = null;
  }
};
</script>

<style scoped>
.institution-dashboard {
  box-sizing: border-box;
  font-family: "Poppins", sans-serif;
  min-height: 100vh;
  width: 100vw;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.container {
  max-width: 1400px;
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

/* Logo disabled állapot */
.logo-section.disabled {
  opacity: 0.6;
  cursor: not-allowed;
  pointer-events: none;
}

.logo-section.disabled:hover {
  opacity: 0.6;
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

/* Intézmény info kártya */
.institution-info-card {
  background: white;
  border-radius: 24px;
  padding: 30px;
  display: flex;
  align-items: center;
  gap: 30px;
  margin-bottom: 30px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.institution-icon {
  width: 80px;
  height: 80px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 40px;
}

.institution-details h2 {
  font-size: 28px;
  margin-bottom: 8px;
  color: #111827;
}

.institution-address {
  color: #6b7280;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 6px;
}

.institution-stats {
  display: flex;
  gap: 30px;
}

.stat-item {
  text-align: center;
}

.stat-value {
  display: block;
  font-size: 24px;
  font-weight: 700;
  color: #4f46e5;
}

.stat-label {
  font-size: 14px;
  color: #6b7280;
}

/* Szekció fejléc */
.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.section-header h3 {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 24px;
  color: #111827;
  margin: 0;
}

.section-header h3 i {
  color: #4f46e5;
}

.header-actions {
  display: flex;
  gap: 16px;
}

/* Kereső */
.search-wrapper {
  position: relative;
  min-width: 300px;
}

.search-wrapper i {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
  font-size: 20px;
}

.search-input {
  width: 100%;
  padding: 12px 16px 12px 48px;
  border: 2px solid #e5e7eb;
  border-radius: 50px;
  font-size: 14px;
  transition: all 0.3s ease;
  background: white;
}

.search-input:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

/* Kérelmek szekció */
.requests-section {
  background: white;
  border-radius: 24px;
  padding: 30px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

/* Request tabok */
.request-tabs {
  display: flex;
  gap: 10px;
  margin-bottom: 30px;
  border-bottom: 2px solid #e5e7eb;
  padding-bottom: 10px;
}

.request-tab {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  background: none;
  border: none;
  border-radius: 8px;
  color: #6b7280;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.request-tab i {
  font-size: 20px;
}

.request-tab:hover {
  background: #f3f4f6;
  color: #4f46e5;
}

.request-tab.active {
  background: #4f46e5;
  color: white;
}

.requests-group {
  margin-bottom: 40px;
}

.group-title {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 18px;
  color: #374151;
  margin-bottom: 20px;
  padding-bottom: 10px;
  border-bottom: 2px solid #e5e7eb;
}

.status-badge {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  display: inline-block;
}

.status-badge.pending {
  background: #f59e0b;
  box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2);
}

.requests-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 20px;
}

.request-card {
  background: #f8f9ff;
  border-radius: 16px;
  padding: 20px;
  border: 2px solid transparent;
  transition: all 0.3s ease;
}

.request-card.pending {
  border-left: 4px solid #f59e0b;
}

.request-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
  border-color: #4f46e5;
}

.request-header {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-bottom: 15px;
}

.user-avatar-medium {
  width: 56px;
  height: 56px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 20px;
}

.user-info h4 {
  margin: 0 0 5px 0;
  font-size: 18px;
  color: #111827;
}

.user-email {
  margin: 0;
  font-size: 14px;
  color: #6b7280;
}

.request-body {
  margin-bottom: 20px;
  padding: 15px 0;
  border-top: 1px solid #e5e7eb;
  border-bottom: 1px solid #e5e7eb;
}

.info-row {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 5px 0;
  color: #4b5563;
  font-size: 14px;
}

.info-row i {
  color: #4f46e5;
  font-size: 18px;
}

.request-actions {
  display: flex;
  gap: 12px;
}

.btn-approve {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-approve:hover {
  background: #059669;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-reject {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px;
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-reject:hover {
  background: #dc2626;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

/* Osztályok szekció */
.classes-section {
  background: white;
  border-radius: 24px;
  padding: 30px;
  margin: 40px 0;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
  border: 2px solid transparent;
  transition: all 0.3s ease;
}

.classes-section.warning {
  border-color: #f59e0b;
  background: #fffbeb;
}

.classes-section.completed {
  border-color: #10b981;
  background: #f0fdf4;
}

.warning-message {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: #fef3c7;
  border-radius: 50px;
  color: #92400e;
  font-size: 14px;
  font-weight: 500;
}

.warning-message i {
  font-size: 18px;
  color: #f59e0b;
}

/* Sikeres állapot üzenet */
.success-message {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: #d1fae5;
  border-radius: 50px;
  color: #065f46;
  font-size: 14px;
  font-weight: 500;
}

.success-message i {
  font-size: 18px;
  color: #10b981;
}

.create-class-form {
  background: #f8f9ff;
  border-radius: 16px;
  padding: 24px;
  margin-bottom: 30px;
}

.create-class-form h4 {
  margin: 0 0 20px 0;
  color: #4f46e5;
  font-size: 18px;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  align-items: end;
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
  font-style: italic;
}

.classes-list {
  margin-top: 30px;
}

.classes-list h4 {
  margin: 0 0 20px 0;
  color: #374151;
  font-size: 18px;
}

.classes-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.class-item {
  background: #f8f9ff;
  border-radius: 12px;
  padding: 20px;
  border: 1px solid #e5e7eb;
  transition: all 0.3s ease;
}

.class-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.05);
  border-color: #4f46e5;
}

.class-item-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
  padding-bottom: 10px;
  border-bottom: 1px solid #e5e7eb;
}

.class-item-header h5 {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
  color: #4f46e5;
}

.class-grade {
  font-size: 14px;
  color: #6b7280;
  background: #e0e7ff;
  padding: 4px 8px;
  border-radius: 20px;
}

.class-item-body {
  margin-bottom: 15px;
}

.class-stat {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 5px 0;
  color: #4b5563;
  font-size: 14px;
}

.class-stat i {
  color: #4f46e5;
  font-size: 18px;
}

.class-item-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  border-top: 1px solid #e5e7eb;
  padding-top: 15px;
}

.blocking-warning {
  margin-top: 30px;
  padding: 24px;
  background: #fef3c7;
  border-radius: 16px;
  display: flex;
  align-items: center;
  gap: 20px;
  border: 2px solid #f59e0b;
}

.blocking-warning i {
  font-size: 48px;
  color: #f59e0b;
}

.warning-content h4 {
  margin: 0 0 10px 0;
  color: #92400e;
  font-size: 20px;
}

.warning-content p {
  margin: 0;
  color: #92400e;
  opacity: 0.9;
  line-height: 1.5;
}

/* Sikeres véglegesítés info */
.success-info {
  margin-top: 30px;
  padding: 30px;
  background: #d1fae5;
  border-radius: 16px;
  display: flex;
  align-items: flex-start;
  gap: 20px;
  border: 2px solid #10b981;
}

.success-info i {
  font-size: 48px;
  color: #10b981;
  flex-shrink: 0;
}

.success-content h4 {
  margin: 0 0 15px 0;
  color: #065f46;
  font-size: 22px;
}

.success-content p {
  margin: 0 0 15px 0;
  color: #065f46;
  line-height: 1.5;
}

.success-content ul {
  margin: 0;
  padding: 0;
  list-style: none;
}

.success-content li {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 0;
  color: #065f46;
  font-size: 16px;
}

.success-content li i {
  font-size: 20px;
  color: #10b981;
}

/* Empty state */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #6b7280;
}

.empty-state i {
  font-size: 64px;
  color: #9ca3af;
  margin-bottom: 20px;
}

.empty-state h4 {
  font-size: 20px;
  margin-bottom: 10px;
  color: #374151;
}

.empty-state p {
  font-size: 16px;
  color: #6b7280;
}

.empty-state.small {
  padding: 40px;
}

.empty-state.small i {
  font-size: 48px;
}

/* Csatlakozott felhasználók szekció */
.connected-users-section {
  background: white;
  border-radius: 24px;
  padding: 30px;
  margin-top: 40px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.user-tabs {
  display: flex;
  gap: 10px;
  margin-bottom: 30px;
  border-bottom: 2px solid #e5e7eb;
  padding-bottom: 10px;
}

.user-tab {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  background: none;
  border: none;
  border-radius: 8px;
  color: #6b7280;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.user-tab i {
  font-size: 20px;
}

.user-tab:hover {
  background: #f3f4f6;
  color: #4f46e5;
}

.user-tab.active {
  background: #4f46e5;
  color: white;
}

.users-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 20px;
}

.user-card {
  background: #f8f9ff;
  border-radius: 16px;
  padding: 20px;
  border: 1px solid #e5e7eb;
  transition: all 0.3s ease;
}

.user-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
  border-color: #4f46e5;
}

.user-card-header {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-bottom: 15px;
}

.user-avatar-small {
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
}

.user-card-info h4 {
  margin: 0 0 5px 0;
  font-size: 16px;
  color: #111827;
}

.user-email {
  margin: 0;
  font-size: 14px;
  color: #6b7280;
}

.user-card-body {
  margin-bottom: 15px;
  padding: 10px 0;
  border-top: 1px solid #e5e7eb;
  border-bottom: 1px solid #e5e7eb;
}

.user-card-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.btn-icon {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: white;
  border: 1px solid #e5e7eb;
  color: #6b7280;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-icon:hover {
  background: #4f46e5;
  color: white;
  border-color: #4f46e5;
}

/* Modal stílusok */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(5px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
}

.modal-container {
  background: white;
  border-radius: 24px;
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
}

.modal-header {
  padding: 20px 30px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.modal-header h3 {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 0;
  color: #111827;
}

.modal-close {
  background: none;
  border: none;
  font-size: 24px;
  color: #6b7280;
  cursor: pointer;
  transition: color 0.3s;
}

.modal-close:hover {
  color: #ef4444;
}

.modal-body {
  padding: 30px;
}

.modal-footer {
  padding: 20px 30px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 15px;
}

.user-summary {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 30px;
  padding-bottom: 20px;
  border-bottom: 1px solid #e5e7eb;
}

.user-avatar-large {
  width: 64px;
  height: 64px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 24px;
}

.user-summary-info h4 {
  margin: 0 0 5px 0;
  font-size: 18px;
}

.user-summary-info p {
  margin: 0 0 8px 0;
  color: #6b7280;
}

.role-badge-small {
  display: inline-block;
  padding: 4px 12px;
  background: #e0e7ff;
  color: #4f46e5;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

/* Form elemek a modalban */
.assignment-form .form-group {
  margin-bottom: 20px;
}

.assignment-form .form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #374151;
}

.form-select {
  width: 100%;
  padding: 12px 16px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 16px;
  background: white;
  cursor: pointer;
  transition: all 0.3s ease;
}

.form-select:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.form-select:disabled {
  background: #f3f4f6;
  cursor: not-allowed;
}

.form-hint {
  margin-top: 8px;
  font-size: 14px;
  color: #6b7280;
  display: flex;
  align-items: center;
  gap: 5px;
}

.form-hint i {
  color: #4f46e5;
}

/* Gombok */
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

.btn-primary {
  display: flex;
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
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
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

/* Modal animáció */
.modal-enter-active,
.modal-leave-active {
  transition: all 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: scale(0.9);
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

/* Reszponzív design */
@media (max-width: 1024px) {
  .institution-info-card {
    flex-direction: column;
    text-align: center;
  }
  
  .institution-stats {
    justify-content: center;
  }
  
  .requests-grid {
    grid-template-columns: 1fr;
  }
  
  .users-grid {
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  }
  
  .section-header {
    flex-direction: column;
    gap: 16px;
  }
  
  .header-actions {
    width: 100%;
  }
  
  .search-wrapper {
    width: 100%;
    min-width: auto;
  }
  
  .form-row {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .main-content {
    padding: 20px 0;
  }
  
  .institution-info-card {
    padding: 20px;
  }
  
  .institution-icon {
    width: 60px;
    height: 60px;
    font-size: 30px;
  }
  
  .institution-details h2 {
    font-size: 24px;
  }
  
  .institution-stats {
    flex-wrap: wrap;
    gap: 20px;
  }
  
  .stat-item {
    flex: 1 1 calc(50% - 20px);
  }
  
  .user-menu {
    width: 280px;
    right: -20px;
  }
  
  .requests-section,
  .connected-users-section,
  .classes-section {
    padding: 20px;
  }
  
  .request-tabs {
    flex-direction: column;
  }
  
  .request-tab {
    width: 100%;
    justify-content: center;
  }
  
  .user-tabs {
    flex-direction: column;
  }
  
  .user-tab {
    width: 100%;
    justify-content: center;
  }
  
  .users-grid {
    grid-template-columns: 1fr;
  }
  
  .classes-grid {
    grid-template-columns: 1fr;
  }
  
  .request-card {
    padding: 15px;
  }
  
  .user-avatar-medium {
    width: 48px;
    height: 48px;
    font-size: 18px;
  }
  
  .user-info h4 {
    font-size: 16px;
  }
  
  .modal-container {
    width: 95%;
  }
  
  .modal-header,
  .modal-body,
  .modal-footer {
    padding: 20px;
  }
  
  .user-summary {
    flex-direction: column;
    text-align: center;
  }
  
  .blocking-warning {
    flex-direction: column;
    text-align: center;
  }
  
  .success-info {
    flex-direction: column;
    text-align: center;
    padding: 20px;
  }
  
  .success-content ul {
    text-align: left;
  }
  
  .toast-notification {
    left: 20px;
    right: 20px;
    min-width: auto;
    bottom: 20px;
  }
  
  .fab {
    width: 48px;
    height: 48px;
    font-size: 20px;
    bottom: 20px;
    right: 20px;
  }
}

@media (max-width: 480px) {
  .container {
    padding: 0 15px;
  }
  
  .logo-text h1 {
    font-size: 20px;
  }
  
  .site-subtitle {
    font-size: 12px;
  }
  
  .avatar-circle {
    width: 40px;
    height: 40px;
    font-size: 14px;
  }
  
  .institution-stats {
    gap: 15px;
  }
  
  .stat-value {
    font-size: 20px;
  }
  
  .stat-label {
    font-size: 12px;
  }
  
  .section-header h3 {
    font-size: 20px;
  }
  
  .group-title {
    font-size: 16px;
  }
  
  .request-actions {
    flex-direction: column;
  }
  
  .btn-approve,
  .btn-reject {
    width: 100%;
  }
  
  .user-card-actions {
    justify-content: center;
  }
  
  .class-item-actions {
    justify-content: center;
  }
  
  .modal-footer {
    flex-direction: column;
  }
  
  .modal-footer button {
    width: 100%;
    justify-content: center;
  }
  
  .empty-state i {
    font-size: 48px;
  }
  
  .empty-state h4 {
    font-size: 18px;
  }
  
  .empty-state p {
    font-size: 14px;
  }
  
  .blocking-warning {
    padding: 16px;
  }
  
  .blocking-warning i {
    font-size: 36px;
  }
  
  .warning-content h4 {
    font-size: 16px;
  }
  
  .warning-content p {
    font-size: 14px;
  }
  
  .success-info i {
    font-size: 36px;
  }
  
  .success-content h4 {
    font-size: 18px;
  }
  
  .success-content li {
    font-size: 14px;
  }
}
</style>