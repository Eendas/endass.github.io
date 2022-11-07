<?php

declare(strict_types=1);

namespace Paysera\Action;

defined('ABSPATH') || exit;

use Paysera\Admin\PayseraDeliveryAdmin;
use Paysera\Entity\PayseraDeliverySettings;

class PayseraDeliveryActions
{
    public function build(): void
    {
        add_action('admin_post_paysera_delivery_gateway_change', [$this, 'changeDeliveryGatewayStatus']);
    }

    public function changeDeliveryGatewayStatus(): void
    {
        $this->updateDeliveryGatewayStatus(
            sanitize_text_field($_GET['gateway']),
            sanitize_text_field($_GET['change']) === 'enable'
        );

        wp_redirect(
            'admin.php?page=paysera-delivery&tab=' . PayseraDeliveryAdmin::TAB_DELIVERY_GATEWAYS_LIST_SETTINGS
        );
    }

    /**
     * @param array $deliveryGateways
     */
    public function setDeliveryGatewayTitles(array $deliveryGateways): void
    {
        foreach ($deliveryGateways as $deliveryGateway) {
            $this->updateDeliveryGatewayTitle($deliveryGateway->getCode(), $deliveryGateway->getDescription());
        }
    }

    /**
     * @param array $deliveryGateways
     */
    public function reSyncDeliveryGatewayStatus(array $deliveryGateways): void
    {
        foreach ($deliveryGateways as $deliveryGateway) {
            if ($deliveryGateway->isEnabled() === false) {
                $this->updateDeliveryGatewayStatus($deliveryGateway->getCode(), false);
            }
        }
    }

    /**
     * @param array $shipmentMethods
     */
    public function syncShipmentMethodsStatus(array $shipmentMethods): void
    {
        foreach ($shipmentMethods as $shipmentMethod) {
            $this->updateShipmentMethodStatus($shipmentMethod->getCode(), $shipmentMethod->isEnabled());
        }
    }

    public function updateDeliveryGatewaySetting(
        string $deliveryGatewayCode,
        string $settingName,
        $settingValue
    ): void {
        $this->updateOptions($deliveryGatewayCode . '_' . $settingName, $settingValue);
    }

    public function updateResolvedProjectId(string $projectId): void
    {
        $this->updateOptions(PayseraDeliverySettings::RESOLVED_PROJECT_ID, $projectId);
    }

    private function updateOptions(string $optionName, $optionValue): void
    {
        $options = get_option(PayseraDeliverySettings::DELIVERY_GATEWAYS_SETTINGS_NAME);

        $options[$optionName] = $optionValue;

        update_option(PayseraDeliverySettings::DELIVERY_GATEWAYS_SETTINGS_NAME, $options);
    }

    private function updateDeliveryGatewayStatus(string $deliveryGateway, bool $isEnabled): void
    {
        $options = get_option(PayseraDeliverySettings::DELIVERY_GATEWAYS_SETTINGS_NAME);

        $options[PayseraDeliverySettings::DELIVERY_GATEWAYS][$deliveryGateway] = $isEnabled;

        update_option(PayseraDeliverySettings::DELIVERY_GATEWAYS_SETTINGS_NAME, $options);
    }

    private function updateDeliveryGatewayTitle(string $deliveryGateway, string $title): void
    {
        $options = get_option(PayseraDeliverySettings::DELIVERY_GATEWAYS_TITLES);

        $options[PayseraDeliverySettings::DELIVERY_GATEWAYS][$deliveryGateway] = $title;

        update_option(PayseraDeliverySettings::DELIVERY_GATEWAYS_TITLES, $options);
    }

    private function updateShipmentMethodStatus(string $shipmentMethod, bool $isEnabled): void
    {
        $options = get_option(PayseraDeliverySettings::DELIVERY_GATEWAYS_SETTINGS_NAME);

        $options[PayseraDeliverySettings::SHIPMENT_METHODS][$shipmentMethod] = $isEnabled;

        update_option(PayseraDeliverySettings::DELIVERY_GATEWAYS_SETTINGS_NAME, $options);
    }
}
