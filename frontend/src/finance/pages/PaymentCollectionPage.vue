<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <PageTitle>Receipt List</PageTitle>
        <p class="mt-1 text-sm text-gray-500">
          Review saved payment collections by job and candidate
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <router-link :to="{ path: '/finance/payment-received', query: { tab: 'sale_entry' } }">
          <BaseButton v-can="'transaction.view'" class="bg-primary text-white hover:opacity-90">
            <i class="fa fa-plus mr-1"></i> Receive Payment
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
        <h3 class="text-base font-semibold text-gray-900">Receipt List</h3>
        <p class="mt-1 text-sm text-gray-500">
          Collected payments with payer and ledger details
        </p>
      </div>

      <div class="p-4">
        <SaleEntriesPanel />
      </div>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed } from 'vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import SaleEntriesPanel from './components/payment/SaleEntriesPanel.vue'
import { useSaleEntryStore } from '@/finance/store/saleEntryStore'

const saleEntryStore = useSaleEntryStore()

const summaryCards = computed(() => [
  {
    title: 'Total Receipts',
    value: saleEntryStore.totalEntryCount,
    subtitle: 'Recorded payment collections',
    icon: 'fa fa-money',
    iconBg: 'bg-emerald-50',
    iconColor: 'text-emerald-600',
  },
  {
    title: 'This Month',
    value: saleEntryStore.thisMonthEntryCount,
    subtitle: 'Collections in current month',
    icon: 'fa fa-calendar',
    iconBg: 'bg-blue-50',
    iconColor: 'text-blue-600',
  },
  {
    title: 'Total Collected',
    value: formatSaleAmount(saleEntryStore.totalCollectedAmount),
    subtitle: 'All collected payment amounts',
    icon: 'fa fa-money',
    iconBg: 'bg-violet-50',
    iconColor: 'text-violet-600',
  },
])

function formatSaleAmount(amount) {
  return `৳${Number(amount || 0).toLocaleString('en-BD')}`
}
</script>
