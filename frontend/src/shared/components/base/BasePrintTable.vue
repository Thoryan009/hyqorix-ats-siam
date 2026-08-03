<template>
  <div class="w-full overflow-hidden">
    <table class="w-full border border-gray-400 border-collapse text-xs">
      <!-- Header -->
      <thead>
        <tr :class="theadBgColor">
          <!-- Select All -->
          <th v-if="selectable" class="border border-gray-400 px-2 py-1 text-center">
            <input
              type="checkbox"
              :checked="rows.length && selectedIds.length === rows.length"
              @change="emit('toggleAll', $event.target.checked)"
            />
          </th>

          <th
            v-for="col in columns"
            :key="col.key"
            class="border border-gray-400 px-2 py-1 text-left font-semibold uppercase"
          >
            {{ col.label }}
          </th>

          <th
            v-if="showActions"
            class="border border-gray-400 px-2 py-1 text-center font-semibold uppercase"
          >
            Actions
          </th>
        </tr>
      </thead>

      <!-- Body -->
      <tbody>
        <tr v-for="(row, index) in rows" :key="index" class="odd:bg-white even:bg-gray-50">
          <!-- Row Checkbox -->
          <td v-if="selectable" class="border border-gray-300 px-2 py-1 text-center">
            <input
              type="checkbox"
              :checked="selectedIds.includes(row.id)"
              @change="emit('toggleRow', row.id)"
            />
          </td>

          <td v-for="col in columns" :key="col.key" class="border border-gray-300 px-2 py-1">
            <!-- Serial -->
            <span v-if="col.key === 'sl'">
              {{ (currentPage - 1) * perPage + index + 1 }}
            </span>

            <span v-else>
              {{ row[col.key] ?? '-' }}
            </span>
          </td>

          <!-- Actions -->
          <td v-if="showActions" class="border border-gray-300 px-2 py-1 text-center">
            <slot name="actions" :row="row" :index="index" />
          </td>
        </tr>

        <!-- Empty -->
        <tr v-if="!rows || rows.length === 0">
          <td
            :colspan="columns.length + (selectable ? 1 : 0) + (showActions ? 1 : 0)"
            class="text-center py-6 text-gray-500"
          >
            No data available
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
defineProps({
  columns: Array,
  rows: Array,

  showActions: {
    type: Boolean,
    default: false,
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

  theadBgColor: {
    type: String,
    default: 'bg-gray-200',
  },
})

const emit = defineEmits(['toggleAll', 'toggleRow'])
</script>
