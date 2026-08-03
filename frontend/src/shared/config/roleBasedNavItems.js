const recruiter = [
  'Dashboard',
  'Country Management',
  'Client Management',
  'Agent Management',
  'Principal Management',
  'Demand Letter Management',
  'Demand Letter Details Management',
  'Job Management',
  'ATS',
  'Single ATS',
  'Hiring List',
  'Rejected List',
  'Waiting List',
  'Application List',
  'Applicant Management',
  'Process Management',
  'Acknowledgement Doc',
  'Setting',
  'ATS Reports',

  'Applicant Reports',
  'Process Expiry Reports',
  'Tasheer Appointment Reports',
  'Subject Management',
  'Qualification Management',
]

const accountant = [
  'Dashboard',
  'POS',
  'Transaction Management',
  'Candidate Bills',
  'Client Bills',
  'Transaction Reports',
  'Print Invoice',
  'Setting',
]

const client = [
  'Dashboard',
  'Job Management',
  'Applicant Management',
  'Process Management',
  'Setting',
  'ATS',
  'Single ATS',
]

const agent = ['Dashboard', 'Applicant Management', 'Process Management', 'ATS', 'Single ATS']

const admin = [...recruiter, 'Employee Management', 'Designation Management']

const super_admin = [...recruiter, ...admin, ...accountant]

const roleBasedNavItems = {
  recruiter,
  admin,
  accountant,
  super_admin,
  client,
  agent,
}

export default roleBasedNavItems
