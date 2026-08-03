<template>
  <form v-if="!isLoading" @submit.prevent="handleSubmit" class="space-y-4">
    <!-- <pre>{{ settingsData }}</pre> -->
    <!-- Software Name -->
    <div>
      <BaseLabel>{{ t('setting.basic.software_name') }}</BaseLabel>
      <BaseInput v-model="formData.software_name" required />
    </div>

    <!-- Company Name -->
    <div>
      <BaseLabel>{{ t('setting.basic.company_name') }}</BaseLabel>
      <BaseInput v-model="formData.company_name" required />
    </div>

    <!-- Company No. -->
    <div>
      <BaseLabel>{{ t('setting.basic.company_no') }}</BaseLabel>
      <BaseInput v-model="formData.company_no" />
    </div>

    <!-- Company No. Active -->
    <div class="space-y-2">
      <BaseLabel>{{ t('setting.basic.company_no_active') }}</BaseLabel>
      <div class="grid grid-cols-2 gap-3">
        <label
          class="flex items-center gap-3 p-4 border rounded-lg cursor-pointer transition"
          :class="
            formData.company_no_active == '1'
              ? 'border-blue-500 bg-blue-50'
              : 'border-gray-300'
          "
        >
          <input
            type="radio"
            v-model="formData.company_no_active"
            value="1"
            class="w-3 h-3"
          />
          <p class="font-medium">{{ t('setting.basic.yes') }}</p>
        </label>
        <label
          class="flex items-center gap-3 p-4 border rounded-lg cursor-pointer transition"
          :class="
            formData.company_no_active == '0'
              ? 'border-blue-500 bg-blue-50'
              : 'border-gray-300'
          "
        >
          <input
            type="radio"
            v-model="formData.company_no_active"
            value="0"
            class="w-3 h-3"
          />
          <p class="font-medium">{{ t('setting.basic.no') }}</p>
        </label>
      </div>
    </div>

    <!-- Phone -->
    <div>
      <BaseLabel>{{ t('setting.basic.company_phone') }}</BaseLabel>
      <BaseInput v-model="formData.company_phone" required />
    </div>

    <!-- Email -->
    <div>
      <BaseLabel>{{ t('setting.basic.company_email') }}</BaseLabel>
      <BaseInput v-model="formData.company_email" />
    </div>

    <!-- Address -->
    <div>
      <BaseLabel>{{ t('setting.basic.company_address') }}</BaseLabel>
      <BaseInput v-model="formData.company_address" />
    </div>

    <!-- Logo -->
    <div>
      <BaseLabel>{{ t('setting.basic.company_logo') }}</BaseLabel>
      <BaseFileInput
        accept=".jpg, .jpeg, .png"
        @change="handleFileChange($event, 'company_logo_file', 'company_logo_preview')"
        :fileName="fileName?.company_logo_file"
      />

      <BaseImagePreview
        :src="
          formData.company_logo_preview ? formData.company_logo_preview : formData?.company_logo_url
        "
        class="rounded-full w-30 h-30"
        :showCancel="false"
        @cancelImage="cancelImage('company_logo')"
        width="120px"
        height="120px"
      />
    </div>


    <!-- Fav Icon -->
    <div>
      <BaseLabel>{{ t('setting.basic.fav_icon') }}</BaseLabel>
      <BaseFileInput
        accept=".jpg, .jpeg, .png"
        @change="handleFileChange($event, 'fav_icon_file', 'fav_icon_preview')"
        :fileName="fileName?.fav_icon_file"
      />

      <BaseImagePreview
        :src="
          formData.fav_icon_preview ? formData.fav_icon_preview : formData?.fav_icon_url
        "
        class="rounded-full w-30 h-30"
        :showCancel="false"
        @cancelImage="cancelImage('fav_icon')"
        width="120px"
        height="120px"
      />
    </div>

    <!-- Login Background Image -->
    <div>
      <BaseLabel>{{ t('setting.basic.login_background_image') }}</BaseLabel>
      <BaseFileInput
        accept=".jpg, .jpeg, .png"
        @change="
          handleFileChange($event, 'login_background_image_file', 'login_background_image_preview')
        "
        :fileName="fileName?.login_background_image_file"
      />

      <BaseImagePreview
        :src="
          formData.login_background_image_preview
            ? formData.login_background_image_preview
            : formData?.login_background_image_url
        "
        class="rounded-md"
        :showCancel="false"
        @cancelImage="cancelImage('login_background_image')"
        width="400px"
        height="220px"
      />
    </div>
    <!-- Department -->
    <div class="space-y-2">
      <BaseLabel for="department_name">{{ t('setting.basic.expiry_report_notify_department') }}</BaseLabel>
      <BaseSelect
        id="department_name"
        v-model="formData.expiry_report_notify_department_id"
        :options="departments"
        placeholder="Select"
        :required="true"
      />
    </div>
    <div>
      <BaseLabel>{{ t('setting.basic.tasheer_appointment_email') }}</BaseLabel>
      <BaseInput v-model="formData.tasheer_appointment_email" />
    </div>
    <!-- Save -->
    <div class="pt-4">
      <BaseButton type="submit" :disabled="updateLoading">
        <span v-if="updateLoading">{{ t('setting.basic.saving') }}</span>
        <span v-else>{{ t('setting.basic.save_settings') }}</span>
      </BaseButton>
    </div>
  </form>

  <div v-else class="text-center py-10 text-gray-500">{{ t('setting.basic.loading') }}</div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useSettingStore } from '../../store/settingStore'
import { useSettingMutations } from '../../queries/useSettingMutations'
import { useSettingsQuery, useSettingDataQuery } from '../../queries/useSettingsQuery'
import { useFileHandler } from '@/shared/composables/useFileHandler'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const store = useSettingStore()

const formData = ref({
  id: '',
  software_name: '',
  company_name: '',
  company_no: '',
  company_no_active: '0',
  company_phone: '',
  company_email: '',
  company_address: '',
  company_logo_path: null,
  company_logo_preview: null,
  company_logo_file: null,
  company_logo_url: null,
  expiry_report_notify_department_id: null,
  tasheer_appointment_email: '',

  login_background_image_path: null,
  login_background_image_preview: null,
  login_background_image_file: null,
  login_background_image_url: null,

  fav_icon_path: null,
  fav_icon_preview: null,
  fav_icon_file: null,
  fav_icon_url: null,
})

const { handleFileChange, fileName, cancelImage } = useFileHandler(formData.value)

// Fetch settings (single record)
const { data, isLoading } = useSettingsQuery(1)
const { data: settingData, isLoading: settingDataLoading } = useSettingDataQuery(1)
const settingsData = computed(() => data.value?.data?.data || {})

// console.log('Fetched settings data:', settingData.value) // Debug log
// console.log('Fetched settings data:', settingsData.value) // Debug log

const departments = computed(() => settingData.value?.data?.data?.departments || [])

const { update, updateLoading } = useSettingMutations(store.moduleName, {
  onSuccess() {
    fileName.value.company_logo_file = null
    fileName.value.login_background_image_file = null
    fileName.value.fav_icon_file = null
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
  { immediate: true }
)

// Submit
const EXCLUDED_KEYS = [
  'company_logo_preview',
  'company_logo_url',
  'login_background_image_preview',
  'login_background_image_url',
  'fav_icon_preview',
  'fav_icon_url',
]

const handleSubmit = async () => {
  const payload = new FormData()

  for (const [key, value] of Object.entries(formData.value)) {
    if (value !== null && !EXCLUDED_KEYS.includes(key)) {
      payload.append(key, value)
    }
  }

  await update.mutateAsync({
    id: formData.value.id,
    payload,
  })
}
</script>
