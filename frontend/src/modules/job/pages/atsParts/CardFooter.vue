<template>
  <div class="border-t border-gray-100 bg-gray-50 px-6 py-4">
    <div class="flex flex-wrap items-center justify-between gap-4 text-sm text-gray-600">
      <!-- Meta Info -->
      <!-- Actions -->
      <div class="flex gap-2">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  createdAt: {
    type: String,
    required: true,
  },
  updatedAt: {
    type: String,
    required: true,
  },
})
</script>
