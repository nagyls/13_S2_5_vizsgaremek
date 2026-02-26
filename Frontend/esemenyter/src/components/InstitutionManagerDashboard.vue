<template>
  <div class="institution-dashboard">
    <header class="main-header">
      <div class="container">
        <div class="header-content">
          <div class="logo-section" @click="$router.push('/user-dashboard')">
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
                  <div class="role-badge institution">
                    Intézményvezető
                  </div>
                </div>
                <div class="menu-items">
                  <router-link to="/profile" class="menu-item">
                    <i class='bx bx-user'></i>
                    <span>Profilom</span>
                  </router-link>
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

        <!-- CSATLAKOZÁSI KÉRELMEK SZEKCIÓ -->
        <div class="requests-section">
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

        <!-- OSZTÁLYOK LÉTREHOZÁSA SZEKCIÓ -->
        <div class="classes-section">
          <div class="section-header">
            <h3>
              <i class='bx bx-group'></i>
              Osztályok kezelése
            </h3>
          </div>

          
          <!-- Osztály létrehozó űrlap -->
          <div class="create-class-form">
            <h4>Új osztály létrehozása</h4>
            
            <div class="form-row">
              <div class="form-group">
            <label for="classGrade">Évfolyam</label>
            <select 
              id="classGrade"
              v-model="newClass.grade"
              class="form-control"
            >
              <option value="">Válassz évfolyamot</option>
              <option v-for="grade in availableGrades" :key="grade" :value="grade">
                {{ grade }}. évfolyam
              </option>
            </select>
          </div>
              <div class="form-group">
                <label for="className">Osztály</label>
                <input 
                  type="text" 
                  id="className"
                  v-model="newClass.name"
                  placeholder="Pl.: A, B, C"
                  class="form-control"
                />
              </div>

              

              <div class="form-group">
                <label for="classCapacity">Férőhely</label>
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
                <label for="classTeacher">Osztályfőnök (választható)</label>
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
        </div>

        <!-- Már csatlakozott felhasználók -->
        <div class="connected-users-section">
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

    <!-- Osztály hozzárendelés modal -->
    <transition name="modal">
      <div v-if="showAssignmentModal" class="modal-overlay" @click.self="closeAssignmentModal">
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
                <label for="class-select">Válassz osztályt (opcionális):</label>
                <select 
                  id="class-select"
                  v-model="selectedClassId" 
                  class="form-select"
                >
                  <option value="">-- Osztály nélkül --</option>
                  <option 
                    v-for="classItem in classes" 
                    :key="classItem.id" 
                    :value="classItem.id"
                  >
                    {{ classItem.name }} 
                    ({{ classItem.student_count || 0 }}/{{ classItem.capacity || '∞' }} diák)
                  </option>
                </select>
                <p class="form-hint">
                  <i class='bx bx-info-circle'></i>
                  Választhatsz osztályt a felhasználónak, de nem kötelező. Később is módosítható.
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
      availableGrades: [9, 10, 11, 12, 13]
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

        // DIÁK csatlakozási kérelmek betöltése
        const studentRequestsResponse = await axios.get(`http://127.0.0.1:8000/api/requests/student/${institutionId}`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        const studentRequests = studentRequestsResponse.data.data || [];

        // TANÁR csatlakozási kérelmek betöltése
        const teacherRequestsResponse = await axios.get(`http://127.0.0.1:8000/api/requests/teacher/${institutionId}`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        const teacherRequests = teacherRequestsResponse.data.data || [];

        // Kérelmek egyesítése
        this.establishmentRequests = [...studentRequests, ...teacherRequests];

        // Összes felhasználó betöltése (a kérelmekhez kapcsolódóan)
        const userIds = this.establishmentRequests.map(r => r.user_id);
        if (userIds.length > 0) {
          await this.loadUsers(userIds);
        }

        // Diákok és tanárok betöltése (akik már csatlakoztak)
        await this.loadInstitutionUsers(institutionId);

        // Osztályok betöltése - JAVÍTVA: classes/{establishment}
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
    
    async loadInstitutionUsers(institutionId) {
      try {
        const token = localStorage.getItem('esemenyter_token');
        
        // Token küldése, de a backend nem ellenőrzi
        const studentsResponse = await axios.get(`http://127.0.0.1:8000/api/establishments/student/${institutionId}`, {
          headers: token ? { Authorization: `Bearer ${token}` } : {}
        });
        this.students = studentsResponse.data.data || [];
      
        const teachersResponse = await axios.get(`http://127.0.0.1:8000/api/establishments/teacher/${institutionId}`, {
          headers: token ? { Authorization: `Bearer ${token}` } : {}
        });
        this.teachers = teachersResponse.data.data || [];
      
      } catch (error) {
        console.error('Hiba az intézmény felhasználóinak betöltésekor:', error);
      }
    },
    
    // Osztályok betöltése intézmény ID alapján
    async loadClasses(institutionId) {
      try {
        const token = localStorage.getItem('esemenyter_token');

        const response = await axios.get(`http://127.0.0.1:8000/api/classes/${institutionId}`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        this.classes = response.data.data || [];

      } catch (error) {
        console.error('Hiba az osztályok betöltésekor:', error);
        this.showNotification('Hiba történt az osztályok betöltésekor', 'error');
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

      // Validáció - csak akkor ellenőrizzük, ha van kitöltve
      if (!this.newClass.name?.trim() && !this.newClass.grade && !this.newClass.capacity) {
        // Ha egy mező sincs kitöltve, ne csináljunk semmit
        return;
      }

      // Ha van név, de nincs évfolyam
      if (this.newClass.name?.trim() && !this.newClass.grade) {
        this.classErrors.grade = 'Az évfolyam kötelező, ha osztálynevet adsz meg';
        return;
      }

      this.isCreatingClass = true;

      try {
        const token = localStorage.getItem('esemenyter_token');

        const classData = {
          name: this.newClass.name || 'Új osztály',
          grade: this.newClass.grade || 1,
          capacity: this.newClass.capacity || null,
          teacher_id: this.newClass.teacher_id || null,
          establishment_id: this.user.institution_id
        };

        const response = await axios.post(`http://127.0.0.1:8000/api/establishment/classes/create`, classData, {
          headers: { Authorization: `Bearer ${token}`, Accept: 'application/json', 'Content-Type': 'application/json' }
        });

        // Újratöltjük az osztályokat
        await this.loadClasses(this.user.institution_id);

        // Űrlap alaphelyzetbe
        this.newClass = {
          name: '',
          grade: '',
          capacity: 30,
          teacher_id: ''
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
      // Ellenőrizzük, hogy van-e user adat
      const user = this.getUserById(request.user_id);
      if (user) {
        request.user = user;
      }
      
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
      
        // Csak akkor rendelünk osztályt, ha van kiválasztva
        if (this.selectedClassId) {
          await axios.post(`http://127.0.0.1:8000/api/users/${userId}/assign-class`, {
            class_id: this.selectedClassId,
            establishment_id: this.user.institution_id
          }, {
            headers: { Authorization: `Bearer ${token}` }
          });
        }
      
        // Kérelem törlése (elfogadás után)
        await axios.delete(`http://127.0.0.1:8000/api/establishment-requests/${requestId}`, {
          headers: { Authorization: `Bearer ${token}` }
        });
      
        // Felhasználó szerepkörének frissítése
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
        this.showNotification('Kérelem sikeresen elfogadva!', 'success');
      
      } catch (error) {
        console.error('Hiba a kérelem elfogadásakor:', error);
        this.errorMessage = error.response?.data?.message || 'Hiba történt a kérelem feldolgozása során';
        this.showNotification(this.errorMessage, 'error');
      }
    },
    
    async rejectRequest(request) {
      const user = this.getUserById(request.user_id);
      if (!confirm(`Biztosan elutasítja ${user?.name || 'a felhasználó'} csatlakozási kérelmét?`)) {
        return;
      }

      try {
        const token = localStorage.getItem('esemenyter_token');

        // Kérelem törlése - a megfelelő endpoint használata
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
        await axios.delete('http://127.0.0.1:8000/api/logout', {}, {
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
  }
};
</script>

<style scoped>
/* Alap stílusok */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;

}

.institution-dashboard {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  width: 100vw;
}

.container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 30px;
}

/* Header stílusok */
.main-header {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 1000;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 0;
}

.logo-section {
  display: flex;
  align-items: center;
  gap: 15px;
  cursor: pointer;
  transition: transform 0.3s ease;
}

.logo-section:hover {
  transform: scale(1.02);
}

.logo-icon {
  width: 50px;
  height: 50px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 28px;
  box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
}

.logo-text {
  line-height: 1.2;
}

.site-title {
  font-size: 24px;
  font-weight: 700;
  background: linear-gradient(135deg, #667eea, #764ba2);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  margin: 0;
}

.site-subtitle {
  font-size: 12px;
  color: #6b7280;
  margin: 0;
}

/* User profile */
.user-profile {
  position: relative;
}

.user-avatar {
  display: flex;
  align-items: center;
  gap: 15px;
  cursor: pointer;
  padding: 5px 10px;
  border-radius: 50px;
  transition: background 0.3s ease;
}

.user-avatar:hover {
  background: #f3f4f6;
}

.avatar-circle {
  width: 45px;
  height: 45px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 18px;
  box-shadow: 0 4px 10px rgba(102, 126, 234, 0.3);
}

.user-status {
  position: relative;
}

.status-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  border: 2px solid white;
  position: absolute;
  bottom: 2px;
  right: 2px;
}

.status-dot.online {
  background: #10b981;
  box-shadow: 0 0 0 2px white;
}

/* User menu */
.user-menu {
  position: absolute;
  top: 60px;
  right: 0;
  width: 280px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  z-index: 1000;
}

.menu-header {
  padding: 20px;
  background: linear-gradient(135deg, #667eea10, #764ba210);
  border-bottom: 1px solid #e5e7eb;
}

.menu-user-info h4 {
  margin: 0 0 5px 0;
  color: #374151;
  font-size: 16px;
}

.user-email {
  margin: 0;
  color: #6b7280;
  font-size: 14px;
}

.role-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 50px;
  font-size: 12px;
  font-weight: 600;
  margin-top: 10px;
}

.role-badge.institution {
  background: #4f46e5;
  color: white;
}

.menu-items {
  padding: 10px;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-radius: 10px;
  color: #374151;
  text-decoration: none;
  transition: all 0.3s ease;
  width: 100%;
  border: none;
  background: none;
  font-size: 14px;
  cursor: pointer;
}

.menu-item i {
  font-size: 20px;
  color: #6b7280;
  transition: color 0.3s ease;
}

.menu-item:hover {
  background: #f3f4f6;
  color: #4f46e5;
}

.menu-item:hover i {
  color: #4f46e5;
}

.menu-divider {
  height: 1px;
  background: #e5e7eb;
  margin: 8px 0;
}

.logout-btn {
  color: #ef4444;
}

.logout-btn i {
  color: #ef4444;
}

.logout-btn:hover {
  background: #fee2e2;
}

/* Slide-fade animáció */
.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
  transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1);
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateY(-10px);
  opacity: 0;
}

/* Main content */
.main-content {
  padding: 40px 0;
}

/* Intézmény info card */
.institution-info-card {
  background: white;
  border-radius: 24px;
  padding: 30px;
  margin-bottom: 30px;
  display: flex;
  align-items: center;
  gap: 30px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.institution-icon {
  width: 80px;
  height: 80px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 40px;
  box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
}

.institution-details {
  flex: 1;
}

.institution-details h2 {
  margin: 0 0 10px 0;
  color: #374151;
  font-size: 28px;
}

.institution-address {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #6b7280;
  margin: 0 0 20px 0;
  font-size: 16px;
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
  font-size: 28px;
  font-weight: 700;
  color: #4f46e5;
  line-height: 1.2;
}

.stat-label {
  font-size: 14px;
  color: #6b7280;
}

/* Section headers */
.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
}

.section-header h3 {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #374151;
  font-size: 22px;
  margin: 0;
}

.section-header h3 i {
  color: #4f46e5;
  font-size: 26px;
}

/* Search */
.header-actions {
  display: flex;
  gap: 15px;
}

.search-wrapper {
  position: relative;
}

.search-wrapper i {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
  font-size: 18px;
}

.search-input {
  padding: 10px 15px 10px 40px;
  border: 2px solid #e5e7eb;
  border-radius: 50px;
  font-size: 14px;
  width: 250px;
  transition: all 0.3s ease;
  background: white;
}

.search-input:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
  width: 300px;
}

/* Request tabs */
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

/* Request cards */
.requests-section {
  background: white;
  border-radius: 24px;
  padding: 30px;
  margin: 30px 0;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.requests-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 20px;
}

.request-card {
  background: #f8f9ff;
  border-radius: 16px;
  padding: 20px;
  border: 1px solid #e5e7eb;
  transition: all 0.3s ease;
}

.request-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
  border-color: #4f46e5;
}

.request-card.pending {
  border-left: 4px solid #f59e0b;
}

.request-header {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-bottom: 15px;
}

.user-avatar-medium {
  width: 50px;
  height: 50px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 18px;
}

.user-info {
  flex: 1;
}

.user-info h4 {
  margin: 0 0 5px 0;
  color: #374151;
  font-size: 16px;
}

.user-info .user-email {
  font-size: 13px;
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
  gap: 8px;
  color: #4b5563;
  font-size: 14px;
  margin-bottom: 8px;
}

.info-row i {
  color: #4f46e5;
  font-size: 16px;
}

.request-actions {
  display: flex;
  gap: 10px;
}

.btn-approve, .btn-reject {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-approve {
  background: #10b981;
  color: white;
}

.btn-approve:hover:not(:disabled) {
  background: #059669;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-reject {
  background: #ef4444;
  color: white;
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
  transition: all 0.3s ease;
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
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-size: 14px;
  font-weight: 600;
  color: #374151;
}

.form-control {
  padding: 10px 12px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.3s ease;
  background: white;
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
  color: #ef4444;
  font-size: 12px;
  margin-top: 4px;
}

.form-select {
  padding: 10px 12px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  background: white;
  cursor: pointer;
}

.form-hint {
  margin: 8px 0 0 0;
  font-size: 12px;
  color: #6b7280;
  display: flex;
  align-items: center;
  gap: 4px;
}

.form-hint i {
  color: #4f46e5;
  font-size: 14px;
}

.btn-primary {
  padding: 12px 24px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
  white-space: nowrap;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-outline {
  padding: 10px 20px;
  background: white;
  color: #374151;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-outline:hover {
  background: #f3f4f6;
  border-color: #4f46e5;
  color: #4f46e5;
}

.btn-icon {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  background: white;
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
  transform: translateY(-2px);
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

/* Connected users section */
.connected-users-section {
  background: white;
  border-radius: 24px;
  padding: 30px;
  margin: 30px 0;
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
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.user-card {
  background: #f8f9ff;
  border-radius: 12px;
  padding: 20px;
  border: 1px solid #e5e7eb;
  transition: all 0.3s ease;
}

.user-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.05);
  border-color: #4f46e5;
}

.user-card-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 15px;
  padding-bottom: 10px;
  border-bottom: 1px solid #e5e7eb;
}

.user-avatar-small {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 16px;
}

.user-card-info {
  flex: 1;
}

.user-card-info h4 {
  margin: 0 0 4px 0;
  font-size: 15px;
  color: #374151;
}

.user-card-info .user-email {
  font-size: 12px;
  color: #6b7280;
  margin: 0;
}

.user-card-body {
  margin-bottom: 15px;
  min-height: 60px;
}

.user-card-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  border-top: 1px solid #e5e7eb;
  padding-top: 15px;
}

/* Empty states */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  background: #f8f9ff;
  border-radius: 16px;
  color: #6b7280;
}

.empty-state i {
  font-size: 60px;
  color: #4f46e5;
  margin-bottom: 20px;
  opacity: 0.5;
}

.empty-state h4 {
  margin: 0 0 10px 0;
  color: #374151;
  font-size: 20px;
}

.empty-state p {
  margin: 0;
  color: #6b7280;
}

.empty-state.small {
  padding: 40px 20px;
}

.empty-state.small i {
  font-size: 40px;
}

/* Modal */
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
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
}

.modal-header {
  padding: 20px 30px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: linear-gradient(135deg, #667eea10, #764ba210);
}

.modal-header h3 {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 0;
  color: #374151;
  font-size: 20px;
}

.modal-header h3 i {
  color: #4f46e5;
}

.modal-close {
  background: none;
  border: none;
  font-size: 24px;
  color: #6b7280;
  cursor: pointer;
  padding: 5px;
  border-radius: 8px;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-close:hover {
  background: #f3f4f6;
  color: #4f46e5;
}

.modal-body {
  padding: 30px;
}

.user-summary {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 30px;
  padding: 20px;
  background: #f8f9ff;
  border-radius: 16px;
}

.user-avatar-large {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 24px;
}

.user-summary-info h4 {
  margin: 0 0 5px 0;
  color: #374151;
  font-size: 18px;
}

.user-summary-info p {
  margin: 0 0 8px 0;
  color: #6b7280;
  font-size: 14px;
}

.role-badge-small {
  display: inline-block;
  padding: 2px 10px;
  background: #4f46e5;
  color: white;
  border-radius: 50px;
  font-size: 12px;
  font-weight: 600;
}

.assignment-form {
  margin-top: 20px;
}

.assignment-form .form-group {
  margin-bottom: 20px;
}

.modal-footer {
  padding: 20px 30px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 15px;
  background: #f8f9ff;
}

/* Modal animáció */
.modal-enter-active,
.modal-leave-active {
  transition: all 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: scale(0.95);
}

/* Toast értesítések */
.toast-notification {
  position: fixed;
  bottom: 30px;
  right: 30px;
  min-width: 300px;
  padding: 16px 24px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
  display: flex;
  align-items: center;
  gap: 12px;
  z-index: 3000;
  animation: slideIn 0.3s ease;
}

.toast-notification.success {
  border-left: 4px solid #10b981;
}

.toast-notification.error {
  border-left: 4px solid #ef4444;
}

.toast-notification.warning {
  border-left: 4px solid #f59e0b;
}

.toast-notification.info {
  border-left: 4px solid #4f46e5;
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
  color: #4f46e5;
}

.toast-notification span {
  color: #374151;
  font-size: 14px;
  font-weight: 500;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from,
.toast-leave-to {
  transform: translateX(100%);
  opacity: 0;
}

/* Floating Action Button */
.fab {
  position: fixed;
  bottom: 30px;
  right: 30px;
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  border: none;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  transition: all 0.3s ease;
  z-index: 1000;
}

.fab:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
}

/* Reszponzív design */
@media (max-width: 768px) {
  .container {
    padding: 0 20px;
  }

  .header-content {
    flex-direction: column;
    gap: 15px;
  }

  .institution-info-card {
    flex-direction: column;
    text-align: center;
  }

  .institution-stats {
    flex-wrap: wrap;
    justify-content: center;
  }

  .request-tabs {
    flex-direction: column;
  }
  
  .request-tab {
    width: 100%;
    justify-content: center;
  }
  
  .create-class-form .form-row {
    grid-template-columns: 1fr;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .search-input {
    width: 200px;
  }

  .search-input:focus {
    width: 250px;
  }

  .modal-container {
    width: 95%;
    margin: 20px;
  }

  .toast-notification {
    left: 20px;
    right: 20px;
    min-width: auto;
  }
}

@media (max-width: 480px) {
  .institution-stats {
    gap: 15px;
  }

  .stat-value {
    font-size: 20px;
  }

  .stat-label {
    font-size: 12px;
  }

  .request-card {
    padding: 15px;
  }

  .section-header {
    flex-direction: column;
    gap: 15px;
    align-items: flex-start;
  }

  .header-actions {
    width: 100%;
  }

  .search-wrapper {
    width: 100%;
  }

  .search-input {
    width: 100%;
  }

  .search-input:focus {
    width: 100%;
  }

  .btn-primary {
    width: 100%;
    justify-content: center;
  }
}
</style>