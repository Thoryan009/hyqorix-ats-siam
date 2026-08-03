<template>
  <form v-if="!isLoading" @submit.prevent="handleEmbassySubmit" class="space-y-4">

    <!-- Embassy Name -->
    <div>
      <BaseLabel>{{ t('setting.embassy.embassy_name') }}</BaseLabel>
      <BaseInput v-model="formData.embassy_name"  />
    </div>

    <!-- Address -->
    <div>
      <BaseLabel>{{ t('setting.embassy.embassy_address') }}</BaseLabel>
      <BaseInput v-model="formData.embassy_address" />
    </div>

    <!-- Company Name for Embassy -->
    <div>
      <BaseLabel>{{ t('setting.embassy.embassy_company_name') }}</BaseLabel>
      <BaseInput v-model="formData.embassy_company_name" />
    </div>

    <!-- Company RL -->
    <div>
      <BaseLabel>{{ t('setting.embassy.company_rl') }}</BaseLabel>
      <BaseInput v-model="formData.company_rl" />
    </div>

    <!-- Save -->
    <div class="pt-4">
      <BaseButton type="submit" :disabled="updateEmbassyLoading">
        <span v-if="updateEmbassyLoading">{{ t('shared.message.saving') }}</span>
        <span v-else>{{ t('setting.embassy.save_settings') }}</span>
      </BaseButton>
    </div>
  </form>

  <div v-else class="text-center py-10 text-gray-500">{{ t('shared.message.loading') }}</div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

import { useSettingStore } from '../../store/settingStore'
import { useSettingMutations } from '../../queries/useSettingMutations'
import { useSettingsQuery } from '../../queries/useSettingsQuery'

import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const store = useSettingStore()

const formData = ref({
  id: '',
  embassy_name: '',
  embassy_address: '',
  embassy_company_name: '',
  company_rl: '',
})


// Fetch settings (single record)
const { data, isLoading } = useSettingsQuery(1)
const settingsData = computed(() => data.value?.data?.data || {})

const { updateEmbassy, updateEmbassyLoading } = useSettingMutations(store.moduleName, {
  onSuccess() {
  },
  onError: (error) => {
    console.log('Custom error handling', error)
  },
})

watch(
  settingsData,
  (newItem) => {
    if (!newItem) return

    Object.keys(formData.value).forEach((key) => {
      formData.value[key] = newItem[key] ?? formData.value[key]
    })
  },
  { immediate: true },
)

const handleEmbassySubmit = async () => {
    await updateEmbassy.mutateAsync({
    id: formData.value.id,
    payload: formData.value,
  })
}
</script>
