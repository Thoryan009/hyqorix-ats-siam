<template>
  <div v-if="data" class="mb-6">
    <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold text-gray-500 uppercase tracking-wide">
      <i class="fa fa-users text-indigo-500"></i>
      Overview
    </h3>

    <div class="flex gap-3 overflow-x-auto pb-2">
      <div class="group relative w-56 flex-shrink-0 overflow-hidden rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-700 p-4 shadow-lg">
        <div class="relative flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/20 text-white">
            <i class="fa fa-users text-lg"></i>
          </div>
          <div>
            <p class="text-2xl font-bold text-white">{{ data.total_employees ?? 0 }}</p>
            <p class="text-xs text-indigo-100">Total Employees</p>
          </div>
        </div>
      </div>

      <div class="group relative w-56 flex-shrink-0 overflow-hidden rounded-xl bg-gradient-to-br from-emerald-400 to-emerald-600 p-4 shadow-lg">
        <div class="relative flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/20 text-white">
            <i class="fa fa-check text-lg"></i>
          </div>
          <div>
            <p class="text-2xl font-bold text-white">{{ data.active_employees_count ?? 0 }}</p>
            <p class="text-xs text-emerald-100">Active</p>
          </div>
        </div>
      </div>

      <div class="group relative w-56 flex-shrink-0 overflow-hidden rounded-xl bg-gradient-to-br from-red-500 to-red-700 p-4 shadow-lg">
        <div class="relative flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/20 text-white">
            <i class="fa fa-times-circle text-lg"></i>
          </div>
          <div>
            <p class="text-2xl font-bold text-white">{{ data.inactive_employees_count ?? 0 }}</p>
            <p class="text-xs text-red-100">Inactive</p>
          </div>
        </div>
      </div>

      <div class="group relative w-56 flex-shrink-0 overflow-hidden rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-700 p-4 shadow-lg">
        <div class="relative flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/20 text-white">
            <i class="fa fa-user-plus text-lg"></i>
          </div>
          <div>
            <p class="text-2xl font-bold text-white">{{ data.today_joined_count ?? 0 }}</p>
            <p class="text-xs text-indigo-100">Joined Today</p>
          </div>
        </div>
      </div>

      <div class="group relative w-56 flex-shrink-0 overflow-hidden rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 p-4 shadow-lg">
        <div class="relative flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/20 text-white">
            <i class="fa fa-tasks text-lg"></i>
          </div>
          <div>
            <p class="text-2xl font-bold text-white">{{ data.employees_with_tasks_count ?? 0 }}</p>
            <p class="text-xs text-amber-100">With Tasks</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({ data: { type: Object, default: null } })
</script>
