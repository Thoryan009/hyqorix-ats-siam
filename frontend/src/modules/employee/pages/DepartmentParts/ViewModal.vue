
<template>
  <BaseModal
    :isVisible="store.isViewModal"
    :title="`View ${store.moduleName} Details`"
    @close="store.handleToggleModal"
    :className="'max-w-[95vw] xl:max-w-[55vw]'"
  >
    <ScrollableLayout>
      <div class="space-y-4 p-2">

        <!-- ══════════════ HEADER CARD ══════════════ -->
        <div class="relative rounded-2xl overflow-hidden shadow-xl"
          style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%)">
          <!-- top accent bar -->
          <div class="h-1 bg-linear-to-r from-violet-500 via-purple-400 to-indigo-500"></div>

          <!-- subtle dot pattern -->
          <div class="absolute inset-0 opacity-[0.04]"
            style="background-image: radial-gradient(circle at 25% 75%, #fff 1px, transparent 1px), radial-gradient(circle at 75% 25%, #fff 1px, transparent 1px); background-size: 28px 28px;">
          </div>

          <div class="relative p-6 flex items-center justify-between gap-4 flex-wrap">
            <!-- left: icon + name + id -->
            <div class="flex items-center gap-4">
              <div class="w-16 h-16 rounded-2xl flex items-center justify-center shadow-lg shrink-0"
                style="background: linear-gradient(135deg, #7c3aed, #4f46e5)">
                <i class="fa fa-building text-white text-2xl"></i>
              </div>
              <div>
                <h2 class="text-xl font-bold text-white leading-tight">{{ store.item?.name }}</h2>
                <p class="text-slate-400 text-xs mt-1 flex items-center gap-1">
                  <i class="fa fa-hashtag text-slate-500"></i>
                  Department ID: <span class="text-slate-200 font-semibold ml-1">{{ store.item?.id }}</span>
                </p>
                <p class="text-violet-300 text-xs mt-0.5 flex items-center gap-1">
                  <i class="fa fa-users"></i>
                  {{ employeeList.length }} {{ employeeList.length === 1 ? 'employee' : 'employees' }} assigned
                </p>
              </div>
            </div>

            <!-- right: employee count badge -->
            <div class="flex flex-col items-center justify-center w-16 h-16 rounded-2xl shadow-lg shrink-0"
              style="background: linear-gradient(135deg, #7c3aed, #4f46e5)">
              <span class="text-2xl font-bold text-white leading-none">{{ employeeList.length }}</span>
              <span class="text-[8px] text-violet-200 uppercase tracking-wide mt-0.5">Employees</span>
            </div>
          </div>
        </div>

        <!-- ══════════════ TABS ══════════════ -->
        <div class="rounded-2xl overflow-hidden shadow-sm border border-gray-100 bg-white">

          <!-- Tab Headers -->
          <div class="flex border-b border-gray-100">
            <button
              v-for="tab in tabs"
              :key="tab.key"
              @click="activeTab = tab.key"
              :class="[
                'flex items-center gap-2 px-6 py-3 text-sm font-semibold transition-all duration-200 focus:outline-none',
                activeTab === tab.key
                  ? 'text-white border-b-2 border-transparent'
                  : 'text-gray-500 hover:text-gray-700 bg-white border-b-2 border-transparent hover:bg-gray-50',
              ]"
              :style="activeTab === tab.key ? `background: linear-gradient(90deg, ${tab.color[0]}, ${tab.color[1]})` : ''"
            >
              <i :class="['fa', tab.icon, activeTab === tab.key ? 'text-white' : tab.iconColor]"></i>
              <span>{{ tab.label }}</span>
              <span
                v-if="tab.key === 'employees'"
                :class="['ml-1 px-2 py-0.5 rounded-full text-xs font-bold',
                  activeTab === tab.key ? 'bg-white/25 text-white' : 'bg-violet-100 text-violet-700']"
              >{{ employeeList.length }}</span>
            </button>
          </div>

          <!-- Tab Content -->
          <div class="p-5">

            <!-- ═══════ SUMMARY TAB ═══════ -->
            <div v-if="activeTab === 'summary'" class="space-y-4">

              <!-- Department Name highlight row -->
              <div class="rounded-xl border border-violet-100 overflow-hidden">
                <div class="px-4 py-2.5 flex items-center gap-2"
                  style="background: linear-gradient(90deg, #7c3aed, #4f46e5)">
                  <i class="fa fa-building text-white text-sm"></i>
                  <span class="text-white text-sm font-semibold tracking-wide uppercase">Department</span>
                </div>
                <div class="p-4 flex items-center gap-3 bg-white">
                  <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 shadow-sm"
                    style="background: linear-gradient(135deg, #7c3aed, #4f46e5)">
                    <i class="fa fa-tag text-white text-sm"></i>
                  </div>
                  <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Name</p>
                    <p class="text-base font-bold text-gray-800">{{ store.item?.name || '—' }}</p>
                  </div>
                  <div class="ml-6">
                    <p class="text-xs text-gray-400 uppercase tracking-wide">ID</p>
                    <p class="text-base font-bold text-gray-800">#{{ store.item?.id }}</p>
                  </div>
                </div>
              </div>

              <!-- Audit Info -->
              <div class="rounded-xl border border-gray-100 overflow-hidden bg-white">
                <div class="px-4 py-2.5 flex items-center gap-2"
                  style="background: linear-gradient(90deg, #374151, #4b5563)">
                  <i class="fa fa-info-circle text-white text-sm"></i>
                  <span class="text-white text-sm font-semibold tracking-wide uppercase">Audit Info</span>
                </div>
                <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 shadow-sm"
                      style="background: linear-gradient(135deg, #10b981, #059669)">
                      <i class="fa fa-clock-o text-white"></i>
                    </div>
                    <div>
                      <p class="text-xs text-gray-400 uppercase tracking-wide">Created At</p>
                      <p class="text-sm font-semibold text-gray-700">{{ store.item?.created_at || '—' }}</p>
                    </div>
                  </div>
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 shadow-sm"
                      style="background: linear-gradient(135deg, #f59e0b, #ef4444)">
                      <i class="fa fa-refresh text-white"></i>
                    </div>
                    <div>
                      <p class="text-xs text-gray-400 uppercase tracking-wide">Updated At</p>
                      <p class="text-sm font-semibold text-gray-700">{{ store.item?.updated_at || '—' }}</p>
                    </div>
                  </div>
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 shadow-sm"
                      style="background: linear-gradient(135deg, #3b82f6, #6366f1)">
                      <i class="fa fa-user text-white"></i>
                    </div>
                    <div>
                      <p class="text-xs text-gray-400 uppercase tracking-wide">Created By</p>
                      <p class="text-sm font-semibold text-gray-700">{{ store.item?.created_by || '—' }}</p>
                    </div>
                  </div>
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 shadow-sm"
                      style="background: linear-gradient(135deg, #8b5cf6, #ec4899)">
                      <i class="fa fa-pencil text-white"></i>
                    </div>
                    <div>
                      <p class="text-xs text-gray-400 uppercase tracking-wide">Updated By</p>
                      <p class="text-sm font-semibold text-gray-700">{{ store.item?.updated_by || '—' }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- ═══════ EMPLOYEES TAB ═══════ -->
            <div v-else-if="activeTab === 'employees'">
              <!-- Empty state -->
              <div
                v-if="!employeeList.length"
                class="flex flex-col items-center justify-center py-14 gap-3"
              >
                <div class="w-20 h-20 rounded-full flex items-center justify-center shadow-md"
                  style="background: linear-gradient(135deg, #ede9fe, #ddd6fe)">
                  <i class="fa fa-users text-violet-400 text-3xl"></i>
                </div>
                <p class="text-gray-500 font-medium text-sm">No employees assigned to this department.</p>
              </div>

              <!-- Employee chips grid -->
              <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <div
                  v-for="(name, index) in employeeList"
                  :key="index"
                  class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 bg-white shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 group"
                >
                  <!-- avatar with initials -->
                  <div
                    class="w-11 h-11 rounded-full flex items-center justify-center text-white font-bold text-sm shrink-0 shadow"
                    :style="`background: linear-gradient(135deg, ${cardColors[index % cardColors.length][0]}, ${cardColors[index % cardColors.length][1]})`"
                  >
                    {{ getInitials(name) }}
                  </div>
                  <!-- name -->
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate">{{ name.trim() }}</p>
                    <p class="text-xs text-gray-400 mt-0.5 flex items-center gap-1">
                      <i class="fa fa-building text-violet-300" style="font-size:10px"></i>
                      {{ store.item?.name }}
                    </p>
                  </div>
                  <!-- index badge -->
                  <span
                    class="text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0"
                    :style="`background: ${cardColors[index % cardColors.length][0]}22; color: ${cardColors[index % cardColors.length][0]}`"
                  >#{{ index + 1 }}</span>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </ScrollableLayout>
  </BaseModal>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  store: {
    type: Object,
    required: true,
  },
})

const activeTab = ref('summary')

const tabs = [
  {
    key: 'summary',
    label: 'Summary',
    icon: 'fa-file-text-o',
    iconColor: 'text-violet-500',
    color: ['#7c3aed', '#4f46e5'],
  },
  {
    key: 'employees',
    label: 'Employees',
    icon: 'fa-users',
    iconColor: 'text-violet-500',
    color: ['#7c3aed', '#4f46e5'],
  },
]

const employeeList = computed(() => {
  const raw = props.store?.item?.employees
  if (!raw || typeof raw !== 'string') return []
  return raw
    .split(',')
    .map((e) => e.trim())
    .filter(Boolean)
})

const cardColors = [
  ['#7c3aed', '#4f46e5'],
  ['#10b981', '#059669'],
  ['#f59e0b', '#ef4444'],
  ['#3b82f6', '#06b6d4'],
  ['#ec4899', '#f43f5e'],
  ['#8b5cf6', '#a855f7'],
  ['#14b8a6', '#0ea5e9'],
  ['#f97316', '#eab308'],
]

function getInitials(name) {
  if (!name) return '?'
  return name
    .trim()
    .split(' ')
    .slice(0, 2)
    .map((w) => w[0]?.toUpperCase() ?? '')
    .join('')
}
</script>
