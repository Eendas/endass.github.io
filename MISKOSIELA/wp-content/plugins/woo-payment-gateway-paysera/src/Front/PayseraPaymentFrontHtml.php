<?php

declare(strict_types=1);

namespace Paysera\Front;

defined('ABSPATH') || exit;

use WebToPay_PaymentMethodGroup;
use WebToPay_PaymentMethod;
use Paysera\Entity\PayseraPaths;

class PayseraPaymentFrontHtml
{
    public function buildCountriesList(array $countries, string $billingCountryCode): string
    {
        $html = '<select id="paysera_country" class="payment-country-select" >';

        foreach ($countries as $country) {
            $isCountrySelected = $country['code'] === $this->getDefaultCountryCode($countries, $billingCountryCode);

            $html .= '<option value="' . $country['code'] . '" ' . (($isCountrySelected === true) ? 'selected' : '')
                . '>' . $country['title'] . '</option>'
            ;
        }

        $html .= '</select>';

        return $html;
    }

    public function buildPaymentsList(array $countries, bool $isGridViewEnabled, string $billingCountryCode): string
    {
        $html = '';

        foreach ($countries as $country) {
            $isCountrySelected = $country['code'] === $this->getDefaultCountryCode($countries, $billingCountryCode);

            $html .= '<div id="' . $country['code'] . '" ' . ' class="payment-countries paysera-payments'
                . (($isGridViewEnabled) ? ' grid"' : '"') . ' style="display:'
                . (($isCountrySelected === true) ? 'block' : 'none') . '">'
                . $this->buildGroupList($country['groups'], $country['code'], $isCountrySelected) . '</div>'
            ;
        }

        return $html;
    }

    public function buildBuyerConsent(): string
    {
        return sprintf(
            __(
                'Please be informed that the account information and payment initiation services will be provided to you by Paysera in accordance with these %s. By proceeding with this payment, you agree to receive this service and the service terms and conditions.',
                PayseraPaths::PAYSERA_TRANSLATIONS
            ),
            '<a href="'
            . __('https://www.paysera.com/v2/en-GB/legal/pis-rules-2020', PayseraPaths::PAYSERA_TRANSLATIONS)
            . ' " target="_blank" rel="noopener noreferrer"> ' . __('rules', PayseraPaths::PAYSERA_TRANSLATIONS)
            . '</a>'
        );
    }

    /**
     * @param WebToPay_PaymentMethodGroup[] $groups
     * @param string $countryCode
     * @param bool $isSelected
     * @return string
     */
    private function buildGroupList(array $groups, string $countryCode, bool $isSelected): string
    {
        $html = '';

        foreach ($groups as $group) {
            $html .= '<div class="payment-group-wrapper"><div class="payment-group-title">' . $group->getTitle()
                . '</div>' . $this->buildMethodsList($group->getPaymentMethods(), $countryCode, $isSelected) . '</div>'
            ;
        }

        return $html;
    }

    /**
     * @param WebToPay_PaymentMethod[] $methods
     * @param string $countryCode
     * @param bool $isSelected
     * @return string
     */
    private function buildMethodsList(array $methods, string $countryCode, bool $isSelected): string
    {
        $html = '';

        foreach ($methods as $method) {
            $html .= '<div id="' . $method->getKey()
                . '" class="paysera-payment-method"><label class="paysera-payment-method-label"><div><input type="radio" rel="r'
                . $countryCode . $method->getKey() . '" name="payment[pay_type]" value="' . $method->getKey()
                . '"/><span class="paysera-text">' . $method->getTitle()
                . '</span></div><div class="paysera-image"><img src="' . $method->getLogoUrl() . '" ' . 'alt="'
                . $method->getTitle() . '"' . (($isSelected) ? '' : 'loading="lazy"') . '/></div></label></div>'
            ;
        }

        return $html;
    }

    private function getDefaultCountryCode(array $countries, string $countryCode): string
    {
        $countryCodes = [];

        foreach ($countries as $country) {
            $countryCodes[] = $country['code'];
        }

        if (in_array($countryCode, $countryCodes, true) === true) {
            return $countryCode;
        }

        if (in_array('other', $countryCodes, true) === true) {
            return 'other';
        }

        return reset($countries)['code'];
    }
}
