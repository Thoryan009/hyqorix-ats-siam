<template>
  <button
    v-bind="$attrs"
    class="w-full py-3 rounded-xl text-white font-semibold text-lg
           bg-gradient-to-r from-blue-600 to-indigo-600
           hover:from-blue-700 hover:to-indigo-700
           transition-all shadow-lg
           disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer"
  >
    <slot></slot>
  </button>
</template>

<script setup></script>