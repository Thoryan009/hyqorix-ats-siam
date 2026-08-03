// Group configuration with icons
export const groupConfig = {
  'Master Data': {
    icon: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7C5 4 4 5 4 7z"/>
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M16 2v4M8 2v4M4 10h16"/>
    </svg>`,
  },
  'Demand Letter': {
    icon: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
      </path>
    </svg>`,
  },
  'Jobs & Applicant': {
    icon: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
      </path>
    </svg>`,
  },
  'Financial Management': {
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
    </svg>`,
  },
  'HR Management': {
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
      <circle cx="9" cy="7" r="4"/>
      <path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/>
      <rect x="15" y="3" width="6" height="10" rx="1"/>
      <path d="M15 7h6"/>
    </svg>`,
  },
  Reports: {
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <rect x="3" y="3" width="18" height="18" rx="2"/>
      <path d="M7 14v4M12 10v8M17 6v12"/>
    </svg>`,
  },
}

export const navItems = [
  // Dashboard - Standalone (no group)
  {
    name: 'Dashboard',
    path: '/dashboard',
    icon: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3
           m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3
           m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2
           a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
      </path>
    </svg>`,
  },

  // Master Data Group
  {
    group: 'Master Data',
    name: 'Country Management',
    path: '/countries',
    icon: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2
           2 2 0 012 2v2.945M8 3.935V5.5
           A2.5 2.5 0 0010.5 8h.5
           a2 2 0 012 2 2 2 0 104 0
           2 2 0 012-2h1.064M15 20.488V18
           a2 2 0 012-2h3.064M21 12
           a9 9 0 11-18 0 9 9 0 0118 0z">
      </path>
    </svg>`,
  },
  {
    group: 'Master Data',
    name: 'Client Management',
    path: '/clients',
    icon: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M17 20h5v-2a3 3 0 00-5.356-1.857
           M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857
           M7 20H2v-2a3 3 0 015.356-1.857
           M7 20v-2c0-.656.126-1.283.356-1.857
           m0 0a5.002 5.002 0 019.288 0
           M15 7a3 3 0 11-6 0 3 3 0 016 0
           zm6 3a2 2 0 11-4 0 2 2 0 014 0
           zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
      </path>
    </svg>`,
  },
  {
    group: 'Master Data',
    name: 'Agent Management',
    path: '/agents',
    icon: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M16 14a4 4 0 10-8 0M12 14v7M5 21h14M19 10a3 3 0 10-6 0M5 10a3 3 0 106 0">
      </path>
    </svg>`,
  },

  {
    group: 'Master Data',
    name: 'Principal Management',
    path: '/principals',
    icon: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M16 14a4 4 0 10-8 0M12 14v7M5 21h14M19 10a3 3 0 10-6 0M5 10a3 3 0 106 0">
      </path>
    </svg>`,
  },

  // Work Orders Group
  {
    group: 'Demand Letter',
    name: 'Demand Letter Management',
    path: '/work-orders',
    icon: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M9 12h6m-6 4h6
           m2 5H7a2 2 0 01-2-2V5
           a2 2 0 012-2h5.586a1 1 0 01.707.293
           l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
      </path>
    </svg>`,
  },

  // Jobs & Applicant Group
  {
    group: 'Jobs & Applicant',
    name: 'Job Management',
    path: '/jobs',
    icon: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M21 13.255A23.931 23.931 0 0112 15
           c-3.183 0-6.22-.62-9-1.745M16 6V4
           a2 2 0 00-2-2h-4a2 2 0 00-2 2v2
           m4 6h.01M5 20h14a2 2 0 002-2V8
           a2 2 0 00-2-2H5a2 2 0 00-2 2v10
           a2 2 0 002 2z">
      </path>
    </svg>`,
  },
  {
    group: 'Jobs & Applicant',
    name: 'ATS',
    path: '/jobs/ats',
    icon: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M9 12h6m-6 4h6M7 4h7l5 5v11
           a2 2 0 01-2 2H7
           a2 2 0 01-2-2V6
           a2 2 0 012-2z" />
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M9 17l2 2 4-4" />
    </svg>`,
  },
  {
    group: 'Jobs & Applicant',
    name: 'Hiring List',
    path: '/applications/hiring-list',
    icon: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
      </path>
    </svg>`,
  },
  {
    group: 'Jobs & Applicant',
    name: 'Rejected List',
    path: '/applications/rejected-list',
    icon: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636">
      </path>
    </svg>`,
  },
  {
    group: 'Jobs & Applicant',
    name: 'Waiting List',
    path: '/applications/waiting-list',
    icon: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z">
      </path>
    </svg>`,
  },
  {
    group: 'Jobs & Applicant',
    name: 'Application List',
    path: '/applications/application-list',
    icon: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
      </path>
    </svg>`,
  },
  {
    group: 'Jobs & Applicant',
    name: 'Applicant Management',
    path: '/applications',
    icon: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
      </path>
    </svg>`,
  },
  {
    group: 'Jobs & Applicant',
    name: 'Process Management',
    path: '/applications/processes',
    icon: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
      </path>
    </svg>`,
  },
  {
    group: 'Jobs & Applicant',
    name: 'Subject Management',
    path: '/applications/subjects',
    icon: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
      d="M12 6l-2-1-6 3v9l6-3 2 1 6-3V3l-6 3z">
    </path>
  </svg>`,
  },
  {
    group: 'Jobs & Applicant',
    name: 'Qualification Management',
    path: '/applications/qualifications',
    icon: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
      d="M12 14l9-5-9-5-9 5 9 5z">
    </path>
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
      d="M12 14v7m0 0l-3-3m3 3l3-3">
    </path>
  </svg>`,
  },
  {
    group: 'Jobs & Applicant',
    name: 'Fee Category Management',
    path: '/jobs/job-details-categories',
    icon: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
      </path>
    </svg>`,
  },
  {
    group: 'Jobs & Applicant',
    name: 'Fee Head Management',
    path: '/jobs/job-details-heads',
    icon: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
      </path>
    </svg>`,
  },

  // Financial Management Group
  {
    group: 'Financial Management',
    name: 'POS',
    path: '/applications/pos',
    icon: `<svg xmlns="http://www.w3.org/2000/svg"
      fill="none"
      stroke="currentColor"
      viewBox="0 0 24 24"
      stroke-width="2">
      <rect x="3" y="4" width="18" height="14" rx="2" ry="2" />
      <path d="M7 8h10" />
      <path d="M7 12h2m2 0h2m2 0h2" />
      <path d="M5 18h14" />
    </svg>`,
  },
  {
    group: 'Financial Management',
    name: 'Transaction Management',
    path: '/applications/transactions',
    icon: `<svg xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
      stroke="currentColor"
      stroke-width="2">
      <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2" />
      <rect x="9" y="3" width="6" height="4" rx="1" />
      <path d="M8 11h8M8 15h6" />
    </svg>`,
  },
  {
    group: 'Financial Management',
    name: 'Candidate Bills',
    path: '/applications/candidate-bills',
    icon: `<svg xmlns="http://www.w3.org/2000/svg"
      fill="none"
      stroke="currentColor"
      viewBox="0 0 24 24"
      stroke-width="2">
      <rect x="3" y="4" width="18" height="14" rx="2" ry="2" />
      <path d="M8 9h8" />
      <path d="M8 13h6" />
      <path d="M8 17h4" />
      <path d="M16 7l3 3-3 3" />
    </svg>`,
  },
  {
    group: 'Financial Management',
    name: 'Client Bills',
    path: '/applications/client-bills',
    icon: `<svg xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
      stroke="currentColor"
      stroke-width="2">
      <path d="M6 2h9l5 5v15a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z" />
      <path d="M14 2v6h6" />
      <path d="M8 11h8M8 15h6" />
      <path d="M8 19h4" />
    </svg>`,
  },

  // HR Management Group
  {
    group: 'HR Management',
    name: 'Employee Management',
    path: '/employees',
    icon: `<svg xmlns="http://www.w3.org/2000/svg"
      fill="none"
      stroke="currentColor"
      viewBox="0 0 24 24"
      stroke-width="2">
      <circle cx="9" cy="7" r="4" />
      <path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2" />
      <rect x="15" y="3" width="6" height="10" rx="1" />
      <path d="M15 7h6" />
    </svg>`,
  },
  {
    group: 'HR Management',
    name: 'Designation Management',
    path: '/employees/designations',
    icon: `<svg xmlns="http://www.w3.org/2000/svg"
      fill="none"
      stroke="currentColor"
      viewBox="0 0 24 24"
      stroke-width="2">
      <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
      <circle cx="8.5" cy="7" r="4" />
      <path d="M20 8v6M23 11h-6" />
    </svg>`,
  },

  // Reports Group
  {
    group: 'Reports',
    name: 'ATS Reports',
    path: '/ats-reports',
    icon: `<svg xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
      stroke="currentColor"
      stroke-width="2">
      <rect x="3" y="3" width="18" height="18" rx="2" />
      <path d="M7 14v4M12 10v8M17 6v12" />
    </svg>`,
  },
  {
    group: 'Reports',
    name: 'Transaction Reports',
    path: '/transaction-reports',
    icon: `<svg xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
      stroke="currentColor"
      stroke-width="2">
      <path d="M3 3v18h18" />
      <rect x="7" y="13" width="3" height="5" rx="1"/>
      <rect x="12" y="9" width="3" height="9" rx="1"/>
      <rect x="17" y="5" width="3" height="13" rx="1"/>
    </svg>`,
  },
  {
    group: 'Reports',
    name: 'Applicant Reports',
    path: '/application-reports',
    icon: `<svg xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
      stroke="currentColor"
      stroke-width="2">
      <path d="M6 3h9l3 3v15a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z"/>
      <path d="M9 10h6M9 14h6M9 18h4"/>
      <path d="M14 6h4v4"/>
    </svg>`,
  },
  {
    group: 'Reports',
    name: 'Process Expiry Reports',
    path: '/expiry-reports',
    icon: `<svg xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
      stroke="currentColor"
      stroke-width="2">
      <rect x="3" y="4" width="18" height="18" rx="2" />
      <path d="M8 2v4M16 2v4M3 10h18" />
      <path d="M9 14h6M9 18h3" />
    </svg>`,
  },
  {
    group: 'Reports',
    name: 'Tasheer Appointment Reports',
    path: '/tasheer-appointment-reports',
    icon: `<svg xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
      stroke="currentColor"
      stroke-width="2">
      <path d="M4 5h16v14H4z" />
      <path d="M8 3v4M16 3v4M4 9h16" />
      <path d="M8 13h8M8 17h5" />
    </svg>`,
  },

  // Settings - Standalone (no group)
  {
    name: 'Setting',
    path: '/settings',
    icon: `<svg xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
      stroke="currentColor"
      stroke-width="2">
      <path d="M12 15a3 3 0 100-6 3 3 0 000 6z"/>
      <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/>
    </svg>`,
  },
]
