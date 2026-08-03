// invoiceData.js
const invoiceData = {
  bill_no: 'INV-2026-0001',
  total_amount: 50000,
  discount_amount: 5000,
  paid_amount: 30000, // total paid so far
  type: 'candidate', // 'client' or 'candidate'
  payment_method: 'cash', // last payment method
  status: 'due', // 'draft', 'due', 'paid', 'bill-generated'
  payment_date: '2026-01-21', // last payment date
  payment_time: '14:30:00',
  remarks: 'Initial payment received for visa processing.',
  person_id: 'APP-2026-001', // application_id or client_id
  application: {
    id: 1,
    sur_name: 'Rahman',
    given_name: 'Mohammad',
    date_of_birth: '1990-05-20',
    sex: 'Male',
    nationality: 'Bangladeshi',
    qualification: 'Bachelor in Business Administration',
    bd_exp: '2 years',
    overseas_exp: '0 years',
    language: 'English',
    mobile: '+8801812345678',
    email: 'rahman@email.com',
    application_id: 'APP-2026-001',
    nid_no: '199012345678',
    passport_no: 'A1234567',
    place_of_birth: 'Dhaka, Bangladesh',
    remarks: 'Candidate has submitted initial documents',
  },

  workOrderDetails: [
    {
      fee_category: 'Visa Processing Fees',
      fee_names: [
        { fee_name: 'Medical Fee', amount: 15000 },
        { fee_name: 'MOFA Fee', amount: 1000 },
        // { fee_name: 'Visa Submission Fee', amount: 100 },
        // { fee_name: 'Tasheer Fee', amount: 500 },
        // { fee_name: 'Takamol Fee', amount: 1000 },
        { fee_name: 'Certificate Attestation Fee', amount: 1000 },
        { fee_name: 'Insurance Fee', amount: 1000 },
      ],
    },
    {
      fee_category: 'Recruitment Service Charge',
      fee_names: [{ fee_name: 'Service Charge', amount: 10000 }],
    },
  ],

  // Transaction history for the same bill_no
  transaction_history: [
    {
      transaction_id: 'TXN-2026-0001',
      bill_no: 'INV-2026-0001',
      total_amount: 5000,
      paid_amount: 2000, // first installment
      due_amount: 3000,
      current_due: 1000, // final due after all transactions
      type: 'candidate',
      payment_method: 'cash',
      status: 'due',
      payment_date: '2026-01-05',
      payment_time: '10:00:00',
      payment_date_formatted: '05 Jan 2026',
      payment_time_formatted: '10:00 AM',
      payment_date_time_formatted: '05 Jan 2026 10:00 AM',
      remarks: 'First installment paid',
    },
    {
      transaction_id: 'TXN-2026-0002',
      bill_no: 'INV-2026-0001',
      total_amount: 5000,
      paid_amount: 1000, // running paid
      due_amount: 2000,
      current_due: 1000,
      type: 'candidate',
      payment_method: 'bank',
      status: 'due',
      payment_date: '2026-01-12',
      payment_time: '11:30:00',
      payment_date_formatted: '12 Jan 2026',
      payment_time_formatted: '11:30 AM',
      payment_date_time_formatted: '12 Jan 2026 11:30 AM',
      remarks: 'Second installment paid',
    },
    {
      transaction_id: 'TXN-2026-0003',
      bill_no: 'INV-2026-0001',
      total_amount: 5000,
      paid_amount: 1000, // running paid
      due_amount: 1000,
      current_due: 1000,
      type: 'candidate',
      payment_method: 'cash',
      status: 'due',
      payment_date: '2026-01-21',
      payment_time: '14:30:00',
      payment_date_formatted: '21 Jan 2026',
      payment_time_formatted: '02:30 PM',
      payment_date_time_formatted: '21 Jan 2026 02:30 PM',
      remarks: 'Third installment paid',
    },
    {
      transaction_id: 'TXN-2026-0002',
      bill_no: 'INV-2026-0001',
      total_amount: 5000,
      paid_amount: 1000, // running paid
      due_amount: 2000,
      current_due: 1000,
      type: 'candidate',
      payment_method: 'bank',
      status: 'due',
      payment_date: '2026-01-12',
      payment_time: '11:30:00',
      payment_date_formatted: '12 Jan 2026',
      payment_time_formatted: '11:30 AM',
      payment_date_time_formatted: '12 Jan 2026 11:30 AM',
      remarks: 'Second installment paid',
    },
    {
      transaction_id: 'TXN-2026-0003',
      bill_no: 'INV-2026-0001',
      total_amount: 5000,
      paid_amount: 1000, // running paid
      due_amount: 1000,
      current_due: 1000,
      type: 'candidate',
      payment_method: 'cash',
      status: 'due',
      payment_date: '2026-01-21',
      payment_time: '14:30:00',
      payment_date_formatted: '21 Jan 2026',
      payment_time_formatted: '02:30 PM',
      payment_date_time_formatted: '21 Jan 2026 02:30 PM',
      remarks: 'Third installment paid',
    },
  ],
}

export default invoiceData
