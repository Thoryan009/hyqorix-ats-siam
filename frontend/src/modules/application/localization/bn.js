export default {
  application: {
    module: 'এপ্লিকেশন',
    management: 'এপ্লিকেশন ম্যানেজমেন্ট',
    applicant: 'প্রার্থী',

    add: 'এপ্লিকেশন যোগ করুন',
    edit: 'এপ্লিকেশন সম্পাদনা করুন',
    view: 'এপ্লিকেশন বিস্তারিত',
    delete: 'এপ্লিকেশন মুছুন',
    summary: 'এপ্লিকেশন সামারি',

    bulk_upload: 'বাল্ক আপলোড',
    hiring_list: 'হায়ারিং লিস্ট',
    waiting_list: 'ওয়েটিং লিস্ট',
    rejected_list: 'রিজেক্টেড লিস্ট',
    application_list: 'এপ্লিকেশন লিস্ট',
    short_list: 'শর্ট লিস্ট',
    ats: 'এটিএস',

    image: 'প্রার্থীর ছবি',
    remove: 'সরান',
    optional: 'ঐচ্ছিক',
    required: 'প্রয়োজনীয়',

    passport: 'পাসপোর্ট',

    app_id: 'এপ্লিকেশন আইডি',
    name: 'প্রার্থীর নাম',
    place_of_birth: 'জন্মস্থান',
    job: 'চাকরির নাম',

    // bulk upload
    csv_upload_guidelines: 'CSV আপলোড নির্দেশিকা',
    every_csv_record_must_include_given_name_and_sur_name:
      'প্রতিটি CSV রেকর্ডে অবশ্যই নাম (Given Name) এবং পদবি (Sur Name) থাকতে হবে। অন্যান্য কলাম ফাঁকা রাখা যেতে পারে।',
    user_must_select_a_job_before_uploading_the_csv:
      'CSV আপলোড করার আগে অবশ্যই একটি জব নির্বাচন করতে হবে।',
    all_workers_will_be_associated_with_selected_job:
      'CSV-এর সকল কর্মী নির্বাচিত জবের সাথে যুক্ত হবে।',
    duplicate_passport_number_record_will_be_skipped:
      'একই পাসপোর্ট নম্বরের ডুপ্লিকেট তথ্য পাওয়া গেলে সেই রেকর্ডটি বাদ দেওয়া হবে।',
    download_sample_csv: 'নমুনা CSV ডাউনলোড করুন',
    job_required: 'জব (আবশ্যক)',

    search_by_country_name: 'দেশের নাম দিয়ে খুঁজুন',
    select_a_job: 'একটি জব নির্বাচন করুন',
    search_by_demand_letter_or_client: 'ডিমান্ড লেটার বা ক্লায়েন্টের নাম দিয়ে খুঁজুন',
    search_by_process_name: 'প্রক্রিয়ার নাম দিয়ে খুঁজুন',
    search_by_d_letter: 'ডি. লেটার দিয়ে খুঁজুন',
    enter_passport_no_or_search: 'পাসপোর্ট নম্বর লিখুন বা খুঁজুন',
    search_candidate_by_name_or_passport_no: 'প্রার্থীর নাম বা পাসপোর্ট নম্বর দিয়ে খুঁজুন',
    agent_required: 'এজেন্ট (আবশ্যক)',
    select_an_agent: 'একজন এজেন্ট নির্বাচন করুন',
    click_to_select_csv_file: 'CSV ফাইল নির্বাচন করতে ক্লিক করুন',
    only_csv_files_are_allowed: 'শুধুমাত্র CSV ফাইল অনুমোদিত',
    upload: 'আপলোড',
    uploading: 'আপলোড হচ্ছে...',

    //passport details

    passport_details: 'পাসপোর্টের তথ্য',
    upload_passport: 'পাসপোর্ট আপলোড করুন',
    processing: 'প্রক্রিয়াকরণ হচ্ছে...',
    passport_preview: 'পাসপোর্ট প্রিভিউ',
    hover_to_zoom: 'বড় করে দেখতে মাউস রাখুন',
    no_passport_uploaded: 'কোনো পাসপোর্ট আপলোড করা হয়নি',
    passport_number_exists: 'এই পাসপোর্ট নম্বরটি ইতোমধ্যে বিদ্যমান।',
    passport_extract_failed: 'পাসপোর্ট থেকে তথ্য সংগ্রহ করা যায়নি।',
    select_passport_first: 'অনুগ্রহ করে প্রথমে একটি পাসপোর্ট ফাইল নির্বাচন করুন।',
    passport_number_placeholder: 'যেমন A1234567',
    given_name_placeholder: 'যেমন রহিম',
    given_name: 'প্রদত্ত নাম',
    surname: 'উপনাম',
    father_name: 'পিতার নাম',
    mother_name: 'মাতার নাম',
    nid_number: 'জাতীয় পরিচয়পত্র নম্বর',
    date_of_birth: 'জন্ম তারিখ',
    sex: 'লিঙ্গ',
    nationality: 'জাতীয়তা',
    date_of_issue: 'ইস্যুর তারিখ',
    date_of_expiry: 'মেয়াদ শেষের তারিখ',
    passport_link: 'পাসপোর্ট লিঙ্ক',

    surname_placeholder: 'যেমন আহমেদ',
    father_name_placeholder: 'যেমন রহিম',
    mother_name_placeholder: 'যেমন রহিম',
    mobile_placeholder: 'যেমন +8801712345678',
    nid_number_placeholder: 'যেমন 1998123456789',
    address_placeholder: 'যেমন ১২৩ মেইন স্ট্রিট, ঢাকা, বাংলাদেশ',

    // personal informatiom
    subject: 'সাবজেক্ট',
    personal_information: 'ব্যক্তিগত তথ্য',
    total_qualifications: 'মোট যোগ্যতাসমূহ',


    email_placeholder: 'যেমন example@mail.com',
    select_marital_status: 'বৈবাহিক অবস্থা নির্বাচন করুন',
    driving_license_number: 'ড্রাইভিং লাইসেন্স নম্বর',
    driving_license_placeholder: 'যেমন DL12345678',
    height_ft: 'উচ্চতা (ফুট)',
    height_placeholder: 'যেমন ৫.৮ (ফুট)',
    weight_kg: 'ওজন (কেজি)',
    weight_placeholder: 'যেমন ৭৫ (কেজি)',
    jpg_png_max_400kb: 'JPG, PNG • সর্বোচ্চ ৪০০KB',

    //educational information
    education_experience: 'শিক্ষা ও অভিজ্ঞতা',
    experience: 'অভিজ্ঞতা',
    bd_experience: 'বাংলাদেশের অভিজ্ঞতা',
    overseas_experience: 'বিদেশের অভিজ্ঞতা',
    add_experience: 'অভিজ্ঞতা যোগ করুন',
    remove_experience: 'অভিজ্ঞতা সরান',
    experience_type: 'অভিজ্ঞতার ধরন',
    select_experience_type: 'ধরন নির্বাচন করুন',
    company_name: 'প্রতিষ্ঠানের নাম',
    company_name_placeholder: 'প্রতিষ্ঠানের নাম লিখুন',
    position: 'পদ',
    position_placeholder: 'পদের নাম লিখুন',
    from_date: 'শুরুর তারিখ',
    to_date: 'শেষের তারিখ',
    currently_working: 'বর্তমানে কর্মরত',
    responsibilities: 'দায়িত্বসমূহ',
    add_responsibility: 'দায়িত্ব যোগ করুন',
    remove_responsibility: 'দায়িত্ব সরান',
    responsibility_placeholder: 'দায়িত্ব {number} (সর্বোচ্চ ১০০ অক্ষর)',
    no_responsibilities:
      'এখনও কোনো দায়িত্ব যোগ করা হয়নি। একটি যোগ করতে "দায়িত্ব যোগ করুন" এ ক্লিক করুন।',

    //SINGLE DOCUMENT UPLOAD
    single_combined_document: 'একক সমন্বিত ডকুমেন্ট',
    combine_documents_hint:
      'আপলোড করার আগে অনুগ্রহ করে সকল প্রয়োজনীয় ডকুমেন্ট একটি PDF ফাইলে একত্রিত করুন।',
    suggested_order: 'প্রস্তাবিত ক্রম',
    resume: 'জীবনবৃত্তান্ত',
    experience_certificate: 'অভিজ্ঞতার সনদ',
    education_certificate: 'শিক্ষাগত সনদ',
    training_certificate: 'প্রশিক্ষণ সনদ',
    upload_single_pdf: 'একটি PDF আপলোড করুন',
    pdf_only_max_400kb: 'শুধুমাত্র PDF • সর্বোচ্চ ৪০০ KB',

    // merge documents
    mergable_documents: 'একত্রিতযোগ্য ডকুমেন্ট',
    mergable_documents_hint:
      'একটি PDF-এ একত্রিত করার জন্য পৃথক ডকুমেন্টসমূহ। শুধুমাত্র PDF • প্রতিটি সর্বোচ্চ ৪০০ KB।',
    passport_pdf: 'পাসপোর্ট PDF',
    driving_license: 'ড্রাইভিং লাইসেন্স',

    // other document upload
    other_documents: 'অন্যান্য ডকুমেন্ট',
    upload_nid: 'এনআইডি আপলোড করুন',
    upload_offer_letter: 'অফার লেটার আপলোড করুন',
    upload_visa_copy: 'ভিসার কপি আপলোড করুন',
    upload_immigration_clearance: 'ইমিগ্রেশন ক্লিয়ারেন্স আপলোড করুন',
    upload_ticket: 'টিকিট আপলোড করুন',
    upload_ticket_acknowledgment: 'টিকিট অ্যাকনলেজমেন্ট আপলোড করুন',
    upload_svp: 'SVP আপলোড করুন',
    upload_qvp: 'QVP আপলোড করুন',

    // application section
    application_details: 'আবেদনের তথ্য',
    job_list_required: 'জব তালিকা (আবশ্যক)',
    applied_through_required: 'আবেদন মাধ্যম (আবশ্যক)',

    search_by_job_name_or_job_code: 'জবের নাম বা জব কোড দিয়ে খুঁজুন',
    search_by_agent_name: 'এজেন্টের নাম দিয়ে খুঁজুন',
    search_by_client_name: 'ক্লায়েন্টের নাম দিয়ে খুঁজুন',
    search_by_assigner_name: 'অ্যাসাইনারের নাম দিয়ে খুঁজুন',
    search_by_principal_name: 'প্রিন্সিপালের নাম দিয়ে খুঁজুন',

    remarks: 'মন্তব্য',
    remarks_optional: 'মন্তব্য (ঐচ্ছিক)',
    candidate_available_for_interview: 'প্রার্থী সাক্ষাৎকারের জন্য উপলব্ধ',
    payment_responsibility_required: 'পেমেন্ট দায়িত্ব (আবশ্যক)',
    direct_candidate: 'সরাসরি প্রার্থী',
    agent_pays_recruitment_charges: 'এজেন্ট নিয়োগ চার্জ প্রদান করে',
    client_pays_in_usd_pricing: 'ক্লায়েন্ট USD মূল্যায়নে প্রদান করে',
    candidate_pays_in_bdt_pricing: 'প্রার্থী BDT মূল্যায়নে প্রদান করে',

    //LinksSection
    document_links: 'ডকুমেন্ট লিংকসমূহ',
    nid_link_optional: 'এনআইডি লিংক (ঐচ্ছিক)',
    passport_link_optional: 'পাসপোর্ট লিংক (ঐচ্ছিক)',
    resume_link_optional: 'রিজিউমে লিংক (ঐচ্ছিক)',
    drive_link_example: 'যেমন https://drive...',

    upload_image: 'এপ্লিকেশন ছবি আপলোড করুন (ঐচ্ছিক)',
    placeholder: 'যেমন: বাংলাদেশ',

    information: 'এপ্লিকেশন সম্পর্কিত তথ্য',
    total_clients: 'মোট ক্লায়েন্ট',
    clients: 'ক্লায়েন্টসমূহ',

    delete_confirmation: 'আপনি কি নিশ্চিত যে এই এপ্লিকেশনটি মুছে ফেলতে চান?',

    //view modal
    education: 'শিক্ষা',
    personal_info: 'ব্যক্তিগত তথ্য',
    contact: 'যোগাযোগ',
    nid_details: 'এনআইডি বিবরণ',
    system_info: 'সিস্টেম তথ্য',
    transaction_history: 'লেনদেনের হিস্টোরি',

    // /PersonalInformation
    single_document: 'একক ডকুমেন্ট',
    worker_image: 'কর্মীর ছবি',
    nid_document: 'এনআইডি ডকুমেন্ট',
    passport_document: 'পাসপোর্ট ডকুমেন্ট',
    payment_responsibility: 'পেমেন্ট রেস্পন্সিবিলিটি',
    general_documents: 'সাধারণ ডকুমেন্টসমূহ',
    not_available: 'নট এভেইলেবল',
    offer_letter: 'অফার লেটার',
    acknowledgment: 'অ্যাকনলেজমেন্ট',
    visa_copy: 'ভিসা কপি',
    immigration_clearance: 'ইমিগ্রেশন ক্লিয়ারেন্স',
    svp: 'এসভিপি',
    qvp: 'কিউভিপি',
    ticket: 'টিকিট',
    applied_through: 'যার মাধ্যমে আবেদন করা হয়েছে',
    record_id: 'রেকর্ড আইডি',

    transaction_id: 'ট্রানজেকশন আইডি',
    total_amount: 'মোট পরিমাণ',
    paid_total: 'মোট প্রদত্ত',
    discount: 'ছাড়',
    due_amount: 'বাকি পরিমাণ',
    payer_name: 'পেমেন্টকারী নাম',
    payment_method: 'পেমেন্ট পদ্ধতি',
    payment_status: 'পেমেন্ট স্ট্যাটাস',
    payment_date: 'পেমেন্ট তারিখ ও সময়',
    payment_note: 'পেমেন্ট নোট',
  },

  embassy: {
    title: 'KSA ভিসা প্রসেসিং ম্যানেজমেন্ট',
    sub_title: 'শুধুমাত্র মেডিকেল ফিট আবেদনসমূহ এই রিপোর্টে প্রদর্শিত হয়।',
    embassy_list: 'এমবাসি তালিকা',
    has_visa_info: 'ভিসা তথ্য আছে কিনা',
    visa_status: 'ভিসা স্ট্যাটাস',

    edit_title: 'KSA ভিসা প্রসেসিং তথ্য সম্পাদনা করুন',
    religion: 'ধর্ম',
    applied_id: 'Applied ID',
    muslim: 'মুসলিম',
    non_muslim: 'অমুসলিম',
    islam_religion: 'ইসলাম ধর্ম',
    other_religion: 'অন্যান্য ধর্ম',
    visa_no: 'ভিসা নং',
    profession: 'পেশা',
    visa_profession_arabic: 'ভিসা পেশা (আরবি)',
    arabic_profession: 'আরবি পেশা',
    english_profession: 'ইংরেজি পেশা (ঐচ্ছিক)',
    visa_profession_bangla: 'ভিসা পেশা (বাংলা)',
    visa_profession_english: 'ভিসা পেশা (ইংরেজি)',
    generating_english_profession: 'ইংরেজিতে পেশা তৈরি হচ্ছে...',


    visit_work_for_arabic: 'ভিজিট / ওয়ার্ক ফর (আরবি)',
    arabic_text: 'আরবি টেক্সট',
    mofa_no: 'MOFA নং',
    police_clearance_no: 'পুলিশ ক্লিয়ারেন্স নং',
    alwakala_no: 'Alwakala নং',

    visa_processing_submission: 'ভিসা প্রসেসিং সাবমিশন',
  },
  embassyList: {
    title: 'সব এমবাসির তালিকা',
    add: 'নতুন এমবাসির তালিকা যোগ করুন',
    edit: 'এমবাসির তালিকা সম্পাদনা করুন',
    view: 'এমবাসির তালিকা দেখুন',
    re_stamping: 'রিস্ট্যাম্পিং',
    new_stamping: 'নিউ স্ট্যাম্পিং',
    cancellation: 'ক্যান্সেলেশন',
    submit_date: 'জমা দেওয়ার তারিখ',
    create_list: 'তালিকা তৈরি করুন',
    update_list: 'তালিকা আপডেট করুন',
    back_to_embassy_list: 'এমবাসি তালিকায় ফিরে যান',
    search_hints: 'সার্চ হিন্টস',

    no_of_new_stamping: 'নিউ স্ট্যাম্পিং সংখ্যা',
    no_of_cancel_stamping: 'ক্যান্সেল স্ট্যাম্পিং সংখ্যা',
    no_of_re_stamping: 'রিস্ট্যাম্পিং সংখ্যা',
    last_update: 'শেষ আপডেট',
  },

  process: {
    title: 'প্রক্রিয়া ব্যবস্থাপনা',
    edit_title: 'প্রক্রিয়া সম্পাদনা করুন',
    view_title: 'প্রক্রিয়া বিবরণ দেখুন',

    name: 'প্রক্রিয়ার নাম',
    validity: 'বৈধতা (দিন)',
    notify_before: 'আগে জানানো (দিন)',
    duration: 'সময়কাল',
  }
}
