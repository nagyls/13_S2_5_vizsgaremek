<template>
  <div class="email-verification">
    <div class="top-logo" @click="goToLogin">
      <img :src="logo2" alt="EseményTér logó" class="top-logo-image">
    </div>

    <!-- MAIN CONTENT -->
    <main class="main-content">
      <div class="container">
        <div class="verification-wrapper">
          <!-- Betöltés állapot -->
          <div v-if="isVerifying" class="verification-card loading">
            <div class="card-icon loading-icon">
              <i class='bx bx-loader-alt bx-spin'></i>
            </div>
            <h2>Email megerősítés folyamatban...</h2>
            <p>Kérjük, várjon, amíg megerősítjük az email címét.</p>
          </div>

          <!-- Sikeres megerősítés -->
          <div v-else-if="verificationStatus === 'success'" class="verification-card success">
            <div class="card-icon success-icon">
              <i class='bx bx-check-circle'></i>
            </div>
            <h2>Email megerősítve!</h2>
            <p>Az email cím sikeresen megerősítve lett. Most már teljes hozzáférése van az EseményTérhez.</p>
            <div class="card-actions">
              <button @click="goToDashboard" class="btn btn-primary">
                <i class='bx bx-home'></i>
                Vissza a főoldalra
              </button>
              <button @click="goToLogin" class="btn btn-outline">
                <i class='bx bx-log-in'></i>
                Bejelentkezés
              </button>
            </div>
          </div>

          <!-- Sikertelen megerősítés -->
          <div v-else-if="verificationStatus === 'error'" class="verification-card error">
            <div class="card-icon error-icon">
              <i class='bx bx-error-circle'></i>
            </div>
            <h2>Hiba a megerősítéskor</h2>
            <p class="error-message">{{ errorMessage }}</p>
            <div class="card-actions">
              <button @click="requestNewEmail" class="btn btn-primary">
                <i class='bx bx-mail-send'></i>
                Új megerősítő email
              </button>
              <button @click="goToLogin" class="btn btn-outline">
                <i class='bx bx-arrow-back'></i>
                Vissza a bejelentkezéshez
              </button>
            </div>
          </div>

          <!-- Lejárt link -->
          <div v-else-if="verificationStatus === 'expired'" class="verification-card warning">
            <div class="card-icon warning-icon">
              <i class='bx bx-time-five'></i>
            </div>
            <h2>Megerősítési link lejárt</h2>
            <p>A megerősítési link 24 óra elteltével lejár. Kérjük, kérjen új megerősítési emailt.</p>
            <div class="card-actions">
              <button @click="requestNewEmail" class="btn btn-primary">
                <i class='bx bx-mail-send'></i>
                Új megerősítő email kérése
              </button>
            </div>
          </div>

          <!-- Nincs bejelentkezve -->
          <div v-else-if="verificationStatus === 'not-logged-in'" class="verification-card info">
            <div class="card-icon info-icon">
              <i class='bx bx-lock-alt'></i>
            </div>
            <h2>Bejelentkezés szükséges</h2>
            <p>Az email megerősítéséhez be kell jelentkeznie a fiókba, amelyhez az email tartozik.</p>
            <div class="card-actions">
              <button @click="goToLogin" class="btn btn-primary">
                <i class='bx bx-log-in'></i>
                Bejelentkezés
              </button>
            </div>
          </div>

          <!-- Sikeresen küldött email -->
          <div v-else-if="verificationStatus === 'resend-success'" class="verification-card success">
            <div class="card-icon success-icon">
              <i class='bx bx-mail-send'></i>
            </div>
            <h2>Email újraküldve!</h2>
            <p>Az új megerősítési email sikeresen elküldésre került az email címére.</p>
            <p class="info-text">Ha az email 5 percen belül nem érkezik meg, nézzen a spam mappában.</p>
            <div class="card-actions">
              <button @click="goToLogin" class="btn btn-primary">
                <i class='bx bx-arrow-back'></i>
                Vissza a bejelentkezéshez
              </button>
              <button @click="resetStatus" class="btn btn-outline">
                <i class='bx bx-refresh'></i>
                Újra küldeni
              </button>
            </div>
          </div>

          <!-- Alap állapot -->
          <div v-else class="verification-card">
            <div class="card-icon">
              <i class='bx bx-envelope'></i>
            </div>
            <h2>Email megerősítés</h2>
            <p>Az email megerősítésének feldolgozása...</p>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import axios from 'axios'
import { toast } from '../../services/toast'
import { API_BASE, getToken, getAuthHeaders } from '../../services/api'
import logo2 from '../../assets/logo2.svg'

export default {
  name: 'EmailVerification',

  data() {
    return {
      isVerifying: false,
      verificationStatus: null,
      errorMessage: '',
      logo2,
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
          const savedUser = localStorage.getItem('esemenyter_user')
          if (savedUser) {
            const userData = JSON.parse(savedUser)
            userData.email_verified = true
            localStorage.setItem('esemenyter_user', JSON.stringify(userData))
          }

          this.verificationStatus = 'success'
          toast.success('Email sikeresen megerősítve!')

          setTimeout(() => {
            this.$router.push('/dashboard')
          }, 3000)
        } else if (response.status === 422) {
          const message = response?.data?.message || 'A megerősítési link lejárt.'
          if (message.includes('lejárt') || message.includes('érvénytelen')) {
            this.verificationStatus = 'expired'
          } else {
            this.verificationStatus = 'success'
            this.errorMessage = 'Az email cím már megerősítve lett.'
          }
        } else {
          this.verificationStatus = 'error'
          this.errorMessage = response?.data?.message || 'Hiba a megerősítéskor. Kérjük, próbálja újra.'
          toast.error(this.errorMessage)
        }
      } catch (error) {
        console.error('Email verifikációs hiba:', error)
        this.verificationStatus = 'error'
        this.errorMessage = 'Hálózati hiba történt. Kérjük, próbálja újra később.'
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

    goToDashboard() {
      this.$router.push('/dashboard')
    },

    resetStatus() {
      this.verificationStatus = null
      this.errorMessage = ''
    }
  }
}
</script>

<style scoped>
/* ===== ALAP STÍLUSOK ===== */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.email-verification {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  width: 100%;
}

.top-logo {
  position: fixed;
  top: 16px;
  left: 16px;
  z-index: 1000;
  cursor: pointer;
}

.top-logo-image {
  width: 96px;
  height: auto;
  display: block;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

/* ===== MAIN CONTENT ===== */
.main-content {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: 40px 0;
}

.verification-wrapper {
  width: 100%;
  max-width: 520px;
  margin: 0 auto;
}

/* ===== VERIFICATION CARD ===== */
.verification-card {
  background: white;
  border-radius: 32px;
  padding: 48px 40px;
  text-align: center;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
  animation: fadeInUp 0.5s ease-out;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Card Icons */
.card-icon {
  font-size: 80px;
  margin-bottom: 24px;
  display: inline-block;
}

.card-icon.loading-icon i {
  color: #4f46e5;
}

.card-icon.success-icon i {
  color: #10b981;
}

.card-icon.error-icon i {
  color: #ef4444;
}

.card-icon.warning-icon i {
  color: #f59e0b;
}

.card-icon.info-icon i {
  color: #3b82f6;
}

/* Loading animation */
.loading-icon i {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* Card Typography */
.verification-card h2 {
  font-size: 28px;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 16px;
}

.verification-card p {
  font-size: 16px;
  color: #6b7280;
  line-height: 1.6;
  margin-bottom: 8px;
}

.info-text {
  font-size: 13px;
  color: #9ca3af;
  margin-top: 12px;
  margin-bottom: 0;
}

.error-message {
  background: #fef2f2;
  border-left: 4px solid #ef4444;
  padding: 12px 16px;
  border-radius: 12px;
  margin: 20px 0;
  text-align: left;
  font-size: 14px;
  color: #991b1b;
}

/* Card Actions */
.card-actions {
  display: flex;
  gap: 12px;
  justify-content: center;
  margin-top: 32px;
  flex-wrap: wrap;
}

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.3s ease;
  text-decoration: none;
  border: none;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}

.btn-outline {
  background: white;
  color: #4f46e5;
  border: 2px solid #e5e7eb;
}

.btn-outline:hover {
  background: #f9fafb;
  border-color: #4f46e5;
  transform: translateY(-2px);
}

/* ===== SUCCESS CARD ===== */
.verification-card.success .card-icon i {
  animation: pulse 0.5s ease-in-out;
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1);
  }
}

/* ===== WARNING CARD ===== */
.verification-card.warning .card-icon i {
  animation: pulse 0.5s ease-in-out;
}

/* ===== ERROR CARD ===== */
.verification-card.error .card-icon i {
  animation: pulse 0.5s ease-in-out;
}

/* ===== LOADING CARD ===== */
.verification-card.loading .card-icon i {
  animation: spin 1s linear infinite;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 640px) {
  .container {
    padding: 0 16px;
  }

  .verification-card {
    padding: 32px 24px;
  }

  .verification-card h2 {
    font-size: 24px;
  }

  .verification-card p {
    font-size: 14px;
  }

  .card-icon {
    font-size: 60px;
  }

  .card-actions {
    flex-direction: column;
    gap: 10px;
  }

  .btn {
    width: 100%;
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .top-logo {
    top: 10px;
    left: 10px;
  }

  .top-logo-image {
    width: 78px;
  }

  .verification-card {
    padding: 28px 20px;
  }

  .verification-card h2 {
    font-size: 20px;
  }

  .card-icon {
    font-size: 50px;
    margin-bottom: 16px;
  }

  .error-message {
    font-size: 13px;
    padding: 10px 12px;
  }
}
</style>