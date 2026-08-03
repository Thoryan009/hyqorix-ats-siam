<template>
  <div class="bill-success print:hidden">
    <div class="bill-success__glow" aria-hidden="true"></div>

    <div class="relative mx-auto max-w-xl px-4 py-10 sm:py-14">
      <div
        class="overflow-hidden rounded-2xl border border-emerald-100 bg-white shadow-[0_20px_50px_-24px_rgba(15,92,77,0.45)]"
      >
        <div
          class="bg-gradient-to-br from-[#0d5c4d] via-[#0f766e] to-[#134e4a] px-6 py-10 text-center text-white sm:px-10"
        >
          <div class="bill-success__badge mx-auto mb-5">
            <i class="fa fa-check"></i>
          </div>
        
          <h2 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">Bill Submitted</h2>
          <p class="mx-auto mt-3 max-w-md text-sm leading-relaxed text-emerald-50/90">
            <template v-if="isBillsToPay">
              Nice work — your expense bill was sent directly to Bills To Pay.
            </template>
            <template v-else>
              Nice work — your expense bill was submitted for manager approval.
            </template>
            <span v-if="billCount > 1"> {{ billCount }} bill entries were created.</span>
          </p>
        </div>

        <div class="space-y-5 px-6 py-7 sm:px-10">
          <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="rounded-xl border border-slate-100 bg-slate-50/80 px-4 py-3 sm:col-span-2">
              <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Request No</p>
              <p class="mt-1 text-lg font-bold tracking-wide text-emerald-800">
                {{ requestNo || '—' }}
              </p>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50/80 px-4 py-3">
              <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Amount</p>
              <p class="mt-1 text-lg font-bold tabular-nums text-emerald-800">
                {{ formatCurrency(totalAmount) }}
              </p>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50/80 px-4 py-3">
              <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Bill Date</p>
              <p class="mt-1 text-lg font-bold text-slate-900">{{ billDateLabel }}</p>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50/80 px-4 py-3 sm:col-span-2">
              <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Particular</p>
              <p class="mt-1 text-sm font-semibold text-slate-900">
                {{ particular || expenseHeadName || '—' }}
              </p>
              <p class="mt-0.5 text-xs text-slate-500">
                {{ categoryName }}
                <span v-if="expenseHeadName && expenseHeadName !== '—'"> · {{ expenseHeadName }}</span>
              </p>
            </div>
          </div>

          <div class="flex flex-col gap-3 sm:flex-row sm:justify-center">
            <BaseButton
              type="button"
              :className="'inline-flex cursor-pointer items-center justify-center rounded-xl bg-[#0d5c4d] px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#0a4a3e]'"
              @click="emit('print')"
            >
              <i class="fa fa-print mr-2"></i>
              Print The Bill
            </BaseButton>
            <BaseButton
              type="button"
              :className="'inline-flex cursor-pointer items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-800 hover:bg-slate-50'"
              @click="emit('create-another')"
            >
              <i class="fa fa-plus mr-2"></i>
              Create Another Bill
            </BaseButton>
          </div>

          <p class="text-center text-xs text-slate-400">
            <template v-if="isBillsToPay">
              You can print this bill now, create another, or review it later under Bills To Pay.
            </template>
            <template v-else>
              You can print this bill now, create another, or review it later under Submitted Bills.
            </template>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { formatCurrency } from '@/finance/utils/billUtils'

const props = defineProps({
  billDate: { type: String, default: '' },
  requestNo: { type: String, default: '' },
  totalAmount: { type: Number, default: 0 },
  particular: { type: String, default: '' },
  categoryName: { type: String, default: '—' },
  expenseHeadName: { type: String, default: '—' },
  billCount: { type: Number, default: 1 },
  destination: { type: String, default: 'submitted_bills' },
})

const emit = defineEmits(['print', 'create-another'])

const isBillsToPay = computed(() => props.destination === 'bills_to_pay')

const billDateLabel = computed(() => {
  if (!props.billDate) return '—'
  const date = new Date(props.billDate)
  if (Number.isNaN(date.getTime())) return props.billDate
  return date.toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })
})
</script>

<style scoped>
.bill-success {
  position: relative;
  min-height: min(70vh, 640px);
}

.bill-success__glow {
  pointer-events: none;
  position: absolute;
  inset: 10% 15% auto;
  height: 220px;
  border-radius: 999px;
  background: radial-gradient(circle, rgba(16, 185, 129, 0.18), transparent 70%);
  filter: blur(8px);
}

.bill-success__badge {
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
  animation: bill-success-pop 0.55s cubic-bezier(0.22, 1, 0.36, 1);
}

.bill-success__badge i {
  font-size: 28px;
}

@keyframes bill-success-pop {
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
