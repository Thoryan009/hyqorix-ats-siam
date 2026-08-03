<template>
  <Teleport to="body">
    <div
      v-if="isVisible"
      class="fixed inset-0 bg-gray-500/70 flex items-center justify-center z-50"
    >
      <div :class="`bg-white rounded-lg shadow-lg p-6 w-full overflow-y-scroll custom-scrollbar mx-4 ${className}`">
        <div class="flex justify-between items-center">
          <h3 class="text-lg font-semibold">{{ title }} </h3>
          <button @click="close" class="text-gray-900 cursor-pointer text-3xl hover:text-primary">
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
