import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useEvenementsStore = defineStore('evenements', () => {
  // État
  const events = ref([])
  const loading = ref(false)
  const error = ref(null)

  // Catégories disponibles
  const categories = ref([
    { value: 'conference', label: 'Conférence' },
    { value: 'workshop', label: 'Atelier' },
    { value: 'meeting', label: 'Réunion' },
    { value: 'social', label: 'Social' },
    { value: 'other', label: 'Autre' }
  ])

  // Actions
  const fetchEvents = async () => {
    loading.value = true
    error.value = null

    try {
      // Simulation d'appel API
      await new Promise(resolve => setTimeout(resolve, 1000))
      
      // Données mock
      events.value = [
        {
          id: 1,
          title: 'Conférence sur l\'Intelligence Artificielle',
          description: 'Une conférence passionnante sur les dernières avancées en IA et leurs applications pratiques.',
          date: '2024-01-15',
          startTime: '09:00',
          endTime: '12:00',
          location: 'Salle de conférence A',
          category: 'conference',
          maxAttendees: 100,
          attendees: [
            { id: 1, name: 'Jean Dupont', email: 'jean@example.com' },
            { id: 2, name: 'Marie Laurent', email: 'marie@example.com' }
          ],
          isRegistered: false,
          organizer: 'Tech Conference',
          price: 0
        },
        {
          id: 2,
          title: 'Atelier de Communication Efficace',
          description: 'Apprenez les techniques de communication pour améliorer vos relations professionnelles.',
          date: '2024-01-18',
          startTime: '14:00',
          endTime: '17:00',
          location: 'Salle B203',
          category: 'workshop',
          maxAttendees: 25,
          attendees: [
            { id: 3, name: 'Thomas Bernard', email: 'thomas@example.com' }
          ],
          isRegistered: true,
          organizer: 'Développement Personnel',
          price: 50
        },
        {
          id: 3,
          title: 'Réunion d\'Équipe Mensuelle',
          description: 'Point sur les projets en cours et objectifs du mois à venir.',
          date: '2024-01-20',
          startTime: '10:00',
          endTime: '11:30',
          location: 'Salle de réunion principale',
          category: 'meeting',
          maxAttendees: 20,
          attendees: [
            { id: 4, name: 'Sophie Martin', email: 'sophie@example.com' },
            { id: 5, name: 'Pierre Durand', email: 'pierre@example.com' }
          ],
          isRegistered: true,
          organizer: 'Direction',
          price: 0
        },
        {
          id: 4,
          title: 'Soirée Réseau Professionnel',
          description: 'Rencontrez d\'autres professionnels dans une ambiance détendue.',
          date: '2024-01-25',
          startTime: '18:00',
          endTime: '21:00',
          location: 'Restaurant Le Gourmet',
          category: 'social',
          maxAttendees: 50,
          attendees: [],
          isRegistered: false,
          organizer: 'Business Network',
          price: 30
        }
      ]
    } catch (err) {
      error.value = 'Erreur lors du chargement des événements'
      console.error('Error fetching events:', err)
    } finally {
      loading.value = false
    }
  }

  const addEvent = async (eventData) => {
    try {
      // Simulation d'appel API
      await new Promise(resolve => setTimeout(resolve, 500))
      
      const newEvent = {
        id: Date.now(),
        ...eventData,
        attendees: [],
        isRegistered: false
      }
      
      events.value.push(newEvent)
      return newEvent
    } catch (err) {
      error.value = 'Erreur lors de l\'ajout de l\'événement'
      console.error('Error adding event:', err)
      throw err
    }
  }

  const updateEvent = async (eventData) => {
    try {
      // Simulation d'appel API
      await new Promise(resolve => setTimeout(resolve, 500))
      
      const index = events.value.findIndex(event => event.id === eventData.id)
      if (index !== -1) {
        events.value[index] = { ...events.value[index], ...eventData }
      }
      return events.value[index]
    } catch (err) {
      error.value = 'Erreur lors de la mise à jour de l\'événement'
      console.error('Error updating event:', err)
      throw err
    }
  }

  const deleteEvent = async (eventId) => {
    try {
      // Simulation d'appel API
      await new Promise(resolve => setTimeout(resolve, 500))
      
      const index = events.value.findIndex(event => event.id === eventId)
      if (index !== -1) {
        events.value.splice(index, 1)
      }
    } catch (err) {
      error.value = 'Erreur lors de la suppression de l\'événement'
      console.error('Error deleting event:', err)
      throw err
    }
  }

  const toggleRegistration = async (eventId) => {
    try {
      // Simulation d'appel API
      await new Promise(resolve => setTimeout(resolve, 300))
      
      const event = events.value.find(e => e.id === eventId)
      if (event) {
        event.isRegistered = !event.isRegistered
        
        // Simulation d'ajout/retrait de l'utilisateur actuel
        const currentUser = { id: 999, name: 'Utilisateur Actuel', email: 'user@example.com' }
        
        if (event.isRegistered) {
          event.attendees.push(currentUser)
        } else {
          event.attendees = event.attendees.filter(attendee => attendee.id !== currentUser.id)
        }
      }
    } catch (err) {
      error.value = 'Erreur lors de la gestion de l\'inscription'
      console.error('Error toggling registration:', err)
      throw err
    }
  }

  const getEventById = (eventId) => {
    return events.value.find(event => event.id === eventId)
  }

  const getUpcomingEvents = () => {
    const today = new Date().toISOString().split('T')[0]
    return events.value.filter(event => event.date >= today)
  }

  const getMyEvents = () => {
    return events.value.filter(event => event.isRegistered)
  }

  return {
    // État
    events,
    loading,
    error,
    categories,
    
    // Actions
    fetchEvents,
    addEvent,
    updateEvent,
    deleteEvent,
    toggleRegistration,
    getEventById,
    getUpcomingEvents,
    getMyEvents
  }
})
