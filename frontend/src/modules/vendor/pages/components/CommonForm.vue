<template>
  <BaseForm :onSubmit="onSubmit" class="h-150">
    <div class="space-y-2">
      <BaseLabel for="organization_name">{{ t('vendor.organization_name') }}</BaseLabel>
      <BaseInput
        id="organization_name"
        v-model="localForm.organization_name"
        :required="true"
        :placeholder="t('vendor.placeholder_organization')"
      />
    </div>

    <div class="space-y-2">
      <BaseLabel for="vendor_type">{{ t('vendor.vendor_type') }}</BaseLabel>
      <BaseSelect
        id="vendor_type"
        v-model="localForm.vendor_type"
        :options="vendorTypeOptions"
        :placeholder="t('vendor.placeholder_vendor_type')"
        :required="true"
      />
    </div>

    <div class="space-y-2">
      <BaseLabel for="contact_person">{{ t('vendor.contact_person') }}</BaseLabel>
      <BaseInput
        id="contact_person"
        v-model="localForm.contact_person"
        :placeholder="t('vendor.placeholder_contact')"
      />
    </div>

    <div class="space-y-2">
      <BaseLabel for="address">{{ t('shared.labels.address') }}</BaseLabel>
      <BaseInput
        id="address"
        v-model="localForm.address"
        :placeholder="t('vendor.placeholder_address')"
      />
    </div>

    <div class="space-y-2">
      <BaseLabel for="email">{{ t('shared.labels.email') }}</BaseLabel>
      <BaseInput
        id="email"
        type="email"
        v-model="localForm.email"
        :required="true"
        placeholder="Eg: vendor@example.com"
      />
    </div>

    <div class="space-y-2">
      <BaseLabel for="phone">{{ t('shared.labels.phone') }}</BaseLabel>
      <BaseInput
        id="phone"
        type="tel"
        v-model="localForm.phone"
        :required="true"
        placeholder="Eg: +1234567890"
      />
    </div>

    <div class="space-y-2">
      <BaseLabel for="whatsapp_no">{{ t('shared.labels.whatsapp_no') }}</BaseLabel>
      <BaseInput
        id="whatsapp_no"
        type="tel"
        v-model="localForm.whatsapp_no"
        :required="true"
        placeholder="Eg: +1234567890"
      />
    </div>

    <div class="space-y-2">
      <BaseLabel for="status">{{ t('shared.labels.status') }}</BaseLabel>
      <BaseSelect
        id="status"
        v-model="localForm.status"
        :options="statusOptions"
        placeholder="Select"
        :required="true"
      />
    </div>

    <div class="space-y-2">
      <BaseLabel for="role">{{ t('shared.labels.role') }}</BaseLabel>
      <BaseSelect
        id="role"
        v-model="localForm.role_id"
        :options="userRoles"
        placeholder="Select"
        :required="true"
      />
    </div>

    <div class="space-y-2">
      <BaseLabel>{{ t('shared.labels.send_notification') }}</BaseLabel>
      <div class="grid grid-cols-2 gap-3">
        <label
          class="flex items-center gap-3 p-4 border rounded-lg cursor-pointer transition"
          :class="localForm.send_notification == '1' ? 'border-blue-500 bg-blue-50' : 'border-gray-300'"
        >
          <input type="radio" v-model="localForm.send_notification" value="1" class="w-3 h-3" />
          <p class="font-medium">Yes</p>
        </label>
        <label
          class="flex items-center gap-3 p-4 border rounded-lg cursor-pointer transition"
          :class="localForm.send_notification == '0' ? 'border-blue-500 bg-blue-50' : 'border-gray-300'"
        >
          <input type="radio" v-model="localForm.send_notification" value="0" class="w-3 h-3" />
          <p class="font-medium">No</p>
        </label>
      </div>
    </div>

    <div class="col-span-full">
      <div class="mt-2">
        <div class="overflow-hidden" v-if="localForm?.vendor_image_preview">
          <BaseImagePreview
            v-if="fileType?.vendor_image_preview === 'image'"
            :src="localForm?.vendor_image_preview"
            width="250px"
            height="200px"
            @cancelImage="cancelImage('vendor_image')"
            cancelKey="vendor_image"
          />
        </div>
        <template v-else-if="store?.item?.vendor_image_url">
          <BaseImagePreview
            :src="store?.item.vendor_image_url"
            width="320px"
            height="200px"
            cancelKey="vendor_image"
            @cancelImage="cancelImage('vendor_image')"
          />
        </template>
      </div>

      <div class="mt-4">
        <BaseLabel for="vendor_image_path">{{ t('vendor.upload_image') }}</BaseLabel>
        <BaseFileInput
          accept=".jpg, .jpeg, .png"
          @change="handleFileChange($event, 'vendor_image_path', 'vendor_image_preview')"
          :fileName="fileName.vendor_image_path"
        />
        <div v-if="fileError.vendor_image_path" class="text-red-600 text-sm mt-1">
          {{ fileError.vendor_image_path }}
        </div>
      </div>
    </div>

    <div class="space-y-2">
      <BaseLabel for="password">{{ t('shared.labels.password') }}</BaseLabel>
      <div class="relative">
        <BaseInput
          id="password"
          type="text"
          v-model="localForm.password"
          placeholder="Auto-generate password"
        />
        <i
          class="fa fa-refresh absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer text-gray-500 hover:text-blue-600"
          title="Generate Password"
          @click="handleGeneratePassword"
        ></i>
      </div>
    </div>

    <div class="flex justify-end gap-2 pt-4 pb-6">
      <BaseButton class="bg-yellow-600 hover:bg-yellow-700" type="button" @click="onCancel">
        {{ t('shared.actions.cancel') }}
      </BaseButton>
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

const { t } = useTranslate('vendor')
const appUrl = appConfig.appUrl

const statusOptions = [
  { id: '1', name: 'Active' },
  { id: '0', name: 'Inactive' },
]

const vendorTypeOptions = [
  { id: 'ticket', name: 'Ticket' },
  { id: 'legal', name: 'Legal' },
]

const props = defineProps({
  formData: { type: Object, required: true },
  userRoles: { type: Array, default: () => [] },
  onSubmit: { type: Function, required: true },
  onCancel: { type: Function, required: true },
  loading: { type: Boolean, required: true },
  store: { type: Object, required: true },
})

const handleCopyCredentials = async () => {
  const toast = useToast()
  try {
    const textToCopy = `
Credentials for ${localForm.value.organization_name}:
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

const emit = defineEmits(['update:formData'])
const localForm = ref({ ...props.formData })

const { handleFileChange, fileName, cancelImage, fileError, fileType } = useFileHandler(localForm.value)
const { generatePassword } = usePasswordGenerator()

const handleGeneratePassword = () => {
  localForm.value.password = generatePassword(10)
}

watch(
  localForm,
  (val) => {
    emit('update:formData', val)
  },
  { deep: true }
)
</script>
