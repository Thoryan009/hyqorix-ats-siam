<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <PageTitle>{{ t('journals.post_title') }}</PageTitle>
        <p class="mt-1 text-sm text-gray-500">{{ t('journals.post_static_note') }}</p>
      </div>
      <BaseButton
        className="border border-gray-300 bg-white text-gray-700 hover:bg-gray-50"
        @click="goBack"
      >
        {{ t('journals.back_to_list') }}
      </BaseButton>
    </PageHeader>

    <div class="mb-6 flex flex-wrap gap-2">
      <button
        v-for="tab in pageTabs"
        :key="tab.id"
        type="button"
        class="rounded-lg border px-4 py-2 text-sm font-semibold transition-all"
        :class="
          activeTab === tab.id
            ? 'border-primary bg-primary-light! text-primary ring-1 ring-primary'
            : 'border-gray-200 bg-white text-gray-700 hover:border-primary hover:bg-primary-light!'
        "
        @click="activeTab = tab.id"
      >
        {{ tab.label }}
      </button>
    </div>

    <div v-if="activeTab === 'bill_entry'">
    <JournalBillEntrySuccess
      v-if="billEntrySuccess"
      :success="billEntrySuccess"
      @primary="handleBillEntrySuccessPrimary"
      @secondary="handleSuccessCreateNew"
    />

    <template v-else>
    <div class="mb-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
      <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
          <h3 class="text-sm font-semibold text-slate-900">
            {{ t('journals.returned_select_title') }}
          </h3>
          <p class="mt-0.5 text-xs text-slate-500">
            {{ t('journals.returned_select_hint') }}
          </p>
        </div>
        <span
          v-if="returnedOptions.length"
          class="inline-flex rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-700 ring-1 ring-rose-200"
        >
          {{ returnedOptions.length }} {{ t('journals.returned') }}
        </span>
      </div>

      <div class="mt-3 max-w-xl">
        <BaseLabel for="returned_journal">{{ t('journals.select_returned_journal') }}</BaseLabel>
        <BaseSearchSelect
          id="returned_journal"
          v-model="selectedReturnedJournalId"
          class="mt-1.5"
          :options="returnedOptions"
          :placeholder="returnedJournalPlaceholder"
          :disabled="isReturnedLoading || !returnedOptions.length"
          :filter-fn="filterReturnedJournal"
          teleport-dropdown
          list-class-name="max-h-72"
        />
      </div>
    </div>

    <div
      v-if="editingJournalId && form.manager_comment"
      class="mb-4 rounded-xl border border-rose-200 bg-rose-50 p-4 shadow-sm"
    >
      <p class="text-[11px] font-semibold uppercase tracking-wide text-rose-700/80">
        {{ t('journals.manager_return_comment') }}
      </p>
      <p class="mt-1.5 text-sm leading-relaxed text-rose-950">
        {{ form.manager_comment }}
      </p>
      <p class="mt-2 text-xs text-rose-700/80">
        {{ t('journals.manager_return_comment_hint') }}
      </p>
    </div>

    <div class="mb-4 grid grid-cols-2 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4">
      <div>
        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
          {{ t('journals.voucher_no') }}
        </p>
        <p class="mt-1 text-sm font-semibold text-slate-900">{{ form.voucher_no }}</p>
      </div>
      <div>
        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
          {{ t('journals.status') }}
        </p>
        <p class="mt-1">
          <span
            class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1"
            :class="statusBadgeClass"
          >
            {{ form.status }}
          </span>
        </p>
      </div>
      <div>
        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
          {{ t('journals.total_debit') }}
        </p>
        <p class="mt-1 text-sm font-semibold tabular-nums text-slate-900">
          {{ formatAmount(totalDebit) }}
        </p>
      </div>
      <div>
        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
          {{ t('journals.total_credit') }}
        </p>
        <p class="mt-1 text-sm font-semibold tabular-nums text-slate-900">
          {{ formatAmount(totalCredit) }}
        </p>
      </div>
    </div>

    <div class="mb-4 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
      <h3 class="mb-4 text-sm font-semibold text-slate-800">
        {{ t('journals.journal_information') }}
      </h3>

      <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="space-y-1.5">
          <BaseLabel for="voucher_date">{{ t('journals.voucher_date') }}</BaseLabel>
          <BaseInput
            id="voucher_date"
            type="date"
            v-model="form.voucher_date"
            className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary"
          />
        </div>

        <div class="space-y-1.5">
          <BaseLabel for="transaction_type">{{ t('journals.transaction_type') }}</BaseLabel>
          <BaseSearchSelect
            id="transaction_type"
            v-model="form.transaction_type"
            :options="transactionTypeOptions"
            :placeholder="t('journals.select_transaction_type')"
            :filter-fn="filterByNameOrCode"
            teleport-dropdown
            list-class-name="max-h-72"
          />
        </div>

        <div class="space-y-1.5">
          <BaseLabel for="reference_no">{{ t('journals.reference_no') }}</BaseLabel>
          <BaseInput
            id="reference_no"
            v-model="form.reference_no"
            :placeholder="t('journals.reference_no_placeholder')"
            className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary"
          />
        </div>

        <JournalProjectSelect input-id="project_id" v-model="form.project_id" />

        <div class="space-y-1.5">
          <BaseLabel for="party_type">{{ t('journals.party_type') }}</BaseLabel>
          <BaseSearchSelect
            id="party_type"
            v-model="form.party_type"
            :options="partyTypeOptions"
            :placeholder="t('journals.select_party_type')"
            :filter-fn="filterByNameOrCode"
            teleport-dropdown
            list-class-name="max-h-72"
          />
        </div>

        <div class="space-y-1.5">
          <BaseLabel for="party_id">{{ t('journals.party_ledger') }}</BaseLabel>
          <BaseSearchSelect
            id="party_id"
            v-model="form.party_id"
            :options="partyLedgerOptions"
            :placeholder="partyLedgerPlaceholder"
            :disabled="isPartyLedgerLoading"
            :filter-fn="filterByCodeOrName"
          />
        </div>
      </div>
    </div>

    <div class="mb-4 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
      <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
          <h3 class="text-sm font-semibold text-slate-800">{{ t('journals.journal_lines') }}</h3>
          <p class="mt-0.5 text-xs text-slate-500">{{ t('journals.balance_hint') }}</p>
        </div>
        <BaseButton className="bg-slate-800 text-white hover:bg-slate-900" @click="addLine">
          + {{ t('journals.add_line') }}
        </BaseButton>
      </div>

      <div class="overflow-x-auto rounded-md border border-slate-200">
        <table class="min-w-full border-collapse text-sm">
          <thead>
            <tr class="bg-slate-50 text-left text-slate-600">
              <th class="whitespace-nowrap border-b border-slate-200 px-3 py-2.5 font-semibold">#</th>
              <th class="min-w-[220px] border-b border-slate-200 px-3 py-2.5 font-semibold">
                {{ t('journals.account') }}
              </th>
              <th class="min-w-[160px] border-b border-slate-200 px-3 py-2.5 font-semibold">
                {{ t('journals.sub_ledger') }}
              </th>
              <th class="min-w-[140px] border-b border-slate-200 px-3 py-2.5 font-semibold">
                {{ t('journals.cost_revenue_type') }}
              </th>
              <th class="w-36 whitespace-nowrap border-b border-slate-200 px-2 py-2.5 text-right font-semibold">
                {{ t('journals.debit_label') }}
              </th>
              <th class="w-36 whitespace-nowrap border-b border-slate-200 px-2 py-2.5 text-right font-semibold">
                {{ t('journals.credit_label') }}
              </th>
              <th class="whitespace-nowrap border-b border-slate-200 px-3 py-2.5 font-semibold">
                {{ t('journals.action') }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(line, index) in lines" :key="line.id" class="align-middle">
              <td class="border-b border-slate-100 px-3 py-2 text-slate-700">{{ index + 1 }}</td>
              <td class="relative overflow-visible border-b border-slate-100 px-3 py-2">
                <BaseSearchSelect
                  v-model="line.account_id"
                  :options="accountOptions"
                  :placeholder="accountPlaceholder"
                  :disabled="isAccountLoading"
                  :filter-fn="filterByCodeOrName"
                  teleport-dropdown
                  list-class-name="max-h-96"
                />
              </td>
              <td class="border-b border-slate-100 px-3 py-2">
                <BaseInput
                  v-model="line.sub_ledger"
                  :placeholder="t('journals.optional')"
                  :disabled="true"
                  className="w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-500"
                />
              </td>
              <td class="border-b border-slate-100 px-3 py-2">
                <BaseSelect
                  v-model="line.cost_type"
                  :options="costTypeOptions"
                  :placeholder="t('journals.select_cost_type')"
                />
              </td>
              <td class="w-36 border-b border-slate-100 px-2 py-2">
                <BaseInput
                  type="number"
                  v-model="line.debit"
                  className="w-full rounded-md border border-gray-300 px-2 py-2 text-right text-sm tabular-nums focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary"
                />
              </td>
              <td class="w-36 border-b border-slate-100 px-2 py-2">
                <BaseInput
                  type="number"
                  v-model="line.credit"
                  className="w-full rounded-md border border-gray-300 px-2 py-2 text-right text-sm tabular-nums focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary"
                />
              </td>
              <td class="whitespace-nowrap border-b border-slate-100 px-3 py-2">
                <div class="flex flex-nowrap items-center gap-1">
                  <button
                    type="button"
                    class="rounded border border-slate-200 px-2 py-1 text-xs text-slate-600 hover:bg-slate-50"
                    @click="copyLine(index)"
                  >
                    {{ t('journals.copy') }}
                  </button>
                  <button
                    type="button"
                    class="rounded border border-red-200 px-2 py-1 text-xs text-red-600 hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-40"
                    :disabled="lines.length <= 1"
                    @click="deleteLine(index)"
                  >
                    {{ t('journals.delete') }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
          <tfoot>
            <tr class="bg-slate-50 font-semibold text-slate-800">
              <td class="px-3 py-2.5" colspan="4">{{ t('journals.total') }}</td>
              <td class="px-3 py-2.5 text-right tabular-nums">{{ formatAmount(totalDebit) }}</td>
              <td class="px-3 py-2.5 text-right tabular-nums">{{ formatAmount(totalCredit) }}</td>
              <td class="px-3 py-2.5"></td>
            </tr>
            <tr class="text-slate-700">
              <td class="px-3 py-2.5" colspan="4">
                {{ t('journals.difference') }}:
                <span class="font-medium">{{ differenceLabel }}</span>
              </td>
              <td class="px-3 py-2.5" colspan="3">
                <div class="flex justify-end">
                  <span
                    class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold"
                    :class="
                      isBalanced
                        ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200'
                        : 'bg-red-50 text-red-700 ring-1 ring-red-200'
                    "
                  >
                    <span
                      class="h-1.5 w-1.5 rounded-full"
                      :class="isBalanced ? 'bg-emerald-500' : 'bg-red-500'"
                    ></span>
                    {{ isBalanced ? t('journals.balanced') : t('journals.unbalanced') }}
                  </span>
                </div>
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <div class="mb-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
      <div class="grid grid-cols-1 divide-y divide-slate-100 xl:grid-cols-3 xl:divide-x xl:divide-y-0">
        <div class="flex min-h-[240px] flex-col p-5">
          <div class="mb-3 flex items-center gap-2.5">
            <span
              class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 ring-1 ring-indigo-100"
            >
              <i class="fa fa-align-left text-xs"></i>
            </span>
            <div>
              <h3 class="text-sm font-semibold text-slate-900">{{ t('journals.narration') }}</h3>
              <p class="text-xs text-slate-500">{{ t('journals.narration_subtitle') }}</p>
            </div>
          </div>
          <BaseTextArea
            id="narration"
            v-model="form.narration"
            :rows="6"
            className="min-h-[148px] flex-1 w-full rounded-lg border border-slate-200 bg-slate-50/50 px-3 py-2.5 text-sm leading-relaxed text-slate-800 placeholder:text-slate-400 focus:border-indigo-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100"
            :placeholder="t('journals.narration_placeholder')"
          />
          <JournalNarrationHints
            v-model="form.narration"
            :context="narrationHintContext"
            :can-suggest="canSuggestNarrationHints"
          />
        </div>

        <div class="flex min-h-[240px] flex-col p-5">
          <div class="mb-3 flex items-center gap-2.5">
            <span
              class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100"
            >
              <i class="fa fa-paperclip text-xs"></i>
            </span>
            <div>
              <h3 class="text-sm font-semibold text-slate-900">{{ t('journals.receipt_optional') }}</h3>
              <p class="text-xs text-slate-500">{{ t('journals.receipt_hint') }}</p>
            </div>
          </div>
          <div class="flex-1">
            <JournalReceiptUpload
              :receipt-path="form.receipt_path"
              :receipt-preview="form.receipt_preview"
              :existing-urls="form.receipt_urls || []"
              label=""
              hint=""
              @update:receipt-path="form.receipt_path = $event"
              @update:receipt-preview="form.receipt_preview = $event"
            />
          </div>
        </div>

        <div class="flex min-h-[240px] flex-col bg-gradient-to-b from-slate-50/90 to-white p-5">
          <div class="mb-3 flex items-center gap-2.5">
            <span
              class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-violet-50 text-violet-600 ring-1 ring-violet-100"
            >
              <i class="fa fa-list-alt text-xs"></i>
            </span>
            <div>
              <h3 class="text-sm font-semibold text-slate-900">{{ t('journals.posting_preview') }}</h3>
              <p class="text-xs text-slate-500">{{ t('journals.posting_preview_subtitle') }}</p>
            </div>
          </div>

          <div class="flex-1 space-y-2 overflow-y-auto pr-1">
            <p
              v-if="!postingPreviewLines.length"
              class="rounded-lg border border-dashed border-slate-200 bg-white/80 px-3 py-4 text-center text-xs text-slate-500"
            >
              {{ t('journals.posting_preview_empty') }}
            </p>
            <div
              v-for="line in postingPreviewLines"
              :key="line.id"
              class="flex items-start justify-between gap-2 rounded-lg border border-slate-200/80 bg-white px-3 py-2 shadow-sm"
            >
              <p class="min-w-0 flex-1 text-xs leading-relaxed text-slate-700">{{ line.account }}</p>
              <div class="flex shrink-0 items-center gap-1.5">
                <span
                  class="rounded px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide"
                  :class="
                    line.side === 'dr'
                      ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-100'
                      : 'bg-amber-50 text-amber-700 ring-1 ring-amber-100'
                  "
                >
                  {{ line.side === 'dr' ? t('journals.dr') : t('journals.cr') }}
                </span>
                <span class="text-xs font-semibold tabular-nums text-slate-900">
                  {{ formatAmount(line.amount) }}
                </span>
              </div>
            </div>
          </div>

          <div class="mt-4 space-y-1.5 border-t border-slate-200/80 pt-3 text-[11px] text-slate-500">
            <p class="flex items-center justify-between gap-2">
              <span>{{ t('journals.prepared_by') }}</span>
              <span class="font-medium text-slate-700">{{ t('journals.current_user') }}</span>
            </p>
            <p class="flex items-center justify-between gap-2">
              <span>{{ t('journals.approval') }}</span>
              <span class="font-medium text-amber-700">{{ t('journals.manager_required') }}</span>
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
      <p v-if="validationMessage" class="w-full text-sm text-red-600 sm:mr-auto sm:self-center">
        {{ validationMessage }}
      </p>
      <BaseButton
        v-if="editingJournalId"
        className="border border-slate-200 bg-white text-slate-700 hover:bg-slate-50"
        :disabled="isResubmitting"
        @click="clearReturnedSelection"
      >
        {{ t('journals.clear_selection') }}
      </BaseButton>
      <template v-if="editingJournalId">
        <BaseButton
          className="border border-indigo-300 bg-white text-indigo-700 hover:bg-indigo-50"
          :disabled="isResubmitting"
          @click="handleResubmit"
        >
          <span v-if="isResubmitting">{{ t('journals.resubmitting') }}</span>
          <span v-else>{{ t('journals.resubmit_approval') }}</span>
        </BaseButton>
      </template>
      <template v-else>
        <BaseButton
          className="border border-indigo-300 bg-white text-indigo-700 hover:bg-indigo-50"
          :disabled="isSubmitting"
          @click="handleSubmit('pending_approval')"
        >
          <span v-if="isSubmitting && pendingStatus === 'pending_approval'">
            {{ t('journals.submitting') }}
          </span>
          <span v-else>{{ t('journals.submit_approval') }}</span>
        </BaseButton>
        <BaseButton
          className="bg-indigo-600 text-white hover:bg-indigo-700"
          :disabled="isSubmitting"
          @click="handleSubmit('posted')"
        >
          <span v-if="isSubmitting && pendingStatus === 'posted'">
            {{ t('journals.posting') }}
          </span>
          <span v-else>{{ t('journals.post_journal') }}</span>
        </BaseButton>
      </template>
    </div>
    </template>
    </div>

    <div v-else-if="activeTab === 'approval'" class="space-y-5">
    <JournalReturnSuccess
      v-if="returnSuccess"
      :success="returnSuccess"
      @go-to-bill-entry="handleReturnSuccessGoToBillEntry"
      @review-more="handleReturnSuccessReviewMore"
    />

    <JournalApprovalSuccess
      v-else-if="approvalSuccess"
      :success="approvalSuccess"
      @go-to-payment="handleApprovalSuccessGoToPayment"
      @review-more="handleApprovalSuccessReviewMore"
    />

    <template v-else>
      <div class="mx-auto w-full max-w-4xl rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm ring-1 ring-slate-100">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div>
            <h3 class="text-base font-semibold text-slate-900">
              {{ t('journals.approval_select_title') }}
            </h3>
            <p class="mt-1 text-sm text-slate-500">
              {{ t('journals.approval_select_hint') }}
            </p>
          </div>
          <span
            v-if="pendingApprovalOptions.length"
            class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800 ring-1 ring-amber-200"
          >
            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
            {{ pendingApprovalOptions.length }} {{ t('journals.waiting_for_approval') }}
          </span>
        </div>

        <div class="mt-4">
          <BaseLabel for="pending_journal">{{ t('journals.select_pending_journal') }}</BaseLabel>
          <BaseSearchSelect
            id="pending_journal"
            v-model="selectedApprovalJournalId"
            class="mt-1.5"
            :options="pendingApprovalOptions"
            :placeholder="pendingJournalPlaceholder"
            :disabled="isPendingApprovalLoading || !pendingApprovalOptions.length"
            :filter-fn="filterPendingJournal"
            teleport-dropdown
            list-class-name="max-h-72"
          />
        </div>
      </div>

      <div
        v-if="isPendingApprovalLoading && !selectedApprovalJournal"
        class="mx-auto max-w-4xl rounded-2xl border border-slate-200 bg-white p-12 text-center shadow-sm"
      >
        <i class="fa fa-spinner fa-spin mb-3 text-2xl text-slate-300"></i>
        <p class="text-sm text-slate-500">{{ t('journals.loading_pending_journals') }}</p>
      </div>

      <div
        v-else-if="!isPendingApprovalLoading && !pendingApprovalOptions.length"
        class="mx-auto max-w-4xl rounded-2xl border border-slate-200 bg-white p-10 text-center shadow-sm"
      >
        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
          <i class="fa fa-inbox text-lg text-slate-400"></i>
        </div>
        <p class="text-sm font-medium text-slate-900">
          {{ t('journals.no_pending_journals') }}
        </p>
        <p class="mt-1 text-sm text-slate-500">
          {{ t('journals.no_pending_journals_hint') }}
        </p>
      </div>

      <div
        v-else-if="!selectedApprovalJournal"
        class="mx-auto max-w-4xl rounded-2xl border border-dashed border-slate-300 bg-slate-50/80 p-10 text-center"
      >
        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-white ring-1 ring-slate-200">
          <i class="fa fa-search text-lg text-slate-400"></i>
        </div>
        <p class="text-sm font-medium text-slate-800">
          {{ t('journals.select_journal_to_preview') }}
        </p>
        <p class="mt-1 text-sm text-slate-500">
          {{ t('journals.select_journal_to_preview_hint') }}
        </p>
      </div>

      <template v-else>
        <JournalApprovalPreview
          :key="selectedApprovalJournal.id"
          :journal="selectedApprovalJournal"
        />

        <div
          class="mx-auto w-full max-w-4xl overflow-hidden rounded-2xl border border-indigo-100 bg-white shadow-sm ring-1 ring-indigo-50"
        >
          <div class="border-b border-indigo-50 bg-gradient-to-r from-indigo-50/80 to-white px-6 py-4">
            <h3 class="text-sm font-semibold text-indigo-950">
              {{ t('journals.manager_comment') }}
            </h3>
            <p class="mt-0.5 text-xs text-indigo-700/70">
              {{ t('journals.manager_action_hint') }}
            </p>
          </div>
          <div class="px-6 py-5">
            <BaseTextArea
              id="manager_comment"
              v-model="managerComment"
              :rows="3"
              className="w-full rounded-lg border border-slate-200 bg-slate-50/50 px-3 py-2.5 text-sm leading-relaxed text-slate-800 placeholder:text-slate-400 focus:border-indigo-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100"
              :placeholder="t('journals.manager_action_placeholder')"
            />
            <JournalManagerCommentPresets v-model="managerComment" />
            <p v-if="approveValidationMessage" class="mt-2 text-sm text-red-600">
              {{ approveValidationMessage }}
            </p>
            <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
              <BaseButton
                className="border border-slate-200 bg-white text-slate-700 hover:bg-slate-50"
                @click="selectedApprovalJournalId = ''"
              >
                {{ t('journals.clear_selection') }}
              </BaseButton>
              <BaseButton
                className="border border-rose-300 bg-white text-rose-700 hover:bg-rose-50"
                :disabled="isReturning || isApproving"
                @click="handleReturn"
              >
                <i v-if="!isReturning" class="fa fa-undo mr-1.5"></i>
                <span v-if="isReturning">{{ t('journals.returning') }}</span>
                <span v-else>{{ t('journals.return_journal') }}</span>
              </BaseButton>
              <BaseButton
                className="bg-indigo-600 text-white hover:bg-indigo-700"
                :disabled="isApproving || isReturning"
                @click="handleApprove"
              >
                <i v-if="!isApproving" class="fa fa-check mr-1.5"></i>
                <span v-if="isApproving">{{ t('journals.approving') }}</span>
                <span v-else>{{ t('journals.approve_journal') }}</span>
              </BaseButton>
            </div>
          </div>
        </div>
      </template>
    </template>
    </div>

    <JournalPaymentPanel
      v-else-if="activeTab === 'payment'"
      :active="activeTab === 'payment'"
      :initial-journal-id="selectedPaymentJournalId"
      @paid="handlePaid"
      @view-journals="handlePaymentViewJournals"
    />
  </SectionHeader>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import { toast } from '@/shared/config/toastConfig'
import { useTranslate } from '@/shared/composables/useTranslate'
import { useAccountOptionsQuery } from '../queries/useAccountOptionsQuery'
import { useJournalMutations } from '../queries/useJournalMutations'
import { usePartyLedgerOptionsQuery } from '../queries/usePartyLedgerOptionsQuery'
import { usePendingApprovalJournalsQuery, useReturnedJournalsQuery } from '../queries/usePendingApprovalJournalsQuery'
import JournalApprovalPreview from './components/JournalApprovalPreview.vue'
import JournalManagerCommentPresets from './components/JournalManagerCommentPresets.vue'
import JournalReturnSuccess from './components/JournalReturnSuccess.vue'
import JournalApprovalSuccess from './components/JournalApprovalSuccess.vue'
import JournalBillEntrySuccess from './components/JournalBillEntrySuccess.vue'
import JournalNarrationHints from './components/JournalNarrationHints.vue'
import JournalPaymentPanel from './components/JournalPaymentPanel.vue'
import JournalProjectSelect from './components/JournalProjectSelect.vue'
import JournalReceiptUpload from './components/JournalReceiptUpload.vue'
import {
  buildJournalPayload,
  buildNarrationHintContext,
  buildResubmitPayload,
  costTypeOptions,
  createEmptyJournalLine,
  defaultJournalForm,
  defaultJournalLines,
  mapJournalToForm,
  mapJournalToLines,
  todayIsoDate,
  validateJournalForm,
} from '../data/postJournalStatic'
import { usePartyTypeOptionsQuery } from '@/modules/parties/queries/usePartyTypeOptionsQuery'
import { useJournalTransactionTypeOptionsQuery } from '../queries/useJournalTransactionTypeOptionsQuery'

const { t } = useTranslate()
const router = useRouter()

const activeTab = ref('bill_entry')
const selectedApprovalJournalId = ref('')
const selectedPaymentJournalId = ref('')
const selectedReturnedJournalId = ref('')
const editingJournalId = ref('')
const billEntrySuccess = ref(null)
const approvalSuccess = ref(null)
const returnSuccess = ref(null)
const managerComment = ref('')
const approveValidationMessage = ref('')
let skipPartyClear = false
const pageTabs = computed(() => [
  { id: 'bill_entry', label: t('journals.tab_bill_entry') },
  { id: 'approval', label: t('journals.tab_approval') },
  { id: 'payment', label: t('journals.tab_payment') },
])

const isApprovalTab = computed(() => activeTab.value === 'approval')
const isBillEntryTab = computed(() => activeTab.value === 'bill_entry')
const {
  data: pendingApprovalData,
  isLoading: isPendingApprovalLoading,
  refetch: refetchPendingApprovals,
} = usePendingApprovalJournalsQuery(isApprovalTab)

const {
  data: returnedData,
  isLoading: isReturnedLoading,
  refetch: refetchReturned,
} = useReturnedJournalsQuery(isBillEntryTab)

const pendingApprovalJournals = computed(() => pendingApprovalData.value?.data?.data ?? [])
const returnedJournals = computed(() => returnedData.value?.data?.data ?? [])

const buildJournalSelectOption = (journal) => {
  const party = journal.party_code || journal.party_name || ''
  const amount = Number(journal.total_debit || 0).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
  const parts = [
    journal.voucher_no,
    journal.voucher_date_label || journal.voucher_date,
    party,
    amount ? `Dr ${amount}` : '',
  ].filter(Boolean)

  return {
    id: journal.id,
    code: journal.voucher_no,
    name: parts.join(' · '),
    voucher_no: journal.voucher_no,
    party_name: journal.party_name || '',
    party_code: journal.party_code || '',
    narration: journal.narration || '',
    reference_no: journal.reference_no || '',
  }
}

const pendingApprovalOptions = computed(() =>
  pendingApprovalJournals.value.map(buildJournalSelectOption),
)

const returnedOptions = computed(() => returnedJournals.value.map(buildJournalSelectOption))

const selectedApprovalJournal = computed(() =>
  pendingApprovalJournals.value.find(
    (journal) => String(journal.id) === String(selectedApprovalJournalId.value),
  ) ?? null,
)

const selectedReturnedJournal = computed(() =>
  returnedJournals.value.find(
    (journal) => String(journal.id) === String(selectedReturnedJournalId.value),
  ) ?? null,
)

const pendingJournalPlaceholder = computed(() =>
  isPendingApprovalLoading.value
    ? t('journals.loading_pending_journals')
    : pendingApprovalOptions.value.length
      ? t('journals.search_pending_journal')
      : t('journals.no_pending_journals'),
)

const returnedJournalPlaceholder = computed(() =>
  isReturnedLoading.value
    ? t('journals.loading_returned_journals')
    : returnedOptions.value.length
      ? t('journals.search_returned_journal')
      : t('journals.no_returned_journals'),
)

const filterPendingJournal = (option, query) => {
  const q = String(query || '').toLowerCase()
  if (!q) return true
  return [
    option?.name,
    option?.code,
    option?.voucher_no,
    option?.party_name,
    option?.party_code,
    option?.narration,
    option?.reference_no,
  ]
    .filter(Boolean)
    .some((value) => String(value).toLowerCase().includes(q))
}

const filterReturnedJournal = filterPendingJournal

const form = ref({ ...defaultJournalForm, voucher_date: todayIsoDate() })
const lines = ref(defaultJournalLines.map((line) => ({ ...line })))
const validationMessage = ref('')
const pendingStatus = ref('')
let nextLineId = Math.max(...lines.value.map((line) => Number(line.id) || 0), 0) + 1

const resetBillEntryForm = () => {
  form.value = { ...defaultJournalForm, voucher_date: todayIsoDate() }
  lines.value = defaultJournalLines.map((line) => ({ ...line }))
  validationMessage.value = ''
  pendingStatus.value = ''
  editingJournalId.value = ''
  nextLineId = Math.max(...lines.value.map((line) => Number(line.id) || 0), 0) + 1
}

const clearReturnedSelection = () => {
  selectedReturnedJournalId.value = ''
  resetBillEntryForm()
}

const loadReturnedJournal = (journal) => {
  if (!journal) {
    if (!selectedReturnedJournalId.value) {
      editingJournalId.value = ''
      resetBillEntryForm()
    }
    return
  }

  skipPartyClear = true
  editingJournalId.value = String(journal.id)
  form.value = mapJournalToForm(journal)
  lines.value = mapJournalToLines(journal)
  validationMessage.value = ''
  nextLineId = Math.max(...lines.value.map((line) => Number(line.id) || 0), 0) + 1
  queueMicrotask(() => {
    skipPartyClear = false
  })
}

const buildBillEntrySuccessPayload = (type, result) => {
  const journal = result?.data || result || {}

  return {
    type,
    voucherNo: journal.voucher_no || '—',
    journalId: journal.id || '',
    status: journal.status || '—',
    statusRaw: journal.status_raw || '',
    totalDebit: journal.total_debit,
    totalCredit: journal.total_credit,
    voucherDate: journal.voucher_date_label || journal.voucher_date || '—',
  }
}

const buildApprovalSuccessPayload = (result, comment = '') => {
  const journal = result?.data || result || {}

  return {
    voucherNo: journal.voucher_no || '—',
    journalId: journal.id || '',
    status: journal.status || '—',
    statusRaw: journal.status_raw || '',
    totalDebit: journal.total_debit,
    totalCredit: journal.total_credit,
    voucherDate: journal.voucher_date_label || journal.voucher_date || '—',
    managerComment: comment || journal.manager_comment || '',
  }
}

const buildReturnSuccessPayload = (result, comment = '') => {
  const journal = result?.data || result || {}

  return {
    voucherNo: journal.voucher_no || '—',
    journalId: journal.id || '',
    status: journal.status || '—',
    statusRaw: journal.status_raw || '',
    totalDebit: journal.total_debit,
    totalCredit: journal.total_credit,
    voucherDate: journal.voucher_date_label || journal.voucher_date || '—',
    managerComment: comment || journal.manager_comment || '',
    journal,
  }
}

const showBillEntrySuccessPage = (type, result) => {
  billEntrySuccess.value = buildBillEntrySuccessPayload(type, result)
}

const showApprovalSuccessPage = (result, comment = '') => {
  approvalSuccess.value = buildApprovalSuccessPayload(result, comment)
}

const showReturnSuccessPage = (result, comment = '') => {
  returnSuccess.value = buildReturnSuccessPayload(result, comment)
}

const handleSuccessGoToApproval = () => {
  billEntrySuccess.value = null
  managerComment.value = ''
  approveValidationMessage.value = ''
  activeTab.value = 'approval'
}

const handleBillEntrySuccessPrimary = () => {
  const type = billEntrySuccess.value?.type
  if (type === 'posted') {
    billEntrySuccess.value = null
    router.push({ name: 'Journal Management' })
    return
  }
  handleSuccessGoToApproval()
}

const handleSuccessCreateNew = () => {
  billEntrySuccess.value = null
  selectedReturnedJournalId.value = ''
  resetBillEntryForm()
}

const handleApprovalSuccessGoToPayment = () => {
  approvalSuccess.value = null
  activeTab.value = 'payment'
}

const handleApprovalSuccessReviewMore = () => {
  approvalSuccess.value = null
  selectedApprovalJournalId.value = ''
  managerComment.value = ''
  approveValidationMessage.value = ''
}

const handleReturnSuccessGoToBillEntry = () => {
  const payload = returnSuccess.value
  returnSuccess.value = null
  activeTab.value = 'bill_entry'

  if (!payload?.journalId) return

  selectedReturnedJournalId.value = String(payload.journalId)
  if (payload.journal?.id) {
    loadReturnedJournal(payload.journal)
  }
}

const handleReturnSuccessReviewMore = () => {
  returnSuccess.value = null
  selectedApprovalJournalId.value = ''
  managerComment.value = ''
  approveValidationMessage.value = ''
}

const { data: transactionTypeOptionsData } = useJournalTransactionTypeOptionsQuery('active')
const transactionTypeOptions = computed(() =>
  (transactionTypeOptionsData.value ?? []).map((item) => ({
    id: item.id ?? item.code,
    code: item.code ?? item.id,
    name: item.name,
  })),
)

const { data: partyTypeOptionsData } = usePartyTypeOptionsQuery('active')
const partyTypeOptions = computed(() => [
  { id: '', code: '', name: 'None' },
  ...(partyTypeOptionsData.value ?? []).map((item) => ({
    id: item.id ?? item.code,
    code: item.code ?? item.id,
    name: item.name,
  })),
])

const { submit, submitLoading: isSubmitting, approve, approveLoading: isApproving, returnJournal: returnMutation, returnLoading: isReturning, resubmit, resubmitLoading: isResubmitting } =
  useJournalMutations({
    onSuccess(result) {
      const journal = result?.data || result || {}
      const journalId = journal?.id || ''
      const status = journal?.status_raw || pendingStatus.value

      if (status === 'pending_approval') {
        resetBillEntryForm()
        selectedReturnedJournalId.value = ''
        selectedApprovalJournalId.value = journalId ? String(journalId) : ''
        refetchPendingApprovals()
        refetchReturned()
        showBillEntrySuccessPage('submitted', result)
        return
      }

      resetBillEntryForm()
      selectedReturnedJournalId.value = ''
      showBillEntrySuccessPage('posted', result)
    },
    onApproveSuccess(result) {
      const journal = result?.data || result || {}
      const journalId = journal?.id || ''
      selectedPaymentJournalId.value = journalId ? String(journalId) : ''
      selectedApprovalJournalId.value = ''
      const comment = String(managerComment.value || '').trim()
      managerComment.value = ''
      approveValidationMessage.value = ''
      refetchPendingApprovals()
      showApprovalSuccessPage(result, comment)
    },
    onReturnSuccess(result) {
      const comment = String(managerComment.value || '').trim()
      selectedApprovalJournalId.value = ''
      managerComment.value = ''
      approveValidationMessage.value = ''
      refetchPendingApprovals()
      refetchReturned()
      showReturnSuccessPage(result, comment)
    },
    onResubmitSuccess(result) {
      const journal = result?.data || result || {}
      const journalId = journal?.id || ''
      selectedReturnedJournalId.value = ''
      editingJournalId.value = ''
      resetBillEntryForm()
      selectedApprovalJournalId.value = journalId ? String(journalId) : ''
      refetchPendingApprovals()
      refetchReturned()
      showBillEntrySuccessPage('resubmitted', result)
    },
  })

const handleApprove = async () => {
  approveValidationMessage.value = ''
  const comment = String(managerComment.value || '').trim()
  if (!comment) {
    approveValidationMessage.value = t('journals.error_manager_comment_required')
    toast.error(approveValidationMessage.value)
    return
  }
  if (!selectedApprovalJournalId.value) return

  try {
    await approve.mutateAsync({
      id: selectedApprovalJournalId.value,
      manager_comment: comment,
    })
  } catch (error) {
    approveValidationMessage.value =
      error?.errors?.manager_comment?.[0] ||
      error?.message ||
      t('journals.error_manager_comment_required')
  }
}

const handleReturn = async () => {
  approveValidationMessage.value = ''
  const comment = String(managerComment.value || '').trim()
  if (!comment) {
    approveValidationMessage.value = t('journals.error_return_comment_required')
    toast.error(approveValidationMessage.value)
    return
  }
  if (!selectedApprovalJournalId.value) return

  try {
    await returnMutation.mutateAsync({
      id: selectedApprovalJournalId.value,
      manager_comment: comment,
    })
  } catch (error) {
    approveValidationMessage.value =
      error?.errors?.manager_comment?.[0] ||
      error?.message ||
      t('journals.error_return_comment_required')
  }
}

const handlePaid = () => {
  selectedPaymentJournalId.value = ''
}

const handlePaymentViewJournals = () => {
  selectedPaymentJournalId.value = ''
  router.push({ name: 'Journal Management' })
}

watch(isApprovalTab, (enabled) => {
  if (enabled) {
    refetchPendingApprovals()
  }
})

watch(isBillEntryTab, (enabled) => {
  if (enabled) {
    refetchReturned()
  }
})

watch(selectedReturnedJournal, (journal) => {
  loadReturnedJournal(journal)
})

watch(returnedOptions, (options) => {
  if (!options.length || isReturnedLoading.value) return
  if (
    selectedReturnedJournalId.value &&
    !options.some((option) => String(option.id) === String(selectedReturnedJournalId.value))
  ) {
    selectedReturnedJournalId.value = ''
  }
})

watch(
  () => form.value.party_type,
  () => {
    if (skipPartyClear) return
    form.value.party_id = ''
  },
)

watch(selectedApprovalJournalId, () => {
  managerComment.value = ''
  approveValidationMessage.value = ''
})

watch(pendingApprovalOptions, (options) => {
  if (!options.length || isPendingApprovalLoading.value) return
  if (
    selectedApprovalJournalId.value &&
    !options.some((option) => String(option.id) === String(selectedApprovalJournalId.value))
  ) {
    selectedApprovalJournalId.value = ''
  }
})

const statusBadgeClass = computed(() => {
  const status = String(form.value.status || '').toLowerCase()
  if (status === 'posted') return 'bg-emerald-50 text-emerald-700 ring-emerald-200'
  if (status.includes('pending')) return 'bg-blue-50 text-blue-700 ring-blue-200'
  if (status.includes('return')) return 'bg-rose-50 text-rose-700 ring-rose-200'
  return 'bg-amber-50 text-amber-700 ring-amber-200'
})

const addLine = () => {
  lines.value.push(createEmptyJournalLine(nextLineId++))
}

const copyLine = (index) => {
  const source = lines.value[index]
  if (!source) return

  const copied = {
    ...source,
    id: nextLineId++,
  }
  lines.value.splice(index + 1, 0, copied)
}

const deleteLine = (index) => {
  if (lines.value.length <= 1) return
  lines.value.splice(index, 1)
}

const partyTypeRef = computed(() => form.value.party_type)
const { data: partiesData, isLoading: isPartyLedgerLoading } =
  usePartyLedgerOptionsQuery(partyTypeRef)
const { data: accountsData, isLoading: isAccountLoading } = useAccountOptionsQuery()

const partyLedgerOptions = computed(() => {
  const parties = partiesData.value?.data?.data ?? []
  return parties.map((party) => ({
    id: party.id,
    name: `${party.code} – ${party.name}`,
    code: party.code,
  }))
})

const accountOptions = computed(() => {
  const accounts = accountsData.value?.data?.data ?? []
  return accounts.map((account) => ({
    id: account.id,
    name: `${account.code} – ${account.name}`,
    code: account.code,
  }))
})

const partyLedgerPlaceholder = computed(() =>
  isPartyLedgerLoading.value
    ? t('journals.loading_parties')
    : t('journals.select_party'),
)

const accountPlaceholder = computed(() =>
  isAccountLoading.value
    ? t('journals.loading_accounts')
    : t('journals.select_account'),
)

const filterByCodeOrName = (option, query) => {
  const label = String(option?.name ?? '').toLowerCase()
  const code = String(option?.code ?? '').toLowerCase()
  return label.includes(query) || code.includes(query)
}

const filterByNameOrCode = (option, query) => {
  const label = String(option?.name ?? '').toLowerCase()
  const code = String(option?.code ?? option?.id ?? '').toLowerCase()
  return label.includes(query) || code.includes(query)
}

const toNumber = (value) => {
  const n = Number(value)
  return Number.isFinite(n) ? n : 0
}

const totalDebit = computed(() =>
  lines.value.reduce((sum, line) => sum + toNumber(line.debit), 0),
)

const totalCredit = computed(() =>
  lines.value.reduce((sum, line) => sum + toNumber(line.credit), 0),
)

const difference = computed(() => totalDebit.value - totalCredit.value)
const isBalanced = computed(() => Math.abs(difference.value) < 0.0001)

const formatAmount = (value) => {
  if (value === null || value === undefined || value === '') return '0'
  return Number(value).toLocaleString('en-US')
}

const differenceLabel = computed(() => {
  const abs = Math.abs(difference.value)
  if (abs < 0.0001) return `0 ${t('journals.cr')}`
  return `${formatAmount(abs)} ${difference.value > 0 ? t('journals.dr') : t('journals.cr')}`
})

const postingPreviewLines = computed(() => {
  const previewLines = lines.value.filter(
    (line) => line.account_id || toNumber(line.debit) > 0 || toNumber(line.credit) > 0,
  )

  return previewLines.map((line) => {
    const account =
      accountOptions.value.find((opt) => String(opt.id) === String(line.account_id))?.name || '—'
    const debit = toNumber(line.debit)

    if (debit > 0) {
      return { id: line.id, account, side: 'dr', amount: debit }
    }

    return { id: line.id, account, side: 'cr', amount: toNumber(line.credit) }
  })
})

const narrationHintContext = computed(() =>
  buildNarrationHintContext(form.value, lines.value, {
    transactionTypeOptions: transactionTypeOptions.value,
    partyTypeOptions: partyTypeOptions.value,
    partyLedgerOptions: partyLedgerOptions.value,
    accountOptions: accountOptions.value,
  }),
)

const canSuggestNarrationHints = computed(() => {
  const context = narrationHintContext.value

  return Boolean(
    context.transaction_type ||
      context.reference_no ||
      context.party_label ||
      (context.lines?.length ?? 0) > 0,
  )
})

const goBack = () => {
  router.push({ name: 'Journal Management' })
}

const handleSubmit = async (status) => {
  validationMessage.value = ''
  pendingStatus.value = status

  const errors = validateJournalForm(form.value, lines.value)
  if (errors.length) {
    const firstError = errors[0]
    const message = t(firstError.key, firstError.params || {})
    validationMessage.value = message
    toast.error(message)
    return
  }

  try {
    await submit.mutateAsync(buildJournalPayload(form.value, lines.value, status))
  } catch (error) {
    const message =
      error?.message ||
      error?.errors?.lines?.[0] ||
      Object.values(error?.errors ?? {})[0]?.[0] ||
      t('journals.error_unbalanced')
    validationMessage.value = message
  }
}

const handleResubmit = async () => {
  validationMessage.value = ''

  if (!editingJournalId.value) return

  const errors = validateJournalForm(form.value, lines.value)
  if (errors.length) {
    const firstError = errors[0]
    const message = t(firstError.key, firstError.params || {})
    validationMessage.value = message
    toast.error(message)
    return
  }

  try {
    await resubmit.mutateAsync({
      id: editingJournalId.value,
      ...buildResubmitPayload(form.value, lines.value),
    })
  } catch (error) {
    const message =
      error?.message ||
      error?.errors?.lines?.[0] ||
      Object.values(error?.errors ?? {})[0]?.[0] ||
      t('journals.error_unbalanced')
    validationMessage.value = message
  }
}
</script>
