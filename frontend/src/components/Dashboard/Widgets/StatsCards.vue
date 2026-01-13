<script setup>
import { computed } from 'vue'
import { useAppelsStore } from '@/stores/appels'
import { useRendezVousStore } from '@/stores/rendezvous'

const appelsStore = useAppelsStore()
const rendezvousStore = useRendezVousStore()

// Statistiques calculées à partir des données réelles
const stats = computed(() => {
  const callStats = appelsStore.getCallStats()
  const appointmentStats = rendezvousStore.getAppointmentStats()
  const todayCalls = appelsStore.calls.filter(call => {
    const today = new Date().toISOString().split('T')[0]
    return call.timestamp.startsWith(today.split('-')[0] + '-' + today.split('-')[1] + '-' + today.split('-')[2])
  }).length
  
  const missedCalls = callStats.missed
  const totalCalls = callStats.total
  const missedCallsPercentage = totalCalls > 0 ? Math.round((missedCalls / totalCalls) * 100) : 0
  
  return [
    {
      title: 'Appels aujourd\'hui',
      value: todayCalls.toString(),
      trend: todayCalls > 0 ? 'up' : 'neutral',
      change: todayCalls > 0 ? `+${todayCalls}` : '0',
      icon: 'phone',
      color: 'bg-blue-500'
    },
    {
      title: 'Rendez-vous confirmés',
      value: appointmentStats.confirmed.toString(),
      trend: appointmentStats.confirmed > 0 ? 'up' : 'neutral',
      change: appointmentStats.confirmed > 0 ? `+${appointmentStats.confirmed}` : '0',
      icon: 'event_available',
      color: 'bg-green-500'
    },
    {
      title: 'Appels manqués',
      value: missedCalls.toString(),
      trend: missedCalls > 0 ? 'down' : 'neutral',
      change: missedCalls > 0 ? `${missedCallsPercentage}%` : '0%',
      icon: 'phone_missed',
      color: 'bg-red-500'
    },
    {
      title: 'Rendez-vous en attente',
      value: appointmentStats.pending.toString(),
      trend: appointmentStats.pending > 0 ? 'up' : 'neutral',
      change: appointmentStats.pending > 0 ? `+${appointmentStats.pending}` : '0',
      icon: 'pending_actions',
      color: 'bg-orange-500'
    }
  ]
})
</script>

<template>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <div 
      v-for="stat in stats"
      :key="stat.title"
      class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md transition-shadow"
    >
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-500 mb-1">{{ stat.title }}</p>
          <p class="text-2xl font-bold text-gray-900">{{ stat.value }}</p>
          <div class="flex items-center mt-2">
            <span 
              v-if="stat.trend !== 'neutral'"
              :class="[
                'material-symbols-outlined text-sm mr-1',
                stat.trend === 'up' ? 'text-green-500' : 'text-red-500'
              ]"
            >
              {{ stat.trend === 'up' ? 'trending_up' : 'trending_down' }}
            </span>
            <span 
              :class="[
                'text-xs font-medium',
                stat.trend === 'up' ? 'text-green-600' : stat.trend === 'down' ? 'text-red-600' : 'text-gray-400'
              ]"
            >
              {{ stat.change }}
            </span>
            <span 
              v-if="stat.trend === 'neutral'"
              class="text-xs text-gray-400 ml-2 italic"
            >
              • aucune activité
            </span>
            <span 
              v-else
              class="text-xs text-gray-500 ml-1"
            >
              aujourd'hui
            </span>
          </div>
        </div>
        <div 
          :class="[
            'p-3 rounded-xl',
            stat.color
          ]"
        >
          <span class="material-symbols-outlined text-white text-xl">
            {{ stat.icon }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.transition-shadow {
  transition: box-shadow 0.3s ease;
}

.transition-shadow:hover {
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}
</style>
