<script setup>
  import { ref,watch } from 'vue'
const props = defineProps({
  event: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['save', 'cancel'])

const form = ref({
  title: '',
  description: '',
  date: '',
  startTime: '',
  endTime: '',
  location: '',
  category: 'conference',
  maxAttendees: 50,
  organizer: '',
  price: 0
})

const categories = [
  { value: 'conference', label: 'Conférence' },
  { value: 'workshop', label: 'Atelier' },
  { value: 'meeting', label: 'Réunion' },
  { value: 'social', label: 'Social' },
  { value: 'other', label: 'Autre' }
]

// Initialiser le formulaire si modification
watch(() => props.event, (newEvent) => {
  if (newEvent) {
    form.value = { ...newEvent }
  } else {
    resetForm()
  }
}, { immediate: true })

const resetForm = () => {
  form.value = {
    title: '',
    description: '',
    date: '',
    startTime: '',
    endTime: '',
    location: '',
    category: 'conference',
    maxAttendees: 50,
    organizer: '',
    price: 0
  }
}

const handleSubmit = () => {
  emit('save', form.value)
}

const handleCancel = () => {
  emit('cancel')
}
</script>

<template>
  <form @submit.prevent="handleSubmit" class="space-y-6">
    <!-- Informations de base -->
    <div class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Titre de l'événement *
        </label>
        <input
          v-model="form.title"
          type="text"
          required
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Ex: Conférence sur l'IA"
        />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Description *
        </label>
        <textarea
          v-model="form.description"
          rows="4"
          required
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
          placeholder="Décrivez votre événement en détail..."
        ></textarea>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Catégorie *
        </label>
        <select
          v-model="form.category"
          required
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option 
            v-for="category in categories" 
            :key="category.value"
            :value="category.value"
          >
            {{ category.label }}
          </option>
        </select>
      </div>
    </div>

    <!-- Date et heure -->
    <div class="space-y-4">
      <h3 class="text-lg font-medium text-gray-900">Date et heure</h3>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Date *
        </label>
        <input
          v-model="form.date"
          type="date"
          required
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Heure de début *
          </label>
          <input
            v-model="form.startTime"
            type="time"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Heure de fin *
          </label>
          <input
            v-model="form.endTime"
            type="time"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
      </div>
    </div>

    <!-- Lieu et organisation -->
    <div class="space-y-4">
      <h3 class="text-lg font-medium text-gray-900">Lieu et organisation</h3>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Lieu *
        </label>
        <input
          v-model="form.location"
          type="text"
          required
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Ex: Salle de conférence A"
        />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Organisateur *
        </label>
        <input
          v-model="form.organizer"
          type="text"
          required
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Nom de l'organisateur"
        />
      </div>
    </div>

    <!-- Participants et prix -->
    <div class="space-y-4">
      <h3 class="text-lg font-medium text-gray-900">Participants</h3>
      
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Nombre maximum de participants *
          </label>
          <input
            v-model.number="form.maxAttendees"
            type="number"
            min="1"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Prix (€)
          </label>
          <input
            v-model.number="form.price"
            type="number"
            min="0"
            step="0.01"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="0 pour gratuit"
          />
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
      <button 
        type="button"
        @click="handleCancel"
        class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
      >
        Annuler
      </button>
      <button 
        type="submit"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
      >
        {{ props.event ? 'Mettre à jour' : 'Créer' }}
      </button>
    </div>
  </form>
</template>

<style scoped>
.space-y-4 > * + * {
  margin-top: 1rem;
}

.space-y-6 > * + * {
  margin-top: 1.5rem;
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

.resize-none {
  resize: none;
}
</style>
