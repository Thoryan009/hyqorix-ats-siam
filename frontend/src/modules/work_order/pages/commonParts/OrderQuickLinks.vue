<template>
  <div class="flex flex-wrap gap-2 sm:gap-4 my-1">
    <router-link :to="{ name: 'Order Details Category List' }"
      class="text-xs sm:text-sm text-primary-dark font-semibold cursor-pointer whitespace-nowrap">
      Demand Letter Details Category
    </router-link>

    <router-link :to="{ name: 'Order Details Head List' }" class="text-xs sm:text-sm text-primary-dark font-semibold cursor-pointer whitespace-nowrap">
      Demand Letter Head Details
    </router-link>
  </div>
</template>

<script setup>
// No props needed
</script>
