<template>
  <div class="my-5">
    <!-- Stats Grid -->
    <div>
      <div
        v-if="items && items.length"
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4"
      >
        <div
          v-for="item in items"
          :key="item.key"
          class="bg-white rounded-lg p-5 border border-gray-100"
        >
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
defineProps({
  items: {
    type: Array,
    required: true,
  },
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
