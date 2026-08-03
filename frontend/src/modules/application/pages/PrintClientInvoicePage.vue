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
        <InvoiceHeader  />

        <div class="h-0.75 bg-gradient-to-r from-black to-black"></div>

        <!-- Content -->
         <InvoiceTitle :transaction="transactionData" />
        <InvoiceMetaInfo :transaction="transactionData" />

         <!-- Remarks -->
        <div v-if="transactionData.positions_summary_paragraph" class="mt-2 mb-1 ">
          <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider"></h4>
          <p class="text-[15px] text-black leading-relaxed">
           <span class="font-medium">Invoice For </span> {{ transactionData.positions_summary_paragraph }}
          </p>
        </div>

        <InvoiceTransactionHistory :transaction="transactionData" />



      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useSingleClientBillQuery } from '../queries/useClientBillQuery'
import InvoiceHeader from './clientInvoiceParts/InvoiceHeader.vue'
import InvoiceTitle from './clientInvoiceParts/InvoiceTitle.vue'
import InvoiceMetaInfo from './clientInvoiceParts/InvoiceMetaInfo.vue'
import InvoiceTransactionHistory from './clientInvoiceParts/InvoiceTransactionHistory.vue'

const route = useRoute()



const billNo = computed(() => route.params.billNo)
const jobId = computed(() => Number(route.params.job_id) || null)
const params = {
  bill_no: billNo.value,
  job_id: jobId.value,
}
const { data, isLoading } = useSingleClientBillQuery(params)
const transactionData = computed(() => data.value?.data?.data || {})

document.title = `Invoice - ${transactionData.value.bill_no || ''}`

watch(transactionData, (newData) => {
  if (newData && newData.bill_no) {
    document.title = `Invoice - ${newData.bill_no}`
  }
})

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

<style scoped>

</style>
