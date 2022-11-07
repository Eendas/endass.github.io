<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use Paysera\Action\PayseraDeliveryActions;
use Paysera\Entity\PayseraDeliverySettings;
use Paysera\Entity\PayseraPaths;
use Paysera\Provider\PayseraDeliverySettingsProvider;

abstract class Paysera_Delivery_Gateway extends WC_Shipping_Method
{
    /**
     * @var string
     */
    protected $deliveryGatewayCode;

    /**
     * @var string
     */
    protected $defaultTitle;

    /**
     * @var string
     */
    protected $receiverType;

    /**
     * @var string
     */
    protected $defaultDescription;

    private $payseraDeliveryActions;
    private $payseraDeliverySettingsProvider;

    public function __construct($instance_id = 0)
    {
        parent::__construct();

        $this->payseraDeliveryActions = new PayseraDeliveryActions();
        $this->payseraDeliverySettingsProvider = new PayseraDeliverySettingsProvider();

        $this->id = $this->generateId(absint($instance_id));
        $this->instance_id = absint($instance_id);
        $this->title = $this->defaultTitle;
        $this->method_title = $this->defaultTitle;
        $this->method_description = $this->buildMethodDescription();

        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->get_option('title');

        $this->payseraDeliveryActions->updateDeliveryGatewaySetting(
            $this->id,
            PayseraDeliverySettings::RECEIVER_TYPE,
            $this->receiverType
        );

        $this->supports = [
            'shipping-zones',
            'instance-settings',
            'instance-settings-modal',
        ];

        add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'process_admin_options']);
        add_filter('woocommerce_package_rates', [$this, 'hideShippingWeightBased'], 10, 2 );
    }

    public function calculate_shipping($package = [])
    {
        $rate = [
            'id' => $this->id,
            'label' => $this->title,
            'cost' => $this->instance_settings[PayseraDeliverySettings::FEE],
        ];

        $freeDeliveryLimit = $this->instance_settings[PayseraDeliverySettings::FREE_DELIVERY_LIMIT];

        if ($freeDeliveryLimit > 0 && WC()->cart->get_displayed_subtotal() > $freeDeliveryLimit) {
            $rate['cost'] = 0;
        }

        $this->add_rate($rate);
    }

    public function hideShippingWeightBased($rates, $package): array
    {
        if (
            array_key_exists($this->id, $rates) === false
            || $this->payseraDeliverySettingsProvider->getPayseraDeliverySettings()->isHideShippingMethodsEnabled() === false
        ) {
            return $rates;
        }

        $totalWeight = 0;

        foreach (WC()->cart->cart_contents as $item) {
            $product = wc_get_product($item['product_id']);

            $totalWeight += (float) ($product->get_weight() ?? 0) * (float) $item['quantity'];
        }

        if (get_option('woocommerce_weight_unit') === 'g') {
            $totalWeight /= 1000;
        }

        $minimumWeight = PayseraDeliverySettings::DEFAULT_MINIMUM_WEIGHT;
        $maximumWeight = PayseraDeliverySettings::DEFAULT_MAXIMUM_WEIGHT;

        if (get_option($this->get_instance_option_key()) !== false) {
            $minimumWeight = (float) $this->get_instance_option(PayseraDeliverySettings::MINIMUM_WEIGHT);
            $maximumWeight = (float) $this->get_instance_option(PayseraDeliverySettings::MAXIMUM_WEIGHT);
        }

        if ($totalWeight > $maximumWeight || $totalWeight < $minimumWeight) {
            unset($rates[$this->id]);
        }

        return $rates;
    }

    public function init_form_fields(): void
    {
        $this->instance_form_fields = [
            'title' => [
                'title' => __('Method title', PayseraPaths::PAYSERA_TRANSLATIONS),
                'type' => 'text',
                'description' => __(
                    'This controls the title which the user sees during shipping selection.',
                    PayseraPaths::PAYSERA_TRANSLATIONS
                ),
                'default' => $this->defaultTitle,
                'desc_tip' => true,
            ],
            PayseraDeliverySettings::FEE => [
                'title' => __('Delivery Fee', PayseraPaths::PAYSERA_TRANSLATIONS),
                'type' => 'price',
                'default' => PayseraDeliverySettings::DEFAULT_FEE,
                'placeholder' => wc_format_localized_price(PayseraDeliverySettings::DEFAULT_FEE),
                'description' => get_woocommerce_currency_symbol(),
                'desc_tip' => true,
            ],
            PayseraDeliverySettings::MINIMUM_WEIGHT => [
                'title' => __('Minimum weight', PayseraPaths::PAYSERA_TRANSLATIONS),
                'type' => 'price',
                'default' => PayseraDeliverySettings::DEFAULT_MINIMUM_WEIGHT,
                'placeholder' => wc_format_localized_price(PayseraDeliverySettings::DEFAULT_MINIMUM_WEIGHT),
                'description' => __('Kilograms', PayseraPaths::PAYSERA_TRANSLATIONS),
                'desc_tip' => true,
            ],
            PayseraDeliverySettings::MAXIMUM_WEIGHT => [
                'title' => __('Maximum weight', PayseraPaths::PAYSERA_TRANSLATIONS),
                'type' => 'price',
                'default' => PayseraDeliverySettings::DEFAULT_MAXIMUM_WEIGHT,
                'placeholder' => wc_format_localized_price(PayseraDeliverySettings::DEFAULT_MAXIMUM_WEIGHT),
                'description' => __('Kilograms', PayseraPaths::PAYSERA_TRANSLATIONS),
                'desc_tip' => true,
            ],
            PayseraDeliverySettings::SENDER_TYPE => [
                'title' => __('Preferred pickup type', PayseraPaths::PAYSERA_TRANSLATIONS),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => PayseraDeliverySettings::TYPE_COURIER,
                'options' => $this->getSenderTypeOptions(),
            ],
            PayseraDeliverySettings::FREE_DELIVERY_LIMIT => [
                'title' => __('Minimum order amount for free shipping', PayseraPaths::PAYSERA_TRANSLATIONS),
                'type' => 'price',
                'placeholder' => wc_format_localized_price(PayseraDeliverySettings::DEFAULT_FREE_DELIVERY_LIMIT),
                'description' => __(
                    'Users will need to spend this amount to get free shipping.',
                    PayseraPaths::PAYSERA_TRANSLATIONS
                ),
                'default' => PayseraDeliverySettings::DEFAULT_FREE_DELIVERY_LIMIT,
                'desc_tip' => true,
            ],
        ];
    }

    private function buildMethodDescription(): string
    {
        wp_enqueue_style('paysera-delivery-css', PayseraPaths::PAYSERA_DELIVERY_CSS);

        $minimumWeight = PayseraDeliverySettings::DEFAULT_MINIMUM_WEIGHT;
        $maximumWeight = PayseraDeliverySettings::DEFAULT_MAXIMUM_WEIGHT;
        $fee = PayseraDeliverySettings::DEFAULT_FEE;
        $preferredPickupType = PayseraDeliverySettings::DEFAULT_TYPE;

        if (get_option($this->get_instance_option_key()) !== false) {
            $minimumWeight = (float) $this->get_instance_option(PayseraDeliverySettings::MINIMUM_WEIGHT);
            $maximumWeight = (float) $this->get_instance_option(PayseraDeliverySettings::MAXIMUM_WEIGHT);
            $fee = (float) $this->get_instance_option(PayseraDeliverySettings::FEE);
            $preferredPickupType = $this->get_instance_option(PayseraDeliverySettings::SENDER_TYPE);

            $this->payseraDeliveryActions->updateDeliveryGatewaySetting(
                $this->id,
                PayseraDeliverySettings::MINIMUM_WEIGHT,
                $minimumWeight
            );
            $this->payseraDeliveryActions->updateDeliveryGatewaySetting(
                $this->id,
                PayseraDeliverySettings::MAXIMUM_WEIGHT,
                $maximumWeight
            );
            $this->payseraDeliveryActions->updateDeliveryGatewaySetting(
                $this->id,
                PayseraDeliverySettings::SENDER_TYPE,
                $preferredPickupType
            );
        }

        return sprintf(
            __($this->defaultDescription, PayseraPaths::PAYSERA_TRANSLATIONS),
            $this->getDeliveryGatewayTitle()
        ) . $this->buildExtraDescription($minimumWeight, $maximumWeight, $fee, $preferredPickupType)
        ;
    }

    private function buildExtraDescription(
        float $minimumWeight,
        float $maximumWeight,
        float $fee,
        string $preferredPickupType
    ): string {
        $extraDescription = '';

        if ($maximumWeight > 0) {
            $extraDescription .= sprintf(
                '<div class="paysera-delivery-extra-description"><strong>%s</strong> %s-%skg</div>',
                __('Allowed weight:', PayseraPaths::PAYSERA_TRANSLATIONS),
                $minimumWeight,
                $maximumWeight
            );
        }

        if ($fee > 0) {
            $extraDescription .= sprintf(
                '<div class="paysera-delivery-extra-description"><strong>%s</strong> %s%s</div>',
                __('Delivery Fee:', PayseraPaths::PAYSERA_TRANSLATIONS),
                $fee,
                get_woocommerce_currency_symbol()
            );
        }

        $extraDescription .= sprintf(
            '<div class="paysera-delivery-extra-description"><strong>%s</strong> %s</div>',
            __('Preferred pickup type:', PayseraPaths::PAYSERA_TRANSLATIONS),
            __(PayseraDeliverySettings::READABLE_TYPES[$preferredPickupType], PayseraPaths::PAYSERA_TRANSLATIONS)
        );

        return $extraDescription;
    }

    private function getDeliveryGatewayTitle(): string
    {
        return str_replace(['Terminals', 'Courier'], '', $this->defaultTitle);
    }

    private function getSenderTypeOptions(): array
    {
        $shipmentMethods = $this->payseraDeliverySettingsProvider->getPayseraDeliverySettings()->getShipmentMethods();

        $senderTypes = [];

        if ($this->receiverType === PayseraDeliverySettings::TYPE_COURIER) {
            if ($shipmentMethods[PayseraDeliverySettings::SHIPMENT_METHOD_COURIER_2_COURIER] === true) {
                $senderTypes[PayseraDeliverySettings::TYPE_COURIER] = __('Courier', PayseraPaths::PAYSERA_TRANSLATIONS);
            }

            if ($shipmentMethods[PayseraDeliverySettings::SHIPMENT_METHOD_PARCEL_MACHINE_2_COURIER] === true) {
                $senderTypes[PayseraDeliverySettings::TYPE_PARCEL_MACHINE] =
                    __('Parcel locker', PayseraPaths::PAYSERA_TRANSLATIONS)
                ;
            }
        } elseif ($this->receiverType === PayseraDeliverySettings::TYPE_PARCEL_MACHINE) {
            if ($shipmentMethods[PayseraDeliverySettings::SHIPMENT_METHOD_COURIER_2_PARCEL_MACHINE] === true) {
                $senderTypes[PayseraDeliverySettings::TYPE_COURIER] = __('Courier', PayseraPaths::PAYSERA_TRANSLATIONS);
            }

            if (
                $shipmentMethods[PayseraDeliverySettings::SHIPMENT_METHOD_PARCEL_MACHINE_2_PARCEL_MACHINE]
                === true
            ) {
                $senderTypes[PayseraDeliverySettings::TYPE_PARCEL_MACHINE] =
                    __('Parcel locker', PayseraPaths::PAYSERA_TRANSLATIONS)
                ;
            }
        }

        return $senderTypes;
    }

    private function generateId(int $instanceId): string
    {
        return $instanceId > 0
            ? PayseraDeliverySettings::DELIVERY_GATEWAY_PREFIX . $this->deliveryGatewayCode . ':' . $instanceId
            : PayseraDeliverySettings::DELIVERY_GATEWAY_PREFIX . $this->deliveryGatewayCode
        ;
    }
}
