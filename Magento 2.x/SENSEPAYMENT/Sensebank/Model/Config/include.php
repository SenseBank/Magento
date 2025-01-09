<?php

define('SENSEPAYMENT_PAYMENT_NAME', 'Sense Bank');

define('SENSEPAYMENT_PROD_URL' , 'https://pay.sensebank.com.ua/ml/v1/payment/');
define('SENSEPAYMENT_TEST_URL' , 'https://sand.sensebank.com.ua/ml/v1/payment/');

define('SENSEPAYMENT_ENABLE_LOGGING', false);
define('SENSEPAYMENT_ENABLE_FISCALE_OPTIONS', false);

define('SENSEPAYMENT_ENABLE_ACTION_TAB', false);

define('SENSEPAYMENT_MEASUREMENT_NAME', 'шт'); //FFD v1.05
define('SENSEPAYMENT_MEASUREMENT_CODE', 0); //FFD v1.2

define('SENSEPAYMENT_SKIP_CONFIRMATION_STEP', true);
define('SENSEPAYMENT_CUSTOMER_EMAIL_SEND', true); //PLUG-4667
define('SENSEPAYMENT_ENABLE_CALLBACK', false);

define('SENSEPAYMENT_CURRENCY_CODES', serialize(array(
    'USD' => '840',
    'UAH' => '980',
    'RUB' => '643',
    'RON' => '946',
    'KZT' => '398',
    'KGS' => '417',
    'JPY' => '392',
    'GBR' => '826',
    'EUR' => '978',
    'CNY' => '156',
    'BYR' => '974',
    'BYN' => '933'
)));
