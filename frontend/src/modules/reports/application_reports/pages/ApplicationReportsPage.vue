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
    <div class="mb-10">
      <!-- Active -->
      <ApplicationReportNavigation />
    </div>

    <!-- Application Detailed Report Section -->
    <ApplicationDemoSection />
  </SectionHeader>
</template>

<script setup>
import { useApplicationReportStore } from '../store/applicationReportStore'
import ApplicationReportNavigation from './components/ApplicationReportNavigation.vue'
import ApplicationDemoSection from './components/ApplicationDemoSection.vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'

const store = useApplicationReportStore()
</script>
