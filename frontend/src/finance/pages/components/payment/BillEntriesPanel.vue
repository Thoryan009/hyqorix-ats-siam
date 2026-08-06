<template>
  <div>
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end sm:justify-between">
      <div class="flex flex-1 flex-wrap gap-3">
        <div class="flex min-w-[220px] flex-1 flex-col">
          <label class="mb-1 text-sm text-gray-700">Search</label>
          <BaseInput v-model="filters.search" placeholder="Search bill entries..." />
        </div>

        <div v-if="showCategoryFilter" class="flex min-w-[200px] flex-col">
          <label class="mb-1 text-sm text-gray-700">
            {{ entryType === 'asset_purchase' ? 'Type' : 'Expense Category' }}
          </label>
          <BaseSelect
            v-model="filters.categoryId"
            :options="categoryFilterOptions"
            placeholder="All Categories"
          />
        </div>

        <div v-if="showStatusFilter" class="flex min-w-[160px] flex-col">
          <label class="mb-1 text-sm text-gray-700">Status</label>
          <BaseSelect
            v-model="filters.status"
            :options="statusFilterOptions"
            placeholder="All Status"
          />
        </div>

        <BaseButton
          v-if="hasActiveFilters"
          class="bg-gray-600 text-white hover:bg-gray-700"
          @click="resetFilters"
        >
          Reset
        </BaseButton>
      </div>

      <div class="flex flex-wrap gap-2">
        <span
          v-if="statusScope === 'submitted' || statusScope === 'all'"
          class="rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700"
        >
          {{ paymentStore.submittedBillCount }} submitted
        </span>
        <span
          v-if="statusScope === 'pending' || statusScope === 'all'"
          class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700"
        >
          {{ paymentStore.pendingBillCount }} bills to pay
        </span>
        <span
          v-if="statusScope === 'processed' || statusScope === 'all'"
          class="rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700"
        >
          {{ paymentStore.approvedBillCount }} paid
        </span>
        <span
          v-if="statusScope === 'payable'"
          class="rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-700"
        >
          {{ paymentStore.payableBillCount }} payable
        </span>
      </div>
    </div>

    <div
      v-if="isBatchSelectableScope && selectedEntryIds.length"
      class="mb-3 flex flex-wrap items-center justify-between gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2"
    >
      <p class="text-sm text-emerald-800">
        <span class="font-semibold">{{ selectedEntryIds.length }}</span>
        bill{{ selectedEntryIds.length === 1 ? '' : 's' }} selected from batch
        <span class="font-semibold">{{ selectedBatchLabel }}</span>
      </p>
      <div class="flex flex-wrap gap-2">
        <BaseButton
          :className="'border border-emerald-300 bg-white text-emerald-800 hover:bg-emerald-100 cursor-pointer'"
          @click="clearSelection"
        >
          Clear
        </BaseButton>
        <BaseButton
          v-if="statusScope === 'submitted'"
          v-can="'submitted_bills.approve'"
          class="bg-emerald-600 text-white hover:bg-emerald-700"
          @click="openBulkApprove"
        >
          Bulk Approve Batch
        </BaseButton>
        <BaseButton
          v-else-if="statusScope === 'pending'"
          v-can="'receive_payment.create'"
          class="bg-emerald-600 text-white hover:bg-emerald-700"
          @click="openBulkApprove"
        >
          Bulk Pay Batch
        </BaseButton>
      </div>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200">
      <table class="w-full border-collapse text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th
              v-if="isBatchSelectableScope"
              class="w-10 border-b border-gray-200 px-3 py-2 text-center"
            >
              <span class="sr-only">Select</span>
            </th>
            <th class="border-b border-gray-200 px-3 py-2 text-left">Bill Date</th>
            <th class="border-b border-gray-200 px-3 py-2 text-left">Category</th>
            <th class="border-b border-gray-200 px-3 py-2 text-left">
              {{ entryType === 'asset_purchase' ? 'Asset Account' : 'Expense Head' }}
            </th>
            <th class="border-b border-gray-200 px-3 py-2 text-left">Linked Account</th>
            <th class="border-b border-gray-200 px-3 py-2 text-left">Candidate / DL</th>
            <th class="border-b border-gray-200 px-3 py-2 text-left">Bill No</th>
            <th class="border-b border-gray-200 px-3 py-2 text-left">Status</th>
            <th class="border-b border-gray-200 px-3 py-2 text-right">
              {{ statusScope === 'payable' ? 'Remaining' : 'Amount' }}
            </th>
            <th class="border-b border-gray-200 px-3 py-2 text-center">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!hasTableRows">
            <td :colspan="tableColspan" class="px-3 py-8 text-center text-gray-500">
              <p>{{ emptyTitle }}</p>
              <p class="mt-1 text-xs">{{ emptyHint }}</p>
            </td>
          </tr>

          <template v-if="isBatchGroupedScope">
            <template v-for="batch in paginatedBatches" :key="batch.key">
              <tr
                class="border-b"
                :class="batchHeaderClass"
              >
                <td v-if="isBatchSelectableScope" class="px-3 py-2 text-center">
                  <input
                    type="checkbox"
                    class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                    :checked="isBatchFullySelected(batch)"
                    :indeterminate.prop="isBatchPartiallySelected(batch)"
                    :title="
                      selectedBatchKey && selectedBatchKey !== batch.key
                        ? 'Only one batch can be selected for bulk action'
                        : 'Select entire batch'
                    "
                    @change="toggleBatchSelection(batch, $event.target.checked)"
                  />
                </td>
                <td :colspan="isBatchSelectableScope ? tableColspan - 1 : tableColspan" class="px-3 py-2">
                  <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="min-w-0">
                      <button
                        type="button"
                        class="inline-flex items-center gap-2 text-left font-semibold"
                        :class="batchTitleClass"
                        @click="toggleBatchExpanded(batch.key)"
                      >
                        <i
                          class="fa text-xs"
                          :class="[
                            expandedBatchKeys.has(batch.key) ? 'fa-chevron-down' : 'fa-chevron-right',
                            batchIconClass,
                          ]"
                        ></i>
                        <span>Batch: {{ batch.label }}</span>
                      </button>
                      <p
                        class="mt-0.5 pl-5 text-xs"
                        :class="batchMetaClass"
                      >
                        {{ batch.entries.length }} bill{{ batch.entries.length === 1 ? '' : 's' }}
                        · {{ batch.payment_date || '—' }}
                        · {{ batch.category_name || '—' }}
                        <template v-if="batch.requested_by_name">
                          · {{ batch.requested_by_name }}
                        </template>
                      </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                      <span
                        class="text-sm font-bold tabular-nums"
                        :class="batchTitleClass"
                      >
                        {{ formatCurrency(batch.totalAmount) }}
                      </span>
                      <BaseTableButton
                        v-if="statusScope === 'submitted'"
                        v-can="'submitted_bills.print'"
                        icon="fa fa-print"
                        variant="info"
                        title="Print Batch"
                        @click="printEntry(batch.entries[0])"
                      />
                      <BaseTableButton
                        v-if="statusScope === 'submitted'"
                        v-can="'submitted_bills.approve'"
                        icon="fa fa-check-square-o"
                        variant="success"
                        :title="
                          batch.entries.length > 1
                            ? 'Bulk Approve Batch'
                            : 'Manager Review'
                        "
                        @click="openBatchReview(batch)"
                      />
                      <BaseTableButton
                        v-else-if="statusScope === 'pending'"
                        v-can="'receive_payment.create'"
                        icon="fa fa-check-square-o"
                        variant="success"
                        :title="
                          batch.entries.length > 1 ? 'Bulk Pay Batch' : 'Review & Pay'
                        "
                        @click="openBatchReview(batch)"
                      />
                    </div>
                  </div>
                </td>
              </tr>

              <template v-if="expandedBatchKeys.has(batch.key)">
                <tr
                  v-for="entry in batch.entries"
                  :key="entry.id"
                  class="border-b border-gray-100 hover:bg-gray-50"
                >
                  <td v-if="isBatchSelectableScope" class="px-3 py-2 text-center">
                    <input
                      type="checkbox"
                      class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                      :checked="selectedEntryIds.includes(entry.id)"
                      :title="
                        selectedBatchKey && selectedBatchKey !== batch.key
                          ? 'Only one batch can be selected for bulk action'
                          : 'Select bill'
                      "
                      @change="toggleEntrySelection(entry, $event.target.checked)"
                    />
                  </td>
                  <td class="px-3 py-2 whitespace-nowrap">{{ entry.payment_date }}</td>
                  <td class="px-3 py-2">{{ entry.category_name }}</td>
                  <td class="px-3 py-2 font-medium text-gray-900">{{ entry.head_name }}</td>
                  <td class="px-3 py-2">
                    <template v-if="hasLinkedBillAccount(entry)">
                      <p class="font-medium text-gray-900">{{ getLinkedBillAccountTypeLabel(entry) }}</p>
                      <p class="text-xs text-gray-500">{{ getLinkedBillAccountName(entry) }}</p>
                    </template>
                    <span v-else class="text-gray-400">—</span>
                  </td>
                  <td class="px-3 py-2">
                    <template v-if="entry.candidate_name">
                      <p class="font-medium text-gray-900">{{ entry.candidate_name }}</p>
                      <p class="text-xs text-gray-500">
                        {{ entry.passport_no }} · {{ entry.application_status }}
                      </p>
                      <p class="text-xs text-gray-500">{{ entry.job_name }}</p>
                    </template>
                    <template v-else-if="entry.demand_letter">
                      <p class="font-medium text-gray-900">{{ entry.demand_letter }}</p>
                      <p class="text-xs text-gray-500">{{ entry.client_name }}</p>
                      <p v-if="entry.demand_letter_country" class="text-xs text-gray-500">
                        {{ entry.demand_letter_country }}
                      </p>
                    </template>
                    <span v-else class="text-gray-400">—</span>
                  </td>
                  <td class="px-3 py-2">
                    <div>{{ entry.voucher_no || entry.reference_no || '—' }}</div>
                    <p v-if="entry.request_no" class="text-xs text-gray-500">
                      Req: {{ entry.request_no }}
                    </p>
                  </td>
                  <td class="px-3 py-2">
                    <span
                      class="rounded-full px-2.5 py-1 text-xs font-semibold"
                      :class="statusClass(entry.status)"
                    >
                      {{ statusLabel(entry.status) }}
                    </span>
                  </td>
                  <td class="px-3 py-2 text-right font-semibold text-red-600">
                    {{ formatCurrency(displayEntryAmount(entry)) }}
                  </td>
                  <td class="px-3 py-2 text-center">
                    <div class="inline-flex items-center justify-center gap-1.5">
                      <BaseTableButton
                        v-if="statusScope === 'submitted'"
                        v-can="'submitted_bills.print'"
                        icon="fa fa-print"
                        variant="info"
                        title="Print Bill"
                        @click="printEntry(entry)"
                      />
                      <BaseTableButton
                        v-can="'receive_payment.create'"
                        :icon="reviewIcon(entry)"
                        :variant="reviewVariant(entry)"
                        :title="reviewTitle(entry)"
                        @click="openReview(entry)"
                      />
                    </div>
                  </td>
                </tr>
              </template>
            </template>
          </template>

          <template v-else>
            <tr
              v-for="entry in paginatedRows"
              :key="entry.id"
              class="border-b border-gray-100 hover:bg-gray-50"
            >
              <td class="px-3 py-2 whitespace-nowrap">{{ entry.payment_date }}</td>
              <td class="px-3 py-2">{{ entry.category_name }}</td>
              <td class="px-3 py-2 font-medium text-gray-900">{{ entry.head_name }}</td>
              <td class="px-3 py-2">
                <template v-if="hasLinkedBillAccount(entry)">
                  <p class="font-medium text-gray-900">{{ getLinkedBillAccountTypeLabel(entry) }}</p>
                  <p class="text-xs text-gray-500">{{ getLinkedBillAccountName(entry) }}</p>
                </template>
                <span v-else class="text-gray-400">—</span>
              </td>
              <td class="px-3 py-2">
                <template v-if="entry.candidate_name">
                  <p class="font-medium text-gray-900">{{ entry.candidate_name }}</p>
                  <p class="text-xs text-gray-500">
                    {{ entry.passport_no }} · {{ entry.application_status }}
                  </p>
                  <p class="text-xs text-gray-500">{{ entry.job_name }}</p>
                </template>
                <template v-else-if="entry.demand_letter">
                  <p class="font-medium text-gray-900">{{ entry.demand_letter }}</p>
                  <p class="text-xs text-gray-500">{{ entry.client_name }}</p>
                  <p v-if="entry.demand_letter_country" class="text-xs text-gray-500">
                    {{ entry.demand_letter_country }}
                  </p>
                </template>
                <span v-else class="text-gray-400">—</span>
              </td>
              <td class="px-3 py-2">
                <div>{{ entry.voucher_no || entry.reference_no || '—' }}</div>
                <p v-if="entry.batch_ref" class="text-xs text-gray-500">Batch: {{ entry.batch_ref }}</p>
              </td>
              <td class="px-3 py-2">
                <span
                  class="rounded-full px-2.5 py-1 text-xs font-semibold"
                  :class="statusClass(entry.status)"
                >
                  {{ statusLabel(entry.status) }}
                </span>
              </td>
              <td class="px-3 py-2 text-right font-semibold text-red-600">
                {{ formatCurrency(displayEntryAmount(entry)) }}
                <p
                  v-if="statusScope === 'payable' && Number(entry.paid_amount) > 0"
                  class="text-xs font-normal text-gray-500"
                >
                  of {{ formatCurrency(entry.amount) }}
                </p>
              </td>
              <td class="px-3 py-2 text-center">
                <div class="inline-flex items-center justify-center gap-1.5">
                  <BaseTableButton
                    v-can="'receive_payment.create'"
                    :icon="reviewIcon(entry)"
                    :variant="reviewVariant(entry)"
                    :title="reviewTitle(entry)"
                    @click="openReview(entry)"
                  />
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <BasePagination
      class="mt-4"
      :total="paginationTotal"
      :showing="showing"
      :links="links"
      :per-page="perPage"
      @update:page="setPage"
      @update:perPage="setPerPage"
    />

    <BillManagerApproveModal
      v-if="statusScope === 'submitted' || selectedEntry?.status === 'submitted'"
      :is-visible="reviewModalOpen && (statusScope === 'submitted' || selectedEntry?.status === 'submitted')"
      :entry="selectedEntry"
      :entries="selectedReviewEntries"
      :readonly="selectedEntry?.status !== 'submitted'"
      @close="closeReview"
      @approved="handleApproved"
      @rejected="handleRejected"
    />

    <BillGenerationPreview
      v-if="printEntryData"
      ref="printPreviewRef"
      print-only
      :request-no="printEntryData.requestNo"
      :bill-no="printEntryData.billNo"
      :prepared-by-name="printEntryData.preparedByName"
      :bill-date="printEntryData.billDate"
      :payment-method-label="printEntryData.paymentMethodLabel"
      :reference-no="printEntryData.referenceNo"
      :category-name="printEntryData.categoryName"
      :category-code="printEntryData.categoryCode"
      :expense-head-name="printEntryData.expenseHeadName"
      :particular="printEntryData.particular"
      :remarks="printEntryData.remarks"
      :total-amount="printEntryData.totalAmount"
      :unit-amount-note="printEntryData.unitAmountNote"
      :demand-letter="printEntryData.demandLetter"
      :applications="printEntryData.applications"
      :linked-accounts="printEntryData.linkedAccounts || []"
      :job-name="printEntryData.jobName"
      :line-items="printEntryData.lineItems || []"
    />
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import Swal from 'sweetalert2'
import { useExpensePaymentStore } from '@/finance/store/expensePaymentStore'
import { useExpenseCategoryStore } from '@/finance/store/expenseCategoryStore'
import { billEntryStatuses } from '@/finance/data/expensePaymentData'
import { paymentMethods } from '@/finance/data/paymentData'
import { DIRECT_COST_CATEGORY_CODE } from '@/finance/data/expenseCategoryCodes'
import {
  formatCurrency,
  formatDirectExpenseParticularDescription,
  getBillBatchKey,
  getBillBatchLabel,
} from '@/finance/utils/billUtils'
import {
  getLinkedBillAccountName,
  getLinkedBillAccountTypeLabel,
  hasLinkedBillAccount,
} from '@/finance/utils/linkedAccountOptions'
import { isPayableBill, isProcessedBill, getPayableRemainingAmount } from '@/finance/utils/payableBillUtils'
import BillManagerApproveModal from './BillManagerApproveModal.vue'
import BillGenerationPreview from './BillGenerationPreview.vue'

const props = defineProps({
  statusScope: {
    type: String,
    default: 'all',
    validator: (value) =>
      ['all', 'submitted', 'pending', 'processed', 'payable', 'rejected'].includes(value),
  },
  entryType: {
    type: String,
    default: 'all',
    validator: (value) => ['all', 'expense_bill', 'asset_purchase'].includes(value),
  },
})

const router = useRouter()
const paymentStore = useExpensePaymentStore()
const categoryStore = useExpenseCategoryStore()

const isBatchGroupedScope = computed(() =>
  ['submitted', 'pending', 'processed', 'rejected'].includes(props.statusScope)
)

const isBatchSelectableScope = computed(
  () => props.statusScope === 'submitted' || props.statusScope === 'pending'
)

const showCategoryFilter = computed(() => props.entryType !== 'asset_purchase')

const entryType = computed(() => props.entryType)

const batchHeaderClass = computed(() => {
  if (props.statusScope === 'pending') return 'border-amber-100 bg-amber-50/80'
  if (props.statusScope === 'processed') return 'border-green-100 bg-green-50/80'
  if (props.statusScope === 'rejected') return 'border-red-100 bg-red-50/80'
  return 'border-sky-100 bg-sky-50/80'
})

const batchTitleClass = computed(() => {
  if (props.statusScope === 'pending') return 'text-amber-900'
  if (props.statusScope === 'processed') return 'text-green-900'
  if (props.statusScope === 'rejected') return 'text-red-900'
  return 'text-sky-900'
})

const batchIconClass = computed(() => {
  if (props.statusScope === 'pending') return 'text-amber-600'
  if (props.statusScope === 'processed') return 'text-green-600'
  if (props.statusScope === 'rejected') return 'text-red-600'
  return 'text-sky-600'
})

const batchMetaClass = computed(() => {
  if (props.statusScope === 'pending') return 'text-amber-700/80'
  if (props.statusScope === 'processed') return 'text-green-700/80'
  if (props.statusScope === 'rejected') return 'text-red-700/80'
  return 'text-sky-700/80'
})

const filters = reactive({
  search: '',
  categoryId: '',
  status: '',
})

const page = ref(1)
const perPage = ref(10)
const showing = ref(0)
const links = ref([])
const reviewModalOpen = ref(false)
const selectedEntry = ref(null)
const selectedReviewEntries = ref([])
const selectedEntryIds = ref([])
const selectedBatchKey = ref('')
const expandedBatchKeys = ref(new Set())
const printEntryData = ref(null)
const printPreviewRef = ref(null)

const categoryFilterOptions = computed(() =>
  categoryStore.categories.map((category) => ({
    id: category.id,
    name: category.name,
  }))
)

const statusFilterOptions = computed(() => {
  if (props.statusScope === 'submitted') {
    return billEntryStatuses
      .filter((status) => status.id === 'submitted')
      .map((status) => ({ id: status.id, name: status.name }))
  }

  if (props.statusScope === 'pending') {
    return billEntryStatuses
      .filter((status) => status.id === 'pending')
      .map((status) => ({ id: status.id, name: status.name }))
  }

  if (props.statusScope === 'processed') {
    return billEntryStatuses
      .filter((status) => ['approved', 'rejected'].includes(status.id))
      .map((status) => ({ id: status.id, name: status.name }))
  }

  if (props.statusScope === 'rejected') {
    return billEntryStatuses
      .filter((status) => status.id === 'rejected')
      .map((status) => ({ id: status.id, name: status.name }))
  }

  if (props.statusScope === 'payable') {
    return billEntryStatuses
      .filter((status) => status.id === 'approved')
      .map((status) => ({ id: status.id, name: status.name }))
  }

  return billEntryStatuses.map((status) => ({
    id: status.id,
    name: status.name,
  }))
})

const showStatusFilter = computed(
  () =>
    props.statusScope !== 'pending' &&
    props.statusScope !== 'submitted' &&
    props.statusScope !== 'payable' &&
    props.statusScope !== 'rejected'
)

const scopedPayments = computed(() => {
  let rows = paymentStore.payments

  if (props.statusScope === 'submitted') {
    rows = rows.filter((entry) => entry.status === 'submitted')
  } else if (props.statusScope === 'pending') {
    rows = rows.filter((entry) => entry.status === 'pending')
  } else if (props.statusScope === 'processed') {
    rows = rows.filter((entry) => isProcessedBill(entry))
  } else if (props.statusScope === 'rejected') {
    rows = rows.filter((entry) => entry.status === 'rejected')
  } else if (props.statusScope === 'payable') {
    rows = rows.filter((entry) => isPayableBill(entry))
  }

  if (props.entryType === 'asset_purchase') {
    return rows.filter((entry) => entry.entry_type === 'asset_purchase')
  }

  if (props.entryType === 'expense_bill') {
    return rows.filter((entry) => (entry.entry_type || 'expense_bill') !== 'asset_purchase')
  }

  return rows
})

const filteredRows = computed(() => {
  const query = filters.search.trim().toLowerCase()

  return scopedPayments.value.filter((entry) => {
    const matchesSearch =
      !query ||
      String(entry.category_name || '').toLowerCase().includes(query) ||
      String(entry.asset_account_name || '').toLowerCase().includes(query) ||
      entry.candidate_name?.toLowerCase().includes(query) ||
      entry.passport_no?.toLowerCase().includes(query) ||
      entry.job_name?.toLowerCase().includes(query) ||
      entry.demand_letter?.toLowerCase().includes(query) ||
      entry.client_name?.toLowerCase().includes(query) ||
      String(entry.head_name || '').toLowerCase().includes(query) ||
      getLinkedBillAccountTypeLabel(entry).toLowerCase().includes(query) ||
      getLinkedBillAccountName(entry).toLowerCase().includes(query) ||
      entry.particular.toLowerCase().includes(query) ||
      String(entry.voucher_no || '').toLowerCase().includes(query) ||
      String(entry.batch_ref || '').toLowerCase().includes(query) ||
      String(entry.request_no || '').toLowerCase().includes(query)

    const matchesCategory =
      !filters.categoryId || Number(entry.category_id) === Number(filters.categoryId)

    const matchesStatus = !filters.status || entry.status === filters.status

    return matchesSearch && matchesCategory && matchesStatus
  })
})

const paginatedRows = computed(() => {
  const start = (page.value - 1) * perPage.value
  return filteredRows.value.slice(start, start + perPage.value)
})

const groupedBatches = computed(() => {
  const groups = new Map()

  for (const entry of filteredRows.value) {
    const key = getBillBatchKey(entry)
    if (!groups.has(key)) {
      groups.set(key, {
        key,
        label: getBillBatchLabel(entry),
        payment_date: entry.payment_date || '',
        category_name: entry.category_name || '',
        requested_by_name: entry.requested_by_name || '',
        entries: [],
        totalAmount: 0,
      })
    }
    const group = groups.get(key)
    group.entries.push(entry)
    group.totalAmount += Number(entry.amount) || 0
  }

  return [...groups.values()].sort((a, b) => {
    const dateCompare = String(b.payment_date || '').localeCompare(String(a.payment_date || ''))
    if (dateCompare) return dateCompare
    return String(b.label).localeCompare(String(a.label))
  })
})

const paginatedBatches = computed(() => {
  const start = (page.value - 1) * perPage.value
  return groupedBatches.value.slice(start, start + perPage.value)
})

const paginationTotal = computed(() =>
  isBatchGroupedScope.value ? groupedBatches.value.length : filteredRows.value.length
)

const hasTableRows = computed(() =>
  isBatchGroupedScope.value ? paginatedBatches.value.length > 0 : paginatedRows.value.length > 0
)

const tableColspan = computed(() => {
  if (isBatchSelectableScope.value) return 10
  if (isBatchGroupedScope.value) return 9
  return 9
})

const selectedBatchLabel = computed(() => {
  if (!selectedBatchKey.value) return ''
  const batch = groupedBatches.value.find((item) => item.key === selectedBatchKey.value)
  return batch?.label || ''
})

const hasActiveFilters = computed(
  () => Boolean(filters.search || filters.categoryId || filters.status)
)

const emptyTitle = computed(() => {
  if (props.statusScope === 'submitted' && props.entryType === 'asset_purchase') {
    return 'No submitted purchases awaiting manager approval.'
  }
  if (props.statusScope === 'submitted' && props.entryType === 'expense_bill') {
    return 'No submitted expense bills awaiting manager approval.'
  }
  if (props.statusScope === 'submitted') return 'No submitted bills awaiting manager approval.'
  if (props.statusScope === 'pending') return 'No expense bills ready to pay.'
  if (props.statusScope === 'processed') return 'No processed bill entries yet.'
  if (props.statusScope === 'rejected') return 'No rejected bills yet.'
  if (props.statusScope === 'payable') return 'No bills payable on expense accounts.'
  return 'No bill entries yet.'
})

const emptyHint = computed(() => {
  if (props.statusScope === 'submitted' && props.entryType === 'asset_purchase') {
    return 'Submit an Asset Purchase from Bills & Purchases to see it here.'
  }
  if (props.statusScope === 'submitted' && props.entryType === 'expense_bill') {
    return 'New expense bills appear here after staff submit from Bills & Purchases.'
  }
  if (props.statusScope === 'submitted') {
    return 'New bills appear here after staff submit from Bills & Purchases.'
  }
  if (props.statusScope === 'pending') {
    return 'Bills appear here after a manager approves them from Submitted Bills.'
  }
  if (props.statusScope === 'processed') {
    return 'Approved (cash/bank paid) and rejected bills will show here.'
  }
  if (props.statusScope === 'rejected') {
    return 'Rejected bills appear here after the rejection action.'
  }
  if (props.statusScope === 'payable') {
    return 'Approved due bills linked to expense accounts appear here after payment review.'
  }
  return 'Save a bill from Finance → Bill Generation to see it here.'
})

const statusLabel = (status) =>
  billEntryStatuses.find((item) => item.id === status)?.name ?? status

function displayEntryAmount(entry) {
  if (props.statusScope === 'payable') {
    return getPayableRemainingAmount(entry)
  }

  return Number(entry.amount) || 0
}

const statusClass = (status) => {
  const classes = {
    submitted: 'bg-sky-100 text-sky-700',
    pending: 'bg-amber-100 text-amber-700',
    approved: 'bg-green-100 text-green-700',
    rejected: 'bg-red-100 text-red-700',
  }

  return classes[status] || classes.pending
}

const reviewIcon = (entry) => {
  if (props.statusScope === 'payable') return 'fa fa-money'
  if (entry.status === 'submitted' || entry.status === 'pending') return 'fa fa-check-square-o'
  return 'fa fa-eye'
}

const reviewVariant = (entry) => {
  if (props.statusScope === 'payable') return 'success'
  if (entry.status === 'submitted' || entry.status === 'pending') return 'success'
  return 'primary'
}

const reviewTitle = (entry) => {
  if (props.statusScope === 'payable') return 'Make Payment'
  if (entry.status === 'submitted') return 'Manager Review'
  if (entry.status === 'pending') return 'Review & Pay'
  return 'View Details'
}

const canPrintEntry = (entry) =>
  ['submitted', 'pending', 'approved'].includes(entry.status)

const openReview = (entry) => {
  if (props.statusScope === 'payable') {
    router.push({
      name: 'Bill Payable Payment',
      params: { id: entry.id },
    })
    return
  }

  if (entry.status === 'submitted' || props.statusScope === 'submitted') {
    selectedEntry.value = { ...entry }
    selectedReviewEntries.value = [{ ...entry }]
    reviewModalOpen.value = true
    return
  }

  router.push({
    name: 'Bill Payment Review',
    params: { id: entry.id },
  })
}

const openBatchReview = (batch) => {
  if (!batch?.entries?.length) return

  if (props.statusScope === 'pending') {
    openPendingBatchPay(batch.entries)
    return
  }

  selectedEntry.value = { ...batch.entries[0] }
  selectedReviewEntries.value = batch.entries.map((entry) => ({ ...entry }))
  reviewModalOpen.value = true
}

const openBulkApprove = async () => {
  const selected = filteredRows.value.filter((entry) => selectedEntryIds.value.includes(entry.id))
  if (!selected.length) return

  const keys = new Set(selected.map((entry) => getBillBatchKey(entry)))
  if (keys.size !== 1) {
    await Swal.fire({
      icon: 'warning',
      title: 'Different Batches',
      text: 'Bulk action is only allowed for bills from the same batch.',
      confirmButtonColor: '#22C55E',
    })
    return
  }

  if (props.statusScope === 'pending') {
    openPendingBatchPay(selected)
    return
  }

  selectedEntry.value = { ...selected[0] }
  selectedReviewEntries.value = selected.map((entry) => ({ ...entry }))
  reviewModalOpen.value = true
}

function openPendingBatchPay(entries) {
  const list = Array.isArray(entries) ? entries : []
  if (!list.length) return

  const ids = list.map((entry) => entry.id).filter(Boolean)
  router.push({
    name: 'Bill Payment Review',
    params: { id: ids[0] },
    query: ids.length > 1 ? { ids: ids.join(',') } : {},
  })
}

const closeReview = () => {
  reviewModalOpen.value = false
  selectedEntry.value = null
  selectedReviewEntries.value = []
}

const handleApproved = () => {
  clearSelection()
  closeReview()
}

const handleRejected = () => {
  clearSelection()
  closeReview()
}

function ensureBatchExpanded(batchKey) {
  const next = new Set(expandedBatchKeys.value)
  next.add(batchKey)
  expandedBatchKeys.value = next
}

function toggleBatchExpanded(batchKey) {
  const next = new Set(expandedBatchKeys.value)
  if (next.has(batchKey)) next.delete(batchKey)
  else next.add(batchKey)
  expandedBatchKeys.value = next
}

function clearSelection() {
  selectedEntryIds.value = []
  selectedBatchKey.value = ''
}

function isBatchFullySelected(batch) {
  return batch.entries.every((entry) => selectedEntryIds.value.includes(entry.id))
}

function isBatchPartiallySelected(batch) {
  const selectedCount = batch.entries.filter((entry) =>
    selectedEntryIds.value.includes(entry.id)
  ).length
  return selectedCount > 0 && selectedCount < batch.entries.length
}

function toggleBatchSelection(batch, checked) {
  if (checked) {
    if (selectedBatchKey.value && selectedBatchKey.value !== batch.key) {
      selectedEntryIds.value = []
    }
    selectedBatchKey.value = batch.key
    selectedEntryIds.value = batch.entries.map((entry) => entry.id)
    ensureBatchExpanded(batch.key)
    return
  }

  selectedEntryIds.value = selectedEntryIds.value.filter(
    (id) => !batch.entries.some((entry) => entry.id === id)
  )
  if (!selectedEntryIds.value.length) {
    selectedBatchKey.value = ''
  }
}

function toggleEntrySelection(entry, checked) {
  const batchKey = getBillBatchKey(entry)

  if (checked) {
    if (selectedBatchKey.value && selectedBatchKey.value !== batchKey) {
      selectedEntryIds.value = []
    }
    selectedBatchKey.value = batchKey
    if (!selectedEntryIds.value.includes(entry.id)) {
      selectedEntryIds.value = [...selectedEntryIds.value, entry.id]
    }
    ensureBatchExpanded(batchKey)
    return
  }

  selectedEntryIds.value = selectedEntryIds.value.filter((id) => id !== entry.id)
  if (!selectedEntryIds.value.length) {
    selectedBatchKey.value = ''
  }
}

function mapEntriesToPrintData(entries) {
  const list = Array.isArray(entries) ? entries : [entries]
  const primary = list[0] || {}
  const methodLabel =
    paymentMethods.find((method) => method.id === primary.payment_method)?.name ||
    primary.payment_method ||
    '—'

  const applications = []
  const seenApplicationIds = new Set()
  for (const entry of list) {
    if (!entry.candidate_name && !entry.application_id) continue
    const key = entry.application_id != null ? String(entry.application_id) : entry.passport_no
    if (key && seenApplicationIds.has(key)) continue
    if (key) seenApplicationIds.add(key)
    applications.push({
      application_id: entry.application_id,
      candidate_name: entry.candidate_name,
      passport_no: entry.passport_no,
      application_status: entry.application_status,
    })
  }

  const demandLetter = primary.demand_letter
    ? {
        dl_no: primary.demand_letter,
        client_name: primary.client_name,
        country: primary.demand_letter_country,
      }
    : null

  const hasCandidateLines = list.some((entry) => entry.candidate_name || entry.application_id)

  const lineItems =
    list.length > 1 || hasCandidateLines
      ? list.map((entry) => {
          const isDirectExpense =
            entry.category_code === DIRECT_COST_CATEGORY_CODE ||
            String(entry.category_name || '').trim().toLowerCase() === 'direct expense'

          return {
            description: isDirectExpense
              ? formatDirectExpenseParticularDescription(entry.head_name)
              : entry.particular || `${entry.category_name} · ${entry.head_name}`,
            expenseHeadName: entry.head_name || '—',
            billNo: entry.voucher_no || entry.reference_no || '',
            amount: Number(entry.amount) || 0,
            linkedAccountType: getLinkedBillAccountTypeLabel(entry),
            linkedAccountName: getLinkedBillAccountName(entry),
            candidateName: isDirectExpense ? entry.candidate_name || '' : '',
            passportNo: isDirectExpense ? entry.passport_no || '' : '',
            applicationStatus: isDirectExpense ? entry.application_status || '' : '',
          }
        })
      : []

  const linkedAccounts = []
  const seenLinkedAccounts = new Set()
  for (const entry of list) {
    if (!hasLinkedBillAccount(entry)) continue
    const account = {
      expenseHeadName: entry.head_name || '',
      typeLabel: getLinkedBillAccountTypeLabel(entry),
      accountName: getLinkedBillAccountName(entry),
    }
    const key = [account.expenseHeadName, account.typeLabel, account.accountName]
      .map((part) => String(part || '').trim().toLowerCase())
      .join('|')
    if (seenLinkedAccounts.has(key)) continue
    seenLinkedAccounts.add(key)
    linkedAccounts.push(account)
  }

  const totalAmount = list.reduce((sum, entry) => sum + (Number(entry.amount) || 0), 0)

  const voucherNos = [
    ...new Set(list.map((entry) => String(entry.voucher_no || '').trim()).filter(Boolean)),
  ]

  return {
    requestNo: primary.request_no || '',
    billNo: voucherNos.length === 1 ? voucherNos[0] : '',
    preparedByName: primary.requested_by_name || '',
    billDate: primary.payment_date || '',
    paymentMethodLabel: methodLabel,
    referenceNo: primary.reference_no || '',
    categoryName: primary.category_name || '—',
    categoryCode: primary.category_code || '',
    expenseHeadName:
      list.length > 1 ? `${list.length} expense heads` : primary.head_name || '—',
    particular:
      list.length > 1
        ? list.map((entry) => entry.particular || entry.head_name).join(', ')
        : primary.particular || '',
    remarks: primary.remarks || '',
    totalAmount,
    unitAmountNote: '',
    demandLetter,
    applications: lineItems.some((item) => item.candidateName) ? [] : applications,
    linkedAccounts,
    jobName: primary.job_name || '',
    lineItems,
  }
}

async function printEntry(entry) {
  const requestNo = String(entry.request_no || '').trim()
  const batchRef = String(entry.batch_ref || '').trim()
  const voucherNo = String(entry.voucher_no || '').trim()

  const siblingEntries = paymentStore.payments.filter((item) => {
    if (item.status !== entry.status) return false
    if (requestNo) {
      return String(item.request_no || '').trim() === requestNo
    }
    if (batchRef) {
      return String(item.batch_ref || '').trim() === batchRef
    }
    if (!voucherNo) return Number(item.id) === Number(entry.id)
    return String(item.voucher_no || '').trim() === voucherNo
  })

  const entriesToPrint = siblingEntries.length ? siblingEntries : [entry]
  printEntryData.value = mapEntriesToPrintData(entriesToPrint)
  await nextTick()
  printPreviewRef.value?.printBill?.()
}

const resetFilters = () => {
  filters.search = ''
  filters.categoryId = ''
  filters.status = ''
  page.value = 1
  clearSelection()
}

const updatePagination = () => {
  const count = paginationTotal.value
  const lastPage = Math.max(1, Math.ceil(count / perPage.value))
  const to = Math.min(page.value * perPage.value, count)

  showing.value = to
  links.value = Array.from({ length: lastPage }, (_, index) => ({
    label: String(index + 1),
    active: page.value === index + 1,
    url: page.value === index + 1 ? null : '#',
  }))

  if (page.value > lastPage) {
    page.value = lastPage
  }
}

watch([filteredRows, page, perPage, () => paymentStore.payments.length], updatePagination, {
  immediate: true,
})

watch(
  groupedBatches,
  (batches) => {
    const next = new Set(expandedBatchKeys.value)
    let changed = false
    for (const batch of batches) {
      if (!next.has(batch.key)) {
        next.add(batch.key)
        changed = true
      }
    }
    if (changed) expandedBatchKeys.value = next
  },
  { immediate: true }
)

watch(
  () => props.statusScope,
  () => {
    filters.status = ''
    page.value = 1
    clearSelection()
  }
)

const setPage = (value) => {
  if (value && value !== page.value) {
    page.value = value
  }
}

const setPerPage = (value) => {
  perPage.value = Number(value)
  page.value = 1
}

onMounted(async () => {
  await Promise.all([categoryStore.fetchCategories(), paymentStore.fetchBillEntries()])
})
</script>
