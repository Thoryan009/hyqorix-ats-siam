<template>
  <BaseModal
    :isVisible="store.isModal || store.isEditModal"
    :title="modalTitle"
    @close="store.handleToggleModal"
  >
    <BaseForm :onSubmit="handleSubmit">
      <div class="space-y-2">
        <BaseLabel for="code">{{ t('party_types.code') }}</BaseLabel>
        <BaseInput
          id="code"
          v-model="formData.code"
          placeholder="Eg: Client"
          :required="true"
        />
      </div>

      <div class="space-y-2">
        <BaseLabel for="name">{{ t('party_types.name') }}</BaseLabel>
        <BaseInput
          id="name"
          v-model="formData.name"
          placeholder="Eg: Client"
          :required="true"
        />
      </div>

      <div class="space-y-2">
        <BaseLabel for="sort_order">{{ t('party_types.sort_order') }}</BaseLabel>
        <BaseInput
          id="sort_order"
          type="number"
          min="0"
          v-model="formData.sort_order"
          placeholder="0"
        />
      </div>

      <div class="space-y-2">
        <BaseLabel for="status">{{ t('shared.labels.status') }}</BaseLabel>
        <BaseSelect
          id="status"
          v-model="formData.status"
          :options="statusOptions"
          placeholder="Select status"
          :required="true"
        />
      </div>

      <div class="space-y-2">
        <BaseLabel>{{ t('party_types.apply_job_filter') }}</BaseLabel>
        <div class="grid grid-cols-2 gap-3">
          <label
            class="flex items-center gap-3 rounded-lg border p-4 transition cursor-pointer"
            :class="
              formData.apply_job_filter == '1'
                ? 'border-blue-500 bg-blue-50'
                : 'border-gray-300'
            "
          >
            <input
              type="radio"
              v-model="formData.apply_job_filter"
              value="1"
              class="h-3 w-3"
            />
            <p class="font-medium">{{ t('shared.labels.yes') }}</p>
          </label>
          <label
            class="flex items-center gap-3 rounded-lg border p-4 transition cursor-pointer"
            :class="
              formData.apply_job_filter == '0'
                ? 'border-blue-500 bg-blue-50'
                : 'border-gray-300'
            "
          >
            <input
              type="radio"
              v-model="formData.apply_job_filter"
              value="0"
              class="h-3 w-3"
            />
            <p class="font-medium">{{ t('shared.labels.no') }}</p>
          </label>
        </div>
      </div>

      <div class="flex justify-end gap-2 pt-4">
        <BaseButton
          class="bg-yellow-600 hover:bg-yellow-700"
          type="button"
          @click="store.handleToggleModal"
        >
          {{ t('shared.actions.cancel') }}
        </BaseButton>
        <BaseButton type="submit" :disabled="isSaving">
          <span v-if="isSaving">{{ t('shared.actions.save') }}...</span>
          <span v-else>{{ t('shared.actions.save') }}</span>
        </BaseButton>
      </div>
    </BaseForm>
  </BaseModal>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import BaseForm from '@/shared/components/base/BaseForm.vue'
import app from '@/shared/config/appConfig'
import { usePartyTypeMutations } from '@/modules/parties/queries/usePartyTypeMutations'
import { usePartyTypeStore } from '@/modules/parties/store/partyTypeStore'
import { useTranslate } from '@/shared/composables/useTranslate'
import { statusOptions } from '../../data/partyOptions'

const { t } = useTranslate()
const store = usePartyTypeStore()

const emptyFormData = {
  code: '',
  name: '',
  sort_order: 0,
  status: 'active',
  apply_job_filter: '0',
}

const sampleFormData = {
  code: 'Partner',
  name: 'Partner',
  sort_order: 10,
  status: 'active',
  apply_job_filter: '0',
}

const createDefaultForm = () =>
  app.moduleLocal ? { ...sampleFormData } : { ...emptyFormData }

const formData = ref(createDefaultForm())

const modalTitle = computed(() =>
  store.isEditModal ? t('party_types.edit') : t('party_types.add'),
)

const isSaving = computed(() => submitLoading.value || updateLoading.value)

const resetForm = () => {
  formData.value = createDefaultForm()
}

watch(
  () => [store.isModal, store.isEditModal, store.item],
  () => {
    if (store.isEditModal && store.item) {
      formData.value = {
        code: store.item.code ?? '',
        name: store.item.name ?? '',
        sort_order: store.item.sort_order ?? 0,
        status: store.item.status_raw ?? 'active',
        apply_job_filter: store.item.apply_job_filter ? '1' : '0',
      }
      return
    }

    if (store.isModal) {
      resetForm()
    }
  },
  { immediate: true },
)

const { submit, submitLoading, update, updateLoading } = usePartyTypeMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal()
    resetForm()
  },
})

const handleSubmit = async () => {
  const payload = {
    code: formData.value.code,
    name: formData.value.name,
    sort_order: Number(formData.value.sort_order) || 0,
    status: formData.value.status,
    apply_job_filter: formData.value.apply_job_filter === '1' ? 1 : 0,
  }

  if (store.isEditModal) {
    await update.mutateAsync({
      id: store.item?.id,
      data: payload,
    })
    return
  }

  await submit.mutateAsync(payload)
}
</script>
