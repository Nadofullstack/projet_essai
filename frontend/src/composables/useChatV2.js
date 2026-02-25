import { ref, computed, onBeforeUnmount } from 'vue';
import {
  initializeWebSocket,
  subscribeToConversation,
  disconnectWebSocket,
  isWebSocketConnected,
  getConversationKey,
} from '@/services/websocket';
import api from '@/services/api';
import { useMessagesStore } from '@/stores/messages'

export function useChat() {
  const messages = ref([]);
  const currentConversation = ref(null);
  const users = ref([]);
  const currentUserStatus = ref('offline');
  const isLoading = ref(false);
  const error = ref(null);
  const unsubscribe = ref(null);
  const isConnected = ref(false);

  /**
   * Initialiser le chat et charger les messages
   */
  const initChat = async (userId, conversationPartnerId) => {
    try {
      isLoading.value = true;
      error.value = null;

      // Initialiser WebSocket si pas déjà fait
      const token = localStorage.getItem('auth_token');
      if (!isWebSocketConnected()) {
        initializeWebSocket(token);
      }
      isConnected.value = true;

      // Marquer l'utilisateur comme en ligne
      await updateUserStatus('online');

      // Charger les messages existants
      await loadMessages();

      // Charger la liste des utilisateurs
      await loadUsers();

      // S'abonner aux nouveaux messages
      subscribeToNewMessages(userId, conversationPartnerId);

      currentConversation.value = {
        userId,
        conversationPartnerId,
      };
    } catch (err) {
      error.value = err.message;
      console.error('Erreur lors de l\'initialisation du chat:', err);
    } finally {
      isLoading.value = false;
    }
  };

  /**
   * Charger les messages d'une conversation
   */
  const loadMessages = async () => {
    try {
      const response = await api.get('/messages');
      if (response.data.success) {
        messages.value = response.data.data || [];
      }
    } catch (err) {
      console.error('Erreur lors du chargement des messages:', err);
      throw err;
    }
  };

  /**
   * Charger la liste des utilisateurs
   */
  const loadUsers = async () => {
    try {
      const response = await api.get('/messages/users/status');
      if (response.data.success) {
        users.value = response.data.data || [];
      }
    } catch (err) {
      console.error('Erreur lors du chargement des utilisateurs:', err);
    }
  };

  /**
   * S'abonner aux nouveaux messages via WebSocket
   */
  const subscribeToNewMessages = (userId, conversationPartnerId) => {
    // Se désabonner de la conversation précédente si nécessaire
    if (unsubscribe.value) {
      unsubscribe.value();
    }

    // S'abonner à la nouvelle conversation
    unsubscribe.value = subscribeToConversation(
      userId,
      conversationPartnerId,
      onMessageReceived
    );
  };

  /**
   * Callback quand un message est reçu
   */
  const onMessageReceived = (messageData) => {
    try {
      // Mettre à jour le store de conversations pour refléter le nouveau message
      const messagesStore = useMessagesStore()
      if (typeof messagesStore.handleIncomingMessage === 'function') {
        messagesStore.handleIncomingMessage(messageData)
      }

      // Mettre aussi à jour le buffer local de messages (si nécessaire)
      const exists = messages.value.some((m) => m.id === messageData.id)
      if (!exists) messages.value.push(messageData)
    } catch (err) {
      console.error('Erreur onMessageReceived:', err)
    }
  };

  /**
   * Envoyer un message
   */
  const sendMessage = async (content, receiverId, type = 'text', attachment = null) => {
    try {
      isLoading.value = true;
      error.value = null;

      const formData = new FormData();
      formData.append('content', content);
      formData.append('type', type);
      formData.append('receiver_id', receiverId);

      if (attachment) {
        formData.append('attachment', attachment);
      }

      const response = await api.post('/messages', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      if (response.data.success) {
        // Le message sera ajouté via WebSocket
        console.log('Message envoyé avec succès');
        return response.data.data;
      } else {
        throw new Error(response.data.message || 'Erreur lors de l\'envoi du message');
      }
    } catch (err) {
      error.value = err.message;
      console.error('Erreur lors de l\'envoi du message:', err);
      throw err;
    } finally {
      isLoading.value = false;
    }
  };

  /**
   * Marquer un message comme lu et nouveau = false
   */
  const markMessageAsRead = async (messageId) => {
    try {
      const response = await api.put(`/messages/${messageId}/read`);
      if (response.data.success) {
        // Mettre à jour le message localement
        const message = messages.value.find((m) => m.id === messageId);
        if (message) {
          message.is_read = true;
          message.is_new = false;
        }
      }
    } catch (err) {
      console.error('Erreur lors du marquage du message comme lu:', err);
    }
  };

  /**
   * Récupérer les conversations
   */
  const getConversations = async () => {
    try {
      const response = await api.get('/messages/conversations');
      if (response.data.success) {
        return response.data.data || [];
      }
    } catch (err) {
      console.error('Erreur lors de la récupération des conversations:', err);
      throw err;
    }
  };

  /**
   * Récupérer les messages non lus
   */
  const getUnreadMessages = async () => {
    try {
      const response = await api.get('/messages/unread');
      if (response.data.success) {
        return response.data.data;
      }
    } catch (err) {
      console.error('Erreur lors de la récupération des messages non lus:', err);
      throw err;
    }
  };

  /**
   * Supprimer un message
   */
  const deleteMessage = async (messageId) => {
    try {
      const response = await api.delete(`/messages/${messageId}`);
      if (response.data.success) {
        // Retirer le message de la liste locale
        const index = messages.value.findIndex((m) => m.id === messageId);
        if (index > -1) {
          messages.value.splice(index, 1);
        }
      }
    } catch (err) {
      console.error('Erreur lors de la suppression du message:', err);
      throw err;
    }
  };

  /**
   * Supprimer tous les messages
   */
  const deleteAllMessages = async () => {
    try {
      const response = await api.delete('/messages/delete-all');
      if (response.data.success) {
        messages.value = [];
        return response.data;
      }
    } catch (err) {
      console.error('Erreur lors de la suppression de tous les messages:', err);
      throw err;
    }
  };

  /**
   * Supprimer les anciens messages
   */
  const deleteOldMessages = async (daysOld, confirm = true) => {
    try {
      const response = await api.delete('/messages/delete-old', {
        data: {
          days_old: daysOld,
          confirm: confirm
        }
      });
      if (response.data.success) {
        // Recharger les messages
        await loadMessages();
        return response.data;
      }
    } catch (err) {
      console.error('Erreur lors de la suppression des anciens messages:', err);
      throw err;
    }
  };

  /**
   * Mettre à jour le statut en ligne de l'utilisateur
   */
  const updateUserStatus = async (status) => {
    try {
      const response = await api.post('/messages/user/status', {
        online_status: status
      });
      if (response.data.success) {
        currentUserStatus.value = response.data.data.online_status;
      }
    } catch (err) {
      console.error('Erreur lors de la mise à jour du statut:', err);
    }
  };

  /**
   * Obtenir un utilisateur spécifique
   */
  const getUser = (userId) => {
    return users.value.find(u => u.id === userId);
  };

  /**
   * Compter les messages non lus
   */
  const unreadCount = computed(() => {
    return messages.value.filter((m) => !m.is_read).length;
  });

  /**
   * Compter les nouveaux messages
   */
  const newMessagesCount = computed(() => {
    return messages.value.filter((m) => m.is_new && !m.is_read).length;
  });

  /**
   * Filtrer les messages par utilisateur
   */
  const getMessagesByUser = computed(() => {
    return (userId) => messages.value.filter((m) => m.sender_id === userId || m.receiver_id === userId);
  });

  /**
   * Nettoyer les ressources
   */
  onBeforeUnmount(() => {
    if (unsubscribe.value) {
      unsubscribe.value();
    }
    // Marquer comme hors ligne avant de quitter
    updateUserStatus('offline');
  });

  return {
    // État
    messages,
    currentConversation,
    users,
    currentUserStatus,
    isLoading,
    error,
    isConnected,
    unreadCount,
    newMessagesCount,

    // Méthodes
    initChat,
    loadMessages,
    loadUsers,
    sendMessage,
    markMessageAsRead,
    getConversations,
    getUnreadMessages,
    deleteMessage,
    deleteAllMessages,
    deleteOldMessages,
    updateUserStatus,
    getUser,
    getMessagesByUser,
    subscribeToNewMessages,

    // Utilitaires
    getConversationKey,
    disconnectWebSocket,
  };
}
