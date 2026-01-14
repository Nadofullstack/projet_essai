import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useAppelsStore = defineStore('appels', () => {
  // État
  const calls = ref([])
  const contacts = ref([])
  const loading = ref(false)
  const error = ref(null)
  const currentCall = ref(null)
  const isInCall = ref(false)

  // Types d'appels
  const callTypes = ref([
    { value: 'audio', label: 'Appel audio', icon: 'phone', color: 'bg-blue-500' },
    { value: 'video', label: 'Appel vidéo', icon: 'videocam', color: 'bg-green-500' },
    { value: 'conference', label: 'Conférence', icon: 'groups', color: 'bg-purple-500' }
  ])

  // Statuts d'appels
  const callStatuses = ref([
    { value: 'missed', label: 'Manqué', color: 'bg-red-100 text-red-800', icon: 'phone_missed' },
    { value: 'incoming', label: 'Entrant', color: 'bg-green-100 text-green-800', icon: 'phone_in_talk' },
    { value: 'outgoing', label: 'Sortant', color: 'bg-blue-100 text-blue-800', icon: 'phone_forwarded' },
    { value: 'ongoing', label: 'En cours', color: 'bg-orange-100 text-orange-800', icon: 'phone_in_talk' }
  ])

  // Actions
  const fetchCalls = async () => {
    loading.value = true
    error.value = null

    try {
      // Simulation d'appel API
      await new Promise(resolve => setTimeout(resolve, 1000))
      
      // Données mock pour l'historique des appels
      calls.value = [
        {
          id: 1,
          contactId: 1,
          type: 'audio',
          status: 'incoming',
          duration: 180, // 3 minutes
          timestamp: '2024-01-10T10:30:00Z',
          notes: 'Discussion sur le projet Alpha'
        },
        {
          id: 2,
          contactId: 2,
          type: 'video',
          status: 'outgoing',
          duration: 600, // 10 minutes
          timestamp: '2024-01-09T14:15:00Z',
          notes: 'Réunion d\'équipe en visio'
        },
        {
          id: 3,
          contactId: 3,
          type: 'audio',
          status: 'missed',
          duration: 0,
          timestamp: '2024-01-08T16:45:00Z',
          notes: 'Appel manqué - à rappeler'
        },
        {
          id: 4,
          contactId: 1,
          type: 'conference',
          status: 'incoming',
          duration: 1200, // 20 minutes
          timestamp: '2024-01-07T09:00:00Z',
          notes: 'Conférence avec plusieurs participants'
        }
      ]
    } catch (err) {
      error.value = 'Erreur lors du chargement des appels'
      console.error('Error fetching calls:', err)
    } finally {
      loading.value = false
    }
  }

  const fetchContacts = async () => {
    try {
      // Simulation d'appel API
      await new Promise(resolve => setTimeout(resolve, 500))
      
      // Données mock pour les contacts
      contacts.value = [
        {
          id: 1,
          name: 'Marie Laurent',
          email: 'marie.laurent@example.com',
          phone: '06 12 34 56 78',
          avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Marie',
          company: 'Tech Corp',
          department: 'Développement',
          lastCall: '2024-01-10T10:30:00Z',
          isFavorite: true,
          notes: 'Contact principal pour le projet Alpha'
        },
        {
          id: 2,
          name: 'Thomas Bernard',
          email: 'thomas.bernard@example.com',
          phone: '06 23 45 67 89',
          avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Thomas',
          company: 'Dev Studio',
          department: 'Design',
          lastCall: '2024-01-09T14:15:00Z',
          isFavorite: false,
          notes: 'Designer UX/UI'
        },
        {
          id: 3,
          name: 'Sophie Martin',
          email: 'sophie.martin@example.com',
          phone: '06 34 56 78 90',
          avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Sophie',
          company: 'Marketing Pro',
          department: 'Marketing',
          lastCall: '2024-01-08T16:45:00Z',
          isFavorite: true,
          notes: 'Responsable marketing'
        },
        {
          id: 4,
          name: 'Pierre Durand',
          email: 'pierre.durand@example.com',
          phone: '06 45 67 89 01',
          avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Pierre',
          company: 'Freelance',
          department: 'Consultant',
          lastCall: '2024-01-07T09:00:00Z',
          isFavorite: false,
          notes: 'Consultant indépendant'
        }
      ]
    } catch (err) {
      error.value = 'Erreur lors du chargement des contacts'
      console.error('Error fetching contacts:', err)
    }
  }

  const initiateCall = async (contactId, type = 'audio') => {
    try {
      const contact = contacts.value.find(c => c.id === contactId)
      if (!contact) {
        throw new Error('Contact non trouvé')
      }

      currentCall.value = {
        id: Date.now(),
        contactId,
        type,
        status: 'ongoing',
        startTime: new Date(),
        duration: 0
      }

      isInCall.value = true

      // Simulation de l'appel
      console.log(`Appel ${type} initié avec ${contact.name}`)
      
      // Mettre à jour le dernier appel du contact
      contact.lastCall = new Date().toISOString()

    } catch (err) {
      error.value = 'Erreur lors de l\'initiation de l\'appel'
      console.error('Error initiating call:', err)
      throw err
    }
  }

  const endCall = async () => {
    if (!currentCall.value) return

    try {
      const endTime = new Date()
      const duration = Math.floor((endTime - currentCall.value.startTime) / 1000)

      // Ajouter l'appel à l'historique
      const callRecord = {
        id: currentCall.value.id,
        contactId: currentCall.value.contactId,
        type: currentCall.value.type,
        status: 'outgoing',
        duration,
        timestamp: currentCall.value.startTime.toISOString(),
        notes: 'Appel terminé'
      }

      calls.value.unshift(callRecord)

      // Réinitialiser l'état
      currentCall.value = null
      isInCall.value = false

      console.log(`Appel terminé après ${duration} secondes`)

    } catch (err) {
      error.value = 'Erreur lors de la fin de l\'appel'
      console.error('Error ending call:', err)
      throw err
    }
  }

  const rejectCall = async () => {
    if (!currentCall.value) return

    try {
      // Ajouter l'appel manqué à l'historique
      const callRecord = {
        id: currentCall.value.id,
        contactId: currentCall.value.contactId,
        type: currentCall.value.type,
        status: 'missed',
        duration: 0,
        timestamp: currentCall.value.startTime.toISOString(),
        notes: 'Appel rejeté'
      }

      calls.value.unshift(callRecord)

      // Réinitialiser l'état
      currentCall.value = null
      isInCall.value = false

      console.log('Appel rejeté')

    } catch (err) {
      error.value = 'Erreur lors du rejet de l\'appel'
      console.error('Error rejecting call:', err)
      throw err
    }
  }

  const addContact = async (contactData) => {
    try {
      const newContact = {
        id: Date.now(),
        ...contactData,
        lastCall: null,
        isFavorite: false,
        notes: ''
      }

      contacts.value.push(newContact)
      return newContact

    } catch (err) {
      error.value = 'Erreur lors de l\'ajout du contact'
      console.error('Error adding contact:', err)
      throw err
    }
  }

  const updateContact = async (contactData) => {
    try {
      const index = contacts.value.findIndex(c => c.id === contactData.id)
      if (index !== -1) {
        contacts.value[index] = { ...contacts.value[index], ...contactData }
      }
      return contacts.value[index]

    } catch (err) {
      error.value = 'Erreur lors de la mise à jour du contact'
      console.error('Error updating contact:', err)
      throw err
    }
  }

  const deleteContact = async (contactId) => {
    try {
      const index = contacts.value.findIndex(c => c.id === contactId)
      if (index !== -1) {
        contacts.value.splice(index, 1)
      }

    } catch (err) {
      error.value = 'Erreur lors de la suppression du contact'
      console.error('Error deleting contact:', err)
      throw err
    }
  }

  const toggleFavorite = async (contactId) => {
    try {
      const contact = contacts.value.find(c => c.id === contactId)
      if (contact) {
        contact.isFavorite = !contact.isFavorite
      }

    } catch (err) {
      error.value = 'Erreur lors de la mise à jour du favori'
      console.error('Error toggling favorite:', err)
      throw err
    }
  }

  // Fonctions utilitaires
  const getContactById = (contactId) => {
    return contacts.value.find(c => c.id === contactId)
  }

  const getCallById = (callId) => {
    return calls.value.find(c => c.id === callId)
  }

  const getCallsByContact = (contactId) => {
    return calls.value.filter(c => c.contactId === contactId)
  }

  const getRecentCalls = (limit = 10) => {
    return calls.value
      .map(call => {
        const contact = contacts.value.find(c => c.id === call.contactId)
        return {
          ...call,
          contact: contact ? contact.name : 'Inconnu',
          type: call.status === 'incoming' ? 'incoming' : 'outgoing' // adjust type
        }
      })
      .sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp))
      .slice(0, limit)
  }

  const getMissedCalls = () => {
    return calls.value.filter(c => c.status === 'missed')
  }

  const getCallStats = () => {
    const total = calls.value.length
    const missed = calls.value.filter(c => c.status === 'missed').length
    const incoming = calls.value.filter(c => c.status === 'incoming').length
    const outgoing = calls.value.filter(c => c.status === 'outgoing').length
    const totalDuration = calls.value.reduce((sum, c) => sum + c.duration, 0)

    return {
      total,
      missed,
      incoming,
      outgoing,
      totalDuration,
      averageDuration: total > 0 ? Math.floor(totalDuration / total) : 0
    }
  }

  const addCallToHistory = (callRecord) => {
    calls.value.unshift(callRecord)
  }

  const updateCallDuration = (callId, duration) => {
    const call = calls.value.find(c => c.id === callId)
    if (call) {
      call.duration = duration
    }
  }

  const formatDuration = (seconds) => {
    if (seconds < 60) {
      return `${seconds}s`
    }
    const minutes = Math.floor(seconds / 60)
    const remainingSeconds = seconds % 60
    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`
  }

  return {
    // État
    calls,
    contacts,
    loading,
    error,
    currentCall,
    isInCall,
    callTypes,
    callStatuses,
    
    // Actions
    fetchCalls,
    fetchContacts,
    initiateCall,
    endCall,
    rejectCall,
    addContact,
    updateContact,
    deleteContact,
    toggleFavorite,
    addCallToHistory,
    updateCallDuration,
    
    // Utilitaires
    getContactById,
    getCallById,
    getCallsByContact,
    getRecentCalls,
    getMissedCalls,
    getCallStats,
    formatDuration
  }
})
