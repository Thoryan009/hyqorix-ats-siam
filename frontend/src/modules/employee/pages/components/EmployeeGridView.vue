<template>
  <div class="p-4 sm:p-6">

    <!-- Loading skeleton -->
    <div v-if="isLoading" class="space-y-4">
      <div v-for="n in perPage" :key="n" class="animate-pulse flex gap-4 p-5 rounded-2xl border bg-white">
        <div class="w-5 h-5 rounded bg-gray-200 shrink-0 mt-7"></div>
        <div class="w-20 h-20 rounded-2xl bg-gray-200 shrink-0"></div>
        <div class="flex-1 space-y-3 py-2">
          <div class="h-4 bg-gray-200 rounded w-2/5"></div>
          <div class="h-3 bg-gray-200 rounded w-1/4"></div>
          <div class="flex gap-3 mt-3">
            <div class="h-3 bg-gray-200 rounded w-32"></div>
            <div class="h-3 bg-gray-200 rounded w-24"></div>
          </div>
        </div>
        <div class="w-10 h-6 rounded bg-gray-200 self-start mt-4 shrink-0"></div>
      </div>
    </div>

    <div v-else>
      <!-- Empty state -->
      <div v-if="!rows.length" class="flex flex-col items-center justify-center py-20 text-gray-400">
        <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center mb-4">
          <i class="fa fa-users text-4xl text-gray-300"></i>
        </div>
        <p class="text-base font-semibold text-gray-500">No employees found</p>
        <p class="text-sm mt-1 text-gray-400">Try adjusting your filters or adding a new employee.</p>
      </div>

      <!-- Employee rows -->
      <div v-else class="space-y-3">
        <div
          v-for="row in rows"
          :key="row.id"
          class="group relative flex items-center gap-4 px-5 py-4 rounded-2xl border border-gray-100 bg-white hover:border-primary/40 hover:shadow-md transition-all duration-200"
        >
          <!-- Checkbox -->
          <div class="shrink-0" @click.stop>
            <input
              type="checkbox"
              class="w-4 h-4 cursor-pointer accent-primary"
              :checked="selectedIds.includes(row.id)"
              @change="$emit('toggleRow', row)"
            />
          </div>

          <!-- Avatar -->
          <div class="shrink-0">
            <div
              class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl overflow-hidden border-2 border-gray-100 bg-linear-to-br from-gray-100 to-gray-200 flex items-center justify-center shadow-sm group-hover:border-primary/25 transition-colors"
            >
              <img
                v-if="row.image_url || row.image || row.avatar"
                :src="row.image_url || row.image || row.avatar"
                :alt="row.name || 'Employee'"
                class="w-full h-full object-cover"
                @error="(e) => (e.target.style.display = 'none')"
              />
              <i v-else class="fa fa-user-circle-o text-gray-300 text-4xl"></i>
            </div>
          </div>

          <!-- Info -->
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-3">
              <!-- Name + designation -->
              <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <h3
                    class="text-sm font-bold text-gray-900 truncate cursor-pointer hover:text-primary transition-colors"
                    @click="$emit('onView', row)"
                  >
                    {{ row.name || row.full_name || '—' }}
                  </h3>
                  <span
                    :class="
                      row.status === 'active'
                        ? 'bg-emerald-100 text-emerald-700 border-emerald-200'
                        : 'bg-red-100 text-red-600 border-red-200'
                    "
                    class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full capitalize border shrink-0"
                  >
                    <i class="fa fa-circle" style="font-size: 6px"></i>
                    {{ row.status || 'N/A' }}
                  </span>
                </div>

                <div class="text-xs font-medium text-indigo-500 mt-0.5 flex items-center gap-1 truncate">
                  <i class="fa fa-briefcase text-gray-400"></i>
                  {{ row.designation || '—' }}
                </div>
              </div>

              <!-- Action buttons — visible on hover -->
              <div
                class="flex items-center gap-1.5 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity duration-150"
                @click.stop
              >
                <button
                  v-can="'employee.view'"
                  @click="$emit('onView', row)"
                  class="w-8 h-8 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 flex items-center justify-center transition-colors"
                  title="View"
                >
                  <i class="fa fa-eye text-xs"></i>
                </button>
                <button
                  v-can="'employee.edit'"
                  @click="$emit('onEdit', row)"
                  class="w-8 h-8 rounded-lg bg-green-50 hover:bg-green-100 text-green-600 flex items-center justify-center transition-colors"
                  title="Edit"
                >
                  <i class="fa fa-pencil text-xs"></i>
                </button>
                <button
                  v-can="'employee.delete'"
                  @click="$emit('onDelete', row.id)"
                  class="w-8 h-8 rounded-lg bg-red-50 hover:bg-red-100 text-red-500 flex items-center justify-center transition-colors"
                  title="Delete"
                >
                  <i class="fa fa-trash text-xs"></i>
                </button>
              </div>
            </div>

            <!-- Contact & meta chips -->
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 mt-2.5">
              <span v-if="row.email" class="flex items-center gap-1.5 text-[11px] text-gray-500">
                <i class="fa fa-envelope-o text-gray-400"></i>
                <span class="truncate max-w-50">{{ row.email }}</span>
              </span>

              <span v-if="row.phone" class="flex items-center gap-1.5 text-[11px] text-gray-500">
                <i class="fa fa-phone text-gray-400"></i>
                {{ row.phone }}
              </span>

              <span v-if="row.roles" class="flex items-center gap-1.5 text-[11px] text-gray-500">
                <i class="fa fa-shield text-gray-400"></i>
                {{ row.roles }}
              </span>

              <span
                v-if="row.tasks_count != null"
                class="inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-600 bg-indigo-50 border border-indigo-100 px-2.5 py-0.5 rounded-full"
              >
                <i class="fa fa-tasks"></i> {{ row.tasks_count }} Tasks
              </span>

              <span
                v-if="row.pending_tasks_count"
                class="inline-flex items-center gap-1 text-[11px] font-semibold text-amber-600 bg-amber-50 border border-amber-100 px-2.5 py-0.5 rounded-full"
              >
                <i class="fa fa-hourglass-half"></i> {{ row.pending_tasks_count }} Pending
              </span>
            </div>
          </div>

          <!-- ID pill (right edge, desktop only) -->
          <div class="hidden sm:flex shrink-0 items-end self-stretch pb-0.5">
            <span
              class="text-[10px] text-gray-400 bg-gray-50 border border-gray-100 px-2.5 py-1 rounded-full font-mono"
            >
              #{{ row.id }}
            </span>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="mt-4">
        <BasePagination
          :total="total"
          :showing="showing"
          :links="links"
          :per-page="perPage"
          @update:page="(v) => $emit('update:page', v)"
          @update:perPage="(v) => $emit('update:perPage', v)"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  rows: {
    type: Array,
    default: () => [],
  },
  isLoading: {
    type: Boolean,
    default: false,
  },
  selectedIds: {
    type: Array,
    default: () => [],
  },
  total: {
    type: Number,
    default: 0,
  },
  showing: {
    type: [Number, String],
    default: 0,
  },
  links: {
    type: Array,
    default: () => [],
  },
  perPage: {
    type: Number,
    default: 10,
  },
})

defineEmits(['onView', 'onEdit', 'onDelete', 'toggleRow', 'update:page', 'update:perPage'])
</script>
