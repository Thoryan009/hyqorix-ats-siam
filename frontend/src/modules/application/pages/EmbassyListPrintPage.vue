<template>
  <div v-if="isLoading" class="p-8 text-center text-sm text-gray-500 print:hidden">
    Loading embassy list...
  </div>

  <div v-else-if="listData" id="embassy-list-print" class="embassy-list-print bg-white text-black">
    <section>
      <!-- <pre>{{ groupedData }}</pre> -->
<!-- <pre>{{ listData }}</pre> -->
 <!-- <pre>{{ cancel_stamping }}</pre> -->
      <table class="w-full mb-8 border-separate border-spacing-y-1">
        <tbody>
          <tr class="text-center">
            <td colspan="4" class="arabic-text font-semibold text-base">بيـان بالجـوازات المقدمة</td>
          </tr>

          <tr class="text-center font-semibold text-base">
            <td>{{listData.company_rl}}</td>
            <td class="arabic-text text-right">رقم:</td>
            <td>{{listData.embassy_company_name}}</td>
            <td class="arabic-text text-right">اسم:</td>
          </tr>

          <tr class="text-center font-semibold text-base">
            <td>&nbsp;</td>
            <td class="text-right">الرخصة</td>
            <td>&nbsp;</td>
            <td class="arabic-text text-right">المكتب</td>
          </tr>

          <tr class="text-center font-semibold text-base">
            <td>{{listData.submit_date_formatted}}</td>
            <td class="text-right">:التاريخ</td>
            <td>&nbsp;</td>
            <td class="arabic-text text-right">:</td>
          </tr>

          <tr class="text-center font-semibold text-base">
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td class="arabic-text text-right">تـــوقـيـــع</td>
          </tr>
        </tbody>
      </table>
    </section>

    <section class="print-section mb-14">
      <table class="report-table">
        <thead>
          <tr>
            <th colspan="3">
              <span class="arabic-text text-xs">المهنة</span> <br />
              <span class="text-xs">Profession</span>
            </th>
            <th colspan="2">
              <span class="arabic-text text-xs">التاريخ</span> <br />
              <span class="text-xs">Year</span>
            </th>
            <th colspan="2">
              <span class="arabic-text text-xs">رقم التأشيرة</span> <br />
              <span class="text-xs">Visa No</span>
            </th>
            <th colspan="4">
              <span class="arabic-text text-xs">اسم الكفيل</span> <br />
              <span class="text-xs">Sponsor Name</span>
            </th>
            <th colspan="2">
              <span class="arabic-text text-xs">رقم الجوازات</span> <br />
              <span class="text-[11px]">Passport No</span>
            </th>
            <th>
              <span class="arabic-text text-xs">ت</span> <br />
              <span class="text-xs">SL.</span>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td colspan="14" class="arabic-text font-semibold text-center text-xs">
              <span>Re-stamping /</span> <span>التجديد</span>
            </td>
          </tr>


          <!-- dekhi  -->
             <tr  v-for="(item, index) in groupedData.restamping" :key="index" class="border-none-row">
            <td colspan="3" class="text-center arabic-text text-xs">{{item.visa_profession_ar }}</td>
            <td colspan="2" class="text-center text-xs">{{ item.date_of_issue }}</td>
            <td colspan="2" class="text-center text-xs">{{item.visa_no}}</td>
            <td colspan="4" class="text-center text-xs">{{ item.visit_work_for_ar }}</td>
            <td colspan="2" class="text-center text-xs">{{item.passport_no}}</td>
            <td  class="text-center  text-xs">{{ index + 1 }}</td>
          </tr>
           <tr>
            <td colspan="14" class="arabic-text font-semibold text-right text-xs">
              <span>المجموعة</span> <span>: {{ listData.restamping }}</span>
            </td>
          </tr>


          <!-- dekhi  -->

          <tr>
            <td colspan="14" class="arabic-text font-semibold text-center text-xs">
              <span>New /</span> <span>جديد</span>
            </td>
          </tr>

          <tr  v-for="(item, index) in groupedData.new_stamping" :key="index" class="border-none-row">
            <td colspan="3" class="text-center arabic-text text-xs">{{item.visa_profession_ar }}</td>
            <td colspan="2" class="text-center text-xs">{{ item.date_of_issue  }}</td>
            <td colspan="2" class="text-center text-xs">{{item.visa_no}}</td>
            <td colspan="4" class="text-center text-xs">{{ item.visit_work_for_ar }}</td>
            <td colspan="2" class="text-center text-xs">{{item.passport_no}}</td>
            <td  class="text-center  text-xs">{{ index + 1 }}</td>
          </tr>


        </tbody>
        <tfoot>
          <tr>
            <td colspan="14" class="arabic-text font-semibold text-right text-xs">
              <span>المجموعة</span> <span>: {{ listData.new_stamping }}</span>
            </td>
          </tr>
          <tr>
            <td colspan="14" class="arabic-text font-semibold text-center text-xs">
              <span>Cancellation /</span> <span>الغأء</span>
            </td>
          </tr>
           <tr  v-for="(item, index) in groupedData.cancellation" :key="index" class="border-none-row">
            <td colspan="3" class="text-center arabic-text text-xs">{{item.visa_profession_ar }}</td>
            <td colspan="2" class="text-center text-xs">{{ item.date_of_issue }}</td>
            <td colspan="2" class="text-center text-xs">{{item.visa_no}}</td>
            <td colspan="4" class="text-center text-xs">{{ item.visit_work_for_ar }}</td>
            <td colspan="2" class="text-center text-xs">{{item.passport_no}}</td>
            <td  class="text-center  text-xs">{{ index + 1 }}</td>
          </tr>

              <tr>
            <td colspan="14" class="arabic-text font-semibold text-right text-xs">
              <span>المجموعة</span> <span>: {{ listData.cancel_stamping }}</span>
            </td>
          </tr>

        </tfoot>
      </table>
    </section>

    <section class="print-section mb-120">
      <table class="w-full">
        <tbody>
          <tr>
            <td class="text-center text-sm font-semibold space-y-1">
              <p>:الختم</p>
              <p>:التعبئة</p>
              <p>:التسجيل</p>
            </td>
            <td class="text-center text-sm font-semibold space-y-1">
              <p>:المستلم</p>
              <p>:المدقق</p>
              <p>:المسئول</p>
            </td>
          </tr>
        </tbody>
      </table>
    </section>

     <!-- 2nd page -->
    <section>
      <div class="text-center mt-6 mb-10">
        <h1 class="text-2xl font-semibold">{{listData.embassy_company_name}} - RL{{listData.company_rl}}</h1>
        <h2 class="text-lg">Embassy List - {{listData.submit_date_formatted}}</h2>
      </div>
    </section>
    <section class="print-section mb-14">
      <table class="report-table">
        <thead>
          <tr>
            <th>
              <span class="text-xs">SL.</span>
            </th>
            <th colspan="2">
              <span class="text-xs">Agent Name</span>
            </th>

            <th colspan="4">
              <span class="text-xs"> Name</span>
            </th>
            <th colspan="2">
              <span class="text-[11px]">Passport No</span>
            </th>
            <th colspan="2">
              <span class="text-xs">Visa No</span>
            </th>
            <th colspan="3">
              <span class="text-xs">Profession</span>
            </th>
          </tr>
        </thead>
        <tbody>

          <tr>
            <td colspan="14" class="arabic-text font-semibold text-center text-xs">
              <span>Re-stamping /</span> <span>التجديد</span>
            </td>
          </tr>
          <tr v-for="(item, index) in groupedData.restamping" :key="index" class="border-none-row">
            <td class="text-center text-xs">{{ index + 1 }}</td>
            <td colspan="2" class="text-center text-xs">N/A</td>
            <td colspan="4" class="text-center text-xs">{{item.given_name}} {{ item.sur_name }}</td>
            <td colspan="2" class="text-center text-xs">{{item.passport_no}}</td>
            <td colspan="2" class="text-center text-xs">{{item.visa_no}}</td>
            <td colspan="3" class="text-center arabic-text text-xs">{{item.visa_profession_ar}}</td>
          </tr>
           <tr>
            <td colspan="14" class="arabic-text font-semibold text-right text-xs">
              <span>المجموعة</span> <span>: {{ listData.restamping }}</span>
            </td>
          </tr>

          <tr>
            <td colspan="14" class="arabic-text font-semibold text-center text-xs">
              <span>New /</span> <span>جديد</span>
            </td>
          </tr>
          <tr v-for="(item, index) in groupedData.new_stamping" :key="index" class="border-none-row">
            <td class="text-center text-xs">{{ index + 1 }}</td>
            <td colspan="2" class="text-center text-xs">N/A</td>
            <td colspan="4" class="text-center text-xs">{{item.given_name}} {{ item.sur_name }}</td>
            <td colspan="2" class="text-center text-xs">{{item.passport_no}}</td>
            <td colspan="2" class="text-center text-xs">{{item.visa_no}}</td>
            <td colspan="3" class="text-center arabic-text text-xs">{{item.visa_profession_ar}}</td>
          </tr>
           <tr>
            <td colspan="14" class="arabic-text font-semibold text-right text-xs">
              <span>المجموعة</span> <span>:  {{ listData.new_stamping }}</span>
            </td>
          </tr>


        </tbody>
        <tfoot>

          <tr>
            <td colspan="14" class="arabic-text font-semibold text-center text-xs">
              <span>Cancellation /</span> <span>الغأء</span>
            </td>
          </tr>
           <tr v-for="(item, index) in groupedData.cancellation" :key="index" class="border-none-row">
           <td class="text-center text-xs">{{ index + 1 }}</td>
            <td colspan="2" class="text-center text-xs">N/A</td>
            <td colspan="4" class="text-center text-xs">{{item.given_name}} {{ item.sur_name }}</td>
            <td colspan="2" class="text-center text-xs">{{item.passport_no}}</td>
            <td colspan="2" class="text-center text-xs">{{item.visa_no}}</td>
            <td colspan="3" class="text-center arabic-text text-xs">{{item.visa_profession_ar}}</td>
          </tr>
           <tr>
            <td colspan="14" class="arabic-text font-semibold text-right text-xs">
              <span>المجموعة</span> <span>: {{ listData.cancel_stamping }}</span>
            </td>
          </tr>
        </tfoot>
      </table>
    </section>

    <div class="mt-4 flex justify-end print:hidden">
      <BaseButton class="bg-slate-800 text-white hover:bg-slate-900" @click="handlePrint">
        <i class="fa fa-print mr-1"></i> Print
      </BaseButton>
    </div>
  </div>

  <div v-else class="p-8 text-center text-sm text-red-600 print:hidden">Embassy list not found.</div>
</template>

<script setup>
import { computed, nextTick, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useEmbassyListQuery } from '../queries/useEmbassyListsQuery'
import { printWithOrientation } from '@/shared/utils/printOrientation'

const route = useRoute()
const listId = computed(() => route.params.id)

const { data, isLoading } = useEmbassyListQuery(listId)

const listData = computed(() => data.value?.data?.data)

const items = computed(() => listData.value?.items ?? [])

const groupedData = computed(() => {
  const groups = {
    restamping: [],
    new_stamping: [],
    cancellation: [],
  }

  items.value.forEach((item) => {
    if (groups[item.list_type]) {
      groups[item.list_type].push(item)
    }
  })

  return groups
})

let hasAutoPrinted = false

const handlePrint = () => {
  printWithOrientation('portrait', '9mm', 'embassy-list-print')
}

watch(
  listData,
  async (record) => {
    if (!record) return

    document.title = `Embassy List - ${record.submit_date_formatted ?? record.submit_date ?? ''}`

    if (hasAutoPrinted) return

    await nextTick()
    hasAutoPrinted = true
    setTimeout(() => {
      printWithOrientation('portrait', '9mm', 'embassy-list-print')
    }, 600)
  },
  { immediate: true }
)
</script>

<style scoped>
.embassy-list-print {
  max-width: 210mm;
  margin: 0 auto;
  padding: 8mm;
  font-family: Arial, Helvetica, sans-serif;
  font-size: 11px;
  line-height: 1.25;
}

.arabic-text {
  font-family: 'Traditional Arabic', 'Arial', sans-serif;
  direction: rtl;
}

.top-header {
  display: grid;
  grid-template-columns: 1fr 1.4fr 1fr;
  gap: 12px;
  margin-bottom: 10px;
  align-items: start;
}

.report-table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed;
}

.report-table th,
.report-table td {
  border: 1px solid #000;
  padding: 4px 5px;
  vertical-align: middle;
  word-break: break-word;
}

.report-table th {
  text-align: center;
  font-size: 10px;
  font-weight: 700;
}

.sponsor-cell {
  font-size: 10px;
}

.text-right {
  text-align: right;
}

.print-section + .print-section {
  margin-top: 0;
}

.border-none-row td {
  border-top: none;
  border-bottom: none;
}
</style>

<style>
@media print {
  #embassy-list-print .overflow-x-auto {
    overflow: visible;
  }

  #embassy-list-print table,
  #embassy-list-print th,
  #embassy-list-print td,
  #embassy-list-print h1,
  #embassy-list-print h2,
  #embassy-list-print p,
  #embassy-list-print span {
    color: #000 !important;
  }
}
</style>
