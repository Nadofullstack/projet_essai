<template>
  <MainLayout>
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
          <div class="flex items-center space-x-4">
            <button @click="$router.go(-1)" class="p-2 rounded-lg hover:bg-gray-100">
              <span class="material-symbols-outlined">arrow_back</span>
            </button>
            <h1 class="text-2xl font-bold text-gray-900">Messages</h1>
          </div>
          <button @click="showNewMessageModal = true" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center space-x-2">
            <span class="material-symbols-outlined">add</span>
            <span>Nouveau message</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Conversations List -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-4 border-b border-gray-200">
              <div class="relative">
                <input 
                  v-model="searchQuery"
                  type="text" 
                  placeholder="Rechercher une conversation..."
                  class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400">search</span>
              </div>
            </div>
            
            <div class="max-h-96 overflow-y-auto">
              <div 
                v-for="conversation in filteredConversations"
                :key="conversation.id"
                @click="selectConversation(conversation)"
                :class="[
                  'p-4 border-b border-gray-100 cursor-pointer hover:bg-gray-50 transition-colors',
                  selectedConversation?.id === conversation.id ? 'bg-blue-50 border-l-4 border-l-blue-500' : ''
                ]"
              >
                <div class="flex items-center space-x-3">
                  <div class="relative">
                    <img :src="conversation.avatar" :alt="conversation.name" class="w-12 h-12 rounded-full">
                    <span v-if="conversation.isOnline" class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></span>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-start">
                      <h3 class="font-semibold text-gray-900 truncate">{{ conversation.name }}</h3>
                      <span class="text-xs text-gray-500">{{ conversation.time }}</span>
                    </div>
                    <p class="text-sm text-gray-600 truncate">{{ conversation.lastMessage }}</p>
                  </div>
                  <div v-if="conversation.unreadCount > 0" class="bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                    {{ conversation.unreadCount }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Chat Area -->
        <div class="lg:col-span-2">
          <div v-if="selectedConversation" class="bg-white rounded-xl shadow-sm border border-gray-200 h-[600px] flex flex-col">
            <!-- Chat Header -->
            <div class="p-4 border-b border-gray-200 flex items-center justify-between">
              <div class="flex items-center space-x-3">
                <img :src="selectedConversation.avatar" :alt="selectedConversation.name" class="w-10 h-10 rounded-full">
                <div>
                  <h3 class="font-semibold text-gray-900">{{ selectedConversation.name }}</h3>
                  <p class="text-sm text-gray-500">{{ selectedConversation.isOnline ? 'En ligne' : 'Hors ligne' }}</p>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <button class="p-2 rounded-lg hover:bg-gray-100">
                  <span class="material-symbols-outlined">phone</span>
                </button>
                <button class="p-2 rounded-lg hover:bg-gray-100">
                  <span class="material-symbols-outlined">videocam</span>
                </button>
                <button class="p-2 rounded-lg hover:bg-gray-100">
                  <span class="material-symbols-outlined">more_vert</span>
                </button>
              </div>
            </div>

            <!-- Messages Area -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4">
              <div 
                v-for="message in selectedConversation.messages"
                :key="message.id"
                :class="[
                  'flex',
                  message.isSender ? 'justify-end' : 'justify-start'
                ]"
              >
                <div :class="[
                  'max-w-xs lg:max-w-md px-4 py-2 rounded-2xl',
                  message.isSender 
                    ? 'bg-blue-600 text-white rounded-br-none' 
                    : 'bg-gray-100 text-gray-900 rounded-bl-none'
                ]">
                  <p class="text-sm">{{ message.content }}</p>
                  <p :class="[
                    'text-xs mt-1',
                    message.isSender ? 'text-blue-100' : 'text-gray-500'
                  ]">
                    {{ message.time }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Message Input -->
            <div class="p-4 border-t border-gray-200">
              <div class="flex items-center space-x-2">
                <button class="p-2 rounded-lg hover:bg-gray-100">
                  <span class="material-symbols-outlined">attach_file</span>
                </button>
                <button class="p-2 rounded-lg hover:bg-gray-100">
                  <span class="material-symbols-outlined">mic</span>
                </button>
                <input 
                  v-model="newMessage"
                  @keyup.enter="sendMessage"
                  type="text" 
                  placeholder="Écrire un message..."
                  class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                <button 
                  @click="sendMessage"
                  class="bg-blue-600 text-white p-2 rounded-lg hover:bg-blue-700"
                >
                  <span class="material-symbols-outlined">send</span>
                </button>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 h-[600px] flex items-center justify-center">
            <div class="text-center">
              <span class="material-symbols-outlined text-6xl text-gray-300">chat</span>
              <h3 class="mt-4 text-lg font-semibold text-gray-900">Sélectionnez une conversation</h3>
              <p class="text-gray-500">Choisissez une conversation pour commencer à discuter</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- New Message Modal -->
    <div v-if="showNewMessageModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Nouveau message</h2>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Destinataire</label>
            <input 
              v-model="newMessageData.recipient"
              type="text" 
              placeholder="Nom du destinataire..."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
            <textarea 
              v-model="newMessageData.content"
              rows="4"
              placeholder="Votre message..."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            ></textarea>
          </div>
        </div>
        <div class="flex justify-end space-x-2 mt-6">
          <button 
            @click="showNewMessageModal = false"
            class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50"
          >
            Annuler
          </button>
          <button 
            @click="createNewMessage"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
          >
            Envoyer
          </button>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useMessagesStore } from '@/stores/messages'
import MainLayout from '@/components/Dashboard/Layout/MainLayout.vue'

const messagesStore = useMessagesStore()

// État local
const searchQuery = ref('')
const selectedConversation = ref(null)
const newMessage = ref('')
const showNewMessageModal = ref(false)
const newMessageData = ref({
  recipient: '',
  content: ''
})

// Données du store
const conversations = computed(() => messagesStore.conversations)

// Computed
const filteredConversations = computed(() => {
  if (!searchQuery.value) return conversations.value
  return conversations.value.filter(conv => 
    conv.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    conv.lastMessage.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

// Méthodes
const selectConversation = (conversation) => {
  selectedConversation.value = conversation
  messagesStore.markConversationAsRead(conversation.id)
}

const sendMessage = () => {
  if (newMessage.value.trim() && selectedConversation.value) {
    messagesStore.sendMessage({
      conversationId: selectedConversation.value.id,
      content: newMessage.value.trim()
    })
    newMessage.value = ''
  }
}

const createNewMessage = () => {
  if (newMessageData.value.recipient && newMessageData.value.content) {
    messagesStore.createNewConversation(newMessageData.value)
    showNewMessageModal.value = false
    newMessageData.value = { recipient: '', content: '' }
  }
}

// Chargement initial
onMounted(() => {
  messagesStore.fetchConversations()
})
</script>

<style scoped>
/* Scrollbar personnalisée */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #a1a1a1;
}
</style>
