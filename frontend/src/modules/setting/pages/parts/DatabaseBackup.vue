<template>
  <div class="space-y-6">
    <!-- Download Backup -->
    <div>
      <h3 class="text-lg font-medium text-gray-900 mb-4">{{ t('setting.backup.download_database_backup') }}</h3>
      <p class="text-sm text-gray-600 mb-4">
        {{ t('setting.backup.download_database_backup_description') }}
      </p>

      <BaseButton @click="handleDownloadBackup" :disabled="backupDBLoading">
        <span v-if="backupDBLoading">{{ t('setting.backup.preparing_download') }}</span>
        <span v-else>{{ t('setting.backup.download_backup') }}</span>
      </BaseButton>
    </div>

    <!-- Schedule Automatic Backup -->
    <div class="border-t pt-6">
      <h3 class="text-lg font-medium text-gray-900 mb-4">{{ t('setting.backup.schedule_automatic_backup') }}</h3>
      <p class="text-sm text-gray-600 mb-4">
        {{ t('setting.backup.schedule_automatic_backup_description') }}
      </p>

      <form @submit.prevent="handleSaveScheduledBackup" class="space-y-4">
        <div>
          <BaseLabel>{{ t('setting.backup.scheduled_backup_email') }}</BaseLabel>
          <BaseInput
            v-model="scheduledBackupData.email"
            type="email"
            :placeholder="t('setting.backup.enter_scheduled_backup_email')"
            required
          />
        </div>

        <div class="flex gap-3 pt-2">
          <BaseButton type="submit" :disabled="scheduleLoading">
            <span v-if="scheduleLoading">{{ t('shared.message.saving') }}</span>
            <span v-else>{{ scheduledBackupData.id ? t('setting.backup.update_email') : t('setting.backup.save_email') }}</span>
          </BaseButton>
        </div>
      </form>

      <!-- Current Schedule Info -->
      <div
        v-if="scheduledBackupData.id"
        class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-md"
      >
        <p class="text-sm text-blue-800">
          <strong>{{ t('setting.backup.current_schedule') }}</strong> {{ t('setting.backup.current_schedule_message', { email: scheduledBackupData.email }) }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { useSettingsQuery } from '../../queries/useSettingsQuery'
import { useSettingMutations } from '../../queries/useSettingMutations'
import { useSettingStore } from '../../store/settingStore'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const { data } = useSettingsQuery(1)
const settingsData = computed(() => data.value?.data?.data || {})

const store = useSettingStore()
const { update, updateLoading, backupDB, backupDBLoading } = useSettingMutations(store.moduleName)

// Scheduled backup form data
const scheduledBackupData = ref({
  id: null,
  email: '',
})

const scheduleLoading = computed(() => updateLoading?.value || false)

// Watch for settings data and populate backup email
watch(
  settingsData,
  (newData) => {
    if (newData && newData.backup_email) {
      scheduledBackupData.value = {
        id: newData.id,
        email: newData.backup_email,
      }
    }
  },
  { immediate: true },
)

// Load scheduled backup settings on mount
onMounted(async () => {
  await loadScheduledBackup()
})

// Load existing scheduled backup configuration
const loadScheduledBackup = async () => {
  try {
    // TODO: Implement API call to load scheduled backup settings

    console.log('Loading scheduled backup settings...')
  } catch (error) {
    console.error('Error loading scheduled backup:', error)
  }
}

// Save/Update scheduled backup
const handleSaveScheduledBackup = async () => {
  try {
    const payload = new FormData()
    payload.append('id', settingsData.value.id)
    payload.append('backup_email', scheduledBackupData.value.email)
    payload.append('software_name', settingsData.value.software_name || '') // Include other required fields if necessary
    payload.append('company_name', settingsData.value.company_name || '')
    payload.append('company_phone', settingsData.value.company_phone || '')

    await update.mutateAsync({
      id: settingsData.value.id,
      payload,
    })

    scheduledBackupData.value.id = settingsData.value.id
  } catch (error) {
    console.error('Error saving scheduled backup:', error)
    alert('Failed to save scheduled backup')
  }
}

// Database Backup - Download Handler
const handleDownloadBackup = async () => {
  try {
    // Call the backup API which returns a blob
    const blob = await backupDB.mutateAsync()

    // Create a download link from the blob
    const url = window.URL.createObjectURL(new Blob([blob]))
    const link = document.createElement('a')
    link.href = url

    // Create filename with timestamp
    const timestamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, -5)
    link.setAttribute('download', `database_backup_${timestamp}.sql`)

    // Trigger download
    document.body.appendChild(link)
    link.click()

    // Cleanup
    link.remove()
    window.URL.revokeObjectURL(url)
  } catch (error) {
    console.error('Download backup error:', error)
    alert('Failed to download database backup')
  }
}
</script>
