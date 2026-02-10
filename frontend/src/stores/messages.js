import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

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
      const response = await api.get('/messages/conversations')
      const convData = response.data.data || response.data // Support les deux formats
      
      conversations.value = convData.map(conv => {
        // Le backend retourne { id, name, avatar, lastMessage, last_message_time, unread_count }
        return {
          id: conv.id,
          name: conv.name || 'Utilisateur',
          avatar: conv.avatar || null,
          lastMessage: conv.lastMessage || '',
          time: conv.last_message_time ? new Date(conv.last_message_time).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }) : 'À l\'instant',
          unreadCount: conv.unread_count || 0,
          isOnline: false, // À mettre à jour avec WebSocket
          messages: []
        }
      })
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors du chargement des conversations'
      console.error('Erreur fetchConversations:', err)
      // Fallback vers données simulées en cas d'erreur
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
              isSender: false,
              isRead: true
            },
            {
              id: 2,
              content: 'Salut ! Comment vas-tu ?',
              time: '10:20',
              isSender: true,
              status: 'read'
            },
            {
              id: 3,
              content: 'Ça va bien, merci ! Et toi ?',
              time: '10:25',
              isSender: false,
              isRead: true
            },
            {
              id: 4,
              content: 'Salut ! Comment ça va ?',
              time: '10:30',
              isSender: false,
              isRead: false
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
              isSender: true,
              status: 'delivered'
            },
            {
              id: 2,
              content: 'Salut ! Comment vas-tu ?',
              time: 'Hier 14:05',
              isSender: false,
              isRead: true
            },
            {
              id: 3,
              content: 'On se voit demain ?',
              time: 'Hier 14:10',
              isSender: false,
              isRead: true
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
              isSender: false,
              isRead: true
            },
            {
              id: 2,
              content: 'Bonjour ! Comment puis-je vous aider ?',
              time: 'Mar 09:05',
              isSender: true,
              status: 'read'
            },
            {
              id: 3,
              content: 'J\'ai besoin d\'aide pour le projet',
              time: 'Mar 09:10',
              isSender: false,
              isRead: true
            },
            {
              id: 4,
              content: 'Bien sûr, je vais t\'aider',
              time: 'Mar 09:15',
              isSender: true,
              status: 'read'
            },
            {
              id: 5,
              content: 'Merci pour ton aide !',
              time: 'Mar 09:20',
              isSender: false,
              isRead: false
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
              isSender: true,
              status: 'sent'
            },
            {
              id: 2,
              content: 'Salut !',
              time: 'Lun 16:05',
              isSender: false,
              isRead: true
            },
            {
              id: 3,
              content: 'À plus tard !',
              time: 'Lun 16:10',
              isSender: false,
              isRead: true
            }
          ]
        }
      ]
    } finally {
      loading.value = false
    }
  }

  const sendMessage = async ({ conversationId, content, receiverId, type = 'text' }) => {
    if (!conversationId) {
      error.value = 'Conversation non sélectionnée'
      throw new Error('Conversation non sélectionnée')
    }

    const conversation = conversations.value.find(c => c.id === conversationId)
    if (!conversation) {
      error.value = 'Conversation non trouvée'
      throw new Error('Conversation non trouvée')
    }

    // Créer le message localement immédiatement (optimistic update)
    const tempId = Math.random().toString(36).substr(2, 9)
    const newMessage = {
      id: tempId,
      content: content,
      time: new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }),
      isSender: true,
      status: 'pending', // En attente d'envoi
      isRead: true
    }

    // Ajouter le message immédiatement (optimistic update)
    conversation.messages = [...conversation.messages, newMessage]
    conversation.lastMessage = content
    conversation.time = 'À l\'instant'

    try {
      // Envoyer le message au serveur
      const response = await api.post('/messages', {
        receiver_id: receiverId,
        content: content,
        type: type
      })

      // Mettre à jour le statut du message avec la réponse du serveur
      const messageIndex = conversation.messages.findIndex(m => m.id === tempId)
      if (messageIndex > -1) {
        conversation.messages[messageIndex] = {
          id: response.data.data?.id || response.data.id || tempId,
          content: content,
          time: new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }),
          isSender: true,
          status: 'sent',
          isRead: true
        }
        // Forcer la réactivité
        conversation.messages = [...conversation.messages]
      }

      return response.data
    } catch (err) {
      console.error('Erreur sendMessage:', err)
      
      // Déterminer la cause de l'erreur
      if (err.code === 'ECONNABORTED') {
        error.value = 'Timeout: le serveur met trop de temps à répondre'
      } else if (err.message === 'Network Error' || err.code === 'ERR_NETWORK') {
        error.value = 'Erreur réseau: impossible de contacter le serveur'
        // Marquer le message comme non envoyé
        const messageIndex = conversation.messages.findIndex(m => m.id === tempId)
        if (messageIndex > -1) {
          conversation.messages[messageIndex].status = 'failed'
          conversation.messages = [...conversation.messages]
        }
      } else if (err.response?.status === 401) {
        error.value = 'Non authentifié: veuillez vous reconnecter'
      } else if (err.response?.status >= 500) {
        error.value = 'Erreur serveur: veuillez réessayer plus tard'
      } else {
        error.value = err.response?.data?.message || err.message || 'Erreur lors de l\'envoi du message'
      }
      
      throw err
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
      // Marquer tous les messages reçus comme lus
      conversation.messages.forEach(message => {
        if (!message.isSender && !message.isRead) {
          message.isRead = true
        }
      })
    }
  }

  const deleteConversation = (conversationId) => {
    const index = conversations.value.findIndex(c => c.id === conversationId)
    if (index > -1) {
      conversations.value.splice(index, 1)
    }
  }

  /**
   * Récupérer la liste des utilisateurs pour recherche
   */
  const searchUsers = async (searchQuery = '') => {
    try {
      const response = await api.get('/messages/users/list', {
        params: { search: searchQuery }
      })
      return response.data.data || []
    } catch (err) {
      console.error('Erreur searchUsers:', err)
      error.value = err.response?.data?.message || 'Erreur lors de la recherche'
      
      // Fallback avec données démo
      const demoUsers = [
        {
          id: 2,
          name: 'Alice Dubois',
          email: 'alice@example.com',
          profile_picture: 'https://picsum.photos/seed/alice/100/100.jpg'
        },
        {
          id: 3,
          name: 'Bob Martin',
          email: 'bob@example.com',
          profile_picture: 'https://picsum.photos/seed/bob/100/100.jpg'
        },
        {
          id: 4,
          name: 'Carol Smith',
          email: 'carol@example.com',
          profile_picture: 'https://picsum.photos/seed/carol/100/100.jpg'
        },
        {
          id: 5,
          name: 'David Johnson',
          email: 'david@example.com',
          profile_picture: 'https://picsum.photos/seed/david/100/100.jpg'
        }
      ]
      
      // Filtrer par requête de recherche
      if (!searchQuery) return demoUsers
      
      const query = searchQuery.toLowerCase()
      return demoUsers.filter(user => 
        user.name.toLowerCase().includes(query) || 
        user.email.toLowerCase().includes(query)
      )

    }
  }

  /**
   * Démarrer une conversation avec un utilisateur
   */
  const startConversationWithUser = (user) => {
    const existingConv = conversations.value.find(c => c.id === user.id)
    
    if (existingConv) {
      return existingConv
    }
    
    // Créer une nouvelle conversation
    const newConversation = {
      id: user.id,
      name: user.name,
      avatar: user.profile_picture || `https://picsum.photos/seed/${user.name}/100/100.jpg`,
      lastMessage: '',
      time: 'À l\'instant',
      unreadCount: 0,
      isOnline: true,
      messages: [],
      userId: user.id
    }
    
    conversations.value.unshift(newConversation)
    return newConversation
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
    deleteConversation,
    searchUsers,
    startConversationWithUser
  }
})
