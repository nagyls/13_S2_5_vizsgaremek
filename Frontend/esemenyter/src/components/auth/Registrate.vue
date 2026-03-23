<template>
    <div class="register-page">
        <div class="register-wrapper">
            <form @submit.prevent="register">
                                <div class="logo-icon">
                                    <img :src="logo2" alt="EseményTér logó" class="logo-image">
                                </div>
                                <h1>Regisztráció</h1>
                
                <div class="input-box">
                    <input id="username" type="text" v-model="username" placeholder="Teljes név" required>
                    <i class='bx bx-user'></i> 
                </div>
                
                <div class="input-box">
                    <input id="email" type="email" v-model="email" placeholder="Email cím" required>
                    <i class='bx bx-envelope'></i> 
                </div>
                
                <div class="input-box">
                    <input id="password" :type="showPass1 ? 'text' : 'password'" v-model="jelszo" placeholder="Jelszó" required>
                    <i :class="showPass1 ? 'bx bx-lock-open' : 'bx bx-lock'" @click="showPass1 = !showPass1"></i>
                </div>
                
                <div class="input-box">
                    <input id="password_confirmation" :type="showPass2 ? 'text' : 'password'" v-model="jelszo_meg" placeholder="Jelszó megerősítése" required>
                    <i :class="showPass2 ? 'bx bx-lock-open' : 'bx bx-lock'" @click="showPass2 = !showPass2"></i>
                </div>
                
                <div class="password-strength" v-if="jelszo.length > 0">
                    <div class="strength-bar">
                        <div class="strength-fill" :class="passwordStrengthClass" :style="{ width: passwordStrength + '%' }"></div>
                    </div>
                    <span class="strength-text" :class="passwordStrengthClass">{{ passwordStrengthText }}</span>
                </div>
                
                <div class="terms-check">
                    <label class="checkbox-container">
                        <input id="accept_terms" type="checkbox" v-model="acceptTerms" required>
                        <span class="checkmark"></span>
                        <span class="terms-text">
                            Elfogadom az <router-link to="/aszf" class="terms-link">ÁSZF</router-link>-et
                            és egyetértek az <router-link to="/privacy" class="terms-link">Adatvédelmi nyilatkozattal</router-link>.
                        </span>
                    </label>
                </div>

                <button id="register_btn" type="submit" class="btn" :disabled="loading">
                    <span style="font-weight: bold;" v-if="!loading">Regisztráció</span>
                    <span v-else class="loader"></span>
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
import { toast } from '../../services/toast'
import logo2 from '../../assets/logo2.svg';

export default {
    name: 'Register',
    
    data() {
        return {
            username: "",
            email: "",
            jelszo: "",
            jelszo_meg: "",
            showPass1: false,
            showPass2: false,
            acceptTerms: false,
            loading: false,
            logo2
        };
    },
    
    computed: {
        passwordStrength() {
            let strength = 0;
            const password = this.jelszo;
            
            if (password.length >= 6) strength += 20;
            if (password.length >= 8) strength += 10;
            if (/[a-z]/.test(password)) strength += 15;
            if (/[A-Z]/.test(password)) strength += 15;
            if (/[0-9]/.test(password)) strength += 20;
            if (/[^A-Za-z0-9]/.test(password)) strength += 20;
            
            return Math.min(100, strength);
        },
        
        passwordStrengthClass() {
            if (this.passwordStrength < 40) return 'weak';
            if (this.passwordStrength < 70) return 'medium';
            return 'strong';
        },
        
        passwordStrengthText() {
            if (this.passwordStrength < 40) return 'Gyenge';
            if (this.passwordStrength < 70) return 'Közepes';
            return 'Erős';
        }
    },
    
    methods: {
        async register() {
            if (this.jelszo !== this.jelszo_meg) {
                toast.error("A jelszavak nem egyeznek!");
                return;
            }

            if (this.jelszo.length < 6) {
                toast.error("A jelszónak legalább 6 karakter hosszúnak kell lennie!");
                return;
            }

            if (!this.acceptTerms) {
                toast.error("Kérjük, fogadd el az ÁSZF-et és az Adatvédelmi nyilatkozatot!");
                return;
            }

            this.loading = true;

            try {
                const res = await axios.post("http://127.0.0.1:8000/api/register", {
                    username: this.username,
                    email: this.email,
                    password: this.jelszo,
                    password_confirmation: this.jelszo_meg
                });

                const userData = {
                    id: res.data.user.id,
                    name: res.data.user.name,
                    email: res.data.user.email,
                    token: res.data.token,
                    isLoggedIn: true,
                    registeredAt: new Date().toISOString()
                };
                
                localStorage.setItem('esemenyter_user', JSON.stringify(userData));
                localStorage.setItem('esemenyter_token', res.data.token);
                
                toast.success("Sikeres regisztráció! Átirányítás...");
                
                setTimeout(() => {
                    this.$router.push('/dashboard');
                }, 1500);
                
            } catch (err) {
                const errorMsg = err.response?.data?.message || 
                               err.response?.data?.error || 
                               "Ismeretlen hiba történt";
                toast.error("Hiba: " + errorMsg);
            } finally {
                this.loading = false;
            }
        }
    },
    
    mounted() {
        const savedUser = localStorage.getItem('esemenyter_user');
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
}
</script>

<style scoped>
* {
    box-sizing: border-box;
    font-family: "Poppins", sans-serif;
    margin: 0;
    padding: 0;
}

.register-page {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    width: 100vw;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.register-wrapper {
    width: 450px;
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

.register-wrapper h1 {
    font-size: 32px;
    text-align: center;
    margin-bottom: 20px;
    font-weight: 600;
}

.register-wrapper .input-box {
    position: relative;
    width: 100%;
    height: 55px;
    margin: 15px 0;
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
    cursor: pointer;
}

.input-box input:focus + i {
    color: #fff;
}

.password-strength {
    margin: 10px 0 15px;
}

.strength-bar {
    height: 4px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 2px;
    overflow: hidden;
    margin-bottom: 5px;
}

.strength-fill {
    height: 100%;
    transition: width 0.3s ease, background-color 0.3s ease;
}

.strength-fill.weak {
    background: #ff4757;
}

.strength-fill.medium {
    background: #ffa502;
}

.strength-fill.strong {
    background: #26de81;
}

.strength-text {
    font-size: 12px;
    font-weight: 500;
}

.strength-text.weak {
    color: #ff4757;
}

.strength-text.medium {
    color: #ffa502;
}

.strength-text.strong {
    color: #26de81;
}

.terms-check {
    margin: 20px 0;
}

.checkbox-container {
    display: flex;
    align-items: flex-start;
    position: relative;
    padding-left: 35px;
    cursor: pointer;
    font-size: 14px;
    user-select: none;
    line-height: 1.5;
}

.checkbox-container input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
    height: 0;
    width: 0;
}

.checkmark {
    position: absolute;
    top: 2px;
    left: 0;
    height: 20px;
    width: 20px;
    background: rgba(255, 255, 255, 0.1);
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 5px;
    transition: all 0.3s ease;
}

.checkbox-container:hover input ~ .checkmark {
    background: rgba(255, 255, 255, 0.2);
    border-color: rgba(255, 255, 255, 0.5);
}

.checkbox-container input:checked ~ .checkmark {
    background: #667eea;
    border-color: #667eea;
}

.checkmark:after {
    content: "";
    position: absolute;
    display: none;
}

.checkbox-container input:checked ~ .checkmark:after {
    display: block;
}

.checkbox-container .checkmark:after {
    left: 6px;
    top: 2px;
    width: 5px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

.terms-text {
    flex: 1;
    color: rgba(255, 255, 255, 0.9);
}

.terms-link {
    color: #fff;
    text-decoration: none;
    font-weight: 600;
    position: relative;
}

.terms-link::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 1px;
    background: #fff;
    transition: width 0.3s ease;
}

.terms-link:hover::after {
    width: 100%;
}

.register-wrapper .btn {
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
    margin-top: 10px;
}

.register-wrapper .btn::before {
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

.register-wrapper .btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.register-wrapper .btn:hover:not(:disabled)::before {
    width: 300px;
    height: 300px;
}

.register-wrapper .btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.loader {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(102, 126, 234, 0.3);
    border-radius: 50%;
    border-top-color: #667eea;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.register-wrapper .login-link {
    font-size: 14.5px;
    text-align: center;
    margin-top: 20px;
}

.login-link p {
    color: rgba(255, 255, 255, 0.9);
}

.login-link p a {
    color: #fff;
    text-decoration: none;
    font-weight: 600;
    position: relative;
}

.login-link p a::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 1px;
    background: #fff;
    transition: width 0.3s ease;
}

.login-link p a:hover::after {
    width: 100%;
}

@media (max-width: 500px) {
    .register-wrapper {
        width: 90%;
        padding: 30px 20px;
    }
    
    .register-wrapper h1 {
        font-size: 28px;
    }
    
    .input-box {
        height: 50px !important;
    }
    
    .terms-text {
        font-size: 13px;
    }
}

/* Logo */
.logo-icon {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 18px;
}
.logo-image {
    max-width: 120px;
    width: 100%;
    height: auto;
    display: block;
    margin: 0 auto;
}
</style>
