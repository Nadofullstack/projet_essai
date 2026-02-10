<template>
  <div class="min-h-screen bg-gray-100 p-8">
    <div class="max-w-md mx-auto">
      <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold mb-6">Test - Recherche d'utilisateurs</h1>
        
        <!-- Status -->
        <div class="mb-6">
          <p v-if="serverAvailable" class="text-green-600 font-semibold">
            ✅ Serveur disponible
          </p>
          <p v-else class="text-yellow-600 font-semibold">
            ⚠️ Mode démo (serveur indisponible)
          </p>
        </div>
        
        <!-- Search Input -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Rechercher un utilisateur:
          </label>
          <input 
            v-model="searchQuery"
            @input="handleSearch"
            type="text" 
            placeholder="Tapez un nom ou email..."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
          >
        </div>
        
        <!-- Results -->
        <div class="mb-4">
          <p class="text-sm text-gray-600 mb-2">
            Résultats: {{ results.length }}
          </p>
          <div v-if="results.length > 0" class="space-y-2">
            <div 
              v-for="user in results"
              :key="user.id"
              class="flex items-center space-x-3 p-3 bg-green-50 border border-green-200 rounded-lg"
            >
              <img 
                :src="user.profile_picture || `https://picsum.photos/seed/${user.name}/50/50.jpg`" 
                :alt="user.name" 
                class="w-10 h-10 rounded-full"
              >
              <div>
                <p class="font-medium text-gray-900">{{ user.name }}</p>
                <p class="text-xs text-gray-500">{{ user.email }}</p>
              </div>
            </div>
          </div>
          <p v-else-if="searchQuery" class="text-gray-500 italic">
            Aucun résultat trouvé
          </p>
        </div>
        
        <!-- Test Actions -->
        <div class="space-y-2">
          <button 
            @click="testSearch('Alice')"
            class="w-full px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
          >
            Test: Rechercher "Alice"
          </button>
          <button 
            @click="testSearch('bob')"
            class="w-full px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
          >
            Test: Rechercher "bob"
          </button>
          <button 
            @click="testSearch('')"
            class="w-full px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600"
          >
            Effacer la recherche
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useMessagesStore } from '@/stores/messages'
import { checkServerHealth } from '@/services/healthCheck'

const messagesStore = useMessagesStore()
const searchQuery = ref('')
const results = ref([])
const serverAvailable = ref(true)

// Check server on mount
onMounted(async () => {
  const health = await checkServerHealth()
  serverAvailable.value = health.available
})

// Debounced search
const searchUsersDebounced = (() => {
  let timeoutId = null
  return async (query) => {
    clearTimeout(timeoutId)
    if (!query) {
      results.value = []
      return
    }
    timeoutId = setTimeout(async () => {
      try {
        results.value = await messagesStore.searchUsers(query)
      } catch (error) {
        console.error('Search error:', error)
        results.value = []
      }
    }, 300)
  }
})()

const handleSearch = (event) => {
  searchUsersDebounced(event.target.value)
}

const testSearch = async (query) => {
  searchQuery.value = query
  results.value = await messagesStore.searchUsers(query)
}

import { onMounted } from 'vue'
</script>

<style scoped>
/* Styles personnalisés si nécessaire */
</style>
