<script setup>
const emit = defineEmits(['select-program', 'new-program'])

const props = defineProps({
  programs: {
    type: Array,
    default: () => [
      {
        id: 1,
        name: 'Programme Vocal Avancé',
        description: 'Exercices de prononciation et articulation',
        progress: 75,
        status: 'active',
        lastActivity: 'Il y a 2 heures',
        color: 'bg-blue-500'
      },
      {
        id: 2,
        name: 'Communication Professionnelle',
        description: 'Techniques de communication en entreprise',
        progress: 45,
        status: 'active',
        lastActivity: 'Hier',
        color: 'bg-green-500'
      },
      {
        id: 3,
        name: 'Développement Personnel',
        description: 'Confiance en soi et estime de soi',
        progress: 90,
        status: 'completed',
        lastActivity: 'Il y a 3 jours',
        color: 'bg-purple-500'
      }
    ]
  }
})

const selectProgram = (program) => {
  emit('select-program', program)
}

const newProgram = () => {
  emit('new-program')
}

const getStatusIcon = (status) => {
  return status === 'completed' ? 'check_circle' : 'play_circle'
}

const getStatusColor = (status) => {
  return status === 'completed' ? 'text-green-600' : 'text-blue-600'
}
</script>

<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <!-- Header -->
    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
      <h3 class="font-semibold text-gray-900">Programmes récents</h3>
      <button 
        @click="newProgram"
        class="text-blue-600 hover:text-blue-700 p-1 rounded hover:bg-blue-50"
      >
        <span class="material-symbols-outlined">add</span>
      </button>
    </div>

    <!-- Programs List -->
    <div class="p-4 space-y-4">
      <div 
        v-for="program in props.programs" 
        :key="program.id"
        @click="selectProgram(program)"
        class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer"
      >
        <div class="flex items-start justify-between mb-3">
          <div class="flex-1">
            <div class="flex items-center space-x-2 mb-1">
              <h4 class="font-medium text-gray-900">{{ program.name }}</h4>
              <span 
                :class="[
                  'material-symbols-outlined text-sm',
                  getStatusColor(program.status)
                ]"
              >
                {{ getStatusIcon(program.status) }}
              </span>
            </div>
            <p class="text-sm text-gray-600">{{ program.description }}</p>
          </div>
          <div 
            :class="[
              'w-10 h-10 rounded-lg flex items-center justify-center',
              program.color
            ]"
          >
            <span class="material-symbols-outlined text-white">psychology</span>
          </div>
        </div>

        <!-- Progress Bar -->
        <div class="mb-2">
          <div class="flex justify-between items-center mb-1">
            <span class="text-xs text-gray-500">Progression</span>
            <span class="text-xs font-medium text-gray-700">{{ program.progress }}%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div 
              :class="[
                'h-2 rounded-full transition-all duration-300',
                program.color
              ]"
              :style="{ width: `${program.progress}%` }"
            ></div>
          </div>
        </div>

        <!-- Last Activity -->
        <div class="flex items-center text-xs text-gray-500">
          <span class="material-symbols-outlined text-sm mr-1">schedule</span>
          {{ program.lastActivity }}
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="p-4 border-t border-gray-200">
      <button class="w-full text-center text-sm text-blue-600 hover:text-blue-700 font-medium">
        Voir tous les programmes
      </button>
    </div>
  </div>
</template>

<style scoped>
.transition-shadow {
  transition: box-shadow 0.3s ease;
}

.transition-shadow:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

.space-y-4 > * + * {
  margin-top: 1rem;
}
</style>
