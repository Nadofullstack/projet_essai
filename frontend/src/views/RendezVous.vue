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
              <h1 class="text-2xl font-bold text-gray-900">Rendez-vous</h1>
            </div>
            <button 
              @click="openNewRdvModal"
              class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center space-x-2"
            >
              <span class="material-symbols-outlined">add</span>
              <span>Nouveau rendez-vous</span>
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Calendar -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <!-- Calendar Header -->
            <div class="p-4 border-b border-gray-200">
              <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                  <button @click="previousMonth" class="p-2 rounded-lg hover:bg-gray-100">
                    <span class="material-symbols-outlined">chevron_left</span>
                  </button>
                  <h2 class="text-xl font-semibold text-gray-900">
                    {{ currentMonthName }} {{ currentYear }}
                  </h2>
                  <button @click="nextMonth" class="p-2 rounded-lg hover:bg-gray-100">
                    <span class="material-symbols-outlined">chevron_right</span>
                  </button>
                </div>
                <div class="flex space-x-2">
                  <button 
                    @click="viewMode = 'month'"
                    :class="[
                      'px-3 py-1 rounded-lg text-sm font-medium',
                      viewMode === 'month' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:text-gray-900'
                    ]"
                  >
                    Mois
                  </button>
                  <button 
                    @click="viewMode = 'week'"
                    :class="[
                      'px-3 py-1 rounded-lg text-sm font-medium',
                      viewMode === 'week' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:text-gray-900'
                    ]"
                  >
                    Semaine
                  </button>
                  <button 
                    @click="viewMode = 'day'"
                    :class="[
                      'px-3 py-1 rounded-lg text-sm font-medium',
                      viewMode === 'day' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:text-gray-900'
                    ]"
                  >
                    Jour
                  </button>
                </div>
              </div>
            </div>

            <!-- Calendar Grid -->
            <div class="p-4">
              <!-- Days of week -->
              <div class="grid grid-cols-7 gap-1 mb-2">
                <div v-for="day in daysOfWeek" :key="day" class="text-center text-sm font-medium text-gray-700 py-2">
                  {{ day }}
                </div>
              </div>

              <!-- Calendar Days -->
              <div class="grid grid-cols-7 gap-1">
                <div 
                  v-for="day in calendarDays" 
                  :key="day.date"
                  :class="[
                    'min-h-[80px] border border-gray-200 rounded-lg p-2 cursor-pointer transition-colors relative',
                    day.isCurrentMonth ? 'bg-white hover:bg-gray-50' : 'bg-gray-50',
                    day.isToday ? 'ring-2 ring-blue-500' : '',
                    selectedDate === day.date ? 'bg-red-50 border-red-500 ring-2 ring-red-500' : ''
                  ]"
                  @click="selectDate(day.date)"
                >
                  <div class="flex justify-between items-start mb-1">
                    <span :class="[
                      'text-sm font-medium',
                      day.isCurrentMonth ? 'text-gray-900' : 'text-gray-400',
                      day.isToday ? 'text-blue-600' : '',
                      selectedDate === day.date ? 'text-red-600 font-bold' : ''
                    ]">
                      {{ day.day }}
                    </span>
                    <span v-if="day.events.length > 0" class="bg-blue-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                      {{ day.events.length }}
                    </span>
                  </div>
                  <div class="space-y-1">
                    <div 
                      v-for="event in day.events.slice(0, 2)" 
                      :key="event.id"
                      class="text-xs p-1 rounded truncate"
                      :class="getEventColorClass(event.type)"
                    >
                      {{ formatTime(event.startTime) }} {{ event.title }}
                    </div>
                    <div v-if="day.events.length > 2" class="text-xs text-gray-500">
                      +{{ day.events.length - 2 }} plus
                    </div>
                  </div>
                  <!-- Overlay pour s'assurer que tout le jour est cliquable -->
                  <div class="absolute inset-0 cursor-pointer" @click="selectDate(day.date)"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Selected Date Events -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-4 border-b border-gray-200">
              <h3 class="font-semibold text-gray-900">
                {{ selectedDate ? `Rendez-vous du ${formatSelectedDate(selectedDate)}` : "Rendez-vous d'aujourd'hui" }}
              </h3>
            </div>
            <div class="max-h-96 overflow-y-auto">
              <div v-if="selectedDateEvents.length === 0" class="p-8 text-center text-gray-500">
                <span class="material-symbols-outlined text-4xl">event_busy</span>
                <p class="mt-2">Aucun rendez-vous ce jour</p>
              </div>
              <div v-else class="divide-y divide-gray-200">
                <div 
                  v-for="event in selectedDateEvents" 
                  :key="event.id"
                  class="p-4 hover:bg-gray-50 transition-colors"
                >
                  <div class="flex justify-between items-start">
                    <div class="flex-1">
                      <h4 class="font-medium text-gray-900">{{ event.title }}</h4>
                      <div class="mt-1 space-y-1">
                        <p class="text-sm text-gray-600 flex items-center">
                          <span class="material-symbols-outlined text-sm mr-1">schedule</span>
                          {{ formatTime(event.startTime) }} - {{ formatTime(event.endTime) }}
                        </p>
                        <p class="text-sm text-gray-600 flex items-center">
                          <span class="material-symbols-outlined text-sm mr-1">person</span>
                          {{ event.participant?.name || 'Non spécifié' }}
                        </p>
                        <p v-if="event.location" class="text-sm text-gray-600 flex items-center">
                          <span class="material-symbols-outlined text-sm mr-1">location_on</span>
                          {{ event.location }}
                        </p>
                      </div>
                      <div class="mt-2 flex items-center space-x-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="getEventColorClass(event.type)">
                          {{ getEventTypeLabel(event.type) }}
                        </span>
                        <span :class="[
                          'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                          event.status === 'confirmed' ? 'bg-green-100 text-green-800' :
                          event.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                          'bg-red-100 text-red-800'
                        ]">
                          {{ getStatusLabel(event.status) }}
                        </span>
                      </div>
                    </div>
                    <div class="flex space-x-1 ml-4">
                      <button @click="editEvent(event)" class="p-1 rounded hover:bg-gray-100 text-blue-600">
                        <span class="material-symbols-outlined text-sm">edit</span>
                      </button>
                      <button @click="deleteEvent(event.id)" class="p-1 rounded hover:bg-gray-100 text-red-600">
                        <span class="material-symbols-outlined text-sm">delete</span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Stats -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <h3 class="font-semibold text-gray-900 mb-4">Statistiques rapides</h3>
            <div class="space-y-3">
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Rendez-vous cette semaine</span>
                <span class="font-semibold text-gray-900">{{ weekStats.total }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Confirmés</span>
                <span class="font-semibold text-green-600">{{ weekStats.confirmed }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">En attente</span>
                <span class="font-semibold text-yellow-600">{{ weekStats.pending }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Aujourd'hui</span>
                <span class="font-semibold text-blue-600">{{ todayStats.total }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- New Rendez-vous Modal -->
    <div v-if="showNewRdvModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-bold text-gray-900 mb-4">
          {{ editingEvent ? 'Modifier le rendez-vous' : 'Nouveau rendez-vous' }}
        </h2>
        <form @submit.prevent="saveRdv" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Titre <span class="text-red-500">*</span></label>
            <input 
              v-model="newRdv.title"
              type="text" 
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Ex: Réunion avec le client"
            >
          </div>
          
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Date <span class="text-red-500">*</span></label>
              <input 
                v-model="newRdv.date"
                type="date" 
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Heure début <span class="text-red-500">*</span></label>
              <input 
                v-model="newRdv.startTime"
                type="time" 
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Durée <span class="text-red-500">*</span></label>
            <select 
              v-model="newRdv.duration"
              @change="updateEndTime"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="0.5">30 minutes</option>
              <option value="1">1 heure</option>
              <option value="1.5">1h 30 minutes</option>
              <option value="2">2 heures</option>
              <option value="3">3 heures</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Heure de fin</label>
            <input 
              v-model="newRdv.endTime"
              type="time" 
              readonly
              class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50"
            >
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nom du contact <span class="text-red-500">*</span></label>
            <input 
              v-model="newRdv.contactName"
              type="text" 
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Ex: Jean Dupont"
            >
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email du contact</label>
            <input 
              v-model="newRdv.contactEmail"
              type="email" 
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="exemple@email.com"
            >
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone du contact</label>
            <input 
              v-model="newRdv.contactPhone"
              type="tel" 
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="01 23 45 67 89"
            >
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Lieu</label>
            <input 
              v-model="newRdv.location"
              type="text" 
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Ex: Bureau 304"
            >
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Type de rendez-vous <span class="text-red-500">*</span></label>
            <select 
              v-model="newRdv.type"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option v-for="type in appointmentTypes" :key="type.value" :value="type.value">
                {{ type.label }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
            <select 
              v-model="newRdv.status"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="pending">En attente</option>
              <option value="confirmed">Confirmé</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea 
              v-model="newRdv.description"
              rows="3"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Notes supplémentaires..."
            ></textarea>
          </div>

          <div class="text-sm text-gray-500">
            <span class="text-red-500">*</span> Champs obligatoires
          </div>

          <div class="flex justify-end space-x-2 pt-4">
            <button 
              type="button"
              @click="closeModal"
              :disabled="isSaving"
              class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Annuler
            </button>
            <button 
              type="submit"
              :disabled="isSaving"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center min-w-[80px]"
            >
              <span v-if="isSaving" class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
              {{ isSaving ? 'Enregistrement...' : (editingEvent ? 'Modifier' : 'Créer') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Success Notification -->
    <div v-if="showSuccessNotification" class="fixed top-4 right-4 z-50">
      <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-lg flex items-center space-x-2">
        <span class="material-symbols-outlined text-green-500">check_circle</span>
        <span>{{ successMessage }}</span>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRendezVousStore } from '@/stores/rendezvous'
import MainLayout from '@/components/Dashboard/Layout/MainLayout.vue'

const rendezvousStore = useRendezVousStore()

// État local
const viewMode = ref('month')
const currentMonthIndex = ref(new Date().getMonth())
const currentYear = ref(new Date().getFullYear())
const selectedDate = ref(null)
const showNewRdvModal = ref(false)
const isSaving = ref(false)
const editingEvent = ref(null)
const showSuccessNotification = ref(false)
const successMessage = ref('')

// Formulaire
const newRdv = ref({
  title: '',
  date: '',
  startTime: '09:00',
  endTime: '10:00',
  duration: '1',
  contactName: '',
  contactEmail: '',
  contactPhone: '',
  location: '',
  type: 'consultation',
  status: 'pending',
  description: ''
})

// Données du store
const events = computed(() => rendezvousStore.appointments || [])
const appointmentTypes = computed(() => rendezvousStore.appointmentTypes || [])

// Computed
const monthNames = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre']
const daysOfWeek = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam']

const currentMonthName = computed(() => monthNames[currentMonthIndex.value])

const calendarDays = computed(() => {
  const today = new Date()
  const todayStr = today.toISOString().split('T')[0]
  
  // Si aucune date n'est sélectionnée, utiliser aujourd'hui
  if (!selectedDate.value) {
    selectedDate.value = todayStr
  }
  
  const firstDay = new Date(currentYear.value, currentMonthIndex.value, 1)
  const lastDay = new Date(currentYear.value, currentMonthIndex.value + 1, 0)
  const startDate = new Date(firstDay)
  startDate.setDate(startDate.getDate() - firstDay.getDay())
  
  const days = []
  
  for (let i = 0; i < 42; i++) {
    const date = new Date(startDate)
    date.setDate(startDate.getDate() + i)
    
    const dateStr = date.toISOString().split('T')[0]
    const dayEvents = events.value.filter(event => event.date === dateStr)
    
    days.push({
      date: dateStr,
      day: date.getDate(),
      isCurrentMonth: date.getMonth() === currentMonthIndex.value,
      isToday: dateStr === todayStr,
      events: dayEvents
    })
  }
  
  return days
})

const selectedDateEvents = computed(() => {
  if (!selectedDate.value) {
    const today = new Date().toISOString().split('T')[0]
    selectedDate.value = today
  }
  return events.value
    .filter(event => event.date === selectedDate.value)
    .sort((a, b) => a.startTime.localeCompare(b.startTime))
})

const weekStats = computed(() => {
  const today = new Date()
  const weekStart = new Date(today)
  weekStart.setDate(today.getDate() - today.getDay())
  const weekEnd = new Date(weekStart)
  weekEnd.setDate(weekStart.getDate() + 6)
  
  const weekStartStr = weekStart.toISOString().split('T')[0]
  const weekEndStr = weekEnd.toISOString().split('T')[0]
  
  const weekEvents = events.value.filter(event => {
    return event.date >= weekStartStr && event.date <= weekEndStr
  })
  
  return {
    total: weekEvents.length,
    confirmed: weekEvents.filter(e => e.status === 'confirmed').length,
    pending: weekEvents.filter(e => e.status === 'pending').length
  }
})

const todayStats = computed(() => {
  const today = new Date().toISOString().split('T')[0]
  const todayEvents = events.value.filter(event => event.date === today)
  return {
    total: todayEvents.length,
    confirmed: todayEvents.filter(e => e.status === 'confirmed').length,
    pending: todayEvents.filter(e => e.status === 'pending').length
  }
})

// Watch pour calculer l'heure de fin
watch(() => [newRdv.value.startTime, newRdv.value.duration], () => {
  updateEndTime()
})

// Méthodes
const previousMonth = () => {
  if (currentMonthIndex.value === 0) {
    currentMonthIndex.value = 11
    currentYear.value--
  } else {
    currentMonthIndex.value--
  }
}

const nextMonth = () => {
  if (currentMonthIndex.value === 11) {
    currentMonthIndex.value = 0
    currentYear.value++
  } else {
    currentMonthIndex.value++
  }
}

const selectDate = (date) => {
  console.log('Date sélectionnée:', date) // Debug
  selectedDate.value = date
  // Pré-remplir le formulaire avec la date sélectionnée
  newRdv.value.date = date
}

const openNewRdvModal = () => {
  editingEvent.value = null
  resetForm()
  
  // Si une date est sélectionnée, l'utiliser, sinon utiliser aujourd'hui
  if (!selectedDate.value) {
    const today = new Date().toISOString().split('T')[0]
    selectedDate.value = today
    newRdv.value.date = today
  } else {
    newRdv.value.date = selectedDate.value
  }
  
  // Définir une heure par défaut (prochaine heure pleine)
  const now = new Date()
  const nextHour = now.getHours() + 1
  newRdv.value.startTime = `${nextHour.toString().padStart(2, '0')}:00`
  updateEndTime()
  
  showNewRdvModal.value = true
}

const editEvent = (event) => {
  editingEvent.value = event
  newRdv.value = {
    title: event.title,
    date: event.date,
    startTime: event.startTime,
    endTime: event.endTime,
    duration: calculateDuration(event.startTime, event.endTime).toString(),
    contactName: event.participant?.name || '',
    contactEmail: event.participant?.email || '',
    contactPhone: event.participant?.phone || '',
    location: event.location || '',
    type: event.type || 'consultation',
    status: event.status || 'pending',
    description: event.description || ''
  }
  showNewRdvModal.value = true
}

const updateEndTime = () => {
  if (!newRdv.value.startTime || !newRdv.value.duration) return
  
  const [hours, minutes] = newRdv.value.startTime.split(':').map(Number)
  const duration = parseFloat(newRdv.value.duration)
  
  const totalMinutes = hours * 60 + minutes + (duration * 60)
  const endHours = Math.floor(totalMinutes / 60) % 24
  const endMinutes = totalMinutes % 60
  
  newRdv.value.endTime = `${endHours.toString().padStart(2, '0')}:${endMinutes.toString().padStart(2, '0')}`
}

const calculateDuration = (startTime, endTime) => {
  const [startHours, startMinutes] = startTime.split(':').map(Number)
  const [endHours, endMinutes] = endTime.split(':').map(Number)
  
  const totalStartMinutes = startHours * 60 + startMinutes
  const totalEndMinutes = endHours * 60 + endMinutes
  
  return (totalEndMinutes - totalStartMinutes) / 60
}

const showSuccess = (message) => {
  successMessage.value = message
  showSuccessNotification.value = true
  setTimeout(() => {
    showSuccessNotification.value = false
  }, 3000)
}

const saveRdv = async () => {
  // Validation
  if (!newRdv.value.title.trim()) {
    alert('Veuillez saisir un titre')
    return
  }
  
  if (!newRdv.value.date) {
    alert('Veuillez sélectionner une date')
    return
  }
  
  if (!newRdv.value.startTime) {
    alert('Veuillez sélectionner une heure de début')
    return
  }
  
  if (!newRdv.value.contactName.trim()) {
    alert('Veuillez saisir un nom de contact')
    return
  }
  
  isSaving.value = true
  
  try {
    const appointmentData = {
      title: newRdv.value.title.trim(),
      date: newRdv.value.date,
      startTime: newRdv.value.startTime,
      endTime: newRdv.value.endTime,
      type: newRdv.value.type,
      status: newRdv.value.status,
      location: newRdv.value.location.trim(),
      description: newRdv.value.description.trim(),
      participant: {
        name: newRdv.value.contactName.trim(),
        email: newRdv.value.contactEmail.trim(),
        phone: newRdv.value.contactPhone.trim()
      }
    }
    
    if (editingEvent.value) {
      // Modification
      appointmentData.id = editingEvent.value.id
      await rendezvousStore.updateAppointment(appointmentData)
      showSuccess('Rendez-vous modifié avec succès !')
    } else {
      // Création
      await rendezvousStore.addAppointment(appointmentData)
      showSuccess('Rendez-vous créé avec succès !')
    }
    
    // Fermer le modal et réinitialiser
    closeModal()
    
  } catch (error) {
    console.error('Erreur lors de l\'enregistrement du rendez-vous:', error)
    alert(`Une erreur est survenue: ${error.message || 'Erreur inconnue'}`)
  } finally {
    isSaving.value = false
  }
}

const deleteEvent = async (eventId) => {
  if (confirm('Êtes-vous sûr de vouloir supprimer ce rendez-vous ?')) {
    try {
      await rendezvousStore.deleteAppointment(eventId)
      showSuccess('Rendez-vous supprimé avec succès !')
    } catch (error) {
      console.error('Erreur lors de la suppression:', error)
      alert('Erreur lors de la suppression du rendez-vous')
    }
  }
}

const closeModal = () => {
  showNewRdvModal.value = false
  editingEvent.value = null
  resetForm()
}

const resetForm = () => {
  const today = new Date().toISOString().split('T')[0]
  newRdv.value = {
    title: '',
    date: today,
    startTime: '09:00',
    endTime: '10:00',
    duration: '1',
    contactName: '',
    contactEmail: '',
    contactPhone: '',
    location: '',
    type: 'consultation',
    status: 'pending',
    description: ''
  }
}

const getEventColorClass = (type) => {
  const typeObj = appointmentTypes.value.find(t => t.value === type)
  if (typeObj) {
    // Convertir bg-blue-500 en bg-blue-100 pour le fond
    const colorClass = typeObj.color.replace('-500', '-100')
    const textClass = typeObj.color.replace('bg-', 'text-').replace('-500', '-800')
    return `${colorClass} ${textClass}`
  }
  return 'bg-gray-100 text-gray-800'
}

const getEventTypeLabel = (type) => {
  const typeObj = appointmentTypes.value.find(t => t.value === type)
  return typeObj ? typeObj.label : type
}

const getStatusLabel = (status) => {
  const labels = {
    confirmed: 'Confirmé',
    pending: 'En attente',
    cancelled: 'Annulé'
  }
  return labels[status] || status
}

const formatSelectedDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatTime = (timeString) => {
  if (!timeString) return ''
  const [hours, minutes] = timeString.split(':')
  return `${hours}h${minutes}`
}

// Chargement initial
onMounted(async () => {
  // Initialiser avec la date d'aujourd'hui
  const today = new Date().toISOString().split('T')[0]
  selectedDate.value = today
  newRdv.value.date = today
  
  // Charger les rendez-vous depuis le store
  await rendezvousStore.fetchAppointments()
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

/* Animation de spin pour le bouton */
@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Style pour le sélecteur de date */
[type="date"]::-webkit-calendar-picker-indicator {
  cursor: pointer;
}
</style>

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

/* Animation de spin pour le bouton */
@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}
</style>