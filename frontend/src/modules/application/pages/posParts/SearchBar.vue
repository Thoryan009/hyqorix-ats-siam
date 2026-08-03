<template>
  <div class="mb-6">
    <div class="relative">
      <input
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        type="text"
        placeholder="Search by name, email, phone, job name..."
        class="w-full rounded-lg border border-gray-300 px-4 py-4 pl-12 text-lg
               focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary"
      />

      <svg
        class="absolute left-4 top-1/2 h-6 w-6 -translate-y-1/2 text-gray-400"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
        />
      </svg>
    </div>
  </div>
</template>

<script setup>
defineProps({
  modelValue: {
    type: String,
    default: '',
  },
})

defineEmits(['update:modelValue'])
</script>
