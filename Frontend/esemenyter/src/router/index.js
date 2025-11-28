import { createRouter, createWebHistory } from 'vue-router'
import Login from '../components/Login.vue'
import Registrate from '../components/Registrate.vue'
import Aszf from '../components/Aszf.vue'
import Privacy from '../components/Privacy.vue'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: Login
  },
  {
    path: '/register',
    name: 'register',
    component: Registrate
  },
  {
    path: '/aszf',
    name: 'aszf',
    component: Aszf
  },
  {
    path: '/privacy',
    name: 'privacy',
    component: Privacy
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
