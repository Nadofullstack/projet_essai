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
      console.log('📡 Fetching available users...')
      const response = await api.get('/messages/users/list')
      const usersData = response.data.data || response.data.users || []

      availableUsers.value = usersData.map(user => ({
        id: user.id,
        name: user.name || user.nom || 'Utilisateur',
        email: user.email,
        avatar: user.avatar || user.profile_picture || null,
        isOnline: user.isOnline || user.is_online || false
      }))

      console.log('✅ Utilisateurs chargés:', availableUsers.value.length)
      return true
    } catch (err) {
      console.error('❌ Erreur fetchAvailableUsers:', err)
      
      if (err.response?.status === 401) {
        error.value = 'Session expirée. Veuillez vous reconnecter.'
        // Rediriger vers login après un délai
        setTimeout(() => {
          window.location.href = '/login'
        }, 2000)
      } else {
        error.value = 'Erreur lors du chargement des utilisateurs'
      }
      
      availableUsers.value = []
      return false
    } finally {
      usersLoading.value = false
    }
  }

  const fetchConversationMessages = async (userId) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await api.get(`/messages/conversation/${userId}`)
      const messagesData = response.data.data || response.data.messages || []
      
      const conversation = conversations.value.find(c => c.id === userId)
      if (conversation) {
        conversation.messages = messagesData.map(msg => ({
          id: msg.id,
          content: msg.content,
          time: new Date(msg.created_at).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }),
          isSender: msg.isSender,
          status: msg.status || 'read',
          isRead: msg.is_read || msg.isRead || false
        }))
      }
      
      return conversation
    } catch (err) {
      console.error('❌ Erreur fetchConversationMessages:', err)
      
      if (err.response?.status === 401) {
        error.value = 'Session expirée. Veuillez vous reconnecter.'
      } else {
        error.value = err.response?.data?.message || 'Erreur lors du chargement des messages'
      }
      
      const conversation = conversations.value.find(c => c.id === userId)
      if (conversation) {
        conversation.messages = []
      }
      return null
    } finally {
      loading.value = false
    }
  }

  const fetchConversations = async () => {
    loading.value = true
    error.value = null
    
    try {
      console.log('📡 Fetching conversations...')
      const response = await api.get('/messages/conversations')
      const convData = response.data.data || response.data
      
      conversations.value = convData.map(conv => ({
        id: conv.id,
        name: conv.name || 'Utilisateur',
        avatar: conv.avatar || null,
        lastMessage: conv.lastMessage || '',
        time: conv.last_message_time ? new Date(conv.last_message_time).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }) : '',
        lastMessageTime: conv.last_message_time,
        unreadCount: conv.unread_count || 0,
        isOnline: false,
        messages: []
      }))
      
      console.log('✅ Conversations chargées:', conversations.value.length)
    } catch (err) {
      console.error('❌ Erreur fetchConversations:', err)
      
      if (err.response?.status === 401) {
        error.value = 'Session expirée. Veuillez vous reconnecter.'
      } else {
        error.value = err.response?.data?.message || 'Erreur lors du chargement des conversations'
      }
      
      conversations.value = []
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

    // Optimistic update
    const tempId = Math.random().toString(36).substr(2, 9)
    const newMessage = {
      id: tempId,
      content: content,
      time: new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }),
      isSender: true,
      status: 'pending',
      isRead: true
    }

    conversation.messages = [...conversation.messages, newMessage]
    conversation.lastMessage = content
    conversation.time = 'À l\'instant'
    conversation.lastMessageTime = new Date().toISOString()

    try {
      const response = await api.post('/messages', {
        receiver_id: receiverId,
        content: content,
        type: type
      })

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
        conversation.messages = [...conversation.messages]
      }

      return response.data
    } catch (err) {
      console.error('❌ Erreur sendMessage:', err)
      
      // Marquer le message comme échoué
      const messageIndex = conversation.messages.findIndex(m => m.id === tempId)
      if (messageIndex > -1) {
        conversation.messages[messageIndex].status = 'failed'
        conversation.messages = [...conversation.messages]
      }
      
      if (err.response?.status === 401) {
        error.value = 'Session expirée. Veuillez vous reconnecter.'
      } else {
        error.value = err.response?.data?.message || err.message || 'Erreur lors de l\'envoi du message'
      }
      
      throw err
    }
  }

  const searchUsers = async (term = '') => {
    // Gestion du paramètre
    let queryParam = ''
    if (typeof term === 'string') {
      queryParam = term
    } else if (term && typeof term === 'object') {
      if (typeof term.target?.value === 'string') queryParam = term.target.value
      else if (typeof term.value === 'string') queryParam = term.value
      else queryParam = searchQuery.value || ''
    } else {
      queryParam = searchQuery.value || ''
    }

    if (!queryParam.trim()) {
      return availableUsers.value.filter(u => u.isOnline)
    }

    try {
      console.log('🔍 Recherche utilisateurs:', queryParam)
      console.log('📤 Token:', localStorage.getItem('token')?.substring(0, 20) + '...')
      
      const response = await api.get('/messages/users/list', {
        params: { search: queryParam }
      })
      
      console.log('✅ Résultats recherche:', response.data)
      return response.data.data || []
    } catch (err) {
      console.error('❌ Erreur searchUsers:', err)
      
      if (err.response?.status === 401) {
        console.log('🚫 Token invalide ou expiré')
        error.value = 'Session expirée. Veuillez vous reconnecter.'
      } else {
        error.value = err.response?.data?.message || 'Erreur lors de la recherche'
      }
      
      return []
    }
  }

  const startConversationWithUser = (user) => {
    const existingConv = conversations.value.find(c => c.id === user.id)
    
    if (existingConv) {
      return existingConv
    }
    
    const newConversation = {
      id: user.id,
      name: user.name,
      avatar: user.avatar || user.profile_picture || null,
      lastMessage: '',
      time: '',
      lastMessageTime: null,
      unreadCount: 0,
      isOnline: user.isOnline || false,
      messages: [],
      userId: user.id
    }
    
    conversations.value.unshift(newConversation)
    return newConversation
  }

  const handleIncomingMessage = (message) => {
    try {
      const senderId = message.sender_id || message.sender?.id || message.senderId
      const receiverId = message.receiver_id || message.receiver?.id || message.receiverId

      let convIndex = conversations.value.findIndex(c =>
        c.id === senderId || c.id === receiverId ||
        c.userId === senderId || c.userId === receiverId
      )

      const meId = currentUserId.value || parseInt(localStorage.getItem('user_id') || '0')
      
      const newMsg = {
        id: message.id,
        content: message.content || message.body || '',
        time: message.created_at ? new Date(message.created_at).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }) : '',
        isSender: !!(senderId && senderId === meId),
        isRead: !!message.is_read
      }

      if (convIndex === -1) {
        const participantId = senderId || receiverId
        if (!participantId) return

        const participant = message.sender || message.receiver || { 
          id: participantId, 
          name: message.sender_name || message.sender?.name || 'Utilisateur' 
        }

        const newConversation = {
          id: participantId,
          userId: participantId,
          name: participant.name || 'Utilisateur',
          avatar: participant.avatar || participant.profile_picture || null,
          lastMessage: newMsg.content,
          lastMessageTime: message.created_at || new Date().toISOString(),
          time: newMsg.time,
          unreadCount: newMsg.isSender ? 0 : 1,
          isOnline: false,
          messages: [newMsg]
        }

        conversations.value.unshift(newConversation)
      } else {
        const conv = conversations.value[convIndex]
        conv.messages = conv.messages || []

        if (!conv.messages.some(m => m.id === newMsg.id)) {
          conv.messages.push(newMsg)
        }

        conv.lastMessage = newMsg.content
        conv.lastMessageTime = message.created_at || new Date().toISOString()
        conv.time = newMsg.time

        if (meId && receiverId && parseInt(receiverId) === meId) {
          conv.unreadCount = (conv.unreadCount || 0) + 1
        }

        conversations.value.splice(convIndex, 1)
        conversations.value.unshift(conv)
      }
    } catch (err) {
      console.error('Erreur handleIncomingMessage:', err)
    }
  }

  const markConversationAsRead = (conversationId) => {
    const conversation = conversations.value.find(c => c.id === conversationId)
    if (conversation) {
      conversation.unreadCount = 0
      if (conversation.messages) {
        conversation.messages.forEach(message => {
          if (!message.isSender && !message.isRead) {
            message.isRead = true
          }
        })
      }
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
    availableUsers,
    loading,
    usersLoading,
    error,
    searchQuery,
    
    // Computed
    filteredUsers,
    sortedConversations,
    
    // Actions
    fetchConversations,
    fetchAvailableUsers,
    fetchConversationMessages,
    sendMessage,
    searchUsers,
    startConversationWithUser,
    handleIncomingMessage,
    setCurrentUser,
    markConversationAsRead,
    deleteConversation
  }
})