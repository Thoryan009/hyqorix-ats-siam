<template>
  <BaseModal
    :isVisible="store.isEditModal"
    :title="t('demand_letter.edit')"
    @close="store.handleToggleModal"
    :className="'xl:max-w-[50vw] w-full max-w-[95vw] sm:max-w-[90vw]'"
  >
    <div class="h-[80vh]">
      <CommonForm
        :clients="clients"
        :employees="employees"
        :onSubmit="handleSubmit"
        @cancel="store.handleToggleModal"
        :loading="updateLoading"
      />
    </div>
  </BaseModal>
</template>

<script setup>
import CommonForm from './CommonForm.vue'
import { watch, computed } from 'vue'
import { useWorkOrderStore } from '../../store/workOrderStore'
import { useWorkOrderMutations } from '../../queries/useWorkOrderMutations'
import { useWorkOrderDataQuery } from '../../queries/useWorkOrdersQuery'
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

watch(
  () => store.item,
  (newItem) => {
    if (!newItem) return

    Object.keys(store.formData).forEach((key) => {
      store.formData[key] = newItem[key] ?? store.formData[key]
    })
  },
  { immediate: true },
)

const { update, updateLoading } = useWorkOrderMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal()
  },
  onError: (error) => {
    console.log('Custom error handling', error)
  },
})

const handleSubmit = async () => {
  const payload = new FormData()
  const itemId = store.formData.id || store.item?.id

  for (const key in store.formData) {
    const value = store.formData[key]

    // Skip null, undefined, empty values
    if (value === null || value === undefined || value === '') {
      continue
    }

    // Skip preview fields and id (only append actual file)
    if (key.includes('_preview') || key === 'id') {
      continue
    }

    payload.append(key, value)
  }

  await update.mutateAsync({ id: itemId, data: payload })
}
</script>
