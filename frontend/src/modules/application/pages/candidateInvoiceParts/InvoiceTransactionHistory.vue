<template>
  <div class="my-4">
    <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3">
      Transaction History
    </h4>
    <table class="w-full border-collapse text-sm">
      <thead class="bg-gray-50 border-b-2 border-black">
        <tr>
          <th v-for="(title, index) in transactionHeaders" :key="index"
            class="text-left py-2 px-2 font-bold text-gray-700 text-xs tracking-wide">
            {{ title }}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(txn, index) in transaction?.bill_transactions" :key="index" class="border-b border-gray-200">
          <td class="py-2 px-2">{{ txn.transaction_id }}</td>
          <td class="py-2 px-2">৳{{ txn.total_amount }}</td>
          <td class="py-2 px-2">৳{{ txn.paid_amount }}</td>
          <td class="py-2 px-2">৳{{ txn.due_amount }}</td>
          <td class="py-2 px-2">{{ txn.payment_method }}</td>
          <td class="py-2 px-2">
            {{ txn.payment_date_formatted }} {{ txn.payment_time_formatted }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
defineProps({
  transaction: {
    type: Object,
    required: true,
  },
})

const transactionHeaders = ['Trx ID', 'Total', 'Paid', 'Due', 'Method', 'Date']

</script>
