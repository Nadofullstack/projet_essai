<script setup>
const emit = defineEmits(['action'])

const actions = [
  {
    id: 'new_message',
    label: 'Nouveau message',
    icon: 'chat',
    color: 'bg-blue-500 hover:bg-blue-600'
  },
  {
    id: 'new_audio',
    label: 'Nouvel audio',
    icon: 'mic',
    color: 'bg-purple-500 hover:bg-purple-600'
  },
  {
    id: 'new_appointment',
    label: 'Nouveau rendez-vous',
    icon: 'event',
    color: 'bg-green-500 hover:bg-green-600'
  },
  {
    id: 'new_event',
    label: 'Nouvel événement',
    icon: 'calendar_month',
    color: 'bg-orange-500 hover:bg-orange-600'
  }
]

const handleAction = (actionId) => {
  emit('action', actionId)
}
</script>

<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
    <h3 class="font-semibold text-gray-900 mb-4">Actions rapides</h3>
    <div class="grid grid-cols-2 gap-3">
      <button
        v-for="action in actions"
        :key="action.id"
        @click="handleAction(action.id)"
        :class="[
          'flex flex-col items-center justify-center p-4 rounded-lg text-white transition-colors',
          action.color
        ]"
      >
        <span class="material-symbols-outlined text-2xl mb-2">{{ action.icon }}</span>
        <span class="text-sm font-medium">{{ action.label }}</span>
      </button>
    </div>
  </div>
</template>

<style scoped>
.grid {
  display: grid;
}

.grid-cols-2 {
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.gap-3 {
  gap: 0.75rem;
}

.transition-colors {
  transition: background-color 0.2s ease;
}
</style>
