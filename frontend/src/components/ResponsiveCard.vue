<template>
  <div 
    :class="cardClasses"
    class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden"
  >
    <!-- Header de la carte -->
    <div v-if="$slots.header" class="px-4 py-3 sm:px-6 border-b border-gray-200 bg-gray-50">
      <slot name="header" />
    </div>

    <!-- Contenu principal -->
    <div :class="contentClasses">
      <slot />
    </div>

    <!-- Footer de la carte -->
    <div v-if="$slots.footer" class="px-4 py-3 sm:px-6 border-t border-gray-200 bg-gray-50">
      <slot name="footer" />
    </div>

    <!-- Actions de la carte -->
    <div v-if="$slots.actions" class="px-4 py-3 sm:px-6 border-t border-gray-200 bg-gray-50">
      <div class="flex justify-end space-x-3">
        <slot name="actions" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  padding: {
    type: String,
    default: 'normal',
    validator: (value) => ['none', 'small', 'normal', 'large'].includes(value)
  },
  hover: {
    type: Boolean,
    default: true
  },
  compact: {
    type: Boolean,
    default: false
  }
})

const cardClasses = computed(() => {
  const classes = []
  
  if (props.hover) {
    classes.push('hover:shadow-lg', 'transition-shadow', 'duration-200')
  }
  
  if (props.compact) {
    classes.push('shadow-sm')
  }
  
  return classes.join(' ')
})

const contentClasses = computed(() => {
  const classes = []
  
  switch (props.padding) {
    case 'none':
      classes.push('p-0')
      break
    case 'small':
      classes.push('p-3', 'sm:p-4')
      break
    case 'normal':
      classes.push('p-4', 'sm:p-6')
      break
    case 'large':
      classes.push('p-6', 'sm:p-8')
      break
  }
  
  return classes.join(' ')
})
</script>
