<template>
    <div class="login-page">
        <div class="login-wrapper">
            <form>
                <h1>Bejelentkezés</h1>
                <div class="input-box">
                    <input type="text" placeholder="Felhasználónév" required />
                </div>
                <div class="input-box">
                    <input type="password" placeholder="Jelszó" required />
                </div>
                <div class="remember-forgot">
                    <label>
                        <input type="checkbox" />Emlékezz rám
                    </label>
                    <a href="#">Elfelejtett jelszó</a>
                </div>

                <button type="submit" class="btn">Bejelentkezés</button>

                <div class="register-link">
                    <p>Nincs még fiókod? <a href="#">Regisztráció</a></p>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
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
    background: seagreen;

    /*
    margin: 0;
    padding: 0;

    background: url("img.jpg") no-repeat; 
    background-position: center; 
    */
}
.login-wrapper {
    width: 420px;
    background: lightblue;
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
    width: 100%;
    height: 50px;
    /* background: salmon; */
    margin: 30px 0;
}
.input-box input {
    width: 100%;
    height: 100%;
    background: transparent;
    border: none;
    outline: none;
    border: 4px solid rgba(255, 255, 255, 0.2);
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
.remember-forgot a:hover {
    text-decoration: underline;
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