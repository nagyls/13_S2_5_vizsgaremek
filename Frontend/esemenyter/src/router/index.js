import { createRouter, createWebHistory } from 'vue-router'
import Login from '../components/auth/Login.vue'
import Registrate from '../components/auth/Registrate.vue'
import EmailVerification from '../components/auth/EmailVerification.vue'
import ForgotPassword from '../components/auth/ForgotPassword.vue'
import ResetPassword from '../components/auth/ResetPassword.vue'
import Aszf from '../components/legal/Aszf.vue'
import Privacy from '../components/legal/Privacy.vue'
import MainPage from '../components/home/MainPage.vue'
import EventCreator from '@/components/events/EventCreator.vue'
import CommentBox from '@/components/events/CommentBox.vue'
import EventDetails from '@/components/events/EventDetails.vue'
import EventManage from '@/components/events/EventManage.vue'
import EventsList from '@/components/events/EventsList.vue'
import EventCalendar from '@/components/events/EventCalendar.vue'
import Dashboard from '@/components/dashboard/Dashboard.vue'
import UserDashboard from '@/components/dashboard/UserDashboard.vue'
import InstitutionManagerDashboard from '@/components/dashboard/InstitutionManagerDashboard.vue'
import PendingApproval from '@/components/dashboard/PendingApproval.vue'
import Profile from '@/components/profile/Profile.vue'
import ApprovalRejected from '@/components/dashboard/ApprovalRejected.vue'

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
    path: '/verify-email/:id/:hash',
    name: 'verify-email',
    component: EmailVerification,
    meta: { title: 'Email megerősítés' }
  },
  {
    path: '/forgot-password',
    name: 'forgot-password',
    component: ForgotPassword,
    meta: { title: 'Elfelejtett jelszó' }
  },
  {
    path: '/reset-password',
    name: 'reset-password',
    component: ResetPassword,
    meta: { title: 'Új jelszó beállítása' }
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
    meta: { title: 'Adatvédelmi nyilatkozat' }
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
    path: '/esemenyek/:id/kezeles',
    name: 'event-manage',
    component: EventManage,
    meta: { title: 'Esemény kezelése' },
    props: true
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
    meta: { title: 'Események listázása' }
  },
  {
    path: '/events-calendar',
    name: 'events-calendar',
    component: EventCalendar,
    meta: { title: 'Eseménynaptár' }
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
    path: '/approval-rejected',
    name: 'approval-rejected',
    component: ApprovalRejected,
    meta: { title: 'Elutasított jóváhagyások' }
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

router.beforeEach((to, from, next) => {
  const savedUserRaw =
    localStorage.getItem('esemenyter_user') ||
    sessionStorage.getItem('esemenyter_user');

  let role = '';
  if (savedUserRaw) {
    try {
      role = JSON.parse(savedUserRaw)?.role || '';
    } catch (error) {
      role = '';
    }
  }

  const isStudentOrTeacher = role === 'student' || role === 'teacher';

  const allowRoleChooser = to.path === '/dashboard' && String(to.query?.chooseRole || '') === '1';

  if (isStudentOrTeacher && to.path === '/dashboard' && !allowRoleChooser) {
    next('/user-dashboard');
    return;
  }

  next();
});

router.afterEach((to) => {
  document.title = (to.meta.title ? to.meta.title + ' – EseményTér' : 'EseményTér');
  window.scrollTo(0, 0);
});

export default router