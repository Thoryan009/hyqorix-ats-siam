<template>
  <div class="rounded-xl border border-neutral-200 bg-white p-1.5">
    <nav class="flex flex-wrap gap-1.5" aria-label="Employee tabs">
      <button
        v-for="tab in tabs"
        :key="tab"
        type="button"
        class="flex items-center gap-2 rounded-lg px-3.5 py-2 text-sm font-medium transition-all duration-200"
        :class="
          activeTab === tab
            ? tabConfig[tab].activeClass
            : 'text-neutral-500 hover:bg-neutral-100 hover:text-neutral-800'
        "
        @click="$emit('change', tab)"
      >
        <i :class="[tabConfig[tab].icon, 'text-xs']"></i>
        <span>{{ tabLabels[tab] }}</span>
        <span
          v-if="tabConfig[tab].count !== undefined"
          class="ml-0.5 rounded-full px-1.5 py-0.5 text-xs font-bold"
          :class="activeTab === tab ? tabConfig[tab].countClass : 'bg-neutral-100 text-neutral-500'"
        >{{ tabConfig[tab].count }}</span>
      </button>
    </nav>
  </div>
</template>

<script setup>
defineProps({
  tabs: { type: Array, required: true },
  tabLabels: { type: Object, required: true },
  activeTab: { type: String, required: true },
  tabConfig: { type: Object, required: true },
})
defineEmits(['change'])
</script>
