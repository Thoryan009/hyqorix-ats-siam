<template>
  <Teleport to="body">
    <div
      v-if="isVisible"
      class="fixed inset-0 bg-gray-500/70 flex items-center justify-center z-50"
    >
      <div
        :class="`bg-white rounded-lg shadow-lg p-6 w-full custom-scrollbar mx-4 overflow-y-auto ${className}`"
      >
        <div class="flex items-start justify-between gap-3">
          <h3 class="min-w-0 flex-1 text-base font-semibold leading-snug sm:text-lg">{{ title }}</h3>
          <button
            type="button"
            class="shrink-0 text-3xl leading-none text-gray-900 cursor-pointer hover:text-primary"
            @click="close"
          >
            &times;
          </button>
        </div>
        <div class="mt-4">
          <!-- Content Slot -->
          <slot></slot>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
defineProps({
  isVisible: Boolean,
  title: String,
  className: {
    type: String,
    default: 'xl:max-w-2xl',
  },
  maxWidth: {
    type: String,
    default: '90vw',
  },
})

const emit = defineEmits(['close'])

const close = () => {
  emit('close')
}
</script>

<style scoped>
/* Add additional styles if needed */
</style>
