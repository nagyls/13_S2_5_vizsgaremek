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
          <button
            v-if="institutionOwnerId === Number(user.id)"
            class="institution-settings-trigger"
            @click="openInstitutionSettingsModal"
            title="Intézmény adatainak szerkesztése"
          >
            <i class='bx bx-cog'></i>
          </button>
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

        <transition name="modal">
          <div v-if="showInstitutionSettingsModal" class="modal-overlay" @click.self="closeInstitutionSettingsModal">
            <div class="modal-container institution-settings-modal">
              <div class="modal-header">
                <h3>
                  <i class='bx bx-cog'></i>
                  Intézmény beállítások
                </h3>
                <button class="modal-close" @click="closeInstitutionSettingsModal">
                  <i class='bx bx-x'></i>
                </button>
              </div>

              <div class="modal-body">
                <div class="assignment-form">
                  <div class="form-row institution-settings-grid">
                    <div class="form-group institution-settings-field-full">
                      <label>Intézmény neve</label>
                      <input
                        v-model.trim="institutionSettingsForm.title"
                        type="text"
                        class="form-control"
                        maxlength="255"
                        placeholder="Intézmény neve"
                      >
                    </div>

                    <div class="form-group institution-settings-field-full">
                      <label>Cím</label>
                      <input
                        v-model.trim="institutionSettingsForm.address"
                        type="text"
                        class="form-control"
                        maxlength="255"
                        placeholder="Intézmény címe"
                      >
                    </div>

                    <div class="form-group">
                      <label>Email</label>
                      <input
                        v-model.trim="institutionSettingsForm.email"
                        type="email"
                        class="form-control"
                        maxlength="255"
                        placeholder="intezmeny@pelda.hu"
                      >
                    </div>

                    <div class="form-group">
                      <label>Telefonszám</label>
                      <input
                        v-model.trim="institutionSettingsForm.phone"
                        type="text"
                        class="form-control"
                        maxlength="32"
                        placeholder="+36 ..."
                      >
                    </div>

                    <div class="form-group institution-settings-field-full">
                      <label>Weboldal</label>
                      <input
                        v-model.trim="institutionSettingsForm.website"
                        type="url"
                        class="form-control"
                        maxlength="255"
                        placeholder="https://..."
                      >
                    </div>

                    <div class="form-group institution-settings-field-full">
                      <label>Leírás</label>
                      <textarea
                        v-model.trim="institutionSettingsForm.description"
                        class="form-control"
                        rows="3"
                        placeholder="Rövid leírás az intézményről"
                      ></textarea>
                    </div>
                  </div>

                  <div class="class-edit-divider"></div>

                  <div class="ownership-transfer-section">
                    <h4>
                      <i class='bx bx-transfer'></i>
                      Tulajdonjog átadása
                    </h4>
                    <p class="form-hint">
                      Az átadás után ez a beállítási panel már nem lesz elérhető számodra tulajdonosként.
                    </p>

                    <div class="teacher-change-row">
                      <select v-model="selectedNewOwnerUserId" class="form-select" :disabled="isTransferringOwnership">
                        <option value="">-- Válassz új tulajdonost --</option>
                        <option
                          v-for="teacher in ownershipTransferCandidates"
                          :key="teacher.id"
                          :value="String(teacher.id)"
                        >
                          {{ getDisplayName(teacher) }} ({{ teacher.role === 'admin' ? 'admin' : 'tanár' }})
                        </option>
                      </select>
                      <button
                        class="btn-outline"
                        @click="transferInstitutionOwnership"
                        :disabled="!selectedNewOwnerUserId || isTransferringOwnership"
                      >
                        <i class='bx' :class="isTransferringOwnership ? 'bx-loader-circle bx-spin' : 'bx-share-alt'"></i>
                        {{ isTransferringOwnership ? 'Átadás...' : 'Tulajdon átadása' }}
                      </button>
                    </div>
                  </div>

                  <div v-if="institutionSettingsError" class="error-message">
                    <i class='bx bx-error-circle'></i>
                    <span>{{ institutionSettingsError }}</span>
                  </div>
                </div>
              </div>

              <div class="modal-footer">
                <button class="btn-outline" @click="closeInstitutionSettingsModal" :disabled="isSavingInstitutionSettings || isTransferringOwnership">Bezárás</button>
                <button class="btn-primary" @click="saveInstitutionSettings" :disabled="isSavingInstitutionSettings || isTransferringOwnership">
                  <i class='bx bx-save'></i>
                  {{ isSavingInstitutionSettings ? 'Mentés...' : 'Intézmény adatok mentése' }}
                </button>
              </div>
            </div>
          </div>
        </transition>

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
              <label class="join-request-toggle" for="join-request-availability">
                <span class="join-request-toggle-label">Új kérelmek fogadása</span>
                <input
                  id="join-request-availability"
                  type="checkbox"
                  :checked="acceptsJoinRequests"
                  :disabled="isUpdatingJoinRequestAvailability"
                  @change="handleJoinRequestAvailabilityToggle"
                />
                <span class="join-request-toggle-track"></span>
              </label>
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
                    <h4>{{ getDisplayName(request.user) }}</h4>
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
                    <h4>{{ getDisplayName(request.user) }}</h4>
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
                    {{ getDisplayName(teacher) }}
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
                  <h4>{{ getDisplayName(student) }}</h4>
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
                <button
                  class="btn-icon btn-danger"
                  @click="removeStudentFromInstitution(student)"
                  :disabled="removingStudentIds.includes(Number(student.id))"
                  title="Diák eltávolítása az intézményből"
                >
                  <i class='bx bx-user-minus'></i>
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
                  <h4>{{ getDisplayName(teacher) }}</h4>
                  <p class="user-email">{{ teacher.email }}</p>
                  <span v-if="teacher.role === 'admin'" class="role-badge-admin"><i class='bx bx-shield'></i> Admin</span>
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
                <button
                  class="btn-icon btn-danger"
                  @click="removeTeacherFromInstitution(teacher)"
                  :disabled="removingTeacherIds.includes(Number(teacher.id))"
                  title="Tanár eltávolítása az intézményből"
                >
                  <i class='bx bx-user-minus'></i>
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
                <h4>{{ getDisplayName(selectedRequest?.user) }}</h4>
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

    <!-- Tanár osztálymódosító modal -->
    <transition name="modal">
      <div v-if="showEditTeacherClassModal" class="modal-overlay" @click.self="closeEditTeacherClassModal">
        <div class="modal-container">
          <div class="modal-header">
            <h3>
              <i class='bx bx-edit'></i>
              Módosítás
            </h3>
            <button class="modal-close" @click="closeEditTeacherClassModal">
              <i class='bx bx-x'></i>
            </button>
          </div>

          <div class="modal-body">
            <div class="user-summary">
              <div class="user-avatar-large">
                <span>{{ getUserInitials(selectedTeacherForClassEdit) }}</span>
              </div>
              <div class="user-summary-info">
                <h4>{{ selectedTeacherForClassEdit?.name }}</h4>
                <p>{{ selectedTeacherForClassEdit?.email }}</p>
                <div class="role-badge-small">Tanár</div>
              </div>
            </div>

            <div class="assignment-form">
              <div class="form-group">
                <label for="edit-teacher-class-select">Új osztály</label>
                <select
                  id="edit-teacher-class-select"
                  v-model="editTeacherClassId"
                  class="form-select"
                  :disabled="isUpdatingTeacherClass"
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
                  Jelenlegi osztály: {{ editTeacherCurrentClassId ? formatCompactClassDisplayName(classes.find(classItem => Number(classItem.id) === Number(editTeacherCurrentClassId))) : 'Nincs beállítva' }}
                </p>
              </div>
            </div>

            <div v-if="editTeacherClassError" class="error-message">
              <i class='bx bx-error-circle'></i>
              <span>{{ editTeacherClassError }}</span>
            </div>
          </div>

          <div class="modal-footer" style="justify-content: space-between;">
            <button
              v-if="institutionOwnerId === Number(user.id) && Number(selectedTeacherForClassEdit?.id) !== Number(user.id)"
              class="btn-outline"
              :class="selectedTeacherForClassEdit?.role === 'admin' ? 'btn-warning-outline' : 'btn-promote-outline'"
              @click="toggleTeacherRole(selectedTeacherForClassEdit)"
              :disabled="promotingTeacherIds.includes(Number(selectedTeacherForClassEdit?.id))"
              style="margin-right: auto;"
            >
              <i :class="selectedTeacherForClassEdit?.role === 'admin' ? 'bx bx-shield-minus' : 'bx bx-shield-plus'"></i>
              {{ selectedTeacherForClassEdit?.role === 'admin' ? 'Visszaminősítés tanárrá' : 'Jogosultság változtatása' }}
            </button>
            <div style="display:flex; gap: 8px;">
              <button class="btn-outline" @click="closeEditTeacherClassModal" :disabled="isUpdatingTeacherClass">Mégse</button>
              <button class="btn-primary" @click="saveTeacherClassChange" :disabled="isUpdatingTeacherClass">
                <i class='bx bx-save'></i>
                {{ isUpdatingTeacherClass ? 'Mentés...' : 'Mentés' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Osztály szerkesztő modul -->
    <transition name="modal">
      <div v-if="showClassEditModal" class="modal-overlay" @click.self="closeClassEditModal">
        <div class="modal-container class-edit-modal">
          <div class="modal-header">
            <h3>
              <i class='bx bx-cog'></i>
              {{ formatClassDisplayName(editingClass) }} – Szerkesztés
            </h3>
            <button class="modal-close" @click="closeClassEditModal">
              <i class='bx bx-x'></i>
            </button>
          </div>

          <div class="modal-body" v-if="editingClass">
            <div class="class-edit-section">
              <h4 class="class-edit-section-title">
                <i class='bx bx-pencil'></i>
                Osztály adatai
              </h4>

              <div class="class-edit-form-grid">
                <label class="class-edit-field">
                  <span>Évfolyam</span>
                  <input
                    v-model.number="classEditForm.grade"
                    type="number"
                    min="1"
                    max="100"
                    class="form-select"
                    :disabled="classEditIsSavingDetails"
                  >
                </label>

                <label class="class-edit-field">
                  <span>Osztály neve</span>
                  <input
                    v-model="classEditForm.name"
                    type="text"
                    maxlength="5"
                    class="form-select"
                    :disabled="classEditIsSavingDetails"
                    @input="sanitizeClassEditNameInput"
                  >
                </label>

                <label class="class-edit-field class-edit-field-full">
                  <span>Max. létszám</span>
                  <input
                    v-model.number="classEditForm.capacity"
                    type="number"
                    min="1"
                    max="200"
                    class="form-select"
                    :disabled="classEditIsSavingDetails"
                  >
                </label>
              </div>

              <div class="class-edit-actions-row">
                <button
                  class="btn-primary btn-sm"
                  @click="saveClassDetails"
                  :disabled="classEditIsSavingDetails"
                >
                  <i class='bx' :class="classEditIsSavingDetails ? 'bx-loader-circle bx-spin' : 'bx-save'"></i>
                  {{ classEditIsSavingDetails ? 'Mentés...' : 'Osztály adatok mentése' }}
                </button>
              </div>
            </div>

            <div class="class-edit-divider"></div>

            <!-- Osztályfőnök szekció -->
            <div class="class-edit-section">
              <h4 class="class-edit-section-title">
                <i class='bx bx-chalkboard'></i>
                Osztályfőnök
              </h4>
              <div class="current-teacher-display">
                <div v-if="editingClass.user" class="teacher-chip">
                  <div class="teacher-chip-avatar">{{ (editingClass.user).charAt(0).toUpperCase() }}</div>
                  <span>{{ editingClass.user }}</span>
                </div>
                <span v-else class="no-teacher-label">Nincs kijelölt osztályfőnök</span>
              </div>
              <div class="teacher-change-row">
                <select v-model="classEditNewTeacherId" class="form-select" :disabled="classEditIsUpdatingTeacher">
                  <option value="">-- Válassz tanárt --</option>
                  <option v-for="t in teachers" :key="t.id" :value="String(t.id)">
                    {{ getDisplayName(t) }}
                  </option>
                </select>
                <button
                  class="btn-primary btn-sm"
                  @click="saveClassTeacher"
                  :disabled="classEditIsUpdatingTeacher || !classEditNewTeacherId"
                >
                  <i class='bx' :class="classEditIsUpdatingTeacher ? 'bx-loader-circle bx-spin' : 'bx-save'"></i>
                  {{ classEditIsUpdatingTeacher ? 'Mentés...' : 'Mentés' }}
                </button>
              </div>
            </div>

            <div class="class-edit-divider"></div>

            <!-- Diákok szekció -->
            <div class="class-edit-section">
              <h4 class="class-edit-section-title">
                <i class='bx bx-group'></i>
                Osztály tagjai ({{ classEditMembers.length }})
              </h4>

              <div v-if="classEditLoading" class="class-edit-loading">
                <i class='bx bx-loader-circle bx-spin'></i> Betöltés...
              </div>

              <div v-else-if="classEditMembers.length === 0" class="class-edit-empty">
                <i class='bx bx-user-x'></i>
                <span>Még nincsenek diákok ebben az osztályban.</span>
              </div>

              <div v-else class="class-members-list">
                <div v-for="member in classEditMembers" :key="member.id" class="class-member-row">
                  <div class="member-info">
                    <div class="member-avatar">{{ (getDisplayName(member) || '?').charAt(0).toUpperCase() }}</div>
                    <div class="member-text">
                      <span class="member-name">{{ getDisplayName(member) }}</span>
                      <span v-if="member.alias && member.name && member.alias !== member.name" class="member-alias">Felhasználónév: {{ member.name }}</span>
                    </div>
                  </div>
                  <button
                    class="btn-icon btn-remove-student"
                    @click="removeStudentFromClass(member)"
                    :disabled="classEditRemovingStudentId === Number(member.id)"
                    title="Eltávolítás az osztályból"
                  >
                    <i class='bx' :class="classEditRemovingStudentId === Number(member.id) ? 'bx-loader-circle bx-spin' : 'bx-user-minus'"></i>
                  </button>
                </div>
              </div>

              <!-- Diák hozzáadás -->
              <div v-if="!classEditLoading && classEditStudentsNotInClass.length > 0" class="add-students-section">
                <label class="add-students-label">
                  <i class='bx bx-user-plus'></i>
                  Diák hozzáadása az osztályhoz:
                </label>
                <div class="teacher-change-row">
                  <select
                    v-model="classEditAddStudentIds"
                    class="form-select"
                    multiple
                    :disabled="classEditIsAddingStudents"
                    size="4"
                  >
                    <option
                      v-for="s in classEditStudentsNotInClass"
                      :key="s.student_id"
                      :value="s.student_id"
                    >
                      {{ getDisplayName(s) }}{{ s.alias && s.name && s.alias !== s.name ? ` (${s.name})` : '' }}
                    </option>
                  </select>
                  <button
                    class="btn-primary btn-sm"
                    @click="addStudentsToClass"
                    :disabled="classEditIsAddingStudents || !classEditAddStudentIds.length"
                  >
                    <i class='bx' :class="classEditIsAddingStudents ? 'bx-loader-circle bx-spin' : 'bx-user-plus'"></i>
                    {{ classEditIsAddingStudents ? 'Hozzáadás...' : 'Hozzáadás' }}
                  </button>
                </div>
                <p class="form-hint">
                  <i class='bx bx-info-circle'></i>
                  Ctrl+klikkel több diákot is kijelölhetsz.
                </p>
              </div>
              <div v-else-if="!classEditLoading && classEditStudentsNotInClass.length === 0 && students.length > 0" class="form-hint">
                <i class='bx bx-check-circle'></i>
                Minden intézményi diák már tagja ennek az osztálynak.
              </div>
            </div>

            <div v-if="classEditError" class="error-message">
              <i class='bx bx-error-circle'></i>
              <span>{{ classEditError }}</span>
            </div>
          </div>

          <div class="modal-footer">
            <button class="btn-outline" @click="closeClassEditModal">Bezárás</button>
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
  
  /**
   * Komponens belső állapota
   */
  data() {
    return {
      logo2,
      // Bejelentkezett felhasználó adatai
      user: {
        id: null,
        name: '',
        email: '',
        institution_id: null,
        role: 'admin'
      },
      // Intézmény adatai
      institution: {
        id: null,
        name: '',
        address: '',
        type: '',
        description: '',
        website: '',
        email: '',
        phone: ''
      },
      // Statisztikai adatok
      stats: {
        totalStudents: 0,
        totalTeachers: 0,
        totalClasses: 0
      },
      // Globális/Kollaborációs esemény meghívások
      collabEvents: [],
      isLoadingCollabEvents: false,
      processingCollabEventId: null,
      showCollabTargetModal: false,
      selectedCollabEvent: null,
      collabTargetGroup: 'teljes_iskola',
      collabSelectedGradeIds: [],
      collabTargetModalError: '',
      // Csatlakozási kérelmek és felhasználók
      establishmentRequests: [], // Összes kérelem a táblából
      allUsers: [], // Összes felhasználó
      students: [], // Diákok
      teachers: [], // Tanárok
      institutionOwnerId: null, // Az intézmény tulajdonosának user_id-je
      promotingTeacherIds: [], // Folyamatban lévő szerepkörváltás
      classes: [], // Osztályok
      userRoles: {}, // Felhasználók szerepköreinek gyorsítótárazása
      
      activeRequestTab: 'students', // 'students' vagy 'teachers'
      activeUserTab: 'students',
      showUserMenu: false,
      showScrollTop: false,
      
      // Keresés és szűrés
      searchQuery: '',
      acceptsJoinRequests: true,
      isUpdatingJoinRequestAvailability: false,

      selectedRequestIds: [],
      isBulkProcessingRequests: false,
      
      // Modal ablakok állapota
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

      showEditTeacherClassModal: false,
      selectedTeacherForClassEdit: null,
      editTeacherClassId: '',
      editTeacherCurrentClassId: '',
      editTeacherClassError: '',
      isUpdatingTeacherClass: false,

      showInstitutionSettingsModal: false,
      isSavingInstitutionSettings: false,
      isTransferringOwnership: false,
      selectedNewOwnerUserId: '',
      institutionSettingsError: '',
      institutionSettingsForm: {
        title: '',
        address: '',
        email: '',
        phone: '',
        website: '',
        description: ''
      },
      
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
      availableGrades: [9, 10, 11, 12, 13],

      // Osztály szerkesztő modal adatai
      showClassEditModal: false,
      classEditLoading: false,
      editingClass: null,
      classEditForm: {
        name: '',
        grade: null,
        capacity: 30
      },
      classEditMembers: [],
      classEditNewTeacherId: '',
      classEditIsSavingDetails: false,
      classEditIsUpdatingTeacher: false,
      classEditAddStudentIds: [],
      classEditIsAddingStudents: false,
      classEditRemovingStudentId: null,
      classEditError: '',
      removingStudentIds: [],
      removingTeacherIds: []
    };
  },
  
  computed: {
    /**
     * Felhasználói monogram előállítása
     */
    userInitials() {
      return this.user.name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
    },
    
    /**
     * Összes függőben lévő kérelem száma
     */
    totalPendingRequests() {
      return this.establishmentRequests.length;
    },

    /**
     * Függőben lévő globális meghívások száma
     */
    pendingGlobalEventRequests() {
      return this.collabEvents.length;
    },

    /**
     * Tulajdonjog átadására jelölhető tanárok (mindenki, kivéve az aktuális felhasználót)
     */
    ownershipTransferCandidates() {
      return this.teachers.filter(teacher => Number(teacher?.id) !== Number(this.user?.id));
    },

    /**
     * Az intézményben elérhető évfolyamok listája (egyedi értékek)
     */
    collabAvailableGrades() {
      return Array.from(new Set(
        this.classes
          .map(classItem => Number(classItem?.grade))
          .filter(Number.isFinite)
      )).sort((left, right) => left - right);
    },

    /**
     * Az osztályban még nem szereplő diákok szűrése a szerkesztő modalhoz
     */
    classEditStudentsNotInClass() {
      if (!this.editingClass) return [];
      const memberUserIds = new Set(this.classEditMembers.map(m => Number(m.id)));
      return this.students.filter(s => !memberUserIds.has(Number(s.id)));
    },
    
    /**
     * Függőben lévő diák kérelmek
     */
    pendingStudentRequests() {
      return this.establishmentRequests.filter(req => req.role === 'student');
    },
    
    /**
     * Függőben lévő tanár kérelmek
     */
    pendingTeacherRequests() {
      return this.establishmentRequests.filter(req => req.role === 'teacher');
    },
    
    /**
     * Keresés alapján szűrt diák kérelmek listája
     */
    filteredStudentRequests() {
      if (!this.searchQuery) return this.pendingStudentRequests;
      
      const query = this.searchQuery.toLowerCase();
      return this.pendingStudentRequests.filter(request => {
        const user = request.user || this.getUserById(request.user_id);
        const alias = (user?.alias || request?.alias || '').toLowerCase();
        return user && (
          alias.includes(query) ||
          (user.name || '').toLowerCase().includes(query) ||
          (user.email || '').toLowerCase().includes(query)
        );
      });
    },
    
    /**
     * Keresés alapján szűrt tanár kérelmek listája
     */
    filteredTeacherRequests() {
      if (!this.searchQuery) return this.pendingTeacherRequests;
      
      const query = this.searchQuery.toLowerCase();
      return this.pendingTeacherRequests.filter(request => {
        const user = request.user || this.getUserById(request.user_id);
        const alias = (user?.alias || request?.alias || '').toLowerCase();
        return user && (
          alias.includes(query) ||
          (user.name || '').toLowerCase().includes(query) ||
          (user.email || '').toLowerCase().includes(query)
        );
      });
    },

    /**
     * Az aktuálisan aktív fülön látható kérelmek
     */
    visibleRequests() {
      return this.activeRequestTab === 'students'
        ? this.filteredStudentRequests
        : this.filteredTeacherRequests;
    },

    /**
     * A látható kérelmek közül kijelölt elemek listája
     */
    selectedVisibleRequests() {
      const selectedIds = new Set(this.selectedRequestIds.map(id => Number(id)));
      return this.visibleRequests.filter(request => selectedIds.has(Number(request.id)));
    },

    /**
     * Kijelölt látható kérelmek száma
     */
    selectedVisibleRequestCount() {
      return this.selectedVisibleRequests.length;
    },

    /**
     * Ellenőrzi, hogy minden látható kérelem ki van-e jelölve
     */
    allVisibleRequestsSelected() {
      if (!this.visibleRequests.length) {
        return false;
      }

      return this.selectedVisibleRequestCount === this.visibleRequests.length;
    },
    

  },
  
  methods: {
    /**
     * Felhasználó objektum lekérése az összes felhasználó közül ID alapján
     */
    getUserById(userId) {
      return this.allUsers.find(u => u.id === userId) || null;
    },
    
    /**
     * Felhasználó monogramjának lekérése
     */
    getUserInitials(user) {
      const displayName = this.getDisplayName(user);
      if (!displayName) return '?';
      return displayName
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
    },

    /**
     * Megjelenítendő név lekérése (alias előnyben részesítése a valós névvel szemben)
     */
    getDisplayName(user) {
      const alias = (user?.alias || '').toString().trim();
      if (alias !== '') {
        return alias;
      }

      return (user?.name || '').toString().trim();
    },
    
    /**
     * Dátum formázása magyar lokalizáció szerint
     */
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

    /**
     * Osztály megnevezésének formázása (pl. "9.A")
     */
    formatClassDisplayName(classItem) {
      const grade = classItem?.grade;
      const className = (classItem?.name || '').toString().trim();

      if (grade && className) {
        return `${grade}.${className}`;
      }

      return className || 'Névtelen osztály';
    },

    /**
     * Osztály megnevezésének tömör formázása
     */
    formatCompactClassDisplayName(classItem) {
      const grade = classItem?.grade;
      const className = (classItem?.name || '').toString().trim();

      if (grade && className) {
        return `${grade}.${className}`;
      }

      return className || 'Nincs beállítva';
    },

    /**
     * Diák osztályának megjelenítése
     */
    getStudentClassDisplay(student) {
      if (!student) return 'Nincs beállítva';

      const className = (student.class_name || '').toString().trim();
      if (className) {
        return className;
      }

      return 'Nincs beállítva';
    },

    /**
     * Tanár által vitt osztályok listájának megjelenítése
     */
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

    /**
     * Osztály maximális kapacitásának lekérése
     */
    getClassCapacity(classItem) {
      const capacity = Number(classItem?.capacity);

      if (Number.isFinite(capacity) && capacity > 0) {
        return capacity;
      }

      return 30;
    },

    /**
     * Osztálynév bevitelének tisztítása (csak betűk engedélyezése)
     */
    sanitizeClassNameInput() {
      this.newClass.name = String(this.newClass.name || '')
        .replace(/[^a-zA-ZáéíóöőúüűÁÉÍÓÖŐÚÜŰ]/g, '')
        .slice(0, 5);
    },

    /**
     * Osztálynév bevitelének tisztítása szerkesztéskor
     */
    sanitizeClassEditNameInput() {
      this.classEditForm.name = String(this.classEditForm.name || '')
        .replace(/[^a-zA-ZáéíóöőúüűÁÉÍÓÖŐÚÜŰ]/g, '')
        .slice(0, 5);
    },

    /**
     * Felhasználói menü láthatóságának váltása
     */
    toggleUserMenu() {
      this.showUserMenu = !this.showUserMenu;
    },

    /**
     * Megerősítő kérdés megjelenítése toast segítségével
     */
    askForConfirmation(message) {
      return toast.confirm(message);
    },

    /**
     * Kérelem azonosítójának normalizálása
     */
    normalizeRequestId(request) {
      return Number(request?.id ?? request?.request_id);
    },

    /**
     * Ellenőrzi, hogy egy adott kérelem ki van-e jelölve
     */
    isRequestSelected(request) {
      const requestId = this.normalizeRequestId(request);
      if (!requestId) {
        return false;
      }

      return this.selectedRequestIds.includes(requestId);
    },

    /**
     * Egy kérelem kijelölésének váltása
     */
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

    /**
     * Minden látható kérelem kijelölése vagy kijelölés megszüntetése
     */
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

    /**
     * Kijelölések törlése
     */
    clearRequestSelection() {
      this.selectedRequestIds = [];
    },

    /**
     * Kijelölések szinkronizálása a valóban létező függő kérelmekkel
     */
    syncRequestSelectionWithPendingList() {
      const pendingIds = new Set(this.establishmentRequests.map(request => this.normalizeRequestId(request)));
      this.selectedRequestIds = this.selectedRequestIds.filter(id => pendingIds.has(Number(id)));
    },

    /**
     * Több kérelem együttes feldolgozása (elfogadás/elutasítás)
     */
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

    /**
     * Csatlakozási kérelmek fogadásának ki/bekapcsolása
     */
    handleJoinRequestAvailabilityToggle(event) {
      const nextValue = Boolean(event?.target?.checked);
      this.updateJoinRequestAvailability(nextValue);
    },

    /**
     * Frissíti a backend-en a csatlakozási kérelmek fogadásának állapotát
     */
    async updateJoinRequestAvailability(nextValue) {
      try {
        const token =
          localStorage.getItem('esemenyter_token') ||
          sessionStorage.getItem('esemenyter_token');
        const institutionId = Number(this.user?.institution_id);

        if (!token || !institutionId) {
          this.showNotification('Hiányzó hitelesítés vagy intézmény azonosító.', 'error');
          return;
        }

        this.isUpdatingJoinRequestAvailability = true;

        const response = await axios.patch(
          `http://127.0.0.1:8000/api/establishment/${institutionId}/join-requests/availability`,
          { accepts_join_requests: nextValue },
          {
            headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' }
          }
        );

        this.acceptsJoinRequests = Boolean(response?.data?.accepts_join_requests);
        this.showNotification(
          response?.data?.message || 'A csatlakozási kérelmek fogadása sikeresen frissítve.',
          'success'
        );
      } catch (error) {
        this.showNotification(
          error?.response?.data?.message || 'Nem sikerült frissíteni a csatlakozási kérelmek fogadását.',
          'error'
        );
      } finally {
        this.isUpdatingJoinRequestAvailability = false;
      }
    },
    
    /**
     * Összes intézményi adat betöltése (alap adatok, kérelmek, felhasználók, osztályok)
     */
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

        // Intézményi alap adatok lekérése
        const instResponse = await axios.get(`http://127.0.0.1:8000/api/establishment/${institutionId}`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        const institutionData = instResponse.data.data || instResponse.data || {};
        this.institution = {
          ...this.institution,
          id: institutionData.id || Number(institutionId),
          name: institutionData.name || institutionData.title || '',
          address: institutionData.address || '',
          type: institutionData.type || '',
          description: institutionData.description || '',
          website: institutionData.website || '',
          email: institutionData.email || '',
          phone: institutionData.phone || ''
        };
        this.institutionOwnerId = Number(institutionData.user_id) || null;

        // Csatlakozási kérelem fogadás állapotának lekérése
        const joinRequestAvailabilityResponse = await axios.get(
          `http://127.0.0.1:8000/api/establishment/${institutionId}/join-requests/availability`,
          {
            headers: { Authorization: `Bearer ${token}` }
          }
        );

        this.acceptsJoinRequests = Boolean(joinRequestAvailabilityResponse?.data?.accepts_join_requests);

        // Függő kérelmek lekérése (külön végponton a diákok és tanárok)
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

        // Kérelmek feldolgozása a komponens formátumára
        this.establishmentRequests = allRequests.map(request => {
          const resolvedRequestId = request.id ?? request.request_id ?? null;
          const resolvedUserId = request.user_id ?? request.user?.id ?? null;
          const resolvedName = (request.user?.name || request.name || '').toString().trim();
          const resolvedEmail = (request.user?.email || request.email || '').toString().trim();
          const resolvedAlias = (request.user?.alias || request.alias || '').toString().trim();

          return {
            ...request,
            id: resolvedRequestId,
            user_id: resolvedUserId,
            alias: resolvedAlias,
            user: request.user || {
              id: resolvedUserId,
              name: resolvedName || `Felhasználó #${resolvedUserId ?? '?'}`,
              email: resolvedEmail,
              alias: resolvedAlias,
            }
          };
        });
        this.syncRequestSelectionWithPendingList();

        // További entitások betöltése
        await this.loadCollabEvents(institutionId);
        await this.loadInstitutionUsers(institutionId);
        await this.loadClasses(institutionId);
        await this.loadStudentClassAssignments(institutionId);

        // Statisztikai összegzés
        this.updateStats();

      } catch (error) {
        console.error('Hiba az adatok betöltésekor:', error);
        this.showNotification('Hiba történt az adatok betöltésekor', 'error');
      }
    },

    /**
     * Intézmény beállítási modal megnyitása és űrlap előtöltése
     */
    openInstitutionSettingsModal() {
      this.institutionSettingsError = '';
      this.selectedNewOwnerUserId = '';
      this.institutionSettingsForm = {
        title: this.institution.name || '',
        address: this.institution.address || '',
        email: this.institution.email || '',
        phone: this.institution.phone || '',
        website: this.institution.website || '',
        description: this.institution.description || ''
      };
      this.showInstitutionSettingsModal = true;
    },

    /**
     * Intézmény beállítási modal bezárása és ideiglenes állapot törlése
     */
    closeInstitutionSettingsModal() {
      this.showInstitutionSettingsModal = false;
      this.institutionSettingsError = '';
      this.selectedNewOwnerUserId = '';
    },

    /**
     * Intézményi adatok mentése a backend felé
     */
    async saveInstitutionSettings() {
      const establishmentId = Number(this.user?.institution_id);
      const token = localStorage.getItem('esemenyter_token') || sessionStorage.getItem('esemenyter_token');

      if (!establishmentId || !token) {
        this.institutionSettingsError = 'Hiányzó hitelesítés vagy intézmény azonosító.';
        return;
      }

      this.isSavingInstitutionSettings = true;
      this.institutionSettingsError = '';

      try {
        const payload = {
          title: (this.institutionSettingsForm.title || '').trim(),
          address: (this.institutionSettingsForm.address || '').trim() || null,
          email: (this.institutionSettingsForm.email || '').trim() || null,
          phone: (this.institutionSettingsForm.phone || '').trim() || null,
          website: (this.institutionSettingsForm.website || '').trim() || null,
          description: (this.institutionSettingsForm.description || '').trim() || null,
        };

        await axios.patch(
          `http://127.0.0.1:8000/api/establishment/${establishmentId}/details`,
          payload,
          { headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' } }
        );

        this.institution = {
          ...this.institution,
          name: payload.title,
          address: payload.address || '',
          email: payload.email || '',
          phone: payload.phone || '',
          website: payload.website || '',
          description: payload.description || '',
        };

        this.showNotification('Intézmény adatok mentve.', 'success');
      } catch (error) {
        const msg = error?.response?.data?.message || 'Nem sikerült menteni az intézmény adatait.';
        this.institutionSettingsError = msg;
        this.showNotification(msg, 'error');
      } finally {
        this.isSavingInstitutionSettings = false;
      }
    },

    /**
     * Tulajdonjog átadása a kiválasztott tanárnak
     */
    async transferInstitutionOwnership() {
      const establishmentId = Number(this.user?.institution_id);
      const newOwnerUserId = Number(this.selectedNewOwnerUserId);
      const token = localStorage.getItem('esemenyter_token') || sessionStorage.getItem('esemenyter_token');

      if (!establishmentId || !newOwnerUserId || !token) {
        this.institutionSettingsError = 'Hiányzó adat a tulajdon átadásához.';
        return;
      }

      const targetTeacher = this.teachers.find(t => Number(t?.id) === newOwnerUserId);
      const targetName = this.getDisplayName(targetTeacher || { name: `Felhasználó #${newOwnerUserId}` });

      const confirmed = await this.askForConfirmation(
        `Biztosan átadod a tulajdonjogot neki: ${targetName}? Ezután már nem te leszel a tulajdonos.`
      );
      if (!confirmed) {
        return;
      }

      this.isTransferringOwnership = true;
      this.institutionSettingsError = '';

      try {
        await axios.patch(
          `http://127.0.0.1:8000/api/establishment/${establishmentId}/ownership`,
          { new_owner_user_id: newOwnerUserId },
          { headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' } }
        );

        this.institutionOwnerId = newOwnerUserId;
        await this.loadInstitutionUsers(establishmentId);

        this.showNotification('Tulajdonjog sikeresen átadva.', 'success');
        this.closeInstitutionSettingsModal();
      } catch (error) {
        const msg = error?.response?.data?.message || 'Nem sikerült átadni a tulajdonjogot.';
        this.institutionSettingsError = msg;
        this.showNotification(msg, 'error');
      } finally {
        this.isTransferringOwnership = false;
      }
    },

    /**
     * Globális esemény meghívások betöltése az intézményhez
     */
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

    /**
     * Célcsoport választó modal megnyitása egy globális eseményhez
     */
    async openCollabTargetModal(eventItem) {
      const establishmentId = Number(this.user?.institution_id);

      if (!establishmentId) {
        this.showNotification('Hiányzó intézmény azonosító.', 'error');
        return;
      }

      this.selectedCollabEvent = eventItem;
      this.collabTargetGroup = 'teljes_iskola';
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

    /**
     * Célcsoport választó modal bezárása és állapot alaphelyzetbe állítása
     */
    closeCollabTargetModal() {
      this.showCollabTargetModal = false;
      this.selectedCollabEvent = null;
      this.collabTargetGroup = 'teljes_iskola';
      this.collabSelectedGradeIds = [];
      this.collabTargetModalError = '';
    },

    /**
     * Elfogadáskor értesítendő felhasználói lista előállítása a választott célcsoport alapján
     */
    resolveCollabAcceptanceUserIds() {
      const classesById = new Map(
        this.classes.map(classItem => [Number(classItem.id), classItem])
      );

      if (this.collabTargetGroup === 'teljes_iskola') {
        return this.resolveInstitutionUserIdsForCollabAcceptance();
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

    /**
     * Felhasználói azonosítók normalizálása, duplikációk kiszűrése és saját felhasználó hozzáadása
     */
    normalizeUserIdsForCollabAcceptance(userIds) {
      const normalizedUserIds = (userIds || []).map(userId => Number(userId)).filter(Number.isFinite);
      const currentUserId = Number(this.user?.id);

      if (Number.isFinite(currentUserId)) {
        normalizedUserIds.push(currentUserId);
      }

      return Array.from(new Set(normalizedUserIds));
    },

    /**
     * Globális esemény elfogadása a modalban kiválasztott célcsoporttal
     */
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

    /**
     * Globális esemény meghívás elfogadása vagy elutasítása
     */
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

    /**
     * Teljes intézményi felhasználói azonosító lista előállítása globális elfogadáshoz
     */
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
    
    /**
     * Intézményhez tartozó diákok és tanárok betöltése
     */
    async loadInstitutionUsers(institutionId) {
      try {
        const token =
          localStorage.getItem('esemenyter_token') ||
          sessionStorage.getItem('esemenyter_token');
        
        const studentsResponse = await axios.get(`http://127.0.0.1:8000/api/establishment/${institutionId}/members/students`, {
          headers: token ? { Authorization: `Bearer ${token}` } : {}
        });
        this.students = studentsResponse.data.data || [];
      
        const teachersResponse = await axios.get(`http://127.0.0.1:8000/api/establishment/${institutionId}/members/staff`, {
          headers: token ? { Authorization: `Bearer ${token}` } : {}
        });
        this.teachers = teachersResponse.data.data || [];
      
      } catch (error) {
        console.error('Hiba az intézmény felhasználóinak betöltésekor:', error);
      }
    },
    
    /**
     * Osztályok betöltése intézmény azonosító alapján
     */
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

    /**
     * Diákok osztály-hozzárendeléseinek szinkronizálása az osztálytagságok alapján
     */
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
    
    /**
     * Statisztikai számlálók frissítése a betöltött listák alapján
     */
    updateStats() {
      this.stats = {
        totalStudents: this.students.length,
        totalTeachers: this.teachers.length,
        totalClasses: this.classes.length
      };
    },
    
    /**
     * Új osztály létrehozása validálás után
     */
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
    
    /**
     * Osztály szerkesztő modal megnyitása a kiválasztott osztállyal
     */
    async editClass(classItem) {
      this.editingClass = { ...classItem };
      this.classEditForm = {
        name: String(classItem?.name || ''),
        grade: Number(classItem?.grade) || null,
        capacity: this.getClassCapacity(classItem)
      };
      this.classEditNewTeacherId = String(classItem.user_id || '');
      this.classEditMembers = [];
      this.classEditAddStudentIds = [];
      this.classEditError = '';
      this.showClassEditModal = true;
      await this.loadClassEditMembers();
    },

    /**
     * Osztály szerkesztő modal bezárása és állapot törlése
     */
    closeClassEditModal() {
      this.showClassEditModal = false;
      this.editingClass = null;
      this.classEditForm = {
        name: '',
        grade: null,
        capacity: 30
      };
      this.classEditMembers = [];
      this.classEditNewTeacherId = '';
      this.classEditAddStudentIds = [];
      this.classEditError = '';
    },

    /**
     * Osztály adatainak mentése (név, évfolyam, kapacitás)
     */
    async saveClassDetails() {
      const establishmentId = Number(this.user?.institution_id);
      const classId = Number(this.editingClass?.id);
      const grade = Number(this.classEditForm.grade);
      const capacity = Number(this.classEditForm.capacity);
      const name = String(this.classEditForm.name || '').trim();

      if (!name) {
        this.classEditError = 'Az osztály neve kötelező.';
        return;
      }

      if (!/^[a-zA-ZáéíóöőúüűÁÉÍÓÖŐÚÜŰ]+$/.test(name)) {
        this.classEditError = 'Az osztály neve csak betűket tartalmazhat.';
        return;
      }

      if (!Number.isInteger(grade) || grade < 1 || grade > 100) {
        this.classEditError = 'Az évfolyamnak 1 és 100 közötti egész számnak kell lennie.';
        return;
      }

      if (!Number.isInteger(capacity) || capacity < 1 || capacity > 200) {
        this.classEditError = 'A létszámnak 1 és 200 közötti egész számnak kell lennie.';
        return;
      }

      try {
        this.classEditIsSavingDetails = true;
        this.classEditError = '';

        const token = localStorage.getItem('esemenyter_token') || sessionStorage.getItem('esemenyter_token');

        await axios.patch(
          `http://127.0.0.1:8000/api/establishment/${establishmentId}/classes/${classId}`,
          {
            name,
            grade,
            capacity
          },
          { headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' } }
        );

        this.editingClass.name = name;
        this.editingClass.grade = grade;
        this.editingClass.capacity = capacity;

        const localClass = this.classes.find(c => Number(c.id) === classId);
        if (localClass) {
          localClass.name = name;
          localClass.grade = grade;
          localClass.capacity = capacity;
        }

        await this.loadClasses(establishmentId);
        await this.loadStudentClassAssignments(establishmentId);
        this.updateStats();
        this.showNotification('Osztály adatai sikeresen frissítve!', 'success');
      } catch (error) {
        const apiErrors = error?.response?.data?.errors;
        const apiErrorText = apiErrors && typeof apiErrors === 'object'
          ? Object.values(apiErrors).flat().join(' ')
          : null;

        this.classEditError = error?.response?.data?.message || apiErrorText || 'Nem sikerült frissíteni az osztály adatait.';
      } finally {
        this.classEditIsSavingDetails = false;
      }
    },

    /**
     * Szerkesztett osztály tagjainak betöltése
     */
    async loadClassEditMembers() {
      const establishmentId = Number(this.user?.institution_id);
      const classId = Number(this.editingClass?.id);
      try {
        this.classEditLoading = true;
        this.classEditError = '';
        const token = localStorage.getItem('esemenyter_token') || sessionStorage.getItem('esemenyter_token');
        const response = await axios.get(
          `http://127.0.0.1:8000/api/establishment/${establishmentId}/classes/${classId}`,
          { headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' } }
        );
        this.classEditMembers = response.data.data || [];
      } catch (error) {
        this.classEditError = 'Nem sikerült betölteni az osztály tagjait.';
      } finally {
        this.classEditLoading = false;
      }
    },

    /**
     * Osztályfőnök mentése a szerkesztett osztályhoz
     */
    async saveClassTeacher() {
      const teacherId = Number(this.classEditNewTeacherId);
      if (!teacherId) {
        this.classEditError = 'Válassz osztályfőnököt!';
        return;
      }
      const establishmentId = Number(this.user?.institution_id);
      const classId = Number(this.editingClass?.id);
      try {
        this.classEditIsUpdatingTeacher = true;
        this.classEditError = '';
        const token = localStorage.getItem('esemenyter_token') || sessionStorage.getItem('esemenyter_token');
        await axios.patch(
          `http://127.0.0.1:8000/api/establishment/${establishmentId}/classes/${classId}`,
          { teacher_id: teacherId },
          { headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' } }
        );
        const teacher = this.teachers.find(t => Number(t.id) === teacherId);
        this.editingClass.user_id = teacherId;
        this.editingClass.user = teacher?.name || null;
        const localClass = this.classes.find(c => Number(c.id) === classId);
        if (localClass) {
          localClass.user_id = teacherId;
          localClass.user = teacher?.name || null;
        }
        this.showNotification('Osztályfőnök sikeresen frissítve!', 'success');
      } catch (error) {
        this.classEditError = error?.response?.data?.message || 'Nem sikerült frissíteni az osztályfőnököt.';
      } finally {
        this.classEditIsUpdatingTeacher = false;
      }
    },

    /**
     * Kiválasztott diákok hozzáadása az aktuálisan szerkesztett osztályhoz
     */
    async addStudentsToClass() {
      if (!this.classEditAddStudentIds.length) {
        this.classEditError = 'Válassz legalább egy diákot!';
        return;
      }
      const establishmentId = Number(this.user?.institution_id);
      const classId = Number(this.editingClass?.id);
      try {
        this.classEditIsAddingStudents = true;
        this.classEditError = '';
        const token = localStorage.getItem('esemenyter_token') || sessionStorage.getItem('esemenyter_token');
        await axios.patch(
          'http://127.0.0.1:8000/api/establishment/classes/add-students',
          {
            establishment_id: establishmentId,
            class_id: classId,
            student_id: this.classEditAddStudentIds.map(Number)
          },
          { headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' } }
        );
        this.classEditAddStudentIds = [];
        await this.loadClasses(establishmentId);
        await this.loadClassEditMembers();
        await this.loadStudentClassAssignments(establishmentId);
        this.updateStats();
        this.showNotification('Diák(ok) sikeresen hozzáadva!', 'success');
      } catch (error) {
        const msg = error?.response?.data?.message || error?.response?.data?.errors;
        this.classEditError = typeof msg === 'string' ? msg : 'Nem sikerült hozzáadni a diákokat.';
      } finally {
        this.classEditIsAddingStudents = false;
      }
    },

    /**
     * Diák eltávolítása az aktuálisan szerkesztett osztályból
     */
    async removeStudentFromClass(member) {
      const establishmentId = Number(this.user?.institution_id);
      const classId = Number(this.editingClass?.id);
      const found = this.students.find(s => Number(s.id) === Number(member.id));
      if (!found?.student_id) {
        this.classEditError = 'A diák azonosítója nem található.';
        return;
      }
      try {
        this.classEditRemovingStudentId = Number(member.id);
        this.classEditError = '';
        const token = localStorage.getItem('esemenyter_token') || sessionStorage.getItem('esemenyter_token');
        await axios.patch(
          'http://127.0.0.1:8000/api/establishment/classes/remove-students',
          {
            establishment_id: establishmentId,
            class_id: classId,
            student_id: [found.student_id]
          },
          { headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' } }
        );
        await this.loadClasses(establishmentId);
        this.classEditMembers = this.classEditMembers.filter(m => Number(m.id) !== Number(member.id));
        await this.loadStudentClassAssignments(establishmentId);
        this.updateStats();
        this.showNotification('Diák eltávolítva az osztályból.', 'success');
      } catch (error) {
        this.classEditError = error?.response?.data?.message || 'Nem sikerült eltávolítani a diákot.';
      } finally {
        this.classEditRemovingStudentId = null;
      }
    },
    
    /**
     * Osztály törlése megerősítés után
     */
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
    
    /**
     * Kérelem hozzárendelési modal megnyitása és alapadatok előkészítése
     */
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
    
    /**
     * Hozzárendelési modal bezárása
     */
    closeAssignmentModal() {
      this.showAssignmentModal = false;
      this.selectedRequest = null;
      this.selectedClassId = '';
    },

    /**
     * Diák jelenlegi osztályazonosítójának meghatározása osztálytagság alapján
     */
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

    /**
     * Tanár jelenlegi osztályazonosítójának meghatározása
     */
    async findTeacherCurrentClassId(teacherUserId) {
      if (!teacherUserId || !this.classes.length) {
        return '';
      }

      const foundClass = this.classes.find(classItem => Number(classItem.user_id) === Number(teacherUserId));
      return foundClass ? String(foundClass.id) : '';
    },

    /**
     * Diák osztálymódosító modal bezárása és állapot törlése
     */
    async closeEditUserClassModal() {
      this.showEditStudentClassModal = false;
      this.selectedStudentForClassEdit = null;
      this.editStudentClassId = '';
      this.editStudentCurrentClassId = '';
      this.editStudentClassError = '';
      this.isUpdatingStudentClass = false;
    },

    /**
     * Tanár osztálymódosító modal bezárása és állapot törlése
     */
    async closeEditTeacherClassModal() {
      this.showEditTeacherClassModal = false;
      this.selectedTeacherForClassEdit = null;
      this.editTeacherClassId = '';
      this.editTeacherCurrentClassId = '';
      this.editTeacherClassError = '';
      this.isUpdatingTeacherClass = false;
    },
    
    /**
     * Egyedi csatlakozási kérelem elfogadása opcionális osztály-hozzárendeléssel
     */
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
    
    /**
     * Egyedi csatlakozási kérelem elutasítása
     */
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

    /**
     * Kijelölt kérelmek tömeges elfogadása
     */
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

    /**
     * Kijelölt kérelmek tömeges elutasítása
     */
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
    
    /**
     * Diák osztálymódosító modal megnyitása
     */
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

    /**
     * Diák osztályváltoztatás mentése
     */
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

    /**
     * Diák eltávolítása az intézményből
     */
    async removeStudentFromInstitution(student) {
      const studentUserId = Number(student?.id);
      const studentRecordId = Number(student?.student_id);
      const establishmentId = Number(this.user?.institution_id);

      if (!studentUserId || !studentRecordId || !establishmentId) {
        this.showNotification('Hiányzó diák vagy intézmény azonosító.', 'error');
        return;
      }

      const isConfirmed = await this.askForConfirmation(`Biztosan eltávolítod ${this.getDisplayName(student)} diákot az intézményből?`);
      if (!isConfirmed) {
        return;
      }

      const token = localStorage.getItem('esemenyter_token') || sessionStorage.getItem('esemenyter_token');

      this.removingStudentIds = [...this.removingStudentIds, studentUserId];

      try {
        await axios.patch('http://127.0.0.1:8000/api/establishment/members/remove-students', {
          establishment_id: establishmentId,
          student_id: [studentRecordId]
        }, {
          headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' }
        });

        await this.loadInstitutionUsers(establishmentId);
        await this.loadClasses(establishmentId);
        await this.loadStudentClassAssignments(establishmentId);
        this.updateStats();

        this.showNotification('Diák sikeresen eltávolítva az intézményből.', 'success');
      } catch (error) {
        const message = error?.response?.data?.message || error?.response?.data?.errors || 'Nem sikerült eltávolítani a diákot.';
        this.showNotification(typeof message === 'string' ? message : 'Nem sikerült eltávolítani a diákot.', 'error');
      } finally {
        this.removingStudentIds = this.removingStudentIds.filter(id => id !== studentUserId);
      }
    },
    
    /**
     * Tanár osztálymódosító modal megnyitása
     */
    async editTeacherClasses(teacher) {
      try {
        this.selectedTeacherForClassEdit = teacher;
        this.editTeacherClassError = '';
        this.editTeacherClassId = '';
        this.editTeacherCurrentClassId = '';
        this.showEditTeacherClassModal = true;

        const currentClassId = await this.findTeacherCurrentClassId(teacher.id);
        this.editTeacherCurrentClassId = currentClassId;
        this.editTeacherClassId = currentClassId;
      } catch (error) {
        console.error('Hiba a tanár osztálymódosító modal megnyitásakor:', error);
        this.showNotification('Nem sikerült betölteni a tanár jelenlegi osztályát.', 'error');
      }
    },

    /**
     * Tanár osztályváltoztatás mentése
     */
    async saveTeacherClassChange() {
      try {
        if (this.isUpdatingTeacherClass) {
          return;
        }

        const teacher = this.selectedTeacherForClassEdit;
        if (!teacher?.id) {
          this.editTeacherClassError = 'A tanár azonosítója hiányzik, frissítsd az oldalt és próbáld újra.';
          return;
        }

        const token =
          localStorage.getItem('esemenyter_token') ||
          sessionStorage.getItem('esemenyter_token');
        const establishmentId = Number(this.user.institution_id);

        if (!token || !establishmentId) {
          this.editTeacherClassError = 'Hiányzó hitelesítés vagy intézmény azonosító.';
          return;
        }

        this.isUpdatingTeacherClass = true;
        this.editTeacherClassError = '';

        const currentClassId = this.editTeacherCurrentClassId ? Number(this.editTeacherCurrentClassId) : null;
        const targetClassId = this.editTeacherClassId ? Number(this.editTeacherClassId) : null;

        if (currentClassId === targetClassId) {
          this.showNotification('Nem történt változás az osztálynál.', 'info');
          await this.closeEditTeacherClassModal();
          return;
        }

        if (currentClassId && currentClassId !== targetClassId) {
          await axios.patch(`http://127.0.0.1:8000/api/establishment/${establishmentId}/classes/${currentClassId}`, {
            teacher_id: null
          }, {
            headers: { Authorization: `Bearer ${token}` }
          });
        }

        if (targetClassId) {
          await axios.patch(`http://127.0.0.1:8000/api/establishment/${establishmentId}/classes/${targetClassId}`, {
            teacher_id: Number(teacher.id)
          }, {
            headers: { Authorization: `Bearer ${token}` }
          });
        }

        await this.loadClasses(establishmentId);
        await this.loadInstitutionUsers(establishmentId);
        this.updateStats();

        await this.closeEditTeacherClassModal();
        this.showNotification('Tanár osztálya sikeresen módosítva.', 'success');
      } catch (error) {
        console.error('Hiba a tanár osztályának módosításakor:', error);
        const apiErrors = error.response?.data?.errors;
        const apiErrorText = typeof apiErrors === 'string'
          ? apiErrors
          : Array.isArray(apiErrors)
            ? apiErrors.join(' ')
            : null;

        this.editTeacherClassError = error.response?.data?.message || apiErrorText || 'Nem sikerült menteni az osztály módosítását.';
        this.showNotification('Nem sikerült menteni a tanár osztályát.', 'error');
      } finally {
        this.isUpdatingTeacherClass = false;
      }
    },

    /**
     * Tanár jogosultsági szintjének váltása (tanár/admin)
     */
    async toggleTeacherRole(teacher) {
      const staffId = Number(teacher?.staff_id)
      const teacherUserId = Number(teacher?.id)
      const establishmentId = Number(this.user?.institution_id)

      if (!staffId || !establishmentId) {
        this.showNotification('Hiányzó azonosító.', 'error')
        return
      }

      const newRole = teacher.role === 'admin' ? 'teacher' : 'admin'
      const roleLabel = newRole === 'admin' ? 'adminisztrátor' : 'tanár'
      const confirmed = await this.askForConfirmation(
        `Biztosan módosítod ${this.getDisplayName(teacher)} szerepkörét ${roleLabel}-ra?`
      )
      if (!confirmed) return

      const token = localStorage.getItem('esemenyter_token') || sessionStorage.getItem('esemenyter_token')
      this.promotingTeacherIds = [...this.promotingTeacherIds, teacherUserId]

      try {
        await axios.patch(
          `http://127.0.0.1:8000/api/establishment/${establishmentId}/staff/${staffId}/role`,
          { role: newRole },
          { headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' } }
        )
        await this.loadInstitutionUsers(establishmentId)
        this.showNotification(`Szerepkör sikeresen frissítve: ${roleLabel}.`, 'success')
      } catch (error) {
        const msg = error?.response?.data?.message || 'Hiba a szerepkör módosításakor.'
        this.showNotification(msg, 'error')
      } finally {
        this.promotingTeacherIds = this.promotingTeacherIds.filter(id => id !== teacherUserId)
      }
    },

    /**
     * Tanár eltávolítása az intézményből
     */
    async removeTeacherFromInstitution(teacher) {
      const teacherUserId = Number(teacher?.id);
      const establishmentId = Number(this.user?.institution_id);

      if (!teacherUserId || !establishmentId) {
        this.showNotification('Hiányzó tanár vagy intézmény azonosító.', 'error');
        return;
      }

      if (teacherUserId === Number(this.user?.id)) {
        this.showNotification('Saját magadat nem távolíthatod el.', 'warning');
        return;
      }

      const isConfirmed = await this.askForConfirmation(`Biztosan eltávolítod ${this.getDisplayName(teacher)} tanárt az intézményből?`);
      if (!isConfirmed) {
        return;
      }

      const token = localStorage.getItem('esemenyter_token') || sessionStorage.getItem('esemenyter_token');

      this.removingTeacherIds = [...this.removingTeacherIds, teacherUserId];

      try {
        await axios.patch('http://127.0.0.1:8000/api/establishment/members/remove-staff', {
          establishment_id: establishmentId,
          user_id: [teacherUserId]
        }, {
          headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' }
        });

        await this.loadInstitutionUsers(establishmentId);
        await this.loadClasses(establishmentId);
        await this.loadStudentClassAssignments(establishmentId);
        this.updateStats();

        this.showNotification('Tanár sikeresen eltávolítva az intézményből.', 'success');
      } catch (error) {
        const message = error?.response?.data?.message || error?.response?.data?.errors || 'Nem sikerült eltávolítani a tanárt.';
        this.showNotification(typeof message === 'string' ? message : 'Nem sikerült eltávolítani a tanárt.', 'error');
      } finally {
        this.removingTeacherIds = this.removingTeacherIds.filter(id => id !== teacherUserId);
      }
    },
    
    /**
     * Egységes toast értesítés megjelenítése
     */
    showNotification(message, type = 'success') {
      const safeType = ['success', 'error', 'warning', 'info'].includes(type) ? type : 'info';
      toast[safeType](message, 3500);
    },
    
    /**
     * Oldal tetejére görgetés
     */
    scrollToTop() {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    
    /**
     * Görgetési állapot kezelése a lebegő gombhoz
     */
    handleScroll() {
      this.showScrollTop = window.scrollY > 300;
    },

    /**
     * Külső kattintás figyelése a felhasználói menü bezárásához
     */
    handleDocumentClick(e) {
      if (!e.target.closest('.user-profile')) {
        this.showUserMenu = false;
      }
    },
    
    /**
     * Kijelentkezés és kliensoldali session adatok törlése
     */
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
    
    /**
     * Bejelentkezési állapot ellenőrzése és jogosultság alapú átirányítás
     */
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
                const numericInstitutionId = Number(storedInstitutionId || this.user.institution_id || 0);
                if (!Number.isFinite(numericInstitutionId) || numericInstitutionId <= 0) {
                  return;
                }

                const roleResponse = await axios.get(`http://127.0.0.1:8000/api/establishment/${numericInstitutionId}/role`, {
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

    document.addEventListener('click', this.handleDocumentClick);
  },
  
  beforeUnmount() {
    window.removeEventListener('scroll', this.handleScroll);
    document.removeEventListener('click', this.handleDocumentClick);
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

/* Fő elrendezés és háttér */
.institution-dashboard {
  min-height: 100vh;
  background: linear-gradient(to top, #5873eb, rgba(0,0,0,0.4));
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  width: 100%;
}

/* Reszponzív konténer */
.container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 30px;
}

/* Header stílusok */
.main-header {
  background: linear-gradient(180deg, rgba(8, 14, 30, 0.92) 0%, rgba(11, 20, 42, 0.82) 100%);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(148, 163, 184, 0.18);
  box-shadow: 0 6px 18px rgba(2, 6, 23, 0.22);
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
  background: linear-gradient(145deg, rgba(255, 255, 255, 0.16), rgba(148, 163, 184, 0.08));
  border: 1px solid rgba(191, 219, 254, 0.28);
  box-shadow: 0 10px 20px rgba(2, 6, 23, 0.28);
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
  color: #f8fafc;
  text-shadow: 0 2px 10px rgba(15, 23, 42, 0.42);
  margin: 0;
}

.site-subtitle {
  margin: 0;
  font-size: 14px;
  color: rgba(226, 232, 240, 0.84);
  font-weight: 500;
}

/* Felhasználói profil szekció */
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
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(191, 219, 254, 0.24);
  transition: background 0.3s ease, border-color 0.3s ease;
}

.user-avatar:hover {
  background: rgba(255, 255, 255, 0.16);
  border-color: rgba(191, 219, 254, 0.42);
}

.avatar-circle {
  width: 45px;
  height: 45px;
  background: linear-gradient(135deg, #4f46e5, #3b82f6);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 18px;
  box-shadow: 0 8px 18px rgba(37, 99, 235, 0.4);
}

.user-status {
  position: relative;
}

.status-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  border: 2px solid rgba(8, 14, 30, 0.92);
  position: absolute;
  bottom: 2px;
  right: 2px;
}

.status-dot.online {
  background: #10b981;
  box-shadow: 0 0 0 2px rgba(8, 14, 30, 0.92);
}

/* Felhasználói legördülő menü */
.user-menu {
  position: absolute;
  top: 60px;
  right: 0;
  width: 280px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  z-index: 9999;
}

.menu-header {
  padding: 20px;
  background: linear-gradient(150deg, #5873eb, rgb(0 0 0));
  border-bottom: 1px solid #e5e7eb;
}

.menu-user-info h4 {
  margin: 0 0 5px 0;
  color: #e4e2e2;
  font-size: 16px;
}

.user-email {
  margin: 0;
  color: #b8b8b8;
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

/* Fő tartalom szekció */
.main-content {
  padding: 40px 0;
}

/* Intézmény információs kártya */
.institution-info-card {
  background: white;
  border-radius: 24px;
  padding: 30px;
  margin-bottom: 30px;
  display: flex;
  align-items: center;
  gap: 30px;
  position: relative;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.institution-settings-trigger {
  position: absolute;
  top: 18px;
  right: 18px;
  width: 40px;
  height: 40px;
  border-radius: 12px;
  border: 1px solid #dbeafe;
  background: #eff6ff;
  color: #4f46e5;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.institution-settings-trigger i {
  font-size: 20px;
}

.institution-settings-trigger:hover {
  background: #4f46e5;
  color: #ffffff;
  border-color: #4f46e5;
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

.institution-settings-modal {
  max-width: 700px;
}

.institution-settings-grid {
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
}

.institution-settings-field-full {
  grid-column: 1 / -1;
}

.ownership-transfer-section h4 {
  margin: 0 0 8px 0;
  font-size: 16px;
  color: #374151;
  display: flex;
  align-items: center;
  gap: 8px;
}

.ownership-transfer-section h4 i {
  color: #4f46e5;
}

/* Szekció fejlécek */
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

/* Keresés stílusok */
.header-actions {
  display: flex;
  gap: 15px;
  align-items: center;
}

.join-request-toggle {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  user-select: none;
}

.join-request-toggle-label {
  font-size: 14px;
  font-weight: 600;
  color: #374151;
}

.join-request-toggle input {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}

.join-request-toggle-track {
  position: relative;
  width: 44px;
  height: 24px;
  border-radius: 999px;
  background: #d1d5db;
  transition: background-color 0.2s ease;
}

.join-request-toggle-track::after {
  content: '';
  position: absolute;
  top: 3px;
  left: 3px;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: #ffffff;
  transition: transform 0.2s ease;
}

.join-request-toggle input:checked + .join-request-toggle-track {
  background: #4f46e5;
}

.join-request-toggle input:checked + .join-request-toggle-track::after {
  transform: translateX(20px);
}

.join-request-toggle input:disabled + .join-request-toggle-track {
  opacity: 0.6;
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

/* Kérelem kezelő fülek */
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

/* Kérelem kártyák szekció */
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

.btn-icon.btn-danger {
  color: #dc2626;
  border-color: #fecaca;
  background: #fff5f5;
}

.btn-icon.btn-danger:hover:not(:disabled) {
  background: #dc2626;
  color: #ffffff;
  border-color: #dc2626;
}

.btn-icon.btn-promote {
  color: #4f46e5;
  border-color: #c7d2fe;
  background: #eef2ff;
}

.btn-icon.btn-promote:hover:not(:disabled) {
  background: #4f46e5;
  color: #ffffff;
  border-color: #4f46e5;
}

.btn-icon.btn-warning {
  color: #d97706;
  border-color: #fde68a;
  background: #fffbeb;
}

.btn-icon.btn-warning:hover:not(:disabled) {
  background: #d97706;
  color: #ffffff;
  border-color: #d97706;
}

.btn-promote-outline {
  color: #4f46e5;
  border-color: #c7d2fe;
  background: #eef2ff;
}

.btn-promote-outline:hover:not(:disabled) {
  background: #4f46e5;
  color: #ffffff;
}

.btn-warning-outline {
  color: #d97706;
  border-color: #fde68a;
  background: #fffbeb;
}

.btn-warning-outline:hover:not(:disabled) {
  background: #d97706;
  color: #ffffff;
}

.role-badge-admin {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: #eef2ff;
  color: #4f46e5;
  border: 1px solid #c7d2fe;
  border-radius: 50px;
  padding: 2px 8px;
  font-size: 11px;
  font-weight: 600;
  margin-top: 4px;
}

.btn-icon:disabled {
  cursor: not-allowed;
  opacity: 0.6;
  transform: none;
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

/* Csatlakozott felhasználók szekció */
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

/* Üres állapotok stílusai */
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

/* Modal ablakok */
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
  overflow: hidden;
  display: flex;
  flex-direction: column;
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
  overflow-y: auto;
  flex: 1 1 auto;
  min-height: 0;
}

.modal-header,
.modal-footer {
  flex-shrink: 0;
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

/* Lebegő műveleti gomb */
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
  .main-header {
    padding: 12px 0;
  }

  .container {
    padding: 0 20px;
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

  .institution-info-card {
    flex-direction: column;
    text-align: center;
    padding-top: 56px;
  }

  .institution-settings-trigger {
    top: 12px;
    right: 12px;
  }

  .institution-stats {
    flex-wrap: wrap;
    justify-content: center;
  }

  .request-tabs {
    flex-direction: column;
  }

  .header-actions {
    width: 100%;
    flex-direction: column;
    align-items: stretch;
  }

  .join-request-toggle {
    justify-content: space-between;
  }

  .bulk-actions-bar {
    flex-direction: column;
    align-items: flex-start;
  }

  .bulk-actions-right {
    width: 100%;
    justify-content: stretch;
    display: grid;
    grid-template-columns: 1fr;
    gap: 8px;
  }

  .bulk-btn {
    min-width: 0;
    width: 100%;
  }

  .bulk-selected-count {
    width: 100%;
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

  .requests-section {
    padding: 20px;
  }

  .requests-grid {
    grid-template-columns: 1fr;
    gap: 14px;
  }

  .request-actions-inline {
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
    max-height: 92vh;
  }

  .modal-body {
    padding: 18px;
  }

  .modal-header {
    padding: 14px 18px;
  }

  .modal-footer {
    padding: 14px 18px;
  }

  .class-edit-form-grid {
    grid-template-columns: 1fr;
  }

  .institution-settings-grid {
    grid-template-columns: 1fr;
  }

  .class-edit-field-full {
    grid-column: 1;
  }

  .teacher-change-row {
    flex-direction: column;
  }

  .teacher-change-row .btn-sm {
    width: 100%;
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
  .main-header {
    padding: 8px 0;
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

  .request-card {
    padding: 15px;
  }

  .requests-section {
    padding: 14px;
  }

  .bulk-actions-bar {
    padding: 10px;
  }

  .bulk-select-all {
    font-size: 14px;
  }

  .bulk-select-all input {
    width: 20px;
    height: 20px;
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

/* Osztály szerkesztő modal */
.class-edit-modal {
  max-width: 560px;
}

.class-edit-section {
  margin-bottom: 8px;
}

.class-edit-form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
  margin-bottom: 14px;
}

.class-edit-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.class-edit-field span {
  font-size: 13px;
  font-weight: 600;
  color: #4b5563;
}

.class-edit-field-full {
  grid-column: 1 / -1;
}

.class-edit-actions-row {
  display: flex;
  justify-content: flex-end;
}

.class-edit-section-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 15px;
  font-weight: 600;
  color: #374151;
  margin: 0 0 14px 0;
}

.class-edit-section-title i {
  color: #4f46e5;
  font-size: 18px;
}

.class-edit-divider {
  height: 1px;
  background: #e5e7eb;
  margin: 20px 0;
}

.current-teacher-display {
  margin-bottom: 12px;
}

.teacher-chip {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #f0f4ff;
  border: 1px solid #c7d2fe;
  border-radius: 50px;
  padding: 6px 14px 6px 6px;
  color: #334155;
  font-weight: 600;
}

.teacher-chip-avatar {
  width: 28px;
  height: 28px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: 13px;
}

.no-teacher-label {
  font-size: 13px;
  color: #9ca3af;
  font-style: italic;
}

.teacher-change-row {
  display: flex;
  gap: 10px;
  align-items: flex-start;
}

.teacher-change-row .form-select {
  flex: 1;
  min-width: 0;
}

.btn-sm {
  padding: 8px 14px;
  font-size: 13px;
  white-space: nowrap;
  flex-shrink: 0;
}

.class-edit-loading {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #6b7280;
  font-size: 14px;
  padding: 12px 0;
}

.class-edit-empty {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #9ca3af;
  font-size: 14px;
  padding: 12px 0;
}

.class-members-list {
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin-bottom: 16px;
  max-height: 200px;
  overflow-y: auto;
}

.class-member-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 12px;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  transition: background 0.2s;
}

.class-member-row:hover {
  background: #f0f4ff;
}

.member-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.member-avatar {
  width: 32px;
  height: 32px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: 13px;
  flex-shrink: 0;
}

.member-text {
  display: flex;
  flex-direction: column;
}

.member-name {
  font-size: 14px;
  font-weight: 500;
  color: #374151;
}

.member-alias {
  font-size: 12px;
  color: #9ca3af;
}

.btn-remove-student {
  color: #ef4444;
  border: 1px solid #fecaca;
  background: #fff5f5;
  border-radius: 8px;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  flex-shrink: 0;
}

.btn-remove-student:hover:not(:disabled) {
  background: #fee2e2;
  border-color: #ef4444;
}

.btn-remove-student:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.add-students-section {
  margin-top: 14px;
  border-top: 1px dashed #e5e7eb;
  padding-top: 14px;
}

.add-students-label {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 14px;
  font-weight: 500;
  color: #374151;
  margin-bottom: 10px;
}

.add-students-label i {
  color: #4f46e5;
}

select[multiple].form-select {
  min-height: 90px;
  padding: 6px;
}
</style>