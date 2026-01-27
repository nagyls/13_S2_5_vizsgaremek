import { createRouter, createWebHistory } from 'vue-router'
import Login from '../components/Login.vue'
import Registrate from '../components/Registrate.vue'
import Aszf from '../components/Aszf.vue'
import Privacy from '../components/Privacy.vue'
import MainPage from '../components/MainPage.vue'
import EventCreator from '@/components/EventCreator.vue'
import CommentBox from '@/components/CommentBox.vue'
import EventDetails from '@/components/EventDetails.vue'
import EventsList from '@/components/EventsList.vue'

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
  },
  {
    path: '/',
    name: 'mainpage',
    component: MainPage,
    meta: { title: 'Főoldal' }
  },
  {
    path: '/event-creator',
    name: 'event-creator',
    component: EventCreator,
    meta: { title: 'Esemény létrehozása' }
  },
  {
    // FONTOS: Ez az útvonal ID paraméterrel kell legyen
    path: '/esemenyek/:id',  // VÁLTOZOTT
    name: 'event-details',
    component: EventDetails,
    meta: { title: 'Esemény részletei' },
    props: true  // Opcionális: átadja a paramétereket props-ként
  },
  {
    path: '/comment-box',
    name: 'comment-box',
    component: CommentBox,
    meta: { title: 'Kommentszekció'}
  },
  {
    path: '/events-list',
    name: 'events-list',
    component: EventsList,
    meta: { title: 'Eventek listázása' }
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/'
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