<template>
  <SectionHeader>
    <!-- Page Header -->
    <PageHeader>
      <div>
        <!-- Responsive Heading -->
        <PageTitle>{{ store.moduleName }} Management</PageTitle>
      </div>

      <RouterLink v-can="'transaction.create'" :to="{ name: 'POS' }">
        <BaseButton>Go To POS</BaseButton>
      </RouterLink>
    </PageHeader>
    <!-- Bulk Delete & All Kinds of Filters -->
    <div class="flex justify-between items-center my-4">
      <!-- BULK DELETE -->
      <div>
        <BaseButton
          v-can="'transaction.delete'"
          v-if="selectedIds.length"
          class="bg-red-600 text-white hover:bg-red-700 mr-2"
          @click="bulkDelete"
          :disabled="removeItemsLoading"
        >
          <span v-if="removeItemsLoading">Deleting...</span>
          <span v-else>Delete Selected ({{ selectedIds.length }})</span>
        </BaseButton>
      </div>

      <div></div>
      <!-- FILTERS -->
      <TableFilters :filters="filters" :has-active-filters="hasActiveFilters" @reset="resetFilters">
        <div class="flex flex-col">
          <label class="text-gray-800 text-[15px]">Bill No</label>
          <BaseSelect v-model="filters.bill_no" :options="transactionBillNo" placeholder="Select" />
        </div>
        <div class="flex flex-col">
          <label class="text-gray-800 text-[15px]">Job List</label>
          <BaseSelect
            v-model="filters.job_list_id"
            :options="transactionJobList"
            placeholder="Select"
          />
        </div>
        <div class="flex flex-col">
          <label class="text-gray-800 text-[15px]">Demand Letter</label>
          <BaseSelect
            v-model="filters.work_order_id"
            :options="transactionWorkOrder"
            placeholder="Select"
          />
        </div>
        <div class="flex flex-col">
          <label class="text-gray-800 text-[15px]">Client</label>
          <BaseSelect
            v-model="filters.client_id"
            :options="transactionClient"
            placeholder="Select"
          />
        </div>
        <div class="flex flex-col">
          <label class="text-gray-800 text-[15px]">Status</label>
          <BaseSelect v-model="filters.status" :options="transactionStatus" placeholder="Select" />
        </div>
        <div class="flex flex-col">
          <label class="text-gray-800 text-[15px]">Payer</label>
          <BaseSelect v-model="filters.payer" :options="transactionPayer" placeholder="Select" />
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
          selectable
          :selected-ids="selectedIds"
          @toggleAll="(checked) => toggleAll(rows, checked)"
          @toggleRow="toggleRow"
        >
          <template #actions="{ row }">
            <BaseTableButton
              icon="fa fa-eye"
              variant="primary"
              title="View"
              @click="onView(row)"
              v-can="'transaction.view'"
            />
            <BaseTableButton
              v-can="'transaction.edit'"
              icon="fa fa-pencil"
              variant="success"
              title="Edit"
              @click="onEdit(row)"
            />
            <BaseTableButton
              v-can="'transaction.delete'"
              icon="fa fa-trash"
              variant="danger"
              title="Delete"
              @click="confirmDelete(row.id)"
            />
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
        <ViewModal v-can="'transaction.view'" />
        <EditModal v-can="'transaction.edit'" />
        <DeleteModal v-can="'transaction.delete'" />
      </div>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, defineAsyncComponent } from 'vue'
import { useTransactionsQuery } from '@/modules/application/queries/useTransactionsQuery'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import { useTransactionStore } from '@/modules/application/store/transactionStore'
import { useTransactionMutations } from '@/modules/application/queries/useTransactionMutation'
import { usePagination } from '@/shared/composables/usePagination'
import { useTransactionQuery } from '@/modules/application/queries/useTransactionsQuery'
import { useBulkDelete } from '@/shared/composables/useBulkDelete'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import { useDeleteWithConfirm } from '@/shared/composables/useDeleteWithConfirm'

const ViewModal = defineAsyncComponent(() => import('./transactionParts/ViewModal.vue'))
const EditModal = defineAsyncComponent(() => import('./transactionParts/EditModal.vue'))
const DeleteModal = defineAsyncComponent(() => import('./transactionParts/DeleteModal.vue'))

const store = useTransactionStore()
const transactionData = useTransactionQuery()

const { filters, hasActiveFilters, resetFilters } = useTableFilters({
  searchQuery: '',
  bill_no: null,
  job_list_id: null,
  work_order_id: null,
  client_id: null,
  status: null,
  payer: null,
  from_date: null,
  to_date: null,
})
// console.log();
const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

// Pass pagination refs into query
const { data, isLoading } = useTransactionsQuery(page, perPage, filters)

pagination.bindMeta(data)

const { remove, removeItems, removeItemsLoading } = useTransactionMutations(store.moduleName)

const { confirmDelete } = useDeleteWithConfirm(remove)

const { selectedIds, toggleAll, toggleRow, bulkDelete } = useBulkDelete(removeItems, {
  confirmText: `Are you sure to Delete selected records?`,
})

const { columns, onView, onEdit } = useCrudTable(
  store,
  [
    { key: 'transaction_id', label: 'Transaction ID' },
    { key: 'bill_no', label: 'Bill No' },
    // { key: 'payer_application_id', label: 'Application ID' },
    { key: 'passport_no', label: 'Passport No' },
    { key: 'payer_name', label: 'Applicant Name' },
    { key: 'payer_mobile', label: 'Mobile' },
    { key: 'job_list', label: 'Job' },
    { key: 'work_order_id', label: 'Demand Letter' },
    { key: 'client_name', label: 'Client Name' },
    { key: 'total_amount_formatted', label: 'Total Amount' },
    { key: 'discount_amount', label: 'Discount Amount' },
    { key: 'paid_amount_formatted', label: 'Paid Amount' },
    { key: 'total_paid_amount_formatted', label: 'Total Paid Amount' },
    { key: 'due_amount', label: 'Due Amount' },
    { key: 'payer', label: 'Payer' },
    { key: 'status', label: 'Status' },
  ],
  { timestamps: false, trackUser: false }
)

const rows = computed(() => data.value?.data?.data ?? [])

const transactionPayer = computed(() => transactionData.data?.value?.data?.data?.payer ?? [])
const transactionWorkOrder = computed(
  () => transactionData.data?.value?.data?.data?.work_order ?? []
)
const transactionClient = computed(() => transactionData.data?.value?.data?.data?.client ?? [])
const transactionJobList = computed(() => transactionData.data?.value?.data?.data?.job_list ?? [])
const transactionBillNo = computed(() => transactionData.data?.value?.data?.data?.bill_no ?? [])
const transactionStatus = computed(() => {
  return transactionData?.data?.value?.data?.data?.status || []
})
</script>
