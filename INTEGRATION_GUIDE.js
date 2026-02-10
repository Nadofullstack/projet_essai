/**
 * Exemple d'intégration WebSocket dans Messages.vue
 * 
 * Ajouter ces imports au début du script setup:
 */

// ============ IMPORTS ============
// import { useChat } from '@/composables/useChat'
// import { useAuth } from '@/composables/useAuth' // À adapter selon votre structure
// import { onMounted, onBeforeUnmount, ref } from 'vue'

// ============ COMPOSABLES ============
// const { 
//   messages,
//   isLoading,
//   error,
//   isConnected,
//   initChat,
//   sendMessage,
//   markMessageAsRead,
//   getConversations,
//   disconnectWebSocket,
// } = useChat()

// const auth = useAuth() // Pour obtenir l'utilisateur actuellement connecté

// ============ STATE ============
// const conversations = ref([])
// const selectedConversation = ref(null)
// const newMessage = ref('')

// ============ LIFECYCLE ============
// onMounted(async () => {
//   try {
//     // Charger les conversations
//     conversations.value = await getConversations()
//     
//     // Initialiser le chat si une conversation est sélectionnée
//     if (selectedConversation.value) {
//       await initChat(
//         auth.user.id,
//         selectedConversation.value.user.id
//       )
//     }
//   } catch (err) {
//     console.error('Erreur:', err)
//   }
// })

// onBeforeUnmount(() => {
//   // Nettoyer les ressources
//   disconnectWebSocket()
// })

// ============ METHODS ============
// const selectConversation = async (conversation) => {
//   selectedConversation.value = conversation
  
//   // Initialiser le chat pour cette conversation
//   await initChat(
//     auth.user.id,
//     conversation.user.id
//   )
// }

// const handleSendMessage = async () => {
//   if (!newMessage.value.trim()) return
  
//   try {
//     await sendMessage(
//       newMessage.value,
//       selectedConversation.value.user.id,
//       'text',
//       null // pas de pièce jointe
//     )
//     newMessage.value = '' // Vider le champ
//   } catch (err) {
//     console.error('Erreur lors de l\'envoi:', err)
//   }
// }

// const handleMarkAsRead = async (messageId) => {
//   await markMessageAsRead(messageId)
// }

// ============ COMPUTED ============
// const filteredConversations = computed(() => {
//   if (!searchQuery.value) return conversations.value
//   
//   return conversations.value.filter(conv =>
//     conv.user.name.toLowerCase().includes(searchQuery.value.toLowerCase())
//   )
// })

// const unreadCount = computed(() => {
//   return messages.value.filter(m => !m.is_read).length
// })

// ============ MODIFICATIONS À APPORTER À LA VUE ============

/**
 * 1. Dans le message input:
 * 
 * <textarea
 *   v-model="newMessage"
 *   @keyup.enter.ctrl="handleSendMessage"
 *   placeholder="Votre message..."
 * ></textarea>
 * 
 * 2. Afficher les messages:
 * 
 * <div class="messages-container">
 *   <div v-for="message in messages" :key="message.id" class="message" :class="{
 *     'sent': message.sender_id === auth.user.id,
 *     'received': message.sender_id !== auth.user.id
 *   }">
 *     <div class="message-header">
 *       <img :src="message.sender.avatar" :alt="message.sender.name">
 *       <div>
 *         <p class="font-bold">{{ message.sender.name }}</p>
 *         <p class="text-xs text-gray-500">{{ formatTime(message.created_at) }}</p>
 *       </div>
 *     </div>
 *     <div class="message-content">{{ message.content }}</div>
 *     <div v-if="message.type !== 'text'" class="message-attachment">
 *       <!-- Afficher l'attachment si présent -->
 *     </div>
 *     <button 
 *       v-if="!message.is_read && message.receiver_id === auth.user.id"
 *       @click="handleMarkAsRead(message.id)"
 *       class="btn-small"
 *     >
 *       ✓ Marquer comme lu
 *     </button>
 *   </div>
 * </div>
 * 
 * 3. Afficher le statut de connexion:
 * 
 * <div v-if="!isConnected" class="alert alert-info">
 *   <span class="material-symbols-outlined">info</span>
 *   Reconnexion en cours...
 * </div>
 * 
 * <div v-if="error" class="alert alert-error">
 *   <span class="material-symbols-outlined">error</span>
 *   {{ error }}
 * </div>
 * 
 * 4. Bouton d'envoi:
 * 
 * <button
 *   @click="handleSendMessage"
 *   :disabled="!newMessage.trim() || isLoading"
 *   class="btn-primary"
 * >
 *   <span class="material-symbols-outlined">send</span>
 *   Envoyer
 * </button>
 */

// ============ STYLES ============
/**
 * .message {
 *   display: flex;
 *   flex-direction: column;
 *   margin: 8px 0;
 *   padding: 12px;
 *   border-radius: 8px;
 *   max-width: 70%;
 * }
 * 
 * .message.sent {
 *   align-self: flex-end;
 *   background-color: #3b82f6;
 *   color: white;
 * }
 * 
 * .message.received {
 *   align-self: flex-start;
 *   background-color: #f3f4f6;
 *   color: #1f2937;
 * }
 * 
 * .message-container {
 *   display: flex;
 *   flex-direction: column;
 *   height: 100%;
 *   overflow-y: auto;
 *   padding: 16px;
 *   gap: 8px;
 * }
 */

export default {
  // Ce fichier est un guide d'intégration - il n'est pas importé directement
}
