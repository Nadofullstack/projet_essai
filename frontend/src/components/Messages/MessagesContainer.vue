<template>
  <div class="h-screen flex bg-gray-100">
    <!-- Sidebar Messages -->
    <div class="w-80 bg-white shadow-sm border-r border-gray-200 flex flex-col">
      <MessagesList @select-conversation="selectConversation" />
    </div>

    <!-- Chat Window -->
    <div class="flex-1">
      <ChatWindow 
        :conversation="selectedConversation"
        @close="selectedConversation = null"
      />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import MessagesList from '@/components/Messages/MessagesList.vue'
import ChatWindow from '@/components/Messages/ChatWindow.vue'

const selectedConversation = ref(null)

const selectConversation = (conversation) => {
  selectedConversation.value = conversation
}
</script>

<style scoped>
/* Styles généraux */
</style>
