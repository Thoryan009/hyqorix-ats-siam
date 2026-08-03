<template>
  <router-link
    v-if="topAgent"
    to="/agent-performance"
    class="group shrink-0 no-underline"
    title="View agent performance"
  >
    <div
      class="flex items-center gap-2 rounded-full border border-green-200/80 bg-linear-to-r from-green-50 via-green-100 to-green-200 px-2 py-1.5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-green-300 hover:shadow-md"
    >
      <div class="relative shrink-0">
        <img
          v-if="topAgent.agent_image_url"
          :src="topAgent.agent_image_url"
          :alt="topAgent.name"
          class="h-9 w-9 rounded-full border-2 border-white object-cover shadow-sm"
        />
        <div
          v-else
          class="flex h-9 w-9 items-center justify-center rounded-full border-2 border-white bg-green-500 text-sm font-semibold text-white shadow-sm"
        >
          {{ initials }}
        </div>
        <span
          class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-green-500 text-[9px] text-white shadow"
        >
          <i class="fa fa-trophy"></i>
        </span>
      </div>

      <div class="hidden min-w-0 text-left lg:block">
        <p class="text-[10px] font-semibold uppercase tracking-wide text-green-700">
                    {{ t('shared.labels.top_agent') }}

        </p>
        <p class="max-w-[130px] truncate text-sm font-semibold text-gray-900">
          {{ topAgent.name }}
        </p>
      </div>

      <div
        class="flex shrink-0 items-center gap-1 rounded-full bg-green-500 px-2.5 py-1 text-xs font-bold text-white shadow-sm"
      >
        <i class="fa fa-star text-[10px]"></i>
        <span>{{ topAgent.points ?? 0 }}</span>
      </div>
    </div>
  </router-link>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { fetchTopAgent } from '@/modules/agent/services/agentService'
import { getUserInitials } from '@/shared/helpers/userHelpers'
import {useTranslate} from '@/shared/composables/useTranslate'
const { t } = useTranslate()
const topAgent = ref(null)

const initials = computed(() => getUserInitials(topAgent.value?.name))

onMounted(async () => {
  try {
    const result = await fetchTopAgent()
      // console.log(JSON.stringify(result, null, 2))
      console.log('TOP AGENT', result)
    topAgent.value = result?.data ?? null
      console.log('TOP AGENT', topAgent.value)
  } catch {
    topAgent.value = null
  }
})
</script>
