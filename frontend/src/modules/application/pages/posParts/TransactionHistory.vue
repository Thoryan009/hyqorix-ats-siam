<template>
  <div class="bg-white px-4 py-3 rounded-b-xl border border-gray-200 overflow-x-auto">
    <h4 class="mb-3 text-sm font-semibold text-gray-700">Transaction History</h4>

    <div class="overflow-x-auto" v-if="transactions && transactions.length > 0">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-2 py-2 text-left text-black text-[14px] font-medium">Bill No</th>
            <th class="px-2 py-2 text-left text-black text-[14px] font-medium">Total</th>
            <th class="px-2 py-2 text-left text-black text-[14px] font-medium">Discount</th>
            <th class="px-2 py-2 text-left text-black text-[14px] font-medium">Paid</th>
            <th class="px-2 py-2 text-left text-black text-[14px] font-medium">Due</th>
            <th class="px-2 py-2 text-left text-black text-[14px] font-medium">Method</th>
            <th class="px-2 py-2 text-left text-black text-[14px] font-medium">Status</th>
            <th class="px-2 py-2 text-left text-black text-[14px] font-medium">Date & Time</th>
            <th class="px-2 py-2 text-left text-black text-[14px] font-medium">Remarks</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-200">
          <tr v-for="(transaction, idx) in transactions" :key="idx" class="hover:bg-gray-50">
            <td class="px-2 py-2 text-[15px]">{{ transaction.bill_no }}</td>
            <td class="px-2 py-2 text-[15px]">৳{{ transaction.total_amount }}</td>
            <td class="px-2 py-2 text-[15px]">৳{{ transaction.discount_amount }}</td>
            <td class="px-2 py-2 text-[15px]">৳{{ transaction.paid_amount }}</td>
            <td class="px-2 py-2 text-[15px]">৳{{ transaction.due_amount }}</td>
            <td class="px-2 py-2 text-[15px]">{{ transaction.payment_method }}</td>
            <td class="px-2 py-2">
              <span
                class="rounded-full px-2 py-1 text-[15px] font-medium"
                :class="{
                  'bg-green-100 text-green-800': transaction.status === 'bill-generated',
                  'bg-yellow-100 text-yellow-800': transaction.status === 'pending',
                  'bg-red-100 text-red-800': transaction.status === 'cancelled',
                  'bg-primary-light text-primary-dark': transaction.status === 'paid',
                  'bg-orange-100 text-orange-800': transaction.status === 'due',
                  'bg-pink-100 text-pink-800': transaction.status === 'draft',
                }"
                >{{ transaction.status }}</span
              >
            </td>

            <td class="px-2 py-2 text-[15px]">{{ transaction.payment_date_time_formatted }}</td>

            <td class="px-2 py-2 text-[14px]">{{ transaction.remarks }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-else class="py-4 text-center text-md text-gray-500">
      No transaction history available
    </div>
  </div>
</template>

<script setup>
defineProps({
  transactions: Array, // server data 그대로
})
</script>
