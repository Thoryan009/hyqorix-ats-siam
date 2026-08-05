<template>
  <div>
    <CreateAgentAccountModal v-if="partyType === 'agent'" />
    <EditAgentAccountModal v-if="partyType === 'agent'" />

    <template v-if="isPartyAccountsType(partyType)">
      <CreateBankAccountModal v-if="partyType === 'banks'" />
      <CreatePartyAccountModal v-else :party-type="partyType" />
      <EditPartyAccountModal :party-type="partyType" />
    </template>
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
      <div class="xl:col-span-9">
        <div class="rounded-lg bg-white shadow-sm">
          <div class="flex flex-col gap-3 border-b border-gray-100 p-4 sm:flex-row sm:flex-wrap sm:items-end">
            <div class="flex flex-1 flex-col sm:min-w-[200px]">
              <label class="mb-1 text-sm text-gray-700">Search</label>
              <BaseInput v-model="filters.search" :placeholder="config.searchPlaceholder" />
            </div>

            <div class="flex flex-col sm:min-w-[160px]">
              <label class="mb-1 text-sm text-gray-700">{{ amountLabel }}</label>
              <BaseSelect
                v-model="filters.balanceType"
                :options="balanceFilterOptions"
                :placeholder="`All ${amountLabelPlural}`"
              />
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
            <template #cell-party_name="{ row }">
              <div class="flex items-center gap-3">
                <div
                  class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary/10"
                >
                  <i :class="[config.rowIcon, 'text-primary']"></i>
                </div>
                <div>
                  <p class="font-medium text-gray-900">{{ row[config.nameKey] }}</p>
                  <p class="text-xs text-gray-500">{{ row[config.codeKey] }}</p>
                </div>
              </div>
            </template>

            <template #cell-phone="{ row }">
              <span class="text-sm text-gray-600">{{ row.phone }}</span>
            </template>

            <template #cell-passport_no="{ row }">
              <span class="text-sm text-gray-700">{{ row.passport_no || '—' }}</span>
            </template>

            <template #cell-balance="{ row }">
              <span
                class="font-semibold"
                :class="
                  isApplicantLedgerAmountDebit(row.balance) ? 'text-red-700' : 'text-green-700'
                "
              >
                {{ formatApplicantLedgerAmount(row.balance, formatCurrency) }}
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
                {{ row.status ?? 'Active' }}
              </span>
            </template>

            <template #actions="{ row }">
              <div class="flex items-center justify-center gap-1">
                <BaseTableButton
                  v-if="config.actions.edit"
                  v-can="'finance_account.edit'"
                  icon="fa fa-pencil"
                  variant="success"
                  title="Edit"
                  @click="handleEdit(row)"
                />

                <router-link
                  v-if="config.actions.ledger && ledgerPath(row.id)"
                  v-can="'finance_account.view'"
                  :to="ledgerPath(row.id)"
                >
                  <BaseTableButton icon="fa fa-book" variant="primary" title="Ledger" />
                </router-link>

                <router-link
                  v-if="config.actions.payment"
                  v-can="'finance_account.view'"
                  :to="`/finance/agent-accounts/${row.id}/payment`"
                >
                  <BaseTableButton icon="fa fa-money" variant="success" title="Payment" />
                </router-link>

                <router-link
                  v-if="config.actions.generateBill && row.bill_agent_id"
                  v-can="'finance_account.view'"
                  :to="{
                    path: '/finance/agent-bills/generate',
                    query: { agent_id: row.bill_agent_id, from: 'agent-accounts' },
                  }"
                >
                  <BaseTableButton
                    icon="fa fa-file-text-o"
                    variant="info"
                    title="Generate Bill"
                  />
                </router-link>
              </div>
            </template>
          </BaseTable>

          <BasePagination
            v-if="!isLoading"
            :total="filteredRows.length"
            :showing="showing"
            :links="links"
            :per-page="perPage"
            @update:page="setPage"
            @update:perPage="setPerPage"
          />
        </div>
      </div>

      <div class="space-y-6 xl:col-span-3">
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
          <h3 class="mb-4 text-base font-semibold text-gray-900">{{ config.summaryTitle }}</h3>
          <div class="flex items-center justify-between text-sm">
            <span class="text-gray-500">Total {{ amountLabel }}</span>
            <span class="text-lg font-semibold text-gray-800">
              {{ formatCurrency(summary.totalBalance) }}
            </span>
          </div>
        </div>
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
  </div>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import BaseTableSkeleton from '@/shared/components/base/BaseTableSkeleton.vue'
import { useAgentAccountStore } from '@/finance/store/agentAccountStore'
import { usePartyAccountsStore } from '@/finance/store/partyAccountsStore'
import { useFinanceAccountStore } from '@/finance/store/financeAccountStore'
import { getAccountCategoryFromPartyType } from '@/finance/data/accountCategoryCodes'
import { getPartyConfig, isPartyAccountsType } from '@/finance/config/partyAccountConfigs'
import { fetchAgentAdvancedAccount } from '@/finance/services/financeAccountService'
import { formatCurrency } from '@/finance/utils/billUtils'
import {
  formatApplicantLedgerAmount,
  isApplicantLedgerAmountDebit,
} from '@/finance/utils/partyLedgerCsvUtils'
import { partyAccountStatusFilterOptions } from '@/finance/data/partyAccountConstants'
import { toast } from '@/shared/config/toastConfig'
import CreateAgentAccountModal from './CreateAgentAccountModal.vue'
import EditAgentAccountModal from './EditAgentAccountModal.vue'
import CreatePartyAccountModal from './CreatePartyAccountModal.vue'
import CreateBankAccountModal from './CreateBankAccountModal.vue'
import EditPartyAccountModal from './EditPartyAccountModal.vue'

const props = defineProps({
  partyType: {
    type: String,
    required: true,
  },
})

const config = computed(() => getPartyConfig(props.partyType))

const amountLabel = computed(() => (config.value?.useAmountLabel ? 'Amount' : 'Balance'))
const amountLabelPlural = computed(() =>
  config.value?.useAmountLabel ? 'Amounts' : 'Balances'
)

function ledgerPath(accountId) {
  if (typeof config.value.ledgerPath === 'function') {
    return config.value.ledgerPath(accountId)
  }
  return null
}

const agentStore = useAgentAccountStore()
const partyStore = usePartyAccountsStore()
const financeAccountStore = useFinanceAccountStore()
const router = useRouter()

const isLoading = computed(() => {
  const category = getAccountCategoryFromPartyType(props.partyType)
  return category ? financeAccountStore.isCategoryLoading(category) : false
})

const balanceFilterOptions = computed(() => [
  { value: 'positive', label: `Positive ${amountLabel.value}` },
  { value: 'negative', label: `Negative ${amountLabel.value}` },
  { value: 'zero', label: `Zero ${amountLabel.value}` },
])

const statusFilterOptions = partyAccountStatusFilterOptions

const filters = reactive({
  search: '',
  balanceType: '',
  status: 'Active',
})

const columns = computed(() => {
  const baseColumns = [
    { key: 'sl', label: 'SL' },
    { key: 'party_name', label: config.value.tableNameLabel },
  ]

  if (!config.value.hidePhone) {
    baseColumns.push({ key: 'phone', label: 'Phone' })
  }

  for (const column of config.value.extraColumns ?? []) {
    baseColumns.push(column)
  }

  baseColumns.push({ key: 'balance', label: amountLabel.value })
  baseColumns.push({ key: 'status', label: 'Status' })

  return baseColumns
})

const accounts = computed(() => {
  if (props.partyType === 'agent') {
    return agentStore.accounts
  }

  return partyStore.getAccounts(props.partyType)
})

const summary = computed(() => {
  const list = accounts.value
  const totalBalance = list.reduce((sum, account) => sum + Number(account.balance || 0), 0)

  return {
    totalParties: list.length,
    totalBalance,
    positiveBalance: list.filter((account) => Number(account.balance) > 0).length,
    negativeBalance: list.filter((account) => Number(account.balance) < 0).length,
    zeroBalance: list.filter((account) => Number(account.balance) === 0).length,
  }
})

const summaryCards = computed(() => [
  {
    title: `Total ${config.value.partyLabelPlural}`,
    value: summary.value.totalParties,
    subtitle: `Registered ${config.value.partyLabelPlural.toLowerCase()}`,
    icon: 'fa fa-users',
    iconBg: 'bg-blue-50',
    iconColor: 'text-blue-600',
  },
  {
    title: `Total ${amountLabel.value}`,
    value: formatCurrency(summary.value.totalBalance),
    subtitle: config.value.useAmountLabel
      ? 'Combined applicant amount'
      : 'Combined wallet balance',
    icon: 'fa fa-money',
    iconBg: 'bg-green-50',
    iconColor: 'text-green-600',
  },
  {
    title: `Positive ${amountLabel.value}`,
    value: summary.value.positiveBalance,
    subtitle: `${config.value.partyLabelPlural} with credit`,
    icon: 'fa fa-arrow-up',
    iconBg: 'bg-emerald-50',
    iconColor: 'text-emerald-600',
  },
  {
    title: `Negative ${amountLabel.value}`,
    value: summary.value.negativeBalance,
    subtitle: `${config.value.partyLabelPlural} with due amount`,
    icon: 'fa fa-arrow-down',
    iconBg: 'bg-red-50',
    iconColor: 'text-red-500',
  },
  {
    title: `Zero ${amountLabel.value}`,
    value: summary.value.zeroBalance,
    subtitle: config.value.useAmountLabel
      ? 'No outstanding amount'
      : 'No outstanding balance',
    icon: 'fa fa-minus-circle',
    iconBg: 'bg-amber-50',
    iconColor: 'text-amber-600',
  },
])

const handleEdit = (row) => {
  if (props.partyType === 'agent') {
    agentStore.openEditModal(row)
    return
  }

  partyStore.openEditModal(props.partyType, row)
}

const handleCreate = () => {
  if (props.partyType === 'agent') {
    agentStore.openCreateModal()
    return
  }

  partyStore.openCreateModal(props.partyType)
}

const quickActions = computed(() => {
  if (props.partyType === 'agent') {
    return [
      {
        label: 'Create Agent Account',
        icon: 'fa fa-plus',
        onClick: handleCreate,
      },
      {
        label: 'Agent Advanced Ledger',
        icon: 'fa fa-credit-card',
        onClick: openAgentAdvancedLedger,
      },
      {
        label: 'Company Accounts',
        icon: 'fa fa-bank',
        onClick: () => router.replace({ path: '/finance/accounts' }),
      },
    ]
  }

  return [
    {
      label: `Create ${config.value.partyLabel} Account`,
      icon: 'fa fa-plus',
      onClick: handleCreate,
    },
    ...config.value.quickActions.map((action) => ({
      ...action,
      onClick: () => router.push(action.route),
    })),
  ]
})

async function openAgentAdvancedLedger() {
  try {
    const account = await fetchAgentAdvancedAccount()
    const accountId = Number(account?.id)
    if (!accountId) {
      toast.error('Agent Advanced Ledger is not available.')
      return
    }
    router.push(`/finance/accounts/${accountId}/ledger`)
  } catch (error) {
    toast.error(error?.message || 'Failed to open Agent Advanced Ledger.')
  }
}

const page = ref(1)
const perPage = ref(10)
const showing = ref(0)
const links = ref([])

const filteredRows = computed(() => {
  const query = filters.search.trim().toLowerCase()
  const nameKey = config.value.nameKey
  const codeKey = config.value.codeKey

  return accounts.value.filter((account) => {
    const matchesSearch =
      !query ||
      String(account[nameKey] ?? '')
        .toLowerCase()
        .includes(query) ||
      String(account[codeKey] ?? '')
        .toLowerCase()
        .includes(query) ||
      String(account.phone ?? '')
        .toLowerCase()
        .includes(query) ||
      String(account.passport_no ?? '')
        .toLowerCase()
        .includes(query)

    const balance = Number(account.balance)
    const matchesBalance =
      !filters.balanceType ||
      (filters.balanceType === 'positive' && balance > 0) ||
      (filters.balanceType === 'negative' && balance < 0) ||
      (filters.balanceType === 'zero' && balance === 0)

    const status = String(account.status || 'Active')
    const matchesStatus =
      !filters.status ||
      filters.status === 'all' ||
      status.toLowerCase() === String(filters.status).toLowerCase()

    return matchesSearch && matchesBalance && matchesStatus
  })
})

const paginatedRows = computed(() => {
  const start = (page.value - 1) * perPage.value
  return filteredRows.value.slice(start, start + perPage.value).map((row, index) => ({
    ...row,
    sl: start + index + 1,
  }))
})

const hasActiveFilters = computed(() =>
  Boolean(
    filters.search ||
      filters.balanceType ||
      (filters.status && filters.status !== 'Active')
  )
)

const resetFilters = () => {
  filters.search = ''
  filters.balanceType = ''
  filters.status = 'Active'
  page.value = 1
}

const updatePagination = () => {
  const count = filteredRows.value.length
  const lastPage = Math.max(1, Math.ceil(count / perPage.value))
  const to = Math.min(page.value * perPage.value, count)

  showing.value = to
  links.value = Array.from({ length: lastPage }, (_, index) => ({
    label: String(index + 1),
    active: page.value === index + 1,
    url: page.value === index + 1 ? null : '#',
  }))

  if (page.value > lastPage) {
    page.value = lastPage
  }
}

watch([filteredRows, page, perPage, () => accounts.value.length], updatePagination, {
  immediate: true,
})

watch(
  () => props.partyType,
  () => {
    resetFilters()
  }
)

const setPage = (value) => {
  if (value && value !== page.value) {
    page.value = value
  }
}

const setPerPage = (value) => {
  perPage.value = Number(value)
  page.value = 1
}
</script>
