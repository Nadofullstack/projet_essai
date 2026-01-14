<script setup>
import { ref } from 'vue'
import { useRoute } from 'vue-router'

const props = defineProps({
  isCollapsed: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['toggle'])

const route = useRoute()

// Données utilisateur
const user = ref({
  name: 'Jean Dupont',
  email: 'jean@example.com',
  avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Jean'
})

// Navigation principale
const mainNav = ref([
  { to: '/dashboard', icon: 'dashboard', label: 'Tableau de bord' },
  { to: '/messages', icon: 'chat', label: 'Messages' },
  { to: '/appels', icon: 'phone', label: 'Appels' },
  { to: '/audio', icon: 'mic', label: 'Audio' },
  { to: '/rendez-vous', icon: 'event', label: 'Rendez-vous' },
  { to: '/evenements', icon: 'calendar_month', label: 'Événements' }
])

// Navigation secondaire
const secondaryNav = ref([
  { to: '/settings', icon: 'settings', label: 'Paramètres' },
  { to: '/help', icon: 'help', label: 'Aide' }
])

// Fonction de déconnexion
const logout = () => {
  // Supprimer le token d'authentification
  localStorage.removeItem('auth_token')
  localStorage.removeItem('user')
  sessionStorage.removeItem('authToken')
  
  // Rediriger vers la page de login
  window.location.href = '/login'
}
</script>

<template>
  <aside 
    :class="[
      'fixed left-0 top-0 h-screen bg-white border-r border-gray-200 z-40 transition-all duration-300 flex flex-col',
      props.isCollapsed ? 'w-16' : 'w-64'
    ]"
  >
    <!-- Logo -->
    <div class="p-4 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <div v-if="!props.isCollapsed" class="flex items-center gap-3">
          <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
            <span class="material-symbols-outlined text-white text-lg">waves</span>
          </div>
          <span class="font-bold text-gray-900 text-xl">VocalApp</span>
        </div>
        <button 
          @click="emit('toggle')"
          class="p-1.5 hover:bg-gray-100 rounded-lg transition-colors"
        >
          <span class="material-symbols-outlined text-gray-600">
            {{ props.isCollapsed ? 'menu_open' : 'menu' }}
          </span>
        </button>
      </div>
    </div>

    <!-- Navigation principale -->
    <nav class="flex-1 p-4 space-y-1">
      <router-link
        v-for="item in mainNav"
        :key="item.to"
        :to="item.to"
        :class="[
          'flex items-center rounded-lg px-3 py-3 transition-all hover:bg-gray-50',
          route.path === item.to 
            ? 'bg-blue-50 text-blue-600 font-medium' 
            : 'text-gray-700 hover:text-gray-900'
        ]"
      >
        <span class="material-symbols-outlined">{{ item.icon }}</span>
        <span v-if="!props.isCollapsed" class="ml-3">{{ item.label }}</span>
      </router-link>
    </nav>

    <!-- Navigation secondaire -->
    <nav class="p-4 border-t border-gray-200 space-y-1">
      <router-link
        v-for="item in secondaryNav"
        :key="item.to"
        :to="item.to"
        :class="[
          'flex items-center rounded-lg px-3 py-3 transition-colors hover:bg-gray-50',
          route.path === item.to 
            ? 'bg-gray-100 text-gray-900 font-medium' 
            : 'text-gray-600 hover:text-gray-900'
        ]"
      >
        <span class="material-symbols-outlined">{{ item.icon }}</span>
        <span v-if="!props.isCollapsed" class="ml-3">{{ item.label }}</span>
      </router-link>
    </nav>

    <!-- User profile -->
    <div class="p-4 border-t border-gray-200">
      <div class="flex items-center mb-3">
        <img 
          :src="user.avatar" 
          :alt="user.name"
          class="w-8 h-8 rounded-full"
        />
        <div v-if="!props.isCollapsed" class="ml-3 flex-1">
          <p class="text-sm font-medium text-gray-900">{{ user.name }}</p>
          <p class="text-xs text-gray-500">{{ user.email }}</p>
        </div>
        <button v-if="!props.isCollapsed" class="ml-2 p-1 hover:bg-gray-100 rounded">
          <span class="material-symbols-outlined text-gray-500 text-sm">more_vert</span>
        </button>
      </div>
      
      <!-- Bouton de déconnexion -->
      <button 
        @click="logout"
        :class="[
          'flex items-center w-full rounded-lg px-3 py-2 transition-colors hover:bg-red-50 hover:text-red-600 text-gray-600',
          props.isCollapsed ? 'justify-center' : ''
        ]"
      >
        <span class="material-symbols-outlined">logout</span>
        <span v-if="!props.isCollapsed" class="ml-3">Déconnexion</span>
      </button>
    </div>
  </aside>
</template>

<style scoped>
/* Transitions smooth */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Scrollbar personnalisée */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #a1a1a1;
}
</style>
