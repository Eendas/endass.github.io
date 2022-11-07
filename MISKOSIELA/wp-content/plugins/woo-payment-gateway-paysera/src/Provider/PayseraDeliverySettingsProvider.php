<?php

declare(strict_types=1);

namespace Paysera\Provider;

defined('ABSPATH') || exit;

use Paysera\Entity\PayseraDeliveryGatewaySettings;
use Paysera\Entity\PayseraDeliverySettings;

class PayseraDeliverySettingsProvider
{
    public function getPayseraDeliveryGatewaySettings(string $deliveryGatewayCode): PayseraDeliveryGatewaySettings
    {
        $options = get_option(PayseraDeliverySettings::DELIVERY_GATEWAYS_SETTINGS_NAME);

        $payseraDeliveryGatewaySettings = (new PayseraDeliveryGatewaySettings())
            ->setMinimumWeight(
                $options[$deliveryGatewayCode . '_' . PayseraDeliverySettings::MINIMUM_WEIGHT]
                ?? PayseraDeliverySettings::DEFAULT_MINIMUM_WEIGHT
            )
            ->setMaximumWeight(
                $options[$deliveryGatewayCode . '_' . PayseraDeliverySettings::MAXIMUM_WEIGHT]
                ?? PayseraDeliverySettings::DEFAULT_MAXIMUM_WEIGHT
            )
            ->setSenderType(PayseraDeliverySettings::DEFAULT_TYPE)
        ;

        if (isset($options[$deliveryGatewayCode . '_' . PayseraDeliverySettings::SENDER_TYPE])) {
            $payseraDeliveryGatewaySettings->setSenderType(
                $options[$deliveryGatewayCode . '_' . PayseraDeliverySettings::SENDER_TYPE]
            );
        }

        if (isset($options[$deliveryGatewayCode . '_' . PayseraDeliverySettings::RECEIVER_TYPE])) {
            $payseraDeliveryGatewaySettings->setReceiverType(
                $options[$deliveryGatewayCode . '_' . PayseraDeliverySettings::RECEIVER_TYPE]
            );
        }

        return $payseraDeliveryGatewaySettings;
    }

    public function getPayseraDeliverySettings(): PayseraDeliverySettings
    {
        $settings = get_option(PayseraDeliverySettings::SETTINGS_NAME);
        $extraSettings = get_option(PayseraDeliverySettings::EXTRA_SETTINGS_NAME);
        $deliveryGatewaysSettings = get_option(PayseraDeliverySettings::DELIVERY_GATEWAYS_SETTINGS_NAME);
        $deliveryGatewaysTitles = get_option(PayseraDeliverySettings::DELIVERY_GATEWAYS_TITLES);

        $payseraDeliverySettings = (new PayseraDeliverySettings())
            ->setGridViewEnabled(false)
            ->setHideShippingMethodsEnabled(true)
        ;

        if (isset($settings[PayseraDeliverySettings::PROJECT_ID])) {
            $payseraDeliverySettings->setProjectId((int) trim($settings[PayseraDeliverySettings::PROJECT_ID]));
        }

        if (isset($deliveryGatewaysSettings[PayseraDeliverySettings::RESOLVED_PROJECT_ID])) {
            $payseraDeliverySettings->setResolvedProjectId(
                $deliveryGatewaysSettings[PayseraDeliverySettings::RESOLVED_PROJECT_ID]
            );
        }

        if (isset($settings[PayseraDeliverySettings::PROJECT_PASSWORD])) {
            $payseraDeliverySettings->setProjectPassword(trim($settings[PayseraDeliverySettings::PROJECT_PASSWORD]));
        }

        if (isset($settings[PayseraDeliverySettings::TEST_MODE])) {
            $payseraDeliverySettings->setTestModeEnabled(
                $settings[PayseraDeliverySettings::TEST_MODE]
                === 'yes'
            );
        }

        if (isset($settings[PayseraDeliverySettings::HOUSE_NUMBER_FIELD])) {
            $payseraDeliverySettings->setHouseNumberFieldEnabled(
                $settings[PayseraDeliverySettings::HOUSE_NUMBER_FIELD]
                === 'yes'
            );
        }

        if (isset($extraSettings[PayseraDeliverySettings::GRID_VIEW])) {
            $payseraDeliverySettings->setGridViewEnabled(
                $extraSettings[PayseraDeliverySettings::GRID_VIEW]
                === 'yes'
            );
        }

        if (isset($extraSettings[PayseraDeliverySettings::HIDE_SHIPPING_METHODS])) {
            $payseraDeliverySettings->setHideShippingMethodsEnabled(
                $extraSettings[PayseraDeliverySettings::HIDE_SHIPPING_METHODS]
                === 'yes'
            );
        }

        if (isset($deliveryGatewaysSettings[PayseraDeliverySettings::DELIVERY_GATEWAYS])) {
            $payseraDeliverySettings->setDeliveryGateways(
                $deliveryGatewaysSettings[PayseraDeliverySettings::DELIVERY_GATEWAYS]
            );
        }

        if (isset($deliveryGatewaysSettings[PayseraDeliverySettings::DELIVERY_GATEWAYS])) {
            $payseraDeliverySettings->setDeliveryGatewayTitles(
                $deliveryGatewaysTitles[PayseraDeliverySettings::DELIVERY_GATEWAYS]
            );
        }

        if (isset($deliveryGatewaysSettings[PayseraDeliverySettings::SHIPMENT_METHODS])) {
            $payseraDeliverySettings->setShipmentMethods(
                $deliveryGatewaysSettings[PayseraDeliverySettings::SHIPMENT_METHODS]
            );
        }

        return $payseraDeliverySettings;
    }
}
