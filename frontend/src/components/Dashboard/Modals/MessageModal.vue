<script setup>
  import {ref} from 'vue'
const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  recipient: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'send'])

const message = ref('')
const isSending = ref(false)

const closeModal = () => {
  emit('close')
  message.value = ''
}

const sendMessage = async () => {
  if (!message.value.trim()) return

  isSending.value = true

  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    emit('send', {
      recipient: props.recipient,
      message: message.value,
      timestamp: new Date()
    })
    
    closeModal()
  } catch (error) {
    console.error('Error sending message:', error)
  } finally {
    isSending.value = false
  }
}

// Close on escape key
const handleEscape = (e) => {
  if (e.key === 'Escape') {
    closeModal()
  }
}

onMounted(() => {
  document.addEventListener('keydown', handleEscape)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleEscape)
})
</script>

<template>
  <div 
    v-if="isOpen"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    @click="closeModal"
  >
    <div 
      class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4"
      @click.stop
    >
      <!-- Header -->
      <div class="p-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
          <h3 class="text-lg font-semibold text-gray-900">
            Nouveau message
          </h3>
          <button 
            @click="closeModal"
            class="p-1 rounded-lg hover:bg-gray-100"
          >
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
      </div>

      <!-- Recipient Info -->
      <div v-if="recipient" class="p-4 border-b border-gray-200">
        <div class="flex items-center space-x-3">
          <img 
            :src="recipient.avatar" 
            :alt="recipient.name"
            class="w-10 h-10 rounded-full"
          />
          <div>
            <p class="font-medium text-gray-900">{{ recipient.name }}</p>
            <p class="text-sm text-gray-500">{{ recipient.email }}</p>
          </div>
        </div>
      </div>

      <!-- Message Form -->
      <div class="p-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Message
        </label>
        <textarea
          v-model="message"
          rows="4"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
          placeholder="Tapez votre message ici..."
          @keydown.ctrl.enter="sendMessage"
        ></textarea>
        <p class="text-xs text-gray-500 mt-1">
          Appuyez sur Ctrl+Entrée pour envoyer
        </p>
      </div>

      <!-- Actions -->
      <div class="p-4 border-t border-gray-200 flex justify-end space-x-3">
        <button 
          @click="closeModal"
          class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
        >
          Annuler
        </button>
        <button 
          @click="sendMessage"
          :disabled="!message.trim() || isSending"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2"
        >
          <span v-if="isSending" class="material-symbols-outlined animate-spin text-sm">refresh</span>
          <span>{{ isSending ? 'Envoi...' : 'Envoyer' }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.resize-none {
  resize: none;
}
</style>
