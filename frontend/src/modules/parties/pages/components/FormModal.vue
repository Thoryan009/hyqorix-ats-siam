<template>
  <BaseModal
    :isVisible="store.isModal || store.isEditModal"
    :title="modalTitle"
    @close="store.handleToggleModal"
  >
    <BaseForm :onSubmit="handleSubmit">
      <div class="space-y-2">
        <BaseLabel for="type">{{ t('parties.party_type') }}</BaseLabel>
        <BaseSelect
          id="type"
          v-model="formData.type"
          :options="partyTypeOptions"
          placeholder="Select type"
          :required="true"
        />
      </div>

      <div v-if="showSourceSelect" class="space-y-2">
        <BaseLabel for="source_id">{{ sourceSelectLabel }}</BaseLabel>
        <BaseSearchSelect
          id="source_id"
          v-model="sourceId"
          :options="sourceOptions"
          :placeholder="sourcePlaceholder"
          :disabled="isSourceLoading"
          :filter-fn="filterByCodeOrName"
          :required="true"
        />
      </div>

      <div class="space-y-2">
        <BaseLabel for="code">{{ t('parties.party_id') }}</BaseLabel>
        <BaseInput
          id="code"
          v-model="formData.code"
          placeholder="Eg: CL001"
          :required="true"
        />
      </div>

      <div class="space-y-2">
        <BaseLabel for="name">{{ t('parties.name') }}</BaseLabel>
        <BaseInput
          id="name"
          v-model="formData.name"
          placeholder="Eg: Gulf Engineering Co."
          :required="true"
        />
      </div>

      <div class="space-y-2">
        <BaseLabel for="opening_debit">{{ t('parties.opening_debit') }}</BaseLabel>
        <BaseInput
          id="opening_debit"
          type="number"
          step="0.01"
          v-model="formData.opening_debit"
          placeholder="0.00"
        />
      </div>

      <div class="space-y-2">
        <BaseLabel for="opening_credit">{{ t('parties.opening_credit') }}</BaseLabel>
        <BaseInput
          id="opening_credit"
          type="number"
          step="0.01"
          v-model="formData.opening_credit"
          placeholder="0.00"
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
        <BaseLabel for="remarks">{{ t('parties.remarks') }}</BaseLabel>
        <BaseTextArea
          id="remarks"
          v-model="formData.remarks"
          placeholder="Optional remarks"
        />
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
import BaseSearchSelect from '@/shared/components/base/BaseSearchSelect.vue'
import app from '@/shared/config/appConfig'
import { usePartyMutations } from '@/modules/parties/queries/usePartyMutations'
import { usePartySourceOptionsQuery } from '@/modules/parties/queries/usePartySourceOptionsQuery'
import { usePartyStore } from '@/modules/parties/store/partyStore'
import { useTranslate } from '@/shared/composables/useTranslate'
import { getPartySourceConfig, hasPartySource } from '../../data/partySourceConfig'
import { partyTypeOptions, statusOptions } from '../../data/partyOptions'

const { t } = useTranslate()
const store = usePartyStore()

const sourceId = ref('')

const emptyFormData = {
  code: '',
  type: '',
  name: '',
  opening_debit: 0,
  opening_credit: 0,
  remarks: '',
  status: 'active',
}

const sampleFormData = {
  code: 'CL001',
  type: 'Client',
  name: 'Gulf Engineering Co.',
  opening_debit: 0,
  opening_credit: 0,
  remarks: 'Needs manpower; pays recruitment fee',
  status: 'active',
}

const createDefaultForm = () =>
  app.moduleLocal ? { ...sampleFormData } : { ...emptyFormData }

const formData = ref(createDefaultForm())

const partyTypeRef = computed(() => formData.value.type)
const { data: sourceData, isLoading: isSourceLoading } = usePartySourceOptionsQuery(partyTypeRef)

const showSourceSelect = computed(
  () => store.isModal && !store.isEditModal && hasPartySource(formData.value.type),
)

const sourceSelectLabel = computed(() => {
  const config = getPartySourceConfig(formData.value.type)
  return config ? t(config.selectLabelKey) : ''
})

const sourceOptions = computed(() => {
  const items = sourceData.value ?? []
  return items.map((item) => ({
    id: item.id,
    name: `${item.code} – ${item.name}`,
    code: item.code,
  }))
})

const sourcePlaceholder = computed(() =>
  isSourceLoading.value ? t('parties.loading_source') : t('parties.search_source'),
)

const filterByCodeOrName = (option, query) => {
  const label = String(option?.name ?? '').toLowerCase()
  const code = String(option?.code ?? '').toLowerCase()
  return label.includes(query) || code.includes(query)
}

const modalTitle = computed(() =>
  store.isEditModal ? t('parties.edit') : t('parties.add'),
)

const isSaving = computed(() => submitLoading.value || updateLoading.value)

const resetForm = () => {
  formData.value = createDefaultForm()
  sourceId.value = ''
}

watch(
  () => formData.value.type,
  (newType, oldType) => {
    if (!store.isModal || store.isEditModal) return
    if (!oldType || newType === oldType) return
    sourceId.value = ''
    formData.value.code = ''
    formData.value.name = ''
  },
)

watch(sourceId, (value) => {
  if (!value) return

  const selected = (sourceData.value ?? []).find((item) => String(item.id) === String(value))
  if (!selected) return

  formData.value.code = selected.code ?? ''
  formData.value.name = selected.name ?? ''
})

watch(
  () => [store.isModal, store.isEditModal, store.item],
  () => {
    if (store.isEditModal && store.item) {
      sourceId.value = ''
      formData.value = {
        code: store.item.code ?? '',
        type: store.item.type ?? '',
        name: store.item.name ?? '',
        opening_debit: store.item.opening_debit ?? 0,
        opening_credit: store.item.opening_credit ?? 0,
        remarks: store.item.remarks ?? '',
        status: store.item.status_raw ?? 'active',
      }
      return
    }

    if (store.isModal) {
      resetForm()
    }
  },
  { immediate: true },
)

const { submit, submitLoading, update, updateLoading } = usePartyMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal()
    resetForm()
  },
})

const handleSubmit = async () => {
  if (store.isEditModal) {
    await update.mutateAsync({
      id: store.item?.id,
      data: { ...formData.value },
    })
    return
  }

  await submit.mutateAsync({ ...formData.value })
}
</script>
