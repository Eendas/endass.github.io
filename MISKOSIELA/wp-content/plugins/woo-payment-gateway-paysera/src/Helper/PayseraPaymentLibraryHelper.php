<?php

declare(strict_types=1);

namespace Paysera\Helper;

defined('ABSPATH') || exit;

use WebToPay;
use WebToPayException;
use WebToPay_PaymentMethodCountry;

class PayseraPaymentLibraryHelper
{
    private const PAYSERA_PAYMENTS_EXCEPTION_TEXT = '[Paysera Payments] Got an exception: ';

    /**
     * @param int $projectId
     * @param float $amount
     * @param string $currency
     * @param string $language
     * @return WebToPay_PaymentMethodCountry[]
     * @throws WebToPayException
     */
    public function getPaymentMethodList(int $projectId, float $amount, string $currency, string $language): array
    {
        try {
            $paymentMethodList = WebToPay::getPaymentMethodList($projectId, $currency)
                ->filterForAmount($amount, $currency)
                ->setDefaultLanguage($language)
                ->getCountries()
            ;
        } catch (WebToPayException $exception) {
            error_log(self::PAYSERA_PAYMENTS_EXCEPTION_TEXT . $exception);

            return [];
        }

        return $paymentMethodList;
    }
}
