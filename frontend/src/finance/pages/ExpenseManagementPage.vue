<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <PageTitle>Expense Setup</PageTitle>
        <p class="mt-1 text-sm text-gray-500">{{ pageSubtitle }}</p>
      </div>

      <BaseButton
        v-if="headerAction"
        v-can="'account_setup.create'"
        class="whitespace-nowrap"
        @click="headerAction.onClick()"
      >
        <i class="fa fa-plus mr-1"></i>
        {{ headerAction.label }}
      </BaseButton>
    </PageHeader>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
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
        @click="setActiveTab(tab.id)"
      >
        <i :class="[tab.icon, 'mr-1.5']"></i>{{ tab.label }}
      </button>
    </div>

    <div class="rounded-lg bg-white shadow-sm">
      <div class="border-b border-gray-100 px-4 py-4">
        <h3 class="text-base font-semibold text-gray-900">{{ activeTabMeta.title }}</h3>
        <p class="mt-1 text-sm text-gray-500">{{ activeTabMeta.description }}</p>
      </div>

      <div class="p-4">
        <ExpenseHeadListPanel
          v-if="activeTab === 'heads'"
          :initial-category-id="selectedCategoryId"
        />

        <ExpenseCategoryPanel v-else-if="activeTab === 'categories'" embedded />
      </div>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import ExpenseCategoryPanel from './components/expense/ExpenseCategoryPanel.vue'
import ExpenseHeadListPanel from './components/expense/ExpenseHeadListPanel.vue'
import { useExpenseCategoryStore } from '@/finance/store/expenseCategoryStore'
import { useExpenseHeadStore } from '@/finance/store/expenseHeadStore'
import { formatCurrency } from '@/finance/utils/billUtils'

const categoryStore = useExpenseCategoryStore()
const headStore = useExpenseHeadStore()
const route = useRoute()
const router = useRouter()

const activeTab = ref('heads')
const selectedCategoryId = ref('')

const pageTabs = [
  { id: 'heads', label: 'Expense Heads', icon: 'fa fa-list-alt' },
  { id: 'categories', label: 'Expense Categories', icon: 'fa fa-tags' },
]

const tabMeta = {
  heads: {
    title: 'Expense Heads',
    description: 'Manage expense heads and base prices under each category',
    subtitle: 'Manage expense heads with category-wise base prices',
  },
  categories: {
    title: 'Expense Categories',
    description: 'Create and manage expense categories used for expense tracking',
    subtitle: 'Manage expense categories and their status',
  },
}

const activeTabMeta = computed(() => tabMeta[activeTab.value] ?? tabMeta.heads)
const pageSubtitle = computed(() => activeTabMeta.value.subtitle)

const totalBasePrice = computed(() =>
  headStore.heads.reduce((sum, head) => sum + Number(head.base_price || 0), 0)
)

const activeHeadCount = computed(
  () => headStore.heads.filter((head) => head.status === 'Active').length
)

const summaryCards = computed(() => [
  {
    title: 'Expense Categories',
    value: categoryStore.categories.length,
    subtitle: 'Configured category groups',
    icon: 'fa fa-tags',
    iconBg: 'bg-blue-50',
    iconColor: 'text-blue-600',
  },
  {
    title: 'Expense Heads',
    value: headStore.heads.length,
    subtitle: 'Total expense head records',
    icon: 'fa fa-list-alt',
    iconBg: 'bg-emerald-50',
    iconColor: 'text-emerald-600',
  },
  {
    title: 'Active Heads',
    value: activeHeadCount.value,
    subtitle: 'Currently active expense heads',
    icon: 'fa fa-check-circle',
    iconBg: 'bg-amber-50',
    iconColor: 'text-amber-600',
  },
  {
    title: 'Total Base Price',
    value: formatCurrency(totalBasePrice.value),
    subtitle: 'Combined base price amount',
    icon: 'fa fa-money',
    iconBg: 'bg-violet-50',
    iconColor: 'text-violet-600',
  },
])

const headerAction = computed(() => {
  if (activeTab.value === 'heads') {
    return {
      label: 'Add Expense Head',
      onClick: () => headStore.handleToggleModal('add'),
    }
  }

  return null
})

const setActiveTab = (tabId, categoryId = '') => {
  activeTab.value = tabId
  selectedCategoryId.value = categoryId ? String(categoryId) : ''

  const query = { ...route.query, tab: tabId }
  if (categoryId) {
    query.category_id = String(categoryId)
  } else {
    delete query.category_id
  }

  router.replace({ query })
}

const applyRouteTab = () => {
  const tab = route.query.tab
  const categoryId = route.query.category_id

  if (pageTabs.some((item) => item.id === tab)) {
    activeTab.value = tab
  } else {
    activeTab.value = 'heads'
  }

  selectedCategoryId.value = categoryId ? String(categoryId) : ''
}

watch(() => route.query, applyRouteTab, { immediate: true, deep: true })

onMounted(async () => {
  await Promise.all([categoryStore.fetchCategories(), headStore.fetchHeads()])
})
</script>
