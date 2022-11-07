<?php

declare(strict_types=1);

namespace Paysera\Generator;

defined('ABSPATH') || exit;

use WebToPay_PaymentMethodCountry;
use Paysera\Front\PayseraPaymentFrontHtml;
use Paysera\Helper\PayseraPaymentLibraryHelper;
use Paysera\Provider\PayseraPaymentSettingsProvider;
use Paysera\Entity\PayseraPaymentSettings;

class PayseraPaymentFieldGenerator
{
    /**
     * @var PayseraPaymentSettings
     */
    private $payseraPaymentSettings;
    private $payseraPaymentFrontHtml;
    private $payseraPaymentLibraryHelper;

    public function __construct()
    {
        $this->payseraPaymentSettings = (new PayseraPaymentSettingsProvider())->getPayseraPaymentSettings();
        $this->payseraPaymentFrontHtml = new PayseraPaymentFrontHtml();
        $this->payseraPaymentLibraryHelper = new PayseraPaymentLibraryHelper();
    }

    public function generatePaymentField(): string
    {
        $billingCountry = strtolower(WC()->customer->get_billing_country());

        $paymentField = '';

        if ($this->payseraPaymentSettings->isListOfPaymentsEnabled() === true) {
            $countries = $this->getCountries(
                $this->payseraPaymentLibraryHelper->getPaymentMethodList(
                    $this->payseraPaymentSettings->getProjectId(),
                    round(WC()->cart->total * 100),
                    get_woocommerce_currency(),
                    $this->getLanguage()
                )
            );

            if (empty($countries) === false) {
                $paymentField .= $this->payseraPaymentFrontHtml->buildCountriesList($countries, $billingCountry)
                    . '<br/>'
                ;
            }

            $paymentField .= $this->payseraPaymentFrontHtml->buildPaymentsList(
                $countries,
                $this->payseraPaymentSettings->isGridViewEnabled(),
                $billingCountry
            );
        } else {
            $paymentField = $this->payseraPaymentSettings->getDescription() . '<br/>';
        }

        if ($this->payseraPaymentSettings->isBuyerConsentEnabled() === true) {
            $paymentField .= '<br/>' . $this->payseraPaymentFrontHtml->buildBuyerConsent();
        }

        return $paymentField;
    }

    /**
     * @param WebToPay_PaymentMethodCountry[] $payseraCountries
     * @return array
     */
    private function getCountries(array $payseraCountries): array
    {
        $specificCountries = $this->payseraPaymentSettings->getSpecificCountries();

        $countries = [];

        foreach ($payseraCountries as $country) {
            if (
                empty($specificCountries) === false
                && in_array(strtoupper($country->getCode()), $specificCountries, true) === false
            ) {
                continue;
            }

            $countries[] = [
                'code' => $country->getCode(),
                'title' => $country->getTitle(),
                'groups' => $country->getGroups(),
            ];
        }

        return $countries;
    }

    private function getLanguage(): string
    {
        $language = explode('_', get_locale())[0];

        if (in_array($language, PayseraPaymentSettings::ISO_639_1_LANGUAGES) === true) {
            return $language;
        }

        return PayseraPaymentSettings::DEFAULT_ISO_639_1_LANGUAGE;
    }
}
