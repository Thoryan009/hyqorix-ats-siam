<template>
  <div class="grid grid-cols-6 gap-6 my-1">
    <!-- Client/Candidate Info -->
    <div class="col-span-3">
      <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider mt-2">Bill To:</h3>
      <div class="text-sm text-gray-700 leading-relaxed">
        <p class="font-bold text-gray-900 text-base mb-1">
          {{ transaction?.payer_name }}
        </p>
        <p class="">{{ transaction?.payer_mobile || 'N/A' }}</p>
        <p class="">{{ transaction?.payer_email || 'N/A' }}</p>
      </div>
    </div>

    <!-- Invoice Info -->
    <div class="col-span-3">
      <!-- ONE card -->
      <div class="bg-gray-50 border border-gray-200 p-3 rounded-lg">
        <div v-for="(category, cIndex) in transaction?.fees" :key="cIndex">
          <!-- Category Title -->
          <h3 class="text-sm font-semibold text-gray-900 border-b border-gray-200 mb-0.5 mt-1">
            {{ category.fee_category }}
          </h3>

          <!-- Fee Items -->
          <div v-for="(fee, fIndex) in category.items" :key="fIndex" class="flex gap-2client-bills/ justify-between items-center py-[2px]">
            <span class="text-sm text-gray-600">
              {{ fee.fee_name }}
            </span>

            <span class="text-sm font-semibold text-gray-900"> ৳{{ fee.amount }} </span>
          </div>
        </div>
      </div>
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
