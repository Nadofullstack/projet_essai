<template>
  <div class="user-status-badge">
    <div class="status-container">
      <div 
        class="status-indicator" 
        :class="statusClass"
        :title="`${user.online_status === 'online' ? 'En ligne' : 'Hors ligne'}`"
      >
        <span v-if="user.online_status === 'online'" class="online-dot"></span>
        <span v-else class="offline-dot"></span>
      </div>
      
      <div class="user-info">
        <p class="user-name">{{ user.name }}</p>
        <p class="user-status">
          <span v-if="user.online_status === 'online'" class="status-text online">
            En ligne
          </span>
          <span v-else class="status-text offline">
            Hors ligne
          </span>
          <span v-if="user.last_seen_at" class="last-seen">
            {{ formatLastSeen(user.last_seen_at) }}
          </span>
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'UserStatusBadge',
  props: {
    user: {
      type: Object,
      required: true,
      default: () => ({
        id: null,
        name: 'Unknown',
        online_status: 'offline',
        last_seen_at: null
      })
    }
  },
  computed: {
    statusClass() {
      return {
        'is-online': this.user.online_status === 'online',
        'is-offline': this.user.online_status === 'offline'
      };
    }
  },
  methods: {
    formatLastSeen(date) {
      if (!date) return '';
      
      const lastSeenDate = new Date(date);
      const now = new Date();
      const diffMs = now - lastSeenDate;
      const diffMins = Math.floor(diffMs / 60000);
      const diffHours = Math.floor(diffMs / 3600000);
      const diffDays = Math.floor(diffMs / 86400000);

      if (diffMins < 1) return 'à l\'instant';
      if (diffMins < 60) return `il y a ${diffMins}m`;
      if (diffHours < 24) return `il y a ${diffHours}h`;
      if (diffDays < 7) return `il y a ${diffDays}j`;
      
      return lastSeenDate.toLocaleDateString('fr-FR');
    }
  }
});
</script>

<style scoped>
.user-status-badge {
  padding: 8px 12px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.status-container {
  display: flex;
  align-items: center;
  gap: 12px;
}

.status-indicator {
  position: relative;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.status-indicator.is-online {
  background: rgba(34, 197, 94, 0.1);
  border: 2px solid #22c55e;
}

.status-indicator.is-offline {
  background: rgba(107, 114, 128, 0.1);
  border: 2px solid #6b7280;
}

.online-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #22c55e;
  animation: pulse 2s infinite;
}

.offline-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #6b7280;
}

.user-info {
  flex: 1;
  min-width: 0;
}

.user-name {
  margin: 0;
  font-weight: 600;
  font-size: 14px;
  color: #ffffff;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-status {
  margin: 4px 0 0 0;
  font-size: 12px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.status-text {
  font-weight: 500;
}

.status-text.online {
  color: #22c55e;
}

.status-text.offline {
  color: #9ca3af;
}

.last-seen {
  color: #6b7280;
  font-style: italic;
  font-size: 11px;
}

@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4);
  }
  70% {
    box-shadow: 0 0 0 6px rgba(34, 197, 94, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
  }
}
</style>
