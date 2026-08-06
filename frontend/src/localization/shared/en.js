export default {
  shared: {
    actions: {
      add: 'Add',
      edit: 'Edit',
      delete: 'Delete',
      view: 'View',
      save: 'Save',
      update: 'Update',
      cancel: 'Cancel',
      close: 'Close',
      submit: 'Submit',
      search: 'Search',
      reset: 'Reset',
      copy_credentials: 'Copy Credentials',
      filter: 'Filter',
      export: 'Export',
      print: 'Print',
      download: 'Download',
      upload: 'Upload',
      actions: 'Actions',
    },

    labels: {
      // Common
      sl: 'SL',
      id: 'ID',
      image: 'Image',
      name: 'Name',
      email: 'Email',
      phone: 'Phone',
      address: 'Address',
      role: 'Role',
      status: 'Status',
      country: 'Country',
      client: 'Client',
      agent: 'Agent',
      points: 'Points',
      demand_letters: 'Demand Letters',
      process: 'Process',
      password: 'Password',
      preview_url: 'Preview URL',
      preview_link: 'Preview Link',
      count: 'Count',
      list: 'List',
      rejected: 'Rejected',
      deployed: 'Deployed',
      declined: 'Declined',

      document: 'Document',
      total: 'Total',

      created_at: 'Created At',
      updated_at: 'Updated At',
      created_by: 'Created By',
      updated_by: 'Updated By',
      completed_at: 'Completed At',

      quick_search_candidate: 'Quick Search Candidate',

      // Client
      client_id: 'Client ID',
      send_notification: 'Send Notification',
      upload_client_image: 'Upload Client Image (Optional)',

      // Agent
      agent_id: 'Agent ID',
      manager_name: 'Manager Name',
      staff_name: 'Staff Name',
      staff_phone: 'Staff Phone',
      phone2: 'Alternate Phone',
      whatsapp: 'WhatsApp',
      whatsapp_no: 'WhatsApp No',
      nid_no: 'NID No',
      top_agent: 'Top Agent',
      top_performer: 'Top Performer',

      // Work Order
      demand_letter: 'D. Letter',
      demand_letter_id: 'D. Letter ID',
      candidates: 'Candidates',
      candidate: 'Candidate',
      visa_issue_number: 'Visa Issue Number',
      sponsor_id: 'Sponsor ID',
      end_date: 'End Date',
      assigned_to: 'Assigned To',

      // Job
      job: 'Job',
      job_name: 'Job Name',
      job_lists: 'Job Lists',
      job_code: 'Job Code',
      vacancy: 'Vacancy',
      payer: 'Payer',
      client_name: 'Client Name',
      price_taka: 'Price (BDT)',
      client_commission_per_candidate: 'Client Commission Per Candidate (Taka)',
      experience: 'Experience',
      salary: 'Salary',
      contract_length: 'Contract Length',
      deadline: 'Deadline',
      interview_date: 'Interview Date',
      experience_required: 'Experience Required',
      age_range: 'Age Range',


      // Principal
      principal: 'Principal',
      principal_id: 'Principal ID',
      organization: 'Organization',
      designation: 'Designation',
      contact_no: 'Contact No',
      contact_person: 'Contact Person',

      //application
      application: 'Application',
      applicants: 'Applicants',
      applications: 'Applications',
      application_id: 'Application ID',
      candidate_name: 'Candidate Name',
      application_status: 'Application Status',
      application_price: 'Application Price',
      application_date: 'Application Date',
      application_number: 'Application Number',
      passport_no: 'Passport No',

      settings: 'Settings',
    },

    placeholders: {
      search: 'Search...',
      searching: 'Searching...',
      refreshing: 'Refreshing...',
      refresh: 'Refresh',
      select: 'Select...',

      country_search: 'Search by country name',

      name: 'Enter name',
      email: 'Enter email',
      phone: 'Enter phone number',
      address: 'Enter address',
    },
    filters: {
      from_date: 'From Date',
      to_date: 'To Date',
      reset: 'Reset',
    },

    messages: {
      loading: 'Loading...',
      updating: 'Updating...',
      creating: 'Creating...',
      saving: 'Saving...',
      deleting: 'Deleting...',
      delete_title: 'Are you sure?',
      delete_yes: 'Yes, delete it!',
      showing_records: 'Showing { showing } records of { total }',
      per_page: 'Per page',
      selected: 'Selected ',
      submitting: 'Submitting...',

      no_data: 'No data available.',
      no_records: 'There are no records to display at this time.',
      delete_selected: 'Delete Selected ({count})',
      delete_confirmation: 'Are you sure you want to delete this record?',
    },

    titles: {
      management: 'Management',
      details: 'Details',
      information: 'Information',
      hide: 'Hide',
    },

    pagination: {
      previous: 'Previous',
      next: 'Next',
    },
  },


  navigation: {
    dashboard: 'Dashboard',
    core_setup: 'Core Setup',
    country_management: 'Country Management',
    client_management: 'Client Management',
    vendor_management: 'Vendor Management',
    agent_management: 'Agent Management',
    principal_management: 'Principal Management',
    subject_management: 'Subject Management',
    qualification_management: 'Qualification Management',
    fee_category_management: 'Fee Category Management',
    fee_head_management: 'Fee Head Management',

    demand_jobs: 'Demand & Jobs',
    demand_letter: 'Demand Letter',
    job_management: 'Job Management',

    ats_workflow: 'ATS & Workflow',
    ats: 'ATS',
    process_management: 'Process Management',
    passport_handover: 'Passport Handover',

    recruitment: 'Recruitment',
    hiring_list: 'Hiring List',
    applicant_management: 'Applicant Management',
    application_list: 'Application List',
    short_list: 'Short List',
    waiting_list: 'Waiting List',
    rejected_list: 'Rejected List',

    document: 'Document',
    document_management: 'Document Management',

    ksa_visa_processing: 'KSA Visa Processing',
    embassy_submission: 'Embassy Submission',
    tasheer_appointment: 'Tasheer Appointment',

    finance: 'Finance',
    account_management: 'Account Management',
    payment_received: 'Payment / Received',
    receipt_list: 'Receipt List',
    income_list: 'Income List',
    bill_generation: 'Bills & Purchases',
    submitted_bills: 'Submitted Bills',
    bill_management: 'Paid Bills',
    rejected_bills: 'Rejected Bills',
    transactions: 'Transactions',
    expense_setup: 'Expense Setup',
    gross_profit_report: 'Gross Profit Report',

    hr_access_control: 'HR & Access Control',
    employee_management: 'Employee Management',
    department_management: 'Department Management',
    designation_management: 'Designation Management',
    roles: 'Roles',
    permissions: 'Permissions',

    reports_analytics: 'Reports & Analytics',
    ats_reports: 'ATS Reports',
    applicant_reports: 'Applicant Reports',
    process_expiry_reports: 'Process Expiry Reports',
    ats_summary_report: 'ATS Summary Report',

    system_settings: 'System Settings',
    general_settings: 'General Settings',
    activity_logs: 'Activity Logs',

    logout: 'Logout'
  }


}
