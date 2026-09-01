<template>
    <SectionHeader>
        <!-- Card with Tabs -->
        <div class="bg-white rounded-lg shadow max-w-3xl mx-auto">
            <!-- Tab Navigation -->
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <button v-can="'setting.basic'" @click="activeTab = 'basic'"
                        :class="[
                            'px-6 py-4 text-sm font-medium border-b-2 transition-colors',
                            activeTab === 'basic' ?
                            'border-primary text-primary' :
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                        ]">
                        {{ t('setting.tabs.basic') }}
                    </button>
                    <button v-can="'setting.password'" @click="activeTab = 'password'"
                        :class="[
                            'px-6 py-4 text-sm font-medium border-b-2 transition-colors',
                            activeTab === 'password' ?
                            'border-primary text-primary' :
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                        ]">
                        {{ t('setting.tabs.password') }}
                    </button>
                    <button v-can="'setting.backup'" @click="activeTab = 'backup'"
                        :class="[
                            'px-6 py-4 text-sm font-medium border-b-2 transition-colors',
                            activeTab === 'backup' ?
                            'border-primary text-primary' :
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                        ]">
                        {{ t('setting.tabs.backup') }}
                    </button>

                    <button v-can="'setting.embassy'" @click="activeTab = 'embassy'"
                        :class="[
                            'px-6 py-4 text-sm font-medium border-b-2 transition-colors',
                            activeTab === 'embassy' ?
                            'border-primary text-primary' :
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                        ]">
                        {{ t('setting.tabs.embassy') }}
                    </button>

                    <button v-can="'setting.party_type_mapping'" @click="activeTab = 'party_type_mapping'"
                        :class="[
                            'px-6 py-4 text-sm font-medium border-b-2 transition-colors',
                            activeTab === 'party_type_mapping' ?
                            'border-primary text-primary' :
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                        ]">
                        {{ t('setting.tabs.party_type_mapping') }}
                    </button>


                </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- Basic Settings Tab -->
                <BasicSettings v-if="activeTab === 'basic'" v-can="'setting.basic'" />

                <!-- Password Change Tab -->
                <PasswordChange v-if="activeTab === 'password' " v-can="'setting.password'" />

                <!-- Database Backup Tab -->
                <DatabaseBackup v-if="activeTab === 'backup' " v-can="'setting.backup'" />

                <!-- Embassy Settings Tab -->
                <EmbassySettings v-if="activeTab === 'embassy' " v-can="'setting.embassy'" />

                <!-- Party Type Mapping Tab -->
                <PartyTypeMappingSettings
                    v-if="activeTab === 'party_type_mapping'"
                    v-can="'setting.party_type_mapping'"
                />


            </div>
        </div>
    </SectionHeader>
</template>

<script setup>
    import {
        ref
    } from 'vue'
    import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
    import BasicSettings from './parts/BasicSettings.vue'
    import PasswordChange from './parts/PasswordChange.vue'
    import DatabaseBackup from './parts/DatabaseBackup.vue'
    import EmbassySettings from './parts/EmbassySettings.vue'
    import PartyTypeMappingSettings from './parts/PartyTypeMappingSettings.vue'
    import { useTranslate } from '@/shared/composables/useTranslate'
    const { t } = useTranslate()
    // Active tab state
    const activeTab = ref('basic')
</script>
