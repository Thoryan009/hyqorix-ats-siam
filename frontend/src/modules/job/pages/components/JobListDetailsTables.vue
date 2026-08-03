<template>
  <div>
    <!-- Page Header -->
    <div class="flex flex-col gap-3 sm:gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="my-3 sm:my-5">
        <PageTitle class="text-lg sm:text-xl">{{ t('job_price.price_details') }}</PageTitle>
      </div>
    </div>
    <!-- Content Card -->
    <div class="overflow-x-auto -mx-2 sm:mx-0">
      <!-- Table  -->
      <div class="min-w-full inline-block align-middle">
        <BaseTable v-if="!isLoading" :columns="columns" :rows="jobStore.item.details">
        </BaseTable>
      </div>
    </div>

  </div>
</template>

<script setup>

// import { useJobDetailsStore } from '../../store/jobDetailsStore'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useJobStore } from '../../store/jobStore'
import { useTranslate } from '@/shared/composables/useTranslate'
import { computed } from 'vue'
const { t } = useTranslate()
const props = defineProps({
  store: {
    type: Object,
    required: true
  }
})

const jobStore = useJobStore()

// const store = useJobDetailsStore()

//  Table Columns

const columnTemp = computed(() => {
  return [
    { key: 'fee_category', label: t('job_price.fee_category') },
    { key: 'fee_name', label: t('job_price.fee_name') },
    { key: 'amount', label: t('job_price.amount') }
  ]
})
const { columns  } = useCrudTable(props.store, columnTemp)


</script>
