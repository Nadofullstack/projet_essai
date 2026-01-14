// Configuration de l'API
import axios from 'axios'
const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000'

// Création d'une instance axios avec configuration par défaut
const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Intercepteur pour ajouter le token d'authentification
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Intercepteur pour gérer les erreurs d'authentification
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Token expiré ou invalide
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')
      // Rediriger vers la page de login
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default api

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
