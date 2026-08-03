<template>
  <div class="space-y-4">
    <!-- ── Hero Card ── -->
    <div class="overflow-hidden rounded-2xl border border-teal-100">
      <!-- Gradient Header -->
      <div class="relative bg-linear-to-r from-teal-600 via-cyan-600 to-emerald-600 px-5 py-5">
        <!-- Decorative circles -->
        <div
          class="pointer-events-none absolute -top-6 -right-6 h-24 w-24 rounded-full bg-white/10"
        ></div>
        <div class="pointer-events-none absolute top-4 right-16 h-10 w-10 rounded-full bg-white/10"></div>

        <div class="relative flex items-center justify-between gap-4">
          <div class="flex items-center gap-4">
            <!-- Avatar -->
            <div class="relative">
              <div
                class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-white/20 text-xl font-bold text-white ring-2 ring-white/30 backdrop-blur-sm"
              >{{ initials }}</div>
              <!-- Online dot -->
              <span
                class="absolute bottom-0.5 right-0.5 h-3 w-3 rounded-full border-2 border-white"
                :class="employee?.status !== 'inactive' ? 'bg-green-400' : 'bg-neutral-400'"
              ></span>
            </div>

            <div>
              <h3 class="text-xl font-bold text-white">{{ employee?.name || '—' }}</h3>
              <div class="mt-1 flex flex-wrap items-center gap-2">
                <span
                  class="inline-flex items-center gap-1 rounded-full bg-white/20 px-2.5 py-0.5 text-xs font-semibold text-white capitalize"
                >
                  <i :class="[typeIcon, 'text-xs']"></i>
                  {{ employee.roles }}
                </span>
                <span
                  v-if="employee?.designation"
                  class="inline-flex items-center gap-1 rounded-full bg-white/15 px-2.5 py-0.5 text-xs text-teal-100"
                >
                  <i class="fa fa-briefcase text-xs"></i>
                  {{ employee.designation }}
                </span>
              </div>
            </div>
          </div>

          <!-- Employee ID pill -->
          <div
            class="shrink-0 rounded-xl border border-white/20 bg-white/15 px-3 py-2 text-center backdrop-blur-sm"
          >
            <p class="text-xs text-teal-100">EMP ID</p>
            <p class="text-sm font-bold text-white">#{{ employee?.id }}</p>
          </div>
        </div>
      </div>

      <!-- Task Stats Bar -->
      <div
        class="grid grid-cols-4 divide-x divide-neutral-100 border-t border-neutral-100 bg-white"
      >
        <div class="flex flex-col items-center gap-0.5 py-3">
          <span class="text-xl font-bold text-neutral-800">{{ taskStats.total }}</span>
          <span class="text-xs text-neutral-500">Total</span>
        </div>
        <div class="flex flex-col items-center gap-0.5 py-3">
          <span class="text-xl font-bold text-green-600">{{ taskStats.completed }}</span>
          <span class="text-xs text-neutral-500">Done</span>
        </div>
        <div class="flex flex-col items-center gap-0.5 py-3">
          <span class="text-xl font-bold text-blue-600">{{ taskStats.in_progress }}</span>
          <span class="text-xs text-neutral-500">Active</span>
        </div>
        <div class="flex flex-col items-center gap-0.5 py-3">
          <span class="text-xl font-bold text-amber-500">{{ taskStats.pending }}</span>
          <span class="text-xs text-neutral-500">Pending</span>
        </div>
      </div>
    </div>

    <!-- ── Contact & Info Grid ── -->
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
      <!-- Email -->
      <div class="flex items-start gap-3 rounded-xl border border-teal-100 bg-teal-50/40 px-4 py-3">
        <span
          class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-teal-100"
        >
          <i class="fa fa-envelope text-teal-600"></i>
        </span>
        <div class="min-w-0">
          <p class="text-xs font-semibold uppercase tracking-wide text-neutral-400">Email</p>
          <p
            class="mt-0.5 truncate text-sm font-semibold text-neutral-800"
          >{{ employee?.email || '—' }}</p>
        </div>
      </div>

      <!-- Phone -->
      <div class="flex items-start gap-3 rounded-xl border border-cyan-100 bg-cyan-50/40 px-4 py-3">
        <span
          class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-cyan-100"
        >
          <i class="fa fa-phone text-cyan-600"></i>
        </span>
        <div class="min-w-0">
          <p class="text-xs font-semibold uppercase tracking-wide text-neutral-400">Phone</p>
          <p class="mt-0.5 text-sm font-semibold text-neutral-800">{{ employee?.phone || '—' }}</p>
        </div>
      </div>

      <!-- Designation -->
      <div
        class="flex items-start gap-3 rounded-xl border border-emerald-100 bg-emerald-50/40 px-4 py-3"
      >
        <span
          class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-100"
        >
          <i class="fa fa-id-card-o text-emerald-600"></i>
        </span>
        <div class="min-w-0">
          <p class="text-xs font-semibold uppercase tracking-wide text-neutral-400">Designation</p>
          <p
            class="mt-0.5 text-sm font-semibold text-neutral-800"
          >{{ employee?.designation || '—' }}</p>
        </div>
      </div>

      <!-- Role -->
      <div
        class="flex items-start gap-3 rounded-xl border border-violet-100 bg-violet-50/40 px-4 py-3"
      >
        <span
          class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-violet-100"
        >
          <i :class="[typeIcon, 'text-violet-600']"></i>
        </span>
        <div class="min-w-0">
          <p class="text-xs font-semibold uppercase tracking-wide text-neutral-400">Roles</p>
          <p class="mt-0.5 text-sm font-semibold capitalize text-neutral-800">{{ employee.roles }}</p>
        </div>
      </div>

      <!-- Designation Description (full width) -->
      <div
        v-if="employee?.designation_description"
        class="flex items-start gap-3 rounded-xl border border-amber-100 bg-amber-50/40 px-4 py-3 sm:col-span-2"
      >
        <span
          class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-100"
        >
          <i class="fa fa-quote-left text-amber-600"></i>
        </span>
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-neutral-400">Job Description</p>
          <p
            class="mt-0.5 text-sm text-neutral-700 leading-relaxed"
          >{{ employee.designation_description }}</p>
        </div>
      </div>
    </div>

    <!-- ── Audit / Meta ── -->
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
      <div class="flex items-center gap-3 rounded-xl border border-neutral-200 bg-white px-4 py-3">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-neutral-100">
          <i class="fa fa-user-plus text-neutral-500 text-sm"></i>
        </span>
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-neutral-400">Created By</p>
          <p class="mt-0.5 text-sm font-medium text-neutral-800">{{ employee?.created_by || '—' }}</p>
        </div>
      </div>
      <div class="flex items-center gap-3 rounded-xl border border-neutral-200 bg-white px-4 py-3">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-neutral-100">
          <i class="fa fa-pencil-square-o text-neutral-500 text-sm"></i>
        </span>
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-neutral-400">Updated By</p>
          <p class="mt-0.5 text-sm font-medium text-neutral-800">{{ employee?.updated_by || '—' }}</p>
        </div>
      </div>
      <div class="flex items-center gap-3 rounded-xl border border-neutral-200 bg-white px-4 py-3">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-neutral-100">
          <i class="fa fa-calendar-plus-o text-neutral-500 text-sm"></i>
        </span>
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-neutral-400">Created At</p>
          <p class="mt-0.5 text-sm font-medium text-neutral-800">{{ employee?.created_at || '—' }}</p>
        </div>
      </div>
      <div class="flex items-center gap-3 rounded-xl border border-neutral-200 bg-white px-4 py-3">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-neutral-100">
          <i class="fa fa-clock-o text-neutral-500 text-sm"></i>
        </span>
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-neutral-400">Updated At</p>
          <p class="mt-0.5 text-sm font-medium text-neutral-800">{{ employee?.updated_at || '—' }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  employee: { type: Object, default: () => ({}) },
})

const initials = computed(
  () =>
    (props.employee?.name || '')
      .split(' ')
      .map((w) => w[0])
      .slice(0, 2)
      .join('')
      .toUpperCase() || '?'
)


const typeIcon = computed(() => {
  const map = {
    branch_admin: 'fa fa-shield',
    admin: 'fa fa-lock',
    staff: 'fa fa-user',
    employee: 'fa fa-user',
    super_admin: 'fa fa-star',
  }
  return map[props.employee?.type] || 'fa fa-user'
})

const taskStats = computed(() => {
  const tasks = props.employee?.tasks || []
  return {
    total: tasks.length,
    completed: tasks.filter((t) => t.status === 'completed').length,
    in_progress: tasks.filter((t) => t.status === 'in_progress').length,
    pending: tasks.filter((t) => t.status === 'pending').length,
  }
})
</script>
