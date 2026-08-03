<template>
  <div class="rounded-lg bg-white p-12 text-center shadow-sm">
    <slot name="icon">
      <!-- default icon -->
      <svg
        class="mx-auto h-16 w-16 text-gray-400"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
        />
      </svg>
    </slot>

    <h3 class="mt-4 text-lg font-medium text-gray-900">
      <slot name="title">No jobs found</slot>
    </h3>

    <p class="mt-2 text-sm text-gray-500">
      <slot name="description">Get started by creating your first job position.</slot>
    </p>
  </div>
</template>

<script setup>
// No props needed because slots make it flexible
</script>
