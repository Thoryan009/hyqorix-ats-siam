<template>
  <div v-if="isLoading">
    <div class="flex items-center justify-center min-h-screen">
      <div
        class="loader ease-linear rounded-full border-8 border-t-8 border-gray-200 h-16 w-16"
      ></div>
    </div>
  </div>

  <div v-else>
    <!-- Page wrapper -->
    <div class="min-h-screen flex flex-col">
      <div class="max-w-[210mm] mx-auto py-4 px-0 rounded-lg flex flex-col flex-1">
        <!-- Header -->
        <InvoiceHeader :applicationId="transactionData.application_unique_id" :settingsData="settingsData" />

        <div class="h-0.75 bg-linear-to-r from-black to-black"></div>

        <!-- Content -->
        <InvoiceTitle :transaction="transactionData" />
        <InvoiceMetaInfo :transaction="transactionData" />
        <InvoiceTransactionHistory :transaction="transactionData" />
        <InvoiceAmountSummary :transaction="transactionData" />

        <!-- Remarks -->
        <div v-if="transactionData.remarks" class="mt-6 mb-4 flex gap-2 items-center">
          <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Notes / Remarks:</h4>
          <p class="text-sm text-gray-700 leading-relaxed">{{ transactionData.remarks }}</p>
        </div>

        <!-- Footer -->
        <div class="mt-auto pt-6 border-gray-200 text-center text-gray-800 text-sm">
          <p class="text-xs text-gray-800 italic">
            This is a computer-generated invoice and does not require a physical signature.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import { useSingleTransactionQuery } from '../queries/useTransactionsQuery'
import InvoiceTitle from './candidateInvoiceParts/InvoiceTitle.vue'
import InvoiceMetaInfo from './candidateInvoiceParts/InvoiceMetaInfo.vue'
import InvoiceTransactionHistory from './candidateInvoiceParts/InvoiceTransactionHistory.vue'
import InvoiceAmountSummary from './candidateInvoiceParts/InvoiceAmountSummary.vue'
import { useSettingsQuery } from '@/modules/setting/queries/useSettingsQuery'
import InvoiceHeader from '../shared/component/InvoiceHeader.vue'

const route = useRoute()
const transactionId = computed(() => Number(route.params.transactionId) || null)
const { data, isLoading } = useSingleTransactionQuery(transactionId.value)
const transactionData = computed(() => data.value?.data?.data || {})

const { data: settingsDataResponse } = useSettingsQuery(1)
const settingsData = computed(() => settingsDataResponse.value?.data?.data || {})

// onmounted print
onMounted(() => {
  const printTimeout = setTimeout(() => {
    window.print()
  }, 1000)

  // Clear the timeout if the component is unmounted before printing
  onUnmounted(() => {
    clearTimeout(printTimeout)
  })
})
</script>

<style scoped></style>
