<?php

declare(strict_types=1);

namespace Paysera\Generator;

defined('ABSPATH') || exit;

use WC_Order;
use WebToPay;
use Paysera\Entity\PayseraPaymentSettings;
use Paysera\Provider\PayseraPaymentSettingsProvider;

class PayseraPaymentRequestGenerator
{
    /**
     * @var PayseraPaymentSettings
     */
    private $payseraPaymentSettings;

    public function __construct()
    {
        $this->payseraPaymentSettings = (new PayseraPaymentSettingsProvider())->getPayseraPaymentSettings();
    }

    public function buildPaymentRequestUrl(WC_Order $order, string $paymentMethod, string $acceptUrl): string
    {
        $request = WebToPay::buildRequest($this->buildParameters($order, $paymentMethod, $acceptUrl));

        return preg_replace('/[\r\n]+/is', '', WebToPay::PAY_URL . '?' . http_build_query($request));
    }

    private function buildParameters(WC_Order $order, string $paymentMethod, string $acceptUrl): array
    {
        $language = PayseraPaymentSettings::DEFAULT_ISO_639_2_LANGUAGE;
        $wordpressLanguage = explode('_', get_locale())[0];

        if (PayseraPaymentSettings::ISO_639_2_LANGUAGES[$wordpressLanguage]) {
            $language = PayseraPaymentSettings::ISO_639_2_LANGUAGES[$wordpressLanguage];
        }

        return [
            'projectid' => $this->limitIntLength($this->payseraPaymentSettings->getProjectId(), 11),
            'sign_password' => $this->limitStringLength($this->payseraPaymentSettings->getProjectPassword()),
            'orderid' => $this->limitIntLength($order->get_id(), 40),
            'amount' => $this->limitIntLength((int) (number_format((float) $order->get_total(), 2, '', '')), 11),
            'currency' => $this->limitStringLength($order->get_currency(), 3),
            'country' => $this->limitStringLength($order->get_billing_country(), 2),
            'accepturl' => $this->limitStringLength($acceptUrl),
            'cancelurl' => $this->limitStringLength(htmlspecialchars_decode($order->get_cancel_order_url())),
            'callbackurl' => $this->limitStringLength(trailingslashit(get_home_url()) . '?wc-api=wc_gateway_paysera'),
            'p_firstname' => $this->limitStringLength($order->get_billing_first_name()),
            'p_lastname' => $this->limitStringLength($order->get_billing_last_name()),
            'p_email' => $this->limitStringLength($order->get_billing_email()),
            'p_street' => $this->limitStringLength($order->get_billing_address_1()),
            'p_countrycode' => $this->limitStringLength($order->get_billing_country(), 2),
            'p_city' => $this->limitStringLength($order->get_billing_city()),
            'p_state' => $this->limitStringLength($order->get_billing_state(), 20),
            'payment' => $this->limitStringLength($paymentMethod, 20),
            'p_zip' => $this->limitStringLength($order->get_billing_postcode(), 20),
            'lang' => $this->limitStringLength($language, 3),
            'test' => $this->limitIntLength((int) $this->payseraPaymentSettings->isTestModeEnabled(), 1),
            'buyer_consent' => $this->limitIntLength((int) $this->payseraPaymentSettings->isBuyerConsentEnabled(), 1),
        ];
    }

    private function limitStringLength(string $value, int $limit = 255): string
    {
        if (strlen($value) > $limit) {
            return substr($value, 0, $limit);
        }

        return $value;
    }

    private function limitIntLength(int $value, int $limit = 255): int
    {
        if (strlen((string) $value) > $limit) {
            return (int) substr((string) $value, 0, $limit);
        }

        return $value;
    }
}
