<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <PageTitle>Payment / Received</PageTitle>
        <p class="mt-1 text-sm text-gray-500">{{ pageSubtitle }}</p>
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

    <div class="mb-6 flex flex-wrap gap-2">
      <button
        v-for="tab in pageTabs"
        v-can="'receive_payment.create'"
        :key="tab.id"
        type="button"
        class="rounded-lg border px-4 py-2 text-sm font-semibold transition-all"
        :class="
          activeTab === tab.id
            ? 'border-primary bg-primary-light! text-primary ring-1 ring-primary'
            : 'border-gray-200 bg-white text-gray-700 hover:border-primary hover:bg-primary-light!'
        "
        @click="setActiveTab(tab.id)"
      >
        <i :class="[tab.icon, 'mr-1.5']"></i>{{ tab.label }}
        <span
          v-if="tab.id === 'transaction_entry' && paymentStore.pendingBillCount"
          class="ml-1.5 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700"
        >
          {{ paymentStore.pendingBillCount }}
        </span>
      </button>
    </div>

    <div class="rounded-lg bg-white shadow-sm">
      <div class="border-b border-gray-100 px-4 py-4">
        <h3 class="text-base font-semibold text-gray-900">{{ activeTabMeta.title }}</h3>
        <p class="mt-1 text-sm text-gray-500">{{ activeTabMeta.description }}</p>
      </div>

      <div class="p-4">
        <TransactionEntryPanel
          v-if="activeTab === 'transaction_entry'"
          :initial-mode="routePaymentMode"
          @saved="goToTransactions"
        />

        <TransactionEntryPanel
          v-else-if="activeTab === 'other_transaction'"
          initial-mode="transaction"
          transaction-only
          @saved="goToTransactions"
        />

        <template v-else>
          <div class="mb-6 rounded-lg border border-gray-200 bg-gray-50/80 p-4">
            <h4 class="mb-1 text-sm font-semibold text-gray-900">Receive Type</h4>
            <p class="mb-3 text-xs text-gray-500">
              Collect gross income, PL income, or settle outstanding due receivables.
            </p>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="type in receiveTypes"
                :key="type.id"
                type="button"
                class="rounded-lg px-4 py-2 text-sm font-medium transition"
                :class="
                  receiveType === type.id
                    ? 'bg-primary text-white'
                    : 'bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50'
                "
                @click="setReceiveType(type.id)"
              >
                <i :class="[type.icon, 'mr-1.5']"></i>{{ type.label }}
                <span
                  v-if="type.id === 'bills_receivable' && saleEntryStore.receivableBillCount"
                  class="ml-1.5 rounded-full px-2 py-0.5 text-xs font-semibold"
                  :class="
                    receiveType === type.id
                      ? 'bg-white/20 text-white'
                      : 'bg-emerald-100 text-emerald-800'
                  "
                >
                  {{ saleEntryStore.receivableBillCount }}
                </span>
              </button>
            </div>
          </div>

          <BillsReceivablePanel v-if="receiveType === 'bills_receivable'" />
          <IncomeCollectionFormPanel
            v-else-if="receiveType === 'income'"
            @saved="onIncomeCollected"
          />
          <SaleEntryFormPanel
            v-else
            @saved="goToPaymentCollections"
          />
        </template>
      </div>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import IncomeCollectionFormPanel from './components/payment/IncomeCollectionFormPanel.vue'
import TransactionEntryPanel from './components/payment/TransactionEntryPanel.vue'
import SaleEntryFormPanel from './components/payment/SaleEntryFormPanel.vue'
import BillsReceivablePanel from './components/payment/BillsReceivablePanel.vue'
import { useExpensePaymentStore } from '@/finance/store/expensePaymentStore'
import { useAccountTransactionStore } from '@/finance/store/accountTransactionStore'
import { useSaleEntryStore } from '@/finance/store/saleEntryStore'
import { useIncomeCollectionStore } from '@/finance/store/incomeCollectionStore'

const paymentStore = useExpensePaymentStore()
const transactionStore = useAccountTransactionStore()
const saleEntryStore = useSaleEntryStore()
const incomeCollectionStore = useIncomeCollectionStore()
const route = useRoute()
const router = useRouter()

const activeTab = ref('transaction_entry')
const receiveType = ref('job')

const pageTabs = [
  { id: 'transaction_entry', label: 'Make Payment', icon: 'fa fa-credit-card' },
  { id: 'sale_entry', label: 'Receive Payment', icon: 'fa fa-money' },
  { id: 'other_transaction', label: 'Other Transaction', icon: 'fa fa-exchange' },
]

const receiveTypes = [
  { id: 'job', label: 'Sale', icon: 'fa fa-briefcase' },
  { id: 'income', label: 'Income', icon: 'fa fa-plus-circle' },
  { id: 'bills_receivable', label: 'Bills Receivable', icon: 'fa fa-file-text-o' },
]

const tabMeta = {
  transaction_entry: {
    title: 'Make Payment',
    description: 'Pay pending expense bills or settle approved due bills payable',
    subtitle: 'Bills to pay and bills payable',
  },
  sale_entry: {
    title: 'Receive Payment',
    description:
      'Collect gross income by payer, PL income, or settle due bills receivable',
    subtitle: 'Gross income, PL income, and bills receivable',
  },
  other_transaction: {
    title: 'Other Transaction',
    description: 'Record loan, advanced, and other account type transactions',
    subtitle: 'Transfer between accounts or adjust balances',
  },
}

const receiveTypeMeta = {
  job: {
    title: 'Receive Payment — Sale',
    description: 'Select payer (agent or candidate) then record job-wise payment collections',
  },
  income: {
    title: 'Receive Payment — Income',
    description: 'Collect PL income against income categories and heads configured in Income Setup',
  },
  bills_receivable: {
    title: 'Receive Payment — Bills Receivable',
    description: 'Outstanding due receivables awaiting cash or bank settlement',
  },
}

const routePaymentMode = computed(() => {
  if (route.query.payment_mode === 'bills_payable') return 'bills_payable'
  if (route.query.payment_mode === 'bills_to_pay') return 'bills_to_pay'
  return 'bills_to_pay'
})

const activeTabMeta = computed(() => {
  if (activeTab.value === 'sale_entry') {
    return {
      ...tabMeta.sale_entry,
      ...(receiveTypeMeta[receiveType.value] ?? receiveTypeMeta.job),
    }
  }
  if (activeTab.value === 'other_transaction') {
    return tabMeta.other_transaction
  }
  if (activeTab.value === 'transaction_entry' && routePaymentMode.value === 'bills_to_pay') {
    return {
      title: 'Make Payment — Bills To Pay',
      description: 'Pending expense bills awaiting accountant review and approval',
      subtitle: 'Approve or reject staff expense bill requests',
    }
  }
  if (activeTab.value === 'transaction_entry' && routePaymentMode.value === 'bills_payable') {
    return {
      title: 'Make Payment — Bills Payable',
      description:
        'Approved due bills posted to expense accounts (direct, client recruitment, operating)',
      subtitle: 'Outstanding payables on expense account ledgers',
    }
  }
  return tabMeta[activeTab.value] ?? tabMeta.sale_entry
})
const pageSubtitle = computed(() => activeTabMeta.value.subtitle)

const summaryCards = computed(() => {
  if (activeTab.value === 'sale_entry' && receiveType.value === 'income') {
    return [
      {
        title: 'Total PL Income',
        value: incomeCollectionStore.collections.length,
        subtitle: 'Recorded PL income entries',
        icon: 'fa fa-plus-circle',
        iconBg: 'bg-emerald-50',
        iconColor: 'text-emerald-600',
      },
      {
        title: 'This Month',
        value: formatSaleAmount(incomeCollectionStore.thisMonthCollectedAmount),
        subtitle: 'PL income collected this month',
        icon: 'fa fa-calendar',
        iconBg: 'bg-blue-50',
        iconColor: 'text-blue-600',
      },
      {
        title: 'Total Collected',
        value: formatSaleAmount(incomeCollectionStore.totalCollectedAmount),
        subtitle: 'All PL income amounts',
        icon: 'fa fa-money',
        iconBg: 'bg-violet-50',
        iconColor: 'text-violet-600',
      },
    ]
  }

  if (activeTab.value === 'sale_entry' && receiveType.value === 'bills_receivable') {
    return [
      {
        title: 'Bills Receivable',
        value: saleEntryStore.receivableBillCount,
        subtitle: 'Outstanding due receivables',
        icon: 'fa fa-file-text-o',
        iconBg: 'bg-emerald-50',
        iconColor: 'text-emerald-600',
      },
      {
        title: 'Total Receivable',
        value: formatSaleAmount(saleEntryStore.totalReceivableAmount),
        subtitle: 'Remaining due amount to collect',
        icon: 'fa fa-money',
        iconBg: 'bg-amber-50',
        iconColor: 'text-amber-600',
      },
      {
        title: 'This Month Collections',
        value: saleEntryStore.thisMonthEntryCount,
        subtitle: 'Collections in current month',
        icon: 'fa fa-calendar',
        iconBg: 'bg-blue-50',
        iconColor: 'text-blue-600',
      },
    ]
  }

  if (activeTab.value === 'sale_entry') {
    return [
      {
        title: 'Total Payment Collections',
        value: saleEntryStore.totalEntryCount,
        subtitle: 'Recorded payment collections',
        icon: 'fa fa-money',
        iconBg: 'bg-emerald-50',
        iconColor: 'text-emerald-600',
      },
      {
        title: 'This Month',
        value: saleEntryStore.thisMonthEntryCount,
        subtitle: 'Collections in current month',
        icon: 'fa fa-calendar',
        iconBg: 'bg-blue-50',
        iconColor: 'text-blue-600',
      },
      {
        title: 'Bills Receivable',
        value: saleEntryStore.receivableBillCount,
        subtitle: 'Outstanding due receivables',
        icon: 'fa fa-file-text-o',
        iconBg: 'bg-amber-50',
        iconColor: 'text-amber-600',
      },
    ]
  }

  if (activeTab.value === 'transaction_entry' && routePaymentMode.value === 'bills_payable') {
    return [
      {
        title: 'Bills Payable',
        value: paymentStore.payableBillCount,
        subtitle: 'Approved due bills on expense accounts',
        icon: 'fa fa-file-text-o',
        iconBg: 'bg-violet-50',
        iconColor: 'text-violet-600',
      },
      {
        title: 'Pending Bills',
        value: paymentStore.pendingBillCount,
        subtitle: 'Bills awaiting payment review',
        icon: 'fa fa-clock-o',
        iconBg: 'bg-amber-50',
        iconColor: 'text-amber-600',
      },
      {
        title: 'This Month Paid',
        value: formatSaleAmount(paymentStore.thisMonthPaidAmount),
        subtitle: 'Approved bill payments this month',
        icon: 'fa fa-calendar',
        iconBg: 'bg-blue-50',
        iconColor: 'text-blue-600',
      },
    ]
  }

  if (activeTab.value === 'transaction_entry' && routePaymentMode.value === 'bills_to_pay') {
    return [
      {
        title: 'Pending Bills',
        value: paymentStore.pendingBillCount,
        subtitle: 'Bills awaiting payment review',
        icon: 'fa fa-clock-o',
        iconBg: 'bg-amber-50',
        iconColor: 'text-amber-600',
      },
      {
        title: 'Submitted Bills',
        value: paymentStore.submittedBillCount,
        subtitle: 'Awaiting manager approval',
        icon: 'fa fa-paper-plane',
        iconBg: 'bg-sky-50',
        iconColor: 'text-sky-600',
      },
      {
        title: 'This Month Paid',
        value: formatSaleAmount(paymentStore.thisMonthPaidAmount),
        subtitle: 'Approved bill payments this month',
        icon: 'fa fa-calendar',
        iconBg: 'bg-blue-50',
        iconColor: 'text-blue-600',
      },
    ]
  }

  return [
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
  ]
})

const setActiveTab = (tabId) => {
  activeTab.value = tabId
  const query = { ...route.query, tab: tabId }
  if (tabId !== 'sale_entry') {
    delete query.receive_type
  } else if (receiveType.value === 'income') {
    query.receive_type = 'income'
  } else if (receiveType.value === 'bills_receivable') {
    query.receive_type = 'bills_receivable'
  } else {
    delete query.receive_type
  }
  if (tabId === 'transaction_entry') {
    query.payment_mode =
      route.query.payment_mode === 'bills_payable' ? 'bills_payable' : 'bills_to_pay'
  } else {
    delete query.payment_mode
  }
  router.replace({ query })
}

const setReceiveType = (typeId) => {
  receiveType.value = typeId
  const query = { ...route.query, tab: 'sale_entry' }
  if (typeId === 'income' || typeId === 'bills_receivable') {
    query.receive_type = typeId
  } else {
    delete query.receive_type
  }
  router.replace({ query })
}

function goToTransactions() {
  router.push({ path: '/finance/transactions' })
}

function goToPaymentCollections() {
  router.push({ path: '/finance/payment-collection' })
}

function onIncomeCollected() {
  incomeCollectionStore.fetchCollections(true)
}

function formatSaleAmount(amount) {
  return `৳${Number(amount || 0).toLocaleString('en-BD')}`
}

const applyRouteTab = () => {
  const tab = route.query.tab

  // Bill Generation moved to its own Finance nav page
  if (tab === 'bill_entry' || tab === 'pay_expense' || route.query.category_id || route.query.head_id) {
    router.replace({
      path: '/finance/bill-generation',
      query: {
        ...(route.query.category_id ? { category_id: route.query.category_id } : {}),
        ...(route.query.head_id ? { head_id: route.query.head_id } : {}),
      },
    })
    return
  }

  if (tab === 'sale_entries') {
    router.replace({ path: '/finance/payment-collection' })
    return
  }

  if (tab === 'transaction_entries') {
    router.replace({ path: '/finance/transactions' })
    return
  }

  if (tab === 'bill_entries' || tab === 'history') {
    router.replace({ path: '/finance/expense-entries' })
    return
  }

  // Legacy: Income Collection was a top-level tab — keep old links working
  if (tab === 'income_collection') {
    activeTab.value = 'sale_entry'
    receiveType.value = 'income'
    router.replace({
      query: { ...route.query, tab: 'sale_entry', receive_type: 'income' },
    })
    return
  }

  // Legacy: Other Transaction lived under Make Payment
  if (tab === 'transaction_entry' && route.query.payment_mode === 'transaction') {
    activeTab.value = 'other_transaction'
    const query = { ...route.query, tab: 'other_transaction' }
    delete query.payment_mode
    router.replace({ query })
    return
  }

  if (pageTabs.some((item) => item.id === tab)) {
    activeTab.value = tab
    if (tab === 'sale_entry') {
      if (route.query.receive_type === 'income') {
        receiveType.value = 'income'
      } else if (route.query.receive_type === 'bills_receivable') {
        receiveType.value = 'bills_receivable'
      } else {
        receiveType.value = 'job'
      }
    }
    return
  }

  // Deep links with receive_type open Receive Payment; otherwise default to Make Payment
  if (route.query.receive_type === 'income') {
    activeTab.value = 'sale_entry'
    receiveType.value = 'income'
  } else if (route.query.receive_type === 'bills_receivable') {
    activeTab.value = 'sale_entry'
    receiveType.value = 'bills_receivable'
  } else if (route.query.payment_mode === 'transaction') {
    activeTab.value = 'other_transaction'
    receiveType.value = 'job'
  } else {
    activeTab.value = 'transaction_entry'
    receiveType.value = 'job'
  }
}

watch(() => route.query, applyRouteTab, { immediate: true, deep: true })

onMounted(async () => {
  await Promise.all([
    paymentStore.fetchBillEntries(),
    transactionStore.fetchTransactions(),
    incomeCollectionStore.fetchCollections(),
    saleEntryStore.fetchReceivableBills(true),
  ])
})
</script>
