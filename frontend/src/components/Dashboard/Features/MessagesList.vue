<script setup>
import { ref, onMounted } from 'vue'
import { useMessagesStore } from '@/stores/messages'

const emit = defineEmits(['select-message', 'new-message'])

const props = defineProps({
  messages: {
    type: Array,
    default: () => [
      {
        id: 1,
        sender: 'Marie Laurent',
        avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Marie',
        message: 'Bonjour ! Comment allez-vous aujourd\'hui ?',
        time: '10:30',
        unread: true
      },
      {
        id: 2,
        sender: 'Thomas Bernard',
        avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Thomas',
        message: 'Le programme est prêt pour la révision',
        time: '09:15',
        unread: false
      },
      {
        id: 3,
        sender: 'Sophie Martin',
        avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Sophie',
        message: 'Merci pour votre aide hier',
        time: 'Hier',
        unread: false
      }
    ]
  }
})

const messagesStore = useMessagesStore()

const search = ref('')
const userResults = ref([])
const showResults = ref(false)
let searchTimer = null

const selectMessage = (message) => {
  emit('select-message', message)
}

const newMessage = () => {
  emit('new-message')
}

const handleSearchInput = () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(async () => {
    const q = search.value.trim()
    if (!q) {
      userResults.value = []
      showResults.value = false
      return
    }

    try {
      const results = await messagesStore.searchUsers(q)
      // messagesStore.searchUsers returns an array
      userResults.value = Array.isArray(results) ? results : (results.data || results.users || [])
      showResults.value = userResults.value.length > 0
    } catch (err) {
      console.error('Erreur search users:', err)
      userResults.value = []
      showResults.value = false
    }
  }, 300)
}

const selectUser = (user) => {
  // démarrer une conversation et émettre l'événement
  messagesStore.startConversationWithUser(user)
  emit('new-message', user)
  search.value = ''
  userResults.value = []
  showResults.value = false
}

onMounted(() => {
  // précharger la liste d'utilisateurs pour rendre la recherche plus rapide
  messagesStore.fetchAvailableUsers().catch(() => {})
})
</script>

<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <!-- Header + Recherche -->
    <div class="p-4 border-b border-gray-200">
      <div class="flex justify-between items-center">
        <h3 class="font-semibold text-gray-900">Messages récents</h3>
        <button 
          @click="newMessage"
          class="text-blue-600 hover:text-blue-700 p-1 rounded hover:bg-blue-50"
        >
          <span class="material-symbols-outlined">add</span>
        </button>
      </div>

      <!-- Recherche d'utilisateurs -->
      <div class="mt-3 relative">
        <span class="material-symbols-outlined absolute left-3 top-3 text-gray-400">search</span>
        <input
          v-model="search"
          @input="handleSearchInput"
          type="text"
          placeholder="Rechercher un utilisateur..."
          class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />

        <div v-if="showResults" class="absolute left-0 right-0 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg z-50 max-h-48 overflow-y-auto">
          <div
            v-for="user in userResults"
            :key="user.id"
            @click="selectUser(user)"
            class="p-3 cursor-pointer hover:bg-gray-50 flex items-center space-x-3"
          >
            <img :src="user.profile_picture || user.avatar || `https://api.dicebear.com/7.x/avataaars/svg?seed=${user.email || user.name}`" class="w-8 h-8 rounded-full" />
            <div class="flex-1 min-w-0">
              <p class="font-medium text-gray-900 truncate">{{ user.name }}</p>
              <p class="text-xs text-gray-500 truncate">{{ user.email }}</p>
            </div>
            <span class="text-green-600 text-sm">Envoyer</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Messages List -->
    <div class="divide-y divide-gray-100">
      <div 
        v-for="message in props.messages" 
        :key="message.id"
        @click="selectMessage(message)"
        class="p-4 hover:bg-gray-50 cursor-pointer transition-colors"
      >
        <div class="flex items-start space-x-3">
          <img 
            :src="message.avatar" 
            :alt="message.sender"
            class="w-10 h-10 rounded-full flex-shrink-0"
          />
          <div class="flex-1 min-w-0">
            <div class="flex justify-between items-start">
              <h4 class="font-medium text-gray-900 truncate">{{ message.sender }}</h4>
              <span class="text-xs text-gray-500">{{ message.time }}</span>
            </div>
            <p class="text-sm text-gray-600 mt-1 truncate">{{ message.message }}</p>
          </div>
          <div v-if="message.unread" class="flex-shrink-0">
            <span class="inline-block w-2 h-2 bg-blue-600 rounded-full"></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="p-4 border-t border-gray-200">
      <button class="w-full text-center text-sm text-blue-600 hover:text-blue-700 font-medium">
        Voir tous les messages
      </button>
    </div>
  </div>
</template>

<style scoped>
.transition-colors {
  transition: background-color 0.2s ease;
}

.divide-y > * + * {
  border-top: 1px solid #f3f4f6;
}

.divide-gray-100 > * + * {
  border-top-color: #f3f4f6;
}
</style>
