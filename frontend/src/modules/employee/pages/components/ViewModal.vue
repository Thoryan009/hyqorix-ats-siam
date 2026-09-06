<template>
  <BaseModal
    :isVisible="store.isViewModal"
    :title="t('employees.view')"
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
                    {{ store.item?.username || t('employees.not_provided') }}
                  </p>
                  <p class="text-teal-300 text-xs mt-0.5 flex items-center gap-1">
                    <i class="fa fa-briefcase"></i>
                    {{ store.item?.designation || t('employees.na') }}
                  </p>

                  <p class="text-indigo-300 text-xs mt-0.5 flex items-center gap-1">

                    <i class="fa fa-building"></i>
                    {{ store.item?.departments || t('employees.na') }}
                  </p>

                  <p class="text-slate-500 text-[11px] mt-0.5">
                    <i class="fa fa-hashtag mr-0.5"></i>
                    {{ t('employees.employee_id_label') }}: {{ store.item?.employee_id || t('employees.na') }}
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
                  <i class="fa fa-envelope-o mr-1"></i>{{ t('shared.labels.email') }}
                </p>
                <p
                  class="text-white text-sm font-medium mt-0.5 truncate"
                >{{ store.item?.email || '—' }}</p>
              </div>
              <div class="rounded-xl px-4 py-3" style="background: rgba(255, 255, 255, 0.07)">
                <p class="text-slate-400 text-[11px] font-semibold uppercase tracking-wide">
                  <i class="fa fa-phone mr-1"></i>{{ t('shared.labels.phone') }}
                </p>
                <p class="text-white text-sm font-medium mt-0.5">{{ store.item?.phone || '—' }}</p>
              </div>

              <div class="rounded-xl px-4 py-3" style="background: rgba(255, 255, 255, 0.07)">
                <p class="text-slate-400 text-[11px] font-semibold uppercase tracking-wide">
                  <i class="fa fa-whatsapp mr-1"></i>{{ t('shared.labels.whatsapp') }}
                </p>
                <p class="text-white text-sm font-medium mt-0.5">{{ store.item?.whatsapp_no || '—' }}</p>
              </div>


            </div>
          </div>
        </div>


      </div>
    </ScrollableLayout>
  </BaseModal>
</template>

<script setup>
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate()

defineProps({
  store: {
    type: Object,
    required: true,
  },
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
