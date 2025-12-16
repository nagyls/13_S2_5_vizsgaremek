
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap'

import './assets/main.css'
import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import Login from './components/Login.vue'
import Registrate from './components/Registrate.vue'
import Aszf from './components/Aszf.vue'
import Privacy from './components/Privacy.vue'
import MainPage from './components/MainPage.vue'
import './style.css'

const app = createApp(App)
app.component('login', Login)
app.component('registrate', Registrate)
app.component('aszf', Aszf)
app.component('privacy', Privacy)
app.component('mainpage', MainPage)
app.use(router)
app.mount('#app')

