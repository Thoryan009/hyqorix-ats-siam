<template>
  <BaseModal
    :isVisible="isVisible"
    :title="modalTitle"
    :className="'!mx-0 !max-h-[100dvh] !rounded-none !p-3 !overflow-hidden h-[100dvh] w-full sm:!mx-4 sm:!max-h-[92vh] sm:!rounded-lg sm:!p-6 sm:h-auto sm:w-[90vw] sm:max-w-[90vw]'"
    @close="closeModal"
  >
    <div
      class="flex h-[calc(100dvh-4.75rem)] flex-col pb-[env(safe-area-inset-bottom)] sm:h-auto sm:max-h-[calc(92vh-5.5rem)] sm:pb-0"
    >
      <div class="grid min-h-0 flex-1 grid-cols-1 gap-4 overflow-y-auto lg:grid-cols-12">
        <!-- Left: Receipt viewer -->
        <div class="flex min-h-[200px] flex-col sm:min-h-[280px] lg:col-span-5">
          <div
            class="relative flex flex-1 flex-col overflow-hidden rounded-xl border border-slate-200 bg-slate-950"
          >
            <div
              class="flex items-center justify-between gap-2 border-b border-white/10 px-3 py-2.5"
            >
              <p class="text-xs font-semibold uppercase tracking-wide text-white/70">
                Bill Receipt{{ receiptUrls.length > 1 ? 's' : '' }}
              </p>
              <p v-if="receiptUrls.length" class="text-xs font-medium text-white/60">
                {{ activeReceiptIndex + 1 }} / {{ receiptUrls.length }}
              </p>
            </div>

            <div v-if="receiptUrls.length" class="relative flex min-h-0 flex-1 items-center justify-center p-3">
              <a
                :href="activeReceiptUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="flex h-full max-h-[220px] w-full items-center justify-center sm:max-h-[420px]"
                title="Open full size"
              >
                <img
                  :src="activeReceiptUrl"
                  :alt="`Bill receipt ${activeReceiptIndex + 1}`"
                  class="max-h-[220px] max-w-full rounded-lg object-contain shadow-lg sm:max-h-[420px]"
                />
              </a>

              <template v-if="receiptUrls.length > 1">
                <button
                  type="button"
                  class="absolute left-2 top-1/2 z-10 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-white/20 bg-black/55 text-white shadow-md backdrop-blur-sm transition hover:bg-black/75"
                  title="Previous receipt"
                  @click.stop="showPreviousReceipt"
                >
                  <i class="fa fa-chevron-left text-sm"></i>
                </button>
                <button
                  type="button"
                  class="absolute right-2 top-1/2 z-10 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-white/20 bg-black/55 text-white shadow-md backdrop-blur-sm transition hover:bg-black/75"
                  title="Next receipt"
                  @click.stop="showNextReceipt"
                >
                  <i class="fa fa-chevron-right text-sm"></i>
                </button>
              </template>
            </div>

            <div
              v-else
              class="flex min-h-[200px] flex-1 flex-col items-center justify-center gap-2 px-4 text-center sm:min-h-[280px]"
            >
              <div
                class="flex h-14 w-14 items-center justify-center rounded-full bg-white/10 text-white/50"
              >
                <i class="fa fa-file-image-o text-xl"></i>
              </div>
              <p class="text-sm font-medium text-white/70">No bill receipt uploaded</p>
              <p class="text-xs text-white/40">Receipts appear here when attached to the bill</p>
            </div>

            <div
              v-if="receiptUrls.length > 1"
              class="flex items-center justify-center gap-1.5 border-t border-white/10 px-3 py-2.5"
            >
              <button
                v-for="(url, index) in receiptUrls"
                :key="`${url}-${index}`"
                type="button"
                class="h-2 w-2 rounded-full transition"
                :class="
                  index === activeReceiptIndex
                    ? 'bg-emerald-400'
                    : 'bg-white/30 hover:bg-white/50'
                "
                :title="`Receipt ${index + 1}`"
                @click="activeReceiptIndex = index"
              />
            </div>
          </div>
        </div>

        <!-- Right: Bill details -->
        <div class="flex min-h-0 flex-col lg:col-span-7">
          <div class="min-h-0 flex-1 space-y-3 pr-0.5 lg:overflow-y-auto">
            <div class="rounded-xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white px-4 py-3.5">
              <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700/80">
                {{ isBatchReview ? 'Batch Total' : 'Amount' }}
              </p>
              <p class="mt-1 text-xl font-bold tabular-nums text-emerald-800 sm:text-2xl">
                {{ formatCurrency(totalAmount) }}
              </p>
              <p v-if="isBatchReview" class="mt-1 text-xs font-medium text-emerald-700/80">
                {{ reviewEntries.length }} bills in batch {{ batchLabel }}
              </p>
            </div>

            <div
              v-if="isBatchReview"
              class="overflow-hidden rounded-xl border border-slate-200"
            >
              <div class="border-b border-slate-200 bg-slate-50 px-3.5 py-2">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                  Batch Bills
                </p>
              </div>
              <div class="max-h-48 overflow-y-auto">
                <div
                  v-for="item in reviewEntries"
                  :key="item.id"
                  class="flex items-start justify-between gap-3 border-b border-slate-100 px-3.5 py-2.5 last:border-b-0"
                >
                  <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-slate-900">
                      {{ item.voucher_no || item.reference_no || `Bill #${item.id}` }}
                    </p>
                    <p class="truncate text-xs text-slate-500">
                      {{ item.head_name || '—' }}
                      <template v-if="item.candidate_name"> · {{ item.candidate_name }}</template>
                    </p>
                  </div>
                  <p class="shrink-0 text-sm font-semibold tabular-nums text-slate-800">
                    {{ formatCurrency(item.amount || 0) }}
                  </p>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
              <div class="rounded-xl border border-slate-200 bg-slate-50/80 px-3.5 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                  {{ isBatchReview ? 'Batch / Request' : 'Bill No' }}
                </p>
                <p class="mt-1 text-sm font-bold text-slate-900">
                  {{
                    isBatchReview
                      ? batchLabel
                      : entry?.voucher_no || entry?.reference_no || '—'
                  }}
                </p>
              </div>
              <div class="rounded-xl border border-slate-200 bg-slate-50/80 px-3.5 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                  Bill Date
                </p>
                <p class="mt-1 text-sm font-bold text-slate-900">
                  {{ entry?.payment_date || '—' }}
                </p>
              </div>
              <div class="rounded-xl border border-slate-200 bg-slate-50/80 px-3.5 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                  Category
                </p>
                <p class="mt-1 text-sm font-semibold text-slate-900">
                  {{ entry?.category_name || '—' }}
                </p>
              </div>
              <div class="rounded-xl border border-slate-200 bg-slate-50/80 px-3.5 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                  Expense Head
                </p>
                <p class="mt-1 text-sm font-semibold text-slate-900">
                  {{
                    isBatchReview
                      ? `${reviewEntries.length} expense heads`
                      : entry?.head_name || '—'
                  }}
                </p>
              </div>
              <div
                v-if="!isBatchReview"
                class="rounded-xl border border-slate-200 bg-slate-50/80 px-3.5 py-3 sm:col-span-2"
              >
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                  Particular
                </p>
                <p class="mt-1 text-sm font-semibold leading-relaxed text-slate-900">
                  {{ entry?.particular || '—' }}
                </p>
              </div>
              <div class="rounded-xl border border-slate-200 bg-slate-50/80 px-3.5 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                  Payment Method
                </p>
                <p class="mt-1 text-sm font-semibold capitalize text-slate-900">
                  {{ entry?.payment_method || '—' }}
                </p>
              </div>
              <div class="rounded-xl border border-slate-200 bg-slate-50/80 px-3.5 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                  Requested By
                </p>
                <p class="mt-1 text-sm font-semibold text-slate-900">
                  {{ entry?.requested_by_name || '—' }}
                </p>
              </div>
              <div
                v-if="entry?.remarks"
                class="rounded-xl border border-slate-200 bg-slate-50/80 px-3.5 py-3 sm:col-span-2"
              >
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                  Remarks
                </p>
                <p class="mt-1 text-sm leading-relaxed text-slate-700">{{ entry.remarks }}</p>
              </div>
            </div>

            <div class="space-y-1.5">
              <BaseLabel for="manager_approval_remarks">Approval Remarks</BaseLabel>
              <BaseInput
                id="manager_approval_remarks"
                v-model="approvalRemarks"
                placeholder="Optional notes for accountant / audit"
                :disabled="readonly"
              />
            </div>
          </div>
        </div>
      </div>

      <div
        class="mt-4 flex flex-col-reverse gap-2 border-t border-slate-100 pt-3 sm:flex-row sm:flex-wrap sm:justify-end sm:pt-4"
      >
        <BaseButton
          type="button"
          :className="'w-full cursor-pointer rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-700 hover:bg-slate-50 sm:w-auto sm:py-2'"
          @click="closeModal"
        >
          Close
        </BaseButton>
        <template v-if="!readonly">
          <BaseButton
            type="button"
            v-can="'submitted_bills.approve'"
            :className="'w-full cursor-pointer rounded-lg bg-red-600 px-4 py-2.5 text-white hover:bg-red-700 sm:w-auto sm:py-2'"
            :disabled="!!loading || isBatchReview"
            :title="isBatchReview ? 'Reject bills one at a time' : 'Reject bill'"
            @click="handleReject"
          >
            {{ loading === 'reject' ? 'Rejecting...' : 'Reject' }}
          </BaseButton>
          <BaseButton
            type="button"
            v-can="'submitted_bills.approve'"
            :className="'w-full cursor-pointer rounded-lg bg-emerald-600 px-4 py-2.5 text-white hover:bg-emerald-700 sm:w-auto sm:py-2'"
            :disabled="!!loading"
            @click="handleApprove"
          >
            <template v-if="loading === 'approve'">Approving...</template>
            <template v-else-if="isBatchReview">
              Approve Batch ({{ reviewEntries.length }})
              <span class="hidden sm:inline"> → Bills To Pay</span>
            </template>
            <template v-else>
              Approve
              <span class="hidden sm:inline"> → Bills To Pay</span>
            </template>
          </BaseButton>
        </template>
      </div>
    </div>
  </BaseModal>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import Swal from 'sweetalert2'
import { useExpensePaymentStore } from '@/finance/store/expensePaymentStore'
import { useAuthQuery } from '@/modules/auth/queries/useAuthQuery'
import { formatCurrency, getBillBatchLabel } from '@/finance/utils/billUtils'

const props = defineProps({
  isVisible: { type: Boolean, default: false },
  entry: { type: Object, default: null },
  entries: { type: Array, default: () => [] },
  readonly: { type: Boolean, default: false },
})

const emit = defineEmits(['close', 'approved', 'rejected'])

const router = useRouter()
const paymentStore = useExpensePaymentStore()
const { data: authData } = useAuthQuery()

const approvalRemarks = ref('')
const loading = ref('')
const activeReceiptIndex = ref(0)

const reviewEntries = computed(() => {
  if (Array.isArray(props.entries) && props.entries.length) {
    return props.entries
  }
  return props.entry ? [props.entry] : []
})

const isBatchReview = computed(() => reviewEntries.value.length > 1)

const entry = computed(() => reviewEntries.value[0] || props.entry || null)

const batchLabel = computed(() => getBillBatchLabel(entry.value || {}))

const totalAmount = computed(() =>
  reviewEntries.value.reduce((sum, item) => sum + (Number(item.amount) || 0), 0)
)

const modalTitle = computed(() => {
  if (props.readonly) return 'Submitted Bill Details'
  if (isBatchReview.value) return 'Batch Manager Approval'
  return 'Manager Approval'
})

const receiptUrls = computed(() => {
  const urls = []
  const seen = new Set()
  for (const item of reviewEntries.value) {
    const list =
      Array.isArray(item.receipt_urls) && item.receipt_urls.length
        ? item.receipt_urls
        : item.receipt_url
          ? [item.receipt_url]
          : []
    for (const url of list.filter(Boolean)) {
      if (seen.has(url)) continue
      seen.add(url)
      urls.push(url)
    }
  }
  return urls
})

const activeReceiptUrl = computed(
  () => receiptUrls.value[activeReceiptIndex.value] || ''
)

watch(
  () => [props.isVisible, props.entry?.id, reviewEntries.value.map((item) => item.id).join(',')],
  () => {
    approvalRemarks.value = entry.value?.approval_remarks || ''
    loading.value = ''
    activeReceiptIndex.value = 0
  }
)

watch(receiptUrls, (urls) => {
  if (activeReceiptIndex.value >= urls.length) {
    activeReceiptIndex.value = Math.max(0, urls.length - 1)
  }
})

function showPreviousReceipt() {
  if (!receiptUrls.value.length) return
  activeReceiptIndex.value =
    (activeReceiptIndex.value - 1 + receiptUrls.value.length) % receiptUrls.value.length
}

function showNextReceipt() {
  if (!receiptUrls.value.length) return
  activeReceiptIndex.value = (activeReceiptIndex.value + 1) % receiptUrls.value.length
}

const managerName = () => authData.value?.data?.name?.trim() || 'Manager'

function closeModal() {
  emit('close')
}

async function handleApprove() {
  if (!reviewEntries.value.length) return
  loading.value = 'approve'

  if (isBatchReview.value) {
    const result = await paymentStore.managerApproveBillEntryBatch({
      ids: reviewEntries.value.map((item) => item.id),
      approval_remarks: approvalRemarks.value,
      approved_by: managerName(),
    })

    loading.value = ''

    if (!result.ok) {
      await Swal.fire({
        icon: 'error',
        title: 'Batch Approval Failed',
        text: result.message,
        confirmButtonColor: '#22C55E',
      })
      return
    }

    await Swal.fire({
      icon: 'success',
      title: 'Batch Approved',
      text: `${result.entries.length} bills moved to Bills To Pay.`,
      confirmButtonColor: '#22C55E',
    })

    emit('approved', result.entries)
    return
  }

  const result = await paymentStore.managerApproveBillEntry({
    id: entry.value.id,
    approval_remarks: approvalRemarks.value,
    approved_by: managerName(),
  })

  loading.value = ''

  if (!result.ok) {
    await Swal.fire({
      icon: 'error',
      title: 'Approval Failed',
      text: result.message,
      confirmButtonColor: '#22C55E',
    })
    return
  }

  await Swal.fire({
    icon: 'success',
    title: 'Manager Approved',
    text: 'Bill moved to Bills To Pay.',
    confirmButtonColor: '#22C55E',
  })

  emit('approved', result.entry)
}

async function handleReject() {
  if (!entry.value?.id || isBatchReview.value) return

  const confirm = await Swal.fire({
    icon: 'warning',
    title: 'Reject this bill?',
    text: 'Rejected bills will not go to Bills To Pay.',
    showCancelButton: true,
    confirmButtonColor: '#DC2626',
    cancelButtonColor: '#6B7280',
    confirmButtonText: 'Reject Bill',
  })

  if (!confirm.isConfirmed) return

  loading.value = 'reject'

  const result = await paymentStore.rejectBillEntry({
    id: entry.value.id,
    amount: entry.value.amount,
    particular: entry.value.particular,
    reference_no: entry.value.reference_no,
    remarks: entry.value.remarks,
    approval_remarks: approvalRemarks.value,
    approved_by: managerName(),
  })

  loading.value = ''

  if (!result.ok) {
    await Swal.fire({
      icon: 'error',
      title: 'Reject Failed',
      text: result.message,
      confirmButtonColor: '#22C55E',
    })
    return
  }

  await Swal.fire({
    icon: 'success',
    title: 'Bill Rejected',
    confirmButtonColor: '#22C55E',
  })

  emit('rejected', result.entry)
  router.push({ path: '/finance/rejected-bills' })
}
</script>
