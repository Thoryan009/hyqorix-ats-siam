<template>
  <div class="mb-2">
    <div class="mb-1 flex items-center justify-between">
      <h2 class="text-lg font-bold text-gray-900">Selected Item</h2>
      <div class="h-2 w-2 rounded-full bg-green-500" v-if="selectedItem"></div>
    </div>

    <div
      v-if="!selectedItem"
      class="rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 py-4 text-center"
    >
      <svg
        class="mx-auto mb-3 h-12 w-12 text-gray-400"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
        />
      </svg>
      <p class="text-sm font-medium text-gray-500">No item selected</p>
      <p class="mt-1 text-xs text-gray-400">Click on an application to select</p>
    </div>

    <div
      v-else
      class="relative overflow-hidden rounded-lg border-2 border-primary bg-linear-to-br from-primary-light to-primary-50 shadow-lg"
    >
      <!-- Close Button -->
      <button
        @click="$emit('clear')"
        class="absolute right-3 top-3 z-10 rounded-full bg-white p-1.5 text-red-500 shadow-md transition-all hover:bg-red-50 hover:text-red-700 hover:shadow-lg cursor-pointer"
        title="Clear selection"
      >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M6 18L18 6M6 6l12 12"
          />
        </svg>
      </button>

      <!-- Header Section -->
      <div class="border-b border-primary-gradient bg-white/50 px-4 py-3">
        <div class="flex items-start gap-3">
          <div
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-dark text-white shadow-md"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
              />
            </svg>
          </div>
          <div class="flex-1 pr-6">
            <h3 class="text-base font-bold text-gray-900">
              {{ selectedItem.full_name }}
            </h3>
            <p class="mt-0.5 flex items-center gap-1.5 text-xs text-gray-600">
              <svg
                class="h-3.5 w-3.5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                />
              </svg>
              {{ selectedItem.mobile }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  selectedItem: Object, // server data 그대로
});

defineEmits(["clear"]);
</script>
