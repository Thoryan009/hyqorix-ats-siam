<template>
  <div class="max-w-[800px] mx-auto bg-white text-black p-1 text-[10px] merriweather">
    <!-- Top label part -->
    <div class="grid grid-cols-3 mt-3 gap-1 mb-3">
      <!-- Photo Box -->
      <div
        class="w-32 h-36 border border-black flex items-center justify-center ml-2 overflow-hidden"
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
      <div class="flex-1 text-center mt-3">
        <div class="flex justify-center">
          <img src="/barcode.png" alt="Barcode" class="w-36 h-8 object-contain" />
        </div>
        <div class="font-bold">130496049467</div>
        <div class="text-[13px] font-bold mt-5">New Application</div>
      </div>
      <!-- Form Number -->
      <div class="text-right mt-3">
        <div class="text-3xl font-[700] mb-2.5">E816528085</div>
        <div class="text-[16px] font-semibold -mb-0.5">EMBASSY OF SAUDI ARABIA</div>
        <div class="text-[16px] font-semibold">CONSULAR SECTION</div>
      </div>
    </div>

    <!-- Main Form Container -->
    <table class="w-full border-collapse border border-black text-black">
      <tbody>
        <!-- Row 1: Full Name -->
        <tr>
          <td class="border border-black text-[12px] px-1 py-[0.2px]  w-1/6" colspan="2">Full Name:</td>
          <td
            class="border text-[13px] border-black px-1 py-[0.2px] text-center font-bold"
            colspan="8"
          >MUJAMMEL MIA S/O. FALO MIA</td>
          <td
            class="border border-black px-1 py-[0.2px] text-gray-700 text-right font-semibold w-1/6 text-[12px]"
            colspan="2"
          >: اسم الكامل</td>
        </tr>

        <!-- Row 2: Mother's Name -->
        <tr>
          <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="2">Mother's Name:</td>
          <td
            class="border text-[11px] border-black px-1 py-[0.1px] text-center font-bold"
            colspan="8"
          >MOMENA KHATUN</td>
          <td
            class="border border-black px-1 py-[0.1px] text-gray-700 text-right font-semibold"
            colspan="2"
          >: اسم الأم</td>
        </tr>

        <!-- Row 3: Date of Birth & Place of Birth -->
        <tr>
          <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="2">Date of Birth:</td>
          <td
            class="border border-black px-1 py-[0.1px] text-center font-bold"
            colspan="2"
          >01-08-2003</td>
          <td
            class="border border-black px-1 py-[0.1px] text-gray-700 text-right font-semibold"
            colspan="2"
          >: تاريخ الولادة</td>
          <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="2">Place of Birth:</td>
          <td
            class="border border-black px-1 py-[0.1px] text-center font-bold"
            colspan="2"
          >KISHOREGANJ</td>
          <td
            class="border border-black px-1 py-[0.1px] text-gray-700 text-right font-semibold"
            colspan="2"
          >: محل الولادة</td>
        </tr>

        <!-- Row 4: Previous Nationality & Present Nationality -->
        <tr>
          <td
            class="border border-black px-1 py-[0.1px] font-semibold"
            colspan="2"
          >Previous Nationality:</td>
          <td
            class="border border-black px-1 py-[0.1px] text-center font-bold"
            colspan="2"
          >BANGLADESH</td>
          <td
            class="border border-black px-1 py-[0.1px] text-gray-700 text-right font-semibold"
            colspan="2"
          >: الجنسية السابقة</td>
          <td
            class="border border-black px-1 py-[0.1px] font-semibold"
            colspan="2"
          >Present Nationality:</td>
          <td
            class="border border-black px-1 py-[0.1px] text-center font-bold"
            colspan="2"
          >BANGLADESH</td>
          <td
            class="border border-black px-1 py-[0.1px] text-gray-700 text-right font-semibold"
            colspan="2"
          >: الجنسية الحالية</td>
        </tr>

        <!-- Row 5: Sex & Marital Status -->
        <tr>
          <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="2">Sex:</td>
          <td class="border border-black px-1 py-[0.1px] text-center font-bold" colspan="2">Male</td>
          <td
            class="border border-black px-1 py-[0.1px] text-gray-700 text-right font-semibold"
            colspan="2"
          >: الجنس</td>
          <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="2">Marital Status:</td>
          <td
            class="border border-black px-1 py-[0.1px] text-center font-bold"
            colspan="2"
          >Unmarried</td>
          <td
            class="border border-black px-1 py-[0.1px] text-gray-700 text-right font-semibold"
            colspan="2"
          >: الحالة الاجتماعية</td>
        </tr>

        <!-- Row 6: Sect & Religion -->
        <tr>
          <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="2">Sect:</td>
          <td class="border border-black px-1 py-[0.1px] text-center font-semibold" colspan="2"></td>
          <td
            class="border border-black px-1 py-[0.1px] text-gray-700 text-right font-semibold"
            colspan="2"
          >: المذهب</td>
          <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="2">Religion:</td>
          <td class="border border-black px-1 py-[0.1px] text-center font-bold" colspan="2">Muslim</td>
          <td
            class="border border-black px-1 py-[0.1px] text-gray-700 text-right font-semibold"
            colspan="2"
          >: الديانة</td>
        </tr>

        <!-- Row 7: Profession Row -->
        <tr>
          <td
            class="border border-black px-1 py-[0.1px] text-gray-700 text-right font-semibold"
            colspan="12"
          >
            <div class="grid grid-cols-6 justify-between">
              <p class></p>
              <p class="text-start">: هرﺪﺼﻣ</p>
              <p class="text-center">: هرﺪﺼﻣ</p>
              <p class="font-bold col-span-2 text-end text-[11px]">تﺂﺸﻨﻣو ﺐﺗﺎﻜﻣ ﻒﯿﻈﻨﺗ ﻞﻣﺎﻋ</p>
              <p>: هرﺪﺼﻣ</p>
            </div>
          </td>
        </tr>

        <tr>
          <td class="border border-black px-1 py-[0.1px] text-right font-semibold" colspan="12">
            <div class="flex justify-between">
              <p>Place of Issue:</p>
              <p>Qualification:</p>
              <p>Profession:</p>
              <p class="font-bold text-[11px] mr-8">Offices and facilities Cleaning worker</p>
            </div>
          </td>
        </tr>

        <tr>
          <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="4">
            <p>Home address & phone No.:</p>
          </td>
          <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="4">
            <p></p>
          </td>
          <td
            class="border border-black px-1 py-[0.1px] text-gray-700 text-right font-semibold"
            colspan="4"
          >
            <p>: نﻮﻔﻠﺘﻟا ﻢﻗرو لﺰﻨﻤﻟا ناﻮﻨﻋ</p>
          </td>
        </tr>

        <tr>
          <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="4">
            <p>Business address & phone No.:</p>
          </td>
          <td
            class="text-[15px] border border-black px-1 py-[0.1px] text-center font-semibold"
            colspan="4"
          >
            <p>MERCHANT OVERSEAS RL: 1160</p>
          </td>
          <td
            class="border border-black px-1 py-[0.1px] text-gray-700 text-right font-semibold"
            colspan="4"
          >
            <p>: نﻮﻔﻠﺘﻟا ﻢﻗر ( ﺔﺴﺳﺆﻤﻟا ) ﺔﻛﺮﺸﻟا ناﻮﻨﻋ</p>
          </td>
        </tr>

        <tr>
          <td
            class="text-[12px] border border-black px-2 py-[0.1px] font-semibold text-center"
            colspan="12"
          >PLOT NO-23,ROAD NO-11,BLOCK-F,A-10,BIR UTTAM KHADEMUL BASHAR SARAWAK,BANANI,DHAKA.</td>
        </tr>

        <tr rowspan="2">
          <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="3">
            <p class="mb-5">Purpose of Travel:</p>
          </td>
          <td class="border border-black px-1 py-1 font-semibold" colspan="6">
            <div class="flex justify-center gap-2">
              <div class="border bg-gray-700 px-3 py-[0.1px]">
                <p class="text-white">
                  ﻞﻐﺸﻟا
                  <br />work
                </p>
              </div>
              <div class="border px-3 py-[0.1px]">
                <p class="text-gray-700">
                  ﻞﻐﺸﻟا
                  <br />Transit
                </p>
              </div>
              <div class="border px-3 py-[0.1px]">
                <p class="text-gray-700">
                  ﻞﻐﺸﻟا
                  <br />Visit
                </p>
              </div>
              <div class="border px-3 py-[0.1px]">
                <p class="text-gray-700">
                  ﻞﻐﺸﻟا
                  <br />Umrah
                </p>
              </div>
              <div class="border px-3 py-[0.1px]">
                <p class="text-gray-700">
                  ﻞﻐﺸﻟا
                  <br />Residence
                </p>
              </div>
              <div class="border px-3 py-[0.1px]">
                <p class="text-gray-700">
                  ﺞﺤﻟا
                  <br />Hajj
                </p>
              </div>
              <div class="border px-3 py-[0.1px]">
                <p class="text-gray-700">
                  ﺔﯿﺳﺎﻣﻮﻠﺑﺪﻟا
                  <br />Diplomacy
                </p>
              </div>
            </div>
          </td>
          <td
            class="border border-black px-1 text-end py-[0.1px] text-gray-700 font-semibold"
            colspan="3"
          >
            <p class="mb-5">: ﺮﻔﺴﻟا ﻦﻣ ﺔﺑﺎﻐﻟا</p>
          </td>
        </tr>

        <tr>
          <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="3">
            <p class="text-center">
              Place of issue:
              <span class="text-gray-700">راﺪﺻﻻا ﻞﺤﻣ</span>
            </p>
          </td>
          <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="3">
            <p class="text-center">
              Date of issue:
              <span class="text-gray-700">راﺪﺻﻻا ﺦﯾرﺎﺗ</span>
            </p>
          </td>
          <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="3">
            <p class="text-center">
              Date of expiry:
              <span class="text-gray-700">ﺔﯿﺣﻼﺼﻟا ءﺎﮭﺘﻧا ﺦﯾرﺎﺗ</span>
            </p>
          </td>
          <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="3">
            <p class="text-center">
              Passport No.:
              <span class="text-gray-700">زاﻮﺠﻟا ﻢﻗر</span>
            </p>
          </td>
        </tr>
        <tr>
          <td
            class="text-center border text-[11px] border-black px-1 py-[0.1px] font-bold"
            colspan="3"
          >
            <p>DHAKA</p>
          </td>
          <td
            class="text-center border text-[11px] border-black px-1 py-[0.1px] font-bold"
            colspan="3"
          >
            <p>13-01-2025</p>
          </td>
          <td
            class="text-center border text-[11px] border-black px-1 py-[0.1px] font-bold"
            colspan="3"
          >
            <p>12-01-2035</p>
          </td>
          <td
            class="text-center border text-[11px] border-black px-1 py-[0.1px] font-bold"
            colspan="3"
          >
            <p>A17516124</p>
          </td>
        </tr>

        <tr>
          <td class="border border-black px-1 py-[0.1px] font-semibold text-center" colspan="12">
            <div class="flex justify-between">
              <p>
                <span class="text-gray-700">: ﺔﻜﻠﻤﻤﻟﺎﺑ ﺔﻣﺎﻗﻻا ةﺪﻣ : لﻮﺻﻮﻟا ﺦﯾرﺎﺗ : ةردﺎﻐﻤﻟا ﺦﯾرﺎﺗ</span>
                <br />Duration of stay in the kingdom:
              </p>
              <p>
                <span></span>
                <br />02 Years
              </p>
              <p>
                <span class="text-gray-700">: لﻮﺻﻮﻟا ﺦﯾرﺎﺗ</span>
                <br />Date of arrival:
              </p>
              <p>
                <span class="text-gray-700">: ةردﺎﻐﻤﻟا ﺦﯾرﺎﺗ</span>
                <br />Date fo departure:
              </p>
            </div>
          </td>
        </tr>
        <tr>
          <td class="border border-black px-1 py-[0.3px] font-semibold text-center" colspan="12">
            <div class="flex justify-between text-gray-700">
              <p>ﺦﯾرﺎﺗ :</p>
              <p>( ) ﻢﻗر لﺎﺼﯾا :</p>
              <p>ﺦﯾرﺎﺗ :</p>
              <p>ﻢﻗر ﻚﯿﺸﺑ :</p>
              <p>ﻢﻗر ﻚﯿﺸﺑ ( ) مﺪﻘﻧ ( ) ﺎﻧﺎﺠﻣ ( ) : ﻊﻓﺪﻟا ﺔﻘﯾﺮط :</p>
            </div>
          </td>
        </tr>
        <tr>
          <td class="border border-black px-1 py-[0.1px] font-semibold text-center" colspan="12">
            <div class="grid grid-cols-8 justify-between">
              <p class="col-span-1">Mode of payment:</p>
              <div class="flex ml-10 gap-3.5">
                <p>Free</p>
                <p>Cash</p>
              </div>
              <p class="col-span-2">Cheque No.</p>
              <p class="ml-8">Date</p>
              <p>No.</p>
              <p class="col-span-2">Date</p>
            </div>
          </td>
        </tr>
        <tr>
          <td class="border border-black px-1 py-[0.1px] font-semibold text-center" colspan="12">
            <div class="flex justify-between">
              <p>
                <span></span>
                <br />Relationship:
              </p>
              <p>
                <span class="text-gray-700 text-[11px] font-bold">: ﮫﺘﻠﺻ</span>
                <br />EMPLOYER AND
                EMPLOYEE
              </p>
              <p></p>
              <p class="text-gray-700">: مﺮﺤﻤﻟا ﻢﺳا</p>
              <p></p>
            </div>
          </td>
        </tr>
        <tr>
          <td class="border border-black px-1 py-[0.1px] font-semibold text-center" colspan="12">
            <div class="flex justify-between">
              <p>Destination:</p>
              <p></p>
              <p class="text-gray-700">: لﻮﺻﻮﻟا ﺔﮭﺟ</p>
              <p>Carrier's:</p>
              <p></p>
              <p class="text-gray-700">: ﺔﻠﻓﺎﻨﻟا ﺔﻛﺮﺸﻟا ﻢﺳا</p>
            </div>
          </td>
        </tr>
        <tr>
          <td class="border border-black px-1 py-[0.1px] font-semibold text-center" colspan="12">
            Dependents traveling in the same passport
            <span class="ml-16 mr-2">:</span>
            <span class="text-gray-700">ﺮﻔﺴﻟا زاﻮﺟ ﺲﻔﻧ ﻲﻠﻋ ( ﻦﯿﻓﺎﻀﻤﻟا ) ﺔﻠﺋﺎﻌﻟا داﺮﻓا ﺺﺨﺗ تﺎﺣﺎﻀﯾا</span>
          </td>
        </tr>

        <tr>
          <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="3">
            <div class="flex flex-col items-center">
              <span class="text-gray-700">ﺔﻠﺼﻟا عﻮﻧ</span>
              <span class="font-bold">Relationship</span>
            </div>
          </td>
          <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="3">
            <div class="flex flex-col items-center">
              <span class="text-gray-700">دﻼﯿﻤﻟا ﺦﯾرﺎﺗ</span>
              <span class="font-bold">Date of Birth</span>
            </div>
          </td>
          <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="3">
            <div class="flex flex-col items-center">
              <span class="text-gray-700">ﺲﻨﺠﻟا</span>
              <span class="font-bold">Sex</span>
            </div>
          </td>
          <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="3">
            <div class="flex flex-col items-center">
              <span class="text-gray-700">ﻞﻣﺎﻜﻟﺎﺑ ﻢﺳﻻا</span>
              <span class="font-bold">Full Name</span>
            </div>
          </td>
        </tr>

        <tr>
          <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="3">
            <p></p>
          </td>
          <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="3">
            <p class="text-center font-bold">CITY: RIYADH, K.S.A</p>
          </td>
          <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="3">
            <p></p>
          </td>
          <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="3">
            <p></p>
          </td>
        </tr>
        <tr>
          <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="3">
            <p></p>
          </td>
          <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="3">
            <p class="text-center font-bold">TEL:</p>
          </td>
          <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="3">
            <p></p>
          </td>
          <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="3">
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
          <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="3">
            <p></p>
          </td>
          <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="3">
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
          <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="3">
            <p></p>
          </td>
          <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="3">
            <p></p>
          </td>
        </tr>

        <tr>
          <td class="border border-black px-1 py-[0.1px] font-semibold" colspan="12">
            <p class="ml-12">
              Name and address of company or individual in the kingdom
              <span class="ml-10 mr-2">:</span>
              <span class="text-gray-700">ﺔﻜﻠﻤﻤﻟﺎﺑ ﮫﻧاﻮﻨﻋ و ﺺﺨﺸﻟا ﻢﺳا وأ ﺔﻛﺮﺸﻟا ناﻮﻨﻋ و ﻢﺳا</span>
            </p>
          </td>
        </tr>
        <tr>
          <td class="border border-black px-1 py-[0.1px] font-semibold text-center" colspan="12">
            <p>&nbsp;</p>
          </td>
        </tr>
        <tr>
          <td
            class="border text-[11px] border-black px-1 py-[0.1px] font-semibold text-center"
            colspan="12"
          >
            <div class="flex flex-row items-center gap-2">
              <p class="text-right">
                I the undersigned hereby that all the information I have provided are correct. I
                will abide by laws of the kingdom during the period of my residence in it.
              </p>
              <p class="text-gray-700 text-right">
                و ﺔﺤﯿﺤﺻ ﺎﮭﺘﻧرد ﻲﺘﻟا تﺎﻣﻮﻠﻌﻤﻟا ﻞﻛ نﺎﺑﺮﻗأ هﺎﻧدأ ﻊﻗﻮﻤﻟا ﺎﻧأ ﺎﮭﺑ يدﻮﺟو ةﺮﺘﻓ ءﺎﻨﺛا
                ﺔﻜﻠﻤﻤﻟا ﻦﯿﻧاﺮﻘﺑ ﺎﻣﺰﻨﻠﻣ نﻮﻛﺎﺳ
              </p>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Bottom Label -->

    <div class="grid grid-cols-4 mt-1 font-semibold border-dashed border-b border-black pb-2">
      <div>
        <p>Date:</p>
      </div>
      <div>
        <p>
          <span class="text-gray-700">: ﺦﯾرﺎﺘﻟا</span>
          <span class="ml-2">Signature:</span>
        </p>
      </div>
      <div>
        <p>
          <span class="text-gray-700">: ﻊﯿﻗﻮﺘﻟا</span>
          <span class="ml-2">Name:</span>
          <span class="ml-2 text-[11px] font-bold">MUJAMMEL MIA</span>
        </p>
      </div>
      <div>
        <p class="text-end text-gray-700">: ﻢﺳﻻا</p>
      </div>
    </div>
    <div class="flex justify-between">
      <p class="underline font-semibold mt-1">For official use only</p>
      <p class="underline font-semibold mt-1 text-gray-700">ﻂﻘﻓ ﻲﻤﺳﺮﻟا لﺎﻤﻌﺘﺳﻼﻟ</p>
    </div>

    <div class="grid grid-cols-4 font-semibold border-b border-black pb-0.5 mt-1">
      <div>
        <p>
          Date:
          <span class="ml-2 font-bold text-[11px]">1446/08/28</span>
        </p>
      </div>

      <div>
        <p>
          <span class="text-gray-700">: ﺦﯾرﺎﺘﻟا</span>
          <span class="ml-2">Visa No:</span>
        </p>
      </div>
      <div>
        <p class="font-bold text-[11px]">1304964944</p>
      </div>
      <div>
        <p class="text-end text-gray-700">: ةﺮﯿﺷﺎﺘﻟا ءﺎﻄﻋأ ﻲﻓ ﮫﯿﻠﻋ ﺪﻤﺘﻌﻤﻟا ﺮﻣﻻا ﻢﻗر</p>
      </div>
    </div>

    <div class="grid grid-cols-3 font-semibold border-b border-black pb-0.5 mt-1">
      <div>
        <p>Visit/Work for:</p>
      </div>

      <div>
        <p class="font-semibold text-gray-700">تﻻوﺎﻘﻤﻠﻟ ﻰﯿﺤﯿﻟا ﺔﻋﻮﻤﺠﻣ</p>
      </div>
      <div>
        <p class="text-end text-gray-700">: ةرﺎﯾﺰﻟ</p>
      </div>
    </div>

    <div class="grid grid-cols-4 font-semibold border-b border-black pb-0.5 mt-1">
      <div>
        <p>Date:</p>
      </div>

      <div>
        <p>
          <span class="text-gray-700">: ﺦﯾرﺎﺘﻟا</span>
          <span class="ml-2">Authorization:</span>
        </p>
      </div>

      <div>
        <p class="font-bold text-[11px]">7009460838</p>
      </div>
      <div>
        <p class="text-end text-gray-700">: ﻢﻗﺮﺑﺮﺷأ</p>
      </div>
    </div>

    <div class="grid grid-cols-4 font-semibold border-b border-black pb-0.5 mt-1">
      <div>
        <p>Fee Collected:</p>
      </div>

      <div>
        <p>
          <span class="text-gray-700">: ﻞﺼﺤﻤﻟا ﻎﻠﺒﻤﻟا</span>
          <span class="ml-2">Type:</span>
        </p>
      </div>

      <div>
        <p>
          <span class="text-gray-700">: ﺎﮭﻋﻮﻧ</span>
          <span class="ml-2">Duration:</span>
        </p>
      </div>
      <div>
        <p class="text-end text-gray-700">: ﺎﮭﺗﺪﻣ</p>
      </div>
    </div>

    <div class="flex justify-between mt-2">
      <div>
        <p class="border-t font-semibold mt-1">ﻲﻠﺼﻨﻘﻟا ﻢﺴﻘﻟا ﺲﯿﺋر</p>
        <p class="font-semibold mt-1">Head of consular section</p>
      </div>
      <div class="mt-1">
        <img src="/barcode.png" alt="Barcode" class="w-32 h-8 mx-auto" />
        <p class="text-center font-bold text-[11px]">A17516124</p>
      </div>

      <div>
        <p class="border-t font-semibold mt-1 text-gray-700">ﻂﻘﻓ ﻲﻤﺳﺮﻟا لﺎﻤﻌﺘﺳﻼﻟ</p>
        <p class="font-semibold mt-1 text-end">Checked by</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

// Set applicant photo URL here, or leave empty to show "Photo" placeholder
const applicantPhoto = ref('')
// Example: const applicantPhoto = ref('/path/to/photo.jpg');
</script>

<style scoped>
@media print {
  * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  @page {
    size: A4;
    margin: 10mm;
  }

  .max-w-4xl {
    max-width: 100%;
    margin: 10px;
    padding: 0;
  }
}
</style>
