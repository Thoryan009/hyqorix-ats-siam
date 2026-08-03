<template>
  <div class="flex gap-3  px-1 sm:px-0">
    <button
      v-for="tab in tabs"
      :key="tab.value"
      @click="selectTab(tab.value)"
      class="flex items-center gap-2 px-3 py-2 sm:px-4 sm:py-2 rounded-lg border text-xs sm:text-sm font-medium whitespace-nowrap transition min-h-[40px] cursor-pointer"
      :class="getTabClasses(tab, modelValue)"
    >
      <span class="truncate max-w-[100px] sm:max-w-none">{{ tab.label }}</span>

      <span
        class="px-2 py-0.5 text-[10px] sm:text-xs rounded-full"
        :class="getTabBadgeClasses(tab, modelValue)"
      >{{ tab.count ?? 0 }}</span>
    </button>
  </div>
</template>

<script setup>
import { getTabClasses, getTabBadgeClasses } from '@/modules/application/utils/tabStyles'

defineProps({
  tabs: {
    type: Array,
    default: () => [],
  },
  modelValue: {
    type: [String, Number],
    default: null,
  },
})

const emit = defineEmits(['update:modelValue'])

const selectTab = (value) => {
  emit('update:modelValue', value) // For v-model
}
</script>
