<template>
  <Transition
    enter-active-class="transition ease-out duration-300"
    enter-from-class="opacity-0 translate-y-2 sm:translate-y-4"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition ease-in duration-200"
    leave-from-class="opacity-100 translate-y-0"
    leave-to-class="opacity-0 translate-y-2 sm:translate-y-4"
  >
    <div v-if="show" class="rounded-md shadow-lg" :class="alertClasses">
      <div class="p-4">
        <div class="flex">
          <!-- Icône -->
          <div class="flex-shrink-0">
            <component 
              :is="iconComponent" 
              class="h-5 w-5" 
              :class="iconClasses"
            />
          </div>
          
          <!-- Contenu -->
          <div class="ml-3">
            <h3 v-if="title" class="text-sm font-medium" :class="titleClasses">
              {{ title }}
            </h3>
            <div class="text-sm" :class="messageClasses">
              <p v-if="typeof message === 'string'">{{ message }}</p>
              <ul v-else class="list-disc list-inside space-y-1">
                <li v-for="(msg, index) in message" :key="index">{{ msg }}</li>
              </ul>
            </div>
          </div>
          
          <!-- Bouton de fermeture -->
          <div class="ml-auto pl-3">
            <div class="-mx-1.5 -my-1.5">
              <button
                @click="close"
                type="button"
                class="inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2"
                :class="closeButtonClasses"
              >
                <span class="sr-only">Fermer</span>
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293 4.293a1 1 0 001.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Actions supplémentaires -->
      <div v-if="$slots.actions" class="mt-4 flex space-x-3">
        <slot name="actions" />
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { computed } from 'vue'
import { 
  CheckCircleIcon, 
  ExclamationCircleIcon, 
  ExclamationTriangleIcon, 
  InformationCircleIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  show: {
    type: Boolean,
    required: true
  },
  type: {
    type: String,
    default: 'info',
    validator: (value) => ['success', 'error', 'warning', 'info'].includes(value)
  },
  title: {
    type: String,
    default: ''
  },
  message: {
    type: [String, Array],
    required: true
  },
  dismissible: {
    type: Boolean,
    default: true
  },
  timeout: {
    type: Number,
    default: 0
  }
})

const emit = defineEmits(['close'])

// Auto-fermeture après timeout
if (props.timeout > 0) {
  setTimeout(() => {
    emit('close')
  }, props.timeout)
}

const alertClasses = computed(() => {
  const baseClasses = []
  
  switch (props.type) {
    case 'success':
      baseClasses.push('bg-green-50', 'border-green-200')
      break
    case 'error':
      baseClasses.push('bg-red-50', 'border-red-200')
      break
    case 'warning':
      baseClasses.push('bg-yellow-50', 'border-yellow-200')
      break
    case 'info':
    default:
      baseClasses.push('bg-blue-50', 'border-blue-200')
  }
  
  return baseClasses.join(' ')
})

const iconComponent = computed(() => {
  switch (props.type) {
    case 'success':
      return CheckCircleIcon
    case 'error':
      return ExclamationCircleIcon
    case 'warning':
      return ExclamationTriangleIcon
    case 'info':
    default:
      return InformationCircleIcon
  }
})

const iconClasses = computed(() => {
  switch (props.type) {
    case 'success':
      return 'text-green-400'
    case 'error':
      return 'text-red-400'
    case 'warning':
      return 'text-yellow-400'
    case 'info':
    default:
      return 'text-blue-400'
  }
})

const titleClasses = computed(() => {
  switch (props.type) {
    case 'success':
      return 'text-green-800'
    case 'error':
      return 'text-red-800'
    case 'warning':
      return 'text-yellow-800'
    case 'info':
    default:
      return 'text-blue-800'
  }
})

const messageClasses = computed(() => {
  switch (props.type) {
    case 'success':
      return 'text-green-700'
    case 'error':
      return 'text-red-700'
    case 'warning':
      return 'text-yellow-700'
    case 'info':
    default:
      return 'text-blue-700'
  }
})

const closeButtonClasses = computed(() => {
  switch (props.type) {
    case 'success':
      return 'text-green-400 hover:text-green-500 focus:ring-green-500 focus:ring-offset-green-50'
    case 'error':
      return 'text-red-400 hover:text-red-500 focus:ring-red-500 focus:ring-offset-red-50'
    case 'warning':
      return 'text-yellow-400 hover:text-yellow-500 focus:ring-yellow-500 focus:ring-offset-yellow-50'
    case 'info':
    default:
      return 'text-blue-400 hover:text-blue-500 focus:ring-blue-500 focus:ring-offset-blue-50'
  }
})

const close = () => {
  emit('close')
}
</script>

<style scoped>
/* Transitions */
.transition {
  transition-property: opacity, transform;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

.opacity-0 {
  opacity: 0;
}

.opacity-100 {
  opacity: 1;
}

.translate-y-0 {
  transform: translateY(0);
}

.translate-y-2 {
  transform: translateY(0.5rem);
}

.translate-y-4 {
  transform: translateY(1rem);
}

/* Responsive */
@media (max-width: 640px) {
  .translate-y-4 {
    transform: translateY(1rem);
  }
}
</style>
