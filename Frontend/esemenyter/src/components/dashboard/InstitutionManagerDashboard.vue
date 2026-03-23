<template>
  <div class="institution-dashboard">
    <header class="main-header">
      <div class="container">
        <div class="header-content">
          <div class="logo-section" @click="$router.push('/user-dashboard')">
            <div class="logo-icon">
              <img :src="logo2" alt="EseményTér logó" class="logo-image">
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
                  <router-link to="/user-dashboard" class="menu-item">
                    <i class='bx bx-building'></i>
                    <span>Főoldal</span>
                  </router-link>
                  <router-link to="/events-list" class="menu-item">
                    <i class='bx bx-calendar-event'></i>
                    <span>Események</span>
                  </router-link>
                  <router-link to="/profile" class="menu-item">
                    <i class='bx bx-user'></i>
                    <span>Profilom</span>
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
              <div class="stat-item">
                <span class="stat-value">{{ pendingGlobalEventRequests }}</span>
                <span class="stat-label">Globális meghívás</span>
              </div>
            </div>
          </div>
        </div>

        <div class="requests-section">
          <div class="section-header">
            <h3>
              <i class='bx bx-world'></i>
              Globális esemény meghívások
            </h3>
          </div>

          <div v-if="isLoadingCollabEvents" class="empty-state">
            <i class='bx bx-loader-circle bx-spin'></i>
            <h4>Betöltés...</h4>
            <p>Globális esemény meghívások lekérése folyamatban.</p>
          </div>

          <div v-else-if="collabEvents.length" class="requests-grid">
            <div
              v-for="eventItem in collabEvents"
              :key="eventItem.id"
              class="request-card pending"
            >
              <div class="request-header">
                <div class="user-avatar-medium">
                  <span>{{ (eventItem.title || 'E')[0]?.toUpperCase() }}</span>
                </div>
                <div class="user-info">
                  <h4>{{ eventItem.title || 'Névtelen esemény' }}</h4>
                  <p class="user-email">{{ formatDate(eventItem.start_date) }} - {{ formatDate(eventItem.end_date) }}</p>
                </div>
              </div>

              <div class="request-body">
                <div class="info-row">
                  <i class='bx bx-detail'></i>
                  <span>{{ eventItem.description || 'Nincs leírás megadva.' }}</span>
                </div>
              </div>

              <div class="request-actions request-actions-stacked">
                <router-link
                  :to="{ path: `/esemenyek/${eventItem.id}`, query: { readonly: '1', source: 'collab-request' } }"
                  class="btn-details btn-details-full"
                >
                  <i class='bx bx-show'></i>
                  <span>Részletek</span>
                </router-link>
                <div class="request-actions-inline">
                  <button
                    class="btn-approve"
                    @click="openCollabTargetModal(eventItem)"
                    :disabled="processingCollabEventId === Number(eventItem.id)"
                  >
                    <i class='bx bx-check'></i>
                    <span>{{ processingCollabEventId === Number(eventItem.id) ? 'Feldolgozás...' : 'Elfogadás' }}</span>
                  </button>
                  <button
                    class="btn-reject"
                    @click="handleCollabEventRequest(eventItem.id, 'reject')"
                    :disabled="processingCollabEventId === Number(eventItem.id)"
                  >
                    <i class='bx bx-x'></i>
                    <span>{{ processingCollabEventId === Number(eventItem.id) ? 'Feldolgozás...' : 'Elutasítás' }}</span>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div v-else class="empty-state">
            <i class='bx bx-inbox'></i>
            <h4>Nincs globális esemény meghívás</h4>
            <p>Ha egy másik intézmény globális eseményre meghív, itt fog megjelenni.</p>
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

          <div v-if="visibleRequests.length > 0" class="bulk-actions-bar">
            <label class="bulk-select-all">
              <input
                type="checkbox"
                :checked="allVisibleRequestsSelected"
                @change="toggleSelectAllVisibleRequests"
              />
              <span>Összes kijelölése</span>
            </label>
            <div class="bulk-actions-right">
              <span class="bulk-selected-count">Kijelölve: {{ selectedVisibleRequestCount }}</span>
              <button
                class="btn-approve bulk-btn"
                @click="bulkApproveSelectedRequests"
                :disabled="!selectedVisibleRequestCount || isBulkProcessingRequests"
              >
                <i class='bx bx-check-double'></i>
                <span>{{ isBulkProcessingRequests ? 'Feldolgozás...' : 'Kijelöltek elfogadása' }}</span>
              </button>
              <button
                class="btn-reject bulk-btn"
                @click="bulkRejectSelectedRequests"
                :disabled="!selectedVisibleRequestCount || isBulkProcessingRequests"
              >
                <i class='bx bx-x-circle'></i>
                <span>{{ isBulkProcessingRequests ? 'Feldolgozás...' : 'Kijelöltek elutasítása' }}</span>
              </button>
            </div>
          </div>

          <!-- Diák kérelmek -->
          <div v-if="activeRequestTab === 'students'" class="requests-group">
            <div v-if="filteredStudentRequests.length > 0" class="requests-grid">
              <div 
                v-for="request in filteredStudentRequests" 
                :key="request.id"
                class="request-card pending"
              >
                <div class="request-select">
                  <input
                    type="checkbox"
                    :checked="isRequestSelected(request)"
                    @change="toggleRequestSelection(request)"
                    :disabled="isBulkProcessingRequests"
                    title="Kérelem kijelölése"
                  />
                </div>
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
                  <button class="btn-approve" @click="showClassAssignmentModal(request)" :disabled="isBulkProcessingRequests">
                    <i class='bx bx-check'></i>
                    <span>Elfogadás</span>
                  </button>
                  <button class="btn-reject" @click="rejectRequest(request)" :disabled="isBulkProcessingRequests">
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
                <div class="request-select">
                  <input
                    type="checkbox"
                    :checked="isRequestSelected(request)"
                    @change="toggleRequestSelection(request)"
                    :disabled="isBulkProcessingRequests"
                    title="Kérelem kijelölése"
                  />
                </div>
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
                  <button class="btn-approve" @click="showClassAssignmentModal(request)" :disabled="isBulkProcessingRequests">
                    <i class='bx bx-check'></i>
                    <span>Elfogadás</span>
                  </button>
                  <button class="btn-reject" @click="rejectRequest(request)" :disabled="isBulkProcessingRequests">
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
            <input 
              id="classGrade"
              v-model.number="newClass.grade"
              type="number"
              min="1"
              max="13"
              placeholder="Pl.: 9"
              class="form-control"
            />
          </div>
              <div class="form-group">
                <label for="className">Osztály</label>
                <input 
                  type="text" 
                  id="className"
                  v-model="newClass.name"
                  @input="sanitizeClassNameInput"
                  placeholder="Pl.: A, B, C"
                  class="form-control"
                />
              </div>

              

              <div class="form-group">
                <label for="classCapacity">Létszám</label>
                <input 
                  type="number" 
                  id="classCapacity"
                  v-model.number="newClass.capacity"
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
                  <h5>{{ formatClassDisplayName(classItem) }}</h5>
                  <span class="class-grade">{{ classItem.grade }}. évfolyam</span>
                </div>
                <div class="class-item-body">
                  <div class="class-stat">
                    <i class='bx bx-group'></i>
                    <span>{{ classItem.student_count || 0 }} / {{ getClassCapacity(classItem) }} diák</span>
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
                  <button
                    class="btn-icon"
                    @click="deleteClass(classItem)"
                    title="Törlés"
                    :disabled="isDeletingClassId === Number(classItem.id)"
                  >
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
                  <span>Osztály: {{ getStudentClassDisplay(student) }}</span>
                </div>
              </div>
              <div class="user-card-actions">
                <button class="btn-icon" @click="editUserClass(student)" title="Osztály módosítása">
                  <i class='bx bx-edit'></i>
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
                  <span>Tanított osztályok: {{ getTeacherClassesDisplay(teacher) }}</span>
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
                <label for="class-select">
                  Válassz osztályt (opcionális):
                </label>
                <select 
                  id="class-select"
                  v-model="selectedClassId" 
                  class="form-select"
                >
                  <option value="">-- Osztály kiválasztása --</option>
                  <option 
                    v-for="classItem in classes" 
                    :key="classItem.id" 
                    :value="classItem.id"
                  >
                    {{ formatClassDisplayName(classItem) }} 
                    ({{ classItem.student_count || 0 }}/{{ getClassCapacity(classItem) }} diák)
                  </option>
                </select>
                <p class="form-hint">
                  <i class='bx bx-info-circle'></i>
                  Az elfogadás osztály választása nélkül is lehetséges.
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
              :disabled="isApprovingRequest"
            >
              <i class='bx bx-check'></i>
              {{ isApprovingRequest ? 'Feldolgozás...' : 'Kérelem elfogadása' }}
            </button>
          </div>
        </div>
      </div>
    </transition>

    <transition name="modal">
      <div v-if="showCollabTargetModal" class="modal-overlay" @click.self="closeCollabTargetModal">
        <div class="modal-container collab-target-modal">
          <div class="modal-header">
            <h3>
              <i class='bx bx-target-lock'></i>
              Globális esemény célcsoport
            </h3>
            <button class="modal-close" @click="closeCollabTargetModal">
              <i class='bx bx-x'></i>
            </button>
          </div>

          <div class="modal-body">
            <div class="target-event-summary">
              <strong>{{ selectedCollabEvent?.title || 'Névtelen esemény' }}</strong>
              <span>{{ formatDate(selectedCollabEvent?.start_date) }}</span>
            </div>

            <div class="target-group-options">
              <div
                class="target-group-option"
                :class="{ selected: collabTargetGroup === 'teljes_iskola' }"
                @click="collabTargetGroup = 'teljes_iskola'"
              >
                <div class="option-content">
                  <i class='bx bx-building-house'></i>
                  <h4>Teljes iskola</h4>
                  <p>Az intézmény minden felhasználója megkapja az eseményt.</p>
                </div>
              </div>

              <div
                class="target-group-option"
                :class="{ selected: collabTargetGroup === 'osztaly_szintu' }"
                @click="collabTargetGroup = 'osztaly_szintu'"
              >
                <div class="option-content">
                  <i class='bx bx-user'></i>
                  <h4>Osztály szintű</h4>
                  <p>Csak a kijelölt osztályok látják az eseményt.</p>
                </div>

                <div v-if="collabTargetGroup === 'osztaly_szintu'" class="target-selector-panel" @click.stop>
                  <div v-if="!classes.length" class="class-target-state warning">
                    Nincsenek osztályok az intézményben.
                  </div>
                  <div v-else class="class-target-grid">
                    <label
                      v-for="classItem in classes"
                      :key="classItem.id"
                      class="class-target-card"
                      :class="{ selected: collabSelectedClassIds.includes(Number(classItem.id)) }"
                    >
                      <input type="checkbox" :value="Number(classItem.id)" v-model="collabSelectedClassIds">
                      <div class="class-target-copy">
                        <div class="class-target-title">{{ formatCompactClassDisplayName(classItem) }}</div>
                        <div class="class-target-meta">{{ classItem.student_count || 0 }} tanuló</div>
                      </div>
                    </label>
                  </div>
                </div>
              </div>

              <div
                class="target-group-option"
                :class="{ selected: collabTargetGroup === 'evfolyam_szintu' }"
                @click="collabTargetGroup = 'evfolyam_szintu'"
              >
                <div class="option-content">
                  <i class='bx bx-group'></i>
                  <h4>Évfolyam szintű</h4>
                  <p>Csak a kijelölt évfolyamok látják az eseményt.</p>
                </div>

                <div v-if="collabTargetGroup === 'evfolyam_szintu'" class="target-selector-panel" @click.stop>
                  <div v-if="!collabAvailableGrades.length" class="class-target-state warning">
                    Nincsenek elérhető évfolyamok.
                  </div>
                  <div v-else class="class-target-grid">
                    <label
                      v-for="grade in collabAvailableGrades"
                      :key="grade"
                      class="class-target-card"
                      :class="{ selected: collabSelectedGradeIds.includes(Number(grade)) }"
                    >
                      <input type="checkbox" :value="Number(grade)" v-model="collabSelectedGradeIds">
                      <div class="class-target-copy">
                        <div class="class-target-title">{{ Number(grade) }}. évfolyam</div>
                      </div>
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="collabTargetModalError" class="error-message">
              <i class='bx bx-error-circle'></i>
              <span>{{ collabTargetModalError }}</span>
            </div>
          </div>

          <div class="modal-footer">
            <button class="btn-outline" @click="closeCollabTargetModal" :disabled="processingCollabEventId === Number(selectedCollabEvent?.id)">
              Mégse
            </button>
            <button
              class="btn-primary"
              @click="approveCollabEventWithTargeting"
              :disabled="processingCollabEventId === Number(selectedCollabEvent?.id)"
            >
              <i class='bx bx-check'></i>
              {{ processingCollabEventId === Number(selectedCollabEvent?.id) ? 'Feldolgozás...' : 'Elfogadás' }}
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Diák osztálymódosító modal -->
    <transition name="modal">
      <div v-if="showEditStudentClassModal" class="modal-overlay" @click.self="closeEditUserClassModal">
        <div class="modal-container">
          <div class="modal-header">
            <h3>
              <i class='bx bx-edit'></i>
              Diák osztály módosítása
            </h3>
            <button class="modal-close" @click="closeEditUserClassModal">
              <i class='bx bx-x'></i>
            </button>
          </div>

          <div class="modal-body">
            <div class="user-summary">
              <div class="user-avatar-large">
                <span>{{ getUserInitials(selectedStudentForClassEdit) }}</span>
              </div>
              <div class="user-summary-info">
                <h4>{{ selectedStudentForClassEdit?.name }}</h4>
                <p>{{ selectedStudentForClassEdit?.email }}</p>
                <div class="role-badge-small">Diák</div>
              </div>
            </div>

            <div class="assignment-form">
              <div class="form-group">
                <label for="edit-student-class-select">Új osztály</label>
                <select
                  id="edit-student-class-select"
                  v-model="editStudentClassId"
                  class="form-select"
                  :disabled="isUpdatingStudentClass"
                >
                  <option value="">-- Nincs osztály --</option>
                  <option
                    v-for="classItem in classes"
                    :key="classItem.id"
                    :value="String(classItem.id)"
                  >
                    {{ formatClassDisplayName(classItem) }}
                    ({{ classItem.student_count || 0 }}/{{ getClassCapacity(classItem) }} diák)
                  </option>
                </select>
                <p class="form-hint">
                  <i class='bx bx-info-circle'></i>
                  Jelenlegi osztály: {{ getStudentClassDisplay(selectedStudentForClassEdit) }}
                </p>
              </div>
            </div>

            <div v-if="editStudentClassError" class="error-message">
              <i class='bx bx-error-circle'></i>
              <span>{{ editStudentClassError }}</span>
            </div>
          </div>

          <div class="modal-footer">
            <button class="btn-outline" @click="closeEditUserClassModal" :disabled="isUpdatingStudentClass">Mégse</button>
            <button class="btn-primary" @click="saveUserClassChange" :disabled="isUpdatingStudentClass">
              <i class='bx bx-save'></i>
              {{ isUpdatingStudentClass ? 'Mentés...' : 'Mentés' }}
            </button>
          </div>
        </div>
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
import { toast } from '../../services/toast';
import logo2 from '../../assets/logo2.svg';

export default {
  name: 'InstitutionManagerDashboard',
  
  data() {
    return {
      logo2,
      user: {
        id: null,
        name: '',
        email: '',
        institution_id: null,
        role: 'admin'
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
      collabEvents: [],
      isLoadingCollabEvents: false,
      processingCollabEventId: null,
      showCollabTargetModal: false,
      selectedCollabEvent: null,
      collabTargetGroup: 'teljes_iskola',
      collabSelectedClassIds: [],
      collabSelectedGradeIds: [],
      collabTargetModalError: '',
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

      selectedRequestIds: [],
      isBulkProcessingRequests: false,
      
      // Modal állapotok
      showAssignmentModal: false,
      selectedRequest: null,
      selectedClassId: '',
      errorMessage: '',
      isApprovingRequest: false,

      showEditStudentClassModal: false,
      selectedStudentForClassEdit: null,
      editStudentClassId: '',
      editStudentCurrentClassId: '',
      editStudentClassError: '',
      isUpdatingStudentClass: false,
      
      // Toast értesítések
      
      // Új osztály létrehozása
      newClass: {
        name: '',
        grade: null,
        capacity: 30,
        teacher_id: ''
      },
      classErrors: {},
      isCreatingClass: false,
      isDeletingClassId: null,
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

    pendingGlobalEventRequests() {
      return this.collabEvents.length;
    },

    collabAvailableGrades() {
      return Array.from(new Set(
        this.classes
          .map(classItem => Number(classItem?.grade))
          .filter(Number.isFinite)
      )).sort((left, right) => left - right);
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
        const user = request.user || this.getUserById(request.user_id);
        return user && (
          (user.name || '').toLowerCase().includes(query) ||
          (user.email || '').toLowerCase().includes(query)
        );
      });
    },
    
    // Szűrt tanár kérelmek
    filteredTeacherRequests() {
      if (!this.searchQuery) return this.pendingTeacherRequests;
      
      const query = this.searchQuery.toLowerCase();
      return this.pendingTeacherRequests.filter(request => {
        const user = request.user || this.getUserById(request.user_id);
        return user && (
          (user.name || '').toLowerCase().includes(query) ||
          (user.email || '').toLowerCase().includes(query)
        );
      });
    },

    visibleRequests() {
      return this.activeRequestTab === 'students'
        ? this.filteredStudentRequests
        : this.filteredTeacherRequests;
    },

    selectedVisibleRequests() {
      const selectedIds = new Set(this.selectedRequestIds.map(id => Number(id)));
      return this.visibleRequests.filter(request => selectedIds.has(Number(request.id)));
    },

    selectedVisibleRequestCount() {
      return this.selectedVisibleRequests.length;
    },

    allVisibleRequestsSelected() {
      if (!this.visibleRequests.length) {
        return false;
      }

      return this.selectedVisibleRequestCount === this.visibleRequests.length;
    },
    

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

    formatClassDisplayName(classItem) {
      const grade = classItem?.grade;
      const className = (classItem?.name || '').toString().trim();

      if (grade && className) {
        return `${grade}.${className}`;
      }

      return className || 'Névtelen osztály';
    },

    formatCompactClassDisplayName(classItem) {
      const grade = classItem?.grade;
      const className = (classItem?.name || '').toString().trim();

      if (grade && className) {
        return `${grade}.${className}`;
      }

      return className || 'Nincs beállítva';
    },

    getStudentClassDisplay(student) {
      if (!student) return 'Nincs beállítva';

      const className = (student.class_name || '').toString().trim();
      if (className) {
        return className;
      }

      return 'Nincs beállítva';
    },

    getTeacherClassesDisplay(teacher) {
      if (!teacher || !this.classes?.length) {
        return 'Nincs beállítva';
      }

      const labels = this.classes
        .filter(classItem => Number(classItem.user_id) === Number(teacher.id))
        .map(classItem => this.formatCompactClassDisplayName(classItem));

      if (!labels.length) {
        return 'Nincs beállítva';
      }

      return labels.join(', ');
    },

    getClassCapacity(classItem) {
      const capacity = Number(classItem?.capacity);

      if (Number.isFinite(capacity) && capacity > 0) {
        return capacity;
      }

      return 30;
    },

    sanitizeClassNameInput() {
      this.newClass.name = String(this.newClass.name || '')
        .replace(/[^a-zA-ZáéíóöőúüűÁÉÍÓÖŐÚÜŰ]/g, '')
        .slice(0, 5);
    },

    toggleUserMenu() {
      this.showUserMenu = !this.showUserMenu;
    },

    askForConfirmation(message) {
      return toast.confirm(message);
    },

    normalizeRequestId(request) {
      return Number(request?.id ?? request?.request_id);
    },

    isRequestSelected(request) {
      const requestId = this.normalizeRequestId(request);
      if (!requestId) {
        return false;
      }

      return this.selectedRequestIds.includes(requestId);
    },

    toggleRequestSelection(request) {
      const requestId = this.normalizeRequestId(request);
      if (!requestId) {
        return;
      }

      if (this.selectedRequestIds.includes(requestId)) {
        this.selectedRequestIds = this.selectedRequestIds.filter(id => id !== requestId);
      } else {
        this.selectedRequestIds = [...this.selectedRequestIds, requestId];
      }
    },

    toggleSelectAllVisibleRequests() {
      if (this.allVisibleRequestsSelected) {
        const visibleIds = new Set(this.visibleRequests.map(request => this.normalizeRequestId(request)));
        this.selectedRequestIds = this.selectedRequestIds.filter(id => !visibleIds.has(Number(id)));
        return;
      }

      const mergedIds = new Set(this.selectedRequestIds);
      this.visibleRequests.forEach(request => {
        const requestId = this.normalizeRequestId(request);
        if (requestId) {
          mergedIds.add(requestId);
        }
      });
      this.selectedRequestIds = Array.from(mergedIds);
    },

    clearRequestSelection() {
      this.selectedRequestIds = [];
    },

    syncRequestSelectionWithPendingList() {
      const pendingIds = new Set(this.establishmentRequests.map(request => this.normalizeRequestId(request)));
      this.selectedRequestIds = this.selectedRequestIds.filter(id => pendingIds.has(Number(id)));
    },

    async processRequests(action, requests, { notify = true } = {}) {
      if (!Array.isArray(requests) || !requests.length) {
        return { success: false, processedCount: 0 };
      }

      const token =
        localStorage.getItem('esemenyter_token') ||
        sessionStorage.getItem('esemenyter_token');

      const requestIds = requests
        .map(request => this.normalizeRequestId(request))
        .filter(id => Number.isFinite(id));

      if (!requestIds.length) {
        this.showNotification('A kiválasztott kérelmek azonosítója hiányzik.', 'error');
        return { success: false, processedCount: 0 };
      }

      await axios.patch('http://127.0.0.1:8000/api/establishment/requests/handle', {
        establishment_id: this.user.institution_id,
        action,
        request_id: requestIds
      }, {
        headers: { Authorization: `Bearer ${token}` }
      });

      const requestIdSet = new Set(requestIds.map(id => Number(id)));
      this.establishmentRequests = this.establishmentRequests.filter(request => !requestIdSet.has(Number(request.id)));
      this.syncRequestSelectionWithPendingList();

      if (action === 'accept') {
        await this.loadInstitutionUsers(this.user.institution_id);
        await this.loadStudentClassAssignments(this.user.institution_id);
        this.updateStats();
      }

      if (notify) {
        const actionLabel = action === 'accept' ? 'elfogadva' : 'elutasítva';
        this.showNotification(`${requestIds.length} kérelem sikeresen ${actionLabel}.`, action === 'accept' ? 'success' : 'warning');
      }

      return { success: true, processedCount: requestIds.length };
    },
    
    // Adatok betöltése
    async loadInstitutionData() {
      try {
        const token =
          localStorage.getItem('esemenyter_token') ||
          sessionStorage.getItem('esemenyter_token');
        const storedInstitutionId = localStorage.getItem('CurrentInstitution') || localStorage.getItem('institutionId');
        const institutionId = this.user.institution_id || storedInstitutionId;

        if (!institutionId) {
          console.error('Nincs intézmény ID');
          return;
        }

        this.user.institution_id = Number(institutionId);

        // Intézmény adatok
        const instResponse = await axios.get(`http://127.0.0.1:8000/api/establishment/${institutionId}`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        const institutionData = instResponse.data.data || instResponse.data || {};
        this.institution = {
          ...this.institution,
          id: institutionData.id || Number(institutionId),
          name: institutionData.name || institutionData.title || '',
          address: institutionData.address || '',
          type: institutionData.type || ''
        };

        // Kérelmek betöltése a létező végpontokról (diák + tanár)
        const [studentRequestsResponse, teacherRequestsResponse] = await Promise.all([
          axios.get(`http://127.0.0.1:8000/api/establishment/${institutionId}/requests/students`, {
            headers: { Authorization: `Bearer ${token}` }
          }),
          axios.get(`http://127.0.0.1:8000/api/establishment/${institutionId}/requests/teachers`, {
            headers: { Authorization: `Bearer ${token}` }
          })
        ]);

        const allRequests = [
          ...(studentRequestsResponse.data.data || []),
          ...(teacherRequestsResponse.data.data || [])
        ];

        // Kérelmek egyesítése, user adatokkal
        // Támogatjuk mindkét backend formátumot:
        // - request.user: { id, name, email }
        // - request.name + request.email
        this.establishmentRequests = allRequests.map(request => {
          const resolvedRequestId = request.id ?? request.request_id ?? null;
          const resolvedUserId = request.user_id ?? request.user?.id ?? null;
          const resolvedName = (request.user?.name || request.name || '').toString().trim();
          const resolvedEmail = (request.user?.email || request.email || '').toString().trim();

          return {
            ...request,
            id: resolvedRequestId,
            user_id: resolvedUserId,
            user: request.user || {
              id: resolvedUserId,
              name: resolvedName || `Felhasználó #${resolvedUserId ?? '?'}`,
              email: resolvedEmail
            }
          };
        });
        this.syncRequestSelectionWithPendingList();

        await this.loadCollabEvents(institutionId);

        // Diákok és tanárok betöltése (akik már csatlakoztak)
        await this.loadInstitutionUsers(institutionId);

        // Osztályok betöltése
        await this.loadClasses(institutionId);

        // Diákok osztályainak hozzárendelése (pl. 11B)
        await this.loadStudentClassAssignments(institutionId);

        // Statisztikák frissítése
        this.updateStats();

      } catch (error) {
        console.error('Hiba az adatok betöltésekor:', error);
        this.showNotification('Hiba történt az adatok betöltésekor', 'error');
      }
    },

    async loadCollabEvents(institutionId) {
      try {
        const token =
          localStorage.getItem('esemenyter_token') ||
          sessionStorage.getItem('esemenyter_token');

        if (!token || !institutionId) {
          this.collabEvents = [];
          return;
        }

        this.isLoadingCollabEvents = true;

        const response = await axios.get(
          `http://127.0.0.1:8000/api/establishment/${institutionId}/event-access`,
          {
            headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' }
          }
        );

        this.collabEvents = Array.isArray(response?.data?.events) ? response.data.events : [];
      } catch (error) {
        console.error('Hiba a globális esemény kérelmek betöltésekor:', error);
        this.collabEvents = [];
      } finally {
        this.isLoadingCollabEvents = false;
      }
    },

    async openCollabTargetModal(eventItem) {
      const establishmentId = Number(this.user?.institution_id);

      if (!establishmentId) {
        this.showNotification('Hiányzó intézmény azonosító.', 'error');
        return;
      }

      this.selectedCollabEvent = eventItem;
      this.collabTargetGroup = 'teljes_iskola';
      this.collabSelectedClassIds = [];
      this.collabSelectedGradeIds = [];
      this.collabTargetModalError = '';

      if (!this.classes.length) {
        await this.loadClasses(establishmentId);
      }

      if (!this.students.length && !this.teachers.length) {
        await this.loadInstitutionUsers(establishmentId);
      }

      await this.loadStudentClassAssignments(establishmentId);

      this.showCollabTargetModal = true;
    },

    closeCollabTargetModal() {
      this.showCollabTargetModal = false;
      this.selectedCollabEvent = null;
      this.collabTargetGroup = 'teljes_iskola';
      this.collabSelectedClassIds = [];
      this.collabSelectedGradeIds = [];
      this.collabTargetModalError = '';
    },

    resolveCollabAcceptanceUserIds() {
      const classesById = new Map(
        this.classes.map(classItem => [Number(classItem.id), classItem])
      );

      if (this.collabTargetGroup === 'teljes_iskola') {
        return this.resolveInstitutionUserIdsForCollabAcceptance();
      }

      if (this.collabTargetGroup === 'osztaly_szintu') {
        const selectedClassIds = this.collabSelectedClassIds
          .map(classId => Number(classId))
          .filter(Number.isFinite);

        if (!selectedClassIds.length) {
          return [];
        }

        const selectedClassLabels = new Set(
          selectedClassIds
            .map(classId => classesById.get(classId))
            .filter(Boolean)
            .map(classItem => this.formatCompactClassDisplayName(classItem))
        );

        const studentIds = this.students
          .filter(student => selectedClassLabels.has((student.class_name || '').toString().trim()))
          .map(student => Number(student?.id))
          .filter(Number.isFinite);

        const teacherIds = selectedClassIds
          .map(classId => Number(classesById.get(classId)?.user_id))
          .filter(Number.isFinite);

        return this.normalizeUserIdsForCollabAcceptance([...studentIds, ...teacherIds]);
      }

      if (this.collabTargetGroup === 'evfolyam_szintu') {
        const selectedGrades = this.collabSelectedGradeIds
          .map(grade => Number(grade))
          .filter(Number.isFinite);

        if (!selectedGrades.length) {
          return [];
        }

        const classIdsInSelectedGrades = this.classes
          .filter(classItem => selectedGrades.includes(Number(classItem?.grade)))
          .map(classItem => Number(classItem.id))
          .filter(Number.isFinite);

        const classLabelsInSelectedGrades = new Set(
          classIdsInSelectedGrades
            .map(classId => classesById.get(classId))
            .filter(Boolean)
            .map(classItem => this.formatCompactClassDisplayName(classItem))
        );

        const studentIds = this.students
          .filter(student => classLabelsInSelectedGrades.has((student.class_name || '').toString().trim()))
          .map(student => Number(student?.id))
          .filter(Number.isFinite);

        const teacherIds = classIdsInSelectedGrades
          .map(classId => Number(classesById.get(classId)?.user_id))
          .filter(Number.isFinite);

        return this.normalizeUserIdsForCollabAcceptance([...studentIds, ...teacherIds]);
      }

      return this.resolveInstitutionUserIdsForCollabAcceptance();
    },

    normalizeUserIdsForCollabAcceptance(userIds) {
      const normalizedUserIds = (userIds || []).map(userId => Number(userId)).filter(Number.isFinite);
      const currentUserId = Number(this.user?.id);

      if (Number.isFinite(currentUserId)) {
        normalizedUserIds.push(currentUserId);
      }

      return Array.from(new Set(normalizedUserIds));
    },

    async approveCollabEventWithTargeting() {
      const eventId = Number(this.selectedCollabEvent?.id);
      if (!eventId) {
        this.collabTargetModalError = 'Hiányzó esemény azonosító.';
        return;
      }

      const selectedUserIds = this.resolveCollabAcceptanceUserIds();
      if (!selectedUserIds.length) {
        this.collabTargetModalError = 'A kiválasztott célcsoporthoz nem találtunk címzetteket.';
        return;
      }

      this.collabTargetModalError = '';
      const success = await this.handleCollabEventRequest(eventId, 'accept', selectedUserIds);

      if (success) {
        this.closeCollabTargetModal();
      }
    },

    async handleCollabEventRequest(eventId, action, usersOverride = null) {
      try {
        const token =
          localStorage.getItem('esemenyter_token') ||
          sessionStorage.getItem('esemenyter_token');
        const establishmentId = this.user.institution_id;

        if (!token || !establishmentId) {
          this.showNotification('Hiányzó hitelesítés vagy intézmény azonosító.', 'error');
          return;
        }

        const eventIdNumber = Number(eventId);
        if (!eventIdNumber) {
          this.showNotification('Hiányzó esemény azonosító.', 'error');
          return;
        }

        if (action === 'accept' && (!this.students.length && !this.teachers.length)) {
          await this.loadInstitutionUsers(establishmentId);
        }

        const acceptanceUserIds = action === 'accept'
          ? this.normalizeUserIdsForCollabAcceptance(
            Array.isArray(usersOverride) && usersOverride.length
              ? usersOverride
              : this.resolveInstitutionUserIdsForCollabAcceptance()
          )
          : undefined;

        if (action === 'accept' && !acceptanceUserIds.length) {
          this.showNotification('Nincs elfogadható intézményi felhasználó az eseményhez.', 'warning');
          return;
        }

        this.processingCollabEventId = eventIdNumber;

        await axios.patch(
          `http://127.0.0.1:8000/api/establishment/${establishmentId}/event-access`,
          {
            event_id: eventIdNumber,
            action,
            ...(action === 'accept' ? { users: acceptanceUserIds } : {})
          },
          {
            headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' }
          }
        );

        this.collabEvents = this.collabEvents.filter(event => Number(event.id) !== eventIdNumber);

        if (action === 'accept') {
          this.showNotification('Globális esemény elfogadva.', 'success');
        } else {
          this.showNotification('Globális esemény kérés elutasítva.', 'warning');
        }

        return true;
      } catch (error) {
        console.error('Hiba a globális esemény kérés feldolgozásakor:', error);
        this.showNotification(error.response?.data?.message || 'Hiba történt a kérés feldolgozásakor.', 'error');
        return false;
      } finally {
        this.processingCollabEventId = null;
      }
    },

    resolveInstitutionUserIdsForCollabAcceptance() {
      const userIds = [
        ...this.students.map(user => Number(user?.id)).filter(Number.isFinite),
        ...this.teachers.map(user => Number(user?.id)).filter(Number.isFinite),
      ];

      const currentUserId = Number(this.user?.id);
      if (Number.isFinite(currentUserId)) {
        userIds.push(currentUserId);
      }

      return Array.from(new Set(userIds));
    },
    
    async loadInstitutionUsers(institutionId) {
      try {
        const token =
          localStorage.getItem('esemenyter_token') ||
          sessionStorage.getItem('esemenyter_token');
        
        const studentsResponse = await axios.get(`http://127.0.0.1:8000/api/members/students/${institutionId}`, {
          headers: token ? { Authorization: `Bearer ${token}` } : {}
        });
        this.students = studentsResponse.data.data || [];
      
        const teachersResponse = await axios.get(`http://127.0.0.1:8000/api/members/staff/${institutionId}`, {
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
        const token =
          localStorage.getItem('esemenyter_token') ||
          sessionStorage.getItem('esemenyter_token');

        const response = await axios.get(`http://127.0.0.1:8000/api/establishment/${institutionId}/classes`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        this.classes = response.data.data || [];

      } catch (error) {
        console.error('Hiba az osztályok betöltésekor:', error);
        this.showNotification('Hiba történt az osztályok betöltésekor', 'error');
      }
    },

    async loadStudentClassAssignments(institutionId) {
      try {
        if (!this.students.length || !this.classes.length) {
          return;
        }

        const token =
          localStorage.getItem('esemenyter_token') ||
          sessionStorage.getItem('esemenyter_token');

        const memberResponses = await Promise.all(
          this.classes.map(classItem =>
            axios.get(`http://127.0.0.1:8000/api/establishment/${institutionId}/classes/${classItem.id}`, {
              headers: { Authorization: `Bearer ${token}` }
            }).catch(() => ({ data: { data: [] } }))
          )
        );

        const classByUserId = new Map();

        this.classes.forEach((classItem, index) => {
          const members = memberResponses[index]?.data?.data || [];
          const classLabel = this.formatCompactClassDisplayName(classItem);

          members.forEach(member => {
            const memberUserId = Number(member.id);

            if (memberUserId && !classByUserId.has(memberUserId)) {
              classByUserId.set(memberUserId, classLabel);
            }
          });
        });

        this.students = this.students.map(student => ({
          ...student,
          class_name: classByUserId.get(Number(student.id)) || student.class_name || ''
        }));
      } catch (error) {
        console.error('Hiba a diák osztály-hozzárendelések betöltésekor:', error);
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
 
      if (!this.newClass.name?.trim() && !this.newClass.grade && !this.newClass.capacity) {
        // Ha egy mező sincs kitöltve, ne csináljunk semmit
        return;
      }

      // Ha van név, de nincs évfolyam
      if (this.newClass.name?.trim() && !this.newClass.grade) {
        this.classErrors.grade = 'Az évfolyam kötelező, ha osztálynevet adsz meg';
        return;
      }

      if (this.newClass.name?.trim() && !/^[a-zA-ZáéíóöőúüűÁÉÍÓÖŐÚÜŰ]+$/.test(this.newClass.name.trim())) {
        this.classErrors.name = 'Az osztály neve csak betűket tartalmazhat';
        return;
      }

      const parsedGrade = Number(this.newClass.grade);
      if (!Number.isInteger(parsedGrade) || parsedGrade < 1 || parsedGrade > 100) {
        this.classErrors.grade = 'Az évfolyamnak 1 és 100 közötti egész számnak kell lennie';
        return;
      }

      const parsedCapacity = Number(this.newClass.capacity);
      if (!Number.isInteger(parsedCapacity) || parsedCapacity < 1 || parsedCapacity > 200) {
        this.classErrors.capacity = 'A létszámnak 1 és 200 közötti egész számnak kell lennie';
        return;
      }

      this.isCreatingClass = true;

      try {
        const token =
          localStorage.getItem('esemenyter_token') ||
          sessionStorage.getItem('esemenyter_token');

        const classData = {
          name: this.newClass.name || 'Új osztály',
          grade: parsedGrade,
          capacity: parsedCapacity,
          user_id: this.newClass.teacher_id || null,
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
          grade: null,
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
      const classId = Number(classItem?.id);
      const establishmentId = Number(this.user?.institution_id);
      const classLabel = this.formatClassDisplayName(classItem);

      if (!Number.isFinite(classId) || classId <= 0) {
        this.showNotification('Hiányzó osztály azonosító.', 'error');
        return;
      }

      if (!Number.isFinite(establishmentId) || establishmentId <= 0) {
        this.showNotification('Hiányzó intézmény azonosító.', 'error');
        return;
      }

      const isConfirmed = await this.askForConfirmation(`Biztosan törölni szeretnéd a(z) ${classLabel} osztályt?`);
      if (!isConfirmed) {
        return;
      }

      const token =
        localStorage.getItem('esemenyter_token') ||
        sessionStorage.getItem('esemenyter_token');

      this.isDeletingClassId = classId;

      try {
        await axios.delete(`http://127.0.0.1:8000/api/establishment/${establishmentId}/classes/${classId}`, {
          headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' }
        });

        await this.loadClasses(establishmentId);
        await this.loadStudentClassAssignments(establishmentId);
        this.updateStats();

        this.showNotification('Osztály sikeresen törölve.', 'success');
      } catch (error) {
        console.error('Hiba az osztály törlésekor:', error);
        this.showNotification(error?.response?.data?.message || 'Hiba történt az osztály törlésekor.', 'error');
      } finally {
        this.isDeletingClassId = null;
      }
    },
    
    // Kérelem kezelés
    showClassAssignmentModal(request) {
      // Ellenőrizzük, hogy van-e user adat
      const user = this.getUserById(request.user_id);
      if (user && !request.user) {
        request.user = user;
      }
      if (!request.user) {
        request.user = {
          id: request.user_id,
          name: `Felhasználó #${request.user_id}`,
          email: ''
        };
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

    async findStudentCurrentClassId(studentUserId) {
      const token =
        localStorage.getItem('esemenyter_token') ||
        sessionStorage.getItem('esemenyter_token');
      const establishmentId = this.user.institution_id;

      if (!token || !establishmentId || !this.classes.length) {
        return '';
      }

      const memberResponses = await Promise.all(
        this.classes.map(classItem =>
          axios
            .get(`http://127.0.0.1:8000/api/establishment/${establishmentId}/classes/${classItem.id}`, {
              headers: { Authorization: `Bearer ${token}` }
            })
            .catch(() => ({ data: { data: [] } }))
        )
      );

      for (let i = 0; i < this.classes.length; i += 1) {
        const classItem = this.classes[i];
        const members = memberResponses[i]?.data?.data || [];
        const isMember = members.some(member => Number(member.id) === Number(studentUserId));

        if (isMember) {
          return String(classItem.id);
        }
      }

      return '';
    },

    async closeEditUserClassModal() {
      this.showEditStudentClassModal = false;
      this.selectedStudentForClassEdit = null;
      this.editStudentClassId = '';
      this.editStudentCurrentClassId = '';
      this.editStudentClassError = '';
      this.isUpdatingStudentClass = false;
    },
    
    async approveRequest() {
      try {
        if (this.isApprovingRequest) {
          return;
        }

        this.isApprovingRequest = true;

        const token =
          localStorage.getItem('esemenyter_token') ||
          sessionStorage.getItem('esemenyter_token');
        const establishmentId = this.user.institution_id;
        const requestId = this.selectedRequest?.id ?? this.selectedRequest?.request_id;
        const userId = this.selectedRequest?.user_id ?? this.selectedRequest?.user?.id;
        const role = this.selectedRequest.role;

        if (!requestId) {
          this.errorMessage = 'A kérelem azonosítója hiányzik, frissítsd az oldalt és próbáld újra.';
          this.showNotification(this.errorMessage, 'error');
          return;
        }

        await axios.patch('http://127.0.0.1:8000/api/establishment/requests/handle', {
          establishment_id: establishmentId,
          action: 'accept',
          request_id: [Number(requestId)]
        }, {
          headers: { Authorization: `Bearer ${token}` }
        });

        // Opcionális osztály hozzárendelés
        if (this.selectedClassId) {
          if (role === 'student') {
            await this.loadInstitutionUsers(establishmentId);
            const acceptedStudent = this.students.find(student => Number(student.id) === Number(userId));

            if (acceptedStudent?.student_id) {
              await axios.patch('http://127.0.0.1:8000/api/establishment/classes/add-students', {
                establishment_id: establishmentId,
                class_id: this.selectedClassId,
                student_id: [acceptedStudent.student_id]
              }, {
                headers: { Authorization: `Bearer ${token}` }
              });
            }
          }

          if (role === 'teacher') {
            await this.loadInstitutionUsers(establishmentId);
            const acceptedTeacher = this.teachers.find(teacher => Number(teacher.id) === Number(userId));

            await axios.patch(`http://127.0.0.1:8000/api/establishment/${establishmentId}/classes/${this.selectedClassId}`, {
              teacher_id: acceptedTeacher.id
            }, {
              headers: { Authorization: `Bearer ${token}` }
            });
          }
        }
      
        // Eltávolítjuk a listából
        const index = this.establishmentRequests.findIndex(r => Number(r.id) === Number(requestId));
        if (index !== -1) {
          this.establishmentRequests.splice(index, 1);
        }
        this.syncRequestSelectionWithPendingList();
      
        // Felhasználók újratöltése
        await this.loadInstitutionUsers(this.user.institution_id);
        await this.loadStudentClassAssignments(establishmentId);
      
        this.closeAssignmentModal();
        this.showNotification('Kérelem sikeresen elfogadva!', 'success');
      
      } catch (error) {
        console.error('Hiba a kérelem elfogadásakor:', error);
        this.errorMessage = error.response?.data?.message || 'Hiba történt a kérelem feldolgozása során';
        this.showNotification(this.errorMessage, 'error');
      } finally {
        this.isApprovingRequest = false;
      }
    },
    
    async rejectRequest(request) {
      const user = request.user || this.getUserById(request.user_id);
      const isConfirmed = await this.askForConfirmation(`Biztosan elutasítja ${user?.name || 'a felhasználó'} csatlakozási kérelmét?`);
      if (!isConfirmed) {
        return;
      }

      try {
        const requestId = this.normalizeRequestId(request);
        if (!requestId) {
          this.showNotification('A kérelem azonosítója hiányzik, frissítsd az oldalt és próbáld újra.', 'error');
          return;
        }
        await this.processRequests('reject', [request], { notify: false });
        this.showNotification('Kérelem elutasítva', 'warning');

      } catch (error) {
        console.error('Hiba a kérelem elutasításakor:', error);
        this.showNotification('Hiba történt a művelet során', 'error');
      }
    },

    async bulkApproveSelectedRequests() {
      if (!this.selectedVisibleRequestCount || this.isBulkProcessingRequests) {
        return;
      }

      const isConfirmed = await this.askForConfirmation(`Biztosan elfogadod a kijelölt ${this.selectedVisibleRequestCount} kérelmet?`);
      if (!isConfirmed) {
        return;
      }

      try {
        this.isBulkProcessingRequests = true;
        await this.processRequests('accept', this.selectedVisibleRequests, { notify: true });
        this.clearRequestSelection();
      } catch (error) {
        console.error('Hiba a kérelmek tömeges elfogadásakor:', error);
        this.showNotification('Hiba történt a kérelmek elfogadásakor.', 'error');
      } finally {
        this.isBulkProcessingRequests = false;
      }
    },

    async bulkRejectSelectedRequests() {
      if (!this.selectedVisibleRequestCount || this.isBulkProcessingRequests) {
        return;
      }

      const isConfirmed = await this.askForConfirmation(`Biztosan elutasítod a kijelölt ${this.selectedVisibleRequestCount} kérelmet?`);
      if (!isConfirmed) {
        return;
      }

      try {
        this.isBulkProcessingRequests = true;
        await this.processRequests('reject', this.selectedVisibleRequests, { notify: true });
        this.clearRequestSelection();
      } catch (error) {
        console.error('Hiba a kérelmek tömeges elutasításakor:', error);
        this.showNotification('Hiba történt a kérelmek elutasításakor.', 'error');
      } finally {
        this.isBulkProcessingRequests = false;
      }
    },
    
    // Felhasználó műveletek
    async editUserClass(user) {
      try {
        this.selectedStudentForClassEdit = user;
        this.editStudentClassError = '';
        this.editStudentClassId = '';
        this.editStudentCurrentClassId = '';
        this.showEditStudentClassModal = true;

        const currentClassId = await this.findStudentCurrentClassId(user.id);
        this.editStudentCurrentClassId = currentClassId;
        this.editStudentClassId = currentClassId;
      } catch (error) {
        console.error('Hiba az osztálymódosító modal megnyitásakor:', error);
        this.showNotification('Nem sikerült betölteni a diák jelenlegi osztályát.', 'error');
      }
    },

    async saveUserClassChange() {
      try {
        if (this.isUpdatingStudentClass) {
          return;
        }

        const student = this.selectedStudentForClassEdit;
        if (!student?.student_id) {
          this.editStudentClassError = 'A diák azonosítója hiányzik, frissítsd az oldalt és próbáld újra.';
          return;
        }

        const token =
          localStorage.getItem('esemenyter_token') ||
          sessionStorage.getItem('esemenyter_token');
        const establishmentId = this.user.institution_id;

        if (!token || !establishmentId) {
          this.editStudentClassError = 'Hiányzó hitelesítés vagy intézmény azonosító.';
          return;
        }

        this.isUpdatingStudentClass = true;
        this.editStudentClassError = '';

        const currentClassId = this.editStudentCurrentClassId ? Number(this.editStudentCurrentClassId) : null;
        const targetClassId = this.editStudentClassId ? Number(this.editStudentClassId) : null;

        if (currentClassId === targetClassId) {
          this.showNotification('Nem történt változás az osztályban.', 'info');
          await this.closeEditUserClassModal();
          return;
        }

        if (currentClassId && currentClassId !== targetClassId) {
          await axios.patch('http://127.0.0.1:8000/api/establishment/classes/remove-students', {
            establishment_id: establishmentId,
            class_id: currentClassId,
            student_id: [student.student_id]
          }, {
            headers: { Authorization: `Bearer ${token}` }
          });
        }

        if (targetClassId) {
          await axios.patch('http://127.0.0.1:8000/api/establishment/classes/add-students', {
            establishment_id: establishmentId,
            class_id: targetClassId,
            student_id: [student.student_id]
          }, {
            headers: { Authorization: `Bearer ${token}` }
          });
        }

        await this.loadClasses(establishmentId);
        await this.loadInstitutionUsers(establishmentId);
        await this.loadStudentClassAssignments(establishmentId);
        this.updateStats();

        await this.closeEditUserClassModal();
        this.showNotification('Diák osztálya sikeresen módosítva.', 'success');
      } catch (error) {
        console.error('Hiba a diák osztályának módosításakor:', error);
        const apiErrors = error.response?.data?.errors;
        const apiErrorText = typeof apiErrors === 'string'
          ? apiErrors
          : Array.isArray(apiErrors)
            ? apiErrors.join(' ')
            : null;

        this.editStudentClassError = error.response?.data?.message || apiErrorText || 'Nem sikerült menteni az osztály módosítását.';
        this.showNotification('Nem sikerült menteni az osztály módosítását.', 'error');
      } finally {
        this.isUpdatingStudentClass = false;
      }
    },
    
    editTeacherClasses(teacher) {
      console.log('Edit teacher classes:', teacher);
      this.showNotification('Tanított osztályok módosítása fejlesztés alatt', 'info');
    },
    
    // Értesítés megjelenítése
    showNotification(message, type = 'success') {
      const safeType = ['success', 'error', 'warning', 'info'].includes(type) ? type : 'info';
      toast[safeType](message, 3500);
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
        const token =
          localStorage.getItem('esemenyter_token') ||
          sessionStorage.getItem('esemenyter_token');
        await axios.delete('http://127.0.0.1:8000/api/logout', {
          headers: { Authorization: `Bearer ${token}` }
        });
      } catch (error) {
        console.error('Logout hiba:', error);
      } finally {
        localStorage.removeItem('esemenyter_user');
        localStorage.removeItem('esemenyter_token');
        localStorage.removeItem('CurrentInstitution');
        localStorage.removeItem('remember_me');
        sessionStorage.removeItem('esemenyter_user');
        sessionStorage.removeItem('esemenyter_token');
        sessionStorage.removeItem('CurrentInstitution');
        delete axios.defaults.headers.common['Authorization'];
        this.$router.push('/');
      }
    },
    
    async checkLoginStatus() {
      const savedUser =
        localStorage.getItem('esemenyter_user') ||
        sessionStorage.getItem('esemenyter_user');
      if (savedUser) {
        const userData = JSON.parse(savedUser);
        if (userData.isLoggedIn) {
          const storedInstitutionId =
            localStorage.getItem('CurrentInstitution') ||
            sessionStorage.getItem('CurrentInstitution') ||
            localStorage.getItem('institutionId') ||
            sessionStorage.getItem('institutionId');
          this.user = { ...this.user, ...userData };

          if (!this.user.role) {
            try {
              const token =
                localStorage.getItem('esemenyter_token') ||
                sessionStorage.getItem('esemenyter_token');
              if (token) {
                const roleResponse = await axios.get('http://127.0.0.1:8000/api/establishment/role', {
                  headers: { Authorization: `Bearer ${token}` }
                });

                const roleFromApi = roleResponse.data?.role;
                if (roleFromApi) {
                  this.user.role = roleFromApi;
                  const mergedUser = {
                    ...userData,
                    role: roleFromApi
                  };

                  if (localStorage.getItem('esemenyter_token')) {
                    localStorage.setItem('esemenyter_user', JSON.stringify(mergedUser));
                  } else {
                    sessionStorage.setItem('esemenyter_user', JSON.stringify(mergedUser));
                  }
                }
              }
            } catch (error) {
              console.error('Role lekérési hiba:', error);
            }
          }

          if (!this.user.institution_id && storedInstitutionId) {
            this.user.institution_id = Number(storedInstitutionId);
          }
          
          // Ellenőrizzük, hogy admin-e
          if (this.user.role !== 'admin') {
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
  width: 100%;
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
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #ffffff;
  box-shadow: 0 10px 20px rgba(15, 23, 42, 0.15);
  overflow: hidden;
}

.logo-image {
  width: 100%;
  height: 100%;
  object-fit: contain;
  display: block;
}

.logo-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.site-title {
  font-size: 24px;
  font-weight: 700;
  background: linear-gradient(135deg, #667eea, #764ba2);
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  margin: 0;
}

.site-subtitle {
  margin: 0;
  font-size: 14px;
  color: #64748b;
  font-weight: 500;
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

.bulk-actions-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
  padding: 12px 14px;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  background: #f8f9ff;
}

.bulk-select-all {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #374151;
  font-weight: 600;
  font-size: 16px;
  padding: 6px 8px;
  border-radius: 10px;
  cursor: pointer;
}

.bulk-select-all input {
  width: 24px;
  height: 24px;
  accent-color: #4f46e5;
  cursor: pointer;
}

.bulk-actions-right {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  justify-content: flex-end;
}

.bulk-selected-count {
  color: #6b7280;
  font-size: 13px;
  font-weight: 600;
}

.bulk-btn {
  flex: 0 0 auto;
  min-width: 180px;
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
  position: relative;
}

.request-select {
  position: absolute;
  top: 10px;
  right: 10px;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.request-select input {
  width: 22px;
  height: 22px;
  accent-color: #4f46e5;
  cursor: pointer;
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

.request-actions-stacked {
  flex-direction: column;
  gap: 12px;
}

.request-actions-inline {
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

.btn-details {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px;
  border-radius: 8px;
  border: 1px solid #c7d2fe;
  background: #eef2ff;
  color: #3730a3;
  font-size: 14px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s ease;
}

.btn-details-full {
  width: 100%;
}

.btn-details:hover {
  background: #e0e7ff;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
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

.collab-target-modal {
  max-width: 760px;
}

.target-event-summary {
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 14px 16px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  margin-bottom: 16px;
}

.target-event-summary strong {
  color: #1f2937;
  font-size: 16px;
}

.target-event-summary span {
  color: #64748b;
  font-size: 13px;
}

.target-group-options {
  display: grid;
  gap: 14px;
}

.target-group-option {
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 16px;
  background: #ffffff;
  cursor: pointer;
  transition: all 0.2s ease;
}

.target-group-option:hover {
  border-color: #a5b4fc;
}

.target-group-option.selected {
  border-color: #6366f1;
  background: #eef2ff;
}

.option-content {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.option-content i {
  font-size: 24px;
  color: #4f46e5;
}

.option-content h4 {
  margin: 0;
  color: #1f2937;
  font-size: 16px;
}

.option-content p {
  margin: 0;
  color: #64748b;
  font-size: 13px;
}

.target-selector-panel {
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid rgba(99, 102, 241, 0.2);
}

.class-target-state {
  padding: 10px 12px;
  border-radius: 10px;
  background: #e2e8f0;
  color: #334155;
  font-size: 13px;
}

.class-target-state.warning {
  background: #fee2e2;
  color: #991b1b;
}

.class-target-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 10px;
}

.class-target-card {
  border: 1px solid #cbd5e1;
  border-radius: 12px;
  padding: 10px;
  background: white;
  cursor: pointer;
  display: flex;
  align-items: flex-start;
  gap: 10px;
}

.class-target-card.selected {
  border-color: #4f46e5;
  box-shadow: 0 0 0 1px #4f46e5;
  background: #eef2ff;
}

.class-target-card input[type="checkbox"] {
  width: 16px;
  height: 16px;
  accent-color: #4f46e5;
  margin-top: 2px;
}

.class-target-copy {
  flex: 1;
}

.class-target-title {
  color: #1e293b;
  font-size: 14px;
  font-weight: 600;
}

.class-target-meta {
  margin-top: 4px;
  color: #64748b;
  font-size: 12px;
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

.confirm-toast {
  min-width: 380px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.confirm-toast-content {
  display: flex;
  align-items: center;
  gap: 10px;
  flex: 1;
}

.confirm-toast-content i {
  color: #f59e0b;
  font-size: 22px;
}

.confirm-toast-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

.confirm-toast-btn {
  border: none;
  border-radius: 8px;
  padding: 8px 12px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
}

.confirm-toast-btn.cancel {
  background: #e5e7eb;
  color: #374151;
}

.confirm-toast-btn.accept {
  background: #4f46e5;
  color: white;
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

  .bulk-actions-bar {
    flex-direction: column;
    align-items: flex-start;
  }

  .bulk-actions-right {
    width: 100%;
    justify-content: flex-start;
  }

  .bulk-btn {
    min-width: 0;
    width: 100%;
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

  .confirm-toast {
    min-width: auto;
    flex-direction: column;
    align-items: stretch;
  }

  .confirm-toast-actions {
    width: 100%;
    justify-content: flex-end;
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