<template>
  <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200">
    <div class="bg-gradient-to-r from-gray-700 to-gray-900 text-white px-5 py-3">
      <h3 class="font-semibold flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
        </svg>
        Amount Summary
      </h3>
    </div>

    <div class="divide-y divide-gray-200 text-sm">

      <div class="grid grid-cols-2 gap-3 px-5 py-4 hover:bg-gray-50 transition-colors">
        <span class="flex items-center gap-2 text-gray-700">
          <span class="w-2 h-2 rounded-full bg-blue-500"></span>
          Total Amount
        </span>
        <span class="font-bold text-gray-900 text-right">৳{{ receipt.total_amount }}</span>
      </div>

      <div class="grid grid-cols-2 gap-3 px-5 py-4 hover:bg-yellow-50 transition-colors">
        <span class="flex items-center gap-2 text-yellow-700">
          <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
          Discount
        </span>
        <span class="font-bold text-yellow-700 text-right">-৳{{ receipt.discount_amount }}</span>
      </div>

      <div class="grid grid-cols-2 gap-3 px-5 py-4 hover:bg-green-50 transition-colors">
        <span class="flex items-center gap-2 text-green-700">
          <span class="w-2 h-2 rounded-full bg-green-500"></span>
          Total Paid (All Time)
        </span>
        <span class="font-bold text-green-700 text-right">৳{{ receipt.total_paid_amount }}</span>
      </div>

      <div class="grid grid-cols-2 gap-3 px-5 py-5 bg-gradient-to-r from-red-50 to-orange-50">
        <span class="flex items-center gap-2 font-bold text-lg text-red-700">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Due Amount
        </span>
        <span class="font-bold text-xl text-red-600 text-right">৳{{ receipt.due_amount }}</span>
      </div>

    </div>
  </div>
</template>

<script setup>
defineProps({
  receipt: Object
})
</script>
