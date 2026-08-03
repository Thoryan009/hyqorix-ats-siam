<?php

return [
    'categories' => [
        'main',
        'capital',
        'agent_advanced',
        'sale',
        'bills_receivable',
        'income_receivable',
        'direct_expense',
        'client_recruitment',
        'operating_expense',
        'recruitment_income',
        'client_income',
        'other_income',
        'agent',
        'vendor',
        'principal',
        'client',
        'staff',
        'applicant',
    ],

    /** Expense category `code` → finance account `category` */
    'expense_category_code_to_account_category' => [
        'direct_cost' => 'direct_expense',
        'client_recruitment_cost' => 'client_recruitment',
        'operating_cost' => 'operating_expense',
    ],

    /** Income category `code` → finance account `category` */
    'income_category_code_to_account_category' => [
        'recruitment_income' => 'recruitment_income',
        'client_income' => 'client_income',
        'other_income' => 'other_income',
    ],
];
