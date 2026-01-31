
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap'

import './assets/main.css'
import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import axios from 'axios'
import Login from './components/Login.vue'
import Registrate from './components/Registrate.vue'
import Aszf from './components/Aszf.vue'
import Privacy from './components/Privacy.vue'
import MainPage from './components/MainPage.vue'
import EventCreator from './components/EventCreator.vue'
import CommentBox from './components/CommentBox.vue'
import EventDetails from './components/EventDetails.vue'
import EventsList from './components/EventsList.vue'
import './style.css'

// Axios interceptor - automatikusan csatolja a tokent az Authorization header-be
axios.interceptors.request.use(config => {
  try {
    // Check for token in localStorage first, then in userData
    let token = localStorage.getItem('esemenyter_token');
    if (!token) {
      const userData = JSON.parse(localStorage.getItem('esemenyter_user') || '{}');
      token = userData.token;
    }
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
  } catch (error) {
    console.error('Token lekérési hiba:', error);
  }
  return config;
});

const app = createApp(App)
app.component('login', Login)
app.component('registrate', Registrate)
app.component('aszf', Aszf)
app.component('privacy', Privacy)
app.component('mainpage', MainPage)
app.component('event-creator', EventCreator)
app.component('comment-box', CommentBox)
app.component('event-details', EventDetails)
app.component('events-list', EventsList)
app.use(router)

app.mount('#app')

