<template>
  <div
    class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200 hover:shadow-2xl transition-all duration-300 cursor-pointer"
    @click="workOrders?.length && $router.push('/work-orders')"
  >
    <!-- Header with linear -->
    <div
      class="bg-linear-to-r from-purple-500 to-purple-600 px-4 py-2.5 flex items-center justify-between"
    >
      <div class="flex items-center gap-2">
        <div
          class="w-7 h-7 rounded-md bg-white/20 backdrop-blur-sm flex items-center justify-center text-white"
        >
          <i class="fa fa-file-text-o text-sm"></i>
        </div>
        <h2 class="text-sm font-semibold text-white">Latest Demand Letters</h2>
      </div>
      <span class="bg-white/20 backdrop-blur-sm text-white text-xs font-medium px-2.5 py-0.5 rounded-full"
        >{{ workOrders?.length }} Active</span
      >
    </div>

    <!-- Content -->
    <div class="p-6">
      <BaseTable
        :columns="columns"
        :rows="workOrders"
        :scrollable="false"
        theadBgColor="bg-primary/20"
      />
    </div>
  </div>
</template>

<script setup>
import BaseTable from '@/shared/components/base/BaseTable.vue'
import { computed } from 'vue'

// Props
const props = defineProps({
  recent_work_orders: {
    type: Array,
  },
})

// Columns definition
const columns = [
  { key: 'workOrderId', label: 'Demand Letter ID' },
  { key: 'client', label: 'Client' },
  { key: 'candidates', label: 'Candidates' },
  { key: 'endDate', label: 'End Date' },
]

// Map incoming data to table format
const workOrders = computed(() =>
  props.recent_work_orders?.map((wo) => ({
    id: wo.id,
    workOrderId: wo.work_order_id,
    client: wo.client,
    candidates: wo.candidates,
    endDate: new Date(wo.end_date).toLocaleDateString('en-GB', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
    }),
  })),
)
</script>
