<template>
    <div class="login-page">
        <div class="login-wrapper">
            <form @submit.prevent="login">
                <h1>Bejelentkezés</h1>
                <div class="input-box">
                    <input type="text" placeholder="Email cím" v-model="email" required >
                    <i class='bx  bx-user'></i> 
                </div>
                <div class="input-box">
                    <input :type="showPassword ? 'text' : 'password'" placeholder="Jelszó" v-model="password" required >
                    <i :class="showPassword ? 'bx bx-lock-open' : 'bx bx-lock'" @click="togglePassword" style="cursor: pointer;"></i>
                </div>
                <div class="remember-forgot">
                    <label>
                        <input type="checkbox" />Emlékezz rám
                    </label>
                    <a href="#">Elfelejtett jelszó</a>
                </div>

                <button type="submit" class="btn" :disabled="loading">
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

export default {
  name: 'Login',
  
  data() {
    return {
      email: "", // Csak a változó neve változott
      password: "",
      showPassword: false,
      loading: false
    };
  },
  
  methods: {
    togglePassword() {
      this.showPassword = !this.showPassword;
    },

    async login() {
      this.loading = true;
      
      try {
        // 1. Backend API hívás
        const res = await axios.post("http://127.0.0.1:8000/api/login", {
          email: this.email,
          password: this.password
        });

        console.log("Backend válasz:", res.data);
        
        // 2. Felhasználói adatok mentése localStorage-ba az API válaszból
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
        
        // 3. LocalStorage-be mentés
        localStorage.setItem('esemenyter_user', JSON.stringify(userData));
        localStorage.setItem('esemenyter_token', res.data.token);
        console.log("User adatok mentve localStorage-ba:", userData);
        console.log("Token mentve:", res.data.token);
        
        // 4. Sikeres üzenet
        alert("Sikeres bejelentkezés! Átirányítás a főoldalra...");
        
        // 5. Rövid várakozás és átirányítás
        setTimeout(() => {
          this.$router.push('/mainpage');
        }, 500);
        
      } catch (err) {
        console.error("Bejelentkezési hiba részletei:", err);
        
        // 6. Hibaüzenet megjelenítése
        const errorMsg = err.response?.data?.message || 
                       err.response?.data?.error || 
                       "Hibás email cím vagy jelszó!";
        alert("Hiba: " + errorMsg);
      } finally {
        this.loading = false;
      }
    }
  },
  
  mounted() {
    console.log("Bejelentkezési oldal betöltve");
    
    // Ha már be vagy jelentkezve, átirányítás a főoldalra
    const savedUser = localStorage.getItem('esemenyter_user');
    if (savedUser) {
      try {
        const userData = JSON.parse(savedUser);
        if (userData.isLoggedIn) {
          console.log("Már be vagy jelentkezve, átirányítás mainpage-re...");
          this.$router.push('/mainpage');
        }
      } catch (error) {
        console.error("Hibás user adatok:", error);
      }
    }
  }
};
</script>

<style scoped>
/* Stílusok változatlanok */
.login-page {
    box-sizing: border-box;
    font-family: "Poppins", sans-serif;

    display: flex;
    justify-content: center;
    align-items: center;
    
    min-height: 100vh;
    width: 100vw;

    background-image: url("./src/assets/login-img.jpg"); 
    background-position: center;
    background-size: cover; 
}
.login-wrapper {
    width: 420px;
    background: transparent;
    border: 2px solid rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(20px);
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    color: #fff;
    border-radius: 10px;
    padding: 30px 40px;
}

.login-wrapper h1 {
    font-size: 36px;
    text-align: center;
    font-weight: bold;
}

.login-wrapper .input-box {
    position: relative;
    width: 100%;
    height: 50px;
    margin: 30px 0;
}
.input-box input {
    width: 100%;
    height: 100%;
    background: transparent;
    border: none;
    outline: none;
    border: 2px solid rgba(255, 255, 255, 0.2);
    border-radius: 40px;
    padding-left: 20px;
    color: #fff;
}

.input-box input::placeholder {
    color: #fff;
}

.input-box i {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    
    font-size: 20px;
}

.login-wrapper .remember-forgot {
    display: flex;
    justify-content: space-between;
    font-size: 14.5px;
    margin: -15px 0 15px;
}
.remember-forgot label input {
    accent-color: #fff;
    margin-right: 3px;
}
.remember-forgot a:hover , .remember-forgot a {
    text-decoration: underline;
    color: #fff;
}

.login-wrapper .btn {
    width: 100%;
    height: 45px;
    background: #fff;
    border: none;
    outline: none;
    border-radius: 40px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    font-size: 16px;
    color: #333;
    font-weight: 600;
    transition: all 0.3s ease;
}

.login-wrapper .btn:hover:not(:disabled) {
    background: rgb(40, 40, 59);
    color: #fff;
    transform: translateY(-2px);
}

.login-wrapper .btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.login-wrapper .register-link {
    font-size: 14.5px;
    text-align: center;
    margin-top: 4px;
}
.register-link p a {
    color: #fff;
    text-decoration: none;
    font-weight: 600;
}
.register-link p a:hover {
    text-decoration: underline;
}
</style>