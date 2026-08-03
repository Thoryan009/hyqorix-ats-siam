<template>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <!-- Header -->
        <div class="flex items-center gap-3 pb-3 mb-2 border-b-2 border-teal-500">
            <span
                class="flex items-center justify-center w-8 h-8 rounded-full bg-teal-500 text-white text-sm font-bold shrink-0">5</span>
            <i class="fa fa-files-o text-teal-500 text-xl"></i>
            <span class="font-semibold text-lg text-gray-800">{{ t('application.mergable_documents') }}</span>
        </div>
        <p class="text-sm text-gray-400 mt-1 mb-3">{{ t('application.mergable_documents_hint') }}</p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 py-4">

            <!-- Resume -->
            <div>
                <div class="overflow-hidden" v-if="store.formData?.resume_preview">
                    <div class="w-80 h-64 rounded-lg border shadow overflow-hidden bg-gray-50 relative">
                        <iframe :src="store.formData?.resume_preview" class="w-full h-full"></iframe>
                        <button
                            class="absolute top-2 right-2 w-6 h-6 bg-slate-900 rounded-full p-1 hover:bg-slate-700 cursor-pointer flex justify-center items-center transition text-slate-50"
                            @click="store.cancelImage('resume')" type="button">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
                <template v-else-if="store.item?.resume_url">
                    <div v-if="store.item.resume_url.split('?')[0].toLowerCase().endsWith('.pdf')"
                        class="w-80 h-64 rounded-lg border shadow overflow-hidden relative bg-gray-50">
                        <iframe :src="store.item.resume_url" class="w-full h-full"></iframe>

                    </div>

                </template>

                <BaseLabel for="resume_path">{{ t('application.resume') }} ({{ t('application.optional') }})</BaseLabel>
                <BaseFileInput accept=".pdf" @change="store.handleFileChange($event, 'resume_path', 'resume_preview')"
                    :fileName="store.fileName.resume_path" />
                <div v-if="store.fileError.resume_path" class="text-red-600 text-sm mt-1">
                    {{ store . fileError . resume_path }}</div>
            </div>

            <!-- Experience Certificate -->
            <div>
                <div class="overflow-hidden" v-if="store.formData?.experience_preview">
                    <div class="w-80 h-64 rounded-lg border shadow overflow-hidden bg-gray-50 relative">
                        <iframe :src="store.formData?.experience_preview" class="w-full h-full"></iframe>
                        <button
                            class="absolute top-2 right-2 w-6 h-6 bg-slate-900 rounded-full p-1 hover:bg-slate-700 cursor-pointer flex justify-center items-center transition text-slate-50"
                            @click="store.cancelImage('experience')" type="button">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
                <template v-else-if="store.item?.experience_url">
                    <div v-if="store.item.experience_url.split('?')[0].toLowerCase().endsWith('.pdf')"
                        class="w-80 h-64 rounded-lg border shadow overflow-hidden relative bg-gray-50">
                        <iframe :src="store.item.experience_url" class="w-full h-full"></iframe>

                    </div>

                </template>

                <BaseLabel for="experience_path">{{ t('application.experience_certificate') }} ({{ t('application.optional') }})</BaseLabel>
                <BaseFileInput accept=".pdf"
                    @change="store.handleFileChange($event, 'experience_path', 'experience_preview')"
                    :fileName="store.fileName.experience_path" />
                <div v-if="store.fileError.experience_path" class="text-red-600 text-sm mt-1">
                    {{ store . fileError . experience_path }}</div>
            </div>

            <!-- Education Certificate -->
            <div>
                <div class="overflow-hidden" v-if="store.formData?.education_preview">
                    <div class="w-80 h-64 rounded-lg border shadow overflow-hidden bg-gray-50 relative">
                        <iframe :src="store.formData?.education_preview" class="w-full h-full"></iframe>
                        <button
                            class="absolute top-2 right-2 w-6 h-6 bg-slate-900 rounded-full p-1 hover:bg-slate-700 cursor-pointer flex justify-center items-center transition text-slate-50"
                            @click="store.cancelImage('education')" type="button">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
                <template v-else-if="store.item?.education_url">
                    <div v-if="store.item.education_url.split('?')[0].toLowerCase().endsWith('.pdf')"
                        class="w-80 h-64 rounded-lg border shadow overflow-hidden relative bg-gray-50">
                        <iframe :src="store.item.education_url" class="w-full h-full"></iframe>

                    </div>

                </template>

                <BaseLabel for="education_path">{{ t('application.education_certificate') }} ({{ t('application.optional') }})</BaseLabel>
                <BaseFileInput accept=".pdf"
                    @change="store.handleFileChange($event, 'education_path', 'education_preview')"
                    :fileName="store.fileName.education_path" />
                <div v-if="store.fileError.education_path" class="text-red-600 text-sm mt-1">
                    {{ store . fileError . education_path }}</div>
            </div>

            <!-- Training Certificate -->
            <div>
               <div class="overflow-hidden" v-if="store.formData?.training_preview">
                    <div class="w-80 h-64 rounded-lg border shadow overflow-hidden bg-gray-50 relative">
                        <iframe :src="store.formData?.training_preview" class="w-full h-full"></iframe>
                        <button
                            class="absolute top-2 right-2 w-6 h-6 bg-slate-900 rounded-full p-1 hover:bg-slate-700 cursor-pointer flex justify-center items-center transition text-slate-50"
                            @click="store.cancelImage('training')" type="button">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
                <template v-else-if="store.item?.training_url">
                    <div v-if="store.item.training_url.split('?')[0].toLowerCase().endsWith('.pdf')"
                        class="w-80 h-64 rounded-lg border shadow overflow-hidden relative bg-gray-50">
                        <iframe :src="store.item.training_url" class="w-full h-full"></iframe>

                    </div>

                </template>

                <BaseLabel for="training_path">{{ t('application.training_certificate') }} ({{ t('application.optional') }})</BaseLabel>
                <BaseFileInput accept=".pdf"
                    @change="store.handleFileChange($event, 'training_path', 'training_preview')"
                    :fileName="store.fileName.training_path" />
                <div v-if="store.fileError.training_path" class="text-red-600 text-sm mt-1">
                    {{ store . fileError . training_path }}</div>
            </div>

            <!-- Passport PDF -->
            <div>
              <div class="overflow-hidden" v-if="store.formData?.passport_pdf_preview">
                    <div class="w-80 h-64 rounded-lg border shadow overflow-hidden bg-gray-50 relative">
                        <iframe :src="store.formData?.passport_pdf_preview" class="w-full h-full"></iframe>
                        <button
                            class="absolute top-2 right-2 w-6 h-6 bg-slate-900 rounded-full p-1 hover:bg-slate-700 cursor-pointer flex justify-center items-center transition text-slate-50"
                            @click="store.cancelImage('passport_pdf')" type="button">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
                <template v-else-if="store.item?.passport_pdf_url">
                    <div v-if="store.item.passport_pdf_url.split('?')[0].toLowerCase().endsWith('.pdf')"
                        class="w-80 h-64 rounded-lg border shadow overflow-hidden relative bg-gray-50">
                        <iframe :src="store.item.passport_pdf_url" class="w-full h-full"></iframe>

                    </div>

                </template>

                <BaseLabel for="passport_pdf_path">{{ t('application.passport_pdf') }} ({{ t('application.optional') }})</BaseLabel>
                <BaseFileInput accept=".pdf"
                    @change="store.handleFileChange($event, 'passport_pdf_path', 'passport_pdf_preview')"
                    :fileName="store.fileName.passport_pdf_path" />
                <div v-if="store.fileError.passport_pdf_path" class="text-red-600 text-sm mt-1">
                    {{ store . fileError . passport_pdf_path }}</div>
            </div>

            <!-- Driving License -->
            <div>
               <div class="overflow-hidden" v-if="store.formData?.driving_license_preview">
                    <div class="w-80 h-64 rounded-lg border shadow overflow-hidden bg-gray-50 relative">
                        <iframe :src="store.formData?.driving_license_preview" class="w-full h-full"></iframe>
                        <button
                            class="absolute top-2 right-2 w-6 h-6 bg-slate-900 rounded-full p-1 hover:bg-slate-700 cursor-pointer flex justify-center items-center transition text-slate-50"
                            @click="store.cancelImage('driving_license')" type="button">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
                <template v-else-if="store.item?.driving_license_url">
                    <div v-if="store.item.driving_license_url.split('?')[0].toLowerCase().endsWith('.pdf')"
                        class="w-80 h-64 rounded-lg border shadow overflow-hidden relative bg-gray-50">
                        <iframe :src="store.item.driving_license_url" class="w-full h-full"></iframe>

                    </div>

                </template>

                <BaseLabel for="driving_license_path">{{ t('application.driving_license') }} ({{ t('application.optional') }})</BaseLabel>
                <BaseFileInput accept=".pdf"
                    @change="store.handleFileChange($event, 'driving_license_path', 'driving_license_preview')"
                    :fileName="store.fileName.driving_license_path" />
                <div v-if="store.fileError.driving_license_path" class="text-red-600 text-sm mt-1">
                    {{ store . fileError . driving_license_path }}</div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import {
        useApplicationStore
    } from '@/modules/application/store/applicationStore'
import {useTranslate} from '@/shared/composables/useTranslate'

const { t } = useTranslate()
    const store = useApplicationStore()
</script>
