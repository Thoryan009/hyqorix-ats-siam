<template>
  <div id="embassy-report-print">
    <!-- Print Button -->
    <div class="flex justify-end p-4 no-print">
      <BaseButton v-can="'visa_processing.print_report'" @click="goToPrint">
        <i class="fa fa-print"></i> Print Mofa PDF
      </BaseButton>
    </div>

    <!-- page - 1 -->
    <div class="max-w-200 mx-auto text-black p-1 text-[10px] merriweather">
      <!-- Top label part -->
      <div class="grid grid-cols-3 mt-3 gap-1 mb-2">
        <!-- Photo Box -->
        <div
          class="w-30 h-34 border border-black flex items-center justify-center ml-2 overflow-hidden"
        >
          <img
            v-if="applicantPhoto"
            :src="applicantPhoto"
            alt="Applicant Photo"
            class="w-full h-full object-cover"
          />
          <span v-else class="text-[12px] font-semibold">Photo</span>
        </div>
        <!-- Center - Barcode and Title -->
        <div class="flex-1 text-center mt-1">
          <div class="flex justify-center">
            <canvas ref="barcodeCanvas" class="w-36 h-14 object-contain"></canvas>
          </div>
          <div class="font-bold text-[14px] -mt-3">{{ info.visa_no }}</div>
          <div class="text-[15px] font-bold mt-3">New Application</div>
        </div>
        <!-- Form Number -->
        <div class="text-right mt-3">
          <div class="text-3xl font-bold mb-2.5">{{ info.mofa_no }}</div>
          <div class="text-[16px] font-semibold -mb-0.5">EMBASSY OF SAUDI ARABIA</div>
          <div class="text-[16px] font-semibold">CONSULAR SECTION</div>
        </div>
      </div>

      <!-- Main Form Container -->
      <table class="w-full border-collapse border border-black text-black">
        <tbody>
          <!-- Row 1: Full Name -->
          <tr>
            <td
              class="border font-semibold border-black text-[12px] px-2 py-[0.2px] w-[45%]"
              colspan="3"
            >Full Name:</td>
            <td
              class="border text-[13px] border-black px-1 py-[0.2px] text-center font-bold"
              colspan="6"
            >{{ info.full_name_with_father_name }}</td>
            <td
              class="border border-black px-2 py-[0.2px] text-black text-right text-[12px] w-[30%] arabic-text"
              colspan="3"
            >: اسم الكامل</td>
          </tr>

          <!-- Row 2: Mother's Name -->
          <tr>
            <td
              class="border font-semibold border-black px-2 py-[0.2px] text-[12px]"
              colspan="3"
            >Mother's Name:</td>
            <td
              class="border text-[13px] border-black px-1 py-[0.2px] text-center font-bold"
              colspan="6"
            >{{ info.mother_name }}</td>
            <td
              class="border border-black px-2 py-[0.2px] text-black text-right text-sm arabic-text"
              colspan="3"
            >: اسم الأم</td>
          </tr>

          <!-- Row 3: Date of Birth & Place of Birth -->
          <tr>
            <td
              class="border font-semibold border-black px-2 py-[0.2px] text-[12px]"
              colspan="3"
            >Date of Birth:</td>
            <td
              class="border border-black px-1 py-[0.2px] text-[14px] text-center font-bold"
              colspan="1"
            >{{ formatDMY(info.date_of_birth) }}</td>
            <td
              class="border border-black px-2 py-[0.2px] text-right text-sm arabic-text"
              colspan="2"
            >: تاريخ الولادة</td>

            <td
              class="border font-semibold border-black px-2 py-[0.2px] text-[12px]"
              colspan="2"
            >Place of Birth:</td>
            <td
              class="border border-black px-1 py-[0.2px] text-center font-bold text-[13px]"
              colspan="1"
            >{{ info.place_of_birth }}</td>
            <td
              class="border border-black px-2 py-[0.2px] text-right text-sm arabic-text"
              colspan="3"
            >: محل الولادة</td>
          </tr>

          <!-- Row 4: Previous Nationality & Present Nationality -->
          <tr>
            <td
              class="border font-semibold border-black pl-2 py-[0.2px] w-[30%] text-[11.6px]"
              colspan="3"
            >Previous Nationality:</td>
            <td
              class="border border-black px-1 py-[0.1px] text-center font-bold text-[13px]"
              colspan="1"
            >{{ info.present_nationality }}</td>
            <td
              class="border border-black px-2 py-[0.1px] text-black text-right text-sm arabic-text"
              colspan="2"
            >: الجنسية السابقة</td>

            <td
              class="border font-semibold border-black px-2 py-[0.1px] text-[11.6px]"
              colspan="2"
            >Present Nationality:</td>
            <td
              class="border border-black px-1 py-[0.1px] text-center font-bold text-[13px]"
              colspan="1"
            >{{ info.present_nationality }}</td>
            <td
              class="border border-black px-2 py-[0.1px] text-black text-right text-sm arabic-text"
              colspan="3"
            >: الجنسية الحالية</td>
          </tr>

          <!-- Row 5: Sex & Marital Status -->
          <tr>
            <td
              class="border font-semibold border-black px-2 py-[0.1px] text-[12px]"
              colspan="3"
            >Sex:</td>
            <td
              class="border border-black px-1 py-[0.1px] text-center font-bold text-[13px]"
              colspan="1"
            >{{ info.sex }}</td>
            <td
              class="border border-black px-2 py-[0.1px] text-black font-me text-right text-sm arabic-text"
              colspan="2"
            >: الجنس</td>

            <td
              class="border font-semibold border-black px-2 py-[0.1px] text-[12px]"
              colspan="2"
            >Marital Status:</td>
            <td
              class="border border-black px-1 py-[0.1px] text-center font-bold text-[13px]"
              colspan="1"
            >{{ info.marital_status ?? 'N/A' }}</td>
            <td
              class="border border-black px-2 py-[0.1px] text-black text-right text-sm arabic-text"
              colspan="3"
            >: الحالة الاجتماعية</td>
          </tr>

          <!-- Row 6: Sect & Religion -->
          <tr>
            <td
              class="border font-semibold border-black px-2 py-[0.1px] text-[12px]"
              colspan="3"
            >Sect:</td>
            <td
              class="border border-black px-1 py-[0.1px] text-center font-semibold text-[13px]"
              colspan="1"
            ></td>
            <td
              class="border border-black px-2 py-[0.1px] text-black text-right text-sm arabic-text"
              colspan="2"
            >: المذهب</td>

            <td
              class="border font-semibold border-black px-2 py-[0.1px] text-[12px]"
              colspan="2"
            >Religion:</td>
            <td
              class="border border-black px-1 py-[0.1px] text-center font-bold text-[13px]"
              colspan="1"
            >{{ info.religion }}</td>
            <td
              class="border border-black px-2 py-[0.1px] text-black text-right text-sm arabic-text"
              colspan="3"
            >: الديانة</td>
          </tr>

          <!-- Row 7: Profession Row -->
          <tr>
            <td
              class="border border-black px-1 py-[0.1px] text-black text-right arabic-text"
              colspan="12"
            >
              <div class="grid grid-cols-6 justify-between">
                <p class></p>
                <p class="text-start text-sm">: مصدره</p>
                <p class="text-center text-sm">: المؤهل العلمي</p>
                <p class="font-bold col-span-2 text-end text-[13px]">{{ info.profession_arabic }}</p>
                <p class="me-1 text-sm">: المهنة</p>
              </div>
            </td>
          </tr>

          <tr>
            <td class="border border-black px-1 py-[0.1px] text-right text-[12px]" colspan="12">
              <div class="flex font-semibold justify-between">
                <p class="px-1">Place of Issue:</p>
                <p>Qualification:</p>
                <p>Profession:</p>
                <p class="font-bold text-[13px] mr-8">{{ info.profession }}</p>
              </div>
            </td>
          </tr>

          <tr>
            <td class="border font-semibold border-black px-1 py-[0.1px] text-[12px]" colspan="4">
              <p class="px-1">Home address & phone No.:</p>
            </td>
            <td class="border border-black px-1 py-[0.1px] text-[12px]" colspan="4">
              <p></p>
            </td>
            <td
              class="border border-black px-1 py-[0.1px] text-black text-right arabic-text"
              colspan="4"
            >
              <p>: عنوان المنزل ورقم التلفون</p>
            </td>
          </tr>

          <tr>
            <td class="border font-semibold border-black px-1 py-[0.1px] text-[12px]" colspan="4">
              <p class="px-1">Business address & phone No.:</p>
            </td>
            <td
              class="text-[13px] border border-black px-1 py-[0.1px] text-center font-bold"
              colspan="4"
            >
              <p>{{ info.embassy_company_name }} RL: {{ info.company_rl }}</p>
            </td>
            <td
              class="border border-black px-1 py-[0.1px] text-black text-right arabic-text"
              colspan="4"
            >
              <p>: رقم التلفون(المؤسسة) عنوان الشركة</p>
            </td>
          </tr>

          <tr>
            <td
              class="text-[13px] border border-black px-2 py-[0.1px] font-bold text-center"
              colspan="12"
            >{{ info.company_address }}</td>
          </tr>

          <tr rowspan="2">
            <td class="border font-semibold border-black px-1 py-[0.1px] text-[12px]" colspan="3">
              <p class="mb-5 px-1">Purpose of Travel:</p>
            </td>
            <td class="border border-black px-1 py-1 text-[11px]" colspan="6">
              <div class="flex font-medium justify-center gap-2">
                <div :class="travelClass('work')">
                  <p :class="travelTextClass('work')">
                    الشغل
                    <br />
                    <span class="merriweather font-semibold">Work</span>
                  </p>
                </div>
                <div :class="travelClass('transit')">
                  <p :class="travelTextClass('transit')">
                    عبور
                    <br />
                    <span class="merriweather font-semibold">Transit</span>
                  </p>
                </div>
                <div :class="travelClass('visit')">
                  <p :class="travelTextClass('visit')">
                    يزور
                    <br />
                    <span class="merriweather font-semibold">Visit</span>
                  </p>
                </div>
                <div :class="travelClass('umrah')">
                  <p :class="travelTextClass('umrah')">
                    العمرة
                    <br />
                    <span class="merriweather font-semibold">Umrah</span>
                  </p>
                </div>
                <div :class="travelClass('residence')">
                  <p :class="travelTextClass('residence')">
                    إقامة
                    <br />
                    <span class="merriweather font-semibold">Residence</span>
                  </p>
                </div>
                <div :class="travelClass('hajj')">
                  <p :class="travelTextClass('hajj')">
                    الحج
                    <br />
                    <span class="merriweather font-semibold">Hajj</span>
                  </p>
                </div>
                <div :class="travelClass('diplomacy')">
                  <p :class="travelTextClass('diplomacy')">
                    الدبلوماسية
                    <br />
                    <span class="merriweather font-semibold">Diplomacy</span>
                  </p>
                </div>
              </div>
            </td>
            <td
              class="border border-black px-1 text-end py-[0.1px] text-black arabic-text"
              colspan="3"
            >
              <p class="mb-5 px-1">: الغابة من السفر</p>
            </td>
          </tr>

          <tr>
            <td class="border border-black px-1 py-[0.2px] text-[12px] arabic-text" colspan="3">
              <p class="text-center">
                محلا االصدار
                <br />
                <span class="text-black font-semibold merriweather">Place of issue:</span>
              </p>
            </td>
            <td class="border border-black px-1 py-[0.2px] text-[12px] arabic-text" colspan="3">
              <p class="text-center">
                تاريخ االصدار
                <br />
                <span class="text-black font-semibold merriweather">Date of issue:</span>
              </p>
            </td>
            <td class="border border-black px-1 py-[0.2px] text-[12px] arabic-text" colspan="3">
              <p class="text-center">
                تاريخ انتهاء الصالحية
                <br />
                <span class="text-black font-semibold merriweather">Date of expiry:</span>
              </p>
            </td>
            <td class="border border-black px-1 py-[0.2px] text-[12px] arabic-text" colspan="3">
              <p class="text-center">
                رقم الجواز
                <br />
                <span class="text-black font-semibold merriweather">Passport No.:</span>
              </p>
            </td>
          </tr>

          <tr>
            <td
              class="text-center border text-[13px] border-black px-1 py-[0.3px] font-bold"
              colspan="3"
            >
              <p class="text-[13px]">DHAKA</p>
            </td>
            <td
              class="text-center border text-[13px] border-black px-1 py-[0.3px] font-bold"
              colspan="3"
            >
              <p class="text-[13px]">{{ formatDMY(info.passport_date_of_issue) }}</p>
            </td>
            <td
              class="text-center border text-[13px] border-black px-1 py-[0.3px] font-bold"
              colspan="3"
            >
              <p class="text-[13px]">{{ formatDMY(info.passport_date_of_expiry) }}</p>
            </td>
            <td
              class="text-center border text-[13px] border-black px-1 py-[0.3px] font-bold"
              colspan="3"
            >
              <p class="text-[13px]">{{ info.passport_no }}</p>
            </td>
          </tr>

          <tr>
            <td class="border border-black px-1 py-[0.2px] text-center text-[12px]" colspan="12">
              <div class="flex justify-between">
                <p class="px-2">
                  <span class="text-black arabic-text">: مدة االقامة بالمملكة</span>
                  <br />
                  <span
                    class="text-black font-semibold merriweather"
                  >Duration of stay in the kingdom:</span>
                </p>
                <p>
                  <span></span>
                  <br />
                  <span class="text-black font-semibold merriweather">02 Years</span>
                </p>
                <p>
                  <span class="text-black arabic-text">: تاريخ الوصول</span>
                  <br />
                  <span class="text-black font-semibold merriweather">Date of arrival:</span>
                </p>
                <p class="text-end">
                  <span class="text-black arabic-text text-end pr-5">: تاريخ المغادرة</span>
                  <br />
                  <span class="text-black font-semibold merriweather pr-15">Date of departure:</span>
                </p>
              </div>
            </td>
          </tr>

          <tr>
            <td class="border border-black px-1 py-[0.2px] text-center" colspan="12">
              <div class="flex justify-between text-black arabic-text">
                <p class="px-2">تاريخ :</p>
                <p>( ) ايصال رقم :</p>
                <p>تاريخ :</p>
                <p>رقم بشيك الدفع</p>
                <p>طريقة الدفع : () مجانا () نقدا () بشيك رقم</p>
              </div>
            </td>
          </tr>

          <tr>
            <td
              class="border font-semibold border-black px-1 py-[0.2px] text-[12px] text-center"
              colspan="12"
            >
              <div class="grid grid-cols-12">
                <p class="col-span-2 mr-3">Mode of payment:</p>
                <div class="flex ml-10 gap-3.5 col-span-4">
                  <p>Free</p>
                  <p>Cash</p>
                  <p>Cheque No.</p>
                </div>
                <p class="ml-20">Date</p>
                <p class="ml-30">No.</p>
                <p class="col-span-4 ml-20">Date</p>
              </div>
            </td>
          </tr>

          <tr>
            <td class="border border-black px-1 py-[0.2px] text-center text-[12px]" colspan="12">
              <div class="flex justify-between">
                <p class="px-2">
                  <span></span>
                  <br />
                  <span class="text-black font-semibold">Relationship:</span>
                </p>
                <p>
                  <span class="text-black text-[11px] arabic-text">: صلته</span>
                  <br />
                  <span class="font-bold text-[13px]">EMPLOYER AND EMPLOYEE</span>
                </p>
                <p></p>
                <p class="text-black arabic-text">: اسم المحرم</p>
                <p></p>
              </div>
            </td>
          </tr>

          <tr>
            <td class="border border-black px-1 py-[0.2px] text-[12px] text-center" colspan="12">
              <div class="flex justify-between">
                <p class="px-2 font-semibold">Destination:</p>
                <p></p>
                <p class="text-black arabic-text">: جهة الوصول</p>
                <p class="font-semibold">Carrier's:</p>
                <p></p>
                <p class="text-black me-1 arabic-text">: اسم الشركة الناقلة</p>
              </div>
            </td>
          </tr>
          <tr>
            <td class="border border-black px-1 py-[0.2px] text-[12px] text-center" colspan="12">
              <span class="font-semibold">Dependents traveling in the same passport</span>
              <span class="ml-16 mr-2">:</span>
              <span
                class="text-black arabic-text"
              >ايضاحات تخص افراد العائلة ( المضافين) علي نفس جواز السفر</span>
            </td>
          </tr>

          <tr>
            <td class="border border-black px-1 py-[0.2px]" colspan="3">
              <div class="flex font-bold flex-col items-center">
                <span class="text-black">نوع الصلة</span>
                <span class="text-[12px]">Relationship</span>
              </div>
            </td>
            <td class="border border-black px-1 py-[0.2px]" colspan="3">
              <div class="flex font-bold flex-col items-center">
                <span class="text-black">تاريخ الميالد</span>
                <span class="text-[12px]">Date of Birth</span>
              </div>
            </td>
            <td class="border border-black px-1 py-[0.2px]" colspan="2">
              <div class="flex font-bold flex-col items-center">
                <span class="text-black">الجنس</span>
                <span class="text-[12px]">Sex</span>
              </div>
            </td>
            <td class="border border-black px-1 py-[0.2px]" colspan="4">
              <div class="flex font-bold flex-col items-center">
                <span class="text-black">الاسم بالكامل</span>
                <span class="text-[12px]">Full Name</span>
              </div>
            </td>
          </tr>

          <tr>
            <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="3">
              <p></p>
            </td>
            <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="3">
              <p class="text-center font-bold text-[12px]">CITY: RIYADH, K.S.A</p>
            </td>
            <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="2">
              <p></p>
            </td>
            <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="4">
              <p></p>
            </td>
          </tr>
          <tr>
            <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="3">
              <p></p>
            </td>
            <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="3">
              <p class="text-center font-bold text-[12px]">TEL:</p>
            </td>
            <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="2">
              <p></p>
            </td>
            <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="4">
              <p></p>
            </td>
          </tr>

          <tr>
            <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="3">
              <p></p>
            </td>
            <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="3">
              <p>&nbsp;</p>
            </td>
            <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="2">
              <p></p>
            </td>
            <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="4">
              <p></p>
            </td>
          </tr>

          <tr>
            <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="3">
              <p></p>
            </td>
            <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="3">
              <p>&nbsp;</p>
            </td>
            <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="2">
              <p></p>
            </td>
            <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="4">
              <p></p>
            </td>
          </tr>

          <tr>
            <td class="border border-black px-1 py-[0.2px] text-[12px]" colspan="12">
              <p class="ml-12">
                <span class="font-semibold">Name and address of company or individual in the kingdom</span>
                <span class="ml-10 mr-2">:</span>
                <span
                  class="text-black mr-1 arabic-text"
                >اسم و عنوان الشركة أو اسم الشخص و عنوانه بالمملكة</span>
              </p>
            </td>
          </tr>
          <tr>
            <td class="border border-black px-1 py-[0.2px] font-semibold text-center" colspan="12">
              <p>&nbsp;</p>
            </td>
          </tr>
          <tr>
            <td class="border border-black px-1 py-[0.2px] text-[12px] text-center" colspan="12">
              <div class="flex flex-row items-center gap-2">
                <p class="text-right font-semibold">
                  I the undersigned hereby that all the information I have provided are correct. I
                  will abide by laws of the kingdom during the period of my residence in it.
                </p>
                <p class="text-black text-right arabic-text">
                  أنا الموقع أدناه أقربان كل المعلومات التي درنتها صحيحة و ساكون ملنزما بقرانين
                  المملكة اثناء فترة وجودي بها
                </p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Bottom Label -->

      <div
        class="grid grid-cols-12 mt-1 text-[12px] border-dashed border-b border-black pb-2 px-1 pl-3"
      >
        <div class="col-span-2">
          <p class="font-semibold">Date:</p>
        </div>
        <div class="col-span-3">
          <p>
            <span class="text-black">: التاريخ</span>
            <span class="ml-2 font-semibold">Signature:</span>
          </p>
        </div>
        <div class="col-span-5">
          <p>
            <span class="text-black">: التوقيع</span>
            <span class="ml-2 font-semibold">Name:</span>
            <span class="ml-5 text-[13px] font-bold">{{ info.applicant_name }}</span>
          </p>
        </div>
        <div class="col-span-2">
          <p class="text-end text-black">: الاسم</p>
        </div>
      </div>
      <div class="flex justify-between text-[12px] px-1 pl-3">
        <p class="underline mt-1 font-semibold">For official use only</p>
        <p class="underline text-black text-[11px]">لالستعمال الرسمي فقط</p>
      </div>

      <div class="grid grid-cols-12 border-b border-black pb-0.5 px-1 pl-3">
        <div class="text-[12px] col-span-3">
          <p>
            <span class="font-semibold">Date:</span>
            <span class="ml-8 font-bold text-[14px]">{{ info.visa_issue_date }}</span>
          </p>
        </div>

        <div class="text-[12px] col-span-3">
          <p>
            <span class="text-black arabic-text">: التاريخ</span>
            <span class="ml-2 font-semibold">Visa No:</span>
          </p>
        </div>
        <div class="text-[12px] col-span-2">
          <p class="font-bold text-[14px]">{{ info.visa_no }}</p>
        </div>
        <div class="text-[11px] col-span-4">
          <p class="text-end text-black">: رقم االمر المعتمد عليه في أعطاء التاشيرة</p>
        </div>
      </div>

      <div class="grid grid-cols-3 border-b border-black pb-0.1 pt-[0.2px] text-[12px] px-1 pl-3">
        <div>
          <p class="font-semibold">Visit/Work for:</p>
        </div>

        <div>
          <p
            class="text-black text-center font-semibold arabic-text"
          >{{ info.visit_work_for_arabic }}</p>
        </div>
        <div>
          <p class="text-end text-black arabic-text">: لزيارة</p>
        </div>
      </div>

      <div class="grid grid-cols-12 border-b border-black pb-0.1 mt-0 text-[12px] px-1 pl-3">
        <div class="col-span-5">
          <p class="font-semibold">Date:</p>
        </div>

        <div class="col-span-7 grid grid-cols-3">
          <div>
            <p class="text-black text-end">
              <span class="text-black arabic-text">: التاريخ</span>
              <span class="ml-2 font-semibold">Authorization:</span>
            </p>
          </div>

          <div>
            <p class="font-bold text-end text-[14px]">{{ info.authorization }}</p>
          </div>
          <div>
            <p class="text-end text-black arabic-text">: أشربرقم</p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-2 border-b border-black pb-0.1 pt-[0.2px] text-[12px] px-1 pl-3">
        <div class="col-span-1 flex justify-between">
          <div>
            <p class="font-semibold">Fee Collected:</p>
          </div>

          <div>
            <p>
              <span class="text-black arabic-text">: المبلغ المحصل</span>
              <span class="ml-2 font-semibold">Type:</span>
            </p>
          </div>
        </div>

        <div class="col-span-1 grid grid-cols-3">
          <p></p>
          <div>
            <p class="flex gap-2">
              <span class="text-black arabic-text">: نوعها</span>
              <span class="ml-2 font-semibold">Duration:</span>
            </p>
          </div>
          <div>
            <p class="text-end text-black arabic-text">: مدتها</p>
          </div>
        </div>
      </div>

      <div class="flex justify-between mt-1 text-[12px] px-1 pl-3">
        <div>
          <p class="border-t mt-1 arabic-text">رئيس القسم القنصلي</p>
          <p class="mt-0 font-semibold">Head of consular section</p>
        </div>
        <div class="mt-1">
          <canvas ref="passportBarcodeCanvas" class="w-36 h-14 mx-auto object-contain"></canvas>
          <p class="text-center font-bold text-[14px] -mt-3">{{ info.passport_no }}</p>
        </div>

        <div>
          <p class="border-t mt-1 text-black">مدقق البيانات رقم صاحب العمل</p>
          <p class="mt-0 font-semibold text-end">Checked by</p>
        </div>
      </div>
    </div>

    <!-- page - 2 -->
    <div class="max-w-200 mx-auto flex justify-center items-start text-black merriweather">
      <div class="w-full p-10 leading-relaxed mt-44">
        <!-- Header Address -->
        <div class="mb-8">
          <p class="m-0 mb-2 text-base font-medium">To,</p>
          <p class="m-0 my-1 text-sm font-medium">The Chief Of Consular Section,</p>
          <p class="m-0 my-1 text-sm font-medium">{{ info.embassy_name }},</p>
          <p class="m-0 my-1 text-sm font-medium">{{ info.embassy_company_address }}.</p>
        </div>

        <!-- Greeting and Introduction -->
        <div class="mb-8">
          <p class="m-0 mb-4 text-sm font-medium">Excellency,</p>
          <p class="m-0 text-sm leading-5 text-justify font-medium">
            With Due Respect we are Submitting One Passport for work Visa with all Necessary
            Documents and Particulars mentioned as below, knowing all instruction and regulation of
            the consulate section.
          </p>
        </div>

        <!-- Employee Details Table -->
        <div class="my-10 py-5">
          <div class="flex mb-4 text-xs border-b border-gray-300 pb-2">
            <div class="min-w-56 font-semibold shrink-0">NAME OF COMPANY :</div>
            <div
              class="font-bold flex-1 pl-25 text-left arabic-text"
            >{{ info.visit_work_for_arabic }}</div>
          </div>
          <div class="flex mb-4 text-xs border-b border-gray-300 pb-2">
            <div class="min-w-56 font-semibold shrink-0">VISA NUMBER & DATE :</div>
            <div
              class="font-bold flex-1 pl-25 text-left"
            >{{ info.visa_no }} Date: {{ info.visa_issue_date }}</div>
          </div>
          <div class="flex mb-4 text-xs border-b border-gray-300 pb-2">
            <div class="min-w-56 font-semibold shrink-0">FULL NAME OF THE EMPLOYEE :</div>
            <div class="font-bold flex-1 pl-25 text-left">{{ info.applicant_name }}</div>
          </div>
          <div class="flex mb-4 text-xs border-b border-gray-300 pb-2">
            <div class="min-w-56 font-semibold shrink-0">PASSPORT NO. WITH ISSUE DATE :</div>
            <div class="font-bold flex-1 pl-25 text-left flex gap-4">
              <span>{{ info.passport_no }}</span>
              <span>Date: {{ formatDMY(info.passport_date_of_issue) }}</span>
            </div>
          </div>
          <div class="flex mb-4 text-xs border-b border-gray-300 pb-2">
            <div class="min-w-56 font-semibold shrink-0">PROFESSION :</div>
            <div class="font-bold flex-1 pl-25 text-left">{{ info.profession }}</div>
          </div>
          <div class="flex mb-4 text-xs border-b border-gray-300 pb-2">
            <div class="min-w-56 font-semibold shrink-0">RELIGION :</div>
            <div class="font-bold flex-1 pl-25 text-left">{{ info.religion }}</div>
          </div>
        </div>

        <!-- Declaration Text -->
        <div class="my-10">
          <p class="m-0 text-sm leading-5 font-medium text-justify text-black">
            I do hereby confirm and declare that the region stated in the Visa form and forwarding
            letter is fully correct. I also undertake with my own responsibility to cancel the Visa
            and to stop functioning with my office, If the statement is found incorrect.
          </p>
        </div>

        <!-- Request Text -->
        <div class="my-8">
          <p class="m-0 text-sm leading-5 font-medium text-justify text-black">
            We therefore, Request your Excellency to kindly issue work Visa out of - 01 - Visas and
            oblige thereby.
          </p>
        </div>

        <!-- Signature Section -->
        <div class="mt-16">
          <p
            class="m-0 text-sm font-medium border-t border-black w-3/12 text-center leading-4"
          >Your Faithfully</p>
          <div class="h-10"></div>
        </div>
      </div>
    </div>

    <!-- page - 3 -->
    <div class="max-w-200 mx-auto flex justify-center m items-start text-black merriweather">
      <div class="w-full p-10 leading-relaxed">
        <!-- Title -->
        <div class="text-center mb-12">
          <h1
            class="m-0 text-lg font-bold border-b border-gray-400 inline-block px-4"
          >EMPLOYMENT AGREEMENT</h1>
        </div>

        <!-- Employee Details Section -->
        <div class="my-8">
          <div class="flex mb-3 text-xs border-b border-gray-300 pb-2">
            <div class="font-semibold min-w-56 shrink-0">NAME OF COMPANY:</div>
            <div
              class="flex-1 font-bold pl-5 text-left arabic-text"
            >{{ info.visit_work_for_arabic }}</div>
          </div>
          <div class="flex mb-3 text-xs border-b border-gray-300 pb-2">
            <div class="font-semibold min-w-56 shrink-0">HEREBY APPOINTED:</div>
            <div class="flex-1 pl-5 font-bold text-left">{{ info.applicant_name }}</div>
          </div>
          <div class="flex mb-3 text-xs border-b border-gray-300 pb-2">
            <div class="font-semibold min-w-56 shrink-0">PASSPORT NO WITH ISSUE DATE</div>
            <div class="flex-1 pl-5 font-bold text-left flex gap-4">
              <span>{{ info.passport_no }}</span>
              <span>Date: {{ formatDMY(info.passport_date_of_issue) }}</span>
            </div>
          </div>
          <div class="flex mb-3 text-xs border-b border-gray-300 pb-2">
            <div class="font-semibold min-w-56 shrink-0">PASSPORT HOLDER:</div>
            <div class="flex-1 pl-5 font-bold text-left">Bangladesh</div>
          </div>
          <div class="flex mb-3 text-xs border-b border-gray-300 pb-2">
            <div class="font-semibold min-w-56 shrink-0">PROFESSION:</div>
            <div class="flex-1 pl-5 font-bold text-left">{{ info.profession }}</div>
          </div>
        </div>

        <!-- Terms and Conditions Title -->
        <div class="text-center my-8">
          <h2
            class="m-0 text-lg font-bold px-3 border-b border-gray-400 inline-block"
          >UNDER THE FOLLOWING TERMS AND CONDITIONS:</h2>
        </div>

        <!-- Terms List -->
        <div class="my-6">
          <div class="grid gap-0 text-xs">
            <!-- Row 1 -->
            <div class="grid grid-cols-12 border-b border-gray-300 py-2">
              <div class="flex gap-4 col-span-8 font-semibold text-left">
                <p>1</p>
                <p>MONTHLY SALARY:</p>
              </div>
              <div class="col-span-4 text-left pl-5">800/= SR</div>
            </div>
            <!-- Row 2 -->
            <div class="grid grid-cols-12 border-b border-gray-300 py-2">
              <div class="flex gap-4 col-span-8 font-semibold text-left">
                <p>2</p>
                <p>FOOD AND ACCOMMODATION:</p>
              </div>
              <div class="col-span-4 text-left pl-5">200/= SR</div>
            </div>
            <!-- Row 3 -->
            <div class="grid grid-cols-12 border-b border-gray-300 py-2">
              <div class="flex gap-4 col-span-8 font-semibold text-left">
                <p>3</p>
                <p>AIR PASSAGE:</p>
              </div>
              <div class="col-span-4 text-left pl-5">BORNE BY THE EMPLOYER</div>
            </div>
            <!-- Row 4 -->
            <div class="grid grid-cols-12 border-b border-gray-300 py-2">
              <div class="flex gap-4 col-span-8 font-semibold text-left">
                <p>4</p>
                <p>DUTY HOUR:</p>
              </div>
              <div class="col-span-4 text-left pl-5">8 HOURS DAILY</div>
            </div>
            <!-- Row 5 -->
            <div class="grid grid-cols-12 border-b border-gray-300 py-2">
              <div class="flex gap-4 col-span-8 font-semibold text-left">
                <p>5</p>
                <p>HOLIDAY:</p>
              </div>
              <div class="col-span-4 text-left pl-5">AS PER SAUDI LABOUR LAWS</div>
            </div>
            <!-- Row 6 -->
            <div class="grid grid-cols-12 border-b border-gray-300 py-2">
              <div class="flex gap-4 col-span-8 font-semibold text-left">
                <p>6</p>
                <p>LEAVE:</p>
              </div>
              <div class="col-span-4 text-left pl-5">AS PER SAUDI LABOUR LAWS</div>
            </div>
            <!-- Row 7 -->
            <div class="grid grid-cols-12 border-b border-gray-300 py-2">
              <div class="flex gap-4 col-span-8 font-semibold text-left">
                <p>7</p>
                <p>OVERTIME & OTHER BENEFIT:</p>
              </div>
              <div class="col-span-4 text-left pl-5">AS PER SAUDI LABOUR LAWS</div>
            </div>
            <!-- Row 8 -->
            <div class="grid grid-cols-12 border-b border-gray-300 py-2">
              <div class="flex gap-4 col-span-8 font-semibold text-left">
                <p>8</p>
                <p>MEDICAL FACILITIES:</p>
              </div>
              <div class="col-span-4 text-left pl-5">FREE</div>
            </div>
            <!-- Row 9 -->
            <div class="grid grid-cols-12 border-b border-gray-300 py-2">
              <div class="flex gap-4 col-span-8 font-semibold text-left">
                <p>9</p>
                <p>PERIOD OF CONTRACT:</p>
              </div>
              <div class="col-span-4 text-left pl-5">TWO/ONE YEARS</div>
            </div>
            <!-- Row 10 -->
            <div class="grid grid-cols-12 border-b border-gray-300 py-2">
              <div class="flex gap-4 col-span-8 font-semibold text-left">
                <p>10</p>
                <p>
                  REPATRIATION ARRANGEMENT INCLUDING RETURN OF DEAD BODY & SERVICE BENEFIT TO THE
                  LEGAL HEIR OF THE EMPLOYEE:
                </p>
              </div>
              <div class="col-span-4 text-left pl-5">AS PER SAUDI LABOUR LAWS</div>
            </div>
          </div>
        </div>

        <!-- Signature Section -->
        <div class="mt-54 flex justify-between">
          <div class="text-center">
            <p
              class="text-xs font-bold border-t border-black inline-block px-5 pt-1"
            >SIGNATURE OF FIRST PARTY</p>
          </div>
          <div class="text-center">
            <p
              class="text-xs font-bold border-t border-black inline-block px-5 pt-1"
            >SIGNATURE OF SECOND PARTY</p>
          </div>
        </div>
      </div>
    </div>

    <!-- page - 4 -->
    <div class="max-w-200 mx-auto flex justify-center items-start text-black merriweather">
      <div class="w-full p-10 leading-relaxed">
        <!-- Title -->
        <div class="text-center mb-6">
          <h1
            class="m-0 text-lg font-bold border-b border-black inline-block px-3 pb-1 arabic-text"
          >إرفاق الجدول التالي في كل معاملة</h1>
        </div>

        <!-- Table -->
        <div class="mb-8">
          <table class="w-full border border-black">
            <!-- Table Header -->
            <thead>
              <tr>
                <td class="border border-black p-3 text-center text-xs font-medium w-2/12">
                  <p class="m-0 arabic-text">المالحظات</p>
                  <p class="m-0 merriweather">Notes</p>
                </td>
                <td class="border border-black p-3 text-center text-xs font-medium w-2/12">
                  <p class="m-0 arabic-text">المنفذ</p>
                  <p class="m-0 merriweather">Port</p>
                </td>
                <td class="border border-black p-3 text-center text-xs font-medium">
                  <p class="m-0 arabic-text">المكتب</p>
                  <p class="m-0 merriweather">Agency</p>
                </td>
                <td class="border border-black p-3 text-center text-xs font-medium">
                  <p class="m-0 arabic-text">االجراء</p>
                  <p class="m-0 merriweather">Step</p>
                </td>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="border border-black p-2 text-center text-xs"></td>
                <td class="border border-black p-2 text-center text-xs"></td>
                <td class="border border-black p-2 text-center text-xs">{{ info.mofa_no }}</td>
                <td class="border border-black p-2 text-right text-xs arabic-text">
                  <span class="merriweather">Application Number</span> / رقم إنجاز
                </td>
              </tr>
              <tr>
                <td class="border border-black p-2 text-center text-xs"></td>
                <td class="border border-black p-2 text-center text-xs"></td>
                <td class="border border-black p-2 text-center text-xs">{{ info.visa_no }}</td>
                <td class="border border-black p-2 text-right text-xs arabic-text">
                  <span class="merriweather">Visa No.</span> / رقم المستند
                </td>
              </tr>
              <tr>
                <td class="border border-black p-2 text-center text-xs"></td>
                <td class="border border-black p-2 text-center text-xs"></td>
                <td class="border border-black p-2 text-center text-xs">{{ info.applicant_name }}</td>
                <td class="border border-black p-2 text-right text-xs arabic-text">
                  <span class="merriweather">Passport Holder Name</span> / االسما في لجواز
                </td>
              </tr>
              <tr>
                <td class="border border-black p-2 text-center text-xs"></td>
                <td class="border border-black p-2 text-center text-xs"></td>
                <td class="border border-black p-2 text-center text-xs">{{ info.passport_no }}</td>
                <td class="border border-black p-2 text-right text-xs arabic-text">
                  <span class="merriweather">Passport Number</span> / رقم الجواز
                </td>
              </tr>
              <tr>
                <td class="border border-black p-2 text-center text-xs"></td>
                <td class="border border-black p-2 text-center text-xs"></td>
                <td
                  class="border border-black p-2 text-center text-xs"
                >{{ formatPassportExpiry(info.passport_date_of_expiry) }}</td>
                <td class="border border-black p-2 text-right text-xs arabic-text">
                  <span class="merriweather">Passport Validity</span> / صلاحية الجواز
                </td>
              </tr>
              <tr>
                <td class="border border-black p-2 text-center text-xs"></td>
                <td class="border border-black p-2 text-center text-xs"></td>
                <td class="border border-black p-2 text-center text-xs">
                  {{ formatDMY(info.date_of_birth) }}
                  <br />
                  {{ info.calculateAge }}
                </td>
                <td class="border border-black p-2 text-right text-xs arabic-text">
                  <span class="merriweather">Age</span>/ العمر
                </td>
              </tr>
              <tr>
                <td class="border border-black p-2 text-center text-xs"></td>
                <td class="border border-black p-2 text-center text-xs"></td>
                <td class="border border-black p-2 text-center text-xs">{{ info.sex }}</td>
                <td class="border border-black p-2 text-right text-xs arabic-text">
                  <span class="merriweather">Sex</span>/ الجنس
                </td>
              </tr>
              <tr>
                <td class="border border-black p-2 text-center text-xs"></td>
                <td class="border border-black p-2 text-center text-xs"></td>
                <td class="border border-black p-2 text-center text-xs">N/A</td>
                <td class="border border-black p-2 text-right text-xs arabic-text">
                  <span class="merriweather">Musaned</span>/ مسند
                </td>
              </tr>
              <tr>
                <td class="border border-black p-2 text-center text-xs"></td>
                <td class="border border-black p-2 text-center text-xs"></td>
                <td class="border border-black p-2 text-center text-xs">{{ info.alwakala_no }}</td>
                <td class="border border-black p-2 text-right text-xs arabic-text">
                  <span class="merriweather">Alwakala</span>/ الوكالة
                </td>
              </tr>
              <tr>
                <td class="border border-black p-2 text-center text-xs"></td>
                <td class="border border-black p-2 text-center text-xs"></td>
                <td class="border border-black p-2 text-center text-xs">{{ info.medical_status }}</td>
                <td class="border border-black p-2 text-right text-xs arabic-text">
                  <span class="merriweather">Medical Report</span>/ فحص طبي
                </td>
              </tr>
              <tr>
                <td class="border border-black p-2 text-center text-xs"></td>
                <td class="border border-black p-2 text-center text-xs"></td>
                <td
                  class="border border-black p-2 text-center text-xs"
                >{{ info.police_clearance_no }}</td>
                <td class="border border-black p-2 text-right text-xs arabic-text">
                  <span class="merriweather">Police Clearance</span>/ ورقة الشرطة
                </td>
              </tr>
              <tr>
                <td class="border border-black p-2 text-center text-xs"></td>
                <td class="border border-black p-2 text-center text-xs"></td>
                <td class="border border-black p-2 text-center text-xs">N/A</td>
                <td class="border border-black p-2 text-right text-xs arabic-text">
                  <span class="merriweather">License</span>/ الرخصة
                </td>
              </tr>
              <tr>
                <td class="border border-black p-2 text-center text-xs"></td>
                <td class="border border-black p-2 text-center text-xs"></td>
                <td
                  class="border border-black p-2 text-center text-xs arabic-text"
                >{{ info.profession_arabic }}</td>
                <td class="border border-black p-2 text-right text-xs arabic-text">
                  <span class="merriweather">Profession</span>/ المهنة
                </td>
              </tr>
              <tr>
                <td class="border border-black p-2 text-center text-xs"></td>
                <td class="border border-black p-2 text-center text-xs"></td>
                <td class="border border-black p-2 text-center text-xs">N/A</td>
                <td class="border border-black p-2 text-right text-xs arabic-text">
                  <span class="merriweather">Experience Certificate</span>/ المؤهل وشهادة الخبرة
                </td>
              </tr>
              <tr>
                <td class="border border-black p-2 text-center text-xs"></td>
                <td class="border border-black p-2 text-center text-xs"></td>
                <td class="border border-black p-2 text-center text-xs">Yes</td>
                <td class="border border-black p-2 text-right text-xs arabic-text">
                  <span class="merriweather">Fingerprint</span>/ البصمة
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Merchant Overseas Section -->
        <div class="text-right mb-12">
          <p class="m-0 text-base font-semibold arabic-text">
            <span class="merriweather">{{ info.embassy_company_name }}</span> - أسم المكتب
          </p>
          <p class="m-0 text-base font-semibold arabic-text">
            <span class="merriweather">{{ info.company_rl }}</span> - رقم الرخصة
          </p>
        </div>

        <!-- Signature and Stamp Section -->
        <div class="text-right space-y-5">
          <p class="m-0 text-base font-semibold arabic-text">- التوقيع</p>
          <div class="h-20"></div>
          <p class="m-0 text-base font-semibold arabic-text">- الختم</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useMofaInformationsQuery } from '../queries/useApplicationsQuery'

import { nextTick, onMounted, watch, ref } from 'vue'
import { generateBarcode } from '@/shared/barcode'
import { printWithOrientation } from '@/shared/utils/printOrientation'

const props = defineProps({
  autoPrint: {
    type: Boolean,
    default: false,
  },
})

const route = useRoute()
const router = useRouter()
const applicationId = computed(() => route.params.applicationId)

const { data, isLoading } = useMofaInformationsQuery(applicationId)

const info = computed(() => data.value?.data?.data?.[0] ?? {})

const applicantPhoto = computed(() => info.value.worker_image ?? '')

const formatDMY = (dateStr) => {
  if (!dateStr) return ''
  const [y, m, d] = dateStr.split('-')
  return `${d}-${m}-${y}`
}

const formatPassportExpiry = (dateStr) => {
  if (!dateStr) return ''
  const months = [
    'Jan',
    'Feb',
    'Mar',
    'Apr',
    'May',
    'Jun',
    'Jul',
    'Aug',
    'Sep',
    'Oct',
    'Nov',
    'Dec',
  ]
  const [y, m, d] = dateStr.split('-')
  return `${d}-${months[parseInt(m, 10) - 1]}-${y}`
}



const travelClass = (option) => {
  const purpose = (info.value.purpose_of_travel ?? '').toLowerCase()
  const match = option.toLowerCase()
  return purpose === match ? 'border bg-gray-700 px-3 py-[0.1px]' : 'border px-3 py-[0.1px]'
}

const travelTextClass = (option) => {
  const purpose = (info.value.purpose_of_travel ?? '').toLowerCase()
  const match = option.toLowerCase()
  return purpose === match ? 'text-white arabic-text' : 'text-black arabic-text'
}

const sanitizeForFileName = (value = '') => {
  return value
    .toString()
    .trim()
    .replace(/\s+/g, '_')
    .replace(/[^a-zA-Z0-9_]/g, '')
}

const getMofaPdfFileName = () => {
  const applicantName = sanitizeForFileName(
    info.value.applicant_name || ''
  )
  const passportNo = sanitizeForFileName(info.value.passport_no || '')
  const fileName = [applicantName, passportNo].filter(Boolean).join('_')

  return fileName || 'mofa_report'
}

const goToPrint = () => {
  const routeData = router.resolve({
    name: 'Embassy Report Print',
    params: {
      applicationId: applicationId.value,
    },
    query: {
      fileName: getMofaPdfFileName(),
    },
  })

  window.open(routeData.href, '_blank')
}

// Barcode rendering
const barcodeCanvas = ref(null)
const passportBarcodeCanvas = ref(null)
onMounted(() => {
  if (barcodeCanvas.value && info.value.visa_no) {
    generateBarcode(info.value.visa_no, barcodeCanvas.value)
  }

  if (passportBarcodeCanvas.value && info.value.passport_no) {
    generateBarcode(info.value.passport_no, passportBarcodeCanvas.value)
  }
})
watch(
  () => info.value.visa_no,
  (newVal) => {
    if (barcodeCanvas.value && newVal) {
      generateBarcode(newVal, barcodeCanvas.value)
    }
  }
)
watch(
  () => info.value.passport_no,
  (newVal) => {
    if (passportBarcodeCanvas.value && newVal) {
      generateBarcode(newVal, passportBarcodeCanvas.value)
    }
  }
)

let hasAutoPrinted = false

watch(
  () => info.value,
  async (record) => {
    if (!props.autoPrint || hasAutoPrinted || isLoading.value) return
    if (!record?.visa_no && !record?.passport_no && !record?.applicant_name) return

    const requestedFileName =
      typeof route.query.fileName === 'string' ? route.query.fileName : ''
    document.title = sanitizeForFileName(requestedFileName) || 'mofa_report'

    await nextTick()
    hasAutoPrinted = true
    setTimeout(() => {
      printWithOrientation('portrait', '9mm 9mm 6mm 9mm', 'embassy-report-print')
    }, 800)
  },
  { immediate: true, deep: true }
)
</script>

<style scoped>
@font-face {
  font-family: 'OPTITimes-Roman';
  src: url('/font/TimesNewRoman.ttf') format('truetype');
  font-weight: normal;
  font-style: normal;
}

.arabic-text {
  font-family: 'OPTITimes-Roman', sans-serif;
}

@media print {
  * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .no-print {
    display: none !important;
  }

  .max-w-4xl {
    max-width: 100%;
    margin: 10px;
    padding: 0;
  }
}
</style>
