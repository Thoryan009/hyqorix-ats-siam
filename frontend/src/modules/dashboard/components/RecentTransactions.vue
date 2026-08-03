<template>
  <div
    class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200 hover:shadow-2xl transition-all duration-300 cursor-pointer"
    @click="$router.push('/applications/transactions')"
  >
    <!-- Header with gradient -->
    <div
      class="bg-primary px-4 py-2.5 flex items-center justify-between"
    >
      <div class="flex items-center gap-2">
        <div
          class="w-7 h-7 rounded-md bg-white/20 backdrop-blur-sm flex items-center justify-center text-white"
        >
          <i class="fa fa-exchange text-sm"></i>
        </div>
        <h2 class="text-sm font-semibold text-white">Latest Transactions</h2>
      </div>
      <span class="bg-white/20 backdrop-blur-sm text-white text-xs font-medium px-2.5 py-0.5 rounded-full"
        >{{ transactions?.length }} Total</span
      >
    </div>

    <!-- Content -->
    <div class="p-6">
      <BaseTable
        :columns="columns"
        :rows="transactions"
        :scrollable="false"
        theadBgColor="bg-primary/20"
      />
    </div>
  </div>
</template>

<script setup>
import BaseTable from '@/shared/components/base/BaseTable.vue'
import { computed } from 'vue'

// Props
const props = defineProps({
  recent_transactions: {
    type: Array,
  },
})

// Columns definition
const columns = [
  { key: 'transactionId', label: 'Transaction ID' },
  { key: 'billNo', label: 'Bill No' },
  { key: 'amount', label: 'Amount' },
  { key: 'status', label: 'Status' },
  { key: 'date', label: 'Date' },
]

// Map incoming data to table format
const transactions = computed(() =>
  props.recent_transactions?.map((trx) => ({
    id: trx.id,
    transactionId: trx.transaction_id,
    billNo: trx.bill_no,
    amount: `$${Number(trx.total_amount).toLocaleString()}`,
    status: trx.status.charAt(0).toUpperCase() + trx.status.slice(1),
    date: new Date(trx.payment_date).toLocaleDateString('en-GB', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
    }),
  })),
)
</script>
