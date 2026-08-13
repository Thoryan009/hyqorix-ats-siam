<template>
  <div>
    <BillSubmittedSuccess
      v-if="submittedBill"
      :bill-date="submittedBill.billDate"
      :request-no="submittedBill.requestNo"
      :total-amount="submittedBill.totalAmount"
      :particular="submittedBill.particular"
      :category-name="submittedBill.categoryName"
      :expense-head-name="submittedBill.expenseHeadName"
      :bill-count="submittedBill.billCount"
      :destination="submittedBill.destination"
      @print="printSubmittedBill"
      @create-another="createAnotherBill"
    />

    <BillGenerationPreview
      v-if="submittedBill"
      ref="submittedBillPreviewRef"
      print-only
      :request-no="submittedBill.requestNo"
      :bill-no="submittedBill.billNo"
      :prepared-by-name="submittedBill.preparedByName"
      :bill-date="submittedBill.billDate"
      :payment-method-label="submittedBill.paymentMethodLabel"
      :reference-no="submittedBill.referenceNo"
      :category-name="submittedBill.categoryName"
      :category-code="submittedBill.categoryCode"
      :expense-head-name="submittedBill.expenseHeadName"
      :particular="submittedBill.particular"
      :remarks="submittedBill.remarks"
      :total-amount="submittedBill.totalAmount"
      :unit-amount-note="submittedBill.unitAmountNote"
      :demand-letter="submittedBill.demandLetter"
      :applications="submittedBill.applications"
      :linked-accounts="submittedBill.linkedAccounts || []"
      :job-name="submittedBill.jobName"
      :line-items="submittedBill.lineItems || []"
    />

    <div v-show="!submittedBill" class="mx-auto max-w-4xl">
      <BaseForm :onSubmit="handleSubmit" class-name="!space-y-0">
      <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div
          class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-3 sm:px-5"
        >
          <div>
            <h3 class="text-sm font-semibold text-slate-900">Bills & Purchases Authorization</h3>
            <p class="text-xs text-slate-500">Enter bill or purchase authorization details and submit when ready</p>
          </div>
          <span
            v-if="!isAssetPurchase && selectedHead"
            class="hidden rounded-md bg-slate-100 px-2 py-1 text-[11px] font-medium text-slate-600 sm:inline"
          >
            Prev. billed {{ formatCurrency(totalPaidForHead) }}
          </span>
        </div>

        <div class="px-4 py-4 sm:px-5">
          <div class="mb-4 space-y-2">
            <BaseLabel>Entry Type</BaseLabel>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="option in billEntryTypeOptions"
                :key="option.id"
                type="button"
                class="rounded-lg border px-4 py-2 text-sm font-semibold transition-all"
                :class="
                  form.entry_type === option.id
                    ? option.activeClass
                    : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300 hover:bg-slate-50'
                "
                @click="form.entry_type = option.id"
              >
                {{ option.label }}
              </button>
            </div>
          </div>

          <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="space-y-1.5">
              <BaseLabel for="payment_date">Bill Date</BaseLabel>
              <BaseInput id="payment_date" v-model="form.payment_date" type="date" :required="true" />
            </div>
            <div class="space-y-1.5">
              <BaseLabel for="payment_method">Payment Method</BaseLabel>
              <BaseSelect
                id="payment_method"
                v-model="form.payment_method"
                :options="paymentMethodOptions"
                placeholder="Select method"
                :required="true"
              />
            </div>

            <template v-if="isAssetPurchase">
              <div class="space-y-1.5 sm:col-span-2">
                <BaseLabel for="asset_account_id">Asset Account</BaseLabel>
                <BaseSelect
                  id="asset_account_id"
                  v-model="form.asset_account_id"
                  :options="purchaseAssetAccountOptions"
                  placeholder="Select purchase-linked asset account"
                  :required="true"
                  :disabled="!purchaseAssetAccountOptions.length"
                />
                <p v-if="!purchaseAssetAccountOptions.length" class="text-xs text-amber-600">
                  No asset accounts are linked to purchase. Enable “Link to Purchase” on an asset
                  account first.
                </p>
              </div>

              <div class="space-y-1.5 sm:col-span-2">
                <BaseLabel for="vendor_account_id">Vendor Account</BaseLabel>
                <BaseSelect
                  id="vendor_account_id"
                  v-model="form.vendor_account_id"
                  :options="vendorAccountOptions"
                  placeholder="Select vendor account"
                  :required="true"
                  :disabled="!vendorAccountOptions.length"
                />
                <p v-if="!vendorAccountOptions.length" class="text-xs text-amber-600">
                  No active vendor accounts found. Create a vendor account first.
                </p>
              </div>
            </template>

            <template v-else>
            <div class="space-y-1.5">
              <BaseLabel for="category_id">Expense Category</BaseLabel>
              <BaseSelect
                id="category_id"
                v-model="form.category_id"
                :options="categoryOptions"
                placeholder="Select category"
                :required="true"
              />
            </div>

            <div v-if="!isOperatingCost" class="space-y-1.5">
              <BaseLabel for="head_id">Expense Head</BaseLabel>
              <BaseSelect
                id="head_id"
                v-model="form.head_id"
                :options="headOptions"
                placeholder="Select expense head"
                :required="true"
                :disabled="!form.category_id"
              />
            </div>
            <div v-else class="space-y-1.5 sm:col-span-2">
              <BaseLabel>Expense Heads</BaseLabel>
              <p class="text-xs text-slate-500">
                Select one or more heads. Enter Bill No manually per head (optional) and Amount.
                Heads with linked accounts will show account selection below.
              </p>
              <div
                v-if="operatingHeadChoices.length"
                class="mt-2 overflow-x-auto rounded-lg border border-slate-200"
              >
                <table class="w-full min-w-[720px] text-sm">
                  <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                      <th class="px-3 py-2.5 w-12"></th>
                      <th class="px-3 py-2.5">Expense Head</th>
                      <th class="w-[200px] px-3 py-2.5">Bill No</th>
                      <th class="w-[200px] px-3 py-2.5 text-right">Amount (৳)</th>
                    </tr>
                  </thead>
                  <tbody>
                    <template v-for="head in operatingHeadChoices" :key="head.id">
                      <tr
                        class="border-t border-slate-100 align-middle"
                        :class="isOperatingHeadSelected(head.id) ? 'bg-emerald-50/40' : 'bg-white'"
                      >
                        <td class="px-3 py-3">
                          <input
                            type="checkbox"
                            class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                            :checked="isOperatingHeadSelected(head.id)"
                            @change="toggleOperatingHead(head, $event.target.checked)"
                          />
                        </td>
                        <td class="px-3 py-3">
                          <p class="font-medium text-slate-900">{{ head.name }}</p>
                          <p class="text-xs text-slate-500">
                            Base {{ formatCurrency(head.base_price) }}
                            <span
                              v-if="getOperatingHeadLinkedGroups(head).length"
                              class="text-emerald-700"
                            >
                              · Linked accounts
                            </span>
                          </p>
                        </td>
                        <td class="px-3 py-3">
                          <input
                            v-if="isOperatingHeadSelected(head.id)"
                            type="text"
                            placeholder="Optional"
                            :value="getOperatingHeadBillNo(head.id)"
                            class="h-12 w-full rounded-lg border border-emerald-300 bg-white px-3 text-sm font-semibold text-emerald-950 shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25"
                            @input="setOperatingHeadBillNo(head.id, $event.target.value)"
                          />
                          <span v-else class="block text-slate-300">—</span>
                        </td>
                        <td class="px-3 py-3">
                          <div
                            v-if="isOperatingHeadSelected(head.id)"
                            class="relative ml-auto w-full max-w-[200px]"
                          >
                            <span
                              class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-base font-semibold text-emerald-700/80"
                            >
                              ৳
                            </span>
                            <input
                              type="number"
                              min="0"
                              step="0.01"
                              placeholder="0.00"
                              :value="getOperatingHeadAmount(head.id)"
                              class="h-12 w-full rounded-lg border border-emerald-300 bg-white pl-9 pr-3 text-lg font-semibold tabular-nums text-emerald-950 shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25"
                              @input="setOperatingHeadAmount(head.id, $event.target.value)"
                            />
                          </div>
                          <span v-else class="block text-right text-slate-300">—</span>
                        </td>
                      </tr>
                      <tr
                        v-if="
                          isOperatingHeadSelected(head.id) &&
                          getOperatingHeadLinkedGroups(head).length
                        "
                        class="border-t border-emerald-100 bg-emerald-50/20"
                      >
                        <td colspan="4" class="px-3 py-3">
                          <ExpenseHeadLinkedAccountsPanel
                            :groups="getOperatingHeadLinkedGroups(head)"
                            :selected-category="getOperatingHeadLinkedCategory(head.id)"
                            :selected-account-id="getOperatingHeadLinkedAccountId(head.id)"
                            @select="
                              (category, accountId) =>
                                selectOperatingHeadLinkedAccount(head.id, category, accountId)
                            "
                          />
                        </td>
                      </tr>
                    </template>
                  </tbody>
                </table>
              </div>
              <p v-else class="mt-2 text-sm text-slate-500">
                No active expense heads for this category.
              </p>
              <p
                v-if="selectedOperatingLines.length"
                class="mt-2 text-sm font-semibold text-emerald-800"
              >
                {{ selectedOperatingLines.length }} head(s) · Total
                {{ formatCurrency(totalBillAmount) }}
              </p>
            </div>

            <ExpenseHeadLinkedAccountsPanel
              v-if="!isOperatingCost && linkedAccountGroups.length"
              :groups="linkedAccountGroups"
              :selected-category="form.linked_account_category"
              :selected-account-id="form.linked_account_id"
              @select="selectLinkedAccount"
            />

            <div v-if="isDirectCost" class="sm:col-span-2">
              <DirectCostApplicationSelector
                v-model="form.application_ids"
                :job-id="form.job_id"
                :expense-head-id="form.head_id"
                :expense-head-name="selectedHead?.name ?? ''"
                @update:job-id="form.job_id = $event"
                @select="handleApplicationSelection"
              />
            </div>

            <div v-if="isClientRecruitmentCost" class="sm:col-span-2">
              <ClientRecruitmentDlSelector v-model="form.demand_letter_id" />
            </div>
            </template>

            <div v-if="!isOperatingCost" class="space-y-1.5 sm:col-span-2">
              <BaseLabel for="particular">Particular</BaseLabel>
              <BaseInput
                id="particular"
                v-model="form.particular"
                :placeholder="isAssetPurchase ? 'Asset purchase particular' : 'Expense bill particular'"
              />
            </div>

            <div v-if="!isOperatingCost" class="space-y-1.5 sm:col-span-2">
              <BaseLabel for="amount">
                {{
                  isDirectCost && selectedApplications.length > 1
                    ? 'Amount Per Candidate (৳)'
                    : 'Bill Amount (৳)'
                }}
              </BaseLabel>
              <div class="relative">
                <span
                  class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-lg font-semibold text-emerald-700/80"
                >
                  ৳
                </span>
                <BaseInput
                  id="amount"
                  v-model="form.amount"
                  type="number"
                  min="0"
                  step="0.01"
                  placeholder="0.00"
                  :required="true"
                  :class-name="'w-full !h-11 !rounded-lg !border-emerald-200 !bg-emerald-50/40 !pl-9 !pr-3 !text-xl !font-semibold !tabular-nums !text-emerald-950 focus:!border-emerald-500 focus:!outline-none focus:!ring-2 focus:!ring-emerald-500/25'"
                />
              </div>
              <div
                v-if="isDirectCost && selectedApplications.length > 1"
                class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-600"
              >
                <span>{{ selectedApplications.length }} candidates × unit amount</span>
                <span class="font-semibold text-emerald-800">
                  Total {{ formatCurrency(totalBillAmount) }}
                </span>
              </div>
              <p v-else-if="!isAssetPurchase && selectedHead" class="text-xs text-slate-500">
                Base price {{ formatCurrency(selectedHead.base_price) }}
                <span
                  v-if="hasLegacyExpenseCostAccountType && !selectedExpenseCostAccount"
                  class="text-amber-700"
                >
                  · No linked account for this head
                </span>
              </p>
            </div>

            <div class="sm:col-span-2">
              <button
                type="button"
                class="inline-flex w-full items-center justify-between gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-left text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-100"
                @click="showOptionalDetails = !showOptionalDetails"
              >
                <span class="inline-flex items-center gap-2">
                  <i
                    class="fa text-xs text-slate-500"
                    :class="showOptionalDetails ? 'fa-chevron-up' : 'fa-chevron-down'"
                  ></i>
                  Additional details
                  <span class="text-xs font-normal text-slate-500">
                    (Reference, Remarks, Receipt)
                  </span>
                </span>
                <span class="text-xs font-normal text-emerald-700">
                  {{ showOptionalDetails ? 'Hide' : 'Expand' }}
                </span>
              </button>

              <div
                v-show="showOptionalDetails"
                class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2"
              >
                <div class="space-y-1.5">
                  <BaseLabel for="reference_no">Reference No</BaseLabel>
                  <BaseInput
                    id="reference_no"
                    v-model="form.reference_no"
                    placeholder="Eg: EXP-001/26"
                  />
                </div>

                <div class="space-y-1.5">
                  <BaseLabel for="remarks">Remarks</BaseLabel>
                  <BaseInput id="remarks" v-model="form.remarks" placeholder="Optional notes" />
                </div>

                <div class="space-y-1.5 sm:col-span-2">
                  <BaseLabel for="receipt_path">Bill Receipt (Optional)</BaseLabel>
                  <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50/70 p-3">
                    <div
                      v-if="receiptPreviews.length"
                      class="mb-3 grid grid-cols-2 gap-3 sm:grid-cols-3"
                    >
                      <BaseImagePreview
                        v-for="(preview, index) in receiptPreviews"
                        :key="`${preview}-${index}`"
                        :src="preview"
                        width="100%"
                        height="120px"
                        class-name="rounded-md"
                        @cancelImage="removeReceiptAt(index)"
                      />
                    </div>
                    <BaseFileInput
                      id="receipt_path"
                      accept=".jpg,.jpeg,.png"
                      multiple
                      :fileName="receiptFileLabel"
                      @change="handleReceiptFilesChange"
                    />
                    <p class="mt-1.5 text-[11px] text-slate-500">
                      JPG or PNG, max 2 MB each · up to {{ maxReceiptFiles }} images
                    </p>
                    <div v-if="fileError.receipt_path" class="mt-1 text-sm text-red-600">
                      {{ fileError.receipt_path }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <div
        class="sticky bottom-3 z-20 mt-3 flex flex-wrap items-center gap-2 rounded-xl border border-slate-200 bg-white/95 px-4 py-3 shadow-lg backdrop-blur-sm sm:px-5 print:hidden"
      >
        <BaseButton
          type="submit"
          v-can="'bill_generation.create'"
          class="cursor-pointer rounded-lg bg-emerald-600 px-4 py-2 text-white shadow-sm hover:bg-emerald-700"
          :disabled="submitLoading"
        >
          <i class="fa fa-check mr-1.5"></i>
          {{ submitLoading && submitMode === 'submitted' ? 'Saving...' : saveButtonLabel }}
        </BaseButton>
        <BaseButton
          type="button"
          v-can="'bill_generation.create'"
          :className="'cursor-pointer rounded-lg bg-sky-600 px-4 py-2 text-white shadow-sm hover:bg-sky-700'"
          :disabled="submitLoading"
          @click="handleManualSubmit"
        >
          <i class="fa fa-file-text-o mr-1.5"></i>
          {{ submitLoading && submitMode === 'pending' ? 'Saving...' : 'Submit Manual Request' }}
        </BaseButton>
        <BaseButton
          type="button"
          :className="'cursor-pointer rounded-lg border border-slate-300 bg-white px-4 py-2 text-slate-700 hover:bg-slate-50'"
          :disabled="submitLoading"
          @click="resetForm"
        >
          Reset
        </BaseButton>
        <span
          v-if="selectedLinkedAccount"
          class="ml-auto hidden text-xs text-slate-500 lg:inline"
        >
          {{ selectedLinkedAccount.categoryLabel }}:
          <span class="font-medium text-slate-800">{{ selectedLinkedAccount.account.name }}</span>
        </span>
      </div>
    </BaseForm>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, reactive, ref, watch } from 'vue'
import BaseForm from '@/shared/components/base/BaseForm.vue'
import BaseFileInput from '@/shared/components/base/BaseFileInput.vue'
import BaseImagePreview from '@/shared/components/base/BaseImagePreview.vue'
import { useFileHandler } from '@/shared/composables/useFileHandler'
import { useExpenseCategoryStore } from '@/finance/store/expenseCategoryStore'
import { useExpenseHeadStore } from '@/finance/store/expenseHeadStore'
import { useExpensePaymentStore } from '@/finance/store/expensePaymentStore'
import { useExpenseCostAccountsStore } from '@/finance/store/expenseCostAccountsStore'
import { paymentMethods } from '@/finance/data/paymentData'
import {
  DIRECT_COST_CATEGORY_CODE,
  getDirectCostApplications,
  getDirectCostJob,
} from '@/finance/data/directCostApplicationData'
import {
  CLIENT_RECRUITMENT_COST_CATEGORY_CODE,
  getDemandLetter,
} from '@/finance/data/clientRecruitmentDemandLetterData'
import { isCategoryCode, OPERATING_COST_CATEGORY_CODE } from '@/finance/data/expenseCategoryCodes'
import DirectCostApplicationSelector from './DirectCostApplicationSelector.vue'
import ClientRecruitmentDlSelector from './ClientRecruitmentDlSelector.vue'
import ExpenseHeadLinkedAccountsPanel from './ExpenseHeadLinkedAccountsPanel.vue'
import BillGenerationPreview from './BillGenerationPreview.vue'
import BillSubmittedSuccess from './BillSubmittedSuccess.vue'
import { formatCurrency, formatDirectExpenseParticularDescription } from '@/finance/utils/billUtils'
import {
  buildLinkedAccountGroups,
  findLinkedAccountSelection,
  getLinkedBillAccountName,
  getLinkedBillAccountTypeLabel,
  hasLinkedBillAccount,
} from '@/finance/utils/linkedAccountOptions'
import { normalizeLinkedAccounts, isExpenseCostAccountCategory } from '@/finance/data/expenseHeadAccountLinkData'
import { getPartyConfig } from '@/finance/config/partyAccountConfigs'
import { useAccountStore } from '@/finance/store/accountStore'
import { usePartyAccountsStore } from '@/finance/store/partyAccountsStore'
import { useAgentAccountStore } from '@/finance/store/agentAccountStore'
import { useFinanceAccountStore } from '@/finance/store/financeAccountStore'
import { ACCOUNT_CATEGORIES } from '@/finance/data/accountCategoryCodes'
import { useAuthQuery } from '@/modules/auth/queries/useAuthQuery'
import Swal from 'sweetalert2'

const props = defineProps({
  initialCategoryId: {
    type: [String, Number],
    default: '',
  },
  initialHeadId: {
    type: [String, Number],
    default: '',
  },
})

const emit = defineEmits(['saved'])

const categoryStore = useExpenseCategoryStore()
const headStore = useExpenseHeadStore()
const paymentStore = useExpensePaymentStore()
const expenseCostAccountsStore = useExpenseCostAccountsStore()
const accountStore = useAccountStore()
const partyAccountsStore = usePartyAccountsStore()
const agentAccountStore = useAgentAccountStore()
const financeAccountStore = useFinanceAccountStore()
const { data: authData } = useAuthQuery()

const billEntryTypeOptions = [
  {
    id: 'expense_bill',
    label: 'Expense Bill',
    activeClass: 'border-emerald-500 bg-emerald-50 text-emerald-800 ring-1 ring-emerald-500',
  },
  {
    id: 'asset_purchase',
    label: 'Asset Purchase',
    activeClass: 'border-sky-500 bg-sky-50 text-sky-800 ring-1 ring-sky-500',
  },
]

const submitLoading = ref(false)
const submitMode = ref('submitted')
const showOptionalDetails = ref(false)
const submittedBill = ref(null)
const submittedBillPreviewRef = ref(null)

const authUserName = computed(() => {
  const user = authData.value?.data ?? {}
  return user.name?.trim() || user.full_name?.trim() || ''
})

function resolveSubmittedRequestNo(result) {
  const payments = Array.isArray(result.payments)
    ? result.payments
    : result.payment
      ? [result.payment]
      : []

  const requestNos = [
    ...new Set(payments.map((payment) => payment?.request_no?.trim()).filter(Boolean)),
  ]

  return requestNos[0] || ''
}

function resolveSubmittedBillNo(result) {
  const payments = Array.isArray(result.payments)
    ? result.payments
    : result.payment
      ? [result.payment]
      : []

  const voucherNos = [
    ...new Set(payments.map((payment) => payment?.voucher_no?.trim()).filter(Boolean)),
  ]

  if (!voucherNos.length) return ''
  if (voucherNos.length === 1) return voucherNos[0]
  return ''
}

function buildSubmittedLineItems(result) {
  const payments = Array.isArray(result.payments) ? result.payments : []
  if (!payments.length) {
    if (isDirectCost.value && selectedApplications.value.length) {
      const unitAmount = Number(form.amount) || 0
      return selectedApplications.value.map((application) => ({
        description: formatDirectExpenseParticularDescription(selectedHead.value?.name),
        expenseHeadName: selectedHead.value?.name || '—',
        billNo: '',
        amount: unitAmount,
        candidateName: application.candidate_name || '',
        passportNo: application.passport_no || '',
        applicationStatus: application.application_status || '',
      }))
    }

    return selectedOperatingLines.value.map((line) => {
      const head = operatingHeadChoices.value.find((item) => Number(item.id) === Number(line.head_id))
      const selection = findLinkedAccountSelection(
        getOperatingHeadLinkedGroups(head),
        line.linked_account_category,
        line.linked_account_id
      )
      return {
        description: `${selectedCategoryName.value} · ${head?.name || 'Head'}`,
        expenseHeadName: head?.name || '—',
        billNo: String(line.bill_no || '').trim(),
        amount: Number(line.amount) || 0,
        linkedAccountType: selection?.categoryLabel || '',
        linkedAccountName: selection?.account?.name?.split(' — ')[0] || selection?.account?.name || '',
      }
    })
  }

  return payments.map((payment) => {
    const isDirectExpense = isCategoryCode(
      { code: payment.category_code, name: payment.category_name },
      DIRECT_COST_CATEGORY_CODE
    )

    return {
      description: isDirectExpense
        ? formatDirectExpenseParticularDescription(payment.head_name)
        : payment.particular ||
          `${payment.category_name || selectedCategoryName.value} · ${payment.head_name || 'Head'}`,
      expenseHeadName: payment.head_name || '—',
      billNo: payment.voucher_no || payment.reference_no || '',
      amount: Number(payment.amount) || 0,
      linkedAccountType: getLinkedBillAccountTypeLabel(payment),
      linkedAccountName: getLinkedBillAccountName(payment),
      candidateName: isDirectExpense ? payment.candidate_name || '' : '',
      passportNo: isDirectExpense ? payment.passport_no || '' : '',
      applicationStatus: isDirectExpense ? payment.application_status || '' : '',
    }
  })
}

function buildPrintLinkedAccounts(result = null) {
  const payments = Array.isArray(result?.payments)
    ? result.payments
    : result?.payment
      ? [result.payment]
      : []

  let accounts = []

  if (payments.length) {
    accounts = payments
      .filter((payment) => hasLinkedBillAccount(payment))
      .map((payment) => ({
        expenseHeadName: payment.head_name || '',
        typeLabel: getLinkedBillAccountTypeLabel(payment),
        accountName: getLinkedBillAccountName(payment),
      }))
  } else if (isOperatingCost.value) {
    accounts = selectedOperatingLines.value
      .map((line) => {
        const head = getOperatingHeadRecord(line.head_id)
        const selection = findLinkedAccountSelection(
          getOperatingHeadLinkedGroups(head),
          line.linked_account_category,
          line.linked_account_id
        )
        if (!selection) return null
        return {
          expenseHeadName: head?.name || '',
          typeLabel: selection.categoryLabel || '',
          accountName:
            selection.account?.name?.replace(/\s—\s৳.*$/, '') ||
            selection.account?.name ||
            '',
        }
      })
      .filter(Boolean)
  } else if (selectedLinkedAccount.value) {
    accounts = [
      {
        expenseHeadName: selectedHead.value?.name || '',
        typeLabel: selectedLinkedAccount.value.categoryLabel || '',
        accountName:
          selectedLinkedAccount.value.account?.name?.replace(/\s—\s৳.*$/, '') ||
          selectedLinkedAccount.value.account?.name ||
          '',
      },
    ]
  }

  const seen = new Set()
  return accounts.filter((account) => {
    const key = [
      account.expenseHeadName,
      account.typeLabel,
      account.accountName,
    ]
      .map((part) => String(part || '').trim().toLowerCase())
      .join('|')
    if (seen.has(key)) return false
    seen.add(key)
    return true
  })
}

const getRequestedByPayload = () => authData.value?.data ?? {}

const createDefaultForm = () => ({
  entry_type: 'expense_bill',
  payment_date: new Date().toISOString().slice(0, 10),
  category_id: props.initialCategoryId ? String(props.initialCategoryId) : '',
  head_id: props.initialHeadId ? String(props.initialHeadId) : '',
  asset_account_id: '',
  vendor_account_id: '',
  amount: '',
  payment_method: 'cash',
  particular: '',
  reference_no: '',
  remarks: '',
  receipt_path: [],
  receipt_preview: [],
  application_ids: [],
  job_id: '',
  demand_letter_id: '',
  linked_account_category: '',
  linked_account_id: '',
  operating_lines: [],
})

const form = reactive(createDefaultForm())
const { fileName, fileError } = useFileHandler(form)

const maxReceiptFiles = 10
const maxReceiptBytes = 2 * 1024 * 1024

const receiptPreviews = computed(() =>
  Array.isArray(form.receipt_preview) ? form.receipt_preview : []
)

const receiptFileLabel = computed(() => {
  const files = Array.isArray(form.receipt_path) ? form.receipt_path : []
  if (!files.length) return ''
  if (files.length === 1) return files[0].name
  return `${files.length} images selected`
})

function clearReceiptFiles() {
  ;(Array.isArray(form.receipt_preview) ? form.receipt_preview : []).forEach((url) => {
    if (typeof url === 'string' && url.startsWith('blob:')) {
      URL.revokeObjectURL(url)
    }
  })
  form.receipt_path = []
  form.receipt_preview = []
  fileName.value.receipt_path = ''
  fileError.value.receipt_path = ''
}

function removeReceiptAt(index) {
  const files = Array.isArray(form.receipt_path) ? [...form.receipt_path] : []
  const previews = Array.isArray(form.receipt_preview) ? [...form.receipt_preview] : []
  const [removedPreview] = previews.splice(index, 1)
  files.splice(index, 1)

  if (typeof removedPreview === 'string' && removedPreview.startsWith('blob:')) {
    URL.revokeObjectURL(removedPreview)
  }

  form.receipt_path = files
  form.receipt_preview = previews
  fileName.value.receipt_path = receiptFileLabel.value
  fileError.value.receipt_path = ''
}

function handleReceiptFilesChange(event) {
  const selected = Array.from(event.target.files || [])
  event.target.value = null

  if (!selected.length) return

  const current = Array.isArray(form.receipt_path) ? [...form.receipt_path] : []
  const previews = Array.isArray(form.receipt_preview) ? [...form.receipt_preview] : []
  const remaining = maxReceiptFiles - current.length

  if (remaining <= 0) {
    fileError.value.receipt_path = `You can upload up to ${maxReceiptFiles} images.`
    return
  }

  const accepted = []
  for (const file of selected.slice(0, remaining)) {
    if (!file.type.startsWith('image/')) {
      fileError.value.receipt_path = 'Only JPG or PNG images are allowed.'
      return
    }
    if (file.size > maxReceiptBytes) {
      fileError.value.receipt_path = `Each image must be under 2 MB. "${file.name}" is too large.`
      return
    }
    accepted.push(file)
  }

  accepted.forEach((file) => {
    current.push(file)
    previews.push(URL.createObjectURL(file))
  })

  form.receipt_path = current
  form.receipt_preview = previews
  fileName.value.receipt_path = receiptFileLabel.value
  fileError.value.receipt_path = ''

  if (selected.length > remaining) {
    fileError.value.receipt_path = `Only ${maxReceiptFiles} images allowed. Extra files were skipped.`
  }
}

const paymentMethodOptions = paymentMethods.map((method) => ({
  id: method.id,
  name: method.name,
}))

const selectedPaymentMethodLabel = computed(
  () => paymentMethodOptions.find((method) => method.id === form.payment_method)?.name ?? '—'
)

const isAssetPurchase = computed(() => form.entry_type === 'asset_purchase')

const selectedAssetAccount = computed(
  () =>
    financeAccountStore
      .getAccountsByCategory(ACCOUNT_CATEGORIES.ASSET)
      .find((account) => Number(account.id) === Number(form.asset_account_id)) ?? null
)

const purchaseAssetAccountOptions = computed(() =>
  financeAccountStore
    .getAccountsByCategory(ACCOUNT_CATEGORIES.ASSET)
    .filter((account) => account.status === 'Active' && account.link_to_purchase)
    .map((account) => ({
      id: account.id,
      name: `${account.account_name} — ${formatCurrency(account.balance ?? account.current_balance ?? 0)}`,
    }))
)

const vendorAccountOptions = computed(() =>
  partyAccountsStore
    .getAccounts('vendor')
    .filter((account) => account.status === 'Active')
    .map((account) => ({
      id: account.id,
      name: `${account.vendor_name || account.account_name} — ${formatCurrency(account.balance ?? account.current_balance ?? 0)}`,
    }))
)

const categoryOptions = computed(() =>
  categoryStore.categories
    .filter((category) => category.status === 'Active')
    .map((category) => ({
      id: category.id,
      name: category.name,
    }))
)

const headOptions = computed(() =>
  headStore.heads
    .filter(
      (head) =>
        head.status === 'Active' &&
        (!form.category_id || Number(head.category_id) === Number(form.category_id))
    )
    .map((head) => ({
      id: head.id,
      name: `${head.name} (${formatCurrency(head.base_price)})`,
    }))
)

const selectedHead = computed(
  () => headStore.heads.find((head) => Number(head.id) === Number(form.head_id)) ?? null
)

const selectedCategory = computed(
  () => categoryStore.categories.find((category) => category.id === Number(form.category_id)) ?? null
)

const selectedCategoryName = computed(() => selectedCategory.value?.name ?? '—')

const isDirectCost = computed(() => isCategoryCode(selectedCategory.value, DIRECT_COST_CATEGORY_CODE))

const isOperatingCost = computed(() =>
  isCategoryCode(selectedCategory.value, OPERATING_COST_CATEGORY_CODE)
)

const isClientRecruitmentCost = computed(() =>
  isCategoryCode(selectedCategory.value, CLIENT_RECRUITMENT_COST_CATEGORY_CODE)
)

const operatingHeadChoices = computed(() =>
  headStore.heads
    .filter(
      (head) =>
        head.status === 'Active' &&
        form.category_id &&
        Number(head.category_id) === Number(form.category_id)
    )
    .map((head) => ({
      id: head.id,
      name: head.name,
      base_price: Number(head.base_price) || 0,
      category_id: head.category_id,
      linked_accounts: normalizeLinkedAccounts(head.linked_accounts),
    }))
)

const selectedOperatingLines = computed(() => form.operating_lines || [])

const linkedAccountStores = {
  expenseCostAccountsStore,
  accountStore,
  partyAccountsStore,
  agentAccountStore,
}

const printLineItems = computed(() => {
  if (!isOperatingCost.value) return []
  return selectedOperatingLines.value.map((line) => {
    const head = operatingHeadChoices.value.find((item) => Number(item.id) === Number(line.head_id))
    return {
      description: `${selectedCategoryName.value} · ${head?.name || 'Head'}`,
      expenseHeadName: head?.name || '—',
      billNo: String(line.bill_no || '').trim(),
      amount: Number(line.amount) || 0,
    }
  })
})

function getOperatingHeadRecord(headId) {
  return (
    operatingHeadChoices.value.find((head) => Number(head.id) === Number(headId)) ||
    headStore.heads.find((head) => Number(head.id) === Number(headId)) ||
    null
  )
}

async function ensureStoresForLinkedCategories(categories = []) {
  const unique = [...new Set((categories || []).filter(Boolean))]
  if (!unique.length) return

  await Promise.all(
    unique.map((category) => {
      if (isExpenseCostAccountCategory(category)) {
        return expenseCostAccountsStore.fetchAccounts(category)
      }
      if (category === 'main') {
        return accountStore.fetchActiveAccounts()
      }
      if (category === 'agent') {
        return agentAccountStore.fetchAccounts()
      }
      if (getPartyConfig(category)) {
        return partyAccountsStore.fetchAccounts(category)
      }
      return Promise.resolve()
    })
  )
}

async function ensureLinkedAccountsForHead(head) {
  const links = normalizeLinkedAccounts(head?.linked_accounts)
  await ensureStoresForLinkedCategories(links.map((link) => link.account_category))
}

async function ensureAssetPurchaseAccounts() {
  await Promise.all([
    financeAccountStore.fetchAccounts(ACCOUNT_CATEGORIES.ASSET),
    partyAccountsStore.fetchAccounts('vendor'),
  ])
}

async function ensureHeadBillContext(headId) {
  if (!headId) return
  await paymentStore.fetchBillEntries({
    force: true,
    page: 1,
    perPage: 300,
    filters: { head_id: headId },
  })
}

function getOperatingHeadLinkedGroups(head) {
  const fullHead = getOperatingHeadRecord(head?.id ?? head)
  if (!fullHead) return []
  return buildLinkedAccountGroups(fullHead, linkedAccountStores, formatCurrency)
}

function getOperatingLine(headId) {
  return form.operating_lines.find((item) => Number(item.head_id) === Number(headId)) ?? null
}

function getOperatingHeadLinkedCategory(headId) {
  return getOperatingLine(headId)?.linked_account_category || ''
}

function getOperatingHeadLinkedAccountId(headId) {
  return getOperatingLine(headId)?.linked_account_id || ''
}

function selectOperatingHeadLinkedAccount(headId, category, accountId) {
  const line = getOperatingLine(headId)
  if (!line) return
  line.linked_account_category = category || ''
  line.linked_account_id = accountId ? String(accountId) : ''
}

async function autoSelectOperatingHeadLinkedAccount(head) {
  const line = getOperatingLine(head.id)
  if (!line) return

  await ensureLinkedAccountsForHead(head)

  const groups = getOperatingHeadLinkedGroups(head)
  if (!groups.length) {
    line.linked_account_category = ''
    line.linked_account_id = ''
    return
  }

  const groupWithSingleAccount = groups.find((group) => group.accounts.length === 1)
  if (groupWithSingleAccount) {
    line.linked_account_category = groupWithSingleAccount.category
    line.linked_account_id = String(groupWithSingleAccount.accounts[0].id)
    return
  }

  line.linked_account_category = ''
  line.linked_account_id = ''
}

function isOperatingHeadSelected(headId) {
  return selectedOperatingLines.value.some((line) => Number(line.head_id) === Number(headId))
}

function getOperatingHeadAmount(headId) {
  return getOperatingLine(headId)?.amount ?? ''
}

function setOperatingHeadAmount(headId, amount) {
  const line = getOperatingLine(headId)
  if (line) {
    line.amount = amount
  }
}

function getOperatingHeadBillNo(headId) {
  return getOperatingLine(headId)?.bill_no ?? ''
}

function setOperatingHeadBillNo(headId, billNo) {
  const line = getOperatingLine(headId)
  if (line) {
    line.bill_no = billNo
  }
}

async function toggleOperatingHead(head, checked) {
  if (checked) {
    if (isOperatingHeadSelected(head.id)) return
    form.operating_lines.push({
      head_id: String(head.id),
      amount: head.base_price || '',
      bill_no: '',
      linked_account_category: '',
      linked_account_id: '',
    })
    await autoSelectOperatingHeadLinkedAccount(head)
    return
  }

  form.operating_lines = form.operating_lines.filter(
    (line) => Number(line.head_id) !== Number(head.id)
  )
}

const selectedDemandLetter = computed(() => getDemandLetter(form.demand_letter_id))

const linkedAccountGroups = computed(() => {
  if (!selectedHead.value) return []
  return buildLinkedAccountGroups(selectedHead.value, linkedAccountStores, formatCurrency)
})

const selectedLinkedAccount = computed(() =>
  findLinkedAccountSelection(
    linkedAccountGroups.value,
    form.linked_account_category,
    form.linked_account_id
  )
)

const hasLegacyExpenseCostAccountType = computed(
  () =>
    !linkedAccountGroups.value.length &&
    Boolean(expenseCostAccountsStore.getCostTypeByCategoryId(form.category_id))
)

const selectedExpenseCostAccount = computed(() => {
  if (!form.category_id || !form.head_id || linkedAccountGroups.value.length) return null
  return expenseCostAccountsStore.getAccountByHeadId(form.category_id, form.head_id)
})

const selectedApplications = computed(() =>
  getDirectCostApplications(form.application_ids).map((application) => {
    const job = getDirectCostJob(application.job_id)

    return {
      application_id: application.id,
      candidate_name: application.name,
      passport_no: application.passport_no,
      application_status: application.process_status || application.status,
      job_id: application.job_id,
      job_name: job?.job_name ?? '—',
      job_code: job?.job_code ?? '—',
      client_name: job?.client_name ?? '—',
    }
  })
)

const selectedJobSummary = computed(() => {
  if (!form.job_id) return null
  return getDirectCostJob(form.job_id)
})

const totalBillAmount = computed(() => {
  if (isOperatingCost.value) {
    return selectedOperatingLines.value.reduce((sum, line) => sum + (Number(line.amount) || 0), 0)
  }

  const unitAmount = Number(form.amount) || 0
  if (!isDirectCost.value || !selectedApplications.value.length) {
    return unitAmount
  }

  return unitAmount * selectedApplications.value.length
})

const previewUnitAmountNote = computed(() => {
  if (!isDirectCost.value || selectedApplications.value.length <= 1) return ''
  return `${formatCurrency(Number(form.amount) || 0)} × ${selectedApplications.value.length} candidates`
})

const saveButtonLabel = computed(() => {
  if (isDirectCost.value && selectedApplications.value.length > 1) {
    return `Submit ${selectedApplications.value.length} Bills`
  }

  return 'Submit Digital Request'
})

const totalPaidForHead = computed(() =>
  form.head_id ? paymentStore.getTotalPaidByHead(form.head_id) : 0
)

const selectLinkedAccount = (category, accountId) => {
  form.linked_account_category = category
  form.linked_account_id = accountId ? String(accountId) : ''
}

const autoSelectLinkedAccount = async () => {
  form.linked_account_category = ''
  form.linked_account_id = ''

  if (!selectedHead.value) return

  await ensureLinkedAccountsForHead(selectedHead.value)

  const groups = linkedAccountGroups.value
  if (!groups.length) return

  const groupWithSingleAccount = groups.find((group) => group.accounts.length === 1)
  if (groupWithSingleAccount) {
    selectLinkedAccount(groupWithSingleAccount.category, groupWithSingleAccount.accounts[0].id)
  }
}

const applyParticular = () => {
  if (!form.category_id || !form.head_id) {
    form.particular = ''
    return
  }

  const categoryName = selectedCategoryName.value
  const headName = selectedHead.value?.name
  if (!categoryName || !headName) return

  if (isDirectCost.value && selectedApplications.value.length === 1) {
    const application = selectedApplications.value[0]
    form.particular = `${categoryName} - ${headName} - ${application.candidate_name} (${application.passport_no})`
    return
  }

  if (isDirectCost.value && selectedApplications.value.length > 1) {
    form.particular = `${categoryName} - ${headName} - ${selectedApplications.value.length} candidates`
    return
  }

  if (isClientRecruitmentCost.value && selectedDemandLetter.value) {
    form.particular = `${categoryName} - ${headName} - ${selectedDemandLetter.value.dl_no} (${selectedDemandLetter.value.client_name})`
    return
  }

  form.particular = `${categoryName} - ${headName}`
}

const handleApplicationSelection = () => {
  applyParticular()
}

const applyBasePrice = (force = false) => {
  if (!selectedHead.value) {
    if (force) form.amount = ''
    return
  }
  if (force || !form.amount || Number(form.amount) === 0) {
    form.amount = Number(selectedHead.value.base_price) || ''
  }
}

watch(
  () => form.category_id,
  (categoryId, previousCategoryId) => {
    if (categoryId !== previousCategoryId) {
      const stillValid = headOptions.value.some((head) => Number(head.id) === Number(form.head_id))
      if (!stillValid) {
        form.head_id = ''
        form.amount = ''
      }

      form.operating_lines = []

      if (!isCategoryCode(selectedCategory.value, DIRECT_COST_CATEGORY_CODE)) {
        form.application_ids = []
        form.job_id = ''
      }

      if (!isCategoryCode(selectedCategory.value, CLIENT_RECRUITMENT_COST_CATEGORY_CODE)) {
        form.demand_letter_id = ''
      }
    }
    applyParticular()
  }
)

watch(
  () => form.head_id,
  async (headId) => {
    form.application_ids = []
    form.job_id = ''
    if (headId) {
      await ensureHeadBillContext(headId)
      await autoSelectLinkedAccount()
      applyParticular()
      applyBasePrice(true)
      return
    }

    form.amount = ''
    await autoSelectLinkedAccount()
    applyParticular()
  }
)

watch(
  () => form.demand_letter_id,
  () => {
    applyParticular()
  }
)

watch(
  () => form.application_ids,
  () => {
    applyParticular()
  },
  { deep: true }
)

watch(
  () => [props.initialCategoryId, props.initialHeadId],
  ([categoryId, headId]) => {
    if (categoryId) {
      form.category_id = String(categoryId)
    }
    if (headId) {
      form.head_id = String(headId)
    }
    applyParticular()
    applyBasePrice(Boolean(headId))
  },
  { immediate: true }
)

const resetForm = () => {
  clearReceiptFiles()
  Object.assign(form, createDefaultForm())
  showOptionalDetails.value = false
  applyParticular()
  applyBasePrice(Boolean(form.head_id))
}

function handleManualSubmit() {
  return handleSubmit('pending')
}

async function handleSubmit(statusOrEvent = 'submitted') {
  const status = statusOrEvent === 'pending' ? 'pending' : 'submitted'

  if (isAssetPurchase.value) {
    if (!form.asset_account_id) {
      await Swal.fire({
        icon: 'warning',
        title: 'Asset Account Required',
        text: 'Please select an asset account linked to purchase.',
        confirmButtonColor: '#22C55E',
      })
      return
    }

    if (!form.vendor_account_id) {
      await Swal.fire({
        icon: 'warning',
        title: 'Vendor Account Required',
        text: 'Please select a vendor account for this asset purchase.',
        confirmButtonColor: '#22C55E',
      })
      return
    }

    if (!(Number(form.amount) > 0)) {
      await Swal.fire({
        icon: 'warning',
        title: 'Amount Required',
        text: 'Please enter a valid purchase amount.',
        confirmButtonColor: '#22C55E',
      })
      return
    }
  } else if (isDirectCost.value && !form.job_id) {
    await Swal.fire({
      icon: 'warning',
      title: 'Job Required',
      text: 'Please select a job for Direct Expense bill entry.',
      confirmButtonColor: '#22C55E',
    })
    return
  }

  if (isDirectCost.value && !form.application_ids.length) {
    await Swal.fire({
      icon: 'warning',
      title: 'Application Required',
      text: 'Please select at least one candidate application for Direct Expense bill entry.',
      confirmButtonColor: '#22C55E',
    })
    return
  }

  if (isClientRecruitmentCost.value && !form.demand_letter_id) {
    await Swal.fire({
      icon: 'warning',
      title: 'Demand Letter Required',
      text: 'Please select a demand letter (DL) for Client Recruitment Expense bill entry.',
      confirmButtonColor: '#22C55E',
    })
    return
  }

  if (isOperatingCost.value) {
    if (!selectedOperatingLines.value.length) {
      await Swal.fire({
        icon: 'warning',
        title: 'Expense Heads Required',
        text: 'Please select at least one expense head for Operating Expense.',
        confirmButtonColor: '#22C55E',
      })
      return
    }

    const invalidLine = selectedOperatingLines.value.find((line) => !(Number(line.amount) > 0))
    if (invalidLine) {
      await Swal.fire({
        icon: 'warning',
        title: 'Amount Required',
        text: 'Please enter a valid amount for every selected expense head.',
        confirmButtonColor: '#22C55E',
      })
      return
    }

    const missingLinked = selectedOperatingLines.value.find((line) => {
      const head = getOperatingHeadRecord(line.head_id)
      const groups = getOperatingHeadLinkedGroups(head)
      return groups.length > 0 && !line.linked_account_id
    })
    if (missingLinked) {
      const head = getOperatingHeadRecord(missingLinked.head_id)
      await Swal.fire({
        icon: 'warning',
        title: 'Account Required',
        text: `Please select a linked account for "${head?.name || 'expense head'}".`,
        confirmButtonColor: '#22C55E',
      })
      return
    }
  }

  if (!isOperatingCost.value && linkedAccountGroups.value.length && !form.linked_account_id) {
    await Swal.fire({
      icon: 'warning',
      title: 'Account Required',
      text: 'Please select an account from the linked accounts list.',
      confirmButtonColor: '#22C55E',
    })
    return
  }

  submitMode.value = status
  submitLoading.value = true

  // Snapshot print fields before submit — after batch save, billed apps are
  // filtered out of the selector and can clear form.application_ids.
  const printSnapshot = {
    billDate: form.payment_date,
    paymentMethodLabel: selectedPaymentMethodLabel.value,
    referenceNo: form.reference_no,
    categoryName: isAssetPurchase.value ? 'Asset Purchase' : selectedCategoryName.value,
    categoryCode: isAssetPurchase.value ? '' : selectedCategory.value?.code || '',
    expenseHeadName: isAssetPurchase.value
      ? selectedAssetAccount.value?.account_name || '—'
      : isOperatingCost.value
        ? selectedOperatingLines.value.length > 1
          ? `${selectedOperatingLines.value.length} expense heads`
          : printLineItems.value[0]?.expenseHeadName || '—'
        : selectedHead.value?.name || '—',
    particular: isOperatingCost.value
      ? printLineItems.value.map((item) => item.description).join(', ')
      : form.particular,
    remarks: form.remarks,
    totalAmount: totalBillAmount.value,
    unitAmountNote: previewUnitAmountNote.value,
    demandLetter: selectedDemandLetter.value ? { ...selectedDemandLetter.value } : null,
    applications: [...selectedApplications.value],
    jobName: selectedJobSummary.value?.job_name || '',
    linkedAccounts: buildPrintLinkedAccounts(),
  }

  const billPayload = {
    entry_type: form.entry_type,
    category_id: form.category_id,
    head_id: form.head_id,
    asset_account_id: form.asset_account_id,
    vendor_account_id: form.vendor_account_id,
    amount: form.amount,
    payment_date: form.payment_date,
    payment_method: form.payment_method,
    particular: form.particular,
    reference_no: form.reference_no,
    remarks: form.remarks,
    receipt_path: Array.isArray(form.receipt_path)
      ? form.receipt_path.filter((file) => file instanceof File)
      : undefined,
    linked_account_category: form.linked_account_category,
    linked_account_id: form.linked_account_id,
    requested_by: getRequestedByPayload(),
    status,
  }

  let result
  if (isAssetPurchase.value) {
    result = await paymentStore.payAssetPurchase(billPayload)
  } else if (isDirectCost.value) {
    result = await paymentStore.payExpenseBatch({
      ...billPayload,
      application_ids: form.application_ids,
      job_id: form.job_id,
    })
  } else if (isOperatingCost.value) {
    result = await paymentStore.payExpenseMultiHead({
      ...billPayload,
      lines: selectedOperatingLines.value.map((line) => ({
        head_id: line.head_id,
        amount: line.amount,
        bill_no: String(line.bill_no || '').trim() || undefined,
        linked_account_category: line.linked_account_category || undefined,
        linked_account_id: line.linked_account_id ? Number(line.linked_account_id) : undefined,
      })),
    })
  } else {
    result = await paymentStore.payExpense({
      ...billPayload,
      demand_letter_id: form.demand_letter_id,
    })
  }

  submitLoading.value = false

  if (!result.ok) {
    await Swal.fire({
      icon: 'error',
      title: 'Bills & Purchases Failed',
      text: result.message,
      confirmButtonColor: '#22C55E',
    })
    return
  }

  const lineItems =
    isOperatingCost.value || isDirectCost.value ? buildSubmittedLineItems(result) : []
  const hasCandidateLines = lineItems.some((item) => item.candidateName)

  submittedBill.value = {
    requestNo: resolveSubmittedRequestNo(result),
    billNo: resolveSubmittedBillNo(result),
    preparedByName: authUserName.value,
    billDate: printSnapshot.billDate,
    paymentMethodLabel: printSnapshot.paymentMethodLabel,
    referenceNo: printSnapshot.referenceNo,
    categoryName: printSnapshot.categoryName,
    categoryCode: printSnapshot.categoryCode,
    expenseHeadName: printSnapshot.expenseHeadName,
    particular: printSnapshot.particular,
    remarks: printSnapshot.remarks,
    totalAmount: printSnapshot.totalAmount,
    unitAmountNote: hasCandidateLines ? '' : printSnapshot.unitAmountNote,
    demandLetter: printSnapshot.demandLetter,
    applications: hasCandidateLines ? [] : printSnapshot.applications,
    jobName: printSnapshot.jobName,
    linkedAccounts: (() => {
      const fromResult = buildPrintLinkedAccounts(result)
      return fromResult.length ? fromResult : printSnapshot.linkedAccounts
    })(),
    lineItems,
    billCount: result.count > 1 ? result.count : 1,
    destination: status === 'pending' ? 'bills_to_pay' : 'submitted_bills',
  }

  emit('saved', {
    payment: result.payments?.[0] ?? result.payment,
    snapshot: submittedBill.value,
  })

  resetForm()

  await nextTick()
  scrollBillPageToTop()
}

function scrollBillPageToTop() {
  const scrollParent =
    document.querySelector('main.overflow-y-auto') ||
    document.scrollingElement ||
    document.documentElement

  if (typeof scrollParent.scrollTo === 'function') {
    scrollParent.scrollTo({ top: 0, behavior: 'smooth' })
  } else {
    scrollParent.scrollTop = 0
  }

  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function printSubmittedBill() {
  submittedBillPreviewRef.value?.printBill?.()
}

async function createAnotherBill() {
  submittedBill.value = null
  resetForm()

  if (form.entry_type === 'asset_purchase') {
    await ensureAssetPurchaseAccounts()
  } else if (form.category_id) {
    await expenseCostAccountsStore.ensureAccountsForCategoryId(form.category_id)
    if (form.head_id) {
      await ensureHeadBillContext(form.head_id)
    }
    await autoSelectLinkedAccount()
    applyParticular()
    applyBasePrice(Boolean(form.head_id))
  }

  await nextTick()
  scrollBillPageToTop()
}

onMounted(async () => {
  await Promise.all([
    categoryStore.fetchCategories(true),
    headStore.fetchHeads({ force: true, page: 1, perPage: 300 }),
  ])

  if (form.category_id) {
    await expenseCostAccountsStore.ensureAccountsForCategoryId(form.category_id)
  }

  if (form.head_id) {
    await ensureHeadBillContext(form.head_id)
    await autoSelectLinkedAccount()
    applyParticular()
    applyBasePrice()
  }
})

watch(
  () => form.entry_type,
  async (entryType) => {
    if (entryType === 'asset_purchase') {
      form.category_id = ''
      form.head_id = ''
      form.application_ids = []
      form.job_id = ''
      form.demand_letter_id = ''
      form.operating_lines = []
      form.linked_account_category = ''
      form.linked_account_id = ''
      form.vendor_account_id = ''
      await ensureAssetPurchaseAccounts()
      return
    }

    form.asset_account_id = ''
    form.vendor_account_id = ''
  }
)

watch(
  () => form.category_id,
  async (categoryId) => {
    if (!categoryId) return
    await expenseCostAccountsStore.ensureAccountsForCategoryId(categoryId)
  }
)
</script>
