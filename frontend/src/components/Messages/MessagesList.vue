<template>
  <div class="h-full flex flex-col bg-white">
    <!-- Header avec recherche -->
    <div class="p-4 border-b border-gray-200">
      <h2 class="text-xl font-bold text-gray-900 mb-4">Messages</h2>
      
      <!-- Barre de recherche -->
      <div class="relative">
        <span class="material-symbols-outlined absolute left-3 top-3 text-gray-400">search</span>
        <input 
          v-model="searchInput"
          type="text"
          placeholder="Rechercher un utilisateur..."
          @input="handleSearch"
          class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <!-- Tabs -->
      <div class="flex gap-4 mt-4 border-b border-gray-200">
        <button 
          @click="activeTab = 'conversations'"
          :class="[
            'pb-2 px-2 font-medium text-sm transition-colors',
            activeTab === 'conversations' 
              ? 'border-b-2 border-blue-500 text-blue-600' 
              : 'text-gray-600 hover:text-gray-900'
          ]"
        >
          Conversations
        </button>
        <button 
          @click="activeTab = 'contacts'"
          :class="[
            'pb-2 px-2 font-medium text-sm transition-colors',
            activeTab === 'contacts' 
              ? 'border-b-2 border-blue-500 text-blue-600' 
              : 'text-gray-600 hover:text-gray-900'
          ]"
        >
          Contacts
        </button>
      </div>
    </div>

    <!-- Contenu principal -->
    <div class="flex-1 overflow-y-auto">
      <!-- Tab: Conversations -->
      <div v-if="activeTab === 'conversations'" class="divide-y divide-gray-200">
        <div 
          v-if="sortedConversations.length === 0"
          class="p-4 text-center text-gray-500"
        >
          <span class="material-symbols-outlined text-4xl opacity-50 block mb-2">mail_outline</span>
          Aucune conversation
        </div>

        <div 
          v-for="conversation in sortedConversations"
          :key="conversation.id"
          @click="selectConversation(conversation)"
          :class="[
            'p-4 cursor-pointer transition-colors hover:bg-gray-50 border-l-4',
            selectedConversationId === conversation.id ? 'border-l-blue-500 bg-blue-50' : 'border-l-transparent'
          ]"
        >
          <div class="flex items-center gap-3">
            <!-- Avatar -->
            <img 
              :src="conversation.avatar"
              :alt="conversation.name"
              class="w-12 h-12 rounded-full object-cover"
            />

            <!-- Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between">
                <h3 class="font-medium text-gray-900">{{ conversation.name }}</h3>
                <span class="text-xs text-gray-500">{{ formatTime(conversation.lastMessageTime || conversation.time) }}</span>
              </div>
              <p class="text-sm text-gray-500 truncate">{{ conversation.lastMessage || 'Aucun message' }}</p>
            </div>

            <!-- Badge unread -->
            <div v-if="conversation.unreadCount > 0" class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-medium">
              {{ conversation.unreadCount }}
            </div>
          </div>
        </div>
      </div>

      <!-- Tab: Contacts -->
      <div v-if="activeTab === 'contacts'" class="divide-y divide-gray-200">
        <div 
          v-if="filteredUsers.length === 0"
          class="p-4 text-center text-gray-500"
        >
          <span class="material-symbols-outlined text-4xl opacity-50 block mb-2">person_outline</span>
          {{ usersLoading ? 'Chargement...' : 'Aucun contact trouvé' }}
        </div>

        <div 
          v-for="user in filteredUsers"
          :key="user.id"
          @click="startConversation(user)"
          class="p-4 cursor-pointer transition-colors hover:bg-gray-50 flex items-center gap-3"
        >
          <!-- Avatar -->
          <img 
            :src="user.avatar"
            :alt="user.name"
            class="w-12 h-12 rounded-full object-cover"
          />

          <!-- Info -->
          <div class="flex-1 min-w-0">
            <h3 class="font-medium text-gray-900">{{ user.name }}</h3>
            <p class="text-sm text-gray-500 truncate">{{ user.email }}</p>
          </div>

          <!-- Status online -->
          <div 
            :class="[
              'w-3 h-3 rounded-full',
              user.isOnline ? 'bg-green-500' : 'bg-gray-300'
            ]"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useMessagesStore } from '@/stores/messages'

const messagesStore = useMessagesStore()

// State
const activeTab = ref('conversations')
const searchInput = ref('')
const selectedConversationId = ref(null)

// Computed
const sortedConversations = computed(() => messagesStore.sortedConversations)
const filteredUsers = computed(() => messagesStore.filteredUsers)
const usersLoading = computed(() => messagesStore.usersLoading)

// Methods
const handleSearch = async () => {
  await messagesStore.searchUsers(searchInput.value)
}

const selectConversation = (conversation) => {
  selectedConversationId.value = conversation.id
  // Émettre un événement pour ouvrir la conversation
  emit('select-conversation', conversation)
}

const startConversation = (user) => {
  // Créer ou ouvrir une conversation avec cet utilisateur
  const conversation = messagesStore.startConversationWithUser(user)
  selectConversation(conversation)
}

const formatTime = (time) => {
  if (!time) return ''
  
  const date = new Date(time)
  const now = new Date()
  const diffTime = Math.abs(now - date)
  const diffMinutes = Math.ceil(diffTime / (1000 * 60))
  const diffHours = Math.ceil(diffTime / (1000 * 60 * 60))
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))

  if (diffMinutes < 1) return 'À l\'instant'
  if (diffMinutes < 60) return `${diffMinutes}m`
  if (diffHours < 24) return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })
  if (diffDays === 1) return 'Hier'
  if (diffDays < 7) return `${diffDays}j`
  
  return date.toLocaleDateString('fr-FR', { month: 'short', day: 'numeric' })
}

// Lifecycle
onMounted(async () => {
  await messagesStore.fetchConversations()
  await messagesStore.fetchAvailableUsers()
})

// Emit
const emit = defineEmits(['select-conversation'])
</script>

<style scoped>
/* Les styles Tailwind sont appliqués via les classes */
</style>
