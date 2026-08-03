<template>
  <div :class="scrollable ? 'overflow-x-auto rounded shadow' : 'rounded shadow'">
    <table class="w-full border-collapse bg-white rounded overflow-hidden">
      <thead>
        <tr :class="theadBgColor">
          <!-- Select All -->
          <th
            v-if="selectable"
            class="px-2 py-2 text-center border-r border-white/30 whitespace-nowrap"
          >
            <div class="flex items-center justify-center">
              <input
                type="checkbox"
                class="w-4 h-4 cursor-pointer accent-primary rounded transition-transform hover:scale-110"
                :checked="rows.length && selectedIds.length === rows.length"
                @change="emit('toggleAll', $event.target.checked)"
              />
            </div>
          </th>

          <th
            v-for="col in displayColumns"
            :key="col.key"
            class="px-3 py-2 text-left text-xs font-bold text-black uppercase tracking-wider border-r border-white/30 last:border-r-0 whitespace-nowrap"
          >{{ col.label }}</th>

          <th
            v-if="showActions"
            class="px-3 py-2 text-center text-xs font-bold text-black uppercase tracking-wider whitespace-nowrap"
          >{{ t('shared.actions.actions') }}</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-gray-100">
        <tr
          v-for="(row, index) in rows"
          :key="index"
          :class="[
            'transition-all duration-200',
            index % 2 === 0 ? 'bg-white' : 'bg-gray-50/70',
            cursorPointer ? 'hover:bg-primary/8 hover:shadow-md hover:scale-[1.001] cursor-pointer' : '',
          ]"
          @click="emit('rowClick', row, index)"
        >
          <!-- Row Checkbox -->
          <td
            v-if="selectable"
            class="px-2 py-1 text-center border-r border-gray-100 whitespace-nowrap"
          >
            <div class="flex items-center justify-center">
              <input
                type="checkbox"
                :checked="selectedIds.includes(row.id)"
                @change="emit('toggleRow', row.id)"
                class="w-4 h-4 cursor-pointer accent-primary rounded transition-transform hover:scale-110"
              />
            </div>
          </td>

          <td
            v-for="col in displayColumns"
            :key="col.key"
            class="px-2 py-2 text-xs text-gray-800 border-r border-gray-100 last:border-r-0 whitespace-nowrap"
          >
            <!-- Serial -->
            <span
              v-if="showSerial && col.key === 'sl'"
              class="font-bold text-primary bg-primary/10 px-3 py-1 rounded-full text-xs"
            >{{ (currentPage - 1) * perPage + index + 1 }}</span>

            <slot v-else-if="$slots[`cell-${col.key}`]" :name="`cell-${col.key}`" :row="row" />
            <span v-else class="font-medium truncate">{{ row[col.key] ?? 'N/A' }}</span>
          </td>

          <!-- Dynamic Actions -->
          <td v-if="showActions" class="px-2 py-2 whitespace-nowrap">
            <div class="flex justify-center gap-2">
              <slot name="actions" :row="row" :index="index" />
            </div>
          </td>
        </tr>

        <!-- Empty State -->
        <tr v-if="!rows || rows.length === 0">
          <td
            :colspan="displayColumns.length + (selectable ? 1 : 0) + (showActions ? 1 : 0)"
            class="px-4 py-12 text-center bg-gradient-to-b from-gray-50 to-white whitespace-normal"
          >
            <div class="flex flex-col items-center gap-4">
              <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center">
                <svg
                  class="w-8 h-8 text-primary"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                  />
                </svg>
              </div>
              <div>
                <p class="text-gray-700 font-semibold text-base mb-1">{{ t('shared.messages.no_data') }}</p>
                <p class="text-gray-500 text-sm">{{ t('shared.messages.no_records') }}</p>
              </div>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  columns: Array,
  rows: Array,

  showActions: {
    type: Boolean,
    default: false,
  },

  showSerial: {
    type: Boolean,
    default: true,
  },

  selectable: {
    type: Boolean,
    default: false,
  },

  selectedIds: {
    type: Array,
    default: () => [],
  },

  currentPage: {
    type: Number,
    default: 1,
  },

  perPage: {
    type: Number,
    default: 10,
  },

  scrollable: {
    type: Boolean,
    default: true,
  },

  theadBgColor: {
    type: String,
    // default: 'bg-[#588F36]',
    default: 'bg-primary/20',
  },
  cursorPointer: {
    type: Boolean,
    default: false,
  },
})
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const displayColumns = computed(() =>
  props.showSerial
    ? props.columns ?? []
    : (props.columns ?? []).filter((col) => col.key !== 'sl')
)

const emit = defineEmits(['toggleAll', 'toggleRow', 'rowClick'])
</script>

<style scoped>
/* Smooth transitions for all interactive elements */
td,
th {
  transition: all 0.2s ease-in-out;
}

tr {
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Custom scrollbar for overflow */
.overflow-x-auto::-webkit-scrollbar {
  height: 10px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: #f3f4f6;
  border-radius: 10px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background: linear-gradient(90deg, var(--primary), var(--primary));
  border-radius: 10px;
  transition: background 0.3s ease;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(90deg, var(--primary), var(--primary));
}

/* Firefox */
.overflow-x-auto {
  scrollbar-width: thin;
  scrollbar-color: var(--primary) #f3f4f6;
}
</style>
