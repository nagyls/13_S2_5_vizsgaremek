<template>
    <div class="login-page">
        <div class="login-wrapper">
            <form @submit.prevent="login">
                <h1>Bejelentkezés</h1>
                <div class="input-box">
                    <input id="email" type="text" placeholder="Email cím" v-model="email" required >
                    <i class='bx bx-user'></i> 
                </div>
                <div class="input-box">
                    <input id="password" :type="showPassword ? 'text' : 'password'" placeholder="Jelszó" v-model="password" required >
                    <i :class="showPassword ? 'bx bx-lock-open' : 'bx bx-lock'" @click="togglePassword" style="cursor: pointer;"></i>
                </div>
                <div class="remember-forgot">
                    <label>
                        <input type="checkbox" v-model="rememberMe" />Emlékezz rám
                    </label>
                </div>

                <button style="font-weight: bold;" id="login_btn" type="submit" class="btn" :disabled="loading">
                    {{ loading ? 'Bejelentkezés...' : 'Bejelentkezés' }}
                </button>

                <div class="register-link">
                    <p>Nincs még fiókod? <router-link to="/register">Regisztráció</router-link></p>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import { toast } from '../services/toast'

export default {
  name: 'Login',
  
  data() {
    return {
      email: "",
      password: "",
      showPassword: false,
      loading: false,
      rememberMe: false
    };
  },
  
  methods: {
    togglePassword() {
      this.showPassword = !this.showPassword;
    },

    async login() {
      this.loading = true;
      
      try {
        const res = await axios.post("http://127.0.0.1:8000/api/login", {
          email: this.email,
          password: this.password
        });

        console.log("Backend válasz:", res.data);
        
        const userData = {
          id: res.data.user.id,
          name: res.data.user.name,
          email: res.data.user.email,
          token: res.data.token,
          is_teacher: res.data.is_teacher || false,
          is_student: res.data.is_student || false,
          establishment_ids: res.data.establishment_ids || [],
          isLoggedIn: true,
          loggedInAt: new Date().toISOString()
        };
        
        if (this.rememberMe) {
          localStorage.setItem('esemenyter_user', JSON.stringify(userData));
          localStorage.setItem('esemenyter_token', res.data.token);
        } else {
          sessionStorage.setItem('esemenyter_user', JSON.stringify(userData));
          sessionStorage.setItem('esemenyter_token', res.data.token);
        }
        
        setTimeout(() => {
          this.$router.push('/dashboard');
        }, 500);
        
      } catch (err) {
        console.error("Bejelentkezési hiba:", err);
        
        const errorMsg = err.response?.data?.message || 
                       err.response?.data?.error || 
                       "Hibás email cím vagy jelszó!";
        toast.error("Hiba: " + errorMsg);
      } finally {
        this.loading = false;
      }
    }
  },
  
  mounted() {
    const savedUser = localStorage.getItem('esemenyter_user') || sessionStorage.getItem('esemenyter_user');
    if (savedUser) {
      try {
        const userData = JSON.parse(savedUser);
        if (userData.isLoggedIn) {
          this.$router.push('/dashboard');
        }
      } catch (error) {
        console.error("Hibás user adatok:", error);
      }
    }
  }
};
</script>

<style scoped>
* {
    box-sizing: border-box;
    font-family: "Poppins", sans-serif;
    margin: 0;
    padding: 0;
}

.login-page {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    width: 100vw;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.login-wrapper {
    width: 420px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 25px 45px rgba(0, 0, 0, 0.2);
    color: #fff;
    border-radius: 20px;
    padding: 40px;
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

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.login-wrapper h1 {
    font-size: 32px;
    text-align: center;
    margin-bottom: 30px;
    font-weight: 600;
}

.login-wrapper .input-box {
    position: relative;
    width: 100%;
    height: 55px;
    margin: 25px 0;
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
    color: rgba(255, 255, 255, 0.8);
    transition: color 0.3s ease;
}

.input-box input:focus + i {
    color: #fff;
}

.login-wrapper .remember-forgot {
    display: flex;
    justify-content: space-between;
    font-size: 14.5px;
    margin: 20px 0;
}

.remember-forgot label {
    display: flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
}

.remember-forgot label input {
    width: 16px;
    height: 16px;
    cursor: pointer;
    accent-color: #667eea;
}

.login-wrapper .btn {
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

.login-wrapper .btn::before {
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

.login-wrapper .btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.login-wrapper .btn:hover:not(:disabled)::before {
    width: 300px;
    height: 300px;
}

.login-wrapper .btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.login-wrapper .register-link {
    font-size: 14.5px;
    text-align: center;
    margin-top: 20px;
}

.register-link p {
    color: rgba(255, 255, 255, 0.9);
}

.register-link p a {
    color: #fff;
    text-decoration: none;
    font-weight: 600;
    position: relative;
}

.register-link p a::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 1px;
    background: #fff;
    transition: width 0.3s ease;
}

.register-link p a:hover::after {
    width: 100%;
}

@media (max-width: 480px) {
    .login-wrapper {
        width: 90%;
        padding: 30px 20px;
    }
    
    .login-wrapper h1 {
        font-size: 28px;
    }
}
</style>