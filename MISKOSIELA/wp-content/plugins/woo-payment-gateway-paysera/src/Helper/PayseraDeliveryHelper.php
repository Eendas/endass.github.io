<?php

declare(strict_types=1);

namespace Paysera\Helper;

defined('ABSPATH') || exit;

use WC_Shipping_Zones;
use Paysera\Entity\PayseraDeliverySettings;

class PayseraDeliveryHelper
{
    public function settingsUrl(array $query = []): string
    {
        return esc_url(admin_url('admin.php?page=paysera-delivery') . '&' . http_build_query($query));
    }

    public function resolveDeliveryGatewayCode(string $deliveryGatewayCode): string
    {
        return str_replace(
            ['_terminals', '_courier', PayseraDeliverySettings::DELIVERY_GATEWAY_PREFIX],
            '',
            strripos($deliveryGatewayCode, ':')
                ? stristr($deliveryGatewayCode, ':', true)
                : $deliveryGatewayCode
        );
    }

    public function isPayseraDeliveryGateway(string $deliveryGateway): bool
    {
        return (strpos($deliveryGateway, PayseraDeliverySettings::DELIVERY_GATEWAY_PREFIX) !== false);
    }

    public function deliveryGatewayClassExists(string $deliveryGateway, string $gatewayType): bool
    {
        return file_exists(
            plugin_dir_path(__FILE__) . 'Entity/class-paysera-' . $deliveryGateway . '-' . $gatewayType
            . '-delivery.php'
        );
    }

    public function getAvailableCountriesByShippingMethodId(string $shippingMethodId): array
    {
        foreach (WC_Shipping_Zones::get_zones() as $shippingZone) {
            foreach ($shippingZone['shipping_methods'] as $shippingMethod) {
                if ($shippingMethod->id === $shippingMethodId) {
                    return $this->formatShippingZoneCountries($shippingZone['zone_locations']);
                }
            }
        }

        return [];
    }

    private function formatShippingZoneCountries(array $shippingZoneLocations): array
    {
        $countryCodes = [];

        foreach ($shippingZoneLocations as $location) {
            $countryCodes[] = $location->code;
        }

        return $countryCodes;
    }
}
