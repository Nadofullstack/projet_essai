/**
 * Composable pour initialiser les données de test
 * À utiliser uniquement pour le développement
 */

import axios from 'axios'

export function useTestSetup() {
  const setupTestAuth = async () => {
    // Vérifier si on est en mode développement
    if (import.meta.env.MODE !== 'development') {
      console.warn('Test setup only available in development mode')
      return
    }

    // Si pas de token, obtenir un token de test du backend
    const existingToken = localStorage.getItem('auth_token')
    if (!existingToken) {
      try {
        // build url using same normalization as services
        const baseUrl = (import.meta.env.VITE_API_URL || 'http://localhost:8000').replace(/\/api\/?$/,'')
        const response = await axios.get(`${baseUrl}/api/auth/test-token`)


        if (response.data.success) {
          const { token, user } = response.data

          localStorage.setItem('auth_token', token)
          localStorage.setItem('user', JSON.stringify(user))

          console.log('✅ Test auth initialized from backend')
          console.log('User:', user)
        }
      } catch (error) {
        console.error('❌ Failed to initialize test auth:', error.message)
        // Fallback avec token factice
        console.log('Using placeholder token for testing')
        const testToken = 'test-token-placeholder'
        const testUser = {
          id: 1,
          name: 'Test User',
          email: 'test@example.com'
        }

        localStorage.setItem('auth_token', testToken)
        localStorage.setItem('user', JSON.stringify(testUser))
      }
    }
  }

  return {
    setupTestAuth
  }
}
