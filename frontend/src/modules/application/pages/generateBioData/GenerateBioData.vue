<template>
  <div class="p-6">
    <div v-if="isLoading" class="text-center py-8 print:hidden">
      <p class="text-gray-600">Loading application data...</p>
    </div>

    <div v-else-if="error" class="text-center py-8 print:hidden">
      <p class="text-red-600">Error loading application: {{ error.message }}</p>
    </div>

    <div class="flex gap-2 flex-row-reverse max-w-7xl mx-auto" v-else-if="applicationData">
      <!-- Action Buttons (Hidden in Print) -->
      <div class="no-print mb-6 flex justify-between gap-3">
        <!-- Print Settings Panel -->
        <div class="bg-white rounded-lg shadow-md p-4 w-fit lg:w-[350px] h-fit">
          <div class="flex justify-between items-center mb-3">
            <h3 class="text-sm font-bold text-gray-900 flex items-center">
              <i class="fa fa-cog mr-2"></i>Print Settings
            </h3>
            <div class="flex gap-2">
              <button
                @click="Object.keys(printSettings).forEach((key) => (printSettings[key] = true))"
                class="text-xs px-2 py-1 bg-green-600 text-white rounded hover:bg-green-700"
              >
                Select All
              </button>
              <button
                @click="Object.keys(printSettings).forEach((key) => (printSettings[key] = false))"
                class="text-xs px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700"
              >
                Clear All
              </button>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-2 text-xs max-h-96 overflow-y-auto">
            <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
              <input type="checkbox" v-model="printSettings.showPhoto" class="cursor-pointer" />
              <span>Photo</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
              <input type="checkbox" v-model="printSettings.showMobile" class="cursor-pointer" />
              <span>Mobile</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
              <input type="checkbox" v-model="printSettings.showEmail" class="cursor-pointer" />
              <span>Email</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
              <input type="checkbox" v-model="printSettings.showAddress" class="cursor-pointer" />
              <span>Address</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
              <input type="checkbox" v-model="printSettings.showLanguage" class="cursor-pointer" />
              <span>Language</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
              <input type="checkbox" v-model="printSettings.showEducation" class="cursor-pointer" />
              <span>Education</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
              <input type="checkbox" v-model="printSettings.showSummary" class="cursor-pointer" />
              <span>Summary</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
              <input
                type="checkbox"
                v-model="printSettings.showFatherName"
                class="cursor-pointer"
              />
              <span>Father Name</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
              <input
                type="checkbox"
                v-model="printSettings.showMotherName"
                class="cursor-pointer"
              />
              <span>Mother Name</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
              <input
                type="checkbox"
                v-model="printSettings.showMaritalStatus"
                class="cursor-pointer"
              />
              <span>Marital Status</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
              <input
                type="checkbox"
                v-model="printSettings.showDateOfBirth"
                class="cursor-pointer"
              />
              <span>Date of Birth</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
              <input type="checkbox" v-model="printSettings.showAge" class="cursor-pointer" />
              <span>Age</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
              <input type="checkbox" v-model="printSettings.showGender" class="cursor-pointer" />
              <span>Gender</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
              <input
                type="checkbox"
                v-model="printSettings.showNationality"
                class="cursor-pointer"
              />
              <span>Nationality</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
              <input type="checkbox" v-model="printSettings.showNID" class="cursor-pointer" />
              <span>NID</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
              <input type="checkbox" v-model="printSettings.showPassport" class="cursor-pointer" />
              <span>Passport No</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
              <input
                type="checkbox"
                v-model="printSettings.showPassportDates"
                class="cursor-pointer"
              />
              <span>Passport Dates</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
              <input
                type="checkbox"
                v-model="printSettings.showDrivingLicense"
                class="cursor-pointer"
              />
              <span>Driving License</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
              <input type="checkbox" v-model="printSettings.showHeight" class="cursor-pointer" />
              <span>Height Label</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
              <input type="checkbox" v-model="printSettings.showHeightValue" class="cursor-pointer" />
              <span>Height Value</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
              <input type="checkbox" v-model="printSettings.showWeight" class="cursor-pointer" />
              <span>Weight Label</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
              <input type="checkbox" v-model="printSettings.showWeightValue" class="cursor-pointer" />
              <span>Weight Value</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
              <input
                type="checkbox"
                v-model="printSettings.showQualificationValue"
                class="cursor-pointer"
              />
              <span>Qualification Value</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
              <input
                type="checkbox"
                v-model="printSettings.showExperience"
                class="cursor-pointer"
              />
              <span>Experience</span>
            </label>
          </div>

          <div class="text-center">
            <button
              @click="handleDownloadPDF"
              class="bg-[#588F36] text-white px-6 py-2 mt-10 rounded-lg hover:bg-[#446d28] transition-colors"
            >
              <i class="fa fa-download mr-2"></i>Download PDF
            </button>
          </div>
        </div>
      </div>

      <!-- BioData Content -->
      <div
        id="biodata-print"
        class="biodata-container bg-white rounded-lg shadow-md mx-auto a4-container"
      >
        <!-- Main Content: Two Column Layout -->
        <div class="flex">
          <!-- Left Sidebar -->
          <div class="left-sidebar bg-gray-50 px-5 py-12" style="width: 35%">
            <!-- Photo -->
            <div class="flex justify-center mb-4" v-if="printSettings.showPhoto">
              <div
                v-if="applicationData.worker_image_url"
                class="w-32 h-32 rounded-full border-4 border-[#588F36] overflow-hidden"
              >
                <img
                  :src="applicationData.worker_image_url"
                  alt="Worker Photo"
                  class="w-full h-full object-cover"
                />
              </div>
              <div
                v-else
                class="w-32 h-32 rounded-full border-4 border-gray-300 flex items-center justify-center bg-gray-200"
              >
                <i class="fa fa-user text-4xl text-gray-400"></i>
              </div>
            </div>
            <!-- Name & Position -->
            <div class="text-center mb-6">
              <h2 class="text-xl font-bold text-gray-900">{{ applicationData.full_name }}</h2>
              <p class="text-[#588F36] font-medium text-sm" v-if="applicationData.just_job">
                {{ applicationData.just_job }}
              </p>
            </div>

            <!-- Contact Information -->
            <div
              class="mb-6"
              v-if="
                printSettings.showMobile || printSettings.showEmail || printSettings.showAddress
              "
            >
              <h3 class="text-md font-bold text-[#588F36] mb-3">Contact Information</h3>
              <div class="space-y-3 text-sm">
                <div class="flex items-start" v-if="printSettings.showMobile">
                  <i class="fa fa-phone text-[#588F36] mr-2 mt-1"></i>
                  <span class="text-gray-700">{{ applicationData.mobile }}</span>
                </div>
                <div v-if="role != 'client' && printSettings.showEmail" class="flex items-start">
                  <i class="fa fa-envelope text-[#588F36] mr-2 mt-1"></i>
                  <span class="text-gray-700 break-all">{{ applicationData.email }}</span>
                </div>
                <div
                  class="flex items-start"
                  v-if="
                    printSettings.showAddress &&
                    (applicationData.address || applicationData.place_of_birth)
                  "
                >
                  <i class="fa fa-map-marker text-[#588F36] mr-2 mt-1"></i>
                  <span class="text-gray-700">{{
                    applicationData.address || applicationData.place_of_birth
                  }}</span>
                </div>
              </div>
            </div>

            <!-- Language Skills -->
            <div class="mb-6" v-if="printSettings.showLanguage && applicationData.language">
              <h3 class="text-md font-bold text-[#588F36] mb-3">Language Skills</h3>
              <p class="text-sm text-gray-700">{{ applicationData.language }}</p>
            </div>

            <!-- Education -->
            <div
              class="mb-6"
              v-if="
                printSettings.showEducation &&
                (applicationData.qualification || applicationData.subject)
              "
            >
              <h3 class="text-md font-bold text-[#588F36] mb-3">Education</h3>
              <div class="text-sm space-y-2">
                <p class="font-semibold text-gray-900" v-if="applicationData.qualification">
                  <span class="font-medium">Qualification:</span>
                  <span v-if="printSettings.showQualificationValue">{{ applicationData.qualification }}</span>
                </p>
                <p class="text-gray-700" v-if="applicationData.subject">
                  <span class="font-medium">Subject:</span> {{ applicationData.subject }}
                </p>
              </div>
            </div>
          </div>

          <!-- Right Content Area -->
          <div class="right-content bg-white px-5 py-12" style="width: 65%">
            <!-- Professional Summary -->
            <section class="mb-5" v-if="printSettings.showSummary && applicationData.summary">
              <h2 class="text-lg font-bold text-[#588F36] mb-3">Professional Summary</h2>
              <p class="text-sm text-justify text-gray-700 leading-relaxed">
                {{ applicationData.summary }}
              </p>
            </section>

            <!-- Personal Information -->
            <section class="mb-5">
              <h2 class="text-lg font-bold text-[#588F36] mb-3">Personal Information</h2>
              <div class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <div
                  class="flex flex-col gap-2"
                  v-if="printSettings.showFatherName && applicationData.father_name"
                >
                  <p class="font-semibold text-gray-900">Father Name:</p>
                  <p class="text-gray-700">{{ applicationData.father_name }}</p>
                </div>
                <div
                  class="flex flex-col gap-2"
                  v-if="printSettings.showMotherName && applicationData.mother_name"
                >
                  <p class="font-semibold text-gray-900">Mother Name:</p>
                  <p class="text-gray-700">{{ applicationData.mother_name }}</p>
                </div>
                <div
                  class="flex flex-col gap-2"
                  v-if="printSettings.showMaritalStatus && applicationData.marital_status"
                >
                  <p class="font-semibold text-gray-900">Marital Status:</p>
                  <p class="text-gray-700">
                    {{
                      applicationData.marital_status === 'married'
                        ? 'Married'
                        : applicationData.marital_status === 'single'
                          ? 'Single'
                          : applicationData.marital_status === 'divorced'
                            ? 'Divorced'
                            : applicationData.marital_status === 'widowed'
                              ? 'Widowed'
                              : applicationData.marital_status
                    }}
                  </p>
                </div>
                <div
                  class="flex flex-col gap-2"
                  v-if="printSettings.showDateOfBirth && applicationData.date_of_birth"
                >
                  <p class="font-semibold text-gray-900">Date of Birth:</p>
                  <p class="text-gray-700">{{ formatDate(applicationData.date_of_birth) }}</p>
                </div>
                <div
                  class="flex flex-col gap-2"
                  v-if="printSettings.showAge && applicationData.date_of_birth"
                >
                  <p class="font-semibold text-gray-900">Age:</p>
                  <p class="text-gray-700">
                    {{ calculateAge(applicationData.date_of_birth) }} years
                  </p>
                </div>
                <div
                  class="flex flex-col gap-2"
                  v-if="printSettings.showGender && applicationData.sex"
                >
                  <p class="font-semibold text-gray-900">Gender:</p>
                  <p class="text-gray-700 capitalize">{{ applicationData.sex }}</p>
                </div>
                <div
                  class="flex flex-col gap-2"
                  v-if="printSettings.showNationality && applicationData.nationality"
                >
                  <p class="font-semibold text-gray-900">Nationality:</p>
                  <p class="text-gray-700">{{ applicationData.nationality }}</p>
                </div>
                <div
                  class="flex flex-col gap-2"
                  v-if="printSettings.showNID && applicationData.nid_no"
                >
                  <p class="font-semibold text-gray-900">NID No:</p>
                  <p class="text-gray-700">{{ applicationData.nid_no }}</p>
                </div>
                <div
                  class="flex flex-col gap-2"
                  v-if="printSettings.showPassport && applicationData.passport_no"
                >
                  <p class="font-semibold text-gray-900">Passport No:</p>
                  <p class="text-gray-700">{{ applicationData.passport_no }}</p>
                </div>
                <div
                  class="flex flex-col gap-2"
                  v-if="printSettings.showPassportDates && applicationData.date_of_issue"
                >
                  <p class="font-semibold text-gray-900">Date of Issue:</p>
                  <p class="text-gray-700">{{ formatDate(applicationData.date_of_issue) }}</p>
                </div>
                <div
                  class="flex flex-col gap-2"
                  v-if="printSettings.showPassportDates && applicationData.date_of_expiry"
                >
                  <p class="font-semibold text-gray-900">Date of Expiry:</p>
                  <p class="text-gray-700">{{ formatDate(applicationData.date_of_expiry) }}</p>
                </div>
                <div
                  class="flex flex-col gap-2"
                  v-if="printSettings.showDrivingLicense && applicationData.driving_license_no"
                >
                  <p class="font-semibold text-gray-900">Driving License No:</p>
                  <p class="text-gray-700">{{ applicationData.driving_license_no }}</p>
                </div>
                <div
                  class="flex flex-col gap-2"
                  v-if="(printSettings.showHeight || printSettings.showHeightValue) && applicationData.height"
                >
                  <p class="font-semibold text-gray-900" v-if="printSettings.showHeight">Height:</p>
                  <p class="text-gray-700" v-if="printSettings.showHeightValue">{{ applicationData.height }}</p>
                </div>
                <div
                  class="flex flex-col gap-2"
                  v-if="(printSettings.showWeight || printSettings.showWeightValue) && applicationData.weight"
                >
                  <p class="font-semibold text-gray-900" v-if="printSettings.showWeight">Weight:</p>
                  <p class="text-gray-700" v-if="printSettings.showWeightValue">{{ applicationData.weight }}</p>
                </div>
              </div>
            </section>

            <!-- Work Experience -->
            <section
              class="mb-5"
              v-if="
                printSettings.showExperience &&
                (applicationData.job ||
                  applicationData.bd_exp ||
                  applicationData.overseas_exp ||
                  (applicationData.experiences && applicationData.experiences.length > 0))
              "
            >
              <h2 class="text-lg font-bold text-[#588F36] mb-3">Work Experience</h2>

              <!-- Display new experiences array -->
              <div
                v-if="applicationData.experiences && applicationData.experiences.length > 0"
                class="space-y-4"
              >
                <div
                  v-for="(experience, index) in applicationData.experiences"
                  :key="index"
                  class="mb-4 pb-4 border-b border-gray-200 last:border-b-0"
                >
                  <div class="flex justify-between items-start mb-1">
                    <div class="flex flex-1 items-center">
                      <h3 class="font-semibold text-gray-900">
                        {{ experience.position || 'Position Not Specified' }} &nbsp;
                      </h3>
                      <p class="text-sm text-[#588F36]" v-if="experience.company_name">
                        ({{ experience.company_name }})
                      </p>
                    </div>
                    <span class="text-xs w-fit text-gray-600 bg-gray-100 px-2 py-1 rounded">
                      {{
                        experience.types === 'bd_exp'
                          ? 'Local'
                          : experience.types === 'overseas_exp'
                            ? 'Overseas'
                            : experience.types
                      }}
                    </span>
                  </div>

                  <div class="text-xs text-gray-600 mb-2" v-if="experience.from_date">
                    {{ formatDate(experience.from_date) }}
                    <span v-if="experience.to_date"> - {{ formatDate(experience.to_date) }}</span>
                    <span v-else> - Present</span>
                  </div>

                  <div
                    v-if="experience.responsibilities && experience.responsibilities.length > 0"
                    class="mt-2"
                  >
                    <p class="text-xs font-semibold text-gray-700 mb-1">Responsibilities:</p>
                    <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                      <li v-for="(resp, rIndex) in experience.responsibilities" :key="rIndex">
                        {{ resp }}
                      </li>
                    </ul>
                  </div>
                </div>
              </div>

              <!-- Fallback to old bd_exp/overseas_exp if no new experiences -->
              <div
                v-else-if="
                  applicationData.job || applicationData.bd_exp || applicationData.overseas_exp
                "
                class="mb-3"
              >
                <div class="flex justify-between items-start mb-1">
                  <h3 class="font-semibold text-gray-900">{{ applicationData.job }}</h3>
                  <span class="text-xs text-gray-600">Current</span>
                </div>
                <p class="text-sm text-[#588F36]" v-if="applicationData.qualification">
                  {{ applicationData.qualification }}
                </p>
                <div
                  class="text-sm text-gray-700 mt-1"
                  v-if="applicationData.bd_exp || applicationData.overseas_exp"
                >
                  <span v-if="applicationData.bd_exp"
                    ><strong>Local:</strong> {{ applicationData.bd_exp }}</span
                  >
                  <span class="ml-4" v-if="applicationData.overseas_exp"
                    ><strong>Overseas:</strong> {{ applicationData.overseas_exp }}</span
                  >
                </div>
              </div>
            </section>

            <!-- Job Application Details -->
            <!-- <section class="mb-5">
              <h2 class="text-lg font-bold text-[#588F36] mb-3">Job Application Details</h2>
              <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
                <div class="flex flex-col gap-1" v-if="applicationData.job">
                  <p class="font-semibold text-gray-900">Applied Position:</p>
                  <p class="text-gray-700">{{ applicationData.job }}</p>
                </div>
              </div>
            </section> -->
          </div>
        </div>

        <!-- Footer -->
        <!-- <div class="bg-white border-t border-gray-100 px-8 py-4">
          <div class="flex justify-between items-center text-xs text-gray-600">
            <p>This CV was generated by MERCHANT OVERSEAS on {{ formatDate(new Date()) }}.</p>
            <p>© {{ new Date().getFullYear() }} MERCHANT OVERSEAS - All rights reserved</p>
          </div>
        </div> -->
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useApplicationQuery } from '../../queries/useApplicationsQuery'
import { printWithOrientation } from '@/shared/utils/printOrientation'

const route = useRoute()
const applicationId = computed(() => route.query.id)

const { data, isLoading, error } = useApplicationQuery(applicationId)

const applicationData = computed(() => data.value?.data?.data)

// Print control settings
const printSettings = ref({
  showPhoto: true,
  showMobile: true,
  showEmail: true,
  showAddress: true,
  showLanguage: true,
  showEducation: true,
  showSummary: true,
  showFatherName: true,
  showMotherName: true,
  showMaritalStatus: true,
  showDateOfBirth: true,
  showAge: true,
  showGender: true,
  showNationality: true,
  showNID: true,
  showPassport: true,
  showPassportDates: true,
  showDrivingLicense: true,
  showHeight: true,
  showWeight: true,

  showHeightValue: true,
  showWeightValue: true,
  showQualificationValue: true,

  showExperience: true,
})

const handleDownloadPDF = () => {
  printWithOrientation('portrait', '0', 'biodata-print')
}

// Calculate age from date of birth
const calculateAge = (dateOfBirth) => {
  if (!dateOfBirth) return 'N/A'
  const today = new Date()
  const birthDate = new Date(dateOfBirth)
  let age = today.getFullYear() - birthDate.getFullYear()
  const monthDiff = today.getMonth() - birthDate.getMonth()
  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
    age--
  }
  return age
}

// Format date
const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

//onMounted(() => {
//  setTimeout(() => {
//    window.print()
//  }, 1000)
//})
</script>

<style scoped>
/* A4 Container */
.a4-container {
  width: 210mm;
  min-height: auto;
  max-height: 297mm;
  box-sizing: border-box;
}

.left-sidebar {
  border-right: 1px solid #e5e7eb;
}

@media print {
  html,
  body {
    margin: 0;
    padding: 0;
    height: 297mm;
    width: 210mm;
  }

  .no-print {
    display: none !important;
  }

  .biodata-container {
    box-shadow: none !important;
    width: 210mm !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    page-break-after: avoid;
    page-break-inside: avoid;
  }

  .left-sidebar,
  .right-content {
    padding: 15mm 10mm !important;
  }

  /* Set page size to A4 */
  @page {
    size: A4 portrait;
    margin: 0;
  }

  /* Ensure colors print correctly */
  * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  /* Prevent page breaks inside sections */
  section {
    page-break-inside: avoid;
  }

  /* Compact line heights for print */
  p,
  span,
  div {
    line-height: 1.4;
  }

  /* Ensure borders print */
  .border-b-4,
  .border-t,
  .border-4,
  .border-right {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
}

@media screen {
  .a4-container {
    /* Shadow effect for screen view to show A4 appearance */
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  }
}
</style>

<style>
@media print {
  #biodata-print .overflow-x-auto {
    overflow: visible;
  }

  #biodata-print h1,
  #biodata-print h2,
  #biodata-print h3,
  #biodata-print p,
  #biodata-print span,
  #biodata-print li {
    color: #000 !important;
  }

  #biodata-print * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
}
</style>
