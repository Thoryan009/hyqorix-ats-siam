<template>
  <div class="space-y-6">
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[1fr_320px]">
      <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <h3 class="mb-4 text-base font-semibold text-gray-800">Agent & Collection Information</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div class="space-y-2">
            <BaseLabel>Agent</BaseLabel>
            <BaseInput :model-value="agentLabel" disabled />
          </div>
          <div class="space-y-2">
            <BaseLabel for="collection_date">Collection Date</BaseLabel>
            <BaseInput id="collection_date" v-model="form.collection_date" type="date" />
          </div>
        </div>
      </div>

      <div class="rounded-lg border border-green-200 bg-green-50 p-4 shadow-sm">
        <h3 class="mb-4 text-base font-semibold text-green-800">Agent Balance Summary</h3>
        <div class="space-y-3 text-sm">
          <div class="flex justify-between">
            <span class="text-gray-600">Opening Balance</span>
            <span class="font-semibold">{{ formatCurrency(summary.opening_balance) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">Total Billed Amount</span>
            <span class="font-semibold">{{ formatCurrency(summary.total_billed) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">Total Paid Amount</span>
            <span class="font-semibold">{{ formatCurrency(summary.total_paid) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">Total Due Amount</span>
            <span class="font-semibold text-red-600">{{ formatCurrency(summary.total_due) }}</span>
          </div>
          <div class="border-t border-green-200 pt-3 flex justify-between">
            <span class="font-medium text-gray-700">Current Balance</span>
            <span
              class="text-lg font-bold text-green-700"
            >{{ formatCurrency(summary.current_balance) }}</span>
          </div>
        </div>
      </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
      <div class="border-b border-gray-200 px-4 py-3">
        <h3 class="text-base font-semibold text-gray-800">Select Bills to Collect</h3>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="border-b border-gray-200 px-3 py-2 text-left">
                <input
                  type="checkbox"
                  class="h-4 w-4 cursor-pointer accent-green-600"
                  :checked="allSelectableSelected"
                  @change="$emit('toggle-all')"
                />
              </th>
              <th class="border-b border-gray-200 px-3 py-2 text-left">Bill No</th>
              <th class="border-b border-gray-200 px-3 py-2 text-left">Bill Date</th>
              <th class="border-b border-gray-200 px-3 py-2 text-left">Job Title / Job Code</th>
              <th class="border-b border-gray-200 px-3 py-2 text-center">Candidates</th>
              <th class="border-b border-gray-200 px-3 py-2 text-right">Billed Amount</th>
              <th class="border-b border-gray-200 px-3 py-2 text-right">Paid Amount</th>
              <th class="border-b border-gray-200 px-3 py-2 text-right">Due Amount</th>
              <th class="border-b border-gray-200 px-3 py-2 text-right">Collect Amount</th>
              <th class="border-b border-gray-200 px-3 py-2 text-center">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!billRows.length">
              <td
                colspan="10"
                class="px-3 py-6 text-center text-gray-500"
              >No pending bills found for this agent.</td>
            </tr>
            <tr
              v-for="bill in billRows"
              :key="bill.id"
              class="border-b border-gray-100 hover:bg-gray-50"
            >
              <td class="px-3 py-2">
                <input
                  type="checkbox"
                  class="h-4 w-4 cursor-pointer accent-green-600"
                  :checked="selectedBillIds.includes(bill.id)"
                  :disabled="bill.due_amount <= 0"
                  @change="$emit('toggle-bill', bill.id)"
                />
              </td>
              <td class="px-3 py-2 font-medium">{{ bill.bill_no }}</td>
              <td class="px-3 py-2">{{ formatDisplayDate(bill.bill_date) }}</td>
              <td class="px-3 py-2">
                <div>{{ bill.job_title }}</div>
                <div class="text-xs text-gray-500">{{ bill.job_code }}</div>
              </td>
              <td class="px-3 py-2 text-center">{{ bill.candidate_count }}</td>
              <td class="px-3 py-2 text-right">{{ formatCurrency(bill.total_amount) }}</td>
              <td class="px-3 py-2 text-right">{{ formatCurrency(bill.paid_amount) }}</td>
              <td
                class="px-3 py-2 text-right font-semibold text-red-600"
              >{{ formatCurrency(bill.due_amount) }}</td>
              <td class="px-3 py-2 text-right">
                <BaseInput
                  v-model="collectAmounts[bill.id]"
                  type="number"
                  min="0"
                  :max="bill.due_amount"
                  step="0.01"
                  class="w-28 text-right"
                  :disabled="bill.due_amount <= 0 || !selectedBillIds.includes(bill.id)"
                />
              </td>
              <td class="px-3 py-2 text-center">
                <span
                  class="rounded-full px-2 py-1 text-xs font-semibold"
                  :class="statusClass(bill.status)"
                >{{ bill.status }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div
        class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 bg-gray-50 px-4 py-3 text-sm"
      >
        <p>
          Total Selected Bills:
          <span class="font-bold">{{ selectedBillIds.length }}</span>
        </p>
        <p>
          Total Collect Amount (BDT):
          <span
            class="font-bold text-green-700"
          >{{ formatCurrency(totalCollectAmount) }}</span>
        </p>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
      <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <h3 class="mb-4 text-base font-semibold text-gray-800">Collection Details</h3>
        <div class="space-y-4">
          <div class="space-y-2">
            <BaseLabel>Payment Type</BaseLabel>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="method in paymentMethods"
                :key="method.id"
                type="button"
                class="rounded-lg px-4 py-2 text-sm font-medium transition"
                :class="
                  form.payment_method === method.id
                    ? 'bg-green-600 text-white'
                    : 'bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50'
                "
                @click="form.payment_method = method.id"
              >
                {{ method.label }}
              </button>
            </div>
          </div>

          <div class="space-y-2">
            <BaseLabel>Total Collect Amount (BDT)</BaseLabel>
            <BaseInput :model-value="formatCurrency(totalCollectAmount)" disabled />
          </div>
          <div class="space-y-2">
            <BaseLabel>Amount in Words</BaseLabel>
            <BaseInput :model-value="amountInWords" disabled />
          </div>
          <div v-if="isCashPayment" class="space-y-2">
            <BaseLabel for="received_amount">Received Amount (BDT)</BaseLabel>
            <BaseInput
              id="received_amount"
              v-model="form.received_amount"
              type="number"
              min="0"
              step="0.01"
              placeholder="Enter received amount"
            />
          </div>
          <div
            v-else
            class="rounded-lg border border-yellow-200 bg-yellow-50 px-3 py-2 text-sm text-yellow-800"
          >
            <i class="fa fa-info-circle mr-1"></i>
            Total collect amount will be deducted from the agent's current balance
            ({{ formatCurrency(summary.current_balance) }}).
          </div>
        </div>
      </div>

      <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <h3 class="mb-4 text-base font-semibold text-gray-800">Payment Information</h3>
        <div class="space-y-4">
          <div v-if="isCashPayment" class="space-y-2">
            <BaseLabel for="account_id">Account</BaseLabel>
            <BaseSelect
              id="account_id"
              v-model="form.account_id"
              :options="accountOptions"
              placeholder="Select account"
            />
          </div>

          <div class="space-y-2">
            <BaseLabel for="notes">Notes (Optional)</BaseLabel>
            <textarea
              id="notes"
              v-model="form.notes"
              rows="4"
              class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary"
              placeholder="Additional payment notes"
            ></textarea>
          </div>
        </div>
      </div>
    </div>

    <div class="flex flex-wrap gap-3">
      <BaseButton
        :className="'bg-green-600 text-white hover:bg-green-700'"
        :disabled="submitLoading"
        @click="$emit('save')"
      >
        <i class="fa fa-save mr-1"></i>
        {{ submitLoading ? 'Saving...' : 'Save Collection' }}
      </BaseButton>
      <BaseButton
        :className="'border border-gray-300 bg-white text-gray-800 hover:bg-gray-50'"
        :disabled="submitLoading"
        @click="$emit('save-print')"
      >
        <i class="fa fa-print mr-1"></i> Save & Print
      </BaseButton>
      <router-link :to="backUrl">
        <BaseButton
          :className="'border border-gray-300 bg-white text-gray-800 hover:bg-gray-50'"
        >Cancel</BaseButton>
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { computed, watch } from 'vue'
import { useAccountStore } from '@/finance/store/accountStore'
import { formatCurrency, formatDisplayDate, numberToWords } from '@/finance/utils/billUtils'

defineEmits(['save', 'save-print', 'toggle-bill', 'toggle-all'])

const accountStore = useAccountStore()

const props = defineProps({
  agentLabel: { type: String, default: '' },
  summary: { type: Object, required: true },
  billRows: { type: Array, default: () => [] },
  selectedBillIds: { type: Array, default: () => [] },
  collectAmounts: { type: Object, required: true },
  form: { type: Object, required: true },
  paymentMethods: { type: Array, default: () => [] },
  submitLoading: { type: Boolean, default: false },
  backUrl: { type: String, default: '/finance/accounts?tab=agent-accounts' },
})

const isCashPayment = computed(() => props.form.payment_method === 'cash')

const totalCollectAmount = computed(() => {
  return props.selectedBillIds.reduce((sum, billId) => {
    return sum + Number(props.collectAmounts[billId] || 0)
  }, 0)
})

watch(totalCollectAmount, (amount) => {
  if (props.form.payment_method === 'cash') {
    props.form.received_amount = amount > 0 ? amount : ''
  }
})

watch(
  () => props.form.payment_method,
  (method) => {
    if (method === 'balance') {
      props.form.received_amount = ''
      props.form.account_id = ''
    } else if (totalCollectAmount.value > 0) {
      props.form.received_amount = totalCollectAmount.value
    }
  }
)

const amountInWords = computed(() => numberToWords(totalCollectAmount.value))

const accountOptions = computed(() =>
  accountStore.accounts
    .filter((account) => account.status === 'Active')
    .map((account) => ({
      id: account.id,
      name: `${account.account_name} - ${account.account_label}`,
    })),
)

const selectableBills = computed(() => props.billRows.filter((bill) => bill.due_amount > 0))

const allSelectableSelected = computed(() => {
  if (!selectableBills.value.length) return false
  return selectableBills.value.every((bill) => props.selectedBillIds.includes(bill.id))
})

function statusClass(status) {
  if (status === 'Paid') return 'bg-green-100 text-green-700'
  if (status === 'Partial') return 'bg-orange-100 text-orange-700'
  return 'bg-red-100 text-red-700'
}
</script>
