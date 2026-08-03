<template>
  <div
    v-if="receipt.remarks"
    class="bg-amber-50 border-l-4 border-amber-400 rounded-lg p-4 shadow-sm"
  >
    <p class="text-xs text-amber-800 font-semibold mb-2 flex items-center gap-1.5">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
      </svg>
      Remarks
    </p>
    <p class="text-sm text-gray-700 italic">{{ receipt.remarks }}</p>
  </div>
</template>

<script setup>
defineProps({
  receipt: Object
})
</script>
