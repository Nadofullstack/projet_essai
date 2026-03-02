// Configuration de l'API
import axios from 'axios'
// Base URL comes from VITE_API_URL which should include '/api' suffix
// this allows requests like api.get('/messages') to target /api/messages
const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'


// Création d'une instance axios
const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 45000,  // ✅ AUGMENTÉ: 45 secondes au lieu de 15 (pagination + chargement lent)
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Fonction pour obtenir un token (authentification normale)
const getToken = () => {
  return localStorage.getItem('auth_token')
}

// Fonction pour se connecter (à appeler depuis le composant Login)
export const login = async (credentials) => {
  try {
    const response = await api.post('/auth/login', credentials)
    if (response.data.token) {
      localStorage.setItem('auth_token', response.data.token)
      localStorage.setItem('user', JSON.stringify(response.data.user))
      return { success: true, data: response.data }
    }
    return { success: false, error: 'Pas de token reçu' }
  } catch (error) {
    return { 
      success: false, 
      error: error.response?.data?.message || 'Erreur de connexion'
    }
  }
}

// Intercepteur pour ajouter le token
api.interceptors.request.use(
  (config) => {
    const token = getToken()
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => Promise.reject(error)
)

// Intercepteur pour gérer les erreurs
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      console.error('Erreur 401 - Non authentifié')
      
      // Ne pas rediriger si c'est une requête de login
      if (!error.config.url.includes('/auth/login')) {
        localStorage.removeItem('auth_token')
        localStorage.removeItem('user')
        
        if (typeof window !== 'undefined' && !window.location.pathname.includes('/login')) {
          window.location.href = '/login'
        }
      }
    }
    return Promise.reject(error)
  }
)

// Export des URLs de l'API
export const API_ENDPOINTS = {
  // Authentification
  AUTH: {
    LOGIN: '/auth/login',
    REGISTER: '/auth/register',
    LOGOUT: '/auth/logout',
    USER: '/user',
    GOOGLE: '/auth/google'
  },
  
  // Dashboard
  DASHBOARD: {
    STATS: '/dashboard/stats'
  },
  
  // Rendez-vous
  RENDEZ_VOUS: {
    LIST: '/rendez-vous',
    TODAY: '/rendez-vous/today',
    CREATE: '/rendez-vous',
    UPDATE: (id) => `/rendez-vous/${id}`,
    DELETE: (id) => `/rendez-vous/${id}`,
    CONFIRM: (id) => `/rendez-vous/${id}/confirm`,
    CANCEL: (id) => `/rendez-vous/${id}/cancel`,
    PERIOD: '/rendez-vous/period'
  },
  
  // Messages
  MESSAGES: {
    LIST: '/messages',
    CONVERSATIONS: '/messages/conversations',
    UNREAD: '/messages/unread',
    CREATE: '/messages',
    UPDATE: (id) => `/messages/${id}`,
    DELETE: (id) => `/messages/${id}`,
    MARK_READ: (id) => `/messages/${id}/read`
  },
  
  // Appels
  APPELS: {
    LIST: '/calls',
    RECENT: '/calls/recent',
    TODAY: '/calls/today',
    CREATE: '/calls',
    UPDATE: (id) => `/calls/${id}`,
    DELETE: (id) => `/calls/${id}`,
    BY_TYPE: (type) => `/calls/type/${type}`,
    BY_STATUS: (status) => `/calls/status/${status}`,
    MISSED: '/calls/missed',
    FAVORITES: '/calls/favorites',
    TOGGLE_FAVORITE: (id) => `/calls/${id}/favorite`,
    STATS: '/calls/stats',
    SEARCH: '/calls/search'
  },
  
  // Audio Manager
  AUDIO: {
    LIST: '/audio',
    CREATE: '/audio',
    UPDATE: (id) => `/audio/${id}`,
    DELETE: (id) => `/audio/${id}`,
    DOWNLOAD: (id) => `/audio/${id}/download`,
    BY_CATEGORY: (category) => `/audio/category/${category}`,
    STATS: '/audio/stats',
    SEARCH: '/audio/search'
  },
  
  // Événements
  EVENTS: {
    LIST: '/events',
    CREATE: '/events',
    UPDATE: (id) => `/events/${id}`,
    DELETE: (id) => `/events/${id}`,
    PERIOD: '/events/period',
    UPCOMING: '/events/upcoming',
    TODAY: '/events/today',
    BY_TYPE: (type) => `/events/type/${type}`,
    STATS: '/events/stats'
  },
  
  // Calendrier
  CALENDAR: {
    MONTH: '/calendar/month',
    WEEK: '/calendar/week',
    DAY: '/calendar/day',
    OVERVIEW: '/calendar/overview',
    STATS: '/calendar/stats'
  }
}

export default api




