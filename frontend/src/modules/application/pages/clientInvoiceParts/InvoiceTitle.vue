<template>
  <div class="mt-2 flex justify-between items-center py-2">
    <div class="flex flex-col mb-2">
      <h2 class="text-3xl font-bold text-gray-900 tracking-[0.1em]">INVOICE</h2>
      <span class="text-black text-[14px]">{{ transaction.bill_no }}</span>
    </div>
    <div class="flex justify-end py-2">
      <span class="inline-block px-3 py-2 rounded-md text-lg font-extrabold uppercase tracking-wider shadow-md" :class="{
        'bg-green-100 text-green-800': transaction.status === 'paid',
        'bg-red-100 text-red-800': transaction.status === 'due',
        'bg-gray-100 text-gray-800': transaction.status === 'draft',
        'bg-gray-100 text-blue-800': transaction.status === 'bill-generated',
      }">
        {{ transaction.status_label }}
      </span>
    </div>
  </div>
</template>

<script setup>
defineProps({
  transaction: {
    type: Object,
    required: true,
  },
})
</script>
