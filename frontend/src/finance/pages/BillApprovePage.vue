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
          Back to {{ isPayableSettlementMode ? 'Bills Payable' : 'Bills To Pay' }}
        </button>
        <PageTitle>{{ pageTitle }}</PageTitle>
      </div>

      <span
        v-if="entry"
        class="rounded-full px-3 py-1 text-xs font-semibold capitalize"
        :class="statusBadgeClass"
      >
        {{ entry.status?.replace('_', ' ') || '—' }}
      </span>
    </PageHeader>

    <div v-if="pageLoading" class="rounded-xl border border-slate-200 bg-white p-10 text-center text-sm text-slate-500">
      Loading bill details...
    </div>

    <div
      v-else-if="!entry"
      class="rounded-xl border border-red-100 bg-red-50 p-8 text-center text-sm text-red-700"
    >
      Bill entry was not found.
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

    <BaseForm v-else :onSubmit="handleApprove" class-name="!space-y-0">
      <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
        <!-- Left: read-only bill summary -->
        <aside class="space-y-3 xl:col-span-5">
          <section
            class="overflow-hidden rounded-xl border border-emerald-100 bg-white shadow-sm"
          >
            <div
              class="bg-gradient-to-br from-[#0d5c4d] via-[#0f766e] to-[#134e4a] px-4 py-4 text-white"
            >
              <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-100/80">
                {{ isBatchPayMode ? 'Batch Total' : 'Bill Amount' }}
              </p>
              <p class="mt-0.5 text-2xl font-bold tabular-nums tracking-tight">
                {{ formatCurrency(displayAmount) }}
              </p>
              <p v-if="isBatchPayMode" class="mt-1 text-xs font-medium text-emerald-100/85">
                {{ batchEntries.length }} bills · {{ batchLabel }}
              </p>
              <div class="mt-2 flex flex-wrap gap-1.5">
                <span
                  class="rounded-full bg-white/15 px-2.5 py-0.5 text-[11px] font-medium text-white/95"
                >
                  {{ form.category_name || 'Expense' }}
                </span>
                <span
                  v-if="!isBatchPayMode && form.head_name"
                  class="rounded-full bg-white/15 px-2.5 py-0.5 text-[11px] font-medium text-white/95"
                >
                  {{ form.head_name }}
                </span>
                <span
                  v-else-if="isBatchPayMode"
                  class="rounded-full bg-white/15 px-2.5 py-0.5 text-[11px] font-medium text-white/95"
                >
                  {{ batchEntries.length }} expense heads
                </span>
              </div>
            </div>

            <div
              v-if="isBatchPayMode"
              class="max-h-52 overflow-y-auto border-b border-slate-100"
            >
              <div
                v-for="item in batchEntries"
                :key="item.id"
                class="flex items-start justify-between gap-3 border-b border-slate-50 px-4 py-2.5 last:border-b-0"
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

            <div class="divide-y divide-slate-100 px-4">
              <div class="grid grid-cols-2 gap-3 py-3">
                <div>
                  <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                    Bill Date
                  </p>
                  <p class="mt-0.5 text-sm font-semibold text-slate-900">
                    {{ formatDisplayDate(form.payment_date) }}
                  </p>
                </div>
                <div>
                  <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                    Payment Method
                  </p>
                  <p class="mt-0.5 text-sm font-semibold capitalize text-slate-900">
                    {{ submittedPaymentMethodLabel }}
                  </p>
                </div>
              </div>

              <div class="py-3">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                  Particular
                </p>
                <p class="mt-0.5 text-sm leading-snug text-slate-800">
                  {{ form.particular || '—' }}
                </p>
              </div>

              <div class="grid grid-cols-2 gap-3 py-3">
                <div>
                  <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                    Request / Bill No
                  </p>
                  <p class="mt-0.5 text-sm font-semibold text-slate-900">
                    {{ entry.request_no || form.voucher_no || '—' }}
                  </p>
                </div>
                <div>
                  <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                    Reference
                  </p>
                  <p class="mt-0.5 text-sm font-semibold text-slate-900">
                    {{ form.reference_no || '—' }}
                  </p>
                </div>
              </div>

              <div v-if="form.remarks" class="py-3">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                  Entry Remarks
                </p>
                <p class="mt-0.5 text-sm leading-snug text-slate-700">{{ form.remarks }}</p>
              </div>

              <div v-if="form.vendor_account_name" class="grid grid-cols-2 gap-3 py-3">
                <div>
                  <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                    Vendor Account
                  </p>
                  <p class="mt-0.5 text-sm font-semibold text-slate-900">
                    {{ form.vendor_account_name }}
                  </p>
                </div>
              </div>

              <div v-if="hasLinkedBillAccountDisplay" class="grid grid-cols-2 gap-3 py-3">
                <div>
                  <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                    Linked Account Type
                  </p>
                  <p class="mt-0.5 text-sm font-semibold text-slate-900">
                    {{ linkedBillAccountTypeLabel || '—' }}
                  </p>
                </div>
                <div>
                  <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                    Linked Account
                  </p>
                  <p class="mt-0.5 text-sm font-semibold text-slate-900">
                    {{ linkedBillAccountName || '—' }}
                  </p>
                </div>
              </div>
            </div>
          </section>

          <section
            v-if="receiptUrls.length"
            class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
          >
            <div class="border-b border-slate-100 px-4 py-2">
              <h3 class="text-sm font-semibold text-slate-900">
                Bill Receipt{{ receiptUrls.length > 1 ? 's' : '' }}
              </h3>
            </div>
            <div class="grid grid-cols-1 gap-2 p-3 sm:grid-cols-2">
              <a
                v-for="(url, index) in receiptUrls"
                :key="`${url}-${index}`"
                :href="url"
                target="_blank"
                rel="noopener noreferrer"
                class="block overflow-hidden rounded-lg border border-slate-200 bg-slate-50"
                title="Open full size"
              >
                <img
                  :src="url"
                  :alt="`Bill receipt ${index + 1}`"
                  class="max-h-40 w-full object-contain"
                />
              </a>
            </div>
          </section>

          <section
            v-if="hasDemandLetterDetails"
            class="rounded-xl border border-violet-100 bg-violet-50/50 p-4 shadow-sm"
          >
            <h3 class="text-sm font-semibold text-slate-900">Demand Letter</h3>
            <dl class="mt-2 space-y-1.5 text-sm">
              <div class="flex justify-between gap-3">
                <dt class="text-slate-500">DL Number</dt>
                <dd class="font-medium text-slate-900">{{ form.demand_letter || '—' }}</dd>
              </div>
              <div class="flex justify-between gap-3">
                <dt class="text-slate-500">Client</dt>
                <dd class="font-medium text-slate-900">{{ form.client_name || '—' }}</dd>
              </div>
              <div class="flex justify-between gap-3">
                <dt class="text-slate-500">Country</dt>
                <dd class="text-slate-800">{{ form.demand_letter_country || '—' }}</dd>
              </div>
            </dl>
          </section>

          <section
            v-if="hasCandidateDetails"
            class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-4 shadow-sm"
          >
            <h3 class="text-sm font-semibold text-slate-900">Candidate / Job</h3>
            <dl class="mt-2 space-y-1.5 text-sm">
              <div class="flex justify-between gap-3">
                <dt class="text-slate-500">Candidate</dt>
                <dd class="font-medium text-slate-900">{{ form.candidate_name || '—' }}</dd>
              </div>
              <div class="flex justify-between gap-3">
                <dt class="text-slate-500">Passport</dt>
                <dd class="font-medium text-slate-900">{{ form.passport_no || '—' }}</dd>
              </div>
              <div class="flex justify-between gap-3">
                <dt class="text-slate-500">Status</dt>
                <dd class="text-slate-800">{{ form.application_status || '—' }}</dd>
              </div>
              <div class="flex justify-between gap-3">
                <dt class="text-slate-500">Job</dt>
                <dd class="font-medium text-slate-900">{{ form.job_name || '—' }}</dd>
              </div>
              <div class="flex justify-between gap-3">
                <dt class="text-slate-500">Job Code</dt>
                <dd class="text-slate-800">{{ form.job_code || '—' }}</dd>
              </div>
            </dl>
          </section>

          <section class="rounded-xl border border-slate-200 bg-slate-50/80 p-4 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-900">Requested By</h3>
            <p class="mt-1 text-sm leading-snug text-slate-700">{{ requestedByParagraph }}</p>
          </section>

          <section
            v-if="showDigitalManagerApproval"
            class="rounded-xl border border-emerald-100 bg-emerald-50/60 p-4 shadow-sm"
          >
            <h3 class="text-sm font-semibold text-slate-900">Approved By</h3>
            <dl class="mt-2 space-y-1.5 text-sm">
              <div class="flex justify-between gap-3">
                <dt class="text-slate-500">Name</dt>
                <dd class="font-semibold text-slate-900">
                  {{ form.manager_approved_by || '—' }}
                </dd>
              </div>
              <div v-if="form.manager_approved_at" class="flex justify-between gap-3">
                <dt class="text-slate-500">Approved At</dt>
                <dd class="text-slate-800">{{ formatDisplayDate(form.manager_approved_at) }}</dd>
              </div>
            </dl>
          </section>
        </aside>

        <!-- Right: payment / writable -->
        <section class="xl:col-span-7">
          <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <h3 class="mb-3 text-base font-semibold text-slate-900">
              {{ isReadonly ? 'Payment Details' : 'Make Payment' }}
            </h3>

            <template v-if="isPayableSettlementMode">
              <div class="mb-3 grid grid-cols-1 gap-2 rounded-lg border border-slate-200 bg-slate-50 p-3 sm:grid-cols-3">
                <div>
                  <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                    Bill Total
                  </p>
                  <p class="mt-0.5 text-sm font-bold tabular-nums text-slate-900">
                    {{ formatCurrency(payableBillTotal) }}
                  </p>
                </div>
                <div>
                  <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                    Already Paid
                  </p>
                  <p class="mt-0.5 text-sm font-bold tabular-nums text-emerald-700">
                    {{ formatCurrency(payablePaidAmount) }}
                  </p>
                </div>
                <div>
                  <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                    Remaining
                  </p>
                  <p class="mt-0.5 text-sm font-bold tabular-nums text-amber-700">
                    {{ formatCurrency(payableRemainingAmount) }}
                  </p>
                </div>
              </div>

              <div class="mb-3 rounded-xl border-2 border-emerald-200 bg-emerald-50/70 p-3">
                <BaseLabel
                  for="pay_settle_amount"
                  :className="'mb-1 block text-xs font-bold uppercase tracking-wide text-emerald-800'"
                >
                  Pay Amount (৳)
                </BaseLabel>
                <BaseInput
                  id="pay_settle_amount"
                  v-model="form.pay_amount"
                  type="number"
                  min="0"
                  :max="payableRemainingAmount"
                  step="0.01"
                  :required="true"
                  :className="'w-full rounded-lg border-2 border-emerald-300 bg-white px-3 py-2.5 text-2xl font-bold tabular-nums text-emerald-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30'"
                />
                <p v-if="payAmountExceedsRemaining" class="mt-2 text-xs text-red-600">
                  Payment cannot exceed remaining balance of {{ formatCurrency(payableRemainingAmount) }}.
                </p>
              </div>

              <div class="mb-3 grid grid-cols-1 gap-2.5 md:grid-cols-2">
                <div class="space-y-1 md:col-span-2">
                  <BaseLabel for="settle_payment_method">Payment Method</BaseLabel>
                  <BaseSelect
                    id="settle_payment_method"
                    v-model="form.payment_method"
                    :options="settlementPaymentMethodOptions"
                    placeholder="Select payment method"
                    :required="true"
                  />
                </div>

                <div
                  v-if="isIncomeLinkMethod"
                  class="space-y-1 md:col-span-2"
                >
                  <BaseLabel>Linked Income Head</BaseLabel>
                  <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5 text-sm text-amber-900">
                    <p class="font-semibold">
                      {{ linkedIncomeHead?.name || 'No linked income head' }}
                    </p>
                    <p class="mt-0.5 text-xs text-amber-700">
                      Settles without cash/bank: CR on this income head ledger, CR on the expense
                      bill ledger.
                    </p>
                  </div>
                </div>
              </div>
            </template>

            <div
              v-else
              class="mb-3 space-y-3"
            >
              <div class="rounded-xl border-2 border-emerald-200 bg-emerald-50/70 p-3">
                <BaseLabel
                  for="pay_amount"
                  :className="'mb-1 block text-xs font-bold uppercase tracking-wide text-emerald-800'"
                >
                  {{ isBatchPayMode ? 'Batch Total (৳)' : 'Bill Amount (৳)' }}
                </BaseLabel>
                <BaseInput
                  id="pay_amount"
                  v-model="form.amount"
                  type="number"
                  min="0"
                  step="0.01"
                  :required="true"
                  :disabled="isReadonly || isBatchPayMode"
                  :className="'w-full rounded-lg border-2 border-emerald-300 bg-white px-3 py-2.5 text-2xl font-bold tabular-nums text-emerald-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30'"
                />
                <p v-if="isBatchPayMode" class="mt-1 text-xs text-emerald-800/80">
                  Each bill in the batch is paid at its own amount. Payment method and account apply to all.
                </p>
              </div>

              <template v-if="!isReadonly && !isBatchPayMode">
                <div
                  v-if="showBillsToPayPaymentMethod"
                  class="space-y-1"
                >
                  <BaseLabel for="pay_payment_method">Payment Method</BaseLabel>
                  <BaseSelect
                    id="pay_payment_method"
                    v-model="form.payment_method"
                    :options="billsToPayPaymentMethodOptions"
                    placeholder="Select payment method"
                    :required="true"
                  />
                </div>

                <div class="grid grid-cols-1 gap-2 rounded-lg border border-slate-200 bg-slate-50 p-3 sm:grid-cols-2">
                  <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                      Pay Now (Cash / Bank)
                    </p>
                    <BaseInput
                      id="bills_to_pay_now"
                      v-model="form.pay_now_amount"
                      type="number"
                      min="0"
                      :max="Number(form.amount) || undefined"
                      step="0.01"
                      :required="true"
                      :disabled="isDuePayment"
                      :className="'mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-lg font-bold tabular-nums text-slate-900'"
                    />
                  </div>
                  <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                      Due Remaining
                    </p>
                    <p
                      class="mt-1 rounded-lg border px-3 py-2 text-lg font-bold tabular-nums"
                      :class="
                        billsToPayDueRemaining > 0
                          ? 'border-amber-200 bg-amber-50 text-amber-800'
                          : 'border-emerald-200 bg-emerald-50 text-emerald-800'
                      "
                    >
                      {{ formatCurrency(billsToPayDueRemaining) }}
                    </p>
                    <p class="mt-1 text-[11px] text-slate-500">
                      Due remaining moves to Bills Payable after approval.
                    </p>
                  </div>
                </div>
                <p v-if="payNowExceedsBillAmount" class="text-xs text-red-600">
                  Pay Now cannot exceed the bill amount.
                </p>
              </template>
            </div>

            <p
              v-if="!isPayableSettlementMode && !isReadonly && billsToPayDueRemaining > 0 && billsToPayNowAmount <= 0"
              class="mb-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800"
            >
              Full bill will be Due — no payment account required. It will appear in Bills Payable.
            </p>
            <p
              v-else-if="!isPayableSettlementMode && !isReadonly && billsToPayDueRemaining > 0"
              class="mb-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800"
            >
              Partial payment: {{ formatCurrency(billsToPayNowAmount) }} will hit the selected account;
              {{ formatCurrency(billsToPayDueRemaining) }} Due will move to Bills Payable.
            </p>
            <p
              v-else-if="isDuePayment"
              class="mb-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800"
            >
              Due payment method — no payment account required.
            </p>

            <div v-if="showPaymentAccountFields" class="mb-3 grid grid-cols-1 gap-2.5 md:grid-cols-2">
              <div class="space-y-1 md:col-span-2">
                <BaseLabel for="pay_account_category">Account Category</BaseLabel>
                <BaseSelect
                  v-if="!isReadonly"
                  id="pay_account_category"
                  v-model="form.payment_account_category"
                  :options="accountCategoryOptions"
                  placeholder="Select account category"
                  :required="true"
                />
                <p
                  v-else
                  class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-800"
                >
                  {{ accountCategoryLabel || '—' }}
                </p>
              </div>

              <div class="space-y-1 md:col-span-2">
                <BaseLabel for="pay_payment_account_id">Payment Account</BaseLabel>
                <BaseSelect
                  v-if="!isReadonly"
                  id="pay_payment_account_id"
                  v-model="form.payment_account_id"
                  :options="paymentAccountOptions"
                  placeholder="Select account"
                  :required="true"
                  :disabled="!canSelectPaymentAccount"
                />
                <p
                  v-else
                  class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-800"
                >
                  {{ selectedPaymentAccountLabel }}
                </p>
              </div>
            </div>

            <div
              v-if="isManualRequestBill"
              class="mb-3 rounded-xl border border-violet-200 bg-violet-50/60 p-3"
            >
              <div class="flex items-center gap-2">
                <i class="fa fa-check-circle text-violet-500"></i>
                <h4 class="text-sm font-semibold text-violet-900">Manual Bill Approval</h4>
              </div>
              <p class="mb-2.5 mt-1 text-xs leading-snug text-violet-800/80">
                This bill was submitted as a manual request and skipped the manager approval screen.
                Record the manager who approved it and attach the approval document.
              </p>

              <div class="grid grid-cols-1 gap-2.5 md:grid-cols-2">
                <div class="space-y-1">
                  <BaseLabel for="manual_approval_manager">Approval Manager</BaseLabel>
                  <BaseSelect
                    v-if="!isReadonly"
                    id="manual_approval_manager"
                    v-model="form.manual_approval_manager_id"
                    :options="approvalManagerOptions"
                    placeholder="Select approval manager"
                    :required="true"
                  />
                  <p
                    v-else
                    class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-800"
                  >
                    {{ form.manual_approval_manager_name || '—' }}
                  </p>
                  <p
                    v-if="!isReadonly && !approvalManagerOptions.length"
                    class="text-[11px] text-red-600"
                  >
                    No approval manager found. Enable Manager Approval on an employee first.
                  </p>
                </div>

                <div class="space-y-1">
                  <BaseLabel for="manual_approval_file">Approval Document</BaseLabel>
                  <BaseFileInput
                    v-if="!isReadonly"
                    id="manual_approval_file"
                    accept=".jpg,.jpeg,.png,.pdf"
                    :fileName="manualApprovalFileName"
                    @change="handleManualApprovalFileChange"
                  />
                  <p v-if="!isReadonly" class="text-[11px] text-slate-500">
                    JPG, PNG or PDF · max 4 MB
                  </p>
                  <p v-if="manualApprovalFileError" class="text-[11px] text-red-600">
                    {{ manualApprovalFileError }}
                  </p>
                  <a
                    v-if="form.manual_approval_url"
                    :href="form.manual_approval_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1.5 text-xs font-medium text-violet-700 hover:underline"
                  >
                    <i class="fa fa-paperclip"></i> View uploaded approval
                  </a>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 gap-2.5 md:grid-cols-2">
              <div class="space-y-1">
                <BaseLabel for="pay_voucher_no">Bill No</BaseLabel>
                <BaseInput
                  id="pay_voucher_no"
                  v-model="form.voucher_no"
                  placeholder="Bill number"
                  :disabled="isReadonly"
                />
              </div>

              <div class="space-y-1">
                <BaseLabel for="pay_reference_no">Reference No</BaseLabel>
                <BaseInput
                  id="pay_reference_no"
                  v-model="form.reference_no"
                  placeholder="Reference number"
                  :disabled="isReadonly"
                />
              </div>

              <div class="space-y-1 md:col-span-2">
                <BaseLabel for="pay_particular">Particular</BaseLabel>
                <BaseInput
                  id="pay_particular"
                  v-model="form.particular"
                  placeholder="Bill particular"
                  :disabled="isReadonly"
                />
              </div>

              <div class="space-y-1 md:col-span-2">
                <BaseLabel for="pay_remarks">Entry Remarks</BaseLabel>
                <BaseInput
                  id="pay_remarks"
                  v-model="form.remarks"
                  placeholder="Original entry remarks"
                  :disabled="isReadonly"
                />
              </div>
            </div>

            <div class="mt-2.5 space-y-1">
              <BaseLabel for="pay_approval_remarks">Accountant Remarks</BaseLabel>
              <BaseInput
                id="pay_approval_remarks"
                v-model="form.approval_remarks"
                placeholder="Verification notes or approval comments"
                :disabled="isReadonly"
              />
            </div>

            <p v-if="errorMessage" class="mt-2 text-sm text-red-600">{{ errorMessage }}</p>

            <div class="mt-3 flex flex-wrap items-center gap-2">
              <template v-if="!isReadonly">
                <BaseButton
                v-can="'receive_payment.create'"
                  type="submit"
                  class="cursor-pointer rounded-lg bg-emerald-600 px-4 py-2 text-white shadow-sm hover:bg-emerald-700"
                  :disabled="loading || payAmountExceedsRemaining || payNowExceedsBillAmount || (showPaymentAccountFields && insufficientPaymentBalance)"
                >
                  {{
                    loading
                      ? isPayableSettlementMode
                        ? 'Paying...'
                        : isBatchPayMode
                          ? 'Paying Batch...'
                          : 'Approving...'
                      : isPayableSettlementMode
                        ? isIncomeLinkMethod
                          ? 'Settle via Income Link'
                          : 'Confirm Payment'
                        : isBatchPayMode
                          ? `Pay Batch (${batchEntries.length})`
                          : billsToPayDueRemaining > 0 && billsToPayNowAmount > 0
                            ? 'Pay Partial & Move Due'
                            : billsToPayNowAmount <= 0
                              ? 'Approve as Due'
                              : 'Pay Bill'
                  }}
                </BaseButton>
                <BaseButton
                v-can="'receive_payment.create'"
                  v-if="!isPayableSettlementMode && !isBatchPayMode"
                  type="button"
                  :className="'cursor-pointer rounded-lg bg-red-600 px-4 py-2 text-white shadow-sm hover:bg-red-700'"
                  :disabled="loading"
                  @click="handleReject"
                >
                  Reject Bill
                </BaseButton>
              </template>
              <BaseButton
                v-can="'receive_payment.create'"
                type="button"
                :className="'cursor-pointer rounded-lg border border-slate-300 bg-white px-4 py-2 text-slate-700 hover:bg-slate-50'"
                @click="goBack"
              >
                {{ isReadonly ? 'Back' : 'Cancel' }}
              </BaseButton>
            </div>
          </div>
        </section>
      </div>
    </BaseForm>
  </SectionHeader>
</template>

<script setup>
import { computed, nextTick, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import BaseForm from '@/shared/components/base/BaseForm.vue'
import { useExpensePaymentStore } from '@/finance/store/expensePaymentStore'
import { useFinanceAccountStore } from '@/finance/store/financeAccountStore'
import { useIncomeHeadStore } from '@/finance/store/incomeHeadStore'
import { ACCOUNT_CATEGORIES } from '@/finance/data/accountCategoryCodes'
import {
  getLinkedBillAccountName,
  getLinkedBillAccountTypeLabel,
  hasLinkedBillAccount,
} from '@/finance/utils/linkedAccountOptions'
import { formatRequestedByParagraph } from '@/finance/data/expensePaymentData'
import {
  billAccountCategoryOptions,
  getBillAccountCategoryLabel,
  isDuePaymentMethod,
} from '@/finance/data/billApprovalAccountData'
import { paymentMethods } from '@/finance/data/paymentData'
import { formatCurrency, getBillBatchKey, getBillBatchLabel } from '@/finance/utils/billUtils'
import { isPayableBill, getPayableRemainingAmount } from '@/finance/utils/payableBillUtils'
import { fetchApprovalManagers } from '@/modules/employee/services/employeeService'
import Swal from 'sweetalert2'

const route = useRoute()
const router = useRouter()

const paymentStore = useExpensePaymentStore()
const financeAccountStore = useFinanceAccountStore()
const incomeHeadStore = useIncomeHeadStore()

const pageLoading = ref(true)
const loading = ref(false)
const errorMessage = ref('')
const entry = ref(null)
const batchEntries = ref([])
const originalParticular = ref('')

const form = reactive({
  id: '',
  payment_date: '',
  category_id: '',
  category_name: '',
  head_id: '',
  head_name: '',
  vendor_account_name: '',
  amount: '',
  pay_amount: '',
  pay_now_amount: '',
  paid_amount: '',
  payment_method: 'cash',
  particular: '',
  reference_no: '',
  voucher_no: '',
  remarks: '',
  approval_remarks: '',
  receipt_url: '',
  receipt_urls: [],
  candidate_name: '',
  passport_no: '',
  application_status: '',
  job_name: '',
  job_code: '',
  client_name: '',
  demand_letter_id: null,
  demand_letter: '',
  demand_letter_country: '',
  requested_by_id: null,
  requested_by_name: '',
  requested_by_email: '',
  requested_by_type: '',
  requested_at: '',
  created_at: '',
  payment_account_category: '',
  main_account_type: '',
  payment_account_type: '',
  payment_account_id: '',
  payment_account_name: '',
  expense_cost_type: '',
  expense_cost_account_id: '',
  expense_cost_account_name: '',
  expense_cost_category_name: '',
  linked_account_category: '',
  linked_account_id: '',
  linked_account_name: '',
  linked_account_type: '',
  manager_approved_by: '',
  manager_approved_at: '',
  manual_approval_manager_id: '',
  manual_approval_manager_name: '',
  manual_approval_url: '',
  manual_approval_path: null,
})

const approvalManagers = ref([])
const manualApprovalFileError = ref('')
const isHydratingApprovalForm = ref(false)

const MAX_MANUAL_APPROVAL_BYTES = 4 * 1024 * 1024

const accountCategoryOptions = billAccountCategoryOptions

const linkedIncomeHead = computed(() => incomeHeadStore.getBillsPayableLinkedHead())

const isIncomeLinkMethod = computed(
  () => String(form.payment_method || '').toLowerCase() === 'income_link'
)

const settlementPaymentMethodOptions = computed(() => {
  const options = paymentMethods.filter((method) => ['cash', 'bank'].includes(method.id))

  if (linkedIncomeHead.value) {
    options.push({
      id: 'income_link',
      name: `Income Link (${linkedIncomeHead.value.name})`,
    })
  }

  return options
})

const billsToPayPaymentMethodOptions = computed(() =>
  paymentMethods.filter((method) => ['cash', 'bank', 'due'].includes(method.id))
)

const billsToPayBillTotal = computed(() => Math.max(Number(form.amount) || 0, 0))
const billsToPayNowAmount = computed(() => {
  const value = Number(form.pay_now_amount)
  if (Number.isNaN(value)) return 0
  return Math.max(value, 0)
})
const billsToPayDueRemaining = computed(() =>
  Math.max(Math.round((billsToPayBillTotal.value - billsToPayNowAmount.value) * 100) / 100, 0)
)
const payNowExceedsBillAmount = computed(() => {
  if (isPayableSettlementMode.value || isBatchPayMode.value) return false
  return billsToPayNowAmount.value > billsToPayBillTotal.value + 0.0001
})
const showBillsToPayPaymentMethod = computed(
  () => !isBatchPayMode.value && !isPayableSettlementMode.value
)

const isPayableSettlementMode = computed(() => route.name === 'Bill Payable Payment')

const isBatchPayMode = computed(
  () => !isPayableSettlementMode.value && batchEntries.value.length > 1
)

const batchLabel = computed(() => getBillBatchLabel(entry.value || batchEntries.value[0] || {}))

const isReadonly = computed(() => {
  if (isPayableSettlementMode.value && isPayableBill(entry.value)) return false
  return entry.value?.status === 'approved' || entry.value?.status === 'rejected'
})

const pageTitle = computed(() => {
  if (isPayableSettlementMode.value) return 'Pay Bill Payable'
  if (isBatchPayMode.value) return 'Review & Pay Batch'
  if (entry.value?.status === 'approved') return 'Approved Bill'
  if (entry.value?.status === 'rejected') return 'Rejected Bill'
  return 'Review & Pay Bill'
})

const statusBadgeClass = computed(() => {
  const status = entry.value?.status
  if (status === 'pending') return 'bg-amber-50 text-amber-700'
  if (status === 'approved') return 'bg-green-100 text-green-700'
  if (status === 'rejected') return 'bg-red-100 text-red-700'
  return 'bg-slate-100 text-slate-600'
})

const displayAmount = computed(() => {
  if (isPayableSettlementMode.value) {
    return payableBillTotal.value
  }

  if (isBatchPayMode.value) {
    return batchEntries.value.reduce((sum, item) => sum + (Number(item.amount) || 0), 0)
  }

  return Number(form.amount) || Number(entry.value?.amount) || 0
})

const payableBillTotal = computed(() => Number(form.amount) || Number(entry.value?.amount) || 0)
const payablePaidAmount = computed(() => Number(form.paid_amount) || 0)
const payableRemainingAmount = computed(() => getPayableRemainingAmount(form))
const payAmountExceedsRemaining = computed(() => {
  if (!isPayableSettlementMode.value) return false
  const payAmount = Number(form.pay_amount) || 0
  return payAmount > payableRemainingAmount.value
})

const accountCategoryLabel = computed(() =>
  getBillAccountCategoryLabel(form.payment_account_category)
)

const canSelectPaymentAccount = computed(() => Boolean(form.payment_account_category))

function mapPaymentAccountOption(account) {
  const balance = formatCurrency(account.balance ?? account.current_balance ?? 0)

  if (account.category === ACCOUNT_CATEGORIES.MAIN) {
    return {
      id: account.id,
      name: `${account.account_type} — ${account.account_name} (${account.account_label}) — ${balance}`,
    }
  }

  if (account.category === ACCOUNT_CATEGORIES.STAFF) {
    return {
      id: account.id,
      name: `Staff — ${account.staff_code} — ${account.staff_name} — ${balance}`,
    }
  }

  return {
    id: account.id,
    name: `${account.account_name} — ${balance}`,
  }
}

function getFinanceAccount(accountId) {
  return financeAccountStore.getAccount(accountId)
}

function getFinanceAccountBalance(accountId) {
  const account = getFinanceAccount(accountId)
  if (!account) return null
  return Number(account.balance ?? account.current_balance ?? 0)
}

const paymentAccountOptions = computed(() => {
  if (form.payment_account_category === 'main') {
    const mainType = String(form.main_account_type || '').trim()

    return financeAccountStore
      .getAccountsByCategory(ACCOUNT_CATEGORIES.MAIN)
      .filter((account) => account.status === 'Active')
      .filter((account) => {
        if (!mainType) return true
        return String(account.account_type || '').toLowerCase() === mainType.toLowerCase()
      })
      .map(mapPaymentAccountOption)
  }

  if (form.payment_account_category === 'staff') {
    return financeAccountStore
      .getAccountsByCategory(ACCOUNT_CATEGORIES.STAFF)
      .filter((account) => account.status === 'Active')
      .map(mapPaymentAccountOption)
  }

  return []
})

const selectedPaymentAccountLabel = computed(() => {
  if (form.payment_account_name) {
    return `${form.payment_account_type ? `${form.payment_account_type} — ` : ''}${
      form.payment_account_name
    }`
  }

  const selected = paymentAccountOptions.value.find(
    (option) => Number(option.id) === Number(form.payment_account_id)
  )

  return selected?.name ?? '—'
})

const selectedPaymentAccountBalance = computed(() => {
  if (!form.payment_account_id || !form.payment_account_category) return null
  return getFinanceAccountBalance(form.payment_account_id)
})

const billAmount = computed(() => {
  if (isPayableSettlementMode.value) {
    return Number(form.pay_amount) || 0
  }

  if (isBatchPayMode.value) {
    return batchEntries.value.reduce((sum, item) => sum + (Number(item.amount) || 0), 0)
  }

  // Bills To Pay: balance check uses the cash/bank portion being paid now.
  return billsToPayNowAmount.value
})
const linkedBillAccountTypeLabel = computed(() => getLinkedBillAccountTypeLabel(form))
const linkedBillAccountName = computed(() => getLinkedBillAccountName(form))
const hasLinkedBillAccountDisplay = computed(() => hasLinkedBillAccount(form))

const paymentBalanceAfter = computed(() => {
  if (selectedPaymentAccountBalance.value === null) return null
  return selectedPaymentAccountBalance.value - billAmount.value
})

const insufficientPaymentBalance = computed(() => {
  if (!form.payment_account_category || !form.payment_account_id) return false
  if (paymentBalanceAfter.value === null) return false
  return paymentBalanceAfter.value < 0
})

const requestedByParagraph = computed(() => formatRequestedByParagraph(form))

const paymentMethodLabel = computed(() => {
  const method = settlementPaymentMethodOptions.value.find(
    (item) => item.id === form.payment_method
  )
  if (method) return method.name
  const fallback = paymentMethods.find((item) => item.id === form.payment_method)
  return fallback?.name ?? form.payment_method
})

const submittedPaymentMethodLabel = computed(() => {
  const method = String(entry.value?.payment_method || '').toLowerCase()
  if (!method) return '—'
  const option = paymentMethods.find((item) => item.id === method)
  return option?.name ?? method
})

function resolveMainAccountTypeFromPaymentMethod(method) {
  const key = String(method || '').toLowerCase()
  if (key === 'bank') return 'Bank'
  if (key === 'cash') return 'Cash'
  return ''
}

const isDuePayment = computed(() => {
  if (isPayableSettlementMode.value) return false
  if (!isBatchPayMode.value) {
    return billsToPayNowAmount.value <= 0 || isDuePaymentMethod(form.payment_method)
  }
  return isDuePaymentMethod(form.payment_method)
})

const showPaymentAccountFields = computed(() => {
  if (isPayableSettlementMode.value && isIncomeLinkMethod.value) return false
  if (isPayableSettlementMode.value) return true
  if (isBatchPayMode.value) return !isDuePayment.value
  return billsToPayNowAmount.value > 0
})

const isManualRequestBill = computed(() => {
  if (isPayableSettlementMode.value) return false
  if (isBatchPayMode.value) {
    return batchEntries.value.some((item) => Boolean(item.is_manual_request))
  }
  return Boolean(entry.value?.is_manual_request)
})

const showDigitalManagerApproval = computed(
  () =>
    !isPayableSettlementMode.value &&
    !isManualRequestBill.value &&
    Boolean(form.manager_approved_by || form.manager_approved_at)
)

const approvalManagerOptions = computed(() =>
  approvalManagers.value.map((manager) => ({
    id: manager.id,
    name: manager.designation ? `${manager.name} — ${manager.designation}` : manager.name,
  }))
)

const manualApprovalFileName = computed(() => form.manual_approval_path?.name ?? '')

function handleManualApprovalFileChange(event) {
  const file = event.target.files?.[0] ?? null
  event.target.value = null

  if (!file) return

  if (file.size > MAX_MANUAL_APPROVAL_BYTES) {
    manualApprovalFileError.value = 'Approval document must be under 4 MB.'
    return
  }

  form.manual_approval_path = file
  manualApprovalFileError.value = ''
}

async function loadApprovalManagers() {
  const { data, error } = await fetchApprovalManagers()
  if (error) {
    approvalManagers.value = []
    return
  }

  approvalManagers.value = Array.isArray(data?.data) ? data.data : []
}

const receiptUrls = computed(() => {
  if (Array.isArray(form.receipt_urls) && form.receipt_urls.length) {
    return form.receipt_urls.filter(Boolean)
  }
  return form.receipt_url ? [form.receipt_url] : []
})

const hasCandidateDetails = computed(
  () =>
    !isBatchPayMode.value &&
    Boolean(form.candidate_name || form.passport_no || form.job_name)
)

const hasDemandLetterDetails = computed(() => Boolean(form.demand_letter))

function formatDisplayDate(value) {
  if (!value) return '—'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value
  return date.toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })
}

function populateForm(bill) {
  if (!bill) return

  const submittedMethod = String(bill.payment_method || 'cash').toLowerCase()
  const submittedMainType =
    bill.payment_account_category === 'main'
      ? bill.payment_account_type || resolveMainAccountTypeFromPaymentMethod(submittedMethod)
      : resolveMainAccountTypeFromPaymentMethod(submittedMethod)

  Object.assign(form, {
    id: bill.id,
    payment_date: bill.payment_date || '',
    category_id: String(bill.category_id || ''),
    category_name: bill.category_name || '',
    head_id: String(bill.head_id || ''),
    head_name: bill.head_name || '',
    vendor_account_name: bill.vendor_account_name || '',
    amount: bill.amount ?? '',
    pay_amount: '',
    pay_now_amount:
      route.name === 'Bill Payable Payment'
        ? ''
        : isDuePaymentMethod(submittedMethod)
          ? 0
          : bill.amount ?? '',
    paid_amount: bill.paid_amount ?? 0,
    payment_method: submittedMethod,
    particular: bill.particular || '',
    reference_no: bill.reference_no || '',
    voucher_no: bill.voucher_no || '',
    remarks: bill.remarks || '',
    approval_remarks: bill.approval_remarks || '',
    receipt_url: bill.receipt_url || '',
    receipt_urls: Array.isArray(bill.receipt_urls)
      ? bill.receipt_urls
      : bill.receipt_url
        ? [bill.receipt_url]
        : [],
    candidate_name: bill.candidate_name || '',
    passport_no: bill.passport_no || '',
    application_status: bill.application_status || '',
    job_name: bill.job_name || '',
    job_code: bill.job_code || '',
    client_name: bill.client_name || '',
    demand_letter_id: bill.demand_letter_id ?? null,
    demand_letter: bill.demand_letter || '',
    demand_letter_country: bill.demand_letter_country || '',
    requested_by_id: bill.requested_by_id ?? null,
    requested_by_name: bill.requested_by_name || '',
    requested_by_email: bill.requested_by_email || '',
    requested_by_type: bill.requested_by_type || '',
    requested_at: bill.requested_at || '',
    created_at: bill.created_at || '',
    payment_account_category: bill.payment_account_category || '',
    main_account_type:
      bill.payment_account_category === 'main'
        ? submittedMainType
        : submittedMainType,
    payment_account_type: bill.payment_account_type || '',
    payment_account_id: bill.payment_account_id ? String(bill.payment_account_id) : '',
    payment_account_name: bill.payment_account_name || '',
    expense_cost_type: bill.expense_cost_type || '',
    expense_cost_account_id: bill.expense_cost_account_id ?? '',
    expense_cost_account_name: bill.expense_cost_account_name || '',
    expense_cost_category_name: bill.expense_cost_category_name || '',
    linked_account_category: bill.linked_account_category || '',
    linked_account_id: bill.linked_account_id ? String(bill.linked_account_id) : '',
    linked_account_name: bill.linked_account_name || '',
    linked_account_type: bill.linked_account_type || '',
    manager_approved_by: bill.manager_approved_by || '',
    manager_approved_at: bill.manager_approved_at || '',
    manual_approval_manager_id: bill.manual_approval_manager_id
      ? String(bill.manual_approval_manager_id)
      : '',
    manual_approval_manager_name: bill.manual_approval_manager_name || '',
    manual_approval_url: bill.manual_approval_url || '',
    manual_approval_path: null,
  })

  manualApprovalFileError.value = ''
  originalParticular.value = bill.particular || ''

  if (
    !bill.payment_account_category &&
    bill.status === 'pending' &&
    !form.payment_account_category &&
    !isDuePaymentMethod(submittedMethod)
  ) {
    form.payment_account_category = 'main'
    if (!form.main_account_type) {
      form.main_account_type = submittedMainType
    }
  }

  if (route.name === 'Bill Payable Payment' && bill.payment_method === 'due') {
    form.payment_method = 'cash'
    form.payment_account_category = form.payment_account_category || 'main'
    form.main_account_type = ''
    form.pay_amount = String(getPayableRemainingAmount(bill) || '')
  }
}

function goBack() {
  if (isPayableSettlementMode.value) {
    router.push({
      path: '/finance/payment-received',
      query: { tab: 'transaction_entry', payment_mode: 'bills_payable' },
    })
    return
  }

  router.push({
    path: '/finance/payment-received',
    query: { tab: 'transaction_entry', payment_mode: 'bills_to_pay' },
  })
}

const buildPayload = () => ({
  ...form,
  // Bills To Pay single: pay_now_amount = cash/bank portion (remainder → Due / Bills Payable).
  // Batch: omit pay_amount so each bill is paid in full at its own amount.
  ...(isPayableSettlementMode.value
    ? { pay_amount: form.pay_amount }
    : isBatchPayMode.value
      ? {}
      : { pay_amount: form.pay_now_amount }),
  payment_method:
    !isPayableSettlementMode.value &&
    !isBatchPayMode.value &&
    (billsToPayNowAmount.value <= 0 || isDuePaymentMethod(form.payment_method))
      ? 'due'
      : form.payment_method,
  manual_approval_manager_id: isManualRequestBill.value ? form.manual_approval_manager_id : null,
  manual_approval_path: isManualRequestBill.value ? form.manual_approval_path : null,
})

async function handleApprove() {
  if (isReadonly.value) return

  errorMessage.value = ''
  loading.value = true

  let result

  if (isPayableSettlementMode.value) {
    result = await paymentStore.payPayableBillEntry(buildPayload())
  } else if (isBatchPayMode.value) {
    result = await paymentStore.approveBillEntryBatch({
      ...buildPayload(),
      ids: batchEntries.value.map((item) => item.id),
    })
  } else {
    result = await paymentStore.approveBillEntry(buildPayload())
  }

  loading.value = false

  if (!result.ok) {
    errorMessage.value = result.message
    return
  }

  const remainingAfter = isPayableSettlementMode.value
    ? getPayableRemainingAmount(result.entry)
    : !isBatchPayMode.value
      ? Math.max(
          Math.round(
            ((Number(result.entry?.amount) || 0) - (Number(result.entry?.paid_amount) || 0)) * 100
          ) / 100,
          0
        )
      : 0
  const isFullPayment = remainingAfter <= 0

  await Swal.fire({
    icon: 'success',
    title: isPayableSettlementMode.value
      ? 'Payment Recorded'
      : isBatchPayMode.value
        ? 'Batch Paid'
        : isFullPayment
          ? 'Bill Approved'
          : 'Partial Payment Recorded',
    text: isPayableSettlementMode.value
      ? isFullPayment
        ? 'Payable bill has been fully settled and moved to Paid Bills.'
        : `Partial payment recorded. Remaining payable: ${formatCurrency(remainingAfter)}.`
      : isBatchPayMode.value
        ? `${result.entries.length} bills have been verified and paid.`
        : isFullPayment
          ? 'Bill entry has been verified and paid.'
          : `Paid ${formatCurrency(Number(result.entry?.paid_amount) || 0)}. Remaining Due ${formatCurrency(remainingAfter)} moved to Bills Payable.`,
    confirmButtonColor: '#22C55E',
  })

  goBack()
}

async function handleReject() {
  if (isReadonly.value) return

  errorMessage.value = ''
  loading.value = true

  const result = await paymentStore.rejectBillEntry(buildPayload())
  loading.value = false

  if (!result.ok) {
    errorMessage.value = result.message
    return
  }

  await Swal.fire({
    icon: 'info',
    title: 'Bill Rejected',
    text: 'Bill entry has been rejected.',
    confirmButtonColor: '#22C55E',
  })

  router.push({ path: '/finance/rejected-bills' })
}

watch(
  () => form.payment_method,
  (method, previousMethod) => {
    if (isPayableSettlementMode.value) {
      if (String(method || '').toLowerCase() === 'income_link') {
        form.payment_account_category = ''
        form.main_account_type = ''
        form.payment_account_type = ''
        form.payment_account_id = ''
        form.payment_account_name = ''

        const linkedName = linkedIncomeHead.value?.name || 'Income Link'
        const headName = form.head_name || entry.value?.head_name || 'expense head'
        form.particular = `Payable settled via Income Link (${linkedName}) — ${headName}`
        return
      }

      // Restore original particular when leaving Income Link.
      if (String(previousMethod || '').toLowerCase() === 'income_link') {
        form.particular = originalParticular.value || form.particular
      }

      // Bills Payable no longer filters main accounts by Cash/Bank type.
      if (!form.payment_account_category) {
        form.payment_account_category = 'main'
      }
      return
    }

    // Bills To Pay — Cash / Bank / Due for the Pay Now portion.
    if (isDuePaymentMethod(method)) {
      form.pay_now_amount = 0
      form.payment_account_category = ''
      form.main_account_type = ''
      form.payment_account_type = ''
      form.payment_account_id = ''
      form.payment_account_name = ''
      return
    }

    if (!form.payment_account_category && billsToPayNowAmount.value > 0) {
      form.payment_account_category = 'main'
    }

    const methodKey = String(method || '').toLowerCase()
    if (methodKey === 'cash' || methodKey === 'bank') {
      form.main_account_type = methodKey === 'bank' ? 'Bank' : 'Cash'
      if (billsToPayNowAmount.value <= 0) {
        form.pay_now_amount = form.amount || ''
      }
    }
  }
)

watch(
  () => form.pay_now_amount,
  () => {
    if (isPayableSettlementMode.value || isBatchPayMode.value || isReadonly.value) return
    if (isHydratingApprovalForm.value) return

    if (billsToPayNowAmount.value <= 0) {
      form.payment_method = 'due'
      form.payment_account_category = ''
      form.main_account_type = ''
      form.payment_account_type = ''
      form.payment_account_id = ''
      form.payment_account_name = ''
      return
    }

    if (!form.payment_account_category) {
      form.payment_account_category = 'main'
    }

    const methodKey = String(form.payment_method || '').toLowerCase()
    if (methodKey === 'cash' || methodKey === 'bank') {
      form.main_account_type = methodKey === 'bank' ? 'Bank' : 'Cash'
    } else if (!isDuePaymentMethod(form.payment_method)) {
      form.payment_method = 'cash'
      form.main_account_type = 'Cash'
    }
  }
)

watch(
  () => form.amount,
  (amount, previousAmount) => {
    if (isPayableSettlementMode.value || isBatchPayMode.value || isReadonly.value) return

    const next = Number(amount)
    const prev = Number(previousAmount)
    if (Number.isNaN(next) || next < 0) return

    // Keep Pay Now in sync when it still matched the previous full bill amount.
    const payNow = Number(form.pay_now_amount)
    if (
      !Number.isNaN(prev) &&
      Math.abs(payNow - prev) < 0.005
    ) {
      form.pay_now_amount = amount
    }
  }
)

watch(
  () => form.payment_account_category,
  (category, previousCategory) => {
    if (category === previousCategory) return

    form.main_account_type = ''
    form.payment_account_type = category === 'staff' ? 'Staff' : ''
    form.payment_account_id = ''
    form.payment_account_name = ''
  }
)

watch(
  () => form.payment_account_id,
  (accountId) => {
    if (!accountId) {
      form.payment_account_name = ''
      return
    }

    if (form.payment_account_category === 'main') {
      const account = getFinanceAccount(accountId)
      form.payment_account_type = account?.account_type ?? form.main_account_type
      form.main_account_type = account?.account_type ?? ''
      form.payment_account_name = account
        ? `${account.account_name} — ${account.account_label}`
        : ''

      // Keep payment method aligned when the user changes account (not during initial load).
      if (!isHydratingApprovalForm.value && !isIncomeLinkMethod.value && !isDuePayment.value) {
        const accountType = String(account?.account_type || '').toLowerCase()
        if (accountType === 'bank') form.payment_method = 'bank'
        else if (accountType === 'cash') form.payment_method = 'cash'
      }
      return
    }

    if (form.payment_account_category === 'staff') {
      const account = getFinanceAccount(accountId)
      form.payment_account_type = 'Staff'
      form.payment_account_name = account ? `${account.staff_code} — ${account.staff_name}` : ''
    }
  }
)

async function autoSelectFirstPaymentAccount() {
  if (isReadonly.value) return
  if (!showPaymentAccountFields.value) return
  if (form.payment_account_id) return
  if (!canSelectPaymentAccount.value) return

  const options = paymentAccountOptions.value
  if (!options.length) return

  // Select the first active account for the chosen category/type.
  form.payment_account_id = String(options[0].id)
}

watch(
  () => [
    showPaymentAccountFields.value,
    form.payment_account_category,
    form.main_account_type,
    paymentAccountOptions.value.length,
    isReadonly.value,
  ],
  async () => {
    await nextTick()
    await autoSelectFirstPaymentAccount()
  },
  { immediate: true }
)

async function loadPage() {
  pageLoading.value = true
  errorMessage.value = ''

  await Promise.all([
    paymentStore.fetchBillEntries(),
    financeAccountStore.fetchAccounts(ACCOUNT_CATEGORIES.MAIN, true),
    financeAccountStore.fetchAccounts(ACCOUNT_CATEGORIES.STAFF, true),
    incomeHeadStore.fetchHeads(true),
    loadApprovalManagers(),
  ])

  const billId = Number(route.params.id)
  const bill = paymentStore.payments.find((item) => Number(item.id) === billId) || null

  if (route.name === 'Bill Payable Payment' && bill && !isPayableBill(bill)) {
    entry.value = null
    batchEntries.value = []
    pageLoading.value = false
    return
  }

  const queryIds = String(route.query.ids || '')
    .split(',')
    .map((id) => Number(id))
    .filter(Boolean)

  let siblings = []
  if (route.name === 'Bill Payment Review' && bill && queryIds.length > 1) {
    siblings = queryIds
      .map((id) => paymentStore.payments.find((item) => Number(item.id) === id))
      .filter(Boolean)

    const batchKey = getBillBatchKey(bill)
    siblings = siblings.filter(
      (item) => item.status === 'pending' && getBillBatchKey(item) === batchKey
    )

    if (!siblings.some((item) => Number(item.id) === Number(bill.id))) {
      siblings = [bill, ...siblings]
    }
  }

  entry.value = bill
  batchEntries.value = siblings.length > 1 ? siblings : bill ? [bill] : []
  isHydratingApprovalForm.value = true
  populateForm(bill)

  if (batchEntries.value.length > 1) {
    form.amount = String(
      batchEntries.value.reduce((sum, item) => sum + (Number(item.amount) || 0), 0)
    )
  }

  pageLoading.value = false

  await nextTick()
  await autoSelectFirstPaymentAccount()
  isHydratingApprovalForm.value = false
  scrollPageToTop()
}

watch(
  () => [route.params.id, route.query.ids],
  () => {
    loadPage()
  }
)

onMounted(() => {
  loadPage()
})

function scrollPageToTop() {
  const scrollParent =
    document.querySelector('main.overflow-y-auto') ||
    document.scrollingElement ||
    document.documentElement

  if (typeof scrollParent.scrollTo === 'function') {
    scrollParent.scrollTo({ top: 0, behavior: 'auto' })
  } else {
    scrollParent.scrollTop = 0
  }

  window.scrollTo({ top: 0, behavior: 'auto' })
}
</script>
