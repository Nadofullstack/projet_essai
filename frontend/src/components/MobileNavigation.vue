<template>
  <!-- Menu mobile glissant -->
  <Transition
    enter-active-class="transition duration-300 ease-in-out"
    enter-from-class="transform -translate-x-full"
    enter-to-class="transform translate-x-0"
    leave-active-class="transition duration-300 ease-in-out"
    leave-from-class="transform translate-x-0"
    leave-to-class="transform -translate-x-full"
  >
    <div v-if="isOpen" class="fixed inset-0 z-50 flex">
      <!-- Overlay -->
      <div class="fixed inset-0 bg-black bg-opacity-50" @click="close" />
      
      <!-- Panneau latéral -->
      <div class="relative flex-1 flex flex-col max-w-xs w-full bg-white">
        <!-- Header du menu -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Menu</h2>
          <button
            @click="close"
            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <!-- Navigation -->
        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
          <router-link 
            v-for="item in menuItems" 
            :key="item.name"
            :to="item.path"
            class="group flex items-center px-2 py-2 text-base font-medium rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-50"
            @click="close"
          >
            <component :is="item.icon" class="mr-3 h-6 w-6 text-gray-400 group-hover:text-gray-500" />
            {{ item.name }}
          </router-link>
        </nav>
        
        <!-- Footer du menu -->
        <div class="border-t border-gray-200 p-4">
          <div class="flex items-center space-x-4">
            <button
              v-if="user"
              class="flex items-center text-sm text-gray-500 hover:text-gray-700"
            >
              <component :is="UserIcon" class="h-5 w-5 mr-2" />
              {{ user.name }}
            </button>
            <button
              @click="logout"
              class="flex items-center text-sm text-gray-500 hover:text-gray-700"
            >
              <component :is="LogoutIcon" class="h-5 w-5 mr-2" />
              Déconnexion
            </button>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { 
  HomeIcon, 
  CalendarIcon, 
  PhoneIcon, 
  MicrophoneIcon, 
  MessageIcon, 
  UserIcon,
  ArrowRightOnRectangleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  },
  user: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'logout'])

const route = useRoute()
const router = useRouter()

const menuItems = [
  { name: 'Tableau de bord', path: '/dashboard', icon: HomeIcon },
  { name: 'Rendez-vous', path: '/rendez-vous', icon: CalendarIcon },
  { name: 'Appels', path: '/appels', icon: PhoneIcon },
  { name: 'Audio', path: '/audio', icon: MicrophoneIcon },
  { name: 'Messages', path: '/messages', icon: MessageIcon },
  { name: 'Profil', path: '/profile', icon: UserIcon }
]

const close = () => {
  emit('close')
}

const logout = () => {
  emit('logout')
}
</script>

<style scoped>
/* Animation de transition */
.transition {
  transition-property: transform;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}

.transform {
  transform: translateX(0);
}

.transform.translate-x-0 {
  transform: translateX(0);
}

.transform.-translate-x-full {
  transform: translateX(-100%);
}

/* Scrollbar personnalisée */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #555;
}
</style>
