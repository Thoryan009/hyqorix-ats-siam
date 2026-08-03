<template>
  <BaseModal
    :isVisible="store.isModal"
    title="Payment Receipt"
    :maxWidth="`${store.modalWidth}`"
    @close="store.handleToggleModal"
  >
    <!-- SCROLLABLE CONTENT -->
    <div class="space-y-4 text-gray-800 max-h-[90vh] overflow-y-auto min-h-0 px-1">
      <!-- Payment Information Card -->
      <PaymentInformation :receipt="store.receiptData.value" />

      <!-- 💰 AMOUNT SUMMARY -->
      <AmountSummary :receipt="store.receiptData.value" />

      <!-- 🔥 CURRENT PAYMENT HIGHLIGHT -->
      <CurrentPaymentCard :receipt="store.receiptData.value" />

      <!-- 📝 REMARKS -->
      <ReceiptRemarks :receipt="store.receiptData.value" />
      <!-- ACTIONS -->
      <div class="flex justify-end gap-3 pt-4 border-t-2 border-dashed border-gray-300">
        <BaseButton
          @click="store.handleToggleModal"
          :className="'bg-gray-200 hover:bg-gray-300 text-gray-800! cursor-pointer!'"
          >Close</BaseButton
        >
        <router-link
          :to="{
            name: 'Print Candidate Invoice',
            params: {
              transactionId: store.receiptData.value.id,
            },
          }"
          target="_blank"
        >
          <BaseButton class="bg-green-700! hover:bg-green-900! text-white!">
            <i class="fa fa-print"></i>
            Print Invoice
          </BaseButton>
        </router-link>
      </div>
    </div>
  </BaseModal>
</template>

<script setup>
import BaseButton from '@/shared/components/base/BaseButton.vue'
import { useTransactionStore } from '../../store/transactionStore'
import AmountSummary from './receipt/AmountSummary.vue'
import CurrentPaymentCard from './receipt/CurrentPaymentCard.vue'
import PaymentInformation from './receipt/PaymentInformation.vue'
import ReceiptRemarks from './receipt/ReceiptRemarks.vue'

const store = useTransactionStore()
</script>
