<template>
  <SectionHeader>
    <!-- Page Header -->
    <PageHeader>
      <div>
        <!-- Responsive Heading -->
        <PageTitle>{{ store.moduleName }}s</PageTitle>
      </div>
    </PageHeader>

    <CardTabs v-model="filters.transaction_status" :tabs="billFilters" />

    <!-- Bulk Delete & All Kinds of Filters -->
    <div class="flex justify-between items-center my-4">
      <!-- BULK DELETE -->

      <div class="flex gap-2 items-center">
        <div>
          <BaseButton
            v-if="selectedIds.length && filters.job_id"
            v-can="'client_bill.send_invoice'"
            @click="store.handleToggleModal('add')"
            >Send Invoice To Client ({{ selectedIds.length }})</BaseButton
          >
        </div>

        <div class="flex gap-2 items-center">
          <BaseButton
           v-can="'client_bill.send_mail'"
            class="bg-violet-600 text-white cursor-pointer"
            v-if="
              selectedIds.length &&
              filters.transaction_status == 'invoice-generated' &&
              filters.bill_no
            "
            @click="store.handleToggleModal('add')"
            >Send Mail To Client ({{ selectedIds.length }})</BaseButton
          >

          <BaseButton
            class="bg-red-600 text-white cursor-pointer"
            v-can="'client_bill.cancel_invoice'"
            v-if="
              selectedIds.length &&
              filters.transaction_status == 'invoice-generated' &&
              filters.bill_no
            "
            @click="cancelledInvoice.mutateAsync({ bill_no: filters.bill_no })"
            :disabled="isCancelling"
          >
            {{ isCancelling ? 'Cancelling...' : `Cancel Invoice (${filters.bill_no})` }}
          </BaseButton>
        </div>
        <div>
          <BaseButton
           v-can="'client_bill.collect_invoice'"
            class="bg-green-600 text-white cursor-pointer"
            v-if="filters.transaction_status == 'invoice-sent' && filters.bill_no"
            @click="collectInvoice.mutateAsync({ bill_no: filters.bill_no })"
            :disabled="isCollecting"
          >
            {{ isCollecting ? 'Collecting...' : `Collect Invoice (${filters.bill_no})` }}
          </BaseButton>
        </div>
      </div>

      <div></div>
      <!-- FILTERS -->
      <TableFilters :filters="filters" :has-active-filters="hasActiveFilters" @reset="resetFilters">
        <div
          class="flex flex-col"
          v-if="
            filters.transaction_status != 'invoice-generated' &&
            filters.transaction_status != 'invoice-sent' &&
            filters.transaction_status != 'paid'
          "
        >
          <label class="text-gray-800 text-[15px]">Jobs</label>
          <BaseSelect v-model="filters.job_id" :options="jobs" placeholder="Select" />
        </div>
        <div class="flex flex-col" v-if="filters.transaction_status != 'bill-generated'">
          <label class="text-gray-800 text-[15px]">Invoice No</label>
          <BaseSelect v-model="filters.bill_no" :options="billNos" placeholder="Select" />
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
          selectable
          :selected-ids="selectedIds"
          @toggleAll="(checked) => toggleAll(rows, checked)"
          @toggleRow="toggleRow"
        ></BaseTable>

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

        <ClientSentBillModal
          :selected-ids="selectedIds"
          :selected-rows="selectedRows"
          @reset="resetFilters"
          :filters="filters"
          v-can="'client_bill.send_invoice'"
        />
      </div>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed } from 'vue'
import { useClientBillStore } from '../store/clientBillStore'
import { usePagination } from '@/shared/composables/usePagination'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import { useClientBillsDataQuery, useClientBillsQuery } from '../queries/useClientBillQuery'
import { useBulkDelete } from '@/shared/composables/useBulkDelete'
import ClientSentBillModal from './clientBillParts/ClientSentBillModal.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import CardTabs from '@/shared/components/ui/CardTabs.vue'
import { useClientBillMutations } from '../queries/useClientBillMutation'

const store = useClientBillStore()

// collect Invoice
const { collectInvoice, isCollecting } = useClientBillMutations(store.moduleName, {
  onSuccess() {
    resetFilters()
  },
  onError(error) {
    console.log('Error:', error)
  },
})

// cancelled Invoice
const { cancelledInvoice, isCancelling } = useClientBillMutations(store.moduleName, {
  onSuccess() {
    resetFilters()
  },
  onError(error) {
    console.log('Error:', error)
  },
})

const { filters, hasActiveFilters, resetFilters } = useTableFilters({
  transaction_status: 'bill-generated',
  job_id: null,
  bill_no: null,
  searchQuery: '',
  from_date: null,
  to_date: null,
})

const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

pagination.setPerPage(100)

const { selectedIds, toggleAll } = useBulkDelete()

const toggleRow = (id) => {
  if (filters.value.transaction_status == 'invoice-generated') {
    alert('You cannot select or deselect bills when the status is "Invoice Generated".')
    return
  } else {
    selectedIds.value.includes(id)
      ? (selectedIds.value = selectedIds.value.filter((i) => i !== id))
      : selectedIds.value.push(id)
  }
}

// Pass pagination refs into query
const { data, isLoading } = useClientBillsQuery(page, perPage, filters, selectedIds)

pagination.bindMeta(data)

const { data: clientBillsData } = useClientBillsDataQuery()

const { columns } = useCrudTable(
  store,
  [
    { key: 'bill_no', label: 'Invoice No' },
    { key: 'payer_application_id', label: 'Application ID' },
    { key: 'payer_name', label: 'Applicant Name' },
    { key: 'payer_mobile', label: 'Mobile' },
    { key: 'job', label: 'Job Name' },
    { key: 'work_order_id', label: 'Demand Letter' },
    { key: 'total_amount_usd', label: 'Total Amount ' },
    { key: 'due_amount_usd', label: 'Due Amount ' },
    { key: 'client_name', label: 'Client Name' },
    { key: 'status', label: 'Status' },
    { key: 'created', label: 'Created' },
    { key: 'updated', label: 'Updated' },
  ],
  {
    timestamps: false,
    trackUser: false,
  },
)

const rows = computed(() => data.value?.data?.data ?? [])
const billFilters = computed(() => {
  const filters = data.value?.data?.bill_filters || []
  return filters.map((filter, index) => ({
    ...filter,
    color: COLOR_ORDER[index % COLOR_ORDER.length],
  }))
})
const selectedRows = computed(() => rows.value.filter((row) => selectedIds.value.includes(row.id)))
// const workOrders = computed(() => clientBillsData.value?.data?.data?.work_orders ?? [])
const jobs = computed(() => clientBillsData.value?.data?.data?.job_lists ?? [])
const billNos = computed(() => {
  const bills = clientBillsData.value?.data?.data?.bill_nos ?? []

  if (filters.value.transaction_status === 'invoice-generated') {
    return bills.filter((bill) => bill.status === 'invoice-generated')
  }

  if (filters.value.transaction_status === 'invoice-sent') {
    return bills.filter((bill) => bill.status === 'invoice-sent')
  }

  return bills
})

const COLOR_ORDER = ['gray', 'blue', 'yellow', 'purple', 'green', 'red']
</script>
