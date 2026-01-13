<template>
  <div class="stats-container">
    <!-- Grid responsive pour les cartes de statistiques -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
      <ResponsiveCard
        v-for="stat in stats"
        :key="stat.id"
        hover
        class="transform hover:scale-105 transition-transform duration-200"
      >
        <div class="flex items-center">
          <div 
            :class="stat.iconBg"
            class="flex-shrink-0 p-3 rounded-lg"
          >
            <component :is="stat.icon" class="h-6 w-6" />
          </div>
          <div class="ml-4 flex-1">
            <p class="text-sm font-medium text-gray-600 truncate">
              {{ stat.title }}
            </p>
            <div class="flex items-baseline">
              <p class="text-2xl font-semibold text-gray-900">
                {{ stat.value }}
              </p>
              <p v-if="stat.change" class="ml-2 text-sm font-medium" :class="stat.changeColor">
                {{ stat.change > 0 ? '+' : '' }}{{ stat.change }}%
              </p>
            </div>
            <p v-if="stat.subtitle" class="text-sm text-gray-500">
              {{ stat.subtitle }}
            </p>
          </div>
        </div>
      </ResponsiveCard>
    </div>

    <!-- Graphique responsive -->
    <div v-if="showChart" class="mt-6 sm:mt-8">
      <ResponsiveCard>
        <template #header>
          <h3 class="text-lg font-medium text-gray-900">{{ chartTitle }}</h3>
        </template>
        <div class="h-64 sm:h-80 lg:h-96">
          <slot name="chart" />
        </div>
      </ResponsiveCard>
    </div>

    <!-- Tableau responsive -->
    <div v-if="showTable" class="mt-6 sm:mt-8">
      <ResponsiveCard>
        <template #header>
          <h3 class="text-lg font-medium text-gray-900">{{ tableTitle }}</h3>
        </template>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th 
                  v-for="header in tableHeaders" 
                  :key="header.key"
                  class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  {{ header.label }}
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(row, index) in tableData" :key="index" class="hover:bg-gray-50">
                <td 
                  v-for="header in tableHeaders" 
                  :key="header.key"
                  class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                >
                  <slot :name="`cell-${header.key}`" :row="row" :value="row[header.key]">
                    {{ row[header.key] }}
                  </slot>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </ResponsiveCard>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import ResponsiveCard from './ResponsiveCard.vue'
import { 
  TrendingUpIcon, 
  TrendingDownIcon, 
  UserIcon, 
  CalendarIcon,
  PhoneIcon,
  MicrophoneIcon,
  MessageIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  stats: {
    type: Array,
    required: true
  },
  showChart: {
    type: Boolean,
    default: false
  },
  chartTitle: {
    type: String,
    default: 'Statistiques'
  },
  showTable: {
    type: Boolean,
    default: false
  },
  tableTitle: {
    type: String,
    default: 'Données'
  },
  tableHeaders: {
    type: Array,
    default: () => []
  },
  tableData: {
    type: Array,
    default: () => []
  }
})

// Calculer les couleurs et icônes pour les statistiques
const statsWithIcons = computed(() => {
  return props.stats.map(stat => {
    let icon, iconBg, changeColor
    
    switch (stat.type) {
      case 'users':
        icon = UserIcon
        iconBg = 'bg-blue-500'
        break
      case 'appointments':
        icon = CalendarIcon
        iconBg = 'bg-green-500'
        break
      case 'calls':
        icon = PhoneIcon
        iconBg = 'bg-purple-500'
        break
      case 'audio':
        icon = MicrophoneIcon
        iconBg = 'bg-yellow-500'
        break
      case 'messages':
        icon = MessageIcon
        iconBg = 'bg-red-500'
        break
      default:
        icon = TrendingUpIcon
        iconBg = 'bg-gray-500'
    }
    
    changeColor = stat.change >= 0 ? 'text-green-600' : 'text-red-600'
    
    return {
      ...stat,
      icon,
      iconBg,
      changeColor
    }
  })
})
</script>

<style scoped>
.stats-container {
  margin-bottom: 1.5rem;
}

/* Responsive pour les tableaux */
@media (max-width: 640px) {
  .overflow-x-auto {
    -webkit-overflow-scrolling: touch;
  }
}

/* Animation pour les cartes */
.transform:hover {
  transform: translateY(-2px);
}

.transition-transform {
  transition-property: transform;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}
</style>
