<template>
  <div class="verification-page">
    <div class="verification-container">
      <!-- Betöltés állapot -->
      <div v-if="isVerifying" class="status-card loading">
        <div class="spinner"></div>
        <h2>Email megerősítés folyamatban...</h2>
        <p>Kérlek, várj, amíg megerősítjük az email címedet.</p>
      </div>

      <!-- Sikeres megerősítés -->
      <div v-else-if="verificationStatus === 'success'" class="status-card success">
        <div class="success-icon">
          <i class='bx bx-check-circle'></i>
        </div>
        <h2>🎉 Email megerősítve!</h2>
        <p>Az email címed sikeresen megerősítve lett. Most már teljes hozzáférésed van az EseményTérhez.</p>
        <button @click="goToLogin" class="btn btn-primary">
          <i class='bx bx-arrow-back'></i> Vissza a bejelentkezéshez
        </button>
      </div>

      <!-- Sikertelen megerősítés -->
      <div v-else-if="verificationStatus === 'error'" class="status-card error">
        <div class="error-icon">
          <i class='bx bx-error-circle'></i>
        </div>
        <h2>⚠️ Hiba a megerősítéskor</h2>
        <p class="error-message">{{ errorMessage }}</p>
        <div class="error-actions">
          <button @click="requestNewEmail" class="btn btn-primary">
            <i class='bx bx-mail-send'></i> Új megerősítő email
          </button>
          <button @click="goToLogin" class="btn btn-secondary">
            <i class='bx bx-arrow-back'></i> Bejelentkezéshez
          </button>
        </div>
      </div>

      <!-- Lejárt link -->
      <div v-else-if="verificationStatus === 'expired'" class="status-card expired">
        <div class="expired-icon">
          <i class='bx bx-time-five'></i>
        </div>
        <h2>⏰ Megerősítési link lejárt</h2>
        <p>A megerősítési link 24 óra múlva lejár. Kérjük, kérj új megerősítési emailt.</p>
        <button @click="requestNewEmail" class="btn btn-primary">
          <i class='bx bx-mail-send'></i> Új megerősítő email kérése
        </button>
      </div>

      <!-- Nincs bejelentkezve -->
      <div v-else-if="verificationStatus === 'not-logged-in'" class="status-card warning">
        <div class="warning-icon">
          <i class='bx bx-lock'></i>
        </div>
        <h2>🔒 Bejelentkezés szükséges</h2>
        <p>Az email megerősítéséhez be kell jelentkezned az account-ba, amelyhez az email tartozik.</p>
        <button @click="goToLogin" class="btn btn-primary">
          <i class='bx bx-log-in'></i> Bejelentkezés
        </button>
      </div>

      <!-- Sikeresen küldött email -->
      <div v-else-if="verificationStatus === 'resend-success'" class="status-card success">
        <div class="success-icon">
          <i class='bx bx-mail-send'></i>
        </div>
        <h2>✉️ Email újraküldve!</h2>
        <p>Az új megerősítési email sikeresen elküldtük az email címedre. Kérlek, ellenőrizd a postafiókod!</p>
        <p class="info-text">Ha az email 5 percen belül nem érkezik meg, nézz a spam mappában.</p>
        <div class="actions">
          <button @click="goToLogin" class="btn btn-primary">
            <i class='bx bx-arrow-back'></i> Bejelentkezéshez
          </button>
          <button @click="resetStatus" class="btn btn-secondary">
            <i class='bx bx-mail-send'></i> Újra küldetni
          </button>
        </div>
      </div>

      <!-- Nincsen státusz -->
      <div v-else class="status-card info">
        <div class="info-icon">
          <i class='bx bx-loader-alt'></i>
        </div>
        <h2>Email megerősítés</h2>
        <p>Az email megerősítésének feldolgozása...</p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import { toast } from '../../services/toast'
import { API_BASE, getToken, getAuthHeaders } from '../../services/api'

export default {
  name: 'EmailVerification',

  data() {
    return {
      isVerifying: false,
      verificationStatus: null,
      errorMessage: '',
    }
  },

  async created() {
    await this.verifyEmail()
  },

  methods: {
    async verifyEmail() {
      const status = String(this.$route.query.status || '')
      const id = this.$route.params.id
      const hash = this.$route.params.hash

      if (status) {
        if (status === 'success' || status === 'already-verified') {
          this.verificationStatus = 'success'
          this.errorMessage = status === 'already-verified' ? 'Az email cím már korábban meg lett erősítve.' : ''
          return
        }

        if (status === 'expired') {
          this.verificationStatus = 'expired'
          return
        }

        this.verificationStatus = 'error'
        this.errorMessage = 'A megerősítési link érvénytelen.'
        return
      }

      if (!id || !hash) {
        this.verificationStatus = 'not-logged-in'
        return
      }

      const token = getToken()
      if (!token) {
        this.verificationStatus = 'not-logged-in'
        return
      }

      this.isVerifying = true

      try {
        const response = await axios.get(
          `${API_BASE}/email/verify/${id}/${hash}`,
          {
            headers: getAuthHeaders(token),
            validateStatus: (status) => status >= 200 && status < 600
          }
        )

        if (response.status === 200) {
          // Frissítsd a user adatot, jelezve, hogy az email már megerősítve
          const savedUser = localStorage.getItem('esemenyter_user')
          if (savedUser) {
            const userData = JSON.parse(savedUser)
            userData.email_verified = true
            localStorage.setItem('esemenyter_user', JSON.stringify(userData))
          }

          this.verificationStatus = 'success'
          toast.success('Email sikeresen megerősítve!')

          // Átirányítás a dashboard-ra 3 másodperc után
          setTimeout(() => {
            this.$router.push('/dashboard')
          }, 3000)
        } else if (response.status === 422) {
          // Link lejárt vagy már megerősítve
          const message = response?.data?.message || 'A megerősítési link lejárt.'
          if (message.includes('lejárt') || message.includes('érvénytelen')) {
            this.verificationStatus = 'expired'
          } else {
            this.verificationStatus = 'success'
            this.errorMessage = 'Az email cím már megerősítve lett.'
          }
        } else {
          this.verificationStatus = 'error'
          this.errorMessage = response?.data?.message || 'Hiba a megerősítéskor. Kérlek, próbáld újra.'
          toast.error(this.errorMessage)
        }
      } catch (error) {
        console.error('Email verifikációs hiba:', error)
        this.verificationStatus = 'error'
        this.errorMessage = 'Hálózati hiba történt. Kérlek, próbáld újra később.'
        toast.error(this.errorMessage)
      } finally {
        this.isVerifying = false
      }
    },

    async requestNewEmail() {
      const token = getToken()
      if (!token) {
        toast.warning('Bejelentkezés szükséges.')
        this.$router.push('/login')
        return
      }

      this.isVerifying = true

      try {
        const response = await axios.post(
          `${API_BASE}/email/resend`,
          {},
          {
            headers: getAuthHeaders(token),
            validateStatus: (status) => status >= 200 && status < 600
          }
        )

        if (response.status === 200) {
          this.verificationStatus = 'resend-success'
          toast.success('Megerősítő email újraküldve!')
        } else {
          this.verificationStatus = 'error'
          this.errorMessage = response?.data?.message || 'Hiba az email újraküldéskor.'
          toast.error(this.errorMessage)
        }
      } catch (error) {
        console.error('Email újraküldési hiba:', error)
        this.verificationStatus = 'error'
        this.errorMessage = 'Hálózati hiba történt.'
        toast.error(this.errorMessage)
      } finally {
        this.isVerifying = false
      }
    },

    goToLogin() {
      this.$router.push('/login')
    },

    resetStatus() {
      this.verificationStatus = null
      this.errorMessage = ''
    }
  }
}
</script>

<style scoped>
* {
  box-sizing: border-box;
  font-family: "Poppins", sans-serif;
  margin: 0;
  padding: 0;
}

.verification-page {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  width: 100vw;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px;
}

.verification-container {
  width: 100%;
  max-width: 500px;
}

.status-card {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 25px 45px rgba(0, 0, 0, 0.2);
  border-radius: 20px;
  padding: 50px 40px;
  text-align: center;
  color: white;
  animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1);
  }
}

/* Loading state */
.loading .spinner {
  width: 60px;
  height: 60px;
  border: 4px solid rgba(255, 255, 255, 0.3);
  border-top: 4px solid white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 30px;
}

/* Icons */
.success-icon,
.error-icon,
.expired-icon,
.warning-icon,
.info-icon {
  font-size: 70px;
  margin-bottom: 20px;
  display: inline-block;
}

.success-icon {
  color: #4ade80;
  animation: pulse 0.6s ease-in-out;
}

.error-icon {
  color: #ef4444;
  animation: pulse 0.6s ease-in-out;
}

.expired-icon {
  color: #f59e0b;
  animation: pulse 0.6s ease-in-out;
}

.warning-icon {
  color: #eab308;
  animation: pulse 0.6s ease-in-out;
}

.info-icon {
  animation: spin 2s linear infinite;
}

/* Text */
.status-card h2 {
  font-size: 28px;
  margin-bottom: 15px;
  font-weight: 600;
}

.status-card p {
  font-size: 15px;
  opacity: 0.9;
  margin-bottom: 10px;
  line-height: 1.6;
}

.info-text {
  font-size: 13px;
  opacity: 0.8;
  margin-top: 15px;
}

.error-message {
  background: rgba(255, 59, 48, 0.2);
  border-left: 3px solid #ff3b30;
  padding: 15px;
  border-radius: 8px;
  margin: 20px 0;
  text-align: left;
  font-size: 14px;
}

/* Buttons */
.btn {
  display: inline-block;
  padding: 12px 30px;
  margin: 10px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.3s ease;
  text-decoration: none;
}

.btn-primary {
  background: white;
  color: #667eea;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
}

.btn-secondary {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.btn-secondary:hover {
  background: rgba(255, 255, 255, 0.3);
}

.actions {
  margin-top: 30px;
  display: flex;
  gap: 10px;
  justify-content: center;
  flex-wrap: wrap;
}

.error-actions {
  margin-top: 30px;
  display: flex;
  gap: 10px;
  justify-content: center;
  flex-wrap: wrap;
}

/* Responsive */
@media (max-width: 600px) {
  .verification-container {
    max-width: 100%;
  }

  .status-card {
    padding: 40px 25px;
  }

  .status-card h2 {
    font-size: 24px;
  }

  .success-icon,
  .error-icon,
  .expired-icon,
  .warning-icon {
    font-size: 50px;
  }

  .btn {
    width: 100%;
    margin: 8px 0;
  }

  .actions,
  .error-actions {
    flex-direction: column;
  }
}
</style>
