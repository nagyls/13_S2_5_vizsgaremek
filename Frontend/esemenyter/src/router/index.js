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
import Dashboard from '@/components/Dashboard.vue'
import UserDashboard from '@/components/UserDashboard.vue'
import InstitutionManagerDashboard from '@/components/InstitutionManagerDashboard.vue'
import PendingApproval from '@/components/PendingApproval.vue'
import Profile from '@/components/Profile.vue'

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
    path: '/dashboard',
    name: 'dashboard',
    component: Dashboard,
    meta: { title: 'Irányítópult' }
  },
  {
    path: '/institution-dashboard',
    name: 'institution-dashboard',
    component: InstitutionManagerDashboard,
    meta: { title: 'Intézménykezelő Irányítópult' }
  },
  {
    path: '/user-dashboard',
    name: 'user-dashboard',
    component: UserDashboard,
    meta: { title: 'Felhasználói Irányítópult' }
  },
  {
    path: '/pending-approval',
    name: 'pending-approval',
    component: PendingApproval,
    meta: { title: 'Függőben lévő jóváhagyások' }
  },
  {
    path: '/profile',
    name: 'profile',
    component: Profile,
    meta: { title: 'Profil' }
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