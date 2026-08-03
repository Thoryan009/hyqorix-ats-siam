<template>
  <BaseModal
    :isVisible="store.isViewModal"
    :title="`View ${store.moduleName} Details`"
    @close="store.handleToggleModal"
    :className="'xl:max-w-[80vw]'"
  >
    <ViewModalLayout>
      <!-- Summary Card -->
      <div v-can="'transaction.view_summary'"
        class="bg-linear-to-r from-blue-50 to-indigo-50 rounded-xl border-2 border-blue-300 p-6 mb-6 shadow-lg"
      >
        <div class="grid grid-cols-5 gap-4">
          <div class="text-center">
            <div class="flex justify-center mb-2">
              <i class="fa fa-file-text text-2xl text-indigo-600"></i>
            </div>
            <p class="text-xs text-gray-600 mb-1 font-medium">Bill Number</p>
            <p class="text-lg font-bold text-gray-800">{{ store.item.bill_no }}</p>
          </div>
          <div class="text-center border-l-2 border-blue-300 pl-4">
            <div class="flex justify-center mb-2">
              <i class="fa fa-money text-2xl text-green-600"></i>
            </div>
            <p class="text-xs text-gray-600 mb-1 font-medium">Total Amount</p>
            <p class="text-lg font-bold text-green-600">{{ store.item.total_amount }}</p>
          </div>
          <div class="text-center border-l-2 border-blue-300 pl-4">
            <div class="flex justify-center mb-2">
              <i class="fa fa-check-circle text-2xl text-blue-600"></i>
            </div>
            <p class="text-xs text-gray-600 mb-1 font-medium">Paid Amount</p>
            <p class="text-lg font-bold text-blue-600">{{ store.item.total_paid_amount }}</p>
          </div>
          <div class="text-center border-l-2 border-blue-300 pl-4">
            <div class="flex justify-center mb-2">
              <i class="fa fa-exclamation-triangle text-2xl text-red-600"></i>
            </div>
            <p class="text-xs text-gray-600 mb-1 font-medium">Due Amount</p>
            <p class="text-lg font-bold text-red-600">{{ store.item.due_amount }}</p>
          </div>
          <div class="text-center border-l-2 border-blue-300 pl-4">
            <div class="flex justify-center mb-2">
              <i class="fa fa-info-circle text-2xl text-yellow-600"></i>
            </div>
            <p class="text-xs text-gray-600 mb-1 font-medium">Status</p>
            <p
              class="text-sm font-semibold text-gray-800 bg-yellow-100 px-3 py-1 rounded-full inline-block mt-1"
            >
              {{ store.item.status }}
            </p>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div
        class="bg-white rounded-xl border-2 border-gray-200 text-black shadow-md overflow-hidden"
      >
        <!-- Tab Headers -->
        <div class="flex bg-gray-50">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'flex-1 px-4 py-4 text-sm font-semibold transition-all duration-300 flex items-center justify-center gap-2 relative',
              activeTab === tab.id
                ? `${tab.activeClass} border-b-4 shadow-md transform scale-105`
                : `${tab.inactiveClass} hover:bg-gray-100 border-b-4 border-transparent`,
            ]"
          >
            <i :class="['fa', tab.icon, 'text-lg']"></i>
            <span>{{ tab.label }}</span>
          </button>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
          <!-- Bill Information Tab -->
          <div v-can="'transaction.view_bill_information'" v-if="activeTab === 'bill'" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div
                class="bg-purple-50 border-l-4 border-purple-500 rounded-lg p-4 shadow hover:shadow-md transition-shadow"
              >
                <div class="flex items-center gap-3">
                  <i class="fa fa-hashtag text-2xl text-purple-600"></i>
                  <div>
                    <p class="text-xs font-medium text-purple-600 uppercase">Bill ID</p>
                    <p class="text-lg font-bold text-purple-900">{{ store.item.id }}</p>
                  </div>
                </div>
              </div>
              <div
                class="bg-purple-50 border-l-4 border-purple-500 rounded-lg p-4 shadow hover:shadow-md transition-shadow"
              >
                <div class="flex items-center gap-3">
                  <i class="fa fa-file-text-o text-2xl text-purple-600"></i>
                  <div>
                    <p class="text-xs font-medium text-purple-600 uppercase">Bill Number</p>
                    <p class="text-lg font-bold text-purple-900">{{ store.item.bill_no }}</p>
                  </div>
                </div>
              </div>
              <div
                class="bg-purple-50 border-l-4 border-purple-500 rounded-lg p-4 shadow hover:shadow-md transition-shadow"
              >
                <div class="flex items-center gap-3">
                  <i class="fa fa-exchange text-2xl text-purple-600"></i>
                  <div>
                    <p class="text-xs font-medium text-purple-600 uppercase">Transaction ID</p>
                    <p class="text-lg font-bold text-purple-900">{{ store.item.transaction_id }}</p>
                  </div>
                </div>
              </div>
              <div
                class="bg-purple-50 border-l-4 border-purple-500 rounded-lg p-4 shadow hover:shadow-md transition-shadow"
              >
                <div class="flex items-center gap-3">
                  <i class="fa fa-credit-card text-2xl text-purple-600"></i>
                  <div>
                    <p class="text-xs font-medium text-purple-600 uppercase">Payment Method</p>
                    <p class="text-lg font-bold text-purple-900">{{ store.item.payment_method }}</p>
                  </div>
                </div>
              </div>
              <div
                class="bg-purple-50 border-l-4 border-purple-500 rounded-lg p-4 shadow hover:shadow-md transition-shadow"
              >
                <div class="flex items-center gap-3">
                  <i class="fa fa-info-circle text-2xl text-purple-600"></i>
                  <div>
                    <p class="text-xs font-medium text-purple-600 uppercase">Payment Status</p>
                    <p class="text-lg font-bold text-purple-900">{{ store.item.status }}</p>
                  </div>
                </div>
              </div>
              <div
                class="bg-purple-50 border-l-4 border-purple-500 rounded-lg p-4 shadow hover:shadow-md transition-shadow md:col-span-2"
              >
                <div class="flex items-center gap-3">
                  <i class="fa fa-comment-o text-2xl text-purple-600"></i>
                  <div class="flex-1">
                    <p class="text-xs font-medium text-purple-600 uppercase">Remarks</p>
                    <p class="text-base font-semibold text-purple-900">{{ store.item.remarks }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Payment Summary Tab -->
          <div v-can="'transaction.view_payment_summary'" v-if="activeTab === 'payment'" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <div
                class="bg-green-50 border-l-4 border-green-500 rounded-lg p-4 shadow hover:shadow-md transition-shadow"
              >
                <div class="flex items-center gap-3">
                  <i class="fa fa-money text-2xl text-green-600"></i>
                  <div>
                    <p class="text-xs font-medium text-green-600 uppercase">Total Amount</p>
                    <p class="text-lg font-bold text-green-900">{{ store.item.total_amount }}</p>
                  </div>
                </div>
              </div>
              <div
                class="bg-green-50 border-l-4 border-green-500 rounded-lg p-4 shadow hover:shadow-md transition-shadow"
              >
                <div class="flex items-center gap-3">
                  <i class="fa fa-tags text-2xl text-green-600"></i>
                  <div>
                    <p class="text-xs font-medium text-green-600 uppercase">Discount Amount</p>
                    <p class="text-lg font-bold text-green-900">
                      {{ store.item.discount_amount }}
                    </p>
                  </div>
                </div>
              </div>
              <div
                class="bg-green-50 border-l-4 border-green-500 rounded-lg p-4 shadow hover:shadow-md transition-shadow"
              >
                <div class="flex items-center gap-3">
                  <i class="fa fa-check-circle text-2xl text-green-600"></i>
                  <div>
                    <p class="text-xs font-medium text-green-600 uppercase">Total Paid Amount</p>
                    <p class="text-lg font-bold text-green-900">
                      {{ store.item.total_paid_amount }}
                    </p>
                  </div>
                </div>
              </div>
              <div
                class="bg-green-50 border-l-4 border-green-500 rounded-lg p-4 shadow hover:shadow-md transition-shadow"
              >
                <div class="flex items-center gap-3">
                  <i class="fa fa-check-square-o text-2xl text-green-600"></i>
                  <div>
                    <p class="text-xs font-medium text-green-600 uppercase">Paid Amount</p>
                    <p class="text-lg font-bold text-green-900">{{ store.item.paid_amount }}</p>
                  </div>
                </div>
              </div>
              <div
                class="bg-red-50 border-l-4 border-red-500 rounded-lg p-4 shadow hover:shadow-md transition-shadow"
              >
                <div class="flex items-center gap-3">
                  <i class="fa fa-exclamation-triangle text-2xl text-red-600"></i>
                  <div>
                    <p class="text-xs font-medium text-red-600 uppercase">Due Amount</p>
                    <p class="text-lg font-bold text-red-900">{{ store.item.due_amount }}</p>
                  </div>
                </div>
              </div>
              <div
                class="bg-green-50 border-l-4 border-green-500 rounded-lg p-4 shadow hover:shadow-md transition-shadow"
              >
                <div class="flex items-center gap-3">
                  <i class="fa fa-calendar text-2xl text-green-600"></i>
                  <div>
                    <p class="text-xs font-medium text-green-600 uppercase">Payment Date</p>
                    <p class="text-lg font-bold text-green-900">
                      {{ store.item.payment_date_formatted }}
                    </p>
                  </div>
                </div>
              </div>
              <div
                class="bg-green-50 border-l-4 border-green-500 rounded-lg p-4 shadow hover:shadow-md transition-shadow"
              >
                <div class="flex items-center gap-3">
                  <i class="fa fa-clock-o text-2xl text-green-600"></i>
                  <div>
                    <p class="text-xs font-medium text-green-600 uppercase">Payment Time</p>
                    <p class="text-lg font-bold text-green-900">
                      {{ store.item.payment_time_formatted }}
                    </p>
                  </div>
                </div>
              </div>
              <div
                class="bg-green-50 border-l-4 border-green-500 rounded-lg p-4 shadow hover:shadow-md transition-shadow"
              >
                <div class="flex items-center gap-3">
                  <i class="fa fa-calendar-check-o text-2xl text-green-600"></i>
                  <div>
                    <p class="text-xs font-medium text-green-600 uppercase">Payment Date Time</p>
                    <p class="text-base font-bold text-green-900">
                      {{ store.item.payment_date_time_formatted }}
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Fees Section -->
            <div v-if="store.item.fees && store.item.fees.length > 0" class="mt-8">
              <div class="flex items-center gap-2 mb-4">
                <i class="fa fa-list-alt text-xl text-indigo-600"></i>
                <h3 class="text-lg font-bold text-indigo-700">Fee Breakdown</h3>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div
                  v-for="(feeCategory, index) in store.item.fees"
                  :key="index"
                  class="bg-linear-to-br from-white to-gray-50 rounded-lg border-2 border-l-4 shadow-md hover:shadow-lg transition-shadow duration-300"
                  :class="[
                    index === 0
                      ? 'border-blue-300 border-l-blue-500'
                      : 'border-purple-300 border-l-purple-500',
                  ]"
                >
                  <div
                    class="px-4 py-3 rounded-t-lg flex items-center gap-2"
                    :class="[index === 0 ? 'bg-blue-50' : 'bg-purple-50']"
                  >
                    <i
                      :class="[
                        'fa text-lg',
                        index === 0 ? 'fa-users text-blue-600' : 'fa-plane text-purple-600',
                      ]"
                    ></i>
                    <h4
                      class="text-sm font-bold"
                      :class="[index === 0 ? 'text-blue-700' : 'text-purple-700']"
                    >
                      {{ feeCategory.fee_category }}
                    </h4>
                  </div>
                  <div class="p-4 space-y-3">
                    <div
                      v-for="(item, itemIndex) in feeCategory.items"
                      :key="itemIndex"
                      class="flex justify-between items-center py-2 px-3 rounded-lg bg-white border border-gray-200 hover:border-gray-300 transition-colors"
                    >
                      <div class="flex items-center gap-2">
                        <i
                          :class="[
                            'fa fa-circle text-xs',
                            index === 0 ? 'text-blue-400' : 'text-purple-400',
                          ]"
                        ></i>
                        <span class="text-sm text-gray-700 font-medium">{{ item.fee_name }}</span>
                      </div>
                      <span
                        class="font-bold text-sm px-3 py-1 rounded-full"
                        :class="[
                          index === 0
                            ? 'text-blue-700 bg-blue-100'
                            : 'text-purple-700 bg-purple-100',
                        ]"
                      >
                        {{ item.amount }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Payer Information Tab -->
          <div v-can="'transaction.view_payer_information'" v-if="activeTab === 'payer'" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div
                class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-4 shadow hover:shadow-md transition-shadow"
              >
                <div class="flex items-center gap-3">
                  <i class="fa fa-user-circle text-2xl text-blue-600"></i>
                  <div>
                    <p class="text-xs font-medium text-blue-600 uppercase">Payer Type</p>
                    <p class="text-lg font-bold text-blue-900">{{ store.item.payer }}</p>
                  </div>
                </div>
              </div>
              <div
                class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-4 shadow hover:shadow-md transition-shadow"
              >
                <div class="flex items-center gap-3">
                  <i class="fa fa-user text-2xl text-blue-600"></i>
                  <div>
                    <p class="text-xs font-medium text-blue-600 uppercase">Payer Name</p>
                    <p class="text-lg font-bold text-blue-900">{{ store.item.payer_name }}</p>
                  </div>
                </div>
              </div>
              <div
                class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-4 shadow hover:shadow-md transition-shadow"
              >
                <div class="flex items-center gap-3">
                  <i class="fa fa-mobile text-3xl text-blue-600"></i>
                  <div>
                    <p class="text-xs font-medium text-blue-600 uppercase">Mobile Number</p>
                    <p class="text-lg font-bold text-blue-900">{{ store.item.payer_mobile }}</p>
                  </div>
                </div>
              </div>
              <div
                class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-4 shadow hover:shadow-md transition-shadow"
              >
                <div class="flex items-center gap-3">
                  <i class="fa fa-envelope text-2xl text-blue-600"></i>
                  <div>
                    <p class="text-xs font-medium text-blue-600 uppercase">Email</p>
                    <p class="text-lg font-bold text-blue-900">{{ store.item.payer_email }}</p>
                  </div>
                </div>
              </div>
              <div
                class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-4 shadow hover:shadow-md transition-shadow md:col-span-2"
              >
                <div class="flex items-center gap-3">
                  <i class="fa fa-id-card text-2xl text-blue-600"></i>
                  <div>
                    <p class="text-xs font-medium text-blue-600 uppercase">Application ID</p>
                    <p class="text-lg font-bold text-blue-900">
                      {{ store.item.payer_application_id }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Application Details Tab -->
          <div v-can="'transaction.view_application_details'" v-if="activeTab === 'application'" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div
                class="bg-orange-50 border-l-4 border-orange-500 rounded-lg p-4 shadow hover:shadow-md transition-shadow"
              >
                <div class="flex items-center gap-3">
                  <i class="fa fa-id-badge text-2xl text-orange-600"></i>
                  <div>
                    <p class="text-xs font-medium text-orange-600 uppercase">Application ID</p>
                    <p class="text-lg font-bold text-orange-900">{{ store.item.application_id }}</p>
                  </div>
                </div>
              </div>
              <div
                class="bg-orange-50 border-l-4 border-orange-500 rounded-lg p-4 shadow hover:shadow-md transition-shadow"
              >
                <div class="flex items-center gap-3">
                  <i class="fa fa-briefcase text-2xl text-orange-600"></i>
                  <div>
                    <p class="text-xs font-medium text-orange-600 uppercase">Applied Job</p>
                    <p class="text-lg font-bold text-orange-900">{{ store.item.applied_job }}</p>
                  </div>
                </div>
              </div>
              <div
                class="bg-orange-50 border-l-4 border-orange-500 rounded-lg p-4 shadow hover:shadow-md transition-shadow"
              >
                <div class="flex items-center gap-3">
                  <i class="fa fa-file-text text-2xl text-orange-600"></i>
                  <div>
                    <p class="text-xs font-medium text-orange-600 uppercase">Work Order</p>
                    <p class="text-lg font-bold text-orange-900">{{ store.item.work_order }}</p>
                  </div>
                </div>
              </div>
              <div
                class="bg-orange-50 border-l-4 border-orange-500 rounded-lg p-4 shadow hover:shadow-md transition-shadow"
              >
                <div class="flex items-center gap-3">
                  <i class="fa fa-building text-2xl text-orange-600"></i>
                  <div>
                    <p class="text-xs font-medium text-orange-600 uppercase">Client</p>
                    <p class="text-lg font-bold text-orange-900">{{ store.item.client }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- System Information Tab -->
          <div v-can="'transaction.view_system_informations'" v-if="activeTab === 'system'" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div
                class="bg-gray-50 border-l-4 border-gray-500 rounded-lg p-4 shadow hover:shadow-md transition-shadow"
              >
                <div class="flex items-center gap-3">
                  <i class="fa fa-calendar-plus-o text-2xl text-gray-600"></i>
                  <div>
                    <p class="text-xs font-medium text-gray-600 uppercase">Created At</p>
                    <p class="text-lg font-bold text-gray-900">{{ store.item.created_at }}</p>
                  </div>
                </div>
              </div>
              <div
                class="bg-gray-50 border-l-4 border-gray-500 rounded-lg p-4 shadow hover:shadow-md transition-shadow"
              >
                <div class="flex items-center gap-3">
                  <i class="fa fa-calendar-o text-2xl text-slate-600"></i>
                  <div>
                    <p class="text-xs font-medium text-slate-600 uppercase">Updated At</p>
                    <p class="text-lg font-bold text-slate-900">{{ store.item.updated_at }}</p>
                  </div>
                </div>
              </div>
              <div
                class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-4 shadow hover:shadow-md transition-shadow"
              >
                <div class="flex items-center gap-3">
                  <i class="fa fa-user-plus text-2xl text-blue-600"></i>
                  <div>
                    <p class="text-xs font-medium text-blue-600 uppercase">Created By</p>
                    <p class="text-lg font-bold text-blue-900">{{ store.item.created_by }}</p>
                  </div>
                </div>
              </div>
              <div
                class="bg-gray-50 border-l-4 border-gray-500 rounded-lg p-4 shadow hover:shadow-md transition-shadow"
              >
                <div class="flex items-center gap-3">
                  <i class="fa fa-user-circle-o text-2xl text-gray-600"></i>
                  <div>
                    <p class="text-xs font-medium text-gray-600 uppercase">Updated By</p>
                    <p class="text-lg font-bold text-gray-900">
                      {{ store.item.updated_by || 'N/A' }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Application Transaction History -->
      <h1 class="text-lg font-bold my-4">{{ store.item.bill_no }} Transaction History</h1>
      <BaseTable v-can="'transaction.view_transaction_history'" v-if="!isLoading" :columns="columns" :rows="store.item.bill_transactions" />
    </ViewModalLayout>
  </BaseModal>
</template>

<script setup>
import { ref } from 'vue'
import { useTransactionStore } from '@/modules/application/store/transactionStore'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import ViewModalLayout from '@/shared/components/ui/ViewModalLayout.vue'

const store = useTransactionStore()

// Tab configuration
const activeTab = ref('bill')
const tabs = [
  {
    id: 'bill',
    label: 'Bill Information',
    icon: 'fa-file-text-o',
    activeClass: 'text-purple-700 bg-purple-50 border-purple-500',
    inactiveClass: 'text-purple-600',
  },
  {
    id: 'payment',
    label: 'Payment Summary',
    icon: 'fa-credit-card',
    activeClass: 'text-green-700 bg-green-50 border-green-500',
    inactiveClass: 'text-green-600',
  },
  {
    id: 'payer',
    label: 'Payer Information',
    icon: 'fa-user',
    activeClass: 'text-blue-700 bg-blue-50 border-blue-500',
    inactiveClass: 'text-blue-600',
  },
  {
    id: 'application',
    label: 'Application Details',
    icon: 'fa-briefcase',
    activeClass: 'text-orange-700 bg-orange-50 border-orange-500',
    inactiveClass: 'text-orange-600',
  },
  {
    id: 'system',
    label: 'System Information',
    icon: 'fa-cog',
    activeClass: 'text-gray-700 bg-gray-50 border-gray-500',
    inactiveClass: 'text-gray-600',
  },
]

const { columns } = useCrudTable(store, [
  { key: 'bill_no', label: 'Bill No' },
  { key: 'total_amount', label: 'Total Amount' },
  { key: 'discount_amount', label: 'Discount Amount' },
  { key: 'paid_amount', label: 'Paid Amount' },
  { key: 'due_amount', label: 'Due Amount' },
  { key: 'payment_method', label: 'Payment Method' },
  { key: 'status', label: 'Status' },
  { key: 'payment_date_formatted', label: 'Payment Date' },
  { key: 'payment_time_formatted', label: 'Payment Time' },
  { key: 'remarks', label: 'Remarks' },
])
</script>
