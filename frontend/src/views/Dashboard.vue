<template>
  <ResponsiveLayout>
    <div class="space-y-6 sm:space-y-8">
      <!-- Page Header -->
      <div class="px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Tableau de bord</h1>
        <p class="text-gray-600 mt-2">Bienvenue, {{ currentUser.name }}. Voici vos programmes et messages.</p>
      </div>
      
      <!-- Stats Cards Responsive -->
      <div class="px-4 sm:px-6 lg:px-8">
        <ResponsiveStats
          :stats="dashboardStats"
          :show-chart="true"
          chart-title="Activité récente"
        >
          <template #chart>
            <div class="h-full flex items-center justify-center text-gray-500">
              <p>Graphique à implémenter</p>
            </div>
          </template>
        </ResponsiveStats>
      </div>
      
      <!-- Main Grid Responsive -->
      <ResponsiveGrid :cols="{ mobile: 1, tablet: 2, desktop: 3, large: 4 }">
        <!-- Section Enregistrement Audio -->
        <ResponsiveCard hover class="transform hover:scale-105 transition-transform duration-200">
          <template #header>
            <h2 class="text-lg sm:text-xl font-bold text-gray-900">Studio d'enregistrement</h2>
            <p class="text-sm text-gray-600 mt-1">Enregistrez vos notes vocales et messages audio</p>
          </template>
          
          <div class="space-y-4">
            <!-- Contrôles d'enregistrement -->
            <div class="flex justify-center">
              <button 
                @click="toggleRecording"
                :class="[
                  'px-4 sm:px-6 py-2 sm:py-3 rounded-lg font-medium transition-all duration-200 flex items-center space-x-2',
                  isRecording 
                    ? 'bg-red-500 hover:bg-red-600 text-white animate-pulse' 
                    : 'bg-blue-500 hover:bg-blue-600 text-white'
                ]"
              >
                <span class="text-sm sm:text-base">
                  {{ isRecording ? 'Arrêter' : 'Commencer' }}
                </span>
                <component :is="isRecording ? StopIcon : MicrophoneIcon" class="h-4 w-4 sm:h-5 sm:w-5" />
              </button>
            </div>
            
            <!-- Timer -->
            <div v-if="isRecording" class="text-center">
              <p class="text-2xl sm:text-3xl font-mono text-gray-900">{{ formatTime(recordingTime) }}</p>
              <p class="text-sm text-gray-600">Enregistrement en cours...</p>
            </div>
            
            <!-- Messages récents -->
            <div class="space-y-3">
              <h3 class="text-sm font-medium text-gray-900">Messages récents</h3>
              <div class="space-y-2">
                <div 
                  v-for="message in recentMessages.slice(0, 3)" 
                  :key="message.id"
                  class="flex items-center space-x-3 p-2 bg-gray-50 rounded-lg"
                >
                  <div class="flex-shrink-0">
                    <div class="h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                      <component :is="MessageIcon" class="h-4 w-4 text-blue-600" />
                    </div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ message.content }}</p>
                    <p class="text-xs text-gray-500">{{ formatTime(message.timestamp) }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <template #actions>
            <button class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
              Voir tout
            </button>
          </template>
        </ResponsiveCard>

        <!-- Section Appels Récents -->
        <ResponsiveCard hover class="transform hover:scale-105 transition-transform duration-200">
          <template #header>
            <h2 class="text-lg sm:text-xl font-bold text-gray-900">Appels récents</h2>
            <p class="text-sm text-gray-600 mt-1">Historique de vos communications</p>
          </template>
          
          <div class="space-y-4">
            <!-- Stats des appels -->
            <div class="grid grid-cols-3 gap-4 text-center">
              <div>
                <p class="text-2xl font-bold text-gray-900">{{ recentCalls.length }}</p>
                <p class="text-xs text-gray-600">Total</p>
              </div>
              <div>
                <p class="text-2xl font-bold text-green-600">{{ answeredCalls }}</p>
                <p class="text-xs text-gray-600">Répondus</p>
              </div>
              <div>
                <p class="text-2xl font-bold text-red-600">{{ missedCalls }}</p>
                <p class="text-xs text-gray-600">Manqués</p>
              </div>
            </div>
            
            <!-- Liste des appels -->
            <div class="space-y-2">
              <div 
                v-for="call in recentCalls.slice(0, 4)" 
                :key="call.id"
                class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg transition-colors"
              >
                <div class="flex items-center space-x-3">
                  <div class="flex-shrink-0">
                    <div :class="[
                      'h-8 w-8 rounded-full flex items-center justify-center',
                      call.type === 'incoming' ? 'bg-green-100' : 'bg-blue-100'
                    ]">
                      <component :is="PhoneIcon" class="h-4 w-4" :class="[
                        call.type === 'incoming' ? 'text-green-600' : 'text-blue-600'
                      ]" />
                    </div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ call.contact }}</p>
                    <p class="text-xs text-gray-500">{{ formatTime(call.timestamp) }}</p>
                  </div>
                </div>
                <div class="flex items-center space-x-2">
                  <span :class="[
                    'text-xs px-2 py-1 rounded-full',
                    call.status === 'answered' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                  ]">
                    {{ call.status === 'answered' ? 'Répondu' : 'Manqué' }}
                  </span>
                  <span class="text-xs text-gray-500">{{ formatDuration(call.duration) }}</span>
                </div>
              </div>
            </div>
          </div>
          
          <template #actions>
            <button class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
              Voir tout
            </button>
          </template>
        </ResponsiveCard>

        <!-- Section Rendez-vous -->
        <ResponsiveCard hover class="transform hover:scale-105 transition-transform duration-200">
          <template #header>
            <h2 class="text-lg sm:text-xl font-bold text-gray-900">Rendez-vous</h2>
            <p class="text-sm text-gray-600 mt-1">Vos prochains rendez-vous</p>
          </template>
          
          <div class="space-y-4">
            <!-- Calendrier simplifié -->
            <div class="text-center">
              <p class="text-lg font-medium text-gray-900">{{ today }}</p>
              <p class="text-sm text-gray-600">{{ formatDate(today) }}</p>
            </div>
            
            <!-- Liste des rendez-vous -->
            <div class="space-y-2">
              <div 
                v-for="appointment in todayAppointments.slice(0, 3)" 
                :key="appointment.id"
                class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded-lg transition-colors"
              >
                <div class="flex-shrink-0">
                  <div class="h-8 w-8 bg-purple-100 rounded-full flex items-center justify-center">
                    <component :is="CalendarIcon" class="h-4 w-4 text-purple-600" />
                  </div>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 truncate">{{ appointment.title }}</p>
                  <p class="text-xs text-gray-500">{{ appointment.time }} - {{ appointment.location }}</p>
                </div>
              </div>
            </div>
          </div>
          
          <template #actions>
            <button class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
              Voir tout
            </button>
          </template>
        </ResponsiveCard>
      </ResponsiveGrid>
    </div>
  </ResponsiveLayout>
    />
    
    <ModalMessageModal 
      v-if="selectedMessage"
      :message="selectedMessage"
      @close="selectedMessage = null"
      @reply="replyToMessage"
    />
 
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import LayoutSidebar from '@/components/Dashboard/Layout/Sidebar.vue'
import LayoutHeader from '@/components/Dashboard/Layout/Header.vue'
import WidgetStatsCards from '@/components/Dashboard/Widgets/StatsCards.vue'
import WidgetCalendarWidget from '@/components/Dashboard/Widgets/CalendarWidget.vue'
import WidgetQuickActions from '@/components/Dashboard/Widgets/QuickActions.vue'
import FeatureAudioRecorder from '@/components/Dashboard/Features/AudioRecorder.vue'
import FeatureMessagesList from '@/components/Dashboard/Features/MessagesList.vue'
import FeatureRecentPrograms from '@/components/Dashboard/Features/RecentPrograms.vue'
import ModalMessageModal from '@/components/Dashboard/Modals/MessageModal.vue'
import ModalProgramModal from '@/components/Dashboard/Modals/ProgramModal.vue'
import RendezVous from './RendezVous.vue'
import { useDashboardStore } from '@/stores/dashboard'

const router = useRouter()
const dashboardStore = useDashboardStore()

// État local
const sidebarCollapsed = ref(false)
const isRecording = ref(false)
const selectedProgram = ref(null)
const selectedMessage = ref(null)

// Nouvelles variables pour l'enregistrement audio
const recordingTime = ref(new Date())
const recordingDuration = ref(0)
const audioQuality = ref('medium')
const audioFormat = ref('mp3')
const recentRecordings = ref([
  {
    id: 1,
    title: 'Note vocale - Réunion projet',
    date: '08/01/2024',
    duration: '2:45',
    size: '3.2 MB'
  },
  {
    id: 2,
    title: 'Message audio - Client',
    date: '07/01/2024',
    duration: '1:15',
    size: '1.8 MB'
  }
])

// Nouvelles variables pour les messages
const messageStats = ref({
  total: 24,
  unread: 5,
  today: 3
})

// Données du store
const currentUser = computed(() => dashboardStore.currentUser)
const dashboardStats = computed(() => dashboardStore.stats)
const recentPrograms = computed(() => dashboardStore.recentPrograms)
const recentMessages = computed(() => dashboardStore.recentMessages)
const upcomingEvents = computed(() => dashboardStore.upcomingEvents)
const unreadMessages = computed(() => dashboardStore.unreadMessages)

// Méthodes
const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
}

const selectProgram = (program) => {
  selectedProgram.value = program
}

const selectMessage = (message) => {
  selectedMessage.value = message
  dashboardStore.markMessageAsRead(message.id)
}

const handleRecording = () => {
  isRecording.value = !isRecording.value
  if (isRecording.value) {
    // Démarrer l'enregistrement
    recordingTime.value = new Date()
    startRecordingTimer()
  } else {
    // Arrêter l'enregistrement
    stopRecordingTimer()
  }
}

const startRecordingTimer = () => {
  recordingDuration.value = 0
  // Timer pour la durée d'enregistrement
  const timer = setInterval(() => {
    recordingDuration.value++
  }, 1000)
  
  // Garder une référence pour arrêter le timer
  window.currentRecordingTimer = timer
}

const stopRecordingTimer = () => {
  if (window.currentRecordingTimer) {
    clearInterval(window.currentRecordingTimer)
  }
}

const toggleRecording = () => {
  isRecording.value = !isRecording.value
  if (isRecording.value) {
    recordingTime.value = new Date()
    startRecordingTimer()
  } else {
    stopRecordingTimer()
  }
}

const playRecording = (recording) => {
  console.log('Lecture de l\'enregistrement:', recording.title)
  // Logique pour lire l'enregistrement
}

const openMessage = (message) => {
  selectedMessage.value = message
  dashboardStore.markMessageAsRead(message.id)
}

const formatTime = (date) => {
  return date.toLocaleTimeString('fr-FR', { 
    hour: '2-digit', 
    minute: '2-digit' 
  })
}

const formatDuration = (seconds) => {
  const mins = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${mins}:${secs.toString().padStart(2, '0')}`
}

const getEventColorClass = (type) => {
  const colors = {
    meeting: 'bg-blue-500',
    consultation: 'bg-green-500',
    call: 'bg-purple-500',
    personal: 'bg-orange-500'
  }
  return colors[type] || 'bg-gray-500'
}

const saveRecording = (audioData) => {
  isRecording.value = false
  dashboardStore.addAudioRecording(audioData)
}

const markAsRead = (messageId) => {
  dashboardStore.markMessageAsRead(messageId)
}

const updateProgram = (programData) => {
  dashboardStore.updateProgram(programData)
  selectedProgram.value = null
}

const replyToMessage = (messageData) => {
  dashboardStore.sendMessage(messageData)
  selectedMessage.value = null
}

const handleQuickAction = (action) => {
  switch(action) {
    case 'new_program':
      router.push('/programs/new')
      break
    case 'new_message':
      router.push('/messages/compose')
      break
    case 'settings':
      router.push('/settings')
      break
    case 'help':
      router.push('/help')
      break
  }
}

const addEvent = (eventData) => {
  dashboardStore.addEvent(eventData)
}

// Chargement initial
onMounted(() => {
  dashboardStore.fetchDashboardData()
})


</script>

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