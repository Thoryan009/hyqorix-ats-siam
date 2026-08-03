<template>
  <router-link
    v-if="topEmployee"
    to="/employee-performance"
    class="group shrink-0 no-underline"
    title="View employee performance"
  >
    <div
      class="flex items-center gap-2 rounded-full border border-amber-200/80 bg-linear-to-r from-amber-50 via-yellow-50 to-orange-50 px-2 py-1.5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-md"
    >
      <div class="relative shrink-0">
        <img
          v-if="topEmployee.image_url"
          :src="topEmployee.image_url"
          :alt="topEmployee.name"
          class="h-9 w-9 rounded-full border-2 border-white object-cover shadow-sm"
        />
        <div
          v-else
          class="flex h-9 w-9 items-center justify-center rounded-full border-2 border-white bg-amber-500 text-sm font-semibold text-white shadow-sm"
        >
          {{ initials }}
        </div>
        <span
          class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-amber-500 text-[9px] text-white shadow"
        >
          <i class="fa fa-trophy"></i>
        </span>
      </div>

      <div class="hidden min-w-0 text-left lg:block">
        <p class="text-[10px] font-semibold uppercase tracking-wide text-amber-700">
          {{ t('shared.labels.top_performer') }}
        </p>
        <p class="max-w-[130px] truncate text-sm font-semibold text-gray-900">
          {{ topEmployee.name }}
        </p>
      </div>

      <div
        class="flex shrink-0 items-center gap-1 rounded-full bg-amber-500 px-2.5 py-1 text-xs font-bold text-white shadow-sm"
      >
        <i class="fa fa-star text-[10px]"></i>
        <span>{{ topEmployee.points ?? 0 }}</span>
      </div>
    </div>
  </router-link>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { fetchTopEmployee } from '@/modules/employee/services/employeeService'
import { getUserInitials } from '@/shared/helpers/userHelpers'
import {useTranslate} from '@/shared/composables/useTranslate'
const { t } = useTranslate()
const topEmployee = ref(null)

const initials = computed(() => getUserInitials(topEmployee.value?.name))

onMounted(async () => {
  try {
    const result = await fetchTopEmployee()
    topEmployee.value = result?.data?.data ?? null
  } catch {
    topEmployee.value = null
  }
})
</script>
