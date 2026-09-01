<template>
  <BaseForm :onSubmit="onSubmit">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- Organization Name -->
      <div class="space-y-2">
        <BaseLabel for="organization_name">{{ t('principal.organization_name') }}</BaseLabel>
        <BaseInput
          id="organization_name"
          v-model="localForm.organization_name"
          :required="true"
          placeholder="Eg: Rabbit Ltd"
        />
      </div>

      <!-- Email -->
      <div class="space-y-2">
        <BaseLabel for="email">{{t('shared.labels.email')}}</BaseLabel>
        <BaseInput
          id="email"
          type="email"
          v-model="localForm.email"
          :required="true"
          placeholder="Eg: company@example.com"
        />
      </div>

      <!-- Contact No -->
      <div class="space-y-2">
        <BaseLabel for="contact_no">{{t('shared.labels.phone')}}</BaseLabel>
        <BaseInput
          id="contact_no"
          v-model="localForm.contact_no"
          placeholder="Eg: +8801XXXXXXXXX"
        />
      </div>

      <!-- Whatsapp No -->
      <div class="space-y-2">
        <BaseLabel for="whatsapp_no">{{t('shared.labels.whatsapp')}}</BaseLabel>
        <BaseInput
          id="whatsapp_no"
          v-model="localForm.whatsapp_no"
          placeholder="Eg: +8801XXXXXXXXX"
        />
      </div>


      <!-- Address -->
      <div class="space-y-2">
        <BaseLabel for="address">{{ t('shared.labels.address') }}</BaseLabel>
        <BaseInput id="address" v-model="localForm.address" placeholder="Eg: Dhaka, Bangladesh" />
      </div>

      <!-- Country -->
      <div class="space-y-2">
        <BaseLabel for="country_id">{{ t('shared.labels.country') }}</BaseLabel>
        <BaseSelect
          id="country_id"
          v-model="localForm.country_id"
          :options="countries"
          placeholder="Select"
          :required="true"
        />
      </div>

      <!-- Contact Person Name -->
      <div class="space-y-2">
        <BaseLabel for="contact_person_name">{{ t('principal.contact_person_name') }}</BaseLabel>
        <BaseInput
          id="contact_person_name"
          v-model="localForm.contact_person_name"
          :required="true"
          placeholder="Eg: John Doe"
        />
      </div>

      <!-- Designation -->
      <div class="space-y-2">
        <BaseLabel for="designation">{{ t('principal.designation') }}</BaseLabel>
        <BaseInput
          id="designation"
          v-model="localForm.designation"
          :required="true"
          placeholder="Eg: Manager"
        />
      </div>

      <!-- Contact Person No -->
      <div class="space-y-2">
        <BaseLabel for="contact_person_no">{{ t('principal.contact_person_number') }}</BaseLabel>
        <BaseInput
          id="contact_person_no"
          v-model="localForm.contact_person_no"
          placeholder="Eg: +8801XXXXXXXXX"
        />
      </div>

      <!-- Role -->
      <div class="space-y-2">
        <BaseLabel for="role">{{t('shared.labels.role')}}</BaseLabel>
        <BaseSelect
          id="role"
          v-model="localForm.role_id"
          :options="roles"
          placeholder="Select"
          :required="true"
        />
      </div>

            <!-- Send Notification -->
            <div class="space-y-2">
              <BaseLabel>{{ t('shared.labels.send_notification') }}</BaseLabel>

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

      <!-- Status -->
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
        source-module="principal"
        class="md:col-span-2"
      />

      <!-- Actions -->
      <div class="md:col-span-2 flex justify-end gap-2 pt-4">
        <BaseButton class="bg-yellow-600 hover:bg-yellow-700" type="button" @click="onCancel">
          {{t('shared.actions.cancel')}}
        </BaseButton>

        <BaseButton
          :className="'bg-blue-600 text-white hover:bg-blue-800'"
          type="button"
          @click="handleCopyCredentials"
        >
          {{t('shared.actions.copy_credentials')}}
        </BaseButton>

        <BaseButton type="submit" :disabled="loading">
          <span v-if="loading">{{ t('shared.messages.saving') }}</span>
          <span v-else>{{ t('shared.actions.save') }}</span>
        </BaseButton>
      </div>
    </div>
  </BaseForm>
</template>

<script setup>
import { useAgentDataQuery } from '@/modules/agent/queries/useAgentsQuery'
import { usePasswordGenerator } from '@/shared/composables/usePasswordGenerator'
import { ref, watch, computed } from 'vue'
import { useToast } from 'vue-toastification'
import appConfig from '@/shared/config/appConfig'
import {useTranslate} from '@/shared/composables/useTranslate'
import CreatePartyAccountCheckbox from '@/modules/parties/components/CreatePartyAccountCheckbox.vue'

const {t} = useTranslate()
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
  isCreate: { type: Boolean, default: false },
})



// Emit
const emit = defineEmits(['update:formData'])

// Local state
const localForm = ref({ ...props.formData })

const { generatePassword } = usePasswordGenerator()

const handleGeneratePassword = () => {
  localForm.value.password = generatePassword(10)
}

// Copy to Clipboard Handler
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

// Sync back to parent
watch(
  localForm,
  (val) => {
    emit('update:formData', val)
  },
  { deep: true },
)
</script>
