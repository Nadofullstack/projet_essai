<script setup>
const emit = defineEmits(['select-message', 'new-message'])

const props = defineProps({
  messages: {
    type: Array,
    default: () => [
      {
        id: 1,
        sender: 'Marie Laurent',
        avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Marie',
        message: 'Bonjour ! Comment allez-vous aujourd\'hui ?',
        time: '10:30',
        unread: true
      },
      {
        id: 2,
        sender: 'Thomas Bernard',
        avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Thomas',
        message: 'Le programme est prêt pour la révision',
        time: '09:15',
        unread: false
      },
      {
        id: 3,
        sender: 'Sophie Martin',
        avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Sophie',
        message: 'Merci pour votre aide hier',
        time: 'Hier',
        unread: false
      }
    ]
  }
})

const selectMessage = (message) => {
  emit('select-message', message)
}

const newMessage = () => {
  emit('new-message')
}
</script>

<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <!-- Header -->
    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
      <h3 class="font-semibold text-gray-900">Messages récents</h3>
      <button 
        @click="newMessage"
        class="text-blue-600 hover:text-blue-700 p-1 rounded hover:bg-blue-50"
      >
        <span class="material-symbols-outlined">add</span>
      </button>
    </div>

    <!-- Messages List -->
    <div class="divide-y divide-gray-100">
      <div 
        v-for="message in props.messages" 
        :key="message.id"
        @click="selectMessage(message)"
        class="p-4 hover:bg-gray-50 cursor-pointer transition-colors"
      >
        <div class="flex items-start space-x-3">
          <img 
            :src="message.avatar" 
            :alt="message.sender"
            class="w-10 h-10 rounded-full flex-shrink-0"
          />
          <div class="flex-1 min-w-0">
            <div class="flex justify-between items-start">
              <h4 class="font-medium text-gray-900 truncate">{{ message.sender }}</h4>
              <span class="text-xs text-gray-500">{{ message.time }}</span>
            </div>
            <p class="text-sm text-gray-600 mt-1 truncate">{{ message.message }}</p>
          </div>
          <div v-if="message.unread" class="flex-shrink-0">
            <span class="inline-block w-2 h-2 bg-blue-600 rounded-full"></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="p-4 border-t border-gray-200">
      <button class="w-full text-center text-sm text-blue-600 hover:text-blue-700 font-medium">
        Voir tous les messages
      </button>
    </div>
  </div>
</template>

<style scoped>
.transition-colors {
  transition: background-color 0.2s ease;
}

.divide-y > * + * {
  border-top: 1px solid #f3f4f6;
}

.divide-gray-100 > * + * {
  border-top-color: #f3f4f6;
}
</style>
