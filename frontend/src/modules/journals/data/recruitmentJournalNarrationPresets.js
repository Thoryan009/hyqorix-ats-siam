export const recruitmentJournalNarrationPresets = [
  {
    id: 'capital_assets',
    categoryKey: 'journals.chat_preset_cat_capital',
    items: [
      { id: 'owner_capital', text: 'Owner introduced capital' },
      { id: 'computers_cash', text: 'Bought computers for cash' },
      { id: 'bank_loan', text: 'Bank loan received' },
      { id: 'owner_withdraw', text: 'Owner withdrew cash' },
      { id: 'depreciation', text: 'Depreciation recorded' },
      { id: 'security_deposit', text: 'Refundable security deposit paid' },
    ],
  },
  {
    id: 'client_commission',
    categoryKey: 'journals.chat_preset_cat_client',
    items: [
      { id: 'client_commission_credit', text: 'Client commission invoice on credit' },
      { id: 'client_commission_invoice', text: 'Client commission invoice' },
      { id: 'client_advance', text: 'Client advance received' },
      { id: 'client_advance_applied', text: 'Client advance applied against commission invoice' },
      { id: 'client_paid_balance', text: 'Client paid remaining commission receivable' },
    ],
  },
  {
    id: 'medical_tickets',
    categoryKey: 'journals.chat_preset_cat_medical',
    items: [
      { id: 'medical_credit', text: 'Medical on credit' },
      { id: 'medical_paid', text: 'Medical vendor paid' },
      { id: 'tickets_credit', text: 'Tickets bought on credit' },
      { id: 'ticket_advance', text: 'Ticket vendor advance' },
      { id: 'vendor_advance_applied', text: 'Vendor advance applied' },
      { id: 'ticket_balance_paid', text: 'Ticket vendor balance paid' },
      { id: 'ticket_cancel_credit', text: 'Ticket cancellation supplier credit' },
      { id: 'ticket_cancel_penalty', text: 'Ticket cancellation penalty' },
    ],
  },
  {
    id: 'principal_visa',
    categoryKey: 'journals.chat_preset_cat_principal',
    items: [
      { id: 'visa_credit', text: 'Principal arranged visa on credit' },
      { id: 'principal_paid', text: 'Principal paid' },
      { id: 'interview_venue', text: 'Interview venue paid' },
      { id: 'trade_test', text: 'Trade test paid' },
      { id: 'delegate_hotel', text: 'Delegate hotel on credit' },
      { id: 'hotel_paid', text: 'Hotel bill paid' },
    ],
  },
  {
    id: 'staff',
    categoryKey: 'journals.chat_preset_cat_staff',
    items: [
      { id: 'staff_advance', text: 'Staff advance issued' },
      { id: 'staff_bmet', text: 'Staff adjusted BMET cost' },
      { id: 'staff_return', text: 'Staff returned unused balance' },
      { id: 'salary_paid', text: 'Salary paid' },
    ],
  },
  {
    id: 'agent_candidate',
    categoryKey: 'journals.chat_preset_cat_agent',
    items: [
      { id: 'agent_advance', text: 'Agent advance received for recruitment service' },
      { id: 'agent_fee', text: 'Agent recruitment fee earned' },
      { id: 'direct_fee_credit', text: 'Direct candidate recruitment fee charged on credit' },
      { id: 'direct_fee_paid', text: 'Direct candidate paid recruitment fee' },
      { id: 'candidate_advance', text: 'Candidate advance received' },
      { id: 'old_fee', text: 'Old candidate fee recognized' },
      { id: 'refund_direct', text: 'Recruitment revenue refund paid to direct candidate' },
      { id: 'refund_agent', text: 'Recruitment revenue refund paid to agent' },
    ],
  },
  {
    id: 'consultancy_finance',
    categoryKey: 'journals.chat_preset_cat_finance',
    items: [
      { id: 'consultancy_accrued', text: 'Consultancy fee accrued' },
      { id: 'consultancy_received', text: 'Consultancy fee received' },
      { id: 'interest_received', text: 'Interest received' },
      { id: 'loan_principal', text: 'Loan principal repaid' },
      { id: 'loan_interest', text: 'Loan interest paid' },
      { id: 'cash_to_bank', text: 'Transferred cash to bank before refund payments' },
    ],
  },
  {
    id: 'expenses_accruals',
    categoryKey: 'journals.chat_preset_cat_expenses',
    items: [
      { id: 'rent_accrued', text: 'Office rent accrued' },
      { id: 'rent_paid', text: 'Accrued rent paid' },
      { id: 'insurance_prepaid', text: 'Prepaid insurance paid' },
      { id: 'prepayment_consumed', text: 'One month prepayment consumed' },
      { id: 'utility_accrued', text: 'Utility bill accrued' },
      { id: 'bad_debt', text: 'Bad candidate receivable written off' },
    ],
  },
]

export function buildPresetChatPrompt(narration, t) {
  return t('journals.chat_preset_prompt', { narration })
}
