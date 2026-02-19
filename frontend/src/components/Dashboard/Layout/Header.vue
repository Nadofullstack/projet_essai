<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  user: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['toggle-sidebar'])

const showUserMenu = ref(false)

// Computed properties pour s'assurer que les données existent
const userName = computed(() => {
  if (!props.user) return 'Utilisateur'
  return props.user.name || 'Utilisateur'
})

const userEmail = computed(() => {
  if (!props.user) return ''
  return props.user.email || ''
})

const userAvatar = computed(() => {
  if (!props.user) return 'https://api.dicebear.com/7.x/avataaars/svg?seed=default'
  return props.user.avatar || `https://api.dicebear.com/7.x/avataaars/svg?seed=${props.user.email || 'user'}`
})

// Watch pour debug
watch(() => props.user, (newVal) => {
  if (newVal?.name || newVal?.email) {
    console.log('✓ Header: Données utilisateur reçues:', newVal)
  }
}, { deep: true })

const toggleUserMenu = () => {
  showUserMenu.value = !showUserMenu.value
}

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
  <header class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center py-4">
        <!-- Left side -->
        <div class="flex items-center space-x-4">
          <button 
            @click="$emit('toggle-sidebar')"
            class="p-2 rounded-lg hover:bg-gray-100 lg:hidden"
          >
            <span class="material-symbols-outlined text-gray-600">menu</span>
          </button>
          
          <div class="flex items-center space-x-2">
            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
              <span class="material-symbols-outlined text-white text-lg">waves</span>
            </div>
            <h1 class="text-xl font-bold text-gray-900 hidden sm:block">VocalApp</h1>
          </div>
        </div>

        <!-- Right side -->
        <div class="flex items-center space-x-4">
          <!-- Notifications -->
       

          <!-- User menu -->
          <div class="relative">
            <button 
              @click="toggleUserMenu"
              class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100"
            >
              <img 
                :src="userAvatar" 
                :alt="userName"
                class="w-8 h-8 rounded-full"
              />
              <span class="material-symbols-outlined text-gray-600">expand_more</span>
            </button>

            <!-- Dropdown menu -->
            <div 
              v-if="showUserMenu"
              class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
            >
              <div class="px-4 py-2 border-b border-gray-100">
                <p class="text-sm font-medium text-gray-900">{{ userName }}</p>
                <p class="text-xs text-gray-500">{{ userEmail }}</p>
              </div>
              <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                <span class="material-symbols-outlined text-sm mr-2">person</span>
                Profil
              </a>
              <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                <span class="material-symbols-outlined text-sm mr-2">settings</span>
                Paramètres
              </a>
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
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<style scoped>
/* Animation pour le dropdown */
.relative {
  position: relative;
}

.absolute {
  position: absolute;
}

.z-50 {
  z-index: 50;
}
</style>
