<template>
  <form @submit.prevent="handlePasswordChange" class="space-y-4">
    <div>
      <BaseLabel>{{ t('setting.password.current_password') }}</BaseLabel>
      <div class="relative">
        <BaseInput
          v-model="passwordData.current_password"
          :type="showCurrentPassword ? 'text' : 'password'"
          :placeholder="t('setting.password.enter_current_password')"
          required
        />
        <button
          type="button"
          @click="showCurrentPassword = !showCurrentPassword"
          class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
        >
          <svg
            v-if="!showCurrentPassword"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            class="w-5 h-5"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"
            />
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
            />
          </svg>
          <svg
            v-else
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            class="w-5 h-5"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"
            />
          </svg>
        </button>
      </div>
    </div>

    <div>
      <BaseLabel>{{ t('setting.password.new_password') }}</BaseLabel>
      <div class="relative">
        <BaseInput
          v-model="passwordData.new_password"
          :type="showNewPassword ? 'text' : 'password'"
          :placeholder="t('setting.password.enter_new_password')"
          minlength="8"
          required
        />
        <button
          type="button"
          @click="showNewPassword = !showNewPassword"
          class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
        >
          <svg
            v-if="!showNewPassword"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            class="w-5 h-5"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"
            />
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
            />
          </svg>
          <svg
            v-else
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            class="w-5 h-5"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"
            />
          </svg>
        </button>
      </div>
      <p class="text-xs text-gray-500 mt-1">{{ t('setting.password.password_requirement') }}</p>
    </div>

    <div>
      <BaseLabel>{{ t('setting.password.confirm_new_password') }}</BaseLabel>
      <div class="relative">
        <BaseInput
          v-model="passwordData.confirm_password"
          :type="showConfirmPassword ? 'text' : 'password'"
          :placeholder="t('setting.password.enter_confirm_new_password')"
          minlength="8"
          required
        />
        <button
          type="button"
          @click="showConfirmPassword = !showConfirmPassword"
          class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
        >
          <svg
            v-if="!showConfirmPassword"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            class="w-5 h-5"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"
            />
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
            />
          </svg>
          <svg
            v-else
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            class="w-5 h-5"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"
            />
          </svg>
        </button>
      </div>
    </div>

    <div class="pt-4">
      <BaseButton type="submit" :disabled="changePasswordLoading">
        <span v-if="changePasswordLoading">{{ t('setting.password.changing_password') }}</span>
        <span v-else>{{ t('setting.password.change_password') }}</span>
      </BaseButton>
    </div>
  </form>
</template>

<script setup>
import { ref } from 'vue'
import { useSettingMutations } from '../../queries/useSettingMutations'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate()
// Password change form data
const passwordData = ref({
  current_password: '',
  new_password: '',
  confirm_password: '',
})

// Show/hide password toggles
const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)
const { changePassword, changePasswordLoading } = useSettingMutations()

// Password Change Handler
const handlePasswordChange = async () => {
  // Validate password length
  if (passwordData.value.new_password.length < 8) {
    alert(t('setting.password.password_min_length_error'))
    return
  }

  // Validate password match
  if (passwordData.value.new_password !== passwordData.value.confirm_password) {
    alert(t('setting.password.password_mismatch_error'))
    return
  }

  try {
    await changePassword.mutateAsync(passwordData.value)
    // Reset form
    passwordData.value = {
      current_password: '',
      new_password: '',
      confirm_password: '',
    }

    // Optionally, you can also toggle off the password visibility
    showCurrentPassword.value = false
    showNewPassword.value = false
    showConfirmPassword.value = false
  } catch (error) {
    console.error('Password change error:', error)
    alert(t('setting.password.password_change_failed'))
  }
}
</script>
