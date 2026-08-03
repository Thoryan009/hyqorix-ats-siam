<template>
  <div class="mb-2">
    <table class="w-full border-collapse text-sm">
      <tbody>
        <tr class="font-semibold border-b border-gray-200">
          <td class="py-2 px-3 text-left">Total Amount:</td>
          <td class="py-2 px-3 text-right">
            ৳{{ formatAmount(transaction.total_amount) }}
          </td>
        </tr>
        <tr class="font-semibold border-b border-gray-200 text-yellow-700">
          <td class="py-2 px-3 text-left">Discount Amount:</td>
          <td class="py-2 px-3 text-right">
            ৳{{ formatAmount(transaction.discount_amount) }}
          </td>
        </tr>
        <tr class="font-semibold border-b border-gray-200 text-green-700">
          <td class="py-2 px-3 text-left">Total Paid Amount:</td>
          <td class="py-2 px-3 text-right">
            ৳{{ formatAmount(transaction.total_paid_amount) }}
          </td>
        </tr>
        <tr v-if="transaction.due_amount > 0" class="font-semibold border-b border-gray-200 text-red-700">
          <td class="py-2 px-3 text-left">Balance Due:</td>
          <td class="py-2 px-3 text-right">
            ৳{{ formatAmount(transaction.due_amount) }}
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

const formatAmount = (amount) => {
  if (amount == null) return '0.00'

  return amount.toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}

</script>
