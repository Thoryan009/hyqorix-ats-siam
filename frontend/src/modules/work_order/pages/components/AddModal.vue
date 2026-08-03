<template>
  <BaseModal
    :isVisible="store.isModal"
    :title="t('demand_letter.add')"
    @close="store.handleToggleModal"
    :className="'xl:max-w-[50vw] w-full max-w-[95vw] sm:max-w-[90vw]'"
  >
    <div class="h-[80vh]">
      <CommonForm
        :clients="clients"
        :employees="employees"
        :onSubmit="handleSubmit"
        @cancel="store.handleToggleModal"
        :loading="submitLoading"
      />
    </div>
  </BaseModal>
</template>

<script setup>
import { computed } from 'vue'
import { useWorkOrderStore } from '@/modules/work_order/store/workOrderStore'
import { useWorkOrderMutations } from '@/modules/work_order/queries/useWorkOrderMutations'
import { useWorkOrderDataQuery } from '@/modules/work_order/queries/useWorkOrdersQuery'
import CommonForm from './CommonForm.vue'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate('work_order')
const store = useWorkOrderStore()
defineProps({
  employees: {
    type: Array,
    default: () => [],
  },
})

const { data } = useWorkOrderDataQuery()
const clients = computed(() => data.value?.data?.data?.clients ?? [])

// mutation
const { submit, submitLoading } = useWorkOrderMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal()
    store.handleReset(store.formData)
  },
})

// submit handler
const handleSubmit = async () => {
  const payload = new FormData()

  for (const key in store.formData) {
    const value = store.formData[key]

    // Skip null, undefined, empty values
    if (value === null || value === undefined || value === '') {
      continue
    }

    // Skip preview fields (only append actual file)
    if (key.includes('_preview')) {
      continue
    }

    payload.append(key, value)
  }

  await submit.mutateAsync(payload)
}
</script>
