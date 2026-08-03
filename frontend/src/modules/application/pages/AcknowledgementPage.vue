<template>
  <div id="acknowledgement-print">
    <div class="min-h-screen">
      <div class="max-w-[210mm] mx-auto py-4 px-0 rounded-lg">
        <!-- Header -->
        <InvoiceHeader :applicationId="applicationId" :settingsData="settingsData" />

        <!-- Acknowledgement Title -->
        <div class="mt-4 mb-6 text-center">
          <h2 class="text-3xl font-bold text-black mt-28">ACKNOWLEDGEMENT</h2>
        </div>

        <!-- Acknowledgement Content -->
        <div class="mt-10 mb-32">
          <p class="text-base text-black leading-relaxed text-justify">
            I hereby acknowledge that I have received my original passport
            <strong>{{ passportNo }}</strong> along with the flight ticket from
            <strong>{{ settingsData.company_name }}</strong
            >. I confirm that the passport and ticket have been handed over to me in good condition,
            and I am fully satisfied with the documents received. This acknowledgement confirms that
            I bear full responsibility for the received passport and ticket from this date onward.
          </p>
        </div>

        <!-- Candidate Info -->
        <div class="">
          <h3 class="text-base font-bold text-black tracking-wider mb-2">Candidate Details:</h3>
          <div class="text-sm text-black leading-relaxed">
            <p class="font-bold text-black text-base">
              {{ name || 'N/A' }}
            </p>
            <p>{{ phone || 'N/A' }}</p>
          </div>
        </div>

        <!-- Signature Section -->
        <div class="mt-28 flex justify-start">
          <div class="text-left">
            <div class="w-64 border-t border-black pt-3">
              <p class="text-base font-semibold text-black">Candidate Signature</p>
              <p class="text-sm text-black mt-6">Date: ____________________</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useSettingsQuery } from '@/modules/setting/queries/useSettingsQuery'
import InvoiceHeader from '../shared/component/InvoiceHeader.vue'
import { onMounted } from "vue"
import { printWithOrientation } from "@/shared/utils/printOrientation"

const { data } = useSettingsQuery(1)
const settingsData = computed(() => data.value?.data?.data || {})

const route = useRoute()

const applicationId = route.query.application_id
const phone = route.query.phone
const name = route.query.name
const passportNo = route.query.passport_no

onMounted(() => {
  setTimeout(() => {
    printWithOrientation(
      "portrait",
      "10mm",
      "acknowledgement-print"
    )
  }, 800)
})


</script>

<style scoped>

@media print {
  #acknowledgement-print {
    background: white;
    color: black;
  }

  #acknowledgement-print * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
}</style>
