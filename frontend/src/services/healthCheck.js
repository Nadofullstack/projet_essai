/**
 * Service pour vérifier la disponibilité du serveur API
 */

import axios from 'axios'

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000'

export async function checkServerHealth() {
  try {
    const response = await axios.get(`${API_BASE_URL}/health`, {
      timeout: 5000
    })
    return {
      available: true,
      message: 'Server is responding',
      data: response.data
    }
  } catch (error) {
    return {
      available: false,
      message: `Server error: ${error.message}`,
      error: error.code
    }
  }
}

export async function ensureAuthenticated() {
  try {
    // Try to get test token if no auth exists
    const token = localStorage.getItem('auth_token')
    
    if (!token) {
      const response = await axios.get(`${API_BASE_URL}/api/auth/test-token`, {
        timeout: 5000
      })
      
      if (response.data.success) {
        localStorage.setItem('auth_token', response.data.token)
        localStorage.setItem('user', JSON.stringify(response.data.user))
        return true
      }
    }
    
    return !!token
  } catch (error) {
    console.error('Authentication setup failed:', error.message)
    return false
  }
}
