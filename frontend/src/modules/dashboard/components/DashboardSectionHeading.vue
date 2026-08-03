<template>
  <div class="mb-3">
    <h2 class="text-base font-semibold flex items-center text-black">
      <div v-if="icon"
        class="w-7 h-7 rounded-md flex items-center justify-center mr-2 shrink-0"
        :class="iconClass"
      >
        <i  :class="[icon, 'text-white text-xs']"></i>
      </div>
      {{ title }}
    </h2>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },
  icon: {
    type: String,
    required: true,
  },
  iconClass: {
    type: String,
    default: 'bg-primary',
  },
})
</script>
