<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <PageTitle>Submitted Bills</PageTitle>
        <p class="mt-1 text-sm text-gray-500">
          Manager review queue — approve to move bills into Bills To Pay
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <router-link to="/finance/bills-and-purchases">
          <BaseButton
            v-can="'bill_generation.create'"
            :className="'border border-gray-300 bg-white text-gray-800 hover:bg-gray-50'"
          >
            <i class="fa fa-file-text-o mr-1"></i> Bills & Purchases
          </BaseButton>
        </router-link>
        <router-link
          :to="{
            path: '/finance/payment-received',
            query: { tab: 'transaction_entry', payment_mode: 'bills_to_pay' },
          }"
        >
          <BaseButton
            v-can="'make_payment.create'"
            :className="'border border-emerald-300 bg-emerald-50 text-emerald-800 hover:bg-emerald-100'"
          >
            <i class="fa fa-credit-card mr-1"></i> Bills To Pay
          </BaseButton>
        </router-link>
      </div>
    </PageHeader>

    <div class="rounded-lg bg-white p-4 shadow-sm">
      <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
          <h3 class="text-base font-semibold text-gray-900">Awaiting Manager Approval</h3>
          <p class="mt-1 text-sm text-gray-500">
            Print anytime. Approve moves the bill to Bills To Pay for payment processing.
          </p>
        </div>
        <span
          class="rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700"
        >
          {{ paymentStore.submittedBillCount }} submitted
        </span>
      </div>

      <div class="mb-4 flex flex-wrap gap-2 border-b border-gray-100 pb-3">
        <button
          v-for="tab in submittedTabs"
          :key="tab.id"
          type="button"
          class="rounded-lg border px-4 py-2 text-sm font-semibold transition-all"
          :class="
            activeSubmittedTab === tab.id
              ? tab.activeClass
              : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300 hover:bg-gray-50'
          "
          @click="activeSubmittedTab = tab.id"
        >
          {{ tab.label }}
          <span class="ml-1.5 rounded-full bg-white/80 px-2 py-0.5 text-xs font-bold">
            {{ tab.count }}
          </span>
        </button>
      </div>

      <BillEntriesPanel status-scope="submitted" :entry-type="activeSubmittedTab" />
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, ref } from 'vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import BillEntriesPanel from './components/payment/BillEntriesPanel.vue'
import { useExpensePaymentStore } from '@/finance/store/expensePaymentStore'

const paymentStore = useExpensePaymentStore()
const activeSubmittedTab = ref('expense_bill')

const submittedTabs = computed(() => [
  {
    id: 'expense_bill',
    label: 'Expense Bills',
    count: paymentStore.submittedExpenseBillCount,
    activeClass: 'border-emerald-500 bg-emerald-50 text-emerald-800 ring-1 ring-emerald-500',
  },
  {
    id: 'asset_purchase',
    label: 'Purchases',
    count: paymentStore.submittedPurchaseCount,
    activeClass: 'border-sky-500 bg-sky-50 text-sky-800 ring-1 ring-sky-500',
  },
])
</script>
