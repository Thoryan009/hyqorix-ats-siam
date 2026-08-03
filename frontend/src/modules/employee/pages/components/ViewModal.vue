<template>
  <BaseModal
    :isVisible="store.isViewModal"
    :title="`View ${store.moduleName} Details`"
    :className="'max-w-[95vw] xl:max-w-[80vw]'"
    @close="store.handleToggleModal"
  >
    <ScrollableLayout>
      <div class="space-y-5 p-1">
        <!-- ══════════════ EMPLOYEE SUMMARY CARD ══════════════ -->
        <div
          class="relative rounded-2xl overflow-hidden shadow-xl"
          style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%)"
        >
          <!-- accent bar -->
          <div class="h-1 bg-gradient-to-r from-teal-400 via-cyan-400 to-indigo-500"></div>

          <!-- dot pattern -->
          <div
            class="absolute inset-0 opacity-[0.05]"
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
              <div class="flex items-center gap-4">
                <!-- avatar -->
                <div
                  class="w-16 h-16 rounded-2xl flex items-center justify-center text-white text-3xl shadow-lg flex-shrink-0"
                >
                  <img :src="store.item?.image_url" alt />
                </div>
<!-- <pre>{{ store.item }}</pre> -->
                <div>
                  <h2 class="text-xl font-bold text-white leading-tight">{{ store.item?.name }}</h2>
                  <p class="text-slate-300 text-xs mt-0.5 flex items-center gap-1">
                    <i class="fa fa-user-circle-o mr-0.5"></i>
                    {{ store.item?.username || 'Not Provided' }}
                  </p>
                  <p class="text-teal-300 text-xs mt-0.5 flex items-center gap-1">
                    <i class="fa fa-briefcase"></i>
                    {{ store.item?.designation || 'N/A' }}
                  </p>

                  <p class="text-indigo-300 text-xs mt-0.5 flex items-center gap-1">

                    <i class="fa fa-building"></i>
                    {{ store.item?.departments || 'N/A' }}
                  </p>

                  <p class="text-slate-500 text-[11px] mt-0.5">
                    <i class="fa fa-hashtag mr-0.5"></i>
                    ID: {{ store.item?.id }}
                    &nbsp;·&nbsp;
                    <i
                      class="fa fa-shield mr-0.5"
                    ></i>
                    {{ store.item?.roles }}
                  </p>

                </div>
              </div>
              <!-- Department Info -->

 <!-- <div class="p-4 ">
    <div
      v-if="departmentList.length"
      class="flex flex-wrap gap-2"
    >
      <div
        v-for="(department, index) in departmentList"
        :key="index"
        class="inline-flex items-center gap-2  text-cyan-300 text-sm font-semibold shadow-sm hover:shadow transition-all"
      >


        <span>{{ department }}</span>
      </div>
    </div>

    <div
      v-else
      class="text-sm text-gray-400 italic"
    >
      No departments assigned.
    </div>
  </div> -->

              <!-- status badge -->
              <span
                :class="
                  store.item?.status === 'active'
                    ? 'bg-emerald-400/20 text-emerald-300 border-emerald-500/30'
                    : 'bg-red-400/20 text-red-300 border-red-500/30'
                "
                class="inline-flex items-center gap-1.5 text-xs font-bold px-4 py-2 rounded-full border capitalize flex-shrink-0"
              >
                <i class="fa fa-circle" style="font-size: 7px"></i>
                {{ store.item?.status }}
              </span>
            </div>

            <!-- stats chips -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
              <div class="rounded-xl px-4 py-3" style="background: rgba(255, 255, 255, 0.07)">
                <p class="text-slate-400 text-[11px] font-semibold uppercase tracking-wide">
                  <i class="fa fa-envelope-o mr-1"></i>Email
                </p>
                <p
                  class="text-white text-sm font-medium mt-0.5 truncate"
                >{{ store.item?.email || '—' }}</p>
              </div>
              <div class="rounded-xl px-4 py-3" style="background: rgba(255, 255, 255, 0.07)">
                <p class="text-slate-400 text-[11px] font-semibold uppercase tracking-wide">
                  <i class="fa fa-phone mr-1"></i>Phone
                </p>
                <p class="text-white text-sm font-medium mt-0.5">{{ store.item?.phone || '—' }}</p>
              </div>

              <div class="rounded-xl px-4 py-3" style="background: rgba(255, 255, 255, 0.07)">
                <p class="text-slate-400 text-[11px] font-semibold uppercase tracking-wide">
                  <i class="fa fa-whatsapp mr-1"></i>WhatsApp
                </p>
                <p class="text-white text-sm font-medium mt-0.5">{{ store.item?.whatsapp_no || '—' }}</p>
              </div>


            </div>
          </div>
        </div>

        <!-- ══════════════ TABS ══════════════ -->
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
          <!-- tab bar -->
          <div class="flex border-b border-gray-100 bg-gray-50 overflow-x-auto">
            <button
              v-for="tab in tabs"
              :key="tab.key"
              @click="activeTab = tab.key"
              :class="[
                'flex items-center gap-2 px-6 py-3.5 text-xs font-semibold uppercase tracking-wider whitespace-nowrap transition-all border-b-2 -mb-px',
                activeTab === tab.key
                  ? 'border-teal-500 text-teal-600 bg-white'
                  : 'border-transparent text-gray-400 hover:text-gray-600 hover:bg-white/60',
              ]"
            >
              <i :class="tab.icon" class="text-sm"></i>
              {{ tab.label }}
              <span
                v-if="tab.badge != null"
                class="rounded-full px-2 py-0.5 text-[10px] font-bold"
                :class="
                  activeTab === tab.key ? 'bg-teal-100 text-teal-700' : 'bg-gray-200 text-gray-500'
                "
              >{{ tab.badge }}</span>
            </button>
          </div>

          <!-- ── Tab 1: Activity Logs ── -->
          <div v-if="activeTab === 'activity_logs'" class="p-5">
            <div
              v-if="!store.item?.activity_logs?.length"
              class="flex flex-col items-center justify-center py-14 text-gray-400"
            >
              <i class="fa fa-history text-4xl mb-3 opacity-30"></i>
              <p class="text-sm">No activity logs found.</p>
            </div>

            <div v-else class="space-y-3">
              <div
                v-for="log in store.item.activity_logs"
                :key="log.id"
                class="flex gap-4 p-4 rounded-xl border bg-gray-50 border-gray-100 hover:border-teal-200 hover:bg-teal-50/30 transition-colors"
              >
                <!-- action icon -->
                <div
                  class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5 shadow-sm"
                  :class="logActionBg(log.action)"
                >
                  <i :class="[logActionIcon(log.action), 'text-white text-sm']"></i>
                </div>

                <!-- content -->
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between gap-2 flex-wrap">
                    <span
                      :class="logActionBadge(log.action)"
                      class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full capitalize"
                    >{{ log.action }}</span>
                    <span class="text-gray-400 text-xs flex items-center gap-1 flex-shrink-0">
                      <i class="fa fa-clock-o"></i>
                      {{ log.created_at }}
                    </span>
                  </div>
                  <p class="text-gray-700 text-sm mt-1.5 leading-relaxed">{{ log.description }}</p>
                  <p class="text-gray-400 text-[11px] mt-1">
                    <i class="fa fa-hashtag mr-0.5"></i>
                    Log #{{ log.id }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- ── Tab 2: Summary ── -->
          <div v-can="'employee.view_summary'" v-if="activeTab === 'summary'" class="p-5 space-y-5">
            <!-- Designation info -->
            <div class="rounded-2xl overflow-hidden border border-teal-100 shadow-sm">
              <div
                class="px-4 py-2.5 flex items-center gap-2"
                style="background: linear-gradient(90deg, #0d9488, #0891b2)"
              >
                <i class="fa fa-id-badge text-white text-sm"></i>
                <span
                  class="text-white text-sm font-semibold uppercase tracking-wide"
                >Designation & Role</span>
              </div>
              <div class="p-4 bg-white grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div
                  class="flex items-center gap-3 bg-gray-50 rounded-xl p-3.5 border border-gray-100"
                >
                  <div
                    class="w-9 h-9 rounded-lg bg-teal-100 flex items-center justify-center flex-shrink-0"
                  >
                    <i class="fa fa-briefcase text-teal-600 text-sm"></i>
                  </div>
                  <div>
                    <p
                      class="text-[11px] text-gray-400 uppercase tracking-wide font-semibold"
                    >Designation</p>
                    <p
                      class="text-sm font-semibold text-gray-800"
                    >{{ store.item?.designation || '—' }}</p>
                  </div>
                </div>
                <div
                  class="flex items-center gap-3 bg-gray-50 rounded-xl p-3.5 border border-gray-100"
                >
                  <div
                    class="w-9 h-9 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0"
                  >
                    <i class="fa fa-shield text-indigo-500 text-sm"></i>
                  </div>
                  <div>
                    <p class="text-[11px] text-gray-400 uppercase tracking-wide font-semibold">Role</p>
                    <p class="text-sm font-semibold text-gray-800">{{ store.item?.roles || '—' }}</p>
                  </div>
                </div>
                <div
                  v-if="store.item?.designation_description"
                  class="flex items-start gap-3 bg-gray-50 rounded-xl p-3.5 border border-gray-100 sm:col-span-2"
                >
                  <div
                    class="w-9 h-9 rounded-lg bg-slate-100 flex items-center justify-center flex-shrink-0 mt-0.5"
                  >
                    <i class="fa fa-align-left text-slate-500 text-sm"></i>
                  </div>
                  <div>
                    <p
                      class="text-[11px] text-gray-400 uppercase tracking-wide font-semibold"
                    >Designation Description</p>
                    <p
                      class="text-sm text-gray-700 mt-0.5 leading-relaxed"
                    >{{ store.item.designation_description }}</p>
                  </div>
                </div>
              </div>
            </div>



            <!-- Audit trail -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div class="flex items-start gap-3 bg-blue-50 rounded-xl p-4 border border-blue-100">
                <div
                  class="w-9 h-9 rounded-full bg-blue-500 flex items-center justify-center text-white flex-shrink-0"
                >
                  <i class="fa fa-user-plus"></i>
                </div>
                <div>
                  <p
                    class="text-[11px] font-semibold text-blue-400 uppercase tracking-wider mb-0.5"
                  >Created By</p>
                  <p class="text-gray-800 font-semibold text-sm">{{ store.item?.created_by || '—' }}</p>
                </div>
              </div>
              <div
                class="flex items-start gap-3 bg-amber-50 rounded-xl p-4 border border-amber-100"
              >
                <div
                  class="w-9 h-9 rounded-full bg-amber-500 flex items-center justify-center text-white flex-shrink-0"
                >
                  <i class="fa fa-pencil"></i>
                </div>
                <div>
                  <p
                    class="text-[11px] font-semibold text-amber-500 uppercase tracking-wider mb-0.5"
                  >Updated By</p>
                  <p class="text-gray-800 font-semibold text-sm">{{ store.item?.updated_by || '—' }}</p>
                </div>
              </div>
              <div class="flex items-center gap-3 bg-gray-50 rounded-xl p-4 border border-gray-100">
                <div
                  class="w-8 h-8 rounded-lg bg-gray-200 flex items-center justify-center flex-shrink-0"
                >
                  <i class="fa fa-clock-o text-gray-500 text-sm"></i>
                </div>
                <div>
                  <p
                    class="text-[11px] text-gray-400 uppercase tracking-wider font-semibold mb-0.5"
                  >Created At</p>
                  <p class="text-gray-700 font-medium text-sm">{{ store.item?.created_at || '—' }}</p>
                </div>
              </div>
              <div class="flex items-center gap-3 bg-gray-50 rounded-xl p-4 border border-gray-100">
                <div
                  class="w-8 h-8 rounded-lg bg-gray-200 flex items-center justify-center flex-shrink-0"
                >
                  <i class="fa fa-refresh text-gray-500 text-sm"></i>
                </div>
                <div>
                  <p
                    class="text-[11px] text-gray-400 uppercase tracking-wider font-semibold mb-0.5"
                  >Updated At</p>
                  <p class="text-gray-700 font-medium text-sm">{{ store.item?.updated_at || '—' }}</p>
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

const activeTab = ref('activity_logs')

const tabs = computed(() => [
  {
    key: 'activity_logs',
    label: 'Activity Logs',
    icon: 'fa fa-history',
    badge: props.store.item?.activity_logs?.length ?? 0,
  },
  {
    key: 'summary',
    label: 'Summary',
    icon: 'fa fa-bar-chart',
    badge: null,
  },
])

// ── Activity log helpers ──
const logActionIcon = (action) => {
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

const logActionBg = (action) => {
  const map = {
    created: 'bg-gradient-to-br from-emerald-500 to-green-600',
    updated: 'bg-gradient-to-br from-blue-500 to-indigo-600',
    deleted: 'bg-gradient-to-br from-red-500 to-rose-600',
    restored: 'bg-gradient-to-br from-amber-500 to-orange-500',
    login: 'bg-gradient-to-br from-cyan-500 to-sky-600',
    logout: 'bg-gradient-to-br from-slate-500 to-slate-600',
    viewed: 'bg-gradient-to-br from-violet-500 to-purple-600',
  }
  return map[action?.toLowerCase()] ?? 'bg-gradient-to-br from-teal-500 to-teal-600'
}

const logActionBadge = (action) => {
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

const departmentList = computed(() => {
  if (!props.store.item?.departments) return []

  return props.store.item.departments
    .split(',')
    .map((item) => item.trim())
    .filter(Boolean)
})


</script>

<style scoped>
.tab-fade-enter-active,
.tab-fade-leave-active {
  transition: opacity 0.18s ease, transform 0.18s ease;
}
.tab-fade-enter-from {
  opacity: 0;
  transform: translateY(6px);
}
.tab-fade-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>
