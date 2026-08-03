<template>
  <div class="rounded-lg bg-white p-3 text-center shadow-sm">
    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ label }}</p>
    <p class="mt-1 text-2xl font-bold" :class="colorClass">{{ value }}</p>
  </div>
</template>

<script setup>
defineProps({
  label: String,
  value: [String, Number],
  colorClass: {
    type: String,
    default: "text-blue-600"
  }
});
</script>
