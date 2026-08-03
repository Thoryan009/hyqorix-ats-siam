<template>
  <BaseModal
    :isVisible="store.isViewModal"
    :title="`View ${store.moduleName} Details`"
    @close="store.handleToggleModal"
    :className="'xl:max-w-[55vw]'"
  >
    <ScrollableLayout>
      <div class="space-y-4 p-2">
        <!-- Header Card -->
        <div class="relative rounded-2xl overflow-hidden shadow border border-gray-100 bg-white">
          <div class="p-5 flex items-center gap-4">
            <div
              class="w-14 h-14 rounded-xl flex items-center justify-center shadow-sm flex-shrink-0"
              style="background: linear-gradient(135deg, #3b82f6, #6366f1)"
            >
              <i class="fa fa-id-badge text-white text-2xl"></i>
            </div>
            <div class="flex-1">
              <h2 class="text-lg font-bold text-gray-900 leading-tight">
                {{ store.item?.name }}
              </h2>
              <p class="text-sm text-gray-500 mt-1 flex items-center gap-1.5">
                <i class="fa fa-hashtag text-gray-400 text-xs"></i>
                <span
                  >ID: <strong class="text-gray-700">{{ store.item?.id }}</strong></span
                >
              </p>
            </div>
            <!-- Employee Count Badge -->
            <div
              class="flex flex-col items-center justify-center w-16 h-16 rounded-xl shadow-sm flex-shrink-0"
              style="background: linear-gradient(135deg, #10b981, #059669)"
            >
              <span class="text-2xl font-bold text-white leading-none">{{
                store.item?.employees_count ?? 0
              }}</span>
              <span class="text-[8px] text-green-100 uppercase tracking-wide mt-0.5"
                >Employees</span
              >
            </div>
          </div>
        </div>

        <!-- Tabs -->
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
              :style="
                activeTab === tab.key
                  ? `background: linear-gradient(90deg, ${tab.color[0]}, ${tab.color[1]})`
                  : ''
              "
            >
              <i
                :class="['fa', tab.icon, activeTab === tab.key ? 'text-white' : tab.iconColor]"
              ></i>
              <span>{{ tab.label }}</span>
              <span
                v-if="tab.key === 'employees'"
                :class="[
                  'ml-1 px-2 py-0.5 rounded-full text-xs font-bold',
                  activeTab === tab.key ? 'bg-white/25 text-white' : 'bg-green-100 text-green-700',
                ]"
                >{{ store.item?.employees_count ?? 0 }}</span
              >
            </button>
          </div>

          <!-- Tab Content -->
          <div class="p-5">
            <!-- ===================== SUMMARY TAB ===================== -->
            <div v-can="'designation.view_summary'" v-if="activeTab === 'summary'" class="space-y-4">
              <!-- Description -->
              <div class="rounded-xl border border-indigo-100 overflow-hidden">
                <div
                  class="px-4 py-2.5 flex items-center gap-2"
                  style="background: linear-gradient(90deg, #6366f1, #8b5cf6)"
                >
                  <i class="fa fa-align-left text-white text-sm"></i>
                  <span class="text-white text-sm font-semibold tracking-wide uppercase"
                    >Description</span
                  >
                </div>
                <div class="p-4 bg-white">
                  <div
                    v-if="store.item?.description"
                    class="text-sm text-gray-700 leading-relaxed prose prose-sm max-w-none text-slate-600 [&_strong]:font-bold [&_em]:italic [&_u]:underline [&_s]:line-through [&_ul]:list-disc [&_ul]:pl-4 [&_ol]:list-decimal [&_ol]:pl-4 [&_h1]:text-lg [&_h2]:text-base [&_h3]:text-sm"
                    v-html="store.item.description"
                  ></div>
                  <span v-else class="italic text-gray-400 text-sm">No description provided.</span>
                </div>
              </div>

              <!-- Audit Info -->
              <div class="rounded-xl border border-gray-100 overflow-hidden bg-white">
                <div
                  class="px-4 py-2.5 flex items-center gap-2"
                  style="background: linear-gradient(90deg, #374151, #4b5563)"
                >
                  <i class="fa fa-info-circle text-white text-sm"></i>
                  <span class="text-white text-sm font-semibold tracking-wide uppercase"
                    >Audit Info</span
                  >
                </div>
                <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div class="flex items-center gap-3">
                    <div
                      class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm"
                      style="background: linear-gradient(135deg, #10b981, #059669)"
                    >
                      <i class="fa fa-clock-o text-white"></i>
                    </div>
                    <div>
                      <p class="text-xs text-gray-400 uppercase tracking-wide">Created At</p>
                      <p class="text-sm font-semibold text-gray-700">
                        {{ store.item?.created_at || '—' }}
                      </p>
                    </div>
                  </div>
                  <div class="flex items-center gap-3">
                    <div
                      class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm"
                      style="background: linear-gradient(135deg, #f59e0b, #ef4444)"
                    >
                      <i class="fa fa-refresh text-white"></i>
                    </div>
                    <div>
                      <p class="text-xs text-gray-400 uppercase tracking-wide">Updated At</p>
                      <p class="text-sm font-semibold text-gray-700">
                        {{ store.item?.updated_at || '—' }}
                      </p>
                    </div>
                  </div>
                  <div class="flex items-center gap-3">
                    <div
                      class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm"
                      style="background: linear-gradient(135deg, #3b82f6, #6366f1)"
                    >
                      <i class="fa fa-user text-white"></i>
                    </div>
                    <div>
                      <p class="text-xs text-gray-400 uppercase tracking-wide">Created By</p>
                      <p class="text-sm font-semibold text-gray-700">
                        {{ store.item?.created_by || '—' }}
                      </p>
                    </div>
                  </div>
                  <div class="flex items-center gap-3">
                    <div
                      class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm"
                      style="background: linear-gradient(135deg, #8b5cf6, #ec4899)"
                    >
                      <i class="fa fa-pencil text-white"></i>
                    </div>
                    <div>
                      <p class="text-xs text-gray-400 uppercase tracking-wide">Updated By</p>
                      <p class="text-sm font-semibold text-gray-700">
                        {{ store.item?.updated_by || '—' }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- ===================== EMPLOYEES TAB ===================== -->
            <div v-can="'designation.view_employees'" v-else-if="activeTab === 'employees'">
              <!-- Empty State -->
              <div
                v-if="!store.item?.employees || store.item.employees.length === 0"
                class="flex flex-col items-center justify-center py-14 gap-3"
              >
                <div
                  class="w-20 h-20 rounded-full flex items-center justify-center shadow-md"
                  style="background: linear-gradient(135deg, #e0e7ff, #ddd6fe)"
                >
                  <i class="fa fa-users text-indigo-400 text-3xl"></i>
                </div>
                <p class="text-gray-500 font-medium text-sm">
                  No employees assigned to this designation.
                </p>
              </div>

              <!-- Employee Cards Grid -->
              <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                  v-for="(employee, index) in store.item.employees"
                  :key="employee.id"
                  class="relative rounded-2xl overflow-hidden shadow-md border border-gray-100 bg-white group hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
                >
                  <!-- Card Top Color Bar -->
                  <div
                    class="h-20 w-full flex items-end justify-center pb-0 relative"
                    :style="`background: linear-gradient(135deg, ${cardColors[index % cardColors.length][0]}, ${cardColors[index % cardColors.length][1]})`"
                  >
                    <!-- Status Badge -->
                    <div class="absolute top-2 right-2">
                      <span
                        :class="[
                          'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide',
                          employee.status === 'active'
                            ? 'bg-green-100 text-green-700'
                            : 'bg-red-100 text-red-600',
                        ]"
                      >
                        <i
                          :class="
                            employee.status === 'active'
                              ? 'fa fa-circle text-green-500'
                              : 'fa fa-circle text-red-400'
                          "
                          style="font-size: 6px"
                        ></i>
                        {{ employee.status || 'N/A' }}
                      </span>
                    </div>

                    <!-- Avatar -->
                    <div class="absolute -bottom-7 left-1/2 -translate-x-1/2">
                      <div
                        class="w-14 h-14 rounded-full border-4 border-white shadow-lg overflow-hidden flex items-center justify-center"
                        :style="`background: linear-gradient(135deg, ${cardColors[index % cardColors.length][0]}, ${cardColors[index % cardColors.length][1]})`"
                      >
                        <img
                          v-if="employee.image_url"
                          :src="employee.image_url"
                          :alt="employee.name"
                          class="w-full h-full object-cover"
                        />
                        <span v-else class="text-xl font-bold text-white leading-none select-none">
                          {{ getInitials(employee.name) }}
                        </span>
                      </div>
                    </div>
                  </div>

                  <!-- Card Body -->
                  <div class="pt-10 pb-5 px-4 text-center">
                    <h3 class="text-sm font-bold text-gray-900 truncate">{{ employee.name }}</h3>

                    <div class="mt-3 space-y-1.5 text-left">
                      <!-- Email -->
                      <div
                        class="flex items-center gap-2 text-xs text-gray-600 bg-gray-50 rounded-lg px-3 py-1.5"
                      >
                        <i class="fa fa-envelope text-indigo-400 w-3 text-center flex-shrink-0"></i>
                        <span class="truncate">{{ employee.email || '—' }}</span>
                      </div>
                      <!-- Phone -->
                      <div
                        class="flex items-center gap-2 text-xs text-gray-600 bg-gray-50 rounded-lg px-3 py-1.5"
                      >
                        <i class="fa fa-phone text-green-500 w-3 text-center flex-shrink-0"></i>
                        <span class="truncate">{{ employee.phone || '—' }}</span>
                      </div>
                      <!-- Username -->
                      <div
                        v-if="employee.username"
                        class="flex items-center gap-2 text-xs text-gray-600 bg-gray-50 rounded-lg px-3 py-1.5"
                      >
                        <i class="fa fa-at text-pink-400 w-3 text-center flex-shrink-0"></i>
                        <span class="truncate">{{ employee.username }}</span>
                      </div>
                    </div>

                    <!-- ID chip -->
                    <div class="mt-3 flex justify-center">
                      <span
                        class="text-[10px] font-semibold px-3 py-0.5 rounded-full"
                        :style="`background: linear-gradient(90deg, ${cardColors[index % cardColors.length][0]}22, ${cardColors[index % cardColors.length][1]}22); color: ${cardColors[index % cardColors.length][0]}`"
                      >
                        <i class="fa fa-hashtag mr-0.5"></i>ID: {{ employee.id }}
                      </span>
                    </div>
                  </div>
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
import { ref } from 'vue'
import { useDesignationStore } from '../../stores/designationStore'

const store = useDesignationStore()

const activeTab = ref('summary')

const tabs = [
  {
    key: 'summary',
    label: 'Summary',
    icon: 'fa-file-text-o',
    iconColor: 'text-indigo-500',
    color: ['#6366f1', '#8b5cf6'],
  },
  {
    key: 'employees',
    label: 'Employees',
    icon: 'fa-users',
    iconColor: 'text-green-500',
    color: ['#10b981', '#059669'],
  },
]

const cardColors = [
  ['#6366f1', '#8b5cf6'],
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
    .split(' ')
    .slice(0, 2)
    .map((w) => w[0]?.toUpperCase() ?? '')
    .join('')
}
</script>
