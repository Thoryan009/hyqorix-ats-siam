<template>
  <BaseModal
    :isVisible="store.isViewModal"
    :title="`View ${store.moduleName} Details`"
    @close="store.handleToggleModal"
    :className="'max-w-[95vw] xl:max-w-[70vw]'"
  >
    <ScrollableLayout>
      <div class="space-y-5 p-1">
        <!-- ══════════════ ACTIVITY LOG SUMMARY CARD ══════════════ -->
        <div
          class="relative rounded-2xl overflow-hidden shadow-xl"
          style="background: linear-gradient(135deg, #1e293b 0%, #334155 55%, #1e293b 100%)"
        >
          <!-- top accent -->
          <div class="h-1 bg-gradient-to-r from-violet-500 via-pink-500 to-rose-500"></div>

          <!-- dot pattern -->
          <div
            class="absolute inset-0 opacity-[0.06]"
            style="
              background-image:
                radial-gradient(circle at 20% 80%, #fff 1px, transparent 1px),
                radial-gradient(circle at 80% 20%, #fff 1px, transparent 1px);
              background-size: 28px 28px;
            "
          ></div>

          <div class="relative p-6">
            <!-- header row -->
            <div class="flex items-start justify-between gap-4 flex-wrap mb-5">
              <div class="flex items-start gap-4">
                <div
                  class="w-14 h-14 rounded-xl flex items-center justify-center shadow-lg flex-shrink-0"
                  :class="actionBgClass(store.item?.action)"
                >
                  <i :class="actionIcon(store.item?.action)" class="text-white text-2xl"></i>
                </div>
                <div>
                  <h2 class="text-xl font-bold text-white leading-tight capitalize">
                    {{ store.item?.action }} Action
                  </h2>
                  <p class="text-slate-400 text-xs mt-1">
                    <i class="fa fa-hashtag mr-1 text-slate-500"></i>Log ID: {{ store.item?.id }}
                  </p>
                </div>
              </div>

              <!-- action badge -->
              <span
                :class="actionBadgeClass(store.item?.action)"
                class="inline-flex items-center gap-1.5 text-xs font-bold px-4 py-2 rounded-full capitalize flex-shrink-0"
              >
                <i :class="actionIcon(store.item?.action)"></i>
                {{ store.item?.action }}
              </span>
            </div>

            <!-- stats chips -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div class="rounded-xl px-4 py-3" style="background: rgba(255, 255, 255, 0.08)">
                <p class="text-slate-400 text-[11px] font-semibold uppercase tracking-wide">
                  <i class="fa fa-user-o mr-1"></i> Performed By
                </p>
                <p class="text-white text-sm font-bold mt-0.5 truncate">
                  {{ store.item?.user_name || '—' }}
                </p>
              </div>
              <div class="rounded-xl px-4 py-3" style="background: rgba(255, 255, 255, 0.08)">
                <p class="text-slate-400 text-[11px] font-semibold uppercase tracking-wide">
                  <i class="fa fa-bolt mr-1"></i> Action
                </p>
                <p class="text-white text-sm font-bold mt-0.5 capitalize">
                  {{ store.item?.action || '—' }}
                </p>
              </div>
              <div class="rounded-xl px-4 py-3" style="background: rgba(255, 255, 255, 0.08)">
                <p class="text-slate-400 text-[11px] font-semibold uppercase tracking-wide">
                  <i class="fa fa-clock-o mr-1"></i> Timestamp
                </p>
                <p class="text-white text-sm font-bold mt-0.5">
                  {{ store.item?.created_at || '—' }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- ══════════════ TABS ══════════════ -->
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
          <!-- tab bar -->
          <div class="flex border-b border-gray-100 bg-gray-50">
            <button
              v-for="tab in tabs"
              :key="tab.key"
              @click="activeTab = tab.key"
              :class="[
                'flex items-center gap-2 px-6 py-3.5 text-xs font-semibold uppercase tracking-wider whitespace-nowrap transition-all border-b-2 -mb-px',
                activeTab === tab.key
                  ? 'border-violet-500 text-violet-600 bg-white'
                  : 'border-transparent text-gray-400 hover:text-gray-600 hover:bg-white/60',
              ]"
            >
              <i :class="tab.icon" class="text-sm"></i>
              {{ tab.label }}
            </button>
          </div>

          <!-- ── Tab 1: User ── -->
          <div v-if="activeTab === 'user'" class="p-5">
            <div
              v-if="!store.item?.user"
              class="flex flex-col items-center justify-center py-14 text-gray-400"
            >
              <i class="fa fa-user-times text-4xl mb-3 opacity-30"></i>
              <p class="text-sm">No user information available.</p>
            </div>
            <div v-else class="space-y-4">
              <!-- user banner -->
              <div
                class="flex items-center gap-4 bg-violet-50 rounded-xl p-4 border border-violet-100"
              >
                <div
                  class="w-14 h-14 rounded-2xl flex items-center justify-center text-white text-2xl shadow-md flex-shrink-0"
                  style="background: linear-gradient(135deg, #7c3aed, #6d28d9)"
                >
                  <i class="fa fa-user"></i>
                </div>
                <div>
                  <h3 class="text-gray-900 font-bold text-base">{{ store.item.user.name }}</h3>
                  <p class="text-gray-400 text-xs mt-0.5 flex items-center gap-1">
                    <i class="fa fa-envelope-o"></i> {{ store.item.user.email }}
                  </p>
                </div>
                <div class="ml-auto flex-shrink-0">
                  <span
                    class="inline-flex items-center gap-1.5 bg-violet-100 text-violet-700 text-xs font-bold px-3 py-1 rounded-full"
                  >
                    <i class="fa fa-hashtag"></i> ID: {{ store.item.user.id }}
                  </span>
                </div>
              </div>

              <!-- user details -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div
                  class="flex items-center gap-3 bg-gray-50 rounded-xl p-3.5 border border-gray-100"
                >
                  <div
                    class="w-9 h-9 rounded-lg bg-violet-100 flex items-center justify-center flex-shrink-0"
                  >
                    <i class="fa fa-hashtag text-violet-500 text-sm"></i>
                  </div>
                  <div>
                    <p class="text-[11px] text-gray-400 uppercase tracking-wide font-semibold">
                      User ID
                    </p>
                    <p class="text-sm font-semibold text-gray-800"># {{ store.item.user.id }}</p>
                  </div>
                </div>

                <div
                  class="flex items-center gap-3 bg-gray-50 rounded-xl p-3.5 border border-gray-100"
                >
                  <div
                    class="w-9 h-9 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0"
                  >
                    <i class="fa fa-user-o text-indigo-500 text-sm"></i>
                  </div>
                  <div>
                    <p class="text-[11px] text-gray-400 uppercase tracking-wide font-semibold">
                      Full Name
                    </p>
                    <p class="text-sm font-semibold text-gray-800">{{ store.item.user.name }}</p>
                  </div>
                </div>

                <div
                  class="flex items-center gap-3 bg-gray-50 rounded-xl p-3.5 border border-gray-100 sm:col-span-2"
                >
                  <div
                    class="w-9 h-9 rounded-lg bg-sky-100 flex items-center justify-center flex-shrink-0"
                  >
                    <i class="fa fa-envelope-o text-sky-500 text-sm"></i>
                  </div>
                  <div>
                    <p class="text-[11px] text-gray-400 uppercase tracking-wide font-semibold">
                      Email
                    </p>
                    <p class="text-sm font-semibold text-gray-800">{{ store.item.user.email }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- ── Tab 2: Summary ── -->
          <div v-if="activeTab === 'summary'" class="p-5 space-y-4">
            <!-- quick stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
              <div class="bg-slate-50 border border-slate-100 rounded-xl p-3 text-center">
                <i class="fa fa-hashtag text-slate-500 text-xl mb-1"></i>
                <p class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider">
                  Log ID
                </p>
                <p class="text-gray-800 font-bold text-sm mt-0.5">{{ store.item?.id }}</p>
              </div>
              <div
                class="border rounded-xl p-3 text-center"
                :class="actionStatClass(store.item?.action)"
              >
                <i :class="actionIcon(store.item?.action)" class="text-xl mb-1"></i>
                <p class="text-[10px] font-semibold uppercase tracking-wider">Action</p>
                <p class="font-bold text-sm mt-0.5 capitalize">{{ store.item?.action }}</p>
              </div>
              <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-3 text-center">
                <i class="fa fa-user-o text-indigo-500 text-xl mb-1"></i>
                <p class="text-[10px] text-indigo-500 font-semibold uppercase tracking-wider">
                  User
                </p>
                <p class="text-gray-800 font-bold text-sm mt-0.5 truncate">
                  {{ store.item?.user_name || '—' }}
                </p>
              </div>
              <div class="bg-sky-50 border border-sky-100 rounded-xl p-3 text-center">
                <i class="fa fa-clock-o text-sky-500 text-xl mb-1"></i>
                <p class="text-[10px] text-sky-500 font-semibold uppercase tracking-wider">Time</p>
                <p class="text-gray-800 font-bold text-[11px] mt-0.5">
                  {{ store.item?.created_at || '—' }}
                </p>
              </div>
            </div>

            <!-- description -->
            <div class="rounded-2xl overflow-hidden border border-gray-100 shadow-sm">
              <div
                class="px-4 py-2.5 flex items-center gap-2"
                style="background: linear-gradient(90deg, #7c3aed, #6366f1)"
              >
                <i class="fa fa-align-left text-white text-sm"></i>
                <span class="text-white text-sm font-semibold uppercase tracking-wide"
                  >Description</span
                >
              </div>
              <div class="p-4 bg-white">
                <p class="text-gray-700 text-sm leading-relaxed">
                  {{ store.item?.description || '— No description —' }}
                </p>
              </div>
            </div>

            <!-- context -->
            <div class="rounded-2xl overflow-hidden border border-gray-100 shadow-sm">
              <div
                class="px-4 py-2.5 flex items-center gap-2"
                style="background: linear-gradient(90deg, #7cf3ea, #f336f1)"
              >
                <i class="fa fa-align-left text-white text-sm"></i>
                <span class="text-white text-sm font-semibold uppercase tracking-wide"
                  >Context</span
                >
              </div>
              <div class="p-4 bg-white">
                <p class="text-gray-700 text-sm leading-relaxed">
                  {{ store.item?.context || '— No context —' }}
                </p>
              </div>
            </div>

            <!-- meta info -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div
                class="flex items-center gap-3 bg-gray-50 rounded-xl p-3.5 border border-gray-100"
              >
                <div
                  class="w-9 h-9 rounded-lg bg-violet-100 flex items-center justify-center flex-shrink-0"
                >
                  <i class="fa fa-user-o text-violet-500 text-sm"></i>
                </div>
                <div>
                  <p class="text-[11px] text-gray-400 uppercase tracking-wide font-semibold">
                    Performed By
                  </p>
                  <p class="text-sm font-semibold text-gray-800">
                    {{ store.item?.user_name || '—' }}
                  </p>
                </div>
              </div>

              <div
                class="flex items-center gap-3 bg-gray-50 rounded-xl p-3.5 border border-gray-100"
              >
                <div
                  class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0"
                  :class="actionIconBg(store.item?.action)"
                >
                  <i
                    :class="[
                      actionIcon(store.item?.action),
                      actionIconColor(store.item?.action),
                      'text-sm',
                    ]"
                  ></i>
                </div>
                <div>
                  <p class="text-[11px] text-gray-400 uppercase tracking-wide font-semibold">
                    Action Type
                  </p>
                  <p class="text-sm font-semibold text-gray-800 capitalize">
                    {{ store.item?.action || '—' }}
                  </p>
                </div>
              </div>

              <div
                class="flex items-center gap-3 bg-gray-50 rounded-xl p-3.5 border border-gray-100 sm:col-span-2"
              >
                <div
                  class="w-9 h-9 rounded-lg bg-gray-200 flex items-center justify-center flex-shrink-0"
                >
                  <i class="fa fa-clock-o text-gray-500 text-sm"></i>
                </div>
                <div>
                  <p class="text-[11px] text-gray-400 uppercase tracking-wide font-semibold">
                    Logged At
                  </p>
                  <p class="text-sm font-semibold text-gray-800">
                    {{ store.item?.created_at || '—' }}
                  </p>
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

defineProps({
  store: {
    type: Object,
    required: true,
  },
})

const activeTab = ref('user')

const tabs = [
  { key: 'user', label: 'User', icon: 'fa fa-user-o' },
  { key: 'summary', label: 'Summary', icon: 'fa fa-bar-chart' },
]

const actionIcon = (action) => {
  const map = {
    created: 'fa fa-plus-circle',
    updated: 'fa fa-pencil',
    deleted: 'fa fa-trash',
    restored: 'fa fa-undo',
    login: 'fa fa-sign-in',
    logout: 'fa fa-sign-out',
    viewed: 'fa fa-eye',
  }
  return map[action?.toLowerCase()] ?? 'fa fa-bolt'
}

const actionBgClass = (action) => {
  const map = {
    created: 'bg-gradient-to-br from-emerald-500 to-green-600',
    updated: 'bg-gradient-to-br from-blue-500 to-indigo-600',
    deleted: 'bg-gradient-to-br from-red-500 to-rose-600',
    restored: 'bg-gradient-to-br from-amber-500 to-orange-500',
    login: 'bg-gradient-to-br from-cyan-500 to-sky-600',
    logout: 'bg-gradient-to-br from-slate-500 to-slate-600',
    viewed: 'bg-gradient-to-br from-violet-500 to-purple-600',
  }
  return map[action?.toLowerCase()] ?? 'bg-gradient-to-br from-slate-500 to-slate-600'
}

const actionBadgeClass = (action) => {
  const map = {
    created: 'bg-emerald-100 text-emerald-700',
    updated: 'bg-blue-100 text-blue-700',
    deleted: 'bg-red-100 text-red-600',
    restored: 'bg-amber-100 text-amber-700',
    login: 'bg-cyan-100 text-cyan-700',
    logout: 'bg-slate-100 text-slate-600',
    viewed: 'bg-violet-100 text-violet-700',
  }
  return map[action?.toLowerCase()] ?? 'bg-gray-100 text-gray-600'
}

const actionStatClass = (action) => {
  const map = {
    created: 'bg-emerald-50 border-emerald-100 text-emerald-600',
    updated: 'bg-blue-50 border-blue-100 text-blue-600',
    deleted: 'bg-red-50 border-red-100 text-red-600',
    restored: 'bg-amber-50 border-amber-100 text-amber-600',
    login: 'bg-cyan-50 border-cyan-100 text-cyan-600',
    logout: 'bg-slate-50 border-slate-100 text-slate-600',
    viewed: 'bg-violet-50 border-violet-100 text-violet-600',
  }
  return map[action?.toLowerCase()] ?? 'bg-gray-50 border-gray-100 text-gray-600'
}

const actionIconBg = (action) => {
  const map = {
    created: 'bg-emerald-100',
    updated: 'bg-blue-100',
    deleted: 'bg-red-100',
    restored: 'bg-amber-100',
    login: 'bg-cyan-100',
    logout: 'bg-slate-100',
    viewed: 'bg-violet-100',
  }
  return map[action?.toLowerCase()] ?? 'bg-gray-100'
}

const actionIconColor = (action) => {
  const map = {
    created: 'text-emerald-500',
    updated: 'text-blue-500',
    deleted: 'text-red-500',
    restored: 'text-amber-500',
    login: 'text-cyan-500',
    logout: 'text-slate-500',
    viewed: 'text-violet-500',
  }
  return map[action?.toLowerCase()] ?? 'text-gray-500'
}
</script>
