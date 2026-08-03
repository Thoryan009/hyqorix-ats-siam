<template>
  <section class="min-h-screen bg-gray-50" :class="className">
    <slot />
  </section>
</template>

<script setup>
defineProps({
  className: {
    type: String,
    default: '',
  },
})
</script>
