import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import { useTestSetup } from './composables/useTestSetup'
import { checkServerHealth } from './services/healthCheck'

const app = createApp(App)

app.use(createPinia())
app.use(router)

// Initialize test auth in development mode and check server
if (import.meta.env.MODE === 'development') {
  // Check server health first
  checkServerHealth().then(health => {
    console.log('Server health check:', health)
    
    if (health.available) {
      // Setup test auth if server is available
      const { setupTestAuth } = useTestSetup()
      setupTestAuth().catch(err => {
        console.error('Error setting up test auth:', err)
      })
    } else {
      console.warn('⚠️ Server is not responding. Using demo data.')
      // Still setup with a placeholder token to allow navigation
      const testToken = 'demo-token-' + Date.now()
      const testUser = {
        id: 1,
        name: 'Demo User',
        email: 'demo@example.com'
      }
      localStorage.setItem('auth_token', testToken)
      localStorage.setItem('user', JSON.stringify(testUser))
    }
  })
}

app.mount('#app')
