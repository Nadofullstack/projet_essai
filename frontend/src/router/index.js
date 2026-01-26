import { createRouter, createWebHistory } from 'vue-router'
import Login from '@/views/auth/Login.vue'
import Register from '@/views/auth/Register.vue'
import Dashboard from '@/views/Dashboard.vue'
import Messages from '@/views/Messages.vue'
import Appels from '@/views/Appels.vue'
import RendezVous from '@/views/RendezVous.vue'
import Evenements from '@/views/Evenements.vue'
import AudioManager from '@/views/AudioManager.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: Login,
      meta: { requiresAuth: false }
    },
    {
      path: '/register',
      name: 'register',
      component: Register,
      meta: { requiresAuth: false }
    },
    // Tableau de bord principal
    {
      path: '/dashboard',
      name: 'dashboard',
      component: Dashboard,
      meta: { requiresAuth: true }
    },
    // Routes principales des fonctionnalités
    {
      path: '/messages',
      name: 'messages',
      component: Messages,
      meta: { requiresAuth: true }
    },
  
    {
      path: '/rendez-vous',
      name: 'rendez-vous',
      component: RendezVous,
      meta: { requiresAuth: true }
    },
   
 
    // Routes par défaut
    {
      path: '/:pathMatch(.*)*',
      redirect: '/dashboard'
    }
  ],
})

//navigation global

router.beforeEach((to,from,next) =>{
   const isAuthenticated = !!localStorage.getItem('auth_token')
 
   //non connecté , par de dashboard
    if(to.meta.requiresAuth && !isAuthenticated){
      next({name:'login'})
    }

    //connecté, pas de login/register
    else if((to.name === 'login' || to.name === 'register') && isAuthenticated){
      next({name:'dashboard'})
    }
    else{
      next()
    }
})

export default router
