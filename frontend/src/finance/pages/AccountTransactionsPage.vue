  <template>
  <SectionHeader>
    <PageHeader>
      <div>
        <PageTitle>Transactions</PageTitle>
        <p class="mt-1 text-sm text-gray-500">
          Review saved account type transactions
        </p>
      </div>
    </PageHeader>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
      <div
        v-for="card in summaryCards"
        :key="card.title"
        class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm"
      >
        <div class="flex items-start justify-between gap-3">
          <div>
            <p class="text-sm text-gray-500">{{ card.title }}</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">{{ card.value }}</p>
            <p class="mt-1 text-xs text-gray-400">{{ card.subtitle }}</p>
          </div>
          <div
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg"
            :class="card.iconBg"
          >
            <i :class="[card.icon, card.iconColor, 'text-lg']"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="rounded-lg bg-white shadow-sm">
      <div class="border-b border-gray-100 px-4 py-4">
        <h3 class="text-base font-semibold text-gray-900">Transaction History</h3>
        <p class="mt-1 text-sm text-gray-500">
          Loan, advanced, repayment, and adjustment records
        </p>
      </div>

      <div class="p-4">
        <TransactionEntriesPanel />
      </div>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import TransactionEntriesPanel from './components/payment/TransactionEntriesPanel.vue'
import { useAccountTransactionStore } from '@/finance/store/accountTransactionStore'
import { useExpensePaymentStore } from '@/finance/store/expensePaymentStore'

const transactionStore = useAccountTransactionStore()
const paymentStore = useExpensePaymentStore()

const summaryCards = computed(() => [
  {
    title: 'Total Transactions',
    value: transactionStore.totalTransactionCount,
    subtitle: 'Saved account type transactions',
    icon: 'fa fa-exchange',
    iconBg: 'bg-violet-50',
    iconColor: 'text-violet-600',
  },
  {
    title: 'This Month',
    value: transactionStore.thisMonthTransactionCount,
    subtitle: 'Transactions in current month',
    icon: 'fa fa-calendar',
    iconBg: 'bg-blue-50',
    iconColor: 'text-blue-600',
  },
  {
    title: 'Pending Bills',
    value: paymentStore.pendingBillCount,
    subtitle: 'Bills awaiting approval',
    icon: 'fa fa-clock-o',
    iconBg: 'bg-amber-50',
    iconColor: 'text-amber-600',
  },
])

onMounted(async () => {
  await Promise.all([
    transactionStore.fetchTransactions(true),
    paymentStore.fetchBillEntries(),
  ])
})
</script>
