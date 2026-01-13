<template>
  <form 
    :class="formClasses"
    @submit.prevent="handleSubmit"
  >
    <div class="space-y-6">
      <!-- Champs du formulaire -->
      <div v-for="field in fields" :key="field.name" class="space-y-1">
        <label :for="field.name" class="block text-sm font-medium text-gray-700">
          {{ field.label }}
          <span v-if="field.required" class="text-red-500">*</span>
        </label>
        
        <!-- Input texte -->
        <input
          v-if="field.type === 'text'"
          :type="field.inputType || 'text'"
          :id="field.name"
          :name="field.name"
          :placeholder="field.placeholder"
          :value="formData[field.name]"
          @input="updateField(field.name, $event.target.value)"
          :required="field.required"
          :disabled="field.disabled"
          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
          :class="fieldClasses"
        />
        
        <!-- Email -->
        <input
          v-else-if="field.type === 'email'"
          type="email"
          :id="field.name"
          :name="field.name"
          :placeholder="field.placeholder"
          :value="formData[field.name]"
          @input="updateField(field.name, $event.target.value)"
          :required="field.required"
          :disabled="field.disabled"
          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
          :class="fieldClasses"
        />
        
        <!-- Textarea -->
        <textarea
          v-else-if="field.type === 'textarea'"
          :id="field.name"
          :name="field.name"
          :placeholder="field.placeholder"
          :value="formData[field.name]"
          @input="updateField(field.name, $event.target.value)"
          :required="field.required"
          :disabled="field.disabled"
          :rows="field.rows || 3"
          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
          :class="fieldClasses"
        />
        
        <!-- Select -->
        <select
          v-else-if="field.type === 'select'"
          :id="field.name"
          :name="field.name"
          :value="formData[field.name]"
          @change="updateField(field.name, $event.target.value)"
          :required="field.required"
          :disabled="field.disabled"
          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
          :class="fieldClasses"
        >
          <option value="">{{ field.placeholder || 'Sélectionner...' }}</option>
          <option 
            v-for="option in field.options" 
            :key="option.value" 
            :value="option.value"
          >
            {{ option.label }}
          </option>
        </select>
        
        <!-- Checkbox -->
        <div v-else-if="field.type === 'checkbox'" class="mt-2">
          <div class="flex items-center">
            <input
              :id="field.name"
              :name="field.name"
              :checked="formData[field.name]"
              @change="updateField(field.name, $event.target.checked)"
              :disabled="field.disabled"
              type="checkbox"
              class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
            />
            <label :for="field.name" class="ml-2 block text-sm text-gray-900">
              {{ field.label }}
            </label>
          </div>
        </div>
        
        <!-- Radio -->
        <div v-else-if="field.type === 'radio'" class="mt-2 space-y-2">
          <div v-for="option in field.options" :key="option.value" class="flex items-center">
            <input
              :id="`${field.name}_${option.value}`"
              :name="field.name"
              :value="option.value"
              :checked="formData[field.name] === option.value"
              @change="updateField(field.name, $event.target.value)"
              :disabled="field.disabled"
              type="radio"
              class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
            />
            <label :for="`${field.name}_${option.value}`" class="ml-2 block text-sm text-gray-900">
              {{ option.label }}
            </label>
          </div>
        </div>
        
        <!-- File -->
        <div v-else-if="field.type === 'file'" class="mt-1">
          <input
            :id="field.name"
            :name="field.name"
            type="file"
            @change="handleFileChange(field.name, $event)"
            :required="field.required"
            :disabled="field.disabled"
            :accept="field.accept"
            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            :class="fieldClasses"
          />
        </div>
        
        <!-- Date -->
        <input
          v-else-if="field.type === 'date'"
          type="date"
          :id="field.name"
          :name="field.name"
          :value="formData[field.name]"
          @input="updateField(field.name, $event.target.value)"
          :required="field.required"
          :disabled="field.disabled"
          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
          :class="fieldClasses"
        />
        
        <!-- Message d'erreur -->
        <p v-if="errors[field.name]" class="mt-1 text-sm text-red-600">
          {{ errors[field.name] }}
        </p>
      </div>
    </div>

    <!-- Boutons d'action -->
    <div class="flex justify-end space-x-3">
      <button
        v-if="showCancel"
        type="button"
        @click="handleCancel"
        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
      >
        {{ cancelText }}
      </button>
      <button
        type="submit"
        :disabled="loading"
        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
      >
        <span v-if="loading">Chargement...</span>
        <span v-else>{{ submitText }}</span>
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  fields: {
    type: Array,
    required: true
  },
  initialData: {
    type: Object,
    default: () => ({})
  },
  submitText: {
    type: String,
    default: 'Enregistrer'
  },
  cancelText: {
    type: String,
    default: 'Annuler'
  },
  showCancel: {
    type: Boolean,
    default: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  layout: {
    type: String,
    default: 'vertical',
    validator: (value) => ['vertical', 'horizontal', 'inline'].includes(value)
  }
})

const emit = defineEmits(['submit', 'cancel'])

const formData = ref({ ...props.initialData })
const errors = ref({})

const formClasses = computed(() => {
  const classes = []
  
  switch (props.layout) {
    case 'horizontal':
      classes.push('flex flex-col sm:flex-row sm:space-x-4')
      break
    case 'inline':
      classes.push('flex flex-wrap items-end space-x-4')
      break
    default:
      classes.push('space-y-6')
  }
  
  return classes.join(' ')
})

const fieldClasses = computed(() => {
  return {
    'border-red-300': Object.keys(errors.value).length > 0,
    'ring-red-500': Object.keys(errors.value).length > 0
  }
})

const updateField = (fieldName, value) => {
  formData.value[fieldName] = value
  // Effacer l'erreur quand l'utilisateur commence à taper
  if (errors.value[fieldName]) {
    delete errors.value[fieldName]
  }
}

const handleFileChange = (fieldName, event) => {
  formData.value[fieldName] = event.target.files[0]
}

const handleSubmit = () => {
  emit('submit', { ...formData.value })
}

const handleCancel = () => {
  emit('cancel')
}

// Exposer les méthodes pour le parent
defineExpose({
  setErrors: (newErrors) => {
    errors.value = newErrors
  },
  clearErrors: () => {
    errors.value = {}
  },
  resetForm: () => {
    formData.value = { ...props.initialData }
    errors.value = {}
  }
})
</script>
