<template>
  <button
    :type="type"
    :title="title"
    :class="buttonClass"
    @click="$emit('click')"
  >
    <!-- Icon -->
    <i v-if="icon" :class="icon"></i>

    <!-- Slot (optional text) -->
    <span v-if="$slots.default" class="ml-1">
      <slot />
    </span>
  </button>
</template>

<script setup>
import { computed } from "vue"

const props = defineProps({
  icon: {
    type: String,
    default: "",
  },
  variant: {
    type: String,
    default: "primary",
  },
  title: {
    type: String,
    default: "",
  },
  type: {
    type: String,
    default: "button",
  },
})

defineEmits(["click"])

const buttonClass = computed(() => {
  const base =
    "inline-flex items-center cursor-pointer transition duration-200"

  const variants = {
    primary: "text-blue-600 hover:text-blue-800",
    success: "text-green-600 hover:text-green-800",
    danger: "text-red-600 hover:text-red-800",
    info: "text-violet-600 hover:text-violet-800",
  }

  return `${base} ${variants[props.variant] || variants.primary}`
})
</script>
