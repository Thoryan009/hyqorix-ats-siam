<template>
  <BaseModal
    :isVisible="store.isModal || store.isEditModal"
    :title="store.title"
    @close="store.handleToggleModal"
    :className="'w-full xl:max-w-[50vw]'"

  >
    <ScrollableLayout height="680px">
      <form @submit.prevent="handleSubmit" class="space-y-5 p-1 h-150">
        <!-- ── Group 1: Personal Info ── -->
        <div class="rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
          <div
            class="px-4 py-2.5 flex items-center gap-2"
            style="background: linear-gradient(90deg, #3b82f6, #6366f1)"
          >
            <i class="fa fa-user text-white text-sm"></i>
            <span class="text-white text-sm font-semibold tracking-wide uppercase">Personal Info</span>
          </div>
          <div class="p-4 bg-white grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Name -->
            <div class="space-y-1.5">
              <label
                for="name"
                class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
              >
                <i class="fa fa-id-card text-blue-400"></i> Name
                <span class="text-red-400">*</span>
              </label>
              <BaseInput
                id="name"
                v-model="formData.name"
                placeholder="Eg: Abdul Alim"
                :required="true"
              />
            </div>
            <!-- Username -->
            <div class="space-y-1.5">
              <label
                for="username"
                class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
              >
                <i class="fa fa-user text-blue-400"></i> Username
                <span class="text-red-400">*</span>
              </label>
              <BaseInput
                id="username"
                v-model="formData.username"
                placeholder="Eg: johndoe"
                :required="true"
              />
            </div>
            <!-- Email -->
            <div class="space-y-1.5">
              <label
                for="email"
                class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
              >
                <i class="fa fa-envelope text-blue-400"></i> Email
                <span class="text-red-400">*</span>
              </label>
              <BaseInput
                id="email"
                type="email"
                v-model="formData.email"
                placeholder="Eg: rabit@example.com"
                :required="true"
              />
            </div>
            <!-- Phone -->
            <div class="space-y-1.5">
              <label
                for="phone"
                class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
              >
                <i class="fa fa-phone text-blue-400"></i> Phone
                <span class="text-red-400">*</span>
              </label>
              <BaseInput
                id="phone"
                type="tel"
                v-model="formData.phone"
                placeholder="Eg: +1234567890"
                :required="true"
              />
            </div>

            <!-- WhatsApp Number -->
            <div class="space-y-1.5">
              <label
                for="whatsapp_no"
                class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
              >
                <i class="fa fa-phone text-blue-400"></i> WhatsApp No
                <span class="text-red-400">*</span>
              </label>
              <BaseInput
                id="whatsapp_no"
                type="tel"
                v-model="formData.whatsapp_no"
                placeholder="Eg: +1234567890"
                :required="false"
              />
            </div>
            <!-- Designation -->
            <div class="space-y-1.5">
              <label
                for="designation"
                class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
              >
                <i class="fa fa-briefcase text-blue-400"></i> Designation
                <span class="text-red-400">*</span>
              </label>
              <BaseSelect
                id="designation"
                v-model="formData.designation_id"
                :options="extraData.designations"
                placeholder="Select"
                :required="true"
              />
            </div>

            <!-- Department -->
             <div class="space-y-1.5">
              <label
                class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
              >
                <i class="fa fa-key text-violet-400"></i> Department
              </label>
              <div class="flex flex-wrap gap-3 pt-1">
                <label
                  v-for="department in extraData.departments"
                  :key="department.id"
                  class="flex items-center gap-2 cursor-pointer"
                >
                  <input
                    type="checkbox"
                    :value="department.id"
                    v-model="formData.department_ids"
                    class="rounded border-gray-300 text-violet-500"
                  />
                  <span class="text-sm text-gray-700">{{ department . name }}</span>
                </label>
              </div>
            </div>
            <!-- <pre>{{ extraData }}</pre> -->

            <!-- document Start -->
            <div class="space-y-1.5">
              <div class="overflow-hidden" v-if="formData?.image_preview">
                <BaseImagePreview
                  v-if="fileType?.image_preview === 'image'"
                  :src="formData?.image_preview"
                  width="250px"
                  height="200px"
                  @cancelImage="cancelImage('document')"
                  cancelKey="document"
                />

                <div
                  v-else
                  class="w-80 h-64 rounded-lg border shadow overflow-hidden bg-gray-50 relative"
                >
                  <iframe :src="formData?.image_preview" class="w-full h-full"></iframe>
                  <button
                    class="absolute top-2 right-2 w-6 h-6 bg-slate-900 rounded-full p-1 hover:bg-slate-700 flex justify-center items-center text-slate-50"
                    @click="cancelImage('document')"
                    type="button"
                  >
                    <i class="fa fa-times"></i>
                  </button>
                </div>
              </div>

              <template v-else-if="formData.image_url">
                <BaseImagePreview
                  :src="formData.image_url"
                  width="320px"
                  height="200px"
                  cancelKey="document"
                  @cancelImage="cancelImage('document')"
                />
              </template>

              <div>
                <BaseLabel for="image_path">Upload document (Optional)</BaseLabel>
                <BaseFileInput
                  accept=".jpg, .jpeg, .png"
                  @change="handleFileChange($event, 'image_path', 'image_preview')"
                  :fileName="fileName.image_path"
                />
                {{ console.log('ase naki nai', formData?.image_path) }}

                <div
                  v-if="fileError.image_path"
                  class="text-red-600 text-sm mt-1"
                >{{ fileError . image_path }}</div>
              </div>
            </div>
            <!-- document End -->
          </div>
        </div>

        <!-- ── Group 2: Access & Status ── -->
        <div class="rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
          <div
            class="px-4 py-2.5 flex items-center gap-2"
            style="background: linear-gradient(90deg, #8b5cf6, #a855f7)"
          >
            <i class="fa fa-shield text-white text-sm"></i>
            <span class="text-white text-sm font-semibold tracking-wide uppercase">Access & Status</span>
          </div>
          <div class="p-4 bg-white grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Assign Roles -->
            <div class="space-y-1.5">
              <label
                class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
              >
                <i class="fa fa-key text-violet-400"></i> Assign Roles
              </label>
              <div class="flex flex-wrap gap-3 pt-1">
                <label
                  v-for="role in extraData.roles"
                  :key="role.id"
                  class="flex items-center gap-2 cursor-pointer"
                >
                  <input
                    type="checkbox"
                    :value="role.id"
                    v-model="formData.role_ids"
                    class="rounded border-gray-300 text-violet-500"
                  />
                  <span class="text-sm text-gray-700">{{ role . name }}</span>
                </label>
              </div>
            </div>
            <!-- Status -->
            <div class="space-y-1.5">
              <label
                for="status"
                class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
              >
                <i class="fa fa-toggle-on text-violet-400"></i> Status
                <span class="text-red-400">*</span>
              </label>

              <BaseSelect
                id="status"
                v-model="formData.status"
                :options="extraData.statuses"
                placeholder="Select"
                :required="true"
              />
            </div>

            <!-- Show ATS Summary -->
            <div class="space-y-1.5">
              <label
                class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
              >
                <i class="fa fa-paper-plane text-emerald-400"></i> Show ATS Summary
              </label>
              <div class="flex items-center gap-6 pt-1">
                <label class="flex items-center gap-2 cursor-pointer">
                  <BaseInput
                    type="radio"
                    name="show_ats_summary"
                    :value="1"
                    v-model="formData.show_ats_summary"
                    :required="true"
                  />
                  <span class="text-sm font-medium text-gray-700">Yes</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                  <BaseInput
                    type="radio"
                    name="show_ats_summary"
                    :value="0"
                    v-model="formData.show_ats_summary"
                  />
                  <span class="text-sm font-medium text-gray-700">No</span>
                </label>
              </div>
            </div>

            <!-- Manager Approval -->
            <div class="space-y-1.5">
              <label
                class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
              >
                <i class="fa fa-check-circle text-violet-400"></i> Manager Approval
              </label>
              <div class="flex items-center gap-6 pt-1">
                <label class="flex items-center gap-2 cursor-pointer">
                  <BaseInput
                    type="radio"
                    name="manager_approval"
                    :value="1"
                    v-model="formData.manager_approval"
                    :required="true"
                  />
                  <span class="text-sm font-medium text-gray-700">Yes</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                  <BaseInput
                    type="radio"
                    name="manager_approval"
                    :value="0"
                    v-model="formData.manager_approval"
                  />
                  <span class="text-sm font-medium text-gray-700">No</span>
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- ── Group 3: Credentials ── -->
        <div class="rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
          <div
            class="px-4 py-2.5 flex items-center gap-2"
            style="background: linear-gradient(90deg, #10b981, #059669)"
          >
            <i class="fa fa-lock text-white text-sm"></i>
            <span class="text-white text-sm font-semibold tracking-wide uppercase">Credentials</span>
          </div>
          <div class="p-4 bg-white grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Password -->
            <div class="space-y-1.5">
              <label
                for="password"
                class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
              >
                <i class="fa fa-key text-emerald-400"></i> Password
                <span v-if="store.isModal" class="text-red-400">*</span>
              </label>
              <div class="relative">
                <BaseInput
                  id="password"
                  type="text"
                  v-model="formData.password"
                  placeholder="Auto-generate password"
                  :required="store.isModal ? true : false"
                />
                <i
                  class="fa fa-refresh absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer text-gray-500 hover:text-emerald-600"
                  title="Generate Password"
                  @click="handleGeneratePassword"
                ></i>
              </div>
            </div>
            <!-- Sent Credentials -->
            <div class="space-y-1.5">
              <label
                class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
              >
                <i class="fa fa-paper-plane text-emerald-400"></i> Sent Credentials
              </label>
              <div class="flex items-center gap-6 pt-1">
                <label class="flex items-center gap-2 cursor-pointer">
                  <BaseInput
                    type="radio"
                    name="send_credentials"
                    :value="1"
                    v-model="formData.send_credentials"
                    :required="true"
                  />
                  <span class="text-sm font-medium text-gray-700">Yes</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                  <BaseInput
                    type="radio"
                    name="send_credentials"
                    :value="0"
                    v-model="formData.send_credentials"
                  />
                  <span class="text-sm font-medium text-gray-700">No</span>
                </label>
              </div>
            </div>
            <CreatePartyAccountCheckbox
              v-if="store.isModal && !store.isEditModal"
              v-model="formData.create_party_account"
              source-module="employee"
            />
          </div>
        </div>

        <!-- ── Actions ── -->
        <div class="flex justify-end gap-3 pt-2 pb-4">
          <BaseButton
            :className="'bg-yellow-700 hover:bg-yellow-800 text-white border border-gray-200 gap-2 cursor-pointer'"
            @click="store.handleToggleModal"
          >
            <i class="fa fa-times"></i> Cancel
          </BaseButton>
          <BaseButton
            class="gap-2"
            type="button"
            :className="'bg-green-600 hover:bg-green-700 text-white border border-gray-200 cursor-pointer'"
            @click="handleCopyCredentials"
          >
            <i class="fa fa-copy"></i> Copy Credentials
          </BaseButton>
          <BaseButton type="submit" :disabled="submitLoading || updateLoading" class="gap-2">
            <span v-if="submitLoading || updateLoading" class="flex items-center gap-2">
              <i class="fa fa-spinner fa-spin"></i>
              {{ submitSavingText }}
            </span>
            <span v-else class="flex items-center gap-2">
              <i class="fa fa-check"></i>
              {{ submitText }}
            </span>
          </BaseButton>
        </div>
      </form>
    </ScrollableLayout>
  </BaseModal>
</template>

<script setup>
import { useCrudForm } from '@/shared/composables/useCrudForm'
import { useCrudSubmit } from '@/shared/composables/useCrudSubmit'
import { useCrudMutations } from '@/shared/composables/useCrudMutations'
import { generatePassword } from '@/shared/utils/password'
import { useToast } from 'vue-toastification'
import appConfig from '@/shared/config/appConfig'
import { useFileHandler } from '@/shared/composables/useFileHandler2'
import CreatePartyAccountCheckbox from '@/modules/parties/components/CreatePartyAccountCheckbox.vue'

const appUrl = appConfig.appUrl

const props = defineProps({
  extraData: {
    type: Object,
    default: () => ({}),
  },
  store: {
    type: Object,
    required: true,
  },
})

// Copy to Clipboard Handler
const handleCopyCredentials = async () => {
  const toast = useToast()
  try {
    const textToCopy = `
Credentials for ${formData.value.name}:
Login URL: ${appUrl}/login
Email: ${formData.value.email}
Password: ${formData.value.password}
    `.trim()

    await navigator.clipboard.writeText(textToCopy)
    toast.success('Credentials copied!')
  } catch (err) {
    console.error('Failed to copy credentials:', err)
    alert('Failed to copy credentials. Please try manually.')
  }
}

const defaultFormData = {
  name: 'John Doe',
  email: 'john@example.com',
  phone: '01784639947',
  whatsapp_no: '01784639947',
  role_ids: [],
  department_ids: [],
  designation_id: 1,
  username: 'johndoe',
  status: 1,
  show_ats_summary: 1,
  manager_approval: 0,
  password: generatePassword(10),
  send_credentials: '0',
  create_party_account: 1,
  image_preview: null,
  image_path: null,
  image_url: null,
}

const buildPayload = () => {
  const payload = new FormData()

  const appendFormData = (data) => {
    for (const key in data) {
      const value = data[key]

      if (value === null || value === undefined || value === '') continue

      // ❗ preview/url skip
      if (key.endsWith('_preview')) continue
      if (key.endsWith('_url')) continue

      // ✅ ARRAY HANDLE
      if (Array.isArray(value)) {
        value.forEach((item, index) => {
          payload.append(`${key}[${index}]`, item)
        })
      }

      // ✅ FILE HANDLE
      else if (value instanceof File) {
        payload.append(key, value)
      }

      // ✅ NORMAL FIELD
      else {
        payload.append(key, value)
      }
    }
  }

  appendFormData(formData.value)

  return payload
}

// API
const api = useCrudMutations(props.store.moduleName, {
  onSuccess() {
    props.store.handleToggleModal()
    resetForm()
  },
})

const { formData, resetForm } = useCrudForm(props.store, defaultFormData, [
  'role_ids',
  'department_ids',
  'status',
  'send_credentials',
  'create_party_account',
  'show_ats_summary',
  'manager_approval',
])


const { handleFileChange, fileName, cancelImage, fileError, fileType } = useFileHandler(formData)

const handleGeneratePassword = () => {
  formData.value.password = generatePassword(10)
}

// SUBMIT
const { handleSubmit, submitLoading, updateLoading, submitText, submitSavingText } = useCrudSubmit(
  props.store,
  api,
  buildPayload,
  resetForm
)
</script>
