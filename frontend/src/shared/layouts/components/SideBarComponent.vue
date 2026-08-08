<template>
  <aside
    :class="[
      'fixed inset-y-0 left-0 z-50 flex flex-col transition-all duration-300 ease-in-out shadow-2xl bg-primary-dark',
      'lg:relative lg:translate-x-0',
      isSidebarOpen ? 'translate-x-0' : '-translate-x-full',
      isSidebarCollapsed ? 'lg:w-16' : 'lg:w-84',
      'w-80',
    ]"
  >
    <!-- Logo -->
    <div class="flex h-16 shrink-0 items-center gap-2 px-4 border-b-2 border-white/50">
      <!-- Clickable company/logo -->
      <router-link to="/" class="flex items-center gap-2 min-w-0">
        <div
          class="bg-white h-10 w-10 flex items-center justify-center rounded-full border-2 p-0.75 border-primary/30"
        >
          <img
            v-if="!isSidebarCollapsed"
            :src="settingsData?.company_logo_url || '/norqel-logo.svg'"
            alt="Logo"
            class="h-8 w-auto shrink-0 rounded-full"
          />
        </div>

        <transition
          enter-active-class="transition-all duration-200 ease-out"
          enter-from-class="-translate-x-1.5 opacity-0"
          enter-to-class="translate-x-0 opacity-100"
          leave-active-class="transition-all duration-200 ease-in"
          leave-from-class="translate-x-0 opacity-100"
          leave-to-class="-translate-x-1.5 opacity-0"
        >
          <h1
            v-if="!isSidebarCollapsed"
            class="text-base font-extrabold text-white whitespace-nowrap overflow-hidden tracking-wide"
          >
            {{ settingsData?.company_name || 'Hyqorix' }}
          </h1>
        </transition>
      </router-link>

      <!-- Desktop collapse button -->
      <button
        @click="$emit('toggle-collapse')"
        class="hidden lg:flex ml-auto shrink-0 items-center justify-center rounded-full w-8 h-8 transition-all duration-300 text-white hover:bg-white/12"
      >
        <i
          :class="isSidebarCollapsed ? 'fa fa-chevron-right' : 'fa fa-chevron-left'"
          class="text-xs"
        ></i>
      </button>

      <!-- Mobile close -->
      <button
        @click="$emit('toggle-sidebar')"
        class="lg:hidden ml-auto rounded-lg p-2 transition-all duration-200 text-white"
      >
        <i class="fa fa-times"></i>
      </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-3 px-3 space-y-3">
      <div v-for="group in filteredGroups" :key="group.label" class="mb-4">
        <!-- Single item group -->
        <router-link
          v-if="group.items.length === 1"
          :to="group.items[0].path"
          :class="[
            'flex items-center gap-2 rounded-xl px-3 py-2 transition-all duration-200',
            route.path === group.items[0].path
              ? 'bg-primary text-white shadow-[0_2px_8px_rgba(0,0,0,0.2)]'
              : 'text-white hover:bg-white/10',
            isSidebarCollapsed ? 'justify-center' : '',
          ]"
        >
          <i :class="[group.icon, 'text-sm w-5 text-center shrink-0']"></i>
          <span
            v-if="!isSidebarCollapsed"
            class="text-sm font-bold uppercase tracking-wide truncate"
          >
          {{ t(group.label) }}
          </span>
        </router-link>

        <!-- Group Header -->
        <button
          v-else
          type="button"
          @click="toggleGroup(group.label)"
          :class="[
            'w-full flex items-center rounded-xl px-3 py-2 transition-all duration-200 cursor-pointer hover:bg-white/10',
            'text-white',
            isSidebarCollapsed ? 'justify-center' : 'justify-between',
          ]"
        >
          <div class="flex items-center gap-3">
            <i :class="[group.icon, 'text-sm w-5 text-center shrink-0']"></i>
            <span v-if="!isSidebarCollapsed" class="text-sm font-bold uppercase tracking-wide">
                {{ t(group.label) }}
            </span>
          </div>

          <i
            v-if="!isSidebarCollapsed"
            class="fa fa-chevron-down text-xs transition-transform duration-300"
            :class="openGroup === group.label ? 'rotate-180' : ''"
          ></i>
        </button>

        <!-- Accordion -->
        <div
          v-if="group.items.length > 1"
          :class="[
            'overflow-hidden transition-all duration-300 ease-out',
            openGroup === group.label
              ? 'mt-1 max-h-auto opacity-100'
              : 'max-h-0 opacity-0 pointer-events-none',
          ]"
        >
          <div class="space-y-1">
            <div v-for="(item, itemIndex) in group.items" :key="item.name">
              <p
                v-if="
                  item.section &&
                  !isSidebarCollapsed &&
                  (itemIndex === 0 || item.section !== group.items[itemIndex - 1]?.section)
                "
                class="px-3 pt-3 pb-1 text-xs font-semibold uppercase tracking-wider text-white/80"
                :class="itemIndex === 0 ? 'pt-1' : ''"
              >
                {{ item.section }}
              </p>

              <router-link
                :to="item.path"
                :class="[
                  'flex items-center ms-2 gap-3 rounded-xl px-3 py-2 text-base font-medium transition-all duration-200',
                  isNavItemActive(item.path)
                    ? 'bg-primary text-white shadow-[0_2px_8px_rgba(0,0,0,0.2)]'
                    : 'text-white hover:bg-white/10',
                  isSidebarCollapsed ? 'justify-center' : '',
                ]"
              >
                <i :class="[item.icon, 'text-sm w-5 text-center shrink-0']"></i>

                <span v-if="!isSidebarCollapsed" class="truncate">
                 {{ t(item.name) }}
                </span>

                <span
                  v-if="item.badge && !isSidebarCollapsed"
                  class="ml-auto shrink-0 text-xs font-bold px-2 py-0.5 rounded-full bg-white/20 text-white"
                >
                  {{ item.badge.value > 99 ? '99+' : item.badge.value }}
                </span>
              </router-link>

              <router-link
                v-for="child in item.children"
                :key="child.name"
                :to="child.path"
                :class="[
                  'flex items-center ms-6 gap-3 rounded-xl px-3 py-2 text-sm font-medium transition-all duration-200',
                  isNavItemActive(child.path)
                    ? 'bg-primary text-white shadow-[0_2px_8px_rgba(0,0,0,0.2)]'
                    : 'text-white/90 hover:bg-white/10',
                  isSidebarCollapsed ? 'justify-center ms-2' : '',
                ]"
              >
                <i :class="[child.icon, 'text-xs w-5 text-center shrink-0']"></i>

                <span v-if="!isSidebarCollapsed" class="truncate">
                 {{ t(child.name) }}
                </span>
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Logout -->
    <div class="shrink-0 px-3 py-3 border-t-2 border-white/50">
      <button
        @click="$emit('profile-action', 'logout')"
        :class="[
          'flex items-center gap-3 w-full rounded-xl px-3 py-2 transition-all duration-200 cursor-pointer text-white hover:bg-white/10',
          isSidebarCollapsed ? 'justify-center' : '',
        ]"
      >
        <i class="fa fa-sign-out text-sm w-5 text-center shrink-0"></i>
        <span v-if="!isSidebarCollapsed" class="text-base font-medium"> {{ t('navigation.logout') }} </span>
      </button>
    </div>
  </aside>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { navGroups } from '@/shared/layouts/navGroups'
import { usePermission } from '@/shared/composables/usePermission'
import { useSettingsQuery } from '@/modules/home/queries/useSettingsQuery'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const { can } = usePermission()

defineProps({
  isSidebarOpen: Boolean,
  isSidebarCollapsed: Boolean,
})

defineEmits(['toggle-sidebar', 'toggle-collapse', 'profile-action'])

const route = useRoute()

/* ------------------------------------------
   Filter Groups Based on Role
-------------------------------------------*/
const filteredGroups = computed(() => {
  return navGroups
    .map((group) => {
      const filteredItems = group.items
        .filter((item) => {
          if (!item.permission) return true
          return can(item.permission)
        })
        .map((item) => {
          if (!item.children?.length) return item

          const children = item.children.filter((child) => {
            if (!child.permission) return true
            return can(child.permission)
          })

          return { ...item, children }
        })

      return { ...group, items: filteredItems }
    })
    .filter((group) => group.items.length > 0)
})

const isNavItemActive = (path) => route.path === path

/* ------------------------------------------
   Accordion State
-------------------------------------------*/
const openGroup = ref('DASHBOARD')

const toggleGroup = (label) => {
  openGroup.value = openGroup.value === label ? null : label
}

watch(
  () => route.path,
  (newPath) => {
    const matchingGroup = filteredGroups.value.find((group) =>
      group.items.some(
        (item) =>
          item.path === newPath || item.children?.some((child) => child.path === newPath),
      ),
    )
    if (matchingGroup) {
      openGroup.value = matchingGroup.label
    }
  },
  { immediate: true },
)

const { data } = useSettingsQuery()
const settingsData = computed(() => data.value?.data || {})
</script>

<style scoped>
/* Smooth transitions for all elements */
* {
  transition-property: color, background-color, border-color, transform, box-shadow;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}
</style>
