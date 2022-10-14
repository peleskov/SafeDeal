<?php

return [
    'merchant_apikey' => [
        'xtype' => 'textfield',
        'value' => '',
        'area' => 'safedeal_payments',
    ],
    'merchant_currency' => [
        'xtype' => 'textfield',
        'value' => 'RUB',
        'area' => 'safedeal_payments',
    ],
    'merchant_id' => [
        'xtype' => 'numberfield',
        'value' => '',
        'area' => 'safedeal_payments',
    ],
    'merchant_ttl' => [
        'xtype' => 'numberfield',
        'value' => '',
        'area' => 'safedeal_payments',
    ],
    'merchant_host' => [
        'xtype' => 'textfield',
        'value' => 'https://pay1time.com/',
        'area' => 'safedeal_payments',
    ],
    'merchant_invoice_url' => [
        'xtype' => 'textfield',
        'value' => 'api/payments/',
        'area' => 'safedeal_payments',
    ],
    'merchant_payform_url' => [
        'xtype' => 'textfield',
        'value' => 'payWithoutForm/',
        'area' => 'safedeal_payments',
    ],
    'merchant_result_url' => [
        'xtype' => 'textfield',
        'value' => 'assets/components/safedeal/payment/callback.php',
        'area' => 'safedeal_payments',
    ],
];