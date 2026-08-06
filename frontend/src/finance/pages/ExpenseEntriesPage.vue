<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <PageTitle>Paid Bills</PageTitle>
        <p class="mt-1 text-sm text-gray-500">{{ pageSubtitle }}</p>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <router-link to="/finance/expense-management" v-can="'account_setup.view'">
          <BaseButton
            v-can="'transaction.view'"
            :className="'border border-gray-300 bg-white text-gray-800 hover:bg-gray-50'"
          >
            <i class="fa fa-tags mr-1"></i> Expense Setup
          </BaseButton>
        </router-link>
        <router-link
          :to="{ path: '/finance/payment-received', query: { tab: 'transaction_entry', payment_mode: 'bills_to_pay' } }"
          v-can="'receive_payment.create'"
        >
          <BaseButton
            v-can="'transaction.view'"
            :className="'border border-gray-300 bg-white text-gray-800 hover:bg-gray-50'"
          >
            <i class="fa fa-clock-o mr-1"></i> Bills To Pay
            <span
              v-if="paymentStore.pendingBillCount"
              class="ml-1.5 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700"
            >
              {{ paymentStore.pendingBillCount }}
            </span>
          </BaseButton>
        </router-link>
        <router-link to="/finance/bills-and-purchases" v-can="'bill_generation.create'">
          <BaseButton v-can="'transaction.view'" class="bg-primary text-white hover:opacity-90">
            <i class="fa fa-plus mr-1"></i> Bills & Purchases
          </BaseButton>
        </router-link>
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
        <h3 class="text-base font-semibold text-gray-900">Paid Bills</h3>
        <p class="mt-1 text-sm text-gray-500">Cash and bank paid bills plus rejected entries (due bills are in Bills Payable)</p>
      </div>

      <div class="p-4">
        <BillEntriesPanel status-scope="processed" />
      </div>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import BillEntriesPanel from './components/payment/BillEntriesPanel.vue'
import { useExpensePaymentStore } from '@/finance/store/expensePaymentStore'

const paymentStore = useExpensePaymentStore()
const route = useRoute()
const router = useRouter()

const pageSubtitle = 'Processed expense bill history'

const summaryCards = computed(() => [
  {
    title: 'Bills To Pay',
    value: paymentStore.pendingBillCount,
    subtitle: 'Pending approval',
    icon: 'fa fa-clock-o',
    iconBg: 'bg-amber-50',
    iconColor: 'text-amber-600',
  },
  {
    title: 'Paid Bills',
    value: paymentStore.approvedBillCount,
    subtitle: 'Cash and bank paid (excludes due bills)',
    icon: 'fa fa-check-circle',
    iconBg: 'bg-emerald-50',
    iconColor: 'text-emerald-600',
  },
  {
    title: 'Total Bills',
    value: paymentStore.payments.length,
    subtitle: 'All recorded expense bills',
    icon: 'fa fa-list-alt',
    iconBg: 'bg-blue-50',
    iconColor: 'text-blue-600',
  },
])

// Bills To Pay moved under Payment / Received → Make Payment
watch(
  () => route.query.tab,
  (tab) => {
    if (tab === 'bill_request') {
      router.replace({
        path: '/finance/payment-received',
        query: { tab: 'transaction_entry', payment_mode: 'bills_to_pay' },
      })
    }
  },
  { immediate: true }
)

onMounted(async () => {
  await paymentStore.fetchBillEntries()
})
</script>
