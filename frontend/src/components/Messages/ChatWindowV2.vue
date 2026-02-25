<template>
  <div class="chat-window">
    <!-- En-tête avec statut utilisateur -->
    <div v-if="currentUser" class="chat-header">
      <div class="header-left">
        <UserStatusBadge :user="currentUser" />
      </div>

      <div class="header-actions">
        <button 
          @click="toggleCleanup" 
          class="action-btn cleanup-btn"
          title="Gérer les messages"
        >
          🗑️
        </button>
        <button 
          @click="$emit('close')" 
          class="action-btn close-btn"
          title="Fermer"
        >
          ✕
        </button>
      </div>
    </div>

    <!-- Corps principal: Liste utilisateurs + Messages -->
    <div class="chat-body">
      <!-- Panneau utilisateurs -->
      <div class="users-panel">
        <UsersList 
          :currentUserStatus="currentUserStatus"
          @select-user="selectUser"
        />
      </div>

      <!-- Panneau de messages -->
      <div v-if="selectedUser" class="messages-panel">
        <!-- En-tête de conversation -->
        <div class="conversation-header">
          <UserStatusBadge :user="selectedUser" />
          <div class="header-info">
            <span v-if="selectedUser.online_status === 'online'" class="online-badge">
              ● En ligne
            </span>
            <span v-else class="offline-badge">
              ● Hors ligne depuis {{ formatLastSeen(selectedUser.last_seen_at) }}
            </span>
          </div>
        </div>

        <!-- Liste des messages -->
        <div class="messages-list">
          <div v-if="conversationMessages.length === 0" class="empty-state">
            <p>Aucun message avec cet utilisateur</p>
          </div>

          <MessageItem 
            v-for="message in conversationMessages"
            :key="message.id"
            :message="message"
            :isSender="message.isSender"
            @delete="deleteMessage"
            @mark-as-read="markMessageAsRead"
          />
        </div>

        <!-- Saisie de message -->
        <div class="message-input-container">
          <div class="input-wrapper">
            <input 
              v-model="messageContent"
              type="text"
              placeholder="Écrivez votre message..."
              class="message-input"
              @keyup.enter="sendMessage"
              :disabled="isSending"
            />
            <button 
              @click="sendMessage"
              class="send-btn"
              :disabled="!messageContent.trim() || isSending"
              title="Envoyer (Entrée)"
            >
              {{ isSending ? '⏳' : '📤' }}
            </button>
          </div>
        </div>
      </div>

      <!-- État vide -->
      <div v-else class="empty-panel">
        <div class="empty-content">
          <span class="empty-icon">💬</span>
          <p>Sélectionnez un utilisateur pour commencer</p>
        </div>
      </div>
    </div>

    <!-- Panneau de gestion des messages -->
    <div v-if="showCleanup" class="cleanup-panel">
      <MessagesCleanup />
    </div>
  </div>
</template>

<script>
import { defineComponent } from 'vue';
import { useChat } from '@/composables/useChat';
import UserStatusBadge from './UserStatusBadge.vue';
import UsersList from './UsersList.vue';
import MessageItem from './MessageItem.vue';
import MessagesCleanup from './MessagesCleanup.vue';
import api from '@/services/api';

export default defineComponent({
  name: 'ChatWindow',
  components: {
    UserStatusBadge,
    UsersList,
    MessageItem,
    MessagesCleanup
  },
  emits: ['close'],
  data() {
    return {
      selectedUser: null,
      messageContent: '',
      isSending: false,
      showCleanup: false,
      currentUser: null,
      refreshInterval: null,
      conversationMessagesInternal: []
    };
  },
  computed: {
    ...useChat(),
    conversationMessages() {
      if (!this.selectedUser) return [];
      
      return this.messages
        .filter(m => 
          (m.sender_id === this.selectedUser.id || m.receiver_id === this.selectedUser.id)
        )
        .map(m => ({
          ...m,
          isSender: m.sender_id === this.currentUser?.id
        }))
        .sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
    }
  },
  async mounted() {
    try {
      // Charger les données utilisateur
      const userResponse = await api.get('/user');
      if (userResponse.data.success || userResponse.data.data) {
        this.currentUser = userResponse.data.data;
      }

      // Initialiser le chat
      if (this.currentUser) {
        await this.initChat(this.currentUser.id, null);
      }

      // Rafraîchir les statuts toutes les 10 secondes
      this.refreshInterval = setInterval(() => {
        this.loadUsers();
      }, 10000);

      // Scroller vers le bas quand les messages changent
      this.$watch('conversationMessages', () => {
        this.$nextTick(() => {
          this.scrollToBottom();
        });
      }, { deep: true });
    } catch (err) {
      console.error('Erreur lors du montage du ChatWindow:', err);
    }
  },
  beforeUnmount() {
    if (this.refreshInterval) {
      clearInterval(this.refreshInterval);
    }
    // Marquer comme hors ligne
    this.updateUserStatus('offline');
    this.disconnectWebSocket();
  },
  methods: {
    async selectUser(user) {
      this.selectedUser = user;
      this.messageContent = '';
      
      // Charger les messages de cette conversation
      try {
        const response = await api.get(`/messages/conversation/${user.id}`);
        if (response.data.success) {
          this.conversationMessagesInternal = response.data.data || [];
          
          // Marquer les messages reçus comme lus
          for (const msg of this.conversationMessagesInternal) {
            if (!msg.isSender && !msg.is_read) {
              await this.markMessageAsRead(msg.id);
            }
          }
        }
      } catch (err) {
        console.error('Erreur lors du chargement de la conversation:', err);
      }

      // Scroller vers le bas
      this.$nextTick(() => {
        this.scrollToBottom();
      });
    },
    async sendMessage() {
      if (!this.messageContent.trim() || !this.selectedUser || this.isSending) {
        return;
      }

      try {
        this.isSending = true;
        await this.sendMessage(
          this.messageContent,
          this.selectedUser.id,
          'text'
        );
        this.messageContent = '';
        
        // Scroller vers le bas
        this.$nextTick(() => {
          this.scrollToBottom();
        });
      } catch (err) {
        console.error('Erreur lors de l\'envoi du message:', err);
      } finally {
        this.isSending = false;
      }
    },
    deleteMessage(messageId) {
      this.deleteMessage(messageId);
    },
    markMessageAsRead(messageId) {
      this.markMessageAsRead(messageId);
    },
    scrollToBottom() {
      const messagesPanel = this.$el.querySelector('.messages-list');
      if (messagesPanel) {
        messagesPanel.scrollTop = messagesPanel.scrollHeight;
      }
    },
    toggleCleanup() {
      this.showCleanup = !this.showCleanup;
    },
    formatLastSeen(date) {
      if (!date) return 'récemment';
      
      const lastSeenDate = new Date(date);
      const now = new Date();
      const diffMs = now - lastSeenDate;
      const diffMins = Math.floor(diffMs / 60000);
      const diffHours = Math.floor(diffMs / 3600000);
      const diffDays = Math.floor(diffMs / 86400000);

      if (diffMins < 1) return 'à l\'instant';
      if (diffMins < 60) return `${diffMins}m`;
      if (diffHours < 24) return `${diffHours}h`;
      if (diffDays < 7) return `${diffDays}j`;
      
      return 'longtemps';
    }
  }
});
</script>

<style scoped>
.chat-window {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background: rgba(15, 23, 42, 0.8);
  border: 1px solid rgba(255, 255, 255, 0.1);
  overflow: hidden;
}

.chat-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(30, 41, 59, 0.5);
}

.header-left {
  flex: 1;
}

.header-actions {
  display: flex;
  gap: 8px;
}

.action-btn {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.05);
  color: #ffffff;
  font-size: 18px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.action-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.3);
}

.cleanup-btn:hover {
  background: rgba(239, 68, 68, 0.1);
  border-color: rgba(239, 68, 68, 0.3);
}

.close-btn:hover {
  background: rgba(239, 68, 68, 0.15);
}

.chat-body {
  display: flex;
  flex: 1;
  gap: 0;
  overflow: hidden;
}

.users-panel {
  width: 300px;
  border-right: 1px solid rgba(255, 255, 255, 0.1);
  overflow: auto;
  background: rgba(30, 41, 59, 0.3);
}

.messages-panel {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  background: rgba(15, 23, 42, 0.5);
}

.conversation-header {
  padding: 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(30, 41, 59, 0.3);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-info {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
}

.online-badge {
  color: #22c55e;
  font-weight: 500;
}

.offline-badge {
  color: #9ca3af;
}

.messages-list {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.empty-state {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: #9ca3af;
  font-size: 14px;
}

.message-input-container {
  padding: 16px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(30, 41, 59, 0.3);
}

.input-wrapper {
  display: flex;
  gap: 8px;
  align-items: flex-end;
}

.message-input {
  flex: 1;
  padding: 10px 14px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.05);
  color: #ffffff;
  font-size: 13px;
  resize: none;
  transition: all 0.2s ease;
  min-height: 40px;
  max-height: 120px;
  line-height: 1.4;
}

.message-input::placeholder {
  color: #6b7280;
}

.message-input:focus {
  outline: none;
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(59, 130, 246, 0.5);
  box-shadow: 0 0 12px rgba(59, 130, 246, 0.1);
}

.message-input:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.send-btn {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid rgba(59, 130, 246, 0.3);
  border-radius: 8px;
  background: rgba(59, 130, 246, 0.1);
  color: #3b82f6;
  font-size: 18px;
  cursor: pointer;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.send-btn:hover:not(:disabled) {
  background: rgba(59, 130, 246, 0.2);
  border-color: rgba(59, 130, 246, 0.5);
  transform: scale(1.05);
}

.send-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.empty-panel {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(15, 23, 42, 0.5);
}

.empty-content {
  text-align: center;
  color: #6b7280;
}

.empty-icon {
  display: block;
  font-size: 48px;
  margin-bottom: 16px;
  opacity: 0.6;
}

.cleanup-panel {
  position: absolute;
  top: 0;
  right: 0;
  width: 500px;
  max-width: 90vw;
  height: 100vh;
  background: rgba(15, 23, 42, 0.95);
  border-left: 1px solid rgba(255, 255, 255, 0.1);
  overflow-y: auto;
  z-index: 1000;
}

/* Scrollbar personnalisée */
.messages-list::-webkit-scrollbar {
  width: 6px;
}

.messages-list::-webkit-scrollbar-track {
  background: transparent;
}

.messages-list::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 3px;
}

.messages-list::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.2);
}

@media (max-width: 1024px) {
  .users-panel {
    width: 250px;
  }
}

@media (max-width: 768px) {
  .chat-body {
    flex-direction: column;
  }

  .users-panel {
    width: 100%;
    max-height: 40%;
    border-right: none;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  }

  .messages-panel {
    max-height: 60%;
  }

  .cleanup-panel {
    width: 100%;
    max-width: 100%;
  }
}
</style>
