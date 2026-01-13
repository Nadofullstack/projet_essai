<script setup>
  import {ref, watch} from 'vue'
const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  program: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'save'])

const form = ref({
  name: '',
  description: '',
  duration: '',
  difficulty: 'beginner',
  exercises: []
})

const difficulties = [
  { value: 'beginner', label: 'Débutant', color: 'bg-green-500' },
  { value: 'intermediate', label: 'Intermédiaire', color: 'bg-yellow-500' },
  { value: 'advanced', label: 'Avancé', color: 'bg-red-500' }
]

const closeModal = () => {
  emit('close')
  resetForm()
}

const resetForm = () => {
  form.value = {
    name: '',
    description: '',
    duration: '',
    difficulty: 'beginner',
    exercises: []
  }
}

const saveProgram = () => {
  emit('save', { ...form.value })
  closeModal()
}

const addExercise = () => {
  form.value.exercises.push({
    id: Date.now(),
    name: '',
    description: '',
    duration: 5
  })
}

const removeExercise = (index) => {
  form.value.exercises.splice(index, 1)
}

// Initialize form if editing
watch(() => props.program, (newProgram) => {
  if (newProgram) {
    form.value = { ...newProgram }
  } else {
    resetForm()
  }
}, { immediate: true })
</script>

<template>
  <div 
    v-if="isOpen"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
    @click="closeModal"
  >
    <div 
      class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto"
      @click.stop
    >
      <!-- Header -->
      <div class="p-6 border-b border-gray-200">
        <div class="flex justify-between items-center">
          <h3 class="text-xl font-semibold text-gray-900">
            {{ program ? 'Modifier le programme' : 'Nouveau programme' }}
          </h3>
          <button 
            @click="closeModal"
            class="p-2 rounded-lg hover:bg-gray-100"
          >
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
      </div>

      <!-- Form -->
      <div class="p-6 space-y-6">
        <!-- Basic Info -->
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Nom du programme
            </label>
            <input
              v-model="form.name"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Ex: Programme Vocal Avancé"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Description
            </label>
            <textarea
              v-model="form.description"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
              placeholder="Description détaillée du programme..."
            ></textarea>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Durée (minutes)
              </label>
              <input
                v-model="form.duration"
                type="number"
                min="1"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="30"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Difficulté
              </label>
              <select
                v-model="form.difficulty"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option 
                  v-for="diff in difficulties" 
                  :key="diff.value"
                  :value="diff.value"
                >
                  {{ diff.label }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <!-- Exercises -->
        <div>
          <div class="flex justify-between items-center mb-4">
            <h4 class="font-medium text-gray-900">Exercices</h4>
            <button 
              @click="addExercise"
              class="text-blue-600 hover:text-blue-700 flex items-center space-x-1 text-sm"
            >
              <span class="material-symbols-outlined">add</span>
              <span>Ajouter un exercice</span>
            </button>
          </div>

          <div v-if="form.exercises.length === 0" class="text-center py-8 text-gray-500">
            <span class="material-symbols-outlined text-4xl">fitness_center</span>
            <p class="mt-2">Aucun exercice ajouté</p>
          </div>

          <div v-else class="space-y-3">
            <div 
              v-for="(exercise, index) in form.exercises" 
              :key="exercise.id"
              class="border border-gray-200 rounded-lg p-4"
            >
              <div class="flex justify-between items-start mb-3">
                <h5 class="font-medium text-gray-900">Exercice {{ index + 1 }}</h5>
                <button 
                  @click="removeExercise(index)"
                  class="text-red-500 hover:text-red-700"
                >
                  <span class="material-symbols-outlined">delete</span>
                </button>
              </div>

              <div class="space-y-3">
                <input
                  v-model="exercise.name"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="Nom de l'exercice"
                />
                <textarea
                  v-model="exercise.description"
                  rows="2"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                  placeholder="Description de l'exercice"
                ></textarea>
                <input
                  v-model="exercise.duration"
                  type="number"
                  min="1"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="Durée en minutes"
                />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="p-6 border-t border-gray-200 flex justify-end space-x-3">
        <button 
          @click="closeModal"
          class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
        >
          Annuler
        </button>
        <button 
          @click="saveProgram"
          :disabled="!form.name.trim()"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ program ? 'Mettre à jour' : 'Créer' }}
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.space-y-4 > * + * {
  margin-top: 1rem;
}

.space-y-3 > * + * {
  margin-top: 0.75rem;
}

.grid {
  display: grid;
}

.grid-cols-2 {
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.gap-4 {
  gap: 1rem;
}

.max-h-90vh {
  max-height: 90vh;
}

.overflow-y-auto {
  overflow-y: auto;
}
</style>
