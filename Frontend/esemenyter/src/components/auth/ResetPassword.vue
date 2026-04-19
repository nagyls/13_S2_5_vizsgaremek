<template>
  <div class="reset-password-page">
    <div class="reset-password-wrapper">
      <form @submit.prevent="submitReset" class="reset-password-form">
        <div class="logo-icon" @click="$router.push('/login')">
          <img :src="logo2" alt="EseményTér logó" class="logo-image">
        </div>

        <h1>Új jelszó beállítása</h1>
        <p class="intro-text">
          Adja meg az email címét és az új jelszávát. A jelszónak legalább 6 karakteresnek kell lennie, és tartalmaznia kell kis- és nagybetűt, valamint számot.
        </p>

        <div v-if="!token || !email" class="status-box error">
          A visszaállító link hiányos vagy érvénytelen.
        </div>

        <template v-else>
          <div class="input-box">
            <input v-model="email" type="email" placeholder="Email cím" required readonly class="email-input">
            <i class='bx bx-envelope'></i>
          </div>

          <div class="input-box">
            <input v-model="password" :type="showPassword ? 'text' : 'password'" placeholder="Új jelszó" required>
            <i :class="showPassword ? 'bx bx-lock-open' : 'bx bx-lock'" @click="togglePassword"></i>
          </div>

          <div class="input-box">
            <input v-model="passwordConfirmation" :type="showPasswordConfirmation ? 'text' : 'password'" placeholder="Új jelszó megerősítése" required>
            <i :class="showPasswordConfirmation ? 'bx bx-lock-open' : 'bx bx-lock'" @click="togglePasswordConfirmation"></i>
          </div>

          <button type="submit" class="btn" :disabled="loading">
            {{ loading ? 'Mentés...' : 'Új jelszó mentése' }}
          </button>
        </template>

        <div class="register-link">
          <p><router-link to="/login">Vissza a bejelentkezéshez</router-link></p>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import { toast } from '../../services/toast'
import logo2 from '../../assets/logo2.svg'
import { API_BASE, clearAuthStorage } from '../../services/api'

export default {
  /**
   * Jelszó-visszaállító nézet a tokenes link alapján.
   */
  name: 'ResetPassword',

  /**
   * A jelszó-visszaállítási űrlap lokális állapota.
   */
  data() {
    return {
      token: '',
      email: '',
      password: '',
      passwordConfirmation: '',
      loading: false,
      showPassword: false,
      showPasswordConfirmation: false,
      logo2,
    }
  },

  /**
   * Token és email előtöltése az URL query paraméterekből.
   */
  created() {
    this.token = String(this.$route.query.token || '')
    this.email = String(this.$route.query.email || '')
  },

  methods: {
    /**
     * Új jelszó mező láthatóságának váltása.
     */
    togglePassword() {
      this.showPassword = !this.showPassword
    },

    /**
     * Jelszó megerősítés mező láthatóságának váltása.
     */
    togglePasswordConfirmation() {
      this.showPasswordConfirmation = !this.showPasswordConfirmation
    },

    /**
     * Jelszó-visszaállítás beküldése validációval és sikeres átirányítással.
     */
    async submitReset() {
      if (!this.token || !this.email) {
        toast.error('A visszaállító link érvénytelen.')
        return
      }

      if (this.password !== this.passwordConfirmation) {
        toast.error('A két jelszó nem egyezik.')
        return
      }

      this.loading = true

      try {
        const response = await axios.post(`${API_BASE}/reset-password`, {
          token: this.token,
          email: this.email,
          password: this.password,
          password_confirmation: this.passwordConfirmation,
        })

        clearAuthStorage()
        const successMessage = response?.data?.message || 'A jelszó sikeresen módosítva.'
        toast.success(successMessage)

        setTimeout(() => {
          this.$router.push('/login')
        }, 2000)
      } catch (error) {
        const message = error?.response?.data?.message || 'A jelszó-visszaállítás nem sikerült.'
        toast.error(message)
      } finally {
        this.loading = false
      }
    },
  },
}
</script>

<!-- ResetPassword komponens stílusai -->
<style scoped>
* {
  box-sizing: border-box;
  font-family: "Poppins", sans-serif;
  margin: 0;
  padding: 0;
}

.reset-password-page {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  width: 100vw;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.reset-password-wrapper {
  width: 440px;
  max-width: calc(100vw - 32px);
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 25px 45px rgba(0, 0, 0, 0.2);
  color: #fff;
  border-radius: 20px;
  padding: 40px;
  animation: slideUp 0.6s ease-out;
}

.reset-password-form {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.logo-icon {
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  margin-bottom: 18px;
}

.logo-image {
  max-width: 120px;
  width: 100%;
  height: auto;
  display: block;
  margin: 0 auto;
}

h1 {
  text-align: center;
  font-size: 32px;
  margin-bottom: 10px;
  font-weight: 600;
}

.intro-text {
  text-align: center;
  color: rgba(255, 255, 255, 0.86);
  line-height: 1.6;
}

.input-box {
  position: relative;
  width: 100%;
}

.input-box input {
  width: 100%;
  height: 55px;
  background: rgba(255, 255, 255, 0.1);
  border: none;
  outline: none;
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 30px;
  font-size: 16px;
  color: #fff;
  padding: 0 45px 0 20px;
  transition: all 0.3s ease;
}

.input-box input::placeholder {
  color: rgba(255, 255, 255, 0.7);
}

.input-box input:focus {
  background: rgba(255, 255, 255, 0.15);
  border-color: #fff;
  box-shadow: 0 0 15px rgba(255, 255, 255, 0.2);
}

.email-input {
  cursor: not-allowed;
  opacity: 0.92;
}

.email-input:focus {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.3);
  box-shadow: none;
}

.input-box i {
  position: absolute;
  right: 20px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 20px;
  cursor: pointer;
  color: rgba(255, 255, 255, 0.8);
  transition: color 0.3s ease;
}

.input-box input:focus + i {
  color: #fff;
}

.btn {
  position: relative;
  width: 100%;
  height: 50px;
  border: none;
  outline: none;
  border-radius: 30px;
  background: #fff;
  cursor: pointer;
  font-size: 16px;
  color: #667eea;
  font-weight: 600;
  transition: all 0.3s ease;
  overflow: hidden;
}

.btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.btn::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 0;
  height: 0;
  border-radius: 50%;
  background: rgba(102, 126, 234, 0.2);
  transform: translate(-50%, -50%);
  transition: width 0.6s, height 0.6s;
}

.btn:hover:not(:disabled)::before {
  width: 300px;
  height: 300px;
}

.btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.status-box {
  padding: 14px 16px;
  border-radius: 16px;
  line-height: 1.5;
}

.status-box.success {
  background: rgba(34, 197, 94, 0.16);
  border: 1px solid rgba(134, 239, 172, 0.45);
}

.status-box.error {
  background: rgba(239, 68, 68, 0.16);
  border: 1px solid rgba(252, 165, 165, 0.45);
}

.register-link {
  font-size: 14.5px;
  text-align: center;
}

.register-link a {
  color: #fff;
  font-weight: 700;
  text-decoration: none;
}

.register-link a:hover {
  text-decoration: underline;
}

@media (max-width: 520px) {
  .reset-password-wrapper {
    width: 100%;
    padding: 30px 20px;
    border-radius: 20px;
  }

  h1 {
    font-size: 28px;
  }

  .intro-text {
    font-size: 14px;
  }

  .logo-image {
    max-width: 110px;
  }

  .input-box input {
    height: 50px;
  }
}
</style>