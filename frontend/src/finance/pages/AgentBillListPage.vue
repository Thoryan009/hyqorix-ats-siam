<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <PageTitle>Agent Bill List</PageTitle>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <input
          id="agent-bill-csv-import"
          type="file"
          accept=".csv,text/csv"
          class="hidden"
          @change="handleImportCsv"
        />
        <BaseButton class="bg-gray-100 text-gray-800 hover:bg-gray-200" @click="triggerImport">
          <i class="fa fa-upload mr-1"></i> Import CSV/Excel
        </BaseButton>
        <BaseButton class="bg-blue-600 text-white hover:bg-blue-700" @click="handleExportCsv">
          <i class="fa fa-download mr-1"></i> Export Excel/CSV
        </BaseButton>
        <router-link to="/finance/agent-bills/generate">
          <BaseButton v-can="'finance_account.view'" class="bg-green-600 text-white hover:bg-green-700">
            <i class="fa fa-plus mr-1"></i> Generate Bill (Job Wise)
          </BaseButton>
        </router-link>
      </div>
    </PageHeader>

    <div class="rounded-lg bg-white shadow-sm">
      <BaseTable
        :columns="columns"
        :rows="rows"
        :current-page="page"
        :per-page="perPage"
        :show-actions="true"
      >
        <template #cell-bill_date="{ row }">
          {{ formatDisplayDate(row.bill_date) }}
        </template>

        <template #cell-total_amount="{ row }">
          <span class="font-semibold text-green-700">
            {{ formatCurrency(row.total_amount, row.currency) }}
          </span>
        </template>

        <template #cell-status="{ row }">
          <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
            {{ row.status }}
          </span>
        </template>

        <template #actions="{ row }">
          <BaseButton
            class="bg-blue-600 px-3 py-1 text-xs text-white hover:bg-blue-700"
            @click="handleExportSingleBill(row)"
          >
            <i class="fa fa-download mr-1"></i> Export CSV
          </BaseButton>
          <BaseButton
            class="bg-green-600 px-3 py-1 text-xs text-white hover:bg-green-700"
            @click="openBillPreview(row)"
          >
            <i class="fa fa-print mr-1"></i> Print / PDF
          </BaseButton>
        </template>
      </BaseTable>

      <BasePagination
        :total="total"
        :showing="showing"
        :links="links"
        :per-page="perPage"
        @update:page="setPage"
        @update:perPage="setPerPage"
      />
    </div>

    <BillPreviewModal
      :is-visible="showBillPreviewModal"
      :agent="previewContext.agent"
      :job="previewContext.job"
      :selected-candidates="previewContext.selectedCandidates"
      :total-amount="previewContext.totalAmount"
      :bill-no="previewContext.billNo"
      :bill-date="previewContext.billDate"
      @close="showBillPreviewModal = false"
    />
  </SectionHeader>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useAgentBillStore } from '../store/agentBillStore'
import { getBillPreviewContext } from '@/finance/data/agentBillData'
import { formatCurrency, formatDisplayDate } from '@/finance/utils/billUtils'
import {
  downloadAgentBillsCsv,
  downloadSingleAgentBillCsv,
  parseImportedAgentBills,
} from '@/finance/utils/agentBillCsvUtils'
import { useCSVParser } from '@/shared/composables/useCSVParser'
import { clearFileInput } from '@/shared/helpers/file'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import BillPreviewModal from './components/agentBillParts/BillPreviewModal.vue'
import Swal from 'sweetalert2'

const billStore = useAgentBillStore()
const { parseCSV } = useCSVParser()

const columns = [
  { key: 'sl', label: 'SL' },
  { key: 'bill_no', label: 'Bill No' },
  { key: 'bill_date', label: 'Bill Date' },
  { key: 'agent_code', label: 'Agent Code' },
  { key: 'agent_name', label: 'Agent Name' },
  { key: 'job_code', label: 'Job Code' },
  { key: 'job_title', label: 'Job Title' },
  { key: 'candidate_count', label: 'Candidates' },
  { key: 'total_amount', label: 'Total Amount' },
  { key: 'status', label: 'Status' },
]

const page = ref(1)
const perPage = ref(10)
const total = ref(0)
const showing = ref(0)
const links = ref([])
const showBillPreviewModal = ref(false)
const previewContext = ref({
  agent: null,
  job: null,
  selectedCandidates: [],
  totalAmount: 0,
  billNo: '',
  billDate: '',
})

const rows = computed(() => {
  const start = (page.value - 1) * perPage.value
  return billStore.bills.slice(start, start + perPage.value)
})

const updatePagination = () => {
  const count = billStore.bills.length
  const lastPage = Math.max(1, Math.ceil(count / perPage.value))
  const to = Math.min(page.value * perPage.value, count)

  total.value = count
  showing.value = to
  links.value = Array.from({ length: lastPage }, (_, index) => ({
    label: String(index + 1),
    active: page.value === index + 1,
    url: null,
  }))

  if (page.value > lastPage) {
    page.value = lastPage
  }
}

watch([() => billStore.bills.length, page, perPage], updatePagination, { immediate: true })

const setPage = (value) => {
  if (value && value !== page.value) {
    page.value = value
  }
}

const setPerPage = (value) => {
  perPage.value = Number(value)
  page.value = 1
}

function openBillPreview(bill) {
  previewContext.value = getBillPreviewContext(bill)
  showBillPreviewModal.value = true
}

function triggerImport() {
  document.getElementById('agent-bill-csv-import')?.click()
}

function handleExportCsv() {
  downloadAgentBillsCsv(billStore.bills)
}

function handleExportSingleBill(bill) {
  downloadSingleAgentBillCsv(bill)
}

async function handleImportCsv(event) {
  const file = event.target.files?.[0]
  if (!file) return

  try {
    const csvText = await file.text()
    const rows = parseCSV(csvText)
    const parsedBills = parseImportedAgentBills(
      rows,
      billStore.bills.map((bill) => bill.bill_no)
    )

    if (!parsedBills.length) {
      await Swal.fire({
        title: 'Import Failed',
        text: 'No valid bill rows found in the file.',
        icon: 'error',
        confirmButtonColor: '#22C55E',
      })
      return
    }

    const importedCount = billStore.importBills(parsedBills)

    await Swal.fire({
      title: 'Import Successful',
      text: `${importedCount} bill(s) imported successfully.`,
      icon: 'success',
      confirmButtonColor: '#22C55E',
    })
  } catch {
    await Swal.fire({
      title: 'Import Failed',
      text: 'Could not read the CSV file. Please use a valid CSV exported from Excel.',
      icon: 'error',
      confirmButtonColor: '#22C55E',
    })
  } finally {
    clearFileInput(null, 'agent-bill-csv-import')
  }
}
</script>
