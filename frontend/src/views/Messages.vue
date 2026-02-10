<template>
  <MainLayout>
    <!-- Server Status Alert -->
    <div v-if="!serverAvailable" class="bg-yellow-50 border-b-2 border-yellow-300 px-4 py-3">
      <div class="max-w-7xl mx-auto flex items-center space-x-3">
        <span class="material-symbols-outlined text-yellow-600">warning</span>
        <p class="text-sm text-yellow-800">
          <strong>Mode démo:</strong> Le serveur n'est pas accessible. Les données affichées sont simulées. Assurez-vous que le serveur Laravel est en cours d'exécution sur <code class="bg-yellow-100 px-2 py-1 rounded">localhost:8000</code>
        </p>
      </div>
    </div>

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
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-[calc(100vh-200px)] lg:h-[calc(100vh-200px)]">
        <!-- Conversations List -->
        <div :class="[
          'bg-white rounded-xl shadow-sm border border-gray-200 mobile-transition',
          selectedConversation ? 'hidden lg:block lg:col-span-1' : 'block lg:col-span-1'
        ]">
          <div class="p-4 border-b border-gray-200">
            <div class="relative mb-4">
              <input 
                v-model="searchQuery"
                type="text" 
                placeholder="Rechercher une conversation..."
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
              <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400">search</span>
            </div>
            
            <!-- Recherche d'utilisateurs -->
            <div class="relative">
              <input 
                v-model="userSearchQuery"
                type="text" 
                placeholder="Trouver un utilisateur..."
                @input="searchUsersDebounced"
                class="w-full pl-10 pr-4 py-2 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
              >
              <span class="material-symbols-outlined absolute left-3 top-2.5 text-green-400">person_add</span>
              
              <!-- Résultats de recherche -->
              <div v-if="showUserSearchResults && userSearchResults.length > 0" class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg z-50 max-h-48 overflow-y-auto">
                <div 
                  v-for="user in userSearchResults" 
                  :key="user.id"
                  @click="selectUserAndStartConversation(user)"
                  class="p-3 border-b border-gray-100 cursor-pointer hover:bg-green-50 transition-colors flex items-center space-x-3"
                >
                  <img :src="user.profile_picture || `https://picsum.photos/seed/${user.name}/50/50.jpg`" :alt="user.name" class="w-8 h-8 rounded-full">
                  <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-900 truncate">{{ user.name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ user.email }}</p>
                  </div>
                  <span class="material-symbols-outlined text-green-600">arrow_forward</span>
                </div>
              </div>
            </div>
          </div>
          
          <div class="max-h-96 lg:max-h-full overflow-y-auto">
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

        <!-- Chat Area -->
        <div :class="[
          'bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col mobile-transition',
          currentConversation ? 'block lg:col-span-2' : 'hidden lg:block lg:col-span-2'
        ]">
          <div v-if="currentConversation" class="flex flex-col h-full">
            <!-- Chat Header -->
            <div class="p-4 border-b border-gray-200 flex items-center justify-between">
              <div class="flex items-center space-x-3">
                <!-- Bouton retour mobile -->
                <button 
                  @click="goBackToConversations"
                  class="lg:hidden p-2 rounded-lg hover:bg-gray-100"
                >
                  <span class="material-symbols-outlined">arrow_back</span>
                </button>
                <img :src="selectedConversation.avatar" :alt="selectedConversation.name" class="w-10 h-10 rounded-full">
                <div>
                  <h3 class="font-semibold text-gray-900">{{ selectedConversation.name }}</h3>
                  <p class="text-sm text-gray-500">{{ selectedConversation.isOnline ? 'En ligne' : 'Hors ligne' }}</p>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <button 
                  @click="startAudioCall"
                  class="p-2 rounded-lg hover:bg-gray-100 text-gray-600 hover:text-green-600 transition-colors"
                  title="Appel audio"
                >
                  <span class="material-symbols-outlined">phone</span>
                </button>
                <button 
                  @click="startVideoCall"
                  :disabled="!isVideoAvailable"
                  :class="[
                    'p-2 rounded-lg transition-colors',
                    isVideoAvailable 
                      ? 'hover:bg-gray-100 text-gray-600 hover:text-blue-600' 
                      : 'text-gray-400 cursor-not-allowed'
                  ]"
                  :title="isVideoAvailable ? 'Appel vidéo' : 'Caméra non disponible'"
                >
                  <span class="material-symbols-outlined">videocam</span>
                </button>
                <button 
                  @click="simulateIncomingCall"
                  class="p-2 rounded-lg hover:bg-gray-100 text-gray-600"
                  title="Simuler appel entrant (test)"
                >
                  <span class="material-symbols-outlined">phone_callback</span>
                </button>
                <button class="p-2 rounded-lg hover:bg-gray-100 text-gray-600">
                  <span class="material-symbols-outlined">more_vert</span>
                </button>
              </div>
            </div>

            <!-- Messages Area -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50">
              <div 
                v-for="message in selectedConversation.messages"
                :key="message.id"
                :class="[
                  'flex mb-4',
                  message.isSender ? 'justify-end' : 'justify-start'
                ]"
              >
                <!-- Avatar pour messages reçus -->
                <div v-if="!message.isSender" class="flex items-end mr-2">
                  <img :src="selectedConversation.avatar" :alt="selectedConversation.name" class="w-8 h-8 rounded-full">
                </div>

                <!-- Bulle de message -->
                <div class="flex flex-col max-w-xs lg:max-w-md">
                  <div :class="[
                    'px-4 py-2 rounded-2xl shadow-sm relative',
                    message.isSender 
                      ? 'bg-green-500 text-white rounded-br-md' 
                      : 'bg-white text-gray-900 rounded-bl-md border border-gray-200'
                  ]">
                    <p class="text-sm">{{ message.content }}</p>
                    
                    <!-- Indicateur de statut pour messages envoyés -->
                    <div v-if="message.isSender" class="flex items-center justify-end mt-1 space-x-1">
                      <span class="text-xs text-green-100">{{ message.time }}</span>
                      <div class="flex">
                        <span v-if="message.status === 'sent'" class="material-symbols-outlined text-xs text-green-100">check</span>
                        <span v-if="message.status === 'delivered'" class="material-symbols-outlined text-xs text-green-100">check</span>
                        <span v-if="message.status === 'delivered'" class="material-symbols-outlined text-xs text-green-100 -ml-1">check</span>
                        <span v-if="message.status === 'read'" class="material-symbols-outlined text-xs text-blue-200">check</span>
                        <span v-if="message.status === 'read'" class="material-symbols-outlined text-xs text-blue-200 -ml-1">check</span>
                      </div>
                    </div>
                    
                    <!-- Heure pour messages reçus -->
                    <div v-else class="flex justify-start mt-1">
                      <span class="text-xs text-gray-500">{{ message.time }}</span>
                    </div>
                  </div>

                  <!-- Indicateur de non lu pour messages reçus -->
                  <div v-if="!message.isSender && !message.isRead" class="flex justify-start mt-1">
                    <span class="inline-block w-2 h-2 bg-red-500 rounded-full"></span>
                  </div>
                </div>

                <!-- Avatar pour messages envoyés -->
                <div v-if="message.isSender" class="flex items-end ml-2">
                  <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                    <span class="material-symbols-outlined text-white text-sm">person</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Message Input -->
            <div class="p-4 border-t border-gray-200 bg-white">
              <div class="flex items-end space-x-3">
                <button class="p-2 rounded-full hover:bg-gray-100 text-gray-500">
                  <span class="material-symbols-outlined">attach_file</span>
                </button>
                <div class="flex-1 relative">
                  <textarea 
                    v-model="newMessage"
                    @keydown.enter.exact.prevent="sendMessage"
                    @keydown.enter.shift.exact="newMessage += '\n'"
                    rows="1"
                    placeholder="Tapez un message..."
                    class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-3xl focus:ring-2 focus:ring-green-500 focus:border-transparent resize-none max-h-32 overflow-y-auto"
                    style="min-height: 44px;"
                  ></textarea>
                  <button class="absolute right-2 bottom-2 p-1 rounded-full hover:bg-gray-100 text-gray-500">
                    <span class="material-symbols-outlined">sentiment_satisfied</span>
                  </button>
                </div>
                <button 
                  v-if="!newMessage.trim()"
                  class="p-3 rounded-full bg-gray-200 text-gray-500"
                  disabled
                >
                  <span class="material-symbols-outlined">mic</span>
                </button>
                <button 
                  v-else
                  @click="sendMessage"
                  class="p-3 rounded-full bg-green-500 text-white hover:bg-green-600 transition-colors"
                >
                  <span class="material-symbols-outlined">send</span>
                </button>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="flex items-center justify-center h-full">
            <div class="text-center">
              <span class="material-symbols-outlined text-6xl text-gray-300">chat</span>
              <h3 class="mt-4 text-lg font-semibold text-gray-900">Sélectionnez une conversation</h3>
              <p class="text-gray-500">Choisissez une conversation pour commencer à discuter</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>

  <!-- Call Modal -->
  <transition name="call-modal" appear>
    <div v-if="showCallModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
      <!-- Call Header -->
      <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6">
        <div class="text-center">
          <div class="relative inline-block mb-4">
            <img 
              :src="selectedConversation?.avatar" 
              :alt="selectedConversation?.name" 
              :class="[
                'w-24 h-24 rounded-full border-4',
                callType === 'video' && !isVideoOn ? 'border-red-500' : 'border-white'
              ]"
            >
            <!-- Icône caméra désactivée -->
            <div v-if="callType === 'video' && !isVideoOn" class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 rounded-full">
              <span class="material-symbols-outlined text-white text-3xl">videocam_off</span>
            </div>
            <!-- Indicateur de statut d'appel -->
            <div v-if="callStatus === 'calling'" class="absolute -bottom-2 left-1/2 transform -translate-x-1/2">
              <div class="bg-yellow-500 text-white text-xs px-2 py-1 rounded-full call-indicator">
                Appel en cours...
              </div>
            </div>
            <div v-if="callStatus === 'connected'" class="absolute -bottom-2 left-1/2 transform -translate-x-1/2">
              <div class="bg-green-500 text-white text-xs px-2 py-1 rounded-full flex items-center space-x-1">
                <span class="w-2 h-2 bg-white rounded-full animate-pulse"></span>
                <span>{{ formatCallDuration(callDuration) }}</span>
              </div>
            </div>
          </div>
          
          <h3 class="text-xl font-semibold mb-1">{{ selectedConversation?.name }}</h3>
          <p class="text-blue-100">
            {{ callStatusText }}
          </p>
          
          <!-- Afficher les erreurs vidéo -->
          <p v-if="videoError" class="text-red-300 text-sm mt-2">
            ⚠️ {{ videoError }}
          </p>
        </div>
      </div>

      <!-- Video Area for Video Calls -->
      <div v-if="callType === 'video' && callStatus === 'connected'" class="relative bg-black">
        <!-- Remote Video (Main) -->
        <div class="aspect-video relative">
          <video 
            ref="remoteVideoRef"
            class="w-full h-full object-cover"
            autoplay
            playsinline
            muted
          ></video>
          <!-- Placeholder if no remote video -->
          <div v-if="!remoteVideoStream" class="absolute inset-0 flex items-center justify-center bg-gray-800">
            <div class="w-32 h-32 rounded-full bg-gray-600 flex items-center justify-center">
              <span class="text-4xl text-white">{{ selectedConversation?.name?.charAt(0) || 'U' }}</span>
            </div>
          </div>
        </div>

        <!-- Local Video (Small window) -->
        <div class="absolute top-4 right-4 w-24 h-32 rounded-lg overflow-hidden border-2 border-white shadow-lg">
          <video 
            ref="localVideoRef"
            class="w-full h-full object-cover"
            autoplay
            playsinline
            muted
          ></video>
          <!-- Placeholder if video disabled -->
          <div v-if="!isVideoOn" class="absolute inset-0 bg-gray-800 flex items-center justify-center">
            <div class="w-8 h-8 rounded-full bg-gray-600 flex items-center justify-center">
              <span class="text-white text-sm">{{ selectedConversation?.name?.charAt(0) || 'U' }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Call Controls -->
      <div class="p-8">
        <!-- Contrôles pour appel entrant -->
        <div v-if="callStatus === 'ringing'" class="flex justify-center space-x-6 mb-8">
          <button 
            @click="rejectCall"
            class="w-16 h-16 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-all"
          >
            <span class="material-symbols-outlined text-2xl">call_end</span>
          </button>
          <button 
            @click="acceptCall"
            class="w-16 h-16 bg-green-500 text-white rounded-full flex items-center justify-center hover:bg-green-600 transition-all"
          >
            <span class="material-symbols-outlined text-2xl">call</span>
          </button>
        </div>

        <!-- Contrôles pour appel en cours -->
        <div v-if="callStatus === 'calling' || callStatus === 'connected'" class="flex justify-center space-x-6 mb-8">
          <!-- Bouton Muet -->
          <button 
            @click="toggleMute"
            :class="[
              'w-16 h-16 rounded-full flex items-center justify-center transition-all',
              isMuted 
                ? 'bg-red-500 text-white' 
                : 'bg-gray-200 text-gray-600 hover:bg-gray-300'
            ]"
          >
            <span class="material-symbols-outlined text-2xl">
              {{ isMuted ? 'mic_off' : 'mic' }}
            </span>
          </button>

          <!-- Bouton Haut-parleur -->
          <button 
            @click="toggleSpeaker"
            :class="[
              'w-16 h-16 rounded-full flex items-center justify-center transition-all',
              !isSpeakerOn 
                ? 'bg-red-500 text-white' 
                : 'bg-gray-200 text-gray-600 hover:bg-gray-300'
            ]"
          >
            <span class="material-symbols-outlined text-2xl">
              {{ isSpeakerOn ? 'volume_up' : 'volume_off' }}
            </span>
          </button>

          <!-- Bouton Raccrocher -->
          <button 
            @click="endCall"
            class="w-16 h-16 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-all"
          >
            <span class="material-symbols-outlined text-2xl">call_end</span>
          </button>
        </div>

        <!-- Boutons supplémentaires pour appel vidéo -->
        <div v-if="callType === 'video' && callStatus === 'connected'" class="flex justify-center space-x-4">
          <button 
            @click="toggleVideo"
            :class="[
              'w-12 h-12 rounded-full flex items-center justify-center transition-all',
              !isVideoOn 
                ? 'bg-red-500 text-white' 
                : 'bg-gray-200 text-gray-600 hover:bg-gray-300'
            ]"
          >
            <span class="material-symbols-outlined">
              {{ isVideoOn ? 'videocam' : 'videocam_off' }}
            </span>
          </button>
          <button 
            @click="switchCamera"
            class="w-12 h-12 bg-gray-200 text-gray-600 rounded-full flex items-center justify-center hover:bg-gray-300 transition-all"
          >
            <span class="material-symbols-outlined">switch_camera</span>
          </button>
        </div>
      </div>
    </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch, onUnmounted } from 'vue'
import { useMessagesStore } from '@/stores/messages'
import { useAppelsStore } from '@/stores/appels'
import MainLayout from '@/components/Dashboard/Layout/MainLayout.vue'

const messagesStore = useMessagesStore()
const appelsStore = useAppelsStore()

// État local
const searchQuery = ref('')
const selectedConversation = ref(null)
const newMessage = ref('')
const serverAvailable = ref(true)

// État de la recherche utilisateur
const userSearchQuery = ref('')
const userSearchResults = ref([])
const showUserSearchResults = ref(false)

// État des appels
const showCallModal = ref(false)
const callType = ref('audio') // 'audio' ou 'video'
const callStatus = ref('idle') // 'idle', 'calling', 'ringing', 'connected', 'ended'
const callDuration = ref(0)
const callTimer = ref(null)
const isMuted = ref(false)
const isSpeakerOn = ref(true)
const isVideoOn = ref(true)
const cameraFacing = ref('front') // 'front' ou 'back'

// État de la vidéo
const localVideoStream = ref(null)
const remoteVideoStream = ref(null)
const localVideoRef = ref(null)
const remoteVideoRef = ref(null)
const videoError = ref('')
const isVideoAvailable = ref(true)

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

const callStatusText = computed(() => {
  switch (callStatus.value) {
    case 'calling': return 'Appel en cours...'
    case 'ringing': return 'Appel entrant...'
    case 'connected': return 'Appel en cours'
    case 'ended': return 'Appel terminé'
    default: return 'Prêt'
  }
})

// ✅ Computed pour synchroniser la conversation sélectionnée avec le store
const currentConversation = computed({
  get() {
    if (selectedConversation.value) {
      // Récupérer la version à jour depuis le store
      return messagesStore.conversations.find(c => c.id === selectedConversation.value.id) || selectedConversation.value
    }
    return null
  },
  set(newValue) {
    selectedConversation.value = newValue
  }
})

// Méthodes
const selectConversation = (conversation) => {
  // ✅ Créer une référence réactive à la conversation sélectionnée
  selectedConversation.value = conversation
  messagesStore.markConversationAsRead(conversation.id)
  
  // ✅ Watcher pour synchroniser les changements
  watch(
    () => messagesStore.conversations,
    (newConversations) => {
      if (selectedConversation.value) {
        // Mettre à jour la conversation sélectionnée avec les derniers changements
        const updated = newConversations.find(c => c.id === selectedConversation.value.id)
        if (updated) {
          selectedConversation.value = updated
        }
      }
    },
    { deep: true }
  )
  
  // Scroll to bottom of messages on mobile
  nextTick(() => {
    const messagesContainer = document.querySelector('.overflow-y-auto')
    if (messagesContainer) {
      messagesContainer.scrollTop = messagesContainer.scrollHeight
    }
  })
}

const sendMessage = async () => {
  if (!newMessage.value.trim() || !selectedConversation.value) {
    return
  }

  const messageContent = newMessage.value.trim()
  newMessage.value = '' // Clear input immédiatement
  
  try {
    await messagesStore.sendMessage({
      conversationId: selectedConversation.value.id,
      receiverId: selectedConversation.value.id, // ID du destinataire
      content: messageContent,
      type: 'text' // Type par défaut
    })
    
    // Scroll to bottom après envoi réussi
    nextTick(() => {
      const messagesContainer = document.querySelector('.overflow-y-auto')
      if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight
      }
    })
  } catch (error) {
    console.error('Erreur lors de l\'envoi du message:', error)
    
    // Afficher l'erreur du store
    if (messagesStore.error) {
      // Créer une alerte visible à l'utilisateur
      alert(`❌ ${messagesStore.error}`)
    } else {
      alert('❌ Erreur lors de l\'envoi du message. Veuillez vérifier votre connexion.')
    }
  }
}

const goBackToConversations = () => {
  selectedConversation.value = null
}

// Recherche utilisateurs avec délai
const searchUsersDebounced = (() => {
  let timeoutId = null
  return async (query) => {
    clearTimeout(timeoutId)
    if (!query) {
      showUserSearchResults.value = false
      userSearchResults.value = []
      return
    }
    timeoutId = setTimeout(async () => {
      try {
        const results = await messagesStore.searchUsers(query)
        userSearchResults.value = results
        showUserSearchResults.value = results.length > 0
      } catch (error) {
        console.error('Erreur lors de la recherche d\'utilisateurs:', error)
        userSearchResults.value = []
        showUserSearchResults.value = false
      }
    }, 300)
  }
})()

const selectUserAndStartConversation = (user) => {
  const conversation = messagesStore.startConversationWithUser(user)
  selectConversation(conversation)
  userSearchQuery.value = ''
  showUserSearchResults.value = false
}

const startAudioCall = () => {
  if (selectedConversation.value) {
    callType.value = 'audio'
    callStatus.value = 'calling'
    showCallModal.value = true
    startCallTimer()
    
    // Simuler la connexion après 2 secondes
    setTimeout(() => {
      callStatus.value = 'connected'
    }, 2000)
  }
}

const startVideoCall = () => {
  if (selectedConversation.value) {
    callType.value = 'video'
    callStatus.value = 'calling'
    showCallModal.value = true
    startCallTimer()
    
    // Accéder à la caméra et au micro
    initializeVideoCall()
    
    // Simuler la connexion après 2 secondes
    setTimeout(() => {
      callStatus.value = 'connected'
      // Simuler le flux vidéo distant
      simulateRemoteVideo()
    }, 2000)
  }
}

const initializeVideoCall = async () => {
  try {
    videoError.value = ''
    const stream = await navigator.mediaDevices.getUserMedia({
      video: {
        facingMode: cameraFacing.value === 'front' ? 'user' : 'environment',
        width: { ideal: 1280 },
        height: { ideal: 720 }
      },
      audio: true
    })
    
    localVideoStream.value = stream
    isVideoAvailable.value = true
    
    if (localVideoRef.value) {
      localVideoRef.value.srcObject = stream
    }
    
    // Gérer le changement de caméra
    stream.getVideoTracks()[0].onended = () => {
      console.log('Vidéo locale arrêtée')
    }
    
  } catch (error) {
    console.error('Erreur d\'accès à la caméra:', error)
    isVideoAvailable.value = false
    
    // Messages d'erreur spécifiques selon le type d'erreur
    if (error.name === 'NotFoundError') {
      videoError.value = 'Aucune caméra trouvée sur cet appareil.'
    } else if (error.name === 'NotAllowedError') {
      videoError.value = 'Permission d\'accès à la caméra refusée.'
    } else if (error.name === 'NotReadableError') {
      videoError.value = 'La caméra est déjà utilisée par une autre application.'
    } else {
      videoError.value = 'Erreur d\'accès à la caméra. Passage en appel audio.'
    }
    
    // Fallback vers appel audio si vidéo échoue
    callType.value = 'audio'
  }
}

const simulateRemoteVideo = () => {
  // Simulation d'un flux vidéo distant (dans un vrai app, ce serait WebRTC)
  // Pour l'instant, on utilise une vidéo placeholder ou on cache l'avatar
  remoteVideoStream.value = 'simulated'
}

const toggleVideo = () => {
  isVideoOn.value = !isVideoOn.value
  
  if (localVideoStream.value) {
    const videoTrack = localVideoStream.value.getVideoTracks()[0]
    if (videoTrack) {
      videoTrack.enabled = isVideoOn.value
    }
  }
}

const switchCamera = async () => {
  if (localVideoStream.value) {
    // Arrêter le flux actuel
    localVideoStream.value.getTracks().forEach(track => track.stop())
    
    cameraFacing.value = cameraFacing.value === 'front' ? 'back' : 'front'
    
    try {
      const newStream = await navigator.mediaDevices.getUserMedia({
        video: {
          facingMode: cameraFacing.value === 'front' ? 'user' : 'environment',
          width: { ideal: 1280 },
          height: { ideal: 720 }
        },
        audio: true
      })
      
      localVideoStream.value = newStream
      
      if (localVideoRef.value) {
        localVideoRef.value.srcObject = newStream
      }
    } catch (error) {
      console.error('Erreur de changement de caméra:', error)
    }
  }
}

const toggleMute = () => {
  isMuted.value = !isMuted.value
  
  if (localVideoStream.value) {
    const audioTrack = localVideoStream.value.getAudioTracks()[0]
    if (audioTrack) {
      audioTrack.enabled = !isMuted.value
    }
  }
}

// Simulation d'appel entrant (pour test)
const simulateIncomingCall = () => {
  if (selectedConversation.value) {
    callType.value = 'audio'
    callStatus.value = 'ringing'
    showCallModal.value = true
  }
}

// Fonctions pour accepter/refuser un appel entrant
const acceptCall = () => {
  callStatus.value = 'connected'
  startCallTimer()
}

const rejectCall = () => {
  callStatus.value = 'ended'
  setTimeout(() => {
    showCallModal.value = false
    callStatus.value = 'idle'
  }, 500)
}

const endCall = () => {
  callStatus.value = 'ended'
  if (callTimer.value) {
    clearInterval(callTimer.value)
  }
  
  // Nettoyer les streams vidéo
  if (localVideoStream.value) {
    localVideoStream.value.getTracks().forEach(track => track.stop())
    localVideoStream.value = null
  }
  if (remoteVideoStream.value) {
    remoteVideoStream.value = null
  }
  
  // Fermer le modal après 1 seconde
  setTimeout(() => {
    showCallModal.value = false
    callStatus.value = 'idle'
    callDuration.value = 0
    isMuted.value = false
    isSpeakerOn.value = true
    isVideoOn.value = true
    cameraFacing.value = 'front'
  }, 1000)
}

const formatCallDuration = (seconds) => {
  const mins = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
}

const startCallTimer = () => {
  callDuration.value = 0
  callTimer.value = setInterval(() => {
    callDuration.value++
  }, 1000)
}

// Chargement initial
onMounted(async () => {
  // Vérifier la santé du serveur
  const { checkServerHealth } = await import('@/services/healthCheck')
  const health = await checkServerHealth()
  serverAvailable.value = health.available
  
  if (!health.available) {
    console.warn('⚠️ Server not available, using demo data')
  }
  
  messagesStore.fetchConversations()
  
  // Vérifier la disponibilité des périphériques vidéo
  try {
    const devices = await navigator.mediaDevices.enumerateDevices()
    const videoDevices = devices.filter(device => device.kind === 'videoinput')
    isVideoAvailable.value = videoDevices.length > 0
  } catch (error) {
    console.warn('Impossible de vérifier les périphériques vidéo:', error)
    isVideoAvailable.value = false
  }
})

// Watcher pour auto-scroll quand de nouveaux messages arrivent
watch(() => selectedConversation.value?.messages, (newMessages) => {
  if (newMessages) {
    nextTick(() => {
      const messagesContainer = document.querySelector('.overflow-y-auto')
      if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight
      }
    })
  }
}, { deep: true })

// Nettoyage du timer à la destruction du composant
onUnmounted(() => {
  if (callTimer.value) {
    clearInterval(callTimer.value)
  }
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

/* Animation pour les messages */
.message-enter-active, .message-leave-active {
  transition: all 0.3s ease;
}

.message-enter-from {
  opacity: 0;
  transform: translateY(20px);
}

.message-leave-to {
  opacity: 0;
  transform: scale(0.95);
}

/* Auto-resize textarea */
textarea {
  field-sizing: content;
  min-height: 44px;
  max-height: 120px;
}

/* Transitions pour mobile */
@media (max-width: 1023px) {
  .mobile-transition {
    transition: all 0.3s ease-in-out;
  }
}

/* Style pour les checks doubles */
.check-double {
  position: relative;
}

.check-double::after {
  content: '';
  position: absolute;
  top: 0;
  left: 4px;
  width: 8px;
  height: 8px;
  background: currentColor;
  clip-path: polygon(0 0, 100% 0, 50% 100%);
}

/* Animation du modal d'appel */
@keyframes pulse-ring {
  0% {
    transform: scale(0.33);
  }
  40%, 50% {
    opacity: 1;
  }
  100% {
    opacity: 0;
    transform: scale(1.5);
  }
}

.call-modal-enter-active, .call-modal-leave-active {
  transition: all 0.3s ease;
}

.call-modal-enter-from {
  opacity: 0;
  transform: scale(0.9);
}

.call-modal-leave-to {
  opacity: 0;
  transform: scale(0.9);
}

/* Animation pour les indicateurs d'appel */
@keyframes call-pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.call-indicator {
  animation: call-pulse 2s infinite;
}
</style>
