import { isAdjustmentTransactionType } from '../data/accountTransactionData'

export function mapAccountTypeTransactionFromApi(row) {
  return {
    id: row.id,
    transaction_type: row.transaction_type,
    amount: Number(row.amount) || 0,
    date: row.date ?? row.transaction_date ?? '',
    particular: row.particular ?? '',
    reference_no: row.reference_no ?? '',
    remarks: row.remarks ?? '',
    account_category: row.account_category ?? '',
    main_account_type: row.main_account_type ?? '',
    account_id: row.account_id ?? null,
    account_label: row.account_label ?? '',
    from_account_category: row.from_account_category ?? '',
    from_main_account_type: row.from_main_account_type ?? '',
    from_account_id: row.from_account_id ?? null,
    from_account_label: row.from_account_label ?? '',
    to_account_category: row.to_account_category ?? '',
    to_main_account_type: row.to_main_account_type ?? '',
    to_account_id: row.to_account_id ?? null,
    to_account_label: row.to_account_label ?? '',
    voucher_no: row.voucher_no ?? '',
    created_at: row.created_at ?? '',
  }
}

export function mapAccountTypeTransactionsFromApi(rows = []) {
  return rows.map(mapAccountTypeTransactionFromApi)
}

export function buildAccountTypeTransactionPayload(payload) {
  const base = {
    transaction_type: payload.transactionType,
    date: payload.date,
    amount: Number(payload.amount),
    particular: payload.particular?.trim() || '',
    reference_no: payload.referenceNo?.trim() || '',
    remarks: payload.remarks?.trim() || '',
  }

  if (isAdjustmentTransactionType(payload.transactionType)) {
    return {
      ...base,
      account_category: payload.accountCategory,
      main_account_type: payload.mainAccountType || '',
      account_id: Number(payload.accountId),
    }
  }

  const extraAssetAccountId = payload.assetAccountId ? Number(payload.assetAccountId) : null
  const extraLiabilitiesAccountId = payload.liabilitiesAccountId
    ? Number(payload.liabilitiesAccountId)
    : null
  const extraOwnersEquityAccountId = payload.ownersEquityAccountId
    ? Number(payload.ownersEquityAccountId)
    : null

  return {
    ...base,
    from_account_category: payload.fromAccountCategory,
    from_main_account_type: payload.fromMainAccountType || '',
    from_account_id: Number(payload.fromAccountId),
    to_account_category: payload.toAccountCategory,
    to_main_account_type: payload.toMainAccountType || '',
    to_account_id: Number(payload.toAccountId),
    ...(extraAssetAccountId ? { asset_account_id: extraAssetAccountId } : {}),
    ...(extraLiabilitiesAccountId ? { liabilities_account_id: extraLiabilitiesAccountId } : {}),
    ...(extraOwnersEquityAccountId
      ? { owners_equity_account_id: extraOwnersEquityAccountId }
      : {}),
    ...(payload.transactionDirection
      ? { transaction_direction: payload.transactionDirection }
      : {}),
  }
}

export function extractAccountTypeTransactionRows(payload) {
  if (!payload) return []
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload.data)) return payload.data
  if (payload.data && !Array.isArray(payload.data)) return [payload.data]
  return []
}

export function extractAccountTypeTransactionRow(payload) {
  const rows = extractAccountTypeTransactionRows(payload)
  return rows[0] ?? payload?.data ?? payload ?? null
}
