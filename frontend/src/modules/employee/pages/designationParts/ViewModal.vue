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
