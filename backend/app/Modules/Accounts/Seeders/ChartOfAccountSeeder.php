<?php

namespace App\Modules\Accounts\Seeders;

use App\Modules\Accounts\Models\ChartOfAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // Assets
            ['code' => '1000', 'name' => 'Cash in Hand', 'type' => 'Asset', 'financial_statement' => 'Balance Sheet', 'normal_balance' => 'debit'],
            ['code' => '1010', 'name' => 'Bank Account', 'type' => 'Asset', 'financial_statement' => 'Balance Sheet', 'normal_balance' => 'debit'],
            ['code' => '1100', 'name' => 'Accounts Receivable - Client', 'type' => 'Asset', 'financial_statement' => 'Balance Sheet', 'normal_balance' => 'debit'],
            ['code' => '1110', 'name' => 'Accounts Receivable - Agent', 'type' => 'Asset', 'financial_statement' => 'Balance Sheet', 'normal_balance' => 'debit'],
            ['code' => '1120', 'name' => 'Accounts Receivable - Candidate', 'type' => 'Asset', 'financial_statement' => 'Balance Sheet', 'normal_balance' => 'debit'],
            ['code' => '1130', 'name' => 'Accounts Receivable - Principal', 'type' => 'Asset', 'financial_statement' => 'Balance Sheet', 'normal_balance' => 'debit'],
            ['code' => '1140', 'name' => 'Staff Advance', 'type' => 'Asset', 'financial_statement' => 'Balance Sheet', 'normal_balance' => 'debit'],
            ['code' => '1150', 'name' => 'Vendor Advance', 'type' => 'Asset', 'financial_statement' => 'Balance Sheet', 'normal_balance' => 'debit'],
            ['code' => '1160', 'name' => 'Prepaid Expense', 'type' => 'Asset', 'financial_statement' => 'Balance Sheet', 'normal_balance' => 'debit'],
            ['code' => '1170', 'name' => 'Other Receivable', 'type' => 'Asset', 'financial_statement' => 'Balance Sheet', 'normal_balance' => 'debit'],
            ['code' => '1200', 'name' => 'Security Deposit', 'type' => 'Asset', 'financial_statement' => 'Balance Sheet', 'normal_balance' => 'debit'],
            ['code' => '1300', 'name' => 'Office Equipment', 'type' => 'Asset', 'financial_statement' => 'Balance Sheet', 'normal_balance' => 'debit'],
            ['code' => '1310', 'name' => 'Furniture & Fixtures', 'type' => 'Asset', 'financial_statement' => 'Balance Sheet', 'normal_balance' => 'debit'],
            ['code' => '1320', 'name' => 'Computer & Software', 'type' => 'Asset', 'financial_statement' => 'Balance Sheet', 'normal_balance' => 'debit'],
            ['code' => '1390', 'name' => 'Accumulated Depreciation', 'type' => 'Contra Asset', 'financial_statement' => 'Balance Sheet', 'normal_balance' => 'credit'],

            // Liabilities
            ['code' => '2000', 'name' => 'Accounts Payable - Vendor', 'type' => 'Liability', 'financial_statement' => 'Balance Sheet', 'normal_balance' => 'credit'],
            ['code' => '2010', 'name' => 'Accounts Payable - Principal', 'type' => 'Liability', 'financial_statement' => 'Balance Sheet', 'normal_balance' => 'credit'],
            ['code' => '2020', 'name' => 'Accounts Payable - Agent', 'type' => 'Liability', 'financial_statement' => 'Balance Sheet', 'normal_balance' => 'credit'],
            ['code' => '2030', 'name' => 'Client Advance / Unearned Revenue', 'type' => 'Liability', 'financial_statement' => 'Balance Sheet', 'normal_balance' => 'credit'],
            ['code' => '2040', 'name' => 'Candidate Advance / Unearned Revenue', 'type' => 'Liability', 'financial_statement' => 'Balance Sheet', 'normal_balance' => 'credit'],
            ['code' => '2050', 'name' => 'Agent Advance / Unearned Revenue', 'type' => 'Liability', 'financial_statement' => 'Balance Sheet', 'normal_balance' => 'credit'],
            ['code' => '2060', 'name' => 'Staff Payable / Reimbursement', 'type' => 'Liability', 'financial_statement' => 'Balance Sheet', 'normal_balance' => 'credit'],
            ['code' => '2070', 'name' => 'Accrued Expenses', 'type' => 'Liability', 'financial_statement' => 'Balance Sheet', 'normal_balance' => 'credit'],
            ['code' => '2100', 'name' => 'Short-term Loan', 'type' => 'Liability', 'financial_statement' => 'Balance Sheet', 'normal_balance' => 'credit'],
            ['code' => '2110', 'name' => 'Long-term Loan', 'type' => 'Liability', 'financial_statement' => 'Balance Sheet', 'normal_balance' => 'credit'],

            // Equity
            ['code' => '3000', 'name' => "Owner's Capital", 'type' => 'Equity', 'financial_statement' => 'Balance Sheet', 'normal_balance' => 'credit'],
            ['code' => '3010', 'name' => "Owner's Drawings", 'type' => 'Contra Equity', 'financial_statement' => 'Balance Sheet', 'normal_balance' => 'debit'],

            // Revenue
            ['code' => '4010', 'name' => 'Recruitment Service Revenue - Candidate', 'type' => 'Revenue', 'financial_statement' => 'Gross Profit', 'normal_balance' => 'credit'],
            ['code' => '4011', 'name' => 'Recruitment Revenue Refund - Candidate', 'type' => 'Contra Revenue', 'financial_statement' => 'Gross Profit', 'normal_balance' => 'debit'],
            ['code' => '4020', 'name' => 'Recruitment Service Revenue - Agent', 'type' => 'Revenue', 'financial_statement' => 'Gross Profit', 'normal_balance' => 'credit'],
            ['code' => '4021', 'name' => 'Recruitment Revenue Refund - Agent', 'type' => 'Contra Revenue', 'financial_statement' => 'Gross Profit', 'normal_balance' => 'debit'],
            ['code' => '4030', 'name' => 'Visa Processing Revenue', 'type' => 'Revenue', 'financial_statement' => 'Gross Profit', 'normal_balance' => 'credit'],
            ['code' => '4040', 'name' => 'Ticket Service Revenue', 'type' => 'Revenue', 'financial_statement' => 'Gross Profit', 'normal_balance' => 'credit'],
            ['code' => '4050', 'name' => 'Other Recruitment Revenue', 'type' => 'Revenue', 'financial_statement' => 'Gross Profit', 'normal_balance' => 'credit'],
            ['code' => '4100', 'name' => 'Client Commission Income', 'type' => 'Other Operating Revenue', 'financial_statement' => 'Income Statement', 'normal_balance' => 'credit'],
            ['code' => '4110', 'name' => 'Consultancy Income', 'type' => 'Other Operating Revenue', 'financial_statement' => 'Income Statement', 'normal_balance' => 'credit'],
            ['code' => '4120', 'name' => 'Interest Income', 'type' => 'Other Income', 'financial_statement' => 'Income Statement', 'normal_balance' => 'credit'],

            // Direct Cost A
            ['code' => '5000', 'name' => 'Medical Cost', 'type' => 'Direct Cost A', 'financial_statement' => 'Gross Profit', 'normal_balance' => 'debit'],
            ['code' => '5010', 'name' => 'Manpower / BMET Cost', 'type' => 'Direct Cost A', 'financial_statement' => 'Gross Profit', 'normal_balance' => 'debit'],
            ['code' => '5020', 'name' => 'Air Ticket Cost', 'type' => 'Direct Cost A', 'financial_statement' => 'Gross Profit', 'normal_balance' => 'debit'],
            ['code' => '5030', 'name' => 'Visa Cost', 'type' => 'Direct Cost A', 'financial_statement' => 'Gross Profit', 'normal_balance' => 'debit'],
            ['code' => '5040', 'name' => 'Police Clearance / Documentation Cost', 'type' => 'Direct Cost A', 'financial_statement' => 'Gross Profit', 'normal_balance' => 'debit'],
            ['code' => '5050', 'name' => 'Other Candidate Direct Cost', 'type' => 'Direct Cost A', 'financial_statement' => 'Gross Profit', 'normal_balance' => 'debit'],

            // Direct Cost B
            ['code' => '5100', 'name' => 'Interview Venue Rent', 'type' => 'Direct Cost B', 'financial_statement' => 'Gross Profit', 'normal_balance' => 'debit'],
            ['code' => '5110', 'name' => 'Trade Test Cost', 'type' => 'Direct Cost B', 'financial_statement' => 'Gross Profit', 'normal_balance' => 'debit'],
            ['code' => '5120', 'name' => 'Delegate Hotel Accommodation', 'type' => 'Direct Cost B', 'financial_statement' => 'Gross Profit', 'normal_balance' => 'debit'],
            ['code' => '5130', 'name' => 'Delegate Local Transport / Interview Cost', 'type' => 'Direct Cost B', 'financial_statement' => 'Gross Profit', 'normal_balance' => 'debit'],
            ['code' => '5140', 'name' => 'Other Interview Direct Cost', 'type' => 'Direct Cost B', 'financial_statement' => 'Gross Profit', 'normal_balance' => 'debit'],

            // Operating expenses
            ['code' => '6000', 'name' => 'Salary & Wages', 'type' => 'Operating Expense', 'financial_statement' => 'Income Statement', 'normal_balance' => 'debit'],
            ['code' => '6010', 'name' => 'Office Rent', 'type' => 'Operating Expense', 'financial_statement' => 'Income Statement', 'normal_balance' => 'debit'],
            ['code' => '6020', 'name' => 'Utilities', 'type' => 'Operating Expense', 'financial_statement' => 'Income Statement', 'normal_balance' => 'debit'],
            ['code' => '6030', 'name' => 'Telephone & Internet', 'type' => 'Operating Expense', 'financial_statement' => 'Income Statement', 'normal_balance' => 'debit'],
            ['code' => '6040', 'name' => 'Office Supplies', 'type' => 'Operating Expense', 'financial_statement' => 'Income Statement', 'normal_balance' => 'debit'],
            ['code' => '6050', 'name' => 'Marketing & Promotion', 'type' => 'Operating Expense', 'financial_statement' => 'Income Statement', 'normal_balance' => 'debit'],
            ['code' => '6060', 'name' => 'Travel & Conveyance', 'type' => 'Operating Expense', 'financial_statement' => 'Income Statement', 'normal_balance' => 'debit'],
            ['code' => '6070', 'name' => 'Professional / Legal Fees', 'type' => 'Operating Expense', 'financial_statement' => 'Income Statement', 'normal_balance' => 'debit'],
            ['code' => '6080', 'name' => 'Bank Charges', 'type' => 'Operating Expense', 'financial_statement' => 'Income Statement', 'normal_balance' => 'debit'],
            ['code' => '6090', 'name' => 'Depreciation Expense', 'type' => 'Operating Expense', 'financial_statement' => 'Income Statement', 'normal_balance' => 'debit'],
            ['code' => '6100', 'name' => 'Bad Debt Expense', 'type' => 'Operating Expense', 'financial_statement' => 'Income Statement', 'normal_balance' => 'debit'],
            ['code' => '6110', 'name' => 'Repair & Maintenance', 'type' => 'Operating Expense', 'financial_statement' => 'Income Statement', 'normal_balance' => 'debit'],
            ['code' => '6120', 'name' => 'Miscellaneous Operating Expense', 'type' => 'Operating Expense', 'financial_statement' => 'Income Statement', 'normal_balance' => 'debit'],
            ['code' => '6200', 'name' => 'Interest Expense', 'type' => 'Finance Cost', 'financial_statement' => 'Income Statement', 'normal_balance' => 'debit'],
            ['code' => '6300', 'name' => 'Flight Cancellation / Refund Loss', 'type' => 'Other Expense', 'financial_statement' => 'Income Statement', 'normal_balance' => 'debit'],
        ];

        foreach ($accounts as $index => $account) {
            ChartOfAccount::query()->updateOrCreate(
                ['code' => $account['code']],
                array_merge($account, [
                    'status' => 'active',
                    'sort_order' => $index + 1,
                    'description' => null,
                ])
            );
        }
    }
}
