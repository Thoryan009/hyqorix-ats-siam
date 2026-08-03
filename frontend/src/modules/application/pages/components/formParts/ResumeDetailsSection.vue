<template>
  <div>
    <div
      class="col-span-1 md:col-span-3 font-semibold text-lg border-b border-gray-300 pb-1"
    >Resume Details</div>
  </div>
</template>

<script setup>
import { useApplicationStore } from '@/modules/application/store/applicationStore'

const store = useApplicationStore()
</script>
