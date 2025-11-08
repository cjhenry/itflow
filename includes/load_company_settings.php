<?php
$sql = mysqli_query($mysqli, "SELECT * FROM companies, settings WHERE settings.company_id = companies.company_id AND companies.company_id = 1");
$row = mysqli_fetch_array($sql);

$session_company_name = $row['company_name'];
$session_company_country = $row['company_country'];
$session_company_locale = $row['company_locale'];
$session_company_currency = $row['company_currency'];

// Map currency code to appropriate locale for proper formatting
$currency_locale_map = [
    'USD' => 'en_US',
    'EUR' => 'en_EU',
    'GBP' => 'en_GB',
    'JPY' => 'ja_JP',
    'CAD' => 'en_CA',
    'AUD' => 'en_AU',
    'CHF' => 'de_CH',
    'CNY' => 'zh_CN',
    'INR' => 'en_IN',
    'AED' => 'ar_AE',
    'SAR' => 'ar_SA',
    'KYD' => 'en_KY',
    'BSD' => 'en_BS',
    'JMD' => 'en_JM',
    'TTD' => 'en_TT',
    'BBD' => 'en_BB',
    'XCD' => 'en_KN',
];

// Use currency-appropriate locale for formatter instead of company locale
$currency_locale = $currency_locale_map[$session_company_currency] ?? 'en_US';
$currency_format = numfmt_create($currency_locale, NumberFormatter::CURRENCY);
