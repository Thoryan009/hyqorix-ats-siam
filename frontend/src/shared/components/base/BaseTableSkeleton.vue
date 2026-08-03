<template>
  <div class="animate-pulse">
    <table class="min-w-full divide-y divide-gray-200">
      <!-- Header -->
      <thead class="bg-gray-50">
        <tr>
          <th v-if="selectable" class="px-4 py-3">
            <div class="h-4 w-4 bg-gray-300 rounded"></div>
          </th>

          <th
            v-for="col in displayColumns"
            :key="col.key"
            class="px-6 py-3 text-left"
          >
            <div class="h-4 w-24 bg-gray-300 rounded"></div>
          </th>

          <th v-if="showActions" class="px-6 py-3">
            <div class="h-4 w-16 bg-gray-300 rounded"></div>
          </th>
        </tr>
      </thead>

      <!-- Body -->
      <tbody class="divide-y divide-gray-200 bg-white">
        <tr v-for="i in rows" :key="i">
          <td v-if="selectable" class="px-4 py-4">
            <div class="h-4 w-4 bg-gray-200 rounded"></div>
          </td>

          <td
            v-for="col in displayColumns"
            :key="col.key"
            class="px-6 py-4"
          >
            <div class="h-4 w-full bg-gray-200 rounded"></div>
          </td>

          <td v-if="showActions" class="px-6 py-4 flex gap-2">
            <div class="h-6 w-6 bg-gray-200 rounded"></div>
            <div class="h-6 w-6 bg-gray-200 rounded"></div>
            <div class="h-6 w-6 bg-gray-200 rounded"></div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  columns: Array,
  rows: {
    type: Number,
    default: 8
  },
  selectable: Boolean,
  showActions: Boolean,
  showSerial: {
    type: Boolean,
    default: true,
  },
})

const displayColumns = computed(() =>
  props.showSerial
    ? props.columns ?? []
    : (props.columns ?? []).filter((col) => col.key !== 'sl')
)
</script>
