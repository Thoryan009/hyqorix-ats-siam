<template>
  <div class="overflow-x-auto rounded-lg border border-gray-200">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">Process Stage</th>
          <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-700">Count</th>
          <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-700">Progress</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-gray-200 bg-white">
        <tr v-for="(value, key) in stages" :key="key" class="transition-colors hover:bg-blue-50">

          <td class="whitespace-nowrap px-4 py-3">
            <div class="flex items-center gap-2">
              <span class="inline-flex h-2 w-2 rounded-full" :class="value > 0 ? 'bg-green-500' : 'bg-gray-300'"></span>
              <span class="text-sm font-medium text-gray-900">{{ formatProcessName(key) }}</span>
            </div>
          </td>

          <td class="whitespace-nowrap px-4 py-3 text-center">
            <span class="inline-flex rounded-full px-3 py-1 text-sm font-semibold"
              :class="value > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600'">{{ value }}</span>
          </td>

          <td class="whitespace-nowrap px-4 py-3">
            <div class="flex items-center justify-center gap-2">
              <div class="h-2 w-24 overflow-hidden rounded-full bg-gray-200">
                <div
                  class="h-full rounded-full transition-all duration-500"
                  :class="value > 0 ? 'bg-gradient-to-r from-blue-500 to-blue-600' : 'bg-gray-300'"
                  :style="{ width: `${getProgressPercentage(value, total)}%` }"
                ></div>
              </div>
              <span class="text-xs font-medium text-gray-600">{{ getProgressPercentage(value, total) }}%</span>
            </div>
          </td>

        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
defineProps({
  stages: Object,
  total: Number,
  formatProcessName: Function,
  getProgressPercentage: Function,
});
</script>
