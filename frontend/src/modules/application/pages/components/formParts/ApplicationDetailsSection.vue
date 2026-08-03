<template>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center gap-3 pb-3 mb-2 border-b-2 border-indigo-500">
            <span
                class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-500 text-white text-sm font-bold shrink-0">7</span>
            <i class="fa fa-briefcase text-indigo-500 text-xl"></i>
            <span class="font-semibold text-lg text-gray-800">{{ t('application.application_details') }}</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 py-4">
            <div>
                <BaseLabel for="job_list_id">{{ t('application.job_list_required') }}</BaseLabel>
                <BaseSearchSelect id="job_list_id" v-model="store.formData.job_list_id" :options="jobs"
                    :placeholder="t('application.search_by_job_name_or_job_code')" optionLabel="name" optionValue="id"
                    :filter-fn="filterJobList" required />
            </div>

            <div class="md:col-span-2 space-y-3">
                <BaseLabel>{{ t('application.applied_through_required') }}</BaseLabel>
                <div class="flex flex-wrap gap-4">
                    <label v-for="option in appliedThroughOptions" :key="option.id"
                        class="inline-flex cursor-pointer items-center gap-2 rounded-lg border px-4 py-2.5 text-sm transition"
                        :class="store.formData.applied_through === option.id ?
                            'border-indigo-500 bg-indigo-50 text-indigo-800 ring-1 ring-indigo-500' :
                            'border-gray-200 bg-white text-gray-700 hover:border-gray-300'">
                        <input v-model="store.formData.applied_through" type="radio"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500" :value="option.id" required />
                        {{ option.label }}
                    </label>
                </div>
            </div>

            <div v-if="store.formData.applied_through === 'agent'">
                <BaseLabel for="agent_id">{{ t('application.agent_required') }}</BaseLabel>
                <BaseSearchSelect id="agent_id" v-model="store.formData.agent_id" :options="agents"
                    :placeholder="t('application.search_by_agent_name')" required optionLabel="name"
                    optionValue="id" :filter-fn="filterAgent" />
            </div>

            <div class="md:col-span-2">
                <PayerCardSelector v-model="store.formData.payment_responsibility"
                    :label="t('application.payment_responsibility_required')" :show-validation="showPaymentValidation"
                    :exclude="paymentResponsibilityExclude" />
            </div>

            <div class="md:col-span-2">
                <BaseLabel for="remarks">{{ t('application.remarks_optional') }}</BaseLabel>
                <BaseInput id="remarks" v-model="store.formData.remarks"
                    :placeholder="t('application.candidate_available_for_interview')" />
            </div>
        </div>
    </div>
</template>

<script setup>
    import {
        computed,
        ref,
        watch
    } from 'vue'
    import {
        useApplicationStore
    } from '@/modules/application/store/applicationStore'
    import PayerCardSelector from '@/modules/job/pages/components/PayerCardSelector.vue'
    import {
        normalizeJobPayers
    } from '@/modules/job/utils/jobPayerUtils'
    import {
        useTranslate
    } from '@/shared/composables/useTranslate'

    const {
        t
    } = useTranslate()
    const store = useApplicationStore()
    const showPaymentValidation = ref(false)

    defineProps({
        jobs: {
            type: Array,
            default: () => []
        },
        agents: {
            type: Array,
            default: () => []
        },
        isEditMode: {
            type: Boolean,
            default: false
        },
    })

    const appliedThroughOptions = [{
            id: 'direct_candidate',
            label: t('application.direct_candidate')
        },
        {
            id: 'agent',
            label: t('shared.labels.agent')
        },
    ]

    const paymentResponsibilityExclude = computed(() =>
        store.formData.applied_through === 'direct_candidate' ? ['agent'] : []
    )

    watch(
        () => store.formData.applied_through,
        (value, previous) => {
            if (value !== 'direct_candidate') return

            store.formData.agent_id = ''

            const current = normalizeJobPayers(store.formData.payment_responsibility).filter(
                (item) => item !== 'agent'
            )

            if (previous === 'agent' || !current.length) {
                store.formData.payment_responsibility = ['candidate']
            } else {
                store.formData.payment_responsibility = current
            }
        }
    )

    watch(
        () => store.formData.payment_responsibility,
        (value) => {
            if (normalizeJobPayers(value).length) {
                showPaymentValidation.value = false
            }
        }, {
            deep: true
        }
    )

    const filterJobList = (option, query) => {
        const jobName = (option.job_name ?? '').toLowerCase()
        const jobCode = (option.job_code ?? '').toLowerCase()
        const displayName = (option.name ?? '').toLowerCase()

        return jobName.includes(query) || jobCode.includes(query) || displayName.includes(query)
    }

    const filterAgent = (option, query) => {
        return (option.name ?? '').toLowerCase().includes(query)
    }
</script>
