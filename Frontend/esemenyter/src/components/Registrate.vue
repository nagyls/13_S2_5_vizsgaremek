<template>
    <div class="register-page">
        <div class="register-wrapper">
            <form @submit.prevent="register">
                <h1>Regisztráció</h1>
                <div class="input-box">
                    <input type="text" v-model="username" placeholder="Felhasználónév" required>
                    <i class='bx bx-user'></i> 
                </div>
                <div class="input-box">
                    <input type="email" v-model="email" placeholder="Email cím" required>
                    <i class='bx bx-envelope'></i> 
                </div>
                <div class="input-box pass-box">
                    <input :type="showPass1 ? 'text' : 'password'" v-model="jelszo" placeholder="Jelszó" required>
                    <i :class="showPass1 ? 'bx bx-lock-open' : 'bx bx-lock'" @click="showPass1 = !showPass1" style="cursor: pointer;"></i>
                </div>
                <div class="input-box pass-box">
                    <input :type="showPass2 ? 'text' : 'password'" v-model="jelszo_meg" placeholder="Jelszó megerősítése" required>
                    <i :class="showPass2 ? 'bx bx-lock-open' : 'bx bx-lock'" @click="showPass2 = !showPass2" style="cursor: pointer;"></i>
                </div>
                <div class="aszf-check">
                    <label>
                        <input type="checkbox" v-model="acceptTerms" required>
                        <span class="aszf-text">
                            Elfogadom az <router-link to="/aszf" class="aszf-link">ÁSZF</router-link>-et
                            és egyetértek az <router-link to="/privacy" class="aszf-link">Adatvédelmi nyilatkozattal</router-link>.
                        </span>
                    </label>
                </div>

                <button type="submit" class="btn" :disabled="loading">
                    {{ loading ? 'Feldolgozás...' : 'Regisztráció' }}
                </button>

                <div class="login-link">
                    <p>Van már fiókod? <router-link to="/login">Bejelentkezés</router-link></p>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    name: 'Registrate',
    
    data() {
        return {
            username: "",
            email: "",
            jelszo: "",
            jelszo_meg: "",
            showPass1: false,
            showPass2: false,
            acceptTerms: false,
            loading: false
        };
    },
    
    methods: {
        async register() {
            // 1. Ellenőrzések
            if (this.jelszo !== this.jelszo_meg) {
                alert("A jelszavak nem egyeznek!");
                return;
            }

            if (this.jelszo.length < 6) {
                alert("A jelszónak legalább 6 karakter hosszúnak kell lennie!");
                return;
            }

            if (!this.acceptTerms) {
                alert("Kérjük, fogadd el az ÁSZF-et és az Adatvédelmi nyilatkozatot!");
                return;
            }

            this.loading = true;

            try {
                // 2. Backend API hívás
                const res = await axios.post("http://127.0.0.1:8000/api/register", {
                    username: this.username,
                    email: this.email,
                    password: this.jelszo,
                    password_confirmation: this.jelszo_meg
                    
                });

                console.log("Backend válasz:", res.data);

                // 3. Felhasználói adatok mentése localStorage-ba az API válaszból
                const userData = {
                    id: res.data.user.id,
                    name: res.data.user.name,
                    email: res.data.user.email,
                    token: res.data.token,
                    isLoggedIn: true,
                    registeredAt: new Date().toISOString()
                };
                
                // 4. LocalStorage-be mentés
                localStorage.setItem('esemenyter_user', JSON.stringify(userData));
                localStorage.setItem('esemenyter_token', res.data.token);
                console.log("User adatok mentve localStorage-ba:", userData);
                console.log("Token mentve:", res.data.token);
                
                // 5. Sikeres üzenet
                alert("Sikeres regisztráció! Átirányítás a főoldalra...");
                
                // 6. Rövid várakozás és átirányítás
                setTimeout(() => {
                    this.$router.push('/mainpage');
                }, 500);
                
            } catch (err) {
                console.error("Regisztrációs hiba részletei:", err);
                
                // Hibaüzenet megjelenítése
                const errorMsg = err.response?.data?.message || 
                               err.response?.data?.error || 
                               "Ismeretlen hiba történt";
                alert("Hiba: " + errorMsg);
            } finally {
                this.loading = false;
            }
        }
    },
    
    mounted() {
        console.log("Regisztrációs oldal betöltve");
        
        // Demo: ha már be vagy jelentkezve, átirányítás
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
}
</script>

<style scoped>
.register-page {
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

.register-wrapper {
    width: 420px;
    background: transparent;
    border: 2px solid rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(20px);
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    color: #fff;
    border-radius: 10px;
    padding: 30px 40px;
}

.register-wrapper h1 {
    font-size: 36px;
    text-align: center;
    font-weight: bold;
    margin-bottom: 20px;
}

.register-wrapper .input-box {
    position: relative;
    width: 100%;
    height: 50px;
    margin: 20px 0;
}

.pass-box {
    margin: 8px 0 !important;
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
    font-size: 16px;
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
    color: rgba(255, 255, 255, 0.7);
}

.aszf-check {
    margin: 15px 0;
    text-align: left;
}

.aszf-check label {
    font-size: 14px;
    display: flex;
    align-items: flex-start;
    cursor: pointer;
    line-height: 1.5;
}

.aszf-check label input {
    margin-top: 3px;
    margin-right: 10px;
    flex-shrink: 0;
    accent-color: #f5365c;
}

.aszf-text {
    flex: 1;
}

.aszf-link {
    color: #f5365c;
    text-decoration: none;
    font-weight: 500;
    transition: opacity 0.3s;
    white-space: nowrap;
}

.aszf-link:hover {
    opacity: 0.8;
    text-decoration: underline;
}

@media (max-width: 480px) {
    .aszf-check label {
        font-size: 13px;
        align-items: flex-start;
    }
    
    .aszf-link {
        white-space: normal;
    }
}

.register-wrapper .btn {
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
    margin-top: 20px;
    transition: all 0.3s ease;
}

.register-wrapper .btn:hover:not(:disabled) {
    background: rgb(40, 40, 59);
    color: #fff;
    transform: translateY(-2px);
}

.register-wrapper .btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.register-wrapper .login-link {
    font-size: 14.5px;
    text-align: center;
    margin-top: 20px;
}

.login-link p a {
    color: #fff;
    text-decoration: none;
    font-weight: 600;
    transition: opacity 0.3s;
}

.login-link p a:hover {
    text-decoration: underline;
    opacity: 0.8;
}

@media (max-width: 480px) {
    .register-wrapper {
        width: 90%;
        padding: 20px;
    }
    
    .register-wrapper h1 {
        font-size: 28px;
    }
}
</style>