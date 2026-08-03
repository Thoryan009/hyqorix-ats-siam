export const agentLedgerEntries = {
  1: [
    {
      id: 1,
      date: '2026-01-01',
      particular: 'Opening Balance',
      voucher_no: 'OB-001/26',
      demand_letter: '',
      job: '',
      client_name: '',
      dr_amount: 0,
      discount: 0,
      cr_amount: 100000,
      payment_method: '',
      balance: 100000,
      remarks: 'Opening balance forwarded',
    },
    {
      id: 2,
      date: '2026-01-05',
      particular: 'Advanced Deposit',
      voucher_no: 'RC-001/26',
      demand_letter: '',
      job: '',
      client_name: '',
      dr_amount: 0,
      discount: 0,
      cr_amount: 25000,
      payment_method: 'Cash',
      balance: 125000,
      remarks: 'Advance deposit received in cash',
    },
    {
      id: 3,
      date: '2026-03-10',
      particular: 'Deployment Charge',
      voucher_no: 'A001/26',
      demand_letter: 'DL-010',
      job: 'Swimmer - Male',
      client_name: 'SRACO',
      dr_amount: 50000,
      discount: 0,
      cr_amount: 0,
      payment_method: '',
      balance: 75000,
      remarks: 'Bill generated for 10 candidates',
    },
    {
      id: 4,
      date: '2026-03-15',
      particular: 'Partial Payment',
      voucher_no: 'RC-002/26',
      demand_letter: 'DL-010',
      job: 'Swimmer - Male',
      client_name: 'SRACO',
      dr_amount: 0,
      discount: 2000,
      cr_amount: 25000,
      payment_method: 'Cash',
      balance: 100000,
      remarks: 'Partial collection against A001/26',
    },
    {
      id: 5,
      date: '2026-03-18',
      particular: 'Deployment Charge',
      voucher_no: 'A005/26',
      demand_letter: 'DL-008',
      job: 'Driver - Light Vehicle',
      client_name: 'Al Rajhi Group',
      dr_amount: 13500,
      discount: 0,
      cr_amount: 0,
      payment_method: '',
      balance: 86500,
      remarks: 'Bill generated for 3 candidates',
    },
    {
      id: 6,
      date: '2026-03-20',
      particular: 'Final Payment',
      voucher_no: 'RC-003/26',
      demand_letter: 'DL-010',
      job: 'Swimmer - Male',
      client_name: 'SRACO',
      dr_amount: 0,
      discount: 0,
      cr_amount: 25000,
      payment_method: 'Adjust from Balance',
      balance: 111500,
      remarks: 'Final settlement for A001/26',
    },
    {
      id: 7,
      date: '2026-03-22',
      particular: 'Loan Provide',
      voucher_no: 'LN-001/26',
      demand_letter: '',
      job: '',
      client_name: '',
      dr_amount: 15000,
      discount: 0,
      cr_amount: 0,
      payment_method: '',
      balance: 96500,
      remarks: 'Loan provided to agent',
    },
  ],
  2: [
    {
      id: 1,
      date: '2026-01-01',
      particular: 'Opening Balance',
      voucher_no: 'OB-001/26',
      demand_letter: '',
      job: '',
      client_name: '',
      dr_amount: 0,
      discount: 0,
      cr_amount: 80000,
      payment_method: '',
      balance: 80000,
      remarks: 'Opening balance forwarded',
    },
    {
      id: 2,
      date: '2026-02-10',
      particular: 'Advanced Deposit',
      voucher_no: 'RC-001/26',
      demand_letter: '',
      job: '',
      client_name: '',
      dr_amount: 0,
      discount: 0,
      cr_amount: 7500,
      payment_method: 'Cash',
      balance: 87500,
      remarks: 'Advance deposit via bank',
    },
    {
      id: 3,
      date: '2026-03-10',
      particular: 'Deployment Charge',
      voucher_no: 'A008/26',
      demand_letter: 'DL-005',
      job: 'Cleaner - Male',
      client_name: 'Emirates Facility',
      dr_amount: 7000,
      discount: 500,
      cr_amount: 0,
      payment_method: '',
      balance: 80500,
      remarks: 'Bill generated for 2 candidates',
    },
    {
      id: 4,
      date: '2026-03-12',
      particular: 'Partial Payment',
      voucher_no: 'RC-002/26',
      demand_letter: 'DL-005',
      job: 'Cleaner - Male',
      client_name: 'Emirates Facility',
      dr_amount: 0,
      discount: 0,
      cr_amount: 3500,
      payment_method: 'Cash',
      balance: 84000,
      remarks: 'Partial payment received',
    },
  ],
  3: [
    {
      id: 1,
      date: '2026-01-01',
      particular: 'Opening Balance',
      voucher_no: 'OB-001/26',
      demand_letter: '',
      job: '',
      client_name: '',
      dr_amount: 0,
      discount: 0,
      cr_amount: 300000,
      payment_method: '',
      balance: 300000,
      remarks: 'Opening balance forwarded',
    },
    {
      id: 2,
      date: '2026-02-15',
      particular: 'Advanced Deposit',
      voucher_no: 'RC-001/26',
      demand_letter: '',
      job: '',
      client_name: '',
      dr_amount: 0,
      discount: 0,
      cr_amount: 42000,
      payment_method: 'Cash',
      balance: 342000,
      remarks: 'Advance wallet top-up',
    },
    {
      id: 3,
      date: '2026-03-05',
      particular: 'Loan Provide',
      voucher_no: 'LN-001/26',
      demand_letter: '',
      job: '',
      client_name: '',
      dr_amount: 20000,
      discount: 0,
      cr_amount: 0,
      payment_method: '',
      balance: 322000,
      remarks: 'Short-term loan to agent',
    },
  ],
}

function applyLedgerEntryToBalance(balance, entry) {
  const discount = Number(entry.discount) || 0
  const drAmount = Number(entry.dr_amount) || 0
  const crAmount = Number(entry.cr_amount) || 0

  if (drAmount > 0) {
    return balance - Math.max(drAmount - discount, 0)
  }

  if (crAmount > 0) {
    return balance + crAmount + discount
  }

  return balance
}

export function computeLedgerBalances(entries) {
  let balance = 0

  return entries.map((entry) => {
    balance = applyLedgerEntryToBalance(balance, entry)
    return {
      ...entry,
      balance,
    }
  })
}

export function computeLedgerAmounts(entries) {
  let amount = 0

  return entries.map((entry) => {
    amount += Number(entry.dr_amount) || 0
    return {
      ...entry,
      amount,
    }
  })
}

export function filterLedgerByDate(entries, fromDate, toDate) {
  return entries.filter((entry) => {
    if (fromDate && entry.date < fromDate) return false
    if (toDate && entry.date > toDate) return false
    return true
  })
}
