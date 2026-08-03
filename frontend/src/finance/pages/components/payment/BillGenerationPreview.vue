<template>
  <aside
    class="overflow-hidden rounded-xl border border-slate-200 bg-slate-100/80 shadow-sm xl:sticky xl:top-4"
    :class="printOnly ? 'pointer-events-none fixed left-[-10000px] top-0 w-[210mm] opacity-0 print:pointer-events-auto print:static print:left-auto print:w-auto print:opacity-100 print:border-0 print:bg-transparent print:shadow-none' : ''"
  >
    <div
      v-if="!printOnly"
      class="flex items-center justify-between gap-3 border-b border-slate-200 bg-white px-4 py-3 print:hidden"
    >
      <div>
        <h3 class="text-sm font-semibold text-slate-900">Bill</h3>
        <p class="text-xs text-slate-500">Premium A4 layout · company branding</p>
      </div>
      <BaseButton
        type="button"
        :className="'inline-flex items-center rounded-lg bg-[#0d5c4d] px-3.5 py-1.5 text-sm font-medium text-white hover:bg-[#0a4a3e]'"
        @click="printBill"
      >
        <i class="fa fa-print mr-1.5"></i>
        Print Bill
      </BaseButton>
    </div>

    <div
      class="print:max-h-none print:overflow-visible print:p-0"
      :class="printOnly ? 'p-0' : 'max-h-[calc(100vh-8rem)] overflow-y-auto p-3'"
    >
      <div id="expense-bill-print" class="eb-sheet">
        <!-- Header: logo | company -->
        <header class="eb-header">
          <div class="eb-logo-col">
            <img
              v-if="companyLogo"
              :src="companyLogo"
              alt="Company logo"
              class="eb-logo"
            />
            <div v-else class="eb-logo-fallback">
              <i class="fa fa-building"></i>
              <span>COMPANY LOGO</span>
            </div>
          </div>

          <div class="eb-divider" aria-hidden="true"></div>

          <div class="eb-company">
            <h1 class="eb-company-name">{{ companyName }}</h1>
            <p v-if="companyAddress" class="eb-company-line">
              <i class="fa fa-map-marker eb-ico"></i>
              <span>{{ companyAddress }}</span>
            </p>
            <p v-if="companyContactParts.length" class="eb-company-contact">
              <span v-if="companyPhone" class="eb-contact-item">
                <i class="fa fa-phone eb-ico"></i>
                {{ companyPhone }}
              </span>
              <span v-if="companyEmail" class="eb-contact-item">
                <i class="fa fa-envelope eb-ico"></i>
                {{ companyEmail }}
              </span>
            </p>
          </div>
        </header>

        <!-- Title ribbon -->
        <div class="eb-title-wrap">
          <div class="eb-title-bar">
            <div class="eb-title-badge">
              <span>EXPENSE BILL</span>
            </div>
          </div>
        </div>

        <div v-if="requestNo" class="eb-bill-no-wrap">
          <div class="eb-bill-no">
            <span class="eb-bill-no-value">{{ requestNo }}</span>
          </div>
        </div>

        <!-- Meta card -->
        <div class="eb-meta">
          <div class="eb-meta-grid">
            <div class="eb-meta-col">
              <div class="eb-meta-row">
                <span class="eb-meta-label">BILL DATE</span>
                <span class="eb-meta-value">{{ billDateLabel }}</span>
              </div>
              <div class="eb-meta-row">
                <span class="eb-meta-label">REFERENCE NO</span>
                <span class="eb-meta-value">{{ referenceNo || '—' }}</span>
              </div>
            </div>
            <div class="eb-meta-split" aria-hidden="true"></div>
            <div class="eb-meta-col">
              <div class="eb-meta-row">
                <span class="eb-meta-label">PAYMENT METHOD</span>
                <span class="eb-meta-value">{{ paymentMethodLabel }}</span>
              </div>
              <div class="eb-meta-row">
                <span class="eb-meta-label">CATEGORY</span>
                <span class="eb-meta-value">{{ categoryName }}</span>
              </div>
            </div>
          </div>
          <div
            v-for="(account, index) in normalizedLinkedAccounts"
            :key="`linked-meta-${index}`"
            class="eb-meta-grid eb-meta-linked"
          >
            <div class="eb-meta-col">
              <div class="eb-meta-row">
                <span class="eb-meta-label">LINKED ACCOUNT</span>
                <span class="eb-meta-value">{{ account.typeLabel || '—' }}</span>
              </div>
            </div>
            <div class="eb-meta-split" aria-hidden="true"></div>
            <div class="eb-meta-col">
              <div class="eb-meta-row">
                <span class="eb-meta-label">ACCOUNT</span>
                <span class="eb-meta-value">{{ account.accountName || '—' }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Particulars -->
        <section class="eb-section">
          <div class="eb-section-title">
            <span class="eb-section-icon">
              <i class="fa fa-file-text-o"></i>
            </span>
            <h3>BILL PARTICULARS</h3>
            <span class="eb-section-line" aria-hidden="true"></span>
          </div>

          <table class="eb-table eb-table-particulars">
            <thead>
              <tr>
                <th class="col-sl">SL</th>
                <th class="col-desc">DESCRIPTION</th>
                <th class="col-amt">AMOUNT (৳)</th>
              </tr>
            </thead>
            <tbody>
              <template v-if="normalizedLineItems.length">
                <tr v-for="(item, index) in normalizedLineItems" :key="`line-${index}`">
                  <td class="col-sl">{{ index + 1 }}</td>
                  <td class="col-desc">
                    <p class="desc-main">
                      {{ item.description }}
                      <span
                        v-if="item.candidateName || item.passportNo || item.applicationStatus"
                        class="desc-candidate"
                      >
                        {{ item.candidateName }}
                        <template v-if="item.passportNo"> · {{ item.passportNo }}</template>
                        <template v-if="item.applicationStatus"> · {{ item.applicationStatus }}</template>
                      </span>
                    </p>
                    <p v-if="showBillNoInParticulars && item.billNo" class="desc-bill-no">
                      Bill No: {{ item.billNo }}
                    </p>
                  </td>
                  <td class="col-amt">{{ formatCurrency(item.amount) }}</td>
                </tr>
              </template>
              <tr v-else>
                <td class="col-sl">1</td>
                <td class="col-desc">
                  <p class="desc-main">{{ descriptionPrimary }}</p>
                  <p v-if="showBillNoInParticulars && billNo" class="desc-bill-no">Bill No: {{ billNo }}</p>
                  <p v-if="unitAmountNote" class="desc-sub">{{ unitAmountNote }}</p>
                  <p
                    v-for="(app, index) in applications"
                    :key="app.application_id || app.id || index"
                    class="desc-sub"
                  >
                    {{ app.candidate_name }}
                    <template v-if="app.passport_no"> · {{ app.passport_no }}</template>
                    <template v-if="app.application_status"> · {{ app.application_status }}</template>
                  </p>
                </td>
                <td class="col-amt">{{ formatCurrency(totalAmount) }}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="2" class="total-label">Total Amount</td>
                <td class="col-amt total-amt">{{ formatCurrency(displayTotalAmount) }}</td>
              </tr>
            </tfoot>
          </table>
        </section>

        <!-- Optional: demand letter / linked accounts / remarks -->
        <section v-if="demandLetter" class="eb-section eb-optional">
          <div class="eb-section-title">
            <span class="eb-section-icon"><i class="fa fa-file-o"></i></span>
            <h3>DEMAND LETTER</h3>
            <span class="eb-section-line" aria-hidden="true"></span>
          </div>
          <div class="eb-optional-box">
            <span><strong>DL No:</strong> {{ demandLetter.dl_no }}</span>
            <span><strong>Client:</strong> {{ demandLetter.client_name }}</span>
            <span v-if="demandLetter.country">
              <strong>Country:</strong> {{ demandLetter.country }}
            </span>
          </div>
        </section>

        <section v-if="remarks" class="eb-section eb-optional">
          <div class="eb-section-title">
            <span class="eb-section-icon"><i class="fa fa-comment-o"></i></span>
            <h3>REMARKS</h3>
            <span class="eb-section-line" aria-hidden="true"></span>
          </div>
          <p class="eb-remarks">{{ remarks }}</p>
        </section>

        <div class="eb-spacer" aria-hidden="true"></div>

        <!-- Signatures -->
        <footer class="eb-footer">
          <div class="eb-sign">
            <p v-if="preparedByDisplayName" class="eb-sign-name">{{ preparedByDisplayName }}</p>
            <div v-else class="eb-sign-space" aria-hidden="true"></div>
            <div class="eb-sign-line"></div>
            <p class="eb-sign-role">
              <i class="fa fa-user"></i>
              Prepared By
            </p>
          </div>
          <div class="eb-sign">
            <div class="eb-sign-space" aria-hidden="true"></div>
            <div class="eb-sign-line"></div>
            <p class="eb-sign-role">
              <i class="fa fa-check-circle"></i>
              Approved By
            </p>
          </div>
          <div class="eb-sign">
            <div class="eb-sign-space" aria-hidden="true"></div>
            <div class="eb-sign-line"></div>
            <p class="eb-sign-role">
              <i class="fa fa-pencil"></i>
              Authorized Signature
            </p>
          </div>
        </footer>

        <div class="eb-bottom-bar" aria-hidden="true">
          <span class="eb-bottom-notch"></span>
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue'
import { useSettingsQuery } from '@/modules/setting/queries/useSettingsQuery'
import { formatCurrency } from '@/finance/utils/billUtils'
import { printWithOrientation } from '@/shared/utils/printOrientation'
import { DIRECT_COST_CATEGORY_CODE } from '@/finance/data/expenseCategoryCodes'

const props = defineProps({
  requestNo: { type: String, default: '' },
  billNo: { type: String, default: '' },
  preparedByName: { type: String, default: '' },
  billDate: { type: String, default: '' },
  paymentMethodLabel: { type: String, default: '—' },
  referenceNo: { type: String, default: '' },
  categoryName: { type: String, default: '—' },
  categoryCode: { type: String, default: '' },
  expenseHeadName: { type: String, default: '—' },
  particular: { type: String, default: '' },
  remarks: { type: String, default: '' },
  totalAmount: { type: Number, default: 0 },
  unitAmountNote: { type: String, default: '' },
  demandLetter: { type: Object, default: null },
  applications: { type: Array, default: () => [] },
  linkedAccounts: { type: Array, default: () => [] },
  jobName: { type: String, default: '' },
  lineItems: { type: Array, default: () => [] },
  printOnly: { type: Boolean, default: false },
})

const { data: settingsResponse } = useSettingsQuery(1)
const settingsData = computed(() => settingsResponse.value?.data?.data || {})

const companyName = computed(() => settingsData.value.company_name || 'Company Name')
const companyLogo = computed(() => settingsData.value.company_logo_url || '')
const companyAddress = computed(() => settingsData.value.company_address || '')
const companyPhone = computed(() => settingsData.value.company_phone || '')
const companyEmail = computed(() => settingsData.value.company_email || '')
const companyContactParts = computed(() =>
  [companyPhone.value, companyEmail.value].filter(Boolean)
)

const preparedByDisplayName = computed(() => {
  const name = props.preparedByName?.trim()
  if (!name) return ''
  return name.charAt(0).toUpperCase() + name.slice(1)
})

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

const isDirectExpenseCategory = computed(() => {
  if (props.categoryCode === DIRECT_COST_CATEGORY_CODE) return true
  return String(props.categoryName || '').trim().toLowerCase() === 'direct expense'
})

const showBillNoInParticulars = computed(() => !isDirectExpenseCategory.value)

const descriptionPrimary = computed(() => {
  if (props.particular) return props.particular
  if (props.categoryName !== '—' && props.expenseHeadName !== '—') {
    return `${props.categoryName} · ${props.expenseHeadName}`
  }
  return props.expenseHeadName
})

const normalizedLineItems = computed(() =>
  (props.lineItems || [])
    .map((item) => ({
      description: item.description || item.particular || item.expenseHeadName || '—',
      expenseHeadName: item.expenseHeadName || '',
      billNo: item.billNo || item.bill_no || item.voucher_no || '',
      amount: Number(item.amount) || 0,
      linkedAccountType: item.linkedAccountType || item.linked_account_type || '',
      linkedAccountName: item.linkedAccountName || item.linked_account_name || '',
      candidateName: item.candidateName || item.candidate_name || '',
      passportNo: item.passportNo || item.passport_no || '',
      applicationStatus: item.applicationStatus || item.application_status || '',
    }))
    .filter((item) => item.amount > 0 || item.description)
)

const normalizedLinkedAccounts = computed(() => {
  const seen = new Set()
  return (props.linkedAccounts || [])
    .map((account) => ({
      expenseHeadName: account.expenseHeadName || account.expense_head_name || '',
      typeLabel: account.typeLabel || account.type_label || account.linked_account_type || '',
      accountName: account.accountName || account.account_name || account.linked_account_name || '',
    }))
    .filter((account) => account.accountName || account.typeLabel)
    .filter((account) => {
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
})

const displayTotalAmount = computed(() => {
  if (normalizedLineItems.value.length) {
    return normalizedLineItems.value.reduce((sum, item) => sum + (Number(item.amount) || 0), 0)
  }
  return Number(props.totalAmount) || 0
})

function printBill() {
  printWithOrientation('portrait', '10mm', 'expense-bill-print')
}

defineExpose({ printBill })
</script>

<style scoped>
.eb-sheet {
  --green: #0d5c4d;
  --green-deep: #0a4a3e;
  --green-soft: #e8f5f1;
  --green-mid: #b7ddd2;
  --ink: #1a1a1a;
  --muted: #6b7280;
  --line: #d1d5db;

  position: relative;
  width: 100%;
  min-height: 297mm;
  max-width: 210mm;
  margin: 0 auto;
  padding: 10.5mm 10.5mm 12.5mm;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  background: #fff;
  color: var(--ink);
  box-shadow: 0 8px 28px rgba(15, 23, 42, 0.08);
  font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
}

/* Header */
.eb-header {
  display: flex;
  align-items: stretch;
  gap: 0;
}

.eb-logo-col {
  width: 108px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  padding-right: 14px;
}

.eb-logo {
  max-width: 96px;
  max-height: 96px;
  object-fit: contain;
}

.eb-logo-fallback {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  color: var(--green);
  font-size: 9px;
  font-weight: 700;
  letter-spacing: 0.04em;
}

.eb-logo-fallback i {
  font-size: 34px;
}

.eb-divider {
  width: 1px;
  background: #c5cdd4;
  margin: 4px 16px 4px 0;
  flex-shrink: 0;
}

.eb-company {
  flex: 1;
  min-width: 0;
  padding-top: 2px;
}

.eb-company-name {
  margin: 0;
  font-size: 20px;
  font-weight: 800;
  letter-spacing: 0.01em;
  line-height: 1.25;
  text-transform: uppercase;
  color: var(--green-deep);
}

.eb-company-line,
.eb-company-contact {
  margin: 7px 0 0;
  font-size: 11px;
  line-height: 1.45;
  color: #374151;
}

.eb-company-contact {
  display: flex;
  flex-wrap: wrap;
  gap: 10px 18px;
}

.eb-contact-item {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.eb-ico {
  color: var(--green);
  width: 12px;
  text-align: center;
}

/* Title ribbon — dark bar with trapezoid badge */
.eb-title-wrap {
  margin-top: 11px;
}

.eb-bill-no-wrap {
  display: flex;
  justify-content: flex-start;
  margin-top: 6px;
}

.eb-bill-no {
  display: inline-flex;
  align-items: center;
  padding: 4px 12px;
  border: 1.5px solid var(--green);
  border-radius: 6px;
  background: var(--green-soft);
}

.eb-bill-no-value {
  font-size: 15px;
  font-weight: 800;
  letter-spacing: 0.03em;
  color: var(--green-deep);
  line-height: 1.2;
  word-break: break-all;
}

.eb-title-bar {
  position: relative;
  height: 34px;
  background: var(--green);
  display: flex;
  align-items: center;
  justify-content: center;
}

.eb-title-badge {
  position: relative;
  z-index: 1;
  min-width: 220px;
  padding: 8px 36px 9px;
  background: var(--green-soft);
  color: var(--green);
  font-size: 15px;
  font-weight: 800;
  letter-spacing: 0.16em;
  text-align: center;
  clip-path: polygon(12px 0, calc(100% - 12px) 0, 100% 100%, 0 100%);
  box-shadow: 0 0 0 1px rgba(13, 92, 77, 0.08);
}

/* Meta box */
.eb-meta {
  display: flex;
  flex-direction: column;
  margin-top: 11px;
  border: 1px solid var(--green-mid);
  border-radius: 8px;
  overflow: visible;
  background: #fff;
  flex-shrink: 0;
}

.eb-meta-grid {
  display: grid;
  grid-template-columns: 1fr 1px 1fr;
}

.eb-meta-linked {
  border-top: 1px solid var(--green-mid);
}

.eb-meta-col {
  padding: 6px 11px;
  display: flex;
  flex-direction: column;
  gap: 4px;
  min-width: 0;
}

.eb-meta-split {
  background: #e5e7eb;
}

.eb-meta-row {
  display: flex;
  flex-direction: row;
  align-items: baseline;
  flex-wrap: wrap;
  gap: 6px 8px;
  min-height: 0;
}

.eb-meta-label {
  font-size: 10px;
  font-weight: 750;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--green);
  line-height: 1.3;
  flex-shrink: 0;
}

.eb-meta-label::after {
  content: ':';
}

.eb-meta-value {
  font-size: 13px;
  font-weight: 700;
  color: var(--ink);
  line-height: 1.35;
  word-break: break-word;
}

/* Section */
.eb-section {
  margin-top: 13px;
}

.eb-section-title {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 5px;
}

.eb-section-icon {
  width: 22px;
  height: 22px;
  border-radius: 999px;
  background: var(--green);
  color: #fff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  flex-shrink: 0;
}

.eb-section-title h3 {
  margin: 0;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.08em;
  color: var(--green-deep);
  white-space: nowrap;
}

.eb-section-line {
  flex: 1;
  height: 1px;
  background: #d1d5db;
}

/* Table */
.eb-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 11.5px;
}

.eb-table th,
.eb-table td {
  border: 1px solid var(--green-mid);
  padding: 6px 8px;
  vertical-align: top;
  line-height: 1.35;
}

.eb-table thead th {
  background: var(--green);
  color: #fff;
  font-size: 10px;
  font-weight: 750;
  letter-spacing: 0.05em;
  text-align: left;
  padding: 4px 8px;
}

.eb-table .col-sl {
  width: 36px;
  text-align: center;
}

.eb-table thead .col-sl {
  text-align: center;
}

.eb-table .col-amt {
  width: 96px;
  text-align: right;
  font-variant-numeric: tabular-nums;
  font-weight: 700;
  white-space: nowrap;
}

.eb-table thead .col-amt {
  text-align: right;
}

.eb-table-particulars {
  border: 1px solid var(--green-mid);
  border-radius: 6px;
  overflow: hidden;
  box-shadow: 0 1px 0 rgba(13, 92, 77, 0.06);
}

.eb-table-particulars thead th {
  background: var(--green);
  color: #ffffff;
  border-color: var(--green-deep);
  font-size: 9.5px;
  font-weight: 750;
  letter-spacing: 0.06em;
  padding-top: 5px;
  padding-bottom: 5px;
}

.eb-table-particulars tbody td {
  border-color: var(--green-mid);
  background: #ffffff;
}

.eb-table-particulars tbody tr:nth-child(even) td {
  background: var(--green-soft);
}

.eb-table-particulars .col-sl {
  background: #dff0ea;
  color: var(--green-deep);
  font-size: 10px;
  font-weight: 700;
}

.eb-table-particulars .col-desc {
  color: var(--ink);
}

.eb-table-particulars .col-desc .desc-main {
  font-size: 11.5px;
  font-weight: 700;
  color: #111111;
  letter-spacing: 0.01em;
}

.eb-table-particulars .col-amt {
  background: #f4faf8;
  color: var(--green-deep);
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.01em;
}

.eb-table-particulars tfoot td {
  background: var(--green-soft);
  border-color: var(--green-mid);
}

.eb-table-particulars tfoot .total-label {
  text-align: right;
  font-size: 10.5px;
  font-weight: 700;
  color: var(--green-deep);
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.eb-table-particulars tfoot .total-amt {
  font-size: 13px;
  font-weight: 900;
  color: var(--green-deep);
}

.desc-main {
  margin: 0;
  font-size: 11px;
  font-weight: 700;
  color: #0f172a;
  line-height: 1.35;
}

.desc-sub {
  margin: 1px 0 0;
  font-size: 9.5px;
  line-height: 1.3;
  color: #6b7280;
  font-weight: 400;
}

.eb-table-particulars .col-desc .desc-candidate {
  display: inline;
  font-size: 9px;
  font-weight: 400;
  color: #4b5563;
  letter-spacing: 0;
}

.desc-bill-no {
  margin: 1px 0 0;
  font-size: 9.5px;
  font-weight: 500;
  letter-spacing: 0.02em;
  line-height: 1.3;
  color: #4b5563;
}

.eb-table tfoot td {
  background: var(--green-soft);
  border-color: var(--green-mid);
}

.eb-table tfoot .total-label {
  text-align: right;
  font-size: 11px;
  font-weight: 800;
  color: #0f172a;
}

.eb-table tfoot .total-amt {
  font-size: 12px;
  font-weight: 800;
  color: #0a4a3e;
}

.eb-table-sm th,
.eb-table-sm td {
  padding: 8px 10px;
  font-size: 11px;
}

.eb-optional-box {
  display: flex;
  flex-wrap: wrap;
  gap: 8px 18px;
  padding: 12px 14px;
  border: 1px solid var(--green-mid);
  border-radius: 8px;
  font-size: 12px;
  color: #374151;
}

.eb-job {
  margin: 0 0 8px;
  font-size: 11px;
  color: var(--muted);
}

.eb-remarks {
  margin: 0;
  padding: 12px 14px;
  border: 1px solid var(--green-mid);
  border-radius: 8px;
  background: #fafafa;
  font-size: 12px;
  line-height: 1.55;
  white-space: pre-wrap;
}

.eb-spacer {
  flex: 1 1 auto;
  min-height: 84px;
}

/* Signatures */
.eb-footer {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
  margin-top: auto;
  padding-top: 13px;
}

.eb-sign {
  text-align: center;
}

.eb-sign-space {
  min-height: 36px;
}

.eb-sign-line {
  height: 0;
  border-bottom: 1.5px solid #374151;
  margin: 8px 0 10px;
}

.eb-sign-name {
  margin: 0;
  min-height: 36px;
  display: flex;
  align-items: flex-end;
  justify-content: center;
  font-size: 13px;
  font-weight: 750;
  color: var(--ink);
}

.eb-sign-role {
  margin: 0;
  display: inline-flex;
  align-items: center;
  gap: 7px;
  font-size: 12px;
  font-weight: 700;
  color: var(--green);
}

.eb-sign i {
  font-size: 13px;
}

/* Bottom decorative bar */
.eb-bottom-bar {
  position: relative;
  margin-top: 22px;
  height: 4px;
  background: var(--green);
}

.eb-bottom-notch {
  position: absolute;
  right: 0;
  bottom: 0;
  width: 28px;
  height: 14px;
  background: var(--green);
  clip-path: polygon(0 100%, 100% 100%, 100% 0, 35% 0);
}

@media (max-width: 640px) {
  .eb-meta-grid {
    grid-template-columns: 1fr;
  }

  .eb-meta-split {
    height: 1px;
    width: 100%;
  }

  .eb-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .eb-divider {
    display: none;
  }

  .eb-logo-col {
    width: auto;
    padding: 0 0 10px;
  }
}
</style>

<style>
@media print {
  .eb-sheet {
    width: 100% !important;
    max-width: none !important;
    min-height: calc(297mm - 20mm) !important;
    height: auto !important;
    padding: 2mm 3mm 4mm !important;
    margin: 0 !important;
    box-shadow: none !important;
    overflow: visible !important;
  }

  .eb-company-name {
    font-size: 22px !important;
  }

  .eb-title-wrap {
    margin-top: 11px !important;
  }

  .eb-bill-no-wrap {
    margin-top: 6px !important;
  }

  .eb-bill-no {
    padding: 3px 10px !important;
  }

  .eb-bill-no-value {
    font-size: 15px !important;
  }

  .eb-title-badge {
    min-width: 240px !important;
    font-size: 16px !important;
  }

  .eb-meta {
    margin-top: 8px !important;
    overflow: visible !important;
    flex-shrink: 0 !important;
    height: auto !important;
    max-height: none !important;
  }

  .eb-meta-col {
    overflow: visible !important;
    padding: 4px 9px !important;
    gap: 3px !important;
  }

  .eb-meta-value {
    line-height: 1.35 !important;
    color: #1a1a1a !important;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }

  .eb-section {
    margin-top: 11px !important;
    flex-shrink: 0 !important;
  }

  .eb-section-title {
    margin-bottom: 3px !important;
    gap: 5px !important;
  }

  .eb-table th,
  .eb-table td {
    padding: 4px 7px !important;
    line-height: 1.3 !important;
  }

  .eb-table thead th {
    padding: 4px 7px !important;
    font-size: 9.5px !important;
  }

  .eb-table-particulars thead th {
    padding-top: 4px !important;
    padding-bottom: 4px !important;
    background: #0d5c4d !important;
    color: #ffffff !important;
    border-color: #0a4a3e !important;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }

  .eb-table-particulars tbody td {
    border-color: #b7ddd2 !important;
  }

  .eb-table-particulars tbody tr:nth-child(even) td {
    background: #e8f5f1 !important;
  }

  .eb-table-particulars .col-sl {
    background: #dff0ea !important;
    color: #0a4a3e !important;
  }

  .eb-table-particulars .col-desc .desc-main {
    font-size: 11px !important;
    font-weight: 700 !important;
    color: #111111 !important;
  }

  .eb-table-particulars .col-amt {
    font-size: 11.5px !important;
    font-weight: 800 !important;
    color: #0a4a3e !important;
    background: #f4faf8 !important;
  }

  .eb-table-particulars tfoot td {
    background: #e8f5f1 !important;
    border-color: #b7ddd2 !important;
  }

  .eb-table-particulars tfoot .total-label {
    font-weight: 700 !important;
    color: #0a4a3e !important;
  }

  .eb-table-particulars tfoot .total-amt {
    font-weight: 900 !important;
    color: #0a4a3e !important;
  }

  .desc-main,
  .desc-sub,
  .desc-bill-no {
    line-height: 1.3 !important;
  }

  .desc-sub,
  .desc-bill-no {
    margin-top: 1px !important;
    font-size: 9px !important;
    color: #0d5c4d !important;
  }

  .eb-table-particulars .col-desc .desc-candidate {
    display: inline !important;
    font-size: 8.5px !important;
    font-weight: 400 !important;
    color: #4b5563 !important;
  }

  .eb-spacer {
    flex: 1 1 auto !important;
    min-height: 50px !important;
  }

  .eb-footer {
    margin-top: auto !important;
    padding-top: 11px !important;
  }

  .eb-sign-name,
  .eb-sign-space {
    min-height: 10px !important;
  }

  .eb-bottom-bar {
    margin-top: 20px !important;
  }
}
</style>
