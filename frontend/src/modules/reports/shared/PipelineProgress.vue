<template>
  <div class="bg-white rounded-xl shadow p-6">
    <!-- Title -->
    <h2 class="text-lg font-semibold text-gray-700 mb-4">
      {{ title }}
    </h2>

    <div class="space-y-4">
      <div v-for="item in items" :key="item.key">

        <!-- Label & Value -->
        <div class="flex justify-between text-sm text-gray-600 mb-1">
          <span>{{ item.label }}</span>
          <span>{{ getValue(item) }}</span>
        </div>

        <!-- Progress Bar -->
        <div v-if="item.showProgress !== false" class="w-full bg-gray-200 h-2 rounded-full overflow-hidden">
          <div class="h-2 rounded-full transition-all duration-500" :class="item.color || defaultColor"
            :style="{ width: getPercentage(item) + '%' }" />
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: String,
  data: { type: Object, required: true },
  items: { type: Array, required: true },
  totalKey: { type: String, default: null },
  totalValue: { type: Number, default: null },
  defaultColor: { type: String, default: 'bg-primary' },
})

// Calculate total value
const total = computed(() =>
  props.totalValue !== null
    ? props.totalValue
    : props.totalKey
      ? props.data?.[props.totalKey] || 0
      : 0
)

// Get safe item value
const getValue = (item) =>
  props.data?.[item.key] ?? 0

// Calculate percentage width
const getPercentage = (item) => {
  const value = getValue(item)

  if (item.percentageFn)
    return item.percentageFn(value, props.data)

  if (!total.value) return 0

  return Math.min(
    100,
    Math.round((value / total.value) * 100)
  )
}
</script>
