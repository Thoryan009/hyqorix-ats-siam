<template>
  <div
    class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-xl px-4 py-3"
  >
    <div>
      <h3 class="text-base font-semibold text-gray-800 tracking-wide">
        Selected Candidates ({{ selectedRows.length }})
      </h3>
      <p class="text-xs text-gray-500">Review selected applications before sending</p>
    </div>
    <span class="bg-blue-600 text-white text-sm font-medium px-3 py-1 rounded-full">
      {{ selectedRows.length }}
    </span>
  </div>
</template>

<script setup>
defineProps({
  selectedRows: { type: Array, required: true },
})
</script>
