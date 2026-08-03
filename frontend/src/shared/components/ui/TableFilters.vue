<template>
  <div class="grid grid-cols-2 sm:flex flex-wrap gap-2 items-stretch sm:items-center">

    <!-- FROM DATE -->
    <div v-if="fromDate" class="flex flex-col w-full sm:w-auto sm:min-w-[150px]">
      <label class="text-gray-800 text-sm sm:text-[15px] mb-1">
        {{ fromDateL }}
      </label>

      <input type="date" v-model="filters.from_date" class="border border-gray-300 rounded-md px-3 py-2 w-full" />
    </div>

    <!-- TO DATE -->
    <div v-if="toDate" class="flex flex-col w-full sm:w-auto sm:min-w-[150px]">
      <label class="text-gray-800 text-sm sm:text-[15px] mb-1">
        {{ toDateL }}
      </label>

      <input type="date" v-model="filters.to_date" class="border border-gray-300 rounded-md px-3 py-2 w-full" />
    </div>

    <!-- SEARCH -->
    <div v-if="showSearch" class="flex flex-col w-full sm:w-auto sm:min-w-[200px] col-span-2 sm:col-span-1">
      <label class="text-gray-800 text-sm sm:text-[15px] mb-1">{{ searchL }}</label>
      <BaseInput v-model="filters.searchQuery" :placeholder="searchL" />
    </div>

    <!-- EXTRA FILTERS (MODULE SPECIFIC) -->
    <slot />

    <!-- RESET -->
    <BaseButton v-if="hasActiveFilters"
      :className="'bg-gray-600 text-white hover:bg-gray-700 w-full sm:w-auto col-span-2 sm:col-span-1 mt-2 sm:mt-auto'"
      @click="$emit('reset')">Reset
    </BaseButton>
  </div>
</template>

<script setup>
import { useTranslate} from '@/shared/composables/useTranslate'
import { computed } from 'vue'

const { t } = useTranslate()

const fromDateL = computed(() => t('shared.filters.from_date'))
const toDateL = computed(() => t('shared.filters.to_date'))
const searchL = computed(() => t('shared.actions.search'))

defineProps({
  filters: Object,
  hasActiveFilters: Boolean,

  showSearch: {
    type: Boolean,
    default: true,
  },
  fromDate: {
    type: Boolean,
    default: true,
  },

  toDate: {
    type: Boolean,
    default: true,
  },

  // fromDateLabel: {
  //   type: String,
  //   default: fromDateL,
  // },

  // toDateLabel: {
  //   type: String,
  //   default: toDateL,
  // },
})

defineEmits(['reset'])
</script>
