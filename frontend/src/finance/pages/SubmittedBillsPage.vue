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
        <router-link to="/finance/bill-generation">
          <BaseButton
            v-can="'bill_generation.create'"
            :className="'border border-gray-300 bg-white text-gray-800 hover:bg-gray-50'"
          >
            <i class="fa fa-file-text-o mr-1"></i> Bill Generation
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

      <BillEntriesPanel status-scope="submitted" />
    </div>
  </SectionHeader>
</template>

<script setup>
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import BillEntriesPanel from './components/payment/BillEntriesPanel.vue'
import { useExpensePaymentStore } from '@/finance/store/expensePaymentStore'

const paymentStore = useExpensePaymentStore()
</script>
