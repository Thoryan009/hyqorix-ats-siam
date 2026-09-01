<template>
    <BaseModal :isVisible="store.isModal" :title="t('agent.add')" @close="store.handleToggleModal"
        :className="'xl:max-w-[50vw]'">
        <!-- Use CommonForm here -->
        <CommonForm v-model:formData="formData" :onSubmit="handleSubmit" :onCancel="store.handleToggleModal"
            :loading="store.isLoading" :store="store" :is-create="true" />
    </BaseModal>
</template>

<script setup>
    import {
        ref
    } from 'vue'
    import {
        useAgentStore
    } from '@/modules/agent/store/agentStore'
    import {
        useAgentMutations
    } from '@/modules/agent/queries/useAgentMutations'
    import app from '@/shared/config/appConfig'
    import CommonForm from './CommonForm.vue'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate('agent')
    // Store
    const store = useAgentStore()

    // Default Form Data
    const defaultFormData = {
        name: 'Test Name',
        email: 'test@example.com',
        manager_name: 'Test Manager',
        agent_image_path: null,
        agent_image_preview: null,
        phone: '+1234567890',
        whatsapp_no: '+0987654321',
        phone2: '+0987654321',
        stuff_name: 'Test Stuff',
        stuff_phone: '+1122334455',
        role_id: 1,
        nid_no: 1001123,
        address: '123 Test Street, Test City',
        password: 'Test@1234',
        status: 1,
        create_party_account: 1,
    }

    // Initialize formData
    const formData = ref(
        app.moduleLocal ?
        {
            ...defaultFormData
        } :
        Object.fromEntries(
            Object.keys(defaultFormData)
            .filter((key) => key !== 'status') // remove status
            .map((key) => [key, ' ']), // set empty space
        ),
    )

    // Mutation
    const {
        submit
    } = useAgentMutations(store.moduleName, {
        onSuccess() {
            store.handleToggleModal()
            store.handleReset(formData.value)
        },
        onError: (error) => {
            console.log('Custom error handling', error)
            store.isLoading = false
        },
    })

    // Submit handler
    // const handleSubmit = async () => {
    //     store.isLoading = true
    //     await submit.mutateAsync(formData.value)
    //     store.isLoading = false
    // }
    // submit handler


const handleSubmit = async () => {
  const payload = new FormData()

  for (const key in formData.value) {
    const value = formData.value[key]

    // Skip null, undefined, empty values
    if (value === null || value === undefined || value === '') {
      continue
    }

    // Skip preview fields (only append actual file)
    if (key.includes('_preview')) {
      continue
    }

    payload.append(key, value)
  }
  await submit.mutateAsync(payload)
}
</script>
