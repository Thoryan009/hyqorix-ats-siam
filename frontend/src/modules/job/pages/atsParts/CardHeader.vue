<template>
  <div class="">
    <div class="flex items-start gap-3">
      <!-- Job Title and Work Order -->
      <div class="flex-1 min-w-0">
        <div class="flex items-start gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary text-white shadow-sm flex-shrink-0">
            <slot name="icon">
              <!-- default icon -->
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                />
              </svg>
            </slot>
          </div>

          <div class="truncate flex-1">
            <h2 class="text-base font-bold text-gray-900 truncate">
              {{ title }}
              <span class="text-xs font-medium text-gray-600"> ({{ jobcode }}) </span>
            </h2>

            <p class="text-xs font-medium text-gray-600 truncate">
              {{ subtitle }}
              <span v-if="meta" class="text-gray-700"> - {{ meta }}</span>
            </p>
          </div>
        </div>
      </div>
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
    required: true,
  },
  meta: {
    type: String,
    default: '',
  },
  jobcode: {
    type: String,
    default: '',
  },
})
</script>
