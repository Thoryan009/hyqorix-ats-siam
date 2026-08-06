import { normalizeExpensePayment } from '../data/expensePaymentData'

export function mapBillEntryFromApi(row) {
  return normalizeExpensePayment({
    ...row,
    payment_date: row.payment_date ?? '',
    created_at: row.created_at ?? '',
    requested_at: row.requested_at ?? row.created_at ?? '',
  })
}

export function mapBillEntriesFromApi(rows = []) {
  return rows.map(mapBillEntryFromApi)
}

export function extractBillEntryRows(payload) {
  if (!payload) return []
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload.data)) return payload.data
  if (Array.isArray(payload.data?.data)) return payload.data.data
  if (payload.data && !Array.isArray(payload.data)) return [payload.data]
  return []
}

export function extractBillEntryRow(payload) {
  const rows = extractBillEntryRows(payload)
  return rows[0] ?? payload?.data ?? payload ?? null
}

export function buildBillEntryPayload(payload, accountData = {}) {
  const receiptFiles = normalizeReceiptFiles(payload.receipt_path)
  const entryType = payload.entry_type === 'asset_purchase' ? 'asset_purchase' : 'expense_bill'

  const base = {
    entry_type: entryType,
    amount: Number(payload.amount),
    payment_date: payload.payment_date,
    payment_method: payload.payment_method || 'cash',
    particular: payload.particular?.trim() || '',
    reference_no: payload.reference_no?.trim() || '',
    remarks: payload.remarks?.trim() || '',
    receipt_path: receiptFiles.length ? receiptFiles : undefined,
    requested_by_id: payload.requested_by_id ?? payload.requested_by?.id ?? null,
    requested_by_name: payload.requested_by_name ?? payload.requested_by?.name ?? '',
    requested_by_email: payload.requested_by_email ?? payload.requested_by?.email ?? '',
    requested_by_type: payload.requested_by_type ?? payload.requested_by?.type ?? '',
    status: payload.status === 'pending' ? 'pending' : 'submitted',
  }

  if (entryType === 'asset_purchase') {
    return {
      ...base,
      asset_account_id: Number(payload.asset_account_id),
    }
  }

  return {
    ...base,
    category_id: Number(payload.category_id),
    head_id: Number(payload.head_id),
    linked_account_category: payload.linked_account_category || accountData.linked_account_category || '',
    linked_account_id: payload.linked_account_id || accountData.linked_account_id || null,
    linked_account_name: accountData.linked_account_name || '',
    linked_account_type: accountData.linked_account_type || '',
    expense_cost_type: accountData.expense_cost_type || '',
    expense_cost_account_id: accountData.expense_cost_account_id || null,
    expense_cost_account_name: accountData.expense_cost_account_name || '',
    expense_cost_category_name: accountData.expense_cost_category_name || '',
    application_id: payload.application_id ? Number(payload.application_id) : null,
    demand_letter_id: payload.demand_letter_id ? Number(payload.demand_letter_id) : null,
  }
}

export function buildBillEntryBatchPayload(payload, accountData = {}) {
  return {
    ...buildBillEntryPayload(payload, accountData),
    job_id: Number(payload.job_id),
    application_ids: (payload.application_ids || []).map(Number).filter(Boolean),
    batch_ref: payload.reference_no?.trim() || '',
  }
}

export function buildBillEntryMultiHeadPayload(payload) {
  const receiptFiles = normalizeReceiptFiles(payload.receipt_path)

  return {
    category_id: Number(payload.category_id),
    payment_date: payload.payment_date,
    payment_method: payload.payment_method || 'cash',
    reference_no: payload.reference_no?.trim() || '',
    remarks: payload.remarks?.trim() || '',
    receipt_path: receiptFiles.length ? receiptFiles : undefined,
    requested_by_id: payload.requested_by_id ?? payload.requested_by?.id ?? null,
    requested_by_name: payload.requested_by_name ?? payload.requested_by?.name ?? '',
    requested_by_email: payload.requested_by_email ?? payload.requested_by?.email ?? '',
    requested_by_type: payload.requested_by_type ?? payload.requested_by?.type ?? '',
    status: payload.status === 'pending' ? 'pending' : 'submitted',
    lines: (payload.lines || []).map((line) => ({
      head_id: Number(line.head_id),
      amount: Number(line.amount),
      bill_no: line.bill_no?.trim() || line.voucher_no?.trim() || '',
      voucher_no: line.voucher_no?.trim() || line.bill_no?.trim() || '',
      reference_no: line.reference_no?.trim() || line.bill_no?.trim() || '',
      particular: line.particular?.trim() || '',
      linked_account_category: line.linked_account_category || '',
      linked_account_id: line.linked_account_id || null,
      linked_account_name: line.linked_account_name || '',
      linked_account_type: line.linked_account_type || '',
      expense_cost_type: line.expense_cost_type || '',
      expense_cost_account_id: line.expense_cost_account_id || null,
      expense_cost_account_name: line.expense_cost_account_name || '',
      expense_cost_category_name: line.expense_cost_category_name || '',
    })),
  }
}

export function buildPayableBillPaymentPayload(payload) {
  return {
    pay_amount: Number(payload.pay_amount ?? payload.amount),
    particular: payload.particular?.trim() || '',
    reference_no: payload.reference_no?.trim() || '',
    voucher_no: payload.voucher_no?.trim() || '',
    remarks: payload.remarks?.trim() || '',
    approval_remarks: payload.approval_remarks?.trim() || '',
    payment_method: payload.payment_method,
    payment_account_category: payload.payment_account_category || '',
    payment_account_type: payload.payment_account_type || '',
    payment_account_id: payload.payment_account_id || null,
    payment_account_name: payload.payment_account_name || '',
    approved_by: payload.approved_by?.trim() || 'Accountant',
  }
}

export function buildBillEntryUpdatePayload(payload) {
  return {
    manual_approval_manager_id: payload.manual_approval_manager_id || null,
    manual_approval_path:
      payload.manual_approval_path instanceof File ? payload.manual_approval_path : undefined,
    amount: Number(payload.amount),
    ...(payload.pay_amount !== undefined && payload.pay_amount !== null && payload.pay_amount !== ''
      ? { pay_amount: Number(payload.pay_amount) }
      : {}),
    particular: payload.particular?.trim() || '',
    reference_no: payload.reference_no?.trim() || '',
    voucher_no: payload.voucher_no?.trim() || '',
    remarks: payload.remarks?.trim() || '',
    approval_remarks: payload.approval_remarks?.trim() || '',
    payment_method: payload.payment_method,
    payment_account_category: payload.payment_account_category || '',
    payment_account_type: payload.payment_account_type || '',
    payment_account_id: payload.payment_account_id || null,
    payment_account_name: payload.payment_account_name || '',
    approved_by: payload.approved_by?.trim() || 'Accountant',
  }
}

/**
 * Dynamically build FormData from a payload object.
 * Skips empty values and *_preview keys; appends File / arrays as-is.
 */
export function toDynamicFormData(payload = {}) {
  const formData = new FormData()

  Object.entries(payload).forEach(([key, value]) => {
    if (value === null || value === undefined || value === '') return
    if (key.endsWith('_preview')) return

    if (Array.isArray(value)) {
      value.forEach((item, index) => {
        if (item === null || item === undefined || item === '') return

        if (item instanceof File) {
          formData.append(`${key}[${index}]`, item)
          return
        }

        if (typeof item === 'object') {
          Object.entries(item).forEach(([nestedKey, nestedValue]) => {
            if (nestedValue === null || nestedValue === undefined || nestedValue === '') return
            formData.append(`${key}[${index}][${nestedKey}]`, nestedValue)
          })
          return
        }

        formData.append(`${key}[${index}]`, item)
      })
      return
    }

    formData.append(key, value)
  })

  return formData
}

export function hasUploadFile(payload = {}) {
  return Object.values(payload).some((value) => {
    if (value instanceof File) return true
    if (Array.isArray(value)) return value.some((item) => item instanceof File)
    return false
  })
}

function normalizeReceiptFiles(value) {
  if (Array.isArray(value)) {
    return value.filter((item) => item instanceof File)
  }
  if (value instanceof File) {
    return [value]
  }
  return []
}
