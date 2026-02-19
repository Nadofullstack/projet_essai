import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000'

export const useMessagesStore = defineStore('messages', () => {
  // État
  const conversations = ref([])
  const availableUsers = ref([])
  const loading = ref(false)
  const usersLoading = ref(false)
  const error = ref(null)
  const searchQuery = ref('')
  const currentUserId = ref(null)

  // Computed
  const filteredUsers = computed(() => {
    if (!searchQuery.value.trim()) return availableUsers.value
    
    const query = searchQuery.value.toLowerCase()
    return availableUsers.value.filter(user =>
      user.name.toLowerCase().includes(query) ||
      user.email.toLowerCase().includes(query)
    )
  })

  const sortedConversations = computed(() => {
    return [...conversations.value].sort((a, b) => {
      const timeA = new Date(a.lastMessageTime || a.time || 0).getTime()
      const timeB = new Date(b.lastMessageTime || b.time || 0).getTime()
      return timeB - timeA
    })
  })

  // Actions
  /**
   * Définir l'utilisateur courant pour le store (id ou objet)
   */
  const setCurrentUser = (user) => {
    if (!user) return
    if (typeof user === 'number') {
      currentUserId.value = user
    } else if (typeof user === 'object') {
      currentUserId.value = user.id || user.userId || null
    }
  }

  const fetchAvailableUsers = async () => {
    usersLoading.value = true
    error.value = null

    try {
      const response = await api.get('/messages/users/list')
      const usersData = response.data.data || response.data.users || []

      availableUsers.value = usersData.map(user => ({
        id: user.id,
        name: user.name || user.nom || 'Utilisateur',
        email: user.email,
        avatar: user.avatar || user.profile_picture || `https://api.dicebear.com/7.x/avataaars/svg?seed=${user.email}`,
        isOnline: user.isOnline || user.is_online || false
      }))

      console.log('✓ Utilisateurs disponibles chargés:', availableUsers.value.length)
      return true
    } catch (err) {
      error.value = 'Erreur lors du chargement des utilisateurs'
      console.error('Erreur fetchAvailableUsers:', err)
      return false
    } finally {
      usersLoading.value = false
    }
  }

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

  /**
   * Gérer un message entrant (depuis WebSocket)
   * Met à jour la conversation correspondante ou en crée une nouvelle
   */
  const handleIncomingMessage = (message) => {
    try {
      const senderId = message.sender_id || message.sender?.id || message.senderId
      const receiverId = message.receiver_id || message.receiver?.id || message.receiverId

      // Tenter de trouver une conversation existante par plusieurs clés
      let convIndex = conversations.value.findIndex(c =>
        c.id === senderId || c.id === receiverId ||
        c.userId === senderId || c.userId === receiverId
      )

      const newMsg = {
        id: message.id,
        content: message.content || message.body || '',
        time: message.created_at ? new Date(message.created_at).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }) : (new Date()).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }),
        isSender: !!(message.sender_id && message.sender_id === (currentUserId.value || parseInt(localStorage.getItem('user_id')))),
        isRead: !!message.is_read
      }

      if (convIndex === -1) {
        // Créer une nouvelle conversation basée sur les données du message
        const participantId = senderId || receiverId
        const participant = message.sender || message.receiver || { id: participantId, name: message.sender_name || message.sender?.name || 'Utilisateur' }

        const newConversation = {
          id: participantId,
          userId: participantId,
          name: participant.name || 'Utilisateur',
          avatar: participant.avatar || participant.profile_picture || `https://api.dicebear.com/7.x/avataaars/svg?seed=${participantId}`,
          lastMessage: newMsg.content,
          lastMessageTime: message.created_at || new Date().toISOString(),
          time: newMsg.time,
          unreadCount: newMsg.isSender ? 0 : 1,
          isOnline: false,
          messages: [newMsg]
        }

        conversations.value.unshift(newConversation)
      } else {
        // Mettre à jour la conversation existante
        const conv = conversations.value[convIndex]

        conv.messages = conv.messages || []

        // Éviter les doublons
        if (!conv.messages.some(m => m.id === newMsg.id)) {
          conv.messages.push(newMsg)
        }

        conv.lastMessage = newMsg.content
        conv.lastMessageTime = message.created_at || new Date().toISOString()
        conv.time = newMsg.time

        // Incrémenter le compteur de non lus si le destinataire est l'utilisateur courant
        const meId = currentUserId.value || parseInt(localStorage.getItem('user_id'))
        if (meId && message.receiver_id && parseInt(message.receiver_id) === meId) {
          conv.unreadCount = (conv.unreadCount || 0) + 1
        }

        // Déplacer la conversation en tête
        conversations.value.splice(convIndex, 1)
        conversations.value.unshift(conv)
      }
    } catch (err) {
      console.error('Erreur handleIncomingMessage:', err)
    }
  }

  return {
    // État
    conversations,
    availableUsers,
    loading,
    error,
    
    // Actions
    fetchConversations,
    fetchAvailableUsers,
    sendMessage,
    createNewConversation,
    markConversationAsRead,
    deleteConversation,
    searchUsers,
    startConversationWithUser
    ,
    handleIncomingMessage,
    setCurrentUser
  }
})
