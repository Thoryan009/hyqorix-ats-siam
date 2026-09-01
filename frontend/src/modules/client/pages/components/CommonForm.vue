<template>
  <BaseForm :onSubmit="onSubmit" class="h-150 ">
    <!-- Name -->
    <div class="space-y-2">
      <BaseLabel for="name">{{t('shared.labels.name')}}</BaseLabel>
      <BaseInput id="name" v-model="localForm.name" :required="true" placeholder="Eg: Rabit Ltd" />
    </div>

    <!-- Email -->
    <div class="space-y-2">
      <BaseLabel for="email">{{t('shared.labels.email')}}</BaseLabel>
      <BaseInput
        id="email"
        type="email"
        v-model="localForm.email"
        :required="true"
        placeholder="Eg: rabit@example.com"
      />
    </div>

    <!-- Phone -->
    <div class="space-y-2">
      <BaseLabel for="phone">{{t('shared.labels.phone')}}</BaseLabel>
      <BaseInput
        id="phone"
        type="tel"
        v-model="localForm.phone"
        :required="true"
        placeholder="Eg: +1234567890"
      />
    </div>

     <!-- WhatsApp No -->
    <div class="space-y-2">
      <BaseLabel for="whatsapp_no">{{t('shared.labels.whatsapp_no')}}</BaseLabel>
      <BaseInput
        id="whatsapp_no"
        type="tel"
        v-model="localForm.whatsapp_no"
        :required="true"
        placeholder="Eg: +1234567890"
      />
    </div>

     <!-- Status -->
    <div class="space-y-2">
      <BaseLabel for="status">{{t('shared.labels.status')}}</BaseLabel>
      <BaseSelect
        id="status"
        v-model="localForm.status"
        :options="statusOptions"
        placeholder="Select"
        :required="true"
      />
    </div>

     <!-- Role  -->
    <div class="space-y-2">
      <BaseLabel for="role">{{t('shared.labels.role')}}</BaseLabel>
      <BaseSelect
        id="role"
        v-model="localForm.role_id"
        :options="userRoles"
        placeholder="Select"
        :required="true"
      />
    </div>

    <!-- Country -->
    <div class="space-y-2">
      <BaseLabel for="country_name">{{t('shared.labels.country')}}</BaseLabel>
      <BaseSelect
        id="country_name"
        v-model="localForm.country_id"
        :options="countries"
        placeholder="Select"
        :required="true"
      />
    </div>

       <!-- Send Notification -->
            <div class="space-y-2">
              <BaseLabel>{{t('shared.labels.send_notification')}}</BaseLabel>

              <div class="grid grid-cols-2 gap-3">
                <!-- Yes -->
                <label
                  class="flex items-center gap-3 p-4 border rounded-lg cursor-pointer transition"
                  :class="
                    localForm.send_notification == '1'
                      ? 'border-blue-500 bg-blue-50'
                      : 'border-gray-300'
                  "
                >
                  <input
                    type="radio"
                    v-model="localForm.send_notification"
                    value="1"
                    class="w-3 h-3"
                  />
                  <div>
                    <p class="font-medium">Yes</p>

                  </div>
                </label>

                <!-- No -->
                <label
                  class="flex items-center gap-3 p-4 border rounded-lg cursor-pointer transition"
                  :class="
                    localForm.send_notification == '0'
                      ? 'border-blue-500 bg-blue-50'
                      : 'border-gray-300'
                  "
                >
                  <input
                    type="radio"
                    v-model="localForm.send_notification"
                    value="0"
                    class="w-3 h-3"
                  />
                  <div>
                    <p class="font-medium">No</p>

                  </div>
                </label>
              </div>
            </div>

  <!-- client picture Upload Section -->
    <div class="col-span-full">
      <!-- IMAGE/PDF PREVIEW -->
      <div class="mt-2">
        <!-- New uploaded file preview -->
        <div class="overflow-hidden" v-if="localForm?.client_image_preview">
          <BaseImagePreview
            v-if="fileType?.client_image_preview === 'image'"
            :src="localForm?.client_image_preview"
            width="250px"
            height="200px"
            @cancelImage="cancelImage('client_image')"
            cancelKey="client_image"
          />
        </div>

        <!-- Existing file from database -->
        <template v-else-if="store?.item?.client_image_url">
          <div
            v-if="store?.item.client_image_url.split('?')[0].toLowerCase().endsWith('.pdf')"
            class="w-80 h-64 rounded-lg border shadow overflow-hidden relative bg-gray-50"
          >
            <iframe :src="store?.item.client_image_url" class="w-full h-full"></iframe>
          </div>
          <BaseImagePreview
            v-else
            :src="store?.item.client_image_url"
            width="320px"
            height="200px"
            cancelKey="client_image"
            @cancelImage="cancelImage('client_image')"
          />
        </template>
      </div>

      <!-- UPLOAD INPUT -->
      <div class="mt-4">
        <BaseLabel for="client_image_path">{{t('shared.labels.upload_client_image')}}</BaseLabel>
        <BaseFileInput
          accept=".jpg, .jpeg, .png"
          @change="handleFileChange($event, 'client_image_path', 'client_image_preview')"
          :fileName="fileName.client_image_path"
        />
        <div v-if="fileError.client_image_path" class="text-red-600 text-sm mt-1">
          {{ fileError.client_image_path }}
        </div>
      </div>
    </div>











    <!-- Password -->
    <div class="space-y-2">
      <BaseLabel for="password">{{t('shared.labels.password')}}</BaseLabel>

      <div class="relative">
        <BaseInput
          id="password"
          type="text"
          v-model="localForm.password"
          placeholder="Auto-generate password"
        />

        <!-- Generate Icon -->
        <i
          class="fa fa-refresh absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer text-gray-500 hover:text-blue-600"
          title="Generate Password"
          @click="handleGeneratePassword"
        ></i>
      </div>
    </div>

    <CreatePartyAccountCheckbox
      v-if="isCreate"
      v-model="localForm.create_party_account"
      source-module="client"
    />

    <!-- Actions -->
    <div class="flex justify-end gap-2 pt-4 pb-6">
      <BaseButton class="bg-yellow-600 hover:bg-yellow-700" type="button" @click="onCancel"
        >{{ t('shared.actions.cancel') }}</BaseButton
      >
      <BaseButton
        :className="'bg-blue-600 text-white hover:bg-blue-800'"
        type="button"
        @click="handleCopyCredentials"
      >
        {{ t('shared.actions.copy_credentials') }}
      </BaseButton>
      <BaseButton type="submit" :disabled="loading">
        <span v-if="loading">{{ t('shared.actions.saving') }}</span>
        <span v-else>{{ t('shared.actions.save') }}</span>
      </BaseButton>
    </div>
  </BaseForm>
</template>

<script setup>
import { usePasswordGenerator } from '@/shared/composables/usePasswordGenerator'
import { ref, watch } from 'vue'
import { useToast } from 'vue-toastification'
import appConfig from '@/shared/config/appConfig'
import { useFileHandler } from '@/shared/composables/useFileHandler'
import { useTranslate } from '@/shared/composables/useTranslate'
import CreatePartyAccountCheckbox from '@/modules/parties/components/CreatePartyAccountCheckbox.vue'

const { t } = useTranslate('client')

const appUrl = appConfig.appUrl


const statusOptions = [
  { id: '1', name: 'Active' },
  { id: '0', name: 'Inactive' },
]

// Props
const props = defineProps({
  formData: { type: Object, required: true },
  userRoles: { type: Array, default: () => [] },
  countries: { type: Array, default: () => [] },
  onSubmit: { type: Function, required: true },
  onCancel: { type: Function, required: true },
  loading: { type: Boolean, required: true },
  store: { type: Object, required: true },
  isCreate: { type: Boolean, default: false },
})

// Copy to Clipboard Handler
const handleCopyCredentials = async () => {
  const toast = useToast()
  try {
    const textToCopy = `
    Credentials for ${localForm.value.name}:
Login URL: ${appUrl}/login
Email: ${localForm.value.email}
Password: ${localForm.value.password}
    `.trim()
    toast.success('Credentials copied!')

    await navigator.clipboard.writeText(textToCopy)
  } catch (err) {
    console.error('Failed to copy credentials:', err)
    alert('Failed to copy credentials. Please try manually.')
  }
}

// Emit updates
const emit = defineEmits(['update:formData'])

// Local reactive copy
const localForm = ref({ ...props.formData })

  const { handleFileChange, fileName, cancelImage, fileError, fileType } = useFileHandler(
    localForm.value)

// Passowrd Generator
const { generatePassword } = usePasswordGenerator()

const handleGeneratePassword = () => {
  localForm.value.password = generatePassword(10)
}

// Sync back to parent
watch(
  localForm,
  (val) => {
    emit('update:formData', val)
  },
  { deep: true },
)
</script>
