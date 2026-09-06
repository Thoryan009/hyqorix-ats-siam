
<template>
  <BaseModal
    :isVisible="store.isViewModal"
    :title="t('departments.view')"
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
                  {{ t('departments.department_id') }}: <span class="text-slate-200 font-semibold ml-1">{{ store.item?.id }}</span>
                </p>
                <p class="text-violet-300 text-xs mt-0.5 flex items-center gap-1">
                  <i class="fa fa-users"></i>
                  {{ t('departments.employees_assigned', employeeList.length, { count: employeeList.length }) }}
                </p>
              </div>
            </div>

            <!-- right: employee count badge -->
            <div class="flex flex-col items-center justify-center w-16 h-16 rounded-2xl shadow-lg shrink-0"
              style="background: linear-gradient(135deg, #7c3aed, #4f46e5)">
              <span class="text-2xl font-bold text-white leading-none">{{ employeeList.length }}</span>
              <span class="text-[8px] text-violet-200 uppercase tracking-wide mt-0.5">{{ t('departments.employees') }}</span>
            </div>
          </div>
        </div>


      </div>
    </ScrollableLayout>
  </BaseModal>
</template>

<script setup>
import { computed } from 'vue'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate()

const props = defineProps({
  store: {
    type: Object,
    required: true,
  },
})


const employeeList = computed(() => {
  const raw = props.store?.item?.employees
  if (!raw || typeof raw !== 'string') return []
  return raw
    .split(',')
    .map((e) => e.trim())
    .filter(Boolean)
})


</script>
