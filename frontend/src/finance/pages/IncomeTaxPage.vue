<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <PageTitle>Income Tax</PageTitle>
        <p class="mt-1 text-sm text-gray-500">
          Record annual income tax against net profit / loss from the Income Statement
        </p>
      </div>
    </PageHeader>

    <div
      class="mb-6 flex flex-col gap-4 rounded-xl border border-gray-100 bg-white p-4 shadow-sm sm:flex-row sm:items-end sm:justify-between"
    >
      <div class="flex flex-col sm:min-w-45">
        <label class="mb-1 text-sm font-medium text-gray-700">Assessment Year</label>
        <select
          v-model.number="selectedYear"
          class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
          @change="loadYearContext"
        >
          <option v-for="year in yearOptions" :key="year" :value="year">{{ year }}</option>
        </select>
      </div>
      <div class="flex flex-wrap items-end gap-3">
        <div class="flex flex-col sm:min-w-50">
          <label class="mb-1 text-sm font-medium text-gray-700">Default Tax Rate (%)</label>
          <div class="flex items-center gap-2">
            <BaseInput
              v-if="isEditingDefaultRate"
              v-model.number="draftDefaultTaxRate"
              type="number"
              min="0"
              max="100"
              step="0.01"
            />
            <div
              v-else
              class="flex h-10 min-w-24 items-center rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm font-semibold tabular-nums text-gray-900"
            >
              {{ Number(defaultTaxRate).toFixed(2) }}%
            </div>
            <template v-if="isEditingDefaultRate">
              <BaseButton
                type="button"
                class="bg-primary text-white hover:opacity-90"
                @click="saveDefaultTaxRate"
              >
                Save
              </BaseButton>
              <BaseButton
                type="button"
                :className="'border border-gray-300 bg-white text-gray-800 hover:bg-gray-50'"
                @click="cancelDefaultTaxRateEdit"
              >
                Cancel
              </BaseButton>
            </template>
            <BaseButton
              v-else
              type="button"
              :className="'border border-gray-300 bg-white text-gray-800 hover:bg-gray-50'"
              @click="startDefaultTaxRateEdit"
            >
              <i class="fa fa-pencil mr-1"></i>
              Edit
            </BaseButton>
          </div>
        </div>
        <BaseButton
          class="bg-primary text-white hover:opacity-90"
          :disabled="contextLoading"
          @click="loadYearContext"
        >
          <i class="fa fa-refresh mr-1"></i>
          {{ contextLoading ? 'Loading...' : 'Refresh' }}
        </BaseButton>
        <router-link
          :to="{
            path: '/finance/reports/income-statement',
            query: {
              from_date: `${selectedYear}-01-01`,
              to_date: `${selectedYear}-12-31`,
            },
          }"
          class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-800 hover:bg-gray-50"
        >
          <i class="fa fa-balance-scale mr-2"></i>
          View Income Statement
        </router-link>
      </div>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <div
        v-for="card in summaryCards"
        :key="card.title"
        class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm"
      >
        <p class="text-sm text-gray-500">{{ card.title }}</p>
        <p class="mt-1 text-2xl font-bold tabular-nums" :class="card.valueClass || 'text-gray-900'">
          {{ card.value }}
        </p>
        <p v-if="card.subtitle" class="mt-1 text-xs text-gray-400">{{ card.subtitle }}</p>
      </div>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-6 xl:grid-cols-5">
      <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm xl:col-span-2">
        <div class="mb-4 flex items-start justify-between gap-3">
          <div>
            <h3 class="text-base font-semibold text-gray-900">
              {{ editingEntry ? `Update ${selectedYear} Entry` : `New Entry for ${selectedYear}` }}
            </h3>
            <p class="mt-1 text-sm text-gray-500">
              Only one income tax record is allowed per calendar year.
            </p>
          </div>
          <span
            class="rounded-full px-2.5 py-1 text-xs font-semibold"
            :class="
              isFormEditable
                ? (editingEntry ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700')
                : 'bg-gray-50 text-gray-700'
            "
          >
            {{
              isFormEditable
                ? editingEntry
                  ? 'Editing'
                  : 'Creating'
                : editingEntry
                  ? 'View'
                  : 'Read Only'
            }}
          </span>
        </div>

        <div
          v-if="!yearContext.is_profit"
          class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800"
        >
          Income Statement shows a net loss for {{ selectedYear }}. Tax may still be recorded if
          required.
        </div>

        <form class="space-y-4" @submit.prevent="handleSubmit">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="flex flex-col">
              <label class="mb-1 text-sm text-gray-700">Tax Amount (BDT)</label>
              <BaseInput
                v-if="isFormEditable"
                v-model="form.tax_amount"
                type="number"
                min="0"
                step="0.01"
                placeholder="0.00"
                required
                @input="onTaxAmountInput"
              />
              <div
                v-else
                class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm font-medium tabular-nums text-gray-900"
              >
                {{ formatCurrency(form.tax_amount) }}
              </div>
            </div>
            <div class="flex flex-col">
              <label class="mb-1 text-sm text-gray-700">Tax Rate (%)</label>
              <BaseInput
                v-if="isFormEditable"
                v-model="form.tax_rate"
                type="number"
                min="0"
                max="100"
                step="0.01"
                placeholder="0.00"
                @input="onTaxRateInput"
              />
              <div
                v-else
                class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm font-medium tabular-nums text-gray-900"
              >
                {{ form.tax_rate ? `${Number(form.tax_rate).toFixed(2)}%` : '—' }}
              </div>
            </div>
          </div>

          <div class="flex flex-col">
            <label class="mb-1 text-sm text-gray-700">Payment Date</label>
            <BaseInput v-if="isFormEditable" v-model="form.payment_date" type="date" />
            <div
              v-else
              class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm font-medium text-gray-900"
            >
              {{ form.payment_date || '—' }}
            </div>
          </div>

          <div class="flex flex-col">
            <label class="mb-1 text-sm text-gray-700">Notes</label>
            <textarea
              v-if="isFormEditable"
              v-model="form.notes"
              rows="3"
              class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
              placeholder="Challan no, remarks, or assessment details..."
            ></textarea>
            <div
              v-else
              class="min-h-20 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-900"
            >
              {{ form.notes || '—' }}
            </div>
          </div>

          <div
            class="rounded-lg border border-dashed border-gray-200 bg-gray-50 px-3 py-3 text-sm text-gray-600"
          >
            <div class="flex items-center justify-between gap-3">
              <span>Net Profit / Loss (from Income Statement)</span>
              <span
                class="font-semibold tabular-nums"
                :class="yearContext.is_profit ? 'text-emerald-700' : 'text-red-700'"
              >
                {{ formatSignedCurrency(yearContext.net_profit, yearContext.is_profit) }}
              </span>
            </div>
            <div class="mt-2 flex items-center justify-between gap-3">
              <span>Estimated Profit After Tax</span>
              <span class="font-semibold tabular-nums text-gray-900">
                {{ formatCurrency(profitAfterTax) }}
              </span>
            </div>
          </div>

          <div class="flex flex-wrap justify-end gap-2 border-t border-gray-100 pt-4">
            <template v-if="isFormEditable">
              <BaseButton
                type="button"
                :className="'border border-gray-300 bg-white text-gray-800 hover:bg-gray-50'"
                :disabled="saving"
                @click="resetForm"
              >
                Cancel
              </BaseButton>
              <BaseButton
                v-can="editingEntry ? 'income_tax.edit' : 'income_tax.create'"
                type="submit"
                class="bg-primary text-white hover:opacity-90"
                :disabled="saving || contextLoading"
              >
                <i :class="['fa mr-1', saving ? 'fa-spinner fa-spin' : 'fa-save']"></i>
                {{
                  saving
                    ? 'Saving...'
                    : editingEntry
                      ? 'Update Income Tax'
                      : 'Save Income Tax'
                }}
              </BaseButton>
            </template>
            <template v-else>
              <BaseButton
                v-can="editingEntry ? 'income_tax.edit' : 'income_tax.create'"
                type="button"
                class="bg-primary text-white hover:opacity-90"
                :disabled="contextLoading"
                @click="isFormEditable = true"
              >
                {{
                  editingEntry ? 'Edit Income Tax' : 'Add Income Tax'
                }}
              </BaseButton>
            </template>
          </div>
        </form>
      </div>

      <div class="rounded-xl border border-gray-100 bg-white shadow-sm xl:col-span-3">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
          <div>
            <h3 class="text-base font-semibold text-gray-900">Income Tax Register</h3>
            <p class="mt-1 text-sm text-gray-500">Historical year-wise tax entries</p>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left font-semibold text-gray-700">Year</th>
                <th class="px-4 py-3 text-right font-semibold text-gray-700">Net Profit / Loss</th>
                <th class="px-4 py-3 text-right font-semibold text-gray-700">
                  Profit After Tax
                </th>
                <th class="px-4 py-3 text-right font-semibold text-gray-700">Tax Amount</th>
                <th class="px-4 py-3 text-right font-semibold text-gray-700">Rate</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-700">Payment Date</th>
                <th class="px-4 py-3 text-right font-semibold text-gray-700">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
              <tr v-if="listLoading">
                <td colspan="7" class="px-4 py-10 text-center text-gray-500">
                  Loading income tax entries...
                </td>
              </tr>
              <tr v-else-if="!entries.length">
                <td colspan="7" class="px-4 py-10 text-center text-gray-500">
                  No income tax entries yet. Select a year and save the first record.
                </td>
              </tr>
              <tr
                v-for="entry in entries"
                :key="entry.id"
                class="hover:bg-gray-50"
                :class="entry.year === selectedYear ? 'bg-emerald-50/40' : ''"
              >
                <td class="px-4 py-3 font-semibold text-gray-900">{{ entry.year }}</td>
                <td
                  class="px-4 py-3 text-right tabular-nums font-medium"
                  :class="entry.is_profit ? 'text-emerald-700' : 'text-red-700'"
                >
                  {{ formatSignedCurrency(entry.net_profit, entry.is_profit) }}
                </td>
                <td
                  class="px-4 py-3 text-right tabular-nums font-medium"
                  :class="entry.is_profit ? 'text-emerald-700' : 'text-red-700'"
                >
                  {{ formatSignedCurrency(entry.profit_after_tax, entry.is_profit) }}
                </td>
                <td class="px-4 py-3 text-right tabular-nums text-gray-900">
                  {{ formatCurrency(entry.tax_amount) }}
                </td>
                <td class="px-4 py-3 text-right tabular-nums text-gray-700">
                  {{ entry.tax_rate != null ? `${Number(entry.tax_rate).toFixed(2)}%` : '—' }}
                </td>
                <td class="px-4 py-3 text-gray-700">
                  {{ entry.payment_date || '—' }}
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center justify-end gap-2">
                    <button
                      type="button"
                      class="rounded-md px-2 py-1 text-sm text-primary hover:bg-primary/10"
                      title="Open year"
                      @click="selectYear(entry.year)"
                    >
                      <i class="fa fa-folder-open"></i>
                    </button>
                    <button
                      v-can="'income_tax.edit'"
                      type="button"
                      class="rounded-md px-2 py-1 text-sm text-amber-700 hover:bg-amber-50"
                      title="Edit"
                      @click="selectYear(entry.year, true)"
                    >
                      <i class="fa fa-pencil"></i>
                    </button>
                    <button
                      v-can="'income_tax.delete'"
                      type="button"
                      class="rounded-md px-2 py-1 text-sm text-red-600 hover:bg-red-50"
                      title="Delete"
                      :disabled="deletingId === entry.id"
                      @click="handleDelete(entry)"
                    >
                      <i
                        :class="[
                          'fa',
                          deletingId === entry.id ? 'fa-spinner fa-spin' : 'fa-trash',
                        ]"
                      ></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import {
  deleteItem,
  fetchAll,
  fetchYearContext,
  submitData,
  updateData,
} from '@/finance/services/incomeTaxService'
import { formatCurrency } from '@/finance/utils/billUtils'
import { toast } from '@/shared/config/toastConfig'

const currentYear = new Date().getFullYear()
const selectedYear = ref(currentYear)
const DEFAULT_TAX_RATE_KEY = 'finance_income_tax_default_rate'
const defaultTaxRate = ref(Number(localStorage.getItem(DEFAULT_TAX_RATE_KEY)) || 15)
const draftDefaultTaxRate = ref(defaultTaxRate.value)
const isEditingDefaultRate = ref(false)
const contextLoading = ref(false)
const listLoading = ref(false)
const saving = ref(false)
const deletingId = ref(null)
const entries = ref([])
const editingEntry = ref(null)
const isFormEditable = ref(false)
const isHydratingForm = ref(false)

const yearContext = reactive({
  net_profit: 0,
  is_profit: true,
  gross_profit: 0,
  total_income: 0,
  total_operating_expense: 0,
  from_date: '',
  to_date: '',
})

const form = reactive({
  tax_amount: '',
  tax_rate: '',
  payment_date: '',
  notes: '',
})

const yearOptions = computed(() => {
  const years = []
  for (let year = currentYear + 1; year >= 2000; year -= 1) {
    years.push(year)
  }
  return years
})

const profitAfterTax = computed(() => {
  const net = Number(yearContext.net_profit) || 0
  const tax = Number(form.tax_amount) || 0
  return net - tax
})

const summaryCards = computed(() => [
  {
    title: yearContext.is_profit ? 'Net Profit' : 'Net Loss',
    value: formatCurrency(Math.abs(yearContext.net_profit)),
    valueClass: yearContext.is_profit ? 'text-emerald-700' : 'text-red-700',
    subtitle: `From Income Statement · ${selectedYear.value}`,
  },
  {
    title: 'Gross Profit',
    value: formatCurrency(yearContext.gross_profit),
    valueClass: yearContext.gross_profit >= 0 ? 'text-emerald-700' : 'text-red-700',
    subtitle: 'Statement summary',
  },
  {
    title: 'Tax Amount',
    value: formatCurrency(editingEntry.value?.tax_amount ?? form.tax_amount),
    valueClass: 'text-gray-900',
    subtitle: editingEntry.value ? 'Saved for this year' : 'Not saved yet',
  },
  {
    title: 'Profit After Tax',
    value: formatCurrency(profitAfterTax.value),
    valueClass: profitAfterTax.value >= 0 ? 'text-emerald-700' : 'text-red-700',
    subtitle: 'Net profit − tax amount',
  },
])

function formatSignedCurrency(amount, isProfit = true) {
  const value = Number(amount) || 0
  const abs = formatCurrency(Math.abs(value))
  if (value === 0) return abs
  return isProfit || value > 0 ? abs : `(${abs})`
}

function getTaxableBase() {
  const net = Number(yearContext.net_profit) || 0
  return net > 0 ? net : 0
}

function calcTaxAmountFromRate(rate) {
  const base = getTaxableBase()
  const taxRate = Number(rate)
  if (!base || Number.isNaN(taxRate) || taxRate < 0) return ''
  return (base * (taxRate / 100)).toFixed(2)
}

function calcTaxRateFromAmount(amount) {
  const base = getTaxableBase()
  const taxAmount = Number(amount)
  if (!base || Number.isNaN(taxAmount) || taxAmount < 0) return ''
  return ((taxAmount / base) * 100).toFixed(4)
}

function applyNewYearDefaults() {
  form.tax_rate = String(defaultTaxRate.value)
  form.tax_amount = calcTaxAmountFromRate(defaultTaxRate.value)
  form.payment_date = ''
  form.notes = ''
}

function fillFormFromEntry(entry) {
  form.tax_amount = entry?.tax_amount != null ? String(entry.tax_amount) : ''
  form.tax_rate = entry?.tax_rate != null ? String(entry.tax_rate) : ''
  form.payment_date = entry?.payment_date_raw || ''
  form.notes = entry?.notes || ''
}

function resetForm() {
  if (editingEntry.value) {
    fillFormFromEntry(editingEntry.value)
    isFormEditable.value = false
    return
  }
  applyNewYearDefaults()
  isFormEditable.value = false
}

function startDefaultTaxRateEdit() {
  draftDefaultTaxRate.value = Number(defaultTaxRate.value)
  isEditingDefaultRate.value = true
}

function cancelDefaultTaxRateEdit() {
  draftDefaultTaxRate.value = Number(defaultTaxRate.value)
  isEditingDefaultRate.value = false
}

function saveDefaultTaxRate() {
  const next = Number(draftDefaultTaxRate.value)
  if (Number.isNaN(next) || next < 0 || next > 100) {
    toast.error('Enter a valid default tax rate between 0 and 100.')
    return
  }

  defaultTaxRate.value = next
  localStorage.setItem(DEFAULT_TAX_RATE_KEY, String(next))
  isEditingDefaultRate.value = false
  toast.success('Default tax rate saved.')

  // Apply to new (unsaved) year entry and recalculate tax amount.
  if (!editingEntry.value?.id) {
    form.tax_rate = String(next)
    form.tax_amount = calcTaxAmountFromRate(next)
  }
}

function onTaxAmountInput() {
  if (isHydratingForm.value) return
  form.tax_rate = calcTaxRateFromAmount(form.tax_amount)
}

function onTaxRateInput() {
  if (isHydratingForm.value) return
  form.tax_amount = calcTaxAmountFromRate(form.tax_rate)
}

async function loadYearContext() {
  contextLoading.value = true
  isHydratingForm.value = true
  try {
    const payload = await fetchYearContext(selectedYear.value)
    const data = payload?.data ?? payload

    yearContext.net_profit = Number(data?.net_profit || 0)
    yearContext.is_profit = data?.is_profit !== false
    yearContext.gross_profit = Number(data?.gross_profit || 0)
    yearContext.total_income = Number(data?.total_income || 0)
    yearContext.total_operating_expense = Number(data?.total_operating_expense || 0)
    yearContext.from_date = data?.from_date || ''
    yearContext.to_date = data?.to_date || ''

    editingEntry.value = data?.entry || null

    if (editingEntry.value) {
      fillFormFromEntry(editingEntry.value)
    } else {
      applyNewYearDefaults()
    }
    isFormEditable.value = false
  } catch (error) {
    toast.error(error?.message || 'Failed to load year summary.')
  } finally {
    contextLoading.value = false
    isHydratingForm.value = false
  }
}

async function loadEntries() {
  listLoading.value = true
  try {
    const { data, error } = await fetchAll(1, 100)
    if (error) throw error
    entries.value = data?.data ?? []
  } catch (error) {
    toast.error(error?.message || 'Failed to load income tax entries.')
  } finally {
    listLoading.value = false
  }
}

async function selectYear(year, editable = false) {
  selectedYear.value = year
  await loadYearContext()
  isFormEditable.value = editable
}

async function handleSubmit() {
  const taxAmount = Number(form.tax_amount)
  if (Number.isNaN(taxAmount) || taxAmount < 0) {
    toast.error('Enter a valid tax amount.')
    return
  }

  saving.value = true
  try {
    const payload = {
      year: selectedYear.value,
      tax_amount: taxAmount,
      tax_rate: form.tax_rate === '' || form.tax_rate == null ? null : Number(form.tax_rate),
      payment_date: form.payment_date || null,
      notes: form.notes || null,
      status: 'active',
    }

    if (editingEntry.value?.id) {
      await updateData({ ...payload, id: editingEntry.value.id })
      toast.success('Income tax entry updated.')
    } else {
      await submitData(payload)
      toast.success('Income tax entry saved.')
    }

    await Promise.all([loadYearContext(), loadEntries()])
  } catch (error) {
    const message =
      error?.errors?.year?.[0] ||
      error?.message ||
      'Failed to save income tax entry.'
    toast.error(message)
  } finally {
    saving.value = false
  }
}

async function handleDelete(entry) {
  if (!entry?.id) return
  const confirmed = window.confirm(
    `Delete income tax entry for ${entry.year}? This cannot be undone.`
  )
  if (!confirmed) return

  deletingId.value = entry.id
  try {
    await deleteItem(entry.id)
    toast.success(`Income tax entry for ${entry.year} deleted.`)
    if (selectedYear.value === entry.year) {
      editingEntry.value = null
      resetForm()
    }
    await Promise.all([loadYearContext(), loadEntries()])
  } catch (error) {
    toast.error(error?.message || 'Failed to delete income tax entry.')
  } finally {
    deletingId.value = null
  }
}

onMounted(async () => {
  await Promise.all([loadYearContext(), loadEntries()])
})
</script>
