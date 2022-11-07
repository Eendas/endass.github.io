<?php

declare(strict_types=1);

namespace Paysera\Admin;

defined('ABSPATH') || exit;

use Paysera\DeliveryApi\MerchantClient\Entity\ShipmentGateway;
use Paysera\Helper\PayseraDeliveryHelper;
use Paysera\Entity\PayseraPaths;
use Paysera\Entity\PayseraDeliverySettings;

class PayseraDeliveryAdminHtml
{
    private $payseraDeliveryHelper;

    public function __construct()
    {
        $this->payseraDeliveryHelper = new PayseraDeliveryHelper();
    }

    public function buildDeliverySettings(string $activeTab, ?int $projectId): void
    {
        wp_enqueue_style('paysera-delivery-css', PayseraPaths::PAYSERA_DELIVERY_CSS);

        printf('<form action="options.php" class="paysera-settings" method="post">');
        printf(
            '<div><img class="paysera-delivery-settings-logo" src="' . PayseraPaths::PAYSERA_LOGO
            . '" alt="paysera-logo"/></div>'
        )
        ;
        printf('<nav class="nav-tab-wrapper woo-nav-tab-wrapper">');
        printf('<a class="nav-tab ');

        if ($activeTab === PayseraDeliveryAdmin::TAB_GENERAL_SETTINGS) {
            printf('nav-tab-active');
        }

        printf('"href="' . $this->payseraDeliveryHelper->settingsUrl() . '">');
        printf(__('Main Settings', PayseraPaths::PAYSERA_TRANSLATIONS) . '</a>');

        if ($projectId !== null) {
            printf('<a class="nav-tab ');
            if ($activeTab === PayseraPaymentAdmin::TAB_EXTRA_SETTINGS) {
                printf('nav-tab-active');
            }

            printf(
                '"href="'
                . $this->payseraDeliveryHelper->settingsUrl(['tab' => PayseraDeliveryAdmin::TAB_EXTRA_SETTINGS]) . '">'
            );
            printf(__('Extra Settings', PayseraPaths::PAYSERA_TRANSLATIONS) . '</a>');

            printf('<a class="nav-tab ');

            if ($activeTab === PayseraDeliveryAdmin::TAB_DELIVERY_GATEWAYS_LIST_SETTINGS) {
                printf('nav-tab-active');
            }

            printf(
                '"href="' .
                $this->payseraDeliveryHelper->settingsUrl(
                    ['tab' => PayseraDeliveryAdmin::TAB_DELIVERY_GATEWAYS_LIST_SETTINGS]
                ) . '">'
            );
            printf(__('Delivery Gateway List', PayseraPaths::PAYSERA_TRANSLATIONS) . '</a>');
        }
        printf('</nav>');

        if ($activeTab === PayseraDeliveryAdmin::TAB_DELIVERY_GATEWAYS_LIST_SETTINGS) {
            printf('<div class="paysera-delivery-gateways-list">');
        }

        if (
            $activeTab === PayseraDeliveryAdmin::TAB_GENERAL_SETTINGS
            || $activeTab === PayseraDeliveryAdmin::TAB_DELIVERY_GATEWAYS_LIST_SETTINGS
        ) {
            settings_fields(PayseraDeliverySettings::SETTINGS_NAME);
            do_settings_sections(PayseraDeliverySettings::SETTINGS_NAME);
        } elseif ($activeTab === PayseraDeliveryAdmin::TAB_EXTRA_SETTINGS) {
            settings_fields(PayseraDeliverySettings::EXTRA_SETTINGS_NAME);
            do_settings_sections(PayseraDeliverySettings::EXTRA_SETTINGS_NAME);
        }

        if ($activeTab === PayseraDeliveryAdmin::TAB_DELIVERY_GATEWAYS_LIST_SETTINGS) {
            printf('</div>');
        }

        if ($activeTab !== PayseraDeliveryAdmin::TAB_DELIVERY_GATEWAYS_LIST_SETTINGS) {
            submit_button();
        }

        printf('</form>');
    }

    /**
     * @param ShipmentGateway[] $deliveryGateways
     * @param array $options
     * @return string
     */
    public function buildDeliveryGatewaysHtml(array $deliveryGateways, array $options): string
    {
        wp_enqueue_style('paysera-delivery-css', PayseraPaths::PAYSERA_DELIVERY_CSS);

        $html = '';

        foreach ($deliveryGateways as $deliveryGateway) {
            if ($deliveryGateway->isEnabled() === false) {
                continue;
            }

            $deliveryGatewayEnabled = false;

            if (isset($options[PayseraDeliverySettings::DELIVERY_GATEWAYS][$deliveryGateway->getCode()])) {
                $deliveryGatewayEnabled =
                    $options[PayseraDeliverySettings::DELIVERY_GATEWAYS][$deliveryGateway->getCode()]
                ;
            }

            $html .= '<div class="paysera-delivery-list-row"><div class="paysera-delivery-list-col"><div>'
                . $this->generateDeliveryGatewayLogoHtml($deliveryGateway) . '<p class="paysera-delivery-gateway-title">'
                . $deliveryGateway->getDescription() . '</p><br></div></div>'
            ;

            if ($deliveryGatewayEnabled === true) {
                $html .= '<div class="paysera-delivery-list-col">' . '<a href="'
                    . admin_url('admin.php?page=wc-settings&tab=shipping') . '" class="button">'
                    . __('Shipping zones', PayseraPaths::PAYSERA_TRANSLATIONS)
                    . '</a></div><div class="paysera-delivery-list-col"><a href="'
                    . admin_url('admin-post.php?action=paysera_delivery_gateway_change&change=disable&gateway='
                    . $deliveryGateway->getCode()) . '" class="button">'
                    . __('Disable', PayseraPaths::PAYSERA_TRANSLATIONS)
                ;
            } else {
                $html .= '<div class="paysera-delivery-list-col"></div><div class="paysera-delivery-list-col">'
                    . '<a href="'
                    . admin_url('admin-post.php?action=paysera_delivery_gateway_change&change=enable&gateway='
                    . $deliveryGateway->getCode()) . '" class="button">'
                    . __('Enable', PayseraPaths::PAYSERA_TRANSLATIONS)
                ;
            }

            $html .= '</a></div></div>';
        }

        return $html;
    }

    public function generateDeliveryGatewayLogoHtml(ShipmentGateway $deliveryGateway, bool $isCheckout = false): string
    {
        return '<img src="' . $deliveryGateway->getLogo() . '" '
            . (($isCheckout) ? 'class="paysera-delivery-checkout-logo"' : '') . ' alt="' . $deliveryGateway->getCode()
            . '"><br>'
        ;
    }
}
