<template>
    <div class="login-page">
        <div class="login-wrapper">
            <form @submit.prevent="login">
                <h1>Bejelentkezés</h1>
                <div class="input-box">
                    <input type="text" placeholder="Felhasználónév" v-model="username" required >
                    <i class='bx  bx-user'></i> 
                </div>
                <div class="input-box">
                    <input type="password" placeholder="Jelszó" v-model="password" required >
                    <i class='bx  bx-lock'></i>
                </div>
                <div class="remember-forgot">
                    <label>
                        <input type="checkbox" />Emlékezz rám
                    </label>
                    <a href="#">Elfelejtett jelszó</a>
                </div>

                <button type="submit" class="btn">Bejelentkezés</button>

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
  data() {
    return {
      username: "",
      password: ""
    };
  },
  methods: {
    async login() {
      try {
        const res = await axios.post("http://127.0.0.1:8000/api/login", {
          username: this.username,
          password: this.password
        });

        alert(res.data.message);
        this.$router.push("/"); // később dashboard
      } catch (err) {
        console.log(err);
        alert("Hibás felhasználónév vagy jelszó!");
      }
    }
  }
};
</script>

<style scoped>
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

    transition: background 0.5s ease, color 0.5s ease;
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
    
.btn:hover {
  background: rgb(40, 40, 59);
  color: #fff;
}
</style>