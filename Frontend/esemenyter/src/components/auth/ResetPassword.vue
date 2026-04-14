<template>
  <div class="reset-password-page">
    <div class="reset-password-wrapper">
      <form @submit.prevent="submitReset" class="reset-password-form">
        <div class="logo-icon" @click="$router.push('/login')">
          <img :src="logo2" alt="EseményTér logó" class="logo-image">
        </div>

        <h1>Új jelszó beállítása</h1>
        <p class="intro-text">
          Add meg az email címedet és az új jelszavadat. A jelszónak legalább 6 karakteresnek kell lennie, és tartalmaznia kell kis- és nagybetűt, valamint számot.
        </p>

        <div v-if="!token || !email" class="status-box error">
          A visszaállító link hiányos vagy érvénytelen.
        </div>

        <template v-else>
          <div class="input-box">
            <input v-model="email" type="email" placeholder="Email cím" required>
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

        <div v-if="successMessage" class="status-box success">
          {{ successMessage }}
        </div>

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
  name: 'ResetPassword',

  data() {
    return {
      token: '',
      email: '',
      password: '',
      passwordConfirmation: '',
      loading: false,
      successMessage: '',
      showPassword: false,
      showPasswordConfirmation: false,
      logo2,
    }
  },

  created() {
    this.token = String(this.$route.query.token || '')
    this.email = String(this.$route.query.email || '')
  },

  methods: {
    togglePassword() {
      this.showPassword = !this.showPassword
    },

    togglePasswordConfirmation() {
      this.showPasswordConfirmation = !this.showPasswordConfirmation
    },

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
        this.successMessage = response?.data?.message || 'A jelszó sikeresen módosítva.'
        toast.success(this.successMessage)

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
  width: 460px;
  max-width: calc(100vw - 32px);
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 25px 45px rgba(0, 0, 0, 0.2);
  color: #fff;
  border-radius: 20px;
  padding: 40px;
}

.reset-password-form {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.logo-icon {
  display: flex;
  justify-content: center;
  cursor: pointer;
}

.logo-image {
  width: 110px;
}

h1 {
  text-align: center;
}

.intro-text {
  text-align: center;
  color: rgba(255, 255, 255, 0.86);
  line-height: 1.6;
}

.input-box {
  position: relative;
  width: 100%;
  height: 50px;
}

.input-box input {
  width: 100%;
  height: 100%;
  background: transparent;
  border: 2px solid rgba(255, 255, 255, 0.2);
  outline: none;
  border-radius: 40px;
  font-size: 16px;
  color: #fff;
  padding: 20px 45px 20px 20px;
}

.input-box input::placeholder {
  color: rgba(255, 255, 255, 0.72);
}

.input-box i {
  position: absolute;
  right: 20px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 20px;
  cursor: pointer;
}

.btn {
  width: 100%;
  height: 45px;
  border: none;
  outline: none;
  border-radius: 40px;
  box-shadow: 0 0 10px rgba(0, 0, 0, .1);
  cursor: pointer;
  font-size: 16px;
  color: #333;
  font-weight: 700;
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
}
</style>