<template>
  <div class="max-w-5xl mx-auto bg-white p-4">
    <!-- Main Form Container -->
    <table class="w-full border-collapse border-2 border-black">
        <!-- Top label part -->
        <div class="grid grid-cols-3 gap-2">

 <!-- Photo Box -->
        <div class="w-40 h-42 border-2 border-black flex items-center justify-center mb-3 ml-4">
          <span class="text-sm">Photo</span>
        </div>
         <!-- Center - Barcode and Title -->
        <div class="flex-1 text-center">
          <div class="flex justify-center">
            <img src="/barcode.png" alt="Barcode" class="w-55 h-15 object-contain" />
          </div>
          <div class="text-base mb-1 font-bold">130496049467</div>
          <div class="text-lg font-bold mt-8">New Application</div>
        </div>

        <!-- Form Number -->
        <div class="text-right">
          <div class="text-4xl font-bold mb-8">E816528085</div>
          <div class="text-2xl font-semibold">EMBASSY OF SAUDI ARABIA</div>
          <div class="text-2xl font-semibold">CONSULAR SECTION</div>
        </div>

        </div>



      <!-- Row 1: Full Name -->
      <tr>
        <td class="border-2 border-black px-2 py-1 font-bold w-1/6" colspan="2">Full Name:</td>
        <td class="text-xl border-2 border-black px-2 py-1 text-center font-bold" colspan="8">
          MUJAMMEL MIA S/O. FALO MIA
        </td>
        <td class="border-2 border-black px-2 py-1 text-right font-bold w-1/6" colspan="2">
          : اسم الكامل
        </td>
      </tr>

      <!-- Row 2: Mother's Name -->
      <tr>
        <td class="border-2 border-black px-2 py-1 font-bold" colspan="2">Mother's Name:</td>
        <td class="text-xl border-2 border-black px-2 py-1 text-center font-bold" colspan="8">
          MOMENA KHATUN
        </td>
        <td class="border-2 border-black px-2 py-1 text-right font-bold" colspan="2">: اسم الأم</td>
      </tr>

      <!-- Row 3: Date of Birth & Place of Birth -->
      <tr>
        <td class="border-2 border-black px-2 py-1 font-bold" colspan="2">Date of Birth:</td>
        <td class="text-xl border-2 border-black px-2 py-1 text-center font-bold" colspan="2">01-08-2003</td>
        <td class="border-2 border-black px-2 py-1 text-right font-bold" colspan="2">
          : تاريخ الولادة
        </td>
        <td class="border-2 border-black px-2 py-1 font-bold" colspan="2">Place of Birth:</td>
        <td class="text-xl border-2 border-black px-2 py-1 text-center font-bold" colspan="2">KISHOREGANJ</td>
        <td class="border-2 border-black px-2 py-1 text-right font-bold" colspan="2">
          : محل الولادة
        </td>
      </tr>

      <!-- Row 4: Previous Nationality & Present Nationality -->
      <tr>
        <td class="border-2 border-black px-2 py-1 font-bold" colspan="2">Previous Nationality:</td>
        <td class="text-xl border-2 border-black px-2 py-1 text-center font-bold" colspan="2">BANGLADESH</td>
        <td class="border-2 border-black px-2 py-1 text-right font-bold" colspan="2">
          : الجنسية السابقة
        </td>
        <td class="border-2 border-black px-2 py-1 font-bold" colspan="2">Present Nationality:</td>
        <td class="text-xl border-2 border-black px-2 py-1 text-center font-bold" colspan="2">BANGLADESH</td>
        <td class="border-2 border-black px-2 py-1 text-right font-bold" colspan="2">
          : الجنسية الحالية
        </td>
      </tr>

      <!-- Row 5: Sex & Marital Status -->
      <tr>
        <td class="border-2 border-black px-2 py-1 font-bold" colspan="2">Sex:</td>
        <td class="text-xl border-2 border-black px-2 py-1 text-center font-bold" colspan="2">Male</td>
        <td class="border-2 border-black px-2 py-1 text-right font-bold" colspan="2">: الجنس</td>
        <td class="border-2 border-black px-2 py-1 font-bold" colspan="2">Marital Status:</td>
        <td class="text-xl border-2 border-black px-2 py-1 text-center font-bold" colspan="2">Unmarried</td>
        <td class="border-2 border-black px-2 py-1 text-right font-bold" colspan="2">
          : الحالة الاجتماعية
        </td>
      </tr>

      <!-- Row 6: Sect & Religion -->
      <tr>
        <td class="border-2 border-black px-2 py-1 font-bold" colspan="2">Sect:</td>
        <td class="border-2 border-black px-2 py-1 text-center font-bold" colspan="2"></td>
        <td class="border-2 border-black px-2 py-1 text-right font-bold" colspan="2">: المذهب</td>
        <td class="border-2 border-black px-2 py-1 font-bold" colspan="2">Religion:</td>
        <td class="text-xl border-2 border-black px-2 py-1 text-center font-bold" colspan="2">Muslim</td>
        <td class="border-2 border-black px-2 py-1 text-right font-bold" colspan="2">: الديانة</td>
      </tr>

      <!-- Row 7: Profession Row -->
      <tr>
        <td class="border-2 border-black px-2 py-1 text-right font-bold" colspan="12">
          <div class="flex justify-between space-y-1">
            <p>: هرﺪﺼﻣ</p>
            <p>: هرﺪﺼﻣ</p>
            <p class="text-xl font-bold">تﺂﺸﻨﻣو ﺐﺗﺎﻜﻣ ﻒﯿﻈﻨﺗ ﻞﻣﺎﻋ</p>
            <p>: هرﺪﺼﻣ</p>
          </div>
        </td>
      </tr>

      <tr>
        <td class="border-2 border-black px-2 py-1 text-right font-bold" colspan="12">
          <div class="flex justify-between space-y-1">
            <p>Place of Issue:</p>
            <p>Qualification:</p>
            <p>Profession:</p>
            <p class="text-xl font-bold">Offices and facilities Cleaning worker</p>
          </div>
        </td>
      </tr>

      <tr>
        <td class="border-2 border-black px-2 py-1 font-bold" colspan="3">
          <p>Home address & phone No.:</p>
        </td>
        <td class="border-2 border-black px-2 py-1 font-bold" colspan="6">
          <p></p>
        </td>
        <td class="border-2 border-black px-2 py-1 text-right font-bold" colspan="3">
          <p>: نﻮﻔﻠﺘﻟا ﻢﻗرو لﺰﻨﻤﻟا ناﻮﻨﻋ</p>
        </td>
      </tr>

      <tr>
        <td class="border-2 border-black px-2 py-1 font-bold" colspan="3">
          <p>Business address & phone No.:</p>
        </td>
        <td class="text-xl border-2 border-black px-2 py-1 text-center font-bold" colspan="6">
          <p>MERCHANT OVERSEAS   RL: 1160</p>
        </td>
        <td class="border-2 border-black px-2 py-1 text-right font-bold" colspan="3">
          <p>: نﻮﻔﻠﺘﻟا ﻢﻗر ( ﺔﺴﺳﺆﻤﻟا ) ﺔﻛﺮﺸﻟا ناﻮﻨﻋ</p>
        </td>
      </tr>

      <tr>
        <td class="text-lg border-2 border-black px-2 py-1 font-bold text-center" colspan="12">
          PLOT NO-23,ROAD NO-11,BLOCK-F,A-10,BIR UTTAM KHADEMUL BASHAR SARAWAK,BANANI,DHAKA.
        </td>
      </tr>

      <tr rowspan="2">
        <td class="border-2 border-black px-2 py-1 font-bold" colspan="2">
          <p>Purpose of Travel:</p>
        </td>
        <td class="border-2 border-black px-2  m-1 py-2 font-bold" colspan="8">
          <div class="flex justify-between space-y-1">
            <div class="border-2 px-3 py-1"><p>ﻞﻐﺸﻟا<br>work</p>
            </div>
            <div class="border-2 px-3 py-1"><p>ﻞﻐﺸﻟا<br>Transit</p>
            </div>
            <div class="border-2 px-3 py-1"><p>ﻞﻐﺸﻟا<br>Visit</p>
            </div>
           <div class="border-2 px-3 py-1"><p>ﻞﻐﺸﻟا<br>Umrah</p>
            </div>
          <div class="border-2 px-3 py-1"><p>ﻞﻐﺸﻟا<br>Residence</p>
            </div>
            <div class="border-2 px-3 py-1"><p>ﺞﺤﻟا<br>Hajj</p>
            </div>
            <div class="border-2 px-3 py-1"><p>ﺔﯿﺳﺎﻣﻮﻠﺑﺪﻟا<br>Diplomacy</p>
            </div>

          </div>
        </td>
        <td class="border-2 border-black px-2 py-1 font-bold" colspan="2">
          <p>: ﺮﻔﺴﻟا ﻦﻣ ﺔﺑﺎﻐﻟا</p>
        </td>
      </tr>

      <tr>
        <td class="border-2 border-black px-2 py-1 font-bold" colspan="3">
          <p>Place of issue: راﺪﺻﻻا ﻞﺤﻣ</p>
        </td>
        <td class="border-2 border-black px-2 py-1 font-bold" colspan="3">
          <p>Date of issue: راﺪﺻﻻا ﺦﯾرﺎﺗ</p>
        </td>
        <td class="border-2 border-black px-2 py-1 font-bold" colspan="3">
          <p>Date of expiry: ﺔﯿﺣﻼﺼﻟا ءﺎﮭﺘﻧا ﺦﯾرﺎﺗ</p>
        </td>
        <td class="border-2 border-black px-2 py-1 font-bold" colspan="3">
          <p>Passport No.: زاﻮﺠﻟا ﻢﻗر</p>
        </td>
      </tr>
      <tr>
        <td class="text-xl text-center border-2 border-black px-2 py-1 font-bold" colspan="3">
          <p>DHAKA</p>
        </td>
        <td class="text-xl text-center border-2 border-black px-2 py-1 font-bold" colspan="3">
          <p>13-01-2025</p>
        </td>
        <td class="text-xl text-center border-2 border-black px-2 py-1 font-bold" colspan="3">
          <p>12-01-2035</p>
        </td>
        <td class="text-xl text-center border-2 border-black px-2 py-1 font-bold" colspan="3">
          <p>A17516124</p>
        </td>
      </tr>

      <tr>
        <td class="border-2 border-black px-2 py-1 font-bold text-center" colspan="12">
          <div class="flex justify-between space-y-1">
            <p>
              : ﺔﻜﻠﻤﻤﻟﺎﺑ ﺔﻣﺎﻗﻻا ةﺪﻣ : لﻮﺻﻮﻟا ﺦﯾرﺎﺗ : ةردﺎﻐﻤﻟا ﺦﯾرﺎﺗ <br />
              Duration of stay in the kingdom:
            </p>
            <p>02 Years</p>
            <p>: لﻮﺻﻮﻟا ﺦﯾرﺎﺗ <br />Date of arrival:</p>
            <p>
              : ةردﺎﻐﻤﻟا ﺦﯾرﺎﺗ <br />
              Date fo departure:
            </p>
          </div>
        </td>
      </tr>
      <tr>
        <td class="border-2 border-black px-2 py-1 font-bold text-center" colspan="12">
          <div class="flex justify-between space-y-1">
            <p>ﺦﯾرﺎﺗ :</p>
            <p>( ) ﻢﻗر لﺎﺼﯾا :</p>
            <p>ﺦﯾرﺎﺗ :</p>
            <p>ﻢﻗر ﻚﯿﺸﺑ :</p>
            <p>ﻢﻗر ﻚﯿﺸﺑ ( ) مﺪﻘﻧ ( ) ﺎﻧﺎﺠﻣ ( ) : ﻊﻓﺪﻟا ﺔﻘﯾﺮط :</p>
          </div>
        </td>
      </tr>
      <tr>
        <td class="border-2 border-black px-2 py-1 font-bold text-center" colspan="12">
         <div class="flex justify-between space-y-1">
           <p>Mode of payment:</p>
          <p>Free</p>
          <p>Cash</p>
          <p>Cheque No.</p>
          <p>Date</p>
          <p>No.</p>
          <p>Date</p>
         </div>
        </td>
      </tr>
      <tr>
        <td class="border-2 border-black px-2 py-1 font-bold text-center" colspan="12">
         <div class="flex justify-between space-y-1">
            <p>
             Relationship:
            </p>
            <p class="text-xl">: ﮫﺘﻠﺻ <br>EMPLOYER AND EMPLOYEE</p>
            <p></p>
            <p>
             : مﺮﺤﻤﻟا ﻢﺳا
            </p>
            <p></p>
          </div>
        </td>
      </tr>
      <tr>
        <td class="border-2 border-black px-2 py-1 font-bold text-center" colspan="12">
       <div class="flex justify-between space-y-1">
            <p>
             Destination:
            </p>
            <p></p>
            <p>: لﻮﺻﻮﻟا ﺔﮭﺟ</p>
            <p>
             Carrier's:
            </p>
            <p></p>
            <p>: ﺔﻠﻓﺎﻨﻟا ﺔﻛﺮﺸﻟا ﻢﺳا</p>
          </div>
        </td>
      </tr>
      <tr>
        <td class="border-2 border-black px-2 py-1 font-bold text-center" colspan="12">
          Dependents traveling in the same passport : ﺮﻔﺴﻟا زاﻮﺟ ﺲﻔﻧ ﻲﻠﻋ ( ﻦﯿﻓﺎﻀﻤﻟا ) ﺔﻠﺋﺎﻌﻟا داﺮﻓا ﺺﺨﺗ تﺎﺣﺎﻀﯾا
        </td>
      </tr>

        <tr>
        <td class="text-lg border-2 border-black px-2 py-1 font-bold" colspan="3">
          <p>ﺔﻠﺼﻟا عﻮﻧ<br>Relatonship</p>
        </td>
        <td class="text-lg border-2 border-black px-2 py-1 font-bold" colspan="3">
          <p>دﻼﯿﻤﻟا ﺦﯾرﺎﺗ<br>Date of Birth</p>
        </td>
        <td class="text-lg border-2 border-black px-2 py-1 font-bold" colspan="3">
          <p>ﺲﻨﺠﻟا<br>Sex</p>
        </td>
        <td class="text-lg border-2 border-black px-2 py-1 font-bold" colspan="3">
          <p>ﻞﻣﺎﻜﻟﺎﺑ ﻢﺳﻻا<br>Full Name</p>
        </td>
      </tr>

      <tr>
        <td class="border-2 border-black px-2 py-1 font-bold" colspan="3">
          <p></p>
        </td>
        <td class="text-lg border-2 border-black px-2 py-1 font-bold" colspan="3">
          <p>CITY: RIYADH, K.S.A</p>
        </td>
        <td class="border-2 border-black px-2 py-1 font-bold" colspan="3">
          <p></p>
        </td>
        <td class="border-2 border-black px-2 py-1 font-bold" colspan="3">
          <p></p>
        </td>
      </tr>
       <tr>
        <td class="border-2 border-black px-2 py-1 font-bold" colspan="3">
          <p></p>
        </td>
        <td class="text-lg border-2 border-black px-2 py-1 font-bold" colspan="3">
          <p>TEL:</p>
        </td>
        <td class="border-2 border-black px-2 py-1 font-bold" colspan="3">
          <p></p>
        </td>
        <td class="border-2 border-black px-2 py-1 font-bold" colspan="3">
          <p></p>
        </td>
      </tr>

       <tr>
        <td class="border-2 border-black px-2 py-1 font-bold" colspan="3">
          <p></p>
        </td>
        <td class="border-2 border-black px-2 py-1 font-bold" colspan="3">
          <p>.</p>
        </td>
        <td class="border-2 border-black px-2 py-1 font-bold" colspan="3">
          <p></p>
        </td>
        <td class="border-2 border-black px-2 py-1 font-bold" colspan="3">
          <p></p>
        </td>
      </tr>

      <tr>
        <td class="border-2 border-black px-2 py-1 font-bold" colspan="3">
          <p></p>
        </td>
        <td class="border-2 border-black px-2 py-1 font-bold" colspan="3">
          <p>.</p>
        </td>
        <td class="border-2 border-black px-2 py-1 font-bold" colspan="3">
          <p></p>
        </td>
        <td class="border-2 border-black px-2 py-1 font-bold" colspan="3">
          <p></p>
        </td>
      </tr>

      <tr>
        <td class="border-2 border-black px-2 py-1 font-bold text-center" colspan="12">
         Name and address of company or individual in the kingdom : ﺔﻜﻠﻤﻤﻟﺎﺑ ﮫﻧاﻮﻨﻋ و ﺺﺨﺸﻟا ﻢﺳا وأ ﺔﻛﺮﺸﻟا ناﻮﻨﻋ و ﻢﺳا
        </td>
      </tr>
      <tr>
        <td class="border-2 border-black px-2 py-1 font-bold text-center" colspan="12">
         .
        </td>
        <tr>
        <td class="border-2 border-black px-2 py-1 font-bold text-center" colspan="12">
         I the undersigned hereby that all the information I have provided are correct. I will abide by laws of the kingdom during the period of my residence in it. و ﺔﺤﯿﺤﺻ ﺎﮭﺘﻧرد ﻲﺘﻟا تﺎﻣﻮﻠﻌﻤﻟا ﻞﻛ نﺎﺑﺮﻗأ هﺎﻧدأ ﻊﻗﻮﻤﻟا ﺎﻧأ ﺎﮭﺑ يدﻮﺟو ةﺮﺘﻓ ءﺎﻨﺛا ﺔﻜﻠﻤﻤﻟا ﻦﯿﻧاﺮﻘﺑ ﺎﻣﺰﻨﻠﻣ نﻮﻛﺎﺳ
        </td>
      </tr>
      </tr>
    </table>


    <!-- Bottom Label -->

    <div class="grid grid-cols-4 text-sm font-bold border-dashed border-b-2 border-black pb-4">
      <div>
        <p>Date:</p>
      </div>
       <div>
        <p>: ﺦﯾرﺎﺘﻟا <span class="ml-2">Signature:</span></p>
      </div>
       <div>
        <p>: ﻊﯿﻗﻮﺘﻟا <span class="ml-2">Name:</span> <span class="ml-2 text-base">MUJAMMEL MIA</span></p>
      </div>
       <div>
        <p class="text-end">: ﻢﺳﻻا</p>
      </div>
    </div>
    <div class="flex justify-between">
      <p class="text-sm underline font-bold mt-2">For official use only</p>
      <p class="text-sm underline font-bold mt-2">ﻂﻘﻓ ﻲﻤﺳﺮﻟا لﺎﻤﻌﺘﺳﻼﻟ</p>
    </div>


    <div class="grid grid-cols-4 text-sm font-bold  border-b-2 border-black pb-2 mt-2">
      <div>
        <p>Date: <span class="ml-4 text-lg font-bold">1446/08/28</span></p>
      </div>
      
       <div>
        <p>: ﺦﯾرﺎﺘﻟا<span class="ml-2">Visa No:</span></p>
      </div>
       <div>
        <p class="text-lg font-bold">1304964944</p>
      </div>
       <div>
        <p class="text-end">: ةﺮﯿﺷﺎﺘﻟا ءﺎﻄﻋأ ﻲﻓ ﮫﯿﻠﻋ ﺪﻤﺘﻌﻤﻟا ﺮﻣﻻا ﻢﻗر</p>
      </div>
    </div>


     <div class="grid grid-cols-3 text-sm font-bold  border-b-2 border-black pb-2 mt-2">
      <div>
        <p>Visit/Work for:</p>
      </div>
      
       <div>
        <p class="text-lg font-bold">تﻻوﺎﻘﻤﻠﻟ ﻰﯿﺤﯿﻟا ﺔﻋﻮﻤﺠﻣ</p>
      </div>
       <div>
        <p class="text-end">: ةرﺎﯾﺰﻟ</p>
      </div>
    </div>


    <div class="grid grid-cols-4 text-sm font-bold  border-b-2 border-black pb-2 mt-2">
      <div>
        <p>Date:</p>
      </div>
      
       <div>
        <p>: ﺦﯾرﺎﺘﻟا <span class="ml-2">Authorization:</span></p>
      </div>

      <div>
        <p class="text-lg font-bold">7009460838</p>
      </div>
       <div>
        <p class="text-end">: ﻢﻗﺮﺑﺮﺷأ</p>
      </div>
    </div>


     <div class="grid grid-cols-4 text-sm font-bold  border-b-2 border-black pb-2 mt-2">
      <div>
        <p>Fee Collected:</p>
      </div>
      
       <div>
        <p>: ﻞﺼﺤﻤﻟا ﻎﻠﺒﻤﻟا<span class="ml-2">Type:</span></p>
      </div>

      <div>
        <p>: ﺎﮭﻋﻮﻧ <span class="ml-2">Duration:</span></p>
      </div>
       <div>
        <p class="text-end">: ﺎﮭﺗﺪﻣ</p>
      </div>
    </div>


      <div class="flex justify-between mt-4">
        <div>
          <p class="text-sm border-t-2 font-bold mt-2">For official use only</p>
           <p class="text-sm font-bold mt-2">Head of consular section</p>
          </div>
          <div>
       <img src="/barcode.png" alt="Barcode" class="w-55 h-15 mx-auto" />
       <p class="text-center font-bold">A17516124</p>

     </div>
      
     <div> 
      <p class="text-sm border-t-2 font-bold mt-2">ﻂﻘﻓ ﻲﻤﺳﺮﻟا لﺎﻤﻌﺘﺳﻼﻟ</p>
       <p class="text-sm font-bold mt-2 text-end">Checked by</p>
    </div>
    </div>

  </div>
</template>

<script setup></script>
