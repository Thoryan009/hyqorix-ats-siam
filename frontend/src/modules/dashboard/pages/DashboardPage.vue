<template>
  <div class="min-h-screen">
    <div class="mx-auto space-y-6">
      <!-- Welcome Header -->
      <div class="mb-2">
        <!-- <h1 class="text-xl font-semibold text-black">Dashboard Overview</h1> -->
         <h1 class="text-xl font-semibold text-black">{{ t('dashboard.overview') }}</h1>
      </div>

      <!-- Main Content with smooth fade-in animation -->
      <div class="space-y-4 animate-fade-in">
        <QuickNavigation />
        <ProcessExpiryWatchlist v-can="'process_expiry_report.view'" />
        <CandidateProcessPipeline
          v-can="'dashboard.view_candidate_process'"
          :pipeline="rows?.pipeline"
        />

        <FlightSummary
          v-can="'dashboard.view_candidate_process'"
          :flight-summary="rows?.flight_summary"
        />

        <SummaryCard v-can="'dashboard.view_summary'" :summary="rows?.summary" />
        <!-- <WorkOrdersSummary
          v-can="'dashboard.demand_letter_summary'"
          :workOrders="rows?.work_orders"
        />-->
        <!-- <JobStatusOverview v-can="'dashboard.view_job_status'" :jobs="rows?.jobs" /> -->

        <!-- <FinancialOverview v-can="'dashboard.view_financial_overview'" :finance="rows?.finance" />
        <PaymentMethodSummary
          v-can="'dashboard.view_payment_method'"
          :payment_methods="rows?.payment_methods"
        />
        <ClientBillingOverview
          v-can="'dashboard.view_client_billing'"
          :client_billing="rows?.client_billing"
        />-->

        <!-- Grid layout for tables -->
        <!-- <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
          <RecentApplications
            v-can="'dashboard.view_recent_applications'"
            :recent_applications="rows?.recent_applications"
          />
          <RecentTransactions
            v-can="'dashboard.view_recent_transactions'"
            :recent_transactions="rows?.recent_transactions"
          />
        </div>

        <RecentWorkOrders
          v-can="'dashboard.view_recent_demand_letters'"
          :recent_work_orders="rows?.recent_work_orders"
        />-->
        <!-- <UpcomingAlerts v-can="'dashboard.view_upcoming_alerts'" :alerts="rows?.alerts" /> -->
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import UpcomingAlerts from '@/modules/dashboard/components/UpcomingAlerts.vue'
import RecentWorkOrders from '@/modules/dashboard/components/RecentWorkOrders.vue'
import ClientBillingOverview from '@/modules/dashboard/components/ClientBillingOverview.vue'
import RecentApplications from '@/modules/dashboard/components/RecentApplications.vue'
import RecentTransactions from '@/modules/dashboard/components/RecentTransactions.vue'
import QuickNavigation from '../components/QuickNavigation.vue'
import SummaryCard from './components/SummaryCard.vue'
import WorkOrdersSummary from './components/WorkOrdersSummary.vue'
import JobStatusOverview from './components/JobStatusOverview.vue'
import CandidateProcessPipeline from './components/CandidateProcessPipeline.vue'
import FlightSummary from './components/FlightSummary.vue'
import ProcessExpiryWatchlist from './components/ProcessExpiryWatchlist.vue'
import FinancialOverview from './components/FinancialOverview.vue'
import PaymentMethodSummary from './components/PaymentMethodSummary.vue'
import { useDashboardQuery } from '../queries/useDashboardQuery'
import { usePagination } from '@/shared/composables/usePagination'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const pagination = usePagination()
const { page, perPage } = pagination
const { data } = useDashboardQuery(page, perPage)
const rows = computed(() => data.value?.data?.data ?? [])
</script>

<style scoped>
@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fade-in 0.6s ease-out;
}
</style>
