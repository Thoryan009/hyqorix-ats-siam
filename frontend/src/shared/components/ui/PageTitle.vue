<template>
  <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl lg:text-xl" :class="className">
    <slot />
  </h1>
</template>

<script setup>
defineProps({
  className: {
    type: String,
    default: '',
  },
})
</script>
