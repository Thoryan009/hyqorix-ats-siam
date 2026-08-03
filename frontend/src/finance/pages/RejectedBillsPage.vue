<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <PageTitle>Rejected Bills</PageTitle>
        <p class="mt-1 text-sm text-gray-500">
          Bills rejected by manager review
        </p>
      </div>
    </PageHeader>

    <div class="rounded-lg bg-white p-4 shadow-sm">
      <BillEntriesPanel status-scope="rejected" />
    </div>
  </SectionHeader>
</template>

<script setup>
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import BillEntriesPanel from './components/payment/BillEntriesPanel.vue'
</script>

