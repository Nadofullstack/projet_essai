<script setup>
import { ref, computed, onMounted } from 'vue'
import { useEvenementsStore } from '@/stores/evenements'
import EventForm from '@/components/Dashboard/Features/EventForm.vue'
import MainLayout from '@/components/Dashboard/Layout/MainLayout.vue'
const evenementsStore = useEvenementsStore()

// État local
const currentView = ref('grid') // grid, list, calendar
const selectedEvent = ref(null)
const showEventModal = ref(false)
const searchQuery = ref('')
const selectedCategory = ref('all')
const selectedDate = ref('')

// Données du store
const events = computed(() => evenementsStore.events)
const loading = computed(() => evenementsStore.loading)
const categories = computed(() => evenementsStore.categories)

// Filtres
const filteredEvents = computed(() => {
  let filtered = events.value

  // Filtre par recherche
  if (searchQuery.value) {
    filtered = filtered.filter(event => 
      event.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      event.description.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }

  // Filtre par catégorie
  if (selectedCategory.value !== 'all') {
    filtered = filtered.filter(event => event.category === selectedCategory.value)
  }

  // Filtre par date
  if (selectedDate.value) {
    filtered = filtered.filter(event => event.date === selectedDate.value)
  }

  return filtered
})

// Méthodes
const openEventModal = (event = null) => {
  selectedEvent.value = event
  showEventModal.value = true
}

const closeEventModal = () => {
  showEventModal.value = false
  selectedEvent.value = null
}

const saveEvent = (eventData) => {
  if (selectedEvent.value) {
    evenementsStore.updateEvent({ ...eventData, id: selectedEvent.value.id })
  } else {
    evenementsStore.addEvent(eventData)
  }
  closeEventModal()
}

const deleteEvent = (eventId) => {
  if (confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')) {
    evenementsStore.deleteEvent(eventId)
  }
}

const toggleRegistration = (eventId) => {
  evenementsStore.toggleRegistration(eventId)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

const formatTime = (time) => {
  return time.substring(0, 5)
}

const getCategoryColor = (category) => {
  const colors = {
    conference: 'bg-blue-100 text-blue-800',
    workshop: 'bg-green-100 text-green-800',
    meeting: 'bg-purple-100 text-purple-800',
    social: 'bg-yellow-100 text-yellow-800',
    other: 'bg-gray-100 text-gray-800'
  }
  return colors[category] || colors.other
}

// Chargement initial
onMounted(() => {
  evenementsStore.fetchEvents()
})
</script>

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
              <h1 class="text-2xl font-bold text-gray-900">Événements</h1>
            </div>
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Événements</h1>
          <p class="text-gray-600 mt-2">Découvrez et participez aux événements à venir</p>
        </div>
        <button 
          @click="openEventModal"
          class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center space-x-2"
        >
          <span class="material-symbols-outlined">add</span>
          <span>Nouvel événement</span>
        </button>
      </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Recherche -->
        <div class="relative">
          <span class="material-symbols-outlined absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
            search
          </span>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Rechercher un événement..."
            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <!-- Filtre catégorie -->
        <select
          v-model="selectedCategory"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="all">Toutes les catégories</option>
          <option v-for="category in categories" :key="category.value" :value="category.value">
            {{ category.label }}
          </option>
        </select>

        <!-- Filtre date -->
        <input
          v-model="selectedDate"
          type="date"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />

        <!-- Vue -->
        <div class="flex space-x-2">
          <button
            v-for="view in ['grid', 'list', 'calendar']"
            :key="view"
            @click="currentView = view"
            :class="[
              'flex-1 p-2 rounded-lg border transition-colors',
              currentView === view 
                ? 'bg-blue-600 text-white border-blue-600' 
                : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
            ]"
          >
            <span class="material-symbols-outlined">
              {{ view === 'grid' ? 'grid_view' : view === 'list' ? 'list' : 'calendar_month' }}
            </span>
          </button>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-12">
      <span class="material-symbols-outlined animate-spin text-4xl text-blue-600">refresh</span>
      <p class="mt-4 text-gray-600">Chargement des événements...</p>
    </div>

    <!-- Contenu principal -->
    <div v-else>
      <!-- Vue Grille -->
      <div v-if="currentView === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div 
          v-for="event in filteredEvents" 
          :key="event.id"
          class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow"
        >
          <div class="h-48 bg-gradient-to-br from-blue-400 to-indigo-600 flex items-center justify-center">
            <span class="material-symbols-outlined text-white text-6xl">event</span>
          </div>
          
          <div class="p-6">
            <div class="flex justify-between items-start mb-3">
              <h3 class="text-lg font-semibold text-gray-900">{{ event.title }}</h3>
              <span :class="['px-2 py-1 rounded-full text-xs font-medium', getCategoryColor(event.category)]">
                {{ categories.find(c => c.value === event.category)?.label }}
              </span>
            </div>
            
            <p class="text-gray-600 mb-4 line-clamp-2">{{ event.description }}</p>
            
            <div class="space-y-2 text-sm text-gray-500 mb-4">
              <div class="flex items-center">
                <span class="material-symbols-outlined mr-2">calendar_today</span>
                {{ formatDate(event.date) }}
              </div>
              <div class="flex items-center">
                <span class="material-symbols-outlined mr-2">schedule</span>
                {{ formatTime(event.startTime) }} - {{ formatTime(event.endTime) }}
              </div>
              <div class="flex items-center">
                <span class="material-symbols-outlined mr-2">location_on</span>
                {{ event.location }}
              </div>
              <div class="flex items-center">
                <span class="material-symbols-outlined mr-2">people</span>
                {{ event.attendees.length }} / {{ event.maxAttendees }} participants
              </div>
            </div>
            
            <div class="flex space-x-2">
              <button 
                @click="toggleRegistration(event.id)"
                :class="[
                  'flex-1 px-3 py-2 rounded-lg font-medium transition-colors',
                  event.isRegistered 
                    ? 'bg-red-100 text-red-700 hover:bg-red-200' 
                    : 'bg-blue-600 text-white hover:bg-blue-700'
                ]"
              >
                {{ event.isRegistered ? 'Se désinscrire' : 'S\'inscrire' }}
              </button>
              <button 
                @click="openEventModal(event)"
                class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50"
              >
                <span class="material-symbols-outlined">edit</span>
              </button>
              <button 
                @click="deleteEvent(event.id)"
                class="p-2 border border-red-300 rounded-lg hover:bg-red-50 text-red-600"
              >
                <span class="material-symbols-outlined">delete</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Vue Liste -->
      <div v-else-if="currentView === 'list'" class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="divide-y divide-gray-200">
          <div 
            v-for="event in filteredEvents" 
            :key="event.id"
            class="p-6 hover:bg-gray-50 transition-colors"
          >
            <div class="flex items-center justify-between">
              <div class="flex-1">
                <div class="flex items-center space-x-3 mb-2">
                  <h3 class="text-lg font-semibold text-gray-900">{{ event.title }}</h3>
                  <span :class="['px-2 py-1 rounded-full text-xs font-medium', getCategoryColor(event.category)]">
                    {{ categories.find(c => c.value === event.category)?.label }}
                  </span>
                  <span v-if="event.isRegistered" class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">
                    Inscrit
                  </span>
                </div>
                <p class="text-gray-600 mb-2">{{ event.description }}</p>
                <div class="flex items-center space-x-4 text-sm text-gray-500">
                  <span>{{ formatDate(event.date) }}</span>
                  <span>{{ formatTime(event.startTime) }} - {{ formatTime(event.endTime) }}</span>
                  <span>{{ event.location }}</span>
                  <span>{{ event.attendees.length }} / {{ event.maxAttendees }} participants</span>
                </div>
              </div>
              <div class="flex space-x-2 ml-4">
                <button 
                  @click="toggleRegistration(event.id)"
                  :class="[
                    'px-3 py-2 rounded-lg font-medium transition-colors',
                    event.isRegistered 
                      ? 'bg-red-100 text-red-700 hover:bg-red-200' 
                      : 'bg-blue-600 text-white hover:bg-blue-700'
                  ]"
                >
                  {{ event.isRegistered ? 'Se désinscrire' : 'S\'inscrire' }}
                </button>
                <button 
                  @click="openEventModal(event)"
                  class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                >
                  <span class="material-symbols-outlined">edit</span>
                </button>
                <button 
                  @click="deleteEvent(event.id)"
                  class="p-2 border border-red-300 rounded-lg hover:bg-red-50 text-red-600"
                >
                  <span class="material-symbols-outlined">delete</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Vue Calendrier -->
      <div v-else-if="currentView === 'calendar'" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-center text-gray-500 py-12">
          Vue calendrier en cours de développement...
        </p>
      </div>

      <!-- Aucun résultat -->
      <div v-if="filteredEvents.length === 0 && !loading" class="text-center py-12">
        <span class="material-symbols-outlined text-6xl text-gray-400">event_busy</span>
        <p class="mt-4 text-gray-600">Aucun événement trouvé</p>
      </div>
    </div>

    <!-- Modal Événement -->
    <div 
      v-if="showEventModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click="closeEventModal"
    >
      <div 
        class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto"
        @click.stop
      >
        <!-- Header -->
        <div class="p-6 border-b border-gray-200">
          <div class="flex justify-between items-center">
            <h3 class="text-xl font-semibold text-gray-900">
              {{ selectedEvent ? 'Modifier l\'événement' : 'Nouvel événement' }}
            </h3>
            <button 
              @click="closeEventModal"
              class="p-2 rounded-lg hover:bg-gray-100"
            >
              <span class="material-symbols-outlined">close</span>
            </button>
          </div>
        </div>

        <!-- Form -->
        <div class="p-6">
          <EventForm 
            :event="selectedEvent"
            @save="saveEvent"
            @cancel="closeEventModal"
          />
        </div>
      </div>
    </div>
    </div>
    </div>
    </div>
  </MainLayout>
</template>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-clamp: 2;
  -webkit-line-clamp: 2;
}

.grid {
  display: grid;
}

.grid-cols-1 {
  grid-template-columns: repeat(1, minmax(0, 1fr));
}

.grid-cols-2 {
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.grid-cols-3 {
  grid-template-columns: repeat(3, minmax(0, 1fr));
}

.grid-cols-4 {
  grid-template-columns: repeat(4, minmax(0, 1fr));
}

.divide-y > * + * {
  border-top: 1px solid #e5e7eb;
}

.divide-gray-200 > * + * {
  border-top-color: #e5e7eb;
}

.max-h-90vh {
  max-height: 90vh;
}

.overflow-y-auto {
  overflow-y: auto;
}
</style>
