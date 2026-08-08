<template>
  <SectionHeader>
    <PageHeader :className="'mb-3'">
      <div>
        <button
          type="button"
          class="mb-1 inline-flex cursor-pointer items-center gap-1.5 text-sm font-medium text-slate-500 transition hover:text-slate-800"
          @click="goBack"
        >
          <i class="fa fa-arrow-left text-xs"></i>
          Back to Bills Receivable
        </button>
        <PageTitle>Receive Bill Receivable</PageTitle>
      </div>
      <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
        Due
      </span>
    </PageHeader>

    <div
      v-if="pageLoading"
      class="rounded-xl border border-slate-200 bg-white p-10 text-center text-sm text-slate-500"
    >
      Loading receivable details...
    </div>

    <div
      v-else-if="!entry"
      class="rounded-xl border border-red-100 bg-red-50 p-8 text-center text-sm text-red-700"
    >
      Bill receivable was not found or is already settled.
      <div class="mt-4">
        <BaseButton
          type="button"
          :className="'cursor-pointer rounded-lg border border-slate-300 bg-white px-4 py-2 text-slate-700 hover:bg-slate-50'"
          @click="goBack"
        >
          Go Back
        </BaseButton>
      </div>
    </div>

    <BaseForm v-else :onSubmit="handleReceive" class-name="!space-y-0">
      <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
        <aside class="space-y-3 xl:col-span-5">
          <section class="overflow-hidden rounded-xl border border-emerald-100 bg-white shadow-sm">
            <div
              class="bg-gradient-to-br from-[#0d5c4d] via-[#0f766e] to-[#134e4a] px-4 py-4 text-white"
            >
              <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-100/80">
                Receivable Remaining
              </p>
              <p class="mt-0.5 text-2xl font-bold tabular-nums tracking-tight">
                {{ formatCurrency(remainingAmount) }}
              </p>
            </div>

            <dl class="divide-y divide-slate-100 px-4 text-sm">
              <div class="flex justify-between gap-3 py-3">
                <dt class="text-slate-500">{{ isSimplePlIncome ? 'Income Head' : 'Candidate' }}</dt>
                <dd class="text-right font-medium text-slate-900">
                  {{ entry.candidate_name || entry.income_head_name || '—' }}
                  <p v-if="!isSimplePlIncome" class="text-xs font-normal text-slate-500">
                    {{ entry.passport_no || '' }}
                  </p>
                  <p v-else class="text-xs font-normal text-slate-500">
                    {{ entry.income_category_name || entry.particular || '' }}
                  </p>
                </dd>
              </div>
              <div class="flex justify-between gap-3 py-3">
                <dt class="text-slate-500">Payer</dt>
                <dd class="text-right font-medium text-slate-900">
                  {{ formatPayerTypeLabel(entry.payer_type) }}
                  <p class="text-xs font-normal text-slate-500">
                    {{ entry.party_account_label || '—' }}
                  </p>
                </dd>
              </div>
              <div v-if="!isSimplePlIncome" class="flex justify-between gap-3 py-3">
                <dt class="text-slate-500">Job</dt>
                <dd class="text-right font-medium text-slate-900">
                  {{ entry.job_title || '—' }}
                  <p class="text-xs font-normal text-slate-500">{{ entry.job_code || '' }}</p>
                </dd>
              </div>
              <div class="flex justify-between gap-3 py-3">
                <dt class="text-slate-500">Sale Price</dt>
                <dd class="font-medium tabular-nums text-slate-900">
                  {{ formatCurrency(entry.sale_price) }}
                </dd>
              </div>
              <div class="flex justify-between gap-3 py-3">
                <dt class="text-slate-500">Already Received</dt>
                <dd class="font-medium tabular-nums text-emerald-700">
                  {{ formatCurrency(entry.collected_amount) }}
                </dd>
              </div>
              <div class="flex justify-between gap-3 py-3">
                <dt class="text-slate-500">Due Date</dt>
                <dd class="font-medium text-slate-900">{{ entry.collection_date || '—' }}</dd>
              </div>
              <div class="flex justify-between gap-3 py-3">
                <dt class="text-slate-500">Voucher</dt>
                <dd class="font-medium text-slate-900">
                  {{ entry.voucher_no || entry.entry_no || '—' }}
                </dd>
              </div>
            </dl>
          </section>
        </aside>

        <section class="xl:col-span-7">
          <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <h3 class="mb-3 text-base font-semibold text-slate-900">Receive Payment</h3>

            <div class="mb-3 grid grid-cols-1 gap-2 rounded-lg border border-slate-200 bg-slate-50 p-3 sm:grid-cols-3">
              <div>
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                  Bill Total
                </p>
                <p class="mt-0.5 text-sm font-bold tabular-nums text-slate-900">
                  {{ formatCurrency(entry.sale_price) }}
                </p>
              </div>
              <div>
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                  Already Received
                </p>
                <p class="mt-0.5 text-sm font-bold tabular-nums text-emerald-700">
                  {{ formatCurrency(entry.collected_amount) }}
                </p>
              </div>
              <div>
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                  Remaining
                </p>
                <p class="mt-0.5 text-sm font-bold tabular-nums text-amber-700">
                  {{ formatCurrency(remainingAmount) }}
                </p>
              </div>
            </div>

            <div class="mb-3 rounded-xl border-2 border-emerald-200 bg-emerald-50/70 p-3">
              <BaseLabel
                for="receive_amount"
                :className="'mb-1 block text-xs font-bold uppercase tracking-wide text-emerald-800'"
              >
                Receive Amount (৳)
              </BaseLabel>
              <BaseInput
                id="receive_amount"
                v-model="form.receive_amount"
                type="number"
                min="0"
                :max="remainingAmount"
                step="0.01"
                :required="true"
                :className="'w-full rounded-lg border-2 border-emerald-300 bg-white px-3 py-2.5 text-2xl font-bold tabular-nums text-emerald-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30'"
              />
              <p v-if="amountExceedsRemaining" class="mt-2 text-xs text-red-600">
                Receive amount cannot exceed remaining of {{ formatCurrency(remainingAmount) }}.
              </p>
            </div>

            <div class="grid grid-cols-1 gap-2.5 md:grid-cols-2">
              <div class="space-y-1">
                <BaseLabel for="receive_method">Receive Method</BaseLabel>
                <BaseSelect
                  id="receive_method"
                  v-model="form.payment_method"
                  :options="receiveMethodOptions"
                  placeholder="Select method"
                  :required="true"
                />
              </div>

              <div class="space-y-1">
                <BaseLabel for="receive_date">Receive Date</BaseLabel>
                <BaseInput id="receive_date" v-model="form.entry_date" type="date" :required="true" />
              </div>

              <div v-if="isExpenseLinkMethod" class="space-y-1 md:col-span-2">
                <BaseLabel>Linked Expense Head</BaseLabel>
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5 text-sm text-amber-900">
                  <p class="font-semibold">
                    {{ linkedExpenseHead?.name || 'No linked expense head' }}
                  </p>
                  <p class="mt-0.5 text-xs text-amber-700">
                    Settles without cash/bank: DR on this expense head ledger, CR on the bill
                    ledger.
                  </p>
                </div>
              </div>

              <div v-else class="space-y-1 md:col-span-2">
                <BaseLabel for="receive_main_account">Receive In Main Account</BaseLabel>
                <BaseSelect
                  id="receive_main_account"
                  v-model="form.main_account_id"
                  :options="mainAccountOptions"
                  placeholder="Select main account"
                  :required="true"
                />
              </div>

              <div class="space-y-1 md:col-span-2">
                <BaseLabel for="receive_particular">Particular</BaseLabel>
                <BaseInput
                  id="receive_particular"
                  v-model="form.particular"
                  placeholder="Payment collection description"
                />
              </div>

              <div class="space-y-1">
                <BaseLabel for="receive_reference">Reference No (Optional)</BaseLabel>
                <BaseInput
                  id="receive_reference"
                  v-model="form.reference_no"
                  placeholder="Reference / voucher no"
                />
              </div>

              <div class="space-y-1 md:col-span-2">
                <BaseLabel for="receive_remarks">Remarks (Optional)</BaseLabel>
                <textarea
                  id="receive_remarks"
                  v-model="form.remarks"
                  rows="3"
                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30"
                  placeholder="Additional notes"
                ></textarea>
              </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-2 border-t border-slate-100 pt-4">
              <BaseButton
                type="submit"
                :className="'rounded-lg bg-emerald-600 px-4 py-2.5 text-white shadow-sm hover:bg-emerald-700'"
                :disabled="submitLoading || amountExceedsRemaining"
                v-can="'receive_payment.create'"
              >
                <i class="fa fa-save mr-1"></i>
                {{
                  submitLoading
                    ? 'Receiving...'
                    : isExpenseLinkMethod
                      ? 'Settle via Expense Link'
                      : 'Receive Payment'
                }}
              </BaseButton>
              <BaseButton
                type="button"
                :className="'rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-700 shadow-sm hover:bg-slate-50'"
                :disabled="submitLoading"
                @click="goBack"
              >
                Cancel
              </BaseButton>
            </div>
          </div>
        </section>
      </div>
    </BaseForm>
  </SectionHeader>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Swal from 'sweetalert2'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import PageTitle from '@/shared/components/ui/PageTitle.vue'
import BaseForm from '@/shared/components/base/BaseForm.vue'
import BaseButton from '@/shared/components/base/BaseButton.vue'
import BaseInput from '@/shared/components/base/BaseInput.vue'
import BaseLabel from '@/shared/components/base/BaseLabel.vue'
import BaseSelect from '@/shared/components/base/BaseSelect.vue'
import { useSaleEntryStore } from '@/finance/store/saleEntryStore'
import { useIncomeCollectionStore } from '@/finance/store/incomeCollectionStore'
import { useAccountStore } from '@/finance/store/accountStore'
import { usePartyAccountsStore } from '@/finance/store/partyAccountsStore'
import { useExpenseHeadStore } from '@/finance/store/expenseHeadStore'
import { formatCurrency } from '@/finance/utils/billUtils'
import {
  formatPayerTypeLabel,
  getReceivableRemainingAmount,
  isSimplePlIncomeReceivable,
} from '@/finance/utils/receivableBillUtils'

const route = useRoute()
const router = useRouter()
const saleEntryStore = useSaleEntryStore()
const incomeCollectionStore = useIncomeCollectionStore()
const accountStore = useAccountStore()
const partyAccountsStore = usePartyAccountsStore()
const expenseHeadStore = useExpenseHeadStore()

const pageLoading = ref(true)
const submitLoading = ref(false)
const entry = ref(null)
const originalParticular = ref('')

const today = new Date().toISOString().slice(0, 10)

const form = reactive({
  receive_amount: '',
  payment_method: 'cash',
  entry_date: today,
  main_account_id: '',
  particular: '',
  reference_no: '',
  remarks: '',
})

const linkedExpenseHead = computed(() => expenseHeadStore.getBillsReceivableLinkedHead())

const isExpenseLinkMethod = computed(
  () => String(form.payment_method || '').toLowerCase() === 'expense_link'
)

const receiveMethodOptions = computed(() => {
  const options = [
    { id: 'cash', name: 'Cash' },
    { id: 'bank', name: 'Bank' },
  ]

  if (linkedExpenseHead.value) {
    options.push({
      id: 'expense_link',
      name: `Expense Link (${linkedExpenseHead.value.name})`,
    })
  }

  return options
})

const remainingAmount = computed(() => getReceivableRemainingAmount(entry.value || {}))

const isSimplePlIncome = computed(() => isSimplePlIncomeReceivable(entry.value || {}))

const receiveAmountNumber = computed(() => Math.max(0, Number(form.receive_amount) || 0))

const amountExceedsRemaining = computed(
  () => receiveAmountNumber.value > remainingAmount.value + 0.0001
)

const mainAccountOptions = computed(() => {
  const method = String(form.payment_method || '').toLowerCase()
  if (method === 'expense_link') return []

  const accountType = method === 'bank' ? 'Bank' : 'Cash'

  return accountStore
    .getActiveAccounts()
    .filter((account) => account.status === 'Active' && account.account_type === accountType)
    .map((account) => ({
      id: account.id,
      name: `${account.account_type} — ${account.account_name} (${account.account_label})`,
    }))
})

function ensureDefaultMainAccount() {
  if (isExpenseLinkMethod.value) {
    form.main_account_id = ''
    return
  }

  const options = mainAccountOptions.value
  if (!options.length) {
    form.main_account_id = ''
    return
  }

  if (!options.some((item) => Number(item.id) === Number(form.main_account_id))) {
    form.main_account_id = options[0].id
  }
}

watch(
  () => form.payment_method,
  (method, previousMethod) => {
    ensureDefaultMainAccount()

    if (String(method || '').toLowerCase() === 'expense_link') {
      const linkedName = linkedExpenseHead.value?.name || 'Expense Link'
      const subject = isSimplePlIncome.value
        ? entry.value?.income_head_name || 'PL income'
        : entry.value?.candidate_name || 'candidate'
      form.particular = `Receivable settled via Expense Link (${linkedName}) — ${subject}`
      return
    }

    if (String(previousMethod || '').toLowerCase() === 'expense_link') {
      form.particular = originalParticular.value || form.particular
    }
  }
)

watch(mainAccountOptions, () => {
  ensureDefaultMainAccount()
})

watch(receiveMethodOptions, (options) => {
  if (!options.some((item) => item.id === form.payment_method)) {
    form.payment_method = 'cash'
  }
})

function goBack() {
  router.push({
    path: '/finance/payment-received',
    query: { tab: 'sale_entry', receive_type: 'bills_receivable' },
  })
}

async function loadEntry() {
  pageLoading.value = true
  entry.value = null

  try {
    const applicationId = Number(route.params.id)
    entry.value = await saleEntryStore.getReceivableBill(applicationId)

    if (entry.value) {
      form.receive_amount = String(getReceivableRemainingAmount(entry.value))
      form.particular =
        entry.value.particular ||
        (isSimplePlIncomeReceivable(entry.value)
          ? `Receive due for ${entry.value.income_head_name || 'PL income'}`
          : `Receive due payment for ${entry.value.candidate_name || 'candidate'}`)
      originalParticular.value = form.particular
      form.reference_no = ''
      form.remarks = ''

      if (String(form.payment_method || '').toLowerCase() === 'expense_link') {
        const linkedName = linkedExpenseHead.value?.name || 'Expense Link'
        const subject = isSimplePlIncomeReceivable(entry.value)
          ? entry.value.income_head_name || 'PL income'
          : entry.value.candidate_name || 'candidate'
        form.particular = `Receivable settled via Expense Link (${linkedName}) — ${subject}`
      }
    }
  } catch {
    entry.value = null
  } finally {
    pageLoading.value = false
    ensureDefaultMainAccount()
  }
}

async function handleReceive() {
  if (!entry.value || submitLoading.value) return

  const amount = receiveAmountNumber.value
  if (amount <= 0) {
    await Swal.fire({
      icon: 'error',
      title: 'Invalid Amount',
      text: 'Please enter a valid receive amount.',
      confirmButtonColor: '#22C55E',
    })
    return
  }

  if (amountExceedsRemaining.value) {
    await Swal.fire({
      icon: 'error',
      title: 'Amount Too High',
      text: `Receive amount cannot exceed remaining of ${formatCurrency(remainingAmount.value)}.`,
      confirmButtonColor: '#22C55E',
    })
    return
  }

  if (!isExpenseLinkMethod.value && !form.main_account_id) {
    await Swal.fire({
      icon: 'error',
      title: 'Main Account Required',
      text: 'Please select a main account to receive payment.',
      confirmButtonColor: '#22C55E',
    })
    return
  }

  if (isExpenseLinkMethod.value && !linkedExpenseHead.value) {
    await Swal.fire({
      icon: 'error',
      title: 'Expense Link Required',
      text: 'No Operating Expense head is linked for bills receivable. Link one under Expense Setup first.',
      confirmButtonColor: '#22C55E',
    })
    return
  }

  const payerType = String(entry.value.payer_type || 'candidate').toLowerCase()
  let partyAccountId = Number(entry.value.party_account_id) || null

  if (!isSimplePlIncome.value && payerType === 'agent' && !partyAccountId) {
    await Swal.fire({
      icon: 'error',
      title: 'Agent Account Required',
      text: 'Party account is missing for this receivable. Please recreate the due bill.',
      confirmButtonColor: '#22C55E',
    })
    return
  }

  if (!isSimplePlIncome.value && payerType === 'candidate') {
    await partyAccountsStore.fetchAccounts('applicant')
    const applicantAccount =
      partyAccountsStore.getAccountByEntityId('applicant', entry.value.application_id) ||
      (partyAccountId
        ? partyAccountsStore.getAccounts('applicant').find((a) => Number(a.id) === partyAccountId)
        : null)

    if (!applicantAccount) {
      await Swal.fire({
        icon: 'error',
        title: 'Applicant Account Required',
        text: 'Please create a finance applicant account for this candidate before receiving payment.',
        confirmButtonColor: '#22C55E',
      })
      return
    }

    partyAccountId = applicantAccount.id
  }

  submitLoading.value = true

  try {
    const isPlIncome = entry.value.source === 'pl_income'
    let result

    if (isPlIncome && isSimplePlIncome.value) {
      result = await incomeCollectionStore.collectIncome({
        category_id: Number(entry.value.income_category_id),
        head_id: Number(entry.value.income_head_id),
        amount,
        collection_date: form.entry_date,
        payment_method: form.payment_method,
        particular: form.particular,
        reference_no: form.reference_no || undefined,
        remarks: form.remarks || undefined,
        receive_account_id: isExpenseLinkMethod.value
          ? undefined
          : Number(form.main_account_id),
        linked_account_category: entry.value.linked_account_category || undefined,
        linked_account_id: Number(entry.value.party_account_id) || undefined,
        linked_account_name: entry.value.party_account_label || undefined,
        settles_income_collection_id: Number(entry.value.collection_id),
      })
    } else if (isPlIncome) {
      result = await incomeCollectionStore.collectIncome({
        category_id: Number(entry.value.income_category_id),
        head_id: Number(entry.value.income_head_id),
        amount,
        collection_date: form.entry_date,
        payment_method: form.payment_method,
        particular: form.particular,
        reference_no: form.reference_no || undefined,
        remarks: form.remarks || undefined,
        receive_account_id: isExpenseLinkMethod.value
          ? undefined
          : Number(form.main_account_id),
        linked_account_category: 'client',
        linked_account_id: Number(entry.value.party_account_id) || undefined,
        linked_account_name: entry.value.party_account_label || undefined,
        linked_account_type: 'Client',
        job_id: entry.value.job_list_id,
        job_code: entry.value.job_code,
        job_title: entry.value.job_title,
        client_name: entry.value.client_name || entry.value.party_account_label,
        settles_income_collection_id: Number(entry.value.collection_id) || undefined,
        candidates: [
          {
            application_id: entry.value.application_id,
            candidate_name: entry.value.candidate_name,
            passport_no: entry.value.passport_no,
            sale_price: Number(entry.value.sale_price) || 0,
            amount,
          },
        ],
      })
    } else {
      result = await saleEntryStore.saveSaleEntry({
        payerType,
        agentAccountId: payerType === 'agent' ? partyAccountId : null,
        payerId: payerType === 'client' ? partyAccountId : null,
        jobId: entry.value.job_list_id,
        jobCode: entry.value.job_code,
        jobTitle: entry.value.job_title,
        clientName: entry.value.party_account_label || entry.value.candidate_name,
        entryDate: form.entry_date,
        paymentMethod: form.payment_method,
        mainAccountId: isExpenseLinkMethod.value ? null : form.main_account_id,
        referenceNo: form.reference_no,
        particular: form.particular,
        remarks: form.remarks,
        candidates: [
          {
            candidate_id: entry.value.application_id,
            passport_no: entry.value.passport_no,
            candidate_name: entry.value.candidate_name,
            amount,
            sale_price: Number(entry.value.sale_price) || 0,
            collected_amount: Number(entry.value.collected_amount) || 0,
            applicant_account_id: payerType === 'candidate' ? partyAccountId : null,
          },
        ],
      })
    }

    if (!result?.ok) {
      await Swal.fire({
        icon: 'error',
        title: 'Receive Failed',
        text: result?.message || 'Failed to receive payment.',
        confirmButtonColor: '#22C55E',
      })
      return
    }

    await Promise.all([
      saleEntryStore.fetchReceivableBills(true),
      incomeCollectionStore.fetchCollections({ force: true, page: 1, perPage: 100 }),
    ])

    const remainingAfter = Math.max(
      0,
      Math.round((remainingAmount.value - amount) * 100) / 100
    )

    await Swal.fire({
      icon: 'success',
      title: remainingAfter <= 0 ? 'Receivable Settled' : 'Payment Received',
      text:
        remainingAfter <= 0
          ? 'This bill receivable has been fully settled.'
          : `Partial payment recorded. Remaining: ${formatCurrency(remainingAfter)}.`,
      confirmButtonColor: '#22C55E',
    })

    if (remainingAfter <= 0) {
      goBack()
    } else {
      await loadEntry()
    }
  } finally {
    submitLoading.value = false
  }
}

onMounted(async () => {
  await Promise.all([
    accountStore.fetchActiveAccounts(true),
    partyAccountsStore.fetchAccounts('applicant'),
    partyAccountsStore.fetchAccounts('client'),
    expenseHeadStore.fetchHeads({ force: true, page: 1, perPage: 300 }),
  ])
  await loadEntry()
})
</script>
