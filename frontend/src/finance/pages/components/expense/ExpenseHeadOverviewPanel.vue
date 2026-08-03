<template>
  <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
    <div
      v-for="group in groupedHeads"
      :key="group.category.id"
      class="overflow-hidden rounded-xl border border-gray-100 bg-linear-to-br from-white to-gray-50 shadow-sm transition-shadow hover:shadow-md"
    >
      <div class="border-b border-gray-100 bg-white px-4 py-3">
        <div class="flex items-center justify-between gap-2">
          <div class="flex items-center gap-2">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary/10">
              <i class="fa fa-tags text-primary"></i>
            </div>
            <h4 class="font-semibold text-gray-900">{{ group.category.name }}</h4>
          </div>
          <span class="rounded-full bg-primary/10 px-2.5 py-1 text-xs font-semibold text-primary">
            {{ group.heads.length }} heads
          </span>
        </div>
        <p class="mt-2 text-xs text-gray-500">
          Total base price: {{ formatCurrency(group.totalBasePrice) }}
        </p>
      </div>

      <ul class="max-h-80 space-y-2 overflow-y-auto p-4">
        <li
          v-for="head in group.heads"
          :key="head.id"
          class="flex items-center justify-between gap-2 rounded-lg border border-gray-100 bg-white px-3 py-2 text-sm"
        >
          <span class="font-medium text-gray-800">{{ head.name }}</span>
          <div class="flex items-center gap-2">
            <span class="font-semibold text-gray-600">{{ formatCurrency(head.base_price) }}</span>
            <button
              v-can="'account_setup.edit'"
              type="button"
              class="rounded-md bg-green-50 px-2 py-1 text-xs font-semibold text-green-700 hover:bg-green-100"
              @click="goToBillEntry(group.category.id, head.id)"
            >
              Expense Entry
            </button>
          </div>
        </li>
      </ul>

      <div class="border-t border-gray-100 bg-white px-4 py-3">
        <button
          type="button"
          class="text-sm font-semibold text-primary hover:text-primary/80"
          @click="$emit('view-heads', group.category.id)"
        >
          Manage heads
          <i class="fa fa-arrow-right ml-1"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useExpenseHeadStore } from '@/finance/store/expenseHeadStore'
import { useExpenseCategoryStore } from '@/finance/store/expenseCategoryStore'
import { formatCurrency } from '@/finance/utils/billUtils'

defineEmits(['view-heads'])

const router = useRouter()
const headStore = useExpenseHeadStore()
const categoryStore = useExpenseCategoryStore()

const goToBillEntry = (categoryId, headId) => {
  router.push({
    path: '/finance/bill-generation',
    query: {
      category_id: String(categoryId),
      head_id: String(headId),
    },
  })
}

const categoryMap = computed(() =>
  Object.fromEntries(categoryStore.categories.map((category) => [category.id, category.name]))
)

const enrichedHeads = computed(() =>
  headStore.heads.map((head) => ({
    ...head,
    category_name: categoryMap.value[head.category_id] ?? '—',
  }))
)

const groupedHeads = computed(() =>
  categoryStore.categories.map((category) => {
    const heads = enrichedHeads.value.filter((head) => head.category_id === category.id)

    return {
      category,
      heads,
      totalBasePrice: heads.reduce((sum, head) => sum + Number(head.base_price || 0), 0),
    }
  })
)

onMounted(async () => {
  await Promise.all([categoryStore.fetchCategories(), headStore.fetchHeads()])
})
</script>
