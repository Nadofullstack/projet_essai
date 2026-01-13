<template>
  <MainLayout>
    <div class="max-w-7xl mx-auto">
      <!-- Header spécifique à la page -->
      <div class="bg-white shadow-sm border-b border-gray-200 mb-6">
        <div class="px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between items-center py-4">
            <div class="flex items-center space-x-4">
              <button @click="$router.go(-1)" class="p-2 rounded-lg hover:bg-gray-100">
                <span class="material-symbols-outlined">arrow_back</span>
              </button>
              <h1 class="text-2xl font-bold text-gray-900">Appels</h1>
            </div>
            <div class="flex items-center space-x-2">
            <button 
              @click="activeTab = 'audio'"
              :class="[
                'px-4 py-2 rounded-lg font-medium transition-colors',
                activeTab === 'audio' 
                  ? 'bg-blue-600 text-white' 
                  : 'text-gray-600 hover:text-gray-900'
              ]"
            >
              Appels audio
            </button>
            <button 
              @click="activeTab = 'video'"
              :class="[
                'px-4 py-2 rounded-lg font-medium transition-colors',
                activeTab === 'video' 
                  ? 'bg-blue-600 text-white' 
                  : 'text-gray-600 hover:text-gray-900'
              ]"
            >
              Appels vidéo
            </button>
          </div>
        </div>
      </div>
    </div>
    </div>
    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Call Interface -->
      <div v-if="isInCall" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Call Header -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6">
          <div class="text-center">
            <div class="relative inline-block">
              <img :src="currentCall.contact.avatar" :alt="currentCall.contact.name" class="w-24 h-24 rounded-full border-4 border-white">
              <div class="absolute bottom-0 right-0 bg-green-500 w-6 h-6 rounded-full border-2 border-white"></div>
            </div>
            <h2 class="mt-4 text-2xl font-bold">{{ currentCall.contact.name }}</h2>
            <p class="text-blue-100">{{ callDuration }}</p>
          </div>
        </div>

        <!-- Video Area (for video calls) -->
        <div v-if="activeTab === 'video' && isInCall" class="relative bg-black h-96">
          <!-- Remote Video -->
          <div class="absolute inset-0 flex items-center justify-center">
            <img 
              v-if="currentCall.remoteVideo"
              :src="currentCall.remoteVideo" 
              alt="Remote video"
              class="w-full h-full object-cover"
            >
            <div v-else class="text-white text-center">
              <span class="material-symbols-outlined text-6xl">person</span>
              <p class="mt-2">{{ currentCall.contact.name }}</p>
            </div>
          </div>

          <!-- Local Video -->
          <div class="absolute bottom-4 right-4 w-32 h-24 bg-gray-800 rounded-lg overflow-hidden border-2 border-white">
            <img 
              v-if="currentCall.localVideo"
              :src="currentCall.localVideo" 
              alt="Local video"
              class="w-full h-full object-cover"
            >
            <div v-else class="w-full h-full flex items-center justify-center">
              <span class="material-symbols-outlined text-white">person</span>
            </div>
          </div>
        </div>

        <!-- Call Controls -->
        <div class="bg-gray-50 p-6">
          <div class="flex justify-center items-center space-x-4">
            <button 
              @click="toggleMute"
              :class="[
                'p-4 rounded-full transition-colors',
                isMuted ? 'bg-red-100 text-red-600' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
              ]"
            >
              <span class="material-symbols-outlined text-2xl">{{ isMuted ? 'mic_off' : 'mic' }}</span>
            </button>
            
            <button 
              v-if="activeTab === 'video'"
              @click="toggleCamera"
              :class="[
                'p-4 rounded-full transition-colors',
                isCameraOff ? 'bg-red-100 text-red-600' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
              ]"
            >
              <span class="material-symbols-outlined text-2xl">{{ isCameraOff ? 'videocam_off' : 'videocam' }}</span>
            </button>

            <button 
              @click="toggleSpeaker"
              :class="[
                'p-4 rounded-full transition-colors',
                isSpeakerOff ? 'bg-red-100 text-red-600' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
              ]"
            >
              <span class="material-symbols-outlined text-2xl">{{ isSpeakerOff ? 'volume_off' : 'volume_up' }}</span>
            </button>

            <button 
              @click="endCall"
              class="p-4 bg-red-600 text-white rounded-full hover:bg-red-700 transition-colors"
            >
              <span class="material-symbols-outlined text-2xl">call_end</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Call List -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Contacts -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-4 border-b border-gray-200">
              <h3 class="font-semibold text-gray-900">Contacts</h3>
            </div>
            <div class="max-h-96 overflow-y-auto">
              <div 
                v-for="contact in contacts"
                :key="contact.id"
                class="p-4 border-b border-gray-100 hover:bg-gray-50 transition-colors"
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-3">
                    <div class="relative">
                      <img :src="contact.avatar" :alt="contact.name" class="w-10 h-10 rounded-full">
                      <span :class="[
                        'absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-white',
                        contact.isOnline ? 'bg-green-500' : 'bg-gray-400'
                      ]"></span>
                    </div>
                    <div>
                      <h4 class="font-medium text-gray-900">{{ contact.name }}</h4>
                      <p class="text-sm text-gray-500">{{ contact.status }}</p>
                    </div>
                  </div>
                  <div class="flex space-x-1">
                    <button 
                      @click="startCall(contact, 'audio')"
                      class="p-2 rounded-lg hover:bg-gray-100 text-gray-600"
                      title="Appel audio"
                    >
                      <span class="material-symbols-outlined">phone</span>
                    </button>
                    <button 
                      v-if="activeTab === 'video'"
                      @click="startCall(contact, 'video')"
                      class="p-2 rounded-lg hover:bg-gray-100 text-gray-600"
                      title="Appel vidéo"
                    >
                      <span class="material-symbols-outlined">videocam</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Calls -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-4 border-b border-gray-200">
              <div class="flex justify-between items-center">
                <h3 class="font-semibold text-gray-900">Appels récents</h3>
                <button class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                  Tout voir
                </button>
              </div>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durée</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr 
                    v-for="call in recentCalls"
                    :key="call.id"
                    class="hover:bg-gray-50"
                  >
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <img :src="call.contact.avatar" :alt="call.contact.name" class="w-8 h-8 rounded-full mr-3">
                        <div class="text-sm font-medium text-gray-900">{{ call.contact.name }}</div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                        :class="call.type === 'video' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'">
                        <span class="material-symbols-outlined text-xs mr-1">
                          {{ call.type === 'video' ? 'videocam' : 'phone' }}
                        </span>
                        {{ call.type === 'video' ? 'Vidéo' : 'Audio' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ call.duration }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ call.date }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                        :class="call.status === 'answered' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                        {{ call.status === 'answered' ? 'Répondu' : 'Manqué' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <button 
                        @click="startCall(call.contact, call.type)"
                        class="text-blue-600 hover:text-blue-900 mr-3"
                      >
                        Rappeler
                      </button>
                      <button class="text-gray-400 hover:text-gray-600">
                        <span class="material-symbols-outlined text-sm">more_vert</span>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useAppelsStore } from '@/stores/appels'
import MainLayout from '@/components/Dashboard/Layout/MainLayout.vue'

const appelsStore = useAppelsStore()

// État local
const activeTab = ref('audio')
const isInCall = ref(false)
const currentCall = ref(null)
const isMuted = ref(false)
const isCameraOff = ref(false)
const isSpeakerOff = ref(false)
const callStartTime = ref(null)
const callTimer = ref(null)

// Données du store
const contacts = computed(() => appelsStore.contacts)
const recentCalls = computed(() => appelsStore.recentCalls)

// Computed
const callDuration = computed(() => {
  if (!callStartTime.value) return '00:00'
  const now = Date.now()
  const duration = Math.floor((now - callStartTime.value) / 1000)
  const minutes = Math.floor(duration / 60)
  const seconds = duration % 60
  return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
})

// Méthodes
const startCall = (contact, type) => {
  currentCall.value = {
    contact,
    type,
    localVideo: null,
    remoteVideo: null
  }
  isInCall.value = true
  callStartTime.value = Date.now()
  
  // Démarrer le timer
  callTimer.value = setInterval(() => {
    // Le computed callDuration se met à jour automatiquement
  }, 1000)

  // Simuler l'appel
  setTimeout(() => {
    if (type === 'video') {
      currentCall.value.remoteVideo = `https://picsum.photos/seed/${contact.id}/640/480.jpg`
      currentCall.value.localVideo = `https://picsum.photos/seed/self/320/240.jpg`
    }
  }, 2000)

  // Enregistrer l'appel dans l'historique
  appelsStore.addCallToHistory({
    contact,
    type,
    status: 'answered',
    startTime: callStartTime.value
  })
}

const endCall = () => {
  if (callTimer.value) {
    clearInterval(callTimer.value)
    callTimer.value = null
  }
  
  // Mettre à jour la durée de l'appel
  if (currentCall.value && callStartTime.value) {
    const duration = Math.floor((Date.now() - callStartTime.value) / 1000)
    appelsStore.updateCallDuration(currentCall.value.contact.id, duration)
  }
  
  isInCall.value = false
  currentCall.value = null
  callStartTime.value = null
  isMuted.value = false
  isCameraOff.value = false
  isSpeakerOff.value = false
}

const toggleMute = () => {
  isMuted.value = !isMuted.value
}

const toggleCamera = () => {
  isCameraOff.value = !isCameraOff.value
  if (!isCameraOff.value) {
    currentCall.value.localVideo = `https://picsum.photos/seed/self/320/240.jpg`
  } else {
    currentCall.value.localVideo = null
  }
}

const toggleSpeaker = () => {
  isSpeakerOff.value = !isSpeakerOff.value
}

// Nettoyer le timer quand le composant est détruit
onUnmounted(() => {
  if (callTimer.value) {
    clearInterval(callTimer.value)
  }
})

// Chargement initial
onMounted(() => {
  appelsStore.fetchContacts()
  appelsStore.fetchCalls()
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
