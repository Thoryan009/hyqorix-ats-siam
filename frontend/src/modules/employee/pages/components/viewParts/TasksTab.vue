<template>
  <div class="space-y-4">
    <!-- ── Empty State ── -->
    <div
      v-if="!tasks || tasks.length === 0"
      class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-blue-200 bg-blue-50 py-14"
    >
      <i class="fa fa-tasks text-4xl text-blue-200"></i>
      <p class="mt-3 text-sm font-semibold text-blue-400">No tasks assigned yet</p>
    </div>

    <template v-else>
      <!-- ── Stats Summary ── -->
      <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-4">
        <!-- Total -->
        <div
          class="flex items-center gap-3 rounded-xl border border-neutral-200 bg-white px-3.5 py-3"
        >
          <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-neutral-100">
            <i class="fa fa-list-ul text-neutral-600 text-sm"></i>
          </span>
          <div>
            <p class="text-xl font-bold text-neutral-800">{{ stats.total }}</p>
            <p class="text-xs text-neutral-900">Total</p>
          </div>
        </div>
        <!-- Completed -->
        <div
          class="flex items-center gap-3 rounded-xl border border-green-100 bg-green-50 px-3.5 py-3"
        >
          <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-green-100">
            <i class="fa fa-check-circle text-green-600 text-sm"></i>
          </span>
          <div>
            <p class="text-xl font-bold text-green-700">{{ stats.completed }}</p>
            <p class="text-xs text-green-600">Completed</p>
          </div>
        </div>
        <!-- In Progress -->
        <div
          class="flex items-center gap-3 rounded-xl border border-blue-100 bg-blue-50 px-3.5 py-3"
        >
          <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-100">
            <i class="fa fa-spinner text-blue-600 text-sm"></i>
          </span>
          <div>
            <p class="text-xl font-bold text-blue-700">{{ stats.in_progress }}</p>
            <p class="text-xs text-blue-600">In Progress</p>
          </div>
        </div>
        <!-- Pending -->
        <div
          class="flex items-center gap-3 rounded-xl border border-amber-100 bg-amber-50 px-3.5 py-3"
        >
          <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-100">
            <i class="fa fa-clock-o text-amber-600 text-sm"></i>
          </span>
          <div>
            <p class="text-xl font-bold text-amber-600">{{ stats.pending }}</p>
            <p class="text-xs text-amber-600">Pending</p>
          </div>
        </div>
      </div>

      <!-- ── Priority Breakdown ── -->
      <div class="flex flex-wrap items-center gap-2">
        <span class="text-xs font-semibold uppercase tracking-wide text-neutral-400">Priority:</span>
        <span
          class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-3 py-0.5 text-xs font-semibold text-red-700"
        >
          <i class="fa fa-arrow-up text-xs"></i>
          High &nbsp;{{ stats.high }}
        </span>
        <span
          class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-0.5 text-xs font-semibold text-amber-700"
        >
          <i class="fa fa-minus text-xs"></i>
          Medium &nbsp;{{ stats.medium }}
        </span>
        <span
          class="inline-flex items-center gap-1.5 rounded-full bg-blue-100 px-3 py-0.5 text-xs font-semibold text-blue-700"
        >
          <i class="fa fa-arrow-down text-xs"></i>
          Low &nbsp;{{ stats.low }}
        </span>
      </div>

      <!-- ── Task Cards ── -->
      <div class="max-h-115 space-y-2.5 overflow-y-auto pr-0.5">
        <div
          v-for="task in tasks"
          :key="task.id"
          class="overflow-hidden rounded-xl border border-neutral-200 bg-white transition-shadow hover:shadow-md"
        >
          <div class="flex">
            <!-- Priority accent bar -->
            <div class="w-1 shrink-0 rounded-l-xl" :class="priorityAccent(task.priority)"></div>

            <div class="flex-1 p-3.5">
              <!-- Top row: name + status + priority -->
              <div class="flex items-start justify-between gap-2">
                <div class="flex-1 min-w-0">
                  <div class="flex flex-wrap items-center gap-2">
                    <p class="text-sm font-semibold text-neutral-900">{{ task.name }}</p>
                    <!-- Priority badge -->
                    <span
                      :class="priorityBadge(task.priority)"
                      class="inline-flex shrink-0 items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold capitalize"
                    >
                      <i :class="priorityIcon(task.priority)" class="text-xs"></i>
                      {{ task.priority }}
                    </span>
                  </div>
                  <p
                    v-if="task.description"
                    class="mt-0.5 line-clamp-1 text-xs text-neutral-900"
                  >{{ task.description }}</p>
                </div>

                <!-- Status badge -->
                <span
                  :class="statusBadge(task.status)"
                  class="inline-flex shrink-0 items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                >
                  <span class="h-1.5 w-1.5 rounded-full" :class="statusDot(task.status)"></span>
                  {{ formatStatus(task.status) }}
                </span>
              </div>

              <!-- Bottom row: dates + case info -->
              <div
                class="mt-2.5 flex flex-wrap items-center gap-x-4 gap-y-1.5 border-t border-neutral-100 pt-2.5"
              >
                <!-- Due date -->
                <div class="flex items-center gap-1.5 text-xs text-neutral-900">
                  <i class="fa fa-calendar text-neutral-400"></i>
                  <span>Due: {{ task.due_date || '—' }}</span>
                </div>

                <!-- Completed at -->
                <div
                  v-if="task.completed_at"
                  class="flex items-center gap-1.5 text-xs text-green-600"
                >
                  <i class="fa fa-check-circle text-green-500"></i>
                  <span>{{ task.completed_at }}</span>
                </div>

                <!-- Case info -->
                <div v-if="task.funeral_case" class="ml-auto flex items-center gap-1.5">
                  <span
                    class="inline-flex items-center gap-1 rounded-md bg-indigo-50 border border-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700"
                  >
                    <i class="fa fa-briefcase text-xs"></i>
                    {{ task.funeral_case.case_number }}
                  </span>
                  <span
                    class="inline-flex items-center gap-1 rounded-md bg-neutral-50 border border-neutral-200 px-2 py-0.5 text-xs text-neutral-600"
                  >
                    <i class="fa fa-map-marker text-xs text-neutral-400"></i>
                    {{ task.funeral_case.branch_name }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  tasks: { type: Array, default: () => [] },
})

/* ── Stats ── */
const stats = computed(() => ({
  total: props.tasks.length,
  completed: props.tasks.filter((t) => t.status === 'completed').length,
  in_progress: props.tasks.filter((t) => t.status === 'in_progress').length,
  pending: props.tasks.filter((t) => t.status === 'pending').length,
  high: props.tasks.filter((t) => t.priority === 'high').length,
  medium: props.tasks.filter((t) => t.priority === 'medium').length,
  low: props.tasks.filter((t) => t.priority === 'low').length,
}))

/* ── Priority helpers ── */
const priorityAccent = (p) =>
  ({
    high: 'bg-red-500',
    medium: 'bg-amber-400',
    low: 'bg-blue-400',
  }[p] ?? 'bg-neutral-300')

const priorityBadge = (p) =>
  ({
    high: 'bg-red-100 text-red-700',
    medium: 'bg-amber-100 text-amber-700',
    low: 'bg-blue-100 text-blue-700',
  }[p] ?? 'bg-neutral-100 text-neutral-600')

const priorityIcon = (p) =>
  ({
    high: 'fa fa-arrow-up',
    medium: 'fa fa-minus',
    low: 'fa fa-arrow-down',
  }[p] ?? 'fa fa-circle')

/* ── Status helpers ── */
const statusBadge = (s) =>
  ({
    completed: 'bg-green-100 text-green-700',
    in_progress: 'bg-blue-100 text-blue-700',
    pending: 'bg-amber-100 text-amber-700',
  }[s] ?? 'bg-neutral-100 text-neutral-600')

const statusDot = (s) =>
  ({
    completed: 'bg-green-500',
    in_progress: 'bg-blue-500',
    pending: 'bg-amber-500',
  }[s] ?? 'bg-neutral-400')

const formatStatus = (s) => (s || '').replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase())
</script>
