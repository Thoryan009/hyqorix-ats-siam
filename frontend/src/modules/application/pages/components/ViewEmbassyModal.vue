<template>
	<BaseModal
		:isVisible="store.isViewModal"
		:title="t('embassy.visa_processing_submission')"
		@close="store.handleToggleModal"
		:className="'xl:max-w-[40vw]'"
	>
		<ViewModalLayout :height="'55vh'">
			<!-- Header like other view modals -->
			<div class="bg-linear-to-r from-gray-50 to-gray-100 rounded-lg p-2 shadow-sm">
				<div class="flex items-center gap-4 p-4 bg-white rounded-lg">
					<div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center overflow-hidden">
						<img v-if="store.item?.worker_image_url" :src="store.item.worker_image_url" class="w-full h-full object-cover" />
						<i v-else class="fa fa-user text-gray-400 text-2xl"></i>
					</div>
					<div class="flex-1">
						<h3 class="text-lg font-bold text-gray-800">{{ store.item?.full_name }}</h3>
						<p class="text-sm text-gray-600">{{ t('shared.labels.job') }}: <span class="font-medium">{{ store.item?.job }}</span></p>
						<p class="text-sm text-gray-600">{{ t('shared.labels.passport_no') }}: <span class="font-medium">{{ store.item?.passport_no || 'N/A' }}</span></p>
						<p class="text-sm text-gray-600">{{ t('application.nationality') }}: <span class="font-medium">{{ store.item?.nationality || 'N/A' }}</span></p>
					</div>
					<div class="text-right">
						<p class="text-sm text-gray-500">{{ t('embassy.applied_id') }}</p>
						<p class="text-sm font-semibold text-gray-800">{{ store.item?.application_id || store.item?.id }}</p>
					</div>
				</div>
			</div>

			<!-- Content area: simple list-style read view matching other view modals -->
			<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
				<div class="bg-white p-4 rounded-lg shadow-sm">
					<h4 class="text-sm font-semibold text-gray-700 mb-2">{{ t('embassy.religion') }}</h4>
					<p class="text-gray-800">{{ embData.religion ? (embData.religion === 'muslim' ? t('embassy.muslim') : t('embassy.non_muslim')) : '—' }}</p>
				</div>

				<div class="bg-white p-4 rounded-lg shadow-sm">
					<h4 class="text-sm font-semibold text-gray-700 mb-2">{{ t('embassy.visa_profession_arabic') }}</h4>
					<p class="text-gray-800 break-words">{{ embData.visa_profession_ar || '—' }}</p>
				</div>

				<div class="bg-white p-4 rounded-lg shadow-sm">
					<h4 class="text-sm font-semibold text-gray-700 mb-2">{{ t('embassy.visa_profession_english') }}</h4>
					<p class="text-gray-800 break-words">{{ embData.visa_profession_en || '—' }}</p>
				</div>

				<div class="bg-white p-4 rounded-lg shadow-sm">
					<h4 class="text-sm font-semibold text-gray-700 mb-2">{{ t('embassy.visit_work_for_arabic') }}</h4>
					<p class="text-gray-800 break-words">{{ embData.visit_work_for_ar || '—' }}</p>
				</div>
			</div>

			<div class="flex justify-end pt-4">
				<BaseButton type="button" class="bg-gray-200" @click="store.handleToggleModal">{{ t('shared.actions.close') }}</BaseButton>
			</div>
		</ViewModalLayout>
	</BaseModal>
</template>

<script setup>
import { watch, ref } from 'vue'
import { useApplicationStore } from '../../store/applicationStore'
import ViewModalLayout from '@/shared/components/ui/ViewModalLayout.vue'
import { useTranslate } from '@/shared/composables/useTranslate'
const { t } = useTranslate()
const store = useApplicationStore()

const embData = ref({
	application_id: null,
	religion: null,
	visa_profession_ar: '',
	visa_profession_en: '',
	visit_work_for_ar: '',
})

defineProps({
	jobs: { type: Array, default: () => [] },
	subjects: { type: Array, default: () => [] },
	qualifications: { type: Array, default: () => [] },
	agents: { type: Array, default: () => [] },
})

// Prefill when modal opens or selected row changes
watch(
	() => store.item,
	(val) => {
		if (!val) return
		const emb = val.embassy_submission || {}
		embData.value = {
			application_id: val.id,
			religion: emb.religion ?? null,
			visa_profession_ar: emb.visa_profession_ar ?? '',
			visa_profession_en: emb.visa_profession_en ?? '',
			visit_work_for_ar: emb.visit_work_for_ar ?? '',
		}
	},
	{ immediate: true },
)
</script>
