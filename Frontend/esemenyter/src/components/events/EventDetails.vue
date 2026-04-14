<template>
  <div class="event-details">
    <div class="container">
      <!-- Navigáció -->
      <div class="navigation">
        <button class="btn-back" @click="$router.back()">
          <i class='bx bx-arrow-back'></i>
          <span><-- Vissza</span>
        </button>
      </div>

      <!-- Betöltés állapot -->
      <div v-if="isLoading" class="status-card loading">
        <div class="loader">
          <div class="spinner"></div>
        </div>
        <h3>Esemény betöltése...</h3>
        <p>Kérlek várj, amíg betöltjük az esemény részleteit</p>
      </div>

      <!-- Hiba állapot -->
      <div v-else-if="errorMessage" class="status-card error-state">
        <div class="error-icon">
          <i class='bx bx-error-circle'></i>
        </div>
        <h3>Hiba történt</h3>
        <p class="error-message">{{ errorMessage }}</p>
        <div class="error-actions">
          <button @click="$router.back()" class="btn btn-secondary">
            <i class='bx bx-arrow-back'></i> Vissza
          </button>
          <button @click="$router.push('/esemenyek')" class="btn btn-primary">
            <i class='bx bx-grid'></i> Összes esemény
          </button>
        </div>
      </div>

      <!-- Esemény tartalom -->
      <div v-else-if="eventData" class="event-content">
        <!-- Hero szekció -->
        <div class="hero-section">
          <div class="hero-overlay"></div>
          <div class="hero-content">
            <div class="hero-badges">
              <span class="badge" :class="eventData.type">
                <i class='bx' :class="eventData.type === 'local' ? 'bx-building' : 'bx-world'"></i>
                {{ eventData.type === 'local' ? 'Helyi esemény' : 'Globális esemény' }}
              </span>
              <span class="badge" :class="eventData.status">
                <i class='bx' :class="eventData.status === 'open' ? 'bx-calendar-event' : 'bx-calendar-x'"></i>
                {{ eventData.status === 'open' ? 'Aktív' : 'Lezárva' }}
              </span>
            </div>
            <h1 class="hero-title">{{ eventData.title }}</h1>
            <div v-if="!isReadOnlyMode" class="hero-actions">
              <button
                v-if="currentUser"
                class="icon-button"
                :class="{ 'active': eventData?.isFavorite }"
                @click="toggleFavorite"
              >
                <i class='bx' :class="eventData?.isFavorite ? 'bxs-star active' : 'bx-star'"></i>
                <span class="btn-text">Kedvenc</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Fő tartalom rács -->
        <div class="content-grid">
          <!-- Bal oldali oszlop -->
          <div class="left-column">
            <!-- Szervező infok -->
            <div class="info-block">
              <div class="block-header">
                <i class='bx bx-user'></i>
                <h2>Szervező</h2>
              </div>
              <div class="organizer-profile">
                <div class="profile-avatar">
                  {{ eventData.creator_name?.charAt(0) || '?' }}
                </div>
                <div class="profile-data">
                  <span class="profile-name">{{ eventData.creator_name || 'Ismeretlen' }}</span>
                  <span class="profile-type">Esemény szervező</span>
                </div>
              </div>
            </div>

            <!-- Leírás -->
            <div class="info-block">
              <div class="block-header">
                <i class='bx bx-detail'></i>
                <h2>Leírás</h2>
              </div>
              <p class="description">{{ eventData.description }}</p>
            </div>

            <!-- Részletes tartalom -->
            <div v-if="eventData.content" class="info-block details-block">
              <div class="block-header">
                <i class='bx bx-file'></i>
                <h2>Részletek</h2>
              </div>
              <div class="detailed-content">{{ eventData.content }}</div>
            </div>

            <div v-if="canCreatePoll" class="info-block poll-builder-block">
              <div class="block-header">
                <i class='bx bx-bar-chart-alt-2'></i>
                <h2>Szavazás létrehozása</h2>
              </div>

              <div class="poll-form">
                <div class="poll-field">
                  <label for="poll-title">Kérdés</label>
                  <input id="poll-title" v-model="pollForm.title" type="text" maxlength="255" placeholder="Pl. Melyik időpont legyen megfelelő?">
                </div>

                <div class="poll-settings-wrapper">
                  <div class="poll-checkbox-grid">
                    <label class="poll-checkbox-field">
                      <input v-model="pollForm.isTimed" type="checkbox" @change="handlePollTimingChange">
                      <span>Időzített szavazás</span>
                    </label>

                    <label class="poll-checkbox-field">
                      <input v-model="pollForm.hiddenResults" type="checkbox">
                      <span>Eredmények csak lezárás után látszanak</span>
                    </label>
                  </div>

                  <div v-if="pollForm.isTimed" class="poll-field poll-deadline-field">
                    <label>Lezárás napja</label>
                    <input v-model="pollForm.deadline" type="date">
                  </div>
                </div>

                <div class="poll-field">
                  <label>Opciók</label>
                  <div class="poll-option-inputs">
                    <div v-for="(option, index) in pollForm.options" :key="`poll-option-${index}`" class="poll-option-input-row">
                      <input
                        v-model="pollForm.options[index]"
                        type="text"
                        maxlength="255"
                        :placeholder="`Opció ${index + 1}`"
                      >
                      <button
                        type="button"
                        class="poll-option-remove"
                        :disabled="pollForm.options.length <= 2"
                        @click="removePollOptionField(index)"
                      >
                        <i class='bx bx-trash'></i>
                      </button>
                    </div>
                  </div>
                  <button type="button" class="poll-option-add" :disabled="pollForm.options.length >= 10" @click="addPollOptionField">
                    <i class='bx bx-plus'></i>
                    Opció hozzáadása
                  </button>
                </div>

                <div class="poll-actions">
                  <button class="btn btn-primary poll-submit-button" :disabled="isCreatingPoll" @click="createPoll">
                    <i class='bx' :class="isCreatingPoll ? 'bx-loader-circle bx-spin' : 'bx-check-circle'"></i>
                    {{ isCreatingPoll ? 'Létrehozás...' : 'Szavazás létrehozása' }}
                  </button>
                </div>
              </div>
            </div>

            <div v-if="canViewPolls" class="info-block poll-results-block">
              <div class="block-header">
                <i class='bx bx-poll'></i>
                <h2>Szavazások</h2>
              </div>

              <div v-if="isPollLoading" class="poll-info-note">
                <i class='bx bx-loader-circle bx-spin'></i>
                Szavazások betöltése...
              </div>

              <div v-else-if="!polls.length" class="poll-info-note">
                <i class='bx bx-info-circle'></i>
                Ehhez az eseményhez még nincs szavazás.
              </div>

              <div v-else class="poll-list">
                <div v-for="poll in polls" :key="poll.id" class="poll-panel">
                  <div class="poll-panel-header">
                    <div>
                      <div class="poll-question">{{ poll.title }}</div>
                      <div class="poll-meta-row">
                        <span class="poll-pill">{{ getPollStateLabel(poll) }}</span>
                        <span v-if="poll.is_timed" class="poll-pill muted">Időzített</span>
                        <span v-if="poll.hidden_results" class="poll-pill muted">Rejtett eredmény</span>
                      </div>
                    </div>

                    <button
                      v-if="poll.can_manage && !poll.is_ended"
                      type="button"
                      class="poll-stop-button"
                      :disabled="Boolean(stoppingPollIds[poll.id])"
                      @click="stopPoll(poll)"
                    >
                      <i class='bx' :class="stoppingPollIds[poll.id] ? 'bx-loader-circle bx-spin' : 'bx-stop-circle'"></i>
                      {{ stoppingPollIds[poll.id] ? 'Lezárás...' : 'Szavazás lezárása' }}
                    </button>
                  </div>

                  <div class="poll-summary-grid">
                    <div class="poll-summary-item">
                      <span class="poll-summary-label">Indulás</span>
                      <span>{{ formatPollCalendarDate(poll.start_date) }}</span>
                    </div>
                    <div class="poll-summary-item">
                      <span class="poll-summary-label">Lezárás</span>
                      <span>{{ poll.is_timed ? formatPollCalendarDate(poll.deadline) : 'Kézi lezárás' }}</span>
                    </div>
                    <div class="poll-summary-item">
                      <span class="poll-summary-label">Összes szavazat</span>
                      <span>{{ poll.results_visible ? (poll.total_votes || 0) : 'Rejtve' }}</span>
                    </div>
                  </div>

                  <div v-if="!poll.is_started" class="poll-info-note">
                    <i class='bx bx-time-five'></i>
                    A szavazás még nem indult el.
                  </div>

                  <div v-else-if="poll.hidden_results && !poll.results_visible" class="poll-info-note">
                    <i class='bx bx-lock-alt'></i>
                    Az eredmények csak a szavazás lezárása után válnak láthatóvá.
                  </div>

                  <div class="poll-option-list">
                    <button
                      v-for="option in poll.options"
                      :key="option.id"
                      type="button"
                      class="poll-option-card"
                      :class="{
                        selected: Number(selectedPollOptionIds[poll.id]) === Number(option.id),
                        voted: Number(poll.selected_option_id) === Number(option.id)
                      }"
                      :disabled="!poll.can_answer || Boolean(submittingPollVoteIds[poll.id])"
                      @click="selectedPollOptionIds = { ...selectedPollOptionIds, [poll.id]: Number(option.id) }"
                    >
                      <div class="poll-option-header">
                        <span class="poll-option-title">{{ option.title }}</span>
                        <span v-if="Number(poll.selected_option_id) === Number(option.id)" class="poll-badge">A te szavazatod</span>
                      </div>

                      <div v-if="poll.results_visible" class="poll-result-row">
                        <div class="poll-result-bar-track">
                          <div
                            class="poll-result-bar-fill"
                            :style="{ width: `${getPollResultPercentage(poll, option.id)}%` }"
                          ></div>
                        </div>
                        <div class="poll-result-meta">
                          <span>{{ getPollResultVotes(poll, option.id) }} szavazat</span>
                          <span>{{ getPollResultPercentage(poll, option.id) }}%</span>
                        </div>
                      </div>
                    </button>
                  </div>

                  <div v-if="poll.can_answer" class="poll-actions">
                    <button
                      class="btn btn-primary poll-submit-button"
                      :disabled="!selectedPollOptionIds[poll.id] || Boolean(submittingPollVoteIds[poll.id])"
                      @click="submitPollVote(poll)"
                    >
                      <i class='bx' :class="submittingPollVoteIds[poll.id] ? 'bx-loader-circle bx-spin' : 'bx-send'"></i>
                      {{ submittingPollVoteIds[poll.id] ? 'Szavazás...' : 'Szavazok' }}
                    </button>
                  </div>

                  <div v-else-if="poll.has_answered" class="poll-info-note success">
                    <i class='bx bx-check-circle'></i>
                    Már leadtad a szavazatodat ehhez a szavazáshoz.
                  </div>

                  <div v-else-if="poll.is_ended" class="poll-info-note success">
                    <i class='bx bx-flag'></i>
                    Ez a szavazás lezárult.
                  </div>
                </div>
              </div>
            </div>

            <!-- Kép -->
            <div v-if="eventData.image_url" class="info-block">
              <div class="block-header">
                <i class='bx bx-image'></i>
                <h2>Galéria</h2>
              </div>
              <div class="image-container">
                <img :src="eventData.image_url" :alt="eventData.title">
              </div>
            </div>

            <!-- Komment szekció -->
            <div v-if="canShowCommentSection" class="comment-section">
              <div class="comment-header">
                <div class="header-left">
                  <i class='bx bx-message-dots'></i>
                  <h2>Hozzászólások</h2>
                </div>

                <div class="comment-header-right">
                  <button
                    v-if="canManageOccurrence"
                    class="comment-toggle-inline"
                    :disabled="isTogglingChat"
                    @click="toggleEventComments"
                  >
                    <i class='bx' :class="isTogglingChat ? 'bx-loader-circle bx-spin' : (isEventChatEnabled ? 'bx-message-x' : 'bx-message-check')"></i>
                    <span>{{ isEventChatEnabled ? 'Kommentek kikapcsolása' : 'Kommentek bekapcsolása' }}</span>
                  </button>

                  <div class="comment-counter">
                    <span>{{ commentCount }}</span>
                    <span>komment</span>
                  </div>
                </div>
              </div>
              
              <CommentBox 
                :esemenyId="parseInt(eventId)"
                :aktualisFelhasznalo="currentUser"
                @komment-sikeres="onCommentAdded"
              />
            </div>

            <div v-else-if="showCommentPlaceholder" class="comment-section comment-section-placeholder">
              <div class="comment-header">
                <div class="header-left">
                  <i class='bx bx-message-dots'></i>
                  <h2>Hozzászólások</h2>
                </div>

                <div class="comment-header-right">
                  <button
                    v-if="canManageOccurrence"
                    class="comment-toggle-inline"
                    :disabled="isTogglingChat"
                    @click="toggleEventComments"
                  >
                    <i class='bx' :class="isTogglingChat ? 'bx-loader-circle bx-spin' : (isEventChatEnabled ? 'bx-message-x' : 'bx-message-check')"></i>
                    <span>{{ isEventChatEnabled ? 'Kommentek kikapcsolása' : 'Kommentek bekapcsolása' }}</span>
                  </button>

                  <div class="comment-counter">
                    <span>{{ commentCount }}</span>
                    <span>komment</span>
                  </div>
                </div>
              </div>

              <div class="comment-lock-message">
                <i class='bx bx-info-circle'></i>
                <h3>{{ commentPlaceholderTitle }}</h3>
                <p>{{ commentPlaceholderDescription }}</p>
              </div>
            </div>

          </div>

          <!-- Jobb oldali oszlop -->
          <div class="right-column">
            <!-- Dátum és idő -->
            <div class="info-card">
              <h3><i class='bx bx-calendar'></i> Időpontok</h3>
              <div class="date-list">
                <div class="date-item">
                  <span class="date-label">Kezdés</span>
                  <span class="date-value">{{ formatDateLikeCreation(eventData.start_date) }}</span>
                </div>
                <div class="date-item">
                  <span class="date-label">Befejezés</span>
                  <span class="date-value">{{ formatDateLikeCreation(eventData.end_date) }}</span>
                </div>
              </div>
            </div>

            <!-- Statisztika -->
            <div class="info-card">
              <h3><i class='bx bx-stats'></i> Statisztika</h3>
              <div class="stats-grid">
                <div class="stat-item stat-elem">
                  <div class="stat-ikon">
                    <i class='bx bx-check-circle'></i>
                  </div>
                  <div class="stat-data">
                    <span class="stat-number">{{ attendingCount }}</span>
                    <span class="stat-label">Részt vesz</span>
                  </div>
                </div>
                <div class="stat-item stat-elem">
                  <div class="stat-ikon">
                    <i class='bx bx-x-circle'></i>
                  </div>
                  <div class="stat-data">
                    <span class="stat-number">{{ notAttendingCount }}</span>
                    <span class="stat-label">Nem vesz részt</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Részvétel -->
            <div v-if="!isReadOnlyMode && eventData.status === 'open' && currentUser" class="info-card participation">
              <h3><i class='bx bx-check-shield'></i> Részvétel</h3>
              <p class="participation-description">{{ isFormal ? 'Hogyan szeretne részt venni az eseményen?' : 'Hogyan szeretnél részt venni az eseményen?' }}</p>
              <div class="participation-options">
                <button @click="submitParticipation('y')" 
                        class="answer-button attending" 
                        :class="{ 'active': userParticipation === 'y' }">
                  <i class='bx bx-check-circle'></i>
                  <div class="answer-content">
                    <span class="answer-title">Részvétel</span>
                    <span class="answer-description">Részt veszek az eseményen</span>
                  </div>
                </button>
                <button @click="submitParticipation('n')" 
                        class="answer-button not-attending" 
                        :class="{ 'active': userParticipation === 'n' }">
                  <i class='bx bx-x-circle'></i>
                  <div class="answer-content">
                    <span class="answer-title">Nem veszek részt</span>
                    <span class="answer-description">Nem tudok jelen lenni</span>
                  </div>
                </button>
              </div>
            </div>

            <div v-if="canManageOccurrence" class="info-card occurrence-manager">
              <h3><i class='bx bx-wrench'></i> Alkalom kezelése</h3>
              <p class="participation-description">Létrehozóként módosíthatod az adott alkalom időpontját, vagy elmaradtként törölheted.</p>

              <div class="occurrence-form-grid">
                <label>
                  Kezdés dátuma
                  <input type="date" v-model="occurrenceForm.startDate" @input="updateOccurrenceStartDateTime" @change="updateOccurrenceStartDateTime">
                </label>
                <label>
                  Kezdés időpontja
                  <input
                    :type="isFirefoxBrowser ? 'text' : 'time'"
                    v-model="occurrenceForm.startTime"
                    inputmode="numeric"
                    placeholder="HH:MM"
                    pattern="^([01]?\d|2[0-3]):[0-5]\d$"
                    @input="updateOccurrenceStartDateTime"
                    @change="updateOccurrenceStartDateTime"
                    @blur="normalizeOccurrenceTime('startTime', updateOccurrenceStartDateTime)"
                  >
                </label>
                <label>
                  Befejezés dátuma
                  <input type="date" v-model="occurrenceForm.endDate" @input="updateOccurrenceEndDateTime" @change="updateOccurrenceEndDateTime">
                </label>
                <label>
                  Befejezés időpontja
                  <input
                    :type="isFirefoxBrowser ? 'text' : 'time'"
                    v-model="occurrenceForm.endTime"
                    inputmode="numeric"
                    placeholder="HH:MM"
                    pattern="^([01]?\d|2[0-3]):[0-5]\d$"
                    @input="updateOccurrenceEndDateTime"
                    @change="updateOccurrenceEndDateTime"
                    @blur="normalizeOccurrenceTime('endTime', updateOccurrenceEndDateTime)"
                  >
                </label>
              </div>

              <div class="occurrence-actions">
                <button
                  class="answer-button attending"
                  :disabled="isUpdatingOccurrence"
                  @click="rescheduleOccurrence"
                >
                  <i class='bx bx-time-five'></i>
                  <div class="answer-content">
                    <span class="answer-title">Időpont módosítása</span>
                    <span class="answer-description">Az adott alkalom átütemezése</span>
                  </div>
                </button>

                <button
                  class="answer-button not-attending"
                  :disabled="isUpdatingOccurrence"
                  @click="cancelOccurrence"
                >
                  <i class='bx bx-calendar-x'></i>
                  <div class="answer-content">
                    <span class="answer-title">Alkalom elmarad</span>
                    <span class="answer-description">Az adott alkalom törlése</span>
                  </div>
                </button>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import CommentBox from './CommentBox.vue';
import { toast } from '../../services/toast'
import { API_BASE, getToken, getAuthHeaders } from '../../services/api'

export default {
  name: 'EventDetails',
  
  components: {
    CommentBox
  },
  
  data() {
    return {
      eventId: this.$route.params.id,
      eventData: null,
      currentUser: null,
      isLoading: true,
      errorMessage: '',
      commentCount: 0,
      attendingCount: 0,
      notAttendingCount: 0,
      favoriteCount: 0,
      userParticipation: null,
      studentHasClass: null,
      polls: [],
      isPollLoading: false,
      isCreatingPoll: false,
      submittingPollVoteIds: {},
      stoppingPollIds: {},
      selectedPollOptionIds: {},
      pollForm: {
        title: '',
        deadline: '',
        isTimed: false,
        hiddenResults: false,
        options: ['', '']
      },
      isTogglingChat: false,
      isUpdatingOccurrence: false,
      isFirefoxBrowser: false,
      occurrenceForm: {
        startDate: '',
        startTime: '',
        endDate: '',
        endTime: '',
        startDateTime: '',
        endDateTime: ''
      }
    }
  },
  
  async created() {
    this.isFirefoxBrowser = typeof navigator !== 'undefined' && /firefox/i.test(navigator.userAgent || '')
    await this.loadCurrentUser()
    await this.loadEvent()
  },
  
  computed: {
    isFormal() {
      return this.currentUser?.role === 'admin' || this.currentUser?.role === 'teacher';
    },

    isReadOnlyMode() {
      return String(this.$route?.query?.readonly || '') === '1'
    },

    canManageOccurrence() {
      return !this.isReadOnlyMode && Number(this.currentUser?.id) > 0 && Number(this.eventData?.user_id) === Number(this.currentUser?.id)
    },

    isEventCreator() {
      return !this.isReadOnlyMode && Number(this.currentUser?.id) > 0 && Number(this.eventData?.user_id) === Number(this.currentUser?.id)
    },

    canCreatePoll() {
      return this.isEventCreator
    },

    canViewPolls() {
      return this.isEventCreator || this.userParticipation === 'y'
    },

    hasParticipationDecision() {
      return this.userParticipation === 'y' || this.userParticipation === 'n'
    },

    isEventChatEnabled() {
      return this.eventData?.chat_enabled !== false
    },

    canShowCommentSection() {
      return !this.isReadOnlyMode
        && this.isEventChatEnabled
        && Boolean(this.currentUser)
        && this.userParticipation === 'y'
    },

    showCommentPlaceholder() {
      return !this.isReadOnlyMode
        && Boolean(this.currentUser)
        && !this.canShowCommentSection
    },

    commentPlaceholderTitle() {
      if (!this.isEventChatEnabled) {
        return 'Kommentelés kikapcsolva'
      }

      if (!this.hasParticipationDecision) {
        return 'Kommentelés még nem elérhető'
      }

      return 'Kommentelés nem elérhető'
    },

    commentPlaceholderDescription() {
      if (!this.isEventChatEnabled) {
        return 'A kommentelés ki van kapcsolva ennél az eseménynél. A szervező bármikor visszakapcsolhatja.'
      }

      if (!this.hasParticipationDecision) {
        return 'Először jelöld be, hogy részt veszel-e az eseményen, és utána megjelenik a kommentbox.'
      }

      return 'Kommentelni csak akkor tudsz, ha a részvételnél a Részvétel opciót választod.'
    }
  },
  
  methods: {
    backToEvents() {
      this.$router.push('/events-list')
    },

    getCurrentInstitutionId() {
      const storedInstitutionId =
        localStorage.getItem('CurrentInstitution') ||
        sessionStorage.getItem('CurrentInstitution') ||
        this.currentUser?.institution_id ||
        this.currentUser?.establishment_id

      const institutionId = Number(storedInstitutionId)
      return Number.isFinite(institutionId) && institutionId > 0 ? institutionId : null
    },

    normalizeEventStatus(status) {
      const normalized = String(status || '').toLowerCase()

      if (normalized === 'ongoing' || normalized === 'open' || normalized === 'upcoming') {
        return 'open'
      }

      if (normalized === 'ended' || normalized === 'closed') {
        return 'closed'
      }

      return 'open'
    },

    normalizeEvent(event) {
      const rawChatEnabled = event?.chat_enabled
      const chatEnabled = rawChatEnabled === undefined || rawChatEnabled === null
        ? true
        : !(rawChatEnabled === false || rawChatEnabled === 0 || rawChatEnabled === '0')

      return {
        ...event,
        status: this.normalizeEventStatus(event?.status),
        creator_name: event?.creator_name || event?.creator?.name || event?.user?.name || 'Ismeretlen szervező',
        participants: Number(event?.participants || event?.participant_count || 0),
        favorites: Number(event?.favorites || event?.favorite_count || 0),
        comment_count: Number(event?.comment_count || event?.comments_count || 0),
        isFavorite: Boolean(event?.isFavorite || event?.is_favorite || event?.is_favourite),
        chat_enabled: chatEnabled
      }
    },

    async loadEvent() {
      try {
        this.isLoading = true
        this.errorMessage = ''
        const foundEvent = await this.fetchEvent(this.eventId)
        
        if (!foundEvent) {
          this.errorMessage = 'A kiválasztott esemény nem létezik vagy elérhetetlen.'
          return
        }
        
        this.eventData = foundEvent
        this.splitDateTimeParts(foundEvent.start_date, 'start')
        this.splitDateTimeParts(foundEvent.end_date, 'end')
        await Promise.all([
          this.loadStats(),
          this.loadParticipationStatus(),
          this.loadPolls()
        ])
      } catch (error) {
        console.error('Hiba az esemény betöltésekor:', error)
        this.errorMessage = 'Nem sikerült betölteni az esemény adatait. Kérlek próbáld újra később.'
      } finally {
        this.isLoading = false
      }
    },
    
    async fetchEvent(eventId) {
      const token = getToken()
      const institutionId = this.getCurrentInstitutionId()

      if (!token || !institutionId) {
        return null
      }

      const endpoint = `${API_BASE}/establishment/${institutionId}/events`
      const response = await axios.get(endpoint, {
        headers: getAuthHeaders(token),
        validateStatus: (status) => status >= 200 && status < 600
      })

      if (response.status >= 400) {
        return null
      }

      const payload = response.data
      const events = Array.isArray(payload)
        ? payload
        : Array.isArray(payload?.events)
          ? payload.events
          : []

      const foundEvent = events.find(item => Number(item?.id) === Number(eventId))
      if (foundEvent) {
        return this.normalizeEvent(foundEvent)
      }

      // Fallback: admin meghívott globális események az event-access végpontról.
      const collabResponse = await axios.get(
        `${API_BASE}/establishment/${institutionId}/event-access`,
        {
          headers: getAuthHeaders(token),
          validateStatus: (status) => status >= 200 && status < 600
        }
      )

      if (collabResponse.status >= 400) {
        return null
      }

      const collabPayload = collabResponse.data
      const collabEvents = Array.isArray(collabPayload)
        ? collabPayload
        : Array.isArray(collabPayload?.events)
          ? collabPayload.events
          : []

      const foundCollabEvent = collabEvents.find(item => Number(item?.id) === Number(eventId))
      return foundCollabEvent ? this.normalizeEvent(foundCollabEvent) : null
    },
    
    async loadCurrentUser() {
      const savedUserRaw =
        localStorage.getItem('esemenyter_user') ||
        sessionStorage.getItem('esemenyter_user')

      if (!savedUserRaw) {
        this.currentUser = null
        return
      }

      const savedUser = JSON.parse(savedUserRaw)
      this.currentUser = {
        ...savedUser,
        id: Number(savedUser?.id) || null,
        username: savedUser?.nev || savedUser?.name || 'Felhasználó',
        name: savedUser?.nev || savedUser?.name || 'Felhasználó',
        email: savedUser?.email || '',
        role: savedUser?.role || 'student'
      }
    },
    
    async loadStats() {
      try {
        const event = this.eventData || {}
        this.commentCount = Number(event.comment_count || event.comments_count || 0)
        this.attendingCount = Number(event.attending_count || event.participant_count || event.participants || 0)
        this.notAttendingCount = Number(event.not_attending_count || 0)
        this.favoriteCount = Number(event.favorite_count || event.favorites || 0)
      } catch (error) {
        console.error('Hiba a statisztikák betöltésekor:', error)
        this.commentCount = 0
        this.attendingCount = 0
        this.notAttendingCount = 0
        this.favoriteCount = 0
      }
    },
    
    async loadParticipationStatus() {
      if (!this.currentUser) {
        this.userParticipation = null
        return
      }

      this.userParticipation = this.eventData?.user_participation || null
    },

    async loadPolls() {
      this.polls = []
      this.selectedPollOptionIds = {}

      const token = getToken()
      const eventId = Number(this.eventData?.id || this.eventId)

      if (!token || !eventId || !this.currentUser) {
        return
      }

      this.isPollLoading = true

      try {
        const response = await axios.get(`${API_BASE}/events/${eventId}/poll`, {
          headers: getAuthHeaders(token),
          validateStatus: (status) => status >= 200 && status < 600
        })

        if (response.status === 403 || response.status === 404) {
          this.polls = []
          return
        }

        if (response.status >= 400) {
          throw new Error(response?.data?.message || 'Nem sikerült betölteni a szavazást.')
        }

        this.polls = Array.isArray(response?.data?.polls) ? response.data.polls : []
        this.selectedPollOptionIds = this.polls.reduce((carry, poll) => {
          if (poll?.selected_option_id) {
            carry[poll.id] = Number(poll.selected_option_id)
          }

          return carry
        }, {})
      } catch (error) {
        console.error('Hiba a szavazás betöltésekor:', error)
      } finally {
        this.isPollLoading = false
      }
    },

    handlePollTimingChange() {
      if (!this.pollForm.isTimed) {
        this.pollForm.deadline = ''
      }
    },

    addPollOptionField() {
      if (this.pollForm.options.length >= 10) {
        return
      }

      this.pollForm.options.push('')
    },

    removePollOptionField(index) {
      if (this.pollForm.options.length <= 2) {
        return
      }

      this.pollForm.options.splice(index, 1)
    },

    async createPoll() {
      if (!this.canCreatePoll) {
        this.showMessage('Csak az esemény létrehozója készíthet szavazást.', 'warning')
        return
      }

      const title = String(this.pollForm.title || '').trim()
      const options = this.pollForm.options.map(option => String(option || '').trim()).filter(Boolean)
      const deadline = this.pollForm.isTimed ? (this.pollForm.deadline || null) : null

      if (!title) {
        this.showMessage('Add meg a szavazás kérdését.', 'warning')
        return
      }

      if (new Set(options).size < 2) {
        this.showMessage('Legalább 2 különböző opció szükséges.', 'warning')
        return
      }

      if (this.pollForm.isTimed && !deadline) {
        this.showMessage('Időzített szavazásnál add meg a lezárás napját.', 'warning')
        return
      }

      if (deadline && new Date(deadline) < new Date(new Date().toISOString().slice(0, 10))) {
        this.showMessage('A lezárási nap nem lehet korábbi a létrehozás napjánál.', 'warning')
        return
      }

      const token = getToken()
      if (!token) {
        this.showMessage('A művelethez bejelentkezés szükséges.', 'warning')
        return
      }

      this.isCreatingPoll = true

      try {
        const response = await axios.post(
          `${API_BASE}/polls`,
          {
            event_id: Number(this.eventData?.id || this.eventId),
            title,
            options,
            deadline,
            is_timed: this.pollForm.isTimed,
            hidden_results: this.pollForm.hiddenResults
          },
          {
            headers: getAuthHeaders(token, true),
            validateStatus: (status) => status >= 200 && status < 600
          }
        )

        if (response.status >= 400) {
          const message = response?.data?.message || 'Nem sikerült létrehozni a szavazást.'
          this.showMessage(message, 'error')
          return
        }

        this.resetPollForm()
        this.showMessage(response?.data?.message || 'Szavazás sikeresen létrehozva.', 'success')
        await this.loadPolls()
      } catch (error) {
        console.error('Szavazás létrehozási hiba:', error)
        this.showMessage('Hiba történt a szavazás létrehozásakor.', 'error')
      } finally {
        this.isCreatingPoll = false
      }
    },

    resetPollForm() {
      this.pollForm = {
        title: '',
        deadline: '',
        isTimed: false,
        hiddenResults: false,
        options: ['', '']
      }
    },

    async submitPollVote(poll) {
      if (!poll?.can_answer || !this.selectedPollOptionIds[poll.id]) {
        return
      }

      const token = getToken()
      if (!token) {
        this.showMessage('A művelethez bejelentkezés szükséges.', 'warning')
        return
      }

      this.submittingPollVoteIds = {
        ...this.submittingPollVoteIds,
        [poll.id]: true
      }

      try {
        const response = await axios.post(
          `${API_BASE}/polls/${poll.id}/answer`,
          { option_id: Number(this.selectedPollOptionIds[poll.id]) },
          {
            headers: getAuthHeaders(token, true),
            validateStatus: (status) => status >= 200 && status < 600
          }
        )

        if (response.status >= 400) {
          const message = response?.data?.message || 'Nem sikerült rögzíteni a szavazatot.'
          this.showMessage(message, 'error')
          return
        }

        this.showMessage(response?.data?.message || 'Szavazat rögzítve.', 'success')
        await this.loadPolls()
      } catch (error) {
        console.error('Szavazási hiba:', error)
        this.showMessage('Hiba történt a szavazat mentésekor.', 'error')
      } finally {
        this.submittingPollVoteIds = {
          ...this.submittingPollVoteIds,
          [poll.id]: false
        }
      }
    },

    async stopPoll(poll) {
      if (!poll?.can_manage || poll?.is_ended) {
        return
      }

      const confirmed = await toast.confirm('Biztosan lezárod ezt a szavazást?', {
        confirmText: 'Lezárom',
        cancelText: 'Mégse'
      })

      if (!confirmed) {
        return
      }

      const token = getToken()
      if (!token) {
        this.showMessage('A művelethez bejelentkezés szükséges.', 'warning')
        return
      }

      this.stoppingPollIds = {
        ...this.stoppingPollIds,
        [poll.id]: true
      }

      try {
        const response = await axios.patch(
          `${API_BASE}/polls/${poll.id}/close`,
          {},
          {
            headers: getAuthHeaders(token, true),
            validateStatus: (status) => status >= 200 && status < 600
          }
        )

        if (response.status >= 400) {
          const message = response?.data?.message || 'Nem sikerült lezárni a szavazást.'
          this.showMessage(message, 'error')
          return
        }

        this.showMessage(response?.data?.message || 'Szavazás lezárva.', 'success')
        await this.loadPolls()
      } catch (error) {
        console.error('Szavazás lezárási hiba:', error)
        this.showMessage('Hiba történt a szavazás lezárásakor.', 'error')
      } finally {
        this.stoppingPollIds = {
          ...this.stoppingPollIds,
          [poll.id]: false
        }
      }
    },

    getPollResult(poll, optionId) {
      if (!poll?.results_visible) {
        return null
      }

      return poll?.options?.find(item => Number(item.id) === Number(optionId)) || null
    },

    getPollResultVotes(poll, optionId) {
      return Number(this.getPollResult(poll, optionId)?.votes || 0)
    },

    getPollResultPercentage(poll, optionId) {
      return Number(this.getPollResult(poll, optionId)?.percentage || 0)
    },

    getPollStateLabel(poll) {
      if (!poll?.is_started) {
        return 'Még nem indult'
      }

      if (poll?.is_ended) {
        return 'Lezárult'
      }

      return 'Aktív'
    },

    formatPollCalendarDate(dateString) {
      if (!dateString) {
        return 'Nincs megadva'
      }

      const match = String(dateString).match(/^(\d{4})-(\d{2})-(\d{2})$/)
      if (!match) {
        return this.formatDate(dateString)
      }

      const year = Number(match[1])
      const month = Number(match[2]) - 1
      const day = Number(match[3])

      return new Date(year, month, day).toLocaleDateString('hu-HU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    },
    
    async submitParticipation(answer) {
      if (this.isReadOnlyMode) {
        this.showMessage('Csak megtekintés módban a részvétel nem módosítható.', 'info')
        return
      }

      if (!this.currentUser) {
        this.showMessage('A részvételhez be kell jelentkezned!', 'info')
        this.$router.push('/login')
        return
      }

      const canParticipate = await this.canCurrentUserParticipate()
      if (!canParticipate) {
        return
      }

      try {
        const token = getToken()
        if (!token) {
          this.showMessage('A részvételhez be kell jelentkezned!', 'info')
          return
        }

        const response = await axios.patch(
          `${API_BASE}/events/${this.eventId}/participation`,
          { answer },
          {
            headers: getAuthHeaders(token, true),
            validateStatus: (status) => status >= 200 && status < 600
          }
        )

        if (response.status >= 400) {
          const message = response?.data?.message || 'Nem sikerült menteni a részvételi választ.'
          this.showMessage(message, 'error')
          return
        }

        this.userParticipation = response?.data?.answer || answer
        this.attendingCount = Number(response?.data?.attending_count || 0)
        this.notAttendingCount = Number(response?.data?.not_attending_count || 0)

        if (this.eventData) {
          this.eventData.user_participation = this.userParticipation
          this.eventData.attending_count = this.attendingCount
          this.eventData.not_attending_count = this.notAttendingCount
          this.eventData.participant_count = this.attendingCount
          this.eventData.participants = this.attendingCount
        }

        if (this.userParticipation === 'y') {
          this.showMessage('Részvétel sikeresen rögzítve.', 'success')
        } else {
          this.showMessage('A nem részvétel sikeresen rögzítve.', 'success')
        }

        await this.loadPolls()
      } catch (error) {
        console.error('Hiba a részvétel mentésekor:', error)
        this.showMessage('Hiba történt a részvétel mentésekor.', 'error')
      }
    },

    async canCurrentUserParticipate() {
      if (!this.currentUser) {
        return false
      }

      if (this.currentUser.role !== 'student') {
        return true
      }

      if (this.studentHasClass === null) {
        this.studentHasClass = await this.checkStudentClassMembership()
      }

      const hasClass = this.studentHasClass

      if (!hasClass) {
        this.showMessage('Osztályhoz nem rendelt diákként nem tudsz eseményre jelentkezni.', 'warning')
        return false
      }

      return true
    },

    async checkStudentClassMembership() {
      try {
        const token = getToken()

        if (!token) {
          return false
        }

        const institutionId =
          this.currentUser?.establishment_id ||
          localStorage.getItem('CurrentInstitution') ||
          sessionStorage.getItem('CurrentInstitution') ||
          localStorage.getItem('institutionId') ||
          sessionStorage.getItem('institutionId')

        if (!institutionId) {
          return false
        }

        const classesResponse = await fetch(`${API_BASE}/establishment/${institutionId}/classes`, {
          headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
          }
        })

        if (!classesResponse.ok) {
          return false
        }

        const classesPayload = await classesResponse.json()
        const classes = Array.isArray(classesPayload?.data) ? classesPayload.data : []

        for (const classItem of classes) {
          const membersResponse = await fetch(
            `${API_BASE}/establishment/${institutionId}/classes/${classItem.id}`,
            {
              headers: {
                Authorization: `Bearer ${token}`,
                Accept: 'application/json'
              }
            }
          )

          if (!membersResponse.ok) {
            continue
          }

          const membersPayload = await membersResponse.json()
          const members = Array.isArray(membersPayload?.data) ? membersPayload.data : []
          const belongsToClass = members.some(member => Number(member?.id) === Number(this.currentUser.id))

          if (belongsToClass) {
            return true
          }
        }

        return false
      } catch (error) {
        console.error('Hiba az osztály tagság ellenőrzése során:', error)
        return false
      }
    },
    
    onCommentAdded() {
      this.commentCount++
    },
    
    async toggleFavorite() {
      if (this.isReadOnlyMode) {
        this.showMessage('Csak megtekintés módban a kedvencek módosítása nem elérhető.', 'info')
        return
      }

      if (!this.currentUser) {
        this.showMessage('A kedvencekhez adáshoz jelentkezz be!', 'info')
        return
      }

      const token = getToken()
      if (!token) {
        this.showMessage('A művelethez bejelentkezés szükséges.', 'warning')
        return
      }

      const eventId = Number(this.eventData?.id || this.eventId)
      if (!eventId) {
        this.showMessage('Nem található esemény azonosító.', 'error')
        return
      }

      try {
        const response = await axios.patch(
          `${API_BASE}/events/${eventId}/favourite`,
          {},
          {
            headers: getAuthHeaders(token),
            validateStatus: (status) => status >= 200 && status < 600
          }
        )

        if (response.status >= 400) {
          const message = response?.data?.message || 'Nem sikerült módosítani a kedvenc jelölést.'
          this.showMessage(message, 'error')
          return
        }

        const wasFavorite = Boolean(this.eventData?.isFavorite)
        const nowFavorite = !wasFavorite

        if (this.eventData) {
          this.eventData.isFavorite = nowFavorite
          this.eventData.is_favorite = nowFavorite
          this.eventData.is_favourite = nowFavorite
        }

        this.favoriteCount = Math.max(0, Number(this.favoriteCount || 0) + (nowFavorite ? 1 : -1))

        this.showMessage(
          response?.data?.message || (nowFavorite ? 'Esemény hozzáadva a kedvencekhez.' : 'Esemény eltávolítva a kedvencek közül.'),
          'success'
        )
      } catch (error) {
        console.error('Kedvenc jelölési hiba:', error)
        this.showMessage('Hiba történt a kedvenc jelölés mentésekor.', 'error')
      }
    },

    async toggleEventComments() {
      if (!this.canManageOccurrence) {
        this.showMessage('Csak a létrehozó módosíthatja a kommentelést.', 'warning')
        return
      }

      const token = getToken()
      if (!token) {
        this.showMessage('A művelethez bejelentkezés szükséges.', 'warning')
        return
      }

      const eventId = Number(this.eventData?.id || this.eventId)
      if (!eventId) {
        this.showMessage('Nem található esemény azonosító.', 'error')
        return
      }

      this.isTogglingChat = true

      try {
        const response = await axios.patch(
          `${API_BASE}/events/${eventId}/chat`,
          { chat_enabled: !this.isEventChatEnabled },
          {
            headers: getAuthHeaders(token, true),
            validateStatus: (status) => status >= 200 && status < 600
          }
        )

        if (response.status >= 400) {
          const message = response?.data?.message || 'Nem sikerült módosítani a kommentelést.'
          this.showMessage(message, 'error')
          return
        }

        if (this.eventData) {
          this.eventData.chat_enabled = Boolean(response?.data?.chat_enabled)
        }

        this.showMessage(
          response?.data?.message || (this.eventData?.chat_enabled ? 'Kommentelés engedélyezve.' : 'Kommentelés kikapcsolva.'),
          'success'
        )
      } catch (error) {
        console.error('Hiba a kommentelés állapot módosítása közben:', error)
        this.showMessage('Hiba történt a kommentelés állapot mentésekor.', 'error')
      } finally {
        this.isTogglingChat = false
      }
    },



    toDateTimeLocalValue(dateString) {
      if (!dateString) {
        return ''
      }

      const normalized = String(dateString).trim().replace(' ', 'T')
      const match = normalized.match(/^(\d{4}-\d{2}-\d{2})T(\d{2}:\d{2})/)

      if (!match) {
        return ''
      }

      return `${match[1]}T${match[2]}`
    },

    splitDateTimeParts(dateTimeString, type) {
      if (!dateTimeString) {
        if (type === 'start') {
          this.occurrenceForm.startDate = ''
          this.occurrenceForm.startTime = ''
        } else {
          this.occurrenceForm.endDate = ''
          this.occurrenceForm.endTime = ''
        }
        return
      }

      const normalized = String(dateTimeString).trim().replace(' ', 'T')
      const match = normalized.match(/^(\d{4}-\d{2}-\d{2})T?(\d{2}:\d{2})/)

      if (!match) {
        return
      }

      const date = match[1]
      const time = match[2]

      if (type === 'start') {
        this.occurrenceForm.startDate = date
        this.occurrenceForm.startTime = time
      } else {
        this.occurrenceForm.endDate = date
        this.occurrenceForm.endTime = time
      }
    },

    combineDateAndTime(date, time) {
      const normalizedTime = this.normalizeTimeValue(time)

      if (!date || !normalizedTime) {
        return ''
      }
      return `${date}T${normalizedTime}`
    },

    normalizeTimeValue(timeValue) {
      if (!timeValue) {
        return ''
      }

      const raw = String(timeValue).trim().replace(/\./g, ':')
      if (raw === '') {
        return ''
      }

      let hours
      let minutes

      const compactMatch = raw.match(/^(\d{1,2})(\d{2})$/)
      if (compactMatch) {
        hours = Number(compactMatch[1])
        minutes = Number(compactMatch[2])
      } else {
        const separatedMatch = raw.match(/^(\d{1,2}):(\d{1,2})$/)
        if (!separatedMatch) {
          return ''
        }
        hours = Number(separatedMatch[1])
        minutes = Number(separatedMatch[2])
      }

      if (!Number.isFinite(hours) || !Number.isFinite(minutes) || hours < 0 || hours > 23 || minutes < 0 || minutes > 59) {
        return ''
      }

      return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`
    },

    normalizeOccurrenceTime(fieldName, afterNormalize) {
      const normalized = this.normalizeTimeValue(this.occurrenceForm[fieldName])
      this.occurrenceForm[fieldName] = normalized

      if (typeof afterNormalize === 'function') {
        afterNormalize.call(this)
      }
    },

    updateOccurrenceStartDateTime() {
      this.occurrenceForm.startDateTime = this.combineDateAndTime(
        this.occurrenceForm.startDate,
        this.occurrenceForm.startTime
      )
    },

    updateOccurrenceEndDateTime() {
      let endDate = this.occurrenceForm.endDate
      const startDate = this.occurrenceForm.startDate
      const endTime = this.occurrenceForm.endTime

      if (endDate && startDate && new Date(`${endDate}T${endTime || '00:00'}`) < new Date(`${startDate}T${this.occurrenceForm.startTime || '00:00'}`)) {
        endDate = startDate
        this.occurrenceForm.endDate = endDate
      }

      this.occurrenceForm.endDateTime = this.combineDateAndTime(endDate, endTime)
    },

    async rescheduleOccurrence() {
      if (this.isReadOnlyMode) {
        this.showMessage('Csak megtekintés módban az alkalom nem módosítható.', 'warning')
        return
      }

      if (!this.canManageOccurrence) {
        this.showMessage('Csak a létrehozó módosíthatja ezt az alkalmat.', 'warning')
        return
      }

      if (!this.occurrenceForm.startDate || !this.occurrenceForm.startTime || !this.occurrenceForm.endDate || !this.occurrenceForm.endTime) {
        this.showMessage('Add meg az új kezdési és befejezési időpontot.', 'warning')
        return
      }

      const startDateTime = this.combineDateAndTime(this.occurrenceForm.startDate, this.occurrenceForm.startTime)
      const endDateTime = this.combineDateAndTime(this.occurrenceForm.endDate, this.occurrenceForm.endTime)

      if (new Date(startDateTime) >= new Date(endDateTime)) {
        this.showMessage('A befejezés legyen későbbi, mint a kezdés.', 'warning')
        return
      }

      const token = getToken()
      if (!token) {
        this.showMessage('A művelethez bejelentkezés szükséges.', 'warning')
        return
      }

      this.isUpdatingOccurrence = true

      try {
        const response = await axios.patch(
          `${API_BASE}/events/${this.eventId}/occurrence`,
          {
            action: 'reschedule',
            start_date: startDateTime,
            end_date: endDateTime
          },
          {
            headers: getAuthHeaders(token, true),
            validateStatus: (status) => status >= 200 && status < 600
          }
        )

        if (response.status >= 400) {
          const message = response?.data?.message || 'Nem sikerült átütemezni az alkalmat.'
          this.showMessage(message, 'error')
          return
        }

        this.showMessage(response?.data?.message || 'Alkalom időpontja módosítva.', 'success')
        await this.loadEvent()
      } catch (error) {
        console.error('Átütemezési hiba:', error)
        this.showMessage('Hiba történt az alkalom módosításakor.', 'error')
      } finally {
        this.isUpdatingOccurrence = false
      }
    },

    async cancelOccurrence() {
      if (this.isReadOnlyMode) {
        this.showMessage('Csak megtekintés módban az alkalom nem törölhető.', 'warning')
        return
      }

      if (!this.canManageOccurrence) {
        this.showMessage('Csak a létrehozó törölheti ezt az alkalmat.', 'warning')
        return
      }

      const confirmCancel = await toast.confirm('Biztosan elmaradtként jelölöd ezt az alkalmat?', {
        confirmText: 'Igen, elmarad',
        cancelText: 'Mégse'
      })
      if (!confirmCancel) {
        return
      }

      const token = getToken()
      if (!token) {
        this.showMessage('A művelethez bejelentkezés szükséges.', 'warning')
        return
      }

      this.isUpdatingOccurrence = true

      try {
        const response = await axios.patch(
          `${API_BASE}/events/${this.eventId}/occurrence`,
          { action: 'cancel' },
          {
            headers: getAuthHeaders(token, true),
            validateStatus: (status) => status >= 200 && status < 600
          }
        )

        if (response.status >= 400) {
          const message = response?.data?.message || 'Nem sikerült elmaradtként jelölni az alkalmat.'
          this.showMessage(message, 'error')
          return
        }

        this.showMessage(response?.data?.message || 'Az alkalom elmaradtként jelölve.', 'success')
        setTimeout(() => {
          this.$router.push('/events-list')
        }, 900)
      } catch (error) {
        console.error('Alkalom törlési hiba:', error)
        this.showMessage('Hiba történt az alkalom törlésekor.', 'error')
      } finally {
        this.isUpdatingOccurrence = false
      }
    },
    
    formatDate(dateString) {
      if (!dateString) return 'Nincs megadva'

      // Keep server-provided date/time as wall time to avoid timezone shifts in UI.
      const datetimeMatch = String(dateString).match(
        /^(\d{4})-(\d{2})-(\d{2})[T\s](\d{2}):(\d{2})/
      )

      let date
      if (datetimeMatch) {
        const [, year, month, day, hour, minute] = datetimeMatch
        date = new Date(
          Number(year),
          Number(month) - 1,
          Number(day),
          Number(hour),
          Number(minute)
        )
      } else {
        date = new Date(dateString)
      }

      return date.toLocaleDateString('hu-HU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    },

    formatDateLikeCreation(dateString) {
      if (!dateString) return 'Nincs megadva'

      const normalized = String(dateString).trim().replace(' ', 'T')
      const match = normalized.match(/^(\d{4}-\d{2}-\d{2})T(\d{2}:\d{2})/)

      if (match) {
        return `${match[1]} ${match[2]}`
      }

      return this.formatDate(dateString)
    },
    
    showMessage(message, type = 'success') {
      const normalizedType = String(type || '').toLowerCase()

      if (normalizedType === 'error') {
        toast.error(message)
        return
      }

      if (normalizedType === 'warning' || normalizedType === 'warn') {
        toast.warning(message)
        return
      }

      if (normalizedType === 'info') {
        toast.info(message)
        return
      }

      toast.success(message)
    }
  }
}
</script>

<style scoped>
/* Alap stílusok */
.event-details {
  min-height: 100vh;
  width: 100%;
  overflow-x: hidden;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

.container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
}

/* Navigáció */
.navigation {
  margin-bottom: 2rem;
}

.btn-back {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1.75rem;
  background: rgba(255, 255, 255, 0.95);
  border: none;
  border-radius: 50px;
  color: #2d3748;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
  backdrop-filter: blur(10px);
}

.btn-back:hover {
  transform: translateX(-5px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  background: white;
}

/* Állapot kártyák */
.status-card {
  background: white;
  border-radius: 32px;
  padding: 3rem 2rem;
  text-align: center;
  max-width: 500px;
  margin: 4rem auto;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.status-card.loading .loader {
  margin-bottom: 1.5rem;
}

.spinner {
  width: 64px;
  height: 64px;
  border: 4px solid #e2e8f0;
  border-top-color: #667eea;
  border-radius: 50%;
  margin: 0 auto;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.status-card h3 {
  font-size: 1.5rem;
  color: #1a202c;
  margin-bottom: 0.75rem;
  font-weight: 700;
}

.status-card p {
  color: #718096;
  line-height: 1.6;
}

.error-icon i {
  font-size: 4rem;
  color: #fc8181;
  margin-bottom: 1rem;
}

.error-message {
  background: #fff5f5;
  color: #c53030;
  padding: 1rem;
  border-radius: 12px;
  margin: 1.5rem 0;
  font-weight: 500;
}

.error-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

/* Hero szekció */
.hero-section {
  position: relative;
  border-radius: 32px;
  overflow: hidden;
  margin-bottom: 2rem;
  background: linear-gradient(45deg, #1a202c, #2d3748);
  min-height: 400px;
  display: flex;
  align-items: flex-end;
}

.hero-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(to top, rgba(0,0,0,0.8), rgba(0,0,0,0.3));
}

.hero-content {
  position: relative;
  padding: 3rem;
  width: 100%;
  color: white;
}

.hero-badges {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.badge {
  padding: 0.5rem 1rem;
  border-radius: 50px;
  font-size: 0.875rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  backdrop-filter: blur(10px);
}

.badge.local {
  background: rgba(72, 187, 120, 0.2);
  color: #fff;
  border: 1px solid rgba(72, 187, 120, 0.4);
}

.badge.global {
  background: rgba(66, 153, 225, 0.2);
  color: #fff;
  border: 1px solid rgba(66, 153, 225, 0.4);
}

.badge.open {
  background: rgba(237, 137, 54, 0.2);
  color: #fff;
  border: 1px solid rgba(237, 137, 54, 0.4);
}

.badge.closed {
  background: rgba(229, 62, 62, 0.2);
  color: #fff;
  border: 1px solid rgba(229, 62, 62, 0.4);
}

.hero-title {
  font-size: 3rem;
  font-weight: 800;
  line-height: 1.2;
  margin-bottom: 1.5rem;
  max-width: 800px;
}

.hero-actions {
  display: flex;
  gap: 1rem;
}

.icon-button {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1.75rem;
  background: rgba(255, 255, 255, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 50px;
  color: white;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  backdrop-filter: blur(10px);
}

.icon-button:hover {
  background: rgba(255, 255, 255, 0.25);
  transform: translateY(-2px);
}

.icon-button i.active {
  color: #f59e0b;
  fill: #f59e0b;
}

.icon-button.active {
  background: #fff7ed;
  border-color: #fdba74;
  color: #f59e0b;
}

.icon-button.active .btn-text {
  color: #f59e0b;
}

.poll-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.poll-field {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.poll-inline-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1rem;
}

.poll-inline-grid label {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  color: #2d3748;
  font-weight: 600;
}

.poll-checkbox-field {
  justify-content: center;
}

.poll-checkbox-field input {
  width: 18px;
  height: 18px;
}

.poll-checkbox-field span {
  font-weight: 600;
}

.poll-field label {
  font-size: 0.95rem;
  font-weight: 600;
  color: #2d3748;
}

.poll-field input {
  width: 100%;
  padding: 0.9rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  font-size: 0.95rem;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.poll-field input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
}

.poll-option-inputs {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.poll-option-input-row {
  display: flex;
  gap: 0.75rem;
}

.poll-option-remove,
.poll-option-add {
  border: none;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s ease;
}

.poll-option-remove {
  width: 48px;
  min-width: 48px;
  background: #fff5f5;
  color: #e53e3e;
}

.poll-option-remove:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.poll-option-add {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  align-self: flex-start;
  padding: 0.75rem 1rem;
  background: #edf2ff;
  color: #4c51bf;
}

.poll-option-add:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.poll-panel {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  padding: 1.25rem;
  border: 1px solid #e2e8f0;
  border-radius: 22px;
  background: #f8fafc;
}

.poll-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  max-height: 34rem;
  overflow-y: auto;
  padding-right: 0.35rem;
}

.poll-results-block {
  max-height: 42rem;
  overflow: hidden;
}

.poll-panel-header {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  align-items: flex-start;
}

.poll-meta-row {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
  margin-top: 0.5rem;
}

.poll-pill {
  display: inline-flex;
  align-items: center;
  padding: 0.3rem 0.7rem;
  border-radius: 999px;
  background: #dbeafe;
  color: #1d4ed8;
  font-size: 0.8rem;
  font-weight: 700;
}

.poll-pill.muted {
  background: #edf2f7;
  color: #4a5568;
}

.poll-stop-button {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  border: none;
  border-radius: 12px;
  background: #fff1f2;
  color: #be123c;
  padding: 0.75rem 1rem;
  font-weight: 700;
  cursor: pointer;
}

.poll-stop-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.poll-summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 0.75rem;
}

.poll-summary-item {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
  padding: 0.85rem 1rem;
  border-radius: 14px;
  background: white;
  color: #2d3748;
}

.poll-summary-label {
  font-size: 0.8rem;
  color: #718096;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.poll-question {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1a202c;
}

.poll-summary {
  color: #718096;
  font-size: 0.95rem;
}

.poll-option-list {
  display: flex;
  flex-direction: column;
  gap: 0.875rem;
}

.poll-option-card {
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  border-radius: 18px;
  padding: 1rem;
  text-align: left;
  transition: all 0.2s ease;
  cursor: pointer;
}

.poll-option-card:hover:not(:disabled) {
  border-color: #a3bffa;
  transform: translateY(-1px);
}

.poll-option-card.selected,
.poll-option-card.voted {
  border-color: #667eea;
  background: #eef2ff;
}

.poll-option-card:disabled {
  cursor: default;
}

.poll-option-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 0.75rem;
}

.poll-option-title {
  font-weight: 700;
  color: #2d3748;
}

.poll-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.6rem;
  border-radius: 999px;
  background: #667eea;
  color: #fff;
  font-size: 0.75rem;
  font-weight: 700;
}

.poll-result-row {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.poll-result-bar-track {
  width: 100%;
  height: 12px;
  background: #e2e8f0;
  border-radius: 999px;
  overflow: hidden;
}

.poll-result-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, #667eea, #764ba2);
  border-radius: 999px;
  transition: width 0.3s ease;
}

.poll-result-meta {
  display: flex;
  justify-content: space-between;
  font-size: 0.85rem;
  color: #4a5568;
}

.poll-actions {
  display: flex;
  justify-content: flex-end;
}

.poll-submit-button {
  min-width: 220px;
}

.poll-info-note {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.9rem 1rem;
  border-radius: 14px;
  font-weight: 500;
}

.poll-info-note.success {
  background: #f0fff4;
  color: #2f855a;
}

@media (max-width: 768px) {
  .poll-panel-header {
    flex-direction: column;
  }

  .poll-stop-button,
  .poll-submit-button {
    width: 100%;
  }

  .poll-actions {
    justify-content: stretch;
  }
}

/* Tartalom rács */
.content-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 2rem;
  margin-bottom: 2rem;
  align-items: stretch;
}

/* Bal oszlop */
.left-column {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.info-block {
  background: white;
  border-radius: 24px;
  padding: 2rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.block-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.block-header i {
  font-size: 1.5rem;
  color: #667eea;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.block-header h2 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1a202c;
  margin: 0;
}

/* Szervező profil */
.organizer-profile {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  background: linear-gradient(135deg, #667eea10, #764ba210);
  padding: 1.5rem;
  border-radius: 16px;
}

.profile-avatar {
  width: 64px;
  height: 64px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  font-weight: 700;
  color: white;
}

.profile-data {
  display: flex;
  flex-direction: column;
}

.profile-name {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1a202c;
  margin-bottom: 0.25rem;
}

.profile-type {
  font-size: 0.875rem;
  color: #718096;
}

/* Leírás */
.description {
  font-size: 1.125rem;
  line-height: 1.7;
  color: #2d3748;
  margin: 0;
}

.detailed-content {
  background: #f7fafc;
  padding: 1.5rem;
  border-radius: 16px;
  color: #4a5568;
  line-height: 1.7;
  white-space: pre-line;
  max-height: 420px;
  overflow: auto;
}

.details-block {
  display: block;
}

.details-block .detailed-content {
  min-height: 140px;
}

/* Kép */
.image-container {
  border-radius: 16px;
  overflow: hidden;
}

.image-container img {
  width: 100%;
  height: auto;
  display: block;
  transition: transform 0.5s;
}

.image-container:hover img {
  transform: scale(1.05);
}

/* Jobb oszlop */
.right-column {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.info-card {
  background: white;
  border-radius: 24px;
  padding: 2rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.info-card h3 {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 1.25rem;
  font-weight: 700;
  color: #1a202c;
  margin-bottom: 1.5rem;
}

.info-card h3 i {
  color: #667eea;
}

/* Dátum lista */
.date-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.date-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: #f7fafc;
  border-radius: 12px;
}

.date-label {
  color: #718096;
  font-size: 0.875rem;
  font-weight: 500;
}

.date-value {
  color: #1a202c;
  font-weight: 600;
}

/* Statisztika */
.stats-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
}

.stat-elem {
  background: #f7fafc;
  border-radius: 16px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: all 0.3s;
}

.stat-elem:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.stat-ikon {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #667eea20, #764ba220);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-ikon i {
  font-size: 1.5rem;
  color: #667eea;
}

.stat-data {
  display: flex;
  flex-direction: column;
}

.stat-number {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1a202c;
  line-height: 1.2;
}

.stat-label {
  font-size: 0.75rem;
  color: #718096;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Részvétel */
.participation-description {
  color: #718096;
  margin-bottom: 1.5rem;
}

.participation-options {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.occurrence-manager .occurrence-form-grid {
  display: grid;
  gap: 0.8rem;
  margin-bottom: 0.9rem;
}

.occurrence-manager .occurrence-form-grid label {
  display: flex;
  flex-direction: column;
  gap: 0.45rem;
  color: #475569;
  font-size: 0.86rem;
  font-weight: 600;
}

.occurrence-manager .occurrence-form-grid input {
  border: 1px solid #dbe3f0;
  border-radius: 10px;
  padding: 0.6rem 0.75rem;
  font-size: 0.92rem;
}

.occurrence-manager .occurrence-actions {
  display: grid;
  gap: 0.7rem;
}

.poll-checkbox-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
  margin-bottom: 1.2rem;
  align-items: center;
}

.poll-settings-wrapper {
  background: #f8fafc;
  padding: 1.25rem;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  margin-bottom: 2rem;
}

.poll-deadline-field {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #e2e8f0;
  margin-bottom: 0 !important;
}

.poll-checkbox-field {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
  font-size: 0.95rem;
  color: #4a5568;
  user-select: none;
}

.poll-checkbox-field input[type="checkbox"] {
  width: 20px;
  height: 20px;
  cursor: pointer;
}

.answer-button {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.5rem;
  border: 2px solid transparent;
  border-radius: 16px;
  background: #f7fafc;
  cursor: pointer;
  transition: all 0.3s;
  width: 100%;
  text-align: left;
}

.answer-button i {
  font-size: 1.5rem;
}

.answer-content {
  display: flex;
  flex-direction: column;
}

.answer-title {
  font-weight: 700;
  margin-bottom: 0.25rem;
}

.answer-description {
  font-size: 0.75rem;
  color: #718096;
}

.answer-button.attending {
  color: #2f855a;
}

.answer-button.attending:hover,
.answer-button.attending.active {
  background: #2f855a;
  color: white;
}

.answer-button.attending.active .answer-description {
  color: rgba(255, 255, 255, 0.9);
}

.answer-button.not-attending {
  color: #c53030;
}

.answer-button.not-attending:hover,
.answer-button.not-attending.active {
  background: #c53030;
  color: white;
}

.answer-button.not-attending.active .answer-description {
  color: rgba(255, 255, 255, 0.9);
}

/* Komment szekció */
.comment-section {
  background: white;
  border-radius: 24px;
  padding: 2rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.comment-section-placeholder {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.comment-section-placeholder .comment-header {
  margin-bottom: 0;
}

.comment-section-placeholder .comment-lock-message {
  max-width: 640px;
  margin: 0 auto;
}

.comment-lock-message {
  width: 100%;
  text-align: center;
  padding: 1.5rem;
  border-radius: 16px;
  border: 2px dashed #cbd5e1;
  background: #f8fafc;
}

.comment-lock-message i {
  font-size: 2.2rem;
  color: #64748b;
  margin-bottom: 0.5rem;
}

.comment-lock-message h3 {
  margin: 0 0 0.5rem 0;
  color: #1f2937;
  font-size: 1.1rem;
}

.comment-lock-message p {
  margin: 0;
  color: #475569;
  line-height: 1.6;
}

.comment-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.comment-header-right {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.comment-toggle-inline {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  border: 1px solid #cbd5e1;
  background: #f8fafc;
  color: #334155;
  border-radius: 999px;
  padding: 0.45rem 0.8rem;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
}

.comment-toggle-inline:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.comment-toggle-inline:hover:not(:disabled) {
  border-color: #94a3b8;
  background: #f1f5f9;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.header-left i {
  font-size: 1.5rem;
  color: #667eea;
}

.header-left h2 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1a202c;
  margin: 0;
}

.comment-counter {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  border-radius: 50px;
  font-weight: 600;
}

/* Gombok */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1.75rem;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
  background: white;
  color: #2d3748;
  border: 2px solid #e2e8f0;
}

.btn-secondary:hover {
  background: #f7fafc;
  border-color: #cbd5e0;
}

/* Responsive */
@media (max-width: 1024px) {
  .content-grid {
    grid-template-columns: 1fr;
  }
  
  .hero-title {
    font-size: 2.5rem;
  }
}

@media (max-width: 768px) {
  .container {
    padding: 1rem;
  }

  .details-block .detailed-content {
    min-height: 140px;
  }

  .btn-back {
    width: 100%;
    justify-content: center;
  }
  
  .hero-content {
    padding: 2rem;
  }
  
  .hero-title {
    font-size: 2rem;
  }
  
  .hero-actions {
    flex-direction: column;
  }
  
  .icon-button {
    width: 100%;
    justify-content: center;
  }
  
  .hero-badges {
    flex-direction: column;
  }
  
  .badge {
    width: fit-content;
  }
  
  .organizer-profile {
    flex-direction: column;
    text-align: center;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .error-actions {
    flex-direction: column;
  }
  
  .comment-header {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }

  .comment-header-right {
    width: 100%;
    justify-content: space-between;
    flex-wrap: wrap;
  }
}

@media (max-width: 480px) {
  .container {
    padding: 0.75rem;
  }

  .details-block .detailed-content {
    min-height: 100px;
  }

  .hero-title {
    font-size: 1.75rem;
  }

  .btn-back {
    font-size: 0.875rem;
    padding: 0.75rem 1rem;
  }
  
  .info-block,
  .info-card,
  .comment-section {
    padding: 1.5rem;
  }
  
  .date-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  
  .answer-button {
    flex-direction: column;
    text-align: center;
  }
  
  .answer-content {
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

.event-content {
  animation: fadeIn 0.5s ease-out;
}

.info-block,
.info-card,
.comment-section {
  animation: fadeIn 0.5s ease-out;
  animation-fill-mode: both;
}

.info-block:nth-child(1) { animation-delay: 0.1s; }
.info-block:nth-child(2) { animation-delay: 0.2s; }
.info-block:nth-child(3) { animation-delay: 0.3s; }
.info-card:nth-child(1) { animation-delay: 0.15s; }
.info-card:nth-child(2) { animation-delay: 0.25s; }
.info-card:nth-child(3) { animation-delay: 0.35s; }
</style>