<script setup>
 import { ref } from 'vue'
const emit = defineEmits(['record', 'save'])

const isRecording = ref(false)
const isPaused = ref(false)
const recordingTime = ref(0)
const recordingTimer = ref(null)

const startRecording = () => {
  isRecording.value = true
  emit('record')
  
  recordingTimer.value = setInterval(() => {
    recordingTime.value++
  }, 1000)
}

const stopRecording = () => {
  isRecording.value = false
  isPaused.value = false
  
  if (recordingTimer.value) {
    clearInterval(recordingTimer.value)
    recordingTimer.value = null
  }
  
  emit('save', { duration: recordingTime.value })
  recordingTime.value = 0
}

const pauseRecording = () => {
  isPaused.value = !isPaused.value
}

const formatTime = (seconds) => {
  const mins = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
}
</script>

<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h3 class="font-semibold text-gray-900 mb-4">Enregistrement audio</h3>
    
    <div class="text-center">
      <!-- Visualisation -->
      <div class="bg-gray-900 rounded-lg p-4 mb-4 h-32 flex items-center justify-center">
        <div v-if="isRecording" class="flex space-x-1">
          <div v-for="i in 20" :key="i" class="w-1 bg-blue-400 rounded-full animate-pulse" 
               :style="{ height: `${Math.random() * 80 + 20}%` }"></div>
        </div>
        <span v-else class="material-symbols-outlined text-gray-400 text-4xl">mic</span>
      </div>
      
      <!-- Timer -->
      <div class="text-2xl font-mono text-gray-900 mb-4">
        {{ formatTime(recordingTime) }}
      </div>
      
      <!-- Controls -->
      <div class="flex justify-center items-center space-x-4">
        <button 
          v-if="isRecording"
          @click="pauseRecording"
          class="p-3 bg-yellow-500 text-white rounded-full hover:bg-yellow-600"
        >
          <span class="material-symbols-outlined">{{ isPaused ? 'play_arrow' : 'pause' }}</span>
        </button>
        
        <button 
          @click="isRecording ? stopRecording() : startRecording()"
          :class="[
            'p-4 rounded-full text-white',
            isRecording ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700'
          ]"
        >
          <span class="material-symbols-outlined text-2xl">
            {{ isRecording ? 'stop' : 'mic' }}
          </span>
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}

.animate-pulse {
  animation: pulse 1s infinite;
}
</style>
