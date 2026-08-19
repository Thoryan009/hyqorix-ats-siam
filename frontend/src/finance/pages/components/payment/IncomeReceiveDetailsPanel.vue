<template>
  <div
    class="overflow-hidden rounded-xl border border-emerald-200/80 bg-white shadow-sm lg:sticky lg:top-4"
  >
    <div
      class="border-b border-emerald-200/70 bg-gradient-to-r from-emerald-600 to-emerald-700 px-4 py-3.5 text-white"
    >
      <div class="flex items-center gap-3">
        <span
          class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-white/15 ring-1 ring-white/25"
        >
          <i class="fa fa-money text-sm"></i>
        </span>
        <h3 class="text-sm font-semibold tracking-tight">Receive Details</h3>
      </div>
    </div>

    <div class="space-y-4 p-4">
      <div class="rounded-xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white px-4 py-3.5 shadow-sm">
        <BaseLabel
          class-name="!mb-1.5 !text-xs !font-medium !uppercase !tracking-wide !text-emerald-700/80"
        >
          {{ amountLabel }}
        </BaseLabel>

        <BaseInput
          v-if="amountEditable"
          :model-value="amountModel"
          type="number"
          min="0"
          step="0.01"
          placeholder="Enter receive amount"
          :required="true"
          :class-name="'w-full rounded-lg border-2 border-emerald-300 bg-white px-3 py-2.5 text-2xl font-bold tabular-nums text-emerald-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30'"
          @update:model-value="$emit('update:amount', $event)"
        />
        <p v-else class="text-2xl font-bold tabular-nums tracking-tight text-emerald-800">
          {{ formatCurrency(amountValue) }}
        </p>
      </div>

      <div class="space-y-2">
        <BaseLabel for="income_receive_method_panel">Receive Method</BaseLabel>
        <BaseSelect
          id="income_receive_method_panel"
          :model-value="paymentMethod"
          :options="receiveMethodOptions"
          placeholder="Select receive method"
          :required="true"
          @update:model-value="$emit('update:paymentMethod', $event)"
        />
      </div>

      <div v-if="requiresMainAccount" class="space-y-2">
        <BaseLabel for="income_receive_account_panel">Receive In Main Account</BaseLabel>
        <BaseSelect
          id="income_receive_account_panel"
          :model-value="receiveAccountId"
          :options="mainAccountOptions"
          placeholder="Select main account"
          :required="true"
          @update:model-value="$emit('update:receiveAccountId', $event)"
        />
      </div>

      <p
        v-else
        class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800"
      >
        Due receive method — no cash/bank account required. Amount is recorded as receivable.
      </p>

      <div class="space-y-2">
        <div class="flex items-center justify-between gap-2">
          <BaseLabel for="income_particular_panel">Particular</BaseLabel>
          <button
            type="button"
            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700"
            title="Reset particular"
            @click.stop="handleResetParticular"
          >
            <i class="fa fa-refresh"></i>
          </button>
        </div>
        <BaseInput
          :key="`income-particular-${particularInputKey}`"
          id="income_particular_panel"
          :model-value="particular"
          placeholder="Payment collection description"
          @update:model-value="$emit('update:particular', $event)"
        />
      </div>

      <div class="overflow-hidden rounded-xl border border-slate-200/90 bg-slate-50/50">
        <button
          type="button"
          class="flex w-full items-center justify-between px-3.5 py-2.5 text-left text-sm font-medium text-slate-700 transition hover:bg-slate-100/80"
          @click="$emit('toggleAdditionalInfo')"
        >
          <span class="inline-flex items-center gap-2">
            <i class="fa fa-ellipsis-h text-slate-400"></i>
            Additional Info
          </span>
          <i
            class="fa text-slate-400"
            :class="showAdditionalInfo ? 'fa-chevron-up' : 'fa-chevron-down'"
          ></i>
        </button>

        <div
          v-if="showAdditionalInfo"
          class="space-y-4 border-t border-slate-200 bg-white px-3.5 py-3.5"
        >
          <div class="space-y-2">
            <BaseLabel for="income_reference_no_panel">Reference No (Optional)</BaseLabel>
            <BaseInput
              id="income_reference_no_panel"
              :model-value="referenceNo"
              placeholder="Reference / voucher no"
              @update:model-value="$emit('update:referenceNo', $event)"
            />
          </div>

          <div class="space-y-2">
            <BaseLabel for="income_remarks_panel">Remarks (Optional)</BaseLabel>
            <textarea
              id="income_remarks_panel"
              :value="remarks"
              rows="3"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30"
              placeholder="Additional notes"
              @input="$emit('update:remarks', $event.target.value)"
            ></textarea>
          </div>
        </div>
      </div>

      <div class="flex flex-col gap-2.5 border-t border-slate-100 pt-4 sm:flex-row sm:flex-wrap">
        <BaseButton
          type="submit"
          :className="'flex-1 cursor-pointer rounded-lg bg-emerald-600 px-4 py-2.5 text-white shadow-sm hover:bg-emerald-700'"
          :disabled="submitLoading"
          v-can="'receive_payment.create'"
        >
          <i class="fa fa-save mr-1"></i>
          {{ submitLoading ? 'Collecting...' : submitLabel }}
        </BaseButton>
        <BaseButton
          type="button"
          :className="'rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-700 shadow-sm hover:bg-slate-50'"
          :disabled="submitLoading"
          v-can="'receive_payment.create'"
          @click="$emit('reset')"
        >
          Reset
        </BaseButton>
      </div>
    </div>
  </div>
</template>

<script setup>
import BaseButton from '@/shared/components/base/BaseButton.vue'
import BaseInput from '@/shared/components/base/BaseInput.vue'
import BaseLabel from '@/shared/components/base/BaseLabel.vue'
import BaseSelect from '@/shared/components/base/BaseSelect.vue'
import { formatCurrency } from '@/finance/utils/billUtils'

defineProps({
  amountLabel: { type: String, default: 'Total Receive Amount (BDT)' },
  amountValue: { type: Number, default: 0 },
  amountEditable: { type: Boolean, default: false },
  amountModel: { type: [String, Number], default: '' },
  paymentMethod: { type: String, default: 'cash' },
  receiveMethodOptions: { type: Array, default: () => [] },
  requiresMainAccount: { type: Boolean, default: true },
  mainAccountOptions: { type: Array, default: () => [] },
  receiveAccountId: { type: [String, Number], default: '' },
  particular: { type: String, default: '' },
  particularInputKey: { type: Number, default: 0 },
  referenceNo: { type: String, default: '' },
  remarks: { type: String, default: '' },
  showAdditionalInfo: { type: Boolean, default: false },
  submitLoading: { type: Boolean, default: false },
  submitLabel: { type: String, default: 'Collect Client Income' },
})

const emit = defineEmits([
  'update:amount',
  'update:paymentMethod',
  'update:receiveAccountId',
  'update:particular',
  'update:referenceNo',
  'update:remarks',
  'toggleAdditionalInfo',
  'reset-particular',
  'reset',
])

function handleResetParticular() {
  emit('reset-particular')
}
</script>
