<template>
  <button
    v-bind="$attrs"
    class="w-full py-3 rounded-lg text-white font-semibold text-base bg-primary hover:bg-primary-hover transition-all shadow-md hover:shadow-lg disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer transform hover:-translate-y-0.5"
  >
    <slot></slot>
  </button>
</template>

<script setup></script>
