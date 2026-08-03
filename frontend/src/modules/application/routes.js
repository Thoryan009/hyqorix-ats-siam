import DashboardLayout from '@/shared/layouts/DashboardLayout.vue'
import ApplicationPage from './pages/ApplicationPage.vue'
import PosPage from './pages/PosPage.vue'
import ProcessPage from './pages/ProcessPage.vue'
import AcknowledgementPage from './pages/AcknowledgementPage.vue'
import TransactionPage from './pages/TransactionPage.vue'
import CandidateBillPage from './pages/CandidateBillPage.vue'
import PrintCandidateInvoicePage from './pages/PrintCandidateInvoicePage.vue'
import ClientBillPage from './pages/ClientBillPage.vue'
import PrintClientInvoicePage from './pages/PrintClientInvoicePage.vue'
import HiringListPage from './pages/HiringListPage.vue'
import RejectedListPage from './pages/RejectedListPage.vue'
import WaitingListPage from './pages/WaitingListPage.vue'
import QualificationPage from './pages/QualificationPage.vue'
import ApplicationListPage from './pages/ApplicationListPage.vue'
import ShortListPage from './pages/ShortListPage.vue'
import EmbassySubmissionPage from './pages/EmbassySubmissionPage.vue'
import EmbassyListPage from './pages/EmbassyListPage.vue'
import AddEmbassyListPage from './pages/AddEmbassyListPage.vue'
import EditEmbassyListPage from './pages/EditEmbassyListPage.vue'
import EmbassyReportPage from './pages/EmbassyReportPage.vue'
import EmbassyReportPrintPage from './pages/EmbassyReportPrintPage.vue'
import EmbassyListPrintPage from './pages/EmbassyListPrintPage.vue'

export default [
  {
    path: '/applications',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Applicant Management',
        component: ApplicationPage,
        meta: {
          permissions: ['application.view'],
        },
      },
      {
        path: 'hiring-list',
        name: 'Hiring List',
        component: HiringListPage,
        meta: { permissions: ['application.view_hiring_list'] },
      },
      {
        path: 'rejected-list',
        name: 'Rejected List',
        component: RejectedListPage,
        meta: { permissions: ['application.view_rejected_list'] },
      },
      {
        path: 'waiting-list',
        name: 'Waiting List',
        component: WaitingListPage,
        meta: { permissions: ['application.view_waiting_list'] },
      },
      {
        path: 'short-list',
        name: 'Short List',
        component: ShortListPage,
        meta: { permissions: ['application.view_short_list'] },
      },
      {
        path: 'application-list',
        name: 'Application List',
        component: ApplicationListPage,
        meta: { permissions: ['application.view_application_list'] },
      },
      {
        path: 'embassy-submission',
        name: 'Embassy Submission',
        component: EmbassySubmissionPage,
        meta: { permissions: ['visa_processing.view'] },
      },
      {
        path: 'embassy-list',
        name: 'Embassy List',
        component: EmbassyListPage,
        meta: { permissions: ['embassy_list.view'] },
      },
      {
        path: 'embassy-list/add',
        name: 'Add Embassy List',
        component: AddEmbassyListPage,
        meta: { permissions: ['embassy_list.create'] },
      },
      {
        path: 'embassy-list/:id/edit',
        name: 'Edit Embassy List',
        component: EditEmbassyListPage,
        meta: { permissions: ['embassy_list.edit'] },
      },

      {
        path: 'embassy-report-pdf/:applicationId',
        name: 'Embassy Report',
        component: EmbassyReportPage,
        meta: { permissions: ['visa_processing.view_report'] },
      },

      {
        path: 'processes',
        name: 'Process Management',
        component: ProcessPage,
        meta: { permissions: ['process.view'] },
      },

      {
        path: 'pos/:applicationId?',
        name: 'POS',
        component: PosPage,
        meta: { permissions: ['pos.view'] },
      },

      {
        path: 'transactions',
        name: 'Transaction Management',
        component: TransactionPage,
        meta: { permissions: ['transaction.view'] },
      },

      {
        path: 'qualifications',
        name: 'Qualification Management',
        component: QualificationPage,
        meta: { permissions: ['qualification.view'] },
      },

      {
        path: 'candidate-bills',
        name: 'Candidate Bills',
        component: CandidateBillPage,
        meta: { permissions: ['candidate_bill.view'] },
      },
      {
        path: 'client-bills',
        name: 'Client Bills',
        component: ClientBillPage,
        meta: { permissions: ['client_bill.view'] },
      },
      {
        path: 'subjects',
        name: 'Subject Management',
        component: () => import('./pages/SubjectPage.vue'),
        meta: { permissions: ['subject.view'] },
      },
    ],
  },
  {
    path: '/applications/embassy-list/:id/print',
    name: 'Embassy List Print',
    component: EmbassyListPrintPage,
    meta: { requiresAuth: true, permissions: ['embassy_list.view'] },
  },
  {
    path: '/applications/embassy-report-pdf/:applicationId/print',
    name: 'Embassy Report Print',
    component: EmbassyReportPrintPage,
    meta: { requiresAuth: true, permissions: ['visa_processing.print_report'] },
  },
  {
    path: '/generate-bio-data',
    name: 'Generate BioData',
    component: () => import('./pages/generateBioData/GenerateBioData.vue'),
    meta: { permissions: ['application.generate_biodata'] },
  },
  {
    path: '/pos/print-candidate-invoice/:transactionId',
    name: 'Print Candidate Invoice',
    component: PrintCandidateInvoicePage,
    meta: { permissions: ['pos.view'] },
  },
  {
    path: '/print-client-invoice/:billNo/:job_id',
    name: 'Print Client Invoice',
    component: PrintClientInvoicePage,
    meta: { permissions: ['client_bill.view'] },
  },
  {
    path: '/acknowledgement-doc',
    name: 'Acknowledgement Doc',
    component: AcknowledgementPage,
    meta: { permissions: ['application.view_acknowledgement_document'] },
  },
]
