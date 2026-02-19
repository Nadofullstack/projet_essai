<template>
  <div class="h-full flex flex-col bg-white">
    <!-- Header de la conversation -->
    <div v-if="conversation" class="p-4 border-b border-gray-200 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <img 
          :src="conversation.avatar"
          :alt="conversation.name"
          class="w-12 h-12 rounded-full object-cover"
        />
        <div>
          <h3 class="font-medium text-gray-900">{{ conversation.name }}</h3>
          <p class="text-xs text-gray-500">
            {{ conversation.isOnline ? 'En ligne' : 'Hors ligne' }}
          </p>
        </div>
      </div>
      <button 
        @click="emit('close')"
        class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
      >
        <span class="material-symbols-outlined text-gray-600">close</span>
      </button>
    </div>

    <!-- Messages -->
    <div v-if="conversation" class="flex-1 overflow-y-auto p-4 space-y-4">
      <!-- Message vide -->
      <div v-if="messages.length === 0" class="flex flex-col items-center justify-center h-full text-center text-gray-500">
        <span class="material-symbols-outlined text-5xl opacity-50 mb-2">mail_outline</span>
        <p>Aucun message pour l'instant</p>
        <p class="text-sm">Commencez la conversation en envoyant un message</p>
      </div>

      <!-- Affichage des messages -->
      <div 
        v-for="(message, index) in messages"
        :key="message.id"
        :class="[
          'flex',
          message.isSender ? 'justify-end' : 'justify-start'
        ]"
      >
        <div :class="[
          'max-w-xs lg:max-w-md px-4 py-2 rounded-lg',
          message.isSender 
            ? 'bg-blue-500 text-white rounded-br-none' 
            : 'bg-gray-200 text-gray-900 rounded-bl-none'
        ]">
          <p class="text-sm">{{ message.content }}</p>
          <p :class="[
            'text-xs mt-1',
            message.isSender ? 'text-blue-100' : 'text-gray-600'
          ]">
            {{ formatTime(message.timestamp || message.time) }}
            <span v-if="message.isSender && message.status" class="ml-2">
              <span v-if="message.status === 'sent'" class="material-symbols-outlined text-xs">check</span>
              <span v-else-if="message.status === 'delivered'" class="material-symbols-outlined text-xs">done_all</span>
              <span v-else-if="message.status === 'read'" class="material-symbols-outlined text-xs">done_all</span>
            </span>
          </p>
        </div>
      </div>
    </div>

    <!-- Input pour envoyer un message -->
    <div v-if="conversation" class="p-4 border-t border-gray-200 bg-gray-50">
      <div class="flex items-end gap-3">
        <input 
          v-model="messageInput"
          @keyup.enter="sendMessage"
          type="text"
          placeholder="Écrivez un message..."
          class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
        />
        <button 
          @click="sendMessage"
          :disabled="!messageInput.trim()"
          class="p-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span class="material-symbols-outlined">send</span>
        </button>
      </div>
    </div>

    <!-- État vide -->
    <div v-if="!conversation" class="flex-1 flex flex-col items-center justify-center text-gray-500">
      <span class="material-symbols-outlined text-6xl opacity-30 mb-4">mail_outline</span>
      <p class="text-lg font-medium">Sélectionnez une conversation</p>
      <p class="text-sm">ou commencez une nouvelle en cherchant un utilisateur</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import { useMessagesStore } from '@/stores/messages'

const messagesStore = useMessagesStore()

// Props
const props = defineProps({
  conversation: {
    type: Object,
    default: null
  }
})

// State
const messageInput = ref('')

// Computed
const messages = computed(() => {
  // Mapper les messages du store pour avoir le format attendu
  return messagesStore.conversations.find(c => c.id === props.conversation?.id)?.messages || []
})

// Methods
const sendMessage = async () => {
  if (!messageInput.value.trim() || !props.conversation) return

  const content = messageInput.value
  messageInput.value = ''

  // Ajouter le message localement en attendant la réponse du serveur
  const localMessage = {
    id: Date.now(),
    content: content,
    isSender: true,
    status: 'sending',
    timestamp: new Date().toISOString(),
    time: new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })
  }

  const conversation = messagesStore.conversations.find(c => c.id === props.conversation.id)
  if (conversation) {
    conversation.messages.push(localMessage)
  }

  // Envoyer au serveur
  try {
    const success = await messagesStore.sendMessage(props.conversation.id, content)
    
    if (success) {
      localMessage.status = 'delivered'
    } else {
      localMessage.status = 'error'
    }
  } catch (error) {
    console.error('Erreur lors de l\'envoi:', error)
    localMessage.status = 'error'
  }

  // Scroller vers le bas
  await nextTick()
  const messagesContainer = document.querySelector('[data-messages-container]')
  if (messagesContainer) {
    messagesContainer.scrollTop = messagesContainer.scrollHeight
  }
}

const formatTime = (time) => {
  if (!time) return ''
  const date = new Date(time)
  return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })
}

// Watch pour scroller vers le bas quand de nouveaux messages arrivent
watch(() => messages.value.length, async () => {
  await nextTick()
  const messagesContainer = document.querySelector('[data-messages-container]')
  if (messagesContainer) {
    messagesContainer.scrollTop = messagesContainer.scrollHeight
  }
})

// Emit
const emit = defineEmits(['close'])
</script>

<style scoped>
/* Scroller personnalisé */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}
</style>
