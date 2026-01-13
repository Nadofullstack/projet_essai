<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAudioStore } from '@/stores/audio'
import MainLayout from '@/components/Dashboard/Layout/MainLayout.vue'

const audioStore = useAudioStore()

// État local
const currentView = ref('list') // list, grid, player
const selectedAudio = ref(null)
const showRecordModal = ref(false)
const searchQuery = ref('')
const selectedCategory = ref('all')
const sortBy = ref('date') // date, name, duration, size

// États pour l'enregistrement
const isRecording = ref(false)
const isPaused = ref(false)
const recordingTime = ref(0)
const recordingTimer = ref(null)

// Données du store
const audioFiles = computed(() => audioStore.audioFiles)
const loading = computed(() => audioStore.loading)
const categories = computed(() => audioStore.categories)
const storageStats = computed(() => audioStore.storageStats)

// Filtres et tri
const filteredAudioFiles = computed(() => {
  let filtered = audioFiles.value

  // Filtre par recherche
  if (searchQuery.value) {
    filtered = filtered.filter(audio => 
      audio.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      audio.description.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }

  // Filtre par catégorie
  if (selectedCategory.value !== 'all') {
    filtered = filtered.filter(audio => audio.category === selectedCategory.value)
  }

  // Tri
  filtered.sort((a, b) => {
    switch (sortBy.value) {
      case 'name':
        return a.name.localeCompare(b.name)
      case 'duration':
        return b.duration - a.duration
      case 'size':
        return b.size - a.size
      case 'date':
      default:
        return new Date(b.createdAt) - new Date(a.createdAt)
    }
  })

  return filtered
})

// Méthodes
const formatDuration = (seconds) => {
  const mins = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${mins}:${secs.toString().padStart(2, '0')}`
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const playAudio = (audio) => {
  selectedAudio.value = audio
  audioStore.playAudio(audio.id)
}

const pauseAudio = () => {
  audioStore.pauseAudio()
}

const stopAudio = () => {
  audioStore.stopAudio()
  selectedAudio.value = null
}

const deleteAudio = async (audioId) => {
  if (confirm('Êtes-vous sûr de vouloir supprimer cet enregistrement ?')) {
    await audioStore.deleteAudio(audioId)
    if (selectedAudio.value?.id === audioId) {
      selectedAudio.value = null
    }
  }
}

const startRecording = () => {
  isRecording.value = true
  recordingTime.value = 0
  
  recordingTimer.value = setInterval(() => {
    recordingTime.value++
  }, 1000)
  
  audioStore.startRecording()
}

const stopRecording = async () => {
  isRecording.value = false
  isPaused.value = false
  
  if (recordingTimer.value) {
    clearInterval(recordingTimer.value)
    recordingTimer.value = null
  }
  
  await audioStore.stopRecording()
  recordingTime.value = 0
}

const pauseRecording = () => {
  isPaused.value = !isPaused.value
  
  if (isPaused.value) {
    audioStore.pauseRecording()
  } else {
    audioStore.resumeRecording()
  }
}

const getCategoryIcon = (category) => {
  const icons = {
    voice: 'mic',
    music: 'music_note',
    podcast: 'podcasts',
    meeting: 'groups',
    other: 'audio_file'
  }
  return icons[category] || icons.other
}

const getCategoryColor = (category) => {
  const colors = {
    voice: 'bg-blue-100 text-blue-800',
    music: 'bg-purple-100 text-purple-800',
    podcast: 'bg-green-100 text-green-800',
    meeting: 'bg-orange-100 text-orange-800',
    other: 'bg-gray-100 text-gray-800'
  }
  return colors[category] || colors.other
}

// Chargement initial
onMounted(() => {
  audioStore.fetchAudioFiles()
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
              <h1 class="text-2xl font-bold text-gray-900">Gestion Audio</h1>
            </div>
            <div class="flex items-center space-x-2">
            </div>
          </div>
        </div>
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Gestion Audio</h1>
          <p class="text-gray-600 mt-2">Enregistrez, gérez et écoutez vos fichiers audio</p>
        </div>
        <button 
          @click="showRecordModal = true"
          class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 flex items-center space-x-2"
        >
          <span class="material-symbols-outlined">mic</span>
          <span>Nouvel enregistrement</span>
        </button>
      </div>
    </div>

    <!-- Stats de stockage -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Total des fichiers</p>
            <p class="text-2xl font-bold text-gray-900">{{ storageStats.totalFiles }}</p>
          </div>
          <span class="material-symbols-outlined text-3xl text-blue-600">audio_file</span>
        </div>
      </div>
      
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Durée totale</p>
            <p class="text-2xl font-bold text-gray-900">{{ formatDuration(storageStats.totalDuration) }}</p>
          </div>
          <span class="material-symbols-outlined text-3xl text-green-600">schedule</span>
        </div>
      </div>
      
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Espace utilisé</p>
            <p class="text-2xl font-bold text-gray-900">{{ formatFileSize(storageStats.usedSpace) }}</p>
          </div>
          <span class="material-symbols-outlined text-3xl text-purple-600">storage</span>
        </div>
      </div>
      
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Espace disponible</p>
            <p class="text-2xl font-bold text-gray-900">{{ formatFileSize(storageStats.availableSpace) }}</p>
          </div>
          <span class="material-symbols-outlined text-3xl text-orange-600">cloud</span>
        </div>
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
            placeholder="Rechercher un fichier audio..."
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

        <!-- Tri -->
        <select
          v-model="sortBy"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="date">Plus récents</option>
          <option value="name">Nom (A-Z)</option>
          <option value="duration">Durée</option>
          <option value="size">Taille</option>
        </select>

        <!-- Vue -->
        <div class="flex space-x-2">
          <button
            v-for="view in ['list', 'grid']"
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
              {{ view === 'list' ? 'list' : 'grid_view' }}
            </span>
          </button>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-12">
      <span class="material-symbols-outlined animate-spin text-4xl text-blue-600">refresh</span>
      <p class="mt-4 text-gray-600">Chargement des fichiers audio...</p>
    </div>

    <!-- Contenu principal -->
    <div v-else>
      <!-- Vue Liste -->
      <div v-if="currentView === 'list'" class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="divide-y divide-gray-200">
          <div 
            v-for="audio in filteredAudioFiles" 
            :key="audio.id"
            class="p-4 hover:bg-gray-50 transition-colors"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4 flex-1">
                <!-- Icône audio -->
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                  <span class="material-symbols-outlined text-blue-600">
                    {{ getCategoryIcon(audio.category) }}
                  </span>
                </div>
                
                <!-- Informations -->
                <div class="flex-1">
                  <div class="flex items-center space-x-2 mb-1">
                    <h3 class="font-semibold text-gray-900">{{ audio.name }}</h3>
                    <span :class="['px-2 py-1 rounded-full text-xs font-medium', getCategoryColor(audio.category)]">
                      {{ categories.find(c => c.value === audio.category)?.label }}
                    </span>
                  </div>
                  <p class="text-sm text-gray-600 mb-1">{{ audio.description }}</p>
                  <div class="flex items-center space-x-4 text-xs text-gray-500">
                    <span>{{ formatDuration(audio.duration) }}</span>
                    <span>{{ formatFileSize(audio.size) }}</span>
                    <span>{{ formatDate(audio.createdAt) }}</span>
                  </div>
                </div>
              </div>
              
              <!-- Actions -->
              <div class="flex items-center space-x-2">
                <button 
                  @click="playAudio(audio)"
                  class="p-2 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200"
                >
                  <span class="material-symbols-outlined">play_arrow</span>
                </button>
                <button 
                  @click="deleteAudio(audio.id)"
                  class="p-2 border border-red-300 rounded-lg hover:bg-red-50 text-red-600"
                >
                  <span class="material-symbols-outlined">delete</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Vue Grille -->
      <div v-else-if="currentView === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <div 
          v-for="audio in filteredAudioFiles" 
          :key="audio.id"
          class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow"
        >
          <div class="h-32 bg-gradient-to-br from-blue-400 to-indigo-600 flex items-center justify-center">
            <span class="material-symbols-outlined text-white text-4xl">
              {{ getCategoryIcon(audio.category) }}
            </span>
          </div>
          
          <div class="p-4">
            <div class="flex justify-between items-start mb-2">
              <h3 class="font-semibold text-gray-900 truncate">{{ audio.name }}</h3>
              <span :class="['px-2 py-1 rounded-full text-xs font-medium', getCategoryColor(audio.category)]">
                {{ categories.find(c => c.value === audio.category)?.label }}
              </span>
            </div>
            
            <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ audio.description }}</p>
            
            <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
              <span>{{ formatDuration(audio.duration) }}</span>
              <span>{{ formatFileSize(audio.size) }}</span>
            </div>
            
            <div class="flex space-x-2">
              <button 
                @click="playAudio(audio)"
                class="flex-1 p-2 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200"
              >
                <span class="material-symbols-outlined">play_arrow</span>
              </button>
              <button 
                @click="deleteAudio(audio.id)"
                class="p-2 border border-red-300 rounded-lg hover:bg-red-50 text-red-600"
              >
                <span class="material-symbols-outlined">delete</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Aucun résultat -->
      <div v-if="filteredAudioFiles.length === 0 && !loading" class="text-center py-12">
        <span class="material-symbols-outlined text-6xl text-gray-400">audio_file_off</span>
        <p class="mt-4 text-gray-600">Aucun fichier audio trouvé</p>
      </div>
    </div>

    <!-- Modal d'enregistrement -->
    <div 
      v-if="showRecordModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click="showRecordModal = false"
    >
      <div 
        class="bg-white rounded-xl shadow-xl w-full max-w-md"
        @click.stop
      >
        <!-- Header -->
        <div class="p-6 border-b border-gray-200">
          <div class="flex justify-between items-center">
            <h3 class="text-xl font-semibold text-gray-900">Nouvel enregistrement</h3>
            <button 
              @click="showRecordModal = false"
              class="p-2 rounded-lg hover:bg-gray-100"
            >
              <span class="material-symbols-outlined">close</span>
            </button>
          </div>
        </div>

        <!-- Contenu -->
        <div class="p-6">
          <!-- Visualisation -->
          <div class="bg-gray-900 rounded-lg p-4 mb-4 h-32 flex items-center justify-center">
            <div v-if="isRecording" class="flex space-x-1">
              <div v-for="i in 20" :key="i" class="w-1 bg-red-400 rounded-full animate-pulse" 
                   :style="{ height: `${Math.random() * 80 + 20}%` }"></div>
            </div>
            <span v-else class="material-symbols-outlined text-gray-400 text-4xl">mic</span>
          </div>
          
          <!-- Timer -->
          <div class="text-2xl font-mono text-center text-gray-900 mb-4">
            {{ formatDuration(recordingTime) }}
          </div>
          
          <!-- Contrôles -->
          <div class="flex justify-center items-center space-x-4">
            <button 
              v-if="isRecording"
              @click="pauseRecording"
              class="p-3 bg-yellow-500 text-white rounded-full hover:bg-yellow-600"
            >
              <span class="material-symbols-outlined">
                {{ isPaused ? 'play_arrow' : 'pause' }}
              </span>
            </button>
            
            <button 
              @click="isRecording ? stopRecording() : startRecording()"
              :class="[
                'p-4 rounded-full text-white',
                isRecording ? 'bg-red-600 hover:bg-red-700' : 'bg-red-600 hover:bg-red-700'
              ]"
            >
              <span class="material-symbols-outlined text-2xl">
                {{ isRecording ? 'stop' : 'mic' }}
              </span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Lecteur audio (si sélectionné) -->
    <div 
      v-if="selectedAudio"
      class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 shadow-lg"
    >
      <div class="max-w-4xl mx-auto flex items-center space-x-4">
        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
          <span class="material-symbols-outlined text-blue-600">
            {{ getCategoryIcon(selectedAudio.category) }}
          </span>
        </div>
        
        <div class="flex-1">
          <h4 class="font-semibold text-gray-900">{{ selectedAudio.name }}</h4>
          <div class="flex items-center space-x-4 text-sm text-gray-500">
            <span>{{ formatDuration(selectedAudio.duration) }}</span>
            <span>{{ formatFileSize(selectedAudio.size) }}</span>
          </div>
        </div>
        
        <div class="flex items-center space-x-2">
          <button 
            @click="pauseAudio"
            class="p-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
          >
            <span class="material-symbols-outlined">pause</span>
          </button>
          <button 
            @click="stopAudio"
            class="p-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200"
          >
            <span class="material-symbols-outlined">stop</span>
          </button>
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

.transition-shadow {
  transition: box-shadow 0.3s ease;
}

.transition-shadow:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.transition-colors {
  transition: background-color 0.2s ease;
}
</style>
