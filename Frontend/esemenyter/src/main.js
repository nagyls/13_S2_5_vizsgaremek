
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap'

import './assets/main.css'
import { createApp } from 'vue'
import App from './App.vue'
import Login from './components/Login.vue'
import './style.css'

const app = createApp(App)
app.component('login', Login)
app.mount('#app')