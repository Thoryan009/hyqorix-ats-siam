<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <PageTitle>Accounts</PageTitle>
        <p class="mt-1 text-sm text-gray-500">{{ pageSubtitle }}</p>
      </div>

      <BaseButton
        v-if="activeTab === 'accounts'"
        v-can="'finance_account.create'"
        class="whitespace-nowrap"
        @click="store.handleToggleModal('add')"
      >
        <i class="fa fa-plus mr-1"></i> Add New Account
      </BaseButton>

      <BaseButton
        v-if="createAccountAction"
        v-can="'finance_account.create'"
        class="whitespace-nowrap"
        @click="createAccountAction.onClick()"
      >
        <i class="fa fa-plus mr-1"></i> {{ createAccountAction.label }}
      </BaseButton>
    </PageHeader>

    <div class="mb-6 space-y-3">
      <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
        <button
          v-for="group in accountNavGroups"
          :key="group.id"
          type="button"
          class="flex items-center gap-3 rounded-xl border px-3 py-3 text-left transition-all"
          :class="
            activeNavGroupId === group.id
              ? 'border-primary bg-primary-light! text-primary shadow-sm ring-1 ring-primary'
              : 'border-gray-200 bg-white text-gray-700 hover:border-primary/40 hover:bg-gray-50'
          "
          @click="setActiveNavGroup(group.id)"
        >
          <span
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg"
            :class="
              activeNavGroupId === group.id ? 'bg-primary/15 text-primary' : 'bg-gray-100 text-gray-500'
            "
          >
            <i :class="group.icon"></i>
          </span>
          <span class="min-w-0">
            <span class="block text-sm font-semibold leading-tight">{{ group.label }}</span>
            <span class="mt-0.5 block truncate text-xs opacity-70">{{ group.description }}</span>
          </span>
        </button>
      </div>

      <div
        v-if="activeNavGroupTabs.length > 1"
        class="flex flex-wrap gap-1.5 rounded-xl border border-gray-100 bg-white p-1.5 shadow-sm"
      >
        <button
          v-for="tab in activeNavGroupTabs"
          :key="tab.id"
          type="button"
          class="rounded-lg px-3.5 py-2 text-sm font-medium transition-all"
          :class="
            activeTab === tab.id
              ? 'bg-primary text-white shadow-sm'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
          "
          @click="setActiveTab(tab.id)"
        >
          <i :class="[tab.icon, 'mr-1.5 opacity-80']"></i>{{ tab.shortLabel || tab.label }}
        </button>
      </div>
    </div>

    <template v-if="activeTab === 'accounts'">
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
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

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
      <!-- Main Table Area -->
      <div class="xl:col-span-9">
        <div class="rounded-lg bg-white shadow-sm">
          <!-- Filters -->
          <div class="flex flex-col gap-3 border-b border-gray-100 p-4 sm:flex-row sm:flex-wrap sm:items-end">
            <div class="flex flex-1 flex-col sm:min-w-[200px]">
              <label class="mb-1 text-sm text-gray-700">Search</label>
              <BaseInput v-model="filters.search" placeholder="Search accounts..." />
            </div>

            <div class="flex flex-col sm:min-w-[160px]">
              <label class="mb-1 text-sm text-gray-700">Account Type</label>
              <BaseSelect
                v-model="filters.accountType"
                :options="accountTypeOptions"
                placeholder="All Types"
              />
            </div>

            <div class="flex flex-col sm:min-w-[140px]">
              <label class="mb-1 text-sm text-gray-700">Status</label>
              <BaseSelect
                v-model="filters.status"
                :options="accountStatusFilterOptions"
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

          <BaseTable
            v-if="!store.isLoading"
            :columns="columns"
            :rows="store.accounts"
            :current-page="page"
            :per-page="perPage"
            show-actions
            scrollable
            selectable
            :selected-ids="selectedIds"
            @toggleAll="(checked) => toggleAll(store.accounts, checked)"
            @toggleRow="toggleRow"
          >
            <template #cell-account_name="{ row }">
              <div class="flex items-center gap-3">
                <div
                  class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary/10"
                >
                  <i :class="[row.icon || 'fa fa-bank', 'text-primary']"></i>
                </div>
                <div>
                  <p class="font-medium text-gray-900">{{ row.account_name }}</p>
                  <p class="text-xs text-gray-500">{{ row.account_label }}</p>
                </div>
              </div>
            </template>

            <template #cell-account_type="{ row }">
              <span
                class="rounded-full px-3 py-1 text-xs font-semibold"
                :class="accountTypeClass(row.account_type)"
              >
                {{ row.account_type }}
              </span>
            </template>

            <template #cell-current_balance="{ row }">
              <span
                class="font-semibold"
                :class="
                  isApplicantLedgerAmountDebit(row.current_balance)
                    ? 'text-red-700'
                    : 'text-green-700'
                "
              >
                {{ formatApplicantLedgerAmount(row.current_balance, formatCurrency) }}
              </span>
            </template>

            <template #cell-status="{ row }">
              <span
                class="rounded-full px-3 py-1 text-xs font-semibold"
                :class="
                  row.status === 'Active'
                    ? 'bg-green-100 text-green-700'
                    : 'bg-red-100 text-red-700'
                "
              >
                {{ row.status }}
              </span>
            </template>

            <template #cell-created_at="{ row }">
              <span class="text-sm text-gray-600">{{ row.created_at || '-' }}</span>
            </template>

            <template #actions="{ row }">
              <div class="flex items-center justify-center gap-1">
                <router-link
                  v-can="'finance_account.view'"
                  :to="`/finance/accounts/${row.id}/ledger`"
                >
                  <BaseTableButton
                    icon="fa fa-book"
                    variant="primary"
                    title="Ledger"
                  />
                </router-link>
                <BaseTableButton
                  v-can="'finance_account.edit'"
                  icon="fa fa-pencil"
                  variant="success"
                  title="Edit"
                  @click="onEdit(row)"
                />
                <BaseTableButton
                  v-if="canDeleteAccount(row)"
                  v-can="'finance_account.delete'"
                  icon="fa fa-trash"
                  variant="danger"
                  title="Delete"
                  @click="confirmDelete(row.id)"
                />
              </div>
            </template>
          </BaseTable>

          <BaseTableSkeleton v-else :columns="columns" show-actions selectable />

          <BasePagination
            v-if="!store.isLoading"
            :total="total"
            :showing="showing"
            :links="links"
            :per-page="perPage"
            @update:page="setPage"
            @update:perPage="setPerPage"
          />
        </div>
      </div>

      <!-- Right Sidebar -->
      <div class="space-y-6 xl:col-span-3">
        <!-- Balance Summary -->
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
          <h3 class="mb-4 text-base font-semibold text-gray-900">Account Balance Summary</h3>
          <div class="flex items-center justify-between text-sm">
            <span class="text-gray-500">Total Balance</span>
            <span class="text-lg font-semibold text-gray-800">
              {{ formatCurrency(store.summary.totalCurrentBalance) }}
            </span>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
          <h3 class="mb-4 text-base font-semibold text-gray-900">Quick Actions</h3>
          <ul class="space-y-2">
            <li v-for="action in quickActions" :key="action.label">
              <button
                v-can="'finance_account.view'"
                type="button"
                class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm text-gray-700 transition-colors hover:bg-primary/5 hover:text-primary"
                @click="action.onClick?.()"
              >
                <i :class="[action.icon, 'w-4 text-center text-primary']"></i>
                {{ action.label }}
              </button>
            </li>
          </ul>
        </div>
      </div>
    </div>
    </template>
    <PartyAccountsPanel v-else-if="activePartyType" :party-type="activePartyType" />

    <ExpenseCostAccountsPanel
      v-else-if="activeExpenseCostType"
      :cost-type="activeExpenseCostType"
    />

    <IncomeAccountsPanel
      v-else-if="activeIncomeType"
      :income-type="activeIncomeType"
    />

    <BankManagementPanel v-else-if="activeTab === 'banks'" />

    <template v-if="activeTab === 'accounts'">
      <AddModal v-can="'transaction.view'" />
      <EditModal v-can="'transaction.view'" />
      <TransferModal
        v-can="'transaction.view'"
        :is-visible="showTransferModal"
        @close="showTransferModal = false"
      />
      <DepositWithdrawModal
        v-can="'transaction.view'"
        :is-visible="showDepositWithdrawModal"
        @close="showDepositWithdrawModal = false"
      />
    </template>
  </SectionHeader>
</template>

<script setup>
import { computed, defineAsyncComponent, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import BaseTableSkeleton from '@/shared/components/base/BaseTableSkeleton.vue'
import { useAccountStore } from '../store/accountStore'
import { useAgentAccountStore } from '../store/agentAccountStore'
import { usePartyAccountsStore } from '../store/partyAccountsStore'
import { useExpenseCostAccountsStore } from '../store/expenseCostAccountsStore'
import { useIncomeAccountsStore } from '../store/incomeAccountsStore'
import { accountStatusFilterOptions, accountTypeOptions } from '../data/accountData'
import { formatCurrency } from '../utils/billUtils'
import {
  formatApplicantLedgerAmount,
  isApplicantLedgerAmountDebit,
} from '../utils/partyLedgerCsvUtils'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useBulkDelete } from '@/shared/composables/useBulkDelete'
import { useDeleteWithConfirm } from '@/shared/composables/useDeleteWithConfirm'
import { toast } from '@/shared/config/toastConfig'
import { fetchCapitalAccount, fetchSaleAccount, fetchBillsReceivableLedgerAccount } from '@/finance/services/financeAccountService'
import {
  expenseCostAccountConfigs,
  expenseCostTabIds,
  getExpenseCostTypeFromTab,
} from '../config/expenseCostAccountConfigs'
import {
  incomeAccountConfigs,
  incomeAccountTabIds,
  getIncomeTypeFromTab,
} from '../config/incomeAccountConfigs'
import {
  partyAccountConfigs,
  partyTabIds,
  getPartyTypeFromTab,
  isPartyAccountsType,
} from '../config/partyAccountConfigs'

const AddModal = defineAsyncComponent(() => import('./components/accounts/AddModal.vue'))
const EditModal = defineAsyncComponent(() => import('./components/accounts/EditModal.vue'))
const TransferModal = defineAsyncComponent(() => import('./components/accounts/TransferModal.vue'))
const DepositWithdrawModal = defineAsyncComponent(
  () => import('./components/accounts/DepositWithdrawModal.vue')
)
const BankManagementPanel = defineAsyncComponent(
  () => import('./components/accounts/BankManagementPanel.vue')
)
const PartyAccountsPanel = defineAsyncComponent(
  () => import('./components/accounts/PartyAccountsPanel.vue')
)
const ExpenseCostAccountsPanel = defineAsyncComponent(
  () => import('./components/accounts/ExpenseCostAccountsPanel.vue')
)
const IncomeAccountsPanel = defineAsyncComponent(
  () => import('./components/accounts/IncomeAccountsPanel.vue')
)

const store = useAccountStore()
const agentAccountStore = useAgentAccountStore()
const partyAccountsStore = usePartyAccountsStore()
const expenseCostAccountsStore = useExpenseCostAccountsStore()
const incomeAccountsStore = useIncomeAccountsStore()
const route = useRoute()
const router = useRouter()

const activeTab = ref('accounts')

const expenseShortLabels = {
  direct_cost: 'Direct Expense',
  client_recruitment_cost: 'Client Recruitment',
  operating_cost: 'Operating Expense',
}

const incomeShortLabels = {
  client_income: 'Client Income',
  other_income: 'Operating Income',
}

const accountNavGroups = [
  {
    id: 'company',
    label: 'Company',
    description: 'Main payment accounts',
    icon: 'fa fa-bank',
    tabs: [{ id: 'accounts', label: 'Main Accounts', shortLabel: 'Main Accounts', icon: 'fa fa-bank' }],
  },
  {
    id: 'expense',
    label: 'Expense',
    description: 'Direct, recruitment & operating',
    icon: 'fa fa-arrow-circle-up',
    tabs: Object.entries(expenseCostAccountConfigs).map(([key, config]) => ({
      id: config.tabId,
      label: config.label,
      shortLabel: expenseShortLabels[key] || config.costTypeLabel,
      icon: config.rowIcon,
    })),
  },
  {
    id: 'income',
    label: 'Income',
    description: 'Client & operating income',
    icon: 'fa fa-arrow-circle-down',
    tabs: Object.entries(incomeAccountConfigs).map(([key, config]) => ({
      id: config.tabId,
      label: config.label,
      shortLabel: incomeShortLabels[key] || config.incomeTypeLabel,
      icon: config.rowIcon,
    })),
  },
  {
    id: 'parties',
    label: 'Parties',
    description: 'Agent, vendor, client & more',
    icon: 'fa fa-users',
    tabs: Object.values(partyAccountConfigs).map((config) => ({
      id: config.tabId,
      label: config.label,
      shortLabel: config.partyLabelPlural || config.partyLabel,
      icon: config.rowIcon,
    })),
  },
]

const findNavGroupIdByTab = (tabId) =>
  accountNavGroups.find((group) => group.tabs.some((tab) => tab.id === tabId))?.id ?? 'company'

const activeNavGroupId = computed(() => {
  if (activeTab.value === 'banks') return 'company'
  return findNavGroupIdByTab(activeTab.value)
})

const activeNavGroupTabs = computed(() => {
  const group = accountNavGroups.find((item) => item.id === activeNavGroupId.value)
  return group?.tabs ?? []
})

const setActiveNavGroup = (groupId) => {
  const group = accountNavGroups.find((item) => item.id === groupId)
  if (!group?.tabs?.length) return

  const alreadyInGroup = group.tabs.some((tab) => tab.id === activeTab.value)
  if (alreadyInGroup) return

  setActiveTab(group.tabs[0].id)
}

const activePartyType = computed(() => getPartyTypeFromTab(activeTab.value))
const activeExpenseCostType = computed(() => getExpenseCostTypeFromTab(activeTab.value))
const activeIncomeType = computed(() => getIncomeTypeFromTab(activeTab.value))

const createAccountAction = computed(() => {
  const expenseCostType = activeExpenseCostType.value
  if (expenseCostType) {
    const config = expenseCostAccountConfigs[expenseCostType]
    return {
      label: `Create ${config.costTypeLabel} Account`,
      onClick: () => expenseCostAccountsStore.openCreateModal(expenseCostType),
    }
  }

  const incomeType = activeIncomeType.value
  if (incomeType) {
    const config = incomeAccountConfigs[incomeType]
    return {
      label: `Create ${config.incomeTypeLabel} Account`,
      onClick: () => incomeAccountsStore.openCreateModal(incomeType),
    }
  }

  const partyType = activePartyType.value
  if (!partyType) return null

  const config = partyAccountConfigs[partyType]
  if (!config?.actions?.create) return null

  return {
    label: `Create ${config.partyLabel} Account`,
    onClick: () => {
      if (partyType === 'agent') {
        agentAccountStore.openCreateModal()
        return
      }

      if (isPartyAccountsType(partyType)) {
        partyAccountsStore.openCreateModal(partyType)
      }
    },
  }
})

const pageSubtitle = computed(() => {
  if (activeTab.value === 'banks') {
    return 'Manage bank master records linked to payment accounts'
  }

  const expenseCostType = activeExpenseCostType.value
  if (expenseCostType) {
    const config = expenseCostAccountConfigs[expenseCostType]
    return `Create and manage ${config.costTypeLabel.toLowerCase()} accounts from expense heads`
  }

  const incomeType = activeIncomeType.value
  if (incomeType) {
    const config = incomeAccountConfigs[incomeType]
    return `Create and manage ${config.incomeTypeLabel.toLowerCase()} accounts from income heads`
  }

  const partyType = activePartyType.value
  if (partyType) {
    const config = partyAccountConfigs[partyType]
    return `Manage ${config.partyLabelPlural.toLowerCase()}, balances, and related finance actions`
  }

  return 'Manage payment accounts, balances, and bank master records'
})

const setActiveTab = (tabId) => {
  activeTab.value = tabId

  const query = { ...route.query }
  if (tabId === 'accounts') {
    delete query.tab
  } else {
    query.tab = tabId
  }

  router.replace({ query })
}

const applyRouteTab = () => {
  const tab = route.query.tab
  if (tab === 'banks' || partyTabIds.includes(tab) || expenseCostTabIds.includes(tab) || incomeAccountTabIds.includes(tab)) {
    activeTab.value = tab
    return
  }

  activeTab.value = 'accounts'
}

watch(() => route.query.tab, applyRouteTab, { immediate: true })

const showTransferModal = ref(false)
const showDepositWithdrawModal = ref(false)

const { onEdit } = useCrudTable(store, [])

const filters = reactive({
  search: '',
  accountType: '',
  status: 'Active',
})

const columns = [
  { key: 'account_name', label: 'Account Name' },
  { key: 'account_type', label: 'Account Type' },
  { key: 'current_balance', label: 'Balance' },
  { key: 'status', label: 'Status' },
  { key: 'created_at', label: 'Created Date' },
]

const quickActions = [
  { label: 'Bank Account Transfer', icon: 'fa fa-exchange', onClick: () => { showTransferModal.value = true } },
  {
    label: 'Deposit / Withdraw',
    icon: 'fa fa-money',
    onClick: () => { showDepositWithdrawModal.value = true },
  },
  {
    label: 'Capital Ledger',
    icon: 'fa fa-balance-scale',
    onClick: openCapitalLedger,
  },
  {
    label: 'Sale Ledger',
    icon: 'fa fa-shopping-cart',
    onClick: openSaleLedger,
  },
  {
    label: 'Sale Receivable',
    icon: 'fa fa-file-text-o',
    onClick: openBillsReceivableLedger,
  },
  {
    label: 'Manage Banks',
    icon: 'fa fa-university',
    onClick: () => {
      setActiveTab('banks')
    },
  },
]

async function openCapitalLedger() {
  try {
    const account = await fetchCapitalAccount()
    const accountId = Number(account?.id)
    if (!accountId) {
      toast.error('Capital Ledger is not available.')
      return
    }
    router.push(`/finance/accounts/${accountId}/ledger`)
  } catch (error) {
    toast.error(error?.message || 'Failed to open Capital Ledger.')
  }
}

async function openSaleLedger() {
  try {
    const account = await fetchSaleAccount()
    const accountId = Number(account?.id)
    if (!accountId) {
      toast.error('Sale Ledger is not available.')
      return
    }
    router.push(`/finance/accounts/${accountId}/ledger`)
  } catch (error) {
    toast.error(error?.message || 'Failed to open Sale Ledger.')
  }
}

async function openBillsReceivableLedger() {
  try {
    const account = await fetchBillsReceivableLedgerAccount()
    const accountId = Number(account?.id)
    if (!accountId) {
      toast.error('Sale Receivable is not available.')
      return
    }
    router.push(`/finance/accounts/${accountId}/ledger`)
  } catch (error) {
    toast.error(error?.message || 'Failed to open Sale Receivable.')
  }
}

const summaryCards = computed(() => [
  {
    title: 'Total Accounts',
    value: store.summary.totalAccounts,
    subtitle: `Active Accounts ${store.summary.activeAccounts}`,
    icon: 'fa fa-university',
    iconBg: 'bg-blue-50',
    iconColor: 'text-blue-600',
  },
  {
    title: 'Total Balance',
    value: formatCurrency(store.summary.totalCurrentBalance),
    subtitle: 'All accounts combined',
    icon: 'fa fa-money',
    iconBg: 'bg-green-50',
    iconColor: 'text-green-600',
  },
  {
    title: 'Active Accounts',
    value: store.summary.activeAccounts,
    subtitle: 'Active and usable',
    icon: 'fa fa-bar-chart',
    iconBg: 'bg-emerald-50',
    iconColor: 'text-emerald-600',
  },
  {
    title: 'Inactive Accounts',
    value: store.summary.inactiveAccounts,
    subtitle: 'Not in use',
    icon: 'fa fa-ban',
    iconBg: 'bg-red-50',
    iconColor: 'text-red-500',
  },
  {
    title: "This Month's Transactions",
    value: formatCurrency(store.summary.thisMonthTransactions),
    subtitle: 'Total Debit + Credit',
    icon: 'fa fa-clock-o',
    iconBg: 'bg-amber-50',
    iconColor: 'text-amber-600',
  },
])

const page = ref(1)
const perPage = ref(10)
const total = computed(() => store.paginationMeta.total ?? 0)
const showing = computed(() => store.paginationMeta.to ?? 0)
const links = computed(() => store.paginationMeta.links ?? [])

const setPage = (value) => {
  if (value && value !== page.value) {
    page.value = value
  }
}

const setPerPage = (value) => {
  perPage.value = Number(value)
  page.value = 1
}

const loadActiveTabData = async () => {
  if (activeTab.value === 'accounts') {
    await Promise.all([
      store.fetchAccountsPage(page.value, perPage.value, filters),
      store.fetchSummary(),
    ])
    return
  }

  const partyType = activePartyType.value
  if (partyType) {
    if (partyType === 'agent') {
      await agentAccountStore.fetchAccounts(true)
      return
    }

    if (isPartyAccountsType(partyType)) {
      await partyAccountsStore.fetchAccounts(partyType, true)
    }
    return
  }

  const expenseCostType = activeExpenseCostType.value
  if (expenseCostType) {
    await expenseCostAccountsStore.fetchAccounts(expenseCostType, true)
    return
  }

  const incomeType = activeIncomeType.value
  if (incomeType) {
    await incomeAccountsStore.fetchAccounts(incomeType, true)
  }
}

let searchDebounceTimer = null

watch(activeTab, () => {
  loadActiveTabData()
}, { immediate: true })

watch(
  () => [page.value, perPage.value],
  () => {
    if (activeTab.value === 'accounts') {
      loadActiveTabData()
    }
  }
)

watch(
  () => [filters.accountType, filters.status],
  () => {
    if (activeTab.value !== 'accounts') return

    if (page.value !== 1) {
      page.value = 1
      return
    }

    loadActiveTabData()
  }
)

watch(
  () => filters.search,
  () => {
    if (activeTab.value !== 'accounts') return

    clearTimeout(searchDebounceTimer)
    searchDebounceTimer = setTimeout(() => {
      page.value = 1
      loadActiveTabData()
    }, 300)
  }
)

const hasActiveFilters = computed(
  () =>
    Boolean(filters.search || filters.accountType || (filters.status && filters.status !== 'Active')),
)

const resetFilters = () => {
  filters.search = ''
  filters.accountType = ''
  filters.status = 'Active'
  page.value = 1
  loadActiveTabData()
}

const removeMutation = {
  mutateAsync: async (id) => {
    const account = store.accounts.find((item) => item.id === Number(id))
    if (account && Number(account.current_balance) > 0) {
      const message = 'Accounts with balance greater than 0 cannot be deleted.'
      toast.error(message)
      throw new Error(message)
    }

    const result = await store.deleteAccount(id)
    if (!result.ok) {
      toast.error(result.message)
      throw new Error(result.message)
    }
    toast.success(`${store.moduleName} operation successful`)
  },
}

const removeItemsMutation = {
  mutate: async (ids, { onSuccess, onError } = {}) => {
    try {
      const blocked = ids.filter((id) => {
        const account = store.accounts.find((item) => item.id === Number(id))
        return account && Number(account.current_balance) > 0
      })

      if (blocked.length) {
        const message = 'Accounts with balance greater than 0 cannot be deleted.'
        toast.error(message)
        throw new Error(message)
      }

      const result = await store.deleteAccounts(ids)
      if (!result.ok) {
        toast.error(result.message)
        throw new Error(result.message)
      }
      toast.success(`${store.moduleName} operation successful`)
      onSuccess?.()
    } catch (error) {
      onError?.(error)
    }
  },
}

const canDeleteAccount = (account) => Number(account?.current_balance ?? 0) <= 0

const { confirmDelete } = useDeleteWithConfirm(removeMutation)

const { selectedIds, toggleAll, toggleRow, bulkDelete } = useBulkDelete(removeItemsMutation, {
  confirmText: 'Are you sure you want to delete selected accounts?',
})

const accountTypeClass = (type) => {
  const classes = {
    Cash: 'bg-amber-100 text-amber-700',
    Bank: 'bg-blue-100 text-blue-700',
    'Mobile Banking': 'bg-purple-100 text-purple-700',
    Card: 'bg-indigo-100 text-indigo-700',
    Other: 'bg-gray-100 text-gray-700',
  }

  return classes[type] || 'bg-gray-100 text-gray-700'
}

onMounted(async () => {
  await store.fetchActiveAccounts()
})
</script>
