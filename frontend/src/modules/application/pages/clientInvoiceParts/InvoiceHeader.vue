<template>
  <div>
    <div class="flex justify-between items-start mb-2 gap-6">
      <!-- Left: Branding -->
      <LeftHeader />

      <!-- Right: Date & Time -->
      <div class="text-right text-sm mt-2">
        <p class="font-semibold">Invoice Generated</p>
        <p>{{ formattedDate }}</p>
        <p>{{ formattedTime }}</p>
      </div>
    </div>

    <!-- Divider -->
    <div class="h-0.5 bg-black"></div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import LeftHeader from '../../shared/component/LeftHeader.vue'

const now = new Date()

const formattedDate = computed(() =>
  now.toLocaleDateString('en-BD', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
)

const formattedTime = computed(() =>
  now.toLocaleTimeString('en-BD', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
  })
)
</script>
