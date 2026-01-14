<template>
  <MainLayout>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
      <!-- Hero Section -->
      <div class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
          <div class="text-center">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Bienvenue sur VocalApp</h1>
            <p class="text-xl text-gray-600">Votre plateforme de communication unifiée</p>
          </div>
        </div>
      </div>

      <!-- Quick Stats -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
              <div class="p-3 bg-blue-100 rounded-lg">
                <span class="material-symbols-outlined text-blue-600">chat</span>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Messages</p>
                <p class="text-2xl font-bold text-gray-900">{{ messageStats.total }}</p>
              </div>
            </div>
          </div>
          <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
              <div class="p-3 bg-green-100 rounded-lg">
                <span class="material-symbols-outlined text-green-600">phone</span>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Appels</p>
                <p class="text-2xl font-bold text-gray-900">{{ answeredCalls }}</p>
              </div>
            </div>
          </div>
          <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
              <div class="p-3 bg-purple-100 rounded-lg">
                <span class="material-symbols-outlined text-purple-600">event</span>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Événements</p>
                <p class="text-2xl font-bold text-gray-900">{{ upcomingEvents.length }}</p>
              </div>
            </div>
          </div>
          <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
              <div class="p-3 bg-orange-100 rounded-lg">
                <span class="material-symbols-outlined text-orange-600">mic</span>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Enregistrements</p>
                <p class="text-2xl font-bold text-gray-900">{{ recentRecordings.length }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Feature Cards -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Messages Card -->
          <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow cursor-pointer" @click="$router.push('/messages')">
            <div class="flex items-center mb-4">
              <div class="p-3 bg-blue-100 rounded-lg">
                <span class="material-symbols-outlined text-blue-600 text-2xl">chat</span>
              </div>
              <h3 class="ml-4 text-xl font-semibold text-gray-900">Messages</h3>
            </div>
            <p class="text-gray-600 mb-4">Envoyez et recevez des messages instantanés</p>
            <div class="flex items-center text-sm text-gray-500">
              <span>{{ unreadMessages }} non lus</span>
            </div>
          </div>

          <!-- Calls Card -->
          <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow cursor-pointer" @click="$router.push('/appels')">
            <div class="flex items-center mb-4">
              <div class="p-3 bg-green-100 rounded-lg">
                <span class="material-symbols-outlined text-green-600 text-2xl">phone</span>
              </div>
              <h3 class="ml-4 text-xl font-semibold text-gray-900">Appels</h3>
            </div>
            <p class="text-gray-600 mb-4">Passez des appels audio et vidéo</p>
            <div class="flex items-center text-sm text-gray-500">
              <span>{{ answeredCalls }} appels récents</span>
            </div>
          </div>

          <!-- Events Card -->
          <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow cursor-pointer" @click="$router.push('/evenements')">
            <div class="flex items-center mb-4">
              <div class="p-3 bg-purple-100 rounded-lg">
                <span class="material-symbols-outlined text-purple-600 text-2xl">event</span>
              </div>
              <h3 class="ml-4 text-xl font-semibold text-gray-900">Événements</h3>
            </div>
            <p class="text-gray-600 mb-4">Planifiez vos événements et rendez-vous</p>
            <div class="flex items-center text-sm text-gray-500">
              <span>{{ upcomingEvents.length }} à venir</span>
            </div>
          </div>

          <!-- Audio Card -->
          <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow cursor-pointer" @click="$router.push('/audio')">
            <div class="flex items-center mb-4">
              <div class="p-3 bg-orange-100 rounded-lg">
                <span class="material-symbols-outlined text-orange-600 text-2xl">mic</span>
              </div>
              <h3 class="ml-4 text-xl font-semibold text-gray-900">Audio</h3>
            </div>
            <p class="text-gray-600 mb-4">Enregistrez et gérez vos fichiers audio</p>
            <div class="flex items-center text-sm text-gray-500">
              <span>{{ recentRecordings.length }} enregistrements</span>
            </div>
          </div>

          <!-- Appointments Card -->
          <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow cursor-pointer" @click="$router.push('/rendez-vous')">
            <div class="flex items-center mb-4">
              <div class="p-3 bg-indigo-100 rounded-lg">
                <span class="material-symbols-outlined text-indigo-600 text-2xl">schedule</span>
              </div>
              <h3 class="ml-4 text-xl font-semibold text-gray-900">Rendez-vous</h3>
            </div>
            <p class="text-gray-600 mb-4">Gérez vos rendez-vous et plannings</p>
            <div class="flex items-center text-sm text-gray-500">
              <span>{{ todayAppointments.length }} aujourd'hui</span>
            </div>
          </div>

          <!-- Settings Card -->
          <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow cursor-pointer" @click="$router.push('/settings')">
            <div class="flex items-center mb-4">
              <div class="p-3 bg-gray-100 rounded-lg">
                <span class="material-symbols-outlined text-gray-600 text-2xl">settings</span>
              </div>
              <h3 class="ml-4 text-xl font-semibold text-gray-900">Paramètres</h3>
            </div>
            <p class="text-gray-600 mb-4">Configurez votre compte et vos préférences</p>
            <div class="flex items-center text-sm text-gray-500">
              <span>Personnalisez</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/components/Dashboard/Layout/MainLayout.vue'
import { useDashboardStore } from '@/stores/dashboard'
import { useAppelsStore } from '@/stores/appels'
import { useRendezVousStore } from '@/stores/rendezvous'
import { useEvenementsStore } from '@/stores/evenements'

const router = useRouter()
const dashboardStore = useDashboardStore()
const appelsStore = useAppelsStore()
const rendezvousStore = useRendezVousStore()
const evenementsStore = useEvenementsStore()

// Nouvelles variables pour l'enregistrement audio
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
// const upcomingEvents = computed(() => dashboardStore.upcomingEvents)
const unreadMessages = computed(() => dashboardStore.unreadMessages)
// const recentCalls = computed(() => {
//   const calls = appelsStore.getRecentCalls(10)
//   return calls.map(call => {
    // const contact = appelsStore.contacts.find(c => c.id === call.contactId)
//     return {
//       ...call,
//       contact: contact ? contact.name : 'Contact inconnu',
//       status: call.status === 'incoming' ? 'answered' : call.status // adjust status
//     }
//   })
// })
// const answeredCalls = computed(() => recentCalls.value.filter(call => call.status === 'answered').length)
// const missedCalls = computed(() => recentCalls.value.filter(call => call.status === 'missed').length)
const todayAppointments = computed(() => {
  const today = new Date().toISOString().split('T')[0]
  return rendezvousStore.appointments.filter(apt => apt.date === today)
})
const upcomingEvents = computed(() => evenementsStore.events.slice(0, 3))
const today = computed(() => formatDate(new Date().toISOString().split('T')[0]))
const recentCalls = computed(() => appelsStore.getRecentCalls(10))
const answeredCalls = computed(() => appelsStore.calls.filter(c => c.status === 'incoming' || c.status === 'outgoing').length)
const missedCalls = computed(() => appelsStore.calls.filter(c => c.status === 'missed').length)

// Chargement initial
onMounted(async () => {
  await dashboardStore.fetchDashboardData()
  await appelsStore.fetchCalls()
  await appelsStore.fetchContacts()
  await rendezvousStore.fetchAppointments()
  await evenementsStore.fetchEvents()
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