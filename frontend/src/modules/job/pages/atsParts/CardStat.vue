<template>
  <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-gradient-to-r" :class="bgGradient">
    <span class="text-xs font-semibold uppercase tracking-wide" :class="textClass">{{ label }}</span>
    <span class="font-bold" :class="valueClass">
      <slot>{{ value }}</slot>
    </span>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  label: {
    type: String,
    required: true,
  },
  value: {
    type: [String, Number],
    default: '',
  },
  valueClass: {
    type: String,
    default: 'text-base text-gray-900',
  },
})

const getColorScheme = (label) => {
  const schemes = {
    'Applications': {
      gradient: 'from-blue-100 to-blue-50',
      text: 'text-blue-700',
    },
    'Vacancy': {
      gradient: 'from-purple-100 to-purple-50',
      text: 'text-purple-700',
    },
    'Salary': {
      gradient: 'from-green-100 to-green-50',
      text: 'text-green-700',
    },
  }
  return schemes[label] || schemes['Applications']
}

const colorScheme = computed(() => getColorScheme(props.label))
const bgGradient = computed(() => colorScheme.value.gradient)
const textClass = computed(() => colorScheme.value.text)
</script>
