<template>
  <div class="flex justify-between items-center">

    <!-- Left Content -->
    <div>
      <h1 class="text-2xl font-bold text-gray-800">
        {{ title }}
      </h1>

      <p
        v-if="subtitle"
        class="text-sm text-gray-500"
      >
        {{ subtitle }}
      </p>
    </div>

  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },
  subtitle: {
    type: String,
    default: '',
  },
  showExport: {
    type: Boolean,
    default: false,
  },
  exportLabel: {
    type: String,
    default: 'Export',
  },
})

defineEmits(['export'])
</script>
