import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useMessagesStore = defineStore('messages', () => {
  // État
  const conversations = ref([])
  const loading = ref(false)
  const error = ref(null)

  // Actions
  const fetchConversations = async () => {
    loading.value = true
    error.value = null
    
    try {
      // Simuler un appel API
      await new Promise(resolve => setTimeout(resolve, 1000))
      
      conversations.value = [
        {
          id: 1,
          name: 'Alice Dubois',
          avatar: 'https://picsum.photos/seed/alice/100/100.jpg',
          lastMessage: 'Salut ! Comment ça va ?',
          time: '10:30',
          unreadCount: 2,
          isOnline: true,
          messages: [
            {
              id: 1,
              content: 'Bonjour Alice !',
              time: '10:15',
              isSender: false
            },
            {
              id: 2,
              content: 'Salut ! Comment vas-tu ?',
              time: '10:20',
              isSender: true
            },
            {
              id: 3,
              content: 'Ça va bien, merci ! Et toi ?',
              time: '10:25',
              isSender: false
            },
            {
              id: 4,
              content: 'Salut ! Comment ça va ?',
              time: '10:30',
              isSender: false
            }
          ]
        },
        {
          id: 2,
          name: 'Bob Martin',
          avatar: 'https://picsum.photos/seed/bob/100/100.jpg',
          lastMessage: 'On se voit demain ?',
          time: 'Hier',
          unreadCount: 0,
          isOnline: false,
          messages: [
            {
              id: 1,
              content: 'Salut Bob !',
              time: 'Hier 14:00',
              isSender: true
            },
            {
              id: 2,
              content: 'Salut ! Comment vas-tu ?',
              time: 'Hier 14:05',
              isSender: false
            },
            {
              id: 3,
              content: 'On se voit demain ?',
              time: 'Hier 14:10',
              isSender: false
            }
          ]
        },
        {
          id: 3,
          name: 'Caroline Petit',
          avatar: 'https://picsum.photos/seed/caroline/100/100.jpg',
          lastMessage: 'Merci pour ton aide !',
          time: 'Mar',
          unreadCount: 1,
          isOnline: true,
          messages: [
            {
              id: 1,
              content: 'Bonjour Caroline !',
              time: 'Mar 09:00',
              isSender: false
            },
            {
              id: 2,
              content: 'Bonjour ! Comment puis-je vous aider ?',
              time: 'Mar 09:05',
              isSender: true
            },
            {
              id: 3,
              content: 'J\'ai besoin d\'aide pour le projet',
              time: 'Mar 09:10',
              isSender: false
            },
            {
              id: 4,
              content: 'Bien sûr, je vais t\'aider',
              time: 'Mar 09:15',
              isSender: true
            },
            {
              id: 5,
              content: 'Merci pour ton aide !',
              time: 'Mar 09:20',
              isSender: false
            }
          ]
        },
        {
          id: 4,
          name: 'David Leroy',
          avatar: 'https://picsum.photos/seed/david/100/100.jpg',
          lastMessage: 'À plus tard !',
          time: 'Lun',
          unreadCount: 0,
          isOnline: false,
          messages: [
            {
              id: 1,
              content: 'Salut David !',
              time: 'Lun 16:00',
              isSender: true
            },
            {
              id: 2,
              content: 'Salut !',
              time: 'Lun 16:05',
              isSender: false
            },
            {
              id: 3,
              content: 'À plus tard !',
              time: 'Lun 16:10',
              isSender: false
            }
          ]
        }
      ]
    } catch (err) {
      error.value = 'Erreur lors du chargement des conversations'
      console.error('Error fetching conversations:', err)
    } finally {
      loading.value = false
    }
  }

  const sendMessage = async ({ conversationId, content }) => {
    try {
      const conversation = conversations.value.find(c => c.id === conversationId)
      if (conversation) {
        const newMessage = {
          id: conversation.messages.length + 1,
          content,
          time: new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }),
          isSender: true
        }
        
        conversation.messages.push(newMessage)
        conversation.lastMessage = content
        conversation.time = 'À l\'instant'
        
        // Simuler une réponse
        setTimeout(() => {
          const responseMessage = {
            id: conversation.messages.length + 1,
            content: 'Message reçu !',
            time: new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }),
            isSender: false
          }
          conversation.messages.push(responseMessage)
          conversation.lastMessage = responseMessage.content
          conversation.time = 'À l\'instant'
        }, 2000)
      }
    } catch (err) {
      error.value = 'Erreur lors de l\'envoi du message'
      console.error('Error sending message:', err)
    }
  }

  const createNewConversation = async ({ recipient, content }) => {
    try {
      const newConversation = {
        id: conversations.value.length + 1,
        name: recipient,
        avatar: `https://picsum.photos/seed/${recipient}/100/100.jpg`,
        lastMessage: content,
        time: 'À l\'instant',
        unreadCount: 0,
        isOnline: Math.random() > 0.5,
        messages: [
          {
            id: 1,
            content,
            time: new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }),
            isSender: true
          }
        ]
      }
      
      conversations.value.unshift(newConversation)
    } catch (err) {
      error.value = 'Erreur lors de la création de la conversation'
      console.error('Error creating conversation:', err)
    }
  }

  const markConversationAsRead = (conversationId) => {
    const conversation = conversations.value.find(c => c.id === conversationId)
    if (conversation) {
      conversation.unreadCount = 0
    }
  }

  const deleteConversation = (conversationId) => {
    const index = conversations.value.findIndex(c => c.id === conversationId)
    if (index > -1) {
      conversations.value.splice(index, 1)
    }
  }

  return {
    // État
    conversations,
    loading,
    error,
    
    // Actions
    fetchConversations,
    sendMessage,
    createNewConversation,
    markConversationAsRead,
    deleteConversation
  }
})
