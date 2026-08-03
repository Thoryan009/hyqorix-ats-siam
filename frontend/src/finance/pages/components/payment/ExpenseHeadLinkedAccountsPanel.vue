<template>
  <div class="space-y-3 rounded-xl border border-gray-200 bg-gray-50/80 p-4 md:col-span-2">
    <div>
      <h4 class="text-sm font-semibold text-gray-900">Linked Accounts</h4>
      <p class="mt-1 text-xs text-gray-500">
        Select an account from the lists below based on the account types linked to this expense head.
      </p>
    </div>

    <div
      v-for="group in groups"
      :key="group.category"
      class="rounded-lg border border-gray-200 bg-white p-4"
    >
      <p class="mb-3 text-sm font-semibold text-gray-800">{{ group.label }}</p>

      <div v-if="group.accounts.length" class="space-y-2">
        <BaseSelect
          :id="`linked_account_${group.category}`"
          :model-value="selectedCategory === group.category ? selectedAccountId : ''"
          :options="group.accounts"
          :placeholder="`Select ${group.label.toLowerCase()}`"
          @update:modelValue="(value) => emit('select', group.category, value)"
        />

        <ul class="space-y-1.5 rounded-lg border border-gray-100 bg-gray-50 p-2">
          <li
            v-for="account in group.accounts"
            :key="account.id"
            class="flex items-center justify-between gap-3 rounded-md px-2 py-1.5 text-xs"
            :class="
              selectedCategory === group.category && Number(selectedAccountId) === Number(account.id)
                ? 'bg-emerald-50 font-semibold text-emerald-800 ring-1 ring-emerald-200'
                : 'text-gray-700'
            "
          >
            <span class="min-w-0 truncate">{{ account.name }}</span>
            <span
              class="shrink-0 font-medium"
              :class="account.balance >= 0 ? 'text-green-700' : 'text-red-700'"
            >
              {{ formatCurrency(account.balance) }}
            </span>
          </li>
        </ul>
      </div>

      <p v-else class="text-xs text-amber-700">
        No active accounts are available for this account type.
      </p>
    </div>
  </div>
</template>

<script setup>
import { formatCurrency } from '@/finance/utils/billUtils'

defineProps({
  groups: {
    type: Array,
    default: () => [],
  },
  selectedCategory: {
    type: String,
    default: '',
  },
  selectedAccountId: {
    type: [String, Number],
    default: '',
  },
})

const emit = defineEmits(['select'])
</script>
