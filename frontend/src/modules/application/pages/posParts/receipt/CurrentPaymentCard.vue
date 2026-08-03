<template>
  <div
    class="bg-gradient-to-br from-green-50 via-emerald-50 to-teal-50 border-2 border-green-300 rounded-2xl p-6 shadow-xl relative overflow-hidden"
  >
    <div class="absolute top-0 right-0 w-32 h-32 bg-green-200 rounded-full -mr-16 -mt-16 opacity-20"></div>
    <div class="absolute bottom-0 left-0 w-24 h-24 bg-emerald-200 rounded-full -ml-12 -mb-12 opacity-20"></div>

    <div class="relative grid grid-cols-2 gap-6">
      <div>
        <p class="text-xs text-green-700 font-medium mb-2 flex items-center gap-1.5">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
          </svg>
          Current Payment Received
        </p>
        <p class="text-4xl font-black text-green-700 tracking-tight flex items-baseline gap-1">
          <span class="text-2xl">৳</span>
          {{ receipt.paid_amount }}
        </p>
      </div>

      <div class="flex flex-col justify-center items-end">
        <p class="text-xs text-gray-600 mb-1">Payment Status</p>
        <div class="inline-flex items-center gap-2 bg-white px-4 py-2 rounded-full shadow-md border border-green-200">
          <span class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse"></span>
          <p class="font-bold capitalize text-xl text-green-800">{{ receipt.status }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  receipt: Object
})
</script>
