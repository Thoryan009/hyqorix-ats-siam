<template>
  <div
    class="bg-white rounded-lg shadow-md p-4 max-h-[600px] overflow-y-auto pr-2 process-list-scroller applicant-scroller"
  >
    <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">{{t('ats.applicant_processes')}}</h3>
    <div v-if="processes && processes.length > 0" class="space-y-2">
      <div
        v-for="process in sortedProcesses"
        :key="process.id"
        @click="selectProcess(process)"
        :class="[
          'px-4 py-2.5 rounded-lg cursor-pointer transition-all duration-200 border-l-4',
          selectedProcessDetails?.id === process.id
            ? 'bg-primary text-white border-primary shadow-lg '
            : 'bg-gray-50 border-gray-300 hover:bg-gray-100 hover:border-primary/50',
        ]"
      >
        <div class="flex items-center justify-between">
          <div class="flex-1">
            <div class="flex items-center gap-2 mb-1">
              <span
                :class="[
                  'text-xs font-medium',
                  selectedProcessDetails?.id === process.id ? 'text-white/90' : 'text-gray-500',
                ]"
              >
                #{{ process.process_id }}
              </span>
              <span
                :class="[
                  'px-2 rounded-full text-xs font-semibold',
                  selectedProcessDetails?.id === process.id
                    ? 'bg-white/20 text-white'
                    : process.status === 'completed'
                      ? 'bg-green-100 text-green-700'
                      : process.status === 'pending'
                        ? 'bg-yellow-100 text-yellow-700'
                        : 'bg-red-100 text-red-700',
                ]"
              >
                {{ process.status }}
              </span>
            </div>

            <p
              :class="[
                'font-medium text-sm',
                selectedProcessDetails?.id === process.id ? 'text-white' : 'text-gray-800',
              ]"
            >
              {{ t(`dashboard.pipeline.${process.process}`) }}
            </p>

            <p
              v-if="process.remarks"
              :class="[
                'text-xs mt-0.5',
                selectedProcessDetails?.id === process.id ? 'text-white/80' : 'text-gray-600',
              ]"
            >
              {{ capitalize(process.remarks) }}
            </p>
          </div>

          <div class="ml-2">
            <svg
              :class="[
                'w-5 h-5',
                selectedProcessDetails?.id === process.id ? 'text-white' : 'text-gray-400',
              ]"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 5l7 7-7 7"
              />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-8 text-gray-500">
      <p class="text-sm">No processes available</p>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits, computed } from 'vue'
import { useTranslate } from '@/shared/composables/useTranslate.js'
const { t } = useTranslate()

const props = defineProps({
  processes: {
    type: Array,
    default: () => [],
  },
  selectedProcessDetails: Object,
})

const emit = defineEmits(['selectProcess'])
const capitalize = (text) => {
  if (!text) return ''
  return text
    .replace(/_/g, ' ')
    .toLowerCase()
    .replace(/\b\w/g, (char) => char.toUpperCase())
}

// Sort processes in descending order by process_id
const sortedProcesses = computed(() => {
  if (!props.processes) return []
  return [...props.processes].sort((a, b) => b.process_id - a.process_id)
})

const selectProcess = (process) => {
  emit('selectProcess', process)
}
</script>

<style scoped>
.applicant-scroller::-webkit-scrollbar {
  width: 5px;
}

.applicant-scroller::-webkit-scrollbar-track {
  background: transparent;
  border-radius: 10px;
}

.applicant-scroller::-webkit-scrollbar-thumb {
  background: linear-gradient(180deg, #e2e8f0, #cbd5e1);
  border-radius: 10px;
  transition: all 0.3s ease;
}

.applicant-scroller::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(180deg, #cbd5e1, #94a3b8);
  box-shadow: 0 0 6px rgba(148, 163, 184, 0.4);
}

.applicant-scroller {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e1 transparent;
}
</style>
