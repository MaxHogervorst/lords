<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | SEPA Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration values for SEPA (Single Euro Payments Area) file generation
    | and direct debit processing.
    |
    */

    'creditor' => [
        'name' => env('SEPA_CREDITOR_NAME', ''),
        'account_iban' => env('SEPA_CREDITOR_IBAN', ''),
        'agent_bic' => env('SEPA_CREDITOR_BIC', ''),
        'id' => env('SEPA_CREDITOR_ID', ''),
        'pain' => env('SEPA_CREDITOR_PAIN', ''),
    ],

    'batch' => [
        'max_money_per_batch' => env('SEPA_MAX_MONEY_PER_BATCH', 999999),
        'max_transactions_per_batch' => env('SEPA_MAX_TRANSACTIONS_PER_BATCH', 999999),
        'max_money_per_transaction' => env('SEPA_MAX_MONEY_PER_TRANSACTION', 999999),
    ],

    'collection' => [
        'due_date_weekdays' => env('SEPA_DUE_DATE_WEEKDAYS', 5),
    ],

    'file' => [
        'prefix' => 'GSRC',
        'storage_path' => 'SEPA',
    ],

    'remittance' => [
        'prefix' => 'GSRC Incasso',
    ],

    'mandate' => [
        'sign_date' => '13.10.2012',
        'id_padding' => 10,
    ],
];
