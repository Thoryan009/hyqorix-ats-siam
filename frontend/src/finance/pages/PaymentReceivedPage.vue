<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <PageTitle>Payment / Received</PageTitle>
        <p class="mt-1 text-sm text-gray-500">{{ pageSubtitle }}</p>
      </div>

      <div v-if="summaryCards.length" class="flex w-full flex-wrap justify-end gap-2 sm:w-auto">
        <div
          v-for="card in summaryCards"
          :key="card.title"
          class="flex min-w-[140px] items-center gap-2 rounded-lg border border-gray-100 bg-white px-3 py-2 shadow-sm"
        >
          <div
            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md"
            :class="card.iconBg"
          >
            <i :class="[card.icon, card.iconColor, 'text-xs']"></i>
          </div>
          <div class="min-w-0">
            <p class="text-[11px] leading-tight text-gray-500">{{ card.title }}</p>
            <p class="text-sm font-semibold leading-tight text-gray-900">{{ card.value }}</p>
          </div>
        </div>
      </div>
    </PageHeader>

    <div class="mb-6 flex flex-wrap gap-2">
      <button
        v-for="tab in pageTabs"
        v-can="'receive_payment.create'"
        :key="tab.id"
        type="button"
        class="inline-flex min-w-[160px] items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold shadow-sm transition-all"
        :class="pageTabClass(tab.id)"
        @click="setActiveTab(tab.id)"
      >
        <i :class="[tab.icon, 'mr-1.5 text-sm']"></i>
        {{ tab.label }}
        <span
          v-if="tab.id === 'transaction_entry' && paymentStore.pendingBillCount"
          class="ml-2 rounded-full px-2 py-0.5 text-xs font-semibold"
          :class="
            activeTab === 'transaction_entry'
              ? 'bg-white/20 text-white'
              : 'bg-rose-100 text-rose-800'
          "
        >{{ paymentStore.pendingBillCount }}</span>
      </button>
    </div>

    <TransactionSubmittedSuccess
      v-if="submittedTransfer"
      :title="successCopy.title"
      :description="successCopy.description"
      :create-another-label="successCopy.createAnotherLabel"
      :view-label="successCopy.viewLabel"
      :from-label="successCopy.fromLabel"
      :to-label="successCopy.toLabel"
      :amount="submittedTransfer.amount"
      :date="submittedTransfer.date"
      :particular="submittedTransfer.particular"
      :voucher-no="submittedTransfer.voucherNo"
      :reference-no="submittedTransfer.referenceNo"
      :from-account-label="submittedTransfer.fromAccountLabel"
      :to-account-label="submittedTransfer.toAccountLabel"
      :direction-label="submittedTransfer.directionLabel"
      :extra-account-type="submittedTransfer.extraAccountType"
      :extra-account-name="submittedTransfer.extraAccountName"
      @create-another="createAnotherTransfer"
      @view-transactions="goToTransactions"
    />

    <div
      v-else-if="activeTab === 'transaction_entry'"
      class="rounded-xl border border-rose-200 bg-rose-50/70 p-4"
    >
      <TransactionEntryPanel
        :initial-mode="routePaymentMode"
        locked-direction="payment"
        @saved="onOtherTransactionSaved"
      />
    </div>

    <div v-else class="rounded-xl border border-emerald-200 bg-emerald-50/70 shadow-sm">
      <div class="border-b border-emerald-200 px-4 py-4">
        <h3 class="text-base font-semibold text-emerald-950">{{ activeTabMeta.title }}</h3>
        <p class="mt-1 text-sm text-emerald-800/70">{{ activeTabMeta.description }}</p>
      </div>

      <div class="p-4">
        <div class="mb-6 rounded-lg border border-emerald-200 bg-white/80 p-4">
          <h4 class="mb-1 text-sm font-semibold text-emerald-950">Receive Type</h4>
          <p class="mb-3 text-xs text-emerald-800/70">
            Collect gross income, PL income, settle outstanding due receivables, or record other
            receive transactions.
          </p>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="type in receiveTypes"
              :key="type.id"
              type="button"
              class="rounded-lg px-4 py-2 text-sm font-medium transition"
              :class="
                receiveType === type.id
                  ? 'bg-emerald-600 text-white shadow-sm'
                  : 'bg-white text-emerald-900 ring-1 ring-emerald-200 hover:bg-emerald-50'
              "
              @click="setReceiveType(type.id)"
            >
              <i :class="[type.icon, 'mr-1.5']"></i>
              {{ type.label }}
              <span
                v-if="type.id === 'bills_receivable' && saleEntryStore.receivableBillCount"
                class="ml-1.5 rounded-full px-2 py-0.5 text-xs font-semibold"
                :class="
                  receiveType === type.id
                    ? 'bg-white/20 text-white'
                    : 'bg-emerald-100 text-emerald-800'
                "
              >{{ saleEntryStore.receivableBillCount }}</span>
            </button>
          </div>
        </div>

        <TransactionEntryPanel
          v-if="receiveType === 'other_transaction'"
          initial-mode="transaction"
          transaction-only
          locked-direction="receive"
          @saved="onOtherTransactionSaved"
        />
        <BillsReceivablePanel v-else-if="receiveType === 'bills_receivable'" />
        <IncomeCollectionFormPanel v-else-if="receiveType === 'income'" @saved="onReceiveSaved" />
        <SaleEntryFormPanel v-else @saved="onReceiveSaved" />
      </div>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import IncomeCollectionFormPanel from './components/payment/IncomeCollectionFormPanel.vue'
import TransactionEntryPanel from './components/payment/TransactionEntryPanel.vue'
import SaleEntryFormPanel from './components/payment/SaleEntryFormPanel.vue'
import BillsReceivablePanel from './components/payment/BillsReceivablePanel.vue'
import TransactionSubmittedSuccess from './components/payment/TransactionSubmittedSuccess.vue'
import { useExpensePaymentStore } from '@/finance/store/expensePaymentStore'
import { useSaleEntryStore } from '@/finance/store/saleEntryStore'
import { useIncomeCollectionStore } from '@/finance/store/incomeCollectionStore'

const paymentStore = useExpensePaymentStore()
const saleEntryStore = useSaleEntryStore()
const incomeCollectionStore = useIncomeCollectionStore()
const route = useRoute()
const router = useRouter()

const activeTab = ref('transaction_entry')
const receiveType = ref('job')
const submittedTransfer = ref(null)

const pageTabs = [
  { id: 'transaction_entry', label: 'Make Payment', icon: 'fa fa-credit-card' },
  { id: 'sale_entry', label: 'Receive Payment', icon: 'fa fa-money' },
]

function pageTabClass(tabId) {
  const isActive = activeTab.value === tabId

  if (tabId === 'transaction_entry') {
    return isActive
      ? 'bg-rose-600 text-white shadow-md ring-2 ring-rose-300'
      : 'border-2 border-rose-200 bg-rose-50 text-rose-800 hover:bg-rose-100'
  }

  return isActive
    ? 'bg-emerald-600 text-white shadow-md ring-2 ring-emerald-300'
    : 'border-2 border-emerald-200 bg-emerald-50 text-emerald-800 hover:bg-emerald-100'
}

const receiveTypes = [
  { id: 'job', label: 'Sale', icon: 'fa fa-briefcase' },
  { id: 'income', label: 'Income', icon: 'fa fa-plus-circle' },
  { id: 'bills_receivable', label: 'Bills Receivable', icon: 'fa fa-file-text-o' },
  { id: 'other_transaction', label: 'Other Transaction', icon: 'fa fa-exchange' },
]

const tabMeta = {
  transaction_entry: {
    title: 'Make Payment',
    description:
      'Pay pending expense bills, settle approved due bills payable, or record other payment transactions',
    subtitle: 'Bills to pay, bills payable, and other transactions',
  },
  sale_entry: {
    title: 'Receive Payment',
    description:
      'Collect gross income by payer, PL income, settle due bills receivable, or record other receive transactions',
    subtitle: 'Gross income, PL income, bills receivable, and other transactions',
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
  other_transaction: {
    title: 'Receive Payment — Other Transaction',
    description: 'Record receive transfers between accounts or adjust balances',
  },
}

const routePaymentMode = computed(() => {
  if (route.query.payment_mode === 'bills_payable') return 'bills_payable'
  if (route.query.payment_mode === 'transaction') return 'transaction'
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
  if (activeTab.value === 'transaction_entry' && routePaymentMode.value === 'transaction') {
    return {
      title: 'Make Payment — Other Transaction',
      description: 'Record payment transfers between accounts or adjust balances',
      subtitle: 'Transfer between accounts or adjust balances',
    }
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

const successCopy = computed(() => {
  const kind = submittedTransfer.value?.kind
  const isDue = String(submittedTransfer.value?.directionLabel || '').toLowerCase() === 'due'

  if (kind === 'sale') {
    return {
      title: isDue ? 'Sale Due Recorded' : 'Payment Received',
      description: isDue
        ? 'The due sale was recorded. Settle it from Bills Receivable with cash or bank, or collect another sale.'
        : 'The sale collection has been recorded. You can collect another sale or review it in Receipt List.',
      createAnotherLabel: 'Collect Another Sale',
      viewLabel: 'View Receipts',
      fromLabel: 'Payer',
      toLabel: 'Received In',
    }
  }

  if (kind === 'income') {
    return {
      title: isDue ? 'Income Due Recorded' : 'Income Collected',
      description: isDue
        ? 'The due income was recorded. Settle it from Bills Receivable with cash or bank, or collect another income.'
        : 'The income collection has been recorded. You can collect another income or review it in Income List.',
      createAnotherLabel: 'Collect Another Income',
      viewLabel: 'View Income List',
      fromLabel: 'From',
      toLabel: 'Received In',
    }
  }

  return {
    title: 'Transfer Successful',
    description:
      'The transaction has been recorded. You can create another transfer or review it in Transactions.',
    createAnotherLabel: 'Create Another Transaction',
    viewLabel: 'View Transactions',
    fromLabel: 'From',
    toLabel: 'To',
  }
})

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

  if (activeTab.value === 'sale_entry' && receiveType.value === 'other_transaction') {
    return []
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

  // Other Transaction has no summary cards.
  return []
})

const setActiveTab = (tabId) => {
  submittedTransfer.value = null
  activeTab.value = tabId
  const query = { ...route.query, tab: tabId }
  if (tabId !== 'sale_entry') {
    delete query.receive_type
  } else if (receiveType.value === 'income') {
    query.receive_type = 'income'
  } else if (receiveType.value === 'bills_receivable') {
    query.receive_type = 'bills_receivable'
  } else if (receiveType.value === 'other_transaction') {
    query.receive_type = 'other_transaction'
  } else {
    delete query.receive_type
  }
  if (tabId === 'transaction_entry') {
    if (route.query.payment_mode === 'bills_payable') {
      query.payment_mode = 'bills_payable'
    } else if (route.query.payment_mode === 'transaction') {
      query.payment_mode = 'transaction'
    } else {
      query.payment_mode = 'bills_to_pay'
    }
  } else {
    delete query.payment_mode
    delete query.bill_id
  }
  router.replace({ query })
}

const setReceiveType = (typeId) => {
  receiveType.value = typeId
  const query = { ...route.query, tab: 'sale_entry' }
  if (typeId === 'income' || typeId === 'bills_receivable' || typeId === 'other_transaction') {
    query.receive_type = typeId
  } else {
    delete query.receive_type
  }
  router.replace({ query })
}

function goToTransactions() {
  const kind = submittedTransfer.value?.kind
  submittedTransfer.value = null
  if (kind === 'sale') {
    router.push({ path: '/finance/payment-collection' })
    return
  }
  if (kind === 'income') {
    router.push({ path: '/finance/income-list' })
    return
  }
  router.push({ path: '/finance/transactions' })
}

function onOtherTransactionSaved(payload) {
  submittedTransfer.value = payload
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function onReceiveSaved(payload) {
  submittedTransfer.value = payload
  if (payload?.kind === 'income') {
    incomeCollectionStore.fetchCollections({ force: true, page: 1, perPage: 10 })
  }
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function createAnotherTransfer() {
  submittedTransfer.value = null
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function formatSaleAmount(amount) {
  return `৳${Number(amount || 0).toLocaleString('en-BD')}`
}

const applyRouteTab = () => {
  const tab = route.query.tab

  // Bill Generation moved to its own Finance nav page
  if (
    tab === 'bill_entry' ||
    tab === 'pay_expense' ||
    route.query.category_id ||
    route.query.head_id
  ) {
    router.replace({
      path: '/finance/bills-and-purchases',
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

  // Legacy top-level Other Transaction tab → Make Payment
  if (tab === 'other_transaction') {
    activeTab.value = 'transaction_entry'
    router.replace({
      query: { ...route.query, tab: 'transaction_entry', payment_mode: 'transaction' },
    })
    return
  }

  if (pageTabs.some((item) => item.id === tab)) {
    activeTab.value = tab
    if (tab === 'sale_entry') {
      if (route.query.receive_type === 'income') {
        receiveType.value = 'income'
      } else if (route.query.receive_type === 'bills_receivable') {
        receiveType.value = 'bills_receivable'
      } else if (route.query.receive_type === 'other_transaction') {
        receiveType.value = 'other_transaction'
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
  } else if (route.query.receive_type === 'other_transaction') {
    activeTab.value = 'sale_entry'
    receiveType.value = 'other_transaction'
  } else {
    activeTab.value = 'transaction_entry'
    receiveType.value = 'job'
  }
}

watch(() => route.query, applyRouteTab, { immediate: true, deep: true })

async function loadActiveTabData() {
  if (activeTab.value === 'transaction_entry') {
    await paymentStore.fetchBillSummary()
    return
  }

  if (activeTab.value === 'sale_entry') {
    if (receiveType.value === 'income') {
      await incomeCollectionStore.fetchCollections({ force: true, page: 1, perPage: 10 })
      return
    }

    if (receiveType.value === 'bills_receivable') {
      await saleEntryStore.fetchReceivableBills(true)
      return
    }

    if (receiveType.value === 'other_transaction') {
      return
    }

    await Promise.all([
      saleEntryStore.fetchSaleEntries({ force: true, page: 1, perPage: 10 }),
      saleEntryStore.fetchReceivableBills(true),
    ])
  }
}

watch(
  [activeTab, receiveType],
  () => {
    loadActiveTabData()
  },
  { immediate: true }
)
</script>
