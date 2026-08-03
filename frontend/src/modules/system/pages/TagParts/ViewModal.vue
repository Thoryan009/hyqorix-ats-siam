<template>
  <BaseModal
    :isVisible="store.isViewModal"
    :title="`View ${store.moduleName}`"
    @close="store.handleToggleModal"
    :className="'w-full xl:max-w-[40vw]'"
  >
    <div class="p-6 space-y-4">
      <div class="rounded-2xl border border-gray-100 bg-white shadow-sm p-5">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Name</p>
        <p class="text-lg font-bold text-gray-900">{{ store.item?.name }}</p>
      </div>

      <div class="rounded-2xl border border-gray-100 bg-white shadow-sm p-5 grid grid-cols-2 gap-4">
        <div>
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Created At</p>
          <p class="text-sm text-gray-700">{{ store.item?.created_at || '—' }}</p>
        </div>
        <div>
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Updated At</p>
          <p class="text-sm text-gray-700">{{ store.item?.updated_at || '—' }}</p>
        </div>
      </div>
    </div>
  </BaseModal>
</template>

<script setup>
import { useTagStore } from '@/modules/system/stores/TagStore'

const props = defineProps({
  store: { type: Object, required: true },
})

const store = useTagStore()
</script>
