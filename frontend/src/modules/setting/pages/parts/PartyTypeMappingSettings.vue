<template>
  <div v-if="!isLoading && !optionsLoading" class="space-y-4">
    <p class="text-sm text-gray-600">
      {{ t('setting.party_type_mapping.description') }}
    </p>

    <div class="space-y-3">
      <div
        v-for="row in formRows"
        :key="row.source_module"
        class="grid grid-cols-1 gap-2 sm:grid-cols-2 sm:items-center sm:gap-4"
      >
        <BaseLabel>{{ row.label }}</BaseLabel>
        <BaseSelect
          v-model="row.party_type_id"
          :options="getOptionsForRow(row.source_module)"
          :placeholder="t('setting.party_type_mapping.select_party_type')"
        />
      </div>
    </div>

    <div class="pt-4">
      <BaseButton :disabled="saveLoading" @click="handleSave">
        <span v-if="saveLoading">{{ t('shared.message.saving') }}</span>
        <span v-else>{{ t('setting.party_type_mapping.save_mappings') }}</span>
      </BaseButton>
    </div>
  </div>

  <div v-else class="py-10 text-center text-gray-500">{{ t('shared.message.loading') }}</div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { useTranslate } from '@/shared/composables/useTranslate'
import {
  usePartyTypeMappingsQuery,
  usePartyTypeMappingOptionsQuery,
  usePartyTypeMappingMutations,
} from '../../queries/usePartyTypeMappingQuery'

const { t } = useTranslate()

const { data: mappingsData, isLoading } = usePartyTypeMappingsQuery()
const { data: partyTypeOptions, isLoading: optionsLoading } = usePartyTypeMappingOptionsQuery()
const { mutateAsync, isPending: saveLoading } = usePartyTypeMappingMutations()

const formRows = ref([])

watch(
  mappingsData,
  (rows) => {
    if (!rows) return

    formRows.value = rows.map((row) => ({
      source_module: row.source_module,
      label: row.label,
      party_type_id: row.party_type_id ?? '',
    }))
  },
  { immediate: true },
)

const partyTypeSelectOptions = computed(() =>
  (partyTypeOptions.value ?? []).map((item) => ({
    id: item.party_type_id,
    name: item.name,
    code: item.code,
  })),
)

function getOptionsForRow(currentModule) {
  const selectedIds = new Set(
    formRows.value
      .filter((row) => row.source_module !== currentModule && row.party_type_id)
      .map((row) => String(row.party_type_id)),
  )

  return [
    { id: '', name: t('setting.party_type_mapping.none') },
    ...partyTypeSelectOptions.value.filter(
      (option) => !selectedIds.has(String(option.id)),
    ),
  ]
}

const handleSave = async () => {
  await mutateAsync({
    mappings: formRows.value.map((row) => ({
      source_module: row.source_module,
      party_type_id: row.party_type_id || null,
    })),
  })
}
</script>
