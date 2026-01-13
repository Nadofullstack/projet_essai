import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useRendezVousStore = defineStore('rendezvous', () => {
  // État
  const appointments = ref([])
  const loading = ref(false)
  const error = ref(null)
  const selectedAppointment = ref(null)

  // Types de rendez-vous
  const appointmentTypes = ref([
    { value: 'consultation', label: 'Consultation', color: 'bg-blue-500', icon: 'medical_services' },
    { value: 'meeting', label: 'Réunion', color: 'bg-green-500', icon: 'groups' },
    { value: 'call', label: 'Appel', color: 'bg-purple-500', icon: 'phone' },
    { value: 'video', label: 'Visioconférence', color: 'bg-orange-500', icon: 'videocam' },
    { value: 'personal', label: 'Personnel', color: 'bg-gray-500', icon: 'person' }
  ])

  // Sauvegarder dans localStorage
  const saveToLocalStorage = () => {
    try {
      localStorage.setItem('appointments', JSON.stringify(appointments.value))
    } catch (error) {
      console.error('Erreur lors de la sauvegarde:', error)
    }
  }

  // Charger depuis localStorage
  const loadFromLocalStorage = () => {
    try {
      const saved = localStorage.getItem('appointments')
      if (saved) {
        appointments.value = JSON.parse(saved)
      }
    } catch (error) {
      console.error('Erreur lors du chargement:', error)
    }
  }

  // Actions
  const fetchAppointments = async () => {
    loading.value = true
    error.value = null

    try {
      // D'abord charger depuis localStorage
      loadFromLocalStorage()
      
      // Si pas de données en localStorage, charger les données mock
      if (appointments.value.length === 0) {
        await new Promise(resolve => setTimeout(resolve, 1000))
        
        // Données mock
        const mockAppointments = [
          {
            id: 1,
            title: 'Consultation avec Dr. Martin',
            description: 'Consultation de suivi pour le traitement vocal',
            date: '2024-01-15',
            startTime: '10:00',
            endTime: '11:00',
            type: 'consultation',
            location: 'Cabinet Médical, Salle 3',
            status: 'confirmed',
            notes: 'Apporter les résultats des examens',
            reminder: true,
            reminderTime: '09:30',
            participant: {
              name: 'Dr. Martin',
              email: 'dr.martin@medic.com',
              phone: '01 23 45 67 89',
              avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=DrMartin'
            }
          },
          {
            id: 2,
            title: 'Réunion d\'équipe',
            description: 'Point sur les projets en cours',
            date: '2024-01-18',
            startTime: '14:00',
            endTime: '16:00',
            type: 'meeting',
            location: 'Salle de conférence A',
            status: 'confirmed',
            notes: 'Préparer la présentation',
            reminder: true,
            reminderTime: '13:30',
            participant: {
              name: 'Équipe Projet',
              email: 'equipe@company.com',
              phone: '',
              avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Team'
            }
          },
          {
            id: 3,
            title: 'Appel avec client',
            description: 'Discussion sur les nouvelles fonctionnalités',
            date: '2024-01-20',
            startTime: '15:30',
            endTime: '16:30',
            type: 'call',
            location: 'Téléphone',
            status: 'pending',
            notes: 'Préparer les questions',
            reminder: true,
            reminderTime: '15:15',
            participant: {
              name: 'Client XYZ',
              email: 'contact@xyz.com',
              phone: '06 12 34 56 78',
              avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Client'
            }
          },
          {
            id: 4,
            title: 'Visioconférence',
            description: 'Réunion avec l\'équipe distante',
            date: '2024-01-22',
            startTime: '10:00',
            endTime: '11:30',
            type: 'video',
            location: 'Zoom',
            status: 'confirmed',
            notes: 'Tester la connexion avant',
            reminder: true,
            reminderTime: '09:45',
            participant: {
              name: 'Équipe Distante',
              email: 'remote@company.com',
              phone: '',
              avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Remote'
            }
          }
        ]
        
        appointments.value = mockAppointments
        saveToLocalStorage()
      }
    } catch (err) {
      error.value = 'Erreur lors du chargement des rendez-vous'
      console.error('Error fetching appointments:', err)
    } finally {
      loading.value = false
    }
  }

  const addAppointment = async (appointmentData) => {
    try {
      // Simulation d'appel API
      await new Promise(resolve => setTimeout(resolve, 500))
      
      const newAppointment = {
        id: Date.now(),
        ...appointmentData,
        status: 'pending',
        createdAt: new Date().toISOString()
      }
      
      appointments.value.push(newAppointment)
      saveToLocalStorage() // Sauvegarder après ajout
      return newAppointment
    } catch (err) {
      error.value = 'Erreur lors de l\'ajout du rendez-vous'
      console.error('Error adding appointment:', err)
      throw err
    }
  }

  const updateAppointment = async (appointmentData) => {
    try {
      // Simulation d'appel API
      await new Promise(resolve => setTimeout(resolve, 500))
      
      const index = appointments.value.findIndex(apt => apt.id === appointmentData.id)
      if (index !== -1) {
        appointments.value[index] = { ...appointments.value[index], ...appointmentData }
        saveToLocalStorage() // Sauvegarder après modification
      }
      return appointments.value[index]
    } catch (err) {
      error.value = 'Erreur lors de la mise à jour du rendez-vous'
      console.error('Error updating appointment:', err)
      throw err
    }
  }

  const deleteAppointment = async (appointmentId) => {
    try {
      // Simulation d'appel API
      await new Promise(resolve => setTimeout(resolve, 500))
      
      const index = appointments.value.findIndex(apt => apt.id === appointmentId)
      if (index !== -1) {
        appointments.value.splice(index, 1)
        saveToLocalStorage() // Sauvegarder après suppression
      }
    } catch (err) {
      error.value = 'Erreur lors de la suppression du rendez-vous'
      console.error('Error deleting appointment:', err)
      throw err
    }
  }

  const confirmAppointment = async (appointmentId) => {
    try {
      await updateAppointment({ id: appointmentId, status: 'confirmed' })
    } catch (err) {
      console.error('Error confirming appointment:', err)
      throw err
    }
  }

  const cancelAppointment = async (appointmentId, reason = '') => {
    try {
      await updateAppointment({ 
        id: appointmentId, 
        status: 'cancelled',
        cancellationReason: reason
      })
    } catch (err) {
      console.error('Error cancelling appointment:', err)
      throw err
    }
  }

  const getAppointmentById = (appointmentId) => {
    return appointments.value.find(apt => apt.id === appointmentId)
  }

  const getAppointmentsByDate = (date) => {
    return appointments.value.filter(apt => apt.date === date)
  }

  const getAppointmentsByWeek = (startDate, endDate) => {
    return appointments.value.filter(apt => {
      const aptDate = new Date(apt.date)
      return aptDate >= new Date(startDate) && aptDate <= new Date(endDate)
    })
  }

  const getAppointmentsByMonth = (year, month) => {
    return appointments.value.filter(apt => {
      const aptDate = new Date(apt.date)
      return aptDate.getFullYear() === year && aptDate.getMonth() === month - 1
    })
  }

  const getUpcomingAppointments = () => {
    const today = new Date().toISOString().split('T')[0]
    return appointments.value
      .filter(apt => apt.date >= today && apt.status !== 'cancelled')
      .sort((a, b) => new Date(a.date + ' ' + a.startTime) - new Date(b.date + ' ' + b.startTime))
  }

  const getTodayAppointments = () => {
    const today = new Date().toISOString().split('T')[0]
    return appointments.value.filter(apt => apt.date === today)
  }

  const getAppointmentStats = () => {
    const total = appointments.value.length
    const confirmed = appointments.value.filter(apt => apt.status === 'confirmed').length
    const pending = appointments.value.filter(apt => apt.status === 'pending').length
    const cancelled = appointments.value.filter(apt => apt.status === 'cancelled').length
    const today = getTodayAppointments().length
    const upcoming = getUpcomingAppointments().length

    return {
      total,
      confirmed,
      pending,
      cancelled,
      today,
      upcoming
    }
  }

  // Initialiser au chargement
  loadFromLocalStorage()

  return {
    // État
    appointments,
    loading,
    error,
    selectedAppointment,
    appointmentTypes,
    
    // Actions
    fetchAppointments,
    addAppointment,
    updateAppointment,
    deleteAppointment,
    confirmAppointment,
    cancelAppointment,
    getAppointmentById,
    getAppointmentsByDate,
    getAppointmentsByWeek,
    getAppointmentsByMonth,
    getUpcomingAppointments,
    getTodayAppointments,
    getAppointmentStats
  }
})