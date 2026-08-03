<template>
  <button
    :type="type"
    class="w-full rounded-md px-4 py-2 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 sm:w-auto"
    :class="[className, disabled && 'opacity-50 cursor-not-allowed!']"
    :disabled="disabled"
    @click="$emit('click')"
  >
    <slot />
  </button>
</template>

<script setup>
defineProps({
  type: {
    type: String,
    default: 'button',
  },
  className: {
    type: String,
    default: 'bg-primary hover:bg-primary/70 text-white cursor-pointer',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
})
defineEmits(['click'])
</script>
