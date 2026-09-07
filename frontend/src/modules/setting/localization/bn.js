export default {
  setting: {
    tabs: {
      basic: 'মৌলিক সেটিংস',
      password: 'পাসওয়ার্ড পরিবর্তন',
      backup: 'ডাটাবেজ ব্যাকআপ',
      embassy: 'এমবাসি সেটিংস',
      party_type_mapping: 'পার্টি টাইপ ম্যাপিং',
    },

    basic: {
      software_name: 'সফটওয়্যারের নাম',
      software_version: 'সফটওয়্যার ভার্সন',
      company_name: 'কোম্পানির নাম',
      company_no: 'কোম্পানি নং',
      company_no_active: 'কোম্পানি নং সক্রিয়',
      yes: 'হ্যাঁ',
      no: 'না',
      company_phone: 'কোম্পানির ফোন',
      company_email: 'কোম্পানির ইমেইল',
      company_address: 'কোম্পানির ঠিকানা',
      company_logo: 'কোম্পানির লোগো',
      fav_icon: 'ফ্যাভ আইকন',
      login_background_image: 'লগইন ব্যাকগ্রাউন্ড ছবি',
      image_max_size: 'শুধুমাত্র JPG বা PNG। সর্বোচ্চ ফাইল সাইজ: ৪০০ KB।',
      expiry_report_notify_department: 'মেয়াদোত্তীর্ণ রিপোর্টের নোটিফিকেশন বিভাগ',
      tasheer_appointment_email: 'তাসহীর অ্যাপয়েন্টমেন্ট ইমেইল',
      primary_color: 'প্রাথমিক রং',
      primary_color_hint: 'অ্যাডমিন প্যানেলের বাটন, সাইডবার এবং হাইলাইটে ব্যবহৃত হয়।',
      save_settings: 'সেটিংস সংরক্ষণ করুন',
    },

    password: {
      current_password: 'বর্তমান পাসওয়ার্ড',
      enter_current_password: 'বর্তমান পাসওয়ার্ড লিখুন',
      new_password: 'নতুন পাসওয়ার্ড',
      enter_new_password: 'নতুন পাসওয়ার্ড লিখুন (ন্যূনতম ৮ অক্ষর)',
      confirm_new_password: 'নতুন পাসওয়ার্ড নিশ্চিত করুন',
      confirm_new_password_placeholder: 'নতুন পাসওয়ার্ড পুনরায় লিখুন',
      password_requirement: 'পাসওয়ার্ড কমপক্ষে ৮ অক্ষরের হতে হবে',
      changing_password: 'পাসওয়ার্ড পরিবর্তন করা হচ্ছে...',
      change_password: 'পাসওয়ার্ড পরিবর্তন করুন',

      password_min_length_error: 'নতুন পাসওয়ার্ড কমপক্ষে ৮ অক্ষরের হতে হবে',
      password_mismatch_error: 'নতুন পাসওয়ার্ড এবং নিশ্চিতকরণ পাসওয়ার্ড মিলছে না',
      password_change_failed: 'পাসওয়ার্ড পরিবর্তন করা যায়নি',
      enter_confirm_new_password: 'নতুন পাসওয়ার্ড নিশ্চিত করুন',
    },

    backup: {
      download_database_backup: 'ডাটাবেজ ব্যাকআপ ডাউনলোড',
      download_database_backup_description:
        'আপনার ডাটাবেজের সম্পূর্ণ ব্যাকআপ (.sql ফাইল) সরাসরি আপনার কম্পিউটারে ডাউনলোড করুন।',
      preparing_download: 'ডাউনলোড প্রস্তুত করা হচ্ছে...',
      download_backup: 'ব্যাকআপ ডাউনলোড করুন',

      schedule_automatic_backup: 'স্বয়ংক্রিয় ব্যাকআপ নির্ধারণ',
      schedule_automatic_backup_description:
        'নির্ধারিত সময়ে স্বয়ংক্রিয়ভাবে ডাটাবেজ ব্যাকআপ (.sql ফাইল) পাওয়ার জন্য একটি ইমেইল ঠিকানা সংরক্ষণ করুন।',

      scheduled_backup_email: 'নির্ধারিত ব্যাকআপের ইমেইল ঠিকানা',
      enter_scheduled_backup_email: 'নির্ধারিত ব্যাকআপের জন্য ইমেইল লিখুন',

      save_email: 'ইমেইল সংরক্ষণ করুন',
      update_email: 'ইমেইল আপডেট করুন',

      current_schedule: 'বর্তমান সময়সূচি:',
      current_schedule_message: 'ব্যাকআপ {email} ঠিকানায় পাঠানো হবে',
    },

    embassy: {
      embassy_name: 'এমবাসির নাম',
      embassy_address: 'এমবাসির ঠিকানা',
      agency_name: 'এমবাসির জন্য এজেন্সির নাম',
      embassy_company_name: 'এমবাসির কোম্পানির নাম',
      company_rl: 'কোম্পানির RL',
      save_settings: 'সেটিংস সংরক্ষণ করুন',
    },

    party_type_mapping: {
      description:
        'প্রতিটি ম্যানেজমেন্ট মডিউল একটি পার্টি টাইপের সাথে লিংক করুন। প্রতিটি ম্যানেজমেন্ট এবং পার্টি টাইপ শুধুমাত্র একবার লিংক করা যাবে।',
      select_party_type: 'পার্টি টাইপ নির্বাচন করুন',
      none: 'কোনোটি নয়',
      save_mappings: 'ম্যাপিং সংরক্ষণ করুন',
    },
  },
}
