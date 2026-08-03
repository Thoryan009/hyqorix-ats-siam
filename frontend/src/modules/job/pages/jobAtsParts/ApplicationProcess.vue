<template>
  <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-6 pt-5 pb-4 mb-2 mt-2">
    <!-- Pipeline Steps -->
    <div class="flex flex-wrap gap-y-3 gap-x-0">
      <!-- <pre>{{ processes }}</pre> -->
      <div v-for="(process, index) in processes" :key="index" class="flex items-center">
        <!-- Step Chip -->
        <button
          @click="select(process)"
          :class="[
            'group relative flex items-center gap-2 px-3 py-1.5 rounded-lg border transition-all duration-200 cursor-pointer',
            selectedProcessId == process.id
              ? 'bg-primary border-primary text-white shadow-md shadow-primary/20'
              : 'bg-gray-50 border-gray-200 text-gray-900 hover:bg-primary/5 hover:border-primary/40 hover:text-primary',
          ]"
          type="button"
        >
          <!-- Label -->
          <span
            class="text-[14px] font-medium whitespace-nowrap leading-none"
          >{{ t(`ats.process.${process.name}`) }}</span>
          <!-- Count Badge -->
          <span
            v-if="process.count > 0"
            :class="[
              'shrink-0 inline-flex items-center justify-center min-w-8 h-8 px-2 text-md font-black rounded-full leading-none tabular-nums ring-4 ring-white shadow-lg',
              selectedProcessId == process.id
                ? 'bg-emerald-700 text-white shadow-white/20 border border-white/50'
                : 'bg-linear-to-r from-red-500 to-red-700 text-white shadow-red-600/40',
            ]"
          >{{ process.count }}</span>
        </button>

        <!-- Arrow Connector -->
        <svg
          v-if="index < processes.length - 1"
          class="w-4 h-4 mx-0.5 flex-shrink-0 text-gray-300"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </div>
    </div>

    <!-- Divider -->
    <div class="border-t border-gray-100 mt-4 mb-3"></div>

    <!-- Status Badges -->
    <div class="flex flex-wrap gap-2">
      <button
        @click="select({ id: jobAts[0]?.rejected_process_id })"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-semibold bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 transition-colors cursor-pointer"
      >
        <span class="w-2 h-2 rounded-full bg-red-500"></span>
        {{t('shared.labels.rejected')}}
        <span
          class="bg-red-500 text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1"
        >{{ jobAts[0]?.rejected_count ?? 0 }}</span>
      </button>
      <button
        @click="select({ id: jobAts[0]?.declined_process_id })"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 transition-colors cursor-pointer"
      >
        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
        {{t('shared.labels.declined')}}
        <span
          class="bg-amber-500 text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1"
        >{{ jobAts[0]?.declined_count ?? 0 }}</span>
      </button>
      <button
        @click="select({ id: jobAts[0]?.deployed_process_id })"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-colors cursor-pointer"
      >
        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
        {{t('shared.labels.deployed')}}
        <span
          class="bg-emerald-500 text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1"
        >{{ jobAts[0]?.deployed_count ?? 0 }}</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { useTranslate } from '@/shared/composables/useTranslate.js'

const { t } = useTranslate()
defineProps({
  processes: Array,
  selectedProcessId: Number,
  selectedApplicant: Object,
  jobAts: Array,
})

const emits = defineEmits(['handleSelectProcess'])

const select = (process) => {
  emits('handleSelectProcess', process.id)
}
</script>
