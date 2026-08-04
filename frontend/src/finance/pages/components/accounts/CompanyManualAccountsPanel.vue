<template>
  <div>
    <CreateCompanyManualAccountModal :manual-type="manualType" />
    <EditCompanyManualAccountModal :manual-type="manualType" />

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <div
        v-for="card in summaryCards"
        :key="card.title"
        class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm"
      >
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <p class="text-sm text-gray-500">{{ card.title }}</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">{{ card.value }}</p>
            <p class="mt-1 text-xs text-gray-400">{{ card.subtitle }}</p>
          </div>
          <div
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg"
            :class="card.iconBg"
          >
            <i :class="[card.icon, card.iconColor, 'text-lg']"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="rounded-lg bg-white shadow-sm">
      <div class="flex flex-col gap-3 border-b border-gray-100 p-4 sm:flex-row sm:flex-wrap sm:items-end">
        <div class="flex flex-1 flex-col sm:min-w-[200px]">
          <label class="mb-1 text-sm text-gray-700">Search</label>
          <BaseInput v-model="filters.search" :placeholder="config.searchPlaceholder" />
        </div>

        <div class="flex flex-col sm:min-w-[140px]">
          <label class="mb-1 text-sm text-gray-700">Status</label>
          <BaseSelect
            v-model="filters.status"
            :options="statusFilterOptions"
            placeholder="Select status"
          />
        </div>

        <BaseButton
          v-if="hasActiveFilters"
          class="bg-gray-600 text-white hover:bg-gray-700"
          @click="resetFilters"
        >
          Reset
        </BaseButton>
      </div>

      <BaseTableSkeleton v-if="isLoading" :columns="columns" show-actions />
      <BaseTable
        v-else
        :columns="columns"
        :rows="paginatedRows"
        :current-page="page"
        :per-page="perPage"
        show-actions
        scrollable
      >
        <template #cell-account_name="{ row }">
          <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100">
              <i :class="[config.rowIcon, 'text-slate-700']"></i>
            </div>
            <p class="font-medium text-gray-900">{{ row.account_name }}</p>
          </div>
        </template>

        <template #cell-balance="{ row }">
          <span class="font-semibold" :class="amountColorClass">
            {{
              formatApplicantLedgerAmount(row.balance ?? row.current_balance, formatCurrency)
            }}
          </span>
        </template>

        <template #cell-status="{ row }">
          <span
            class="rounded-full px-3 py-1 text-xs font-semibold"
            :class="
              row.status === 'Active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
            "
          >
            {{ row.status }}
          </span>
        </template>

        <template #actions="{ row }">
          <div class="flex items-center justify-end gap-2">
            <BaseButton
              class="bg-slate-700 text-white hover:bg-slate-800"
              @click="openLedger(row)"
            >
              <i class="fa fa-book mr-1"></i> Ledger
            </BaseButton>
            <BaseButton
              v-can="'finance_account.edit'"
              class="bg-amber-500 text-white hover:bg-amber-600"
              @click="manualStore.openEditModal(manualType, row)"
            >
              <i class="fa fa-pencil"></i>
            </BaseButton>
            <BaseButton
              v-can="'finance_account.delete'"
              class="bg-red-500 text-white hover:bg-red-600"
              @click="confirmDelete(row)"
            >
              <i class="fa fa-trash"></i>
            </BaseButton>
          </div>
        </template>
      </BaseTable>

      <div
        v-if="!isLoading && !filteredRows.length"
        class="border-t border-gray-100 px-4 py-10 text-center text-sm text-gray-500"
      >
        No {{ config.typeLabel.toLowerCase() }} accounts yet. Create one to get started.
      </div>

      <div
        v-if="filteredRows.length > perPage"
        class="flex items-center justify-between border-t border-gray-100 px-4 py-3"
      >
        <BaseSelect
          :model-value="String(perPage)"
          :options="perPageOptions"
          @update:model-value="(value) => (perPage = Number(value))"
        />
        <div class="flex items-center gap-2">
          <BaseButton
            class="bg-gray-100 text-gray-700 hover:bg-gray-200"
            :disabled="page <= 1"
            @click="page -= 1"
          >
            Prev
          </BaseButton>
          <span class="text-sm text-gray-600">Page {{ page }}</span>
          <BaseButton
            class="bg-gray-100 text-gray-700 hover:bg-gray-200"
            :disabled="page >= totalPages"
            @click="page += 1"
          >
            Next
          </BaseButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useCompanyManualAccountsStore } from '@/finance/store/companyManualAccountsStore'
import { useFinanceAccountStore } from '@/finance/store/financeAccountStore'
import { getCompanyManualAccountConfig } from '@/finance/config/companyManualAccountConfigs'
import { partyAccountStatusFilterOptions } from '@/finance/data/partyAccountConstants'
import { formatCurrency } from '@/finance/utils/billUtils'
import { formatApplicantLedgerAmount } from '@/finance/utils/partyLedgerCsvUtils'
import { showConfirmDialog } from '@/shared/utils/sweetAlertUtils'
import { toast } from '@/shared/config/toastConfig'
import CreateCompanyManualAccountModal from './CreateCompanyManualAccountModal.vue'
import EditCompanyManualAccountModal from './EditCompanyManualAccountModal.vue'
import BaseTableSkeleton from '@/shared/components/base/BaseTableSkeleton.vue'

const props = defineProps({
  manualType: {
    type: String,
    required: true,
  },
})

const router = useRouter()
const manualStore = useCompanyManualAccountsStore()
const financeAccountStore = useFinanceAccountStore()

const config = computed(() => getCompanyManualAccountConfig(props.manualType))

const amountColorClass = computed(() =>
  props.manualType === 'liabilities' ? 'text-red-700' : 'text-green-700'
)

const filters = reactive({
  search: '',
  status: 'Active',
})

const page = ref(1)
const perPage = ref(10)

const statusFilterOptions = partyAccountStatusFilterOptions.map((option) => ({
  id: option.value,
  name: option.label,
}))

const perPageOptions = [
  { id: '10', name: '10 / page' },
  { id: '25', name: '25 / page' },
  { id: '50', name: '50 / page' },
]

const columns = [
  { key: 'account_name', label: 'Account Name' },
  { key: 'balance', label: 'Amount' },
  { key: 'status', label: 'Status' },
]

const isLoading = computed(() =>
  financeAccountStore.isCategoryLoading(config.value.accountCategory)
)

const accounts = computed(() => manualStore.getAccounts(props.manualType))

const filteredRows = computed(() => {
  const search = filters.search.trim().toLowerCase()

  return accounts.value.filter((row) => {
    if (filters.status !== 'all' && row.status !== filters.status) return false
    if (!search) return true

    const haystack = [row.account_name, row.account_label, row.code]
      .filter(Boolean)
      .join(' ')
      .toLowerCase()

    return haystack.includes(search)
  })
})

const totalPages = computed(() => Math.max(1, Math.ceil(filteredRows.value.length / perPage.value)))

const paginatedRows = computed(() => {
  const start = (page.value - 1) * perPage.value
  return filteredRows.value.slice(start, start + perPage.value)
})

const hasActiveFilters = computed(
  () => Boolean(filters.search.trim()) || filters.status !== 'Active'
)

const summaryCards = computed(() => {
  const rows = accounts.value
  const activeCount = rows.filter((row) => row.status === 'Active').length
  const totalBalance = rows.reduce(
    (sum, row) => sum + Number(row.balance ?? row.current_balance ?? 0),
    0
  )

  return [
    {
      title: `Total ${config.value.typeLabel}`,
      value: rows.length,
      subtitle: `${activeCount} active`,
      icon: config.value.rowIcon,
      iconBg: 'bg-slate-50',
      iconColor: 'text-slate-700',
    },
    {
      title: 'Total Amount',
      value: formatCurrency(totalBalance),
      subtitle: 'All accounts combined',
      icon: 'fa fa-money',
      iconBg: 'bg-green-50',
      iconColor: 'text-green-600',
    },
    {
      title: 'Active Accounts',
      value: activeCount,
      subtitle: 'Ready for transactions',
      icon: 'fa fa-check-circle',
      iconBg: 'bg-emerald-50',
      iconColor: 'text-emerald-600',
    },
    {
      title: 'Inactive Accounts',
      value: rows.length - activeCount,
      subtitle: 'Disabled accounts',
      icon: 'fa fa-ban',
      iconBg: 'bg-red-50',
      iconColor: 'text-red-600',
    },
  ]
})

const resetFilters = () => {
  filters.search = ''
  filters.status = 'Active'
  page.value = 1
}

const openLedger = (row) => {
  router.push(`/finance/accounts/${row.id}/ledger`)
}

async function confirmDelete(row) {
  const result = await showConfirmDialog({
    text: `Delete ${row.account_name}? This cannot be undone.`,
  })
  if (!result.isConfirmed) return

  const deleteResult = await manualStore.deleteAccount(props.manualType, row.id)
  if (!deleteResult.ok) {
    toast.error(deleteResult.message)
    return
  }
  toast.success(`${config.value.typeLabel} account deleted`)
}

watch(
  () => [filters.search, filters.status, perPage.value],
  () => {
    page.value = 1
  }
)

watch(
  () => props.manualType,
  async (manualType) => {
    page.value = 1
    await manualStore.fetchAccounts(manualType, true)
  }
)

onMounted(async () => {
  await manualStore.fetchAccounts(props.manualType, true)
})
</script>
