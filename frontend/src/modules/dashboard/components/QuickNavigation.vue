<template>
  <section v-if="visibleGroups.length" class="w-full">
    <DashboardSectionHeading :title="t('dashboard.quick_navigation')" icon="fa fa-compass" />

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
      <div
        v-for="(group, groupIndex) in coloredGroups"
        :key="group.label"
        class="rounded-xl shadow-md border-2 overflow-hidden hover:shadow-xl transition-all duration-300 bg-linear-to-br from-white to-gray-50"
        :style="{ animationDelay: `${groupIndex * 80}ms`, borderColor: `${group.color}55` }"
      >
        <div
          class="px-3 py-2 border-b"
          :style="{
            borderColor: `${group.color}22`,
            background: `linear-gradient(to right, ${group.color}14, transparent)`,
          }"
        >
          <div class="flex items-center gap-2">
            <div
              class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 shadow-sm"
              :style="{ background: `linear-gradient(135deg, ${group.color}, ${group.color}dd)` }"
            >
              <i :class="[group.icon, 'text-xs text-white']"></i>
            </div>
            <div class="min-w-0">
              <h3 class="text-xs font-bold text-black uppercase tracking-wide truncate">
               {{ $t(group.label) }}
              </h3>
            </div>
          </div>
        </div>

        <div class="p-2 space-y-0.5">
          <router-link
            v-for="item in group.items"
            :key="item.path"
            :to="item.path"
            class="nav-link group flex items-center gap-2 rounded-lg px-2 py-1.5 transition-all duration-200 border border-transparent"
            :style="{ '--item-color': item.color }"
          >
            <div
              class="nav-icon w-7 h-7 rounded-md flex items-center justify-center shrink-0 transition-colors duration-200"
              :style="{
                backgroundColor: `${item.color}14`,
                color: item.color,
              }"
            >
              <i :class="[item.icon, 'text-xs']"></i>
            </div>

            <div class="min-w-0 flex-1">
              <p class="text-xs font-semibold text-black truncate">{{ $t(item.name) }}</p>
            </div>

            <i class="nav-chevron fa fa-angle-right text-xs shrink-0 transition-all duration-200"></i>
          </router-link>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'
import { usePermission } from '@/shared/composables/usePermission'
import { resolveQuickNavGroups } from '../config/quickNavGroups'
import DashboardSectionHeading from './DashboardSectionHeading.vue'
import { useI18n } from 'vue-i18n'
import {useTranslate} from '@/shared/composables/useTranslate'
import { usePrimaryColor } from '@/shared/composables/usePrimaryColor'

const { t } = useTranslate()
const { can } = usePermission()
const primaryColor = usePrimaryColor()

const navColors = computed(() => [primaryColor.value, '#0D71B9', '#E2232A', '#F59E0B', '#9333EA', '#6366F1'])

const visibleGroups = computed(() => resolveQuickNavGroups(can))

const coloredGroups = computed(() =>
  visibleGroups.value.map((group, groupIndex) => {
    const groupColor = navColors.value[groupIndex % navColors.value.length]

    return {
      ...group,
      color: groupColor,
      items: group.items.map((item, itemIndex) => ({
        ...item,
        color: navColors.value[(groupIndex + itemIndex + 1) % navColors.value.length],
      })),
    }
  }),
)
</script>

<style scoped>
@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(16px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.grid > div {
  animation: slideIn 0.5s ease-out forwards;
}

.nav-link:hover {
  border-color: color-mix(in srgb, var(--item-color) 22%, transparent);
  background-color: color-mix(in srgb, var(--item-color) 8%, transparent);
}

.nav-link:hover .nav-icon {
  background-color: var(--item-color) !important;
  color: #fff !important;
}

.nav-chevron {
  color: color-mix(in srgb, var(--item-color) 45%, transparent);
}

.nav-link:hover .nav-chevron {
  color: var(--item-color);
  transform: translateX(2px);
}
</style>
