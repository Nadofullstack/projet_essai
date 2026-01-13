<script setup>
import { ref, computed, onMounted, watch } from 'vue'

const props = defineProps({
  initialEvents: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['events-updated'])

// Clé pour le localStorage
const EVENTS_STORAGE_KEY = 'calendar_events'

// États
const currentMonth = ref(new Date().getMonth())
const currentYear = ref(new Date().getFullYear())
const selectedDate = ref(null)
const showEventForm = ref(false)
const events = ref([])

// États pour les modales
const showDeleteModal = ref(false)
const eventToDelete = ref(null)
const showEditForm = ref(false)
const eventToEdit = ref(null)

// Formulaire événement
const eventForm = ref({
  title: '',
  description: '',
  date: '',
  time: '',
  color: '#3B82F6'
})

const monthNames = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre']
const daysOfWeek = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam']

// Options de couleur pour les événements
const colorOptions = [
  { value: '#3B82F6', label: 'Bleu', class: 'bg-blue-500' },
  { value: '#10B981', label: 'Vert', class: 'bg-green-500' },
  { value: '#F59E0B', label: 'Orange', class: 'bg-orange-500' },
  { value: '#EF4444', label: 'Rouge', class: 'bg-red-500' },
  { value: '#8B5CF6', label: 'Violet', class: 'bg-purple-500' },
  { value: '#EC4899', label: 'Rose', class: 'bg-pink-500' }
]

const currentMonthName = computed(() => monthNames[currentMonth.value])

// Fonctions pour le localStorage
const saveEventsToStorage = () => {
  try {
    localStorage.setItem(EVENTS_STORAGE_KEY, JSON.stringify(events.value))
  } catch (error) {
    console.error('Erreur lors de la sauvegarde des événements:', error)
  }
}

const loadEventsFromStorage = () => {
  try {
    const savedEvents = localStorage.getItem(EVENTS_STORAGE_KEY)
    if (savedEvents) {
      events.value = JSON.parse(savedEvents)
    } else {
      events.value = [...props.initialEvents]
    }
  } catch (error) {
    console.error('Erreur lors du chargement des événements:', error)
    events.value = [...props.initialEvents]
  }
}

// Sauvegarder automatiquement quand les événements changent
watch(events, () => {
  saveEventsToStorage()
  emit('events-updated', events.value)
}, { deep: true })

// Fonction pour formater la date sans problèmes de fuseau horaire
const formatDateWithoutTimezone = (date) => {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

// Fonction pour obtenir la date d'aujourd'hui sans fuseau horaire
const getTodayDateString = () => {
  const today = new Date()
  return formatDateWithoutTimezone(today)
}

// Formater la date pour l'affichage
const formatDisplayDate = (dateStr) => {
  const date = new Date(dateStr)
  return date.toLocaleDateString('fr-FR', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

// Trier les événements par date et heure
const sortedEvents = computed(() => {
  return [...events.value].sort((a, b) => {
    // Comparaison par date
    const dateCompare = a.date.localeCompare(b.date)
    if (dateCompare !== 0) return dateCompare
    
    // Si même date, comparer par heure
    if (a.time && b.time) {
      return a.time.localeCompare(b.time)
    }
    
    // Si l'un n'a pas d'heure, le mettre après
    if (!a.time && b.time) return 1
    if (a.time && !b.time) return -1
    
    return 0
  })
})

const calendarDays = computed(() => {
  const firstDay = new Date(currentYear.value, currentMonth.value, 1)
  const lastDay = new Date(currentYear.value, currentMonth.value + 1, 0)
  const startDate = new Date(firstDay)
  startDate.setDate(startDate.getDate() - firstDay.getDay())
  
  const days = []
  const todayStr = getTodayDateString()
  
  for (let i = 0; i < 42; i++) {
    const date = new Date(startDate)
    date.setDate(startDate.getDate() + i)
    
    const dateStr = formatDateWithoutTimezone(date)
    const dayEvents = events.value.filter(event => event.date === dateStr)
    
    days.push({
      dateObj: date,
      dateStr: dateStr,
      day: date.getDate(),
      isCurrentMonth: date.getMonth() === currentMonth.value,
      isToday: dateStr === todayStr,
      events: dayEvents
    })
  }
  
  return days
})

const todayEvents = computed(() => {
  const todayStr = getTodayDateString()
  return events.value.filter(event => event.date === todayStr)
})

const selectedDateEvents = computed(() => {
  if (!selectedDate.value) return []
  return events.value.filter(event => event.date === selectedDate.value)
})

const upcomingEvents = computed(() => {
  const todayStr = getTodayDateString()
  const today = new Date(todayStr)
  
  return sortedEvents.value.filter(event => {
    const eventDate = new Date(event.date)
    return eventDate >= today
  }).slice(0, 5) // Limiter à 5 événements
})

// Navigation du calendrier
const previousMonth = () => {
  if (currentMonth.value === 0) {
    currentMonth.value = 11
    currentYear.value--
  } else {
    currentMonth.value--
  }
}

const nextMonth = () => {
  if (currentMonth.value === 11) {
    currentMonth.value = 0
    currentYear.value++
  } else {
    currentMonth.value++
  }
}

// Sélection d'une date
const selectDate = (day) => {
  selectedDate.value = day.dateStr
}

// Ouvrir le formulaire d'ajout d'événement
const openAddEventForm = () => {
  eventForm.value = {
    title: '',
    description: '',
    date: selectedDate.value || getTodayDateString(),
    time: '09:00',
    color: '#3B82F6'
  }
  showEventForm.value = true
  showEditForm.value = false
}

// Ouvrir le formulaire de modification
const openEditEventForm = (event) => {
  eventToEdit.value = event
  eventForm.value = {
    title: event.title,
    description: event.description || '',
    date: event.date,
    time: event.time || '',
    color: event.color || '#3B82F6'
  }
  showEditForm.value = true
  showEventForm.value = false
}

// Fermer tous les formulaires
const closeAllForms = () => {
  showEventForm.value = false
  showEditForm.value = false
  showDeleteModal.value = false
  eventForm.value = {
    title: '',
    description: '',
    date: '',
    time: '',
    color: '#3B82F6'
  }
  eventToDelete.value = null
  eventToEdit.value = null
}

// Ajouter un événement
const addEvent = () => {
  if (!eventForm.value.title.trim()) {
    alert('Veuillez saisir un titre pour l\'événement')
    return
  }

  const newEvent = {
    id: Date.now().toString(),
    ...eventForm.value,
    createdAt: new Date().toISOString()
  }

  events.value.push(newEvent)
  closeAllForms()
}

// Modifier un événement
const updateEvent = () => {
  if (!eventForm.value.title.trim()) {
    alert('Veuillez saisir un titre pour l\'événement')
    return
  }

  const index = events.value.findIndex(event => event.id === eventToEdit.value.id)
  if (index !== -1) {
    events.value[index] = {
      ...events.value[index],
      ...eventForm.value,
      updatedAt: new Date().toISOString()
    }
  }
  
  closeAllForms()
}

// Ouvrir la modal de suppression
const openDeleteModal = (event) => {
  eventToDelete.value = event
  showDeleteModal.value = true
}

// Confirmer la suppression
const confirmDeleteEvent = () => {
  if (eventToDelete.value) {
    events.value = events.value.filter(event => event.id !== eventToDelete.value.id)
  }
  closeAllForms()
}

// Effacer tous les événements (optionnel)
const clearAllEvents = () => {
  if (confirm('Voulez-vous vraiment supprimer tous les événements ? Cette action est irréversible.')) {
    events.value = []
    localStorage.removeItem(EVENTS_STORAGE_KEY)
  }
}

// Initialiser avec la date d'aujourd'hui
onMounted(() => {
  loadEventsFromStorage()
  selectedDate.value = getTodayDateString()
})
</script>

<template>
  <div class="flex flex-col lg:flex-row gap-6">
    <!-- Calendrier -->
    <div class="lg:w-2/3">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <!-- Header -->
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
            <div class="flex items-center space-x-2">
              <button 
                v-if="events.length > 0"
                @click="clearAllEvents"
                class="text-sm text-gray-500 hover:text-red-600 px-3 py-1.5 border border-gray-300 rounded-lg hover:bg-red-50"
                title="Effacer tous les événements"
              >
                <span class="material-symbols-outlined text-sm mr-1">delete_sweep</span>
                Tout effacer
              </button>
              <button 
                @click="openAddEventForm"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center space-x-2 text-sm font-medium"
              >
                <span class="material-symbols-outlined text-base">add</span>
                <span>Événement</span>
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
              :key="day.dateStr" 
              @click="selectDate(day)"
              :class="[
                'min-h-[60px] border rounded p-1 cursor-pointer transition-colors',
                day.isCurrentMonth ? 'bg-white hover:bg-gray-50' : 'bg-gray-50',
                day.isToday ? 'ring-2 ring-blue-500 border-blue-500' : 'border-gray-200',
                selectedDate === day.dateStr ? 'ring-2 ring-red-500 border-red-500 bg-red-50' : ''
              ]"
            >
              <div class="flex justify-between items-start mb-1">
                <span :class="[
                  'text-sm font-medium',
                  day.isCurrentMonth ? 'text-gray-900' : 'text-gray-400',
                  day.isToday ? 'text-blue-600 font-bold' : '',
                  selectedDate === day.dateStr ? 'text-red-600 font-bold' : ''
                ]">
                  {{ day.day }}
                </span>
                <span v-if="day.events.length > 0" 
                  :style="{ backgroundColor: day.events[0].color }"
                  class="text-white text-xs rounded-full px-1.5 py-0.5">
                  {{ day.events.length }}
                </span>
              </div>
              <div v-if="day.events.length > 0" class="space-y-1">
                <div 
                  v-for="event in day.events.slice(0, 2)" 
                  :key="event.id"
                  :style="{ backgroundColor: event.color + '20', borderLeft: `3px solid ${event.color}` }"
                  class="text-xs p-1 rounded truncate"
                >
                  <span class="font-medium">{{ event.title }}</span>
                  <span v-if="event.time" class="text-gray-600 ml-1">({{ event.time }})</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Événements de la date sélectionnée -->
        <div v-if="selectedDateEvents.length > 0" class="p-4 border-t border-gray-200">
          <h3 class="font-semibold text-gray-900 mb-3">
            Événements du {{ formatDisplayDate(selectedDate) }}
          </h3>
          <div class="space-y-2">
            <div 
              v-for="event in selectedDateEvents" 
              :key="event.id"
              :style="{ borderLeft: `4px solid ${event.color}` }"
              class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100"
            >
              <div class="flex items-start space-x-3 flex-1">
                <div class="mt-1">
                  <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: event.color }"></div>
                </div>
                <div class="flex-1">
                  <p class="font-medium text-gray-900">{{ event.title }}</p>
                  <p v-if="event.description" class="text-sm text-gray-600 mt-1">{{ event.description }}</p>
                  <div class="flex items-center space-x-4 mt-2">
                    <span class="text-xs text-gray-500 flex items-center">
                      <span class="material-symbols-outlined text-xs mr-1">schedule</span>
                      {{ event.time || 'Toute la journée' }}
                    </span>
                    <span class="text-xs text-gray-500 flex items-center">
                      <span class="material-symbols-outlined text-xs mr-1">calendar_today</span>
                      Ajouté le {{ new Date(event.createdAt).toLocaleDateString('fr-FR') }}
                    </span>
                  </div>
                </div>
              </div>
              <div class="flex space-x-2 ml-2">
                <button 
                  @click="openEditEventForm(event)"
                  class="text-blue-500 hover:text-blue-700 p-1"
                  title="Modifier"
                >
                  <span class="material-symbols-outlined text-sm">edit</span>
                </button>
                <button 
                  @click="openDeleteModal(event)"
                  class="text-red-500 hover:text-red-700 p-1"
                  title="Supprimer"
                >
                  <span class="material-symbols-outlined text-sm">delete</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Today's Events -->
        <div v-else-if="todayEvents.length > 0" class="p-4 border-t border-gray-200">
          <h3 class="font-semibold text-gray-900 mb-3">Événements d'aujourd'hui</h3>
          <div class="space-y-2">
            <div 
              v-for="event in todayEvents" 
              :key="event.id"
              :style="{ borderLeft: `4px solid ${event.color}` }"
              class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100"
            >
              <div class="flex items-center space-x-3 flex-1">
                <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: event.color }"></div>
                <div class="flex-1">
                  <p class="text-sm font-medium text-gray-900">{{ event.title }}</p>
                  <p class="text-xs text-gray-500">{{ event.time || 'Toute la journée' }}</p>
                </div>
              </div>
              <div class="flex space-x-2 ml-2">
                <button 
                  @click="openEditEventForm(event)"
                  class="text-blue-500 hover:text-blue-700 p-1"
                  title="Modifier"
                >
                  <span class="material-symbols-outlined text-sm">edit</span>
                </button>
                <button 
                  @click="openDeleteModal(event)"
                  class="text-red-500 hover:text-red-700 p-1"
                  title="Supprimer"
                >
                  <span class="material-symbols-outlined text-sm">delete</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Formulaire d'ajout/modification d'événement -->
    <div v-if="showEventForm || showEditForm" class="lg:w-1/3">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 sticky top-4">
        <div class="p-4 border-b border-gray-200">
          <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-900">
              {{ showEditForm ? 'Modifier l\'Événement' : 'Nouvel Événement' }}
            </h2>
            <button 
              @click="closeAllForms"
              class="text-gray-400 hover:text-gray-600"
            >
              <span class="material-symbols-outlined">close</span>
            </button>
          </div>
        </div>

        <div class="p-4">
          <form @submit.prevent="showEditForm ? updateEvent() : addEvent()" class="space-y-4">
            <!-- Titre -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Titre *
              </label>
              <input
                v-model="eventForm.title"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Titre de l'événement"
              />
            </div>

            <!-- Description -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Description
              </label>
              <textarea
                v-model="eventForm.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Description de l'événement (optionnel)"
              ></textarea>
            </div>

            <!-- Date et Heure -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Date *
                </label>
                <input
                  v-model="eventForm.date"
                  type="date"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Heure
                </label>
                <input
                  v-model="eventForm.time"
                  type="time"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
              </div>
            </div>

            <!-- Couleur -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Couleur
              </label>
              <div class="flex space-x-2">
                <button
                  v-for="color in colorOptions"
                  :key="color.value"
                  type="button"
                  @click="eventForm.color = color.value"
                  :class="[
                    'w-8 h-8 rounded-full border-2',
                    eventForm.color === color.value ? 'border-gray-400' : 'border-transparent'
                  ]"
                  :style="{ backgroundColor: color.value }"
                  :title="color.label"
                >
                  <span v-if="eventForm.color === color.value" class="material-symbols-outlined text-white text-sm">
                    check
                  </span>
                </button>
              </div>
            </div>

            <!-- Boutons -->
            <div class="flex space-x-3 pt-4">
              <button
                type="button"
                @click="closeAllForms"
                class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
              >
                Annuler
              </button>
              <button
                type="submit"
                :class="[
                  'flex-1 px-4 py-2 rounded-lg font-medium',
                  showEditForm 
                    ? 'bg-green-600 text-white hover:bg-green-700' 
                    : 'bg-blue-600 text-white hover:bg-blue-700'
                ]"
              >
                {{ showEditForm ? 'Modifier l\'événement' : 'Ajouter l\'événement' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Panel latéral (quand le formulaire n'est pas ouvert) -->
    <div v-else class="lg:w-1/3">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 sticky top-4">
        <div class="p-4 border-b border-gray-200">
          <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-900">
              Événements à venir
            </h2>
            <span class="text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded">
              {{ events.length }} événement(s)
            </span>
          </div>
        </div>
        <div class="p-4">
          <div v-if="events.length === 0" class="text-center py-8">
            <span class="material-symbols-outlined text-gray-400 text-4xl mb-2">event</span>
            <p class="text-gray-500">Aucun événement programmé</p>
            <p class="text-sm text-gray-400 mt-1">Cliquez sur "Événement" pour en créer un</p>
          </div>
          <div v-else class="space-y-3">
            <div 
              v-for="event in upcomingEvents" 
              :key="event.id"
              :style="{ borderLeft: `4px solid ${event.color}` }"
              class="p-3 bg-gray-50 rounded-lg hover:bg-gray-100 cursor-pointer"
              @click="selectDate({ dateStr: event.date })"
            >
              <div class="flex items-start space-x-2">
                <div class="w-2 h-2 rounded-full mt-1.5" :style="{ backgroundColor: event.color }"></div>
                <div class="flex-1">
                  <p class="font-medium text-gray-900">{{ event.title }}</p>
                  <p class="text-xs text-gray-500 mt-1 flex items-center">
                    <span class="material-symbols-outlined text-xs mr-1">calendar_today</span>
                    {{ formatDisplayDate(event.date) }}
                    <span v-if="event.time" class="ml-2">
                      <span class="material-symbols-outlined text-xs mr-1">schedule</span>
                      {{ event.time }}
                    </span>
                  </p>
                </div>
              </div>
            </div>
            <div v-if="events.length > 5" class="text-center pt-2">
              <button @click="selectedDate = null" class="text-blue-600 text-sm hover:text-blue-800 font-medium">
                Voir tous les événements ({{ events.length }})
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de confirmation de suppression -->
  <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-md w-full">
      <div class="p-6">
        <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
          <span class="material-symbols-outlined text-red-600">warning</span>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">
          Confirmer la suppression
        </h3>
        <p class="text-gray-600 text-center mb-6">
          Êtes-vous sûr de vouloir supprimer l'événement "<span class="font-medium">{{ eventToDelete?.title }}</span>" ?
        </p>
        <div class="flex space-x-3">
          <button
            @click="closeAllForms"
            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium"
          >
            Annuler
          </button>
          <button
            @click="confirmDeleteEvent"
            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium"
          >
            Supprimer
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.transition-colors {
  transition: all 0.2s ease;
}

.grid {
  display: grid;
}

.grid-cols-7 {
  grid-template-columns: repeat(7, minmax(0, 1fr));
}

.gap-1 {
  gap: 0.25rem;
}

/* Style pour améliorer l'apparence du focus rouge */
.ring-red-500 {
  --tw-ring-color: rgba(239, 68, 68, 0.5);
}

/* Style pour le texte dans les cases d'événements */
.text-xs {
  font-size: 0.75rem;
}

/* Animation pour la modal */
.fixed {
  position: fixed;
}

.inset-0 {
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
}

.z-50 {
  z-index: 50;
}
</style>