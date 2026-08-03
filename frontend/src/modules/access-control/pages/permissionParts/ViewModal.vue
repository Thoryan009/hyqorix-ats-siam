<template>
  <BaseModal
    :isVisible="store.isViewModal"
    :title="`View ${store.moduleName} Details`"
    @close="store.handleToggleModal"
    :className="'max-w-[95vw] xl:max-w-[40vw]'"
  >
    <ScrollableLayout>
      <div class="space-y-5 p-2">
        <!-- Header Card -->
        <div class="rounded-2xl overflow-hidden shadow border border-gray-100 bg-white">
          <div class="p-6 flex items-start gap-4">
            <div
              class="w-14 h-14 rounded-xl flex items-center justify-center shadow-sm flex-shrink-0"
              style="background: linear-gradient(135deg, #8b5cf6, #a855f7)"
            >
              <i class="fa fa-key text-white text-2xl"></i>
            </div>
            <div class="flex-1">
              <h2 class="text-lg font-bold text-gray-900 leading-tight">
                {{ store.item?.name }}
              </h2>
              <p class="text-sm text-gray-500 mt-1 flex items-center gap-1.5">
                <i class="fa fa-link text-gray-400"></i>
                <span>{{ store.item?.slug || '—' }}</span>
              </p>
              <p class="text-sm text-gray-500 mt-1 flex items-center gap-1.5">
                <i class="fa fa-hashtag text-gray-400"></i>
                <span
                  >ID: <strong class="text-gray-700">{{ store.item?.id }}</strong></span
                >
              </p>
            </div>
          </div>
        </div>

        <!-- Audit Info Card -->
        <div class="rounded-2xl shadow-sm border border-gray-100 overflow-hidden bg-white">
          <div
            class="px-4 py-2.5 flex items-center gap-2"
            style="background: linear-gradient(90deg, #374151, #4b5563)"
          >
            <i class="fa fa-info-circle text-white text-sm"></i>
            <span class="text-white text-sm font-semibold tracking-wide uppercase">Audit Info</span>
          </div>
          <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Created At -->
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
            <!-- Updated At -->
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
          </div>
        </div>
      </div>
    </ScrollableLayout>
  </BaseModal>
</template>

<script setup>
import { usepermissionStore } from '@/modules/access-control/stores/permissionStore'

const store = usepermissionStore()
</script>
