<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Sidebar -->
    <LayoutSidebar :is-collapsed="sidebarCollapsed" @toggle="toggleSidebar" />
    
    <!-- Main Content -->
    <div :class="['transition-all duration-300', sidebarCollapsed ? 'ml-16' : 'ml-64']">
      <!-- Header -->
      <LayoutHeader 
        @toggle-sidebar="toggleSidebar"
        :unread-count="unreadMessages"
        :user="currentUser"
      />
      
      <!-- Page Content -->
      <main class="p-6">
        <slot></slot>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import LayoutSidebar from './Sidebar.vue'
import LayoutHeader from './Header.vue'
import { useDashboardStore } from '@/stores/dashboard'

const dashboardStore = useDashboardStore()

// État local
const sidebarCollapsed = ref(false)

// Données du store
const currentUser = computed(() => dashboardStore.currentUser)
const unreadMessages = computed(() => dashboardStore.unreadMessages)

// Méthodes
const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
}
</script>

<style scoped>
/* Transitions smooth */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Scrollbar personnalisée */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #a1a1a1;
}
</style>
