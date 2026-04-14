<template>
  <div class="forgot-password-page">
    <div class="forgot-password-wrapper">
      <form @submit.prevent="submitRequest" class="forgot-password-form">
        <div class="logo-icon" @click="$router.push('/login')">
          <img :src="logo2" alt="EseményTér logó" class="logo-image">
        </div>

        <h1>Elfelejtett jelszó</h1>
        <p class="intro-text">
          Add meg a regisztrált email címedet, és küldünk egy linket az új jelszó beállításához.
        </p>

        <div class="input-box">
          <input v-model="email" type="email" placeholder="Email cím" required>
          <i class='bx bx-envelope'></i>
        </div>

        <button type="submit" class="btn" :disabled="loading">
          {{ loading ? 'Küldés...' : 'Visszaállító email küldése' }}
        </button>

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
import { API_BASE } from '../../services/api'

export default {
  name: 'ForgotPassword',

  data() {
    return {
      email: '',
      loading: false,
      logo2,
    }
  },

  methods: {
    async submitRequest() {
      const normalizedEmail = String(this.email || '').trim().toLowerCase()
      if (!normalizedEmail) {
        toast.error('Add meg az email címet.')
        return
      }

      this.loading = true
      this.email = normalizedEmail

      try {
        const response = await axios.post(`${API_BASE}/forgot-password`, {
          email: normalizedEmail,
        })

        const successMessage = response?.data?.message || 'A jelszó-visszaállító email elküldve.'
        toast.success(successMessage)
      } catch (error) {
        const firstValidationError = Object.values(error?.response?.data?.errors || {})?.[0]?.[0]
        const message =
          firstValidationError ||
          error?.response?.data?.message ||
          'Nem sikerült elküldeni a jelszó-visszaállító emailt.'
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

.forgot-password-page {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  width: 100vw;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.forgot-password-wrapper {
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

.forgot-password-form {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.logo-icon {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-bottom: 18px;
  cursor: pointer;
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
  height: 55px;
  margin: 5px 0;
}

.input-box input {
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.1);
  border: none;
  outline: none;
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 30px;
  padding: 0 45px 0 20px;
  color: #fff;
  font-size: 16px;
  transition: all 0.3s ease;
}

.input-box input:focus {
  background: rgba(255, 255, 255, 0.15);
  border-color: #fff;
  box-shadow: 0 0 15px rgba(255, 255, 255, 0.2);
}

.input-box input::placeholder {
  color: rgba(255, 255, 255, 0.7);
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
  background: #fff;
  border: none;
  outline: none;
  border-radius: 30px;
  cursor: pointer;
  font-size: 16px;
  color: #667eea;
  font-weight: 600;
  transition: all 0.3s ease;
  overflow: hidden;
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

.btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
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
  margin-top: 4px;
}

.register-link a {
  color: #fff;
  text-decoration: none;
  font-weight: 600;
  position: relative;
}

.register-link a::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 0;
  height: 1px;
  background: #fff;
  transition: width 0.3s ease;
}

.register-link a:hover::after {
  width: 100%;
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

@media (max-width: 480px) {
  .forgot-password-wrapper {
    width: 90%;
    padding: 30px 20px;
  }

  h1 {
    font-size: 28px;
  }

  .input-box {
    height: 50px;
  }

  .logo-image {
    max-width: 110px;
  }
}
</style>