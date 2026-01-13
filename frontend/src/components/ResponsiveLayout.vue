<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header Responsive -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <!-- Logo -->
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <h1 class="text-xl sm:text-2xl font-bold text-indigo-600">
                VocalApp
              </h1>
            </div>
          </div>

          <!-- Menu Desktop -->
          <nav class="hidden md:flex space-x-8">
            <router-link 
              v-for="item in menuItems" 
              :key="item.name"
              :to="item.path"
              class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
              active-class="bg-indigo-100 text-indigo-700"
            >
              {{ item.name }}
            </router-link>
          </nav>

          <!-- Menu Mobile Toggle -->
          <div class="md:hidden">
            <button
              @click="toggleMobileMenu"
              class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Menu Mobile -->
      <div v-if="mobileMenuOpen" class="md:hidden">
        <div class="px-2 pt-2 pb-3 space-y-1">
          <router-link 
            v-for="item in menuItems" 
            :key="item.name"
            :to="item.path"
            class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50"
            @click="closeMobileMenu"
          >
            {{ item.name }}
          </router-link>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1">
      <div class="py-6 sm:py-8 lg:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <!-- Sidebar pour desktop -->
          <div class="hidden lg:fixed lg:inset-y-0 lg:left-0 lg:z-50 lg:w-64 lg:flex lg:flex-col">
            <div class="flex flex-col h-full bg-white border-r border-gray-200">
              <div class="flex items-center h-16 px-6">
                <h2 class="text-lg font-semibold text-gray-900">Menu</h2>
              </div>
              <nav class="flex-1 px-4 py-6 space-y-2">
                <router-link 
                  v-for="item in sidebarItems" 
                  :key="item.name"
                  :to="item.path"
                  class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-50"
                  active-class="bg-gray-100 text-gray-900"
                >
                  <component :is="item.icon" class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" />
                  {{ item.name }}
                </router-link>
              </nav>
            </div>
          </div>

          <!-- Contenu principal -->
          <div class="lg:pl-72">
            <div class="space-y-6">
              <!-- Breadcrumb pour mobile/tablette -->
              <nav class="flex mb-4 lg:hidden" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-sm">
                  <li>
                    <router-link to="/" class="text-gray-500 hover:text-gray-700">
                      Accueil
                    </router-link>
                  </li>
                  <li v-for="(crumb, index) in breadcrumbs" :key="index">
                    <div class="flex items-center">
                      <svg class="flex-shrink-0 h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 00-1.414 0L4.586 10l2.293 2.293a1 1 0 001.414 1.414l4-4a1 1 0 001.414 0z" clip-rule="evenodd" />
                      </svg>
                      <router-link :to="crumb.path" class="ml-2 text-gray-500 hover:text-gray-700">
                        {{ crumb.name }}
                      </router-link>
                    </div>
                  </li>
                </ol>
              </nav>

              <!-- Contenu de la page -->
              <div class="bg-white shadow rounded-lg p-4 sm:p-6 lg:p-8">
                <slot />
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Footer Responsive -->
    <footer class="bg-white border-t border-gray-200 mt-auto">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0 sm:space-x-8">
          <div class="text-sm text-gray-500">
            © 2024 VocalApp. Tous droits réservés.
          </div>
          <div class="flex space-x-6">
            <a href="#" class="text-gray-500 hover:text-gray-700 text-sm">
              Confidentialité
            </a>
            <a href="#" class="text-gray-500 hover:text-gray-700 text-sm">
              Conditions
            </a>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import { 
  HomeIcon, 
  CalendarIcon, 
  PhoneIcon, 
  MicrophoneIcon, 
  MessageIcon, 
  UserIcon 
} from '@heroicons/vue/24/outline'

const route = useRoute()
const mobileMenuOpen = ref(false)

const menuItems = [
  { name: 'Tableau de bord', path: '/dashboard', icon: HomeIcon },
  { name: 'Rendez-vous', path: '/rendez-vous', icon: CalendarIcon },
  { name: 'Appels', path: '/appels', icon: PhoneIcon },
  { name: 'Audio', path: '/audio', icon: MicrophoneIcon },
  { name: 'Messages', path: '/messages', icon: MessageIcon },
  { name: 'Profil', path: '/profile', icon: UserIcon }
]

const sidebarItems = [
  { name: 'Tableau de bord', path: '/dashboard', icon: HomeIcon },
  { name: 'Rendez-vous', path: '/rendez-vous', icon: CalendarIcon },
  { name: 'Appels', path: '/appels', icon: PhoneIcon },
  { name: 'Audio', path: '/audio', icon: MicrophoneIcon },
  { name: 'Messages', path: '/messages', icon: MessageIcon },
  { name: 'Événements', path: '/events', icon: CalendarIcon },
  { name: 'Calendrier', path: '/calendar', icon: CalendarIcon },
  { name: 'Profil', path: '/profile', icon: UserIcon }
]

const breadcrumbs = computed(() => {
  const pathSegments = route.path.split('/').filter(Boolean)
  const crumbs = []
  
  pathSegments.forEach((segment, index) => {
    if (index === 0) return
    
    const path = '/' + pathSegments.slice(0, index + 1).join('/')
    const name = segment.charAt(0).toUpperCase() + segment.slice(1)
    
    crumbs.push({ name, path })
  })
  
  return crumbs
})

const toggleMobileMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value
}

const closeMobileMenu = () => {
  mobileMenuOpen.value = false
}
</script>

<style scoped>
/* Styles spécifiques pour le responsive */
@media (max-width: 640px) {
  .sidebar-hidden {
    display: none;
  }
}

@media (min-width: 1024px) {
  .sidebar-desktop {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 16rem;
  }
}
</style>
