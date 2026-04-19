<template>
  <div class="manage-overlay" @click.self="closeView">
    <div class="manage-panel-shell">
      <!-- Betöltési állapot visszajelzése -->
      <div v-if="isLoading" class="status-card loading">
        <i class='bx bx-loader-alt bx-spin'></i>
        <h3>Betöltés...</h3>
      </div>

      <!-- Hibaüzenet megjelenítése, ha az esemény nem elérhető -->
      <div v-else-if="errorMessage" class="status-card error">
        <i class='bx bx-error-circle'></i>
        <p>{{ errorMessage }}</p>
      </div>

      <!-- Kezelőfelület megjelenítése, ha van adat és van jogosultság -->
      <div v-else-if="eventData && canManageOccurrence" class="manage-panel">
        <div class="manage-panel-header">
          <h2><i class='bx bx-cog'></i> Esemény kezelése</h2>
          <button class="close-btn" @click="closeView">
            <i class='bx bx-x'></i>
          </button>
        </div>

        <div class="manage-body">
          <!-- Résztvevők listája és kitiltási lehetőség (moderáció) -->
          <div class="info-card participants-manager">
            <h3 class="participants-toggle" @click="participantsExpanded = !participantsExpanded">
              <span><i class='bx bx-group'></i> Résztvevők kezelése</span>
              <i class='bx' :class="participantsExpanded ? 'bx-chevron-up' : 'bx-chevron-down'"></i>
            </h3>

            <div v-show="participantsExpanded">
              <div v-if="isParticipantsLoading" class="participants-state loading">
                <i class='bx bx-loader-circle bx-spin'></i>
                <span>Résztvevők betöltése...</span>
              </div>

              <div v-else-if="!participants.length" class="participants-state empty">
                <i class='bx bx-info-circle'></i>
                <span>Még nincs aktív résztvevő az eseményen.</span>
              </div>

              <div v-else class="participants-list">
                <div v-for="participant in participants" :key="participant.user_id" class="participant-item">
                  <div class="participant-meta">
                    <div class="participant-name">{{ participant.alias || participant.name || 'Ismeretlen felhasználó' }}</div>
                    <div class="participant-subline">{{ participant.email || 'Nincs email megadva' }}</div>
                  </div>

                  <!-- Résztvevő kitiltása az eseményről -->
                  <button
                    class="participant-ban-button"
                    :disabled="Boolean(banningParticipantIds[participant.user_id])"
                    @click="banParticipant(participant)"
                  >
                    <i class='bx' :class="banningParticipantIds[participant.user_id] ? 'bx-loader-circle bx-spin' : 'bx-block'"></i>
                    {{ banningParticipantIds[participant.user_id] ? 'Tiltás...' : 'Tiltás' }}
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Időpont átütemezése vagy az alkalom törlése (elmaradás jelzése) -->
          <div class="info-card occurrence-manager">
            <h3><i class='bx bx-wrench'></i> Időpont módosítása</h3>
            <p class="description">Létrehozóként módosíthatja az adott alkalom időpontját, vagy elmaradtként törölheti.</p>

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
              <!-- Átütemezés végrehajtása -->
              <button class="action-btn attending" :disabled="isUpdatingOccurrence" @click="rescheduleOccurrence">
                <i class='bx bx-time-five'></i>
                <span>Időpont módosítása</span>
              </button>

              <!-- Alkam elmaradtként jelölése -->
              <button class="action-btn not-attending" :disabled="isUpdatingOccurrence" @click="cancelOccurrence">
                <i class='bx bx-calendar-x'></i>
                <span>Alkalom elmarad</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Jogosultság hiánya esetén megjelenő üzenet -->
      <div v-else class="status-card error">
        <i class='bx bx-shield-x'></i>
        <p>Ehhez az oldalhoz nincs jogosultságod.</p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import { toast } from '../../services/toast'
import { API_BASE, getToken, getAuthHeaders, getCurrentInstitutionId, normalizeEventStatus } from '../../services/api'

export default {
  name: 'EventManage',

  emits: ['close'],

  props: {
    eventId: {
      type: [String, Number],
      default: null
    }
  },

  data() {
    return {
      eventData: null,
      currentUser: null,
      isLoading: true,
      errorMessage: '',
      participantsExpanded: true,
      isParticipantsLoading: false,
      participants: [],
      banningParticipantIds: {},
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

  computed: {
    resolvedEventId() {
      return Number(this.eventId || this.$route?.params?.id || 0)
    },

    canManageOccurrence() {
      return Number(this.currentUser?.id) > 0 && Number(this.eventData?.user_id) === Number(this.currentUser?.id)
    }
  },

  async created() {
    // Firefox specifikus időkitöltési hibák kezelése miatt detektáljuk a böngészőt
    this.isFirefoxBrowser = typeof navigator !== 'undefined' && /firefox/i.test(navigator.userAgent || '')
    await this.loadCurrentUser()
    await this.loadEvent()
    // Csak akkor töltjük be a résztvevőket, ha a felhasználónak van joga kezelni őket
    if (this.canManageOccurrence) {
      await this.loadParticipants()
    }
  },

  methods: {
    closeView() {
      this.$emit('close')

      // Ha az eseménykezelő külön útvonalként nyílt meg, visszairányítunk az eseményre
      if (!this.eventId && this.$route?.name === 'event-manage') {
        this.$router.push(`/esemenyek/${this.resolvedEventId}`)
      }
    },

    /**
     * Bejelentkezett felhasználó adatainak betöltése tárolóból
     */
    async loadCurrentUser() {
      const savedUserRaw = localStorage.getItem('esemenyter_user') || sessionStorage.getItem('esemenyter_user')
      if (!savedUserRaw) {
        this.currentUser = null
        return
      }

      const savedUser = JSON.parse(savedUserRaw)
      this.currentUser = {
        ...savedUser,
        id: Number(savedUser?.id) || null,
        name: savedUser?.nev || savedUser?.name || 'Felhasználó',
        role: savedUser?.role || 'student'
      }
    },

    /**
     * Esemény adatainak lekérése és az űrlap alaphelyzetbe állítása
     */
    async loadEvent() {
      try {
        this.isLoading = true
        this.errorMessage = ''
        const foundEvent = await this.fetchEvent(this.resolvedEventId)

        if (!foundEvent) {
          this.errorMessage = 'A kiválasztott esemény nem létezik vagy elérhetetlen.'
          return
        }

        this.eventData = foundEvent
        this.splitDateTimeParts(foundEvent.start_date, 'start')
        this.splitDateTimeParts(foundEvent.end_date, 'end')
      } catch (error) {
        this.errorMessage = 'Nem sikerült betölteni az esemény adatait.'
      } finally {
        this.isLoading = false
      }
    },

    /**
     * Próbálja lekérni az eseményt saját vagy kollaborációs hozzáféréssel
     */
    async fetchEvent(eventId) {
      const token = getToken()
      const institutionId = getCurrentInstitutionId(this.currentUser)
      if (!token || !institutionId) return null

      // Saját intézményi események keresése
      const response = await axios.get(`${API_BASE}/establishment/${institutionId}/events`, {
        headers: getAuthHeaders(token),
        validateStatus: (status) => status >= 200 && status < 600
      })

      if (response.status < 400) {
        const events = Array.isArray(response.data) ? response.data : (response.data?.events || [])
        const found = events.find(item => Number(item?.id) === Number(eventId))
        if (found) return this.normalizeEvent(found)
      }

      // Kollaborációs (meghívott) események keresése
      const collabResponse = await axios.get(`${API_BASE}/establishment/${institutionId}/event-access`, {
        headers: getAuthHeaders(token),
        validateStatus: (status) => status >= 200 && status < 600
      })

      if (collabResponse.status < 400) {
        const collabEvents = Array.isArray(collabResponse.data) ? collabResponse.data : (collabResponse.data?.events || [])
        const foundCollab = collabEvents.find(item => Number(item?.id) === Number(eventId))
        return foundCollab ? this.normalizeEvent(foundCollab) : null
      }

      return null
    },

    normalizeEvent(event) {
      if (!event) return null
      return {
        ...event,
        id: Number(event.id),
        status: normalizeEventStatus(event.status),
        is_recurring: Boolean(event.is_recurring),
        creator_name: event.creator_name || event.creator?.name || event.user?.name || 'Ismeretlen szervezo'
      }
    },

    /**
     * Résztvevők listájának lekérése az adott eseményhez
     */
    async loadParticipants() {
      const token = getToken()
      const eventId = Number(this.eventData?.id || this.resolvedEventId)

      if (!token || !eventId || !this.canManageOccurrence) {
        this.participants = []
        return
      }

      this.isParticipantsLoading = true

      try {
        const response = await axios.get(`${API_BASE}/events/${eventId}/participants`, {
          headers: getAuthHeaders(token),
          validateStatus: (status) => status >= 200 && status < 600
        })

        if (response.status >= 400) {
          this.participants = []
          return
        }

        this.participants = Array.isArray(response?.data?.participants) ? response.data.participants : []
      } catch (error) {
        this.participants = []
      } finally {
        this.isParticipantsLoading = false
      }
    },

    /**
     * Résztvevő kitiltása az eseményről (moderációs funkció)
     */
    async banParticipant(participant) {
      const participantId = Number(participant?.user_id)
      const eventId = Number(this.eventData?.id || this.resolvedEventId)
      if (!participantId || !eventId) return

      const confirmed = await toast.confirm(
        `Biztosan ki szeretné tiltani ${participant?.alias || participant?.name || 'a felhasználót'} az eseményből?`,
        { confirmText: 'Tiltás', cancelText: 'Mégse' }
      )

      if (!confirmed) return

      const token = getToken()
      if (!token) {
        this.showMessage('A művelethez bejelentkezés szükséges.', 'warning')
        return
      }

      this.banningParticipantIds = { ...this.banningParticipantIds, [participantId]: true }

      try {
        const response = await axios.delete(`${API_BASE}/events/${eventId}/participants/${participantId}`, {
          headers: getAuthHeaders(token),
          validateStatus: (status) => status >= 200 && status < 600
        })

        if (response.status >= 400) {
          this.showMessage(response?.data?.message || 'Nem sikerült kitiltani a résztvevőt.', 'error')
          return
        }

        this.participants = this.participants.filter(item => Number(item.user_id) !== participantId)
        this.showMessage(response?.data?.message || 'Résztvevő sikeresen kitiltva.', 'success')
      } catch (error) {
        this.showMessage('Hiba történt a résztvevő kitiltásakor.', 'error')
      } finally {
        this.banningParticipantIds = { ...this.banningParticipantIds, [participantId]: false }
      }
    },

    /**
     * ISO dátum szöveg szétvágása dátum és idő részekre az input mezőknek
     */
    splitDateTimeParts(dateTimeString, type) {
      if (!dateTimeString) return

      const normalized = String(dateTimeString).trim().replace(' ', 'T')
      const match = normalized.match(/^(\d{4}-\d{2}-\d{2})T?(\d{2}:\d{2})/)
      if (!match) return

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

    /**
     * Dátum és idő összefűzése API-kompatibilis formátumba
     */
    combineDateAndTime(date, time) {
      const normalizedTime = this.normalizeTimeValue(time)
      if (!date || !normalizedTime) return ''
      return `${date}T${normalizedTime}`
    },

    /**
     * Idő formátum normalizálása (pl. 8.30 -> 08:30, 1400 -> 14:00)
     * Firefox és kézi bevitel esetén kritikus.
     */
    normalizeTimeValue(timeValue) {
      if (!timeValue) return ''

      const raw = String(timeValue).trim().replace(/\./g, ':')
      if (raw === '') return ''

      let hours
      let minutes

      const compactMatch = raw.match(/^(\d{1,2})(\d{2})$/)
      if (compactMatch) {
        hours = Number(compactMatch[1])
        minutes = Number(compactMatch[2])
      } else {
        const separatedMatch = raw.match(/^(\d{1,2}):(\d{1,2})$/)
        if (!separatedMatch) return ''
        hours = Number(separatedMatch[1])
        minutes = Number(separatedMatch[2])
      }

      if (!Number.isFinite(hours) || !Number.isFinite(minutes) || hours < 0 || hours > 23 || minutes < 0 || minutes > 59) {
        return ''
      }

      return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`
    },

    /**
     * Blur eseményre hívható idő-normalizáló
     */
    normalizeOccurrenceTime(fieldName, afterNormalize) {
      const normalized = this.normalizeTimeValue(this.occurrenceForm[fieldName])
      this.occurrenceForm[fieldName] = normalized

      if (typeof afterNormalize === 'function') {
        afterNormalize.call(this)
      }
    },

    updateOccurrenceStartDateTime() {
      this.occurrenceForm.startDateTime = this.combineDateAndTime(this.occurrenceForm.startDate, this.occurrenceForm.startTime)
    },

    /**
     * Biztosítja, hogy a befejezés ne lehessen korábban, mint a kezdés
     */
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

    /**
     * Az adott alkalom átütemezése az új időpontra (patch kérés)
     */
    async rescheduleOccurrence() {
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
          `${API_BASE}/events/${this.resolvedEventId}/occurrence`,
          { action: 'reschedule', start_date: startDateTime, end_date: endDateTime },
          {
            headers: getAuthHeaders(token, true),
            validateStatus: (status) => status >= 200 && status < 600
          }
        )

        if (response.status >= 400) {
          this.showMessage(response?.data?.message || 'Nem sikerült átütemezni az alkalmat.', 'error')
          return
        }

        this.showMessage(response?.data?.message || 'Alkalom időpontja módosítva.', 'success')
        await this.loadEvent()
      } catch (error) {
        this.showMessage('Hiba történt az alkalom módosításakor.', 'error')
      } finally {
        this.isUpdatingOccurrence = false
      }
    },

    /**
     * Az adott alkalom elmaradtként való megjelölése
     */
    async cancelOccurrence() {
      if (!this.canManageOccurrence) {
        this.showMessage('Csak a létrehozó törölheti ezt az alkalmat.', 'warning')
        return
      }

      const confirmCancel = await toast.confirm('Biztosan elmaradtként jelölöd ezt az alkalmat?', {
        confirmText: 'Igen, elmarad',
        cancelText: 'Mégse'
      })

      if (!confirmCancel) return

      const token = getToken()
      if (!token) {
        this.showMessage('A művelethez bejelentkezés szükséges.', 'warning')
        return
      }

      this.isUpdatingOccurrence = true

      try {
        const response = await axios.patch(
          `${API_BASE}/events/${this.resolvedEventId}/occurrence`,
          { action: 'cancel' },
          {
            headers: getAuthHeaders(token, true),
            validateStatus: (status) => status >= 200 && status < 600
          }
        )

        if (response.status >= 400) {
          this.showMessage(response?.data?.message || 'Nem sikerült elmaradtként jelölni az alkalmat.', 'error')
          return
        }

        this.showMessage(response?.data?.message || 'Az alkalom elmaradtként jelölve.', 'success')
        setTimeout(() => this.closeView(), 900)
      } catch (error) {
        this.showMessage('Hiba történt az alkalom törlésekor.', 'error')
      } finally {
        this.isUpdatingOccurrence = false
      }
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
/* Fő konténer és overlay (háttér homályosítása) */
.manage-overlay {
  position: fixed;
  inset: 0;
  z-index: 1200;
  background: rgba(2, 6, 23, 0.68);
  backdrop-filter: blur(6px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  animation: overlayFadeIn 180ms ease-out;
}

/* A kezelőpanel külső burka (görgethető terület) */
.manage-panel-shell {
  width: min(1100px, 100%);
  max-height: 92vh;
  overflow: auto;
  border-radius: 24px;
  animation: dashboardPopIn 240ms cubic-bezier(0.22, 1, 0.36, 1);
}

/* Állapotjelző kártyák (töltés, hiba) */
.status-card {
  background: #ffffff;
  border-radius: 18px;
  padding: 2rem;
  text-align: center;
  border: 1px solid #e2e8f0;
  box-shadow: 0 18px 40px rgba(15, 23, 42, 0.25);
}

.status-card.loading i {
  font-size: 2rem;
  color: #4f46e5;
}

.status-card.error i {
  font-size: 2rem;
  color: #dc2626;
}

/* Sötét fejléc és a panel alapstílusa */
.manage-panel {
  background: linear-gradient(180deg, #f8faff 0%, #f2f5ff 100%);
  border-radius: 22px;
  overflow: hidden;
  box-shadow: 0 25px 60px rgba(2, 6, 23, 0.4);
  border: 1px solid rgba(148, 163, 184, 0.35);
}

.manage-panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem;
  background: linear-gradient(90deg, #1e293b, #334155);
  color: #fff;
}

.manage-panel-header h2 {
  margin: 0;
  display: inline-flex;
  align-items: center;
  gap: 0.55rem;
  font-size: 1.2rem;
  font-weight: 700;
}

/* Bezárás gomb stílusa */
.close-btn {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  border: 1px solid rgba(255, 255, 255, 0.35);
  background: rgba(255, 255, 255, 0.12);
  color: #fff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.close-btn:hover {
  transform: translateY(-1px);
  background: rgba(255, 255, 255, 0.2);
}

/* Tartalmi rész elrendezése (grid) */
.manage-body {
  padding: 1rem;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  align-items: start;
}

/* Általános kártya dizájn (fehér háttér, árnyék) */
.info-card {
  background: #ffffff;
  border-radius: 18px;
  padding: 1.25rem;
  box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
  border: 1px solid #dbe3f0;
}

.description {
  color: #64748b;
  margin-top: 0;
}

.info-card h3 {
  margin: 0 0 0.9rem;
  color: #1e293b;
  font-size: 1.1rem;
}

/* Résztvevők listája és lenyitható fejléc */
.participants-toggle {
  display: flex;
  align-items: center;
  justify-content: space-between;
  cursor: pointer;
  user-select: none;
  margin-bottom: 0.5rem;
}

.participants-toggle i {
  color: #64748b;
}

.participants-state {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 0.85rem;
  border-radius: 12px;
  font-size: 0.95rem;
}

.participants-state.loading {
  background: #edf2ff;
  color: #3949ab;
}

.participants-state.empty {
  background: #f8fafc;
  color: #4a5568;
}

/* Egyedi résztvevő sorok és a tiltás gomb */
.participants-list {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
  max-height: 360px;
  overflow-y: auto;
  padding-right: 0.2rem;
}

.participant-item {
  display: flex;
  justify-content: space-between;
  gap: 0.75rem;
  align-items: center;
  padding: 0.65rem 0.75rem;
  border-radius: 12px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
}

.participant-meta {
  min-width: 0;
}

.participant-name {
  font-weight: 700;
  color: #1a202c;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.participant-subline {
  color: #718096;
  font-size: 0.85rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.participant-ban-button {
  border: none;
  border-radius: 10px;
  padding: 0.5rem 0.75rem;
  font-size: 0.85rem;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  color: #fff;
  background: #e53e3e;
  cursor: pointer;
}

.participant-ban-button:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

/* Időpont módosító űrlap elemei */
.occurrence-form-grid {
  display: grid;
  gap: 0.8rem;
  margin-bottom: 0.9rem;
}

.occurrence-form-grid label {
  display: flex;
  flex-direction: column;
  gap: 0.45rem;
  color: #475569;
  font-size: 0.86rem;
  font-weight: 600;
}

.occurrence-form-grid input {
  border: 1px solid #dbe3f0;
  border-radius: 10px;
  padding: 0.6rem 0.75rem;
  font-size: 0.92rem;
  color: #1e293b;
  background: #ffffff;
}

.occurrence-form-grid input:focus {
  outline: none;
  border-color: #818cf8;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.18);
}

/* Műveleti gombok (módosítás / elmaradás) */
.occurrence-actions {
  display: grid;
  gap: 0.7rem;
}

.action-btn {
  border: none;
  border-radius: 14px;
  cursor: pointer;
  padding: 0.85rem 1rem;
  font-weight: 700;
  font-size: 0.98rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.6rem;
  transition: all 0.2s ease;
}

.action-btn.attending {
  background: #e8f7ee;
  color: #18794e;
}

.action-btn.not-attending {
  background: #fff1f2;
  color: #dc2626;
}

.action-btn:hover:not(:disabled) {
  transform: translateY(-1px);
  filter: brightness(0.98);
}

.action-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Animációk definíciója */
@keyframes overlayFadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes dashboardPopIn {
  from {
    opacity: 0;
    transform: translateY(12px) scale(0.985);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

@media (prefers-reduced-motion: reduce) {
  .manage-overlay,
  .manage-panel-shell {
    animation: none;
  }
}

/* Reszponzív nézet: mobil eszközökön egy oszlopba rendeződik */
@media (max-width: 900px) {
  .manage-body {
    grid-template-columns: 1fr;
  }
}
</style>
