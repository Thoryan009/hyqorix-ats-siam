<template>
  <div>
    <!-- Page Header -->
    <div class="flex flex-col gap-3 sm:gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="my-3 sm:my-5">
        <PageTitle class="text-lg sm:text-xl">Price Details</PageTitle>
      </div>
    </div>

    <!-- Content Card -->
    <div class="overflow-x-auto -mx-2 sm:mx-0">
      <!-- Table  -->
      <div class="min-w-full inline-block align-middle">
        <BaseTable v-if="!isLoading" :columns="columns" :rows="workOrderStore.item.details">
        </BaseTable>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useWorkOrderDetailsStore } from '../../store/workOrderDetailsStore'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useWorkOrderStore } from '../../store/workOrderStore'

const workOrderStore = useWorkOrderStore()

const store = useWorkOrderDetailsStore()

//  Table Columns
const { columns } = useCrudTable(
  store,
  [
    { key: 'fee_category', label: 'Fee Category' },
    { key: 'fee_name', label: 'Fee Name' },
    { key: 'amount', label: 'Amount (৳)' },
  ],
  {
    trackUser: false,
    timestamps: false,
  },
)
</script>
