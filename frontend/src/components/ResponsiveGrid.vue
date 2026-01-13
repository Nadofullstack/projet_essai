<template>
  <div class="responsive-grid">
    <div 
      :class="gridClasses"
      class="grid gap-4 sm:gap-6 lg:gap-8"
    >
      <slot />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  cols: {
    type: Object,
    default: () => ({
      mobile: 1,
      tablet: 2,
      desktop: 3,
      large: 4
    })
  }
})

const gridClasses = computed(() => {
  return {
    'grid-cols-1': props.cols.mobile === 1,
    'grid-cols-2': props.cols.mobile === 2,
    'grid-cols-3': props.cols.mobile === 3,
    'grid-cols-4': props.cols.mobile === 4,
    
    'sm:grid-cols-1': props.cols.tablet === 1,
    'sm:grid-cols-2': props.cols.tablet === 2,
    'sm:grid-cols-3': props.cols.tablet === 3,
    'sm:grid-cols-4': props.cols.tablet === 4,
    
    'lg:grid-cols-1': props.cols.desktop === 1,
    'lg:grid-cols-2': props.cols.desktop === 2,
    'lg:grid-cols-3': props.cols.desktop === 3,
    'lg:grid-cols-4': props.cols.desktop === 4,
    
    'xl:grid-cols-1': props.cols.large === 1,
    'xl:grid-cols-2': props.cols.large === 2,
    'xl:grid-cols-3': props.cols.large === 3,
    'xl:grid-cols-4': props.cols.large === 4,
  }
})
</script>
