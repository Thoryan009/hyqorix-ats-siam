<template>
  <SectionHeader>
    <!-- Page Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <!-- Responsive Heading -->
        <PageTitle>{{ store.moduleName }} Management</PageTitle>
      </div>
    </div>

    <!-- Top Report Cards -->
    <div class="mb-5 md:mb-10">
      <!-- Active -->
      <TransactionReportNavigation />
    </div>
    <!-- Transaction Detailed Report Section -->
    <TransactionDemoSection />
  </SectionHeader>
</template>

<script setup>
import { useTransactionReportStore } from '../store/transactionReportStore'
import TransactionReportNavigation from './components/TransactionReportNavigation.vue'
import TransactionDemoSection from './components/TransactionDemoSection.vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'

const store = useTransactionReportStore()
</script>
