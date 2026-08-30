export const navGroups = [
  {
    label: 'navigation.dashboard',
    icon: 'fa fa-dashboard',
    items: [
      {
        name: 'navigation.dashboard',
        path: '/dashboard',
        icon: 'fa fa-home',
        permission: 'dashboard.view',
      },
    ],
  },
   {
    label: 'navigation.recruitment',
    icon: 'fa fa-users',
    items: [
      {
        name: 'navigation.hiring_list',
        path: '/applications/hiring-list',
        icon: 'fa fa-check',
        permission: 'application.view_hiring_list',
      },
      {
        name: 'navigation.applicant_management',
        path: '/applications',
        icon: 'fa fa-user',
        permission: 'application.view',
      },
      {
        name: 'navigation.application_list',
        path: '/applications/application-list',
        icon: 'fa fa-list',
        permission: 'application.view_application_list',
      },
      {
        name: 'navigation.short_list',
        path: '/applications/short-list',
        icon: 'fa fa-list-alt',
        permission: 'application.view_short_list',
      },
      {
        name: 'navigation.waiting_list',
        path: '/applications/waiting-list',
        icon: 'fa fa-clock-o',
        permission: 'application.view_waiting_list',
      },
      {
        name: 'navigation.rejected_list',
        path: '/applications/rejected-list',
        icon: 'fa fa-times',
        permission: 'application.view_rejected_list',
      },
    ],
  },
   {
    label: 'navigation.ats_workflow',
    icon: 'fa fa-list',
    items: [
      {
        name: 'navigation.ats',
        path: '/jobs/ats',
        icon: 'fa fa-list-alt',
        permission: 'ats.view',
      },
      {
        name: 'navigation.process_management',
        path: '/applications/processes',
        icon: 'fa fa-cogs',
        permission: 'process.view',
      },
      {
        name: 'navigation.passport_handover',
        path: '/passport-handover',
        icon: 'fa fa-id-card',
        permission: 'passport_handover.view',
      },
    ],
  },

   {
    label: 'navigation.ksa_visa_processing',
    icon: 'fa fa-building',
    items: [
      {
        name: 'navigation.embassy_submission',
        path: '/applications/embassy-submission',
        icon: 'fa fa-paper-plane',
        permission: 'visa_processing.view',
      },
      {
        name: 'navigation.tasheer_appointment',
        path: '/tasheer-appointment-reports',
        icon: 'fa fa-calendar-check-o',
        permission: 'visa_processing.view',
      },
    ],
  },

   {
    label: 'navigation.finance',
    icon: 'fa fa-money',
    items: [
      // {
      //   name: 'POS',
      //   path: '/applications/pos',
      //   icon: 'fa fa-credit-card',
      //   permission: 'pos.view',
      // },
      {
        name: 'navigation.bill_generation',
        path: '/finance/bills-and-purchases',
        icon: 'fa fa-file-text-o',
        permission: 'bill_generation.create',
        section: 'Operations',
      },
      {
        name: 'navigation.payment_received',
        path: '/finance/payment-received',
        icon: 'fa fa-exchange',
        permission: 'receive_payment.create',
        section: 'Operations',
      },
      {
        name: 'navigation.account_management',
        path: '/finance/accounts',
        icon: 'fa fa-bank',
        permission: 'finance_account.view',
        section: 'Operations',
      },
          {
        name: 'Gross Profit',
        path: '/finance/reports/gross-profit',
        icon: 'fa fa-line-chart',
        permission: 'gross_profit_report.view',
        section: 'Accounts',
      },
      {
        name: 'Trial Balance',
        path: '/finance/reports/trial-balance',
        icon: 'fa fa-list-alt',
        permission: 'trial_balance.view',
        section: 'Accounts',
      },
      {
        name: 'Income Statement',
        path: '/finance/reports/income-statement',
        icon: 'fa fa-balance-scale',
        permission: 'income_statement.view',
        section: 'Accounts',
      },
      {
        name: 'Balance Sheet',
        path: '/finance/reports/balance-sheet',
        icon: 'fa fa-file-text-o',
        permission: 'balance_sheet.view',
        section: 'Accounts',
      },
      {
        name: 'navigation.agent_ledger',
        path: '/finance/accounts',
        query: { tab: 'agent-accounts' },
        matchPrefixes: ['/finance/agent-accounts'],
        icon: 'fa fa-address-book',
        permission: 'finance_account.view',
        section: 'Ledgers',
      },
      {
        name: 'navigation.vendor_ledger',
        path: '/finance/accounts',
        query: { tab: 'vendor-accounts' },
        matchPrefixes: ['/finance/vendor-accounts'],
        icon: 'fa fa-book',
        permission: 'finance_account.view',
        section: 'Ledgers',
      },
      {
        name: 'navigation.submitted_bills',
        path: '/finance/submitted-bills',
        icon: 'fa fa-hourglass-half',
        permission: 'submitted_bills.view',
        section: 'Reports',
      },
      {
        name: 'navigation.bill_management',
        path: '/finance/expense-entries',
        icon: 'fa fa-list-alt',
        permission: 'paid_bill.view',
        section: 'Reports',
      },
      {
        name: 'navigation.rejected_bills',
        path: '/finance/rejected-bills',
        icon: 'fa fa-times-circle',
        permission: 'rejected_bills.view',
        section: 'Reports',
      },
      {
        name: 'navigation.receipt_list',
        path: '/finance/payment-collection',
        icon: 'fa fa-money',
        permission: 'receive_list.view',
        section: 'Reports',
      },
      {
        name: 'navigation.income_list',
        path: '/finance/income-list',
        icon: 'fa fa-plus-circle',
        permission: 'income_list.view',
        section: 'Reports',
      },
      {
        name: 'navigation.transactions',
        path: '/finance/transactions',
        icon: 'fa fa-random',
        permission: 'transaction.view',
        section: 'Reports',
      },
      {
        name: 'navigation.expense_setup',
        path: '/finance/expense-management',
        icon: 'fa fa-tags',
        permission: 'account_setup.view',
        section: 'Setup',
      },
      {
        name: 'Income Setup',
        path: '/finance/income-management',
        icon: 'fa fa-money',
        permission: 'account_setup.view',
        section: 'Setup',
      },

      // {
      //   name: 'navigation.generate_bill',
      //   path: '/finance/agent-bills/generate',
      //   icon: 'fa fa-file-text-o',
      //   permission: 'transaction.view',
      // },
      // {
      //   name: 'navigation.job_wise_billing',
      //   path: '/finance/agent-bills',
      //   icon: 'fa fa-file-text-o',
      //   permission: 'transaction.view',
      // },
      // {
      //   name: 'navigation.transaction_management',
      //   path: '/applications/transactions',
      //   icon: 'fa fa-exchange',
      //   permission: 'transaction.view',
      // },
      // {
      //   name: 'navigation.candidate_bills',
      //   path: '/applications/candidate-bills',
      //   icon: 'fa fa-file-text-o',
      //   permission: 'candidate_bill.view',
      // },
      // {
      //   name: 'navigation.client_bills',
      //   path: '/applications/client-bills',
      //   icon: 'fa fa-file',
      //   permission: 'client_bill.view',
      // },
    ],
  },

  {
    label: 'FiNANCE 2',
    icon: 'fa fa-calculator',
    items: [
      {
        name: 'Chart of Accounts',
        path: '/accounts/chart-of-accounts',
        icon: 'fa fa-list-alt',
      },
      {
        name: 'Party Type Management',
        path: '/party-types',
        icon: 'fa fa-tags',
      },
      {
        name: 'Party Management',
        path: '/parties',
        icon: 'fa fa-handshake-o',
      },
      {
        name: 'Transaction Type Management',
        path: '/journal-transaction-types',
        icon: 'fa fa-exchange',
      },
      {
        name: 'Journal Management',
        path: '/journals',
        icon: 'fa fa-book',
      },
      {
        name: 'Post Journal',
        path: '/journals/post',
        icon: 'fa fa-pencil-square-o',
      },
      {
        name: 'accounts.trial_balance',
        path: '/accounts/trial-balance',
        icon: 'fa fa-balance-scale',
      },
      {
        name: 'accounts.gross_profit',
        path: '/accounts/gross-profit',
        icon: 'fa fa-line-chart',
      },
    ],
  },

    {
    label: 'navigation.hr_access_control',
    icon: 'fa fa-users',
    items: [
      {
        name: 'navigation.employee_management',
        path: '/employees',
        icon: 'fa fa-user',
        permission: 'employee.view',
      },
      {
        name: 'navigation.department_management',
        path: '/employees/departments',
        icon: 'fa fa-building',
        permission: 'department.view',
      },
      {
        name: 'navigation.designation_management',
        path: '/employees/designations',
        icon: 'fa fa-id-badge',
        permission: 'designation.view',
      },
      {
        name: 'navigation.roles',
        path: '/employees/roles',
        icon: 'fa fa-user-secret',
        permission: 'role.view',
      },
      {
        name: 'navigation.permissions',
        path: '/employees/permissions',
        icon: 'fa fa-lock',
        permission: 'permission.view',
      },
    ],
  },


  {
    label: 'navigation.document',
    icon: 'fa fa-folder-open',
    items: [
      {
        name: 'navigation.document_management',
        path: '/documents',
        icon: 'fa fa-file-text-o',
        permission: 'document.view',
      },
    ],
  },

   {
    label: 'navigation.reports_analytics',
    icon: 'fa fa-bar-chart',
    items: [
      {
        name: 'navigation.ats_reports',
        path: '/ats-reports/all',
        icon: 'fa fa-line-chart',
        permission: 'ats_report.view',
      },
      // {
      //   name: 'navigation.transaction_reports',
      //   path: '/transaction-reports',
      //   icon: 'fa fa-area-chart',
      //   permission: 'transaction_report.view',
      // },
      {
        name: 'navigation.applicant_reports',
        path: '/application-reports/application',
        icon: 'fa fa-pie-chart',
        permission: 'application_report.view',
      },
      {
        name: 'navigation.process_expiry_reports',
        path: '/expiry-reports',
        icon: 'fa fa-calendar-times-o',
        permission: 'process_expiry_report.view',
      },

      {
        name: 'navigation.ats_summary_report',
        path: '/ats-summary-report',
        icon: 'fa fa-line-chart',
        permission: 'ats_summary_report.view',
      },
    ],
  },



  {
    label: 'navigation.core_setup',
    icon: 'fa fa-database',
    items: [
      {
        name: 'navigation.country_management',
        path: '/countries',
        icon: 'fa fa-globe',
        permission: 'country.view',
      },
      {
        name: 'navigation.client_management',
        path: '/clients',
        icon: 'fa fa-users',
        permission: 'client.view',
      },
      {
        name: 'navigation.vendor_management',
        path: '/vendors',
        icon: 'fa fa-ticket',
        permission: 'vendor.view',
      },
      {
        name: 'navigation.agent_management',
        path: '/agents',
        icon: 'fa fa-user-secret',
        permission: 'agent.view',
      },
      {
        name: 'navigation.principal_management',
        path: '/principals',
        icon: 'fa fa-user-secret',
        permission: 'principal.view',
      },
      {
        name: 'navigation.subject_management',
        path: '/applications/subjects',
        icon: 'fa fa-book',
        permission: 'subject.view',
      },
      {
        name: 'navigation.qualification_management',
        path: '/applications/qualifications',
        icon: 'fa fa-graduation-cap',
        permission: 'qualification.view',
      },
      {
        name: 'navigation.fee_category_management',
        path: '/jobs/job-details-categories',
        icon: 'fa fa-tags',
        permission: 'fee_category.view',
      },
      {
        name: 'navigation.fee_head_management',
        path: '/jobs/job-details-heads',
        icon: 'fa fa-list-alt',
        permission: 'fee_head.view',
      },
    ],
  },

  {
    label: 'navigation.demand_jobs',
    icon: 'fa fa-briefcase',
    items: [
      {
        name: 'navigation.demand_letter',
        path: '/work-orders',
        icon: 'fa fa-file-text',
        permission: 'demand_letter.view',
      },
      {
        name: 'navigation.job_management',
        path: '/jobs',
        icon: 'fa fa-suitcase',
        permission: 'job.view',
      },
    ],
  },










  {
    label: 'navigation.system_settings',
    icon: 'fa fa-cog',
    items: [
      {
        name: 'navigation.general_settings',
        path: '/settings',
        icon: 'fa fa-cog',
        permission: 'setting.view',
      },
      {
        name: 'navigation.activity_logs',
        path: '/employees/activity-logs',
        icon: 'fa fa-list',
        permission: 'activity.view',
      },
    ],
  },
]
