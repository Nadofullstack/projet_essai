import { defineStore } from 'pinia'
import { ref,computed } from 'vue'

export const useAudioStore = defineStore('audio', () => {
  // État
  const audioFiles = ref([])
  const loading = ref(false)
  const error = ref(null)
  const currentAudio = ref(null)
  const isPlaying = ref(false)
  const isRecording = ref(false)
  const recordingData = ref(null)

  // Catégories disponibles
  const categories = ref([
    { value: 'voice', label: 'Voix' },
    { value: 'music', label: 'Musique' },
    { value: 'podcast', label: 'Podcast' },
    { value: 'meeting', label: 'Réunion' },
    { value: 'other', label: 'Autre' }
  ])

  // Statistiques de stockage
  const storageStats = computed(() => {
    const totalFiles = audioFiles.value.length
    const totalDuration = audioFiles.value.reduce((sum, audio) => sum + audio.duration, 0)
    const usedSpace = audioFiles.value.reduce((sum, audio) => sum + audio.size, 0)
    const maxSpace = 1024 * 1024 * 1024 // 1GB
    const availableSpace = maxSpace - usedSpace

    return {
      totalFiles,
      totalDuration,
      usedSpace,
      availableSpace,
      maxSpace
    }
  })

  // Actions
  const fetchAudioFiles = async () => {
    loading.value = true
    error.value = null

    try {
      // Simulation d'appel API
      await new Promise(resolve => setTimeout(resolve, 1000))
      
      // Données mock
      audioFiles.value = [
        {
          id: 1,
          name: 'Présentation Projet Alpha',
          description: 'Enregistrement de la présentation du nouveau projet',
          duration: 1800, // 30 minutes
          size: 5242880, // 5MB
          category: 'voice',
          createdAt: '2024-01-10T10:30:00Z',
          url: '/audio/presentation_alpha.mp3',
          waveform: [0.1, 0.3, 0.2, 0.5, 0.4, 0.6, 0.3, 0.2, 0.4, 0.5]
        },
        {
          id: 2,
          name: 'Réunion Équipe Q1',
          description: 'Compte-rendu de la réunion trimestrielle',
          duration: 3600, // 1 heure
          size: 10485760, // 10MB
          category: 'meeting',
          createdAt: '2024-01-08T14:00:00Z',
          url: '/audio/reunion_q1.mp3',
          waveform: [0.2, 0.4, 0.3, 0.6, 0.5, 0.7, 0.4, 0.3, 0.5, 0.6]
        },
        {
          id: 3,
          name: 'Podcast Tech News',
          description: 'Dernier épisode sur les innovations technologiques',
          duration: 2700, // 45 minutes
          size: 7864320, // 7.5MB
          category: 'podcast',
          createdAt: '2024-01-05T09:00:00Z',
          url: '/audio/tech_news.mp3',
          waveform: [0.3, 0.5, 0.4, 0.7, 0.6, 0.8, 0.5, 0.4, 0.6, 0.7]
        },
        {
          id: 4,
          name: 'Démo Musicale',
          description: 'Enregistrement de la nouvelle démo musicale',
          duration: 240, // 4 minutes
          size: 655360, // 640KB
          category: 'music',
          createdAt: '2024-01-03T16:30:00Z',
          url: '/audio/demo_musicale.mp3',
          waveform: [0.4, 0.6, 0.5, 0.8, 0.7, 0.9, 0.6, 0.5, 0.7, 0.8]
        }
      ]
    } catch (err) {
      error.value = 'Erreur lors du chargement des fichiers audio'
      console.error('Error fetching audio files:', err)
    } finally {
      loading.value = false
    }
  }

  const addAudioFile = async (audioData) => {
    try {
      // Simulation d'appel API
      await new Promise(resolve => setTimeout(resolve, 500))
      
      const newAudio = {
        id: Date.now(),
        ...audioData,
        createdAt: new Date().toISOString(),
        waveform: generateWaveform()
      }
      
      audioFiles.value.unshift(newAudio)
      return newAudio
    } catch (err) {
      error.value = 'Erreur lors de l\'ajout du fichier audio'
      console.error('Error adding audio file:', err)
      throw err
    }
  }

  const updateAudioFile = async (audioData) => {
    try {
      // Simulation d'appel API
      await new Promise(resolve => setTimeout(resolve, 500))
      
      const index = audioFiles.value.findIndex(audio => audio.id === audioData.id)
      if (index !== -1) {
        audioFiles.value[index] = { ...audioFiles.value[index], ...audioData }
      }
      return audioFiles.value[index]
    } catch (err) {
      error.value = 'Erreur lors de la mise à jour du fichier audio'
      console.error('Error updating audio file:', err)
      throw err
    }
  }

  const deleteAudio = async (audioId) => {
    try {
      // Simulation d'appel API
      await new Promise(resolve => setTimeout(resolve, 500))
      
      const index = audioFiles.value.findIndex(audio => audio.id === audioId)
      if (index !== -1) {
        audioFiles.value.splice(index, 1)
      }
      
      // Si c'était l'audio courant, on l'arrête
      if (currentAudio.value?.id === audioId) {
        stopAudio()
      }
    } catch (err) {
      error.value = 'Erreur lors de la suppression du fichier audio'
      console.error('Error deleting audio:', err)
      throw err
    }
  }

  const playAudio = (audioId) => {
    const audio = audioFiles.value.find(a => a.id === audioId)
    if (audio) {
      currentAudio.value = audio
      isPlaying.value = true
      // Ici vous pourriez utiliser l'API Web Audio pour jouer réellement le son
      console.log('Playing audio:', audio.name)
    }
  }

  const pauseAudio = () => {
    isPlaying.value = false
    if (currentAudio.value) {
      console.log('Paused audio:', currentAudio.value.name)
    }
  }

  const stopAudio = () => {
    isPlaying.value = false
    currentAudio.value = null
    console.log('Audio stopped')
  }

  const startRecording = () => {
    isRecording.value = true
    recordingData.value = {
      startTime: Date.now(),
      chunks: []
    }
    // Ici vous pourriez utiliser l'API MediaRecorder pour enregistrer réellement
    console.log('Recording started')
  }

  const pauseRecording = () => {
    if (isRecording.value) {
      // Logique pour mettre en pause l'enregistrement
      console.log('Recording paused')
    }
  }

  const resumeRecording = () => {
    if (isRecording.value) {
      // Logique pour reprendre l'enregistrement
      console.log('Recording resumed')
    }
  }

  const stopRecording = async () => {
    if (!isRecording.value) return

    isRecording.value = false
    
    if (recordingData.value) {
      const duration = Math.floor((Date.now() - recordingData.value.startTime) / 1000)
      
      // Créer un nouvel enregistrement
      const newAudio = {
        name: `Enregistrement ${new Date().toLocaleString('fr-FR')}`,
        description: 'Enregistrement audio créé depuis l\'interface',
        duration,
        size: Math.floor(duration * 1024), // Simulation de taille
        category: 'voice',
        url: `/audio/recording_${Date.now()}.mp3`,
        waveform: generateWaveform()
      }
      
      await addAudioFile(newAudio)
      recordingData.value = null
    }
    
    console.log('Recording stopped and saved')
  }

  // Fonctions utilitaires
  const generateWaveform = () => {
    const points = 50
    return Array.from({ length: points }, () => Math.random() * 0.8 + 0.1)
  }

  const getAudioById = (audioId) => {
    return audioFiles.value.find(audio => audio.id === audioId)
  }

  const getAudioByCategory = (category) => {
    return audioFiles.value.filter(audio => audio.category === category)
  }

  const searchAudio = (query) => {
    const searchTerm = query.toLowerCase()
    return audioFiles.value.filter(audio => 
      audio.name.toLowerCase().includes(searchTerm) ||
      audio.description.toLowerCase().includes(searchTerm)
    )
  }

  return {
    // État
    audioFiles,
    loading,
    error,
    currentAudio,
    isPlaying,
    isRecording,
    categories,
    storageStats,
    
    // Actions
    fetchAudioFiles,
    addAudioFile,
    updateAudioFile,
    deleteAudio,
    playAudio,
    pauseAudio,
    stopAudio,
    startRecording,
    pauseRecording,
    resumeRecording,
    stopRecording,
    getAudioById,
    getAudioByCategory,
    searchAudio
  }
})
