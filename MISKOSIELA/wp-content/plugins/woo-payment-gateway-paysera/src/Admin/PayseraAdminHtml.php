<?php

declare(strict_types=1);

namespace Paysera\Admin;

defined('ABSPATH') || exit;

use Paysera\Entity\PayseraPaths;

class PayseraAdminHtml
{
    public function buildAboutPage(): string
    {
        wp_enqueue_style('paysera-delivery-css', PayseraPaths::PAYSERA_DELIVERY_CSS);

        return '<div class="paysera-delivery-about-container"><div class="paysera-delivery-logo-container">'
            . '<img src="' . PayseraPaths::PAYSERA_LOGO . '" alt="paysera-logo"/></div>'
            . '<h1>' . __('About Paysera', PayseraPaths::PAYSERA_TRANSLATIONS) . '</h1><p>'
            . sprintf(
                __('%s is a global fintech company providing financial and related services to clients from all over the world since 2004.', PayseraPaths::PAYSERA_TRANSLATIONS),
                '<a href="' . __('https://www.paysera.com/v2/en-GB/paysera-account', PayseraPaths::PAYSERA_TRANSLATIONS) . '" target="_blank" rel="noopener noreferrer">Paysera</a>'
            ) . '</p><h1>' . __('Getting started', PayseraPaths::PAYSERA_TRANSLATIONS) . '</h1>'
            . '<p>' . __('In order to receive full benefits of both Paysera Payment and Delivery plugins, please use the outlined links to access our detailed how-to instructions.', PayseraPaths::PAYSERA_TRANSLATIONS) . '</p>'
            . '<div class="paysera-delivery-inline-container-wrapper"><div class="paysera-delivery-inline-container">'
            . '<h2>' . __('Paysera Payments', PayseraPaths::PAYSERA_TRANSLATIONS) . '</h2></div>'
            . '<div class="paysera-delivery-inline-container">'
            . '<h2>' . __('Paysera Delivery', PayseraPaths::PAYSERA_TRANSLATIONS) . '</h2></div></div>'
            . '<div class="paysera-delivery-inline-container-wrapper"><div class="paysera-delivery-inline-container">'
            . '<p>' . __('This plugin enables you to accept online payments via cards, SMS, or the most popular banks in your country. It is easy to install and is used by thousands of online merchants across Europe.', PayseraPaths::PAYSERA_TRANSLATIONS) . '</p>'
            . '</div><div class="paysera-delivery-inline-container">'
            . '<p>' . __('This plugin displays several different delivery companies that your buyers can choose from when ordering your products. No need to sign separate agreements with the courier companies – we have done it for you. Enjoy low delivery prices and quick support when needed.', PayseraPaths::PAYSERA_TRANSLATIONS) . '</p>'
            . '</div></div><div class="paysera-delivery-inline-container-wrapper"><div class="paysera-delivery-inline-container">'
            . '<p class="paysera-delivery-small-paragraph"><a href="' . __('https://www.paysera.com/v2/en-GB/payment-gateway-checkout', PayseraPaths::PAYSERA_TRANSLATIONS)
            . '" target="_blank" rel="noopener noreferrer">' . __('Read more about it >', PayseraPaths::PAYSERA_TRANSLATIONS) . '</a></p>'
            . '<p class="paysera-delivery-small-paragraph"><a href="' . __('https://developers.paysera.com/en/checkout/basic', PayseraPaths::PAYSERA_TRANSLATIONS)
            . '" target="_blank" rel="noopener noreferrer">' . __('Instructions >', PayseraPaths::PAYSERA_TRANSLATIONS) . '</a></p>'
            . '</div><div class="paysera-delivery-inline-container">'
            . '<p class="paysera-delivery-small-paragraph"><a href="' . __('https://www.paysera.com/v2/en-GB/checkout-delivery-service', PayseraPaths::PAYSERA_TRANSLATIONS)
            . '" target="_blank" rel="noopener noreferrer">' . __('Read more about it >', PayseraPaths::PAYSERA_TRANSLATIONS) . '</a></p>'
            . '<p class="paysera-delivery-small-paragraph"><a href="' . __('https://developers.paysera.com/en/delivery', PayseraPaths::PAYSERA_TRANSLATIONS)
            . '" target="_blank" rel="noopener noreferrer">' . __('Instructions >', PayseraPaths::PAYSERA_TRANSLATIONS) . '</a></p>'
            . '</div></div><h1>' . __('Need assistance?', PayseraPaths::PAYSERA_TRANSLATIONS) . '</h1>'
            . '<p>' . __('Paysera client support in English is available 24/7!', PayseraPaths::PAYSERA_TRANSLATIONS) . '</p>'
            . '<p>+44 20 80996963</br>support@paysera.com</p>'
            . '<p>' . __('During working hours the support is available in 12 languages.', PayseraPaths::PAYSERA_TRANSLATIONS) . '</br>'
            . '<a href="' . __('https://www.paysera.com/v2/en-GB/contacts', PayseraPaths::PAYSERA_TRANSLATIONS)
            . '" target="_blank" rel="noopener noreferrer">' . __('Contact us >', PayseraPaths::PAYSERA_TRANSLATIONS) . '</a></p>'
            . '<p>' . sprintf(
                __('For the latest news about the Paysera services and status updates – follow us on %s and %s.', PayseraPaths::PAYSERA_TRANSLATIONS),
                '<a href="https://www.facebook.com/paysera.international/" target="_blank" rel="noopener noreferrer">Facebook</a>',
                '<a href="https://twitter.com/paysera" target="_blank" rel="noopener noreferrer">Twitter</a>'
            ) . '</p>'
            . '<h1>' . __('Explore other services of Paysera', PayseraPaths::PAYSERA_TRANSLATIONS) . '</h1>'
            . '<p>' . __('Alongside its\' most popular services, Paysera also offers:', PayseraPaths::PAYSERA_TRANSLATIONS) . '</p>'
            . '<ul><li><p class="paysera-delivery-small-paragraph">' . __('Currency exchange at competitive rates', PayseraPaths::PAYSERA_TRANSLATIONS) . '</p></li>'
            . '<li><p class="paysera-delivery-small-paragraph">' . __('Enables instant Euro and cheap international transfers', PayseraPaths::PAYSERA_TRANSLATIONS) . '</p></li>'
            . '<li><p class="paysera-delivery-small-paragraph">' . sprintf(
                __('Provides LT, BGN, and RON %s for business and private clients', PayseraPaths::PAYSERA_TRANSLATIONS),
                '<a href="' . __('https://www.paysera.com/v2/en/blog/iban-account', PayseraPaths::PAYSERA_TRANSLATIONS)
                . '" target="_blank" rel="noopener noreferrer">IBAN</a>'
            ) . '</p></li>'
            . '<li><p class="paysera-delivery-small-paragraph">' . sprintf(
                __('Issues %s that are compatible with %s and %s, and so much more.', PayseraPaths::PAYSERA_TRANSLATIONS),
                '<a href="' . __('https://www.paysera.com/v2/en-GB/payment-card-visa', PayseraPaths::PAYSERA_TRANSLATIONS) . '" target="_blank" rel="noopener noreferrer">'
                . __('Visa cards', PayseraPaths::PAYSERA_TRANSLATIONS) . '</a>',
                '<a href="' . __('https://www.paysera.com/v2/en-GB/blog/googlepay-samsungpay', PayseraPaths::PAYSERA_TRANSLATIONS) . '" target="_blank" rel="noopener noreferrer">Google Pay</a>',
                '<a href="' . __('https://www.paysera.com/v2/en-GB/apple-pay', PayseraPaths::PAYSERA_TRANSLATIONS) . '" target="_blank" rel="noopener noreferrer">Apple Pay</a>'
            ) . '</p></li>'
            . '</ul><p>' . sprintf(
                __('All the main services can be easily managed via the %s which is available for download from the App Store, Google Play, and Huawei AppGallery.', PayseraPaths::PAYSERA_TRANSLATIONS),
                '<a href="' . __('https://www.paysera.com/v2/en-GB/mobile-application', PayseraPaths::PAYSERA_TRANSLATIONS) . '" target="_blank" rel="noopener noreferrer">'
                . __('Paysera mobile app', PayseraPaths::PAYSERA_TRANSLATIONS) . '</a>'
            ) . '</p>'
            . '<p>' . __('Thank you for choosing our services!', PayseraPaths::PAYSERA_TRANSLATIONS) . '</p></div>';
    }

    public function getTextInput(bool $isRequired = false): string
    {
        return '<input class="paysera-settings-input" type="text" name="%s" value="%s" ' . (($isRequired) ? 'required' : '') . '/>';
    }

    public function getNumberInput(bool $isRequired = false): string
    {
        return '<input class="paysera-settings-input" type="number" name="%s" value="%s" ' . (($isRequired) ? 'required' : '') . '/>';
    }

    public function getTextAreaInput(): string
    {
        return '<textarea class="paysera-settings-input" type="number" name="%s">%s</textarea>';
    }

    public function getMultipleSelectInput(array $options, array $selected): string
    {
        $html = '<select class="paysera-multiple-select" multiple="multiple" name="%s[]">';

        foreach ($options as $key => $option) {
            $html .= '<option value="' . $key . '" ' . ((in_array($key, $selected, true) === true) ? 'selected' : '')
                . '>' . $option . '</option>'
            ;
        }

        $html .= '</select>';

        return $html;
    }

    public function getSelectInput(array $options, string $selected): string
    {
        $html = '<select class="paysera-multiple-select" name="%s[]">';

        foreach ($options as $value => $option) {
            $html .= '<option value="' . $value . '" ' . (($value === $selected) ? 'selected' : '') . '>' . $option
                . '</option>'
            ;
        }

        $html .= '</select>';

        return $html;
    }

    public function getEnableInput(string $parametersName, string $parameterValue): string
    {
        return '<select name="' . $parametersName . '">' . '<option value="yes">'
            . __('Enabled', PayseraPaths::PAYSERA_TRANSLATIONS) . '</option>' . '<option value="no" '
            . (($parameterValue === 'no') ? 'selected' : '') . '>' . __('Disabled', PayseraPaths::PAYSERA_TRANSLATIONS)
            . '</option></select>'
        ;
    }

    public function getSettingsSavedMessage(): string
    {
        return '<div class="updated"><p>' . __('Settings saved.', PayseraPaths::PAYSERA_TRANSLATIONS) . '</p></div>';
    }

    public function getSettingsWarningNotice(): string
    {
        return '<div class="update-message notice inline notice-error notice-alt"><p>'
            . __('The project ID and password fields must be filled with valid data', PayseraPaths::PAYSERA_TRANSLATIONS) . '</p></div>'
        ;
    }

    public function buildLabel(string $label): string
    {
        return '<p class="description">' . $label . '</p>';
    }
}
