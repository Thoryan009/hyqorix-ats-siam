<template>
  <SectionHeader>
    <PageHeader>
      <div class="min-w-0">
        <PageTitle>Submitted Bills</PageTitle>
        <p class="mt-1 text-sm text-gray-500">
          Manager review queue — approve to move bills into Bills To Pay
        </p>
      </div>

      <div class="grid w-full grid-cols-1 gap-2 sm:flex sm:w-auto sm:flex-wrap sm:items-center">
        <router-link to="/finance/bills-and-purchases" class="block">
          <BaseButton
            v-can="'bill_generation.create'"
            :className="'w-full border border-gray-300 bg-white text-gray-800 hover:bg-gray-50 sm:w-auto'"
          >
            <i class="fa fa-file-text-o mr-1"></i> Bills & Purchases Authorization
          </BaseButton>
        </router-link>
        <router-link
          class="block"
          :to="{
            path: '/finance/payment-received',
            query: { tab: 'transaction_entry', payment_mode: 'bills_to_pay' },
          }"
        >
          <BaseButton
            v-can="'make_payment.create'"
            :className="'w-full border border-emerald-300 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 sm:w-auto'"
          >
            <i class="fa fa-credit-card mr-1"></i> Bills To Pay
          </BaseButton>
        </router-link>
      </div>
    </PageHeader>

    <div class="rounded-lg bg-white p-3 shadow-sm sm:p-4">
      <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end sm:justify-between">
        <div class="min-w-0">
          <h3 class="text-base font-semibold text-gray-900">Awaiting Manager Approval</h3>
          <p class="mt-1 text-sm text-gray-500">
            Print anytime. Approve moves the bill to Bills To Pay for payment processing.
          </p>
        </div>
        <span
          class="w-fit rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700"
        >
          {{ paymentStore.submittedBillCount }} submitted
        </span>
      </div>

      <div class="-mx-1 mb-4 flex gap-2 overflow-x-auto border-b border-gray-100 px-1 pb-3">
        <button
          v-for="tab in submittedTabs"
          :key="tab.id"
          type="button"
          class="shrink-0 rounded-lg border px-3 py-2 text-sm font-semibold transition-all sm:px-4"
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
