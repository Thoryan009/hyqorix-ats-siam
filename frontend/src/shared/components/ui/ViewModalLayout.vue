<template>
  <div class="w-full">
    <!-- Scrollable Body -->
    <div class="px-4 pb-4" :style="{ maxHeight: height }">
      <slot />
    </div>
  </div>
</template>

<script setup>
defineProps({
  height: {
    type: String,
    default: '600px',
  },
})
</script>
