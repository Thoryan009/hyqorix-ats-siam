<template>
  <SectionHeader>
    <!-- Page Header -->
    <PageHeader>
      <div>
        <!-- Responsive Heading -->
        <PageTitle>{{ store.moduleName }}s </PageTitle>
      </div>
    </PageHeader>

    <!-- Bulk Delete & All Kinds of Filters -->
    <div class="flex justify-between items-center my-4">
      <!-- BULK DELETE -->
      <div></div>
      <div></div>
      <!-- FILTERS -->
      <TableFilters :filters="filters" :has-active-filters="hasActiveFilters" @reset="resetFilters">
        <div class="flex flex-col">
          <label class="text-gray-800 text-[15px]">Job</label>
          <BaseSelect v-model="filters.job_id" :options="jobs" placeholder="Select" />
        </div>
      </TableFilters>
    </div>

    <!-- Content Card -->
    <div class="rounded-lg bg-white shadow-sm">
      <div>
        <BaseTableSkeleton v-if="isLoading" :columns="columns" selectable show-actions />
        <BaseTable
          v-else
          :columns="columns"
          :rows="rows"
          :current-page="page"
          :per-page="perPage"
          show-actions
        >
          <template #actions="{ row }">
            <router-link v-can="'candidate_bill.collect'" :to="`/applications/pos/${row.payer_application_id}`">
              <BaseButton className="bg-green-600 text-white hover:bg-green-700 cursor-pointer"
                >Collect Bill
              </BaseButton>
            </router-link>
          </template>
        </BaseTable>

        <!-- Pagination, Per Page & Showing Records -->
        <BasePagination
          v-if="!isLoading"
          :total="total"
          :showing="showing"
          :links="links"
          :per-page="perPage"
          @update:page="setPage"
          @update:perPage="setPerPage"
        />
      </div>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed } from 'vue'
import { useCandidateBillStore } from '../store/candidateBillStore'
import { usePagination } from '@/shared/composables/usePagination'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import { useCandidateBillJobsQuery, useCandidateBillsQuery } from '../queries/useCandidateBillQuery'
import PageHeader from '@/shared/components/ui/PageHeader.vue'

const store = useCandidateBillStore()

const { filters, hasActiveFilters, resetFilters } = useTableFilters({
  searchQuery: '',
  from_date: null,
  to_date: null,
})

const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

// Pass pagination refs into query
const { data, isLoading } = useCandidateBillsQuery(page, perPage, filters)

pagination.bindMeta(data)

const { data: jobsData } = useCandidateBillJobsQuery()

const { columns } = useCrudTable(
  store,
  [
    { key: 'bill_no', label: 'Bill No' },
    { key: 'payer_application_id', label: 'Application ID' },
    { key: 'payer_name', label: 'Applicant Name' },
    { key: 'payer_mobile', label: 'Mobile' },
    { key: 'job', label: 'Job Name' },
    { key: 'work_order_id', label: 'Demand Letter' },
    { key: 'total_amount', label: 'Total Amount' },
    { key: 'due_amount', label: 'Due Amount' },
    { key: 'payer', label: 'Payer' },
    { key: 'client_name', label: 'Client Name' },
    { key: 'status', label: 'Status' },
  ],
  {
    trackUser: false,
    timestamps: false,
  },
)

const rows = computed(() => data.value?.data?.data ?? [])
const jobs = computed(() => jobsData.value?.data?.data ?? [])
</script>
