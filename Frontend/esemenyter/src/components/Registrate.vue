<template>
    <div class="register-page">
        <div class="register-wrapper">
            <form>
                <h1>Regisztráció</h1>
                <div class="input-box">
                    <input type="text" placeholder="Felhasználónév" required >
                    <i class='bx  bx-user'></i> 
                </div>
                <div class="input-box">
                    <input type="email" placeholder="Email cím" required >
                    <i class='bxr  bx-envelope'></i> 
                </div>
                <div class="input-box pass-box">
                    <input type="password" placeholder="Jelszó" required >
                    <i class='bx  bx-lock'></i>
                </div>
                <div class="input-box pass-box">
                    <input type="password" placeholder="Jelszó megerősítése" required >
                    <i class='bx  bx-lock'></i>
                </div>
                <div class="aszf-check">
                    <label>
                        <input type="checkbox" required > Elfogadom az <router-link to="/aszf" style="color: #f5365c;">ÁSZF</router-link>-et és egyetértek az <router-link to="/privacy" style="color: #f5365c;">Adatvédelmi nyilatkozattal</router-link>.
                    </label>
                </div>

                <button type="submit" class="btn">Regisztráció</button>

                <div class="login-link">
                    <p>Van már fiókod? <router-link to="/login">Bejelentkezés</router-link></p>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
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
}

.register-wrapper .input-box {
    position: relative;
    width: 100%;
    height: 50px;
    margin: 30px 0px;
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
}
.aszf-check label{
    font-size: 13px;
}
.aszf-check label input {
    accent-color: #fff;
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
    margin-top: 25px;

    transition: background 0.5s ease, color 0.5s ease;
}


.register-wrapper .login-link {
    font-size: 14.5px;
    text-align: center;
    margin-top: 4px;
}
.login-link p a {
    color: #fff;
    text-decoration: none;
    font-weight: 600;
}
.login-link p a:hover {
    text-decoration: underline;
}
    
.btn:hover {
  background: rgb(40, 40, 59);
  color: #fff;
}
</style>