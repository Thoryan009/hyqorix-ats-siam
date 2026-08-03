<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <div class="mb-1 flex items-center gap-2 text-sm text-gray-500">
          <router-link to="/finance/accounts?tab=agent-accounts" class="hover:text-green-700">
            Agent Accounts
          </router-link>
          <span>/</span>
          <span class="text-gray-700">Agent P/R Panel</span>
        </div>
        <PageTitle>Agent P/R Panel</PageTitle>
        <p v-if="agent" class="mt-1 text-sm text-gray-600">
          {{ agent.agent_code }} - {{ agent.agent_name }}
        </p>
      </div>
    </PageHeader>

    <div v-if="!agent" class="rounded-lg bg-white p-6 text-center text-gray-500 shadow-sm">
      Agent not found.
      <router-link to="/finance/accounts?tab=agent-accounts" class="ml-2 text-green-700 hover:underline">
        Back to Agent Accounts
      </router-link>
    </div>

    <template v-else>
      <div class="mb-6 flex flex-wrap gap-2">
        <button
          v-for="type in paymentTypes"
          :key="type.id"
          type="button"
          class="rounded-lg px-4 py-2 text-sm font-medium transition"
          :class="
            activeType === type.id
              ? 'bg-green-600 text-white'
              : 'bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50'
          "
          @click="switchType(type.id)"
        >
          {{ type.label }}
        </button>
      </div>

      <BillCollectionPanel
        v-if="activeType === 'collect_bill'"
        :agent-label="agentLabel"
        :summary="balanceSummary"
        :bill-rows="billRows"
        :selected-bill-ids="selectedBillIds"
        :collect-amounts="collectAmounts"
        :form="collectionForm"
        :payment-methods="collectionPaymentMethods"
        :submit-loading="submitLoading"
        back-url="/finance/accounts?tab=agent-accounts"
        @toggle-bill="toggleBill"
        @toggle-all="toggleAllBills"
        @save="saveCollection(false)"
        @save-print="saveCollection(true)"
      />

      <div v-else class="max-w-xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-base font-semibold text-gray-800">{{ activeTypeLabel }}</h3>

        <BaseForm :onSubmit="handleOtherPayment">
          <div class="mb-4 space-y-2">
            <BaseLabel for="payment_date">Date</BaseLabel>
            <BaseInput
              id="payment_date"
              v-model="otherForm.date"
              type="date"
              :required="true"
            />
          </div>

          <div class="mb-4 space-y-2">
            <BaseLabel for="amount">Amount (BDT)</BaseLabel>
            <BaseInput
              id="amount"
              v-model="otherForm.amount"
              type="number"
              min="0"
              step="0.01"
              placeholder="Enter amount"
              :required="true"
            />
          </div>

          <div class="mb-4 space-y-2">
            <BaseLabel for="particular">Particular Name</BaseLabel>
            <BaseInput
              id="particular"
              v-model="otherForm.particular"
              placeholder="Eg: Cash deposit, Loan adjustment"
              :required="true"
            />
          </div>

          <div class="mb-4 space-y-2">
            <BaseLabel for="other_account_id">Account</BaseLabel>
            <BaseSelect
              id="other_account_id"
              v-model="otherForm.account_id"
              :options="accountOptions"
              placeholder="Select account"
            />
          </div>

          <div class="mb-4 space-y-2">
            <BaseLabel for="other_notes">Notes (Optional)</BaseLabel>
            <textarea
              id="other_notes"
              v-model="otherForm.notes"
              rows="3"
              class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary"
              placeholder="Additional payment notes"
            ></textarea>
          </div>

          <div
            v-if="activeType === 'loan'"
            class="mb-4 rounded-lg border border-yellow-200 bg-yellow-50 px-3 py-2 text-sm text-yellow-800"
          >
            Providing loan will deduct the amount from the agent balance.
          </div>

          <div class="flex gap-3">
            <BaseButton type="submit" :className="'bg-green-600 text-white hover:bg-green-700'" :disabled="submitLoading">
              {{ submitLoading ? 'Processing...' : submitLabel }}
            </BaseButton>
            <router-link to="/finance/accounts?tab=agent-accounts">
              <BaseButton :className="'border border-gray-300 bg-white text-gray-800 hover:bg-gray-50'">
                Cancel
              </BaseButton>
            </router-link>
          </div>
        </BaseForm>
      </div>

      <!-- <div class="mt-8 rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-4 py-3">
          <h3 class="text-base font-semibold text-gray-800">Transaction History</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full border-collapse text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="border-b border-gray-200 px-3 py-2 text-left">Date</th>
                <th class="border-b border-gray-200 px-3 py-2 text-left">Type</th>
                <th class="border-b border-gray-200 px-3 py-2 text-left">Particular</th>
                <th class="border-b border-gray-200 px-3 py-2 text-left">Bill No</th>
                <th class="border-b border-gray-200 px-3 py-2 text-right">Amount</th>
                <th class="border-b border-gray-200 px-3 py-2 text-right">Balance After</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="!transactionRows.length">
                <td colspan="6" class="px-3 py-4 text-center text-gray-500">No transactions yet</td>
              </tr>
              <tr
                v-for="item in transactionRows"
                :key="item.id"
                class="border-b border-gray-100 hover:bg-gray-50"
              >
                <td class="px-3 py-2 whitespace-nowrap">{{ item.created_at }}</td>
                <td class="px-3 py-2">
                  <span class="rounded-full px-2 py-1 text-xs font-semibold" :class="typeBadgeClass(item.type)">
                    {{ typeLabel(item.type) }}
                  </span>
                </td>
                <td class="px-3 py-2">{{ item.particular }}</td>
                <td class="px-3 py-2">{{ item.bill_no || '-' }}</td>
                <td
                  class="px-3 py-2 text-right font-semibold"
                  :class="isCredit(item.type) ? 'text-green-700' : 'text-red-600'"
                >
                  {{ isCredit(item.type) ? '+' : '-' }}{{ formatCurrency(item.amount) }}
                </td>
                <td class="px-3 py-2 text-right font-medium">
                  {{ formatCurrency(item.balance_after) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div> -->
    </template>
  </SectionHeader>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import BaseForm from '@/shared/components/base/BaseForm.vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import BillCollectionPanel from './components/agentPaymentParts/BillCollectionPanel.vue'
import { useAgentAccountStore } from '@/finance/store/agentAccountStore'
import { useAccountStore } from '@/finance/store/accountStore'
import { useAgentBillStore } from '@/finance/store/agentBillStore'
import { formatCurrency } from '@/finance/utils/billUtils'
import Swal from 'sweetalert2'

const route = useRoute()
const accountStore = useAgentAccountStore()
const paymentAccountStore = useAccountStore()
const billStore = useAgentBillStore()

const activeType = ref('collect_bill')
const submitLoading = ref(false)
const selectedBillIds = ref([])
const collectAmounts = reactive({})

const today = new Date().toISOString().slice(0, 10)

function createOtherPaymentForm() {
  return {
    amount: '',
    particular: '',
    account_id: '',
    notes: '',
    date: new Date().toISOString().slice(0, 10),
  }
}

const collectionForm = ref({
  collection_date: today,
  account_id: '',
  reference_no: '',
  received_by: '1',
  remarks: '',
  received_amount: '',
  particular: '',
  payment_method: 'cash',
  cash_received_by: '1',
  notes: '',
})

const collectionPaymentMethods = [
  { id: 'cash', label: 'Cash Payment' },
  { id: 'balance', label: 'Adjust from Balance' },
]

const otherForm = ref(createOtherPaymentForm())

const accountOptions = computed(() =>
  paymentAccountStore.accounts
    .filter((account) => account.status === 'Active')
    .map((account) => ({
      id: account.id,
      name: `${account.account_name} - ${account.account_label}`,
    }))
)

const paymentTypes = accountStore.paymentTypes

const agentId = computed(() => Number(route.params.agentId))

const agent = computed(() => accountStore.getAccount(agentId.value))

const agentLabel = computed(() => {
  if (!agent.value) return ''
  return `${agent.value.agent_code} - ${agent.value.agent_name}`
})

const balanceSummary = computed(() => {
  if (!agent.value?.bill_agent_id) {
    return {
      opening_balance: agent.value?.opening_balance || 0,
      total_billed: 0,
      total_paid: 0,
      total_due: 0,
      current_balance: agent.value?.balance || 0,
    }
  }

  return billStore.getAgentBalanceSummary(agent.value.bill_agent_id, agent.value)
})

const billRows = computed(() => {
  if (!agent.value?.bill_agent_id) return []

  return billStore.getPendingBillsByBillAgent(agent.value.bill_agent_id).map((bill) => ({
    ...bill,
    due_amount: billStore.getBillDueAmount(bill),
    status: billStore.getBillPaymentStatus(bill),
  }))
})

const transactionRows = computed(() => accountStore.getTransactions(agentId.value))

const activeTypeLabel = computed(() => {
  return paymentTypes.find((type) => type.id === activeType.value)?.label ?? 'Payment'
})

const submitLabel = computed(() => {
  const labels = {
    deposit: 'Deposit Balance',
    loan: 'Provide Loan',
  }
  return labels[activeType.value] ?? 'Submit'
})

watch(
  billRows,
  (rows) => {
    rows.forEach((bill) => {
      if (collectAmounts[bill.id] === undefined) {
        collectAmounts[bill.id] = bill.due_amount > 0 ? bill.due_amount : 0
      }
    })
  },
  { immediate: true },
)

function switchType(type) {
  activeType.value = type
  otherForm.value = createOtherPaymentForm()
}

function toggleBill(billId) {
  if (selectedBillIds.value.includes(billId)) {
    selectedBillIds.value = selectedBillIds.value.filter((id) => id !== billId)
  } else {
    selectedBillIds.value.push(billId)
    const bill = billRows.value.find((item) => item.id === billId)
    if (bill && !collectAmounts[billId]) {
      collectAmounts[billId] = bill.due_amount
    }
  }
}

function toggleAllBills() {
  const selectable = billRows.value.filter((bill) => bill.due_amount > 0)

  if (selectedBillIds.value.length === selectable.length) {
    selectedBillIds.value = []
    return
  }

  selectedBillIds.value = selectable.map((bill) => bill.id)
  selectable.forEach((bill) => {
    collectAmounts[bill.id] = bill.due_amount
  })
}

function getSelectedCollectionItems() {
  return selectedBillIds.value
    .map((billId) => {
      const bill = billRows.value.find((item) => item.id === billId)
      const amount = Number(collectAmounts[billId] || 0)
      return bill ? { bill, amount } : null
    })
    .filter(Boolean)
}

async function saveCollection(shouldPrint) {
  if (!agent.value) return

  const paymentMethod = collectionForm.value.payment_method
  const particular =
    collectionForm.value.particular.trim() ||
    (paymentMethod === 'balance'
      ? 'Bill collection adjusted from agent balance'
      : 'Bill collection via cash')

  const items = getSelectedCollectionItems().filter((item) => item.amount > 0)
  if (!items.length) {
    await Swal.fire({
      icon: 'warning',
      title: 'No Bills Selected',
      text: 'Please select at least one bill with a collect amount.',
      confirmButtonColor: '#22C55E',
    })
    return
  }

  for (const item of items) {
    if (item.amount > item.bill.due_amount) {
      await Swal.fire({
        icon: 'error',
        title: 'Invalid Collect Amount',
        text: `Collect amount for ${item.bill.bill_no} exceeds due amount.`,
        confirmButtonColor: '#22C55E',
      })
      return
    }
  }

  const totalAmount = items.reduce((sum, item) => sum + item.amount, 0)

  if (paymentMethod === 'balance' && agent.value.balance < totalAmount) {
    await Swal.fire({
      icon: 'error',
      title: 'Insufficient Balance',
      text: 'Agent balance is not enough to adjust this bill collection.',
      confirmButtonColor: '#22C55E',
    })
    return
  }

  if (paymentMethod === 'cash') {
    if (!collectionForm.value.account_id) {
      await Swal.fire({
        icon: 'warning',
        title: 'Account Required',
        text: 'Please select an account for cash payment.',
        confirmButtonColor: '#22C55E',
      })
      return
    }

    const receivedAmount = Number(collectionForm.value.received_amount || 0)
    if (receivedAmount > 0 && receivedAmount < totalAmount) {
      await Swal.fire({
        icon: 'error',
        title: 'Insufficient Received Amount',
        text: 'Received amount must be equal to or greater than total collect amount.',
        confirmButtonColor: '#22C55E',
      })
      return
    }
  }

  submitLoading.value = true

  const billItems = items.map((item) => {
    billStore.applyBillCollection(item.bill.id, item.amount)
    return {
      bill_no: item.bill.bill_no,
      amount: item.amount,
      job_code: item.bill.job_code,
      job_title: item.bill.job_title,
    }
  })

  accountStore.saveBillCollection(agent.value.id, {
    particular,
    totalAmount,
    billItems,
    paymentMethod,
    referenceNo: collectionForm.value.reference_no,
    accountId: paymentMethod === 'cash' ? collectionForm.value.account_id : null,
    collectionDate: collectionForm.value.collection_date,
    remarks: collectionForm.value.remarks,
  })

  submitLoading.value = false

  await Swal.fire({
    icon: 'success',
    title: 'Collection Saved',
    text: 'Bill collection has been saved successfully.',
    confirmButtonColor: '#22C55E',
  })

  selectedBillIds.value = []
  collectionForm.value.received_amount = ''
  collectionForm.value.particular = ''

  if (shouldPrint) {
    window.print()
  }
}

async function handleOtherPayment() {
  if (!agent.value) return

  const amount = Number(otherForm.value.amount)
  const particular = otherForm.value.particular.trim()

  if (!particular) {
    await Swal.fire({
      icon: 'warning',
      title: 'Particular Required',
      text: 'Please enter a particular name.',
      confirmButtonColor: '#22C55E',
    })
    return
  }

  if (!amount || amount <= 0) {
    await Swal.fire({
      icon: 'warning',
      title: 'Amount Required',
      text: 'Please enter a valid amount.',
      confirmButtonColor: '#22C55E',
    })
    return
  }

  if (!otherForm.value.date) {
    await Swal.fire({
      icon: 'warning',
      title: 'Date Required',
      text: 'Please select a date.',
      confirmButtonColor: '#22C55E',
    })
    return
  }

  if (!otherForm.value.account_id) {
    await Swal.fire({
      icon: 'warning',
      title: 'Account Required',
      text: 'Please select an account.',
      confirmButtonColor: '#22C55E',
    })
    return
  }

  const currentBalance = agent.value.balance

  if (activeType.value === 'loan' && currentBalance < amount) {
    await Swal.fire({
      icon: 'error',
      title: 'Insufficient Balance',
      text: 'Agent balance is not enough to provide this loan.',
      confirmButtonColor: '#22C55E',
    })
    return
  }

  submitLoading.value = true

  if (activeType.value === 'deposit') {
    accountStore.depositBalance(
      agent.value.id,
      amount,
      particular,
      otherForm.value.account_id,
      otherForm.value.date
    )
  } else if (activeType.value === 'loan') {
    accountStore.provideLoan(
      agent.value.id,
      amount,
      particular,
      otherForm.value.account_id,
      otherForm.value.date
    )
  }

  submitLoading.value = false

  await Swal.fire({
    icon: 'success',
    title: 'Success',
    text: `${submitLabel.value} completed successfully.`,
    confirmButtonColor: '#22C55E',
  })

  otherForm.value = createOtherPaymentForm()
}

function isCredit(type) {
  return type === 'deposit' || type === 'add_payment' || type === 'collect_bill'
}

function typeLabel(type) {
  const labels = {
    deposit: 'Deposit',
    loan: 'Loan',
    collect_bill: 'Collect Bill',
    add_payment: 'Payment',
  }
  return labels[type] ?? type
}

function typeBadgeClass(type) {
  if (isCredit(type)) return 'bg-green-100 text-green-700'
  return 'bg-red-100 text-red-700'
}
</script>

<style scoped>
@media print {
  :deep(.page-header),
  :deep(.mb-6),
  :deep(.mt-8) {
    display: none !important;
  }
}
</style>
