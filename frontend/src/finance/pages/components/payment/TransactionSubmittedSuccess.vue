<template>
  <div class="txn-success print:hidden">
    <div class="txn-success__glow" aria-hidden="true"></div>

    <div class="relative mx-auto max-w-xl px-4 py-10 sm:py-14">
      <div
        class="overflow-hidden rounded-2xl border border-emerald-100 bg-white shadow-[0_20px_50px_-24px_rgba(15,92,77,0.45)]"
      >
        <div
          class="bg-gradient-to-br from-[#0d5c4d] via-[#0f766e] to-[#134e4a] px-6 py-10 text-center text-white sm:px-10"
        >
          <div class="txn-success__badge mx-auto mb-5">
            <i class="fa fa-check"></i>
          </div>

          <h2 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">Transfer Successful</h2>
          <p class="mx-auto mt-3 max-w-md text-sm leading-relaxed text-emerald-50/90">
            The transaction has been recorded. You can create another transfer or review it in
            Transactions.
          </p>
        </div>

        <div class="space-y-5 px-6 py-7 sm:px-10">
          <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="rounded-xl border border-slate-100 bg-slate-50/80 px-4 py-3 sm:col-span-2">
              <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                Voucher / Reference
              </p>
              <p class="mt-1 text-lg font-bold tracking-wide text-emerald-800">
                {{ voucherNo || referenceNo || '—' }}
              </p>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50/80 px-4 py-3">
              <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Amount</p>
              <p class="mt-1 text-lg font-bold tabular-nums text-emerald-800">
                {{ formatCurrency(amount) }}
              </p>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50/80 px-4 py-3">
              <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Date</p>
              <p class="mt-1 text-lg font-bold text-slate-900">{{ dateLabel }}</p>
            </div>
            <div v-if="directionLabel" class="rounded-xl border border-slate-100 bg-slate-50/80 px-4 py-3">
              <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                Cash Direction
              </p>
              <p class="mt-1 text-lg font-bold text-slate-900">{{ directionLabel }}</p>
            </div>
            <div
              v-if="extraAccountName"
              class="rounded-xl border border-slate-100 bg-slate-50/80 px-4 py-3"
              :class="{ 'sm:col-span-2': !directionLabel }"
            >
              <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                {{ extraAccountType || 'Linked Account' }}
              </p>
              <p class="mt-1 text-sm font-semibold text-slate-900">{{ extraAccountName }}</p>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50/80 px-4 py-3 sm:col-span-2">
              <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">From</p>
              <p class="mt-1 text-sm font-semibold text-slate-900">{{ fromAccountLabel || '—' }}</p>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50/80 px-4 py-3 sm:col-span-2">
              <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">To</p>
              <p class="mt-1 text-sm font-semibold text-slate-900">{{ toAccountLabel || '—' }}</p>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50/80 px-4 py-3 sm:col-span-2">
              <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                Particular
              </p>
              <p class="mt-1 text-sm font-semibold text-slate-900">{{ particular || '—' }}</p>
            </div>
          </div>

          <div class="flex flex-col gap-3 sm:flex-row sm:justify-center">
            <BaseButton
              type="button"
              :className="'inline-flex cursor-pointer items-center justify-center rounded-xl bg-[#0d5c4d] px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#0a4a3e]'"
              @click="emit('create-another')"
            >
              <i class="fa fa-plus mr-2"></i>
              Create Another Transaction
            </BaseButton>
            <BaseButton
              type="button"
              :className="'inline-flex cursor-pointer items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-800 hover:bg-slate-50'"
              @click="emit('view-transactions')"
            >
              <i class="fa fa-list mr-2"></i>
              View Transactions
            </BaseButton>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { formatCurrency } from '@/finance/utils/billUtils'

const props = defineProps({
  amount: { type: Number, default: 0 },
  date: { type: String, default: '' },
  particular: { type: String, default: '' },
  voucherNo: { type: String, default: '' },
  referenceNo: { type: String, default: '' },
  fromAccountLabel: { type: String, default: '' },
  toAccountLabel: { type: String, default: '' },
  directionLabel: { type: String, default: '' },
  extraAccountType: { type: String, default: '' },
  extraAccountName: { type: String, default: '' },
})

const emit = defineEmits(['create-another', 'view-transactions'])

const dateLabel = computed(() => {
  if (!props.date) return '—'
  const date = new Date(props.date)
  if (Number.isNaN(date.getTime())) return props.date
  return date.toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })
})
</script>

<style scoped>
.txn-success {
  position: relative;
  min-height: min(70vh, 640px);
}

.txn-success__glow {
  pointer-events: none;
  position: absolute;
  inset: 10% 15% auto;
  height: 220px;
  border-radius: 999px;
  background: radial-gradient(circle, rgba(16, 185, 129, 0.18), transparent 70%);
  filter: blur(8px);
}

.txn-success__badge {
  display: flex;
  width: 72px;
  height: 72px;
  align-items: center;
  justify-content: center;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.14);
  box-shadow:
    0 0 0 10px rgba(255, 255, 255, 0.06),
    0 12px 30px rgba(0, 0, 0, 0.18);
  animation: txn-success-pop 0.55s cubic-bezier(0.22, 1, 0.36, 1);
}

.txn-success__badge i {
  font-size: 28px;
}

@keyframes txn-success-pop {
  0% {
    transform: scale(0.55);
    opacity: 0;
  }
  70% {
    transform: scale(1.08);
    opacity: 1;
  }
  100% {
    transform: scale(1);
  }
}
</style>
