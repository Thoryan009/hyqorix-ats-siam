export default {
  setting: {
    tabs: {
      basic: 'Basic Settings',
      password: 'Password Change',
      backup: 'Database Backup',
      embassy: 'Embassy Settings',
    },

    basic: {
      software_name: 'Software Name',
      company_name: 'Company Name',
      company_no: 'Company No.',
      company_no_active: 'Company No. Active',
      yes: 'Yes',
      no: 'No',
      company_phone: 'Company Phone',
      company_email: 'Company Email',
      company_address: 'Company Address',
      company_logo: 'Company Logo',
      fav_icon: 'Fav Icon',
      login_background_image: 'Login Background Image',
      expiry_report_notify_department: 'Expiry Report Notify Department',
      tasheer_appointment_email: 'Tasheer Appointment Email',
      primary_color: 'Primary Color',
      primary_color_hint: 'Used across the admin panel for buttons, sidebar, and highlights.',
      save_settings: 'Save Settings',
    },

    password: {
      current_password: 'Current Password',
      enter_current_password: 'Enter your current password',
      new_password: 'New Password',
      enter_new_password: 'Enter your new password (min 8 characters)',
      confirm_new_password: 'Confirm New Password',
      confirm_new_password_placeholder: 'Confirm your new password',
      password_requirement: 'Password must be at least 8 characters long',
      changing_password: 'Changing Password...',
      change_password: 'Change Password',

      password_min_length_error: 'New password must be at least 8 characters long',
      password_mismatch_error: 'New password and confirm password do not match',
      password_change_failed: 'Failed to change password',
      enter_confirm_new_password: 'Enter your confirm new password',
    },

    backup: {
      download_database_backup: 'Download Database Backup',
      download_database_backup_description:
        'Download a complete backup of your database (.sql file) directly to your computer.',
      preparing_download: 'Preparing Download...',
      download_backup: 'Download Backup',

      schedule_automatic_backup: 'Schedule Automatic Backup',
      schedule_automatic_backup_description:
        'Save an email address to receive scheduled database backups (.sql file) automatically.',

      scheduled_backup_email: 'Email Address for Scheduled Backups',
      enter_scheduled_backup_email: 'Enter email for scheduled backups',

      save_email: 'Save Email',
      update_email: 'Update Email',

      current_schedule: 'Current Schedule:',
      current_schedule_message: 'Backups will be sent to {email}',
    },

    embassy: {
      embassy_name: 'Embassy Name',
      embassy_address: 'Embassy Address',
      agency_name: 'Agency Name for Embassy',
      company_rl: 'Company RL',
      save_settings: 'Save Settings',
    },
  },
}
