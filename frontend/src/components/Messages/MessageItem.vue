<template>
  <div class="message-item" :class="messageClasses">
    <div class="message-content">
      <!-- Indicateur visuel pour nouveau message -->
      <div v-if="message.is_new && !isSender" class="new-message-indicator" title="Nouveau message">
        <span class="new-badge">●</span>
      </div>

      <!-- Contenu du message -->
      <div class="message-body">
        <p class="message-text">{{ message.content }}</p>
        <span class="message-time">{{ formatTime(message.created_at) }}</span>
      </div>

      <!-- Statut de lecture -->
      <div v-if="isSender" class="message-status">
        <span v-if="message.is_read" class="read-status" title="Lus">✓✓</span>
        <span v-else class="sent-status" title="Envoyés">✓</span>
      </div>
    </div>

    <!-- Actions du message -->
    <div class="message-actions">
      <button 
        v-if="!isSender"
        @click="markAsRead" 
        class="action-btn read-btn"
        :disabled="message.is_read"
        title="Marquer comme lu"
      >
        📖
      </button>
      <button 
        @click="deleteMessage" 
        class="action-btn delete-btn"
        title="Supprimer"
      >
        🗑️
      </button>
    </div>
  </div>
</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'MessageItem',
  props: {
    message: {
      type: Object,
      required: true
    },
    isSender: {
      type: Boolean,
      default: false
    }
  },
  emits: ['delete', 'mark-as-read'],
  computed: {
    messageClasses() {
      return {
        'is-sender': this.isSender,
        'is-receiver': !this.isSender,
        'is-new': this.message.is_new && !this.isSender,
        'is-read': this.message.is_read
      };
    }
  },
  methods: {
    formatTime(date) {
      if (!date) return '';
      const d = new Date(date);
      return d.toLocaleTimeString('fr-FR', { 
        hour: '2-digit', 
        minute: '2-digit'
      });
    },
    deleteMessage() {
      if (confirm('Êtes-vous sûr de vouloir supprimer ce message ?')) {
        this.$emit('delete', this.message.id);
      }
    },
    markAsRead() {
      if (!this.message.is_read) {
        this.$emit('mark-as-read', this.message.id);
      }
    }
  }
});
</script>

<style scoped>
.message-item {
  display: flex;
  gap: 12px;
  margin-bottom: 12px;
  padding: 8px;
  border-radius: 8px;
  transition: background-color 0.2s ease;
}

.message-item.is-sender {
  justify-content: flex-end;
}

.message-item.is-receiver {
  justify-content: flex-start;
}

.message-item.is-new {
  background: rgba(34, 197, 94, 0.05);
  border: 1px solid rgba(34, 197, 94, 0.2);
}

.message-item:hover {
  background: rgba(255, 255, 255, 0.05);
}

.message-content {
  display: flex;
  align-items: flex-end;
  gap: 8px;
  max-width: 70%;
}

.message-item.is-sender .message-content {
  flex-direction: row-reverse;
}

.new-message-indicator {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 20px;
  height: 20px;
}

.new-badge {
  color: #22c55e;
  font-size: 16px;
  animation: pulse-green 2s infinite;
}

.message-body {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding: 12px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.message-item.is-sender .message-body {
  background: rgba(59, 130, 246, 0.2);
  border-color: rgba(59, 130, 246, 0.3);
}

.message-item.is-receiver .message-body {
  background: rgba(107, 114, 128, 0.1);
  border-color: rgba(107, 114, 128, 0.2);
}

.message-item.is-new .message-body {
  background: rgba(34, 197, 94, 0.1);
  border-color: rgba(34, 197, 94, 0.3);
  box-shadow: 0 0 8px rgba(34, 197, 94, 0.15);
}

.message-text {
  margin: 0;
  color: #ffffff;
  word-wrap: break-word;
  font-size: 14px;
  line-height: 1.4;
}

.message-time {
  font-size: 11px;
  color: #9ca3af;
  display: flex;
  justify-content: flex-end;
}

.message-status {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 20px;
  font-size: 12px;
}

.read-status {
  color: #3b82f6;
  font-weight: bold;
}

.sent-status {
  color: #6b7280;
}

.message-actions {
  display: flex;
  gap: 4px;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.message-item:hover .message-actions {
  opacity: 1;
}

.action-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  padding: 0;
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.05);
  cursor: pointer;
  font-size: 14px;
  transition: all 0.2s ease;
}

.action-btn:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.3);
  transform: scale(1.05);
}

.action-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.read-btn:hover:not(:disabled) {
  border-color: #22c55e;
  background: rgba(34, 197, 94, 0.1);
}

.delete-btn:hover {
  border-color: #ef4444;
  background: rgba(239, 68, 68, 0.1);
}

@keyframes pulse-green {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.6;
    transform: scale(0.8);
  }
}

/* Mode responsive */
@media (max-width: 768px) {
  .message-content {
    max-width: 90%;
  }
}
</style>
