<template>
  <div class="messages-cleanup">
    <div class="cleanup-header">
      <h3>⚙️ Gestion des Messages</h3>
      <p class="subtitle">Supprimez définitivement vos messages enregistrés</p>
    </div>

    <!-- Options de suppression -->
    <div class="cleanup-options">
      <!-- Option 1: Supprimer tous les messages -->
      <div class="cleanup-card delete-all">
        <div class="card-header">
          <h4>Supprimer Tous les Messages</h4>
          <span class="danger-badge">⚠️ DANGER</span>
        </div>
        <p class="card-description">
          Supprime définitivement TOUS vos messages (envoyés et reçus). 
          <strong>Cette action est irréversible.</strong>
        </p>
        <button 
          @click="openDeleteAllConfirm"
          class="btn btn-danger"
          :disabled="isLoading || !canDeleteAll"
        >
          Supprimer tous les messages
        </button>
      </div>

      <!-- Option 2: Supprimer les anciens messages -->
      <div class="cleanup-card delete-old">
        <div class="card-header">
          <h4>Supprimer les Anciens Messages</h4>
          <span class="info-badge">ℹ️ INFO</span>
        </div>
        <p class="card-description">
          Supprime définitivement les messages plus anciens qu'une date spécifiée.
        </p>

        <div class="form-group">
          <label for="days-old">Nombre de jours :</label>
          <div class="input-group">
            <input 
              id="days-old"
              v-model.number="daysOld"
              type="number"
              min="1"
              max="3650"
              class="input"
              placeholder="Exemple: 30"
            />
            <span class="info-text">
              Supprimera les messages plus vieux que {{ formatDate(calculateDate()) }}
            </span>
          </div>
        </div>

        <button 
          @click="openDeleteOldConfirm"
          class="btn btn-warning"
          :disabled="isLoading || daysOld < 1 || daysOld > 3650"
        >
          Supprimer les messages de plus de {{ daysOld }} jours
        </button>
      </div>

      <!-- Statistiques -->
      <div class="cleanup-card stats-card">
        <div class="card-header">
          <h4>📊 Statistiques</h4>
        </div>
        <div class="stats-grid">
          <div class="stat-item">
            <span class="stat-label">Total de messages :</span>
            <span class="stat-value">{{ totalMessages }}</span>
          </div>
          <div class="stat-item">
            <span class="stat-label">Messages non lus :</span>
            <span class="stat-value unread">{{ unreadMessages }}</span>
          </div>
          <div class="stat-item">
            <span class="stat-label">Nouveaux messages :</span>
            <span class="stat-value new">{{ newMessages }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de confirmation -->
    <div v-if="showDeleteConfirm" class="modal-overlay">
      <div class="modal">
        <div class="modal-header">
          <h3>⚠️ Confirmer la Suppression</h3>
          <button @click="closeConfirm" class="close-btn">✕</button>
        </div>

        <div class="modal-body">
          <p v-if="deleteType === 'all'" class="confirm-text danger">
            Vous êtes sur le point de supprimer <strong>tous</strong> vos messages.
            Cette action est <strong>IRRÉVERSIBLE</strong>.
          </p>
          <p v-else class="confirm-text warning">
            Vous êtes sur le point de supprimer tous les messages de plus de 
            <strong>{{ daysOld }} jours</strong> (avant {{ formatDate(calculateDate()) }}).
            Cette action est <strong>irréversible</strong>.
          </p>

          <!-- Confirmation textuelle -->
          <div class="confirmation-input">
            <label>Tapez "<strong>confirmer</strong>" pour valider :</label>
            <input 
              v-model="confirmText"
              type="text"
              class="input"
              placeholder="confirmer"
              @keyup.enter="proceedDelete"
            />
          </div>

          <div class="modal-actions">
            <button 
              @click="closeConfirm"
              class="btn btn-secondary"
            >
              Annuler
            </button>
            <button 
              @click="proceedDelete"
              class="btn btn-danger"
              :disabled="confirmText !== 'confirmer' || isLoading"
            >
              {{ isLoading ? 'Suppression en cours...' : 'Supprimer définitivement' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Message de succès -->
    <div v-if="successMessage" class="success-alert">
      <span>✓ {{ successMessage }}</span>
      <button @click="successMessage = ''" class="close-btn">✕</button>
    </div>

    <!-- Message d'erreur -->
    <div v-if="errorMessage" class="error-alert">
      <span>✗ {{ errorMessage }}</span>
      <button @click="errorMessage = ''" class="close-btn">✕</button>
    </div>
  </div>
</template>

<script>
import { defineComponent } from 'vue';
import api from '@/services/api';

export default defineComponent({
  name: 'MessagesCleanup',
  data() {
    return {
      daysOld: 30,
      isLoading: false,
      showDeleteConfirm: false,
      deleteType: null,
      confirmText: '',
      successMessage: '',
      errorMessage: '',
      totalMessages: 0,
      unreadMessages: 0,
      newMessages: 0,
      canDeleteAll: true
    };
  },
  mounted() {
    this.loadStats();
  },
  methods: {
    async loadStats() {
      try {
        const response = await api.get('/messages');
        if (response.data.success) {
          const messages = response.data.data || [];
          this.totalMessages = messages.length;
          this.unreadMessages = messages.filter(m => !m.is_read).length;
          this.newMessages = messages.filter(m => m.is_new).length;
        }
      } catch (err) {
        console.error('Erreur loadStats:', err);
      }
    },
    openDeleteAllConfirm() {
      this.deleteType = 'all';
      this.confirmText = '';
      this.showDeleteConfirm = true;
    },
    openDeleteOldConfirm() {
      this.deleteType = 'old';
      this.confirmText = '';
      this.showDeleteConfirm = true;
    },
    closeConfirm() {
      this.showDeleteConfirm = false;
      this.confirmText = '';
    },
    async proceedDelete() {
      if (this.confirmText !== 'confirmer') {
        this.errorMessage = 'Veuillez confirmer en tapant "confirmer"';
        return;
      }

      try {
        this.isLoading = true;
        this.errorMessage = '';
        this.successMessage = '';

        let response;
        if (this.deleteType === 'all') {
          response = await api.delete('/messages/delete-all');
        } else {
          response = await api.delete('/messages/delete-old', {
            data: {
              days_old: this.daysOld,
              confirm: true
            }
          });
        }

        if (response.data.success) {
          this.successMessage = response.data.message;
          this.closeConfirm();
          await this.loadStats();
          
          // Masquer le message de succès après 5 secondes
          setTimeout(() => {
            this.successMessage = '';
          }, 5000);
        }
      } catch (err) {
        this.errorMessage = err.response?.data?.message || 'Erreur lors de la suppression';
        console.error('Erreur proceedDelete:', err);
      } finally {
        this.isLoading = false;
      }
    },
    calculateDate() {
      const date = new Date();
      date.setDate(date.getDate() - this.daysOld);
      return date;
    },
    formatDate(date) {
      return date.toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    }
  }
});
</script>

<style scoped>
.messages-cleanup {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}

.cleanup-header {
  margin-bottom: 32px;
}

.cleanup-header h3 {
  margin: 0 0 8px 0;
  font-size: 24px;
  color: #ffffff;
}

.subtitle {
  margin: 0;
  color: #9ca3af;
  font-size: 14px;
}

.cleanup-options {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
  margin-bottom: 20px;
}

.cleanup-card {
  padding: 20px;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(30, 41, 59, 0.5);
  backdrop-filter: blur(10px);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 12px;
  gap: 12px;
}

.card-header h4 {
  margin: 0;
  font-size: 16px;
  color: #ffffff;
}

.danger-badge,
.info-badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 600;
  white-space: nowrap;
}

.danger-badge {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.info-badge {
  background: rgba(59, 130, 246, 0.1);
  color: #3b82f6;
  border: 1px solid rgba(59, 130, 246, 0.3);
}

.card-description {
  margin: 0 0 16px 0;
  font-size: 13px;
  color: #d1d5db;
  line-height: 1.5;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-size: 13px;
  font-weight: 500;
  color: #e5e7eb;
}

.input-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.input {
  padding: 8px 12px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.05);
  color: #ffffff;
  font-size: 13px;
  transition: all 0.2s ease;
}

.input:focus {
  outline: none;
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(59, 130, 246, 0.5);
}

.info-text {
  display: block;
  font-size: 11px;
  color: #9ca3af;
  font-style: italic;
}

.btn {
  width: 100%;
  padding: 10px 16px;
  border: none;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-danger {
  background: rgba(239, 68, 68, 0.2);
  color: #ef4444;
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.btn-danger:hover:not(:disabled) {
  background: rgba(239, 68, 68, 0.3);
  border-color: rgba(239, 68, 68, 0.5);
  color: #ff6b6b;
}

.btn-warning {
  background: rgba(249, 115, 22, 0.2);
  color: #f97316;
  border: 1px solid rgba(249, 115, 22, 0.3);
}

.btn-warning:hover:not(:disabled) {
  background: rgba(249, 115, 22, 0.3);
  border-color: rgba(249, 115, 22, 0.5);
}

.btn-secondary {
  background: rgba(107, 114, 128, 0.2);
  color: #9ca3af;
  border: 1px solid rgba(107, 114, 128, 0.3);
}

.btn-secondary:hover:not(:disabled) {
  background: rgba(107, 114, 128, 0.3);
  border-color: rgba(107, 114, 128, 0.5);
}

.stats-card {
  grid-column: 1 / -1;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 16px;
}

.stat-item {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding: 12px;
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.stat-label {
  font-size: 12px;
  color: #9ca3af;
  font-weight: 500;
}

.stat-value {
  font-size: 18px;
  font-weight: 600;
  color: #ffffff;
}

.stat-value.unread {
  color: #f97316;
}

.stat-value.new {
  color: #22c55e;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal {
  background: rgba(15, 23, 42, 0.95);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  max-width: 500px;
  width: 100%;
  overflow: hidden;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.modal-header h3 {
  margin: 0;
  font-size: 18px;
  color: #ffffff;
}

.close-btn {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  background: transparent;
  color: #9ca3af;
  font-size: 18px;
  cursor: pointer;
  transition: color 0.2s ease;
}

.close-btn:hover {
  color: #ffffff;
}

.modal-body {
  padding: 20px;
}

.confirm-text {
  margin: 0 0 16px 0;
  padding: 12px;
  border-radius: 6px;
  font-size: 13px;
  line-height: 1.6;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.confirm-text.danger {
  background: rgba(239, 68, 68, 0.1);
  border-color: rgba(239, 68, 68, 0.3);
  color: #fca5a5;
}

.confirm-text.warning {
  background: rgba(249, 115, 22, 0.1);
  border-color: rgba(249, 115, 22, 0.3);
  color: #fed7aa;
}

.confirmation-input {
  margin: 16px 0;
}

.confirmation-input label {
  display: block;
  margin-bottom: 8px;
  font-size: 12px;
  color: #d1d5db;
}

.modal-actions {
  display: flex;
  gap: 12px;
  margin-top: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  padding-top: 20px;
}

.modal-actions .btn {
  flex: 1;
  margin: 0;
}

/* Alertes */
.success-alert,
.error-alert {
  position: fixed;
  bottom: 20px;
  right: 20px;
  padding: 12px 16px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 13px;
  z-index: 999;
  animation: slideIn 0.3s ease;
}

.success-alert {
  background: rgba(34, 197, 94, 0.2);
  border: 1px solid rgba(34, 197, 94, 0.3);
  color: #86efac;
}

.error-alert {
  background: rgba(239, 68, 68, 0.2);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #fca5a5;
}

@keyframes slideIn {
  from {
    transform: translateX(400px);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

@media (max-width: 768px) {
  .cleanup-options {
    grid-template-columns: 1fr;
  }

  .cleanup-card {
    grid-column: 1;
  }

  .stats-card {
    grid-column: 1;
  }
}
</style>
