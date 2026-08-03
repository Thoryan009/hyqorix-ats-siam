<template>
  <label :for="forId" class="block text-gray-700 font-medium mb-2 text-sm" :class="className" v-bind="$attrs">
    <slot />
  </label>
</template>

<script setup>
defineProps({
  forId: { type: String, default: null },
  className: { type: String, default: 'text-[16px]' },
})
</script>
