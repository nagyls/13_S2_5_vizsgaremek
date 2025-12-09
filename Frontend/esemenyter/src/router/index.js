import { createRouter, createWebHistory } from 'vue-router'
import Login from '../components/Login.vue'
import Registrate from '../components/Registrate.vue'
import Aszf from '../components/Aszf.vue'
import Privacy from '../components/Privacy.vue'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: { title: 'Bejelentkezés' }
  },
  {
    path: '/register',
    name: 'register',
    component: Registrate,
    meta: { title: 'Regisztráció' }
  },
  {
    path: '/aszf',
    name: 'aszf',
    component: Aszf,
    meta: { title: 'ÁSZF' }
  },
  {
    path: '/privacy',
    name: 'privacy',
    component: Privacy,
    meta: { title: 'Adatvédelemi nyilatkozat' }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.afterEach((to) => {
  document.title = (to.meta.title ? to.meta.title + ' – EseményTér' : 'EseményTér');
});

export default router
