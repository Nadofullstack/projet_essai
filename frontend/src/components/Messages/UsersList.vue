<template>
  <div class="users-list">
    <div class="users-header">
      <h3>Utilisateurs</h3>
      <div class="status-indicator">
        <span :class="`dot ${currentUserStatus}`"></span>
        {{ currentUserStatus === 'online' ? 'En ligne' : 'Hors ligne' }}
      </div>
    </div>

    <!-- Barre de recherche -->
    <div class="search-container">
      <input 
        v-model="searchQuery"
        type="text" 
        placeholder="Rechercher un utilisateur..."
        class="search-input"
      />
    </div>

    <!-- Filtre de statut -->
    <div class="filter-buttons">
      <button 
        @click="filterStatus = 'all'"
        :class="['filter-btn', { active: filterStatus === 'all' }]"
      >
        Tous ({{ users.length }})
      </button>
      <button 
        @click="filterStatus = 'online'"
        :class="['filter-btn', { active: filterStatus === 'online' }]"
      >
        En ligne ({{ onlineCount }})
      </button>
      <button 
        @click="filterStatus = 'offline'"
        :class="['filter-btn', { active: filterStatus === 'offline' }]"
      >
        Hors ligne ({{ offlineCount }})
      </button>
    </div>

    <!-- Liste des utilisateurs -->
    <div class="users-items">
      <div 
        v-for="user in filteredUsers" 
        :key="user.id"
        class="user-item"
        :class="`status-${user.online_status}`"
      >
        <div class="user-avatar">
          <img 
            v-if="user.profile_picture"
            :src="user.profile_picture" 
            :alt="user.name"
            class="avatar-img"
          />
          <div v-else class="avatar-placeholder">
            {{ getInitials(user.name) }}
          </div>
          <span 
            class="status-dot"
            :class="user.online_status"
          ></span>
        </div>

        <div class="user-details">
          <p class="user-name">{{ user.name }}</p>
          <p class="user-status">
            <span v-if="user.online_status === 'online'" class="online-badge">
              ● En ligne
            </span>
            <span v-else class="offline-badge">
              ● Hors ligne depuis {{ formatLastSeen(user.last_seen_at) }}
            </span>
          </p>
        </div>

        <div class="user-actions">
          <button 
            @click="$emit('select-user', user)"
            class="action-btn message-btn"
            title="Envoyer un message"
          >
            💬
          </button>
        </div>
      </div>

      <!-- Message vide -->
      <div v-if="filteredUsers.length === 0" class="empty-state">
        <p>Aucun utilisateur trouvé</p>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent } from 'vue';
import api from '@/services/api';

export default defineComponent({
  name: 'UsersList',
  props: {
    currentUserStatus: {
      type: String,
      default: 'offline'
    }
  },
  emits: ['select-user'],
  data() {
    return {
      users: [],
      searchQuery: '',
      filterStatus: 'all',
      isLoading: false,
      error: null
    };
  },
  computed: {
    onlineCount() {
      return this.users.filter(u => u.online_status === 'online').length;
    },
    offlineCount() {
      return this.users.filter(u => u.online_status === 'offline').length;
    },
    filteredUsers() {
      let filtered = this.users;

      // Filtrer par statut
      if (this.filterStatus !== 'all') {
        filtered = filtered.filter(u => u.online_status === this.filterStatus);
      }

      // Filtrer par recherche
      if (this.searchQuery.trim()) {
        const query = this.searchQuery.toLowerCase();
        filtered = filtered.filter(u => 
          u.name.toLowerCase().includes(query) ||
          u.email.toLowerCase().includes(query)
        );
      }

      return filtered;
    }
  },
  mounted() {
    this.loadUsers();
    // Recharger les statuts toutes les 10 secondes
    this.refreshInterval = setInterval(() => {
      this.loadUsers();
    }, 10000);
  },
  beforeUnmount() {
    if (this.refreshInterval) {
      clearInterval(this.refreshInterval);
    }
  },
  methods: {
    async loadUsers() {
      try {
        this.isLoading = true;
        this.error = null;
        
        const response = await api.get('/messages/users/status');
        if (response.data.success) {
          this.users = response.data.data || [];
        }
      } catch (err) {
        this.error = 'Erreur lors du chargement des utilisateurs';
        console.error('Erreur loadUsers:', err);
      } finally {
        this.isLoading = false;
      }
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
    },
    getInitials(name) {
      return name
        .split(' ')
        .map(part => part[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
    }
  }
});
</script>

<style scoped>
.users-list {
  display: flex;
  flex-direction: column;
  height: 100%;
  background: rgba(15, 23, 42, 0.8);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  overflow: hidden;
}

.users-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(30, 41, 59, 0.5);
}

.users-header h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
  color: #ffffff;
}

.status-indicator {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: #9ca3af;
}

.status-indicator .dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  display: inline-block;
}

.status-indicator .dot.online {
  background: #22c55e;
  box-shadow: 0 0 4px rgba(34, 197, 94, 0.6);
}

.status-indicator .dot.offline {
  background: #6b7280;
}

.search-container {
  padding: 12px 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.search-input {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.05);
  color: #ffffff;
  font-size: 13px;
  transition: all 0.2s ease;
}

.search-input::placeholder {
  color: #9ca3af;
}

.search-input:focus {
  outline: none;
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(59, 130, 246, 0.5);
  box-shadow: 0 0 8px rgba(59, 130, 246, 0.1);
}

.filter-buttons {
  display: flex;
  gap: 8px;
  padding: 12px 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  flex-wrap: wrap;
}

.filter-btn {
  padding: 6px 12px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.05);
  color: #9ca3af;
  font-size: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.filter-btn:hover {
  border-color: rgba(255, 255, 255, 0.3);
  background: rgba(255, 255, 255, 0.08);
  color: #ffffff;
}

.filter-btn.active {
  background: rgba(59, 130, 246, 0.2);
  border-color: rgba(59, 130, 246, 0.5);
  color: #3b82f6;
}

.users-items {
  flex: 1;
  overflow-y: auto;
  padding: 8px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.user-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.1);
  cursor: pointer;
  transition: all 0.2s ease;
}

.user-item:hover {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.2);
}

.user-item.status-online {
  border-left: 3px solid #22c55e;
}

.user-item.status-offline {
  border-left: 3px solid #6b7280;
  opacity: 0.7;
}

.user-avatar {
  position: relative;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
}

.avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  color: #ffffff;
  font-weight: 600;
  font-size: 14px;
}

.status-dot {
  position: absolute;
  bottom: 0;
  right: 0;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  border: 2px solid rgba(15, 23, 42, 0.8);
}

.status-dot.online {
  background: #22c55e;
  box-shadow: 0 0 6px rgba(34, 197, 94, 0.8);
}

.status-dot.offline {
  background: #6b7280;
}

.user-details {
  flex: 1;
  min-width: 0;
}

.user-name {
  margin: 0 0 4px 0;
  font-weight: 500;
  font-size: 14px;
  color: #ffffff;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-status {
  margin: 0;
  font-size: 12px;
  color: #9ca3af;
}

.online-badge,
.offline-badge {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 11px;
  white-space: nowrap;
}

.online-badge {
  color: #22c55e;
  font-weight: 500;
}

.offline-badge {
  color: #6b7280;
}

.user-actions {
  display: flex;
  gap: 8px;
}

.action-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  padding: 0;
  border: 1px solid rgba(59, 130, 246, 0.3);
  border-radius: 6px;
  background: rgba(59, 130, 246, 0.1);
  cursor: pointer;
  font-size: 14px;
  transition: all 0.2s ease;
}

.action-btn:hover {
  background: rgba(59, 130, 246, 0.2);
  border-color: rgba(59, 130, 246, 0.5);
  transform: scale(1.05);
}

.message-btn {
  border-color: rgba(34, 197, 94, 0.3);
  background: rgba(34, 197, 94, 0.1);
}

.message-btn:hover {
  background: rgba(34, 197, 94, 0.2);
  border-color: rgba(34, 197, 94, 0.5);
}

.empty-state {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: #9ca3af;
  font-size: 14px;
}

/* Scrollbar personnalisée */
.users-items::-webkit-scrollbar {
  width: 6px;
}

.users-items::-webkit-scrollbar-track {
  background: transparent;
}

.users-items::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 3px;
}

.users-items::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.2);
}
</style>
