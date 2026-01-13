<script setup>
import { ref } from 'vue'

const props = defineProps({
  unreadCount: {
    type: Number,
    default: 0
  },
  user: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['toggle-sidebar'])

const showUserMenu = ref(false)

const toggleUserMenu = () => {
  showUserMenu.value = !showUserMenu.value
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
          <button class="relative p-2 rounded-lg hover:bg-gray-100">
            <span class="material-symbols-outlined text-gray-600">notifications</span>
            <span 
              v-if="unreadCount > 0"
              class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"
            >
              {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
          </button>

          <!-- User menu -->
          <div class="relative">
            <button 
              @click="toggleUserMenu"
              class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100"
            >
              <img 
                :src="user.avatar" 
                :alt="user.name"
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
                <p class="text-sm font-medium text-gray-900">{{ user.name }}</p>
                <p class="text-xs text-gray-500">{{ user.email }}</p>
              </div>
              <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                <span class="material-symbols-outlined text-sm mr-2">person</span>
                Profil
              </a>
              <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                <span class="material-symbols-outlined text-sm mr-2">settings</span>
                Paramètres
              </a>
              <a href="#" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                <span class="material-symbols-outlined text-sm mr-2">logout</span>
                Déconnexion
              </a>
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
