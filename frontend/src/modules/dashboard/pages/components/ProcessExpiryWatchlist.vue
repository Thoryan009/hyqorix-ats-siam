<template>
  <div class="w-full">
    <DashboardSectionHeading
     :title="t('dashboard.process_expiry_watchlist')"
      icon="fa fa-clock-o"
      icon-class="bg-linear-to-br from-tertiary to-tertiary/70"
    />
    <div
      class="bg-white backdrop-blur-lg rounded-2xl shadow-xl p-4 border border-gray-200 hover:shadow-2xl transition-shadow duration-300"
    >
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <button
          v-for="(card, index) in statusCards"
          :key="card.id"
          type="button"
          class="group relative overflow-hidden p-3.5 rounded-xl shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 text-left w-full border-2 bg-linear-to-br from-white to-gray-50"
          :style="{ animationDelay: `${index * 50}ms`, borderColor: `${card.color}55` }"
          @click="goToExpiryReport(card)"
        >
          <div
            class="absolute -right-4 -top-4 w-16 h-16 rounded-full opacity-10 group-hover:scale-150 transition-transform duration-500"
            :style="{ backgroundColor: card.color }"
          ></div>

          <div class="relative grid grid-cols-[auto_1fr] gap-x-2.5 gap-y-0.5 items-center">
            <div
              class="row-span-2 w-9 h-9 rounded-lg flex items-center justify-center shadow-md group-hover:scale-110 transition-all duration-300 self-center"
              :style="{ background: `linear-gradient(135deg, ${card.color}, ${card.color}dd)` }"
            >
              <i :class="`${card.icon} text-sm text-white`"></i>
            </div>

            <p class="text-xs font-semibold uppercase tracking-wide truncate text-black">
             {{ t(`dashboard.expiry_status.${card.id}`) }}
            </p>

            <p class="text-xl font-bold leading-tight" :style="{ color: card.color }">
              {{ card.value }}
            </p>
          </div>

          <div
            class="absolute inset-0 border-2 rounded-xl transition-all duration-300 pointer-events-none opacity-0 group-hover:opacity-100"
            :style="{ borderColor: card.color }"
          ></div>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import DashboardSectionHeading from '../../components/DashboardSectionHeading.vue'
import { useExpiryFilterDataQuery } from '@/modules/reports/expiry_reports/queries/useExpiryReportQuery'
import { useExpiryStatusCards } from '@/modules/reports/expiry_reports/composables/useExpiryStatusCards'
import {useTranslate} from '@/shared/composables/useTranslate'

const { t } = useTranslate()

const router = useRouter()

const filters = ref({
  searchQuery: '',
  process: null,
  client_id: null,
  agent_id: null,
  job_id: null,
  from_date: null,
  to_date: null,
})

const { data: filterData } = useExpiryFilterDataQuery(filters)

const processOptions = computed(() => filterData.value?.data?.documents ?? [])

const { statusCards } = useExpiryStatusCards(processOptions)

const goToExpiryReport = (card) => {
  router.push({
    name: 'Process Expiry Reports',
    query: card.processId ? { process: card.processId } : {},
  })
}
</script>

<style scoped>
@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

button {
  animation: slideIn 0.5s ease-out forwards;
}
</style>
