<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <PageTitle>Income List</PageTitle>
        <p class="mt-1 text-sm text-gray-500">
          Review collected income by category and head
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <router-link
          :to="{ path: '/finance/payment-received', query: { tab: 'sale_entry', receive_type: 'income' } }"
        >
          <BaseButton v-can="'transaction.view'" class="bg-primary text-white hover:opacity-90">
            <i class="fa fa-plus mr-1"></i> Operating Income
          </BaseButton>
        </router-link>
        <router-link to="/finance/income-management">
          <BaseButton
            v-can="'transaction.view'"
            :className="'border border-gray-300 bg-white text-gray-800 hover:bg-gray-50'"
          >
            <i class="fa fa-tags mr-1"></i> Income Setup
          </BaseButton>
        </router-link>
      </div>
    </PageHeader>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
      <div
        v-for="card in summaryCards"
        :key="card.title"
        class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm"
      >
        <div class="flex items-start justify-between gap-3">
          <div>
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
      <div class="border-b border-gray-100 px-4 py-4">
        <h3 class="text-base font-semibold text-gray-900">Income List</h3>
        <p class="mt-1 text-sm text-gray-500">
          Other income recorded from Payment / Received
        </p>
      </div>

      <div class="p-4">
        <IncomeCollectionsPanel />
      </div>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import IncomeCollectionsPanel from './components/payment/IncomeCollectionsPanel.vue'
import { useIncomeCollectionStore } from '@/finance/store/incomeCollectionStore'
import { formatCurrency } from '@/finance/utils/billUtils'

const collectionStore = useIncomeCollectionStore()

const thisMonthCount = computed(() => {
  const now = new Date()
  const month = now.getMonth()
  const year = now.getFullYear()

  return collectionStore.collections.filter((row) => {
    const raw = row.collection_date_raw || row.collection_date
    if (!raw) return false
    const date = new Date(raw)
    return date.getMonth() === month && date.getFullYear() === year
  }).length
})

const summaryCards = computed(() => [
  {
    title: 'Total Collections',
    value: collectionStore.collections.length,
    subtitle: 'Recorded operating income entries',
    icon: 'fa fa-list-alt',
    iconBg: 'bg-blue-50',
    iconColor: 'text-blue-600',
  },
  {
    title: 'This Month',
    value: thisMonthCount.value,
    subtitle: 'Entries in current month',
    icon: 'fa fa-calendar',
    iconBg: 'bg-emerald-50',
    iconColor: 'text-emerald-600',
  },
  {
    title: 'Total Collected',
    value: formatCurrency(collectionStore.totalCollectedAmount),
    subtitle: 'All operating income amounts',
    icon: 'fa fa-money',
    iconBg: 'bg-violet-50',
    iconColor: 'text-violet-600',
  },
])

onMounted(() => {
  collectionStore.fetchCollections(true)
})
</script>
