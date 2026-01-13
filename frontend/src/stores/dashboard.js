import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useDashboardStore = defineStore('dashboard', () => {
  // État
  const currentUser = ref({
    id: 1,
    name: 'Jean Dupont',
    email: 'jean@example.com',
    role: 'user',
    avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Jean'
  })

  const stats = ref({
    totalPrograms: 12,
    activePrograms: 5,
    messagesSent: 48,
    audioRecordings: 23,
    upcomingEvents: 3,
    completionRate: 78
  })

  const recentPrograms = ref([
    {
      id: 1,
      title: 'Programme de méditation',
      description: 'Session quotidienne de relaxation',
      type: 'audio',
      progress: 75,
      status: 'active',
      lastActivity: '2024-01-15',
      duration: '15 min',
      color: 'bg-blue-500'
    },
    {
      id: 2,
      title: 'Exercices cognitifs',
      description: 'Entraînement mémoire et concentration',
      type: 'interactive',
      progress: 40,
      status: 'active',
      lastActivity: '2024-01-14',
      duration: '20 min',
      color: 'bg-green-500'
    },
    {
      id: 3,
      title: 'Routines matinales',
      description: 'Structurez vos matins',
      type: 'checklist',
      progress: 100,
      status: 'completed',
      lastActivity: '2024-01-13',
      duration: '10 min',
      color: 'bg-purple-500'
    },
    {
      id: 4,
      title: 'Gestion du stress',
      description: 'Techniques de respiration',
      type: 'video',
      progress: 25,
      status: 'active',
      lastActivity: '2024-01-12',
      duration: '30 min',
      color: 'bg-orange-500'
    }
  ])

  const recentMessages = ref([
    {
      id: 1,
      sender: 'Sophie Martin',
      senderAvatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Sophie',
      subject: 'Feedback programme',
      preview: 'Merci pour votre participation au programme...',
      timestamp: 'Il y a 2h',
      unread: true,
      type: 'incoming',
      priority: 'normal'
    },
    {
      id: 2,
      sender: 'Coach Pierre',
      senderAvatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Pierre',
      subject: 'Rappel séance',
      preview: 'Notre prochaine séance est prévue pour demain...',
      timestamp: 'Il y a 5h',
      unread: false,
      type: 'incoming',
      priority: 'high'
    },
    {
      id: 3,
      sender: 'Vous',
      senderAvatar: currentUser.value.avatar,
      subject: 'Question sur exercice',
      preview: 'J\'ai une question concernant l\'exercice 3...',
      timestamp: 'Hier',
      unread: false,
      type: 'outgoing',
      priority: 'normal'
    }
  ])

  const upcomingEvents = ref([
    {
      id: 1,
      title: 'Session coaching',
      date: '2024-01-16',
      time: '14:00',
      duration: '45 min',
      type: 'video_call',
      participants: 2
    },
    {
      id: 2,
      title: 'Atelier groupe',
      date: '2024-01-18',
      time: '10:00',
      duration: '2h',
      type: 'workshop',
      participants: 12
    },
    {
      id: 3,
      title: 'Revue mensuelle',
      date: '2024-01-20',
      time: '16:30',
      duration: '1h',
      type: 'meeting',
      participants: 4
    }
  ])

  const audioRecordings = ref([])

  // Getters
  const unreadMessages = computed(() => {
    return recentMessages.value.filter(msg => msg.unread).length
  })

  const totalAudioDuration = computed(() => {
    return audioRecordings.value.reduce((total, recording) => total + recording.duration, 0)
  })

  // Actions
  const fetchDashboardData = async () => {
    // Simulation d'appel API
    await new Promise(resolve => setTimeout(resolve, 500))
    console.log('Données dashboard chargées')
  }

  const markMessageAsRead = (messageId) => {
    const message = recentMessages.value.find(msg => msg.id === messageId)
    if (message) {
      message.unread = false
    }
  }

  const addAudioRecording = (audioData) => {
    const newRecording = {
      id: Date.now(),
      title: `Enregistrement ${audioRecordings.value.length + 1}`,
      duration: audioData.duration || 0,
      date: new Date().toISOString(),
      url: audioData.url,
      size: audioData.size || '0 MB'
    }
    audioRecordings.value.unshift(newRecording)
    stats.value.audioRecordings = audioRecordings.value.length
  }

  const sendMessage = (messageData) => {
    const newMessage = {
      id: Date.now(),
      sender: 'Vous',
      senderAvatar: currentUser.value.avatar,
      subject: messageData.subject || 'Sans objet',
      preview: messageData.content?.substring(0, 50) + '...',
      timestamp: 'À l\'instant',
      unread: false,
      type: 'outgoing',
      priority: messageData.priority || 'normal'
    }
    recentMessages.value.unshift(newMessage)
    stats.value.messagesSent++
  }

  const updateProgram = (programData) => {
    const index = recentPrograms.value.findIndex(p => p.id === programData.id)
    if (index !== -1) {
      recentPrograms.value[index] = { ...recentPrograms.value[index], ...programData }
    }
  }

  const addEvent = (eventData) => {
    const newEvent = {
      id: Date.now(),
      ...eventData
    }
    upcomingEvents.value.push(newEvent)
    upcomingEvents.value.sort((a, b) => new Date(a.date) - new Date(b.date))
    stats.value.upcomingEvents = upcomingEvents.value.length
  }

  return {
    // State
    currentUser,
    stats,
    recentPrograms,
    recentMessages,
    upcomingEvents,
    audioRecordings,
    
    // Getters
    unreadMessages,
    totalAudioDuration,
    
    // Actions
    fetchDashboardData,
    markMessageAsRead,
    addAudioRecording,
    sendMessage,
    updateProgram,
    addEvent
  }
})