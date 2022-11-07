<?php

declare(strict_types=1);

namespace Paysera\Admin;

defined('ABSPATH') || exit;

use Paysera\DeliveryApi\MerchantClient\Entity\Address;
use Paysera\DeliveryApi\MerchantClient\Entity\Contact;
use Paysera\DeliveryApi\MerchantClient\Entity\OrderCreate;
use Paysera\DeliveryApi\MerchantClient\Entity\Party;
use Exception;
use Paysera\Helper\PayseraDeliveryLibraryHelper;
use Paysera\Provider\PayseraDeliverySettingsProvider;
use Paysera\Action\PayseraDeliveryActions;
use Paysera\Helper\PayseraDeliveryHelper;
use Paysera\Entity\PayseraPaths;
use Paysera\Entity\PayseraDeliverySettings;
use WC_Order;

class PayseraDeliveryAdmin
{
    public const TAB_GENERAL_SETTINGS = 'general_settings';
    public const TAB_EXTRA_SETTINGS = 'extra_settings';
    public const TAB_DELIVERY_GATEWAYS_LIST_SETTINGS = 'delivery_gateways_list_settings';

    private $payseraAdminHtml;
    private $payseraDeliveryAdminHtml;
    private $payseraDeliveryLibraryHelper;
    private $payseraDeliverySettingsProvider;
    private $payseraDeliveryActions;
    private $payseraDeliveryHelper;

    /**
     * @var string
     */
    private $tab;

    /**
     * @var string[]
     */
    private $tabs;

    public function __construct()
    {
        $this->payseraAdminHtml = new PayseraAdminHtml();
        $this->payseraDeliveryAdminHtml = new PayseraDeliveryAdminHtml();
        $this->payseraDeliveryLibraryHelper = new PayseraDeliveryLibraryHelper();
        $this->payseraDeliverySettingsProvider = new PayseraDeliverySettingsProvider();
        $this->payseraDeliveryActions = new PayseraDeliveryActions();
        $this->payseraDeliveryHelper = new PayseraDeliveryHelper();
        $this->tab = self::TAB_GENERAL_SETTINGS;
        $this->tabs = [
            self::TAB_GENERAL_SETTINGS,
            self::TAB_EXTRA_SETTINGS,
            self::TAB_DELIVERY_GATEWAYS_LIST_SETTINGS,
        ];
    }

    public function build(): void
    {
        add_action('admin_init', [$this, 'settingsInit']);
        add_action('woocommerce_checkout_order_processed', [$this, 'createDeliveryOrder'], 1, 1);
        add_action('woocommerce_admin_order_data_after_shipping_address', [$this, 'setShippingHouseField'], 10, 1);
        add_action('woocommerce_admin_order_data_after_billing_address', [$this, 'setBillingHouseField'], 10, 1);
    }

    public function settingsInit(): void
    {
        if (array_key_exists('tab', $_GET) === true) {
            $this->tab = $_GET['tab'];
        }

        if (in_array($this->tab, $this->tabs, true) === false) {
            $this->tab = self::TAB_GENERAL_SETTINGS;
        }

        add_settings_section(
            self::TAB_GENERAL_SETTINGS,
            null,
            [$this, 'payseraDeliverySettingsSectionCallback'],
            PayseraDeliverySettings::SETTINGS_NAME
        );
        add_settings_section(
            self::TAB_EXTRA_SETTINGS,
            null,
            [$this, 'payseraDeliverySettingsSectionCallback'],
            PayseraDeliverySettings::EXTRA_SETTINGS_NAME
        );

        register_setting(PayseraDeliverySettings::SETTINGS_NAME, PayseraDeliverySettings::SETTINGS_NAME);
        register_setting(PayseraDeliverySettings::EXTRA_SETTINGS_NAME, PayseraDeliverySettings::EXTRA_SETTINGS_NAME);

        if ($this->tab === self::TAB_GENERAL_SETTINGS) {
            add_settings_field(
                PayseraDeliverySettings::PROJECT_ID,
                __('Project ID', PayseraPaths::PAYSERA_TRANSLATIONS),
                [$this, 'projectIdRender'],
                PayseraDeliverySettings::SETTINGS_NAME,
                self::TAB_GENERAL_SETTINGS
            );
            add_settings_field(
                PayseraDeliverySettings::PROJECT_PASSWORD,
                __('Project password', PayseraPaths::PAYSERA_TRANSLATIONS),
                [$this, 'projectPasswordRender'],
                PayseraDeliverySettings::SETTINGS_NAME,
                self::TAB_GENERAL_SETTINGS
            );
            add_settings_field(
                PayseraDeliverySettings::TEST_MODE,
                __('Test mode', PayseraPaths::PAYSERA_TRANSLATIONS),
                [$this, 'testModeRender'],
                PayseraDeliverySettings::SETTINGS_NAME,
                self::TAB_GENERAL_SETTINGS
            );
            add_settings_field(
                PayseraDeliverySettings::HOUSE_NUMBER_FIELD,
                __('House number field', PayseraPaths::PAYSERA_TRANSLATIONS),
                [$this, 'houseNumberFieldRender'],
                PayseraDeliverySettings::SETTINGS_NAME,
                self::TAB_GENERAL_SETTINGS
            );
        } elseif ($this->tab === self::TAB_EXTRA_SETTINGS) {
            add_settings_field(
                PayseraDeliverySettings::GRID_VIEW,
                __('Grid view', PayseraPaths::PAYSERA_TRANSLATIONS),
                [$this, 'gridViewRender'],
                PayseraDeliverySettings::EXTRA_SETTINGS_NAME,
                self::TAB_EXTRA_SETTINGS
            );
            add_settings_field(
                PayseraDeliverySettings::HIDE_SHIPPING_METHODS,
                __('Hide shipping methods', PayseraPaths::PAYSERA_TRANSLATIONS),
                [$this, 'hideShippingMethodsRender'],
                PayseraDeliverySettings::EXTRA_SETTINGS_NAME,
                self::TAB_EXTRA_SETTINGS
            );
        } elseif ($this->tab === self::TAB_DELIVERY_GATEWAYS_LIST_SETTINGS) {
            add_settings_field(
                PayseraDeliverySettings::DELIVERY_GATEWAYS,
                __('Delivery Gateways', PayseraPaths::PAYSERA_TRANSLATIONS),
                [$this, 'buildDeliveryGatewaysList'],
                PayseraDeliverySettings::SETTINGS_NAME,
                self::TAB_GENERAL_SETTINGS
            );
        }
    }

    public function buildSettingsPage(): void
    {
        if (isset($_REQUEST['settings-updated'])) {
            printf($this->payseraAdminHtml->getSettingsSavedMessage());
        }

        $this->payseraDeliveryAdminHtml->buildDeliverySettings(
            $_GET['tab'] ?? $this->tab,
            $this->payseraDeliverySettingsProvider->getPayseraDeliverySettings()->getProjectId()
        );
    }

    public function payseraDeliverySettingsSectionCallback(): void
    {
    }

    public function projectIdRender(): void
    {
        printf(
            $this->payseraAdminHtml->getNumberInput(),
            esc_attr(PayseraDeliverySettings::SETTINGS_NAME . '[' . PayseraDeliverySettings::PROJECT_ID . ']'),
            esc_attr($this->payseraDeliverySettingsProvider->getPayseraDeliverySettings()->getProjectId())
        );
    }

    public function projectPasswordRender(): void
    {
        printf(
            $this->payseraAdminHtml->getTextInput(),
            esc_attr(PayseraDeliverySettings::SETTINGS_NAME . '[' . PayseraDeliverySettings::PROJECT_PASSWORD . ']'),
            esc_attr($this->payseraDeliverySettingsProvider->getPayseraDeliverySettings()->getProjectPassword())
        );
    }

    public function testModeRender(): void
    {
        printf(
            $this->payseraAdminHtml->getEnableInput(
                PayseraDeliverySettings::SETTINGS_NAME . '[' . PayseraDeliverySettings::TEST_MODE . ']',
                $this->payseraDeliverySettingsProvider->getPayseraDeliverySettings()->isTestModeEnabled()
                    ? 'yes' : 'no'
            )
        );
    }

    public function houseNumberFieldRender(): void
    {
        printf(
            $this->payseraAdminHtml->getEnableInput(
                PayseraDeliverySettings::SETTINGS_NAME . '[' . PayseraDeliverySettings::HOUSE_NUMBER_FIELD . ']',
                $this->payseraDeliverySettingsProvider->getPayseraDeliverySettings()
                    ->isHouseNumberFieldEnabled() ? 'yes' : 'no'
            )
        );
    }

    public function gridViewRender(): void
    {
        printf(
            $this->payseraAdminHtml->getEnableInput(
                PayseraDeliverySettings::EXTRA_SETTINGS_NAME . '[' . PayseraDeliverySettings::GRID_VIEW . ']',
                $this->payseraDeliverySettingsProvider->getPayseraDeliverySettings()->isGridViewEnabled()
                    ? 'yes' : 'no'
            )
        );
    }

    public function hideShippingMethodsRender(): void
    {
        printf(
            $this->payseraAdminHtml->getEnableInput(
                PayseraDeliverySettings::EXTRA_SETTINGS_NAME . '[' . PayseraDeliverySettings::HIDE_SHIPPING_METHODS . ']',
                $this->payseraDeliverySettingsProvider->getPayseraDeliverySettings()->isHideShippingMethodsEnabled()
                    ? 'yes' : 'no'
            ) .
            $this->payseraAdminHtml->buildLabel(
                __('Hide shipping methods that are above or under set weight limits', PayseraPaths::PAYSERA_TRANSLATIONS)
            )
        );
    }

    public function buildDeliveryGatewaysList(): void
    {
        $deliveryGateways = $this->payseraDeliveryLibraryHelper->getPayseraDeliveryGateways();

        $this->payseraDeliveryActions->setDeliveryGatewayTitles($deliveryGateways);
        $this->payseraDeliveryActions->reSyncDeliveryGatewayStatus($deliveryGateways);
        $this->payseraDeliveryActions->syncShipmentMethodsStatus(
            $this->payseraDeliveryLibraryHelper->getPayseraShipmentMethods()
        );

        if (empty($deliveryGateways) === false) {
            printf(
                $this->payseraDeliveryAdminHtml->buildDeliveryGatewaysHtml(
                    $deliveryGateways,
                    get_option(PayseraDeliverySettings::DELIVERY_GATEWAYS_SETTINGS_NAME)
                )
            );
        } else {
            printf(
                sprintf(
                    "<strong style='%s'>%s</strong>",
                    'color: red',
                    __('Either project id or project password is incorrect', PayseraPaths::PAYSERA_TRANSLATIONS)
                )
            );
        }
    }

    public function setShippingHouseField(WC_Order $order): void
    {
        foreach ($order->get_meta_data() as $metaData) {
            if ($metaData->get_data()['key'] === '_shipping_house_no') {
                echo '<div class="address"><p><strong>' . __('House Number', PayseraPaths::PAYSERA_TRANSLATIONS) . ':</strong> '
                    . $metaData->get_data()['value'] . '</p></div>'
                ;
            }
        }
    }

    public function setBillingHouseField(WC_Order $order): void
    {
        foreach ($order->get_meta_data() as $metaData) {
            if ($metaData->get_data()['key'] === '_billing_house_no') {
                echo '<div class="address"><p><strong>' . __('House Number', PayseraPaths::PAYSERA_TRANSLATIONS) . ':</strong> '
                    . $metaData->get_data()['value'] . '</p></div>'
                ;
            }
        }
    }

    public function createDeliveryOrder(?string $orderId): void
    {
        $payseraDeliverySettings = $this->payseraDeliverySettingsProvider->getPayseraDeliverySettings();

        if ($orderId === null || $payseraDeliverySettings->isTestModeEnabled() === true) {
            return;
        }

        $order = wc_get_order($orderId);

        $deliveryGatewayCode = '';

        if (empty($order->get_shipping_methods()) === true) {
            return;
        }

        foreach ($order->get_shipping_methods() as $shippingMethod) {
            $deliveryGatewayCode = $shippingMethod->get_data()['method_id'];

            if ($this->payseraDeliveryHelper->isPayseraDeliveryGateway($deliveryGatewayCode) === false) {
                return;
            }
        }

        $shipments = [];

        foreach ($order->get_items() as $item) {
            $quantity = $item->get_data()['quantity'];

            for ($productQuantity = 1; $productQuantity <= $quantity; $productQuantity++) {
                $shipments[] = $this->payseraDeliveryLibraryHelper->createShipment(
                    wc_get_product($item->get_data()['product_id'])
                );
            }
        }

        $payseraDeliveryGatewaySettings = $this->payseraDeliverySettingsProvider->getPayseraDeliveryGatewaySettings(
            $deliveryGatewayCode
        );

        $receiverTitle = [
            $order->get_billing_first_name() ?? '',
            $order->get_billing_last_name() ?? '',
            $order->get_billing_company() ?? '',
        ];

        $receiverParty = (new Party())
            ->setTitle(implode(' ', array_filter($receiverTitle)))
            ->setEmail($order->get_billing_email())
            ->setPhone($order->get_billing_phone())
        ;

        $receiverAddress = (new Address())
            ->setCountry($order->get_shipping_country())
            ->setState($order->get_shipping_state())
            ->setCity($order->get_shipping_city())
            ->setStreet($order->get_shipping_address_1())
            ->setPostalCode($order->get_shipping_postcode())
        ;

        if (($payseraDeliveryGatewaySettings->getReceiverType() === PayseraDeliverySettings::TYPE_PARCEL_MACHINE)) {
            $receiverAddress->setCountry(WC()->session->get('paysera_terminal_country'));
            $receiverAddress->setCity(WC()->session->get('paysera_terminal_city'));
        }

        if (WC()->session->get('shipping_house_no') !== '') {
            $receiverAddress->setHouseNumber(WC()->session->get('shipping_house_no'));
        } elseif (WC()->session->get('billing_house_no') !== '') {
            $receiverAddress->setHouseNumber(WC()->session->get('billing_house_no'));
        }

        $orderCreate = (new OrderCreate())
            ->setShipmentGatewayCode($this->payseraDeliveryHelper->resolveDeliveryGatewayCode($deliveryGatewayCode))
            ->setShipmentMethodCode(
                $payseraDeliveryGatewaySettings->getSenderType() . '2'
                . $payseraDeliveryGatewaySettings->getReceiverType()
            )
            ->setShipments($shipments)
            ->setReceiver(
                $this->payseraDeliveryLibraryHelper->createOrderParty(
                    $payseraDeliveryGatewaySettings->getReceiverType(),
                    'receiver',
                    (new Contact())->setParty($receiverParty)->setAddress($receiverAddress)
                )
            )
            ->setEshopOrderId($orderId)
        ;

        if ($payseraDeliverySettings->getResolvedProjectId() !== null) {
            $orderCreate->setProjectId($payseraDeliverySettings->getResolvedProjectId());
        }

        $merchantClient = $this->payseraDeliveryLibraryHelper->getMerchantClient();

        if ($merchantClient === null) {
            return;
        }

        if ($payseraDeliveryGatewaySettings->getReceiverType() === PayseraDeliverySettings::TYPE_PARCEL_MACHINE) {
            $order->add_order_note(
                $this->payseraDeliveryLibraryHelper->formatSelectedTerminalNote(
                    WC()->session->get('paysera_terminal_country'),
                    WC()->session->get('paysera_terminal_city'),
                    $this->payseraDeliveryHelper->resolveDeliveryGatewayCode($deliveryGatewayCode),
                    WC()->session->get('terminal')
                )
            );
        }

        try {
            $orderNumber = $merchantClient->createOrder($orderCreate)->getNumber();
        } catch (Exception $exception) {
            $errorMessage = $exception->getResponse()->getBody()->getContents();
            error_log(PayseraDeliveryLibraryHelper::PAYSERA_DELIVERY_EXCEPTION_TEXT . $errorMessage);

            $order->add_order_note(
                __(
                    PayseraPaths::PAYSERA_MESSAGE
                    . 'Delivery order creation failed, please create order manually in Paysera system',
                    PayseraPaths::PAYSERA_TRANSLATIONS
                ) . '<br>' . __('Error:', PayseraPaths::PAYSERA_TRANSLATIONS) . '<br>' . $errorMessage
            );

            return;
        }

        $order->add_order_note(
            sprintf(
                __(PayseraPaths::PAYSERA_MESSAGE . 'Delivery order created - %s', PayseraPaths::PAYSERA_TRANSLATIONS),
                $orderNumber
            )
        );
    }
}
