
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap'

import './assets/main.css'
import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import axios from 'axios'
import Login from './components/auth/Login.vue'
import Registrate from './components/auth/Registrate.vue'
import Aszf from './components/legal/Aszf.vue'
import Privacy from './components/legal/Privacy.vue'
import MainPage from './components/home/MainPage.vue'
import EventCreator from './components/events/EventCreator.vue'
import CommentBox from './components/events/CommentBox.vue'
import EventDetails from './components/events/EventDetails.vue'
import EventsList from './components/events/EventsList.vue'
import Dashboard from './components/dashboard/Dashboard.vue'
import InstitutionManagerDashboard from './components/dashboard/InstitutionManagerDashboard.vue'
import UserDashboard from './components/dashboard/UserDashboard.vue'
import PendingApproval from './components/dashboard/PendingApproval.vue'
import Profile from './components/profile/Profile.vue'
import ToastContainer from './components/toasts/Toast.vue'
import './style.css'

const AUTH_DEBUG = import.meta.env.VITE_DEBUG_AUTH === 'true'

// Axios interceptor - automatikusan csatolja a tokent az Authorization header-be
axios.interceptors.request.use(config => {
  try {
    if (!config.headers) {
      config.headers = {}
    }

    // Check for token in localStorage first, then in sessionStorage
    let token = localStorage.getItem('esemenyter_token')
    if (!token) {
      token = sessionStorage.getItem('esemenyter_token')
    }

    if (token) {
      config.headers.Authorization = `Bearer ${token}`
      if (AUTH_DEBUG) {
        console.log('Token hozzáadva a kéréshez:', token.substring(0, 20) + '...')
      }
    } else if (AUTH_DEBUG) {
      console.warn('Nincs token a kéréshez!')
    }
  } catch (error) {
    if (AUTH_DEBUG) {
      console.error('Token lekérési hiba:', error)
    }
  }
  return config
})

// Válasz interceptor - kezeli a 401-es hibákat
axios.interceptors.response.use(
  response => response,
  error => {
    if (error.response && error.response.status === 401) {
      console.error('401 hiba - jogosulatlan hozzáférés');
      
      // Token törlése
      localStorage.removeItem('esemenyter_token');
      localStorage.removeItem('esemenyter_user');
      sessionStorage.removeItem('esemenyter_token');
      sessionStorage.removeItem('esemenyter_user');
      
      // Ha nem a login oldalon vagyunk, irányítsunk át
      if (!window.location.pathname.includes('/login') && 
          !window.location.pathname.includes('/mainpage')) {
        window.location.href = '/mainpage';
      }
    }
    return Promise.reject(error);
  }
);

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
app.component('dashboard', Dashboard)
app.component('institution-dashboard', InstitutionManagerDashboard)
app.component('user-dashboard', UserDashboard)
app.component('pending-approval', PendingApproval)
app.component('profile', Profile)
app.component('toastcontainer', ToastContainer)
app.use(router)

app.mount('#app')

