<template>
  <BaseForm :onSubmit="onSubmit" class="h-150 ">
    <!-- Name -->
    <div class="space-y-2">
      <BaseLabel for="name">{{ t('shared.labels.name') }}</BaseLabel>
      <BaseInput id="name" v-model="localForm.name" :required="true" placeholder="Eg: Rabit Ltd" />
    </div>
    <!-- Email -->
    <div class="space-y-2">
      <BaseLabel for="email">{{ t('shared.labels.email') }}</BaseLabel>
      <BaseInput
        id="email"
        type="email"
        v-model="localForm.email"

        placeholder="Eg: rabit@example.com"
      />
    </div>

    <!-- NID No -->
    <div class="space-y-2">
      <BaseLabel for="nid_no">{{ t('shared.labels.nid_no') }}</BaseLabel>
      <BaseInput
        id="nid_no"
        type="text"
        v-model="localForm.nid_no"

        placeholder="Eg: 1234567890"
      />
    </div>
    <!-- Address -->
    <div class="space-y-2">
      <BaseLabel for="address">{{ t('shared.labels.address') }}</BaseLabel>
      <BaseInput
        id="address"
        type="text"
        v-model="localForm.address"

        placeholder="Eg: 123 Main St, City, Country"
      />
    </div>

    <!-- Phone -->
    <div class="space-y-2">
      <BaseLabel for="phone">{{ t('shared.labels.phone') }}</BaseLabel>
      <BaseInput
        id="phone"
        type="tel"
        v-model="localForm.phone"

        placeholder="Eg: +1234567890"
      />
    </div>

     <!-- WhatsApp -->
    <div class="space-y-2">
      <BaseLabel for="whatsapp_no">{{ t('shared.labels.whatsapp') }}</BaseLabel>
      <BaseInput
        id="whatsapp_no"
        type="tel"
        v-model="localForm.whatsapp_no"

        placeholder="Eg: +1234567890"
      />
    </div>
    <!-- Manager Name -->
    <div class="space-y-2">
      <BaseLabel for="manager_name">{{ t('shared.labels.manager_name') }}</BaseLabel>
      <BaseInput
        id="manager_name"
        type="text"
        v-model="localForm.manager_name"

        placeholder="Eg: John Doe"
      />
    </div>

    <!--Alternate Phone -->
    <div class="space-y-2">
      <BaseLabel for="phone2">{{ t('shared.labels.phone2') }}</BaseLabel>
      <BaseInput
        id="phone2"
        type="tel"
        v-model="localForm.phone2"

        placeholder="Eg: +1234567890"
      />
    </div>

    <!--Staff Name -->
    <div class="space-y-2">
      <BaseLabel for="stuff_name">{{ t('shared.labels.staff_name') }}</BaseLabel>
      <BaseInput
        id="stuff_name"
        type="text"
        v-model="localForm.stuff_name"
        placeholder="Eg: John Doe"
      />
    </div>

    <!--Staff Phone -->
    <div class="space-y-2">
      <BaseLabel for="stuff_phone">{{ t('shared.labels.staff_phone') }}</BaseLabel>
      <BaseInput
        id="stuff_phone"
        type="tel"
        v-model="localForm.stuff_phone"

        placeholder="Eg: +1234567890"
      />
    </div>

    <!-- Role -->
    <div class="space-y-2">
      <BaseLabel for="role">{{ t('shared.labels.role') }}</BaseLabel>
      <BaseSelect
        id="role"
        v-model="localForm.role_id"
        :options="roles"
        placeholder="Select"

      />
    </div>

    <!-- Status -->
    <div class="space-y-2">
      <BaseLabel for="status">{{ t('shared.labels.status') }}</BaseLabel>
      <BaseSelect
        id="status"
        v-model="localForm.status"
        :options="statusOptions"
        placeholder="Select"

      />
    </div>


<!-- jekhane store.formData oita hobe localForm -->
 <!-- jeta store.fileName, cancelImage, fileError, fileType oita asbe fileHelper theke -->
  <!-- jeta store.item oitar agent er original store call kora lagbe -->
   <!-- note: useFileHandler ami ekhane call krte parbo na karon formData.value parameter pass korte hobe r oita ache addModal form e-->




    <!-- agent picture Upload Section -->
    <div class="col-span-full">
      <!-- IMAGE/PDF PREVIEW -->
      <div class="mt-2">
        <!-- New uploaded file preview -->
        <div class="overflow-hidden" v-if="localForm?.agent_image_preview">
          <BaseImagePreview
            v-if="fileType?.agent_image_preview === 'image'"
            :src="localForm?.agent_image_preview"
            width="250px"
            height="200px"
            @cancelImage="cancelImage('agent_image')"
            cancelKey="agent_image"
          />
        </div>

        <!-- Existing file from database -->
        <template v-else-if="store?.item?.agent_image_url">
          <div
            v-if="store?.item.agent_image_url.split('?')[0].toLowerCase().endsWith('.pdf')"
            class="w-80 h-64 rounded-lg border shadow overflow-hidden relative bg-gray-50"
          >
            <iframe :src="store?.item.agent_image_url" class="w-full h-full"></iframe>
          </div>
          <BaseImagePreview
            v-else
            :src="store?.item.agent_image_url"
            width="320px"
            height="200px"
            cancelKey="agent_image"
            @cancelImage="cancelImage('agent_image')"
          />
        </template>
      </div>

      <!-- UPLOAD INPUT -->
      <div class="mt-4">
        <BaseLabel for="agent_image_path">{{ t('agent.upload_image') }}</BaseLabel>
        <BaseFileInput
          accept=".jpg, .jpeg, .png"
          @change="handleFileChange($event, 'agent_image_path', 'agent_image_preview')"
          :fileName="fileName.agent_image_path"
        />
        <div v-if="fileError.agent_image_path" class="text-red-600 text-sm mt-1">
          {{ fileError.agent_image_path }}
        </div>
      </div>
    </div>

    <!-- Password -->
    <div class="space-y-2">
      <BaseLabel for="password">{{ t('shared.labels.password') }}</BaseLabel>

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
      source-module="agent"
    />

    <!-- Actions -->
    <div class="flex justify-end gap-2 pt-4 pb-6" >
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
import { useAgentDataQuery } from '../../queries/useAgentsQuery'
import { ref, watch, computed } from 'vue'
import { useToast } from 'vue-toastification'
import appConfig from '@/shared/config/appConfig'
import { useFileHandler } from '@/shared/composables/useFileHandler'
import { useTranslate } from '@/shared/composables/useTranslate'
import CreatePartyAccountCheckbox from '@/modules/parties/components/CreatePartyAccountCheckbox.vue'

const { t } = useTranslate('agent')
const appUrl = appConfig.appUrl

const { data: agentData } = useAgentDataQuery()
const roles = computed(() => agentData.value?.data?.roles ?? [])

const statusOptions = [
  { id: '1', name: 'Active' },
  { id: '0', name: 'Inactive' },
]

// Props
const props = defineProps({
  formData: { type: Object, required: true },
  countries: { type: Array, default: () => [] },
  onSubmit: { type: Function, required: true },
  onCancel: { type: Function, required: true },
  loading: { type: Boolean, default: false },
  store: { type: Object, required: true }, // For accessing store data and methods
  localFormstore: { type: Object }, // For accessing store data like item
  isCreate: { type: Boolean, default: false },
})

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

// Sync back to parent
watch(
  localForm,
  (val) => {
    emit('update:formData', val)
  },
  { deep: true },
)
</script>
