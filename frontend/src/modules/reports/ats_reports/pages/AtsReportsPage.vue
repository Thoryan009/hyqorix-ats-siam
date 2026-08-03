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
      <AtsReportNavigation />
    </div>
    <!-- ATS Detailed Report Section -->
    <AtsDemoSection />
  </SectionHeader>
</template>

<script setup>
import { useAtsReportStore } from '../store/atsReportStore'
import AtsReportNavigation from './components/AtsReportNavigation.vue'
import AtsDemoSection from './components/AtsDemoSection.vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'

const store = useAtsReportStore()
</script>
