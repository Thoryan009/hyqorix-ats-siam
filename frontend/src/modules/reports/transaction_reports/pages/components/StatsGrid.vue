<template>
  <div class="bg-white rounded-xl shadow my-5">
    <!-- Tabs -->
    <div class="border-b border-green-200">
      <nav class="flex -mb-px">
        <button
          @click="activeTab = 'candidate'"
          :class="[
            'px-6 py-3 text-sm font-medium border-b-2 transition-colors cursor-pointer',
            activeTab === 'candidate'
              ? 'border-green-500 text-green-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          Candidate
        </button>
        <button
          @click="activeTab = 'client'"
          :class="[
            'px-6 py-3 text-sm font-medium border-b-2 transition-colors cursor-pointer',
            activeTab === 'client'
              ? 'border-green-500 text-green-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          Client
        </button>
      </nav>
    </div>

    <!-- Stats Grid -->
    <div class="p-6">
      <div v-if="currentStats && currentStats.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div v-for="item in currentStats" :key="item.key" class="bg-gray-50 rounded-lg p-5 border border-gray-100">
          <!-- Label -->
          <p class="text-sm text-gray-500">
            {{ item.label }}
          </p>

          <!-- Value -->
          <p class="text-2xl font-bold text-gray-800 mt-2">
            {{ formatValue(item.value) }}
          </p>
        </div>
      </div>

      <!-- Optional empty state -->
      <div v-else class="text-center text-gray-400 py-6">No data available</div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  candidateStats: {
    type: Array,
    required: true,
  },
  clientStats: {
    type: Array,
    required: true,
  },
})

const activeTab = ref('candidate')

const currentStats = computed(() => {
  return activeTab.value === 'candidate' ? props.candidateStats : props.clientStats
})

/* Safe formatter */
const formatValue = (val) => {
  if (val === null || val === undefined) return 0
  // If it's already a formatted string (like "৳116000" or "$6625"), return as is
  if (typeof val === 'string' && isNaN(val.replace(/[^0-9.-]/g, ''))) return val
  if (typeof val === 'string') return val
  return Number(val).toLocaleString()
}
</script>
